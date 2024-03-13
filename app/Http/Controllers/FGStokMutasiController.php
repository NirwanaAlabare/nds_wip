<?php

namespace App\Http\Controllers;

use App\Exports\ExportLaporanFGStokMutasiInternal;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportLaporanPenerimaanFGStokBPB;

class FGStokMutasiController extends Controller
{
    public function index(Request $request)
    {
        $tgl_awal = $request->dateFrom;
        $tgl_akhir = $request->dateTo;
        $user = Auth::user()->name;

        if ($request->ajax()) {
            $data_input = DB::select("
            select
            a.id,
            no_mut,
            tgl_mut,
            concat((DATE_FORMAT(tgl_mut,  '%d')), '-', left(DATE_FORMAT(tgl_mut,  '%M'),3),'-',DATE_FORMAT(tgl_mut,  '%Y')) tgl_mut_fix,
            buyer,
            ws,
            brand,
            styleno,
            color,
            size,
            a.qty_mut,
            a.grade,
            lokasi_asal,
			no_carton_asal,
            lokasi_tujuan,
            no_carton_tujuan,
            a.created_by,
            created_at,
			bpb.no_trans,
			bppb.no_trans_out
            from fg_stok_mutasi_log a
            inner join master_sb_ws m on a.id_so_det = m.id_so_det
            inner join (select no_mutasi,no_trans from fg_stok_bpb where cancel = 'N' and mutasi = 'Y' group by no_trans) bpb on a.no_mut = bpb.no_mutasi
            inner join (select no_mutasi,no_trans_out from fg_stok_bppb where cancel = 'N' and mutasi = 'Y' group by no_trans_out) bppb on a.no_mut = bpb.no_mutasi
            where tgl_mut >= '$tgl_awal' and tgl_mut <= '$tgl_akhir'
            order by substr(no_trans,14) desc
            ");

            return DataTables::of($data_input)->toJson();
        }

        return view('fg-stock.mutasi_fg_stock', ['page' => 'dashboard-fg-stock', "subPageGroup" => "fgstock-mutasi", "subPage" => "mutasi-fg-stock"]);
    }

    public function store(Request $request)
    {
        $timestamp = Carbon::now();
        $user               =  Auth::user()->name;
        $JmlArray         = $_POST['txtqty'];
        $id_so_detArray         = $_POST['id_so_det'];
        $no_cartonArray         = $_POST['no_carton'];
        $gradeArray         = $_POST['grade'];
        $lokasi_asal             = $request->cbolok_asal;
        $lokasi_tuj             = $request->cbolok_tuj;
        $no_carton_tuj             = $request->txtno_carton_tuj;
        $tgl_pengeluaran = Carbon::now()->isoFormat('YYYY-MM-DD');

        $tahun = date('Y', strtotime($tgl_pengeluaran));
        $no = date('my', strtotime($tgl_pengeluaran));
        $kode = 'FGS/MUT/';
        $cek_nomor = DB::select("
        select max(right(no_mut,5))nomor from fg_stok_mutasi_log where year(tgl_mut) = '" . $tahun . "'
        ");
        $nomor_tr = $cek_nomor[0]->nomor;
        $urutan = (int)($nomor_tr);
        $urutan++;
        $kodepay = sprintf("%05s", $urutan);
        $kode_trans = $kode . $no . '/' . $kodepay;

        $kode_bppb = 'FGS/OUT/';
        $cek_nomor_bppb = DB::select("
        select max(right(no_trans_out,5))nomor from fg_stok_bppb where year(tgl_pengeluaran) = '" . $tahun . "'
        ");
        $nomor_tr_bppb = $cek_nomor_bppb[0]->nomor;
        $urutan_bppb = (int)($nomor_tr_bppb);
        $urutan_bppb++;
        $kodepay_bppb = sprintf("%05s", $urutan_bppb);
        $kode_trans_bppb = $kode_bppb . $no . '/' . $kodepay_bppb;

        $kode_bpb = 'FGS/IN/';
        $cek_nomor_bpb = DB::select("
        select max(right(no_trans,5))nomor from fg_stok_bpb where year(tgl_terima) = '" . $tahun . "'
        ");
        $nomor_tr_bpb = $cek_nomor_bpb[0]->nomor;
        $urutan_bpb = (int)($nomor_tr_bpb);
        $urutan_bpb++;
        $kodepay_bpb = sprintf("%05s", $urutan_bpb);
        $kode_trans_bpb = $kode_bpb . $no . '/' . $kodepay_bpb;



        foreach ($JmlArray as $key => $value) {
            if ($value != '0' && $value != '') {
                $txtqty         = $JmlArray[$key];
                $txtid_so_det   = $id_so_detArray[$key];
                $txtno_carton   = $no_cartonArray[$key];
                $txtgrade       = $gradeArray[$key]; {
                    $insert_mut =  DB::insert("
                insert into fg_stok_mutasi_log(no_mut,tgl_mut,id_so_det,qty_mut,grade,lokasi_asal,no_carton_asal,lokasi_tujuan,no_carton_tujuan,cancel,created_by,created_at,updated_at)
                values('$kode_trans','$tgl_pengeluaran','$txtid_so_det','$txtqty','$txtgrade','$lokasi_asal','$txtno_carton','$lokasi_tuj','$no_carton_tuj','N','$user','$timestamp','$timestamp')");
                }
                $insert_bppb =  DB::insert("
                insert into fg_stok_bppb(no_trans_out,tgl_pengeluaran,id_so_det,qty_out,grade,no_carton,lokasi,tujuan,mutasi,no_mutasi,cancel,created_by,created_at,updated_at)
                values('$kode_trans_bppb','$tgl_pengeluaran','$txtid_so_det','$txtqty','$txtgrade','$txtno_carton','$lokasi_asal','MUTASI INTERNAL','Y','$kode_trans','N','$user','$timestamp','$timestamp')");
                $insert_bpb =  DB::insert("
                insert into fg_stok_bpb(no_trans,tgl_terima,id_so_det,qty,grade,no_carton,lokasi,sumber_pemasukan,mutasi,no_mutasi,cancel,created_by,created_at,updated_at)
                values('$kode_trans_bpb','$tgl_pengeluaran','$txtid_so_det','$txtqty','$txtgrade','$no_carton_tuj','$lokasi_tuj','MUTASI INTERNAL','Y','$kode_trans','N','$user','$timestamp','$timestamp')");
            }
        }

        if ($insert_mut != '') {
            return array(
                "status" => 900,
                "message" => 'No Transaksi :
                 ' . $kode_trans . '
                 Sudah Terbuat',
                "additional" => [],
            );
        } else {
            return array(
                "status" => 200,
                "message" => 'Tidak ada Data',
                "additional" => [],
            );
        }
    }

    public function create(Request $request)
    {
        $user = Auth::user()->name;

        $data_lok_asal = DB::select("select kode_lok_fg_stok isi , kode_lok_fg_stok tampil from fg_stok_master_lok");

        $data_lok_tuj = DB::select("select kode_lok_fg_stok isi , kode_lok_fg_stok tampil from fg_stok_master_lok");

        return view('fg-stock.create_mutasi_fg_stock', [
            'page' => 'dashboard-fg-stock', "subPageGroup" => "fgstock-mutasi", "subPage" => "mutasi-fg-stock",
            "data_lok_asal" => $data_lok_asal, "data_lok_tuj" => $data_lok_tuj, "user" => $user
        ]);
    }

    public function getno_karton_asal(Request $request)
    {
        $data_no_karton_asal = DB::select("
        select lokasi,
        no_carton isi,
        sum(s.qty_in) - sum(s.qty_out) saldo,
                    concat (no_carton, ' ( ',sum(s.qty_in) - sum(s.qty_out), ' )' ) tampil
        from
        (
        select lokasi,no_carton,a.id_so_det,sum(a.qty) qty_in, '0' qty_out,grade  from fg_stok_bpb a
        inner join master_sb_ws m on a.id_so_det = m.id_so_det
        where lokasi = '" . $request->cbolok_asal . "'
        group by no_carton, a.id_so_det, a.grade
        union
        select lokasi,no_carton,a.id_so_det,'0' qty_in,sum(a.qty_out) qty_out,grade  from fg_stok_bppb a
        inner join master_sb_ws m on a.id_so_det = m.id_so_det
        where lokasi = '" . $request->cbolok_asal . "'
        group by no_carton, a.id_so_det, a.grade
        )
        s
        inner join master_sb_ws m on s.id_so_det = m.id_so_det
        group by no_carton
        having sum(s.qty_in) - sum(s.qty_out) != '0'
        ");

        $html = "<option value=''  selected='true' disabled='true'>Pilih No Karton Asal</option>";

        foreach ($data_no_karton_asal as $datanokartonasal) {
            $html .= " <option value='" . $datanokartonasal->isi . "'>" . $datanokartonasal->tampil . "</option> ";
        }

        return $html;
    }

    public function export_excel_mutasi_int_fg_stok(Request $request)
    {
        return Excel::download(new ExportLaporanFGStokMutasiInternal($request->from, $request->to), 'Laporan_Mutasi_Internal_FG_Stok.xlsx');
    }
}
