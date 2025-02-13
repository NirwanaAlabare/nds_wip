@php
    $indexAdditional;

    $generatable = true;
@endphp
<div class="table-responsive">
    <table class="table table-bordered table-sm" id="table-ratio-{{ $indexAdditional }}">
        <thead>
            <th>Size</th>
            <th>Ratio</th>
            <th>Qty Cut</th>
            <th>Range Awal</th>
            <th>Range Akhir</th>
            <th class="d-none">Print Stocker</th>
            {{-- <th>Print Numbering</th> --}}
        </thead>
        <tbody>
            @foreach ($dataRatioAdditional as $ratio)
                @php
                    $qtyAdditional = intval($ratio->ratio) * intval($currentTotalAdditional);
                    $qtyBeforeAdditional = intval($ratio->ratio) * intval($currentBeforeAdditional);

                    if (isset($modifySizeQtyStocker) && $modifySizeQtyStocker) {
                        $modifyThisStocker = $modifySizeQtyStocker->where("so_det_id", $ratio->so_det_id)->first();

                        if ($modifyThisStocker) {
                            $qtyAdditional = $qtyAdditional + $modifyThisStocker->difference_qty;
                        }
                    }

                    if ($qtyAdditional > 0) :
                    $rangeAwalAdditional = 1 + $qtyBeforeAdditional;
                    $rangeAkhirAdditional = $qtyBeforeAdditional + $qtyAdditional;
                @endphp
                <tr>
                    <input type="hidden" name="ratio_add[{{ $indexAdditional }}]" id="ratio_add_{{ $indexAdditional }}" value="{{ $ratio->ratio }}">
                    <input type="hidden" name="so_det_id_add[{{ $indexAdditional }}]" id="so_det_id_add_{{ $indexAdditional }}" value="{{ $ratio->so_det_id }}">
                    <input type="hidden" name="size_add[{{ $indexAdditional }}]" id="size_add_{{ $indexAdditional }}" value="{{ $ratio->size }}">
                    <input type="hidden" name="group_add[{{ $indexAdditional }}]" id="group_add_{{ $indexAdditional }}" value="{{ $currentGroupAdditional }}">
                    <input type="hidden" name="group_stocker_add[{{ $indexAdditional }}]" id="group_stocker_add_{{ $indexAdditional }}" value="{{ $currentGroupStockerAdditional }}">
                    <input type="hidden" name="qty_ply_group_add[{{ $indexAdditional }}]" id="qty_ply_group_add_{{ $indexAdditional }}" value="{{ $currentTotalAdditional }}">
                    <input type="hidden" name="qty_cut_add[{{ $indexAdditional }}]" id="qty_cut_add_{{ $indexAdditional }}" value="{{ $qtyAdditional }}">
                    <input type="hidden" name="range_awal_add[{{ $indexAdditional }}]" id="range_awal_add_{{ $indexAdditional }}" value="{{ $rangeAwalAdditional }}">
                    <input type="hidden" name="range_akhir_add[{{ $indexAdditional }}]" id="range_akhir_add_{{ $indexAdditional }}" value="{{ $rangeAkhirAdditional }}">

                    <td>{{ $ratio->size_dest }}</td>
                    <td>{{ $ratio->ratio }}</td>
                    <td>{{ (intval($ratio->ratio) * intval($currentTotalAdditional)) != $qtyAdditional ? $qtyAdditional." (".(intval($ratio->ratio) * intval($currentTotalAdditional))."".(($qtyAdditional - (intval($ratio->ratio) * intval($currentTotalAdditional))) > 0 ? "+".($qtyAdditional - (intval($ratio->ratio) * intval($currentTotalAdditional))) : ($qtyAdditional - (intval($ratio->ratio) * intval($currentTotalAdditional)))).")" : $qtyAdditional }}</td>
                    <td>{{ $rangeAwalAdditional }}</td>
                    <td>{{ $rangeAkhirAdditional }}</td>
                    <td class="d-none">
                        <button type="button" class="btn btn-sm btn-danger" onclick="printStockerAdditional({{ $indexAdditional }});">
                            <i class="fa fa-print fa-s"></i>
                        </button>
                    </td>
                </tr>
                @php
                        $indexAdditional++;
                    endif;
                @endphp
            @endforeach
        </tbody>
    </table>
    <button type="button" class="btn btn-sm btn-danger fw-bold float-end mb-3" onclick="printStockerAllSizeAdd();" {{ $generatable ? '' : 'disabled' }}>Generate All Size <i class="fas fa-print"></i></button>
    <input type="hidden" class="generatable" name="generatable[{{ $indexAdditional }}]" id="generatable_{{ $indexAdditional }}" data-group="{{ $indexAdditional }}" value="{{ $generatable }}">
</div>
