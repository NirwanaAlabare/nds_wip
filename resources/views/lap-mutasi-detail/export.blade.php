<!DOCTYPE html>
<html lang="en">

<table class="table">
    <tr>
        <td colspan='9' style="font-size: 16px;"><b>Laporan Mutasi Detail</b></td>
    </tr>
    <tr>
        <td colspan='9' style="font-size: 12px;">Periode {{ date('d-M-Y', strtotime($from)) }} s/d {{ date('d-M-Y', strtotime($to)) }}
        </td>
    </tr>
    <thead>
        <tr>
            <th>No</th>
            <th>Lokasi</th>
            <th>Id Jo</th>
            <th>WS</th>
            <th>Style</th>
            <th>Buyer</th>
            <th>Id Item</th>
            <th>Kode Barang</th>
            <th>Nama Barang</th>
            <th>satuan</th>
            <th>Saldo Awal</th>
            <th>Pemasukan</th>
            <th>Pengeluaran</th>
            <th>Saldo Akhir</th>
        </tr>
    </thead>
    <tbody>
        @php
            $no = 1;
        @endphp
        @foreach ($data as $item)
            <tr>
                <td>{{ $no++ }}.</td>
                <td>{{ $item->kode_lok }}</td>
                <td>{{ $item->id_jo }}</td>
                <td>{{ $item->no_ws }}</td>
                <td>{{ $item->styleno }}</td>
                <td>{{ $item->buyer }}</td>
                <td>{{ $item->id_item }}</td>
                <td>{{ $item->goods_code }}</td>
                <td>{{ $item->itemdesc }}</td>
                <td>{{ $item->satuan }}</td>
                <td>{{ $item->sal_awal }}</td>
                <td>{{ $item->qty_in }}</td>
                <td>{{ $item->qty_out }}</td>
                <td>{{ $item->sal_akhir }}</td>

            </tr>
        @endforeach
    </tbody>

</table>

</html>
