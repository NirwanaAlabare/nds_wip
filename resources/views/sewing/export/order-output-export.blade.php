<table class="table table-bordered">
    <tr>
        <td colspan="{{ ($groupBy == "size" ? 6 : 5) + $orderOutputs->sortBy("tanggal")->groupBy("tanggal")->count() }}" style="text-align: center;">Tanggal Export : {{ $dateFrom || $dateTo ? $dateFrom." - ".$dateTo : 'All Day' }}</td>
    </tr>
    <tr>
        <td colspan="{{ ($groupBy == "size" ? 6 : 5) + $orderOutputs->sortBy("tanggal")->groupBy("tanggal")->count() }}" style="text-align: center; font-weight: 800;">{{ $buyer ? "'".$buyerName."'" : '' }} {{ $order ? "'".$order."'" : '' }} Output {{ $outputType && $outputType == "_packing" ? "FINISHING" : "SEWING" }}</td>
    </tr>
    <tr>
        <th style="font-weight: 800;">No. WS</th>
        <th style="font-weight: 800;">Style</th>
        <th style="font-weight: 800;">Color</th>
        <th style="font-weight: 800;">Line</th>

        @if ($groupBy === 'size')
            <th style="font-weight: 800;">Size</th>
        @endif

        @foreach ($dates as $date)
            <th style="font-weight: 800;">
                {{ \Carbon\Carbon::parse($date)->format('d-m-Y') }}
            </th>
        @endforeach

        <th style="font-weight: 800;">TOTAL</th>
    </tr>

    @php $useSize = $groupBy === 'size'; @endphp

    @foreach ($orderGroup as $row)
        @if (is_object($row))
            @php
                $key = implode('|', [
                    $row->ws,
                    $row->style,
                    $row->color,
                    $row->sewing_line,
                    $useSize ? $row->size : '_',
                ]);
            @endphp

            <tr>
                <td>
                    <span>{{ $row->ws }}</span>
                </td>
                <td>
                    <span>{{ $row->style }}</span>
                </td>
                <td>
                    <span>{{ $row->color }}</span>
                </td>
                <td>
                    <span>
                        {{ strtoupper(str_replace('_', ' ', $row->sewing_line)) }}
                    </span>
                </td>

                @if ($useSize)
                    <td>{{ $row->size }}</td>
                @endif

                @foreach ($dates as $date)
                    <td>
                        {{ $outputMap[$key][$date] ?? 0 }}
                    </td>
                @endforeach

                <td>
                    {{ $rowTotals[$key] ?? 0 }}
                </td>
            </tr>
        @endif
    @endforeach

    <tr>
        <td
            style="font-weight: 800;"
            colspan="{{ $useSize ? 5 : 4 }}"
        >
            TOTAL
        </td>

        @foreach ($dates as $date)
            <td style="font-weight: 800;">
                {{ $dateTotals[$date] ?? 0 }}
            </td>
        @endforeach

        <td style="font-weight: 800;">
            {{ $grandTotal }}
        </td>
    </tr>
</table>
