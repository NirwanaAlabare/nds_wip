<!DOCTYPE html>
<html lang="en">

<table>
    <tr>
        <th colspan="8">CUTTING PLAN</th>
    </tr>
    <tr>
        <th colspan="4">{{ $from." / ".$to }}</th>
    </tr>
    <tr></tr>
    <tr>
        <th style="font-weight: 800;border: 1px solid #000;">Tanggal</th>
        <th style="font-weight: 800;border: 1px solid #000;">No. Form</th>
        <th style="font-weight: 800;border: 1px solid #000;">Meja</th>
        <th style="font-weight: 800;border: 1px solid #000;">Style</th>
        <th style="font-weight: 800;border: 1px solid #000;">Color</th>
        <th style="font-weight: 800;border: 1px solid #000;">Panel</th>
        <th style="font-weight: 800;border: 1px solid #000;">Size Ratio</th>
        <th style="font-weight: 800;border: 1px solid #000;">Qty Ply</th>
        <th style="font-weight: 800;border: 1px solid #000;">Qty Output</th>
        <th style="font-weight: 800;border: 1px solid #000;">Qty Actual</th>
        <th style="font-weight: 800;border: 1px solid #000;">No. Marker</th>
        <th style="font-weight: 800;border: 1px solid #000;">No. WS</th>
        <th style="font-weight: 800;border: 1px solid #000;">Status</th>
    </tr>
    @foreach ($data as $d)
        <tr>
            <td style="border: 1px solid #000;">{{ $d->tgl_form_cut }}</td>
            <td style="border: 1px solid #000;">{{ $d->no_form }} </td>
            <td style="border: 1px solid #000;">{{ $d->nama_meja }} </td>
            <td style="border: 1px solid #000;">{{ $d->style }} </td>
            <td style="border: 1px solid #000;">{{ $d->color }} </td>
            <td style="border: 1px solid #000;">{{ $d->panel }} </td>
            <td style="border: 1px solid #000;">{{ $d->marker_details }} </td>
            <td style="border: 1px solid #000;">{{ $d->total_lembar }} </td>
            <td style="border: 1px solid #000;">{{ $d->qty_output }} </td>
            <td style="border: 1px solid #000;">{{ $d->qty_act }} </td>
            <td style="border: 1px solid #000;">{{ $d->id_marker }} </td>
            <td style="border: 1px solid #000;">{{ $d->ws }} </td>
            <td style="border: 1px solid #000;">{{ $d->status }} </td>
        </tr>
    @endforeach
</table>

</html>
