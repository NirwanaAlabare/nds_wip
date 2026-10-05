<!DOCTYPE html>
<html lang="en">

<table>
    @php
        $totalAwal = 0;
        $totalSwitchingOut = 0;
        $totalSwitchingIn = 0;
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
        <th style="font-weight: 800;text-align: center;vertical-align: middle;">NO. FORM</th>
        <th style="font-weight: 800;text-align: center;vertical-align: middle;">OUTPUT AWAL</th>
        <th style="font-weight: 800;text-align: center;vertical-align: middle;">SWITCHING OUT</th>
        <th style="font-weight: 800;text-align: center;vertical-align: middle;">SWITCHING IN</th>
        <th style="font-weight: 800;text-align: center;vertical-align: middle;">OUTPUT</th>
    </tr>
    @foreach ($reportCutting as $cutting)
        <tr>
            <td>{{ $cutting->tanggal }}</td>
            {{-- @if ($currentMeja != $cutting->meja)
                @php
                    $currentMeja = $cutting->meja;
                @endphp
                <td style="text-align: center;vertical-align: middle;" rowspan="{{ $reportCutting->where('meja', $cutting->meja)->count() }}">{{ $cutting->meja }}</td>
            @endif --}}
            <td style="text-align: center;vertical-align: middle;">{{ $cutting->meja }}</td>
            <td>{{ $cutting->buyer }}</td>
            <td>{{ $cutting->worksheet }}</td>
            <td>{{ $cutting->style }}</td>
            <td>{{ $cutting->color }}</td>
            <td>{{ $cutting->panel }}</td>
            <td>{{ $cutting->no_form }}</td>
            <td>{{ $cutting->qty_awal }}</td>
            <td>{{ $cutting->qty_switching_out }}</td>
            <td>{{ $cutting->qty_switching_in }}</td>
            <td>{{ $cutting->qty_aktual }}</td>
            @php
                $totalAwal += $cutting->qty_awal;
                $totalSwitchingOut += $cutting->qty_switching_out;
                $totalSwitchingIn += $cutting->qty_switching_in;
                $totalOutput += $cutting->qty_aktual;
            @endphp
        </tr>
    @endforeach
    <tr>
        <th style="font-weight: bold;" colspan="8">TOTAL</th>
        <th style="font-weight: bold;">{{ $totalAwal }}</th>
        <th style="font-weight: bold;">{{ $totalSwitchingOut }}</th>
        <th style="font-weight: bold;">{{ $totalSwitchingIn }}</th>
        <th style="font-weight: bold;">{{ $totalOutput }}</th>
    </tr>
</table>

</html>
