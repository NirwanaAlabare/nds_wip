<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PPICMasterSo;
use App\Models\SignalBit\ActCosting;
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
        $barcode = PPICMasterSo::where("id_so_det", $request->so_det_id)->first();

        return $barcode;
    }

    public function generateBarcode($barcode)
    {
        if ($barcode) {
            $masterSo = PPICMasterSo::select("ppic_master_so.barcode", "master_sb_ws.ws", "master_sb_ws.styleno", "master_sb_ws.color", "master_sb_ws.size", "master_sb_ws.dest")->leftJoin("master_sb_ws", "master_sb_ws.id_so_det", "=", "ppic_master_so.id_so_det")->where("ppic_master_so.barcode", $barcode)->first();

            $pdf = PDF::loadView('ppic.barcode-packing.export.barcode-packing-pdf', ['masterSo' => $masterSo])->setPaper('A7', 'landscape');

            return $pdf->stream('Barcode Packing '.$barcode.'.pdf');
        }

        return null;
    }

    public function downloadBarcode(Request $request)
    {
        $barcode = $request->barcode;

        if ($barcode) {
            $masterSo = PPICMasterSo::select("ppic_master_so.barcode", "master_sb_ws.ws", "master_sb_ws.styleno", "master_sb_ws.color", "master_sb_ws.size", "master_sb_ws.dest")->leftJoin("master_sb_ws", "master_sb_ws.id_so_det", "=", "ppic_master_so.id_so_det")->where("ppic_master_so.barcode", $barcode)->first();

            $pdf = PDF::loadView('ppic.barcode-packing.export.barcode-packing-pdf', ['masterSo' => $masterSo])->setPaper('A7', 'landscape');

            return $pdf->download('Barcode Packing '.$barcode.'.pdf');
        }

        return null;
    }
}
