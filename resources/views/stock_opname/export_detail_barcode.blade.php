<!DOCTYPE html>
<html lang="en">

<table class="table">
    <tr>
        <td colspan='26' style="font-size: 16px;"><b>Laporan Detail Barcode</b></td>
    </tr>
    <tr></tr>
    <thead>
        <tr>
            <th>No</th>
            <th>No Transaksi</th>
            <th>Item Tipe</th>
            <th>Tgl Saldo</th>
            <th>No Barcode</th>
            <th>ID JO</th>
            <th>No WS</th>
            <th>Style</th>
            <th>ID Item</th>
            <th>Kode Item</th>
            <th>Deskripsi Item</th>
            <th>Lokasi</th>
            <th>No Lot</th>
            <th>No Roll</th>
            <th>Unit</th>
            <th>Qty Item</th>
            <th>Qty SO</th>
            <th>Sisa</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @php
        $no = 1;
        @endphp
        @foreach ($data as $item)
        <tr>
            <td>{{ $no++ }}.</td>
            <td>{{ $item->no_transaksi }}</td>
            <td>{{ $item->tipe_item }}</td>
            <td>{{ $item->tgl_saldo }}</td>
            <td>{{ $item->no_barcode }}</td>
            <td>{{ $item->id_jo }}</td>
            <td>{{ $item->no_ws }}</td>
            <td>{{ $item->styleno }}</td>
            <td>{{ $item->id_item }}</td>
            <td>{{ $item->goods_code }}</td>
            <td>{{ $item->itemdesc }}</td>
            <td>{{ $item->kode_lok }}</td>
            <td>{{ $item->no_lot }}</td>
            <td>{{ $item->no_roll }}</td>
            <td>{{ $item->unit }}</td>
            <td>{{ $item->qty }}</td>
            <td>{{ $item->qty_so }}</td>
            <td>{{ $item->qty_sisa }}</td>
            <td>{{ $item->status }}</td>
        </tr>
        @endforeach
    </tbody>

</table>

</html>
