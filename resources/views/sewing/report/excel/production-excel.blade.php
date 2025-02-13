<!DOCTYPE html>
<html lang="en">
<table>
    <tr>
        <td>{{ $tanggal }}</td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
    </tr>
    <tr>
        <th style="background-color: #004391; color: #fbfbfb">Line</th>
        <th style="background-color: #004391; color: #fbfbfb">Buyer</th>
        <th style="background-color: #004391; color: #fbfbfb">WS Number</th>
        <th style="background-color: #004391; color: #fbfbfb">Style</th>
        <th style="background-color: #004391; color: #fbfbfb">Chief</th>
        <th style="background-color: #004391; color: #fbfbfb">Leader</th>
        <th style="background-color: #004391; color: #fbfbfb">Admin</th>
        <th style="background-color: #004391; color: #fbfbfb">Jumlah OP</th>
        <th style="background-color: #004391; color: #fbfbfb">SMV</th>
        <th style="background-color: #004391; color: #fbfbfb">Target (/Jam)</th>
        <th style="background-color: #004391; color: #fbfbfb">Target (/Hari)</th>
        @foreach ($jamProduksi as $jam)
            <th style="background-color: #ffa600; color: #fbfbfb">{{ $jam }}</th>
        @endforeach
        <th style="background-color: #004391; color: #fbfbfb">Total</th>
        <th style="background-color: #004391; color: #fbfbfb">Efficiency</th>
    </tr>
    @php
        $sumTargetJam = 0;
        $sumTargetToday = 0;
        $sumManPower = 0;
        $sumTotal = 0;
        $sumEfficiency = 0;
        $sumMinsAvail = 0;
        $sumMinsProd = 0;
        $totalData = 0;

        $sewingLine = "";
        $merge = false;
    @endphp
    @foreach ($dataDetailProduksiDay as $day)
        <tr>
            @if ($sewingLine != $day->sewing_line)
                @php
                    $sewingLine = $day->sewing_line;
                    $merge = true;
                @endphp
                <td rowspan="{{ $dataDetailProduksiDay->where('sewing_line', $day->sewing_line)->count() }}" style="vertical-align: middle; text-align: center;">{{ str_replace('line_', '', $day->sewing_line) }}</td>
            @endif
            <td>{{ $day->nama_buyer }}</td>
            <td>{{ $day->no_ws }}</td>
            <td>{{ $day->no_style }}</td>
            <td>{{ $day->chief_name }}</td>
            <td>{{ $day->leader_name }}</td>
            <td>{{ $day->admin_name }}</td>
            <td data-format="0" style="text-align:right;">{{ $day->man_power }}</td>
            <td data-format="0.00" style="text-align:right;">{{ $day->smv }}</td>
            <td data-format="0" style="text-align:right;">{{ (floatval($day->jam_aktual) > 0 ? floor($day->target/$day->jam_aktual) : 0) }}</td>
            <td data-format="0" style="text-align:right;">{{ $day->target }}</td>
            @foreach ($jamProduksi as $jam)
                <td style="text-align:right;">
                    @php
                        $output = 0;
                    @endphp
                    @foreach ($hourlyDataDay as $hour)
                        @if (($hour->hour > date('H:i', strtotime($jam.' -1 hour')) && $hour->hour <= $jam) && ($hour->no_ws == $day->no_ws && $hour->sewing_line == $day->sewing_line))
                            @php
                                $output += $hour->hour_output;
                            @endphp
                        @endif
                    @endforeach
                    {{ $output }}
                </td>
            @endforeach
            <td style="text-align:right;">{{ $day->output }}</td>
            <td style="text-align:right;">{{ round($day->efficiency, 2) }} %</td>
        </tr>
        @php
            $sumTargetJam += floatval($day->jam_aktual) > 0 ? (floatval($day->target)/floatval($day->jam_aktual)) : 0;
            $sumTargetToday += $day->target;
            $sumManPower += $day->man_power;
            $sumTotal += $day->output;
            $sumEfficiency += $day->efficiency;
            $sumMinsAvail += $day->mins_avail;
            $sumMinsProd += $day->mins_prod;
            $totalData++;
        @endphp
    @endforeach
    <tr>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td data-format="0" style="background-color: #cacaca;text-align: right;">{{ $totalDataDetailProduksiDay->total_mp }}</td>
        <td></td>
        <td data-format="0" style="background-color: #cacaca;text-align: right;">{{ $sumTargetToday }}</td>
        <td data-format="0" style="background-color: #cacaca;text-align: right;">{{ $sumTargetJam }}</td>
        @foreach ($jamProduksi as $jam)
            <td data-format="0" style="background-color: #cacaca;text-align: right;">
                @php
                    $outputSum = 0;
                @endphp
                @foreach ($hourlyDataDaySum as $hourSum)
                    @if (($hourSum->hour > date('H:i', strtotime($jam.' -1 hour'))) && $hourSum->hour <= $jam)
                        @php
                            $outputSum += $hourSum->sum_hour_output;
                        @endphp
                    @endif
                @endforeach
                {{ $outputSum }}
            </td>
        @endforeach
        <td data-format="0" style="background-color: #cacaca;text-align: right;">{{ $sumTotal }}</td>
        <td data-format="0.00%" style="background-color: #cacaca;text-align: right;">{{ floatval($sumMinsAvail) > 0 ? percentage((floatval($sumMinsProd) / floatval($sumMinsAvail) * 100)) : 0 }} %</td>
    </tr>
</table>

</html>
