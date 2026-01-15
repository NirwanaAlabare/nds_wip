<?php

namespace App\Http\Controllers\Cutting;

use App\Http\Controllers\Controller;
use App\Models\Cutting\ScannedItem;
use App\Models\Cutting\FormCutInputDetail;
use App\Exports\ExportLaporanRoll;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use DNS1D;
use PDF;
use DB;
use \avadim\FastExcelLaravel\Excel as FastExcel;
//
use Illuminate\Support\Facades\Auth;

class RollController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        ini_set("memory_limit", "1024M");
        ini_set("max_execution_time", 36000);

        // if ($request->ajax()) {
        //     $additionalQuery = "";

        //     if ($request->dateFrom) {
        //         $additionalQuery .= " and DATE(b.created_at) >= '" . $request->dateFrom . "'";
        //     }

        //     if ($request->dateTo) {
        //         $additionalQuery .= " and DATE(b.created_at) <= '" . $request->dateTo . "'";
        //     }

        //     $keywordQuery = "";
        //     if ($request->search["value"]) {
        //         $keywordQuery = "
        //             and (
        //                 act_costing_ws like '%" . $request->search["value"] . "%' OR
        //                 DATE_FORMAT(b.created_at, '%d-%m-%Y') like '%" . $request->search["value"] . "%'
        //             )
        //         ";
        //     }

        //     $data_pemakaian = DB::select("
        //         select
        //             DATE_FORMAT(b.updated_at, '%M) bulan,
        //             DATE_FORMAT(b.updated_at, '%d-%m-%Y) tgl_input,
        //             b.no_form_cut_input,
        //             UPPER(meja.name) nama_meja,
        //             act_costing_ws,
        //             buyer,
        //             style,
        //             color,
        //             b.color_act,
        //             panel,
        //             master_sb_ws.qty,
        //             cons_ws,
        //             cons_marker,
        //             a.cons_ampar,
        //             a.cons_act,
        //             COALESCE(a.cons_pipping, cons_piping) cons_piping,
        //             panjang_marker,
        //             unit_panjang_marker,
        //             comma_marker,
        //             unit_comma_marker,
        //             lebar_marker,
        //             unit_lebar_marker,
        //             a.p_act panjang_actual,
        //             a.unit_p_act unit_panjang_actual,
        //             a.comma_p_act comma_actual,
        //             a.unit_comma_p_act unit_comma_actual,
        //             a.l_act lebar_actual,
        //             a.unit_l_actual unit_lebar_actual,
        //             COALESCE(id_roll, '-') id_roll,
        //             id_item,
        //             detail_item,
        //             COALESCE(b.roll_buyer, b.roll) roll,
        //             COALESCE(b.lot, '-') lot,
        //             COALESCE(b.group_roll, '-') group_roll,
        //             b.qty qty_roll,
        //             b.unit unit_roll,
        //             COALESCE(b.berat_amparan, '-') berat_amparan,
        //             b.est_amparan,
        //             b.lembar_gelaran,
        //             mrk.total_ratio,
        //             (mrk.total_ratio * b.lembar_gelaran) qty_cut,
        //             b.average_time,
        //             b.sisa_gelaran,
        //             b.sambungan,
        //             b.sambungan_roll,
        //             b.kepala_kain,
        //             b.lembar_gelaran,
        //             b.sisa_tidak_bisa,
        //             b.reject,
        //             b.piping,
        //             COALESCE(b.sisa_kain, 0) sisa_kain,
        //             b.pemakaian_lembar,
        //             b.total_pemakaian_roll,
        //             b.short_roll,
        //             CONCAT(ROUND(((b.short_roll / b.qty) * 100), 2), ' %') short_roll_percentage,
        //             a.operator
        //         from
        //             form_cut_input a
        //             left join form_cut_input_detail b on a.no_form = b.no_form_cut_input
        //             left join users meja on meja.id = a.no_meja
        //             left join marker_input mrk on a.id_marker = mrk.kode
        //         where
        //             (a.cancel = 'N'  OR a.cancel IS NULL)
        //             AND (mrk.cancel = 'N'  OR mrk.cancel IS NULL)
        //             and b.status != 'not completed'
        //             and id_item is not null
        //             " . $additionalQuery . "
        //             " . $keywordQuery . "
        //         group by
        //             b.id
        //         order by
        //             a.waktu_mulai asc,
        //             b.id asc
        //     ");

        //     return DataTables::of($data_pemakaian)->toJson();
        // }

        return view('cutting.roll.roll', ['page' => 'dashboard-cutting', "subPageGroup" => "laporan-cutting", "subPage" => "lap-pemakaian"]);
    }

    public function pemakaianRollData(Request $request)
    {
        ini_set("memory_limit", "2048M");
        ini_set("max_execution_time", 36000);

        $additionalQuery = "";
        $additionalQuery1 = "";
        $additionalQuery2 = "";

        if ($request->dateFrom) {
            $additionalQuery .= " and b.created_at >= '" . $request->dateFrom . " 00:00:00'";
            $additionalQuery1 .= " and form_cut_piping.created_at >= '" . $request->dateFrom . " 00:00:00'";
            $additionalQuery2 .= " and form_cut_piece_detail.created_at >= '" . $request->dateFrom . " 00:00:00'";
        }

        if ($request->dateTo) {
            $additionalQuery .= " and b.created_at <= '" . $request->dateTo . " 23:59:59'";
            $additionalQuery1 .= " and form_cut_piping.created_at <= '" . $request->dateTo . " 23:59:59'";
            $additionalQuery2 .= " and form_cut_piece_detail.created_at <= '" . $request->dateTo . " 23:59:59'";
        }

        if ($request->supplier) {
            $additionalQuery .= " and msb.buyer LIKE '%" . $request->supplier . "%'";
            $additionalQuery1 .= " and msb.buyer LIKE '%" . $request->supplier . "%'";
            $additionalQuery2 .= " and msb.buyer LIKE '%" . $request->supplier . "%'";
        }

        if ($request->id_ws) {
            $additionalQuery .= " and mrk.act_costing_id = " . $request->id_ws . "";
            $additionalQuery1 .= " and form_cut_piping.act_costing_id = " . $request->id_ws . "";
            $additionalQuery2 .= " and form_cut_piece.act_costing_id = " . $request->id_ws . "";
        }

        $keywordQuery = "";
        $keywordQuery1 = "";
        $keywordQuery2 = "";
        if ($request->search["value"]) {
            $keywordQuery = "
                and (
                    act_costing_ws like '%" . $request->search["value"] . "%' OR
                    DATE_FORMAT(b.created_at, '%d-%m-%Y') like '%" . $request->search["value"] . "%'
                )
            ";

            $keywordQuery1 = "
                and (
                    act_costing_ws like '%" . $request->search["value"] . "%' OR
                    DATE_FORMAT(form_cut_piping.created_at, '%d-%m-%Y') like '%" . $request->search["value"] . "%'
                )
            ";

            $keywordQuery2 = "
                and (
                    act_costing_ws like '%" . $request->search["value"] . "%' OR
                    DATE_FORMAT(form_cut_piece_detail.created_at, '%d-%m-%Y') like '%" . $request->search["value"] . "%'
                )
            ";
        }

        $data_pemakaian = DB::select("
            select * from (
                select
                    COALESCE(b.qty) qty_in,
                    a.waktu_mulai,
                    a.waktu_selesai,
                    b.id,
                    DATE_FORMAT(b.created_at, '%M') bulan,
                    DATE_FORMAT(b.created_at, '%d-%m-%Y') tgl_input,
                    b.no_form_cut_input,
                    UPPER(meja.name) nama_meja,
                    mrk.act_costing_ws,
                    master_sb_ws.buyer,
                    mrk.style,
                    mrk.color,
                    COALESCE(b.color_act, '-') color_act,
                    mrk.panel,
                    master_sb_ws.qty,
                    cons_ws,
                    cons_marker,
                    a.cons_ampar,
                    a.cons_act,
                    (CASE WHEN a.cons_pipping > 0 THEN a.cons_pipping ELSE mrk.cons_piping END) cons_piping,
                    panjang_marker,
                    unit_panjang_marker,
                    comma_marker,
                    unit_comma_marker,
                    lebar_marker,
                    unit_lebar_marker,
                    a.p_act panjang_actual,
                    a.unit_p_act unit_panjang_actual,
                    a.comma_p_act comma_actual,
                    a.unit_comma_p_act unit_comma_actual,
                    a.l_act lebar_actual,
                    a.unit_l_act unit_lebar_actual,
                    COALESCE(b.id_roll, '-') id_roll,
                    b.id_item,
                    b.detail_item,
                    COALESCE(b.roll_buyer, b.roll) roll,
                    COALESCE(b.lot, '-') lot,
                    COALESCE(b.group_roll, '-') group_roll,
                    (
                            CASE WHEN
                                    b.status != 'extension' AND b.status != 'extension complete'
                            THEN
                                    (CASE WHEN COALESCE(scanned_item.qty_in, b.qty) > b.qty AND c.id IS NULL THEN 'Sisa Kain' ELSE 'Roll Utuh' END)
                            ELSE
                                    'Sambungan'
                            END
                    ) status_roll,
                    COALESCE(c.qty, b.qty) qty_awal,
                    b.qty qty_roll,
                    b.unit unit_roll,
                    COALESCE(b.berat_amparan, '-') berat_amparan,
                    b.est_amparan,
                    b.lembar_gelaran,
                    mrk.total_ratio,
                    (mrk.total_ratio * b.lembar_gelaran) qty_cut,
                    b.average_time,
                    b.sisa_gelaran,
                    b.sambungan,
                    b.sambungan_roll,
                    b.kepala_kain,
                    b.sisa_tidak_bisa,
                    b.reject,
                    b.piping,
                    ROUND(MIN(CASE WHEN b.status != 'extension' AND b.status != 'extension complete' THEN (b.sisa_kain) ELSE (b.qty - b.total_pemakaian_roll) END), 2) sisa_kain,
                    ROUND((CASE WHEN b.status != 'extension complete' THEN ((CASE WHEN b.unit = 'KGM' THEN b.berat_amparan ELSE a.p_act + (a.comma_p_act/100) END) * b.lembar_gelaran) ELSE b.sambungan END) + (b.sisa_gelaran ) + (b.sambungan_roll ) , 2) pemakaian_lembar,
                    ROUND((CASE WHEN b.status != 'extension complete' THEN ((CASE WHEN b.unit = 'KGM' THEN b.berat_amparan ELSE a.p_act + (a.comma_p_act/100) END) * b.lembar_gelaran) ELSE b.sambungan END) + (b.sisa_gelaran) + (b.sambungan_roll) + (b.kepala_kain) + (b.sisa_tidak_bisa) + (b.reject) + (b.piping), 2) total_pemakaian_roll,
                    ROUND(((CASE WHEN b.status != 'extension complete' THEN ((CASE WHEN b.unit = 'KGM' THEN b.berat_amparan ELSE a.p_act + (a.comma_p_act/100) END) * b.lembar_gelaran) ELSE b.sambungan END) + (b.sisa_gelaran) + (b.sambungan_roll) + (b.kepala_kain) + (b.sisa_tidak_bisa) + (b.reject) + (b.piping))+(ROUND(MIN(CASE WHEN b.status != 'extension' AND b.status != 'extension complete' THEN (b.sisa_kain) ELSE (b.qty - b.total_pemakaian_roll) END), 2))-b.qty, 2) short_roll,
                    ROUND((((CASE WHEN b.status != 'extension complete' THEN ((CASE WHEN b.unit = 'KGM' THEN b.berat_amparan ELSE a.p_act + (a.comma_p_act/100) END) * b.lembar_gelaran) ELSE b.sambungan END) + (b.sisa_gelaran) + (b.sambungan_roll) + (b.kepala_kain) + (b.sisa_tidak_bisa) + (b.reject) + (b.piping)+(ROUND(MIN(CASE WHEN b.status != 'extension' AND b.status != 'extension complete' THEN (b.sisa_kain) ELSE (b.qty - b.total_pemakaian_roll) END), 2)))-b.qty)/b.qty*100, 2) short_roll_percentage,
                    b.status,
                    a.operator,
                    a.tipe_form_cut,
                    b.created_at,
                    b.updated_at,
                    (CASE WHEN d.id is null and e.id is null THEN 'latest' ELSE 'not latest' END) roll_status
                from
                    form_cut_input a
                    left join form_cut_input_detail b on a.id = b.form_cut_id
                    left join form_cut_input_detail c ON c.form_cut_id = b.form_cut_id and c.id_roll = b.id_roll and (c.status = 'extension' OR c.status = 'extension complete')
                    LEFT JOIN form_cut_input_detail d on d.id_roll = b.id_roll AND b.id != d.id AND d.created_at > b.created_at and d.created_at >= '2025-01-01 00:00:00' and d.created_at <= '2025-12-31 23:59:59'
                    LEFT JOIN form_cut_piping e on e.id_roll = b.id_roll AND e.created_at > b.created_at and e.created_at >= '2025-01-01 00:00:00' and e.created_at <= '2025-12-31 23:59:59'
                    left join users meja on meja.id = a.no_meja
                    left join (SELECT marker_input.*, SUM(marker_input_detail.ratio) total_ratio FROM marker_input LEFT JOIN marker_input_detail ON marker_input_detail.marker_id = marker_input.id GROUP BY marker_input.id) mrk on a.id_marker = mrk.kode
                    left join (SELECT * FROM master_sb_ws GROUP BY id_act_cost) master_sb_ws on master_sb_ws.id_act_cost = mrk.act_costing_id
                    left join scanned_item on scanned_item.id_roll = b.id_roll
                where
                    (a.cancel = 'N'  OR a.cancel IS NULL)
                    AND (mrk.cancel = 'N'  OR mrk.cancel IS NULL)
                    AND a.status = 'SELESAI PENGERJAAN'
                    and b.status != 'not complete'
                    and b.id_item is not null
                    " . $additionalQuery . "
                    " . $keywordQuery . "
                group by
                    b.id
                UNION ALL
                select
                    COALESCE(form_cut_piping.qty) qty_in,
                    form_cut_piping.created_at waktu_mulai,
                    form_cut_piping.updated_at waktu_selesai,
                    form_cut_piping.id,
                    DATE_FORMAT(form_cut_piping.created_at, '%M') bulan,
                    DATE_FORMAT(form_cut_piping.created_at, '%d-%m-%Y') tgl_input,
                    'PIPING' no_form_cut_input,
                    '-' nama_meja,
                    form_cut_piping.act_costing_ws,
                    master_sb_ws.buyer,
                    form_cut_piping.style,
                    form_cut_piping.color,
                    form_cut_piping.color color_act,
                    form_cut_piping.panel,
                    master_sb_ws.qty,
                    '0' cons_ws,
                    0 cons_marker,
                    '0' cons_ampar,
                    0 cons_act,
                    form_cut_piping.cons_piping cons_piping,
                    0 panjang_marker,
                    '-' unit_panjang_marker,
                    0 comma_marker,
                    '-' unit_comma_marker,
                    0 lebar_marker,
                    '-' unit_lebar_marker,
                    0 panjang_actual,
                    '-' unit_panjang_actual,
                    0 comma_actual,
                    '-' unit_comma_actual,
                    0 lebar_actual,
                    '-' unit_lebar_actual,
                    form_cut_piping.id_roll,
                    scanned_item.id_item,
                    scanned_item.detail_item,
                    COALESCE(scanned_item.roll_buyer, scanned_item.roll) roll,
                    scanned_item.lot,
                    '-' group_roll,
                    'Piping' status_roll,
                    COALESCE(scanned_item.qty_in, form_cut_piping.qty) qty_awal,
                    form_cut_piping.qty qty_roll,
                    form_cut_piping.unit unit_roll,
                    0 berat_amparan,
                    0 est_amparan,
                    0 lembar_gelaran,
                    0 total_ratio,
                    0 qty_cut,
                    '00:00' average_time,
                    '0' sisa_gelaran,
                    0 sambungan,
                    0 sambungan_roll,
                    0 kepala_kain,
                    0 sisa_tidak_bisa,
                    0 reject,
                    form_cut_piping.piping piping,
                    form_cut_piping.qty_sisa sisa_kain,
                    form_cut_piping.piping pemakaian_lembar,
                    form_cut_piping.piping total_pemakaian_roll,
                    ROUND((form_cut_piping.piping + form_cut_piping.qty_sisa) - form_cut_piping.qty, 2) short_roll,
                    ROUND(((form_cut_piping.piping + form_cut_piping.qty_sisa) - form_cut_piping.qty)/coalesce(scanned_item.qty_in, form_cut_piping.qty) * 100, 2) short_roll_percentage,
                    null `status`,
                    form_cut_piping.operator,
                    'PIPING' tipe_form_cut,
                    form_cut_piping.created_at,
                    form_cut_piping.updated_at,
                    (CASE WHEN c.id is null THEN 'latest' ELSE 'not latest' END) roll_status
                from
                    form_cut_piping
                    LEFT JOIN form_cut_input_detail b on b.id_roll = form_cut_piping.id_roll AND b.created_at > form_cut_piping.created_at and b.created_at >= '2025-01-01 00:00:00' and b.created_at <= '2025-12-31 23:59:59'
                    LEFT JOIN form_cut_piping c on c.id_roll = form_cut_piping.id_roll AND c.id != form_cut_piping.id and c.created_at > form_cut_piping.created_at and c.created_at >= '2025-01-01 00:00:00' and c.created_at <= '2025-12-31 23:59:59'
                    left join (SELECT * FROM master_sb_ws GROUP BY id_act_cost) master_sb_ws on master_sb_ws.id_act_cost = form_cut_piping.act_costing_id
                    left join scanned_item on scanned_item.id_roll = form_cut_piping.id_roll
                where
                    scanned_item.id_item is not null
                    " . $additionalQuery1 . "
                    " . $keywordQuery1 . "
                group by
                    form_cut_piping.id
                UNION ALL
                SELECT
                    form_cut_piece_detail.qty qty_in,
                    form_cut_piece.created_at waktu_mulai,
                    form_cut_piece.updated_at waktu_selesai,
                    form_cut_piece.id,
                    DATE_FORMAT( form_cut_piece.created_at, '%M' ) bulan,
                    DATE_FORMAT( form_cut_piece.created_at, '%d-%m-%Y' ) tgl_input,
                    form_cut_piece.no_form no_form_cut_input,
                    '-' nama_meja,
                    form_cut_piece.act_costing_ws,
                    master_sb_ws.buyer,
                    form_cut_piece.style,
                    form_cut_piece.color,
                    form_cut_piece.color color_act,
                    form_cut_piece.panel,
                    master_sb_ws.qty,
                    form_cut_piece.cons_ws cons_ws,
                    form_cut_piece.cons_ws cons_marker,
                    '0' cons_ampar,
                    0 cons_act,
                    0 cons_piping,
                    0 panjang_marker,
                    '-' unit_panjang_marker,
                    0 comma_marker,
                    '-' unit_comma_marker,
                    0 lebar_marker,
                    '-' unit_lebar_marker,
                    0 panjang_actual,
                    '-' unit_panjang_actual,
                    0 comma_actual,
                    '-' unit_comma_actual,
                    0 lebar_actual,
                    '-' unit_lebar_actual,
                    form_cut_piece_detail.id_roll,
                    scanned_item.id_item,
                    scanned_item.detail_item,
                    COALESCE ( scanned_item.roll_buyer, scanned_item.roll ) roll,
                    scanned_item.lot,
                    '-' group_roll,
                    ( CASE WHEN form_cut_piece_detail.qty >= COALESCE ( scanned_item.qty_in, 0 ) THEN 'Roll Utuh' ELSE 'Sisa Kain' END ) status_roll,
                    COALESCE ( scanned_item.qty_in, form_cut_piece_detail.qty ) qty_awal,
                    form_cut_piece_detail.qty qty_roll,
                    form_cut_piece_detail.qty_unit unit_roll,
                    0 berat_amparan,
                    0 est_amparan,
                    0 lembar_gelaran,
                    0 total_ratio,
                    0 qty_cut,
                    '00:00' average_time,
                    '0' sisa_gelaran,
                    0 sambungan,
                    0 sambungan_roll,
                    0 kepala_kain,
                    0 sisa_tidak_bisa,
                    0 reject,
                    0 piping,
                    form_cut_piece_detail.qty_sisa sisa_kain,
                    form_cut_piece_detail.qty_pemakaian pemakaian_lembar,
                    form_cut_piece_detail.qty_pemakaian total_pemakaian_roll,
                    ROUND(
                    form_cut_piece_detail.qty - ( form_cut_piece_detail.qty_pemakaian + form_cut_piece_detail.qty_sisa )) short_roll,
                    ROUND((form_cut_piece_detail.qty - ( form_cut_piece_detail.qty_pemakaian + form_cut_piece_detail.qty_sisa ))/ COALESCE ( scanned_item.qty_in, form_cut_piece_detail.qty ) * 100, 2 ) short_roll_percentage,
                    form_cut_piece_detail.STATUS `status`,
                    form_cut_piece.employee_name,
                    'PCS' tipe_form_cut,
                    form_cut_piece.created_at,
                    form_cut_piece.updated_at,
                    (CASE WHEN b.id is null THEN 'latest' ELSE 'not latest' END) roll_status
                FROM
                    form_cut_piece
                    LEFT JOIN form_cut_piece_detail ON form_cut_piece_detail.form_id = form_cut_piece.id
                    LEFT JOIN form_cut_piece_detail b on b.id_roll = form_cut_piece_detail.id_roll AND b.created_at > form_cut_piece_detail.created_at
                    LEFT JOIN ( SELECT * FROM master_sb_ws GROUP BY id_act_cost ) master_sb_ws ON master_sb_ws.id_act_cost = form_cut_piece.act_costing_id
                    LEFT JOIN scanned_item ON scanned_item.id_roll = form_cut_piece_detail.id_roll
                WHERE
                    scanned_item.id_item IS NOT NULL
                    AND form_cut_piece_detail.STATUS = 'complete'
                    " . $additionalQuery2 . "
                    " . $keywordQuery2 . "
                GROUP BY
                    form_cut_piece_detail.id
            ) roll_consumption
            order by
                no_form_cut_input,
                id_roll,
                created_at asc
        ");

        return DataTables::of($data_pemakaian)->toJson();
    }

    public function getSupplier(Request $request)
    {
        $suppliers = DB::connection('mysql_sb')->table('mastersupplier')->selectRaw('Id_Supplier as id, Supplier as name')->leftJoin('act_costing', 'act_costing.id_buyer', '=', 'mastersupplier.Id_Supplier')->where('mastersupplier.tipe_sup', 'C')->where('status', '!=', 'CANCEL')->where('type_ws', 'STD')->where('cost_date', '>=', '2023-01-01')->orderBy('Supplier', 'ASC')->groupBy('Id_Supplier', 'Supplier')->get();

        return $suppliers;
    }

    public function getOrder(Request $request)
    {
        $orderSql = DB::connection('mysql_sb')->table('act_costing')->selectRaw('
                id as id_ws,
                kpno as no_ws
            ')->where('status', '!=', 'CANCEL')->where('cost_date', '>=', '2023-01-01')->where('type_ws', 'STD');
        if ($request->supplier) {
            $orderSql->where('id_buyer', $request->supplier);
        }
        $orders = $orderSql->orderBy('cost_date', 'desc')->orderBy('kpno', 'asc')->groupBy('kpno')->get();

        return $orders;
    }

    public function export_excel(Request $request)
    {
        ini_set("memory_limit", "2048M");
        ini_set("max_execution_time", 36000);

        $excel = FastExcel::create('data');
        $sheet = $excel->getSheet();

        $area = $sheet->beginArea();

        $sheet->writeTo('A1', 'Laporan Pemakaian Roll Roll', ['font-size' => 16]);
        $sheet->writeTo('A2', $request->dateFrom.' / '.$request->dateTo, ['font-size' => 16]);
        // $sheet->mergeCells('A1:S1');

        $sheet->writeTo("A4", "No.")->applyBgColor('#a9cff5')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo("B4", "Bulan")->applyBgColor('#a9cff5')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo("C4", "Tanggal Input")->applyBgColor('#a9cff5')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo("D4", "No. Form")->applyBgColor('#a9cff5')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo("E4", "Meja")->applyBgColor('#a9cff5')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo("F4", "No. WS")->applyBgColor('#a9cff5')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo("G4", "Buyer")->applyBgColor('#a9cff5')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo("H4", "Style")->applyBgColor('#a9cff5')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo("I4", "Color")->applyBgColor('#a9cff5')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo("J4", "Color Actual")->applyBgColor('#a9cff5')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo("K4", "Panel")->applyBgColor('#a9cff5')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo("L4", "Qty Order")->applyBgColor('#a9cff5')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo("M4", "Cons. WS")->applyBgColor('#a9cff5')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo("N4", "Cons. Marker")->applyBgColor('#a9cff5')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo("O4", "Cons. Ampar")->applyBgColor('#a9cff5')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo("P4", "Cons. Actual")->applyBgColor('#a9cff5')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo("Q4", "Cons. Piping")->applyBgColor('#a9cff5')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo("R4", "Panjang Marker")->applyBgColor('#a9cff5')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo("S4", "Unit Panjang Marker")->applyBgColor('#a9cff5')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo("T4", "Comma Marker")->applyBgColor('#a9cff5')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo("U4", "Unit Comma Marker")->applyBgColor('#a9cff5')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo("V4", "Lebar Marker")->applyBgColor('#a9cff5')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo("W4", "Unit Lebar Marker")->applyBgColor('#a9cff5')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo("X4", "Panjang Actual")->applyBgColor('#a9cff5')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo("Y4", "Unit Panjang Actual")->applyBgColor('#a9cff5')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo("Z4", "Comma Actual")->applyBgColor('#a9cff5')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo("AA4", "Unit Comma Actual")->applyBgColor('#a9cff5')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo("AB4", "Lebar Actual")->applyBgColor('#a9cff5')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo("AC4", "Unit Lebar Actual")->applyBgColor('#a9cff5')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo("AD4", "ID Roll")->applyBgColor('#f5dda9')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo("AE4", "ID Item")->applyBgColor('#f5dda9')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo("AF4", "Detail Item")->applyBgColor('#f5dda9')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo("AG4", "No. Roll")->applyBgColor('#f5dda9')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo("AH4", "Lot")->applyBgColor('#f5dda9')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo("AI4", "Group")->applyBgColor('#f5dda9')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo("AJ4", "Status Roll")->applyBgColor('#f5dda9')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo("AK4", "Qty Roll")->applyBgColor('#f5dda9')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo("AL4", "Unit Roll")->applyBgColor('#f5dda9')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo("AM4", "Berat Amparan (KGM)")->applyBgColor('#f5dda9')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo("AN4", "Estimasi Amparan")->applyBgColor('#f5dda9')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo("AO4", "Lembar Amparan")->applyBgColor('#f5dda9')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo("AP4", "Ratio")->applyBgColor('#f5dda9')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo("AQ4", "Qty Cut")->applyBgColor('#f5dda9')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo("AR4", "Average Time")->applyBgColor('#f5dda9')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo("AS4", "Sisa Gelaran")->applyBgColor('#f5dda9')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo("AT4", "Sambungan")->applyBgColor('#f5dda9')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo("AU4", "Sambungan Roll")->applyBgColor('#f5dda9')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo("AV4", "Kepala Kain")->applyBgColor('#f5dda9')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo("AW4", "Sisa Tidak Bisa")->applyBgColor('#f5dda9')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo("AX4", "Reject")->applyBgColor('#f5dda9')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo("AY4", "Piping")->applyBgColor('#f5dda9')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo("AZ4", "Sisa Kain")->applyBgColor('#f5dda9')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo("BA4", "Pemakaian Gelar")->applyBgColor('#f5dda9')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo("BB4", "Total Pemakaian Roll")->applyBgColor('#f5dda9')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo("BC4", "Short Roll")->applyBgColor('#f5dda9')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo("BD4", "Short Roll (%)")->applyBgColor('#f5dda9')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo("BE4", "Operator")->applyBgColor('#f5dda9')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        $additionalQuery = "";
        $additionalQuery1 = "";
        $additionalQuery2 = "";

        if ($request->dateFrom) {
            $additionalQuery .= " and b.created_at >= '" . $request->dateFrom . " 00:00:00'";
            $additionalQuery1 .= " and form_cut_piping.created_at >= '" . $request->dateFrom . " 00:00:00'";
            $additionalQuery2 .= " and form_cut_piece_detail.created_at >= '" . $request->dateFrom . " 00:00:00'";
        }

        if ($request->dateTo) {
            $additionalQuery .= " and b.created_at <= '" . $request->dateTo . " 23:59:59'";
            $additionalQuery1 .= " and form_cut_piping.created_at <= '" . $request->dateTo . " 23:59:59'";
            $additionalQuery2 .= " and form_cut_piece_detail.created_at <= '" . $request->dateTo . " 23:59:59'";
        }

        if ($request->supplier) {
            $additionalQuery .= " and msb.buyer LIKE '%" . $request->supplier . "%'";
            $additionalQuery1 .= " and msb.buyer LIKE '%" . $request->supplier . "%'";
            $additionalQuery2 .= " and msb.buyer LIKE '%" . $request->supplier . "%'";
        }

        if ($request->id_ws) {
            $additionalQuery .= " and mrk.act_costing_id = " . $request->id_ws . "";
            $additionalQuery1 .= " and form_cut_piping.act_costing_id = " . $request->id_ws . "";
            $additionalQuery2 .= " and form_cut_piece.act_costing_id = " . $request->id_ws . "";
        }

        $data = DB::select("
            select * from (
                select
                    COALESCE(b.qty) qty_in,
                    a.waktu_mulai,
                    a.waktu_selesai,
                    b.id,
                    DATE_FORMAT(b.created_at, '%M') bulan,
                    DATE_FORMAT(b.created_at, '%d-%m-%Y') tgl_input,
                    b.no_form_cut_input,
                    UPPER(meja.name) nama_meja,
                    mrk.act_costing_ws,
                    master_sb_ws.buyer,
                    mrk.style,
                    mrk.color,
                    COALESCE(b.color_act, '-') color_act,
                    mrk.panel,
                    master_sb_ws.qty,
                    cons_ws,
                    cons_marker,
                    a.cons_ampar,
                    a.cons_act,
                    (CASE WHEN a.cons_pipping > 0 THEN a.cons_pipping ELSE mrk.cons_piping END) cons_piping,
                    panjang_marker,
                    unit_panjang_marker,
                    comma_marker,
                    unit_comma_marker,
                    lebar_marker,
                    unit_lebar_marker,
                    a.p_act panjang_actual,
                    a.unit_p_act unit_panjang_actual,
                    a.comma_p_act comma_actual,
                    a.unit_comma_p_act unit_comma_actual,
                    a.l_act lebar_actual,
                    a.unit_l_act unit_lebar_actual,
                    COALESCE(b.id_roll, '-') id_roll,
                    b.id_item,
                    b.detail_item,
                    COALESCE(b.roll_buyer, b.roll) roll,
                    COALESCE(b.lot, '-') lot,
                    COALESCE(b.group_roll, '-') group_roll,
                    (
                        CASE WHEN
                                b.status != 'extension' AND b.status != 'extension complete'
                        THEN
                                (CASE WHEN COALESCE(scanned_item.qty_in, b.qty) > b.qty AND c.id IS NULL THEN 'Sisa Kain' ELSE 'Roll Utuh' END)
                        ELSE
                                'Sambungan'
                        END
                    ) status_roll,
                    COALESCE(c.qty, b.qty) qty_awal,
                    b.qty qty_roll,
                    b.unit unit_roll,
                    COALESCE(b.berat_amparan, '-') berat_amparan,
                    b.est_amparan,
                    b.lembar_gelaran,
                    mrk.total_ratio,
                    (mrk.total_ratio * b.lembar_gelaran) qty_cut,
                    b.average_time,
                    b.sisa_gelaran,
                    b.sambungan,
                    b.sambungan_roll,
                    b.kepala_kain,
                    b.sisa_tidak_bisa,
                    b.reject,
                    b.piping,
                    ROUND(MIN(CASE WHEN b.status != 'extension' AND b.status != 'extension complete' THEN (b.sisa_kain) ELSE (b.qty - b.total_pemakaian_roll) END), 2) sisa_kain,
                    ROUND((CASE WHEN b.status != 'extension complete' THEN ((CASE WHEN b.unit = 'KGM' THEN b.berat_amparan ELSE a.p_act + (a.comma_p_act/100) END) * b.lembar_gelaran) ELSE b.sambungan END) + (b.sisa_gelaran ) + (b.sambungan_roll ) , 2) pemakaian_lembar,
                    ROUND((CASE WHEN b.status != 'extension complete' THEN ((CASE WHEN b.unit = 'KGM' THEN b.berat_amparan ELSE a.p_act + (a.comma_p_act/100) END) * b.lembar_gelaran) ELSE b.sambungan END) + (b.sisa_gelaran) + (b.sambungan_roll) + (b.kepala_kain) + (b.sisa_tidak_bisa) + (b.reject) + (b.piping), 2) total_pemakaian_roll,
                    ROUND(((CASE WHEN b.status != 'extension complete' THEN ((CASE WHEN b.unit = 'KGM' THEN b.berat_amparan ELSE a.p_act + (a.comma_p_act/100) END) * b.lembar_gelaran) ELSE b.sambungan END) + (b.sisa_gelaran) + (b.sambungan_roll) + (b.kepala_kain) + (b.sisa_tidak_bisa) + (b.reject) + (b.piping))+(ROUND(MIN(CASE WHEN b.status != 'extension' AND b.status != 'extension complete' THEN (b.sisa_kain) ELSE (b.qty - b.total_pemakaian_roll) END), 2))-b.qty, 2) short_roll,
                    ROUND((((CASE WHEN b.status != 'extension complete' THEN ((CASE WHEN b.unit = 'KGM' THEN b.berat_amparan ELSE a.p_act + (a.comma_p_act/100) END) * b.lembar_gelaran) ELSE b.sambungan END) + (b.sisa_gelaran) + (b.sambungan_roll) + (b.kepala_kain) + (b.sisa_tidak_bisa) + (b.reject) + (b.piping)+(ROUND(MIN(CASE WHEN b.status != 'extension' AND b.status != 'extension complete' THEN (b.sisa_kain) ELSE (b.qty - b.total_pemakaian_roll) END), 2)))-b.qty)/b.qty*100, 2) short_roll_percentage,
                    b.status,
                    a.operator,
                    a.tipe_form_cut,
                    b.created_at,
                    b.updated_at,
                    (CASE WHEN d.id is null and e.id is null THEN 'latest' ELSE 'not latest' END) roll_status
                from
                    form_cut_input a
                    left join form_cut_input_detail b on a.id = b.form_cut_id
                    left join form_cut_input_detail c ON c.form_cut_id = b.form_cut_id and c.id_roll = b.id_roll and (c.status = 'extension' OR c.status = 'extension complete')
                    LEFT JOIN form_cut_input_detail d on d.id_roll = b.id_roll AND b.id != d.id AND d.created_at > b.created_at and d.created_at >= '2025-01-01 00:00:00' and d.created_at <= '2025-12-31 23:59:59'
                    LEFT JOIN form_cut_piping e on e.id_roll = b.id_roll AND e.created_at > b.created_at and e.created_at >= '2025-01-01 00:00:00' and e.created_at <= '2025-12-31 23:59:59'
                    left join users meja on meja.id = a.no_meja
                    left join (SELECT marker_input.*, SUM(marker_input_detail.ratio) total_ratio FROM marker_input LEFT JOIN marker_input_detail ON marker_input_detail.marker_id = marker_input.id GROUP BY marker_input.id) mrk on a.id_marker = mrk.kode
                    left join (SELECT * FROM master_sb_ws GROUP BY id_act_cost) master_sb_ws on master_sb_ws.id_act_cost = mrk.act_costing_id
                    left join scanned_item on scanned_item.id_roll = b.id_roll
                where
                    (a.cancel = 'N'  OR a.cancel IS NULL)
                    AND (mrk.cancel = 'N'  OR mrk.cancel IS NULL)
                    AND a.status = 'SELESAI PENGERJAAN'
                    and b.status != 'not complete'
                    and b.id_item is not null
                    " . $additionalQuery . "
                group by
                    b.id
                UNION ALL
                select
                    COALESCE(form_cut_piping.qty) qty_in,
                    form_cut_piping.created_at waktu_mulai,
                    form_cut_piping.updated_at waktu_selesai,
                    form_cut_piping.id,
                    DATE_FORMAT(form_cut_piping.created_at, '%M') bulan,
                    DATE_FORMAT(form_cut_piping.created_at, '%d-%m-%Y') tgl_input,
                    'PIPING' no_form_cut_input,
                    '-' nama_meja,
                    form_cut_piping.act_costing_ws,
                    master_sb_ws.buyer,
                    form_cut_piping.style,
                    form_cut_piping.color,
                    form_cut_piping.color color_act,
                    form_cut_piping.panel,
                    master_sb_ws.qty,
                    '0' cons_ws,
                    0 cons_marker,
                    '0' cons_ampar,
                    0 cons_act,
                    form_cut_piping.cons_piping cons_piping,
                    0 panjang_marker,
                    '-' unit_panjang_marker,
                    0 comma_marker,
                    '-' unit_comma_marker,
                    0 lebar_marker,
                    '-' unit_lebar_marker,
                    0 panjang_actual,
                    '-' unit_panjang_actual,
                    0 comma_actual,
                    '-' unit_comma_actual,
                    0 lebar_actual,
                    '-' unit_lebar_actual,
                    form_cut_piping.id_roll,
                    scanned_item.id_item,
                    scanned_item.detail_item,
                    COALESCE(scanned_item.roll_buyer, scanned_item.roll) roll,
                    scanned_item.lot,
                    '-' group_roll,
                    'Piping' status_roll,
                    COALESCE(scanned_item.qty_in, form_cut_piping.qty) qty_awal,
                    form_cut_piping.qty qty_roll,
                    form_cut_piping.unit unit_roll,
                    0 berat_amparan,
                    0 est_amparan,
                    0 lembar_gelaran,
                    0 total_ratio,
                    0 qty_cut,
                    '00:00' average_time,
                    '0' sisa_gelaran,
                    0 sambungan,
                    0 sambungan_roll,
                    0 kepala_kain,
                    0 sisa_tidak_bisa,
                    0 reject,
                    form_cut_piping.piping piping,
                    form_cut_piping.qty_sisa sisa_kain,
                    form_cut_piping.piping pemakaian_lembar,
                    form_cut_piping.piping total_pemakaian_roll,
                    ROUND((form_cut_piping.piping + form_cut_piping.qty_sisa) - form_cut_piping.qty, 2) short_roll,
                    ROUND(((form_cut_piping.piping + form_cut_piping.qty_sisa) - form_cut_piping.qty)/coalesce(scanned_item.qty_in, form_cut_piping.qty) * 100, 2) short_roll_percentage,
                    null `status`,
                    form_cut_piping.operator,
                    'PIPING' tipe_form_cut,
                    form_cut_piping.created_at,
                    form_cut_piping.updated_at,
                    (CASE WHEN c.id is null THEN 'latest' ELSE 'not latest' END) roll_status
                from
                    form_cut_piping
                    LEFT JOIN form_cut_input_detail b on b.id_roll = form_cut_piping.id_roll AND b.created_at > form_cut_piping.created_at and b.created_at >= '2025-01-01 00:00:00' and b.created_at <= '2025-12-31 23:59:59'
                    LEFT JOIN form_cut_piping c on c.id_roll = form_cut_piping.id_roll AND c.id != form_cut_piping.id and c.created_at > form_cut_piping.created_at and c.created_at >= '2025-01-01 00:00:00' and c.created_at <= '2025-12-31 23:59:59'
                    left join (SELECT * FROM master_sb_ws GROUP BY id_act_cost) master_sb_ws on master_sb_ws.id_act_cost = form_cut_piping.act_costing_id
                    left join scanned_item on scanned_item.id_roll = form_cut_piping.id_roll
                where
                    scanned_item.id_item is not null
                    " . $additionalQuery1 . "
                group by
                    form_cut_piping.id
                UNION ALL
                SELECT
                    form_cut_piece_detail.qty qty_in,
                    form_cut_piece.created_at waktu_mulai,
                    form_cut_piece.updated_at waktu_selesai,
                    form_cut_piece.id,
                    DATE_FORMAT( form_cut_piece.created_at, '%M' ) bulan,
                    DATE_FORMAT( form_cut_piece.created_at, '%d-%m-%Y' ) tgl_input,
                    form_cut_piece.no_form no_form_cut_input,
                    '-' nama_meja,
                    form_cut_piece.act_costing_ws,
                    master_sb_ws.buyer,
                    form_cut_piece.style,
                    form_cut_piece.color,
                    form_cut_piece.color color_act,
                    form_cut_piece.panel,
                    master_sb_ws.qty,
                    form_cut_piece.cons_ws cons_ws,
                    form_cut_piece.cons_ws cons_marker,
                    '0' cons_ampar,
                    0 cons_act,
                    0 cons_piping,
                    0 panjang_marker,
                    '-' unit_panjang_marker,
                    0 comma_marker,
                    '-' unit_comma_marker,
                    0 lebar_marker,
                    '-' unit_lebar_marker,
                    0 panjang_actual,
                    '-' unit_panjang_actual,
                    0 comma_actual,
                    '-' unit_comma_actual,
                    0 lebar_actual,
                    '-' unit_lebar_actual,
                    form_cut_piece_detail.id_roll,
                    scanned_item.id_item,
                    scanned_item.detail_item,
                    COALESCE ( scanned_item.roll_buyer, scanned_item.roll ) roll,
                    scanned_item.lot,
                    '-' group_roll,
                    ( CASE WHEN form_cut_piece_detail.qty >= COALESCE ( scanned_item.qty_in, 0 ) THEN 'Roll Utuh' ELSE 'Sisa Kain' END ) status_roll,
                    COALESCE ( scanned_item.qty_in, form_cut_piece_detail.qty ) qty_awal,
                    form_cut_piece_detail.qty qty_roll,
                    form_cut_piece_detail.qty_unit unit_roll,
                    0 berat_amparan,
                    0 est_amparan,
                    0 lembar_gelaran,
                    0 total_ratio,
                    0 qty_cut,
                    '00:00' average_time,
                    '0' sisa_gelaran,
                    0 sambungan,
                    0 sambungan_roll,
                    0 kepala_kain,
                    0 sisa_tidak_bisa,
                    0 reject,
                    0 piping,
                    form_cut_piece_detail.qty_sisa sisa_kain,
                    form_cut_piece_detail.qty_pemakaian pemakaian_lembar,
                    form_cut_piece_detail.qty_pemakaian total_pemakaian_roll,
                    ROUND(
                    form_cut_piece_detail.qty - ( form_cut_piece_detail.qty_pemakaian + form_cut_piece_detail.qty_sisa )) short_roll,
                    ROUND((form_cut_piece_detail.qty - ( form_cut_piece_detail.qty_pemakaian + form_cut_piece_detail.qty_sisa ))/ COALESCE ( scanned_item.qty_in, form_cut_piece_detail.qty ) * 100, 2 ) short_roll_percentage,
                    form_cut_piece_detail.STATUS `status`,
                    form_cut_piece.employee_name,
                    'PCS' tipe_form_cut,
                    form_cut_piece.created_at,
                    form_cut_piece.updated_at,
                    (CASE WHEN b.id is null THEN 'latest' ELSE 'not latest' END) roll_status
                FROM
                    form_cut_piece
                    LEFT JOIN form_cut_piece_detail ON form_cut_piece_detail.form_id = form_cut_piece.id
                    LEFT JOIN form_cut_piece_detail b on b.id_roll = form_cut_piece_detail.id_roll AND b.created_at > form_cut_piece_detail.created_at
                    LEFT JOIN ( SELECT * FROM master_sb_ws GROUP BY id_act_cost ) master_sb_ws ON master_sb_ws.id_act_cost = form_cut_piece.act_costing_id
                    LEFT JOIN scanned_item ON scanned_item.id_roll = form_cut_piece_detail.id_roll
                WHERE
                    scanned_item.id_item IS NOT NULL
                    AND form_cut_piece_detail.STATUS = 'complete'
                    " . $additionalQuery2 . "
                GROUP BY
                    form_cut_piece_detail.id
            ) roll_consumption
            order by
                no_form_cut_input asc,
                id_roll asc,
                created_at asc
        ");

        $sheet->writeAreas();

        $rows = [];

        $no = 1;

        $latestKepalaKain = 0;
        $latestSisaTidakBisa = 0;
        $latestReject = 0;
        $latestPiping = 0;
        $latestSambungan = 0;
        $latestSambunganRoll = 0;
        $latestPemakaianLembar = 0;
        $latestTotalPemakaian = 0;
        $latestShortRoll = 0;

        $latestStatus = '';
        $latestQty = 0;
        $latestUnit = '';

        $currentForm = '';

        foreach ($data as $item) {

            // =======================
            // RESET PER FORM
            // =======================
            if ($item->no_form_cut_input !== 'PIPING' &&
                $currentForm !== $item->no_form_cut_input) {

                $latestKepalaKain = 0;
                $latestSisaTidakBisa = 0;
                $latestReject = 0;
                $latestPiping = 0;
                $latestSambungan = 0;
                $latestSambunganRoll = 0;
                $latestPemakaianLembar = 0;
                $latestTotalPemakaian = 0;
                $latestShortRoll = 0;

                $latestStatus = '';
                $latestQty = 0;
                $latestUnit = '';

                $currentForm = $item->no_form_cut_input;
            }

            // =======================
            // CONS LEMBAR
            // =======================
            if ($item->unit_roll === 'KGM') {
                $consLembar = (float) $item->berat_amparan;
            } else {
                $consLembar = (float) $item->panjang_actual + ((float) $item->comma_actual / 100);
            }

            // =======================
            // ROLLING VALUES
            // =======================
            $currentSambunganRoll = $latestStatus !== 'extension complete'
                ? (float) $item->sambungan_roll
                : round($item->sambungan_roll + $latestSambunganRoll, 2);

            $currentKepalaKain = $latestStatus !== 'extension complete'
                ? (float) $item->kepala_kain
                : round($item->kepala_kain + $latestKepalaKain, 2);

            $currentSisaTidakBisa = $latestStatus !== 'extension complete'
                ? (float) $item->sisa_tidak_bisa
                : round($item->sisa_tidak_bisa + $latestSisaTidakBisa, 2);

            $currentReject = $latestStatus !== 'extension complete'
                ? (float) $item->reject
                : round($item->reject + $latestReject, 2);

            $currentPiping = $latestStatus !== 'extension complete'
                ? (float) $item->piping
                : round($item->piping + $latestPiping, 2);

            // =======================
            // PEMAKAIAN
            // =======================
            $currentPemakaianLembar = round(
                (float) $item->pemakaian_lembar +
                ($latestStatus !== 'extension complete' ? 0 : $latestPemakaianLembar),
                2
            );

            $currentTotalPemakaian = round(
                (float) $item->total_pemakaian_roll +
                ($latestStatus !== 'extension complete' ? 0 : $latestTotalPemakaian),
                2
            );

            // =======================
            // SHORT ROLL
            // =======================
            $baseQty = $latestStatus !== 'extension complete'
                ? (float) $item->qty_roll
                : (float) $latestQty;

            $currentShortRoll = round(
                ($currentTotalPemakaian + (float) $item->sisa_kain) - $baseQty,
                2
            );

            $currentShortRollPercentage = $baseQty > 0
                ? round(($currentShortRoll / $baseQty) * 100, 2)
                : 0;

            // =======================
            // BUILD EXCEL ROW
            // =======================
            $row = [
                $item->status !== 'extension complete' ? $no++ : '',
                (string) $item->bulan,
                (string) $item->tgl_input,
                (string) $item->no_form_cut_input,
                (string) $item->nama_meja,
                (string) $item->act_costing_ws,
                (string) $item->buyer,
                (string) $item->style,
                (string) $item->color,
                (string) $item->color_act,
                (string) $item->panel,
                (int) $item->qty,
                (float) $item->cons_ws,
                (float) $item->cons_marker,
                (float) $item->cons_ampar,
                (float) $item->cons_act,
                (float) $item->cons_piping,
                (float) $item->panjang_marker,
                (string) $item->unit_panjang_marker,
                (float) $item->comma_marker,
                (string) $item->unit_comma_marker,
                (float) $item->lebar_marker,
                (string) $item->unit_lebar_marker,
                (float) $item->panjang_actual,
                (string) $item->unit_panjang_actual,
                (float) $item->comma_actual,
                (string) $item->unit_comma_actual,
                (float) $item->lebar_actual,
                (string) $item->unit_lebar_actual,
                (string) $item->id_roll,
                (string) $item->id_item,
                (string) $item->detail_item,
                $item->status === 'extension complete' ? 'SAMBUNGAN' : (string) $item->roll,
                (string) $item->lot,
                (string) $item->group_roll,
                (string) $item->status_roll,
            ];

            // =======================
            // CONDITIONAL COLUMNS
            // =======================
            if ($item->tipe_form_cut !== 'PIPING' && $item->tipe_form_cut !== 'PCS') {

                if ($item->status !== 'extension complete') {

                    $row = array_merge($row, [
                        $latestStatus !== 'extension complete' ? $item->qty_roll : $latestQty,
                        $item->unit_roll,
                        $item->berat_amparan,
                        $item->est_amparan,
                        $item->lembar_gelaran,
                        $item->total_ratio,
                        $item->qty_cut,
                        $item->average_time,
                        $item->sisa_gelaran,
                        $latestStatus !== 'extension complete' ? 0 : $latestSambungan,
                        $currentSambunganRoll,
                        $currentKepalaKain,
                        $currentSisaTidakBisa,
                        $currentReject,
                        $latestStatus !== 'extension complete'
                            ? $item->piping
                            : round($item->piping + $latestPiping, 2),
                        $item->sisa_kain,
                        $currentPemakaianLembar,
                        $currentTotalPemakaian,
                        $currentShortRoll,
                        $currentShortRollPercentage . ' %',
                    ]);

                } else {

                   $row = array_merge($row, [
                        '-', '-', '-', '-',
                        $item->lembar_gelaran,
                        $item->total_ratio,
                        $item->qty_cut,
                        '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-',
                    ]);
                }

                // UPDATE STATE
                $latestKepalaKain = $currentKepalaKain;
                $latestSisaTidakBisa = $currentSisaTidakBisa;
                $latestReject = $currentReject;
                $latestPiping = $currentPiping;
                $latestSambungan = $item->sambungan;
                $latestSambunganRoll = $currentSambunganRoll;
                $latestPemakaianLembar = $currentPemakaianLembar;
                $latestTotalPemakaian = $currentTotalPemakaian;
                $latestShortRoll = $currentShortRoll;

                $latestStatus = $item->status;
                $latestQty = $item->qty_roll;
                $latestUnit = $item->unit_roll;

            } else {

                $row = array_merge($row, [
                    $item->qty_roll,
                    $item->unit_roll,
                    $item->berat_amparan,
                    $item->est_amparan,
                    $item->lembar_gelaran,
                    $item->total_ratio,
                    $item->qty_cut,
                    $item->average_time,
                    $item->sisa_gelaran,
                    $item->sambungan,
                    $item->sambungan_roll,
                    $item->kepala_kain,
                    $item->sisa_tidak_bisa,
                    $item->reject,
                    $item->piping,
                    $item->sisa_kain,
                    $item->pemakaian_lembar,
                    $item->total_pemakaian_roll,
                    $item->short_roll,
                    $item->short_roll_percentage . ' %',
                ]);
            }

            $row[] = (string) $item->operator;

            $sheet->writeRow($row)->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        }

        $filename = $request->dateFrom . ' / ' . $request->dateTo . ' Pemakaian Kain.xlsx';

        return $excel->download($filename);
    }

    public function sisaKainRoll(Request $request)
    {
        $newItem = DB::connection("mysql_sb")->select("
            SELECT
                mastersupplier.Supplier buyer,
                whs_bppb_h.no_ws_aktual no_ws,
                act_costing.styleno style,
                masteritem.color,
                whs_bppb_det.id_roll,
                masteritem.itemdesc detail_item,
                masteritem.color detail_item_color,
                masteritem.size detail_item_size,
                whs_bppb_det.id_item,
                whs_bppb_det.no_lot lot,
                COALESCE(whs_lokasi_inmaterial.no_roll_buyer, whs_bppb_det.no_roll) no_roll,
                whs_bppb_det.satuan unit,
                whs_bppb_det.qty_stok,
                SUM(whs_bppb_det.qty_out) qty
            FROM
                whs_bppb_det
                LEFT JOIN (SELECT jo_det.* FROM jo_det WHERE cancel != 'Y' GROUP BY id_jo) jodet ON jodet.id_jo = whs_bppb_det.id_jo
                LEFT JOIN so ON so.id = jodet.id_so
                LEFT JOIN act_costing ON act_costing.id = so.id_cost
                LEFT JOIN mastersupplier ON mastersupplier.Id_Supplier = act_costing.id_buyer
                LEFT JOIN masteritem ON masteritem.id_item = whs_bppb_det.id_item
                LEFT JOIN whs_bppb_h ON whs_bppb_h.no_bppb = whs_bppb_det.no_bppb
                LEFT JOIN whs_lokasi_inmaterial ON whs_lokasi_inmaterial.no_barcode = whs_bppb_det.id_roll
            WHERE
                whs_bppb_det.id_roll = '" . $request->id . "'
                AND whs_bppb_h.tujuan = 'Production - Cutting'
                AND cast(whs_bppb_det.qty_out AS DECIMAL ( 11, 3 )) > 0.000
            GROUP BY
                whs_bppb_det.id_roll
            LIMIT 1
        ");

        return view("cutting.roll.sisa-kain-roll", ['page' => 'dashboard-cutting', "subPageGroup" => "laporan-cutting", "subPage" => "sisa-kain-roll"]);
    }

    public function getScannedItem($id)
    {
        $newItem = DB::connection("mysql_sb")->select("
            SELECT
                buyer,
                no_ws,
                style,
                color,
                id_roll,
                detail_item,
                detail_item_color,
                detail_item_size,
                id_item,
                lot,
                roll,
                roll_buyer,
                qty_stok,
                SUM(qty)-COALESCE(qty_ri, 0) as qty,
                unit,
                rule_bom,
                so_det_list,
                size_list
            FROM (
                SELECT
                    mastersupplier.Supplier buyer,
                    whs_bppb_h.no_ws_aktual no_ws,
                    act_costing.styleno style,
                    masteritem.color,
                    whs_bppb_det.id_roll,
                    masteritem.itemdesc detail_item,
                    masteritem.color detail_item_color,
                    masteritem.size detail_item_size,
                    whs_bppb_det.id_item,
                    whs_bppb_det.no_lot lot,
                    whs_bppb_det.no_roll roll,
                    whs_lokasi_inmaterial.no_roll_buyer roll_buyer,
                    whs_bppb_det.qty_stok,
                    whs_bppb_det.qty_out qty,
                    whs_bppb_det.satuan unit,
                    bji.rule_bom,
                    GROUP_CONCAT(DISTINCT so_det.id ORDER BY so_det.id ASC SEPARATOR ', ') as so_det_list,
                    GROUP_CONCAT(DISTINCT so_det.size ORDER BY so_det.id ASC SEPARATOR ', ') as size_list
                FROM
                    whs_bppb_det
                    LEFT JOIN whs_bppb_h ON whs_bppb_h.no_bppb = whs_bppb_det.no_bppb
                    LEFT JOIN (SELECT no_barcode, id_item, no_roll_buyer FROM whs_lokasi_inmaterial where no_barcode = '" . $id . "' GROUP BY no_barcode, no_roll_buyer) whs_lokasi_inmaterial ON whs_lokasi_inmaterial.no_barcode = whs_bppb_det.id_roll
                    LEFT JOIN masteritem ON masteritem.id_item = whs_lokasi_inmaterial.id_item
                    LEFT JOIN bom_jo_item bji ON bji.id_item = masteritem.id_gen
                    LEFT JOIN so_det ON so_det.id = bji.id_so_det
                    LEFT JOIN so ON so.id = so_det.id_so
                    LEFT JOIN act_costing ON act_costing.id = so.id_cost
                    LEFT JOIN mastersupplier ON mastersupplier.Id_Supplier = act_costing.id_buyer
                WHERE
                    whs_bppb_det.id_roll = '" . $id . "'
                    AND whs_bppb_h.tujuan = 'Production - Cutting'
                    AND cast(whs_bppb_det.qty_out AS DECIMAL ( 11, 3 )) > 0.000
                GROUP BY
                    whs_bppb_det.id
            ) item
            LEFT JOIN (select no_barcode, sum(qty_aktual) qty_ri from whs_lokasi_inmaterial a INNER JOIN whs_inmaterial_fabric b on b.no_dok = a.no_dok where a.no_barcode = '" . $id . "' and supplier = 'Production - Cutting' and a.status = 'Y' GROUP BY no_barcode) as ri on ri.no_barcode = item.id_roll
            GROUP BY
                    id_roll
            LIMIT 1
        ");

        if ($newItem) {
            $scannedItem = ScannedItem::selectRaw("
                COALESCE(marker_input.buyer, form_cut_piece.buyer) buyer,
                COALESCE(marker_input.act_costing_ws, form_cut_piece.act_costing_ws) no_ws,
                COALESCE(marker_input.style, form_cut_piece.style) style,
                COALESCE(marker_input.color, form_cut_piece.color) color,
                scanned_item.id_roll,
                scanned_item.id_item,
                scanned_item.detail_item,
                scanned_item.detail_item_color,
                scanned_item.detail_item_size,
                scanned_item.lot,
                COALESCE(scanned_item.roll_buyer, scanned_item.roll) no_roll,
                scanned_item.qty,
                scanned_item.qty_in,
                scanned_item.qty_stok,
                scanned_item.unit,
                COALESCE(scanned_item.updated_at, scanned_item.created_at) updated_at
            ")->leftJoin('form_cut_input_detail', 'form_cut_input_detail.id_roll', '=', 'scanned_item.id_roll')->leftJoin('form_cut_input', 'form_cut_input.id', '=', 'form_cut_input_detail.form_cut_id')->leftJoin('marker_input', 'marker_input.kode', '=', 'form_cut_input.id_marker')->leftJoin('form_cut_piece_detail', 'form_cut_piece_detail.id_roll', '=', 'scanned_item.id_roll')->leftJoin('form_cut_piece', 'form_cut_piece.id', '=', 'form_cut_piece_detail.form_id')->where('scanned_item.id_roll', $id)->where('scanned_item.id_item', $newItem[0]->id_item)->first();

            if ($scannedItem) {
                $scannedItem->qty_stok = $newItem[0]->qty_stok;
                $scannedItem->qty_in = $newItem[0]->qty;
                $scannedItem->qty = floatval($newItem[0]->qty - $scannedItem->qty_in + $scannedItem->qty);
                $scannedItem->save();

                return json_encode($scannedItem);
            }

            return json_encode($newItem ? $newItem[0] : null);
        }

        $item = DB::connection("mysql_sb")->select("
            SELECT
                ms.Supplier buyer,
                ac.kpno no_ws,
                ac.styleno style,
                mi.color,
                br.id id_roll,
                mi.itemdesc detail_item,
                mi.color detail_item_color,
                mi.size detail_item_size,
                mi.id_item,
                goods_code,
                supplier,
                bpbno_int,
                pono,
                invno,
                roll_no no_roll,
                roll_qty qty,
                lot_no lot,
                bpb.unit,
                kode_rak
            FROM
                bpb_roll br
                INNER JOIN bpb_roll_h brh ON br.id_h = brh.id
                INNER JOIN masteritem mi ON brh.id_item = mi.id_item
                INNER JOIN bpb ON brh.bpbno = bpb.bpbno
                AND brh.id_jo = bpb.id_jo
                AND brh.id_item = bpb.id_item
                INNER JOIN mastersupplier ms ON bpb.id_supplier = ms.Id_Supplier
                INNER JOIN jo_det jd ON brh.id_jo = jd.id_jo
                INNER JOIN so ON jd.id_so = so.id
                INNER JOIN act_costing ac ON so.id_cost = ac.id
                INNER JOIN master_rak mr ON br.id_rak_loc = mr.id
            WHERE
                br.id = '" . $id . "'
                AND cast(roll_qty AS DECIMAL ( 11, 3 )) > 0.000
                LIMIT 1
        ");

        if ($item) {
            $scannedItem = ScannedItem::selectRaw("
                marker_input.buyer,
                marker_input.act_costing_ws no_ws,
                marker_input.style style,
                marker_input.color color,
                scanned_item.id_roll,
                scanned_item.id_item,
                scanned_item.detail_item,
                scanned_item.detail_item_color,
                scanned_item.detail_item_size,
                scanned_item.lot,
                COALESCE(scanned_item.roll, scanned_item.roll_buyer) no_roll,
                scanned_item.qty,
                scanned_item.qty_in,
                scanned_item.qty_stok,
                scanned_item.unit,
                COALESCE(scanned_item.updated_at, scanned_item.created_at) updated_at
            ")->leftJoin('form_cut_input_detail', 'form_cut_input_detail.id_roll', '=', 'scanned_item.id_roll')->leftJoin('form_cut_input', 'form_cut_input.id', '=', 'form_cut_input_detail.form_cut_id')->leftJoin('marker_input', 'marker_input.kode', '=', 'form_cut_input.id_marker')->where('scanned_item.id_roll', $id)->where('scanned_item.id_item', $item[0]->id_item)->first();

            if ($scannedItem && $scannedItem->buyer) {
                return json_encode($scannedItem);
            }

            return json_encode($item ? $item[0] : null);
        }

        return  null;
    }

    public function getSisaKainForm(Request $request)
    {
        $forms = DB::select("
            SELECT
                form_cut_input.id id_form,
                no_form_cut_input,
                form_cut_input.no_cut,
                id_roll,
                MAX( qty ) qty,
                unit,
                SUM( total_pemakaian_roll ) total_pemakaian_roll,
                SUM( short_roll ) short_roll,
                MIN( CASE WHEN form_cut_input_detail.status = 'extension' OR form_cut_input_detail.status = 'extension complete' THEN form_cut_input_detail.qty - form_cut_input_detail.total_pemakaian_roll ELSE form_cut_input_detail.sisa_kain END ) sisa_kain,
                form_cut_input.status status_form,
                form_cut_input_detail.status,
                COALESCE ( form_cut_input_detail.created_at, form_cut_input_detail.updated_at ) updated_at,
                'REGULAR' as tipe
            FROM
                `form_cut_input_detail`
                LEFT JOIN `form_cut_input` ON `form_cut_input`.`id` = `form_cut_input_detail`.`form_cut_id`
            WHERE
                ( form_cut_input.status != 'SELESAI PENGERJAAN' OR ( form_cut_input.status = 'SELESAI PENGERJAAN' AND form_cut_input.status != 'not complete' AND form_cut_input.status != 'extension' ) )
                AND `id_roll` = '" . $request->id . "'
                AND ( id_roll IS NOT NULL AND id_roll != '' )
                AND form_cut_input_detail.updated_at >= DATE ( NOW()- INTERVAL 2 YEAR )
            GROUP BY
                `form_cut_input`.`id`

            UNION

            SELECT
                form_cut_piece.id id_form,
                form_cut_piece.no_form no_form_cut_input,
                form_cut_piece.no_cut,
                id_roll,
                MAX( qty ) qty,
                qty_unit as unit,
                SUM( qty_pemakaian ) total_pemakaian_roll,
                SUM( qty - (qty_pemakaian + qty_sisa) ) short_roll,
                qty_sisa sisa_kain,
                form_cut_piece.status status_form,
                form_cut_piece_detail.status,
                COALESCE ( form_cut_piece_detail.created_at, form_cut_piece_detail.updated_at ) updated_at,
                'PIECE' as tipe
            FROM
                `form_cut_piece_detail`
                LEFT JOIN `form_cut_piece` ON `form_cut_piece`.`id` = `form_cut_piece_detail`.`form_id`
            WHERE
                ( form_cut_piece.status = 'complete' OR form_cut_piece_detail.status = 'complete' )
                AND `id_roll` = '" . $request->id . "'
                AND ( id_roll IS NOT NULL AND id_roll != '' )
                AND form_cut_piece_detail.updated_at >= DATE ( NOW()- INTERVAL 2 YEAR )
            GROUP BY
                `form_cut_piece`.`id`

            UNION

            SELECT
                form_cut_piping.id id_form,
                'PIPING' no_form_cut_input,
                '-',
                id_roll,
                MAX( qty ) qty,
                unit,
                SUM( piping ) total_pemakaian_roll,
                SUM( qty - (piping + qty_sisa) ) short_roll,
                qty_sisa sisa_kain,
                '-' status_form,
                '-' status,
                COALESCE ( form_cut_piping.created_at, form_cut_piping.updated_at ) updated_at,
                'PIPING' as tipe
            FROM
                `form_cut_piping`
            WHERE
                `id_roll` = '" . $request->id . "'
                AND ( id_roll IS NOT NULL AND id_roll != '' )
                AND form_cut_piping.updated_at >= DATE ( NOW()- INTERVAL 2 YEAR )
            GROUP BY
                `form_cut_piping`.`id`
        ");

        return DataTables::of($forms)->toJson();
    }

    public function printSisaKain($id)
    {
        $sbItem = DB::connection("mysql_sb")->select("
            SELECT
                masteritem.itemdesc detail_item,
                masteritem.goods_code,
                masteritem.id_item,
                CONCAT(whs_bppb_h.no_bppb, ' | ', whs_bppb_h.tgl_bppb) bppb,
                whs_bppb_h.no_req,
                whs_bppb_h.no_ws_aktual no_ws,
                act_costing.styleno style,
                masteritem.color,
                whs_bppb_det.id_roll,
                whs_bppb_det.id_item,
                whs_bppb_det.no_lot lot,
                whs_bppb_det.no_roll,
                COALESCE(whs_lokasi_inmaterial.no_roll_buyer, '-') no_roll_buyer,
                whs_lokasi_inmaterial.kode_lok lokasi,
                whs_bppb_det.satuan unit,
                whs_bppb_det.qty_stok,
                SUM(whs_bppb_det.qty_out)-COALESCE(qty_ri, 0) qty
            FROM
                whs_bppb_det
                LEFT JOIN (SELECT jo_det.* FROM jo_det WHERE cancel != 'Y' GROUP BY id_jo) jodet ON jodet.id_jo = whs_bppb_det.id_jo
                LEFT JOIN so ON so.id = jodet.id_so
                LEFT JOIN act_costing ON act_costing.id = so.id_cost
                LEFT JOIN mastersupplier ON mastersupplier.Id_Supplier = act_costing.id_buyer
                LEFT JOIN masteritem ON masteritem.id_item = whs_bppb_det.id_item
                LEFT JOIN whs_bppb_h ON whs_bppb_h.no_bppb = whs_bppb_det.no_bppb
                LEFT JOIN (SELECT * from whs_lokasi_inmaterial WHERE no_barcode = '" . $id . "' ORDER BY id DESC LIMIT 1) as whs_lokasi_inmaterial ON whs_lokasi_inmaterial.no_barcode = whs_bppb_det.id_roll
                LEFT JOIN (select no_barcode, sum(qty_aktual) qty_ri from whs_lokasi_inmaterial a INNER JOIN whs_inmaterial_fabric b on b.no_dok = a.no_dok where a.no_barcode = '" . $id . "' and supplier = 'Production - Cutting' and a.status = 'Y' GROUP BY no_barcode) as ri on ri.no_barcode = whs_bppb_det.id_roll
            WHERE
                whs_bppb_det.id_roll = '" . $id . "'
                AND whs_bppb_h.tujuan = 'Production - Cutting'
                AND cast(whs_bppb_det.qty_out AS DECIMAL ( 11, 3 )) > 0.000
                AND whs_bppb_det.no_bppb LIKE '%GK/OUT%'
            GROUP BY
                whs_bppb_det.id_roll
            LIMIT 1
        ");

        if (!$sbItem) {
            $sbItem = DB::connection("mysql_sb")->select("
                SELECT
                    mi.itemdesc detail_item,
                    mi.goods_code,
                    mi.id_item,
                    CONCAT(bpb.bpbno_int, ' | ', bpb.bpbdate) bppb,
                    '-' no_req,
                    ac.kpno no_ws,
                    ac.styleno style,
                    mi.color,
                    br.id id_roll,
                    brh.id_item,
                    br.lot_no lot,
                    br.roll_no no_roll,
                    '-' no_roll_buyer,
                    '-' lokasi,
                    br.unit,
                    br.roll_qty qty
                FROM
                    bpb_roll br
                    INNER JOIN bpb_roll_h brh ON br.id_h = brh.id
                    INNER JOIN masteritem mi ON brh.id_item = mi.id_item
                    INNER JOIN bpb ON brh.bpbno = bpb.bpbno
                    AND brh.id_jo = bpb.id_jo
                    AND brh.id_item = bpb.id_item
                    INNER JOIN mastersupplier ms ON bpb.id_supplier = ms.Id_Supplier
                    INNER JOIN jo_det jd ON brh.id_jo = jd.id_jo
                    INNER JOIN so ON jd.id_so = so.id
                    INNER JOIN act_costing ac ON so.id_cost = ac.id
                    INNER JOIN master_rak mr ON br.id_rak_loc = mr.id
                WHERE
                    br.id = '" . $id . "'
                    AND cast(roll_qty AS DECIMAL ( 11, 3 )) > 0.000
                    LIMIT 1
            ");
        }

        $ndsItem = ScannedItem::selectRaw("
                GROUP_CONCAT(DISTINCT form_cut_input_detail.group_roll) group_roll,
                COALESCE(scanned_item.qty, MIN(form_cut_input_detail.sisa_kain)) sisa_kain,
                scanned_item.unit,
                GROUP_CONCAT(DISTINCT CONCAT( form_cut_input.no_form, ' | ', COALESCE(form_cut_input.operator, '-')) SEPARATOR '^') AS no_form
            ")->leftJoin("form_cut_input_detail", "form_cut_input_detail.id_roll", "=", "scanned_item.id_roll")->leftJoin("form_cut_input", "form_cut_input.id", "=", "form_cut_input_detail.form_cut_id")->where("scanned_item.id_roll", $id)->orderBy("scanned_item.id", "desc")->groupBy("scanned_item.id_roll")->first();

        PDF::setOption(['dpi' => 150, 'defaultFont' => 'Helvetica-Bold']);
        $pdf = PDF::loadView('cutting.roll.pdf.sisa-kain-roll', ["sbItem" => ($sbItem ? $sbItem[0] : null), "ndsItem" => $ndsItem])->setPaper('a7', 'landscape');

        $fileName = 'Sisa_Kain_' . $id . '.pdf';

        return $pdf->download(str_replace("/", "_", $fileName));
    }

    public function massPrintSisaKain(Request $request)
    {
        $idsStr = addQuotesAround($request->ids);

        $sbItems = DB::connection("mysql_sb")->select("
            SELECT
                masteritem.itemdesc detail_item,
                masteritem.goods_code,
                masteritem.id_item,
                CONCAT(whs_bppb_h.no_bppb, ' | ', whs_bppb_h.tgl_bppb) bppb,
                whs_bppb_h.no_req,
                whs_bppb_h.no_ws_aktual no_ws,
                act_costing.styleno style,
                masteritem.color,
                whs_bppb_det.id_roll,
                whs_bppb_det.id_item,
                whs_bppb_det.no_lot lot,
                whs_bppb_det.no_roll,
                COALESCE(whs_lokasi_inmaterial.no_roll_buyer, '-') no_roll_buyer,
                whs_lokasi_inmaterial.kode_lok lokasi,
                whs_bppb_det.satuan unit,
                whs_bppb_det.qty_stok,
                SUM(whs_bppb_det.qty_out)-COALESCE(qty_ri, 0) qty
            FROM
                whs_bppb_det
                LEFT JOIN (SELECT jo_det.* FROM jo_det WHERE cancel != 'Y' GROUP BY id_jo) jodet ON jodet.id_jo = whs_bppb_det.id_jo
                LEFT JOIN so ON so.id = jodet.id_so
                LEFT JOIN act_costing ON act_costing.id = so.id_cost
                LEFT JOIN mastersupplier ON mastersupplier.Id_Supplier = act_costing.id_buyer
                LEFT JOIN masteritem ON masteritem.id_item = whs_bppb_det.id_item
                LEFT JOIN whs_bppb_h ON whs_bppb_h.no_bppb = whs_bppb_det.no_bppb
                LEFT JOIN (SELECT * FROM whs_lokasi_inmaterial WHERE no_barcode in (" . $idsStr . ") GROUP BY no_barcode) as whs_lokasi_inmaterial ON whs_lokasi_inmaterial.no_barcode = whs_bppb_det.id_roll
                LEFT JOIN (select no_barcode, sum(qty_aktual) qty_ri from whs_lokasi_inmaterial a INNER JOIN whs_inmaterial_fabric b on b.no_dok = a.no_dok where no_barcode in (" . $idsStr . ") and supplier = 'Production - Cutting' and a.status = 'Y' GROUP BY no_barcode) as ri on ri.no_barcode = whs_bppb_det.id_roll
            WHERE
                whs_bppb_det.id_roll in (" . $idsStr . ")
                AND whs_bppb_h.tujuan = 'Production - Cutting'
                AND cast(whs_bppb_det.qty_out AS DECIMAL ( 11, 3 )) > 0.000
                AND whs_bppb_det.no_bppb LIKE '%GK/OUT%'
            GROUP BY
                whs_bppb_det.id_roll
        ");

        if (!$sbItems) {
            $sbItems = DB::connection("mysql_sb")->select("
                SELECT
                    mi.itemdesc detail_item,
                    mi.goods_code,
                    mi.id_item,
                    CONCAT(bpb.bpbno_int, ' | ', bpb.bpbdate) bppb,
                    '-' no_req,
                    ac.kpno no_ws,
                    ac.styleno style,
                    mi.color,
                    br.id id_roll,
                    brh.id_item,
                    br.lot_no lot,
                    br.roll_no no_roll,
                    '-' no_roll_buyer,
                    '-' lokasi,
                    br.unit,
                    br.roll_qty qty
                FROM
                    bpb_roll br
                    INNER JOIN bpb_roll_h brh ON br.id_h = brh.id
                    INNER JOIN masteritem mi ON brh.id_item = mi.id_item
                    INNER JOIN bpb ON brh.bpbno = bpb.bpbno
                    AND brh.id_jo = bpb.id_jo
                    AND brh.id_item = bpb.id_item
                    INNER JOIN mastersupplier ms ON bpb.id_supplier = ms.Id_Supplier
                    INNER JOIN jo_det jd ON brh.id_jo = jd.id_jo
                    INNER JOIN so ON jd.id_so = so.id
                    INNER JOIN act_costing ac ON so.id_cost = ac.id
                    INNER JOIN master_rak mr ON br.id_rak_loc = mr.id
                WHERE
                    br.id in (" . $idsStr . ")
                    AND cast(roll_qty AS DECIMAL ( 11, 3 )) > 0.000
            ");
        }

        $ndsItems = ScannedItem::selectRaw("
                scanned_item.id_roll,
                GROUP_CONCAT(DISTINCT form_cut_input_detail.group_roll) group_roll,
                COALESCE(scanned_item.qty, MIN(form_cut_input_detail.sisa_kain)) sisa_kain,
                scanned_item.unit,
                GROUP_CONCAT(DISTINCT CONCAT( form_cut_input.no_form, ' | ', COALESCE(form_cut_input.operator, '-')) SEPARATOR '^') AS no_form
            ")->leftJoin("form_cut_input_detail", "form_cut_input_detail.id_roll", "=", "scanned_item.id_roll")->leftJoin("form_cut_input", "form_cut_input.id", "=", "form_cut_input_detail.form_cut_id")->whereRaw("scanned_item.id_roll in (" . $idsStr . ")")->orderBy("scanned_item.id", "desc")->groupBy("scanned_item.id_roll")->get();

        PDF::setOption(['dpi' => 150, 'defaultFont' => 'Helvetica-Bold']);
        $pdf = PDF::loadView('cutting.roll.pdf.mass-sisa-kain-roll', ["sbItems" => ($sbItems ? $sbItems : null), "ndsItems" => $ndsItems])->setPaper('a7', 'landscape');

        $fileName = 'Mass_Sisa_Kain.pdf';

        return $pdf->download(str_replace("/", "_", $fileName));
    }


    /// alokasi fabric gr panel
    public function alokasi_fabric_gr_panel(Request $request)
    {
        $tgl_awal = $request->dateFrom;
        $tgl_akhir = $request->dateTo;
        $tgl_skrg = date('Y-m-d');
        $tgl_skrg_min_sebulan = date('Y-m-d', strtotime('-30 days'));
        $user = Auth::user()->name;

        if ($request->ajax()) {
            $data_input = DB::select("SELECT
a.tgl_trans,
DATE_FORMAT(a.tgl_trans, '%d-%M-%Y') AS tgl_trans_fix,
barcode,
id_item,
buyer,
c.kpno as ws,
c.styleno,
color,
a.qty_pakai,
b.unit,
a.created_by,
a.updated_at
from form_cut_alokasi_gr_panel_barcode a
left join scanned_item b on a.barcode = b.id_roll
left join (
select supplier as buyer,ac.styleno,jd.id_jo, ac.kpno
from signalbit_erp.jo_det jd
         inner join signalbit_erp.so on jd.id_so = so.id
         inner join signalbit_erp.act_costing ac on so.id_cost = ac.id
		    inner join signalbit_erp.mastersupplier ms on ac.id_buyer = ms.id_supplier
         where jd.cancel = 'N'
         group by id_cost order by id_jo asc
) c on b.id_jo = c.id_jo
 where a.tgl_trans >= '$tgl_awal' and a.tgl_trans <= '$tgl_akhir'
 order by a.tgl_trans desc
            ");

            return DataTables::of($data_input)->toJson();
        }

        return view(
            'cutting.roll.alokasi_fabric_gr_panel',
            [
                'page' => 'dashboard-cutting',
                "subPageGroup" => "laporan-cutting",
                "subPage" => "alokasi-fabric-gr-panel",
                'tgl_skrg_min_sebulan' => $tgl_skrg_min_sebulan,
                'tgl_skrg' => $tgl_skrg,
                "user" => $user
            ]
        );
    }

    public function create_alokasi_fabric_gr_panel(Request $request)
    {
        $user = Auth::user()->name;

        return view(
            'cutting.roll.create_alokasi_fabric_gr_panel',
            [
                'page' => 'dashboard-cutting',
                "subPageGroup" => "laporan-cutting",
                "subPage" => "alokasi-fabric-gr-panel",
                "user" => $user
            ]
        );
    }


    public function save_alokasi_fabric_gr_panel(Request $request)
    {
        $user = Auth::user()->name;
        $timestamp = Carbon::now();

        $barcode = $request->barcode;
        $qty_roll = $request->qty_roll;
        $qty_sisa = $request->qty_sisa;
        $qty_pakai = $request->qty_pakai;
        $today     = date('Y-m-d');

        // Update scanned item (roll detail & qty)
        ScannedItem::updateOrCreate(
            ["id_roll" => $barcode],
            [
                "qty" => $qty_sisa,
            ]
        );


        $id = DB::table('form_cut_alokasi_gr_panel_barcode')->insertGetId([
            'tgl_trans'             => $today,
            'barcode'               => $barcode,
            'qty_roll'              => $qty_roll,
            'qty_pakai'             => $qty_pakai,
            'sisa_kain'             => $qty_sisa,
            'created_by'            => $user,
            'created_at'            => $timestamp,
            'updated_at'            => $timestamp,
        ]);

        // Return detailed response
        return response()->json([
            'status' => 'success',
            'message' => 'Form berhasil disimpan.'
        ]);
    }



    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
