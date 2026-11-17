<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Excel Costing DB baru, meniru "excel costing.xlsx": kolom A-T, rumus hidup (PRICE IDR, PRICE USD, VALUE, %, QTY BOM,
 * total, summary G&A s.d. FOB), gambar pertama di header, semua gambar di bagian C. IMAGES.
 * Data dari Marketing_CostingNdsController::costingDocument() (sama dengan PDF).
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

    private Worksheet $ws;

    public function download(array $d)
    {
        $book = new Spreadsheet();
        $book->getDefaultStyle()->getFont()->setName('Arial')->setSize(10);
        $this->ws = $ws = $book->getActiveSheet();
        $ws->setTitle('Costing');
        $ws->setShowGridlines(false);
        foreach (['A' => 5, 'B' => 13, 'C' => 13, 'D' => 15, 'E' => 15, 'F' => 6, 'G' => 24, 'H' => 14, 'I' => 7, 'J' => 11, 'K' => 12, 'L' => 11, 'M' => 9, 'N' => 7, 'O' => 9, 'P' => 14, 'Q' => 12, 'R' => 11, 'S' => 11, 'T' => 13] as $col => $width) {
            $ws->getColumnDimension($col)->setWidth($width);
        }

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
        $this->totalRow($matRow, 'TOTAL MATERIAL', 'DFE8F5', fn ($c) => implode('+', array_map(fn ($t) => $c . $t, $matTotals)), true);
        $this->pctCells[] = ['R', $matRow];
        $r = $matRow + 1;
        $ws->getRowDimension($r)->setRowHeight(8.1);

        // B. MANUFACTURING & OTHER COST
        $r++;
        $this->title($r, 'B. MANUFACTURING & OTHER COST');
        $this->tableHead($r + 1, ['CALC TYPE', 'SUBCONT']);
        $r += 2;
        [$r, $mfgRow] = $this->group($r, $d['manufacturing'], true);
        [$r, $otherRow] = $this->group($r, $d['other'], true);
        $grandRow = $r;
        $this->band("A{$grandRow}:T{$grandRow}", 'DFE8F5', true);
        $ws->mergeCells("A{$grandRow}:O{$grandRow}");
        $ws->setCellValue("A{$grandRow}", 'GRAND TOTAL (A + B)');
        $ws->getStyle("A{$grandRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $ws->setCellValue("S{$grandRow}", "=S{$matRow}+S{$mfgRow}");
        $ws->setCellValue("T{$grandRow}", "=T{$matRow}+T{$mfgRow}");
        $this->numbers($grandRow);
        $ws->getRowDimension($grandRow)->setRowHeight(18);

        // Notes, tanda tangan, summary
        $r = $grandRow + 1;
        $ws->getRowDimension($r)->setRowHeight(9.95);
        $top = $r + 1;
        $this->summary($top, $d, $matRow, $mfgRow, $otherRow);
        $totalCostRow = $top + 6;
        // Kolom % tiap baris = VALUE IDR / TOTAL COST di summary.
        foreach ($this->pctCells as $cell) {
            [$col, $row] = $cell;
            $ws->setCellValue("R{$row}", "=IF(\$Q\${$totalCostRow}=0,0,P{$row}/\$Q\${$totalCostRow})");
        }
        $this->notesAndSign($top, $d);

        // C. IMAGES
        $last = $top + 10;
        if ($d['images']) {
            $r = $top + 12;
            $this->title($r, 'C. IMAGES (' . count($d['images']) . ')');
            $r++;
            foreach (array_chunk($d['images'], 5) as $chunkIndex => $chunk) {
                $ws->getRowDimension($r)->setRowHeight(120);
                foreach ($chunk as $k => $image) {
                    $col = ['A', 'E', 'I', 'M', 'Q'][$k];
                    $this->image($image['path'], "{$col}{$r}", 150, 155, 4, 2);
                    $ws->setCellValue($col . ($r + 1), ($chunkIndex * 5 + $k + 1) . '. ' . $image['type']);
                    $ws->getStyle($col . ($r + 1))->getFont()->setBold(true)->getColor()->setRGB(self::NAVY);
                    $ws->setCellValue($col . ($r + 2), $image['name']);
                    $ws->getStyle($col . ($r + 2))->getFont()->setSize(8)->getColor()->setRGB('666666');
                }
                $r += 4;
            }
            $last = $r;
        }

        $ws->freezePane('A' . ($headerRow + 1));
        $setup = $ws->getPageSetup();
        $setup->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)->setPaperSize(PageSetup::PAPERSIZE_A4)
            ->setFitToWidth(1)->setFitToHeight(0)->setPrintArea("A1:T{$last}");
        $ws->getPageMargins()->setLeft(0.4)->setRight(0.4)->setTop(0.4)->setBottom(0.5);
        $ws->getHeaderFooter()->setOddFooter('&L&7 ' . $d['no'] . ' Rev ' . $d['revision'] . '&R&7 Halaman &P dari &N');
        $ws->setSelectedCell('A1');

        $file = tempnam(sys_get_temp_dir(), 'cst') . '.xlsx';
        (new Xlsx($book))->save($file);

        return response()->download($file, 'Costing_' . str_replace('/', '-', $d['no']) . '.xlsx')->deleteFileAfterSend(true);
    }

    /** Sel kolom % yang rumusnya diisi setelah posisi TOTAL COST diketahui. */
    private array $pctCells = [];

    private function header(array $d): void
    {
        $ws = $this->ws;
        $ws->getRowDimension(1)->setRowHeight(21.95);
        $ws->getRowDimension(2)->setRowHeight(15.95);
        $ws->getRowDimension(3)->setRowHeight(6);
        $this->image(public_path('assets/dist/img/nag-mark.png'), 'A1', 57, 34, 0, 0);
        $ws->mergeCells('B1:H1');
        $ws->setCellValue('B1', '      PT NIRWANA ALABARE GARMENT');
        $ws->getStyle('B1')->getFont()->setBold(true)->setSize(14)->getColor()->setRGB(self::NAVY);
        $ws->mergeCells('B2:H2');
        $ws->setCellValue('B2', '        Marketing');
        $ws->getStyle('B2')->getFont()->setSize(10)->getColor()->setRGB('666666');
        $ws->mergeCells('M1:T1');
        $ws->setCellValue('M1', 'COSTING');
        $ws->getStyle('M1')->getFont()->setBold(true)->setSize(18)->getColor()->setRGB(self::NAVY);
        $ws->mergeCells('M2:T2');
        $ws->setCellValue('M2', $d['no']);
        $ws->getStyle('M2')->getFont()->setBold(true)->setSize(11);
        $ws->getStyle('M1:T2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT)->setVertical(Alignment::VERTICAL_CENTER);
        $ws->getStyle('A2:T2')->getBorders()->getBottom()->setBorderStyle(Border::BORDER_MEDIUM)->getColor()->setRGB(self::NAVY);
    }

    private function info(array $d): void
    {
        $ws = $this->ws;
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
        $ws->getStyle('A4:T9')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F5F8FC');
        foreach ($cells as $i => $cell) {
            if (!$cell) {
                continue;
            }
            $row = 4 + ($i % 6);
            [$labelCol, $valueCol, $label, $value] = $cell;
            [$labelSpan, $valueSpan] = $merge[$labelCol];
            $ws->mergeCells(str_replace(':', $row . ':', $labelSpan) . $row);
            if ($valueSpan) {
                $ws->mergeCells(str_replace(':', $row . ':', $valueSpan) . $row);
            }
            $ws->setCellValue($labelCol . $row, $label);
            $ws->getStyle($labelCol . $row)->getFont()->setSize(10)->getColor()->setRGB('555555');
            $ws->setCellValue($valueCol . $row, $value);
            $style = $ws->getStyle($valueCol . $row);
            $style->getFont()->setBold(true)->setSize(10);
            if (isset($cell[4])) {
                $style->getNumberFormat()->setFormatCode($cell[4]);
            }
        }
        // Baris yang hanya punya pasangan di kolom A/E (6 baris) tetap digabung rapi di kolom H & L.
        foreach ([4, 5, 6, 7, 8, 9] as $row) {
            foreach (['H' => ['H:I', 'J:K'], 'L' => ['L:M', 'N:P']] as $spans) {
                foreach ($spans as $span) {
                    $range = str_replace(':', $row . ':', $span) . $row;
                    if (!$this->isMerged($range)) {
                        $ws->mergeCells($range);
                    }
                }
            }
            $ws->getRowDimension($row)->setRowHeight(15.95);
        }
        $ws->getStyle('A4:T9')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_CENTER);
        $ws->getStyle('A9:T9')->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB(self::LINE);
        $ws->getRowDimension(10)->setRowHeight(8.1);

        if ($d['header_image']) {
            // Gambar yang pertama diupload, di kotak S4:T9.
            $this->image($d['header_image']['path'], 'S4', 160, 120, 4, 2);
        }
    }

    private function isMerged(string $range): bool
    {
        return isset($this->ws->getMergeCells()[$range]);
    }

    private function title(int $row, string $text): void
    {
        $this->ws->setCellValue("A{$row}", $text);
        $this->ws->getStyle("A{$row}")->getFont()->setBold(true)->setSize(10)->getColor()->setRGB(self::NAVY);
        $this->ws->getRowDimension($row)->setRowHeight(18);
    }

    private function tableHead(int $row, array $dePair): void
    {
        $ws = $this->ws;
        $heads = ['A' => 'NO', 'B' => 'CONTENT', 'D' => $dePair[0], 'E' => $dePair[1], 'F' => 'SET', 'G' => 'DESCRIPTION', 'H' => 'SUPPLIER', 'I' => 'CURR', 'J' => 'PRICE',
            'K' => 'PRICE IDR', 'L' => 'PRICE USD', 'M' => 'CONS/PC', 'N' => 'UNIT', 'O' => 'ALLOW %', 'P' => 'VALUE IDR', 'Q' => 'VALUE USD', 'R' => '%', 'S' => 'QTY BOM', 'T' => 'VALUE'];
        $ws->mergeCells("B{$row}:C{$row}");
        foreach ($heads as $col => $text) {
            $ws->setCellValue($col . $row, $text);
        }
        $style = $ws->getStyle("A{$row}:T{$row}");
        $style->getFont()->setBold(true)->setSize(9)->getColor()->setRGB('FFFFFF');
        $style->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::NAVY);
        $style->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
        $this->borders("A{$row}:T{$row}");
        $ws->getRowDimension($row)->setRowHeight(21.95);
    }

    /** Satu kelompok (FABRIC, ACC SEWING, ..., OTHER): baris judul, baris detail, baris TOTAL. Kembalikan [baris berikut, baris total]. */
    private function group(int $r, array $group, bool $charge): array
    {
        $ws = $this->ws;
        $other = $group['label'] === 'OTHER';
        $ws->mergeCells("A{$r}:T{$r}");
        $ws->setCellValue("A{$r}", $group['label']);
        $style = $ws->getStyle("A{$r}:T{$r}");
        $style->getFont()->setBold(true)->setSize(10)->getColor()->setRGB('1C3447');
        $style->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('C7DCFF');
        $style->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $this->borders("A{$r}:T{$r}");
        $ws->getRowDimension($r)->setRowHeight(18);
        $r++;

        $first = $r;
        $usd = $other ? '$N$4' : '$N$5';
        foreach ($group['rows'] as $i => $row) {
            $ws->mergeCells("B{$r}:C{$r}");
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
            foreach ($values as $col => $value) {
                $ws->setCellValueExplicit($col . $r, $value, is_string($value) && str_starts_with($value, '=') ? \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_FORMULA
                    : (is_string($value) ? \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING : \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC));
            }
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
        $ws = $this->ws;
        $this->borders("A{$r}:T{$r}");
        $ws->getStyle("A{$r}:T{$r}")->getFont()->setSize(10);
        $ws->getStyle("A{$r}:T{$r}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $ws->getStyle("A{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $ws->getStyle("B{$r}:C{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setWrapText(true);
        // DESCRIPTION rata kiri (tengah bila "-"), SUPPLIER rata tengah; keduanya boleh turun baris.
        $ws->getStyle("G{$r}")->getAlignment()->setHorizontal(($values['G'] ?? '-') === '-' ? Alignment::HORIZONTAL_CENTER : Alignment::HORIZONTAL_LEFT)->setWrapText(true);
        $ws->getStyle("H{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setWrapText(true);
        // SHELL / PART (material) rata kiri bila berisi; CALC TYPE / SUBCONT (charge) rata tengah.
        foreach (['D', 'E'] as $col) {
            $isText = ($values[$col] ?? '-') !== '-' && !preg_match('/^(SMV|PERCENT|FIXED|Y$|N$)/', (string) $values[$col]);
            $ws->getStyle($col . $r)->getAlignment()->setHorizontal($isText ? Alignment::HORIZONTAL_LEFT : Alignment::HORIZONTAL_CENTER);
        }
        foreach (['F', 'I', 'N'] as $col) {
            $ws->getStyle($col . $r)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }
        foreach (['D', 'E', 'F', 'G', 'H'] as $col) {
            if (($values[$col] ?? null) === '-') {
                $ws->getStyle($col . $r)->getFont()->getColor()->setRGB('A0A8B5');
            }
        }
        $this->numbers($r);
        $ws->getStyle("P{$r}:Q{$r}")->getFont()->setBold(true);
        $ws->getStyle("T{$r}")->getFont()->setBold(true);

        // Tinggi baris mengikuti teks terpanjang yang turun baris (CONTENT, DESCRIPTION, SUPPLIER).
        $lines = 1;
        foreach (['B' => 24, 'G' => 22, 'H' => 12] as $col => $chars) {
            $lines = max($lines, (int) ceil(mb_strlen((string) ($values[$col] ?? '')) / $chars));
        }
        $ws->getRowDimension($r)->setRowHeight(max(15.95, $lines * 12.6 + 4));
    }

    private function numbers(int $r): void
    {
        $ws = $this->ws;
        foreach (['J' => self::MONEY, 'K' => self::MONEY, 'L' => self::MONEY, 'M' => self::CONS, 'O' => self::ALLOW, 'P' => self::MONEY, 'Q' => self::USD, 'R' => self::PCT, 'S' => self::QTY, 'T' => self::MONEY] as $col => $format) {
            $ws->getStyle($col . $r)->getNumberFormat()->setFormatCode($format);
            $ws->getStyle($col . $r)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        }
    }

    private function totalRow(int $r, string $label, string $fill, callable $formula, bool $withBom): void
    {
        $ws = $this->ws;
        $this->band("A{$r}:T{$r}", $fill, true);
        $ws->mergeCells("A{$r}:O{$r}");
        $ws->setCellValue("A{$r}", $label);
        $ws->getStyle("A{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        foreach ($withBom ? ['P', 'Q', 'S', 'T'] : ['P', 'Q'] as $col) {
            $ws->setCellValue($col . $r, '=' . $formula($col));
        }
        $this->numbers($r);
        $ws->getRowDimension($r)->setRowHeight($fill === 'DFE8F5' ? 18 : 15.95);
    }

    private function band(string $range, string $fill, bool $bold): void
    {
        $style = $this->ws->getStyle($range);
        $style->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($fill);
        $style->getFont()->setBold($bold)->setSize(10);
        $style->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $this->borders($range);
    }

    private function borders(string $range): void
    {
        $this->ws->getStyle($range)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB(self::LINE);
    }

    private function summary(int $top, array $d, int $matRow, int $mfgRow, int $otherRow): void
    {
        $ws = $this->ws;
        $s = $d['summary'];
        $ws->mergeCells("M{$top}:O{$top}");
        foreach (['M' => 'SUMMARY', 'P' => '%', 'Q' => 'VALUE IDR', 'R' => 'VALUE USD'] as $col => $text) {
            $ws->setCellValue($col . $top, $text);
        }
        $head = $ws->getStyle("M{$top}:R{$top}");
        $head->getFont()->setBold(true)->setSize(9)->getColor()->setRGB('FFFFFF');
        $head->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::NAVY);
        $head->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $ws->getStyle("M{$top}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $this->borders("M{$top}:R{$top}");
        $ws->getRowDimension($top)->setRowHeight(18);

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
            $ws->mergeCells("M{$r}:O{$r}");
            $ws->setCellValue("M{$r}", $label);
            if ($pct !== null) {
                $ws->setCellValue("P{$r}", $pct);
                $ws->getStyle("P{$r}")->getNumberFormat()->setFormatCode(self::PCT_INPUT);
            }
            if ($idr !== null) {
                $ws->setCellValue("Q{$r}", $idr);
            }
            if ($usd !== null) {
                $ws->setCellValue("R{$r}", $usd);
            }
            $ws->getStyle("Q{$r}")->getNumberFormat()->setFormatCode(self::MONEY);
            $ws->getStyle("R{$r}")->getNumberFormat()->setFormatCode(self::USD);
            $ws->getStyle("P{$r}:R{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $ws->getStyle("M{$r}:R{$r}")->getFont()->setSize(10);
            $ws->getStyle("M{$r}:R{$r}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $ws->getStyle("M{$r}")->getFont()->setBold(true);
            $fill = $label === 'TOTAL COST' ? 'DFE8F5' : ($label === 'FOB PRICE / PCS' ? self::NAVY : 'EEF3FA');
            $range = in_array($label, ['TOTAL COST', 'FOB PRICE / PCS'], true) ? "M{$r}:R{$r}" : "M{$r}";
            $ws->getStyle($range)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($fill);
            if (in_array($label, ['TOTAL COST', 'FOB PRICE / PCS'], true)) {
                $ws->getStyle("M{$r}:R{$r}")->getFont()->setBold(true);
            }
            if ($label === 'FOB PRICE / PCS') {
                $ws->getStyle("M{$r}:R{$r}")->getFont()->getColor()->setRGB('FFFFFF');
            }
            $this->borders("M{$r}:R{$r}");
            $ws->getRowDimension($r)->setRowHeight(15.95);
        }
    }

    private function notesAndSign(int $top, array $d): void
    {
        $ws = $this->ws;
        $ws->mergeCells('A' . $top . ':H' . ($top + 1));
        $ws->setCellValue("A{$top}", 'Notes : ' . $d['notes']);
        $notes = $ws->getStyle('A' . $top . ':H' . ($top + 1));
        $notes->getFont()->setSize(10)->getColor()->setRGB('555555');
        $notes->getAlignment()->setVertical(Alignment::VERTICAL_TOP)->setWrapText(true);
        $notes->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB(self::LINE);

        $sign = $top + 3;
        foreach ([['A', 'D', 'Created by', $d['creator'], $d['created_at']], ['F', 'G', 'Approved by', '', '']] as [$from, $to, $label, $who, $when]) {
            for ($r = $sign; $r <= $sign + 4; $r++) {
                $ws->mergeCells("{$from}{$r}:{$to}{$r}");
            }
            $ws->setCellValue("{$from}{$sign}", $label);
            $ws->getStyle("{$from}{$sign}")->getFont()->setSize(10)->getColor()->setRGB('555555');
            $ws->setCellValue($from . ($sign + 3), $who);
            $ws->getStyle($from . ($sign + 3))->getFont()->setBold(true)->setSize(10);
            $ws->getStyle($from . ($sign + 3))->getAlignment()->setVertical(Alignment::VERTICAL_BOTTOM);
            $ws->setCellValue($from . ($sign + 4), $when);
            $ws->getStyle($from . ($sign + 4))->getFont()->setSize(8)->getColor()->setRGB('666666');
            $box = $ws->getStyle("{$from}{$sign}:{$to}" . ($sign + 4));
            $box->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB(self::LINE);
            $ws->getStyle("{$from}" . ($sign + 3) . ":{$to}" . ($sign + 3))->getBorders()->getTop()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB(self::LINE);
        }
    }

    /** Gambar dimuat ke dalam kotak maks $maxW x $maxH px, perbandingan tetap. */
    private function image(string $path, string $cell, int $maxW, int $maxH, int $offX, int $offY): void
    {
        if (!is_file($path) || !($size = @getimagesize($path))) {
            return;
        }
        $scale = min($maxW / $size[0], $maxH / $size[1], 1);
        $drawing = new Drawing();
        $drawing->setPath($path);
        $drawing->setCoordinates($cell);
        $drawing->setResizeProportional(false);
        $drawing->setWidth((int) round($size[0] * $scale));
        $drawing->setHeight((int) round($size[1] * $scale));
        $drawing->setOffsetX($offX);
        $drawing->setOffsetY($offY);
        $drawing->setWorksheet($this->ws);
    }
}
