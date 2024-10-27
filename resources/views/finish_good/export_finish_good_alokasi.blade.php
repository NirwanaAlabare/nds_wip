<!DOCTYPE html>
<html lang="en">

<table class="table">
    <tr>
        <td colspan='11'>Laporan FG Alokasi</td>
    </tr>
    <tr>
        <td colspan='11'>{{ date('d-M-Y') }}
        </td>
    </tr>
    <tr>
        <td></td>
    </tr>
    <thead>
        <tr>
            <th style="background-color: yellow;border:1px solid black;font-weight:bold">Lokasi</th>
            <th style="background-color: yellow;border:1px solid black;font-weight:bold">Penempatan</th>
            <th style="background-color: yellow;border:1px solid black;font-weight:bold">Buyer</th>
            <th style="background-color: yellow;border:1px solid black;font-weight:bold">PO</th>
            <th style="background-color: yellow;border:1px solid black;font-weight:bold">WS</th>
            <th style="background-color: yellow;border:1px solid black;font-weight:bold">Barcode</th>
            <th style="background-color: yellow;border:1px solid black;font-weight:bold">Color</th>
            <th style="background-color: yellow;border:1px solid black;font-weight:bold">Size</th>
            <th style="background-color: yellow;border:1px solid black;font-weight:bold">Qty</th>
            <th style="background-color: yellow;border:1px solid black;font-weight:bold">Dest</th>
            <th style="background-color: yellow;border:1px solid black;font-weight:bold">No. Carton</th>
        </tr>
    </thead>
    <tbody>
        @php
            $no = 1;
        @endphp
        @foreach ($data as $item)
            <tr>
                {{-- <td>{{ $no++ }}.</td> --}}
                <td>{{ $item->lokasi }}</td>
                <td>{{ $item->penempatan }}</td>
                <td>{{ $item->buyer }}</td>
                <td>{{ $item->po }}</td>
                <td>{{ $item->ws }}</td>
                <td>{{ $item->barcode }}</td>
                <td>{{ $item->color }}</td>
                <td>{{ $item->size }}</td>
                <td>{{ $item->qty }}</td>
                <td>{{ $item->dest }}</td>
                <td>{{ $item->no_carton }}</td>
            </tr>
        @endforeach
    </tbody>

</table>

</html>
