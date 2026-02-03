<!DOCTYPE html>
<html lang="en">

<table>
    <tr>
        <th style="font-weight: 800;">No WsColorSize</th>
        <th style="font-weight: 800;">No WsColorPart</th>
        <th style="font-weight: 800;">No. WS</th>
        <th style="font-weight: 800;">Buyer</th>
        <th style="font-weight: 800;">Style</th>
        <th style="font-weight: 800;">Color</th>
        <th style="font-weight: 800;">Size</th>
        <th style="font-weight: 800;">Part</th>
        <th style="font-weight: 800;">Saldo Awal</th>
        <th style="font-weight: 800;">Masuk</th>
        <th style="font-weight: 800;">Kirim Sec Dalam</th>
        <th style="font-weight: 800;">Terima Repaired Sec Dalam</th>
        <th style="font-weight: 800;">Terima Good Sec Dalam</th>
        <th style="font-weight: 800;">Kirim Sec Luar</th>
        <th style="font-weight: 800;">Terima Repaired Sec Luar</th>
        <th style="font-weight: 800;">Terima Good Sec Luar</th>
        <th style="font-weight: 800;">Loading</th>
        <th style="font-weight: 800;">Saldo Akhir</th>
    </tr>
    @foreach ($dataReport as $data )
        <tr>
            <td>{{ $data->color }} </td>
            <td>{{ $data->color }} </td>
            <td>{{ $data->color }} </td>
            <td>{{ $data->color }} </td>
            <td>{{ $data->color }} </td>
            <td>{{ $data->color }} </td>
            <td>{{ $data->color }} </td>
            <td>{{ $data->color }} </td>
            <td>{{ $data->color }} </td>
            <td>{{ $data->color }} </td>
            <td>{{ $data->color }} </td>
            <td>{{ $data->color }} </td>
            <td>{{ $data->color }} </td>
            <td>{{ $data->color }} </td>
            <td>{{ $data->color }} </td>
            <td>{{ $data->color }} </td>
            <td>{{ $data->color }} </td>
            <td>{{ $data->color }} </td>
           
        </tr>
    @endforeach
</table>

</html>
