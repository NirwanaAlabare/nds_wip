<!DOCTYPE html>
<html lang="en">

<table class="table">
    <tr>
        <td colspan='9' style="font-size: 16px;"><b>Laporan Mutasi Barcode</b></td>
    </tr>
    <tr>
        <td colspan='9' style="font-size: 12px;">Periode {{ date('d-M-Y', strtotime($from)) }} s/d {{ date('d-M-Y', strtotime($to)) }}
        </td>
    </tr>
    <thead>
        <tr>
            <th>No</th>
            <th>No Barcode</th>
            <th>No BPB</th>
            <th>Tgl BPB</th>
            <th>Supplier</th>
            <th>Lokasi</th>
            <th>Id JO</th>
            <th>No WS</th>
            <th>Style</th>
            <th>Id Item</th>
            <th>Nama Barang</th>
            <th>No Roll</th>
            <th>No Lot</th>
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
            <td>{{ $item->no_barcode }}</td>
            <td>{{ $item->no_dok }}</td>
            <td>{{ $item->tgl_dok }}</td>
            <td>{{ $item->supplier }}</td>
            <td>{{ $item->kode_lok }}</td>
            <td>{{ $item->id_jo }}</td>
            <td>{{ $item->kpno }}</td>
            <td>{{ $item->styleno }}</td>
            <td>{{ $item->id_item }}</td>
            <td>{{ $item->itemdesc }}</td>
            <td>{{ $item->no_roll }}</td>
            <td>{{ $item->no_lot }}</td>
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
