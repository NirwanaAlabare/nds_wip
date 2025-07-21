@extends('layouts.index')

@section('custom-link')
    {{-- <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}"> --}}

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables 2.0/jquery.dataTables.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables 2.0/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables 2.0/fixedColumns.bootstrap4.min.css') }}">
    <!-- jQuery -->
    <script src="{{ asset('plugins/datatables 2.0/jquery-3.3.1.js') }}"></script>


    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

    <style>
        .select2-container--bootstrap4 .select2-selection--single {
            height: 31px !important;
            padding: 0.25rem 0.5rem !important;
            font-size: 0.875rem !important;
        }

        .select2-container--bootstrap4 .select2-selection__rendered {
            line-height: 1.5 !important;
        }
    </style>
@endsection

@section('content')
    {{-- Modal Start From And Validate User --}}
    <div class="modal fade" id="startFormModal" tabindex="-1" aria-labelledby="startFormModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg"> <!-- Can change to modal-md or modal-sm if needed -->
            <div class="modal-content">

                <!-- Modal Header -->
                <div class="modal-header bg-sb text-white">
                    <h5 class="modal-title" id="startFormModalLabel">
                        <i class="fas fa-qrcode"></i> Scan ID Operator
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="txtqr_operator" class="form-label label-input">
                                <small><b>QR Code</b></small>
                            </label>
                            <div class="input-group">
                                <input type="text" class="form-control  border-input" name="txtqr_operator"
                                    id="txtqr_operator" autocomplete="off" enterkeyhint="go" autofocus>

                                <!-- Scan label aligned properly with input -->
                                <span class="input-group-text  bg-light text-muted py-0 px-2">
                                    <small>Scan</small>
                                </span>
                            </div>
                        </div>
                        <div class="col-md-12 mt-3">
                            <div id="reader"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                    {{-- <input type="button" class="btn btn-primary btn-sm d-none" id="btnSaveStart" value="Save"
                        onclick="start_form();"> --}}
                    <input type="button" class="btn btn-primary btn-sm" id="btnSaveStart" value="Save"
                        onclick="start_form();">
                </div>
            </div>
        </div>
    </div>

    <!-- QR Scanner Modal -->
    <div class="modal fade" id="qrScannerModalFabric" tabindex="-1" aria-labelledby="qrScannerModalFabricLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-sb text-white">
                    <h5 class="modal-title" id="qrScannerModalFabricLabel">Scan Fabric</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                        onclick="stopScannerFabric()"></button>
                </div>
                <div class="modal-body text-center">
                    <div id="reader_fabric" style="width: 300px; height: 300px; margin: auto;"></div>
                </div>
            </div>
        </div>
    </div>



    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-list"></i> Form Inspect</h5>
        </div>
        <div class="card-body pb-0">
            <div class="row mb-3" id="startFormRow">
                <div class="col-md-3 text-start">
                    <button type="button" class="btn btn-primary btn-sm" onclick="openScannerModalOperator()">
                        Start Form
                    </button>
                </div>
            </div>
            <div class="row mb-3">
                <input type="hidden" id="id" name="id" class="form-control form-control-sm border-primary"
                    value="{{ $id }}" readonly>
                <input type="hidden" id="status_proses_form" name="status_proses_form"
                    class="form-control form-control-sm border-primary" value="{{ $status_proses_form }}" readonly>
                <input type="hidden" id="txtid_jo" name="txtid_jo" class="form-control form-control-sm border-primary"
                    value="{{ $id_jo }}" readonly>
                <div class="col-md-6">
                    <label for="txtstart_form"><small><b>Start Form:</b></small></label>
                    <input type="text" id="txtstart_form" name="txtstart_form"
                        class="form-control form-control-sm border-primary" value="{{ $start_form_fix }}" readonly>
                </div>
                <div class="col-md-6">
                    <label for="txtfinish_form"><small><b>Finish Form:</b></small></label>
                    <input type="text" id="txtfinish_form" name="txtfinish_form"
                        class="form-control form-control-sm border-primary" value="" readonly>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="txtno_form"><small><b>No. Form :</b></small></label>
                    <input type="text" id="txtno_form" name="txtno_form"
                        class="form-control form-control-sm border-primary" value="{{ $no_form }}" readonly>
                </div>
                <div class="col-md-6">
                    <label for="txtno_invoice"><small><b>No. PL :</b></small></label>
                    <input type="text" id="txtno_invoice" name="txtno_invoice"
                        class="form-control form-control-sm border-primary" value="{{ $no_invoice }}" readonly>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="txtno_mesin"><small><b>No. Mesin :</b></small></label>
                    <input type="text" id="txtno_mesin" name="txtno_mesin"
                        class="form-control form-control-sm border-primary" value="{{ $user }}" readonly>
                </div>
                <div class="col-md-4">
                    <label for="txtnik"><small><b>NIK :</b></small></label>
                    <input type="text" id="txtnik" name="txtnik"
                        class="form-control form-control-sm border-primary" value="{{ $nik }}" readonly>
                </div>
                <div class="col-md-4">
                    <label for="txtoperator"><small><b>Operator :</b></small></label>
                    <input type="text" id="txtoperator" name="txtoperator"
                        class="form-control form-control-sm border-primary" value="{{ $operator }}" readonly>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-3">
                    <label for="txtbuyer"><small><b>Buyer :</b></small></label>
                    <input type="text" id="txtbuyer" name="txtbuyer"
                        class="form-control form-control-sm border-primary" value="{{ $buyer }}" readonly>
                </div>
                <div class="col-md-3">
                    <label for="txtws"><small><b>WS :</b></small></label>
                    <input type="text" id="txtws" name="txtws"
                        class="form-control form-control-sm border-primary" value="{{ $ws }}" readonly>
                </div>
                <div class="col-md-3">
                    <label for="txtstyle"><small><b>Style :</b></small></label>
                    <input type="text" id="txtstyle" name="txtstyle"
                        class="form-control form-control-sm border-primary" value="{{ $style }}" readonly>
                </div>
                <div class="col-md-3">
                    <label for="txtcolor"><small><b>Color :</b></small></label>
                    <input type="text" id="txtcolor" name="txtcolor"
                        class="form-control form-control-sm border-primary" value="{{ $color }}" readonly>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-3">
                    <label for="txtno_lot"><small><b>No. Lot :</b></small></label>
                    <input type="text" id="txtno_lot" name="txtno_lot"
                        class="form-control form-control-sm border-primary" value="{{ $no_lot }}" readonly>
                </div>
                <div class="col-md-3">
                    <label for="txtgroup_inspect"><small><b>Group Inspect :</b></small></label>
                    <input type="text" id="txtgroup_inspect" name="txtgroup_inspect"
                        class="form-control form-control-sm border-primary" value="{{ $group_inspect }}" readonly>
                </div>
                <div class="col-md-3">
                    <label for="txtcek_inspect"><small><b>Cek Inspect (%) :</b></small></label>
                    <input type="text" id="txtcek_inspect" name="txtcek_inspect"
                        class="form-control form-control-sm border-primary" value="{{ $cek_inspect }}" readonly>
                </div>
                <div class="col-md-3">
                    <label for="txtnote"><small><b>Notes :</b></small></label>
                    <input type="text" id="txtnote" name="txtnote"
                        class="form-control form-control-sm border-primary" value="{{ $notes }}" readonly>
                </div>
            </div>
        </div>
    </div>


    <div class="card card-sb" id="scanFormFabric">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-list"></i> Scan Form Fabric</h5>
        </div>
        <div class="card-body pb-0">
            <div class="row mb-3">
                <div class="col-md-3">
                    <label for="txtid_item"><small><b>ID Item :</b></small></label>
                    <input type="text" id="txtid_item" name="txtid_item"
                        class="form-control form-control-sm border-primary" value="{{ $id_item }}" readonly>
                </div>
                <div class="col-md-3">
                    <label for="txtno_roll"><small><b>No. Roll :</b></small></label>
                    <input type="text" id="txtno_roll" name="txtno_roll"
                        class="form-control form-control-sm border-primary" value="{{ $no_roll }}" readonly>
                </div>
                <div class="col-md-3">
                    <label for="txtbarcode"><small><b>Barcode :</b></small></label>
                    <input type="text" id="txtbarcode" name="txtbarcode"
                        class="form-control form-control-sm border-primary" value="{{ $barcode }}" readonly>
                </div>
                <div class="col-md-3" id="scanBarcodeRow">
                    <label for="txtscan_fabric"><small><b>Scan :</b></small></label>
                    <button type="button" id="btnScanBarcode" class="btn btn-sm btn-outline-primary w-100"
                        onclick="openScannerModalFabric()">
                        Scan Barcode
                    </button>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="txtfabric"><small><b>Fabric :</b></small></label>
                    <input type="text" id="txtfabric" name="txtfabric"
                        class="form-control form-control-sm border-primary" value="{{ $itemdesc }}" readonly>
                </div>
                <div class="col-md-6">
                    <label for="txtsupp"><small><b>Supplier :</b></small></label>
                    <input type="text" id="txtsupp" name="txtsupp"
                        class="form-control form-control-sm border-primary" value="{{ $supplier }}" readonly>
                </div>
            </div>
            <div class="row mb-3" id="unitInputRow_1">
                <div class="col-md-3">
                    <label for="txtweight"><small><b>Weight :</b></small></label>
                    <div class="input-group input-group-sm">
                        <input type="number" step="any" min="0" id="txtweight" name="txtweight"
                            class="form-control form-control-sm border-primary fabric-input" value="{{ $weight }}">
                        <span class="input-group-text border-primary text-primary">KG</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <label for="txtlbs"><small><b>&nbsp;</b></small></label>
                    <div class="input-group input-group-sm">
                        <input type="text" id="txtlbs" name="txtlbs"
                            class="form-control form-control-sm border-primary" readonly value="{{ $lbs }}">
                        <span class="input-group-text border-primary text-primary">LBS</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <label for="txtwidth"><small><b>Width :</b></small></label>
                    <div class="input-group input-group-sm">
                        <input type="number" step="any" min="0" id="txtwidth" name="txtwidth"
                            class="form-control form-control-sm border-primary fabric-input" value="{{ $width }}">
                        <select id="unitWidth" name="unitWidth"
                            class="form-select form-select-sm border-primary text-primary fabric-input"
                            style="max-width: 100px;">
                            <option value="cm" {{ $unit_width === 'cm' ? 'selected' : '' }}>CM</option>
                            <option value="inch" {{ $unit_width === 'inch' ? 'selected' : '' }}>INCH</option>
                        </select>

                    </div>
                </div>
                <div class="col-md-3">
                    <label for="txtinch"><small><b>&nbsp;</b></small></label>
                    <div class="input-group input-group-sm">
                        <input type="text" id="txtinch" name="txtinch"
                            class="form-control form-control-sm border-primary" readonly>
                        <span class="input-group-text border-primary text-primary">INCH</span>
                    </div>
                </div>
            </div>
            <div class="row mb-3" id="unitInputRow_2">
                <div class="col-md-3">
                    <label for="txtact_weight"><small><b>Actual Weight :</b></small></label>
                    <div class="input-group input-group-sm">
                        <input type="number" step="any" min="0" id="txtact_weight" name="txtact_weight"
                            class="form-control form-control-sm border-success fabric-input" value="{{ $act_weight }}">
                        <span class="input-group-text border-success text-success">KG</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <label for="txtgramage"><small><b>Gramage :</b></small></label>
                    <div class="input-group input-group-sm">
                        <input type="number" step="any" min="0" id="txtgramage" name="txtgramage"
                            class="form-control form-control-sm border-success fabric-input" value="{{ $gramage }}">
                        <span class="input-group-text border-success text-success">GSM</span>
                    </div>
                </div>
                <div class="col-md-6 d-flex align-items-end justify-content-end">
                    <button type="button" class="btn btn-primary btn-sm" id="btnNext"
                        onclick="save_detail_fabric();">
                        Next <i class="fas fa-long-arrow-alt-right ms-1"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>


    <div class="card card-sb" id="visualInspection">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-list"></i> Visual Inspection</h5>
        </div>
        <div class="card-body pb-0">
            <div class="row mb-3">
                <div class="col-md-3">
                    <label for="cbo_length"><small><b>Length :</b></small></label>
                    <select class="form-control form-control-sm select2bs4 select-border-primary" id="cbo_length"
                        name="cbo_length" style="width: 100%;">
                        <option selected="selected" value="" disabled="true">Pilih Length
                        </option>
                        @foreach ($data_length as $dl)
                            <option value="{{ $dl->isi }}">
                                {{ $dl->tampil }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-9">
                    <label for="cbo_defect"><small><b>Critical Defect :</b></small></label>
                    <select class="form-control form-control-sm select2bs4 " id="cbo_defect" name="cbo_defect"
                        style="width: 100%;">
                        <option selected="selected" value="" disabled="true">Pilih Defect
                        </option>
                        @foreach ($data_defect as $dd)
                            <option value="{{ $dd->isi }}">
                                {{ $dd->tampil }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-3">
                    <label for="txtup_to_3"><small><b>Up To 3" (1) :</b></small></label>
                    <div class="input-group input-group-sm">
                        <input type="number" min="0" id="txtup_to_3" name="txtup_to_3"
                            class="form-control form-control-sm border-primary" value="">
                    </div>
                </div>
                <div class="col-md-3">
                    <label for="txt3_6"><small><b>3" - 6" (2) :</b></small></label>
                    <div class="input-group input-group-sm">
                        <input type="number" min="0" id="txt3_6" name="txt3_6"
                            class="form-control form-control-sm border-primary" value="">
                    </div>
                </div>
                <div class="col-md-3">
                    <label for="txt6_9"><small><b>6" - 9" (3) :</b></small></label>
                    <div class="input-group input-group-sm">
                        <input type="number" min="0" id="txt6_9" name="txt6_9"
                            class="form-control form-control-sm border-primary" value="">
                    </div>
                </div>
                <div class="col-md-3">
                    <label for="txtovr_9"><small><b>Over 9" (4) :</b></small></label>
                    <div class="input-group input-group-sm">
                        <input type="number" min="0" id="txtovr_9" name="txtovr_9"
                            class="form-control form-control-sm border-primary" value="">
                    </div>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-3">
                    <label for="txtfull_width"><small><b>Full Width :</b></small></label>
                    <div class="input-group input-group-sm">
                        <input type="number" step="any" min="0" id="txtfull_width" name="txtfull_width"
                            class="form-control form-control-sm border-primary" value="">
                        <select id="unitFullWidth" name="unitFullWidth"
                            class="form-select form-select-sm border-primary text-primary" style="max-width: 100px;">
                            <option value="cm" {{ $unit_width === 'cm' ? 'selected' : '' }}>CM</option>
                            <option value="inch" {{ $unit_width === 'inch' ? 'selected' : '' }}>INCH</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <label for="txtfull_width_act"><small><b>&nbsp;</b></small></label>
                    <div class="input-group input-group-sm">
                        <input type="number" step="any" min="0" id="txtfull_width_act"
                            name="txtfull_width_act" class="form-control form-control-sm border-primary" value=""
                            readonly>
                        <span class="input-group-text border-primary text-primary">INCH</span>
                    </div>
                </div>

                <div class="col-md-3">
                    <label for="txtcuttable_width"><small><b>Cuttable Width :</b></small></label>
                    <div class="input-group input-group-sm">
                        <input type="number" step="any" min="0" id="txtcuttable_width"
                            name="txtcuttable_width" class="form-control form-control-sm border-primary" value="">
                        <select id="unitCuttableWidth" name="unitCuttableWidth"
                            class="form-select form-select-sm border-primary text-primary" style="max-width: 100px;">
                            <option value="cm" {{ $unit_width === 'cm' ? 'selected' : '' }}>CM</option>
                            <option value="inch" {{ $unit_width === 'inch' ? 'selected' : '' }}>INCH</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <label for="txtcuttable_width_act"><small><b>&nbsp;</b></small></label>
                    <div class="input-group input-group-sm">
                        <input type="number" step="any" min="0" id="txtcuttable_width_act"
                            name="txtcuttable_width_act" class="form-control form-control-sm border-primary"
                            value="" readonly>
                        <span class="input-group-text border-primary text-primary">INCH</span>
                    </div>
                </div>
            </div>
            <div class="row mb-4">
                <div class="col-md-3 offset-md-9 text-end">
                    <button type="button" class="btn btn-primary btn-sm" onclick="save_visual_inspection();">
                        <i class="fas fa-plus"></i> Add
                    </button>
                </div>
            </div>

            <!-- Summary Title -->
            <div class="row mb-2">
                <div class="col">
                    <h6 class="text-primary fw-bold">Summary</h6>
                </div>
            </div>
            <div class="row mb-3">
                <div class="table-responsive">
                    <table id="datatable" class="table table-bordered table-striped w-100 text-wrap">
                        <thead class="bg-sb">
                            <tr style="text-align:center; vertical-align:middle">
                                <th scope="col">LENGTH</th>
                                <th scope="col">CRITICAL DEFECT</th>
                                <th scope="col">UP TO 3"</th>
                                <th scope="col">3" - 6"</th>
                                <th scope="col">6" - 9"</th>
                                <th scope="col">OVER 9"</th>
                                <th scope="col">WIDTH</th>
                                <th scope="col">ACT</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-3">
                    <label for="txtbintex_length"><small><b>Bintex Length :</b></small></label>
                    <div class="input-group input-group-sm">
                        <input type="number" step="any" min="0" id="txtbintex_length"
                            name="txtbintex_length" class="form-control form-control-sm border-primary"
                            value="{{ $bintex_length }}">
                        <select id="unitBintex" name="unitBintex"
                            class="form-select form-select-sm border-primary text-primary" style="max-width: 100px;">
                            <option value="yard" {{ $unit_bintex === 'yard' ? 'selected' : '' }}>YARD</option>
                            <option value="meter" {{ $unit_bintex === 'meter' ? 'selected' : '' }}>METER</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <label for="txtbintex_act"><small><b>&nbsp;</b></small></label>
                    <div class="input-group input-group-sm">
                        <input type="number" step="any" min="0" id="txtbintex_act" name="txtbintex_act"
                            class="form-control form-control-sm border-primary" value="" readonly>
                        <span class="input-group-text border-primary text-primary">YARD</span>
                    </div>
                </div>

                <div class="col-md-3">
                    <label for="txtact_length"><small><b>Actual length :</b></small></label>
                    <div class="input-group input-group-sm">
                        <input type="number" step="any" min="0" id="txtact_length" name="txtact_length"
                            class="form-control form-control-sm border-primary" value="{{ $act_length }}">
                        <select id="unitActLength" name="unitActLength"
                            class="form-select form-select-sm border-primary text-primary" style="max-width: 100px;">
                            <option value="yard" {{ $unit_act_length === 'yard' ? 'selected' : '' }}>YARD</option>
                            <option value="meter" {{ $unit_act_length === 'meter' ? 'selected' : '' }}>METER</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <label for="txtact_length_fix"><small><b>&nbsp;</b></small></label>
                    <div class="input-group input-group-sm">
                        <input type="number" step="any" min="0" id="txtact_length_fix"
                            name="txtact_length_fix" class="form-control form-control-sm border-primary" value=""
                            readonly>
                        <span class="input-group-text border-primary text-primary">YARD</span>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-12">
                    <button type="button" class="btn btn-info btn-sm w-100" id="btnCalculate"
                        onclick="calculate_act_point();">
                        <i class="fas fa-calculator"></i> Calculate
                    </button>
                </div>
            </div>
            <!-- Summary Title -->
            <div class="row mb-2">
                <div class="col">
                    <h6 class="text-primary fw-bold">Actual Point Length</h6>
                </div>
            </div>

            <div class="row mb-3">
                <div class="table-responsive">
                    <table id="datatable_act_point" class="table table-bordered table-striped w-100 text-wrap">
                        <thead class="bg-sb">
                            <tr style="text-align:center; vertical-align:middle">
                                <th scope="col">UP TO 3"</th>
                                <th scope="col">3" - 6"</th>
                                <th scope="col">6" - 9"</th>
                                <th scope="col">OVER 9"</th>
                                <th scope="col">TOTAL POINT</th>
                                <th scope="col">ACTUAL POINT</th>
                                <th scope="col">MAX POINT</th>
                                <th scope="col">RESULT</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-12">
                    <button type="button" class="btn btn-success btn-sm w-100" id="btnFinish" onclick="">
                        <i class="fas fa-check"></i> Finish
                    </button>
                </div>
            </div>

        </div>
    </div>
@endsection

@section('custom-script')
    <!-- DataTables & Plugins -->
    {{-- <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script> --}}
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables 2.0/dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables 2.0/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables 2.0/dataTables.fixedColumns.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-rowsgroup/dataTables.rowsGroup.js') }}"></script>
    <script>
        // Select2 Autofocus
        $(document).on('select2:open', () => {
            document.querySelector('.select2-search__field').focus();
        });

        // Initialize Select2 Elements
        $('.select2').select2();

        // Initialize Select2BS4 Elements
        $('.select2bs4').select2({
            theme: 'bootstrap4',
        });

        function notif() {
            alert("Maaf, Fitur belum tersedia!");
        }

        $('#cbo_length').select2({
            theme: 'bootstrap4',
            minimumResultsForSearch: Infinity
        });
    </script>
    <script>
        $(document).ready(function() {
            const status = $('#status_proses_form').val();
            reset_visual_inspection();
            if (status === 'new') {
                // Hide the row that contains the Start Form button
                $('#startFormRow').hide();
                $('#visualInspection').hide();
            } else if (status === 'scan_form_fabric') {
                $('.fabric-input').prop('disabled', true); // disables inputs & selects
                $('#startFormRow').hide();
                $('#btnNext').hide();
            } else if (status === 'draft') {
                $('#scanFormFabric').hide();
                $('#visualInspection').hide();
            }
            const barcode = $('#txtbarcode').val();
            if (barcode !== '') {
                // Hide the row that contains the Start Form button
                $('#scanBarcodeRow').hide();
            } else {
                $('#unitInputRow_1').hide();
                $('#unitInputRow_2').hide();
            }


            // Auto convert weight KG to LBS
            $('#txtweight').on('input', function() {
                const weightKg = parseFloat($(this).val());
                if (!isNaN(weightKg)) {
                    const weightLbs = (weightKg * 2.2).toFixed(2);
                    $('#txtlbs').val(weightLbs);
                } else {
                    $('#txtlbs').val('');
                }
            });

            updateInch();
        });


        function updateInch() {
    let width = parseFloat($('#txtwidth').val()) || 0;
    let unit = $('#unitWidth').val();

    let inchValue = 0;
    if (unit === 'inch') {
        inchValue = width;
    } else if (unit === 'cm') {
        inchValue = width * 0.3937;
    }

    // Round up to 2 decimal places
    let roundedUp = Math.ceil(inchValue * 100) / 100;

    $('#txtinch').val(roundedUp.toFixed(2));
}


        $('#txtwidth, #unitWidth').on('input change', updateInch);

        // Stop Operator Scanner when modal is hidden
        document.getElementById('startFormModal').addEventListener('hidden.bs.modal', function() {
            stopScannerOperator();
        });


        let html5QrcodeScanner = null;

        function openScannerModalOperator() {
            $('#txtqr_operator').val('');
            document.getElementById('btnSaveStart').classList.add('d-none');

            const modal = new bootstrap.Modal(document.getElementById('startFormModal'));
            modal.show();

            initScan();
        }


        async function stopScannerOperator() {
            if (html5QrcodeScanner) {
                try {
                    await html5QrcodeScanner.clear();
                } catch (e) {
                    console.error("Error stopping Operator scanner:", e);
                }
                html5QrcodeScanner = null;
            }
        }

        function initScan() {
            if (document.getElementById("reader")) {
                if (html5QrcodeScanner) {
                    stopScannerOperator(); // avoid duplicate
                }

                function onScanSuccess(decodedText) {
                    if (decodedText && decodedText.trim() !== "") {
                        document.getElementById('txtqr_operator').value = decodedText;
                        document.getElementById('btnSaveStart').classList.remove('d-none');

                        // Stop scanner and close modal
                        stopScannerOperator();
                        // const modal = bootstrap.Modal.getInstance(document.getElementById('startFormModal'));
                        // if (modal) modal.hide();
                    }
                }

                function onScanFailure(error) {
                    console.warn(`Scan error: ${error}`);
                }

                html5QrcodeScanner = new Html5QrcodeScanner(
                    "reader", {
                        fps: 10,
                        qrbox: {
                            width: 250,
                            height: 250
                        }
                    },
                    false
                );

                html5QrcodeScanner.render(onScanSuccess, onScanFailure);
            }
        }

        function start_form() {
            let txtqr_operator = $('#txtqr_operator').val();
            let id = $('#id').val();

            if (!txtqr_operator || txtqr_operator.trim() === '') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan!',
                    text: 'Field "QR Operator" wajib diisi.',
                });
                return;
            }

            // Step 1: Get operator info
            $.ajax({
                type: "POST",
                url: '{{ route('get_operator_info') }}', // route for checking operator
                data: {
                    _token: '{{ csrf_token() }}',
                    txtqr_operator: txtqr_operator
                },
                success: function(response) {
                    if (response.status === 'not_found') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Data Tidak Ditemukan',
                            text: 'Operator tidak terdaftar di HRIS.'
                        });
                        return;
                    }

                    // Step 2: Show confirmation before saving
                    Swal.fire({
                        title: 'Konfirmasi Data Operator',
                        html: `
                    <p><b>Nama:</b> ${response.data.operator}</p>
                    <p><b>NIK:</b> ${response.data.nik}</p>
                    <p><b>Enroll ID:</b> ${response.data.enroll_id}</p>
                    <hr>
                    Lanjutkan update form dengan data ini?
                `,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Lanjutkan',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Step 3: Save the data
                            $.ajax({
                                type: "POST",
                                url: '{{ route('save_start_form_inspect') }}',
                                data: {
                                    _token: '{{ csrf_token() }}',
                                    txtqr_operator: txtqr_operator,
                                    id: id
                                },
                                success: function(res) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Berhasil',
                                        text: res.message
                                    });
                                    $('#startFormModal').modal('hide');
                                    location.reload();
                                },
                                error: function(err) {
                                    console.error('Save Error:', err);
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Gagal',
                                        text: 'Gagal menyimpan data.'
                                    });
                                }
                            });
                        }
                    });
                },
                error: function(xhr) {
                    console.error('Operator Check Error:', xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Terjadi kesalahan saat mengambil data operator.'
                    });
                }
            });
        }


        let html5QrcodeScanner_fabric = null;
        let isProcessingScan = false;

        function openScannerModalFabric() {
            isProcessingScan = false;

            const modal = new bootstrap.Modal(document.getElementById('qrScannerModalFabric'));
            modal.show();

            initScanFabric();
        }


        async function stopScannerFabric() {
            if (html5QrcodeScanner_fabric) {
                try {
                    await html5QrcodeScanner_fabric.clear();
                } catch (e) {
                    console.error("Error stopping scanner:", e);
                }
                html5QrcodeScanner_fabric = null;
            }
        }

        function initScanFabric() {
            if (document.getElementById("reader_fabric")) {
                if (html5QrcodeScanner_fabric) {
                    stopScannerFabric(); // avoid duplicate scanner
                }

                function onScanSuccess(decodedText) {
                    if (isProcessingScan) return; // Prevent multiple triggers
                    isProcessingScan = true;

                    check_barcode(decodedText, function(confirmed) {
                        if (confirmed) {
                            stopScannerFabric();

                            const modal = bootstrap.Modal.getInstance(document.getElementById(
                                'qrScannerModalFabric'));
                            if (modal) modal.hide();
                        } else {
                            // If user cancels, you can choose:
                            // Option A: Close modal and stop scanner (safe)
                            const modal = bootstrap.Modal.getInstance(document.getElementById(
                                'qrScannerModalFabric'));
                            if (modal) modal.hide();

                            stopScannerFabric();

                            // Option B: Resume scanning again if you want to let user try again
                            // isProcessingScan = false; // <- uncomment if you want to scan again
                        }
                    });
                }



                function onScanFailure(error) {
                    console.warn(`Scan error: ${error}`);
                }

                html5QrcodeScanner_fabric = new Html5QrcodeScanner(
                    "reader_fabric", {
                        fps: 10,
                        qrbox: {
                            width: 200,
                            height: 200
                        }
                    },
                    false
                );

                html5QrcodeScanner_fabric.render(onScanSuccess, onScanFailure);
            }
        }



        function check_barcode(barcode, callback) {
            const id_item = $('#txtid_item').val();
            const id_jo = $('#txtid_jo').val();
            const no_lot = $('#txtno_lot').val();
            const no_invoice = $('#txtno_invoice').val();
            const color = $('#txtcolor').val();
            const id = $('#id').val();

            $.ajax({
                type: "POST",
                url: '{{ route('get_barcode_info') }}',
                data: {
                    _token: '{{ csrf_token() }}',
                    barcode,
                    id_item,
                    id_jo,
                    no_lot,
                    no_invoice,
                    color
                },
                success: function(response) {
                    if (response.status === 'not_found') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Data Tidak Ditemukan',
                            text: 'Barcode tidak sesuai.'
                        });
                        callback(false);
                        return;
                    }

                    Swal.fire({
                        title: 'Konfirmasi Data Fabric',
                        html: `
                    <p><b>Barcode:</b> ${response.data.barcode}</p>
                    <p><b>Supplier:</b> ${response.data.supplier}</p>
                    <p><b>No. Roll:</b> ${response.data.no_roll}</p>
                    <p><b>Color:</b> ${response.data.color}</p>
                    <p><b>Deskripsi:</b> ${response.data.itemdesc}</p>
                    <hr>
                    Lanjutkan update form dengan data ini?
                `,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Lanjutkan',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Save the data
                            $.ajax({
                                type: "POST",
                                url: '{{ route('save_fabric_form_inspect') }}',
                                data: {
                                    _token: '{{ csrf_token() }}',
                                    barcode,
                                    id
                                },
                                success: function(res) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Berhasil',
                                        text: res.message
                                    });
                                    $('#qrScannerModalFabric').modal('hide');
                                    callback(true); // tell scanner to stop
                                    location.reload();
                                },
                                error: function(err) {
                                    console.error('Save Error:', err);
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Gagal',
                                        text: 'Gagal menyimpan data.'
                                    });
                                    callback(false);
                                }
                            });
                        } else {
                            // User clicked cancel
                            callback(false); // IMPORTANT: ensure scanner continues/stops as needed
                        }
                    });
                },
                error: function(xhr) {
                    console.error('Error:', xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Terjadi kesalahan saat mengambil data fabric.'
                    });
                    callback(false);
                }
            });
        }

        function save_detail_fabric() {
            let id = $('#id').val();
            let weight = $('#txtweight').val();
            let lbs = $('#txtlbs').val();
            let width = $('#txtwidth').val();
            let unitWidth = $('#unitWidth').val();
            let act_weight = $('#txtact_weight').val();
            let gramage = $('#txtgramage').val();

            // Build summary before save
            Swal.fire({
                title: 'Konfirmasi Detail Fabric',
                html: `
            <p><b>Weight        :</b> ${weight} KG</p>
            <p><b>Width         :</b> ${width} ${unitWidth.toUpperCase()}</p>
            <p><b>Lbs           :</b> ${lbs} LBS</p>
            <p><b>Actual Weight :</b> ${act_weight} KG</p>
            <p><b>Gramage       :</b> ${gramage} GSM</p>
            <hr>
            Lanjutkan simpan data ini?
        `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Simpan',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Send data only if confirmed
                    $.ajax({
                        type: "POST",
                        url: '{{ route('save_detail_fabric') }}',
                        data: {
                            _token: '{{ csrf_token() }}',
                            id: id,
                            weight: weight,
                            width: width,
                            unitWidth: unitWidth,
                            lbs: lbs,
                            act_weight: act_weight,
                            gramage: gramage
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: response.message || 'Data berhasil disimpan.'
                            }).then(() => {
                                location.reload();
                            });
                        },
                        error: function(xhr) {
                            console.error('Save Error:', xhr.responseText);
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: 'Gagal menyimpan data.'
                            });
                        }
                    });
                }
            });
        }

        function updateFullWidthAct() {
            const fullWidth = parseFloat(document.getElementById('txtfull_width').value) || 0;
            const unit = document.getElementById('unitFullWidth').value;
            const actual = unit === 'inch' ? fullWidth : (fullWidth / 2.54);
            document.getElementById('txtfull_width_act').value = actual.toFixed(2);
        }

        // Attach event listeners
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('txtfull_width').addEventListener('input', updateFullWidthAct);
            document.getElementById('unitFullWidth').addEventListener('change', updateFullWidthAct);

            // Run once at load in case there's a preset value
            updateFullWidthAct();
        });

        function updateCuttableWidthAct() {
            const cuttableWidth = parseFloat(document.getElementById('txtcuttable_width').value) || 0;
            const unit = document.getElementById('unitCuttableWidth').value;
            const actual = unit === 'inch' ? cuttableWidth : (cuttableWidth / 2.54);
            document.getElementById('txtcuttable_width_act').value = actual.toFixed(2);
        }

        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('txtcuttable_width').addEventListener('input', updateCuttableWidthAct);
            document.getElementById('unitCuttableWidth').addEventListener('change', updateCuttableWidthAct);

            // Run once at page load
            updateCuttableWidthAct();
        });

        function save_visual_inspection() {
            let id = $('#id').val();
            let txtno_form = $('#txtno_form').val();
            let cbo_length = $('#cbo_length').val();
            let cbo_defect = $('#cbo_defect').val();
            let txtup_to_3 = $('#txtup_to_3').val();
            let txt3_6 = $('#txt3_6').val();
            let txt6_9 = $('#txt6_9').val();
            let txtovr_9 = $('#txtovr_9').val();
            let txtfull_width_act = $('#txtfull_width_act').val();
            let txtcuttable_width_act = $('#txtcuttable_width_act').val();

            // ✅ Simple validation
            if (!cbo_length || cbo_length === "") {
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan',
                    text: 'Silakan pilih LENGTH terlebih dahulu.',
                    timer: 1500,
                    showConfirmButton: false
                });
                return;
            }

            if (!cbo_defect || cbo_defect === "") {
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan',
                    text: 'Silakan pilih CRITICAL DEFECT terlebih dahulu.',
                    timer: 1500,
                    showConfirmButton: false
                });
                return;
            }

            // Send data only if confirmed
            $.ajax({
                type: "POST",
                url: '{{ route('save_visual_inspection') }}',
                data: {
                    _token: '{{ csrf_token() }}',
                    id: id,
                    txtno_form: txtno_form,
                    cbo_length: cbo_length,
                    cbo_defect: cbo_defect,
                    txtup_to_3: txtup_to_3,
                    txt3_6: txt3_6,
                    txt6_9: txt6_9,
                    txtovr_9: txtovr_9,
                    txtfull_width_act: txtfull_width_act,
                    txtcuttable_width_act: txtcuttable_width_act
                },
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: response.message || 'Data berhasil disimpan.',
                        timer: 1000, // auto close after 1 second
                        showConfirmButton: false // no OK button
                    }).then(() => {
                        reset_visual_inspection();
                        const table = $('#datatable').DataTable();
                        table.ajax.reload(null, false); // reload and stay on the same page
                        table.columns.adjust().responsive.recalc(); // fix column widths and layout
                    });
                },
                error: function(xhr) {
                    console.error('Save Error:', xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Gagal menyimpan data.'
                    });
                }
            });

        }

        function reset_visual_inspection() {
            $('#cbo_length').val('').trigger('change');
            $('#cbo_defect').val('').trigger('change');
            $('#txtup_to_3').val('');
            $('#txt3_6').val('');
            $('#txt6_9').val('');
            $('#txtovr_9').val('');
            $('#txtfull_width').val('');
            $('#txtfull_width_act').val('');
            $('#txtcuttable_width').val('');
            $('#txtcuttable_width_act').val('');
        }

        let datatable = $("#datatable").DataTable({
            ordering: false,
            responsive: true,
            processing: true,
            serverSide: false,
            paging: false,
            searching: false,
            scrollY: true,
            scrollX: false,
            scrollCollapse: false,
            ajax: {
                url: '{{ route('qc_inspect_show_visual_inspect') }}',
                data: function(d) {
                    d.id = $('#id').val();
                    d.txtno_form = $('#txtno_form').val();
                },
            },
            columns: [{
                    data: 'nm_length'
                },
                {
                    data: 'critical_defect'
                },
                {
                    data: 'up_to_3',
                    className: 'text-center'
                },
                {
                    data: '3_6',
                    className: 'text-center'
                },
                {
                    data: '6_9',
                    className: 'text-center'
                },
                {
                    data: 'over_9',
                    className: 'text-center'
                },
                {
                    data: 'hasil',
                    className: 'text-center'
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        return `
            <div class="text-center align-middle">
                <button class="btn btn-outline-danger btn-sm"
                        onclick="deleteVisualInspection(${data.id})">
                    Delete
                </button>
            </div>
        `;
                    }
                }

            ],
        });

        function deleteVisualInspection(id) {
            Swal.fire({
                title: 'Yakin ingin menghapus?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: 'POST',
                        url: '{{ route('qc_inspect_delete_visual') }}', // Your delete route
                        data: {
                            _token: '{{ csrf_token() }}',
                            id: id
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: response.message,
                                timer: 1000,
                                showConfirmButton: false
                            });
                            const table = $('#datatable').DataTable();
                            table.ajax.reload(null, false); // reload and stay on the same page
                            table.columns.adjust().responsive.recalc(); // fix column widths and layout
                        },
                        error: function() {
                            Swal.fire('Gagal', 'Data tidak dapat dihapus', 'error');
                        }
                    });
                }
            });
        }

        function updateBintexAct() {
            const bintexLength = parseFloat(document.getElementById('txtbintex_length').value) || 0;
            const unit = document.getElementById('unitBintex').value;
            const actual = unit === 'yard' ? bintexLength : (bintexLength / 0.9144);
            document.getElementById('txtbintex_act').value = actual.toFixed(2);
        }

        // Attach event listeners
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('txtbintex_length').addEventListener('input', updateBintexAct);
            document.getElementById('unitBintex').addEventListener('change', updateBintexAct);

            // Run once at load in case there's a preset value
            updateBintexAct();
        });

        function updateActLengthFix() {
            const actLength = parseFloat(document.getElementById('txtact_length').value) || 0;
            const unit = document.getElementById('unitActLength').value;
            const actual = unit === 'yard' ? actLength : (actLength / 0.9144);
            document.getElementById('txtact_length_fix').value = actual.toFixed(2);
        }

        // Attach event listeners
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('txtact_length').addEventListener('input', updateActLengthFix);
            document.getElementById('unitActLength').addEventListener('change', updateActLengthFix);

            // Run once at load for preset value
            updateActLengthFix();
        });


        function calculate_act_point() {
            let id = $('#id').val();
            let txtno_form = $('#txtno_form').val();
            let txtbintex_length = $('#txtbintex_length').val();
            let unitBintex = $('#unitBintex').val();
            let txtbintex_act = $('#txtbintex_act').val();
            let txtact_length = $('#txtact_length').val();
            let unitActLength = $('#unitActLength').val();
            let txtact_length_fix = $('#txtact_length_fix').val();

            // Send data only if confirmed
            $.ajax({
                type: "POST",
                url: '{{ route('calculate_act_point') }}',
                data: {
                    _token: '{{ csrf_token() }}',
                    id: id,
                    txtno_form: txtno_form,
                    txtbintex_length: txtbintex_length,
                    unitBintex: unitBintex,
                    txtbintex_act: txtbintex_act,
                    txtact_length: txtact_length,
                    unitActLength: unitActLength,
                    txtact_length_fix: txtact_length_fix
                },
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: response.message || 'Data berhasil disimpan.'
                    }).then(() => {
                        location.reload();
                    });
                },
                error: function(xhr) {
                    console.error('Save Error:', xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Gagal menyimpan data.'
                    });
                }
            });

        }

        let datatable_act_point = $("#datatable_act_point").DataTable({
            ordering: false,
            responsive: true,
            processing: true,
            serverSide: false,
            paging: false,
            searching: false,
            scrollY: true,
            scrollX: false,
            scrollCollapse: false,
            info: false,
            ajax: {
                url: '{{ route('qc_inspect_show_act_point') }}',
                data: function(d) {
                    d.id = $('#id').val();
                    d.txtno_form = $('#txtno_form').val();
                },
            },
            columns: [{
                    data: 'sum_up_to_3'
                },
                {
                    data: 'sum_3_6'
                },
                {
                    data: 'sum_6_9'
                },
                {
                    data: 'sum_over_9'
                },
                {
                    data: 'total_point'
                },
                {
                    data: 'act_point'
                },
                {
                    data: 'shipment',
                },
                {
                    data: 'result',
                },
            ],
        });
    </script>
@endsection
