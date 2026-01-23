<table>
    <tr>
        <td colspan="{{ ($groupBy == "size" ? 7 : 6) + $dates->count() }}">Tanggal Export : {{ $dateFrom || $dateTo ? $dateFrom." - ".$dateTo : 'All Day' }}</td>
    </tr>
    <tr>
        <td colspan="{{ ($groupBy == "size" ? 7 : 6) + $dates->count() }}" style="font-weight: 800;">{{ $buyer ? "'".$buyerName."'" : '' }} {{ $order ? "'".$order."'" : '' }} Cutting Output</td>
    </tr>
    <tr>
        <td style="font-weight: 800;">No. WS</td>
        <td style="font-weight: 800;">Style</td>
        <td style="font-weight: 800;">Color</td>
        <td style="font-weight: 800;">Meja</td>
        <td style="font-weight: 800;">Panel</td>

        @if ($groupBy === 'size')
            <td>Size</td>
        @endif

        @foreach ($dates as $tanggal)
            <th style="font-weight: 800;">
                {{ \Carbon\Carbon::parse($tanggal)->format('d-m-Y') }}
            </th>
        @endforeach

        <th style="font-weight: 800;">TOTAL</th>
    </tr>
    @php
        $dateOutputs = collect();
        $totalOutput = 0;
    @endphp

    @foreach ($dailyOrderGroup as $dailyGroup)
        @php
            $thisRowOutput = 0;
        @endphp

        <tr>
            <td>
                <span>{{ $dailyGroup->ws }}</span>
            </td>
            <td>
                <span>{{ $dailyGroup->style }}</span>
            </td>
            <td>
                <span>{{ $dailyGroup->color }}</span>
            </td>
            <td>
                <span>
                    {{ strtoupper(str_replace('_', ' ', $dailyGroup->meja)) }}
                </span>
            </td>
            <td>
                <span>{{ $dailyGroup->panel }}</span>
            </td>

            @if ($groupBy === 'size')
                <td>{{ $dailyGroup->size }}</td>
            @endif

            @foreach ($dates as $tanggal)
                @php
                    $key = implode('|', [
                        $dailyGroup->ws,
                        $dailyGroup->style,
                        $dailyGroup->color,
                        $dailyGroup->panel,
                        $dailyGroup->id_meja,
                        $groupBy === 'size' ? $dailyGroup->size : 'all',
                        $tanggal,
                    ]);

                    $thisOutput = $outputSums[$key] ?? 0;
                    $thisRowOutput += $thisOutput;

                    $dateOutputs[$tanggal] = ($dateOutputs[$tanggal] ?? 0) + $thisOutput;
                @endphp

                <td style="text-align: right;">
                    {{ $thisOutput }}
                </td>
            @endforeach

            <td style="font-weight: 800;">
                {{ $thisRowOutput }}
            </td>

            @php
                $totalOutput += $thisRowOutput;
            @endphp
        </tr>
    @endforeach
    <tr>
        <th colspan="{{ $groupBy === 'size' ? 6 : 5 }}" style="font-weight: 800;">
            TOTAL
        </th>

        @foreach ($dates as $tanggal)
            <td style="font-weight: 800;">
                {{$dateOutputs[$tanggal] ?? 0 }}
            </td>
        @endforeach

        <td style="font-weight: 800;">
            {{$totalOutput }}
        </td>
    </tr>
</table>

