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
            font-size: 11px;
        }

        img {
            width: 95px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table td, table th{
            text-align: center;
            vertical-align: middle;
            padding: 1.5px 3px;
            border: 1px solid;
            width: auto;
        }
    </style>
</head>
<body>
    @php
        $pMeter = 0;
        $commaCM = 0;

        if ($markerData->unit_panjang_marker == "YARD") {
            $pMeter = $markerData->panjang_marker / 1.094;
            $commaCM = $markerData->comma_marker * 2.54;
        } else {
            $pMeter = $markerData->panjang_marker;
            $commaCM = $markerData->comma_marker;
        }
    @endphp
    <table>
        <tr>
            <th colspan="5" style="white-space: nowrap;">PT. NIRWANA ALABARE GARMENT</th>
            <th colspan="{{ $markerData->markerDetails->count()+5 }}">JOB ORDER</th>
            <th>DATE</th>
            <th colspan="3">{{ date("d-M-Y") }}</th>
        </tr>
        <tr>
            <td rowspan="2">STYLE</td>
            <th rowspan="2" colspan="2" style="max-width: 165px;">{{ $markerData->style }}</th>
            <td colspan="2">DESCRIPTION</td>
            <td>QUANTITY</td>
            <td style="text-align: right;">{{ $actCostingData->order_qty }}</td>
            <td style="text-align: left;">{{ $actCostingData->unit_qty }}</td>
            <td colspan="2" style="text-align: left;">FABRIC</td>
            <td colspan="{{ $markerData->markerDetails->count() }}">-</td>
            <td>BUYER</td>
            <th colspan="3">{{ $markerData->buyer }}</th>
        </tr>
        <tr>
            <td rowspan="3" colspan="2">-</td>
            <td style="text-align: left;">WEIGHT</td>
            <td></td>
            <td></td>
            <td colspan="2" style="text-align: left;">NO. WS</td>
            <th colspan="{{ $markerData->markerDetails->count() }}">{{ $markerData->act_costing_ws }}</th>
            <td>WIDTH</td>
            <th colspan="3">{{ $markerData->panel }}</th>
        </tr>
        <tr>
            <td rowspan="2">LABEL</td>
            <td rowspan="2" colspan="2">-</td>
            <td colspan="2" style="text-align: left;">MACHINE/GG</td>
            <td></td>
            <td colspan="2" style="text-align: left;">Delivery</td>
            <td colspan="{{ $markerData->markerDetails->count() }}">-</td>
            <td>FULL WIDTH</td>
            <td colspan="3">-</td>
        </tr>
        <tr>
            <th rowspan="2" colspan="{{ $markerData->markerDetails->count()+5 }}">COLOR BREAKDOWN</th>
            <td>CUTTABLE WIDTH</td>
            <td colspan="3">-</td>
        </tr>
        <tr>
            <th colspan="5">SKETCH</th>
            <td>PO.NO</td>
            <th colspan="3">-</th>
        </tr>
        <tr>
            <td rowspan="13" colspan="5"></td>
            <th rowspan="3" colspan="3">{{ $markerData->color }}</th>
            <td colspan="{{ $markerData->markerDetails->count()+1 }}">QUANTITY</td>
            <td rowspan="2" colspan="2">CONS WS</td>
            <td rowspan="2" colspan="2">TOTAL CONS WS</td>
            <td>GRAMASI</td>
        </tr>
        <tr>
            @foreach ($markerData->markerDetails as $markerDetail)
                <th>{{ $markerDetail->size }}</th>
            @endforeach
            <th>TOTAL</th>
            <td>{{ $markerData->panel }}</td>
        </tr>
        <tr>
            @foreach ($soDetData as $soDet)
                <td>{{ $soDet->qty }}</td>
            @endforeach
            <td>{{ $actCostingData->order_qty }}</td>
            <th rowspan="2">{{ $markerData->panel }}</th>
            <th rowspan="2">{{ $orderQty[0]->cons_ws }}</th>
            <th rowspan="2" colspan="2">{{ $orderQty[0]->cons_ws * $actCostingData->order_qty }}</th>
            <th rowspan="2">{{ $markerData->gramasi }}</th>
        </tr>
        <tr>
            <th colspan="3">TOTAL</th>
            @foreach ($soDetData as $soDet)
                <th>{{ $soDet->qty }}</th>
            @endforeach
            <th>{{ $actCostingData->order_qty }}</th>
        </tr>
        <tr>
            <td colspan="{{ $markerData->markerDetails->count() + 9 }}" style="border: none;"><br></td>
        </tr>
        <tr>
            <td colspan="3" style="border: none;"></td>
            <th colspan="2">{{ $markerData->panel }}</th>
            <td colspan="{{ $markerData->markerDetails->count() + 4 }}" style="border: none;"><br></td>
        </tr>
        <tr>
            <th rowspan="7" colspan="2">{{ $markerData->color }}</th>
            <th rowspan="2">MRK</th>
            @foreach ($soDetData as $soDet)
                @php
                    $markerDetail = $markerData->markerDetails->where("so_det_id", $soDet->id)->first();
                @endphp
                <th rowspan="2">{{ $markerDetail->size }}</th>
            @endforeach
            <th rowspan="2">TOTAL</th>
            <th rowspan="2">TOTAL PLY</th>
            <th rowspan="2">TOTAL QTY PER MARKER</th>
            <th colspan="2">LENGTH</th>
            <th>CONS MARKER</th>
        </tr>
        <tr>
            <th>MT</th>
            <th>CM</th>
            <th>MT/PC</th>
        </tr>
        <tr>
            <th>{{ $markerData->urutan_marker }}</th>
            @php
                $totalRatio = 0;
            @endphp
            @foreach ($soDetData as $soDet)
                @php
                    $markerDetail = $markerData->markerDetails->where("so_det_id", $soDet->id)->first();
                    $totalRatio += $markerDetail->ratio;
                @endphp
                <th>{{ $markerDetail->ratio }}</th>
            @endforeach
            <th>{{ $totalRatio }}</th>
            <th>{{ $markerData->gelar_qty }}</th>
            <th>{{ $totalRatio * $markerData->gelar_qty }}</th>
            <th>{{ $pMeter }}</th>
            <th>{{ $commaCM }}</th>
            <th>{{ $markerData->cons_marker }}</th>
        </tr>
        <tr>
            <th colspan="{{ $markerData->markerDetails->count() + 2 }}" style="border: none;"><br></th>
            <th>TOTAL QTY</th>
            <th>{{ $totalRatio * $markerData->gelar_qty }}</th>
            <th rowspan="2" colspan="2">-</th>
            <th>RATA-RATA CONS</th>
        </tr>
        <tr>
            <th colspan="{{ $markerData->markerDetails->count() + 2 }}" style="border: none;"><br></th>
            <th>SELISIH ALW</th>
            <th>-</th>
            <th>{{ $markerData->cons_marker }}</th>
        </tr>
        <tr>
            <th colspan="{{ $markerData->markerDetails->count() + 6 }}" style="border: none; border-bottom: 0px;"><br></th>
            <th>-</th>
        </tr>
        <tr>
            <th colspan="{{ $markerData->markerDetails->count() + 7 }}" style="border: none;"><br></th>
        </tr>
        <tr>
            <th colspan="2">ADM</th>
            <th colspan="2">MANAGER</th>
            <th colspan="2">MANAGER</th>
            <td colspan="2" style="border: none;"></td>
            <th style="border: none; white-space:nowrap; background-color: #ffd900; text-align: left;">NOTE:</th>
        </tr>
        <tr>
            <td colspan="2"><br><br></td>
            <td colspan="2"><br><br></td>
            <td colspan="2"><br><br></td>
            <td style="border: none;" colspan="2"></td>
            <th style="border: none; white-space:nowrap; background-color: #ffd900; text-align: center;" colspan="{{ $markerData->markerDetails->count() }}">CONS WS SUDAH DIKONVERSI KE METER</th>
        </tr>
    </table>
</body>
</html>
