<?php

namespace App\Services\Marketing;

use avadim\FastExcelWriter\Excel;
use ZipArchive;

/**
 * Penulis .xlsx marketing di atas avadim/fast-excel-writer (fast excel).
 * Sel ditampung dulu (alamat bebas, urutan bebas) lalu ditulis sekaligus saat save, jadi generator bisa menulis
 * seperti PhpSpreadsheet: nilai, style per rentang (digabung), border all / outline / sisi, merge, lebar kolom,
 * tinggi baris, gambar, freeze, pengaturan cetak, footer, dan tanpa gridline.
 *
 * Kunci style(): bold, italic, size, color, fill, h (left|center|right), v (top|center|bottom), wrap, format.
 */
class MarketingXlsx
{
    private array $sheets = [];
    private int $cur = -1;
    private array $defaultFont = ['font-name' => 'Arial', 'font-size' => 10];

    public function font(string $name, float $size): self
    {
        $this->defaultFont = ['font-name' => $name, 'font-size' => $size];

        return $this;
    }

    /** Sheet baru (jadi sheet aktif untuk panggilan berikutnya). */
    public function sheet(string $name): self
    {
        $this->sheets[] = ['name' => $name, 'cells' => [], 'merges' => [], 'widths' => [], 'heights' => [], 'images' => [],
            'freeze' => 0, 'grid' => true, 'page' => [], 'footer' => null, 'print' => null];
        $this->cur = count($this->sheets) - 1;

        return $this;
    }

    public function set(string $cell, $value, array $style = []): self
    {
        [$c, $r] = self::addr($cell);
        $this->sheets[$this->cur]['cells'][$r][$c]['v'] = $value;
        if ($style) {
            $this->style($cell, $style);
        }

        return $this;
    }

    /** Isi beberapa kolom satu baris sekaligus: ['A' => 1, 'B' => 'x', ...]; null dilewati. */
    public function row(int $r, array $values): self
    {
        foreach ($values as $col => $value) {
            if ($value !== null) {
                $this->set($col . $r, $value);
            }
        }

        return $this;
    }

    /** Tulis tabel mulai dari $cell (seperti fromArray). */
    public function rows(string $cell, array $rows): self
    {
        [$c0, $r0] = self::addr($cell);
        foreach (array_values($rows) as $i => $row) {
            foreach (array_values((array) $row) as $j => $value) {
                if ($value !== null) {
                    $this->set(self::col($c0 + $j) . ($r0 + $i), $value);
                }
            }
        }

        return $this;
    }

    public function value(string $cell)
    {
        [$c, $r] = self::addr($cell);

        return $this->sheets[$this->cur]['cells'][$r][$c]['v'] ?? null;
    }

    public function style(string $range, array $style): self
    {
        foreach ($this->cellsOf($range) as [$c, $r]) {
            $old = $this->sheets[$this->cur]['cells'][$r][$c]['s'] ?? [];
            $this->sheets[$this->cur]['cells'][$r][$c]['s'] = array_replace($old, $style);
        }

        return $this;
    }

    /** $where: all | outline | top | bottom | left | right. */
    public function border(string $range, string $where = 'all', string $style = 'thin', string $color = '000000'): self
    {
        [$c1, $r1, $c2, $r2] = self::range($range);
        $line = ['style' => $style, 'color' => '#' . ltrim($color, '#')];
        foreach ($this->cellsOf($range) as [$c, $r]) {
            $sides = $where === 'all' ? ['top', 'bottom', 'left', 'right'] : [];
            if ($where === 'outline') {
                $sides = array_keys(array_filter(['top' => $r === $r1, 'bottom' => $r === $r2, 'left' => $c === $c1, 'right' => $c === $c2]));
            } elseif ($where !== 'all') {
                $sides = in_array($where, ['top', 'left'], true)
                    ? (($where === 'top' ? $r === $r1 : $c === $c1) ? [$where] : [])
                    : (($where === 'bottom' ? $r === $r2 : $c === $c2) ? [$where] : []);
            }
            foreach ($sides as $side) {
                $this->sheets[$this->cur]['cells'][$r][$c]['b'][$side] = $line;
            }
        }

        return $this;
    }

