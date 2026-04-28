@extends('layouts.index')

@section('custom-link')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">

    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

    <style type="text/css">
        input[type=file]::file-selector-button {
        margin-right: 20px;
        border: none;
        background: #084cdf;
        padding: 10px 20px;
        border-radius: 10px;
        color: #fff;
        cursor: pointer;
        transition: background .2s ease-in-out;
        }

        input[type=file]::file-selector-button:hover {
        background: #0d45a5;
        }

        .drop-container {
        position: relative;
        display: flex;
        gap: 10px;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        height: 200px;
        padding: 20px;
        border-radius: 10px;
        border: 2px dashed #555;
        color: #444;
        cursor: pointer;
        transition: background .2s ease-in-out, border .2s ease-in-out;
        }

        .drop-container:hover {
        background: #eee;
        border-color: #111;
        }

        .drop-container:hover .drop-title {
        color: #222;
        }

        .drop-title {
        color: #444;
        font-size: 20px;
        font-weight: bold;
        text-align: center;
        transition: color .2s ease-in-out;
        }
    </style>
@endsection

@section('content')
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title">
                <i class="fa-solid fa-screwdriver-wrench"></i> Cutting Tools
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <h5 class="text-sb-secondary fw-bold mt-3">Modify Data</h5>
                </div>
                <div class="col-md-4">
                    <a type="button" class="home-item" data-bs-toggle="modal" data-bs-target="#modifyFormRatio">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="text-sb mb-0"><i class="fa-solid fa-gears"></i> Modify Form Ratio</h5>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-4">
                    <a type="button" class="home-item" data-bs-toggle="modal" data-bs-target="#modifyFormMarker">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="text-sb mb-0"><i class="fa-solid fa-gears"></i> Modify Form Marker</h5>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-4">
                    <a type="button" class="home-item" data-bs-toggle="modal" data-bs-target="#modifyFormGroup">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="text-sb mb-0"><i class="fa-solid fa-gears"></i> Modify Form Group</h5>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-4 d-none">
                    <a type="button" class="home-item" data-bs-toggle="modal" data-bs-target="#modifyFormSwap">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="text-sb mb-0"><i class="fa-solid fa-gears"></i> Modify Form Swap</h5>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-12">
                    <h5 class="text-sb-secondary fw-bold mt-3">Synchronize Data</h5>
                </div>
                <div class="col-md-4">
                    <a type="button" class="home-item" data-bs-toggle="modal" data-bs-target="#fixRollQty">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="text-sb mb-0"><i class="fa-solid fa-gears"></i> Fix Roll Qty</h5>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-4">
                    <a type="button" class="home-item" data-bs-toggle="modal" data-bs-target="#deleteRedundantRoll">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="text-sb mb-0"><i class="fa-solid fa-gears"></i> Delete Redundant</h5>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-12">
                    <h5 class="text-sb-secondary fw-bold mt-3">Populate Data</h5>
                </div>
                <div class="col-md-4">
                    <a type="button" class="home-item" onclick="openImportCuttingManual()">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="text-sb mb-0"><i class="fa-solid fa-gears"></i> Import Cutting Manual</h5>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-4">
                    <a type="button" class="home-item" onclick="openImportSaldoAwalCutting()">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="text-sb mb-0"><i class="fa-solid fa-gears"></i> Inject Saldo Awal Cutting</h5>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Delete Redundant Roll --}}
    <div class="modal fade" id="deleteRedundantRoll" tabindex="-1" aria-labelledby="deleteRedundantRollLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-sb">
                    <h1 class="modal-title fs-5" id="deleteRedundantRollLabel">Delete Redundant Roll</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">ID Roll</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="delete_roll_id">
                            <button class="btn btn-sb" onclick="fetchScan(document.getElementById('delete_roll_id'), 'delete_roll_')">Get</button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ID Item</label>
                        <input type="text" class="form-control" id="delete_roll_id_item" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <input type="text" class="form-control" id="delete_roll_detail_item" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Color</label>
                        <input type="text" class="form-control" id="delete_roll_color" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Buyer</label>
                        <input type="text" class="form-control" id="delete_roll_buyer" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">No. WS</label>
                        <input type="text" class="form-control" id="delete_roll_no_ws" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Style</label>
                        <input type="text" class="form-control" id="delete_roll_style" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Lot</label>
                        <input type="text" class="form-control" id="delete_roll_lot" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">No. Roll</label>
                        <input type="text" class="form-control" id="delete_roll_no_roll" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Qty</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="delete_roll_qty" readonly>
                            <input type="text" class="form-control" id="delete_roll_unit" readonly>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sb btn-block" onclick="deleteRedundantRoll()">Delete Redundant Roll</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Fix Roll Qty -->
    <div class="modal fade" id="fixRollQty" tabindex="-1" aria-labelledby="fixRollQtyLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-sb">
                    <h1 class="modal-title fs-5" id="fixRollQtyLabel">Fix Roll Qty</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">ID Roll</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="fix_roll_id">
                            <button class="btn btn-sb" onclick="fetchScan(document.getElementById('fix_roll_id'), 'fix_roll_')">Get</button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ID Item</label>
                        <input type="text" class="form-control" id="fix_roll_id_item" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <input type="text" class="form-control" id="fix_roll_detail_item" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Color</label>
                        <input type="text" class="form-control" id="fix_roll_color" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Buyer</label>
                        <input type="text" class="form-control" id="fix_roll_buyer" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">No. WS</label>
                        <input type="text" class="form-control" id="fix_roll_no_ws" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Style</label>
                        <input type="text" class="form-control" id="fix_roll_style" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Lot</label>
                        <input type="text" class="form-control" id="fix_roll_lot" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">No. Roll</label>
                        <input type="text" class="form-control" id="fix_roll_no_roll" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Qty</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="fix_roll_qty">
                            <input type="text" class="form-control" id="fix_roll_unit" readonly>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sb btn-block" onclick="fixRollQty()">Fix Roll Qty</button>
                    <hr class="border-sb">
                    <h5 class="text-center">OR</h5>
                    <hr class="border-sb">
                    <button type="button" class="btn btn-sb-secondary btn-block" onclick="fixRollQtyUpdate()">Fix All Roll Qty</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modify Form Ratio -->
    <div class="modal fade" id="modifyFormRatio" tabindex="-1" aria-labelledby="modifyFormRatioLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-sb">
                    <h1 class="modal-title fs-5" id="modifyFormRatioLabel">Modify Form Ratio</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('update-form-ratio') }}" method="post" onsubmit="submitForm(this, event)">
                        <div class="mb-3">
                            <label class="form-label">No. Form</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="modify_ratio_no_form" name="modify_ratio_no_form">
                                <button type="button" class="btn btn-sb" onclick="getFormCut(document.getElementById('modify_ratio_no_form').value, 'modify_ratio_')">Get</button>
                            </div>
                        </div>
                        <div class="mb-3 d-none">
                            <label class="form-label">ID</label>
                            <input type="hidden" class="form-control" id="modify_ratio_form_id" name="modify_ratio_form_id" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Kode Marker</label>
                            <input type="text" class="form-control" id="modify_ratio_kode_marker" name="modify_ratio_kode_marker" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">No. WS</label>
                            <input type="text" class="form-control" id="modify_ratio_no_ws" name="modify_ratio_no_ws" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Style</label>
                            <input type="text" class="form-control" id="modify_ratio_style" name="modify_ratio_style" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Color</label>
                            <input type="text" class="form-control" id="modify_ratio_color" name="modify_ratio_color" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Panel</label>
                            <input type="text" class="form-control" id="modify_ratio_panel" name="modify_ratio_panel" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Qty Ply</label>
                            <input type="text" class="form-control" id="modify_ratio_qty_ply" name="modify_ratio_qty_ply" readonly>
                        </div>
                        <div class="table-responsive mb-3">
                            <table class="table table-bordered" id="modify_ratio_table">
                                <thead>
                                    <th>So Det Id</th>
                                    <th>Size</th>
                                    <th>Ratio</th>
                                    <th>Qty Cut</th>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                        <button type="submit" class="btn btn-sb btn-block">Simpan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Modify Form Marker --}}
    <div class="modal fade" id="modifyFormMarker" tabindex="-1" aria-labelledby="modifyFormMarkerLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-sb">
                    <h1 class="modal-title fs-5" id="modifyFormMarkerLabel">Modify Form Marker</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('update-form-marker') }}" method="post" onsubmit="submitForm(this, event)" id="modify-form-marker">
                        <div class="mb-3">
                            <label class="form-label">No. Form</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="modify_marker_no_form" name="modify_marker_no_form">
                                <button type="button" class="btn btn-sb" onclick="getFormMarker(document.getElementById('modify_marker_no_form').value, 'modify_marker_')">Get</button>
                            </div>
                        </div>
                        <div class="mb-3 d-none">
                            <label class="form-label">ID</label>
                            <input type="hidden" class="form-control" id="modify_marker_form_id" name="modify_marker_form_id" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Kode Marker</label>
                            <input type="text" class="form-control" id="modify_marker_kode_marker" name="modify_marker_kode_marker" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">No. WS</label>
                            <select class="form-control select2bs4formmarker" id="modify_marker_no_ws" name="modify_marker_no_ws" onchange="getColors(this, 'modify_marker_')">
                                <option value=""></option>
                                @foreach ($orders as $order)
                                    <option value="{{ $order->id }}" >{{ $order->kpno }}</option>
                                @endforeach
                            </select>
                            <input type="hidden" class="form-control" id="modify_marker_no_ws_input" name="modify_marker_no_ws_input">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Style</label>
                            <input type="text" class="form-control" id="modify_marker_style" name="modify_marker_style" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Color</label>
                            <select class="form-control select2bs4formmarker" id="modify_marker_color" name="modify_marker_color" onchange="getPanels('modify_marker_')">
                                <option value=""></option>
                            </select>
                            <input type="hidden" class="form-control" id="modify_marker_color_input" name="modify_marker_color_input">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Panel</label>
                            <select class="form-control select2bs4formmarker" id="modify_marker_panel" name="modify_marker_panel">
                                <option value=""></option>
                            </select>
                            <input type="hidden" class="form-control" id="modify_marker_panel_input" name="modify_marker_panel_input">
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="bypass" id="modify_bypass_stocker" name="modify_bypass_stocker">
                                <label class="form-check-label" for="modify_bypass_stocker">
                                    Bypass Stocker
                                </label>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-sb btn-block mt-3">Simpan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modify Form Swap -->
    <div class="modal fade" id="modifyFormSwap" tabindex="-1" aria-labelledby="modifyFormSwapLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-sb">
                    <h1 class="modal-title fs-5" id="modifyFormSwapLabel">Modify Form Swap</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('update-form-swap') }}" method="post" onsubmit="submitForm(this, event)">
                        <div class="mb-3">
                            <label class="form-label">No. Form</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="modify_swap_no_form" name="modify_swap_no_form">
                                <button type="button" class="btn btn-sb" onclick="getFormCut(document.getElementById('modify_swap_no_form').value, 'modify_swap_')">Get</button>
                            </div>
                        </div>
                        <div class="mb-3 d-none">
                            <label class="form-label">ID</label>
                            <input type="hidden" class="form-control" id="modify_swap_form_id" name="modify_swap_form_id" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Kode Marker</label>
                            <input type="text" class="form-control" id="modify_swap_kode_marker" name="modify_swap_kode_marker" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">No. WS</label>
                            <input type="text" class="form-control" id="modify_swap_no_ws" name="modify_swap_no_ws" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Style</label>
                            <input type="text" class="form-control" id="modify_swap_style" name="modify_swap_style" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Color</label>
                            <input type="text" class="form-control" id="modify_swap_color" name="modify_swap_color" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Panel</label>
                            <input type="text" class="form-control" id="modify_swap_panel" name="modify_swap_panel" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Qty Ply</label>
                            <input type="text" class="form-control" id="modify_swap_qty_ply" name="modify_swap_qty_ply" readonly>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">From Size</label>
                                <select class="form-control select2bs4formswap" name="modify_swap_from" id="modify_swap_from">
                                    <option value=""></option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">To Size</label>
                                <select class="form-control select2bs4formswap" name="modify_swap_to" id="modify_swap_to">
                                    <option value=""></option>
                                </select>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-sb btn-block">Simpan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Modify Form Group --}}
    <div class="modal fade" id="modifyFormGroup" tabindex="-1" aria-labelledby="modifyFormGroupLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-sb">
                    <h1 class="modal-title fs-5" id="modifyFormGroupLabel">Modify Form Group</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">No. Form</label>
                        <select name="no_form_modify_group" id="no_form_modify_group" class="form-control select2bs4formgroup" style="width: 100%;"  onchange="getFormGroupList();setFormType();">
                            <option value="">Pilih Form</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Form Type</label>
                        <input type="text" class="form-control" id="form_type_modify_group" id="form_type_modify_group" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Group</label>
                        <select name="form_group_modify_group" id="form_group_modify_group" class="form-control select2bs4formgroup" style="width: 100%;">
                            <option value="">Pilih Form Group</option>
                        </select>
                    </div>
                    <hr class="border-top mt-3" style="border: 1px solid black;">
                    <h6 class="text-sb fw-bold text-center">UPDATE</h6>
                    <div>
                        <label class="form-label">New Group</label>
                        <input type="text" name="form_group_new_modify_group" id="form_group_new_modify_group" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="fa fa-times"></i> Batal</button>
                    <button type="button" class="btn btn-sb" onclick="modifyFormGroup()"><i class="fa fa-save"></i> Ubah</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Import Manual --}}
    <div class="modal fade" id="importExcel" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form method="post" action="{{ route('import-cutting-manual') }}" enctype="multipart/form-data" onsubmit="submitImportCuttingManual(this, event)">
                <div class="modal-content">
                    <div class="modal-header bg-sb text-light">
                        <h5 class="modal-title" id="exampleModalLabel">Import Cutting Manual</h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        {{ csrf_field() }}
                        <label for="images" class="drop-container" id="dropcontainer">
                            <input type="file" name="file" required="required">
                        </label>
                        <a href="{{ asset('example/contoh-import-cutting-manual.xlsx') }}" download class="btn btn-sb-secondary btn-sm"><i class="fa fa-solid fa-download"></i> Contoh Excel</a>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="fa fa-window-close" aria-hidden="true"></i> Close</button>
                        <button type="submit" class="btn btn-sb toastsDefaultDanger"><i class="fa fa-thumbs-up" aria-hidden="true"></i> Import</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Inject Cutting Saldo Awal --}}
    <div class="modal fade" id="importSaldoAwalExcel" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <form method="post" action="{{ route('save-saldo-awal-cutting') }}" enctype="multipart/form-data" id="importSaldoAwalForm" onsubmit="submitSaldoAwalCutting(this, event)">
                <div class="modal-content">
                    <div class="modal-header bg-sb text-light">
                        <h5 class="modal-title" id="exampleModalLabel">Inject Saldo Awal Cutting PCS</h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        {{ csrf_field() }}
                        <label for="images" class="drop-container" id="dropcontainer">
                            <input type="file" name="file" id="saldoAwalFileInput" onchange="submitImportSaldoAwalCutting(document.getElementById('importSaldoAwalForm'), event)">
                        </label>
                        <div class="d-flex justify-content-between">
                            <a href="{{ asset('example/contoh-import-cutting-saldo-awal.xlsx') }}" download class="btn btn-sb-secondary btn-sm"><i class="fa fa-solid fa-download"></i> Contoh Excel</a>
                            <button class="btn btn-no btn-sm" onclick="emptySaldoAwal('actual')" type="button"><i class="fa fa-trash"></i> Kosongkan Saldo Awal</button>
                        </div>

                        <div class="table-responsive mt-5">
                            <table class="table table-bordered w-100" id="saldo-awal-cutting-pcs-table">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>ID SO Detail</th>
                                        <th>Buyer</th>
                                        <th>No. WS</th>
                                        <th>Style</th>
                                        <th>Color</th>
                                        <th>Size</th>
                                        <th>Panel</th>
                                        <th>Qty</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-end my-3">
                            <button class="btn btn-danger btn-sm" onclick="emptySaldoAwal('tmp')" type="button"><i class="fa fa-trash"></i> Kosongkan Saldo Awal Temporary</button>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="fa fa-window-close" aria-hidden="true"></i> Close</button>
                        <button type="submit" class="btn btn-sb toastsDefaultDanger"><i class="fa fa-thumbs-up" aria-hidden="true"></i> Inject</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('custom-script')
    <!-- DataTables & Plugins -->
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-rowsgroup/dataTables.rowsGroup.js') }}"></script>

    <!-- Select2 -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        $('.select2').select2({
            theme: 'bootstrap4'
        });
        $('.select2bs4').select2({
            theme: 'bootstrap4'
        });
        $('.select2bs4form').select2({
            theme: 'bootstrap4',
            dropdownParent: $('#fixRollQty')
        });
        $('.select2bs4formmarker').select2({
            theme: 'bootstrap4',
            dropdownParent: $('#modifyFormMarker')
        });
        $('.select2bs4formswap').select2({
            theme: 'bootstrap4',
            dropdownParent: $('#modifyFormSwap')
        });
        $('.select2bs4formgroup').select2({
            theme: 'bootstrap4',
            dropdownParent: $('#modifyFormGroup')
        });

        $(document).ready(function () {
            document.getElementById("modify-form-marker").reset();

            getFormList();
        });

        document.getElementById("fix_roll_id").addEventListener("onkeydown", (event) => {
            if (event.keyCode == 13) {
                fetchScan(this, "fix_roll_");
            }
        })

        function fetchScan(element, prefix) {
            let idRoll = element.value;

            getScannedItem(idRoll, prefix);
        }

        async function getScannedItem(id, prefix) {

            document.getElementById("loading").classList.remove("d-none");

            document.getElementById(prefix+"id_item").value = "";
            document.getElementById(prefix+"detail_item").value = "";
            document.getElementById(prefix+"color").value = "";
            document.getElementById(prefix+"buyer").value = "";
            document.getElementById(prefix+"no_ws").value = "";
            document.getElementById(prefix+"lot").value = "";
            document.getElementById(prefix+"no_roll").value = "";
            document.getElementById(prefix+"qty").value = "";
            document.getElementById(prefix+"unit").value = "";

            if (isNotNull(id)) {
                return $.ajax({
                    url: '{{ route('get-roll-qty') }}',
                    type: 'get',
                    data: {
                        id: id
                    },
                    dataType: 'json',
                    success: function(res) {
                        if (typeof res === 'object' && res !== null) {
                            document.getElementById(prefix+"id_item").value = res.id_item;
                            document.getElementById(prefix+"detail_item").value = res.detail_item;
                            document.getElementById(prefix+"buyer").value = res.buyer;
                            document.getElementById(prefix+"no_ws").value = res.no_ws;
                            document.getElementById(prefix+"style").value = res.style;
                            document.getElementById(prefix+"color").value = res.color;
                            document.getElementById(prefix+"lot").value = res.lot;
                            document.getElementById(prefix+"no_roll").value = res.no_roll;
                            document.getElementById(prefix+"qty").value = res.qty;
                            document.getElementById(prefix+"unit").value = res.unit;

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
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: 'Roll tidak tersedia.',
                            showCancelButton: false,
                            showConfirmButton: true,
                            confirmButtonText: 'Oke',
                        });

                        document.getElementById("loading").classList.add("d-none");
                    }
                });
            }

            document.getElementById("loading").classList.add("d-none");

            return Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: 'Item tidak ditemukan',
                showCancelButton: false,
                showConfirmButton: true,
                confirmButtonText: 'Oke',
            });
        }

        async function fixRollQty() {
            let idRoll = document.getElementById("fix_roll_id").value;
            let qtyRoll = document.getElementById("fix_roll_qty").value;

            if (!idRoll) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Invalid Input',
                    text: 'Please enter a valid Roll ID.',
                });
                return;
            }

            await fixRollQtyUpdate(idRoll, qtyRoll);
        }

        async function fixRollQtyUpdate(idRoll = null, qtyRoll = null) {
            document.getElementById("loading").classList.remove("d-none");

            $.ajax({
                type: "post",
                url: "{{ route('fix-roll-qty') }}",
                data: {
                    id_roll: idRoll,
                    qty: qtyRoll,
                },
                dataType: "json",
                success: function (response) {
                    if (response && response.status == 200) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: response.message,
                            showCancelButton: false,
                            showConfirmButton: true,
                            confirmButtonText: 'Oke',
                        });

                        if (idRoll) {
                            getScannedItem(idRoll, "fix_roll_");
                        }
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: 'Roll yang tidak sesuai tidak ditemukan',
                            showCancelButton: false,
                            showConfirmButton: true,
                            confirmButtonText: 'Oke',
                        });
                    }

                    document.getElementById("loading").classList.add("d-none");
                }
            });
        }

        async function getFormCut(noForm, prefix) {
            document.getElementById("loading").classList.remove("d-none");

            document.getElementById(prefix+"form_id").value = "";
            document.getElementById(prefix+"kode_marker").value = "";
            document.getElementById(prefix+"no_ws").value = "";
            document.getElementById(prefix+"style").value = "";
            document.getElementById(prefix+"color").value = "";
            document.getElementById(prefix+"panel").value = "";
            document.getElementById(prefix+"qty_ply").value = "";

            // RATIO
            if (prefix == "modify_ratio_") {
                document.getElementById(prefix+"table").innerHTML = "";
            }

            // SWAP
            if (prefix == "modify_swap_") {
                document.getElementById(prefix+"from").innerHTML = "";
                document.getElementById(prefix+"to").innerHTML = "";
            }

            if (isNotNull(noForm)) {
                return $.ajax({
                    url: '{{ route('get-form-ratio') }}',
                    type: 'get',
                    data: {
                        no_form: noForm
                    },
                    dataType: 'json',
                    success: function(res) {
                        if (typeof res === 'object' && res !== null) {
                            document.getElementById(prefix+"form_id").value = res.form_id;
                            document.getElementById(prefix+"kode_marker").value = res.kode_marker;
                            document.getElementById(prefix+"no_ws").value = res.no_ws;
                            document.getElementById(prefix+"style").value = res.style;
                            document.getElementById(prefix+"color").value = res.color;
                            document.getElementById(prefix+"panel").value = res.panel;
                            document.getElementById(prefix+"qty_ply").value = res.qty_ply;

                            // RATIO
                            if (prefix == "modify_ratio_") {
                                if (res.ratio && res.ratio.length > 0) {
                                    let tableTbodyInnerHTML = `
                                        <tr>
                                            <th>So Det</th>
                                            <th>Size</th>
                                            <th>Ratio</th>
                                            <th>Qty Cut</th>
                                        </tr>
                                    `;

                                    res.ratio.forEach((item, i) => {
                                        tableTbodyInnerHTML += `
                                            <tr>
                                                <td><input type='text' class='form-control' value='`+item.so_det_id+`' id='`+prefix+`so_det_id_`+i+`' data-index='`+i+`' name='`+prefix+`so_det_id[`+i+`]' readonly /></td>
                                                <td><input type='text' class='form-control' value='`+item.size+`' id='`+prefix+`size_`+i+`' data-index='`+i+`' name='`+prefix+`size[`+i+`]' readonly /></td>
                                                <td><input type='number' class='form-control' value='`+item.ratio+`' id='`+prefix+`ratio_`+i+`' data-index='`+i+`' name='`+prefix+`ratio[`+i+`]' onkeyup='calculateRatio(this)' onchange='calculateRatio(this)'/></td>
                                                <td><input type='number' class='form-control' value='`+item.cut_qty+`' id='`+prefix+`cut_qty_`+i+`' data-index='`+i+`' name='`+prefix+`cut_qty[`+i+`]' readonly /></td>
                                            </tr>
                                        `;
                                    });

                                    document.getElementById(prefix+"table").innerHTML = tableTbodyInnerHTML;
                                }
                            }

                            // SWAP
                            if (prefix == 'modify_swap_') {
                                let optionHTML = "";
                                res.ratio.forEach((item, i) => {
                                    optionHTML += `
                                        <option value='`+item.so_det_id+`'>`+item.size+` (`+item.cut_qty+`)</option>
                                    `;
                                });

                                document.getElementById(prefix+"from").innerHTML = optionHTML;
                                document.getElementById(prefix+"to").innerHTML = optionHTML;
                            }
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: res ? res : 'Form tidak ditemukan.',
                                showCancelButton: false,
                                showConfirmButton: true,
                                confirmButtonText: 'Oke',
                            });
                        }

                        document.getElementById("loading").classList.add("d-none");
                    },
                    error: function(jqXHR) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: 'Form tidak ditemukan.',
                            showCancelButton: false,
                            showConfirmButton: true,
                            confirmButtonText: 'Oke',
                        });

                        document.getElementById("loading").classList.add("d-none");
                    }
                });
            }

            document.getElementById("loading").classList.add("d-none");

            return Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: 'No. Form tidak ditemukan',
                showCancelButton: false,
                showConfirmButton: true,
                confirmButtonText: 'Oke',
            });
        }

        async function getFormMarker(noForm, prefix) {
            document.getElementById("loading").classList.remove("d-none");

            $("#"+prefix+"form_id").val("");
            $("#"+prefix+"kode_marker").val("");
            $("#"+prefix+"no_ws").val("");
            $("#"+prefix+"style").val("");
            $("#"+prefix+"color").val("");
            $("#"+prefix+"panel").val("");

            if (isNotNull(noForm)) {
                return $.ajax({
                    url: '{{ route('get-form-marker') }}',
                    type: 'get',
                    data: {
                        no_form: noForm
                    },
                    dataType: 'json',
                    success: function(res) {
                        if (typeof res === 'object' && res !== null) {
                            $("#"+prefix+"form_id").val(res.form_id).trigger("change");
                            $("#"+prefix+"kode_marker").val(res.kode_marker).trigger("change");
                            $("#"+prefix+"no_ws").val(res.no_ws).trigger("change");
                            $("#"+prefix+"no_ws_input").val(res.no_ws_input).trigger("change");
                            $("#"+prefix+"style").val(res.style).trigger("change");
                            $("#"+prefix+"style_input").val(res.style).trigger("change");
                            $("#"+prefix+"color_input").val(res.color).trigger("change");
                            $("#"+prefix+"panel_input").val(res.panel).trigger("change");
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: res ? res : 'Form tidak ditemukan.',
                                showCancelButton: false,
                                showConfirmButton: true,
                                confirmButtonText: 'Oke',
                            });
                        }

                        document.getElementById("loading").classList.add("d-none");
                    },
                    error: function(jqXHR) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: 'Form tidak ditemukan.',
                            showCancelButton: false,
                            showConfirmButton: true,
                            confirmButtonText: 'Oke',
                        });

                        document.getElementById("loading").classList.add("d-none");
                    }
                });
            }

            document.getElementById("loading").classList.add("d-none");

            return Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: 'No. Form tidak ditemukan',
                showCancelButton: false,
                showConfirmButton: true,
                confirmButtonText: 'Oke',
            });
        }

        function getColors(element, prefix) {
            let order = element.value;

            // if (order) {
                document.getElementById("loading").classList.remove("d-none");

                $.ajax({
                    url: "{{ route('get-colors') }}",
                    type: "GET",
                    data: { act_costing_id: order },
                    success: function (response) {
                        document.getElementById("loading").classList.add("d-none");

                        if (response) {
                            $('#'+prefix+'color').empty().append('<option value="">Pilih Color</option>');
                            response.forEach(function (color) {
                                $('#'+prefix+'color').append(`<option value="${color.color}">${color.color}</option>`);
                            });
                            $('#'+prefix+'color').val($('#'+prefix+'color_input').val()).trigger('change');
                        }
                    },
                    error: function (jqXHR) {
                        document.getElementById("loading").classList.add("d-none");

                        console.error(jqXHR);
                    }
                });
            // }
        }

        function getPanels(prefix) {
            let order = $("#"+prefix+"no_ws").val();
            let color = $("#"+prefix+"color").val();

            // if (order && color) {
                document.getElementById("loading").classList.remove("d-none");

                $.ajax({
                    url: "{{ route('get-panels') }}",
                    type: "GET",
                    data: {
                        act_costing_id: order,
                        color: color,
                    },
                    success: function (response) {
                        document.getElementById("loading").classList.add("d-none");

                        if (response) {
                            $('#'+prefix+'panel').empty().append('<option value="">Pilih Panel</option>');
                            response.forEach(function (panel) {
                                $('#'+prefix+'panel').append(`<option value="${panel.panel}">${panel.panel}</option>`);
                            });
                            $('#'+prefix+'panel').val($('#'+prefix+'panel_input').val()).trigger('change');
                        }
                    },
                    error: function (jqXHR) {
                        document.getElementById("loading").classList.add("d-none");

                        console.error(jqXHR);
                    }
                });
            // }
        }

        function calculateRatio(el) {
            let ratio = el.value;
            let ratioIndex = el.getAttribute("data-index");

            if (document.getElementById("modify_ratio_cut_qty_"+ratioIndex)) {
                if (ratio && ratio > 0) {
                    let qtyPly = document.getElementById("modify_ratio_qty_ply").value;
                    let qtyCut = qtyPly * ratio;

                    if (qtyCut) {
                        return document.getElementById("modify_ratio_cut_qty_"+ratioIndex).value = qtyCut;
                    } else {
                        document.getElementById("modify_ratio_cut_qty_"+ratioIndex).value = 0;
                    }
                } else {
                    document.getElementById("modify_ratio_cut_qty_"+ratioIndex).value = 0;
                }
            }
        }

        function getFormList() {
            document.getElementById("loading").classList.remove("d-none");

            $.ajax({
                type: "get",
                url: "{{ route('get-no-form-cut') }}",
                dataType: "json",
                success: function (response) {
                    if (response) {
                        $('#no_form_modify_group').empty();
                        $('#no_form_modify_group').append('<option value="">Pilih Form</option>');
                        $.each(response, function (key, value) {
                            $('#no_form_modify_group').append('<option value="' + value.form_cut_id + '" data-type="'+value.type+'">' + value.no_form + '</option>');
                        });
                    }

                    document.getElementById("form_type_modify_group").value = "";

                    document.getElementById("loading").classList.add("d-none");
                },
                error: function (xhr, status, error) {
                    console.error(xhr);

                    document.getElementById("loading").classList.add("d-none");
                }
            });
        }

        function setFormType() {
            const formType = $('#no_form_modify_group option:selected').attr('data-type');
            $('#form_type_modify_group').val(formType);
        }

        function getFormGroupList() {
            document.getElementById("loading").classList.remove("d-none");

            $.ajax({
                type: "get",
                url: "{{ route('get-form-group') }}",
                data: {
                    form_cut_id: $("#no_form_modify_group").val(),
                    form_group: $('#form_group_modify_group').val() ? $('#form_group_modify_group').val() : null,
                    form_type: $('#no_form_modify_group option:selected').attr('data-type')
                },
                dataType: "json",
                success: function (response) {
                    if (response) {
                        $('#form_group_modify_group').empty();
                        $('#form_group_modify_group').append('<option value="">Pilih Form Group</option>');
                        $.each(response, function (key, value) {
                            $('#form_group_modify_group').append('<option value="' + (value.group_stocker ? value.group_stocker : "") + '">' + (value.group_stocker ? value.group_stocker : '/') + " - " + (value.shade ? value.shade : '/') + '</option>');
                        });

                        document.getElementById("loading").classList.add("d-none");
                    }
                },
                error: function (xhr, status, error) {
                    console.error(xhr);

                    document.getElementById("loading").classList.add("d-none");
                }
            });
        }

        function modifyFormGroup() {
            Swal.fire({
                title: 'Update Form Group?',
                html: 'Yakin akan mengubah data group form <br> '+$('#no_form_modify_group option:selected').text()+' / '+$('#form_group_modify_group option:selected').text()+' <br> ke <br> '+$('#form_group_new_modify_group').val()+'?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'UBAH',
                cancelButtonText: 'BATAL',
                confirmButtonColor: "#dc3545"
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById("loading").classList.remove("d-none");

                    $.ajax({
                        type: "post",
                        url: "{{ route("update-form-group") }}",
                        data: {
                            form_cut_id: $('#no_form_modify_group').val(),
                            form_type: $('#no_form_modify_group option:selected').attr('data-type'),
                            no_form: $('#no_form_modify_group option:selected').text(),
                            form_group: $('#form_group_modify_group').val() ? $('#form_group_modify_group').val() : null,
                            form_group_new: $('#form_group_new_modify_group').val() ? $('#form_group_new_modify_group').val() : null,
                        },
                        dataType: "json",
                        success: function (response) {
                            document.getElementById("loading").classList.add("d-none");

                            console.log(response);
                            if (response.status == 200) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil',
                                    html: response.message,
                                    showCancelButton: false,
                                    showConfirmButton: true,
                                    confirmButtonText: 'Oke',
                                });

                                clearModifyFormGroup();
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal',
                                    html: response.message,
                                    showCancelButton: false,
                                    showConfirmButton: true,
                                    confirmButtonText: 'Oke',
                                });
                            }
                        }, error (jqXHR) {
                            document.getElementById("loading").classList.add("d-none");

                            console.error(jqXHR);

                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: "Terjadi Kesalahan.",
                                showCancelButton: false,
                                showConfirmButton: true,
                                confirmButtonText: 'Oke',
                            });
                        }
                    });
                }
            })
        }

        function clearModifyFormGroup() {
            $('#no_form_modify_group').val("").trigger("change");
            $('#no_form_modify_group').val("").trigger("change");
            $('#no_form_modify_group').val("").trigger("change");
            $('#form_group_modify_group').val("").trigger("change");
            $('#form_group_modify_group').val("").trigger("change");
            $('#form_group_new_modify_group').val("").trigger("change");
        }

        function deleteRedundantRoll() {
            Swal.fire({
                title: 'Hapus Redundant Roll?',
                html: 'Yakin akan menghapus redundant roll? <br> '+$('#delete_roll_id').val()+'?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'UBAH',
                cancelButtonText: 'BATAL',
                confirmButtonColor: "#dc3545"
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById("loading").classList.remove("d-none");

                    $.ajax({
                        type: "post",
                        url: "{{ route("delete-redundant-roll") }}",
                        data: {
                            id_roll: $('#delete_roll_id').val(),
                        },
                        dataType: "json",
                        success: function (response) {
                            document.getElementById("loading").classList.add("d-none");

                            console.log(response);

                            if (response.status == 200) {
                                Swal.fire({
                                    icon: "success",
                                    title: "Proses Selesai",
                                    html: response.message,
                                    showCancelButton: false,
                                    showConfirmButton: true,
                                    confirmButtonText: 'Oke',
                                });
                            } else {
                                Swal.fire({
                                    icon: "error",
                                    title: "Proses Gagal",
                                    html: response.message,
                                    showCancelButton: false,
                                    showConfirmButton: true,
                                    confirmButtonText: 'Oke',
                                });
                            }
                        },
                        error: function (jqXHR) {
                            document.getElementById("loading").classList.add("d-none");

                            console.error(jqXHR);

                            Swal.fire({
                                icon: "error",
                                title: "Terjadi Kesalahan",
                                showCancelButton: false,
                                showConfirmButton: true,
                                confirmButtonText: 'Oke',
                            });
                        }
                    });
                }
            });
        }

        function openImportCuttingManual(){
            $('#importExcel').modal('show');

            saldoAwalCuttingReload();
        }

        function submitImportCuttingManual(e, evt) {
            document.getElementById("loading").classList.remove("d-none");

            evt.preventDefault();

            clearModified();

            $.ajax({
                url: e.getAttribute('action'),
                type: e.getAttribute('method'),
                data: new FormData(e),
                processData: false,
                contentType: false,
                success: async function(res) {
                    document.getElementById("loading").classList.add("d-none");

                    if (res.status == 200) {
                        console.log(res);

                        e.reset();

                        Swal.fire({
                            icon: 'success',
                            title: 'Data Cutting berhasil diupload',
                            showCancelButton: false,
                            showConfirmButton: true,
                            confirmButtonText: 'Oke',
                            timer: 5000,
                            timerProgressBar: true
                        });
                    }
                },
                error: function (jqXHR) {
                    document.getElementById("loading").classList.add("d-none");

                    console.error(jqXHR);
                }
            });
        }

        function openImportSaldoAwalCutting() {
            $('#importSaldoAwalExcel').modal('show');

            saldoAwalCuttingReload();
        }

        function submitImportSaldoAwalCutting(e, evt) {
            document.getElementById("loading").classList.remove("d-none");

            if (evt && evt.preventDefault) {
                evt.preventDefault();
            }

            // Get file input
            const fileInput = e.querySelector('input[type="file"]');

            if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
                document.getElementById("loading").classList.add("d-none");
                Swal.fire({
                    icon: 'warning',
                    title: 'File Belum Dipilih',
                    text: 'Silakan pilih file Excel terlebih dahulu.',
                    showCancelButton: false,
                    showConfirmButton: true,
                    confirmButtonText: 'Oke',
                });
                return;
            }

            // Validate file extension
            const file = fileInput.files[0];
            const validExtensions = ['xlsx', 'xls'];
            const fileExtension = file.name.split('.').pop().toLowerCase();

            if (!validExtensions.includes(fileExtension)) {
                document.getElementById("loading").classList.add("d-none");
                Swal.fire({
                    icon: 'error',
                    title: 'Format File Salah',
                    text: 'Hanya file Excel (.xlsx, .xls) yang diperbolehkan.',
                    showCancelButton: false,
                    showConfirmButton: true,
                    confirmButtonText: 'Oke',
                });
                return;
            }

            $.ajax({
                url: '{{ route('import-saldo-awal-cutting') }}',
                type: 'post',
                data: new FormData(e),
                processData: false,
                contentType: false,
                success: async function(res) {
                    document.getElementById("loading").classList.add("d-none");

                    if (res.status == 200) {
                        console.log(res);

                        e.reset();
                        fileInput.value = '';

                        Swal.fire({
                            icon: 'success',
                            title: 'Data Cutting berhasil diupload',
                            showCancelButton: false,
                            showConfirmButton: true,
                            confirmButtonText: 'Oke'
                        }).then(() => {
                            saldoAwalCuttingReload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: res.message || 'Terjadi kesalahan saat upload file',
                            showCancelButton: false,
                            showConfirmButton: true,
                            confirmButtonText: 'Oke',
                        });
                    }
                },
                error: function (jqXHR) {
                    document.getElementById("loading").classList.add("d-none");

                    console.error(jqXHR);

                    Swal.fire({
                        icon: 'error',
                        title: 'Terjadi Kesalahan',
                        text: 'Gagal mengupload file. Silakan coba lagi.',
                        showCancelButton: false,
                        showConfirmButton: true,
                        confirmButtonText: 'Oke',
                    });
                }
            });
        }

        // Initialize DataTable for #saldo-awal-cutting-pcs-table
        $('#saldo-awal-cutting-pcs-table').DataTable({
            processing: true,
            serverSide: true,
            ordering: false,
            paging: true,
            searching: true,
            info: true,
            lengthChange: true,
            pageLength: 10,
            ajax: {
                url: '{{ route('get-saldo-awal-cutting-tmp') }}',
                type: 'GET'
            },
            columns: [
                { data: 'tgl_trans' },
                { data: 'id_so_det' },
                { data: 'buyer' },
                { data: 'ws' },
                { data: 'style' },
                { data: 'color' },
                { data: 'size' },
                { data: 'panel' },
                { data: 'saldo' }
            ],
            columnDefs: [
                {
                    targets: "_all",
                    className: "text-nowrap"
                }
            ]
        });

        function saldoAwalCuttingReload() {
            $('#saldo-awal-cutting-pcs-table').DataTable().ajax.reload();
        }

        function submitSaldoAwalCutting(e, evt) {
            if (evt && evt.preventDefault) {
                evt.preventDefault();
            }

            // Ask for confirmation before sending request
            Swal.fire({
                icon: 'warning',
                title: 'Konfirmasi',
                text: 'Yakin akan simpan, Saldo Awal Cutting?',
                showCancelButton: true,
                confirmButtonText: 'Ya, Simpan',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (!result.isConfirmed) return;

                document.getElementById("loading").classList.remove("d-none");

                $.ajax({
                    url: e.getAttribute('action'),
                    type: e.getAttribute('method'),
                    processData: false,
                    contentType: false,
                    success: async function(res) {
                        document.getElementById("loading").classList.add("d-none");

                        if (res.status == 200) {
                            console.log(res);

                            e.reset();

                            Swal.fire({
                                icon: 'success',
                                title: 'Data Saldo Cutting berhasil disimpan',
                                html: res.message,
                                showCancelButton: false,
                                showConfirmButton: true,
                                confirmButtonText: 'Oke'
                            }).then(() => {
                                $('#importSaldoAwalExcel').modal('hide');
                                saldoAwalCuttingReload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: res.message || 'Terjadi kesalahan',
                                showCancelButton: false,
                                showConfirmButton: true,
                                confirmButtonText: 'Oke',
                            });
                        }
                    },
                    error: function (jqXHR) {
                        document.getElementById("loading").classList.add("d-none");

                        console.error(jqXHR);

                        Swal.fire({
                            icon: 'error',
                            title: 'Terjadi Kesalahan',
                            text: 'Gagal menyimpan saldo awal. Silakan coba lagi.',
                            showCancelButton: false,
                            showConfirmButton: true,
                            confirmButtonText: 'Oke',
                        });
                    }
                });
            });
        }

        function emptySaldoAwal(type = 'tmp') {
            Swal.fire({
                title: 'Kosongkan Saldo Awal?',
                html: 'Yakin akan mengosongkan semua saldo awal '+(type == 'tmp' ? 'Temporary' : 'Aktual')+'?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'UBAH',
                cancelButtonText: 'BATAL',
                confirmButtonColor: "#dc3545"
            }).then((result) => {
                if (result.isConfirmed) {
                    emptySaldoAwalSubmit(type);
                }
            });
        }

        function emptySaldoAwalSubmit(type = 'tmp') {
            document.getElementById("loading").classList.remove("d-none");

            $.ajax({
                type: "post",
                url: '{{ route('empty-saldo-awal-cutting') }}',
                data: {
                    type: type
                },
                dataType: "json",
                success: function (response) {
                    document.getElementById("loading").classList.add("d-none");

                    Swal.fire({
                        icon: response.status == 200 ? "success" : "error" ,
                        title: response.status == 200 ? "Berhasil" : "Gagal" ,
                        html: response.message ? response.message : (response.status == 200 ? "Berhasil mengosongkan saldo awal" : "Terjadi kesalahan saat mengosongkan saldo awal")
                    }).then(() => {
                        saldoAwalCuttingReload();
                    });
                },
                error: function (jqXHR) {
                    document.getElementById("loading").classList.add("d-none");

                    Swal.fire({
                        icon: "error",
                        title: "Gagal",
                        html: "Terjadi Kesalahan"
                    }).then(() => {
                        saldoAwalCuttingReload();
                    });
                }
            });
        }
    </script>
@endsection

