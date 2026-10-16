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
}
