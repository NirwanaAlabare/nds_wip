<?php

namespace App\Services;

use App\Http\Controllers\LapDetPemasukanController;
use App\Http\Controllers\LapDetPemasukanRollController;
use App\Http\Controllers\LapDetPengeluaranController;
use App\Http\Controllers\LapDetPengeluaranRollController;
use App\Http\Controllers\LapMutasiBarcodeController;
use App\Http\Controllers\LapMutasiDetailController;
use App\Http\Controllers\LapMutasiGlobalController;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

// Rekonsiliasi qty IN/OUT antar 7 laporan fabric warehouse.
//
// Angka tiap laporan diambil dari SQL export Excel-nya (exportSql) dan dibulatkan 2 desimal per baris
// seperti saat ditulis ke Excel, jadi selalu sama dengan file export. Selisih antar laporan dicari dengan
// membandingkan baris export pada kunci yang sama (dokumen, item, barcode), sehingga jumlah selisih yang
// ditampilkan selalu sama persis dengan selisih totalnya.
//
// Export untuk rentang tanggal panjang bisa lama, jadi hasil tiap laporan disimpan di cache per periode
// dan dihitung per laporan secara terpisah.
class RekonsiliasiMutasiService
{
    const CACHE_TTL = 21600; // 6 jam
    const LOCK_TTL = 1800;
    // Naikkan bila isi hasil hitungan berubah, supaya cache lama tidak terpakai
    const CACHE_VERSION = 2;

    // groups = pengelompokan yang dibutuhkan untuk perbandingan (lihat edges())
    public static function reports()
    {
        return [
            'pdi' => ['label' => 'Pemasukan Detail Item', 'controller' => LapDetPemasukanController::class, 'in' => 'qty', 'out' => null, 'unit' => 'unit', 'doc' => 'bpbno', 'barcode' => null, 'saldo' => false, 'groups' => ['doc', 'item']],
            'pdr' => ['label' => 'Pemasukan Detail Roll', 'controller' => LapDetPemasukanRollController::class, 'in' => 'qty', 'out' => null, 'unit' => 'satuan', 'doc' => 'no_dok', 'barcode' => 'barcode', 'saldo' => false, 'groups' => ['doc', 'barcode']],
            'kdi' => ['label' => 'Pengeluaran Detail Item', 'controller' => LapDetPengeluaranController::class, 'in' => null, 'out' => 'qty', 'unit' => 'unit', 'doc' => 'bppbno', 'barcode' => null, 'saldo' => false, 'groups' => ['doc', 'item']],
            'kdr' => ['label' => 'Pengeluaran Detail Roll', 'controller' => LapDetPengeluaranRollController::class, 'in' => null, 'out' => 'qty_out', 'unit' => 'unit', 'doc' => 'no_bppb', 'barcode' => 'no_barcode', 'saldo' => false, 'groups' => ['doc', 'barcode']],
            'mg' => ['label' => 'Mutasi Global', 'controller' => LapMutasiGlobalController::class, 'in' => 'qty_in', 'out' => 'qty_out', 'unit' => 'unit', 'doc' => null, 'barcode' => null, 'saldo' => true, 'groups' => ['item']],
            'md' => ['label' => 'Mutasi Detail', 'controller' => LapMutasiDetailController::class, 'in' => 'qty_in', 'out' => 'qty_out', 'unit' => 'satuan', 'doc' => null, 'barcode' => null, 'saldo' => true, 'groups' => ['jo']],
            'mb' => ['label' => 'Mutasi Barcode', 'controller' => LapMutasiBarcodeController::class, 'in' => 'qty_in', 'out' => 'qty_out', 'unit' => 'satuan', 'doc' => null, 'barcode' => 'no_barcode', 'saldo' => true, 'groups' => ['barcode', 'jo']],
        ];
    }

