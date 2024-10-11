<!DOCTYPE html>
<html>
<head>
    <title>Stocker</title>
    <style>
        @page { margin: 5px; }

        @font-face {
            font-family: 'Open Sans';
            font-style: normal;
            font-weight: bold;
            src: url({{ storage_path("OpenSans-Bold.ttf") }}) format('truetype');
        }

        body {
            margin: 0.0px;
            font-family: 'Open Sans', sans-serif;
            font-weight: bold;
        }

        * {
            font-size: 9px;
        }

        img {
            width: 40px;

        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table td, table th{
            text-align: left;
            vertical-align: middle;
            padding: 0.0px;
            border: 1px solid;
        }
    </style>
</head>
<body>
    <table style="width: 100%;margin-bottom: 5px;">
        <tr>
            <th style="border: none;vertical-align: middle;">Product</th>
            <td style="border: none;vertical-align: middle;padding: 2px;padding-left: 5px;padding-right: 5px;"> : </td>
            <th style="border: none;vertical-align: middle;">{{ $sbItem->detail_item }}</th>
        </tr>
        <tr>
            <th style="border: none;vertical-align: middle;">Kode&nbsp;Barang</th>
            <td style="border: none;vertical-align: middle;padding: 2px;padding-left: 5px;padding-right: 5px;"> : </td>
            <th style="border: none;vertical-align: middle;">{{ $sbItem->goods_code }}</th>
        </tr>
        <tr>
            <th style="border: none;vertical-align: middle;">ID Item</th>
            <td style="border: none;vertical-align: middle;padding: 2px;padding-left: 5px;padding-right: 5px;"> : </td>
            <th style="border: none;vertical-align: middle;">{{ $sbItem->id_item }}</th>
        </tr>
        <tr>
            <th style="border: none;vertical-align: middle;">No. BPB</th>
            <td style="border: none;vertical-align: middle;padding: 2px;padding-left: 5px;padding-right: 5px;"> : </td>
            <th style="border: none;vertical-align: middle;">{{ $sbItem->bppb }}</th>
        </tr>
        <tr>
            <th style="border: none;vertical-align: middle;">No. Req</th>
            <td style="border: none;vertical-align: middle;padding: 2px;padding-left: 5px;padding-right: 5px;"> : </td>
            <th style="border: none;vertical-align: middle;">{{ $sbItem->no_req }}</th>
        </tr>
        <tr>
            <th style="border: none;vertical-align: middle;">No. Form</th>
            <td style="border: none;vertical-align: middle;padding: 2px;padding-left: 5px;padding-right: 5px;"> : </td>
            <th style="border: none;vertical-align: middle;">{{ $ndsItem ? ($ndsItem->no_form ? $ndsItem->no_form : '-') : '-' }}</th>
        </tr>
        <tr>
            <th style="border: none;vertical-align: middle;">No. WS</th>
            <td style="border: none;vertical-align: middle;padding: 2px;padding-left: 5px;padding-right: 5px;"> : </td>
            <th style="border: none;vertical-align: middle;">{{ strtoupper($sbItem->no_ws) }}</th>
        </tr>
        <tr>
            <th style="border: none;vertical-align: middle;">Style</th>
            <td style="border: none;vertical-align: middle;padding: 2px;padding-left: 5px;padding-right: 5px;"> : </td>
            <th style="border: none;vertical-align: middle;">{{ $sbItem->style }}</th>
        </tr>
    </table>
    <table style="width: 100%;margin-bottom: 5px;">
        <tr>
            <th>No. Roll</th>
            <th>No. Roll Buyer</th>
            <th>Lot</th>
            <th>Qty</th>
            <th>Unit</th>
        </tr>
        <tr>
            <td>{{ $sbItem->no_roll }}</td>
            <td>{{ $sbItem->no_roll_buyer }}</td>
            <td>{{ $sbItem->lot }}</td>
            <td>{{ $ndsItem ? ((($sbItem->unit == "YRD" || $sbItem->unit == "YARD") && $ndsItem == "METER") ?  $ndsItem->sisa_kain * 1.09361 : $ndsItem->sisa_kain) : $sbItem->qty }}</td>
            <td>{{ $sbItem->unit }}</td>
        </tr>
    </table>
    <table style="width: 50%;margin-bottom: 5px;">
        <tr>
            <td style="text-align: center;font-size: 20px; padding: 10px;">
                <span style="">
                    @php
                        echo  DNS1D::getBarcodeHTML($sbItem->id_roll, 'c39', 1.1, 50,'black', false);
                    @endphp
                </span>
                <span style="font-weight: bold; font-size: 15px;">{{ $sbItem->id_roll }}</span>
            </td>
        </tr>
    </table>
</body>
</html>
