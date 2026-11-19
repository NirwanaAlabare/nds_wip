{{-- PDF BOM landscape (dompdf), acuan "pdf bom.pdf". Satu dokumen per WS; data dari MarketingNdsDocumentService::bom()['print'].
     Footer "BOM ... Rev ... (WS)" + "Halaman X dari Y" per dokumen ditulis controller lewat canvas dompdf. --}}
@php
    $int = fn ($v) => number_format((float) $v, 0);
    $dec = fn ($v, $d = 3) => $v === null ? '-' : number_format((float) $v, $d);
    $pct = fn ($v) => rtrim(rtrim(number_format((float) $v, 2, '.', ''), '0'), '.');
    $img = function ($path) {
        $type = strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'png' ? 'png' : 'jpeg';
        return 'data:image/' . $type . ';base64,' . base64_encode(file_get_contents($path));
    };
    // Lebar kolom A. MATERIAL kira-kira mengikuti Excel BOM; PRICE dilebarkan untuk harga IDR.
    $w = [6, 18, 15, 26, 15, 9, 16, 11, 10, 9, 7, 12, 8, 10, 13, 7, 20, 12, 12];
    $w = array_map(fn ($x) => round($x / array_sum($w) * 100, 2), $w);
    $heads = ['NO', 'CONTENT', 'SHELL', 'ITEMS', 'COLOR', 'SIZE', 'DESTINATION', 'SHIP MODE', 'ORIG QTY', 'CONS / PC', 'UNIT', 'QTY REQ', 'LOSS %', 'VALUE', 'TOTAL QTY REQ', 'UNIT', 'SUPPLIER', 'PRICE', 'PRICE COSTING'];
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Bill of Material</title>
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
        .warn { background: #fff4e5; border: 1px solid #f0b46a; color: #8a4b00; padding: 5px 8px; margin-bottom: 8px; }
        .info { width: 100%; background: #f5f8fc; margin-bottom: 14px; }
        .info td { padding: 4px 8px; font-size: 7.5px; vertical-align: top; }
        .info .l { color: #666; white-space: nowrap; }
        .info .v { font-weight: bold; color: #1f2933; }
        .info .pic { text-align: right; vertical-align: top; padding: 6px 8px; }
        .info .pic div { display: inline-block; border: 1px solid #c9d2de; border-radius: 3px; background: #fff; padding: 4px; }
        .info .pic img { max-width: 110px; max-height: 100px; }
        .sec { font-size: 8px; font-weight: bold; color: #284a63; letter-spacing: .4px; margin: 6px 0 5px; }
        .grid { width: 100%; table-layout: fixed; }
        .grid th { background: #284a63; color: #fff; font-size: 6.5px; font-weight: bold; text-align: center; padding: 5px 3px; border: 1px solid #b8c2d1; }
        .grid td { border: 1px solid #b8c2d1; padding: 4px 4px; font-size: 7px; vertical-align: middle; word-wrap: break-word; }
        .grid tr.grp td { background: #c7dcff; color: #1c3447; font-weight: bold; }
        .c { text-align: center; } .r { text-align: right; white-space: nowrap; } .b { font-weight: bold; }
        .m { color: #a0a8b5; }
        .foot { font-size: 6.5px; color: #666; margin: 4px 0 12px; }
        .notes { border: 1px solid #c9d2de; border-radius: 3px; padding: 6px 8px; height: 30px; color: #555; margin: 12px 0 10px; }
        .sign { width: 40%; page-break-inside: avoid; }
        .sign td.box { width: 50%; border: 1px solid #c9d2de; border-radius: 3px; padding: 6px 8px; height: 62px; vertical-align: top; }
        .sign td.gap { width: 14px; }
        .sign .t { color: #555; }
        .sign .line { border-top: 1px solid #c9d2de; margin-top: 26px; padding-top: 3px; }
        .sign .who { font-weight: bold; color: #1f2933; }
        .sign .when { color: #888; font-size: 6.5px; }
        .page-break { page-break-after: always; }
    </style>
</head>
<body>
@foreach ($docs as $di => $doc)
    @php $p = $doc['print']; @endphp
    <table class="kop">
        <tr>
            <td style="width: 52px;"><img src="{{ public_path('assets/dist/img/nag-mark.png') }}" style="height: 28px;"></td>
            <td><div class="co">PT NIRWANA ALABARE GARMENT</div><div class="div">Marketing</div></td>
            <td style="width: 45%;"><div class="doc">BILL OF MATERIAL</div><div class="docno">{{ $p['no'] }}</div></td>
        </tr>
    </table>

    @if (!$doc['bom'])
        <div class="warn">BOM WS ini belum disimpan: isi di bawah masih isian awal dari Costing.</div>
    @elseif ($doc['stale']['changes'])
        <div class="warn">BOM perlu disesuaikan: Sales Order / Costing berubah setelah BOM disimpan.</div>
    @endif

    {{-- Tiga kolom info, gambar Costing di kolom sendiri supaya tidak meregangkan baris info. --}}
    <table class="info">
        <tr>
            <td style="width: 88%; padding: 0;">
                <table style="width: 100%;">
                    @for ($i = 0; $i < 6; $i++)
                        <tr>
                            @foreach ($p['info'] as $k => $pairs)
                                @if (isset($pairs[$i]))
                                    <td class="l" style="width: {{ [9, 8, 9][$k] }}%;">{{ $pairs[$i][0] }}</td>
                                    <td class="v" style="width: {{ [23, 26, 25][$k] }}%;">: {{ $pairs[$i][1] }}</td>
                                @else
                                    <td></td><td></td>
                                @endif
                            @endforeach
                        </tr>
                    @endfor
                </table>
            </td>
            <td class="pic" style="width: 12%;">
                <div>
                    @if (!empty($doc['image']))
                        <img src="{{ $img($doc['image']) }}">
                    @else
                        <span style="display: inline-block; width: 90px; height: 90px; line-height: 90px; text-align: center; color: #a0a8b5;">No Image</span>
                    @endif
                </div>
            </td>
        </tr>
    </table>

    <div class="sec">A. MATERIAL</div>
    <table class="grid">
        <thead>
            <tr>
                @foreach ($heads as $i => $head)
                    <th style="width: {{ $w[$i] }}%;">{{ $head }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($p['groups'] as $group)
                <tr class="grp"><td colspan="19">{{ $group['label'] }}</td></tr>
                @foreach ($group['rows'] as $row)
                    <tr>
                        <td class="c b">{{ $row['no'] }}</td>
                        <td>{{ $row['content'] }}</td>
                        <td class="{{ $row['shell'] === '-' ? 'c m' : '' }}">{{ $row['shell'] }}</td>
                        <td class="{{ $row['item'] === '-' ? 'c m' : '' }}">{{ $row['item'] }}</td>
                        <td class="c {{ $row['color'] === '-' ? 'm' : '' }}">{{ $row['color'] }}</td>
                        <td class="c">{{ $row['size'] }}</td>
                        <td class="c">{{ $row['dest'] }}</td>
                        <td class="c">{{ $row['ship_mode'] }}</td>
                        <td class="r">{{ $int($row['orig']) }}</td>
                        <td class="r">{{ $dec($row['cons']) }}</td>
                        <td class="c">{{ $row['unit'] }}</td>
                        <td class="r">{{ $dec($row['req']) }}</td>
                        <td class="r">{{ $pct($row['loss']) }}</td>
                        <td class="r">{{ $dec($row['loss_value']) }}</td>
                        <td class="r b">{{ $dec($row['total']) }}</td>
                        <td class="c">{{ $row['unit'] }}</td>
                        <td class="{{ $row['supplier'] === '-' ? 'c m' : '' }}">{{ $row['supplier'] }}</td>
                        <td class="r">{{ $dec($row['price'], 2) }}</td>
                        <td class="r {{ $row['price_costing'] === null ? 'm' : '' }}">{{ $dec($row['price_costing'], 2) }}</td>
                    </tr>
                @endforeach
            @empty
                <tr><td colspan="19" class="c m">Belum ada material.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="foot">{{ $p['note'] }}</div>

    {{-- Bagian B hanya Manufacturing; Other Cost disembunyikan dulu (sama dengan Excel BOM). Tambahkan 'OTH' untuk menampilkan lagi. --}}
    @php
        $chargeTabs = ['MFG'];
        $chargeRows = array_values(array_filter($p['charges'], fn ($c) => in_array($c['tab'] ?? 'MFG', $chargeTabs, true)));
    @endphp
    <div class="sec">{{ in_array('OTH', $chargeTabs, true) ? 'B. MANUFACTURING & OTHER COST' : 'B. MANUFACTURING' }}</div>
    <table class="grid">
        <thead>
            <tr>
                <th style="width: 4%;">NO</th>
                <th style="width: 34%;">ITEM</th>
                <th style="width: 20%;">SUPPLIER</th>
                <th style="width: 8%;">CONS</th>
                <th style="width: 6%;">UNIT</th>
                <th style="width: 9%;">PRICE</th>
                <th style="width: 6%;">CURR</th>
                <th style="width: 13%;">KETERANGAN</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($chargeRows as $i => $c)
                <tr>
                    <td class="c">{{ $i + 1 }}</td>
                    <td>{{ $c['item'] }}</td>
                    <td class="{{ $c['supplier'] === '-' ? 'c m' : '' }}">{{ $c['supplier'] }}</td>
                    <td class="r">{{ $dec($c['cons']) }}</td>
                    <td class="c">{{ $c['unit'] }}</td>
                    <td class="r">{{ $dec($c['price'], 2) }}</td>
                    <td class="c">{{ $c['currency'] }}</td>
                    <td>{{ $c['note'] }}</td>
                </tr>
            @empty
                <tr><td colspan="8" class="c m">Tidak ada manufacturing.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="notes">Notes : {{ $p['notes'] }}</div>
    <table class="sign">
        <tr>
            <td class="box">
                <div class="t">Created by</div>
                <div class="line"><div class="who">{{ $p['creator'] }}</div><div class="when">{{ $p['created_at'] }}</div></div>
            </td>
            <td class="gap"></td>
            <td class="box">
                <div class="t">Approved by</div>
                <div class="line">@if ($p['approver'])<div class="who">{{ $p['approver'] }}</div><div class="when">{{ $p['approved_at'] }}</div>@endif</div>
            </td>
        </tr>
    </table>
    @if ($di < count($docs) - 1)
        <div class="page-break"></div>
    @endif
@endforeach
</body>
</html>
