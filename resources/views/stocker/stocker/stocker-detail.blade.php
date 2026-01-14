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
            @php
                $dataPartFormPrevious = $dataPartForm->where("no_cut", "<", $dataSpreading->no_cut)->sortByDesc("no_cut")->first();
                $dataPartFormNext = $dataPartForm->where("no_cut", ">", $dataSpreading->no_cut)->sortBy("no_cut")->first();
            @endphp
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    @if ($dataPartFormPrevious)
                        <a href="{{ route('show-stocker')."/".$dataPartFormPrevious->form_id }}" class="text-sb"><u><< Prev</u></a>
                    @endif
                </div>
                <div>
                    <select class="form-select form-select-sm" name="gotocut" id="gotocut" onchange="redirectToCut(this)">
                        @foreach ($dataPartForm->sortBy("no_cut") as $dpf)
                            <option value="{{ route("show-stocker")."/".$dpf->form_id }}" {{ $dpf->no_cut == $dataSpreading->no_cut ? "selected='true'" : "" }}>{{ $dpf->no_cut }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    @if ($dataPartFormNext)
                        <a href="{{ route('show-stocker')."/".$dataPartFormNext->form_id }}" class="text-sb"><u>Next >></u></a>
                    @endif
                </div>
            </div>
            <div class="d-flex justify-content-end gap-3 mb-3">
                <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#separate-qty-modal">
                    <i class="fa-solid fa-screwdriver-wrench fa-sm"></i> Separate Qty
                </button>
                <button type="button" class="btn btn-success btn-sm" onclick="countStockerUpdate()">
                    <i class="fa-solid fa-screwdriver-wrench fa-sm"></i> No. Stocker
                </button>
                <button type="button" class="btn btn-info btn-sm" onclick="rearrangeGroup('{{ $dataSpreading->id }}', '{{ $dataSpreading->no_form }}')">
                    <i class="fa-solid fa-screwdriver-wrench fa-sm"></i> Grouping
                </button>
                <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#size-qty-modal">
                    <i class="fa-solid fa-screwdriver-wrench fa-sm"></i> Size Qty
                </button>
            </div>
            <form action="#" method="post" id="stocker-form">
                {{-- Stocker --}}
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
                                    <input type="hidden" id="form_cut_id" name="form_cut_id" value="{{ $dataSpreading->id }}" readonly>
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
                    <h5 class="fw-bold text-sb mb-3 ps-1">Print Stocker</h5>
                    <div class="card">
                        <div class="card-body">
                            @php
                                $index = 0;
                                $partIndex = 0;

                                $currentGroup = "";
                                $currentGroupStocker = 0;
                                $currentTotal = 0;
                                $currentBefore = 0;
                                $currentModifySize = collect(["so_det_id" => null, "group_stocker" => null, "difference_qty" => null]);

                                $currentModifySizeQty = $modifySizeQty->filter(function ($item) {
                                    return !is_null($item->group_stocker);
                                })->count();

                                $groupStockerList = [];
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
                                        @php
                                            array_push($groupStockerList, ["group_stocker" => $currentGroupStocker, "qty" => $currentTotal]);
                                        @endphp

                                        @if ($currentModifySizeQty > 0)
                                            @include('stocker.stocker.stocker-detail-part', ["modifySizeQtyStocker" => $modifySizeQty])
                                        @else
                                            @include('stocker.stocker.stocker-detail-part')
                                        @endif
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

                                            $currentModifySize = $modifySizeQty->where("group_stocker", $currentGroupStocker)->first() ? $modifySizeQty->where("group_stocker", $currentGroupStocker)->first()->difference_qty : 0;
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
                                            @php
                                                array_push($groupStockerList, ["group_stocker" => $currentGroupStocker, "qty" => $currentTotal]);
                                            @endphp

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
                                            @php
                                                array_push($groupStockerList, ["group_stocker" => $currentGroupStocker, "qty" => $currentTotal]);
                                            @endphp

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
                                        @php
                                            array_push($groupStockerList, ["group_stocker" => $currentGroupStocker, "qty" => $currentTotal]);
                                        @endphp

                                        @if ($currentModifySizeQty > 0)
                                            @include('stocker.stocker.stocker-detail-part', ["modifySizeQtyStocker" => $modifySizeQty])
                                        @else
                                            @include('stocker.stocker.stocker-detail-part')
                                        @endif
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
                                            @php
                                                array_push($groupStockerList, ["group_stocker" => $currentGroupStocker, "qty" => $currentTotal]);
                                            @endphp

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
                                            @php
                                                array_push($groupStockerList, ["group_stocker" => $currentGroupStocker, "qty" => $currentTotal]);
                                            @endphp

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
                {{-- Additional Stocker --}}
                <div class="mb-5">
                    <h5 class="text-sb-secondary fw-bold mb-3 ps-1">Additional Stocker</h5>
                    <div class="card">
                        <div class="card-body">
                            @if (!$dataAdditional)
                                <div class="d-flex justify-content-end">
                                    <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#additional-modal">
                                        <i class="fa-solid fa-plus fa-sm"></i> Add Additional Stocker
                                    </button>
                                </div>
                            @endif
                            @if ($dataAdditional)
                                <input type="hidden" value="{{ $dataAdditional->act_costing_ws }}" id="id_ws_add" readonly>
                                <div class="row my-3">
                                    <div class="col-md-3">
                                        <label><small>No. WS</small></label>
                                        <input type="text" class="form-control form-control-sm" value="{{ $dataAdditional->act_costing_ws }}" id="no_ws_add" name="no_ws_add" readonly>
                                    </div>
                                    <div class="col-md-3">
                                        <label><small>Style</small></label>
                                        <input type="text" class="form-control form-control-sm" value="{{ $dataAdditional->style }}" id="style_add" name="style_add" readonly>
                                    </div>
                                    <div class="col-md-3">
                                        <label><small>Color</small></label>
                                        <input type="text" class="form-control form-control-sm" value="{{ $dataAdditional->color }}" id="color_add" name="color_add" readonly>
                                    </div>
                                    <div class="col-md-3">
                                        <label><small>Panel</small></label>
                                        <input type="text" class="form-control form-control-sm" value="{{ $dataAdditional->panel }}" id="panel_add" name="panel_add" readonly>
                                    </div>
                                </div>
                                <hr style="margin-top: 1rem; margin-bottom: 1rem; border: 1px; border-top-width: 1px; border-top-style: none; border-top-color: currentcolor; border-top: 1px solid rgb(149, 149, 149);">
                                @php
                                    $indexAdditional = 0;
                                    $partIndexAdditional = 0;

                                    $currentGroupAdditional = "";
                                    $currentGroupStockerAdditional = 0;
                                    $currentTotalAdditional = 0;
                                    $currentBeforeAdditional = 0;

                                    $currentModifySizeAdditional = collect(["so_det_id" => null, "group_stocker" => null, "difference_qty" => null]);

                                    $currentModifySizeQtyAdditional = $modifySizeQty->filter(function ($item) {
                                        return !is_null($item->group_stocker);
                                    })->count();
                                @endphp
                                @foreach ($dataSpreading->formCutInputDetails->where('status', '!=', 'not complete')->sortByDesc('group_roll')->sortByDesc('group_stocker') as $detail)
                                    @if (!$detail->group_stocker)
                                    {{-- Without group stocker condition --}}

                                        @if ($loop->first)
                                        {{-- Initial group --}}
                                            @php
                                                $currentGroupAdditional = $detail->group_roll;
                                                $currentGroupStockerAdditional = $detail->group_stocker;
                                            @endphp
                                        @endif

                                        @if ($detail->group_roll != $currentGroupAdditional)
                                            {{-- Create element when switching group --}}
                                            <div class="d-flex gap-3">
                                                <div class="mb-3">
                                                    <label><small>Group</small></label>
                                                    <input type="text" class="form-control form-control-sm" value="{{ $currentGroupAdditional }}" readonly>
                                                </div>
                                                <div class="mb-3">
                                                    <label><small>Qty</small></label>
                                                    <input type="text" class="form-control form-control-sm" value="{{ $currentTotalAdditional }}" readonly>
                                                </div>
                                            </div>

                                            @if ($currentModifySizeQtyAdditional > 0)
                                                @include('stocker.stocker.stocker-detail-part-additional', ["modifySizeQtyStocker" => $modifySizeQty])
                                            @else
                                                @include('stocker.stocker.stocker-detail-part-additional')
                                            @endif
                                            @php
                                                $indexAdditional += $dataRatioAdditional->count() * $dataPartDetailAdditional->count();
                                                $partIndexAdditional += $dataPartDetailAdditional->count();
                                            @endphp

                                            {{-- Change initial group --}}
                                            @php
                                                $currentBeforeAdditional += $currentTotal;

                                                $currentGroupAdditional = $detail->group_roll;
                                                $currentGroupStockerAdditional = $detail->group_stocker;
                                                $currentTotalAdditional = $detail->lembar_gelaran;
                                            @endphp

                                            @if ($loop->last)
                                                {{-- Create last element when it comes to an end of this loop --}}
                                                <div class="d-flex gap-3">
                                                    <div class="mb-3">
                                                        <label><small>Group</small></label>
                                                        <input type="text" class="form-control form-control-sm" value="{{ $currentGroupAdditional }}" readonly>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label><small>Qty</small></label>
                                                        <input type="text" class="form-control form-control-sm" value="{{ $currentTotalAdditional }}" readonly>
                                                    </div>
                                                </div>

                                                @include('stocker.stocker.stocker-detail-part-additional', ["modifySizeQtyStocker" => $modifySizeQty])
                                                @php
                                                    $indexAdditional += $dataRatioAdditional->count() * $dataPartDetailAdditional->count();
                                                    $partIndexAdditional += $dataPartDetailAdditional->count();
                                                @endphp
                                            @endif
                                        @else
                                            {{-- Accumulate when it still in the same group --}}
                                            @php
                                                $currentTotalAdditional += $detail->lembar_gelaran;
                                            @endphp

                                            {{-- Create last element when it comes to an end of this loop --}}
                                            @if ($loop->last)
                                                <div class="d-flex gap-3">
                                                    <div class="mb-3">
                                                        <label><small>Group</small></label>
                                                        <input type="text" class="form-control form-control-sm" value="{{ $currentGroupAdditional }}" readonly>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label><small>Qty</small></label>
                                                        <input type="text" class="form-control form-control-sm" value="{{ $currentTotalAdditional }}" readonly>
                                                    </div>
                                                </div>

                                                @include('stocker.stocker.stocker-detail-part-additional', ["modifySizeQtyStocker" => $modifySizeQty])
                                                @php
                                                    $indexAdditional += $dataRatioAdditional->count() * $dataPartDetailAdditional->count();
                                                    $partIndexAdditional += $dataPartDetailAdditional->count();
                                                @endphp
                                            @endif
                                        @endif
                                    @else
                                    {{-- With group stocker condition --}}

                                        @if ($loop->first)
                                        {{-- Initial Group --}}
                                            @php
                                                $currentGroupAdditional = $detail->group_roll;
                                                $currentGroupStockerAdditional = $detail->group_stocker;
                                            @endphp
                                        @endif

                                        @if ($detail->group_stocker != $currentGroupStockerAdditional)
                                            {{-- Create element when switching group --}}
                                            <div class="d-flex gap-3">
                                                <div class="mb-3">
                                                    <label><small>Group</small></label>
                                                    <input type="text" class="form-control form-control-sm" value="{{ $currentGroupAdditional }}" readonly>
                                                </div>
                                                <div class="mb-3">
                                                    <label><small>Qty</small></label>
                                                    <input type="text" class="form-control form-control-sm" value="{{ $currentTotalAdditional }}" readonly>
                                                </div>
                                            </div>

                                            @if ($currentModifySizeQtyAdditional > 0)
                                                @include('stocker.stocker.stocker-detail-part-additional', ["modifySizeQtyStocker" => $modifySizeQty])
                                            @else
                                                @include('stocker.stocker.stocker-detail-part-additional')
                                            @endif
                                            @php
                                                $indexAdditional += $dataRatioAdditional->count() * $dataPartDetailAdditional->count();
                                                $partIndexAdditional += $dataPartDetailAdditional->count();
                                            @endphp

                                            {{-- Change initial group --}}
                                            @php
                                                $currentBeforeAdditional += $currentTotalAdditional;

                                                $currentGroupAdditional = $detail->group_roll;
                                                $currentGroupStockerAdditional = $detail->group_stocker;
                                                $currentTotalAdditional = $detail->lembar_gelaran;
                                            @endphp

                                            {{-- Create last element when it comes to an end of this loop --}}
                                            @if ($loop->last)
                                                <div class="d-flex gap-3">
                                                    <div class="mb-3">
                                                        <label><small>Group</small></label>
                                                        <input type="text" class="form-control form-control-sm" value="{{ $currentGroupAdditional }}" readonly>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label><small>Qty</small></label>
                                                        <input type="text" class="form-control form-control-sm" value="{{ $currentTotalAdditional }}" readonly>
                                                    </div>
                                                </div>

                                                @include('stocker.stocker.stocker-detail-part-additional', ["modifySizeQtyStocker" => $modifySizeQty])
                                                @php
                                                    $indexAdditional += $dataRatioAdditional->count() * $dataPartDetailAdditional->count();
                                                    $partIndexAdditional += $dataPartDetailAdditional->count();
                                                @endphp
                                            @endif
                                        @else
                                            {{-- Accumulate when it still in the group --}}
                                            @php
                                                $currentTotalAdditional += $detail->lembar_gelaran;
                                            @endphp

                                            @if ($loop->last)
                                            {{-- Create last element when it comes to an end of this loop --}}
                                                <div class="d-flex gap-3">
                                                    <div class="mb-3">
                                                        <label><small>Group</small></label>
                                                        <input type="text" class="form-control form-control-sm" value="{{ $currentGroupAdditional }}" readonly>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label><small>Qty</small></label>
                                                        <input type="text" class="form-control form-control-sm" value="{{ $currentTotalAdditional }}" readonly>
                                                    </div>
                                                </div>

                                                @include('stocker.stocker.stocker-detail-part-additional', ["modifySizeQtyStocker" => $modifySizeQty])
                                                @php
                                                    $indexAdditional += $dataRatio->count() * $dataPartDetailAdditional->count();
                                                    $partIndexAdditional += $dataPartDetailAdditional->count();
                                                @endphp
                                            @endif
                                        @endif
                                    @endif
                                @endforeach
                            @endif
                        </div>
                        <div class="d-flex justify-content-end p-3">
                            <button type="button" class="btn btn-no btn-sm mb-3 w-auto" onclick="generateCheckedStockerAdd()"><i class="fa fa-print"></i> Generate Checked Stocker Additional</button>
                        </div>
                    </div>
                </div>

                {{-- Numbering --}}
                <div class="mb-5">
                    <h5 class="fw-bold mb-3 ps-1">Print Numbering</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered table" id="table-ratio-numbering">
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

    {{-- Modify Size Qty --}}
    <div class="modal" tabindex="-1" id="size-qty-modal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-sb">
                    <h5 class="modal-title"><i class="fa-solid fa-expand"></i> Modify Size Qty</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="#" method="post" id="mod-size-qty-form">
                        <div id="mod-size-table-container">
                            <div class="table-responsive">
                                <table class="table table-bordered table" id="table-ratio-numbering-mod">
                                    <thead>
                                        <th>Size</th>
                                        <th>Ratio</th>
                                        <th>Qty Cut</th>
                                        <th>Range Awal</th>
                                        <th>Range Akhir</th>
                                        <th>Catatan</th>
                                        <th>Group</th>
                                    </thead>
                                    <tbody>
                                        @foreach ($dataRatio as $ratio)
                                            @php
                                                $qty = intval($ratio->ratio) * intval($dataSpreading->total_lembar);

                                                $numberingThis = $dataNumbering ? $dataNumbering->where("so_det_id", $ratio->so_det_id)->where("no_cut", $dataSpreading->no_cut)->where("color", $dataSpreading->color)->where("ratio", ">", "0")->first() : null;
                                                $numberingBefore = $dataNumbering ? $dataNumbering->where("so_det_id", $ratio->so_det_id)->where("no_cut", "<", $dataSpreading->no_cut)->where("color", $dataSpreading->color)->where("ratio", ">", "0")->sortByDesc('no_cut')->first() : null;

                                                $rangeAwal = ($dataSpreading->no_cut > 1 ? ($numberingBefore ? ($numberingBefore->numbering_id != null ? $numberingBefore->range_akhir + 1 : "-") : ($qty > 0 ? 1 : '-')) : ($qty > 0 ? 1 : '-'));
                                                $rangeAkhir = ($dataSpreading->no_cut > 1 ? ($numberingBefore ? ($numberingBefore->numbering_id != null ? $numberingBefore->range_akhir + $qty : "-") : ($qty > 0 ? $qty : '-')) : ($qty > 0 ? $qty : '-'));

                                                $numGeneratable = true;
                                            @endphp
                                            <tr>
                                                <td>{{ $ratio->size_dest}}</td>
                                                <td>{{ $ratio->ratio }}</td>
                                                <td>
                                                    <input class="form-control form-control-sm" type="number" name="mod_qty_cut[{{ $index }}]" id="mod_qty_cut_{{ $index }}" data-original-value="{{ $ratio->ratio * ($groupStockerList[count($groupStockerList)-1] ? $groupStockerList[count($groupStockerList)-1]['qty'] : 0) }}" value="{{ $ratio->ratio * ($groupStockerList[count($groupStockerList)-1] ? $groupStockerList[count($groupStockerList)-1]['qty'] : 0) }}" onchange="modifyQty(this, {{ $index }});">
                                                </td>
                                                <td id="mod_range_awal_view_{{ $index }}">{{ $rangeAwal }}</td>
                                                <td id="mod_range_akhir_view_{{ $index }}">{{ ($rangeAkhir >= $rangeAwal ? $rangeAkhir : $rangeAwal) }}</td>
                                                <td>
                                                    <input class="form-control form-control-sm" type="text" name="mod_note[{{ $index }}]" id="mod_note_{{ $index }}" value="">
                                                </td>
                                                <td>
                                                    <select class="form-select form-select-sm" name="mod_group_stocker[{{ $index }}]" id="mod_group_stocker_{{ $index }}" onchange="modifyGroupStocker(this, {{ $index }});">
                                                        @foreach ($groupStockerList as $groupStocker)
                                                            @if ($loop->index == (count($groupStockerList)-1))
                                                                <option value="{{ $groupStocker['group_stocker'] }}" data-qty="{{ $groupStocker['qty'] }}" selected>{{ $groupStocker['group_stocker'] }}</option>
                                                            @else
                                                                <option value="{{ $groupStocker['group_stocker'] }}" data-qty="{{ $groupStocker['qty'] }}">{{ $groupStocker['group_stocker'] }}</option>
                                                            @endif
                                                        @endforeach
                                                    </select>
                                                </td>
                                            </tr>

                                            <input type="hidden" name="mod_original_qty[{{ $index }}]" id="mod_original_qty_{{ $index }}" data-original-value="{{ $ratio->ratio * ($groupStockerList[count($groupStockerList)-1] ? $groupStockerList[count($groupStockerList)-1]['qty'] : 0) }}" value="{{ $ratio->ratio * ($groupStockerList[count($groupStockerList)-1] ? $groupStockerList[count($groupStockerList)-1]['qty'] : 0) }}" value="{{ $ratio->ratio * ($groupStockerList[count($groupStockerList)-1] ? $groupStockerList[count($groupStockerList)-1]['qty'] : 0) }}">
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
                        </div>
                        <button type="button" class="btn btn-sb mb-3" id="add-mod-button"><i class="fa fa-plus"></i> Add Mod</button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="fa-solid fa-times fa-sm"></i> Tutup</button>
                    <button type="button" class="btn btn-success" onclick="submitModifyQty()"><i class="fa-solid fa-save fa-sm"></i> Simpan</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Additional Modal --}}
    <div class="modal" id="additional-modal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-sb">
                    <h5 class="modal-title"><i class="fa-solid fa-plus"></i> Add Additional Stocker</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="post" action="{{ route('submit-stocker-add') }}" id="add-additional-stocker-form">
                        <input type="hidden" id="add_form_cut_id" name="add_form_cut_id" value="{{ $dataSpreading->id }}">
                        <input type="hidden" id="add_no_form" name="add_no_form" value="{{ $dataSpreading->no_form }}">
                        <input type="hidden" class="form-control" id="add_ws_ws" name="add_ws_ws" readonly>
                        <div class="row">
                            <div class="col-6 col-md-3">
                                <div class="mb-1">
                                    <div class="form-group">
                                        <label><small>No. WS</small></label>
                                        <select class="form-control select2bs4" id="add_ws" name="add_ws" style="width: 100%;">
                                            <option selected="selected" value="">Pilih WS</option>
                                            @foreach ($orders as $order)
                                                <option value="{{ $order->id }}">
                                                    {{ $order->kpno }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="mb-1">
                                    <label class="form-label"><small>Buyer</small></label>
                                    <input type="text" class="form-control" id="add_buyer" name="add_buyer" readonly>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="mb-1">
                                    <label class="form-label"><small>Style</small></label>
                                    <input type="text" class="form-control" id="add_style" name="add_style" readonly>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="mb-1">
                                    <div class="form-group">
                                        <label><small>Color</small></label>
                                        <select class="form-control select2bs4" id="add_color" name="add_color" style="width: 100%;">
                                            <option selected="selected" value="">Pilih Color</option>
                                            {{-- select 2 option --}}
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="mb-1">
                                    <div class="form-group">
                                        <label><small>Panel</small></label>
                                        <select class="form-control select2bs4" id="add_panel_id" name="add_panel_id" style="width: 100%;" >
                                            <option selected="selected" value="">Pilih Panel</option>
                                            {{-- select 2 option --}}
                                        </select>
                                        <input type="hidden" class="form-control readonly d-none" id="add_panel" name="add_panel">
                                    </div>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table id="orderQtyDatatable" class="table table-bordered table-striped table w-100">
                                    <thead>
                                        <tr>
                                            <th>WS</th>
                                            <th>Color</th>
                                            <th>Size</th>
                                            <th>Size Input</th>
                                            <th>So Det Id</th>
                                            <th>Ratio</th>
                                            <th>Cut Qty</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th id="total_ratio"></th>
                                            <th id="total_cut_qty"></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <input type="hidden" class="form-control" id="jumlah_so_det" name="jumlah_so_det" readonly>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="fa-solid fa-times fa-sm"></i> Tutup</button>
                    <button type="button" class="btn btn-success" onclick="submitAdditionalStocker()"><i class="fa-solid fa-save fa-sm"></i> Simpan</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Separate Stocker Qty --}}
    <div class="modal" tabindex="-1" id="separate-qty-modal">
        <div class="modal-dialog modal-dialog-scrollable modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-sb">
                    <h5 class="modal-title"><i class="fa-solid fa-expand"></i> Separate Qty</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="#" method="post" id="separate-qty-form">
                        @php
                            $indexSeparate = 0;

                            $currentGroupSeparate = "";
                            $currentGroupStockerSeparate = 0;
                            $currentTotalSeparate = 0;
                            $currentBeforeSeparate = 0;
                        @endphp
                        @foreach ($dataSpreading->formCutInputDetails->where('status', '!=', 'not complete')->sortByDesc('group_roll')->sortByDesc('group_stocker') as $detail)
                            @if (!$detail->group_stocker)
                            {{-- Without group stocker condition --}}

                                @if ($loop->first)
                                {{-- Initial group --}}
                                    @php
                                        $currentGroupSeparate = $detail->group_roll;
                                        $currentGroupStockerSeparate = $detail->group_stocker;
                                    @endphp
                                @endif

                                @if ($detail->group_roll != $currentGroupSeparate)
                                    {{-- Create element when switching group --}}
                                    <div class="d-flex gap-3">
                                        <div class="mb-3">
                                            <label><small>Group</small></label>
                                            <input type="text" class="form-control form-control-sm" value="{{ $currentGroupSeparate }}" readonly>
                                        </div>
                                        <div class="mb-3">
                                            <label><small>Qty</small></label>
                                            <input type="text" class="form-control form-control-sm" value="{{ $currentTotalSeparate }}" readonly>
                                        </div>
                                    </div>

                                    @include('stocker.stocker.stocker-detail-separate', ["modifySizeQtyStocker" => $modifySizeQty])
                                    @php
                                        $indexSeparate += $dataRatio->count();
                                    @endphp

                                    {{-- Change initial group --}}
                                    @php
                                        $currentBeforeSeparate += $currentTotalSeparate;

                                        $currentGroupSeparate = $detail->group_roll;
                                        $currentGroupStockerSeparate = $detail->group_stocker;
                                        $currentTotalSeparate = $detail->lembar_gelaran;
                                    @endphp

                                    @if ($loop->last)
                                        {{-- Create last element when it comes to an end of this loop --}}
                                        <div class="d-flex gap-3">
                                            <div class="mb-3">
                                                <label><small>Group</small></label>
                                                <input type="text" class="form-control form-control-sm" value="{{ $currentGroupSeparate }}" readonly>
                                            </div>
                                            <div class="mb-3">
                                                <label><small>Qty</small></label>
                                                <input type="text" class="form-control form-control-sm" value="{{ $currentTotalSeparate }}" readonly>
                                            </div>
                                        </div>

                                        @include('stocker.stocker.stocker-detail-separate', ["modifySizeQtyStocker" => $modifySizeQty])
                                        @php
                                            $indexSeparate += $dataRatio->count();
                                        @endphp
                                    @endif
                                @else
                                    {{-- Accumulate when it still in the same group --}}
                                    @php
                                        $currentTotalSeparate += $detail->lembar_gelaran;
                                    @endphp

                                    {{-- Create last element when it comes to an end of this loop --}}
                                    @if ($loop->last)
                                        <div class="d-flex gap-3">
                                            <div class="mb-3">
                                                <label><small>Group</small></label>
                                                <input type="text" class="form-control form-control-sm" value="{{ $currentGroupSeparate }}" readonly>
                                            </div>
                                            <div class="mb-3">
                                                <label><small>Qty</small></label>
                                                <input type="text" class="form-control form-control-sm" value="{{ $currentTotalSeparate }}" readonly>
                                            </div>
                                        </div>

                                        @include('stocker.stocker.stocker-detail-separate', ["modifySizeQtyStocker" => $modifySizeQty])
                                        @php
                                            $indexSeparate += $dataRatio->count();
                                        @endphp
                                    @endif
                                @endif
                            @else
                            {{-- With group stocker condition --}}

                                @if ($loop->first)
                                {{-- Initial Group --}}
                                    @php
                                        $currentGroupSeparate = $detail->group_roll;
                                        $currentGroupStockerSeparate = $detail->group_stocker;
                                    @endphp
                                @endif

                                @if ($detail->group_stocker != $currentGroupStockerSeparate)
                                    {{-- Create element when switching group --}}
                                    <div class="d-flex gap-3">
                                        <div class="mb-3">
                                            <label><small>Group</small></label>
                                            <input type="text" class="form-control form-control-sm" value="{{ $currentGroupSeparate }}" readonly>
                                        </div>
                                        <div class="mb-3">
                                            <label><small>Qty</small></label>
                                            <input type="text" class="form-control form-control-sm" value="{{ $currentTotalSeparate }}" readonly>
                                        </div>
                                    </div>

                                    @include('stocker.stocker.stocker-detail-separate', ["modifySizeQtyStocker" => $modifySizeQty])
                                    @php
                                        $indexSeparate += $dataRatio->count();
                                    @endphp

                                    {{-- Change initial group --}}
                                    @php
                                        $currentBeforeSeparate += $currentTotalSeparate;

                                        $currentGroupSeparate = $detail->group_roll;
                                        $currentGroupStockerSeparate = $detail->group_stocker;
                                        $currentTotalSeparate = $detail->lembar_gelaran;
                                    @endphp

                                    {{-- Create last element when it comes to an end of this loop --}}
                                    @if ($loop->last)
                                        <div class="d-flex gap-3">
                                            <div class="mb-3">
                                                <label><small>Group</small></label>
                                                <input type="text" class="form-control form-control-sm" value="{{ $currentGroupSeparate }}" readonly>
                                            </div>
                                            <div class="mb-3">
                                                <label><small>Qty</small></label>
                                                <input type="text" class="form-control form-control-sm" value="{{ $currentTotalSeparate }}" readonly>
                                            </div>
                                        </div>

                                        @include('stocker.stocker.stocker-detail-separate', ["modifySizeQtyStocker" => $modifySizeQty])
                                        @php
                                            $indexSeparate += $dataRatio->count();
                                        @endphp
                                    @endif
                                @else
                                    {{-- Accumulate when it still in the group --}}
                                    @php
                                        $currentTotalSeparate += $detail->lembar_gelaran;
                                    @endphp

                                    @if ($loop->last)
                                    {{-- Create last element when it comes to an end of this loop --}}
                                        <div class="d-flex gap-3">
                                            <div class="mb-3">
                                                <label><small>Group</small></label>
                                                <input type="text" class="form-control form-control-sm" value="{{ $currentGroupSeparate }}" readonly>
                                            </div>
                                            <div class="mb-3">
                                                <label><small>Qty</small></label>
                                                <input type="text" class="form-control form-control-sm" value="{{ $currentTotalSeparate }}" readonly>
                                            </div>
                                        </div>

                                        @include('stocker.stocker.stocker-detail-separate', ["modifySizeQtyStocker" => $modifySizeQty])
                                        @php
                                            $indexSeparate += $dataRatio->count();
                                        @endphp
                                    @endif
                                @endif
                            @endif
                        @endforeach
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="fa-solid fa-times fa-sm"></i> Tutup</button>
                    <button type="button" class="btn btn-success" onclick="submitSeparate()"><i class="fa-solid fa-save fa-sm"></i> Simpan</button>
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
            dropdownParent: $("#additional-modal")
        })

        $("#datatable").DataTable({
            ordering: false,
            paging: false,
        });

        var generating = false;

        $(document).ready(function () {
            let generatableElements = document.getElementsByClassName('generatable');

            for (let i = 0; i < generatableElements.length; i++) {
                let generatableValue = generatableElements[i].value;
                let generatableIndex = generatableElements[i].getAttribute('data-group');

                let generateStocker = document.getElementsByClassName('generate-'+generatableIndex);

                let generateStockerAdd = document.getElementsByClassName('generate-add-'+generatableIndex);

                for (let j = 0; j < generateStocker.length; j++) {
                    if (generatableValue) {
                        generateStocker[j].removeAttribute('disabled');
                    } else {
                        generateStocker[j].setAttribute('disabled', true);
                    }
                }

                for (let k = 0; k < generateStockerAdd.length; k++) {
                    if (generatableValue) {
                        generateStockerAdd[k].removeAttribute('disabled');
                    } else {
                        generateStockerAdd[k].setAttribute('disabled', true);
                    }
                }
            }

            $("#add_ws").val(null).trigger("change");
            $("#add_color").prop("disabled", true);
            $("#add_panel_id").prop("disabled", true);

            addModSize();

            // window.onfocus = function() {
            //     console.log(generating);

            //     if (!generating) {
            //         window.location.reload();
            //     }
            // }
        });

        function addModSize() {
            const addModBtn = document.getElementById('add-mod-button');
            const tableContainer = document.getElementById('mod-size-table-container');

            addModBtn.addEventListener('click', function(e) {
                e.preventDefault(); // prevent form submission

                const originalTable = document.getElementById('table-ratio-numbering-mod');
                const clonedTable = originalTable.cloneNode(true);

                // Determine the last index from existing mod_qty_cut inputs
                let lastInputs = tableContainer.querySelectorAll('input[name^="mod_qty_cut"]');
                let lastIndex = lastInputs.length > 0
                    ? parseInt(lastInputs[lastInputs.length - 1].name.match(/\d+/)[0])
                    : -1;

                let currentIndex = lastIndex + 1;

                // Loop through each row
                clonedTable.querySelectorAll('tbody tr').forEach(function(tr) {
                    // Update visible inputs and selects in this row
                    tr.querySelectorAll('[id]').forEach(function(el) {
                        const oldId = el.id;
                        el.id = oldId.replace(/\d+/, currentIndex);

                        if (el.name) {
                            el.name = el.name.replace(/\[\d+\]/, `[${currentIndex}]`);
                        }

                        if (el.hasAttribute('onchange')) {
                            let onchangeStr = el.getAttribute('onchange');
                            // Replace the old index number with currentIndex
                            onchangeStr = onchangeStr.replace(/\d+/, currentIndex);
                            el.setAttribute('onchange', onchangeStr);
                        }

                        // Reset visible values
                        if (el.tagName === 'INPUT') {
                            if (el.type === 'number') el.value = el.dataset.originalValue || 0;
                            else if (el.type === 'text') el.value = '';
                        }

                        if (el.tagName === 'SELECT') {
                            el.selectedIndex = el.options.length - 1;
                        };
                    });

                    // Update all hidden inputs that belong to this row (inside tbody)
                    // Assuming hidden inputs are directly after the row
                    let next = tr.nextElementSibling;
                    while (next && next.tagName === 'INPUT' && next.type === 'hidden') {
                        const hidden = next;
                        const oldId = hidden.id;
                        hidden.id = oldId.replace(/\d+/, currentIndex);

                        if (hidden.name) hidden.name = hidden.name.replace(/\[\d+\]/, `[${currentIndex}]`);

                        // Reset hidden values
                        if (hidden.name.includes('mod_difference_qty')) hidden.value = 0;
                        if (hidden.name.includes('mod_original_qty')) hidden.value = hidden.dataset.originalValue;

                        next = next.nextElementSibling;
                    }

                    currentIndex++; // increment index once per row
                });

                // Append cloned table
                const wrapper = document.createElement('div');
                wrapper.classList.add('table-responsive', 'mt-3');
                wrapper.appendChild(clonedTable);
                tableContainer.appendChild(wrapper);
            });
        }

        function openAdditionalModal() {
            $("#additional-modal").modal("show");
        }

        // Step One (WS) on change event
        $('#add_ws').on('change', function(e) {
            if (this.value) {
                updateColorList();
                updateOrderInfo();
            }
        });

        // Step Two (Color) on change event
        $('#add_color').on('change', function(e) {
            if (this.value) {
                updatePanelList();
                updateSizeList();
            }
        });

        // Step Three (Panel) on change event
        $('#add_panel_id').on('change', function(e) {
            if (this.value) {
                $('#add_panel').val($('#add_panel_id option:selected').text());
            }
        });

        $('#add_panel').on('change', function(e) {
            updateSizeList();
        });

        function updateOrderInfo() {
            return $.ajax({
                url: '{{ route("get-marker-order") }}',
                type: 'get',
                data: {
                    act_costing_id: $('#add_ws').val(),
                    color: $('#add_color').val(),
                },
                dataType: 'json',
                success: function (res) {
                    if (res) {
                        document.getElementById('add_ws_ws').value = res.kpno;
                        document.getElementById('add_buyer').value = res.buyer;
                        document.getElementById('add_style').value = res.styleno;
                    }
                },
            });
        }

        // Update Color Select Option Based on Order WS
        function updateColorList() {
            document.getElementById('add_color').value = null;

            return $.ajax({
                url: '{{ route("get-marker-colors") }}',
                type: 'get',
                data: {
                    act_costing_id: $('#add_ws').val(),
                },
                success: function (res) {
                    if (res) {
                        // Update this step
                        document.getElementById('add_color').innerHTML = res;

                        // Reset next step
                        document.getElementById('add_panel_id').innerHTML = null;
                        document.getElementById('add_panel_id').value = null;

                        // Open this step
                        $("#add_color").prop("disabled", false);

                        // Close next step
                        $("#add_panel_id").prop("disabled", true);
                    }
                },
            });
        }

        // Update Panel Select Option Based on Order WS and Color WS
        function updatePanelList() {
            document.getElementById('add_panel_id').value = null;
            return $.ajax({
                url: '{{ route("get-marker-panels") }}',
                type: 'get',
                data: {
                    act_costing_id: $('#add_ws').val(),
                    color: $('#add_color').val(),
                },
                success: function (res) {
                    if (res) {
                        // Update this step
                        document.getElementById('add_panel_id').innerHTML = res;

                        // Open this step
                        $("#add_panel_id").prop("disabled", false);
                    }
                },
            });
        }

        // Order Qty Datatable (Size|Ratio|Cut Qty)
        let orderQtyDatatable = $("#orderQtyDatatable").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            paging: false,
            ajax: {
                url: '{{ route("get-general-sizes") }}',
                data: function (d) {
                    d.act_costing_id = $('#add_ws').val();
                    d.color = $('#add_color').val();
                },
            },
            columns: [
                {
                    data: 'no_ws'
                },
                {
                    data: 'color'
                },
                {
                    data: 'size_dest'
                },
                {
                    data: 'size' // size input
                },
                {
                    data: 'so_det_id' // detail so input
                },
                {
                    data: 'so_det_id' // ratio input
                },
                {
                    data: 'so_det_id' // cut qty input
                }
            ],
            columnDefs: [
                {
                    // Size Input
                    targets: [3],
                    className: "d-none",
                    render: (data, type, row, meta) => {
                        // Hidden Size Input
                        return '<input type="hidden" class="form-control" id="add-size-' + meta.row + '" name="add_size['+meta.row+']" value="' + data + '" readonly />'
                    }
                },
                {
                    // SO Detail Input
                    targets: [4],
                    className: "d-none",
                    render: (data, type, row, meta) => {
                        // Hidden Detail SO Input
                        return '<input type="hidden" id="add-so-det-id-' + meta.row + '" name="add_so_det_id['+meta.row+']" value="' + data + '" readonly />'
                    }
                },
                {
                    // Ratio Input
                    targets: [5],
                    render: (data, type, row, meta) => {
                        // Hidden Ratio Input
                        return '<input type="number" id="add-ratio-' + meta.row + '" name="add_ratio[' + meta.row + ']" onchange="calculateRatio(' + meta.row + ');" onkeyup="calculateRatio(' + meta.row + ');" />';
                    }
                },
                {
                    // Cut Qty Input
                    targets: [6],
                    render: (data, type, row, meta) => {
                        // Hidden Cut Qty Input
                        return '<input type="number" id="add-cut-qty-' + meta.row + '" name="add_cut_qty['+meta.row+']" readonly />'
                    }
                }
            ],
        });

        // Update Order Qty Datatable
        async function updateSizeList() {
            await orderQtyDatatable.ajax.reload(() => {
                // Get Sizes Count ( for looping over sizes input )
                document.getElementById('jumlah_so_det').value = orderQtyDatatable.data().count();
            });
        }

        // Calculate Cut Qty Based on Ratio and Spread Qty ( Ratio * Spread Qty )
        function calculateRatio(id) {
            let ratio = document.getElementById('add-ratio-'+id).value;
            let gelarQty = $("#qty_ply_total").val();

            // Cut Qty Formula
            document.getElementById('add-cut-qty-'+id).value = ratio * gelarQty;

            // Call Calculate Total Ratio Function ( for order qty datatable summary )
            calculateTotalRatio();
        }

        // Calculate Total Ratio
        function calculateTotalRatio() {
            // Get Sizes Count
            let totalSize = document.getElementById('jumlah_so_det').value;

            let totalRatio = 0;
            let totalCutQty = 0;

            // Looping Over Sizes Input
            for (let i = 0; i < totalSize; i++) {
                // Sum Ratio and Cut Qty
                totalRatio += Number(document.getElementById('add-ratio-'+i).value);
                totalCutQty += Number(document.getElementById('add-cut-qty-'+i).value);
            }

            // Set Ratio and Cut Qty ( order qty datatable summary )
            document.querySelector("table#orderQtyDatatable tfoot tr th:nth-child(4)").innerText = totalRatio;
            document.querySelector("table#orderQtyDatatable tfoot tr th:nth-child(5)").innerText = totalCutQty;
        }

        // Calculate All Cut Qty at Once Based on Spread Qty
        function calculateAllRatio(element) {
            // Get Sizes Count
            let totalSize = document.getElementById('jumlah_so_det').value;

            let gelarQty = element.value;

            // Looping Over Sizes Input
            for (let i = 0; i < totalSize; i++) {
                // Calculate Cut Qty
                let ratio = document.getElementById('add-ratio-'+i).value;

                // Cut Qty Formula
                document.getElementById('add-cut-qty-'+i).value = ratio * gelarQty;
            }

            // Call Calculate Total Ratio Function ( for order qty datatable summary )
            calculateTotalRatio();
        }

        document.getElementById('additional-modal').addEventListener('shown.bs.modal', event => {
            generating = 'true';
        });

        document.getElementById('additional-modal').addEventListener('hidden.bs.modal', event => {
            generating = 'false';
        });

        function submitAdditionalStocker() {
            let e = document.getElementById("add-additional-stocker-form");

            document.getElementById("loading").classList.remove("d-none");

            clearModified();

            $.ajax({
                url: e.getAttribute('action'),
                type: e.getAttribute('method'),
                data: new FormData(e),
                processData: false,
                contentType: false,
                success: async function(res) {
                    document.getElementById("loading").classList.add("d-none");

                    // Success Response
                    if (res.status == 200) {
                        // When Actually Success :

                        // Reset This Form
                        e.reset();

                        // Success Alert
                        Swal.fire({
                            icon: 'success',
                            title: 'Data Additional berhasil disimpan',
                            text: res.message,
                            showCancelButton: false,
                            showConfirmButton: true,
                            confirmButtonText: 'Oke',
                            timer: 5000,
                            timerProgressBar: true
                        }).then(() => {
                            // Reset Step ( back to step one )
                            resetStep();

                            window.location.reload();
                        });
                    } else {
                        // When Actually Error :

                        // Error Alert
                        iziToast.error({
                            title: 'Error',
                            message: res.message,
                            position: 'topCenter'
                        });
                    }

                    // Reload Order Qty Datatable
                    orderQtyDatatable.ajax.reload();

                    // If There Are Some Additional Error
                    if (Object.keys(res.additional).length > 0 ) {
                        for (let key in res.additional) {
                            if (document.getElementById(key)) {
                                document.getElementById(key).classList.add('is-invalid');

                                if (res.additional[key].hasOwnProperty('message')) {
                                    document.getElementById(key+'_error').classList.remove('d-none');
                                    document.getElementById(key+'_error').innerHTML = res.additional[key]['message'];
                                }

                                if (res.additional[key].hasOwnProperty('value')) {
                                    document.getElementById(key).value = res.additional[key]['value'];
                                }

                                modified.push(
                                    [key, '.classList', '.remove(', "'is-invalid')"],
                                    [key+'_error', '.classList', '.add(', "'d-none')"],
                                    [key+'_error', '.innerHTML = ', "''"],
                                )
                            }
                        }
                    }
                }, error: function (jqXHR) {
                    document.getElementById("loading").classList.add("d-none");

                    // Error Response

                    let res = jqXHR.responseJSON;
                    let message = '';
                    let i = 0;

                    for (let key in res.errors) {
                        message = res.errors[key];
                        document.getElementById(key).classList.add('is-invalid');
                        modified.push(
                            [key, '.classList', '.remove(', "'is-invalid')"],
                        )

                        if (i == 0) {
                            document.getElementById(key).focus();
                            i++;
                        }
                    };
                }
            });
        }

        // Reset Step
        async function resetStep() {
            await $("#add_ws").val(null).trigger("change");
            await $("#add_color").val(null).trigger("change");
            await $("#add_panel_id").val(null).trigger("change");
            await $("#add_color").prop("disabled", true);
            await $("#add_panel_id").prop("disabled", true);
        }

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

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Terjadi kesalahan',
                        confirmButtonText: 'Coba Lagi',
                        showCancelButton: true,
                        cancelButtonText: 'Batalkan',
                    }).then(result => {
                        if (result.isConfirmed) {
                            printStocker(index); // Retry the request
                        }
                    });
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

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Terjadi kesalahan',
                        confirmButtonText: 'Coba Lagi',
                        showCancelButton: true,
                        cancelButtonText: 'Batalkan',
                    }).then(result => {
                        if (result.isConfirmed) {
                            printStockerAllSize(part); // Retry the request
                        }
                    });
                }
            });
        }

        function printStockerAllSizeAdd() {
            generating = true;

            let stockerForm = new FormData(document.getElementById("stocker-form"));

            let no_ws = document.getElementById("no_ws_add").value;
            let style = document.getElementById("style_add").value;
            let color = document.getElementById("color_add").value;
            let panel = document.getElementById("panel_add").value;
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
                url: '{{ route('print-stocker-all-size-add') }}',
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

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Terjadi kesalahan',
                        confirmButtonText: 'Coba Lagi',
                        showCancelButton: true,
                        cancelButtonText: 'Batalkan',
                    }).then(result => {
                        if (result.isConfirmed) {
                            printStockerAllSizeAdd(); // Retry the request
                        }
                    });
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

                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Terjadi kesalahan',
                            confirmButtonText: 'Coba Lagi',
                            showCancelButton: true,
                            cancelButtonText: 'Batalkan',
                        }).then(result => {
                            if (result.isConfirmed) {
                                generateCheckedStocker(); // Retry the request
                            }
                        });
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

        function generateCheckedStockerAdd() {
            let generateStockerCheck = document.getElementsByClassName('generate-stocker-check-add');

            let checkedCount = 0;
            for (let i = 0; i < generateStockerCheck.length; i++) {
                if (generateStockerCheck[i].checked) {
                    checkedCount++;
                }
            }

            if (checkedCount > 0) {
                generating = true;

                let stockerForm = new FormData(document.getElementById("stocker-form"));

                let no_ws = document.getElementById("no_ws_add").value;
                let style = document.getElementById("style_add").value;
                let color = document.getElementById("color_add").value;
                let panel = document.getElementById("panel_add").value;
                let no_form_cut = document.getElementById("no_form_cut").value;

                let fileName = [
                    no_ws,
                    style,
                    color,
                    panel,
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
                    url: '{{ route('print-stocker-checked-add') }}',
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

                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Terjadi kesalahan',
                            confirmButtonText: 'Coba Lagi',
                            showCancelButton: true,
                            cancelButtonText: 'Batalkan',
                        }).then(result => {
                            if (result.isConfirmed) {
                                generateCheckedStockerAdd(); // Retry the request
                            }
                        });
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

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Terjadi kesalahan',
                        confirmButtonText: 'Coba Lagi',
                        showCancelButton: true,
                        cancelButtonText: 'Batalkan',
                    }).then(result => {
                        if (result.isConfirmed) {
                            printNumbering(); // Retry the request
                        }
                    });
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

                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Terjadi kesalahan',
                            confirmButtonText: 'Coba Lagi',
                            showCancelButton: true,
                            cancelButtonText: 'Batalkan',
                        }).then(result => {
                            if (result.isConfirmed) {
                                generateCheckedNumbering(); // Retry the request
                            }
                        });
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

        function rearrangeGroup(id, no_form) {
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
                    form_cut_id : id,
                    no_form : no_form
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

        function modifyGroupStocker(element, index) {
            let currentVal = element.value;
            let currentQty = element.options[element.selectedIndex].getAttribute('data-qty');
            let qtyCutElement = document.getElementById('mod_qty_cut_'+index);
            let originalElement = document.getElementById('mod_original_qty_'+index);
            let differenceElement = document.getElementById('mod_difference_qty_'+index);
            let ratioElement = document.getElementById('mod_ratio_'+index);
            let rangeAwalElement = document.getElementById("mod_range_awal_"+index);
            let rangeAwalElementView = document.getElementById("mod_range_awal_view_"+index);
            let rangeAkhirElement = document.getElementById("mod_range_akhir_"+index);
            let rangeAkhirElementView = document.getElementById("mod_range_akhir_view_"+index);
            let noteElement = document.getElementById("mod_note_"+index);

            let currentQtyRatio = Number(ratioElement.value) * Number(currentQty);
            originalElement.value = currentQtyRatio;
            qtyCutElement.value = currentQtyRatio;

            modifyQty(qtyCutElement, index);
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
            modSizeQtyForm.append("form_cut_id", document.getElementById("form_cut_id").value);
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

                    swal.close();
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

        function redirectToCut(select) {
            const url = select.value;
            if (url) {
                window.location.href = url;
            }
        }

        function addSeparatePart(index) {
            let wrapper = document.getElementById(`separate_qty_wrapper_${index}`);
            let targetQty = parseInt(wrapper.dataset.qty);

            // Create new input
            let input = document.createElement("input");
            input.type = "number";
            input.className = "form-control form-control-sm separate-part";
            input.name = `separate_qty[${index}][]`;
            input.onkeyup = () => validateAndAdjust(index, input);
            input.onchange = () => validateAndAdjust(index, input);
            input.oninput = () => validateAndAdjust(index, input);
            input.setAttribute("min", 0);

            wrapper.appendChild(input);
            redistributeEvenly(wrapper, targetQty);
        }

        function removeSeparatePart(index) {
            let wrapper = document.getElementById(`separate_qty_wrapper_${index}`);
            let targetQty = parseInt(wrapper.dataset.qty);

            let lastInput = Array.from(wrapper.querySelectorAll('input')).pop();
            if (lastInput) {
                wrapper.removeChild(lastInput);
                redistributeEvenly(wrapper, targetQty);
            }
        }

        function redistributeEvenly(wrapper, targetQty) {
            let parts = wrapper.querySelectorAll(".separate-part");
            let count = parts.length;
            let base = Math.floor(targetQty / count);
            let remainder = targetQty % count;

            parts.forEach((input, i) => {
                input.value = base + (i < remainder ? 1 : 0);
            });

            validateSeparateSum(wrapper.id.replace("separate_qty_wrapper_", ""));
        }

        function validateSeparateSum(index) {
            let wrapper = document.getElementById(`separate_qty_wrapper_${index}`);
            let targetQty = parseInt(wrapper.dataset.qty);
            let sum = 0;

            wrapper.querySelectorAll(".separate-part").forEach(input => {
                sum += parseInt(input.value) || 0;
            });

            let note = document.getElementById(`separate_qty_note_${index}`);
            if (sum === targetQty) {
                note.textContent = `✔ Total OK (${sum})`;
                note.style.color = "green";
            } else {
                note.textContent = `✘ Total ${sum} (qty awal : ${targetQty})`;
                note.style.color = "red";
            }
        }

        function validateAndAdjust(index, changedInput) {
            if (changedInput.value < 0) {
                changedInput.value = 0;
            }

            let wrapper = document.getElementById(`separate_qty_wrapper_${index}`);
            let targetQty = parseInt(wrapper.dataset.qty);
            let inputs = Array.from(wrapper.querySelectorAll(".separate-part"));

            // current sum vs target
            let sum = inputs.reduce((acc, inp) => acc + (parseInt(inp.value) || 0), 0);
            let diff = targetQty - sum;

            // start adjusting from the *next* input
            let i = (inputs.indexOf(changedInput) + 1) % inputs.length;

            // loop around until diff is eaten up
            while (diff !== 0) {
                let inp = inputs[i];
                let current = parseInt(inp.value) || 0;
                let newVal = current + diff;

                if (newVal < 0) {
                    inp.value = 0;
                    diff = newVal; // leftover negative still to fix
                } else {
                    inp.value = newVal;
                    diff = 0; // all good
                }

                i = (i + 1) % inputs.length;
            }

            validateSeparateSum(index);
        }

        function validateAllSeparates() {
            let allValid = true;

            document.querySelectorAll("[id^='separate_qty_wrapper_']").forEach(wrapper => {
                let index = wrapper.id.replace("separate_qty_wrapper_", "");
                validateSeparateSum(index);

                let targetQty = parseInt(wrapper.dataset.qty);
                let sum = 0;

                wrapper.querySelectorAll(".separate-part").forEach(input => {
                    sum += parseInt(input.value) || 0;
                });

                if (sum !== targetQty) {
                    allValid = false;
                }
            });

            return allValid;
        }

        function submitSeparate() {
            if (!validateAllSeparates()) {
                Swal.fire({
                    icon: 'error',
                    title: 'Validasi Gagal',
                    text: 'Pastikan semua total jumlah sesuai dengan qty awal sebelum melanjutkan.',
                });
                return;
            }

            Swal.fire({
                icon: 'question',
                title: 'Konfirmasi',
                text: 'Apakah Anda yakin ingin memisahkan stocker ini?',
                showCancelButton: true,
                confirmButtonText: 'Ya, Pisahkan',
                cancelButtonText: 'Tidak',
            }).then(result => {
                if (result.isConfirmed) {
                    separateStocker();
                }
            });
        }

        function separateStocker() {
            Swal.fire({
                title: 'Please Wait...',
                html: 'Modifying Data...',
                didOpen: () => {
                    Swal.showLoading()
                },
                allowOutsideClick: false,
            });

            let separateQtyForm = new FormData(document.getElementById("separate-qty-form"));
            separateQtyForm.append("form_cut_id", document.getElementById("form_cut_id").value);
            separateQtyForm.append("no_form", document.getElementById("no_form_cut").value);

            return $.ajax({
                url: '{{ route('separate-stocker') }}',
                type: 'post',
                data: separateQtyForm,
                processData: false,
                contentType: false,
                success: function(res) {
                    if (res) {
                        if (res.status == 200) {
                            Swal.fire({
                                icon: 'success',
                                title: res.message,
                                html: res.data ? res.data.join(" Berhasil dipisahkan. <br> ") : "",
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

                    swal.close();
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
