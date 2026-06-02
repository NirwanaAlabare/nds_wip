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
        $kata = $request->input('q', '');
        try {
            if(strlen($kata) < 2) {
                return response()->json(['results' => []]);
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

            // Jika API sukses terhubung tetapi mengembalikan error internal / exception dari CEISA
            if (isset($result['Exception']) || empty($result['data'])) {
                return $this->getPelabuhanFallback($kata);
            }

            return response()->json(['results' => []]);

        } catch (\Exception $e) {
            // Jika API timeout / error koneksi, fallback ke list lokal
            return $this->getPelabuhanFallback($kata);
        }
    }

    /**
     * Fallback data pelabuhan dari UN/LOCODE (17.000+ pelabuhan seluruh dunia)
     * Digunakan ketika API CEISA dev mengalami kendala (timeout / Code 408)
     */
    private function getPelabuhanFallback($kata)
    {
        $jsonFile = database_path('data/world_ports.json');

        if (!file_exists($jsonFile)) {
            // Jika file JSON belum ada, kembalikan opsi custom saja
            return response()->json(['results' => [
                ['id' => strtoupper($kata), 'text' => strtoupper($kata) . ' - (Custom / Manual Input)']
            ]]);
        }

        // Load JSON (di-cache di memori PHP per-request, tidak re-read berkali-kali)
        $allPorts = json_decode(file_get_contents($jsonFile), true);

        if (!is_array($allPorts)) {
            return response()->json(['results' => []]);
        }

        $formatted  = [];
        $kataUpper  = strtoupper($kata);
        $maxResults = 25;

        foreach ($allPorts as $item) {
            if (count($formatted) >= $maxResults) break;

            $kode = $item['k'] ?? '';
            $nama = $item['n'] ?? '';

            if (
                stripos($kode, $kataUpper) !== false ||
                stripos($nama, $kataUpper) !== false
            ) {
                $formatted[] = [
                    'id'   => $kode,
                    'text' => $kode . ' - ' . $nama
                ];
            }
        }

        // Jika tidak ada yang cocok, berikan opsi custom agar form bisa disubmit
        if (empty($formatted) && strlen($kata) >= 2) {
            $formatted[] = [
                'id'   => strtoupper($kata),
                'text' => strtoupper($kata) . ' - (Custom / Manual Input)'
            ];
        }

        return response()->json(['results' => $formatted]);
    }
}
