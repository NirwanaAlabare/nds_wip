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
    @php
        function setFontSize($str) {
            $strLen = strlen($str);
            $fontSize = '10px';
            switch ($strLen) {
                case $strLen > 40 && $strLen <= 50:
                    $fontSize = '9px';
                    break;
                case $strLen > 50 && $strLen <= 60:
                    $fontSize = '8px';
                    break;
                case $strLen > 60 && $strLen <= 70:
                    $fontSize = '7px';
                    break;
                case $strLen > 70 && $strLen <= 80:
                    $fontSize = '6px';
                    break;
                case $strLen > 80:
                    $fontSize = '5px';
                    break;
                default:
                    $fontSize = '10px';
            }

            return $fontSize;
        }
    @endphp
    @foreach ($dataStockers as $dataStocker)
        <table class="{{ $loop->index != 0 ? 'page-break' : '' }}">
            <tr>
                <td rowspan="3" style="vertical-align: middle; text-align: center; width: 35%;">
                    <img src="data:image/png;base64, {!! base64_encode(QrCode::format('svg')->size(100)->generate($dataStocker->id_qr_stocker)) !!}">
                </td>
                <td>Bundle Qty : {{ $dataStocker->bundle_qty }}</td>
            </tr>
            <tr>
                <td>Size : {{ $dataStocker->size }}</td>
            </tr>
            <tr>
                <td>Range : {{ $dataStocker->range_awal." - ".$dataStocker->range_akhir }}</td>
            </tr>
        </table>
        <table style="margin-top: -0.5px;">
            <tr>
                <td style="border: none;border-left: 0.75px solid; border-top: 0.75px solid; border-bottom: 0.75px solid;white-space: nowrap;">Kode Stocker</td>
                <td style="border: none;border-left: none; border-top: 0.75px solid; border-bottom: 0.75px solid;text-align: center;width: auto;">:</td>
                <td colspan="2" style="border: none;border-right: 0.75px solid; border-top: 0.75px solid; border-bottom: 0.75px solid;">{{ $dataStocker->id_qr_stocker }}</td>
                <td colspan="4" style="border: none; border-top: 0.75px solid; border-bottom: 0.75px solid; border-right: 0.75px solid;">{{ $dataStocker->proses }}</td>
            </tr>
            <tr>
                <td style="border: none;border-left: 0.75px solid; border-top: 0.75px solid; border-bottom: 0.75px solid;white-space: nowrap;">Worksheet</td>
                <td style="border: none;border-left: none; border-top: 0.75px solid; border-bottom: 0.75px solid;text-align: center;width: auto;">:</td>
                <td colspan="6" style="border: none;border-right: 0.75px solid; border-top: 0.75px solid; border-bottom: 0.75px solid;">{{ $dataStocker->act_costing_ws }}</td>
            </tr>
            <tr>
                <td style="border: none;border-left: 0.75px solid; border-top: 0.75px solid; border-bottom: 0.75px solid;white-space: nowrap;">Panel</td>
                <td style="border: none;border-left: none; border-top: 0.75px solid; border-bottom: 0.75px solid;text-align: center;width: auto;">:</td>
                <td colspan="6" style="border: none;border-right: 0.75px solid; border-top: 0.75px solid; border-bottom: 0.75px solid;{{ strlen($dataStocker->panel) > 40 ? "font-size: ".setFontSize($dataStocker->panel).";" : "" }}">{{ $dataStocker->panel }}</td>
            </tr>
            <tr>
                <td style="border: none;border-left: 0.75px solid; border-top: 0.75px solid; border-bottom: 0.75px solid;white-space: nowrap;">Buyer</td>
                <td style="border: none;border-left: none; border-top: 0.75px solid; border-bottom: 0.75px solid;text-align: center;width: auto;">:</td>
                <td colspan="6" style="border: none;border-right: 0.75px solid; border-top: 0.75px solid; border-bottom: 0.75px solid;">{{ strtoupper(substr($dataStocker->buyer, 0, 30)).(strlen($dataStocker->buyer) > 30 ? '...' : '') }}</td>
            </tr>
            <tr>
                <td style="border: none;border-left: 0.75px solid; border-top: 0.75px solid; border-bottom: 0.75px solid;white-space: nowrap;">Style</td>
                <td style="border: none;border-left: none; border-top: 0.75px solid; border-bottom: 0.75px solid;text-align: center;width: auto;">:</td>
                <td colspan="6" style="border: none;border-right: 0.75px solid; border-top: 0.75px solid; border-bottom: 0.75px solid;">{{ substr($dataStocker->style, 0, 45).(strlen($dataStocker->style) > 45 ? '...' : '') }}</td>
            </tr>
            <tr>
                <td style="border: none;border-left: 0.75px solid; border-top: 0.75px solid; border-bottom: 0.75px solid;white-space: nowrap;">Color</td>
                <td style="border: none;border-left: none; border-top: 0.75px solid; border-bottom: 0.75px solid;text-align: center;width: auto;">:</td>
                <td colspan="6" style="border: none;border-right: 0.75px solid; border-top: 0.75px solid; border-bottom: 0.75px solid;">{{ substr($dataStocker->color, 0, 30).(strlen($dataStocker->color) > 30 ? '...' : '') }}</td>
            </tr>
            <tr>
                <td style="border: none;border-left: 0.75px solid; border-top: 0.75px solid; border-bottom: 0.75px solid;white-space: nowrap;">Part</td>
                <td style="border: none;border-left: none; border-top: 0.75px solid; border-bottom: 0.75px solid;text-align: center;width: auto;">:</td>
                <td colspan="6" style="border: none;border-right: 0.75px solid; border-top: 0.75px solid; border-bottom: 0.75px solid;">{{ substr($dataStocker->part, 0, 30).(strlen($dataStocker->part) > 30 ? '...' : '') }}</td>
            </tr>
            <tr>
                <td style="border: none;border-left: 0.75px solid; border-top: 0.75px solid; border-bottom: 0.75px solid;white-space: nowrap;">Shade</td>
                <td style="border: none;border-left: none; border-top: 0.75px solid; border-bottom: 0.75px solid;text-align: center;width: auto;">:</td>
                <td colspan="3" style="border: none;border-right: 0.75px solid; border-top: 0.75px solid; border-bottom: 0.75px solid;">{{ $dataStocker->shade }}</td>
                <td style="border: none;border-left: 0.75px solid; border-top: 0.75px solid; border-bottom: 0.75px solid;white-space: nowrap;">No. Cut</td>
                <td style="border: none;border-left: none; border-top: 0.75px solid; border-bottom: 0.75px solid;text-align: center;width: auto;">:</td>
                <td style="border: none;border-right: 0.75px solid; border-top: 0.75px solid; border-bottom: 0.75px solid;">{{ $dataStocker->no_cut }}</td>
            </tr>
            <tr>
                <td style="border: none;border-left: 0.75px solid; border-top: 0.75px solid; border-bottom: 0.75px solid;white-space: nowrap;">Country</td>
                <td style="border: none;border-left: none; border-top: 0.75px solid; border-bottom: 0.75px solid;text-align: center;width: auto;">:</td>
                <td colspan="6" style="border: none;border-right: 0.75px solid; border-top: 0.75px solid; border-bottom: 0.75px solid;">{{ $dataStocker->dest }}</td>
            </tr>
            <tr>
                <td style="border: none;border-left: 0.75px solid; border-top: 0.75px solid; border-bottom: 0.75px solid;white-space: nowrap;">Note</td>
                <td style="border: none;border-left: none; border-top: 0.75px solid; border-bottom: 0.75px solid;text-align: center;width: auto;">:</td>
                <td colspan="6" style="border: none;border-right: 0.75px solid; border-top: 0.75px solid; border-bottom: 0.75px solid;{{ strlen($dataStocker->notes) > 30 ? 'font-size: 10px;' : '' }}">{{ substr($dataStocker->notes, 0, 40).(strlen($dataStocker->notes) > 40 ? '...' : '') }}</td>
            </tr>
            <tr>
                <td style="border: none; border-top: 0.75px solid; border-bottom: 0.75px solid; border-left: 0.75px solid; white-space: nowrap;">Reject Panel</td>
                <td style="border: none; border-top: 0.75px solid; border-bottom: 0.75px solid; text-align: center;width: auto;">:</td>
                <td colspan="2" style="border: none; border-top: 0.75px solid; border-bottom: 0.75px solid; border-right: 0.75px solid;">&nbsp;</td>
                <td style="border: none; border-top: 0.75px solid; border-bottom: 0.75px solid; border-left: 0.75px solid;white-space: nowrap;">Reject/Return Print</td>
                <td style="border: none; border-top: 0.75px solid; border-bottom: 0.75px solid; text-align: center;width: auto;">: </td>
                <td colspan="2" style="border: none; border-top: 0.75px solid; border-bottom: 0.75px solid; border-right: 0.75px solid;">&nbsp;</td>
            </tr>
            <tr>
                <td style="border: none; border-top: 0.75px solid; border-bottom: 0.75px solid; border-left: 0.75px solid; white-space: nowrap;">Reject Heatseal</td>
                <td style="border: none; border-top: 0.75px solid; border-bottom: 0.75px solid; text-align: center;width: auto;">:</td>
                <td colspan="2" style="border: none; border-top: 0.75px solid; border-bottom: 0.75px solid; border-right: 0.75px solid;">&nbsp;</td>
                <td style="border: none; border-top: 0.75px solid; border-bottom: 0.75px solid; border-left: 0.75px solid;white-space: nowrap;">Reject Embro</td>
                <td style="border: none; border-top: 0.75px solid; border-bottom: 0.75px solid; text-align: center;width: auto;">: </td>
                <td colspan="2" style="border: none; border-top: 0.75px solid; border-bottom: 0.75px solid; border-right: 0.75px solid;">&nbsp;</td>
            </tr>
            <tr>
                <td style="border: none;border-top: 0.75px solid; border-bottom: 0.75px solid; border-left: 0.75px solid; border-bottom: 0.75px solid;white-space: nowrap;">Qty Final Join</td>
                <td style="border: none;border-top: 0.75px solid; border-bottom: 0.75px solid; border-left: none; border-bottom: 0.75px solid;text-align: center;width: auto;">:</td>
                <td colspan="6" style="border: none;border-top: 0.75px solid; border-bottom: 0.75px solid; border-right: 0.75px solid; border-bottom: 0.75px solid;">&nbsp;</td>
            </tr>
        </table>
    @endforeach
</body>
</html>
