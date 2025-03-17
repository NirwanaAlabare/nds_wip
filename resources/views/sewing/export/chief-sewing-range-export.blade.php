<table>
    <tr>
        <td colspan="{{ $chiefPerformance->sortBy("tanggal")->groupBy("tanggal")->count() }}" style="text-align: center;">Tanggal Export : {{ $from || $to ? $from." - ".$to : 'All Day' }}</td>
    </tr>
    <tr>
        <td colspan="{{ $chiefPerformance->sortBy("tanggal")->groupBy("tanggal")->count() }}" style="text-align: center; font-weight: 800;">Chief Performance Range</td>
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
                foreach ($chiefPerformance->groupBy("chief_nik") as $key => $chief) {
                    ?>
                        <tr>
                            <td style="text-align: left;vertical-align: top;">{{ $chief->first()->chief_name }}</td>
                            @php
                                $sumRft = 0;
                                $sumOutput = 0;
                                $sumMinsProd = 0;
                                $sumMinsAvail = 0;
                            @endphp
                            @foreach ($chiefPerformance->sortBy("tanggal")->groupBy("tanggal") as $dailyDate)
                                @php
                                    $thisRft = $chiefPerformance->where('chief_nik', $key)->where('tanggal', $dailyDate->first()->tanggal)->sum("rft");
                                    $thisOutput = $chiefPerformance->where('chief_nik', $key)->where('tanggal', $dailyDate->first()->tanggal)->sum("output");
                                    $thisMinsProd = $chiefPerformance->where('chief_nik', $key)->where('tanggal', $dailyDate->first()->tanggal)->sum("mins_prod");
                                    $thisMinsAvail = $chiefPerformance->where('chief_nik', $key)->where('tanggal', $dailyDate->first()->tanggal)->sum("mins_avail");

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
                                {{ ($sumRft > 0 ? round($sumRft/$sumOutput*100, 2) : 0) }} %
                            </td>
                            <td style="font-weight: 800;">
                                {{ ($sumMinsAvail > 0 ? round($sumMinsProd/$sumMinsAvail*100, 2) : 0) }} %
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
