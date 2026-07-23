@extends('layouts.index')

@section('custom-link')
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
    <style>
        .select2-container--open {
            z-index: 99999 !important;
        }
        #table-material-so thead th {
            position: sticky;
            top: 0;
            z-index: 10;
            background: #f8f9fa;
            vertical-align: middle;
        }
        .modal-xl {
            max-width: 95% !important;
        }

        .table-scroll-modal {
            max-height: 60vh;
            overflow-y: auto;
            overflow-x: auto;
        }

        #table-detail-so thead th,
        #table-material-so thead th {
            position: sticky;
            top: 0;
            z-index: 10;
            background: #f8f9fa;
            vertical-align: middle;
            box-shadow: 0 2px 2px -1px rgba(0, 0, 0, 0.2);
        }

        .modal-xl {
            max-width: 95% !important;
        }

        #table-material-so thead th {
            position: sticky;
            top: 0;
            z-index: 2;
            background-color: #f8f9fa;
            box-shadow: inset 0 -1px 0 #dee2e6, inset 0 1px 0 #dee2e6;
        }
    </style>
    <style>
        /* ===== Modal Detail SO - Redesign ===== */
        #modal-detail .modal-content {
            border: none;
            border-radius: 14px;
            overflow: hidden;
        }

        #modal-detail .modal-header {
            background: linear-gradient(135deg, #0d1b3e 0%, #0d1b3e 100%);
            border-bottom: none;
            padding: 1rem 1.5rem;
        }

        #modal-detail .modal-header .modal-title {
            font-weight: 600;
            letter-spacing: .3px;
        }

        #modal-detail .modal-body {
            background: #f4f6f9;
            padding: 1.25rem 1.5rem;
        }

        /* Info header card (No SO, WS, Style, Buyer) */
        .so-info-card {
            background: linear-gradient(135deg, #f8fafc 0%, #edf2f7 100%);
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 1.15rem 1.25rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
            margin-bottom: 1.25rem;
        }

        .so-info-item {
            background: #ffffff;
            border-radius: 10px;
            padding: 0.75rem 0.9rem;
            border: 1px solid #edf2f7;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .so-info-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(42, 82, 152, 0.08);
            border-color: #cbd5e1;
        }

        .so-info-item .so-info-label {
            font-size: 10.5px;
            text-transform: uppercase;
            letter-spacing: .6px;
            color: #64748b;
            font-weight: 700;
            margin-bottom: 3px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .so-info-item .so-info-label i {
            color: #3b82f6;
            font-size: 11px;
        }

        .so-info-item .so-info-value {
            font-size: 14px;
            font-weight: 700;
            color: #1e293b;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Section cards (List Detail Qty / Tambah Warna) */
        .section-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,.06);
            height: 100%;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .section-card-header {
            padding: .85rem 1.1rem;
            border-bottom: 1px solid #eef1f5;
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
            user-select: none;
        }

        .section-card-header h6 {
            margin: 0;
            font-weight: 700;
            font-size: 14px;
            color: #1e2a3a;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .section-card-header h6 i {
            color: #2a5298;
        }

        .section-card-header .section-toggle-icon {
            color: #8a94a6;
            transition: transform .2s ease;
        }

        .section-card-header.collapsed .section-toggle-icon {
            transform: rotate(-90deg);
        }

        .section-card-header-right {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-card-body {
            padding: 1rem 1.1rem;
            flex: 1;
            max-height: 500px;
            overflow: auto;
            transition: max-height .25s ease, padding .25s ease;
        }

        .section-card-body.collapsed {
            max-height: 0;
            padding-top: 0;
            padding-bottom: 0;
            overflow: hidden;
        }

        .section-card.section-add .section-card-header h6 i {
            color: #198754;
        }

        /* Badge total qty di header list detail */
        .badge-total-qty {
            background: #eaf1ff;
            color: #2a5298;
            font-weight: 600;
            font-size: 12px;
            padding: 5px 10px;
            border-radius: 20px;
        }

        /* Table detail qty */
        #table-detail-so {
            font-size: 13px;
        }

        #table-detail-so thead th {
            background: #f8f9fb !important;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .4px;
            color: #6c7788;
            border-bottom: 2px solid #eef1f5 !important;
        }

        #table-detail-so tbody tr:hover {
            background: #f8fafd;
        }

        #table-detail-so .qty-input {
            border-radius: 6px;
            font-weight: 600;
        }

        #table-detail-so .qty-input:focus {
            border-color: #2a5298;
            box-shadow: 0 0 0 .15rem rgba(42,82,152,.15);
        }

        #table-detail-so .btn-danger,
        #table-detail-so .btn-info {
            border-radius: 6px;
            font-size: 12px;
            padding: .25rem .6rem;
        }

        /* Form tambah warna/size */
        #form-add-color-so .form-group label {
            font-size: 12px;
            font-weight: 600;
            color: #4a5568;
            margin-bottom: 4px;
        }

        #form-add-color-so .select2-container .select2-selection--multiple,
        #form-add-color-so input[type="number"] {
            border-radius: 8px;
            border: 1px solid #e1e5ea;
        }

        #form-add-color-so .btn-primary {
            border-radius: 8px;
            font-weight: 600;
            background: linear-gradient(135deg, #2a5298 0%, #1e3c72 100%);
            border: none;
            padding: .55rem 0;
        }

        .divider-hint {
            font-size: 11px;
            color: #a0aab8;
            margin-top: .75rem;
            padding-top: .75rem;
            border-top: 1px dashed #e6e9ee;
        }

        #modal-detail .modal-footer {
            background: #fff;
            border-top: 1px solid #eef1f5;
            padding: .9rem 1.5rem;
        }

        #modal-detail .modal-footer .btn {
            border-radius: 8px;
            font-weight: 600;
            padding: .5rem 1.1rem;
        }
    </style>

    <style>
        .preview-upload-card {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,.08);
            margin-bottom: 1.25rem;
        }

        .preview-upload-header {
            background: #0d1b3e;
            color: #fff;
            padding: .75rem 1.1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
            user-select: none;
        }

        .preview-upload-header h6 {
            margin: 0;
            font-weight: 700;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .preview-upload-header .toggle-icon {
            transition: transform .2s ease;
        }

        .preview-upload-header.collapsed .toggle-icon {
            transform: rotate(-90deg);
        }

        .preview-upload-body {
            background: #fff;
            max-height: 65vh;
            overflow: auto;
            transition: max-height .25s ease;
        }

        .preview-upload-body.collapsed {
            max-height: 0;
            overflow: hidden;
        }

        /* Table */
        #table-preview-so-det {
            margin: 0;
            font-size: 12px;
            white-space: nowrap;
        }

        #table-preview-so-det thead th {
            background: #0d1b3e;
            color: #fff;
            text-align: center;
            vertical-align: middle;
            border-color: #1e3c72;
            position: sticky;
            top: 0;
            z-index: 2;
        }

        /* Kolom-kolom identitas (Style s/d Color) dibuat sticky ke kiri supaya
        saat scroll horizontal ke kanan (lihat Size), tetap kelihatan */
        #table-preview-so-det thead th.sticky-col,
        #table-preview-so-det tbody td.sticky-col {
            position: sticky;
            left: 0;
            background: #fff;
            z-index: 1;
        }

        #table-preview-so-det thead th.sticky-col {
            background: #0d1b3e;
            z-index: 3;
        }

        /* Kolom Size di-highlight beda supaya kebaca sebagai "grup size" */
        #table-preview-so-det th.col-size,
        #table-preview-so-det td.col-size {
            background: #0d1b3e;
            text-align: center;
            min-width: 55px;
        }

        #table-preview-so-det td.col-size.has-qty {
            background: #d7e6ff;
            font-weight: 700;
            color: #0d1b3e;
        }

        #table-preview-so-det td.col-total {
            background: #0d1b3e;
            color: #fff;
            font-weight: 700;
            text-align: center;
        }

        #table-preview-so-det tbody tr:hover td:not(.col-size):not(.col-total) {
            background: #f8fafd;
        }
    </style>


