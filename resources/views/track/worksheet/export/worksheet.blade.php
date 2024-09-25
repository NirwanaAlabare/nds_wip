<!DOCTYPE html>
<html lang="en">

<table >
    <tr>
        <td colspan="15" style="font-weight: 800;">Track WS</td>
    </tr>
    <tr>
        <td colspan="15" style="font-weight: 800;">{{ $month."-".$year }}</td>
    </tr>
    <tr>
        <th style="font-weight: 800;">Worksheet</th>
        <th style="font-weight: 800;">Style</th>
        <th style="font-weight: 800;">Color</th>
        <th style="font-weight: 800;">Size</th>
        <th style="font-weight: 800;">Destination</th>
        <th style="font-weight: 800;">Qty</th>
        <th style="font-weight: 800;">Panel</th>
        <th style="font-weight: 800;">Cut Marker</th>
        <th style="font-weight: 800;">Cut Form</th>
        <th style="font-weight: 800;">Stocker</th>
        <th style="font-weight: 800;">DC</th>
        <th style="font-weight: 800;">Secondary In</th>
        <th style="font-weight: 800;">Secondary Inhouse</th>
        <th style="font-weight: 800;">QC Sewing</th>
        <th style="font-weight: 800;">Packing Sewing</th>
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
            <td style="vertical-align: top;">{{ $ws->total_cut_marker ? $ws->total_cut_marker : '0'  }}</td>
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
