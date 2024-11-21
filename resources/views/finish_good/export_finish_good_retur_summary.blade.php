<!DOCTYPE html>
<html lang="en">

<table class="table">
    <tr>
        <td colspan='16'>Laporan FG Retur Summary</td>
    </tr>
    <tr>
        <td colspan='16'>{{ date('d-M-Y', strtotime($from)) }} - {{ date('d-M-Y', strtotime($to)) }}
        </td>
    </tr>
    <tr>
        <td></td>
    </tr>
    <thead>
        <tr>
            <th style="background-color: aqua;border:1px solid black;font-weight:bold">No. SB</th>
            <th style="background-color: aqua;border:1px solid black;font-weight:bold">Tgl. Trans</th>
            <th style="background-color: aqua;border:1px solid black;font-weight:bold">PO</th>
            <th style="background-color: aqua;border:1px solid black;font-weight:bold">Buyer</th>
            <th style="background-color: aqua;border:1px solid black;font-weight:bold">WS</th>
            <th style="background-color: aqua;border:1px solid black;font-weight:bold">ID So Det</th>
            <th style="background-color: aqua;border:1px solid black;font-weight:bold">Color</th>
            <th style="background-color: aqua;border:1px solid black;font-weight:bold">Size</th>
            <th style="background-color: aqua;border:1px solid black;font-weight:bold">Dest</th>
            <th style="background-color: aqua;border:1px solid black;font-weight:bold">Qty</th>
            <th style="background-color: aqua;border:1px solid black;font-weight:bold">Notes</th>
            <th style="background-color: aqua;border:1px solid black;font-weight:bold">Inv / SJ</th>
            <th style="background-color: aqua;border:1px solid black;font-weight:bold">Jenis Dok</th>
            <th style="background-color: aqua;border:1px solid black;font-weight:bold">User</th>
            <th style="background-color: aqua;border:1px solid black;font-weight:bold">Tgl. Input</th>
        </tr>
    </thead>
    <tbody>
        @php
            $no = 1;
        @endphp
        @foreach ($data as $item)
            <tr>
                {{-- <td>{{ $no++ }}.</td> --}}
                <td>{{ $item->no_sb }}</td>
                <td>{{ $item->tgl_pengeluaran_fix }}</td>
                <td>{{ $item->po }}</td>
                <td>{{ $item->buyer }}</td>
                <td>{{ $item->ws }}</td>
                <td>{{ $item->id_so_det }}</td>
                <td>{{ $item->color }}</td>
                <td>{{ $item->size }}</td>
                <td>{{ $item->dest }}</td>
                <td>{{ $item->qty }}</td>
                <td>{{ $item->notes }}</td>
                <td>{{ $item->invno }}</td>
                <td>{{ $item->jenis_dok }}</td>
                <td>{{ $item->created_by }}</td>
                <td>{{ $item->created_at }}</td>
            </tr>
        @endforeach
    </tbody>

</table>

</html>
