<!DOCTYPE html>
<html lang="en">

<table >
    <tr>
        <td colspan="16">Track WS</td>
    </tr>
    <tr>
        <td colspan="16">{{ $month."-".$year }}</td>
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
        <tr>
            @if ($currentWorksheet != $ws->ws)
                <td style="vertical-align: top;" rowspan="{{ $worksheet->where("ws", $ws->ws)->count() }}">{{ $ws->ws }}</td>

                @php
                    $currentWorksheet = $ws->ws;
                    $currentStyle = null;
                    $currentColor = null;
                    $currentSize = null;
                    $currentDest = null;
                @endphp
            @endif

            @if ($currentWorksheet == $ws->ws && $currentStyle != $ws->styleno)
                <td style="vertical-align: top;" rowspan="{{ $worksheet->where('ws', $ws->ws)->where("styleno", $ws->styleno)->count() }}">{{ $ws->styleno ? $ws->styleno : '-'  }}</td>

                @php
                    $currentStyle = $ws->styleno;
                    $currentColor = null;
                    $currentSize = null;
                    $currentDest = null;
                @endphp
            @endif

            @if ($currentWorksheet == $ws->ws && $currentStyle == $ws->styleno && $currentColor != $ws->color)
                <td style="vertical-align: top;" rowspan="{{ $worksheet->where('ws', $ws->ws)->where('styleno', $ws->styleno)->where("color", $ws->color)->count() }}">{{ $ws->color ? $ws->color : '-'  }}</td>

                @php
                    $currentColor = $ws->color;
                    $currentSize = null;
                    $currentDest = null;
                @endphp
            @endif

            @if ($currentWorksheet == $ws->ws && $currentStyle == $ws->styleno && $currentColor == $ws->color && $currentSize != $ws->size)
                <td style="vertical-align: top;" rowspan="{{ $worksheet->where('ws', $ws->ws)->where('styleno', $ws->styleno)->where("color", $ws->color)->where("size", $ws->size)->count() }}">{{ $ws->size ? $ws->size : '-'  }}</td>

                @php
                    $currentSize = $ws->size;
                    $currentDest = null;
                @endphp
            @endif

            @if ($currentWorksheet == $ws->ws && $currentStyle == $ws->styleno && $currentColor == $ws->color && $currentSize == $ws->size && $currentDest != $ws->dest)
                @php
                    $currentRowspan = $worksheet->where('ws', $ws->ws)->where('styleno', $ws->styleno)->where("color", $ws->color)->where("size", $ws->size)->where("dest", $ws->dest)->count();
                @endphp

                <td style="vertical-align: top;" rowspan="{{ $currentRowspan }}">{{ $ws->dest ? $ws->dest : '-'  }}</td>
                <td style="vertical-align: top;" rowspan="{{ $currentRowspan }}">{{ $ws->qty ? $ws->qty : '0'  }}</td>

                @php
                    $currentDest = $ws->dest;
                    $currentRowspan = 0;
                @endphp
            @endif

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
        </tr>
    @endforeach
</table>

</html>
