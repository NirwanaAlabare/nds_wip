<!DOCTYPE html>
<html>
<head>
    <title>Sewing Out</title>
    <style>
        @page { margin: 15px; }
        body { margin: 15px; font-family: sans-serif; }
        h3 { font-weight: normal; }
        table {
            border-spacing: 0;
            border-collapse: collapse;
            margin: 0;
            padding: 0;
        }
        td, th { padding: 2px; margin: 0; }
        .table {
            border-collapse: collapse !important;
            width: 100%;
            max-width: 100%;
            font-size: 10px;
        }
        .table td { background-color: #fff; }
        .table th { background-color: #fff; }
        .table-bordered th,
        .table-bordered td { border: 1px solid #ddd !important; }
        .text-right  { text-align: right  !important; }
        .text-center { text-align: center !important; }
        table.repHeader th { text-align: left; }
    </style>
</head>
<body>

{{-- Logo & Company --}}
<table class="table" style="border-bottom: 2px solid #000000; margin-bottom:5px;">
    <tr>
        <td><img src="{{ public_path('nag-logo.png') }}" width="100px" height="70px"></td>
        <td style="text-align:right; vertical-align:bottom; font-size:16px;">
            <?= strtoupper("PT. NIRWANA ALABARE GARMENT") ?>
        </td>
    </tr>
</table>

{{-- Title --}}
<table width="100%" style="border:none;">
    <tr style="line-height:8px;">
        <td align="center" style="border:none;"><h3>SEWING OUT</h3></td>
    </tr>
    <tr style="line-height:8px;">
        <td align="center" style="border:none; font-size:12pt;">{{ $header->no_bppb }}</td>
    </tr>
</table>

{{-- Info Header --}}
<table width="100%" style="border:none; font-size:9pt; margin-top:4px;">
    <tr>
        <td width="10%"><b>WS #</b></td>
        <td width="25%"> : {{ $header->kpno }}</td>
        <td width="10%"><b>No PO</b></td>
        <td> : {{ $header->no_po }}</td>
    </tr>
    <tr>
        <td><b>Style #</b></td>
        <td> : {{ $header->styleno }}</td>
        <td><b>Send To</b></td>
        <td> : {{ $header->tujuan }}</td>
    </tr>
    <tr>
        <td><b>Trans Date</b></td>
        <td colspan="3"> : {{ $header->tgl_bppb }}</td>
    </tr>
    @if($header->keterangan)
    <tr>
        <td><b>Notes</b></td>
        <td colspan="3"> : {{ $header->keterangan }}</td>
    </tr>
    @endif
</table>

<br>

{{-- Detail Table --}}
<table class="main" repeat_header="1" border="1" cellspacing="0" width="100%"
       style="border-collapse:collapse; width:100%; font-size:10px;">
    <thead>
        <tr style="background-color:#f0f0f0;">
            <th align="center" style="width:4%;">NO</th>
            <th align="center" style="width:18%;">ITEM NAME</th>
            <th align="center" style="width:38%;">COLOR</th>
            <th align="center" style="width:6%;">SIZE</th>
            <th align="center" style="width:9%;">QTY</th>
            <th align="center" style="width:6%;">UNIT</th>
            <th align="center" style="width:19%;">REMARK</th>
        </tr>
    </thead>
    <tbody>
        <?php $x = 1; ?>
        @foreach($detail as $d)
        <tr>
            <td align="center">{{ $x }}</td>
            <td align="left">{{ $d->itemdesc }}</td>
            <td align="left">{{ $d->color }}</td>
            <td align="center">{{ $d->size }}</td>
            <td align="right">{{ number_format($d->qty, 2) }}</td>
            <td align="center">{{ $d->unit }}</td>
            <td align="left">&nbsp;</td>
        </tr>
        <?php $x++; ?>
        @endforeach
        <tr>
            <td align="center" colspan="4"><b>Total</b></td>
            <td align="right"><b>{{ number_format($total->total_qty, 2) }}</b></td>
            <td colspan="2"></td>
        </tr>
    </tbody>
</table>

<br><br>

{{-- Signatures --}}
<table class="table" width="100%">
    <tr>
        <td class="text-center" width="50%">PENGIRIM</td>
        <td class="text-center" width="50%">PENERIMA</td>
    </tr>
    <tr style="height:60px;">
        <td></td><td></td>
    </tr>
    <tr>
        <td class="text-center">( _________________ )</td>
        <td class="text-center">( _________________ )</td>
    </tr>
</table>

</body>
</html>
