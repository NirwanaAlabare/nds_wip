<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Histori alur Costing -> SO -> BOM, disimpan di process_logs DB baru (koneksi mysql_mkt).
 * $costingId = act_costing.id, barisnya dicari lewat nomor Costing. Lewat forNds(), $costingId = costings.id.
 * SO dari alur lama dan BOM belum ada di DB baru, jadi sales_orders_id dan bom_id-nya belum diisi; nomornya ada di kalimat note.
 */
class MarketingHistoryService
{
    private const CONNECTION = 'mysql_mkt';

    private bool $nds = false;

    /** Log dari alur DB baru: $costingId sudah costings.id dan log langsung ditulis, tanpa menunggu Signalbit. */
    public function forNds(): self
    {
        $history = clone $this;
        $history->nds = true;

        return $history;
    }

    // Status dokumen sesudah aksi. SO tanpa approval, jadi tidak punya status.
    private const STATUS_DOCUMENTS = ['costing', 'bom'];
    private const STATUS_AFTER_ACTION = [
        'created' => 'awaiting',
        'copied' => 'awaiting',
        'resubmitted' => 'awaiting',
        'approved' => 'approved',
        'relocked' => 'approved',
        'return' => 'return',
        'cancel' => 'cancel',
        'unlocked' => 'unlocked',
    ];

    public function record(
        int $costingId,
        string $type,
        string $action,
        string $description,
        bool $bySystem = false
    ): void {
        if ($costingId <= 0) {
            throw new \InvalidArgumentException('ID Costing harus lebih besar dari nol.');
        }

        foreach (['type' => $type, 'action' => $action, 'description' => $description] as $field => $value) {
            if (trim($value) === '') {
                throw new \InvalidArgumentException("Field histori {$field} tidak boleh kosong.");
            }
        }

        $this->afterSignalbitCommit($costingId, function (int $logCostingId) use ($type, $action, $description, $bySystem) {
            $this->insertLog($logCostingId, $type, $action, $description, $bySystem);
        });
    }

    /** Timeline listing Costing, terlama dulu. */
    public function timeline(int $costingId)
    {
        $number = $this->costingNumber($costingId);
        if (!MarketingNdsCostingService::enabled() || $number === null) {
            return collect();
        }

        return DB::connection(self::CONNECTION)->table('process_logs as l')
            ->join('costings as c', 'c.id', '=', 'l.costings_id')
            ->leftJoin('users as u', 'u.id', '=', 'l.action_by')
            ->where('c.costing_no', $number)
            ->orderBy('l.action_at')
            ->orderBy('l.id')
            ->get([
                'l.id',
                'l.doc_type as type',
                'l.note as desc',
                'u.username as user',
                'l.action_at as created_at',
                'l.action',
            ]);
    }

    /**
     * Catatan Return to Staff / Cancel terakhir per Costing.
     *
     * @return array<int, string> act_costing.id => catatan
     */
    public function latestNotes(string $type, array $costingIds): array
    {
        if (!MarketingNdsCostingService::enabled() || !$costingIds) {
            return [];
        }
        $numbers = DB::connection('mysql_sb')->table('act_costing')->whereIn('id', $costingIds)->pluck('cost_no', 'id');
        $notes = DB::connection(self::CONNECTION)->table('process_logs as l')
            ->join('costings as c', 'c.id', '=', 'l.costings_id')
            ->whereIn('c.costing_no', $numbers->values()->all())
            ->where('l.doc_type', $type)
            ->whereIn('l.action', ['return', 'cancel'])
            ->orderBy('l.id')
            ->pluck('l.note', 'c.costing_no');

        return $numbers->map(fn ($number) => $notes[$number] ?? null)->filter()->all();
    }

    /** Ada simpanan header / item Costing sejak waktu tertentu. */
    public function costingChangedSince(int $costingId, $since): bool
    {
        $number = $this->costingNumber($costingId);
        if (!MarketingNdsCostingService::enabled() || $number === null) {
            return false;
        }

        return DB::connection(self::CONNECTION)->table('process_logs as l')
            ->join('costings as c', 'c.id', '=', 'l.costings_id')
            ->where('c.costing_no', $number)
            ->whereIn('l.doc_type', ['costing', 'costing_item'])
            ->whereIn('l.action', ['updated', 'items_added', 'items_updated', 'items_deleted'])
            ->when($since, fn ($q) => $q->where('l.action_at', '>=', $since))
            ->exists();
    }

