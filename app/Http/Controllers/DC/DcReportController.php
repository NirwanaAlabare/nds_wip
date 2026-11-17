<?php

namespace App\Http\Controllers\DC;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Exports\DC\ExportReportDc;
use \avadim\FastExcelLaravel\Excel as FastExcel;
use App\Services\DcService;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use DB;

class DcReportController extends Controller
{
    public function buildQuery($from, $to) {
        $dateFrom = $from ? $from : date("Y-m-d");
        $dateTo = $to ? $to : date("Y-m-d");

        $dcService = new DcService();

        return $dcService->buildQuery($dateFrom, $dateTo);
    }

    public function index(Request $request){
        ini_set("max_execution_time", 36000);
        ini_set('memory_limit', '1024M');

        if ($request->ajax()) {

            if (!$request->dateFrom || !$request->dateTo) {
                return DataTables::of([])->toJson();
            }

            $dateFrom = $request->dateFrom ? $request->dateFrom : date("Y-m-d");
            $dateTo = $request->dateTo ? $request->dateTo : date("Y-m-d");

            $dcQuery = $this->buildQuery($dateFrom, $dateTo);

            $dataReport = DB::select($dcQuery);

            return DataTables::of($dataReport)->toJson();
        }

        return view('dc.report.report', [
            "page" => "dashboard-dc",
            "subPageGroup" => "report",
            "subPage" => "dc-report",
        ]);
    }

