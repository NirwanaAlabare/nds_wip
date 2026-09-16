<?php

namespace App\Http\Controllers\Cutting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use \avadim\FastExcelLaravel\Excel as FastExcel;
use DB;

class CuttingStockOpnameController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $dateFrom = $request->dateFrom;
            $dateTo = $request->dateTo;

            $stockOpname = DB::connection("mysql_so")->table('opname_fabric_cutting')->
                selectRaw("
                    opname_fabric_cutting.tanggal_opname,
                    opname_fabric_cutting.nomor_opname,
                    opname_fabric_cutting.status,
                    whs_bppb_det.no_bppb,
                    opname_fabric_cutting_detail.id_roll,
                    opname_fabric_cutting_detail.id_item,
                    opname_fabric_cutting_detail.no_lot,
                    opname_fabric_cutting_detail.no_roll,
                    opname_fabric_cutting_detail.no_roll_buyer,
                    opname_fabric_cutting_detail.item_desc,
                    opname_fabric_cutting_detail.no_ws_aktual,
                    opname_fabric_cutting_detail.style_aktual,
                    opname_fabric_cutting_detail.color,
                    opname_fabric_cutting_detail.qty_aktual,
                    opname_fabric_cutting_detail.unit_aktual,
                    scanned_item.qty,
                    scanned_item.unit,
                    opname_fabric_cutting_detail.created_by,
                    opname_fabric_cutting_detail.created_by_username,
                    opname_fabric_cutting_detail.created_at
                ")->
                leftJoin("opname_fabric_cutting_detail", "opname_fabric_cutting.id", "=", "opname_fabric_cutting_detail.opname_fabric_cutting_id")->
                leftJoin("signalbit_erp.whs_bppb_det", "opname_fabric_cutting_detail.whs_bppb_det_id", "=", "whs_bppb_det.id")->
                leftJoin("laravel_nds.scanned_item", "scanned_item.id_roll", "=", "opname_fabric_cutting_detail.id_roll")->
                whereNotNull('opname_fabric_cutting_detail.id_roll')->
                whereBetween('tanggal_opname', [$dateFrom, $dateTo])->
                get();

            return Datatables::of($stockOpname)->toJson();
        }

        return view('cutting.stock-opname.index', ["page" => "dashboard-cutting"]);
    }

    public function exportExcel(Request $request)
    {
        $dateFrom = $request->dateFrom;
        $dateTo = $request->dateTo;

        $excel = FastExcel::create('data');
        $sheet = $excel->getSheet();

        $area = $sheet->beginArea();

        $sheet->writeTo('A1', 'Stock Opname Cutting', ['font-size' => 16]);
        $sheet->mergeCells('A1:O1');

        $sheet->writeTo('A2', "Tanggal Opname")->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('B2', "Nomor Opname")->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('C2', "Status")->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('D2', "No BPPB")->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('E2', "ID Roll")->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('F2', "ID Item")->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('G2', "No Lot")->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('H2', "No Roll")->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('I2', "No Roll Buyer")->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('J2', "Item Desc")->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('K2', "No WS Aktual")->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('L2', "Style Aktual")->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('M2', "Color")->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('N2', "Qty Sistem")->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('O2', "Unit Sistem")->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('P2', "Qty Aktual")->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('Q2', "Unit Aktual")->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('R2', "Created At")->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('S2', "User NIK")->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('T2', "User Name")->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        DB::connection("mysql_so")->table('opname_fabric_cutting')
            ->selectRaw("
                opname_fabric_cutting.tanggal_opname,
                opname_fabric_cutting.nomor_opname,
                opname_fabric_cutting.status,
                whs_bppb_det.no_bppb,
                opname_fabric_cutting_detail.id_roll,
                opname_fabric_cutting_detail.id_item,
                opname_fabric_cutting_detail.no_lot,
                opname_fabric_cutting_detail.no_roll,
                opname_fabric_cutting_detail.no_roll_buyer,
                opname_fabric_cutting_detail.item_desc,
                opname_fabric_cutting_detail.no_ws_aktual,
                opname_fabric_cutting_detail.style_aktual,
                opname_fabric_cutting_detail.color,
                opname_fabric_cutting_detail.qty_aktual,
                opname_fabric_cutting_detail.unit_aktual,
                scanned_item.qty,
                scanned_item.unit,
                opname_fabric_cutting_detail.created_at,
                opname_fabric_cutting_detail.created_by,
                opname_fabric_cutting_detail.created_by_username
            ")
            ->leftJoin("opname_fabric_cutting_detail", "opname_fabric_cutting.id", "=", "opname_fabric_cutting_detail.opname_fabric_cutting_id")
            ->leftJoin("signalbit_erp.whs_bppb_det", "opname_fabric_cutting_detail.whs_bppb_det_id", "=", "whs_bppb_det.id")
            ->leftJoin("laravel_nds.scanned_item", "scanned_item.id_roll", "=", "opname_fabric_cutting_detail.id_roll")
            ->whereNotNull('opname_fabric_cutting_detail.id_roll')
            ->whereBetween('tanggal_opname', [$dateFrom, $dateTo])
            ->orderBy("opname_fabric_cutting_detail.id", "asc")
            ->chunk(10000, function ($rows) use ($sheet) {
                $sheet->writeAreas();

                foreach ($rows as $row) {
                    $rowArr = [
                        $row->tanggal_opname ?? "-",
                        $row->nomor_opname ?? "-",
                        $row->status ?? "-",
                        $row->no_bppb ?? "-",
                        $row->id_roll ?? "-",
                        $row->id_item ?? "-",
                        $row->no_lot ?? "-",
                        $row->no_roll ?? "-",
                        $row->no_roll_buyer ?? "-",
                        $row->item_desc ?? "-",
                        $row->no_ws_aktual ?? "-",
                        $row->style_aktual ?? "-",
                        $row->color ?? "-",
                        num($row->qty, 2) ?? "-",
                        $row->unit ?? "-",
                        num($row->qty_aktual, 2) ?? "-",
                        $row->unit_aktual ?? "-",
                        $row->created_at ?? "-",
                        $row->created_by ?? "-",
                        $row->created_by_username ?? "-",
                    ];

                    $sheet->writeRow($rowArr)->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                }
            });

        $filename = date('Y-m-d') . ' Stock Opname Cutting.xlsx';

        return $excel->download($filename);
    }
}
