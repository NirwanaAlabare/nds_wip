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

class PPIC_MonitoringMaterialDetController extends Controller
{
    public function ppic_monitoring_material_det(Request $request)
    {

        $data_style = DB::connection('mysql_sb')->select("SELECT styleno isi, styleno tampil from act_costing ac
                        inner join signalbit_erp.mastersupplier ms on ac.id_buyer = ms.Id_Supplier
                        where ac.status = 'CONFIRM' and ac.cost_date >= '2024-05-01' and ac.styleno != '-'
                        GROUP BY ac.styleno
                        order by ac.styleno asc
");

        return view(
            'ppic.monitoring_material_det',
            [
                'page' => 'dashboard-ppic',
                "subPageGroup" => "ppic-monitoring",
                "subPage" => "ppic_monitoring_material_det",
                "containerFluid" => true,
                "data_style" => $data_style
            ]
        );
    }
    public function show_lap_monitoring_material_f_detail(Request $request)
    {
        $user = Auth::user()->name;
        $style = $request->style_filter;

        $data_monitoring_order = DB::connection('mysql_sb')->select("WITH gmt as (
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
c.tgl_dok,
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
'0' AS qty_out_prod,
'0' AS qty_out_subkon,
'0' AS qty_retur_pembelian,
'0' AS qty_out_sample,
'0' AS qty_out_lainnya,
'0' AS qty_out_adj,
    CASE
        WHEN b.unit = 'YRD' THEN 'METER'
        ELSE b.unit
    END AS unit_konv
from bom_det a
left join whs_inmaterial_fabric_det b on a.id_item = b.id_item and a.id_jo = b.id_jo
left join whs_inmaterial_fabric c on b.no_dok = c.no_dok
left join whs_master_pilihan d on c.type_pch = d.nama_pilihan
where mattype = 'F'
group by c.tgl_dok, a.id_item, unit_konv
),
bpb_f_glb as (
select
a.id_item,
c.tgl_dok,
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
'0' AS qty_out_prod,
'0' AS qty_out_subkon,
'0' AS qty_retur_pembelian,
'0' AS qty_out_sample,
'0' AS qty_out_lainnya,
'0' AS qty_out_adj,
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
group by c.tgl_dok, a.id_item, unit_konv
),
bppb_f as (
select
a.id_item,
c.tgl_bppb tgl_dok,
'0' AS qty_pembelian,
'0' AS qty_retur_subkon,
'0' AS qty_retur_prod,
'0' AS qty_adj,
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
group by c.tgl_bppb, a.id_item, unit_konv
),
bppb_f_glb as (
select
a.id_item,
c.tgl_bppb tgl_dok,
'0' AS qty_pembelian,
'0' AS qty_retur_subkon,
'0' AS qty_retur_prod,
'0' AS qty_adj,
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
left join whs_bppb_det b on a.id_item = b.id_item and a.id_jo = b.id_jo
left join whs_bppb_h c on b.no_bppb = c.no_bppb
left join mastertransaksi d on c.jenis_pengeluaran = d.nama_trans
where mattype = 'F'
group by c.tgl_bppb, a.id_item, unit_konv
)

select
a.id_item,
mi.itemdesc,
tgl_dok,
DATE_FORMAT(tgl_dok, '%d - %b - %Y') AS tgl_dok_fix,
round(SUM(qty_pembelian),2) qty_pembelian,
round(SUM(qty_retur_subkon),2) qty_retur_subkon,
round(SUM(qty_retur_prod),2) qty_retur_prod,
round(SUM(qty_adj),2) qty_adj,
round(SUM(qty_out_prod),2) qty_out_prod,
round(SUM(qty_out_subkon),2) qty_out_subkon,
round(SUM(qty_retur_pembelian),2) qty_retur_pembelian,
round(SUM(qty_out_sample),2) qty_out_sample,
round(SUM(qty_out_lainnya),2) qty_out_lainnya,
round(SUM(qty_out_adj),2) qty_out_adj,
unit_konv
FROM
(
select * from bpb_f
union
select * from bpb_f_glb
union
select * from bppb_f_glb
union
select * from bppb_f
) a
inner join masteritem mi on a.id_item = mi.id_item
where tgl_dok is not null
group by tgl_dok, id_item
order by tgl_dok asc

        ");

        return DataTables::of($data_monitoring_order)->toJson();
    }
}
