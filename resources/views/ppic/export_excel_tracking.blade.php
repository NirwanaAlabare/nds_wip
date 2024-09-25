<!DOCTYPE html>
<html lang="en">

<table class="table">
    <tr>
        <td colspan='9'>Laporan Tracking Summary</td>
    </tr>
    <tr>
        {{-- <td colspan='12'>{{ date('d-M-Y', strtotime($from)) }} - {{ date('d-M-Y', strtotime($to)) }}
        </td> --}}
    </tr>
    <tr>
        <td></td>
    </tr>
    <thead>
        <tr>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">Buyer</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">WS</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">Color</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">Size</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">Qc Output</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">Packing Line Output</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">Qty Trf Garment</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">Qty Packing In</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">Qty Packing Out</th>
        </tr>
    </thead>
    <tbody>
        @php
            $no = 1;
        @endphp
        @foreach ($data as $item)
            <tr>
                <td>{{ $item->buyer }}</td>
                <td>{{ $item->ws }}</td>
                <td>{{ $item->color }}</td>
                <td>{{ $item->size }}</td>
                <td>{{ $item->tot_qc }}</td>
                <td>{{ $item->tot_p_line }}</td>
                <td>{{ $item->qty_trf_garment }}</td>
                <td>{{ $item->qty_packing_in }}</td>
                <td>{{ $item->qty_packing_out }}</td>
            </tr>
        @endforeach
    </tbody>

</table>

</html>
