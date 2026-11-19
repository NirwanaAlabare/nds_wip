<?php

namespace App\Services\Marketing;

/**
 * Excel BOM DB baru (fast excel / avadim lewat MarketingXlsx), meniru acuan QA "excel bom (2).xlsx": satu sheet per WS, kolom A-S,
 * rumus hidup (QTY REQ, VALUE, TOTAL QTY REQ). Data dari MarketingNdsDocumentService::bom()['print'] (sama dengan PDF).
 */
class MarketingNdsBomExcel
{
    /** Tab charge yang dicetak di bagian B Excel. Other Cost (OTH) disembunyikan dulu; tambahkan 'OTH' untuk menampilkan lagi. */
    private const CHARGE_TABS = ['MFG'];

    private const NAVY = '284A63';
    private const LINE = 'B8C2D1';
    private const WIDTHS = ['A' => 6, 'B' => 18, 'C' => 15, 'D' => 44, 'E' => 16, 'F' => 8, 'G' => 22, 'H' => 15, 'I' => 10, 'J' => 9,
        'K' => 7, 'L' => 12, 'M' => 8, 'N' => 10, 'O' => 13, 'P' => 7, 'Q' => 24, 'R' => 9, 'S' => 10];

    private MarketingXlsx $x;

    public function build(array $docs): MarketingXlsx
    {
        $this->x = new MarketingXlsx();
        $titles = [];
        foreach ($docs as $doc) {
            $p = $doc['print'];
            $title = substr(preg_replace('/[\\\\\/?*\[\]:]+/', '-', $p['no'] !== '-' ? $p['no'] : (string) $p['no_ws']), 0, 31) ?: 'BOM';
            while (in_array($title, $titles, true)) {
                $title = substr($title, 0, 28) . '-' . count($titles);
            }
            $titles[] = $title;
            $this->x->sheet($title);
            $this->sheet($p, $doc['image'] ?? null);
        }
        if (!$docs) {
            $this->x->sheet('BOM');
        }

        return $this->x;
    }

    private function sheet(array $p, ?string $image): void
    {
        $x = $this->x;
        $x->hideGrid()->widths(self::WIDTHS);
        $this->header($p);
        $this->info($p, $image);

        // A. MATERIAL
        $r = 11;
        $this->title($r, 'A. MATERIAL');
        $head = $r + 1;
        $this->headRow($head, ['A' => 'NO', 'B' => 'CONTENT', 'C' => 'SHELL', 'D' => 'ITEMS', 'E' => 'COLOR', 'F' => 'SIZE', 'G' => 'DESTINATION',
            'H' => 'SHIP MODE', 'I' => 'ORIG QTY', 'J' => 'CONS / PC', 'K' => 'UNIT', 'L' => 'QTY REQ', 'M' => 'LOSS %', 'N' => 'VALUE',
            'O' => 'TOTAL QTY REQ', 'P' => 'UNIT', 'Q' => 'SUPPLIER', 'R' => 'PRICE', 'S' => 'PRICE COSTING']);
        $r = $head + 1;
        // NO = nomor urut baris material (berlanjut antar grup).
        $no = 0;
        foreach ($p['groups'] as $group) {
            $this->groupRow($r++, $group['label']);
            foreach ($group['rows'] as $row) {
                $this->materialRow($r++, ['no' => ++$no] + $row);
            }
        }
        if (!$p['groups']) {
            $this->emptyRow($r++, 'Belum ada material.');
        }
        $x->merge("A{$r}:S{$r}")->set("A{$r}", $p['note_xls'], ['size' => 8, 'color' => '555555']);
        $r += 2;

        // B. MANUFACTURING (Other Cost disembunyikan dulu, lihat CHARGE_TABS).
        $chargeRows = array_values(array_filter($p['charges'], fn ($c) => in_array($c['tab'] ?? 'MFG', self::CHARGE_TABS, true)));
        if (self::CHARGE_TABS) {
        $this->title($r, in_array('OTH', self::CHARGE_TABS, true) ? 'B. MANUFACTURING & OTHER COST' : 'B. MANUFACTURING');
        $r++;
        $spans = ['A' => null, 'B' => 'D', 'E' => 'G', 'H' => null, 'I' => null, 'J' => 'K', 'L' => null, 'M' => 'S'];
        $this->spans($r, $spans);
        $this->headRow($r, ['A' => 'NO', 'B' => 'ITEM', 'E' => 'SUPPLIER', 'H' => 'CONS', 'I' => 'UNIT', 'J' => 'PRICE', 'L' => 'CURR', 'M' => 'KETERANGAN']);
        $r++;
        foreach ($chargeRows as $i => $c) {
            $this->spans($r, $spans);
            $x->row($r, ['A' => $i + 1, 'B' => $c['item'], 'E' => $c['supplier'], 'H' => $c['cons'], 'I' => $c['unit'],
                'J' => $c['price'], 'L' => $c['currency'], 'M' => $c['note']]);
            $this->borders("A{$r}:S{$r}");
            $x->style("A{$r}:S{$r}", ['v' => 'center', 'wrap' => true, 'h' => 'left'])->height($r, 16);
            foreach (['A', 'I', 'L'] as $col) {
                $x->style("{$col}{$r}", ['h' => 'center']);
            }
            $x->style("H{$r}", ['format' => '0.000', 'h' => 'right'])->style("J{$r}", ['format' => '0.0000', 'h' => 'right']);
            $r++;
        }
        if (!$chargeRows) {
            $this->emptyRow($r++, 'Tidak ada manufacturing.');
        }
        $r++;
        }

        // Notes + tanda tangan
        $x->merge("A{$r}:S" . ($r + 1))->set("A{$r}", 'Notes : ' . $p['notes'])
            ->style("A{$r}:S" . ($r + 1), ['color' => '555555', 'v' => 'top', 'wrap' => true])
            ->border("A{$r}:S" . ($r + 1), 'outline', 'thin', self::LINE);
        $sign = $r + 3;
        foreach ([['A', 'C', 'Created by', $p['creator'], $p['created_at']], ['E', 'G', 'Approved by', $p['approver'] ?? '', $p['approved_at']]] as [$from, $to, $label, $who, $when]) {
            for ($row = $sign; $row <= $sign + 4; $row++) {
                $x->merge("{$from}{$row}:{$to}{$row}");
            }
            $x->set("{$from}{$sign}", $label, ['color' => '555555'])
                ->set($from . ($sign + 3), (string) $who, ['bold' => true])
                ->set($from . ($sign + 4), (string) $when, ['size' => 8, 'color' => '666666'])
                ->border("{$from}{$sign}:{$to}" . ($sign + 4), 'outline', 'thin', self::LINE)
                ->border("{$from}" . ($sign + 3) . ":{$to}" . ($sign + 3), 'top', 'thin', self::LINE);
        }
        $last = $sign + 4;

        $x->printLandscape('A1:S' . ($last + 1))
            ->footer('BOM ' . $p['no'] . ' Rev ' . $p['revision'] . ' (' . $p['no_ws'] . ')');
    }

