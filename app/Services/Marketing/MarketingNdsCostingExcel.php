<?php

namespace App\Services\Marketing;

/**
 * Excel Costing DB baru (fast excel / avadim lewat MarketingXlsx), meniru "excel costing.xlsx": kolom A-T, rumus hidup
 * (PRICE IDR, PRICE USD, VALUE, %, QTY BOM, total, summary G&A s.d. FOB), gambar pertama di header, semua gambar di
 * bagian C. IMAGES. Data dari Marketing_CostingNdsController::costingDocument() (sama dengan PDF).
 */
class MarketingNdsCostingExcel
{
    private const NAVY = '284A63';
    private const LINE = 'B8C2D1';
    private const MONEY = '#,##0.00_);\-#,##0.00_);"-"_)';
    private const USD = '#,##0.0000_);\-#,##0.0000_);"-"_)';
    private const ALLOW = '0.00_);\-0.00_);"-"_)';
    private const CONS = '0.000_)';
    private const QTY = '#,##0_)';
    private const PCT = '0.00%_)';
    private const PCT_INPUT = '0.00"%"_)';

    private MarketingXlsx $x;

    /** Sel kolom % yang rumusnya diisi setelah posisi TOTAL COST diketahui. */
    private array $pctCells = [];

    public function download(array $d)
    {
        $this->x = $x = (new MarketingXlsx())->sheet('Costing');
        $x->hideGrid()->widths(['A' => 5, 'B' => 13, 'C' => 13, 'D' => 15, 'E' => 15, 'F' => 6, 'G' => 24, 'H' => 14, 'I' => 7, 'J' => 11,
            'K' => 12, 'L' => 11, 'M' => 9, 'N' => 7, 'O' => 9, 'P' => 14, 'Q' => 12, 'R' => 11, 'S' => 11, 'T' => 13]);

        $this->header($d);
        $this->info($d);

        // A. MATERIAL
        $r = 11;
        $this->title($r, 'A. MATERIAL');
        $headerRow = $r + 1;
        $this->tableHead($headerRow, ['SHELL', 'PART']);
        $r = $headerRow + 1;
        $matTotals = [];
        foreach ($d['material'] as $group) {
            [$r, $totalRow] = $this->group($r, $group, false);
            $matTotals[] = $totalRow;
        }
        $matRow = $r;
        $this->totalRow($matRow, 'TOTAL MATERIAL', 'DFE8F5', fn ($c) => $matTotals ? implode('+', array_map(fn ($t) => $c . $t, $matTotals)) : '0', true);
        $this->pctCells[] = ['R', $matRow];
        $r = $matRow + 1;
        $x->height($r, 8.1);

        // B. MANUFACTURING & OTHER COST
        $r++;
        $this->title($r, 'B. MANUFACTURING & OTHER COST');
        $this->tableHead($r + 1, ['CALC TYPE', 'SUBCONT']);
        $r += 2;
        [$r, $mfgRow] = $this->group($r, $d['manufacturing'], true);
        [$r, $otherRow] = $this->group($r, $d['other'], true);
        $grandRow = $r;
        $this->band("A{$grandRow}:T{$grandRow}", 'DFE8F5', true);
        $x->merge("A{$grandRow}:O{$grandRow}")->set("A{$grandRow}", 'GRAND TOTAL (A + B)', ['h' => 'left'])
            ->set("S{$grandRow}", "=S{$matRow}+S{$mfgRow}")->set("T{$grandRow}", "=T{$matRow}+T{$mfgRow}");
        $this->numbers($grandRow);
        $x->height($grandRow, 18);

        // Notes, tanda tangan, summary
        $r = $grandRow + 1;
        $x->height($r, 9.95);
        $top = $r + 1;
        $this->summary($top, $d, $matRow, $mfgRow, $otherRow);
        $totalCostRow = $top + 6;
        // Kolom % tiap baris = VALUE IDR / TOTAL COST di summary.
        foreach ($this->pctCells as [$col, $row]) {
            $x->set("R{$row}", "=IF(\$Q\${$totalCostRow}=0,0,P{$row}/\$Q\${$totalCostRow})");
        }
        $this->notesAndSign($top, $d);

        // C. IMAGES
        $last = $top + 10;
        if ($d['images']) {
            $r = $top + 12;
            $this->title($r, 'C. IMAGES (' . count($d['images']) . ')');
            $r++;
            foreach (array_chunk($d['images'], 5) as $chunkIndex => $chunk) {
                $x->height($r, 120);
                foreach ($chunk as $k => $image) {
                    $col = ['A', 'E', 'I', 'M', 'Q'][$k];
                    $x->image($image['path'], "{$col}{$r}", 150, 155)
                        ->set($col . ($r + 1), ($chunkIndex * 5 + $k + 1) . '. ' . $image['type'], ['bold' => true, 'color' => self::NAVY])
                        ->set($col . ($r + 2), (string) $image['name'], ['size' => 8, 'color' => '666666']);
                }
                $r += 4;
            }
            $last = $r;
        }

        $x->freeze($headerRow)->printLandscape("A1:T{$last}")->footer($d['no'] . ' Rev ' . $d['revision']);

        return $x->download('Costing_' . str_replace('/', '-', $d['no']) . '.xlsx');
    }

