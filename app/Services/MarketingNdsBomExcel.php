<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Excel BOM DB baru, meniru "excel bom.xlsx": satu sheet per WS, kolom A-R, rumus hidup
 * (QTY REQ, VALUE, TOTAL QTY REQ). Data dari MarketingNdsDocumentService::bom()['print'] (sama dengan PDF).
 */
class MarketingNdsBomExcel
{
    private const NAVY = '284A63';
    private const LINE = 'B8C2D1';
    private const WIDTHS = ['A' => 6, 'B' => 18, 'C' => 15, 'D' => 30, 'E' => 15, 'F' => 12, 'G' => 16, 'H' => 10, 'I' => 9,
        'J' => 7, 'K' => 12, 'L' => 8, 'M' => 10, 'N' => 13, 'O' => 7, 'P' => 22, 'Q' => 12, 'R' => 12];

    private Worksheet $ws;

    public function build(array $docs): Spreadsheet
    {
        $book = new Spreadsheet();
        $book->getDefaultStyle()->getFont()->setName('Arial')->setSize(10);
        $book->removeSheetByIndex(0);
        $titles = [];
        foreach ($docs as $doc) {
            $p = $doc['print'];
            $title = substr(preg_replace('/[\\\\\/?*\[\]:]+/', '-', $p['no'] !== '-' ? $p['no'] : (string) $p['no_ws']), 0, 31) ?: 'BOM';
            while (in_array($title, $titles, true)) {
                $title = substr($title, 0, 28) . '-' . count($titles);
            }
            $titles[] = $title;
            $this->ws = $book->createSheet();
            $this->ws->setTitle($title);
            $this->sheet($p, $doc['image'] ?? null);
        }
        if (!$docs) {
            $book->createSheet()->setTitle('BOM');
        }
        $book->setActiveSheetIndex(0);

        return $book;
    }

