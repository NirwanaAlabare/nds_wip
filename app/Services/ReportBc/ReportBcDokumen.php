<?php

namespace App\Services\ReportBc;

/**
 * Tampilan nomor & tanggal dokumen pabean (nomor daftar / tanggal daftar) di Report BC.
 * Dokumen INHOUSE tidak punya dokumen pabean: nomor & tanggalnya selalu "-"
 * (dulu tidak seragam: 000000, 00000-, -, 00-00-0000, atau tanggal BPB).
 * Dokumen non-INHOUSE yang nomornya belum diisi EXIM juga tampil "-".
 */
class ReportBcDokumen
{
    public static function inhouse($row): bool
    {
        return strtoupper(trim((string) ($row->jenis_dokumen ?? ''))) === 'INHOUSE';
    }

    public static function nomor($row): string
    {
        if (self::inhouse($row)) {
            return '-';
        }

        // Nomor belum diisi EXIM bisa tersimpan kosong, "-" (setelah LPAD jadi 00000-) atau 000000: tampil "-"
        $nomor = trim((string) ($row->nomor_daftar ?? ''));

        return preg_match('/^0*-?$/', $nomor) ? '-' : $nomor;
    }

    // $kosong: teks bila tanggal dokumen bukan INHOUSE kosong (tabel memakai '00-00-0000', excel '-')
    public static function tanggal($row, string $kosong = '-'): string
    {
        if (self::inhouse($row)) {
            return '-';
        }

        $tgl = $row->tanggal_daftar ?? null;
        if (! $tgl || $tgl == '0000-00-00' || $tgl == '0000-00-00 00:00:00') {
            return $kosong;
        }

        return date('d-m-Y', strtotime($tgl));
    }
}
