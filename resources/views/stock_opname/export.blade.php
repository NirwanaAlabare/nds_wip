<!DOCTYPE html>
<html lang="en">

<table class="table">
    <tr>
        <td colspan='36' style="font-size: 16px;"><b>Laporan Transaksi</b></td>
    </tr>
    <tr>
        <td colspan='36' style="font-size: 12px;">Periode {{ date('d-M-Y', strtotime($from)) }} s/d {{ date('d-M-Y', strtotime($to)) }}
        </td>
    </tr>
    <thead>
        <tr>
            <th>No</th>
            <th>No Transaksi</th>
            <th>Item Tipe</th>
            <th>Tgl Saldo</th>
<!--             <th>Lokasi</th>
            <th>ID JO</th>
            <th>ID Item</th>
            <th>Kode Item</th>
            <th>Deskripsi Item</th> -->
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
            <!-- <td>{{ $item->kode_lok }}</td>
            <td>{{ $item->id_jo }}</td>
            <td>{{ $item->id_item }}</td>
            <td>{{ $item->goods_code }}</td>
            <td>{{ $item->itemdesc }}</td> -->
            <td>{{ $item->qty_show }}</td>
            <td>{{ $item->qty_so_show }}</td>
            <td>{{ $item->qty_sisa_show }}</td>
            <td>{{ $item->status }}</td>
        </tr>
        @endforeach
    </tbody>

</table>

</html>
