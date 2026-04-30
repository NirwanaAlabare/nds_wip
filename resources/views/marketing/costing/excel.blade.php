<?php
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=\"Costing_".$costing->no_costing.".xls\"");
header("Cache-Control: max-age=0");
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
</head>
<body style="font-family: Arial, sans-serif; font-size: 12px;">

    <table border="0" style="width: 100%;">
        <tr>
            <td colspan="2" rowspan="2" style="vertical-align: middle; text-align: center;">
                <img src="{{ asset('assets/dist/img/nag-logo.png') }}" width="120" height="60">
            </td>
            <td colspan="13" style="font-size: 16px; font-weight: bold; text-align: center; vertical-align: bottom;">PT NIRWANA ALABARE GARMENT</td>
        </tr>
        <tr>
            <td colspan="13" style="font-size: 14px; font-weight: bold; text-align: center; vertical-align: top;">COSTING</td>
        </tr>
        <tr><td colspan="15"></td></tr>
    </table>

    <table border="0" style="width: 100%;">
        <tr>
            <td style="font-weight: bold;">No Costing</td>
            <td colspan="3">: {{ $costing->no_costing }}</td>
            <td style="font-weight: bold;">Style</td>
            <td colspan="3">: {{ $costing->style }}</td>
            <td style="font-weight: bold;">Ship Mode</td>
            <td colspan="3">: {{ $costing->nama_ship_mode ?? $costing->ship_mode }}</td>

            <td colspan="3" rowspan="5" style="text-align: center; vertical-align: middle; border: 1px solid #000;">
                @if(!empty($costing->foto))
                    <img src="{{ asset('uploads/costing/' . $costing->foto) }}" width="150" height="100">
                @else
                    NO IMAGE
                @endif
            </td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Buyer</td>
            <td colspan="3">: {{ $costing->nama_buyer }}</td>
            <td style="font-weight: bold;">SMV</td>
            <td colspan="3">: {{ $costing->smv }}</td>
            <td style="font-weight: bold;">Shipment Type</td>
            <td colspan="3">: {{ strtoupper($costing->shipment_type) }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Brand</td>
            <td colspan="3">: {{ $costing->brand }}</td>
            <td style="font-weight: bold;">Qty (PCS)</td>
            <td colspan="3">: {{ $costing->qty }}</td>
            <td style="font-weight: bold;">Rate to IDR</td>
            <td colspan="3">: {{ $costing->rate_to_idr }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Product Group</td>
            <td colspan="3">: {{ $costing->product_group }}</td>
            <td style="font-weight: bold;">Dest</td>
            <td colspan="3">: {{ $costing->nama_dest ?? $costing->main_dest }}</td>
            <td style="font-weight: bold;">Rate from IDR</td>
            <td colspan="3">: {{ $costing->rate_from_idr }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Product Item</td>
            <td colspan="3">: {{ $costing->nama_product_item ?? $costing->product_item }}</td>
            <td style="font-weight: bold;">Market</td>
            <td colspan="3">: {{ $costing->market }}</td>
            <td style="font-weight: bold;">VAT</td>
            <td colspan="3">: {{ $costing->vat }} %</td>
        </tr>
        <tr><td colspan="15"></td></tr>
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

        foreach($categories as $key => $title) {
            if(isset($details[$key]) && count($details[$key]) > 0) {
                foreach($details[$key] as $det) {
                    if ($key == 'Fabric') {
                        $sum_fab_idr += $det->value_idr; $sum_fab_usd += $det->value_usd;
                    } elseif ($key == 'Accessories Sewing') {
                        $sum_sew_idr += $det->value_idr; $sum_sew_usd += $det->value_usd;
                    } elseif ($key == 'Accessories Packing') {
                        $sum_pack_idr += $det->value_idr; $sum_pack_usd += $det->value_usd;
                    } elseif ($key == 'Manufacturing') {
                        $sum_mfg_idr += $det->value_idr; $sum_mfg_usd += $det->value_usd;
                    } elseif ($key == 'Other Cost') {
                        if (str_contains(strtoupper($det->nama_item), 'OVERHEAD')) {
                            $overhead_row = $det;
                        } else {
                            $sum_oth_norm_idr += $det->value_idr; $sum_oth_norm_usd += $det->value_usd;
                        }
                    }
                }
            }
        }

        $base_overhead_idr = $sum_fab_idr + $sum_sew_idr + $sum_pack_idr + $sum_mfg_idr + $sum_oth_norm_idr;
        $base_overhead_usd = $sum_fab_usd + $sum_sew_usd + $sum_pack_usd + $sum_mfg_usd + $sum_oth_norm_usd;

        $overhead_idr = 0; $overhead_usd = 0;
        if ($overhead_row) {
            $oh_allow = $overhead_row->allowance > 0 ? $overhead_row->allowance : 6;
            $overhead_idr = $base_overhead_idr * ($oh_allow / 100);
            $overhead_usd = $base_overhead_usd * ($oh_allow / 100);
            $overhead_row->value_idr = $overhead_idr;
            $overhead_row->value_usd = $overhead_usd;
        }

        $tot_other_idr = $sum_oth_norm_idr + $overhead_idr;
        $tot_other_usd = $sum_oth_norm_usd + $overhead_usd;
        $base_ga_idr = $sum_fab_idr + $sum_sew_idr + $sum_pack_idr + $sum_mfg_idr + $tot_other_idr;
        $base_ga_usd = $sum_fab_usd + $sum_sew_usd + $sum_pack_usd + $sum_mfg_usd + $tot_other_usd;
        $ga_idr = $base_ga_idr * 0.03;
        $ga_usd = $base_ga_usd * 0.03;
        $grand_idr = $base_ga_idr + $ga_idr;
        $rate_from_idr = $costing->rate_from_idr > 0 ? $costing->rate_from_idr : 15000;
        $grand_usd = $grand_idr / $rate_from_idr;
        $pembagi_persen = $grand_idr;
    @endphp

    @foreach($categories as $key => $title)
        @php
            $sub_idr = 0; $sub_usd = 0; $sum_val = 0;
        @endphp

        <table><tr><td></td></tr></table> @if($key !== 'Other Cost')
            <table border="1" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th colspan="15" bgcolor="#343a40" style="color: white; text-align: left; padding: 5px;">{{ $title }}</th>
                    </tr>
                    <tr bgcolor="#f2f2f2" style="font-weight: bold; text-align: center;">
                        <th width="3%">NO</th>
                        <th width="15%">ITEM</th>
                        <th width="7%">SET</th>
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
                            @endphp
                            <tr>
                                <td style="text-align: center;">{{ $idx + 1 }}</td>
                                <td>{{ $det->nama_item ?? '-' }}</td>
                                <td>{{ $det->nama_set ?? '-' }}</td>
                                <td>{{ $det->item_desc ?? '-' }}</td>
                                <td>{{ $det->nama_supplier ?? '-' }}</td>
                                <td style="text-align: right;">{{ round($det->price_px_idr, 2) }}</td>
                                <td style="text-align: right;">{{ round($det->price_px_usd, 4) }}</td>
                                <td style="text-align: center;">{{ round($det->cons, 4) }}</td>
                                <td style="text-align: center;">{{ $det->unit }}</td>
                                <td style="text-align: center;">{{ round($det->allowance, 2) }}</td>
                                <td style="text-align: right; font-weight: bold;">{{ round($det->value_idr, 2) }}</td>
                                <td style="text-align: right; font-weight: bold;">{{ round($det->value_usd, 4) }}</td>
                                <td style="text-align: center;">{{ round($persen, 2) }}%</td>
                                <td style="text-align: center;">{{ $qty_bom }}</td>
                                <td style="text-align: right;">{{ round($tot_val, 2) }}</td>
                            </tr>
                        @endforeach
                    @else
                        <tr><td colspan="15" style="text-align: center; color: #666;">Tidak ada data {{ $title }}</td></tr>
                    @endif
                </tbody>
                <tfoot>
                    @php $sub_persen = $pembagi_persen > 0 ? ($sub_idr / $pembagi_persen) * 100 : 0; @endphp
                    <tr bgcolor="#f2f2f2" style="font-weight: bold;">
                        <td colspan="10" style="text-align: left;">TOTAL {{ $title }} :</td>
                        <td style="text-align: right;">{{ round($sub_idr, 2) }}</td>
                        <td style="text-align: right;">{{ round($sub_usd, 4) }}</td>
                        <td style="text-align: center;">{{ round($sub_persen, 2) }}%</td>
                        <td></td>
                        <td style="text-align: right;">{{ round($sum_val, 2) }}</td>
                    </tr>
                </tfoot>
            </table>

        @else
            <table border="1" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th colspan="6" bgcolor="#343a40" style="color: white; text-align: left; padding: 5px;">{{ $title }}</th>
                        <th colspan="9" style="border:none;"></th>
                    </tr>
                    <tr bgcolor="#f2f2f2" style="font-weight: bold; text-align: center;">
                        <td width="5%">NO</td>
                        <td width="40%">ITEM</td>
                        <td width="10%">ALLOW %</td>
                        <td width="20%">VALUE IDR</td>
                        <td width="20%">VALUE USD</td>
                        <td width="5%">%</td>
                        <td colspan="9" style="border:none;"></td>
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
                                <td style="text-align: center;">{{ $idx + 1 }}</td>
                                <td>{{ $det->nama_item ?? '-' }}</td>
                                <td style="text-align: center;">{{ round($det->allowance, 2) }}</td>
                                <td style="text-align: right; font-weight: bold;">{{ round($det->value_idr, 2) }}</td>
                                <td style="text-align: right; font-weight: bold;">{{ round($det->value_usd, 4) }}</td>
                                <td style="text-align: center;">{{ round($persen, 2) }}%</td>
                                <td colspan="9" style="border:none;"></td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="6" style="text-align: center; color: #666;">Tidak ada data {{ $title }}</td>
                            <td colspan="9" style="border:none;"></td>
                        </tr>
                    @endif
                </tbody>
                <tfoot>
                    @php $sub_persen = $pembagi_persen > 0 ? ($sub_idr / $pembagi_persen) * 100 : 0; @endphp
                    <tr bgcolor="#f2f2f2" style="font-weight: bold;">
                        <td colspan="3" style="text-align: left;">TOTAL OTHER COST :</td>
                        <td style="text-align: right;">{{ round($sub_idr, 2) }}</td>
                        <td style="text-align: right;">{{ round($sub_usd, 4) }}</td>
                        <td style="text-align: center;">{{ round($sub_persen, 2) }}%</td>
                        <td colspan="9" style="border:none;"></td>
                    </tr>
                </tfoot>
            </table>
        @endif
    @endforeach

    @php
        $actual_vat = strtolower($costing->shipment_type) == 'export' ? 0 : $costing->vat;
        $vat_multiplier = 1 + ($actual_vat / 100);
        $vat_idr = $grand_idr * $vat_multiplier;
        $vat_usd = $grand_usd * $vat_multiplier;
        $profit_idr = $vat_idr * 1.06;
        $profit_usd = $vat_usd * 1.06;
        $ga_pct = $grand_idr > 0 ? ($ga_idr / $grand_idr) * 100 : 0;
    @endphp

    <table><tr><td></td></tr></table>

    <table border="1" style="width: 100%; border-collapse: collapse;">
        <tr>
            <td colspan="10" style="border:none;"></td>
            <td colspan="2" bgcolor="#f2f2f2" style="text-align: center; font-weight: bold;"></td>
            <td bgcolor="#f2f2f2" style="text-align: center; font-weight: bold;">VALUE IDR</td>
            <td bgcolor="#f2f2f2" style="text-align: center; font-weight: bold;">VALUE USD</td>
            <td bgcolor="#f2f2f2" style="text-align: center; font-weight: bold;">%</td>
        </tr>
        <tr>
            <td colspan="10" style="border:none;"></td>
            <td colspan="2" bgcolor="#f2f2f2" style="text-align: left; font-weight: bold;">G&A (3%)</td>
            <td style="text-align: right;">{{ round($ga_idr, 2) }}</td>
            <td style="text-align: right;">{{ round($ga_usd, 4) }}</td>
            <td style="text-align: center;">{{ round($ga_pct, 2) }}%</td>
        </tr>
        <tr>
            <td colspan="10" style="border:none;"></td>
            <td colspan="2" bgcolor="#f2f2f2" style="text-align: left; font-weight: bold;">TOTAL COST</td>
            <td style="text-align: right; font-weight: bold; color: red;">{{ round($grand_idr, 2) }}</td>
            <td style="text-align: right; font-weight: bold; color: red;">{{ round($grand_usd, 4) }}</td>
            <td style="text-align: center;"></td>
        </tr>
        <tr>
            <td colspan="10" style="border:none;"></td>
            <td colspan="2" bgcolor="#f2f2f2" style="text-align: left; font-weight: bold;">VAT ({{ $actual_vat }}%)</td>
            <td style="text-align: right;">{{ round($vat_idr, 2) }}</td>
            <td style="text-align: right;">{{ round($vat_usd, 4) }}</td>
            <td style="text-align: center;"></td>
        </tr>
        <tr>
            <td colspan="10" style="border:none;"></td>
            <td colspan="2" bgcolor="#f2f2f2" style="text-align: left; font-weight: bold;">PROFIT (6%)</td>
            <td style="text-align: right;">{{ round($profit_idr, 2) }}</td>
            <td style="text-align: right;">{{ round($profit_usd, 4) }}</td>
            <td style="text-align: center;"></td>
        </tr>
    </table>

</body>
</html>
