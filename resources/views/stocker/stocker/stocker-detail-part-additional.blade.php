<div class="accordion mb-3" id="accordionPanelsStayOpenExample1">
    @php
        $indexAdditional;
        $partIndexAdditional;
    @endphp

    @foreach ($dataPartDetailAdditional as $partDetailAdd)
        @php
            $generatableAdd = true;
        @endphp
        <div class="accordion-item">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="accordion-header w-75">
                    <button class="accordion-button accordion-sb collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen1-{{ $indexAdditional }}" aria-expanded="false" aria-controls="panelsStayOpen1-collapseTwo">
                        <div class="d-flex w-75 justify-content-between align-items-center">
                            <p class="w-25 mb-0">{{ $partDetailAdd->nama_part." - ".$partDetailAdd->bag }}</p>
                            <p class="w-50 mb-0">{{ $partDetailAdd->tujuan." - ".$partDetailAdd->proses }}</p>
                        </div>
                    </button>
                </h2>
                <div class="accordion-header-side col-3">
                    <div class="form-check ms-3">
                        <input class="form-check-input generate-stocker-check-add generate-add-{{ $partDetailAdd->id }}" type="checkbox" id="generate_add{{ $partIndexAdditional }}" name="generate_stocker_add[{{ $partIndexAdditional }}]" data-group="generate-add-{{ $partDetailAdd->id }}" value="{{ $partDetailAdd->id }}" onchange="massChange(this)" disabled>
                        <label class="form-check-label fw-bold text-sb">
                            Generate Stocker Additional
                        </label>
                    </div>
                </div>
            </div>
            <div id="panelsStayOpen1-{{ $indexAdditional }}" class="accordion-collapse collapse">
                <div class="accordion-body">
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
                                        <input type="hidden" name="part_detail_id_add[{{ $indexAdditional }}]" id="part_detail_id_add_{{ $indexAdditional }}" value="{{ $partDetailAdd->id }}">
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
                        <button type="button" class="btn btn-sm btn-danger fw-bold float-end mb-3" onclick="printStockerAllSizeAdd('{{ $partDetailAdd->id }}', '{{ $currentGroupAdditional }}', '{{ $currentTotalAdditional }}');" {{ $generatableAdd ? '' : 'disabled' }}>Generate All Size <i class="fas fa-print"></i></button>
                        <input type="hidden" class="generatable" name="generatable_add[{{ $partIndexAdditional }}]" id="generatable_add_{{ $partIndexAdditional }}" data-group="{{ $partDetailAdd->id }}" value="{{ $generatableAdd }}">
                    </div>
                </div>
            </div>
        </div>

        @php
            $partIndexAdditional++;
        @endphp
    @endforeach
</div>
