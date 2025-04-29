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
    <div class="row g-3">
        <div class="d-flex gap-3 align-items-center">
            <h5 class="mb-1">Form Cut Pilot</h5>
            <button class="btn btn-sm btn-success" id="start-process" onclick="startProcess()">Buat Form Cut Pilot</button>
            {{-- <button class="btn btn-sm btn-sb-secondary d-none" id="create-new-form" onclick="createNewForm()">Buat Form Cut Pilot Baru</button> --}}
        </div>
        <div class="col-md-6">
            <div class="card card-sb d-none" id="header-data-card">
                <div class="card-header">
                    <h3 class="card-title">Header Data</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body" style="display: block;">
                    @php
                        $thisActCosting = (isset($formCutInputData) && $formCutInputData) ? $actCostingData->where('id', $formCutInputData->act_costing_id)->first() : null;
                        $thisMarkerDetails = (isset($formCutInputData) && $formCutInputData) ? $markerDetailData->where('kode_marker', $formCutInputData->id_marker) : null;
                    @endphp
                    <form action="{{ route('store-marker-pilot-form-cut') }}" method="post" onsubmit="submitMarkerForm(this, event)" id="store-marker">
                        <div class="row align-items-end">
                            {{-- Form Information --}}
                            <input type="hidden" name="id" id="id" value="{{ (isset($formCutInputData) && $formCutInputData) ? ($formCutInputData->form_id ? $formCutInputData->form_id : "") : "" }}" readonly>
                            <input type="hidden" name="status" id="status" value="{{ (isset($formCutInputData) && $formCutInputData) ? ($formCutInputData->status ? $formCutInputData->status : "") : "" }}" readonly>
                            <input type="hidden" name="no_meja" id="no_meja" value="{{ (isset($formCutInputData) && $formCutInputData) ? ($formCutInputData->no_meja ? $formCutInputData->no_meja : Auth::user()->id) : Auth::user()->id }}" readonly>
                            <div class="col-6 col-md-4">
                                <div class="mb-3">
                                    <label class="form-label"><small><b>Start</b></small></label>
                                    <input type="text" class="form-control form-control-sm" name="start" id="start-time" value="" readonly>
                                </div>
                            </div>
                            <div class="col-6 col-md-4">
                                <div class="mb-3">
                                    <label class="form-label"><small><b>Finish</b></small></label>
                                    <input type="text" class="form-control form-control-sm" name="finish" id="finish-time" value="" readonly>
                                </div>
                            </div>
                            <div class="col-6 col-md-4">
                                <div class="mb-3">
                                    <label class="form-label"><small><b>Shell</b></small></label>
                                    <select class="form-select form-select-sm" name="shell" id="shell">
                                        <option value="a">A</option>
                                        <option value="b">B</option>
                                        <option value="c">C</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-6 col-md-6">
                                <div class="mb-3">
                                    <label class="form-label "><small><b>No. Form</b></small></label>
                                    <input type="text" class="form-control form-control-sm " name="no_form" id="no_form" value="{{ (isset($formCutInputData) && $formCutInputData) ? ($formCutInputData->no_form ? $formCutInputData->no_form : "") : "" }}" readonly>
                                </div>
                            </div>
                            <div class="col-6 col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><small><b>Tanggal</b></small></label>
                                    <input type="date" class="form-control form-control-sm" value="{{ (isset($formCutInputData) && $formCutInputData) ? ($formCutInputData->tgl_form_cut ? $formCutInputData->tgl_form_cut : date('Y-m-d')) : date('Y-m-d') }}" name="tgl_form" readonly>
                                </div>
                            </div>

                            {{-- Marker Form --}}
                            <div class="col-6 col-md-6">
                                <div class="mb-3">
                                    <label class="form-label "><small><b>Kode Marker</b></small></label>
                                    <input type="text" class="form-control form-control-sm" id="id_marker" value="{{ (isset($formCutInputData) && $formCutInputData) ? ($formCutInputData->kode ? $formCutInputData->kode : date('Y-m-d')) : date('Y-m-d') }}" readonly>
                                </div>
                            </div>
                            <div class="col-6 col-md-6">
                                <div class="mb-3">
                                    <label class="form-label "><small><b>No. WS</b></small></label>
                                    <input type="text" name="no_ws" id="no_ws" class="form-control form-control-sm d-none" value="{{ (isset($formCutInputData) && $formCutInputData) ? ($formCutInputData->act_costing_ws ? $formCutInputData->act_costing_ws : '') : '' }}" readonly>
                                    <select class="form-control select2bs4" id="act_costing_id" name="act_costing_id" style="width: 100%;">
                                        <option value="" selected>Pilih WS</option>
                                        @foreach ($orders as $order)
                                            <option value="{{ $order->id }}">
                                                {{ $order->kpno }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <input type="text" class="d-none" id="act_costing_id_text" value="{{ (isset($formCutInputData) && $formCutInputData) ? ($formCutInputData->act_costing_id ? $formCutInputData->act_costing_id : '') : '' }}" readonly>
                                </div>
                            </div>
                            <div class="col-6 col-md-6">
                                <div class="mb-3">
                                    <label class="form-label "><small><b>Color</b></small></label>
                                    <select class="form-control select2bs4" id="color" name="color" style="width: 100%;">
                                        <option selected="selected" value="">Pilih Color</option>
                                        {{-- select 2 option --}}
                                    </select>
                                    <input type="text" class="form-control form-control-sm d-none" id="color_text" value="{{ (isset($formCutInputData) && $formCutInputData) ? ($formCutInputData->color ? $formCutInputData->color : '') : '' }}" readonly>
                                </div>
                            </div>
                            <div class="col-6 col-md-6">
                                <div class="mb-3">
                                    <label class="form-label "><small><b>Panel</b></small></label>
                                    <select class="form-control select2bs4" id="panel" name="panel" style="width: 100%;">
                                        <option selected="selected" value="">Pilih Panel</option>
                                        {{-- select 2 option --}}
                                    </select>
                                    <input type="text" class="form-control form-control-sm d-none" id="panel_text" value="{{ (isset($formCutInputData) && $formCutInputData) ? ($formCutInputData->panel ? $formCutInputData->panel : '') : '' }}" readonly>
                                </div>
                            </div>
                            <div class="col-6 col-md-6">
                                <div class="mb-3">
                                    <label class="form-label "><small><b>Buyer</b></small></label>
                                    <input type="text" class="form-control form-control-sm " name="buyer" id="buyer" value="{{ (isset($formCutInputData) && $formCutInputData) ? ($formCutInputData->buyer ? $formCutInputData->buyer : '') : '' }}" readonly>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="mb-3">
                                    <label class="form-label "><small><b>Style</b></small></label>
                                    <input type="text" class="form-control form-control-sm " name="style" id="style" value="{{ (isset($formCutInputData) && $formCutInputData) ? ($formCutInputData->style ? $formCutInputData->style : '') : '' }}" readonly>
                                </div>
                            </div>
                            <div class="col-4 col-md-4">
                                <div class="mb-3">
                                    <label class="form-label "><small><b>Tipe Marker</b></small></label>
                                    <select class="form-select form-select-sm" name="tipe_marker" id="tipe_marker">
                                        <option value="pilot marker" selected>Pilot Marker</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-4 col-md-4">
                                <div class="mb-3">
                                    <label class="form-label"><small><b>PO</b></small></label>
                                    <input type="text" class="form-control form-control-sm" name="po" id="po" value="{{ (isset($formCutInputData) && $formCutInputData) ? ($formCutInputData->po ? $formCutInputData->po : '') : '' }}">
                                </div>
                            </div>
                            <div class="col-4 col-md-4">
                                <div class="mb-3">
                                    <label class="form-label"><small><b>QTY Gelar Marker</b></small></label>
                                    <input type="text" class="form-control form-control-sm" name="gelar_qty" id="gelar_qty" {{ (isset($formCutInputData) && $formCutInputData) ? ($formCutInputData->gelar_qty ? "value=".$formCutInputData->gelar_qty." readonly" : '') : "" }} onchange="calculateAllRatio(this)" onkeyup="calculateAllRatio(this)">
                                </div>
                            </div>
                        </div>
                        @php
                            $totalRatio = 0;
                            $totalCutQty = 0;
                            $totalCutQtyPly = 0;
                        @endphp

                        <div class="table-responsive {{ isset($formCutInputData) && $formCutInputData ? ($formCutInputData->marker ? 'd-none' : '') : '' }}">
                            <table id="ratio-datatable" class="table table-bordered table-striped table w-100">
                                <thead>
                                    <tr>
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
                                        <th>Total</th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        @if (isset($formCutInputData) && $formCutInputData && $formCutInputData->marker)
                            <div class="table-responsive">
                                <table id="ratio-datatable" class="table table-bordered table-striped table w-100">
                                    <thead>
                                        <tr>
                                            <th>Size</th>
                                            <th>Ratio</th>
                                            <th>Qty Output</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $totalRatio = 0;
                                            $totalCutQtyPly = 0;
                                        @endphp
                                        @foreach ($thisMarkerDetails as $item)
                                            <tr>
                                                @php
                                                    $totalRatio += $item->ratio;
                                                    $qtyPly = $item->ratio*$formCutInputData->qty_ply;
                                                    $totalCutQtyPly += $qtyPly;
                                                @endphp
                                                <td>{{ $item->size }}</td>
                                                <td>{{ $item->ratio }}</td>
                                                <td>{{ $qtyPly }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th class="text-center">Total</th>
                                            <th>{{ $totalRatio }}</th>
                                            <th>{{ $totalCutQtyPly }}</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        @endif

                        {{-- Marker Number Information --}}
                        <input type="hidden" name="cons_ws_marker" id="cons_ws_marker" value="{{ (isset($formCutInputData) && $formCutInputData) ? ($formCutInputData->cons_ws ? $formCutInputData->cons_ws : '') : '' }}">
                        <input type="hidden" name="urutan_marker" id="urutan_marker" value="{{ (isset($formCutInputData) && $formCutInputData) ? ($formCutInputData->urutan_marker ? $formCutInputData->urutan_marker : '') : '' }}">

                        {{-- Marker Summary Information --}}
                        <input type="hidden" name="total_size" id="total_size" value="">
                        <input type="hidden" name="total_ratio" id="total_ratio" value="{{ $totalRatio }}">
                        <input type="hidden" name="total_qty_cut_ply" id="total_qty_cut_ply" value="{{ $totalCutQtyPly }}">

                        <button type="submit" class="btn btn-sb d-none mt-3" id="store-marker-submit">Simpan</button>
                    </form>
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
                        <div class="col-6 col-md-6">
                            <div class="mb-3">
                                <label class="form-label label-input"><small><b>P. Act</b></small></label>
                                <input type="number" class="form-control form-control-sm border-input" name="p_act" id="p_act" value=""
                                    onkeyup="
                                        calculateConsAct();
                                        calculateConsAmpar();
                                    "
                                    onchange="
                                        calculateConsAct();
                                        calculateConsAmpar();
                                    "
                                >
                            </div>
                        </div>
                        <div class="col-6 col-md-6">
                            <div class="mb-3">
                                <label class="form-label label-input"><small><b>Unit Act</b></small></label>
                                <input type="text" class="form-control form-control-sm border-input" name="unit_p_act" id="unit_p_act" value="METER" readonly>
                            </div>
                        </div>
                        <div class="col-6 col-md-6">
                            <div class="mb-3">
                                <label class="form-label label-input"><small><b>Comma Act</b></small></label>
                                <input type="number" class="form-control form-control-sm border-input" name="comma_act" id="comma_act" value="CM"
                                    onkeyup="
                                        calculateConsAct();
                                        calculateConsAmpar();
                                    "
                                    onchange="
                                        calculateConsAct();
                                        calculateConsAmpar();
                                    ">
                            </div>
                        </div>
                        <div class="col-6 col-md-6">
                            <div class="mb-3">
                                <label class="form-label label-input"><small><b>Unit Act</b></small></label>
                                <input type="text" class="form-control form-control-sm border-input" name="unit_comma_act" id="unit_comma_act" value="CM" readonly>
                            </div>
                        </div>
                        <div class="col-6 col-md-6">
                            <div class="mb-3">
                                <label class="form-label label-input"><small><b>L. Act</b></small></label>
                                <input type="number" class="form-control form-control-sm border-input" name="l_act" id="l_act" value=""
                                    onkeyup="
                                        calculateConsAmpar();
                                    "
                                    onchange="
                                        calculateConsAmpar();
                                    ">
                            </div>
                        </div>
                        <div class="col-6 col-md-6">
                            <div class="mb-3">
                                <label class="form-label label-input"><small><b>Unit Act</b></small></label>
                                <input type="text" class="form-control form-control-sm border-input" name="unit_l_act" id="unit_l_act" value="CM" readonly>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="mb-3">
                                <label class="form-label label-fetch"><small><b>Cons WS</b></small></label>
                                <input type="number" class="form-control form-control-sm border-fetch" name="cons_ws" id="cons_ws" value="{{ (isset($formCutInputData) && $formCutInputData) ? $formCutInputData->cons_ws : '' }}" readonly step=".01">
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="mb-3">
                                <label class="form-label"><small><b>Gramasi</b></small></label>
                                <input type="number" class="form-control form-control-sm" name="gramasi" id="gramasi" value="" onkeyup="calculateConsAmpar()" onchange="calculateConsAmpar()" step=".01">
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="mb-3">
                                <label class="form-label"><small><b>Cons Marker</b></small></label>
                                <input type="number" class="form-control form-control-sm" name="cons_marker" id="cons_marker" value="" onkeyup="calculateEstKain(this.value)" onchange="calculateEstKain(this.value)" step=".01">
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="mb-3">
                                <label class="form-label label-calc"><small><b>Cons Act</b></small></label>
                                <input type="number" class="form-control form-control-sm border-calc" name="cons_act" id="cons_act" value="" step=".01" readonly>
                            </div>
                        </div>
                        <div class="col-6 col-md-6">
                            <div class="mb-3">
                                <label class="form-label"><small><b>Cons Piping</b></small></label>
                                <div class="row">
                                    <div class="col-8">
                                        <input type="number" class="form-control form-control-sm" step=".01" name="cons_pipping" id="cons_pipping" value=""
                                            onkeyup="calculateEstPipping(this.value)"
                                            onchange="calculateEstPipping(this.value)"
                                        >
                                    </div>
                                    <div class="col-4">
                                        <input type="text" class="form-control form-control-sm" name="unit_cons_pipping" id="unit_cons_pipping" value="METER" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-6">
                            <div class="mb-3">
                                <label class="form-label label-calc"><small><b>Cons 1 Ampar</b></small></label>
                                <div class="row">
                                    <div class="col-8">
                                        <input type="number" class="form-control form-control-sm border-calc" step=".01" name="cons_ampar" id="cons_ampar" value="" readonly>
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
                                        <input type="number" class="form-control form-control-sm border-calc" step=".01" name="est_pipping" id="est_pipping" value="0" readonly>
                                    </div>
                                    <div class="col-6">
                                        <input type="text" class="form-control form-control-sm border-calc" name="est_pipping_unit" id="est_pipping_unit" value="METER" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label label-calc"><small><b>Est. Kebutuhan Kain</b></small></label>
                                <div class="row g-1">
                                    <div class="col-6">
                                        <input type="number" class="form-control form-control-sm border-calc" step=".01" name="est_kain" id="est_kain" value="0" readonly>
                                    </div>
                                    <div class="col-6">
                                        <input type="text" class="form-control form-control-sm border-calc" name="est_kain_unit" id="est_kain_unit" value="METER" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <button class="btn btn-sb mb-3 float-end d-none" id="next-process-2" onclick="nextProcessTwo()">SIMPAN</button>
        </div>
    </div>
@endsection

@section('custom-script')
    <!-- DataTables & Plugins -->
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <!-- Select2 -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>

    <!-- Marker Script -->
    <script>
        // -Form ID-
        var id = document.getElementById("id").value;

        // -Ratio & Qty Cuy-
        var totalRatio = document.getElementById('total_ratio').value;
        var totalQtyCut = document.getElementById('total_qty_cut_ply').value;

        // Step One (WS) on change event
        $('#act_costing_id').on('change', function(e) {
            if (this.value) {
                updateColorList();
                updateOrderInfo();
            }
        });

        // Step Two (Color) on change event
        $('#color').on('change', function(e) {
            if (this.value) {
                updatePanelList();
                updateSizeList();
            }
        });

        // Step Three (Panel) on change event
        $('#panel').on('change', function(e) {
            if (this.value) {
                getNumber();
                updateSizeList();
                getMarkerCount();
            }
        });

        // Update Order Information Based on Order WS and Order Color
        function updateOrderInfo() {
            return $.ajax({
                url: '{{ route("pilot-form-cut-get-order") }}',
                type: 'get',
                data: {
                    act_costing_id: $('#act_costing_id').val(),
                    color: $('#color').val(),
                },
                dataType: 'json',
                success: function (res) {
                    if (res) {
                        document.getElementById('no_ws').value = res.kpno;
                        document.getElementById('buyer').value = res.buyer;
                        document.getElementById('style').value = res.styleno;
                    }
                },
            });
        }

        // Update Color Select Option Based on Order WS
        function updateColorList() {
            document.getElementById('color').value = null;

            return $.ajax({
                url: '{{ route("pilot-form-cut-get-colors") }}',
                type: 'get',
                data: {
                    act_costing_id: $('#act_costing_id').val(),
                },
                success: function (res) {
                    if (res) {
                        // Update this step
                        document.getElementById('color').innerHTML = res;

                        // Reset next step
                        document.getElementById('panel').innerHTML = null;
                        document.getElementById('panel').value = null;

                        // Open this step
                        $("#color").prop("disabled", false);

                        // Close next step
                        $("#panel").prop("disabled", true);

                        // Reset order information
                        document.getElementById('cons_ws_marker').value = null;
                    }
                },
            });
        }

        // Update Panel Select Option Based on Order WS and Color WS
        function updatePanelList() {
            document.getElementById('panel').value = null;
            return $.ajax({
                url: '{{ route("pilot-form-cut-get-panels") }}',
                type: 'get',
                data: {
                    act_costing_id: $('#act_costing_id').val(),
                    color: $('#color').val(),
                },
                success: function (res) {
                    if (res) {
                        // Update this step
                        document.getElementById('panel').innerHTML = res;
                        document.getElementById('cons_ws').innerHTML = res;

                        // Open this step
                        $("#panel").prop("disabled", false);

                        // Reset order information
                        document.getElementById('cons_ws_marker').value = null;
                    }
                },
            });
        }

        // Order Qty Datatable (Size|Ratio|Cut Qty)
        let ratioDatatable = $("#ratio-datatable").DataTable({
            info: false,
            ordering: false,
            searching: false,
            paging: false,
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("pilot-form-cut-get-sizes") }}',
                data: function (d) {
                    d.act_costing_id = $('#act_costing_id').val();
                    d.color = $('#color').val();
                },
            },
            columns: [
                {
                    data: 'size'
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
                    targets: [1],
                    className: "d-none",
                    render: (data, type, row, meta) => {
                        // Hidden Size Input
                        return '<input type="hidden" id="size-' + meta.row + '" name="size['+meta.row+']" value="' + data + '" readonly />'
                    }
                },
                {
                    // SO Detail Input
                    targets: [2],
                    className: "d-none",
                    render: (data, type, row, meta) => {
                        // Hidden Detail SO Input
                        return '<input type="hidden" id="so-det-id-' + meta.row + '" name="so_det_id['+meta.row+']" value="' + data + '" readonly />'
                    }
                },
                {
                    // Ratio Input
                    targets: [3],
                    render: (data, type, row, meta) => {
                        // Hidden Ratio Input
                        return '<input type="number" id="ratio-' + meta.row + '" name="ratio[' + meta.row + ']" onchange="calculateRatio(' + meta.row + ');" onkeyup="calculateRatio(' + meta.row + ');" />';
                    }
                },
                {
                    // Cut Qty Input
                    targets: [4],
                    render: (data, type, row, meta) => {
                        // Hidden Cut Qty Input
                        return '<input type="number" id="cut-qty-' + meta.row + '" name="cut_qty['+meta.row+']" readonly />'
                    }
                }
            ],
            footerCallback: function(row, data, start, end, display) {
                // This datatable api
                let api = this.api();

                // Remove the formatting to get integer data for summation
                let intVal = function(i) {
                    return typeof i === 'string' ? i.replace(/[\$,]/g, '') * 1 : typeof i === 'number' ? i : 0;
                };

                // Update footer
                $(api.column(0).footer()).html("Total");
                $(api.column(3).footer()).html(0); // Total ratio
                $(api.column(4).footer()).html(0); // Total cut qty
            },
        });

        // Update Order Qty Datatable
        async function updateSizeList() {
            await ratioDatatable.ajax.reload(() => {
                // Get Sizes Count ( for looping over sizes input )
                document.getElementById('total_size').value = ratioDatatable.data().count();
            });
        }

        // Get & Set Marker Count Based on Order WS, Order Color and Order Panel
        function getMarkerCount() {
            document.getElementById('urutan_marker').value = "";
            return $.ajax({
                url: '{{ route("pilot-form-cut-get-count") }}',
                type: 'get',
                data: {
                    act_costing_id: $('#act_costing_id').val(),
                    color: $('#color').val(),
                    panel: $('#panel').val()
                },
                success: function (res) {
                    if (res) {
                        document.getElementById('urutan_marker').value = res;
                    }
                }
            });
        }

        // Get & Set Order WS Cons and Order Qty Based on Order WS, Order Color and Order Panel
        function getNumber() {
            document.getElementById('cons_ws_marker').value = null;
            document.getElementById('cons_ws').value = null;
            return $.ajax({
                url: ' {{ route("pilot-form-cut-get-number") }}',
                type: 'get',
                dataType: 'json',
                data: {
                    act_costing_id: $('#act_costing_id').val(),
                    color: $('#color').val(),
                    panel: $('#panel').val()
                },
                success: function (res) {
                    if (res) {
                        document.getElementById('cons_ws_marker').value = res.cons_ws;
                        document.getElementById('cons_ws').value = res.cons_ws;
                    }
                },
                error: function (jqXHR) {
                    console.log(jqXHR);
                }
            });
        }

        // Calculate Cut Qty Based on Ratio and Spread Qty ( Ratio * Spread Qty )
        function calculateRatio(id) {
            let ratio = document.getElementById('ratio-'+id).value;
            let gelarQty = document.getElementById('gelar_qty').value;

            // Cut Qty Formula
            document.getElementById('cut-qty-'+id).value = ratio * gelarQty;

            // Call Calculate Total Ratio Function ( for order qty datatable summary )
            calculateTotalRatio();
        }

        // Calculate Total Ratio
        function calculateTotalRatio() {
            // Get Sizes Count
            let totalSize = document.getElementById('total_size').value;

            totalRatio = 0;
            totalQtyCut = 0;

            // Looping Over Sizes Input
            for (let i = 0; i < totalSize; i++) {
                // Sum Ratio and Cut Qty
                totalRatio += Number(document.getElementById('ratio-'+i).value);
                totalQtyCut += Number(document.getElementById('cut-qty-'+i).value);
            }

            // Set Ratio and Cut Qty ( order qty datatable summary )
            document.getElementById('total_ratio').value = totalRatio;
            document.getElementById('total_qty_cut_ply').value = totalQtyCut;
            document.querySelector("table#ratio-datatable tfoot tr th:nth-child(4)").innerText = totalRatio;
            document.querySelector("table#ratio-datatable tfoot tr th:nth-child(5)").innerText = totalQtyCut;
        }

        // Calculate All Cut Qty at Once Based on Spread Qty
        function calculateAllRatio(element) {
            // Get Sizes Count
            let totalSize = document.getElementById('total_size').value;

            let gelarQty = element.value;

            // Looping Over Sizes Input
            for (let i = 0; i < totalSize; i++) {
                // Calculate Cut Qty
                let ratio = document.getElementById('ratio-'+i).value;

                // Cut Qty Formula
                document.getElementById('cut-qty-'+i).value = ratio * gelarQty;
            }

            // Call Calculate Total Ratio Function ( for order qty datatable summary )
            calculateTotalRatio();
        }

        // Prevent Form Submit When Pressing Enter
        document.getElementById("store-marker").onkeypress = function(e) {
            var key = e.charCode || e.keyCode || 0;
            if (key == 13) {
                e.preventDefault();
            }
        }

        // Submit Marker Form
        function submitMarkerForm(e, evt) {
            evt.preventDefault();

            clearModified();

            $.ajax({
                url: e.getAttribute('action'),
                type: e.getAttribute('method'),
                data: new FormData(e),
                processData: false,
                contentType: false,
                success: async function(res) {
                    // Success Response

                    if (res.status == 200) {
                        // When Actually Success :
                        disableMarkerForm();

                        $('#header-data-card').CardWidget('collapse');
                        $('#detail-data-card').removeClass('d-none');

                        nextProcessOneButton.classList.add("d-none");
                        nextProcessTwoButton.classList.remove("d-none");

                        if (Object.keys(res.additional).length > 0) {
                            for (let key in res.additional) {
                                if (document.getElementById(key)) {
                                    console.log(key, document.getElementById(key), res.additional[key]);
                                    document.getElementById(key).value = res.additional[key];

                                    if (key == 'id') {
                                        id = res.additional[key];
                                    }
                                }
                            }
                        }
                    } else {
                        // When Actually Error :

                        // Error Alert
                        iziToast.error({
                            title: 'Error',
                            message: res.message,
                            position: 'topCenter'
                        });
                    }
                }, error: function (jqXHR) {
                    // Error Response

                    let res = jqXHR.responseJSON;
                    let message = '';
                    let i = 0;

                    for (let key in res.errors) {
                        message = res.errors[key];

                        if (document.getElementById(key)) {
                            document.getElementById(key).classList.add('is-invalid');
                            modified.push(
                                [key, '.classList', '.remove(', "'is-invalid')"],
                            )

                            if (i == 0) {
                                document.getElementById(key).focus();
                                i++;
                            }
                        }
                    };
                }
            });
        }

        // Reset Step
        async function resetStep() {
            await $("#act_costing_id").val(null).trigger("change");
            await $("#color").val(null).trigger("change");
            await $("#panel").val(null).trigger("change");
            await $("#color").prop("disabled", true);
            await $("#panel").prop("disabled", true);
        }

        function disableMarkerForm() {
            $("#act_costing_id").prop("disabled", true);
            $("#color").prop("disabled", true);
            $("#panel").prop("disabled", true);
            $("#tipe_marker").prop("readonly", true);
            $("#po").prop("readonly", true);
            $("#gelar_qty").prop("readonly", true);

            let totalSize = $("#total_size").val()

            for (let i = 0; i < totalSize; i++) {
                $('#ratio-'+i).prop("readonly", true);
                $('#cut-qty-'+i).prop("readonly", true);
            }
        }

    // Form Cut Script
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

        // -Global Key Event-
        $(document).keyup(function(e) {
            if (e.key === "Escape") {
                e.preventDefault();

                // stopTimeRecord()
            }
        });

        // Variable List :
            // -Form Cut Input Header Data-
            var status = document.getElementById("status").value;
            var startTime = document.getElementById("start-time");
            var finishTime = document.getElementById("finish-time");

            // -Process Button Elements-
            var startProcessButton = document.getElementById("start-process");
            var nextProcessOneButton = document.getElementById("next-process-1");
            var nextProcessTwoButton = document.getElementById("next-process-2");

            // -Summary Data-
            var summaryData = null;

        // Function List :
            // -On Load-
            $(document).ready(() => {
                checkStatus();

                // -Select2 Prevent Step-Jump Input ( Step = WS -> Color -> Panel )-
                $("#color").prop("disabled", true);
                $("#panel").prop("disabled", true);
            });

        // Process :
            // -Create New Form-
            function createNewForm() {
                Swal.fire({
                    icon: 'info',
                    title: 'Buat Form Cut Pilot Baru?',
                    text: 'Yakin akan membuat form pilot baru?',
                    showCancelButton: true,
                    showConfirmButton: true,
                    confirmButtonText: 'Buat',
                    confirmButtonColor: "#6531a0",
                }).then(async (result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '{{ route('create-new-pilot-form-cut') }}/',
                            type: 'get',
                            dataType: 'json',
                            success: function(res) {
                                if (res) {
                                    window.location = res.redirect;
                                }
                            }
                        })
                    }
                });
            }

            // -Start Process-
            function startProcess() {
                Swal.fire({
                    icon: 'info',
                    title: 'Buat Form Cut Pilot?',
                    text: 'Yakin akan membuat form cut pilot?',
                    showCancelButton: true,
                    showConfirmButton: true,
                    confirmButtonText: 'Buat',
                    confirmButtonColor: "#6531a0",
                }).then(async (result) => {
                    if (result.isConfirmed) {
                        let now = new Date();

                        updateToStartProcess();

                        startProcessButton.classList.add("d-none");
                        nextProcessOneButton.classList.remove("d-none");
                    }
                });
            }

            // -Start Process Transaction-
            function updateToStartProcess() {
                return $.ajax({
                    url: '{{ route('start-process-pilot-form-cut') }}',
                    type: 'put',
                    dataType: 'json',
                    success: function(res) {
                        if (res) {
                            $("#create-new-form").removeClass("d-none");
                            $('#header-data-card').removeClass('d-none');

                            if (Object.keys(res.additional).length > 0) {
                                for (let key in res.additional) {
                                    if (document.getElementById(key)) {
                                        document.getElementById(key).value = res.additional[key];

                                        if (key == 'id') {
                                            id = res.additional[key];
                                        }
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // -Process One-
            function nextProcessOne() {
                updateToNextProcessOne();
            }

            // -Process One Transaction-
            function  updateToNextProcessOne() {
                return document.getElementById('store-marker-submit').click();
            }

            // -Process Two-
            function nextProcessTwo() {
                updateToNextProcessTwo();
            }

            // -Process Two Transaction-
            function updateToNextProcessTwo() {
                id = document.getElementById('id').value;
                var idMarker = document.getElementById('id_marker').value;
                var pActual = document.getElementById('p_act').value;
                var pUnitActual = document.getElementById('unit_p_act').value;
                var commaActual = document.getElementById('comma_act').value;
                var commaUnitActual = document.getElementById('unit_comma_act').value;
                var lActual = document.getElementById('l_act').value;
                var lUnitActual = document.getElementById('unit_l_act').value;
                var consWs = document.getElementById('cons_ws').value;
                var consActual = document.getElementById('cons_act').value;
                var consPipping = document.getElementById('cons_pipping').value;
                var consAmpar = document.getElementById('cons_ampar').value;
                var estPipping = document.getElementById('est_pipping').value;
                var estPippingUnit = document.getElementById('est_pipping_unit').value;
                var estKain = document.getElementById('est_kain').value;
                var estKainUnit = document.getElementById('est_kain_unit').value;
                var gramasi = document.getElementById('gramasi').value;
                var consMarker = document.getElementById('cons_marker').value;

                clearModified();

                return $.ajax({
                    url: '{{ route('next-process-two-pilot-form-cut') }}/' + id,
                    type: 'put',
                    dataType: 'json',
                    data: {
                        id_marker: idMarker,
                        p_act: pActual,
                        unit_p_act: pUnitActual,
                        comma_act: commaActual,
                        unit_comma_act: commaUnitActual,
                        l_act: lActual,
                        unit_l_act: lUnitActual,
                        cons_ws: consWs,
                        cons_act: consActual,
                        cons_pipping: consPipping,
                        cons_ampar: consAmpar,
                        est_pipping: estPipping,
                        est_pipping_unit: estPippingUnit,
                        est_kain: estKain,
                        est_kain_unit: estKainUnit,
                        gramasi: gramasi,
                        cons_marker: consMarker,
                    },
                    success: function(res) {
                        if (res) {
                            if (res.status == 200) {
                                $('#header-data-card').CardWidget('collapse');
                                $('#detail-data-card').CardWidget('collapse');

                                nextProcessTwoButton.classList.add("d-none");

                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil',
                                    text: 'Form Pilot dengan no. form : "'+document.getElementById('no_form').value+'" telah berhasil dibuat (Laman akan ditutup)',
                                    showCancelButton: false,
                                    showConfirmButton: true,
                                    confirmButtonText: 'Oke',
                                    timerProgressBar: true
                                }).then((result) => {
                                    window.close();
                                });
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
                        pActualConverted = pActualVar + (commaActualVar/36);
                    } else if (unitPActualVar == "METER") {
                        pActualConverted = pActualVar + (commaActualVar/100);
                    }
                } else {
                    // YARD
                    if (unitPActualVar == "YARD" || unitPActualVar == "YRD") {
                        let pActualInch = ((pActualVar * 36/1) + commaActualVar)

                        if (unitQtyVar == "METER") {
                            pActualConverted = pActualInch * 0.0254;
                        } else if (unitQtyVar == "KGM") {
                            let gramasiInch = gramasiVar / 1550;

                            pActualConverted = ((gramasiInch * ( pActualInch * lActualVar ))/ 1000);
                        } else {
                            pActualConverted = pActualVar + (commaActualVar/36);
                        }

                    // METER
                    } else if (unitPActualVar == "METER") {
                        let pActualInch = ((pActualVar * 39.3701) + (commaActualVar/2.54));
                        let lActualInch = lActualVar / 2.54;

                        if (unitQtyVar == "YARD" || unitQtyVar == "YRD") {
                            pActualConverted = pActualInch / 36;
                        } else if (unitQtyVar == "KGM") {
                            let gramasiInch = gramasiVar / 1550;

                            pActualConverted = ((gramasiInch * ( pActualInch * lActualInch ))/ 1000);
                        } else {
                            pActualConverted = pActualVar + (commaActualVar/100);
                        }
                    }
                }

                return pActualConverted;
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

            // -Calculate Cons Ampar-
            function calculateConsAmpar() {
                let pActualVar = Number(document.getElementById("p_act").value);
                let unitPActualVar = document.getElementById("unit_p_act").value;
                let commaActualVar = Number(document.getElementById("comma_act").value);
                let lActualVar = Number(document.getElementById("l_act").value);
                let gramasiVar = Number(document.getElementById("gramasi").value);
                let lActualMeter = lActualVar / 100;

                let pActualFinal = pActualCommaActual(pActualVar, unitPActualVar, commaActualVar);

                console.log(totalRatio, totalQtyCut)

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

                consActual = totalQtyCut > 0 ? pActualFinal / totalQtyCut : 0;

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

            // -Check Form Cut Input Status-
            async function checkStatus() {
                if (status == "PENGERJAAN PILOT MARKER") {
                    $('#header-data-card').removeClass('d-none');
                    startProcessButton.classList.add("d-none");
                    nextProcessOneButton.classList.remove("d-none");

                    document.getElementById("create-new-form").classList.remove("d-none");
                }

                if (status == "PENGERJAAN PILOT DETAIL") {
                    console.log($("#act_costing_id_text").val(), $("#color_text").val(), $("#panel_text").val());

                    await $("#act_costing_id").val($("#act_costing_id_text").val()).trigger("change");
                    await $("#color").val($("#color_text").val()).trigger("change");
                    await $("#panel").val($("#panel_text").val()).trigger("change");

                    $('#act_costing_id').next(".select2-container").addClass("d-none");
                    $("#color").next(".select2-container").addClass("d-none");
                    $("#panel").next(".select2-container").addClass("d-none");

                    $("#no_ws").removeClass("d-none");
                    $("#color_text").removeClass("d-none");
                    $("#panel_text").removeClass("d-none");

                    updateSizeList();

                    calculateConsAmpar();

                    startProcessButton.classList.add("d-none");
                    nextProcessOneButton.classList.add("d-none");

                    $('#header-data-card').removeClass('d-none');
                    $('#header-data-card').CardWidget('collapse');
                    $('#detail-data-card').removeClass('d-none');
                    nextProcessTwoButton.classList.remove("d-none");
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
                document.getElementById('cons_act').setAttribute('readonly', true);
                document.getElementById('cons_pipping').setAttribute('readonly', true);
                document.getElementById('cons_ampar').setAttribute('readonly', true);
                document.getElementById('est_pipping').setAttribute('readonly', true);
                document.getElementById('est_kain').setAttribute('readonly', true);
                document.getElementById('operator').setAttribute('readonly', true);
                document.getElementById('unit_cons_actual_gelaran').setAttribute('readonly', true);
            }
    </script>
@endsection