    public function merge(string $range): self
    {
        $this->sheets[$this->cur]['merges'][$range] = $range;

        return $this;
    }

    public function isMerged(string $range): bool
    {
        return isset($this->sheets[$this->cur]['merges'][$range]);
    }

    public function width(string $col, float $width): self
    {
        $this->sheets[$this->cur]['widths'][$col] = $width;

        return $this;
    }

    public function widths(array $widths): self
    {
        foreach ($widths as $col => $width) {
            $this->width($col, $width);
        }

        return $this;
    }

    public function height(int $row, float $height): self
    {
        $this->sheets[$this->cur]['heights'][$row] = $height;

        return $this;
    }

    /** Gambar dimuat ke dalam kotak maks $maxW x $maxH px, perbandingan tetap. File tidak ada = dilewati. */
    public function image(string $path, string $cell, int $maxW, int $maxH): self
    {
        if (is_file($path) && ($size = @getimagesize($path))) {
            $scale = min($maxW / $size[0], $maxH / $size[1], 1);
            $this->sheets[$this->cur]['images'][] = [$cell, $path, (int) round($size[0] * $scale), (int) round($size[1] * $scale)];
        }

        return $this;
    }

    /** Baris 1..$rows tetap terlihat saat scroll. */
    public function freeze(int $rows): self
    {
        $this->sheets[$this->cur]['freeze'] = $rows;

        return $this;
    }

    public function hideGrid(): self
    {
        $this->sheets[$this->cur]['grid'] = false;

        return $this;
    }

    /** Cetak A4 landscape, lebar muat 1 halaman, margin (inci), area cetak. */
    public function printLandscape(string $area, array $margins = ['left' => 0.4, 'right' => 0.4, 'top' => 0.4, 'bottom' => 0.5]): self
    {
        $this->sheets[$this->cur]['page'] = $margins;
        $this->sheets[$this->cur]['print'] = $area;

        return $this;
    }

    /** Footer cetak: teks kiri + "Halaman X dari Y" di kanan. */
    public function footer(string $left): self
    {
        $this->sheets[$this->cur]['footer'] = $left;

        return $this;
    }

    public function save(string $file): void
    {
        $excel = Excel::create(array_column($this->sheets, 'name') ?: ['Sheet1']);
        $excel->setDefaultFont($this->defaultFont);
        foreach ($this->sheets as $i => $def) {
            $sheet = $excel->sheet($def['name']);
            if ($def['widths']) {
                $sheet->setColWidths($def['widths']);
            }
            foreach ($def['heights'] as $row => $height) {
                $sheet->setRowHeight($row, $height);
            }
            // Baris tanpa sel tidak ditulis avadim, jadi tingginya hilang: beri sel kosong.
            foreach (array_keys($def['heights']) as $row) {
                $def['cells'][$row] ??= [1 => ['v' => '']];
            }
            ksort($def['cells']);
            foreach ($def['cells'] as $r => $cols) {
                ksort($cols);
                foreach ($cols as $c => $cell) {
                    $sheet->setValue(self::col($c) . $r, $cell['v'] ?? (isset($cell['s']) || isset($cell['b']) ? '' : null), $this->avStyle($cell['s'] ?? [], $cell['b'] ?? []));
                }
            }
            foreach ($def['merges'] as $range) {
                $sheet->mergeCells($range);
            }
            foreach ($def['images'] as [$cell, $path, $w, $h]) {
                $sheet->addImage($cell, $path, ['width' => $w, 'height' => $h]);
            }
            if ($def['freeze']) {
                $sheet->setFreezeRows($def['freeze']);
            }
            if ($def['print']) {
                $sheet->pageLandscape();
                $sheet->pagePaperSize(Excel::PAPERSIZE_A4);
                $sheet->pageFitToWidth(1);
                $sheet->pageFitToHeight(0);
                $sheet->pageMargins($def['page']);
                $sheet->setPrintArea($def['print']);
            }
        }
        $excel->save($file);
        $this->patch($file);
    }

