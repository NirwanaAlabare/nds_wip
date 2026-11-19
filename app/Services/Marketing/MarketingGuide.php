<?php

namespace App\Services\Marketing;

/**
 * Pembantu gambar User Guide marketing (resources/views/marketing/guides/*, modal marketing/partials/user_guide).
 * - map(): denah halaman (SVG) dengan bagian yang sedang dibahas disorot oranye.
 * - field() / num(): contoh isian (tiruan field form) dan nomor penjelasan oranye.
 * Warna memakai variabel CSS --g-* dari marketing-guide.css, jadi ikut tema terang/gelap.
 */
class MarketingGuide
{
    /**
     * @param array<string, array{0:float,1:float,2:float,3:float,4:string,5?:string}> $parts key => [x, y, w, h, label, 'button'?]
     * @param string|array $active key bagian yang disorot ('all' = semua)
     */
    public static function map(array $parts, $active, string $label, int $width = 160, int $height = 134): string
    {
        $active = (array) $active;
        $svg = '<svg viewBox="0 0 ' . $width . ' ' . $height . '" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="' . e($label) . '">'
            . '<rect x="0.5" y="0.5" width="' . ($width - 1) . '" height="' . ($height - 1) . '" rx="5" fill="var(--g-card)" stroke="var(--g-line)"/>';
        foreach ($parts as $key => $p) {
            [$x, $y, $w, $h, $text] = $p;
            $button = ($p[5] ?? '') === 'button';
            $on = in_array('all', $active, true) || in_array($key, $active, true);
            if ($button) {
                $fill = $on ? ($p[6] ?? '#16a34a') : 'var(--g-line)';
                $svg .= "<rect x=\"$x\" y=\"$y\" width=\"$w\" height=\"$h\" rx=\"" . ($h / 2) . "\" fill=\"$fill\" stroke=\"" . ($on ? '#ff9f1c' : 'none') . '" stroke-width="1"/>'
                    . '<text x="' . ($x + $w / 2) . '" y="' . ($y + $h / 2 + 1.6) . '" font-size="4.4" font-family="Inter, sans-serif" font-weight="700" text-anchor="middle" fill="#fff">' . e($text) . '</text>';
                continue;
            }
            $svg .= "<rect x=\"$x\" y=\"$y\" width=\"$w\" height=\"$h\" rx=\"3\" fill=\"" . ($on ? 'var(--g-hlbg)' : 'var(--g-soft)') . '" stroke="' . ($on ? '#ff9f1c' : 'var(--g-line)') . '" stroke-width="' . ($on ? 1.4 : .8) . '"/>';
            // garis-garis tiruan isian
            for ($ly = $y + 9; $ly < $y + $h - 3; $ly += 6) {
                $svg .= '<rect x="' . ($x + 4) . "\" y=\"$ly\" width=\"" . ($w - 8) . '" height="2.6" rx="1.3" fill="' . ($on ? 'rgba(255,159,28,.45)' : 'var(--g-line)') . '"/>';
            }
            $svg .= '<text x="' . ($x + 4) . '" y="' . ($y + 6) . '" font-size="4.4" font-family="Inter, sans-serif" font-weight="700" fill="' . ($on ? '#c2620c' : 'var(--g-muted)') . '">' . e($text) . '</text>';
        }

        return $svg . '</svg>';
    }

    /** Tiruan field: $opt berisi kelas gm-in (sel, ro, hl) dan/atau 'span' (selebar baris). $value boleh HTML. */
    public static function field(string $label, string $value, string $opt = '', ?int $n = null): string
    {
        return '<div class="gm-f' . (str_contains($opt, 'span') ? ' span' : '') . '"><label>' . $label . '</label><div class="gm-in ' . e($opt) . '">' . $value . '</div>'
            . ($n ? '<span class="gm-n">' . $n . '</span>' : '') . '</div>';
    }

    /** Nomor penjelasan oranye (cocok dengan nomor di gambar). */
    public static function num(int $n): string
    {
        return '<span class="mkt-guide-num">' . $n . '</span>';
    }
}
