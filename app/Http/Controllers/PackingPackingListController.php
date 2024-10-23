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
use App\Imports\UploadPackingListKartonVertical;
use App\Exports\ExportDataTemplatePackingListHorizontal;
use App\Exports\ExportDataTemplatePackingListVertical;
use Illuminate\Support\Str;

class PackingPackingListController extends Controller
{
    public function index(Request $request)
    {
        $tgl_akhir_fix = date('Y-m-d', strtotime("+90 days"));
        $tgl_awal_fix = date('Y-m-d', strtotime("-90 days"));
        $user = Auth::user()->name;
        $tgl_awal = $request->dateFrom;
        $tgl_akhir = $request->dateTo;
        $user = Auth::user()->name;
        if ($request->ajax()) {
            $additionalQuery = '';
            $data_pl = DB::select("SELECT
a.po,
count(DISTINCT(no_carton)) tot_carton,
a.dest,
m.buyer,
m.styleno,
p.tgl_shipment,
concat((DATE_FORMAT(p.tgl_shipment,  '%d')), '-', left(DATE_FORMAT(p.tgl_shipment,  '%M'),3),'-',DATE_FORMAT(p.tgl_shipment,  '%Y')) tgl_shipment_fix,
sum(a.qty) tot_qty
from packing_master_packing_list a
left join ppic_master_so p on a.id_ppic_master_so = p.id
inner join master_sb_ws m on p.id_so_det = m.id_so_det
where tgl_shipment >= '$tgl_awal' and tgl_shipment <= '$tgl_akhir'
group by po
order by tgl_shipment asc, po asc
          ");

            return DataTables::of($data_pl)->toJson();
        }

        $data_po = DB::select("SELECT
concat(p.po,'_',p.dest)isi,
concat(p.po, ' - ', buyer, ' - ', styleno, ' - ', p.dest) tampil from ppic_master_so p
inner join master_sb_ws m on p.id_so_det = m.id_so_det
left join (select po from packing_master_packing_list group by po) a on p.po = a.po
where tgl_shipment >= '2024-09-01' and a.po is null
group by p.po,p.dest
order by buyer asc, styleno asc, p.po asc");

        $data_list = DB::select("select 'HORIZONTAL' isi,'HORIZONTAL' tampil
union
select 'VERTICAL' isi,'VERTICAL' tampil ");


        return view(
            'packing.packing_packing_list',
            [
                'page' => 'dashboard-packing',
                "subPageGroup" => "packing-master-karton",
                "subPage" => "packing-list",
                "data_po" => $data_po,
                "data_list" => $data_list,
                "user" => $user,
                "tgl_awal_fix" => $tgl_awal_fix,
                "tgl_akhir_fix" => $tgl_akhir_fix,
            ]
        );
    }

    public function show_det_po(Request $request)
    {
        $data_header = DB::select("
        SELECT buyer,po,styleno,p.dest from ppic_master_so p
        inner join master_sb_ws m on p.id_so_det = m.id_so_det
        where po = '$request->cbopo' and p.dest = '$request->dest' limit 1
        ");

        return json_encode($data_header ? $data_header[0] : null);
    }

    public function upload_packing_list(Request $request)
    {
        // validasi
        $po = $request->cbopo;
        $tipe = $request->cbotipe;
        $this->validate($request, [
            'file' => 'required|mimes:csv,xls,xlsx'
        ]);

        if ($tipe == 'HORIZONTAL') {
            $file = $request->file('file');

            $nama_file = $file->getClientOriginalName();
            $nama_file_without_extension = substr($nama_file, 0, strrpos($nama_file, '.'));
            $splitData = explode("_", $nama_file_without_extension);
            $dest = $splitData[1];
            $po = $splitData[0];
            $ponew = str_replace("/", "_", $po);
            $txtpo = $request->txtpo;

            $parts = explode('_', $nama_file_without_extension);
            $lastPart = end($parts);
            $ceklist = $lastPart[0];

            if ($ceklist == 'H') {
                if (str_contains($nama_file_without_extension, $ponew)) {
                    $file->move('file_upload', $nama_file);
                    Excel::import(new UploadPackingListKarton, public_path('/file_upload/' . $nama_file));
                    Excel::import(new UploadPackingListHeader($txtpo, $dest), public_path('/file_upload/' . $nama_file));
                    return array(
                        "status" => 201,
                        "message" => 'Data Berhasil Di Upload',
                        'table' => 'datatable_upload',
                        "additional" => [],
                        // "redirect" => url('in-material/upload-lokasi')
                    );
                } else {
                    return array(
                        "status" => 202,
                        "message" => 'Data Gagal Di Upload',
                        'table' => 'datatable_upload',
                        "additional" => [],
                        // "redirect" => url('in-material/upload-lokasi')
                    );
                }
            } else { {
                    return array(
                        "status" => 202,
                        "message" => 'Data Gagal Di Upload Cek Tipe',
                        'table' => 'datatable_upload',
                        "additional" => [],
                        // "redirect" => url('in-material/upload-lokasi')
                    );
                }
            }
        } else if ($tipe == 'VERTICAL') {
            $file = $request->file('file');

            $nama_file = $file->getClientOriginalName();
            $nama_file_without_extension = substr($nama_file, 0, strrpos($nama_file, '.'));
            $ponew = str_replace("/", "_", $po);
            $ceklist = substr($nama_file_without_extension, -1);

            $parts = explode('_', $nama_file_without_extension);
            $lastPart = end($parts);
            $ceklist = $lastPart[0];

            if ($ceklist == 'V') {
                if (str_contains($nama_file_without_extension, $ponew)) {
                    $file->move('file_upload', $nama_file);
                    Excel::import(new UploadPackingListKartonVertical, public_path('/file_upload/' . $nama_file));
                    return array(
                        "status" => 201,
                        "message" => 'Data Berhasil Di Upload',
                        'table' => 'datatable_upload',
                        "additional" => [],
                        // "redirect" => url('in-material/upload-lokasi')
                    );
                } else {
                    return array(
                        "status" => 202,
                        "message" => 'Data Gagal Di Upload',
                        'table' => 'datatable_upload',
                        "additional" => [],
                        // "redirect" => url('in-material/upload-lokasi')
                    );
                }
            }
        } else { {
                return array(
                    "status" => 202,
                    "message" => 'Data Gagal Di Upload Cek Tipe',
                    'table' => 'datatable_upload',
                    "additional" => [],
                    // "redirect" => url('in-material/upload-lokasi')
                );
            }
        }
    }

    public function delete_upload_packing_list(Request $request)
    {
        $user = Auth::user()->name;
        $datapo = $request->po;

        if (!empty($datapo)) {
            $splitData = explode("_", $datapo);
            $po = $splitData[0];
            $dest = $splitData[1];

            $delete =  DB::delete(
                "DELETE FROM packing_master_upload_packing_list_det_horizontal where po = '$po' and dest = '$dest' and created_by = '$user' "
            );
            // dd("DELETE FROM packing_master_upload_packing_list_det_horizontal where po = '$po' and dest = '$dest' and created_by = '$user' ");

            $delete =  DB::delete(
                "DELETE FROM packing_master_upload_packing_list_header_horizontal where po = '$po' and dest = '$dest' and created_by = '$user'"
            );


            $delete =  DB::delete(
                "DELETE FROM packing_master_upload_packing_list_det_vertical where po = '$po' and dest = '$dest' and created_by = '$user'"
            );
        } else {
            $po = null;
            $dest = null;
        }
    }



    public function show_datatable_upload_packing_list(Request $request)
    {

        $user = Auth::user()->name;
        $po = $request->po;
        $dest = $request->dest;
        $tipe = $request->tipe;
        if ($request->ajax() && $tipe == 'HORIZONTAL') {
            $data_upload = DB::select("SELECT
    a.no_carton,
    b.no_carton_awal,
    b.no_carton_akhir,
    b.no_carton_akhir - b.no_carton_awal + 1 total_ctn ,
    po,
    color,
    tipe_pack,
    id_ppic_master_so,
    barcode,
    id_so_det,
    tgl_shipment,
    concat((DATE_FORMAT(tgl_shipment,  '%d')), '-', left(DATE_FORMAT(tgl_shipment,  '%M'),3),'-',DATE_FORMAT(tgl_shipment,  '%Y')
    ) tgl_shipment_fix,
    buyer,
    field_value size,
    if (tipe_pack = 'RATIO',qty/(b.no_carton_akhir - b.no_carton_awal + 1), qty) qty
    from (
    select * from dim_no_carton
    where no_carton >= (select  min(no_carton_awal) from packing_master_upload_packing_list_det_horizontal where po = '$po' and dest = '$dest')
    and no_carton <= (select  max(no_carton_akhir) from packing_master_upload_packing_list_det_horizontal where po = '$po' and dest = '$dest')
    ) a
    join
    (
    select
    a.no_carton_awal,
    a.no_carton_akhir,
    a.no_carton_akhir - a.no_carton_awal,
    a.po,
    a.color,
    a.tipe_pack,
    p.id id_ppic_master_so,
    p.tgl_shipment,
    p.id_so_det,
    p.buyer,
    p.barcode,
    h.field_value,
    l.qty
    from
    (
    SELECT 'field_1' AS field_name, field_1 AS field_value
    FROM packing_master_upload_packing_list_header_horizontal a where po = '$po' and dest = '$dest'
    union
    SELECT 'field_2' AS field_name, field_2 AS field_value
    FROM packing_master_upload_packing_list_header_horizontal a where po = '$po' and dest = '$dest'
    union
    SELECT 'field_3' AS field_name, field_3 AS field_value
    FROM packing_master_upload_packing_list_header_horizontal a where po = '$po' and dest = '$dest'
    union
    SELECT 'field_4' AS field_name, field_4 AS field_value
    FROM packing_master_upload_packing_list_header_horizontal a where po = '$po' and dest = '$dest'
    union
    SELECT 'field_5' AS field_name, field_5 AS field_value
    FROM packing_master_upload_packing_list_header_horizontal a where po = '$po' and dest = '$dest'
    union
    SELECT 'field_6' AS field_name, field_6 AS field_value
    FROM packing_master_upload_packing_list_header_horizontal a where po = '$po' and dest = '$dest'
    union
    SELECT 'field_7' AS field_name, field_7 AS field_value
    FROM packing_master_upload_packing_list_header_horizontal a where po = '$po' and dest = '$dest'
    union
    SELECT 'field_8' AS field_name, field_8 AS field_value
    FROM packing_master_upload_packing_list_header_horizontal a where po = '$po' and dest = '$dest'
    union
    SELECT 'field_9' AS field_name, field_9 AS field_value
    FROM packing_master_upload_packing_list_header_horizontal a where po = '$po' and dest = '$dest'
    union
    SELECT 'field_10' AS field_name, field_10 AS field_value
    FROM packing_master_upload_packing_list_header_horizontal a where po = '$po' and dest = '$dest'
    union
    SELECT 'field_11' AS field_name, field_11 AS field_value
    FROM packing_master_upload_packing_list_header_horizontal a where po = '$po' and dest = '$dest'
    union
    SELECT 'field_12' AS field_name, field_12 AS field_value
    FROM packing_master_upload_packing_list_header_horizontal a where po = '$po' and dest = '$dest'
    union
    SELECT 'field_13' AS field_name, field_13 AS field_value
    FROM packing_master_upload_packing_list_header_horizontal a where po = '$po' and dest = '$dest'
    union
    SELECT 'field_14' AS field_name, field_14 AS field_value
    FROM packing_master_upload_packing_list_header_horizontal a where po = '$po' and dest = '$dest'
    union
    SELECT 'field_15' AS field_name, field_15 AS field_value
    FROM packing_master_upload_packing_list_header_horizontal a where po = '$po' and dest = '$dest'
	union
    SELECT 'field_16' AS field_name, field_16 AS field_value
    FROM packing_master_upload_packing_list_header_horizontal a where po = '$po' and dest = '$dest'
	union
    SELECT 'field_17' AS field_name, field_17 AS field_value
    FROM packing_master_upload_packing_list_header_horizontal a where po = '$po' and dest = '$dest'
	union
    SELECT 'field_18' AS field_name, field_18 AS field_value
    FROM packing_master_upload_packing_list_header_horizontal a where po = '$po' and dest = '$dest'
	union
    SELECT 'field_19' AS field_name, field_19 AS field_value
    FROM packing_master_upload_packing_list_header_horizontal a where po = '$po' and dest = '$dest'
	union
    SELECT 'field_20' AS field_name, field_20 AS field_value
    FROM packing_master_upload_packing_list_header_horizontal a where po = '$po' and dest = '$dest'
	union
    SELECT 'field_21' AS field_name, field_21 AS field_value
    FROM packing_master_upload_packing_list_header_horizontal a where po = '$po' and dest = '$dest'
	union
    SELECT 'field_22' AS field_name, field_22 AS field_value
    FROM packing_master_upload_packing_list_header_horizontal a where po = '$po' and dest = '$dest'
	union
    SELECT 'field_23' AS field_name, field_23 AS field_value
    FROM packing_master_upload_packing_list_header_horizontal a where po = '$po' and dest = '$dest'
	union
    SELECT 'field_24' AS field_name, field_24 AS field_value
    FROM packing_master_upload_packing_list_header_horizontal a where po = '$po' and dest = '$dest'
	union
    SELECT 'field_25' AS field_name, field_25 AS field_value
    FROM packing_master_upload_packing_list_header_horizontal a where po = '$po' and dest = '$dest'
    ) h
    left join
    (
    SELECT id,'field_1' AS field_name, field_1 AS qty
    FROM packing_master_upload_packing_list_det_horizontal a
    where field_1 is not null and po = '$po' and dest = '$dest'
    union
    SELECT id,'field_2' AS field_name, field_2 AS qty
    FROM packing_master_upload_packing_list_det_horizontal a
    where field_2 is not null and po = '$po' and dest = '$dest'
    union
    SELECT id,'field_3' AS field_name, field_3 AS qty
    FROM packing_master_upload_packing_list_det_horizontal a
    where field_3 is not null and po = '$po' and dest = '$dest'
    union
    SELECT id,'field_4' AS field_name, field_4 AS qty
    FROM packing_master_upload_packing_list_det_horizontal a
    where field_4 is not null and po = '$po' and dest = '$dest'
    union
    SELECT id,'field_5' AS field_name, field_5 AS qty
    FROM packing_master_upload_packing_list_det_horizontal a
    where field_5 is not null and po = '$po' and dest = '$dest'
    union
    SELECT id,'field_6' AS field_name, field_6 AS qty
    FROM packing_master_upload_packing_list_det_horizontal a
    where field_6 is not null and po = '$po' and dest = '$dest'
    union
    SELECT id,'field_7' AS field_name, field_7 AS qty
    FROM packing_master_upload_packing_list_det_horizontal a
    where field_7 is not null and po = '$po' and dest = '$dest'
    union
    SELECT id,'field_8' AS field_name, field_8 AS qty
    FROM packing_master_upload_packing_list_det_horizontal a
    where field_8 is not null and po = '$po' and dest = '$dest'
    union
    SELECT id,'field_9' AS field_name, field_9 AS qty
    FROM packing_master_upload_packing_list_det_horizontal a
    where field_9 is not null and po = '$po' and dest = '$dest'
    union
    SELECT id,'field_10' AS field_name, field_10 AS qty
    FROM packing_master_upload_packing_list_det_horizontal a
    where field_10 is not null and po = '$po' and dest = '$dest'
    union
    SELECT id,'field_11' AS field_name, field_11 AS qty
    FROM packing_master_upload_packing_list_det_horizontal a
    where field_11 is not null and po = '$po' and dest = '$dest'
    union
    SELECT id,'field_12' AS field_name, field_12 AS qty
    FROM packing_master_upload_packing_list_det_horizontal a
    where field_12 is not null and po = '$po' and dest = '$dest'
    union
    SELECT id,'field_13' AS field_name, field_13 AS qty
    FROM packing_master_upload_packing_list_det_horizontal a
    where field_13 is not null and po = '$po' and dest = '$dest'
    union
    SELECT id,'field_14' AS field_name, field_14 AS qty
    FROM packing_master_upload_packing_list_det_horizontal a
    where field_14 is not null and po = '$po' and dest = '$dest'
    union
    SELECT id,'field_15' AS field_name, field_15 AS qty
    FROM packing_master_upload_packing_list_det_horizontal a
    where field_15 is not null and po = '$po' and dest = '$dest'
    union
    SELECT id,'field_16' AS field_name, field_17 AS qty
    FROM packing_master_upload_packing_list_det_horizontal a
    where field_16 is not null and po = '$po' and dest = '$dest'
    union
    SELECT id,'field_17' AS field_name, field_17 AS qty
    FROM packing_master_upload_packing_list_det_horizontal a
    where field_17 is not null and po = '$po' and dest = '$dest'
    union
    SELECT id,'field_18' AS field_name, field_18 AS qty
    FROM packing_master_upload_packing_list_det_horizontal a
    where field_18 is not null and po = '$po' and dest = '$dest'
    union
    SELECT id,'field_19' AS field_name, field_19 AS qty
    FROM packing_master_upload_packing_list_det_horizontal a
    where field_19 is not null and po = '$po' and dest = '$dest'
    union
    SELECT id,'field_20' AS field_name, field_20 AS qty
    FROM packing_master_upload_packing_list_det_horizontal a
    where field_20 is not null and po = '$po' and dest = '$dest'
    union
    SELECT id,'field_21' AS field_name, field_21 AS qty
    FROM packing_master_upload_packing_list_det_horizontal a
    where field_21 is not null and po = '$po' and dest = '$dest'
    union
    SELECT id,'field_22' AS field_name, field_22 AS qty
    FROM packing_master_upload_packing_list_det_horizontal a
    where field_22 is not null and po = '$po' and dest = '$dest'
    union
    SELECT id,'field_23' AS field_name, field_23 AS qty
    FROM packing_master_upload_packing_list_det_horizontal a
    where field_23 is not null and po = '$po' and dest = '$dest'
    union
    SELECT id,'field_24' AS field_name, field_24 AS qty
    FROM packing_master_upload_packing_list_det_horizontal a
    where field_24 is not null and po = '$po' and dest = '$dest'
    union
    SELECT id,'field_25' AS field_name, field_25 AS qty
    FROM packing_master_upload_packing_list_det_horizontal a
    where field_25 is not null and po = '$po' and dest = '$dest'
    ) l on h.field_name = l.field_name
    left join packing_master_upload_packing_list_det_horizontal a on l.id = a.id
    left join
    (
    select p.id, barcode, color, size, p.id_so_det, po, tgl_shipment, buyer, p.dest from ppic_master_so p
    inner join master_sb_ws m on p.id_so_det = m.id_so_det
    where po = '$po' and p.dest = '$dest'
    ) p on a.po = p.po and a.dest = p.dest and a.color = p.color and h.field_value = p.size
    where a.id is not null
    order by a.no_carton_awal asc
    )
    b on a.no_carton >= b.no_carton_awal and a.no_carton <= b.no_carton_akhir
              ");

            return DataTables::of($data_upload)->toJson();
        } else if ($request->ajax() && $tipe == 'VERTICAL') {
            $data_upload = DB::select("SELECT
            a.no_carton,
            b.no_carton_awal,
            b.no_carton_akhir,
            b.no_carton_akhir - b.no_carton_awal + 1 total_ctn ,
            po,
            color,
            tipe_pack,
            id_ppic_master_so,
            barcode,
            id_so_det,
            tgl_shipment,
            concat((DATE_FORMAT(tgl_shipment,  '%d')), '-', left(DATE_FORMAT(tgl_shipment,  '%M'),3),'-',DATE_FORMAT(tgl_shipment,  '%Y')
            ) tgl_shipment_fix,
            buyer,
            size,
            IF(tipe_pack = 'RATIO',CAST(qty / (b.no_carton_akhir - b.no_carton_awal + 1) AS UNSIGNED), qty) AS qty
            from
            (
            select * from dim_no_carton
            where no_carton >= (select  min(no_carton_awal) from packing_master_upload_packing_list_det_vertical where po = '$po' and dest = '$dest')
            and no_carton <= (select  max(no_carton_akhir) from packing_master_upload_packing_list_det_vertical where po = '$po'  and dest = '$dest')
            ) a
            join
            (
            select
            a.no_carton_awal,
            a.no_carton_akhir,
            a.po,
            a.color,
            a.tipe_pack,
            p.id id_ppic_master_so,
            p.tgl_shipment,
            p.id_so_det,
            p.buyer,
            p.barcode,
            a.size,
            a.qty
            from packing_master_upload_packing_list_det_vertical a
            left join (
            select p.id, barcode, color, size, p.id_so_det, po, tgl_shipment, buyer, p.dest from ppic_master_so p
            inner join master_sb_ws m on p.id_so_det = m.id_so_det
            where po = '$po' and p.dest = '$dest'
            ) p on  a.po = p.po and a.dest = p.dest and a.color = p.color and a.size = p.size
            where a.id is not null
            )
            b on a.no_carton >= b.no_carton_awal and a.no_carton <= b.no_carton_akhir
                                      ");

            return DataTables::of($data_upload)->toJson();
        }
        // Check if data is returned

        if (empty($data_upload)) {
            return DataTables::of([])->toJson();
        }
    }


    public function store(Request $request)
    {
        $user = Auth::user()->name;
        $timestamp = Carbon::now();
        // $po = $request->po;
        $tipe = $request->tipe;
        $txtnon_upload = $request->txtnon_upload;
        $po = $request->txtpo;
        $dest = $request->txtdest;

        if ($txtnon_upload == '0') {

            if ($tipe == 'HORIZONTAL') {
                $insert = DB::insert(
                    "INSERT INTO packing_master_packing_list
        (
        po,
        dest,
        no_carton,
        no_carton_awal,
        no_carton_akhir,
        tot_ctn,
        tipe_pack,
        color,
        id_ppic_master_so,
        barcode,
        id_so_det,
        size,
        qty,
        upload_tipe,
        created_at,
        updated_at,
        created_by
        )
    SELECT
    po,
    dest,
    a.no_carton,
    b.no_carton_awal,
    b.no_carton_akhir,
    b.no_carton_akhir - b.no_carton_awal + 1 total_ctn ,
    tipe_pack,
    color,
    id_ppic_master_so,
    barcode,
    id_so_det,
    field_value size,
    if (tipe_pack = 'RATIO',qty/(b.no_carton_akhir - b.no_carton_awal + 1), qty) qty,
    'HORIZONTAL',
    '$timestamp',
    '$timestamp',
    '$user'
    from (
    select * from dim_no_carton
    where no_carton >= (select  min(no_carton_awal) from packing_master_upload_packing_list_det_horizontal where po = '$po' and dest = '$dest')
    and no_carton <= (select  max(no_carton_akhir) from packing_master_upload_packing_list_det_horizontal where po = '$po' and dest = '$dest')
    ) a
    join
    (
    select
    a.no_carton_awal,
    a.no_carton_akhir,
    a.no_carton_akhir - a.no_carton_awal,
    a.po,
    a.dest,
    a.color,
    a.tipe_pack,
    p.id id_ppic_master_so,
    p.tgl_shipment,
    p.id_so_det,
    p.buyer,
    p.barcode,
    h.field_value,
    l.qty
    from
    (
    SELECT 'field_1' AS field_name, field_1 AS field_value
    FROM packing_master_upload_packing_list_header_horizontal a where po = '$po' and dest = '$dest'
    union
    SELECT 'field_2' AS field_name, field_2 AS field_value
    FROM packing_master_upload_packing_list_header_horizontal a where po = '$po' and dest = '$dest'
    union
    SELECT 'field_3' AS field_name, field_3 AS field_value
    FROM packing_master_upload_packing_list_header_horizontal a where po = '$po' and dest = '$dest'
    union
    SELECT 'field_4' AS field_name, field_4 AS field_value
    FROM packing_master_upload_packing_list_header_horizontal a where po = '$po' and dest = '$dest'
    union
    SELECT 'field_5' AS field_name, field_5 AS field_value
    FROM packing_master_upload_packing_list_header_horizontal a where po = '$po' and dest = '$dest'
    union
    SELECT 'field_6' AS field_name, field_6 AS field_value
    FROM packing_master_upload_packing_list_header_horizontal a where po = '$po' and dest = '$dest'
    union
    SELECT 'field_7' AS field_name, field_7 AS field_value
    FROM packing_master_upload_packing_list_header_horizontal a where po = '$po' and dest = '$dest'
    union
    SELECT 'field_8' AS field_name, field_8 AS field_value
    FROM packing_master_upload_packing_list_header_horizontal a where po = '$po' and dest = '$dest'
    union
    SELECT 'field_9' AS field_name, field_9 AS field_value
    FROM packing_master_upload_packing_list_header_horizontal a where po = '$po' and dest = '$dest'
    union
    SELECT 'field_10' AS field_name, field_10 AS field_value
    FROM packing_master_upload_packing_list_header_horizontal a where po = '$po' and dest = '$dest'
    union
    SELECT 'field_11' AS field_name, field_11 AS field_value
    FROM packing_master_upload_packing_list_header_horizontal a where po = '$po' and dest = '$dest'
    union
    SELECT 'field_12' AS field_name, field_12 AS field_value
    FROM packing_master_upload_packing_list_header_horizontal a where po = '$po' and dest = '$dest'
    union
    SELECT 'field_13' AS field_name, field_13 AS field_value
    FROM packing_master_upload_packing_list_header_horizontal a where po = '$po' and dest = '$dest'
    union
    SELECT 'field_14' AS field_name, field_14 AS field_value
    FROM packing_master_upload_packing_list_header_horizontal a where po = '$po' and dest = '$dest'
    union
    SELECT 'field_15' AS field_name, field_15 AS field_value
    FROM packing_master_upload_packing_list_header_horizontal a where po = '$po' and dest = '$dest'
	union
    SELECT 'field_16' AS field_name, field_16 AS field_value
    FROM packing_master_upload_packing_list_header_horizontal a where po = '$po' and dest = '$dest'
	union
    SELECT 'field_17' AS field_name, field_17 AS field_value
    FROM packing_master_upload_packing_list_header_horizontal a where po = '$po' and dest = '$dest'
	union
    SELECT 'field_18' AS field_name, field_18 AS field_value
    FROM packing_master_upload_packing_list_header_horizontal a where po = '$po' and dest = '$dest'
	union
    SELECT 'field_19' AS field_name, field_19 AS field_value
    FROM packing_master_upload_packing_list_header_horizontal a where po = '$po' and dest = '$dest'
	union
    SELECT 'field_20' AS field_name, field_20 AS field_value
    FROM packing_master_upload_packing_list_header_horizontal a where po = '$po' and dest = '$dest'
	union
    SELECT 'field_21' AS field_name, field_21 AS field_value
    FROM packing_master_upload_packing_list_header_horizontal a where po = '$po' and dest = '$dest'
	union
    SELECT 'field_22' AS field_name, field_22 AS field_value
    FROM packing_master_upload_packing_list_header_horizontal a where po = '$po' and dest = '$dest'
	union
    SELECT 'field_23' AS field_name, field_23 AS field_value
    FROM packing_master_upload_packing_list_header_horizontal a where po = '$po' and dest = '$dest'
	union
    SELECT 'field_24' AS field_name, field_24 AS field_value
    FROM packing_master_upload_packing_list_header_horizontal a where po = '$po' and dest = '$dest'
	union
    SELECT 'field_25' AS field_name, field_25 AS field_value
    FROM packing_master_upload_packing_list_header_horizontal a where po = '$po' and dest = '$dest'
    ) h
    left join
    (
    SELECT id,'field_1' AS field_name, field_1 AS qty
    FROM packing_master_upload_packing_list_det_horizontal a
    where field_1 is not null and po = '$po' and dest = '$dest'
    union
    SELECT id,'field_2' AS field_name, field_2 AS qty
    FROM packing_master_upload_packing_list_det_horizontal a
    where field_2 is not null and po = '$po' and dest = '$dest'
    union
    SELECT id,'field_3' AS field_name, field_3 AS qty
    FROM packing_master_upload_packing_list_det_horizontal a
    where field_3 is not null and po = '$po' and dest = '$dest'
    union
    SELECT id,'field_4' AS field_name, field_4 AS qty
    FROM packing_master_upload_packing_list_det_horizontal a
    where field_4 is not null and po = '$po' and dest = '$dest'
    union
    SELECT id,'field_5' AS field_name, field_5 AS qty
    FROM packing_master_upload_packing_list_det_horizontal a
    where field_5 is not null and po = '$po' and dest = '$dest'
    union
    SELECT id,'field_6' AS field_name, field_6 AS qty
    FROM packing_master_upload_packing_list_det_horizontal a
    where field_6 is not null and po = '$po' and dest = '$dest'
    union
    SELECT id,'field_7' AS field_name, field_7 AS qty
    FROM packing_master_upload_packing_list_det_horizontal a
    where field_7 is not null and po = '$po' and dest = '$dest'
    union
    SELECT id,'field_8' AS field_name, field_8 AS qty
    FROM packing_master_upload_packing_list_det_horizontal a
    where field_8 is not null and po = '$po' and dest = '$dest'
    union
    SELECT id,'field_9' AS field_name, field_9 AS qty
    FROM packing_master_upload_packing_list_det_horizontal a
    where field_9 is not null and po = '$po' and dest = '$dest'
    union
    SELECT id,'field_10' AS field_name, field_10 AS qty
    FROM packing_master_upload_packing_list_det_horizontal a
    where field_10 is not null and po = '$po' and dest = '$dest'
    union
    SELECT id,'field_11' AS field_name, field_11 AS qty
    FROM packing_master_upload_packing_list_det_horizontal a
    where field_11 is not null and po = '$po' and dest = '$dest'
    union
    SELECT id,'field_12' AS field_name, field_12 AS qty
    FROM packing_master_upload_packing_list_det_horizontal a
    where field_12 is not null and po = '$po' and dest = '$dest'
    union
    SELECT id,'field_13' AS field_name, field_13 AS qty
    FROM packing_master_upload_packing_list_det_horizontal a
    where field_13 is not null and po = '$po' and dest = '$dest'
    union
    SELECT id,'field_14' AS field_name, field_14 AS qty
    FROM packing_master_upload_packing_list_det_horizontal a
    where field_14 is not null and po = '$po' and dest = '$dest'
    union
    SELECT id,'field_15' AS field_name, field_15 AS qty
    FROM packing_master_upload_packing_list_det_horizontal a
    where field_15 is not null and po = '$po' and dest = '$dest'
    union
    SELECT id,'field_16' AS field_name, field_17 AS qty
    FROM packing_master_upload_packing_list_det_horizontal a
    where field_16 is not null and po = '$po' and dest = '$dest'
    union
    SELECT id,'field_17' AS field_name, field_17 AS qty
    FROM packing_master_upload_packing_list_det_horizontal a
    where field_17 is not null and po = '$po' and dest = '$dest'
    union
    SELECT id,'field_18' AS field_name, field_18 AS qty
    FROM packing_master_upload_packing_list_det_horizontal a
    where field_18 is not null and po = '$po' and dest = '$dest'
    union
    SELECT id,'field_19' AS field_name, field_19 AS qty
    FROM packing_master_upload_packing_list_det_horizontal a
    where field_19 is not null and po = '$po' and dest = '$dest'
    union
    SELECT id,'field_20' AS field_name, field_20 AS qty
    FROM packing_master_upload_packing_list_det_horizontal a
    where field_20 is not null and po = '$po' and dest = '$dest'
    union
    SELECT id,'field_21' AS field_name, field_21 AS qty
    FROM packing_master_upload_packing_list_det_horizontal a
    where field_21 is not null and po = '$po' and dest = '$dest'
    union
    SELECT id,'field_22' AS field_name, field_22 AS qty
    FROM packing_master_upload_packing_list_det_horizontal a
    where field_22 is not null and po = '$po' and dest = '$dest'
    union
    SELECT id,'field_23' AS field_name, field_23 AS qty
    FROM packing_master_upload_packing_list_det_horizontal a
    where field_23 is not null and po = '$po' and dest = '$dest'
    union
    SELECT id,'field_24' AS field_name, field_24 AS qty
    FROM packing_master_upload_packing_list_det_horizontal a
    where field_24 is not null and po = '$po' and dest = '$dest'
    union
    SELECT id,'field_25' AS field_name, field_25 AS qty
    FROM packing_master_upload_packing_list_det_horizontal a
    where field_25 is not null and po = '$po' and dest = '$dest'
    ) l on h.field_name = l.field_name
    left join packing_master_upload_packing_list_det_horizontal a on l.id = a.id
    left join
    (
    select p.id, barcode, color, size, p.id_so_det, po, tgl_shipment, buyer, p.dest from ppic_master_so p
    inner join master_sb_ws m on p.id_so_det = m.id_so_det
    where po = '$po' and p.dest = '$dest'
    ) p on a.po = p.po and a.dest = p.dest and a.color = p.color and h.field_value = p.size
    where a.id is not null
    order by a.no_carton_awal asc
    )
    b on a.no_carton >= b.no_carton_awal and a.no_carton <= b.no_carton_akhir
        "
                );
            } else if ($tipe == 'VERTICAL') {
                $insert = DB::insert(
                    "INSERT INTO packing_master_packing_list
        (
        po,
        dest,
        no_carton,
        no_carton_awal,
        no_carton_akhir,
        tot_ctn,
        tipe_pack,
        color,
        id_ppic_master_so,
        barcode,
        id_so_det,
        size,
        qty,
        upload_tipe,
        created_at,
        updated_at,
        created_by
        )
    SELECT
    po,
    dest,
    a.no_carton,
    b.no_carton_awal,
    b.no_carton_akhir,
    b.no_carton_akhir - b.no_carton_awal + 1 total_ctn ,
    tipe_pack,
    color,
    id_ppic_master_so,
    barcode,
    id_so_det,
    size,
    if (tipe_pack = 'RATIO',qty/(b.no_carton_akhir - b.no_carton_awal + 1), qty) qty,
    'VERTICAL',
    '$timestamp',
    '$timestamp',
    '$user'
    from (
            select * from dim_no_carton
            where no_carton >= (select  min(no_carton_awal) from packing_master_upload_packing_list_det_vertical where po = '$po' and dest = '$dest')
            and no_carton <= (select  max(no_carton_akhir) from packing_master_upload_packing_list_det_vertical where po = '$po'  and dest = '$dest')
            ) a
            join
            (
            select
            a.no_carton_awal,
            a.no_carton_akhir,
            a.po,
            a.dest,
            a.color,
            a.tipe_pack,
            p.id id_ppic_master_so,
            p.tgl_shipment,
            p.id_so_det,
            p.buyer,
            p.barcode,
            a.size,
            a.qty
            from packing_master_upload_packing_list_det_vertical a
            left join (
            select p.id, barcode, color, size, p.id_so_det, po, tgl_shipment, buyer, p.dest from ppic_master_so p
            inner join master_sb_ws m on p.id_so_det = m.id_so_det
            where po = '$po' and p.dest = '$dest'
            ) p on  a.po = p.po and a.dest = p.dest and a.color = p.color and a.size = p.size
            where a.id is not null
            )
            b on a.no_carton >= b.no_carton_awal and a.no_carton <= b.no_carton_akhir
        "
                );
            }

            if ($insert) {
                return array(
                    'icon' => 'benar',
                    'msg' => 'Transaksi Sudah Terbuat',
                );
            }
        } else {
            return array(
                'icon' => 'salah',
                'msg' => 'Tidak ada yang disimpan, Periksa Data Lagi',
            );
        }
    }



    public function export_data_template_po_packing_list_horizontal(Request $request)
    {
        return Excel::download(new ExportDataTemplatePackingListHorizontal($request->po, $request->dest), 'Laporan_Hasil_Scan.xlsx');
    }

    public function export_data_template_po_packing_list_vertical(Request $request)
    {
        return Excel::download(new ExportDataTemplatePackingListVertical($request->po, $request->dest), 'Laporan_Hasil_Scan.xlsx');
    }

    public function getPoData()

    {
        $data_po = DB::select("SELECT
concat(p.po,'_',p.dest)isi,
concat(p.po, ' - ', buyer, ' - ', styleno, ' - ', p.dest) tampil from ppic_master_so p
inner join master_sb_ws m on p.id_so_det = m.id_so_det
left join (select po from packing_master_packing_list group by po) a on p.po = a.po
where tgl_shipment >= '2024-05-01' and a.po is null
group by p.po,p.dest
order by buyer asc, styleno asc, p.po asc");
        return response()->json($data_po);
    }


    public function show_detail_packing_list(Request $request)
    {
        $po = $request->po;
        $dest = $request->dest;

        $data_det_poacking_list = DB::select("select
a.po,
a.dest,
m.styleno,
m.ws,
a.no_carton,
a.qty,
tipe_pack,
p.barcode,
m.color,
m.size
from packing_master_packing_list a
inner join ppic_master_so p on a.id_ppic_master_so = p.id
inner join master_sb_ws m on p.id_so_det = m.id_so_det
where a.po = '$po' and a.dest = '$dest'
order by no_carton asc
                    ");
        return DataTables::of($data_det_poacking_list)->toJson();
    }
}
