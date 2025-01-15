<!DOCTYPE html>
<html lang="en">
<table class="table">
    <tr>
        <td colspan='13'>Laporan Packing Terima</td>
    </tr>
    <tr>
        <td colspan='13'>{{ date('d-M-Y', strtotime($from)) }} - {{ date('d-M-Y', strtotime($to)) }}
        </td>
    </tr>
    <tr>
        <td></td>
    </tr>
    <thead>
        <tr>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">No</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">No. Trans</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">Tgl. Trans</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">No. Trans Garment</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">Line</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">Barcode</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">PO</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">WS</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">Color</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">Size</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">Dest</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">Qty</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">User</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">Tgl. Input</th>
        </tr>
    </thead>
    <tbody>
        @php
            $no = 1;
        @endphp
        @foreach ($data as $item)
            <tr>
                <td>{{ $no++ }}.</td>
                <td>{{ $item->no_trans }}</td>
                <td>{{ $item->tgl_penerimaan_fix }}</td>
                <td>{{ $item->no_trf_garment }}</td>
                <td>{{ $item->line }}</td>
                <td>{{ $item->barcode }}</td>
                <td>{{ $item->po }}</td>
                <td>{{ $item->ws }}</td>
                <td>{{ $item->color }}</td>
                <td>{{ $item->size }}</td>
                <td>{{ $item->dest }}</td>
                <td>{{ $item->qty }}</td>
                <td>{{ $item->created_by }}</td>
                <td>{{ $item->created_at }}</td>
            </tr>
        @endforeach
    </tbody>

</table>

</html>
