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
            width: 100px;
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
            <td style="border: none;border-left: 1px solid; border-top: 1px solid; border-bottom: 1px solid;white-space: nowrap;">Kode Stocker</td>
            <td style="border: none;border-left: none; border-top: 1px solid; border-bottom: 1px solid;text-align: center;"> : </td>
            <td style="border: none;border-right: 1px solid; border-top: 1px solid; border-bottom: 1px solid;">{{ $dataSpreading->id_qr_stocker }}</td>
        </tr>
        <tr>
            <td style="border: none;border-left: 1px solid; border-top: 1px solid; border-bottom: 1px solid;white-space: nowrap;">Worksheet</td>
            <td style="border: none;border-left: none; border-top: 1px solid; border-bottom: 1px solid;text-align: center;"> : </td>
            <td style="border: none;border-right: 1px solid; border-top: 1px solid; border-bottom: 1px solid;">{{ $dataSpreading->act_costing_ws }}</td>
        </tr>
        <tr>
            <td style="border: none;border-left: 1px solid; border-top: 1px solid; border-bottom: 1px solid;white-space: nowrap;">Buyer</td>
            <td style="border: none;border-left: none; border-top: 1px solid; border-bottom: 1px solid;text-align: center;"> : </td>
            <td style="border: none;border-right: 1px solid; border-top: 1px solid; border-bottom: 1px solid;">{{ $dataSpreading->buyer }}</td>
        </tr>
        <tr>
            <td style="border: none;border-left: 1px solid; border-top: 1px solid; border-bottom: 1px solid;white-space: nowrap;">Style</td>
            <td style="border: none;border-left: none; border-top: 1px solid; border-bottom: 1px solid;text-align: center;"> : </td>
            <td style="border: none;border-right: 1px solid; border-top: 1px solid; border-bottom: 1px solid;">{{ $dataSpreading->style }}</td>
        </tr>
        <tr>
            <td style="border: none;border-left: 1px solid; border-top: 1px solid; border-bottom: 1px solid;white-space: nowrap;">Color</td>
            <td style="border: none;border-left: none; border-top: 1px solid; border-bottom: 1px solid;text-align: center;"> : </td>
            <td style="border: none;border-right: 1px solid; border-top: 1px solid; border-bottom: 1px solid;">{{ $dataSpreading->color }}</td>
        </tr>
        <tr>
            <td style="border: none;border-left: 1px solid; border-top: 1px solid; border-bottom: 1px solid;white-space: nowrap;">Shade</td>
            <td style="border: none;border-left: none; border-top: 1px solid; border-bottom: 1px solid;text-align: center;"> : </td>
            <td style="border: none;border-right: 1px solid; border-top: 1px solid; border-bottom: 1px solid;">{{ $dataSpreading->shade }}</td>
        </tr>
    </table>
</body>
</html>
