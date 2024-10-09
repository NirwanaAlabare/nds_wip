<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportLaporanTrfGarment;

class FinishGoodAlokasiKartonController extends Controller
{
    public function index(Request $request)
    {
        $tgl_awal = $request->dateFrom;
        $tgl_akhir = $request->dateTo;
        $tgl_skrg = date('Y-m-d');
        $user = Auth::user()->username;
        if ($request->ajax()) {
            $additionalQuery = '';
            $data_input = DB::select("SELECT a.*, coalesce(tot_karton,0)tot_karton from fg_fg_master_lok a
left join
(
select lokasi,count(no_carton) tot_karton from
(
select * from (select * from fg_fg_in where status  = 'NORMAL') a
left join
(
select id_fg_in from fg_fg_out where status = 'NORMAL'
) b on a.id = b.id_fg_in
where b.id_fg_in is null
group by a.no_carton, a.po
)
b
group by lokasi
) b on a.kode_lok = b.lokasi
");

            return DataTables::of($data_input)->toJson();
        }
        $data_po = DB::select("SELECT po isi, concat(po, ' - ', buyer) tampil from
(select id_so_det, po from fg_fg_in WHERE status = 'NORMAL' group by po) a
inner join master_sb_ws m on a.id_so_det = m.id_so_det
order by buyer asc");

        return view(
            'finish_good.finish_good_alokasi_karton',
            [
                'page' => 'dashboard_finish_good',
                "subPageGroup" => "finish_good_alokasi_karton",
                "data_po" => $data_po,
                "user" => $user,
                "subPage" => "finish_good_alokasi_karton"
            ]
        );
    }


    public function getdata_lokasi_alokasi(Request $request)
    {
        $cek_data = DB::select("
        SELECT * FROM fg_fg_master_lok	where id = '$request->id_e'
        ");
        return json_encode($cek_data ? $cek_data[0] : null);
    }

    public function getno_carton_alokasi(Request $request)
    {
        $data_no_carton = DB::select("SELECT
        concat(no_carton,'_',notes) isi, concat(no_carton, ' ( ', sum(qty) , ' pcs ) ', ' ( Lok : ', a.lokasi,' ) ', notes) tampil
        from fg_fg_in a
        left join (select id_fg_in from fg_fg_in_alokasi_karton_tmp) b on a.id = b.id_fg_in
        where  lokasi != '" . $request->txtkode_lok . "' and po = '" . $request->cbopo . "'
        and status = 'NORMAL' and b.id_fg_in is null
        GROUP BY po, no_carton
        order by no_carton asc
        ");

        $html = "<option value=''>Pilih No. Carton</option>";

        foreach ($data_no_carton as $datanocarton) {
            $html .= " <option value='" . $datanocarton->isi . "'>" . $datanocarton->tampil . "</option> ";
        }

        return $html;
    }

    public function show_preview_detail_alokasi(Request $request)
    {
        $user = Auth::user()->name;
        $lok = $request->txtkode_lok;
        if ($request->ajax()) {


            // SELECT
            // buyer,
            // po,
            // no_carton,
            // ws,
            // color,
            // size,
            // notes,
            // a.qty,
            // 'tetap' stat
            // from fg_fg_in a
            // inner join master_sb_ws m on a.id_so_det = m.id_so_det
            // where lokasi = '$lok' and status = 'NORMAL'
            // union all
            // select
            // buyer,
            // a.po,
            // a.no_carton,
            // ws,
            // color,
            // size,
            // b.notes,
            // b.qty,
            // 'tmp' stat
            // from fg_fg_in_alokasi_karton_tmp a
            // inner join 	fg_fg_in b on a.id_fg_in = b.id
            // inner join master_sb_ws m on b.id_so_det = m.id_so_det
            // where a.lokasi = '$lok'
            // order by
            // case when stat = 'tmp' then '1'
            // else
            // '2'
            // end, no_carton ASC

            $data_preview = DB::select("SELECT
            buyer,
            a.po,
            no_carton,
            ws,
            color,
            size,
            notes,
            a.qty,
			p.tgl_shipment,
			concat((DATE_FORMAT(p.tgl_shipment,  '%d')), '-', left(DATE_FORMAT(p.tgl_shipment,  '%M'),3),'-',DATE_FORMAT(p.tgl_shipment,  '%Y')
            ) tgl_shipment_fix,
            'tetap' stat
            from (select * from fg_fg_in where status = 'NORMAL') a
			left join (select id_fg_in from fg_fg_out where status = 'NORMAL') b on a.id = b.id_fg_in
            inner join master_sb_ws m on a.id_so_det = m.id_so_det
			inner join ppic_master_so p on a.id_ppic_master_so = p.id
            where lokasi = '$lok' and b.id_fg_in is null
            union all
            SELECT
            buyer,
            a.po,
            a.no_carton,
            ws,
            color,
            size,
            b.notes,
            b.qty,
            p.tgl_shipment,
			concat((DATE_FORMAT(p.tgl_shipment,  '%d')), '-', left(DATE_FORMAT(p.tgl_shipment,  '%M'),3),'-',DATE_FORMAT(p.tgl_shipment,  '%Y')
            ) tgl_shipment_fix,
            'tmp' stat
            from fg_fg_in_alokasi_karton_tmp a
            inner join 	fg_fg_in b on a.id_fg_in = b.id
            inner join master_sb_ws m on b.id_so_det = m.id_so_det
			inner join ppic_master_so p on b.id_ppic_master_so = p.id
            where a.lokasi = '$lok' and status = 'NORMAL'
            order by
            case when stat = 'tmp' then '1'
            else
            '2'
            end, no_carton ASC
            ");

            return DataTables::of($data_preview)->toJson();
        }
    }


    public function insert_tmp_alokasi_karton(Request $request)
    {
        $timestamp = Carbon::now();
        $user = Auth::user()->username;
        $tgl_skrg = date('Y-m-d');

        $lokasi = $request->cbolok;
        $po = $request->cbopo;
        $cekArray = explode('_', $_POST['cbono_carton']);
        $no_carton = $cekArray[0];
        $notes = $cekArray[1];

        $ins_tmp =  DB::insert("INSERT into fg_fg_in_alokasi_karton_tmp (id_fg_in,po,no_carton,lokasi,notes,created_at,updated_at,created_by)
        select id,po,no_carton,'$lokasi','$notes','$timestamp','$timestamp','$user' from fg_fg_in where  status = 'NORMAL' and po = '$po' and no_carton = '$no_carton' and notes = '$notes' ");
    }

    public function alokasi_hapus_tmp(Request $request)
    {
        $id_po = $request->id_po;
        $id_no_carton = $request->id_no_carton;
        $id_notes = $request->id_notes;

        $del_history =  DB::delete("
        delete from fg_fg_in_alokasi_karton_tmp where po = '$id_po' and no_carton = '$id_no_carton' and notes = '$id_notes'");
    }

    public function delete_tmp_all_alokasi_karton(Request $request)
    {
        $user = $request->user;
        $lokasi = $request->cbolok;
        $del_tmp_all =  DB::delete("
        delete from fg_fg_in_alokasi_karton_tmp where created_by = '$user' and lokasi = '$lokasi'");
    }

    public function store(Request $request)
    {
        $timestamp = Carbon::now();
        $user = Auth::user()->username;
        $tgl_skrg = date('Y-m-d');

        $lokasi   = $_POST['txtkode_lok'];
        // $po       = $_POST['cbopo'];
        // $cekArray = explode('_', $_POST['cbono_carton']);
        // $no_carton = $cekArray[0];
        // $notes = $cekArray[1];

        $update_lokasi =  DB::update("UPDATE fg_fg_in a
        inner join fg_fg_in_alokasi_karton_tmp b on a.id = b.id_fg_in
        set a.lokasi = b.lokasi, a.updated_at = '$timestamp'
        where b.lokasi = '$lokasi' and a.status = 'NORMAL' ");

        $del_tmp =  DB::delete("
        delete from fg_fg_in_alokasi_karton_tmp where lokasi = '$lokasi'");



        if ($update_lokasi != '') {
            return array(
                "status" => 201,
                "message" => 'Data Sudah Terbuat',
                "additional" => [],
                'table' => 'datatable_preview'
                // "callback" => "getno_carton();dataTableReload();"
            );
        } else {
            return array(
                "status" => 200,
                "message" => 'Tidak ada Data',
                "additional" => [],
            );
        }
    }
}
