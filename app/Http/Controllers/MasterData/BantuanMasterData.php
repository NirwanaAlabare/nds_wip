<?php

namespace App\Http\Controllers\MasterData;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Bantuan bersama controller Master Data (customer / supplier / item): aturan teks Signalbit
 * dan log audit ke act_hist, supaya data dari NDS sama bentuknya dengan yang dibuat di Signalbit.
 */
trait BantuanMasterData
{
    // Log audit seperti insert_log() Signalbit: query-nya ke act_hist (tanda kutip jadi '!').
    // Host ditandai NDS supaya kelihatan perubahannya dibuat dari aplikasi ini.
    // Id act_hist-nya dikembalikan untuk riwayat (supaya sinkron act_hist tidak mencatatnya dua kali).
    private function catatLog($sql)
    {
        $user = Auth::user()->username ?? Auth::user()->name;

        return DB::connection('mysql_sb')->table('act_hist')->insertGetId([
            'Trans_Date' => now()->format('Y-m-d H:i:s'),
            'Trans_Desc' => str_replace("'", '!', $sql),
            'Trans_Host' => substr($user . '/' . request()->ip() . '/NDS', 0, 60),
        ]);
    }

    // Query insert untuk log, bentuknya sama dengan insert Signalbit
    private function sqlInsert($tabel, array $data)
    {
        $nilai = collect($data)->map(fn ($x) => "'" . $x . "'")->implode(',');
        return 'insert into ' . $tabel . ' (' . implode(',', array_keys($data)) . ') values (' . $nilai . ')';
    }

    // Query update untuk log: hanya kolom yang berubah
    private function sqlUpdate($tabel, array $data, $kolomId, $id)
    {
        $set = collect($data)->map(fn ($nilai, $kolom) => $kolom . "='" . $nilai . "'")->implode(',');
        return 'update ' . $tabel . ' set ' . $set . ' where ' . $kolomId . "='" . $id . "'";
    }

    // nb() Signalbit: trim, huruf besar, tanda kutip tunggal dibuang
    private function nb($nilai)
    {
        return trim(strtoupper(str_replace("'", '', (string) $nilai)));
    }

    // Kolom angka (double): isian kosong / bukan angka disimpan 0, sama hasilnya dengan Signalbit
    private function angka($nilai)
    {
        $nilai = str_replace(',', '', trim((string) $nilai));
        return is_numeric($nilai) ? (float) $nilai : 0;
    }
}
