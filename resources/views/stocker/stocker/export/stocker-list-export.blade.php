<table>
    <tr>
        <th colspan="16">Stocker Number List</th>
    </tr>
    <tr>
        <th colspan="16">{{ $dateFrom." - ".$dateTo }}</th>
    </tr>
    <tr>
        <th style="font-weight: 800;">Tanggal</th>
        <th style="font-weight: 800;">No. Form</th>
        <th style="font-weight: 800;">No. Cut</th>
        <th style="font-weight: 800;">Color</th>
        <th style="font-weight: 800;">Size</th>
        <th style="font-weight: 800;">Dest</th>
        <th style="font-weight: 800;">Panel</th>
        <th style="font-weight: 800;">Qty</th>
        <th style="font-weight: 800;">Year Sequence</th>
        <th style="font-weight: 800;">Year Sequence Range</th>
        <th style="font-weight: 800;">Buyer</th>
        <th style="font-weight: 800;">No. WS</th>
        <th style="font-weight: 800;">Style</th>
        <th style="font-weight: 800;">Stocker</th>
        <th style="font-weight: 800;">Stock</th>
        <th style="font-weight: 800;">Part</th>
        <th style="font-weight: 800;">Group</th>
        <th style="font-weight: 800;">Shade</th>
        <th style="font-weight: 800;">Ratio</th>
        <th style="font-weight: 800;">Stocker Range</th>
    </tr>
    @php
        $total = 0;
        $totalQty = 0;
    @endphp
    @foreach ($stockerList as $stock)
        @php
            $total++;
            $totalQty += $stock->qty;
        @endphp
        <tr>
            <td>{{ $stock->updated_at }}</td>
            <td>{{ $stock->no_form }}</td>
            <td>{{ $stock->no_cut }}</td>
            <td>{{ $stock->color }}</td>
            <td>{{ $stock->size }}</td>
            <td>{{ $stock->dest }}</td>
            <td>{{ $stock->panel }}</td>
            <td>{{ $stock->qty }}</td>
            <td>{{ $stock->year_sequence }}</td>
            <td>{{ $stock->numbering_range }}</td>
            <td>{{ $stock->buyer }}</td>
            <td>{{ $stock->act_costing_ws }}</td>
            <td>{{ $stock->style }}</td>
            <td>{{ $stock->id_qr_stocker_bundle }}</td>
            <td>{{ $stock->tipe }}</td>
            <td>{{ $stock->part }}</td>
            <td>{{ $stock->group_stocker }}</td>
            <td>{{ $stock->shade }}</td>
            <td>{{ $stock->ratio }}</td>
            <td>{{ $stock->stocker_range }}</td>
        </tr>
    @endforeach
    <tr>
        <td style="font-weight: bold;" colspan="6">TOTAL</td>
        <td style="font-weight: bold;">{{ $total }}</td>
        <td style="font-weight: bold;">{{ $totalQty }}</td>
    </tr>
</table>
