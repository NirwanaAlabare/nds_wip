<!DOCTYPE html>
<html lang="en">
<table class="table">
    <tr>
        <td colspan='14'>Laporan Bahan Bakar</td>
    </tr>
    <tr>
        <td colspan='14'>{{ date('d-M-Y', strtotime($from)) }} - {{ date('d-M-Y', strtotime($to)) }}
        </td>
    </tr>
    <tr>
        <td></td>
    </tr>
    <thead>
        <tr>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">No</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">No. Form</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">Tgl. Trans</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">Plat No</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">Driver</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">Kendaraan</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">Oddometer</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">Bahan Bakar</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">Jumlah</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">Total Pembayaran</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">Jumlah Realisasi</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">Realisasi Bayar</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">Tgl. Realisasi</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">Status</th>
        </tr>
    </thead>
    <tbody>
        @php
            $no = 1;
        @endphp
        @foreach ($data as $item)
            <tr>
                <td>{{ $no++ }}.</td>
                <td>{{ $item->no_trans }}</td>
                <td>{{ $item->tgl_trans_fix }}</td>
                <td>{{ $item->plat_no }}</td>
                <td>{{ $item->nm_driver }}</td>
                <td>{{ $item->jns_kendaraan }}</td>
                <td>{{ $item->oddometer }}</td>
                <td>{{ $item->nm_bhn_bakar }}</td>
                <td>{{ $item->jml_fix }}</td>
                <td>{{ $item->tot_biaya_fix }}</td>
                <td>{{ $item->realisasi_jml_fix }}</td>
                <td>{{ $item->tot_biaya_realisasi_fix }}</td>
                <td>{{ $item->realisasi_tgl_fix }}</td>
                <td>{{ $item->status_fix }}</td>
            </tr>
        @endforeach
    </tbody>

</table>

</html>
