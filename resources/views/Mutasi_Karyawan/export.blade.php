<!DOCTYPE html>
<html lang="en">

<table class="table">
    <tr>
        <td colspan='6'>Laporan Karyawan</td>
    </tr>
    <tr>
        <td colspan='6'>{{ date('d-M-Y', strtotime($from)) }} - {{ date('d-M-Y', strtotime($to)) }}
        </td>
    </tr>
    <thead>
        <tr>
            <td>No</td>
            <td>Tanggal</td>
            <td>Line Sekarang</td>
            <td>NIK</td>
            <td>Nama Karyawan</td>
            <td>Line Asal</td>
            <td>Last Update</td>
        </tr>
    </thead>
    <tbody>
        @php
            $no = 1;
        @endphp
        @foreach ($data as $item)
            <tr>
                <td>{{ $no++ }}.</td>
                <td>{{ $item->tanggal_berjalan}}</td>
                <td>{{ $item->line }}</td>
                <td>{{ $item->nik }}</td>
                <td>{{ $item->nm_karyawan }}</td>
                <td>{{ $item->line_asal }}</td>
                <td>{{ $item->updated_at }}</td>
            </tr>
        @endforeach
    </tbody>

</table>

</html>
