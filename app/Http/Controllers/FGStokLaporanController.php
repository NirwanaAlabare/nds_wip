<?php

namespace App\Http\Controllers;

use App\Exports\ExportLaporanFGStokMutasi;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportListLaporanPenerimaanFGStockBPB;
use Illuminate\Http\JsonResponse;
use \avadim\FastExcelLaravel\Excel as FastExcel;

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
        select 'Mutasi' isi, 'MUTASI DETAIL' tampil
        union
        select 'Mutasi Global' isi, 'MUTASI GLOBAL' tampil
        ");

        return view('fg-stock.laporan_fg_stock', ['page' => 'dashboard-fg-stock', "subPageGroup" => "fgstock-laporan", "subPage" => "laporan-fg-stock", "data_laporan" => $data_laporan]);
    }

    public function export_excel_mutasi_fg_stok(Request $request)
    {
        ini_set('memory_limit', '1024M');

        // dd($request->from, $request->to);

        $dateFrom = Carbon::parse($request->from)->format('Y-m-d');
        $dateTo   = Carbon::parse($request->to)->format('Y-m-d');

        return Excel::download(new ExportLaporanFGStokMutasi($dateFrom, $dateTo), 'Laporan_Mutasi FG_Stok.xlsx');
    }

    public function exportExcelMutasiFgStok(Request $request) {
        $dateFrom = Carbon::parse($request->from)->format('Y-m-d');
        $dateTo   = Carbon::parse($request->to)->format('Y-m-d');

        $excel = FastExcel::create('data');
        $sheet = $excel->getSheet();

        $area = $sheet->beginArea();

        $sheet->writeTo('A1', 'Laporan Mutasi Barang Jadi Stok',);
        $sheet->mergeCells('A1:Q1');
        $sheet->writeTo('A2', $request->from." - ".$request->to,);
        $sheet->mergeCells('A2:Q2');

        $sheet->writeTo('A4', "No.")->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('B4', "WS")->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('C4', "styleno")->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('D4', "product_group")->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('E4', "product_item")->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('F4', "color")->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('G4', "Style")->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('H4', "Color")->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('I4', "size")->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('J4', "Grade")->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('K4', "Lokasi")->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('L4', "No. Carton")->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('M4', "Saldo Awal")->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('N4', "Penerimaan")->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('O4', "Pengeluaran")->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('P4', "Saldo Akhir")->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeAreas();

        $data = DB::select("
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
            m.dest,
            '$dateFrom',
            '$dateTo'
            from
            (
                select id_so_det,sum(qty_in) - sum(qty_out) qty_awal,'0' qty_in,'0' qty_out, grade, lokasi, no_carton
                from
                (
                select id_so_det,sum(qty) qty_in,'0' qty_out,grade, lokasi, no_carton
                from fg_stok_bpb
                where tgl_terima < '$dateFrom'
                group by id_so_det, grade, lokasi, no_carton
                UNION
                select id_so_det,'0' qty_in,sum(qty_out) qty_out,grade, lokasi, no_carton
                from fg_stok_bppb
                where tgl_pengeluaran < '$dateFrom'
                group by id_so_det, grade, lokasi, no_carton
                ) sa
                group by id_so_det, grade, lokasi, no_carton
            union
            select id_so_det,'0' qty_awal,sum(qty) qty_in,'0' qty_out,grade, lokasi, no_carton
            from fg_stok_bpb
            where tgl_terima >= '$dateFrom' and tgl_terima <= '$dateTo'
            group by id_so_det, grade, lokasi, no_carton
            union
            select id_so_det,'0' qty_awal,'0' qty_in,sum(qty_out) qty_out,grade, lokasi, no_carton
            from fg_stok_bppb
            where tgl_pengeluaran >= '$dateFrom' and tgl_pengeluaran <= '$dateTo'
            group by id_so_det, grade, lokasi, no_carton
            )
            mt
            left join master_sb_ws m on mt.id_so_det = m.id_so_det
            left join master_size_new ms on m.size = ms.size
            group by mt.id_so_det, grade, lokasi, no_carton
            order by buyer asc, color asc, ms.urutan asc
        ");

        $i = 0;
        foreach ($data as $row) {
            $i++;

            $rowArr = [
                $i,
                $row->ws ?? '-',
                $row->styleno ?? '-',
                $row->product_group ?? '-',
                $row->product_item ?? '-',
                $row->color ?? '-',
                $row->size ?? '-',
                $row->grade ?? '-',
                $row->lokasi ?? '-',
                $row->no_carton ?? '-',
                $row->qty_awal ?? 0,
                $row->qty_in ?? 0,
                $row->qty_out ?? 0,
                $row->saldo_akhir ?? 0,
            ];

            $sheet->writeRow($rowArr)->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        }

        $filename = date('Y-m-d') . ' MutasiFgStok.xlsx';

        return $excel->download($filename);
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
                SELECT
                    mt.id_so_det,
                    SUM(qty_awal) AS qty_awal,
                    SUM(qty_in) AS qty_in,
                    SUM(qty_out) AS qty_out,
                    SUM(qty_awal) + SUM(qty_in) - SUM(qty_out) AS saldo_akhir,
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
                FROM
                (
                    SELECT
                        id_so_det,
                        SUM(qty_in) - SUM(qty_out) AS qty_awal,
                        0 AS qty_in,
                        0 AS qty_out,
                        grade,
                        lokasi,
                        no_carton
                    FROM
                    (
                        SELECT
                            id_so_det,
                            SUM(qty) AS qty_in,
                            0 AS qty_out,
                            grade,
                            lokasi,
                            no_carton
                        FROM fg_stok_bpb
                        WHERE tgl_terima < '$tgl_awal'
                        GROUP BY id_so_det, grade, lokasi, no_carton

                        UNION ALL

                        SELECT
                            id_so_det,
                            SUM(qty) AS qty_in,
                            0 AS qty_out,
                            grade,
                            lokasi,
                            no_carton
                        FROM fg_stok_bpb_scan
                        WHERE tgl_terima < '$tgl_awal'
                        GROUP BY id_so_det, grade, lokasi, no_carton

                        UNION ALL

                        SELECT
                            id_so_det,
                            0 AS qty_in,
                            SUM(qty_out) AS qty_out,
                            grade,
                            lokasi,
                            no_carton
                        FROM fg_stok_bppb
                        WHERE tgl_pengeluaran < '$tgl_awal'
                        GROUP BY id_so_det, grade, lokasi, no_carton

                    ) sa
                    GROUP BY id_so_det, grade, lokasi, no_carton

                    UNION ALL

                    SELECT
                        id_so_det,
                        0 AS qty_awal,
                        SUM(qty) AS qty_in,
                        0 AS qty_out,
                        grade,
                        lokasi,
                        no_carton
                    FROM fg_stok_bpb
                    WHERE tgl_terima BETWEEN '$tgl_awal' AND '$tgl_akhir'
                    GROUP BY id_so_det, grade, lokasi, no_carton

                    UNION ALL

                    SELECT
                        id_so_det,
                        0 AS qty_awal,
                        SUM(qty) AS qty_in,
                        0 AS qty_out,
                        grade,
                        lokasi,
                        no_carton
                    FROM fg_stok_bpb_scan
                    WHERE tgl_terima BETWEEN '$tgl_awal' AND '$tgl_akhir'
                    GROUP BY id_so_det, grade, lokasi, no_carton

                    UNION ALL

                    SELECT
                        id_so_det,
                        0 AS qty_awal,
                        0 AS qty_in,
                        SUM(qty_out) AS qty_out,
                        grade,
                        lokasi,
                        no_carton
                    FROM fg_stok_bppb
                    WHERE tgl_pengeluaran BETWEEN '$tgl_awal' AND '$tgl_akhir'
                    GROUP BY id_so_det, grade, lokasi, no_carton
                ) mt
                LEFT JOIN master_sb_ws m ON mt.id_so_det = m.id_so_det
                LEFT JOIN master_size_new ms ON m.size = ms.size
                GROUP BY mt.id_so_det, grade, lokasi, no_carton
                ORDER BY buyer ASC, color ASC, ms.urutan ASC
            ");

            return DataTables::of($data_input)->toJson();
        }
    }

    public function export_excel_mutasi_fg_stock(Request $request)
    {
        $tgl_awal = $request->from;
        $tgl_akhir = $request->to;
        $data = DB::select("
            SELECT
                ROW_NUMBER() OVER (
                    ORDER BY buyer ASC, color ASC, ms.urutan ASC
                ) AS no_urut,

                mt.id_so_det,

                SUM(qty_awal) AS qty_awal,
                SUM(qty_in) AS qty_in,
                SUM(qty_out) AS qty_out,
                SUM(qty_awal) + SUM(qty_in) - SUM(qty_out) AS saldo_akhir,
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
            FROM
            (
                SELECT
                    id_so_det,
                    SUM(qty_in) - SUM(qty_out) AS qty_awal,
                    0 AS qty_in,
                    0 AS qty_out,
                    grade,
                    lokasi,
                    no_carton
                FROM
                (
                    SELECT
                        id_so_det,
                        SUM(qty) AS qty_in,
                        0 AS qty_out,
                        grade,
                        lokasi,
                        no_carton
                    FROM fg_stok_bpb
                    WHERE tgl_terima < '$tgl_awal'
                    GROUP BY id_so_det, grade, lokasi, no_carton

                    UNION ALL

                    SELECT
                        id_so_det,
                        SUM(qty) AS qty_in,
                        0 AS qty_out,
                        grade,
                        lokasi,
                        no_carton
                    FROM fg_stok_bpb_scan
                    WHERE tgl_terima < '$tgl_awal'
                    GROUP BY id_so_det, grade, lokasi, no_carton

                    UNION ALL

                    SELECT
                        id_so_det,
                        0 AS qty_in,
                        SUM(qty_out) AS qty_out,
                        grade,
                        lokasi,
                        no_carton
                    FROM fg_stok_bppb
                    WHERE tgl_pengeluaran < '$tgl_awal'
                    GROUP BY id_so_det, grade, lokasi, no_carton

                ) sa
                GROUP BY id_so_det, grade, lokasi, no_carton

                UNION ALL

                SELECT
                    id_so_det,
                    0 AS qty_awal,
                    SUM(qty) AS qty_in,
                    0 AS qty_out,
                    grade,
                    lokasi,
                    no_carton
                FROM fg_stok_bpb
                WHERE tgl_terima BETWEEN '$tgl_awal' AND '$tgl_akhir'
                GROUP BY id_so_det, grade, lokasi, no_carton

                UNION ALL

                SELECT
                    id_so_det,
                    0 AS qty_awal,
                    SUM(qty) AS qty_in,
                    0 AS qty_out,
                    grade,
                    lokasi,
                    no_carton
                FROM fg_stok_bpb_scan
                WHERE tgl_terima BETWEEN '$tgl_awal' AND '$tgl_akhir'
                GROUP BY id_so_det, grade, lokasi, no_carton

                UNION ALL

                SELECT
                    id_so_det,
                    0 AS qty_awal,
                    0 AS qty_in,
                    SUM(qty_out) AS qty_out,
                    grade,
                    lokasi,
                    no_carton
                FROM fg_stok_bppb
                WHERE tgl_pengeluaran BETWEEN '$tgl_awal' AND '$tgl_akhir'
                GROUP BY id_so_det, grade, lokasi, no_carton
            ) mt
            LEFT JOIN master_sb_ws m ON mt.id_so_det = m.id_so_det
            LEFT JOIN master_size_new ms ON m.size = ms.size
            GROUP BY mt.id_so_det, grade, lokasi, no_carton
            ORDER BY buyer ASC, color ASC, ms.urutan ASC
        ");

        return response()->json($data);
    }

    public function exportExcelMutasiFgStokSb(Request $request) {
        $dateFrom = Carbon::parse($request->from)->format('Y-m-d');
        $dateTo   = Carbon::parse($request->to)->format('Y-m-d');

        $excel = FastExcel::create('data');
        $sheet = $excel->getSheet();

        $area = $sheet->beginArea();

        $sheet->writeTo('A1', 'KAWASAN BERIKAT PT. NIRWANA ALABARE GARMENT');
        $sheet->writeTo('A2', 'LAPORAN PERTANGGUNGJAWABAN MUTASI BARANG JADI STOK');
        $sheet->writeTo('A3', 'PERIODE '.Carbon::parse($request->from)->format('d F Y')." S/D ".Carbon::parse($request->to)->format('d F Y'));
        $sheet->writeTo('A4', $dateFrom." - ".$dateTo);
        $sheet->mergeCells('A4:N4');

        $sheet->writeTo('A6', "No.")->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('B6', "WS")->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('C6', "Style")->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('D6', "ID SO Det")->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('E6', "Product Group")->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('F6', "Product Item")->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('G6', "Color")->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('H6', "Size")->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('I6', "Grade")->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('J6', "Lokasi")->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('K6', "No. Carton")->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('L6', "Saldo Awal")->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('M6', "Penerimaan")->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('N6', "Pengeluaran")->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('O6', "Saldo Akhir")->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        // $sheet->writeTo('M4', "Saldo Awal")->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        // $sheet->writeTo('N4', "Penerimaan")->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        // $sheet->writeTo('O4', "Pengeluaran")->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        // $sheet->writeTo('P4', "Saldo Akhir")->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeAreas();

        $data = DB::select("select mt.id_so_det,
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
            '$dateFrom',
            '$dateTo'
            from
            (
                select id_so_det,sum(qty_in) - sum(qty_out) qty_awal,'0' qty_in,'0' qty_out, grade, lokasi, no_carton
                from
                (
                select id_so_det,sum(qty) qty_in,'0' qty_out,grade, lokasi, no_carton
                from fg_stok_bpb
                where tgl_terima < '$dateFrom'
                group by id_so_det, grade, lokasi, no_carton
                UNION
                select id_so_det,'0' qty_in,sum(qty_out) qty_out,grade, lokasi, no_carton
                from fg_stok_bppb
                where tgl_pengeluaran < '$dateFrom'
                group by id_so_det, grade, lokasi, no_carton
                ) sa
                group by id_so_det, grade, lokasi, no_carton
            union
            select id_so_det,'0' qty_awal,sum(qty) qty_in,'0' qty_out,grade, lokasi, no_carton
            from fg_stok_bpb
            where tgl_terima >= '$dateFrom' and tgl_terima <= '$dateTo'
            group by id_so_det, grade, lokasi, no_carton
            union
            select id_so_det,'0' qty_awal,'0' qty_in,sum(qty_out) qty_out,grade, lokasi, no_carton
            from fg_stok_bppb
            where tgl_pengeluaran >= '$dateFrom' and tgl_pengeluaran <= '$dateTo'
            group by id_so_det, grade, lokasi, no_carton
            )
            mt
            left join master_sb_ws m on mt.id_so_det = m.id_so_det
            left join master_size_new ms on m.size = ms.size
            group by mt.id_so_det, grade, lokasi, no_carton
            order by buyer asc, color asc, ms.urutan asc
        ");

        $i = 0;
        foreach ($data as $row) {
            $i++;

            $rowArr = [
                $i,
                $row->ws ?? '-',
                $row->styleno ?? '-',
                $row->id_so_det ?? '-',
                $row->product_group ?? '-',
                $row->product_item ?? '-',
                $row->color ?? '-',
                $row->size ?? '-',
                $row->grade ?? '-',
                $row->lokasi ?? '-',
                $row->no_carton ?? '-',
                $row->qty_awal ?? 0,
                $row->qty_in ?? 0,
                $row->qty_out ?? 0,
                $row->saldo_akhir ?? 0,
            ];

            $sheet->writeRow($rowArr)->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        }

        $filename = date('Y-m-d') . ' MutasiFgStok.xlsx';

        return $excel->download($filename);
    }

    public function getDataPenerimaan(Request $request){
        $data = DB::select("
            SELECT
                a.id,
                a.no_trans,
                a.tgl_terima,
                CONCAT(
                    DATE_FORMAT(a.tgl_terima, '%d'), '-',
                    LEFT(DATE_FORMAT(a.tgl_terima, '%M'), 3), '-',
                    DATE_FORMAT(a.tgl_terima, '%Y')
                ) AS tgl_terima_fix,
                buyer,
                ws,
                brand,
                styleno,
                color,
                size,
                a.qty,
                a.grade,
                no_carton,
                lokasi,
                sumber_pemasukan,
                created_by,
                created_at
            FROM fg_stok_bpb a
            LEFT JOIN master_sb_ws m
                ON a.id_so_det = m.id_so_det
            WHERE a.tgl_terima BETWEEN '$request->dateFrom' AND '$request->dateTo'

            UNION ALL

            SELECT
                a.id,
                a.no_trans,
                a.tgl_terima,
                CONCAT(
                    DATE_FORMAT(a.tgl_terima, '%d'), '-',
                    LEFT(DATE_FORMAT(a.tgl_terima, '%M'), 3), '-',
                    DATE_FORMAT(a.tgl_terima, '%Y')
                ) AS tgl_terima_fix,
                buyer,
                ws,
                brand,
                styleno,
                color,
                size,
                a.qty,
                a.grade,
                no_carton,
                lokasi,
                sumber_pemasukan,
                created_by,
                created_at
            FROM fg_stok_bpb_scan a
            LEFT JOIN master_sb_ws m
                ON a.id_so_det = m.id_so_det
            WHERE a.tgl_terima BETWEEN '$request->dateFrom' AND '$request->dateTo'

            ORDER BY SUBSTR(no_trans, 13) DESC
        ");

        return DataTables::of($data)->toJson();
    }

    public function exportPenerimaan(Request $request)
    {
        return Excel::download(new ExportListLaporanPenerimaanFGStockBPB($request->from, $request->to), 'Laporan_Penerimaan FG_Stok.xlsx');
    }

    public function rep_mutasi_global_fg_stock(Request $request)
    {
        $tgl_awal = $request->dateFrom;
        $tgl_akhir = $request->dateTo;
        $saldo_awal = '2026-05-01';

        if ($request->ajax()) {
            $data_input = DB::select("WITH 

                saldo_awal AS (
                    SELECT
                        buyer,
                        ws,
                        styleno,
                        color,
                        m.size,
                        SUM(qty_awal) AS qty_awal,
                        SUM(qty_in) AS qty_in,
                        SUM(qty_out) AS qty_out,
                        SUM(qty_awal) + SUM(qty_in) - SUM(qty_out) AS saldo_akhir
                    FROM
                    (
                        SELECT
                            id_so_det,
                            SUM(qty_in) - SUM(qty_out) AS qty_awal,
                            0 AS qty_in,
                            0 AS qty_out,
                            grade,
                            lokasi,
                            no_carton
                        FROM
                        (
                            SELECT
                                id_so_det,
                                SUM(qty) AS qty_in,
                                0 AS qty_out,
                                grade,
                                lokasi,
                                no_carton
                            FROM fg_stok_bpb
                            WHERE tgl_terima < '2026-05-01'
                            GROUP BY id_so_det, grade, lokasi, no_carton

                            UNION ALL

                            SELECT
                                id_so_det,
                                SUM(qty) AS qty_in,
                                0 AS qty_out,
                                grade,
                                lokasi,
                                no_carton
                            FROM fg_stok_bpb_scan
                            WHERE tgl_terima < '2026-05-01'
                            GROUP BY id_so_det, grade, lokasi, no_carton

                            UNION ALL

                            SELECT
                                id_so_det,
                                0 AS qty_in,
                                SUM(qty_out) AS qty_out,
                                grade,
                                lokasi,
                                no_carton
                            FROM fg_stok_bppb
                            WHERE tgl_pengeluaran < '2026-05-01'
                            GROUP BY id_so_det, grade, lokasi, no_carton

                        ) sa
                        GROUP BY id_so_det, grade, lokasi, no_carton
                    ) mt
                    LEFT JOIN master_sb_ws m ON mt.id_so_det = m.id_so_det
                    LEFT JOIN master_size_new ms ON m.size = ms.size
                    GROUP BY mt.id_so_det, grade, lokasi, no_carton
                ),
            
                all_data AS (
                    SELECT
                        x.buyer,
                        x.ws,
                        x.color,
                        x.styleno,
                        x.size,
                        SUM(x.qty_saldo_awal_adjustment_before) AS qty_saldo_awal_adjustment_before,
                        SUM(x.qty_in_qc_reject_before) AS qty_in_qc_reject_before,
                        SUM(x.qty_in_qc_reject) AS qty_in_qc_reject,
                        SUM(x.qty_in_ekspedisi_before) AS qty_in_ekspedisi_before,
                        SUM(x.qty_in_ekspedisi) AS qty_in_ekspedisi,
                        SUM(x.qty_out_qc_reject_before) AS qty_out_qc_reject_before,
                        SUM(x.qty_out_qc_reject) AS qty_out_qc_reject,
                        SUM(x.qty_out_ekspedisi_before) AS qty_out_ekspedisi_before,
                        SUM(x.qty_out_ekspedisi) AS qty_out_ekspedisi,
                        SUM(x.qty_adjustment_before) AS qty_adjustment_before,
                        SUM(x.qty_adjustment) AS qty_adjustment,
                        SUM(x.qty_terima_qc_reject_before) AS qty_terima_qc_reject_before,
                        SUM(x.qty_terima_qc_reject) AS qty_terima_qc_reject,
                        SUM(x.qty_terima_ekspedisi_before) AS qty_terima_ekspedisi_before,
                        SUM(x.qty_terima_ekspedisi) AS qty_terima_ekspedisi,
                        SUM(x.qty_keluar_sewing_before) AS qty_keluar_sewing_before,
                        SUM(x.qty_keluar_sewing) AS qty_keluar_sewing,
                        SUM(x.qty_keluar_qa_before) AS qty_keluar_qa_before,
                        SUM(x.qty_keluar_qa) AS qty_keluar_qa
                    FROM (

                        SELECT
                            buyer,
                            ws,
                            color,
                            styleno,
                            size,
                            saldo_awal.qty_awal AS qty_saldo_awal_adjustment_before,
                            0 qty_in_qc_reject_before,
                            0 qty_in_qc_reject,
                            0 qty_in_ekspedisi_before,
                            0 qty_in_ekspedisi,
                            0 qty_out_qc_reject_before,
                            0 qty_out_qc_reject,
                            0 qty_out_ekspedisi_before,
                            0 qty_out_ekspedisi,
                            0 qty_adjustment_before,
                            0 qty_adjustment,
                            0 qty_terima_qc_reject_before,
                            0 qty_terima_qc_reject,
                            0 qty_terima_ekspedisi_before,
                            0 qty_terima_ekspedisi,
                            0 qty_keluar_sewing_before,
                            0 qty_keluar_sewing,
                            0 qty_keluar_qa_before,
                            0 qty_keluar_qa
                        FROM saldo_awal 

                        UNION ALL

                        SELECT
                            mb.buyer,
                            mb.ws,
                            mb.color,
                            mb.styleno,
                            mb.size,
                            0 qty_saldo_awal_adjustment_before,
                            COUNT(CASE WHEN b.status = 'rejected' AND DATE(a.created_at) >= '".$saldo_awal."' AND DATE(a.created_at) < '".$tgl_awal."' THEN 1 END) AS qty_in_qc_reject_before,
                            COUNT(CASE WHEN b.status = 'rejected' AND date(a.created_at) >= '".$tgl_awal."' THEN 1 END) AS qty_in_qc_reject,
                            0 qty_in_ekspedisi_before,
                            0 qty_in_ekspedisi,
                            0 qty_out_qc_reject_before,
                            0 qty_out_qc_reject,
                            0 qty_out_ekspedisi_before,
                            0 qty_out_ekspedisi,
                            0 qty_adjustment_before,
                            0 qty_adjustment,
                            0 qty_terima_qc_reject_before,
                            0 qty_terima_qc_reject,
                            0 qty_terima_ekspedisi_before,
                            0 qty_terima_ekspedisi,
                            0 qty_keluar_sewing_before,
                            0 qty_keluar_sewing,
                            0 qty_keluar_qa_before,
                            0 qty_keluar_qa
                        FROM signalbit_erp.output_reject_out_detail a
                        INNER JOIN signalbit_erp.output_reject_in b on a.reject_in_id = b.id
                        INNER JOIN signalbit_erp.master_plan mp on b.master_plan_id = mp.id
                        LEFT JOIN (
                            SELECT
                                sd.id as id_so_det,
                                ac.kpno as ws,
                                supplier as buyer,
                                styleno,
                                color,
                                size,
                                dest
                            FROM signalbit_erp.so_det sd
                            INNER JOIN signalbit_erp.so ON sd.id_so = so.id
                            INNER JOIN signalbit_erp.jo_det jd ON so.id = jd.id_so
                            INNER JOIN signalbit_erp.act_costing ac ON so.id_cost = ac.id
                            INNER JOIN signalbit_erp.mastersupplier ms ON ac.id_buyer = ms.id_supplier
                            WHERE jd.cancel = 'N'
                        ) mb on b.so_det_id = mb.id_so_det
                        WHERE DATE(a.created_at) <= '".$tgl_akhir."'
                        AND mp.cancel = 'N'
                        GROUP BY
                        mb.buyer,
                        mb.ws,
                        mb.color,
                        mb.styleno,
                        mb.size

                        UNION ALL

                        SELECT
                            buyer.supplier as buyer,
                            act_costing.kpno ws,
                            masterstyle.color,
                            act_costing.styleno,
                            masterstyle.size,
                            0 qty_saldo_awal_adjustment_before,
                            0 qty_in_qc_reject_before,
                            0 qty_in_qc_reject,
                            IF(bppbdate >= '".$saldo_awal."' AND bppbdate < '".$tgl_awal."', bppb.qty, 0) qty_in_ekspedisi_before,
                            IF(bppbdate >= '".$tgl_awal."', bppb.qty, 0) qty_in_ekspedisi,
                            0 qty_out_qc_reject_before,
                            0 qty_out_qc_reject,
                            0 qty_out_ekspedisi_before,
                            0 qty_out_ekspedisi,
                            0 qty_adjustment_before,
                            0 qty_adjustment,
                            0 qty_terima_qc_reject_before,
                            0 qty_terima_qc_reject,
                            0 qty_terima_ekspedisi_before,
                            0 qty_terima_ekspedisi,
                            0 qty_keluar_sewing_before,
                            0 qty_keluar_sewing,
                            0 qty_keluar_qa_before,
                            0 qty_keluar_qa
                        FROM
                            signalbit_erp.bppb
                        INNER JOIN signalbit_erp.masterstyle ON masterstyle.id_item = bppb.id_item
                        INNER JOIN signalbit_erp.mastersupplier ON mastersupplier.Id_Supplier = bppb.id_supplier
                        LEFT JOIN (select sod.id_so,sod.id id_so_det from signalbit_erp.so_det sod  group by sod.id) tmpjod on tmpjod.id_so_det=bppb.id_so_det
                        LEFT JOIN signalbit_erp.so ON so.id = tmpjod.id_so
                        LEFT JOIN signalbit_erp.act_costing ON act_costing.id = so.id_cost
                        LEFT JOIN signalbit_erp.mastersupplier buyer ON buyer.Id_Supplier = act_costing.id_buyer
                        WHERE mid(bppbno,4,2) in ('FG') AND bppbdate <= '".$tgl_akhir."' AND mastersupplier.supplier = 'BARANG JADI STOCK'

                        UNION ALL

                        SELECT
                            m.buyer,
                            m.ws,
                            m.color,
                            m.styleno,
                            m.size,
                            0 qty_saldo_awal_adjustment_before,
                            0 qty_in_qc_reject_before,
                            0 qty_in_qc_reject,
                            0 qty_in_ekspedisi_before,
                            0 qty_in_ekspedisi,
                            IF(a.tgl_terima >= '".$saldo_awal."' AND a.tgl_terima < '".$tgl_awal."', a.qty, 0) AS qty_out_qc_reject_before,
                            IF(a.tgl_terima >= '".$tgl_awal."', a.qty, 0) AS qty_out_qc_reject,
                            0 qty_out_ekspedisi_before,
                            0 qty_out_ekspedisi,
                            0 qty_adjustment_before,
                            0 qty_adjustment,
                            0 qty_terima_qc_reject_before,
                            0 qty_terima_qc_reject,
                            0 qty_terima_ekspedisi_before,
                            0 qty_terima_ekspedisi,
                            0 qty_keluar_sewing_before,
                            0 qty_keluar_sewing,
                            0 qty_keluar_qa_before,
                            0 qty_keluar_qa
                        FROM fg_stok_bpb a
                        LEFT JOIN master_sb_ws m ON a.id_so_det = m.id_so_det
                        WHERE a.tgl_terima <= '".$tgl_akhir."'
                        AND a.sumber_pemasukan = 'SEWING'

                        UNION ALL

                        SELECT
                            m.buyer,
                            m.ws,
                            m.color,
                            m.styleno,
                            m.size,
                            0 qty_saldo_awal_adjustment_before,
                            0 qty_in_qc_reject_before,
                            0 qty_in_qc_reject,
                            0 qty_in_ekspedisi_before,
                            0 qty_in_ekspedisi,
                            IF(a.tgl_terima >= '".$saldo_awal."' AND a.tgl_terima < '".$tgl_awal."', a.qty, 0) AS qty_out_qc_reject_before,
                            IF(a.tgl_terima >= '".$tgl_awal."', a.qty, 0) AS qty_out_qc_reject,
                            0 qty_out_ekspedisi_before,
                            0 qty_out_ekspedisi,
                            0 qty_adjustment_before,
                            0 qty_adjustment,
                            0 qty_terima_qc_reject_before,
                            0 qty_terima_qc_reject,
                            0 qty_terima_ekspedisi_before,
                            0 qty_terima_ekspedisi,
                            0 qty_keluar_sewing_before,
                            0 qty_keluar_sewing,
                            0 qty_keluar_qa_before,
                            0 qty_keluar_qa
                        FROM fg_stok_bpb_scan a
                        LEFT JOIN master_sb_ws m ON a.id_so_det = m.id_so_det
                        WHERE a.tgl_terima <= '".$tgl_akhir."'
                        AND a.sumber_pemasukan = 'SEWING'

                        UNION ALL

                        SELECT
                            m.buyer,
                            m.ws,
                            m.color,
                            m.styleno,
                            m.size,
                            0 qty_saldo_awal_adjustment_before,
                            0 qty_in_qc_reject_before,
                            0 qty_in_qc_reject,
                            0 qty_in_ekspedisi_before,
                            0 qty_in_ekspedisi,
                            0 qty_out_qc_reject_before,
                            0 qty_out_qc_reject,
                            IF(a.tgl_terima >= '".$saldo_awal."' AND a.tgl_terima < '".$tgl_awal."', a.qty, 0) AS qty_out_ekspedisi_before,
                            IF(a.tgl_terima >= '".$tgl_awal."', a.qty, 0) AS qty_out_ekspedisi,
                            0 qty_adjustment_before,
                            0 qty_adjustment,
                            0 qty_terima_qc_reject_before,
                            0 qty_terima_qc_reject,
                            0 qty_terima_ekspedisi_before,
                            0 qty_terima_ekspedisi,
                            0 qty_keluar_sewing_before,
                            0 qty_keluar_sewing,
                            0 qty_keluar_qa_before,
                            0 qty_keluar_qa
                        FROM fg_stok_bpb a
                        LEFT JOIN master_sb_ws m ON a.id_so_det = m.id_so_det
                        WHERE a.tgl_terima <= '".$tgl_akhir."'
                        AND a.sumber_pemasukan = 'EKSPEDISI'

                        UNION ALL

                        SELECT
                            m.buyer,
                            m.ws,
                            m.color,
                            m.styleno,
                            m.size,
                            0 qty_saldo_awal_adjustment_before,
                            0 qty_in_qc_reject_before,
                            0 qty_in_qc_reject,
                            0 qty_in_ekspedisi_before,
                            0 qty_in_ekspedisi,
                            0 qty_out_qc_reject_before,
                            0 qty_out_qc_reject,
                            IF(a.tgl_terima >= '".$saldo_awal."' AND a.tgl_terima < '".$tgl_awal."', a.qty, 0) AS qty_out_ekspedisi_before,
                            IF(a.tgl_terima >= '".$tgl_awal."', a.qty, 0) AS qty_out_ekspedisi,
                            0 qty_adjustment_before,
                            0 qty_adjustment,
                            0 qty_terima_qc_reject_before,
                            0 qty_terima_qc_reject,
                            0 qty_terima_ekspedisi_before,
                            0 qty_terima_ekspedisi,
                            0 qty_keluar_sewing_before,
                            0 qty_keluar_sewing,
                            0 qty_keluar_qa_before,
                            0 qty_keluar_qa
                        FROM fg_stok_bpb_scan a
                        LEFT JOIN master_sb_ws m ON a.id_so_det = m.id_so_det
                        WHERE a.tgl_terima <= '".$tgl_akhir."'
                        AND a.sumber_pemasukan = 'EKSPEDISI'
                        
                        UNION ALL

                        SELECT
                            buyer,
                            no_ws ws,
                            style styleno,
                            color,
                            size,
                            0 qty_saldo_awal_adjustment_before,
                            0 qty_in_qc_reject_before,
                            0 qty_in_qc_reject,
                            0 qty_in_ekspedisi_before,
                            0 qty_in_ekspedisi,
                            0 qty_out_qc_reject_before,
                            0 qty_out_qc_reject,
                            0 qty_out_ekspedisi_before,
                            0 qty_out_ekspedisi,
                            SUM(IF(tgl_saldo >= '{$saldo_awal}' AND tgl_saldo < '{$tgl_awal}',qty,0)) qty_adjustment_before,
                            SUM(IF(tgl_saldo >= '{$tgl_awal}',qty,0)) qty_adjustment,
                            0 qty_terima_qc_reject_before,
                            0 qty_terima_qc_reject,
                            0 qty_terima_ekspedisi_before,
                            0 qty_terima_ekspedisi,
                            0 qty_keluar_sewing_before,
                            0 qty_keluar_sewing,
                            0 qty_keluar_qa_before,
                            0 qty_keluar_qa
                        FROM
                            wip_adjustment
                        WHERE
                            tgl_saldo <= '{$tgl_akhir}' and
                            type_report = 'TRANSIT_GUDANG_STOK'
                        GROUP BY
                            ws, color, size, panel, part

                        UNION ALL

                        SELECT
                            m.buyer,
                            m.ws,
                            m.color,
                            m.styleno,
                            m.size,
                            0 qty_saldo_awal_adjustment_before,
                            0 qty_in_qc_reject_before,
                            0 qty_in_qc_reject,
                            0 qty_in_ekspedisi_before,
                            0 qty_in_ekspedisi,
                            0 qty_out_qc_reject_before,
                            0 qty_out_qc_reject,
                            0 qty_out_ekspedisi_before,
                            0 qty_out_ekspedisi,
                            0 qty_adjustment_before,
                            0 qty_adjustment,
                            IF(a.tgl_terima >= '".$saldo_awal."' AND a.tgl_terima < '".$tgl_awal."', a.qty, 0) AS qty_terima_qc_reject_before,
                            IF(a.tgl_terima >= '".$tgl_awal."', a.qty, 0) AS qty_terima_qc_reject,
                            0 qty_terima_ekspedisi_before,
                            0 qty_terima_ekspedisi,
                            0 qty_keluar_sewing_before,
                            0 qty_keluar_sewing,
                            0 qty_keluar_qa_before,
                            0 qty_keluar_qa
                        FROM fg_stok_bpb a
                        LEFT JOIN master_sb_ws m ON a.id_so_det = m.id_so_det
                        WHERE a.tgl_terima <= '".$tgl_akhir."'
                        AND a.sumber_pemasukan = 'SEWING'

                        UNION ALL

                        SELECT
                            m.buyer,
                            m.ws,
                            m.color,
                            m.styleno,
                            m.size,
                            0 qty_saldo_awal_adjustment_before,
                            0 qty_in_qc_reject_before,
                            0 qty_in_qc_reject,
                            0 qty_in_ekspedisi_before,
                            0 qty_in_ekspedisi,
                            0 qty_out_qc_reject_before,
                            0 qty_out_qc_reject,
                            0 qty_out_ekspedisi_before,
                            0 qty_out_ekspedisi,
                            0 qty_adjustment_before,
                            0 qty_adjustment,
                            IF(a.tgl_terima >= '".$saldo_awal."' AND a.tgl_terima < '".$tgl_awal."', a.qty, 0) AS qty_terima_qc_reject_before,
                            IF(a.tgl_terima >= '".$tgl_awal."', a.qty, 0) AS qty_terima_qc_reject,
                            0 qty_terima_ekspedisi_before,
                            0 qty_terima_ekspedisi,
                            0 qty_keluar_sewing_before,
                            0 qty_keluar_sewing,
                            0 qty_keluar_qa_before,
                            0 qty_keluar_qa
                        FROM fg_stok_bpb_scan a
                        LEFT JOIN master_sb_ws m ON a.id_so_det = m.id_so_det
                        WHERE a.tgl_terima <= '".$tgl_akhir."'
                        AND a.sumber_pemasukan = 'SEWING'

                        UNION ALL

                        SELECT
                            m.buyer,
                            m.ws,
                            m.color,
                            m.styleno,
                            m.size,
                            0 qty_saldo_awal_adjustment_before,
                            0 qty_in_qc_reject_before,
                            0 qty_in_qc_reject,
                            0 qty_in_ekspedisi_before,
                            0 qty_in_ekspedisi,
                            0 qty_out_qc_reject_before,
                            0 qty_out_qc_reject,
                            0 qty_out_ekspedisi_before,
                            0 qty_out_ekspedisi,
                            0 qty_adjustment_before,
                            0 qty_adjustment,
                            0 qty_terima_qc_reject_before,
                            0 qty_terima_qc_reject,
                            IF(a.tgl_terima >= '".$saldo_awal."' AND a.tgl_terima < '".$tgl_awal."', a.qty, 0) AS qty_terima_ekspedisi_before,
                            IF(a.tgl_terima >= '".$tgl_awal."', a.qty, 0) AS qty_terima_ekspedisi,
                            0 qty_keluar_sewing_before,
                            0 qty_keluar_sewing,
                            0 qty_keluar_qa_before,
                            0 qty_keluar_qa
                        FROM fg_stok_bpb a
                        LEFT JOIN master_sb_ws m ON a.id_so_det = m.id_so_det
                        WHERE a.tgl_terima <= '".$tgl_akhir."'
                        AND a.sumber_pemasukan = 'EKSPEDISI'

                        UNION ALL

                        SELECT
                            m.buyer,
                            m.ws,
                            m.color,
                            m.styleno,
                            m.size,
                            0 qty_saldo_awal_adjustment_before,
                            0 qty_in_qc_reject_before,
                            0 qty_in_qc_reject,
                            0 qty_in_ekspedisi_before,
                            0 qty_in_ekspedisi,
                            0 qty_out_qc_reject_before,
                            0 qty_out_qc_reject,
                            0 qty_out_ekspedisi_before,
                            0 qty_out_ekspedisi,
                            0 qty_adjustment_before,
                            0 qty_adjustment,
                            0 qty_terima_qc_reject_before,
                            0 qty_terima_qc_reject,
                            IF(a.tgl_terima >= '".$saldo_awal."' AND a.tgl_terima < '".$tgl_awal."', a.qty, 0) AS qty_terima_ekspedisi_before,
                            IF(a.tgl_terima >= '".$tgl_awal."', a.qty, 0) AS qty_terima_ekspedisi,
                            0 qty_keluar_sewing_before,
                            0 qty_keluar_sewing,
                            0 qty_keluar_qa_before,
                            0 qty_keluar_qa
                        FROM fg_stok_bpb_scan a
                        LEFT JOIN master_sb_ws m ON a.id_so_det = m.id_so_det
                        WHERE a.tgl_terima <= '".$tgl_akhir."'
                        AND a.sumber_pemasukan = 'EKSPEDISI'

                        UNION ALL

                        SELECT
                            m.buyer,
                            m.ws,
                            m.color,
                            m.styleno,
                            m.size,
                            0 qty_saldo_awal_adjustment_before,
                            0 qty_in_qc_reject_before,
                            0 qty_in_qc_reject,
                            0 qty_in_ekspedisi_before,
                            0 qty_in_ekspedisi,
                            0 qty_out_qc_reject_before,
                            0 qty_out_qc_reject,
                            0 qty_out_ekspedisi_before,
                            0 qty_out_ekspedisi,
                            0 qty_adjustment_before,
                            0 qty_adjustment,
                            0 qty_terima_qc_reject_before,
                            0 qty_terima_qc_reject,
                            0 qty_terima_ekspedisi_before,
                            0 qty_terima_ekspedisi,
                            IF(tgl_pengeluaran >= '".$saldo_awal."' AND tgl_pengeluaran < '".$tgl_awal."', a.qty_out, 0) AS qty_keluar_sewing_before,
                            IF(tgl_pengeluaran >= '".$tgl_awal."', a.qty_out, 0) AS qty_keluar_sewing,
                            0 qty_keluar_qa_before,
                            0 qty_keluar_qa
                        FROM fg_stok_bppb a
                        LEFT JOIN master_sb_ws m on a.id_so_det = m.id_so_det
                        WHERE a.tgl_pengeluaran <= '".$tgl_akhir."'
                        AND a.tujuan = 'PRODUCTION-SEWING'

                        UNION ALL

                        SELECT
                            m.buyer,
                            m.ws,
                            m.color,
                            m.styleno,
                            m.size,
                            0 qty_saldo_awal_adjustment_before,
                            0 qty_in_qc_reject_before,
                            0 qty_in_qc_reject,
                            0 qty_in_ekspedisi_before,
                            0 qty_in_ekspedisi,
                            0 qty_out_qc_reject_before,
                            0 qty_out_qc_reject,
                            0 qty_out_ekspedisi_before,
                            0 qty_out_ekspedisi,
                            0 qty_adjustment_before,
                            0 qty_adjustment,
                            0 qty_terima_qc_reject_before,
                            0 qty_terima_qc_reject,
                            0 qty_terima_ekspedisi_before,
                            0 qty_terima_ekspedisi,
                            0 qty_keluar_sewing_before,
                            0 qty_keluar_sewing,
                            IF(tgl_pengeluaran >= '".$saldo_awal."' AND tgl_pengeluaran < '".$tgl_awal."', a.qty_out, 0) AS qty_keluar_qa_before,
                            IF(tgl_pengeluaran >= '".$tgl_awal."', a.qty_out, 0) AS qty_keluar_qa
                        FROM fg_stok_bppb a
                        LEFT JOIN master_sb_ws m on a.id_so_det = m.id_so_det
                        WHERE a.tgl_pengeluaran <= '".$tgl_akhir."'
                        AND a.tujuan = 'QA'
                    ) x

                    GROUP BY
                        x.buyer,
                        x.ws,
                        x.color,
                        x.styleno,
                        x.size

                )

                SELECT 
                    buyer,
                    ws,
                    styleno,
                    color,
                    size,
                    (
                        COALESCE(qty_in_qc_reject_before,0)
                        + COALESCE(qty_in_ekspedisi_before,0)
                        - COALESCE(qty_out_qc_reject_before,0)
                        - COALESCE(qty_out_ekspedisi_before,0)
                        + COALESCE(qty_adjustment_before,0)
                    ) AS saldo_awal_transit,
                    qty_in_qc_reject,
                    qty_in_ekspedisi,
                    qty_out_qc_reject,
                    qty_out_ekspedisi,
                    qty_adjustment,
                    (
                        COALESCE(qty_in_qc_reject_before,0)
                        + COALESCE(qty_in_ekspedisi_before,0)
                        - COALESCE(qty_out_qc_reject_before,0)
                        - COALESCE(qty_out_ekspedisi_before,0)
                        + COALESCE(qty_adjustment_before,0)

                        + COALESCE(qty_in_qc_reject,0)
                        + COALESCE(qty_in_ekspedisi,0)
                        - COALESCE(qty_out_qc_reject,0)
                        - COALESCE(qty_out_ekspedisi,0)
                        + COALESCE(qty_adjustment,0)
                    ) AS saldo_akhir_transit,
                    (
                        CASE 
                            WHEN '".$tgl_awal."' = '".$saldo_awal."'
                            THEN COALESCE(qty_saldo_awal_adjustment_before,0)
                            ELSE
                                COALESCE(qty_saldo_awal_adjustment_before,0)
                                + COALESCE(qty_terima_qc_reject_before,0)
                                + COALESCE(qty_terima_ekspedisi_before,0)
                                - COALESCE(qty_keluar_sewing_before,0)
                                - COALESCE(qty_keluar_qa_before,0)

                        END
                    ) AS saldo_awal_gudang_stok,
                    qty_terima_qc_reject,
                    qty_terima_ekspedisi,
                    qty_keluar_sewing,
                    qty_keluar_qa,
                    (
                        CASE 
                            WHEN '".$tgl_awal."' = '".$saldo_awal."'
                            THEN COALESCE(qty_saldo_awal_adjustment_before,0)
                            ELSE
                                COALESCE(qty_saldo_awal_adjustment_before,0)
                                + COALESCE(qty_terima_qc_reject_before,0)
                                + COALESCE(qty_terima_ekspedisi_before,0)
                                - COALESCE(qty_keluar_sewing_before,0)
                                - COALESCE(qty_keluar_qa_before,0)
                        END
                        + COALESCE(qty_terima_qc_reject,0)
                        + COALESCE(qty_terima_ekspedisi,0)
                        - COALESCE(qty_keluar_sewing,0)
                        - COALESCE(qty_keluar_qa,0)

                    ) AS saldo_akhir_gudang_stok
                FROM 
                    all_data
            ");

            return DataTables::of($data_input)->toJson();
        }
    }

    public function export_excel_rep_mutasi_global_fg_stock(Request $request)
    {
        $tgl_awal = $request->from;
        $tgl_akhir = $request->to;
        $saldo_awal = '2026-05-01';

        $data = DB::select("WITH 

                saldo_awal AS (
                    SELECT
                        buyer,
                        ws,
                        styleno,
                        color,
                        m.size,
                        SUM(qty_awal) AS qty_awal,
                        SUM(qty_in) AS qty_in,
                        SUM(qty_out) AS qty_out,
                        SUM(qty_awal) + SUM(qty_in) - SUM(qty_out) AS saldo_akhir
                    FROM
                    (
                        SELECT
                            id_so_det,
                            SUM(qty_in) - SUM(qty_out) AS qty_awal,
                            0 AS qty_in,
                            0 AS qty_out,
                            grade,
                            lokasi,
                            no_carton
                        FROM
                        (
                            SELECT
                                id_so_det,
                                SUM(qty) AS qty_in,
                                0 AS qty_out,
                                grade,
                                lokasi,
                                no_carton
                            FROM fg_stok_bpb
                            WHERE tgl_terima < '2026-05-01'
                            GROUP BY id_so_det, grade, lokasi, no_carton

                            UNION ALL

                            SELECT
                                id_so_det,
                                SUM(qty) AS qty_in,
                                0 AS qty_out,
                                grade,
                                lokasi,
                                no_carton
                            FROM fg_stok_bpb_scan
                            WHERE tgl_terima < '2026-05-01'
                            GROUP BY id_so_det, grade, lokasi, no_carton

                            UNION ALL

                            SELECT
                                id_so_det,
                                0 AS qty_in,
                                SUM(qty_out) AS qty_out,
                                grade,
                                lokasi,
                                no_carton
                            FROM fg_stok_bppb
                            WHERE tgl_pengeluaran < '2026-05-01'
                            GROUP BY id_so_det, grade, lokasi, no_carton

                        ) sa
                        GROUP BY id_so_det, grade, lokasi, no_carton
                    ) mt
                    LEFT JOIN master_sb_ws m ON mt.id_so_det = m.id_so_det
                    LEFT JOIN master_size_new ms ON m.size = ms.size
                    GROUP BY mt.id_so_det, grade, lokasi, no_carton
                ),
            
                all_data AS (
                    SELECT
                        x.buyer,
                        x.ws,
                        x.color,
                        x.styleno,
                        x.size,
                        SUM(x.qty_saldo_awal_adjustment_before) AS qty_saldo_awal_adjustment_before,
                        SUM(x.qty_in_qc_reject_before) AS qty_in_qc_reject_before,
                        SUM(x.qty_in_qc_reject) AS qty_in_qc_reject,
                        SUM(x.qty_in_ekspedisi_before) AS qty_in_ekspedisi_before,
                        SUM(x.qty_in_ekspedisi) AS qty_in_ekspedisi,
                        SUM(x.qty_out_qc_reject_before) AS qty_out_qc_reject_before,
                        SUM(x.qty_out_qc_reject) AS qty_out_qc_reject,
                        SUM(x.qty_out_ekspedisi_before) AS qty_out_ekspedisi_before,
                        SUM(x.qty_out_ekspedisi) AS qty_out_ekspedisi,
                        SUM(x.qty_adjustment_before) AS qty_adjustment_before,
                        SUM(x.qty_adjustment) AS qty_adjustment,
                        SUM(x.qty_terima_qc_reject_before) AS qty_terima_qc_reject_before,
                        SUM(x.qty_terima_qc_reject) AS qty_terima_qc_reject,
                        SUM(x.qty_terima_ekspedisi_before) AS qty_terima_ekspedisi_before,
                        SUM(x.qty_terima_ekspedisi) AS qty_terima_ekspedisi,
                        SUM(x.qty_keluar_sewing_before) AS qty_keluar_sewing_before,
                        SUM(x.qty_keluar_sewing) AS qty_keluar_sewing,
                        SUM(x.qty_keluar_qa_before) AS qty_keluar_qa_before,
                        SUM(x.qty_keluar_qa) AS qty_keluar_qa
                    FROM (

                        SELECT
                            buyer,
                            ws,
                            color,
                            styleno,
                            size,
                            saldo_awal.qty_awal AS qty_saldo_awal_adjustment_before,
                            0 qty_in_qc_reject_before,
                            0 qty_in_qc_reject,
                            0 qty_in_ekspedisi_before,
                            0 qty_in_ekspedisi,
                            0 qty_out_qc_reject_before,
                            0 qty_out_qc_reject,
                            0 qty_out_ekspedisi_before,
                            0 qty_out_ekspedisi,
                            0 qty_adjustment_before,
                            0 qty_adjustment,
                            0 qty_terima_qc_reject_before,
                            0 qty_terima_qc_reject,
                            0 qty_terima_ekspedisi_before,
                            0 qty_terima_ekspedisi,
                            0 qty_keluar_sewing_before,
                            0 qty_keluar_sewing,
                            0 qty_keluar_qa_before,
                            0 qty_keluar_qa
                        FROM saldo_awal 

                        UNION ALL

                        SELECT
                            mb.buyer,
                            mb.ws,
                            mb.color,
                            mb.styleno,
                            mb.size,
                            0 qty_saldo_awal_adjustment_before,
                            COUNT(CASE WHEN b.status = 'rejected' AND DATE(a.created_at) >= '".$saldo_awal."' AND DATE(a.created_at) < '".$tgl_awal."' THEN 1 END) AS qty_in_qc_reject_before,
                            COUNT(CASE WHEN b.status = 'rejected' AND date(a.created_at) >= '".$tgl_awal."' THEN 1 END) AS qty_in_qc_reject,
                            0 qty_in_ekspedisi_before,
                            0 qty_in_ekspedisi,
                            0 qty_out_qc_reject_before,
                            0 qty_out_qc_reject,
                            0 qty_out_ekspedisi_before,
                            0 qty_out_ekspedisi,
                            0 qty_adjustment_before,
                            0 qty_adjustment,
                            0 qty_terima_qc_reject_before,
                            0 qty_terima_qc_reject,
                            0 qty_terima_ekspedisi_before,
                            0 qty_terima_ekspedisi,
                            0 qty_keluar_sewing_before,
                            0 qty_keluar_sewing,
                            0 qty_keluar_qa_before,
                            0 qty_keluar_qa
                        FROM signalbit_erp.output_reject_out_detail a
                        INNER JOIN signalbit_erp.output_reject_in b on a.reject_in_id = b.id
                        INNER JOIN signalbit_erp.master_plan mp on b.master_plan_id = mp.id
                        LEFT JOIN (
                            SELECT
                                sd.id as id_so_det,
                                ac.kpno as ws,
                                supplier as buyer,
                                styleno,
                                color,
                                size,
                                dest
                            FROM signalbit_erp.so_det sd
                            INNER JOIN signalbit_erp.so ON sd.id_so = so.id
                            INNER JOIN signalbit_erp.jo_det jd ON so.id = jd.id_so
                            INNER JOIN signalbit_erp.act_costing ac ON so.id_cost = ac.id
                            INNER JOIN signalbit_erp.mastersupplier ms ON ac.id_buyer = ms.id_supplier
                            WHERE jd.cancel = 'N'
                        ) mb on b.so_det_id = mb.id_so_det
                        WHERE DATE(a.created_at) <= '".$tgl_akhir."'
                        AND mp.cancel = 'N'
                        GROUP BY
                        mb.buyer,
                        mb.ws,
                        mb.color,
                        mb.styleno,
                        mb.size

                        UNION ALL

                        SELECT
                            buyer.supplier as buyer,
                            act_costing.kpno ws,
                            masterstyle.color,
                            act_costing.styleno,
                            masterstyle.size,
                            0 qty_saldo_awal_adjustment_before,
                            0 qty_in_qc_reject_before,
                            0 qty_in_qc_reject,
                            IF(bppbdate >= '".$saldo_awal."' AND bppbdate < '".$tgl_awal."', bppb.qty, 0) qty_in_ekspedisi_before,
                            IF(bppbdate >= '".$tgl_awal."', bppb.qty, 0) qty_in_ekspedisi,
                            0 qty_out_qc_reject_before,
                            0 qty_out_qc_reject,
                            0 qty_out_ekspedisi_before,
                            0 qty_out_ekspedisi,
                            0 qty_adjustment_before,
                            0 qty_adjustment,
                            0 qty_terima_qc_reject_before,
                            0 qty_terima_qc_reject,
                            0 qty_terima_ekspedisi_before,
                            0 qty_terima_ekspedisi,
                            0 qty_keluar_sewing_before,
                            0 qty_keluar_sewing,
                            0 qty_keluar_qa_before,
                            0 qty_keluar_qa
                        FROM
                            signalbit_erp.bppb
                        INNER JOIN signalbit_erp.masterstyle ON masterstyle.id_item = bppb.id_item
                        INNER JOIN signalbit_erp.mastersupplier ON mastersupplier.Id_Supplier = bppb.id_supplier
                        LEFT JOIN (select sod.id_so,sod.id id_so_det from signalbit_erp.so_det sod  group by sod.id) tmpjod on tmpjod.id_so_det=bppb.id_so_det
                        LEFT JOIN signalbit_erp.so ON so.id = tmpjod.id_so
                        LEFT JOIN signalbit_erp.act_costing ON act_costing.id = so.id_cost
                        LEFT JOIN signalbit_erp.mastersupplier buyer ON buyer.Id_Supplier = act_costing.id_buyer
                        WHERE mid(bppbno,4,2) in ('FG') AND bppbdate <= '".$tgl_akhir."' AND mastersupplier.supplier = 'BARANG JADI STOCK'

                        UNION ALL

                        SELECT
                            m.buyer,
                            m.ws,
                            m.color,
                            m.styleno,
                            m.size,
                            0 qty_saldo_awal_adjustment_before,
                            0 qty_in_qc_reject_before,
                            0 qty_in_qc_reject,
                            0 qty_in_ekspedisi_before,
                            0 qty_in_ekspedisi,
                            IF(a.tgl_terima >= '".$saldo_awal."' AND a.tgl_terima < '".$tgl_awal."', a.qty, 0) AS qty_out_qc_reject_before,
                            IF(a.tgl_terima >= '".$tgl_awal."', a.qty, 0) AS qty_out_qc_reject,
                            0 qty_out_ekspedisi_before,
                            0 qty_out_ekspedisi,
                            0 qty_adjustment_before,
                            0 qty_adjustment,
                            0 qty_terima_qc_reject_before,
                            0 qty_terima_qc_reject,
                            0 qty_terima_ekspedisi_before,
                            0 qty_terima_ekspedisi,
                            0 qty_keluar_sewing_before,
                            0 qty_keluar_sewing,
                            0 qty_keluar_qa_before,
                            0 qty_keluar_qa
                        FROM fg_stok_bpb a
                        LEFT JOIN master_sb_ws m ON a.id_so_det = m.id_so_det
                        WHERE a.tgl_terima <= '".$tgl_akhir."'
                        AND a.sumber_pemasukan = 'SEWING'

                        UNION ALL

                        SELECT
                            m.buyer,
                            m.ws,
                            m.color,
                            m.styleno,
                            m.size,
                            0 qty_saldo_awal_adjustment_before,
                            0 qty_in_qc_reject_before,
                            0 qty_in_qc_reject,
                            0 qty_in_ekspedisi_before,
                            0 qty_in_ekspedisi,
                            IF(a.tgl_terima >= '".$saldo_awal."' AND a.tgl_terima < '".$tgl_awal."', a.qty, 0) AS qty_out_qc_reject_before,
                            IF(a.tgl_terima >= '".$tgl_awal."', a.qty, 0) AS qty_out_qc_reject,
                            0 qty_out_ekspedisi_before,
                            0 qty_out_ekspedisi,
                            0 qty_adjustment_before,
                            0 qty_adjustment,
                            0 qty_terima_qc_reject_before,
                            0 qty_terima_qc_reject,
                            0 qty_terima_ekspedisi_before,
                            0 qty_terima_ekspedisi,
                            0 qty_keluar_sewing_before,
                            0 qty_keluar_sewing,
                            0 qty_keluar_qa_before,
                            0 qty_keluar_qa
                        FROM fg_stok_bpb_scan a
                        LEFT JOIN master_sb_ws m ON a.id_so_det = m.id_so_det
                        WHERE a.tgl_terima <= '".$tgl_akhir."'
                        AND a.sumber_pemasukan = 'SEWING'

                        UNION ALL

                        SELECT
                            m.buyer,
                            m.ws,
                            m.color,
                            m.styleno,
                            m.size,
                            0 qty_saldo_awal_adjustment_before,
                            0 qty_in_qc_reject_before,
                            0 qty_in_qc_reject,
                            0 qty_in_ekspedisi_before,
                            0 qty_in_ekspedisi,
                            0 qty_out_qc_reject_before,
                            0 qty_out_qc_reject,
                            IF(a.tgl_terima >= '".$saldo_awal."' AND a.tgl_terima < '".$tgl_awal."', a.qty, 0) AS qty_out_ekspedisi_before,
                            IF(a.tgl_terima >= '".$tgl_awal."', a.qty, 0) AS qty_out_ekspedisi,
                            0 qty_adjustment_before,
                            0 qty_adjustment,
                            0 qty_terima_qc_reject_before,
                            0 qty_terima_qc_reject,
                            0 qty_terima_ekspedisi_before,
                            0 qty_terima_ekspedisi,
                            0 qty_keluar_sewing_before,
                            0 qty_keluar_sewing,
                            0 qty_keluar_qa_before,
                            0 qty_keluar_qa
                        FROM fg_stok_bpb a
                        LEFT JOIN master_sb_ws m ON a.id_so_det = m.id_so_det
                        WHERE a.tgl_terima <= '".$tgl_akhir."'
                        AND a.sumber_pemasukan = 'EKSPEDISI'

                        UNION ALL

                        SELECT
                            m.buyer,
                            m.ws,
                            m.color,
                            m.styleno,
                            m.size,
                            0 qty_saldo_awal_adjustment_before,
                            0 qty_in_qc_reject_before,
                            0 qty_in_qc_reject,
                            0 qty_in_ekspedisi_before,
                            0 qty_in_ekspedisi,
                            0 qty_out_qc_reject_before,
                            0 qty_out_qc_reject,
                            IF(a.tgl_terima >= '".$saldo_awal."' AND a.tgl_terima < '".$tgl_awal."', a.qty, 0) AS qty_out_ekspedisi_before,
                            IF(a.tgl_terima >= '".$tgl_awal."', a.qty, 0) AS qty_out_ekspedisi,
                            0 qty_adjustment_before,
                            0 qty_adjustment,
                            0 qty_terima_qc_reject_before,
                            0 qty_terima_qc_reject,
                            0 qty_terima_ekspedisi_before,
                            0 qty_terima_ekspedisi,
                            0 qty_keluar_sewing_before,
                            0 qty_keluar_sewing,
                            0 qty_keluar_qa_before,
                            0 qty_keluar_qa
                        FROM fg_stok_bpb_scan a
                        LEFT JOIN master_sb_ws m ON a.id_so_det = m.id_so_det
                        WHERE a.tgl_terima <= '".$tgl_akhir."'
                        AND a.sumber_pemasukan = 'EKSPEDISI'
                        
                        UNION ALL

                        SELECT
                            buyer,
                            no_ws ws,
                            style styleno,
                            color,
                            size,
                            0 qty_saldo_awal_adjustment_before,
                            0 qty_in_qc_reject_before,
                            0 qty_in_qc_reject,
                            0 qty_in_ekspedisi_before,
                            0 qty_in_ekspedisi,
                            0 qty_out_qc_reject_before,
                            0 qty_out_qc_reject,
                            0 qty_out_ekspedisi_before,
                            0 qty_out_ekspedisi,
                            SUM(IF(tgl_saldo >= '{$saldo_awal}' AND tgl_saldo < '{$tgl_awal}',qty,0)) qty_adjustment_before,
                            SUM(IF(tgl_saldo >= '{$tgl_awal}',qty,0)) qty_adjustment,
                            0 qty_terima_qc_reject_before,
                            0 qty_terima_qc_reject,
                            0 qty_terima_ekspedisi_before,
                            0 qty_terima_ekspedisi,
                            0 qty_keluar_sewing_before,
                            0 qty_keluar_sewing,
                            0 qty_keluar_qa_before,
                            0 qty_keluar_qa
                        FROM
                            wip_adjustment
                        WHERE
                            tgl_saldo <= '{$tgl_akhir}' and
                            type_report = 'TRANSIT_GUDANG_STOK'
                        GROUP BY
                            ws, color, size, panel, part

                        UNION ALL

                        SELECT
                            m.buyer,
                            m.ws,
                            m.color,
                            m.styleno,
                            m.size,
                            0 qty_saldo_awal_adjustment_before,
                            0 qty_in_qc_reject_before,
                            0 qty_in_qc_reject,
                            0 qty_in_ekspedisi_before,
                            0 qty_in_ekspedisi,
                            0 qty_out_qc_reject_before,
                            0 qty_out_qc_reject,
                            0 qty_out_ekspedisi_before,
                            0 qty_out_ekspedisi,
                            0 qty_adjustment_before,
                            0 qty_adjustment,
                            IF(a.tgl_terima >= '".$saldo_awal."' AND a.tgl_terima < '".$tgl_awal."', a.qty, 0) AS qty_terima_qc_reject_before,
                            IF(a.tgl_terima >= '".$tgl_awal."', a.qty, 0) AS qty_terima_qc_reject,
                            0 qty_terima_ekspedisi_before,
                            0 qty_terima_ekspedisi,
                            0 qty_keluar_sewing_before,
                            0 qty_keluar_sewing,
                            0 qty_keluar_qa_before,
                            0 qty_keluar_qa
                        FROM fg_stok_bpb a
                        LEFT JOIN master_sb_ws m ON a.id_so_det = m.id_so_det
                        WHERE a.tgl_terima <= '".$tgl_akhir."'
                        AND a.sumber_pemasukan = 'SEWING'

                        UNION ALL

                        SELECT
                            m.buyer,
                            m.ws,
                            m.color,
                            m.styleno,
                            m.size,
                            0 qty_saldo_awal_adjustment_before,
                            0 qty_in_qc_reject_before,
                            0 qty_in_qc_reject,
                            0 qty_in_ekspedisi_before,
                            0 qty_in_ekspedisi,
                            0 qty_out_qc_reject_before,
                            0 qty_out_qc_reject,
                            0 qty_out_ekspedisi_before,
                            0 qty_out_ekspedisi,
                            0 qty_adjustment_before,
                            0 qty_adjustment,
                            IF(a.tgl_terima >= '".$saldo_awal."' AND a.tgl_terima < '".$tgl_awal."', a.qty, 0) AS qty_terima_qc_reject_before,
                            IF(a.tgl_terima >= '".$tgl_awal."', a.qty, 0) AS qty_terima_qc_reject,
                            0 qty_terima_ekspedisi_before,
                            0 qty_terima_ekspedisi,
                            0 qty_keluar_sewing_before,
                            0 qty_keluar_sewing,
                            0 qty_keluar_qa_before,
                            0 qty_keluar_qa
                        FROM fg_stok_bpb_scan a
                        LEFT JOIN master_sb_ws m ON a.id_so_det = m.id_so_det
                        WHERE a.tgl_terima <= '".$tgl_akhir."'
                        AND a.sumber_pemasukan = 'SEWING'

                        UNION ALL

                        SELECT
                            m.buyer,
                            m.ws,
                            m.color,
                            m.styleno,
                            m.size,
                            0 qty_saldo_awal_adjustment_before,
                            0 qty_in_qc_reject_before,
                            0 qty_in_qc_reject,
                            0 qty_in_ekspedisi_before,
                            0 qty_in_ekspedisi,
                            0 qty_out_qc_reject_before,
                            0 qty_out_qc_reject,
                            0 qty_out_ekspedisi_before,
                            0 qty_out_ekspedisi,
                            0 qty_adjustment_before,
                            0 qty_adjustment,
                            0 qty_terima_qc_reject_before,
                            0 qty_terima_qc_reject,
                            IF(a.tgl_terima >= '".$saldo_awal."' AND a.tgl_terima < '".$tgl_awal."', a.qty, 0) AS qty_terima_ekspedisi_before,
                            IF(a.tgl_terima >= '".$tgl_awal."', a.qty, 0) AS qty_terima_ekspedisi,
                            0 qty_keluar_sewing_before,
                            0 qty_keluar_sewing,
                            0 qty_keluar_qa_before,
                            0 qty_keluar_qa
                        FROM fg_stok_bpb a
                        LEFT JOIN master_sb_ws m ON a.id_so_det = m.id_so_det
                        WHERE a.tgl_terima <= '".$tgl_akhir."'
                        AND a.sumber_pemasukan = 'EKSPEDISI'

                        UNION ALL

                        SELECT
                            m.buyer,
                            m.ws,
                            m.color,
                            m.styleno,
                            m.size,
                            0 qty_saldo_awal_adjustment_before,
                            0 qty_in_qc_reject_before,
                            0 qty_in_qc_reject,
                            0 qty_in_ekspedisi_before,
                            0 qty_in_ekspedisi,
                            0 qty_out_qc_reject_before,
                            0 qty_out_qc_reject,
                            0 qty_out_ekspedisi_before,
                            0 qty_out_ekspedisi,
                            0 qty_adjustment_before,
                            0 qty_adjustment,
                            0 qty_terima_qc_reject_before,
                            0 qty_terima_qc_reject,
                            IF(a.tgl_terima >= '".$saldo_awal."' AND a.tgl_terima < '".$tgl_awal."', a.qty, 0) AS qty_terima_ekspedisi_before,
                            IF(a.tgl_terima >= '".$tgl_awal."', a.qty, 0) AS qty_terima_ekspedisi,
                            0 qty_keluar_sewing_before,
                            0 qty_keluar_sewing,
                            0 qty_keluar_qa_before,
                            0 qty_keluar_qa
                        FROM fg_stok_bpb_scan a
                        LEFT JOIN master_sb_ws m ON a.id_so_det = m.id_so_det
                        WHERE a.tgl_terima <= '".$tgl_akhir."'
                        AND a.sumber_pemasukan = 'EKSPEDISI'

                        UNION ALL

                        SELECT
                            m.buyer,
                            m.ws,
                            m.color,
                            m.styleno,
                            m.size,
                            0 qty_saldo_awal_adjustment_before,
                            0 qty_in_qc_reject_before,
                            0 qty_in_qc_reject,
                            0 qty_in_ekspedisi_before,
                            0 qty_in_ekspedisi,
                            0 qty_out_qc_reject_before,
                            0 qty_out_qc_reject,
                            0 qty_out_ekspedisi_before,
                            0 qty_out_ekspedisi,
                            0 qty_adjustment_before,
                            0 qty_adjustment,
                            0 qty_terima_qc_reject_before,
                            0 qty_terima_qc_reject,
                            0 qty_terima_ekspedisi_before,
                            0 qty_terima_ekspedisi,
                            IF(tgl_pengeluaran >= '".$saldo_awal."' AND tgl_pengeluaran < '".$tgl_awal."', a.qty_out, 0) AS qty_keluar_sewing_before,
                            IF(tgl_pengeluaran >= '".$tgl_awal."', a.qty_out, 0) AS qty_keluar_sewing,
                            0 qty_keluar_qa_before,
                            0 qty_keluar_qa
                        FROM fg_stok_bppb a
                        LEFT JOIN master_sb_ws m on a.id_so_det = m.id_so_det
                        WHERE a.tgl_pengeluaran <= '".$tgl_akhir."'
                        AND a.tujuan = 'PRODUCTION-SEWING'

                        UNION ALL

                        SELECT
                            m.buyer,
                            m.ws,
                            m.color,
                            m.styleno,
                            m.size,
                            0 qty_saldo_awal_adjustment_before,
                            0 qty_in_qc_reject_before,
                            0 qty_in_qc_reject,
                            0 qty_in_ekspedisi_before,
                            0 qty_in_ekspedisi,
                            0 qty_out_qc_reject_before,
                            0 qty_out_qc_reject,
                            0 qty_out_ekspedisi_before,
                            0 qty_out_ekspedisi,
                            0 qty_adjustment_before,
                            0 qty_adjustment,
                            0 qty_terima_qc_reject_before,
                            0 qty_terima_qc_reject,
                            0 qty_terima_ekspedisi_before,
                            0 qty_terima_ekspedisi,
                            0 qty_keluar_sewing_before,
                            0 qty_keluar_sewing,
                            IF(tgl_pengeluaran >= '".$saldo_awal."' AND tgl_pengeluaran < '".$tgl_awal."', a.qty_out, 0) AS qty_keluar_qa_before,
                            IF(tgl_pengeluaran >= '".$tgl_awal."', a.qty_out, 0) AS qty_keluar_qa
                        FROM fg_stok_bppb a
                        LEFT JOIN master_sb_ws m on a.id_so_det = m.id_so_det
                        WHERE a.tgl_pengeluaran <= '".$tgl_akhir."'
                        AND a.tujuan = 'QA'
                    ) x

                    GROUP BY
                        x.buyer,
                        x.ws,
                        x.color,
                        x.styleno,
                        x.size

                )

                SELECT 
                    buyer,
                    ws,
                    styleno,
                    color,
                    size,
                    (
                        COALESCE(qty_in_qc_reject_before,0)
                        + COALESCE(qty_in_ekspedisi_before,0)
                        - COALESCE(qty_out_qc_reject_before,0)
                        - COALESCE(qty_out_ekspedisi_before,0)
                        + COALESCE(qty_adjustment_before,0)
                    ) AS saldo_awal_transit,
                    qty_in_qc_reject,
                    qty_in_ekspedisi,
                    qty_out_qc_reject,
                    qty_out_ekspedisi,
                    qty_adjustment,
                    (
                        COALESCE(qty_in_qc_reject_before,0)
                        + COALESCE(qty_in_ekspedisi_before,0)
                        - COALESCE(qty_out_qc_reject_before,0)
                        - COALESCE(qty_out_ekspedisi_before,0)
                        + COALESCE(qty_adjustment_before,0)

                        + COALESCE(qty_in_qc_reject,0)
                        + COALESCE(qty_in_ekspedisi,0)
                        - COALESCE(qty_out_qc_reject,0)
                        - COALESCE(qty_out_ekspedisi,0)
                        + COALESCE(qty_adjustment,0)
                    ) AS saldo_akhir_transit,
                    (
                        CASE 
                            WHEN '".$tgl_awal."' = '".$saldo_awal."'
                            THEN COALESCE(qty_saldo_awal_adjustment_before,0)
                            ELSE
                                COALESCE(qty_saldo_awal_adjustment_before,0)
                                + COALESCE(qty_terima_qc_reject_before,0)
                                + COALESCE(qty_terima_ekspedisi_before,0)
                                - COALESCE(qty_keluar_sewing_before,0)
                                - COALESCE(qty_keluar_qa_before,0)

                        END
                    ) AS saldo_awal_gudang_stok,
                    qty_terima_qc_reject,
                    qty_terima_ekspedisi,
                    qty_keluar_sewing,
                    qty_keluar_qa,
                    (
                        CASE 
                            WHEN '".$tgl_awal."' = '".$saldo_awal."'
                            THEN COALESCE(qty_saldo_awal_adjustment_before,0)
                            ELSE
                                COALESCE(qty_saldo_awal_adjustment_before,0)
                                + COALESCE(qty_terima_qc_reject_before,0)
                                + COALESCE(qty_terima_ekspedisi_before,0)
                                - COALESCE(qty_keluar_sewing_before,0)
                                - COALESCE(qty_keluar_qa_before,0)
                        END
                        + COALESCE(qty_terima_qc_reject,0)
                        + COALESCE(qty_terima_ekspedisi,0)
                        - COALESCE(qty_keluar_sewing,0)
                        - COALESCE(qty_keluar_qa,0)

                    ) AS saldo_akhir_gudang_stok
                FROM 
                    all_data
        ");

        $fileName = 'laporan-mutasi-fg-stock-global';

        $excel = FastExcel::create($fileName);
        
        $sheet = $excel->sheet();

        $sheet->writeRow(
            ['Laporan Mutasi FG Stock Global'],
            [
                'font-style' => 'bold',
                'font-size'  => 14,
                'halign'     => 'center',
                'valign'     => 'center',
            ]
        );

        $sheet->writeRow(
            ['Periode ' . $tgl_awal . ' s/d ' . $tgl_akhir],
            [
                'halign' => 'center',
            ]
        );

        $sheet->writeRow(['']);

        $sheet->writeRow([
            'Buyer','WS','Style','Color','Size',
            'Transit Terima Gudang Stok','','','','','','',
            'Gudang Stok','','','','',''
        ], [
            'font-style' => 'bold',
            'border'     => 'thin',
            'halign'     => 'center',
            'valign'     => 'center',
        ]);

        $sheet->mergeCells('A4:A5');
        $sheet->mergeCells('B4:B5');
        $sheet->mergeCells('C4:C5');
        $sheet->mergeCells('D4:D5');
        $sheet->mergeCells('E4:E5');
        $sheet->mergeCells('F4:L4');
        $sheet->mergeCells('M4:R4');

        $sheet->setCellStyle('A4:E4', [
            'fill'   => '#ADD8E6',
            'text-align' => 'center',
        ]);

        $sheet->setCellStyle('F4:L4', [
            'fill'   => '#90EE90',
            'text-align' => 'center',
        ]);

        $sheet->setCellStyle('M4:R4', [
            'fill'   => '#FFFFE0',
            'text-align' => 'center',
        ]);

        $sheet->writeRow([
            '','','','','',
            'Saldo Awal',
            'In QC Reject',
            'In Ekspedisi',
            'Out QC Reject',
            'Out Ekspedisi',
            'Adjustment',
            'Saldo Akhir',

            'Saldo Awal',
            'Terima QC Reject',
            'Terima Ekspedisi',
            'Keluar Sewing',
            'Keluar QA',
            'Saldo Akhir',
        ], [
            'font-style' => 'bold',
            'border'     => 'thin',
            'halign'     => 'center',
            'valign'     => 'center',
        ]);

        $sheet->setCellStyle('A5:E5', [
            'fill'   => '#ADD8E6',
            'text-align' => 'center',
        ]);

        $sheet->setCellStyle('F5:L5', [
            'fill'   => '#90EE90',
            'text-align' => 'center',
        ]);

        $sheet->setCellStyle('M5:R5', [
            'fill'   => '#FFFFE0',
            'text-align' => 'center',
        ]);

        foreach ($data as $row) {

            $rows = [
                $row->buyer ?: '',
                $row->ws ?: '',
                $row->styleno ?: '',
                $row->color ?: '',
                $row->size ?: '',

                (float) ($row->saldo_awal_transit ?? 0),
                (float) ($row->qty_in_qc_reject ?? 0),
                (float) ($row->qty_in_ekspedisi ?? 0),
                (float) ($row->qty_out_qc_reject ?? 0),
                (float) ($row->qty_out_ekspedisi ?? 0),
                (float) ($row->qty_adjustment ?? 0),
                (float) ($row->saldo_akhir_transit ?? 0),

                (float) ($row->saldo_awal_gudang_stok ?? 0),
                (float) ($row->qty_terima_qc_reject ?? 0),
                (float) ($row->qty_terima_ekspedisi ?? 0),
                (float) ($row->qty_keluar_sewing ?? 0),
                (float) ($row->qty_keluar_qa ?? 0),
                (float) ($row->saldo_akhir_gudang_stok ?? 0),
            ];

            $sheet->writeRow($rows, [ 'border' => 'thin', ] );
        }

        foreach (range('A', 'R') as $col) {
            $sheet->setColWidth($col, 20);
        }

        return $excel->download();
    }
}
