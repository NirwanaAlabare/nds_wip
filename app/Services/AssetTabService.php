<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AssetTabService
{
    // Tab yang sudah dibawa lebih lama dari ini ditandai "belum kembali"
    const OVERDUE_HOURS = 12;

    // Transaksi Tab: ID card (enroll ID) wajib diisi atau tidak, baik Single maupun Bulk.
    // Masa awal masih boleh kosong; ubah ke true kalau semua transaksi harus pakai ID card.
    const NIK_WAJIB = false;

    // Pemegang tab = transaksi AMBIL terakhir dari tab tersebut.
    // $codes null = semua tab yang sedang TAKEN. $withNames false = tanpa nama karyawan (tidak query ke HRIS).
    // Return: [KODE RFID => object {rfid_code, enroll_id, employee_name, tujuan, tipe_input, created_by, taken_at}]
    public function holders(?array $codes = null, $withNames = true)
    {
        if ($codes !== null && !$codes) {
            return [];
        }

        $lastAmbil = DB::table('asset_trans_tab')
            ->selectRaw('MAX(id) AS id')
            ->where('status', 'AMBIL')
            ->groupBy('rfid_code');

        if ($codes === null) {
            $lastAmbil->whereIn('rfid_code', DB::table('asset_master_tab')->select('rfid_code')->where('status', 'TAKEN'));
        } else {
            $lastAmbil->whereIn('rfid_code', $codes);
        }

        $rows = DB::table('asset_trans_tab as tt')
            ->joinSub($lastAmbil, 'last', 'last.id', '=', 'tt.id')
            ->select('tt.rfid_code', 'tt.enroll_id', 'tt.tujuan', 'tt.tipe_input', 'tt.created_by', 'tt.created_at as taken_at')
            ->get();

        $names = $withNames ? $this->employeeNames($rows->pluck('enroll_id')->all()) : [];

        $holders = [];
        foreach ($rows as $row) {
            $row->employee_name = $names[$row->enroll_id] ?? null;
            $holders[strtoupper($row->rfid_code)] = $row;
        }

        return $holders;
    }

    // Data pemegang yang dikirim ke browser
    public function holderPayload($holder)
    {
        if (!$holder) {
            return null;
        }

        $takenAt = $holder->taken_at ? Carbon::parse($holder->taken_at) : null;

        return [
            'enroll_id' => $holder->enroll_id,
            'name' => $holder->employee_name,
            'tujuan' => $holder->tujuan,
            'tipe_input' => $holder->tipe_input,
            'taken_at' => $takenAt ? $takenAt->format('Y-m-d H:i') : null,
            'menit' => $takenAt ? $takenAt->diffInMinutes(Carbon::now()) : null,
            'overdue' => $this->isOverdue($holder->taken_at),
        ];
    }

    public function isOverdue($takenAt)
    {
        return $takenAt !== null && Carbon::parse($takenAt)->lte(Carbon::now()->subHours(self::OVERDUE_HOURS));
    }

    public function overdueCount()
    {
        return collect($this->holders(null, false))->filter(fn($holder) => $this->isOverdue($holder->taken_at))->count();
    }

    // Nama karyawan dari HRIS. Kalau HRIS tidak bisa dihubungi, nama dikosongkan saja
    // supaya scan dan transaksi tab tetap jalan.
    public function employeeNames(array $enrollIds)
    {
        $enrollIds = array_values(array_unique(array_filter($enrollIds)));

        if (!$enrollIds || $this->hrisDown()) {
            return [];
        }

        try {
            $placeholders = implode(',', array_fill(0, count($enrollIds), '?'));
            $rows = DB::connection('mysql_hris')->select("
                SELECT enroll_id, employee_name
                FROM employee_atribut
                WHERE enroll_id IN ($placeholders)
            ", $enrollIds);
        } catch (\Throwable $e) {
            $this->markHrisDown($e);
            return [];
        }

        $names = [];
        foreach ($rows as $row) {
            $names[$row->enroll_id] = $row->employee_name;
        }

        return $names;
    }

    // Cari enroll ID dari potongan nama karyawan (untuk pencarian riwayat berdasarkan nama)
    public function enrollIdsByName($keyword, $limit = 200)
    {
        if ($this->hrisDown()) {
            return [];
        }

        try {
            return collect(DB::connection('mysql_hris')->select("
                SELECT enroll_id FROM employee_atribut WHERE employee_name LIKE ? LIMIT " . (int) $limit . "
            ", ['%' . $keyword . '%']))->pluck('enroll_id')->all();
        } catch (\Throwable $e) {
            $this->markHrisDown($e);
            return [];
        }
    }

    // Setelah HRIS gagal dihubungi, pencarian nama dilewati sebentar supaya scan berikutnya
    // tidak ikut tertahan menunggu timeout koneksi
    private function hrisDown()
    {
        return Cache::has('asset_tab_hris_down');
    }

    private function markHrisDown(\Throwable $e)
    {
        Log::warning('HRIS tidak bisa dihubungi dari menu Tab: ' . $e->getMessage());
        Cache::put('asset_tab_hris_down', true, 60);
    }
}
