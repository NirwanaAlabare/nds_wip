<table id="datatable-input">
    <tr>
        <th><b>Tgl Transaksi</b></th>
        <th><b>ID QR</b></th>
        <th><b>WS</b></th>
        <th><b>Style</b></th>
        <th><b>Color</b></th>
        <th><b>Part</b></th>
        <th><b>Size</b></th>
        <th><b>No. Cut</b></th>
        <th><b>Tujuan Asal</b></th>
        <th><b>Lokasi Asal</b></th>
        <th><b>Range</b></th>
        <th><b>Qty Awal</b></th>
        <th><b>Qty Reject</b></th>
        <th><b>Qty Replace</b></th>
        <th><b>Qty In</b></th>
        <th><b>Buyer</b></th>
        <th><b>User</b></th>
        <th><b>Created At</b></th>
    </tr>
    @php
        $totalQtyAwal = 0;
        $totalQtyReject = 0;
        $totalQtyReplace = 0;
        $totalQtyIn = 0;
    @endphp
    @foreach ($data as $d)
        @php
            $totalQtyAwal += $d->qty_awal;
            $totalQtyReject += $d->qty_reject;
            $totalQtyReplace += $d->qty_replace;
            $totalQtyIn += $d->qty_in;
        @endphp
        <tr>
            <td>{{ $d->tgl_trans_fix }}</td>
            <td>{{ $d->id_qr_stocker }}</td>
            <td>{{ $d->act_costing_ws }}</td>
            <td>{{ $d->style }}</td>
            <td>{{ $d->color }}</td>
            <td>{{ $d->nama_part }}</td>
            <td>{{ $d->size }}</td>
            <td>{{ $d->no_cut }}</td>
            <td>{{ $d->tujuan }}</td>
            <td>{{ $d->lokasi }}</td>
            <td>{{ $d->stocker_range }}</td>
            <td>{{ $d->qty_awal }}</td>
            <td>{{ $d->qty_reject }}</td>
            <td>{{ $d->qty_replace }}</td>
            <td>{{ $d->qty_in }}</td>
            <td>{{ $d->buyer }}</td>
            <td>{{ $d->user }}</td>
            <td>{{ $d->created_at }}</td>
        </tr>
    @endforeach
    <tr>
        <td colspan="11">Total</td>
        <td>{{ $totalQtyAwal }}</td>
        <td>{{ $totalQtyReject }}</td>
        <td>{{ $totalQtyReplace }}</td>
        <td>{{ $totalQtyIn }}</td>
    </tr>
</table>