    private function header(array $d): void
    {
        $this->x->height(1, 21.95)->height(2, 15.95)->height(3, 6)
            ->image(public_path('assets/dist/img/nag-mark.png'), 'A1', 57, 34)
            ->merge('B1:H1')->set('B1', '      PT NIRWANA ALABARE GARMENT', ['bold' => true, 'size' => 14, 'color' => self::NAVY])
            ->merge('B2:H2')->set('B2', '        Marketing', ['size' => 10, 'color' => '666666'])
            ->merge('M1:T1')->set('M1', 'COSTING', ['bold' => true, 'size' => 18, 'color' => self::NAVY])
            ->merge('M2:T2')->set('M2', (string) $d['no'], ['bold' => true, 'size' => 11])
            ->style('M1:T2', ['h' => 'right', 'v' => 'center'])
            ->border('A2:T2', 'bottom', 'medium', self::NAVY);
    }

    private function info(array $d): void
    {
        $x = $this->x;
        $text = fn ($v) => ': ' . $v;
        // [kolom label, kolom nilai (gabung sampai), label, nilai, format angka]
        $cells = [
            ['A', 'C', 'No Costing', $text($d['no'])], ['A', 'C', 'Revision', $d['revision'], '": "0'], ['A', 'C', 'Costing Date', $text($d['date'])],
            ['A', 'C', 'Status', $text($d['status'])], ['A', 'C', 'Buyer', $text($d['buyer'])], ['A', 'C', 'Brand', $text($d['brand'])],
            ['E', 'G', 'Season', $text($d['season'])], ['E', 'G', 'Style', $text($d['style'])], ['E', 'G', 'Product Group', $text($d['product_group'])],
            ['E', 'G', 'Product Item', $text($d['product_item'])], ['E', 'G', 'Costing Type', $text($d['type'])], ['E', 'G', 'Unit', $text($d['unit'])],
            ['H', 'J', 'Main Destination', $text($d['destination'])], ['H', 'J', 'Marketing Office', $text($d['office'])], ['H', 'J', 'Planned Qty', $d['qty'], '": "#,##0'],
            ['H', 'J', 'SMV', $d['smv'], '": "0.00'], ['H', 'J', 'Currency', $text($d['currency'])], null,
            ['L', 'N', 'Rate USD to IDR', $d['rate_usd_idr'], '": "#,##0.00'], ['L', 'N', 'Rate IDR to USD', $d['rate_idr_usd'], '": "#,##0.00'], ['L', 'N', 'VAT %', $d['vat'], '": "0.00'],
        ];
        $merge = ['A' => ['A:B', 'C:D'], 'E' => ['E:F', null], 'H' => ['H:I', 'J:K'], 'L' => ['L:M', 'N:P']];
        $x->style('A4:T9', ['fill' => 'F5F8FC']);
        foreach ($cells as $i => $cell) {
            if (!$cell) {
                continue;
            }
            $row = 4 + ($i % 6);
            [$labelCol, $valueCol, $label, $value] = $cell;
            [$labelSpan, $valueSpan] = $merge[$labelCol];
            $x->merge(str_replace(':', $row . ':', $labelSpan) . $row);
            if ($valueSpan) {
                $x->merge(str_replace(':', $row . ':', $valueSpan) . $row);
            }
            $x->set($labelCol . $row, $label, ['size' => 10, 'color' => '555555'])
                ->set($valueCol . $row, $value, ['bold' => true, 'size' => 10] + (isset($cell[4]) ? ['format' => $cell[4]] : []));
        }
        // Baris yang hanya punya pasangan di kolom A/E (6 baris) tetap digabung rapi di kolom H & L.
        foreach ([4, 5, 6, 7, 8, 9] as $row) {
            foreach (['H' => ['H:I', 'J:K'], 'L' => ['L:M', 'N:P']] as $spans) {
                foreach ($spans as $span) {
                    $range = str_replace(':', $row . ':', $span) . $row;
                    if (!$x->isMerged($range)) {
                        $x->merge($range);
                    }
                }
            }
            $x->height($row, 15.95);
        }
        $x->style('A4:T9', ['h' => 'left', 'v' => 'center'])->border('A9:T9', 'bottom', 'thin', self::LINE)->height(10, 8.1);

        if ($d['header_image']) {
            // Gambar yang pertama diupload, di kotak S4:T9.
            $x->image($d['header_image']['path'], 'S4', 160, 120);
        }
    }

