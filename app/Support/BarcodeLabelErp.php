<?php

namespace App\Support;

// Gambar barcode roll fabric yang sama persis (piksel per piksel) dengan label ERP Signalbit
// (erp/pages/forms/pdfBarcode_whs.php, yang memakai barcode.php klasik dengan size 40, code128, print):
// Code 128-B 1 px per modul, ruang kosong 10 px kiri-kanan, batang setinggi 41 px, lalu teks kode
// dengan font GD 5 di (31, 40). Untuk kode 10 karakter hasilnya 165 x 70 px, dicetak pada 96 dpi.
class BarcodeLabelErp
{
    // Pola Code 128 nilai 0-102 (lebar batang/spasi bergantian, dalam modul)
    private const POLA = [
        '212222', '222122', '222221', '121223', '121322', '131222', '122213', '122312', '132212', '221213',
        '221312', '231212', '112232', '122132', '122231', '113222', '123122', '123221', '223211', '221132',
        '221231', '213212', '223112', '312131', '311222', '321122', '321221', '312212', '322112', '322211',
        '212123', '212321', '232121', '111323', '131123', '131321', '112313', '132113', '132311', '211313',
        '231113', '231311', '112133', '112331', '132131', '113123', '113321', '133121', '313121', '211331',
        '231131', '213113', '213311', '213131', '311123', '311321', '331121', '312113', '312311', '332111',
        '314111', '221411', '431111', '111224', '111422', '121124', '121421', '141122', '141221', '112214',
        '112412', '122114', '122411', '142112', '142211', '241211', '221114', '413111', '241112', '134111',
        '111242', '121142', '121241', '114212', '124112', '124211', '411212', '421112', '421211', '212141',
        '214121', '412121', '111143', '111341', '131141', '114113', '114311', '411113', '411311', '113141',
        '114131', '311141', '411131',
    ];
    private const START_B = '211214';
    private const STOP = '2331112';
    private const TINGGI_BATANG = 40;

    // PNG (biner) untuk satu kode. Karakter di luar Code 128-B (ASCII 32-127) dilewati.
    public static function png(string $kode): string
    {
        $pola = self::START_B;
        $checksum = 104;
        $posisi = 0;
        foreach (str_split($kode) as $karakter) {
            $nilai = ord($karakter) - 32;
            if ($nilai < 0 || $nilai > 95) {
                continue;
            }
            $pola .= self::POLA[$nilai];
            $checksum += $nilai * ++$posisi;
        }
        $pola .= self::POLA[$checksum % 103] . self::STOP;

        $lebar = 20 + array_sum(str_split($pola));
        $gambar = imagecreate($lebar, self::TINGGI_BATANG + 30);
        $hitam = imagecolorallocate($gambar, 0, 0, 0);
        $putih = imagecolorallocate($gambar, 255, 255, 255);
        imagefill($gambar, 0, 0, $putih);
        imagestring($gambar, 5, 31, self::TINGGI_BATANG, $kode, $hitam);

        // Sama dengan barcode.php: tiap persegi digambar sampai x+lebar (inklusif), jadi batang terakhir 1 px lebih lebar
        $x = 10;
        foreach (str_split($pola) as $i => $modul) {
            imagefilledrectangle($gambar, $x, 0, $x + (int) $modul, self::TINGGI_BATANG, $i % 2 == 0 ? $hitam : $putih);
            $x += (int) $modul;
        }

        ob_start();
        imagepng($gambar);
        imagedestroy($gambar);

        return ob_get_clean();
    }
}
