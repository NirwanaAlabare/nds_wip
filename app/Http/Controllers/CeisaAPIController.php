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

    public function getTps(Request $request)
    {
        $kata = $request->input('q', '');
        $kodeKantor = $request->input('kodeKantor', '050500');

        $queryParam = !empty($kata) ? $kata : $kodeKantor;

        try {
            $result = $this->ceisaService->getTps($queryParam);

            $formatted = [];
            if(isset($result['data']) && is_array($result['data'])) {
                foreach($result['data'] as $item) {
                    $kode = $item['kodeTps'] ?? ($item['kode'] ?? '');
                    $nama = $item['namaTps'] ?? ($item['nama'] ?? ($item['uraian'] ?? ''));
                    if (!empty($kode)) {
                        $formatted[] = [
                            'id' => $kode,
                            'text' => $kode . ' - ' . $nama
                        ];
                    }
                }
                if (!empty($formatted)) {
                    return response()->json(['results' => $formatted]);
                }
            }

            return $this->getTpsFallback($queryParam, !empty($kata));

        } catch (\Exception $e) {
            return $this->getTpsFallback($queryParam, !empty($kata));
        }
    }

    private function getTpsFallback($kata, $isSearch = true)
    {
        // Daftar TPS lengkap se-Indonesia sebagai fallback jika API CEISA dev timeout/terkendala
        $commonTps = [
            // Tanjung Priok / Jakarta (040300)
            ['id' => 'KOJA', 'text' => 'KOJA - KSO BPK KOJA', 'kantor' => '040300'],
            ['id' => 'JICT', 'text' => 'JICT - PT JAKARTA INTERNATIONAL CONTAINER TERMINAL', 'kantor' => '040300'],
            ['id' => '3T01', 'text' => '3T01 - PT MUSTIKA ALAM LESTARI (MAL)', 'kantor' => '040300'],
            ['id' => '1T01', 'text' => '1T01 - PT PELABUHAN INDONESIA II CABANG TANJUNG PRIOK', 'kantor' => '040300'],
            ['id' => '1T02', 'text' => '1T02 - TERMINAL 3 TANJUNG PRIOK', 'kantor' => '040300'],
            ['id' => 'KJT1', 'text' => 'KJT1 - TERMINAL PETIKEMAS KOJA', 'kantor' => '040300'],
            ['id' => 'NPCT', 'text' => 'NPCT - NEW PRIOK CONTAINER TERMINAL ONE (NPCT1)', 'kantor' => '040300'],
            ['id' => 'DWKA', 'text' => 'DWKA - PT DWIPA KHARISMA MITRA TANJUNG PRIOK', 'kantor' => '040300'],
            ['id' => 'AGTP', 'text' => 'AGTP - PT AIRIN TANJUNG PRIOK', 'kantor' => '040300'],
            ['id' => 'MIPR', 'text' => 'MIPR - PT MULTI INTIPARNA TANJUNG PRIOK', 'kantor' => '040300'],

            // Soekarno-Hatta / Tangerang / Jakarta (050100)
            ['id' => 'JASA', 'text' => 'JASA - PT JASA ANGKASA SEMESTA (JAS) CARGO SOEKARNO HATTA', 'kantor' => '050100'],
            ['id' => 'GARU', 'text' => 'GARU - PT GARUDA INDONESIA CARGO SOEKARNO HATTA', 'kantor' => '050100'],
            ['id' => 'UNPA', 'text' => 'UNPA - PT UNIAIR INDOTAMA CARGO SOEKARNO HATTA', 'kantor' => '050100'],
            ['id' => 'FEDX', 'text' => 'FEDX - PT FEDERAL EXPRESS SOEKARNO HATTA', 'kantor' => '050100'],
            ['id' => 'DHLX', 'text' => 'DHLX - PT BIROTIKA SEMESTA (DHL EXPRESS) SOEKARNO HATTA', 'kantor' => '050100'],
            ['id' => 'UPSX', 'text' => 'UPSX - PT UPS CARDIG INTERNATIONAL SOEKARNO HATTA', 'kantor' => '050100'],
            ['id' => 'TNTX', 'text' => 'TNTX - PT SKYLIFT CONSOLIDATOR (TNT EXPRESS) SOEKARNO HATTA', 'kantor' => '050100'],
            ['id' => 'GAPU', 'text' => 'GAPU - PT GAPURA ANGKASA CARGO SOEKARNO HATTA', 'kantor' => '050100'],
            ['id' => 'ANGK', 'text' => 'ANGK - PT ANGKASA PURA II CARGO SOEKARNO HATTA', 'kantor' => '050100'],

            // Tanjung Perak / Surabaya (070100)
            ['id' => 'TPS1', 'text' => 'TPS1 - PT TERMINAL PETIKEMAS SURABAYA (TPS)', 'kantor' => '070100'],
            ['id' => 'BJTI', 'text' => 'BJTI - PT BERLIAN JASA TERMINAL INDONESIA', 'kantor' => '070100'],
            ['id' => 'TTL1', 'text' => 'TTL1 - PT TERMINAL TELUK LAMONG', 'kantor' => '070100'],
            ['id' => 'MTPS', 'text' => 'MTPS - PT MIRAH TERMINAL PETIKEMAS SURABAYA', 'kantor' => '070100'],
            ['id' => 'DWKS', 'text' => 'DWKS - PT DWIPA KHARISMA MITRA SURABAYA', 'kantor' => '070100'],
            ['id' => 'ISPS', 'text' => 'ISPS - PT INDOLINE SURABAYA', 'kantor' => '070100'],

            // Juanda / Sidoarjo / Surabaya (070200)
            ['id' => 'JASJ', 'text' => 'JASJ - PT JASA ANGKASA SEMESTA (JAS) CARGO JUANDA', 'kantor' => '070200'],
            ['id' => 'GAPJ', 'text' => 'GAPJ - PT GAPURA ANGKASA CARGO JUANDA', 'kantor' => '070200'],
            ['id' => 'GARJ', 'text' => 'GARJ - PT GARUDA INDONESIA CARGO JUANDA', 'kantor' => '070200'],
            ['id' => 'DHLJ', 'text' => 'DHLJ - PT BIROTIKA SEMESTA (DHL) JUANDA', 'kantor' => '070200'],

            // Tanjung Emas / Semarang (060100)
            ['id' => 'TPK2', 'text' => 'TPK2 - TERMINAL PETIKEMAS SEMARANG (TPKS)', 'kantor' => '060100'],
            ['id' => 'SRIS', 'text' => 'SRIS - PT SARI RANA INDAH SEMARANG', 'kantor' => '060100'],
            ['id' => 'DHLS', 'text' => 'DHLS - PT BIROTIKA SEMESTA SEMARANG', 'kantor' => '060100'],
            ['id' => 'GAPM', 'text' => 'GAPM - PT GAPURA ANGKASA CARGO AHMAD YANI SEMARANG', 'kantor' => '060100'],

            // Belawan / Medan (010700)
            ['id' => 'BICT', 'text' => 'BICT - BELAWAN INTERNATIONAL CONTAINER TERMINAL', 'kantor' => '010700'],
            ['id' => 'TPKB', 'text' => 'TPKB - TERMINAL PETIKEMAS BELAWAN', 'kantor' => '010700'],
            ['id' => 'BTLP', 'text' => 'BTLP - PT BELAWAN TERMINAL LOGISTIK PERSERO', 'kantor' => '010700'],

            // Kualanamu / Medan (010800)
            ['id' => 'JASK', 'text' => 'JASK - PT JASA ANGKASA SEMESTA CARGO KUALANAMU', 'kantor' => '010800'],
            ['id' => 'GAPK', 'text' => 'GAPK - PT GAPURA ANGKASA CARGO KUALANAMU', 'kantor' => '010800'],
            ['id' => 'GARK', 'text' => 'GARK - PT GARUDA INDONESIA CARGO KUALANAMU', 'kantor' => '010800'],

            // Ngurah Rai / Denpasar / Bali (080100)
            ['id' => 'JASD', 'text' => 'JASD - PT JASA ANGKASA SEMESTA CARGO NGURAH RAI', 'kantor' => '080100'],
            ['id' => 'GAPD', 'text' => 'GAPD - PT GAPURA ANGKASA CARGO NGURAH RAI', 'kantor' => '080100'],
            ['id' => 'GARD', 'text' => 'GARD - PT GARUDA INDONESIA CARGO NGURAH RAI', 'kantor' => '080100'],

            // Batam / Kepulauan Riau (020100)
            ['id' => 'BTBP', 'text' => 'BTBP - PT BATAM PERSERO BEKAS / BATU AMPAR', 'kantor' => '020100'],
            ['id' => 'BICT2', 'text' => 'BICT2 - BATAM INTERNATIONAL CONTAINER TERMINAL', 'kantor' => '020100'],
            ['id' => 'DHLB', 'text' => 'DHLB - PT BIROTIKA SEMESTA BATAM', 'kantor' => '020100'],
            ['id' => 'CGKB2', 'text' => 'CGKB2 - TPS CARGO BANDARA HANG NADIM BATAM', 'kantor' => '020100'],

            // Makassar / Sulawesi Selatan (100100)
            ['id' => 'TPKM', 'text' => 'TPKM - TERMINAL PETIKEMAS MAKASSAR (PELINDO IV)', 'kantor' => '100100'],
            ['id' => 'GAPG', 'text' => 'GAPG - PT GAPURA ANGKASA CARGO SULTAN HASANUDDIN MAKASSAR', 'kantor' => '100100'],
            ['id' => 'GARM', 'text' => 'GARM - PT GARUDA INDONESIA CARGO MAKASSAR', 'kantor' => '100100'],

            // Balikpapan / Kalimantan Timur (120100)
            ['id' => 'KKT1', 'text' => 'KKT1 - PT KALTIM KARIANGAU TERMINAL (KKT) BALIKPAPAN', 'kantor' => '120100'],
            ['id' => 'GAPB', 'text' => 'GAPB - PT GAPURA ANGKASA CARGO SEPINGGAN BALIKPAPAN', 'kantor' => '120100'],

            // Cikarang / Bekasi (050300)
            ['id' => 'CDP1', 'text' => 'CDP1 - CIKARANG DRY PORT (PT CIKARANG INLAND PORT)', 'kantor' => '050300'],
            ['id' => 'MTB1', 'text' => 'MTB1 - PT MITRA TATA BUANA CIKARANG', 'kantor' => '050300'],

            // Bandung (050500)
            ['id' => 'BDRB', 'text' => 'BDRB - PT BHANDA GHARA REKSA (BGR) GEDEBAGE BANDUNG', 'kantor' => '050500'],
            ['id' => 'GDBG', 'text' => 'GDBG - TPS GEDEBAGE BANDUNG', 'kantor' => '050500'],
            ['id' => 'PTKB', 'text' => 'PTKB - TPS PT POS INDONESIA BANDUNG', 'kantor' => '050500'],
            ['id' => 'CGKB', 'text' => 'CGKB - TPS CARGO BANDARA HUSEIN SASTRANEGARA BANDUNG', 'kantor' => '050500'],

            // Tangerang / Serpong (050200)
            ['id' => 'BSDT', 'text' => 'BSDT - TPS BSD TANGERANG KOTA', 'kantor' => '050200'],
            ['id' => 'IKGT', 'text' => 'IKGT - PT INDO KOR GUNA TANGERANG', 'kantor' => '050200'],

            // Merak / Banten (040100)
            ['id' => 'IKPT', 'text' => 'IKPT - PT INDAH KIAT PULP & PAPER MERAK BANTEN', 'kantor' => '040100'],
            ['id' => 'CMPT', 'text' => 'CMPT - PT CIWANDAN MULTI PURPOSES TERMINAL MERAK', 'kantor' => '040100'],

            // Tanjung Pinang / Kepri (020200)
            ['id' => 'TPTP', 'text' => 'TPTP - TERMINAL PETIKEMAS TANJUNG PINANG', 'kantor' => '020200'],
            ['id' => 'KIPT', 'text' => 'KIPT - KIJANG PORT TERMINAL', 'kantor' => '020200'],

            // Palembang (030100)
            ['id' => 'BMTP2', 'text' => 'BMTP2 - BOOM BARU TERMINAL PETIKEMAS PALEMBANG (PELINDO II)', 'kantor' => '030100'],
            ['id' => 'GAPP', 'text' => 'GAPP - PT GAPURA ANGKASA CARGO PALEMBANG', 'kantor' => '030100'],

            // Lampung / Panjang (030400)
            ['id' => 'TPKP', 'text' => 'TPKP - TERMINAL PETIKEMAS PANJANG (PELINDO II)', 'kantor' => '030400'],
            ['id' => 'PJPG', 'text' => 'PJPG - PELABUHAN PANJANG', 'kantor' => '030400'],

            // Pontianak (130100)
            ['id' => 'TPKN', 'text' => 'TPKN - TERMINAL PETIKEMAS PONTIANAK (PELINDO II)', 'kantor' => '130100'],
            ['id' => 'SUPN', 'text' => 'SUPN - TPS CARGO BANDARA SUPADIO PONTIANAK', 'kantor' => '130100'],

            // Banjarmasin (130300)
            ['id' => 'TPPB', 'text' => 'TPPB - TERMINAL PETIKEMAS TRISAKTI BANJARMASIN (PELINDO III)', 'kantor' => '130300'],
            ['id' => 'BDJB', 'text' => 'BDJB - TPS CARGO BANDARA SYAMSUDIN NOOR BANJARMASIN', 'kantor' => '130300'],

            // Samarinda (120200)
            ['id' => 'PSMD', 'text' => 'PSMD - PALARAN SAMARINDA CONTAINER TERMINAL (PT PSP)', 'kantor' => '120200'],

            // Bitung / Manado (110100)
            ['id' => 'TPBI', 'text' => 'TPBI - TERMINAL PETIKEMAS BITUNG (PELINDO IV)', 'kantor' => '110100'],
            ['id' => 'MDCB', 'text' => 'MDCB - TPS CARGO BANDARA SAM RATULANGI MANADO', 'kantor' => '110100'],

            // Ambon (140100)
            ['id' => 'TPKA', 'text' => 'TPKA - TERMINAL PETIKEMAS AMBON (PELINDO IV)', 'kantor' => '140100'],
            ['id' => 'AMQB', 'text' => 'AMQB - TPS CARGO BANDARA PATTIMURA AMBON', 'kantor' => '140100'],

            // Jayapura / Papua (140200)
            ['id' => 'TPKJ', 'text' => 'TPKJ - TERMINAL PETIKEMAS JAYAPURA (PELINDO IV)', 'kantor' => '140200'],
            ['id' => 'DJJB', 'text' => 'DJJB - TPS CARGO BANDARA SENTANI JAYAPURA', 'kantor' => '140200'],

            // Sorong (140400)
            ['id' => 'TPKS2', 'text' => 'TPKS2 - TERMINAL PETIKEMAS SORONG (PELINDO IV)', 'kantor' => '140400'],

            // Kupang / NTT (080300)
            ['id' => 'TPKK', 'text' => 'TPKK - TERMINAL PETIKEMAS TENAU KUPANG (PELINDO III)', 'kantor' => '080300'],

            // Mataram / Lembar / NTB (080200)
            ['id' => 'TPML', 'text' => 'TPML - TERMINAL PETIKEMAS LEMBAR MATARAM (PELINDO III)', 'kantor' => '080200'],
        ];

        $formatted = [];
        $kataUpper = strtoupper($kata);

        foreach ($commonTps as $item) {
            if (
                stripos($item['id'], $kataUpper) !== false || 
                stripos($item['text'], $kataUpper) !== false ||
                (isset($item['kantor']) && $item['kantor'] === $kataUpper)
            ) {
                $formatted[] = [
                    'id' => $item['id'],
                    'text' => $item['text']
                ];
            }
        }

        // Selalu tambahkan opsi custom dari inputan user agar tetap bisa memilih kode TPS apapun yang diketik
        if ($isSearch && strlen($kata) >= 2) {
            $formatted[] = [
                'id' => strtoupper($kata),
                'text' => strtoupper($kata) . ' - (Custom / Manual Input)'
            ];
        }

        return response()->json(['results' => $formatted]);
    }
}
