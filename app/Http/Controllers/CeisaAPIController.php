<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\CeisaService;
use Illuminate\Http\Request;

class CeisaAPIController extends Controller
{
    protected $ceisaService;

    public function __construct(CeisaService $ceisaService)
    {
        $this->ceisaService = $ceisaService;
    }

    public function testStatus()
    {
        try {

            $result = $this->ceisaService->cekStatus();
            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function testKurs($kodeKurs)
    {
        try {

            $result = $this->ceisaService->cekKurs($kodeKurs);
            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getPelabuhan(Request $request)
    {
        try {
            $kata = $request->input('q', '');
            if(strlen($kata) < 2) {
                return response()->json(['data' => []]);
            }

            $result = $this->ceisaService->getPelabuhan($kata);

            $formatted = [];
            if(isset($result['data']) && is_array($result['data'])) {
                foreach($result['data'] as $item) {
                    $formatted[] = [
                        'id' => $item['kodePelabuhan'],
                        'text' => $item['kodePelabuhan'] . ' - ' . $item['namaPelabuhan']
                    ];
                }
                return response()->json(['results' => $formatted]);
            }

            return response()->json(['results' => []]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
