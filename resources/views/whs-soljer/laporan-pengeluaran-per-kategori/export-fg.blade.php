<!DOCTYPE html>
<html lang="en">

<table>
    <tr>
        <th colspan="16">Laporan Pengeluaran Per Kategori (FG)</th>
    </tr>
    <tr>
        <th colspan="16">{{ date('d-m-Y', strtotime($from)) }} / {{ date('d-m-Y', strtotime($to)) }}</th>
    </tr>
    <tr></tr>
    <tr>
        <th style="font-weight: 800;border: 1px solid #000;">No BPB</th>
        <th style="font-weight: 800;border: 1px solid #000;">Tgl BPB</th>
        <th style="font-weight: 800;border: 1px solid #000;">Barcode</th>
        <th style="font-weight: 800;border: 1px solid #000;">No Koli</th>
        <th style="font-weight: 800;border: 1px solid #000;">Buyer</th>
        <th style="font-weight: 800;border: 1px solid #000;">No WS</th>
        <th style="font-weight: 800;border: 1px solid #000;">Style</th>
        <th style="font-weight: 800;border: 1px solid #000;">Product Item</th>
        <th style="font-weight: 800;border: 1px solid #000;">Warna</th>
        <th style="font-weight: 800;border: 1px solid #000;">Size</th>
        <th style="font-weight: 800;border: 1px solid #000;">Grade</th>
        <th style="font-weight: 800;border: 1px solid #000;">Qty</th>
        <th style="font-weight: 800;border: 1px solid #000;">Satuan</th>
        <th style="font-weight: 800;border: 1px solid #000;">Keterangan</th>
        <th style="font-weight: 800;border: 1px solid #000;">Lokasi</th>
        <th style="font-weight: 800;border: 1px solid #000;">Qty Out</th>
    </tr>
    @foreach ($data as $d)
        <tr>
            <td style="border: 1px solid #000;">{{ $d->no_bpb }}</td>
            <td style="border: 1px solid #000;">{{ $d->tgl_bpb }} </td>
            <td style="border: 1px solid #000;">{{ $d->barcode }} </td>
            <td style="border: 1px solid #000;">{{ $d->no_koli }} </td>
            <td style="border: 1px solid #000;">{{ $d->buyer }} </td>
            <td style="border: 1px solid #000;">{{ $d->no_ws }} </td>
            <td style="border: 1px solid #000;">{{ $d->style }} </td>
            <td style="border: 1px solid #000;">{{ $d->product_item }} </td>
            <td style="border: 1px solid #000;">{{ $d->warna }} </td>
            <td style="border: 1px solid #000;">{{ $d->size }} </td>
            <td style="border: 1px solid #000;">{{ $d->grade }} </td>
            <td style="border: 1px solid #000;">{{ $d->qty }} </td>
            <td style="border: 1px solid #000;">{{ $d->satuan }} </td>
            <td style="border: 1px solid #000;">{{ $d->keterangan }} </td>
            <td style="border: 1px solid #000;">{{ $d->lokasi }} </td>
            <td style="border: 1px solid #000;">{{ $d->qty_out }} </td>
        </tr>
    @endforeach
</table>

</html>