    // Pasangan laporan yang dibandingkan. a dan b harus punya group yang sama.
    public static function edges()
    {
        return [
            ['id' => 'in_item_roll', 'jenis' => 'in', 'a' => 'pdi', 'b' => 'pdr', 'group' => 'doc',
                'judul' => 'IN: Detail Item vs Detail Roll',
                'penjelasan' => 'Dibandingkan per dokumen & item. Qty item lebih besar dari qty roll berarti ada roll yang belum di-upload lokasi/barcode.'],
            ['id' => 'in_item_global', 'jenis' => 'in', 'a' => 'pdi', 'b' => 'mg', 'group' => 'item',
                'judul' => 'IN: Detail Item vs Mutasi Global',
                'penjelasan' => 'Dibandingkan per item & satuan. Mutasi Global hanya menghitung qty good (tanpa reject), status detail, dan tanggal detail.'],
            ['id' => 'in_roll_barcode', 'jenis' => 'in', 'a' => 'pdr', 'b' => 'mb', 'group' => 'barcode',
                'judul' => 'IN: Detail Roll vs Mutasi Barcode',
                'penjelasan' => 'Dibandingkan per barcode. Mutasi Barcode tidak menghitung dokumen Cancel dan JO yang tidak punya buyer.'],
            ['id' => 'in_barcode_detail', 'jenis' => 'in', 'a' => 'mb', 'b' => 'md', 'group' => 'jo',
                'judul' => 'IN: Mutasi Barcode vs Mutasi Detail',
                'penjelasan' => 'Dibandingkan per item, JO & satuan.'],
            ['id' => 'out_item_roll', 'jenis' => 'out', 'a' => 'kdi', 'b' => 'kdr', 'group' => 'doc',
                'judul' => 'OUT: Detail Item vs Detail Roll',
                'penjelasan' => 'Dibandingkan per dokumen & item. Selisih biasanya karena baris roll dobel (Detail Roll menggabungkan baris yang persis sama).'],
            ['id' => 'out_item_global', 'jenis' => 'out', 'a' => 'kdi', 'b' => 'mg', 'group' => 'item',
                'judul' => 'OUT: Detail Item vs Mutasi Global',
                'penjelasan' => 'Dibandingkan per item & satuan. Mutasi Global membuang OUT yang satuannya tidak pernah dipakai saat IN.'],
            ['id' => 'out_roll_barcode', 'jenis' => 'out', 'a' => 'kdr', 'b' => 'mb', 'group' => 'barcode',
                'judul' => 'OUT: Detail Roll vs Mutasi Barcode',
                'penjelasan' => 'Dibandingkan per barcode. Mutasi Barcode tidak menghitung OUT yang lokasinya tidak cocok dengan lokasi IN.'],
            ['id' => 'out_barcode_detail', 'jenis' => 'out', 'a' => 'mb', 'b' => 'md', 'group' => 'jo',
                'judul' => 'OUT: Mutasi Barcode vs Mutasi Detail',
                'penjelasan' => 'Dibandingkan per item, JO & satuan.'],
        ];
    }

    public static function cacheKey($report, $from, $to)
    {
        return 'rekonsiliasi_mutasi:v' . self::CACHE_VERSION . ":$report:$from:$to";
    }

    public static function getProfile($report, $from, $to)
    {
        return Cache::get(self::cacheKey($report, $from, $to));
    }

    // Hitung satu laporan (atau pakai cache). Return ['status' => 'done'|'running', 'profile' => ...].
    public static function hitung($report, $from, $to, $refresh = false)
    {
        if (!$refresh && ($profile = self::getProfile($report, $from, $to))) {
            return ['status' => 'done', 'profile' => $profile];
        }

        $lock = Cache::lock("rekonsiliasi_mutasi_lock:$report:$from:$to", self::LOCK_TTL);
        if (!$lock->get()) {
            return ['status' => 'running'];
        }

        try {
            // Buang hasil lama dulu, supaya status() tidak menganggap hasil lama sebagai hasil hitung ulang
            Cache::forget(self::cacheKey($report, $from, $to));

            $profile = self::computeProfile($report, $from, $to);
            Cache::put(self::cacheKey($report, $from, $to), $profile, self::CACHE_TTL);

            return ['status' => 'done', 'profile' => $profile];
        } finally {
            $lock->release();
        }
    }

    public static function isRunning($report, $from, $to)
    {
        $lock = Cache::lock("rekonsiliasi_mutasi_lock:$report:$from:$to", self::LOCK_TTL);
        if ($lock->get()) {
            $lock->release();

            return false;
        }

        return true;
    }

