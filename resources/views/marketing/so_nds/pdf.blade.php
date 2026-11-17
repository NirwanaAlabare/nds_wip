{{-- PDF Sales Order portrait (dompdf), acuan: "so potrait.pdf". Data dari MarketingNdsDocumentService::salesOrderPrint(). --}}
@php
    $d = $p;
    $cur = $d['currency'];
    $int = fn ($v) => number_format((float) $v, 0);
    $sizeCell = fn ($v) => $v ? number_format($v, 0) : '';
    $sizeTot = fn ($v) => $v ? number_format($v, 0) : '-';
    $money = fn ($v) => number_format((float) $v, 2);
    $img = function ($path) {
        $type = strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'png' ? 'png' : 'jpeg';
        return 'data:image/' . $type . ';base64,' . base64_encode(file_get_contents($path));
    };
    // Kolom tetap (persen lebar halaman); sisa lebar dibagi rata ke kolom size.
    $sizeCount = max(count($d['sizes']), 1);
    $fixed = ['no' => 4, 'dest' => 15, 'date' => 9.5, 'ship' => 9.5, 'color' => 12, 'total' => 7.5, 'price' => 8, 'value' => 10];
    $sizeW = max((100 - array_sum($fixed)) / $sizeCount, 3.2);
    $lead = 5; // NO, DESTINATION, DELIVERY DATE, SHIP MODE, COLOR
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sales Order - {{ $d['no'] }}</title>
    <style>
        @page { margin: 26px 28px 36px; }
        body { font-family: Helvetica, Arial, sans-serif; font-size: 7.5px; color: #1f2933; }
        table { border-collapse: collapse; }
        .kop { width: 100%; border-bottom: 2px solid #284a63; margin-bottom: 12px; }
        .kop td { vertical-align: middle; padding: 0 0 7px; }
        .kop .co { font-size: 13px; font-weight: bold; color: #284a63; }
        .kop .div { font-size: 8.5px; color: #666; margin-top: 2px; }
        .kop .doc { text-align: right; font-size: 17px; font-weight: bold; color: #284a63; letter-spacing: 1.5px; }
        .kop .docno { text-align: right; font-size: 10px; font-weight: bold; color: #1f2933; margin-top: 2px; }
        .info { width: 100%; background: #f5f8fc; margin-bottom: 14px; }
        .info td { padding: 2px 8px; font-size: 7.5px; vertical-align: top; }
        .info tr:first-child td { padding-top: 7px; }
        .info tr:last-child td { padding-bottom: 7px; }
        .info .l { color: #666; white-space: nowrap; }
        .info .v { font-weight: bold; color: #1f2933; }
        .info .pic { text-align: right; vertical-align: top; padding: 6px 8px; }
        .info .pic div { display: inline-block; border: 1px solid #c9d2de; border-radius: 3px; background: #fff; padding: 4px; }
        .info .pic img { max-width: 130px; max-height: 150px; }
        /* Jarak baris info disesuaikan supaya tinggi blok teks kira-kira sama dengan kotak gambar. */
        .info .info-rows td { padding-top: 4px; padding-bottom: 4px; }
        .sec { font-size: 8px; font-weight: bold; color: #284a63; letter-spacing: .4px; margin: 6px 0 5px; }
        .grid { width: 100%; table-layout: fixed; margin-bottom: 12px; }
        .grid th { background: #284a63; color: #fff; font-size: 6.5px; font-weight: bold; text-align: center; padding: 5px 3px; border: 1px solid #b8c2d1; }
        .grid td { border: 1px solid #b8c2d1; padding: 4px 4px; font-size: 7px; vertical-align: middle; word-wrap: break-word; }
        .grid tr.grp td { background: #c7dcff; color: #1c3447; font-weight: bold; font-size: 7px; }
        .grid tr.tot td { background: #eef3fa; font-weight: bold; }
        .grid tr.gtot td { background: #dfe8f5; font-weight: bold; }
        .c { text-align: center; } .r { text-align: right; white-space: nowrap; } .b { font-weight: bold; }
        .m { color: #a0a8b5; }
        .bottom { width: 100%; margin-top: 4px; page-break-inside: avoid; }
        .bottom > tbody > tr > td { vertical-align: top; }
        .sign { width: 100%; }
        .sign td.box { width: 50%; border: 1px solid #c9d2de; border-radius: 3px; padding: 6px 8px; height: 62px; vertical-align: top; }
        .sign td.gap { width: 14px; }
        .sign .t { color: #555; }
        .sign .line { border-top: 1px solid #c9d2de; margin-top: 26px; padding-top: 3px; }
        .sign .who { font-weight: bold; color: #1f2933; }
        .sign .when { color: #888; font-size: 6.5px; }
        .sum { width: 100%; }
        .sum th { background: #284a63; color: #fff; font-size: 6.5px; padding: 5px 6px; border: 1px solid #b8c2d1; }
        .sum td { border: 1px solid #b8c2d1; padding: 4px 6px; font-size: 7px; }
        .sum td.k { background: #eef3fa; font-weight: bold; }
        .sum tr.tv td { background: #284a63; color: #fff; font-weight: bold; }
    </style>
</head>
<body>
    {{-- Footer "Sales Order ... Rev ..." + "Halaman X dari Y" ditulis controller lewat canvas dompdf. --}}
    <table class="kop">
        <tr>
            <td style="width: 52px;"><img src="{{ public_path('assets/dist/img/nag-mark.png') }}" style="height: 28px;"></td>
            <td><div class="co">PT NIRWANA ALABARE GARMENT</div><div class="div">Marketing</div></td>
            <td style="width: 45%;"><div class="doc">SALES ORDER</div><div class="docno">{{ $d['no'] }} &middot; Detail PO Buyer</div></td>
        </tr>
    </table>

    @php $infoRows = max(count($d['info_left']), count($d['info_right'])); @endphp
    {{-- Info di kiri, gambar di kolom sendiri: tinggi gambar tidak meregangkan baris info (teks tetap rapat). --}}
    <table class="info">
        <tr>
            <td style="width: 84%; padding: 0; vertical-align: top;">
                <table class="info-rows" style="width: 100%;">
                    @for ($i = 0; $i < $infoRows; $i++)
                        <tr>
                            @foreach ([$d['info_left'][$i] ?? null, $d['info_right'][$i] ?? null] as $k => $pair)
                                @if ($pair)
                                    <td class="l" style="width: {{ $k ? 13 : 12 }}%;">{{ $pair[0] }}</td><td class="v" style="width: {{ $k ? 42 : 33 }}%;">: {{ $pair[1] }}</td>
                                @else
                                    <td></td><td></td>
                                @endif
                            @endforeach
                        </tr>
                    @endfor
                </table>
            </td>
            <td class="pic" style="width: 16%;">
                <div>
                    @if ($d['image'])
                        <img src="{{ $img($d['image']) }}">
                    @else
                        <span style="display: inline-block; width: 110px; height: 130px; line-height: 130px; text-align: center; color: #a0a8b5;">No Image</span>
                    @endif
                </div>
            </td>
        </tr>
    </table>

    {{-- A. PO BUYER PER DESTINATION --}}
    <div class="sec">A. PO BUYER PER DESTINATION</div>
    <table class="grid">
        <thead>
            <tr>
                <th style="width: {{ $fixed['no'] }}%;">NO</th>
                <th style="width: {{ $fixed['dest'] }}%;">DESTINATION</th>
                <th style="width: {{ $fixed['date'] }}%;">DELIVERY DATE</th>
                <th style="width: {{ $fixed['ship'] }}%;">SHIP MODE</th>
                <th style="width: {{ $fixed['color'] }}%;">COLOR</th>
                @foreach ($d['sizes'] as $size)
                    <th style="width: {{ $sizeW }}%;">{{ $size }}</th>
                @endforeach
                <th style="width: {{ $fixed['total'] }}%;">TOTAL QTY</th>
                <th style="width: {{ $fixed['price'] }}%;">PRICE ({{ $cur }})</th>
                <th style="width: {{ $fixed['value'] }}%;">VALUE ({{ $cur }})</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($d['pos'] as $po)
                <tr class="grp"><td colspan="{{ $lead + count($d['sizes']) + 3 }}">{{ $po['po'] }}@if ($po['po_date']) &nbsp;&middot;&nbsp; PO Date {{ date('Y-m-d', strtotime($po['po_date'])) }}@endif</td></tr>
                @foreach ($po['lines'] as $i => $line)
                    <tr>
                        <td class="c">{{ $i + 1 }}</td>
                        <td>{{ $line['destination'] }}</td>
                        <td class="c">{{ $line['date'] ? date('Y-m-d', strtotime($line['date'])) : '-' }}</td>
                        <td>{{ $line['ship_mode'] }}</td>
                        <td class="b">{{ $line['color'] }}</td>
                        @foreach ($line['qty'] as $q)
                            <td class="r">{{ $sizeCell($q) }}</td>
                        @endforeach
                        <td class="r b">{{ $int($line['total']) }}</td>
                        <td class="r">{{ $line['price'] !== null ? number_format($line['price'], 4) : '-' }}</td>
                        <td class="r">{{ $money($line['value']) }}</td>
                    </tr>
                @endforeach
                <tr class="tot">
                    <td colspan="{{ $lead }}">TOTAL {{ $po['po'] }}</td>
                    @foreach ($po['size_totals'] as $q)
                        <td class="r">{!! $q ? $int($q) : '<span class="m">-</span>' !!}</td>
                    @endforeach
                    <td class="r">{{ $int($po['total']) }}</td>
                    <td></td>
                    <td class="r">{{ $money($po['value']) }}</td>
                </tr>
            @endforeach
            <tr class="gtot">
                <td colspan="{{ $lead }}">GRAND TOTAL</td>
                @foreach ($d['grand_sizes'] as $q)
                    <td class="r">{{ $sizeTot($q) }}</td>
                @endforeach
                <td class="r">{{ $int($d['total_qty']) }}</td>
                <td></td>
                <td class="r">{{ $money($d['total_value']) }}</td>
            </tr>
        </tbody>
    </table>

    {{-- B. DETAIL SKU & BARCODE --}}
    <div class="sec">B. DETAIL SKU &amp; BARCODE</div>
    <table class="grid">
        <thead>
            <tr>
                <th style="width: 4%;">NO</th>
                <th style="width: 11%;">BUYER PO NO</th>
                <th style="width: 16%;">DESTINATION</th>
                <th style="width: 12%;">COLOR</th>
                <th style="width: 6%;">SIZE</th>
                <th style="width: 17%;">SKU</th>
                <th style="width: 14%;">BARCODE</th>
                <th style="width: 8%;">QTY</th>
                <th style="width: 12%;">VALUE ({{ $cur }})</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($d['skus'] as $i => $u)
                <tr>
                    <td class="c">{{ $i + 1 }}</td>
                    <td class="b">{{ $u['po'] }}</td>
                    <td>{{ $u['destination'] }}</td>
                    <td>{{ $u['color'] }}</td>
                    <td class="c">{{ $u['size'] }}</td>
                    <td class="{{ $u['sku'] ? '' : 'm' }}">{{ $u['sku'] ?: '-' }}</td>
                    <td class="{{ $u['barcode'] ? '' : 'm' }}">{{ $u['barcode'] ?: '-' }}</td>
                    <td class="r">{{ $int($u['qty']) }}</td>
                    <td class="r">{{ $money($u['value']) }}</td>
                </tr>
            @endforeach
            <tr class="gtot">
                <td colspan="7">TOTAL</td>
                <td class="r">{{ $int($d['total_qty']) }}</td>
                <td class="r">{{ $money($d['total_value']) }}</td>
            </tr>
        </tbody>
    </table>

    {{-- Tanda tangan + summary --}}
    <table class="bottom">
        <tr>
            <td style="width: 56%; padding-right: 18px;">
                <table class="sign">
                    <tr>
                        <td class="box">
                            <div class="t">Created by</div>
                            <div class="line"><div class="who">{{ $d['creator'] }}</div><div class="when">{{ $d['created_at'] }}</div></div>
                        </td>
                        <td class="gap"></td>
                        <td class="box">
                            <div class="t">Approved by</div>
                            <div class="line">@if ($d['approver'])<div class="who">{{ $d['approver'] }}</div><div class="when">{{ $d['approved_at'] }}</div>@endif</div>
                        </td>
                    </tr>
                </table>
            </td>
            <td style="width: 44%;">
                <table class="sum">
                    <tr><th style="text-align: left; width: 58%;">SUMMARY</th><th style="width: 42%;">TOTAL</th></tr>
                    @foreach ($d['by_destination'] as $dest => $qty)
                        <tr><td class="k">Qty {{ $dest }}</td><td class="r">{{ $int($qty) }}</td></tr>
                    @endforeach
                    <tr><td class="k">Total Qty Order</td><td class="r">{{ $int($d['total_qty']) }}</td></tr>
                    <tr class="tv"><td>TOTAL VALUE</td><td class="r">{{ $cur }} {{ $money($d['total_value']) }}</td></tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
