<!DOCTYPE html>
<html lang="en">

<table class="table">
    <tr>
        <td colspan='16'>Laporan Master SO PPIC</td>
    </tr>
    <tr>
        <td colspan='11'>{{ date('d-M-Y', strtotime($from)) }} - {{ date('d-M-Y', strtotime($to)) }}
        </td>
    </tr>
    <thead>
        <tr>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">No</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">Buyer</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">Tgl. Shipment</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">WS</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">Style</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">Barcode</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">Reff</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">No. PO</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">Dest</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">Color</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">Size</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">Qty PO</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">Qty Tr Garment</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">Qty Packing In</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">Qty Packing Out</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">User</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">Tgl. Upload</th>
        </tr>
    </thead>
    <tbody>
        @php
            $no = 1;
        @endphp
        @foreach ($data as $item)
            <tr>
                <td>{{ $no++ }}.</td>
                <td>{{ $item->buyer }}</td>
                <td>{{ $item->tgl_shipment_fix }}</td>
                <td>{{ $item->ws }}</td>
                <td>{{ $item->styleno }}</td>
                <td>{{ $item->barcode }}</td>
                <td>{{ $item->reff_no }}</td>
                <td>{{ $item->po }}</td>
                <td>{{ $item->dest }}</td>
                <td>{{ $item->color }}</td>
                <td>{{ $item->size }}</td>
                <td>{{ $item->qty_po }}</td>
                <td>{{ $item->qty_trf }}</td>
                <td>{{ $item->qty_packing_in }}</td>
                <td>{{ $item->qty_packing_out }}</td>
                <td>{{ $item->created_by }}</td>
                <td>{{ $item->created_at }}</td>
            </tr>
        @endforeach
    </tbody>

</table>

</html>
