<!DOCTYPE html>
<html lang="en">

<table >
    @php
        $currentPanel = "";
    @endphp
    @foreach ($reportCutting as $cutting)
        @if ($currentPanel != $cutting->panel)
            @if ($loop->index != 0)
                <tr></tr>
                <tr></tr>
                <tr></tr>
            @endif
            <tr>
                <td colspan="2" style="font-weight: 800;background: #ffd966;">PLANNING</td>
                <td colspan="2" style="font-weight: 800;background: #ffd966;">{{ $cutting->panel }}</td>
                <td colspan="8" style="font-weight: 800;background: #ffd966;">{{ localeDateFormat($date) }}</td>
                <td colspan="4" style="font-weight: 800;background: #bdd7ee;text-align: center;">SPREADING</td>
                <td colspan="4" style="font-weight: 800;background: #f8cbad;text-align: center;">CUTTING</td>
                <td colspan="9" style="font-weight: 800;background: #ccccff;text-align: center;">MATERIAL</td>
            </tr>
            <tr>
                <th style="font-weight: 800;background: #ffd966;">Tanggal</th>
                <th style="font-weight: 800;background: #ffd966;">Meja</th>
                <th style="font-weight: 800;background: #ffd966;">Buyer</th>
                <th style="font-weight: 800;background: #ffd966;">No. WS</th>
                <th style="font-weight: 800;background: #ffd966;">Style</th>
                <th style="font-weight: 800;background: #ffd966;">Color</th>
                <th style="font-weight: 800;background: #ffd966;">Ket.</th>
                <th style="font-weight: 800;background: #ffd966;">Target Shift 1</th>
                <th style="font-weight: 800;background: #ffd966;">Pending Shift 1</th>
                <th style="font-weight: 800;background: #ffd966;">Target Shift 2</th>
                <th style="font-weight: 800;background: #ffd966;">Pending Shift 2</th>
                <th style="font-weight: 800;background: #ffd966;">Total Target</th>
                <th style="font-weight: 800;background: #bdd7ee;">Balance To Target</th>
                <th style="font-weight: 800;background: #bdd7ee;">Total Target</th>
                <th style="font-weight: 800;background: #bdd7ee;">Output Spreading</th>
                <th style="font-weight: 800;background: #bdd7ee;">Balance</th>
                <th style="font-weight: 800;background: #f8cbad;">Balance To Spreading</th>
                <th style="font-weight: 800;background: #f8cbad;">Total Target</th>
                <th style="font-weight: 800;background: #f8cbad;">Output Cutting</th>
                <th style="font-weight: 800;background: #f8cbad;">Balance</th>
                <th style="font-weight: 800;background: #ccccff;">Cons</th>
                <th style="font-weight: 800;background: #ccccff;">Need</th>
                <th style="font-weight: 800;background: #ccccff;">Sisa Kemarin</th>
                <th style="font-weight: 800;background: #ccccff;">In</th>
                <th style="font-weight: 800;background: #ccccff;">Total In</th>
                <th style="font-weight: 800;background: #ccccff;">Balance</th>
                <th style="font-weight: 800;background: #ccccff;">Use Act</th>
                <th style="font-weight: 800;background: #ccccff;">Sisa</th>
                <th style="font-weight: 800;background: #ccccff;">Unit</th>
            </tr>

            @php
                $currentPanel = $cutting->panel;
            @endphp
        @endif
        <tr>
            <td style="vertical-align: top;">{{ $cutting->tgl_form_cut }}</td>
            <td style="vertical-align: top;">{{ $cutting->meja ? $cutting->meja : '-'  }}</td>
            <td style="vertical-align: top;">{{ $cutting->buyer ? $cutting->buyer : '-'  }}</td>
            <td style="vertical-align: top;">{{ $cutting->act_costing_ws ? $cutting->act_costing_ws : '-'  }}</td>
            <td data-format="@" style="vertical-align: top;">{{ $cutting->style ? $cutting->style : '-'  }}</td>
            <td style="vertical-align: top;">{{ $cutting->color ? $cutting->color : '-'  }}</td>
            <td style="vertical-align: top;">{{ $cutting->notes ? $cutting->notes : '-'  }}</td>
            <td style="vertical-align: top;"></td>
            <td style="vertical-align: top;"></td>
            <td style="vertical-align: top;"></td>
            <td style="vertical-align: top;"></td>
            <td style="vertical-align: top;">{{ $cutting->marker_gelar ? $cutting->marker_gelar : '-'  }}</td>
            <td style="vertical-align: top;"></td>
            <td style="vertical-align: top;">{{ $cutting->marker_gelar ? $cutting->marker_gelar : '-'  }}</td>
            <td style="vertical-align: top;">{{ $cutting->spreading_gelar ? $cutting->spreading_gelar : '-'  }}</td>
            <td style="vertical-align: top;">{{ ($cutting->spreading_gelar ? $cutting->spreading_gelar : 0) - ($cutting->marker_gelar ? $cutting->marker_gelar : 0)  }}</td>
            <td style="vertical-align: top;"></td>
            <td style="vertical-align: top;">{{ $cutting->spreading_gelar ? $cutting->spreading_gelar : '-'  }}</td>
            <td style="vertical-align: top;">{{ $cutting->form_gelar ? $cutting->form_gelar : '-'  }}</td>
            <td style="vertical-align: top;">{{ ($cutting->form_gelar ? $cutting->form_gelar : 0) - ($cutting->spreading_gelar ? $cutting->spreading_gelar : 0) }}</td>
            <td style="vertical-align: top;">{{ ($cutting->cons_ws ? $cutting->cons_ws : 0) }}</td>
            <td style="vertical-align: top;"></td>
            <td style="vertical-align: top;"></td>
            <td style="vertical-align: top;"></td>
            <td style="vertical-align: top;"></td>
            <td style="vertical-align: top;"></td>
            <td style="vertical-align: top;">{{ ($cutting->spreading_gelar ? $cutting->spreading_gelar : 0) * ($cutting->cons_ws ? $cutting->cons_ws : 0) }}</td>
            <td style="vertical-align: top;"></td>
            <td style="vertical-align: top;">{{ ($cutting->unit) }}</td>
        </tr>

    @endforeach
</table>

</html>
