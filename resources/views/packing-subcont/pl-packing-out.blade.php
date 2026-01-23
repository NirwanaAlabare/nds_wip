<!-- resources/views/packing-subcont/pl-packing-out.blade.php -->

<table style="width:100%; border-collapse: collapse;">

    <!-- HEADER PERUSAHAAN -->
    <tr>
        <td colspan="{{ 4 + count($sizes) + 3 }}" 
            style="text-align:center; font-size: 11pt;">
            PT. NIRWANA ALABARE GARMENT
        </td>
    </tr>
    <tr>
        <td colspan="{{ 4 + count($sizes) + 3 }}" 
            style="text-align:center; font-size: 11pt;">
            JL. RANCAEKEK MAJALAYA NO 289
        </td>
    </tr>
    <tr>
        <td colspan="{{ 4 + count($sizes) + 3 }}" 
            style="text-align:center; font-size: 11pt;">
            SOLOKAN JERUK MAJALAYA KAB. BANDUNG
        </td>
    </tr>
    <tr style="border-bottom: 1pt solid black;">
        <td colspan="{{ 4 + count($sizes) + 3 }}" 
            style="text-align:center; font-size: 11pt;border-bottom: 1pt solid black;">
            INDONESIA
        </td>
    </tr>

</table>

<table style="font-size: 11pt;width:100%;">
    <tr>
        <td style="padding:4px;" colspan="2">No Transaksi : {{ $header->no_bppb ?? '-' }}
        </td>
        <td style="padding:4px;" colspan="2">Item : {{ $header->itemdesc ?? '-' }}
        </td>
        <td style="padding:4px;" colspan="4">Berat Garment : {{ $header->berat_garment ?? '-' }} kg
        </td>
    </tr>
    <tr>
        <td style="padding:4px;" colspan="2">Supplier : {{ $header->supplier ?? '-' }}
        </td>
        <td style="padding:4px;" colspan="2">Qty Karton : -
        </td>
        <td style="padding:4px;" colspan="4">Berat Karton : {{ $header->berat_karton ?? '-' }} kg
        </td>
    </tr>
</table>

<table style="font-size: 11pt;width:100%; border-collapse: collapse;white-space: nowrap;" width="100%">

    <!-- HEADER BARIS 1 -->
    <tr>
        <th rowspan="2" style="border:1px solid #000; text-align:center;width: 200px;vertical-align: middle">Buyer</th>
        <th rowspan="2" style="border:1px solid #000; text-align:center;width: 100px;vertical-align: middle">Worksheet</th>
        <th rowspan="2" style="border:1px solid #000; text-align:center;width: 100px;vertical-align: middle">Style</th>
        <th rowspan="2" style="border:1px solid #000; text-align:center;width: 200px;vertical-align: middle">Color</th>

        <th colspan="{{ count($sizes) }}" 
            style="border:1px solid #000; text-align:center;vertical-align: middle">
            Size
        </th>

        <th rowspan="2" style="border:1px solid #000; text-align:center;width: 70px;vertical-align: middle">
            Total PCS
        </th>
        <th rowspan="2" style="border:1px solid #000; text-align:center;width: 70px;vertical-align: middle">
            NW (kg)
        </th>
        <th rowspan="2" style="border:1px solid #000; text-align:center;width: 70px;vertical-align: middle">
            GW (kg)
        </th>
    </tr>

    <!-- HEADER BARIS 2 -->
    <tr>

        @foreach ($sizes as $s)
            <th style="border:1px solid #000; text-align:center;">
                {{ $s }}
            </th>
        @endforeach

    </tr>

    <!-- DATA -->
    @php $grandTotal = 0; @endphp
    @foreach ($rows as $row)
        @php
            $sum = 0;
        @endphp

        <tr>
            <td style="border:1px solid #000;">{{ $row['buyer'] }}</td>
            <td style="border:1px solid #000;">{{ $row['kpno'] }}</td>
            <td style="border:1px solid #000;">{{ $row['styleno'] }}</td>
            <td style="border:1px solid #000;">{{ $row['color'] }}</td>

            @foreach ($sizes as $s)
                @php $val = $row[$s] ?? 0; $sum += $val; @endphp
                <td style="border:1px solid #000; text-align:right;">
                    {{ $val }}
                </td>
            @endforeach

            <td style="border:1px solid #000; text-align:right;">
                {{ $sum }}
            </td>
            <td style="border:1px solid #000;">{{ $row['total_nw'] }}</td>
            <td style="border:1px solid #000;">{{ $row['total_gw'] }}</td>
        </tr>

        @php $grandTotal += $sum; @endphp
    @endforeach

    <!-- TOTAL -->
    <tr>
        <td colspan="{{ 4 + count($sizes) }}" 
            style="border:1px solid #000; text-align:right; font-size: 11pt;">
            TOTAL
        </td>
        <td style="border:1px solid #000; text-align:right; font-size: 11pt;">
            {{ $grandTotal }}
        </td>
        <td style="border:1px solid #000; text-align:right; font-size: 11pt;"></td>
        <td style="border:1px solid #000; text-align:right; font-size: 11pt;"></td>
    </tr>
</table>
