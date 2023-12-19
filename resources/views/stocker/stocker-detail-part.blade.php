@if (isset($dataPartDetail) && isset($group) && isset($qtyPly) && isset($index))
    <div class="accordion mb-3" id="accordionPanelsStayOpenExample">
        @php
            $thisIndex = $index;
        @endphp

        @foreach ($dataPartDetail as $partDetail)
            @php
                $generatable = true;
            @endphp
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button accordion-sb collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-{{ $thisIndex }}" aria-expanded="false" aria-controls="panelsStayOpen-collapseTwo">
                        {{ $partDetail->nama_part }}
                    </button>
                </h2>
                <div id="panelsStayOpen-{{ $thisIndex }}" class="accordion-collapse collapse">
                    <div class="accordion-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm" id="table-ratio-{{ $thisIndex }}">
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
                                            $qty = intval($ratio->ratio) * intval($qtyPly);

                                            $stockerThis = $dataStocker ? $dataStocker->where("part_detail_id", $partDetail->id)->where("so_det_id", $ratio->so_det_id)->where("no_cut", $dataSpreading->no_cut)->where("color", $dataSpreading->color)->where("shade", $group)->where("qty_ply", $qtyPly)->where("ratio", ">", "0")->first() : null;
                                            $stockerBefore = $dataStocker ? $dataStocker->where("part_detail_id", $partDetail->id)->where("so_det_id", $ratio->so_det_id)->where("no_cut", "<", $dataSpreading->no_cut)->where("color", $dataSpreading->color)->where("shade", $group)->where("qty_ply", $qtyPly)->where("ratio", ">", "0")->sortByDesc('no_cut')->sortByDesc('range_akhir')->first() : null;
                                            $rangeAwal = ($dataSpreading->no_cut > 1 ? ($stockerBefore ? ($stockerBefore->stocker_id != null ? $stockerBefore->range_akhir + 1 : "-") : 1) : 1);
                                            $rangeAkhir = ($dataSpreading->no_cut > 1 ? ($stockerBefore ? ($stockerBefore->stocker_id != null ? $stockerBefore->range_akhir + $qty : "-") : $qty) : $qty);
                                        @endphp
                                        <tr>
                                            <input type="hidden" name="part_detail_id[{{ $thisIndex }}]" id="part_detail_id_{{ $thisIndex }}" value="{{ $partDetail->id }}">
                                            <input type="hidden" name="ratio[{{ $thisIndex }}]" id="ratio_{{ $thisIndex }}" value="{{ $ratio->ratio }}">
                                            <input type="hidden" name="so_det_id[{{ $thisIndex }}]" id="so_det_id_{{ $thisIndex }}" value="{{ $ratio->so_det_id }}">
                                            <input type="hidden" name="size[{{ $thisIndex }}]" id="size_{{ $thisIndex }}" value="{{ $ratio->size }}">
                                            <input type="hidden" name="group[{{ $thisIndex }}]" id="group_{{ $thisIndex }}" value="{{ $group }}">
                                            <input type="hidden" name="group_stocker[{{ $thisIndex }}]" id="group_stocker_{{ $thisIndex }}" value="{{ $groupStocker }}">
                                            <input type="hidden" name="qty_ply_group[{{ $thisIndex }}]" id="qty_ply_group_{{ $thisIndex }}" value="{{ $qtyPly }}">
                                            <input type="hidden" name="qty_cut[{{ $thisIndex }}]" id="qty_cut_{{ $thisIndex }}" value="{{ $qty }}">
                                            <input type="hidden" name="range_awal[{{ $thisIndex }}]" id="range_awal_{{ $thisIndex }}" value="{{ $rangeAwal }}">
                                            <input type="hidden" name="range_akhir[{{ $thisIndex }}]" id="range_akhir_{{ $thisIndex }}" value="{{ $rangeAkhir }}">

                                            <td>{{ $ratio->size}}</td>
                                            <td>{{ $ratio->ratio }}</td>
                                            <td>{{ $qty }}</td>
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
                                                <button type="button" class="btn btn-sm btn-danger" onclick="printStocker({{ $thisIndex }});" {{ ($dataSpreading->no_cut > 1 ? ($stockerBefore ? ($stockerBefore->stocker_id != null ? "" : "disabled") : "") : "") }}>
                                                    <i class="fa fa-print fa-s"></i>
                                                </button>
                                            </td>
                                            {{-- <td>
                                                <button type="button" class="btn btn-sm btn-danger" onclick="printNumbering({{ $thisIndex }});" {{ ($dataSpreading->no_cut > 1 ? ($stockerBefore ? ($stockerBefore->stocker_id != null ? "" : "disabled") : "") : "") }}>
                                                    <i class="fa fa-print fa-s"></i>
                                                </button>
                                            </td> --}}
                                        </tr>
                                        @php
                                            $thisIndex++;
                                        @endphp
                                    @endforeach
                                </tbody>
                            </table>
                            <button type="button" class="btn btn-sm btn-danger fw-bold float-end mb-3" onclick="printStockerAllSize('{{ $partDetail->id }}', '{{ $group }}', '{{ $qtyPly }}');" {{ $generatable ? '' : 'disabled' }}>Generate All Size <i class="fas fa-print"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif
