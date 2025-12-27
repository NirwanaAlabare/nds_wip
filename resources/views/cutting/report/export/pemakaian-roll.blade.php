<!DOCTYPE html>
<html lang="en">

<table>
    <tr>
        <th style="font-weight:800;">Pemakaian Roll</th>
    </tr>
    <tr>
        <th>{{ $dateFrom." - ".$dateTo }}</th>
    </tr>
    <tr>
        <th>Tanggal Req</th>
        <th>No. Req</th>
        <th>No. Trans</th>
        <th>No. WS</th>
        <th>No. WS Aktual</th>
        <th>Style</th>
        <th>ID Roll</th>
        <th>ID Item</th>
        <th>Detail Item</th>
        <th>Color</th>
        <th>Lot</th>
        <th>Roll</th>
        <th>Qty</th>
        <th>Total Pemakaian Kain</th>
        <th>Sisa Kain</th>
        <th>Total Short Roll</th>
        <th>Short Roll Percentage</th>
        <th>Unit</th>
    </tr>
    @foreach ($data as $roll)
        <tr>
            <td>{{ $roll->pluck("tanggal_req")->unique()->values()->implode(',') }}</td>
            <td>{{ $roll->pluck("no_req")->unique()->values()->implode(',') }}</td>
            <td>{{ $roll->pluck("no_out")->unique()->values()->implode(',') }}</td>
            <td>{{ $roll->pluck("no_ws")->unique()->values()->implode(',') }}</td>
            <td>{{ $roll->pluck("no_ws_aktual")->unique()->values()->implode(',') }}</td>
            <td>{{ $roll->pluck("styleno")->unique()->values()->implode(',') }}</td>
            <td>{{ $roll->pluck("id_roll")->unique()->values()->implode(',') }}</td>
            <td>{{ $roll->pluck("id_item")->unique()->values()->implode(',') }}</td>
            <td>{{ $roll->pluck("detail_item")->unique()->values()->implode(',') }}</td>
            <td>{{ $roll->pluck("color")->unique()->values()->implode(',') }}</td>
            <td>{{ $roll->pluck("lot")->unique()->values()->implode(',') }}</td>
            <td>{{ $roll->pluck("roll")->unique()->values()->implode(',') }}</td>
            <td>{{ $roll->first()->qty }}</td>
            <td>{{ $roll->first()->total_pemakaian_roll }}</td>
            <td>{{ $roll->first()->sisa_kain }}</td>
            <td>{{ $roll->first()->total_short_roll }}</td>
            <td>{{ $roll->first()->qty > 0 ? round(($roll->first()->total_short_roll/($roll->first()->qty+$roll->first()->sisa_kain)) * 100, 2) : 0 }} %</td>
            <td>{{ $roll->first()->unit }}</td>
        </tr>
    @endforeach
</table>

</html>
