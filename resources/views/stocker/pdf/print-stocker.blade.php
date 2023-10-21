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
            margin: 5px;
            font-family: 'Open Sans', sans-serif;
            font-weight: bold;
        }

        * {
            font-size: 12px;
        }

        img {
            width: 95px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table td, table th{
            text-align: left;
            vertical-align: middle;
            padding: 1.5px 3px;
            border: 1px solid;
            width: auto;
        }
    </style>
</head>
<body>
    <table>
        <tr>
            <td rowspan="3" style="vertical-align: middle; text-align: center; width: 35%;">
                <img src="data:image/png;base64, {!! $qrCode !!}">
            </td>
            <td>Bundle Qty : {{ $dataSpreading->bundle_qty }}</td>
        </tr>
        <tr>
            <td>Size : {{ $dataSpreading->size }}</td>
        </tr>
        <tr>
            <td>Range : {{ $dataSpreading->range_awal." - ".$dataSpreading->range_akhir }}</td>
        </tr>
    </table>
    <table style="margin-top: -0.5px;">
        <tr>
            <td colspan="3" style="text-align: center;">Deskripsi Item</td>
        </tr>
        <tr>
            <td style="border: none;border-left: 1px solid; border-top: 1px solid; border-bottom: 1px solid;white-space: nowrap;">Kode Stocker</td>
            <td style="border: none;border-left: none; border-top: 1px solid; border-bottom: 1px solid;text-align: center;width: auto;">:</td>
            <td style="border: none;border-right: 1px solid; border-top: 1px solid; border-bottom: 1px solid;">{{ $dataSpreading->id_qr_stocker }}</td>
        </tr>
        <tr>
            <td style="border: none;border-left: 1px solid; border-top: 1px solid; border-bottom: 1px solid;white-space: nowrap;">Worksheet</td>
            <td style="border: none;border-left: none; border-top: 1px solid; border-bottom: 1px solid;text-align: center;width: auto;">:</td>
            <td style="border: none;border-right: 1px solid; border-top: 1px solid; border-bottom: 1px solid;">{{ $dataSpreading->act_costing_ws }}</td>
        </tr>
        <tr>
            <td style="border: none;border-left: 1px solid; border-top: 1px solid; border-bottom: 1px solid;white-space: nowrap;">Buyer</td>
            <td style="border: none;border-left: none; border-top: 1px solid; border-bottom: 1px solid;text-align: center;width: auto;">:</td>
            <td style="border: none;border-right: 1px solid; border-top: 1px solid; border-bottom: 1px solid;">{{ $dataSpreading->buyer }}</td>
        </tr>
        <tr>
            <td style="border: none;border-left: 1px solid; border-top: 1px solid; border-bottom: 1px solid;white-space: nowrap;">Style</td>
            <td style="border: none;border-left: none; border-top: 1px solid; border-bottom: 1px solid;text-align: center;width: auto;">:</td>
            <td style="border: none;border-right: 1px solid; border-top: 1px solid; border-bottom: 1px solid;">{{ $dataSpreading->style }}</td>
        </tr>
        <tr>
            <td style="border: none;border-left: 1px solid; border-top: 1px solid; border-bottom: 1px solid;white-space: nowrap;">Color</td>
            <td style="border: none;border-left: none; border-top: 1px solid; border-bottom: 1px solid;text-align: center;width: auto;">:</td>
            <td style="border: none;border-right: 1px solid; border-top: 1px solid; border-bottom: 1px solid;">{{ $dataSpreading->color }}</td>
        </tr>
        <tr>
            <td style="border: none;border-left: 1px solid; border-top: 1px solid; border-bottom: 1px solid;white-space: nowrap;">Shade</td>
            <td style="border: none;border-left: none; border-top: 1px solid; border-bottom: 1px solid;text-align: center;width: auto;">:</td>
            <td style="border: none;border-right: 1px solid; border-top: 1px solid; border-bottom: 1px solid;">{{ $dataSpreading->shade }}</td>
        </tr>
    </table>
</body>
</html>
