<!DOCTYPE html>
<html lang="en">

<table>
    <tr>
        <th style="font-weight: 800;">Pemakaian Roll</th>
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
    @foreach ($data->sortBy("no_req") as $roll)
        <tr>
            <td>{{ $roll->tanggal_req }}</td>
            <td>{{ $roll->no_req }}</td>
            <td>{{ $roll->no_out }}</td>
            <td>{{ $roll->no_ws }}</td>
            <td>{{ $roll->no_ws_aktual }}</td>
            <td>{{ $roll->styleno }}</td>
            <td>{{ $roll->id_roll }}</td>
            <td>{{ $roll->id_item }}</td>
            <td>{{ $roll->detail_item }}</td>
            <td>{{ $roll->color }}</td>
            <td>{{ $roll->lot }}</td>
            <td>{{ $roll->roll }}</td>
            <td>{{ $roll->qty }}</td>
            <td>{{ $roll->total_pemakaian_roll }}</td>
            <td>{{ $roll->sisa_kain }}</td>
            <td>{{ $roll->total_short_roll }}</td>
            <td>{{ $roll->qty > 0 ? round(($roll->total_short_roll/($roll->qty+$roll->sisa_kain)) * 100, 2) : 0 }} %</td>
            <td>{{ $roll->unit }}</td>
        </tr>
    @endforeach
</table>

</html>
