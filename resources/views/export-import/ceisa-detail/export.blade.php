<!DOCTYPE html>
<html lang="en">

<table class="table">
    <tr>
        <td colspan='19' style="font-size: 16px;"><b>Laporan Data Ceisa Detail</b></td>
    </tr>
    <tr>
        <td colspan='19' style="font-size: 12px;">Periode {{ date('d-M-Y', strtotime($from)) }} s/d {{ date('d-M-Y', strtotime($to)) }}
        </td>
    </tr>
    <thead>
        <tr>
            <th>No</th>
            <th>No Upload</th>
            <th>Jenis Dokumen</th>
            <th>Supplier</th>
            <th>No Daftar</th>
            <th>Tgl Daftar</th>
            <th>No Aju</th>
            <th>Tgl Aju</th>
            <th>Kode Barang</th>
            <th>Nama Barang</th>
            <th>Qty</th>
            <th>Satuan</th>
            <th>Curr</th>
            <th>Price</th>
            <th>Total</th>
            <th>Rate</th>
            <th>Total IDR</th>
            <th>Uploaded By</th>
            <th>Uploaded Date</th>
        </tr>
    </thead>
    <tbody>
       @php
       $no = 1;
       @endphp
       @foreach ($data as $item)
       <tr>
        <td>{{ $no++ }}.</td>
        <td>{{ $item->no_dokumen }}</td>
        <td>{{ $item->kode_dokumen_format }}</td>
        <td>{{ $item->nama_entitas }}</td>
        <td>{{ $item->no_daftar }}</td>
        <td>{{ $item->tgl_daftar }}</td>
        <td>{{ $item->no_aju }}</td>
        <td>{{ $item->tgl_aju }}</td>
        <td>{{ $item->kode_barang }}</td>
        <td>{{ $item->uraian }}</td>

        <td>
            {{ is_numeric($item->qty) ? number_format((float) $item->qty, 2, '.', ',') : $item->qty }}
        </td>

        <td>{{ $item->unit }}</td>
        <td>{{ $item->curr }}</td>

        <td>
            {{ is_numeric($item->price) ? number_format((float) $item->price, 2, '.', ',') : $item->price }}
        </td>

        <td>
            {{ is_numeric($item->cif) ? number_format((float) $item->cif, 2, '.', ',') : $item->cif }}
        </td>

        <td>
            {{ is_numeric($item->rates) ? number_format((float) $item->rates, 2, '.', ',') : $item->rates }}
        </td>

        <td>
            {{ is_numeric($item->cif_rupiah) ? number_format((float) $item->cif_rupiah, 2, '.', ',') : $item->cif_rupiah }}
        </td>

        <td>{{ $item->created_by }}</td>
        <td>{{ $item->created_date }}</td>
    </tr>

    @endforeach

</tbody>

</table>

</html>
