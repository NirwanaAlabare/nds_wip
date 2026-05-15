<!DOCTYPE html>
<html lang="en">

<table>
    <tr>
        <th colspan="9">Laporan Data Rack</th>
    </tr>
    <tr>
        <th colspan="9">{{ date('d-m-Y', strtotime($from)) }} / {{ date('d-m-Y', strtotime($to)) }}</th>
    </tr>
    <tr></tr>
    <tr>
        <th style="font-weight: 800;border: 1px solid #000;">No. Rack</th>
        <th style="font-weight: 800;border: 1px solid #000;">No. Stocker</th>
        <th style="font-weight: 800;border: 1px solid #000;">No. WS</th>
        <th style="font-weight: 800;border: 1px solid #000;">No. Cut</th>
        <th style="font-weight: 800;border: 1px solid #000;">Style</th>
        <th style="font-weight: 800;border: 1px solid #000;">Color</th>
        <th style="font-weight: 800;border: 1px solid #000;">Part</th>
        <th style="font-weight: 800;border: 1px solid #000;">Size</th>
        <th style="font-weight: 800;border: 1px solid #000;">Qty</th>
    </tr>
    @foreach ($data as $d)
        <tr>
            <td style="border: 1px solid #000;">{{ $d->no_rak }}</td>
            <td style="border: 1px solid #000;">{{ $d->no_stocker }} </td>
            <td style="border: 1px solid #000;">{{ $d->no_ws }} </td>
            <td style="border: 1px solid #000;">{{ $d->no_cut }} </td>
            <td style="border: 1px solid #000;">{{ $d->style }} </td>
            <td style="border: 1px solid #000;">{{ $d->color }} </td>
            <td style="border: 1px solid #000;">{{ $d->part }} </td>
            <td style="border: 1px solid #000;">{{ $d->size }} </td>
            <td style="border: 1px solid #000;">{{ $d->qty_in }} </td>
        </tr>
    @endforeach
</table>

</html>