    private function title(int $row, string $text): void
    {
        $this->x->set("A{$row}", $text, ['bold' => true, 'size' => 10, 'color' => self::NAVY])->height($row, 18);
    }

    private function tableHead(int $row, array $dePair): void
    {
        $heads = ['A' => 'NO', 'B' => 'CONTENT', 'D' => $dePair[0], 'E' => $dePair[1], 'F' => 'SET', 'G' => 'DESCRIPTION', 'H' => 'SUPPLIER', 'I' => 'CURR', 'J' => 'PRICE',
            'K' => 'PRICE IDR', 'L' => 'PRICE USD', 'M' => 'CONS/PC', 'N' => 'UNIT', 'O' => 'ALLOW %', 'P' => 'VALUE IDR', 'Q' => 'VALUE USD', 'R' => '%', 'S' => 'QTY BOM', 'T' => 'VALUE'];
        $this->x->merge("B{$row}:C{$row}")->row($row, $heads)
            ->style("A{$row}:T{$row}", ['bold' => true, 'size' => 9, 'color' => 'FFFFFF', 'fill' => self::NAVY, 'h' => 'center', 'v' => 'center', 'wrap' => true])
            ->height($row, 21.95);
        $this->borders("A{$row}:T{$row}");
    }

    /** Satu kelompok (FABRIC, ACC SEWING, ..., OTHER): baris judul, baris detail, baris TOTAL. Kembalikan [baris berikut, baris total]. */
    private function group(int $r, array $group, bool $charge): array
    {
        $x = $this->x;
        $other = $group['label'] === 'OTHER';
        $x->merge("A{$r}:T{$r}")->set("A{$r}", $group['label'])
            ->style("A{$r}:T{$r}", ['bold' => true, 'size' => 10, 'color' => '1C3447', 'fill' => 'C7DCFF', 'v' => 'center'])->height($r, 18);
        $this->borders("A{$r}:T{$r}");
        $r++;

        $first = $r;
        $usd = $other ? '$N$4' : '$N$5';
        foreach ($group['rows'] as $i => $row) {
            $values = [
                'A' => $i + 1, 'B' => $row['content'],
                'D' => $charge ? $row['calc'] : $row['shell'], 'E' => $charge ? $row['subcont'] : $row['part'],
                'F' => $row['set'], 'G' => $row['desc'], 'H' => $row['supplier'], 'I' => $row['curr'], 'J' => round($row['price'], 7),
                'K' => "=IF(I{$r}=\"USD\",J{$r}*\$N\$4,J{$r})", 'L' => "=K{$r}/{$usd}", 'M' => $row['cons'], 'N' => $row['unit'], 'O' => $row['allow'],
                'P' => "=K{$r}*M{$r}*(1+O{$r}/100)", 'Q' => "=P{$r}/{$usd}",
            ];
            if (!$other) {
                $values['S'] = "=ROUNDUP(M{$r}*\$J\$6*(1+O{$r}/100),0)";
                $values['T'] = "=S{$r}*K{$r}";
            }
            $x->merge("B{$r}:C{$r}")->row($r, $values);
            $this->pctCells[] = ['R', $r];
            $this->detailStyle($r, $values);
            $r++;
        }
        $last = $r - 1;

        $sum = fn ($c) => $last >= $first ? "SUM({$c}{$first}:{$c}{$last})" : '0';
        $this->totalRow($r, 'TOTAL ' . $group['label'], 'EEF3FA', $sum, !$other);
        $this->pctCells[] = ['R', $r];

        return [$r + 1, $r];
    }

