<!DOCTYPE html>
<html lang="en">

<table class="table">
    <tr>
        <td colspan='12'>Laporan Master SO PPIC</td>
    </tr>
    <tr>
        <td></td>
    </tr>
    <thead>
        <tr>
            <th style="background-color: yellow;border:1px solid black;font-weight:bold">No</th>
            <th style="background-color: yellow;border:1px solid black;font-weight:bold">Buyer</th>
            <th style="background-color: yellow;border:1px solid black;font-weight:bold">Tgl. Shipment</th>
            <th style="background-color: yellow;border:1px solid black;font-weight:bold">Barcode</th>
            <th style="background-color: yellow;border:1px solid black;font-weight:bold">Reff</th>
            <th style="background-color: yellow;border:1px solid black;font-weight:bold">No. PO</th>
            <th style="background-color: yellow;border:1px solid black;font-weight:bold">Dest</th>
            <th style="background-color: yellow;border:1px solid black;font-weight:bold">Color</th>
            <th style="background-color: yellow;border:1px solid black;font-weight:bold">Size</th>
            <th style="background-color: yellow;border:1px solid black;font-weight:bold">Qty PO</th>
            <th style="background-color: yellow;border:1px solid black;font-weight:bold">User</th>
            <th style="background-color: yellow;border:1px solid black;font-weight:bold">Tgl. Upload</th>
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
                <td>{{ $item->barcode }}</td>
                <td>{{ $item->reff_no }}</td>
                <td>{{ $item->po }}</td>
                <td>{{ $item->dest }}</td>
                <td>{{ $item->color }}</td>
                <td>{{ $item->size }}</td>
                <td>{{ $item->qty_po }}</td>
                <td>{{ $item->created_by }}</td>
                <td>{{ $item->created_at }}</td>
            </tr>
        @endforeach
    </tbody>

</table>

</html>
