<div class="accordion mb-3" id="accordionPanelsStayOpenExampleCom-{{ $i }}">
    @php
        $index;
        $partIndex;
    @endphp

    @foreach ($currentDataPartDetail as $partDetail)
        @php
            $generatable = true;
        @endphp
        <div class="accordion-item">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="accordion-header w-75">
                    <button class="accordion-button accordion-sb collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpenCom-{{ $i }}-{{ $index }}" aria-expanded="false" aria-controls="panelsStayOpen-collapseTwo">
                        <div class="d-flex w-75 justify-content-between align-items-center">
                            <p class="w-25 mb-0">{{ $partDetail->nama_part." - ".$partDetail->bag }}</p>
                            <p class="w-50 mb-0">{{ $partDetail->proses_tujuan }}</p>
                        </div>
                    </button>
                </h2>
                <div class="accordion-header-side col-3">
                    <div class="form-check ms-3">
                        <input class="form-check-input generate-stocker-check-com-{{ $i }} generate-{{ $partDetail->id }}" type="checkbox" id="generate_com_{{ $i }}_{{ $partIndex }}" name="generate_stocker[{{ $partIndex }}]" data-group="generate-{{ $partDetail->id }}" value="{{ $partDetail->id }}" onchange="massChange(this)" disabled>
                        <label class="form-check-label fw-bold text-sb">
                            Generate Stocker
                        </label>
                    </div>
                </div>
            </div>
            <div id="panelsStayOpenCom-{{ $i }}-{{ $index }}" class="accordion-collapse collapse">
                <div class="accordion-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table" id="table-ratio-com-{{ $i }}-{{ $index }}">
                            <thead>
                                <th>Size</th>
                                <th>Ratio</th>
                                <th>Qty Cut</th>
                                <th>Range Awal</th>
                                <th>Range Akhir</th>
                                <th>Generated</th>
                                <th>Print Stocker</th>
                                {{-- <th>Print Numbering</th> --}}
                            </thead>
                            <tbody>
                                @foreach ($currentDataRatio as $ratio)
                                    @php
                                        $currentModify = $currentModifySize ? $currentModifySize->where("group_stocker", "!=", $currentGroupStocker)->where("so_det_id", $ratio->so_det_id)->first() : null;

                                        $qty = intval($ratio->ratio) * intval($currentTotal);
                                        $qtyBefore = (intval($ratio->ratio) * intval($currentBefore)) + ($currentModify ? $currentModify['difference_qty'] : 0);
                                    @endphp

                                    @php
                                        if (isset($modifySizeQtyStocker) && $modifySizeQtyStocker) {
                                            $modifyThisStocker = null;
                                            if ($currentModifySizeQty > 0) {
                                                $modifyThisStocker = $modifySizeQtyStocker->where("group_stocker", $currentGroupStocker)->where("so_det_id", $ratio->so_det_id)->first();
                                            } else {
                                                $modifyThisStocker = $modifySizeQtyStocker->where("so_det_id", $ratio->so_det_id)->first();
                                            }

                                            if ($modifyThisStocker) {
                                                $qty = $qty + $modifyThisStocker->difference_qty;

                                                $currentModifySize->push(["so_det_id" => $ratio->so_det_id, "group_stocker" => $currentGroupStocker, "difference_qty" => $modifyThisStocker->difference_qty]);
                                            }
                                        }

                                        if ($qty > 0) :
                                            $stockerThis = $currentDataStocker ? $currentDataStocker->where("so_det_id", $ratio->so_det_id)->where("no_cut", $currentDataSpreading->no_cut)->where('ratio', '>', '0')->first() : null;
                                            $stockerBefore = $currentDataStocker ? $currentDataStocker->where("so_det_id", $ratio->so_det_id)->where("no_cut", "<", $currentDataSpreading->no_cut)->sortBy([['no_cut', 'desc'],['range_akhir', 'desc']])->filter(function ($item) { return $item->ratio > 0 && ($item->difference_qty > 0 || $item->difference_qty == null); })->first() : null;

                                            // dd($stockerBefore);

                                            $rangeAwal = ($currentDataSpreading->no_cut > 1 ? ($stockerBefore ? ($stockerBefore->stocker_id != null ? $stockerBefore->range_akhir + 1 + ($qtyBefore) : "-") : 1 + ($qtyBefore)) : 1 + ($qtyBefore));
                                            $rangeAkhir = ($currentDataSpreading->no_cut > 1 ? ($stockerBefore ? ($stockerBefore->stocker_id != null ? $stockerBefore->range_akhir + $qty + ($qtyBefore) : "-") : $qty + ($qtyBefore)) : $qty + ($qtyBefore));
                                            if ($currentGroupStocker == '2') {
                                                // dd($rangeAwal, $rangeAkhir, $stockerBefore->range_akhir, $qtyBefore, $qty);
                                            }
                                    @endphp
                                    <tr>
                                        <input type="hidden" name="part_detail_id[{{ $index }}]" id="part_detail_id_com_{{ $i }}_{{ $index }}" value="{{ $partDetail->id }}">
                                        <input type="hidden" name="ratio[{{ $index }}]" id="ratio_com_{{ $i }}_{{ $index }}" value="{{ $ratio->ratio }}">
                                        <input type="hidden" name="so_det_id[{{ $index }}]" id="so_det_id_com_{{ $i }}_{{ $index }}" value="{{ $ratio->so_det_id }}">
                                        <input type="hidden" name="size[{{ $index }}]" id="size_com_{{ $i }}_{{ $index }}" value="{{ $ratio->size }}">
                                        <input type="hidden" name="group[{{ $index }}]" id="group_com_{{ $i }}_{{ $index }}" value="{{ $currentGroup }}">
                                        <input type="hidden" name="group_stocker[{{ $index }}]" id="group_stocker_com_{{ $i }}_{{ $index }}" value="{{ $currentGroupStocker }}">
                                        <input type="hidden" name="qty_ply_group[{{ $index }}]" id="qty_ply_group_com_{{ $i }}_{{ $index }}" value="{{ $currentTotal }}">
                                        <input type="hidden" name="qty_cut[{{ $index }}]" id="qty_cut_com_{{ $i }}_{{ $index }}" value="{{ $qty }}">
                                        <input type="hidden" name="range_awal[{{ $index }}]" id="range_awal_com_{{ $i }}_{{ $index }}" value="{{ $rangeAwal }}">
                                        <input type="hidden" name="range_akhir[{{ $index }}]" id="range_akhir_com_{{ $i }}_{{ $index }}" value="{{ $rangeAkhir }}">

                                        <td>{{ $ratio->size_dest }}</td>
                                        <td>{{ $ratio->ratio }}</td>
                                        <td>{{ (intval($ratio->ratio) * intval($currentTotal)) != $qty ? $qty." (".(intval($ratio->ratio) * intval($currentTotal))."".(($qty - (intval($ratio->ratio) * intval($currentTotal))) > 0 ? "+".($qty - (intval($ratio->ratio) * intval($currentTotal))) : ($qty - (intval($ratio->ratio) * intval($currentTotal)))).")" : $qty }}</td>
                                        <td>{{ $rangeAwal }}</td>
                                        <td>{{ $rangeAkhir }}</td>
                                        <td>
                                            @if ($currentDataSpreading->no_cut > 1)
                                                @if ($stockerBefore)
                                                    @if ($stockerBefore->stocker_id != null)
                                                        @if ($stockerThis && $stockerThis->stocker_id != null)
                                                            <i class="fa fa-check"></i>
                                                        @else
                                                            <i class="fa fa-times"></i>
                                                        @endif
                                                    @else
                                                        @php $generatable = false; @endphp
                                                        <i class="fa fa-minus"></i>
                                                    @endif
                                                @else
                                                    @if ($stockerThis && $stockerThis->stocker_id != null)
                                                        <i class="fa fa-check"></i>
                                                    @else
                                                        <i class="fa fa-times"></i>
                                                    @endif
                                                @endif
                                            @else
                                                @if ($stockerThis && $stockerThis->stocker_id != null)
                                                    <i class="fa fa-check"></i>
                                                @else
                                                    <i class="fa fa-times"></i>
                                                @endif
                                            @endif
                                        <td>
                                            <button type="button" class="btn btn-sm btn-danger" onclick="printStockerCom({{ $i }}, {{ $index }});" {{ ($currentDataSpreading->no_cut > 1 ? ($stockerBefore ? ($stockerBefore->stocker_id != null ? "" : "disabled") : "") : "") }}>
                                                <i class="fa fa-print fa-s"></i>
                                            </button>
                                        </td>
                                        {{-- <td>
                                            <button type="button" class="btn btn-sm btn-danger" onclick="printNumbering({{ $index }});" {{ ($currentDataSpreading->no_cut > 1 ? ($stockerBefore ? ($stockerBefore->stocker_id != null ? "" : "disabled") : "") : "") }}>
                                                <i class="fa fa-print fa-s"></i>
                                            </button>
                                        </td> --}}
                                    </tr>
                                    @php
                                            $index++;
                                        endif;
                                    @endphp
                                @endforeach
                            </tbody>
                        </table>
                        <button type="button" class="btn btn-sm btn-danger fw-bold float-end mb-3" onclick="printStockerAllSizeCom('{{ $i }}', '{{ $partDetail->id }}', '{{ $currentGroup }}', '{{ $currentTotal }}');" {{ $generatable ? '' : 'disabled' }}>Generate All Size <i class="fas fa-print"></i></button>
                        <input type="hidden" class="generatable" name="generatable[{{ $partIndex }}]" id="generatable_com_{{ $i }}_{{ $partIndex }}" data-group="{{ $partDetail->id }}" value="{{ $generatable }}">
                    </div>
                </div>
            </div>
        </div>

        @php
            $partIndex++;
        @endphp
    @endforeach
</div>
