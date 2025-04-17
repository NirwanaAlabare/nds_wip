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

        $data_buyer = DB::connection('mysql_sb')->select("SELECT supplier isi, supplier tampil from signalbit_erp.so
                                inner join signalbit_erp.act_costing ac on so.id_cost = ac.id
                                inner join signalbit_erp.mastersupplier ms on ac.id_buyer = ms.Id_Supplier
                                where so_date >= '2024-05-01' and ac.status = 'CONFIRM'
                                GROUP BY supplier
                                order by supplier asc
");

        return view(
            'ppic.monitoring_material',
            [
                'page' => 'dashboard-ppic',
                "subPageGroup" => "ppic-monitoring",
                "subPage" => "ppic_monitoring_material",
                "containerFluid" => true,
                "data_buyer" => $data_buyer
            ]
        );
    }

    public function get_ppic_monitoring_material_style(Request $request)
    {
        $data_style =  DB::connection('mysql_sb')->select("SELECT styleno isi, styleno tampil from act_costing ac
                        inner join signalbit_erp.mastersupplier ms on ac.id_buyer = ms.Id_Supplier
                        where ac.status = 'CONFIRM' and ac.cost_date >= '2024-05-01' and supplier = '" . $request->buyer . "'
                        GROUP BY ac.styleno
                        order by ac.styleno asc

        ");
        $html = "<option value=''>Pilih Style</option>";

        foreach ($data_style as $datastyle) {
            $html .= " <option value='" . $datastyle->isi . "'>" . $datastyle->tampil . "</option> ";
        }

        return $html;
    }


    public function show_lap_monitoring_material_f_det(Request $request)
    {
        $user = Auth::user()->name;
        $buyer = $request->buyer_filter;
        $style = $request->style_filter;

        $data_monitoring_mat_f_det = DB::connection('mysql_sb')->select("WITH gmt as (
select sd.id as id_so_det,id_jo,kpno,styleno, sd.* from so_det sd
inner join so on sd.id_so = so.id
inner join act_costing ac on so.id_cost = ac.id
inner join jo_det jd on so.id = jd.id_so
inner join mastersupplier ms on ac.id_buyer = ms.id_supplier
where ac.styleno = '$style' and ms.supplier = '$buyer' and sd.cancel = 'N' and so.cancel_h = 'N'
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
where status = 'M'
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
            WHEN a.unit = 'YRD' THEN qty_aktual * 0.9144
            ELSE qty_aktual
        END
    ELSE 0 END) AS qty_pembelian,
SUM(CASE WHEN d.kategori = 'Pengembalian dari Subkontraktor' THEN
        CASE
            WHEN a.unit = 'YRD' THEN qty_aktual * 0.9144
            ELSE qty_aktual
        END
    ELSE 0 END) AS qty_retur_subkon,
SUM(CASE WHEN d.kategori = 'Retur Produksi' THEN
        CASE
            WHEN a.unit = 'YRD' THEN qty_aktual * 0.9144
            ELSE qty_aktual
        END
    ELSE 0 END) AS qty_retur_prod,
SUM(CASE WHEN d.kategori = 'Adjustment' THEN
        CASE
            WHEN a.unit = 'YRD' THEN qty_aktual * 0.9144
            ELSE qty_aktual
        END
    ELSE 0 END) AS qty_adj,
    CASE
        WHEN a.unit = 'YRD' THEN 'METER'
        ELSE a.unit
    END AS unit_konv
from bom_det a
left join whs_lokasi_inmaterial b on a.id_item = b.id_item and a.unit = b.satuan and a.id_jo = b.id_jo
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
            WHEN a.unit = 'YRD' THEN qty_aktual * 0.9144
            ELSE qty_aktual
        END
    ELSE 0 END) AS qty_pembelian,
SUM(CASE WHEN d.kategori = 'Pengembalian dari Subkontraktor' THEN
        CASE
            WHEN a.unit = 'YRD' THEN qty_aktual * 0.9144
            ELSE qty_aktual
        END
    ELSE 0 END) AS qty_retur_subkon,
SUM(CASE WHEN d.kategori = 'Retur Produksi' THEN
        CASE
            WHEN a.unit = 'YRD' THEN qty_aktual * 0.9144
            ELSE qty_aktual
        END
    ELSE 0 END) AS qty_retur_prod,
SUM(CASE WHEN d.kategori = 'Adjustment' THEN
        CASE
            WHEN a.unit = 'YRD' THEN qty_aktual * 0.9144
            ELSE qty_aktual
        END
    ELSE 0 END) AS qty_adj,
    CASE
        WHEN a.unit = 'YRD' THEN 'METER'
        ELSE a.unit
    END AS unit_konv
from bom_global a
inner join masteritem mi on a.id_item = mi.id_item
left join whs_lokasi_inmaterial b on a.id_item = b.id_item and a.unit = b.satuan and a.id_jo = b.id_jo
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
),
bppb_f as (
select
a.id_item,
SUM(CASE WHEN d.kategori = 'Pengeluaran Produksi' THEN
        CASE
            WHEN a.unit = 'YRD' THEN qty_out * 0.9144
            ELSE qty_out
        END
    ELSE 0 END) AS qty_out_prod,
SUM(CASE WHEN d.kategori = 'Pengiriman ke Subkontraktor' THEN
        CASE
            WHEN a.unit = 'YRD' THEN qty_out * 0.9144
            ELSE qty_out
        END
    ELSE 0 END) AS qty_out_subkon,
SUM(CASE WHEN d.kategori = 'Retur Pembelian' THEN
        CASE
            WHEN a.unit = 'YRD' THEN qty_out * 0.9144
            ELSE qty_out
        END
    ELSE 0 END) AS qty_retur_pembelian,
SUM(CASE WHEN d.kategori = 'Pengeluaran Sample' THEN
        CASE
            WHEN a.unit = 'YRD' THEN qty_out * 0.9144
            ELSE qty_out
        END
    ELSE 0 END) AS qty_out_sample,
SUM(CASE WHEN d.kategori = 'Lainnya' THEN
        CASE
            WHEN a.unit = 'YRD' THEN qty_out * 0.9144
            ELSE qty_out
        END
    ELSE 0 END) AS qty_out_lainnya,
SUM(CASE WHEN d.kategori = 'Adjustment' THEN
        CASE
            WHEN a.unit = 'YRD' THEN qty_out * 0.9144
            ELSE qty_out
        END
    ELSE 0 END) AS qty_out_adj,
    CASE
        WHEN a.unit = 'YRD' THEN 'METER'
        ELSE a.unit
    END AS unit_konv
from bom_det a
left join whs_bppb_det b on a.id_item = b.id_item and a.unit = b.satuan and a.id_jo = b.id_jo
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
            WHEN a.unit = 'YRD' THEN qty_out * 0.9144
            ELSE qty_out
        END
    ELSE 0 END) AS qty_out_prod,
