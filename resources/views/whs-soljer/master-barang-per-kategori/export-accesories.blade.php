<!DOCTYPE html>
<html lang="en">

<table>
    <tr>
        <th colspan="13">Master Barang (ACCESORIES)</th>
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

</html>