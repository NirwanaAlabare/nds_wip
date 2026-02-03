<table id="datatable-input">
    <tr>
        <th><b>WS</b></th>
        <th><b>Style</b></th>
        <th><b>Color</b></th>
        <th><b>Panel</b></th>
        <th><b>Part</b></th>
        <th><b>Size</b></th>
        <th><b>Tujuan</b></th>
        <th><b>Proses</b></th>
        <th><b>In</b></th>
        <th><b>Buyer</b></th>
    </tr>
    @php
        $totalQtyIn = 0;
    @endphp
    @foreach ($data as $d)
        @php
            $totalQtyIn += $d->qty_in;
        @endphp
        <tr>
            <td>{{ $d->act_costing_ws }}</td>
            <td>{{ $d->styleno }}</td>
            <td>{{ $d->color }}</td>
            <td>{{ $d->panel }}</td>
            <td>{{ $d->nama_part }}</td>
            <td>{{ $d->size }}</td>
            <td>{{ $d->tujuan }}</td>
            <td>{{ $d->proses }}</td>
            <td>{{ $d->qty_in }}</td>
            <td>{{ $d->buyer }}</td>
        </tr>
    @endforeach
    <tr>
        <td colspan="8">Total</td>
        <td>{{ $totalQtyIn }}</td>
    </tr>
</table>