    public static function computeProfile($report, $from, $to)
    {
        $def = self::reports()[$report];
        $started = microtime(true);
        $rows = DB::connection('mysql_sb')->select(app($def['controller'])->exportSql($from, $to));

        $blank = ['in' => 0.0, 'out' => 0.0, 'sal_awal' => 0.0, 'sal_akhir' => 0.0];
        $total = $blank;
        $perUnit = [];
        $groups = array_fill_keys($def['groups'], []);
        $negatif = [];

        foreach ($rows as $row) {
            $r = (array) $row;
            // Sama seperti export: round(nilai, 2) per baris
            $in = $def['in'] ? round((float) ($r[$def['in']] ?? 0), 2) : 0.0;
            $out = $def['out'] ? round((float) ($r[$def['out']] ?? 0), 2) : 0.0;
            $salAwal = $def['saldo'] ? round((float) ($r['sal_awal'] ?? 0), 2) : 0.0;
            $salAkhir = $def['saldo'] ? round((float) ($r['sal_akhir'] ?? 0), 2) : 0.0;
            $unit = (string) ($r[$def['unit']] ?? '');
            $idItem = (string) ($r['id_item'] ?? '');
            $idJo = (string) ($r['id_jo'] ?? '');

            foreach (['in' => $in, 'out' => $out, 'sal_awal' => $salAwal, 'sal_akhir' => $salAkhir] as $k => $v) {
                $total[$k] += $v;
                $perUnit[$unit][$k] = ($perUnit[$unit][$k] ?? 0) + $v;
            }

            foreach ($def['groups'] as $group) {
                if ($group === 'doc') {
                    $key = ($r[$def['doc']] ?? '') . '|' . $idItem . '|' . $idJo;
                    $meta = ['doc' => (string) ($r[$def['doc']] ?? ''), 'id_item' => $idItem, 'id_jo' => $idJo, 'unit' => $unit];
                } elseif ($group === 'item') {
                    $key = $idItem . '|' . $unit;
                    $meta = ['id_item' => $idItem, 'unit' => $unit];
                } elseif ($group === 'barcode') {
                    $key = (string) ($r[$def['barcode']] ?? '');
                    $meta = ['barcode' => $key, 'id_item' => $idItem, 'unit' => $unit];
                } else { // jo
                    $key = $idItem . '|' . $idJo . '|' . $unit;
                    $meta = ['id_item' => $idItem, 'id_jo' => $idJo, 'unit' => $unit];
                }

                if (!isset($groups[$group][$key])) {
                    $groups[$group][$key] = $meta + ['in' => 0.0, 'out' => 0.0, 'n' => 0];
                }
                $groups[$group][$key]['in'] += $in;
                $groups[$group][$key]['out'] += $out;
                $groups[$group][$key]['n']++;

                // Catat dokumen penyusun tiap item/barcode, supaya selisihnya bisa ditelusuri ke dokumen
                if ($group !== 'doc' && $def['doc']) {
                    $docNo = (string) ($r[$def['doc']] ?? '');
                    $groups[$group][$key]['docs'][$docNo] = ($groups[$group][$key]['docs'][$docNo] ?? 0) + ($def['in'] ? $in : $out);
                }
            }

            // Mutasi Barcode: saldo akhir minus = roll keluar melebihi stok
            if ($report === 'mb' && $salAkhir < 0) {
                $negatif[] = [
                    'barcode' => (string) ($r['no_barcode'] ?? ''),
                    'kode_lok' => (string) ($r['kode_lok'] ?? ''),
                    'no_dok' => (string) ($r['no_dok'] ?? ''),
                    'id_item' => $idItem,
                    'id_jo' => $idJo,
                    'unit' => $unit,
                    'sal_awal' => $salAwal,
                    'qty_in' => $in,
                    'qty_out' => $out,
                    'sal_akhir' => $salAkhir,
                ];
            }
        }

        $round = fn($arr) => array_map(fn($v) => round($v, 2), $arr);
        $perUnit = array_map($round, $perUnit);
        ksort($perUnit);

        return [
            'report' => $report,
            'from' => $from,
            'to' => $to,
            'rows' => count($rows),
            'seconds' => round(microtime(true) - $started, 1),
            'computed_at' => now()->format('Y-m-d H:i:s'),
            'total' => $round($total),
            'per_unit' => $perUnit,
            'groups' => $groups,
            'negatif' => $negatif,
        ];
    }

