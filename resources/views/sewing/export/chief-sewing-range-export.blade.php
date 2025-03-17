<table>
    <tr>
        <td style="text-align: center;">Tanggal Export : {{ $from || $to ? $from." - ".$to : 'All Day' }}</td>
    </tr>
    <tr>
        <td style="text-align: center; font-weight: 800;">Chief Performance Range</td>
    </tr>
    @if ($chiefPerformance && $chiefPerformance->count() > 0)
        <tr>
            <th style="font-weight: 800;" rowspan="2">Nama Chief</th>
            <?php
                if ( $chiefPerformance && $chiefPerformance->count() > 0 ) {
                    foreach ($chiefPerformance->sortBy("tanggal")->groupBy("tanggal") as $dailyDate) {
                        ?>
                            <th style="font-weight: 800;" colspan="2">{{ date_format(date_create($dailyDate->first()->tanggal), "d-m-Y") }}</th>
                        <?php
                    }
            ?>
                    <th style="font-weight: 800;" colspan="2">TOTAL</th>
                </tr>
                <tr>
            <?php
                foreach ($chiefPerformance->sortBy("tanggal")->groupBy("tanggal") as $dailyDate) {
                    ?>
                        <th style="font-weight: 800;">RFT</th>
                        <th style="font-weight: 800;">Efficiency</th>
                    <?php
                }
            ?>
                    <th style="font-weight: 800;">RFT</th>
                    <th style="font-weight: 800;">Efficiency</th>
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

                foreach ($sortedChiefGroup as $chief) {
                    ?>
                        <tr>
                            <td style="text-align: left;vertical-align: top;">{{ $chief['chief_name'] }}</td>
                            @php
                                $sumRft = 0;
                                $sumOutput = 0;
                                $sumMinsProd = 0;
                                $sumMinsAvail = 0;
                            @endphp
                            @foreach ($chiefPerformance->sortBy("tanggal")->groupBy("tanggal") as $dailyDate)
                                @php
                                    $thisRft = $chiefPerformance->where('chief_nik', $chief['chief_nik'])->where('tanggal', $dailyDate->first()->tanggal)->sum("rft");
                                    $thisOutput = $chiefPerformance->where('chief_nik', $chief['chief_nik'])->where('tanggal', $dailyDate->first()->tanggal)->sum("output");
                                    $thisMinsProd = $chiefPerformance->where('chief_nik', $chief['chief_nik'])->where('tanggal', $dailyDate->first()->tanggal)->sum("mins_prod");
                                    $thisMinsAvail = $chiefPerformance->where('chief_nik', $chief['chief_nik'])->where('tanggal', $dailyDate->first()->tanggal)->sum("mins_avail");

                                    $sumRft += $thisRft;
                                    $sumOutput += $thisOutput;
                                    $sumMinsProd += $thisMinsProd;
                                    $sumMinsAvail += $thisMinsAvail;
                                @endphp
                                <td>
                                    {{ ($thisRft > 0 ? round($thisRft/$thisOutput*100, 2) : 0) }} %
                                </td>
                                <td>
                                    {{ ($thisMinsAvail > 0 ? round($thisMinsProd/$thisMinsAvail*100, 2) : 0) }} %
                                </td>
                            @endforeach
                            <td style="font-weight: 800;">
                                {{ ($chief['total_output'] > 0 ? round($chief['total_rft']/$chief['total_output']*100, 2) : 0) }} %
                            </td>
                            <td style="font-weight: 800;">
                                {{ ($chief['total_mins_avail']  > 0 ? round($chief['total_mins_prod']/$chief['total_mins_avail']*100, 2) : 0) }} %
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
    @else
        <tr>
            <td style="text-align:center;">Data tidak ditemukan.</td>
        </tr>
    @endif
</table>
