@extends('layouts.index')

@section('custom-link')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">

    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

    <!-- apexchart CSS -->
    <link href="{{ asset('plugins/apexcharts/apexcharts.css') }}" rel="stylesheet">

    <style>
        div.sticky {
            position: -webkit-sticky; /* Safari */
            position: sticky;
            z-index:999;
            top: 0;
        }

        th.dtfc-fixed-left,
        th.dtfc-fixed-right {
            z-index: 1 !important;
        }
    </style>
@endsection

@section('content')
    @php
        $firstWs = $ws->first();
    @endphp
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="text-sb fw-bold">{{ $firstWs ? $firstWs->ws : '-' }}</h5>
        <a href="{{ route('track-ws') }}" class="btn btn-primary btn-sm"><i class="fa fa-reply"></i> Kembali ke WS Summary</a>
    </div>
    <input type="hidden" value="{{ $firstWs ? $firstWs->id_act_cost : '-' }}" id="id">
    <div class="row sticky" style="background: #f4f6f9;">
        <div class="col-md-3">
            <div class="mb-3">
                <label class="form-label form-label-sm">Worksheet</label>
                <input type="text" class="form-control" value="{{ $firstWs ? $firstWs->ws : '-' }}" id="ws" readonly>
            </div>
        </div>
        <div class="col-md-3">
            <div class="mb-3">
                <label class="form-label form-label-sm">Style</label>
                <input type="text" class="form-control" value="{{ $firstWs ? $firstWs->styleno : '-' }}" id="style" readonly>
            </div>
        </div>
        <div class="col-md-2">
            <div class="mb-3">
                <label class="form-label form-label-sm">Color</label>
                <select class="form-select select2bs4" id="ws-color-filter">
                    <option value="" selected>All Color</option>
                    @foreach ($ws->groupBy("color")->keys() as $color)
                        <option value="{{ $color }}">{{ $color }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-2">
            <div class="mb-3">
                <label class="form-label form-label-sm">Panel</label>
                <select class="form-select select2bs4" id="ws-panel-filter">
                    <option value="" selected>All Panel</option>
                    @foreach ($panels as $panel)
                        <option value="{{ $panel->panel }}">{{ $panel->panel }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-2">
            <div class="mb-3">
                <label class="form-label form-label-sm">Size</label>
                <select class="form-select select2bs4" id="ws-size-filter">
                    <option value="" selected>All Size</option>
                    @foreach ($ws->sortBy("id")->groupBy("size")->keys() as $size)
                        <option value="{{ $size }}">{{ $size }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- PART START --}}
        <div class="card card-sb h-100 collapsed collapsed-card" id="part-card">
            <div class="card-header">
                <div class="row justify-content-between align-items-center">
                    <div class="col-6">
                        <h5 class="card-title fw-bold">
                            <i class="fa fa-shirt fa-sm"></i> Part
                        </h5>
                    </div>
                    <div class="col-auto">
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <form method="post" id="store-secondary" name='form'>
                    <div class="row">
                        <input type="hidden" id="part_id_input">
                        <div class="col-2 col-md-2">
                            <div class="mb-4">
                                <label><small><b>Panel</b></small></label>
                                <select class="form-control select2bs4" id="part_panel_input" name="part_panel_input" style="width: 100%;">
                                    <option value="" selected>Pilih Panel</option>
                                    @foreach ($panels as $panel)
                                        <option value="{{ $panel->panel }}">{{ $panel->panel }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-2 col-md-2">
                            <div class="mb-4">
                                <label><small><b>Part</b></small></label>
                                <select class="form-control select2bs4" id="part_part_input" name="part_part_input" style="width: 100%;">
                                    <option selected="selected" value="">Pilih Part</option>
                                    @foreach ($masterPart as $part)
                                        <option value="{{ $part->id }}">
                                            {{ $part->nama_part . ' - ' . $part->bag }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-2 col-md-2">
                            <div class="mb-4">
                                <label><small><b>Cons</b></small></label>
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control" name="part_cons_input" id="part_cons_input">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" style="border-radius: 0 3px 3px 0;">METER</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-2 col-md-2">
                            <div class="mb-4">
                                <label><small><b>Tujuan</b></small></label>
                                <select class="form-control select2bs4" id="part_tujuan_input" name="part_tujuan_input" style="width: 100%;" onchange="getproses();">
                                    <option selected="selected" value="">Pilih Tujuan</option>
                                    @foreach ($masterTujuan as $tujuan)
                                        <option value="{{ $tujuan->isi }}">
                                            {{ $tujuan->tampil }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-4 col-md-4">
                            <div class="row align-items-end">
                                <div class="col-6">
                                    <div class="mb-4">
                                        <label><small><b>Proses</b></small></label>
                                        <select class="form-control select2bs4 w-100" id="part_proses_input" name="part_proses_input" style="width: 100%;">
                                        </select>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="mb-4">
                                        <label><small><b>&nbsp;</b></small></label>
                                        <button type="button" class="btn btn-block bg-primary" name="simpan" id="simpan" onclick="savePartData();"><i class="fa fa-save"></i></button>
                                        {{-- <input type="button" class="btn bg-primary w-100" name="simpan" id="simpan" value="Simpan" onclick="simpan_data();"> --}}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                <div class="table-responsive">
                    <table id="part-table" class="table table-bordered table-sm w-100">
                        <thead>
                            <tr>
                                <th>Action</th>
                                <th>Panel</th>
                                <th>Part</th>
                                <th>Cons.</th>
                                <th>Satuan</th>
                                <th>Tujuan</th>
                                <th>Proses</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    {{-- PART END --}}

    {{-- MARKER START --}}
        <div class="card card-sb" id="marker-card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title fw-bold mb-0"><i class="fas fa-marker fa-sm"></i> Marker</h5>
                    <div class="d-flex justify-content-end align-items-center gap-1">
                        <input type="date" class="form-control form-control-sm" id="marker-from">
                        <span>-</span>
                        <input type="date" class="form-control form-control-sm" id="marker-to">
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body" style="display: block;">
                <div class="row mb-3" id="marker-summary">
                    <div class="col-md-3">
                        <div class="info-box h-100">
                            <span class="info-box-icon bg-sb"><i class="fa-solid fa-marker"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text text-sb fw-bold">Marker</span>
                                <span class="info-box-number" id="total-marker"></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box h-100">
                            <span class="info-box-icon bg-sb-secondary"><i class="fa-solid fa-ruler-combined"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text text-sb-secondary fw-bold">Panjang Lebar</span>
                                <span class="info-box-number" id="total-marker-pl"></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box h-100">
                            <span class="info-box-icon bg-sb"><i class="fa-solid fa-scroll"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text text-sb fw-bold">Gelar</span>
                                <span class="info-box-number" id="total-marker-gelar"></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box h-100">
                            <span class="info-box-icon bg-sb-secondary"><i class="fa-solid fa-copy"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text text-sb-secondary fw-bold">Form</span>
                                <span class="info-box-number" id="total-marker-form"></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="accordion" id="marker-detail">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed bg-sb-secondary text-light" type="button" data-bs-toggle="collapse" data-bs-target="#marker-detail-1" aria-expanded="true" aria-controls="marker-detail-1">
                                <b>Detail</b>
                            </button>
                        </h2>
                        <div id="marker-detail-1" class="accordion-collapse collapse" data-bs-parent="#marker-detail">
                            <div class="accordion-body">
                                <div class="table-responsive">
                                    <table id="marker-table" class="table table-bordered table-sm w-100">
                                        <thead>
                                            <tr>
                                                <th class="align-bottom">Action</th>
                                                <th>Tanggal</th>
                                                <th>No. Marker</th>
                                                <th>Color</th>
                                                <th>Panel</th>
                                                <th>Urutan</th>
                                                <th>Panjang</th>
                                                <th>Lebar</th>
                                                <th>Gramasi</th>
                                                <th>Gelar QTYs</th>
                                                <th>Total Form</th>
                                                <th>Ratio</th>
                                                <th>PO</th>
                                                <th>Ket.</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                        <tfoot>
                                            <tr>
                                                <th class="text-end" colspan="5">Total</th>
                                                <th id="total-marker"></th>
                                                <th id="total-panjang-marker"></th>
                                                <th id="total-lebar-marker"></th>
                                                <th id="total-gramasi-marker"></th>
                                                <th id="total-gelar-marker"></th>
                                                <th id="total-form-marker"></th>
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{-- Show Detail Marker Modal --}}
        <div class="modal fade" id="showMarkerModal" tabindex="-1" role="dialog" aria-labelledby="showMarkerModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header bg-sb text-light">
                        <h1 class="modal-title fs-5" id="showMarkerModalLabel"></h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" id="detail">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
        {{-- Edit Marker Modal --}}
        <div class="modal fade" id="editMarkerModal" tabindex="-1" role="dialog" aria-labelledby="editMarkerModalLabel" aria-hidden="true">
            <form action="{{ route('update_marker') }}" method="post" onsubmit="submitForm(this, event)">
                @method('PUT')
                <div class="modal-dialog modal-xl modal-dialog-scrollable" style="max-width: 35%;">
                    <div class="modal-content">
                        <div class="modal-header bg-sb text-light">
                            <h1 class="modal-title fs-5" id="editMarkerModalLabel"></h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class='row'>
                                <div class='col-sm-12'>
                                    <div class='form-group'>
                                        <label class='form-label'><small class="fw-bold">Gramasi</small></label>
                                        <input type='number' class='form-control' id='txt_gramasi' name='txt_gramasi' value = ''>
                                        <input type='hidden' class='form-control' id='id_c' name='id_c' value = ''>
                                    </div>
                                </div>
                                <div class='col-sm-12' id="marker_pilot">
                                    <div class='form-group'>
                                        <label class='form-label'><small class="fw-bold">Status Pilot</small></label>
                                        <div class="d-flex gap-3 ms-1">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="pilot_status" id="idle" value="idle">
                                                <label class="form-check-label" for="idle">
                                                    <small class="fw-bold"><i class="fa fa-minus fa-sm"></i> Pilot Idle</small>
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="pilot_status" id="active" value="active">
                                                <label class="form-check-label text-success" for="active">
                                                    <small class="fw-bold"><i class="fa fa-check fa-sm"></i> Pilot
                                                        Approve</small>
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="pilot_status" id="not_active" value="not active">
                                                <label class="form-check-label text-danger" for="not_active">
                                                    <small class="fw-bold"><i class="fa fa-times fa-sm"></i> Pilot
                                                        Disapprove</small>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-12 d-none" id="advanced-edit-section">
                                    <a href="" class="btn btn-primary btn-sm btn-block" id="advanced-edit-link"><i class="fas fa-edit"></i> Advanced Edit</a>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Tutup</button>
                            <button type="submit" class="btn btn-success">Simpan</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    {{-- MARKER END --}}

    {{-- FORM START --}}
        <div class="card card-sb" id="form-card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title fw-bold mb-0"><i class="fas fa-file fa-sm"></i> Form</h5>
                    <div class="d-flex justify-content-end align-items-center gap-1">
                        <input type="date" class="form-control form-control-sm" id="form-from">
                        <span>-</span>
                        <input type="date" class="form-control form-control-sm" id="form-to">
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body" style="display: block;">
                <div class="row mb-3" id="form-summary">
                    <div class="col-md-4">
                        <div class="info-box h-100">
                            <span class="info-box-icon bg-sb"><i class="fa-solid fa-copy"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text text-sb fw-bold">Form</span>
                                <span class="info-box-number" id="total-form"></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="info-box h-100">
                            <span class="info-box-icon bg-sb-secondary"><i class="fa-solid fa-scroll"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text text-sb-secondary fw-bold">Qty Ply</span>
                                <span class="info-box-number" id="total-form-ply"></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="accordion" id="form-detail">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed bg-sb-secondary text-light" type="button" data-bs-toggle="collapse" data-bs-target="#form-detail-1" aria-expanded="true" aria-controls="form-detail-1">
                                <b>Detail</b>
                            </button>
                        </h2>
                        <div id="form-detail-1" class="accordion-collapse collapse" data-bs-parent="#form-detail">
                            <div class="accordion-body">
                                <div class="table-responsive">
                                    <table id="form-table" class="table table-bordered table-sm w-100">
                                        <thead>
                                            <tr>
                                                <th>Action</th>
                                                <th>Tanggal Form</th>
                                                <th>No. Form</th>
                                                <th>No. Meja</th>
                                                <th>No. Marker</th>
                                                <th>Color</th>
                                                <th>Panel</th>
                                                <th>Qty Ply</th>
                                                <th>Size Ratio</th>
                                                <th>Ket.</th>
                                                <th class="align-bottom" style="text-align: left !important;">Status</th>
                                                <th>Plan</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th colspan="6" class="text-end">Total</th>
                                                <th id="total-form"></th>
                                                <th id="total-form-qty"></th>
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Edit Meja Modal -->
        <div class="modal fade" id="editMejaModal" tabindex="-1" aria-labelledby="editMejaModalLabel" aria-hidden="true">
            <form action="{{ route('update-spreading') }}" method="post" onsubmit="submitForm(this, event)">
                @method('PUT')
                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header bg-sb text-light">
                            <h1 class="modal-title fs-5" id="editMejaModalLabel"><i class="fa fa-edit fa-sm"></i> Edit Meja</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body" style="max-height: 65vh !important;">
                            <div class="row">
                                <input type="hidden" id="edit_id" name="edit_id">
                                <input type="hidden" id="edit_marker_id" name="edit_marker_id">
                                <div class="col-6 col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label"><small>Tanggal Form</small></label>
                                        <input type="text" class="form-control" id="edit_tgl_form_cut" name="edit_tgl_form_cut" value="" readonly />
                                    </div>
                                </div>
                                <div class="col-6 col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label"><small>No. Form</small></label>
                                        <input type="text" class="form-control" id="edit_no_form" name="edit_no_form" value="" readonly />
                                    </div>
                                </div>
                                <div class="col-6 col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label"><small>Marker</small></label>
                                        <input type="text" class="form-control" id="edit_id_marker" name="edit_id_marker" value="" readonly />
                                    </div>
                                </div>
                                <div class="col-6 col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label"><small>WS</small></label>
                                        <input type="text" class="form-control" id="edit_ws" name="edit_ws" value="" readonly />
                                    </div>
                                </div>
                                <div class="col-6 col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label"><small>Color</small></label>
                                        <input type="text" class="form-control" id="edit_color" name="edit_color" value="" readonly />
                                    </div>
                                </div>
                                <div class="col-6 col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label"><small>Panel</small></label>
                                        <input type="text" class="form-control" id="edit_panel" name="edit_panel" value="" readonly />
                                    </div>
                                </div>
                                <div class="col-4 col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label"><small>P. Marker</small></label>
                                        <input type="text" class="form-control" id="edit_panjang_marker" name="edit_panjang_marker" value="" readonly />
                                    </div>
                                </div>
                                <div class="col-4 col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label"><small>Unit P. Marker</small></label>
                                        <input type="text" class="form-control" id="edit_unit_panjang_marker" name="edit_unit_panjang_marker" value="" readonly />
                                    </div>
                                </div>
                                <div class="col-4 col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label"><small>Comma Marker</small></label>
                                        <input type="text" class="form-control" id="edit_comma_marker" name="edit_comma_marker" value="" readonly />
                                    </div>
                                </div>
                                <div class="col-4 col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label"><small>Unit Comma Marker</small></label>
                                        <input type="text" class="form-control" id="edit_unit_comma_marker" name="edit_unit_comma_marker" value="" readonly />
                                    </div>
                                </div>
                                <div class="col-4 col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label"><small>Lebar Marker</small></label>
                                        <input type="text" class="form-control" id="edit_lebar_marker" name="edit_lebar_marker" value="" readonly />
                                    </div>
                                </div>
                                <div class="col-4 col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label"><small>Unit Lebar Marker</small></label>
                                        <input type="text" class="form-control" id="edit_unit_lebar_marker" name="edit_unit_lebar_marker" value="" readonly />
                                    </div>
                                </div>
                                <div class="col-4 col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label"><small>PO Marker</small></label>
                                        <input type="text" class="form-control" id="edit_po_marker" name="edit_po_marker" value="" readonly />
                                    </div>
                                </div>
                                <div class="col-4 col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label"><small>Gelar QTY</small></label>
                                        <input type="text" class="form-control" id="edit_gelar_qty" name="edit_gelar_qty" value="" readonly />
                                    </div>
                                </div>
                                <div class="col-4 col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label"><small>Ply QTY</small></label>
                                        <input type="text" class="form-control" id="edit_qty_ply" name="edit_qty_ply" value="" readonly />
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label"><small>Urutan Marker</small></label>
                                        <input type="text" class="form-control" id="edit_urutan_marker" name="edit_urutan_marker" value="" readonly />
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label"><small>Cons. Marker</small></label>
                                        <input type="text" class="form-control" id="edit_cons_marker" name="edit_cons_marker" value="" readonly />
                                    </div>
                                </div>
                                <div class="col-4 col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label"><small>Tipe Marker</small></label>
                                        <input type="text" class="form-control" id="edit_tipe_marker" name="edit_tipe_marker" value="" readonly />
                                    </div>
                                </div>
                                <div class="col-4 col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label"><small>No. Meja</small></label>
                                        <select class="form-select form-select-sm select2bs4" aria-label="Default select example" id="edit_no_meja" name="edit_no_meja">
                                            <option value="">-</option>
                                            @foreach ($meja as $m)
                                                <option value="{{ $m->id }}">{{ strtoupper($m->name) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-4 col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label"><small>Keterangan</small></label>
                                        <textarea class="form-control" id="edit_notes" name="edit_notes" rows="1" readonly></textarea>
                                    </div>
                                </div>
                                <div class="col-md-12 table-responsive">
                                    <table id="form-ratio-table" class="table table-bordered table-striped table-sm w-100">
                                        <thead>
                                            <tr>
                                                <th>Size</th>
                                                <th>Ratio</th>
                                                <th>Cut Qty</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Tutup</button>
                            <button type="submit" class="btn btn-success">Simpan</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Edit Status Modal -->
        <div class="modal fade" id="editStatusModal" tabindex="-1" aria-labelledby="editStatusModalLabel" aria-hidden="true">
            <form action="{{ route('update-status') }}" method="post" onsubmit="submitForm(this, event)">
                @method('PUT')
                <div class="modal-dialog modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header bg-sb text-light">
                            <h1 class="modal-title fs-5" id="editStatusModalLabel"><i class="fa fa-cog fa-sm"></i> Edit Status</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body" style="max-height: 65vh !important;">
                            <div class="row">
                                <input type="hidden" id="edit_id_status" name="edit_id_status">
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label class="form-label">Status</label>
                                        <select class="form-select select2bs4stat" name="edit_status" id="edit_status">
                                            <option value="SPREADING">SPREADING</option>
                                            <option value="PENGERJAAN FORM CUTTING">PENGERJAAN FORM CUTTING</option>
                                            <option value="PENGERJAAN FORM CUTTING DETAIL">PENGERJAAN FORM CUTTING DETAIL</option>
                                            <option value="PENGERJAAN FORM CUTTING SPREAD">PENGERJAAN FORM CUTTING SPREAD</option>
                                            <option value="SELESAI PENGERJAAN">SELESAI PENGERJAAN</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Tutup</button>
                            <button type="submit" class="btn btn-success">Simpan</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    {{-- FORM END --}}

    {{-- ROLL START --}}
        <div class="card card-sb" id="roll-card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title fw-bold mb-0"><i class="fa-solid fa-toilet-paper fa-sm"></i> Roll</h5>
                    <div class="d-flex justify-content-end align-items-center gap-1">
                        <input type="date" class="form-control form-control-sm" id="roll-from">
                        <span>-</span>
                        <input type="date" class="form-control form-control-sm" id="roll-to">
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-3" id="roll-summary">
                    <div class="col-md-4">
                        <div class="info-box h-100">
                            <span class="info-box-icon bg-sb"><i class="fa-solid fa-toilet-paper"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text text-sb fw-bold">Roll</span>
                                <span class="info-box-number" id="total-roll-qty"></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-box h-100">
                            <span class="info-box-icon bg-sb-secondary"><i class="fa-solid fa-scroll"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text text-sb-secondary fw-bold">Lembar</span>
                                <span class="info-box-number" id="total-roll-lembar"></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-box h-100">
                            <span class="info-box-icon bg-sb"><i class="fa-solid fa-scissors"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text text-sb fw-bold">Total Pemakaian</span>
                                <span class="info-box-number" id="total-roll-pemakaian"></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="accordion" id="roll-detail">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed bg-sb-secondary text-light" type="button" data-bs-toggle="collapse" data-bs-target="#roll-detail-1" aria-expanded="true" aria-controls="roll-detail-1">
                                <b>Detail</b>
                            </button>
                        </h2>
                        <div id="roll-detail-1" class="accordion-collapse collapse" data-bs-parent="#roll-detail">
                            <div class="accordion-body">
                                <div class="table-responsive">
                                    <table id="roll-table" class="table table-bordered table-hover table-sm w-100">
                                        <thead>
                                            <tr>
                                                <th>Color</th>
                                                <th>Panel</th>
                                                <th>No. Form</th>
                                                <th>ID Item</th>
                                                <th>Nama Barang</th>
                                                <th>No. Meja</th>
                                                <th>Qty</th>
                                                <th>Unit</th>
                                                <th>Lembar Gelaran</th>
                                                <th>Total Pemakaian</th>
                                                <th>Short Roll</th>
                                                <th>Remark</th>
                                                <th>Sisa Gelaran</th>
                                                <th>Sambungan</th>
                                                <th>Kepala Kain</th>
                                                <th>Sisa Tidak Bisa</th>
                                                <th>Reject</th>
                                                <th>Sisa Kain</th>
                                                <th>Piping</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th colspan="6" class="text-end">Total</th>
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    {{-- ROLL END --}}

    {{-- STOCKER START --}}
        <div class="card card-sb" id="stocker-card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title fw-bold mb-0"><i class="fa-solid fa-ticket fa-sm"></i> Stocker</h5>
                    <div class="d-flex justify-content-end align-items-center gap-1">
                        <input type="date" class="form-control form-control-sm" id="stocker-from">
                        <span>-</span>
                        <input type="date" class="form-control form-control-sm" id="stocker-to">
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-3" id="stocker-summary">
                    <div class="col-md-3">
                        <div class="info-box h-100">
                            <span class="info-box-icon bg-sb"><i class="fa-solid fa-receipt"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text text-sb fw-bold">Stocker</span>
                                <span class="info-box-number" id="total-stocker"></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box h-100">
                            <span class="info-box-icon bg-sb-secondary"><i class="fa-solid fa-table-cells"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text text-sb-secondary fw-bold">Part Stock Qty</span>
                                <span class="info-box-number" id="total-stocker-qty"></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box h-100">
                            <span class="info-box-icon bg-sb"><i class="fa-solid fa-list-ol"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text text-sb fw-bold">Max Range</span>
                                <span class="info-box-number" id="total-stocker-range"></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box h-100">
                            <span class="info-box-icon bg-sb-secondary"><i class="fa-solid fa-plus-minus"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text text-sb-secondary fw-bold">Modified Qty</span>
                                <span class="info-box-number" id="total-stocker-difference"></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="accordion" id="stocker-detail">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed bg-sb-secondary text-light" type="button" data-bs-toggle="collapse" data-bs-target="#stocker-detail-1" aria-expanded="true" aria-controls="stocker-detail-1">
                                <b>Detail</b>
                            </button>
                        </h2>
                        <div id="stocker-detail-1" class="accordion-collapse collapse" data-bs-parent="#stocker-detail">
                            <div class="accordion-body">
                                <div class="table-responsive">
                                    <table id="stocker-table" class="table table-bordered table-sm w-100">
                                        <thead>
                                            <tr>
                                                <th>Color</th>
                                                <th>Panel</th>
                                                <th>Part</th>
                                                <th>No. Form</th>
                                                <th>No. Cut</th>
                                                <th>Size</th>
                                                <th>Group</th>
                                                <th>No. Stocker</th>
                                                <th>Qty</th>
                                                <th>Range</th>
                                                <th>Difference</th>
                                                <th>Secondary</th>
                                                <th>Rak</th>
                                                <th>Trolley</th>
                                                <th>Line</th>
                                                <th>Updated At</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th class="text-end" colspan="7">Total</th>
                                                <th id="total-stocker"></th>
                                                <th id="total-stocker-qty"></th>
                                                <th id="total-stocker-range"></th>
                                                <th id="total-stocker-difference"></th>
                                                <th colspan="5"></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    {{-- STOCKER END --}}

    {{-- OUTPUT LINE START --}}
        <div class="card card-sb" id="output-line-card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title fw-bold mb-0"><i class="fa-solid fa-users-line"></i> Output Line</h5>
                    <div class="d-flex justify-content-end align-items-center gap-1">
                        <input type="date" class="form-control form-control-sm" id="output-line-from">
                        <span>-</span>
                        <input type="date" class="form-control form-control-sm" id="output-line-to">
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="d-none mb-3" id="loading-output-line">
                    <div class="loading-container">
                        <div class="loading"></div>
                    </div>
                </div>
                <div class="row mb-3" id="output-line-summary">
                    <div class="col-md-3">
                        <div class="info-box h-100">
                            <span class="info-box-icon bg-success"><i class="fa-solid fa-check"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text text-success fw-bold">RFT</span>
                                <span class="info-box-number" id="output-rft"></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box h-100">
                            <span class="info-box-icon bg-primary"><i class="fa-solid fa-triangle-exclamation"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text text-primary fw-bold">DEFECT</span>
                                <span class="info-box-number" id="output-defect"></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box h-100">
                            <span class="info-box-icon bg-danger"><i class="fa-solid fa-xmark"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text text-danger fw-bold">REJECT</span>
                                <span class="info-box-number" id="output-reject"></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box h-100">
                            <span class="info-box-icon bg-sb"><i class="fa-solid fa-box"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text text-sb fw-bold">PACKING</span>
                                <span class="info-box-number" id="output-packing"></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="output-line-chart" style="height: 350px;"></div>
                <a href="{{ route('sewing-track-order-output') }}" target="_blank" class="btn btn-sb w-100"><i class="fa fa-search"></i> Track Detail Output</a>
            </div>
        </div>
    {{-- OUTPUT LINE END --}}
@endsection

@section('custom-script')
    <!-- apexchart JavaScript -->
    <script src="{{ asset('plugins/apexcharts/apexcharts.min.js') }}"></script>

    <!-- DataTables & Plugins -->
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
        })
    </script>

    <script>
        document.getElementById("loading").classList.remove("d-none");

        $(document).ready(async function() {
            $('#part-card').on('expanded.lte.cardwidget', () => {
                partTableReload();
            });

            $('#marker-card').on('expanded.lte.cardwidget', () => {
                filterMarkerTable();
            });

            $('#marker-detail-1').on('shown.bs.collapse', () => {
                filterMarkerTable();
            });

            $('#form-card').on('expanded.lte.cardwidget', () => {
                formTableReload();
            });

            $('#form-detail-1').on('shown.bs.collapse', () => {
                formTableReload();
            });

            $('#roll-card').on('expanded.lte.cardwidget', () => {
                rollTableReload();
            });

            $('#roll-detail-1').on('shown.bs.collapse', () => {
                rollTableReload();
            });

            $('#stocker-card').on('expanded.lte.cardwidget', () => {
                stockerTableReload();
            });

            $('#stocker-detail-1').on("shown.bs.collapse", () => {
                stockerTableReload();
            });

            // $('#output-line-card').on('expanded.lte.cardwidget', () => {
            //     getLineOutput();
            // });

            let today = new Date();
            let todayDate = ("0" + today.getDate()).slice(-2);
            let todayMonth = ("0" + (today.getMonth() + 1)).slice(-2);
            let todayYear = today.getFullYear();
            let todayFullDate = todayYear + '-' + todayMonth + '-' + todayDate;

            $('#ws-color-filter').on("change", () => {
                filterMarkerTable();
                formTableReload();
                rollTableReload();
                stockerTableReload();
                getLineOutput();
            });

            $('#ws-panel-filter').on("change", () => {
                filterMarkerTable();
                formTableReload();
                rollTableReload();
                stockerTableReload();
                getLineOutput();
            });

            $('#ws-size-filter').on("change", () => {
                filterMarkerTable();
                formTableReload();
                rollTableReload();
                stockerTableReload();
                getLineOutput();
            });

            // Marker
            filterMarkerTable();
            $('#marker-from').on("change", () => {
                filterMarkerTable();
            });
            $('#marker-to').on("change", () => {
                filterMarkerTable();
            });

            // Part
            partTableReload();
            $("#part_panel_input").on("change", () => {
                partTableReload();
                getPartId();
            });

            // Form
            formTableReload();
            $('#form-from').on("change", () => {
                formTableReload();
            });
            $('#form-to').on("change", () => {
                formTableReload();
            });

            // Roll
            rollTableReload();
            $('#form-from').on("change", () => {
                rollTableReload();
            });
            $('#form-to').on("change", () => {
                rollTableReload();
            });

            // Stocker
            stockerTableReload();
            $('#stocker-from').on("change", () => {
                stockerTableReload();
            });
            $('#stocker-to').on("change", () => {
                stockerTableReload();
            });

            // Sewing
            getLineOutput();
            $('#output-line-from').on("change", () => {
                getLineOutput();
            });
            $('#output-line-to').on("change", () => {
                getLineOutput();
            });

            document.getElementById("loading").classList.add("d-none");
        });

        // Marker :
            var markerTableParameter = ['mrk_action', 'mrk_tanggal', 'mrk_no_marker', 'mrk_color', 'mrk_panel', 'mrk_urutan', 'mrk_panjang', 'mrk_lebar', 'mrk_gramasi', 'mrk_qty_ply', 'mrk_total_form', 'mrk_po', 'mrk_ket'];

            $('#marker-table thead tr').clone(true).appendTo('#marker-table thead');
            $('#marker-table thead tr:eq(1) th').each(function(i) {
                if (i != 0) {
                    var title = $(this).text();
                    $(this).html('<input type="text" class="form-control form-control-sm" id="'+markerTableParameter[i]+'" style="width:100%"/>');

                    $('input', this).on('keyup change', function() {
                        if (markerTable.column(i).search() !== this.value) {
                            markerTable
                                .column(i)
                                .search(this.value)
                                .draw();
                        }
                    });
                } else {
                    $(this).empty();
                }
            });

            let markerTable = $("#marker-table").DataTable({
                ordering: false,
                processing: true,
                serverSide: true,
                dom: 'rtip',
                pageLength: 50,
                scrollX: '500px',
                scrollY: '350px',
                ajax: {
                    url: '{{ route('track-ws-marker') }}',
                    data: function(d) {
                        d.actCostingId = $('#id').val();
                        d.dateFrom = $('#marker-from').val();
                        d.dateTo = $('#marker-to').val();
                        d.color = $('#ws-color-filter').val();
                        d.panel = $('#ws-panel-filter').val();
                        d.size = $('#ws-size-filter').val();
                    },
                },
                columns: [
                    {
                        data: 'id'
                    },
                    {
                        data: 'tgl_cut_fix',
                        searchable: false
                    },
                    {
                        data: 'kode'
                    },
                    {
                        data: 'color'
                    },
                    {
                        data: 'panel'
                    },
                    {
                        data: 'urutan_marker'
                    },
                    {
                        data: 'panjang_marker_fix',
                        searchable: false
                    },
                    {
                        data: 'lebar_marker',
                    },
                    {
                        data: 'gramasi'
                    },
                    {
                        data: undefined
                    },
                    {
                        data: 'total_form',
                        searchable: false
                    },
                    {
                        data: 'marker_details',
                        searchable: false
                    },
                    {
                        data: 'po_marker'
                    },
                    {
                        data: 'notes',
                    },
                ],
                columnDefs: [
                    {
                        targets: [0],
                        render: (data, type, row, meta) => {
                            let exportBtn = `
                                <button type="button" class="btn btn-sm btn-success" onclick="printMarker('` + row.kode + `');">
                                    <i class="fa fa-print"></i>
                                </button>
                            `;

                            if (row.cancel != 'Y' && row.tot_form != 0 && row.tipe_marker != "pilot marker") {
                                return `
                                    <div class='d-flex gap-1 justify-content-start mb-1'>
                                        <a class='btn btn-primary btn-sm' data-bs-toggle="modal" data-bs-target="#showMarkerModal" onclick='getdetail(` + row.id + `);'>
                                            <i class='fa fa-search'></i>
                                        </a>
                                        <button class='btn btn-info btn-sm' data-bs-toggle="modal" data-bs-target="#editMarkerModal" onclick='edit(` + row.id + `);'>
                                            <i class='fa fa-edit'></i>
                                        </button>
                                        ` + exportBtn + `
                                        <button class='btn btn-danger btn-sm' onclick='cancel(` + row.id + `);' disabled>
                                            <i class='fa fa-ban'></i>
                                        </button>
                                    </div>
                                `;
                            } else if ((row.cancel != 'Y' && row.tot_form == 0) || (row.cancel != 'Y' && row.gelar_qty_balance > 0 && row.tipe_marker == "pilot marker")) {
                                return `
                                    <div class='d-flex gap-1 justify-content-start mb-1'>
                                        <a class='btn btn-primary btn-sm' data-bs-toggle="modal" data-bs-target="#showMarkerModal" onclick='getdetail(` + row.id + `);'>
                                            <i class='fa fa-search'></i>
                                        </a>
                                        <button class='btn btn-info btn-sm' data-bs-toggle="modal" data-bs-target="#editMarkerModal" onclick='edit(` + row.id + `);'>
                                            <i class='fa fa-edit'></i>
                                        </button>
                                        ` + exportBtn + `
                                        <button class='btn btn-danger btn-sm' onclick='cancel(` + row.id + `);'>
                                            <i class='fa fa-ban'></i>
                                        </button>
                                    </div>
                                `;
                            } else if (row.cancel == 'Y') {
                                return `
                                    <div class='d-flex gap-1 justify-content-start'>
                                        <a class='btn btn-primary btn-sm' data-bs-toggle="modal" data-bs-target="#showMarkerModal" onclick='getdetail(` + row.id + `);'>
                                            <i class='fa fa-search'></i>
                                        </a>
                                        <button class='btn btn-info btn-sm' data-bs-toggle="modal" data-bs-target="#editMarkerModal" onclick='edit(` + row.id + `);' disabled>
                                            <i class='fa fa-edit'></i>
                                        </button>
                                        ` + exportBtn + `
                                        <button class='btn btn-danger btn-sm' onclick='cancel(` + row.id + `);' disabled>
                                            <i class='fa fa-ban'></i>
                                        </button>
                                    </div>
                                `;
                            } else {
                                return `
                                    <div class='d-flex gap-1 justify-content-start'>
                                        <a class='btn btn-primary btn-sm' data-bs-toggle="modal" data-bs-target="#showMarkerModal" onclick='getdetail(` + row.id + `);'>
                                            <i class='fa fa-search'></i>
                                        </a>
                                        <button class='btn btn-info btn-sm' data-bs-toggle="modal" data-bs-target="#editMarkerModal" onclick='edit(` + row.id + `);' disabled>
                                            <i class='fa fa-edit'></i>
                                        </button>
                                        ` + exportBtn + `
                                        <button class='btn btn-danger btn-sm' onclick='cancel(` + row.id + `);' disabled>
                                            <i class='fa fa-ban'></i>
                                        </button>
                                    </div>
                                `;
                            }
                        }
                    },
                    {
                        targets: [9],
                        render: (data, type, row, meta) => {
                            return `
                                <div class="progress border border-sb position-relative" style="height: 21px; width: 150px;">
                                    <p class="position-absolute" style="top: 50%;left: 50%;transform: translate(-50%, -50%);">` + (row.total_lembar).toLocaleString("ID-id") + `/` + (row.gelar_qty).toLocaleString("ID-id") + `</p>
                                    <div class="progress-bar" style="background-color: #75baeb;width: ` + ((row.total_lembar / row.gelar_qty) * 100) + `%" role="progressbar"></div>
                                </div>
                            `;
                        }
                    },
                    {
                        targets: '_all',
                        className: 'text-nowrap',
                        render: (data, type, row, meta) => {
                            var color = '#2b2f3a';
                            if (row.tot_form != '0' && row.cancel == 'N') {
                                color = '#087521';
                            } else if (row.tot_form == '0' && row.cancel == 'N') {
                                color = '#2243d6';
                            } else if (row.cancel == 'Y') {
                                color = '#d33141';
                            }
                            return '<span style="font-weight: 600; color:' + color + '">' + data + '</span>';
                        }
                    },
                ],
                rowCallback: function( row, data, index ) {
                    if (data['tipe_marker'] == 'special marker') {
                        $('td', row).css('background-color', '#e7dcf7');
                        $('td', row).css('border', '0.15px solid #d0d0d0');
                    } else if (data['tipe_marker'] == 'pilot marker' || data['tipe_marker'] == 'bulk marker') {
                        $('td', row).css('background-color', '#c5e0fa');
                        $('td', row).css('border', '0.15px solid #d0d0d0');
                    }
                },
                footerCallback: async function (row, data, start, end, display) {
                    let api = this.api();

                    api.column(5).footer().innerHTML = "...";
                    api.column(6).footer().innerHTML = "...";
                    api.column(7).footer().innerHTML = "...";
                    api.column(8).footer().innerHTML = "...";
                    api.column(9).footer().innerHTML = "...";
                    api.column(10).footer().innerHTML = "...";

                    let totalMarker = await getTotalMarker();

                    if (await totalMarker) {
                        api.column(5).footer().innerHTML = totalMarker.totalMarker;
                        api.column(6).footer().innerHTML = totalMarker.totalMarkerPanjang;
                        api.column(7).footer().innerHTML = totalMarker.totalMarkerLebar;
                        api.column(8).footer().innerHTML = totalMarker.totalMarkerGramasi;
                        api.column(9).footer().innerHTML = `
                            <div class="progress border border-sb position-relative" style="height: 21px; width: 150px;">
                                <p class="position-absolute" style="top: 50%;left: 50%;transform: translate(-50%, -50%);">` + (totalMarker.totalMarkerFormLembar).toLocaleString("ID-id") + `/` + (totalMarker.totalMarkerGelar).toLocaleString("ID-id") + `</p>
                                <div class="progress-bar" style="background-color: #75baeb;width: ` + ((totalMarker.totalMarkerFormLembar / totalMarker.totalMarkerGelar) * 100) + `%" role="progressbar"></div>
                            </div>
                        `;
                        api.column(10).footer().innerHTML = totalMarker.totalMarkerForm;

                        document.getElementById("total-marker").innerHTML = totalMarker.totalMarker;
                        document.getElementById("total-marker-pl").innerHTML = totalMarker.totalMarkerPanjang+" <br> "+totalMarker.totalMarkerLebar;
                        document.getElementById("total-marker-gelar").innerHTML = `
                            <div class="progress border border-sb position-relative" style="height: 21px; width: 100%;">
                                <p class="position-absolute" style="top: 50%;left: 50%;transform: translate(-50%, -50%);">` + (totalMarker.totalMarkerFormLembar).toLocaleString("ID-id") + `/` + (totalMarker.totalMarkerGelar).toLocaleString("ID-id") + `</p>
                                <div class="progress-bar" style="background-color: #75baeb;width: ` + ((totalMarker.totalMarkerFormLembar / totalMarker.totalMarkerGelar) * 100) + `%" role="progressbar"></div>
                            </div>
                        `;
                        document.getElementById("total-marker-form").innerHTML = totalMarker.totalMarkerForm;
                    }
                }
            });

            function filterMarkerTable() {
                markerTable.ajax.reload();
            }

            async function getTotalMarker() {
                return $.ajax({
                    url: '{{ route('track-ws-marker-total') }}',
                    type: 'GET',
                    dataType: 'json',
                    data: {
                        actCostingId : $('#id').val(),
                        dateFrom : $('#marker-from').val(),
                        dateTo : $('#marker-to').val(),
                        color : $('#ws-color-filter').val(),
                        panel : $('#ws-panel-filter').val(),
                        size : $('#ws-size-filter').val(),
                        no_marker : $('#mrk_no_marker').val(),
                        mrk_color : $('#mrk_color').val(),
                        mrk_panel : $('#mrk_panel').val(),
                        urutan : $('#mrk_urutan').val(),
                        panjang : $('#mrk_panjang').val(),
                        lebar : $('#mrk_lebar').val(),
                        gramasi : $('#mrk_gramasi').val(),
                        qty_ply : $('#mrk_qty_ply').val(),
                        total_form : $('#mrk_total_form').val(),
                        po : $('#mrk_po').val(),
                        ket : $('#mrk_ket').val()
                    },
                });
            }

            function getdetail(id_c) {
                $("#showMarkerModalLabel").html('<i class="fa-solid fa-magnifying-glass fa-sm"></i> Marker Detail');
                let html = $.ajax({
                    type: "POST",
                    url: '{{ route('show-marker') }}',
                    data: {
                        id_c: id_c
                    },
                    async: false
                }).responseText;
                $("#detail").html(html);

                $("#detail-marker-ratio").DataTable({
                    ordering: false,
                    paging: false
                });

                $("#detail-marker-form").DataTable({
                    ordering: false
                });
            };

            function edit(id_c) {
                $("#editMarkerModalLabel").html('<i class="fa fa-edit fa-sm"></i> Marker Edit');
                $.ajax({
                    url: '{{ route('show_gramasi') }}',
                    method: 'POST',
                    data: {
                        id_c: id_c
                    },
                    dataType: 'json',
                    success: function(response) {
                        document.getElementById('txt_gramasi').value = response.gramasi;
                        document.getElementById('id_c').value = response.id;

                        if (response.tipe_marker == "pilot marker") {
                            document.getElementById('marker_pilot').classList.remove('d-none');

                            if (response.status_marker) {
                                document.getElementById(response.status_marker).checked = true;
                            } else {
                                document.getElementById("idle").checked = true;
                            }
                        } else {
                            document.getElementById('marker_pilot').classList.add('d-none');
                        }

                        if (response.jumlah_form > 0) {
                            document.getElementById('txt_gramasi').setAttribute('readonly', true);
                        } else {
                            document.getElementById('txt_gramasi').removeAttribute('readonly');
                        }

                        document.getElementById('advanced-edit-link').setAttribute('href','{{ route('edit-marker') }}/' + response.id);
                        document.getElementById('advanced-edit-section').classList.remove('d-none');
                    },
                    error: function(request, status, error) {
                        alert(request.responseText);
                    },
                });
            };

            function cancel(id_c) {
                let html = $.ajax({
                    type: "POST",
                    url: '{{ route('update_status') }}',
                    data: {
                        id_c: id_c
                    },
                    async: false
                }).responseText;

                swal.fire({
                    position: 'mid-end',
                    icon: 'info',
                    title: 'Data Sudah Di Ubah',
                    showConfirmButton: false,
                    timer: 1000
                });

                datatable.ajax.reload();
            };

            function printMarker(kodeMarker) {
                let fileName = kodeMarker;

                // Show Loading
                Swal.fire({
                    title: 'Please Wait...',
                    html: 'Exporting Data...',
                    didOpen: () => {
                        Swal.showLoading()
                    },
                    allowOutsideClick: false,
                });

                $.ajax({
                    url: '{{ route('print-marker') }}/' + kodeMarker.replace(/\//g, '_'),
                    type: 'post',
                    processData: false,
                    contentType: false,
                    xhrFields: {
                        responseType: 'blob'
                    },
                    success: function(res) {
                        if (res) {
                            var blob = new Blob([res], {
                                type: 'application/pdf'
                            });

                            var link = document.createElement('a');
                            link.href = window.URL.createObjectURL(blob);
                            link.download = fileName + ".pdf";
                            link.click();

                            swal.close();
                        }
                    }
                });
            }

            function fixMarkerBalanceQty() {
                Swal.fire({
                    title: 'Please Wait...',
                    html: 'Fixing Data...',
                    didOpen: () => {
                        Swal.showLoading()
                    },
                    allowOutsideClick: false,
                });

                $.ajax({
                    url: '{{ route('fix-marker-balance-qty') }}',
                    method: 'POST',
                    dataType: 'json',
                    success: async function(res) {
                        console.log(res);

                        await swal.close();

                        Swal.fire({
                            icon: 'success',
                            title: res.message,
                            showCancelButton: false,
                            showConfirmButton: true,
                            confirmButtonText: 'Oke',
                            timer: (res.status == 200 ? 5000 : 200),
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
                    },
                    error: function(jqXHR) {
                        console.log(jqXHR);
                    },
                });
            }

        // Part :
            function getproses() {
                let tujuan = document.form.part_tujuan_input.value;
                let html = $.ajax({
                    type: "GET",
                    url: '{{ route('get_proses') }}',
                    data: {
                        cbotuj: tujuan
                    },
                    async: false
                }).responseText;

                console.log(html != "");

                if (html != "") {
                    $("#part_proses_input").html(html);
                }
            };

            let partTable = $("#part-table").DataTable({
                ordering: false,
                processing: true,
                serverSide: true,
                paging: false,
                destroy: true,
                ajax: {
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: '{{ route('track-ws-part') }}',
                    dataType: 'json',
                    dataSrc: 'data',
                    data: function(d) {
                        d.actCostingId = $('#id').val();
                        d.panel = $('#part_panel_input').val();
                    },
                },
                columns: [
                    {
                        data: 'id',
                    },
                    {
                        data: 'nama_panel',
                    },
                    {
                        data: 'nama_part',
                    },
                    {
                        data: 'cons',
                    },
                    {
                        data: 'unit',
                    },
                    {
                        data: 'tujuan',
                    },
                    {
                        data: 'proses',
                    },
                ],
                columnDefs: [
                    {
                        targets: [0],
                        className: "text-center",
                        render: (data, type, row, meta) => {
                            return `
                                <a class='btn btn-danger btn-sm' data='`+JSON.stringify(row)+`' data-url='{{ route('destroy-part-detail') }}/`+row['id']+`' onclick='deleteThisPart(this);'>
                                    <i class='fa fa-trash'></i>
                                </a>
                            `;
                        }
                    },
                ]
            });

            function partTableReload() {
                partTable.ajax.reload();
            }

            function clearPartInput() {
                $("#part_panel_input").val('').trigger('change');
                $("#part_part_input").val('').trigger('change');
                $("#part_proses_input").val('').trigger('change');
                $("#part_tujuan_input").val('').trigger('change');
                $("#part_cons_input").val('').trigger('change');
            }

            function getPartId() {
                $.ajax({
                    type: "get",
                    url: '{{ route('track-ws-part-id') }}',
                    data: {
                        actCostingId: $("#id").val(),
                        panel: $("#part_panel_input").val()
                    },
                    success: function(response) {
                        document.getElementById("part_id_input").value = response.id;
                    },
                    error: function(jqXHR) {
                        console.log(jqXHR);
                    }
                });
            }

            function savePartData() {
                let id = document.getElementById("part_id_input").value;
                let tujuan = document.form.part_tujuan_input.value;
                let part = document.form.part_part_input.value;
                let cons = document.form.part_cons_input.value;
                let proses = document.form.part_proses_input.value;
                $.ajax({
                    type: "post",
                    url: '{{ route('store_part_secondary') }}',
                    data: {
                        id: id,
                        cbotuj: tujuan,
                        txtpart: part,
                        txtcons: cons,
                        cboproses: proses
                    },
                    success: function(response) {
                        if (response.icon == 'salah') {
                            iziToast.warning({
                                message: response.msg,
                                position: 'topCenter'
                            });
                        } else {
                            iziToast.success({
                                message: response.msg,
                                position: 'topCenter'
                            });

                            partTableReload();
                            clearPartInput();
                        }
                    },
                    // error: function(request, status, error) {
                    //     alert(request.responseText);
                    // },
                });
            };

            function deleteThisPart(e) {
                let data = JSON.parse(e.getAttribute('data'));

                if (data.hasOwnProperty('id')) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Hapus data?',
                        showCancelButton: true,
                        showConfirmButton: true,
                        confirmButtonText: 'Hapus',
                        cancelButtonText: 'Batal',
                        confirmButtonColor: '#fa4456',
                    }).then((result) => {
                        if (result.isConfirmed) {
                            if (document.getElementById("loading")) {
                                document.getElementById("loading").classList.remove("d-none");
                            }

                            $.ajax({
                                url: e.getAttribute('data-url'),
                                type: 'POST',
                                data: {
                                    _method: 'DELETE'
                                },
                                success: function(res) {
                                    if (document.getElementById("loading")) {
                                        document.getElementById("loading").classList.add("d-none");
                                    }

                                    if (res.status == 200) {
                                        iziToast.success({
                                            title: 'Success',
                                            message: res.message,
                                            position: 'topCenter'
                                        });

                                        $('.modal').modal('hide');
                                    } else {
                                        iziToast.error({
                                            title: 'Error',
                                            message: res.message,
                                            position: 'topCenter'
                                        });
                                    }

                                    partTableReload();

                                    if (res.table != '') {
                                        $('#'+res.table).DataTable().ajax.reload();
                                    }
                                }, error: function (jqXHR) {
                                    if (document.getElementById("loading")) {
                                        document.getElementById("loading").classList.add("d-none");
                                    }

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
            }

        // Spreading Form :
            var formTableParameter = ['frm_action', 'frm_tanggal', 'frm_no_form', 'frm_no_meja', 'frm_no_marker', 'frm_color', 'frm_panel', 'frm_qty_ply', 'frm_ratio', 'frm_keterangan', 'frm_status', 'frm_plan'];

            $('#form-table thead tr').clone(true).appendTo('#form-table thead');
            $('#form-table thead tr:eq(1) th').each(function(i) {
                if (i != 0 && i != 9 && i != 10 && i != 12) {
                    var title = $(this).text();
                    $(this).html('<input type="text" class="form-control form-control-sm" id="'+formTableParameter[i]+'""/>');

                    $('input', this).on('keyup change', function() {
                        if (formTable.column(i).search() !== this.value) {
                            formTable
                                .column(i)
                                .search(this.value)
                                .draw();
                        }
                    });
                } else {
                    $(this).empty();
                }
            });

            let formTable = $("#form-table").DataTable({
                ordering: false,
                processing: true,
                serverSide: true,
                pageLength: 50,
                dom: 'rtip',
                scrollX: "500px",
                scrollY: "350px",
                ajax: {
                    url: '{{ route('track-ws-form') }}',
                    data: function(d) {
                        d.actCostingId = $('#id').val();
                        d.panel = $('#ws-panel-filter').val();
                        d.color = $('#ws-color-filter').val();
                        d.size = $('#ws-size-filter').val();
                        d.dateFrom = $('#form-from').val();
                        d.dateTo = $('#form-to').val();
                    },
                },
                columns: [
                    {
                        data: 'id'
                    },
                    {
                        data: 'tgl_form_cut'
                    },
                    {
                        data: 'no_form'
                    },
                    {
                        data: 'nama_meja'
                    },
                    {
                        data: 'id_marker'
                    },
                    {
                        data: 'color'
                    },
                    {
                        data: 'panel'
                    },
                    {
                        data: 'ply_progress'
                    },
                    {
                        data: 'marker_details'
                    },
                    {
                        data: 'notes'
                    },
                    {
                        data: 'status'
                    },
                    {
                        data: 'tgl_plan'
                    },
                ],
                columnDefs: [
                    {
                        targets: [0],
                        render: (data, type, row, meta) => {
                            let btnEditMeja = row.status == 'SPREADING' ? "<a href='javascript:void(0);' class='btn btn-info btn-sm' onclick='editData(" + JSON.stringify(row) + ", \"editMejaModal\", [{\"function\" : \"formRatioTableReload()\"}]);'><i class='fa fa-edit'></i></a>" : "<button class='btn btn-info btn-sm' onclick='editData(" + JSON.stringify(row) + ", \"editMejaModal\", [{\"function\" : \"formRatioTableReload()\"}]);' disabled><i class='fa fa-edit'></i></button>";
                            let btnEditStatus = row.status != 'SPREADING' ? "<a href='javascript:void(0);' class='btn btn-primary btn-sm' onclick='editData(" + JSON.stringify({'id_status' : row.id, 'status' : row.status}) + ", \"editStatusModal\", [{\"function\" : \"formRatioTable1Reload()\"}]);'><i class='fa fa-cog'></i></a>" : "<button class='btn btn-primary btn-sm' onclick='editData(" + JSON.stringify({'id_status' : row.id, 'status' : row.status}) + ", \"editStatusModal\", [{\"function\" : \"formRatioTable1Reload()\"}]);' disabled><i class='fa fa-cog'></i></button>";
                            let btnDelete = row.status == 'SPREADING' ? "<a href='javascript:void(0);' class='btn btn-danger btn-sm' data='"+JSON.stringify(row)+"' data-url='"+'{{ route('destroy-spreading') }}'+"/"+row.id+"' onclick='deleteData(this);'><i class='fa fa-trash'></i></a>" : "<button class='btn btn-danger btn-sm' data='"+JSON.stringify(row)+"' data-url='"+'{{ route('destroy-spreading') }}'+"/"+row.id+"' onclick='deleteData(this);' disabled><i class='fa fa-trash'></i></button>";
                            let btnProcess = "";

                            if (row.tipe_form_cut == 'MANUAL') {
                                btnProcess = (row.qty_ply > 0 && row.no_meja != '' && row.no_meja != null && row.app == 'Y') || row.status != 'SPREADING' ?
                                    `<a class='btn btn-success btn-sm' href='`+(row.status == "SELESAI PENGERJAAN" ? `{{ route('detail-cutting') }}` : `{{ route('process-manual-form-cut') }}`)+`/` + row.id + `' data-bs-toggle='tooltip' target='_blank'><i class='fa `+ (row.status == "SELESAI PENGERJAAN" ? `fa-search-plus` : `fa-plus`) +`'></i></a>` :
                                    `<button class='btn btn-success btn-sm' data-bs-toggle='tooltip' disabled><i class='fa `+ (row.status == "SELESAI PENGERJAAN" ? `fa-search-plus` : `fa-plus`) +`'></i></button>`;
                            } else if (row.tipe_form_cut == 'PILOT') {
                                btnProcess = (row.qty_ply > 0 && row.no_meja != '' && row.no_meja != null && row.app == 'Y') || row.status != 'SPREADING' ?
                                    `<a class='btn btn-success btn-sm' href='`+(row.status == "SELESAI PENGERJAAN" ? `{{ route('detail-cutting') }}` : `{{ route('process-pilot-form-cut') }}`)+`/` + row.id + `' data-bs-toggle='tooltip' target='_blank'><i class='fa `+(row.status == "SELESAI PENGERJAAN" ? `fa-search-plus` : `fa-plus`)+`'></i></a>` :
                                    `<button class='btn btn-success btn-sm' data-bs-toggle='tooltip' disabled><i class='fa `+(row.status == "SELESAI PENGERJAAN" ? `fa-search-plus` : `fa-plus`)+`'></i></button>`;
                            } else {
                                btnProcess = (row.qty_ply > 0 && row.no_meja != '' && row.no_meja != null && row.app == 'Y') || row.status != 'SPREADING' ?
                                    `<a class='btn btn-success btn-sm' href='`+(row.status == "SELESAI PENGERJAAN" ? `{{ route('detail-cutting') }}` : `{{ route('process-form-cut-input') }}`)+`/` + row.id + `' data-bs-toggle='tooltip' target='_blank'><i class='fa `+(row.status == "SELESAI PENGERJAAN" ? `fa-search-plus` : `fa-plus`)+`'></i></a>` :
                                    `<button class='btn btn-success btn-sm' data-bs-toggle='tooltip' disabled><i class='fa `+(row.status == "SELESAI PENGERJAAN" ? `fa-search-plus` : `fa-plus`)+`'></i></button>`;
                            }

                            return `<div class='d-flex gap-1 justify-content-center'>` + btnEditMeja + btnEditStatus + btnProcess + btnDelete + `</div>`;
                        }
                    },
                    {
                        targets: [7],
                        render: (data, type, row, meta) => {
                            return `
                                <div class="progress border border-sb position-relative" style="min-width: 150px;height: 21px">
                                    <p class="position-absolute" style="top: 50%;left: 50%;transform: translate(-50%, -50%);">`+row.total_lembar+`/`+row.qty_ply+`</p>
                                    <div class="progress-bar" style="background-color: #75baeb;width: `+((row.total_lembar/row.qty_ply)*100)+`%" role="progressbar"></div>
                                </div>
                            `;
                        }
                    },
                    {
                        targets: [10],
                        className: "text-center align-middle",
                        render: (data, type, row, meta) => {
                            icon = "";

                            switch (data) {
                                case "SPREADING":
                                case "PENGERJAAN PILOT MARKER":
                                case "PENGERJAAN PILOT DETAIL":
                                    icon = `<i class="fas fa-file fa-lg"></i>`;
                                    break;
                                case "PENGERJAAN MARKER":
                                case "PENGERJAAN FORM CUTTING":
                                case "PENGERJAAN FORM CUTTING DETAIL":
                                case "PENGERJAAN FORM CUTTING SPREAD":
                                    icon = `<i class="fas fa-sync-alt fa-spin fa-lg" style="color: #2243d6;"></i>`;
                                    break;
                                case "SELESAI PENGERJAAN":
                                    icon = `<i class="fas fa-check fa-lg" style="color: #087521;"></i>`;
                                    break;
                            }

                            return icon;
                        }
                    },
                    {
                        targets: '_all',
                        className: "text-nowrap",
                        render: (data, type, row, meta) => {
                            let color = "";

                            if (row.status == 'SELESAI PENGERJAAN') {
                                color = '#087521';
                            } else if (row.status == 'PENGERJAAN MARKER') {
                                color = '#2243d6';
                            } else if (row.status == 'PENGERJAAN FORM CUTTING') {
                                color = '#2243d6';
                            } else if (row.status == 'PENGERJAAN FORM CUTTING DETAIL') {
                                color = '#2243d6';
                            } else if (row.status == 'PENGERJAAN FORM CUTTING SPREAD') {
                                color = '#2243d6';
                            }

                            return  "<span style='font-weight: 600; color: "+ color + "' >" + (data ? data : '-') + "</span>"
                        }
                    },
                ],
                rowCallback: function( row, data, index ) {
                    if (data['tipe_form_cut'] == 'MANUAL') {
                        $('td', row).css('background-color', '#e7dcf7');
                        $('td', row).css('border', '0.15px solid #d0d0d0');
                    } else if (data['tipe_form_cut'] == 'PILOT') {
                        $('td', row).css('background-color', '#c5e0fa');
                        $('td', row).css('border', '0.15px solid #d0d0d0');
                    }
                },
                footerCallback: async function (row, data, start, end, display) {
                    let api = this.api();

                    api.column(7).footer().innerHTML = "...";

                    let totalForm = await getTotalForm();

                    if (await totalForm) {
                        api.column(6).footer().innerHTML = totalForm.total_form;
                        api.column(7).footer().innerHTML = `
                            <div class="progress border border-sb position-relative" style="min-width: 50px;height: 21px">
                                <p class="position-absolute" style="top: 50%;left: 50%;transform: translate(-50%, -50%);">`+(totalForm.total_lembar).toLocaleString("id-ID")+`/`+(totalForm.qty_ply).toLocaleString("id-ID")+`</p>
                                <div class="progress-bar" style="background-color: #75baeb;width: `+((totalForm.total_lembar/totalForm.qty_ply)*100)+`%" role="progressbar"></div>
                            </div>
                        `;

                        document.getElementById('total-form').innerHTML = totalForm.total_form;
                        document.getElementById('total-form-ply').innerHTML = `
                            <div class="progress border border-sb position-relative" style="min-width: 50px;height: 30px">
                                <p class="position-absolute" style="font-size: 16px;top: 50%;left: 50%;transform: translate(-50%, -50%);">`+(totalForm.total_lembar).toLocaleString("id-ID")+`/`+(totalForm.qty_ply).toLocaleString("id-ID")+`</p>
                                <div class="progress-bar" style="background-color: #75baeb;width: `+((totalForm.total_lembar/totalForm.qty_ply)*100)+`%" role="progressbar"></div>
                            </div>
                        `;
                    }
                }
            });

            function formTableReload() {
                formTable.ajax.reload();
            }

            async function getTotalForm() {
                return $.ajax({
                    url: '{{ route('track-ws-form-total') }}',
                    type: 'GET',
                    dataType: 'json',
                    data: {
                        actCostingId : $('#id').val(),
                        dateFrom : $('#marker-from').val(),
                        dateTo : $('#marker-to').val(),
                        color : $('#ws-color-filter').val(),
                        size : $('#ws-size-filter').val(),
                        panel : $('#ws-panel-filter').val(),
                        tanggal : $('#frm_tanggal').val(),
                        meja : $('#frm_no_meja').val(),
                        no_marker : $('#frm_no_marker').val(),
                        frm_color : $('#frm_color').val(),
                        frm_panel : $('#frm_panel').val(),
                        ratio : $('#frm_ratio').val(),
                        qty_ply : $('#frm_qty_ply').val(),
                        plan : $('#frm_plan').val()
                    },
                });
            }

            let formRatioTable = $("#form-ratio-table").DataTable({
                ordering: false,
                processing: true,
                serverSide: true,
                paging: false,
                ajax: {
                    url: '{{ route('getdata_ratio') }}',
                    data: function(d) {
                        d.cbomarker = $('#edit_marker_id').val();
                    },
                },
                columns: [
                    {
                        data: 'size'
                    },
                    {
                        data: 'ratio'
                    },
                    {
                        data: 'cut_qty'
                    },
                ]
            });

            function formRatioTableReload() {
                formRatioTable.ajax.reload();
            }

            let formRatioTable1 = $("#form-ratio-table-1").DataTable({
                ordering: false,
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('getdata_ratio') }}',
                    data: function(d) {
                        d.cbomarker = $('#edit_marker_id').val();
                    },
                },
                columns: [
                    {
                        data: 'size'
                    },
                    {
                        data: 'ratio'
                    },
                    {
                        data: 'cut_qty'
                    },
                ]
            });

            function formRatioTable1Reload() {
                formRatioTable1.ajax.reload();
            }

        // Roll Consumption
            var rollTableParameter = ['roll_color', 'roll_panel', 'roll_no_form', 'roll_id_item', 'roll_nama_barang', 'roll_no_meja', 'roll_qty', 'roll_unit'];

            $('#roll-table thead tr').clone(true).appendTo('#roll-table thead');
            $('#roll-table thead tr:eq(1) th').each(function(i) {
                if (i != 6 && i != 8 && i != 9 && i != 10 && i != 11 && i != 12 && i != 13 && i != 14 && i != 15 && i != 16 && i != 17 && i != 18) {
                    var title = $(this).text();
                    $(this).html('<input type="text" class="form-control form-control-sm" id="'+rollTableParameter[i]+'" />');

                    $('input', this).on('keyup change', function() {
                        if (rollTable.column(i).search() !== this.value) {
                            rollTable
                                .column(i)
                                .search(this.value)
                                .draw();
                        }
                    });
                } else {
                    $(this).empty();
                }
            });

            let rollTable = $("#roll-table").DataTable({
                processing: true,
                serverSide: true,
                ordering: false,
                dom: 'rtip',
                scrollX: "500px",
                scrollY: "350px",
                pageLength: 50,
                ajax: {
                    url: '{{ route('track-ws-roll') }}',
                    data: function(d) {
                        d.actCostingId = $('#id').val();
                        d.panel = $('#ws-panel-filter').val();
                        d.color = $('#ws-color-filter').val();
                        d.dateFrom = $('#roll-from').val();
                        d.dateTo = $('#roll-to').val();
                    },
                },
                columns: [
                    {
                        data: 'color'
                    },
                    {
                        data: 'panel'
                    },
                    {
                        data: 'no_form_cut_input'
                    },
                    {
                        data: 'id_item'
                    },
                    {
                        data: 'detail_item'
                    },
                    {
                        data: 'nama_meja'
                    },
                    {
                        data: 'qty_item'
                    },
                    {
                        data: 'unit_item'
                    },
                    {
                        data: 'lembar_gelaran'
                    },
                    {
                        data: 'total_pemakaian_roll'
                    },
                    {
                        data: 'short_roll'
                    },
                    {
                        data: 'remark'
                    },
                    {
                        data: 'sisa_gelaran'
                    },
                    {
                        data: 'sambungan'
                    },
                    {
                        data: 'kepala_kain'
                    },
                    {
                        data: 'sisa_tidak_bisa'
                    },
                    {
                        data: 'reject'
                    },
                    {
                        data: 'sisa_kain'
                    },
                    {
                        data: 'piping'
                    },
                ],
                columnDefs: [
                    {
                        targets: "_all",
                        className: "text-nowrap"
                    },
                    {
                        targets: [6, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18],
                        className: "text-nowrap",
                        render: (data, type, row, meta) => {
                            return data ? '<div style="min-width: 100px;">'+(Number(data).round(2)).toLocaleString("id-ID")+'</div>' : 0;
                        }
                    },
                ],
                footerCallback: async function (row, data, start, end, display) {
                    let api = this.api();

                    api.column(6).footer().innerHTML = "...";
                    api.column(7).footer().innerHTML = "...";
                    api.column(8).footer().innerHTML = "...";
                    api.column(9).footer().innerHTML = "...";
                    api.column(10).footer().innerHTML = "...";
                    api.column(11).footer().innerHTML = "...";
                    api.column(12).footer().innerHTML = "...";
                    api.column(13).footer().innerHTML = "...";
                    api.column(14).footer().innerHTML = "...";
                    api.column(15).footer().innerHTML = "...";
                    api.column(16).footer().innerHTML = "...";
                    api.column(17).footer().innerHTML = "...";
                    api.column(18).footer().innerHTML = "...";

                    let totalRoll = await getTotalRoll();

                    if (await totalRoll) {
                        api.column(6).footer().innerHTML = totalRoll.totalQty;
                        api.column(7).footer().innerHTML = totalRoll.totalUnit;
                        api.column(8).footer().innerHTML = totalRoll.totalLembarGelaran;
                        api.column(9).footer().innerHTML = totalRoll.totalTotalPemakaian;
                        api.column(10).footer().innerHTML = totalRoll.totalShortRoll;
                        api.column(11).footer().innerHTML = totalRoll.totalRemark;
                        api.column(12).footer().innerHTML = totalRoll.totalSisaGelaran;
                        api.column(13).footer().innerHTML = totalRoll.totalSambungan;
                        api.column(14).footer().innerHTML = totalRoll.totalKepalaKain;
                        api.column(15).footer().innerHTML = totalRoll.totalSisaTidakBisa;
                        api.column(16).footer().innerHTML = totalRoll.totalReject;
                        api.column(17).footer().innerHTML = totalRoll.totalSisaKain;
                        api.column(18).footer().innerHTML = totalRoll.totalPiping;

                        document.getElementById('total-roll-qty').innerText = totalRoll.totalQty+" "+totalRoll.totalUnit;
                        document.getElementById('total-roll-lembar').innerText = totalRoll.totalLembarGelaran+" Lembar";
                        document.getElementById('total-roll-pemakaian').innerText = totalRoll.totalTotalPemakaian+" "+totalRoll.totalUnit;
                    }
                }
            });

            function rollTableReload() {
                rollTable.ajax.reload();
            }

            async function getTotalRoll() {
                return $.ajax({
                    url: '{{ route('track-ws-roll-total') }}',
                    type: 'GET',
                    dataType: 'json',
                    data: {
                        actCostingId : $('#id').val(),
                        dateFrom : $('#marker-from').val(),
                        dateTo : $('#marker-to').val(),
                        color : $('#ws-color-filter').val(),
                        panel : $('#ws-panel-filter').val(),
                        roll_color : $('#roll_color').val(),
                        roll_panel : $('#roll_panel').val(),
                        roll_no_form : $('#roll_no_form').val(),
                        roll_id_item : $('#roll_id_item').val(),
                        roll_nama_barang : $('#roll_nama_barang').val(),
                        roll_no_meja : $('#roll_no_meja').val(),
                        roll_qty : $('#roll_qty').val(),
                        roll_unit : $('#roll_unit').val(),
                    },
                });
            }

        // Stocker
            var stockerTableParameter = ['stk_color', 'stk_panel', 'stk_part', 'stk_no_form', 'stk_no_cut', 'stk_size', 'stk_group', 'stk_no_stocker', 'stk_qty', 'stk_range', 'stk_difference', 'stk_secondary','stk_rack', 'stk_trolley', 'stk_line', 'stk_updated_at'];

            $('#stocker-table thead tr').clone(true).appendTo('#stocker-table thead');
                $('#stocker-table thead tr:eq(1) th').each(function(i) {
                    if (i != 8 && i != 9 && i != 15) {
                        var title = $(this).text();
                        $(this).html('<input type="text" class="form-control form-control-sm" id="'+stockerTableParameter[i]+'" />');

                        $('input', this).on('keyup change', function() {
                            if (stockerTable.column(i).search() !== this.value) {
                                stockerTable
                                    .column(i)
                                    .search(this.value)
                                    .draw();
                            }
                        });
                    } else {
                        $(this).empty();
                    }
                });

                let stockerTable = $("#stocker-table").DataTable({
                    ordering: false,
                    processing: true,
                    serverSide: true,
                    pageLength: 50,
                    dom: 'rtip',
                    scrollX: "500px",
                    scrollY: "350px",
                    ajax: {
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        url: '{{ route('track-ws-stocker') }}',
                        dataType: 'json',
                        dataSrc: 'data',
                        data: function(d) {
                            d.actCostingId = $('#id').val();
                            d.panel = $('#ws-panel-filter').val();
                            d.color = $('#ws-color-filter').val();
                            d.size = $('#ws-size-filter').val();
                            d.dateFrom = $('#stocker-from').val();
                            d.dateTo = $('#stocker-to').val();
                        },
                    },
                    columns: [
                        {
                            data: 'color',
                        },
                        {
                            data: 'panel',
                        },
                        {
                            data: 'nama_part',
                        },
                        {
                            data: 'no_form',
                        },
                        {
                            data: 'no_cut',
                        },
                        {
                            data: 'size',
                        },
                        {
                            data: 'shade',
                        },
                        {
                            data: 'id_qr_stocker',
                        },
                        {
                            data: 'qty_ply',
                        },
                        {
                            data: 'stocker_range',
                        },
                        {
                            data: 'difference_qty',
                        },
                        {
                            data: 'secondary',
                        },
                        {
                            data: 'rak',
                        },
                        {
                            data: 'troli',
                        },
                        {
                            data: 'line',
                        },
                        {
                            data: 'latest_update',
                        },
                    ],
                    columnDefs: [
                        {
                            targets: [0, 1, 2, 3, 4, 5],
                            className: "text-nowrap"
                        },
                        {
                            targets: [7, 8, 9],
                            render: (data, type, row, meta) => {
                                return `<div style="min-width: 100px;">`+data+`</div>`;
                            }
                        },
                        {
                            targets: "_all",
                            className: "text-nowrap colorize"
                        }
                    ],
                    rowsGroup: [
                        0,
                        1,
                        2,
                        3,
                        4,
                        5
                    ],
                    rowCallback: function( row, data, index ) {
                        if (data['line'] != '-') {
                            $('td.colorize', row).css('color', '#2e8a57');
                            $('td.colorize', row).css('font-weight', '600');
                        } else if (!data['dc_in_id'] && data['troli'] == '-') {
                            $('td.colorize', row).css('color', '#da4f4a');
                            $('td.colorize', row).css('font-weight', '600');
                        }
                    },
                    footerCallback: async function (row, data, start, end, display) {
                        let api = this.api();

                        api.column(7).footer().innerHTML = "...";
                        api.column(8).footer().innerHTML = "...";
                        api.column(9).footer().innerHTML = "...";
                        api.column(10).footer().innerHTML = "...";

                        let totalStocker = await getTotalStocker();

                        if (await totalStocker) {
                            api.column(7).footer().innerHTML = totalStocker.totalStocker;
                            api.column(8).footer().innerHTML = totalStocker.totalQtyPly;
                            api.column(9).footer().innerHTML = totalStocker.totalRange;
                            api.column(10).footer().innerHTML = totalStocker.totalDifference;

                            document.getElementById("total-stocker").innerText = totalStocker.totalStocker;
                            document.getElementById("total-stocker-qty").innerText = totalStocker.totalQtyPly;
                            document.getElementById("total-stocker-range").innerText = totalStocker.totalRange;
                            document.getElementById("total-stocker-difference").innerText = totalStocker.totalDifference;
                        }
                    }
                });

                async function stockerTableReload() {
                    document.getElementById('loading').classList.remove("d-none");

                    stockerTable.ajax.reload();

                    document.getElementById('loading').classList.add("d-none");
                }

                async function getTotalStocker() {
                    return $.ajax({
                        url: '{{ route('track-ws-stocker-total') }}',
                        type: 'GET',
                        dataType: 'json',
                        data: {
                            actCostingId : $('#id').val(),
                            dateFrom : $('#stocker-from').val(),
                            dateTo : $('#stocker-to').val(),
                            color : $('#ws-color-filter').val(),
                            panel : $('#ws-panel-filter').val(),
                            size : $('#ws-size-filter').val(),
                            stkColor : $('#stk_color').val(),
                            stkPanel : $('#stk_panel').val(),
                            stkPart : $('#stk_part').val(),
                            stkNoForm : $('#stk_no_form').val(),
                            stkNoCut : $('#stk_no_cut').val(),
                            stkSize : $('#stk_size').val(),
                            stkGroup : $('#stk_group').val(),
                            stkNoStocker : $('#stk_no_stocker').val(),
                            stkSecondary : $('#stk_secondary').val(),
                            stkRack : $('#stk_rack').val(),
                            stkTrolley : $('#stk_trolley').val(),
                            stkLine : $('#stk_line').val(),
                            stkDifference : $('#stk_difference').val()
                        },
                    });
                }

        // Sewing Output
            // Output Chart
            var options = {
                series: [],
                chart: {
                    type: 'line',
                    toolbar: {
                        show: true
                    }
                },
                colors: ['#b02ffa', '#428af5', '#1c59ff', '#2e8a57'],
                grid: {
                    borderColor: '#e7e7e7',
                    row: {
                        colors: ['#ebebeb', 'transparent'], // takes an array which will be repeated on columns
                        opacity: 0.5
                    },
                },
                yaxis: {
                    tickAmount: 10,
                    labels: {
                        formatter: function (value) {
                            return Math.round(value);
                        }
                    },
                },
                dataLabels: {
                    enabled: true,
                    formatter: function (val, opts) {
                        return val.toLocaleString('ID-id');
                    },
                    style: {
                        fontSize: '10px',
                        fontFamily: 'Helvetica, Arial, sans-serif',
                    },
                },
                legend: {
                    show: true,
                    position: 'top',
                    horizontalAlign: 'left',
                },
                noData: {
                    text: 'Loading...'
                }
            };

            var chart = new ApexCharts(document.querySelector("#output-line-chart"), options);
            chart.render();

            async function getLineOutput() {
                document.getElementById("loading").classList.remove("d-none");

                return $.ajax({
                    url: '{{ route('track-ws-sewing-output') }}',
                    type: 'get',
                    data: {
                        actCostingId : $('#id').val(),
                        panel : $('#ws-panel-filter').val(),
                        color : $('#ws-color-filter').val(),
                        size : $('#ws-size-filter').val(),
                        dateFrom : $('#output-line-from').val(),
                        dateTo : $('#output-line-to').val()
                    },
                    dataType: 'json',
                    success: async function(res) {
                        let tglArr = [];
                        let rftArr = [];
                        let defectArr = [];
                        let rejectArr = [];
                        let packingArr = [];

                        let rftTotal = 0;
                        let defectTotal = 0;
                        let rejectTotal = 0;
                        let packingTotal = 0;

                        if (res) {
                            res.forEach(item => {
                                tglArr.push(item.tgl_produksi);
                                rftArr.push(item.rft_output);
                                defectArr.push(item.defect_output);
                                rejectArr.push(item.reject_output);
                                packingArr.push(item.rft_packing_output);

                                rftTotal += Number(item.rft_output);
                                defectTotal += Number(item.defect_output);
                                rejectTotal += Number(item.reject_output);
                                packingTotal += Number(item.rft_packing_output);
                            });

                            document.getElementById("output-rft").innerText = rftTotal.toLocaleString("ID-id");
                            document.getElementById("output-defect").innerText = defectTotal.toLocaleString("ID-id");
                            document.getElementById("output-reject").innerText = rejectTotal.toLocaleString("ID-id");
                            document.getElementById("output-packing").innerText = packingTotal.toLocaleString("ID-id");

                            await chart.updateSeries(
                            [
                                {
                                    name: 'Rft',
                                    data: rftArr
                                },
                                {
                                    name: 'Defect',
                                    data: defectArr
                                },
                                {
                                    name: 'Reject',
                                    data: rejectArr
                                },
                                {
                                    name: 'Packing',
                                    data: packingArr
                                }
                            ], true);

                            await chart.updateOptions({
                                xaxis: {
                                    categories: tglArr,
                                },
                                noData: {
                                    text: 'Data Not Found'
                                }
                            });
                        }

                        document.getElementById("loading").classList.add("d-none");
                    }, error: function (jqXHR) {
                        let res = jqXHR.responseJSON;
                        console.error(res.message);
                        iziToast.error({
                            title: 'Error',
                            message: res.message,
                            position: 'topCenter'
                        });

                        document.getElementById("loading").classList.add("d-none");
                    }
                });
            }
    </script>
@endsection
