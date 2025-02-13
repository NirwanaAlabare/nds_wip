<!DOCTYPE html>
<html lang="en">

<table class="table">
    <tr>
        <td colspan='9'>Laporan Master Mesin</td>
    </tr>
    <tr>
    </tr>
    <thead>
        <tr>
            <td>No</td>
            <td>Kode QR</td>
            <td>Jenis</td>
            <td>Brand</td>
            <td>Tipe</td>
            <td>Serial No</td>
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
            </tr>
        @endforeach
    </tbody>

</table>

</html>
