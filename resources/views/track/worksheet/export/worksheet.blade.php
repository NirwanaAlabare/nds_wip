<!DOCTYPE html>
<html lang="en">

<table >
    <tr>
        <td colspan="18">Track WS</td>
    </tr>
    <tr>
        <td colspan="18">{{ $month."-".$year }}</td>
    </tr>
    <tr>
        <th>Worksheet</th>
        <th>Style</th>
        <th>Color</th>
        <th>Size</th>
        <th>Destination</th>
        <th>Qty</th>
        <th>Panel</th>
        <th>Ratio</th>
        <th>Ply Marker</th>
        <th>Cut Marker</th>
        <th>Ply Form</th>
        <th>Cut Form</th>
        <th>Stocker</th>
        <th>DC</th>
        <th>Secondary In</th>
        <th>Secondary Inhouse</th>
        <th>QC Sewing</th>
        <th>Packing Sewing</th>
    </tr>
    @php
        $currentWorksheet = null;
        $currentStyle = null;
        $currentColor = null;
        $currentSize = null;
        $currentDest = null;
        $currentRowspan = 0;
    @endphp
    @foreach ($worksheet as $ws)
        @php
            $thisOutputSewing = $outputSewing->where('so_det_id', $ws->id_so_det)->first();
            $thisOutputPacking = $outputPacking->where('so_det_id', $ws->id_so_det)->first();
        @endphp
        <tr>
            <td style="vertical-align: top;">{{ $ws->ws }}</td>
            <td style="vertical-align: top;">{{ $ws->styleno ? $ws->styleno : '-'  }}</td>
            <td style="vertical-align: top;">{{ $ws->color ? $ws->color : '-'  }}</td>
            <td style="vertical-align: top;">{{ $ws->size ? $ws->size : '-'  }}</td>
            <td style="vertical-align: top;">{{ $ws->dest ? $ws->dest : '-'  }}</td>
            <td style="vertical-align: top;">{{ $ws->qty ? $ws->qty : '0'  }}</td>
            <td style="vertical-align: top;">{{ $ws->panel ? $ws->panel : '-'  }}</td>
            <td style="vertical-align: top;">{{ $ws->total_ratio_marker ? $ws->total_ratio_marker : '0'  }}</td>
            <td style="vertical-align: top;">{{ $ws->total_gelar_marker ? $ws->total_gelar_marker : '0'  }}</td>
            <td style="vertical-align: top;">{{ $ws->total_cut_marker ? $ws->total_cut_marker : '0'  }}</td>
            <td style="vertical-align: top;">{{ $ws->total_lembar_form ? $ws->total_lembar_form : '0'  }}</td>
            <td style="vertical-align: top;">{{ $ws->total_cut_form ? $ws->total_cut_form : '0'  }}</td>
            <td style="vertical-align: top;">{{ $ws->total_stocker ? $ws->total_stocker : '0'  }}</td>
            <td style="vertical-align: top;">{{ $ws->total_dc ? $ws->total_dc : '0'  }}</td>
            <td style="vertical-align: top;">{{ $ws->total_sec ? $ws->total_sec : '0'  }}</td>
            <td style="vertical-align: top;">{{ $ws->total_sec_in ? $ws->total_sec_in : '0'  }}</td>
            <td style="vertical-align: top;">{{ $thisOutputSewing ? $thisOutputSewing->total_output : '0'  }}</td>
            <td style="vertical-align: top;">{{ $thisOutputPacking ? $thisOutputPacking->total_output : '0'  }}</td>
        </tr>
    @endforeach
</table>

</html>
