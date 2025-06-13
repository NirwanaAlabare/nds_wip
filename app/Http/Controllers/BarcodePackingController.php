<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PPICMasterSo;
use App\Models\SignalBit\ActCosting;
use App\Models\SignalBit\SoDet;
use PDF;

class BarcodePackingController extends Controller
{
    public function index()
    {
        $orders = ActCosting::select('id', 'kpno', 'styleno')->where('status', '!=', 'CANCEL')->where('cost_date', '>=', '2023-01-01')->where('type_ws', 'STD')->orderBy('cost_date', 'desc')->orderBy('kpno', 'asc')->get();

        return view(
            'ppic.barcode-packing.barcode-packing',
            [
                "page" => "dashboard-ppic",
                "subPageGroup" => "generate-barcode-packing",
                "subPage" => "generate-barcode-packing",
                "orders" => $orders
            ]
        );
    }

    public function getBarcode(Request $request) {
        $barcode = SoDet::where("id", $request->so_det_id)->first();

        return $barcode;
    }

    public function generateBarcode($barcode)
    {
        if ($barcode) {
            $masterSo = SoDet::select("so_det.id", "act_costing.kpno", "act_costing.styleno", "so_det.color", "so_det.size", "so_det.dest")->leftJoin("so", "so.id", "=", "so_det.id_so")->leftJoin("act_costing", "act_costing.id", "=", "so.id_cost")->where("so_det.id", $barcode)->first();

            $pdf = PDF::loadView('ppic.barcode-packing.export.barcode-packing-pdf', ['masterSo' => $masterSo])->setPaper('A7', 'landscape');

            return $pdf->stream('Barcode Packing '.$barcode.'.pdf');
        }

        return null;
    }

    public function downloadBarcode(Request $request)
    {
        $barcode = $request->barcode;

        if ($barcode) {
            $masterSo = SoDet::select("so_det.id", "act_costing.kpno", "act_costing.styleno", "so_det.color", "so_det.size", "so_det.dest")->leftJoin("so", "so.id", "=", "so_det.id_so")->leftJoin("act_costing", "act_costing.id", "=", "so.id_cost")->where("so_det.id", $barcode)->first();

            $pdf = PDF::loadView('ppic.barcode-packing.export.barcode-packing-pdf', ['masterSo' => $masterSo])->setPaper('A7', 'landscape');

            return $pdf->download('Barcode Packing '.$barcode.'.pdf');
        }

        return null;
    }
}
