<!DOCTYPE html>
<html lang="en">

<table>
    <tr>
        <th style="font-weight: 800;">Line</th>
        <th style="font-weight: 800;">No. WS</th>
        <th style="font-weight: 800;">Style</th>
        <th style="font-weight: 800;">Color</th>
        <th style="font-weight: 800;">No. Cut</th>
        <th style="font-weight: 800;">No. Form</th>
        <th style="font-weight: 800;">Size</th>
        <th style="font-weight: 800;">Group</th>
        <th style="font-weight: 800;">Group</th>
        <th style="font-weight: 800;">Range</th>
        <th style="font-weight: 800;">Range</th>
        <th style="font-weight: 800;">Part</th>
        <th style="font-weight: 800;">Status</th>
        <th style="font-weight: 800;">No. Stocker</th>
        <th style="font-weight: 800;">Stock</th>
        <th style="font-weight: 800;">No. Bon</th>
        <th style="font-weight: 800;">Qty</th>
        <th style="font-weight: 800;">Hari</th>
        <th style="font-weight: 800;">Waktu</th>
        <th style="font-weight: 800;">By</th>
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

            if ($currentSize != $loadingLine->size || $currentRange != $loadingLine->range_awal || (str_contains($currentForm, "GR") || $currentForm != $loadingLine->no_form)) {
                $currentForm = $loadingLine->no_form;
                $currentSize = $loadingLine->size;
                $currentGroup = $loadingLine->group_stocker ? $loadingLine->group_stocker : $loadingLine->shade;
                $currentRange = $loadingLine->range_awal;

                $currentUpdate = $loadingLine->tanggal_loading;
                $currentUpdate > $latestUpdate && $latestUpdate = $currentUpdate;

                $totalQty += $qty;

                $currentQty = $qty;
            } else {
                // $currentQty > $qty ? $totalQty = $totalQty - $currentQty + $qty : $totalQty = $totalQty;

                $currentQty = $qty;
            }
        @endphp
        <tr>
            <td>{{ $loadingLine->nama_line }}</td>
            <td>{{ $loadingLine->act_costing_ws }}</td>
            <td>{{ $loadingLine->style }}</td>
            <td>{{ $loadingLine->color }}</td>
            <td>{{ $loadingLine->no_cut }}</td>
            <td>{{ $loadingLine->no_form }}</td>
            <td>{{ $loadingLine->size }}</td>
            <td>{{ $loadingLine->group_stocker }}</td>
            <td>{{ $loadingLine->shade }}</td>
            <td>{{ $loadingLine->range_awal }}</td>
            <td>{{ ($loadingLine->range_awal)." - ".($loadingLine->range_akhir) }}</td>
            <td>{{ $loadingLine->part }}</td>
            <td style="{{ $loadingLine->part_status == 'main' ? 'font-weight: 800;' : '' }}">{{ strtoupper($loadingLine->part_status) }}</td>
            <td>{{ $loadingLine->id_qr_stocker }}</td>
            <td>{{ $loadingLine->type }}</td>
            <td>{{ $loadingLine->no_bon ? $loadingLine->no_bon : '-' }}</td>
            <td style="{{ $loadingLine->part_status == 'main' ? 'font-weight: 800;' : '' }}">{{ $qty }}</td>
            <td>{{ $loadingLine->tanggal_loading }}</td>
            <td>{{ $loadingLine->waktu_loading }}</td>
            <td>{{ $loadingLine->user }}</td>
        </tr>
    @endforeach
    <tr>
        <th style="font-weight: 800;" colspan="16">TOTAL</th>
        <th style="font-weight: 800;">{{ $totalQty }}</th>
        <th style="font-weight: 800;">{{ $latestUpdate }}</th>
    </tr>
</table>

</html>
