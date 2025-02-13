<!DOCTYPE html>
<html lang="en">

<table class="table">
    <tr>
        <td colspan='17'>Laporan Mutasi Internal Barang Jadi Stok</td>
    </tr>
    <tr>
        <td colspan='17'>{{ date('d-M-Y', strtotime($from)) }} - {{ date('d-M-Y', strtotime($to)) }}
        </td>
    </tr>
    <tr>
        <td></td>
    </tr>
    <thead>
        <tr style ="width:100%">
            <th style="background-color: yellow;border:1px solid black;font-weight:bold">No</th>
            <th style="background-color: yellow;border:1px solid black;font-weight:bold;width:100%">Buyer</th>
            <th style="background-color: yellow;border:1px solid black;font-weight:bold">WS</th>
            <th style="background-color: yellow;border:1px solid black;font-weight:bold">Brand</th>
            <th style="background-color: yellow;border:1px solid black;font-weight:bold">Style</th>
            <th style="background-color: yellow;border:1px solid black;font-weight:bold">Color</th>
            <th style="background-color: yellow;border:1px solid black;font-weight:bold">Size</th>
            <th style="background-color: yellow;border:1px solid black;font-weight:bold">Grade</th>
            <th style="background-color: yellow;border:1px solid black;font-weight:bold">Qty</th>
            <th style="background-color: yellow;border:1px solid black;font-weight:bold">Lokasi Asal</th>
            <th style="background-color: yellow;border:1px solid black;font-weight:bold">No. Carton Asal</th>
            <th style="background-color: yellow;border:1px solid black;font-weight:bold">Lokasi Tujuan</th>
            <th style="background-color: yellow;border:1px solid black;font-weight:bold">No. Carton Tujuan</th>
            <th style="background-color: yellow;border:1px solid black;font-weight:bold">No. BPB</th>
            <th style="background-color: yellow;border:1px solid black;font-weight:bold">No. BPPB</th>
            <th style="background-color: yellow;border:1px solid black;font-weight:bold">User</th>
            <th style="background-color: yellow;border:1px solid black;font-weight:bold">Tgl. Input</th>
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
                <td>{{ $item->ws }}</td>
                <td>{{ $item->brand }}</td>
                <td>{{ $item->styleno }}</td>
                <td>{{ $item->color }}</td>
                <td>{{ $item->size }}</td>
                <td>{{ $item->grade }}</td>
                <td>{{ $item->qty_mut }}</td>
                <td>{{ $item->lokasi_asal }}</td>
                <td>{{ $item->no_carton_asal }}</td>
                <td>{{ $item->lokasi_tujuan }}</td>
                <td>{{ $item->no_carton_tujuan }}</td>
                <td>{{ $item->no_trans }}</td>
                <td>{{ $item->no_trans_out }}</td>
                <td>{{ $item->created_by }}</td>
                <td>{{ $item->created_at }}</td>
            </tr>
        @endforeach
    </tbody>

</table>

</html>
