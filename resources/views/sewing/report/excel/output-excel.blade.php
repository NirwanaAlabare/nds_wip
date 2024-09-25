<!DOCTYPE html>
<html lang="en">
<table>
    <tr>
        <td>Majalaya</td>
        <td>,</td>
        <td data-format="yyyy-mm-dd">{{ $tanggal }}</td>
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
        <td></td>
        <td></td>
    </tr>
    <tr>
        <th rowspan="2"></th>
        <th rowspan="2">WS Number</th>
        <th rowspan="2">Nama Buyer</th>
        <th rowspan="2">Style</th>
        <th colspan="2">CM Price</th>
        <th rowspan="2">SMV</th>
        <th rowspan="2">Tanggal Delivery</th>
        <th rowspan="2">QTY Order</th>
        <th rowspan="2">Total Output</th>
        <th rowspan="2">Total Balance</th>
        <td colspan="{{ $allLine->count() }}">Line</td>
        <th rowspan="2">Today Output</th>
        <th rowspan="2">Total Output</th>
        <th rowspan="2">Balance Order</th>
        <th rowspan="2">Total Earning (Rp) TODAY</th>
        <th rowspan="2">Total Earning ($) TODAY</th>
        <th rowspan="2">Total Earning (Rp) CUMULATIVE</th>
        <th rowspan="2">Total Earning ($) CUMULATIVE</th>
    </tr>
    <tr>
        <th>Rp</th>
        <th>$</th>
        @foreach ($allLine as $line)
            <th>{{ str_replace('line_', '', $line->username) }}</th>
        @endforeach
    </tr>
    @php
        $summaryOutput = 0;
        $summaryCumulativeOutput = 0;
        $summaryCumulativeBalance = 0;
        $summaryEarningRupiah = 0;
        $summaryEarningDollar = 0;
        $summaryCumulativeRupiah = 0;
        $summaryCumulativeDollar = 0;
    @endphp
    @foreach ($dataDetailProduksiDay as $day)
        <tr>
            <td style="text-align: center;">{{ $loop->iteration }}</td>
            <td>{{ $day->no_ws }}</td>
            <td>{{ $day->nama_buyer }}</td>
            <td>{{ $day->no_style }}</td>
            <td data-format="0.00" style="text-align: right;">{{ $day->order_cfm_price_rupiah }}</td>
            <td data-format="0.00" style="text-align: right;">{{ $day->order_cfm_price_dollar }}</td>
            <td data-format="0.00" style="text-align: right;">{{ $day->smv }}</td>
            <td>{{ $day->tanggal_delivery }}</td>
            <td data-format="0" style="text-align: right;">{{ $day->order_qty_cutting }}</td>
            <td data-format="0" style="text-align: right;">{{ $day->before_output }}</td>
            <td data-format="0" style="text-align: right;">{{ $day->before_balance }}</td>
            @foreach ($allLine as $line)
                <td data-format="0" style="text-align: right;">
                    @foreach ($dataLineProduksiDay as $lineProduksiDay)
                        @if (trim($lineProduksiDay->data_produksi_id) == trim($day->data_produksi_id) && trim($lineProduksiDay->sewing_line) == trim($line->username))
                            {{ $lineProduksiDay->output }}
                            @php
                                $line->total_output += $lineProduksiDay->output;
                                $line->total_cumulative_output = $lineProduksiDay->cumulative_output;
                            @endphp
                        @endif
                    @endforeach
                </td>
            @endforeach
            <td data-format="0" style="text-align: right;">{{ $day->output }}</td>
            <td data-format="0" style="text-align: right;">{{ $day->cumulative_output }}</td>
            <td data-format="0" style="text-align: right;">{{ $day->cumulative_balance }}</td>
            <td data-format="0.00" style="text-align: right;">{{ $day->kode_mata_uang == 'IDR' ? $day->earning : '0' }}</td>
            <td data-format="0.00" style="text-align: right;">{{ $day->kode_mata_uang != 'IDR' ?  $day->earning : '0' }}</td>
            <td data-format="0.00" style="text-align: right;">{{ $day->kode_mata_uang == 'IDR' ? $day->cumulative_earning : '0' }}</td>
            <td data-format="0.00" style="text-align: right;">{{ $day->kode_mata_uang != 'IDR' ? $day->cumulative_earning : '0' }}</td>
        </tr>
        @php
            $summaryOutput += $day->output;
            $summaryCumulativeOutput += $day->cumulative_output;
            $summaryCumulativeBalance += $day->cumulative_balance;
            $summaryEarningRupiah += ($day->kode_mata_uang == 'IDR' ? $day->earning : 0);
            $summaryEarningDollar += ($day->kode_mata_uang != 'IDR' ? $day->earning : 0);
            $summaryCumulativeRupiah += ($day->kode_mata_uang == 'IDR' ? $day->cumulative_earning : 0);
            $summaryCumulativeDollar += ($day->kode_mata_uang != 'IDR' ? $day->cumulative_earning : 0);
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
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        @foreach ($allLine as $line)
            <td></td>
        @endforeach
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
    </tr>
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
        <td></td>
        @foreach ($allLine as $line)
            <td></td>
        @endforeach
        <td data-format="0" style="background-color: #ffee00;text-align: right;">{{ $summaryOutput }}</td>
        <td data-format="0" style="background-color: #ffee00;text-align: right;">{{ $summaryCumulativeOutput }}</td>
        <td data-format="0" style="background-color: #ffee00;text-align: right;">{{ $summaryCumulativeBalance }}</td>
        <td data-format="0.00" style="background-color: #ffee00;text-align: right;">{{ $summaryEarningRupiah }}</td>
        <td data-format="0.00" style="background-color: #ffee00;text-align: right;">{{ $summaryEarningDollar }}</td>
        <td data-format="0.00" style="background-color: #ffee00;text-align: right;">{{ $summaryCumulativeRupiah }}</td>
        <td data-format="0.00" style="background-color: #ffee00;text-align: right;">{{ $summaryCumulativeDollar }}</td>
    </tr>
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
        <td>Total Output Today</td>
        @foreach ($allLine as $line)
            <td style="text-align: right;">{{ $line->total_output }}</td>
        @endforeach
        <td data-format="0" style="background-color: #b6e68f;text-align: right;">{{ $summaryOutput }}</td>
        <td></td>
        <td></td>
        <td style="font-weight: bold;">TOTAL</td>
        <td data-format="0.00" style="background-color: #b6e68f; font-weight: bold;text-align: right;">{{ $kurs != null ? "".(floatval($summaryEarningRupiah) + (floatval($summaryEarningDollar) * $kurs->kurs_tengah)) : "Rp. ".floatval($summaryEarningRupiah)  ." + ". floatval($summaryEarningDollar)  }}</td>
        <td style="font-weight: bold;">TOTAL</td>
        <td data-format="0.00" style="background-color: #b6e68f; font-weight: bold;text-align: right;">{{ $kurs != null ? "".(floatval($summaryCumulativeRupiah) + (floatval($summaryCumulativeDollar) * $kurs->kurs_tengah)) : "Rp. ".floatval($summaryCumulativeRupiah)  ." + ". floatval($summaryCumulativeDollar)  }}</td>
    </tr>
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
        <td>Total Cumulative Output Today</td>
        @foreach ($allLine as $line)
            <td data-format="0" style="text-align: right;">{{ $line->total_cumulative_output }}</td>
        @endforeach
        <td data-format="0" style="background-color: #b6e68f;text-align: right;">{{ $summaryCumulativeOutput }}</td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
    </tr>
</table>

</html>
