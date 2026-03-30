<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Worksheet SO</title>
    <style>
        @page { margin: 15px 15px; }

        body {
            font-family: 'Helvetica', Arial, sans-serif;
            font-size: 9px;
            color: #000;
            line-height: 1.0;
        }

        table { width: 100%; border-collapse: collapse; }

        .table-data {
            table-layout: fixed;
            border: 1px solid #000;
        }

        /* Thead otomatis berulang di halaman selanjutnya jika tabel kepanjangan */
        .table-data thead { display: table-header-group; }
        .table-data tr { page-break-inside: avoid; page-break-after: auto; }

        .table-data th, .table-data td {
            border: 1px solid #000;
            padding: 3px 3px;
            text-align: center;
            vertical-align: middle;
            word-wrap: break-word;
            overflow: hidden;
        }

        .table-data th { background-color: #f2f2f2; font-weight: bold; }

        .color-cell-first { border-bottom: none !important; }
        .color-cell-middle { border-top: none !important; border-bottom: none !important; }
        .color-cell-last { border-top: none !important; }
        .color-cell-single { border-top: 1px solid #000 !important; border-bottom: 1px solid #000 !important; }

        .header-info { margin-bottom: 10px; width: 100%; font-size: 12px; }
        .header-info td {
            padding: 0;
            vertical-align: top;
            line-height: 1.3;
            padding-bottom: 8px;
        }

        .header-info .label-left { font-weight: bold; width: 11%; }
        .header-info .label { font-weight: bold; width: 13%; }
        .header-info .colon{ width: 1%; text-align: left; font-weight: bold; }
        .header-info .value{ width: 28%; padding-right: 15px; }

        .img-so { max-width: 220px; max-height: 160px; object-fit: contain; border: 1px solid #ccc; padding: 2px; }
        .no-image { width: 160px; height: 160px; border: 1px dashed #999; display: inline-block; text-align: center; line-height: 160px; color: #999; font-size: 10px; }

        .text-left { text-align: left !important; }
        .text-center { text-align: center !important; }
        .text-right { text-align: right !important; }
        .fw-bold { font-weight: bold !important; }

        footer { position: fixed; bottom: -60px; right: 0px; font-size: 10px; font-weight: bold; }
        .page-number:after { content: "Halaman " counter(page) " / " counter(pages); }
    </style>
</head>
<body>

    <footer><span class="page-number"></span></footer>

    <table style="margin-bottom: 15px; border-bottom: 2px solid #000; padding-bottom: 10px;">
        <tr>
            <td style="width: 15%; vertical-align: middle;">
                <img src="{{ public_path('assets/dist/img/nag-logo.png') }}" style="max-width: 120px; max-height: 60px; object-fit: contain;">
            </td>
            <td style="width: 85%; vertical-align: center; text-align: center;">
                <h5 style="margin: 0; font-size: 20px;">PT NIRWANA ALABARE GARMENT</h5>
                <h5 style="margin: 0; font-size: 18px;">WORKSHEET</h5>
            </td>
        </tr>
    </table>

    <table style="width: 100%; margin-bottom: 15px;">
        <tr>
            <td style="width: 75%; vertical-align: top; padding: 0;">
                <table class="header-info" style="margin-bottom: 0;">
                    <tr>
                        <td class="label-left">BUYER</td><td class="colon">:</td>
                        <td class="value">{{ $header->buyer ?? '-' }}</td>
                        <td class="label">DESCRIPTION</td><td class="colon">:</td>
                        <td class="value">{{ $header->product_group ?? '-' }} / {{ $header->product_item ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="label-left">STYLE</td><td class="colon">:</td>
                        <td class="value">{{ $header->styleno ?? '-' }}</td>
                        <td class="label">WORKSHEET</td><td class="colon">:</td>
                        <td class="value">{{ $header->kpno ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="label-left">EX-FTY DATE</td><td class="colon">:</td>
                        <td class="value">{{ $header->ex_fty_date ?? '' }}</td>
                        <td class="label">PO</td><td class="colon">:</td>
                        <td class="value">{{ $header->no_po ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="label-left">MARKET</td><td class="colon">:</td>
                        <td class="value">{{ $header->market ?? '-' }}</td>
                        <td class="label">QTY</td><td class="colon">:</td>
                        <td class="value">{{ number_format($header->qty ?? 0, 0, ',', '.') }} PCS</td>
                    </tr>
                </table>
            </td>

            <td style="width: 25%; text-align: right; vertical-align: top; padding: 0;">
                @if(!empty($header->nm_file))
                    <img src="{{ public_path('uploads/so/' . $header->nm_file) }}" class="img-so" alt="Gambar SO">
                @else
                    <div class="no-image">No Image</div>
                @endif
            </td>
        </tr>
    </table>

    <table class="table-data" style="margin-bottom: 20px;">
        <thead>
            <tr>
                <th rowspan="2" width="5%">NO</th>
                <th rowspan="2" class="text-left" width="25%">PO COLOR DESC.</th>
                <th colspan="{{ count($sizes) > 0 ? count($sizes) : 1 }}">QTY</th>
                <th rowspan="2" width="10%">TOTAL (PC)</th>
            </tr>
            <tr>
                @if(count($sizes) > 0)
                    @foreach($sizes as $size)
                        <th>{{ $size }}</th>
                    @endforeach
                @else
                    <th>-</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @php
                $no = 1;
                $grand_total = 0;
                $size_totals = array_fill_keys($sizes, 0);
            @endphp
            @forelse($details as $color => $row_sizes)
                @php $row_total = 0; @endphp
                <tr>
                    <td>{{ $no++ }}</td>
                    <td class="text-left fw-bold">{{ $color }}</td>
                    @foreach($sizes as $size)
                        @php
                            $qty = $row_sizes[$size] ?? 0;
                            $row_total += $qty;
                            $size_totals[$size] += $qty;
                        @endphp
                        <td>{{ $qty > 0 ? number_format($qty, 0, ',', '.') : '-' }}</td>
                    @endforeach
                    <td class="text-right fw-bold">{{ number_format($row_total, 0, ',', '.') }}</td>
                </tr>
                @php $grand_total += $row_total; @endphp
            @empty
                <tr><td colspan="{{ count($sizes) + 3 }}">Belum ada detail ukuran & warna.</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th colspan="2" class="text-right">GRAND TOTAL</th>
                @foreach($sizes as $size)
                    <th>{{ number_format($size_totals[$size], 0, ',', '.') }}</th>
                @endforeach
                <th class="text-right">{{ number_format($grand_total, 0, ',', '.') }}</th>
            </tr>
        </tfoot>
    </table>
    @foreach ($group_names as $g_name)
        @php
            $is_fabric = str_contains($g_name, 'FABRIC');
            $has_data = isset($materials[$g_name]) && count($materials[$g_name]) > 0;

            $avoid_break_if_empty = !$has_data ? 'page-break-inside: avoid;' : '';
        @endphp

        <table class="table-data" style="margin-bottom: 20px; {{ $avoid_break_if_empty }}">
            <thead>
                <tr>
                    <th colspan="{{ $is_fabric ? 13 : 10 }}" class="text-left" style="background-color: #e9ecef; font-size: 11px; padding: 6px;">
                        {{ $g_name }}
                    </th>
                </tr>
                @if($is_fabric)
                    <tr>
                        <th width="7%">COLOR GARMENT</th>
                        <th width="5%">SHELL</th>
                        <th width="25%">ITEM</th>
                        <th width="15%">DESC</th>
                        <th width="5%">SIZE</th>
                        <th width="7%">COLOR ITEM</th>
                        <th width="5%">QTY</th>
                        <th width="5%">CONS</th>
                        <th width="5%">UNIT</th>
                        <th width="5%">CONS CONV</th>
                        <th width="5%">UNIT</th>
                        <th width="6%">TOTAL</th>
                        <th width="5%">UNIT</th>
                    </tr>
                @else
                    <tr>
                        <th width="10%">COLOR</th>
                        <th width="25%">ITEM</th>
                        <th width="15%">DESCRIPTION</th>
                        <th width="10%">COLOR ITEM</th>
                        <th width="10%">SIZE ITEM</th>
                        <th width="5%">QTY</th>
                        <th width="5%">CONS</th>
                        <th width="5%">UNIT</th>
                        <th width="6%">TOTAL</th>
                        <th width="6%">UNIT</th>
                    </tr>
                @endif
            </thead>

            @if($has_data)
                @foreach($materials[$g_name] as $color_name => $items)
                    @php
                        $row_count = count($items);
                        $mid_idx = (int) floor(($row_count - 1) / 2);
                    @endphp
                    <tbody style="page-break-inside: avoid;">
                        @foreach($items as $index => $item)
                            @php
                                $qty = $item->qty ?? 0;
                                $cons = round($item->cons ?? 0, 3);

                                $color_class = 'color-cell-middle';
                                if ($row_count == 1) { $color_class = 'color-cell-single'; }
                                elseif ($index == 0) { $color_class = 'color-cell-first'; }
                                elseif ($index == $row_count - 1) { $color_class = 'color-cell-last'; }
                            @endphp

                            @if($is_fabric)
                                @php
                                    $unit_asli = strtoupper(trim($item->unit ?? '-'));

                                    if (str_contains($unit_asli, 'YARD') || str_contains($unit_asli, 'YRD')) {
                                        $cons_conv = round($cons * 0.9144, 3);
                                        $unit_conv = str_replace(['YARD', 'YRD'], 'MTR', $unit_asli);
                                    } else {
                                        $cons_conv = $cons;
                                        $unit_conv = $unit_asli;
                                    }

                                    $total = round($qty * $cons_conv, 3);
                                @endphp
                                <tr>
                                    <td class="text-left fw-bold {{ $color_class }}">
                                        {{ $index === $mid_idx ? $color_name : '' }}
                                    </td>
                                    <td>{{ $item->shell ?? '-' }}</td>
                                    <td class="text-left">{{ $item->item_name ?? '-' }}</td>
                                    <td class="text-center">{{ $item->description ?? '-' }}</td>
                                    <td>{{ $item->size_gmt ?? '-' }}</td>
                                    <td>{{ $item->color_item ?? '-' }}</td>
                                    <td class="text-right">{{ $qty > 0 ? number_format($qty, 0, ',', '.') : '-' }}</td>
                                    <td class="text-right">{{ $cons > 0 ? number_format($cons, 3, '.', ',') : '-' }}</td>
                                    <td>{{ $item->unit ?? '-' }}</td>
                                    <td class="text-right">{{ $cons_conv > 0 ? number_format($cons_conv, 3, '.', ',') : '-' }}</td>
                                    <td>{{ $unit_conv }}</td>
                                    <td class="text-right fw-bold">{{ $total > 0 ? number_format($total, 3, '.', ',') : '-' }}</td>
                                    <td>{{ $unit_conv }}</td>
                                </tr>
                            @else
                                @php
                                    $total = round($qty * $cons, 3);
                                @endphp
                                <tr>
                                    <td class="text-left fw-bold {{ $color_class }}">
                                        {{ $index === $mid_idx ? $color_name : '' }}
                                    </td>
                                    <td class="text-left">{{ $item->item_name ?? '-' }}</td>
                                    <td class="text-center">{{ $item->description ?? '-' }}</td>
                                    <td>{{ $item->color_item ?? '-' }}</td>
                                    <td>{{ $item->size_item ?? '-' }}</td>
                                    <td class="text-right">{{ $qty > 0 ? number_format($qty, 0, ',', '.') : '-' }}</td>
                                    <td class="text-right">{{ $cons > 0 ? number_format($cons, 3, '.', ',') : '-' }}</td>
                                    <td>{{ $item->unit ?? '-' }}</td>
                                    <td class="text-right fw-bold">{{ $total > 0 ? number_format($total, 3, '.', ',') : '-' }}</td>
                                    <td>{{ $item->unit ?? '-' }}</td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                @endforeach
            @else
                <tbody>
                    <tr>
                        <td colspan="{{ $is_fabric ? 13 : 10 }}" class="text-center" style="color: black;">
                            Tidak ada data {{ $g_name }}
                        </td>
                    </tr>
                </tbody>
            @endif
        </table>
    @endforeach

    @php
        $has_mfg_data = isset($materials_manufacturing) && count($materials_manufacturing) > 0;
        $avoid_break_mfg = !$has_mfg_data ? 'page-break-inside: avoid;' : '';
    @endphp
    <table class="table-data" style="margin-bottom: 20px; {{ $avoid_break_mfg }}">
        <thead>
            <tr>
                <th colspan="10" class="text-left" style="background-color: #e9ecef; font-size: 11px; padding: 6px;">
                    MANUFACTURING
                </th>
            </tr>
            <tr>
                <th width="10%">COLOR</th>
                <th width="25%">ITEM</th>
                <th width="15%">DESCRIPTION</th>
                <th width="10%">COLOR ITEM</th>
                <th width="10%">SIZE ITEM</th>
                <th width="5%">QTY</th>
                <th width="5%">CONS</th>
                <th width="5%">UNIT</th>
                <th width="6%">TOTAL</th>
                <th width="6%">UNIT</th>
            </tr>
        </thead>
        @if($has_mfg_data)
            @foreach($materials_manufacturing as $color_name => $items)
                @php
                    $row_count = count($items);
                    $mid_idx = (int) floor(($row_count - 1) / 2);
                @endphp
                <tbody style="page-break-inside: avoid;">
                    @foreach($items as $index => $item)
                        @php
                            $qty = $item->qty ?? 0;
                            $cons = round($item->cons ?? 0, 3);

                            $total = round($qty * $cons, 3);

                            $color_class = 'color-cell-middle';
                            if ($row_count == 1) { $color_class = 'color-cell-single'; }
                            elseif ($index == 0) { $color_class = 'color-cell-first'; }
                            elseif ($index == $row_count - 1) { $color_class = 'color-cell-last'; }
                        @endphp
                        <tr>
                            <td class="text-left fw-bold {{ $color_class }}">
                                {{ $index === $mid_idx ? $color_name : '' }}
                            </td>
                            <td class="text-left">{{ $item->item_name ?? '-' }}</td>
                            <td class="text-center">{{ $item->description ?? '-' }}</td>
                            <td>{{ $item->color_item ?? '-' }}</td>
                            <td>{{ $item->size_item ?? '-' }}</td>
                            <td class="text-right">{{ $qty > 0 ? number_format($qty, 0, ',', '.') : '-' }}</td>
                            <td class="text-right">{{ $cons > 0 ? number_format($cons, 3, '.', ',') : '-' }}</td>
                            <td>{{ $item->unit ?? '-' }}</td>
                            <td class="text-right fw-bold">{{ $total > 0 ? number_format($total, 3, '.', ',') : '-' }}</td>
                            <td>{{ $item->unit ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            @endforeach
        @else
            <tbody>
                <tr>
                    <td colspan="10" class="text-center" style="color: black;">
                        Tidak ada data Manufacturing
                    </td>
                </tr>
            </tbody>
        @endif
    </table>
</body>
</html>
