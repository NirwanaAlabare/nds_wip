<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use App\Models\SecondaryInhouse;
use DB;
use Illuminate\Support\Facades\Auth;

class DCInController extends Controller
{
    public function index(Request $request)
    {
        $tgl_skrg = Carbon::now()->isoFormat('D MMMM Y hh:mm:ss');
        $tglskrg = date('Y-m-d');

        $data_rak = DB::select("select nama_detail_rak isi, nama_detail_rak tampil from rack_detail");
        // dd($data_rak);
        if ($request->ajax()) {
            $additionalQuery = '';

            // if ($request->dateFrom) {
            //     $additionalQuery .= " and a.tgl_form_cut >= '" . $request->dateFrom . "' ";
            // }

            // if ($request->dateTo) {
            //     $additionalQuery .= " and a.tgl_form_cut <= '" . $request->dateTo . "' ";
            // }

            $keywordQuery = '';
            if ($request->search['value']) {
                $keywordQuery =
                    "
                     (
                        line like '%" .
                    $request->search['value'] .
                    "%'
                    )
                ";
            }

            $data_input = DB::select("
            SELECT
            a.id_qr_stocker,
            DATE_FORMAT(a.tgl_trans, '%d-%m-%Y') tgl_trans_fix,
            s.act_costing_ws,
            s.color,
            p.buyer,
            p.style,
            a.qty_awal,
            a.qty_reject,
            a.qty_replace,
            (a.qty_awal + a.qty_reject - a.qty_replace) qty_in,
            a.tujuan,
            a.lokasi,
            a.tempat,
            a.created_at,
            user
            from dc_in_input a
            inner join stocker_input s on a.id_qr_stocker = s.id_qr_stocker
            inner join part_detail pd on s.part_detail_id = pd.id
            inner join part p on pd.part_id = p.id
            order by a.tgl_trans desc
            ");

            return DataTables::of($data_input)->toJson();
        }
        return view('dc-in.dc-in', ['page' => 'dashboard-dc', "subPageGroup" => "dcin-dc", "subPage" => "dc-in", "data_rak" => $data_rak], ['tgl_skrg' => $tgl_skrg]);
    }

    public function insert_tmp_dc(Request $request)
    {
        $user = Auth::user()->name;
        if ($request->txttuj_h == 'NON SECONDARY') {
            $tujuan = $request->txttuj_h;
            $lokasi = $request->txtlok_h;
            $tempat = $request->txttempat_h;
        } else {
            $tujuan = $request->txttuj_h;
            $lokasi = $request->txtlok_h;
            $tempat = '-';
        }

        $cekdata =  DB::select("
select * from tmp_dc_in_input_new where id_qr_stocker = '" . $request->txtqrstocker . "'
        ");

        $cekdata_fix = $cekdata ? $cekdata[0] : null;
        if ($cekdata_fix ==  null) {

        DB::insert(
            "insert into tmp_dc_in_input_new (id_qr_stocker,qty_reject,qty_replace,tujuan,tempat,lokasi, user)
            values ('" . $request->txtqrstocker . "','0','0','$tujuan','$tempat','$lokasi','$user')"
        );

        $cekdata_fix = $cekdata ? $cekdata[0] : null;
        if ($cekdata_fix ==  null) {

            DB::insert(
                "insert into tmp_dc_in_input_new (id_qr_stocker,qty_reject,qty_replace,tujuan,tempat,lokasi, user)
            values ('" . $request->txtqrstocker . "','0','0','$tujuan','$tempat','$lokasi','$user')"
            );

            DB::update(
                "update stocker_input set status = 'dc' where id_qr_stocker = '" . $request->txtqrstocker . "'"
            );
        }
    }
    }

    public function get_data_tmp(Request $request)
    {
        $user = Auth::user()->name;
        $data_tmp = DB::select(
            "
            select
            ms.id_qr_stocker,
            mp.nama_part,
            concat(ms.id_qr_stocker,' - ',mp.nama_part)kode_stocker,
            ifnull(s.tujuan,'-') tujuan,
            ifnull(tmp.tempat,'-') tempat,
            ifnull(tmp.lokasi,'-') lokasi,
            ms.qty_ply - coalesce(tmp.qty_reject,0) + coalesce(tmp.qty_replace,0) qty_in,
            ifnull(tmp.id_qr_stocker,'x') cek_stat
            from (
                select *,concat(so_det_id,'_',range_awal,'_',range_akhir,'_',shade)kode from stocker_input) ms
                inner join
                        (
                        select concat(so_det_id,'_',range_awal,'_',range_akhir,'_',shade)kode  from tmp_dc_in_input_new x
                        inner join stocker_input y on x.id_qr_stocker = y.id_qr_stocker
                        where user = '$user'
                        group by concat(so_det_id,'_',range_awal,'_',range_akhir,'_',shade)
                        )
                        a on ms.kode = a.kode
                        inner join part_detail pd on ms.part_detail_id = pd.id
                        inner join master_part mp  on pd.master_part_id = mp.id
                        left join master_secondary s on pd.master_secondary_id = s.id
                        left join tmp_dc_in_input_new tmp on ms.id_qr_stocker = tmp.id_qr_stocker
                        order by ifnull(tmp.id_qr_stocker,'x') asc
            ",
        );

        return DataTables::of($data_tmp)->toJson();
    }

    public function show_data_header(Request $request)
    {
        $data_header = DB::select("
        SELECT
        a.act_costing_ws,
        m.buyer,
        m.styleno,
        a.color,
        a.size,
        a.panel,
        f.no_cut,
        f.id,
        b.grouplot,
        a.qty_ply,
        a.range_awal,
        a.range_akhir,
        concat(so_det_id,'_',range_awal,'_',range_akhir,'_',shade)kode,
        ms.tujuan,
        IF(ms.tujuan = 'NON SECONDARY',a.lokasi,ms.proses) lokasi ,
        a.tempat
        FROM `stocker_input` a
        inner join master_sb_ws m on a.so_det_id = m.id_so_det
        inner join form_cut_input f on a.form_cut_id = f.id
        inner join part_detail pd on a.part_detail_id = pd.id
        inner join master_secondary ms on pd.master_secondary_id = ms.id
        inner join
        (
        select no_form_cut_input, group_concat(distinct(upper(group_roll))) grouplot from form_cut_input_detail
        group by no_form_cut_input,group_roll
        ) b on f.no_form = b.no_form_cut_input
        where a.id_qr_stocker = '$request->txtqrstocker'");
        return json_encode($data_header ? $data_header[0] : null);
    }


    public function show_tmp_dc_in(Request $request)
    {
        $data_tmp_dc_in = DB::select("
        SELECT
        s.id_qr_stocker,
        s.qty_ply - coalesce(tmp.qty_reject,0) + coalesce(tmp.qty_replace,0) qty_in,
        tmp.qty_reject,
        tmp.qty_replace,
        ms.tujuan,
        ms.proses,
        tmp.tempat,
        tmp.lokasi,
        tmp.ket,
        concat(so_det_id,'_',range_awal,'_',range_akhir,'_',shade)kode
        from stocker_input s
        inner join part_detail pd on s.part_detail_id = pd.id
        inner join master_part mp  on pd.master_part_id = mp.id
        left join master_secondary ms on pd.master_secondary_id = ms.id
        left join tmp_dc_in_input_new tmp on s.id_qr_stocker = tmp.id_qr_stocker
        where s.id_qr_stocker= '$request->id_c'");
        return json_encode($data_tmp_dc_in[0]);
    }

    public function get_tempat(Request $request)
    {
        $tujuan = $request->tujuan;
        if ($tujuan == 'NON SECONDARY') {
            $data_tempat = DB::select("select 'RAK' isi, 'RAK' tampil
            union
            select 'TROLLEY', 'TROLLEY'");
            $html = "<option value=''>Pilih Tempat</option>";
            foreach ($data_tempat as $datatempat) {
                $html .= " <option value='" . $datatempat->tampil . "'>" . $datatempat->tampil . "</option> ";
            }
        } else {
            $data_tempat = DB::select("select '-' isi, '-' tampil");
            $html = "<option value = '-' selected> - </option>";
        }

        return $html;
    }


    public function get_lokasi(Request $request)
    {
        $tujuan = $request->tujuan;
        $tempat = $request->tempat;
        if ($tujuan == 'NON SECONDARY' && $tempat == 'RAK') {
            $data_alokasi = DB::select("select kode isi, nama_detail_rak tampil from rack_detail");
            $html = "<option value=''>Pilih Rak</option>";
            foreach ($data_alokasi as $dataalokasi) {
                $html .= " <option value='" . $dataalokasi->tampil . "'>" . $dataalokasi->tampil . "</option> ";
            }
        } else if ($tujuan == 'NON SECONDARY' && $tempat == 'TROLLEY') {
            $data_alokasi = DB::select("select kode isi, nama_trolley tampil from trolley");
            $html = "<option value=''>Pilih Trolley</option>";
            foreach ($data_alokasi as $dataalokasi) {
                $html .= " <option value='" . $dataalokasi->tampil . "'>" . $dataalokasi->tampil . "</option> ";
            }
        } else {
            $data_alokasi = DB::select("select proses isi, proses tampil from master_secondary where tujuan = '$tujuan'");
            $html = "<option value=''>Pilih Lokasi</option>";
            foreach ($data_alokasi as $dataalokasi) {
                $html .= " <option value='" . $dataalokasi->tampil . "'>" . $dataalokasi->tampil . "</option> ";
            }
        }

        return $html;
    }


    public function create(Request $request)
    {
        return view('dc-in.create-dc-in', ['page' => 'dashboard-dc', "subPageGroup" => "dcin-dc", "subPage" => "dc-in"]);
    }

    public function update_tmp_dc_in(Request $request)
    {

        if ($request->txttuj == 'NON SECONDARY') {
            $update_stocker_input = DB::update(
                "update stocker_input set
                tempat = '" . $request->cbotempat . "',
                tujuan = '" . $request->txttuj . "',
                lokasi = '" . $request->cbolokasi . "'
                where concat(so_det_id,'_',range_awal,'_',range_akhir,'_',shade) = '" . $request->id_kode . "'
                "
            );
        }

        $update_tmp_dc_in = DB::update(
            "update tmp_dc_in_input_new set
            qty_reject = '" . $request->txtqtyreject . "',
            qty_replace = '" . $request->txtqtyreplace . "',
            tujuan = '" . $request->txttuj . "',
            tempat = '" . $request->cbotempat . "',
            lokasi = '" . $request->cbolokasi . "',
            ket = '" . $request->txtket . "'
            where id_qr_stocker = '" . $request->id_c . "'
            "
        );

        if ($update_tmp_dc_in) {
            return array(
                'status' => 300,
                'message' => 'Data Stocker "' . $request->id_c . '" berhasil diubah',
                'redirect' => '',
                'table' => 'datatable-scan',
                'additional' => [],
                'callback' => 'tmp_dc_input_new'
            );
        }
    }



    public function store(Request $request)
    {
        $tgltrans = date('Y-m-d');
        $timestamp = Carbon::now();
        $user = Auth::user()->name;



        DB::insert(
            "insert into dc_in_input
            (id_qr_stocker,tgl_trans,tujuan,lokasi,tempat,qty_awal,qty_reject,qty_replace,user,status,created_at,updated_at)
            select tmp.id_qr_stocker,'$tgltrans',tmp.tujuan,tmp.lokasi,tmp.tempat,ms.qty_ply,qty_reject,qty_replace,user,'N','$timestamp','$timestamp' from tmp_dc_in_input_new tmp
            inner join stocker_input ms on tmp.id_qr_stocker = ms.id_qr_stocker
            where user = '$user'"
        );

        DB::insert(
            "INSERT INTO rack_detail_stocker (detail_rack_id,nm_rak,stocker_id,qty_in,created_at,updated_at)
            select r.id,nama_detail_rak,tmp.id_qr_stocker,s.qty_ply - qty_reject + qty_replace qty_in, '$timestamp','$timestamp'
            from tmp_dc_in_input_new tmp
            inner join rack_detail r on tmp.lokasi = r.nama_detail_rak
            inner join stocker_input s on tmp.id_qr_stocker = s.id_qr_stocker
            where tmp.tujuan = 'NON SECONDARY' AND user = '$user'"
        );



        return array(
            'status' => 999,
            'message' => 'Data Sudah Disimpan',
            'redirect' => 'reload',
            'table' => '',
            'additional' => [],
            'callback' => 'cleard()',
        );
    }

    public function destroy(Request $request)
    {
        $user = Auth::user()->name;

        DB::delete(
            "DELETE FROM tmp_dc_in_input_new where user = '$user'"
        );
    }

    // public function export_excel_mut_karyawan(Request $request)
    // {
    //     return Excel::download(new ExportLaporanMutasiKaryawan($request->from, $request->to), 'Laporan_Mutasi_Karyawan.xlsx');
    // }
}
