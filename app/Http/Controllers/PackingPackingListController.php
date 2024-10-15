<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportLaporanPackingMasterkarton;
use App\Exports\ExportDataPoUpload;
use App\Imports\UploadPackingListKarton;
use App\Imports\UploadPackingListHeader;
use App\Exports\ExportDataTemplatePackingList;

class PackingPackingListController extends Controller
{
    public function index(Request $request)
    {
        $tgl_akhir_fix = date('Y-m-d', strtotime("+120 days"));
        $user = Auth::user()->name;
        $tgl_awal = $request->dateFrom;
        $tgl_akhir = $request->dateTo;
        $user = Auth::user()->name;
        if ($request->ajax()) {
            $additionalQuery = '';
            $data_carton = DB::select("SELECT
a.po,
b.ws,
b.buyer,
b.styleno,
b.product_group,
b.product_item,
b.qty_po,
concat((DATE_FORMAT(b.tgl_shipment,  '%d')), '-', left(DATE_FORMAT(b.tgl_shipment,  '%M'),3),'-',DATE_FORMAT(b.tgl_shipment,  '%Y')) tgl_shipment_fix,
tot_karton,
tot_karton_isi,
tot_karton_kosong,
coalesce(s.tot_scan,0) tot_scan
from
  (
select a.po,
count(a.no_carton)tot_karton,
count(IF(b.no_carton is not null,1,null)) tot_karton_isi,
count(IF(b.no_carton is null,1,null)) tot_karton_kosong
from  packing_master_carton a
left join (
select no_carton, po from packing_packing_out_scan group by no_carton,po  ) b on
a.po = b.po and  a.no_carton = b.no_carton
group by a.po
) a
left join
(
select
p.po,
m.ws,
m.styleno,
tgl_shipment,
m.buyer,
m.product_group,
m.product_item,
sum(qty_po) qty_po
from ppic_master_so p
inner join master_sb_ws m on p.id_so_det = m.id_so_det
group by po
) b on a.po = b.po
left join
(select po,count(barcode) tot_scan from packing_packing_out_scan group by po) s on a.po = s.po
where tgl_shipment >= '$tgl_awal' and tgl_shipment <= '$tgl_akhir'
 order by tgl_shipment asc, po asc
          ");

            return DataTables::of($data_carton)->toJson();
        }

        $data_po = DB::select("SELECT
po isi,
concat(po, ' - ', buyer, ' - ', styleno, ' - ', p.dest) tampil from ppic_master_so p
inner join master_sb_ws m on p.id_so_det = m.id_so_det
where tgl_shipment >= '2024-09-01'
group by po
order by buyer asc, styleno asc, po asc");


        return view(
            'packing.packing_packing_list',
            [
                'page' => 'dashboard-packing',
                "subPageGroup" => "packing-master-karton",
                "subPage" => "packing-list",
                "data_po" => $data_po,
                "user" => $user,
                "tgl_akhir_fix" => $tgl_akhir_fix,
            ]
        );
    }

    public function show_det_po(Request $request)
    {
        $data_header = DB::select("
        SELECT buyer,po,styleno,p.dest from ppic_master_so p
        inner join master_sb_ws m on p.id_so_det = m.id_so_det
        where po = '$request->cbopo'
        ");

        return json_encode($data_header ? $data_header[0] : null);
    }

    public function upload_packing_list(Request $request)
    {
        // validasi
        $po = $request->cbopo;
        $this->validate($request, [
            'file' => 'required|mimes:csv,xls,xlsx'
        ]);

        $file = $request->file('file');

        $nama_file = $file->getClientOriginalName();
        $nama_file_without_extension = substr($nama_file, 0, strrpos($nama_file, '.'));

        $file->move('file_upload', $nama_file);
        Excel::import(new UploadPackingListKarton, public_path('/file_upload/' . $nama_file));
        Excel::import(new UploadPackingListHeader($request->cbopo), public_path('/file_upload/' . $nama_file));
        return array(
            "status" => 201,
            "message" => 'Data Berhasil Di Upload',
            'table' => 'datatable_upload',
            "additional" => [],
            // "redirect" => url('in-material/upload-lokasi')
        );
    }

    public function delete_upload_packing_list(Request $request)
    {
        $user = Auth::user()->name;
        $po = $request->po;

        $delete =  DB::delete(
            "DELETE FROM packing_master_upload_packing_list_det_horizontal where po = '$po'"
        );

        $delete =  DB::delete(
            "DELETE FROM packing_master_upload_packing_list_header_horizontal where po = '$po'"
        );
    }



    public function show_datatable_upload_packing_list(Request $request)
    {

        $user = Auth::user()->name;
        $po = $request->po;
        if ($request->ajax()) {
            $additionalQuery = '';
            $data_upload = DB::select("SELECT
a.no_carton,
b.no_carton_awal,
b.no_carton_akhir,
b.no_carton_akhir - b.no_carton_awal + 1 total_ctn ,
po,
color,
id_ppic_master_so,
id_so_det,
tgl_shipment,
concat((DATE_FORMAT(tgl_shipment,  '%d')), '-', left(DATE_FORMAT(tgl_shipment,  '%M'),3),'-',DATE_FORMAT(tgl_shipment,  '%Y')
) tgl_shipment_fix,
buyer,
field_value size,
if (b.no_carton_akhir - b.no_carton_awal + 1 = '0',qty,qty/(b.no_carton_akhir - b.no_carton_awal + 1)) qty
from (
select * from dim_no_carton
where no_carton >= (select  min(no_carton_awal) from packing_master_upload_packing_list_det_horizontal where po = '$po')
and no_carton <= (select  max(no_carton_akhir) from packing_master_upload_packing_list_det_horizontal where po = '$po')
) a
join
(
select
a.no_carton_awal,
a.no_carton_akhir,
a.no_carton_akhir - a.no_carton_awal,
a.po,
a.color,
p.id id_ppic_master_so,
p.tgl_shipment,
p.id_so_det,
p.buyer,
h.field_value,
l.qty
from
(
SELECT 'field_1' AS field_name, field_1 AS field_value
FROM packing_master_upload_packing_list_header_horizontal a where po = '$po'
union
SELECT 'field_2' AS field_name, field_2 AS field_value
FROM packing_master_upload_packing_list_header_horizontal a where po = '$po'
union
SELECT 'field_3' AS field_name, field_3 AS field_value
FROM packing_master_upload_packing_list_header_horizontal a where po = '$po'
union
SELECT 'field_4' AS field_name, field_4 AS field_value
FROM packing_master_upload_packing_list_header_horizontal a where po = '$po'
union
SELECT 'field_5' AS field_name, field_5 AS field_value
FROM packing_master_upload_packing_list_header_horizontal a where po = '$po'
union
SELECT 'field_6' AS field_name, field_6 AS field_value
FROM packing_master_upload_packing_list_header_horizontal a where po = '$po'
union
SELECT 'field_7' AS field_name, field_7 AS field_value
FROM packing_master_upload_packing_list_header_horizontal a where po = '$po'
union
SELECT 'field_8' AS field_name, field_8 AS field_value
FROM packing_master_upload_packing_list_header_horizontal a where po = '$po'
union
SELECT 'field_9' AS field_name, field_9 AS field_value
FROM packing_master_upload_packing_list_header_horizontal a where po = '$po'
union
SELECT 'field_10' AS field_name, field_10 AS field_value
FROM packing_master_upload_packing_list_header_horizontal a where po = '$po'
) h
left join
(
SELECT id,'field_1' AS field_name, field_1 AS qty
FROM packing_master_upload_packing_list_det_horizontal a
where field_1 is not null and po = '$po'
union
SELECT id,'field_2' AS field_name, field_2 AS qty
FROM packing_master_upload_packing_list_det_horizontal a
where field_2 is not null and po = '$po'
union
SELECT id,'field_3' AS field_name, field_3 AS qty
FROM packing_master_upload_packing_list_det_horizontal a
where field_3 is not null and po = '$po'
union
SELECT id,'field_4' AS field_name, field_4 AS qty
FROM packing_master_upload_packing_list_det_horizontal a
where field_4 is not null and po = '$po'
union
SELECT id,'field_5' AS field_name, field_5 AS qty
FROM packing_master_upload_packing_list_det_horizontal a
where field_5 is not null and po = '$po'
union
SELECT id,'field_6' AS field_name, field_6 AS qty
FROM packing_master_upload_packing_list_det_horizontal a
where field_6 is not null and po = '$po'
union
SELECT id,'field_7' AS field_name, field_7 AS qty
FROM packing_master_upload_packing_list_det_horizontal a
where field_7 is not null and po = '$po'
union
SELECT id,'field_8' AS field_name, field_8 AS qty
FROM packing_master_upload_packing_list_det_horizontal a
where field_8 is not null and po = '$po'
union
SELECT id,'field_9' AS field_name, field_9 AS qty
FROM packing_master_upload_packing_list_det_horizontal a
where field_9 is not null and po = '$po'
union
SELECT id,'field_10' AS field_name, field_10 AS qty
FROM packing_master_upload_packing_list_det_horizontal a
where field_10 is not null and po = '$po'
) l on h.field_name = l.field_name
left join packing_master_upload_packing_list_det_horizontal a on l.id = a.id
left join
(
select p.id, color, size, p.id_so_det, po, tgl_shipment, buyer from ppic_master_so p
inner join master_sb_ws m on p.id_so_det = m.id_so_det
where po = '$po'
) p on a.po = p.po and a.color = p.color and h.field_value = p.size
where a.id is not null
order by a.no_carton_awal asc
)
b on a.no_carton >= b.no_carton_awal and a.no_carton <= b.no_carton_akhir
          ");

            return DataTables::of($data_upload)->toJson();
        }
    }


    public function export_data_template_po_packing_list(Request $request)
    {
        return Excel::download(new ExportDataTemplatePackingList($request->po), 'Laporan_Hasil_Scan.xlsx');
    }
}
