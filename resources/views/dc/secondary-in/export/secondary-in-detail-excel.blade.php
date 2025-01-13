<table id="datatable-input">
    <tr>
        <th><b>WS</b></th>
        <th><b>Buyer</b></th>
        <th><b>Style</b></th>
        <th><b>Color</b></th>
        <th><b>In</b></th>
        <th><b>Reject</b></th>
        <th><b>Replace</b></th>
        <th><b>Out</b></th>
        <th><b>Proses</b></th>
    </tr>
    @php
        $totalQtyIn = 0;
        $totalQtyReject = 0;
        $totalQtyReplace = 0;
        $totalQtyOut = 0;
        $totalBalance = 0;
    @endphp
    @foreach ($data as $d)
        @php
            $totalQtyIn += $d->qty_in;
            $totalQtyReject += $d->qty_reject;
            $totalQtyReplace += $d->qty_replace;
            $totalQtyOut += $d->qty_out;
        @endphp
        <tr>
            <td>{{ $d->act_costing_ws }}</td>
            <td>{{ $d->buyer }}</td>
            <td>{{ $d->styleno }}</td>
            <td>{{ $d->color }}</td>
            <td>{{ $d->qty_in }}</td>
            <td>{{ $d->qty_reject }}</td>
            <td>{{ $d->qty_replace }}</td>
            <td>{{ $d->qty_out }}</td>
            <td>{{ $d->lokasi }}</td>
        </tr>
    @endforeach
    <tr>
        <td colspan="4">Total</td>
        <td>{{ $totalQtyIn }}</td>
        <td>{{ $totalQtyReject }}</td>
        <td>{{ $totalQtyReplace }}</td>
        <td>{{ $totalQtyOut }}</td>
    </tr>
</table>
