<!DOCTYPE html>
<html>
<head>
    <title>Form</title>
    <style>
        @page { margin-top: 10px; margin-bottom: 10px; }

        body { margin-top: 10px; margin-bottom: 10px; }

        * {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 6.5px;
        }

        img {
            max-width: 100%;
            max-height: 100%;
        }

        .img-container {
            width: 50px;
            height: 30px;
        }

        table {
            margin-left: -7%;
            width: 114%;
            table-layout: fixed;
            border-collapse: collapse;
        }

        table td, table th{
            text-align: center;
            vertical-align: middle;
            padding: 2px 3px;
            border: 0.1px solid rgba(0, 0, 0, 0.5);
            word-wrap: break-word;
            white-space: wrap
        }

        td.borderless, th.borderless {
            border: 0px !important;
        }

        .text-center {
            text-align: center;
        }

        .text-left {
            text-align: left !important;
        }

        .text-right {
            text-align: right !important;
        }

        .header-1 {
            font-size: 8.5px;
        }

        .header-2 {
            font-size: 8px;
        }

        .header-3 {
            font-size: 7.5px;
        }

        .header-img {
            width: 90px;
            height: 55px;
        }

        .header-bg {
            background: #d4ecfb;
        }

        .yellow-bg {
            background: #ffee00;
        }

        .orange-bg {
            background: #ffc37e;
        }

        .secondary-bg {
            background: #f4f4f4;
        }
    </style>