    private function header(array $p): void
    {
        $this->x->height(1, 22)->height(2, 16)->height(3, 6)
            ->image(public_path('assets/dist/img/nag-mark.png'), 'A1', 57, 34)
            ->merge('B1:K1')->set('B1', '      PT NIRWANA ALABARE GARMENT', ['bold' => true, 'size' => 14, 'color' => self::NAVY, 'v' => 'center'])
            ->merge('B2:K2')->set('B2', '        Marketing', ['color' => '666666', 'v' => 'top'])
            ->merge('L1:S1')->set('L1', 'BILL OF MATERIAL', ['bold' => true, 'size' => 18, 'color' => self::NAVY])
            ->merge('L2:S2')->set('L2', (string) $p['no'], ['bold' => true, 'size' => 11])
            ->style('L1:S1', ['h' => 'right', 'v' => 'center'])->style('L2:S2', ['h' => 'right', 'v' => 'top'])
            ->border('A2:S2', 'bottom', 'medium', self::NAVY);
    }

    /**
     * Tiga kolom info (acuan QA): label A:B / nilai C, label D:E / nilai F:H, label J:L / nilai M:P; gambar Costing di R4.
     * Revision, Qty Order, dan Qty + Allowance ditulis sebagai angka berformat (": 0", ": #,##0 PCS").
     */
    private function info(array $p, ?string $image): void
    {
        $x = $this->x;
        $layout = [['A', 'A:B', 'C', null], ['D', 'D:E', 'F', 'F:H'], ['J', 'J:L', 'M', 'M:P']];
        $unit = str_replace('"', '', (string) ($p['unit'] ?? 'PCS'));
        $x->style('A4:S9', ['fill' => 'F5F8FC']);
        foreach ($p['info'] as $k => $pairs) {
            [$labelCol, $labelSpan, $valueCol, $valueSpan] = $layout[$k];
            foreach ($pairs as $i => [$label, $value]) {
                $row = 4 + $i;
                $x->merge(str_replace(':', $row . ':', $labelSpan) . $row);
                if ($valueSpan) {
                    $x->merge(str_replace(':', $row . ':', $valueSpan) . $row);
                }
                $x->set($labelCol . $row, $label, ['color' => '555555']);
                if ($label === 'Revision') {
                    $x->set($valueCol . $row, (int) $value, ['bold' => true, 'format' => '": "0']);
                } elseif ($label === 'Qty Order' || str_starts_with($label, 'Qty + Allowance')) {
                    $x->set($valueCol . $row, $label === 'Qty Order' ? (int) $p['qty'] : (int) $p['qty_allow_xls'], ['bold' => true, 'format' => '": "#,##0" ' . $unit . '"']);
                } else {
                    $x->set($valueCol . $row, ': ' . $value, ['bold' => true]);
                }
            }
        }
        foreach (range(4, 9) as $row) {
            $x->height($row, 16);
        }
        $x->style('A4:S9', ['h' => 'left', 'v' => 'center'])->border('A9:S9', 'bottom', 'thin', self::LINE);
        if ($image) {
            $x->image($image, 'R4', 80, 106);
        }
    }

