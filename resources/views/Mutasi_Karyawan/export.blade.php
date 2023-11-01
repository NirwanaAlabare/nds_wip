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
            <td>Tgl. Pindah</td>
            <td>NIK</td>
            <td>Nama Karyawan</td>
            <td>Line</td>
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
                <td>{{ $item->tgl_pindah }}</td>
                <td>{{ $item->nik }}</td>
                <td>{{ $item->nm_karyawan }}</td>
                <td>{{ $item->line }}</td>
                <td>{{ $item->updated_at }}</td>
            </tr>
        @endforeach
    </tbody>

</table>

</html>
