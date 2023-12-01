<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\MutKaryawan;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use App\Exports\ExportLaporanMutasiKaryawan;
use App\Models\DCIn;
use App\Models\Tmp_Dc_in;
use Maatwebsite\Excel\Facades\Excel;
use DB;

class DCInController extends Controller
{
    public function index(Request $request)
    {
        $tgl_skrg = Carbon::now()->isoFormat('D MMMM Y hh:mm:ss');
        $tglskrg = date('Y-m-d');

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

            $dc_in_index_group = DB::select("
            select a.no_form,
            a.no_cut,
            p.act_costing_ws,
            p.buyer,
            p.style,
            mi.color,
            b.list_part
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
            "SELECT ifnull(tmp.id_qr_stocker,'x'),a.no_form,mp.nama_part,mp.id,s.* FROM `stocker_input` s
            inner join form_cut_input a on s.form_cut_id = a.id
            inner join part_detail p on s.part_detail_id = p.id
            inner join master_part mp on p.master_part_id = mp.id
            left join tmp_dc_in_input tmp on s.id_qr_stocker = tmp.id_qr_stocker
            where a.no_form = '" . $request->no_form . "' and ifnull(tmp.id_qr_stocker,'x') = 'x'
            order by color asc, size asc "
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
            tmp.qty_replace
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
        SELECT a.id_qr_stocker,
        tujuan,
        alokasi,
        qty_ply,
        qty_reject,
        qty_replace
        FROM `tmp_dc_in_input`a
        inner join stocker_input s on a.id_qr_stocker = s.id_qr_stocker
        where a.id_qr_stocker = '$request->id_c'");
        return json_encode($data_tmp_dc_in[0]);
    }

    public function get_alokasi(Request $request)
    {
        $data_tujuan = $request->tujuan;

        if ($data_tujuan == 'NON SECONDARY') {
            $data_alokasi = DB::select("select nama_detail_rak isi, nama_detail_rak tampil from rack_detail");
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


    public function update_tmp_dc_in(Request $request)
    {
        $update_tmp_dc_in = DB::update("
        update tmp_dc_in_input
        set qty_reject = '$request->txtqtyreject',
        qty_replace = '$request->txtqtyreplace',
        tujuan = '$request->cbotuj',
        alokasi = '$request->cboalokasi'
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


    public function store(Request $request)
    {
        $timestamp = Carbon::now();
        $savemutasi = Tmp_Dc_in::create([
            'id_qr_stocker' => $request['txtqrstocker'],
            'no_form' => $request['no_form'],
            'qty_reject' => '0',
            'qty_replace' => '0',
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);
    }

    public function export_excel_mut_karyawan(Request $request)
    {
        return Excel::download(new ExportLaporanMutasiKaryawan($request->from, $request->to), 'Laporan_Mutasi_Karyawan.xlsx');
    }
}
