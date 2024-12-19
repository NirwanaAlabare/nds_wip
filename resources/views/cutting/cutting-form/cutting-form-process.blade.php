@extends('layouts.index')

@section('custom-link')
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@endsection

@section('content')
    <div class="row g-3 mb-3">
        <div class="d-flex gap-3 align-items-center">
            <h5 class="mb-1">Form Cut - {{ strtoupper($formCutInputData->name) }}</h5>
            <button class="btn btn-sm btn-success" id="start-process" onclick="startProcess()">Mulai Pengerjaan</button>
        </div>
        <div class="col-md-6">
            <div class="card card-sb" id="header-data-card">
                <div class="card-header">
                    <h3 class="card-title">Header Data</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body" style="display: block;">
                    @php
                        $thisActCosting = $actCostingData->where('id', $formCutInputData->act_costing_id)->first();
                        $thisMarkerDetails = $markerDetailData->where('kode_marker', $formCutInputData->id_marker);
                    @endphp
                    <div class="row align-items-end">
                        <input type="hidden" name="id" id="id" value="{{ $id }}" readonly>
                        <input type="hidden" name="act_costing_id" id="act_costing_id" value="{{ $formCutInputData->act_costing_id }}" readonly>
                        <input type="hidden" name="status" id="status" value="{{ $formCutInputData->status }}" readonly>
                        <input type="hidden" name="locked" id="locked" value="{{ $formCutInputData->locked }}" readonly>
                        <input type="hidden" name="unlocked_by" id="unlocked_by" value="{{ $formCutInputData->unlocked_by }}" readonly>
                        <input type="hidden" name="no_meja" id="no_meja" value="{{ $formCutInputData->no_meja }}" readonly>
                        <div class="col-6 col-md-4">
                            <div class="mb-3">
                                <label class="form-label"><small><b>Start</b></small></label>
                                <input type="text" class="form-control form-control-sm" name="start" id="start-time" value="{{ $formCutInputData->waktu_mulai }}" readonly>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="mb-3">
                                <label class="form-label"><small><b>Finish</b></small></label>
                                <input type="text" class="form-control form-control-sm" name="finish" id="finish-time" value="{{ $formCutInputData->waktu_selesai }}" readonly>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="mb-3">
                                <label class="form-label"><small><b>Shell</b></small></label>
                                <select class="form-select form-select-sm" name="shell" id="shell">
                                    <option value="a" {{ $formCutInputData->shell == 'a' ? 'selected' : '' }}>A</option>
                                    <option value="b" {{ $formCutInputData->shell == 'b' ? 'selected' : '' }}>B</option>
                                    <option value="c" {{ $formCutInputData->shell == 'c' ? 'selected' : '' }}>C</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-6 col-md-6">
                            <div class="mb-3">
                                <label class="form-label label-fetch"><small><b>No. Form</b></small></label>
                                <input type="text" class="form-control form-control-sm border-fetch" name="no_form" id="no_form" value="{{ $formCutInputData->no_form }}" readonly>
                            </div>
                        </div>
                        <div class="col-6 col-md-6">
                            <div class="mb-3">
                                <label class="form-label"><small><b>Tanggal</b></small></label>
                                <input type="date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" readonly>
                            </div>
                        </div>
                        <div class="col-6 col-md-6">
                            <div class="mb-3">
                                <label class="form-label label-fetch"><small><b>Kode Marker</b></small></label>
                                <input type="text" class="form-control form-control-sm border-fetch" value="{{ $formCutInputData->id_marker }}" readonly>
                            </div>
                        </div>
                        <div class="col-6 col-md-6">
                            <div class="mb-3">
                                <label class="form-label label-fetch"><small><b>No. WS</b></small></label>
                                <input type="text" class="form-control form-control-sm border-fetch" name="no_ws" value="{{ $formCutInputData->act_costing_ws }}" readonly>
                            </div>
                        </div>
                        <div class="col-6 col-md-6">
                            <div class="mb-3">
                                <label class="form-label label-fetch"><small><b>Buyer</b></small></label>
                                <input type="text" class="form-control form-control-sm border-fetch" name="buyer" value="{{ $thisActCosting->buyer }}" readonly>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="form-label label-fetch"><small><b>Style</b></small></label>
                                <input type="text" class="form-control form-control-sm border-fetch" name="style" value="{{ $thisActCosting->style }}" readonly>
                            </div>
                        </div>
                        <div class="col-4 col-md-4">
                            <div class="mb-3">
                                <label class="form-label label-fetch"><small><b>Color</b></small></label>
                                <input type="text" class="form-control form-control-sm border-fetch" name="color" id="color" value="{{ $formCutInputData->color }}" readonly>
                            </div>
                        </div>
                        <div class="col-4 col-md-4">
                            <div class="mb-3">
                                <label class="form-label label-fetch"><small><b>Panel</b></small></label>
                                <input type="text" class="form-control form-control-sm border-fetch" name="panel" id="panel" value="{{ $formCutInputData->panel }}" readonly>
                            </div>
                        </div>
                        <div class="col-4 col-md-4">
                            <div class="mb-3">
                                <label class="form-label label-fetch"><small><b>Tipe Marker</b></small></label>
                                <input type="text" class="form-control form-control-sm border-fetch" name="tipe_marker" id="tipe_marker" value="{{ $formCutInputData->tipe_marker ? strtoupper($formCutInputData->tipe_marker) : '-' }}" readonly>
                            </div>
                        </div>
                        <div class="col-4 col-md-4">
                            <div class="mb-3">
                                <label class="form-label label-fetch"><small><b>PO</b></small></label>
                                <input type="text" class="form-control form-control-sm border-fetch" name="po" value="{{ $formCutInputData->po_marker }}" readonly>
                            </div>
                        </div>
                        <div class="col-4 col-md-4">
                            <div class="mb-3">
                                <label class="form-label label-fetch"><small><b>QTY Gelar Marker</b></small></label>
                                <input type="text" class="form-control form-control-sm border-fetch" name="gelar_qty" value="{{ $formCutInputData->gelar_qty }}" readonly>
                            </div>
                        </div>
                        <div class="col-4 col-md-4">
                            <div class="mb-3">
                                <label class="form-label label-fetch"><small><b>QTY Cut Ply</b></small></label>
                                <input type="text" class="form-control form-control-sm border-fetch" id="qty_ply" name="qty_ply" value="{{ $formCutInputData->qty_ply }}" readonly>
                            </div>
                        </div>
                    </div>
                    <table id="ratio-datatable"
                        class="table table-striped table-bordered table-sm w-100 text-center mt-3">
                        <thead>
                            <tr>
                                <th class="label-fetch">Size</th>
                                <th class="label-fetch">Ratio</th>
                                <th class="label-fetch">Qty Cut Marker</th>
                                <th class="label-fetch">Qty Output</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $totalRatio = 0;
                                $totalCutQty = 0;
                                $totalCutQtyPly = 0;
                            @endphp
                            @foreach ($thisMarkerDetails as $item)
                                <tr>
                                    @php
                                        $totalRatio += $item->ratio;
                                        $totalCutQty += $item->cut_qty;
                                        $qtyPly = $item->ratio * $formCutInputData->qty_ply;
                                        $totalCutQtyPly += $qtyPly;
                                    @endphp
                                    <td>{{ $item->size }}</td>
                                    <td>{{ $item->ratio }}</td>
                                    <td>{{ $item->cut_qty }}</td>
                                    <td>{{ $qtyPly }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th class="text-center">Total</th>
                                <th id="totalRatio">{{ $totalRatio }}</th>
                                <th id="totalQtyCutMarker">{{ $totalCutQty }}</th>
                                <th id="totalQtyCutPly">{{ $totalCutQtyPly }}</th>
                            </tr>
                        </tfoot>
                    </table>
                    <input type="hidden" name="total_ratio" id="total_ratio" value="{{ $totalRatio }}">
                    <input type="hidden" name="total_qty_cut" id="total_qty_cut" value="{{ $totalCutQty }}">
                    <input type="hidden" name="total_qty_cut_ply" id="total_qty_cut_ply" value="{{ $totalCutQtyPly }}">
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <button class="btn btn-sb mb-3 d-none" id="next-process-1" onclick="nextProcessOne()">NEXT</button>
            <div class="card card-sb d-none" id="detail-data-card">
                <div class="card-header">
                    <h3 class="card-title">Detail Data</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                    </div>
                </div>
                <div class="card-body" style="display: block;">
                    <div class="row">
                        <div class="col-6 col-md-3">
                            <div class="mb-3">
                                <label class="form-label label-fetch"><small><b>P. Marker</b></small></label>
                                <input type="number" class="form-control form-control-sm border-fetch" value="{{ $formCutInputData->panjang_marker }}" readonly>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="mb-3">
                                <label class="form-label label-fetch"><small><b>Unit</b></small></label>
                                <input type="text" class="form-control form-control-sm border-fetch" value="{{ strtoupper($formCutInputData->unit_panjang_marker) }}" readonly>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="mb-3">
                                <label class="form-label label-input"><small><b>P. Act</b></small></label>
                                <input type="number" class="form-control form-control-sm border-input" name="p_act" id="p_act" value="{{ $formCutInputData->p_act }}"
                                    onkeyup="
                                        calculateConsAct();
                                        calculateConsAmpar();
                                        calculateEstAmpar();
                                        calculateShortRoll(event);
                                        calculatePemakaianLembar();
                                        calculateTotalPemakaian();
                                        // calculateSisaKain();
                                    "
                                    onchange="
                                        calculateConsAct();
                                        calculateConsAmpar();
                                        calculateEstAmpar();
                                        calculateShortRoll(event);
                                        calculatePemakaianLembar();
                                        calculateTotalPemakaian();
                                        // calculateSisaKain();
                                    ">
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="mb-3">
                                <label class="form-label label-input"><small><b>Unit Act</b></small></label>
                                <input type="text" class="form-control form-control-sm border-input" name="unit_p_act" id="unit_p_act" value="{{ strtoupper($formCutInputData->unit_panjang_marker) }}" readonly>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="mb-3">
                                <label class="form-label label-fetch"><small><b>Comma</b></small></label>
                                <input type="number" class="form-control form-control-sm border-fetch" value="{{ $formCutInputData->comma_marker }}" readonly>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="mb-3">
                                <label class="form-label label-fetch"><small><b>Unit</b></small></label>
                                <input type="text" class="form-control form-control-sm border-fetch"value="{{ strtoupper($formCutInputData->unit_comma_marker) }}" readonly>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="mb-3">
                                <label class="form-label label-input"><small><b>Comma Act</b></small></label>
                                <input type="number" class="form-control form-control-sm border-input" name="comma_act" id="comma_act" value="{{ $formCutInputData->comma_p_act }}"
                                    onkeyup="
                                        calculateConsAct();
                                        calculateConsAmpar();
                                        calculateEstAmpar();
                                        calculateShortRoll(event);
                                        calculatePemakaianLembar();
                                        calculateTotalPemakaian();
                                        // calculateSisaKain();
                                    "
                                    onchange="
                                        calculateConsAct();
                                        calculateConsAmpar();
                                        calculateEstAmpar();
                                        calculateShortRoll(event);
                                        calculatePemakaianLembar();
                                        calculateTotalPemakaian();
                                        // calculateSisaKain();
                                    ">
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="mb-3">
                                <label class="form-label label-input"><small><b>Unit Act</b></small></label>
                                <input type="text" class="form-control form-control-sm border-input" name="unit_comma_act" id="unit_comma_act" value="{{ strtoupper($formCutInputData->unit_comma_marker) }}" readonly>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="mb-3">
                                <label class="form-label label-fetch"><small><b>L. Marker</b></small></label>
                                <input type="number" class="form-control form-control-sm border-fetch" value="{{ strtoupper($formCutInputData->lebar_marker) }}" readonly>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="mb-3">
                                <label class="form-label label-fetch"><small><b>Unit</b></small></label>
                                <input type="text" class="form-control form-control-sm border-fetch" value="{{ strtoupper($formCutInputData->unit_lebar_marker) }}" readonly>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="mb-3">
                                <label class="form-label label-input"><small><b>L. Act</b></small></label>
                                <input type="number" class="form-control form-control-sm border-input" name="l_act" id="l_act" value="{{ $formCutInputData->l_act }}"
                                    onkeyup="
                                        calculateConsAmpar();
                                        calculateEstAmpar();
                                        calculateShortRoll(event);
                                        calculatePemakaianLembar();
                                        calculateTotalPemakaian();
                                        // calculateSisaKain();
                                    "
                                    onchange="
                                        calculateConsAmpar();
                                        calculateEstAmpar();
                                        calculateShortRoll(event);
                                        calculatePemakaianLembar();
                                        calculateTotalPemakaian();
                                        // calculateSisaKain();
                                    ">
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="mb-3">
                                <label class="form-label label-input"><small><b>Unit Act</b></small></label>
                                <input type="text" class="form-control form-control-sm border-input" name="unit_l_act" id="unit_l_act" value="{{ strtoupper($formCutInputData->unit_lebar_marker) }}" readonly>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="mb-3">
                                <label class="form-label label-fetch"><small><b>Gramasi</b></small></label>
                                <input type="text" class="form-control form-control-sm border-fetch" name="gramasi" id="gramasi" value="{{ $formCutInputData->gramasi }}"
                                    onkeyup="calculateEstAmpar(undefined, undefined, undefined, this.value);"
                                    onchange="calculateEstAmpar(undefined, undefined, undefined, this.value);" readonly>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="mb-3">
                                <label class="form-label label-fetch"><small><b>Cons WS</b></small></label>
                                <input type="text" class="form-control form-control-sm border-fetch" name="cons_ws" id="cons_ws" readonly>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="mb-3">
                                <label class="form-label label-fetch"><small><b>Cons Marker</b></small></label>
                                <input type="text" class="form-control form-control-sm border-fetch" name="cons_marker" id="cons_marker" value="{{ $formCutInputData->cons_marker }}" readonly
                                    onkeyup="calculateEstKain(this.value)"
                                    onchange="calculateEstKain(this.value)"
                                >
                            </div>
                        </div>
                        <div class="col-6 col-md-6">
                            <div class="mb-3">
                                <label class="form-label label-calc"><small><b>Cons. Ampar</b></small></label>
                                <input type="number" class="form-control form-control-sm border-calc" name="cons_act" id="cons_act" value="{{ round($formCutInputData->cons_ampar, 3) > 0 ? round($formCutInputData->cons_ampar, 3) : '0'}}" step=".01" readonly>
                            </div>
                        </div>
                        <div class="col-6 col-md-6">
                            <div class="mb-3">
                                <label class="form-label label-fetch"><small><b>Cons Piping</b></small></label>
                                <input type="number" class="form-control form-control-sm border-fetch" step=".01" name="cons_pipping" id="cons_pipping" value="{{ $formCutInputData->cons_pipping ? $formCutInputData->cons_pipping : '0' }}" readonly
                                    onkeyup="calculateEstPipping(this.value)"
                                    onchange="calculateEstPipping(this.value)"
                                >
                            </div>
                        </div>
                        <div class="col-6 col-md-6 d-none">
                            <div class="mb-3">
                                <label class="form-label label-calc"><small><b>Cons 1 Ampar</b></small></label>
                                <div class="row">
                                    <div class="col-8">
                                        <input type="number" class="form-control form-control-sm border-calc" step=".01" name="cons_ampar" id="cons_ampar" value="{{ $formCutInputData->cons_ampar }}" readonly>
                                    </div>
                                    <div class="col-4">
                                        <input type="text" class="form-control form-control-sm border-calc" name="unit_cons_ampar" id="unit_cons_ampar" value="KGM" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label label-calc"><small><b>Est. Kebutuhan Kain Piping</b></small></label>
                                <div class="row g-1">
                                    <div class="col-6">
                                        <input type="number" class="form-control form-control-sm border-calc" step=".01" name="est_pipping" id="est_pipping" value="{{ $formCutInputData->est_pipping ? $formCutInputData->est_pipping : '0.00' }}" readonly>
                                    </div>
                                    <div class="col-6">
                                        <input type="text" class="form-control form-control-sm border-calc" name="est_pipping_unit" id="est_pipping_unit" value="{{ strtoupper($formCutInputData->unit_panjang_marker) }}" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label label-calc"><small><b>Est. Kebutuhan Kain</b></small></label>
                                <div class="row g-1">
                                    <div class="col-6">
                                        <input type="number" class="form-control form-control-sm border-calc" step=".01" name="est_kain" id="est_kain" value="{{ $formCutInputData->cons_marker * $totalCutQtyPly }}" readonly>
                                    </div>
                                    <div class="col-6">
                                        <input type="text" class="form-control form-control-sm border-calc" name="est_kain_unit" id="est_kain_unit" value="{{ strtoupper($formCutInputData->unit_panjang_marker) }}" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <button class="btn btn-sb mb-3 float-end d-none" id="next-process-2" onclick="nextProcessTwo()">NEXT</button>
            <div class="card card-sb d-none" id="scan-qr-card">
                <div class="card-header">
                    <h3 class="card-title">Scan QR</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                    </div>
                </div>
                <div class="card-body" style="display: block;">
                    <div class="row justify-content-center align-items-end">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <div id="reader"></div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="d-flex justify-content-center mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="switch-method" checked onchange="switchMethod(this)">
                                    <label class="form-check-label" id="to-scan">Scan Roll</label>
                                    <label class="form-check-label d-none" id="to-item">Pilih Barang</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12" id="scan-method">
                            <div class="row align-items-end">
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label class="form-label label-input"><small><b>ID Roll</b></small></label>
                                        <div class="input-group">
                                            <input type="text" class="form-control form-control-sm border-input" name="kode_barang" id="kode_barang">
                                            <button class="btn btn-sm btn-success" type="button" id="get-button" onclick="fetchScan()">Get</button>
                                            <button class="btn btn-sm btn-primary" type="button" id="scan-button" onclick="refreshScan()">Scan</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label label-scan"><small><b>ID Item</b></small></label>
                                        <input type="text" class="form-control form-control-sm border-scan" name="id_item" id="id_item" readonly>
                                    </div>
                                </div>
                                <div class="col-6 col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label label-scan"><small><b>Detail Item</b></small></label>
                                        <input type="text" class="form-control form-control-sm border-scan" name="detail_item" id="detail_item" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label label-input"><small><b>Color Act</b></small></label>
                                        <input type="text" class="form-control form-control-sm border-input" name="color_act" id="color_act">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mt-auto mb-3">
                                        <button class="btn btn-sb btn-sm btn-block d-none" id="next-process-3" onclick="nextProcessThree()">START</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12 d-none" id="item-method">
                            <div class="row align-items-end">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label label-input"><small><b>Pilih Barang</b></small></label>
                                        <select class="form-select form-control-sm select2bs4" name="select_item" id="select_item" onchange="setSelectedItem(this)">
                                            <option value="">Pilih Barang</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mt-auto mb-3">
                                        <button class="btn btn-sb btn-sm btn-block" id="next-process-3-item" onclick="nextProcessThree()">START</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="card card-sb d-none" id="spreading-form-card">
                <div class="card-header">
                    <h3 class="card-title">Spreading</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                    </div>
                </div>
                <div class="card-body" style="display: block;">
                    <form action="#" method="post" id="spreading-form">
                        <input type="hidden" id="id_sambungan" name="id_sambungan" readonly>
                        <input type="hidden" id="status_sambungan" name="status_sambungan" readonly>
                        <input type="hidden" id="current_id_roll" name="current_id_roll" readonly>
                        <input type="hidden" id="method" name="method" readonly>
                        <div class="row">
                            <div class="col-3">
                                <div class="mb-3">
                                    <label class="form-label label-input"><small><b>Group</b></small></label>
                                    <input type="text" class="form-control form-control-sm border-input" id="current_group" name="current_group">
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="mb-3">
                                    <label class="form-label label-scan" id="current_id_item_label"><small><b>Id Item</b></small></label>
                                    <input type="text" class="form-control form-control-sm border-scan" id="current_id_item" name="current_id_item" readonly>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="mb-3">
                                    <label class="form-label label-scan" id="current_lot_label"><small><b>Lot</b></small></label>
                                    <input type="text" class="form-control form-control-sm border-scan" id="current_lot" name="current_lot" readonly>
                                </div>
                            </div>
                            <div class="col-3 d-none">
                                <div class="mb-3">
                                    <label class="form-label label-scan" id="current_roll_label"><small><b>Roll</b></small></label>
                                    <input type="text" class="form-control form-control-sm border-scan" id="current_roll" name="current_roll" readonly>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="mb-3">
                                    <label class="form-label label-scan" id="current_roll_buyer_label"><small><b>Roll Buyer</b></small></label>
                                    <input type="text" class="form-control form-control-sm border-scan" id="current_roll_buyer" name="current_roll_buyer" readonly>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="d-flex gap-3">
                                    <div>
                                        <div class="mb-3">
                                            <label class="form-label label-scan" id="current_qty_awal_label"><small><b>Qty Awal</b></small></label>
                                            <div class="d-flex mb-3">
                                                <div style="width: 60%">
                                                    <input type="number" class="form-control form-control-sm border-scan" id="current_qty_awal" name="current_qty_awal" readonly>
                                                </div>
                                                <div style="width: 40%">
                                                    <input type="text" class="form-control form-control-sm border-scan" id="current_unit_awal" name="current_unit_awal" value="METER" readonly>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="form-label label-scan" id="current_qty_real_label"><small><b>Qty</b></small></label>
                                        <div class="d-flex mb-3">
                                            <div style="width: 60%">
                                                <input type="number" class="form-control form-control-sm border-scan" id="current_qty_real" name="current_qty_real" readonly
                                                onchange="setRollQtyConversion(this.value); calculateEstAmpar();"
                                                onkeyup="setRollQtyConversion(this.value); calculateEstAmpar();">
                                            </div>
                                            <div style="width: 40%">
                                                <input type="text" class="form-control form-control-sm border-scan" id="current_unit" name="current_unit" readonly>
                                                <select class="form-select form-select-sm d-none rounded-0" name="current_custom_unit" id="current_custom_unit" onchange="setCustomUnit(this.value); setRollQtyConversion()">
                                                    <option value="METER">METER</option>
                                                    <option value="KGM">KGM</option>
                                                    <option value="YARD">YARD</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="mb-3">
                                            <label class="form-label label-calc" id="current_qty_label"><small><b>Qty Konversi</b></small></label>
                                            <div class="d-flex mb-3">
                                                <div style="width: 60%">
                                                    <input type="number" class="form-control form-control-sm border-calc" id="current_qty" name="current_qty" readonly>
                                                </div>
                                                <div style="width: 40%">
                                                    <input type="text" class="form-control form-control-sm border-calc" id="current_unit_convert" name="current_unit_convert" value="METER" readonly>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="form-label label-input"><small><b>Sisa Gelaran</b></small></label>
                                    <div class="d-flex mb-3">
                                        <div style="width: 60%;">
                                            <input type="number" class="form-control form-control-sm border-input" id="current_sisa_gelaran" name="current_sisa_gelaran" step=".01"
                                                onkeyup="
                                                    // restrictRemainPly();
                                                    calculateShortRoll(event);
                                                    calculatePemakaianLembar();
                                                    calculateTotalPemakaian();
                                                "
                                                onchange="
                                                    // restrictRemainPly();
                                                    calculateShortRoll(event);
                                                    calculatePemakaianLembar();
                                                    calculateTotalPemakaian();
                                                ">
                                        </div>
                                        <div style="width: 40%;">
                                            <input type="text" class="form-control form-control-sm border-input" id="current_sisa_gelaran_unit" name="current_sisa_gelaran_unit" step=".01" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="form-label label-input"><small><b>Sambungan</b></small></label>
                                    <div class="d-flex">
                                        <div style="width: 60%">
                                            <input type="number" class="form-control form-control-sm border-input"
                                                id="current_sambungan" name="current_sambungan" step=".01"
                                                onkeyup="
                                                    calculateShortRoll(event);
                                                    calculatePemakaianLembar();
                                                    calculateTotalPemakaian();
                                                    // calculateSisaKain();
                                                "
                                                onchange="
                                                    calculateShortRoll(event);
                                                    calculatePemakaianLembar();
                                                    calculateTotalPemakaian();
                                                    // calculateSisaKain();
                                                ">
                                        </div>
                                        <div style="width: 40%">
                                            <input type="text" class="form-control form-control-sm border-input"
                                                id="current_sambungan_unit" name="current_sambungan_unit" step=".01"
                                                readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="row">
                                    <div class="col d-none" id="berat_amparan">
                                        <div class="mb-3">
                                            <label class="form-label"><small><b>Berat 1 Ampar</b></small></label>
                                            <div class="input-group input-group-sm">
                                                <input type="number" class="form-control form-control-sm " id="current_berat_amparan" name="current_berat_amparan" step=".01"
                                                    onkeyup="
                                                        calculateSambungan();
                                                        calculateShortRoll(event);
                                                        calculateEstAmpar();
                                                        calculatePemakaianLembar();
                                                        calculateTotalPemakaian();
                                                        // calculateSisaKain();
                                                    "

                                                    onchange="
                                                        calculateSambungan();
                                                        calculateShortRoll(event);
                                                        calculateEstAmpar();
                                                        calculatePemakaianLembar();
                                                        calculateTotalPemakaian();
                                                        // calculateSisaKain();
                                                    "
                                                >
                                                <span class="input-group-text">KGM</span>
                                              </div>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="mb-3">
                                            <label class="form-label label-calc"><small><b>Estimasi Amparan</b></small></label>
                                            <input type="number" class="form-control form-control-sm border-calc" id="current_est_amparan" name="current_est_amparan" step=".01" readonly>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="mb-3">
                                            <label class="form-label label-sb"><small><b>Lembar Gelaran</b></small></label>
                                            <input type="number" class="form-control form-control-sm border-sb" id="current_lembar_gelaran" name="current_lembar_gelaran" readonly
                                                onkeyup="
                                                    calculateShortRoll(event);
                                                    calculatePemakaianLembar();
                                                    calculateTotalPemakaian();
                                                    // calculateSisaKain();
                                                "
                                                onchange="
                                                    calculateShortRoll(event);
                                                    calculatePemakaianLembar();
                                                    calculateTotalPemakaian();
                                                    // calculateSisaKain();
                                                ">
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="mb-3">
                                            <label class="form-label label-sb"><small><b>Average Time</b></small></label>
                                            <input type="text" class="form-control form-control-sm border-sb" id="current_average_time" name="current_average_time" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="form-label label-sb"><small><b>Ply Progress</b></small></label>
                                    <div class="progress border border-sb" style="height: 31px">
                                        <p class="position-absolute" style="top: 59%;left: 50%;transform: translate(-50%, -50%);" id="current_ply_progress_txt"></p>
                                        <div class="progress-bar" style="background-color: #75baeb;" role="progressbar" id="current_ply_progress"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="mb-3">
                                    <label class="form-label label-input"><small><b>Kepala Kain</b></small></label>
                                    <div class="input-group input-group-sm mb-3">
                                        <input type="number" class="form-control border-input" id="current_kepala_kain" name="current_kepala_kain" step=".01"
                                            onkeyup="
                                                calculatePemakaianLembar();
                                                calculateTotalPemakaian();
                                                calculateShortRoll(event);
                                                // calculateSisaKain();
                                            "
                                            onchange="
                                                calculatePemakaianLembar();
                                                calculateTotalPemakaian();
                                                calculateShortRoll(event);
                                                // calculateSisaKain();
                                            ">
                                        <span class="input-group-text input-group-unit"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="mb-3">
                                    <label class="form-label label-input"><small><b>Sisa Tidak Bisa</b></small></label>
                                    <div class="input-group input-group-sm mb-3">
                                        <input type="number" class="form-control border-input" id="current_sisa_tidak_bisa" name="current_sisa_tidak_bisa" step=".01"
                                            onkeyup="
                                                calculateShortRoll(event);
                                                calculatePemakaianLembar();
                                                calculateTotalPemakaian();
                                                // calculateSisaKain();
                                            "
                                            onchange="
                                                calculateShortRoll(event);
                                                calculatePemakaianLembar();
                                                calculateTotalPemakaian();
                                                // calculateSisaKain();
                                            ">
                                        <span class="input-group-text input-group-unit"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="mb-3">
                                    <label class="form-label label-input"><small><b>Reject</b></small></label>
                                    <div class="input-group input-group-sm mb-3">
                                        <input type="number" class="form-control form-control-sm border-input" id="current_reject" name="current_reject" step=".01"
                                            onkeyup="
                                                calculateShortRoll(event);
                                                calculatePemakaianLembar();
                                                calculateTotalPemakaian();
                                                // calculateSisaKain();
                                            "
                                            onchange="
                                                calculateShortRoll(event);
                                                calculatePemakaianLembar();
                                                calculateTotalPemakaian();
                                                // calculateSisaKain();
                                            ">
                                        <span class="input-group-text input-group-unit"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="mb-3">
                                    <label class="form-label label-input"><small><b>Piping</b></small></label>
                                    <div class="input-group input-group-sm mb-3">
                                        <input type="number" class="form-control form-control-sm border-input" id="current_piping" name="current_piping" step=".01"
                                            onkeyup="
                                                calculateShortRoll(event);
                                                calculatePemakaianLembar();
                                                calculateTotalPemakaian();
                                                // calculateSisaKain();
                                            "
                                            onchange="
                                                calculateShortRoll(event);
                                                calculatePemakaianLembar();
                                                calculateTotalPemakaian();
                                                // calculateSisaKain();
                                            ">
                                        <span class="input-group-text input-group-unit"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="mb-3">
                                    <label class="form-label label-calc"><small><b>Pemakaian Gelar</b></small></label>
                                    <div class="input-group input-group-sm mb-3">
                                        <input type="number" class="form-control form-control-sm border-calc" id="current_pemakaian_lembar" name="current_pemakaian_lembar" step=".01" readonly>
                                        <span class="input-group-text input-group-unit"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="mb-3">
                                    <label class="form-label label-calc"><small><b>Tot. Pakai /Roll</b></small></label>
                                    <div class="input-group input-group-sm mb-3">
                                        <input type="number" class="form-control form-control-sm border-calc" id="current_total_pemakaian_roll" name="current_total_pemakaian_roll" step=".01" readonly>
                                        <span class="input-group-text input-group-unit"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="row">
                                    <div class="col-md-7">
                                        <div class="mb-3">
                                            <label class="form-label label-calc"><small><b>Short Roll +/-</b></small></label>
                                            <div class="input-group input-group-sm mb-3">
                                                <input type="number" class="form-control form-control-sm border-calc" id="current_short_roll" name="current_short_roll" step=".01" readonly>
                                                <span class="input-group-text input-group-unit"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="mb-3">
                                            <label class="form-label label-calc"><small><b>Short Roll(%)</b></small></label>
                                            <input type="number" class="form-control form-control-sm border-calc" id="current_short_roll_percentage" name="current_short_roll_percentage" step=".01" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="mb-3">
                                    <label class="form-label label-input"><small><b>Sisa Kain</b></small></label>
                                    <div class="input-group input-group-sm mb-3">
                                        <input type="number" class="form-control form-control-sm border-input" id="current_sisa_kain" name="current_sisa_kain" step=".01"
                                            onkeyup="
                                                calculateShortRoll(event);
                                                calculatePemakaianLembar();
                                                calculateTotalPemakaian();
                                            "
                                            onchange="
                                                calculateShortRoll(event);
                                                calculatePemakaianLembar();
                                                calculateTotalPemakaian();
                                            "
                                        >
                                        <span class="input-group-text input-group-unit"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6" id="total-sambungan-section">
                                <div class="row align-items-end mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label label-input"><small><b>Total Sambungan Roll</b></small></label>
                                        <input type="number" class="form-control form-control-sm" id="current_total_sambungan_roll" name="current_total_sambungan_roll" readonly onchange="calculatePemakaianLembar();calculateTotalPemakaian();calculateShortRoll(event);/*calculateRemark();*/">
                                    </div>
                                    <div class="col-md-6">
                                        <button type="button" class="btn btn-primary btn-sm btn-block float-end fw-bold" onclick="addNewSambungan()"><i class="fa-regular fa-square-plus"></i> SAMBUNGAN</button>
                                        <input type="hidden" class="form-control form-control-sm" id="current_jumlah_sambungan" name="current_jumlah_sambungan">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6" id="sambungan-section">
                                <div class="row">
                                    <div class="col-12 mb-1">
                                        <label class="form-label label-input"><small><b>Sambungan Roll</b></small></label>
                                        <input type="number" class="form-control form-control-sm sambungan_roll" id="sambungan_roll_0" name="sambungan_roll[0]" onkeyup="sumNewSambungan()" onchange="sumNewSambungan()">
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-md-6 my-3">
                                <button type="button" class="fs-5 fw-bold btn btn-success btn-sm w-100 h-100" style="min-height: 90px !important;" id="startLapButton" onclick="startTimeRecord()">START</button>
                                <button type="button" class="fs-5 fw-bold btn btn-primary btn-sm d-none w-100 h-100" style="min-height: 90px !important;" id="nextLapButton" onclick="addNewTimeRecord()">NEXT LAP</button>
                            </div>
                            <div class="col-6 col-md-6 my-3">
                                <div class="row">
                                    <div class="col-5">
                                        <input type="text" class="form-control form-control-sm" id="minutes" value="00" readonly class="mx-1">
                                    </div>
                                    <div class="col-2">
                                        <center>:</center>
                                    </div>
                                    <div class="col-5">
                                        <input type="text" class="form-control form-control-sm" id="seconds" value="00" readonly class="mx-1">
                                    </div>
                                </div>
                                <div class="w-100 table-responsive mt-3" style="max-height: 150px; overflow-y: auto;">
                                    <table class="table table-bordered table-sm" id="timeRecordTable">
                                        <thead>
                                            <tr>
                                                <th>Lap</th>
                                                <th>Waktu</th>
                                                <th class="d-none"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <button type="button" class="btn btn-sb btn-sm btn-block my-3" id="stopLapButton" onclick="stopTimeRecord()">Simpan</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="card card-sb collapsed-card d-none" id="lost-time-card">
                <div class="card-header">
                    <h3 class="card-title">Loss Time</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                    </div>
                </div>
                <div class="card-body" style="display: block;">
                    <div class="row">
                        <div class="col-6 col-md-6 my-3">
                            <button type="button" class="fs-5 fw-bold btn btn-danger btn-sm w-100 h-100" style="min-height: 90px !important;" id="startLostButton" onclick="startLostTime()">START</button>
                            <button type="button" class="fs-5 fw-bold btn btn-warning btn-sm d-none w-100 h-100" style="min-height: 90px !important;" id="nextLostButton" onclick="addNewLostTime()">STOP</button>
                        </div>
                        <div class="col-6 col-md-6 my-3">
                            <div class="row">
                                <div class="col-5">
                                    <input type="text" class="form-control form-control-sm" id="lostMinutes" value="00" readonly class="mx-1">
                                </div>
                                <div class="col-2">
                                    <center>:</center>
                                </div>
                                <div class="col-5">
                                    <input type="text" class="form-control form-control-sm" id="lostSeconds" value="00" readonly class="mx-1">
                                </div>
                            </div>
                            <div class="w-100 h-100 table-responsive mt-3" style="max-height: 150px; overflow-y: auto;">
                                <form action="#" method="post" id="lost-time-form">
                                    <input type="hidden" id="current_lost_time" name="current_lost_time">
                                    <table class="table table-bordered table-sm" id="lostTimeTable">
                                        <thead>
                                            <tr>
                                                <th>No.</th>
                                                <th>Waktu</th>
                                                <th class="d-none"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="card card-sb d-none" id="summary-card">
                <div class="card-header">
                    <h3 class="card-title">Summary</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                    </div>
                </div>
                <div class="card-body" style="display: block;">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="w-100 table-responsive my-3">
                                <table class="table table-bordered table-sm" id="scannedItemTable">
                                    <thead>
                                        <tr>
                                            <th>No.</th>
                                            <th>Group</th>
                                            <th>Group Number</th>
                                            <th class="label-scan">ID Roll</th>
                                            <th class="label-scan">ID Item</th>
                                            <th class="label-scan">Lot</th>
                                            <th class="label-scan">Roll Buyer</th>
                                            <th class="label-scan">Qty Awal</th>
                                            <th class="label-scan">Qty</th>
                                            <th class="label-scan">Unit</th>
                                            <th>Sisa Gelaran</th>
                                            <th>Sambungan</th>
                                            <th>Sambungan Roll</th>
                                            <th class="label-calc">Estimasi Amparan</th>
                                            <th>Lembar Gelaran</th>
                                            <th>Average Time</th>
                                            <th>Kepala Kain</th>
                                            <th>Sisa Tidak Bisa</th>
                                            <th>Reject</th>
                                            <th>Piping</th>
                                            <th>Sisa Kain</th>
                                            <th class="label-calc">Pemakaian Lembar</th>
                                            <th class="label-calc">Total Pemakaian Per Roll</th>
                                            <th class="label-calc">Short Roll +/-</th>
                                            <th class="label-calc">Short Roll (%)</th>
                                            <th id="th-berat-amparan">Berat 1 Amparan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="8" class="text-center">Total</th>
                                            <th id="total-qty"></th>
                                            <th id="total-unit"></th>
                                            <th id="total-sisa-gelaran"></th>
                                            <th id="total-sambungan"></th>
                                            <th id="total-sambungan-roll"></th>
                                            <th id="total-est-amparan"></th>
                                            <th id="total-lembar"></th>
                                            <th id="total-average-time"></th>
                                            <th id="total-kepala-kain"></th>
                                            <th id="total-sisa-tidak-bisa"></th>
                                            <th id="total-reject"></th>
                                            <th id="total-piping"></th>
                                            <th id="total-sisa-kain"></th>
                                            <th id="total-pemakaian-lembar"></th>
                                            <th id="total-total-pemakaian"></th>
                                            <th id="total-short-roll"></th>
                                            <th id="total-short-roll-percentage"></th>
                                            <th id="total-berat-amparan"></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="row align-items-end">
                                <div class="col-sm-6 col-md-3">
                                    <div class="mb-3">
                                        <label class="form-labe label-calc"><small><b>Cons. Actual</b></small></label>
                                        <input type="text" class="form-control form-control-sm border-calc" name="cons_actual_gelaran" id="cons_actual_gelaran" readonly>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label label-calc"><small><b>Unit</b></small></label>
                                        <select class="form-select form-select-sm border-calc" name="unit_cons_actual_gelaran" id="unit_cons_actual_gelaran" disabled>
                                            <option value="meter">METER</option>
                                            <option value="yard">YARD</option>
                                            <option value="kgm">KGM</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-md-3">
                                    <div class="mb-3">
                                        <label class="form-labe label-calc"><small><b>Kenaikan Cons. WS</b></small></label>
                                        <div class="input-group input-group-sm mb-3">
                                            <input type="text" class="form-control border-calc" name="cons_ws_uprate" id="cons_ws_uprate" readonly>
                                            <span class="input-group-text border-calc">%</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-md-3">
                                    <div class="mb-3">
                                        <label class="form-labe label-calc"><small><b>Kenaikan Cons. Marker</b></small></label>
                                        <div class="input-group input-group-sm mb-3">
                                            <input type="text" class="form-control border-calc" name="cons_marker_uprate" id="cons_marker_uprate" readonly>
                                            <span class="input-group-text border-calc">%</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-md-3 d-none">
                                    <div class="mb-3">
                                        <label class="form-labe label-calc"><small><b>Cons. Actual Tanpa Short Roll</b></small></label>
                                        <input type="text" class="form-control form-control-sm border-calc" name="cons_actual_gelaran_short_rolless" id="cons_actual_gelaran_short_rolless" readonly>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-md-3 d-none">
                                    <div class="mb-3">
                                        <label class="form-label label-calc"><small><b>Unit</b></small></label>
                                        <select class="form-select form-select-sm border-calc" name="unit_cons_actual_gelaran_short_rolless" id="unit_cons_actual_gelaran_short_rolless" disabled>
                                            <option value="meter">METER</option>
                                            <option value="yard">YARD</option>
                                            <option value="kgm">KGM</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-md-3 d-none">
                                    <div class="mb-3">
                                        <label class="form-labe label-calc"><small><b>Kenaikan Cons. WS</b></small></label>
                                        <div class="input-group input-group-sm mb-3">
                                            <input type="text" class="form-control border-calc" name="cons_ws_uprate_nosr" id="cons_ws_uprate_nosr" readonly>
                                            <span class="input-group-text border-calc">%</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-md-3 d-none">
                                    <div class="mb-3">
                                        <label class="form-labe label-calc"><small><b>Kenaikan Cons. Marker</b></small></label>
                                        <div class="input-group input-group-sm mb-3">
                                            <input type="text" class="form-control border-calc" name="cons_marker_uprate_nosr" id="cons_marker_uprate_nosr" readonly>
                                            <span class="input-group-text border-calc">%</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label class="form-label label-input"><small><b>Operator</b></small></label>
                                        <input type="text" class="form-control form-control-sm border-input" name="operator" id="operator" value="{{ $formCutInputData->operator }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <button class="btn btn-block btn-sb d-none" id="finish-process" onclick="finishProcess()">SELESAI PENGERJAAN</button>
        </div>
    </div>
@endsection

@section('custom-script')
    <!-- Select2 -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>

    <script>
        // Select2 Autofocus
        $(document).on('select2:open', () => {
            document.querySelector('.select2-search__field').focus();
        });

        $('.select2').select2();

        $('.select2bs4').select2({
            theme: 'bootstrap4',
            containerCssClass: 'form-control-sm'
        });
    </script>

    <script>
        // Variable List :
            // -Form Cut Input Header Data-
            var id = document.getElementById("id").value;
            var status = document.getElementById("status").value;
            var startTime = document.getElementById("start-time");
            var finishTime = document.getElementById("finish-time");

            // -Process Button Elements-
            var startProcessButton = document.getElementById("start-process");
            var nextProcessOneButton = document.getElementById("next-process-1");
            var nextProcessTwoButton = document.getElementById("next-process-2");
            var nextProcessThreeButton = document.getElementById("next-process-3");
            var finishProcessButton = document.getElementById("finish-process");

            // -Current Scanned Item-
            var currentScannedItem = null;
            var currentSisaGelaran = null;

            // -Summary Data-
            var summaryData = null;

            // -Ratio & Qty Cuy-
            var totalRatio = document.getElementById('total_ratio').value;
            var totalQtyCut = document.getElementById('total_qty_cut_ply').value;

            // -Method-
            var method = "scan";

        // Function List :
            // -On Load-
            $(document).ready(async () => {
                document.getElementById("loading").classList.remove("d-none");

                await getNumberData();
                await clearGeneralForm();
                await clearScanItemForm();
                await clearSpreadingForm();
                await checkStatus();

                document.getElementById("loading").classList.add("d-none");

                // -Global Key Event-
                $(document).keyup(function(e) {
                    if (e.key === "Escape") {
                        e.preventDefault();

                        // stopTimeRecord()
                    }
                });

                // -Kode Barang Manual Input Event-
                $('#kode_barang').keyup(function(e) {
                    if (e.key === "Enter") {
                        e.preventDefault();

                        getScannedItem(this.value);
                    }
                });

                // -Trigger Next Lap Button on Key Up 'Enter'-
                nextLapButton.addEventListener("keyup", function(evt) {
                    if (evt.key === "Enter") {
                        // Cancel the default action, if needed
                        event.preventDefault();
                        // Trigger the button element with a click
                        nextLapButton.click();
                    }
                });

                // // -On Scan Card Collapse-
                // $('#scan-qr-card').on('collapsed.lte.cardwidget', function(e) {
                //     clearQrCodeScanner();
                // });

                // -On Scan Card Expand-
                $('#scan-qr-card').on('expanded.lte.cardwidget', function(e) {
                    refreshScan();
                });

                // -Default Method-
                await $('#switch-method').prop('checked', true);

                document.getElementById("current_jumlah_sambungan").value = 1;
            });

        // Process :
            // -Start Process-
            function startProcess() {
                Swal.fire({
                    icon: 'info',
                    title: 'Approve Pengerjaan Form Cut?',
                    text: 'Yakin akan approve proses pengerjaan?',
                    showCancelButton: true,
                    showConfirmButton: true,
                    confirmButtonText: 'Approve',
                    confirmButtonColor: "#6531a0",
                }).then(async (result) => {
                    if (result.isConfirmed) {
                        let now = new Date();
                        startTime.value = now.getFullYear().toString() + "-" + pad(now.getMonth() + 1) + "-" + pad(now.getDate()) + " " + pad(now.getHours()) + ":" + pad(now.getMinutes()) + ":" + pad(now.getSeconds());

                        updateToStartProcess();

                        startProcessButton.classList.add("d-none");
                        nextProcessOneButton.classList.remove("d-none");
                        document.getElementById("lost-time-card").classList.remove("d-none");
                    }
                });
            }

            // -Start Process Transaction-
            function updateToStartProcess() {
                return $.ajax({
                    url: '{{ route('start-process-form-cut-input') }}/' + id,
                    type: 'put',
                    dataType: 'json',
                    data: {
                        startTime: startTime.value,
                    },
                    success: function(res) {
                        if (res) {
                            status = "PENGERJAAN FORM CUTTING";
                        }
                    }
                });
            }

            // -Process One-
            function nextProcessOne() {
                updateToNextProcessOne();

                $('#header-data-card').CardWidget('collapse');
                $('#detail-data-card').removeClass('d-none');

                nextProcessOneButton.classList.add("d-none");
                nextProcessTwoButton.classList.remove("d-none");
            }

            // -Process One Transaction-
            function updateToNextProcessOne() {
                return $.ajax({
                    url: '{{ route('next-process-one-form-cut-input') }}/' + id,
                    type: 'put',
                    dataType: 'json',
                    data: {
                        shell: $("#shell").val()
                    },
                    success: function(res) {
                        if (res) {
                            console.log(res);
                            status = "PENGERJAAN FORM CUTTING DETAIL";
                        }
                    }
                });
            }

            // -Process Two-
            function nextProcessTwo() {
                updateToNextProcessTwo();
            }

            // -Process Two Transaction-
            function updateToNextProcessTwo() {
                var pActual = document.getElementById('p_act').value;
                var pUnitActual = document.getElementById('unit_p_act').value;
                var commaActual = document.getElementById('comma_act').value;
                var commaUnitActual = document.getElementById('unit_comma_act').value;
                var lActual = document.getElementById('l_act').value;
                var lUnitActual = document.getElementById('unit_l_act').value;
                var consActual = document.getElementById('cons_act').value;
                var consPipping = document.getElementById('cons_pipping').value;
                var consAmpar = document.getElementById('cons_ampar').value;
                var estPipping = document.getElementById('est_pipping').value;
                var estPippingUnit = document.getElementById('est_pipping_unit').value;
                var estKain = document.getElementById('est_kain').value;
                var estKainUnit = document.getElementById('est_kain_unit').value;

                clearModified();

                return $.ajax({
                    url: '{{ route('next-process-two-form-cut-input') }}/' + id,
                    type: 'put',
                    dataType: 'json',
                    data: {
                        p_act: pActual,
                        unit_p_act: pUnitActual,
                        comma_act: commaActual,
                        unit_comma_act: commaUnitActual,
                        l_act: lActual,
                        unit_l_act: lUnitActual,
                        cons_act: consActual,
                        cons_pipping: consPipping,
                        cons_ampar: consAmpar,
                        est_pipping: estPipping,
                        est_pipping_unit: estPippingUnit,
                        est_kain: estKain,
                        est_kain_unit: estKainUnit,
                    },
                    success: function(res) {
                        if (res) {
                            if (res.status == 200) {
                                $('#header-data-card').CardWidget('collapse');
                                $('#detail-data-card').CardWidget('collapse');
                                $('#scan-qr-card').removeClass('d-none');

                                nextProcessTwoButton.classList.add("d-none");
                                nextProcessThreeButton.classList.remove("d-none");

                                initScan();
                                getItemList()

                                status = "PENGERJAAN FORM CUTTING SPREAD"
                            }
                        }
                    },
                    error: function(jqXHR) {
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

            // -Process Three-
            function nextProcessThree() {
                updateToNextProcessThree();
            }

            // -Process Three Transaction-
            async function updateToNextProcessThree() {
                let validation = false;

                switch (method) {
                    case "scan" :
                        validation = isNotNull(document.getElementById("id_item").value) && isNotNull(document.getElementById("detail_item").value) && isNotNull(document.getElementById("color_act").value);
                        break;
                    case "item" :
                        validation = isNotNull(document.getElementById("select_item").value);
                        break;
                    default :
                        validation = isNotNull(document.getElementById("id_item").value) && isNotNull(document.getElementById("detail_item").value) && isNotNull(document.getElementById("color_act").value);
                        break;
                }

                if (validation && currentScannedItem) {
                    if (method == "scan" && currentScannedItem.qty && currentScannedItem.qty == 0) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: 'Pastikan item yang di scan tersedia dan color actual sudah diisi',
                            showCancelButton: false,
                            showConfirmButton: true,
                            confirmButtonText: 'Oke',
                        })
                    } else {
                        nextProcessThreeButton.classList.add("d-none");

                        $('#scan-qr-card').CardWidget('collapse');

                        setSpreadingForm(currentScannedItem, sisaGelaran, unitSisaGelaran);

                        getSummary();

                        $('#spreading-form-card').removeClass('d-none');
                        $('#spreading-form-card').CardWidget('expand');
                        $('#summary-card').removeClass('d-none');

                        location.href = "#spreading-form-card";
                    }
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Pastikan item yang di scan tersedia dan color actual sudah diisi',
                        showCancelButton: false,
                        showConfirmButton: true,
                        confirmButtonText: 'Oke',
                    })
                }
            }

            // -Back to Process Three-
            async function backToProcessThree() {
                storeTimeRecord();
            }

            // -Store Time Record Transaction-
            async function storeTimeRecord(isContinue = 0) {
                document.getElementById("loading").classList.remove("d-none");

                clearModified();

                let spreadingForm = new FormData(document.getElementById("spreading-form"));

                let dataObj = {
                    "id": $("#id").val(),
                    "p_act": $("#p_act").val(),
                    "unit_p_act": $("#unit_p_act").val(),
                    "comma_act": $("#comma_act").val(),
                    "no_form_cut_input": $("#no_form").val(),
                    "no_meja": $("#no_meja").val(),
                    "color_act": $("#color_act").val(),
                    "detail_item": $("#detail_item").val(),
                    "metode": method,
                }

                spreadingForm.forEach((value, key) => dataObj[key] = value);

                if ($("#current_unit").val() == "KGM" && $("#current_berat_amparan").val() <= 0) {
                    document.getElementById("loading").classList.add("d-none");

                    return Swal.fire({
                        icon: 'error',
                        title: 'Harap isi berat amparan',
                        showConfirmButton: true,
                        confirmButtonText: 'Oke',
                        confirmButtonColor: "#6531a0",
                    });
                }

                if (isContinue < 1) {
                    if (!($('#unlocked_by').val() && $('#unlocked_by').val() > 0)) {
                        if (!isNaN($("#current_short_roll_percentage").val()) && Number($("#current_short_roll_percentage").val()) < -2) {
                            document.getElementById("loading").classList.add("d-none");

                            lockForm();

                            return Swal.fire({
                                icon: 'error',
                                title: 'Short Roll Kurang dari -2%',
                                html: `
                                    Harap laporkan kepada atasan untuk dapat melanjutkan
                                    <input type='text' class='my-3 form-control form-control-sm' id='unlock_form_username' placeholder='Masukkan username...' onkeyup='unlockEnter(event)'>
                                    <input type='password' class='my-3 form-control form-control-sm' id='unlock_form_password' placeholder='Masukkan password...' onkeyup='unlockEnter(event)'>
                                    <button type='button' class='mb-3 btn btn-primary' id='submit_form_unlock_token' onclick='unlockForm()'>Lanjutkan Form</button>
                                `,
                                showCancelButton: false,
                                showConfirmButton: false,
                                allowOutsideClick: false,
                            });
                        }
                    }
                }

                if ($("#status_sambungan").val() != "extension") {
                    // Not an Extension :
                    return $.ajax({
                        url: '{{ route('store-time-form-cut-input') }}',
                        type: 'post',
                        dataType: 'json',
                        data: dataObj,
                        success: function(res) {
                            document.getElementById("loading").classList.add("d-none");

                            if (res) {
                                timeRecordTableTbody.innerHTML = "";

                                clearScanItemForm();
                                openScanItemForm();
                                clearSpreadingForm();
                                firstTimeRecordCondition();

                                nextProcessThreeButton.classList.remove('d-none');

                                if (res.additional.length > 0) {
                                    $('#summary-card').removeClass('d-none');

                                    appendScannedItem(res.additional[0]);

                                    if (res.additional.length > 1) {
                                        if (res.additional[1]) {
                                            sisaGelaran = res.additional[0].sisa_gelaran;
                                            unitSisaGelaran = res.additional[0].unit;
                                            setSpreadingForm(res.additional[1], sisaGelaran, unitSisaGelaran);
                                        } else {
                                            checkStatus();
                                        }
                                    }
                                }

                                resetTimeRecord();
                                resetSambungan();
                            }
                        },
                        error: function(jqXHR) {
                            document.getElementById("loading").classList.add("d-none");

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
                } else {
                    // An Extension :
                    await calculateTotalPemakaian();

                    return $.ajax({
                        url: '{{ route('store-time-ext-form-cut-input') }}',
                        type: 'post',
                        dataType: 'json',
                        data: dataObj,
                        success: function(res) {
                            document.getElementById("loading").classList.add("d-none");

                            if (res) {
                                timeRecordTableTbody.innerHTML = "";

                                clearScanItemForm();
                                openScanItemForm();
                                clearSpreadingForm();
                                firstTimeRecordCondition();

                                nextProcessThreeButton.classList.remove('d-none');

                                if (res.additional.length > 0) {
                                    $('#summary-card').removeClass('d-none');

                                    appendScannedItem(res.additional[0]);

                                    if (res.additional.length > 1) {
                                        if (res.additional[1]) {
                                            sisaGelaran = res.additional[0].sisa_gelaran;
                                            unitSisaGelaran = res.additional[0].unit;
                                            setSpreadingForm(res.additional[1], sisaGelaran, unitSisaGelaran);
                                        } else {
                                            checkStatus();
                                        }
                                    }
                                }

                                resetTimeRecord();
                                resetSambungan();
                            }
                        },
                        error: function(jqXHR) {
                            document.getElementById("loading").classList.add("d-none");

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
            }

            async function unlockEnter(evt, isStoring = 0) {
                if (evt.keyCode == 13) {
                    unlockForm(isStoring);
                }
            }

            async function lockForm() {
                document.getElementById("loading").classList.remove('d-none');

                $.ajax({
                    url: '{{ route('form-cut-lock') }}',
                    method: 'POST',
                    data: {
                        id: $("#id").val()
                    },
                    success: function (res) {
                        document.getElementById("loading").classList.add('d-none');

                        if (res) {
                            iziToast.warning({
                                title: 'Form di Kunci',
                                message: 'Harap hubungi atasan untuk melanjutkan',
                                position: 'topCenter'
                            });
                        }
                    },
                    error: function (jqXHR) {
                        document.getElementById("loading").classList.add('d-none');

                        console.error(jqXHR);
                    }
                });
            }

            async function unlockForm(isStoring = 0) {
                document.getElementById("loading").classList.remove('d-none');

                if ($("#unlock_form_username").val() && $("#unlock_form_password").val()) {
                    $.ajax({
                        url: '{{ route('form-cut-unlock') }}',
                        method: 'POST',
                        data: {
                            id: $("#id").val(),
                            username: $("#unlock_form_username").val(),
                            password: $("#unlock_form_password").val()
                        },
                        success: function (res) {
                            document.getElementById("loading").classList.add('d-none');

                            if (res) {
                                if (res.locked < 1) {
                                    Swal.close();

                                    iziToast.success({
                                        title: 'Berhasil',
                                        message: 'Form berhasil dibuka',
                                        position: 'topCenter'
                                    });

                                    $("#locked").val(res.locked);
                                    $("#unlocked_by").val(res.unlocked_by);

                                    if (isStoring > 0) {
                                        storeTimeRecord(1);
                                    }
                                } else {
                                    iziToast.error({
                                        title: 'Form gagal dibuka',
                                        message: 'Password salah',
                                        position: 'topCenter'
                                    });
                                }
                            }
                        },
                        error: function (jqXHR) {
                            document.getElementById("loading").classList.add('d-none');

                            console.error(jqXHR);
                        }
                    });
                } else {
                    document.getElementById("loading").classList.add('d-none');

                    iziToast.error({
                        title: 'Form gagal dibuka',
                        message: 'Harap isi kolom password',
                        position: 'topCenter'
                    });
                }
            }

            // -Store This Time Record Transaction-
            function storeThisTimeRecord() {
                document.getElementById("loading").classList.remove("d-none");

                let spreadingForm = new FormData(document.getElementById("spreading-form"));

                let dataObj = {
                    "id": $("#id").val(),
                    "no_form_cut_input": $("#no_form").val(),
                    "color_act": $("#color_act").val(),
                    "detail_item": $("#detail_item").val(),
                    "no_meja": $("#no_meja").val(),
                    "metode": method,
                    "lap": lap
                }

                spreadingForm.forEach((value, key) => dataObj[key] = value);

                return $.ajax({
                    url: '{{ route('store-this-time-form-cut-input') }}',
                    type: 'post',
                    dataType: 'json',
                    data: dataObj,
                    success: function(res) {
                        document.getElementById("loading").classList.add("d-none");

                        if (res) {
                            console.log(res);
                        }
                    }, error: function(jqXHR) {
                        document.getElementById("loading").classList.add("d-none");

                        console.log(jqXHR);

                        Swal.fire({
                            icon: 'error',
                            title: 'Koneksi Hilang',
                            text: 'Terjadi Kesalahan',
                            showConfirmButton: true,
                            confirmButtonText: 'Oke',
                            confirmButtonColor: "#6531a0",
                        }).then(() => {
                            // location.reload();
                        });
                    }
                });
            }

            // -Finish Process-
            function finishProcess() {
                let now = new Date();
                finishTime.value = now.getFullYear().toString() + "-" + pad(now.getMonth() + 1) + "-" + pad(now.getDate()) +" " + pad(now.getHours()) + ":" + pad(now.getMinutes()) + ":" + pad(now.getSeconds());

                if ($("#operator").val() == "" || $("#cons_actual_gelaran").val() == "") {
                    return Swal.fire({
                        icon: 'error',
                        title: 'Tidak Dapat Menyelesaikan Proses',
                        text: 'Harap pastikan data "Operator" dan "Cons. Actual 1 Gelaran" telah terisi',
                        showConfirmButton: true,
                        confirmButtonText: 'Oke',
                        confirmButtonColor: "#6531a0",
                    });
                }

                updateToFinishProcess();
            }

            // -Finish Process Transaction-
            function updateToFinishProcess() {
                Swal.fire({
                    icon: 'info',
                    title: 'Selesaikan Pengerjaan?',
                    text: 'Yakin akan menyelesaikan proses pengerjaan?',
                    showCancelButton: true,
                    showConfirmButton: true,
                    confirmButtonText: 'Selesaikan',
                    confirmButtonColor: "#6531a0",
                }).then(async (result) => {
                    if (result.isConfirmed) {
                        document.getElementById("loading").classList.remove("d-none");

                        await updateToNextProcessOne();
                        await updateToNextProcessTwo();

                        $.ajax({
                            url: '{{ route('finish-process-form-cut-input') }}/' + id,
                            type: 'put',
                            dataType: 'json',
                            data: {
                                finishTime: finishTime.value,
                                operator: $('#operator').val(),
                                consAct: $('#cons_actual_gelaran').val(),
                                unitConsAct: $('#unit_cons_actual_gelaran').val(),
                                consActNosr: $('#cons_actual_gelaran_short_rolless').val(),
                                unitConsActNosr: $('#unit_cons_actual_gelaran_short_rolless').val(),
                                consWsUprate: $('#cons_ws_uprate').val(),
                                consMarkerUprate: $('#cons_marker_uprate').val(),
                                consWsUprateNoSr: $('#cons_ws_uprate_nosr').val(),
                                consMarkerUprateNoSr: $('#cons_marker_uprate_nosr').val(),
                                totalLembar: totalLembar
                            },
                            success: function(res) {
                                document.getElementById("loading").classList.add("d-none");

                                if (res) {
                                    lockFormCutInput();

                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Berhasil',
                                        text: 'Proses telah berhasil diselesaikan (Laman akan ditutup)',
                                        showCancelButton: false,
                                        showConfirmButton: true,
                                        confirmButtonText: 'Oke',
                                        timer: 3000,
                                        timerProgressBar: true
                                    }).then((result) => {
                                        window.close();
                                    })

                                    status = "SELESAI PENGERJAAN";
                                }
                            },
                            error: function(jqXHR) {
                                document.getElementById("loading").classList.add("d-none");

                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal',
                                    text: 'Terjadi kesalahan',
                                    showCancelButton: false,
                                    showConfirmButton: true,
                                    confirmButtonText: 'Oke',
                                });
                            }
                        });
                    }
                });
            }

            // -Lock Process-
            function lockProcessCondition() {
                startProcessButton.disabled = true;
                nextProcessOneButton.disabled = true;
                nextProcessTwoButton.disabled = true;
                nextProcessThreeButton.disabled = true;
                finishProcessButton.disabled = true;
            }

            // -Calculate P. Actual + Comma Actual-
            function pActualCommaActual(pActualVar, unitPActualVar, commaActualVar) {
                let pActualFinal = 0;

                if (unitPActualVar == "YARD" || unitPActualVar == "YRD") {
                    let commaMeter = commaActualVar / 36;

                    pActualFinal = (pActualVar + commaMeter);
                } else if (unitPActualVar == "METER") {
                    let commaMeter = commaActualVar / 100;

                    pActualFinal = (pActualVar + commaMeter);
                }

                return pActualFinal;
            }

            // -Convert P. Actual-
            function pActualConversion(pActualVar, unitPActualVar, commaActualVar, lActualVar, gramasiVar, unitQtyVar) {
                let pActualConverted = 0;

                if (unitQtyVar == unitPActualVar) {
                    if (unitPActualVar == "YARD" || unitPActualVar == "YRD") {
                        pActualConverted = pActualVar + (commaActualVar / 36);
                    } else if (unitPActualVar == "METER") {
                        pActualConverted = pActualVar + (commaActualVar / 100);
                    }
                } else {
                    // YARD
                    if (unitPActualVar == "YARD" || unitPActualVar == "YRD") {
                        let pActualInch = ((pActualVar * 36 / 1) + commaActualVar)

                        if (unitQtyVar == "METER") {
                            pActualConverted = pActualInch * 0.0254;
                        } else if (unitQtyVar == "KGM") {
                            let gramasiInch = gramasiVar / 1550;

                            pActualConverted = ((gramasiInch * (pActualInch * lActualVar)) / 1000);
                        } else {
                            pActualConverted = pActualVar + (commaActualVar / 36);
                        }

                        // METER
                    } else if (unitPActualVar == "METER") {
                        let pActualInch = ((pActualVar * 39.3701) + (commaActualVar / 2.54));
                        let lActualInch = lActualVar / 2.54;

                        if (unitQtyVar == "YARD" || unitQtyVar == "YRD") {
                            pActualConverted = pActualInch / 36;
                        } else if (unitQtyVar == "KGM") {
                            let gramasiInch = gramasiVar / 1550;

                            pActualConverted = ((gramasiInch * (pActualInch * lActualInch)) / 1000);
                        } else {
                            pActualConverted = pActualVar + (commaActualVar / 100);
                        }
                    }
                }

                return pActualConverted;
            }

            // -Convert Roll Qty Actual-
            function rollQtyConversion(rollQtyVar, unitQtyVar, gramasi = 0, pActual = 0, lActual = 0, commaActual = 0) {
                let rollQtyConverted = 0;
                let gramasiVar = gramasi > 0 ? Number(gramasi) : Number(document.getElementById("gramasi").value);
                let pActualVar = pActual > 0 ? Number(pActual) : Number(document.getElementById("p_act").value);
                let lActualVar = lActual > 0 ? Number(lActual) : Number(document.getElementById("l_act").value);
                let commaActualVar = commaActual > 0 ? Number(commaActual) : Number(document.getElementById("comma_act").value);

                if (rollQtyVar && unitQtyVar) {
                    if (unitQtyVar == "YARD" || unitQtyVar == "YRD") {
                        // YARD
                        rollQtyConverted = rollQtyVar * 0.9144;

                    } else if (unitQtyVar == "KGM") {
                        // KGM
                        let gramasiConverted = gramasiVar / 1000;
                        let lActualConverted = lActualVar / 100;

                        rollQtyConverted = rollQtyVar / (gramasiConverted * lActualConverted);

                    } else {
                        // METER

                        rollQtyConverted = rollQtyVar;
                    }

                    return Number(rollQtyConverted).round(2);
                }

                return null;
            }

            function setRollQtyConversion(rollQty = 0, unitQty) {
                let rollQtyVar = rollQty > 0 ? Number(rollQty) : Number(document.getElementById("current_qty_real").value);
                let unitQtyVar = unitQty ? unitQty : document.getElementById("current_unit").value;

                document.getElementById("current_qty").value = rollQtyConversion(rollQtyVar, unitQtyVar);

                // if (unitQtyVar == "KGM" || unitQtyVar == "KG") {
                //     document.getElementById("current_qty_awal").value = rollQtyVar;
                //     document.getElementById("current_unit_awal").innerHTML = unitQtyVar;
                // } else {
                //     document.getElementById("current_qty_awal").value = rollQtyConversion(rollQtyVar, unitQtyVar);
                //     document.getElementById("current_unit_awal").innerHTML = "METER";
                // }

                calculateEstAmpar();
            }

            function conversion(qty, unit, unitBefore) {
                console.log("convert", qty, unitBefore, unit);

                let gramasiVar = Number(document.getElementById("gramasi").value);
                let pActualVar = Number(document.getElementById("p_act").value);
                let lActualVar = Number(document.getElementById("l_act").value);
                let commaActualVar = Number(document.getElementById("comma_act").value);

                let qtyConverted = 0;

                if (qty && unit && unitBefore) {
                    if (unit == unitBefore) {
                        qtyConverted = qty;
                    } else {
                        if (unitBefore == "KGM" && unit == "METER") {
                            // KGM
                            let gramasiConverted = gramasiVar / 1000;
                            let lActualConverted = lActualVar / 100;

                            qtyConverted = qty / (gramasiConverted * lActualConverted);
                        } else if (unitBefore == "METER" && unit == "KGM") {
                            let gramasiInch = gramasiVar / 1550;
                            let qtyInch = qty * 39.3701;
                            let lActualInch = lActualVar / 2.54;

                            qtyConverted = (gramasiInch * (qtyInch * lActualInch)) / 1000;
                        }
                    }

                    return Number(qtyConverted).round(2);
                }

                return null;
            }

            // -Restrict Sisa Gelaran-
            function restrictRemainPly() {
                let estSambungan = calculateSambungan();

                if (estSambungan <= 0) {
                    // document.getElementById('current_sisa_gelaran').value = 0;

                    iziToast.warning({
                        title: 'Warning',
                        message: 'Sisa gelaran telah melebihi Panjang actual',
                        position: 'topCenter'
                    });
                }
            }

            // -Calculate Cons Ampar-
            function calculateConsAmpar() {
                let pActualVar = Number(document.getElementById("p_act").value);
                let unitPActualVar = document.getElementById("unit_p_act").value;
                let commaActualVar = Number(document.getElementById("comma_act").value);
                let lActualVar = Number(document.getElementById("l_act").value);
                let gramasiVar = Number(document.getElementById("gramasi").value);
                let lActualMeter = lActualVar / 100;

                let pActualFinal = pActualCommaActual(pActualVar, unitPActualVar, commaActualVar);

                consAmpar = totalRatio > 0 ? (gramasiVar * pActualFinal * lActualMeter) / 1000 : 0;

                document.getElementById('cons_ampar').value = consAmpar.round(2);
            }

            // -Calculate Cons Act-
            function calculateConsAct() {
                let pActualVar = Number(document.getElementById("p_act").value);
                let unitPActualVar = document.getElementById("unit_p_act").value;
                let commaActualVar = Number(document.getElementById("comma_act").value);

                let consActual = 0;

                let pActualFinal = pActualCommaActual(pActualVar, unitPActualVar, commaActualVar);

                // consActual = totalQtyCut > 0 ? pActualFinal / totalQtyCut : 0;
                consActual = totalRatio > 0 ? pActualFinal / totalRatio : 0;

                document.getElementById('cons_act').value = consActual.round(3);
            }

            // -Calculate Est. Piping-
            function calculateEstPipping(consPipping = 0) {
                let consPippingVar = consPipping;

                let estPipping = consPippingVar * totalQtyCut;

                document.getElementById('est_pipping').value = estPipping.round(2);
            }

            // -Calculate Est. Kain-
            function calculateEstKain(consMarker = 0) {
                let consMarkerVar = consMarker;

                let estKain = consMarkerVar * totalQtyCut

                document.getElementById('est_kain').value = estKain.round(2);
            }

            // -Calculate Est. Ampar-
            function calculateEstAmpar() {
                let qtyVar = Number(document.getElementById("current_qty").value);
                let pActualVar = Number(document.getElementById("p_act").value);
                let lActualVar = Number(document.getElementById("l_act").value);
                let commaActualVar = Number(document.getElementById("comma_act").value);
                let unitQtyVar = document.getElementById("current_unit").value;
                let unitPActualVar = document.getElementById("unit_p_act").value;
                let unitCommaActualVar = document.getElementById("unit_comma_act").value;
                let gramasiVar = Number(document.getElementById("gramasi").value);

                let pActualConverted = 0;

                if (unitQtyVar != "KGM") {
                    pActualConverted = pActualCommaActual(pActualVar, unitPActualVar, commaActualVar);
                } else {
                    qtyVar = Number(document.getElementById("current_qty_real").value);

                    // pActualConverted = pActualConversion(pActualVar, unitPActualVar, commaActualVar, lActualVar, gramasiVar, unitQtyVar);
                    pActualConverted = Number(document.getElementById("current_berat_amparan").value);
                }

                let estAmpar = pActualConverted > 0 ? qtyVar / pActualConverted : 0;

                document.getElementById("current_est_amparan").value = estAmpar.round(2);
            }

            // -Calculate Pemakaian Lembar
            function calculatePemakaianLembar() {
                let lembarGelaranVar = Number(document.getElementById("current_lembar_gelaran").value);
                let pActualVar = Number(document.getElementById("p_act").value);
                let sisaGelaranVar = Number(document.getElementById("current_sisa_gelaran").value);
                let sambunganVar = Number(document.getElementById("current_sambungan").value);
                let sambunganRollVar = Number(document.getElementById("current_total_sambungan_roll").value);
                let qtyVar = Number(document.getElementById("current_qty").value);
                let unitQtyVar = document.getElementById("current_unit").value;
                let gramasiVar = Number(document.getElementById("gramasi").value);
                let unitPActualVar = document.getElementById("unit_p_act").value;
                let lActualVar = Number(document.getElementById("l_act").value);
                let commaActualVar = Number(document.getElementById("comma_act").value);

                let pActualConverted = 0;

                if (document.getElementById("status_sambungan").value == "extension") {
                    pActualConverted = document.getElementById("current_sambungan").value;
                } else {
                    if (unitQtyVar != "KGM") {
                        pActualConverted = pActualCommaActual(pActualVar, unitPActualVar, commaActualVar);
                    } else {
                        qtyVar = Number(document.getElementById("current_qty_real").value);

                        // pActualConverted = pActualConversion(pActualVar, unitPActualVar, commaActualVar, lActualVar, gramasiVar, unitQtyVar);
                        pActualConverted = Number(document.getElementById("current_berat_amparan").value);
                    }
                }

                let totalPemakaian = ((pActualConverted * lembarGelaranVar) + sambunganRollVar + sisaGelaranVar);

                document.getElementById("current_pemakaian_lembar").value = totalPemakaian.round(2);
            }

            // -Calculate Total Pemakaian Roll-
            function calculateTotalPemakaian() {
                // Legacy
                    // let lembarGelaranVar = Number(document.getElementById("current_lembar_gelaran").value);
                    // let pActualVar = Number(document.getElementById("p_act").value);
                    // let kepalaKainVar = Number(document.getElementById("current_kepala_kain").value);
                    // let sisaTidakBisaVar = Number(document.getElementById("current_sisa_tidak_bisa").value);
                    // let rejectVar = Number(document.getElementById("current_reject").value);
                    // let lActualVar = Number(document.getElementById("l_act").value);
                    // let gramasiVar = Number(document.getElementById("gramasi").value);
                    // let unitQtyVar = document.getElementById("current_unit").value;
                    // let unitPActualVar = document.getElementById("unit_p_act").value;
                    // let commaActualVar = Number(document.getElementById("comma_act").value);

                    // let pActualConverted = 0;

                    // if (document.getElementById("status_sambungan").value == "extension") {
                    //     pActualConverted = document.getElementById("current_sambungan").value;
                    // } else {
                    //     if (unitQtyVar != "KGM") {
                    //         pActualConverted = pActualCommaActual(pActualVar, unitPActualVar, commaActualVar);
                    //     } else {
                    //         qtyVar = Number(document.getElementById("current_qty_real").value);

                    //         // pActualConverted = pActualConversion(pActualVar, unitPActualVar, commaActualVar, lActualVar, gramasiVar, unitQtyVar);
                    //         pActualConverted = Number(document.getElementById("current_berat_amparan").value);
                    //     }
                    // }

                    // // let totalPemakaian = lembarGelaranVar * pActualConverted + kepalaKainVar + sisaTidakBisaVar + rejectVar;
                    // let totalPemakaian = lembarGelaranVar * pActualConverted;

                    // document.getElementById("current_total_pemakaian_roll").value = totalPemakaian.round(2);

                let lembarGelaranVar = Number(document.getElementById("current_lembar_gelaran").value);
                let pActualVar = Number(document.getElementById("p_act").value);
                let unitPActualVar = document.getElementById("unit_p_act").value;
                let lActualVar = Number(document.getElementById("l_act").value);
                let commaActualVar = Number(document.getElementById("comma_act").value);
                let qtyVar = Number(document.getElementById("current_qty").value);
                let unitQtyVar = document.getElementById("current_unit").value;
                let gramasiVar = Number(document.getElementById("gramasi").value);

                let kepalaKainVar = Number(document.getElementById("current_kepala_kain").value);
                let pipingVar = Number(document.getElementById("current_piping").value);
                let sisaKainVar = Number(document.getElementById("current_sisa_kain").value);
                let rejectVar = Number(document.getElementById("current_reject").value);
                let sambunganVar = Number(document.getElementById("current_sambungan").value);
                let sambunganRollVar = Number(document.getElementById("current_total_sambungan_roll").value);
                let sisaGelaranVar = Number(document.getElementById("current_sisa_gelaran").value);
                let sisaTidakBisaVar = Number(document.getElementById("current_sisa_tidak_bisa").value);

                let pActualConverted = 0;

                if (document.getElementById("status_sambungan").value == "extension") {
                    pActualConverted = document.getElementById("current_sambungan").value;
                } else {
                    if (unitQtyVar != "KGM") {
                        pActualConverted = pActualCommaActual(pActualVar, unitPActualVar, commaActualVar);
                    } else {
                        qtyVar = Number(document.getElementById("current_qty_real").value);

                        // pActualConverted = pActualConversion(pActualVar, unitPActualVar, commaActualVar, lActualVar, gramasiVar, unitQtyVar);
                        pActualConverted = Number(document.getElementById("current_berat_amparan").value);
                    }
                }

                let totalPemakaian = ((pActualConverted * lembarGelaranVar) + sisaGelaranVar + kepalaKainVar + sisaTidakBisaVar + rejectVar + pipingVar + sambunganRollVar);

                document.getElementById("current_total_pemakaian_roll").value = totalPemakaian.round(2);
            }

            // -Calculate Short Roll-
            async function calculateShortRoll(event) {
                // if (event.type == "change") {
                //     document.getElementById("loading").classList.remove("d-none");
                // }

                let shortRollResult = await calculatingShortRoll();

                if (shortRollResult) {
                    document.getElementById("current_short_roll").value = isNaN(shortRollResult.shortRoll.round(2)) ? 0 : shortRollResult.shortRoll.round(2);
                    document.getElementById("current_short_roll_percentage").value = isNaN(shortRollResult.shortRollPercentage.round(2)) ? 0 : shortRollResult.shortRollPercentage.round(2);

                    // if (event.type == 'change') {
                    //     setTimeout(() => {
                    //         document.getElementById("loading").classList.add("d-none");
                    //     }, 300)
                    // }
                }
            }

            async function calculatingShortRoll() {
                var toast = document.querySelector('.iziToast'); // Selector of your toast

                if (toast) {
                    iziToast.hide({}, toast);
                }

                let lembarGelaranVar = Number(document.getElementById("current_lembar_gelaran").value);
                let pActualVar = Number(document.getElementById("p_act").value);
                let unitPActualVar = document.getElementById("unit_p_act").value;
                let lActualVar = Number(document.getElementById("l_act").value);
                let commaActualVar = Number(document.getElementById("comma_act").value);
                let qtyAwalVar =  Number(document.getElementById("current_qty_awal").value);
                let qtyVar =  latestStatus == "extension complete" ? Number(latestQty) : Number(document.getElementById("current_qty").value);
                let unitQtyVar = document.getElementById("current_unit").value;
                let gramasiVar = Number(document.getElementById("gramasi").value);

                let kepalaKainVar = latestStatus == "extension complete" ? Number(latestKepalaKain) + Number(document.getElementById("current_kepala_kain").value) : Number(document.getElementById("current_kepala_kain").value);
                let pipingVar = latestStatus == "extension complete" ? Number(latestPiping) + Number(document.getElementById("current_piping").value) : Number(document.getElementById("current_piping").value);
                let sisaKainVar = Number(document.getElementById("current_sisa_kain").value);
                let rejectVar = latestStatus == "extension complete" ? Number(latestReject) + Number(document.getElementById("current_reject").value) : Number(document.getElementById("current_reject").value);
                let sambunganVar = latestStatus == "extension complete" ? Number(latestSambungan) + Number(document.getElementById("current_sambungan").value) : Number(document.getElementById("current_sambungan").value);
                let sambunganRollVar = latestStatus == "extension complete" ? Number(latestSambunganRoll) + Number(document.getElementById("current_total_sambungan_roll").value) : Number(document.getElementById("current_total_sambungan_roll").value);
                let sisaGelaranVar = Number(document.getElementById("current_sisa_gelaran").value);
                let sisaTidakBisaVar = latestStatus == "extension complete" ? Number(latestSisaTidakBisa) + Number(document.getElementById("current_sisa_tidak_bisa").value) : Number(document.getElementById("current_sisa_tidak_bisa").value);

                let pActualConverted = 0;

                if (document.getElementById("status_sambungan").value == "extension" && Number(document.getElementById("current_sambungan").value) > 0) {
                    pActualConverted = document.getElementById("current_sambungan").value;
                } else {
                    if (unitQtyVar != "KGM") {
                        pActualConverted = pActualCommaActual(pActualVar, unitPActualVar, commaActualVar);
                    } else {
                        qtyVar = Number(document.getElementById("current_qty_real").value);

                        // pActualConverted = pActualConversion(pActualVar, unitPActualVar, commaActualVar, lActualVar, gramasiVar, unitQtyVar);
                        pActualConverted = Number(document.getElementById("current_berat_amparan").value);
                    }
                }

                // let shortRoll = pActualConverted * lembarGelaranVar + kepalaKainVar + pipingVar + sisaKainVar + rejectVar + sambunganVar - qtyVar;
                let shortRoll = ((pActualConverted * lembarGelaranVar) + sambunganVar + sisaGelaranVar + sambunganRollVar + kepalaKainVar + sisaTidakBisaVar + rejectVar + sisaKainVar + pipingVar) - qtyVar;

                if (document.getElementById("status_sambungan").value == "extension" && Number(document.getElementById("current_sambungan").value) > 0) {
                    shortRoll = 0;
                }

                let shortRollPercentage = qtyAwalVar > 0 ? (shortRoll / qtyAwalVar) * 100 : 0;

                if (!($('#unlocked_by').val() && $('#unlocked_by').val() > 0)) {
                    if (status == "PENGERJAAN FORM CUTTING SPREAD") {
                        if (!isNaN(shortRollPercentage) && shortRollPercentage < -2) {
                            iziToast.warning({
                                title: 'Warning',
                                message: 'Short Roll kurang dari -2 %',
                                position: 'topCenter'
                            });
                        } else {
                            iziToast.success({
                                title: 'Aman',
                                message: 'Short Roll tidak kurang dari -2 %',
                                position: 'topCenter'
                            });
                        }
                    }
                }

                return {"shortRoll" : shortRoll, "shortRollPercentage" : shortRollPercentage};
            }

            // -Calculate Sisa Kain-
            function calculateSisaKain() {
                let lembarGelaranVar = Number(document.getElementById("current_lembar_gelaran").value);
                let kepalaKainVar = Number(document.getElementById("current_kepala_kain").value);
                let rejectVar = Number(document.getElementById("current_reject").value);
                let pipingVar = Number(document.getElementById("current_piping").value);
                let sisaTidakBisaVar = Number(document.getElementById("current_sisa_tidak_bisa").value);

                let pActualVar = Number(document.getElementById("p_act").value);
                let lActualVar = Number(document.getElementById("l_act").value);
                let unitPActualVar = document.getElementById("unit_p_act").value;
                let commaActualVar = Number(document.getElementById("comma_act").value);
                let gramasiVar = Number(document.getElementById("gramasi").value);

                let qtyVar = Number(document.getElementById("current_qty").value);
                let unitQtyVar = document.getElementById("current_unit").value;

                let pActualConverted = 0;

                if (document.getElementById("status_sambungan").value == "extension") {
                    pActualConverted = document.getElementById("current_sambungan").value;
                } else {
                    if (unitQtyVar != "KGM") {
                        pActualConverted = pActualCommaActual(pActualVar, unitPActualVar, commaActualVar);
                    } else {
                        qtyVar = Number(document.getElementById("current_qty_real").value);

                        // pActualConverted = pActualConversion(pActualVar, unitPActualVar, commaActualVar, lActualVar, gramasiVar, unitQtyVar);
                        pActualConverted = Number(document.getElementById("current_berat_amparan").value);
                    }
                }

                let sisaKain = qtyVar - ((pActualConverted * lembarGelaranVar) + kepalaKainVar + sisaTidakBisaVar + rejectVar + rejectVar + pipingVar);

                document.getElementById("current_sisa_kain").value = sisaKain.round(2);
            }

            // -Calculate Sambungan-
            function calculateSambungan(sisaGelaranParam, unitSisaGelaranParam) {
                let sisaGelaranVar = sisaGelaranParam > 0 ? Number(sisaGelaranParam) : (sisaGelaran > 0 ? Number(sisaGelaran) : Number(document.getElementById("current_sisa_gelaran").value));
                let unitSisaGelaranVar = unitSisaGelaranParam ? unitSisaGelaranParam : (unitSisaGelaran ? unitSisaGelaran : document.getElementById("current_sisa_gelaran_unit").value);
                let qtyVar = Number(document.getElementById("current_qty").value);
                let unitQtyVar = document.getElementById("current_unit").value;
                let pActualVar = Number(document.getElementById('p_act').value);
                let unitPActualVar = document.getElementById('unit_p_act').value;
                let commaActualVar = Number(document.getElementById('comma_act').value);
                let lActualVar = Number(document.getElementById('l_act').value);
                let gramasiVar = Number(document.getElementById('gramasi').value);

                let pActualConverted = 0;
                let sisaGelaranConverted = 0;

                // Convert P Actual
                if (unitQtyVar != "KGM") {
                    pActualConverted = pActualCommaActual(pActualVar, unitPActualVar, commaActualVar);
                } else {
                    // pActualConverted = pActualConversion(pActualVar, unitPActualVar, commaActualVar, lActualVar, gramasiVar, unitQtyVar);
                    pActualConverted = Number(document.getElementById("current_berat_amparan").value);
                }

                // Convert Sisa Gelaran
                if (unitSisaGelaranVar == unitQtyVar) {
                    sisaGelaranConverted = sisaGelaranVar;
                } else {
                    if (unitQtyVar == "YARD" || unitQtyVar == "YRD") {
                        unitQtyVar = "METER";
                    }

                    sisaGelaranConverted = conversion(sisaGelaranVar, unitQtyVar, unitSisaGelaranVar);
                }

                console.log("sisa gelaran = "+sisaGelaranVar, "kain actual = "+pActualConverted);

                let estSambungan = pActualConverted - sisaGelaranConverted;

                if (latestStatus == "need extension" || sisaGelaran > 0) {
                    document.getElementById("current_sambungan").value = estSambungan.round(2);
                    document.getElementById("current_total_pemakaian_roll").value = estSambungan.round(2);
                }

                return estSambungan.round(2);
            }

            // Legacy
                // -Calculate Cons. Actual 1 Gelaran-
                // function calculateConsActualGelaran(unit = 0, piping = 0, lembar = 0, totalQtyFabric = 0, totalQtyCut = 0, totalPemakaian) {
                //     let unitVar = unit;
                //     let pipingVar = Number(piping);
                //     let lembarVar = Number(lembar);
                //     let totalQtyFabricVar = Number(totalQtyFabric);
                //     let totalQtyCutVar = Number(totalQtyCut);
                //     let totalPemakaianVar = Number(totalPemakaian);
                //     let pActualVar = Number(document.getElementById('p_act').value);
                //     let unitPActualVar = document.getElementById("unit_p_act").value;
                //     let lActualVar = Number(document.getElementById("l_act").value);
                //     let commaActualVar = Number(document.getElementById("comma_act").value);
                //     let gramasiVar = Number(document.getElementById("gramasi").value);

                //     if (isNotNull(unitVar) && isNotNull(pipingVar) && isNotNull(lembarVar) && isNotNull(pActualVar) && isNotNull(totalQtyCutVar)) {
                //         let consActualGelaran = 0;

                //         let commaMeter = commaActualVar / 100;

                //         let pActualConverted = 0;

                //         if (document.getElementById("status_sambungan").value == "extension") {
                //             pActualConverted = document.getElementById("current_sambungan").value;
                //         } else {
                //             if (unitVar != "KGM") {
                //                 pActualConverted = pActualCommaActual(pActualVar, unitPActualVar,
                //                     commaActualVar);
                //             } else {
                //                 pActualConverted = pActualConversion(pActualVar, unitPActualVar, commaActualVar,
                //                     lActualVar,
                //                     gramasiVar, unitVar);
                //             }
                //         }

                //         // consActualGelaran = totalQtyCutVar > 0 ? (lembarVar * pActualConverted) / totalQtyCutVar : 0;
                //         consActualGelaran = totalPemakaianVar / lembarVar;

                //         document.getElementById("cons_actual_gelaran").value = consActualGelaran.round(2);
                //         document.getElementById("unit_cons_actual_gelaran").value = unitVar.toLowerCase();
                //         // document.getElementById("unit_cons_ampar").value = unitVar.toUpperCase();

                //         calculateConsAmpar();
                //     }
                // }

            function calculateConsActualGelaran(unit, totalQtyFabric, totalKepalaKain, totalSisaTidakBisa, totalReject, totalSisaKain, totalPiping, totalShortRoll, totalLembar, totalTotalPemakaian, totalSambunganRoll) {
                let unitVar = unit;
                let totalQtyFabricVar = totalQtyFabric ? Number(totalQtyFabric) : 0;
                let totalKepalaKainVar = totalKepalaKain ? Number(totalKepalaKain) : 0;
                let totalSisaTidakBisaVar = totalSisaTidakBisa ? Number(totalSisaTidakBisa) : 0;
                let totalRejectVar = totalReject ? Number(totalReject) : 0;
                let totalSisaKainVar = totalSisaKain ? Number(totalSisaKain) : 0;
                let totalPipingVar = totalPiping ? Number(totalPiping) : 0;
                let totalShortRollVar = totalShortRoll ? Number(totalShortRoll) : 0;
                let totalLembarVar = totalLembar ? Number(totalLembar) : 0;
                let totalTotalPemakaianVar = totalTotalPemakaian ? Number(totalTotalPemakaian) : 0;

                let consActualGelaran = (totalLembar * totalRatio) > 0 ? (totalQtyFabricVar - totalSisaKainVar)/(totalLembarVar * totalRatio) : 0;
                let consActualGelaranShortRolless = (totalLembar * totalRatio) > 0 ? (totalQtyFabricVar - totalSisaKainVar + totalShortRollVar)/(totalLembarVar * totalRatio) : 0;

                document.getElementById('cons_actual_gelaran').value = Number(consActualGelaran).round(3);
                document.getElementById('cons_actual_gelaran_short_rolless').value = Number(consActualGelaranShortRolless).round(3);

                document.getElementById("unit_cons_actual_gelaran").value = unitVar.toLowerCase();
                document.getElementById("unit_cons_actual_gelaran_short_rolless").value = unitVar.toLowerCase();

                calculateConsAmpar();

                consUpRate();
            }

            function consUpRate() {
                let consActualGelaran = document.getElementById('cons_actual_gelaran').value;
                let unitConsActualGelaran = document.getElementById('unit_cons_actual_gelaran').value;

                let consActualGelaranShortRolless = document.getElementById('cons_actual_gelaran_short_rolless').value;
                let unitConsActualGelaranShortRolless = document.getElementById('unit_cons_actual_gelaran_short_rolless').value;

                let consWs = document.getElementById('cons_ws').value;
                let consMarker = document.getElementById('cons_marker').value;

                let consWsUpRate = 0;
                let consMarkerUpRate = 0;
                let consWsUpRateNoSr = 0;
                let consMarkerUpRateNoSr = 0;

                if ((unitConsActualGelaran == "YARD" || unitConsActualGelaran == "YRD") && (unitConsActualGelaranShortRolless == "YARD" || unitConsActualGelaranShortRolless == "YRD")) {
                    let consActualGelaranConverted = conversion(consActualGelaran, "METER", unitConsActualGelaran.toUpperCase());
                    let consActualGelaranShortRollessConverted = conversion(consActualGelaranShortRolless, "METER", unitConsActualGelaranShortRolless.toUpperCase());

                    consWsUpRate = (consActualGelaranConverted - consWs)/consWs * 100;
                    consMarkerUpRate = ((consActualGelaranConverted - consMarker)/consMarker) * 100;

                    consWsUpRateNoSr = ((consActualGelaranShortRollessConverted - consWs)/consWs) * 100;
                    consMarkerUpRateNoSr = ((consActualGelaranShortRollessConverted - consMarker)/consMarker) * 100;

                    // console.log("cons actual gelaran converted "+consActualGelaranConverted, consWs, consMarker, consWsUpRate, consMarkerUpRate);
                } else {
                    consWsUpRate = ((consActualGelaran - consWs)/consWs) * 100;
                    consMarkerUpRate = ((consActualGelaran - consMarker)/consMarker) * 100;

                    consWsUpRateNoSr = ((consActualGelaranShortRolless - consWs)/consWs) * 100;
                    consMarkerUpRateNoSr = ((consActualGelaranShortRolless - consMarker)/consMarker) * 100;

                    // console.log("cons actual gelaran "+consActualGelaran, consWs, consMarker);
                }

                document.getElementById('cons_ws_uprate').value = Number(consWsUpRate).round(2);
                document.getElementById('cons_marker_uprate').value = Number(consMarkerUpRate).round(2);
                document.getElementById('cons_ws_uprate_nosr').value = Number(consWsUpRateNoSr).round(2);
                document.getElementById('cons_marker_uprate_nosr').value = Number(consMarkerUpRateNoSr).round(2);
            }

            // -Get Cons. WS Data-
            function getNumberData() {
                return $.ajax({
                    url: '{{ route('get-number-form-cut-input') }}',
                    type: 'get',
                    data: {
                        act_costing_id: $("#act_costing_id").val(),
                        color: $("#color").val(),
                        panel: $("#panel").val(),
                    },
                    dataType: 'json',
                    success: function(res) {
                        if (res) {
                            let consWs = res.cons_ws;

                            document.getElementById("cons_ws").value = consWs;
                        }
                    }
                });
            }

            // -Check Form Cut Input Status-
            async function checkStatus() {
                $('#lost-time-card').CardWidget('collapse');

                checkLostTime(id);

                if (status == "PENGERJAAN FORM CUTTING") {
                    startProcessButton.classList.add("d-none");
                    nextProcessOneButton.classList.remove("d-none");

                    document.getElementById("lost-time-card").classList.remove("d-none");
                }

                if (status == "PENGERJAAN FORM CUTTING DETAIL") {
                    document.getElementById("lost-time-card").classList.remove("d-none");

                    startProcessButton.classList.add("d-none");
                    nextProcessOneButton.classList.add("d-none");

                    $('#header-data-card').CardWidget('collapse');
                    $('#detail-data-card').removeClass('d-none');
                    nextProcessTwoButton.classList.remove("d-none");
                }

                if (status == "PENGERJAAN FORM CUTTING SPREAD") {
                    document.getElementById("lost-time-card").classList.remove("d-none");

                    if ($("#status_sambungan").val() != "extension") {
                        document.getElementById("current_sambungan").setAttribute('readonly', true);
                        document.getElementById("current_sisa_gelaran").removeAttribute('readonly');
                    }

                    startProcessButton.classList.add("d-none");
                    nextProcessOneButton.classList.add("d-none");
                    nextProcessTwoButton.classList.add("d-none");
                    nextProcessThreeButton.classList.remove("d-none");

                    $('#header-data-card').CardWidget('collapse');
                    $('#detail-data-card').removeClass('d-none');
                    $('#detail-data-card').CardWidget('collapse');
                    $('#scan-qr-card').removeClass('d-none');

                    initScan();
                    getItemList();

                    checkSpreadingForm();

                    await getSummary()
                    if (summaryData != null && summaryData.length > 0) {
                        $('#spreading-form-card').removeClass("d-none");
                        $('#summary-card').removeClass("d-none");

                        finishProcessButton.classList.remove("d-none");
                    }
                }

                if (status == "SELESAI PENGERJAAN") {
                    document.getElementById("lost-time-card").classList.remove("d-none");

                    startProcessButton.classList.add("d-none");
                    nextProcessOneButton.classList.add("d-none");
                    nextProcessTwoButton.classList.add("d-none");
                    nextProcessThreeButton.classList.add("d-none");

                    $('#header-data-card').CardWidget('collapse');
                    $('#detail-data-card').removeClass('d-none');
                    $('#detail-data-card').CardWidget('collapse');
                    $('#scan-qr-card').removeClass('d-none');
                    $('#scan-qr-card').CardWidget('collapse');

                    nextProcessThreeButton.setAttribute("disabled", true);

                    await getSummary();
                    if (summaryData != null && summaryData.length > 0) {
                        $('#spreading-form-card').CardWidget("collapse");
                        $('#spreading-form-card').removeClass("d-none");
                        $('#summary-card').removeClass("d-none");

                        finishProcessButton.classList.remove("d-none");
                    }

                    lockFormCutInput();
                }

                if ($("#locked").val() > 0) {
                    Swal.close();

                    lockForm();

                    Swal.fire({
                        icon: 'error',
                        title: 'Form terkunci',
                        html: `
                            Harap laporkan kepada atasan untuk dapat melanjutkan
                            <input type='text' class='my-3 form-control form-control-sm' id='unlock_form_username' placeholder='Masukkan username...' onkeyup='unlockEnter(event)'>
                            <input type='password' class='my-3 form-control form-control-sm' id='unlock_form_password' placeholder='Masukkan password...' onkeyup='unlockEnter(event)'>
                            <button type='button' class='mb-3 btn btn-primary' id='submit_form_unlock_token' onclick='unlockForm()'>Lanjutkan Form</button>
                        `,
                        showCancelButton: false,
                        showConfirmButton: false,
                        allowOutsideClick: false,
                    });
                } else {
                    Swal.close();
                }
            }

            // -Clear General Form Value-
            function clearGeneralForm() {
                if (startTime.value == "" || startTime.value == null) {
                    startTime.value = "";
                }

                if (finishTime.value == "" || finishTime.value == null) {
                    finishTime.value = "";
                }
            }

            // -Lock General Form-
            function lockGeneralForm() {
                document.getElementById('shell').setAttribute('disabled', true);
                document.getElementById('p_act').setAttribute('readonly', true);
                document.getElementById('comma_act').setAttribute('readonly', true);
                document.getElementById('l_act').setAttribute('readonly', true);
                document.getElementById('cons_ws').setAttribute('readonly', true);
                document.getElementById('cons_act').setAttribute('readonly', true);
                document.getElementById('cons_pipping').setAttribute('readonly', true);
                document.getElementById('cons_ampar').setAttribute('readonly', true);
                document.getElementById('est_pipping').setAttribute('readonly', true);
                document.getElementById('est_kain').setAttribute('readonly', true);
                document.getElementById('operator').setAttribute('readonly', true);
                document.getElementById('unit_cons_actual_gelaran').setAttribute('readonly', true);
            }

            // -Switch Method-
            function switchMethod(element) {
                if (element.checked) {
                    toScanMethod();
                } else {
                    toItemMethod();
                }
            }

            function toItemMethod() {
                method = "item";

                document.getElementById("scan-method").classList.add('d-none');
                document.getElementById("to-scan").classList.add('d-none');

                document.getElementById("item-method").classList.remove('d-none');
                document.getElementById("to-item").classList.remove('d-none');
                $("#select_item").val("").trigger("change");

                clearQrCodeScanner();

                removeColorSpreading();

                location.href = "#scan-qr-card";
            }

            function toScanMethod() {
                method = "scan";

                document.getElementById("item-method").classList.add('d-none');
                document.getElementById("to-item").classList.add('d-none');

                document.getElementById("scan-method").classList.remove('d-none');
                document.getElementById("to-scan").classList.remove('d-none');
                $("#select_item").val("").trigger("change");

                initScan();

                addColorSpreading();

                location.href = "#scan-qr-card";
            }

            function addColorSpreading() {
                document.getElementById("current_id_item_label").classList.add("label-scan");
                document.getElementById("current_id_item").classList.add("border-scan");
                document.getElementById("current_lot_label").classList.add("label-scan");
                document.getElementById("current_lot").classList.add("border-scan");
                document.getElementById("current_roll_label").classList.add("label-scan");
                document.getElementById("current_roll").classList.add("border-scan");
                document.getElementById("current_roll_buyer_label").classList.add("label-scan");
                document.getElementById("current_roll_buyer").classList.add("border-scan");
                document.getElementById("current_qty_real_label").classList.add("label-scan");
                document.getElementById("current_qty_real").classList.add("border-scan");
                document.getElementById("current_unit").classList.add("border-scan");
                document.getElementById("current_qty_label").classList.add("label-calc");
                document.getElementById("current_qty").classList.add("border-calc");
                document.getElementById("current_unit_convert").classList.add("border-calc");
            }

            function removeColorSpreading() {
                document.getElementById("current_id_item_label").classList.remove("label-scan");
                document.getElementById("current_id_item").classList.remove("border-scan");
                document.getElementById("current_lot_label").classList.remove("label-scan");
                document.getElementById("current_lot").classList.remove("border-scan");
                document.getElementById("current_roll_label").classList.remove("label-scan");
                document.getElementById("current_roll").classList.remove("border-scan");
                document.getElementById("current_roll_buyer_label").classList.remove("label-scan");
                document.getElementById("current_roll_buyer").classList.remove("border-scan");
                document.getElementById("current_qty_real_label").classList.remove("label-scan");
                document.getElementById("current_qty_real").classList.remove("border-scan");
                document.getElementById("current_unit").classList.remove("border-scan");
                document.getElementById("current_qty_label").classList.remove("label-calc");
                document.getElementById("current_qty").classList.remove("border-calc");
                document.getElementById("current_unit_convert").classList.remove("border-calc");
            }

            // Get Item List Module :
            async function getItemList() {
                $("#select_item").prop("disabled", true);

                await $.ajax({
                    url: '{{ route('get-item-form-cut-input') }}',
                    type: 'get',
                    data: {
                        act_costing_id: $("#act_costing_id").val(),
                    },
                    dataType: 'json',
                    success: function(res) {
                        if (res) {
                            res.forEach((item) => {
                                let option = document.createElement("option");
                                option.text = item.itemdesc;
                                option.value = item.id_item;

                                document.getElementById("select_item").appendChild(option);
                            })
                        }
                    },
                });

                $("#select_item").prop("disabled", false);
            }

            function setSelectedItem(element) {
                currentScannedItem = null;

                if (element.value && element.value != "") {
                    document.getElementById("kode_barang").value = "";
                    document.getElementById("current_id_roll").value = "";

                    document.getElementById("id_item").value = element.value;
                    document.getElementById("detail_item").value = $("#select_item option:selected").text();

                    currentScannedItem = {"id_item": element.value, "detail_item": $("#select_item option:selected").text(), "unit": "METER"};
                }
            }

            function setCustomUnit(unit) {
                document.getElementById("current_unit").value = unit;

                let inputGroupUnit = document.getElementsByClassName("input-group-unit");
                let unitSimplified = unit != "KGM" ? "M" : "KG";

                if (unit == "KGM" || unit == "KG") {
                    document.getElementById("current_unit_awal").value = unit;
                    document.getElementById("current_sisa_gelaran_unit").value = unit;
                    document.getElementById("current_sambungan_unit").value = unit;

                    for (var i = 0; i < inputGroupUnit.length; i++) {
                        inputGroupUnit[i].innerText = unitSimplified;
                    }

                    document.getElementById("berat_amparan").classList.remove("d-none");
                    document.getElementById("th-berat-amparan").classList.remove("d-none");
                    document.getElementById("total-berat-amparan").classList.remove("d-none");
                } else {
                    document.getElementById("current_unit_awal").value = "METER";
                    document.getElementById("current_sisa_gelaran_unit").value = "METER";
                    document.getElementById("current_sambungan_unit").value = "METER";

                    for (var i = 0; i < inputGroupUnit.length; i++) {
                        inputGroupUnit[i].innerText = unitSimplified;
                    }

                    document.getElementById("berat_amparan").classList.add("d-none");
                    document.getElementById("th-berat-amparan").classList.add("d-none");
                    document.getElementById("total-berat-amparan").classList.add("d-none");
                }

                if (sisaGelaran > 0) {
                    let sambungan = calculateSambungan(sisaGelaran, unitSisaGelaran);

                    document.getElementById("current_sambungan").value = sambungan;
                    document.getElementById("current_total_pemakaian_roll").value = sambungan;

                    console.log(sisaGelaran, unitSisaGelaran);
                }
            }

        // Spreading Form Module :
            // Variable :
            var spreadingFormData = null;
            var sisaGelaran = null;
            var unitSisaGelaran = null;

        // Function :
            // -Check Spreading Form-
            function checkSpreadingForm() {
                let id = document.getElementById("id").value;
                let noForm = document.getElementById("no_form").value;
                let noMeja = document.getElementById("no_meja").value;

                $.ajax({
                    url: '{{ route('check-spreading-form-cut-input') }}/' + id + '/' + noForm + '/' + noMeja,
                    type: 'get',
                    dataType: 'json',
                    success: function(res) {
                        if (res) {
                            nextProcessThreeButton.classList.remove('d-none');

                            firstTimeRecordCondition();

                            if (res.count > 0) {
                                spreadingFormData = res.data;
                                sisaGelaran = res.sisaGelaran;
                                unitSisaGelaran = res.unitSisaGelaran;
                                method = res.data.metode ? res.data.metode : "scan";

                                setSpreadingForm(spreadingFormData, sisaGelaran, unitSisaGelaran);

                                checkTimeRecordLap(res.data.id);

                                checkSambungan(res.data.id);

                                document.getElementById("kode_barang").value = res.data.id_roll;
                                document.getElementById("id_item").value = res.data.id_item;
                                document.getElementById("color_act").value = res.data.color_act;
                                document.getElementById("detail_item").value = res.data.detail_item;

                                $('#spreading-form-card').CardWidget('expand');
                                $('#spreading-form-card').removeClass("d-none");
                            } else {
                                $('#spreading-form-card').CardWidget('collapse');
                            }
                        }
                    }
                });
            }

            // -Set Spreading Form-
            async function setSpreadingForm(data, sisaGelaran, unitSisaGelaran) {
                await lockItemSpreading();

                // if not an extension
                if (!(sisaGelaran)) {
                    await clearSpreadingForm();
                }

                // if the roll method is item
                if (method == "item") {
                    await openItemSpreading();

                    document.getElementById("current_unit").value = "METER";
                    document.getElementById("current_custom_unit").value = "METER";
                }

                console.log(data);

                // spreading form data set
                let convertedQty = await rollQtyConversion(data.qty, data.unit);

                data.id_roll ? document.getElementById("kode_barang").value = data.id_roll : '';
                data.id_item ? document.getElementById("id_item").value = data.id_item : '';
                data.detail_item ? document.getElementById("detail_item").value = data.detail_item : '';
                data.color_act ? document.getElementById("color_act").value = data.color_act : '';
                data.id_roll ? document.getElementById("current_id_roll").value = data.id_roll : '';
                data.group_roll ? document.getElementById("current_group").value = data.group_roll : '';
                data.id_item ? document.getElementById("current_id_item").value = data.id_item : '';
                data.lot ? document.getElementById("current_lot").value = data.lot : '';
                data.roll ? document.getElementById("current_roll").value = data.roll : '';
                data.roll_buyer ? document.getElementById("current_roll_buyer").value = data.roll_buyer : '';
                data.qty ? document.getElementById("current_qty").value = convertedQty : '';
                data.qty ? document.getElementById("current_qty_awal").value = (data.qty_in ? data.qty_in : data.qty) : '';
                data.qty ? document.getElementById("current_qty_real").value = data.qty : '';
                data.unit ? document.getElementById("current_unit").value = data.unit : '';
                data.unit ? document.getElementById("current_unit_awal").value = data.unit : '';
                data.unit ? document.getElementById("current_custom_unit").value = data.unit : '';
                data.unit ? document.getElementById("current_sisa_gelaran_unit").value = (data.unit != "KGM" ? "METER" : "KGM") : '';
                data.unit ? document.getElementById("current_sambungan_unit").value = (data.unit != "KGM" ? "METER" : "KGM") : '';
                data.sisa_gelaran ? document.getElementById("current_sisa_gelaran").value = data.sisa_gelaran : '';
                data.sambungan ? document.getElementById("current_sambungan").value = data.sambungan : '';
                data.est_amparan ? document.getElementById("current_est_amparan").value = data.est_amparan : '';
                data.lembar_gelaran ? document.getElementById("current_lembar_gelaran").value = data.lembar_gelaran : '';
                data.average_time ? document.getElementById("current_average_time").value = data.average_time : '';
                data.kepala_kain ? document.getElementById("current_kepala_kain").value = data.kepala_kain : '';
                data.sisa_tidak_bisa ? document.getElementById("current_sisa_tidak_bisa").value = data.sisa_tidak_bisa : '';
                data.reject ? document.getElementById("current_reject").value = data.reject : '';
                data.sisa_kain ? document.getElementById("current_sisa_kain").value = data.sisa_kain : '';
                data.total_pemakaian_roll ? document.getElementById("current_total_pemakaian_roll").value = data.total_pemakaian_roll : '';
                data.short_roll ? document.getElementById("current_short_roll").value = data.short_roll : '';
                data.piping ? document.getElementById("current_piping").value = data.piping : '';
                document.getElementById("current_berat_amparan").value = latestBerat;

                if (data.unit == "KGM" || data.unit == "KG") {
                    document.getElementById("berat_amparan").classList.remove("d-none");
                    document.getElementById("th-berat-amparan").classList.remove("d-none");
                    document.getElementById("total-berat-amparan").classList.remove("d-none");
                } else if (data.unit == "METER" || data.unit == "YARD" || data.unit == "YRD") {
                    document.getElementById("berat_amparan").classList.add("d-none");
                    document.getElementById("th-berat-amparan").classList.add("d-none");
                    document.getElementById("total-berat-amparan").classList.add("d-none");
                }

                // simplified unit name
                let unitSimplified = data.unit != "KGM" ? "M" : "KG";

                let inputGroupUnit = document.getElementsByClassName("input-group-unit");

                for (var i = 0; i < inputGroupUnit.length; i++) {
                    inputGroupUnit[i].innerText = unitSimplified;
                }

                if (method == "item") {
                    $("#current_custom_unit").val(latestUnit);
                    setCustomUnit(latestUnit);
                }

                // updating est ampar & ply progress bar
                calculateEstAmpar();

                updatePlyProgress();

                // if is an extension
                if (sisaGelaran > 0) {
                    // extension things
                    let estSambungan = calculateSambungan(sisaGelaran, unitSisaGelaran);

                    data.id_sambungan ? document.getElementById("id_sambungan").value = data.id_sambungan : '';
                    document.getElementById("status_sambungan").value = "extension";
                    document.getElementById("current_sambungan").value = estSambungan;

                    openExtension();

                    // set total pemakaian
                    document.getElementById("current_total_pemakaian_roll").value = document.getElementById("current_sambungan").value;

                // if not an extension
                } else {
                    nextProcessThreeButton.classList.add("d-none");

                    if ($("#status_sambungan").val() != "extension") {
                        lockExtension();
                    }
                }

                // if item is not yet scanned/selected
                if (!(data.id_item)) {
                    openScanItemForm();

                    // scan qr
                    $('#scan-qr-card').CardWidget('expand');
                    $('#spreading-form-card').CardWidget('collapse');
                    document.getElementById('kode_barang').focus();

                    // time record
                    firstTimeRecordCondition();

                // if item scanned/selected
                } else {
                    lockScanItemForm();

                    // scan qr
                    $('#scan-qr-card').CardWidget('collapse');
                    $('#spreading-form-card').CardWidget('expand');

                    // time record
                    openTimeRecordCondition();
                }

                if (latestStatus == 'extension complete') {
                    $('#current_kepala_kain').val(0);
                    $('#current_kepala_kain').prop('readonly', true);
                } else {
                    $('#current_kepala_kain').val(0);
                    $('#current_kepala_kain').prop('readonly', false);
                }
            }

            // -Clear Spreading Form-
            function clearSpreadingForm() {
                $('#spreading-form-card').CardWidget('collapse');

                lockItemSpreading();

                document.getElementById("current_id_item").setAttribute("readonly", true);
                document.getElementById("current_lot").setAttribute("readonly", true);
                document.getElementById("current_roll").setAttribute("readonly", true);
                document.getElementById("current_roll_buyer").setAttribute("readonly", true);
                document.getElementById("current_qty").setAttribute("readonly", true);
                document.getElementById("current_qty_real").setAttribute("readonly", true);

                document.getElementById("id_sambungan").value = "";
                document.getElementById("status_sambungan").value = "";
                document.getElementById("current_group").value = "";
                document.getElementById("current_id_item").value = "";
                document.getElementById("current_lot").value = "";
                document.getElementById("current_roll").value = "";
                document.getElementById("current_roll_buyer").value = "";
                document.getElementById("current_qty").value = "";
                document.getElementById("current_qty_real").value = "";
                document.getElementById("current_unit").value = "";
                document.getElementById("current_sisa_gelaran").value = 0;
                document.getElementById("current_sisa_gelaran_unit").value = "";
                document.getElementById("current_sambungan").value = 0;
                document.getElementById("current_sambungan_unit").value = "";
                document.getElementById("current_est_amparan").value = 0;
                document.getElementById("current_lembar_gelaran").value = 0;
                document.getElementById("current_average_time").value = "00:00";
                document.getElementById("current_kepala_kain").value = 0;
                document.getElementById("current_sisa_tidak_bisa").value = 0;
                document.getElementById("current_reject").value = 0;
                document.getElementById("current_sisa_kain").value = "";
                document.getElementById("current_pemakaian_lembar").value = 0;
                document.getElementById("current_total_pemakaian_roll").value = 0;
                document.getElementById("current_short_roll").value = 0;
                document.getElementById("current_short_roll_percentage").value = 0;
                document.getElementById("current_piping").value = 0;

                let inputGroupUnit = document.getElementsByClassName("input-group-unit");

                for (var i = 0; i < inputGroupUnit.length; i++) {
                    inputGroupUnit[i].innerText = "";
                }
            }

            // -Lock Extension input on Spreading Form-
            function lockExtension() {
                document.getElementById("current_sambungan").setAttribute('readonly', true);
                document.getElementById("current_sisa_gelaran").removeAttribute('readonly');

                document.getElementById("current_lembar_gelaran").removeAttribute('readonly');
                document.getElementById("current_lembar_gelaran").setAttribute('onkeyup', "calculatePemakaianLembar();calculateTotalPemakaian();calculateShortRoll(event);openStopTimeRecord();");
                document.getElementById("current_lembar_gelaran").setAttribute('onchange', "calculatePemakaianLembar();calculateTotalPemakaian();calculateShortRoll(event);openStopTimeRecord();");

                // showSambungan();
            }

            // -Open Extension input on Spreading Form-
            function openExtension() {
                document.getElementById("current_sambungan").removeAttribute('readonly');
                document.getElementById("current_sisa_gelaran").setAttribute('readonly', true);

                document.getElementById("current_lembar_gelaran").setAttribute('readonly', true);
                document.getElementById("current_lembar_gelaran").setAttribute('onkeyup', "calculatePemakaianLembar();calculateTotalPemakaian();calculateShortRoll(event);");
                document.getElementById("current_lembar_gelaran").setAttribute('onchange', "calculatePemakaianLembar();calculateTotalPemakaian();calculateShortRoll(event);");

                // hideSambungan();
            }

            // -Lock Item input on Spreading Form-
            function lockItemSpreading() {
                document.getElementById("current_id_item").setAttribute("readonly", true);
                document.getElementById("current_lot").setAttribute("readonly", true);
                document.getElementById("current_roll").setAttribute("readonly", true);
                document.getElementById("current_roll_buyer").setAttribute("readonly", true);
                document.getElementById("current_qty").setAttribute("readonly", true);
                document.getElementById("current_qty_real").setAttribute("readonly", true);

                document.getElementById("current_unit").classList.remove("d-none");
                document.getElementById("current_custom_unit").classList.add("d-none");
            }

            // -Open Item input on Spreading Form-
            function openItemSpreading() {
                // document.getElementById("current_id_item").removeAttribute("readonly");
                document.getElementById("current_lot").removeAttribute("readonly");
                document.getElementById("current_roll").removeAttribute("readonly");
                document.getElementById("current_roll_buyer").removeAttribute("readonly");
                document.getElementById("current_qty").removeAttribute("readonly");
                document.getElementById("current_qty_real").removeAttribute("readonly");

                document.getElementById("current_unit").classList.add("d-none");
                document.getElementById("current_custom_unit").classList.remove("d-none");
            }

            // -Lock Spreading Form-
            function lockSpreadingForm() {
                document.getElementById("current_group").setAttribute("readonly", true);
                document.getElementById("current_id_item").setAttribute("readonly", true);
                document.getElementById("current_lot").setAttribute("readonly", true);
                document.getElementById("current_roll").setAttribute("readonly", true);
                document.getElementById("current_roll_buyer").setAttribute("readonly", true);
                document.getElementById("current_qty").setAttribute("readonly", true);
                document.getElementById("current_unit").setAttribute("readonly", true);
                document.getElementById("current_sisa_gelaran").setAttribute("readonly", true);
                document.getElementById("current_sambungan").setAttribute("readonly", true);
                document.getElementById("current_est_amparan").setAttribute("readonly", true);
                document.getElementById("current_lembar_gelaran").setAttribute("readonly", true);
                document.getElementById("current_average_time").setAttribute("readonly", true);
                document.getElementById("current_kepala_kain").setAttribute("readonly", true);
                document.getElementById("current_sisa_tidak_bisa").setAttribute("readonly", true);
                document.getElementById("current_reject").setAttribute("readonly", true);
                document.getElementById("current_sisa_kain").setAttribute("readonly", true);
                document.getElementById("current_pemakaian_lembar").setAttribute("readonly", true);
                document.getElementById("current_total_pemakaian_roll").setAttribute("readonly", true);
                document.getElementById("current_short_roll").setAttribute("readonly", true);
                document.getElementById("current_short_roll_percentage").setAttribute("readonly", true);
                document.getElementById("current_piping").setAttribute("readonly", true);
            }

            // -Get Summary Data-
            function getSummary() {
                if (summaryData == null) {
                    let id = document.getElementById("id").value;
                    let noForm = document.getElementById("no_form").value;

                    return $.ajax({
                        url: '{{ route('get-time-form-cut-input') }}/' + id + '/' + noForm,
                        type: 'get',
                        dataType: 'json',
                        success: function(res) {
                            if (res) {
                                summaryData = res;
                                setSummary(summaryData);
                            }
                        }
                    });
                }
            }

            // -Set Summary Data-
            function setSummary(data) {
                if (totalScannedItem < 1) {
                    summaryData.forEach((data) => {
                        appendScannedItem(data)
                    });

                    updatePlyProgress();
                }
            }

            // -Update Ply Progress-
            function updatePlyProgress() {
                let currentLembar = Number($("#current_lembar_gelaran").val());
                let qtyPly = Number($("#qty_ply").val());

                document.getElementById("current_ply_progress_txt").innerText = (totalLembar + currentLembar) + "/" + qtyPly;
                document.getElementById("current_ply_progress").style.width = Number(qtyPly) > 0 ? (Number( totalLembar + currentLembar) / Number(qtyPly) * 100) + "%" : "0%";
            }

            // -Lock Form Cut Input-
            function lockFormCutInput() {
                lockProcessCondition();

                lockGeneralForm();

                lockScanItemForm();

                lockSpreadingForm();

                firstTimeRecordCondition();

                lockTimeRecord();

                firstLostTimeCondition();
            }

        // Scan QR Module :
            // Variable List :
            var html5QrcodeScanner = new Html5Qrcode("reader");
            var scannerInitialized = false;

        // Function List :
            // -Initialize Scanner-
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

                            clearQrCodeScanner();
                        };
                        const config = { fps: 10, qrbox: { width: 250, height: 250 } };

                        await html5QrcodeScanner.start({ facingMode: "environment" }, config, qrCodeSuccessCallback);
                    }
                }
            }

            async function clearQrCodeScanner() {
                console.log(html5QrcodeScanner, html5QrcodeScanner.getState());
                if (html5QrcodeScanner && (html5QrcodeScanner.isScanning)) {
                    await html5QrcodeScanner.stop();
                    await html5QrcodeScanner.clear();
                }
            }

            async function refreshScan() {
                await clearQrCodeScanner();
                await initScan();
            }

            // --Clear Scan Item Form--
            function clearScanItemForm() {
                $("#kode_barang").val("");
                $("#id_item").val("");
                $("#detail_item").val("");
                $("#color_act").val("");
            }

            // --Lock Scan Item Form then Clear Scanner--
            function lockScanItemForm() {
                document.getElementById("kode_barang").setAttribute("readonly", true);
                document.getElementById("color_act").setAttribute("disabled", true);
                document.getElementById("get-button").setAttribute("disabled", true);
                document.getElementById("scan-button").setAttribute("disabled", true);
                document.getElementById("switch-method").setAttribute("disabled", true);
                document.getElementById("reader").classList.add("d-none");

                clearQrCodeScanner();
            }

            // --Open Scan Item Form then Open Scanner--
            function openScanItemForm() {
                if (status != "SELESAI PENGERJAAN") {

                    document.getElementById("kode_barang").removeAttribute("readonly");
                    document.getElementById("color_act").removeAttribute("disabled");
                    document.getElementById("get-button").removeAttribute("disabled");
                    document.getElementById("scan-button").removeAttribute("disabled");
                    document.getElementById("switch-method").removeAttribute("disabled");
                    document.getElementById("reader").classList.remove("d-none");

                    initScan();
                }
            }

        // Scanned Item Module :
            // Variable List :
            var scannedItemTable = document.getElementById("scannedItemTable");
            var scannedItemTableTbody = scannedItemTable.getElementsByTagName("tbody")[0];
            var totalRow = 0;
            var totalScannedItem = 0;
            var totalSisaGelaran = 0;
            var totalSambungan = 0;
            var totalSambunganRoll = 0;
            var totalEstAmparan = 0;
            var totalAverageTime = 0;
            var totalKepalaKain = 0;
            var totalSisaTidakBisa = 0;
            var totalReject = 0;
            var totalSisaKain = 0;
            var totalPemakaianLembar = 0;
            var totalTotalPemakaian = 0;
            var totalShortRoll = 0;
            var totalShortRollPercentage = 0;
            var totalLembar = 0;
            var totalPiping = 0;
            var totalQtyFabric = 0;
            var totalBeratAmparan = 0;

            var latestKepalaKain = 0;
            var latestSisaTidakBisa = 0;
            var latestReject = 0;
            var latestSisaKain = 0;
            var latestPiping = 0;
            var latestSambungan = 0;
            var latestSambunganRoll = 0;
            var latestLembarSambunganRoll = 0;
            var latestPemakaianLembar = 0;
            var latestTotalPemakaian = 0;
            var latestShortRoll = 0;

            var latestStatus = "";
            var latestQty = 0;
            var latestUnit = "";
            var latestBerat = 0;

        // Function List :
            // -Fetch Scanned Item Data-
            function fetchScan() {
                let kodeBarang = document.getElementById('kode_barang').value;

                getScannedItem(kodeBarang);
            }

            // -Get Scanned Item Data-
            function getScannedItem(id) {
                document.getElementById("loading").classList.remove("d-none");

                document.getElementById("id_item").value = "";
                document.getElementById("detail_item").value = "";
                document.getElementById("color_act").value = "";

                if (isNotNull(id)) {
                    return $.ajax({
                        url: '{{ route('get-scanned-form-cut-input') }}/' + id,
                        type: 'get',
                        dataType: 'json',
                        success: function(res) {
                            console.log(res);

                            if (typeof res === 'object' && res !== null) {
                                if (res.qty > 0) {
                                    if (totalScannedItem > 0) {
                                        // if (res.unit.toLowerCase() != ($("#unit_cons_actual_gelaran").val()).toLowerCase()) {
                                        //     Swal.fire({
                                        //         icon: 'error',
                                        //         title: 'Gagal',
                                        //         text: 'Unit tidak sesuai',
                                        //         showCancelButton: false,
                                        //         showConfirmButton: true,
                                        //         confirmButtonText: 'Oke',
                                        //     });
                                        // } else {
                                        //     currentScannedItem = res;

                                        //     document.getElementById("id_item").value = res.id_item;
                                        //     document.getElementById("detail_item").value = res.detail_item;
                                        // }

                                        currentScannedItem = res;

                                        document.getElementById("id_item").value = res.id_item;
                                        document.getElementById("detail_item").value = res.detail_item;
                                    } else {
                                        currentScannedItem = res;

                                        document.getElementById("id_item").value = res.id_item;
                                        document.getElementById("detail_item").value = res.detail_item;
                                    }
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Gagal',
                                        text: 'Qty sudah habis.',
                                        showCancelButton: false,
                                        showConfirmButton: true,
                                        confirmButtonText: 'Oke',
                                    });
                                }
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

                return Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Item tidak ditemukan',
                    showCancelButton: false,
                    showConfirmButton: true,
                    confirmButtonText: 'Oke',
                });
            }

            // -Append Scanned Item to Summary Table-
            function appendScannedItem(data) {
                totalLembar += Number(data.lembar_gelaran);
                latestStatus != 'extension complete' ? totalQtyFabric += Number(data.qty) : '';
                latestUnit = data.unit;
                latestBerat = data.berat_amparan;

                let tr = document.createElement('tr');
                let td1 = document.createElement('td');
                let td2 = document.createElement('td');
                let td3 = document.createElement('td');
                let td4 = document.createElement('td');
                let td5 = document.createElement('td');
                let td6 = document.createElement('td');
                let td7 = document.createElement('td');
                let td8 = document.createElement('td');
                let td9 = document.createElement('td');
                let td10 = document.createElement('td');
                let td11 = document.createElement('td');
                let td12 = document.createElement('td');
                let td13 = document.createElement('td');
                let td14 = document.createElement('td');
                let td15 = document.createElement('td');
                let td16 = document.createElement('td');
                let td17 = document.createElement('td');
                let td18 = document.createElement('td');
                let td19 = document.createElement('td');
                let td20 = document.createElement('td');
                let td21 = document.createElement('td');
                let td22 = document.createElement('td');
                let td23 = document.createElement('td');
                let td24 = document.createElement('td');
                let td25 = document.createElement('td');

                if (latestStatus != 'need extension') {
                    td1.innerHTML = (latestStatus != 'need extension' ? totalScannedItem + 1 : '');
                    td2.innerHTML = data.group_roll ? data.group_roll : '-';
                    td3.innerHTML = data.group_stocker ? data.group_stocker : '-';
                    td4.innerHTML = data.id_roll ? data.id_roll : '-';
                    td5.innerHTML = data.id_item ? data.id_item : '-';
                    td6.innerHTML = data.lot ? data.lot : '-';
                    td7.innerHTML = data.roll_buyer ? data.roll_buyer : '-';
                    td8.innerHTML = data.qty_awal > data.qty ? data.qty_awal : (latestStatus != 'extension complete' ? data.qty : latestQty);
                    td9.innerHTML = (latestStatus != 'extension complete' ? data.qty : latestQty);
                    td10.innerHTML = data.unit ? data.unit : '-';
                    td11.innerHTML = data.sisa_gelaran ? data.sisa_gelaran : 0;
                    td12.innerHTML = (latestStatus != 'extension complete' ? 0 : (latestSambungan ? latestSambungan : 0)).round(2);
                    td13.innerHTML = (latestStatus != 'extension complete' ? (data.sambungan_roll ? data.sambungan_roll : 0) : ((data.sambungan_roll ? data.sambungan_roll : 0)+latestSambunganRoll)).round(2);
                    td14.innerHTML = data.est_amparan ? data.est_amparan : 0;
                    td15.innerHTML = data.lembar_gelaran ? data.lembar_gelaran : '-';
                    td16.innerHTML = data.average_time ? data.average_time : 0;
                    td17.innerHTML = (latestStatus != 'extension complete' ? (data.kepala_kain ? data.kepala_kain : 0) : Number(data.kepala_kain ? data.kepala_kain : 0)+Number(latestKepalaKain)).round(2);
                    td18.innerHTML = (latestStatus != 'extension complete' ? (data.sisa_tidak_bisa ? data.sisa_tidak_bisa : 0) : Number(data.sisa_tidak_bisa ? data.sisa_tidak_bisa : 0)+Number(latestSisaTidakBisa)).round(2);
                    td19.innerHTML = (latestStatus != 'extension complete' ? (data.reject ? data.reject : 0) : Number(data.reject ? data.reject : 0)+Number(latestReject)).round(2);
                    td20.innerHTML = (latestStatus != 'extension complete' ? (data.piping ? data.piping : 0) : Number(data.piping ? data.piping : 0)+Number(latestPiping)).round(2);
                    td21.innerHTML = (latestStatus != 'extension complete' ? (data.sisa_kain ? data.sisa_kain : 0) : Number(data.sisa_kain ? data.sisa_kain : 0)+Number(latestSisaKain)).round(2);
                    td22.innerHTML = (latestStatus != 'extension complete' ? (data.pemakaian_lembar ? data.pemakaian_lembar : 0) : Number(data.pemakaian_lembar ? data.pemakaian_lembar : 0)+Number(latestPemakaianLembar)).round(2);
                    td23.innerHTML = (latestStatus != 'extension complete' ? (data.total_pemakaian_roll ? data.total_pemakaian_roll : 0) : Number(data.total_pemakaian_roll ? data.total_pemakaian_roll : 0)+Number(latestTotalPemakaian)).round(2);
                    td24.innerHTML = (latestStatus != 'extension complete' ? Number(Number((data.total_pemakaian_roll ? data.total_pemakaian_roll : 0) + ( data.sisa_kain ? data.sisa_kain : 0 )).round(2) - ( data.qty ? data.qty : 0 )).round(2) : Number(Number((data.total_pemakaian_roll ? data.total_pemakaian_roll : 0) + (latestTotalPemakaian ? latestTotalPemakaian : 0) + ( data.sisa_kain ? data.sisa_kain : 0 )).round(2) - ( latestQty ? latestQty : 0 )).round(2));
                    td25.innerHTML = (latestStatus != 'extension complete' ? Number((Number((data.total_pemakaian_roll ? data.total_pemakaian_roll : 0) + ( data.sisa_kain ? data.sisa_kain : 0 )).round(2) - ( data.qty ? data.qty : 0 )) / ( data.qty ? data.qty : 0 ) * 100 ).round(2) : Number((Number((data.total_pemakaian_roll ? data.total_pemakaian_roll : 0 )  + (latestTotalPemakaian ? latestTotalPemakaian : 0) + ( data.sisa_kain ? data.sisa_kain : 0 )).round(2) - ( latestQty ? latestQty : 0 )) / ( latestQty ? latestQty : 0 ) * 100).round(2));
                } else {
                    td1.innerHTML = '';
                    td15.innerHTML = data.lembar_gelaran ? data.lembar_gelaran : '';
                }

                tr.appendChild(td1);
                tr.appendChild(td2);
                tr.appendChild(td3);
                tr.appendChild(td4);
                tr.appendChild(td5);
                tr.appendChild(td6);
                tr.appendChild(td7);
                tr.appendChild(td8);
                tr.appendChild(td9);
                tr.appendChild(td10);
                tr.appendChild(td11);
                tr.appendChild(td12);
                tr.appendChild(td13);
                tr.appendChild(td14);
                tr.appendChild(td15);
                tr.appendChild(td16);
                tr.appendChild(td17);
                tr.appendChild(td18);
                tr.appendChild(td19);
                tr.appendChild(td20);
                tr.appendChild(td21);
                tr.appendChild(td22);
                tr.appendChild(td23);
                tr.appendChild(td24);
                tr.appendChild(td25);

                if (latestUnit == "KGM" || latestUnit == "KG") {
                    let td26 = document.createElement('td');
                    td26.innerHTML = latestStatus != 'need extension' ? (data.berat_amparan ? data.berat_amparan : '-') : '';
                    tr.appendChild(td26);

                    document.getElementById("th-berat-amparan").classList.remove("d-none");
                    document.getElementById("total-berat-amparan").classList.remove("d-none");
                } else {

                    document.getElementById("th-berat-amparan").classList.add("d-none");
                    document.getElementById("total-berat-amparan").classList.add("d-none");
                }

                scannedItemTableTbody.appendChild(tr);

                totalRow++;
                latestStatus != 'need extension' ? totalScannedItem++ : '';

                totalSisaGelaran += Number(data.sisa_gelaran);
                totalSambungan += Number(data.sambungan);
                totalSambunganRoll += Number(data.sambungan_roll);
                latestStatus != 'extension complete' ? totalEstAmparan += Number(data.est_amparan) : '';
                totalAverageTime += (Number(data.average_time.slice(0, 2)) * 60) + Number(data.average_time.slice(3, 5));
                totalKepalaKain += Number(data.kepala_kain);
                totalSisaTidakBisa += Number(data.sisa_tidak_bisa);
                totalReject += Number(data.reject);
                totalPiping += Number(data.piping);
                totalSisaKain += Number(data.sisa_kain);
                totalPemakaianLembar += Number(data.pemakaian_lembar);
                totalTotalPemakaian += Number(data.total_pemakaian_roll);
                latestStatus != 'extension complete' ? totalBeratAmparan += Number(data.berat_amparan) : '';
                Number(data.short_roll) < 0 ? totalShortRoll += (latestStatus != 'extension complete' ? Number((data.total_pemakaian_roll ? data.total_pemakaian_roll : 0) + ( data.sisa_kain ? data.sisa_kain : 0 ) - ( data.qty ? data.qty : 0 )).round(2) : Number((data.total_pemakaian_roll ? data.total_pemakaian_roll : 0) + (latestTotalPemakaian ? latestTotalPemakaian : 0) + ( data.sisa_kain ? data.sisa_kain : 0 ) - ( latestQty ? latestQty : 0 )).round(2)) : "";
                Number(data.short_roll) < 0 ? totalShortRollPercentage += (latestStatus != 'extension complete' ? Number(((data.total_pemakaian_roll ? data.total_pemakaian_roll : 0) + ( data.sisa_kain ? data.sisa_kain : 0 ) - ( data.qty ? data.qty : 0 )) / ( data.qty ? data.qty : 0 ) * 100 ).round(2) : Number(((data.total_pemakaian_roll ? data.total_pemakaian_roll : 0 )  + (latestTotalPemakaian ? latestTotalPemakaian : 0) + ( data.sisa_kain ? data.sisa_kain : 0 ) - ( latestQty ? latestQty : 0 )) / ( latestQty ? latestQty : 0 ) * 100).round(2)) : 0;

                let averageTotalAverageTime = totalAverageTime / totalRow;
                let averageTotalAverageTimeMinute = averageTotalAverageTime.round(0) >= 60 ? pad((averageTotalAverageTime.round(0) / 60).round(0)) : pad(0);
                let averageTotalAverageTimeSecond = averageTotalAverageTime.round(0) >= 60 ? pad((averageTotalAverageTime.round(0) % 60).round(0)) : pad(averageTotalAverageTime.round(0));

                document.getElementById("total-qty").innerText = Number(totalQtyFabric).round(2);
                document.getElementById("total-unit").innerText = latestUnit;
                document.getElementById("total-sisa-gelaran").innerText = Number(totalSisaGelaran).round(2);
                document.getElementById("total-sambungan").innerText = Number(totalSambungan).round(2);
                document.getElementById("total-sambungan-roll").innerText = Number(totalSambunganRoll).round(2);
                document.getElementById("total-est-amparan").innerText = Number(totalEstAmparan).round(2);
                document.getElementById("total-lembar").innerText = Number(totalLembar).round(2);
                document.getElementById("total-average-time").innerText = (averageTotalAverageTimeMinute + ":" +averageTotalAverageTimeSecond);
                document.getElementById("total-kepala-kain").innerText = Number(totalKepalaKain).round(2);
                document.getElementById("total-sisa-tidak-bisa").innerText = Number(totalSisaTidakBisa).round(2);
                document.getElementById("total-reject").innerText = Number(totalReject).round(2);
                document.getElementById("total-sisa-kain").innerText = Number(totalSisaKain).round(2);
                document.getElementById("total-pemakaian-lembar").innerText = Number(totalPemakaianLembar).round(2);
                document.getElementById("total-total-pemakaian").innerText = Number(totalTotalPemakaian).round(2);
                document.getElementById("total-short-roll").innerText = Number(totalShortRoll).round(2);
                document.getElementById("total-short-roll-percentage").innerText = Number(totalShortRollPercentage).round(2);
                document.getElementById("total-piping").innerText = Number(totalPiping).round(2);
                document.getElementById("total-berat-amparan").innerText = Number(totalBeratAmparan).round(3);

                calculateConsActualGelaran(latestUnit, totalQtyFabric, totalKepalaKain, totalSisaTidakBisa, totalReject, totalSisaKain, totalPiping, totalShortRoll, totalLembar, totalTotalPemakaian);

                latestKepalaKain = Number(data.kepala_kain);
                latestSisaTidakBisa = Number(data.sisa_tidak_bisa);
                latestReject = Number(data.reject);
                latestPiping = Number(data.piping);
                latestSambungan = Number(data.sambungan);
                latestSambunganRoll = Number(data.sambungan_roll);
                latestLembarSambunganRoll = Number(data.lembar_sambungan);
                latestPemakaianLembar = Number(data.pemakaian_lembar);
                latestTotalPemakaian = Number(data.total_pemakaian_roll);
                latestShortRoll = Number(data.short_roll);

                latestStatus = data.status;
                latestQty = Number(data.qty);
                latestUnit = data.unit;
            }

        // Time Record Module :
            // Variable List :
                // Button Elements
                var startLapButton = document.getElementById("startLapButton");
                var nextLapButton = document.getElementById("nextLapButton");
                var stopLapButton = document.getElementById("stopLapButton");

                // Time Elements
                var minutes = document.getElementById("minutes");
                var seconds = document.getElementById("seconds");

                // Table Elements
                var timeRecordTable = document.getElementById('timeRecordTable');
                var timeRecordTableTbody = timeRecordTable.getElementsByTagName("tbody")[0];

                // Calculate Things
                var lap = 0;
                var totalSeconds = 0;
                var summarySeconds = 0;
                var averageSeconds = 0;
                var timeRecordInterval = 0;

                // Initialize Time Elements
                seconds.value = pad(totalSeconds % 60);
                minutes.value = pad(parseInt(totalSeconds / 60));

        // Function List :
            // -Time Record-
            function checkTimeRecordLap(detailId) {
                $.ajax({
                    url: '{{ route('check-time-record-form-cut-input') }}/' + detailId,
                    type: 'get',
                    dataType: 'json',
                    success: function(res) {
                        if (res.count > 0) {
                            setTimeRecordLap(res.data);
                        }
                    }
                });
            }

            function setTimeRecordLap(data) {
                data.forEach((element, index, array) => {
                    let time = element.waktu.split(":");
                    let minutesData = Number(time[0]) * 60;
                    let secondsData = Number(time[1]);

                    summarySeconds += (minutesData + secondsData);
                    lap++;

                    if (index == (array.length - 1)) {
                        averageSeconds = (parseFloat(summarySeconds) / parseFloat(lap)).round(0);

                        $("#current_lembar_gelaran").val(lap).trigger('change');
                        $("#current_average_time").val((pad(parseInt(averageSeconds / 60))) + ':' + (pad(averageSeconds % 60)))
                    }

                    let tr = document.createElement('tr');
                    let td1 = document.createElement('td');
                    let td2 = document.createElement('td');
                    let td3 = document.createElement('td');
                    td1.innerHTML = lap;
                    td2.innerHTML = element.waktu;
                    td3.classList.add('d-none');
                    td3.innerHTML = `<input type='hidden' name="time_record[` + lap + `]" value="` + element.waktu + `" />`;
                    tr.appendChild(td1);
                    tr.appendChild(td2);
                    tr.appendChild(td3);

                    timeRecordTableTbody.prepend(tr);
                });

                if (data.length > 0) {
                    stopLapButton.disabled = false;
                }
            }

            // -Set Time-
            function setTime() {
                ++totalSeconds;
                seconds.value = pad(totalSeconds % 60);
                minutes.value = pad(parseInt(totalSeconds / 60));
            }

            // -Start Time Record-
            function startTimeRecord() {
                if (lostInterval) {
                    addNewLostTime();
                }

                timeRecordInterval = setInterval(setTime, 999);

                startLapButton.classList.add("d-none")
                nextLapButton.classList.remove('d-none');

                openLapTimeRecordCondition();

                if ($("#status_sambungan").val() != "extension") {
                    storeThisTimeRecord();
                }
            }

            // -Next Lap Time Record-
            async function addNewTimeRecord(data = null) {
                if ($("#status_sambungan").val() == "extension") {
                    pauseTimeRecordButtons();

                    summarySeconds += totalSeconds;
                    totalSeconds = 0;
                    lap++;

                    averageSeconds = (parseFloat(summarySeconds) / parseFloat(lap)).round(0);

                    $("#current_lembar_gelaran").val(lap).trigger('change');
                    $("#current_average_time").val((pad(parseInt(averageSeconds / 60))) + ':' + (pad(averageSeconds % 60)))

                    let tr = document.createElement('tr');
                    let td1 = document.createElement('td');
                    let td2 = document.createElement('td');
                    let td3 = document.createElement('td');
                    td1.innerHTML = lap;
                    td2.innerHTML = minutes.value + ':' + seconds.value;
                    td3.classList.add('d-none');
                    td3.innerHTML = `<input type='hidden' name="time_record[` + lap + `]" value="` + minutes.value + ':' +seconds.value + `" />`;
                    tr.appendChild(td1);
                    tr.appendChild(td2);
                    tr.appendChild(td3);

                    timeRecordTableTbody.prepend(tr);

                    stopLapButton.disabled = false;

                    if (!(await stopTimeRecord())) {
                        resetTimeRecord();
                    }
                } else {
                    pauseTimeRecordButtons();

                    summarySeconds += totalSeconds;
                    totalSeconds = 0;
                    lap++;

                    averageSeconds = (parseFloat(summarySeconds) / parseFloat(lap)).round(0);

                    $("#current_lembar_gelaran").val(lap).trigger('change');
                    $("#current_average_time").val((pad(parseInt(averageSeconds / 60))) + ':' + (pad(averageSeconds % 60)))

                    let tr = document.createElement('tr');
                    let td1 = document.createElement('td');
                    let td2 = document.createElement('td');
                    let td3 = document.createElement('td');
                    td1.innerHTML = lap;
                    td2.innerHTML = minutes.value + ':' + seconds.value;
                    td3.classList.add('d-none');
                    td3.innerHTML = `<input type='hidden' name="time_record[` + lap + `]" value="` + minutes.value + ':' +seconds.value + `" />`;
                    tr.appendChild(td1);
                    tr.appendChild(td2);
                    tr.appendChild(td3);

                    timeRecordTableTbody.prepend(tr);

                    await storeThisTimeRecord();

                    stopLapButton.disabled = false;
                }

                updatePlyProgress();
            }

            function openStopTimeRecord() {
                let lembarGelaran = document.getElementById('current_lembar_gelaran').value;

                if (lembarGelaran > 0) {
                    stopLapButton.disabled = false;
                } else {
                    stopLapButton.disabled = true;
                }
            }

            // -Stop Time Record-
            async function stopTimeRecord() {
                backToProcessThree();
            }

            // -Reset Time Record-
            function resetTimeRecord() {
                clearTimeout(timeRecordInterval);

                summarySeconds = 0;
                totalSeconds = 0;
                timeRecordInterval = 0;

                seconds.value = 00;
                minutes.value = 00;
                lap = 0;

                startLapButton.classList.remove('d-none');
                nextLapButton.classList.add('d-none');

                timeRecordTableTbody.innerHTML = "";

                $("#switch-method").prop("checked", true).trigger("change");
            }

            function failTimeRecord() {
                clearTimeout(timeRecordInterval);

                summarySeconds = 0;
                totalSeconds = 0;
                timeRecordInterval = 0;

                seconds.value = 00;
                minutes.value = 00;
                lap = 0;

                timeRecordTableTbody.innerHTML = "";

                $("#switch-method").prop("checked", true).trigger("change");
            }

            // Pause Buttons
            function pauseTimeRecordButtons() {
                startLapButton.disabled = true;
                nextLapButton.disabled = true;
                setTimeout(function() {
                    startLapButton.disabled = false;
                    nextLapButton.disabled = false;

                    nextLapButton.focus();
                }, 1500);
            }

            // Conditions :
            function firstTimeRecordCondition() {
                startLapButton.disabled = true;
                nextLapButton.disabled = true;
                stopLapButton.disabled = true;
                finishProcessButton.disabled = false;

                finishProcessButton.classList.remove("d-none");

                openScanItemForm();
            }

            function openTimeRecordCondition() {
                startLapButton.disabled = false;
                nextLapButton.disabled = true;
                stopLapButton.disabled = true;

                lockScanItemForm();
            }

            function openLapTimeRecordCondition() {
                startLapButton.disabled = true;
                nextLapButton.disabled = false;

                pauseTimeRecordButtons();
            }

            function nextTimeRecordCondition() {
                startLapButton.disabled = true;
                nextLapButton.disabled = true;
                stopLapButton.disabled = true;
                finishProcessButton.disabled = false;
            }

            function lockTimeRecord() {
                finishProcessButton.disabled = true;
                finishProcessButton.innerHTML = "PENGERJAAN TELAH DISELESAIKAN";
            }

            // -Disable Time Record-
            function disableTimeRecord() {
                startLapButton.disabled = true;
                nextLapButton.disabled = true;

                clearTimeout(timeRecordInterval);
            }

            // -Enable Time Record-
            function enableTimeRecord() {
                startLapButton.disabled = false;
                nextLapButton.disabled = false;

                timeRecordInterval = setInterval(setTime, 999);
            }

        // Lost Time Module :
            // Variable List :
                // Button Elements
                var startLostButton = document.getElementById("startLostButton");
                var nextLostButton = document.getElementById("nextLostButton");

                // Time Elements
                var lostMinutes = document.getElementById("lostMinutes");
                var lostSeconds = document.getElementById("lostSeconds");

                // Table Elements
                var lostTimeTable = document.getElementById('lostTimeTable');
                var lostTimeTableTbody = lostTimeTable.getElementsByTagName("tbody")[0];

                // Calculate Things
                var lostTime = 0;
                var totalLostSeconds = 0;
                var summaryLostSeconds = 0;
                var averageLostSeconds = 0;
                var lostInterval = 0;

                // Status
                var pausedTimeRecord = false;

                // Initialize Time Elements
                lostSeconds.value = pad(totalLostSeconds % 60);
                lostMinutes.value = pad(parseInt(totalLostSeconds / 60));

            // Function List :
                // -Time Record-
                function checkLostTime(id) {
                    $.ajax({
                        url: '{{ route('check-lost-form-cut-input') }}/' + id,
                        type: 'get',
                        dataType: 'json',
                        success: function(res) {
                            openLostTimeCondition();

                            if (res.count > 0) {
                                setLostTime(res.data);
                            }
                        }
                    });
                }

                async function setLostTime(data) {
                    await data.forEach((element, index, array) => {
                        let time = element.waktu.split(":");
                        let minutesData = Number(time[0]) * 60;
                        let secondsData = Number(time[1]);

                        lostTime++;

                        let tr = document.createElement('tr');
                        let td1 = document.createElement('td');
                        let td2 = document.createElement('td');
                        let td3 = document.createElement('td');
                        td1.innerHTML = lostTime;
                        td2.innerHTML = element.waktu;
                        td3.classList.add('d-none');
                        td3.innerHTML = `<input type='hidden' name="lost_time[` + lostTime +
                            `]" value="` + element
                            .waktu + `" />`;
                        tr.appendChild(td1);
                        tr.appendChild(td2);
                        tr.appendChild(td3);

                        lostTimeTableTbody.prepend(tr);
                    });

                    document.getElementById("current_lost_time").value = lostTime;
                }

                // -Set Time-
                function setTimeLost() {
                    ++totalLostSeconds;
                    lostSeconds.value = pad(totalLostSeconds % 60);
                    lostMinutes.value = pad(parseInt(totalLostSeconds / 60));
                }

                // -Start Time Record-
                function startLostTime() {
                    pausedTimeRecord = false;

                    if (timeRecordInterval) {
                        disableTimeRecord();

                        pausedTimeRecord = true;
                    }

                    pauseLostTimeButtons();

                    lostInterval = setInterval(setTimeLost, 999);

                    startLostButton.classList.add("d-none")
                    nextLostButton.classList.remove('d-none');
                    nextLostButton.focus();

                    openLostTimeCondition();
                }

                // -Next Lap Time Record-
                async function addNewLostTime() {
                    pauseLostTimeButtons();

                    totalLostSeconds = 0;
                    lostTime++;

                    document.getElementById("current_lost_time").value = lostTime;

                    let tr = document.createElement('tr');
                    let td1 = document.createElement('td');
                    let td2 = document.createElement('td');
                    let td3 = document.createElement('td');
                    td1.innerHTML = lostTime;
                    td2.innerHTML = lostMinutes.value + ':' + lostSeconds.value;
                    td3.classList.add('d-none');
                    td3.innerHTML = `<input type='hidden' name="lost_time[` + lostTime + `]" value="` +
                        lostMinutes.value +
                        ':' + lostSeconds.value + `" />`;
                    tr.appendChild(td1);
                    tr.appendChild(td2);
                    tr.appendChild(td3);

                    lostTimeTableTbody.prepend(tr);

                    console.log(lostTime);

                    stopLostTime();

                    $('#lost-time-card').CardWidget('collapse');
                }

                // -Stop Time Record-
                async function stopLostTime() {
                    if (pausedTimeRecord) {
                        enableTimeRecord();
                    }

                    pauseLostTimeButtons();

                    clearTimeout(lostInterval);

                    totalLostSeconds = 0;
                    lostInterval = 0;

                    lostSeconds.value = "00";
                    lostMinutes.value = "00";

                    startLostButton.classList.remove('d-none');
                    nextLostButton.classList.add('d-none');

                    storeLostTime(id);
                }

                // Pause Buttons
                function pauseLostTimeButtons(disables = []) {
                    startLostButton.disabled = true;
                    nextLostButton.disabled = true;
                    setTimeout(function() {
                        startLostButton.disabled = false;
                        nextLostButton.disabled = false;
                    }, 1500);
                }

                // Store Lost Time
                function storeLostTime(id) {
                    let lostTimeForm = new FormData(document.getElementById('lost-time-form'));

                    $.ajax({
                        url: '{{ route('store-lost-form-cut-input') }}/' + id,
                        type: 'post',
                        processData: false,
                        contentType: false,
                        data: lostTimeForm,
                        success: function(res) {
                            if (res) {
                                console.log(res);
                            }
                        }
                    });
                }

                // Conditions :
                function firstLostTimeCondition() {
                    startLostButton.disabled = true;
                    nextLostButton.disabled = true;
                }

                function openLostTimeCondition() {
                    startLostButton.disabled = false;
                    nextLostButton.disabled = true;
                }

                function openLapLostTimeCondition() {
                    startLostButton.disabled = true;
                    nextLostButton.disabled = false;
                }

                function nextLostTimeCondition() {
                    startLostButton.disabled = true;
                    nextLostButton.disabled = true;
                }

        // Sambungan Module
            var sambunganSection = document.getElementById("sambungan-section");
            var totalSambunganSection = document.getElementById("total-sambungan-section");
            var jumlahSambungan = document.getElementById("current_jumlah_sambungan");

            function showSambungan() {
                sambunganSection.classList.remove('d-none');
                totalSambunganSection.classList.remove('d-none');
            }

            function hideSambungan() {
                sambunganSection.classList.add('d-none');
                totalSambunganSection.classList.add('d-none');
            }

            async function addNewSambungan() {
                let currentSambunganRoll = document.getElementById('sambungan_roll_'+(jumlahSambungan.value-1));

                console.log(currentSambunganRoll, jumlahSambungan.value);

                if ((currentSambunganRoll) && (currentSambunganRoll.value > 0)) {
                    document.getElementById("loading").classList.remove("d-none");

                    await storeSambungan();

                    document.getElementById("loading").classList.add("d-none");

                    // row
                    let divRow = document.createElement('div');
                    divRow.setAttribute('class', 'row');

                    // 1
                    let divCol1 = document.createElement('div');
                    divCol1.setAttribute('class', 'col-12 mb-1');

                    divCol1.innerHTML=`
                        <div class="input-group input-group-sm">
                            <input type="number" class="form-control form-control-sm border-input sambungan_roll" id="sambungan_roll_`+jumlahSambungan.value+`" name="sambungan_roll[`+jumlahSambungan.value+`]" step=".0001" onkeyup="sumNewSambungan()" onchange="sumNewSambungan();"
                            <span class="input-group-text input-group-unit"></span>
                        </div>
                    `;

                    divRow.appendChild(divCol1);
                    sambunganSection.appendChild(divRow);

                    jumlahSambungan.value++;
                }
            }

            var totalCurrentSambunganRoll = 0;

            async function sumNewSambungan() {
                totalCurrentSambunganRoll = 0;

                let sambunganRollElements = document.getElementsByClassName("sambungan_roll");

                for (let i = 0; i < sambunganRollElements.length; i++) {
                    totalCurrentSambunganRoll += Number(sambunganRollElements[i].value);
                }

                $("#current_total_sambungan_roll").val(Number(totalCurrentSambunganRoll).round(2)).trigger("change");
            }

            async function storeSambungan() {
                document.getElementById("loading").classList.remove("d-none");

                let spreadingForm = new FormData(document.getElementById("spreading-form"));

                let dataObj = {
                    "no_form_cut_input": $("#no_form").val(),
                    "color_act": $("#color_act").val(),
                    "detail_item": $("#detail_item").val(),
                    "no_meja": $("#no_meja").val(),
                    "metode": method,
                    "lap": lap
                }

                spreadingForm.forEach((value, key) => dataObj[key] = value);

                return $.ajax({
                    url: '{{ route('store-this-time-form-cut-input') }}',
                    type: 'post',
                    dataType: 'json',
                    data: dataObj,
                    success: function(res) {
                        document.getElementById("loading").classList.add("d-none");

                        if (res) {
                            console.log(res);
                        }
                    }, error: function(jqXHR) {
                        document.getElementById("loading").classList.add("d-none");

                        console.log(jqXHR);

                        Swal.fire({
                            icon: 'error',
                            title: 'Koneksi Hilang',
                            text: 'Terjadi Kesalahan',
                            showConfirmButton: true,
                            confirmButtonText: 'Oke',
                            confirmButtonColor: "#6531a0",
                        }).then(() => {
                            // location.reload();
                        });
                    }
                });
            }

            function checkSambungan(detailId) {
                $.ajax({
                    url: '{{ route('check-sambungan') }}/' + detailId,
                    type: 'get',
                    dataType: 'json',
                    success: async function(res) {
                        if (res.count > 0) {
                            await setSambungan(res.data);

                            sumNewSambungan();
                        }
                    }
                });
            }

            async function setSambungan(data) {
                jumlahSambungan.value = 0;

                sambunganSection.innerHTML = "";

                let divRow = document.createElement('div');
                divRow.setAttribute('class', 'row');

                data.forEach((element, index, array) => {
                    // 1
                    let divCol1 = document.createElement('div');
                    divCol1.setAttribute('class', 'col-12 mb-1');

                    divCol1.innerHTML=`
                        `+(index == 0 ? `<label class="form-label"><small><b>Sambungan Roll</b></small></label>`: `` )+`
                        <div class="input-group input-group-sm">
                            <input type="number" class="form-control form-control-sm border-input sambungan_roll" id="sambungan_roll_`+index+`" name="sambungan_roll[`+index+`]" step=".0001" onkeyup="sumNewSambungan()" onchange="sumNewSambungan()" value="`+element.sambungan_roll+`">
                        </div>
                    `;

                    // row
                    divRow.appendChild(divCol1);
                    sambunganSection.appendChild(divRow);

                    jumlahSambungan.value++;
                });

                // 1
                let divCol1 = document.createElement('div');
                divCol1.setAttribute('class', 'col-12 mb-1');

                divCol1.innerHTML=`
                    <div class="input-group input-group-sm">
                        <input type="number" class="form-control form-control-sm border-input sambungan_roll" id="sambungan_roll_`+jumlahSambungan.value+`" name="sambungan_roll[`+jumlahSambungan.value+`]" step=".0001" onkeyup="sumNewSambungan()" onchange="sumNewSambungan()" value="">
                    </div>
                `;

                // row
                divRow.appendChild(divCol1);
                sambunganSection.appendChild(divRow);

                jumlahSambungan.value++;
            }

            function resetSambungan() {
                $("#current_total_sambungan_roll").val("0");

                sambunganSection.innerHTML = "";

                // row
                let divRow = document.createElement('div');
                divRow.setAttribute('class', 'row');

                // 1
                let divCol1 = document.createElement('div');
                divCol1.setAttribute('class', 'col-12 mb-1');

                divCol1.innerHTML=`
                    <label class="form-label"><small><b>Sambungan Roll</b></small></label>
                    <div class="input-group input-group-sm">
                        <input type="number" class="form-control form-control-sm border-input sambungan_roll" id="sambungan_roll_0" name="sambungan_roll[0]" step=".0001" onkeyup="sumNewSambungan()" onchange="sumNewSambungan()">
                        <span class="input-group-text input-group-unit"></span>
                    </div>
                `;

                // row
                divRow.appendChild(divCol1);

                sambunganSection.appendChild(divRow);

                jumlahSambungan.value = 1;
            }
    </script>
@endsection
