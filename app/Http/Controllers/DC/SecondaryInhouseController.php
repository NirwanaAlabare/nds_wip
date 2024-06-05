<?php

namespace App\Http\Controllers\DC;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Exports\ExportLaporanMutasiKaryawan;
use App\Models\SecondaryInhouse;
use Yajra\DataTables\Facades\DataTables;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use DB;

class SecondaryInhouseController extends Controller
{
    public function index(Request $request)
    {
        $tgl_skrg = Carbon::now()->isoFormat('D MMMM Y hh:mm:ss');
        $tglskrg = date('Y-m-d');

        $data_rak = DB::select("select nama_detail_rak isi, nama_detail_rak tampil from rack_detail");
        // dd($data_rak);
        if ($request->ajax()) {
            $additionalQuery = '';

            if ($request->dateFrom) {
                $additionalQuery .= " and a.tgl_trans >= '" . $request->dateFrom . "' ";
            }

            if ($request->dateTo) {
                $additionalQuery .= " and a.tgl_trans <= '" . $request->dateTo . "' ";
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

            $data_input = DB::select("
                SELECT a.*,
                DATE_FORMAT(a.tgl_trans, '%d-%m-%Y') tgl_trans_fix,
                a.tgl_trans,
                s.act_costing_ws,
                s.color,
                p.buyer,
                p.style,
                a.qty_awal,
                a.qty_reject,
                a.qty_replace,
                a.qty_in,
                a.created_at,
                dc.tujuan,
                dc.lokasi,
                dc.tempat,
                f.no_cut,
                COALESCE(msb.size, s.size) size,
                a.user,
                mp.nama_part
                from secondary_inhouse_input a
                inner join stocker_input s on a.id_qr_stocker = s.id_qr_stocker
                left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                left join form_cut_input f on f.id = s.form_cut_id
                inner join part_detail pd on s.part_detail_id = pd.id
                inner join part p on pd.part_id = p.id
                inner join master_part mp on mp.id = pd.master_part_id
                inner join (select id_qr_stocker,tujuan, lokasi, tempat from dc_in_input) dc on a.id_qr_stocker = dc.id_qr_stocker
                where
                a.tgl_trans is not null
                ".$additionalQuery."
                order by a.tgl_trans desc
            ");

            return DataTables::of($data_input)->toJson();
        }
        return view('dc.secondary-inhouse.secondary-inhouse', ['page' => 'dashboard-dc', "subPageGroup" => "secondary-dc", "subPage" => "secondary-inhouse", "data_rak" => $data_rak], ['tgl_skrg' => $tgl_skrg]);
    }


    public function detail_stocker_inhouse(Request $request)
    {
        $tgl_skrg = Carbon::now()->isoFormat('D MMMM Y hh:mm:ss');
        $tglskrg = date('Y-m-d');
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

            $data_detail = DB::select("
            select s.act_costing_ws, m.buyer,s.color,  styleno, COALESCE(sum(sii.qty_in), 0) qty_out, COALESCE(sum(dc.qty_awal - dc.qty_reject + dc.qty_replace), 0) qty_in,COALESCE((sum(sii.qty_in) - sum(dc.qty_awal - dc.qty_reject + dc.qty_replace)), 0) balance, dc.lokasi from dc_in_input dc
            inner join stocker_input s on dc.id_qr_stocker = s.id_qr_stocker
            inner join master_sb_ws m on s.so_det_id = m.id_so_det
            left join secondary_inhouse_input sii on dc.id_qr_stocker = sii.id_qr_stocker
            where dc.tujuan = 'SECONDARY DALAM' group by m.ws,m.buyer,m.styleno,m.color,dc.lokasi
            having sum(sii.qty_in) - sum(dc.qty_awal - dc.qty_reject + dc.qty_replace) != '0'
            ");

            return DataTables::of($data_detail)->toJson();
        }
        return view('dc.secondary-inhouse.secondary-inhouse', ['page' => 'dashboard-dc', "subPageGroup" => "secondary-dc", "subPage" => "secondary-inhouse"], ['tgl_skrg' => $tgl_skrg]);
    }



    public function cek_data_stocker_inhouse(Request $request)
    {
        $cekdata =  DB::select("
        SELECT
        dc.id_qr_stocker,
        s.act_costing_ws,
        msb.buyer,
        no_cut,
        style,
        s.color,
        COALESCE(msb.size, s.size) size,
        mp.nama_part,
        dc.tujuan,
        dc.lokasi,
        coalesce(s.qty_ply_mod, s.qty_ply) - dc.qty_reject + dc.qty_replace qty_awal,
        ifnull(si.id_qr_stocker,'x')
        from dc_in_input dc
        inner join stocker_input s on dc.id_qr_stocker = s.id_qr_stocker
        left join master_sb_ws msb on msb.id_so_det = s.so_det_id
        inner join form_cut_input a on s.form_cut_id = a.id
        inner join part_detail p on s.part_detail_id = p.id
        inner join master_part mp on p.master_part_id = mp.id
        inner join marker_input mi on a.id_marker = mi.kode
        left join secondary_inhouse_input si on dc.id_qr_stocker = si.id_qr_stocker
        where dc.id_qr_stocker =  '" . $request->txtqrstocker . "' and dc.tujuan = 'SECONDARY DALAM'
        and ifnull(si.id_qr_stocker,'x') = 'x'
        ");
        return json_encode($cekdata[0]);
        dd($cekdata);
    }


    // public function get_rak(Request $request)
    // {
    //     $data_rak = DB::select("select nama_detail_rak isi, nama_detail_rak tampil from rack_detail");
    //     $html = "<option value=''>Pilih Rak</option>";

    //     foreach ($data_rak as $datarak) {
    //         $html .= " <option value='" . $datarak->isi . "'>" . $datarak->tampil . "</option> ";
    //     }

    //     return $html;
    // }

    public function create()
    {
        return view('dc.secondary-in.create-secondary-in', ['page' => 'dashboard-dc']);
    }

    public function store(Request $request)
    {
        $tgltrans = date('Y-m-d');
        $timestamp = Carbon::now();

        $validatedRequest = $request->validate([
            "txtqtyreject" => "required"
        ]);

        $saveinhouse = SecondaryInhouse::create([
            'tgl_trans' => $tgltrans,
            'id_qr_stocker' => $request['txtno_stocker'],
            'qty_awal' => $request['txtqtyawal'],
            'qty_reject' => $request['txtqtyreject'],
            'qty_replace' => $request['txtqtyreplace'],
            'qty_in' => $request['txtqtyin'],
            'user' => Auth::user()->name,
            'ket' => $request['txtket'],
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);


        DB::update(
            "update stocker_input set status = 'secondary' where id_qr_stocker = '" . $request->txtno_stocker . "'"
        );

        // dd($savemutasi);
        // $message .= "$tglpindah <br>";


        return array(
            'status' => 300,
            'message' => 'Data Sudah Disimpan',
            'redirect' => '',
            'table' => 'datatable-input',
            'additional' => [],
        );
    }

    // public function export_excel_mut_karyawan(Request $request)
    // {
    //     return Excel::download(new ExportLaporanMutasiKaryawan($request->from, $request->to), 'Laporan_Mutasi_Karyawan.xlsx');
    // }
}
