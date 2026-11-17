<!DOCTYPE html>
<html lang="en">

<table class="table">
    <tr>
        <td colspan='9'>Stok Temporary Packing</td>
    </tr>
    <tr>
        <td colspan='9'>Per {{ date('d-M-Y H:i') }}</td>
    </tr>
    <tr>
        <td></td>
    </tr>
    <thead>
        <tr>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">No</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">Buyer</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">PO</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">WS</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">Style</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">Color</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">Size</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">Dest</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">Qty</th>
        </tr>
    </thead>
    <tbody>
        @php
            $no = 1;
            $total = 0;
        @endphp
        @foreach ($data as $item)
            @php
                $total += $item->stok;
            @endphp
            <tr>
                <td>{{ $no++ }}</td>
                <td>{{ $item->buyer }}</td>
                <td>{{ $item->po }}</td>
                <td>{{ $item->ws }}</td>
                <td>{{ $item->styleno }}</td>
                <td>{{ $item->color }}</td>
                <td>{{ $item->size }}</td>
                <td>{{ $item->dest }}</td>
                <td>{{ $item->stok }}</td>
            </tr>
        @endforeach
        <tr>
            <td colspan='8' style="font-weight:bold">Total</td>
            <td style="font-weight:bold">{{ $total }}</td>
        </tr>
    </tbody>

</table>

</html>
