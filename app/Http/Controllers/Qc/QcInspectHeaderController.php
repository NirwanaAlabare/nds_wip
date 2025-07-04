<?php

namespace App\Http\Controllers\Qc;

use App\Http\Controllers\Controller;
use App\Models\qc\inspect\QcInspectHeader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QcInspectHeaderController extends Controller
{
    public function index()
    {
        return view('qc.inspect.index', ['page' => 'dashboard-warehouse'])
            ->with('pageTitle', 'Data QC Inspection Fabric');
    }
    public function generateRoll()
    {
        return view('qc.inspect.generateRoll', ['page' => 'dashboard-warehouse'])
            ->with('pageTitle', 'Generate Roll QC Inspection Fabric');
    }

    public function getDatatables(Request $request)
    {
        // Set default date range if not provided
        $tgl_awal = $request->input('tgl_awal', now()->subDays(30)->format('Y-m-d'));
        $tgl_akhir = $request->input('tgl_akhir', now()->format('Y-m-d'));

        // Validate date inputs
        if (!strtotime($tgl_awal) || !strtotime($tgl_akhir)) {
            return response()->json(['error' => 'Invalid date format'], 400);
        }

        $data = QcInspectHeader::with('imaterialBarcode')
            ->whereBetween('tgl_pl', [$tgl_awal, $tgl_akhir])
            ->orderBy('tgl_pl', 'desc')
            ->get();
    // dd("data", $data);   

        return datatables()->of($data)
            ->addIndexColumn()

            ->rawColumns(['action'])
            ->make(true);
    }
   public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'id_whs_lokasi_inmaterial' => 'required|integer',
                'id_item' => 'required|integer',
                'tgl_pl' => 'required|date',
                'no_dok' => 'required|string',
                'no_pl' => 'required|string',
                'no_lot' => 'required|string',
                'color' => 'required|string',
                'supplier' => 'required|string',
                'buyer' => 'required|string',
                'style' => 'required|string',
                'qty_roll' => 'required|integer',
                'notes' => 'nullable|string',
            ]);

            $inspection = QcInspectHeader::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Inspection created successfully',
                'data' => $inspection,
                // 'redirect_url' => route('qc-inspect-inmaterial.detail', $inspection->id)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getDataRolltables(Request $request)
    {
        $no_pl = $request->no_pl;
        $no_lot = $request->no_lot;
        $id_item = $request->id_item;

        $data = DB::connection('mysql_sb')->select("
            SELECT no_roll, no_barcode, b.itemdesc, b.color
            FROM `whs_lokasi_inmaterial` a
            JOIN masteritem b ON b.id_item = a.id_item
            JOIN whs_inmaterial_fabric c ON c.no_dok = a.no_dok
            WHERE 
            c.no_invoice = ? AND
            a.no_lot = ? AND
            a.id_item = ?
            ORDER BY no_roll ASC
        ", [$no_pl, $no_lot, $id_item]);

        return datatables()->of($data)
            ->addIndexColumn()
            ->make(true);
    }
}