    private function sheet(array $p, ?string $image): void
    {
        $ws = $this->ws;
        $ws->setShowGridlines(false);
        foreach (self::WIDTHS as $col => $width) {
            $ws->getColumnDimension($col)->setWidth($width);
        }
        $this->header($p);
        $this->info($p, $image);

        // A. MATERIAL
        $r = 11;
        $this->title($r, 'A. MATERIAL');
        $head = $r + 1;
        $this->headRow($head, ['A' => 'NO', 'B' => 'CONTENT', 'C' => 'SHELL', 'D' => 'ITEMS', 'E' => 'COLOR', 'F' => 'SIZE', 'G' => 'DESTINATION',
            'H' => 'ORIG QTY', 'I' => 'CONS / PC', 'J' => 'UNIT', 'K' => 'QTY REQ', 'L' => 'LOSS %', 'M' => 'VALUE', 'N' => 'TOTAL QTY REQ',
            'O' => 'UNIT', 'P' => 'SUPPLIER', 'Q' => 'PRICE', 'R' => 'PRICE COSTING']);
        $r = $head + 1;
        foreach ($p['groups'] as $group) {
            $this->groupRow($r++, $group['label']);
            foreach ($group['rows'] as $row) {
                $this->materialRow($r++, $row);
            }
        }
        if (!$p['groups']) {
            $this->emptyRow($r++, 'Belum ada material.');
        }
        $ws->mergeCells("A{$r}:R{$r}");
        $ws->setCellValue("A{$r}", $p['note']);
        $ws->getStyle("A{$r}")->getFont()->setSize(8)->getColor()->setRGB('666666');
        $r += 2;

        // B. CHARGES
        $this->title($r, 'B. CHARGES');
        $r++;
        $spans = ['A' => null, 'B' => null, 'C' => 'D', 'E' => 'G', 'H' => null, 'I' => null, 'J' => 'K', 'L' => null, 'M' => 'R'];
        $this->spans($r, $spans);
        $this->headRow($r, ['A' => 'NO', 'B' => 'ITEM CODE', 'C' => 'ITEM', 'E' => 'SUPPLIER', 'H' => 'CONS', 'I' => 'UNIT', 'J' => 'PRICE', 'L' => 'CURR', 'M' => 'KETERANGAN']);
        $r++;
        foreach ($p['charges'] as $i => $c) {
            $this->spans($r, $spans);
            $this->put($r, ['A' => $i + 1, 'B' => $c['code'], 'C' => $c['item'], 'E' => $c['supplier'], 'H' => $c['cons'], 'I' => $c['unit'],
                'J' => $c['price'], 'L' => $c['currency'], 'M' => $c['note']]);
            $this->borders("A{$r}:R{$r}");
            $ws->getStyle("A{$r}:R{$r}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            foreach (['A', 'I', 'L'] as $col) {
                $ws->getStyle("{$col}{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            }
            $ws->getStyle("H{$r}")->getNumberFormat()->setFormatCode('#,##0.000');
            $ws->getStyle("J{$r}")->getNumberFormat()->setFormatCode('#,##0.00');
            $r++;
        }
        if (!$p['charges']) {
            $this->emptyRow($r++, 'Tidak ada charges.');
        }
        $r++;

        // Notes + tanda tangan
        $ws->mergeCells("A{$r}:R" . ($r + 1));
        $ws->setCellValue("A{$r}", 'Notes : ' . $p['notes']);
        $notes = $ws->getStyle("A{$r}:R" . ($r + 1));
        $notes->getFont()->getColor()->setRGB('555555');
        $notes->getAlignment()->setVertical(Alignment::VERTICAL_TOP)->setWrapText(true);
        $notes->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB(self::LINE);
        $sign = $r + 3;
        foreach ([['A', 'C', 'Created by', $p['creator'], $p['created_at']], ['E', 'G', 'Approved by', $p['approver'] ?? '', $p['approved_at']]] as [$from, $to, $label, $who, $when]) {
            for ($row = $sign; $row <= $sign + 4; $row++) {
                $ws->mergeCells("{$from}{$row}:{$to}{$row}");
            }
            $ws->setCellValue("{$from}{$sign}", $label);
            $ws->getStyle("{$from}{$sign}")->getFont()->getColor()->setRGB('555555');
            $ws->setCellValueExplicit($from . ($sign + 3), (string) $who, DataType::TYPE_STRING);
            $ws->getStyle($from . ($sign + 3))->getFont()->setBold(true);
            $ws->setCellValueExplicit($from . ($sign + 4), (string) $when, DataType::TYPE_STRING);
            $ws->getStyle($from . ($sign + 4))->getFont()->setSize(8)->getColor()->setRGB('666666');
            $ws->getStyle("{$from}{$sign}:{$to}" . ($sign + 4))->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB(self::LINE);
            $ws->getStyle("{$from}" . ($sign + 3) . ":{$to}" . ($sign + 3))->getBorders()->getTop()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB(self::LINE);
        }
        $last = $sign + 4;

        $ws->freezePane('A' . ($head + 1));
        $setup = $ws->getPageSetup();
        $setup->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)->setPaperSize(PageSetup::PAPERSIZE_A4)
            ->setFitToWidth(1)->setFitToHeight(0)->setPrintArea("A1:R{$last}");
        $ws->getPageMargins()->setLeft(0.4)->setRight(0.4)->setTop(0.4)->setBottom(0.5);
        $label = str_replace('&', '&&', 'BOM ' . $p['no'] . ' Rev ' . $p['revision'] . ' (' . $p['no_ws'] . ')');
        $ws->getHeaderFooter()->setOddFooter('&L&7 ' . $label . '&R&7 Halaman &P dari &N');
        $ws->setSelectedCell('A1');
    }

    private function header(array $p): void
    {
        $ws = $this->ws;
        $ws->getRowDimension(1)->setRowHeight(21.95);
        $ws->getRowDimension(2)->setRowHeight(15.95);
        $ws->getRowDimension(3)->setRowHeight(6);
        $this->image(public_path('assets/dist/img/nag-mark.png'), 'A1', 57, 34, 0, 0);
        $ws->mergeCells('B1:K1');
        $ws->setCellValue('B1', '      PT NIRWANA ALABARE GARMENT');
        $ws->getStyle('B1')->getFont()->setBold(true)->setSize(14)->getColor()->setRGB(self::NAVY);
        $ws->mergeCells('B2:K2');
        $ws->setCellValue('B2', '        Marketing');
        $ws->getStyle('B2')->getFont()->getColor()->setRGB('666666');
        $ws->mergeCells('L1:R1');
        $ws->setCellValue('L1', 'BILL OF MATERIAL');
        $ws->getStyle('L1')->getFont()->setBold(true)->setSize(18)->getColor()->setRGB(self::NAVY);
        $ws->mergeCells('L2:R2');
        $ws->setCellValueExplicit('L2', $p['no'], DataType::TYPE_STRING);
        $ws->getStyle('L2')->getFont()->setBold(true)->setSize(11);
        $ws->getStyle('L1:R2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT)->setVertical(Alignment::VERTICAL_CENTER);
        $ws->getStyle('A2:R2')->getBorders()->getBottom()->setBorderStyle(Border::BORDER_MEDIUM)->getColor()->setRGB(self::NAVY);
    }

    /** Tiga kolom info: [kolom label, rentang label, kolom nilai, rentang nilai]; gambar Costing di Q4:R9. */
    private function info(array $p, ?string $image): void
    {
        $ws = $this->ws;
        $layout = [['A', 'A:B', 'C', null], ['D', 'D:E', 'F', 'F:H'], ['J', 'J:L', 'M', 'M:P']];
        $ws->getStyle('A4:R9')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F5F8FC');
        foreach ($p['info'] as $k => $pairs) {
            [$labelCol, $labelSpan, $valueCol, $valueSpan] = $layout[$k];
            for ($i = 0; $i < 6; $i++) {
                $row = 4 + $i;
                $ws->mergeCells(str_replace(':', $row . ':', $labelSpan) . $row);
                if ($valueSpan) {
                    $ws->mergeCells(str_replace(':', $row . ':', $valueSpan) . $row);
                }
                if (!isset($pairs[$i])) {
                    continue;
                }
                $ws->setCellValue($labelCol . $row, $pairs[$i][0]);
                $ws->getStyle($labelCol . $row)->getFont()->getColor()->setRGB('555555');
                $ws->setCellValueExplicit($valueCol . $row, ': ' . $pairs[$i][1], DataType::TYPE_STRING);
                $ws->getStyle($valueCol . $row)->getFont()->setBold(true);
            }
        }
        foreach (range(4, 9) as $row) {
            $ws->getRowDimension($row)->setRowHeight(15.95);
        }
        $ws->getStyle('A4:R9')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_CENTER);
        $ws->getRowDimension(10)->setRowHeight(8.1);
        if ($image) {
            $this->image($image, 'Q4', 120, 120, 4, 2);
        }
    }

    private function title(int $row, string $text): void
    {
        $this->ws->setCellValue("A{$row}", $text);
        $this->ws->getStyle("A{$row}")->getFont()->setBold(true)->getColor()->setRGB(self::NAVY);
        $this->ws->getRowDimension($row)->setRowHeight(18);
    }

    private function headRow(int $row, array $heads): void
    {
        $ws = $this->ws;
        foreach ($heads as $col => $text) {
            $ws->setCellValue($col . $row, $text);
        }
        $style = $ws->getStyle("A{$row}:R{$row}");
        $style->getFont()->setBold(true)->setSize(9)->getColor()->setRGB('FFFFFF');
        $style->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::NAVY);
        $style->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
        $this->borders("A{$row}:R{$row}");
        $ws->getRowDimension($row)->setRowHeight(24);
    }

