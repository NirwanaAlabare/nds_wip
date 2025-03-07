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
            margin: 3px;
            font-family: 'Open Sans', sans-serif;
            font-weight: bold;
        }

        * {
            font-size: 10px;
        }

        img {
            width: 55px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table td, table th{
            text-align: left;
            vertical-align: middle;
            padding: 1.1px 2px;
            border: 0.5px solid;
            width: auto;
        }

        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    @foreach ($stockers as $stocker)
        <table class="{{ $loop->index != 0 ? 'page-break' : '' }}">
            <tr>
                <td rowspan="3" style="vertical-align: middle; text-align: center; width: 35%;">
                    <img src="data:image/png;base64, {!! base64_encode(QrCode::format('svg')->size(100)->generate($stocker->id_qr_stocker)) !!}">
                </td>
                <td>Bundle Qty : {{ $stocker->bundle_qty }}</td>
            </tr>
            <tr>
                <td>Size : {{ $stocker->size }}</td>
            </tr>
            <tr>
                <td>Range : {{ $stocker->range_awal." - ".$stocker->range_akhir }}</td>
            </tr>
        </table>
        <table style="margin-top: -0.5px;">
            <tr>
                <td colspan="8" style="text-align: center;">STOCKER REJECT</td>
            </tr>
            <tr>
                <td colspan="8" style="text-align: center;">Deskripsi Item</td>
            </tr>
            <tr>
                <td style="border: none;border-left: 0.75px solid; border-top: 0.75px solid; border-bottom: 0.75px solid;white-space: nowrap;">Kode Stocker</td>
                <td style="border: none;border-left: none; border-top: 0.75px solid; border-bottom: 0.75px solid;text-align: center;width: auto;">:</td>
                <td colspan="6" style="border: none;border-right: 0.75px solid; border-top: 0.75px solid; border-bottom: 0.75px solid;">{{ $stocker->id_qr_stocker }}</td>
            </tr>
            <tr>
                <td style="border: none;border-left: 0.75px solid; border-top: 0.75px solid; border-bottom: 0.75px solid;white-space: nowrap;">No. Form Reject</td>
                <td style="border: none;border-left: none; border-top: 0.75px solid; border-bottom: 0.75px solid;text-align: center;width: auto;">:</td>
                <td colspan="6" style="border: none;border-right: 0.75px solid; border-top: 0.75px solid; border-bottom: 0.75px solid;">{{ $stocker->no_form }}</td>
            </tr>
            <tr>
                <td style="border: none;border-left: 0.75px solid; border-top: 0.75px solid; border-bottom: 0.75px solid;white-space: nowrap;">Worksheet</td>
                <td style="border: none;border-left: none; border-top: 0.75px solid; border-bottom: 0.75px solid;text-align: center;width: auto;">:</td>
                <td colspan="6" style="border: none;border-right: 0.75px solid; border-top: 0.75px solid; border-bottom: 0.75px solid;">{{ $stocker->act_costing_ws }}</td>
            </tr>
            <tr>
                <td style="border: none;border-left: 0.75px solid; border-top: 0.75px solid; border-bottom: 0.75px solid;white-space: nowrap;">Buyer</td>
                <td style="border: none;border-left: none; border-top: 0.75px solid; border-bottom: 0.75px solid;text-align: center;width: auto;">:</td>
                <td colspan="6" style="border: none;border-right: 0.75px solid; border-top: 0.75px solid; border-bottom: 0.75px solid;">{{ strtoupper(substr($stocker->buyer, 0, 30)).(strlen($stocker->buyer) > 30 ? '...' : '') }}</td>
            </tr>
            <tr>
                <td style="border: none;border-left: 0.75px solid; border-top: 0.75px solid; border-bottom: 0.75px solid;white-space: nowrap;">Style</td>
                <td style="border: none;border-left: none; border-top: 0.75px solid; border-bottom: 0.75px solid;text-align: center;width: auto;">:</td>
                <td colspan="6" style="border: none;border-right: 0.75px solid; border-top: 0.75px solid; border-bottom: 0.75px solid;">{{ substr($stocker->style, 0, 30).(strlen($stocker->style) > 30 ? '...' : '') }}</td>
            </tr>
            <tr>
                <td style="border: none;border-left: 0.75px solid; border-top: 0.75px solid; border-bottom: 0.75px solid;white-space: nowrap;">Color</td>
                <td style="border: none;border-left: none; border-top: 0.75px solid; border-bottom: 0.75px solid;text-align: center;width: auto;">:</td>
                <td colspan="6" style="border: none;border-right: 0.75px solid; border-top: 0.75px solid; border-bottom: 0.75px solid;">{{ substr($stocker->color, 0, 30).(strlen($stocker->color) > 30 ? '...' : '') }}</td>
            </tr>
            <tr>
                <td style="border: none;border-left: 0.75px solid; border-top: 0.75px solid; border-bottom: 0.75px solid;white-space: nowrap;">Part</td>
                <td style="border: none;border-left: none; border-top: 0.75px solid; border-bottom: 0.75px solid;text-align: center;width: auto;">:</td>
                <td colspan="6" style="border: none;border-right: 0.75px solid; border-top: 0.75px solid; border-bottom: 0.75px solid;">{{ substr($stocker->part, 0, 30).(strlen($stocker->part) > 30 ? '...' : '') }}</td>
            </tr>
            <tr>
                <td style="border: none;border-left: 0.75px solid; border-top: 0.75px solid; border-bottom: 0.75px solid;white-space: nowrap;">Shade</td>
                <td style="border: none;border-left: none; border-top: 0.75px solid; border-bottom: 0.75px solid;text-align: center;width: auto;">:</td>
                <td colspan="6" style="border: none;border-right: 0.75px solid; border-top: 0.75px solid; border-bottom: 0.75px solid;">{{ $stocker->shade }}</td>
            </tr>
            <tr>
                <td style="border: none;border-left: 0.75px solid; border-top: 0.75px solid; border-bottom: 0.75px solid;white-space: nowrap;">Country</td>
                <td style="border: none;border-left: none; border-top: 0.75px solid; border-bottom: 0.75px solid;text-align: center;width: auto;">:</td>
                <td colspan="6" style="border: none;border-right: 0.75px solid; border-top: 0.75px solid; border-bottom: 0.75px solid;">{{ $stocker->dest }}</td>
            </tr>
            <tr>
                <td style="border: none;border-left: 0.75px solid; border-top: 0.75px solid; border-bottom: 0.75px solid;white-space: nowrap;">Note</td>
                <td style="border: none;border-left: none; border-top: 0.75px solid; border-bottom: 0.75px solid;text-align: center;width: auto;">:</td>
                <td colspan="6" style="border: none;border-right: 0.75px solid; border-top: 0.75px solid; border-bottom: 0.75px solid;{{ strlen($stocker->notes) > 30 ? 'font-size: 10px;' : '' }}">{{ substr($stocker->notes, 0, 40).(strlen($stocker->notes) > 40 ? '...' : '') }}</td>
            </tr>
        </table>
    @endforeach
</body>
</html>