    public function exportReportDc(Request $request) {
        ini_set("max_execution_time", 36000);
        ini_set('memory_limit', '1024M');

        $from = $request->from;
        $to   = $request->to;

        $dateFrom = $from;
        $dateTo   = $to;

        $dataReport = collect();

        if (!empty($dateFrom) && !empty($dateTo)) {

                $dcQuery = $this->buildQuery($dateFrom, $dateTo);

                $dataReport = collect(DB::select($dcQuery));
        }

        $fileName = 'laporan-dc';

        $excel = FastExcel::create($fileName);

        $sheet = $excel->sheet();

        $sheet->writeRow(
            ['Laporan DC'],
            [
                'font-style' => 'bold',
                'font-size'  => 14,
                'halign'     => 'center',
                'valign'     => 'center',
            ]
        );

        $sheet->writeRow(
            ['Periode ' . $dateFrom . ' s/d ' . $dateTo],
            [
                'halign' => 'center',
            ]
        );

        $sheet->writeRow(['']);

        $batasTanggal = '2026-07-01';

        $isOld = $to < $batasTanggal;

        $header = [
                'No. WS',
                'Buyer',
                'Style',
                'Color',
                'Size',
                'Panel',
                'Part',

                'Mutasi DC',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',

                'Mutasi Secondary Dalam',
                '',
                '',
                '',
                '',
                '',
        ];


        if ($isOld) {

                $header = array_merge($header, [
                        'Mutasi Secondary Luar',
                        '',
                        '',
                        '',
                        '',
                        '',
                ]);

        } else {

                $header = array_merge($header, [
                        'Mutasi Secondary Luar',
                        '',
                        '',
                        '',
                        '',

                        'Terima Transit Secondary Luar',
                        '',
                        '',
                        '',
                        '',
                        '',
                ]);
        }

        $sheet->writeRow($header, [
                'font-style' => 'bold',
                'border'     => 'thin',
                'halign'     => 'center',
                'valign'     => 'center',
        ]);

        foreach (range('A', 'G') as $col) {
                $sheet->mergeCells("{$col}4:{$col}5");
        }

        $sheet->mergeCells('H4:R4');
        $sheet->mergeCells('S4:X4');

        if ($isOld) {
                $sheet->mergeCells('Y4:AD4');
        } else {
                $sheet->mergeCells('Y4:AC4');
                $sheet->mergeCells('AD4:AI4');
        }

        // Identitas
        $sheet->setCellStyle('A4:G4', [
                'fill'       => '#ADD8E6',
                'text-align' => 'center',
        ]);

        // Mutasi DC
        $sheet->setCellStyle('H4:R4', [
                'fill'       => '#FFFFE0',
                'text-align' => 'center',
        ]);

        // Mutasi Secondary Dalam
        $sheet->setCellStyle('S4:X4', [
                'fill'       => '#FFD966',
                'text-align' => 'center',
        ]);

        if ($isOld) {

                $sheet->setCellStyle('Y4:AD4', [
                        'fill'       => '#90EE90',
                        'text-align' => 'center',
                ]);

        } else {

                $sheet->setCellStyle('Y4:AC4', [
                        'fill'       => '#90EE90',
                        'text-align' => 'center',
                ]);

                $sheet->setCellStyle('AD4:AI4', [
                        'fill'       => '#FFA94D',
                        'text-align' => 'center',
                ]);
        }


        $subHeader = [
                '',
                '',
                '',
                '',
                '',
                '',
                '',

                'Saldo Awal',
                'Masuk',
                'Kirim Sec Dalam',
                'Terima Rep Sec Dalam',
                'Terima Good Sec Dalam',
                'Kirim Sec Luar',
                'Terima Rep Sec Luar',
                'Terima Good Sec Luar',
                'Loading',
                'Adjustment',
                'Saldo Akhir',

                'Saldo Awal Secondary',
                'Terima DC',
                'Kirim Rep ke DC',
                'Kirim Good ke DC',
                'Adjustment',
                'Saldo Akhir Secondary',
        ];

        if ($isOld) {

                $subHeader = array_merge($subHeader, [
                        'Saldo Awal Secondary',
                        'Terima DC',
                        'Kirim Rep ke DC',
                        'Kirim Good ke DC',
                        'Adjustment',
                        'Saldo Akhir Secondary',
                ]);

        } else {

                $subHeader = array_merge($subHeader, [
                        'Saldo Awal Secondary',
                        'Terima DC',
                        'Kirim Ke DC',
                        'Adjustment',
                        'Saldo Akhir Secondary',
                ]);

                $subHeader = array_merge($subHeader, [
                        'Saldo Awal',
                        'Terima Secondary Luar',
                        'Kirim Rep Secondary Luar',
                        'Kirim Good Secondary Luar',
                        'Adjustment',
                        'Saldo Akhir',
                ]);
        }

        $sheet->writeRow($subHeader, [
                'font-style' => 'bold',
                'border'     => 'thin',
                'halign'     => 'center',
                'valign'     => 'center',
        ]);

        // Identitas
        $sheet->setCellStyle('A5:G5', [
                'fill'       => '#ADD8E6',
                'text-align' => 'center',
        ]);

        // Mutasi DC
        $sheet->setCellStyle('H5:R5', [
                'fill'       => '#FFFFE0',
                'text-align' => 'center',
        ]);

        // Mutasi Secondary Dalam
        $sheet->setCellStyle('S5:X5', [
                'fill'       => '#FFD966',
                'text-align' => 'center',
        ]);

        if ($isOld) {

                $sheet->setCellStyle('Y5:AD5', [
                        'fill'       => '#90EE90',
                        'text-align' => 'center',
                ]);

        } else {

                $sheet->setCellStyle('Y5:AC5', [
                        'fill'       => '#90EE90',
                        'text-align' => 'center',
                ]);

                $sheet->setCellStyle('AD5:AI5', [
                        'fill'       => '#FFA94D',
                        'text-align' => 'center',
                ]);
        }


        foreach ($dataReport as $row) {

                $saldoAwal = $row->current_saldo_awal_adjustment ?? 0;
                $masuk = $row->qty_in ?? 0;
                $kirimDalam = $row->kirim_secondary_dalam ?? 0;
                $repDalam = $row->terima_repaired_secondary_dalam ?? 0;
                $goodDalam = $row->terima_good_secondary_dalam ?? 0;
                $kirimLuar = $row->kirim_secondary_luar ?? 0;
                $repLuar = $row->terima_repaired_secondary_luar ?? 0;
                $goodLuar = $row->terima_good_secondary_luar ?? 0;
                $loading = $row->loading_qty ?? 0;
                $adjustment = $row->adjustment ?? 0;
                $switchingIn = $row->switching_in ?? 0;
                $switchingOut = $row->switching_out ?? 0;

                $saldoAkhir =
                        $saldoAwal + $masuk - $kirimDalam + $repDalam + $goodDalam - $kirimLuar + $repLuar + $goodLuar - $loading
                        + ( $adjustment + $switchingIn - $switchingOut);
                $rows = [

                        $row->ws,
                        $row->buyer,
                        $row->style,
                        $row->color,
                        $row->size,
                        $row->panel,
                        $row->nama_part,

                        (float) $saldoAwal,
                        (float) $masuk,
                        (float) $kirimDalam,
                        (float) $repDalam,
                        (float) $goodDalam,
                        (float) $kirimLuar,
                        (float) $repLuar,
                        (float) $goodLuar,
                        (float) $loading,
                        (float) $adjustment,
                        (float) $saldoAkhir,
                ];


                $rows = array_merge($rows, [
                        (float) ($row->saldo_awal_secondary_dalam ?? 0),
                        (float) ($row->kirim_secondary_dalam ?? 0),
                        (float) ($row->terima_repaired_secondary_dalam ?? 0),
                        (float) ($row->terima_good_secondary_dalam ?? 0),
                        (float) ($row->qty_adjustment_secondary_dalam ?? 0),
                        (float) ($row->saldo_akhir_secondary_dalam ?? 0),
                ]);

                if ($isOld) {
                        $rows = array_merge($rows, [
                                (float) ($row->saldo_awal_secondary_luar ?? 0),
                                (float) ($row->kirim_secondary_luar ?? 0),
                                (float) ($row->terima_repaired_secondary_luar ?? 0),
                                (float) ($row->terima_good_secondary_luar ?? 0),
                                (float) ($row->qty_adjustment_secondary_luar ?? 0),
                                (float) ($row->saldo_akhir_secondary_luar ?? 0),
                        ]);

                } else {
                        $rows = array_merge($rows, [
                                (float) ($row->new_saldo_awal_secondary_luar ?? 0),
                                (float) ($row->new_terima_dc ?? 0),
                                (float) ($row->new_kirim_dc ?? 0),
                                (float) ($row->new_qty_adjustment_secondary_luar ?? 0),
                                (float) ($row->new_saldo_akhir_secondary_luar ?? 0),
                        ]);

                        $rows = array_merge($rows, [
                                (float) ($row->transit_saldo_awal_secondary_luar ?? 0),
                                (float) ($row->transit_terima_secondary_luar ?? 0),
                                (float) ($row->transit_kirim_rep_secondary_luar ?? 0),
                                (float) ($row->transit_kirim_good_secondary_luar ?? 0),
                                (float) ($row->transit_qty_adjustment_transit_terima_secondary_luar ?? 0),
                                (float) ($row->transit_saldo_akhir_secondary_luar ?? 0),
                        ]);
                }

                $sheet->writeRow($rows, [
                        'border' => 'thin',
                ]);
        }

        // A:Z
        foreach (range('A', 'Z') as $col) {
                $sheet->setColWidth($col, 20);
        }

        foreach ([ 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI'] as $col) {
                $sheet->setColWidth($col, 20);
        }

        return $excel->download();
    }

    public function buildQueryMutasiWipDcSet($dateFrom, $dateTo){

        // Saldo awal DC Set diambil dari saldo tersimpan (saldo_mutasi_dc_set),
        // tanggal awal laporan minimal tanggal ini
        $awalSaldo = '2026-11-01';

        if ($dateFrom < $awalSaldo) {
            $dateFrom = $awalSaldo;
        }

        // Snapshot saldo terakhir yang <= tanggal awal
        $tglSaldo = DB::table('saldo_mutasi_dc_set')->where('tgl_saldo', '<=', $dateFrom)->max('tgl_saldo') ?? $awalSaldo;

        // Saldo awal = saldo tersimpan pada tgl_saldo + SUM(masuk - loading + adjustment) dari tgl_saldo s/d sehari sebelum tanggal awal
        $mutasi = "
                SELECT
                    ws,
                    buyer,
                    style,
                    color,
                    size,
                    0 current_saldo_awal_adjustment,
                    qty_in,
                    loading,
                    adjustment
                FROM (
                    ".$this->queryMutasiWipDcSetRingan($dateFrom, $dateTo)."
                ) periode
                UNION ALL
                SELECT
                    ws,
                    buyer,
                    style,
                    color,
                    size,
                    qty current_saldo_awal_adjustment,
                    0 qty_in,
                    0 loading,
                    0 adjustment
                FROM
                    saldo_mutasi_dc_set
                WHERE
                    tgl_saldo = '".$tglSaldo."'
        ";

        if ($dateFrom > $tglSaldo) {
            $sebelum = date('Y-m-d', strtotime($dateFrom . ' -1 day'));

            $mutasi .= "
                UNION ALL
                SELECT
                    ws,
                    buyer,
                    style,
                    color,
                    size,
                    qty_in - loading + adjustment current_saldo_awal_adjustment,
                    0 qty_in,
                    0 loading,
                    0 adjustment
                FROM (
                    ".$this->queryMutasiWipDcSetRingan($tglSaldo, $sebelum)."
                ) sebelum
            ";
        }

        return "
                SELECT
                    ws,
                    buyer,
                    style,
                    color,
                    size,
                    SUM(current_saldo_awal_adjustment) current_saldo_awal_adjustment,
                    SUM(qty_in) qty_in,
                    SUM(loading) loading,
                    SUM(adjustment) adjustment,
                    (
                        SUM(current_saldo_awal_adjustment)
                        + SUM(qty_in)
                        - SUM(loading)
                        + SUM(adjustment)
                    ) current_saldo_akhir_adjustment
                FROM (
                    ".$mutasi."
                ) mutasi_dc_set
                GROUP BY
                    ws,
                    buyer,
                    style,
                    color,
                    size
                ORDER BY
                    ws,
                    color,
                    size
        ";
    }

    // Mutasi DC Set 1 periode versi ringan (khusus periode mulai awal modul DC Set):
    // hanya hitung masuk & loading per panel (MAX per set) + adjustment DC_SET.
    // Tidak memakai query DC lengkap (saldo before, secondary, dll) karena saldo awal DC Set
    // diambil dari saldo tersimpan / mutasi sebelumnya, dan adjustment DC per panel sudah tidak berlaku.
    // Logika masuk & loading mengikuti DcService::buildBaseCte (dc_current_saldo) & dc_loading_inject / dc_in_dump.
    private function queryMutasiWipDcSetRingan($dateFrom, $dateTo){

        $filterStocker = "
            (s.cancel IS NULL OR s.cancel != 'y') and
            ((s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') AND NOT (COALESCE(s.notes, '') = 'INJECT 10-11-2026 10:00' AND s.part_detail_id IN (SELECT pd_stk.id FROM part_detail pd_stk JOIN master_part mp_stk ON mp_stk.id = pd_stk.master_part_id WHERE mp_stk.nama_part = 'STK')))
        ";

        $panel = "COALESCE((CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'complement' THEN COALESCE(p_com.panel, p.panel) ELSE p.panel END), '-')";
        $namaPart = "COALESCE(NULLIF((CASE WHEN mp.nama_part = 'STK' AND COALESCE(mp.bag, '') != '' THEN CONCAT(mp.nama_part, '-', mp.bag) ELSE mp.nama_part END), ''), '-')";

        $joinPart = "
            left join master_sb_ws msb on msb.id_so_det = s.so_det_id
            left join part_detail pd on pd.id = s.part_detail_id
            left join part p on p.id = pd.part_id
            left join part_detail pd_com on pd_com.id = pd.from_part_detail
            left join part p_com on p_com.id = pd_com.part_id
            left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
            left join master_part mp on mp.id = pd.master_part_id
        ";

        return "
            SELECT
                ws,
                buyer,
                style,
                color,
                size,
                0 current_saldo_awal_adjustment,
                COALESCE(MAX(qty_in), 0) qty_in,
                COALESCE(MAX(loading), 0) loading,
                0 adjustment
            FROM (
                -- Total per panel & part
                SELECT
                    ws,
                    MAX(buyer) buyer,
                    MAX(style) style,
                    color,
                    size,
                    SUM(qty_in) qty_in,
                    SUM(loading) loading
                FROM (
                    -- Masuk DC
                    SELECT
                        msb.ws,
                        msb.buyer,
                        msb.styleno style,
                        UPPER(TRIM(msb.color)) color,
                        msb.size,
                        ".$panel." panel,
                        ".$namaPart." nama_part,
                        a.qty_awal qty_in,
                        0 loading
                    FROM
                        dc_in_input a
                        inner join stocker_input s on s.id_qr_stocker = a.id_qr_stocker
                        ".$joinPart."
                    WHERE
                        a.tgl_trans between '".$dateFrom."' and '".$dateTo."' and
                        ".$filterStocker."
                    UNION ALL
                    -- Loading line (qty per bundle = MIN qty dalam 1 panel)
                    SELECT
                        msb.ws,
                        msb.buyer,
                        msb.styleno style,
                        UPPER(TRIM(msb.color)) color,
                        msb.size,
                        ".$panel." panel,
                        ".$namaPart." nama_part,
                        0 qty_in,
                        COALESCE(
                            MIN(ll.qty) OVER (
                                PARTITION BY
                                    (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'complement' THEN COALESCE(p_com.panel, p.panel) ELSE p.panel END),
                                    s.form_cut_id,
                                    s.form_reject_id,
                                    s.form_piece_id,
                                    s.so_det_id,
                                    s.group_stocker,
                                    s.ratio,
                                    s.stocker_reject
                            ),
                            ll.qty
                        ) loading
                    FROM
                        loading_line ll
                        inner join stocker_input s on s.id = ll.stocker_id
                        ".$joinPart."
                    WHERE
                        ll.tanggal_loading between '".$dateFrom."' and '".$dateTo."' and
                        ".$filterStocker."
                    UNION ALL
                    -- Masuk DC dari data dump
                    SELECT
                        dc_in_dump.ws,
                        dc_in_dump.buyer,
                        dc_in_dump.style,
                        UPPER(TRIM(dc_in_dump.color)) color,
                        dc_in_dump.size,
                        COALESCE(dc_in_dump.panel, '-') panel,
                        COALESCE(NULLIF(dc_in_dump.part, ''), '-') nama_part,
                        dc_in_dump.qty_in,
                        0 loading
                    FROM
                        dc_in_dump
                    WHERE
                        dc_in_dump.tgl_trans between '".$dateFrom."' and '".$dateTo."' and
                        EXISTS (
                            SELECT 1
                            FROM part
                            inner join part_detail on part_detail.part_id = part.id
                            inner join master_part on master_part.id = part_detail.master_part_id
                            WHERE
                                part.act_costing_ws = dc_in_dump.ws and
                                part.panel = dc_in_dump.panel and
                                master_part.nama_part = dc_in_dump.part
                        )
                    UNION ALL
                    -- Loading inject
                    SELECT
                        ws,
                        buyer,
                        style,
                        UPPER(TRIM(color)) color,
                        size,
                        COALESCE(panel, '-') panel,
                        COALESCE(NULLIF(part, ''), '-') nama_part,
                        0 qty_in,
                        qty loading
                    FROM
                        dc_loading_inject
                    WHERE
                        tanggal between '".$dateFrom."' and '".$dateTo."'
                ) mutasi_panel
                GROUP BY
                    ws,
                    color,
                    size,
                    panel,
                    nama_part
            ) mutasi_set
            GROUP BY
                ws,
                buyer,
                style,
                color,
                size
            UNION ALL
            SELECT
                no_ws ws,
                buyer,
                style,
                color,
                size,
                0 current_saldo_awal_adjustment,
                0 qty_in,
                0 loading,
                SUM(qty) adjustment
            FROM
                wip_adjustment
            WHERE
                tgl_saldo between '".$dateFrom."' and '".$dateTo."' and
                type_report = 'DC_SET' and
                status = 'Y'
            GROUP BY
                no_ws,
                buyer,
                style,
                color,
                size
        ";
    }

    public function report_mutasi_wip_dc_set(Request $request){
        ini_set("max_execution_time", 36000);
        ini_set('memory_limit', '1024M');

        $dateFrom = $request->dateFrom;
        $dateTo = $request->dateTo;

        if ($request->ajax()) {
            if ($dateFrom === null || $dateTo === null) {
                return response()->json(['data' => []]);
            } else {
                $dataReport = DB::select($this->buildQueryMutasiWipDcSet($dateFrom, $dateTo));
            }

            return DataTables::of($dataReport)->toJson();
        }

        return view('dc.report.report-mutasi-set', [
            "page" => "dashboard-dc",
            "subPageGroup" => "report",
            "subPage" => "report_mutasi_wip_dc_set",
        ]);
    }

    public function export_excel_report_mutasi_wip_dc_set(Request $request){
        ini_set("max_execution_time", 36000);
        ini_set('memory_limit', '1024M');

        $start_date = $request->from;
        $end_date = $request->to;
        $dateFrom = $request->from;
        $dateTo = $request->to;

        $data = DB::select($this->buildQueryMutasiWipDcSet($dateFrom, $dateTo));

        $fileName = 'report-mutasi-wip-set-dc';

        $excel = FastExcel::create($fileName);

        $sheet = $excel->sheet();

        $sheet->writeRow(
            ['Report Mutasi WIP Set DC'],
            [
                'font-style' => 'bold',
                'font-size'  => 14,
            ]
        );

        $sheet->writeRow(
            ['Periode ' . $start_date . ' s/d ' . $end_date],
            [
                'font-size' => 12,
            ]
        );

        $sheet->writeRow(['']);

        $sheet->writeRow([
                'No. WS',
                'Buyer',
                'Style',
                'Color',
                'Size',
                'Mutasi DC',
                '',
                '',
                '',
                ''
        ], [
                'font-style' => 'bold',
                'border' => 'thin',
                'halign' => 'center',
                'valign'     => 'center',
                'text-align' => 'center',
        ]);

        $sheet->writeRow([
                '',
                '',
                '',
                '',
                '',
                'Saldo Awal',
                'Pemasukan',
                'Pengeluaran',
                'Adjustment',
                'Saldo Akhir'
        ], [
                'font-style' => 'bold',
                'border' => 'thin',
                'halign' => 'center',
                'valign'     => 'center',
                'text-align' => 'center',
        ]);

        $sheet->mergeCells('A4:A5');
        $sheet->mergeCells('B4:B5');
        $sheet->mergeCells('C4:C5');
        $sheet->mergeCells('D4:D5');
        $sheet->mergeCells('E4:E5');

        $sheet->mergeCells('F4:J4');

        foreach ($data as $row) {

        $rows = [
                $row->ws ?: '',
                $row->buyer ?: '',
                $row->style ?: '',
                $row->color ?: '',
                $row->size ?: '',

                (float) ($row->current_saldo_awal_adjustment ?? 0),
                (float) ($row->qty_in ?? 0),
                (float) ($row->loading ?? 0),
                (float) ($row->adjustment ?? 0),
                (float) ($row->current_saldo_akhir_adjustment ?? 0),
        ];

        $sheet->writeRow(
                $rows, [ 'border' => 'thin', ] );
        }

        foreach (range('A', 'J') as $col) {
                $sheet->setColWidth($col, 20);
        }

        return $excel->download();
    }

    public function report_terima_secondary_luar(Request $request) {
        ini_set("max_execution_time", 36000);
        ini_set('memory_limit', '1024M');

        $dateFrom = $request->dateFrom;
        $dateTo = $request->dateTo;

        if ($request->ajax()) {
            if ($dateFrom === null || $dateTo === null) {
                return response()->json(['data' => []]);
            } else {
                $dataReport = DB::select("
                        SELECT
                                *,
                                DATE_FORMAT(tanggal, '%d-%m-%Y') AS tanggal
                        FROM inject_mutasi_dc
                        WHERE tanggal BETWEEN ? AND ?
                        AND type_report = 'SECONDARY_LUAR'
                        AND cancelled_at IS NULL
                ", [$dateFrom, $dateTo]);
            }

            return DataTables::of($dataReport)->toJson();
        }

        return view('dc.report.report-terima-secondary-luar', [
            "page" => "dashboard-dc",
            "subPageGroup" => "report",
            "subPage" => "report_terima_secondary_luar",
        ]);
    }

    // Semua id hasil filter tabel (lintas halaman) untuk check all
    public function checkAllTerimaSecondaryLuar(Request $request) {
        $request->validate([
            'dateFrom' => 'required|date',
            'dateTo'   => 'required|date',
        ]);

        // Parameter filter kolom => kolom/ekspresi SQL (format sama dengan yang tampil di tabel)
        $filters = [
            'tanggal_filter'    => "DATE_FORMAT(tanggal, '%d-%m-%Y')",
            'no_ws_filter'      => 'no_ws',
            'buyer_filter'      => 'buyer',
            'style_filter'      => 'style',
            'color_filter'      => 'color',
            'size_filter'       => 'size',
            'panel_filter'      => 'panel',
            'part_filter'       => 'part',
            'qty_filter'        => 'qty',
            'created_by_filter' => 'created_by_username',
            'created_at_filter' => 'created_at',
        ];

        $query = DB::table('inject_mutasi_dc')
            ->select('id')
            ->whereBetween('tanggal', [$request->dateFrom, $request->dateTo])
            ->where('type_report', 'SECONDARY_LUAR')
            ->whereNull('cancelled_at');

        // Semua filter yang terisi dipakai bersamaan
        foreach ($filters as $param => $expression) {
            $value = trim((string) $request->input($param));

            if ($value !== '') {
                $query->whereRaw("CAST($expression AS CHAR) LIKE ?", ['%' . $value . '%']);
            }
        }

        return $query->get();
    }

    public function previewImportTerimaSecLuar(Request $request)
    {
        // 1. Validasi File
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            $file = $request->file('file');

            // 2. Langsung baca file Excel ke Array (Tanpa Import Class)
            // Hasilnya berupa array multidimensi: $sheets[index_sheet][index_baris][index_kolom]
            $sheets = Excel::toArray([], $file);
            $rows = $sheets[0] ?? []; // Ambil sheet pertama

            if (count($rows) <= 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'File kosong atau hanya berisi header.'
                ], 422);
            }

            $previewData = [];

            // 3. Loop mulai baris ke-2 (karena baris ke-1 index [0] adalah Header)
            foreach (array_slice($rows, 1) as $row) {
                // Skip jika kolom Tanggal & No. WS kosong
                if (empty($row[0]) && empty($row[1])) {
                    continue;
                }

                // Format Tanggal (Handling jika Excel kirim format serial date angka)
                $tanggal = $row[0];
                if (is_numeric($tanggal)) {
                    $tanggal = Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($tanggal))->format('Y-m-d');
                } else if ($tanggal) {
                    $tanggal = Carbon::parse($tanggal)->format('Y-m-d');
                }

                $previewData[] = [
                    'tanggal' => $tanggal ?? '-',
                    'no_ws'   => $row[1] ?? '-',
                    'buyer'   => $row[2] ?? '-',
                    'style'   => $row[3] ?? '-',
                    'color'   => $row[4] ?? '-',
                    'size'    => $row[5] ?? '-',
                    'panel'   => $row[6] ?? '-',
                    'part'    => $row[7] ?? '-',
                    'qty'     => isset($row[8]) ? (int)$row[8] : 0,
                ];
            }

            // 4. Simpan sementara ke Session
            session(['temp_terima_secondary_luar_import' => $previewData]);

            // 5. Return JSON ke Frontend
            return response()->json([
                'success' => true,
                'data'    => $previewData,
                'summary' => 'Total data terbaca: <strong>' . count($previewData) . '</strong> baris.',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membaca file: ' . $e->getMessage()
            ], 422);
        }
    }

