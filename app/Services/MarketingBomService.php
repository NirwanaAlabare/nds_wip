<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MarketingBomService
{
    /**
     * Header BOM (bom_marketing) dibuat bersamaan dengan Costing.
     * Mengembalikan BOM aktif terbaru untuk Costing tersebut, atau membuat yang baru bila belum ada.
     */
    public function ensureForCosting(int $costingId, string $connection = 'mysql_sb'): object
    {
        $db = DB::connection($connection);

        // Kunci baris costing supaya dua request bersamaan tidak membuat dua header.
        $costing = $db->table('act_costing')->where('id', $costingId)->lockForUpdate()->first();
        if (!$costing) {
            throw new \RuntimeException('Data Costing tidak ditemukan.');
        }

        $existing = $this->activeBomForCosting($costingId, $connection);
        if ($existing) {
            return $existing;
        }

        $user = Auth::user();
        $noKatalogBom = $this->nextCatalogNumber($connection);
        $id = $db->table('bom_marketing')->insertGetId([
            'no_katalog_bom' => $noKatalogBom,
            'id_buyer' => $costing->id_buyer,
            'style' => $costing->styleno,
            'market' => $costing->market ?? null,
            'id_costing' => $costingId,
            'created_at' => now(),
            'updated_at' => now(),
            'created_by' => $user ? ($user->username ?? $user->name) : 'system',
        ]);

        return (object) ['id' => $id, 'no_katalog_bom' => $noKatalogBom];
    }

    public function activeBomForCosting(int $costingId, string $connection = 'mysql_sb'): ?object
    {
        return DB::connection($connection)->table('bom_marketing')
            ->where('id_costing', $costingId)
            ->where(function ($q) {
                $q->whereNull('cancel')->orWhere('cancel', 'N');
            })
            ->orderByDesc('id')
            ->select('id', 'no_katalog_bom')
            ->first();
    }

    /**
     * Hubungkan SO aktif milik Costing yang belum punya BOM ke header BOM Costing tersebut.
     */
    public function attachSalesOrders(int $costingId, int $bomId, string $connection = 'mysql_sb'): int
    {
        return DB::connection($connection)->table('so')
            ->where('id_cost', $costingId)
            ->where('cancel_h', 'N')
            ->where(function ($q) {
                $q->whereNull('id_bom')->orWhere('id_bom', 0);
            })
            ->update(['id_bom' => $bomId]);
    }

    /**
     * bom_marketing.colors/sizes (JSON id) masih dibaca halaman edit & PDF BOM.
     * Header dibuat sebelum ada SO, jadi isinya diambil dari so_det semua SO aktif yang memakai BOM ini.
     */
    public function refreshColorsSizes(int $bomId, string $connection = 'mysql_sb'): void
    {
        $db = DB::connection($connection);
        $details = $db->table('so_det as sd')
            ->join('so', 'so.id', '=', 'sd.id_so')
            ->where('so.id_bom', $bomId)
            ->where('so.cancel_h', 'N')
            ->where('sd.cancel', 'N')
            ->select('sd.id_color', 'sd.id_size')
            ->get();

        $colors = $details->pluck('id_color')->filter()->map(fn ($id) => (string) $id)->unique()->values();
        $sizes = $details->pluck('id_size')->filter()->map(fn ($id) => (string) $id)->unique()->values();

        $db->table('bom_marketing')->where('id', $bomId)->update([
            'colors' => $colors->isEmpty() ? null : json_encode($colors->all()),
            'sizes' => $sizes->isEmpty() ? null : json_encode($sizes->all()),
        ]);
    }

    public function nextCatalogNumber(string $connection = 'mysql_sb'): string
    {
        $prefix = 'BOM/' . Carbon::now()->format('my') . '/';
        $lastBomNo = DB::connection($connection)->table('bom_marketing')
            ->where('no_katalog_bom', 'LIKE', $prefix . '%')
            ->orderByDesc('id')
            ->value('no_katalog_bom');

        $lastSequence = $lastBomNo ? (int) last(explode('/', $lastBomNo)) : 0;

        return $prefix . str_pad($lastSequence + 1, 4, '0', STR_PAD_LEFT);
    }
}