@endsection

@php
    $master_colors = DB::connection('mysql_sb')->table('master_colors_gmt')->orderBy('name')->get();
    $master_sizes = DB::connection('mysql_sb')->table('master_size_new')->orderBy('urutan')->get();
@endphp

@section('content')
<div class="card card-sb">
    <div class="card-header">
        <h5 class="card-title fw-bold mb-0"><i class="fas fa-list"></i> List Data Sales Order</h5>
    </div>
    <div class="card-body">
        <div class="mb-3">
            <a href="{{ route('create-so') }}" class="btn btn-outline-primary">
                <i class="fas fa-plus"></i> Create SO
            </a>
        </div>

        <div class="row align-items-end mb-4">
            <div class="col-md-2">
                <label class="small fw-bold">Tgl Awal</label>
                <input type="date" id="date_from" class="form-control form-control-sm" value="{{ date('Y-m-d') }}">
            </div>
            <div class="col-md-2">
                <label class="small fw-bold">Tgl Akhir</label>
                <input type="date" id="date_to" class="form-control form-control-sm" value="{{ date('Y-m-d') }}">
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary btn-sm" onclick="refreshTable()">
                    <i class="fas fa-search"></i> Filter
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover w-100" id="table-so">
                <thead>
                    <tr class="text-center">
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>No SO</th>
                        <th>No PO</th>
                        <th>WS</th>
                        <th>Style</th>
                        <th>Buyer</th>
                        <th>Market</th>
                        <th>Product</th>
                        <th>Item Name</th>
                        <th>Qty</th>
                        <th width="15%">Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>
