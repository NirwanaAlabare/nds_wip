<!DOCTYPE html>
<html lang="en">

<table>
    <tr>
        <th colspan="13">Detail History</th>
    </tr>
    <tr></tr>
    <tr>
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
    </tr>
    @foreach ($data as $d)
        <tr>
            <td style="border: 1px solid #000;">{{ $d->barcode }}</td>
            <td style="border: 1px solid #000;">{{ $d->no_koli }} </td>
            <td style="border: 1px solid #000;">{{ $d->buyer }} </td>
            <td style="border: 1px solid #000;">{{ $d->no_ws }} </td>
            <td style="border: 1px solid #000;">{{ $d->style }} </td>
            <td style="border: 1px solid #000;">{{ $d->product_item }} </td>
            <td style="border: 1px solid #000;">{{ $d->warna }} </td>
            <td style="border: 1px solid #000;">{{ $d->size }} </td>
            <td style="border: 1px solid #000;">{{ $d->grade }} </td>
            <td style="border: 1px solid #000;">{{ $d->qty_saat_ini }} </td>
            <td style="border: 1px solid #000;">{{ $d->satuan }} </td>
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
    </tr>
    @foreach ($dataDetail as $detail)
        <tr>
            <td style="border: 1px solid #000;">{{ $detail->jenis_tipe }}</td>
            <td style="border: 1px solid #000;">{{ $detail->no_bpb }}</td>
            <td style="border: 1px solid #000;">{{ $detail->tgl_bpb }}</td>
            <td style="border: 1px solid #000;">{{ $detail->barcode }}</td>
            <td style="border: 1px solid #000;">{{ $detail->no_koli }} </td>
            <td style="border: 1px solid #000;">{{ $detail->buyer }} </td>
            <td style="border: 1px solid #000;">{{ $detail->no_ws }} </td>
            <td style="border: 1px solid #000;">{{ $detail->style }} </td>
            <td style="border: 1px solid #000;">{{ $detail->product_item }} </td>
            <td style="border: 1px solid #000;">{{ $detail->warna }} </td>
            <td style="border: 1px solid #000;">{{ $detail->size }} </td>
            <td style="border: 1px solid #000;">{{ $detail->grade }} </td>
            <td style="border: 1px solid #000;">{{ $detail->qty }} </td>
            <td style="border: 1px solid #000;">{{ $detail->satuan }} </td>
            <td style="border: 1px solid #000;">{{ $detail->keterangan }} </td>
            <td style="border: 1px solid #000;">{{ $detail->lokasi }} </td>
        </tr>
    @endforeach
</table>

</html>