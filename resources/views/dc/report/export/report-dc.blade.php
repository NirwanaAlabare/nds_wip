<!DOCTYPE html>
<html lang="en">

<table border="1">
    <tr>
        <th colspan="16" style="text-align:left; font-size:16px; font-weight:bold;">
            REPORT DC
        </th>
    </tr>
    <tr>
        <th colspan="16" style="text-align:left;">
            Periode : {{ $from }} s/d {{ $to }}
        </th>
    </tr>
    <tr>
        <th colspan="16"></th>
    </tr>
    <tr>
        {{-- <th>No WsColorSize</th> --}}
        {{-- <th>No WsColorPart</th> --}}
        <th>No. WS</th>
        <th>Buyer</th>
        <th>Style</th>
        <th>Color</th>
        <th>Size</th>
        <th>Part</th>
        <th>Saldo Awal</th>
        <th>Masuk</th>
        <th>Kirim Sec Dalam</th>
        <th>Terima Repaired Sec Dalam</th>
        <th>Terima Good Sec Dalam</th>
        <th>Kirim Sec Luar</th>
        <th>Terima Repaired Sec Luar</th>
        <th>Terima Good Sec Luar</th>
        <th>Loading</th>
        <th>Saldo Akhir</th>
    </tr>
    @php
        $totalSaldoAwal = 0;
        $totalMasuk = 0;
        $totalKirimSecDalam = 0;
        $totalTerimaRepairedSecDalam = 0;
        $totalTerimaGoodSecDalam = 0;
        $totalKirimSecLuar = 0;
        $totalTerimaRepairedSecLuar = 0;
        $totalTerimaGoodSecLuar = 0;
        $totalLoading = 0;
        $totalSaldoAkhir = 0;
    @endphp

    @foreach ($dataReport as $data)

        @php
            $totalSaldoAwal += $data->saldo_awal ?? 0;
            $totalMasuk += $data->qty_in ?? 0;
            $totalKirimSecDalam += $data->kirim_secondary_dalam ?? 0;
            $totalTerimaRepairedSecDalam += $data->terima_repaired_secondary_dalam ?? 0;
            $totalTerimaGoodSecDalam += $data->terima_good_secondary_dalam ?? 0;
            $totalKirimSecLuar += $data->kirim_secondary_luar ?? 0;
            $totalTerimaRepairedSecLuar += $data->terima_repaired_secondary_luar ?? 0;
            $totalTerimaGoodSecLuar += $data->terima_good_secondary_luar ?? 0;
            $totalLoading += $data->loading ?? 0;
            $totalSaldoAkhir += $data->saldo_akhir ?? 0;
        @endphp

        <tr>
            {{-- <td> {{ $data->ws_color_size }}</td> --}}
            {{-- <td> {{ $data->ws_color_part }}</td> --}}
            <td>{{ $data->act_costing_ws }}</td>
            <td>{{ $data->buyer }}</td>
            <td>{{ $data->style }}</td>
            <td>{{ $data->color }}</td>
            <td>{{ $data->size }}</td>
            <td>{{ $data->nama_part }}</td>
            <td></td>
            <td>{{ $data->qty_in }}</td>
            <td>{{ $data->kirim_secondary_dalam }}</td>
            <td>{{ $data->terima_repaired_secondary_dalam }}</td>
            <td>{{ $data->terima_good_secondary_dalam }}</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
    @endforeach
    <tr style="font-weight:bold; background:#eee;">
        <td colspan="6" style="text-align:right; border:1px solid #000;">TOTAL</td>
        <td style="border:1px solid #000;">{{ $totalSaldoAwal }}</td>
        <td style="border:1px solid #000;">{{ $totalMasuk }}</td>
        <td style="border:1px solid #000;">{{ $totalKirimSecDalam }}</td>
        <td style="border:1px solid #000;">{{ $totalTerimaRepairedSecDalam }}</td>
        <td style="border:1px solid #000;">{{ $totalTerimaGoodSecDalam }}</td>
        <td style="border:1px solid #000;">{{ $totalKirimSecLuar }}</td>
        <td style="border:1px solid #000;">{{ $totalTerimaRepairedSecLuar }}</td>
        <td style="border:1px solid #000;">{{ $totalTerimaGoodSecLuar }}</td>
        <td style="border:1px solid #000;">{{ $totalLoading }}</td>
        <td style="border:1px solid #000;">{{ $totalSaldoAkhir }}</td>
    </tr>

</table>

</html>
