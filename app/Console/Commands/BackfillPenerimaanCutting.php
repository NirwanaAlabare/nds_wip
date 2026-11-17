<?php

namespace App\Console\Commands;

use App\Models\Cutting\ScannedItem;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Penerimaan massal untuk pengeluaran gudang ke Cutting yang tgl_bppb-nya
 * sebelum tanggal tertentu (default 1 Oktober 2026).
 *
 * Langkah 1 (penerimaan_cutting):
 * - Insert per whs_bppb_det yang belum diterima, status != 'N'.
 * - tanggal_terima = tgl_bppb, created_at = tgl_bppb 00:00:00.
 *
 * Langkah 2 (scanned_item):
 * - Hanya DIBUAT untuk roll hasil backfill yang belum punya scanned_item.
 *   Roll yang sudah punya scanned_item tidak diubah sama sekali.
 * - Target diambil dari penerimaan_cutting ber-tanda backfill, jadi aman
 *   dijalankan ulang bila sempat terhenti (--only-scanned).
 *
 * Tidak menjalankan fixChainedQty / perhitungan ulang lain.
 * Semua data ditandai created_by_username = 'backfill'.
 *
 * Contoh:
 *   php artisan penerimaan-cutting:backfill --dry-run
 *   php artisan penerimaan-cutting:backfill --skip-scanned --force
 *   php artisan penerimaan-cutting:backfill --only-scanned --force
 */
class BackfillPenerimaanCutting extends Command
{
    protected $signature = 'penerimaan-cutting:backfill
                            {--before=2026-10-01 : Ambil whs_bppb_det dengan tgl_bppb < tanggal ini}
                            {--chunk=500 : Jumlah baris per batch}
                            {--sleep=200 : Jeda antar batch (milidetik) agar tidak membebani DB}
                            {--skip-scanned : Hanya insert penerimaan_cutting, lewati pembuatan scanned_item}
                            {--only-scanned : Hanya buat scanned_item (untuk roll hasil backfill yang belum punya)}
                            {--force : Jalankan tanpa konfirmasi (untuk dijalankan tanpa ditunggui)}
                            {--dry-run : Hanya hitung, tidak menyimpan apa pun}';

    protected $description = 'Penerimaan Fabric Cutting massal untuk BPPB sebelum tanggal tertentu (+ buat scanned_item yang belum ada)';

    private const MARK = 'backfill';

