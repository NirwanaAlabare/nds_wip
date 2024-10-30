<table class="table table-bordered">
    <tr>
        <td colspan="{{ ($groupBy == "size" ? 7 : 6) + $orderOutputs->sortBy("tanggal")->groupBy("tanggal")->count() }}" style="text-align: center;">Tanggal Export : {{ $dateFrom || $dateTo ? $dateFrom." - ".$dateTo : 'All Day' }}</td>
    </tr>
    <tr>
        <td colspan="{{ ($groupBy == "size" ? 7 : 6) + $orderOutputs->sortBy("tanggal")->groupBy("tanggal")->count() }}" style="text-align: center; font-weight: 800;">{{ $buyer ? "'".$buyerName."'" : '' }} {{ $order ? "'".$order."'" : '' }} Cutting Output</td>
    </tr>
    @if ($orderOutputs && $orderOutputs->count() > 0)
        <tr>
            <th style="font-weight: 800;">No. WS</th>
            <th style="font-weight: 800;">Style</th>
            <th style="font-weight: 800;">Color</th>
            <th style="font-weight: 800;">Meja</th>
            <th style="font-weight: 800;">Panel</th>
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
                    $currentMeja = null;
                    $currentPanel = null;
                    $currentSize = null;

                    $dateOutputs = collect();
                    $totalOutput = null;

                    foreach ($orderGroup as $group) {
                        ?>
                            <tr>
                                @if ($group->ws != $currentWs)
                                    <td style="vertical-align: top;" rowspan="{{ $orderGroup->where('ws', $group->ws)->count(); }}">{{ $group->ws }}</td>

                                    @php
                                        $currentWs = $group->ws;
                                        $currentStyle = null;
                                        $currentColor = null;
                                        $currentMeja = null;
                                    @endphp
                                @endif
                                @if ($group->ws == $currentWs && $group->style != $currentStyle)
                                    <td style="vertical-align: top;" rowspan="{{ $orderGroup->where('ws', $group->ws)->where('style', $group->style)->count(); }}">{{ $group->style }}</td>

                                    @php
                                        $currentStyle = $group->style;
                                        $currentColor = null;
                                        $currentMeja = null;
                                    @endphp
                                @endif
                                @if ($group->ws == $currentWs && $group->style == $currentStyle && $group->color != $currentColor)
                                    <td style="vertical-align: top;" rowspan="{{ $orderGroup->where('ws', $group->ws)->where('style', $group->style)->where('color', $group->color)->count(); }}">{{ $group->color }}</td>

                                    @php
                                        $currentColor = $group->color;
                                        $currentMeja = null;
                                        $currentPanel = null;
                                    @endphp
                                @endif
                                @if ($group->ws == $currentWs && $group->style == $currentStyle && $group->color == $currentColor && $group->id_meja != $currentMeja)
                                    <td style="vertical-align: top;" rowspan="{{ $orderGroup->where('ws', $group->ws)->where('style', $group->style)->where('color', $group->color)->where('id_meja', $group->id_meja)->count(); }}">{{ strtoupper(str_replace('_', ' ', $group->meja)) }}</td>

                                    @php
                                        $currentMeja = $group->id_meja;
                                        $currentPanel = null;
                                    @endphp
                                @endif
                                @if ($group->ws == $currentWs && $group->style == $currentStyle && $group->color == $currentColor && $group->id_meja == $currentMeja && $group->panel != $currentPanel)
                                    <td style="vertical-align: top;" rowspan="{{ $orderGroup->where('ws', $group->ws)->where('style', $group->style)->where('color', $group->color)->where('id_meja', $group->id_meja)->where('panel', $group->panel)->count(); }}">{{ $group->panel }}</td>

                                    @php
                                        $currentPanel = $group->panel;
                                    @endphp
                                @endif
                                @if ($groupBy == "size")
                                    <td style="vertical-align: top;">{{ $group->size }}</td>
                                @endif

                                @php
                                    $thisRowOutput = 0;
                                @endphp
                                @foreach ($orderOutputs->sortBy("tanggal")->groupBy("tanggal") as $dailyDate)
                                    @php
                                        $thisOutput = 0;

                                        if ($groupBy == 'size') {
                                            $thisOutput = $orderOutputs->where('ws', $group->ws)->where('style', $group->style)->where('color', $group->color)->where('panel', $group->panel)->where('id_meja', $group->id_meja)->where('tanggal', $dailyDate->first()->tanggal)->where('size', $group->size)->sum("qty");
                                        } else {
                                            $thisOutput = $orderOutputs->where('ws', $group->ws)->where('style', $group->style)->where('color', $group->color)->where('panel', $group->panel)->where('id_meja', $group->id_meja)->where('tanggal', $dailyDate->first()->tanggal)->sum("output");
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
                            <td style="text-align:center;" colspan="{{ $groupBy == "size" ? '8' : '5' }}">Data tidak ditemukan</td>
                        </tr>
                    <?php
                }
            ?>
    @else
        <tr>
            <td style="text-align:center;" colspan="{{ $groupBy == "size" ? "13" : "12" }}">Data tidak ditemukan.</td>
        </tr>
    @endif

    <tr>
        <th colspan="{{ $groupBy == "size" ? '6' : '5' }}" style="font-weight: 800;">
            TOTAL
        </th>
        @if ( $orderOutputs && $orderOutputs->count() > 0)
            @foreach ($dateOutputs as $dateOutput)
                <td style="font-weight: 800;">
                    {{ $dateOutput }}
                </td>
            @endforeach
            <td style="font-weight: 800;">{{ $totalOutput }}</td>
        @endif
    </tr>
</table>
