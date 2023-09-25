@extends('layouts.index')

@section('content')
    <div class="row g-3">
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
                    <div class="row">
                        <input type="hidden" name="id" id="id" value="{{ $id }}" readonly>
                        <input type="hidden" name="act_costing_id" id="act_costing_id" value="{{ $formCutInputData->act_costing_id }}" readonly>
                        <input type="hidden" name="status" id="status" value="{{ $formCutInputData->status }}" readonly>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label"><small><b>Start</b></small></label>
                                <input type="text" class="form-control form-control-sm" name="start" id="start-time" value="{{ $formCutInputData->waktu_mulai }}" readonly>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label"><small><b>Finish</b></small></label>
                                <input type="text" class="form-control form-control-sm" name="finish" id="finish-time" value="{{ $formCutInputData->waktu_selesai }}" readonly>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label"><small><b>Shell</b></small></label>
                                <select class="form-select form-select-sm" name="shell" id="shell">
                                    <option value="a" {{ $formCutInputData->shell == "a" ? "selected" : "" }}>A</option>
                                    <option value="b" {{ $formCutInputData->shell == "b" ? "selected" : "" }}>B</option>
                                    <option value="c" {{ $formCutInputData->shell == "c" ? "selected" : "" }}>C</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label label-fetch"><small><b>No. Form</b></small></label>
                                <input type="text" class="form-control form-control-sm border-fetch" name="no_form" id="no_form" value="{{ $formCutInputData->no_form }}" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label"><small><b>Tanggal</b></small></label>
                                <input type="date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label label-fetch"><small><b>Kode Marker</b></small></label>
                                <input type="text" class="form-control form-control-sm border-fetch" value="{{ $formCutInputData->id_marker }}" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label label-fetch"><small><b>No. WS</b></small></label>
                                <input type="text" class="form-control form-control-sm border-fetch" name="no_ws" value="{{ $formCutInputData->act_costing_ws }}" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label label-fetch"><small><b>Buyer</b></small></label>
                                <input type="text" class="form-control form-control-sm border-fetch" name="buyer" value="{{ $thisActCosting->buyer }}" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label label-fetch"><small><b>Style</b></small></label>
                                <input type="text" class="form-control form-control-sm border-fetch" name="style" value="{{ $thisActCosting->style }}" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label label-fetch"><small><b>Color</b></small></label>
                                <input type="text" class="form-control form-control-sm border-fetch" name="color" id="color" value="{{ $formCutInputData->color }}" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label label-fetch"><small><b>Panel</b></small></label>
                                <input type="text" class="form-control form-control-sm border-fetch" name="panel" id="panel" value="{{ $formCutInputData->panel }}" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label label-fetch"><small><b>PO</b></small></label>
                                <input type="text" class="form-control form-control-sm border-fetch" name="po" value="{{ $formCutInputData->po_marker }}" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label label-fetch"><small><b>QTY Gelar Marker</b></small></label>
                                <input type="text" class="form-control form-control-sm border-fetch" name="gelar_qty" value="{{ $formCutInputData->gelar_qty }}" readonly>
                            </div>
                        </div>
                    </div>
                    <table id="ratio-datatable" class="table table-striped table-sm w-100 text-center mt-3">
                        <thead>
                            <tr>
                                <th class="label-fetch">Size</th>
                                <th class="label-fetch">Ratio</th>
                                <th class="label-fetch">Qty Cut Marker</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $totalRatio = 0;
                                $totalCutQty = 0;
                            @endphp
                            @foreach ($thisMarkerDetails as $item)
                                <tr>
                                    @php
                                        $totalRatio += $item->ratio;
                                        $totalCutQty += $item->cut_qty;
                                    @endphp
                                    <td>{{ $soDetData->where('id', $item->so_det_id)->first()->size }}</td>
                                    <td>{{ $item->ratio }}</td>
                                    <td>{{ $item->cut_qty }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>Total</th>
                                <th id="totalRatio">{{ $totalRatio }}</th>
                                <th id="totalQtyCutMarker">{{ $totalCutQty }}</th>
                            </tr>
                        </tfoot>
                    </table>
                    <input type="hidden" name="total_ratio" id="total_ratio" value="{{ $totalRatio }}">
                    <input type="hidden" name="total_qty_cut" id="total_qty_cut" value="{{ $totalCutQty }}">
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <button class="btn btn-sb mb-3 d-none" id="next-process-1" onclick="nextProcessOne()">NEXT</button>
            <div class="card card-sb d-none" id="detail-data-card">
                <div class="card-header">
                    <h3 class="card-title">Detail Data</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse"><i
                                class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body" style="display: block;">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label label-fetch"><small><b>P. Marker</b></small></label>
                                <input type="number" class="form-control form-control-sm border-fetch" value="{{ $formCutInputData->panjang_marker }}" readonly>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label label-fetch"><small><b>Unit</b></small></label>
                                <input type="text" class="form-control form-control-sm border-fetch" value="{{ strtoupper($formCutInputData->unit_panjang_marker) }}" readonly>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label label-input"><small><b>P. Act</b></small></label>
                                <input type="number" class="form-control form-control-sm border-input" name="p_act" id="p_act" value="{{ $formCutInputData->p_act }}" onkeyup="calculateConsAmpar(this.value, {{ $totalRatio }}); calculateEstAmpar(undefined, this.value);" onchange="calculateConsAmpar(this.value, {{ $totalRatio }}); calculateEstAmpar(undefined, this.value);">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label label-input"><small><b>Unit Act</b></small></label>
                                <input type="text" class="form-control form-control-sm border-input" name="unit_p_act" id="unit_p_act" value="{{ strtoupper($formCutInputData->unit_panjang_marker) }}" readonly>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label label-fetch"><small><b>Comma</b></small></label>
                                <input type="number" class="form-control form-control-sm border-fetch" value="{{ $formCutInputData->comma_marker }}" readonly>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label label-fetch"><small><b>Unit</b></small></label>
                                <input type="text" class="form-control form-control-sm border-fetch" value="{{ strtoupper($formCutInputData->unit_comma_marker) }}" readonly>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label label-input"><small><b>Comma Act</b></small></label>
                                <input type="number" class="form-control form-control-sm border-input" name="comma_act" id="comma_act" value="{{ $formCutInputData->p_act }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label label-input"><small><b>Unit Act</b></small></label>
                                <input type="text" class="form-control form-control-sm border-input" name="unit_comma_act" id="unit_comma_act" value="{{ strtoupper($formCutInputData->unit_comma_marker) }}" readonly>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label label-fetch"><small><b>L. Marker</b></small></label>
                                <input type="number" class="form-control form-control-sm border-fetch" value="{{ strtoupper($formCutInputData->lebar_marker) }}" readonly>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label label-fetch"><small><b>Unit</b></small></label>
                                <input type="text" class="form-control form-control-sm border-fetch" value="{{ strtoupper($formCutInputData->unit_lebar_marker) }}" readonly>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label label-input"><small><b>L. Act</b></small></label>
                                <input type="number" class="form-control form-control-sm border-input" name="l_act" id="l_act" value="{{ $formCutInputData->l_act }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label label-input"><small><b>Unit Act</b></small></label>
                                <input type="text" class="form-control form-control-sm border-input" name="unit_l_act"
                                    id="unit_l_act" value="{{ strtoupper($formCutInputData->unit_lebar_marker) }}" readonly>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label label-fetch"><small><b>Cons WS</b></small></label>
                                <input type="text" class="form-control form-control-sm border-fetch" name="cons_ws" id="cons_ws"
                                    onkeyup="calculateEstKain(this.value, {{ $totalCutQty }})"
                                    onchange="calculateEstKain(this.value, {{ $totalCutQty }})" readonly>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label label-fetch"><small><b>Cons Marker</b></small></label>
                                <input type="text" class="form-control form-control-sm border-fetch" name="cons_marker" id="cons_marker" value="{{ $formCutInputData->cons_marker }}" readonly>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label label-input"><small><b>Cons Act</b></small></label>
                                <input type="number" class="form-control form-control-sm border-input label-input" name="cons_act" id="cons_act" value="{{ $formCutInputData->cons_act }}" step=".01">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label label-fetch"><small><b>Cons Piping</b></small></label>
                                <input type="number" class="form-control form-control-sm border-fetch" step=".01" name="cons_pipping" id="cons_pipping" onkeyup="calculateEstPipping(this.value, {{ $totalCutQty }})" onchange="calculateEstPipping(this.value, {{ $totalCutQty }})" value="{{ $formCutInputData->cons_pipping }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label label-calc"><small><b>Cons 1 Ampar</b></small></label>
                                <input type="number" class="form-control form-control-sm border-calc" step=".01" name="cons_ampar" id="cons_ampar" value="{{ $formCutInputData->cons_ampar }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label label-calc"><small><b>Est. Kebutuhan Kain Piping</b></small></label>
                                <div class="row g-1">
                                    <div class="col-6">
                                        <input type="number" class="form-control form-control-sm border-calc" step=".01" name="est_pipping" id="est_pipping" value="{{ $formCutInputData->est_pipping }}">
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
                                        <input type="number" class="form-control form-control-sm border-calc" step=".01" name="est_kain" id="est_kain" value="{{ $formCutInputData->est_kain }}">
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
                                <div id="reader" style="width: 576px !important; margin: auto;"></div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label label-input"><small><b>Kode Barang</b></small></label>
                                <div class="input-group">
                                    <input type="text" class="form-control form-control-sm border-input" name="kode_barang" id="kode_barang">
                                    <button class="btn btn-sm btn-primary" type="button" id="scan-button" onclick="initScan()">Scan</button>
                                    <button class="btn btn-sm btn-success" type="button" id="scan-button" onclick="fetchScan()">Get</button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label label-scan"><small><b>ID Item</b></small></label>
                                <input type="text" class="form-control form-control-sm border-scan" name="id_item" id="id_item" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
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
                        <div class="row">
                            <div class="col-4">
                                <div class="mb-3">
                                    <label class="form-label label-input"><small><b>Group</b></small></label>
                                    <input type="text" class="form-control form-control-sm border-input" id="current_group" name="current_group">
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="mb-3">
                                    <label class="form-label label-scan"><small><b>Id Item</b></small></label>
                                    <input type="text" class="form-control form-control-sm border-scan" id="current_id_item" name="current_id_item" readonly>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="mb-3">
                                    <label class="form-label label-scan"><small><b>Lot</b></small></label>
                                    <input type="text" class="form-control form-control-sm border-scan" id="current_lot" name="current_lot" readonly>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="mb-3">
                                    <label class="form-label label-scan"><small><b>Roll</b></small></label>
                                    <input type="text" class="form-control form-control-sm border-scan" id="current_roll" name="current_roll" readonly>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="mb-3">
                                    <label class="form-label label-scan"><small><b>Qty</b></small></label>
                                    <input type="number" class="form-control form-control-sm border-scan" id="current_qty" name="current_qty" readonly>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="mb-3">
                                    <label class="form-label label-scan"><small><b>Unit</b></small></label>
                                    <input type="text" class="form-control form-control-sm border-scan" id="current_unit" name="current_unit" readonly>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="mb-3">
                                    <label class="form-label label-input"><small><b>Sisa Gelaran</b></small></label>
                                    <input type="number" class="form-control form-control-sm border-input" id="current_sisa_gelaran" name="current_sisa_gelaran" step=".01">
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="mb-3">
                                    <label class="form-label label-input"><small><b>Sambungan</b></small></label>
                                    <input type="number" class="form-control form-control-sm border-input" id="current_sambungan" name="current_sambungan" step=".01"
                                        onkeyup="calculateShortRoll(lembarGelaran = undefined, pActual = undefined, kepalaKain = undefined, piping = undefined, sisaKain = undefined, reject = undefined, sambungan = this.value, qty = undefined)"
                                        onchange="calculateShortRoll(lembarGelaran = undefined, pActual = undefined, kepalaKain = undefined, piping = undefined, sisaKain = undefined, reject = undefined, sambungan = this.value, qty = undefined)">
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="mb-3">
                                    <label class="form-label label-calc"><small><b>Estimasi Amparan</b></small></label>
                                    <input type="number" class="form-control form-control-sm border-calc" id="current_est_amparan" name="current_est_amparan" step=".01" readonly>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="mb-3">
                                    <label class="form-label label-sb"><small><b>Lembar Gelaran</b></small></label>
                                    <input type="number" class="form-control form-control-sm border-sb" id="current_lembar_gelaran" name="current_lembar_gelaran"
                                        onkeyup="calculateTotalPemakaian(lembarGelaran = this.value, pActual = undefined, kepalaKain = undefined, sisaTidakBisa = undefined, reject = undefined);calculateShortRoll(lembarGelaran = this.value, pActual = undefined, kepalaKain = undefined, piping = undefined, sisaKain = undefined, reject = undefined, sambungan = undefined, qty = undefined);"
                                        onchange="calculateTotalPemakaian(lembarGelaran = this.value, pActual = undefined, kepalaKain = undefined, sisaTidakBisa = undefined, reject = undefined);calculateShortRoll(lembarGelaran = this.value, pActual = undefined, kepalaKain = undefined, piping = undefined, sisaKain = undefined, reject = undefined, sambungan = undefined, qty = undefined);" readonly>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="mb-3">
                                    <label class="form-label label-sb"><small><b>Average Time</b></small></label>
                                    <input type="text" class="form-control form-control-sm border-sb" id="current_average_time" name="current_average_time" readonly>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="mb-3">
                                    <label class="form-label label-input"><small><b>Kepala Kain</b></small></label>
                                    <input type="number" class="form-control form-control-sm border-input" id="current_kepala_kain" name="current_kepala_kain"
                                        onkeyup="calculateTotalPemakaian(lembarGelaran = undefined, pActual = undefined, kepalaKain = this.value, sisaTidakBisa = undefined, reject = undefined)"
                                        onchange="calculateTotalPemakaian(lembarGelaran = undefined, pActual = undefined, kepalaKain = this.value, sisaTidakBisa = undefined, reject = undefined)" step=".01">
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="mb-3">
                                    <label class="form-label label-input"><small><b>Sisa Tidak Bisa</b></small></label>
                                    <input type="number" class="form-control form-control-sm border-input" id="current_sisa_tidak_bisa" name="current_sisa_tidak_bisa" step=".01" onkeyup="calculateTotalPemakaian(lembarGelaran = undefined, pActual = undefined, kepalaKain = undefined, sisaTidakBisa = this.value, reject = undefined);" onchange="calculateTotalPemakaian(lembarGelaran = undefined, pActual = undefined, kepalaKain = undefined, sisaTidakBisa = this.value, reject = undefined);">
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="mb-3">
                                    <label class="form-label label-input"><small><b>Reject</b></small></label>
                                    <input type="number" class="form-control form-control-sm border-input" id="current_reject" name="current_reject" step=".01"
                                        onkeyup="calculateTotalPemakaian(lembarGelaran = undefined, pActual = undefined, kepalaKain = undefined, sisaTidakBisa = undefined, reject = this.value); calculateShortRoll(lembarGelaran = undefined, pActual = undefined, kepalaKain = undefined, piping = undefined, sisaKain = undefined, reject = this.value, sambungan = undefined, qty = undefined);"
                                        onchange="calculateTotalPemakaian(lembarGelaran = undefined, pActual = undefined, kepalaKain = undefined, sisaTidakBisa = undefined, reject = this.value); calculateShortRoll(lembarGelaran = undefined, pActual = undefined, kepalaKain = undefined, piping = undefined, sisaKain = undefined, reject = this.value, sambungan = undefined, qty = undefined);">
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="mb-3">
                                    <label class="form-label label-input"><small><b>Sisa Kain</b></small></label>
                                    <input type="number" class="form-control form-control-sm border-input" id="current_sisa_kain" name="current_sisa_kain" step=".01"
                                        onkeyup="calculateShortRoll(lembarGelaran = undefined, pActual = undefined, kepalaKain = undefined, piping = undefined, sisaKain = this.value, reject = undefined, sambungan = undefined, qty = undefined)"
                                        onchange="calculateShortRoll(lembarGelaran = undefined, pActual = undefined, kepalaKain = undefined, piping = undefined, sisaKain = this.value, reject = undefined, sambungan = undefined, qty = undefined)">
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="mb-3">
                                    <label class="form-label label-calc"><small><b>Tot. Pakai /Roll</b></small></label>
                                    <input type="number" class="form-control form-control-sm border-calc" id="current_total_pemakaian_roll" name="current_total_pemakaian_roll" step=".01" readonly>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="mb-3">
                                    <label class="form-label label-calc"><small><b>Short Roll +/-</b></small></label>
                                    <input type="number" class="form-control form-control-sm border-calc" id="current_short_roll" name="current_short_roll" step=".01" readonly>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="mb-3">
                                    <label class="form-label label-input"><small><b>Piping</b></small></label>
                                    <input type="number" class="form-control form-control-sm border-input" id="current_piping" name="current_piping" step=".01"
                                        onkeyup="calculateShortRoll(lembarGelaran = undefined, pActual = undefined, kepalaKain = undefined, piping = this.value, sisaKain = undefined, reject = undefined, sambungan = undefined, qty = undefined)"
                                        onchange="calculateShortRoll(lembarGelaran = undefined, pActual = undefined, kepalaKain = undefined, piping = this.value, sisaKain = undefined, reject = undefined, sambungan = undefined, qty = undefined)">
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="mb-3">
                                    <label class="form-label label-input"><small><b>Remark</b></small></label>
                                    <input type="number" class="form-control form-control-sm border-input" id="current_remark" name="current_remark" step=".01">
                                </div>
                            </div>
                            <div class="col-md-6 my-3">
                                <button type="button" class="btn btn-success btn-sm w-100 h-100" style="min-height: 90px !important;" id="startLapButton" onclick="startTimeRecord()">Start</button>
                                <button type="button" class="btn btn-primary btn-sm d-none w-100 h-100" style="min-height: 90px !important;" id="nextLapButton" onclick="addNewTimeRecord()">Next Lap</button>
                            </div>
                            <div class="col-md-6 my-3">
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
                                <div class="w-100 h-100 table-responsive mt-3">
                                    {{-- <form action="#" method="post" id="time-record-form"> --}}
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
                                    {{-- </form> --}}
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
                                            <th class="label-scan">ID Item</th>
                                            <th class="label-scan">Lot</th>
                                            <th class="label-scan">Roll</th>
                                            <th class="label-scan">Qty</th>
                                            <th class="label-scan">Unit</th>
                                            <th>Sisa Gelaran</th>
                                            <th>Sambungan</th>
                                            <th class="label-calc">Estimasi Amparan</th>
                                            <th>Lembar Gelaran</th>
                                            <th>Average Time</th>
                                            <th>Kepala Kain</th>
                                            <th>Sisa Tidak Bisa</th>
                                            <th>Reject</th>
                                            <th>Sisa Kain</th>
                                            <th class="label-calc">Total Pemakaian Per Roll</th>
                                            <th class="label-calc">Short Roll +/-</th>
                                            <th>Piping</th>
                                            <th>Remark</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label label-input"><small><b>Operator</b></small></label>
                                        <input type="text" class="form-control form-control-sm border-input" name="operator" id="operator" value="{{ $formCutInputData->operator }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-labe label-calc"><small><b>Cons. Actual 1 Gelaran</b></small></label>
                                        <input type="text" class="form-control form-control-sm border-calc" name="cons_actual_gelaran" id="cons_actual_gelaran" readonly>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label label-calc"><small><b>Unit</b></small></label>
                                        <select class="form-select form-select-sm border-calc" name="unit_cons_actual_gelaran" id="unit_cons_actual_gelaran" disabled>
                                            <option value="meter">METER</option>
                                            <option value="yard">YARD</option>
                                        </select>
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

            // -Summary Data-
            var summaryData = null;

            // -Ratio & Qty Cuy-
            var totalRatio = document.getElementById('total_ratio').value;
            var totalQtyCut = document.getElementById('total_qty_cut').value;

        // Function List :
            // -On Load-
            $(document).ready(() => {
                checkStatus();
                getNumberData();
                clearGeneralForm();
                clearScanItemForm();
                clearSpreadingForm();

                // -Global Key Event-
                $(document).keyup(function(e) {
                    if (e.key === "Escape") {
                        e.preventDefault();

                        stopTimeRecord()
                    }
                });

                // -Kode Barang Manual Input Event-
                $('#kode_barang').keyup(function(e) {
                    if (e.key === "Enter") {
                        e.preventDefault();

                        getScannedItem(this.value);
                    }
                });
            });

            // Process :
                // -Start Process-
                function startProcess() {
                    let now = new Date();
                    startTime.value = now.getFullYear().toString() + "-" + pad(now.getMonth() + 1) + "-" + pad(now.getDate()) + " " + pad(now.getHours()) + ":" + pad(now.getMinutes()) + ":" + pad(now.getSeconds());

                    updateToStartProcess();

                    startProcessButton.classList.add("d-none");
                    nextProcessOneButton.classList.remove("d-none");
                }

                // -Start Process Transaction-
                function updateToStartProcess() {
                    return $.ajax({
                        url: '{{ route("start-process-form-cut-input") }}/'+id,
                        type: 'put',
                        dataType: 'json',
                        data: {
                            startTime: startTime.value,
                        },
                        success: function(res) {
                            if (res) {
                                console.log(res);
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
                        url: '{{ route("next-process-one-form-cut-input") }}/'+id,
                        type: 'put',
                        dataType: 'json',
                        data: {
                            shell: $("#shell").val()
                        },
                        success: function(res) {
                            if (res) {
                                console.log(res);
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
                        url: '{{ route("next-process-two-form-cut-input") }}/'+id,
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
                                console.log(res.message);

                                if (res.status == 200) {
                                    $('#header-data-card').CardWidget('collapse');
                                    $('#detail-data-card').CardWidget('collapse');
                                    $('#scan-qr-card').removeClass('d-none');

                                    nextProcessTwoButton.classList.add("d-none");
                                    nextProcessThreeButton.classList.remove("d-none");
                                    initScan();
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
                    if (checkIfNull(document.getElementById("id_item").value) && checkIfNull(document.getElementById("detail_item").value) && checkIfNull(document.getElementById("color_act").value) && currentScannedItem) {
                        nextProcessThreeButton.classList.add("d-none");

                        $('#scan-qr-card').CardWidget('collapse');

                        setSpreadingForm(currentScannedItem);
                        openTimeRecordCondition();
                        getSummary();

                        $('#spreading-form-card').removeClass('d-none');
                        $('#summary-card').removeClass('d-none');
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
                function backToProcessThree() {
                    storeTimeRecord();
                }

                // -Store Time Record Transaction-
                function storeTimeRecord() {
                    let spreadingForm = new FormData(document.getElementById("spreading-form"));

                    let dataObj = {
                        "no_form_cut_input": $("#no_form").val(),
                        "color_act": $("#color_act").val(),
                        "detail_item": $("#detail_item").val(),
                    }

                    spreadingForm.forEach((value, key) => dataObj[key] = value);

                    return $.ajax({
                        url: '{{ route("store-time-form-cut-input") }}',
                        type: 'post',
                        dataType: 'json',
                        data: dataObj,
                        success: function(res) {
                            if (res) {
                                initScan();
                                clearScanItemForm();
                                clearSpreadingForm();
                                firstTimeRecordCondition();

                                if (res.additional.length > 0) {
                                    $('#summary-card').removeClass('d-none');

                                    appendScannedItem(res.additional[0]);
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

                // -Store This Time Record Transaction-
                function storeThisTimeRecord() {
                    let spreadingForm = new FormData(document.getElementById("spreading-form"));

                    let dataObj = {
                        "no_form_cut_input": $("#no_form").val(),
                        "color_act": $("#color_act").val(),
                        "detail_item": $("#detail_item").val(),
                        "lap": lap
                    }

                    spreadingForm.forEach((value, key) => dataObj[key] = value);

                    return $.ajax({
                        url: '{{ route("store-this-time-form-cut-input") }}',
                        type: 'post',
                        dataType: 'json',
                        data: dataObj,
                        success: function(res) {
                            if (res) {
                                console.log(res);
                            }
                        }
                    });
                }

                // -Finish Process-
                function finishProcess() {
                    let now = new Date();
                    finishTime.value = now.getFullYear().toString() + "-" + pad(now.getMonth() + 1) + "-" + pad(now.getDate()) + " " + pad(now.getHours()) + ":" + pad(now.getMinutes()) + ":" + pad(now.getSeconds());

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
                            await updateToNextProcessOne();
                            await updateToNextProcessTwo();

                            $.ajax({
                                url: '{{ route("finish-process-form-cut-input") }}/'+id,
                                type: 'put',
                                dataType: 'json',
                                data: {
                                    finishTime: finishTime.value,
                                    operator: $('#operator').val(),
                                    consAct: $('#cons_actual_gelaran').val(),
                                    unitConsAct: $('#unit_cons_actual_gelaran').val()
                                },
                                success: function(res) {
                                    if (res) {
                                        console.log(res);

                                        Swal.fire({
                                            icon: 'success',
                                            title: 'Berhasil',
                                            text: 'Proses telah berhasil diselesaikan',
                                            showCancelButton: false,
                                            showConfirmButton: true,
                                            confirmButtonText: 'Oke',
                                        });

                                        lockFormCutInput();
                                    }
                                },
                                error: function(jqXHR) {
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

            // -Calculate Cons Ampar-
            function calculateConsAmpar(pActual = 0, totalRatio = 0) {
                let pActualVar = Number(pActual);

                let consAmpar = totalRatio > 0 ? pActualVar/totalRatio : 0;

                document.getElementById('cons_ampar').value = consAmpar.toFixed(2);
            }

            // -Calculate Est. Piping-
            function calculateEstPipping(consPipping = 0, totalQtyCut = 0) {
                let consPippingVar = Number(consPipping);

                let estPipping = consPippingVar * totalQtyCut;

                document.getElementById('est_pipping').value = estPipping.toFixed(2);
            }

            // -Calculate Est. Kain-
            function calculateEstKain(consWs = 0, totalQtyCut = 0) {
                let consWsVar = Number(consWs);

                let estKain = consWsVar * totalQtyCut
                document.getElementById('est_kain').value = estKain.toFixed(2);
            }

            // -Calculate Est. Ampar-
            function calculateEstAmpar(qty = 0, pActual = 0) {
                let qtyVar = qty > 0 ? Number(qty) : Number(document.getElementById("current_qty").value);
                let pActualVar = qty > 0 ? Number(pActual) : Number(document.getElementById("p_act").value);

                let estAmpar = pActualVar > 0 ? qtyVar/pActualVar : 0;

                document.getElementById("current_est_amparan").value = estAmpar.toFixed(2);
            }

            // -Calculate Total Pemakaian Roll-
            function calculateTotalPemakaian(lembarGelaran = 0, pActual = 0, kepalaKain = 0, sisaTidakBisa = 0, reject = 0) {
                let lembarGelaranVar = lembarGelaran > 0 ? Number(lembarGelaran) : Number(document.getElementById("current_lembar_gelaran").value);
                let pActualVar = pActual > 0 ? Number(pActual) : Number(document.getElementById("p_act").value);
                let kepalaKainVar = kepalaKain > 0 ? Number(kepalaKain) : Number(document.getElementById("current_kepala_kain").value);
                let sisaTidakBisaVar = sisaTidakBisa > 0 ? Number(sisaTidakBisa) : Number(document.getElementById("current_sisa_tidak_bisa").value);
                let rejectVar = reject > 0 ? Number(reject) : Number(document.getElementById("current_reject").value);

                let totalPemakaian = lembarGelaranVar * pActualVar + kepalaKainVar + sisaTidakBisaVar + rejectVar;

                document.getElementById("current_total_pemakaian_roll").value = totalPemakaian.toFixed(2);
            }

            // -Calculate Short Roll-
            function calculateShortRoll(lembarGelaran = 0, pActual = 0, kepalaKain = 0, piping = 0, sisaKain = 0, reject = 0, sambungan = 0, qty = 0) {
                let lembarGelaranVar = lembarGelaran > 0 ? Number(lembarGelaran) : Number(document.getElementById("current_lembar_gelaran").value);
                let pActualVar = pActual > 0 ? Number(pActual) : Number(document.getElementById("p_act").value);
                let kepalaKainVar = kepalaKain > 0 ? Number(kepalaKain) : Number(document.getElementById("current_kepala_kain").value);
                let pipingVar = piping > 0 ? Number(piping) : Number(document.getElementById("current_piping").value);
                let sisaKainVar = sisaKain > 0 ? Number(sisaKain) : Number(document.getElementById("current_sisa_kain").value);
                let rejectVar = reject > 0 ? Number(reject) : Number(document.getElementById("current_reject").value);
                let sambunganVar = sambungan > 0 ? Number(sambungan) : Number(document.getElementById("current_sambungan").value);
                let qtyVar = qty > 0 ? Number(qty) : Number(document.getElementById("current_qty").value);

                let shortRoll = pActualVar * lembarGelaranVar + kepalaKainVar + pipingVar + sisaKainVar + rejectVar + sambunganVar - sisaKainVar - qtyVar;

                document.getElementById("current_short_roll").value = shortRoll.toFixed(2);
            }

            // -Calculate Cons. Actual 1 Gelaran
            function calculateConsActualGelaran(unit = 0, piping = 0, lembar = 0, pActual = 0, totalQtyFabric = 0, totalQtyCut = 0) {
                let unitVar = unit;
                let pipingVar = Number(piping);
                let lembarVar = Number(lembar);
                let pActualVar = pActual > 0 ? Number(pActual) : Number(document.getElementById('p_act').value);
                let totalQtyFabricVar = Number(totalQtyFabric);
                let totalQtyCutVar = Number(totalQtyCut);

                if (unitVar && pipingVar && lembarVar && pActualVar && totalQtyCutVar) {
                    let consActualGelaran = "";

                    if (unitVar == "KGM") {
                        consActualGelaran = totalQtyCutVar > 0 ? totalQtyFabricVar - pipingVar / totalQtyCutVar : 0;
                    } else {
                        consActualGelaran = totalQtyCutVar > 0 ? lembarVar * pActualVar / totalQtyCutVar : 0
                    }

                    document.getElementById("cons_actual_gelaran").value = consActualGelaran.toFixed(2);
                }
            }

            // -Get Cons. WS Data-
            function getNumberData() {
                return $.ajax({
                    url: '{{ route("get-number-form-cut-input") }}',
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

                            calculateEstKain(consWs, totalQtyCut);
                        }
                    }
                });
            }

            // -Check Form Cut Input Status-
            async function checkStatus() {
                if (status == "PENGERJAAN FORM CUTTING") {
                    startProcessButton.classList.add("d-none");
                    nextProcessOneButton.classList.remove("d-none");
                }

                if (status == "PENGERJAAN FORM CUTTING DETAIL") {
                    startProcessButton.classList.add("d-none");
                    nextProcessOneButton.classList.add("d-none");

                    $('#header-data-card').CardWidget('collapse');
                    $('#detail-data-card').removeClass('d-none');
                    nextProcessTwoButton.classList.remove("d-none");
                }

                if (status == "PENGERJAAN FORM CUTTING SPREAD") {
                    startProcessButton.classList.add("d-none");
                    nextProcessOneButton.classList.add("d-none");
                    nextProcessTwoButton.classList.add("d-none");
                    nextProcessThreeButton.classList.remove("d-none");

                    $('#header-data-card').CardWidget('collapse');
                    $('#detail-data-card').removeClass('d-none');
                    $('#detail-data-card').CardWidget('collapse');
                    $('#scan-qr-card').removeClass('d-none');

                    initScan();

                    checkSpreadingForm();

                    await getSummary()
                    if (summaryData != null && summaryData.length > 0) {
                        firstTimeRecordCondition();

                        $('#spreading-form-card').removeClass("d-none");
                        $('#summary-card').removeClass("d-none");

                        finishProcessButton.classList.remove("d-none");
                    }
                }

                if (status == "SELESAI PENGERJAAN") {
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

            var spreadingFormData = null;

            // -Check Spreading Form-
            function checkSpreadingForm() {
                let noForm = document.getElementById("no_form").value;

                $.ajax({
                    url: '{{ route("check-spreading-form-cut-input") }}/'+noForm,
                    type: 'get',
                    dataType: 'json',
                    success: function(res) {
                        if (res) {
                            nextProcessThreeButton.classList.remove('d-none');

                            if (res.count > 0) {
                                spreadingFormData = res.data;

                                setSpreadingForm(spreadingFormData);

                                checkTimeRecordLap(res.data.id, res.data.id_item);

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
            function setSpreadingForm(data) {
                clearSpreadingForm();

                document.getElementById("current_group").value = data.group ? data.group : '';
                document.getElementById("current_id_item").value = data.id_item ? data.id_item : '';
                document.getElementById("current_lot").value = data.lot ? data.lot : '';
                document.getElementById("current_roll").value = data.roll ? data.roll : '';
                document.getElementById("current_qty").value = data.qty ? data.qty : '';
                document.getElementById("current_unit").value = data.unit ? data.unit : '';
                document.getElementById("current_sisa_gelaran").value = data.sisa_gelaran ? data.sisa_gelaran : '';
                document.getElementById("current_sambungan").value = data.sambungan ? data.sambungan : '';
                document.getElementById("current_est_amparan").value = data.est_amparan ? data.est_amparan : '';
                document.getElementById("current_lembar_gelaran").value = data.lembar_gelaran ? data.lembar_gelaran : '';
                document.getElementById("current_average_time").value = data.average_time ? data.average_time : '';
                document.getElementById("current_kepala_kain").value = data.kepala_kain ? data.kepala_kain : '';
                document.getElementById("current_sisa_tidak_bisa").value = data.sisa_tidak_bisa ? data.sisa_tidak_bisa : '';
                document.getElementById("current_reject").value = data.reject ? data.reject : '';
                document.getElementById("current_sisa_kain").value = data.sisa_kain ? data.sisa_kain : '';
                document.getElementById("current_total_pemakaian_roll").value = data.total_pemakaian_roll ? data.total_pemakaian_roll : '';
                document.getElementById("current_short_roll").value = data.short_roll ? data.short_roll : '';
                document.getElementById("current_piping").value = data.piping ? data.piping : '';
                document.getElementById("current_remark").value = data.remark ? data.remark : '';

                calculateEstAmpar(data.roll_qty, document.getElementById("p_act").value);

                nextProcessThreeButton.classList.add("d-none");
                $('#scan-qr-card').CardWidget('collapse');

                $('#spreading-form-card').CardWidget('expand');
            }

            // -Clear Spreading Form-
            function clearSpreadingForm() {
                $('#spreading-form-card').CardWidget('collapse');

                document.getElementById("current_group").value = "";
                document.getElementById("current_id_item").value = "";
                document.getElementById("current_lot").value = "";
                document.getElementById("current_roll").value = "";
                document.getElementById("current_qty").value = "";
                document.getElementById("current_unit").value = "";
                document.getElementById("current_sisa_gelaran").value = "";
                document.getElementById("current_sambungan").value = "";
                document.getElementById("current_est_amparan").value = "";
                document.getElementById("current_lembar_gelaran").value = "";
                document.getElementById("current_average_time").value = "";
                document.getElementById("current_kepala_kain").value = "";
                document.getElementById("current_sisa_tidak_bisa").value = "";
                document.getElementById("current_reject").value = "";
                document.getElementById("current_sisa_kain").value = "";
                document.getElementById("current_total_pemakaian_roll").value = "";
                document.getElementById("current_short_roll").value = "";
                document.getElementById("current_piping").value = "";
                document.getElementById("current_remark").value = "";
            }

            // -Lock Spreading Form-
            function lockSpreadingForm() {
                document.getElementById("current_group").setAttribute("readonly", true);
                document.getElementById("current_id_item").setAttribute("readonly", true);
                document.getElementById("current_lot").setAttribute("readonly", true);
                document.getElementById("current_roll").setAttribute("readonly", true);
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
                document.getElementById("current_total_pemakaian_roll").setAttribute("readonly", true);
                document.getElementById("current_short_roll").setAttribute("readonly", true);
                document.getElementById("current_piping").setAttribute("readonly", true);
                document.getElementById("current_remark").setAttribute("readonly", true);
            }

            // -Get Summary Data-
            function getSummary() {
                if (summaryData == null) {
                    let noForm = document.getElementById("no_form").value;

                    return $.ajax({
                        url: '{{ route("get-time-form-cut-input") }}/'+noForm,
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
                }
            }

            // -Lock Form Cut Input-
            function lockFormCutInput() {
                lockProcessCondition();

                lockGeneralForm();

                lockScanItemForm();

                lockSpreadingForm();

                firstTimeRecordCondition();

                lockTimeRecord();
            }

        // Scan QR Module :
            // Variable List :
                var html5QrcodeScanner = null;

            // Function List :
                // -Initialize Scanner-
                function initScan() {
                    if (document.getElementById("reader")) {
                        function onScanSuccess(decodedText, decodedResult) {
                            // handle the scanned code as you like, for example:
                            console.log(`Code matched = ${decodedText}`, decodedResult);

                            // store to input text
                            let breakDecodedText = decodedText.split('-');

                            document.getElementById('kode_barang').value = breakDecodedText[0];

                            getScannedItem(breakDecodedText[0]);

                            html5QrcodeScanner.clear();
                        }

                        function onScanFailure(error) {
                            // handle scan failure, usually better to ignore and keep scanning.
                            // for example:
                            console.warn(`Code scan error = ${error}`);
                        }

                        html5QrcodeScanner = new Html5QrcodeScanner(
                            "reader", {
                                fps: 10,
                                qrbox: {
                                    width: 250,
                                    height: 250
                                }
                            },
                            /* verbose= */ false);
                        html5QrcodeScanner.render(onScanSuccess, onScanFailure);
                    }
                }

                // --Clear Scan Item Form--
                function clearScanItemForm() {
                    $("#kode_barang").val("");
                    $("#id_item").val("");
                    $("#detail_item").val("");
                    $("#color_act").val("");
                }

                // --Lock Scan Item Form then Clear Scanner
                function lockScanItemForm() {
                    document.getElementById("kode_barang").setAttribute("readonly", true);
                    document.getElementById("id_item").setAttribute("readonly", true);
                    document.getElementById("detail_item").setAttribute("readonly", true);
                    document.getElementById("color_act").setAttribute("disabled", true);
                    document.getElementById("scan-button").setAttribute("disabled", true);
                    document.getElementById("reader").classList.add("d-none");

                    if (html5QrcodeScanner != null) {
                        html5QrcodeScanner.clear();
                    }
                }

        // Scanned Item Module :
            // Variable List :
                var scannedItemTable = document.getElementById("scannedItemTable");
                var scannedItemTableTbody = scannedItemTable.getElementsByTagName("tbody")[0];
                var totalScannedItem = 0;
                var totalLembar = 0;
                var totalPiping = 0;
                var totalQtyFabric = 0;
                var latestUnit = "";

            // Function List :
                // -Fetch Scanned Item Data-
                function fetchScan() {
                    let kodeBarang = document.getElementById('kode_barang').value;

                    getScannedItem(kodeBarang);
                }

                // -Get Scanned Item Data-
                function getScannedItem(id) {
                    document.getElementById("id_item").value = "";
                    document.getElementById("detail_item").value = "";
                    document.getElementById("color_act").value = "";

                    if (checkIfNull(id)) {
                        return $.ajax({
                            url: '{{ route("get-scanned-form-cut-input") }}/'+id,
                            type: 'get',
                            dataType: 'json',
                            success: function(res) {
                                if (res) {
                                    currentScannedItem = res;

                                    document.getElementById("id_item").value = res.id_item;
                                    document.getElementById("detail_item").value = res.itemdesc;
                                }
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
                    totalPiping += Number(data.piping);
                    totalQtyFabric += Number(data.qty);
                    latestUnit = data.unit;

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
                    td1.innerHTML = totalScannedItem + 1;
                    td2.innerHTML = data.group ? data.group : '-' ;
                    td3.innerHTML = data.id_item ? data.id_item : '-' ;
                    td4.innerHTML = data.lot ? data.lot : '-' ;
                    td5.innerHTML = data.roll ? data.roll : '-' ;
                    td6.innerHTML = data.qty ? data.qty : '-' ;
                    td7.innerHTML = data.unit ? data.unit : '-' ;
                    td8.innerHTML = data.sisa_gelaran ? data.sisa_gelaran : '-' ;
                    td9.innerHTML = data.sambungan ? data.sambungan : '-' ;
                    td10.innerHTML = data.est_amparan ? data.est_amparan : '-' ;
                    td11.innerHTML = data.lembar_gelaran ? data.lembar_gelaran : '';
                    td12.innerHTML = data.average_time ? data.average_time : '-' ;
                    td13.innerHTML = data.kepala_kain ? data.kepala_kain : '-' ;
                    td14.innerHTML = data.sisa_tidak_bisa ? data.sisa_tidak_bisa : '-' ;
                    td15.innerHTML = data.reject ? data.reject : '-' ;
                    td16.innerHTML = data.sisa_kain ? data.sisa_kain : '-' ;
                    td17.innerHTML = data.total_pemakaian_roll ? data.total_pemakaian_roll : '-' ;
                    td18.innerHTML = data.short_roll ? data.short_roll : '-' ;
                    td19.innerHTML = data.piping ? data.piping : '-' ;
                    td20.innerHTML = data.remark ? data.remark : '-' ;
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

                    scannedItemTableTbody.appendChild(tr);

                    totalScannedItem++;

                    calculateConsActualGelaran(unit = latestUnit, piping = totalPiping, lembar = totalLembar, pActual = undefined, totalQtyFabric, totalQtyCut);
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
                        url: 'route("check-time-record-form-cut-input")/'+detailId,
                        type: 'get',
                        dataType: 'json',
                        success: function(res) {
                            openTimeRecordCondition();

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
                            averageSeconds = (parseFloat(summarySeconds)/parseFloat(lap)).toFixed(0);

                            $("#current_lembar_gelaran").val(lap).trigger('change');
                            $("#current_average_time").val((pad(parseInt(averageSeconds / 60)))+':'+(pad(averageSeconds % 60)))
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

                        timeRecordTableTbody.appendChild(tr);
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
                    timeRecordInterval = setInterval(setTime, 999);

                    startLapButton.classList.add("d-none")
                    nextLapButton.classList.remove('d-none');
                    nextLapButton.focus();

                    openLapTimeRecordCondition();

                    storeThisTimeRecord();
                }

                // -Next Lap Time Record-
                function addNewTimeRecord(data = null) {
                    summarySeconds += totalSeconds;
                    totalSeconds = 0;
                    lap++;

                    averageSeconds = (parseFloat(summarySeconds)/parseFloat(lap)).toFixed(0);

                    $("#current_lembar_gelaran").val(lap).trigger('change');
                    $("#current_average_time").val((pad(parseInt(averageSeconds / 60)))+':'+(pad(averageSeconds % 60)))

                    let tr = document.createElement('tr');
                    let td1 = document.createElement('td');
                    let td2 = document.createElement('td');
                    let td3 = document.createElement('td');
                    td1.innerHTML = lap;
                    td2.innerHTML = minutes.value + ':' + seconds.value;
                    td3.classList.add('d-none');
                    td3.innerHTML = `<input type='hidden' name="time_record[` + lap + `]" value="` + minutes.value + ':' + seconds.value + `" />`;
                    tr.appendChild(td1);
                    tr.appendChild(td2);
                    tr.appendChild(td3);

                    timeRecordTableTbody.appendChild(tr);

                    stopLapButton.disabled = false;

                    storeThisTimeRecord();
                }

                // -Stop Time Record-
                async function stopTimeRecord() {
                    clearTimeout(timeRecordInterval);

                    summarySeconds = 0;
                    totalSeconds = 0;
                    timeRecordInterval = 0;

                    seconds.value = 00;
                    minutes.value = 00;
                    lap = 0;

                    startLapButton.classList.remove('d-none');
                    nextLapButton.classList.add('d-none');

                    await backToProcessThree();

                    timeRecordTableTbody.innerHTML = "";
                }

                // Conditions :
                    function firstTimeRecordCondition() {
                        startLapButton.disabled = true;
                        nextLapButton.disabled = true;
                        stopLapButton.disabled = true;
                        finishProcessButton.disabled = false;

                        finishProcessButton.classList.remove("d-none");
                    }

                    function openTimeRecordCondition() {
                        startLapButton.disabled = false;
                        nextLapButton.disabled = true;
                        stopLapButton.disabled = true;
                    }

                    function openLapTimeRecordCondition() {
                        startLapButton.disabled = true;
                        nextLapButton.disabled = false;
                        stopLapButton.disabled = true;
                    }

                    function nextTimeRecordCondition() {
                        startLapButton.disabled = true;
                        nextLapButton.disabled = true;
                        stopLapButton.disabled = true;
                        finishProcessButton.disabled = false;
                    }

                    function lockTimeRecord() {
                        // for (let i = 0; i < summaryData.length; i++) {
                        //     document.getElementById("group-"+(i)).setAttribute("readonly", true);
                        //     document.getElementById("lembar-"+(i)).setAttribute("readonly", true);
                        // }
                        finishProcessButton.disabled = true;
                        finishProcessButton.innerHTML = "PENGERJAAN TELAH DISELESAIKAN";
                    }
    </script>
@endsection
