<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportLaporanPPICTracking;
use App\Exports\ExportPPIC_Master_so_sb;
use App\Exports\ExportPPIC_Master_so_ppic;
use App\Imports\ImportPPIC_SO;
use \avadim\FastExcelLaravel\Excel as FastExcel;
use PhpOffice\PhpSpreadsheet\Style\Style;

class PPIC_MonitoringMaterialController extends Controller
{
    public function ppic_monitoring_material(Request $request)
    {

        $data_style = DB::connection('mysql_sb')->select("SELECT styleno isi, styleno tampil from act_costing ac
                        inner join signalbit_erp.mastersupplier ms on ac.id_buyer = ms.Id_Supplier
                        where ac.status = 'CONFIRM' and ac.cost_date >= '2024-05-01' and ac.styleno != '-'
                        GROUP BY ac.styleno
                        order by ac.styleno asc
");

        return view(
            'ppic.monitoring_material',
            [
                'page' => 'dashboard-ppic',
                "subPageGroup" => "ppic-monitoring",
                "subPage" => "ppic_monitoring_material",
                "containerFluid" => true,
                "data_style" => $data_style
            ]
        );
    }


    public function show_lap_monitoring_material_f_det(Request $request)
    {
        $user = Auth::user()->name;
        // $buyer = $request->buyer;
        $style = $request->style;

        // Step 1: Get the dynamic list of (month, year)
        $periods = DB::connection('mysql_sb')->select("
  WITH gmt AS (
       SELECT sd.id AS id_so_det, id_jo, kpno, styleno, sd.*
       FROM so_det sd
       JOIN so ON sd.id_so = so.id
       JOIN act_costing ac ON so.id_cost = ac.id
       JOIN jo_det jd ON so.id = jd.id_so
       JOIN mastersupplier ms ON ac.id_buyer = ms.id_supplier
       WHERE ac.styleno = '$style' AND sd.cancel = 'N' AND so.cancel_h = 'N' and ac.status = 'CONFIRM'
   ),
   bom AS (
       SELECT a.*, mp.nama_panel, gmt.qty AS qty_order
       FROM bom_jo_item a
       JOIN gmt ON gmt.id_so_det = a.id_so_det AND gmt.id_jo = a.id_jo
       LEFT JOIN masterpanel mp ON a.id_panel = mp.id
       WHERE status = 'M'
   ),
   output_det AS (
       SELECT a.id_item, itemdesc,
           CASE WHEN a.unit = 'YRD' THEN ROUND(cons * 0.9144, 2) ELSE cons END AS cons_ws_konv,
           nama_panel,
           SUM(qty_po) AS qty_po,
           MONTH(tgl_shipment) AS bln,
           YEAR(tgl_shipment) AS thn
       FROM bom a
       JOIN masteritem mi ON a.id_item = mi.id_gen
       JOIN laravel_nds.ppic_master_so p ON a.id_so_det = p.id_so_det
       WHERE mi.mattype = 'F' AND nama_panel IS NOT NULL
       GROUP BY a.id_item, itemdesc, nama_panel, cons_ws_konv, MONTH(tgl_shipment), YEAR(tgl_shipment)
   )
   SELECT bln, thn
   FROM output_det
   GROUP BY bln, thn
   ORDER BY thn, bln
");

        // Step 2: Build dynamic columns from that result
        $monthNames = [
            1 => 'Jan',
            2 => 'Feb',
            3 => 'Mar',
            4 => 'Apr',
            5 => 'May',
            6 => 'Jun',
            7 => 'Jul',
            8 => 'Aug',
            9 => 'Sep',
            10 => 'Oct',
            11 => 'Nov',
            12 => 'Dec'
        ];
        $dynamicSelects = [];
        $dynamicBalance = [];
        $dynamicPercents = [];
        $dynamicCappedOutput = [];

        $subtractions = [];                         // For blc_after logic
        $rollingBlcExpr = "output_pcs_cons_ws";     // For rolling balance (blc_month)
        $totalUsedExpr = "0";                       // Cumulative sum of prior capped usage

        foreach ($periods as $p) {
            $colName = $monthNames[$p->bln] . '_' . $p->thn;
            $lowerCol = strtolower($colName);

            // 1. Monthly PO quantity
            $dynamicSelects[] = "SUM(CASE WHEN bln = {$p->bln} AND thn = {$p->thn} THEN qty_po ELSE 0 END) AS `$colName`";

            // 2. blc_after
            $subtractions[] = "IFNULL(`$colName`, 0)";
            $balanceExpr = "output_pcs_cons_ws - " . implode(" - ", $subtractions);
            $dynamicBalance[] = "$balanceExpr AS `blc_after_$lowerCol`";

            // 3. Capped output logic
            $remainingExpr = "GREATEST(0, output_pcs_cons_ws - ($totalUsedExpr))";
            $capExprRaw = "LEAST($remainingExpr, IFNULL(`$colName`, 0))";
            $dynamicCappedOutput[] = "$capExprRaw AS `cap_out_$lowerCol`";

            // 4. Percent logic using raw expression (NOT alias)
            $percentExpr = "CASE
                WHEN `$colName` = 0 THEN concat(0, ' %')
                ELSE concat(ROUND(($capExprRaw / `$colName`) * 100, 2), ' %')
            END AS `pct_cap_$lowerCol`";
            $dynamicPercents[] = $percentExpr;

            // 5. Update total used expression for next month
            $totalUsedExpr .= " + $capExprRaw";
        }


        // Final SQL-ready strings
        $selectString         = implode(",\n", $dynamicSelects);
        $balanceString        = implode(",\n", $dynamicBalance);
        $percentString        = implode(",\n", $dynamicPercents);
        $cappedOutputString   = implode(",\n", $dynamicCappedOutput);

        $data_monitoring_mat_f_det = DB::connection('mysql_sb')->select("WITH gmt as (
            select sd.id as id_so_det,id_jo,kpno,styleno, sd.* from so_det sd
            inner join so on sd.id_so = so.id
            inner join act_costing ac on so.id_cost = ac.id
            inner join jo_det jd on so.id = jd.id_so
            inner join mastersupplier ms on ac.id_buyer = ms.id_supplier
            where ac.styleno = '$style' and sd.cancel = 'N' and so.cancel_h = 'N'
            ),
            bom_global as (
            select a.id_jo, id_item, a.unit from bom_jo_global_item a
            inner join gmt on a.id_jo = gmt.id_jo
            group by a.id_item, a.id_jo, a.unit
            ),
            bom as (
            select a.*, mp.nama_panel, gmt.qty qty_order from bom_jo_item a
            inner join gmt on gmt.id_so_det  = a.id_so_det and gmt.id_jo = a.id_jo
            left join masterpanel mp on a.id_panel = mp.id
            where status = 'M' and a.cancel = 'N'
            ),
            bom_det as (
            select a.id_jo,mi.id_item, mi.mattype, itemdesc, a.unit,
            case
            when a.unit = 'YRD' THEN round(sum(cons * qty) * 0.9144,2)
            else round(sum(cons * qty),2)
            end as total_cons,
            case
            when a.unit = 'YRD' THEN 'METER'
            else a.unit
            end as unit_konv
            from bom a
            inner join masteritem mi on a.id_item = mi.id_gen
            inner join so_det sd on a.id_so_det = sd.id
            where status = 'M' and a.cancel = 'N' and sd.cancel = 'N'
            group by id_item
            ),
            bpb_f as (
            select
            a.id_item,
            SUM(CASE WHEN d.kategori = 'Pembelian' THEN
                    CASE
                        WHEN b.unit = 'YRD' THEN qty_good * 0.9144
                        ELSE qty_good
                    END
                ELSE 0 END) AS qty_pembelian,
            SUM(CASE WHEN d.kategori = 'Pengembalian dari Subkontraktor' THEN
                    CASE
                        WHEN b.unit = 'YRD' THEN qty_good * 0.9144
                        ELSE qty_good
                    END
                ELSE 0 END) AS qty_retur_subkon,
            SUM(CASE WHEN d.kategori = 'Retur Produksi' THEN
                    CASE
                        WHEN b.unit = 'YRD' THEN qty_good * 0.9144
                        ELSE qty_good
                    END
                ELSE 0 END) AS qty_retur_prod,
            SUM(CASE WHEN d.kategori = 'Adjustment' THEN
                    CASE
                        WHEN b.unit = 'YRD' THEN qty_good * 0.9144
                        ELSE qty_good
                    END
                ELSE 0 END) AS qty_adj,
                CASE
                    WHEN b.unit = 'YRD' THEN 'METER'
                    ELSE b.unit
                END AS unit_konv
            from bom_det a
            left join whs_inmaterial_fabric_det b on a.id_item = b.id_item and a.id_jo = b.id_jo
            left join whs_inmaterial_fabric c on b.no_dok = c.no_dok
            left join whs_master_pilihan d on c.type_pch = d.nama_pilihan
            where mattype = 'F'
            group by id_item, unit_konv
            ),
            bpb_f_glb as (
            select
            a.id_item,
            SUM(CASE WHEN d.kategori = 'Pembelian' THEN
                    CASE
                        WHEN b.unit = 'YRD' THEN qty_good * 0.9144
                        ELSE qty_good
                    END
                ELSE 0 END) AS qty_pembelian,
            SUM(CASE WHEN d.kategori = 'Pengembalian dari Subkontraktor' THEN
                    CASE
                        WHEN b.unit = 'YRD' THEN qty_good * 0.9144
                        ELSE qty_good
                    END
                ELSE 0 END) AS qty_retur_subkon,
            SUM(CASE WHEN d.kategori = 'Retur Produksi' THEN
                    CASE
                        WHEN b.unit = 'YRD' THEN qty_good * 0.9144
                        ELSE qty_good
                    END
                ELSE 0 END) AS qty_retur_prod,
            SUM(CASE WHEN d.kategori = 'Adjustment' THEN
                    CASE
                        WHEN b.unit = 'YRD' THEN qty_good * 0.9144
                        ELSE qty_good
                    END
                ELSE 0 END) AS qty_adj,
                CASE
                    WHEN b.unit = 'YRD' THEN 'METER'
                    ELSE b.unit
                END AS unit_konv
            from bom_global a
            inner join masteritem mi on a.id_item = mi.id_item
            left join whs_inmaterial_fabric_det b on a.id_item = b.id_item and a.id_jo = b.id_jo
            left join whs_inmaterial_fabric c on b.no_dok = c.no_dok
            left join whs_master_pilihan d on c.type_pch = d.nama_pilihan
            where mattype = 'F'
            group by id_item, unit_konv
            ),
            mut_bpb_f as (
            select
            id_item,
            sum(qty_pembelian) qty_pembelian,
            sum(qty_retur_subkon) qty_retur_subkon,
            sum(qty_retur_prod) qty_retur_prod,
            sum(qty_adj) qty_adj,
            unit_konv
            from
            (
            select * from bpb_f group by id_item, unit_konv
            union
            select * from bpb_f_glb group by id_item, unit_konv
            ) a
            group by id_item, unit_konv
            having unit_konv is not null
            ),
            bppb_f as (
            select
            a.id_item,
            SUM(CASE WHEN d.kategori = 'Pengeluaran Produksi' THEN
                    CASE
                        WHEN b.satuan = 'YRD' THEN qty_out * 0.9144
                        ELSE qty_out
                    END
                ELSE 0 END) AS qty_out_prod,
            SUM(CASE WHEN d.kategori = 'Pengiriman ke Subkontraktor' THEN
                    CASE
                        WHEN b.satuan = 'YRD' THEN qty_out * 0.9144
                        ELSE qty_out
                    END
                ELSE 0 END) AS qty_out_subkon,
            SUM(CASE WHEN d.kategori = 'Retur Pembelian' THEN
                    CASE
                        WHEN b.satuan = 'YRD' THEN qty_out * 0.9144
                        ELSE qty_out
                    END
                ELSE 0 END) AS qty_retur_pembelian,
            SUM(CASE WHEN d.kategori = 'Pengeluaran Sample' THEN
                    CASE
                        WHEN b.satuan = 'YRD' THEN qty_out * 0.9144
                        ELSE qty_out
                    END
                ELSE 0 END) AS qty_out_sample,
            SUM(CASE WHEN d.kategori = 'Lainnya' THEN
                    CASE
                        WHEN b.satuan = 'YRD' THEN qty_out * 0.9144
                        ELSE qty_out
                    END
                ELSE 0 END) AS qty_out_lainnya,
            SUM(CASE WHEN d.kategori = 'Adjustment' THEN
                    CASE
                        WHEN b.satuan = 'YRD' THEN qty_out * 0.9144
                        ELSE qty_out
                    END
                ELSE 0 END) AS qty_out_adj,
                CASE
                    WHEN b.satuan = 'YRD' THEN 'METER'
                    ELSE b.satuan
                END AS unit_konv
            from bom_det a
            left join whs_bppb_det b on a.id_item = b.id_item and a.id_jo = b.id_jo
            left join whs_bppb_h c on b.no_bppb = c.no_bppb
            left join mastertransaksi d on c.jenis_pengeluaran = d.nama_trans
            where mattype = 'F'
            group by id_item, unit_konv
            ),
            bppb_f_glb as (
            select
            a.id_item,
            SUM(CASE WHEN d.kategori = 'Pengeluaran Produksi' THEN
                    CASE
                        WHEN b.satuan = 'YRD' THEN qty_out * 0.9144
                        ELSE qty_out
                    END
                ELSE 0 END) AS qty_out_prod,
            SUM(CASE WHEN d.kategori = 'Pengiriman ke Subkontraktor' THEN
                    CASE
                        WHEN b.satuan = 'YRD' THEN qty_out * 0.9144
                        ELSE qty_out
                    END
                ELSE 0 END) AS qty_out_subkon,
            SUM(CASE WHEN d.kategori = 'Retur Pembelian' THEN
                    CASE
                        WHEN b.satuan = 'YRD' THEN qty_out * 0.9144
                        ELSE qty_out
                    END
                ELSE 0 END) AS qty_retur_pembelian,
            SUM(CASE WHEN d.kategori = 'Pengeluaran Sample' THEN
                    CASE
                        WHEN b.satuan = 'YRD' THEN qty_out * 0.9144
                        ELSE qty_out
                    END
                ELSE 0 END) AS qty_out_sample,
            SUM(CASE WHEN d.kategori = 'Lainnya' THEN
                    CASE
                        WHEN b.satuan = 'YRD' THEN qty_out * 0.9144
                        ELSE qty_out
                    END
                ELSE 0 END) AS qty_out_lainnya,
            SUM(CASE WHEN d.kategori = 'Adjustment' THEN
                    CASE
                        WHEN b.satuan = 'YRD' THEN qty_out * 0.9144
                        ELSE qty_out
                    END
                ELSE 0 END) AS qty_out_adj,
                CASE
                    WHEN b.satuan = 'YRD' THEN 'METER'
                    ELSE b.satuan
                END AS unit_konv
            from bom_global a
            inner join masteritem mi on a.id_item = mi.id_item
            left join whs_bppb_det b on a.id_item = b.id_item  and a.id_jo = b.id_jo
            left join whs_bppb_h c on b.no_bppb = c.no_bppb
            left join mastertransaksi d on c.jenis_pengeluaran = d.nama_trans
            where mattype = 'F'
            group by id_item, unit_konv
            ),
            mut_bppb_f as (
            select
            id_item,
            sum(qty_out_prod) qty_out_prod,
            sum(qty_out_subkon) qty_out_subkon,
            sum(qty_retur_pembelian) qty_retur_pembelian,
            sum(qty_out_sample) qty_out_sample,
            sum(qty_out_lainnya) qty_out_lainnya,
            sum(qty_out_adj) qty_out_adj,
            unit_konv
            from
            (
            select * from bppb_f group by id_item, unit_konv
            union
            select * from bppb_f_glb group by id_item, unit_konv
            ) a
            group by id_item, unit_konv
            having unit_konv is not null
            ),
            mat_det as (
            SELECT
            mi.id_item,
            mi.itemdesc,
            color,
            mi.mattype,
            a.nama_panel,
            sum(a.qty_order) qty_order,
            case
            when a.unit = 'YRD' THEN round(cons * 0.9144,3)
            else cons
            end as cons_ws_konv,
            case
            when a.unit = 'YRD' THEN cons * 0.9144
            else cons
            end as cons_ws_konv_hit,
            case
            when a.unit = 'YRD' THEN round(sum(a.qty_order) * (cons * 0.9144),2)
            else round(sum(a.qty_order) * cons,2)
            end as need_material,
            case
            when a.unit = 'YRD' THEN 'METER'
            else a.unit
            end as unit_konv
            from bom a
            inner join masteritem mi on a.id_item = mi.id_gen
            where mi.mattype = 'F'
            group by id_item, color, nama_panel,cons_ws_konv
            ),
            output_det as (
            SELECT id_item, sum(qty_po) qty_po,cons_ws_konv, nama_panel, month(tgl_shipment) bln, year(tgl_shipment) thn FROM
            (
            SELECT
            id_so_det,
            mi.id_item,
            case
            when a.unit = 'YRD' THEN round(cons * 0.9144,3)
            else cons
            end as cons_ws_konv,
            nama_panel
            from bom a
            inner join masteritem mi on a.id_item = mi.id_gen
            where mi.mattype = 'F' and nama_panel is not null
            group by id_so_det, case
            when a.unit = 'YRD' THEN round(cons * 0.9144,3)
            else cons
            end, nama_panel,
                        mi.id_item
            )a
            inner join laravel_nds.ppic_master_so p on a.id_so_det = p.id_so_det
            group by id_item, cons_ws_konv, nama_panel, month(tgl_shipment), year(tgl_shipment)
            order by month(tgl_shipment) asc, year(tgl_shipment) asc, nama_panel asc
            ),
            col_gmt as (
            select mi.id_item, GROUP_CONCAT(DISTINCT sd.color SEPARATOR ' | ') col_gmt from bom
            inner join masteritem mi on bom.id_item = mi.id_gen
            inner join so_det sd on bom.id_so_det = sd.id
            group by id_item
            )

SELECT *,
    $balanceString,
    $percentString,
    $cappedOutputString
FROM
(
SELECT
a.id_item,
a.id_item AS id_item_mat,
a.itemdesc,
a.color,
a.mattype,
a.nama_panel AS nama_panel_mat,
a.nama_panel,
a.qty_order,
a.cons_ws_konv,
a.cons_ws_konv AS cons_ws_konv_mat,
a.need_material,
a.unit_konv,
round(b.tot_mat,2) tot_mat,
round(a.need_material / b.tot_mat * 100,2) dist_cons_ws,
round(coalesce(qty_pembelian,0),2) qty_pembelian,
round(coalesce(qty_retur_prod,0),2) qty_retur_prod,
round(coalesce(qty_out_prod,0),2) qty_out_prod,
ROUND(COALESCE(qty_pembelian, 0) + COALESCE(qty_retur_prod, 0) - COALESCE(qty_out_prod, 0),2) AS blc_mat,
FLOOR(COALESCE(qty_pembelian, 0) * (COALESCE(a.need_material, 0) / NULLIF(COALESCE(b.tot_mat, 0), 0)) / NULLIF(COALESCE(a.cons_ws_konv_hit, 0), 0)) AS output_pcs_cons_ws,
SUM(FLOOR(COALESCE(qty_pembelian, 0) * (COALESCE(a.need_material, 0) / NULLIF(COALESCE(b.tot_mat, 0), 0)) / NULLIF(COALESCE(a.cons_ws_konv_hit, 0), 0))) OVER (PARTITION BY a.nama_panel, a.id_item) AS total_output_pcs_cons_ws,
FLOOR(COALESCE(qty_pembelian, 0) * (COALESCE(a.need_material, 0) / NULLIF(COALESCE(b.tot_mat, 0), 0)) / NULLIF(COALESCE(a.cons_ws_konv_hit, 0), 0)) - COALESCE(a.qty_order, 0) AS blc_order
from mat_det a
left join (select id_item, sum(need_material) tot_mat from mat_det group by id_item) b on a.id_item = b.id_item
left join mut_bpb_f on a.id_item = mut_bpb_f.id_item
left join mut_bppb_f on a.id_item = mut_bppb_f.id_item
) a
left join
(
        SELECT
            id_item, nama_panel, cons_ws_konv,
            $selectString
        FROM output_det a
        GROUP BY id_item, nama_panel, cons_ws_konv
) b on a.id_item = b.id_item and a.cons_ws_konv = b.cons_ws_konv
left join col_gmt c on a.id_item_mat = c.id_item
order by a.id_item asc,a.nama_panel asc, color asc
        ");

        // Build final dynamic column names (to send to frontend)
        $finalDynamicColumnNames = [];
        foreach ($periods as $p) {
            $col = $monthNames[$p->bln] . '_' . $p->thn;
            $lower = strtolower($col);
            $finalDynamicColumnNames[] = $col;
            $finalDynamicColumnNames[] = "cap_out_$lower";
            $finalDynamicColumnNames[] = "pct_cap_$lower";
        }

        // Format data rows
        $formattedDataRows = [];
        foreach ($data_monitoring_mat_f_det as $row) {
            // Convert stdClass to array
            $rowArray = (array) $row;

            $formattedDataRows[] = [
                'id_item' => $rowArray['id_item_mat'],
                'itemdesc' => $rowArray['itemdesc'],
                'color' => $rowArray['color'],
                'col_gmt' => $rowArray['col_gmt'],
                'nama_panel' => $rowArray['nama_panel_mat'],
                'qty_order' => $rowArray['qty_order'],
                'cons_ws_konv' => $rowArray['cons_ws_konv_mat'],
                'need_material' => $rowArray['need_material'],
                'tot_mat' => $rowArray['tot_mat'],
                'qty_pembelian' => $rowArray['qty_pembelian'],
                'qty_retur_prod' => $rowArray['qty_retur_prod'],
                'qty_out_prod' => $rowArray['qty_out_prod'],
                'blc_mat' => $rowArray['blc_mat'],
                'unit_konv' => $rowArray['unit_konv'],
                'output_pcs_cons_ws' => $rowArray['output_pcs_cons_ws'],
                'blc_order' => $rowArray['blc_order'],
                'total_output_pcs_cons_ws' => $rowArray['total_output_pcs_cons_ws'],

                // Dynamic monthly values in correct order
                'values' => array_map(fn($colName) => $rowArray[$colName] ?? 0, $finalDynamicColumnNames),
            ];
        }

        // Send formatted result
        return response()->json([
            'columns' => $finalDynamicColumnNames,     // Dynamic column names to match `values[]`
            'data' => $formattedDataRows               // Row data with fixed fields + dynamic `values`
        ]);

        // return DataTables::of($data_monitoring_mat_f_det)->toJson();

        // return response()->json([
        //     'columns' => $periods,
        //     'data' => $data_monitoring_mat_f_det
        // ]);
    }
}
