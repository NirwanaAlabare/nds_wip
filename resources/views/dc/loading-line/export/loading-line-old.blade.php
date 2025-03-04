<!DOCTYPE html>
<html lang="en">

<table>
    <tr>
        <th style="font-weight: 800;">Line</th>
        <th style="font-weight: 800;">No. WS</th>
        <th style="font-weight: 800;">Style</th>
        <th style="font-weight: 800;">Color</th>
        <th style="font-weight: 800;">Target Sewing</th>
        <th style="font-weight: 800;">Target Loading</th>
        <th style="font-weight: 800;">Loading</th>
        <th style="font-weight: 800;">Balance Loading</th>
        <th style="font-weight: 800;">Trolley</th>
        <th style="font-weight: 800;">Stok Trolley</th>
        <th style="font-weight: 800;">Color</th>
    </tr>
    @php
        $totalTargetSewing = 0;
        $totalTargetLoading = 0;
        $totalLoading = 0;
        $totalBalanceLoading = 0;
    @endphp
    @foreach ($data as $d)
        @php
            $lineData = $lines->where('line_id', $d->line_id)->first();
            $line = $lineData ? strtoupper(str_replace("_", " ", $lineData->username)) : "";

            $totalTargetSewing += $d->target_sewing;
            $totalTargetLoading += $d->target_loading;
            $totalLoading += $d->loading_qty;
            $totalBalanceLoading += $d->loading_balance;
        @endphp
        <tr>
            <td>{{ $line }}</td>
            <td>{{ $d->act_costing_ws }}</td>
            <td>{{ $d->style }}</td>
            <td>{{ $d->color }}</td>
            <td>{{ $d->target_sewing ? $d->target_sewing : 0 }}</td>
            <td>{{ $d->target_loading ? $d->target_loading : 0 }}</td>
            <td>{{ $d->loading_qty ? $d->loading_qty : 0 }}</td>
            <td>{{ $d->loading_balance ? $d->loading_balance : 0 }}</td>
            <td>{{ $d->nama_trolley }}</td>
            <td>{{ $d->trolley_qty ? $d->trolley_qty : 0 }}</td>
            <td>{{ $d->trolley_color ? $d->trolley_color : "-" }}</td>
        </tr>
    @endforeach
    <tr>
        <th colspan="4">Total</th>
        <th>{{ $totalTargetSewing }}</th>
        <th>{{ $totalTargetLoading }}</th>
        <th>{{ $totalLoading }}</th>
        <th>{{ $totalBalanceLoading }}</th>
        <th></th>
        <th></th>
        <th></th>
    </tr>
</table>

</html>
