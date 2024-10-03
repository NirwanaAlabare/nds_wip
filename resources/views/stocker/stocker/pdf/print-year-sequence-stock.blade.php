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
    <div style="border: 1px solid;padding: 5px;">
        <div class="clearfix" style="margin-bottom: 5px;">
            <div style="float: left;">
                <p>'{{ $stockerData->id_qr_stocker }}' Numbering Stock</p>
            </div>
        </div>
        <table style="margin-bottom: 5px;">
            <tr>
                <th style='border: none;border-left: 1px solid;border-top: 1px solid;border-bottom: 1px solid;'>No. WS</th>
                <td style='border: none;border-top: 1px solid;border-bottom: 1px solid;'>:</td>
                <td style='border: none;border-top: 1px solid;border-bottom: 1px solid;border-right: 1px solid;'>{{ $stockerData->act_costing_ws }}</td>

                <th style='border: none;border-left: 1px solid;border-top: 1px solid;border-bottom: 1px solid;'>No. Form</th>
                <td style='border: none;border-top: 1px solid;border-bottom: 1px solid;'>:</td>
                <td style='border: none;border-top: 1px solid;border-bottom: 1px solid;border-right: 1px solid;'>{{ $stockerData->formCut->no_form." / ".$stockerData->formCut->no_cut }}</td>
            </tr>
            <tr>
                <th style='border: none;border-left: 1px solid;border-top: 1px solid;border-bottom: 1px solid;'>Color</th>
                <td style='border: none;border-top: 1px solid;border-bottom: 1px solid;'>:</td>
                <td style='border: none;border-top: 1px solid;border-bottom: 1px solid;border-right: 1px solid;'>{{ $stockerData->color }}</td>

                <th style='border: none;border-left: 1px solid;border-top: 1px solid;border-bottom: 1px solid;'>Stocker Qty</th>
                <td style='border: none;border-top: 1px solid;border-bottom: 1px solid;'>:</td>
                <td style='border: none;border-top: 1px solid;border-bottom: 1px solid;border-right: 1px solid;'>{{ $stockerData->range_akhir - $stockerData->range_awal + 1  }}</td>
            </tr>
            <tr>
                <th style='border: none;border-left: 1px solid;border-top: 1px solid;border-bottom: 1px solid;'>Size</th>
                <td style='border: none;border-top: 1px solid;border-bottom: 1px solid;'>:</td>
                <td style='border: none;border-top: 1px solid;border-bottom: 1px solid;border-right: 1px solid;'>{{ $stockerData->size }}</td>

                <th style='border: none;border-left: 1px solid;border-top: 1px solid;border-bottom: 1px solid;'>Range Stocker</th>
                <td style='border: none;border-top: 1px solid;border-bottom: 1px solid;'>:</td>
                <td style='border: none;border-top: 1px solid;border-bottom: 1px solid;border-right: 1px solid;'>{{ $stockerData->range_awal. ' - ' .$stockerData->range_akhir }} </td>
            </tr>
            <tr>
                <th style='border: none;border-left: 1px solid;border-top: 1px solid;border-bottom: 1px solid;'>Part</th>
                <td style='border: none;border-top: 1px solid;border-bottom: 1px solid;'>:</td>
                <td style='border: none;border-top: 1px solid;border-bottom: 1px solid;border-right: 1px solid;'>{{ $stockerData->partDetail->masterPart->nama_part }}</td>

                <th style='border: none;border-left: 1px solid;border-top: 1px solid;border-bottom: 1px solid;'>Numbering Qty</th>
                <td style='border: none;border-top: 1px solid;border-bottom: 1px solid;'>:</td>
                <td style='border: none;border-top: 1px solid;border-bottom: 1px solid;border-right: 1px solid;'>{{ ($range_akhir - $range_awal + 1) }}</td>
            </tr>
            <tr>
                <th style='border: none;border-left: 1px solid;border-top: 1px solid;border-bottom: 1px solid;'>Shade</th>
                <td style='border: none;border-top: 1px solid;border-bottom: 1px solid;'>:</td>
                <td style='border: none;border-top: 1px solid;border-bottom: 1px solid;border-right: 1px solid;'>{{ $stockerData->shade }}</td>

                <th style='border: none;border-left: 1px solid;border-top: 1px solid;border-bottom: 1px solid;'>Range Numbering</th>
                <td style='border: none;border-top: 1px solid;border-bottom: 1px solid;'>:</td>
                <td style='border: none;border-top: 1px solid;border-bottom: 1px solid;border-right: 1px solid;'>{{ $range_awal. ' - ' .$range_akhir }}</td>
            </tr>
        </table>
        <div class="clearfix">
            <div style="float: right;">
                <p>{{ $stockerData->updated_at }}</p>
            </div>
        </div>
    </div>
</body>
