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
            <td rowspan="3">
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
            <td colspan="3">Deskripsi Item</td>
        </tr>
        <tr>
            <td>Kode Stocker</td>
            <td>:</td>
            <td>{{ $dataSpreading->id_qr_stocker }}</td>
        </tr>
        <tr>
            <td>Worksheet</td>
            <td>:</td>
            <td>{{ $dataSpreading->act_costing_ws }}</td>
        </tr>
        <tr>
            <td>Buyer</td>
            <td>:</td>
            <td>{{ $dataSpreading->buyer }}</td>
        </tr>
        <tr>
            <td>Style</td>
            <td>:</td>
            <td>{{ $dataSpreading->style }}</td>
        </tr>
        <tr>
            <td>Color</td>
            <td>:</td>
            <td>{{ $dataSpreading->color }}</td>
        </tr>
        <tr>
            <td>Shade</td>
            <td>:</td>
            <td>{{ $dataSpreading->shade }}</td>
        </tr>
    </table>
</body>
</html>
