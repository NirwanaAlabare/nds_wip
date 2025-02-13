<!DOCTYPE html>
<html>
<head>
    <title>Stocker</title>
    <style>
        @page { margin: 5px; }

        body { margin: 5px; }

        * {
            font-size: 13px;
        }

        img {
            width: 69px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table td, table th{
            text-align: left;
            vertical-align: middle;
            padding: 1px 3px;
            border: 1px solid;
            width: auto;
        }
    </style>
</head>
<body>
    <table>
        <tr>
            <td rowspan="3" style="vertical-align: middle; text-align: center;">
                <img src="data:image/png;base64, {!! $qrCode !!}">
            </td>
            <td colspan="2">Bundle Qty : {{ $dataSpreading->bundle_qty }}</td>
        </tr>
        <tr>
            <td colspan="2">Size : {{ $dataSpreading->size }}</td>
        </tr>
        <tr>
            <td colspan="2">Range : {{ $dataSpreading->range_awal." - ".$dataSpreading->range_akhir }}</td>
        </tr>
        <tr>
            <td colspan="3" style="text-align: center;">Deskripsi Item</td>
        </tr>
        <tr>
            <td style="border: none;border-left: 1px solid; border-top: 1px solid; border-bottom: 1px solid;" colspan="2">Kode Stocker</td>
            <td style="border: none;border-right: 1px solid; border-top: 1px solid; border-bottom: 1px solid;">&nbsp;&nbsp;&nbsp;:&nbsp;&nbsp;&nbsp;{{ $dataSpreading->id_qr_stocker }}</td>
        </tr>
        <tr>
            <td style="border: none;border-left: 1px solid; border-top: 1px solid; border-bottom: 1px solid;" colspan="2">Worksheet</td>
            <td style="border: none;border-right: 1px solid; border-top: 1px solid; border-bottom: 1px solid;">&nbsp;&nbsp;&nbsp;:&nbsp;&nbsp;&nbsp;{{ $dataSpreading->act_costing_ws }}</td>
        </tr>
        <tr>
            <td style="border: none;border-left: 1px solid; border-top: 1px solid; border-bottom: 1px solid;" colspan="2">Buyer</td>
            <td style="border: none;border-right: 1px solid; border-top: 1px solid; border-bottom: 1px solid;">&nbsp;&nbsp;&nbsp;:&nbsp;&nbsp;&nbsp;{{ $dataSpreading->buyer }}</td>
        </tr>
        <tr>
            <td style="border: none;border-left: 1px solid; border-top: 1px solid; border-bottom: 1px solid;" colspan="2">Style</td>
            <td style="border: none;border-right: 1px solid; border-top: 1px solid; border-bottom: 1px solid;">&nbsp;&nbsp;&nbsp;:&nbsp;&nbsp;&nbsp;{{ $dataSpreading->style }}</td>
        </tr>
        <tr>
            <td style="border: none;border-left: 1px solid; border-top: 1px solid; border-bottom: 1px solid;" colspan="2">Color</td>
            <td style="border: none;border-right: 1px solid; border-top: 1px solid; border-bottom: 1px solid;">&nbsp;&nbsp;&nbsp;:&nbsp;&nbsp;&nbsp;{{ $dataSpreading->color }}</td>
        </tr>
        <tr>
            <td style="border: none;border-left: 1px solid; border-top: 1px solid; border-bottom: 1px solid;" colspan="2">Shade</td>
            <td style="border: none;border-right: 1px solid; border-top: 1px solid; border-bottom: 1px solid;">&nbsp;&nbsp;&nbsp;:&nbsp;&nbsp;&nbsp;{{ $dataSpreading->shade }}</td>
        </tr>
    </table>
</body>
</html>