</head>
<body>
    @php
        $marker = $form->marker;

        function shortUnit($unit) {
            $shortenUnit = "";
            switch ($unit) {
                case "METER" :
                    $shortenUnit = "MT";
                    break;
                case "YARD" :
                    $shortenUnit = "YRD";
                    break;
                default :
                    $shortenUnit = $unit;
                    break;
            }

            return $shortenUnit;
        }
    @endphp

    @if ($marker)
        <table>
            <tr>
                <td class="borderless"></td>
                <td colspan="2" rowspan="3" class="borderless" style="margin: auto;">
                    <div class="img-container">
                        <img class="header-img" src="{{ public_path("assets/dist/img/nag-logo.png") }}" alt="">
                    </div>
                </td>
                <th colspan="8" rowspan="2" class="text-left w-50 header-1">PT. NIRWANA ALABARE GARMENT</th>
                <td class="borderless"></td>
                <td class="header-bg">MEJA</td>
                <td colspan="4" ></td>
                <td colspan="2" class="header-bg">OPERATOR GELAR</td>
                <td class="borderless"></td>
            </tr>
            <tr>
                <td class="borderless"></td>
                <td class="borderless"></td>
                <td class="header-bg">WAKTU</td>
                <td colspan="2" class="header-bg">TANGGAL</td>
                <td colspan="2" class="header-bg">JAM</td>
                <td colspan="2" class="text-left">1.</td>
                <td class="borderless"></td>
            </tr>
            <tr>
                <td class="borderless"></td>
                <td colspan="8" class="text-left">FORM SPREADING</td>
                <td class="borderless"></td>
                <td class="header-bg">MULAI</td>
                <td colspan="2"></td>
                <td colspan="2"></td>
                <td colspan="2" class="text-left">2.</td>
                <td class="borderless"></td>
            </tr>
            <tr>
                <td class="borderless"></td>
                <td class="borderless"></td>
                <td class="borderless"></td>
                <td colspan="8" class="borderless"></td>
                <td class="borderless"></td>
                <td class="header-bg">SELESAI</td>
                <td colspan="2"></td>
                <td colspan="2"></td>
                <td colspan="2"></td>
                <td class="borderless"></td>
            </tr>
            <tr>
                <td colspan="20" class="borderless"></td>
            </tr>
            <tr>
                <th class="borderless"></td>
                <th colspan="2" class="header-bg header-1">
                    NO. FORM
                </th>
                <th colspan="4" class="header-1">
                    {{ $form->no_form }}
                </th>
                <th colspan="2" class="header-bg header-1">
                    PANEL
                </th>
                <th colspan="4" class="header-1">
                    {{ $marker->panel }}
                </th>
                <th colspan="2" class="header-bg header-1">
                    COLOR
                </th>
                <th colspan="4">
                    {{ $marker->color }}
                </th>
                <th class="borderless"></th>
            </tr>
            <tr>
                <th class="borderless"></th>
                <th colspan="2" class="header-bg header-1">
                    PO
                </th>
                <th colspan="4" class="header-1">
                    {{ $marker->po }}
                </th>
                <th colspan="2" class="header-bg header-1">
                    NO. WS
                </th>
                <th colspan="4" class="header-1">
                    {{ $marker->act_costing_ws }}
                </th>
                <th colspan="2" class="header-bg header-1">
                    STYLE
                </th>
                <th colspan="4">
                    {{ $marker->style }}
                </th>
                <th class="borderless"></th>
            </tr>
            <tr>
                <th class="borderless"></th>
                <th colspan="2" class="header-bg header-1">
                    BUYER
                </th>
                <th colspan="4" class="header-1">
                    {{ $marker->buyer }}
                </th>
                <th colspan="2" class="header-bg header-1">
                    NOTES
                </th>
                <th colspan="10" class="yellow-bg header-1">
                    {{ strtoupper($form->notes) }}
                </th>
                <th class="borderless header-1"></th>
            </tr>
            <tr>
                <td colspan="20" class="borderless"></td>
            </tr>
            <tr>
                <td class="borderless"></td>
                <td colspan="18" class="text-center header-bg">FABRIC YANG HARUS DIGUNAKAN</td>
                <td class="borderless"></td>
            </tr>
            <tr>
                <td class="borderless"></td>
                <td colspan="2">ID ITEM</td>
                <td colspan="16" class="text-left">{{ ($item ? $item->id_item : '') }}</td>
                <td class="borderless"></td>
            </tr>
            <tr>
                <td class="borderless"></td>
                <td colspan="2">ITEM</td>
                <td colspan="16" class="text-left">{{ $item ? $item->itemdesc : '' }}</td>
                <td class="borderless"></td>
            </tr>
            <tr>
                <td colspan="20" class="borderless"></td>
            </tr>
            @php
                $markerDetails = collect($marker->markerDetails->filter(function ($item) {
                    return $item->ratio > 0;
                })->values());
                $markerDetailChunk = $markerDetails->chunk(10);
                $index = 0;
            @endphp
            @foreach ($markerDetailChunk as $markerDetail)
                <tr>
                    <td class="borderless"></td>
                    <th colspan="2" class="header-bg header-2">SIZE</td>
                    @for ($i = 0; $i < 10; $i++)
                        <th class="header-bg header-2">{{ (isset($markerDetail[$i]) && $markerDetail[$i] ? explode("-", $markerDetail[$i ]->size)[0] : '') }}</td>
                    @endfor
                    <th class="header-bg header-2">TOTAL</td>
                    <th class="borderless"></th>
                    @if ($index == 0)
                        <td colspan="2" class="header-bg header-2">MARKER</td>
                        <td colspan="2" class="header-2">{{ $marker->urutan_marker }}/{{ $formNumber ? $formNumber->nomor : '-' }}</td>
                    @else
                        <td colspan="2" class="borderless"></td>
                        <td colspan="2" class="borderless"></td>
                    @endif
                    <td class="borderless"></td>
                </tr>
                <tr>
                    <td class="borderless"></td>
                    <th colspan="2" class="header-bg header-2">RATIO</th>
                    @for ($i = 0; $i < 10; $i++)
                        <th class="header-2 text-right">{{ (isset($markerDetail[$i]) && $markerDetail[$i] ? $markerDetail[$i]->ratio : '') }}</th>
                    @endfor
                    <th class="header-2 text-right">{{ $markerDetail->sum("ratio") }}</th>
                    <td class="borderless"></td>
                    @if ($index == 0)
                        <td colspan="2" class="header-bg header-2">QTY GELAR</td>
                        <th colspan="2" class="header-2">{{ $form->qty_ply }}</th>
                    @else
                        <td colspan="2" class="borderless"></td>
                        <td colspan="2" class="borderless"></td>
                    @endif
                    <td class="borderless"></td>
                </tr>
                <tr>
                    <td  class="borderless"></td>
                    <th colspan="2" class="header-bg header-2">QTY FORM</th>
                    @for ($i = 0; $i < 10; $i++)
                        <td class="header-2 text-right">{{ (isset($markerDetail[$i]) && $markerDetail[$i] ? ($markerDetail[$i]->ratio * $form->qty_ply) : '') }}</td>
                    @endfor
                    <td class="header-2 text-right">{{ $markerDetail->sum("ratio") * $form->qty_ply }}</td>
                    <td class="borderless"></td>
                    @if ($index == 0)
                        <td colspan="2" class="header-bg header-2">NO. CUTTING</td>
                        <td colspan="2"></td>
                    @else
                        <td colspan="2" class="borderless"></td>
                        <td colspan="2" class="borderless"></td>
                    @endif
                    <td class="borderless"></td>
                </tr>
                <tr>
                    <td  class="borderless"></td>
                    <th colspan="2" class="header-bg header-2">QTY AKTUAL</th>
                    @for ($i = 0; $i < 10; $i++)
                        <td class="secondary-bg"></td>
                    @endfor
                    <td class="secondary-bg"></td>
                    <td class="borderless"></td>
                    <td colspan="2" class="borderless"></td>
                    <td colspan="2" class="borderless"></td>
                    <td class="borderless"></td>
                </tr>
            @endforeach
            <tr>
                <td colspan="20" class="borderless"></td>
            </tr>
            <tr>
                <td class="borderless"></td>
                <th colspan="2" class="header-bg text-left header-2">CONS. WS</th>
                <th class="text-right header-2">{{ num($marker->cons_ws, 4, false) }}</th>
                <th class="text-left header-2">{{ (shortUnit($marker->unit_cons_ws)) }}/PC</th>

                <td colspan="2" class="header-bg text-left header-2">EST CONS/AMPAR</td>
                <td class="text-right header-2">{{ num($marker->panjang_marker/($markerDetails->sum("ratio")), 4, false) }}</td>
                <td class="text-left header-2">{{ (shortUnit($marker->unit_panjang_marker)) }}/PC</td>

                <td colspan="2" class="header-bg header-3 text-left">PANJANG MARKER</td>
                <td colspan="3" class="header-2">{{ num($marker->panjang_marker, 4, false)." ".(shortUnit($marker->unit_panjang_marker)).", ".num($marker->comma_marker, 4, false)." ".$marker->unit_comma_marker }}</td>

                <td colspan="2" class="header-bg header-3 text-left">PANJANG AKTUAL</td>
                <td colspan="3"></td>
                <td class="borderless"></td>
            </tr>
            <tr>
                <th class="borderless"></th>
                <th colspan="2" class="header-bg text-left header-2">CONS. MARKER</th>
                <th class="text-right header-2">{{ num($marker->cons_marker, 4, false) }}</th>
                <th class="text-left header-2">{{ (shortUnit($marker->unit_cons_marker)) }}/PC</th>

                <td colspan="2" class="header-bg text-left header-2">NEED KAIN GELAR</td>
                <td class="text-right header-2">{{ num(($marker->cons_marker * ($markerDetails->sum("ratio") * $form->qty_ply)), 4, false) }}</td>
                <td class="text-left header-2">{{ (shortUnit($marker->unit_cons_marker)) }}</td>

                <td colspan="2" class="header-bg text-left header-2">LEBAR MARKER</td>
                <td colspan="3" class="header-2">{{ num($marker->lebar_marker, 4, false)." ".$marker->unit_lebar_marker }}</td>

                <td colspan="2" class="header-bg text-left header-2">LEBAR AKTUAL</td>
                <td colspan="3"></td>
                <td class="borderless"></td>
            </tr>
            <tr>
                <th class="borderless"></th>
                <th colspan="2" class="header-bg text-left header-2">CONS. PIPING</th>
                <th class="text-right header-2">{{ num($marker->cons_piping, 4, false) }}</th>
                <th class="text-left header-2">{{ (shortUnit($marker->unit_cons_piping)) }}/PC</th>

                <td colspan="2" class="header-bg text-left header-2">NEED KAIN PIPING</td>
                <td class="text-right header-2">{{ num(($marker->cons_piping * ($markerDetails->sum("ratio") * $form->qty_ply)), 4, false) }}</td>
                <td class="text-left header-2">{{ (shortUnit($marker->unit_cons_piping)) }}</td>

                <td colspan="2" class="header-bg text-left header-2">LEBAR WS</td>
                <td colspan="3" class="header-2">{{ num($marker->lebar_ws, 4, false) > 0 ? (num($marker->lebar_ws, 4, false)." ".$marker->unit_lebar_ws) : null }}</td>

                <td colspan="2" class="header-bg text-left header-2">CONS 1 AMPAR</td>
                <td colspan="3"></td>
                <td class="borderless"></td>
            </tr>
            <tr>
                <td colspan="20" class="borderless"></td>
            </tr>
            <tr>
                <td class="borderless"></td>
                <td class="header-bg">NO.</td>
                <td class="header-bg">GROUP</td>
                <td class="header-bg">LOT</td>
                <td class="header-bg">NO. ROLL</td>
                <td class="header-bg">QTY ROLL (KG / YRD / METER)</td>
                <td class="header-bg">ESTIMASI AMPARAN</td>
                <td class="orange-bg">LEMBAR GELARAN</td>
                <td class="header-bg">SISA GELAR</td>
                <td class="header-bg">SAMBUNGAN SISA</td>
                <td class="header-bg">SAMBUNGAN DLM ROLL</td>
                <td class="header-bg">KEPALA KAIN</td>
                <td class="header-bg">SISA TDK BISA</td>
                <td class="header-bg">REJECT YDS</td>
                <td class="header-bg">SISA KAIN</td>
                <td class="header-bg">PIPING</td>
                <td class="header-bg">TOTAL PEMAKAIAN</td>
                <td class="header-bg">SHORT ROLL +/-</td>
                <td class="header-bg">PERCENT SHORT ROLL (%)</td>
                <td class="borderless"></td>
            </tr>
            @for ($i = 0; $i < 25; $i++)
                <tr>
                    <td class="borderless"></td>
                    @for ($j = 0; $j < 18; $j++)
                        <td style="font-size: 15px !important;">&nbsp;</td>
                    @endfor
                    <td class="borderless" class="borderless"></td>
                </tr>
            @endfor
            <tr>
                <td class="borderless"></td>
                <td colspan="4" class="text-left">TOTAL</td>
                @for ($i = 0; $i < 14; $i++)
                    <td style="font-size: 18px !important;">&nbsp;</td>
                @endfor
                <td class="borderless"></td>
            </tr>
            <tr>
                <td colspan="20" class="borderless"></td>
            </tr>
            <tr>
                <td class="borderless"></td>
                <td colspan="4" rowspan="3">FABRIC SWATCH</td>
                <td colspan="6" class="text-left" style="border-bottom: 0px;">KETERANGAN:</td>
                <td rowspan="2" class="header-bg">TENSION MESIN</td>
                <td rowspan="2" class="header-bg">KECEPATAN MESIN</td>
                <td colspan="3" class="header-bg">CEK SHORT ROLL</td>
                <td colspan="3" class="header-bg">CEK STOCKER</td>
                <td class="borderless"></td>
            </tr>
            <tr>
                <td class="borderless"></td>
                <td colspan="6" rowspan="2" class="text-left" style="border-top: 0px;"></td>
                <td colspan="2" class="header-bg">HASIL</td>
                <td class="header-bg">OLEH</td>
                <td colspan="2" class="header-bg">HASIL</td>
                <td class="header-bg">OLEH</td>
                <td class="borderless"></td>
            </tr>
            <tr>
                <td class="borderless"></td>
                <td style="height: 45px;">&nbsp;</td>
                <td style="height: 45px;">&nbsp;</td>
                <td style="height: 45px;" colspan="2">&nbsp;</td>
                <td style="height: 45px;">&nbsp;</td>
                <td style="height: 45px;" colspan="2">&nbsp;</td>
                <td style="height: 45px;">&nbsp;</td>
                <td class="borderless"></td>
            </tr>
            <tr>
                <td colspan="20" class="borderless"></td>
            </tr>
            <tr>
                <td class="borderless"></td>
                <td colspan="18" style="height: 100px;">
                    POTONGAN DETAIL MARKER
                </td>
                <td class="borderless"></td>
            </tr>
        </table>
    @endif
</body>

</html>
