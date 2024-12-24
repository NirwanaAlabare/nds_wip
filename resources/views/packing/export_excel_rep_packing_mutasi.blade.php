<!DOCTYPE html>
<html lang="en">
<table class="table">
    <tr>
        <td colspan='13'>Laporan Packing Line</td>
    </tr>
    {{-- <tr>
        <td colspan='13'>{{ date('d-M-Y', strtotime($from)) }} - {{ date('d-M-Y', strtotime($to)) }}
        </td>
    </tr> --}}
    <tr>
        <td></td>
    </tr>
    <thead>
        <tr>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">No. Carton</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">Barcode</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">PO</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">Buyer</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">WS</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">Color</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">Size</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">Dest</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">Qty PL</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">Qty Scan</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">Qty FG in</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">Qty FG Out</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">Lokasi</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">Balance</th>
        </tr>
    </thead>
    <tbody>
        @php
            $no = 1;
        @endphp
        @foreach ($data as $item)
            <tr>
                <td>{{ $item->no_carton }}</td>
                <td>{{ $item->barcode }}</td>
                <td>{{ $item->po }}</td>
                <td>{{ $item->buyer }}</td>
                <td>{{ $item->ws }}</td>
                <td>{{ $item->color }}</td>
                <td>{{ $item->size }}</td>
                <td>{{ $item->dest }}</td>
                <td>{{ $item->qty_pl }}</td>
                <td>{{ $item->tot_scan }}</td>
                <td>{{ $item->qty_fg_in }}</td>
                <td>{{ $item->qty_fg_out }}</td>
                <td>{{ $item->lokasi }}</td>
                <td>{{ $item->balance }}</td>
            </tr>
        @endforeach
    </tbody>

</table>

</html>