    public function handle()
    {
        $before = $this->option('before');
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $before)) {
            $this->error('Format --before harus YYYY-MM-DD');
            return 1;
        }

        $chunk = max(50, (int) $this->option('chunk'));
        $sleep = max(0, (int) $this->option('sleep'));
        $dryRun = (bool) $this->option('dry-run');
        $skipScanned = (bool) $this->option('skip-scanned');
        $onlyScanned = (bool) $this->option('only-scanned');

        if ($skipScanned && $onlyScanned) {
            $this->error('--skip-scanned dan --only-scanned tidak bisa dipakai bersamaan.');
            return 1;
        }

        $this->info('Mulai: ' . Carbon::now()->format('Y-m-d H:i:s'));

        if (!$onlyScanned) {
            if (!$this->stepPenerimaan($before, $chunk, $sleep, $dryRun)) {
                return 0;
            }
        }

        if (!$skipScanned) {
            $this->stepScannedItem($chunk, $sleep, $dryRun);
        }

        $this->info('Selesai: ' . Carbon::now()->format('Y-m-d H:i:s'));
        if (!$dryRun) {
            $this->comment("Rollback bila perlu: DELETE dari penerimaan_cutting & scanned_item WHERE created_by_username = '" . self::MARK . "'");
        }

        return 0;
    }

    /**
     * Langkah 1: insert penerimaan_cutting. Return false bila harus berhenti.
     */
    private function stepPenerimaan(string $before, int $chunk, int $sleep, bool $dryRun): bool
    {
        $summary = $this->baseQuery($before)
            ->leftJoin('laravel_nds.scanned_item as si', 'si.id_roll', '=', 'd.id_roll')
            ->selectRaw('COUNT(*) AS baris, COUNT(DISTINCT d.id_roll) AS roll, COUNT(DISTINCT CASE WHEN si.id IS NULL THEN d.id_roll END) AS roll_tanpa_scanned')
            ->first();

        $this->info("[1/2] BPPB ke Cutting sebelum {$before} yang belum diterima (status != 'N'):");
        $this->table(['Baris penerimaan', 'Roll unik', 'Roll tanpa scanned_item'], [[
            $summary->baris, $summary->roll, $summary->roll_tanpa_scanned,
        ]]);

        if ($dryRun) {
            $this->comment('Dry run: penerimaan tidak disimpan.');
            return true;
        }

        if ($summary->baris < 1) {
            $this->info('Tidak ada penerimaan yang perlu diproses.');
            return true;
        }

        if (!$this->option('force') && !$this->confirm("Simpan {$summary->baris} penerimaan?")) {
            return false;
        }

        $bar = $this->output->createProgressBar($summary->baris);
        $inserted = 0;

        $this->baseQuery($before)
            ->selectRaw('d.id, d.id_roll, d.qty_out, d.satuan, h.tgl_bppb')
            ->chunkById($chunk, function ($rows) use (&$inserted, $bar, $sleep) {
                $now = Carbon::now();
                $data = [];

                foreach ($rows as $row) {
                    [$qtyKonv, $unitKonv] = $this->konversi($row->qty_out, $row->satuan);
                    $tgl = substr($row->tgl_bppb, 0, 10);

                    $data[] = [
                        'tanggal_terima'      => $tgl,
                        'whs_bppb_det_id'     => $row->id,
                        'id_roll'             => $row->id_roll,
                        'qty_konv'            => $qtyKonv,
                        'unit_konv'           => $unitKonv,
                        'created_by'          => null,
                        'created_by_username' => self::MARK,
                        'created_at'          => $tgl . ' 00:00:00',
                        'updated_at'          => $now,
                    ];
                }

                DB::table('penerimaan_cutting')->insert($data);
                $inserted += count($data);
                $bar->advance(count($data));

                if ($sleep) usleep($sleep * 1000);
            }, 'd.id', 'id');

        $bar->finish();
        $this->newLine();
        $this->info("Penerimaan tersimpan: {$inserted}");

        return true;
    }

    /**
     * Langkah 2: buat scanned_item untuk roll hasil backfill yang belum punya.
     */
    private function stepScannedItem(int $chunk, int $sleep, bool $dryRun): void
    {
        $rolls = DB::table('penerimaan_cutting as pc')
            ->leftJoin('scanned_item as si', 'si.id_roll', '=', 'pc.id_roll')
            ->where('pc.created_by_username', self::MARK)
            ->whereNull('si.id')
            ->whereNotNull('pc.id_roll')
            ->distinct()
            ->orderBy('pc.id_roll')
            ->pluck('pc.id_roll');

        $this->info("[2/2] Roll hasil backfill yang belum punya scanned_item: {$rolls->count()}");

        if ($dryRun) {
            $this->comment('Dry run: scanned_item tidak dibuat. (Angka ini 0 bila langkah 1 belum pernah dijalankan.)');
            return;
        }

        if ($rolls->count() < 1) {
            return;
        }

        if (!$this->option('force') && !$this->confirm("Buat scanned_item untuk {$rolls->count()} roll?")) {
            return;
        }

        $bar = $this->output->createProgressBar($rolls->count());
        $created = 0;
        $skipped = [];

        foreach ($rolls as $i => $idRoll) {
            // Bisa saja sudah dibuat dari form cutting selama command berjalan
            if (ScannedItem::where('id_roll', $idRoll)->exists()) {
                $bar->advance();
                continue;
            }

            $item = $this->rollData($idRoll);

            if (!$item) {
                $skipped[] = $idRoll;
                $bar->advance();
                continue;
            }

            [$qty] = $this->konversi($item->qty, $item->unit);
            [$qtyStok, $unit] = $this->konversi($item->qty_stok, $item->unit);

            ScannedItem::create([
                'id_roll'             => strtoupper($idRoll),
                'id_item'             => $item->id_item,
                'id_jo'               => $item->id_jo,
                'color'               => '-',
                'detail_item'         => $item->detail_item,
                'detail_item_color'   => $item->detail_item_color,
                'detail_item_size'    => $item->detail_item_size,
                'lot'                 => $item->lot,
                'roll'                => $item->roll,
                'roll_buyer'          => $item->roll_buyer,
                'qty'                 => $qty,
                'qty_stok'            => $qtyStok,
                'qty_in'              => $qty,
                'qty_pakai'           => 0,
                'unit'                => $unit,
                'rule_bom'            => $item->rule_bom,
                'so_det_list'         => $item->so_det_list,
                'size_list'           => $item->size_list,
                'created_by'          => null,
                'created_by_username' => self::MARK,
            ]);

            $created++;
            $bar->advance();

            if ($sleep && $i % $chunk === $chunk - 1) usleep($sleep * 1000);
        }

        $bar->finish();
        $this->newLine();
        $this->info("scanned_item dibuat: {$created}");

        if (count($skipped)) {
            $this->warn(count($skipped) . ' roll tidak dibuat scanned_item (data roll tidak ditemukan / qty_out 0 / bukan GK/OUT):');
            $this->line(implode(', ', array_slice($skipped, 0, 50)) . (count($skipped) > 50 ? ', …' : ''));
        }
    }

    /**
     * whs_bppb_det ke Cutting sebelum $before, belum diterima, status bukan 'N'.
     */
    private function baseQuery(string $before)
    {
        return DB::connection('mysql_sb')
            ->table('whs_bppb_det as d')
            ->join('whs_bppb_h as h', 'h.no_bppb', '=', 'd.no_bppb')
            ->leftJoin('laravel_nds.penerimaan_cutting as pc', 'pc.whs_bppb_det_id', '=', 'd.id')
            ->where('h.tujuan', 'Production - Cutting')
            ->where('d.no_bppb', 'NOT LIKE', 'MT/%')
            ->where('h.tgl_bppb', '<', $before)
            ->whereRaw("COALESCE(d.status, 'Y') <> 'N'")
            ->whereNull('pc.id');
    }

    /**
     * Data roll untuk scanned_item. Rumus sama dengan GeneralController::getScannedItem
     * (query $newItem): qty = total qty_out yang sudah diterima - retur dari Cutting.
     */
    private function rollData(string $idRoll)
    {
        $rows = DB::connection('mysql_sb')->select("
            SELECT
                id_roll, id_jo, detail_item, detail_item_color, detail_item_size, id_item,
                lot, roll, roll_buyer, qty_stok,
                SUM(qty) - COALESCE(qty_ri, 0) AS qty,
                unit, rule_bom, so_det_list, size_list
            FROM (
                SELECT
                    whs_bppb_det.id_roll,
                    whs_bppb_det.id_jo,
                    masteritem.itemdesc detail_item,
                    masteritem.color detail_item_color,
                    masteritem.size detail_item_size,
                    whs_bppb_det.id_item,
                    whs_bppb_det.no_lot lot,
                    whs_bppb_det.no_roll roll,
                    whs_lokasi_inmaterial.no_roll_buyer roll_buyer,
                    whs_bppb_det.qty_stok,
                    whs_bppb_det.qty_out qty,
                    whs_bppb_det.satuan unit,
                    bji.rule_bom,
                    GROUP_CONCAT(DISTINCT so_det.id ORDER BY so_det.id ASC SEPARATOR ', ') AS so_det_list,
                    GROUP_CONCAT(DISTINCT so_det.size ORDER BY so_det.id ASC SEPARATOR ', ') AS size_list
                FROM
                    laravel_nds.penerimaan_cutting
                    LEFT JOIN whs_bppb_det ON whs_bppb_det.id = penerimaan_cutting.whs_bppb_det_id
                    LEFT JOIN whs_bppb_h ON whs_bppb_h.no_bppb = whs_bppb_det.no_bppb
                    LEFT JOIN (SELECT no_barcode, id_item, no_roll_buyer FROM whs_lokasi_inmaterial WHERE no_barcode = ? GROUP BY no_barcode, no_roll_buyer) whs_lokasi_inmaterial ON whs_lokasi_inmaterial.no_barcode = whs_bppb_det.id_roll
                    LEFT JOIN masteritem ON masteritem.id_item = whs_lokasi_inmaterial.id_item
                    LEFT JOIN bom_jo_item bji ON bji.id_item = masteritem.id_gen
                    LEFT JOIN so_det ON so_det.id = bji.id_so_det
                WHERE
                    whs_bppb_det.id_roll = ?
                    AND whs_bppb_h.tujuan = 'Production - Cutting'
                    AND CAST(whs_bppb_det.qty_out AS DECIMAL(11, 3)) > 0.000
                    AND whs_bppb_det.no_bppb LIKE '%GK/OUT%'
                GROUP BY
                    whs_bppb_det.id
            ) item
            LEFT JOIN (
                SELECT a.no_barcode, (CASE WHEN supplier_in.no_barcode IS NULL THEN 0 ELSE SUM(qty_aktual) END) qty_ri
                FROM whs_lokasi_inmaterial a
                INNER JOIN whs_inmaterial_fabric b ON b.no_dok = a.no_dok
                LEFT JOIN (
                    SELECT b.no_barcode FROM whs_inmaterial_fabric a
                    LEFT JOIN whs_lokasi_inmaterial b ON b.no_dok = a.no_dok
                    WHERE b.no_barcode = ? AND supplier != 'Production - Cutting' AND b.status = 'Y'
                    GROUP BY no_barcode
                ) supplier_in ON supplier_in.no_barcode = a.no_barcode
                WHERE a.no_barcode = ? AND supplier = 'Production - Cutting' AND a.status = 'Y'
                GROUP BY no_barcode
            ) AS ri ON ri.no_barcode = item.id_roll
            GROUP BY id_roll
            LIMIT 1
        ", [$idRoll, $idRoll, $idRoll, $idRoll]);

        return $rows[0] ?? null;
    }

    private function konversi($qty, $unit): array
    {
        $unit = strtoupper(trim((string) $unit));

        if ($unit == 'YRD' || $unit == 'YARD') {
            return [round(((float) $qty) * 0.9144, 2), 'METER'];
        }

        return [(float) $qty, $unit];
    }
}
