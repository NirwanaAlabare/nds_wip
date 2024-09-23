<!DOCTYPE html>
<html lang="en">

<table>
    <tr>
        <th style="font-weight: 800;">Detail Pemakaian Roll - {{ date('d-m-Y') }}</th>
    </tr>
    <tr>
        <th>No. Request : {{ $no_req }}</th>
    </tr>
    <tr>
        <th>ID Item : {{ $id_item }}</th>
    </tr>
    <tr>
        <th>ID Roll</th>
        <th>ID Item</th>
        <th>Detail Item</th>
        <th>Lot</th>
        <th>Roll</th>
        <th>Qty</th>
        <th>Total Pemakaian Kain</th>
        <th>Total Short Roll</th>
        <th>Unit</th>
    </tr>
    @foreach ($rolls as $roll)
        <tr>
            <td>{{ $roll->id_roll }}</td>
            <td>{{ $roll->id_item }}</td>
            <td>{{ $roll->detail_item }}</td>
            <td>{{ $roll->lot }}</td>
            <td>{{ $roll->roll }}</td>
            <td>{{ $roll->qty }}</td>
            <td>{{ $roll->total_pemakaian_roll }}</td>
            <td>{{ $roll->total_short_roll }}</td>
            <td>{{ $roll->unit }}</td>
        </tr>
    @endforeach
</table>

</html>
