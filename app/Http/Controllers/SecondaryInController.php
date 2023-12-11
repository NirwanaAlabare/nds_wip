<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use App\Exports\ExportLaporanMutasiKaryawan;
use App\Models\SecondaryIn;
use Maatwebsite\Excel\Facades\Excel;
use DB;
use Illuminate\Support\Facades\Auth;

class SecondaryInController extends Controller
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
            SELECT a.*,
            DATE_FORMAT(a.tgl_trans, '%d-%m-%Y') tgl_trans_fix,
            s.act_costing_ws,
            s.color,
            p.buyer,
            p.style,
            a.qty_awal,
            a.qty_reject,
            a.qty_replace,
            a.qty_in,
            a.created_at,
            users.name
            from secondary_in_input a
            inner join stocker_input s on a.id_qr_stocker = s.id_qr_stocker
            inner join part_detail pd on s.part_detail_id = pd.id
            inner join part p on pd.part_id = p.id
            inner join users on a.user = users.id
            ");

            return DataTables::of($data_input)->toJson();
        }
        return view('secondary-in.secondary-in', ['page' => 'dashboard-dc', "subPageGroup" => "secondary-dc", "subPage" => "secondary-in", "data_rak" => $data_rak], ['tgl_skrg' => $tgl_skrg]);
    }

    public function cek_data_stocker_in(Request $request)
    {
        $cekdata =  DB::select("
        select
        sii.no_form,
        sii.id_qr_stocker,
        s.act_costing_ws,
        buyer,
        no_cut,
        style,
        s.color,
        s.size,
        dc.tujuan,
        dc.alokasi,
        mp.nama_part,
        sii.qty_in qty_awal,
        ifnull(b.id_qr_stocker,'x')
        from secondary_inhouse_input sii
        inner join stocker_input s on sii.id_qr_stocker = s.id_qr_stocker
        inner join form_cut_input a on s.form_cut_id = a.id
        inner join part_detail p on s.part_detail_id = p.id
        inner join master_part mp on p.master_part_id = mp.id
        inner join marker_input mi on a.id_marker = mi.kode
		left join secondary_in_input b on sii.id_qr_stocker = b.id_qr_stocker
        inner join dc_in_input dc on sii.id_qr_stocker = dc.id_qr_stocker
        where sii.id_qr_stocker =  '" . $request->txtqrstocker . "' and ifnull(b.id_qr_stocker,'x') = 'x'
        ");
        return json_encode($cekdata[0]);
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
        return view('secondary-in.create-secondary-in', ['page' => 'dashboard-dc']);
    }

    public function store(Request $request)
    {
        $tgltrans = date('Y-m-d');
        $timestamp = Carbon::now();

        $validatedRequest = $request->validate([
            "cborak" => "required",
            "txtqtyreject" => "required"
        ]);

        $saveinhouse = SecondaryIn::create([
            'tgl_trans' => $tgltrans,
            'no_form' => $request['txtno_form'],
            'id_qr_stocker' => $request['txtno_stocker'],
            'tujuan' => 'NON SECONDARY',
            'alokasi' => $validatedRequest['cborak'],
            'qty_awal' => $request['txtqtyawal'],
            'qty_reject' => $request['txtqtyreject'],
            'qty_replace' => $request['txtqtyreplace'],
            'qty_in' => $request['txtqtyin'],
            'user' => Auth::user()->id,
            'ket' => $request['txtket'],
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);
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
