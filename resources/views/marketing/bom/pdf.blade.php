<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BOM Marketing - {{ $bom->no_katalog_bom }}</title>
    <style>
        @page { margin: 20px 30px; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 9px; color: #333; }

        table { border-collapse: collapse; margin-bottom: 10px; }
        .table-border th, .table-border td { border: 1px solid #ccc; padding: 4px; }

        .bg-light { background-color: #f2f2f2; }
        .bg-category { background-color: #e9ecef; font-weight: bold; }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .fw-bold { font-weight: bold; }
        .w-100 { width: 100%; }

        .header-logo { max-width: 120px; max-height: 60px; object-fit: contain; }
        .company-name { font-size: 16px; margin: 0; text-align: right; padding-top: 20px; }

        .title-bom { font-size: 16px; font-weight: bold; margin-top: 15px; margin-bottom: 15px; }

        .info-table { width: 48%; float: left; font-size: 9px; }
        .info-table th { text-align: left; background-color: #f2f2f2; border: 1px solid #ccc; padding: 3px 5px; width: 35%; }
        .info-table td { border: 1px solid #ccc; padding: 3px 5px; }

        .info-table-right { width: 48%; float: right; font-size: 9px; }
        .info-table-right th { text-align: left; background-color: #f2f2f2; border: 1px solid #ccc; padding: 3px 5px; width: 40%; }
        .info-table-right td { border: 1px solid #ccc; padding: 3px 5px; }

        .clearfix { clear: both; margin-bottom: 15px; }

        .main-table th { background-color: #f2f2f2; font-size: 8px; text-align: center; }
        .main-table td { font-size: 8px; }
        .col-loss { background-color: #f2f2f2; }
    </style>
</head>
<body>

    <table class="w-100" style="margin-bottom: 15px; border-bottom: 2px solid #000; padding-bottom: 10px;">
        <tr>
            <td style="width: 15%; vertical-align: middle;">
                <img src="{{ public_path('assets/dist/img/nag-logo.png') }}" style="max-width: 120px; max-height: 60px; object-fit: contain;">
            </td>
            <td style="width: 85%; vertical-align: middle; text-align: center;">
                <h5 style="margin: 0; font-size: 20px;">PT NIRWANA ALABARE GARMENT</h5>
                <h5 style="margin: 0; font-size: 18px;">BILL OF MATERIAL</h5>
            </td>
        </tr>
    </table>

    <div>
        <table class="info-table">
            <tr>
                <th class="fw-bold text-center">BUYER</th>
                <td>{{ $bom->nama_buyer }}</td>
            </tr>
            <tr>
                <th class="fw-bold text-center">PO. NUMBER</th>
                <td>-</td>
            </tr>
            <tr>
                <th class="fw-bold text-center">AGENT</th>
                <td>-</td>
            </tr>
            <tr>
                <th class="fw-bold text-center">DATE OF ISSUE</th>
                <td>{{ date('d-M-Y', strtotime($bom->created_at)) }}</td>
            </tr>
            <tr>
                <th class="fw-bold text-center">CREATED BY</th>
                <td>{{ $bom->created_by ?? 'admin' }}</td>
            </tr>
        </table>

        <table class="info-table-right">
            {{-- <tr>
                <th class="fw-bold text-center">NO WS</th>
                <td>{{ $bom->no_costing ?? '-' }}</td>
            </tr> --}}
            <tr>
                <th class="fw-bold text-center">ITEM</th>
                <td>-</td>
            </tr>
            <tr>
                <th class="fw-bold text-center">STYLE NO.</th>
                <td>{{ $bom->style }}</td>
            </tr>
            <tr>
                <th class="fw-bold text-center">QUANTITY</th>
                <td>{{ number_format($bom->qty_order ?? 0, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <th class="fw-bold text-center">DELIVERY DATE</th>
                <td>-</td>
            </tr>
            <tr>
                <th class="fw-bold text-center">REVISE</th>
                <td>0</td>
            </tr>
        </table>
    </div>

    <div class="clearfix"></div>

    <table class="table-border main-table w-100">
        <thead>
            <tr>
                <th rowspan="2" width="3%">NO</th>
                <th rowspan="2" width="10%">ITEM</th>
                <th rowspan="2" width="20%">DESCRIPTION</th>
                <th rowspan="2" width="15%">COLOR / SIZE</th>
                <th rowspan="2" width="8%">PANEL</th>
                <th rowspan="2" width="5%">ORIG<br>QTY</th>
                <th colspan="2">CONS</th>
                <th rowspan="2" width="8%">QTY REQ'D</th>
                <th colspan="2" class="col-loss">LOSS</th>
                <th colspan="2">TOT QTY REQ'D</th>
            </tr>
            <tr>
                <th width="6%">/PC</th>
                <th width="5%">UNIT</th>
                <th width="4%" class="col-loss">%</th>
                <th width="4%" class="col-loss">VALUE</th>
                <th width="8%">QTY</th>
                <th width="5%">UNIT</th>
            </tr>
        </thead>
        <tbody>
            @if(count($groupedByCategory) > 0)
                @foreach($groupedByCategory as $category => $items)
                    <tr>
                        <td colspan="13" class="text-center bg-category">{{ $category }}</td>
                    </tr>
                    @foreach($items as $idx => $det)
                        @php
                            $orig_qty   = $det->orig_qty ?? 0;
                            $cons       = $det->cons ?? 0;
                            $qty_reqd   = round($orig_qty * $cons, 10);
                            $tot_qty_reqd = $qty_reqd; // loss = 0
                        @endphp
                        <tr>
                            <td class="text-center"></td>
                            <td>{{ $det->content_name ?? '-' }}</td>
                            <td>{{ $det->item_name ?? '-' }}</td>
                            <td class="text-center">
                                {!! $det->color_display == 'All Color' ? '<b>All Color</b>' : ($det->color_display ?? '-') !!}<br>
                                {!! $det->size_display == 'All Size' ? '<b>All Size</b>' : ($det->size_display ?? '-') !!}
                            </td>
                            <td class="text-center">{{ $det->nama_panel ?? '-' }}</td>
                            <td class="text-right">{{ number_format($orig_qty, 0, ',', '.') }}</td>
                            <td class="text-center">{{ rtrim(rtrim(number_format($cons, 10, '.', ''), '0'), '.') }}</td>
                            <td class="text-center">{{ $det->unit_name ?? '-' }}</td>
                            <td class="text-right">{{ rtrim(rtrim(number_format($qty_reqd, 10, '.', ''), '0'), '.') }}</td>
                            <td class="text-center col-loss">0%</td>
                            <td class="text-center col-loss">0</td>
                            <td class="text-right fw-bold">{{ rtrim(rtrim(number_format($tot_qty_reqd, 10, '.', ''), '0'), '.') }}</td>
                            <td class="text-center fw-bold">{{ $det->unit_name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td colspan="5">PO No.</td>
                            <td colspan="8" class="text-right fw-bold bg-light">TOTAL &nbsp;&nbsp;&nbsp; {{ rtrim(rtrim(number_format($tot_qty_reqd, 10, '.', ''), '0'), '.') }} &nbsp;&nbsp; {{ $det->unit_name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td colspan="13">Notes :</td>
                        </tr>
                    @endforeach
                @endforeach
            @else
                <tr>
                    <td colspan="13" class="text-center text-muted">Belum ada item yang ditambahkan.</td>
                </tr>
            @endif
        </tbody>
    </table>

</body>
</html>