SUM(CASE WHEN d.kategori = 'Pengiriman ke Subkontraktor' THEN
        CASE
            WHEN a.unit = 'YRD' THEN qty_out * 0.9144
            ELSE qty_out
        END
    ELSE 0 END) AS qty_out_subkon,
SUM(CASE WHEN d.kategori = 'Retur Pembelian' THEN
        CASE
            WHEN a.unit = 'YRD' THEN qty_out * 0.9144
            ELSE qty_out
        END
    ELSE 0 END) AS qty_retur_pembelian,
SUM(CASE WHEN d.kategori = 'Pengeluaran Sample' THEN
        CASE
            WHEN a.unit = 'YRD' THEN qty_out * 0.9144
            ELSE qty_out
        END
    ELSE 0 END) AS qty_out_sample,
SUM(CASE WHEN d.kategori = 'Lainnya' THEN
        CASE
            WHEN a.unit = 'YRD' THEN qty_out * 0.9144
            ELSE qty_out
        END
    ELSE 0 END) AS qty_out_lainnya,
SUM(CASE WHEN d.kategori = 'Adjustment' THEN
        CASE
            WHEN a.unit = 'YRD' THEN qty_out * 0.9144
            ELSE qty_out
        END
    ELSE 0 END) AS qty_out_adj,
    CASE
        WHEN a.unit = 'YRD' THEN 'METER'
        ELSE a.unit
    END AS unit_konv