    private function title(int $row, string $text): void
    {
        $this->x->set("A{$row}", $text, ['bold' => true, 'color' => self::NAVY])->height($row, 18);
    }

    private function headRow(int $row, array $heads): void
    {
        $this->x->row($row, $heads)
            ->style("A{$row}:S{$row}", ['bold' => true, 'size' => 9, 'color' => 'FFFFFF', 'fill' => self::NAVY, 'h' => 'center', 'v' => 'center', 'wrap' => true])
            ->height($row, 22);
        $this->borders("A{$row}:S{$row}");
    }

    private function groupRow(int $r, string $label): void
    {
        $this->x->merge("A{$r}:S{$r}")->set("A{$r}", $label)
            ->style("A{$r}:S{$r}", ['bold' => true, 'color' => '1C3447', 'fill' => 'C7DCFF', 'v' => 'center'])->height($r, 18);
        $this->borders("A{$r}:S{$r}");
    }

    private function materialRow(int $r, array $row): void
    {
        $x = $this->x;
        // Unit LOT: qty req = cons (tidak dikali orig qty), sama dengan halaman Input BOM.
        $x->row($r, [
            'A' => $row['no'], 'B' => $row['content'], 'C' => $row['shell'], 'D' => $row['item'], 'E' => $row['color'], 'F' => $row['size'], 'G' => $row['dest_xls'],
            'H' => $row['ship_mode'], 'I' => $row['orig_xls'], 'J' => $row['cons'], 'K' => $row['unit'],
            'L' => $row['lot'] ? "=J{$r}" : "=I{$r}*J{$r}", 'M' => $row['loss'], 'N' => "=L{$r}*M{$r}/100", 'O' => $row['lot'] ? "=L{$r}+N{$r}" : "=ROUNDUP(L{$r}+N{$r},0)", // dibulatkan ke atas per sel (sama dengan PDF / layar)
            'P' => $row['unit'], 'Q' => $row['supplier'], 'R' => $row['price'], 'S' => $row['price_costing'] ?? '-',
        ]);
        // Format & perataan per kolom sesuai acuan QA; tinggi 16, lebih tinggi kalau teks panjang turun baris (tidak terpotong).
        $this->borders("A{$r}:S{$r}");
        $x->style("A{$r}:S{$r}", ['v' => 'center', 'wrap' => true])->height($r, 16);
        $x->style("A{$r}", ['h' => 'center', 'bold' => true]);
        foreach (['B', 'C', 'D', 'Q'] as $col) {
            $x->style("{$col}{$r}", ['h' => 'left']);
        }
        foreach (['E', 'F', 'G', 'H', 'K', 'P'] as $col) {
            $x->style("{$col}{$r}", ['h' => 'center']);
        }
        foreach (['I' => '#,##0_);-#,##0_);"-"_)', 'J' => '0.000', 'L' => '#,##0.000', 'M' => fmod((float) $row['loss'], 1) ? '0.00' : '0', 'N' => '#,##0.000', 'O' => '#,##0.000', 'R' => '0.0000', 'S' => '0.0000'] as $col => $format) {
            $x->style("{$col}{$r}", ['format' => $format, 'h' => 'right']);
        }
        $x->style("O{$r}", ['bold' => true]);
        $lines = 1;
        foreach (['B' => 18, 'C' => 15, 'D' => 44, 'G' => 22, 'H' => 15, 'Q' => 24] as $col => $chars) {
            $lines = max($lines, (int) ceil(mb_strlen((string) $x->value("{$col}{$r}")) * 1.1 / $chars));
        }
        $x->height($r, max(16, $lines * 12.6 + 3));
    }

    private function emptyRow(int $r, string $text): void
    {
        $this->x->merge("A{$r}:S{$r}")->set("A{$r}", $text, ['color' => 'A0A8B5', 'h' => 'center']);
        $this->borders("A{$r}:S{$r}");
    }

    private function spans(int $r, array $spans): void
    {
        foreach ($spans as $from => $to) {
            if ($to) {
                $this->x->merge("{$from}{$r}:{$to}{$r}");
            }
        }
    }

    private function borders(string $range): void
    {
        $this->x->border($range, 'all', 'thin', self::LINE);
    }
}