    private function detailStyle(int $r, array $values): void
    {
        $x = $this->x;
        $this->borders("A{$r}:T{$r}");
        $x->style("A{$r}:T{$r}", ['size' => 10, 'v' => 'center'])
            ->style("A{$r}", ['h' => 'center'])
            ->style("B{$r}:C{$r}", ['h' => 'left', 'wrap' => true])
            // DESCRIPTION rata kiri (tengah bila "-"), SUPPLIER rata tengah; keduanya boleh turun baris.
            ->style("G{$r}", ['h' => ($values['G'] ?? '-') === '-' ? 'center' : 'left', 'wrap' => true])
            ->style("H{$r}", ['h' => 'center', 'wrap' => true]);
        // SHELL / PART (material) rata kiri bila berisi; CALC TYPE / SUBCONT (charge) rata tengah.
        foreach (['D', 'E'] as $col) {
            $isText = ($values[$col] ?? '-') !== '-' && !preg_match('/^(SMV|PERCENT|FIXED|Y$|N$)/', (string) $values[$col]);
            $x->style($col . $r, ['h' => $isText ? 'left' : 'center']);
        }
        foreach (['F', 'I', 'N'] as $col) {
            $x->style($col . $r, ['h' => 'center']);
        }
        foreach (['D', 'E', 'F', 'G', 'H'] as $col) {
            if (($values[$col] ?? null) === '-') {
                $x->style($col . $r, ['color' => 'A0A8B5']);
            }
        }
        $this->numbers($r);
        $x->style("P{$r}:Q{$r}", ['bold' => true])->style("T{$r}", ['bold' => true]);

        // Tinggi baris mengikuti teks terpanjang yang turun baris (CONTENT, DESCRIPTION, SUPPLIER).
        $lines = 1;
        foreach (['B' => 24, 'G' => 22, 'H' => 12] as $col => $chars) {
            $lines = max($lines, (int) ceil(mb_strlen((string) ($values[$col] ?? '')) / $chars));
        }
        $x->height($r, max(15.95, $lines * 12.6 + 4));
    }

    private function numbers(int $r): void
    {
        foreach (['J' => self::MONEY, 'K' => self::MONEY, 'L' => self::MONEY, 'M' => self::CONS, 'O' => self::ALLOW, 'P' => self::MONEY, 'Q' => self::USD, 'R' => self::PCT, 'S' => self::QTY, 'T' => self::MONEY] as $col => $format) {
            $this->x->style($col . $r, ['format' => $format, 'h' => 'right']);
        }
    }

    private function totalRow(int $r, string $label, string $fill, callable $formula, bool $withBom): void
    {
        $x = $this->x;
        $this->band("A{$r}:T{$r}", $fill, true);
        $x->merge("A{$r}:O{$r}")->set("A{$r}", $label, ['h' => 'left']);
        foreach ($withBom ? ['P', 'Q', 'S', 'T'] : ['P', 'Q'] as $col) {
            $x->set($col . $r, '=' . $formula($col));
        }
        $this->numbers($r);
        $x->height($r, $fill === 'DFE8F5' ? 18 : 15.95);
    }

    private function band(string $range, string $fill, bool $bold): void
    {
        $this->x->style($range, ['fill' => $fill, 'bold' => $bold, 'size' => 10, 'v' => 'center']);
        $this->borders($range);
    }

    private function borders(string $range): void
    {
        $this->x->border($range, 'all', 'thin', self::LINE);
    }