    // Gabungkan hasil 7 laporan menjadi ringkasan + daftar selisih. Null bila ada laporan yang belum dihitung.
    public static function hasil($from, $to)
    {
        $profiles = [];
        $missing = [];
        foreach (array_keys(self::reports()) as $report) {
            $profile = self::getProfile($report, $from, $to);
            if ($profile) {
                $profiles[$report] = $profile;
            } else {
                $missing[] = $report;
            }
        }

        if ($missing) {
            return ['status' => 'incomplete', 'missing' => $missing];
        }

        $summary = [];
        foreach (self::reports() as $key => $def) {
            $p = $profiles[$key];
            $summary[] = [
                'key' => $key,
                'label' => $def['label'],
                'has_in' => (bool) $def['in'],
                'has_out' => (bool) $def['out'],
                'has_saldo' => $def['saldo'],
                'rows' => $p['rows'],
                'seconds' => $p['seconds'],
                'computed_at' => $p['computed_at'],
                'total' => $p['total'],
                'per_unit' => $p['per_unit'],
            ];
        }

        $edges = [];
        foreach (self::edges() as $edge) {
            $edges[] = self::diffEdge($edge, $profiles);
        }

        // Dokumen OUT yang menguras roll bersaldo minus (dicocokkan per lokasi)
        $saldoMinus = $profiles['mb']['negatif'];
        $outDocs = self::dokumenOutRoll($saldoMinus);
        foreach ($saldoMinus as &$row) {
            $row['dokumen'] = $outDocs[$row['barcode'] . '|' . $row['kode_lok']] ?? [];
        }
        unset($row);

        $result = [
            'status' => 'done',
            'from' => $from,
            'to' => $to,
            'summary' => $summary,
            'edges' => $edges,
            'saldo_minus' => $saldoMinus,
            'dobel' => self::rollDobel($from, $to),
        ];

        self::enrich($result);

        return $result;
    }

    private static function diffEdge(array $edge, array $profiles)
    {
        $jenis = $edge['jenis'];
        $ga = $profiles[$edge['a']]['groups'][$edge['group']];
        $gb = $profiles[$edge['b']]['groups'][$edge['group']];

        $rows = [];
        $totalA = 0.0;
        $totalB = 0.0;
        foreach (array_unique(array_merge(array_keys($ga), array_keys($gb))) as $key) {
            $a = $ga[$key] ?? null;
            $b = $gb[$key] ?? null;
            $qa = $a ? $a[$jenis] : 0.0;
            $qb = $b ? $b[$jenis] : 0.0;
            $totalA += $qa;
            $totalB += $qb;

            $selisih = round($qa - $qb, 2);
            if (abs($selisih) < 0.005) {
                continue;
            }

            // Dokumen penyusun (dari laporan Detail Item/Roll) untuk perbandingan per item/barcode
            $docs = [];
            foreach ([$a, $b] as $side) {
                foreach ($side['docs'] ?? [] as $docNo => $qty) {
                    $docs[$docNo] = ($docs[$docNo] ?? 0) + $qty;
                }
            }
            arsort($docs);

            $meta = $a ?? $b;
            unset($meta['in'], $meta['out'], $meta['n'], $meta['docs']);
            $rows[] = $meta + [
                'qty_a' => round($qa, 2),
                'qty_b' => round($qb, 2),
                'selisih' => $selisih,
                'n_a' => $a['n'] ?? 0,
                'n_b' => $b['n'] ?? 0,
                'keterangan' => self::keterangan($edge, $a, $b, $qa, $qb),
                'dokumen' => array_map(fn($docNo, $qty) => ['doc' => (string) $docNo, 'qty' => round($qty, 2)], array_keys($docs), $docs),
            ];
        }

        usort($rows, fn($x, $y) => abs($y['selisih']) <=> abs($x['selisih']));

        return [
            'id' => $edge['id'],
            'jenis' => $jenis,
            'group' => $edge['group'],
            'judul' => $edge['judul'],
            'penjelasan' => $edge['penjelasan'],
            'label_a' => self::reports()[$edge['a']]['label'],
            'label_b' => self::reports()[$edge['b']]['label'],
            // ?: 0.0 supaya tidak tampil -0
            'total_a' => round($totalA, 2) ?: 0.0,
            'total_b' => round($totalB, 2) ?: 0.0,
            'selisih' => round($totalA - $totalB, 2) ?: 0.0,
            'rows' => $rows,
        ];
    }

