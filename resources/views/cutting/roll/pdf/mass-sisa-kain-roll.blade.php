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
            margin: 0.0px;
            font-family: 'Open Sans', sans-serif;
            font-weight: bold;
        }

        * {
            font-size: 9px;
        }

        img {
            width: 40px;

        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table td, table th{
            text-align: left;
            vertical-align: middle;
            padding: 0.0px;
            border: 1px solid;
        }

        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    @php
        $i = 0;
    @endphp
    @foreach ($sbItems as $sbItem)
        @php
            $i++;
            $ndsItem = $ndsItems->where("id_roll", $sbItem->id_roll)->first();
        @endphp
        <div style="border: 1px solid; padding: 5px;">
            <table style="width: 100%;margin-bottom: 5px;">
                <tr>
                    <th style="border: none;vertical-align: middle;">Product</th>
                    <td style="border: none;vertical-align: middle;padding: 2px;padding-left: 5px;padding-right: 5px;"> : </td>
                    <th style="border: none;vertical-align: middle;">{{ $sbItem->detail_item }}</th>
                </tr>
                <tr>
                    <th style="border: none;vertical-align: middle;">Kode&nbsp;Barang</th>
                    <td style="border: none;vertical-align: middle;padding: 2px;padding-left: 5px;padding-right: 5px;"> : </td>
                    <th style="border: none;vertical-align: middle;">{{ $sbItem->goods_code }}</th>
                </tr>
                <tr>
                    <th style="border: none;vertical-align: middle;">ID Item</th>
                    <td style="border: none;vertical-align: middle;padding: 2px;padding-left: 5px;padding-right: 5px;"> : </td>
                    <th style="border: none;vertical-align: middle;">{{ $sbItem->id_item }}</th>
                </tr>
                <tr>
                    <th style="border: none;vertical-align: middle;">No. BPB</th>
                    <td style="border: none;vertical-align: middle;padding: 2px;padding-left: 5px;padding-right: 5px;"> : </td>
                    <th style="border: none;vertical-align: middle;">{{ $sbItem->bppb }}</th>
                </tr>
                <tr>
                    <th style="border: none;vertical-align: middle;">No. Req</th>
                    <td style="border: none;vertical-align: middle;padding: 2px;padding-left: 5px;padding-right: 5px;"> : </td>
                    <th style="border: none;vertical-align: middle;">{{ $sbItem->no_req }}</th>
                </tr>
                <tr>
                    <th style="border: none;vertical-align: middle;">No. WS</th>
                    <td style="border: none;vertical-align: middle;padding: 2px;padding-left: 5px;padding-right: 5px;"> : </td>
                    <th style="border: none;vertical-align: middle;">{{ strtoupper($sbItem->no_ws) }}</th>
                </tr>
                <tr>
                    <th style="border: none;vertical-align: middle;">Style</th>
                    <td style="border: none;vertical-align: middle;padding: 2px;padding-left: 5px;padding-right: 5px;"> : </td>
                    <th style="border: none;vertical-align: middle;">{{ $sbItem->style }}</th>
                </tr>
            </table>
            <table style="width: 100%;margin-bottom: 5px;">
                <tr>
                    <th style="padding-left: 1px;font-size: 7.5px;">Group</th>
                    <th style="padding-left: 1px;font-size: 7.5px;">No. Roll</th>
                    <th style="padding-left: 1px;font-size: 7.5px;">No. Roll Buyer</th>
                    <th style="padding-left: 1px;font-size: 7.5px;">Lot</th>
                    <th style="padding-left: 1px;font-size: 7.5px;">Qty Awal</th>
                    <th style="padding-left: 1px;font-size: 7.5px;">Qty Sisa</th>
                    <th style="padding-left: 1px;font-size: 7.5px;">Unit</th>
                    @if ($sbItem->unit == "YRD" || $sbItem->unit == "YARD")
                        <th style="padding-left: 1px;font-size: 7.5px;">Konv.Awal</th>
                        <th style="padding-left: 1px;font-size: 7.5px;">Konv.Sisa</th>
                        <th style="padding-left: 1px;font-size: 7.5px;">Konv.Unit</th>
                    @endif
                </tr>
                <tr>
                    <td style="padding-left: 1px;font-size: 7.5px;">{{ $ndsItem ? $ndsItem->group_roll : "-" }}</td>
                    <td style="padding-left: 1px;font-size: 7.5px;">{{ $sbItem->no_roll }}</td>
                    <td style="padding-left: 1px;font-size: 7.5px;">{{ $sbItem->no_roll_buyer }}</td>
                    <td style="padding-left: 1px;font-size: 7.5px;">{{ $sbItem->lot }}</td>
                    <td style="padding-left: 1px;font-size: 7.5px;">{{ $sbItem->qty }}</td>
                    <td style="padding-left: 1px;font-size: 7.5px;">{{ $ndsItem ? ((($sbItem->unit == "YRD" || $sbItem->unit == "YARD") && $ndsItem->unit == "METER") ?  round($ndsItem->sisa_kain * 1.09361, 2) : $ndsItem->sisa_kain) : $sbItem->qty }}</td>
                    <td style="padding-left: 1px;font-size: 7.5px;">{{ $sbItem->unit }}</td>
                    @if ($sbItem->unit == "YRD" || $sbItem->unit == "YARD")
                        <td style="padding-left: 1px;font-size: 7.5px;">{{ ($sbItem->unit == "YRD" || $sbItem->unit == "YARD" ? round($sbItem->qty * 0.9144, 2) : $sbItem->qty) }}</td>
                        <td style="padding-left: 1px;font-size: 7.5px;">{{ $ndsItem ? ($ndsItem->unit == "YRD" || $ndsItem->unit == "YARD" ? round($ndsItem->sisa_kain * 0.9144, 2) : $ndsItem->sisa_kain) : ($sbItem->unit == "YRD" || $sbItem->unit == "YARD" ? round($sbItem->qty * 0.9144, 2) : $sbItem->qty) }}</td>
                        <td style="padding-left: 1px;font-size: 7.5px;">{{ $ndsItem ? ($ndsItem->unit == "YRD" || $ndsItem->unit == "YARD" ? "METER" : $ndsItem->unit) : ($sbItem->unit == "YRD" || $sbItem->unit == "YARD" ? "METER" : $sbItem->unit) }}</td>
                    @endif
                </tr>
            </table>
            <table style="margin-bottom: 5px;">
                @php
                    $forms = explode('^', ($ndsItem ? $ndsItem->no_form : '-'));
                @endphp
                <tr>
                    <td style="text-align: center;padding-top: 3px; padding-bottom: 3px; width: auto;" rowspan="{{ count($forms) > 0 ? count($forms) : 1 }}">
                        <img src="data:image/png;base64, {!! DNS1D::getBarcodePNG($sbItem->id_roll, 'c39', 2, 70) !!}" style="width: 150px; padding-bottom: 3px;">
                        <br>
                        <span style="font-weight: bold; font-size: 15px;">{{ $sbItem->id_roll }}</span>
                    </td>
                    @if (count($forms) > 0)
                        <th style="width: auto;border: none;border-left: 1px solid;border-top:1px solid;border-bottom:1px solid;vertical-align: middle;padding-left: 5px;padding-bottom: 0px;margin-bottom: 0px;">No. Form</th>
                        <td style="width: auto;border: none;border-top:1px solid;border-bottom:1px solid;vertical-align: middle;padding-left: 5px;padding-right: 5px;padding-bottom: 0px;margin-bottom: 0px;"> : </td>
                        <th style="width: auto;border: none;border-right: 1px solid;border-top:1px solid;border-bottom:1px solid;vertical-align: middle;padding-left: 5px;padding-bottom: 0px;margin-bottom: 0px;">{{ $forms[0] }}</th>
                    @else
                        <th style="width: auto;border: none;border-left: 1px solid;border-top:1px solid;border-bottom:1px solid;vertical-align: middle;padding-left: 5px;padding-bottom: 0px;margin-bottom: 0px;">No. Form</th>
                        <td style="width: auto;border: none;border-top:1px solid;border-bottom:1px solid;vertical-align: middle;padding-left: 5px;padding-right: 5px;padding-bottom: 0px;margin-bottom: 0px;"> : </td>
                        <th style="width: auto;border: none;border-right: 1px solid;border-top:1px solid;border-bottom:1px solid;vertical-align: middle;padding-left: 5px;padding-bottom: 0px;margin-bottom: 0px;">-</th>
                    @endif
                </tr>
                @if (count($forms) > 1)
                    @foreach ($forms as $form)
                        @if ($loop->index > 0)
                            <tr>
                                <th style="width: auto;border: none;border-left: 1px solid;border-top:1px solid;border-bottom:1px solid;vertical-align: middle;padding-left: 5px;padding-bottom: 0px;margin-bottom: 0px;">No. Form</th>
                                <td style="width: auto;border: none;border-top:1px solid;border-bottom:1px solid;vertical-align: middle;padding-left: 5px;padding-right: 5px;padding-bottom: 0px;margin-bottom: 0px;"> : </td>
                                <th style="width: auto;border: none;border-right: 1px solid;border-top:1px solid;border-bottom:1px solid;vertical-align: middle;padding-left: 5px;padding-bottom: 0px;margin-bottom: 0px;">{{ $form }}</th>
                            </tr>
                        @endif
                    @endforeach
                @endif
            </table>
        </div>
        @if ($i != count($sbItems))
            <div class="page-break"></div>
        @endif
    @endforeach
</body>
</html>
