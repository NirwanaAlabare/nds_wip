<?php

namespace App\Http\Controllers\Qc;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\qc\inspect\QcInspectDetail;


class QcInspectDetailController extends Controller
{
     public function store(Request $request)
    {
        try {
           $validated = $request->validate([
                'id_master_group_inspect' => 'required|integer',
                'id_inspect_list_header' => 'required|integer',
                'rata_rata' => 'nullable|numeric|regex:/^\d+(\.\d{1,2})?$/',
                'percentage' => 'nullable|numeric|regex:/^\d+(\.\d{1,2})?$/',
                'result' => 'nullable|string',
            ]);

            $inspectionDetail = QcInspectDetail::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Inspection Detail created successfully',
                'data' => $inspectionDetail,
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
