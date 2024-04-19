@extends('layouts.index')

@section('custom-link')
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@endsection

@section('content')
    <div class="row g-3">
        <div class="d-flex gap-3 justify-content-between align-items-center">
            <h5 class="text-sb fw-bold mb-1">Form Cut / {{ $formCutInputData->no_form . " / ". strtoupper($formCutInputData->name) }}</h5>
            <a href="{{ route('manage-cutting') }}" class="btn btn-sb-secondary btn-sm"><i class="fa fa-reply"></i> Kembali ke Completed Form</a>
        </div>
        <div class="col-md-6">
            <div class="card card-sb h-100" id="header-data-card">
                <div class="card-header">
                    <h3 class="card-title">Header Data</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
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
                        <!-- <input type="hidden" name="no_meja" id="no_meja" value="{{ $formCutInputData->no_meja }}" readonly> -->
                        <div class="col-12 col-md-12">
                            <div class="mb-3">
                            <label class="form-label"><small><b>Meja</b></small></label>
                                <select class="form-control select2bs4" id="no_meja" name="no_meja" style="width: 100%;">
                                    <option value="">Pilih Meja</option>
                                        @foreach ($meja as $m)
                                            <option value="{{ $m->id }}" {{ isset($formCutInputData) ? ($formCutInputData->no_meja ? ($formCutInputData->no_meja == $m->id ? "selected" : "") : "") : "" }}>{{ strtoupper($m->name) }}</option>
                                        @endforeach
                                    </option>
                                </select>
                            </div>
                        </div>
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
                                <input type="text" class="form-control form-control-sm" name="shell" id="shell" value="{{ $formCutInputData->shell ? strtoupper($formCutInputData->shell) : "-" }}" readonly>
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
                    <table id="ratio-datatable" class="table table-striped table-bordered table-sm w-100 text-center mt-3">
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
                                        $qtyPly = $item->ratio*$formCutInputData->qty_ply;
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
            <div class="card card-sb h-100" id="detail-data-card">
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
                                <input type="number" class="form-control form-control-sm border-input" name="p_act" id="p_act" value="{{ $formCutInputData->p_act }}" readonly>
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
                                <input type="text" class="form-control form-control-sm border-fetch" value="{{ strtoupper($formCutInputData->unit_comma_marker) }}" readonly>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="mb-3">
                                <label class="form-label label-input"><small><b>Comma Act</b></small></label>
                                <input type="number" class="form-control form-control-sm border-input" name="comma_act" id="comma_act" value="{{ $formCutInputData->comma_p_act }}" readonly>
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
                                <input type="number" class="form-control form-control-sm border-input" name="l_act" id="l_act" value="{{ $formCutInputData->l_act }}" readonly>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="mb-3">
                                <label class="form-label label-input"><small><b>Unit Act</b></small></label>
                                <input type="text" class="form-control form-control-sm border-input" name="unit_l_act" id="unit_l_act" value="{{ strtoupper($formCutInputData->unit_lebar_marker) }}" readonly>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="mb-3">
                                <label class="form-label label-fetch"><small><b>Gramasi</b></small></label>
                                <input type="text" class="form-control form-control-sm border-fetch" name="gramasi" id="gramasi" value="{{ $formCutInputData->gramasi }}" readonly>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="mb-3">
                                <label class="form-label label-fetch"><small><b>Cons WS</b></small></label>
                                <input type="text" class="form-control form-control-sm border-fetch" name="cons_ws" id="cons_ws" readonly>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="mb-3">
                                <label class="form-label label-fetch"><small><b>Cons Marker</b></small></label>
                                <input type="text" class="form-control form-control-sm border-fetch" name="cons_marker" id="cons_marker" value="{{ $formCutInputData->cons_marker }}" readonly>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="mb-3">
                                <label class="form-label label-calc"><small><b>Cons Act</b></small></label>
                                <input type="number" class="form-control form-control-sm border-calc" name="cons_act" id="cons_act" value="{{ round($formCutInputData->cons_act, 2) > 0 ? $formCutInputData->cons_act : ($totalCutQtyPly > 0 ? round($formCutInputData->p_act / $totalCutQtyPly, 2) : '0') }}" step=".01" readonly>
                            </div>
                        </div>
                        <div class="col-6 col-md-6">
                            <div class="mb-3">
                                <label class="form-label label-fetch"><small><b>Cons Piping</b></small></label>
                                <input type="number" class="form-control form-control-sm border-fetch" step=".01" name="cons_pipping" id="cons_pipping" value="{{ $formCutInputData->cons_piping ? $formCutInputData->cons_piping : 0 }}" readonly>
                            </div>
                        </div>
                        <div class="col-6 col-md-6">
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
                                        <input type="number" class="form-control form-control-sm border-calc" step=".01" name="est_pipping" id="est_pipping" value="{{ $formCutInputData->est_pipping ? $formCutInputData->est_pipping : "0.00" }}" readonly>
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
            <div class="card card-sb collapsed-card h-100" id="lost-time-card">
                <div class="card-header">
                    <h3 class="card-title">Loss Time</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                    </div>
                </div>
                <div class="card-body" style="display: block;">
                    <div class="w-100 h-100 table-responsive mt-3">
                        <form action="#" method="post" id="lost-time-form">
                            <input type="hidden" id="current_lost_time" name="current_lost_time">
                            <table class="table table-bordered table-sm" id="lostTimeTable">
                                <thead>
                                    <tr>
                                        <th>No.</th>
                                        <th>Waktu</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if ($lostTimeData->count() < 1)
                                        <tr>
                                            <th colspan="2" class="text-center">Data tidak ada</th>
                                        </tr>
                                    @else
                                        @foreach ($lostTimeData as $lost)
                                            <tr>
                                                <td>{{ $lost->lost_time_ke }}</td>
                                                <td>{{ $lost->waktu }}</td>
                                            </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="card card-sb" id="summary-card">
                <div class="card-header">
                    <h3 class="card-title">Summary</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse"><i
                                class="fas fa-minus"></i></button>
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
                                    <tfoot>
                                        <tr>
                                            <th colspan="7" class="text-center">Total</th>
                                            <th id="total-qty"></th>
                                            <th id="total-unit"></th>
                                            <th id="total-sisa-gelaran"></th>
                                            <th id="total-sambungan"></th>
                                            <th id="total-est-amparan"></th>
                                            <th id="total-lembar"></th>
                                            <th id="total-average-time"></th>
                                            <th id="total-kepala-kain"></th>
                                            <th id="total-sisa-tidak-bisa"></th>
                                            <th id="total-reject"></th>
                                            <th id="total-sisa-kain"></th>
                                            <th id="total-total-pemakaian"></th>
                                            <th id="total-short-roll"></th>
                                            <th id="total-piping"></th>
                                            <th id="total-remark"></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="row align-items-end">
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-labe label-calc"><small><b>Cons. Actual 1 Gelaran</b></small></label>
                                        <input type="text" class="form-control form-control-sm border-calc" name="cons_actual_gelaran" id="cons_actual_gelaran" readonly>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label label-calc"><small><b>Unit</b></small></label>
                                        <select class="form-select form-select-sm border-calc"
                                            name="unit_cons_actual_gelaran" id="unit_cons_actual_gelaran" disabled>
                                            <option value="meter">METER</option>
                                            <option value="yard">YARD</option>
                                            <option value="kgm">KGM</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-labe label-calc"><small><b>Kenaikan Cons. WS</b></small></label>
                                        <div class="input-group input-group-sm mb-3">
                                            <input type="text" class="form-control border-calc" name="cons_ws_uprate" id="cons_ws_uprate" readonly>
                                            <span class="input-group-text border-calc">%</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-labe label-calc"><small><b>Kenaikan Cons. Marker</b></small></label>
                                        <div class="input-group input-group-sm mb-3">
                                            <input type="text" class="form-control border-calc" name="cons_marker_uprate" id="cons_marker_uprate" readonly>
                                            <span class="input-group-text border-calc">%</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-labe label-calc"><small><b>Cons. Actual 1 Gelaran Tanpa Short Roll</b></small></label>
                                        <input type="text" class="form-control form-control-sm border-calc" name="cons_actual_gelaran_short_rolless" id="cons_actual_gelaran_short_rolless" readonly>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label label-calc"><small><b>Unit</b></small></label>
                                        <select class="form-select form-select-sm border-calc"
                                            name="unit_cons_actual_gelaran_short_rolless" id="unit_cons_actual_gelaran_short_rolless" disabled>
                                            <option value="meter">METER</option>
                                            <option value="yard">YARD</option>
                                            <option value="kgm">KGM</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-labe label-calc"><small><b>Kenaikan Cons. WS</b></small></label>
                                        <div class="input-group input-group-sm mb-3">
                                            <input type="text" class="form-control border-calc" name="cons_ws_uprate_nosr" id="cons_ws_uprate_nosr" readonly>
                                            <span class="input-group-text border-calc">%</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
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
                                        <input type="text" class="form-control form-control-sm border-input"
                                            name="operator" id="operator" value="{{ $formCutInputData->operator }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="card card-sb" id="spreading-form-card">
                <div class="card-header">
                    <h3 class="card-title">Spreading</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                    </div>
                </div>
                <div class="card-body" style="display: block;">
                    <form action="#" method="post" id="spreading-form">
                        <div class="row">
                            <input type="hidden" class="form-control" id="current_id" name="current_id">
                            <div class="col-3">
                                <div class="mb-3">
                                    <label class="form-label" id="current_id_roll_label"><small><b>Id Roll</b></small></label>
                                    <input type="text" class="form-control form-control-sm" id="current_id_roll" name="current_id_roll" onchange="fetchScan()">
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="mb-3">
                                    <label class="form-label" id="current_id_item_label"><small><b>Id Item</b></small></label>
                                    <input type="text" class="form-control form-control-sm" id="current_id_item" name="current_id_item">
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="mb-3">
                                    <label class="form-label" id="current_lot_label"><small><b>Lot</b></small></label>
                                    <input type="text" class="form-control form-control-sm" id="current_lot" name="current_lot">
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="mb-3">
                                    <label class="form-label" id="current_roll_label"><small><b>Roll</b></small></label>
                                    <input type="text" class="form-control form-control-sm" id="current_roll" name="current_roll">
                                </div>
                            </div>
                            <div class="col-6">
                                <label class="form-label" id="current_qty_real_label"><small><b>Qty</b></small></label>
                                <div class="d-flex mb-3">
                                    <div style="width: 60%;">
                                        <input type="number" class="form-control form-control-sm" style="border-radius: 3px 0 0 3px" id="current_qty_real" name="current_qty_real" onchange="setRollQtyConversion(this.value); calculateEstAmpar(); calculateShortRoll()" onkeyup="setRollQtyConversion(this.value); calculateEstAmpar(); calculateShortRoll()">
                                    </div>
                                    <div style="width: 40%;">
                                        <input type="text" class="form-control form-control-sm" style="border-radius: 0 3px 3px 0" id="current_unit" name="current_unit" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="form-label label-calc" id="current_qty_label"><small><b>Qty Konversi</b></small></label>
                                    <div class="d-flex mb-3">
                                        <div style="width: 60%">
                                            <input type="number" class="form-control form-control-sm border-calc" style="border-radius: 3px 0 0 3px" id="current_qty" name="current_qty" readonly>
                                        </div>
                                        <div style="width: 40%">
                                            <input type="text" class="form-control form-control-sm border-calc" style="border-radius: 0 3px 3px 0" id="current_unit_convert" name="current_unit_convert" value="METER" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="form-label label-input"><small><b>Sisa Gelaran</b></small></label>
                                    <div class="d-flex mb-3">
                                        <div style="width: 60%;">
                                            <input type="number" class="form-control form-control-sm border-input" style="border-radius: 3px 0 0 3px" id="current_sisa_gelaran" name="current_sisa_gelaran" step=".01" readonly
                                                onkeyup="
                                                    // restrictRemainPly();
                                                    calculateShortRoll();
                                                    calculateRemark();
                                                "
                                                onchange="
                                                    // restrictRemainPly();
                                                    calculateShortRoll();
                                                    calculateRemark();
                                                ">
                                        </div>
                                        <div style="width: 40%;">
                                            <input type="text" class="form-control form-control-sm border-input" style="border-radius: 0 3px 3px 0" id="current_sisa_gelaran_unit" name="current_sisa_gelaran_unit" step=".01" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="form-label label-input"><small><b>Sambungan</b></small></label>
                                    <div class="d-flex">
                                        <div style="width: 60%">
                                            <input type="number" class="form-control form-control-sm border-input" style="border-radius: 3px 0 0 3px" id="current_sambungan" name="current_sambungan" step=".01" readonly
                                                onkeyup="
                                                    calculateTotalPemakaian();
                                                    calculateShortRoll();
                                                    calculateSisaKain();
                                                "
                                                onchange="
                                                    calculateTotalPemakaian();
                                                    calculateShortRoll();
                                                    calculateSisaKain();
                                                ">
                                        </div>
                                        <div style="width: 40%">
                                            <input type="text" class="form-control form-control-sm border-input" style="border-radius: 0 3px 3px 0" id="current_sambungan_unit" name="current_sambungan_unit" step=".01" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="mb-3">
                                    <label class="form-label label-input"><small><b>Group</b></small></label>
                                    <input type="text" class="form-control form-control-sm border-input" id="current_group" name="current_group">
                                </div>
                            </div>
                            <div class="col-3 d-none">
                                <div class="mb-3">
                                    <label class="form-label label-input"><small><b>Group Number</b></small></label>
                                    <input type="text" class="form-control form-control-sm border-input" id="current_group_stocker" name="current_group_stocker">
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="mb-3">
                                    <label class="form-label label-calc"><small><b>Estimasi Amparan</b></small></label>
                                    <input type="number" class="form-control form-control-sm border-calc"
                                        id="current_est_amparan" name="current_est_amparan" step=".01" readonly>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="mb-3">
                                    <label class="form-label label-sb"><small><b>Lembar Gelaran</b></small></label>
                                    <input type="hidden" id="lembar_gelaran" name="lembar_gelaran">
                                    <input type="number" class="form-control form-control-sm border-sb" id="current_lembar_gelaran" name="current_lembar_gelaran"
                                        onkeyup="
                                            calculateTotalPemakaian();
                                            calculateShortRoll();
                                            calculateRemark();
                                            updatePlyProgress();
                                            calculateSisaKain();
                                        "
                                        onchange="
                                            calculateTotalPemakaian();
                                            calculateShortRoll();
                                            calculateRemark();
                                            updatePlyProgress();
                                            calculateSisaKain();
                                        ">
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="form-label label-sb"><small><b>Ply Progress</b></small></label>
                                    <div class="progress border border-sb" style="height: 31px">
                                        <p class="position-absolute"
                                            style="top: 59%;left: 50%;transform: translate(-50%, -50%);"
                                            id="current_ply_progress_txt"></p>
                                        <div class="progress-bar" style="background-color: #75baeb;" role="progressbar"
                                            id="current_ply_progress"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="mb-3">
                                    <label class="form-label label-input"><small><b>Kepala Kain</b></small></label>
                                    <div class="input-group input-group-sm mb-3">
                                        <input type="number" class="form-control border-input" id="current_kepala_kain" name="current_kepala_kain" step=".01"
                                            onkeyup="
                                                calculateTotalPemakaian();
                                                calculateShortRoll();
                                                calculateSisaKain();
                                            "
                                            onchange="
                                                calculateTotalPemakaian();
                                                calculateShortRoll();
                                                calculateSisaKain();
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
                                                calculateTotalPemakaian();
                                                calculateShortRoll();
                                                calculateRemark();
                                                calculateSisaKain();
                                            "
                                            onchange="
                                                calculateTotalPemakaian();
                                                calculateShortRoll();
                                                calculateRemark();
                                                calculateSisaKain();
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
                                                calculateTotalPemakaian();
                                                calculateShortRoll();
                                                calculateRemark();
                                                calculateSisaKain();
                                            "
                                            onchange="
                                                calculateTotalPemakaian();
                                                calculateShortRoll();
                                                calculateRemark();
                                                calculateSisaKain();
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
                                                calculateShortRoll();
                                                calculateRemark();
                                                calculateSisaKain();
                                            "
                                            onchange="
                                                calculateShortRoll();
                                                calculateRemark();
                                                calculateSisaKain();
                                            ">
                                        <span class="input-group-text input-group-unit"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="mb-3">
                                    <label class="form-label label-calc"><small><b>Tot. Pakai /Roll</b></small></label>
                                    <div class="input-group input-group-sm mb-3">
                                        <input type="number" class="form-control form-control-sm border-calc" id="current_total_pemakaian_roll" name="current_total_pemakaian_roll" step=".01" readonly>
                                        <span class="input-group-text input-group-unit"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="mb-3">
                                    <label class="form-label label-calc"><small><b>Short Roll +/-</b></small></label>
                                    <div class="input-group input-group-sm mb-3">
                                        <input type="number" class="form-control form-control-sm border-calc" id="current_short_roll" name="current_short_roll" step=".01" readonly>
                                        <span class="input-group-text input-group-unit"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="mb-3">
                                    <label class="form-label label-input"><small><b>Sisa Kain</b></small></label>
                                    <div class="input-group input-group-sm mb-3">
                                        <input type="number" class="form-control form-control-sm border-input" id="current_sisa_kain" name="current_sisa_kain" step=".01"
                                            onkeyup="
                                                calculateShortRoll();
                                                calculateRemark();
                                            "
                                            onchange="
                                                calculateShortRoll();
                                                calculateRemark();
                                            ">
                                        <span class="input-group-text input-group-unit"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="mb-3">
                                    <label class="form-label label-input"><small><b>Remark</b></small></label>
                                    <div class="input-group input-group-sm mb-3">
                                        <input type="number" class="form-control form-control-sm border-input" id="current_remark" name="current_remark" step=".01">
                                        <span class="input-group-text input-group-unit"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <button type="button" class="btn btn-success btn-sm btn-block my-3" id="stopLapButton" onclick="storeTimeRecord()"><i class="fa fa-check"></i> Simpan</button>
                                <button type="button" class="btn btn-no btn-sm btn-block my-3" id="stopLapButton" onclick="deleteTimeRecord()"><i class="fa fa-trash"></i> Hapus</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('custom-script')
    <!-- Select2 -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        var id = document.getElementById("id").value;

        var summaryData = null;
        var totalSummaryData = 0;

        // -Ratio & Qty Cuy-
        var totalRatio = document.getElementById('total_ratio').value;
        var totalQtyCut = document.getElementById('total_qty_cut_ply').value;

        $(document).ready(async () => {
            document.getElementById("loading").classList.remove("d-none");

            await getNumberData();

            await clearSpreadingForm();

            await lockItemSpreading();

            await getSummary();

            document.getElementById("loading").classList.add("d-none");

            // Select2 Autofocus
            $(document).on('select2:open', () => {
                document.querySelector('.select2-search__field').focus();
            });

            // Initialize Select2 Elements
            $('.select2').select2()

            // Initialize Select2BS4 Elements
            $('.select2bs4').select2({
                theme: 'bootstrap4',
                containerCssClass: 'form-control-sm'
            })
        });

        function fetchScan() {
            let idRoll = document.getElementById('current_id_roll').value;

            getScannedItem(idRoll);
        }

        // -Get Scanned Item Data-
        function getScannedItem(id) {
            if (isNotNull(id)) {
                return $.ajax({
                    url: '{{ route('get-scanned-form-cut-input') }}/' + id,
                    type: 'get',
                    dataType: 'json',
                    success: function(res) {
                        if (res) {
                            setSpreadingForm(res);
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: 'Roll tidak tersedia atau sudah habis.',
                                showCancelButton: false,
                                showConfirmButton: true,
                                confirmButtonText: 'Oke',
                            });
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

        // -Calculate Cons. Actual 1 Gelaran-
        async function calculateConsActualGelaran(unit, totalQtyFabric, totalKepalaKain, totalSisaTidakBisa, totalReject, totalSisaKain, totalPiping, totalShortRoll) {
            let unitVar = unit;
            let totalQtyFabricVar = totalQtyFabric ? Number(totalQtyFabric) : 0;
            let totalKepalaKainVar = totalKepalaKain ? Number(totalKepalaKain) : 0;
            let totalSisaTidakBisaVar = totalSisaTidakBisa ? Number(totalSisaTidakBisa) : 0;
            let totalRejectVar = totalReject ? Number(totalReject) : 0;
            let totalSisaKainVar = totalSisaKain ? Number(totalSisaKain) : 0;
            let totalPipingVar = totalPiping ? Number(totalPiping) : 0;
            let totalShortRollVar = totalShortRoll ? Number(totalShortRoll) : 0;

            let consActualGelaran = (totalQtyFabricVar - totalKepalaKainVar - totalSisaTidakBisaVar - totalRejectVar - totalSisaKainVar - totalPipingVar)/totalQtyCut;
            let consActualGelaranShortRolless = (totalQtyFabricVar - totalKepalaKainVar - totalSisaTidakBisaVar - totalRejectVar - totalSisaKainVar - totalPipingVar + totalShortRollVar)/totalQtyCut;

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

            if (unitConsActualGelaran != "METER" && unitConsActualGelaranShortRolless != "METER") {
                let consActualGelaranConverted = conversion(consActualGelaran, "METER", unitConsActualGelaran.toUpperCase());
                let consActualGelaranShortRollessConverted = conversion(consActualGelaranShortRolless, "METER", unitConsActualGelaranShortRolless.toUpperCase());

                consWsUpRate = (consActualGelaranConverted - consWs)/consWs * 100;
                consMarkerUpRate = ((consActualGelaranConverted - consMarker)/consMarker) * 100;

                consWsUpRateNoSr = ((consActualGelaranShortRollessConverted - consWs)/consWs) * 100;
                consMarkerUpRateNoSr = ((consActualGelaranShortRollessConverted - consMarker)/consMarker) * 100;
            } else {
                consWsUpRate = ((consActualGelaran - consWs)/consWs) * 100;
                consMarkerUpRate = ((consActualGelaran - consMarker)/consMarker) * 100;

                consWsUpRateNoSr = ((consActualGelaranShortRolless - consWs)/consWs) * 100;
                consMarkerUpRateNoSr = ((consActualGelaranShortRolless - consMarker)/consMarker) * 100;
            }

            document.getElementById('cons_ws_uprate').value = Number(consWsUpRate).round(2);
            document.getElementById('cons_marker_uprate').value = Number(consMarkerUpRate).round(2);
            document.getElementById('cons_ws_uprate_nosr').value = Number(consWsUpRateNoSr).round(2);
            document.getElementById('cons_marker_uprate_nosr').value = Number(consMarkerUpRateNoSr).round(2);
        }

        // -Get Summary Data-
        function getSummary(refresh = false) {
            if (refresh) {
                summaryData = null;
            }

            if (summaryData == null) {
                let noForm = document.getElementById("no_form").value;

                return $.ajax({
                    url: '{{ route('get-time-form-cut-input') }}/' + noForm,
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
            resetSummary();

            summaryData.forEach((data) => {
                appendSummaryItem(data)
            });
        }

        var totalRow = 0;
        var summaryItemTable = document.getElementById("scannedItemTable");
        var summaryItemTableTbody = summaryItemTable.getElementsByTagName("tbody")[0];
        var totalSisaGelaran = 0;
        var totalSambungan = 0;
        var totalEstAmparan = 0;
        var totalAverageTime = 0;
        var totalKepalaKain = 0;
        var totalSisaTidakBisa = 0;
        var totalReject = 0;
        var totalSisaKain = 0;
        var totalTotalPemakaian = 0;
        var totalShortRoll = 0;
        var totalRemark = 0;
        var totalLembar = 0;
        var totalPiping = 0;
        var totalQtyFabric = 0;
        var latestStatus = "";
        var latestUnit = "";

        function appendSummaryItem(data) {
            totalLembar += Number(data.lembar_gelaran);
            totalPiping += Number(data.piping);
            totalQtyFabric += Number(data.qty);
            latestStatus != 'extension complete' ? totalQtyFabric += Number(data.qty) : '';
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
            let td21 = document.createElement('td');
            let td22 = document.createElement('td');
            td1.innerHTML = (latestStatus != 'extension complete' ? totalSummaryData + 1 : "");
            td2.innerHTML = data.group_roll ? data.group_roll : '-';
            td3.innerHTML = data.group_stocker ? data.group_stocker : '-';
            td4.innerHTML = data.id_roll ? data.id_roll : '-';
            td5.innerHTML = data.id_item ? data.id_item : '-';
            td6.innerHTML = data.lot ? data.lot : '-';
            td7.innerHTML = data.roll ? data.roll : '-';
            td8.innerHTML = data.qty ? data.qty : '-';
            td9.innerHTML = data.unit ? data.unit : '-';
            td10.innerHTML = data.sisa_gelaran ? data.sisa_gelaran : '-';
            td11.innerHTML = data.sambungan ? data.sambungan : '-';
            td12.innerHTML = data.est_amparan ? data.est_amparan : '-';
            td13.innerHTML = data.lembar_gelaran ? data.lembar_gelaran : '';
            td14.innerHTML = data.average_time ? data.average_time : '-';
            td15.innerHTML = data.kepala_kain ? data.kepala_kain : '-';
            td16.innerHTML = data.sisa_tidak_bisa ? data.sisa_tidak_bisa : '-';
            td17.innerHTML = data.reject ? data.reject : '-';
            td18.innerHTML = data.sisa_kain ? data.sisa_kain : '-';
            td19.innerHTML = data.total_pemakaian_roll ? data.total_pemakaian_roll : '-';
            td20.innerHTML = data.short_roll ? data.short_roll : '-';
            td21.innerHTML = data.piping ? data.piping : '-';
            td22.innerHTML = data.remark ? data.remark : '-';
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

            tr.onclick = async function() {
                clearSpreadingForm();

                for (let i = 0; i < summaryItemTableTbody.children.length; i++) {
                    summaryItemTableTbody.children[i].classList.remove('selected');
                }

                this.classList.add('selected');

                setSpreadingForm(data);

                location.href = '#spreading-form-card';
            };

            summaryItemTableTbody.appendChild(tr);

            totalRow++;
            latestStatus != 'extension complete' ? totalSummaryData++ : '';

            totalSisaGelaran += Number(data.sisa_gelaran);
            totalSambungan += Number(data.sambungan);
            totalEstAmparan += Number(data.est_amparan);
            totalAverageTime += (Number(data.average_time.slice(0, 2)) * 60) + Number(data.average_time.slice(3, 5));
            totalKepalaKain += Number(data.kepala_kain);
            totalSisaTidakBisa += Number(data.sisa_tidak_bisa);
            totalReject += Number(data.reject);
            totalSisaKain += Number(data.sisa_kain);
            totalTotalPemakaian += Number(data.total_pemakaian_roll);
            Number(data.short_roll) < 0 ? totalShortRoll += Number(data.short_roll) : "";
            totalRemark += Number(data.remark);

            let averageTotalAverageTime = totalAverageTime / totalRow;
            let averageTotalAverageTimeMinute = averageTotalAverageTime.round(0) >= 60 ? pad((averageTotalAverageTime.round(0) / 60).round(0)) : pad(0);
            let averageTotalAverageTimeSecond = averageTotalAverageTime.round(0) >= 60 ? pad((averageTotalAverageTime.round(0) % 60).round(0)) : pad(averageTotalAverageTime.round(0));

            document.getElementById("total-qty").innerText = Number(totalQtyFabric).round(2);
            document.getElementById("total-unit").innerText = latestUnit;
            document.getElementById("total-sisa-gelaran").innerText = Number(totalSisaGelaran).round(2);
            document.getElementById("total-sambungan").innerText = Number(totalSambungan).round(2);
            document.getElementById("total-est-amparan").innerText = Number(totalEstAmparan).round(2);
            document.getElementById("total-lembar").innerText = Number(totalLembar).round(2);
            document.getElementById("total-average-time").innerText = averageTotalAverageTimeMinute+":"+averageTotalAverageTimeSecond;
            document.getElementById("total-kepala-kain").innerText = Number(totalKepalaKain).round(2);
            document.getElementById("total-sisa-tidak-bisa").innerText = Number(totalSisaTidakBisa).round(2);
            document.getElementById("total-reject").innerText = Number(totalReject).round(2);
            document.getElementById("total-sisa-kain").innerText = Number(totalSisaKain).round(2);
            document.getElementById("total-total-pemakaian").innerText = Number(totalTotalPemakaian).round(2);
            document.getElementById("total-short-roll").innerText = Number(totalShortRoll).round(2);
            document.getElementById("total-piping").innerText = Number(totalPiping).round(2);
            document.getElementById("total-remark").innerText = Number(totalRemark).round(2);


            calculateConsActualGelaran(latestUnit, totalQtyFabric, totalKepalaKain, totalSisaTidakBisa, totalReject, totalSisaKain, totalPiping, totalShortRoll);

            latestStatus = data.status;
        }

        function resetSummary() {
            totalSisaGelaran = 0;
            totalSambungan = 0;
            totalEstAmparan = 0;
            totalAverageTime = 0;
            totalKepalaKain = 0;
            totalSisaTidakBisa = 0;
            totalReject = 0;
            totalSisaKain = 0;
            totalTotalPemakaian = 0;
            totalShortRoll = 0;
            totalRemark = 0;
            totalLembar = 0;
            totalPiping = 0;
            totalQtyFabric = 0;
            latestUnit = "";
            summaryItemTableTbody.innerHTML = '';
        }

        // -Set Spreading Form-
        function setSpreadingForm(data) {
            openItemSpreading();

            // spreading form data set
            let convertedQty = rollQtyConversion(data.qty, data.unit);

            data.id ? document.getElementById("current_id").value = data.id : '';
            data.id_roll ? document.getElementById("current_id_roll").value = data.id_roll : '';
            data.group_roll ? document.getElementById("current_group").value = data.group_roll : '';
            data.group_stocker ? document.getElementById("current_group_stocker").value = data.group_stocker : '';
            data.id_item ? document.getElementById("current_id_item").value = data.id_item : '';
            data.lot ? document.getElementById("current_lot").value = data.lot : '';
            data.roll ? document.getElementById("current_roll").value = data.roll : '';
            data.qty ? document.getElementById("current_qty").value = convertedQty : '';
            data.qty ? document.getElementById("current_qty_real").value = data.qty : '';
            data.unit ? document.getElementById("current_unit").value = data.unit : '';
            data.unit ? document.getElementById("current_sisa_gelaran_unit").value = (data.unit != "KGM" ? "METER" : "KGM") : '';
            data.unit ? document.getElementById("current_sambungan_unit").value = (data.unit != "KGM" ? "METER" : "KGM") : '';
            data.sisa_gelaran ? document.getElementById("current_sisa_gelaran").value = data.sisa_gelaran : '';
            data.sambungan ? document.getElementById("current_sambungan").value = data.sambungan : '';
            data.est_amparan ? document.getElementById("current_est_amparan").value = data.est_amparan : '';
            data.lembar_gelaran ? document.getElementById("lembar_gelaran").value = data.lembar_gelaran : '';
            data.lembar_gelaran ? document.getElementById("current_lembar_gelaran").value = data.lembar_gelaran : '';
            data.kepala_kain ? document.getElementById("current_kepala_kain").value = data.kepala_kain : '';
            data.sisa_tidak_bisa ? document.getElementById("current_sisa_tidak_bisa").value = data.sisa_tidak_bisa : '';
            data.reject ? document.getElementById("current_reject").value = data.reject : '';
            data.sisa_kain ? document.getElementById("current_sisa_kain").value = data.sisa_kain : '';
            data.total_pemakaian_roll ? document.getElementById("current_total_pemakaian_roll").value = data.total_pemakaian_roll : '';
            data.short_roll ? document.getElementById("current_short_roll").value = data.short_roll : '';
            data.piping ? document.getElementById("current_piping").value = data.piping : '';
            data.remark ? document.getElementById("current_remark").value = data.remark : '';

            // simplified unit name
            let unitSimplified = data.unit != "KGM" ? "M" : "KG";

            let inputGroupUnit = document.getElementsByClassName("input-group-unit");

            for (var i = 0; i < inputGroupUnit.length; i++) {
                inputGroupUnit[i].innerText = unitSimplified;
            }

            // updating est ampar & updating short roll & ply progress bar
            calculateEstAmpar();

            calculateShortRoll();

            updatePlyProgress();
        }

        // -Clear Spreading Form-
        function clearSpreadingForm() {
            lockItemSpreading();

            document.getElementById("current_group").value = "";
            document.getElementById("current_group_stocker").value = "";
            document.getElementById("current_id_roll").value = "";
            document.getElementById("current_id_item").value = "";
            document.getElementById("current_lot").value = "";
            document.getElementById("current_roll").value = "";
            document.getElementById("current_qty").value = "";
            document.getElementById("current_qty_real").value = "";
            document.getElementById("current_unit").value = "";
            document.getElementById("current_sisa_gelaran").value = 0;
            document.getElementById("current_sisa_gelaran_unit").value = "";
            document.getElementById("current_sambungan").value = 0;
            document.getElementById("current_sambungan_unit").value = "";
            document.getElementById("current_est_amparan").value = 0;
            document.getElementById("current_lembar_gelaran").value = 0;
            document.getElementById("current_kepala_kain").value = 0;
            document.getElementById("current_sisa_tidak_bisa").value = 0;
            document.getElementById("current_reject").value = 0;
            document.getElementById("current_sisa_kain").value = "";
            document.getElementById("current_total_pemakaian_roll").value = 0;
            document.getElementById("current_short_roll").value = 0;
            document.getElementById("current_piping").value = 0;
            document.getElementById("current_remark").value = 0;

            let inputGroupUnit = document.getElementsByClassName("input-group-unit");

            for (var i = 0; i < inputGroupUnit.length; i++) {
                inputGroupUnit[i].innerText = "";
            }
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
        }

        function conversion(qty, unit, unitBefore) {

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
            consActual = totalQtyCut > 0 ? pActualFinal / totalRatio : 0;

            document.getElementById('cons_act').value = consActual.round(2);
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

                pActualConverted = pActualConversion(pActualVar, unitPActualVar, commaActualVar, lActualVar, gramasiVar, unitQtyVar);
            }

            let estAmpar = pActualConverted > 0 ? qtyVar / pActualConverted : 0;

            document.getElementById("current_est_amparan").value = estAmpar.round(2);
        }

        // -Calculate Total Pemakaian Roll-
        function calculateTotalPemakaian() {
            let lembarGelaranVar = Number(document.getElementById("current_lembar_gelaran").value);
            let pActualVar = Number(document.getElementById("p_act").value);
            let kepalaKainVar = Number(document.getElementById("current_kepala_kain").value);
            let sisaTidakBisaVar = Number(document.getElementById("current_sisa_tidak_bisa").value);
            let rejectVar = Number(document.getElementById("current_reject").value);
            let lActualVar = Number(document.getElementById("l_act").value);
            let gramasiVar = Number(document.getElementById("gramasi").value);
            let unitQtyVar = document.getElementById("current_unit").value;
            let unitPActualVar = document.getElementById("unit_p_act").value;
            let commaActualVar = Number(document.getElementById("comma_act").value);

            let pActualConverted = 0;

            if (unitQtyVar != "KGM") {
                pActualConverted = pActualCommaActual(pActualVar, unitPActualVar, commaActualVar);
            } else {
                qtyVar = Number(document.getElementById("current_qty_real").value);

                pActualConverted = pActualConversion(pActualVar, unitPActualVar, commaActualVar, lActualVar, gramasiVar, unitQtyVar);
            }

            // let totalPemakaian = lembarGelaranVar * pActualConverted + kepalaKainVar + sisaTidakBisaVar + rejectVar;
            let totalPemakaian = lembarGelaranVar * pActualConverted;

            document.getElementById("current_total_pemakaian_roll").value = totalPemakaian.round(2);
        }

        // -Calculate Short Roll-
        function calculateShortRoll() {
            let lembarGelaranVar = Number(document.getElementById("current_lembar_gelaran").value);
            let pActualVar = Number(document.getElementById("p_act").value);
            let kepalaKainVar = Number(document.getElementById("current_kepala_kain").value);
            let pipingVar = Number(document.getElementById("current_piping").value);
            let sisaKainVar = Number(document.getElementById("current_sisa_kain").value);
            let rejectVar = Number(document.getElementById("current_reject").value);
            let sambunganVar = Number(document.getElementById("current_sambungan").value);
            let qtyVar = Number(document.getElementById("current_qty").value);
            let unitQtyVar = document.getElementById("current_unit").value;
            let gramasiVar = Number(document.getElementById("gramasi").value);
            let unitPActualVar = document.getElementById("unit_p_act").value;
            let lActualVar = Number(document.getElementById("l_act").value);
            let commaActualVar = Number(document.getElementById("comma_act").value);
            let sisaGelaranVar = Number(document.getElementById("current_sisa_gelaran").value);
            let sisaTidakBisaVar = Number(document.getElementById("current_sisa_tidak_bisa").value);

            let pActualConverted = 0;

            if (document.getElementById("current_sambungan").value > 0) {
                pActualConverted = document.getElementById("current_sambungan").value;
            } else {
                if (unitQtyVar != "KGM") {
                    pActualConverted = pActualCommaActual(pActualVar, unitPActualVar, commaActualVar);
                } else {
                    qtyVar = Number(document.getElementById("current_qty_real").value);

                    pActualConverted = pActualConversion(pActualVar, unitPActualVar, commaActualVar, lActualVar, gramasiVar, unitQtyVar);
                }
            }

            // let shortRoll = pActualConverted * lembarGelaranVar + kepalaKainVar + pipingVar + sisaKainVar + rejectVar + sambunganVar - qtyVar;
            let shortRoll = ((pActualConverted * lembarGelaranVar) + sisaGelaranVar + sambunganVar + kepalaKainVar + sisaTidakBisaVar + rejectVar + sisaKainVar + pipingVar) - qtyVar;

            if (sambunganVar != 0) {
                shortRoll = 0;
            }

            document.getElementById("current_short_roll").value = isNaN(shortRoll.round(2)) ? 0 : shortRoll.round(2);
        }

        // -Calculate Remark-
        function calculateRemark() {
            let lembarGelaranVar = Number(document.getElementById("current_lembar_gelaran").value);
            let pActualVar = Number(document.getElementById("p_act").value);
            let kepalaKainVar = Number(document.getElementById("current_kepala_kain").value);
            let pipingVar = Number(document.getElementById("current_piping").value);
            let sisaKainVar = Number(document.getElementById("current_sisa_kain").value);
            let rejectVar = Number(document.getElementById("current_reject").value);
            let sambunganVar = Number(document.getElementById("current_sambungan").value);
            let qtyVar = Number(document.getElementById("current_qty").value);
            let unitQtyVar = document.getElementById("current_unit").value;
            let gramasiVar = Number(document.getElementById("gramasi").value);
            let unitPActualVar = document.getElementById("unit_p_act").value;
            let lActualVar = Number(document.getElementById("l_act").value);
            let commaActualVar = Number(document.getElementById("comma_act").value);
            let sisaGelaranVar = Number(document.getElementById("current_sisa_gelaran").value);
            let sisaTidakBisaVar = Number(document.getElementById("current_sisa_tidak_bisa").value);

            let pActualConverted = 0;

            if (unitQtyVar != "KGM") {
                pActualConverted = pActualCommaActual(pActualVar, unitPActualVar, commaActualVar);
            } else {
                qtyVar = Number(document.getElementById("current_qty_real").value);

                pActualConverted = pActualConversion(pActualVar, unitPActualVar, commaActualVar, lActualVar, gramasiVar, unitQtyVar);
            }

            let remark = ((pActualConverted * lembarGelaranVar) + sisaGelaranVar + sambunganVar + kepalaKainVar + sisaTidakBisaVar + rejectVar + sisaKainVar + pipingVar);

            document.getElementById("current_remark").value = remark.round(2);
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

            if (unitQtyVar != "KGM") {
                pActualConverted = pActualCommaActual(pActualVar, unitPActualVar, commaActualVar);
            } else {
                qtyVar = Number(document.getElementById("current_qty_real").value);

                pActualConverted = pActualConversion(pActualVar, unitPActualVar, commaActualVar, lActualVar, gramasiVar, unitQtyVar);
            }

            let sisaKain = qtyVar - ((pActualConverted * lembarGelaranVar) + kepalaKainVar + sisaTidakBisaVar + rejectVar + rejectVar + pipingVar);

            document.getElementById("current_sisa_kain").value = sisaKain.round(2);
        }

        // -Calculate Sambungan-
        function calculateSambungan(sisaGelaran, unitSisaGelaran) {
            let sisaGelaranVar = sisaGelaran > 0 ? Number(sisaGelaran) : Number(document.getElementById("current_sisa_gelaran").value);
            let unitSisaGelaranVar = unitSisaGelaran ? unitSisaGelaran : document.getElementById("current_sisa_gelaran_unit").value;
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
                pActualConverted = pActualConversion(pActualVar, unitPActualVar, commaActualVar, lActualVar, gramasiVar, unitQtyVar);
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

            let estSambungan = pActualConverted - sisaGelaranConverted;

            return estSambungan.round(2);
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

        // -Update Ply Progress-
        function updatePlyProgress() {
            let originalLembar = Number($("#lembar_gelaran").val());
            let currentLembar = Number($("#current_lembar_gelaran").val());
            let qtyPly = Number($("#qty_ply").val());

            document.getElementById("current_ply_progress_txt").innerText = ((totalLembar - originalLembar) + currentLembar) + "/" + qtyPly;
            document.getElementById("current_ply_progress").style.width = Number(qtyPly) > 0 ? ((Number( totalLembar - originalLembar) + currentLembar) / Number(qtyPly) * 100) + "%" : "0%";
        }

        // -Store Time Record Transaction-
        function storeTimeRecord() {
            document.getElementById("loading").classList.remove("d-none");

            clearModified();

            let spreadingForm = new FormData(document.getElementById("spreading-form"));

            let dataObj = {
                "p_act": $("#p_act").val(),
                "unit_p_act": $("#unit_p_act").val(),
                "comma_act": $("#comma_act").val(),
                "no_form_cut_input": $("#no_form").val(),
                "no_meja": $("#no_meja").val(),
                "color_act": $("#color_act").val(),
                "detail_item": $("#detail_item").val(),
                "operator": $('#operator').val(),
                "consAct": $('#cons_actual_gelaran').val(),
                "unitConsAct": $('#unit_cons_actual_gelaran').val(),
                "totalLembar": totalLembar
            }

            spreadingForm.forEach((value, key) => dataObj[key] = value);

            return $.ajax({
                url: '{{ route('update-spreading-form') }}',
                type: 'post',
                dataType: 'json',
                data: dataObj,
                success: async function(res) {
                    if (res) {
                        await clearSpreadingForm();

                        await getSummary(true);

                        await finishProcess()
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

        // -Lock Item input on Spreading Form-
        function lockItemSpreading() {
            document.getElementById("current_id_roll").setAttribute("readonly", true);
            document.getElementById("current_id_item").setAttribute("readonly", true);
            document.getElementById("current_lot").setAttribute("readonly", true);
            document.getElementById("current_roll").setAttribute("readonly", true);
            document.getElementById("current_qty").setAttribute("readonly", true);
            document.getElementById("current_qty_real").setAttribute("readonly", true);
        }

        // -Open Item input on Spreading Form-
        function openItemSpreading() {
            document.getElementById("current_id_roll").removeAttribute("readonly");
            document.getElementById("current_id_item").removeAttribute("readonly");
            document.getElementById("current_lot").removeAttribute("readonly");
            document.getElementById("current_roll").removeAttribute("readonly");
            document.getElementById("current_qty").removeAttribute("readonly");
            document.getElementById("current_qty_real").removeAttribute("readonly");
        }

        function deleteTimeRecord() {
            let idRoll = document.getElementById("current_id").value;

            Swal.fire({
                icon: 'error',
                title: 'Hapus roll?',
                showCancelButton: true,
                showConfirmButton: true,
                confirmButtonText: 'Hapus',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#fa4456',
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById("loading").classList.remove("d-none");

                    $.ajax({
                        url: '{{ route('destroy-spreading-roll') }}/'+idRoll,
                        type: 'POST',
                        data: {
                            _method: 'DELETE'
                        },
                        success: async function(res) {
                            if (res.status == 200) {

                                await clearSpreadingForm();

                                await getSummary(true);

                                await finishProcess();

                            }
                        }, error: function (jqXHR) {
                            let res = jqXHR.responseJSON;
                            let message = '';

                            for (let key in res.errors) {
                                message = res.errors[key];
                            }

                            iziToast.error({
                                title: 'Error',
                                message: 'Terjadi kesalahan. '+message,
                                position: 'topCenter'
                            });
                        }
                    })
                }
            })
        }

        // -Finish Process-
        function finishProcess() {
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
            $.ajax({
                url: '{{ route('finish-update-spreading-form') }}/' + id,
                type: 'put',
                dataType: 'json',
                data: {
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
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: 'Data berhasil diubah',
                            showCancelButton: false,
                            showConfirmButton: true,
                            confirmButtonText: 'Oke',
                            timer: 3000,
                            timerProgressBar: true
                        }).then((result) => {
                            location.reload();
                        })
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
    </script>
@endsection
