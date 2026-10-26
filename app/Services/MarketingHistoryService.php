<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MarketingHistoryService
{
    public function record(
        int $costingId,
        string $type,
        string $action,
        string $description,
        ?int $typeId = null,
        ?string $userName = null,
        string $connection = 'mysql_sb'
    ): int {
        if ($costingId <= 0) {
            throw new \InvalidArgumentException('ID Costing harus lebih besar dari nol.');
        }

        foreach (['type' => $type, 'action' => $action, 'description' => $description] as $field => $value) {
            if (trim($value) === '') {
                throw new \InvalidArgumentException("Field histori {$field} tidak boleh kosong.");
            }
        }

        $db = DB::connection($connection);
        if (!Schema::connection($connection)->hasTable('mkt_hist')) {
            throw new \RuntimeException("Tabel mkt_hist belum tersedia pada koneksi {$connection}.");
        }

        if ($userName === null) {
            $user = Auth::user();
            $userName = $user ? ($user->name ?: $user->username) : null;
        }

        return (int) $db->table('mkt_hist')->insertGetId([
            'id_act_cost' => $costingId,
            'type' => $type,
            'type_id' => $typeId ?? $costingId,
            'desc' => $description,
            'user' => $userName,
            'created_at' => now(),
            'action' => $action,
        ]);
    }

    // ===== Kalimat histori alur Costing -> SO -> BOM (sesuai tabel "Rules icon process marketing") =====

    /** Format tanggal di kalimat histori, sama dengan timeline listing: "23 Oct 2026 @ 2:05 PM". */
    public static function stamp(): string
    {
        return now()->format('j M Y @ g:i A');
    }

    /** Costing: 1. Marketing Staff created costing on [tanggal @ jam] */
    public function costingCreated(int $costingId): void
    {
        $this->record($costingId, 'costing', 'created', 'Marketing Staff created costing on ' . self::stamp());
    }

    /**
     * Costing / BOM disetujui manager:
     * 2. Marketing Manager changed status from Awaiting Approval to Approved on [..]. This request has been approved.
     * 3. Costing [No] is Approved and ready to be created as a Sales Order on [..]
     *    BOM [No] is Approved and ready to be created as a Worksheet on [..]
     */
    public function approved(string $type, int $costingId, int $typeId, string $number): void
    {
        $stamp = self::stamp();
        $this->record($costingId, $type, 'approved',
            "Marketing Manager changed status from Awaiting Approval to Approved on {$stamp}. This request has been approved.", $typeId);
        $this->record($costingId, $type, 'ready', $type === 'bom'
            ? "BOM {$number} is Approved and ready to be created as a Worksheet on {$stamp}."
            : "Costing {$number} is Approved and ready to be created as a Sales Order on {$stamp}.", $typeId);
    }

    /**
     * Sales Order (tanpa approval):
     * 1. Marketing Staff created sales order on [..]
     * 2. Sales Order [No] is Approved automatically by the system and ready to be created as a BOM on [..]
     */
    public function soCreated(int $costingId, int $soId, string $soNo): void
    {
        $stamp = self::stamp();
        $this->record($costingId, 'so', 'created', "Marketing Staff created sales order on {$stamp}", $soId);
        $this->record($costingId, 'so', 'ready',
            "Sales Order {$soNo} is Approved automatically by the system and ready to be created as a BOM on {$stamp}", $soId, 'System');
    }

    /** Header costing disimpan: Marketing Staff updated costing on [..] */
    public function costingUpdated(int $costingId): void
    {
        $this->record($costingId, 'costing', 'updated', 'Marketing Staff updated costing on ' . self::stamp());
    }

    /** Item costing ditambah / diubah / dihapus (lihat itemsChanged). */
    public function costingItemsChanged(int $costingId, string $action, string $category, int $count = 1): void
    {
        $this->itemsChanged($costingId, 'costing', $costingId, $action, $category, $count);
    }

    /** Item BOM ditambah / diubah / dihapus (lihat itemsChanged). */
    public function bomItemsChanged(int $costingId, int $bomId, string $action, int $count = 1): void
    {
        if ($count > 0) {
            $this->itemsChanged($costingId, 'bom', $bomId, $action, null, $count);
        }
    }

    /**
     * Histori item dokumen (costing / BOM):
     *   Marketing Staff added 2 costing items in Fabric on [..]
     *   Marketing Staff updated costing items in Fabric, Other Cost (3 changes) on [..]
     *   Marketing Staff deleted 1 BOM item on [..]
     * Tabel item menyimpan per baris, jadi perubahan beruntun (user, dokumen & aksi sama, maks. 10 menit, tanpa histori
     * lain di antaranya) digabung ke satu baris histori: jumlah & kategori ditambah, waktunya diperbarui.
     *
     * @param string $doc    costing | bom
     * @param string $action items_added | items_updated | items_deleted
     */
    public function itemsChanged(int $costingId, string $doc, int $typeId, string $action, ?string $category, int $count = 1): void
    {
        $verbs = ['items_added' => 'added', 'items_updated' => 'updated', 'items_deleted' => 'deleted'];
        $nouns = ['costing' => 'costing', 'bom' => 'BOM'];
        if (!isset($verbs[$action]) || !isset($nouns[$doc])) {
            throw new \InvalidArgumentException('Aksi histori item tidak valid.');
        }
        $type = $doc . '_item';
        $noun = $nouns[$doc];
        $user = Auth::user();
        $userName = $user ? ($user->name ?: $user->username) : null;
        $db = DB::connection('mysql_sb');

        $last = $db->table('mkt_hist')->where('id_act_cost', $costingId)->orderByDesc('id')->first();
        $categories = $category !== null && $category !== '' ? [$category] : [];
        $total = $count;
        $mergeId = null;
        $nounRe = preg_quote($noun, '/');
        $pattern = $action === 'items_updated'
            ? '/' . $nounRe . ' items?(?: in (?<cats>.+?))? \((?<n>\d+) changes?\) on \d/'
            : '/ (?<n>\d+) ' . $nounRe . ' items?(?: in (?<cats>.+?))? on \d/';
        if ($last && $last->action === $action && $last->type === $type && (int) $last->type_id === $typeId
            && (string) $last->user === (string) $userName
            && $last->created_at && now()->diffInMinutes(\Carbon\Carbon::parse($last->created_at)) <= 10
            && preg_match($pattern, (string) $last->desc, $m)) {
            $mergeId = $last->id;
            $total += (int) $m['n'];
            $previous = !empty($m['cats']) ? array_map('trim', explode(',', $m['cats'])) : [];
            $categories = array_values(array_unique(array_merge($previous, $categories)));
        }

        $in = $categories ? ' in ' . implode(', ', $categories) : '';
        $desc = $action === 'items_updated'
            ? sprintf('Marketing Staff updated %s items%s (%d %s) on %s', $noun, $in, $total, $total === 1 ? 'change' : 'changes', self::stamp())
            : sprintf('Marketing Staff %s %d %s %s%s on %s', $verbs[$action], $total, $noun, $total === 1 ? 'item' : 'items', $in, self::stamp());
        if ($mergeId) {
            $db->table('mkt_hist')->where('id', $mergeId)->update(['desc' => $desc, 'created_at' => now()]);
            return;
        }
        $this->record($costingId, $type, $action, $desc, $typeId, $userName);
    }

    /**
     * Sales Order diubah (Edit SO): Marketing Staff updated sales order SO/.. (2 details updated, 1 added) on [..]
     *
     * @param array<string,int> $counts ['updated' => n, 'added' => n, 'deleted' => n, 'restored' => n]
     */
    public function soUpdated(int $costingId, int $soId, string $soNo, array $counts): void
    {
        $parts = [];
        foreach (['updated', 'added', 'deleted', 'restored'] as $key) {
            $n = (int) ($counts[$key] ?? 0);
            if ($n > 0) {
                $parts[] = $n . ' ' . ($n === 1 ? 'detail' : 'details') . ' ' . $key;
            }
        }
        $detail = $parts ? ' (' . implode(', ', $parts) . ')' : '';
        $this->record($costingId, 'so', 'updated', "Marketing Staff updated sales order {$soNo}{$detail} on " . self::stamp(), $soId);
    }

    /**
     * BOM: 1. Marketing Staff created BOM on [..] — dicatat sekali, saat item BOM pertama kali disimpan.
     * Hasil: true kalau baris "created" baru saja ditulis (berarti ini item pertama BOM).
     */
    public function bomCreatedOnce(int $costingId, int $bomId): bool
    {
        $exists = DB::connection('mysql_sb')->table('mkt_hist')
            ->where('type', 'bom')->where('type_id', $bomId)->where('action', 'created')->exists();
        if (!$exists) {
            $this->record($costingId, 'bom', 'created', 'Marketing Staff created BOM on ' . self::stamp(), $bomId);
        }
        return !$exists;
    }
}
