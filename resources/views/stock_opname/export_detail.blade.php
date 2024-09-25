<!DOCTYPE html>
<html lang="en">

<table class="table">
    <tr>
        <td colspan='36' style="font-size: 16px;"><b>Detail Stock Opname</b></td>
    </tr>
    <tr>
        <td colspan='36' style="font-size: 12px;">Periode {{ date('d-M-Y', strtotime($from)) }} s/d {{ date('d-M-Y', strtotime($to)) }}
        </td>
    </tr>
    <thead>
        <tr>
            <th>No</th>
            <th>Tipe Item</th>
            <th>No Transaksi</th>
            <th>Tgl Transaksi</th>
            <th>No Barcode</th>
            <th>Lokasi Scan</th>
            <th>Lokasi Aktual</th>
            <th>ID JO</th>
            <th>ID Item</th>
            <th>Kode Item</th>
            <th>Deskripsi Item</th>
            <th>No Lot</th>
            <th>No Roll</th>
            <th>Qty Saldo</th>
            <th>Qty SO</th>
            <th>Unit</th>
            <th>Created by</th>
            <th>Created date</th>
        </tr>
    </thead>
    <tbody>
        @php
        $no = 1;
        @endphp
        @foreach ($data as $item)
        <tr>
            <td>{{ $no++ }}.</td>
            <td>{{ $item->tipe_item }}</td>
            <td>{{ $item->no_dokumen }}</td>
            <td>{{ $item->tgl_dokumen }}</td>
            <td>{{ $item->no_barcode }}</td>
            <td>{{ $item->lokasi_scan }}</td>
            <td>{{ $item->lokasi_aktual }}</td>
            <td>{{ $item->id_jo }}</td>
            <td>{{ $item->id_item }}</td>
            <td>{{ $item->goods_code }}</td>
            <td>{{ $item->itemdesc }}</td>
            <td>{{ $item->no_lot }}</td>
            <td>{{ $item->no_roll }}</td>
            <td>{{ $item->qty_so }}</td>
            <td>{{ $item->qty }}</td>
            <td>{{ $item->unit }}</td>
            <td>{{ $item->created_by }}</td>
            <td>{{ $item->created_at }}</td>
        </tr>
        @endforeach
    </tbody>

</table>

</html>
