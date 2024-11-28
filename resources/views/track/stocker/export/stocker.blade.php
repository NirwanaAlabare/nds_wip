<!DOCTYPE html>
<html lang="en">

<table>
    <tr>
        <th>Worksheet Stocker {{ $month."/".$year }}</th>
    </tr>
    <tr>
        <th>Tanggal Kirim</th>
        <th>WS</th>
        <th>Style</th>
        <th>Color</th>
        <th>Stocker Qty</th>
    </tr>
    @php
        $totalStock = 0;
    @endphp
    @foreach ($stocker as $stock)
        @php
            $totalStock += $stock->qty;
        @endphp
        <tr>
            <td>{{ $stock->tgl_kirim }}</td>
            <td>{{ $stock->act_costing_ws }}</td>
            <td>{{ $stock->styleno }}</td>
            <td>{{ $stock->color }}</td>
            <td>{{ $stock->qty }}</td>
        </tr>
    @endforeach
    <tr>
        <td>Total</td>
        <td>{{ $totalStock }}</td>
    </tr>
</table>

</html>
