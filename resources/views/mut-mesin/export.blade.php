<!DOCTYPE html>
<html lang="en">

<table class="table">
    <tr>
        <td colspan='9'>Laporan Karyawan</td>
    </tr>
    <tr>
        <td colspan='9'>{{ date('d-M-Y', strtotime($from)) }} - {{ date('d-M-Y', strtotime($to)) }}
        </td>
    </tr>
    <thead>
        <tr>
            <td>No</td>
            <td>Tanggal Pindah</td>
            <td>Line Sekarang</td>
            <td>Jenis Mesin</td>
            <td>Brand</td>
            <td>Tipe Mesin</td>
            <td>Serial No</td>
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
                <td>{{ $item->tgl_pindah_fix }}</td>
                <td>{{ $item->line }}</td>
                <td>{{ $item->jenis_mesin }}</td>
                <td>{{ $item->brand }}</td>
                <td>{{ $item->tipe_mesin }}</td>
                <td>{{ $item->serial_no }}</td>
                <td>{{ $item->line_asal }}</td>
                <td>{{ $item->updated_data }}</td>
            </tr>
        @endforeach
    </tbody>

</table>

</html>
