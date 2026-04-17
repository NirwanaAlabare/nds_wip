<!DOCTYPE html>
<html lang="en">

<table>
    <tr>
        <th colspan="8">Penerimaan Fabric Cutting</th>
    </tr>
    <tr>
        <th colspan="4">{{ $from." / ".$to }}</th>
    </tr>
    <tr></tr>
    <tr>
        <th style="font-weight: 800;border: 1px solid #000;">Tanggal Terima</th>
        <th style="font-weight: 800;border: 1px solid #000;">Barcode</th>
        <th style="font-weight: 800;border: 1px solid #000;">Qty Out</th>
        <th style="font-weight: 800;border: 1px solid #000;">Unit</th>
        <th style="font-weight: 800;border: 1px solid #000;">Qty Konv</th>
        <th style="font-weight: 800;border: 1px solid #000;">Unit Konv</th>
        <th style="font-weight: 800;border: 1px solid #000;">No. Req</th>
        <th style="font-weight: 800;border: 1px solid #000;">No. BPPB</th>
        <th style="font-weight: 800;border: 1px solid #000;">Tgl. BPPB</th>
        <th style="font-weight: 800;border: 1px solid #000;">Tujuan</th>
        <th style="font-weight: 800;border: 1px solid #000;">No. WS</th>
        <th style="font-weight: 800;border: 1px solid #000;">No. WS Act</th>
        <th style="font-weight: 800;border: 1px solid #000;">ID Item</th>
        <th style="font-weight: 800;border: 1px solid #000;">Style</th>
        <th style="font-weight: 800;border: 1px solid #000;">Warna</th>
        <th style="font-weight: 800;border: 1px solid #000;">No. Lot</th>
        <th style="font-weight: 800;border: 1px solid #000;">No. Roll</th>
        <th style="font-weight: 800;border: 1px solid #000;">No. Roll Buyer</th>
        <th style="font-weight: 800;border: 1px solid #000;">Created By</th>
        <th style="font-weight: 800;border: 1px solid #000;">Created At</th>
    </tr>
    @foreach ($data as $d)
        <tr>
            <td style="border: 1px solid #000;">{{ $d->tanggal_terima }}</td>
            <td style="border: 1px solid #000;">{{ $d->barcode }} </td>
            <td style="border: 1px solid #000;">{{ $d->qty_out }} </td>
            <td style="border: 1px solid #000;">{{ $d->unit }} </td>
            <td style="border: 1px solid #000;">{{ $d->qty_konv }} </td>
            <td style="border: 1px solid #000;">{{ $d->unit_konv }} </td>
            <td style="border: 1px solid #000;">{{ $d->no_req }} </td>
            <td style="border: 1px solid #000;">{{ $d->no_bppb }} </td>
            <td style="border: 1px solid #000;">{{ $d->tanggal_bppb }} </td>
            <td style="border: 1px solid #000;">{{ $d->tujuan }} </td>
            <td style="border: 1px solid #000;">{{ $d->no_ws }} </td>
            <td style="border: 1px solid #000;">{{ $d->no_ws_act }} </td>
            <td style="border: 1px solid #000;">{{ $d->id_item }} </td>
            <td style="border: 1px solid #000;">{{ $d->style }} </td>
            <td style="border: 1px solid #000;">{{ $d->warna }} </td>
            <td style="border: 1px solid #000;">{{ $d->no_lot }} </td>
            <td style="border: 1px solid #000;">{{ $d->no_roll }} </td>
            <td style="border: 1px solid #000;">{{ $d->no_roll_buyer }} </td>
            <td style="border: 1px solid #000;">{{ $d->created_by_username }} </td>
            <td style="border: 1px solid #000;">{{ $d->created_at_format }} </td>
        </tr>
    @endforeach
</table>

</html>
