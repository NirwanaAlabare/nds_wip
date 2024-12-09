<!DOCTYPE html>
<html lang="en">
<table class="table">
    <tr>
        <td colspan='13'>Laporan Packing Line</td>
    </tr>
    <tr>
        <td colspan='13'>{{ $buyer }}
        </td>
    </tr>
    <tr>
        <td></td>
    </tr>
    <thead>
        <tr>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">Buyer</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">WS</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">Color</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">Size</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">Qty</th>
        </tr>
    </thead>
    <tbody>
        @php
            $no = 1;
        @endphp
        @foreach ($data as $item)
            <tr>
                <td>{{ $item->buyer }}</td>
                <td>{{ $item->ws }}</td>
                <td>{{ $item->color }}</td>
                <td>{{ $item->size }}</td>
                <td>{{ $item->qty }}</td>
            </tr>
        @endforeach
    </tbody>

</table>

</html>
