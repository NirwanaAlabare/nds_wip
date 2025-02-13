<!DOCTYPE html>
<html lang="en">
    <table>
        <tr>
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
            <td>Kurs Tengah BI</td>
            <td data-format="0" style="text-align: right;">{{ $kurs != null ? "Rp. ".curr($kurs->kurs_tengah) : "Data kurs tidak ditemukan" }}</td>
            <td></td>
            <td></td>
        </tr>
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
            <td>Total Earning</td>
            <td data-format="0" style="text-align: right;">{{ $kurs != null ? "Rp. ".curr(floatval($totalDataDetailProduksiDay['total_earning_rupiah']) + (floatval($totalDataDetailProduksiDay['total_earning_dollar']) * $kurs->kurs_tengah)) : "Rp. ".curr(floatval($totalDataDetailProduksiDay['total_earning_rupiah']))." + $ ". curr(floatval($totalDataDetailProduksiDay['total_earning_dollar'])) }}</td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td>EFISIENSI TOTAL</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td style="text-align: right;">{{ "Rp " }}</td>
            <td style="text-align: right;">{{ "$ " }}</td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td>Jml OP</td>
            <td data-format="0" style="text-align: right;">{{ $totalDataDetailProduksiDay['total_mp'] }}</td>
            <td data-format="0.00" style="text-align: right;">{{ $totalDataDetailProduksiDay['total_mins_avail'] }}</td>
            <td data-format="0" style="text-align: right;">{{ $totalDataDetailProduksiDay['total_target'] }}</td>
            <td data-format="0" style="text-align: right;">{{ $totalDataDetailProduksiDay['total_output'] }}</td>
            <td></td>
            <td data-format="0.00" style="text-align: right;">{{ $totalDataDetailProduksiDay['total_earning_rupiah'] }}</td>
            <td data-format="0.00" style="text-align: right;">{{ $totalDataDetailProduksiDay['total_earning_dollar'] }}</td>
            <td></td>
            <td></td>
            <td data-format="0.00" style="text-align: right;">{{ $totalDataDetailProduksiDay['total_mins_prod'] }}</td>
            @php
                $efficiencyAll = ($totalDataDetailProduksiDay['total_mins_avail'] > 0 ? round(((floatval($totalDataDetailProduksiDay['total_mins_prod'])/floatval($totalDataDetailProduksiDay['total_mins_avail']))*100), 2) : 0);
            @endphp
            <td data-format="0.00%" style="text-align: right;">{{ percentage($efficiencyAll) }} %</td>
            {{-- @php
                $rftAll = ($totalDataDetailProduksiDay['total_output_rft'] > 0 ? round(((floatval($totalDataDetailProduksiDay['total_output_rft'])/floatval($totalDataDetailProduksiDay['total_output']))*100), 2) : 0);
            @endphp --}}
            {{-- <td data-format="0.00%" style="text-align: right;">{{ percentage($rftAll) }} %</td> --}}
            <td data-format="0.00%" style="text-align: right;"> - </td>
        </tr>
        <tr>
            <th>Line</th>
            <th>WS Number</th>
            <th>Buyer</th>
            <th>Style</th>
            <th>SMV</th>
            <th>MP</th>
            <th>Mins. Avail</th>
            <th>Target</th>
            <th>Output</th>
            <th>CM Price</th>
            <th>Mata Uang</th>
            <th>Earning Today</th>
            <th>Efficiency</th>
            <th>RFT</th>
            <th>Mins. Prod</th>
            <th>Efficiency Line</th>
            <th>RFT Line</th>
            <th style="background: #e9ff25;">Jam Aktual</th>
            <th>Tanggal Produksi</th>
            <th>Chief</th>
            <th>Mins. Avail Chief</th>
            <th>Mins. Prod Chief</th>
            <th>Efficiency Chief</th>
        </tr>
        @php
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
                    <td rowspan="{{ $dataDetailProduksiDay->where('sewing_line', $day->sewing_line)->where('tgl_produksi', $day->tgl_produksi)->count() }}" style="vertical-align: middle; text-align: center;">{{ str_replace('line_', '', $day->sewing_line) }}</td>
                @endif
                <td>{{ $day->no_ws }}</td>
                <td>{{ $day->nama_buyer }}</td>
                <td>{{ $day->no_style }}</td>
                <td data-format="0.00" style="text-align: right;">{{ round($day->smv, 2) }}</td>
                <td data-format="0" style="background: #e9ff25;text-align: right;">{{ $day->man_power }}</td>
                <td data-format='{{ is_decimal($day->mins_avail) ? "0.00" : "0" }}' style="background: #bccee7;text-align: right;">{{ round($day->mins_avail, 2) }}</td>
                <td data-format="0" style="text-align: right;">{{ $day->target }}</td>
                <td data-format="0" style="background: #bef5c0;text-align: right;">{{ $day->output }}</td>
                <td data-format="0.00" style="text-align: right;">{{ $day->order_cfm_price }}</td>
                <td style="text-align: right;">{{ $day->kode_mata_uang != "IDR" ? "$ " : "Rp "}}</td>
                <td data-format="0.00" style="text-align: right;">{{ $day->earning }}</td>
                <td data-format="0.00%" style="text-align: right;">{{ percentage($day->efficiency)." %" }}</td>
                @php
                    // $rftRate = $day->output > 0 ? round(((floatval($day->output_rft)/floatval($day->output))*100), 2): 0;
                @endphp
                <td data-format="0.00%" style="text-align: right;"> - </td>
                <td data-format="0.00" style="background: #bccee7;text-align: right;">{{ $day->mins_prod }}</td>
                @if ($merge)
                    @php
                        $minsAvail = $dataDetailProduksiDay->where('sewing_line', $day->sewing_line)->sum("mins_avail");
                        $minsProd = $dataDetailProduksiDay->where('sewing_line', $day->sewing_line)->sum("mins_prod");
                        $efficiencyLine = $minsAvail > 0 ? round(((floatval($minsProd)/floatval($minsAvail))*100), 2) : 0;
                        // $rftLine = $day->output_line > 0 ? round(((floatval($day->output_rft_line)/floatval($day->output_line))*100), 2) : 0;
                    @endphp
                    <td data-format="0.00%" rowspan="{{ $dataDetailProduksiDay->where('sewing_line', $day->sewing_line)->where('tgl_produksi', $day->tgl_produksi)->count() }}" style="text-align: right;vertical-align: middle;">{{ percentage($efficiencyLine) }} %</td>
                    <td data-format="0.00%" rowspan="{{ $dataDetailProduksiDay->where('sewing_line', $day->sewing_line)->where('tgl_produksi', $day->tgl_produksi)->count() }}" style="text-align: right;vertical-align: middle;"> - </td>
                @endif
                <td style="background: #e9ff1f;text-align: right;">{{ $day->jam_aktual }}</td>
                @if ($merge)
                    @php
                        $merge = false;
                    @endphp
                    <td style="text-align: right;vertical-align: middle;" rowspan="{{ $dataDetailProduksiDay->where('sewing_line', $day->sewing_line)->where('tgl_produksi', $day->tgl_produksi)->count() }}">{{ $day->tgl_produksi }}</td>
                @endif
                {{-- @foreach ($summaryChiefDay as $chief)
                    @if ($chief->id != null)
                        @php
                            $chiefEfficiency = $chief->total_mins_avail > 0 ? round(((floatval($chief->total_mins_prod)/floatval($chief->total_mins_avail))) * 100, 2) : 0;
                        @endphp
                        @if ($chief->id == $day->chief_enroll_id)
                            <td>{{ $chief->nama }}</td>
                            <td data-format="0.00" style="text-align: right;">{{ $chief->total_mins_avail }}</td>
                            <td data-format="0.00" style="text-align: right;">{{ $chief->total_mins_prod }}</td>
                            <td data-format="0.00%" style="text-align: right;">{{ $chiefEfficiency }} %</td>
                        @endif
                    @else
                        <td>-</td>
                        <td>-</td>
                        <td>-</td>
                        <td>-</td>
                    @endif
                @endforeach --}}
                <td>-</td>
                <td>-</td>
                <td>-</td>
                <td>-</td>
            </tr>
        @endforeach
    </table>
</html>