    private function groupRow(int $r, string $label): void
    {
        $ws = $this->ws;
        $ws->mergeCells("A{$r}:R{$r}");
        $ws->setCellValue("A{$r}", $label);
        $style = $ws->getStyle("A{$r}:R{$r}");
        $style->getFont()->setBold(true)->getColor()->setRGB('1C3447');
        $style->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('C7DCFF');
        $style->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $this->borders("A{$r}:R{$r}");
        $ws->getRowDimension($r)->setRowHeight(18);
    }

    private function materialRow(int $r, array $row): void
    {
        $ws = $this->ws;
        // Unit LOT: qty req = cons (tidak dikali orig qty), sama dengan halaman Input BOM.
        $this->put($r, [
            'A' => $row['no'], 'B' => $row['content'], 'C' => $row['shell'], 'D' => $row['item'], 'E' => $row['color'], 'F' => $row['size'], 'G' => $row['dest'],
            'H' => $row['orig'], 'I' => $row['cons'], 'J' => $row['unit'],
            'K' => $row['lot'] ? "=I{$r}" : "=H{$r}*I{$r}", 'L' => $row['loss'], 'M' => "=K{$r}*L{$r}/100", 'N' => "=ROUNDUP(K{$r}+M{$r},0)",
            'O' => "=J{$r}", 'P' => $row['supplier'], 'Q' => $row['price'], 'R' => $row['price_costing'] ?? '-',
        ]);
        $this->borders("A{$r}:R{$r}");
        $ws->getStyle("A{$r}:R{$r}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        foreach (['A', 'E', 'F', 'G', 'J', 'O'] as $col) {
            $ws->getStyle("{$col}{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }
        foreach (['B', 'D', 'G', 'P'] as $col) {
            $ws->getStyle("{$col}{$r}")->getAlignment()->setWrapText(true);
        }
        foreach (['H' => '#,##0', 'I' => '#,##0.000', 'K' => '#,##0.000', 'L' => '0.##', 'M' => '#,##0.000', 'N' => '#,##0.000', 'Q' => '#,##0.00', 'R' => '#,##0.00'] as $col => $format) {
            $ws->getStyle("{$col}{$r}")->getNumberFormat()->setFormatCode($format);
            $ws->getStyle("{$col}{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        }
        $ws->getStyle("N{$r}")->getFont()->setBold(true);
        $lines = 1;
        foreach (['B' => 18, 'D' => 30, 'G' => 16, 'P' => 22] as $col => $chars) {
            $lines = max($lines, (int) ceil(mb_strlen((string) $ws->getCell("{$col}{$r}")->getValue()) / $chars));
        }
        $ws->getRowDimension($r)->setRowHeight(max(15.95, $lines * 12.6 + 4));
    }

    private function emptyRow(int $r, string $text): void
    {
        $this->ws->mergeCells("A{$r}:R{$r}");
        $this->ws->setCellValue("A{$r}", $text);
        $this->ws->getStyle("A{$r}")->getFont()->getColor()->setRGB('A0A8B5');
        $this->ws->getStyle("A{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $this->borders("A{$r}:R{$r}");
    }

    private function spans(int $r, array $spans): void
    {
        foreach ($spans as $from => $to) {
            if ($to) {
                $this->ws->mergeCells("{$from}{$r}:{$to}{$r}");
            }
        }
    }

    /** Tulis sel: rumus (=...), angka, atau teks apa adanya (nomor dokumen tidak berubah jadi angka/tanggal). */
    private function put(int $r, array $values): void
    {
        foreach ($values as $col => $value) {
            if ($value === null) {
                continue;
            }
            $type = is_string($value) ? (str_starts_with($value, '=') ? DataType::TYPE_FORMULA : DataType::TYPE_STRING) : DataType::TYPE_NUMERIC;
            $this->ws->setCellValueExplicit($col . $r, $value, $type);
        }
    }

    private function borders(string $range): void
    {
        $this->ws->getStyle($range)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB(self::LINE);
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