    private static function keterangan(array $edge, $a, $b, $qa, $qb)
    {
        $labelB = self::reports()[$edge['b']]['label'];

        if ($edge['id'] === 'in_item_roll') {
            if (!$b) {
                return 'Belum ada roll/lokasi sama sekali';
            }
            if (!$a) {
                return 'Ada roll aktif tapi detail dokumen tidak aktif/cancel';
            }

            return $qa > $qb
                ? 'Sebagian roll belum di-lokasi (' . $b['n'] . ' roll tercatat)'
                : 'Qty roll melebihi qty dokumen';
        }

        if ($edge['id'] === 'out_item_roll') {
            return $qa > $qb ? 'Kemungkinan baris roll dobel' : 'Qty roll melebihi qty dokumen';
        }

        if (!$b) {
            return 'Tidak terbaca di ' . $labelB;
        }
        if (!$a) {
            return 'Hanya ada di ' . $labelB;
        }

        return 'Qty berbeda';
    }

    // Semua dokumen OUT (GK/OUT, GK/RO, mutasi) yang mengeluarkan barcode dari lokasi tsb, tanpa batas periode,
    // karena saldo minus bisa berasal dari pengeluaran bulan sebelumnya. Return ['barcode|lokasi' => [...]].
    public static function dokumenOutRoll(array $rows)
    {
        $barcodes = array_values(array_unique(array_filter(array_column($rows, 'barcode'))));
        if (!$barcodes) {
            return [];
        }

        $in = implode(',', array_fill(0, count($barcodes), '?'));
        $list = DB::connection('mysql_sb')->select("
            select d.id_roll barcode, d.no_rak, h.no_bppb doc, h.tgl_bppb tgl, round(sum(d.qty_out), 2) qty
            from whs_bppb_det d
            inner join whs_bppb_h h on h.no_bppb = d.no_bppb
            where d.status = 'Y' and h.status != 'cancel' and d.id_roll in ($in)
            group by d.id_roll, d.no_rak, h.no_bppb

            union all

            select d.id_roll, d.no_rak, h.no_mut, h.tgl_mut, round(sum(d.qty_out), 2)
            from whs_bppb_det d
            inner join whs_mut_lokasi_h h on h.no_mut = d.no_bppb
            where d.status = 'Y' and h.status != 'cancel' and d.id_roll in ($in)
            group by d.id_roll, d.no_rak, h.no_mut

            order by tgl, doc
        ", array_merge($barcodes, $barcodes));

        $map = [];
        foreach ($list as $r) {
            $map[$r->barcode . '|' . $r->no_rak][] = ['doc' => $r->doc, 'tgl' => $r->tgl, 'qty' => (float) $r->qty];
        }

        return $map;
    }

    // Roll yang muncul lebih dari sekali dalam satu dokumen OUT (kemungkinan dobel scan/input).
    public static function rollDobel($from, $to)
    {
        return array_map(fn($r) => (array) $r, DB::connection('mysql_sb')->select("
            select h.no_bppb doc, h.id doc_id, h.tgl_bppb tgl, d.id_roll barcode, d.id_item, d.satuan unit,
                count(*) n, round(sum(d.qty_out), 2) total,
                group_concat(round(d.qty_out, 2) order by d.id separator ' + ') rincian
            from whs_bppb_det d
            inner join whs_bppb_h h on h.no_bppb = d.no_bppb
            where d.status = 'Y' and h.status != 'cancel' and h.tgl_bppb between ? and ?
            group by h.no_bppb, d.id_roll
            having count(*) > 1

            union all

            select h.no_mut doc, h.id doc_id, h.tgl_mut tgl, d.id_roll barcode, d.id_item, d.satuan unit,
                count(*) n, round(sum(d.qty_out), 2) total,
                group_concat(round(d.qty_out, 2) order by d.id separator ' + ') rincian
            from whs_bppb_det d
            inner join whs_mut_lokasi_h h on h.no_mut = d.no_bppb
            where d.status = 'Y' and h.status != 'cancel' and h.tgl_mut between ? and ?
            group by h.no_mut, d.id_roll
            having count(*) > 1

            order by doc, barcode
        ", [$from, $to, $from, $to]));
    }

    // Tambahkan nama item, satuan IN, dan link dokumen agar bisa langsung diperbaiki.
    private static function enrich(array &$result)
    {
        $items = [];
        $docs = [];

        foreach ($result['edges'] as $edge) {
            foreach ($edge['rows'] as $row) {
                if (!empty($row['id_item'])) {
                    $items[$row['id_item']] = true;
                }
                if (!empty($row['doc'])) {
                    $docs[$row['doc']] = true;
                }
                foreach ($row['dokumen'] as $d) {
                    $docs[$d['doc']] = true;
                }
            }
        }
        foreach (array_merge($result['saldo_minus'], $result['dobel']) as $row) {
            if (!empty($row['id_item'])) {
                $items[$row['id_item']] = true;
            }
            if (!empty($row['doc'])) {
                $docs[$row['doc']] = true;
            }
            foreach ($row['dokumen'] ?? [] as $d) {
                $docs[$d['doc']] = true;
            }
        }

        $itemInfo = [];
        foreach (array_chunk(array_keys($items), 1000) as $chunk) {
            $in = implode(',', array_fill(0, count($chunk), '?'));
            foreach (DB::connection('mysql_sb')->select("select id_item, goods_code, itemdesc from masteritem where id_item in ($in)", $chunk) as $r) {
                $itemInfo[$r->id_item] = ['kode' => $r->goods_code, 'nama' => $r->itemdesc];
            }
            // Satuan yang pernah dipakai saat IN, membantu menjelaskan OUT yang tidak terbaca Mutasi Global
            foreach (DB::connection('mysql_sb')->select("select id_item, group_concat(distinct unit order by unit) units from whs_inmaterial_fabric_det where id_item in ($in) group by id_item", $chunk) as $r) {
                $itemInfo[$r->id_item]['unit_in'] = $r->units;
            }
        }

        $docLinks = self::docLinks(array_keys($docs));

        foreach ($result['edges'] as &$edge) {
            foreach ($edge['rows'] as &$row) {
                $info = $itemInfo[$row['id_item'] ?? ''] ?? [];
                $row['kode_item'] = $info['kode'] ?? '';
                $row['nama_item'] = $info['nama'] ?? '';
                $row['link'] = isset($row['doc']) ? ($docLinks[$row['doc']] ?? null) : null;
                foreach ($row['dokumen'] as &$d) {
                    $d['link'] = $docLinks[$d['doc']] ?? null;
                }
                unset($d);

                if ($edge['group'] === 'item' && !empty($info['unit_in']) && strpos($row['keterangan'], 'Tidak terbaca') === 0) {
                    $row['keterangan'] .= ' (satuan saat IN: ' . $info['unit_in'] . ')';
                }
            }
            unset($row);
        }
        unset($edge);

        foreach (['saldo_minus', 'dobel'] as $list) {
            foreach ($result[$list] as &$row) {
                $info = $itemInfo[$row['id_item'] ?? ''] ?? [];
                $row['kode_item'] = $info['kode'] ?? '';
                $row['nama_item'] = $info['nama'] ?? '';
            }
            unset($row);
        }

        foreach ($result['saldo_minus'] as &$row) {
            foreach ($row['dokumen'] as &$d) {
                $d['link'] = $docLinks[$d['doc']] ?? null;
            }
            unset($d);
        }
        unset($row);

        foreach ($result['dobel'] as &$row) {
            $row['link'] = $docLinks[$row['doc']] ?? null;
        }
        unset($row);
    }

    private static function docLinks(array $docs)
    {
        $links = [];
        if (!$docs) {
            return $links;
        }

        foreach (array_chunk($docs, 1000) as $chunk) {
            $in = implode(',', array_fill(0, count($chunk), '?'));

            foreach (DB::connection('mysql_sb')->select("select id, no_dok from whs_inmaterial_fabric where no_dok in ($in)", $chunk) as $r) {
                if (strpos($r->no_dok, 'GK/IN') === 0) {
                    $links[$r->no_dok] = route('lokasi-inmaterial', $r->id);
                } elseif (strpos($r->no_dok, 'GK/RI') === 0) {
                    $links[$r->no_dok] = route('lokasi-retur-inmaterial', $r->id);
                }
            }
            foreach (DB::connection('mysql_sb')->select("select id, no_bppb from whs_bppb_h where no_bppb in ($in)", $chunk) as $r) {
                if (strpos($r->no_bppb, 'GK/OUT') === 0) {
                    $links[$r->no_bppb] = route('edit-out-material', $r->id);
                }
            }
            foreach (DB::connection('mysql_sb')->select("select id, no_mut from whs_mut_lokasi_h where no_mut in ($in)", $chunk) as $r) {
                $links[$r->no_mut] = route('edit-mutlok', $r->id);
            }
        }

        return $links;
    }
}
