<?php

namespace App\Http\Controllers\Qc;

use App\Http\Controllers\Controller;
use App\Models\qc\inspect\QcInspectHeader;
use Illuminate\Http\Request;

class QcInspectHeaderController extends Controller
{
   public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'id_whs_lokasi_inmaterial' => 'required|integer',
                'tgl_pl' => 'required|date',
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
}
