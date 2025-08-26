<table class="table table-bordered">
    <tr>
        <td colspan="{{ ($groupBy == "size" ? 6 : 5) + $orderOutputs->sortBy("tanggal")->groupBy("tanggal")->count() }}" style="text-align: center;">Tanggal Export : {{ $dateFrom || $dateTo ? $dateFrom." - ".$dateTo : 'All Day' }}</td>
    </tr>
    <tr>
        <td colspan="{{ ($groupBy == "size" ? 6 : 5) + $orderOutputs->sortBy("tanggal")->groupBy("tanggal")->count() }}" style="text-align: center; font-weight: 800;">{{ $buyer ? "'".$buyerName."'" : '' }} {{ $order ? "'".$order."'" : '' }} Output {{ $outputType && $outputType == "_packing" ? "FINISHING" : "SEWING" }}</td>
    </tr>
    @if ($orderOutputs && $orderOutputs->count() > 0)
        <tr>
            <th style="font-weight: 800;">No. WS</th>
            <th style="font-weight: 800;">Style</th>
            <th style="font-weight: 800;">Color</th>
            <th style="font-weight: 800;">Line</th>
            @if ($groupBy == 'size')
                <th style="font-weight: 800;">Size</th>
            @endif
            <?php
                if ( $orderOutputs && $orderOutputs->count() > 0 ) {
                    foreach ($orderOutputs->sortBy("tanggal")->groupBy("tanggal") as $dailyDate) {
                        ?>
                            <th style="font-weight: 800;">{{ date_format(date_create($dailyDate->first()->tanggal), "d-m-Y") }}</th>
                        <?php
                    }

            ?>
                    <th style="font-weight: 800;" class="text-center">TOTAL</th>
                </tr>
            <?php

                    $currentWs = null;
                    $currentStyle = null;
                    $currentColor = null;
                    $currentLine = null;
                    $currentSize = null;

                    $dateOutputs = collect();
                    $totalOutput = null;

                    foreach ($orderGroup as $group) {
                        ?>
                            <tr>
                                {{-- @if ($group->ws != $currentWs)
                                    <td style="text-align: left;vertical-align: top;" rowspan="{{ $orderGroup->where('ws', $group->ws)->count() }}">{{ $group->ws }}</td>

                                    @php
                                        $currentWs = $group->ws;
                                        $currentStyle = null;
                                        $currentColor = null;
                                        $currentLine = null;
                                    @endphp
                                @endif
                                @if ($group->ws == $currentWs && $group->style != $currentStyle)
                                    <td style="text-align: left;vertical-align: top;" rowspan="{{ $orderGroup->where('ws', $group->ws)->where('style', $group->style)->count() }}">{{ $group->style }}</td>

                                    @php
                                        $currentStyle = $group->style;
                                        $currentColor = null;
                                        $currentLine = null;
                                    @endphp
                                @endif
                                @if ($group->ws == $currentWs && $group->style == $currentStyle && $group->color != $currentColor)
                                    <td style="text-align: left;vertical-align: top;" rowspan="{{ $orderGroup->where('ws', $group->ws)->where('style', $group->style)->where('color', $group->color)->count() }}">{{ $group->color }}</td>

                                    @php
                                        $currentColor = $group->color;
                                        $currentLine = null;
                                    @endphp
                                @endif
                                @if ($group->ws == $currentWs && $group->style == $currentStyle && $group->color == $currentColor && $group->sewing_line != $currentLine)
                                    <td style="text-align: left;vertical-align: top;" rowspan="{{ $orderGroup->where('ws', $group->ws)->where('style', $group->style)->where('color', $group->color)->where('sewing_line', $group->sewing_line)->count() }}">{{ strtoupper(str_replace('_', ' ', $group->sewing_line)) }}</td>

                                    @php
                                        $currentLine = $group->sewing_line;
                                    @endphp
                                @endif --}}
                                <td style="text-align: left;vertical-align: top;" >{{ $group->ws }}</td>
                                <td style="text-align: left;vertical-align: top;" >{{ $group->style }}</td>
                                <td style="text-align: left;vertical-align: top;" >{{ $group->color }}</td>
                                <td style="text-align: left;vertical-align: top;" >{{ strtoupper(str_replace('_', ' ', $group->sewing_line)) }}</td>

                                @if ($groupBy == "size")
                                    <td style="text-align: left;vertical-align: top;">{{ $group->size }}</td>
                                @endif

                                @php
                                    $thisRowOutput = 0;
                                @endphp
                                @foreach ($orderOutputs->sortBy("tanggal")->groupBy("tanggal") as $dailyDate)
                                    @php
                                        $thisOutput = 0;

                                        if ($groupBy == 'size') {
                                            $thisOutput = $orderOutputs->where('ws', $group->ws)->where('style', $group->style)->where('color', $group->color)->where('sewing_line', $group->sewing_line)->where('tanggal', $dailyDate->first()->tanggal)->where('size', $group->size)->sum("output");
                                        } else {
                                            $thisOutput = $orderOutputs->where('ws', $group->ws)->where('style', $group->style)->where('color', $group->color)->where('sewing_line', $group->sewing_line)->where('tanggal', $dailyDate->first()->tanggal)->sum("output");
                                        }

                                        if (isset($dateOutputs[$dailyDate->first()->tanggal])) {
                                            $dateOutputs[$dailyDate->first()->tanggal] += $thisOutput;
                                        } else {
                                            if ($dailyDate->first()->tanggal != "0" && $dailyDate->first()->tanggal != null && $dailyDate->first()->tanggal != "") {
                                                $dateOutputs->put($dailyDate->first()->tanggal, $thisOutput);
                                            }
                                        }
                                        $thisRowOutput += $thisOutput;
                                    @endphp

                                    <td>
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
                        <?php
                    }
                } else {
                    ?>
                        <tr>
                            <td style="text-align:center;" colspan="{{ $groupBy == "size" ? '7' : '4' }}">Data tidak ditemukan</td>
                        </tr>
                    <?php
                }
            ?>
    @else
        <tr>
            <td style="text-align:center;" colspan="{{ $groupBy == "size" ? "12" : "11" }}">Data tidak ditemukan.</td>
        </tr>
    @endif

    <tr>
        <th colspan="{{ $groupBy == "size" ? '5' : '4' }}" style="font-weight: 800;">
            TOTAL
        </th>
        @if ($orderOutputs && $orderOutputs->count() > 0)
            @foreach ($dateOutputs as $dateOutput)
                <td style="font-weight: 800;">
                    {{ $dateOutput }}
                </td>
            @endforeach
            <td style="font-weight: 800;">{{ $totalOutput }}</td>
        @endif
    </tr>
</table>