    private function summary(int $top, array $d, int $matRow, int $mfgRow, int $otherRow): void
    {
        $x = $this->x;
        $s = $d['summary'];
        $x->merge("M{$top}:O{$top}")->row($top, ['M' => 'SUMMARY', 'P' => '%', 'Q' => 'VALUE IDR', 'R' => 'VALUE USD'])
            ->style("M{$top}:R{$top}", ['bold' => true, 'size' => 9, 'color' => 'FFFFFF', 'fill' => self::NAVY, 'h' => 'center', 'v' => 'center'])
            ->style("M{$top}", ['h' => 'left'])->height($top, 18);
        $this->borders("M{$top}:R{$top}");

        $r1 = $top + 1;
        $rows = [
            ['Total Material', null, "=P{$matRow}", "=Q{$matRow}"],
            ['Total Manufacturing', null, "=P{$mfgRow}", "=Q{$mfgRow}"],
            ['Total Other Cost', null, "=P{$otherRow}", "=Q{$otherRow}"],
            ['G&A', $s['ga'][0], '=(Q' . $r1 . '+Q' . ($r1 + 1) . '+Q' . ($r1 + 2) . ')*P' . ($r1 + 3) . '/100', '=Q' . ($r1 + 3) . '/$N$4'],
            ['G&A Extra', $s['ga_extra'][0], '=(Q' . $r1 . '+Q' . ($r1 + 1) . '+Q' . ($r1 + 2) . ')*P' . ($r1 + 4) . '/100', '=Q' . ($r1 + 4) . '/$N$4'],
            ['TOTAL COST', null, '=(Q' . $r1 . '+Q' . ($r1 + 1) . '+Q' . ($r1 + 2) . ')+Q' . ($r1 + 3) . '+Q' . ($r1 + 4), '=Q' . ($r1 + 5) . '/$N$4'],
            ['Deal Allowance', $s['deal'], null, null],
            ['Cost + VAT', '=$N$6', '=Q' . ($r1 + 5) . '*(1+P' . ($r1 + 7) . '/100)', '=Q' . ($r1 + 7) . '/$N$4'],
            ['Cost + VAT + Profit', $s['profit'][0], '=Q' . ($r1 + 7) . '*(1+P' . ($r1 + 8) . '/100)', '=Q' . ($r1 + 8) . '/$N$4'],
            ['FOB PRICE / PCS', null, '=Q' . ($r1 + 8), '=Q' . ($r1 + 9) . '/$N$4'],
        ];
        foreach ($rows as $i => [$label, $pct, $idr, $usd]) {
            $r = $r1 + $i;
            $x->merge("M{$r}:O{$r}")->set("M{$r}", $label);
            if ($pct !== null) {
                $x->set("P{$r}", $pct, ['format' => self::PCT_INPUT]);
            }
            if ($idr !== null) {
                $x->set("Q{$r}", $idr);
            }
            if ($usd !== null) {
                $x->set("R{$r}", $usd);
            }
            $x->style("Q{$r}", ['format' => self::MONEY])->style("R{$r}", ['format' => self::USD])
                ->style("P{$r}:R{$r}", ['h' => 'right'])->style("M{$r}:R{$r}", ['size' => 10, 'v' => 'center'])->style("M{$r}", ['bold' => true]);
            $strong = in_array($label, ['TOTAL COST', 'FOB PRICE / PCS'], true);
            $fill = $label === 'TOTAL COST' ? 'DFE8F5' : ($label === 'FOB PRICE / PCS' ? self::NAVY : 'EEF3FA');
            $x->style($strong ? "M{$r}:R{$r}" : "M{$r}", ['fill' => $fill]);
            if ($strong) {
                $x->style("M{$r}:R{$r}", ['bold' => true]);
            }
            if ($label === 'FOB PRICE / PCS') {
                $x->style("M{$r}:R{$r}", ['color' => 'FFFFFF']);
            }
            $this->borders("M{$r}:R{$r}");
            $x->height($r, 15.95);
        }
    }

    private function notesAndSign(int $top, array $d): void
    {
        $x = $this->x;
        $x->merge('A' . $top . ':H' . ($top + 1))->set("A{$top}", 'Notes : ' . $d['notes'])
            ->style('A' . $top . ':H' . ($top + 1), ['size' => 10, 'color' => '555555', 'v' => 'top', 'wrap' => true])
            ->border('A' . $top . ':H' . ($top + 1), 'outline', 'thin', self::LINE);

        $sign = $top + 3;
        foreach ([['A', 'D', 'Created by', $d['creator'], $d['created_at']], ['F', 'G', 'Approved by', '', '']] as [$from, $to, $label, $who, $when]) {
            for ($r = $sign; $r <= $sign + 4; $r++) {
                $x->merge("{$from}{$r}:{$to}{$r}");
            }
            $x->set("{$from}{$sign}", $label, ['size' => 10, 'color' => '555555'])
                ->set($from . ($sign + 3), (string) $who, ['bold' => true, 'size' => 10, 'v' => 'bottom'])
                ->set($from . ($sign + 4), (string) $when, ['size' => 8, 'color' => '666666'])
                ->border("{$from}{$sign}:{$to}" . ($sign + 4), 'outline', 'thin', self::LINE)
                ->border("{$from}" . ($sign + 3) . ":{$to}" . ($sign + 3), 'top', 'thin', self::LINE);
        }
    }
}
