<!DOCTYPE html>
<html lang="en">

<table>
    @php
        $totalOutput = 0;

        $currentMeja = "";
    @endphp
    <tr>
        <th></th>
        <th colspan="2" style="font-weight: 800;text-align: center;vertical-align: middle;">OUTPUT DAILY REPORT CUTTING</th>
    </tr>
    <tr></tr>
    <tr>
        <th></th>
        <th>Dari : {{ $dateFrom }}</th>
        <th>Sampai : {{ $dateTo }}</th>
    </tr>
    <tr></tr>
    <tr>
        <th style="font-weight: 800;text-align: center;vertical-align: middle;">TANGGAL</th>
        <th style="font-weight: 800;text-align: center;vertical-align: middle;">MEJA</th>
        <th style="font-weight: 800;text-align: center;vertical-align: middle;">BUYER</th>
        <th style="font-weight: 800;text-align: center;vertical-align: middle;">WORKSHEET</th>
        <th style="font-weight: 800;text-align: center;vertical-align: middle;">STYLE</th>
        <th style="font-weight: 800;text-align: center;vertical-align: middle;">COLOR</th>
        <th style="font-weight: 800;text-align: center;vertical-align: middle;">PANEL</th>
        <th style="font-weight: 800;text-align: center;vertical-align: middle;">OUTPUT</th>
    </tr>
    @foreach ($reportCutting as $cutting)
        <tr>
            <td>{{ $cutting->tgl_form_cut }}</td>
            @if ($currentMeja != $cutting->meja)
                @php
                    $currentMeja = $cutting->meja;
                @endphp
                <td style="text-align: center;vertical-align: middle;" rowspan="{{ $reportCutting->where('meja', $cutting->meja)->count() }}">{{ $cutting->meja }}</td>
            @endif
            <td>{{ $cutting->buyer }}</td>
            <td>{{ $cutting->act_costing_ws }}</td>
            <td>{{ $cutting->style }}</td>
            <td>{{ $cutting->color }}</td>
            <td>{{ $cutting->panel }}</td>
            <td>{{ $cutting->qty }}</td>
            @php
                $totalOutput += $cutting->qty;
            @endphp
        </tr>
    @endforeach
    <tr>
        <th style="font-weight: bold;" colspan="6">TOTAL</th>
        <th style="font-weight: bold;" colspan="2">{{ $totalOutput }}</th>
    </tr>
</table>

</html>
