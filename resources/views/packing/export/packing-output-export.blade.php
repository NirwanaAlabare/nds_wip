@php
    $useSize  = $groupBy === 'size';
    $colspan  = ($useSize ? 8 : 7) + count($dates);
@endphp
<table class="table table-bordered">
    <tr>
        <td colspan="{{ $colspan }}" style="text-align: center;">Tanggal Export : {{ $dateFrom || $dateTo ? $dateFrom." - ".$dateTo : 'All Day' }}</td>
    </tr>
    <tr>
        <td colspan="{{ $colspan }}" style="text-align: center; font-weight: 800;">{{ $buyer ? "'".$buyerName."'" : '' }} {{ $order ? "'".$order."'" : '' }} Packing Output</td>
    </tr>

    @if ($orderGroup && $orderGroup->count() > 0)
        <tr>
            <th style="font-weight: 800;">No. WS</th>
            <th style="font-weight: 800;">Style</th>
            <th style="font-weight: 800;">Color</th>
            <th style="font-weight: 800;">Line</th>
            <th style="font-weight: 800;">PO</th>
            <th style="font-weight: 800;">Quality</th>
            @if ($useSize)
                <th style="font-weight: 800;">Size</th>
            @endif
            @foreach ($dates as $date)
                <th style="font-weight: 800;">{{ \Carbon\Carbon::parse($date)->format('d-m-Y') }}</th>
            @endforeach
            <th style="font-weight: 800;" class="text-center">TOTAL</th>
        </tr>

        @foreach ($orderGroup as $row)
            @php
                $key = implode('|', [
                    $row->ws,
                    $row->style,
                    $row->color,
                    $row->sewing_line,
                    $row->po,
                    $row->type,
                    $useSize ? $row->size : '_',
                ]);
            @endphp
            <tr>
                <td style="vertical-align: top;text-align: left;">{{ $row->ws }}</td>
                <td style="vertical-align: top;text-align: left;">{{ $row->style }}</td>
                <td style="vertical-align: top;text-align: left;">{{ $row->color }}</td>
                <td style="vertical-align: top;text-align: left;">{{ strtoupper(str_replace('_', ' ', $row->sewing_line)) }}</td>
                <td style="vertical-align: top;text-align: left;">{{ $row->po }}</td>
                <td style="vertical-align: top;text-align: left;">{{ strtoupper($row->type) }}</td>
                @if ($useSize)
                    <td style="vertical-align: top;text-align: left;">{{ $row->size }}</td>
                @endif

                @foreach ($dates as $date)
                    <td>{{ $outputMap[$key][$date] ?? 0 }}</td>
                @endforeach

                <td style="font-weight: 800;">{{ $rowTotals[$key] ?? 0 }}</td>
            </tr>
        @endforeach

        <tr>
            <th colspan="{{ $useSize ? 7 : 6 }}" style="font-weight: 800;">TOTAL</th>
            @foreach ($dates as $date)
                <td style="font-weight: 800;">{{ $dateTotals[$date] ?? 0 }}</td>
            @endforeach
            <td style="font-weight: 800;">{{ $grandTotal }}</td>
        </tr>
    @else
        <tr>
            <td style="text-align:center;" colspan="{{ $colspan }}">Data tidak ditemukan.</td>
        </tr>
    @endif
</table>
