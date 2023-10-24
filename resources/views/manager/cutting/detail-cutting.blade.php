@extends('layouts.index')

@section('content')
    <div class="row g-3">
        <div class="d-flex gap-3 align-items-center">
            <h5 class="mb-1">Form Cut - {{ strtoupper($formCutInputData->name) }}</h5>
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
                                            <th colspan="8" class="text-center">Total</th>
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
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label label-input"><small><b>Operator</b></small></label>
                                        <input type="text" class="form-control form-control-sm border-input" name="operator" id="operator" value="{{ $formCutInputData->operator }}" readonly>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-labe label-calc"><small><b>Cons. Actual 1 Gelaran</b></small></label>
                                        <input type="text" class="form-control form-control-sm border-calc" name="cons_actual_gelaran" id="cons_actual_gelaran" readonly>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label label-calc"><small><b>Unit</b></small></label>
                                        <select class="form-select form-select-sm border-calc" name="unit_cons_actual_gelaran" id="unit_cons_actual_gelaran" disabled>
                                            <option value="meter">METER</option>
                                            <option value="yard">YARD</option>
                                            <option value="kgm">KGM</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label label-input"><small><b>Catatan</b></small></label>
                                        <textarea class="form-control border-input" id="notes" name="notes" rows="2">{{ $formCutInputData->app_notes }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            @if ($formCutInputData->app == "Y")
                <button class="btn btn-block btn-danger" id="approve-cutting" onclick="approveCutting({{ $id }}, 'N')"><i class="fa fa-times"></i> DISAPPROVE</button>
            @else
                <button class="btn btn-block btn-success" id="approve-cutting" onclick="approveCutting({{ $id }}, 'Y')"><i class="fa fa-check"></i> APPROVE</button>
            @endif
        </div>
    </div>
@endsection

@section('custom-script')
    <script>
        var summaryData = null;
        var totalSummaryData = 0;

        // -Ratio & Qty Cuy-
        var totalRatio = document.getElementById('total_ratio').value;
        var totalQtyCut = document.getElementById('total_qty_cut_ply').value;

        $(document).ready(() => {
            getSummary();
        });

        // -Calculate Cons. Actual 1 Gelaran-
        function calculateConsActualGelaran(unit = 0, lembar = 0, totalPemakaian = 0) {
            let unitVar = unit;
            let lembarVar = Number(lembar);
            let totalPemakaianVar = Number(totalPemakaian);
            let consActualGelaran = 0;

            // consActualGelaran = totalQtyCutVar > 0 ? (lembarVar * pActualConverted) / totalQtyCutVar : 0;
            consActualGelaran = totalPemakaianVar / lembarVar;

            document.getElementById("cons_actual_gelaran").value = consActualGelaran.round(2);
            document.getElementById("unit_cons_actual_gelaran").value = unitVar.toLowerCase();
        }

        // -Get Summary Data-
        function getSummary() {
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
            summaryData.forEach((data) => {
                appendSummaryItem(data)
            });
        }

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
        var latestUnit = "";

        function appendSummaryItem(data) {
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
            let td21 = document.createElement('td');
            td1.innerHTML = totalSummaryData + 1;
            td2.innerHTML = data.group ? data.group : '-';
            td3.innerHTML = data.id_roll ? data.id_roll : '-';
            td4.innerHTML = data.id_item ? data.id_item : '-';
            td5.innerHTML = data.lot ? data.lot : '-';
            td6.innerHTML = data.roll ? data.roll : '-';
            td7.innerHTML = data.qty ? data.qty : '-';
            td8.innerHTML = data.unit ? data.unit : '-';
            td9.innerHTML = data.sisa_gelaran ? data.sisa_gelaran : '-';
            td10.innerHTML = data.sambungan ? data.sambungan : '-';
            td11.innerHTML = data.est_amparan ? data.est_amparan : '-';
            td12.innerHTML = data.lembar_gelaran ? data.lembar_gelaran : '';
            td13.innerHTML = data.average_time ? data.average_time : '-';
            td14.innerHTML = data.kepala_kain ? data.kepala_kain : '-';
            td15.innerHTML = data.sisa_tidak_bisa ? data.sisa_tidak_bisa : '-';
            td16.innerHTML = data.reject ? data.reject : '-';
            td17.innerHTML = data.sisa_kain ? data.sisa_kain : '-';
            td18.innerHTML = data.total_pemakaian_roll ? data.total_pemakaian_roll : '-';
            td19.innerHTML = data.short_roll ? data.short_roll : '-';
            td20.innerHTML = data.piping ? data.piping : '-';
            td21.innerHTML = data.remark ? data.remark : '-';
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

            summaryItemTableTbody.appendChild(tr);

            totalSummaryData++;

            totalSisaGelaran += Number(data.sisa_gelaran);
            totalSambungan += Number(data.sambungan);
            totalEstAmparan += Number(data.est_amparan);
            totalAverageTime += (Number(data.average_time.slice(0, 2)) * 60) + Number(data.average_time.slice(3, 5));
            totalKepalaKain += Number(data.kepala_kain);
            totalSisaTidakBisa += Number(data.sisa_tidak_bisa);
            totalReject += Number(data.reject);
            totalSisaKain += Number(data.sisa_kain);
            totalTotalPemakaian += Number(data.total_pemakaian_roll);
            totalShortRoll += Number(data.short_roll);
            totalRemark += Number(data.remark);

            let averageTotalAverageTime = totalAverageTime/totalSummaryData;
            let averageTotalAverageTimeMinute = pad((averageTotalAverageTime/60).round(0));
            let averageTotalAverageTimeSecond = pad((averageTotalAverageTime%60).round(0));

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

            calculateConsActualGelaran(unit = latestUnit, lembar = totalLembar, totalTotalPemakaian);
        }

        function approveCutting(id, approveType) {
            let alertTitle = "";
            let alertText = "";
            let alertButtonText = "";
            let alertButtonColor = "";
            let approveNotes = document.getElementById("notes").value;

            if (approveType == "Y") {
                alertTitle = "Approve Form Cut?";
                alertText = "Yakin akan approve Form Cut ini?";
                alertButtonText = "Approve";
                alertButtonColor = "#2e8a57";
            } else {
                alertTitle = "Disapprove Form Cut";
                alertText = "Yakin akan disapprove Form Cut ini?";
                alertButtonText = "Disapprove";
                alertButtonColor = "#d33141";
            }

            Swal.fire({
                icon: 'info',
                title: alertTitle,
                text: alertText,
                showCancelButton: true,
                showConfirmButton: true,
                confirmButtonText: alertButtonText,
                confirmButtonColor: alertButtonColor,
            }).then(async (result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ route('approve-cutting') }}/' + id,
                        type: 'put',
                        data: {
                            approve_type: approveType,
                            approve_notes: approveNotes
                        },
                        dataType: 'json',
                        success: function(res) {
                            if (res.status == 200) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil',
                                    text: 'Form Cut telah di '+alertButtonText+' (Laman akan ditutup)',
                                    showCancelButton: false,
                                    showConfirmButton: true,
                                    confirmButtonText: 'Oke',
                                    timer: 3000,
                                    timerProgressBar: true
                                }).then((result) => {
                                    window.close();
                                })
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal',
                                    text: 'Terjadi kesalahan',
                                    showCancelButton: false,
                                    showConfirmButton: true,
                                    confirmButtonText: 'Oke',
                                });
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

        function unApproveCutting(id) {
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
                    $.ajax({
                        url: '{{ route('approve-cutting') }}/' + id,
                        type: 'put',
                        dataType: 'json',
                        success: function(res) {
                            if (res.status == 200) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil',
                                    text: 'Form Cut telah di Approve (Laman akan ditutup)',
                                    showCancelButton: false,
                                    showConfirmButton: true,
                                    confirmButtonText: 'Oke',
                                    timer: 3000,
                                    timerProgressBar: true
                                }).then((result) => {
                                    window.close();
                                })
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal',
                                    text: 'Terjadi kesalahan',
                                    showCancelButton: false,
                                    showConfirmButton: true,
                                    confirmButtonText: 'Oke',
                                });
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
    </script>
@endsection
