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
            @foreach ($currentDetail as $detail)
                @php
                    $qty = intval(1) * intval($detail->qty);

                    $beforeQty = isset($currentBeforeSeparate[$detail->so_det_id]) ? $currentBeforeSeparate[$detail->so_det_id] : 0;

                    if ($qty > 0) :
                        $stockerThis = $dataStocker ? $dataStocker->where("so_det_id", $detail->so_det_id)->where("no_cut", $dataSpreading->no_cut)->first() : null;
                        $stockerBefore = $dataStocker ? $dataStocker->where("so_det_id", $detail->so_det_id)->where("no_cut", "<", $dataSpreading->no_cut)->sortBy([['no_cut', 'desc'],['range_akhir', 'desc']])->first() : null;

                        $rangeAwal = ($dataSpreading->no_cut > 1 ? ($stockerBefore ? ($stockerBefore->stocker_id != null ? $stockerBefore->range_akhir + 1 + $beforeQty : "-") : 1 + $beforeQty) : 1 + $beforeQty);
                        $rangeAkhir = ($dataSpreading->no_cut > 1 ? ($stockerBefore ? ($stockerBefore->stocker_id != null ? $stockerBefore->range_akhir + $qty + $beforeQty : "-") : $qty + $beforeQty) : $qty + $beforeQty);

                        $separatedStocker = $dataStockerSeparate ? $dataStockerSeparate->where("so_det_id", $detail->so_det_id)->where("group_roll", $currentGroupSeparate)->where("group_stocker", $currentGroupStockerSeparate)->first() : null;
                        $separatedStockerDetails = $separatedStocker ? $separatedStocker->stockerSeparateDetails : null;
                @endphp
                <tr>
                    <input type="hidden" name="ratio[{{ $indexSeparate }}]" id="ratio_{{ $indexSeparate }}" value="{{ 1 }}">
                    <input type="hidden" name="so_det_id[{{ $indexSeparate }}]" id="so_det_id_{{ $indexSeparate }}" value="{{ $detail->so_det_id }}">
                    <input type="hidden" name="size[{{ $indexSeparate }}]" id="size_{{ $indexSeparate }}" value="{{ $detail->size }}">
                    <input type="hidden" name="group[{{ $indexSeparate }}]" id="group_{{ $indexSeparate }}" value="{{ $currentGroupSeparate }}">
                    <input type="hidden" name="group_stocker[{{ $indexSeparate }}]" id="group_stocker_{{ $indexSeparate }}" value="{{ $currentGroupStockerSeparate }}">
                    <input type="hidden" name="qty_ply_group[{{ $indexSeparate }}]" id="qty_ply_group_{{ $indexSeparate }}" value="{{ $qty }}">
                    <input type="hidden" name="qty_cut[{{ $indexSeparate }}]" id="qty_cut_{{ $indexSeparate }}" value="{{ $qty }}">
                    <input type="hidden" name="range_awal[{{ $indexSeparate }}]" id="range_awal_{{ $indexSeparate }}" value="{{ $rangeAwal }}">
                    <input type="hidden" name="range_akhir[{{ $indexSeparate }}]" id="range_akhir_{{ $indexSeparate }}" value="{{ $rangeAkhir }}">

                    <td>{{ $detail->size_dest }}</td>
                    <td>{{ 1 }}</td>
                    <td>{{ $qty }}</td>
                    <td>{{ $separatedStockerDetails ? $separatedStockerDetails->implode("qty", " | ") : "-" }}</td>
                    <td>{{ $rangeAwal }}</td>
                    <td>{{ $rangeAkhir }}</td>
                    <td>
                        <div id="separate_qty_wrapper_{{ $indexSeparate }}"  class="d-flex flex-wrap gap-1"  data-qty="{{ $qty }}">
                            @if ($separatedStockerDetails)
                                @foreach ($separatedStockerDetails as $separatedStockerDetail)
                                    <input type="number" class="form-control form-control-sm separate-part" name="separate_qty[{{ $indexSeparate }}][]" value="{{ $separatedStockerDetail->qty }}" onkeyup="validateAndAdjust({{ $indexSeparate }}, this)">
                                @endforeach
                            @else
                                <input type="number" class="form-control form-control-sm separate-part" name="separate_qty[{{ $indexSeparate }}][]" value="{{ $qty }}" onkeyup="validateAndAdjust({{ $indexSeparate }}, this)">
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

                        $currentBeforeSeparate->put($detail->so_det_id, $rangeAkhir);
                    endif;
                @endphp
            @endforeach
        </tbody>
    </table>
</div>