<div class="modal fade" id="modal-detail" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header text-white">
                <h5 class="modal-title"><i class="fas fa-info-circle"></i> Detail Sales Order</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">

                {{-- Info Card --}}
                <div class="so-info-card">
                    <div class="row g-2">
                        <div class="col-6 col-md-2 mb-2 mb-md-0">
                            <div class="so-info-item">
                                <span class="so-info-label"><i class="fas fa-hashtag"></i> No SO</span>
                                <span class="so-info-value" id="so_no" title="-">-</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-2 mb-2 mb-md-0">
                            <div class="so-info-item">
                                <span class="so-info-label"><i class="fas fa-tag"></i> WS</span>
                                <span class="so-info-value" id="kpno" title="-">-</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3 mb-2 mb-md-0">
                            <div class="so-info-item">
                                <span class="so-info-label"><i class="fas fa-tshirt"></i> Style</span>
                                <input type="text" id="style_input_header" class="form-control form-control-sm font-weight-bold text-dark px-2 py-1" style="font-size: 14px; background-color: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 6px;" placeholder="Nama Style...">
                            </div>
                        </div>
                        <div class="col-6 col-md-3 mb-2 mb-md-0">
                            <div class="so-info-item">
                                <span class="so-info-label"><i class="fas fa-building"></i> Buyer</span>
                                <span class="so-info-value" id="buyer" title="-">-</span>
                            </div>
                        </div>
                        <div class="col-12 col-md-2">
                            <div class="so-info-item">
                                <span class="so-info-label"><i class="fas fa-map-marker-alt"></i> Market</span>
                                <span class="so-info-value" id="market" title="-">-</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Section Cards --}}
                <div class="row">
                    <div class="col-md-7 mb-3 mb-md-0">
                        <div class="section-card">
                            <div class="section-card-header section-toggle" data-target="#body-list-qty">
                                <h6><i class="fas fa-list"></i> List Detail Qty</h6>
                                <div class="section-card-header-right">
                                    <span class="badge-total-qty" id="total_qty_badge">
                                        <i class="fas fa-boxes"></i> Total: <span id="total_qty_value">0</span>
                                    </span>
                                    <i class="fas fa-chevron-down section-toggle-icon"></i>
                                </div>
                            </div>
                            <div class="section-card-body" id="body-list-qty">
                                <div class="table-responsive table-scroll-modal">
                                    <table class="table table-bordered table-sm w-100" id="table-detail-so">
                                        <thead class="text-center">
                                            <tr>
                                                <th>Color</th>
                                                <th>Market</th>
                                                <th width="20%">Size</th>
                                                <th width="20%">Qty</th>
                                                <th width="15%">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="dtl_table_body">
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-5">
                        <div class="section-card section-add">
                            <div class="section-card-header section-toggle" data-target="#body-add-warna">
                                <h6><i class="fas fa-plus-circle"></i> Tambah Warna / Size</h6>
                                <div class="section-card-header-right">
                                    <i class="fas fa-chevron-down section-toggle-icon"></i>
                                </div>
                            </div>
                            <div class="section-card-body" id="body-add-warna">
                                <form id="form-add-color-so">
                                    <div class="form-group mb-3">
                                        <label>Warna</label>
                                        <select class="form-control select2-modal" id="add_id_color" multiple="multiple" data-placeholder="- Pilih Warna -" style="width: 100%;">
                                            @foreach($master_colors as $color)
                                                <option value="{{ $color->id }}">{{ $color->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group mb-3">
                                        <label>Size</label>
                                        <select class="form-control select2-modal" id="add_id_size" multiple="multiple" data-placeholder="- Pilih Size -" style="width: 100%;">
                                            @foreach($master_sizes as $size)
                                                <option value="{{ $size->id }}">{{ $size->size }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group mb-3">
                                        <label>Qty (Default)</label>
                                        <input type="number" id="add_qty" class="form-control" value="0" min="0">
                                    </div>

                                    <button type="button" class="btn btn-primary w-100" onclick="addSoDetailRow()">
                                        <i class="fas fa-plus"></i> Tambah Data
                                    </button>

                                    <div class="divider-hint">
                                        <i class="fas fa-info-circle"></i>
                                        Bisa pilih lebih dari 1 warna & size sekaligus, kombinasi akan otomatis dibuat.
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="preview-upload-card mt-5">
                    <div class="preview-upload-header" id="preview-upload-toggle">
                        <h6><i class="fas fa-table"></i>SO Detail</h6>
                        <i class="fas fa-chevron-down toggle-icon"></i>
                    </div>
                    <div class="preview-upload-body" id="preview-upload-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="table-preview-so-det">
                                <thead>
                                    <tr id="preview-upload-thead-row">
                                        <th class="sticky-col">Style</th>
                                        <th class="sticky-col">Desc</th>
                                        <th class="sticky-col">PO</th>
                                        <th class="sticky-col">Market</th>
                                        <th class="sticky-col">Ex Fty</th>
                                        <th class="sticky-col">Color</th>
                                        {{-- kolom size akan di-generate otomatis oleh JS di sini --}}
                                        <th class="col-total">Total Qty</th>
                                    </tr>
                                </thead>
                                <tbody id="preview-upload-tbody">
                                    {{-- rows di-render via JS --}}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success btn-sm" onclick="saveAllQtySO()"><i class="fas fa-save"></i> Edit All Qty</button>
                <button type="button" class="btn btn-outline-danger btn-sm" data-bs-dismiss="modal"><i class="fas fa-times-circle"></i> Tutup</button>
            </div>
        </div>
    </div>
</div>

{{-- <div class="modal fade" id="modal-detail" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-sb text-white">
                <h5 class="modal-title"><i class="fas fa-info-circle"></i> Detail Sales Order</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="row mb-2">
                    <div class="col-md-4">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <th width="30%">No SO</th>
                                <td>: <span id="so_no">-</span></td>
                            </tr>
                            <tr>
                                <th>WS</th>
                                <td>: <span id="kpno">-</span></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-8">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <th>Style</th>
                                <td>: <span id="style">-</span></td>
                            </tr>
                            <tr>
                                <th width="30%">Buyer</th>
                                <td>: <span id="buyer">-</span></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-7" style="border-right: 1px solid #dee2e6;">
                        <h6 class="font-weight-bold mb-2"><i class="fas fa-list"></i> List Detail Qty</h6>
                        <div class="table-responsive table-scroll-modal">
                            <table class="table table-bordered table-sm w-100" id="table-detail-so">
                                <thead class="bg-light text-center">
                                    <tr>
                                        <th>Color</th>
                                        <th width="20%">Size</th>
                                        <th width="20%">Qty</th>
                                        <th width="15%">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="dtl_table_body">
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="col-md-5">
                        <form id="form-add-color-so">
                            <h6 class="font-weight-bold mb-2"><i class="fas fa-plus"></i> Tambah Warna / Size</h6>

                            <div class="form-group mb-3">
                                <label>Warna</label>
                                <select class="form-control select2-modal" id="add_id_color" multiple="multiple" data-placeholder="- Pilih Warna -" style="width: 100%;">
                                    @foreach($master_colors as $color)
                                        <option value="{{ $color->id }}">{{ $color->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group mb-3">
                                <label>Size</label>
                                <select class="form-control select2-modal" id="add_id_size" multiple="multiple" data-placeholder="- Pilih Size -" style="width: 100%;">
                                    @foreach($master_sizes as $size)
                                        <option value="{{ $size->id }}">{{ $size->size }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Qty (Default)</label>
                                        <input type="number" id="add_qty" class="form-control" value="0" min="0">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>&nbsp;</label><br>
                                        <button type="button" class="btn btn-primary w-100" onclick="addSoDetailRow()"><i class="fas fa-plus"></i> Tambah Data</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success btn-sm" onclick="saveAllQtySO()"><i class="fas fa-save"></i> Edit All Qty</button>
                <button type="button" class="btn btn-outline-danger btn-sm" data-bs-dismiss="modal"><i class="fas fa-times-circle"></i> Tutup</button>
            </div>
        </div>
    </div>
</div> --}}
<div class="modal fade" id="modal-detail-material" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title fw-bold"><i class="fas fa-boxes"></i> Detail Material BOM</h5>
                <button type="button" class="close text-dark btn-close-modal" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="table-responsive table-scroll-modal">
                    <table class="table table-bordered table-sm w-100 text-nowrap" id="table-material-so" style="font-size: 11px;">
                        <thead class="bg-light text-center">
                            <tr>
                                <th>No</th>
                                <th>ID Item</th>
                                <th>ID Contents</th>
                                <th>Panel</th>
                                <th>Dest</th>
                                <th>Product Set</th>
                                <th>Color Gmt</th>
                                <th>Size Gmt</th>
                                <th>Item</th>
                                <th>Qty Gmt</th>
                                <th>Cons</th>
                                <th>Qty BOM</th>
                                <th>Unit</th>
                                <th>Notes</th>
                                <th>Created By</th>
                                <th>Rule BOM</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm btn-close-modal" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Merge SO --}}
<div class="modal fade" id="modal-merge-so" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title"><i class="fas fa-code-branch"></i> Merge / Pindah Detail SO</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                {{-- Info SO Sumber --}}
                <div class="alert alert-info py-2 mb-3">
                    <strong><i class="fas fa-arrow-right"></i> SO Sumber:</strong>
                    <span id="merge-src-info">-</span>
                </div>

                {{-- Pilih SO Tujuan --}}
                <div class="form-group row align-items-center mb-3">
                    <label class="col-md-2 col-form-label font-weight-bold">SO Tujuan:</label>
                    <div class="col-md-6">
                        <select id="merge-dst-select" class="form-control select2bs4">
                            <option value="">-- Pilih SO Tujuan --</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted" id="merge-dst-info"></small>
                    </div>
                </div>

                {{-- Opsi No PO setelah Merge --}}
                <div class="form-group row align-items-start mb-3">
                    <label class="col-md-2 col-form-label font-weight-bold">No PO Hasil:</label>
                    <div class="col-md-10">
                        <div class="custom-control custom-radio mb-1">
                            <input type="radio" id="po_opt_combine" name="po_merge_option" value="combine" class="custom-control-input" checked>
                            <label class="custom-control-label" for="po_opt_combine">
                                Gabungkan <span class="text-muted small">(misal: <i>PO-001, PO-002</i>)</span>
                            </label>
                        </div>
                        <div class="custom-control custom-radio mb-1">
                            <input type="radio" id="po_opt_dst" name="po_merge_option" value="use_dst" class="custom-control-input">
                            <label class="custom-control-label" for="po_opt_dst">
                                Pakai No PO Tujuan saja <span class="badge badge-info" id="badge-po-dst">-</span>
                            </label>
                        </div>
                        <div class="custom-control custom-radio mb-1">
                            <input type="radio" id="po_opt_manual" name="po_merge_option" value="manual" class="custom-control-input">
                            <label class="custom-control-label" for="po_opt_manual">
                                Ketik Manual:
                                <input type="text" id="po_manual_input" class="form-control form-control-sm d-inline-block ml-1" style="width:220px;" placeholder="Tulis No PO..." disabled>
                            </label>
                        </div>
                    </div>
                </div>
                {{-- Tabel Detail SO Sumber --}}
                <div class="table-responsive" style="max-height: 55vh; overflow-y:auto;">
                    <table class="table table-bordered table-sm table-hover" id="table-merge-detail" style="font-size:12px;">
                        <thead class="bg-light text-center">
                            <tr>
                                <th width="3%">
                                    <input type="checkbox" id="check-all-merge" title="Pilih Semua">
                                </th>
                                <th>No</th>
                                <th>Color</th>
                                <th>Size</th>
                                <th>Product Set</th>
                                <th>Style</th>
                                <th>Qty</th>
                                <th>Ex FTY</th>
                                <th>Dest</th>
                            </tr>
                        </thead>
                        <tbody id="merge-detail-body">
                            <tr><td colspan="9" class="text-center text-muted">Loading...</td></tr>
                        </tbody>
                    </table>
                </div>
                <div class="mt-2">
                    <span class="badge badge-primary" id="merge-selected-count">0 baris dipilih</span>
                    <span class="ml-3 text-muted small">Centang baris yang ingin dipindahkan ke SO Tujuan.</span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                    <i class="fas fa-times"></i> Batal
                </button>
                <button type="button" class="btn btn-danger btn-sm" id="btn-execute-merge" onclick="executeMerge()">
                    <i class="fas fa-code-branch"></i> Pindahkan Detail
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('custom-script')
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>

    <script>

        $('.btn-close-modal').on('click', function() {
            $('#modal-detail-material').modal('hide');
        });

        $('#modal-detail-material').on('hidden.bs.modal', function () {
            $('body').removeClass('modal-open');
            $('.modal-backdrop').remove();
        });

        $(".close").click(function(){
            $("#modal-detail").modal("hide");
        });

        let table;
        $(document).ready(function() {
            table = $('#table-so').DataTable({
                processing: true,
                serverSide: false,
                ajax: {
                    url: "{{ route('master-marketing-so') }}",
                    data: function (d) {
                        d.date_from = $('#date_from').val();
                        d.date_to = $('#date_to').val();
                    }
                },
                lengthMenu: [[5, 10, 25, -1], [5, 10, 25, "All"]],
                pageLength: -1,
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'd_insert', name: 'so.d_insert', className: 'text-center' },
                    { data: 'so_no', name: 'so.so_no' },
                    { data: 'no_po', name: 'so.no_po' },
                    { data: 'kpno', name: 'act.kpno' },
                    { data: 'style', name: 'so.style' },
                    { data: 'buyer', name: 'ms.Supplier' },
                    { data: 'market', name: 'so.market' },
                    { data: 'product_group', name: 'mp.product_group' },
                    { data: 'product_item', name: 'mp.product_item' },
                    { data: 'qty', name: 'so.qty', className: 'text-right' },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
                ]
            });

            $('#modal-detail').on('shown.bs.modal', function () {
                if (typeof $.fn.select2 !== 'undefined') {
                    $('.select2-modal').select2({
                        dropdownParent: $('#modal-detail'),
                        theme: 'bootstrap4'
                    });
                }
            });
        });

        function refreshTable() {
            table.ajax.reload();
        }


        let tableDetail;
        let current_so_id = null;

        function showDetail(id) {
            current_so_id = id;
            $('#modal-detail').modal('show');

            if ($.fn.DataTable.isDataTable('#table-detail-so')) {
                $('#table-detail-so').DataTable().destroy();
            }

            $('#table-detail-so tbody').html('<tr><td colspan="4" class="text-center">Loading...</td></tr>');

            let url = "{{ route('get-detail-so', ':id') }}";
            url = url.replace(':id', id);

            $.ajax({
                url: url,
                type: 'GET',
                dataType: 'json'
            })
            .done((res) => {
                const { so_no, kpno, buyer, style, market } = res.header || {};
                $('#so_no').text(so_no || '-');
                $('#kpno').text(kpno || '-');
                $('#buyer').text(buyer || '-');
                // $('#style').text(style || '-');
                $('#style_input_header').val(style || '');
                $('#market').text(market || '-');

                let sortedDetails = res.details.sort((a, b) => {
                    let urutanA = parseInt(a.size_urutan) || 999;
                    let urutanB = parseInt(b.size_urutan) || 999;
                    return urutanA - urutanB;
                });

                let rows = '';
                sortedDetails.forEach(item => {
                    let detail_id = item.id;

                    let isCanceled = (item.cancel === 'Y');

                    let readonly = isCanceled ? 'readonly' : '';
                    let bgClass = isCanceled ? 'bg-light text-muted' : '';

                    let actionBtn = isCanceled
                        ? `<button type="button" class="btn btn-sm btn-info" onclick="toggleCancelRestoreSO(${detail_id}, 'restore')"><i class="fas fa-undo"></i> Restore</button>`
                        : `<button type="button" class="btn btn-sm btn-danger" onclick="toggleCancelRestoreSO(${detail_id}, 'cancel')"><i class="fas fa-times"></i> Cancel</button>`;

                    rows += `<tr class="${bgClass}">
                        <td class="align-middle">${item.color || '-'}</td>
                        <td class="align-middle">${item.dest || '-'}</td>
                        <td class="text-center align-middle">${item.size || '-'}</td>
                        <td>
                            <input type="number" id="qty_input_${detail_id}" data-id="${detail_id}" class="form-control form-control-sm text-right qty-input" value="${item.qty}" ${readonly}>
                        </td>
                        <td class="text-center align-middle">
                            ${actionBtn}
                        </td>
                    </tr>`;
                });

                $('#table-detail-so tbody').html(rows);

                tableDetail = $('#table-detail-so').DataTable({
                    "paging": true,
                    "ordering": true,
                    "info": true,
                    "searching": true,
                    "lengthMenu": [[5, 10, 25, -1], [5, 10, 25, "All"]],
                    "pageLength": -1
                });

                let totalQty = res.details
                    .filter(i => i.cancel !== 'Y')
                    .reduce((sum, i) => sum + (parseInt(i.qty) || 0), 0);
                $('#total_qty_value').text(totalQty.toLocaleString());

                let previewRows = res.details
                    .filter(item => item.cancel !== 'Y')
                    .map(item => ({
                        style : item.styleno_prod || '-',
                        desc  : buyer || '-',
                        po    : so_no || '-',
                        market: item.dest || '-',
                        ex_fty: item.deldate || '-',
                        color : item.color || '-',
                        size  : item.size || '-',
                        qty   : item.qty || 0,
                        urutan: parseInt(item.size_urutan) || 999,
                    }));

                renderPreviewUpload(previewRows);

            })
            .fail((xhr) => {
                console.error(xhr.responseText);
                Swal.fire('Error', 'Gagal memuat data detail.', 'error');
            });
        }

        function toggleCancelRestoreSO(detail_id, action) {
            let titleText = action === 'cancel' ? 'Batalkan Item ini?' : 'Kembalikan Item ini?';

            Swal.fire({
                title: titleText,
                text: "Total Qty di SO akan dihitung ulang otomatis.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: (action === 'cancel' ? '#d33' : '#17a2b8'),
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Proses!'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({ title: 'Memproses...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });

                    $.ajax({
                        url: "{{ route('cancel-restore-so') }}",
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            id: detail_id,
                            action: action
                        },
                        success: function(res) {
                            if (res.status == 200) {
                                Swal.fire({ icon: 'success', title: 'Berhasil!', text: res.message, timer: 1500, showConfirmButton: false });

                                showDetail(current_so_id);
                                if(table) { table.ajax.reload(null, false); }
                            } else {
                                Swal.fire('Gagal!', res.message, 'error');
                            }
                        },
                        error: function(err) {
                            Swal.fire('Error!', 'Terjadi kesalahan sistem.', 'error');
                        }
                    });
                }
            });
        }

        function addSoDetailRow() {
            let id_color = $('#add_id_color').val();
            let id_size = $('#add_id_size').val();
            let qty = $('#add_qty').val();

            if (!id_color || id_color.length === 0 || !id_size || id_size.length === 0 || !qty || qty <= 0) {
                Swal.fire('Peringatan!', 'Harap pilih minimal 1 Warna, 1 Size, dan isi Qty dengan benar.', 'warning');
                return;
            }

            Swal.fire({ title: 'Menambahkan...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });

            $.ajax({
                url: "{{ route('add-so-detail') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    id_so: current_so_id,
                    id_color: id_color,
                    id_size: id_size,
                    qty: qty
                },
                success: function(res) {
                    if (res.status == 200) {
                        Swal.fire({ icon: 'success', title: 'Berhasil!', text: res.message, timer: 1500, showConfirmButton: false });

                        $('#add_id_color').val('').trigger('change');
                        $('#add_id_size').val('').trigger('change');
                        $('#add_qty').val(0);

                        showDetail(current_so_id);

                        if(table) { table.ajax.reload(null, false); }
                    } else {
                        Swal.fire('Gagal!', res.message, 'error');
                    }
                },
                error: function(xhr) {
                    console.log(xhr.responseText);
                    Swal.fire('Error!', 'Terjadi kesalahan sistem.', 'error');
                }
            });
        }

        function saveAllQtySO() {
            let dataToSave = [];
            let isValid = true;

            let updatedStyle = $('#style_input_header').val();

            $('.qty-input').each(function() {
                let id = $(this).data('id');
                let qty = $(this).val();

                if (qty === '' || qty <= 0) {
                    isValid = false;
                }

                dataToSave.push({
                    id: id,
                    qty: qty
                });
            });

            if (!isValid) {
                Swal.fire('Peringatan!', 'Terdapat Qty yang kosong atau 0. Harap periksa kembali.', 'warning');
                return;
            }

            if (dataToSave.length === 0) {
                Swal.fire('Peringatan!', 'Tidak ada data untuk disimpan.', 'warning');
                return;
            }

            Swal.fire({ title: 'Menyimpan...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });

            $.ajax({
                url: "{{ route('update-qty-so') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    id_so: current_so_id,
                    style: updatedStyle,
                    data: dataToSave
                },
                success: function(res) {
                    if (res.status == 200) {
                        Swal.fire({ icon: 'success', title: 'Berhasil!', text: res.message, timer: 1500, showConfirmButton: false });

                        $("#modal-detail").modal("hide");
                        if(table) { table.ajax.reload(null, false); }
                    } else {
                        Swal.fire('Gagal!', res.message, 'error');
                    }
                },
                error: function(err) {
                    Swal.fire('Error!', 'Terjadi kesalahan saat menyimpan data.', 'error');
                }
            });
        }

        let tableMaterial;

        function showDetailMaterial(id) {
            $('#modal-detail-material').modal('show');

            if ($.fn.DataTable.isDataTable('#table-material-so')) {
                $('#table-material-so').DataTable().destroy();
            }

            $('#table-material-so tbody').html('<tr><td colspan="16" class="text-center">Loading Data Material...</td></tr>');

            let url = "{{ route('get-detail-material-so', ':id') }}";
            url = url.replace(':id', id);

            $.ajax({
                url: url,
                type: 'GET',
                dataType: 'json'
            })
            .done((res) => {
                let rows = '';
                res.data.forEach((item, index) => {
                    rows += `<tr>
                        <td class="text-center">${String(index + 1).padStart(3, '0')}</td>
                        <td>${item.id_item || '-'}</td>
                        <td>${item.id_contents || '-'}</td>
                        <td>${item.panel || '-'}</td>
                        <td>${item.dest || '-'}</td>
                        <td>${item.product_set || '-'}</td>
                        <td>${item.color_gmt || '-'}</td>
                        <td>${item.size_gmt || '-'}</td>
                        <td style="max-width: 300px; overflow: hidden; text-overflow: ellipsis;">${item.item_desc || '-'}</td>
                        <td class="text-right">${parseFloat(item.qty_gmt).toFixed(2) || 0}</td>
                        <td class="text-right">${parseFloat(item.cons).toFixed(2) || 0}</td>
                        <td class="text-right">${parseFloat(item.qty_bom).toFixed(2)}</td>
                        <td>${item.unit || '-'}</td>
                        <td>${item.notes || '-'}</td>
                        <td>${item.created_by || '-'}</td>
                        <td>${item.rule_bom || '-'}</td>
                        <td class="text-center">${item.status || '-'}</td>
                    </tr>`;
                });

                $('#table-material-so tbody').html(rows);

                tableMaterial = $('#table-material-so').DataTable({
                    "paging": true,
                    "info": true,
                    "searching": true,
                    "lengthMenu": [
                        [5, 10, 25, -1],
                        [5, 10, 25, "All"]
                    ],
                    "pageLength": -1,
                    "columnDefs": [
                        { "className": "align-middle", "targets": "_all" }
                    ]
                });
            })
            .fail((xhr) => {
                Swal.fire('Error', 'Gagal memuat detail material', 'error');
            });
        }
        function syncBom(id) {
            Swal.fire({
                title: 'Sync BOM?',
                text: "Sistem akan mengecek dan menarik material terbaru dari Master BOM ke SO ini tanpa menghapus data yang sudah ada.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#007bff',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Sync!'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({ title: 'Memproses Sync...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });

                    let url = "{{ route('so-sync-bom', ':id') }}";
                    url = url.replace(':id', id);

                    $.ajax({
                        url: url,
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(res) {
                            if (res.status == 200) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Sync Berhasil!',
                                    html: `Material Baru: <b>${res.inserted}</b> baris<br>Diupdate: <b>${res.updated}</b> baris<br>Dibatalkan (Cancel): <b>${res.canceled}</b> baris`,
                                });
                                if(table) { table.ajax.reload(null, false); }
                            } else {
                                Swal.fire('Gagal!', res.message, 'error');
                            }
                        },
                        error: function(err) {
                            Swal.fire('Error!', 'Terjadi kesalahan saat proses Sync.', 'error');
                        }
                    });
                }
            });
        }

        // =============================================
        // MERGE SO JAVASCRIPT
        // =============================================

        let merge_so_src_id = null;

        function openMergeModal(id) {
            merge_so_src_id = id;
            $('#merge-detail-body').html('<tr><td colspan="9" class="text-center">Loading...</td></tr>');
            $('#merge-src-info').text('Memuat...');
            $('#merge-dst-select').html('<option value="">-- Pilih SO Tujuan --</option>');
            $('#merge-dst-info').text('');
            $('#merge-selected-count').text('0 baris dipilih');

            let url = "{{ route('so-merge-candidates', ':id') }}".replace(':id', id);

            $.getJSON(url, function(res) {
                if (res.status !== 200) {
                    Swal.fire('Gagal', res.message || 'Error', 'error');
                    return;
                }

                $('#merge-src-info').text(res.source.so_no + ' | PO: ' + (res.source.no_po || '-'));

                if (res.candidates.length === 0) {
                    $('#merge-dst-select').html('<option value="">-- Tidak ada SO dengan BOM yang sama --</option>');
                } else {
                    let opts = '<option value="">-- Pilih SO Tujuan --</option>';
                    res.candidates.forEach(function(c) {
                        opts += `<option value="${c.id}" data-kpno="${c.kpno}" data-po="${c.no_po}" data-qty="${c.qty}">${c.so_no} | WS: ${c.kpno} | PO: ${c.no_po || '-'} | Qty: ${Number(c.qty).toLocaleString()}</option>`;
                    });
                    $('#merge-dst-select').html(opts);
                }

                if (typeof $.fn.select2 !== 'undefined') {
                    $('#merge-dst-select').select2({ dropdownParent: $('#modal-merge-so') });
                }

                loadMergeDetail(id);
            }).fail(function() {
                Swal.fire('Error', 'Gagal memuat data kandidat SO', 'error');
            });

            $('#merge-dst-select').on('change', function() {
                let opt = $(this).find(':selected');
                if ($(this).val()) {
                    $('#merge-dst-info').html(`<b>WS:</b> ${opt.data('kpno')} | <b>Qty saat ini:</b> ${Number(opt.data('qty')).toLocaleString()}`);
                    // Update badge no PO tujuan
                    let poDst = opt.data('po') || '-';
                    $('#badge-po-dst').text(poDst);
                } else {
                    $('#merge-dst-info').text('');
                    $('#badge-po-dst').text('-');
                }
            });

            // Enable/disable input manual
            $(document).off('change', 'input[name=po_merge_option]').on('change', 'input[name=po_merge_option]', function() {
                if ($(this).val() === 'manual') {
                    $('#po_manual_input').prop('disabled', false).focus();
                } else {
                    $('#po_manual_input').prop('disabled', true);
                }
            });

            $('#modal-merge-so').modal('show');
        }

        function loadMergeDetail(id) {
            let url = "{{ route('so-merge-source-detail', ':id') }}".replace(':id', id);
            $.getJSON(url, function(res) {
                if (res.status !== 200) return;
                let rows = '';
                res.data.forEach(function(d, i) {
                    rows += `
                        <tr>
                            <td class="text-center">
                                <input type="checkbox" class="merge-check" value="${d.id}">
                            </td>
                            <td class="text-center">${i + 1}</td>
                            <td>${d.color || '-'}</td>
                            <td>${d.size || '-'}</td>
                            <td>${d.product_set || '-'}</td>
                            <td>${d.styleno_prod || '-'}</td>
                            <td class="text-right">${Number(d.qty).toLocaleString()}</td>
                            <td class="text-center">${d.deldate_det || '-'}</td>
                            <td>${d.dest || '-'}</td>
                        </tr>`;
                });
                $('#merge-detail-body').html(rows || '<tr><td colspan="9" class="text-center text-muted">Tidak ada data</td></tr>');

                // Event: update counter
                $(document).on('change', '.merge-check', function() {
                    let count = $('.merge-check:checked').length;
                    $('#merge-selected-count').text(count + ' baris dipilih');
                });
            });
        }

        // Check All
        $('#check-all-merge').on('change', function() {
            $('.merge-check').prop('checked', $(this).is(':checked'));
            let count = $('.merge-check:checked').length;
            $('#merge-selected-count').text(count + ' baris dipilih');
        });

        function executeMerge() {
            let id_so_dst = $('#merge-dst-select').val();
            let det_ids   = [];
            $('.merge-check:checked').each(function() {
                det_ids.push($(this).val());
            });

            if (!id_so_dst) {
                Swal.fire('Perhatian', 'Silakan pilih SO Tujuan terlebih dahulu.', 'warning');
                return;
            }
            if (det_ids.length === 0) {
                Swal.fire('Perhatian', 'Centang minimal 1 baris detail yang akan dipindahkan.', 'warning');
                return;
            }

            Swal.fire({
                title: 'Konfirmasi Merge',
                html: `Anda akan memindahkan <b>${det_ids.length} baris</b> dari SO Sumber ke SO Tujuan.<br><small class="text-muted">Jika SO Sumber kosong setelah dipindah, SO tersebut otomatis di-void.</small>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Pindahkan!',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#dc3545'
            }).then(function(result) {
                if (result.isConfirmed) {
                    Swal.fire({ title: 'Memproses...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

                    let po_merge_option = $('input[name=po_merge_option]:checked').val();
                    let po_manual       = $('#po_manual_input').val();

                    $.ajax({
                        url: "{{ route('so-execute-merge') }}",
                        type: 'POST',
                        data: {
                            _token          : "{{ csrf_token() }}",
                            id_so_src       : merge_so_src_id,
                            id_so_dst       : id_so_dst,
                            det_ids         : det_ids,
                            po_merge_option : po_merge_option,
                            po_manual       : po_manual
                        },
                        success: function(res) {
                            if (res.status === 200) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    html: res.message + `<br><small>Qty SO Sumber: <b>${Number(res.qty_src || 0).toLocaleString()}</b> | Qty SO Tujuan: <b>${Number(res.qty_dst || 0).toLocaleString()}</b></small>`
                                }).then(function() {
                                    $('#modal-merge-so').modal('hide');
                                    table.ajax.reload(null, false);
                                });
                            } else {
                                Swal.fire('Gagal', res.message, 'error');
                            }
                        },
                        error: function(xhr) {
                            let msg = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan server';
                            Swal.fire('Error!', msg, 'error');
                        }
                    });
                }
            });
        }

        function renderPreviewUpload(rows) {
            $('#preview-upload-thead-row th.col-size').remove();

            if (!rows || rows.length === 0) {
                $('#preview-upload-tbody').html('<tr><td colspan="7" class="text-center text-muted py-3">Tidak ada data</td></tr>');
                return;
            }

            let sizeMap = new Map();
            rows.forEach(r => {
                if (!sizeMap.has(r.size)) {
                    sizeMap.set(r.size, r.urutan !== undefined ? r.urutan : 999);
                }
            });

            let sizeList = Array.from(sizeMap.keys()).sort((a, b) => {
                return sizeMap.get(a) - sizeMap.get(b);
            });

            let sizeHeaderHtml = sizeList.map(s => `<th class="col-size">${s}</th>`).join('');
            $('#preview-upload-thead-row .col-total').before(sizeHeaderHtml);

            let grouped = {};
            rows.forEach(r => {
                let key = [r.style, r.desc, r.po, r.market, r.color].join('||');
                if (!grouped[key]) {
                    grouped[key] = {
                        style: r.style, desc: r.desc, po: r.po, market: r.market,
                        ex_fty: r.ex_fty, color: r.color, sizes: {}
                    };
                }
                grouped[key].sizes[r.size] = (grouped[key].sizes[r.size] || 0) + (parseInt(r.qty) || 0);
            });

            let bodyHtml = '';
            Object.values(grouped).forEach(g => {
                let total = 0;
                let sizeCells = sizeList.map(s => {
                    let qty = g.sizes[s] || 0;
                    total += qty;
                    let cellClass = qty > 0 ? 'col-size has-qty' : 'col-size text-muted';
                    return `<td class="${cellClass}">${qty > 0 ? qty : '-'}</td>`;
                }).join('');

                bodyHtml += `
                    <tr>
                        <td class="sticky-col">${g.style}</td>
                        <td class="sticky-col">${g.desc}</td>
                        <td class="sticky-col">${g.po}</td>
                        <td class="sticky-col">${g.market}</td>
                        <td class="sticky-col">${g.ex_fty}</td>
                        <td class="sticky-col">${g.color}</td>
                        ${sizeCells}
                        <td class="col-total">${total}</td>
                    </tr>`;
            });

            $('#preview-upload-tbody').html(bodyHtml);
        }


    </script>
    <script>
    $('#preview-upload-toggle').on('click', function() {
        $(this).toggleClass('collapsed');
        $('#preview-upload-body').toggleClass('collapsed');
    });

</script>

<script>
    $(document).on('click', '.section-toggle', function() {
        let $header = $(this);
        let target  = $header.data('target');

        $header.toggleClass('collapsed');
        $(target).toggleClass('collapsed');
    });

    let totalQty = res.details.filter(i => i.cancel !== 'Y').reduce((sum, i) => sum + parseInt(i.qty || 0), 0);
    $('#total_qty_value').text(totalQty.toLocaleString());

</script>


@endsection
