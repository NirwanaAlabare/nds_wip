<!DOCTYPE html>
<html>
<head>
    <title>Stocker</title>
    <style>
        @page { margin: 1px; }

        @font-face {
            font-family: 'Open Sans';
            font-style: normal;
            font-weight: bold;
            src: url({{ storage_path("OpenSans-Bold.ttf") }}) format('truetype');
        }

        body {
            margin: 1px;
            font-family: 'Open Sans', sans-serif;
            font-weight: bold;
        }

        * {
            font-size: 11px;
        }

        /* img {
            width: 60px;
        } */

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table td, table th {
            text-align: left;
            vertical-align: middle;
            padding: 1.5px 3px;
            border: 1px solid;
            width: auto;
        }

        table th {
            font-weight: 400;
        }

        table td {
            font-weight: bold;
        }

        .page-break {
            page-break-before: always;
        }

        .clearfix::after {
            content: "";
            clear: both;
            display: flex;
            align-items: flex-end;
        }
    </style>
</head>
<body>
    <div style="border: 1px solid;padding: 1px">
        <div class="clearfix" style="margin-bottom: 1px;">
            <div style="float: left;">
                <p>'{{ $stockerData->id_qr_stocker }}'</p>
            </div>
        </div>
        <table style="margin-bottom: 1px;">
            <tr>
                <th style='border: none;border-left: 1px solid;border-top: 1px solid;border-bottom: 1px solid;width: 55px; text-wrap: nowrap;'>Buyer</th>
                <td style='border: none;border-top: 1px solid;border-bottom: 1px solid;width:5px;'>:</td>
                <td style='border: none;border-top: 1px solid;border-bottom: 1px solid;border-right: 1px solid;' colspan="4">{{ $stockerData->formCut && $stockerData->formCut->marker ? strtoupper(substr($stockerData->formCut->marker->buyer, 0, 50)).(strlen($stockerData->formcut->marker->buyer) > 50 ? '...' : '') : ($stockerData->formReject ? strtoupper(substr($stockerData->formReject->buyer, 0, 50)).(strlen($stockerData->formReject->buyer) > 50 ? '...' : '') : ($stockerData->formPiece ? strtoupper(substr($stockerData->formPiece->buyer, 0, 50)).(strlen($stockerData->formPiece->buyer) > 50 ? '...' : '') : '-')) }}</td>
            </tr>
            <tr>
                <th style='border: none;border-left: 1px solid;border-top: 1px solid;border-bottom: 1px solid;width: 55px; text-wrap: nowrap;'>No. WS</th>
                <td style='border: none;border-top: 1px solid;border-bottom: 1px solid;width:5px;'>:</td>
                <td style='border: none;border-top: 1px solid;border-bottom: 1px solid;border-right: 1px solid;'>{{ $stockerData->act_costing_ws }}</td>

                <th style='border: none;border-left: 1px solid;border-top: 1px solid;border-bottom: 1px solid;width: 35px; text-wrap: nowrap;'>Style</th>
                <td style='border: none;border-top: 1px solid;border-bottom: 1px solid;width:5px;'>:</td>
                <td style='border: none;border-top: 1px solid;border-bottom: 1px solid;border-right: 1px solid;'>{{ $stockerData->formCut && $stockerData->formCut->marker ? strtoupper(substr($stockerData->formCut->marker->style, 0, 15)).(strlen($stockerData->formCut->marker->style) > 15 ? '...' : '') : ($stockerData->formReject ? strtoupper(substr($stockerData->formReject->style, 0, 15)).(strlen($stockerData->formReject->style) > 15 ? '...' : '') : ($stockerData->formPiece ? strtoupper(substr($stockerData->formPiece->style, 0, 15)).(strlen($stockerData->formPiece->style) > 15 ? '...' : '') : '-')) }}</td>
            </tr>
            <tr>
                <th style='border: none;border-left: 1px solid;border-top: 1px solid;border-bottom: 1px solid;width: 35px; text-wrap: nowrap;'>Color</th>
                <td style='border: none;border-top: 1px solid;border-bottom: 1px solid;width:5px;'>:</td>
                <td style='border: none;border-top: 1px solid;border-bottom: 1px solid;border-right: 1px solid;'>{{ strtoupper(substr($stockerData->color, 0, 11)).(strlen($stockerData->color) > 11 ? '...' : '') }}</td>

                <th style='border: none;border-left: 1px solid;border-top: 1px solid;border-bottom: 1px solid;width: 35px; text-wrap: nowrap;'>Qty</th>
                <td style='border: none;border-top: 1px solid;border-bottom: 1px solid;width:5px;'>:</td>
                <td style='border: none;border-top: 1px solid;border-bottom: 1px solid;border-right: 1px solid;'>{{ $range_akhir - $range_awal + 1 }}</td>
            </tr>
            <tr>
                <td style='border: none;border-top: 1px solid;border-bottom: 1px solid;border-left: 1px solid;border-right: 1px solid;{{ strlen($stockerData->size) > 5 ? "font-size: 15px;" : "font-size: 60px;" }}text-align: center;' colspan="3" rowspan="4">{{ strtoupper($stockerData->size) }}</td>>

                <th style='border: none;border-left: 1px solid;border-top: 1px solid;border-bottom: 1px solid;width: 35px; text-wrap: nowrap;'>Dest</th>
                <td style='border: none;border-top: 1px solid;border-bottom: 1px solid;width:5px;'>:</td>
                <td style='border: none;border-top: 1px solid;border-bottom: 1px solid;border-right: 1px solid;'>{{ $stockerData->masterSbWs->dest }}</td>
            </tr>
            <tr>
                <th style='border: none;border-left: 1px solid;border-top: 1px solid;border-bottom: 1px solid;width: 35px; text-wrap: nowrap;'>Seq</th>
                <td style='border: none;border-top: 1px solid;border-bottom: 1px solid;width:5px;'>:</td>
                <td style='border: none;border-top: 1px solid;border-bottom: 1px solid;border-right: 1px solid;'>{{ $year_sequence }}</td>
            </tr>
            <tr>
                <th style='border: none;border-left: 1px solid;border-top: 1px solid;border-bottom: 1px solid;width: 35px; text-wrap: nowrap;'>Range</th>
                <td style='border: none;border-top: 1px solid;border-bottom: 1px solid;width:5px;'>:</td>
                <td style='border: none;border-top: 1px solid;border-bottom: 1px solid;border-right: 1px solid;'>{{ $range_awal." - ".$range_akhir }}</td>
            </tr>
            <tr>
                <td style="text-align: center;" colspan="3">{{ $stockerData->updated_at }}</td>
            </tr>
        </table>
        {{-- <div class="clearfix">
            <div style="float: right;">
                <p>{{ $stockerData->updated_at }}</p>
            </div>
        </div> --}}
    </div>
</body>