    /** Simpan ke file sementara lalu kirim sebagai unduhan (FastExcel::download() merusak response Laravel). */
    public function download(string $name)
    {
        $file = tempnam(sys_get_temp_dir(), 'mkx') . '.xlsx';
        $this->save($file);

        return response()->download($file, $name, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
            ->deleteFileAfterSend(true);
    }

    private function avStyle(array $s, array $borders): array
    {
        // Font sel selalu membawa nama & ukuran default (avadim memakai Calibri 11 untuk atribut yang tidak disebut).
        $font = array_filter([
            'name' => $this->defaultFont['font-name'],
            'style' => trim(($s['bold'] ?? false ? 'bold ' : '') . ($s['italic'] ?? false ? 'italic' : '')) ?: null,
            'size' => $s['size'] ?? $this->defaultFont['font-size'],
            'color' => isset($s['color']) ? '#' . ltrim($s['color'], '#') : null,
        ]);
        $out = [];
        if ($font) {
            $out['font'] = $font;
        }
        if (!empty($s['fill'])) {
            $out['fill-color'] = '#' . ltrim($s['fill'], '#');
        }
        foreach (['h' => 'format-align-horizontal', 'v' => 'format-align-vertical', 'format' => 'format'] as $k => $key) {
            if (isset($s[$k]) && $s[$k] !== '') {
                $out[$key] = $s[$k];
            }
        }
        if (!empty($s['wrap'])) {
            $out['format-text-wrap'] = true;
        }
        if ($borders) {
            $out['border'] = $borders;
        }

        return $out;
    }

    /** Tanpa gridline + footer: avadim belum punya pengaturannya, jadi XML sheet disunting langsung. */
    private function patch(string $file): void
    {
        $zip = new ZipArchive();
        if ($zip->open($file) !== true) {
            return;
        }
        foreach ($this->sheets as $i => $def) {
            $path = 'xl/worksheets/sheet' . ($i + 1) . '.xml';
            $xml = $zip->getFromName($path);
            if ($xml === false || ($def['grid'] && $def['footer'] === null)) {
                continue;
            }
            if (!$def['grid']) {
                $xml = preg_replace('/<sheetView(?=[\s>\/])(?![^>]*showGridLines)/', '<sheetView showGridLines="0"', $xml, 1);
            }
            if ($def['footer'] !== null) {
                $left = htmlspecialchars(str_replace('&', '&&', $def['footer']), ENT_XML1);
                $footer = '<headerFooter><oddFooter>&amp;L&amp;7 ' . $left . '&amp;R&amp;7 Halaman &amp;P dari &amp;N</oddFooter></headerFooter>';
                $xml = preg_match('/<pageSetup[^>]*\/>/', $xml)
                    ? preg_replace('/(<pageSetup[^>]*\/>)/', '$1' . $footer, $xml, 1)
                    : preg_replace('/(<drawing |<\/worksheet>)/', $footer . '$1', $xml, 1);
            }
            $zip->addFromString($path, $xml);
        }
        $zip->close();
    }

    private function cellsOf(string $range): array
    {
        [$c1, $r1, $c2, $r2] = self::range($range);
        $out = [];
        for ($r = $r1; $r <= $r2; $r++) {
            for ($c = $c1; $c <= $c2; $c++) {
                $out[] = [$c, $r];
            }
        }

        return $out;
    }

    private static function range(string $range): array
    {
        $parts = explode(':', $range);
        [$c1, $r1] = self::addr($parts[0]);
        [$c2, $r2] = self::addr($parts[1] ?? $parts[0]);

        return [min($c1, $c2), min($r1, $r2), max($c1, $c2), max($r1, $r2)];
    }

    /** 'B12' => [2, 12]. */
    private static function addr(string $cell): array
    {
        preg_match('/^([A-Z]+)(\d+)$/', strtoupper($cell), $m);
        $c = 0;
        foreach (str_split($m[1]) as $ch) {
            $c = $c * 26 + ord($ch) - 64;
        }

        return [$c, (int) $m[2]];
    }

    public static function col(int $c): string
    {
        $s = '';
        while ($c > 0) {
            $m = ($c - 1) % 26;
            $s = chr(65 + $m) . $s;
            $c = intdiv($c - 1, 26);
        }

        return $s;
    }
}
