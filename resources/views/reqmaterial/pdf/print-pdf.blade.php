<!DOCTYPE html>
<html>
<head>
    <title>Stocker</title>
    <style>
        @page { margin: 15px; }

        body { margin: 15px;
        font-family: sans-serif; }
        h3 {
            font-weight: normal;
        }
        table {
            border-spacing: 0;
            border-collapse: collapse;
            margin:0;
            padding:0;
        }
        td,
        th {
            padding: 2px;
            margin:0;
        }
        .table {
            border-collapse: collapse !important;
            width: 100%;
            max-width: 100%;
            font-size: 10px;
        }
        .table td{
            background-color: #fff;
        }
        .table th {
            background-color: #fff;
        }
        .table-bordered th,
        .table-bordered td {
            border: 1px solid #ddd !important;
        }
        .text-right {
            text-align: right !important;
        }
        .text-center {
            text-align: center !important;
        }
        table.repHeader th {
            text-align: left;
        }
    }
    </style>
</head>
<body>
        <table class="table" style="border-bottom: 2px solid #000000; margin-bottom:5px;">
    <tr>
        <td><img src="{{ public_path('nag-logo.png') }}"  width="100px" height="70px"> </td>
        <td style="text-align: right;vertical-align: bottom;font-size: 16px;"><?=strtoupper("PT. NIRWANA ALABARE GARMENT")?></td>
    </tr>
</table>

    @foreach ($dataHeader as $dheader)
    <table width="100%" style="border:none;">
        <tr style="line-height: 8px;">
            <td align="center" style="border:none;"><h3>MATERIAL REQUEST</h3></td>
        </tr>
        <tr style="line-height: 8px;">
            <td align="center" style="border:none; font-size:12pt;">{{ $dheader->bppbno }}</td>
        </tr>
    </table>
    <table width="100%" style="border:none; font-size:9pt">
        <tr>
            <td width="10%"><b>WS #</b></td>
            <td> : {{ $dheader->kpno }}</td>
            <td width="10%"><b>Del. Date</b></td>
            <td> : {{ $dheader->del_date }}</td>
        </tr>
        <tr>
            <td><b>Style #</b></td>
            <td> : {{ $dheader->styleno }}</td>
            <td><b>To</b></td>
            <td> : {{ $dheader->tujuan }}</td>    
        </tr>
        <tr>
            <td><b>Req Date</b></td>
            <td> : {{ $dheader->bppbdate }}</td>
            <td><b>Request By</b></td>
            <td> : {{ $dheader->username }}</td>
        </tr>

        <tr>
            <td><b>WS ACT #</b></td>
            <td> : {{ $dheader->idws_act }}</td>
            <td></td>
            <td></td>
        </tr>
    </table>
    @endforeach
    <br>
    <table class="main" repeat_header="1" border="1" cellspacing="0" width="100%" 
                 style="border-collapse: collapse; width:100%; font-size: 10px;">
           <thead>
                <tr>
                    <th align="center" rowspan="2">NO</th>
                    <th align="center" rowspan="2">ITEM NAME</th>
                    <th align="center" rowspan="2">COLOR</th>
                    <th align="center" rowspan="2">RAK</th>
                    <th align="center" rowspan="2">QTY REQUEST</th>
                    <th align="center" colspan="2">QTY KELUAR</th>
                    <th align="center" colspan="3">CHECKLIST</th>
                    <th align="center" rowspan="2">REMARK</th>
                </tr>
                <tr>
                    <th align="center">QTY</th>
                    <th align="center">UNIT</th>
                    <th align="center">PICKER</th>
                    <th align="center">LOADER</th>
                    <th align="center">PENERIMA</th>
                </tr> 
            </thead>

                   <tbody> 
                    <?php $x = 1; ?>
                        @foreach ($dataDetail as $ddetail)
                            <tr>
                                <td align="center"><?= $x; ?></td>
                                <td align="left">{{ $ddetail->itemdesc }}</td>
                                <td align="left">{{ $ddetail->color }}</td>
                                <td align="left">{{ $ddetail->location }}</td>
                                <td align="right">{{ $ddetail->qty_request }}</td>
                                <td align="right">{{ $ddetail->out_qty }}</td>
                                <td align="right">{{ $ddetail->out_unit }}</td>
                                <td align="left">{{ $ddetail->check_picker }}</td>
                                <td align="right">{{ $ddetail->check_loader }}</td>
                                <td align="left">{{ $ddetail->check_penerima }}</td>
                                <td align="right">{{ $ddetail->remark }}</td>
                            </tr>
                    <?php $x++; ?>
                        @endforeach
                        @foreach ($dataSum as $dsum)
                            <tr>
                                <td align="center" colspan="4">Total</td>
                                <td align="right">{{ $dsum->total_req }}</td>
                                <td align="right" colspan="6"></td>
                            </tr>
                        @endforeach
                    </tbody>
            </table>
            <br>
            <br>
            <table class="table">
        <tr>
            <td class="text-center">PIMPINAN</td>
            <td class="text-center">K. GUDANG</td>
            <td class="text-center">PENERIMA</td>
        </tr>
    </table>
</body>
</html>
