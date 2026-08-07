<?php

namespace App\Services\ReportBc;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

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
        'BC 25 FG'    => ['pengeluaran' => 'BC 2.5 FG'],
        'BC 25 Scrap' => ['pengeluaran' => 'BC 2.5 SCRAP'],
        'BC 261'    => ['pengeluaran' => ['BC 2.6.1 KELUAR', 'BC 2.6.1']],
        'BC 262'    => ['pemasukan' => 'BC 2.6.2'],
        'BC 27 In'  => ['pemasukan' => 'BC 2.7'],
        'BC 27 Out' => ['pengeluaran' => 'BC 2.7'],
    ];

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
            'pemasukan'   => $this->query_pemasukan($from, $to),
            'pengeluaran' => $this->query_pengeluaran($from, $to),
        ];
    }

    protected function query_pemasukan(string $fromDate, string $toDate)
    {
        $dateField = 'a.bcdate';
        $mysql_sb  = DB::connection('mysql_sb');

        $caseJenisDokumen = "
            CASE
                WHEN a.jenis_dok = '2.3' AND a.invno LIKE '%PJT%' THEN 'BC 2.3 IMPOR PJT'
                WHEN a.jenis_dok = '2.3' AND a.invno NOT LIKE '%PJT%' AND a.invno NOT LIKE '%PIB%' AND a.invno NOT LIKE '%PIBK%' THEN 'BC 2.3'
                WHEN a.jenis_dok = '2.6.2' THEN 'BC 2.6.2'
                WHEN a.jenis_dok = '2.7' THEN 'BC 2.7'
                WHEN a.jenis_dok = '4.0' AND UPPER(a.invno) NOT LIKE '%SEWA%' AND UPPER(a.tujuan) NOT LIKE '%SUBKON%' THEN 'BC 4.0'
                WHEN a.jenis_dok = '4.0' AND UPPER(a.invno) LIKE '%SEWA%' THEN 'BC 4.0 (SEWA)'
                WHEN a.jenis_dok = '4.0' AND UPPER(a.invno) NOT LIKE '%SEWA%' AND UPPER(a.tujuan) LIKE '%SUBKON%' THEN 'BC 4.0 SUBKON'
                WHEN d.area = 'I' AND a.invno LIKE '%PIB%' AND a.invno NOT LIKE '%PIBK%' THEN 'BC 2.0 IMPOR PIB'
                WHEN d.area = 'I' AND a.invno LIKE '%PIBK%' THEN 'BC 2.1 IMPOR PIBK'
                WHEN d.status_kb = 'KITTE' AND d.area = 'L' THEN 'BC 2.4 KITTE'
                ELSE __ELSE_RULE__
            END
        ";

        $selectData = fn ($jenisDokElse, $bcdateExpr, $kodeBrgExpr, $itemdescExpr, $matclassExpr) => [
            DB::raw(str_replace('__ELSE_RULE__', $jenisDokElse, $caseJenisDokumen) . " as jenis_dokumen"),
            DB::raw("LPAD(a.bcno, 6, '0') as bcno"),
            DB::raw("$bcdateExpr as bcdate"),
            DB::raw("IF(a.bpbno_int != '', a.bpbno_int, a.bpbno) as trans_no"),
            'a.bpbdate',
            'd.supplier',
            DB::raw("$kodeBrgExpr as kode_brg"),
            DB::raw("$itemdescExpr as itemdesc"),
            'a.unit',
            DB::raw("SUM(a.qty) as qty"),
            DB::raw("IFNULL(NULLIF(TRIM(a.curr_bc), ''), a.curr) as curr"),
            DB::raw("ROUND(SUM(IFNULL(NULLIF(TRIM(a.price_bc), ''), a.price) * a.qty), 2) as nilai_barang"),
            'a.berat_bersih',
            'a.berat_kotor',
            DB::raw("RIGHT(a.nomor_aju, 6) as nomor_aju"),
            'a.tujuan',
            'a.id_item',
            DB::raw("$matclassExpr as matclass"),
        ];

        $queryBahanBaku = $mysql_sb->table('bpb as a')
            ->join('masteritem as s', 'a.id_item', '=', 's.id_item')
            ->join('mastersupplier as d', 'a.id_supplier', '=', 'd.id_supplier')
            ->where('a.cancel', 'N')
            ->where('a.jenis_dok', '!=', 'INHOUSE')
            ->where('a.bpbno', 'not like', 'FG%')
            ->whereBetween($dateField, [$fromDate, $toDate])
            ->select($selectData(
                'a.jenis_dok',
                "IF(a.bcdate IS NULL OR a.bcdate = '0000-00-00', a.bpbdate, a.bcdate)",
                "IF(s.goods_code = '' OR s.goods_code = '-' OR s.goods_code = '0', CONCAT(s.mattype, ' ', a.id_item), s.goods_code)",
                "CONCAT_WS(' ', s.itemdesc, s.color, s.size, s.add_info)",
                's.matclass'
            ))
            ->groupBy('a.bcno', 'a.bpbno', 'a.id_item', 'a.price');

        $queryBarangJadi = $mysql_sb->table('bpb as a')
            ->join('masterstyle as s', 'a.id_item', '=', 's.id_item')
            ->join('mastersupplier as d', 'a.id_supplier', '=', 'd.id_supplier')
            ->where('a.cancel', 'N')
            ->where('a.jenis_dok', '!=', 'INHOUSE')
            ->where('a.bpbno', 'like', 'FG%')
            ->whereBetween($dateField, [$fromDate, $toDate])
            ->select($selectData(
                "'N/A'",
                'a.bcdate',
                "IF(s.goods_code = '' OR s.goods_code = '-' OR s.goods_code = '0', CONCAT('FG ', a.id_item), s.goods_code)",
                's.itemname',
                "'BARANG JADI'"
            ))
            ->groupBy('a.bcno', 'a.bpbno', 'a.id_item', 'a.price');

        $unionQuery = $queryBahanBaku->unionAll($queryBarangJadi);

        $rateSubQuery = $mysql_sb->table('masterrate')
            ->select('tanggal', 'curr', 'rate')
            ->whereRaw("TRIM(UPPER(v_codecurr)) = 'PAJAK'")
            ->groupBy('tanggal', 'curr');

        return $mysql_sb->table(DB::raw("({$unionQuery->toSql()}) as a"))
            ->mergeBindings($unionQuery)
            ->leftJoinSub($rateSubQuery, 'mr', function ($join) {
                $join->on('mr.tanggal', '=', 'a.bcdate')
                    ->on('mr.curr', '=', 'a.curr');
            })
            ->select(
                'a.jenis_dokumen',
                'a.matclass as kategori_barang',
                'a.bcno as nomor_daftar',
                'a.bcdate as tanggal_daftar',
                'a.trans_no as nomor_bpb',
                'a.curr as kode_valuta',
                'a.nilai_barang',
                DB::raw('COALESCE(mr.rate, 1) as kurs'),
                DB::raw('(a.nilai_barang * COALESCE(mr.rate, 1)) as nilai_barang_idr')
            )
            ->get();
    }

    protected function query_pengeluaran(string $fromDate, string $toDate)
    {
        $dateField = 'a.bcdate';
        $mysql_sb  = DB::connection('mysql_sb');

        $caseJenisDokumen = "
            CASE
                WHEN a.jenis_dok = 'BC 3.0' THEN 'BC 3.0'
                WHEN a.jenis_dok = 'BC 2.6.1' AND a.bcno != '-' THEN 'BC 2.6.1 KELUAR'
                WHEN a.jenis_dok = 'BC 2.7' AND a.tujuan NOT IN ('DIKEMBALIKAN', 'DISUBKONTRAKKAN') THEN 'BC 2.7 OUT'
                WHEN a.jenis_dok = 'BC 2.5' AND SUBSTRING(a.bppbno, 4, 2) = 'FG' THEN 'BC 2.5 FG'
                WHEN a.jenis_dok = 'BC 2.5' THEN 'BC 2.5 SCRAP'
                WHEN a.jenis_dok = 'BC 3.3' THEN 'BC 3.3'
                WHEN a.jenis_dok = 'BC 4.1' AND UPPER(a.remark) LIKE '%SEWA%' THEN 'BC 4.1 SEWA'
                WHEN a.jenis_dok = 'BC 4.1' AND UPPER(a.tujuan) LIKE '%SUBKON%' THEN 'BC 4.1 SUBKON'
                WHEN a.jenis_dok = 'BC 4.1' THEN 'BC 4.1 LOKAL'
                ELSE a.jenis_dok
            END
        ";

        $selectData = fn ($kodeBrgExpr, $itemdescExpr, $idItemExpr, $matclassExpr) => [
            DB::raw("$caseJenisDokumen as jenis_dokumen"),
            DB::raw("LPAD(a.bcno, 6, '0') as bcno"),
            'a.bcdate',
            DB::raw("IF(a.bppbno_int != '', a.bppbno_int, a.bppbno) as trans_no"),
            'a.bppbdate',
            'd.supplier',
            DB::raw("$kodeBrgExpr as kode_brg"),
            DB::raw("$itemdescExpr as itemdesc"),
            'a.unit',
            DB::raw("SUM(a.qty) as qty"),
            DB::raw("IFNULL(NULLIF(TRIM(a.curr_bc), ''), a.curr) as curr"),
            DB::raw("ROUND(SUM(a.qty * IFNULL(NULLIF(TRIM(a.price_bc), ''), a.price)), 2) as nilai_barang"),
            DB::raw("$idItemExpr as id_item"),
            DB::raw("$matclassExpr as matclass"),
        ];

        $queryBahanBaku = $mysql_sb->table('bppb as a')
            ->join('masteritem as s', 'a.id_item', '=', 's.id_item')
            ->join('mastersupplier as d', 'a.id_supplier', '=', 'd.id_supplier')
            ->where('a.jenis_dok', '!=', 'INHOUSE')
            ->where(function ($query) {
                $query->where('a.jenis_dok', '!=', 'BC 2.7')
                    ->orWhereNotIn('a.tujuan', ['DIKEMBALIKAN', 'DISUBKONTRAKKAN']);
            })
            ->whereRaw("SUBSTRING(a.bppbno, 4, 2) != 'FG'")
            ->whereRaw("a.cancel != 'Y'")
            ->whereBetween($dateField, [$fromDate, $toDate])
            ->select($selectData(
                "IF(s.goods_code != '' AND s.goods_code != '-' AND s.goods_code != '0', s.goods_code, CONCAT(s.mattype, s.id_item))",
                's.itemdesc',
                'a.id_item',
                's.matclass'
            ))
            ->groupBy('a.bcno', 'a.bppbno', 'a.id_item', 'a.price', 'a.jenis_dok', 'a.remark', 'a.tujuan');

        $queryBarangJadi = $mysql_sb->table('bppb as a')
            ->join('masterstyle as s', 'a.id_item', '=', 's.id_item')
            ->join('mastersupplier as d', 'a.id_supplier', '=', 'd.id_supplier')
            ->where('a.jenis_dok', '!=', 'INHOUSE')
            ->where(function ($query) {
                $query->where('a.jenis_dok', '!=', 'BC 2.7')
                    ->orWhereNotIn('a.tujuan', ['DIKEMBALIKAN', 'DISUBKONTRAKKAN']);
            })
            ->whereRaw("SUBSTRING(a.bppbno, 4, 2) = 'FG'")
            ->whereRaw("a.cancel != 'Y'")
            ->whereBetween($dateField, [$fromDate, $toDate])
            ->select($selectData(
                "IF(s.goods_code != '' AND s.goods_code != '-' AND s.goods_code != '0', s.goods_code, CONCAT('FG ', s.id_item))",
                's.itemname',
                's.id_so_det',
                "'BARANG JADI'"
            ))
            ->groupBy('a.bcno', 'a.bppbno', 'a.id_item', 'a.price', 'a.jenis_dok', 'a.remark', 'a.tujuan');

        $unionQuery = $queryBahanBaku->unionAll($queryBarangJadi);

        $rateSubQuery = $mysql_sb->table('masterrate')
            ->select('tanggal', 'curr', 'rate')
            ->whereRaw("TRIM(UPPER(v_codecurr)) = 'PAJAK'")
            ->groupBy('tanggal', 'curr');

        return $mysql_sb->table(DB::raw("({$unionQuery->toSql()}) as a"))
            ->mergeBindings($unionQuery)
            ->leftJoinSub($rateSubQuery, 'mr', function ($join) {
                $join->on('mr.tanggal', '=', 'a.bcdate')
                    ->on('mr.curr', '=', 'a.curr');
            })
            ->select(
                'a.jenis_dokumen',
                'a.matclass as kategori_barang',
                'a.bcno as nomor_daftar',
                'a.bcdate as tanggal_daftar',
                'a.trans_no as nomor_bpb',
                'a.curr as kode_valuta',
                'a.nilai_barang',
                DB::raw('COALESCE(mr.rate, 1) as kurs'),
                DB::raw('(a.nilai_barang * COALESCE(mr.rate, 1)) as nilai_barang_idr')
            )
            ->get();
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
