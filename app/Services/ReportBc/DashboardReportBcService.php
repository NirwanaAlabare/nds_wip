<?php

namespace App\Services\ReportBc;

use Carbon\Carbon;

class DashboardReportBcService
{
    protected array $mapNilai = [
        'BC 23'        => 'BC 2.3',
        'BC 27 In'     => 'BC 2.7',
        'BC 27 Out'    => 'BC 2.7 OUT',
        'BC 30'        => 'BC 3.0',
        'BC 41'        => ['BC 4.1 SEWA', 'BC 4.1 SUBKON', 'BC 4.1 LOKAL'],
        'BC 25 FG'     => 'BC 2.5 FG',
        'BC 25 Scrap'  => 'BC 2.5 SCRAP',
    ];

    protected array $dokumenSourceMap = [
        'BC 23'     => ['pemasukan' => 'BC 2.3'],
        'BC 30'     => ['pengeluaran' => 'BC 3.0'],
        'BC 33'     => ['pengeluaran' => 'BC 3.3'],
        'BC 40'     => ['pemasukan' => 'BC 4.0'],
        'BC 41'     => ['pemasukan' => 'BC 4.1', 'pengeluaran' => ['BC 4.1 SEWA', 'BC 4.1 SUBKON', 'BC 4.1 LOKAL']],
        'BC 25 FG'  => ['pengeluaran' => 'BC 2.5 FG'],
        'BC 25 Scrap'=> ['pengeluaran' => 'BC 2.5 SCRAP'],
        'BC 261'    => ['pengeluaran' => ['BC 2.6.1 KELUAR', 'BC 2.6.1']],
        'BC 262'    => ['pemasukan' => 'BC 2.6.2'],
        'BC 27 In'  => ['pemasukan' => 'BC 2.7'],
        'BC 27 Out' => ['pengeluaran' => 'BC 2.7'],
    ];

    protected $pemasukanService;
    protected $pengeluaranService;

    public function __construct(
        PemasukanService $pemasukanService,
        PengeluaranService $pengeluaranService
    ) {
        $this->pemasukanService = $pemasukanService;
        $this->pengeluaranService = $pengeluaranService;
    }

    public function getSummary(): array
    {
        $today = Carbon::today();

        $periodeYtd = [
            'label' => 'Januari - Saat Ini',
            'from'  => $today->copy()->startOfYear()->toDateString(),
            'to'    => $today->toDateString(),
        ];

        $periodeBulan = [
            'label' => 'Periode Bulan Ini',
            'from'  => $today->copy()->startOfMonth()->toDateString(),
            'to'    => $today->toDateString(),
        ];

        $rawYtd   = $this->get_raw($periodeYtd['from'], $periodeYtd['to']);
        $rawBulan = $this->get_raw($periodeBulan['from'], $periodeBulan['to']);

        return [
            'periode' => [
                'ytd'   => $periodeYtd,
                'bulan' => $periodeBulan,
            ],
            'nilai' => [
                'ytd'   => $this->sum_nilai($rawYtd),
                'bulan' => $this->sum_nilai($rawBulan),
            ],
            'dokumen' => [
                'ytd'   => $this->sum_dokumen($rawYtd),
                'bulan' => $this->sum_dokumen($rawBulan),
            ],
            'penangguhan_bc23' => [
                'ytd'   => ['bea_masuk' => 0, 'bmt' => 0, 'ppn' => 0, 'pph' => 0],
                'bulan' => ['bea_masuk' => 0, 'bmt' => 0, 'ppn' => 0, 'pph' => 0],
            ],
        ];
    }

    protected function get_raw(string $from, string $to): array
    {
        return [
            'pemasukan'   => $this->pemasukanService->getData($from, $to),
            'pengeluaran' => $this->pengeluaranService->getData($from, $to),
        ];
    }

    protected function merge_group($groupedCollection, $keys)
    {
        $keys = is_array($keys) ? $keys : [$keys];
        $merged = collect();

        foreach ($keys as $key) {
            $merged = $merged->merge($groupedCollection->get($key, collect()));
        }

        return $merged;
    }

    protected function sum_nilai(array $raw): array
    {
        $pemasukanGroup   = collect($raw['pemasukan'])->groupBy('jenis_dokumen');
        $pengeluaranGroup = collect($raw['pengeluaran'])->groupBy('jenis_dokumen');

        $nilaiSourceByLabel = [
            'BC 23'       => $pemasukanGroup,
            'BC 27 In'    => $pemasukanGroup,
            'BC 27 Out'   => $pengeluaranGroup,
            'BC 30'       => $pengeluaranGroup,
            'BC 41'       => $pengeluaranGroup,
            'BC 25 FG'    => $pengeluaranGroup,
            'BC 25 Scrap' => $pengeluaranGroup,
        ];

        $result = [];
        foreach ($this->mapNilai as $label => $jenisDokumen) {
            $group = $nilaiSourceByLabel[$label] ?? collect();
            $result[$label] = (float) $this->merge_group($group, $jenisDokumen)->sum('nilai_barang_idr');
        }

        return $result;
    }

    protected function sum_dokumen(array $raw): array
    {
        $pemasukanGroup   = collect($raw['pemasukan'])->groupBy('jenis_dokumen');
        $pengeluaranGroup = collect($raw['pengeluaran'])->groupBy('jenis_dokumen');

        $result = [];
        foreach ($this->dokumenSourceMap as $label => $sources) {
            $count = 0;

            if (isset($sources['pemasukan'])) {
                $count += $this->merge_group($pemasukanGroup, $sources['pemasukan'])
                    ->pluck('nomor_bpb')->unique()->count();
            }

            if (isset($sources['pengeluaran'])) {
                $count += $this->merge_group($pengeluaranGroup, $sources['pengeluaran'])
                    ->pluck('nomor_bpb')->unique()->count();
            }

            $result[$label] = $count;
        }

        return $result;
    }
}
