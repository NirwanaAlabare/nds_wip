<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use App\Exports\ExportLaporanMutasiKaryawan;
use App\Models\Dc\DCIn;
use App\Models\Tmp_Dc_in;
use Maatwebsite\Excel\Facades\Excel;
use DB;
use Illuminate\Support\Facades\Auth;

class DCInController extends Controller
{
    public function index(Request $request)
    {
        $tgl_skrg = Carbon::now()->isoFormat('D MMMM Y hh:mm:ss');
        $tglskrg = date('Y-m-d');

        if ($request->ajax()) {
            $additionalQuery = '';

            if ($request->dateFrom) {
                $additionalQuery .= " where a.waktu_selesai >= '" . $request->dateFrom . " 00:00:00' ";
            }

            if ($request->dateTo) {
                $additionalQuery .= " and a.waktu_selesai <= '" . $request->dateTo . " 23:59:59' ";
            }

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

            $dc_in_index_group = DB::select("
            select a.no_form,
            a.no_cut,
            p.act_costing_ws,
            p.buyer,
            p.style,
            mi.color,
			mi.po_marker,
            b.list_part,
			count(c.id_qr_stocker) tot_stocker,
			count(dc.id_qr_stocker) in_stocker,
			count(c.id_qr_stocker) - count(dc.id_qr_stocker) sisa_stocker,
            count(tmp.id_qr_stocker ) tmp_stocker,
			DATE_FORMAT(a.waktu_selesai, '%d-%m-%Y %T') tgl_selesai_fix
            from part p
            inner join part_form pf on p.id = pf.part_id
            inner join form_cut_input a on pf.form_id = a.id
            inner join marker_input mi on a.id_marker = mi.kode
            inner join
            (
            select part_id,group_concat(mp.nama_part ORDER BY mp.id ASC) list_part from part_detail a
            inner join master_part mp on a.master_part_id = mp.id
            group by part_id
            ) b on p.id = b.part_id
            inner join stocker_input c on a.id = c.form_cut_id
			left join dc_in_input dc on c.id_qr_stocker = dc.id_qr_stocker
            left join tmp_dc_in_input tmp on c.id_qr_stocker = tmp.id_qr_stocker
            " . $additionalQuery . "
            group by no_form
            order by act_costing_ws asc, no_cut asc
            ");


            return DataTables::of($dc_in_index_group)->toJson();
        }
        return view('dc-in.dc-in', ['page' => 'dashboard-dc', "subPageGroup" => "dcin-dc", "subPage" => "dc-in"], ['tgl_skrg' => $tgl_skrg]);
    }

    public function create(Request $request, $no_form = 0)

    {
        $header_data = DB::select("
        select a.no_form,a.no_cut,p.*,b.list_part from part p
        inner join part_form pf on p.id = pf.part_id
        inner join form_cut_input a on pf.form_id = a.id
        inner join
        (
        select part_id,group_concat(mp.nama_part ORDER BY mp.id ASC) list_part from part_detail a
        inner join master_part mp on a.master_part_id = mp.id
        group by part_id
        ) b on p.id = b.part_id
        where a.no_form = '" . $no_form . "'
        order by act_costing_ws asc, no_cut asc
        ");


        $data_tujuan = DB::select("select 'NON SECONDARY' as tujuan, 'Non Secondary'alokasi
        union
        select 'SECONDARY DALAM', 'Secondary Dalam' alokasi
        union
        select 'SECONDARY LUAR', 'Secondary Luar' alokasi");

        // return view('dc-in.create-dc-in', ['page' => 'dashboard-dc', 'data_tujuan' => $data_tujuan, 'header' => $header_data[0]],);
        return view('dc-in.create-dc-in', ['page' => 'dashboard-dc', "subPageGroup" => "dcin-dc", "subPage" => "dc-in", 'data_tujuan' => $data_tujuan, 'header' => $header_data[0]],);
    }


    public function getdata_stocker_info(Request $request)
    {
        $det_dc_info = DB::select(
            // "SELECT ifnull(tmp.id_qr_stocker,'x'),a.no_form,mp.nama_part,mp.id,s.* FROM `stocker_input` s
            // inner join form_cut_input a on s.form_cut_id = a.id
            // inner join part_detail p on s.part_detail_id = p.id
            // inner join master_part mp on p.master_part_id = mp.id
            // left join tmp_dc_in_input tmp on s.id_qr_stocker = tmp.id_qr_stocker
            // where a.no_form = '" . $request->no_form . "' and ifnull(tmp.id_qr_stocker,'x') = 'x'
            // order by color asc, size asc "
            "select
            ifnull(tmp.id_qr_stocker,'x'),
            ifnull(dc.id_qr_stocker,'x'),
            a.no_form,
            mp.nama_part,
            mp.id,
            s.id_qr_stocker,
            s.part_detail_id,
            s.form_cut_id,
            s.act_costing_ws,
            s.so_det_id,
            s.size,
            s.color,
            s.panel,
            s.shade,
            s.ratio,
            s.qty_ply,
            s.range_awal,
            s.range_akhir,
            cek.so_det_id cekdata
            from
            stocker_input s
            inner join form_cut_input a on s.form_cut_id = a.id
            inner join part_detail p on s.part_detail_id = p.id
            inner join master_part mp on p.master_part_id = mp.id
            left join tmp_dc_in_input tmp on s.id_qr_stocker = tmp.id_qr_stocker
            left join dc_in_input dc on s.id_qr_stocker = dc.id_qr_stocker
            left join
            (
                select so_det_id, no_form from tmp_dc_in_input a
                inner join stocker_input s on a.id_qr_stocker = s.id_qr_stocker
                where no_form = '" . $request->no_form . "'
                group by so_det_id
            )   cek on s.so_det_id = cek.so_det_id
            where a.no_form = '" . $request->no_form . "' and ifnull(tmp.id_qr_stocker,'x') = 'x'  and ifnull(dc.id_qr_stocker,'x') = 'x'
            order by color asc, size asc
            "


        );

        return DataTables::of($det_dc_info)->toJson();
    }

    public function getdata_stocker_input(Request $request)
    {
        $det_dc_input = DB::select(
            "SELECT
            tmp.no_form,
            mp.nama_part,
            mp.id,
            s.id_qr_stocker,
            s.shade,
            s.color,
            s.size,
            s.qty_ply,
            tmp.qty_reject,
            tmp.qty_replace,
            s.qty_ply - tmp.qty_reject + tmp.qty_replace qty_in,
            tmp.tujuan,
            tmp.det_alokasi,
            tmp.alokasi
            from tmp_dc_in_input tmp
            inner join stocker_input s on tmp.id_qr_stocker = s.id_qr_stocker
            inner join form_cut_input a on s.form_cut_id = a.id
            inner join part_detail p on s.part_detail_id = p.id
            inner join master_part mp on p.master_part_id = mp.id
            where tmp.no_form = '" . $request->no_form . "'
            order by color asc, size asc "
        );

        return DataTables::of($det_dc_input)->toJson();
    }



    public function getdata_dc_in(Request $request)
    {
        $det_dc_in = DB::select(
            "select * from dc_in_input a
            inner join stocker_input b on a.id_qr_stocker = b.id_qr_stocker"
        );

        return DataTables::of($det_dc_in)->toJson();
    }

    public function show_tmp_dc_in(Request $request)
    {
        $data_tmp_dc_in = DB::select("
        SELECT
        concat (a.id_qr_stocker, ' ', panel , ' ', size, ' ', color) nama_stocker,
        a.id_qr_stocker,
        tujuan,
        alokasi,
        det_alokasi,
        qty_ply,
        qty_reject,
        qty_replace,
        qty_ply - qty_reject + qty_replace qty_in
        FROM `tmp_dc_in_input`a
        inner join stocker_input s on a.id_qr_stocker = s.id_qr_stocker
        where a.id_qr_stocker = '$request->id_c'");
        return json_encode($data_tmp_dc_in[0]);
    }

    public function get_alokasi(Request $request)
    {
        $data_tujuan = $request->tujuan;
        if ($data_tujuan == 'NON SECONDARY') {
            $data_alokasi = DB::select("select 'RAK' isi, 'RAK' tampil
            union
            select 'TROLLEY', 'TROLLEY'");
            $html = "<option value=''>Pilih Rak</option>";
        } else if ($data_tujuan == 'SECONDARY DALAM') {
            $data_alokasi = DB::select("select kode isi, proses tampil from master_secondary where jenis = 'DALAM'");
            $html = "<option value=''>Pilih Proses Secondary Dalam</option>";
        } else if ($data_tujuan == 'SECONDARY LUAR') {
            $data_alokasi = DB::select("select kode isi, proses tampil from master_secondary where jenis = 'LUAR'");
            $html = "<option value=''>Pilih Proses Secondary Luar</option>";
        }

        // $datano_marker = DB::select("select *,  concat(kode,' - ',color, ' - (',panel, ' - ',urutan_marker, ' )') tampil  from marker_input a
        // left join (select id_marker from form_cut_input group by id_marker ) b on a.kode = b.id_marker
        // where act_costing_id = '" . $request->cbows . "' and b.id_marker is null and a.cancel = 'N' order by urutan_marker asc");
        // $html = "<option value=''>Pilih No Marker</option>";

        foreach ($data_alokasi as $dataalokasi) {
            $html .= " <option value='" . $dataalokasi->tampil . "'>" . $dataalokasi->tampil . "</option> ";
        }

        return $html;
    }

    public function get_det_alokasi(Request $request)
    {
        $data_alokasi = $request->alokasi;
        if ($data_alokasi == 'RAK') {
            $data_detail_alokasi = DB::select("select nama_detail_rak isi, nama_detail_rak tampil from rack_detail");
            $html = "<option value=''>Pilih Penempatan</option>";
        } else if ($data_alokasi == 'TROLLEY') {
            $data_detail_alokasi = DB::select("select kode isi, nama_trolley tampil from trolley");
            $html = "<option value=''>Pilih Penempatan</option>";
        }

        foreach ($data_detail_alokasi as $datadetailalokasi) {
            $html .= " <option value='" . $datadetailalokasi->tampil . "'>" . $datadetailalokasi->tampil . "</option> ";
        }

        return $html;
    }



    public function update_tmp_dc_in(Request $request)
    {
        if ($request->cbotuj != 'NON SECONDARY') {
            $validatedRequest = $request->validate([
                "cbotuj" => "required",
                "cboalokasi" => "required",
            ]);

            $update_tmp_dc_in = DB::update("
            update tmp_dc_in_input
            set
            qty_awal = '$request->txtqty',
            qty_reject = '$request->txtqtyreject',
            qty_replace = '$request->txtqtyreplace',
            tujuan =  '" . $validatedRequest['cbotuj'] . "',
            alokasi = '" . $validatedRequest['cboalokasi'] . "',
            det_alokasi = '" . $validatedRequest['cboalokasi'] . "'
            where id_qr_stocker = '$request->id_c'");

            if ($update_tmp_dc_in) {
                return array(
                    'status' => 300,
                    'message' => 'Data Stocker "' . $request->id_c . '" berhasil diubah',
                    'redirect' => '',
                    'table' => 'datatable-input',
                    'additional' => [],
                );
            }
            return array(
                'status' => 400,
                'message' => 'Data produksi gagal diubah',
                'redirect' => '',
                'table' => 'datatable-input',
                'additional' => [],
            );
        } else {
            $validatedRequest = $request->validate([
                "cbotuj" => "required",
                "cboalokasi" => "required",
                "cbodetalokasi" => "required",
            ]);

            $update_tmp_dc_in = DB::update("
    update tmp_dc_in_input
    set
    qty_awal = '$request->txtqty',
    qty_reject = '$request->txtqtyreject',
    qty_replace = '$request->txtqtyreplace',
    tujuan =  '" . $validatedRequest['cbotuj'] . "',
    alokasi = '" . $validatedRequest['cboalokasi'] . "',
    det_alokasi = '" . $validatedRequest['cbodetalokasi'] . "'
    where id_qr_stocker = '$request->id_c'");

            if ($update_tmp_dc_in) {
                return array(
                    'status' => 300,
                    'message' => 'Data Stocker "' . $request->id_c . '" berhasil diubah',
                    'redirect' => '',
                    'table' => 'datatable-input',
                    'additional' => [],
                );
            }
            return array(
                'status' => 400,
                'message' => 'Data produksi gagal diubah',
                'redirect' => '',
                'table' => 'datatable-input',
                'additional' => [],
            );
        }
    }


    public function store(Request $request)
    {
        $timestamp = Carbon::now();
        $cekdata =  DB::select("
        select
        ifnull(tmp.id_qr_stocker,'x'),
        ifnull(dc.id_qr_stocker,'x'),
        a.no_form,
        mp.nama_part,
        mp.id,
        s.id_qr_stocker,
        s.part_detail_id,
        s.form_cut_id,
        s.act_costing_ws,
        s.so_det_id,
        s.size,
        s.color,
        s.panel,
        s.shade,
        s.qty_ply,
        s.ratio,
        s.range_awal,
        s.range_akhir
        from
        stocker_input	s
        inner join form_cut_input a on s.form_cut_id = a.id
        inner join part_detail p on s.part_detail_id = p.id
        inner join master_part mp on p.master_part_id = mp.id
        left join tmp_dc_in_input tmp on s.id_qr_stocker = tmp.id_qr_stocker
        left join dc_in_input dc on s.id_qr_stocker = dc.id_qr_stocker
        where a.no_form = '" . $request->no_form . "' and ifnull(tmp.id_qr_stocker,'x') = 'x'
        and ifnull(dc.id_qr_stocker,'x') = 'x' and s.id_qr_stocker = '" . $request->txtqrstocker . "'
        order by color asc, size asc
        ");
        $cekdata_fix = $cekdata ? $cekdata[0]->id_qr_stocker : null;
        if ($cekdata_fix == $request->id_qr_stocker) {
            return [
                'icon' => 'salah',
                'msg' => "Stocker " . $request->txtqrstocker . " Tidak Tersedia",
            ];
        } else {
            $savemutasi = Tmp_Dc_in::create([
                'id_qr_stocker' => $request['txtqrstocker'],
                'no_form' => $request['no_form'],
                'qty_awal' => '0',
                'qty_reject' => '0',
                'qty_replace' => '0',
                'user' => Auth::user()->id,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);
        }
        return [
            'icon' => 'benar',
            'msg' => "Stocker " . $request->txtqrstocker . " Sudah Terinput",
        ];
    }

    public function simpan_final_dc_in(Request $request)
    {
        $timestamp = Carbon::now();

        $cekdata =  DB::select("
        select count(cek_stocker) -
        COUNT(CASE cek_tmp WHEN cek_tmp = 'x' THEN 1 else null end)  cek_data
        from
        (
        select
        ifnull(a.id_qr_stocker,'x') cek_stocker,
        ifnull(tmp.id_qr_stocker,'x') cek_tmp,
        cek.so_det_id cekdata
        from stocker_input	a
        inner join form_cut_input b on a.form_cut_id = b.id
        left join
        (
        select * from tmp_dc_in_input where no_form = '" . $request->no_form . "'  and tujuan is not null and alokasi is not null and det_alokasi is not null
        )tmp on a.id_qr_stocker = tmp.id_qr_stocker
        left join
        (
                        select so_det_id, no_form from tmp_dc_in_input a
                        inner join stocker_input s on a.id_qr_stocker = s.id_qr_stocker
                        where no_form = '" . $request->no_form . "'
                        group by so_det_id
        )
        cek on a.so_det_id = cek.so_det_id
        where b.no_form = '" . $request->no_form . "'
        and cek.so_det_id is not null
        ) cek_tmp
        ");

        $cekdata_fix = $cekdata ? $cekdata[0]->cek_data :  '0';

        if ($cekdata_fix != '0') {

            return [
                'icon' => 'warning',
                'msg' => 'Terjadi Kesalahan',
                'timer' => false,
                'prog' => false,
            ];
        } else {

            $insert_dc_in = DB::insert("
        INSERT INTO dc_in_input
        (no_form, id_qr_stocker, tujuan, alokasi,det_alokasi,qty_awal,qty_reject,qty_replace,user, created_at, updated_at)
        SELECT no_form, id_qr_stocker, tujuan, alokasi, det_alokasi,qty_awal,qty_reject,qty_replace,user,created_at, updated_at
        FROM tmp_dc_in_input
        WHERE no_form = '$request->no_form'");
            $insert_rak = DB::insert("
        INSERT INTO rack_detail_stocker
            (nm_rak, detail_rack_id, stocker_id, qty_in, created_at, updated_at)
            SELECT det_alokasi, rack_detail.id,id_qr_stocker, qty_awal - qty_reject + qty_replace qty_in ,tmp_dc_in_input.created_at, tmp_dc_in_input.updated_at
            FROM tmp_dc_in_input
            left join rack_detail on tmp_dc_in_input.det_alokasi = rack_detail.nama_detail_rak
            WHERE no_form = '$request->no_form' and tujuan = 'NON SECONDARY' and alokasi = 'RAK'");
            $delete_tmp = DB::delete("
        delete from tmp_dc_in_input
        WHERE no_form = '$request->no_form'");
            return [
                'icon' => 'success',
                'msg' => 'Data Sudah Tersimpan',
                'timer' => false,
                'prog' => false,
            ];
        }
    }


    public function getdata_stocker_history(Request $request)
    {
        $history = DB::select(
            "
            select
            a.no_form,
            a.id_qr_stocker,
            mp.nama_part,
            s.size,
            s.shade,
            s.color,
            s.range_awal,
            s.range_akhir,
            s.qty_ply,
            a.qty_reject,
            a.qty_replace,
            s.qty_ply - a.qty_reject + a.qty_replace qty_in,
            a.tujuan,
            a.alokasi,
            a.det_alokasi,
            users.name,
            DATE_FORMAT(a.created_at, '%d-%m-%Y %T') tgl_create_fix,
            a.created_at,
            a.updated_at
            from dc_in_input a
            inner join stocker_input s on a.id_qr_stocker = s.id_qr_stocker
            inner join part_detail p on s.part_detail_id = p.id
            inner join master_part mp on p.master_part_id = mp.id
            inner join users on a.user = users.id
            where no_form = '" . $request->no_form . "'
            "
        );

        return DataTables::of($history)->toJson();
    }




    // public function export_excel_mut_karyawan(Request $request)
    // {
    //     return Excel::download(new ExportLaporanMutasiKaryawan($request->from, $request->to), 'Laporan_Mutasi_Karyawan.xlsx');
    // }
}
