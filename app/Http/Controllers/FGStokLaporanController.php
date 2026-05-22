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
        select 'Mutasi' isi, 'MUTASI' tampil");

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
}
