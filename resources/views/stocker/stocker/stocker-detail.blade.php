@extends('layouts.index')

@section('custom-link')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">

    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@endsection

@section('content')
    <div class="card card-sb">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title fw-bold mb-0"><i class="fas fa-receipt fa-sm"></i> Detail Stocker</h5>
                <div>
                    <a href="{{ route('stocker') }}" class="btn btn-primary btn-sm">
                        <i class="fa fa-reply"></i> Kembali ke Stocker
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="d-flex justify-content-end gap-3 mb-3">
                <button type="button" class="btn btn-success btn-sm" onclick="countStockerUpdate()">
                    <i class="fa-solid fa-screwdriver-wrench fa-sm"></i> No. Stocker
                </button>
                <button type="button" class="btn btn-info btn-sm" onclick="rearrangeGroup('{{ $dataSpreading->no_form }}')">
                    <i class="fa-solid fa-screwdriver-wrench fa-sm"></i> Grouping
                </button>
                <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#size-qty-modal">
                    <i class="fa-solid fa-screwdriver-wrench fa-sm"></i> Size Qty
                </button>
            </div>
            <form action="#" method="post" id="stocker-form">
                <div class="row mb-3">
                    <div class="col-6 col-md-3">
                        <div class="mb-1">
                            <label class="form-label"><small>WS Number</small></label>
                            <input type="text" class="form-control form-control-sm" id="no_ws" name="no_ws" value="{{ $dataSpreading->ws }}" readonly>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="mb-1">
                            <label class="form-label"><small>Buyer</small></label>
                            <input type="text" class="form-control form-control-sm" id="buyer" name="buyer" value="{{ $dataSpreading->buyer }}" readonly>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="mb-1">
                            <label class="form-label"><small>Style</small></label>
                            <input type="text" class="form-control form-control-sm" id="style" name="style" value="{{ $dataSpreading->style }}" readonly>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="mb-1">
                            <label class="form-label"><small>Color</small></label>
                            <input type="text" class="form-control form-control-sm" id="color" name="color" value="{{ $dataSpreading->color }}" readonly>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="mb-1">
                            <label class="form-label"><small>Size</small></label>
                            <input type="text" class="form-control form-control-sm" id="size" name="size" value="{{ $dataSpreading->sizes }}" readonly>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="mb-1">
                            <label class="form-label"><small>Panel</small></label>
                            <input type="text" class="form-control form-control-sm" id="panel" name="panel" value="{{ $dataSpreading->panel }}" readonly>
                        </div>
                    </div>
                    <div class="col-6 col-md-6">
                        <div class="row">
                            <div class="col-12 col-md-12">
                                <div class="mb-1">
                                    <label class="form-label"><small>Part</small></label>
                                    <input type="text" class="form-control form-control-sm" id="part" name="part" value="{{ $dataSpreading->part }}" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-6">
                        <div class="row">
                            <div class="col-4 col-md-4">
                                <div class="mb-1">
                                    <label class="form-label"><small>Form Cut</small></label>
                                    <input type="hidden" id="form_cut_id" name="form_cut_id" value="{{ $dataSpreading->form_cut_id }}">
                                    <input type="text" class="form-control form-control-sm" id="no_form_cut" name="no_form_cut" value="{{ $dataSpreading->no_form }}" readonly>
                                </div>
                            </div>
                            <div class="col-4 col-md-4">
                                <div class="mb-1">
                                    <label class="form-label"><small>Total Lembar</small></label>
                                    <input type="text" class="form-control form-control-sm" id="qty_ply_total" name="qty_ply_total" value="{{ $dataSpreading->total_lembar }}" readonly>
                                </div>
                            </div>
                            <div class="col-4 col-md-4">
                                <div class="mb-1">
                                    <label class="form-label"><small>No. Cut</small></label>
                                    <input type="text" class="form-control form-control-sm" id="no_cut" name="no_cut" value="{{ $dataSpreading->no_cut }}" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="mb-1">
                            <label class="form-label"><small>Tanggal Cutting</small></label>
                            <input type="date" class="form-control form-control-sm" id="tgl_form_cut" name="tgl_form_cut" value="{{ $dataSpreading->tgl_form_cut }}" readonly>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="mb-1">
                            <label class="form-label"><small>Catatan</small></label>
                            <textarea class="form-control form-control-sm" id="note" name="note" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="mb-5">
                    <h5 class="fw-bold mb-3 ps-1">Print Stocker</h5>
                    <div class="card">
                        <div class="card-body">
                            @php
                                $index = 0;
                                $partIndex = 0;

                                $currentGroup = "";
                                $currentGroupStocker = 0;
                                $currentTotal = 0;
                                $currentBefore = 0;
                            @endphp
                            @foreach ($dataSpreading->formCutInputDetails->where('status', '!=', 'not complete')->sortByDesc('group_roll')->sortByDesc('group_stocker') as $detail)
                                @if (!$detail->group_stocker)
                                {{-- Without group stocker condition --}}

                                    @if ($loop->first)
                                    {{-- Initial group --}}
                                        @php
                                            $currentGroup = $detail->group_roll;
                                            $currentGroupStocker = $detail->group_stocker;
                                        @endphp
                                    @endif

                                    @if ($detail->group_roll != $currentGroup)
                                        {{-- Create element when switching group --}}
                                        <div class="d-flex gap-3">
                                            <div class="mb-3">
                                                <label><small>Group</small></label>
                                                <input type="text" class="form-control form-control-sm" value="{{ $currentGroup }}" readonly>
                                            </div>
                                            <div class="mb-3">
                                                <label><small>Qty</small></label>
                                                <input type="text" class="form-control form-control-sm" value="{{ $currentTotal }}" readonly>
                                            </div>
                                        </div>

                                        @include('stocker.stocker.stocker-detail-part')
                                        @php
                                            $index += $dataRatio->count() * $dataPartDetail->count();
                                            $partIndex += $dataPartDetail->count();
                                        @endphp

                                        {{-- Change initial group --}}
                                        @php
                                            $currentBefore += $currentTotal;

                                            $currentGroup = $detail->group_roll;
                                            $currentGroupStocker = $detail->group_stocker;
                                            $currentTotal = $detail->lembar_gelaran;
                                        @endphp

                                        @if ($loop->last)
                                            {{-- Create last element when it comes to an end of this loop --}}
                                            <div class="d-flex gap-3">
                                                <div class="mb-3">
                                                    <label><small>Group</small></label>
                                                    <input type="text" class="form-control form-control-sm" value="{{ $currentGroup }}" readonly>
                                                </div>
                                                <div class="mb-3">
                                                    <label><small>Qty</small></label>
                                                    <input type="text" class="form-control form-control-sm" value="{{ $currentTotal }}" readonly>
                                                </div>
                                            </div>

                                            @include('stocker.stocker.stocker-detail-part', ["modifySizeQtyStocker" => $modifySizeQty])
                                            @php
                                                $index += $dataRatio->count() * $dataPartDetail->count();
                                                $partIndex += $dataPartDetail->count();
                                            @endphp
                                        @endif
                                    @else
                                        {{-- Accumulate when it still in the same group --}}
                                        @php
                                            $currentTotal += $detail->lembar_gelaran;
                                        @endphp

                                        {{-- Create last element when it comes to an end of this loop --}}
                                        @if ($loop->last)
                                            <div class="d-flex gap-3">
                                                <div class="mb-3">
                                                    <label><small>Group</small></label>
                                                    <input type="text" class="form-control form-control-sm" value="{{ $currentGroup }}" readonly>
                                                </div>
                                                <div class="mb-3">
                                                    <label><small>Qty</small></label>
                                                    <input type="text" class="form-control form-control-sm" value="{{ $currentTotal }}" readonly>
                                                </div>
                                            </div>

                                            @include('stocker.stocker.stocker-detail-part', ["modifySizeQtyStocker" => $modifySizeQty])
                                            @php
                                                $index += $dataRatio->count() * $dataPartDetail->count();
                                                $partIndex += $dataPartDetail->count();
                                            @endphp
                                        @endif
                                    @endif
                                @else
                                {{-- With group stocker condition --}}

                                    @if ($loop->first)
                                    {{-- Initial Group --}}
                                        @php
                                            $currentGroup = $detail->group_roll;
                                            $currentGroupStocker = $detail->group_stocker;
                                        @endphp
                                    @endif

                                    @if ($detail->group_stocker != $currentGroupStocker)
                                        {{-- Create element when switching group --}}
                                        <div class="d-flex gap-3">
                                            <div class="mb-3">
                                                <label><small>Group</small></label>
                                                <input type="text" class="form-control form-control-sm" value="{{ $currentGroup }}" readonly>
                                            </div>
                                            <div class="mb-3">
                                                <label><small>Qty</small></label>
                                                <input type="text" class="form-control form-control-sm" value="{{ $currentTotal }}" readonly>
                                            </div>
                                        </div>

                                        @include('stocker.stocker.stocker-detail-part')
                                        @php
                                            $index += $dataRatio->count() * $dataPartDetail->count();
                                            $partIndex += $dataPartDetail->count();
                                        @endphp

                                        {{-- Change initial group --}}
                                        @php
                                            $currentBefore += $currentTotal;

                                            $currentGroup = $detail->group_roll;
                                            $currentGroupStocker = $detail->group_stocker;
                                            $currentTotal = $detail->lembar_gelaran;
                                        @endphp

                                        {{-- Create last element when it comes to an end of this loop --}}
                                        @if ($loop->last)
                                            <div class="d-flex gap-3">
                                                <div class="mb-3">
                                                    <label><small>Group</small></label>
                                                    <input type="text" class="form-control form-control-sm" value="{{ $currentGroup }}" readonly>
                                                </div>
                                                <div class="mb-3">
                                                    <label><small>Qty</small></label>
                                                    <input type="text" class="form-control form-control-sm" value="{{ $currentTotal }}" readonly>
                                                </div>
                                            </div>

                                            @include('stocker.stocker.stocker-detail-part', ["modifySizeQtyStocker" => $modifySizeQty])
                                            @php
                                                $index += $dataRatio->count() * $dataPartDetail->count();
                                                $partIndex += $dataPartDetail->count();
                                            @endphp
                                        @endif
                                    @else
                                        {{-- Accumulate when it still in the group --}}
                                        @php
                                            $currentTotal += $detail->lembar_gelaran;
                                        @endphp

                                        @if ($loop->last)
                                        {{-- Create last element when it comes to an end of this loop --}}
                                            <div class="d-flex gap-3">
                                                <div class="mb-3">
                                                    <label><small>Group</small></label>
                                                    <input type="text" class="form-control form-control-sm" value="{{ $currentGroup }}" readonly>
                                                </div>
                                                <div class="mb-3">
                                                    <label><small>Qty</small></label>
                                                    <input type="text" class="form-control form-control-sm" value="{{ $currentTotal }}" readonly>
                                                </div>
                                            </div>

                                            @include('stocker.stocker.stocker-detail-part', ["modifySizeQtyStocker" => $modifySizeQty])
                                            @php
                                                $index += $dataRatio->count() * $dataPartDetail->count();
                                                $partIndex += $dataPartDetail->count();
                                            @endphp
                                        @endif
                                    @endif
                                @endif
                            @endforeach
                        </div>
                        <div class="d-flex justify-content-end p-3">
                            <button type="button" class="btn btn-danger btn-sm mb-3 w-auto" onclick="generateCheckedStocker()"><i class="fa fa-print"></i> Generate Checked Stocker</button>
                        </div>
                    </div>
                </div>
                <div class="mb-5">
                    <h5 class="fw-bold mb-3 ps-1">Print Numbering</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm" id="table-ratio-numbering">
                            <thead>
                                <th>Size</th>
                                <th>Ratio</th>
                                <th>Qty Cut</th>
                                <th>Range Awal</th>
                                <th>Range Akhir</th>
                                <th>Generated</th>
                                <th>Print Numbering</th>
                            </thead>
                            <tbody>
                                @foreach ($dataRatio as $ratio)
                                    @php
                                        $qty = intval($ratio->ratio) * intval($dataSpreading->total_lembar);

                                        if (isset($modifySizeQty) && $modifySizeQty) {
                                            $modifyThis = $modifySizeQty->where("so_det_id", $ratio->so_det_id)->first();

                                            if ($modifyThis) {
                                                $qty=$modifyThis->modified_qty;
                                            }
                                        }

                                        if ($qty > 0) :
                                            $numberingThis = $dataNumbering ? $dataNumbering->where("so_det_id", $ratio->so_det_id)->where("no_cut", $dataSpreading->no_cut)->where("color", $dataSpreading->color)->filter(function ($item) { return $item->ratio > 0 || $item->difference_qty > 0; })->first() : null;
                                            $numberingBefore = $dataNumbering ? $dataNumbering->where("so_det_id", $ratio->so_det_id)->where("no_cut", "<", $dataSpreading->no_cut)->where("color", $dataSpreading->color)->sortByDesc('no_cut')->filter(function ($item) { return $item->ratio > 0 || $item->difference_qty > 0; })->first() : null;

                                            $rangeAwal = ($dataSpreading->no_cut > 1 ? ($numberingBefore ? ($numberingBefore->numbering_id != null ? $numberingBefore->range_akhir + 1 : "-") : 1) : 1);
                                            $rangeAkhir = ($dataSpreading->no_cut > 1 ? ($numberingBefore ? ($numberingBefore->numbering_id != null ? $numberingBefore->range_akhir + $qty : "-") : $qty) : $qty);

                                            $numGeneratable = true;
                                    @endphp
                                            <tr>
                                                <input type="hidden" name="ratio[{{ $index }}]" id="ratio_{{ $index }}" value="{{ $ratio->ratio }}">
                                                <input type="hidden" name="so_det_id[{{ $index }}]" id="so_det_id_{{ $index }}" value="{{ $ratio->so_det_id }}">
                                                <input type="hidden" name="size[{{ $index }}]" id="size_{{ $index }}" value="{{ $ratio->size }}">
                                                <input type="hidden" name="qty_cut[{{ $index }}]" id="qty_cut_{{ $index }}" value="{{ $qty }}">
                                                <input type="hidden" name="range_awal[{{ $index }}]" id="range_awal_{{ $index }}" value="{{ $rangeAwal }}">
                                                <input type="hidden" name="range_akhir[{{ $index }}]" id="range_akhir_{{ $index }}" value="{{ $rangeAkhir }}">

                                                <td>{{ $ratio->size_dest}}</td>
                                                <td>{{ $ratio->ratio }}</td>
                                                <td>{{ (intval($ratio->ratio) * intval($dataSpreading->total_lembar)) != $qty ? $qty." (".(intval($ratio->ratio) * intval($dataSpreading->total_lembar))."".(($qty - (intval($ratio->ratio) * intval($dataSpreading->total_lembar))) > 0 ? "+".($qty - (intval($ratio->ratio) * intval($dataSpreading->total_lembar))) : ($qty - (intval($ratio->ratio) * intval($dataSpreading->total_lembar)))).")" : $qty }}</td>
                                                <td>{{ $rangeAwal }}</td>
                                                <td>{{ $rangeAkhir }}</td>
                                                <td>
                                                    @if ($dataSpreading->no_cut > 1)
                                                        @if ($numberingBefore)
                                                            @if ($numberingBefore->numbering_id != null)
                                                                @if ($numberingThis && $numberingThis->numbering_id != null)
                                                                    <i class="fa fa-check"></i>
                                                                @else
                                                                    <i class="fa fa-times"></i>
                                                                @endif
                                                            @else
                                                                @php $numGeneratable = false; @endphp
                                                                <i class="fa fa-minus"></i>
                                                            @endif
                                                        @else
                                                            @if ($numberingThis && $numberingThis->numbering_id != null)
                                                                <i class="fa fa-check"></i>
                                                            @else
                                                                <i class="fa fa-times"></i>
                                                            @endif
                                                        @endif
                                                    @else
                                                        @if ($numberingThis && $numberingThis->numbering_id != null)
                                                            <i class="fa fa-check"></i>
                                                        @else
                                                            <i class="fa fa-times"></i>
                                                        @endif
                                                    @endif
                                                <td>
                                                    <div class="d-flex gap-3">
                                                        <button type="button" class="btn btn-sm btn-danger" onclick="printNumbering({{ $index }});" {{ $numGeneratable ? '' : 'disabled' }}>
                                                            <i class="fa fa-print fa-s"></i> {{-- Numbering --}}
                                                        </button>
                                                        {{-- <button type="button" class="btn btn-sm btn-primary" onclick="printNumbering({{ $index }}, 'month_count');" {{ $numGeneratable ? '' : 'disabled' }}>
                                                            <i class="fa fa-print fa-s"></i> Month Count
                                                        </button> --}}
                                                        <div class="form-check mt-1 mb-0">
                                                            <input class="form-check-input generate-num-check" type="checkbox" name="generate_num[{{ $index }}]" id="generate_num_{{ $index }}" value="{{ $ratio->so_det_id }}" {{ $numGeneratable ? '' : 'disabled' }}>
                                                            <label class="form-check-label" for="flexCheckDefault">
                                                                Select
                                                            </label>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                    @php
                                            $index++;
                                        endif;
                                    @endphp
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-end gap-3 p-3">
                        <button type="button" class="btn btn-danger btn-sm w-auto" onclick="generateCheckedNumbering()"><i class="fa fa-print"></i> Generate Numbering</button>
                        {{-- <button type="button" class="btn btn-primary btn-sm w-auto" onclick="generateCheckedNumbering('month_count')"><i class="fa fa-print"></i> Generate Month Count</button>
                        <button type="button" class="btn btn-sb btn-sm w-auto" onclick="generateCheckedNumbering('both')"><i class="fa fa-print"></i> Generate All</button> --}}
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal" tabindex="-1" id="size-qty-modal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-sb">
                    <h5 class="modal-title"><i class="fa-solid fa-expand"></i> Modify Size Qty</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="#" method="post" id="mod-size-qty-form">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm" id="table-ratio-numbering">
                                <thead>
                                    <th>Size</th>
                                    <th>Ratio</th>
                                    <th>Qty Cut</th>
                                    <th>Range Awal</th>
                                    <th>Range Akhir</th>
                                    <th>Catatan</th>
                                </thead>
                                <tbody>
                                    @foreach ($dataRatio as $ratio)
                                        @php
                                            $qty = intval($ratio->ratio) * intval($dataSpreading->total_lembar);

                                            $numberingThis = $dataNumbering ? $dataNumbering->where("so_det_id", $ratio->so_det_id)->where("no_cut", $dataSpreading->no_cut)->where("color", $dataSpreading->color)->where("ratio", ">", "0")->first() : null;
                                            $numberingBefore = $dataNumbering ? $dataNumbering->where("so_det_id", $ratio->so_det_id)->where("no_cut", "<", $dataSpreading->no_cut)->where("color", $dataSpreading->color)->where("ratio", ">", "0")->sortByDesc('no_cut')->first() : null;

                                            $rangeAwal = ($dataSpreading->no_cut > 1 ? ($numberingBefore ? ($numberingBefore->numbering_id != null ? $numberingBefore->range_akhir + 1 : "-") : 1) : 1);
                                            $rangeAkhir = ($dataSpreading->no_cut > 1 ? ($numberingBefore ? ($numberingBefore->numbering_id != null ? $numberingBefore->range_akhir + $qty : "-") : $qty) : $qty);

                                            $numGeneratable = true;
                                        @endphp
                                        <tr>
                                            <td>{{ $ratio->size_dest}}</td>
                                            <td>{{ $ratio->ratio }}</td>
                                            <td>
                                                <input class="form-control form-control-sm" type="number" name="mod_qty_cut[{{ $index }}]" id="mod_qty_cut_{{ $index }}" data-original-value="{{ $qty }}" value="{{ $qty }}" onchange="modifyQty(this, {{ $index }});">
                                            </td>
                                            <td id="mod_range_awal_view_{{ $index }}">{{ $rangeAwal }}</td>
                                            <td id="mod_range_akhir_view_{{ $index }}">{{ ($rangeAkhir >= $rangeAwal ? $rangeAkhir : $rangeAwal) }}</td>
                                            <td>
                                                <input class="form-control form-control-sm" type="text" name="mod_note[{{ $index }}]" id="mod_note_{{ $index }}" value="">
                                            </td>
                                        </tr>

                                        <input type="hidden" name="mod_original_qty[{{ $index }}]" id="mod_original_qty_{{ $index }}" value="{{ $qty }}">
                                        <input type="hidden" name="mod_difference_qty[{{ $index }}]" id="mod_difference_qty_{{ $index }}" value="{{ 0 }}">
                                        <input type="hidden" name="mod_ratio[{{ $index }}]" id="mod_ratio_{{ $index }}" value="{{ $ratio->ratio }}">
                                        <input type="hidden" name="mod_so_det_id[{{ $index }}]" id="mod_so_det_id_{{ $index }}" value="{{ $ratio->so_det_id }}">
                                        <input type="hidden" name="mod_size[{{ $index }}]" id="mod_size_{{ $index }}" value="{{ $ratio->size }}">
                                        <input type="hidden" name="mod_range_awal[{{ $index }}]" id="mod_range_awal_{{ $index }}" value="{{ $rangeAwal }}">
                                        <input type="hidden" name="mod_range_akhir[{{ $index }}]" id="mod_range_akhir_{{ $index }}" value="{{ $rangeAkhir }}">
                                        @php
                                            $index++;
                                        @endphp
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="fa-solid fa-times fa-sm"></i> Tutup</button>
                    <button type="button" class="btn btn-success" onclick="submitModifyQty()"><i class="fa-solid fa-save fa-sm"></i> Simpan</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('custom-script')
    <!-- DataTables  & Plugins -->
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>

    <!-- Select2 -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        $('.select2').select2()
        $('.select2bs4').select2({
            theme: 'bootstrap4',
            dropdownParent: $("#editMejaModal")
        })

        $("#datatable").DataTable({
            ordering: false,
            paging: false,
        });

        var generating = false;

        $(document).ready(() => {
            let generatableElements = document.getElementsByClassName('generatable');

            for (let i = 0; i < generatableElements.length; i++) {
                let generatableValue = generatableElements[i].value;
                let generatableIndex = generatableElements[i].getAttribute('data-group');

                let generateStocker = document.getElementsByClassName('generate-'+generatableIndex);

                for (let j = 0; j < generateStocker.length; j++) {
                    if (generatableValue) {
                        generateStocker[j].removeAttribute('disabled');
                    } else {
                        generateStocker[j].setAttribute('disabled', true);
                    }
                }
            }

            window.onfocus = function() {
                console.log(generating);

                if (!generating) {
                    window.location.reload();
                }
            }
        });

        function printStocker(index) {
            generating = true;

            let stockerForm = new FormData(document.getElementById("stocker-form"));

            let no_ws = document.getElementById("no_ws").value;
            let style = document.getElementById("style").value;
            let color = document.getElementById("color").value;
            let panel = document.getElementById("panel").value;
            let no_form_cut = document.getElementById("no_form_cut").value;
            let current_size = document.getElementById("size_"+index).value;

            let fileName = [
                no_ws,
                style,
                color,
                panel,
                no_form_cut,
                current_size,
            ].join('-');

            Swal.fire({
                title: 'Please Wait...',
                html: 'Exporting Data...',
                didOpen: () => {
                    Swal.showLoading()
                },
                allowOutsideClick: false,
            });

            $.ajax({
                url: '{{ route('print-stocker') }}/'+index,
                type: 'post',
                processData: false,
                contentType: false,
                data: stockerForm,
                xhrFields:
                {
                    responseType: 'blob'
                },
                success: function(res) {
                    if (res) {
                        console.log(res);

                        var blob = new Blob([res], {type: 'application/pdf'});
                        var link = document.createElement('a');
                        link.href = window.URL.createObjectURL(blob);
                        link.download = fileName+".pdf";
                        link.click();

                        swal.close();

                        window.location.reload();
                    }

                    generating = false;
                },
                error: function(jqXHR) {
                    console.log(jqXHR);

                    generating = false;
                }
            });
        }

        function printStockerAllSize(part) {
            generating = true;

            let stockerForm = new FormData(document.getElementById("stocker-form"));

            let no_ws = document.getElementById("no_ws").value;
            let style = document.getElementById("style").value;
            let color = document.getElementById("color").value;
            let panel = document.getElementById("panel").value;
            let no_form_cut = document.getElementById("no_form_cut").value;

            let fileName = [
                no_ws,
                style,
                color,
                panel,
                part,
                no_form_cut
            ].join('-');

            Swal.fire({
                title: 'Please Wait...',
                html: 'Exporting Data...',
                didOpen: () => {
                    Swal.showLoading()
                },
                allowOutsideClick: false,
            });

            $.ajax({
                url: '{{ route('print-stocker-all-size') }}/'+part,
                type: 'post',
                processData: false,
                contentType: false,
                data: stockerForm,
                xhrFields:
                {
                    responseType: 'blob'
                },
                success: function(res) {
                    if (res) {
                        console.log(res);

                        var blob = new Blob([res], {type: 'application/pdf'});
                        var link = document.createElement('a');
                        link.href = window.URL.createObjectURL(blob);
                        link.download = fileName+".pdf";
                        link.click();

                        swal.close();

                        window.location.reload();
                    }
                    generating = false;
                },
                error: function(jqXHR) {
                    console.log(jqXHR);

                    generating = false;
                }
            });
        }

        function massChange(element) {
            let dataGroup = element.getAttribute('data-group');
            let dataChecked = element.checked;

            let similarElements = document.getElementsByClassName(dataGroup);

            for (let i = 0; i < similarElements.length; i++) {
                similarElements[i].checked = dataChecked;
            };
        }

        function generateCheckedStocker() {
            let generateStockerCheck = document.getElementsByClassName('generate-stocker-check');

            let checkedCount = 0;
            for (let i = 0; i < generateStockerCheck.length; i++) {
                if (generateStockerCheck[i].checked) {
                    checkedCount++;
                }
            }

            if (checkedCount > 0) {
                generating = true;

                let stockerForm = new FormData(document.getElementById("stocker-form"));

                let no_ws = document.getElementById("no_ws").value;
                let style = document.getElementById("style").value;
                let color = document.getElementById("color").value;
                let panel = document.getElementById("panel").value;
                let no_form_cut = document.getElementById("no_form_cut").value;

                let fileName = [
                    no_ws,
                    style,
                    color,
                    panel,
                    part,
                    no_form_cut
                ].join('-');

                Swal.fire({
                    title: 'Please Wait...',
                    html: 'Exporting Data...',
                    didOpen: () => {
                        Swal.showLoading()
                    },
                    allowOutsideClick: false,
                });

                $.ajax({
                    url: '{{ route('print-stocker-checked') }}',
                    type: 'post',
                    processData: false,
                    contentType: false,
                    data: stockerForm,
                    xhrFields:
                    {
                        responseType: 'blob'
                    },
                    success: function(res) {
                        if (res) {
                            console.log(res);

                            var blob = new Blob([res], {type: 'application/pdf'});
                            var link = document.createElement('a');
                            link.href = window.URL.createObjectURL(blob);
                            link.download = fileName+".pdf";
                            link.click();

                            swal.close();

                            window.location.reload();
                        }

                        generating = false;
                    },
                    error: function(jqXHR) {
                        console.log(jqXHR);

                        generating = false;
                    }
                });
            } else {
                Swal.fire({
                    icon:'warning',
                    title: 'Warning',
                    html: 'Harap ceklis stocker yang akan di print',
                });
            }
        }

        function printNumbering(index, type=null) {
            generating = true;

            let stockerForm = new FormData(document.getElementById("stocker-form"));

            stockerForm.append("type", type);

            let no_ws = document.getElementById("no_ws").value;
            let style = document.getElementById("style").value;
            let color = document.getElementById("color").value;
            let panel = document.getElementById("panel").value;
            let no_form_cut = document.getElementById("no_form_cut").value;
            let current_size = document.getElementById("size_"+index).value;

            let fileName = [
                no_ws,
                style,
                color,
                panel,
                no_form_cut,
                current_size,
            ].join('-');

            Swal.fire({
                title: 'Please Wait...',
                html: 'Exporting Data...',
                didOpen: () => {
                    Swal.showLoading()
                },
                allowOutsideClick: false,
            });

            $.ajax({
                url: '{{ route('print-numbering') }}/'+index,
                type: 'post',
                processData: false,
                contentType: false,
                data: stockerForm,
                xhrFields:
                {
                    responseType: 'blob'
                },
                success: function(res) {
                    if (res) {
                        console.log(res);

                        var blob = new Blob([res], {type: 'application/pdf'});
                        var link = document.createElement('a');
                        link.href = window.URL.createObjectURL(blob);
                        link.download = fileName+".pdf";
                        link.click();
                    }

                    window.location.reload();

                    generating = false;
                },
                error: function(jqXHR) {
                    console.log(jqXHR);

                    generating = false;
                }
            });
        }

        function generateCheckedNumbering(type = null) {
            type ? type = type : type = 'numbering';

            generating = true;

            let generateNumberingCheck = document.getElementsByClassName('generate-num-check');

            let checkedCount = 0;
            for (let i = 0; i < generateNumberingCheck.length; i++) {
                if (generateNumberingCheck[i].checked) {
                    checkedCount++;
                }
            }

            if (checkedCount > 0) {
                let stockerForm = new FormData(document.getElementById("stocker-form"));

                stockerForm.append('type', type);

                let no_ws = document.getElementById("no_ws").value;
                let style = document.getElementById("style").value;
                let color = document.getElementById("color").value;
                let panel = document.getElementById("panel").value;
                let no_form_cut = document.getElementById("no_form_cut").value;

                let fileName = [
                    no_ws,
                    style,
                    color,
                    panel,
                    no_form_cut,
                ].join('-');

                Swal.fire({
                    title: 'Please Wait...',
                    html: 'Exporting Data...',
                    didOpen: () => {
                        Swal.showLoading()
                    },
                    allowOutsideClick: false,
                });

                $.ajax({
                    url: '{{ route('print-numbering-checked') }}',
                    type: 'post',
                    processData: false,
                    contentType: false,
                    data: stockerForm,
                    xhrFields:
                    {
                        responseType: 'blob'
                    },
                    success: function(res) {
                        if (res) {
                            console.log(res);

                            var blob = new Blob([res], {type: 'application/pdf'});
                            var link = document.createElement('a');
                            link.href = window.URL.createObjectURL(blob);
                            link.download = fileName+".pdf";
                            link.click();
                        }

                        window.location.reload();

                        generating = false;
                    },
                    error: function(jqXHR) {
                        console.log(jqXHR);

                        generating = false;
                    }
                });
            } else {
                Swal.fire({
                    icon:'warning',
                    title: 'Warning',
                    html: 'Harap ceklis numbering yang akan di print',
                });
            }
        }

        function rearrangeGroup(noForm) {
            Swal.fire({
                title: 'Please Wait...',
                html: 'Rearranging Data...',
                didOpen: () => {
                    Swal.showLoading()
                },
                allowOutsideClick: false,
            });

            $.ajax({
                url: '{{ route('rearrange-group') }}',
                type: 'post',
                data: {
                    no_form : noForm
                },
                success: function(res) {
                    console.log("successs", res);

                    location.reload();
                },
                error: function(jqXHR) {
                    console.log("error", jqXHR);
                }
            });
        }

        function countStockerUpdate() {
            generating = true;

            Swal.fire({
                title: 'Please Wait...',
                html: 'Fixing Data...',
                didOpen: () => {
                    Swal.showLoading()
                },
                allowOutsideClick: false,
            });

            let stockerForm = new FormData(document.getElementById("stocker-form"));

            return $.ajax({
                url: '{{ route('count-stocker-update') }}',
                type: 'put',
                data: stockerForm,
                processData: false,
                contentType: false,
                success: function(res) {
                    console.log("successs", res);

                    swal.close();

                    generating = false;
                },
                error: function(jqXHR) {
                    console.log("error", jqXHR);

                    swal.close();

                    generating = false;
                }
            });
        }

        document.getElementById('size-qty-modal').addEventListener('shown.bs.modal', event => {
            generating = true;
        });

        document.getElementById('size-qty-modal').addEventListener('hidden.bs.modal', event => {
            generating = false;
        });

        function modifyQty(element, index) {
            let currentVal = element.value;
            let originalElement = document.getElementById('mod_original_qty_'+index);
            let differenceElement = document.getElementById('mod_difference_qty_'+index);
            let rangeAwalElement = document.getElementById("mod_range_awal_"+index);
            let rangeAwalElementView = document.getElementById("mod_range_awal_view_"+index);
            let rangeAkhirElement = document.getElementById("mod_range_akhir_"+index);
            let rangeAkhirElementView = document.getElementById("mod_range_akhir_view_"+index);
            let noteElement = document.getElementById("mod_note_"+index);

            console.log(currentVal, originalElement.value);

            let difference = currentVal - originalElement.value;

            if (originalElement.value != currentVal) {
                rangeAkhirElement.value = Number(rangeAwalElement.value) ? Number(rangeAwalElement.value) + (currentVal - 1) : "-";
                rangeAkhirElementView.innerText = Number(rangeAwalElement.value) ? Number(rangeAwalElement.value) + (currentVal - 1) : "-";

                noteElement.value = difference > 0 ? "+"+difference : difference;
            } else {
                rangeAkhirElement.value = Number(rangeAwalElement.value) ? Number(rangeAwalElement.value) + (currentVal - 1) : "-";
                rangeAkhirElementView.innerText = Number(rangeAwalElement.value) ? Number(rangeAwalElement.value) + (currentVal - 1) : "-";

                noteElement.value = "";
            }

            differenceElement.value = difference;
        }

        function submitModifyQty() {
            Swal.fire({
                title: 'Please Wait...',
                html: 'Modifying Data...',
                didOpen: () => {
                    Swal.showLoading()
                },
                allowOutsideClick: false,
            });

            let modSizeQtyForm = new FormData(document.getElementById("mod-size-qty-form"));
            modSizeQtyForm.append("no_form", document.getElementById("no_form_cut").value);

            return $.ajax({
                url: '{{ route('modify-size-qty') }}',
                type: 'post',
                data: modSizeQtyForm,
                processData: false,
                contentType: false,
                success: function(res) {
                    if (res) {
                        if (res.status == 200) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Perubahan qty size berhasil disimpan',
                                html: res.message,
                                showCancelButton: false,
                                showConfirmButton: true,
                                confirmButtonText: 'Oke',
                                timer: (res.status == 200 ? 5000 : 3000),
                                timerProgressBar: true
                            }).then(() => {
                                if (isNotNull(res.redirect)) {
                                    if (res.redirect != 'reload') {
                                        location.href = res.redirect;
                                    } else {
                                        location.reload();
                                    }
                                } else {
                                    location.reload();
                                }
                            });
                        } else {
                            iziToast.error({
                                title: 'error',
                                message: res.message,
                                position: 'topCenter'
                            });
                        }
                    }
                },
                error: function(jqXHR) {
                    iziToast.error({
                        title: 'error',
                        message: jqXHR.responseText,
                        position: 'topCenter'
                    });

                    swal.close();
                }
            });
        }
    </script>
@endsection
