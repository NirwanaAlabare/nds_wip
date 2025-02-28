<!DOCTYPE html>
<html lang="en">

<table>
    <tr>
        <th>Line</th>
        <th>No. WS</th>
        <th>Style</th>
        <th>Color</th>
        <th>No. Cut</th>
        <th>No. Form</th>
        <th>Size</th>
        <th>Group</th>
        <th>Group</th>
        <th>Range</th>
        <th>Range</th>
        <th>No. Stocker</th>
        <th>Qty</th>
        <th>Waktu Loading</th>
    </tr>
    @php
        $currentForm = null;
        $currentSize = null;
        $currentGroup = null;
        $currentRange = null;
        $currentQty = null;

        $latestUpdate = null;

        $totalQty = 0;
    @endphp
    @foreach ($loadingLines as $loadingLine)
        @php
            $qty = $loadingLine->qty;

            if ($currentSize != $loadingLine->size || $currentRange != $loadingLine->range_awal) {
                $currentForm = $loadingLine->no_form;
                $currentSize = $loadingLine->size;
                $currentGroup = $loadingLine->group_stocker;
                $currentRange = $loadingLine->range_awal;

                $currentUpdate = $loadingLine->tanggal_loading;
                $currentUpdate > $latestUpdate && $latestUpdate = $currentUpdate;

                $totalQty += $qty;

                $currentQty = $qty;
            }
            else {
                $currentQty > $qty ? $totalQty = $totalQty - $currentQty + $qty : $totalQty = $totalQty;

                $currentQty = $qty;
            }
        @endphp
        <tr>
            <td>{{ $loadingLine->nama_line }}</td>
            <td>{{ $loadingLine->act_costing_ws }}</td>
            <td>{{ $loadingLine->style }}</td>
            <td>{{ $loadingLine->color }}</td>
            <td>{{ $loadingLine->no_form." / ".$loadingLine->no_cut }}</td>
            <td>{{ $loadingLine->no_form }}</td>
            <td>{{ $loadingLine->size }}</td>
            <td>{{ $loadingLine->group_stocker }}</td>
            <td>{{ $loadingLine->shade }}</td>
            <td>{{ $loadingLine->range_awal }}</td>
            <td>{{ ($loadingLine->range_awal)." - ".($loadingLine->range_akhir) }}</td>
            <td>{{ $loadingLine->id_qr_stocker }}</td>
            <td>{{ $qty }}</td>
            <td>{{ $loadingLine->tanggal_loading }}</td>
        </tr>
    @endforeach
    <tr>
        <th colspan="12">TOTAL</th>
        <th>{{ $totalQty }}</th>
        <th>{{ $latestUpdate }}</th>
    </tr>
</table>

</html>
