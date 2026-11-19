{{-- Tabel satu bagian BOM (Detail & PDF). $section dari MarketingNdsDocumentService::bom(), $cls = class tabel. --}}
@php
    $v = fn ($x) => $x === null || $x === '' ? '-' : $x;
    $n0 = fn ($x) => number_format((float) $x, 0, ',', '.');
    $n2 = fn ($x) => number_format((float) $x, 2, ',', '.');
    $cons = fn ($x) => $x === null ? '-' : rtrim(rtrim(number_format((float) $x, 4, ',', '.'), '0'), ',');
    $price = fn ($x) => $x === null ? '-' : number_format((float) $x, 3, ',', '.');
    $r = $cls === 'grid' ? 'r' : 'n';
@endphp
<table class="{{ $cls }}">
    <thead>
        @if ($section['charge'])
            <tr>
                <th>NO</th>
                <th>CHARGE</th>
                <th>KETERANGAN</th>
                <th>QTY/PC</th>
                <th>UNIT</th>
                <th>SUPPLIER</th>
                <th>PRICE</th>
                <th>PRICE COSTING</th>
                <th>TOTAL QTY</th>
                <th>TOTAL PRICE</th></tr>
        @else
            <tr>
                <th>NO</th>
                <th>CONTENT</th>
                <th>SHELL</th>
                <th>RULE BOM</th>
                <th>COLOR</th>
                <th>SIZE</th>
                <th>ITEM</th>
                <th>DESTINASI</th>
                <th>ORIG QTY</th>
                <th>CONS/PC</th>
                <th>UNIT</th>
                <th>QTY REQ</th>
                <th>LOSS %</th>
                <th>VALUE</th>
                <th>TOTAL QTY REQ</th>
                <th>SUPPLIER</th>
                <th>PRICE</th>
                <th>PRICE COSTING</th>
                <th>TOTAL PRICE</th></tr>
        @endif
    </thead>
    <tbody>
        @foreach ($section['items'] as $no => $item)
            @foreach ($item['lines'] as $i => $l)
                @if ($section['charge'])
                    <tr>
                        <td>{{ $no + 1 }}</td><td class="w">{{ $l['content'] }}</td><td class="w">{{ $v($l['item']) }}</td><td class="{{ $r }}">{{ $cons($l['cons']) }}</td><td>{{ $v($l['unit']) }}</td>
                        <td class="w">{{ $v($l['supplier']) }}</td><td class="{{ $r }}">{{ $price($l['price']) }}</td><td class="{{ $r }}">{{ $price($l['price_costing']) }} {{ $l['currency'] }}</td>
                        <td class="{{ $r }}">{{ $n0($l['total']) }}</td><td class="{{ $r }}">{{ $n2($l['value']) }}</td>
                    </tr>
                @else
                    <tr class="{{ $i > 0 ? 'subrow' : '' }}">
                        <td>{{ $i === 0 ? $no + 1 : '' }}</td>
                        <td class="w">{{ $i === 0 ? $l['content'] : '' }}</td>
                        <td class="w">@if ($i === 0){{ $v($l['shell']) }}@if ($l['parts'])<br><span class="dim">{{ $l['parts'] }}</span>@endif @endif</td>
                        <td>{{ $i === 0 ? $l['rule'] : '' }}</td>
                        <td>{{ $l['color'] ?? 'Semua' }}</td><td>{{ $l['size'] ?? 'Semua' }}</td><td class="w l">{{ $v($l['item']) }}</td><td>{{ $l['dest'] ?? 'Semua' }}</td>
                        <td class="{{ $r }}">{{ $n0($l['orig']) }}</td><td class="{{ $r }}">{{ $cons($l['cons']) }}</td><td>{{ $v($l['unit']) }}</td><td class="{{ $r }}">{{ $n2($l['req']) }}</td>
                        <td class="{{ $r }}">{{ $n0($l['loss']) }}</td><td class="{{ $r }}">{{ $n2($l['loss_value']) }}</td><td class="{{ $r }}">{{ $n0($l['total']) }}</td>
                        <td class="w">{{ $v($l['supplier']) }}</td><td class="{{ $r }}">{{ $price($l['price']) }}</td><td class="{{ $r }}">{{ $price($l['price_costing']) }} {{ $l['currency'] }}</td>
                        <td class="{{ $r }}">{{ $n2($l['value']) }}</td>
                    </tr>
                @endif
            @endforeach
        @endforeach
        <tr class="tot"><td colspan="{{ $section['charge'] ? 9 : 18 }}">TOTAL {{ strtoupper($section['label']) }}</td><td class="{{ $r }}">{{ $n2($section['value']) }}</td></tr>
    </tbody>
</table>
