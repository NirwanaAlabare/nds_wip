<!DOCTYPE html>
<html lang="en">

<table class="table">
    <tr>
        <td colspan='16'>Laporan Master SO SB</td>
    </tr>
    <tr>
        <td></td>
    </tr>
    <thead>
        <tr>
            <th style="background-color: yellow;border:1px solid black;font-weight:bold">No</th>
            <th style="background-color: #D6EEEE;border:1px solid black;font-weight:bold">ID SO Det</th>
            <th style="background-color: yellow;border:1px solid black;font-weight:bold">WS</th>
            <th style="background-color: yellow;border:1px solid black;font-weight:bold">No. Costing</th>
            <th style="background-color: yellow;border:1px solid black;font-weight:bold">Tgl. Kirim</th>
            <th style="background-color: yellow;border:1px solid black;font-weight:bold">Product Group</th>
            <th style="background-color: yellow;border:1px solid black;font-weight:bold">Product Item</th>
            <th style="background-color: yellow;border:1px solid black;font-weight:bold">Style</th>
            <th style="background-color: yellow;border:1px solid black;font-weight:bold">Main Dest</th>
            <th style="background-color: yellow;border:1px solid black;font-weight:bold">Dest</th>
            <th style="background-color: yellow;border:1px solid black;font-weight:bold">Brand</th>
            <th style="background-color: yellow;border:1px solid black;font-weight:bold">No. SO</th>
            <th style="background-color: yellow;border:1px solid black;font-weight:bold">Buyer</th>
            <th style="background-color: yellow;border:1px solid black;font-weight:bold">Color</th>
            <th style="background-color: yellow;border:1px solid black;font-weight:bold">Size</th>
            <th style="background-color: yellow;border:1px solid black;font-weight:bold">Qty SO</th>
            <th style="background-color: yellow;border:1px solid black;font-weight:bold">Price</th>
            <th style="background-color: yellow;border:1px solid black;font-weight:bold">Reff No</th>
            <th style="background-color: yellow;border:1px solid black;font-weight:bold">Style No Prod</th>
        </tr>
    </thead>
    <tbody>
        @php
            $no = 1;
        @endphp
        @foreach ($data as $item)
            <tr>
                <td>{{ $no++ }}.</td>
                <td style="background-color: #D6EEEE;">{{ $item->id_so_det }}</td>
                <td>{{ $item->ws }}</td>
                <td>{{ $item->cost_no }}</td>
                <td>{{ $item->tgl_kirim_fix }}</td>
                <td>{{ $item->product_group }}</td>
                <td>{{ $item->product_item }}</td>
                <td>{{ $item->styleno }}</td>
                <td>{{ $item->main_dest }}</td>
                <td>{{ $item->dest }}</td>
                <td>{{ $item->brand }}</td>
                <td>{{ $item->so_no }}</td>
                <td>{{ $item->buyer }}</td>
                <td>{{ $item->color }}</td>
                <td>{{ $item->size }}</td>
                <td>{{ $item->qty }}</td>
                <td>{{ $item->price }}</td>
                <td>{{ $item->reff_no }}</td>
                <td>{{ $item->styleno_prod }}</td>
            </tr>
        @endforeach
    </tbody>

</table>

</html>
