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
                SELECT
                    a.id,
                    no_mut,
                    tgl_mut,
                    CONCAT(
                        DATE_FORMAT(tgl_mut, '%d'), '-',
                        LEFT(DATE_FORMAT(tgl_mut, '%M'), 3), '-',
                        DATE_FORMAT(tgl_mut, '%Y')
                    ) AS tgl_mut_fix,
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
                FROM fg_stok_mutasi_log a
                INNER JOIN master_sb_ws m ON a.id_so_det = m.id_so_det
                INNER JOIN (
                    SELECT
                        no_mutasi,
                        no_trans_out
                    FROM fg_stok_bppb
                    WHERE cancel = 'N'
                        AND mutasi = 'Y'
                    GROUP BY no_mutasi, no_trans_out
                ) bppb
                    ON a.no_mut = bppb.no_mutasi
                INNER JOIN (
                    SELECT
                        no_mutasi,
                        no_trans
                    FROM
                    (
                        SELECT
                            no_mutasi,
                            no_trans
                        FROM fg_stok_bpb
                        WHERE cancel = 'N'
                            AND mutasi = 'Y'

                        UNION ALL

                        SELECT
                            no_mutasi,
                            no_trans
                        FROM fg_stok_bpb_scan
                        WHERE cancel = 'N'
                            AND mutasi = 'Y'

                    ) bpb_all

                    GROUP BY no_mutasi, no_trans
                ) bpb
                    ON a.no_mut = bpb.no_mutasi
                WHERE tgl_mut BETWEEN '$tgl_awal' AND '$tgl_akhir'
                ORDER BY SUBSTR(no_trans, 14) DESC
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
        $sourceTableArray = $_POST['source_table'];
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

        $kode_bpb_scan = 'FGS/SCAN/IN/';
        $cek_nomor_bpb_scan = DB::select("
        select max(right(no_trans,5))nomor from fg_stok_bpb_scan where year(tgl_terima) = '" . $tahun . "'
        ");
        $nomor_tr_bpb_scan = $cek_nomor_bpb_scan[0]->nomor;
        $urutan_bpb_scan = (int)($nomor_tr_bpb_scan);
        $urutan_bpb_scan++;
        $kodepay_bpb_scan = sprintf("%05s", $urutan_bpb_scan);
        $kode_trans_bpb_scan = $kode_bpb_scan . $no . '/' . $kodepay_bpb_scan;

        foreach ($JmlArray as $key => $value) {
            if ($value != '0' && $value != '') {
                $txtqty         = $JmlArray[$key];
                $txtid_so_det   = $id_so_detArray[$key];
                $txtno_carton   = $no_cartonArray[$key];
                $txtgrade       = $gradeArray[$key];
                $source_table = $sourceTableArray[$key]; 
                
                $insert_mut =  DB::insert("
                insert into fg_stok_mutasi_log(no_mut,tgl_mut,id_so_det,qty_mut,grade,lokasi_asal,no_carton_asal,lokasi_tujuan,no_carton_tujuan,cancel,created_by,created_at,updated_at)
                values('$kode_trans','$tgl_pengeluaran','$txtid_so_det','$txtqty','$txtgrade','$lokasi_asal','$txtno_carton','$lokasi_tuj','$no_carton_tuj','N','$user','$timestamp','$timestamp')");
                $insert_bppb =  DB::insert("
                insert into fg_stok_bppb(no_trans_out,tgl_pengeluaran,id_so_det,qty_out,grade,no_carton,lokasi,tujuan,mutasi,no_mutasi,cancel,created_by,created_at,updated_at)
                values('$kode_trans_bppb','$tgl_pengeluaran','$txtid_so_det','$txtqty','$txtgrade','$txtno_carton','$lokasi_asal','MUTASI INTERNAL','Y','$kode_trans','N','$user','$timestamp','$timestamp')");
                if ($source_table == 'BPB') {
                    $insert_bpb =  DB::insert("
                    insert into fg_stok_bpb(no_trans,tgl_terima,id_so_det,qty,grade,no_carton,lokasi,sumber_pemasukan,mutasi,no_mutasi,cancel,created_by,created_at,updated_at)
                    values('$kode_trans_bpb','$tgl_pengeluaran','$txtid_so_det','$txtqty','$txtgrade','$no_carton_tuj','$lokasi_tuj','MUTASI INTERNAL','Y','$kode_trans','N','$user','$timestamp','$timestamp')");
                }else if($source_table == 'BPB_SCAN'){
                    $insert_bpb_scan =  DB::insert("
                    insert into fg_stok_bpb_scan(no_trans,tgl_terima,id_so_det,qty,grade,no_carton,lokasi,sumber_pemasukan,mutasi,no_mutasi,cancel,created_by,created_at,updated_at)
                    values('$kode_trans_bpb_scan','$tgl_pengeluaran','$txtid_so_det','$txtqty','$txtgrade','$no_carton_tuj','$lokasi_tuj','MUTASI INTERNAL','Y','$kode_trans','N','$user','$timestamp','$timestamp')");
                }
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
            SELECT
                lokasi,
                no_carton AS isi,
                SUM(s.qty_in) - SUM(s.qty_out) AS saldo,
                CONCAT(
                    no_carton,
                    ' ( ',
                    SUM(s.qty_in) - SUM(s.qty_out),
                    ' )'
                ) AS tampil
            FROM
            (
                SELECT
                    lokasi,
                    no_carton,
                    a.id_so_det,
                    SUM(a.qty) AS qty_in,
                    0 AS qty_out,
                    grade
                FROM fg_stok_bpb a
                INNER JOIN master_sb_ws m
                    ON a.id_so_det = m.id_so_det
                WHERE lokasi = '" . $request->cbolok_asal . "'
                GROUP BY no_carton, a.id_so_det, a.grade

                UNION ALL

                SELECT
                    lokasi,
                    no_carton,
                    a.id_so_det,
                    SUM(a.qty) AS qty_in,
                    0 AS qty_out,
                    grade
                FROM fg_stok_bpb_scan a
                INNER JOIN master_sb_ws m
                    ON a.id_so_det = m.id_so_det
                WHERE lokasi = '" . $request->cbolok_asal . "'
                GROUP BY no_carton, a.id_so_det, a.grade

                UNION ALL

                SELECT
                    lokasi,
                    no_carton,
                    a.id_so_det,
                    0 AS qty_in,
                    SUM(a.qty_out) AS qty_out,
                    grade
                FROM fg_stok_bppb a
                INNER JOIN master_sb_ws m
                    ON a.id_so_det = m.id_so_det
                WHERE lokasi = '" . $request->cbolok_asal . "'
                GROUP BY no_carton, a.id_so_det, a.grade

            ) s
            INNER JOIN master_sb_ws m ON s.id_so_det = m.id_so_det
            GROUP BY no_carton
            HAVING SUM(s.qty_in) - SUM(s.qty_out) != 0
        ");

        $html = "<option value=''  selected='true' disabled='true'>Pilih No Karton Asal</option>";

        foreach ($data_no_karton_asal as $datanokartonasal) {
            $html .= " <option value='" . $datanokartonasal->isi . "'>" . $datanokartonasal->tampil . "</option> ";
        }

        return $html;
    }


    public function show_det_mutasi(Request $request)
    {
        $user = Auth::user()->name;
        if ($request->ajax()) {

            $data_det = DB::select("
                SELECT
                    lokasi,
                    no_carton,
                    s.id_so_det,
                    ws,
                    SUM(s.qty_in) - SUM(s.qty_out) AS saldo,
                    m.buyer,
                    m.color,
                    m.size,
                    m.styleno,
                    m.brand,
                    s.grade,
                    CONCAT(
                        s.id_so_det, '_',
                        no_carton, '_',
                        grade
                    ) AS kode,
                    CASE
                        WHEN SUM(CASE WHEN s.source_table = 'BPB_SCAN' THEN 1 ELSE 0 END) > 0
                            THEN 'BPB_SCAN'
                        ELSE 'BPB'
                    END AS source_table
                FROM
                (
                    SELECT
                        lokasi,
                        no_carton,
                        a.id_so_det,
                        SUM(a.qty) AS qty_in,
                        0 AS qty_out,
                        grade,
                        'BPB' AS source_table
                    FROM fg_stok_bpb a
                    INNER JOIN master_sb_ws m
                        ON a.id_so_det = m.id_so_det
                    WHERE lokasi = '" . $request->cbolok_asal . "'
                        AND no_carton = '" . $request->cbono_carton_asal . "'
                    GROUP BY no_carton, a.id_so_det, a.grade

                    UNION ALL

                    SELECT
                        lokasi,
                        no_carton,
                        a.id_so_det,
                        SUM(a.qty) AS qty_in,
                        0 AS qty_out,
                        grade,
                        'BPB_SCAN' AS source_table
                    FROM fg_stok_bpb_scan a
                    INNER JOIN master_sb_ws m
                        ON a.id_so_det = m.id_so_det
                    WHERE lokasi = '" . $request->cbolok_asal . "'
                        AND no_carton = '" . $request->cbono_carton_asal . "'
                    GROUP BY no_carton, a.id_so_det, a.grade

                    UNION ALL

                    SELECT
                        lokasi,
                        no_carton,
                        a.id_so_det,
                        0 AS qty_in,
                        SUM(a.qty_out) AS qty_out,
                        grade,
                        'BPPB' AS source_table
                    FROM fg_stok_bppb a
                    INNER JOIN master_sb_ws m
                        ON a.id_so_det = m.id_so_det
                    WHERE lokasi = '" . $request->cbolok_asal . "'
                        AND no_carton = '" . $request->cbono_carton_asal . "'
                    GROUP BY no_carton, a.id_so_det, a.grade
                ) s
                INNER JOIN master_sb_ws m ON s.id_so_det = m.id_so_det
                GROUP BY no_carton, s.id_so_det, s.grade
            ");

            return DataTables::of($data_det)->toJson();
        }
    }


    public function export_excel_mutasi_int_fg_stok(Request $request)
    {
        return Excel::download(new ExportLaporanFGStokMutasiInternal($request->from, $request->to), 'Laporan_Mutasi_Internal_FG_Stok.xlsx');
    }
}
