<!DOCTYPE html>
<html lang="en">

<table class="table">
    <tr>
        <td colspan='5' style='text-align:center; vertical-align:middle'>Laporan Stok Mesin</td>
    </tr>
    <tr>
    </tr>
    <thead>
        <tr>
            <td>No</td>
            <td>Jenis Mesin</td>
            <td>Brand</td>
            <td>Total</td>
            <td>Satuan</td>
        </tr>
    </thead>
    <tbody>
        @php
            $no = 1;
        @endphp
        @foreach ($data as $item)
            <tr>
                <td>{{ $no++ }}.</td>
                <td>{{ $item->jenis_mesin }}</td>
                <td>{{ $item->brand }}</td>
                <td>{{ $item->total }}</td>
                <td>{{ $item->satuan }}</td>
            </tr>
        @endforeach
    </tbody>

</table>

</html>