    // 2. FUNGSI SIMPAN KE DATABASE PERMANEN
    public function storeImportTerimaSecLuar(Request $request)
    {
        $user = Auth::user();

        $data = session('temp_terima_secondary_luar_import');

        if (empty($data)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada data preview yang bisa disimpan!'
            ], 422);
        }

        // Check Closing Date (per tanggal unik, sebelum transaksi dimulai)
        foreach (array_unique(array_column($data, 'tanggal')) as $tanggal) {
            if (checkClosingDate($tanggal)) {
                return response()->json([
                    'success'    => false,
                    'message'    => 'Data tidak dapat disimpan karena periode ' . date('d-m-Y', strtotime($tanggal)) . ' sudah ditutup.',
                    'additional' => 'Closing'
                ], 422);
            }
        }

        try {
            DB::beginTransaction();

            foreach ($data as$item) {
                // Ganti 'nama_tabel_kamu' dengan tabel database tujuan
                DB::table('inject_mutasi_dc')->insert([
                    'type_report'         => "SECONDARY_LUAR",
                    'tanggal'             => $item['tanggal'],
                    'no_ws'               => $item['no_ws'],
                    'buyer'               => $item['buyer'],
                    'style'               => $item['style'],
                    'color'               => $item['color'],
                    'size'                => $item['size'],
                    'panel'               => $item['panel'],
                    'part'                => $item['part'],
                    'qty'                 => $item['qty'],
                    'created_by'          => $user->id,
                    'created_by_username' => $user->username,
                    'created_at'          => now(),
                    'updated_at'          => now(),
                ]);
            }

            DB::commit();

            // Bersihkan session setelah berhasil disimpan
            session()->forget('temp_terima_secondary_luar_import');

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil disimpan ke database!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan data: ' . $e->getMessage()
            ], 500);
        }
    }

    // 3. FUNGSI KOSONGKAN SESSION PREVIEW
    public function emptyPreviewTerimaSecLuar(Request $request)
    {
        session()->forget('temp_terima_secondary_luar_import');

        return response()->json([
            'success' => true,
            'message' => 'Preview berhasil dikosongkan.'
        ]);
    }

    // 4. FUNGSI CANCEL DATA TERIMA SECONDARY LUAR
    public function cancelTerimaSecLuar(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'integer',
        ]);

        $user = Auth::user();

        // Check Closing Date (cukup tanggal paling awal dari data yang akan dicancel)
        $minTanggal = DB::table('inject_mutasi_dc')
            ->whereIn('id', $request->ids)
            ->where('type_report', 'SECONDARY_LUAR')
            ->whereNull('cancelled_at')
            ->min('tanggal');

        if ($minTanggal && checkClosingDate($minTanggal)) {
            return response()->json([
                'success' => false,
                'message' => 'Data periode ' . date('d-m-Y', strtotime($minTanggal)) . ' sudah closing, tidak dapat dicancel.'
            ], 422);
        }

        $cancelled = DB::table('inject_mutasi_dc')
            ->whereIn('id', $request->ids)
            ->where('type_report', 'SECONDARY_LUAR')
            ->whereNull('cancelled_at')
            ->update([
                'cancelled_at'          => now(),
                'cancelled_by'          => $user->id,
                'cancelled_by_username' => $user->username,
                'updated_at'            => now(),
            ]);

        if (!$cancelled) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan atau sudah dicancel.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => $cancelled . ' data berhasil dicancel.'
        ]);
    }

    public function export_excel_report_terima_secondary_luar(Request $request)
    {
        $start_date = $request->from;
        $end_date = $request->to;
        $dateFrom = $request->from;
        $dateTo = $request->to;

        $data = DB::select("
                SELECT
                        *,
                        DATE_FORMAT(tanggal, '%d-%m-%Y') AS tanggal
                FROM inject_mutasi_dc
                WHERE tanggal BETWEEN ? AND ?
                AND type_report = 'SECONDARY_LUAR'
                AND cancelled_at IS NULL
        ", [$dateFrom, $dateTo]);

        $fileName = 'report-mutasi-wip-set-dc';

        $excel = FastExcel::create($fileName);

        $sheet = $excel->sheet();

        $sheet->writeRow(
            ['Report Terima Secondary Luar'],
            [
                'font-style' => 'bold',
                'font-size'  => 14,
            ]
        );

        $sheet->writeRow(
            ['Periode ' . $start_date . ' s/d ' . $end_date],
            [
                'font-size' => 12,
            ]
        );

        $sheet->writeRow(['']);

        $sheet->writeRow([
                'Tanggal',
                'No. WS',
                'Buyer',
                'Style',
                'Color',
                'Size',
                'Panel',
                'Part',
                'Qty',
                'Dibuat Oleh',
                'Dibuat Pada',
        ], [
                'font-style' => 'bold',
                'border' => 'thin',
                'halign' => 'center',
                'valign'     => 'center',
                'text-align' => 'center',
        ]);

        foreach ($data as $row) {

        $rows = [
                $row->tanggal ?: '',
                $row->no_ws ?: '',
                $row->buyer ?: '',
                $row->style ?: '',
                $row->color ?: '',
                $row->size ?: '',
                (string) $row->panel,
                (string) $row->part,
                (float) ($row->qty ?? 0),
                $row->created_by_username ?: '',
                $row->created_at ?: '',
        ];

        $sheet->writeRow(
                $rows, [ 'border' => 'thin', ] );
        }

        foreach (range('A', 'I') as $col) {
                $sheet->setColWidth($col, 20);
        }

        return $excel->download();
    }
}
