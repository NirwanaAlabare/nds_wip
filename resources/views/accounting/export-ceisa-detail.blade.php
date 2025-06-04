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
        <td>{{ number_format($item->qty, 2, '.', ',') }}</td>
        <td>{{ $item->unit }}</td>
        <td>{{ $item->curr }}</td>
        <td>{{ number_format($item->price, 2, '.', ',') }}</td>
        <td>{{ number_format($item->cif, 2, '.', ',') }}</td>
        <td>{{ number_format($item->rates, 2, '.', ',') }}</td>
        <td>{{ number_format($item->cif_rupiah, 2, '.', ',') }}</td>
        <td>{{ $item->created_by }}</td>
        <td>{{ $item->created_date }}</td>
    </tr>
    @endforeach

</tbody>

</table>

</html>
