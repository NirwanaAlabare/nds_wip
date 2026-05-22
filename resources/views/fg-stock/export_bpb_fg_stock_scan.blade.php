<!DOCTYPE html>
<html lang="en">

<table class="table">
    <tr>
        <td colspan='16'>Laporan Penerimaan Scan</td>
    </tr>
    <tr>
        <td colspan='16'>{{ date('d-M-Y', strtotime($from)) }} - {{ date('d-M-Y', strtotime($to)) }}
        </td>
    </tr>
    <tr>
        <td></td>
    </tr>
    <thead>
        <tr>
            <th style="background-color: yellow;border:1px solid black;font-weight:bold">No. Trans</th>
            <th style="background-color: yellow;border:1px solid black;font-weight:bold">Tgl. Trans</th>
            <th style="background-color: yellow;border:1px solid black;font-weight:bold">Lokasi</th>
            <th style="background-color: yellow;border:1px solid black;font-weight:bold">Buyer</th>
            <th style="background-color: yellow;border:1px solid black;font-weight:bold">Brand</th>
            <th style="background-color: yellow;border:1px solid black;font-weight:bold">Style</th>
            <th style="background-color: yellow;border:1px solid black;font-weight:bold">WS</th>
            <th style="background-color: yellow;border:1px solid black;font-weight:bold">Color</th>
            <th style="background-color: yellow;border:1px solid black;font-weight:bold">Size</th>
            <th style="background-color: yellow;border:1px solid black;font-weight:bold">Total Karton</th>
            <th style="background-color: yellow;border:1px solid black;font-weight:bold">Total Qty</th>
            <th style="background-color: yellow;border:1px solid black;font-weight:bold">Sumber</th>
        </tr>
    </thead>
    <tbody>
        @php
            $no = 1;
        @endphp
        @foreach ($data as $item)
            <tr>
                <td>{{ $item->no_trans }}</td>
                <td>{{ $item->tgl_terima_fix }}</td>
                <td>{{ $item->lokasi }}</td>
                <td>{{ $item->buyer }}</td>
                <td>{{ $item->brand }}</td>
                <td>{{ $item->styleno }}</td>
                <td>{{ $item->ws }}</td>
                <td>{{ $item->color }}</td>
                <td>{{ $item->size }}</td>
                <td>{{ $item->total_carton }}</td>
                <td>{{ $item->total_qty }}</td>
                <td>{{ $item->sumber_pemasukan }}</td>
            </tr>
        @endforeach
    </tbody>

</table>

</html>
