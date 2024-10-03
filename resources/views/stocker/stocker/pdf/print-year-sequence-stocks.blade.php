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
        <div style="border: 1px solid;padding: 5px">
            <div class="clearfix">
                <div style="float: left;">
                    <p>'{{ $stockNumber['id_qr_stocker'] }}' Numbering Stock</p>
                </div>
                <div style="float: right;">
                    <p>{{ $stockNumber['updated_at'] }}</p>
                </div>
            </div>
            <table style="margin-bottom: 30px;">
                <tr>
                    <th style='border: none;border-left: 1px solid;border-top: 1px solid;border-bottom: 1px solid;'>No. WS</th>
                    <td style='border: none;border-top: 1px solid;border-bottom: 1px solid;'>:</td>
                    <td style='border: none;border-top: 1px solid;border-bottom: 1px solid;border-right: 1px solid;'>{{ $stockNumber['act_costing_ws'] }}</td>

                    <th style='border: none;border-left: 1px solid;border-top: 1px solid;border-bottom: 1px solid;'>No. Form</th>
                    <td style='border: none;border-top: 1px solid;border-bottom: 1px solid;'>:</td>
                    <td style='border: none;border-top: 1px solid;border-bottom: 1px solid;border-right: 1px solid;'>{{ $stockNumber['no_form']." / ".$stockNumber['no_cut'] }}</td>
                </tr>
                <tr>
                    <th style='border: none;border-left: 1px solid;border-top: 1px solid;border-bottom: 1px solid;'>Color</th>
                    <td style='border: none;border-top: 1px solid;border-bottom: 1px solid;'>:</td>
                    <td style='border: none;border-top: 1px solid;border-bottom: 1px solid;border-right: 1px solid;'>{{ $stockNumber['color'] }}</td>

                    <th style='border: none;border-left: 1px solid;border-top: 1px solid;border-bottom: 1px solid;'>Stocker Qty</th>
                    <td style='border: none;border-top: 1px solid;border-bottom: 1px solid;'>:</td>
                    <td style='border: none;border-top: 1px solid;border-bottom: 1px solid;border-right: 1px solid;'>{{ $stockNumber['qty_stocker']  }}</td>
                </tr>
                <tr>
                    <th style='border: none;border-left: 1px solid;border-top: 1px solid;border-bottom: 1px solid;'>Size</th>
                    <td style='border: none;border-top: 1px solid;border-bottom: 1px solid;'>:</td>
                    <td style='border: none;border-top: 1px solid;border-bottom: 1px solid;border-right: 1px solid;'>{{ $stockNumber['size'] }}</td>

                    <th style='border: none;border-left: 1px solid;border-top: 1px solid;border-bottom: 1px solid;'>Range Stocker</th>
                    <td style='border: none;border-top: 1px solid;border-bottom: 1px solid;'>:</td>
                    <td style='border: none;border-top: 1px solid;border-bottom: 1px solid;border-right: 1px solid;'>{{ $stockNumber['stocker_range'] }} </td>
                </tr>
                <tr>
                    <th style='border: none;border-left: 1px solid;border-top: 1px solid;border-bottom: 1px solid;'>Part</th>
                    <td style='border: none;border-top: 1px solid;border-bottom: 1px solid;'>:</td>
                    <td style='border: none;border-top: 1px solid;border-bottom: 1px solid;border-right: 1px solid;'>
                        @php
                            foreach (explode(',', $stockNumber['part']) as $key => $value) {
                                echo $value."<br>";
                            }
                        @endphp
                    </td>

                    <th style='border: none;border-left: 1px solid;border-top: 1px solid;border-bottom: 1px solid;'>Numbering Qty</th>
                    <td style='border: none;border-top: 1px solid;border-bottom: 1px solid;'>:</td>
                    <td style='border: none;border-top: 1px solid;border-bottom: 1px solid;border-right: 1px solid;'>{{ $stockNumber['qty'] }}</td>
                </tr>
                <tr>
                    <th style='border: none;border-left: 1px solid;border-top: 1px solid;border-bottom: 1px solid;'>Shade</th>
                    <td style='border: none;border-top: 1px solid;border-bottom: 1px solid;'>:</td>
                    <td style='border: none;border-top: 1px solid;border-bottom: 1px solid;border-right: 1px solid;'>{{ $stockNumber['shade'] }}</td>

                    <th style='border: none;border-left: 1px solid;border-top: 1px solid;border-bottom: 1px solid;'>Range Numbering</th>
                    <td style='border: none;border-top: 1px solid;border-bottom: 1px solid;'>:</td>
                    <td style='border: none;border-top: 1px solid;border-bottom: 1px solid;border-right: 1px solid;'>{{ $stockNumber['numbering_range'] }}</td>
                </tr>
            </table>
            <div class="clearfix">
                <div style="float: right;">
                    <p>{{ $stockerData->updated_at }}</p>
                </div>
            </div>
        </div>
    @endforeach
</body>
