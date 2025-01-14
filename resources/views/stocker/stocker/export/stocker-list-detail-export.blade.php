<table>
    <tr>
        <td>Stocker List Number</td>
    </tr>
    <tr>
        <td>{{ $stockerList[0]->id_qr_stocker }}</td>
        <td>{{ $stockerList[0]->act_costing_ws }}</td>
        <td>{{ $stockerList[0]->style }}</td>
        <td>{{ $stockerList[0]->color }}</td>
        <td>{{ $stockerList[0]->size.($stockerList[0]->dest && $stockerList[0]->dest != '-' ? " - ".$stockerList[0]->dest : "") }}</td>
    </tr>
    <tr>
        <td>{{ $stockerList[0]->no_form." / ".$stockerList[0]->no_cut }}</td>
        <td>{{ $stockerList[0]->shade }}</td>
        <td>{{ $stockerList[0]->ratio }}</td>
        <td>{{ $stockerList[0]->range_awal." - ".$stockerList[0]->range_akhir }}</td>
    </tr>
    <tr>
        <th>Number</th>
        <th>Year Sequence</th>
        <th>Year Sequence Number</th>
        <th>Size</th>
        <th>Dest</th>
        <th>Sewing Line</th>
        <th>Sewing Input</th>
        <th>Packing Line</th>
        <th>Packing Input</th>
    </tr>
    @foreach ($stockerListNumber as $number)
        @php
            $thisOutput = $output->where("kode_numbering", $number->id_year_sequence)->first();
        @endphp
        <tr>
            <td>{{ $number->number }}</td>
            <td>{{ $number->year."_".$number->year_sequence }}</td>
            <td>{{ $number->year_sequence_number }}</td>
            <td>{{ $number->size }}</td>
            <td>{{ $number->dest }}</td>
            <td>{{ $thisOutput ? ($thisOutput->sewing_line ? $thisOutput->sewing_line : '-') : '-' }}</td>
            <td>{{ $thisOutput ? ($thisOutput->sewing_update ? $thisOutput->sewing_update : '-') : '-' }}</td>
            <td>{{ $thisOutput ? ($thisOutput->packing_line ? $thisOutput->packing_line : '-') : '-' }}</td>
            <td>{{ $thisOutput ? ($thisOutput->packing_update ? $thisOutput->packing_update : '-') : '-' }}</td>
        </tr>
    @endforeach
</table>
