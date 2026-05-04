<!DOCTYPE html>
<html lang="en">

<table>
    <tr>
        <th colspan="12">Laporan Penerimaan Per Kategori (FABRIC)</th>
    </tr>
    <tr>
        <th colspan="12">{{ date('d-m-Y', strtotime($from)) }} / {{ date('d-m-Y', strtotime($to)) }}</th>
    </tr>
    <tr></tr>
    <tr>
        <th style="font-weight: 800;border: 1px solid #000;">No BPB</th>
        <th style="font-weight: 800;border: 1px solid #000;">Tgl BPB</th>
        <th style="font-weight: 800;border: 1px solid #000;">Barcode</th>
        <th style="font-weight: 800;border: 1px solid #000;">Lokasi</th>
        <th style="font-weight: 800;border: 1px solid #000;">Buyer</th>
        <th style="font-weight: 800;border: 1px solid #000;">Keterangan</th>
        <th style="font-weight: 800;border: 1px solid #000;">Jenis Item</th>
        <th style="font-weight: 800;border: 1px solid #000;">Warna</th>
        <th style="font-weight: 800;border: 1px solid #000;">Lot</th>
        <th style="font-weight: 800;border: 1px solid #000;">No Roll</th>
        <th style="font-weight: 800;border: 1px solid #000;">Qty</th>
        <th style="font-weight: 800;border: 1px solid #000;">Satuan</th>
    </tr>
    @foreach ($data as $d)
        <tr>
            <td style="border: 1px solid #000;">{{ $d->no_bpb }}</td>
            <td style="border: 1px solid #000;">{{ $d->tgl_bpb }} </td>
            <td style="border: 1px solid #000;">{{ $d->barcode }} </td>
            <td style="border: 1px solid #000;">{{ $d->lokasi }} </td>
            <td style="border: 1px solid #000;">{{ $d->buyer }} </td>
            <td style="border: 1px solid #000;">{{ $d->keterangan }} </td>
            <td style="border: 1px solid #000;">{{ $d->jenis_item }} </td>
            <td style="border: 1px solid #000;">{{ $d->warna }} </td>
            <td style="border: 1px solid #000;">{{ $d->lot }} </td>
            <td style="border: 1px solid #000;">{{ $d->no_roll }} </td>
            <td style="border: 1px solid #000;">{{ $d->qty }} </td>
            <td style="border: 1px solid #000;">{{ $d->satuan }} </td>
        </tr>
    @endforeach
</table>

</html>