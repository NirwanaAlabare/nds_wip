<table>
    <tr>
        <td style="text-align: start;" colspan="3">Tanggal Export : {{ $from || $to ? $from." s/d ".$to : 'All Day' }}</td>
    </tr>
    <tr>
        <td style="text-align: start; font-weight: 800;" colspan="3">Chief Performance Range</td>
    </tr>
    @php
        function colorizeEfficiency($efficiency) {
            $color = "";
            switch (true) {
                case $efficiency < 75 :
                    $color = '#dc3545';
                    break;
                case $efficiency >= 75 && $efficiency <= 85 :
                    $color = '#f09900';
                    break;
                case $efficiency > 85 :
                    $color = '#28a745';
                    break;
            }

            return $color;
        }

        function colorizeRft($rft) {
            $color = "";
            switch (true) {
                case $rft < 97 :
                    $color = '#dc3545';
                    break;
                case $rft >= 97 && $rft < 98 :
                    $color = '#f09900';
                    break;
                case $rft >= 98 :
                    $color = '#28a745';
                    break;
            }

            return $color;
        }
    @endphp
    @if ($chiefPerformance && $chiefPerformance->count() > 0)
        {{-- ALL --}}
        <tr>
            <th style="font-weight: 800;text-align: center;vertical-align: middle;" rowspan="2">NAMA CHIEF</th>
            <?php
                if ( $chiefPerformance && $chiefPerformance->count() > 0 ) {
                    foreach ($chiefPerformance->sortBy("tanggal")->groupBy("tanggal") as $dailyDate) {
                        ?>
                            <th style="font-weight: 800; text-align: center;" colspan="2">{{ date_format(date_create($dailyDate->first()->tanggal), "d-m-Y") }}</th>
                        <?php
                    }
            ?>
                    <th style="font-weight: 800; text-align: center;" colspan="2">TOTAL</th>
                    <th style="font-weight: 800;vertical-align: middle;" rowspan="2">RANK</th>
                </tr>
                <tr>
            <?php
                foreach ($chiefPerformance->sortBy("tanggal")->groupBy("tanggal") as $dailyDate) {
                    ?>
                        <th style="font-weight: 800;">Efficiency</th>
                        <th style="font-weight: 800;">RFT</th>
                    <?php
                }
            ?>
                    <th style="font-weight: 800;">Efficiency</th>
                    <th style="font-weight: 800;">RFT</th>
                </tr>
            <?php
                $chiefGroups = $chiefPerformance->groupBy("chief_nik");

                $chiefGroup = $chiefGroups->map(function ($group) {
                    return [
                        'chief_nik' => $group->first()->chief_nik, // opposition_id is constant inside the same group, so just take the first or whatever.
                        'chief_name' => $group->first()->chief_name, // opposition_id is constant inside the same group, so just take the first or whatever.
                        'total_rft' => $group->sum('rft'),
                        'total_output' => $group->sum('output'),
                        'total_mins_prod' => $group->sum('mins_prod'),
                        'total_mins_avail' => $group->sum('mins_avail'),
                    ];
                });

                $sortedChiefGroup = $chiefGroup->sort(function ($a, $b) {
                    $eff_a = $a['total_mins_avail'] > 0 ? $a['total_mins_prod']/$a['total_mins_avail']*100 : '-';
                    $eff_b = $b['total_mins_avail'] > 0 ? $b['total_mins_prod']/$b['total_mins_avail']*100 : '-';

                    return $eff_b - $eff_a;
                });

                $i = 1;
                foreach ($sortedChiefGroup as $chief) {
                    ?>
                        <tr>
                            <td style="font-weight: 800;text-align: center;">{{ $chief['chief_name'] }}</td>
                            @php
                                $sumRft = ($chief['total_output'] > 0 ? round($chief['total_rft']/$chief['total_output'], 2) : 0);
                                $sumEff = ($chief['total_mins_avail']  > 0 ? round($chief['total_mins_prod']/$chief['total_mins_avail'], 2) : 0);
                            @endphp
                            @foreach ($chiefPerformance->sortBy("tanggal")->groupBy("tanggal") as $dailyDate)
                                @php
                                    $thisRft = $chiefPerformance->where('chief_nik', $chief['chief_nik'])->where('tanggal', $dailyDate->first()->tanggal)->sum("rft");
                                    $thisOutput = $chiefPerformance->where('chief_nik', $chief['chief_nik'])->where('tanggal', $dailyDate->first()->tanggal)->sum("output");
                                    $thisMinsProd = $chiefPerformance->where('chief_nik', $chief['chief_nik'])->where('tanggal', $dailyDate->first()->tanggal)->sum("mins_prod");
                                    $thisMinsAvail = $chiefPerformance->where('chief_nik', $chief['chief_nik'])->where('tanggal', $dailyDate->first()->tanggal)->sum("mins_avail");

                                    $thisRftRate = ($thisOutput > 0 ? round(($thisRft/$thisOutput)*100, 2) : 0);
                                    $thisEff = ($thisMinsAvail > 0 ? round(($thisMinsProd/$thisMinsAvail)*100, 2) : 0);
                                @endphp
                                <td style="font-weight: 800;color: {{ colorizeEfficiency($thisEff) }};" data-format="0.00">
                                    {{ $thisEff }} %
                                </td>
                                <td style="font-weight: 800;color: {{ colorizeRft($thisRftRate) }};" data-format="0.00">
                                    {{ $thisRftRate }} %
                                </td>
                            @endforeach
                            <td style="font-weight: 800;color: {{ colorizeEfficiency($sumEff) }};" data-format="0.00">
                                {{ $sumEff }} %
                            </td>
                            <td style="font-weight: 800;color: {{ colorizeRft($sumRft) }};" data-format="0.00%">
                                {{ $sumRft }} %
                            </td>
                            <td style="font-weight: 800;text-align: center;">
                                {{ $i }}
                            </td>
                        </tr>
                    <?php
                    $i++;
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
        {{-- EFF --}}
        <tr>
            <th style="font-weight: 800;text-align: center;vertical-align: middle;color: #ffffff;">CHIEF EFF</th>
            <?php
                if ( $chiefPerformance && $chiefPerformance->count() > 0 ) {
                    foreach ($chiefPerformance->sortBy("tanggal")->groupBy("tanggal") as $dailyDate) {
                        ?>
                            <th style="font-weight: 800; text-align: center;color: #ffffff;">{{ date_format(date_create($dailyDate->first()->tanggal), "d-m-Y") }}</th>
                        <?php
                    }
            ?>
                </tr>
            <?php
                foreach ($sortedChiefGroup as $chief) {
                    ?>
                        <tr>
                            <td style="font-weight: 800;text-align: center;color: #ffffff;">{{ $chief['chief_name'] }}</td>
                            @foreach ($chiefPerformance->sortBy("tanggal")->groupBy("tanggal") as $dailyDate)
                                @php
                                    $thisMinsProd = $chiefPerformance->where('chief_nik', $chief['chief_nik'])->where('tanggal', $dailyDate->first()->tanggal)->sum("mins_prod");
                                    $thisMinsAvail = $chiefPerformance->where('chief_nik', $chief['chief_nik'])->where('tanggal', $dailyDate->first()->tanggal)->sum("mins_avail");

                                    $thisEff = ($thisMinsAvail > 0 ? round(($thisMinsProd/$thisMinsAvail)*100, 2) : 0);
                                @endphp
                                <td style="font-weight: 800;color: #ffffff;" data-format="0.00">
                                    {{ $thisEff }}
                                </td>
                            @endforeach
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
        {{-- RFT --}}
        <tr>
            <th style="font-weight: 800;text-align: center;vertical-align: middle;color: #ffffff;">CHIEF RFT</th>
            <?php
                if ( $chiefPerformance && $chiefPerformance->count() > 0 ) {
                    foreach ($chiefPerformance->sortBy("tanggal")->groupBy("tanggal") as $dailyDate) {
                        ?>
                            <th style="font-weight: 800; text-align: center;color: #ffffff;">{{ date_format(date_create($dailyDate->first()->tanggal), "d-m-Y") }}</th>
                        <?php
                    }
            ?>
                </tr>
            <?php
                foreach ($sortedChiefGroup as $chief) {
                    ?>
                        <tr>
                            <td style="font-weight: 800;text-align: center;color: #ffffff;">{{ $chief['chief_name'] }}</td>
                            @foreach ($chiefPerformance->sortBy("tanggal")->groupBy("tanggal") as $dailyDate)
                                @php
                                    $thisRft = $chiefPerformance->where('chief_nik', $chief['chief_nik'])->where('tanggal', $dailyDate->first()->tanggal)->sum("rft");
                                    $thisOutput = $chiefPerformance->where('chief_nik', $chief['chief_nik'])->where('tanggal', $dailyDate->first()->tanggal)->sum("output");

                                    $thisRftRate = ($thisOutput > 0 ? round(($thisRft/$thisOutput)*100, 2) : 0);
                                @endphp
                                <td style="font-weight: 800;color: #ffffff;" data-format="0.00">
                                    {{ $thisRftRate }}
                                </td>
                            @endforeach
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
    @else
        <tr>
            <td style="text-align:center;">Data tidak ditemukan.</td>
        </tr>
    @endif
</table>
