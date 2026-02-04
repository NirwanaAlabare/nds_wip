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

    @foreach ($dataReport as $data)
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
</table>

</html>
