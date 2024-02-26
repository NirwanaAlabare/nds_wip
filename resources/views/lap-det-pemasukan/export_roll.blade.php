<!DOCTYPE html>
<html lang="en">

<table class="table">
    <tr>
        <td colspan='25' style="font-size: 16px;"><b>Laporan Detail Pemasukan Barcode</b></td>
    </tr>
    <tr>
        <td colspan='25' style="font-size: 12px;">Periode {{ date('d-M-Y', strtotime($from)) }} s/d {{ date('d-M-Y', strtotime($to)) }}
        </td>
    </tr>
    <thead>
        <tr>
            <th>No</th>
            <th>No BPB</th>
            <th>Tgl BPB</th>
            <th>No Mutasi</th>
            <th>Supplier</th>
            <th>No SJ</th>
            <th>No PO</th>
            <th>Styleno</th>
            <th>Rak</th>
            <th>No Barcode</th>
            <th>No Roll</th>
            <th>No Lot</th>
            <th>Qty BPB</th>
            <th>Qty Mutasi</th>
            <th>Unit</th>
            <th>Id Item</th>
            <th>Id Jo</th>
            <th>No WS</th>
            <th>Kode Barang</th>
            <th>Nama Barang</th>
            <th>Warna</th>
            <th>Ukuran</th>
            <th>Keterangan</th>
            <th>Nama User</th>
            <th>Approve By</th>
        </tr>
    </thead>
    <tbody>
        @php
            $no = 1;
        @endphp
        @foreach ($data as $item)
            <tr>
                <td>{{ $no++ }}.</td>
                <td>{{ $item->no_dok }}</td>
                <td>{{ $item->tgl_dok }}</td>
                <td>{{ $item->no_mut }}</td>
                <td>{{ $item->supplier }}</td>
                <td>{{ $item->no_invoice }}</td>
                <td>{{ $item->no_po }}</td>
                <td>{{ $item->styleno }}</td>
                <td>{{ $item->rak }}</td>
                <td>{{ $item->barcode }}</td>
                <td>{{ $item->no_roll }}</td>
                <td>{{ $item->no_lot }}</td>
                <td>{{ $item->qty }}</td>
                <td>{{ $item->qty_mut }}</td>
                <td>{{ $item->satuan }}</td>
                <td>{{ $item->id_item }}</td>
                <td>{{ $item->id_jo }}</td>
                <td>{{ $item->no_ws }}</td>
                <td>{{ $item->goods_code }}</td>
                <td>{{ $item->itemdesc }}</td>
                <td>{{ $item->color }}</td>
                <td>{{ $item->size }}</td>
                <td>{{ $item->deskripsi }}</td>
                <td>{{ $item->username }}</td>
                <td>{{ $item->confirm_by }}</td>

            </tr>
        @endforeach
    </tbody>

</table>

</html>
