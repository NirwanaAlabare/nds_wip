<?php

namespace App\Http\Controllers\Sewing;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;
use \avadim\FastExcelLaravel\Excel as FastExcel;

class ReportQcFinishingCekReturnPackingController extends Controller
{
    public function index(Request $request){

        if ($request->ajax()) {
            $tipe = $request->tipe;
            $tglAwal = $request->dateFrom;
            $tglAkhir = $request->dateTo;
            $buyer = $request->buyer;

            if ($tipe == 'Detail') {
                $data = DB::table(DB::raw("
                    (
                        SELECT 
                            *
                        FROM (
                            SELECT
                                x.*,
                                ROW_NUMBER() OVER (
                                    PARTITION BY x.id
                                    ORDER BY x.last_update DESC
                                ) AS rn
                            FROM (
                                SELECT
                                    rfts.id,
                                    DATE_FORMAT( defect.created_at, '%d-%m-%Y' ) AS tgl_return,
                                    rfts.kode_numbering,
                                    mb.buyer,
                                    rfts.kpno AS ws,
                                    rfts.style,
                                    rfts.color,
                                    rfts.size,
                                    rfts.packing_line,
                                    rfts.line_qc_finishing,
                                    'DEFECT' AS status,
                                    output_defect_areas.defect_area,
                                    output_defect_types.defect_type,
                                    output_defect_types.allocation AS alokasi,
                                    defect.created_at AS last_update 
                                FROM
                                    signalbit_erp.output_defect_packing_po_return defect
                                    LEFT JOIN signalbit_erp.output_rfts_packing_po_return rfts ON rfts.id = defect.output_rfts_packing_po_return_id
                                    LEFT JOIN signalbit_erp.master_plan ON master_plan.id = rfts.master_plan_id
                                    LEFT JOIN signalbit_erp.output_defect_areas ON output_defect_areas.id = defect.defect_area_id
                                    LEFT JOIN signalbit_erp.output_defect_types ON output_defect_types.id = defect.defect_type_id
                                    LEFT JOIN (
                                    SELECT
                                        sd.id AS id_so_det,
                                        supplier AS buyer 
                                    FROM
                                        signalbit_erp.so_det sd
                                        INNER JOIN signalbit_erp.so ON sd.id_so = so.id
                                        INNER JOIN signalbit_erp.jo_det jd ON so.id = jd.id_so
                                        INNER JOIN signalbit_erp.act_costing ac ON so.id_cost = ac.id
                                        INNER JOIN signalbit_erp.mastersupplier ms ON ac.id_buyer = ms.id_supplier 
                                    WHERE
                                        jd.cancel = 'N' 
                                    ) mb ON rfts.so_det_id = mb.id_so_det 
                                WHERE
                                    DATE ( defect.created_at ) >= '{$tglAwal}' 
                                    AND DATE ( defect.created_at ) <= '{$tglAkhir}' 
                                    AND defect.defect_status = 'defect'
                                    
                                UNION ALL
                                
                                SELECT
                                    rfts.id,
                                    DATE_FORMAT( defect.reworked_at, '%d-%m-%Y' ) AS tgl_return,
                                    rfts.kode_numbering,
                                    mb.buyer,
                                    rfts.kpno AS ws,
                                    rfts.style,
                                    rfts.color,
                                    rfts.size,
                                    rfts.packing_line,
                                    rfts.line_qc_finishing,
                                    'REWORK' AS status,
                                    output_defect_areas.defect_area,
                                    output_defect_types.defect_type,
                                    output_defect_types.allocation AS alokasi,
                                    defect.reworked_at AS last_update 
                                FROM
                                    signalbit_erp.output_defect_packing_po_return defect
                                    LEFT JOIN signalbit_erp.output_rfts_packing_po_return rfts ON rfts.id = defect.output_rfts_packing_po_return_id
                                    LEFT JOIN signalbit_erp.master_plan ON master_plan.id = rfts.master_plan_id
                                    LEFT JOIN signalbit_erp.output_defect_areas ON output_defect_areas.id = defect.defect_area_id
                                    LEFT JOIN signalbit_erp.output_defect_types ON output_defect_types.id = defect.defect_type_id
                                    LEFT JOIN (
                                    SELECT
                                        sd.id AS id_so_det,
                                        supplier AS buyer 
                                    FROM
                                        signalbit_erp.so_det sd
                                        INNER JOIN signalbit_erp.so ON sd.id_so = so.id
                                        INNER JOIN signalbit_erp.jo_det jd ON so.id = jd.id_so
                                        INNER JOIN signalbit_erp.act_costing ac ON so.id_cost = ac.id
                                        INNER JOIN signalbit_erp.mastersupplier ms ON ac.id_buyer = ms.id_supplier 
                                    WHERE
                                        jd.cancel = 'N' 
                                    ) mb ON rfts.so_det_id = mb.id_so_det 
                                WHERE
                                    DATE ( defect.reworked_at ) >= '{$tglAwal}' 
                                    AND DATE ( defect.reworked_at ) <= '{$tglAkhir}' 
                                    AND defect.defect_status = 'reworked'
                                    
                                UNION ALL
                                
                                SELECT
                                    rfts.id,
                                    DATE_FORMAT( reject.created_at, '%d-%m-%Y' ) AS tgl_return,
                                    rfts.kode_numbering,
                                    mb.buyer,
                                    rfts.kpno AS ws,
                                    rfts.style,
                                    rfts.color,
                                    rfts.size,
                                    rfts.packing_line,
                                    rfts.line_qc_finishing,
                                    'REJECT' AS status,
                                    output_defect_areas.defect_area,
                                    output_defect_types.defect_type,
                                    output_defect_types.allocation AS alokasi,
                                    reject.created_at AS last_update 
                                FROM
                                    signalbit_erp.output_reject_packing_po_return reject
                                    LEFT JOIN signalbit_erp.output_rfts_packing_po_return rfts ON rfts.id = reject.output_rfts_packing_po_return_id
                                    LEFT JOIN signalbit_erp.master_plan ON master_plan.id = rfts.master_plan_id
                                    LEFT JOIN signalbit_erp.output_defect_areas ON output_defect_areas.id = reject.reject_area_id
                                    LEFT JOIN signalbit_erp.output_defect_types ON output_defect_types.id = reject.reject_type_id
                                    LEFT JOIN (
                                    SELECT
                                        sd.id AS id_so_det,
                                        supplier AS buyer 
                                    FROM
                                        signalbit_erp.so_det sd
                                        INNER JOIN signalbit_erp.so ON sd.id_so = so.id
                                        INNER JOIN signalbit_erp.jo_det jd ON so.id = jd.id_so
                                        INNER JOIN signalbit_erp.act_costing ac ON so.id_cost = ac.id
                                        INNER JOIN signalbit_erp.mastersupplier ms ON ac.id_buyer = ms.id_supplier 
                                    WHERE
                                        jd.cancel = 'N' 
                                    ) mb ON rfts.so_det_id = mb.id_so_det 
                                WHERE
                                    DATE ( reject.created_at ) >= '{$tglAwal}' 
                                    AND DATE ( reject.created_at ) <= '{$tglAkhir}' 
                            ) x
                        ) y
                        WHERE y.rn = 1
                        ORDER BY y.last_update DESC
                    ) as results
                "))
                ->when($buyer, function ($query) use ($buyer) {
                    return $query->where('results.buyer', $buyer);
                });

            } else if ($tipe == 'Defect Summary') {
                $data = DB::table(DB::raw("
                    (
                        SELECT
                            mb.buyer,
                            rfts.kpno as ws,
                            rfts.style,
                            rfts.color,
                            rfts.size,
                            COUNT(*) AS qty
                        FROM
                            signalbit_erp.output_defect_packing_po_return defect
                        LEFT JOIN signalbit_erp.output_rfts_packing_po_return rfts ON rfts.id = defect.output_rfts_packing_po_return_id
                        LEFT JOIN (
                            SELECT
                                sd.id as id_so_det,
                                supplier as buyer
                            FROM signalbit_erp.so_det sd
                            INNER JOIN signalbit_erp.so ON sd.id_so = so.id
                            INNER JOIN signalbit_erp.jo_det jd ON so.id = jd.id_so
                            INNER JOIN signalbit_erp.act_costing ac ON so.id_cost = ac.id
                            INNER JOIN signalbit_erp.mastersupplier ms ON ac.id_buyer = ms.id_supplier
                            WHERE jd.cancel = 'N'
                        ) mb on rfts.so_det_id = mb.id_so_det
                        WHERE
                            DATE(defect.created_at) >= '{$tglAwal}'
                            AND DATE(defect.created_at) <= '{$tglAkhir}'
                        GROUP BY mb.buyer, rfts.kpno, rfts.style, rfts.color, rfts.size
                        ORDER BY mb.buyer ASC
                    ) as results
                "))
                ->when($buyer, function ($query) use ($buyer) {
                    return $query->where('results.buyer', $buyer);
                });

            } else if ($tipe == 'Rework Summary') {
                $data = DB::table(DB::raw("
                    (
                        SELECT
                            mb.buyer,
                            rfts.kpno as ws,
                            rfts.style,
                            rfts.color,
                            rfts.size,
                            COUNT(*) AS qty
                        FROM
                            signalbit_erp.output_defect_packing_po_return defect
                        LEFT JOIN signalbit_erp.output_rfts_packing_po_return rfts ON rfts.id = defect.output_rfts_packing_po_return_id
                        LEFT JOIN (
                            SELECT
                                sd.id as id_so_det,
                                supplier as buyer
                            FROM signalbit_erp.so_det sd
                            INNER JOIN signalbit_erp.so ON sd.id_so = so.id
                            INNER JOIN signalbit_erp.jo_det jd ON so.id = jd.id_so
                            INNER JOIN signalbit_erp.act_costing ac ON so.id_cost = ac.id
                            INNER JOIN signalbit_erp.mastersupplier ms ON ac.id_buyer = ms.id_supplier
                            WHERE jd.cancel = 'N'
                        ) mb on rfts.so_det_id = mb.id_so_det
                        WHERE
                            DATE(defect.reworked_at) >= '{$tglAwal}'
                            AND DATE(defect.reworked_at) <= '{$tglAkhir}'
                            AND defect_status = 'reworked'
                        GROUP BY mb.buyer, rfts.kpno, rfts.style, rfts.color, rfts.size
                        ORDER BY mb.buyer ASC
                    ) as results
                "))
                ->when($buyer, function ($query) use ($buyer) {
                    return $query->where('results.buyer', $buyer);
                });

            } else if ($tipe == 'Reject Summary') {
                $data = DB::table(DB::raw("
                    (
                        SELECT
                            mb.buyer,
                            rfts.kpno as ws,
                            rfts.style,
                            rfts.color,
                            rfts.size,
                            COUNT(*) AS qty
                        FROM
                            signalbit_erp.output_reject_packing_po_return reject
                        LEFT JOIN signalbit_erp.output_rfts_packing_po_return rfts ON rfts.id = reject.output_rfts_packing_po_return_id
                        LEFT JOIN (
                            SELECT
                                sd.id as id_so_det,
                                supplier as buyer
                            FROM signalbit_erp.so_det sd
                            INNER JOIN signalbit_erp.so ON sd.id_so = so.id
                            INNER JOIN signalbit_erp.jo_det jd ON so.id = jd.id_so
                            INNER JOIN signalbit_erp.act_costing ac ON so.id_cost = ac.id
                            INNER JOIN signalbit_erp.mastersupplier ms ON ac.id_buyer = ms.id_supplier
                            WHERE jd.cancel = 'N'
                        ) mb on rfts.so_det_id = mb.id_so_det
                        WHERE
                            DATE(reject.created_at) >= '{$tglAwal}'
                            AND DATE(reject.created_at) <= '{$tglAkhir}'
                        GROUP BY mb.buyer, rfts.kpno, rfts.style, rfts.color, rfts.size
                        ORDER BY mb.buyer ASC
                    ) as results
                "))
                ->when($buyer, function ($query) use ($buyer) {
                    return $query->where('results.buyer', $buyer);
                });

            } else {
                $data = DB::table(DB::raw("(SELECT 1 as dummy) as results"))->whereRaw('1 = 0');
            }

            return DataTables::queryBuilder($data)->make(true);
        }

        $buyer = DB::connection('mysql_sb')
            ->table('mastersupplier')
            ->select('supplier')
            ->orderBy('supplier', 'ASC')
            ->get();

        return view("sewing.report.report-qc-finishing-cek-return-packing", [
            'page' => 'dashboard-sewing-eff',
            "subPageGroup" => "sewing-report",
            "subPage" => "report-qc-finishing-cek-return-packing",
            'containerFluid' => true,
            "buyer" => $buyer
        ]);
    }

    public function export(Request $request)
    {
        $tipe = $request->tipe;
        $tglAwal = $request->dateFrom;
        $tglAkhir = $request->dateTo;
        $buyer = $request->buyer;

        if ($tipe == 'Detail') {
            $data = DB::table(DB::raw("
                (
                    SELECT 
                        *
                    FROM (
                        SELECT
                            x.*,
                            ROW_NUMBER() OVER (
                                PARTITION BY x.id
                                ORDER BY x.last_update DESC
                            ) AS rn
                        FROM (
                            SELECT
                                rfts.id,
                                DATE_FORMAT( defect.created_at, '%d-%m-%Y' ) AS tgl_return,
                                rfts.kode_numbering,
                                mb.buyer,
                                rfts.kpno AS ws,
                                rfts.style,
                                rfts.color,
                                rfts.size,
                                rfts.packing_line,
                                rfts.line_qc_finishing,
                                'DEFECT' AS status,
                                output_defect_areas.defect_area,
                                output_defect_types.defect_type,
                                output_defect_types.allocation AS alokasi,
                                defect.created_at AS last_update 
                            FROM
                                signalbit_erp.output_defect_packing_po_return defect
                                LEFT JOIN signalbit_erp.output_rfts_packing_po_return rfts ON rfts.id = defect.output_rfts_packing_po_return_id
                                LEFT JOIN signalbit_erp.master_plan ON master_plan.id = rfts.master_plan_id
                                LEFT JOIN signalbit_erp.output_defect_areas ON output_defect_areas.id = defect.defect_area_id
                                LEFT JOIN signalbit_erp.output_defect_types ON output_defect_types.id = defect.defect_type_id
                                LEFT JOIN (
                                SELECT
                                    sd.id AS id_so_det,
                                    supplier AS buyer 
                                FROM
                                    signalbit_erp.so_det sd
                                    INNER JOIN signalbit_erp.so ON sd.id_so = so.id
                                    INNER JOIN signalbit_erp.jo_det jd ON so.id = jd.id_so
                                    INNER JOIN signalbit_erp.act_costing ac ON so.id_cost = ac.id
                                    INNER JOIN signalbit_erp.mastersupplier ms ON ac.id_buyer = ms.id_supplier 
                                WHERE
                                    jd.cancel = 'N' 
                                ) mb ON rfts.so_det_id = mb.id_so_det 
                            WHERE
                                DATE ( defect.created_at ) >= '{$tglAwal}' 
                                AND DATE ( defect.created_at ) <= '{$tglAkhir}' 
                                AND defect.defect_status = 'defect'
                                
                            UNION ALL
                            
                            SELECT
                                rfts.id,
                                DATE_FORMAT( defect.reworked_at, '%d-%m-%Y' ) AS tgl_return,
                                rfts.kode_numbering,
                                mb.buyer,
                                rfts.kpno AS ws,
                                rfts.style,
                                rfts.color,
                                rfts.size,
                                rfts.packing_line,
                                rfts.line_qc_finishing,
                                'REWORK' AS status,
                                output_defect_areas.defect_area,
                                output_defect_types.defect_type,
                                output_defect_types.allocation AS alokasi,
                                defect.reworked_at AS last_update 
                            FROM
                                signalbit_erp.output_defect_packing_po_return defect
                                LEFT JOIN signalbit_erp.output_rfts_packing_po_return rfts ON rfts.id = defect.output_rfts_packing_po_return_id
                                LEFT JOIN signalbit_erp.master_plan ON master_plan.id = rfts.master_plan_id
                                LEFT JOIN signalbit_erp.output_defect_areas ON output_defect_areas.id = defect.defect_area_id
                                LEFT JOIN signalbit_erp.output_defect_types ON output_defect_types.id = defect.defect_type_id
                                LEFT JOIN (
                                SELECT
                                    sd.id AS id_so_det,
                                    supplier AS buyer 
                                FROM
                                    signalbit_erp.so_det sd
                                    INNER JOIN signalbit_erp.so ON sd.id_so = so.id
                                    INNER JOIN signalbit_erp.jo_det jd ON so.id = jd.id_so
                                    INNER JOIN signalbit_erp.act_costing ac ON so.id_cost = ac.id
                                    INNER JOIN signalbit_erp.mastersupplier ms ON ac.id_buyer = ms.id_supplier 
                                WHERE
                                    jd.cancel = 'N' 
                                ) mb ON rfts.so_det_id = mb.id_so_det 
                            WHERE
                                DATE ( defect.reworked_at ) >= '{$tglAwal}' 
                                AND DATE ( defect.reworked_at ) <= '{$tglAkhir}' 
                                AND defect.defect_status = 'reworked'
                                
                            UNION ALL
                            
                            SELECT
                                rfts.id,
                                DATE_FORMAT( reject.created_at, '%d-%m-%Y' ) AS tgl_return,
                                rfts.kode_numbering,
                                mb.buyer,
                                rfts.kpno AS ws,
                                rfts.style,
                                rfts.color,
                                rfts.size,
                                rfts.packing_line,
                                rfts.line_qc_finishing,
                                'REJECT' AS status,
                                output_defect_areas.defect_area,
                                output_defect_types.defect_type,
                                output_defect_types.allocation AS alokasi,
                                reject.created_at AS last_update 
                            FROM
                                signalbit_erp.output_reject_packing_po_return reject
                                LEFT JOIN signalbit_erp.output_rfts_packing_po_return rfts ON rfts.id = reject.output_rfts_packing_po_return_id
                                LEFT JOIN signalbit_erp.master_plan ON master_plan.id = rfts.master_plan_id
                                LEFT JOIN signalbit_erp.output_defect_areas ON output_defect_areas.id = reject.reject_area_id
                                LEFT JOIN signalbit_erp.output_defect_types ON output_defect_types.id = reject.reject_type_id
                                LEFT JOIN (
                                SELECT
                                    sd.id AS id_so_det,
                                    supplier AS buyer 
                                FROM
                                    signalbit_erp.so_det sd
                                    INNER JOIN signalbit_erp.so ON sd.id_so = so.id
                                    INNER JOIN signalbit_erp.jo_det jd ON so.id = jd.id_so
                                    INNER JOIN signalbit_erp.act_costing ac ON so.id_cost = ac.id
                                    INNER JOIN signalbit_erp.mastersupplier ms ON ac.id_buyer = ms.id_supplier 
                                WHERE
                                    jd.cancel = 'N' 
                                ) mb ON rfts.so_det_id = mb.id_so_det 
                            WHERE
                                DATE ( reject.created_at ) >= '{$tglAwal}' 
                                AND DATE ( reject.created_at ) <= '{$tglAkhir}' 
                        ) x
                    ) y
                    WHERE y.rn = 1
                    ORDER BY y.last_update DESC
                ) as results
            "))
            ->when($buyer, function ($query) use ($buyer) {
                return $query->where('results.buyer', $buyer);
            })->get();

        } else if ($tipe == 'Defect Summary') {
            $data = DB::table(DB::raw("
                (
                    SELECT
                        mb.buyer,
                        rfts.kpno as ws,
                        rfts.style,
                        rfts.color,
                        rfts.size,
                        COUNT(*) AS qty
                    FROM
                        signalbit_erp.output_defect_packing_po_return defect
                    LEFT JOIN signalbit_erp.output_rfts_packing_po_return rfts ON rfts.id = defect.output_rfts_packing_po_return_id
                    LEFT JOIN (
                        SELECT
                            sd.id as id_so_det,
                            supplier as buyer
                        FROM signalbit_erp.so_det sd
                        INNER JOIN signalbit_erp.so ON sd.id_so = so.id
                        INNER JOIN signalbit_erp.jo_det jd ON so.id = jd.id_so
                        INNER JOIN signalbit_erp.act_costing ac ON so.id_cost = ac.id
                        INNER JOIN signalbit_erp.mastersupplier ms ON ac.id_buyer = ms.id_supplier
                        WHERE jd.cancel = 'N'
                    ) mb on rfts.so_det_id = mb.id_so_det
                    WHERE
                        DATE(defect.created_at) >= '{$tglAwal}'
                        AND DATE(defect.created_at) <= '{$tglAkhir}'
                    GROUP BY mb.buyer, rfts.kpno, rfts.style, rfts.color, rfts.size
                    ORDER BY mb.buyer ASC
                ) as results
            "))
            ->when($buyer, function ($query) use ($buyer) {
                return $query->where('results.buyer', $buyer);
            })->get();

        } else if ($tipe == 'Rework Summary') {
            $data = DB::table(DB::raw("
                (
                    SELECT
                        mb.buyer,
                        rfts.kpno as ws,
                        rfts.style,
                        rfts.color,
                        rfts.size,
                        COUNT(*) AS qty
                    FROM
                        signalbit_erp.output_defect_packing_po_return defect
                    LEFT JOIN signalbit_erp.output_rfts_packing_po_return rfts ON rfts.id = defect.output_rfts_packing_po_return_id
                    LEFT JOIN (
                        SELECT
                            sd.id as id_so_det,
                            supplier as buyer
                        FROM signalbit_erp.so_det sd
                        INNER JOIN signalbit_erp.so ON sd.id_so = so.id
                        INNER JOIN signalbit_erp.jo_det jd ON so.id = jd.id_so
                        INNER JOIN signalbit_erp.act_costing ac ON so.id_cost = ac.id
                        INNER JOIN signalbit_erp.mastersupplier ms ON ac.id_buyer = ms.id_supplier
                        WHERE jd.cancel = 'N'
                    ) mb on rfts.so_det_id = mb.id_so_det
                    WHERE
                        DATE(defect.reworked_at) >= '{$tglAwal}'
                        AND DATE(defect.reworked_at) <= '{$tglAkhir}'
                        AND defect_status = 'reworked'
                    GROUP BY mb.buyer, rfts.kpno, rfts.style, rfts.color, rfts.size
                    ORDER BY mb.buyer ASC
                ) as results
            "))
            ->when($buyer, function ($query) use ($buyer) {
                return $query->where('results.buyer', $buyer);
            })->get();

        } else if ($tipe == 'Reject Summary') {
            $data = DB::table(DB::raw("
                (
                    SELECT
                        mb.buyer,
                        rfts.kpno as ws,
                        rfts.style,
                        rfts.color,
                        rfts.size,
                        COUNT(*) AS qty
                    FROM
                        signalbit_erp.output_reject_packing_po_return reject
                    LEFT JOIN signalbit_erp.output_rfts_packing_po_return rfts ON rfts.id = reject.output_rfts_packing_po_return_id
                    LEFT JOIN (
                        SELECT
                            sd.id as id_so_det,
                            supplier as buyer
                        FROM signalbit_erp.so_det sd
                        INNER JOIN signalbit_erp.so ON sd.id_so = so.id
                        INNER JOIN signalbit_erp.jo_det jd ON so.id = jd.id_so
                        INNER JOIN signalbit_erp.act_costing ac ON so.id_cost = ac.id
                        INNER JOIN signalbit_erp.mastersupplier ms ON ac.id_buyer = ms.id_supplier
                        WHERE jd.cancel = 'N'
                    ) mb on rfts.so_det_id = mb.id_so_det
                    WHERE
                        DATE(reject.created_at) >= '{$tglAwal}'
                        AND DATE(reject.created_at) <= '{$tglAkhir}'
                    GROUP BY mb.buyer, rfts.kpno, rfts.style, rfts.color, rfts.size
                    ORDER BY mb.buyer ASC
                ) as results
            "))
            ->when($buyer, function ($query) use ($buyer) {
                return $query->where('results.buyer', $buyer);
            })->get();

        } else {
            $data = DB::table(DB::raw("(SELECT 1 as dummy) as results"))->whereRaw('1 = 0')->get();
        }

        $fileName = 'report-qc-finishing-cek-return-packing';

        $excel = FastExcel::create($fileName);

        $sheet = $excel->sheet();

        $sheet->writeRow(
            ['Report QC Finishing Cek Return Packing'],
            [
                'font-style' => 'bold',
                'font-size'  => 14,
            ]
        );

        $sheet->writeRow(
            ['Periode ' . $tglAwal . ' s/d ' . $tglAkhir],
            [
                'font-size' => 12,
            ]
        );
        $sheet->writeRow(
            ['Tipe : ' . $tipe],
            [
                'font-size' => 12,
            ]
        );
        $sheet->writeRow(
            ['Buyer : ' . $buyer],
            [
                'font-size' => 12,
            ]
        );

        $sheet->writeRow(['']);

        if ($tipe == 'Detail') {
            $header = [
                'Tgl Return',
                'Nomor QR',
                'Buyer',
                'Worksheet',
                'Style',
                'Color',
                'Size',
                'Packing Line',
                'QC Finishing',
                'Status',
                'Defect Area',
                'Defect Type',
                'Alokasi',
                'Last Update',
            ];
        } else {
            $header = [
                'Buyer',
                'Worksheet',
                'Style',
                'Color',
                'Size',
                'Qty',
            ];
        }

        $sheet->writeRow($header, [
            'font-style' => 'bold',
            'border' => 'thin',
        ]);

        foreach ($data as $row) {
            if ($tipe == 'Detail') {
                $rows = [
                    $row->tgl_return,
                    $row->kode_numbering ?? '',
                    $row->buyer,
                    $row->ws,
                    $row->style,
                    $row->color,
                    $row->size,
                    $row->packing_line,
                    $row->line_qc_finishing,
                    $row->status,
                    $row->defect_area ?? '',
                    $row->defect_type ?? '',
                    $row->alokasi ?? '',
                    $row->last_update,
                ];

                foreach (range('A', 'N') as $col) {
                    $sheet->setColWidth($col, 20);
                }
            } else {
                $rows = [
                    $row->buyer,
                    $row->ws,
                    $row->style,
                    $row->color,
                    $row->size,
                    (float) $row->qty,
                ];

                foreach (range('A', 'F') as $col) {
                    $sheet->setColWidth($col, 20);
                }
            }

            $sheet->writeRow($rows, [
                'border' => 'thin',
            ]);
        }

        return $excel->download();
    }
}
