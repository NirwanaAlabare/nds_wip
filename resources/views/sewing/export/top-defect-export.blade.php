<table>
    <tr>
        <td style="text-align: start; font-weight: 800;" colspan="3">Defect {{ $department == "_packing" ? "Finishing" : "QC" }} {{ $dateFrom." s/d ".$dateTo }}</td>
    </tr>
    <tr>
        <td style="text-align: start; font-weight: 800;" colspan="3">WS : {{ $ws }}</td>
    </tr>
    <tr>
        <td style="text-align: start; font-weight: 800;" colspan="3">Style : {{ $ws && $ws != "All WS" ? $topDefect->groupBy("style")->keys()->implode(", ") : $style }}</td>
    </tr>
    <tr>
        <td style="text-align: start; font-weight: 800;" colspan="3">Color : {{ $color }}</td>
    </tr>
    <tr>
        <td style="text-align: start; font-weight: 800;" colspan="3">Sewing Line : {{ $sewingLine }}</td>
    </tr>
    @php
        $lineStyleGroups = $topDefect->groupBy("grouping");

        $lineStyleGroup = $lineStyleGroups->map(function ($group) {
            return [
                'grouping' => $group->first()->grouping,
                'sewing_line' => $group->first()->sewing_line,
                'ws' => $group->first()->ws,
                'style' => $group->first()->style,
                'color' => $group->first()->color,
                'size' => $group->first()->size,
                'defect_type' => $group->first()->defect_type,
                'total_defect' => $group->sum('total_defect')
            ];
        });

        $sortedLineStyleGroup = $lineStyleGroup->sortBy([
            ['sewing_line', 'asc'],
            ['ws', 'asc'],
            ['style', 'asc'],
            ['color', 'asc'],
            ['total_defect', 'desc'],
        ])->values();
    @endphp
    {{-- Line Style --}}
    <tr>
        <th style="font-weight: 800;text-align: center;vertical-align: middle;border: 1px solid #000;">Line</th>
        <th style="font-weight: 800;text-align: center;vertical-align: middle;border: 1px solid #000;">WS</th>
        <th style="font-weight: 800;text-align: center;vertical-align: middle;border: 1px solid #000;">Style</th>
        <th style="font-weight: 800;text-align: center;vertical-align: middle;border: 1px solid #000;">Color</th>
        <th style="font-weight: 800;text-align: center;vertical-align: middle;border: 1px solid #000;">Size</th>
        <th style="font-weight: 800;text-align: center;vertical-align: middle;border: 1px solid #000;">Defect Type</th>
        <?php
            if ( $topDefect && $topDefect->count() > 0 ) {
                foreach ($topDefect->sortBy("tanggal")->groupBy("tanggal") as $dailyDate) {
                    ?>
                        <th style="font-weight: 800; text-align: center;border: 1px solid #000;">{{ date_format(date_create($dailyDate->first()->tanggal), "d-m-Y") }}</th>
                    <?php
                }
        ?>
        <th style="font-weight: 800;text-align: center;vertical-align: middle;border: 1px solid #000;">Grand Total</th>
    </tr>
        <?php
                foreach ($sortedLineStyleGroup as $lineStyle) {
                    ?>
                        <tr>
                            <td style="border: 1px solid #000;">{{ $lineStyle['sewing_line'] }}</td>
                            <td style="border: 1px solid #000;">{{ $lineStyle['ws'] }}</td>
                            <td style="border: 1px solid #000;">{{ $lineStyle['style'] }}</td>
                            <td style="border: 1px solid #000;">{{ $lineStyle['color'] }}</td>
                            <td style="border: 1px solid #000;">{{ $lineStyle['size'] }}</td>
                            <td style="border: 1px solid #000;">{{ $lineStyle['defect_type'] }}</td>
                            @foreach ($topDefect->sortBy("tanggal")->groupBy("tanggal") as $dailyDate)
                                @php
                                    $thisDefect = $topDefect->where('grouping', $lineStyle['grouping'])->where('tanggal', $dailyDate->first()->tanggal)->sum("total_defect");
                                @endphp
                                <td style="border: 1px solid #000;" data-format="0">
                                    {{ $thisDefect }}
                                </td>
                            @endforeach
                            <td style="font-weight: 800;vertical-align: middle;border: 1px solid #000;" data-format="0">
                                {{ $lineStyle['total_defect'] }}
                            </td>
                        </tr>
                    <?php
                }
            } else {
                ?>
                    <tr>
                        <td style="text-align:center;">Data tidak ditemukan</td>
                    </tr>
                <?php
            }
        ?>
    <tr>
        <th style="font-weight: 800;text-align: center;border: 1px solid #000;" colspan="6">TOTAL</th>
        @foreach ($topDefect->sortBy("tanggal")->groupBy("tanggal") as $dailyDate)
            @php
                $thisDefect = $topDefect->where('tanggal', $dailyDate->first()->tanggal)->sum("total_defect");
            @endphp
            <th style="font-weight: 800;border: 1px solid #000;" data-format="0">
                {{ $thisDefect }}
            </th>
        @endforeach
        <th style="font-weight: 800;border: 1px solid #000;" data-format="0">
            {{ $topDefect->sum("total_defect") }}
        </th>
    </tr>

    <tr></tr>

    {{-- @php
        $lineGroups = $topDefect->groupBy("line_grouping");

        $lineGroup = $lineGroups->map(function ($group) {
            return [
                'line_grouping' => $group->first()->line_grouping,
                'sewing_line' => $group->first()->sewing_line,
                'defect_type' => $group->first()->defect_type,
                'total_defect' => $group->sum('total_defect')
            ];
        });

        $sortedLineGroup = $lineGroup->sortBy([
            ['total_defect', 'desc'],
        ])->values();
    @endphp --}}
    {{-- Line --}}
    {{-- <tr>
        <th style="font-weight: 800;text-align: center;vertical-align: middle;border: 1px solid #000;">Line</th>
        <th style="font-weight: 800;text-align: center;vertical-align: middle;border: 1px solid #000;">Defect Type</th>
        <?php
            if ( $topDefect && $topDefect->count() > 0 ) {
                foreach ($topDefect->sortBy("tanggal")->groupBy("tanggal") as $dailyDate) {
                    ?>
                        <th style="font-weight: 800; text-align: center;border: 1px solid #000;">{{ date_format(date_create($dailyDate->first()->tanggal), "d-m-Y") }}</th>
                    <?php
                }
        ?>
        <th style="font-weight: 800;text-align: center;vertical-align: middle;border: 1px solid #000;">Grand Total</th>
        <th style="font-weight: 800;text-align: center;vertical-align: middle;border: 1px solid #000;">Rank</th>
    </tr>
        <?php
                foreach ($sortedLineGroup as $index => $line) {
                    ?>
                        <tr>
                            <td style="border: 1px solid #000;">{{ $line['sewing_line'] }}</td>
                            <td style="border: 1px solid #000;">{{ $line['defect_type'] }}</td>
                            @foreach ($topDefect->sortBy("tanggal")->groupBy("tanggal") as $dailyDate)
                                @php
                                    $thisDefect = $topDefect->where('line_grouping', $line['line_grouping'])->where('tanggal', $dailyDate->first()->tanggal)->sum("total_defect");
                                @endphp
                                <td style="border: 1px solid #000;" data-format="0">
                                    {{ $thisDefect }}
                                </td>
                            @endforeach
                            <td style="font-weight: 800;vertical-align: middle;border: 1px solid #000;" data-format="0">
                                {{ $line['total_defect'] }}
                            </td>
                            <td style="font-weight: 800;vertical-align: middle;border: 1px solid #000;" data-format="0">{{ intval($index)+1 }}</td>
                        </tr>
                    <?php
                }
            } else {
                ?>
                    <tr>
                        <td style="text-align:center;">Data tidak ditemukan</td>
                    </tr>
                <?php
            }
        ?>
    <tr>
    </tr> --}}

    {{-- @php
        $styleGroup = $topDefect->groupBy("style_grouping");

        $styleGroup = $styleGroup->map(function ($group) {
            return [
                'style_grouping' => $group->first()->style_grouping,
                'style' => $group->first()->style,
                'defect_type' => $group->first()->defect_type,
                'total_defect' => $group->sum('total_defect')
            ];
        });

        $sortedStyleGroup = $styleGroup->sortBy([
            ['total_defect', 'desc'],
        ])->values();
    @endphp --}}
    {{-- Style --}}
    {{-- <tr>
        <th style="font-weight: 800;text-align: center;vertical-align: middle;border: 1px solid #000;">Style</th>
        <th style="font-weight: 800;text-align: center;vertical-align: middle;border: 1px solid #000;">Defect Type</th>
        <?php
            if ( $topDefect && $topDefect->count() > 0 ) {
                foreach ($topDefect->sortBy("tanggal")->groupBy("tanggal") as $dailyDate) {
                    ?>
                        <th style="font-weight: 800; text-align: center;border: 1px solid #000;">{{ date_format(date_create($dailyDate->first()->tanggal), "d-m-Y") }}</th>
                    <?php
                }
        ?>
        <th style="font-weight: 800;text-align: center;vertical-align: middle;border: 1px solid #000;">Grand Total</th>
        <th style="font-weight: 800;text-align: center;vertical-align: middle;border: 1px solid #000;">Rank</th>
    </tr>
        <?php
                foreach ($sortedStyleGroup as $index => $style) {
                    ?>
                        <tr>
                            <td style="border: 1px solid #000;">{{ $style['style'] }}</td>
                            <td style="border: 1px solid #000;">{{ $style['defect_type'] }}</td>
                            @foreach ($topDefect->sortBy("tanggal")->groupBy("tanggal") as $dailyDate)
                                @php
                                    $thisDefect = $topDefect->where('style_grouping', $style['style_grouping'])->where('tanggal', $dailyDate->first()->tanggal)->sum("total_defect");
                                @endphp
                                <td style="border: 1px solid #000;" data-format="0">
                                    {{ $thisDefect }}
                                </td>
                            @endforeach
                            <td style="font-weight: 800;vertical-align: middle;border: 1px solid #000;" data-format="0">
                                {{ $style['total_defect'] }}
                            </td>
                            <td style="font-weight: 800;vertical-align: middle;border: 1px solid #000;" data-format="0">{{ intval($index)+1 }}</td>
                        </tr>
                    <?php
                }
            } else {
                ?>
                    <tr>
                        <td style="text-align:center;">Data tidak ditemukan</td>
                    </tr>
                <?php
            }
        ?>
    <tr>
    </tr> --}}
</table>
