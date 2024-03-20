<!DOCTYPE html>
<html lang="en">

<table class="table">
    <tr>
        <td colspan='16'>Laporan Mutasi Barang Jadi Stok</td>
    </tr>
    <tr>
        <td colspan='16'>{{ date('d-M-Y', strtotime($from)) }} - {{ date('d-M-Y', strtotime($to)) }}
        </td>
    </tr>
    <tr>
        <td></td>
    </tr>
    <thead>
        <tr style ="width:100%">
            <th style="background-color: yellow;border:1px solid black;font-weight:bold">No</th>
            <th style="background-color: yellow;border:1px solid black;font-weight:bold">ID SO Det</th>
            <th style="background-color: yellow;border:1px solid black;font-weight:bold;width:100%">Buyer</th>
            <th style="background-color: yellow;border:1px solid black;font-weight:bold;width:100%">Product Group</th>
            <th style="background-color: yellow;border:1px solid black;font-weight:bold;width:100%">Product Item</th>
            <th style="background-color: yellow;border:1px solid black;font-weight:bold">WS</th>
            <th style="background-color: yellow;border:1px solid black;font-weight:bold">Brand</th>
            <th style="background-color: yellow;border:1px solid black;font-weight:bold">Style</th>
            <th style="background-color: yellow;border:1px solid black;font-weight:bold">Color</th>
            <th style="background-color: yellow;border:1px solid black;font-weight:bold">Size</th>
            <th style="background-color: yellow;border:1px solid black;font-weight:bold">Grade</th>
            <th style="background-color: yellow;border:1px solid black;font-weight:bold">No. Carton</th>
            <th style="background-color: yellow;border:1px solid black;font-weight:bold">Lokasi</th>
            <th style="background-color: yellow;border:1px solid black;font-weight:bold">Saldo Awal</th>
            <th style="background-color: yellow;border:1px solid black;font-weight:bold">Penerimaan</th>
            <th style="background-color: yellow;border:1px solid black;font-weight:bold">Pengeluaran</th>
            <th style="background-color: yellow;border:1px solid black;font-weight:bold">Saldo Akhir</th>
        </tr>
    </thead>
    <tbody>
        @php
            $no = 1;
        @endphp
        @foreach ($data as $item)
            <tr>
                <td>{{ $no++ }}.</td>
                <td>{{ $item->id_so_det }}</td>
                <td>{{ $item->buyer }}</td>
                <td>{{ $item->product_group }}</td>
                <td>{{ $item->product_item }}</td>
                <td>{{ $item->ws }}</td>
                <td>{{ $item->brand }}</td>
                <td>{{ $item->styleno }}</td>
                <td>{{ $item->color }}</td>
                <td>{{ $item->size }}</td>
                <td>{{ $item->grade }}</td>
                <td>{{ $item->no_carton }}</td>
                <td>{{ $item->lokasi }}</td>
                <td>{{ $item->qty_awal }}</td>
                <td>{{ $item->qty_in }}</td>
                <td>{{ $item->qty_out }}</td>
                <td>{{ $item->saldo_akhir }}</td>
            </tr>
        @endforeach
    </tbody>

</table>

</html>