from bom_global a
inner join masteritem mi on a.id_item = mi.id_item
left join whs_bppb_det b on a.id_item = b.id_item and a.unit = b.satuan and a.id_jo = b.id_jo
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
group by nama_panel, cons, id_item
),
output_det as (
SELECT id_item, sum(qty_po) qty_po, nama_panel, month(tgl_shipment) bln, year(tgl_shipment) thn FROM
(
SELECT
id_so_det,
mi.id_item,
	case
			when a.unit = 'YRD' THEN round(cons * 0.9144,2)
			else cons
			end as cons_ws_konv,
nama_panel
from bom a
inner join masteritem mi on a.id_item = mi.id_gen
where mi.mattype = 'F' and nama_panel is not null
group by id_so_det, 	case
			when a.unit = 'YRD' THEN round(cons * 0.9144,2)
			else cons
			end, nama_panel
)	a
inner join laravel_nds.ppic_master_so p on a.id_so_det = p.id_so_det
group by id_item, cons_ws_konv, nama_panel, month(tgl_shipment), year(tgl_shipment)
order by month(tgl_shipment) asc, year(tgl_shipment) asc, nama_panel asc
)


SELECT
a.id_item,
a.itemdesc,
a.color,
a.mattype,
a.nama_panel,
a.qty_order,
a.cons_ws_konv,
a.need_material,
a.unit_konv,
round(b.tot_mat,2) tot_mat,
round(a.need_material / b.tot_mat * 100,2) dist_cons_ws,
round(qty_pembelian,2) qty_pembelian,
round(qty_retur_prod,2) qty_retur_prod,
round(qty_out_prod,2) qty_out_prod,
qty_pembelian + qty_retur_prod - qty_out_prod blc_mat,
FLOOR((qty_pembelian * (a.need_material / b.tot_mat)) / a.cons_ws_konv_hit) output_pcs_cons_ws,
SUM(FLOOR((qty_pembelian * (a.need_material / b.tot_mat)) / a.cons_ws_konv_hit)) OVER (PARTITION BY a.nama_panel, a.id_item) AS total_output_pcs_cons_ws,
FLOOR((qty_pembelian * (a.need_material / b.tot_mat)) / a.cons_ws_konv_hit) - a.qty_order as blc_order
from mat_det a
left join (select id_item, sum(need_material) tot_mat from mat_det group by id_item) b on a.id_item = b.id_item
left join mut_bpb_f on a.id_item = mut_bpb_f.id_item
left join mut_bppb_f on a.id_item = mut_bppb_f.id_item
order by id_item asc, color asc

        ");

        return DataTables::of($data_monitoring_mat_f_det)->toJson();
    }

    public function export_excel_monitoring_material(Request $request)
    {
        $user = Auth::user()->name;
        $buyer = $request->buyer_filter;
        $style = $request->style_filter;

        $data_monitoring_mat_f_det = DB::connection('mysql_sb')->select("WITH gmt as (
            select sd.id as id_so_det,id_jo,kpno,styleno, sd.* from so_det sd
            inner join so on sd.id_so = so.id
            inner join act_costing ac on so.id_cost = ac.id
            inner join jo_det jd on so.id = jd.id_so
            inner join mastersupplier ms on ac.id_buyer = ms.id_supplier
            where ac.styleno = '$style' and ms.supplier = '$buyer' and sd.cancel = 'N' and so.cancel_h = 'N'
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
            where status = 'M'
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
                        WHEN a.unit = 'YRD' THEN qty_aktual * 0.9144
                        ELSE qty_aktual
                    END
                ELSE 0 END) AS qty_pembelian,
            SUM(CASE WHEN d.kategori = 'Pengembalian dari Subkontraktor' THEN
                    CASE
                        WHEN a.unit = 'YRD' THEN qty_aktual * 0.9144
                        ELSE qty_aktual
                    END
                ELSE 0 END) AS qty_retur_subkon,
            SUM(CASE WHEN d.kategori = 'Retur Produksi' THEN
                    CASE
                        WHEN a.unit = 'YRD' THEN qty_aktual * 0.9144
                        ELSE qty_aktual
                    END
                ELSE 0 END) AS qty_retur_prod,
            SUM(CASE WHEN d.kategori = 'Adjustment' THEN
                    CASE
                        WHEN a.unit = 'YRD' THEN qty_aktual * 0.9144
                        ELSE qty_aktual
                    END
                ELSE 0 END) AS qty_adj,
                CASE
                    WHEN a.unit = 'YRD' THEN 'METER'
                    ELSE a.unit
                END AS unit_konv
            from bom_det a
            left join whs_lokasi_inmaterial b on a.id_item = b.id_item and a.unit = b.satuan and a.id_jo = b.id_jo
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
                        WHEN a.unit = 'YRD' THEN qty_aktual * 0.9144
                        ELSE qty_aktual
                    END
                ELSE 0 END) AS qty_pembelian,
            SUM(CASE WHEN d.kategori = 'Pengembalian dari Subkontraktor' THEN
                    CASE
                        WHEN a.unit = 'YRD' THEN qty_aktual * 0.9144
                        ELSE qty_aktual
                    END
                ELSE 0 END) AS qty_retur_subkon,
            SUM(CASE WHEN d.kategori = 'Retur Produksi' THEN
                    CASE
                        WHEN a.unit = 'YRD' THEN qty_aktual * 0.9144
                        ELSE qty_aktual
                    END
                ELSE 0 END) AS qty_retur_prod,
            SUM(CASE WHEN d.kategori = 'Adjustment' THEN
                    CASE
                        WHEN a.unit = 'YRD' THEN qty_aktual * 0.9144
                        ELSE qty_aktual
                    END
                ELSE 0 END) AS qty_adj,
                CASE
                    WHEN a.unit = 'YRD' THEN 'METER'
                    ELSE a.unit
                END AS unit_konv
            from bom_global a
            inner join masteritem mi on a.id_item = mi.id_item
            left join whs_lokasi_inmaterial b on a.id_item = b.id_item and a.unit = b.satuan and a.id_jo = b.id_jo
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
            ),
            bppb_f as (
            select
            a.id_item,
            SUM(CASE WHEN d.kategori = 'Pengeluaran Produksi' THEN
                    CASE
                        WHEN a.unit = 'YRD' THEN qty_out * 0.9144
                        ELSE qty_out
                    END
                ELSE 0 END) AS qty_out_prod,
            SUM(CASE WHEN d.kategori = 'Pengiriman ke Subkontraktor' THEN
                    CASE
                        WHEN a.unit = 'YRD' THEN qty_out * 0.9144
                        ELSE qty_out
                    END
                ELSE 0 END) AS qty_out_subkon,
            SUM(CASE WHEN d.kategori = 'Retur Pembelian' THEN
                    CASE
                        WHEN a.unit = 'YRD' THEN qty_out * 0.9144
                        ELSE qty_out
                    END
                ELSE 0 END) AS qty_retur_pembelian,
            SUM(CASE WHEN d.kategori = 'Pengeluaran Sample' THEN
                    CASE
                        WHEN a.unit = 'YRD' THEN qty_out * 0.9144
                        ELSE qty_out
                    END
                ELSE 0 END) AS qty_out_sample,
            SUM(CASE WHEN d.kategori = 'Lainnya' THEN
                    CASE
                        WHEN a.unit = 'YRD' THEN qty_out * 0.9144
                        ELSE qty_out
                    END
                ELSE 0 END) AS qty_out_lainnya,
            SUM(CASE WHEN d.kategori = 'Adjustment' THEN
                    CASE
                        WHEN a.unit = 'YRD' THEN qty_out * 0.9144
                        ELSE qty_out
                    END
                ELSE 0 END) AS qty_out_adj,
                CASE
                    WHEN a.unit = 'YRD' THEN 'METER'
                    ELSE a.unit
                END AS unit_konv
            from bom_det a
            left join whs_bppb_det b on a.id_item = b.id_item and a.unit = b.satuan and a.id_jo = b.id_jo
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
                        WHEN a.unit = 'YRD' THEN qty_out * 0.9144
                        ELSE qty_out
                    END
                ELSE 0 END) AS qty_out_prod,
            SUM(CASE WHEN d.kategori = 'Pengiriman ke Subkontraktor' THEN
                    CASE
                        WHEN a.unit = 'YRD' THEN qty_out * 0.9144
                        ELSE qty_out
                    END
                ELSE 0 END) AS qty_out_subkon,
            SUM(CASE WHEN d.kategori = 'Retur Pembelian' THEN
                    CASE
                        WHEN a.unit = 'YRD' THEN qty_out * 0.9144
                        ELSE qty_out
                    END
                ELSE 0 END) AS qty_retur_pembelian,
            SUM(CASE WHEN d.kategori = 'Pengeluaran Sample' THEN
                    CASE
                        WHEN a.unit = 'YRD' THEN qty_out * 0.9144
                        ELSE qty_out
                    END
                ELSE 0 END) AS qty_out_sample,
            SUM(CASE WHEN d.kategori = 'Lainnya' THEN
                    CASE
                        WHEN a.unit = 'YRD' THEN qty_out * 0.9144
                        ELSE qty_out
                    END
                ELSE 0 END) AS qty_out_lainnya,
            SUM(CASE WHEN d.kategori = 'Adjustment' THEN
                    CASE
                        WHEN a.unit = 'YRD' THEN qty_out * 0.9144
                        ELSE qty_out
                    END
                ELSE 0 END) AS qty_out_adj,
                CASE
                    WHEN a.unit = 'YRD' THEN 'METER'
                    ELSE a.unit
                END AS unit_konv
            from bom_global a
            inner join masteritem mi on a.id_item = mi.id_item
            left join whs_bppb_det b on a.id_item = b.id_item and a.unit = b.satuan and a.id_jo = b.id_jo
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
            group by nama_panel, cons, id_item
            ),
            output_det as (
            SELECT id_item, sum(qty_po) qty_po, nama_panel, month(tgl_shipment) bln, year(tgl_shipment) thn FROM
            (
            SELECT
            id_so_det,
            mi.id_item,
                case
                        when a.unit = 'YRD' THEN round(cons * 0.9144,2)
                        else cons
                        end as cons_ws_konv,
            nama_panel
            from bom a
            inner join masteritem mi on a.id_item = mi.id_gen
            where mi.mattype = 'F' and nama_panel is not null
            group by id_so_det, 	case
                        when a.unit = 'YRD' THEN round(cons * 0.9144,2)
                        else cons
                        end, nama_panel
            )	a
            inner join laravel_nds.ppic_master_so p on a.id_so_det = p.id_so_det
            group by id_item, cons_ws_konv, nama_panel, month(tgl_shipment), year(tgl_shipment)
            order by month(tgl_shipment) asc, year(tgl_shipment) asc, nama_panel asc
            )


            SELECT
            a.id_item,
            a.itemdesc,
            a.color,
            a.mattype,
            a.nama_panel,
            a.qty_order,
            a.cons_ws_konv,
            a.need_material,
            a.unit_konv,
            round(b.tot_mat,2) tot_mat,
            round(a.need_material / b.tot_mat * 100,2) dist_cons_ws,
            round(qty_pembelian,2) qty_pembelian,
            round(qty_retur_prod,2) qty_retur_prod,
            round(qty_out_prod,2) qty_out_prod,
            qty_pembelian + qty_retur_prod - qty_out_prod blc_mat,
            FLOOR((qty_pembelian * (a.need_material / b.tot_mat)) / a.cons_ws_konv_hit) output_pcs_cons_ws,
            SUM(FLOOR((qty_pembelian * (a.need_material / b.tot_mat)) / a.cons_ws_konv_hit)) OVER (PARTITION BY a.nama_panel, a.id_item) AS total_output_pcs_cons_ws,
            FLOOR((qty_pembelian * (a.need_material / b.tot_mat)) / a.cons_ws_konv_hit) - a.qty_order as blc_order
            from mat_det a
            left join (select id_item, sum(need_material) tot_mat from mat_det group by id_item) b on a.id_item = b.id_item
            left join mut_bpb_f on a.id_item = mut_bpb_f.id_item
            left join mut_bppb_f on a.id_item = mut_bppb_f.id_item
            order by id_item asc, color asc
                    ");

        return response()->json($data_monitoring_mat_f_det);
    }
}
