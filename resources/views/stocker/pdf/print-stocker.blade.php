<!DOCTYPE html>
<html>
<head>
    <title>Stocker</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }

        table td, table th{
            text-align: left;
            vertical-align: top;
            padding: 10px;
            border: 1px solid;
            width: 100%;
        }
    </style>
</head>
<body>
    <table>
        <tr>
            <td rowspan="3" style="vertical-align: middle; text-align: center;">
                <img src="data:image/png;base64, {!! $qrCode !!}">
            </td>
            <td colspan="3">Bundle Qty : {{ $dataSpreading->bundle_qty }}</td>
        </tr>
        <tr>
            <td colspan="3">Size : {{ $dataSpreading->size }}</td>
        </tr>
        <tr>
            <td colspan="3">Range : {{ $dataSpreading->range_awal." - ".$dataSpreading->range_akhir }}</td>
        </tr>
        <tr>
            <td colspan="4" style="text-align: center;">Deskripsi Item</td>
        </tr>
        <tr>
            <td colspan="2">Kode Stocker</td>
            <td style="text-align: center;border: none;border-top: 1px solid;border-bottom: 1px solid;">:</td>
            <td>{{ $dataSpreading->id_qr_stocker }}</td>
        </tr>
        <tr>
            <td colspan="2">Worksheet</td>
            <td style="text-align: center;border: none;border-top: 1px solid;border-bottom: 1px solid;">:</td>
            <td>{{ $dataSpreading->act_costing_ws }}</td>
        </tr>
        <tr>
            <td colspan="2">Buyer</td>
            <td style="text-align: center;border: none;border-top: 1px solid;border-bottom: 1px solid;">:</td>
            <td>{{ $dataSpreading->buyer }}</td>
        </tr>
        <tr>
            <td colspan="2">Style</td>
            <td style="text-align: center;border: none;border-top: 1px solid;border-bottom: 1px solid;">:</td>
            <td>{{ $dataSpreading->style }}</td>
        </tr>
        <tr>
            <td colspan="2">Color</td>
            <td style="text-align: center;border: none;border-top: 1px solid;border-bottom: 1px solid;">:</td>
            <td>{{ $dataSpreading->color }}</td>
        </tr>
        <tr>
            <td colspan="2">Shade</td>
            <td style="text-align: center;border: none;border-top: 1px solid;border-bottom: 1px solid;">:</td>
            <td>{{ $dataSpreading->shade }}</td>
        </tr>
    </table>
</body>
</html>
