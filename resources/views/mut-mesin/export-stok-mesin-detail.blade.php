<!DOCTYPE html>
<html lang="en">

<table class="table">
    <tr>
        <td colspan='7' style='text-align:center; vertical-align:middle'>Laporan Stok Detail Mesin</td>
    </tr>
    <tr>
    </tr>
    <thead>
        <tr>
            <td>No</td>
            <td>Kode QR</td>
            <td>Jenis Mesin</td>
            <td>Brand</td>
            <td>Tipe Mesin</td>
            <td>Serial No</td>
            <td>Lokasi</td>
        </tr>
    </thead>
    <tbody>
        @php
            $no = 1;
        @endphp
        @foreach ($data as $item)
            <tr>
                <td>{{ $no++ }}.</td>
                <td>{{ $item->id_qr }}</td>
                <td>{{ $item->jenis_mesin }}</td>
                <td>{{ $item->brand }}</td>
                <td>{{ $item->tipe_mesin }}</td>
                <td>{{ $item->serial_no }}</td>
                <td>{{ $item->line }}</td>
            </tr>
        @endforeach
    </tbody>

</table>

</html>
