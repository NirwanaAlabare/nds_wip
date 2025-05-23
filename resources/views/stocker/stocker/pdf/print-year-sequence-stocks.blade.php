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
    @foreach ($stockNumbers as $stockNumber)
        @if ($loop->index > 0)
            <div class="page-break"></div>
        @endif
        <div style="border: 1px solid;padding: 1px">
            <div class="clearfix" style="margin-bottom: 1px;">
                <div style="float: left;">
                    <p>'{{ $stockNumber['id_qr_stocker'] }}'</p>
                </div>
            </div>
            <table style="margin-bottom: 1px;">
                <tr>
                    <th style='border: none;border-left: 1px solid;border-top: 1px solid;border-bottom: 1px solid;'>Buyer</th>
                    <td style='border: none;border-top: 1px solid;border-bottom: 1px solid;width: auto;'>:</td>
                    <td style='border: none;border-top: 1px solid;border-bottom: 1px solid;border-right: 1px solid;' colspan="4">{{ strtoupper(substr($stockNumber['buyer'], 0, 50)).(strlen($stockNumber['buyer']) > 50 ? '...' : '') }}</td>
                </tr>
                <tr>
                    <th style='border: none;border-left: 1px solid;border-top: 1px solid;border-bottom: 1px solid;'>No. WS</th>
                    <td style='border: none;border-top: 1px solid;border-bottom: 1px solid;width: auto;'>:</td>
                    <td style='border: none;border-top: 1px solid;border-bottom: 1px solid;border-right: 1px solid;'>{{ $stockNumber['act_costing_ws'] }}</td>

                    <th style='border: none;border-left: 1px solid;border-top: 1px solid;border-bottom: 1px solid;'>Style</th>
                    <td style='border: none;border-top: 1px solid;border-bottom: 1px solid;width: auto;'>:</td>
                    <td style='border: none;border-top: 1px solid;border-bottom: 1px solid;border-right: 1px solid;'>{{ strtoupper(substr($stockNumber['style'], 0, 9)).(strlen($stockNumber['style']) > 9 ? '...' : '') }}</td>
                </tr>
                <tr>
                    <th style='border: none;border-left: 1px solid;border-top: 1px solid;border-bottom: 1px solid;'>Color</th>
                    <td style='border: none;border-top: 1px solid;border-bottom: 1px solid;width: auto;'>:</td>
                    <td style='border: none;border-top: 1px solid;border-bottom: 1px solid;border-right: 1px solid;'>{{ strtoupper(substr($stockNumber['color'], 0, 9)).(strlen($stockNumber['color']) > 9 ? '...' : '') }}</td>

                    <th style='border: none;border-left: 1px solid;border-top: 1px solid;border-bottom: 1px solid;'>Qty</th>
                    <td style='border: none;border-top: 1px solid;border-bottom: 1px solid;width: auto;'>:</td>
                    <td style='border: none;border-top: 1px solid;border-bottom: 1px solid;border-right: 1px solid;'>{{ $stockNumber['qty'] }}</td>
                </tr>
                <tr>
                    <td style='border: none;border-top: 1px solid;border-bottom: 1px solid;border-left: 1px solid;border-right: 1px solid;{{ strlen($stockNumber["size"]) > 5 ? "font-size: 15px;" : "font-size: 60px;" }}text-align: center;' colspan="3" rowspan="3">{{ strtoupper($stockNumber['size']) }}</td>>

                    <th style='border: none;border-left: 1px solid;border-top: 1px solid;border-bottom: 1px solid;'>Dest</th>
                    <td style='border: none;border-top: 1px solid;border-bottom: 1px solid;width: auto;'>:</td>
                    <td style='border: none;border-top: 1px solid;border-bottom: 1px solid;border-right: 1px solid;'>{{ $stockNumber['dest'] }}</td>
                </tr>
                <tr>
                    <th style='border: none;border-left: 1px solid;border-top: 1px solid;border-bottom: 1px solid;'>Range QR</th>
                    <td style='border: none;border-top: 1px solid;border-bottom: 1px solid;width: auto;'>:</td>
                    <td style='border: none;border-top: 1px solid;border-bottom: 1px solid;border-right: 1px solid;'>{{ $stockNumber['numbering_range'] }}</td>
                </tr>
                <tr>
                    <td style="text-align: center;" colspan="3"><p>{{ $stockNumber['updated_at'] }}</p></td>
                </tr>
            </table>
            {{-- <div class="clearfix">
                <div style="float: right;">
                    <p>{{ $stockNumber['updated_at'] }}</p>
                </div>
            </div> --}}
        </div>
    @endforeach
</body>