    /**
     * Log ditulis setelah transaksi Signalbit selesai, supaya tidak tertinggal kalau transaksinya batal dan
     * Costing baru sudah bisa disalin ke DB baru. Gagal tidak membatalkan simpan di Signalbit, hanya dicatat.
     */
    /**
     * Log Sales Order DB baru. $costingId di sini = costings.id, bukan act_costing.id.
     */
    public function salesOrder(int $costingId, int $salesOrderId, string $action, string $description): void
    {
        try {
            DB::connection(self::CONNECTION)->table('process_logs')->insert([
                'costings_id' => $costingId,
                'sales_orders_id' => $salesOrderId,
                'doc_type' => 'so',
                'action' => $action,
                'note' => $description . ' on ' . self::stamp(),
                'action_by' => app(MarketingNdsCostingService::class)->userId(),
                'action_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $exception) {
            logger()->error('Gagal menulis process log.', [
                'sales_orders_id' => $salesOrderId,
                'exception' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * Log BOM DB baru (Input BOM per WS). $costingId = costings.id.
     *   [user] created BOM BOM/1126/00001 for worksheet SGT/1126/001 on [..]
     *   [user] updated BOM BOM/1126/00001 for worksheet SGT/1126/001 on [..]
     * Save beruntun (BOM & user sama, maks. 10 menit, tanpa histori lain di antaranya) digabung ke satu baris "updated".
     */
    public function bom(int $costingId, int $bomId, string $action, string $bomNo, string $worksheet): void
    {
        try {
            $db = DB::connection(self::CONNECTION);
            $userId = app(MarketingNdsCostingService::class)->userId();
            $verb = $action === 'created' ? 'created' : 'updated';
            $note = self::actor() . " {$verb} BOM {$bomNo} for worksheet {$worksheet} on " . self::stamp();
            $last = $db->table('process_logs')->where('costings_id', $costingId)->orderByDesc('id')->first();
            if ($action === 'updated' && $last && $last->doc_type === 'bom' && $last->action === 'updated'
                && (int) $last->bom_id === $bomId && (int) $last->action_by === (int) $userId
                && $last->action_at && now()->diffInMinutes(\Carbon\Carbon::parse($last->action_at)) <= 10) {
                $db->table('process_logs')->where('id', $last->id)->update(['note' => $note, 'action_at' => now(), 'updated_at' => now()]);

                return;
            }
            $db->table('process_logs')->insert([
                'costings_id' => $costingId,
                'bom_id' => $bomId,
                'doc_type' => 'bom',
                'action' => $verb,
                'note' => $note,
                'action_by' => $userId,
                'action_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $exception) {
            logger()->error('Gagal menulis process log.', ['bom_id' => $bomId, 'exception' => $exception->getMessage()]);
        }
    }

    private function afterSignalbitCommit(int $costingId, \Closure $write): void
    {
        if (!MarketingNdsCostingService::enabled()) {
            return;
        }

        if ($this->nds) {
            try {
                $write($costingId);
            } catch (\Throwable $exception) {
                logger()->error('Gagal menulis process log.', ['costings_id' => $costingId, 'exception' => $exception->getMessage()]);
            }

            return;
        }

        DB::connection('mysql_sb')->afterCommit(function () use ($costingId, $write) {
            try {
                // Costing lama yang tidak ada di DB baru tidak dicatat.
                $logCostingId = DB::connection(self::CONNECTION)->table('costings')->where('costing_no', $this->legacyCostingNumber($costingId))->value('id');
                if ($logCostingId) {
                    $write((int) $logCostingId);
                }
            } catch (\Throwable $exception) {
                logger()->error('Gagal menulis process log.', [
                    'act_costing_id' => $costingId,
                    'exception' => $exception->getMessage(),
                ]);
            }
        });
    }

    private function insertLog(int $logCostingId, string $type, string $action, string $description, bool $bySystem): void
    {
        $db = DB::connection(self::CONNECTION);
        $toStatus = in_array($type, self::STATUS_DOCUMENTS, true) ? (self::STATUS_AFTER_ACTION[$action] ?? null) : null;
        $fromStatus = $toStatus === null ? null : $db->table('process_logs')
            ->where('costings_id', $logCostingId)
            ->where('doc_type', $type)
            ->whereNotNull('to_status')
            ->orderByDesc('id')
            ->value('to_status');

        $db->table('process_logs')->insert([
            'costings_id' => $logCostingId,
            'doc_type' => $type,
            'action' => $action,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'note' => $description,
            'action_by' => $bySystem ? null : app(MarketingNdsCostingService::class)->userId(),
            'action_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function costingNumber(int $costingId): ?string
    {
        return $this->nds
            ? DB::connection(self::CONNECTION)->table('costings')->where('id', $costingId)->value('costing_no')
            : $this->legacyCostingNumber($costingId);
    }

    private function legacyCostingNumber(int $costingId): ?string
    {
        return DB::connection('mysql_sb')->table('act_costing')->where('id', $costingId)->value('cost_no');
    }

    // ===== Kalimat histori alur Costing -> SO -> BOM (sesuai tabel "Rules icon process marketing") =====

    /** Pelaku di kalimat histori: username yang login, sama dengan nama di users DB baru. */
    public static function actor(): string
    {
        $user = Auth::user();

        return $user ? (string) ($user->username ?? $user->name ?? 'User') : 'User';
    }

    /** Format tanggal di kalimat histori, sama dengan timeline listing: "23 Oct 2026 @ 2:05 PM". */
    public static function stamp(): string
    {
        return now()->format('j M Y @ g:i A');
    }

    /** Costing: 1. [user] created costing on [tanggal @ jam] */
    public function costingCreated(int $costingId): void
    {
        $this->record($costingId, 'costing', 'created', self::actor() . ' created costing on ' . self::stamp());
    }

    /**
     * Costing / BOM disetujui manager:
     * 2. [user] changed status from Awaiting Approval to Approved on [..]. This request has been approved.
     * 3. Costing [No] is Approved and ready to be created as a Sales Order on [..]
     *    BOM [No] is Approved and ready to be created as a Worksheet on [..]
     */
    public function approved(string $type, int $costingId, string $number): void
    {
        $stamp = self::stamp();
        $this->record($costingId, $type, 'approved',
            self::actor() . " changed status from Awaiting Approval to Approved on {$stamp}. This request has been approved.");
        $this->record($costingId, $type, 'ready', $type === 'bom'
            ? "BOM {$number} is Approved and ready to be created as a Worksheet on {$stamp}."
            : "Costing {$number} is Approved and ready to be created as a Sales Order on {$stamp}.");
    }

    /**
     * Sales Order (tanpa approval):
     * 1. [user] created sales order on [..]
     * 2. Sales Order [No] is Approved automatically by the system and ready to be created as a BOM on [..]
     */
    public function soCreated(int $costingId, string $soNo): void
    {
        $stamp = self::stamp();
        $this->record($costingId, 'so', 'created', self::actor() . " created sales order on {$stamp}");
        $this->record($costingId, 'so', 'ready',
            "Sales Order {$soNo} is Approved automatically by the system and ready to be created as a BOM on {$stamp}", true);
    }

    /** Header costing disimpan: [user] updated costing on [..] */
    public function costingUpdated(int $costingId): void
    {
        $this->record($costingId, 'costing', 'updated', self::actor() . ' updated costing on ' . self::stamp());
    }

    /** Item costing ditambah / diubah / dihapus (lihat itemsChanged). */
    public function costingItemsChanged(int $costingId, string $action, string $category, int $count = 1): void
    {
        $this->itemsChanged($costingId, 'costing', $action, $category, $count);
    }

    /** Item BOM ditambah / diubah / dihapus (lihat itemsChanged). */
    public function bomItemsChanged(int $costingId, string $action, int $count = 1): void
    {
        if ($count > 0) {
            $this->itemsChanged($costingId, 'bom', $action, null, $count);
        }
    }

    /**
     * Histori item dokumen (costing / BOM):
     *   [user] added 2 costing items in Fabric on [..]
     *   [user] updated costing items in Fabric, Other Cost (3 changes) on [..]
     *   [user] deleted 1 BOM item on [..]
     * Tabel item menyimpan per baris, jadi perubahan beruntun (user, dokumen & aksi sama, maks. 10 menit, tanpa histori
     * lain di antaranya) digabung ke satu baris histori: jumlah & kategori ditambah, waktunya diperbarui.
     *
     * @param string $doc    costing | bom
     * @param string $action items_added | items_updated | items_deleted
     */
    public function itemsChanged(int $costingId, string $doc, string $action, ?string $category, int $count = 1): void
    {
        $verbs = ['items_added' => 'added', 'items_updated' => 'updated', 'items_deleted' => 'deleted'];
        $nouns = ['costing' => 'costing', 'bom' => 'BOM'];
        if (!isset($verbs[$action]) || !isset($nouns[$doc])) {
            throw new \InvalidArgumentException('Aksi histori item tidak valid.');
        }
        $this->afterSignalbitCommit($costingId, function (int $logCostingId) use ($doc, $action, $category, $count, $verbs, $nouns) {
            $this->writeItemsChanged($logCostingId, $doc . '_item', $nouns[$doc], $verbs[$action], $action, $category, $count);
        });
    }

    private function writeItemsChanged(int $logCostingId, string $type, string $noun, string $verb, string $action, ?string $category, int $count): void
    {
        $db = DB::connection(self::CONNECTION);
        $userId = app(MarketingNdsCostingService::class)->userId();

        $last = $db->table('process_logs')->where('costings_id', $logCostingId)->orderByDesc('id')->first();
        $categories = $category !== null && $category !== '' ? [$category] : [];
        $total = $count;
        $mergeId = null;
        $nounRe = preg_quote($noun, '/');
        $pattern = $action === 'items_updated'
            ? '/' . $nounRe . ' items?(?: in (?<cats>.+?))? \((?<n>\d+) changes?\) on \d/'
            : '/ (?<n>\d+) ' . $nounRe . ' items?(?: in (?<cats>.+?))? on \d/';
        if ($last && $last->action === $action && $last->doc_type === $type
            && (int) $last->action_by === (int) $userId
            && $last->action_at && now()->diffInMinutes(\Carbon\Carbon::parse($last->action_at)) <= 10
            && preg_match($pattern, (string) $last->note, $m)) {
            $mergeId = $last->id;
            $total += (int) $m['n'];
            $previous = !empty($m['cats']) ? array_map('trim', explode(',', $m['cats'])) : [];
            $categories = array_values(array_unique(array_merge($previous, $categories)));
        }

        $in = $categories ? ' in ' . implode(', ', $categories) : '';
        $desc = $action === 'items_updated'
            ? sprintf('%s updated %s items%s (%d %s) on %s', self::actor(), $noun, $in, $total, $total === 1 ? 'change' : 'changes', self::stamp())
            : sprintf('%s %s %d %s %s%s on %s', self::actor(), $verb, $total, $noun, $total === 1 ? 'item' : 'items', $in, self::stamp());
        if ($mergeId) {
            $db->table('process_logs')->where('id', $mergeId)->update(['note' => $desc, 'action_at' => now(), 'updated_at' => now()]);
            return;
        }
        $this->insertLog($logCostingId, $type, $action, $desc, false);
    }

    /**
     * Sales Order diubah (Edit SO): [user] updated sales order SO/.. (2 details updated, 1 added) on [..]
     *
     * @param array<string,int> $counts ['updated' => n, 'added' => n, 'deleted' => n, 'restored' => n]
     */
    public function soUpdated(int $costingId, string $soNo, array $counts): void
    {
        $parts = [];
        foreach (['updated', 'added', 'deleted', 'restored'] as $key) {
            $n = (int) ($counts[$key] ?? 0);
            if ($n > 0) {
                $parts[] = $n . ' ' . ($n === 1 ? 'detail' : 'details') . ' ' . $key;
            }
        }
        $detail = $parts ? ' (' . implode(', ', $parts) . ')' : '';
        $this->record($costingId, 'so', 'updated', self::actor() . " updated sales order {$soNo}{$detail} on " . self::stamp());
    }

    /**
     * BOM: 1. [user] created BOM on [..] — dicatat sekali, saat item BOM pertama kali disimpan.
     * Hasil: true kalau baris "created" baru saja ditulis (berarti ini item pertama BOM).
     */
    public function bomCreatedOnce(int $costingId): bool
    {
        $number = $this->costingNumber($costingId);
        $exists = MarketingNdsCostingService::enabled() && DB::connection(self::CONNECTION)->table('process_logs as l')
            ->join('costings as c', 'c.id', '=', 'l.costings_id')
            ->where('c.costing_no', $number)
            ->where('l.doc_type', 'bom')
            ->where('l.action', 'created')
            ->exists();
        if (!$exists) {
            $this->record($costingId, 'bom', 'created', self::actor() . ' created BOM on ' . self::stamp());
        }
        return !$exists;
    }
}
