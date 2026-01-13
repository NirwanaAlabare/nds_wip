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
            <td class="text-nowrap">
                <span class="bg-light text-dark sticky-span">{{ $dailyGroup->ws }}</span>
            </td>
            <td class="text-nowrap">
                <span class="bg-light text-dark sticky-span">{{ $dailyGroup->style }}</span>
            </td>
            <td class="text-nowrap">
                <span class="bg-light text-dark sticky-span">{{ $dailyGroup->color }}</span>
            </td>
            <td class="text-nowrap">
                <span class="bg-light text-dark sticky-span">
                    {{ strtoupper(str_replace('_', ' ', $dailyGroup->meja)) }}
                </span>
            </td>
            <td class="text-nowrap">
                <span class="bg-light text-dark sticky-span">{{ $dailyGroup->panel }}</span>
            </td>

            @if ($groupBy === 'size')
                <td class="text-nowrap">{{ $dailyGroup->size }}</td>
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

                <td class="text-end text-nowrap">
                    {{ num($thisOutput) }}
                </td>
            @endforeach

            <td class="fw-bold text-end text-nowrap fs-5">
                {{ num($thisRowOutput) }}
            </td>

            @php
                $totalOutput += $thisRowOutput;
            @endphp
        </tr>
    @endforeach
    <tr>
        <th colspan="{{ $groupBy === 'size' ? 6 : 5 }}" class="bg-sb text-light text-end">
            TOTAL
        </th>

        @foreach ($dates as $tanggal)
            <td class="fw-bold text-end text-nowrap fs-5 bg-sb text-light">
                {{ num($dateOutputs[$tanggal] ?? 0) }}
            </td>
        @endforeach

        <td class="fw-bold text-end text-nowrap fs-5 bg-sb text-light">
            {{ num($totalOutput) }}
        </td>
    </tr>
</table>

