<!DOCTYPE html>
<html lang="en">

<table class="table">
    <tr>
        <td colspan='10'>Laporan Master Karton</td>
    </tr>
    <tr>
        <td colspan='10'>{{ date('d-M-Y', strtotime($from)) }} - {{ date('d-M-Y', strtotime($to)) }}
        </td>
    </tr>
    <tr>
        <td></td>
    </tr>
    <thead>
        <tr>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">Tgl. Shipment</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">No Carton</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">PO</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">Buyer</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">Barcode</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">WS</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">Color</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">Size</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">Dest</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">Notes</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">Total</th>
        </tr>
    </thead>
    <tbody>
        @php
            $no = 1;
        @endphp
        @foreach ($data as $item)
            <tr>
                <td>{{ $item->tgl_shipment_fix }}</td>
                <td>{{ $item->no_carton }}</td>
                <td>{{ $item->po }}</td>
                <td>{{ $item->buyer }}</td>
                <td>{{ $item->barcode }}</td>
                <td>{{ $item->ws }}</td>
                <td>{{ $item->color }}</td>
                <td>{{ $item->size }}</td>
                <td>{{ $item->dest }}</td>
                <td>{{ $item->notes }}</td>
                <td>{{ $item->tot_isi }}</td>
            </tr>
        @endforeach
    </tbody>

</table>

</html>
