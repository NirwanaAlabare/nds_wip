@extends('layouts.index')

@section('custom-link')
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@endsection

@section('content')
    @php
        $currentPiping = null;

        if (isset($piping)) {
            $currentPiping = $piping;
        }
    @endphp
    <div class="d-flex justify-content-between mb-3">
        <h5 class="fw-bold text-sb"><i class="fa fa-plus fa-sm"></i> Tambah Proses Piping</h5>
        <div class="d-flex justify-content-end gap-3">
            <a href="{{ route('create-new-piping-process') }}" class="btn btn-success btn-sm px-1 py-1 fw-bold"><i class="fas fa-plus"></i> NEW</a>
            <a href="{{ route('piping-process') }}" class="btn btn-primary btn-sm px-1 py-1"><i class="fas fa-reply"></i> Kembali ke Data Proses Piping</a>
        </div>
    </div>
    <input type="hidden" id="process" value="{{ $currentPiping ? $currentPiping->process : null }}"></input>
    {{-- PROCESS ONE --}}
        <form action="{{ route("store-piping-process") }}" method="post" id="piping-process-header" onsubmit="processOne(this, event)">
            @csrf
            <div class="card card-sb">
                <div class="card-header">
                    <h5 class="card-title fw-bold">
                        Header Data
                    </h5>
                </div>
                <div class="card-body">
                    <input type="hidden" id="process_1" name="process_1" value="1" readonly>
                    <div class="row justify-content-start align-items-end g-3">
                        <div class="col-6 col-md-4">
                            <div class="mb-1">
                                <div class="form-group mb-0">
                                    <label>Kode Piping</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="kode_piping" name="kode_piping" value="{{ $currentPiping ? $currentPiping->kode_piping : null }}" readonly>
                                        <button type="button" class="btn btn-sb-secondary" onclick="generateCode()"><i class="fa fa-rotate"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="mb-1">
                                <div class="form-group mb-0">
                                    <label>Buyer</label>
                                    @if ($currentPiping)
                                        <input type="hidden" class="form-control" id="buyer_id" name="buyer_id" value="{{ $currentPiping ? $currentPiping->masterPiping->buyer_id : null }}">
                                        <input type="text" class="form-control" id="buyer" name="buyer" value="{{ $currentPiping ? $currentPiping->masterPiping->buyer : null }}" readonly>
                                    @else
                                        <select class="form-select select2bs4" id="buyer_id" name="buyer_id">
                                            <option selected="selected" value="">Pilih Buyer</option>
                                            @foreach ($buyers as $buyer)
                                                <option value="{{ $buyer->id }}">
                                                    {{ $buyer->buyer }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <input type="hidden" class="form-control" id="buyer" name="buyer" value="{{ $currentPiping ? $currentPiping->masterPiping->buyer : null }}" readonly>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="mb-1">
                                <div class="form-group mb-0">
                                    <label>No. WS</label>
                                    @if ($currentPiping)
                                        <input type="hidden" class="form-control" id="act_costing_id" name="act_costing_id" value="{{ $currentPiping ? $currentPiping->masterPiping->act_costing_id : null }}">
                                        <input type="text" class="form-control" id="act_costing_ws" name="act_costing_ws" value="{{ $currentPiping ? $currentPiping->masterPiping->act_costing_ws : null }}" readonly>
                                    @else
                                        <select class="form-select select2bs4" id="act_costing_id" name="act_costing_id">
                                            <option selected="selected" value="">Pilih WS</option>
                                            {{-- select 2 option --}}
                                        </select>
                                    @endif
                                </div>
                                <input type="hidden" name="act_costing_ws" id="act_costing_ws">
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="mb-1">
                                <div class="form-group mb-0">
                                    <label>Style</label>
                                    <input type="text" class="form-control" id="style" name="style" value="{{ $currentPiping ? $currentPiping->masterPiping->style : null }}" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="mb-1">
                                <div class="form-group mb-0">
                                    <label>Color</label>
                                    @if ($currentPiping)
                                        <input type="text" class="form-control" id="color" name="color" value="{{ $currentPiping ? $currentPiping->color : null }}" readonly>
                                    @else
                                        <select class="form-select select2bs4" id="color" name="color">
                                            <option value=""></option>
                                        </select>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="mb-1">
                                <div class="form-group mb-0">
                                    <label>Part</label>
                                    @if ($currentPiping)
                                        <input type="hidden" class="form-control" id="master_piping_id" name="master_piping_id" value="{{ $currentPiping ? $currentPiping->masterPiping->id : null }}">
                                        <input type="text" class="form-control" id="part" name="part" value="{{ $currentPiping ? $currentPiping->masterPiping->part : null }}" readonly>
                                    @else
                                        <select class="form-select select2bs4" id="master_piping_id" name="master_piping_id">
                                            <option selected="selected" value="">Pilih Part</option>
                                            {{-- select 2 option --}}
                                        </select>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="mb-1">
                                <div class="form-group mb-0">
                                    <label>Panjang</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="panjang" name="panjang" value="{{ $currentPiping ? $currentPiping->masterPiping->panjang : null }}" readonly>
                                        <input type="text" class="form-control" id="unit" name="unit" value="{{ $currentPiping ? $currentPiping->masterPiping->unit : null }}" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex justify-content-center">
                                <button type="submit" class="btn btn-success mt-1 mb-3 fw-bold">START</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    {{-- END OF PROCESS ONE --}}

    {{-- ID --}}
        <input type="hidden" id="id" name="id" value="{{ $currentPiping ? $currentPiping->id : null }}" readonly>

    {{-- PROCESS TWO --}}
        @php
            $currentPipingDetail = null;
            $currentPipingDetails = null;

            if ($currentPiping && $currentPiping->process >= 2 && $currentPiping->pipingProcessDetails && $currentPiping->pipingProcessDetails->count() > 0) {
                $currentPipingDetail = $currentPiping->pipingProcessDetails->first();
                $currentPipingDetails = $currentPiping->pipingProcessDetails;
            }
        @endphp

        <form action="{{ route("store-piping-process") }}" method="post" id="piping-process-scan" onsubmit="processTwo(this, event)" class="{{ ($currentPiping ? ($currentPiping->process >= 1 ? "" : "d-none") : "d-none") }}">
            @csrf
            <div class="card card-sb">
                <div class="card-header">
                    <h5 class="card-title fw-bold">
                        Scan Roll
                    </h5>
                </div>
                <div class="card-body">
                    <input type="hidden" id="process_2" name="process_2" value="2" readonly>
                    <div class="row justify-content-start align-items-end g-3 mb-3">
                        {{-- Switch Method --}}
                        <div class="col-12 col-md-12">
                            <div class="d-flex justify-content-center mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="switch-method" checked onchange="switchMethod(this)">
                                    <label class="form-check-label" id="to-scan">Scan Roll</label>
                                    <label class="form-check-label d-none" id="to-multi">Multi Roll</label>
                                </div>
                            </div>
                        </div>
                        {{-- Scan Method --}}
                        <div class="col-md-12 {{ ($currentPiping ? ($currentPiping->method == "single" ? "" : "d-none") : "") }}" id="scan-method">
                            <div class="mb-3" id="reader"></div>
                            <div class="row align-items-end g-3">
                                <div class="col-6 col-md-6">
                                    <label class="form-label">ID Roll</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control form-control-sm" name="id_roll" id="id_roll" value="{{ $currentPipingDetail ? $currentPipingDetail->id_roll : null }}">
                                        <button class="btn btn-sm btn-success" type="button" id="get-button" onclick="fetchScan()">Get</button>
                                        <button class="btn btn-sm btn-primary" type="button" id="scan-button" onclick="refreshScan()">Scan</button>
                                    </div>
                                </div>
                                <input type="hidden" name="form_cut_id" id="form_cut_id" value="{{ $currentPipingDetail ? $currentPipingDetail->form_cut_id : null }}" readonly>
                                <div class="col-6 col-md-6">
                                    <label class="form-label">No. Form</label>
                                    <input type="text" class="form-control form-control-sm" name="no_form" id="no_form" value="{{ $currentPipingDetail ? ($currentPipingDetail->formCutInput ? $currentPipingDetail->formCutInput->no_form : "-") : null }}" readonly>
                                </div>
                                <div class="col-6 col-md-6">
                                    <label class="form-label">ID Item</label>
                                    <input type="text" class="form-control form-control-sm" name="id_item" id="id_item" value="{{ $currentPipingDetail ? ($currentPipingDetail->scannedItem ? $currentPipingDetail->scannedItem->id_item : "-") : null }}" readonly>
                                </div>
                                <div class="col-6 col-md-6">
                                    <label class="form-label">Detail Item</label>
                                    <input type="text" class="form-control form-control-sm" name="detail_item" id="detail_item" value="{{ $currentPipingDetail ? ($currentPipingDetail->scannedItem ? $currentPipingDetail->scannedItem->detail_item : "-") : null }}" readonly>
                                </div>
                                <div class="col-6 col-md-6">
                                    <label class="form-label">Color Act</label>
                                    <input type="text" class="form-control form-control-sm" name="color_act" id="color_act" value="{{ $currentPipingDetail ? $currentPipingDetail->color_act : null }}">
                                </div>
                                <div class="col-6 col-md-6">
                                    <label class="form-label">Group</label>
                                    <input type="text" class="form-control form-control-sm" name="group_item" id="group_item" value="{{ $currentPipingDetail ? $currentPipingDetail->group : null }}" readonly>
                                </div>
                                <div class="col-4 col-md-4">
                                    <label class="form-label">Lot</label>
                                    <input type="text" class="form-control form-control-sm" name="lot_item" id="lot_item" value="{{ $currentPipingDetail ? $currentPipingDetail->lot : null }}" readonly>
                                </div>
                                <div class="col-4 col-md-4">
                                    <label class="form-label">No. Roll Buyer</label>
                                    <input type="text" class="form-control form-control-sm" name="no_roll_item" id="no_roll_item" value="{{ $currentPipingDetail ? $currentPipingDetail->no_roll : null }}" readonly>
                                </div>
                                <div class="col-4 col-md-4">
                                    <label class="form-label">Qty</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control form-control-sm" name="qty_item" id="qty_item" value="{{ $currentPipingDetail ? $currentPipingDetail->qty : null }}" readonly>
                                        <input type="text" class="form-control form-control-sm" name="unit_item" id="unit_item" value="{{ $currentPipingDetail ? $currentPipingDetail->unit : null }}" readonly>
                                    </div>
                                </div>
                                <div class="col-12 col-md-12">
                                    <button type="submit" class="btn btn-sm btn-success btn-block fw-bold mt-3">NEXT</button>
                                </div>
                            </div>
                        </div>
                        {{-- Multi Method --}}
                        <div class="col-md-12 {{ ($currentPiping ? ($currentPiping->method == "single" ? "d-none" : "") : "d-none") }}" id="multi-method">
                            <div id="multi-rolls" class="mb-3">
                                @if ($currentPipingDetails && $currentPipingDetails->count() > 0)
                                    @foreach ($currentPipingDetails as $pipingDetail)
                                        <div class="row align-items-end" id="multi-roll-{{ $loop->index+1 }}">
                                            <div class="col-md-2">
                                                <label class="form-label">ID Roll</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control" id="id_rolls_{{ $loop->index+1 }}" name="id_rolls[{{ $loop->index+1 }}]" value="{{ $pipingDetail->id_roll }}" disabled>
                                                    <button class="btn btn-success" type="button" id="get_button_{{ $loop->index+1 }}" onclick="getMultiItemForm({{ $loop->index+1 }})" disabled>Get</button>
                                                </div>
                                            </div>
                                            <div class="col-md-2 d-none">
                                                <label class="form-label">ID Item</label>
                                                <input type="text" class="form-control" id="id_item_rolls_{{ $loop->index+1 }}" name="id_item_rolls[{{ $loop->index+1 }}]" value="{{ $pipingDetail->id_item }}" disabled>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">No. Form</label>
                                                <input type="hidden" id="id_form_rolls_{{ $loop->index+1 }}" name="id_form_rolls[{{ $loop->index+1 }}]" value="{{ $pipingDetail->form_cut_id }}" disabled>
                                                <input type="text" class="form-control" id="no_form_rolls_{{ $loop->index+1 }}" name="no_form_rolls[{{ $loop->index+1 }}]" value="{{ $pipingDetail->formCutInput ? $pipingDetail->formCutInput->no_form : "-" }}" disabled>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Color</label>
                                                <input type="text" class="form-control" id="color_rolls_{{ $loop->index+1 }}" name="color_rolls[{{ $loop->index+1 }}]" value="{{ $pipingDetail->color_act }}" disabled>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Group</label>
                                                <input type="text" class="form-control" id="group_rolls_{{ $loop->index+1 }}" name="group_rolls[{{ $loop->index+1 }}]" value="{{ $pipingDetail->group }}" disabled>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Lot</label>
                                                <input type="text" class="form-control" id="lot_rolls_{{ $loop->index+1 }}" name="lot_rolls[{{ $loop->index+1 }}]" value="{{ $pipingDetail->lot }}" disabled>
                                            </div>
                                            <div class="col-md-2 d-none">
                                                <label class="form-label">No. Roll Buyer</label>
                                                <input type="text" class="form-control" id="no_rolls_{{ $loop->index+1 }}" name="no_rolls[{{ $loop->index+1 }}]" value="{{ $pipingDetail->no_roll }}" disabled>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Qty</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control" id="qty_rolls_{{ $loop->index+1 }}" name="qty_rolls[{{ $loop->index+1 }}]" value="{{ $pipingDetail->qty }}" disabled>
                                                    <input type="text" class="form-control" id="unit_rolls_{{ $loop->index+1 }}" name="unit_rolls[{{ $loop->index+1 }}]" value="{{ $pipingDetail->unit }}" disabled>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="row align-items-end" id="multi-roll-1">
                                        <div class="col-md-2">
                                            <label class="form-label">ID Roll</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control" id="id_rolls_1" name="id_rolls[1]">
                                                <button class="btn btn-success" type="button" id="get_button_1" onclick="getMultiItemForm(1)">Get</button>
                                            </div>
                                        </div>
                                        <div class="col-md-2 d-none">
                                            <label class="form-label">ID Item</label>
                                            <input type="text" class="form-control" id="id_item_rolls_1" name="id_item_rolls[1]" readonly>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">No. Form</label>
                                            <select class="form-select" id="id_form_rolls_1" name="id_form_rolls[1]" onchange="getMultiItemPiping(1)">
                                                {{-- Form Options --}}
                                            </select>
                                            <input type="hidden" id="no_form_rolls_1" name="no_form_rolls[1]">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Color</label>
                                            <input type="text" class="form-control" id="color_rolls_1" name="color_rolls[1]" readonly>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Group</label>
                                            <input type="text" class="form-control" id="group_rolls_1" name="group_rolls[1]" readonly>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Lot</label>
                                            <input type="text" class="form-control" id="lot_rolls_1" name="lot_rolls[1]" readonly>
                                        </div>
                                        <div class="col-md-2 d-none">
                                            <label class="form-label">No. Roll</label>
                                            <input type="text" class="form-control" id="no_rolls_1" name="no_rolls[1]" readonly>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Qty</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control" id="qty_rolls_1" name="qty_rolls[1]" readonly>
                                                <input type="text" class="form-control" id="unit_rolls_1" name="unit_rolls[1]" readonly>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div class="d-flex justify-content-end mb-3">
                                <button type="button" class="btn btn-sb-secondary w-auto" id="add-new-roll" onclick="addMultiRoll()"><i class="fa fa-plus fa-sm"></i></button>
                            </div>
                            <div class="row justify-content-end g-3 mb-3">
                                @if ($currentPiping && $currentPiping->process >= 2 && $currentPiping->pipingProcessDetails && $currentPiping->pipingProcessDetails->count() > 0)
                                    <div class="col-md-6">
                                        <label class="form-label">Total Roll</label>
                                        <input type="text" class="form-control form-control-sm" id="total_roll" name="total_roll" value="{{ $currentPiping->pipingProcessDetails->count() }}" readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Total Qty</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control form-control-sm" id="total_qty" name="total_qty" value="{{ $currentPiping->pipingProcessDetails->sum('qty') }}" readonly>
                                            <input type="text" class="form-control form-control-sm" id="total_unit" name="total_unit" value="{{ $currentPiping->pipingProcessDetails->first()->unit }}" readonly>
                                        </div>
                                    </div>
                                @else
                                    <div class="col-md-6">
                                        <label class="form-label">Total Roll</label>
                                        <input type="text" class="form-control form-control-sm" id="total_roll" name="total_roll" value="" readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Total Qty</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control form-control-sm" id="total_qty" name="total_qty" value="" readonly>
                                            <input type="text" class="form-control form-control-sm" id="total_unit" name="total_unit" value="" readonly>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <button type="submit" class="btn btn-sm btn-block btn-sb fw-bold">NEXT</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    {{-- END OF PROCESS TWO --}}

    {{-- PROCESS THREE --}}
        <form action="{{ route("store-piping-process") }}" method="post" id="piping-process-calculate" onsubmit="processThree(this, event)" class="{{ ($currentPiping ? ($currentPiping->process >= 2 ? "" : "d-none") : "d-none") }}">
            @csrf
            <div class="card card-sb">
                <div class="card-header">
                    <h5 class="card-title fw-bold">
                        Process
                    </h5>
                </div>
                <div class="card-body">
                    <input type="hidden" id="process_3" name="process_3" value="3" readonly>
                    <div class="row justify-content-start align-items-end g-3 mb-3">
                        <div class="col-12">
                            <label class="form-label">Arah Potong Roll</label>
                            <select class="form-select" id="arah_potong" name="arah_potong" onchange="calculate()">
                                <option value="" {{ $currentPiping ? ($currentPiping->arah_potong == "" ? "selected" : "") : "" }} disabled>Pilih Arah Potong</option>
                                <option value="vertical" {{ $currentPiping ? ($currentPiping->arah_potong == "vertical" ? "selected" : "") : "" }}>Vertikal</option>
                                <option value="horizontal" {{ $currentPiping ? ($currentPiping->arah_potong == "horizontal" ? "selected" : "") : "" }}>Horizontal</option>
                            </select>
                        </div>
                        <div class="col-4 col-md-3">
                            <label class="form-label">Group</label>
                            <input type="text" class="form-control" id="group" name="group" value="{{ $currentPiping ? $currentPiping->group : null }}">
                        </div>
                        <div class="col-4 col-md-3">
                            <label class="form-label">ID Item</label>
                            <input type="text" class="form-control" id="id_item" name="id_item" value="{{ $currentPiping ? $currentPiping->id_item : null }}" readonly>
                        </div>
                        <div class="col-4 col-md-3">
                            <label class="form-label">Lot</label>
                            <input type="text" class="form-control" id="lot" name="lot" value="{{ $currentPiping ? $currentPiping->lot : null }}" readonly>
                        </div>
                        <div class="col-4 col-md-3">
                            <label class="form-label">No. Roll</label>
                            <input type="text" class="form-control" id="no_roll" name="no_roll" value="{{ $currentPiping ? $currentPiping->no_roll : null }}" readonly>
                        </div>
                        <div class="col-4 col-md-3">
                            <label class="form-label">Qty Awal</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="qty_awal" name="qty_awal" value="{{ $currentPiping ? $currentPiping->qty_awal : null }}" readonly>
                                <input type="text" class="form-control" id="qty_awal_unit" name="qty_awal_unit" value="{{ $currentPiping ? $currentPiping->unit : null }}" readonly>
                            </div>
                        </div>
                        <div class="col-4 col-md-3">
                            <label class="form-label">Qty</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="qty" name="qty" value="{{ $currentPiping ? $currentPiping->qty : null }}" onkeyup="calculate()">
                                <input type="text" class="form-control" id="qty_unit" name="qty_unit" value="{{ $currentPiping ? $currentPiping->unit : null }}" readonly>
                            </div>
                        </div>
                        <div class="col-4 col-md-3">
                            <label class="form-label">Qty Konversi</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="qty_konversi" name="qty_konversi" value="{{ $currentPiping ? $currentPiping->qty_konversi : null }}" readonly>
                                <input type="text" class="form-control" id="qty_konversi_unit" name="qty_konversi_unit" value="{{ $currentPiping ? $currentPiping->unit : null }}" readonly>
                            </div>
                        </div>
                        <div class="col-4 col-md-3">
                            <label class="form-label">Panjang/pcs</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="panjang_roll_piping" name="panjang_roll_piping" value="{{ $currentPiping ? ($currentPiping->masterPiping ? $currentPiping->masterPiping->panjang : null) : null }}" readonly>
                                <input type="text" class="form-control" id="panjang_roll_piping_unit" name="panjang_roll_piping_unit" value="{{ $currentPiping ? ($currentPiping->masterPiping ? $currentPiping->masterPiping->unit : null) : null }}" readonly>
                            </div>
                        </div>
                        <div class="col-4 col-md-3">
                            <label class="form-label">Lebar Kain Act</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="lebar_kain_act" name="lebar_kain_act" value="{{ $currentPiping ? $currentPiping->lebar_kain_act : null }}" onkeyup="calculate()">
                                <input type="text" class="form-control" id="lebar_kain_act_unit" name="lebar_kain_act_unit" value="{{ $currentPiping ? $currentPiping->lebar_kain_act_unit : null }}" readonly>
                            </div>
                        </div>
                        <div class="col-4 col-md-3">
                            <label class="form-label">Lebar Cuttable Kain</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="lebar_kain_cuttable" name="lebar_kain_cuttable" value="{{ $currentPiping ? $currentPiping->lebar_kain_cuttable : null }}" onkeyup="calculate()">
                                <input type="text" class="form-control" id="lebar_kain_cuttable_unit" name="lebar_kain_cuttable_unit" value="{{ $currentPiping ? $currentPiping->lebar_kain_cuttable_unit : null }}" readonly>
                            </div>
                        </div>
                        <div class="col-4 col-md-3">
                            <label class="form-label">Lebar Roll Piping</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="lebar_roll_piping" name="lebar_roll_piping" value="{{ $currentPiping ? $currentPiping->lebar_roll_piping : null }}" onkeyup="calculate()">
                                <input type="text" class="form-control" id="lebar_roll_piping_unit" name="lebar_roll_piping_unit" value="{{ $currentPiping ? $currentPiping->lebar_roll_piping_unit : null }}" readonly>
                            </div>
                        </div>
                        <div class="col-4 col-md-3">
                            <label class="form-label">Output Total Roll</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="output_total_roll" name="output_total_roll" value="{{ $currentPiping ? $currentPiping->output_total_roll : null }}" onkeyup="calculate()">
                                <input type="text" class="form-control" id="output_total_roll_unit" name="output_total_roll_unit" value="ROLL" readonly>
                            </div>
                        </div>
                        <div class="col-4 col-md-3">
                            <label class="form-label">Jenis Potong Piping</label>
                            <select class="form-select" id="jenis_potong_piping" name="jenis_potong_piping" onchange="calculate()">
                                <option value="" {{ $currentPiping ? ($currentPiping->jenis_potong_piping == "" ? "selected" : "") : "" }} disabled>Pilih Jenis Potong</option>
                                <option value="straight" {{ $currentPiping ? ($currentPiping->jenis_potong_piping == "straight" ? "selected" : "") : "" }}>Straigth</option>
                                <option value="bias" {{ $currentPiping ? ($currentPiping->jenis_potong_piping == "bias" ? "selected" : "") : "" }}>Bias</option>
                            </select>
                        </div>
                        <div class="col-4 col-md-3">
                            <label class="form-label">Est. Output/Roll</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="estimasi_output_roll" name="estimasi_output_roll" value="{{ $currentPiping ? $currentPiping->estimasi_output_roll : null }}" readonly>
                                <input type="text" class="form-control" id="estimasi_output_roll_unit" name="estimasi_output_roll_unit" value="PCS" readonly>
                            </div>
                        </div>
                        <div class="col-4 col-md-3">
                            <label class="form-label">Est. Output Total</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="estimasi_output_total" name="estimasi_output_total" value="{{ $currentPiping ? $currentPiping->estimasi_output_total : null }}" readonly>
                                <input type="text" class="form-control" id="estimasi_output_total_unit" name="estimasi_output_total_unit" value="PCS" readonly>
                            </div>
                        </div>
                        <div class="col-4 col-md-3">
                            <button type="submit" class="btn btn-success btn-block" >SIMPAN</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    {{-- END OF PROCESS THREE --}}

    {{-- FINISH --}}
        <div id="piping-process-finish" class="my-5 {{ ($currentPiping ? ($currentPiping->process >= 3 ? "" : "d-none") : "d-none") }}">
            <h3 class="text-center text-sb fw-bold">PROCESS FINISHED</h3>
            <h5 class="text-center">Last Update : <span id="last-update" class="fw-bold">{{ $currentPiping ? $currentPiping->updated_at : "-" }}</span></h5>
        </div>
    {{-- END OF THE LINE --}}
@endsection

@section('custom-script')
    <!-- Select2 -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <!-- Page specific script -->
    <script>
        // Initial Window On Load Event
        $(document).ready(async function () {
            initial();

            disableFormSubmit("#piping-process-header");
            disableFormSubmit("#piping-process-scan");
            disableFormSubmit("#piping-process-calculate");
        });

        // Select2 Autofocus
        $(document).on('select2:open', () => {
            document.querySelector('.select2-search__field').focus();
        });

        // Initialize Select2 Elements
        $('.select2').select2()

        // Initialize Select2BS4 Elements
        $('.select2bs4').select2({
            theme: 'bootstrap4',
        })

        // Init
        async function initial() {
            document.getElementById("loading").classList.remove("d-none");

            if (document.getElementById("process").value > 0) {
                // Check Current Process
                await checkProcess(document.getElementById("process").value);
            } else {
                //Reset Form
                if (document.getElementById('piping-process-header')) {
                    document.getElementById('piping-process-header').reset();

                    $("#buyer_id").val(null).trigger("change");
                }

                // Select2 Prevent Step-Jump Input ( Step = WS -> Color -> Panel )
                $("#color").prop("disabled", true);
                $("#master_piping_id").prop("disabled", true);

                // Generate Code
                generateCode();
            }

            await document.getElementById("loading").classList.add("d-none");
        }

        // CHECK PROCESS
            async function checkProcess(process) {
                document.getElementById("loading").classList.remove("d-none");

                switch (process) {
                    case "1" :
                        initProcessTwo();

                        break;
                    case "2" :
                        initProcessThree();

                        break;
                    case "3" :
                        initFinish();

                        break;
                }

                document.getElementById("loading").classList.add("d-none");
            }

        // PROCESS ONE :
            // Generate Piping Code
            function generateCode() {
                document.getElementById("loading").classList.remove("d-none");

                $.ajax({
                    url: "{{ route("generate-piping-process") }}",
                    type: "get",
                    dataType: "json",
                    success: function (response) {
                        $("#kode_piping").val(response);

                        document.getElementById("loading").classList.add("d-none");
                    },
                    error: function (jqXHR) {
                        console.log(jqXHR);

                        document.getElementById("loading").classList.add("d-none");
                    }
                });
            }

            // Step One (Buyer) on change event
            $('#buyer_id').on('change', function(e) {
                if (this.value) {
                    $('#buyer').val($('#buyer_id').find(":selected").text());

                    updateOrderList();
                }
            });

            // Step Two (Order) on change event
            $('#act_costing_id').on('change', function(e) {
                if (this.value) {
                    $('#act_costing_ws').val($('#act_costing_id').find(":selected").text());

                    updateOrderInfo();
                    updateColorList();
                }
            });

            // Step Three (Color) on change event
            $('#color').on('change', function(e) {
                if (this.value) {
                    updatePartList();
                }
            });

            // Step Four (Part) on change event
            $('#master_piping_id').on('change', function(e) {
                if (this.value) {
                    takeMasterPiping();
                }
            });

            // Get Master Piping Buyer List
            function updateBuyerList() {
                document.getElementById('loading').classList.remove("d-none");

                document.getElementById("buyer_id").value = null;
                document.getElementById("buyer_id").innerHTML = null;

                $.ajax({
                    url: "{{ route("list-master-piping") }}",
                    type: "get",
                    data: {
                        data: 'buyer'
                    },
                    dataType: "json",
                    success: function (response) {
                        if (response) {
                            if (response.length > 0) {
                                let selectElement = document.getElementById("buyer_id");

                                for (let i = 0; i < response.length; i++) {
                                    let newOption = document.createElement("option");
                                    newOption.value = response[i].buyer_id;
                                    newOption.innerHTML = response[i].buyer;

                                    selectElement.prepend(newOption);
                                }

                                $("#buyer_id").val(response[response.length-1].buyer_id).trigger("change");
                            }
                        }

                        document.getElementById("loading").classList.add("d-none");
                    },
                    error: function (jqXHR) {
                        console.error(jqXHR);

                        document.getElementById("loading").classList.add("d-none");
                    }
                });
            }

            // Get Master Piping Order List
            function updateOrderList() {
                document.getElementById('loading').classList.remove("d-none");

                document.getElementById("act_costing_id").value = null;
                document.getElementById("act_costing_id").innerHTML = null;

                $.ajax({
                    url: "{{ route("list-master-piping") }}",
                    type: "get",
                    data: {
                        data: 'worksheet',
                        buyer_id: $('#buyer_id').val()
                    },
                    dataType: "json",
                    success: function (response) {
                        if (response) {
                            if (response.length > 0) {
                                let selectElement = document.getElementById("act_costing_id");

                                for (let i = 0; i < response.length; i++) {
                                    let newOption = document.createElement("option");
                                    newOption.value = response[i].act_costing_id;
                                    newOption.innerHTML = response[i].act_costing_ws;

                                    selectElement.prepend(newOption);
                                }

                                $("#act_costing_id").val(response[response.length-1].act_costing_id).trigger("change");
                            }
                        }

                        document.getElementById('loading').classList.add('d-none');
                    },
                    error: function (jqXHR) {
                        console.error(jqXHR);

                        document.getElementById('loading').classList.add('d-none');
                    }
                });
            }

            // Update Order Information Based on Order WS and Order Color
            function updateOrderInfo() {
                return $.ajax({
                    url: '{{ route('get-general-order') }}',
                    type: 'get',
                    data: {
                        act_costing_id: $('#act_costing_id').val()
                    },
                    dataType: 'json',
                    success: function (res) {
                        if (res) {
                            console.log(res);
                            document.getElementById('act_costing_ws').value = res.kpno;
                            document.getElementById('buyer').value = res.buyer;
                            document.getElementById('style').value = res.styleno;
                        }
                    },
                    error: function (jqXHR) {
                        console.error(jqXHR);

                        document.getElementById('loading').classList.add('d-none');
                    }
                });
            }

            // Get Master Piping Color List
            function updateColorList() {
                document.getElementById('loading').classList.remove("d-none");

                document.getElementById("color").value = null;
                document.getElementById("color").innerHTML = null;

                $.ajax({
                    url: "{{ route("get-colors") }}",
                    type: "get",
                    data: {
                        act_costing_id: $('#act_costing_id').val()
                    },
                    dataType: "json",
                    success: function (response) {
                        if (response) {
                            if (response.length > 0) {
                                $("#color").prop("disabled", false);

                                let selectElement = document.getElementById("color");

                                for (let i = 0; i < response.length; i++) {
                                    let newOption = document.createElement("option");
                                    newOption.value = response[i].color;
                                    newOption.innerHTML = response[i].color;

                                    selectElement.prepend(newOption);
                                }

                                $("#color").val(response[response.length-1].color).trigger("change");
                            }
                        }

                        document.getElementById('loading').classList.add('d-none');
                    },
                    error: function (jqXHR) {
                        console.error(jqXHR);

                        document.getElementById('loading').classList.add('d-none');
                    }
                });
            }

            // Get Master Piping Part List
            function updatePartList() {
                document.getElementById('loading').classList.remove("d-none");

                document.getElementById("master_piping_id").value = null;
                document.getElementById("master_piping_id").innerHTML = null;

                $.ajax({
                    url: "{{ route("list-master-piping") }}",
                    type: "get",
                    data: {
                        data: 'part',
                        buyer_id: $('#buyer_id').val(),
                        act_costing_id: $('#act_costing_id').val()
                    },
                    dataType: "json",
                    success: function (response) {
                        if (response) {
                            if (response.length > 0) {
                                $("#master_piping_id").prop("disabled", false);

                                let selectElement = document.getElementById("master_piping_id");

                                for (let i = 0; i < response.length; i++) {
                                    let newOption = document.createElement("option");
                                    newOption.value = response[i].id;
                                    newOption.innerHTML = response[i].part.toUpperCase();

                                    selectElement.prepend(newOption);
                                }

                                $("#master_piping_id").val(response[response.length-1].part).trigger("change");
                            }
                        }

                        document.getElementById('loading').classList.add('d-none');
                    },
                    error: function (jqXHR) {
                        console.error(jqXHR);

                        document.getElementById('loading').classList.add('d-none');
                    }
                });
            }

            // Take Master Piping Data
            function takeMasterPiping() {
                $.ajax({
                    url: "{{ route("take-master-piping") }}/"+$("#master_piping_id").val(),
                    type: "get",
                    data: "data",
                    dataType: "json",
                    success: function (response) {
                        document.getElementById("panjang").value = response.panjang;
                        document.getElementById("unit").value = response.unit.toUpperCase();
                    }
                });
            }

            // Prevent Form Submit When Pressing Enter
            document.getElementById("piping-process-header").onkeypress = function(e) {
                var key = e.charCode || e.keyCode || 0;
                if (key == 13) {
                    e.preventDefault();
                }
            }

            // Submit Process One
            function processOne(e, event) {
                document.getElementById("loading").classList.remove("d-none");

                event.preventDefault();

                let form = new FormData(e);

                let dataObj = {
                    "process": 1,
                }

                form.forEach((value, key) => dataObj[key] = value);

                $.ajax({
                    url: "{{ route("store-piping-process") }}",
                    type: "post",
                    data: dataObj,
                    dataType: "json",
                    success: function (response) {
                        if (response.status == 200) {
                            if (response.additional) {
                                document.getElementById("id").value = response.additional.id;
                            }

                            initProcessTwo();
                        }

                        document.getElementById("loading").classList.add("d-none");
                    },
                    error: function (jqXHR) {
                        console.error(jqXHR);

                        document.getElementById("loading").classList.add("d-none");
                    }
                });
            }
        // END OF PROCESS ONE

        // PROCESS TWO :
            // -Init Process Two-
            function initProcessTwo() {
                initScan();

                resetScanMethod();
                resetMultiMethod();

                document.getElementById("piping-process-scan").classList.remove("d-none");

                let form = document.getElementById("piping-process-header");

                for (let i = 0; i < form.length; i++) {
                    form[i].setAttribute("disabled", "true");
                }

                document.getElementById("switch-method").checked = true;

                $("#switch-method").trigger("change");
            }

            // -Scan Roll-
            var method;

            function switchMethod(element) {
                if (element.checked) {
                    toScanMethod();
                } else {
                    toMultiMethod();
                }
            }

            function toMultiMethod() {
                method = "multi";

                document.getElementById("scan-method").classList.add('d-none');
                document.getElementById("to-scan").classList.add('d-none');

                resetScanMethod();

                document.getElementById("multi-method").classList.remove('d-none');
                document.getElementById("to-multi").classList.remove('d-none');

                clearScan();

                location.href = "#piping-process-scan";
            }

            function toScanMethod() {
                method = "scan";

                document.getElementById("multi-method").classList.add('d-none');
                document.getElementById("to-multi").classList.add('d-none');

                resetMultiMethod();

                document.getElementById("scan-method").classList.remove('d-none');
                document.getElementById("to-scan").classList.remove('d-none');

                initScan();

                location.href = "#piping-process-scan";
            }

            async function resetScanMethod() {
                let idRoll = document.getElementById('id_roll');

                idRoll.value = null;

                fetchScan();
            }

            async function resetMultiMethod() {
                let rolls = document.querySelectorAll('#multi-rolls .row');

                for (let i = 0; i < rolls.length; i++) {
                    let roll = document.getElementById('multi-roll-' + (i+1));

                    if (i == 0) {
                        clearMultiItem((i+1));
                    } else {
                        roll.remove()
                    }
                }

                calculateMultiItem();
            }

            // -Scanner-
            var html5QrcodeScanner = new Html5Qrcode("reader");
            var scannerInitialized = false;

            // -Init Scanner-
            async function initScan() {
                if (document.getElementById("reader")) {
                    if (html5QrcodeScanner == null || (html5QrcodeScanner && (html5QrcodeScanner.isScanning == false))) {
                        const qrCodeSuccessCallback = (decodedText, decodedResult) => {
                            // handle the scanned code as you like, for example:
                            console.log(`Code matched = ${decodedText}`, decodedResult);

                            // store to input text
                            let breakDecodedText = decodedText.split('-');

                            document.getElementById('kode_barang').value = breakDecodedText[0];

                            getScannedItem(breakDecodedText[0]);

                            clearScan();
                        };
                        const config = { fps: 10, qrbox: { width: 250, height: 250 } };

                        await html5QrcodeScanner.start({ facingMode: "environment" }, config, qrCodeSuccessCallback);
                    }
                }
            }

            // -Clear Scanner-
            async function clearScan() {
                console.log(html5QrcodeScanner, html5QrcodeScanner.getState());
                if (html5QrcodeScanner && (html5QrcodeScanner.isScanning)) {
                    await html5QrcodeScanner.stop();
                    await html5QrcodeScanner.clear();
                }
            }

            // -Refresh Scanner-
            async function refreshScan() {
                await clearScan();
                await initScan();
            }

            // -Fetch Scanned Item Data-
            async function fetchScan() {
                let kodeBarang = document.getElementById('id_roll').value;

                await getScannedItem(kodeBarang);
            }

            // -Get Scanned Item Data-
            async function getScannedItem(id) {
                document.getElementById("loading").classList.remove("d-none");

                document.getElementById("form_cut_id").value = "";
                document.getElementById("no_form").value = "";
                document.getElementById("id_item").value = "";
                document.getElementById("detail_item").value = "";
                document.getElementById("group_item").value = "";
                document.getElementById("lot_item").value = "";
                document.getElementById("no_roll_item").value = "";
                document.getElementById("qty_item").value = "";
                document.getElementById("unit_item").value = "";
                document.getElementById("color_act").value = "";

                if (isNotNull(id)) {
                    return $.ajax({
                        url: '{{ route('item-piping') }}/' + id,
                        type: 'get',
                        dataType: 'json',
                        success: function(res) {
                            console.log(res);

                            if (typeof res === 'object' && res !== null) {
                                document.getElementById("form_cut_id").value = res.form_cut_id;
                                document.getElementById("no_form").value = res.no_form_cut_input;
                                document.getElementById("id_item").value = res.id_item;
                                document.getElementById("detail_item").value = res.detail_item;
                                document.getElementById("group_item").value = res.group_roll;
                                document.getElementById("lot_item").value = res.lot;
                                document.getElementById("no_roll_item").value = res.no_roll;
                                document.getElementById("qty_item").value = res.piping;
                                document.getElementById("unit_item").value = res.unit;
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal',
                                    text: res ? res : 'Roll tidak tersedia.',
                                    showCancelButton: false,
                                    showConfirmButton: true,
                                    confirmButtonText: 'Oke',
                                });
                            }

                            document.getElementById("loading").classList.add("d-none");
                        },
                        error: function(jqXHR) {
                            console.log(jqXHR);

                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: jqXHR.responseText ? jqXHR.responseText : 'Roll tidak tersedia.',
                                showCancelButton: false,
                                showConfirmButton: true,
                                confirmButtonText: 'Oke',
                            });

                            document.getElementById("loading").classList.add("d-none");
                        }
                    });
                }

                document.getElementById("loading").classList.add("d-none");
            }

            // -Add Multi Roll-
            async function addMultiRoll() {
                if (document.getElementById('id_rolls_1').value && document.getElementById('id_form_rolls_1').value) {
                    // Clone the existing roll element
                    let rollElement = document.getElementById('multi-roll-1');
                    let newRoll = rollElement.cloneNode(true);  // Clone the element including its children

                    // Update the IDs and names to make them unique for the new roll
                    let rollCount = document.querySelectorAll('#multi-rolls .row').length + 1;  // Get the next roll count
                    newRoll.id = 'multi-roll-' + rollCount;  // Update the ID for the new roll

                    // Update all input names and IDs within the new roll to ensure uniqueness
                    let inputs = newRoll.querySelectorAll('input, select, button');
                    inputs.forEach(function(input) {
                        input.value = null;

                        let id = input.getAttribute('id');
                        if (id) {
                            input.setAttribute('id', id.replace(/\d+/, rollCount));  // Update ID attribute
                        }

                        let name = input.getAttribute('name');
                        if (name) {
                            input.setAttribute('name', name.replace(/\d+/, rollCount));  // Update name attribute
                        }

                        if (id.includes("get_button")) {
                            let onclick = input.getAttribute('onclick');
                            if (onclick) {
                                input.setAttribute('onclick', onclick.replace(/\d+/, rollCount));  // Update onclick attribute
                            }
                        }

                        if (id.includes("id_form_rolls")) {
                            input.innerHTML = null;

                            let onchange = input.getAttribute('onchange');
                            if (onchange) {
                                input.setAttribute('onchange', onchange.replace(/\d+/, rollCount));  // Update onchange attribute
                            }
                        }
                    });

                    // Append the new roll element to the container
                    document.getElementById('multi-rolls').appendChild(newRoll);
                }
            }

            // -Check Multi Roll-
            function checkMultiItem(check = "all") {
                console.log(check);

                let unitSetting = document.getElementById('unit_rolls_1').value;

                if (check > 1) {
                    let roll = document.getElementById('multi-roll-' + (check));

                    let rollUnit = roll.querySelector('#unit_rolls_' + (check));

                    if (rollUnit.value != unitSetting) {
                        return false;
                    }
                } else {
                    let rolls = document.querySelectorAll('#multi-rolls .row');

                    for (let i = 1; i < rolls.length; i++) {
                        let roll = document.getElementById('multi-roll-' + (i+1));

                        let rollUnit = roll.querySelector('#unit_rolls_' + (i+1));

                        if (rollUnit.value != unitSetting) {
                            return false;
                        }
                    }
                }

                return true;
            }

            // -Calculate Multi Roll-
            function calculateMultiItem() {
                let totalRollQty = document.getElementById("total_qty");
                let totalRollUnit = document.getElementById("total_unit");

                console.log(totalRollQty, totalRollUnit);

                if (totalRollQty && totalRollUnit) {
                    totalRollQty.value = null;
                    totalRollUnit.value = null;

                    let rolls = document.querySelectorAll('#multi-rolls .row');

                    let totalRoll = 0;
                    for (let i = 0; i < rolls.length; i++) {
                        let roll = document.getElementById('multi-roll-' + (i+1));

                        let idRoll = document.getElementById('id_rolls_' + (i+1));

                        let rollQty = roll.querySelector('#qty_rolls_' + (i+1));
                        let rollUnit = roll.querySelector('#unit_rolls_' + (i+1));

                        rollQty.value ? totalRollQty.value = Number(totalRollQty.value) + Number(rollQty.value) : console.log("null value");
                        rollUnit.value ? totalRollUnit.value =  rollUnit.value : console.log("null value");

                        idRoll.value ? totalRoll++ : console.log("null value");
                    }

                    document.getElementById("total_roll").value = totalRoll;
                }
            }

            // -Get Multi Item Form-
            function getMultiItemForm(index) {
                document.getElementById("loading").classList.remove("d-none");

                let id  = document.getElementById("id_rolls_"+index).value;

                $.ajax({
                    url: "{{ route("item-forms-piping") }}/"+id,
                    type: "get",
                    dataType: "json",
                    success: function (response) {
                        let select = document.getElementById("id_form_rolls_"+index)

                        if (select) {
                            select.innerHTML = null;

                            if (response && response.length > 0) {
                                for (let i = 0; i < response.length ;i++) {
                                    let option = document.createElement("option");
                                    option.setAttribute("value", response[i].id);
                                    option.innerText = response[i].no_form;

                                    select.appendChild(option);
                                }
                            }

                            $("#id_form_rolls_"+index).val(response && response[0] ? response[0].id : null).trigger("change");
                        }

                        document.getElementById("loading").classList.add("d-none");
                    },
                    error: function (jqXHR) {
                        console.error(jqXHR);

                        document.getElementById("loading").classList.add("d-none");
                    }
                });
            }

            // -Get Multi Item Piping-
            function getMultiItemPiping(index) {
                document.getElementById("loading").classList.remove("d-none");

                let id  = document.getElementById("id_rolls_"+index).value;
                let idForm  = document.getElementById("id_form_rolls_"+index).value;

                let totalRollQty = document.getElementById("total_qty");
                let totalRollUnit = document.getElementById("total_unit");

                $.ajax({
                    url: "{{ route("item-piping-piping") }}/"+id+"/"+idForm,
                    type: "get",
                    // dataType: "json",
                    success: async function (response) {
                        console.log("piping", response, typeof response === 'object', response && typeof response === 'object');

                        if (response && typeof response === 'object') {
                            document.getElementById("no_form_rolls_"+index).value = response.no_form_cut_input;
                            document.getElementById("id_item_rolls_"+index).value = response.id_item;
                            document.getElementById("color_rolls_"+index).value = response.color_act;
                            document.getElementById("group_rolls_"+index).value = response.group_roll;
                            document.getElementById("lot_rolls_"+index).value = response.lot;
                            document.getElementById("no_rolls_"+index).value = response.no_roll;
                            document.getElementById("qty_rolls_"+index).value = response.piping;
                            document.getElementById("unit_rolls_"+index).value = response.unit;
                        } else {
                            document.getElementById("no_form_rolls_"+index).value = "";
                            document.getElementById("id_item_rolls_"+index).value = "";
                            document.getElementById("color_rolls_"+index).value = "";
                            document.getElementById("group_rolls_"+index).value = "";
                            document.getElementById("lot_rolls_"+index).value = "";
                            document.getElementById("no_rolls_"+index).value = "";
                            document.getElementById("qty_rolls_"+index).value = "";
                            document.getElementById("unit_rolls_"+index).value = "";
                        }

                        console.log("check multi item", checkMultiItem(index));

                        if (!checkMultiItem(index)) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: 'Unit Roll tidak sesuai.',
                                showCancelButton: false,
                                showConfirmButton: true,
                                confirmButtonText: 'Oke',
                            });

                            clearMultiItem(index);
                        }

                        calculateMultiItem();

                        document.getElementById("loading").classList.add("d-none");
                    },
                    error: function (jqXHR) {
                        console.error(jqXHR);

                        document.getElementById("loading").classList.add("d-none");
                    }
                });
            }

            function clearMultiItem(index) {
                document.getElementById("id_rolls_"+index).value = "";
                document.getElementById("id_item_rolls_"+index).value = "";
                document.getElementById("id_form_rolls_"+index).value = "";
                document.getElementById("id_form_rolls_"+index).innerHTML = "";
                document.getElementById("no_form_rolls_"+index).value = "";
                document.getElementById("color_rolls_"+index).value = "";
                document.getElementById("group_rolls_"+index).value = "";
                document.getElementById("lot_rolls_"+index).value = "";
                document.getElementById("no_rolls_"+index).value = "";
                document.getElementById("qty_rolls_"+index).value = "";
                document.getElementById("unit_rolls_"+index).value = "";
            }

            // Submit Process Two
            function processTwo(e, event) {
                document.getElementById("loading").classList.remove("d-none");

                event.preventDefault();

                let form = new FormData(e);

                let dataObj = {
                    "process": 2,
                    "id": document.getElementById("id").value,
                    "method": document.getElementById("switch-method").checked ? "single" : "multi"
                }

                form.forEach((value, key) => dataObj[key] = value);

                $.ajax({
                    url: "{{ route("store-piping-process") }}",
                    type: "post",
                    data: dataObj,
                    dataType: "json",
                    success: function (response) {
                        if (response.status == 200) {
                            if (response.additional) {
                                setProcessThree(response.additional);
                            }

                            initProcessThree();
                        }

                        document.getElementById("loading").classList.add("d-none");
                    },
                    error: function (jqXHR) {
                        console.error(jqXHR);

                        document.getElementById("loading").classList.add("d-none");
                    }
                });
            }
        // END OF PROCESS TWO

        // PROCESS THREE
            function initProcessThree() {
                clearScan();

                document.getElementById("piping-process-calculate").classList.remove("d-none");

                // Process Header
                let headerForm = document.getElementById("piping-process-header");

                for (let i = 0; i < headerForm.length; i++) {
                    headerForm[i].setAttribute("disabled", "true");
                }

                // Process Scan
                let scanForm = document.getElementById("piping-process-scan");

                for (let i = 0; i < scanForm.length; i++) {
                    scanForm[i].setAttribute("disabled", "true");
                }
            }

            function setProcessThree(data) {
                document.getElementById("piping-process-calculate").querySelector("#arah_potong").value = data ? data.arah_potong : "";
                document.getElementById("piping-process-calculate").querySelector("#group").value = data ? data.group : "";
                document.getElementById("piping-process-calculate").querySelector("#id_item").value = data ? data.id_item : "";
                document.getElementById("piping-process-calculate").querySelector("#lot").value = data ? data.lot : "";
                document.getElementById("piping-process-calculate").querySelector("#no_roll").value = data ? data.no_roll : "";
                document.getElementById("piping-process-calculate").querySelector("#qty_awal").value = data ? data.qty_awal : "";
                document.getElementById("piping-process-calculate").querySelector("#qty_awal_unit").value = data ? data.unit : "";
                document.getElementById("piping-process-calculate").querySelector("#qty").value = data ? data.qty : "";
                document.getElementById("piping-process-calculate").querySelector("#qty_unit").value = data ? data.unit : "";
                document.getElementById("piping-process-calculate").querySelector("#qty_konversi").value = data ? data.qty_konversi : "";
                document.getElementById("piping-process-calculate").querySelector("#qty_konversi_unit").value = data ? data.unit : "";
                document.getElementById("piping-process-calculate").querySelector("#panjang_roll_piping").value = data ? data.panjang_roll_piping : "";
                document.getElementById("piping-process-calculate").querySelector("#panjang_roll_piping_unit").value = data ? data.panjang_roll_piping_unit : "";
                document.getElementById("piping-process-calculate").querySelector("#lebar_kain_act").value = data ? data.lebar_kain_act : "";
                document.getElementById("piping-process-calculate").querySelector("#lebar_kain_act_unit").value = data ? data.lebar_kain_act_unit : "";
                document.getElementById("piping-process-calculate").querySelector("#lebar_kain_cuttable").value = data ? data.lebar_kain_cuttable : "";
                document.getElementById("piping-process-calculate").querySelector("#lebar_kain_cuttable_unit").value = data ? data.lebar_kain_cuttable_unit : "";
                document.getElementById("piping-process-calculate").querySelector("#lebar_roll_piping").value = data ? data.lebar_roll_piping : "";
                document.getElementById("piping-process-calculate").querySelector("#lebar_roll_piping_unit").value = data ? data.lebar_roll_piping_unit : "";
                document.getElementById("piping-process-calculate").querySelector("#output_total_roll").value = data ? data.output_total_roll : "";
                document.getElementById("piping-process-calculate").querySelector("#output_total_roll_unit").value = "ROLL";
                document.getElementById("piping-process-calculate").querySelector("#jenis_potong_piping").value = data ? data.jenis_potong_piping : "";
                document.getElementById("piping-process-calculate").querySelector("#estimasi_output_roll").value = data ? data.estimasi_output_roll : "";
                document.getElementById("piping-process-calculate").querySelector("#estimasi_output_roll_unit").value = "PCS";
                document.getElementById("piping-process-calculate").querySelector("#estimasi_output_total").value = data ? data.estimasi_output_total : "";
                document.getElementById("piping-process-calculate").querySelector("#estimasi_output_total_unit").value = "PCS";
            }

            async function calculate() {
                let arahPotongRoll = document.getElementById("arah_potong");

                let qty = document.getElementById("qty");
                let qtyKonversi = document.getElementById("qty_konversi");
                let panjangRollPiping = document.getElementById("panjang_roll_piping");
                let lebarKain = document.getElementById("lebar_kain_act");
                let lebarCuttable = document.getElementById("lebar_kain_cuttable");
                let lebarRollPiping = document.getElementById("lebar_roll_piping");
                let outputTotalRoll = document.getElementById("output_total_roll");
                let estOutputRoll = document.getElementById("estimasi_output_roll");
                let estOutputTotalRoll = document.getElementById("estimasi_output_total");

                let jenisPotongPiping = document.getElementById("jenis_potong_piping");

                // Qty Konversi
                let qtyValue = qty.value ? qty.value : 0;

                qtyKonversi.value = qtyValue;

                // Calculate Output Total Roll
                let lebarCuttableValue = lebarCuttable.value ? Number(lebarCuttable.value) : 0;
                let lebarRollPipingValue = lebarRollPiping.value ? Number(lebarRollPiping.value) : 0;

                outputTotalRoll.value = Math.floor((lebarCuttableValue*100)/(lebarRollPipingValue ? lebarRollPipingValue : 1));

                // Calculate Est. Output Roll
                let qtyKonversiValue = qtyKonversi.value ? Number(qtyKonversi.value) : 0;
                let lebarKainValue = lebarKain.value ? Number(lebarKain.value) : 0;
                let panjangRollPipingValue = panjangRollPiping.value ? Number(panjangRollPiping.value) : 0;

                if (jenisPotongPiping.value == "straight") {
                    if (arahPotongRoll.value == "horizontal") {
                        estOutputRoll.value = Math.floor(((qtyKonversiValue*lebarKainValue)*100)/(panjangRollPipingValue ? panjangRollPipingValue : 1))
                    } else {
                        estOutputRoll.value = Math.floor((qtyKonversiValue*100)/(panjangRollPipingValue ? panjangRollPipingValue : 1))
                    }
                } else if (jenisPotongPiping.value == "bias") {
                    estOutputRoll.value = Math.floor(((Math.sqrt(((qtyKonversiValue/2)*(qtyKonversiValue/2))+(lebarCuttableValue*lebarCuttableValue))*2)*100)/(panjangRollPipingValue ? panjangRollPipingValue : 1));
                }

                // Est. Output Total Roll
                if (estOutputRoll.value && outputTotalRoll.value) {
                    let estOutputRollValue = estOutputRoll.value ? Number(estOutputRoll.value) : 0;
                    let outputTotalRollValue = outputTotalRoll.value ? Number(outputTotalRoll.value) : 0;

                    estOutputTotalRoll.value = Math.floor(estOutputRollValue*outputTotalRollValue);
                }
            }

            // Submit Process Three
            function processThree(e, event) {
                document.getElementById("loading").classList.remove("d-none");

                event.preventDefault();

                let form = new FormData(e);

                let dataObj = {
                    "process": 3,
                    "id": document.getElementById("id").value
                }

                form.forEach((value, key) => dataObj[key] = value);

                $.ajax({
                    url: "{{ route("store-piping-process") }}",
                    type: "post",
                    data: dataObj,
                    dataType: "json",
                    success: function (response) {
                        if (response.status == 200) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: 'Data hitung Piping berhasil disimpan.',
                                showCancelButton: false,
                                showConfirmButton: true,
                                confirmButtonText: 'Oke',
                            });

                            initFinish();

                            if (response.additional) {
                                setFinish(response.additional);
                            }
                        }

                        document.getElementById("loading").classList.add("d-none");
                    },
                    error: function (jqXHR) {
                        console.error(jqXHR);

                        document.getElementById("loading").classList.add("d-none");
                    }
                });
            }
        // END PROCES THREE

        // FINISH
            function initFinish() {
                clearScan();

                document.getElementById("piping-process-finish").classList.remove("d-none");

                // Process Header
                let headerForm = document.getElementById("piping-process-header");

                for (let i = 0; i < headerForm.length; i++) {
                    headerForm[i].setAttribute("disabled", "true");
                }

                // Process Scan
                let scanForm = document.getElementById("piping-process-scan");

                for (let i = 0; i < scanForm.length; i++) {
                    scanForm[i].setAttribute("disabled", "true");
                }

                // Process Calculate
                let calculateForm = document.getElementById("piping-process-calculate");

                for (let i = 0; i < calculateForm.length; i++) {
                    calculateForm[i].setAttribute("disabled", "true");
                }
            }

            function setFinish(data) {
                if (data && data.updated_at) {
                    document.getElementById("last-update").innerText = formatDateTime(data.updated_at);
                }
            }
        // END OF THE LINE


        // Reset Step
        async function resetStep() {
            await $("#buyer_id").val(null).trigger("change");
            await $("#act_costing_id").val(null).trigger("change");
            await $("#color").val(null).trigger("change");
            await $("#color").prop("disabled", true);
            await $("#master_piping_id").val(null).trigger("change");
            await $("#master_piping_id").prop("disabled", true);
        }
    </script>
@endsection
