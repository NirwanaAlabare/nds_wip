<table>
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
            $rft_a = $a['total_output'] > 0 ? $a['total_rft']/$a['total_output']*100 : '-';
            $eff_b = $b['total_mins_avail'] > 0 ? $b['total_mins_prod']/$b['total_mins_avail']*100 : '-';
            $rft_b = $b['total_output'] > 0 ? $b['total_rft']/$b['total_output']*100 : '-';

            return ($eff_b+$rft_b) - ($eff_a+$rft_a);
        });
    @endphp
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
    @if ((($rowCount+2)*2) < 29)
        @for ($i = 0; $i < (29-(($rowCount+2)*2)); $i++)
            <tr>
                <td></td>
            </tr>
        @endfor
    @endif
    <tr>
        <td style="text-align: start;" colspan="3">Tanggal Export : {{ $from || $to ? $from." s/d ".$to : 'All Day' }}</td>
    </tr>
    <tr>
        <td style="text-align: start; font-weight: 800;" colspan="3">Chief Performance Range</td>
    </tr>
    @if ($chiefPerformance && $chiefPerformance->count() > 0)
        {{-- ALL --}}
        <tr>
            <th style="font-weight: 800;text-align: center;vertical-align: middle;border: 1px solid #000000;" rowspan="2">NAMA CHIEF</th>
            <?php
                if ( $chiefPerformance && $chiefPerformance->count() > 0 ) {
                    foreach ($chiefPerformance->sortBy("tanggal")->groupBy("tanggal") as $dailyDate) {
                        ?>
                            <th style="font-weight: 800; text-align: center;border: 1px solid #000000;" colspan="2">{{ date_format(date_create($dailyDate->first()->tanggal), "d-m-Y") }}</th>
                        <?php
                    }
            ?>
                    <th style="font-weight: 800; text-align: center;border: 1px solid #000000;" colspan="2">TOTAL</th>
                    <th style="font-weight: 800;vertical-align: middle;border: 1px solid #000000;" rowspan="2">RANK</th>
                </tr>
                <tr>
            <?php
                foreach ($chiefPerformance->sortBy("tanggal")->groupBy("tanggal") as $dailyDate) {
                    ?>
                        <th style="font-weight: 800;border: 1px solid #000000;">Efficiency</th>
                        <th style="font-weight: 800;border: 1px solid #000000;">RFT</th>
                    <?php
                }
            ?>
                    <th style="font-weight: 800;border: 1px solid #000000;">Efficiency</th>
                    <th style="font-weight: 800;border: 1px solid #000000;">RFT</th>
                </tr>
            <?php
                $i = 1;
                foreach ($sortedChiefGroup as $chief) {
                    ?>
                        <tr>
                            <td style="font-weight: 800;text-align: center;border: 1px solid #000000;">{{ $chief['chief_name'] }}</td>
                            @php
                                $sumRft = ($chief['total_output'] > 0 ? round(($chief['total_rft']/$chief['total_output'])*100, 2) : 0);
                                $sumEff = ($chief['total_mins_avail']  > 0 ? round(($chief['total_mins_prod']/$chief['total_mins_avail'])*100, 2) : 0);
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
                                <td style="font-weight: 800;border: 1px solid #000000;color: {{ colorizeEfficiency($thisEff) }};" data-format="0.00">
                                    {{ $thisEff }} %
                                </td>
                                <td style="font-weight: 800;border: 1px solid #000000;color: {{ colorizeRft($thisRftRate) }};" data-format="0.00">
                                    {{ $thisRftRate }} %
                                </td>
                            @endforeach
                            <td style="font-weight: 800;border: 1px solid #000000;color: {{ colorizeEfficiency($sumEff) }};" data-format="0.00">
                                {{ $sumEff }} %
                            </td>
                            <td style="font-weight: 800;border: 1px solid #000000;color: {{ colorizeRft($sumRft) }};" data-format="0.00%">
                                {{ $sumRft }} %
                            </td>
                            <td style="font-weight: 800;border: 1px solid #000000;text-align: center;">
                                {{ $i }}
                            </td>
                        </tr>
                    <?php
                    $i++;
                }
            } else {
                ?>
                    <tr>
                        <td style="text-align:center;border: 1px solid #000000;">Data tidak ditemukan</td>
                    </tr>
                <?php
            }
        ?>
        <tr>
        </tr>
    @else
        <tr>
            <td style="text-align:center;border: 1px solid #000000;">Data tidak ditemukan.</td>
        </tr>
    @endif
</table>
