<!DOCTYPE html>
<html lang="en">

<table class="table">
    <tr>
        <td>
            Laporan Pemakaian Roll
        </td>
    </tr>
    <tr>
        <td>
            {{ $from. " - " .$to}}
        </td>
    </tr>
    <thead>
        <tr>
            <th style="background-color: #a9cff5">No.</th>
            <th style="background-color: #a9cff5">Bulan</th>
            <th style="background-color: #a9cff5">Tanggal Input</th>
            <th style="background-color: #a9cff5">No. Form</th>
            <th style="background-color: #a9cff5">Meja</th>
            <th style="background-color: #a9cff5">No. WS</th>
            <th style="background-color: #a9cff5">Buyer</th>
            <th style="background-color: #a9cff5">Style</th>
            <th style="background-color: #a9cff5">Color</th>
            <th style="background-color: #a9cff5">Color Actual</th>
            <th style="background-color: #a9cff5">Panel</th>
            <th style="background-color: #a9cff5">Qty Order</th>
            <th style="background-color: #a9cff5">Cons. WS</th>
            <th style="background-color: #a9cff5">Cons. Marker</th>
            <th style="background-color: #a9cff5">Cons. Ampar</th>
            <th style="background-color: #a9cff5">Cons. Actual</th>
            <th style="background-color: #a9cff5">Cons. Piping</th>
            <th style="background-color: #a9cff5">Panjang Marker</th>
            <th style="background-color: #a9cff5">Unit Panjang Marker</th>
            <th style="background-color: #a9cff5">Comma Marker</th>
            <th style="background-color: #a9cff5">Unit Comma Marker</th>
            <th style="background-color: #a9cff5">Lebar Marker</th>
            <th style="background-color: #a9cff5">Unit Lebar Marker</th>
            <th style="background-color: #a9cff5">Panjang Actual</th>
            <th style="background-color: #a9cff5">Unit Panjang Actual</th>
            <th style="background-color: #a9cff5">Comma Actual</th>
            <th style="background-color: #a9cff5">Unit Comma Actual</th>
            <th style="background-color: #a9cff5">Lebar Actual</th>
            <th style="background-color: #a9cff5">Unit Lebar Actual</th>
            <th style="background-color: #f5dda9">ID Roll</th>
            <th style="background-color: #f5dda9">ID Item</th>
            <th style="background-color: #f5dda9">Detail Item</th>
            <th style="background-color: #f5dda9">No. Roll</th>
            <th style="background-color: #f5dda9">Lot</th>
            <th style="background-color: #f5dda9">Group</th>
            <th style="background-color: #f5dda9">Qty Roll</th>
            <th style="background-color: #f5dda9">Unit Roll</th>
            <th style="background-color: #f5dda9">Berat Amparan (KGM)</th>
            <th style="background-color: #f5dda9">Estimasi Amparan</th>
            <th style="background-color: #f5dda9">Lembar Amparan</th>
            <th style="background-color: #f5dda9">Ratio</th>
            <th style="background-color: #f5dda9">Qty Cut</th>
            <th style="background-color: #f5dda9">Average Time</th>
            <th style="background-color: #f5dda9">Sisa Gelaran</th>
            <th style="background-color: #f5dda9">Sambungan</th>
            <th style="background-color: #f5dda9">Sambungan Roll</th>
            <th style="background-color: #f5dda9">Kepala Kain</th>
            <th style="background-color: #f5dda9">Sisa Tidak Bisa</th>
            <th style="background-color: #f5dda9">Reject</th>
            <th style="background-color: #f5dda9">Piping</th>
            <th style="background-color: #f5dda9">Sisa Kain</th>
            <th style="background-color: #f5dda9">Pemakaian Gelar</th>
            <th style="background-color: #f5dda9">Total Pemakaian Roll</th>
            <th style="background-color: #f5dda9">Short Roll</th>
            <th style="background-color: #f5dda9">Short Roll (%)</th>
            <th style="background-color: #f5dda9">Operator</th>
        </tr>
    </thead>
    <tbody>
        @php
            $no = 1;

            $latestKepalaKain = 0;
            $latestSisaTidakBisa = 0;
            $latestReject = 0;
            $latestPiping = 0;
            $latestSambungan = 0;
            $latestSambunganRoll = 0;
            $latestPemakaianLembar = 0;
            $latestTotalPemakaian = 0;
            $latestShortRoll = 0;

            $latestStatus = "";
            $latestQty = 0;
            $latestUnit = "";

            $currentForm = "";
        @endphp
        @foreach ($data as $item)
            @php
                if ($item->no_form_cut_input != 'PIPING' && $currentForm != $item->no_form_cut_input) {
                    $latestKepalaKain = 0;
                    $latestSisaTidakBisa = 0;
                    $latestReject = 0;
                    $latestPiping = 0;
                    $latestSambungan = 0;
                    $latestSambunganRoll = 0;
                    $latestPemakaianLembar = 0;
                    $latestTotalPemakaian = 0;
                    $latestShortRoll = 0;

                    $latestStatus = "";
                    $latestQty = 0;
                    $latestUnit = "";

                    $currentForm = $item->no_form_cut_input;
                }

                $consLembar = 0;
                if ($item->unit_roll == 'KGM') {
                    $consLembar = $item->berat_amparan;
                } else {
                    $consLembar = $item->panjang_actual + ($item->comma_actual/100);
                }

                $currentSambunganRoll = $latestStatus != 'extension complete' ? $item->sambungan_roll : round(($item->sambungan_roll+$latestSambunganRoll), 2);
                $currentKepalaKain = $latestStatus != 'extension complete' ? $item->kepala_kain : round(($item->kepala_kain+$latestKepalaKain), 2);
                $currentSisaTidakBisa = $latestStatus != 'extension complete' ? $item->sisa_tidak_bisa : round(($item->sisa_tidak_bisa+$latestSisaTidakBisa), 2);
                $currentReject = $latestStatus != 'extension complete' ? $item->reject : round(($item->reject+$latestReject), 2);
                $currentPiping = $latestStatus != 'extension complete' ? $item->piping : round(($item->piping+$latestPiping), 2);

                $currentPemakaianLembar =  round(($item->status != 'extension complete' ? ($consLembar * $item->lembar_gelaran) : $item->sambungan) + $item->sisa_gelaran + $item->sambungan_roll + ($latestStatus != 'extension complete' ? 0 : $latestSambungan), 2);
                $currentTotalPemakaian = round($currentPemakaianLembar + $currentKepalaKain + $currentSisaTidakBisa + $currentReject + $currentPiping, 2);

                $currentShortRoll = $latestStatus != 'extension complete' ? round((($currentTotalPemakaian+$item->sisa_kain)-$item->qty_roll), 2) : round((($currentTotalPemakaian+$item->sisa_kain)-$latestQty), 2);
                $currentShortRollPercentage = $latestStatus != 'extension complete' ? ($item->qty_roll > 0 ? round((($currentTotalPemakaian+$item->sisa_kain)-$item->qty_roll)/$item->qty_roll*100) : 0 ) : ($latestQty > 0 ? round((($currentTotalPemakaian+$item->sisa_kain)-$latestQty)/$latestQty*100, 2) : 0);
            @endphp
            <tr>
                <td>{{ $item->status != 'extension complete' ? $no++ : '' }}</td>
                <td>{{ $item->bulan }}</td>
                <td>{{ $item->tgl_input }}</td>
                <td>{{ $item->no_form_cut_input }}</td>
                <td>{{ $item->nama_meja }}</td>
                <td>{{ $item->act_costing_ws }}</td>
                <td>{{ $item->buyer }}</td>
                <td>{{ $item->style }}</td>
                <td>{{ $item->color }}</td>
                <td>{{ $item->color_act }}</td>
                <td>{{ $item->panel }}</td>
                <td>{{ $item->qty }}</td>
                <td>{{ $item->cons_ws }}</td>
                <td>{{ $item->cons_marker }}</td>
                <td>{{ $item->cons_ampar }}</td>
                <td>{{ $item->cons_act }}</td>
                <td>{{ $item->cons_piping }}</td>
                <td>{{ $item->panjang_marker }}</td>
                <td>{{ $item->unit_panjang_marker }}</td>
                <td>{{ $item->comma_marker }}</td>
                <td>{{ $item->unit_comma_marker }}</td>
                <td>{{ $item->lebar_marker }}</td>
                <td>{{ $item->unit_lebar_marker }}</td>
                <td>{{ $item->panjang_actual }}</td>
                <td>{{ $item->unit_panjang_actual }}</td>
                <td>{{ $item->comma_actual }}</td>
                <td>{{ $item->unit_comma_actual }}</td>
                <td>{{ $item->lebar_actual }}</td>
                <td>{{ $item->unit_lebar_actual }}</td>
                <td>{{ $item->id_roll }}</td>
                <td>{{ $item->id_item }}</td>
                <td>{{ $item->detail_item }}</td>
                <td>{{ $item->status == "extension complete" ? "SAMBUNGAN" : $item->roll }}</td>
                <td>{{ $item->lot }}</td>
                <td>{{ $item->group_roll }}</td>
                @if ($item->status != 'extension complete')
                    <td>{{ $latestStatus != 'extension complete' ? $item->qty_roll : $latestQty }}</td>
                    <td>{{ $item->unit_roll }}</td>
                    <td>{{ $item->berat_amparan }}</td>
                    <td>{{ $item->est_amparan }}</td>
                    <td>{{ $item->lembar_gelaran }}</td>
                    <td>{{ $item->total_ratio }}</td>
                    <td>{{ $item->qty_cut }}</td>
                    <td>{{ $item->average_time }}</td>
                    <td>{{ $item->sisa_gelaran }}</td>
                    <td>{{ $latestStatus != 'extension complete' ? 0 : $latestSambungan }}</td>
                    <td>{{ $currentSambunganRoll }}</td>
                    <td>{{ $currentKepalaKain }}</td>
                    <td>{{ $currentSisaTidakBisa }}</td>
                    <td>{{ $currentReject }}</td>
                    <td>{{ $latestStatus != 'extension complete' ? $item->piping : round(($item->piping+$latestPiping), 2) }}</td>
                    <td>{{ $item->sisa_kain }}</td>
                    <td>{{ $currentPemakaianLembar }}</td>
                    <td>{{ $currentTotalPemakaian }}</td>
                    <td>{{ $currentShortRoll }}</td>
                    <td>{{ $currentShortRollPercentage }} %</td>
                @else
                    <td>-</td>
                    <td>-</td>
                    <td>-</td>
                    <td>-</td>
                    <td>{{ $item->lembar_gelaran }}</td>
                    <td>{{ $item->total_ratio }}</td>
                    <td>{{ $item->qty_cut }}</td>
                    <td>-</td>
                    <td>-</td>
                    <td>-</td>
                    <td>-</td>
                    <td>-</td>
                    <td>-</td>
                    <td>-</td>
                    <td>-</td>
                    <td>-</td>
                    <td>-</td>
                    <td>-</td>
                    <td>-</td>
                    <td>-</td>
                @endif
                <td>{{ $item->operator }}</td>
            </tr>

            @php
                $latestKepalaKain = $currentKepalaKain;
                $latestSisaTidakBisa = $currentSisaTidakBisa;
                $latestReject = $currentReject;
                $latestPiping = $currentPiping;
                $latestSambungan = $item->sambungan;
                $latestSambunganRoll = $currentSambunganRoll;
                $latestPemakaianLembar = $currentPemakaianLembar;
                $latestTotalPemakaian = $currentTotalPemakaian;
                $latestShortRoll = $currentShortRoll;

                $latestStatus = $item->status;
                $latestQty = $item->qty_roll;
                $latestUnit = $item->unit_roll;
            @endphp
        @endforeach
    </tbody>

</table>

</html>
