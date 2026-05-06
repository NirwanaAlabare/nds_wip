<!DOCTYPE html>
<html lang="en">

<table>
    <tr>
        <th colspan="13">Detail History</th>
    </tr>
    <tr></tr>
    <tr>
        <th style="font-weight: 800;border: 1px solid #000;">Barcode</th>
        <th style="font-weight: 800;border: 1px solid #000;">No Box/Koli</th>
        <th style="font-weight: 800;border: 1px solid #000;">Buyer</th>
        <th style="font-weight: 800;border: 1px solid #000;">Worksheet</th>
        <th style="font-weight: 800;border: 1px solid #000;">Nama Barang</th>
        <th style="font-weight: 800;border: 1px solid #000;">Kode</th>
        <th style="font-weight: 800;border: 1px solid #000;">Warna</th>
        <th style="font-weight: 800;border: 1px solid #000;">Size</th>
        <th style="font-weight: 800;border: 1px solid #000;">Qty</th>
        <th style="font-weight: 800;border: 1px solid #000;">Satuan</th>
        <th style="font-weight: 800;border: 1px solid #000;">Qty KGM</th>
        <th style="font-weight: 800;border: 1px solid #000;">Keterangan</th>
        <th style="font-weight: 800;border: 1px solid #000;">Lokasi</th>
    </tr>
    @foreach ($data as $d)
        <tr>
            <td style="border: 1px solid #000;">{{ $d->barcode }}</td>
            <td style="border: 1px solid #000;">{{ $d->no_box }} </td>
            <td style="border: 1px solid #000;">{{ $d->buyer }} </td>
            <td style="border: 1px solid #000;">{{ $d->worksheet }} </td>
            <td style="border: 1px solid #000;">{{ $d->nama_barang }} </td>
            <td style="border: 1px solid #000;">{{ $d->kode }} </td>
            <td style="border: 1px solid #000;">{{ $d->warna }} </td>
            <td style="border: 1px solid #000;">{{ $d->size }} </td>
            <td style="border: 1px solid #000;">{{ $d->qty_saat_ini }} </td>
            <td style="border: 1px solid #000;">{{ $d->satuan }} </td>
            <td style="border: 1px solid #000;">{{ $d->qty_kgm_saat_ini }} </td>
            <td style="border: 1px solid #000;">{{ $d->keterangan }} </td>
            <td style="border: 1px solid #000;">{{ $d->lokasi }} </td>
        </tr>
    @endforeach
</table>

<table>
    <tr></tr>
    <tr>
        <th style="font-weight: 800;border: 1px solid #000;">Jenis Tipe</th>
        <th style="font-weight: 800;border: 1px solid #000;">No. BPB</th>
        <th style="font-weight: 800;border: 1px solid #000;">Tgl BPB</th>
        <th style="font-weight: 800;border: 1px solid #000;">Barcode</th>
        <th style="font-weight: 800;border: 1px solid #000;">No Box/Koli</th>
        <th style="font-weight: 800;border: 1px solid #000;">Buyer</th>
        <th style="font-weight: 800;border: 1px solid #000;">Worksheet</th>
        <th style="font-weight: 800;border: 1px solid #000;">Nama Barang</th>
        <th style="font-weight: 800;border: 1px solid #000;">Kode</th>
        <th style="font-weight: 800;border: 1px solid #000;">Warna</th>
        <th style="font-weight: 800;border: 1px solid #000;">Size</th>
        <th style="font-weight: 800;border: 1px solid #000;">Qty</th>
        <th style="font-weight: 800;border: 1px solid #000;">Satuan</th>
        <th style="font-weight: 800;border: 1px solid #000;">Qty KGM</th>
        <th style="font-weight: 800;border: 1px solid #000;">Keterangan</th>
        <th style="font-weight: 800;border: 1px solid #000;">Lokasi</th>
    </tr>
    @foreach ($dataDetail as $detail)
        <tr>
            <td style="border: 1px solid #000;">{{ $detail->jenis_tipe }}</td>
            <td style="border: 1px solid #000;">{{ $detail->no_bpb }}</td>
            <td style="border: 1px solid #000;">{{ $detail->tgl_bpb }}</td>
            <td style="border: 1px solid #000;">{{ $detail->barcode }}</td>
            <td style="border: 1px solid #000;">{{ $detail->no_box }} </td>
            <td style="border: 1px solid #000;">{{ $detail->buyer }} </td>
            <td style="border: 1px solid #000;">{{ $detail->worksheet }} </td>
            <td style="border: 1px solid #000;">{{ $detail->nama_barang }} </td>
            <td style="border: 1px solid #000;">{{ $detail->kode }} </td>
            <td style="border: 1px solid #000;">{{ $detail->warna }} </td>
            <td style="border: 1px solid #000;">{{ $detail->size }} </td>
            <td style="border: 1px solid #000;">{{ $detail->qty }} </td>
            <td style="border: 1px solid #000;">{{ $detail->satuan }} </td>
            <td style="border: 1px solid #000;">{{ $detail->qty_kgm }} </td>
            <td style="border: 1px solid #000;">{{ $detail->keterangan }} </td>
            <td style="border: 1px solid #000;">{{ $detail->lokasi }} </td>
        </tr>
    @endforeach
</table>

</html>