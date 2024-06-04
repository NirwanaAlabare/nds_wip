<div class="accordion mb-3" id="accordionPanelsStayOpenExample">
    @php
        $index;
        $partIndex;
    @endphp

    @foreach ($dataPartDetail as $partDetail)
        @php
            $generatable = true;
        @endphp
        <div class="accordion-item">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="accordion-header w-75">
                    <button class="accordion-button accordion-sb collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-{{ $index }}" aria-expanded="false" aria-controls="panelsStayOpen-collapseTwo">
                        <div class="d-flex w-75 justify-content-between align-items-center">
                            <p class="w-25 mb-0">{{ $partDetail->nama_part." - ".$partDetail->bag }}</p>
                            <p class="w-50 mb-0">{{ $partDetail->tujuan." - ".$partDetail->proses }}</p>
                        </div>
                    </button>
                </h2>
                <div class="accordion-header-side col-3">
                    <div class="form-check ms-3">
                        <input class="form-check-input generate-stocker-check generate-{{ $partDetail->id }}" type="checkbox" id="generate_{{ $partIndex }}" name="generate_stocker[{{ $partIndex }}]" data-group="generate-{{ $partDetail->id }}" value="{{ $partDetail->id }}" onchange="massChange(this)" disabled>
                        <label class="form-check-label fw-bold text-sb">
                            Generate Stocker
                        </label>
                    </div>
                </div>
            </div>
            <div id="panelsStayOpen-{{ $index }}" class="accordion-collapse collapse">
                <div class="accordion-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm" id="table-ratio-{{ $index }}">
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
                                @foreach ($dataRatio as $ratio)
                                    @php
                                        $qty = intval($ratio->ratio) * intval($currentTotal);
                                        $qtyBefore = intval($ratio->ratio) * intval($currentBefore);

                                        if (isset($modifySizeQtyStocker) && $modifySizeQtyStocker) {
                                            $modifyThisStocker = $modifySizeQtyStocker->where("so_det_id", $ratio->so_det_id)->first();

                                            if ($modifyThisStocker) {
                                                $qty = $qty + $modifyThisStocker->difference_qty;
                                            }
                                        }

                                        if ($qty > 0) :
                                            $stockerThis = $dataStocker ? $dataStocker->where("so_det_id", $ratio->so_det_id)->where("no_cut", $dataSpreading->no_cut)->where('ratio', '>', '0')->first() : null;

                                            $stockerBefore = $dataStocker ? $dataStocker->where("so_det_id", $ratio->so_det_id)->where("no_cut", "<", $dataSpreading->no_cut)->sortByDesc('no_cut')->where('ratio', '>', '0')->first() : null;

                                            $rangeAwal = ($dataSpreading->no_cut > 1 ? ($stockerBefore ? ($stockerBefore->stocker_id != null ? $stockerBefore->range_akhir + 1 + ($qtyBefore) : "-") : 1 + ($qtyBefore)) : 1 + ($qtyBefore));
                                            $rangeAkhir = ($dataSpreading->no_cut > 1 ? ($stockerBefore ? ($stockerBefore->stocker_id != null ? $stockerBefore->range_akhir + $qty + ($qtyBefore) : "-") : $qty + ($qtyBefore)) : $qty + ($qtyBefore));
                                    @endphp
                                    <tr>
                                        <input type="hidden" name="part_detail_id[{{ $index }}]" id="part_detail_id_{{ $index }}" value="{{ $partDetail->id }}">
                                        <input type="hidden" name="ratio[{{ $index }}]" id="ratio_{{ $index }}" value="{{ $ratio->ratio }}">
                                        <input type="hidden" name="so_det_id[{{ $index }}]" id="so_det_id_{{ $index }}" value="{{ $ratio->so_det_id }}">
                                        <input type="hidden" name="size[{{ $index }}]" id="size_{{ $index }}" value="{{ $ratio->size }}">
                                        <input type="hidden" name="group[{{ $index }}]" id="group_{{ $index }}" value="{{ $currentGroup }}">
                                        <input type="hidden" name="group_stocker[{{ $index }}]" id="group_stocker_{{ $index }}" value="{{ $currentGroupStocker }}">
                                        <input type="hidden" name="qty_ply_group[{{ $index }}]" id="qty_ply_group_{{ $index }}" value="{{ $currentTotal }}">
                                        <input type="hidden" name="qty_cut[{{ $index }}]" id="qty_cut_{{ $index }}" value="{{ $qty }}">
                                        <input type="hidden" name="range_awal[{{ $index }}]" id="range_awal_{{ $index }}" value="{{ $rangeAwal }}">
                                        <input type="hidden" name="range_akhir[{{ $index }}]" id="range_akhir_{{ $index }}" value="{{ $rangeAkhir }}">

                                        <td>{{ $ratio->size}}</td>
                                        <td>{{ $ratio->ratio }}</td>
                                        <td>{{ (intval($ratio->ratio) * intval($currentTotal)) != $qty ? $qty." (".(intval($ratio->ratio) * intval($currentTotal))."".(($qty - (intval($ratio->ratio) * intval($currentTotal))) > 0 ? "+".($qty - (intval($ratio->ratio) * intval($currentTotal))) : ($qty - (intval($ratio->ratio) * intval($currentTotal)))).")" : $qty }}</td>
                                        <td>{{ $rangeAwal }}</td>
                                        <td>{{ $rangeAkhir }}</td>
                                        <td>
                                            @if ($dataSpreading->no_cut > 1)
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
                                            <button type="button" class="btn btn-sm btn-danger" onclick="printStocker({{ $index }});" {{ ($dataSpreading->no_cut > 1 ? ($stockerBefore ? ($stockerBefore->stocker_id != null ? "" : "disabled") : "") : "") }}>
                                                <i class="fa fa-print fa-s"></i>
                                            </button>
                                        </td>
                                        {{-- <td>
                                            <button type="button" class="btn btn-sm btn-danger" onclick="printNumbering({{ $index }});" {{ ($dataSpreading->no_cut > 1 ? ($stockerBefore ? ($stockerBefore->stocker_id != null ? "" : "disabled") : "") : "") }}>
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
                        <button type="button" class="btn btn-sm btn-danger fw-bold float-end mb-3" onclick="printStockerAllSize('{{ $partDetail->id }}', '{{ $currentGroup }}', '{{ $currentTotal }}');" {{ $generatable ? '' : 'disabled' }}>Generate All Size <i class="fas fa-print"></i></button>
                        <input type="hidden" class="generatable" name="generatable[{{ $partIndex }}]" id="generatable_{{ $partIndex }}" data-group="{{ $partDetail->id }}" value="{{ $generatable }}">
                    </div>
                </div>
            </div>
        </div>

        @php
            $partIndex++;
        @endphp
    @endforeach
</div>
