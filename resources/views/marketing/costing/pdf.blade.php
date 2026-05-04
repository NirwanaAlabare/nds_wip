<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Costing - {{ $costing->no_costing }}</title>
    <style>
        @page { margin: 20px 30px; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 9px; color: #333; }

        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .table-border th, .table-border td { border: 1px solid #000; padding: 4px; }

        .header-title { font-size: 16px; font-weight: bold; margin-bottom: 10px; text-align: center; }

        .bg-light { background-color: #f2f2f2; }
        .bg-dark { background-color: #343a40; color: #fff; }
        .bg-warning { background-color: #ffc107; }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .fw-bold { font-weight: bold; }

        .w-100 { width: 100%; }
        .w-50 { width: 50%; }
        .img-costing { max-width: 180px; height: auto; max-height: 120px; border: 1px solid #ccc; padding: 2px; }
        .no-image { width: 150px; height: 100px; border: 1px dashed #999; display: inline-block; text-align: center; line-height: 100px; color: #999; font-size: 10px; }
        .layout-table td { border: none; padding: 2px 5px; vertical-align: top; }

         { color: #6c757d; }
         { color: #007bff; }
    </style>
</head>
<body>

    <table style="margin-bottom: 15px; border-bottom: 2px solid #000; padding-bottom: 10px;">
        <tr>
            <td style="width: 15%; vertical-align: middle;">
                <img src="{{ public_path('assets/dist/img/nag-logo.png') }}" style="max-width: 120px; max-height: 60px; object-fit: contain;">
            </td>
            <td style="width: 85%; vertical-align: center; text-align: center;">
                <h5 style="margin: 0; font-size: 20px;">PT NIRWANA ALABARE GARMENT</h5>
                <h5 style="margin: 0; font-size: 18px;">COSTING</h5>
            </td>
        </tr>
    </table>

    <table class="layout-table" style="margin-bottom: 15px; width: 100%;">
        <tr>
            <td class="fw-bold" style="width: 1%; white-space: nowrap;">No Costing</td>
            <td style="width: 24%;">: {{ $costing->no_costing }}</td>

            <td class="fw-bold" style="width: 1%; white-space: nowrap;">Style</td>
            <td style="width: 24%;">: {{ $costing->style }}</td>

            <td class="fw-bold" style="width: 1%; white-space: nowrap;">Ship Mode</td>
            <td style="width: 24%;">: {{ $costing->nama_ship_mode ?? $costing->ship_mode }}</td>

            <td rowspan="5" style="width: 25%; text-align: right; vertical-align: top; padding: 0;">
                @if(!empty($costing->foto))
                    <img src="{{ public_path('uploads/costing/' . $costing->foto) }}" class="img-costing" alt="Gambar costing">
                @else
                    <div class="no-image">No Image</div>
                @endif
            </td>
        </tr>
        <tr>
            <td class="fw-bold">Buyer</td>
            <td>: {{ $costing->nama_buyer }}</td>
            <td class="fw-bold">SMV</td>
            <td>: {{ number_format($costing->smv, 2) }}</td>
            <td class="fw-bold" style="white-space: nowrap;">Shipment Type</td>
            <td>: {{ strtoupper($costing->shipment_type) }}</td>
        </tr>
        <tr>
            <td class="fw-bold">Brand</td>
            <td>: {{ $costing->brand }}</td>
            <td class="fw-bold" style="white-space: nowrap;">Qty (PCS)</td>
            <td>: {{ number_format($costing->qty, 0, ',', '.') }}</td>
            <td class="fw-bold" style="white-space: nowrap;">Rate to IDR</td>
            <td>: {{ number_format($costing->rate_to_idr, 2, ',', '.') }}</td>
        </tr>
        @php
            $set_string = '-';

            if (strtolower($costing->type) === 'multiple' && !empty($costing->product_set)) {
                $saved_sets = array_map('trim', explode(',', $costing->product_set));
                $active_set_names = [];

                foreach ($master_set as $m_set) {
                    if (in_array($m_set->id, $saved_sets)) {
                        $active_set_names[] = strtoupper($m_set->nama ?? $m_set->id);
                    }
                }

                if (count($active_set_names) > 0) {
                    $set_string = implode(', ', $active_set_names);
                }
            }
        @endphp
        <tr>
            <td class="fw-bold" style="white-space: nowrap;">Product Group</td>
            <td>: {{ $costing->product_group }}</td>
            <td class="fw-bold">Type</td>
            <td>: {{ strtoupper($costing->type) }}</td>
            <td class="fw-bold" style="white-space: nowrap;">Rate from IDR</td>
            <td>: {{ number_format($costing->rate_from_idr, 2, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="fw-bold" style="white-space: nowrap;">Product Item</td>
            <td>: {{ $costing->nama_product_item ?? $costing->product_item }}</td>
            <td class="fw-bold">Set</td>
            <td>: {{ $set_string }}</td>
            <td class="fw-bold">VAT</td>
            <td>: {{ number_format($costing->vat, 2) }} %</td>
        </tr>
    </table>

    @php
        $categories = [
            'Fabric' => 'FABRIC',
            'Accessories Sewing' => 'ACCESSORIES SEWING',
            'Accessories Packing' => 'ACCESSORIES PACKING',
            'Manufacturing' => 'MANUFACTURING',
            'Other Cost' => 'OTHER COST'
        ];

        $sum_fab_idr = 0; $sum_sew_idr = 0; $sum_pack_idr = 0; $sum_mfg_idr = 0; $sum_oth_norm_idr = 0;
        $sum_fab_usd = 0; $sum_sew_usd = 0; $sum_pack_usd = 0; $sum_mfg_usd = 0; $sum_oth_norm_usd = 0;

        $overhead_row = null;
        $rate_from_idr = $costing->rate_from_idr > 0 ? $costing->rate_from_idr : 15000;


        $saved_sets = $costing->product_set ? explode(',', $costing->product_set) : [];
        $saved_sets = array_map('trim', $saved_sets);

        $active_sets = [];
        foreach ($master_set as $m_set) {
            if (in_array($m_set->id, $saved_sets)) {
                $active_sets[] = strtoupper($m_set->nama ?? $m_set->id);
            }
        }

        // Inisialisasi angka 0 agar Set-nya tetap ke-print walau isinya kosong
        $cat_set_totals = [];
        $grand_set_totals = ['bom' => 0, 'val' => 0, 'sets' => []];
        foreach(['Fabric', 'Accessories Sewing', 'Accessories Packing', 'Manufacturing'] as $k) {
            foreach($active_sets as $s) {
                $cat_set_totals[$k][$s] = ['idr' => 0, 'usd' => 0, 'bom' => 0, 'val' => 0];
            }
        }
        foreach($active_sets as $s) {
            $grand_set_totals['sets'][$s] = ['bom' => 0, 'val' => 0];
        }

        // ===================================================================
        // 2. HITUNG MATERIAL (Akumulasi sesuai SET)
        // ===================================================================
        foreach(['Fabric', 'Accessories Sewing', 'Accessories Packing', 'Manufacturing'] as $key) {
            if(isset($details[$key]) && count($details[$key]) > 0) {
                foreach($details[$key] as $det) {

                    // Ambil Set
                    $set_name = strtoupper(trim($det->nama_set ?? ''));

                    // Hitung BOM & Value per baris
                    $allow = $det->allowance > 0 ? $det->allowance : 0;
                    $qty_bom = ceil((1 + ($allow / 100)) * $costing->qty * $det->cons);
                    $tot_val = $qty_bom * $det->price_px_idr;

                    // Akumulasi Total Kategori
                    if ($key == 'Fabric') {
                        $sum_fab_idr += $det->value_idr; $sum_fab_usd += $det->value_usd;
                    } elseif ($key == 'Accessories Sewing') {
                        $sum_sew_idr += $det->value_idr; $sum_sew_usd += $det->value_usd;
                    } elseif ($key == 'Accessories Packing') {
                        $sum_pack_idr += $det->value_idr; $sum_pack_usd += $det->value_usd;
                    } elseif ($key == 'Manufacturing') {
                        $sum_mfg_idr += $det->value_idr; $sum_mfg_usd += $det->value_usd;
                    }

                    // Akumulasi Data Dinamis Per Set
                    if ($set_name && in_array($set_name, $active_sets)) {
                        $cat_set_totals[$key][$set_name]['idr'] += $det->value_idr;
                        $cat_set_totals[$key][$set_name]['usd'] += $det->value_usd;
                        $cat_set_totals[$key][$set_name]['bom'] += $qty_bom;
                        $cat_set_totals[$key][$set_name]['val'] += $tot_val;

                        $grand_set_totals['sets'][$set_name]['bom'] += $qty_bom;
                        $grand_set_totals['sets'][$set_name]['val'] += $tot_val;
                    }

                    $grand_set_totals['bom'] += $qty_bom;
                    $grand_set_totals['val'] += $tot_val;
                }
            }
        }

        $base_material_idr = $sum_fab_idr + $sum_sew_idr + $sum_pack_idr;
        $base_material_usd = $sum_fab_usd + $sum_sew_usd + $sum_pack_usd;

        if(isset($details['Other Cost']) && count($details['Other Cost']) > 0) {
            foreach($details['Other Cost'] as $det) {
                if (str_contains(strtoupper($det->nama_item), 'OVERHEAD')) {
                    $overhead_row = $det;
                } else {
                    $det->value_usd = $det->value_idr / $rate_from_idr;
                    $sum_oth_norm_idr += $det->value_idr;
                    $sum_oth_norm_usd += $det->value_usd;
                }
            }
        }

        // 4. Base Overhead (Base Material + Other Cost Normal)
        $base_overhead_idr = $base_material_idr + $sum_oth_norm_idr;
        $base_overhead_usd = $base_material_usd + $sum_oth_norm_usd;

        $overhead_idr = 0; $overhead_usd = 0;
        if ($overhead_row) {
            $oh_allow = $overhead_row->allowance > 0 ? $overhead_row->allowance : 6;

            $overhead_idr = $base_overhead_idr * ($oh_allow / 100);
            $overhead_usd = $base_overhead_usd * ($oh_allow / 100);

            // Update row obj agar di tabel HTML tercetak benar
            $overhead_row->value_idr = $overhead_idr;
            $overhead_row->value_usd = $overhead_usd;
        }

        $tot_other_idr = $sum_oth_norm_idr + $overhead_idr;
        $tot_other_usd = $sum_oth_norm_usd + $overhead_usd;

        // 5. Hitung Grand Total Cost Akhir
        $base_ga_idr = $base_material_idr + $sum_mfg_idr + $tot_other_idr;
        $base_ga_usd = $base_material_usd + $sum_mfg_usd + $tot_other_usd;

        $ga_idr = $base_ga_idr * 0.03;
        $ga_usd = $base_ga_usd * 0.03;

        $grand_idr = $base_ga_idr + $ga_idr;
        $grand_usd = $grand_idr / $rate_from_idr;

        $pembagi_persen = $grand_idr;

        //

        $actual_vat = strtolower($costing->shipment_type) == 'export' ? 0 : $costing->vat;
        $vat_multiplier = 1 + ($actual_vat / 100);

        $vat_idr = $grand_idr * $vat_multiplier;
        $vat_usd = $grand_usd * $vat_multiplier;

        $profit_idr = $vat_idr * 1.06;
        $profit_usd = $vat_usd * 1.06;

        $ga_pct = $grand_idr > 0 ? ($ga_idr / $grand_idr) * 100 : 0;
    @endphp


    @foreach($categories as $key => $title)
        @php
            $sub_idr = 0;
            $sub_usd = 0;
            $sum_val = 0;
            $sub_bom = 0;
        @endphp

        <div class="fw-bold" style="margin-top: 10px; margin-bottom: 3px; font-size: 10px;">{{ $title }}</div>



        @if($key !== 'Other Cost')
            <table class="table-border w-100">
                <thead class="bg-light text-center">
                    <tr>
                        <th width="3%">NO</th>
                        <th width="15%">ITEM</th>
                        <th width="6%">SET</th>
                        <th width="12%">DESC</th>
                        <th width="8%">SUPPLIER</th>
                        <th width="8%">PRICE IDR</th>
                        <th width="8%">PRICE USD</th>
                        <th width="6%">CONS/PC</th>
                        <th width="4%">UNIT</th>
                        <th width="4%">ALLOW %</th>
                        <th width="9%">VALUE IDR</th>
                        <th width="9%">VALUE USD</th>
                        <th width="5%">%</th>
                        <th width="4%">QTY BOM</th>
                        <th width="5%">VALUE</th>
                    </tr>
                </thead>
                <tbody>
                    @if(isset($details[$key]) && count($details[$key]) > 0)
                        @foreach($details[$key] as $idx => $det)
                            @php
                                $sub_idr += $det->value_idr;
                                $sub_usd += $det->value_usd;

                                $persen = $pembagi_persen > 0 ? ($det->value_idr / $pembagi_persen) * 100 : 0;

                                $allow = $det->allowance > 0 ? $det->allowance : 0;
                                $qty_bom = ceil((1 + ($allow / 100)) * $costing->qty * $det->cons);
                                $tot_val = $qty_bom * $det->price_px_idr;

                                $sum_val += $tot_val;
                                $sub_bom += $qty_bom;
                            @endphp
                            <tr>
                                <td class="text-center">{{ $idx + 1 }}</td>
                                <td>{{ $det->nama_item ?? '-' }}</td>
                                <td>{{ $det->nama_set ?? '-' }}</td>
                                <td>{{ $det->item_desc ?? '-' }}</td>
                                <td>{{ $det->nama_supplier ?? '-' }}</td>
                                <td class="text-right">{{ number_format($det->price_px_idr, 2) }}</td>
                                <td class="text-right">{{ number_format($det->price_px_usd, 4) }}</td>
                                <td class="text-center">{{ number_format($det->cons, 4) }}</td>
                                <td class="text-center">{{ $det->unit }}</td>
                                <td class="text-center">{{ number_format($det->allowance, 2) }}</td>
                                <td class="text-right fw-bold">{{ number_format($det->value_idr, 2) }}</td>
                                <td class="text-right fw-bold">{{ number_format($det->value_usd, 4) }}</td>
                                <td class="text-center">{{ number_format($persen, 2) }}%</td>
                                <td class="text-center">{{ number_format($qty_bom, 0, ',', '.') }}</td>
                                <td class="text-right">{{ number_format($tot_val, 2) }}</td>
                            </tr>
                        @endforeach
                    @else
                        <tr><td colspan="15" class="text-center text-muted">Tidak ada data {{ $title }}</td></tr>
                    @endif
                </tbody>
                <tfoot>
                    @php
                        $sub_persen = $pembagi_persen > 0 ? ($sub_idr / $pembagi_persen) * 100 : 0;
                    @endphp

                    @foreach($active_sets as $s)
                        @if(isset($cat_set_totals[$key][$s]))
                            @php $d = $cat_set_totals[$key][$s]; @endphp
                            <tr style="background-color: #f8f9fa;">
                                <td colspan="10" class="text-left">{{ $title }} - TOTAL {{ $s }} :</td>
                                <td class="text-right">{{ number_format($d['idr'], 2) }}</td>
                                <td class="text-right">{{ number_format($d['usd'], 4) }}</td>
                                <td></td>
                                <td class="text-center">{{ number_format($d['bom'], 0, ',', '.') }}</td>
                                <td class="text-right">{{ number_format($d['val'], 2) }}</td>
                            </tr>
                        @endif
                    @endforeach

                   <tr class="bg-light fw-bold">
                        <td colspan="10" class="text-left">TOTAL {{ $title }} :</td>
                        <td class="text-right">{{ number_format($sub_idr, 2) }}</td>
                        <td class="text-right">{{ number_format($sub_usd, 4) }}</td>
                        <td></td>
                        <td class="text-center">{{ number_format($sub_bom, 0, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($sum_val, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        @else
            <table class="table-border w-100" style="width: 70%;">
                <thead class="bg-light text-center">
                    <tr>
                        <th width="5%">NO</th>
                        <th width="40%">ITEM</th>
                        <th width="10%">ALLOW %</th>
                        <th width="20%">VALUE IDR</th>
                        <th width="20%">VALUE USD</th>
                        <th width="5%">%</th>
                    </tr>
                </thead>
                <tbody>
                    @if(isset($details[$key]) && count($details[$key]) > 0)
                        @foreach($details[$key] as $idx => $det)
                            @php
                                $sub_idr += $det->value_idr;
                                $sub_usd += $det->value_usd;
                                $persen = $pembagi_persen > 0 ? ($det->value_idr / $pembagi_persen) * 100 : 0;
                            @endphp
                            <tr>
                                <td class="text-center">{{ $idx + 1 }}</td>
                                <td>{{ $det->nama_item ?? '-' }}</td>
                                <td class="text-center">{{ number_format($det->allowance > 0 ? $det->allowance : (str_contains(strtoupper($det->nama_item), 'OVERHEAD') ? 6 : 0), 2) }}</td>
                                <td class="text-right fw-bold">{{ number_format($det->value_idr, 6, '.', ',') }}</td>
                                <td class="text-right fw-bold">{{ number_format($det->value_usd, 4, '.', ',') }}</td>
                                <td class="text-center">{{ number_format($persen, 2) }}%</td>
                            </tr>
                        @endforeach
                    @else
                        <tr><td colspan="6" class="text-center text-muted">Tidak ada data {{ $title }}</td></tr>
                    @endif
                </tbody>
                <tfoot>
                    @php
                        $sub_persen = $pembagi_persen > 0 ? ($sub_idr / $pembagi_persen) * 100 : 0;
                    @endphp
                    <tr class="bg-light fw-bold">
                        <td colspan="3" class="text-left">TOTAL OTHER COST :</td>
                        <td class="text-right">{{ number_format($sub_idr, 6, '.', ',') }}</td>
                        <td class="text-right">{{ number_format($sub_usd, 4, '.', ',') }}</td>
                        <td class="text-center">{{ number_format($sub_persen, 2) }}%</td>
                    </tr>
                     <tr class="bg-light fw-bold">
                        <td colspan="3" class="text-left">G&A (3%)</td>
                        <td class="text-right">{{ number_format($ga_idr, 6, '.', ',') }}</td>
                        <td class="text-right">{{ number_format($ga_usd, 4, '.', ',') }}</td>
                        <td class="text-center">{{ number_format($ga_pct, 2) }}%</td>
                    </tr>
                </tfoot>
            </table>
        @endif
    @endforeach

    {{-- TABEL SUMMARY GRAND TOTAL (BARU DITAMBAHKAN) --}}
    <table class="table-border w-100" style="margin-top: 15px; font-size: 11px;">
        <tr style="background-color: #f2f2f2;">
            <td width="40%"></td>
            <td width="30%" class="text-center fw-bold">TOTAL QTY BOM</td>
            <td width="30%" class="text-center fw-bold">TOTAL VALUE</td>
        </tr>
        @if(isset($grand_set_totals['sets']))
            @foreach($active_sets as $s)
                @if(isset($grand_set_totals['sets'][$s]))
                    @php $d = $grand_set_totals['sets'][$s]; @endphp
                    <tr style="background-color: #f8f9fa; font-weight: bold;">
                        <td>TOTAL {{ $s }}</td>
                        <td class="text-center">{{ number_format($d['bom'], 0, ',', '.') }}</td>
                        <td class="text-center">{{ number_format($d['val'], 2) }}</td>
                    </tr>
                @endif
            @endforeach
        @endif
        <tr class="fw-bold">
            <td>GRAND TOTAL</td>
            <td class="text-center">{{ number_format($grand_set_totals['bom'], 0, ',', '.') }}</td>
            <td class="text-center">{{ number_format($grand_set_totals['val'], 2) }}</td>
        </tr>

    </table>

    <hr style="border: 1px solid #333; margin-top: 15px; margin-bottom: 15px;">

    <table style="width: 50%; float: right;" class="table-border">
         <tr>
            <td width="50%" class="fw-bold bg-light"></td>
            <td width="25%" class="text-center fw-bold">VALUE IDR</td>
            <td width="25%" class="text-center fw-bold">VALUE USD</td>
        </tr>

        <tr>
            <td class="fw-bold bg-light">TOTAL COST</td>
            <td class="text-right">{{ number_format($grand_idr, 6) }}</td>
            <td class="text-right">{{ number_format($grand_usd, 4) }}</td>
        </tr>
        <tr>
            <td class="fw-bold bg-light">VAT ({{ $actual_vat }}%)</td>
            <td class="text-right">{{ number_format($vat_idr, 6) }}</td>
            <td class="text-right">{{ number_format($vat_usd, 4) }}</td>
        </tr>
        <tr>
            <td class="fw-bold bg-light">PROFIT (6%)</td>
            <td class="text-right">{{ number_format($profit_idr, 2) }}</td>
            <td class="text-right">{{ number_format($profit_usd, 4) }}</td>
        </tr>
    </table>

    <table class="layout-table" style="width: 45%; float: left; margin-top: 30px; font-size: 10px;">
        <tr>
            <td class="fw-bold" style="width: 8%;">Created by</td>
            <td class="fw-bold" style="width: 35%;">: {{ $costing->created_by ?? '()' }} - ({{ $costing->created_at ? date('d/m/Y H:i', strtotime($costing->created_at)) : '(time)' }})</td>
        </tr>
        <tr>
            <td class="fw-bold">Approved by</td>
            <td class="fw-bold">: </td>
        </tr>
    </table>

    <div style="clear: both;"></div>

</body>
</html>
