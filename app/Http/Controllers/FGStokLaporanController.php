<?php

namespace App\Http\Controllers;

use App\Exports\ExportLaporanFGStokMutasi;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportLaporanPenerimaanFGStokBPB;
use Illuminate\Http\JsonResponse;

class FGStokLaporanController extends Controller
{
    public function index(Request $request)
    {
        $tgl_awal = $request->dateFrom;
        $tgl_akhir = $request->dateTo;
        $user = Auth::user()->name;
        $data_laporan = DB::select("select 'Penerimaan' isi, 'PENERIMAAN' tampil
        union
        select 'Pengeluaran' isi, 'PENGELUARAN' tampil
        union
        select 'Mutasi' isi, 'MUTASI' tampil");

        return view('fg-stock.laporan_fg_stock', ['page' => 'dashboard-fg-stock', "subPageGroup" => "fgstock-laporan", "subPage" => "laporan-fg-stock", "data_laporan" => $data_laporan]);
    }

    public function export_excel_mutasi_fg_stok(Request $request)
    {
        ini_set('memory_limit', '1024M');
        return Excel::download(new ExportLaporanFGStokMutasi($request->from, $request->to), 'Laporan_Mutasi FG_Stok.xlsx');
    }


    // api
    public function show_fg_stok_mutasi(Request $request)
    {
        ini_set("max_execution_time", 3600);

        // $user = Auth::user()->name;
        $tgl_awal = $request->tgl_awal ? $request->tgl_awal : date('Y-m-d');
        $tgl_akhir = $request->tgl_akhir ? $request->tgl_akhir : date('Y-m-d');

        $data_preview = DB::select("select mt.id_so_det,
                sum(qty_awal) qty_awal,
                sum(qty_in) qty_in,
                sum(qty_out) qty_out,
                sum(qty_awal) + sum(qty_in) - sum(qty_out) saldo_akhir,
                grade,
                lokasi,
                no_carton,
                buyer,
                color,
                m.size,
                ws,
                brand,
                styleno,
                m.product_group,
                m.product_item,
                m.dest,
                '$tgl_awal',
                '$tgl_akhir'
                from
                (
                    select id_so_det,sum(qty_in) - sum(qty_out) qty_awal,'0' qty_in,'0' qty_out, grade, lokasi, no_carton
                    from
                    (
                    select id_so_det,sum(qty) qty_in,'0' qty_out,grade, lokasi, no_carton
                    from fg_stok_bpb
                    where tgl_terima < '$tgl_awal'
                    group by id_so_det, grade, lokasi, no_carton
                    UNION
                    select id_so_det,'0' qty_in,sum(qty_out) qty_out,grade, lokasi, no_carton
                    from fg_stok_bppb
                    where tgl_pengeluaran < '$tgl_awal'
                    group by id_so_det, grade, lokasi, no_carton
                    ) sa
                    group by id_so_det, grade, lokasi, no_carton
                union
                select id_so_det,'0' qty_awal,sum(qty) qty_in,'0' qty_out,grade, lokasi, no_carton
                from fg_stok_bpb
                where tgl_terima >= '$tgl_awal' and tgl_terima <= '$tgl_akhir'
                group by id_so_det, grade, lokasi, no_carton
                union
                select id_so_det,'0' qty_awal,'0' qty_in,sum(qty_out) qty_out,grade, lokasi, no_carton
                from fg_stok_bppb
                where tgl_pengeluaran >= '$tgl_awal' and tgl_pengeluaran <= '$tgl_akhir'
                group by id_so_det, grade, lokasi, no_carton
                )
                mt
                left join master_sb_ws m on mt.id_so_det = m.id_so_det
                left join master_size_new ms on m.size = ms.size
                group by mt.id_so_det, grade, lokasi, no_carton
                order by buyer asc, color asc, ms.urutan asc
            ");

        return response()->json([
            'tanggal' => $tgl_awal . " - " . $tgl_akhir,
            'data' => $data_preview,
            'message' => 'Succeed'
        ], JsonResponse::HTTP_OK);
    }

    public function rep_mutasi_fg_stock(Request $request)
    {
        $tgl_awal = $request->dateFrom;
        $tgl_akhir = $request->dateTo;
        $user = Auth::user()->name;

        if ($request->ajax()) {
            $data_input = DB::select("
                select mt.id_so_det,
                sum(qty_awal) qty_awal,
                sum(qty_in) qty_in,
                sum(qty_out) qty_out,
                sum(qty_awal) + sum(qty_in) - sum(qty_out) saldo_akhir,
                grade,
                lokasi,
                no_carton,
                buyer,
                color,
                m.size,
                ws,
                brand,
                styleno,
                m.product_group,
                m.product_item,
                m.dest
                from
                (
                    select id_so_det,sum(qty_in) - sum(qty_out) qty_awal,'0' qty_in,'0' qty_out, grade, lokasi, no_carton
                    from
                    (
                    select id_so_det,sum(qty) qty_in,'0' qty_out,grade, lokasi, no_carton
                    from fg_stok_bpb
                    where tgl_terima < '$tgl_awal'
                    group by id_so_det, grade, lokasi, no_carton
                    UNION
                    select id_so_det,'0' qty_in,sum(qty_out) qty_out,grade, lokasi, no_carton
                    from fg_stok_bppb
                    where tgl_pengeluaran < '$tgl_awal'
                    group by id_so_det, grade, lokasi, no_carton
                    ) sa
                    group by id_so_det, grade, lokasi, no_carton
                union
                select id_so_det,'0' qty_awal,sum(qty) qty_in,'0' qty_out,grade, lokasi, no_carton
                from fg_stok_bpb
                where tgl_terima >= '$tgl_awal' and tgl_terima <= '$tgl_akhir'
                group by id_so_det, grade, lokasi, no_carton
                union
                select id_so_det,'0' qty_awal,'0' qty_in,sum(qty_out) qty_out,grade, lokasi, no_carton
                from fg_stok_bppb
                where tgl_pengeluaran >= '$tgl_awal' and tgl_pengeluaran <= '$tgl_akhir'
                group by id_so_det, grade, lokasi, no_carton
                )
                mt
                left join master_sb_ws m on mt.id_so_det = m.id_so_det
                left join master_size_new ms on m.size = ms.size
                group by mt.id_so_det, grade, lokasi, no_carton
                order by buyer asc, color asc, ms.urutan asc
            ");

            return DataTables::of($data_input)->toJson();
        }
    }
}
