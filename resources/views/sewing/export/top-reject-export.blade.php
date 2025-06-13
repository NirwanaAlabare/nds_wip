<table>
    <tr>
        <td style="text-align: start; font-weight: 800;" colspan="3">Top Reject {{ $department == "_packing" ? "Packing" : "QC" }} {{ $dateFrom." s/d ".$dateTo }}</td>
    </tr>
    <tr>
        <td style="text-align: start; font-weight: 800;" colspan="3">WS : {{ $ws }}</td>
    </tr>
    <tr>
        <td style="text-align: start; font-weight: 800;" colspan="3">Style : {{ $ws && $ws != "All WS" ? $topReject->groupBy("style")->keys()->implode(", ") : $style }}</td>
    </tr>
    <tr>
        <td style="text-align: start; font-weight: 800;" colspan="3">Color : {{ $color }}</td>
    </tr>
    <tr>
        <td style="text-align: start; font-weight: 800;" colspan="3">Sewing Line : {{ $sewingLine }}</td>
    </tr>
    @php
        $lineStyleGroups = $topReject->groupBy("grouping");

        $lineStyleGroup = $lineStyleGroups->map(function ($group) {
            return [
                'grouping' => $group->first()->grouping,
                'sewing_line' => $group->first()->sewing_line,
                'style' => $group->first()->style,
                'color' => $group->first()->color,
                'reject_type' => $group->first()->reject_type,
                'total_reject' => $group->sum('total_reject')
            ];
        });

        $sortedLineStyleGroup = $lineStyleGroup->sortBy([
            ['sewing_line', 'asc'],
            ['style', 'asc'],
            ['color', 'asc'],
            ['total_reject', 'desc'],
        ])->values();
    @endphp
    {{-- Line Style --}}
    <tr>
        <th style="font-weight: 800;text-align: center;vertical-align: middle;border: 1px solid #000;">Line</th>
        <th style="font-weight: 800;text-align: center;vertical-align: middle;border: 1px solid #000;">Style</th>
        <th style="font-weight: 800;text-align: center;vertical-align: middle;border: 1px solid #000;">Color</th>
        <th style="font-weight: 800;text-align: center;vertical-align: middle;border: 1px solid #000;">Reject Type</th>
        <?php
            if ( $topReject && $topReject->count() > 0 ) {
                foreach ($topReject->sortBy("tanggal")->groupBy("tanggal") as $dailyDate) {
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
                            <td style="border: 1px solid #000;">{{ $lineStyle['style'] }}</td>
                            <td style="border: 1px solid #000;">{{ $lineStyle['color'] }}</td>
                            <td style="border: 1px solid #000;">{{ $lineStyle['reject_type'] }}</td>
                            @foreach ($topReject->sortBy("tanggal")->groupBy("tanggal") as $dailyDate)
                                @php
                                    $thisReject = $topReject->where('grouping', $lineStyle['grouping'])->where('tanggal', $dailyDate->first()->tanggal)->sum("total_reject");
                                @endphp
                                <td style="border: 1px solid #000;" data-format="0">
                                    {{ $thisReject }}
                                </td>
                            @endforeach
                            <td style="font-weight: 800;vertical-align: middle;border: 1px solid #000;" data-format="0">
                                {{ $lineStyle['total_reject'] }}
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
        <th style="font-weight: 800;text-align: center;border: 1px solid #000;" colspan="4">TOTAL</th>
        @foreach ($topReject->sortBy("tanggal")->groupBy("tanggal") as $dailyDate)
            @php
                $thisReject = $topReject->where('tanggal', $dailyDate->first()->tanggal)->sum("total_reject");
            @endphp
            <th style="font-weight: 800;border: 1px solid #000;" data-format="0">
                {{ $thisReject }}
            </th>
        @endforeach
        <th style="font-weight: 800;border: 1px solid #000;" data-format="0">
            {{ $topReject->sum("total_reject") }}
        </th>
    </tr>

    <tr></tr>

    @php
        $lineGroups = $topReject->groupBy("line_grouping");

        $lineGroup = $lineGroups->map(function ($group) {
            return [
                'line_grouping' => $group->first()->line_grouping,
                'sewing_line' => $group->first()->sewing_line,
                'reject_type' => $group->first()->reject_type,
                'total_reject' => $group->sum('total_reject')
            ];
        });

        $sortedLineGroup = $lineGroup->sortBy([
            ['total_reject', 'desc'],
        ])->values();
    @endphp
    {{-- Line --}}
    <tr>
        <th style="font-weight: 800;text-align: center;vertical-align: middle;border: 1px solid #000;">Line</th>
        <th style="font-weight: 800;text-align: center;vertical-align: middle;border: 1px solid #000;">Reject Type</th>
        <?php
            if ( $topReject && $topReject->count() > 0 ) {
                foreach ($topReject->sortBy("tanggal")->groupBy("tanggal") as $dailyDate) {
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
                            <td style="border: 1px solid #000;">{{ $line['reject_type'] }}</td>
                            @foreach ($topReject->sortBy("tanggal")->groupBy("tanggal") as $dailyDate)
                                @php
                                    $thisReject = $topReject->where('line_grouping', $line['line_grouping'])->where('tanggal', $dailyDate->first()->tanggal)->sum("total_reject");
                                @endphp
                                <td style="border: 1px solid #000;" data-format="0">
                                    {{ $thisReject }}
                                </td>
                            @endforeach
                            <td style="font-weight: 800;vertical-align: middle;border: 1px solid #000;" data-format="0">
                                {{ $line['total_reject'] }}
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
    </tr>

    @php
        $styleGroup = $topReject->groupBy("style_grouping");

        $styleGroup = $styleGroup->map(function ($group) {
            return [
                'style_grouping' => $group->first()->style_grouping,
                'style' => $group->first()->style,
                'reject_type' => $group->first()->reject_type,
                'total_reject' => $group->sum('total_reject')
            ];
        });

        $sortedStyleGroup = $styleGroup->sortBy([
            ['total_reject', 'desc'],
        ])->values();
    @endphp
    {{-- Style --}}
    <tr>
        <th style="font-weight: 800;text-align: center;vertical-align: middle;border: 1px solid #000;">Style</th>
        <th style="font-weight: 800;text-align: center;vertical-align: middle;border: 1px solid #000;">Reject Type</th>
        <?php
            if ( $topReject && $topReject->count() > 0 ) {
                foreach ($topReject->sortBy("tanggal")->groupBy("tanggal") as $dailyDate) {
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
                            <td style="border: 1px solid #000;">{{ $style['reject_type'] }}</td>
                            @foreach ($topReject->sortBy("tanggal")->groupBy("tanggal") as $dailyDate)
                                @php
                                    $thisReject = $topReject->where('style_grouping', $style['style_grouping'])->where('tanggal', $dailyDate->first()->tanggal)->sum("total_reject");
                                @endphp
                                <td style="border: 1px solid #000;" data-format="0">
                                    {{ $thisReject }}
                                </td>
                            @endforeach
                            <td style="font-weight: 800;vertical-align: middle;border: 1px solid #000;" data-format="0">
                                {{ $style['total_reject'] }}
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
    </tr>
</table>
