{{-- PDF Costing DB baru (dompdf), acuan: "pdf costing.pdf". Angka dari Marketing_CostingNdsController::costingDocument(), sama dengan Excel. --}}
@php
    $d = $doc;
    $n2 = fn ($v) => $v === null ? '' : (abs((float) $v) < 0.005 ? '-' : number_format((float) $v, 2));
    $n4 = fn ($v) => $v === null ? '' : (abs((float) $v) < 0.00005 ? '-' : number_format((float) $v, 4));
    $price = fn ($v) => abs((float) $v) < 0.005 ? '' : number_format((float) $v, 2);
    $pct = fn ($v) => $v === null ? '' : number_format((float) $v * 100, 2) . '%';
    $int = fn ($v) => $v === null ? '' : number_format((float) $v, 0);
    $img = function ($path) {
        $type = strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'png' ? 'png' : 'jpeg';
        return 'data:image/' . $type . ';base64,' . base64_encode(file_get_contents($path));
    };
    // Lebar kolom mengikuti lebar kolom Excel (B:C digabung untuk CONTENT).
    $widths = [5, 26, 15, 15, 9, 21, 14, 7, 11, 12, 11, 9, 7, 9, 14, 12, 11, 11, 13];
    $sumW = array_sum($widths);
    $w = array_map(fn ($x) => round($x / $sumW * 100, 2) . '%', $widths);
    $batch = $batch ?? false;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Costing - {{ $d['no'] }}</title>
    <style>
        @page { margin: 24px 26px 34px; }
        body { font-family: Helvetica, Arial, sans-serif; font-size: 7.5px; color: #1f2933; }
        table { border-collapse: collapse; }
        .kop { width: 100%; border-bottom: 2px solid #284a63; margin-bottom: 9px; }
        .kop td { vertical-align: middle; padding: 0 0 6px; }
        .kop .co { font-size: 13px; font-weight: bold; color: #284a63; }
        .kop .div { font-size: 8.5px; color: #666; margin-top: 2px; }
        .kop .doc { text-align: right; font-size: 17px; font-weight: bold; color: #284a63; letter-spacing: 1.5px; }
        .kop .docno { text-align: right; font-size: 10px; font-weight: bold; color: #1f2933; margin-top: 2px; }
        .info { width: 100%; background: #f5f8fc; border-radius: 4px; margin-bottom: 12px; }
        .info td { padding: 2px 6px; font-size: 7.5px; vertical-align: middle; }
        .info .l { color: #666; white-space: nowrap; }
        .info .v { font-weight: bold; color: #1f2933; }
        .info .pic { text-align: center; vertical-align: middle; padding: 6px 8px; }
        .info .pic div { display: inline-block; border: 1px solid #c9d2de; background: #fff; padding: 3px; }
        .info .pic img { max-width: 150px; max-height: 110px; }
        /* Jarak baris info disesuaikan supaya tinggi blok teks kira-kira sama dengan kotak gambar (6 baris). */
        .info .info-rows td { padding-top: 5px; padding-bottom: 5px; }
        .sec { font-size: 8px; font-weight: bold; color: #284a63; letter-spacing: .4px; margin: 6px 0 5px; }
        .grid { width: 100%; table-layout: fixed; margin-bottom: 10px; }
        .grid th { background: #284a63; color: #fff; font-size: 6.5px; font-weight: bold; text-align: center; padding: 5px 3px; border: 1px solid #b8c2d1; }
        .grid td { border: 1px solid #b8c2d1; padding: 4px 3px; font-size: 7px; vertical-align: middle; word-wrap: break-word; }
        .grid tr.grp td { background: #c7dcff; color: #1c3447; font-weight: bold; font-size: 7.5px; }
        .grid tr.tot td { background: #eef3fa; font-weight: bold; }
        .grid tr.gtot td { background: #dfe8f5; font-weight: bold; }
        .c { text-align: center; } .r { text-align: right; white-space: nowrap; } .b { font-weight: bold; }
        .m { color: #a0a8b5; }
        .bottom { width: 100%; margin-top: 4px; }
        .bottom > tbody > tr > td { vertical-align: top; }
        .notes { border: 1px solid #c9d2de; border-radius: 3px; padding: 6px 8px; min-height: 30px; color: #555; }
        .sign { width: 100%; margin-top: 10px; }
        .sign td.box { width: 50%; border: 1px solid #c9d2de; border-radius: 3px; padding: 6px 8px; height: 62px; vertical-align: top; }
        .sign td.gap { width: 12px; }
        .sign .t { color: #555; }
        .sign .line { border-top: 1px solid #c9d2de; margin-top: 26px; padding-top: 3px; }
        .sign .who { font-weight: bold; color: #1f2933; }
        .sign .when { color: #888; font-size: 6.5px; }
        .sum { width: 100%; }
        .sum th { background: #284a63; color: #fff; font-size: 6.5px; padding: 5px 6px; border: 1px solid #b8c2d1; }
        .sum td { border: 1px solid #b8c2d1; padding: 4px 6px; font-size: 7px; }
        .sum td.k { background: #eef3fa; font-weight: bold; }
        .sum tr.tc td { background: #dfe8f5; font-weight: bold; }
        .sum tr.fob td { background: #284a63; color: #fff; font-weight: bold; }
        .imgs { width: 100%; table-layout: fixed; border-collapse: separate; border-spacing: 8px 0; margin: 0 -8px; }
        .imgs td { vertical-align: top; }
        .card { border: 1px solid #c9d2de; border-radius: 4px; padding: 6px; text-align: center; margin-bottom: 8px; }
        .card .ph { height: 180px; }
        .card img { max-width: 100%; max-height: 176px; }
        .card .cap { font-weight: bold; color: #284a63; margin-top: 4px; font-size: 7.5px; }
        .card .fn { color: #666; font-size: 6.5px; margin-top: 1px; }
    </style>
</head>
<body>
    {{-- Footer "No Rev" + "Halaman X dari Y" ditulis controller lewat canvas dompdf (total halaman baru diketahui setelah render). --}}
    <table class="kop">
        <tr>
            <td style="width: 58px;"><img src="{{ public_path('assets/dist/img/nag-mark.png') }}" style="height: 28px;"></td>
            <td><div class="co">PT NIRWANA ALABARE GARMENT</div><div class="div">Marketing</div></td>
            <td style="width: 40%;"><div class="doc">COSTING</div><div class="docno">{{ $d['no'] }}</div></td>
        </tr>
    </table>

    @php
        $info = [
            [['No Costing', $d['no']], ['Season', $d['season']], ['Main Destination', $d['destination']], ['Rate USD to IDR', number_format($d['rate_usd_idr'], 2)]],
            [['Revision', $d['revision']], ['Style', $d['style']], ['Marketing Office', $d['office']], ['Rate IDR to USD', number_format($d['rate_idr_usd'], 2)]],
            [['Costing Date', $d['date']], ['Product Group', $d['product_group']], ['Planned Qty', number_format($d['qty'], 0)], ['VAT', number_format($d['vat'], 2) . ' %']],
            [['Status', $d['status']], ['Product Item', $d['product_item']], ['SMV', number_format($d['smv'], 2)], null],
            [['Buyer', $d['buyer']], ['Costing Type', $d['type']], ['Currency', $d['currency']], null],
            [['Brand', $d['brand']], ['Unit', $d['unit']], null, null],
        ];
    @endphp
    {{-- Info di kiri, gambar di kolom sendiri: tinggi gambar tidak meregangkan baris info (teks tetap rapat). --}}
    <table class="info">
        <tr>
            <td style="width: 86%; padding: 0; vertical-align: top;">
                <table class="info-rows" style="width: 100%;">
                    @foreach ($info as $line)
                        <tr>
                            @foreach ($line as $pair)
                                @if ($pair)
                                    <td class="l" style="width: 8%;">{{ $pair[0] }}</td><td class="v" style="width: 17%;">: {{ $pair[1] }}</td>
                                @else
                                    <td style="width: 8%;"></td><td style="width: 17%;"></td>
                                @endif
                            @endforeach
                        </tr>
                    @endforeach
                </table>
            </td>
            <td class="pic" style="width: 14%;">
                <div>
                    @if ($d['header_image'])
                        <img src="{{ $img($d['header_image']['path']) }}">
                    @else
                        <span style="display: inline-block; width: 130px; height: 104px; line-height: 104px; color: #a0a8b5;">No Image</span>
                    @endif
                </div>
            </td>
        </tr>
    </table>

    {{-- A. MATERIAL --}}
    <div class="sec">A. MATERIAL</div>
    <table class="grid">
        <thead>
            <tr>
                @foreach (['NO', 'CONTENT', 'SHELL', 'PART', 'SET', 'DESCRIPTION', 'SUPPLIER', 'CURR', 'PRICE', 'PRICE IDR', 'PRICE USD', 'CONS/PC', 'UNIT', 'ALLOW %', 'VALUE IDR', 'VALUE USD', '%', 'QTY BOM', 'VALUE'] as $k => $h)
                    <th style="width: {{ $w[$k] }};">{{ $h }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($d['material'] as $g)
                <tr class="grp"><td colspan="19">{{ $g['label'] }}</td></tr>
                @foreach ($g['rows'] as $i => $r)
                    <tr>
                        <td class="c">{{ $i + 1 }}</td>
                        <td>{{ $r['content'] }}</td>
                        <td class="{{ $r['shell'] === '-' ? 'm' : '' }}">{{ $r['shell'] }}</td>
                        <td class="{{ $r['part'] === '-' ? 'm' : '' }}">{{ $r['part'] }}</td>
                        <td class="c {{ $r['set'] === '-' ? 'm' : '' }}">{{ $r['set'] }}</td>
                        <td class="{{ $r['desc'] === '-' ? 'm' : '' }}">{{ $r['desc'] }}</td>
                        <td class="{{ $r['supplier'] === '-' ? 'm c' : '' }}">{{ $r['supplier'] }}</td>
                        <td class="c">{{ $r['curr'] }}</td>
                        <td class="r">{{ $price($r['price']) }}</td>
                        <td class="r">{{ $n2($r['price_idr']) }}</td>
                        <td class="r">{{ $n2($r['price_usd']) }}</td>
                        <td class="r">{{ number_format($r['cons'], 3) }}</td>
                        <td class="c">{{ $r['unit'] }}</td>
                        <td class="r">{{ $n2($r['allow']) }}</td>
                        <td class="r b">{{ $n2($r['value_idr']) }}</td>
                        <td class="r b">{{ $n4($r['value_usd']) }}</td>
                        <td class="r">{{ $pct($r['pct']) }}</td>
                        <td class="r">{{ $int($r['qty_bom']) }}</td>
                        <td class="r b">{{ $n2($r['value']) }}</td>
                    </tr>
                @endforeach
                <tr class="tot">
                    <td colspan="14">TOTAL {{ $g['label'] }}</td>
                    <td class="r">{{ $n2($g['value_idr']) }}</td>
                    <td class="r">{{ $n4($g['value_usd']) }}</td>
                    <td class="r">{{ $pct($g['pct']) }}</td>
                    <td class="r">{{ $int($g['qty_bom']) }}</td>
                    <td class="r">{{ $n2($g['value']) }}</td>
                </tr>
            @endforeach
            <tr class="gtot">
                <td colspan="14">TOTAL MATERIAL</td>
                <td class="r">{{ $n2($d['material_total']['value_idr']) }}</td>
                <td class="r">{{ $n4($d['material_total']['value_usd']) }}</td>
                <td class="r">{{ $pct($d['material_total']['pct']) }}</td>
                <td class="r">{{ $int($d['material_total']['qty_bom']) }}</td>
                <td class="r">{{ $n2($d['material_total']['value']) }}</td>
            </tr>
        </tbody>
    </table>

    {{-- B. MANUFACTURING & OTHER COST --}}
    <div class="sec">B. MANUFACTURING &amp; OTHER COST</div>
    <table class="grid">
        <thead>
            <tr>
                @foreach (['NO', 'CONTENT', 'CALC TYPE', 'SUBCONT', 'SET', 'DESCRIPTION', 'SUPPLIER', 'CURR', 'PRICE', 'PRICE IDR', 'PRICE USD', 'CONS/PC', 'UNIT', 'ALLOW %', 'VALUE IDR', 'VALUE USD', '%', 'QTY BOM', 'VALUE'] as $k => $h)
                    <th style="width: {{ $w[$k] }};">{{ $h }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ([$d['manufacturing'], $d['other']] as $g)
                <tr class="grp"><td colspan="19">{{ $g['label'] }}</td></tr>
                @foreach ($g['rows'] as $i => $r)
                    <tr>
                        <td class="c">{{ $i + 1 }}</td>
                        <td>{{ $r['content'] }}</td>
                        <td class="c">{{ $r['calc'] }}</td>
                        <td class="c">{{ $r['subcont'] }}</td>
                        <td class="c {{ $r['set'] === '-' ? 'm' : '' }}">{{ $r['set'] }}</td>
                        <td class="{{ $r['desc'] === '-' ? 'm' : '' }}">{{ $r['desc'] }}</td>
                        <td class="{{ $r['supplier'] === '-' ? 'm c' : '' }}">{{ $r['supplier'] }}</td>
                        <td class="c">{{ $r['curr'] }}</td>
                        <td class="r">{{ $price($r['price']) }}</td>
                        <td class="r">{{ $n2($r['price_idr']) }}</td>
                        <td class="r">{{ $n2($r['price_usd']) }}</td>
                        <td class="r">{{ number_format($r['cons'], 3) }}</td>
                        <td class="c">{{ $r['unit'] }}</td>
                        <td class="r">{{ $n2($r['allow']) }}</td>
                        <td class="r b">{{ $n2($r['value_idr']) }}</td>
                        <td class="r b">{{ $n4($r['value_usd']) }}</td>
                        <td class="r">{{ $pct($r['pct']) }}</td>
                        <td class="r">{{ $int($r['qty_bom']) }}</td>
                        <td class="r b">{{ $n2($r['value']) }}</td>
                    </tr>
                @endforeach
                <tr class="tot">
                    <td colspan="14">TOTAL {{ $g['label'] }}</td>
                    <td class="r">{{ $n2($g['value_idr']) }}</td>
                    <td class="r">{{ $n4($g['value_usd']) }}</td>
                    <td class="r">{{ $pct($g['pct']) }}</td>
                    <td class="r">{{ $int($g['qty_bom']) }}</td>
                    <td class="r">{{ $n2($g['value']) }}</td>
                </tr>
            @endforeach
            <tr class="gtot">
                <td colspan="17">GRAND TOTAL (A + B)</td>
                <td class="r">{{ $int($d['grand']['qty_bom']) }}</td>
                <td class="r">{{ $n2($d['grand']['value']) }}</td>
            </tr>
        </tbody>
    </table>

    {{-- Notes, tanda tangan, summary --}}
    @php $s = $d['summary']; @endphp
    <table class="bottom" style="page-break-inside: avoid;">
        <tr>
            <td style="width: 66%; padding-right: 18px;">
                <div class="notes">Notes : {{ $d['notes'] }}</div>
                <table class="sign" style="width: 62%;">
                    <tr>
                        <td class="box">
                            <div class="t">Created by</div>
                            <div class="line"><div class="who">{{ $d['creator'] }}</div><div class="when">{{ $d['created_at'] }}</div></div>
                        </td>
                        <td class="gap"></td>
                        <td class="box">
                            <div class="t">Approved by</div>
                            <div class="line"></div>
                        </td>
                    </tr>
                </table>
            </td>
            <td style="width: 34%;">
                <table class="sum">
                    <tr><th style="text-align: left; width: 40%;">SUMMARY</th><th style="width: 14%;">%</th><th style="width: 26%;">VALUE IDR</th><th style="width: 20%;">VALUE USD</th></tr>
                    <tr><td class="k">Total Material</td><td></td><td class="r">{{ $n2($s['material'][0]) }}</td><td class="r">{{ $n4($s['material'][1]) }}</td></tr>
                    <tr><td class="k">Total Manufacturing</td><td></td><td class="r">{{ $n2($s['manufacturing'][0]) }}</td><td class="r">{{ $n4($s['manufacturing'][1]) }}</td></tr>
                    <tr><td class="k">Total Other Cost</td><td></td><td class="r">{{ $n2($s['other'][0]) }}</td><td class="r">{{ $n4($s['other'][1]) }}</td></tr>
                    <tr><td class="k">G&amp;A</td><td class="r">{{ number_format($s['ga'][0], 2) }}%</td><td class="r">{{ $n2($s['ga'][1]) }}</td><td class="r">{{ $n4($s['ga'][2]) }}</td></tr>
                    <tr><td class="k">G&amp;A Extra</td><td class="r">{{ number_format($s['ga_extra'][0], 2) }}%</td><td class="r">{{ $n2($s['ga_extra'][1]) }}</td><td class="r">{{ $n4($s['ga_extra'][2]) }}</td></tr>
                    <tr class="tc"><td>TOTAL COST</td><td></td><td class="r">{{ number_format($s['total'][0], 2) }}</td><td class="r">{{ number_format($s['total'][1], 4) }}</td></tr>
                    <tr><td class="k">Deal Allowance</td><td class="r">{{ number_format($s['deal'], 2) }}%</td><td></td><td></td></tr>
                    <tr><td class="k">Cost + VAT</td><td class="r">{{ number_format($s['vat'][0], 2) }}%</td><td class="r">{{ number_format($s['vat'][1], 2) }}</td><td class="r">{{ number_format($s['vat'][2], 4) }}</td></tr>
                    <tr><td class="k">Cost + VAT + Profit</td><td class="r">{{ number_format($s['profit'][0], 2) }}%</td><td class="r">{{ number_format($s['profit'][1], 2) }}</td><td class="r">{{ number_format($s['profit'][2], 4) }}</td></tr>
                    <tr class="fob"><td>FOB PRICE / PCS</td><td></td><td class="r">{{ number_format($s['profit'][1], 2) }}</td><td class="r">{{ number_format($s['profit'][2], 4) }}</td></tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- C. IMAGES: semua gambar, urut sesuai upload, 5 per baris --}}
    @if (count($d['images']))
        <div class="sec" style="margin-top: 12px;">C. IMAGES ({{ count($d['images']) }})</div>
        <table class="imgs">
            @foreach (array_chunk($d['images'], 5) as $chunkIndex => $chunk)
                <tr>
                    @for ($k = 0; $k < 5; $k++)
                        <td>
                            @if (isset($chunk[$k]))
                                <div class="card">
                                    <div class="ph"><img src="{{ $img($chunk[$k]['path']) }}"></div>
                                    <div class="cap">{{ $chunkIndex * 5 + $k + 1 }}. {{ $chunk[$k]['type'] }}</div>
                                    <div class="fn">{{ $chunk[$k]['name'] }}</div>
                                </div>
                            @endif
                        </td>
                    @endfor
                </tr>
            @endforeach
        </table>
    @endif
</body>
</html>
