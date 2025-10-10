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
            font-size: 8px;
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
    </style>
</head>
<body>
    <div style="border: 1px solid; padding: 5px;">
        <table style="width: 100%;margin-bottom: 5px;">
            <tr>
                <th style="border: none;vertical-align: middle;">Product</th>
                <td style="border: none;vertical-align: middle;padding: 2px;padding-left: 5px;padding-right: 5px;"> : </td>
                <th style="border: none;vertical-align: middle;">{{ strtoupper(substr($sbItem->detail_item, 0, 65)).(strlen($sbItem->detail_item) > 65 ? '...' : '') }}</th>
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
                <th style="padding-left: 1px;font-size: 7px;">Group</th>
                <th style="padding-left: 1px;font-size: 7px;">No. Roll</th>
                <th style="padding-left: 1px;font-size: 7px;">No. Roll Buyer</th>
                <th style="padding-left: 1px;font-size: 7px;">Lot</th>
                <th style="padding-left: 1px;font-size: 7px;">Qty Awal</th>
                <th style="padding-left: 1px;font-size: 7px;">Qty Sisa</th>
                <th style="padding-left: 1px;font-size: 7px;">Unit</th>
                @if ($sbItem->unit == "YRD" || $sbItem->unit == "YARD")
                    <th style="padding-left: 1px;font-size: 7px;">Konv.Awal</th>
                    <th style="padding-left: 1px;font-size: 7px;">Konv.Sisa</th>
                    <th style="padding-left: 1px;font-size: 7px;">Konv.Unit</th>
                @endif
            </tr>
            <tr>
                <td style="padding-left: 1px;font-size: 7px;">{{ $ndsItem ? strtoupper(substr($ndsItem->group_roll, 0, 15)).(strlen($ndsItem->group_roll) > 15 ? '...' : '') : '-' }}</td>
                <td style="padding-left: 1px;font-size: 7px;">{{ $sbItem->no_roll }}</td>
                <td style="padding-left: 1px;font-size: 7px;">{{ $sbItem->no_roll_buyer }}</td>
                <td style="padding-left: 1px;font-size: 7px;">{{ $sbItem->lot }}</td>
                <td style="padding-left: 1px;font-size: 7px;">{{ $sbItem->qty }}</td>
                <td style="padding-left: 1px;font-size: 7px;">{{ $ndsItem ? ((($sbItem->unit == "YRD" || $sbItem->unit == "YARD") && $ndsItem->unit == "METER") ?  round($ndsItem->sisa_kain * 1.09361, 2) : $ndsItem->sisa_kain) : $sbItem->qty }}</td>
                <td style="padding-left: 1px;font-size: 7px;">{{ $sbItem->unit }}</td>
                @if ($sbItem->unit == "YRD" || $sbItem->unit == "YARD")
                    <td style="padding-left: 1px;font-size: 7px;">{{ ($sbItem->unit == "YRD" || $sbItem->unit == "YARD" ? round($sbItem->qty * 0.9144, 2) : $sbItem->qty) }}</td>
                    <td style="padding-left: 1px;font-size: 7px;">{{ $ndsItem ? ($ndsItem->unit == "YRD" || $ndsItem->unit == "YARD" ? round($ndsItem->sisa_kain * 0.9144, 2) : $ndsItem->sisa_kain) : ($sbItem->unit == "YRD" || $sbItem->unit == "YARD" ? round($sbItem->qty * 0.9144, 2) : $sbItem->qty) }}</td>
                    <td style="padding-left: 1px;font-size: 7px;">{{ $ndsItem ? ($ndsItem->unit == "YRD" || $ndsItem->unit == "YARD" ? "METER" : $ndsItem->unit) : ($sbItem->unit == "YRD" || $sbItem->unit == "YARD" ? "METER" : $sbItem->unit) }}</td>
                @endif
            </tr>
        </table>
        <table style="margin-bottom: 5px;">
            @php
                $forms = explode('^', ($ndsItem ? $ndsItem->no_form : '-'));
                $formsChunk = array_chunk($forms, 10);
            @endphp
            <tr>
                <td style="text-align: center; width: auto;" rowspan="{{ count($forms) > 0 ? (count($forms) > 10 ? 10 : count($forms)) : 1 }}">
                    <div style="padding-top: 25px; padding-bottom: 25px;">
                        <img src="data:image/png;base64, {!! DNS1D::getBarcodePNG($sbItem->id_roll, 'c39', 2, 70) !!}" style="width: 150px; padding-bottom: 3px;">
                        <br>
                        <span style="font-weight: bold; font-size: 15px;">{{ $sbItem->id_roll }}</span>
                    </div>
                </td>
                @if (count($forms) > 0)
                    <th style="font-size: 6.5px;width: auto;border: none;border-left: 1px solid;border-top:1px solid;border-bottom:1px solid;vertical-align: middle;padding-left: 1px;padding-bottom: 0px;margin-bottom: 0px;">No. Form</th>
                    <td style="font-size: 6.5px;width: auto;border: none;border-top:1px solid;border-bottom:1px solid;vertical-align: middle;padding-left: 1px;padding-right: 1px;padding-bottom: 0px;margin-bottom: 0px;"> : </td>
                    <th style="font-size: 6.5px;width: auto;border: none;border-right: 1px solid;border-top:1px solid;border-bottom:1px solid;vertical-align: middle;padding-left: 1px;padding-bottom: 0px;margin-bottom: 0px;">{{ $forms[0] }}</th>

                    @if (count($forms) > 10)
                        @foreach ($formsChunk as $formChunk)
                            @if ($loop->index > 0)
                                @if (isset($forms[10*$loop->index-1]))
                                    <th style="font-size: 6.5px;width: auto;border: none;border-left: 1px solid;border-top:1px solid;border-bottom:1px solid;vertical-align: middle;padding-left: 1px;padding-bottom: 0px;margin-bottom: 0px;">No. Form</th>
                                    <td style="font-size: 6.5px;width: auto;border: none;border-top:1px solid;border-bottom:1px solid;vertical-align: middle;padding-left: 1px;padding-right: 1px;padding-bottom: 0px;margin-bottom: 0px;"> : </td>
                                    <th style="font-size: 6.5px;width: auto;border: none;border-right: 1px solid;border-top:1px solid;border-bottom:1px solid;vertical-align: middle;padding-left: 1px;padding-bottom: 0px;margin-bottom: 0px;">{{ $forms[10*$loop->index-1] }}</th>
                                @else
                                    <th style="font-size: 6.5px;width: auto;border: none;border-left: 1px solid;border-top:1px solid;border-bottom:1px solid;vertical-align: middle;padding-left: 1px;padding-bottom: 0px;margin-bottom: 0px;"></th>
                                    <td style="font-size: 6.5px;width: auto;border: none;border-top:1px solid;border-bottom:1px solid;vertical-align: middle;padding-left: 1px;padding-right: 1px;padding-bottom: 0px;margin-bottom: 0px;"></td>
                                    <th style="font-size: 6.5px;width: auto;border: none;border-right: 1px solid;border-top:1px solid;border-bottom:1px solid;vertical-align: middle;padding-left: 1px;padding-bottom: 0px;margin-bottom: 0px;"></th>
                                @endif
                            @endif
                        @endforeach
                    @endif
                @else
                    <th style="font-size: 6.5px;width: auto;border: none;border-left: 1px solid;border-top:1px solid;border-bottom:1px solid;vertical-align: middle;padding-left: 1px;padding-bottom: 0px;margin-bottom: 0px;">No. Form</th>
                    <td style="font-size: 6.5px;width: auto;border: none;border-top:1px solid;border-bottom:1px solid;vertical-align: middle;padding-left: 1px;padding-right: 1px;padding-bottom: 0px;margin-bottom: 0px;"> : </td>
                    <th style="font-size: 6.5px;width: auto;border: none;border-right: 1px solid;border-top:1px solid;border-bottom:1px solid;vertical-align: middle;padding-left: 1px;padding-bottom: 0px;margin-bottom: 0px;">-</th>
                @endif
            </tr>
            @if (count($forms) > 1)
                @for ($i = 1; $i < (count($forms) > 10 ? 10 : count($forms)); $i++)
                    <tr>
                        <th style="font-size: 6.5px;width: auto;border: none;border-left: 1px solid;border-top:1px solid;border-bottom:1px solid;vertical-align: middle;padding-left: 1px;padding-bottom: 0px;margin-bottom: 0px;">No. Form</th>
                        <td style="font-size: 6.5px;width: auto;border: none;border-top:1px solid;border-bottom:1px solid;vertical-align: middle;padding-left: 1px;padding-right: 1px;padding-bottom: 0px;margin-bottom: 0px;"> : </td>
                        <th style="font-size: 6.5px;width: auto;border: none;border-right: 1px solid;border-top:1px solid;border-bottom:1px solid;vertical-align: middle;padding-left: 1px;padding-bottom: 0px;margin-bottom: 0px;">{{ isset($forms[$i]) ? $forms[$i] : "-" }}</th>

                        @foreach ($formsChunk as $chunk)
                            @if ($loop->index > 0)
                                @if (isset($forms[10*$loop->index+$i]))
                                    <th style="font-size: 6.5px;width: auto;border: none;border-left: 1px solid;border-top:1px solid;border-bottom:1px solid;vertical-align: middle;padding-left: 1px;padding-bottom: 0px;margin-bottom: 0px;">No. Form</th>
                                    <td style="font-size: 6.5px;width: auto;border: none;border-top:1px solid;border-bottom:1px solid;vertical-align: middle;padding-left: 1px;padding-right: 1px;padding-bottom: 0px;margin-bottom: 0px;"> : </td>
                                    <th style="font-size: 6.5px;width: auto;border: none;border-right: 1px solid;border-top:1px solid;border-bottom:1px solid;vertical-align: middle;padding-left: 1px;padding-bottom: 0px;margin-bottom: 0px;">{{ $forms[(10*$loop->index)+$i] }}</th>
                                @else
                                    <th style="font-size: 6.5px;width: auto;border: none;border-left: 1px solid;border-top:1px solid;border-bottom:1px solid;vertical-align: middle;padding-left: 1px;padding-bottom: 0px;margin-bottom: 0px;"></th>
                                    <td style="font-size: 6.5px;width: auto;border: none;border-top:1px solid;border-bottom:1px solid;vertical-align: middle;padding-left: 1px;padding-right: 1px;padding-bottom: 0px;margin-bottom: 0px;"></td>
                                    <th style="font-size: 6.5px;width: auto;border: none;border-right: 1px solid;border-top:1px solid;border-bottom:1px solid;vertical-align: middle;padding-left: 1px;padding-bottom: 0px;margin-bottom: 0px;"></th>
                                @endif
                            @endif
                        @endforeach
                    </tr>
                @endfor
            @endif
        </table>
    </div>
</body>
</html>
