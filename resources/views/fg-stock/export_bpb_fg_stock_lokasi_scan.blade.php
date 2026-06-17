<!DOCTYPE html>
<html lang="en">

<table class="table">
    <tr>
        <td colspan='10'>Laporan Penerimaan Lokasi Scan</td>
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
            <th style="background-color: yellow;border:1px solid black;font-weight:bold">No. Trans</th>
            <th style="background-color: yellow;border:1px solid black;font-weight:bold">Tgl. Trans</th>
            <th style="background-color: yellow;border:1px solid black;font-weight:bold">Qr Code</th>
            <th style="background-color: yellow;border:1px solid black;font-weight:bold">No Karton</th>
            <th style="background-color: yellow;border:1px solid black;font-weight:bold">Buyer</th>
            <th style="background-color: yellow;border:1px solid black;font-weight:bold">Brand</th>
            <th style="background-color: yellow;border:1px solid black;font-weight:bold">Style</th>
            <th style="background-color: yellow;border:1px solid black;font-weight:bold">Grade</th>
            <th style="background-color: yellow;border:1px solid black;font-weight:bold">WS</th>
            <th style="background-color: yellow;border:1px solid black;font-weight:bold">Color</th>
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
                <td>{{ $item->qr_code }}</td>
                <td>{{ $item->no_carton }}</td>
                <td>{{ $item->buyer }}</td>
                <td>{{ $item->brand }}</td>
                <td>{{ $item->styleno }}</td>
                <td>{{ $item->grade }}</td>
                <td>{{ $item->ws }}</td>
                <td>{{ $item->color }}</td>
            </tr>
        @endforeach
    </tbody>

</table>

</html>
