<!DOCTYPE html>
<html lang="en">
<table class="table">
    <tr>
        <td colspan='13'> Laporan Mutasi Packing (WIP) | Periode {{ $from }} s/d {{ $to }}</td>
    </tr>
    {{-- <tr>
        <td colspan='13'>{{ date('d-M-Y', strtotime($from)) }} - {{ date('d-M-Y', strtotime($to)) }}
        </td>
    </tr> --}}
    <tr>
        <td></td>
    </tr>
    <thead>
        <tr style='text-align:center; vertical-align:middle'>
            <th colspan="5" style="background-color: lightblue; text-align:center;">Jenis Produk</th>
            <th colspan="5" style="background-color: lightgreen; text-align:center;">Packing Line</th>
            <th colspan="4" style="background-color: lightsteelblue; text-align:center;">Transfer Garment
            </th>
            <th colspan="4" style="background-color: lightgoldenrodyellow; text-align:center;">Packing
                Central</th>
        </tr>
        <tr style='text-align:center; vertical-align:middle'>
            <th style="background-color: lightblue;">WS</th>
            <th style="background-color: lightblue;">Buyer</th>
            <th style="background-color: lightblue;">Style</th>
            <th style="background-color: lightblue;">Color</th>
            <th style="background-color: lightblue;">Size</th>
            <th style="background-color: lightgreen;">Saldo Awal</th>
            <th style="background-color: lightgreen;">Terima RTF</th>
            <th style="background-color: lightgreen;">Terima Reject</th>
            <th style="background-color: lightgreen;">Keluar</th>
            <th style="background-color: lightgreen;">Saldo Akhir</th>
            <th style="background-color: lightsteelblue;">Saldo Awal</th>
            <th style="background-color: lightsteelblue;">Terima</th>
            <th style="background-color: lightsteelblue;">Keluar</th>
            <th style="background-color: lightsteelblue;">Saldo Akhir</th>
            <th style="background-color: lightgoldenrodyellow;">Saldo Awal</th>
            <th style="background-color: lightgoldenrodyellow;">Terima</th>
            <th style="background-color: lightgoldenrodyellow;">Packing Scan</th>
            <th style="background-color: lightgoldenrodyellow;">Saldo Akhir</th>
        </tr>
    </thead>
    <tbody>
        @php
            $no = 1;
        @endphp
        @foreach ($data as $item)
            <tr>
                <td>{{ $item->ws }}</td>
                <td>{{ $item->buyer }}</td>
                <td>{{ $item->style }}</td>
                <td>{{ $item->color }}</td>
                <td>{{ $item->size }}</td>
                <td>{{ $item->pl_saldo_awal }}</td>
                <td>{{ $item->pl_rft }}</td>
                <td>{{ $item->pl_reject }}</td>
                <td>{{ $item->pl_keluar }}</td>
                <td>{{ $item->pl_saldo_akhir }}</td>
                <td>{{ $item->tg_saldo_awal }}</td>
                <td>{{ $item->tg_masuk }}</td>
                <td>{{ $item->tg_keluar }}</td>
                <td>{{ $item->tg_saldo_akhir }}</td>
                <td>{{ $item->pc_saldo_awal }}</td>
                <td>{{ $item->pc_terima }}</td>
                <td>{{ $item->pc_packing_scan }}</td>
                <td>{{ $item->pc_saldo_akhir }}</td>
            </tr>
        @endforeach
    </tbody>

</table>

</html>
