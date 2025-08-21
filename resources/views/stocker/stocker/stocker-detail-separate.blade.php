<div class="table-responsive">
    <table class="table table-bordered table" id="table-ratio-separate-{{ $indexSeparate }}">
        <thead>
            <th>Size</th>
            <th>Ratio</th>
            <th>Qty Cut</th>
            <th>Separated</th>
            <th>Range Awal</th>
            <th>Range Akhir</th>
            <th>Separate Qty</th>
        </thead>
        <tbody>
            @foreach ($dataRatio as $ratio)
                @php
                    $qty = intval($ratio->ratio) * intval($currentTotalSeparate);
                    $qtyBefore = intval($ratio->ratio) * intval($currentBeforeSeparate);

                    if (isset($modifySizeQtyStocker) && $modifySizeQtyStocker) {
                        $modifyThisStocker = $modifySizeQtyStocker->where("so_det_id", $ratio->so_det_id)->first();

                        if ($modifyThisStocker) {
                            $qty = $qty + $modifyThisStocker->difference_qty;
                        }
                    }

                    if ($qty > 0) :
                        $stockerThis = $dataStocker ? $dataStocker->where("so_det_id", $ratio->so_det_id)->where("no_cut", $dataSpreading->no_cut)->where('ratio', '>', '0')->first() : null;
                        $stockerBefore = $dataStocker ? $dataStocker->where("so_det_id", $ratio->so_det_id)->where("no_cut", "<", $dataSpreading->no_cut)->sortBy([['no_cut', 'desc'],['range_akhir', 'desc']])->filter(function ($item) { return $item->ratio > 0 && ($item->difference_qty > 0 || $item->difference_qty == null); })->first() : null;

                        $rangeAwal = ($dataSpreading->no_cut > 1 ? ($stockerBefore ? ($stockerBefore->stocker_id != null ? $stockerBefore->range_akhir + 1 + ($qtyBefore) : "-") : 1 + ($qtyBefore)) : 1 + ($qtyBefore));
                        $rangeAkhir = ($dataSpreading->no_cut > 1 ? ($stockerBefore ? ($stockerBefore->stocker_id != null ? $stockerBefore->range_akhir + $qty + ($qtyBefore) : "-") : $qty + ($qtyBefore)) : $qty + ($qtyBefore));

                        $separatedStocker = $dataStockerSeparate ? $dataStockerSeparate->where("so_det_id", $ratio->so_det_id)->where("group_roll", $currentGroupSeparate)->where("group_stocker", $currentGroupStockerSeparate)->first() : null;
                        $separatedStockerDetails = $separatedStocker ? $separatedStocker->stockerSeparateDetails : null;
                @endphp
                <tr>
                    <input type="hidden" name="ratio[{{ $indexSeparate }}]" id="ratio_{{ $indexSeparate }}" value="{{ $ratio->ratio }}">
                    <input type="hidden" name="so_det_id[{{ $indexSeparate }}]" id="so_det_id_{{ $indexSeparate }}" value="{{ $ratio->so_det_id }}">
                    <input type="hidden" name="size[{{ $indexSeparate }}]" id="size_{{ $indexSeparate }}" value="{{ $ratio->size }}">
                    <input type="hidden" name="group[{{ $indexSeparate }}]" id="group_{{ $indexSeparate }}" value="{{ $currentGroupSeparate }}">
                    <input type="hidden" name="group_stocker[{{ $indexSeparate }}]" id="group_stocker_{{ $indexSeparate }}" value="{{ $currentGroupStockerSeparate }}">
                    <input type="hidden" name="qty_ply_group[{{ $indexSeparate }}]" id="qty_ply_group_{{ $indexSeparate }}" value="{{ $currentTotalSeparate }}">
                    <input type="hidden" name="qty_cut[{{ $indexSeparate }}]" id="qty_cut_{{ $indexSeparate }}" value="{{ $qty }}">
                    <input type="hidden" name="range_awal[{{ $indexSeparate }}]" id="range_awal_{{ $indexSeparate }}" value="{{ $rangeAwal }}">
                    <input type="hidden" name="range_akhir[{{ $indexSeparate }}]" id="range_akhir_{{ $indexSeparate }}" value="{{ $rangeAkhir }}">

                    <td>{{ $ratio->size_dest }}</td>
                    <td>{{ $ratio->ratio }}</td>
                    <td>{{ (intval($ratio->ratio) * intval($currentTotalSeparate)) != $qty ? $qty." (".(intval($ratio->ratio) * intval($currentTotalSeparate))."".(($qty - (intval($ratio->ratio) * intval($currentTotalSeparate))) > 0 ? "+".($qty - (intval($ratio->ratio) * intval($currentTotalSeparate))) : ($qty - (intval($ratio->ratio) * intval($currentTotalSeparate)))).")" : $qty }}</td>
                    <td>{{ $separatedStockerDetails ? $separatedStockerDetails->implode("qty", " | ") : "-" }}</td>
                    <td>{{ $rangeAwal }}</td>
                    <td>{{ $rangeAkhir }}</td>
                    <td>
                        <div id="separate_qty_wrapper_{{ $indexSeparate }}" class="d-flex flex-wrap gap-1"  data-qty="{{ $qty }}">
                            @if ($separatedStockerDetails)
                                @foreach ($separatedStockerDetails as $separatedStockerDetail)
                                    <input type="number" class="form-control form-control-sm separate-part" name="separate_qty[{{ $indexSeparate }}][]" value="{{ $separatedStockerDetail->qty }}" oninput="validateAndAdjust({{ $indexSeparate }}, this)" onchange="validateAndAdjust({{ $indexSeparate }}, this)" onkeyup="validateAndAdjust({{ $indexSeparate }}, this)" min="0">
                                @endforeach
                            @else
                                @for ($i = 0; $i < $ratio->ratio; $i++)
                                    <input type="number" class="form-control form-control-sm separate-part" name="separate_qty[{{ $indexSeparate }}][]" value="{{ $qty/$ratio->ratio }}" oninput="validateAndAdjust({{ $indexSeparate }}, this)" onchange="validateAndAdjust({{ $indexSeparate }}, this)" onkeyup="validateAndAdjust({{ $indexSeparate }}, this)" min="0">
                                @endfor
                            @endif
                        </div>
                        <div class="mt-1">
                            <button type="button" class="btn btn-sm btn-outline-success" onclick="addSeparatePart({{ $indexSeparate }})">+</button>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeSeparatePart({{ $indexSeparate }})">−</button>
                        </div>
                        <small class="text-muted" id="separate_qty_note_{{ $indexSeparate }}"></small>
                    </td>
                </tr>
                @php
                        $indexSeparate++;
                    endif;
                @endphp
            @endforeach
        </tbody>
    </table>
</div>
