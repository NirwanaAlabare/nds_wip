@extends('layouts.index')

@section('custom-link')
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

    <style>
        :root { --costing-navy: #183f5a; --costing-border: #dce4ec; }
        .costing-create-page { padding: 8px 12px 24px; background: #f5f3f3; min-height: calc(100vh - 56px); font-family: 'Poppins', sans-serif; }
        .costing-crumb { background: var(--costing-navy); color: #b4b4b4; padding: 8px 10px; margin-bottom: 14px; font-size: 16px; font-weight: 500; }
        .costing-crumb a { color: #b4b4b4; text-decoration: none; }
        .costing-crumb a:hover { color: #fff; text-decoration: underline; }
        .costing-crumb b { color: #fff; font-weight: 500; }
        .costing-back { display: inline-flex; align-items: center; gap: 8px; margin: 10px 0; color: var(--costing-navy); font-size: 14px; font-weight: 700; }

        .costing-create-card { border: 0; border-radius: 4px; box-shadow: 0 5px 12px -3px rgba(30, 41, 59, .18), 0 2px 4px rgba(30, 41, 59, .07); background: #fff;}
        .costing-create-card > .card-body { padding: 20px 24px 32px; }

        .costing-page-title { color: #284a63; font-size: 32px; font-weight: 700; margin: 0 0 4px; }
        .costing-page-subtitle { color: #6c757d; font-size: 11px; margin: 0 0 20px; font-weight: 500; }

        .costing-section { margin-bottom: 16px; overflow: hidden; border: 1px solid #284a63; border-radius: 8px; background: #fff; box-shadow: 0 4px 8px -2px rgba(24, 63, 90, .16), 0 1px 2px rgba(24, 63, 90, .06); }
        .costing-section-header { margin: 0; }
        .costing-section-toggle {
            display: flex; justify-content: space-between; align-items: center;
            width: 100%; box-sizing: border-box; padding: 10px 15px;
            border: 0; border-radius: 0;
            background: #f8fafc; color: #284a63;
            text-align: left; font-family: 'Poppins', sans-serif; font-size: 25px; font-weight: 500; appearance: none;
        }
        .costing-section-toggle:focus { outline: 0; box-shadow: 0 0 0 2px rgba(48, 133, 214, .18); }
        .costing-section-toggle .required-mark { color: #dc3545; margin-left: 2px;}

        .section-chevron-box {
            background-color: #a3b2c7; color: white; border-radius: 4px;
            width: 22px; height: 22px; display: flex; align-items: center; justify-content: center; font-size: 11px;
        }
        .section-chevron-box i { transition: transform .2s ease; }
        .costing-section-toggle[aria-expanded="false"] .section-chevron-box i { transform: rotate(180deg); }

        .costing-section-body { padding: 14px 15px 4px; border-top: 1px solid #edf1f5; }

        .costing-fields { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); column-gap: 15px; row-gap: 10px; }
        .costing-fields.five-columns { grid-template-columns: repeat(5, minmax(0, 1fr)); }

        .costing-field { min-width: 0; margin-bottom: 10px; }
        .costing-field label { display: block; min-height: 27px; margin-bottom: 6px; color: #ababab; font-size: 18px; }
        .costing-create-page .costing-field label:not(.form-check-label):not(.custom-file-label) { font-weight: 400; }
        .costing-field .form-control,
        .costing-field .select2-container--bootstrap4 .select2-selection { height: 36px; min-height: 36px; border: 1px solid #e1e5e9; border-radius: 5px; font-size: 12px; color: #495057; font-weight: 600;}
        .costing-field input.form-control::placeholder { color: #adb5bd; font-weight: 500; }
        .costing-field input.form-control { padding: 6px 12px; }

        .costing-field .select2-container { width: 100% !important; }
        .costing-field .select2-container--bootstrap4 .select2-selection--single { padding: 6px 12px; }
        .costing-field .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__rendered { padding: 0 12px; }
        .costing-field .select2-container--bootstrap4 .select2-selection__rendered { padding-left: 0; line-height: 22px; color: #495057; font-weight: 600; }
        .costing-field .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow { top: 4px; right: 5px; }

        .costing-field .form-control:focus { border-color: #8aa8bc; box-shadow: 0 0 0 2px rgba(39, 75, 99, .08); }
        .costing-field .form-control[readonly],
        .costing-field .form-control:disabled { background: #e9ecef; color: #6c757d; border-color: #dee2e6; }

        /* Gambar Upload Section */
        .upload-file-box { border: 1px solid var(--costing-border); padding: 15px; border-radius: 5px; background: #fff; }
        .costing-picture-dropzone {
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            border: 1.5px dashed var(--costing-navy); border-radius: 5px; background: #fff;
            cursor: pointer; text-align: center; padding: 25px 10px; min-height: 120px;
        }
        .costing-picture-dropzone.dragging { background: #eff7ff; }
        .costing-picture-thumbnails {
            border: 1px dashed #6f42c1; border-radius: 5px; padding: 10px;
            min-height: 70px; display: flex; flex-wrap: wrap; gap: 6px;
        }
        .costing-picture-clear { margin-top: 8px; }

        .costing-picture-carousel { position: relative; display: flex; flex-direction: column; align-items: center; justify-content: center; width: 100%; height: 100%; min-height: 250px;}
        .costing-picture-main { max-height: 250px; max-width: 100%; object-fit: contain; }
        .costing-picture-nav {
            width: 30px; height: 30px; padding: 0; border: 0; border-radius: 50%;
            background: #f1f3f5; color: #53687a; box-shadow: 0 2px 4px rgba(0,0,0,0.1); transition: background 0.2s;
        }
        .costing-picture-nav:hover { background: #e2e6ea; }

        .costing-form-actions { padding-top: 20px; text-align: right; }
        .btn-success-custom { background-color: #198754; border-color: #198754; border-radius: 20px; padding: 6px 30px; font-weight: 600; color: #fff;}
        .btn-success-custom:hover { background-color: #157347; }

        @media (max-width: 991px) {
            .costing-fields, .costing-fields.five-columns { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        }
        @media (max-width: 767px) {
            .costing-fields, .costing-fields.five-columns { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (max-width: 480px) {
            .costing-fields, .costing-fields.five-columns { grid-template-columns: 1fr; }
        }

        .select2-container .select2-selection--single .select2-selection__arrow {
            top: 50% !important;
            transform: translateY(-50%) !important;
            height: auto !important;
        }

        /* ================= EDIT PAGE: tambahan di atas style create ================= */
        .costing-no { margin-left: 8px; padding: 2px 10px; border-radius: 12px; background: #eaf0f6; color: var(--costing-navy); font-size: 12px; font-weight: 700; vertical-align: middle; }
        .bg-sb { background: var(--costing-navy) !important; }
        .costing-picture-thumb { position: relative; width: 60px; height: 60px; padding: 0; border: 1px solid #dce4ec; border-radius: 4px; overflow: hidden; background: #fff; cursor: pointer; }
        .costing-picture-thumb.active { border: 2px solid #3085d6; }
        .costing-picture-thumb img { width: 100%; height: 100%; object-fit: cover; }
        .costing-picture-remove { position: absolute; top: 2px; right: 2px; z-index: 1; width: 18px; height: 18px; padding: 0; border: 0; border-radius: 50%; background: rgba(220, 53, 69, .92); color: #fff; font-size: 10px; line-height: 18px; }

        /* ---- tab kategori ---- */
        .costing-category-tabs { display: flex; margin: 30px 0 20px; border-bottom: 1px solid #e3e8ee; overflow-x: auto; }
        .costing-category-tab { flex: 0 0 auto; min-width: max-content; padding: 11px 20px; border: 0; border-bottom: 3px solid transparent; background: transparent; color: #d3d3d3; font-family: 'Poppins', sans-serif; font-size: 25px; font-weight: 600; text-align: center; white-space: nowrap; transition: color .15s ease; }
        .costing-category-tab:hover { color: #6f8799; }
        .costing-category-tab:focus { outline: 0; }
        .costing-category-tab.active { color: #284a63; border-bottom-color: #284a63; }
        .costing-next-wrap { display: flex; justify-content: flex-end; padding: 0 0 24px; }
        .costing-next-tab { min-width: 88px; padding: 8px 20px; border: 0; border-radius: 20px; background: #ff6b00; color: #fff; font-family: 'Poppins', sans-serif; font-size: 13px; font-weight: 500; box-shadow: 0 3px 8px rgba(30, 41, 59, .16); }
        .costing-next-tab:hover:not(:disabled) { background: #e85f00; }
        .costing-next-tab:disabled { cursor: not-allowed; opacity: .55; }

        /* ---- toolbar Add Item ---- */
        .costing-detail-toolbar { display: flex; align-items: flex-end; gap: 12px; margin: 0 0 14px; flex-wrap: wrap; }
        .costing-detail-toolbar .costing-field { width: 300px; max-width: 100%; margin: 0; }
        .btn-add-item { height: 36px; padding: 0 24px; border: 0; border-radius: 20px; background: #198754; color: #fff; font-size: 12px; font-weight: 700; }
        .btn-add-item:hover:not(:disabled) { background: #157347; }
        .btn-add-item:disabled { opacity: .5; }
        .costing-detail-body:has(.costing-category-content.is-all-items) .costing-detail-toolbar { display: none; }
        .xl-bar { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin: 0 0 10px; min-height: 26px; }
        .xl-bar .btn { font-size: 11px; padding: 3px 12px; border-radius: 14px; }

        /* ---- panel per kategori ---- */
        .costing-category-content { margin-top: 4px; }
        .costing-tab-panel { display: none; margin-bottom: 22px; }
        .costing-tab-panel.active,
        .costing-category-content.is-all-items .costing-tab-panel { display: block; }
        .costing-panel-heading { display: none; align-items: center; justify-content: space-between; margin: 0 0 12px; padding: 10px 15px; border: 1px solid #ced4da; border-radius: 6px; background: #fff; color: var(--costing-navy); font-size: 15px; font-weight: 700; cursor: pointer; }
        .costing-category-content.is-all-items .costing-tab-panel > .costing-panel-heading { display: flex; }
        .costing-panel-heading .section-chevron-box i { transition: transform .2s ease; }
        .costing-tab-panel.is-collapsed .costing-panel-heading .section-chevron-box i { transform: rotate(180deg); }
        .costing-tab-panel.is-collapsed .table-responsive,
        .costing-tab-panel.is-collapsed .xl-pager { display: none; }
        .costing-tab-panel .table-responsive { margin: 0 0 8px !important; padding: 10px 12px 8px; overflow-x: auto; border: 1px solid #edf0f5; border-radius: 8px; background: #fff; box-shadow: 0 3px 12px rgba(30, 41, 59, .09); }
        .costing-tab-panel table { margin-bottom: 0; border: 0; background: #fff; }
        .costing-tab-panel table thead th { padding: 8px 8px; border: 0; border-right: 1px solid #3a5f78; background: #274b63 !important; color: #fff !important; font-size: 10px; font-weight: 700; text-align: center; vertical-align: middle; white-space: nowrap; }
        .costing-tab-panel table thead th:first-child { border-top-left-radius: 4px; }
        .costing-tab-panel table thead th:last-child { border-right: 0; border-top-right-radius: 4px; }
        .costing-tab-panel table tbody td { padding: 6px 8px; border: 0; border-bottom: 1px solid #eef1f5; font-size: 11px; font-weight: 600; color: #26323c; vertical-align: middle; }
        .costing-tab-panel table tbody tr:hover > td { background: #fafcff; }
        .xl-th-pen { margin-left: 4px; font-size: 8px; opacity: .85; }
        .costing-tab-panel table tfoot td { padding: 8px 8px; border: 0; font-size: 11px; font-weight: 700; color: #17212b; vertical-align: middle; }
        .costing-tab-panel table tfoot tr { background: #ffd9dc !important; }
        .costing-tab-panel table tfoot tr[style] { background: #ffeced !important; }
        .costing-tab-panel table tfoot tr.bg-warning { background: #ffd9dc !important; }
        .costing-tab-panel[data-panel-category="Other Cost"] table tfoot tr + tr { background: #ffb3b3 !important; }
        #ga_percent { width: 70px !important; height: 24px; padding: 0 6px; font-size: 11px; display: inline-block; }
        .costing-tab-panel .btn-sm { display: inline-flex; width: 22px; height: 22px; align-items: center; justify-content: center; padding: 0 !important; border: 0; border-radius: 50%; font-size: 10px; }
        .costing-tab-panel .btn-info { background: #17b3d4; color: #fff; }
        .costing-tab-panel .btn-danger { background: #e5173f; color: #fff; }

        /* ---- sel inline (Excel-like) ---- */
        .costing-inline-control { width: 100%; min-width: 54px; padding: 3px 5px; border: 1px solid transparent; border-radius: 3px; background: transparent; color: inherit; font-size: inherit; font-weight: inherit; }
        .costing-inline-control:hover, .costing-inline-control:focus { border-color: #9db2c1; background: #fff; outline: none; }
        .costing-inline-control[data-field="set"], .costing-inline-control[data-field="unit"], .costing-inline-control[data-field="supplier"] { min-width: 90px; }
        .costing-inline-control.is-invalid { border-color: #dc3545; }
        .costing-inline-select + .select2-container { width: 100% !important; min-width: 90px; }
        .costing-inline-select + .select2-container .select2-selection--single {
            height: 27px; min-height: 27px; padding: 1px 5px; border: 1px solid transparent;
            background: transparent; font-size: inherit; line-height: 23px;
        }
        .costing-inline-select + .select2-container .select2-selection__rendered { padding-left: 0; line-height: 23px; }
        .costing-inline-select + .select2-container .select2-selection__arrow { height: 25px; }
        .costing-tab-panel table th.costing-desc-column,
        .costing-tab-panel table td.desc-td { width: 280px; min-width: 280px; }

        /* ---- ringkasan (hanya View All Items) ---- */
        .costing-combined-summary { display: none; }
        .costing-category-content.is-all-items .costing-combined-summary { display: block; }
        .costing-summary-wrap { display: flex; justify-content: flex-end; margin-top: 18px; }
        .costing-summary-card { width: min(100%, 520px); overflow: hidden; border-radius: 6px; background: #fff; box-shadow: 0 3px 14px rgba(30, 41, 59, .13); }
        .costing-summary-card table { width: 100%; margin: 0; border: 0; }
        .costing-summary-card td { padding: 9px 10px; border: 0; font-size: 11px; font-weight: 700; color: #17212b; vertical-align: middle; }
        #table-grand-total tr:first-child { background: #274b63 !important; }
        #table-grand-total tr:first-child td { color: #fff; font-size: 10px; }
        #table-grand-total tr:last-child { background: #f1f3f5 !important; }
        #table-cost-summary tr:first-child { background: #274b63 !important; }
        #table-cost-summary tr:first-child td { color: #fff; font-size: 10px; text-align: left; }
        #table-cost-summary tr:nth-child(2) { background: #fff; }
        #table-cost-summary tr:nth-child(3) { background: #fff7d9; }
        #table-cost-summary tr:nth-child(4) { background: #ffe6b3; }
        #table-cost-summary tr:nth-child(5) { background: #f26b6b; }
        #table-cost-summary td { border: 0 !important; }

        /* ---- modal edit detail ---- */
        .modal-content { border: none; border-radius: 14px; overflow: hidden; }
        .modal-header { border-bottom: 1px solid #eef1f5; }
        .modal-footer { border-top: 1px solid #eef1f5; }

        @media (max-width: 575px) {
            .costing-detail-toolbar .costing-field { width: 100%; }
            .costing-create-card > .card-body { padding: 14px 12px 26px; }
        }
        /* ===== Excel-like grid ===== */
        .costing-tab-panel table tbody td { position: relative; }
        .costing-tab-panel table tbody td:has(> .costing-inline-control) { padding: 1px 2px; }
        body.xl-dragging { user-select: none; -webkit-user-select: none; }
        td.xl-sel {
            --xt: 0 0 0 0 transparent; --xb: 0 0 0 0 transparent; --xl: 0 0 0 0 transparent; --xr: 0 0 0 0 transparent;
            background: #e8f2fb !important;
            box-shadow: var(--xt), var(--xb), var(--xl), var(--xr);
        }
        td.xl-sel.xl-top { --xt: inset 0 2px 0 #1a73e8; }
        td.xl-sel.xl-bottom { --xb: inset 0 -2px 0 #1a73e8; }
        td.xl-sel.xl-left { --xl: inset 2px 0 0 #1a73e8; }
        td.xl-sel.xl-right { --xr: inset -2px 0 0 #1a73e8; }
        td.xl-sel .costing-inline-control { background: transparent; }
        td.xl-fill-preview { background: #fff3c4 !important; box-shadow: inset 0 0 0 1px #e0a800; }
        .xl-fill-handle {
            position: absolute; right: -4px; bottom: -4px; z-index: 6;
            width: 8px; height: 8px; background: #1a73e8; border: 1px solid #fff; cursor: crosshair;
        }
        tr.xl-hidden { display: none !important; }
        tr.row-costing.xl-row-selected > td { background: #eef6ff !important; }
        tr.row-costing.xl-row-error > td { background: #fdecea !important; }
        tr.row-costing.xl-saving .row-number::after { content: '\2022'; color: #e0a800; margin-left: 2px; }
        .costing-tab-panel table .xl-check-all,
        .costing-tab-panel table .xl-row-check { cursor: pointer; margin: 0; }
        .costing-tab-panel table td.xl-check-td,
        .costing-tab-panel table th.xl-check-th { width: 28px; text-align: center; }
        .xl-hint { margin-left: auto; color: #8b97a2; font-size: 9px; }
        .xl-pager { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin: -4px 5px 12px; padding: 6px 12px; font-size: 9px; color: #5d6b78; flex-wrap: wrap; }
        .xl-pager select { height: 24px; padding: 0 4px; border: 1px solid #dce4ec; border-radius: 4px; font-size: 9px; background: #fff; }
        .xl-pager .xl-pages { display: inline-flex; align-items: center; gap: 4px; }
        .xl-pager .xl-page-btn { min-width: 22px; height: 22px; padding: 0 5px; border: 0; border-radius: 4px; background: transparent; color: #5d6b78; font-size: 9px; cursor: pointer; }
        .xl-pager .xl-page-btn.active { background: #274b63; color: #fff; font-weight: 700; }
        .xl-pager .xl-page-btn:disabled { opacity: .35; cursor: default; }
        .costing-category-content:not(.is-all-items) .costing-tab-panel table tfoot[id^="tfoot-"] tr:not(:last-child) { display: none; }

        /* font size table costing-item-entry kecilin */
        .costing-tab-panel table td, .costing-tab-panel table th {
            font-size: 12px !important;
        }
    </style>
@endsection

@section('content')
<div class="costing-create-page">
    {{-- <a href="{{ route('master-costing') }}" class="costing-back"><i class="fas fa-chevron-left" style="background:#a3b2c7; color:white; border-radius:4px; padding:3px 5px; font-size:10px;"></i> Back</a> --}}

    <div class="card costing-create-card">
        <div class="card-body">
            <div class="costing-crumb"><a href="{{ route('master-costing') }}">Marketing &nbsp;›&nbsp; Proses &nbsp;›&nbsp; Costing</a> &nbsp;›&nbsp; <b>Edit Costing</b></div>
            <h1 class="costing-page-title">Edit Costing <span class="costing-no">{{ $costing->no_costing }}</span></h1>
            <p class="costing-page-subtitle"></p>

            @if ($errors->any())
            <div class="alert alert-danger py-2" role="alert">
                <ul class="mb-0 pl-3">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form action="{{ route('update-costing-header', $costing->id) }}" method="POST" id="form-header" enctype="multipart/form-data">
                @csrf @method('PUT')
                <div class="costing-section">
                    <div class="costing-section-header">
                        <button type="button" class="costing-section-toggle" data-section-target="#section-buyer" aria-expanded="true">
                            <span>Information Buyer<span class="required-mark">*</span></span>
                            <span class="section-chevron-box"><i class="fas fa-chevron-down"></i></span>
                        </button>
                    </div>
                    <div id="section-buyer" class="collapse show">
                        <div class="costing-section-body">
                            <div class="costing-fields five-columns">
                                <div class="costing-field">
                                    <label>Buyer Name</label>
                                    <select name="buyer" id="buyer" class="form-control select2bs4" required>
                                        <option value="">Choose Buyer Name</option>
                                        @foreach ($buyers as $b) @php $sup_id = $b->id_supplier ?? $b->Id_Supplier; @endphp
                                        <option value="{{ $sup_id }}" data-buyer-code="{{ $b->supplier_code }}" {{ $costing->buyer == $sup_id ? 'selected' : '' }}> {{ $b->supplier ?? $b->Supplier }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="costing-field">
                                    <label>Kode Buyer</label>
                                    <input type="text" id="buyer_code" class="form-control" placeholder="Buyer" value="{{ $costing->buyer_code }}" readonly>
                                </div>
                                <div class="costing-field">
                                    <label>Brand</label>
                                    <input type="text" name="brand" class="form-control" placeholder="Brand" value="{{ $costing->brand }}" required>
                                </div>
                                <div class="costing-field">
                                    <label>Season</label>
                                    <select name="season_id" id="season_id" class="form-control select2bs4" required>
                                        <option value="">Choose Season</option>
                                        @foreach ($seasons as $s)
                                        <option value="{{ $s->id_season }}" {{ $costing->season_id == $s->id_season ? 'selected' : '' }}> {{ $s->season }} - {{ $s->season_desc }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="costing-field">
                                    <label>Style</label>
                                    <input type="text" name="style" class="form-control" placeholder="Style" value="{{ $costing->style }}" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="costing-section">
                    <div class="costing-section-header">
                        <button type="button" class="costing-section-toggle" data-section-target="#section-product" aria-expanded="true">
                            <span>Product & Type<span class="required-mark">*</span></span>
                            <span class="section-chevron-box"><i class="fas fa-chevron-down"></i></span>
                        </button>
                    </div>
                    <div id="section-product" class="collapse show">
                        <div class="costing-section-body">
                            <div class="costing-fields">
                                <div class="costing-field">
                                    <label>Product Group</label>
                                    <select name="product_group" id="product_group" class="form-control select2bs4" required>
                                        <option value="">Choose Product Group</option>
                                        @foreach ($product_groups as $pg)
                                        <option value="{{ $pg->product_group }}" {{ $costing->product_group == $pg->product_group ? 'selected' : '' }}> {{ $pg->product_group }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="costing-field">
                                    <label>Product Item</label>
                                    <select name="product_item" id="product_item" class="form-control select2bs4" required>
                                        <option value="{{ $costing->product_item }}" selected>{{ $costing->product_item_name }}</option>
                                    </select>
                                </div>
                                <div class="costing-field">
                                    <label>Costing Type</label>
                                    <select name="type" id="type" class="form-control select2bs4" required>
                                        <option value="single" {{ $costing->type == 'single' ? 'selected' : '' }}>SINGLE</option>
                                        <option value="multiple" {{ $costing->type == 'multiple' ? 'selected' : '' }}>MULTIPLE</option>
                                    </select>
                                </div>
                                <div class="costing-field">
                                    <label>Product Type</label>
                                    <select id="product_set" name="product_set[]" class="form-control select2bs4" data-placeholder="Product Type" multiple>
                                        @php $saved_sets = $costing->product_set ? explode(',', $costing->product_set) : []; $saved_sets = array_map('trim', $saved_sets); @endphp @foreach ($master_set as $m_set)
                                        <option value="{{ $m_set->id }}" {{ in_array($m_set->id, $saved_sets) ? 'selected' : '' }}> {{ $m_set->nama ?? $m_set->id }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="costing-field">
                                    <label>WS Type</label>
                                    <select name="tipe_ws" id="tipe_ws" class="form-control select2bs4" required>
                                        <option value="">Choose WS Type</option>
                                        <option value="GLOBAL" {{ $costing->tipe_ws == 'GLOBAL' ? 'selected' : '' }}>GLOBAL</option>
                                        <option value="STD" {{ $costing->tipe_ws == 'STD' ? 'selected' : '' }}>STANDAR</option>
                                        {{--
                                        <option value="DTH" {{ $costing->tipe_ws == 'DTH' ? 'selected' : '' }}>DTH</option> --}}
                                    </select>
                                </div>
                                <div class="costing-field">
                                    <label>Unit</label>
                                    <select name="unit" id="unit" class="form-control select2bs4" required>
                                        <option value="">Choose Unit</option>
                                        @foreach ($units as $u)
                                        <option value="{{ $u->nama_pilihan }}" {{ ($costing->unit ?? 'PCS') == $u->nama_pilihan ? 'selected' : '' }}>{{ $u->nama_pilihan }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="costing-section">
                    <div class="costing-section-header">
                        <button type="button" class="costing-section-toggle" data-section-target="#section-shipping" aria-expanded="true">
                            <span>Shipping & Destinations<span class="required-mark">*</span></span>
                            <span class="section-chevron-box"><i class="fas fa-chevron-down"></i></span>
                        </button>
                    </div>
                    <div id="section-shipping" class="collapse show">
                        <div class="costing-section-body">
                            <div class="costing-fields">
                                <div class="costing-field">
                                    <label>Ship Mode</label>
                                    <select name="ship_mode" id="ship_mode" class="form-control select2bs4" required>
                                        <option value="">Choose Ship Mode</option>
                                        @foreach ($shipmodes as $sm)
                                        <option value="{{ $sm->id }}" {{ $costing->ship_mode == $sm->id ? 'selected' : '' }}> {{ $sm->shipmode }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="costing-field">
                                    <label>Shipment Type</label>
                                    <select name="shipment_type" id="shipment_type" class="form-control select2bs4" required>
                                        <option value="local" {{ $costing->shipment_type == 'local' ? 'selected' : '' }}>LOCAL</option>
                                        <option value="export" {{ $costing->shipment_type == 'export' ? 'selected' : '' }}>EXPORT</option>
                                    </select>
                                </div>
                                <div class="costing-field">
                                    <label>Delivery Date</label>
                                    <input type="date" name="deldate" id="deldate" class="form-control" value="{{ $costing->deldate }}" required>
                                </div>
                                <div class="costing-field">
                                    <label>Status</label>
                                    <select name="status" id="status" class="form-control select2bs4" required>
                                        <option value="">Choose Status</option>
                                        <option value="CONFIRM" {{ $costing->status == 'CONFIRM' ? 'selected' : '' }}>CONFIRM</option>
                                        <option value="CANCEL" {{ $costing->status == 'CANCEL' ? 'selected' : '' }}>CANCEL</option>
                                        <option value="SAMPLE" {{ $costing->status == 'SAMPLE' ? 'selected' : '' }}>SAMPLE</option>
                                    </select>
                                </div>
                                <div class="costing-field">
                                    <label>Marketing Order</label>
                                    <select name="marketing_order" id="marketing_order" class="form-control select2bs4" required>
                                        <option value="">Choose Marketing Order</option>
                                        @foreach ($marketing_orders as $mo)
                                        <option value="{{ $mo->id }}" {{ $costing->marketing_order == $mo->id ? 'selected' : '' }}> {{ $mo->mkt_order }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="costing-field">
                                    <label>Main Destination</label>
                                    @php $saved_dests = $costing->main_dest ? explode(',', $costing->main_dest) : []; $saved_dests = array_map('trim', $saved_dests); @endphp
                                    <select name="main_dest[]" id="main_dest" class="form-control select2bs4" data-placeholder="Choose Destination" multiple>
                                        @foreach ($destinations as $dest)
                                        <option value="{{ $dest->country_name }}" {{ in_array($dest->country_name, $saved_dests) ? 'selected' : '' }}>{{ $dest->country_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="costing-field">
                                    <label>Market</label>
                                    <input type="text" name="market" id="market" class="form-control" value="{{ $costing->market }}" placeholder="Market">
                                </div>
                                <div class="costing-field">
                                    <label>Notes</label>
                                    <textarea id="notes" name="notes" rows="1" class="form-control" placeholder="Notes">{{ $costing->notes }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="costing-section">
                    <div class="costing-section-header">
                        <button type="button" class="costing-section-toggle" data-section-target="#section-price" aria-expanded="true">
                            <span>Price & Currency<span class="required-mark">*</span></span>
                            <span class="section-chevron-box"><i class="fas fa-chevron-down"></i></span>
                        </button>
                    </div>
                    <div id="section-price" class="collapse show">
                        <div class="costing-section-body">
                            <div class="costing-fields">
                                <div class="costing-field">
                                    <label>Currency</label>
                                    <select name="curr" id="curr" class="form-control select2bs4" required>
                                        <option value="">Choose Currency</option>
                                        @foreach ($currencies as $cur)
                                        <option value="{{ $cur->id }}" {{ $costing->curr == $cur->id ? 'selected' : '' }}> {{ $cur->nama_pilihan }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="costing-field">
                                    <label>Confirm Price</label>
                                    <input type="text" name="confirm_price" id="confirm_price" class="form-control input-decimal" placeholder="Confirm Price" value="{{ isset($costing->confirm_price) ? number_format($costing->confirm_price, 7, '.', '') : '' }}">
                                </div>
                                <div class="costing-field">
                                    <label>Rate to IDR</label>
                                    <input type="text" name="rate_to_idr" id="rate_to_idr" class="form-control input-decimal" placeholder="Rate to IDR" value="{{ number_format($costing->rate_to_idr, 2, '.', '') }}" required>
                                </div>
                                <div class="costing-field">
                                    <label>Rate from IDR</label>
                                    <input type="text" name="rate_from_idr" id="rate_from_idr" class="form-control input-decimal" placeholder="Rate from IDR" value="{{ number_format($costing->rate_from_idr, 2, '.', '') }}" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="costing-section">
                    <div class="costing-section-header">
                        <button type="button" class="costing-section-toggle" data-section-target="#section-parameters" aria-expanded="true">
                            <span>Parameter Costing<span class="required-mark">*</span></span>
                            <span class="section-chevron-box"><i class="fas fa-chevron-down"></i></span>
                        </button>
                    </div>
                    <div id="section-parameters" class="collapse show">
                        <div class="costing-section-body">
                            <div class="costing-fields">
                                <div class="costing-field">
                                    <label>QTY Planned (PCS)</label>
                                    <input type="text" name="qty_planned" id="qty_planned" class="form-control input-decimal" placeholder="QTY Planned (PCS)" value="{{ isset($costing->qty_planned) ? number_format($costing->qty_planned, 0, '', '') : '' }}" required>
                                </div>
                                <div class="costing-field">
                                    <label>QTY (PCS)</label>
                                    <input type="text" name="qty" id="qty" class="form-control input-decimal" placeholder="QTY (PCS)" value="{{ number_format($costing->qty, 0, '', '') }}" required>
                                </div>
                                <div class="costing-field">
                                    <label>SMV (Min)</label>
                                    <input type="text" name="smv" id="smv" class="form-control input-decimal" placeholder="SMV (Min)" value="{{ number_format($costing->smv, 4, '.', '') }}" required>
                                </div>
                                <div class="costing-field">
                                    <label>VAT (%)</label>
                                    <input type="text" name="vat" id="vat" class="form-control input-decimal" placeholder="VAT (%)" value="{{ number_format($costing->vat, 2, '.', '') }}" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="costing-section">
                    <div class="costing-section-header">
                        <button type="button" class="costing-section-toggle" data-section-target="#section-image" aria-expanded="true">
                            <span>Upload Image Costing<span class="required-mark">*</span></span>
                            <span class="section-chevron-box"><i class="fas fa-chevron-down"></i></span>
                        </button>
                    </div>
                    <div id="section-image" class="collapse show">
                        <div class="costing-section-body mb-4">
                            <div class="row pt-3">
                                <div class="col-md-4">
                                    <input type="file" name="upload_foto[]" id="upload_foto" accept="image/*" multiple hidden>
                                    <div class="upload-file-box mb-3">
                                        <h6 style="font-size: 11px; font-weight: 700; color: var(--costing-navy); margin-bottom: 15px;">Upload File</h6>
                                        <div id="picture-dropzone" class="costing-picture-dropzone" role="button" tabindex="0">
                                            <i class="fas fa-file-alt" style="font-size: 30px; color: var(--costing-navy); margin-bottom: 12px;"></i>
                                            <div style="font-size: 11px; font-weight: 600; color: var(--costing-navy);">
                                                Drag n Drop here
                                                <br>Or
                                                <br><span style="color: #0056b3;">Browse</span>
                                            </div>
                                        </div>
                                        <button type="button" id="btn-upload-now" class="btn btn-block mt-3" style="background-color: #c4c4c4; font-weight: 700; font-size: 11px; color: #333;">Upload Now</button>
                                    </div>
                                    <div id="picture-thumbnails" class="costing-picture-thumbnails" aria-label="Costing image previews"></div>
                                    <button type="button" id="clear-pictures" class="btn btn-outline-danger btn-sm costing-picture-clear" disabled>
                                        <i class="fas fa-trash-alt"></i> Clear All
                                    </button>
                                </div>
                                <div class="col-md-8">
                                    <div class="costing-picture-carousel">
                                        <div class="d-flex align-items-center justify-content-center gap-4 w-100">
                                            <button type="button" id="picture-prev" class="costing-picture-nav" aria-label="Previous image"><i class="fas fa-chevron-left"></i></button>
                                            <div class  ="main-img-container" style="min-height: 250px; display:flex; align-items:center;">
                                                <img id="img_preview" class="costing-picture-main" alt="Costing image preview" hidden>
                                                <span id="no_img_text" class="text-muted" style="font-size: 11px;" hidden>Belum ada gambar</span>
                                            </div>
                                            <button type="button" id="picture-next" class="costing-picture-nav" aria-label="Next image"><i class="fas fa-chevron-right"></i></button>
                                        </div>
                                        <div id="carousel-dots" class="mt-4 d-flex justify-content-center" style="gap: 5px;"></div>
                                    </div>
                                </div>
                            </div>
                            <div id="saved-costing-pictures" class="d-none">
                                @foreach ($pictures as $picture)
                                <span data-url="/nds_wip/public/uploads/costing/{{ $picture->file_name }}" data-name="{{ $picture->original_name ?? $picture->file_name }}" data-file="{{ $picture->file_name }}"></span> @endforeach
                            </div>
                            <input type="hidden" name="clear_existing_pictures" id="clear_existing_pictures" value="0">
                            <div id="removed-costing-pictures"></div>

                        </div>
                    </div>
                </div>

                <div class="costing-form-actions">
                    <button type="submit" class="btn btn-success-custom shadow-sm">Save</button>
                </div>
            </form>

            <div class="costing-category-tabs" role="tablist" aria-label="Costing item categories">
                <button type="button" class="costing-category-tab" data-category="Fabric">Fabric</button>
                <button type="button" class="costing-category-tab" data-category="Accessories Sewing">Accessories Sewing</button>
                <button type="button" class="costing-category-tab" data-category="Accessories Packing">Accessories Packing</button>
                <button type="button" class="costing-category-tab" data-category="Manufacturing">Manufacturing</button>
                <button type="button" class="costing-category-tab" data-category="Other Cost">Other Cost</button>
                <button type="button" id="view-all-items" class="costing-category-tab docs-tab">View All Items</button>
            </div>

            <div class="costing-section">
                <div class="costing-section-header">
                    <button type="button" class="costing-section-toggle" data-section-target="#section-detail" aria-expanded="true">
                        <span>Summary Detail</span>
                        <span class="section-chevron-box"><i class="fas fa-chevron-down"></i></span>
                    </button>
                </div>
                <div id="section-detail" class="collapse show">
                    <div class="costing-section-body costing-detail-body">
                        <div class="costing-detail-toolbar">
                            <div class="costing-field" id="costing-item-select-host">
                                <label>Items</label>
                                <select id="txt_item" class="form-control select2bs4">
                                    <option value="">Choose Items</option>
                                </select>
                            </div>
                            <button type="button" id="toggle-add-costing-item" class="btn-add-item"><i class="fas fa-plus"></i> Add Item</button>
                        </div>
                        <div class="xl-bar">
                            <button type="button" id="xl-bulk-delete" class="btn btn-danger btn-sm" hidden><i class="fas fa-trash"></i> Hapus (0)</button>
                        </div>

                        <select id="category" class="d-none" aria-hidden="true">
                            <option value="Fabric">Fabric</option>
                            <option value="Accessories Sewing">Accessories Sewing</option>
                            <option value="Accessories Packing">Accessories Packing</option>
                            <option value="Manufacturing">Manufacturing</option>
                            <option value="Other Cost">Other Cost</option>
                        </select>

                        @php $saved_sets = $costing->product_set ? explode(',', $costing->product_set) : []; $saved_sets = array_map('trim', $saved_sets); $active_sets_data = []; foreach ($master_set as $m_set) { if (in_array($m_set->id, $saved_sets)) { $active_sets_data[] =
                        [ 'id' => $m_set->id, 'nama' => strtoupper($m_set->nama ?? $m_set->id), 'urutan' => $m_set->urutan ]; } } @endphp

                        <div id="costing-item-entry" class="costing-item-entry" hidden>
                            <div class="table-responsive" style="overflow-x: auto; white-space: nowrap;">
                                <table class="table table-bordered table-sm table-striped w-100" style="font-size: 11px; min-width: 1250px;">
                                    <thead class="bg-sb text-white text-center">
                                        <tr>
                                            <th style="min-width: 120px;">Set</th>
                                            <th style="min-width: 150px;">Desc</th>
                                            <th style="min-width: 200px;">Supplier</th>
                                            <th style="min-width: 80px;">Curr</th>
                                            <th style="min-width: 100px;">Price</th>
                                            <th style="min-width: 80px;">Cons/Pc</th>
                                            <th style="min-width: 100px;">Unit</th>
                                            <th style="min-width: 100px;">Price PX IDR</th>
                                            <th style="min-width: 100px;">Price PX USD</th>
                                            <th style="min-width: 80px;">Allow (%)</th>
                                            <th style="min-width: 120px;">Value IDR</th>
                                            <th style="min-width: 120px;">Value USD</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-light">
                                        <tr>
                                            <td style="padding: 4px;">
                                                <select id="txt_set" class="form-control form-control-sm select2bs4" style="width: 100%;">
                                                    <option value="">-- Set --</option>
                                                    {{-- @foreach ($set as $s)
                                                    <option value="{{ $s->id ?? $s->nama }}">{{ $s->nama ?? $s->id }}</option>
                                                    @endforeach --}} @foreach ($active_sets_data as $act_set)
                                                    <option value="{{ $act_set['id'] }}">{{ $act_set['nama'] }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td style="padding: 4px;">
                                                <input type="text" id="txt_desc" class="form-control form-control-sm" placeholder="Desc...">
                                            </td>
                                            <td style="padding: 4px;">
                                                <select id="txt_supplier" class="form-control form-control-sm select2bs4" style="width: 100%;">
                                                    <option value="">-- Supplier --</option>
                                                    @foreach ($suppliers as $sup)
                                                    <option value="{{ $sup->Id_Supplier ?? $sup->id_supplier }}">{{ $sup->Supplier ?? $sup->supplier }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td style="padding: 4px;">
                                                <select id="txt_curr" class="form-control form-control-sm calc-input select2bs4">
                                                    <option value="">-- Curr --</option>
                                                    @foreach ($currencies as $cur)
                                                    <option value="{{ $cur->nama_pilihan }}">{{ $cur->nama_pilihan }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td style="padding: 4px;">
                                                <input type="text" id="txt_price" class="form-control form-control-sm text-right input-decimal calc-input" value="0">
                                            </td>
                                            <td style="padding: 4px;">
                                                <input type="text" id="txt_cons" class="form-control form-control-sm text-center input-decimal calc-input" value="0">
                                            </td>
                                            <td style="padding: 4px;">
                                                <select id="txt_unit" class="form-control form-control-sm select2bs4" style="width: 100%;">
                                                    <option value="">-- Unit --</option>
                                                    @foreach ($units as $u)
                                                    <option value="{{ $u->nama_pilihan }}">{{ $u->nama_pilihan }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td style="padding: 4px;">
                                                <input type="text" id="txt_px_idr" class="form-control form-control-sm text-right bg-white" readonly value="0">
                                            </td>
                                            <td style="padding: 4px;">
                                                <input type="text" id="txt_px_usd" class="form-control form-control-sm text-right bg-white" readonly value="0">
                                            </td>
                                            <td style="padding: 4px;">
                                                <input type="text" id="txt_allowance" class="form-control form-control-sm text-center input-decimal calc-input" value="0">
                                            </td>
                                            <td style="padding: 4px;">
                                                <input type="text" id="txt_val_idr" class="form-control form-control-sm text-right font-weight-bold bg-white input-decimal calc-input" value="0">
                                            </td>
                                            <td style="padding: 4px;">
                                                <input type="text" id="txt_val_usd" class="form-control form-control-sm text-right font-weight-bold bg-white input-decimal calc-input" value="0">
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>


                        @php $std_categories = [ ['key' => 'Fabric', 'title' => 'FABRIC', 'tbody' => 'tbody-fabric', 'prefix' => 'fab', 'token' => 'fabric'], ['key' => 'Accessories Sewing', 'title' => 'ACCESSORIES SEWING', 'tbody' => 'tbody-acc-sewing', 'prefix' => 'sew', 'token'
                        => 'accessories-sewing'], ['key' => 'Accessories Packing', 'title' => 'ACCESSORIES PACKING', 'tbody' => 'tbody-acc-packing', 'prefix' => 'pack', 'token' => 'accessories-packing'], ['key' => 'Manufacturing', 'title' => 'MANUFACTURING',
                        'tbody' => 'tbody-manufacturing', 'prefix' => 'mfg', 'token' => 'manufacturing'], ]; $order_qty = $costing->qty ?? 0; @endphp

                        <div id="costing-category-content" class="costing-category-content">
                            @foreach($std_categories as $cat)
                            <div class="costing-tab-panel" data-panel-category="{{ $cat['key'] }}">
                                <div class="costing-panel-heading" role="button" tabindex="0"><span>{{ $cat['key'] }}</span><span class="section-chevron-box"><i class="fas fa-chevron-down"></i></span></div>
                                <div class="table-responsive mb-3">
                                    <table class="table table-bordered table-sm text-nowrap w-100" style="font-size: 12px; min-width: 1400px;">
                                        <thead class="text-center">
                                            <tr>
                                                <th class="xl-check-th" width="2%">
                                                    <input type="checkbox" class="xl-check-all" title="Pilih semua">
                                                </th>
                                                <th width="3%">NO</th>
                                                <th width="14%">ITEM</th>
                                                <th width="7%">SET</th>
                                                <th class="costing-desc-column">DESC</th>
                                                <th width="10%">SUPPLIER</th>
                                                <th width="5%">CURR</th>
                                                <th width="7%">PRICE</th>
                                                <th width="7%">PRICE PX IDR</th>
                                                <th width="7%">PRICE PX USD</th>
                                                <th width="5%">CONS/PC</th>
                                                <th width="5%">UNIT</th>
                                                <th width="5%">ALLOW (%)</th>
                                                <th width="8%">VALUE IDR</th>
                                                <th width="8%">VALUE USD</th>
                                                <th width="5%">%</th>
                                                <th width="6%">QTY BOM</th>
                                                <th width="8%">VALUE</th>
                                                <th width="7%">ACTION</th>
                                            </tr>
                                        </thead>
                                        <tbody id="{{ $cat['tbody'] }}">
                                            @if(isset($details[$cat['key']])) @php $sorted_details = collect($details[$cat['key']])->sortBy(function ($item) use ($master_set) { $set_id = $item->set ?? ''; $found = collect($master_set)->firstWhere('id', $set_id); return $found ? $found->urutan :
                                            999; })->values()->all(); @endphp @foreach($sorted_details as $det) @php $allow = $det->allowance > 0 ? $det->allowance : 0; $qty_bom = ceil((1 + ($allow / 100)) * $order_qty * $det->cons); $tot_val = $qty_bom
                                            * $det->price_px_idr; @endphp
                                            <tr class="row-costing" data-category="{{ $cat['key'] }}" data-item="{{ $det->item_id }}" data-set="{{ $det->set }}" data-desc="{{ $det->item_desc }}" data-supplier="{{ $det->supplier_id }}" data-curr="{{ $det->curr }}" data-price="{{ $det->price }}"
                                            data-cons="{{ $det->cons }}" data-unit="{{ $det->unit }}" data-allowance="{{ $det->allowance }}" data-px-idr="{{ $det->price_px_idr }}" data-px-usd="{{ $det->price_px_usd }}" data-value-idr="{{ $det->value_idr }}"
                                            data-value-usd="{{ $det->value_usd }}" id="row-{{ $cat['token'] }}-{{ $det->id }}">
                                                <td class="xl-check-td">
                                                    <input type="checkbox" class="xl-row-check">
                                                </td>
                                                <td class="text-center fw-bold row-number">{{ $loop->iteration }}</td>
                                                <td class="item-name">{{ $det->nama_item }}</td>
                                                <td class="set-td">{{ $det->nama_set }}</td>
                                                <td class="desc-td">{{ $det->item_desc }}</td>
                                                <td class="supplier-td">{{ $det->nama_supplier }}</td>
                                                <td class="text-center curr-td">{{ $det->curr }}</td>
                                                <td class="text-right price-td" data-val="{{ $det->price }}">{{ number_format($det->price, 2, '.', ',') }}</td>
                                                <td class="text-right px-idr-td" data-val="{{ $det->price_px_idr }}">{{ number_format($det->price_px_idr, 2, '.', ',') }}</td>
                                                <td class="text-right px-usd-td" data-val="{{ $det->price_px_usd }}">{{ number_format($det->price_px_usd, 4, '.', ',') }}</td>
                                                <td class="text-center cons-td" data-val="{{ $det->cons }}">{{ number_format($det->cons, 4, '.', '') }}</td>
                                                <td class="text-center unit-td">{{ $det->unit }}</td>
                                                <td class="text-center allow-td" data-val="{{ $det->allowance }}">{{ number_format($det->allowance, 2, '.', '') . '%' }}</td>
                                                <td class="text-right val-idr-td fw-bold" data-val="{{ $det->value_idr }}">{{ number_format($det->value_idr, 2, '.', ',') }}</td>
                                                <td class="text-right val-usd-td fw-bold" data-val="{{ $det->value_usd }}">{{ number_format($det->value_usd, 4, '.', ',') }}</td>
                                                <td class="text-center pct-td fw-bold">0%</td>
                                                <td class="text-right qty-bom-td fw-bold" data-val="{{ $qty_bom }}">{{ number_format($qty_bom, 0, '.', ',') }}</td>
                                                <td class="text-right tot-val-td fw-bold" data-val="{{ $tot_val }}">{{ number_format($tot_val, 2, '.', ',') }}</td>
                                                <td class="text-center align-middle">
                                                    {{-- <button type="button" class="btn btn-sm btn-info py-0 px-2 mr-1" title="Edit" onclick="editRowModal(this, {{ $det->id }}, '{{ $cat['key'] }}')"><i class="fas fa-edit"></i></button> --}}
                                                    <button type="button" class="btn btn-sm btn-danger py-0 px-2" title="Hapus" onclick="removeRow(this, {{ $det->id }}, '{{ $cat['key'] }}')"><i class="fas fa-trash"></i></button>
                                                </td>
                                            </tr>
                                            @endforeach @endif
                                        </tbody>
                                        <tfoot class="fw-bold" id="tfoot-{{ $cat['prefix'] }}">
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                            @endforeach

                            <div class="costing-tab-panel" data-panel-category="Other Cost">
                                <div class="costing-panel-heading" role="button" tabindex="0"><span>Other Cost</span><span class="section-chevron-box"><i class="fas fa-chevron-down"></i></span></div>
                                <div class="table-responsive mb-3">
                                    <table class="table table-bordered table-sm text-nowrap w-100" style="font-size: 12px; min-width: 1000px;">
                                        <thead class="text-center">
                                            <tr>
                                                <th class="xl-check-th" width="3%">
                                                    <input type="checkbox" class="xl-check-all" title="Pilih semua">
                                                </th>
                                                <th width="5%">NO</th>
                                                <th width="30%">ITEM</th>
                                                <th width="14%">ALLOW (%)</th>
                                                <th width="15%">VALUE IDR</th>
                                                <th width="15%">VALUE USD</th>
                                                <th width="9%">%</th>
                                                <th width="9%">ACTION</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tbody-other-cost">
                                            @if(isset($details['Other Cost'])) @foreach($details['Other Cost'] as $det)
                                            <tr class="row-costing" data-category="Other Cost" data-item="{{ $det->item_id }}" data-set="" data-desc="{{ $det->item_desc }}" data-supplier="{{ $det->supplier_id }}" data-curr="{{ $det->curr }}" data-price="{{ $det->price }}" data-cons="{{ $det->cons }}"
                                            data-unit="{{ $det->unit }}" data-allowance="{{ $det->allowance }}" data-px-idr="{{ $det->price_px_idr }}" data-px-usd="{{ $det->price_px_usd }}" data-value-idr="{{ $det->value_idr }}" data-value-usd="{{ $det->value_usd }}"
                                            id="row-other-cost-{{ $det->id }}">
                                                <td class="xl-check-td">
                                                    <input type="checkbox" class="xl-row-check">
                                                </td>
                                                <td class="text-center fw-bold row-number">{{ $loop->iteration }}</td>
                                                <td class="item-name">{{ $det->nama_item }}</td>
                                                <td class="text-center allow-td" data-val="{{ $det->allowance }}">{{ number_format($det->allowance, 2, '.', '') }}%</td>
                                                <td class="text-right val-idr-td-other fw-bold" data-val="{{ $det->value_idr }}">{{ number_format($det->value_idr, 7, '.', ',') }}</td>
                                                <td class="text-right val-usd-td-other fw-bold" data-val="{{ $det->value_usd }}">{{ number_format($det->value_usd, 7, '.', ',') }}</td>
                                                <td class="text-center pct-td fw-bold">0%</td>
                                                <td class="text-center align-middle">
                                                    {{-- <button type="button" class="btn btn-sm btn-info py-0 px-2 mr-1" title="Edit" onclick="editRowModal(this, {{ $det->id }}, 'Other Cost')"><i class="fas fa-edit"></i></button> --}}
                                                    <button type="button" class="btn btn-sm btn-danger py-0 px-2" title="Hapus" onclick="removeRow(this, {{ $det->id }}, 'Other Cost')"><i class="fas fa-trash"></i></button>
                                                </td>
                                            </tr>
                                            @endforeach @endif
                                        </tbody>
                                        <tfoot class="bg-warning fw-bold">
                                            <tr>
                                                <td colspan="4" class="text-left">TOTAL OTHER COST :</td>
                                                <td class="text-right" id="tot-other-idr">0.00</td>
                                                <td class="text-right" id="tot-other-usd">0.00</td>
                                                <td class="text-center" id="tot-other-pct">0%</td>
                                                <td></td>
                                            </tr>
                                            <tr>
                                                <td colspan="3" class="text-left">G&A</td>
                                                <td class="text-center">
                                                    <input type="text" id="ga_percent" name="ga_percent" class="form-control form-control-sm d-inline-block text-center input-decimal" style="width: 120px;" value="{{$costing->ga_percent ?? '3.00' }}">%</td>
                                                <td class="text-right" id="val-ga-idr">0.00</td>
                                                <td class="text-right" id="val-ga-usd">0.00</td>
                                                <td class="text-center pct-ga fw-bold">0%</td>
                                                <td></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>


                            <div class="costing-combined-summary">
                                <div class="costing-summary-wrap">
                                    <div class="costing-summary-card">
                                        <table class="table table-sm" id="table-grand-total"></table>
                                    </div>
                                </div>
                                <div class="costing-summary-wrap">
                                    <div class="costing-summary-card">
                                        <table class="table table-sm" id="table-cost-summary">
                                            <tr>
                                                <td width="40%"></td>
                                                <td class="text-right">VALUE IDR</td>
                                                <td class="text-right">VALUE USD</td>
                                            </tr>
                                            <tr>
                                                <td width="50%">TOTAL COST</td>
                                                <td width="25%" class="text-right" id="grand-tot-idr">0.00</td>
                                                <td width="25%" class="text-right" id="grand-tot-usd">0.00</td>
                                            </tr>
                                            <tr>
                                                <td>BALANCE OTHER COST</td>
                                                <td class="text-right" id="val-suggest-idr">0.00</td>
                                                <td class="text-right" id="val-suggest-usd">0.00</td>
                                            </tr>
                                            <tr>
                                                <td>VAT <span class="float-right" id="vat-label-pct">0%</span></td>
                                                <td class="text-right" id="val-vat-idr">0.00</td>
                                                <td class="text-right" id="val-vat-usd">0.00</td>
                                            </tr>
                                            <tr>
                                                <td>PROFIT <span class="float-right">6%</span></td>
                                                <td class="text-right" id="val-profit-idr">0.00</td>
                                                <td class="text-right" id="val-profit-usd">0.00</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="costing-next-wrap">
                <button type="button" id="costing-next-tab" class="costing-next-tab">Next</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal_edit_costing" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title fw-bold text-dark"><i class="fas fa-edit"></i> Edit Detail Costing</h5>
                <button type="button" class="btn-close text-light" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body bg-light">
                <input type="hidden" id="m_id_detail">
                <input type="hidden" id="m_category">

                <div class="row">
                    <div class="col-md-12 form-group">
                        <label class="fw-bold">Item <span class="text-danger">*</span></label>
                        <select id="m_item" class="form-control select2modal" style="width: 100%;"></select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 form-group m-not-other">
                        <label class="fw-bold">Set</label>
                        <select id="m_set" class="form-control select2modal" style="width: 100%;">
                            <option value="">-- Set --</option>
                            @foreach ($set as $s)
                            <option value="{{ $s->id ?? $s->nama }}">{{ $s->nama ?? $s->id }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 form-group m-not-other">
                        <label class="fw-bold">Desc</label>
                        <input type="text" id="m_desc" class="form-control">
                    </div>
                    <div class="col-md-4 form-group m-not-other">
                        <label class="fw-bold">Supplier</label>
                        <select id="m_supplier" class="form-control select2modal" style="width: 100%;">
                            <option value="">-- Supplier --</option>
                            @foreach ($suppliers as $sup)
                            <option value="{{ $sup->Id_Supplier ?? $sup->id_supplier }}">{{ $sup->Supplier ?? $sup->supplier }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row m-not-other">
                    <div class="col-md-3 form-group">
                        <label class="fw-bold">Curr</label>
                        <select id="m_curr" class="form-control m-calc">
                            @foreach ($currencies as $cur)
                            <option value="{{ $cur->nama_pilihan }}">{{ $cur->nama_pilihan }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 form-group">
                        <label class="fw-bold">Price</label>
                        <input type="text" id="m_price" class="form-control text-right input-decimal m-calc" value="0">
                    </div>
                    <div class="col-md-3 form-group">
                        <label class="fw-bold">Cons/Pc</label>
                        <input type="text" id="m_cons" class="form-control text-center input-decimal m-calc" value="0">
                    </div>
                    <div class="col-md-3 form-group">
                        <label class="fw-bold">Unit</label>
                        <select id="m_unit" class="form-control select2modal" style="width: 100%;">
                            @foreach ($units as $u)
                            <option value="{{ $u->nama_pilihan }}">{{ $u->nama_pilihan }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row mt-2 pt-3 border-top">
                    <div class="col-md-4 form-group">
                        <label class="fw-bold">Allow (%)</label>
                        <input type="text" id="m_allowance" class="form-control text-center input-decimal m-calc" value="0">
                    </div>
                    <div class="col-md-4 form-group">
                        <label class="fw-bold ">Value IDR</label>
                        <input type="text" id="m_val_idr" class="form-control text-right fw-bold bg-white text-success input-decimal m-calc" readonly>
                    </div>
                    <div class="col-md-4 form-group">
                        <label class="fw-bold">Value USD</label>
                        <input type="text" id="m_val_usd" class="form-control text-right fw-bold bg-white text-success input-decimal m-calc" readonly>
                    </div>
                </div>
            </div>
            <div class="modal-footer py-1">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="fa fa-times"></i> Batal</button>
                <button type="button" class="btn btn-success fw-bold" onclick="updateDetailModal()"><i class="fas fa-save"></i> Update</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('custom-script')
<script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
<script>
    function costingRowSelector(category, id) {
        let token = category.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
        return '#row-' + token + '-' + id;
    }

    function lock_type_dropdown() {
        let set_type = $('#type').val();
        let jumlah = $('.row-costing').not('[data-category="Other Cost"]').length;

        if (jumlah > 0) {
            $('#type').prop('disabled', true);
        } else {
            $('#type').prop('disabled', false);
        }

        if (set_type === 'single') {
            $('#product_set').prop('disabled', true);
            $('#txt_set, #m_set').val('').trigger('change').prop('disabled', true);
        } else {
            $('#product_set').prop('disabled', false);
            $('#txt_set, #m_set').prop('disabled', false);
        }
    }

    // Injeksi data urutan set dari PHP ke Javascript
    const masterSetOrder = @json(collect($master_set)->pluck('urutan', 'nama')->mapWithKeys(function ($item, $key) {
        return [strtoupper(trim($key)) => $item];
    }));

    // Fungsi Sort dinamis tabel berdasarkan URUTAN di database (bukan alfabet)
    function sortTbody(tbody_selector) {
        let rows = $(tbody_selector).find('tr.row-costing').get();
        rows.sort(function(a, b) {
            let $setA = $(a).find('.set-td select');
            let $setB = $(b).find('.set-td select');
            let setA = ($setA.length ? $setA.find('option:selected').text() : $(a).find('.set-td').text()).toUpperCase().trim();
            let setB = ($setB.length ? $setB.find('option:selected').text() : $(b).find('.set-td').text()).toUpperCase().trim();

            let orderA = masterSetOrder[setA] !== undefined ? masterSetOrder[setA] : 999;
            let orderB = masterSetOrder[setB] !== undefined ? masterSetOrder[setB] : 999;

            return orderA - orderB; // Sort Ascending berdasar angka urutan
        });

        $.each(rows, function(index, row) {
            $(tbody_selector).append(row);
            $(row).find('.row-number').text(index + 1);
        });
    }

    function autosave_header() {
        let form = $('#form-header');
        let formData = new FormData(form[0]);

        formData.append('ga_percent', $('#ga_percent').val());

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                console.log("Header & G&A berhasil di-autosave!");
            },
            error: function() {
                console.error("Gagal autosave");
            }
        });
    }

    function sync_set_dropdowns() {
        let optionsHtml = '<option value="">-- Set --</option>';

        $('#product_set option:selected').each(function() {
            optionsHtml += `<option value="${$(this).val()}">${$(this).text().trim()}</option>`;
        });

        let current_txt = $('#txt_set').val();
        let current_m = $('#m_set').val();

        $('#txt_set').html(optionsHtml).val(current_txt);
        $('#m_set').html(optionsHtml).val(current_m);
    }


    $('#product_set').on('select2:unselect', function (e) {
        let removedSetId = e.params.data.id;
        let removedSetText = e.params.data.text.toUpperCase().trim();
        let foundCount = 0;

        $('.row-costing').each(function() {
            let rowSet = $(this).find('.set-td').text().toUpperCase().trim();
            if (rowSet === removedSetText) foundCount++;
        });

        if (foundCount > 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Gagal Menghapus Set!',
                html: `Masih ada <b>${foundCount} baris</b> detail yang memakai Set <b>${removedSetText}</b>.<br>Hapus baris tersebut terlebih dahulu.`,
                confirmButtonColor: '#3085d6',
            });

            let currentValues = $(this).val() || [];
            currentValues.push(removedSetId);
            $(this).val(currentValues).trigger('change.select2');
            return false;
        }

        sync_set_dropdowns();
        autosave_header();
    });


    $('#product_set').on('select2:select', function (e) {
        sync_set_dropdowns();
        autosave_header();
    });

    $(document).ready(function() {
        $('.select2bs4').select2({ theme: 'bootstrap4', width: '100%' });

        $('.costing-section-toggle').on('click', function() {
            const $toggle = $(this);
            const $section = $($toggle.attr('data-section-target'));
            const isExpanded = $toggle.attr('aria-expanded') === 'true';

            $section.collapse('toggle');
            $toggle.attr('aria-expanded', String(!isExpanded));
        });

        $(document).on('click keydown', '.costing-panel-heading', function(event) {
            if (event.type === 'keydown' && event.key !== 'Enter' && event.key !== ' ') return;
            event.preventDefault();
            $(this).closest('.costing-tab-panel').toggleClass('is-collapsed');
        });

        $(document).on('select2:open', () => {
            document.querySelector('.select2-container--open .select2-search__field').focus();
        });

        $(document).on('input', '.input-decimal', function() {
            let val = $(this).val().replace(/[^0-9.]/g, '');
            let parts = val.split('.');
            if (parts.length > 2) val = parts[0] + '.' + parts.slice(1).join('');
            $(this).val(val);
        });

        $('.calc-input').on('input change', function() { calculate_template(); });
        $('.m-calc').on('input change', function() { calculate_modal(); });
        $('#rate_to_idr, #rate_from_idr, #confirm_price').on('input', function() { calculate_template(); calculate_summary(); });

        calculate_summary();

        $('#form-header').on('submit', function() {
            $('#type').prop('disabled', false);
        });

        $('input[name="qty"], input[name="vat"]').on('input change', function() {
            calculate_summary();
        });

       function check_shipment_type() {
            let shipment_type = $('#shipment_type').val();
            $('#vat').val('0.00');
            if (shipment_type === 'local') {
                $('#vat').val('11.00');
            }
        }

        $('#shipment_type').on('change', function() {
            check_shipment_type();
        });

        $('#shipment_type').on('change', function() {
            let shipment_type = $(this).val();

            if (shipment_type === 'local') {
                $('#vat').val('11.00');
            } else {
                $('#vat').val('0.00');
            }

            calculate_summary();
            autosave_header();
        });

        let typingTimer;
        let doneTypingInterval = 2000;

        $('#ga_percent').on('input', function() {
            calculate_summary();

            clearTimeout(typingTimer);

            typingTimer = setTimeout(function() {
                autosave_header();
            }, doneTypingInterval);
        });

        $('#vat').on('change', function() {
            let val = parseFloat($(this).val()) || 0;
            $(this).val(val.toFixed(2));

            calculate_summary();
            autosave_header();
        });

        let saved_product_item = "{{ $costing->product_item ?? '' }}";

        function loadProductItems(selected_grup) {
            let select_item = $('#product_item');
            if(selected_grup) {
                $.post("{{ route('get-product-items') }}", { _token: "{{ csrf_token() }}", product_group: selected_grup }, function(res) {
                    select_item.empty().append('<option value="">Pilih Product Item</option>');
                    $.each(res, function(key, value) {
                        let is_selected = (value.id == saved_product_item) ? 'selected' : '';
                        select_item.append(`<option value="${value.id}" ${is_selected}>${value.product_item}</option>`);
                    });
                    select_item.trigger('change');
                });
            } else {
                select_item.empty().append('<option value="">Pilih Product Item</option>').trigger('change');
            }
        }

        $('#product_group').on('change', function() {
            loadProductItems($(this).val());
        });

        $('#buyer').on('change', function() {
            $('#buyer_code').val($(this).find(':selected').data('buyer-code') || '');
        });

        if ($('#product_group').val() !== '') {
            loadProductItems($('#product_group').val());
        }

        const $categoryTabs = $('.costing-category-tab');
        function updateNextTabButton() {
            const activeIndex = $categoryTabs.index($('.costing-category-tab.active').first());
            $('#costing-next-tab').prop('disabled', activeIndex < 0 || activeIndex >= $categoryTabs.length - 1);
        }

        $('#category').on('change', function() {
            let cat = $(this).val();
            let select_item = $('#txt_item');
            $('#costing-category-content').removeClass('is-all-items');
            $('#txt_item, #toggle-add-costing-item').prop('disabled', false);
            $('.costing-category-tab').removeClass('active').attr('aria-selected', 'false');
            $(`.costing-category-tab[data-category="${cat}"]`).addClass('active').attr('aria-selected', 'true');
            $('.costing-tab-panel').removeClass('active')
                .filter(`[data-panel-category="${cat}"]`).addClass('active');
            updateNextTabButton();

            if (cat === 'Other Cost') {
                $('#txt_set, #txt_supplier, #txt_curr, #txt_unit').prop('disabled', true);
                $('#txt_desc, #txt_price, #txt_cons , #txt_px_idr , #txt_px_usd').prop('readonly', true);
                $('#txt_allowance').prop('readonly', false).removeClass('bg-light');
                $('#txt_val_idr').prop('readonly', false).removeClass('bg-light text-success').addClass('bg-white');
                $('#txt_val_usd').prop('readonly', false).removeClass('bg-light text-success').addClass('bg-white');
                $('#txt_price, #txt_cons, #txt_allowance, #txt_val_idr, #txt_val_usd, #txt_px_idr , #txt_px_usd').val('0');

            } else {
                $('#txt_supplier, #txt_curr, #txt_unit').prop('disabled', false);
                $('#txt_desc, #txt_price, #txt_cons, #txt_px_idr , #txt_px_usd').prop('readonly', false);
                $('#txt_allowance').prop('readonly', false).removeClass('bg-light');
                lock_type_dropdown();

                $('#txt_val_idr, #txt_val_usd').prop('readonly', true).addClass('bg-light text-success').removeClass('bg-white');
            }

            if (select_item.data("select2")) {
                select_item.select2('destroy');
            }

            select_item.html('<option value="" disabled selected>Sedang Menarik Data Item...</option>');
            select_item.select2({ theme: 'bootstrap4', width: '100%' });

            if (cat) {
                let cat_query = cat;

                $.ajax({
                    url: "{{ route('get-item-contents') }}",
                    type: "GET",
                    data: { kategori: cat_query },
                    success: function(res) {
                        if (select_item.data("select2")) {
                            select_item.select2('destroy');
                        }
                        select_item.html(res);
                        select_item.select2({ theme: 'bootstrap4', width: '100%' });
                    },
                    error: function() { alert('Gagal mengambil data item dari server.'); }
                });
            }
        });

        $('.costing-category-tab[data-category]').on('click', function() {
            $('#category').val($(this).data('category')).trigger('change');
        });
        $('#costing-next-tab').on('click', function() {
            const activeIndex = $categoryTabs.index($('.costing-category-tab.active').first());
            const nextTab = $categoryTabs.get(activeIndex + 1);
            if (!nextTab) return;
            nextTab.click();
            nextTab.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'nearest' });
        });
        $('#toggle-add-costing-item').on('click', function() {
            saveDetail();
        });
        $('#view-all-items').on('click', function() {
            $('.costing-category-tab').removeClass('active').attr('aria-selected', 'false');
            $(this).addClass('active').attr('aria-selected', 'true');
            $('#costing-category-content').addClass('is-all-items');
            $('#txt_item, #toggle-add-costing-item').prop('disabled', true);
            updateNextTabButton();
        });

        $('#category').val('Fabric').trigger('change');
        $('#costing-category-content .row-costing').each(function() {
            initializeCostingInlineRow($(this));
        });
        calculate_summary();
        xlInit();

        let prev_dest_count = $('#main_dest').val() ? $('#main_dest').val().length : 0;
        $('#main_dest').on('change', function() {
            let curr_val = $(this).val();
            let curr_count = curr_val ? curr_val.length : 0;

            if (curr_count > 1 && prev_dest_count <= 1) {
                Swal.fire({
                    title: 'Perhatian!',
                    text: 'Anda memilih lebih dari 1 Destination. Ingat untuk menggabungkan WS jika diperlukan!',
                    icon: 'info',
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, Mengerti'
                });
            }
            prev_dest_count = curr_count;
        });

        $('#type').on('change', function() {
            lock_type_dropdown();
        });

        $('#txt_item, #txt_set').on('change', function() {
            let cat = $('#category').val();
            let selected_item = $('#txt_item').val();
            let selected_set = $('#txt_set').val() || '';
        });

        $('#m_item, #m_set').on('change', function() {
            let cat = $('#m_category').val();
            let selected_item = $('#m_item').val();
            let selected_set = $('#m_set').val() || '';
            let current_row_id = 'row-' + $('#m_id_detail').val();
        });
    });

    function calculate_template() {
        let rate_to_idr = parseFloat($('#rate_to_idr').val()) || 0;
        let rate_from_idr = parseFloat($('#rate_from_idr').val()) || 1;
        let header_curr = $('#curr option:selected').text().trim().toUpperCase();

        let cat = $('#category').val();
        let curr = $('#txt_curr option:selected').text().trim().toUpperCase();
        let price = parseFloat($('#txt_price').val()) || 0;
        let cons = parseFloat($('#txt_cons').val()) || 0;
        let allow_pct = parseFloat($('#txt_allowance').val()) || 0;
        let item_text = $('#txt_item option:selected').text().toUpperCase();

        if (cat === 'Other Cost') {
            $('#txt_set, #txt_supplier, #txt_curr, #txt_unit').prop('disabled', true);
            $('#txt_desc, #txt_price, #txt_cons').prop('readonly', true);

            $('#txt_allowance').prop('readonly', false).removeClass('bg-light');
            $('#txt_val_idr').prop('readonly', false).removeClass('bg-light').addClass('bg-white');
            $('#txt_val_usd').prop('readonly', false).removeClass('bg-light').addClass('bg-white');

            $('#txt_px_idr, #txt_px_usd').val('0').data('raw', 0);

            if (document.activeElement && document.activeElement.id === 'txt_val_usd') {
                let val_usd = parseFloat(String($('#txt_val_usd').val()).replace(/,/g, '')) || 0;
                let val_idr = val_usd * rate_to_idr;

                $('#txt_val_usd').data('raw', val_usd);
                $('#txt_val_idr').val(val_idr.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})).data('raw', val_idr);
            } else {
                let val_idr = parseFloat(String($('#txt_val_idr').val()).replace(/,/g, '')) || 0;
                let val_usd = val_idr / rate_to_idr;

                $('#txt_val_idr').data('raw', val_idr);
                $('#txt_val_usd').val(val_usd.toLocaleString('en-US', {minimumFractionDigits: 4, maximumFractionDigits: 4})).data('raw', val_usd);
            }
        } else {
            $('#txt_supplier, #txt_curr, #txt_unit').prop('disabled', false);
            $('#txt_desc, #txt_price, #txt_cons').prop('readonly', false);
            lock_type_dropdown();

            $('#txt_allowance').prop('readonly', false).removeClass('bg-light');
            $('#txt_val_idr, #txt_val_usd').prop('readonly', true).addClass('bg-light').removeClass('bg-white');

            let px_idr = 0; let px_usd = 0;
            if (header_curr === 'USD') {
                px_usd = price; px_idr = price * rate_to_idr;
            } else {
                px_idr = price; px_usd = price / rate_from_idr;
            }

            let allow = 1 + (allow_pct / 100);
            let val_idr = px_idr * cons * allow;
            let val_usd = px_usd * cons * allow;

            $('#txt_px_idr').val(px_idr.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})).data('raw', px_idr);
            $('#txt_px_usd').val(px_usd.toLocaleString('en-US', {minimumFractionDigits: 4, maximumFractionDigits: 4})).data('raw', px_usd);
            $('#txt_val_idr').val(val_idr.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})).data('raw', val_idr);
            $('#txt_val_usd').val(val_usd.toLocaleString('en-US', {minimumFractionDigits: 4, maximumFractionDigits: 4})).data('raw', val_usd);
        }
    }

    function calculate_modal() {
        let rate_to_idr = parseFloat($('#rate_to_idr').val()) || 0;
        let rate_from_idr = parseFloat($('#rate_from_idr').val()) || 1;
        let header_curr = $('#curr option:selected').text().trim().toUpperCase();

        let cat = $('#m_category').val();
        let allow_pct = parseFloat($('#m_allowance').val()) || 0;
        let item_text = $('#m_item option:selected').text().toUpperCase();

        if (cat === 'Other Cost') {
            $('#m_set, #m_supplier, #m_curr, #m_unit').prop('disabled', true);
            $('#m_desc, #m_price, #m_cons').prop('readonly', true);

            $('#m_allowance').prop('readonly', false).removeClass('bg-light');
            $('#m_val_idr').prop('readonly', false).removeClass('bg-light text-success').addClass('bg-white');
            $('#m_val_usd').prop('readonly', false).removeClass('bg-light text-success').addClass('bg-white');

            $('#m_price').data('raw_idr', 0).data('raw_usd', 0);

            if (document.activeElement && document.activeElement.id === 'm_val_usd') {
                let current_val_usd = parseFloat(String($('#m_val_usd').val()).replace(/,/g,'')) || 0;
                let current_val_idr = current_val_usd * rate_to_idr;

                $('#m_val_usd').data('raw', current_val_usd);
                $('#m_val_idr').val(current_val_idr.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})).data('raw', current_val_idr);
            } else {
                let current_val_idr = parseFloat(String($('#m_val_idr').val()).replace(/,/g,'')) || 0;
                let current_val_usd = current_val_idr / rate_to_idr;

                $('#m_val_idr').data('raw', current_val_idr);
                $('#m_val_usd').val(current_val_usd.toLocaleString('en-US', {minimumFractionDigits: 4, maximumFractionDigits: 4})).data('raw', current_val_usd);
            }

        }
        else {
            $('#m_supplier, #m_curr, #m_unit').prop('disabled', false);
            $('#m_desc, #m_price, #m_cons').prop('readonly', false);
            lock_type_dropdown();

            $('#m_allowance').prop('readonly', false).removeClass('bg-light');
            $('#m_val_idr, #m_val_usd').prop('readonly', true).addClass('bg-white text-success').removeClass('bg-light');

            let curr = $('#m_curr').val().toUpperCase();
            let price = parseFloat($('#m_price').val()) || 0;
            let cons = parseFloat($('#m_cons').val()) || 0;

            let px_idr = 0; let px_usd = 0;

            if (header_curr === 'USD') {
                px_usd = price; px_idr = price * rate_to_idr;
            } else {
                px_idr = price; px_usd = price / rate_from_idr;
            }

            let allow = 1 + (allow_pct / 100);
            let val_idr = px_idr * cons * allow;
            let val_usd = px_usd * cons * allow;

            $('#m_val_idr').val(val_idr.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})).data('raw', val_idr);
            $('#m_val_usd').val(val_usd.toLocaleString('en-US', {minimumFractionDigits: 4, maximumFractionDigits: 4})).data('raw', val_usd);
            $('#m_price').data('raw_idr', px_idr).data('raw_usd', px_usd);
        }
    }

    function check_duplicate(category_id, item_id, setId = '', exclude_row_id = null) {
        let is_duplicate = false;

        $('.row-costing[data-category="'+category_id+'"]').each(function() {
            let row_item = $(this).data('item') ? $(this).data('item').toString() : '';
            let row_set = $(this).data('set') ? $(this).data('set').toString() : '';
            let row_id = $(this).attr('id');

            if (exclude_row_id && row_id === exclude_row_id) {
                return true;
            }

            if (category_id === 'Other Cost') {
                if (row_item === item_id.toString()) {
                    is_duplicate = true;
                    return false;
                }
            } else {
                let inputSetId = setId ? setId.toString() : '';

                if (row_item === item_id.toString() && row_set === inputSetId) {
                    is_duplicate = true;
                    return false;
                }
            }
        });

        return is_duplicate;
    }

    function initializeCostingInlineRow($row) {
        if ($row.data('inline-ready')) return;
        const isOtherCost = $row.attr('data-category') === 'Other Cost';
        const input = (field, value, numeric = false) => $('<input>', {
            type: 'text',
            class: `costing-inline-control${numeric ? ' text-right' : ''}`,
            'data-field': field,
            draggable: false,
            value: value || ''
        });
        const select = (field, options, value) => {
            const $select = $('<select>', {
                class: 'costing-inline-control costing-inline-select',
                'data-field': field,
                draggable: false
            }).html(options);
            return $select.val(value || '');
        };

        if (!isOtherCost) {
            $row.find('.set-td').empty().append(select('set', $('#txt_set').html(), $row.attr('data-set')));
            $row.find('.desc-td').empty().append(input('desc', $row.attr('data-desc')));
            $row.find('.supplier-td').empty().append(select('supplier', $('#txt_supplier').html(), $row.attr('data-supplier')));
            $row.find('.curr-td').empty().append(select('curr', $('#txt_curr').html(), $row.attr('data-curr')));
            $row.find('.price-td').empty().append(input('price', $row.attr('data-price'), true));
            $row.find('.px-idr-td').empty().append(input('price_px_idr', $row.attr('data-px-idr'), true));
            $row.find('.px-usd-td').empty().append(input('price_px_usd', $row.attr('data-px-usd'), true));
            $row.find('.cons-td').empty().append(input('cons', $row.attr('data-cons'), true));
            $row.find('.unit-td').empty().append(select('unit', $('#txt_unit').html(), $row.attr('data-unit')));
            $row.find('.allow-td').empty().append(input('allowance', $row.attr('data-allowance'), true));
            $row.find('.val-idr-td').empty().append(input('value_idr', $row.attr('data-value-idr'), true));
            $row.find('.val-usd-td').empty().append(input('value_usd', $row.attr('data-value-usd'), true));
        } else {
            $row.find('.allow-td').empty().append(input('allowance', $row.attr('data-allowance'), true));
            $row.find('.val-idr-td-other').empty().append(input('value_idr', $row.attr('data-value-idr'), true));
            $row.find('.val-usd-td-other').empty().append(input('value_usd', $row.attr('data-value-usd'), true));
        }
        $row.find('.costing-inline-select').select2({
            theme: 'bootstrap4',
            width: '100%'
        });
        $row.data('inline-ready', true);
    }

    function costingActionButtons(id, cat) {
        // return `<button type="button" class="btn btn-sm btn-info py-0 px-2 mr-1" title="Edit" onclick="editRowModal(this, ${id}, '${cat}')"><i class="fas fa-edit"></i></button>` +
         return `<button type="button" class="btn btn-sm btn-danger py-0 px-2" title="Hapus" onclick="removeRow(this, ${id}, '${cat}')"><i class="fas fa-trash"></i></button>`;
    }

    function costingRowHtml(cat, rowId, idDetail, d, no) {
        const esc = v => $('<span>').text(v == null ? '' : v).html();
        const at = v => String(v == null ? '' : v).replace(/&/g, '&amp;').replace(/"/g, '&quot;');
        const fmt = (n, dp) => Number(n || 0).toLocaleString('en-US', { minimumFractionDigits: dp, maximumFractionDigits: dp });
        const attrs = `class="row-costing" data-category="${at(cat)}" data-item="${at(d.item_id)}" data-set="${at(d.set_val)}" data-desc="${at(d.desc)}" data-supplier="${at(d.supplier_id)}" data-curr="${at(d.curr)}" data-price="${at(d.price)}" data-cons="${at(d.cons)}" data-unit="${at(d.unit)}" data-allowance="${at(d.allow)}" data-px-idr="${at(d.px_idr)}" data-px-usd="${at(d.px_usd)}" data-value-idr="${at(d.val_idr)}" data-value-usd="${at(d.val_usd)}" id="${rowId}"`;
        const check = '<td class="xl-check-td"><input type="checkbox" class="xl-row-check"></td>';
        const actions = `<td class="text-center align-middle">${costingActionButtons(idDetail, cat)}</td>`;
        if (cat === 'Other Cost') {
            return `<tr ${attrs}>${check}<td class="text-center fw-bold row-number">${no}</td><td class="item-name">${esc(d.item_text)}</td>
                <td class="text-center allow-td" data-val="${at(d.allow)}"></td><td class="text-right val-idr-td-other fw-bold" data-val="${at(d.val_idr)}"></td>
                <td class="text-right val-usd-td-other fw-bold" data-val="${at(d.val_usd)}"></td><td class="text-center pct-td fw-bold">0%</td>${actions}</tr>`;
        }
        return `<tr ${attrs}>${check}<td class="text-center fw-bold row-number">${no}</td><td class="item-name">${esc(d.item_text)}</td><td class="set-td"></td><td class="desc-td"></td>
            <td class="supplier-td"></td><td class="text-center curr-td"></td><td class="text-right price-td" data-val="${at(d.price)}"></td>
            <td class="text-right px-idr-td" data-val="${at(d.px_idr)}"></td><td class="text-right px-usd-td" data-val="${at(d.px_usd)}"></td>
            <td class="text-center cons-td" data-val="${at(d.cons)}"></td><td class="text-center unit-td"></td><td class="text-center allow-td" data-val="${at(d.allow)}"></td>
            <td class="text-right val-idr-td fw-bold" data-val="${at(d.val_idr)}"></td><td class="text-right val-usd-td fw-bold" data-val="${at(d.val_usd)}"></td>
            <td class="text-center pct-td fw-bold">0%</td><td class="text-right qty-bom-td fw-bold" data-val="${at(d.qty_bom)}">${fmt(d.qty_bom, 0)}</td>
            <td class="text-right tot-val-td fw-bold" data-val="${at(d.tot_val)}">${fmt(d.tot_val, 2)}</td>${actions}</tr>`;
    }

    function saveDetail() {
        const category = $('#category').val();
        const itemId = $('#txt_item').val();
        if (!category || !itemId) {
            Swal.fire('Peringatan', 'Pilih item terlebih dahulu.', 'warning');
            return;
        }
        if (check_duplicate(category, itemId, '', null)) {
            Swal.fire('Peringatan', 'Item tersebut sudah ada di kategori ini.', 'warning');
            return;
        }

        const itemText = $('<span>').text($('#txt_item option:selected').text()).html();
        const currency = $('#curr option:selected').text().trim() || 'IDR';
        const bodyByCategory = {
            'Fabric': '#tbody-fabric',
            'Accessories Sewing': '#tbody-acc-sewing',
            'Accessories Packing': '#tbody-acc-packing',
            'Manufacturing': '#tbody-manufacturing',
            'Other Cost': '#tbody-other-cost'
        };
        const target = bodyByCategory[category];
        const defaults = {
            desc: '', supplier: '', curr: currency, price: 0, cons: 0, unit: '',
            price_px_idr: 0, price_px_usd: 0, allowance: 0, set: '',
            value_idr: 0, value_usd: 0
        };
        const $button = $('#toggle-add-costing-item').prop('disabled', true);
        $.ajax({
            url: "{{ route('store-costing-detail') }}",
            type: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                id_costing: '{{ $costing->id }}',
                category: category,
                item: itemId,
                ...defaults
            },
            success: function(response) {
                if (response.status !== 200) {
                    Swal.fire('Gagal menambahkan item', response.message || 'Item tidak tersimpan.', 'error');
                    return;
                }
                const rowId = costingRowSelector(category, response.insert_id).substring(1);
                const count = $(`${target} tr`).length + 1;
                const actions = costingActionButtons(response.insert_id, category);
                let html;
                if (category === 'Other Cost') {
                    html = `<tr class="row-costing" data-category="${category}" data-item="${itemId}" data-set="" data-desc="" data-supplier="" data-curr="${currency}" data-price="0" data-cons="0" data-unit="" data-allowance="0" data-px-idr="0" data-px-usd="0" data-value-idr="0" data-value-usd="0" id="${rowId}">
                        <td class="xl-check-td"><input type="checkbox" class="xl-row-check"></td><td class="text-center fw-bold row-number">${count}</td><td class="item-name">${itemText}</td>
                        <td class="text-center allow-td" data-val="0"></td><td class="text-right val-idr-td-other fw-bold" data-val="0"></td>
                        <td class="text-right val-usd-td-other fw-bold" data-val="0"></td><td class="text-center pct-td fw-bold">0%</td><td class="text-center align-middle">${actions}</td></tr>`;
                } else {
                    html = `<tr class="row-costing" data-category="${category}" data-item="${itemId}" data-set="" data-desc="" data-supplier="" data-curr="${currency}" data-price="0" data-cons="0" data-unit="" data-allowance="0" data-px-idr="0" data-px-usd="0" data-value-idr="0" data-value-usd="0" id="${rowId}">
                        <td class="xl-check-td"><input type="checkbox" class="xl-row-check"></td><td class="text-center fw-bold row-number">${count}</td><td class="item-name">${itemText}</td><td class="set-td"></td><td class="desc-td"></td>
                        <td class="supplier-td"></td><td class="text-center curr-td"></td><td class="text-right price-td" data-val="0"></td>
                        <td class="text-right px-idr-td" data-val="0"></td><td class="text-right px-usd-td" data-val="0"></td>
                        <td class="text-center cons-td" data-val="0"></td><td class="text-center unit-td"></td><td class="text-center allow-td" data-val="0"></td>
                        <td class="text-right val-idr-td fw-bold" data-val="0"></td><td class="text-right val-usd-td fw-bold" data-val="0"></td>
                        <td class="text-center pct-td fw-bold">0%</td><td class="text-right qty-bom-td fw-bold" data-val="0">0</td>
                        <td class="text-right tot-val-td fw-bold" data-val="0">0.00</td><td class="text-center align-middle">${actions}</td></tr>`;
                }
                const $row = $(html).appendTo(target);
                initializeCostingInlineRow($row);
                $('#txt_item').val('').trigger('change');
                if (category !== 'Other Cost') sortTbody(target);
                calculate_summary();
                xlShowRow($row);
                $row.find('.costing-inline-control').first().trigger('focus');
            },
            error: function(xhr) {
                const message = xhr.responseJSON && xhr.responseJSON.message;
                Swal.fire('Gagal menambahkan item', message || 'Item tidak tersimpan.', 'error');
            },
            complete: function() {
                $button.prop('disabled', false);
            }
        });
    }

    function saveDetailLegacy() {
        let id_costing = '{{ $costing->id ?? "" }}';
        let cat = $('#category').val();
        let item_id = $('#txt_item').val();
        let item_text = $('#txt_item option:selected').text();
        let desc = $('#txt_desc').val();
        let supplier_id = $('#txt_supplier').val() || '';
        let supplier_text = supplier_id ? $('#txt_supplier option:selected').text() : '';
        let curr = $('#txt_curr').val();
        let price = $('#txt_price').val() || 0;
        let cons = $('#txt_cons').val() || 0;
        let unit = $('#txt_unit').val();
        let allow = $('#txt_allowance').val() || 0;

        let px_idr_text = $('#txt_px_idr').val();
        let px_usd_text = $('#txt_px_usd').val();

        let px_idr_raw = $('#txt_px_idr').data('raw') || 0;
        let px_usd_raw = $('#txt_px_usd').data('raw') || 0;
        let val_idr_raw = $('#txt_val_idr').data('raw') || 0;
        let val_usd_raw = $('#txt_val_usd').data('raw') || 0;

        let set_val = $('#txt_set').val() || '';
        let set_text = set_val ? $('#txt_set option:selected').text() : '';

        let order_qty = parseFloat($('#qty').val().replace(/,/g, '')) || 0;
        let qty_bom_js = Math.ceil((1 + (allow / 100)) * order_qty * cons);
        let tot_val_js = qty_bom_js * px_idr_raw;

        if (!cat) {
            Swal.fire('Peringatan', 'Silakan Pilih Kategori di atas!', 'warning');
            return;
        }

        if (!item_id) {
            Swal.fire('Peringatan', 'Silakan Pilih Item pada form!', 'warning');
            return;
        }

        let header_type = $('#type').val();
        if (cat !== 'Other Cost' && header_type === 'multiple' && set_val === '') {
            Swal.fire('Peringatan', 'Tipe Costing MULTIPLE, silakan Pilih Set terlebih dahulu!', 'warning');
            return;
        }

        Swal.fire({ title: 'Menyimpan...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });

        $.ajax({
            url: "{{ route('store-costing-detail') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                id_costing: id_costing,
                category: cat,
                item: item_id,
                desc: desc,
                supplier: supplier_id,
                curr: curr,
                price: price,
                cons: cons,
                unit: unit,
                price_px_idr: px_idr_raw,
                price_px_usd: px_usd_raw,
                allowance: allow,
                set: set_val,
                value_idr: val_idr_raw,
                value_usd: val_usd_raw
            },
            success: function(res) {
                if(res.status == 200) {
                    Swal.close();
                    let val_idr_text = val_idr_raw.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    let val_usd_text = val_usd_raw.toLocaleString('en-US', {minimumFractionDigits: 7, maximumFractionDigits: 7});

                    let val_idr_text_other = val_idr_raw.toLocaleString('en-US', {minimumFractionDigits: 7, maximumFractionDigits: 7});

                    let qty_bom_text = qty_bom_js.toLocaleString('en-US');
                    let tot_val_text = tot_val_js.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});

                    let target_tbody = '';
                    if(cat === 'Fabric') { target_tbody = '#tbody-fabric'; }
                    else if (cat === 'Accessories Sewing') { target_tbody = '#tbody-acc-sewing'; }
                    else if (cat === 'Accessories Packing') { target_tbody = '#tbody-acc-packing'; }
                    else if (cat === 'Manufacturing') { target_tbody = '#tbody-manufacturing'; }
                    else if (cat === 'Other Cost') { target_tbody = '#tbody-other-cost'; }

                    let rowCount = $(target_tbody + ' tr').length + 1;
                    let rowId = costingRowSelector(cat, res.insert_id).substring(1);
                    let tr = '';

                    if (cat === 'Other Cost') {
                        tr = `
                            <tr class="row-costing" data-category="${cat}" data-item="${item_id}" id="${rowId}">
                                <td class="text-center fw-bold row-number">${rowCount}</td>
                                <td class="item-name">${item_text}</td>
                                <td class="text-center allow-td" data-val="${allow}">${allow > 0 ? allow + '%' : ''}</td>
                                <td class="text-right val-idr-td-other fw-bold" data-val="${val_idr_raw}">${val_idr_text_other}</td>
                                <td class="text-right val-usd-td- fw-bold" data-val="${val_usd_raw}">${val_usd_text}</td>
                                <td class="text-center pct-td fw-bold">0%</td>
                                <td class="text-center align-middle">
                                    <button type="button" class="btn btn-sm btn-primary py-0 px-2 mr-1" onclick="editRowModal(this, ${res.insert_id}, '${cat}')"><i class="fas fa-edit"></i></button>
                                    <button type="button" class="btn btn-sm btn-danger py-0 px-2" onclick="removeRow(this, ${res.insert_id}, '${cat}')"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                        `;
                    } else {
                        tr = `
                            <tr class="row-costing" data-category="${cat}" data-item="${item_id}" data-set="${set_val}" id="${rowId}">
                                <td class="text-center fw-bold row-number">${rowCount}</td>
                                <td class="item-name">${item_text}</td>
                                <td class="set-td">${set_text}</td>
                                <td class="desc-td">${desc}</td>
                                <td class="supplier-td">${supplier_text}</td>
                                <td class="text-right px-idr-td" data-val="${px_idr_raw}">${px_idr_text}</td>
                                <td class="text-right px-usd-td" data-val="${px_usd_raw}">${px_usd_text}</td>
                                <td class="text-center cons-td" data-val="${cons}">${cons}</td><td class="text-center">${unit}</td>
                                <td class="text-center allow-td" data-val="${allow}">${allow}</td>
                                <td class="text-right val-idr-td fw-bold" data-val="${val_idr_raw}">${val_idr_text}</td>
                                <td class="text-right val-usd-td fw-bold" data-val="${val_usd_raw}">${val_usd_text}</td>
                                <td class="text-center pct-td fw-bold">0%</td>
                                <td class="text-right qty-bom-td fw-bold" data-val="${qty_bom_js}">${qty_bom_text}</td>
                                <td class="text-right tot-val-td fw-bold" data-val="${tot_val_js}">${tot_val_text}</td>
                                <td class="text-center align-middle">
                                    <button type="button" class="btn btn-sm btn-primary py-0 px-2 mr-1" onclick="editRowModal(this, ${res.insert_id}, '${cat}')"><i class="fas fa-edit"></i></button>
                                    <button type="button" class="btn btn-sm btn-danger py-0 px-2" onclick="removeRow(this, ${res.insert_id}, '${cat}')"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                        `;
                    }

                    $(target_tbody).append(tr);

                    $('#txt_item').val('').trigger('change');
                    $('#txt_set').val('').trigger('change');
                    $('#txt_desc').val('');
                    $('#txt_supplier').val('').trigger('change');
                    $('#txt_price').val('0');
                    $('#txt_cons').val('0');
                    $('#txt_allowance').val('0').prop('readonly', false);
                    $('#txt_val_idr').val('0');
                    $('#txt_val_usd').val('0');

                    if (cat !== 'Other Cost') {
                        sortTbody(target_tbody);
                    }

                    calculate_template();
                    calculate_summary();
                } else {
                    Swal.fire('Error', res.message, 'error');
                }
            }
        });
    }

    function editRowModal(btn, id, cat) {
        Swal.fire({ title: 'Memuat data...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });

        let urlTarget = "{{ route('get-detail-row-costing', ':id') }}".replace(':id', id);

        $.get(urlTarget, { category: cat, id_costing: '{{ $costing->id }}' }, function(res) {
            if(res.status == 200) {
                let d = res.data;

                $('#m_id_detail').val(d.id);
                $('#m_category').val(cat);

                let cat_for_query = cat;

                $.ajax({
                    url: "{{ route('get-item-contents') }}",
                    type: "GET",
                    data: { kategori: cat_for_query },
                    success: function(htmlOptions) {
                        Swal.close();

                        if ($('#m_item').data('select2')) {
                            $('#m_item').select2('destroy');
                        }

                        $('#m_item').html(htmlOptions).select2({ theme: 'bootstrap4', dropdownParent: $('#modal_edit_costing') });
                        $('#m_item').val(d.item_id).trigger('change');

                        if(cat === 'Other Cost') { $('.m-not-other').hide(); }
                        else { $('.m-not-other').show(); }

                        $('#m_desc').val(d.item_desc);
                        $('#m_supplier').val(d.supplier_id || d.supplier).trigger('change');
                        $('#m_curr').val(d.curr || 'IDR');
                        $('#m_price').val(d.price || 0);
                        $('#m_cons').val(d.cons || 0);
                        $('#m_unit').val(d.unit).trigger('change');
                        $('#m_allowance').val(d.allowance || 0);
                        $('#m_set').val(d.set).trigger('change');

                        $('#m_val_idr').val(d.value_idr || 0);
                        $('#m_val_usd').val(d.value_usd || 0);

                        calculate_modal();

                        $('.select2modal').not('#m_item').each(function() {
                            if ($(this).data('select2')) { $(this).select2('destroy'); }
                        });
                        $('.select2modal').not('#m_item').select2({ theme: 'bootstrap4', dropdownParent: $('#modal_edit_costing') });

                        $('#modal_edit_costing').modal('show');
                    },
                    error: function() {
                        Swal.close();
                        Swal.fire('Error', 'Gagal mengambil list item', 'error');
                    }
                });

            } else {
                Swal.close();
                Swal.fire('Error', 'Data detail tidak ditemukan.', 'error');
            }
        }).fail(function() {
            Swal.close();
            Swal.fire('Error', 'Gagal menghubungi server (404/500)', 'error');
        });
    }

    function updateDetailModal() {
        let id_detail = $('#m_id_detail').val();
        let cat = $('#m_category').val();

        let item_id = $('#m_item').val();
        let item_text = $('#m_item option:selected').text();
        let desc = $('#m_desc').val();
        let supplier_id = $('#m_supplier').val() || '';
        let supplier_text = supplier_id ? $('#m_supplier option:selected').text() : '';
        let curr = $('#m_curr').val();
        let price = $('#m_price').val() || 0;
        let cons = $('#m_cons').val() || 0;
        let unit = $('#m_unit').val();
        let allow = $('#m_allowance').val() || 0;

        let px_idr_raw = $('#m_price').data('raw_idr') || 0;
        let px_usd_raw = $('#m_price').data('raw_usd') || 0;
        let val_idr_raw = $('#m_val_idr').data('raw') || 0;
        let val_usd_raw = $('#m_val_usd').data('raw') || 0;

        let set_val = $('#m_set').val() || '';
        let set_text = set_val ? $('#m_set option:selected').text() : '';

        let order_qty = parseFloat($('#qty').val().replace(/,/g, '')) || 0;
        let qty_bom_js = Math.ceil((1 + (allow / 100)) * order_qty * cons);
        let tot_val_js = qty_bom_js * px_idr_raw;

        if (!item_id) { Swal.fire('Peringatan', 'Pilih Item terlebih dahulu!', 'warning'); return; }

        let header_type = $('#type').val();
        if (cat !== 'Other Cost' && header_type === 'multiple' && set_val === '') {
            Swal.fire('Peringatan', 'Tipe Costing MULTIPLE, silakan Pilih Set terlebih dahulu!', 'warning');
            return;
        }

        Swal.fire({ title: 'Menyimpan...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });

        $.ajax({
            url: "{{ route('update-detail-costing') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                id_detail: id_detail,
                id_costing: '{{ $costing->id }}',
                category: cat,
                item: item_id,
                desc: desc,
                supplier: supplier_id,
                curr: curr,
                price: price,
                cons: cons,
                unit: unit,
                price_px_idr: px_idr_raw,
                price_px_usd: px_usd_raw,
                allowance: allow,
                set: set_val,
                value_idr: val_idr_raw,
                value_usd: val_usd_raw
            },
            success: function(res) {
                if(res.status == 200) {
                    Swal.close();
                    $('#modal_edit_costing').modal('hide');

                    let val_idr_text = val_idr_raw.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    let val_idr_text_other = val_idr_raw.toLocaleString('en-US', {minimumFractionDigits: 7, maximumFractionDigits: 7});
                    let val_usd_text = val_usd_raw.toLocaleString('en-US', {minimumFractionDigits: 7, maximumFractionDigits: 7});
                    let px_idr_text = px_idr_raw.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    let px_usd_text = px_usd_raw.toLocaleString('en-US', {minimumFractionDigits: 4, maximumFractionDigits: 4});

                    let qty_bom_text = qty_bom_js.toLocaleString('en-US');
                    let tot_val_text = tot_val_js.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});

                    let target_tbody = '';
                    if(cat === 'Fabric') { target_tbody = '#tbody-fabric'; }
                    else if (cat === 'Accessories Sewing') { target_tbody = '#tbody-acc-sewing'; }
                    else if (cat === 'Accessories Packing') { target_tbody = '#tbody-acc-packing'; }
                    else if (cat === 'Manufacturing') { target_tbody = '#tbody-manufacturing'; }
                    else if (cat === 'Other Cost') { target_tbody = '#tbody-other-cost'; }

                    let rowSelector = costingRowSelector(cat, id_detail);
                    let rowNo = $(rowSelector).find('.row-number').text();
                    let rowId = rowSelector.substring(1);
                    let $newRow = $(costingRowHtml(cat, rowId, id_detail, {
                        item_id: item_id, item_text: item_text, set_val: set_val, desc: desc, supplier_id: supplier_id,
                        curr: curr, price: price, cons: cons, unit: unit, allow: allow,
                        px_idr: px_idr_raw, px_usd: px_usd_raw, val_idr: val_idr_raw, val_usd: val_usd_raw,
                        qty_bom: qty_bom_js, tot_val: tot_val_js
                    }, rowNo));
                    $(rowSelector).replaceWith($newRow);
                    initializeCostingInlineRow($newRow);

                    if (cat !== 'Other Cost') {
                        sortTbody(target_tbody);
                    }

                    calculate_summary();
                } else { Swal.fire('Error', res.message, 'error'); }
            }
        });
    }

    function removeRow(btn, id, cat) {
        Swal.fire({
            title: 'Hapus Item?',
            text: "Data akan dihapus secara permanen dari sistem!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: 'Menghapus...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });

                let deleteUrl = "{{ route('delete-costing-detail', ':id') }}".replace(':id', id);

                $.ajax({
                    url: deleteUrl,
                    type: "POST",
                    data: {
                        _method: "DELETE",
                        _token: "{{ csrf_token() }}",
                        category: cat,
                        id_costing: '{{ $costing->id }}'
                    },
                    success: function(res) {
                        if(res.status == 200) {
                            Swal.close();
                            let tbody = $(btn).closest('tbody');
                            $(btn).closest('tr').remove();

                            tbody.find('tr').each(function(index) {
                                $(this).find('.row-number').text(index + 1);
                            });

                            calculate_summary();
                        } else {
                            Swal.fire('Error', res.message || 'Gagal menghapus data', 'error');
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error', 'Terjadi kesalahan pada server.', 'error');
                    }
                });
            }
        });
    }

    let savedCostingImages = $('#saved-costing-pictures [data-url]').map(function() {
        return { url: $(this).data('url'), name: $(this).data('name'), fileName: $(this).data('file'), saved: true };
    }).get();
    let pendingCostingImages = [];
    let selectedCostingImage = 0;

    function renderCostingImageCarousel() {
        const images = savedCostingImages.concat(pendingCostingImages);
        const $preview = $('#img_preview');
        const $thumbnails = $('#picture-thumbnails').empty();
        const $dots = $('#carousel-dots').empty();

        $('#no_img_text').prop('hidden', images.length > 0);
        $preview.prop('hidden', images.length === 0);
        $('#picture-counter').text(images.length ? `${selectedCostingImage + 1} / ${images.length}` : '');
        $('#picture-prev, #picture-next').prop('disabled', images.length < 2);
        $('#clear-pictures').prop('disabled', images.length === 0);

        if (!images.length) {
            $preview.attr('src', '');
            return;
        }

        selectedCostingImage = (selectedCostingImage + images.length) % images.length;
        $preview.attr('src', images[selectedCostingImage].url);
        images.forEach((image, index) => {
            const $thumbnail = $('<div>', {
                class: `costing-picture-thumb${index === selectedCostingImage ? ' active' : ''}`,
                'aria-label': `Show image ${index + 1}`
            }).append($('<img>', { src: image.url, alt: '' }))
                .on('click', function() {
                    selectedCostingImage = index;
                    renderCostingImageCarousel();
                });
            $('<button>', {
                type: 'button',
                class: 'costing-picture-remove',
                title: 'Hapus gambar',
                'aria-label': `Remove image ${index + 1}`
            }).append('<i class="fas fa-times"></i>').on('click', function(event) {
                event.stopPropagation();
                removeCostingImage(index);
            }).appendTo($thumbnail);
            $thumbnail.appendTo($thumbnails);
            $('<span>', {
                style: `display: block; width: 20px; height: 4px; border-radius: 2px; cursor: pointer; background: ${index === selectedCostingImage ? '#0d6efd' : '#e0e0e0'};`
            }).on('click', function() {
                selectedCostingImage = index;
                renderCostingImageCarousel();
            }).appendTo($dots);
        });
    }

    function removeCostingImage(index) {
        const images = savedCostingImages.concat(pendingCostingImages);
        const image = images[index];
        if (!image) return;
        const previousSelectedImage = selectedCostingImage;

        if (image.saved) {
            savedCostingImages = savedCostingImages.filter(saved => saved !== image);
            if (image.fileName && $('#clear_existing_pictures').val() !== '1') {
                $('<input>', {
                    type: 'hidden',
                    name: 'remove_picture_files[]'
                }).val(image.fileName).appendTo('#removed-costing-pictures');
            }
        } else {
            pendingCostingImages = pendingCostingImages.filter(pending => pending !== image);
            URL.revokeObjectURL(image.url);
            const transfer = new DataTransfer();
            pendingCostingImages.forEach(pending => transfer.items.add(pending.file));
            $('#upload_foto')[0].files = transfer.files;
        }

        const remainingCount = images.length - 1;
        selectedCostingImage = index < previousSelectedImage
            ? previousSelectedImage - 1
            : index === previousSelectedImage
                ? Math.min(index, remainingCount - 1)
                : previousSelectedImage;
        selectedCostingImage = Math.max(0, selectedCostingImage);
        renderCostingImageCarousel();
    }

    function addCostingImageFiles(files) {
        const selectedFiles = Array.from(files || []);
        const invalidFile = selectedFiles.find(file => !file.type.startsWith('image/'));
        if (invalidFile) {
            $('#upload_foto').val('');
            Swal.fire('File tidak valid', `${invalidFile.name} bukan file gambar.`, 'error');
            return;
        }

        const existingFiles = pendingCostingImages.map(image => image.file);
        const allFiles = existingFiles.concat(selectedFiles).filter((file, index, filesList) =>
            filesList.findIndex(candidate => candidate.name === file.name && candidate.size === file.size && candidate.lastModified === file.lastModified) === index
        );
        const input = $('#upload_foto')[0];
        const transfer = new DataTransfer();
        allFiles.forEach(file => transfer.items.add(file));
        input.files = transfer.files;

        const existingKeys = new Set(pendingCostingImages.map(image => image.key));
        selectedFiles.forEach(file => {
            const key = `${file.name}:${file.size}:${file.lastModified}`;
            if (existingKeys.has(key)) return;
            pendingCostingImages.push({ url: URL.createObjectURL(file), name: file.name, key, file });
            existingKeys.add(key);
        });
        selectedCostingImage = Math.max(0, savedCostingImages.length + pendingCostingImages.length - selectedFiles.length);
        renderCostingImageCarousel();
    }

    $('#btn-upload-now, #picture-dropzone').on('click', function() {
        $('#upload_foto').trigger('click');
    });
    $('#picture-dropzone').on('keydown', function(event) {
        if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            $('#upload_foto').trigger('click');
        }
    }).on('dragover', function(event) {
        event.preventDefault();
        $(this).addClass('dragging');
    }).on('dragleave drop', function(event) {
        event.preventDefault();
        $(this).removeClass('dragging');
    }).on('drop', function(event) {
        addCostingImageFiles(event.originalEvent.dataTransfer.files);
    });
    $('#upload_foto').on('change', function() {
        addCostingImageFiles(this.files);
    });
    $('#clear-pictures').on('click', function() {
        $('#upload_foto').val('');
        pendingCostingImages.forEach(image => URL.revokeObjectURL(image.url));
        pendingCostingImages = [];
        savedCostingImages = [];
        $('#removed-costing-pictures').empty();
        $('#clear_existing_pictures').val('1');
        selectedCostingImage = 0;
        renderCostingImageCarousel();
    });
    $('#picture-prev').on('click', function() {
        selectedCostingImage--;
        renderCostingImageCarousel();
    });
    $('#picture-next').on('click', function() {
        selectedCostingImage++;
        renderCostingImageCarousel();
    });
    renderCostingImageCarousel();

    function calculate_summary() {
        calculate_summary_core();
        if (typeof xlPaginateAll === 'function') xlPaginateAll();
    }

    function calculate_summary_core() {

        // ============================
        // 0. INPUT DASAR & CACHE SELECTOR
        // ============================
        let order_qty   = parseFloat($('#qty').val().replace(/,/g, '')) || 0;
        let rate_to_idr  = parseFloat($('#rate_to_idr').val()) || 0;
        let header_curr  = $('#curr option:selected').text().trim().toUpperCase(); // <-- dipakai utk konversi confirm_price

        let cat_totals = {
            'Fabric':               { idr: 0, usd: 0, val: 0, bom: 0, prefix: 'fab',  sets: {} },
            'Accessories Sewing':   { idr: 0, usd: 0, val: 0, bom: 0, prefix: 'sew',  sets: {} },
            'Accessories Packing':  { idr: 0, usd: 0, val: 0, bom: 0, prefix: 'pack', sets: {} },
            'Manufacturing':        { idr: 0, usd: 0, val: 0, bom: 0, prefix: 'mfg',  sets: {} }
        };
        let grand_totals = { val: 0, bom: 0, sets: {} };

        // ============================
        // 1. SIAPKAN DAFTAR SET AKTIF (khusus type = multiple)
        // ============================
        let active_sets = new Set();
        if ($('#type').val() === 'multiple') {
            $('#txt_set option').each(function() {
                let txt = $(this).text().toUpperCase().trim();
                if (txt && txt !== '-- SET --') active_sets.add(txt);
            });
        }

        let sets_arr = Array.from(active_sets).sort(function(a, b) {
            let orderA = masterSetOrder[a] !== undefined ? masterSetOrder[a] : 999;
            let orderB = masterSetOrder[b] !== undefined ? masterSetOrder[b] : 999;
            return orderA - orderB;
        });

        sets_arr.forEach(s => {
            for (let c in cat_totals) { cat_totals[c].sets[s] = { idr: 0, usd: 0, val: 0, bom: 0 }; }
            grand_totals.sets[s] = { val: 0, bom: 0 };
        });

        // ============================
        // 2. HITUNG TOTAL PER BARIS (Fabric / Sewing / Packing / Manufacturing)
        // ============================
        $('.row-costing').each(function() {
            let cat = $(this).data('category');
            if (cat === 'Other Cost' || !cat_totals[cat]) return;

            let val_idr = parseFloat($(this).find('.val-idr-td').data('val')) || 0;
            let val_usd = parseFloat($(this).find('.val-usd-td').data('val')) || 0;
            let cons    = parseFloat($(this).find('.cons-td').data('val')) || 0;
            let allow   = parseFloat($(this).find('.allow-td').data('val')) || 0;
            let px_idr  = parseFloat($(this).find('.px-idr-td').data('val')) || 0;

            let qty_bom = Math.ceil((1 + (allow / 100)) * order_qty * cons);
            let tot_val = qty_bom * px_idr;

            let $set = $(this).find('.set-td select');
            let set_name = ($set.length ? $set.find('option:selected').text() : $(this).find('.set-td').text()).toUpperCase().trim();

            $(this).find('.qty-bom-td').data('val', qty_bom).text(qty_bom.toLocaleString('en-US'));
            $(this).find('.tot-val-td').data('val', tot_val).text(tot_val.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));

            cat_totals[cat].idr += val_idr;
            cat_totals[cat].usd += val_usd;
            cat_totals[cat].val += tot_val;
            cat_totals[cat].bom += qty_bom;

            grand_totals.val += tot_val;
            grand_totals.bom += qty_bom;

            if (set_name && cat_totals[cat].sets[set_name]) {
                cat_totals[cat].sets[set_name].idr += val_idr;
                cat_totals[cat].sets[set_name].usd += val_usd;
                cat_totals[cat].sets[set_name].val += tot_val;
                cat_totals[cat].sets[set_name].bom += qty_bom;

                grand_totals.sets[set_name].val += tot_val;
                grand_totals.sets[set_name].bom += qty_bom;
            }
        });

        let base_material_idr = cat_totals['Fabric'].idr + cat_totals['Accessories Sewing'].idr + cat_totals['Accessories Packing'].idr;
        let base_material_usd = cat_totals['Fabric'].usd + cat_totals['Accessories Sewing'].usd + cat_totals['Accessories Packing'].usd;

        // ============================
        // 3. OTHER COST + OVERHEAD (overhead = % dari base_material)
        // ============================
        let sum_oth_norm_idr = 0, sum_oth_norm_usd = 0;
        let overhead_row = null;

        $('#tbody-other-cost tr').each(function() {
            let name      = $(this).find('.item-name').text().toUpperCase();
            let val_idr   = parseFloat($(this).find('.val-idr-td-other').data('val')) || 0;
            let allow_val = parseFloat($(this).find('.allow-td').data('val')) || 0;

            if (name.includes('OVERHEAD')) {
                overhead_row = $(this);
            }

            let val_usd = val_idr / rate_to_idr;
            let $usdCell = $(this).find('.val-usd-td-other');
            $usdCell.data('val', val_usd);
            let $usdCtl = $usdCell.find('.costing-inline-control');
            if ($usdCtl.length) {
                if (document.activeElement !== $usdCtl[0]) $usdCtl.val(Number(val_usd.toFixed(7)));
            } else {
                $usdCell.text(val_usd.toLocaleString('en-US', { minimumFractionDigits: 7, maximumFractionDigits: 7 }));
            }
            let $allowCell = $(this).find('.allow-td');
            if (!$allowCell.find('.costing-inline-control').length) {
                $allowCell.text(allow_val > 0 ? allow_val.toFixed(2) + '%' : '');
            }

            sum_oth_norm_idr += val_idr;
            sum_oth_norm_usd += val_usd;
        });

        let tot_other_idr = sum_oth_norm_idr;
        let tot_other_usd = sum_oth_norm_usd;

        $('#tot-other-idr').text(tot_other_idr.toLocaleString('en-US', { minimumFractionDigits: 7, maximumFractionDigits: 7 }));
        $('#tot-other-usd').text(tot_other_usd.toLocaleString('en-US', { minimumFractionDigits: 7, maximumFractionDigits: 7 }));

        // ============================
        // 4. G&A (dihitung dari material + manufacturing + other cost)
        // ============================
        let base_ga_idr = base_material_idr + cat_totals['Manufacturing'].idr + tot_other_idr;
        let base_ga_usd = base_material_usd + cat_totals['Manufacturing'].usd + tot_other_usd;

        let input_ga_pct = parseFloat($('#ga_percent').val()) || 0;
        let ga_idr = base_ga_idr * (input_ga_pct / 100);
        let ga_usd = base_ga_usd * (input_ga_pct / 100);

        let grand_idr = base_ga_idr + ga_idr;
        let grand_usd = grand_idr / rate_to_idr;

        $('#val-ga-idr').text(ga_idr.toLocaleString('en-US', { minimumFractionDigits: 7, maximumFractionDigits: 7 }));
        $('#val-ga-usd').text(ga_usd.toLocaleString('en-US', { minimumFractionDigits: 7, maximumFractionDigits: 7 }));

        let ga_pct = (grand_idr > 0) ? (ga_idr / grand_idr) * 100 : 0;
        $('.pct-ga').text(ga_pct.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '%');

        let pct_other = (grand_idr > 0) ? (tot_other_idr / grand_idr) * 100 : 0;
        $('#tot-other-pct').text(pct_other.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '%');

        $('.row-costing').each(function() {
            let val_idr = parseFloat($(this).find('.val-idr-td, .val-idr-td-other').data('val')) || 0;
            let pct = (grand_idr > 0) ? (val_idr / grand_idr) * 100 : 0;
            $(this).find('.pct-td').text(pct.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '%');
        });

        // ============================
        // 5. RENDER TFOOT PER KATEGORI (Fabric/Sewing/Packing/Manufacturing)
        // ============================
        for (let c in cat_totals) {
            let obj   = cat_totals[c];
            let pfx   = obj.prefix;
            let title = c.toUpperCase();

            let tfoot_html = '';

            sets_arr.forEach(s => {
                let d = obj.sets[s];
                tfoot_html += `
                    <tr style="background-color: #f8f9fa;">
                        <td colspan="13" class="text-left">${title} - TOTAL ${s}</td>
                        <td class="text-right">${d.idr.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                        <td class="text-right">${d.usd.toLocaleString('en-US', { minimumFractionDigits: 4, maximumFractionDigits: 4 })}</td>
                        <td></td>
                        <td class="text-right">${d.bom.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 0 })}</td>
                        <td class="text-right">${d.val.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                        <td></td>
                    </tr>
                `;
            });

            tfoot_html += `
                <tr class="bg-warning text-dark">
                    <td colspan="13" class="text-left">TOTAL ${title} :</td>
                    <td class="text-right">${obj.idr.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                    <td class="text-right">${obj.usd.toLocaleString('en-US', { minimumFractionDigits: 4, maximumFractionDigits: 4 })}</td>
                    <td class="text-center"></td>
                    <td class="text-right">${obj.bom.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 0 })}</td>
                    <td class="text-right">${obj.val.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                    <td></td>
                </tr>
            `;

            $('#tfoot-' + pfx).html(tfoot_html);
        }

        // ============================
        // 6. RENDER GRAND TOTAL TABLE
        // ============================
        let grand_set_html = '';
        sets_arr.forEach(s => {
            let d = grand_totals.sets[s];
            grand_set_html += `
                <tr style="font-weight: bold; background-color: #f8f9fa;">
                    <td width="40%">TOTAL ${s}</td>
                    <td width="30%" class="text-center">${d.bom.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 0 })}</td>
                    <td width="30%" class="text-center">${d.val.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                </tr>
            `;
        });

        $('#table-grand-total').html(`
            <tr style="border-top: 2px solid #000; font-weight: bold; background-color: #f2f2f2;">
                <td width="40%"></td>
                <td width="30%" class="text-center">TOTAL QTY BOM</td>
                <td width="30%" class="text-center">TOTAL VALUE</td>
            </tr>
            ${grand_set_html}
            <tr style="font-weight: bold;">
                <td>GRAND TOTAL</td>
                <td class="text-center text-dark">${grand_totals.bom.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 0 })}</td>
                <td class="text-center text-dark">${grand_totals.val.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
            </tr>
        `);

        // ============================
        // 7. VAT & PROFIT
        // ============================
        let input_vat      = parseFloat($('input[name="vat"]').val()) || 0;
        let shipment_type   = $('#shipment_type').val();
        let actual_vat_pct  = (shipment_type === 'export') ? 0 : input_vat;
        $('#vat-label-pct').text(actual_vat_pct + '%');

        let vat_multiplier = 1 + (actual_vat_pct / 100);
        let vat_idr     = Math.round((grand_idr * vat_multiplier) * 100) / 100;
        let vat_usd     = Math.round((grand_usd * vat_multiplier) * 10000000) / 10000000;
        let profit_idr  = Math.round((vat_idr * 1.06) * 100) / 100;
        let profit_usd  = Math.round((vat_usd * 1.06) * 10000000) / 10000000;

        // ============================
        // 8. BALANCE OTHER COST (SUGGEST) — FIX: konversi confirm_price sesuai currency header
        // ============================
        let confirm_price_raw = parseFloat($('#confirm_price').val()) || 0;
        let suggest_idr = 0;
        let suggest_usd = 0;

        if (confirm_price_raw !== 0) {
            // confirm_price diinput sesuai currency di header (bisa USD atau IDR),
            // sedangkan grand_idr selalu dalam IDR -> harus disamakan dulu satuannya.
            let confirm_price_idr = (header_curr === 'USD')
                ? confirm_price_raw * rate_to_idr
                : confirm_price_raw;

            let diff_total = confirm_price_idr - grand_idr;

            let oh_allow_pct = 0;
            if (overhead_row) {
                oh_allow_pct = parseFloat(overhead_row.find('.allow-td').data('val')) || 0;
            }
            let oh_multiplier = 1 + (oh_allow_pct / 100);

            suggest_idr = diff_total / (oh_multiplier * 1.03);
            suggest_usd = suggest_idr / rate_to_idr;
        }

        // ============================
        // 9. RENDER RINGKASAN AKHIR
        // ============================
        $('#grand-tot-idr').text(grand_idr.toLocaleString('en-US', { minimumFractionDigits: 7, maximumFractionDigits: 7 }));
        $('#grand-tot-usd').text(grand_usd.toLocaleString('en-US', { minimumFractionDigits: 7, maximumFractionDigits: 7 }));

        $('#val-vat-idr').text(vat_idr.toLocaleString('en-US', { minimumFractionDigits: 7, maximumFractionDigits: 7 }));
        $('#val-vat-usd').text(vat_usd.toLocaleString('en-US', { minimumFractionDigits: 7, maximumFractionDigits: 7 }));

        $('#val-suggest-idr').text(suggest_idr.toLocaleString('en-US', { minimumFractionDigits: 7, maximumFractionDigits: 7 }));
        $('#val-suggest-usd').text(suggest_usd.toLocaleString('en-US', { minimumFractionDigits: 7, maximumFractionDigits: 7 }));

        $('#val-profit-idr').text(profit_idr.toLocaleString('en-US', { minimumFractionDigits: 7, maximumFractionDigits: 7 }));
        $('#val-profit-usd').text(profit_usd.toLocaleString('en-US', { minimumFractionDigits: 7, maximumFractionDigits: 7 }));

        lock_type_dropdown();
    }
    /* =====================================================================
       EXCEL-LIKE GRID untuk semua tab (Fabric ... Other Cost)
       - klik / drag untuk blok sel, Shift+klik untuk perluas
       - titik biru (fill handle) di pojok kanan bawah: drag untuk copy ke bawah/atas
       - Ctrl+C / Ctrl+V (bisa paste langsung dari Excel), Ctrl+D (fill down), Delete (kosongkan)
       - Panah / Enter untuk pindah sel, Esc untuk batal pilih
       - Price / Cons / Allow berubah -> Price PX, Value IDR/USD otomatis dihitung ulang
       - 1 baris = 1 request save (di-debounce), jadi aman untuk fill banyak baris
       ===================================================================== */
    const XL = { $tbody: null, anchor: null, rect: null, dragging: false, moved: false, filling: false, fillTarget: null, sortTimers: {} };
    const XL_ATTR = {
        set: 'set', desc: 'desc', supplier: 'supplier', curr: 'curr', price: 'price', cons: 'cons', unit: 'unit',
        allowance: 'allowance', price_px_idr: 'px-idr', price_px_usd: 'px-usd', value_idr: 'value-idr', value_usd: 'value-usd'
    };
    const XL_TD = {
        price: '.price-td', cons: '.cons-td', allowance: '.allow-td',
        price_px_idr: '.px-idr-td', price_px_usd: '.px-usd-td',
        value_idr: '.val-idr-td, .val-idr-td-other', value_usd: '.val-usd-td, .val-usd-td-other'
    };

    function xlRows($tbody) { return $tbody.children('tr.row-costing'); }
    function xlHasCtrl($td) { return $td.children('.costing-inline-control').length > 0; }
    function xlCtrl($td) { return $td.children('.costing-inline-control').first(); }
    function xlTdAt($tbody, r, c) { return xlRows($tbody).eq(r).children().eq(c); }
    function xlPosOf($td) { return { r: xlRows($td.closest('tbody')).index($td.parent()), c: $td.index() }; }
    function xlInGrid(el) { return !!$(el).closest('#costing-category-content').length; }

    function xlClear() {
        $('#costing-category-content .xl-sel').removeClass('xl-sel xl-top xl-bottom xl-left xl-right');
        $('#costing-category-content .xl-fill-preview').removeClass('xl-fill-preview');
        $('#costing-category-content .xl-fill-handle').remove();
        XL.rect = null;
    }

    function xlSelect($a, $b) {
        const $tb = $a.closest('tbody');
        const pa = xlPosOf($a), pb = xlPosOf($b);
        const r0 = Math.min(pa.r, pb.r), r1 = Math.max(pa.r, pb.r);
        const c0 = Math.min(pa.c, pb.c), c1 = Math.max(pa.c, pb.c);
        xlClear();
        XL.$tbody = $tb; XL.anchor = $a; XL.rect = { r0: r0, r1: r1, c0: c0, c1: c1 };
        const $rows = xlRows($tb);
        for (let r = r0; r <= r1; r++) {
            const $tr = $rows.eq(r);
            for (let c = c0; c <= c1; c++) {
                const $td = $tr.children().eq(c);
                if (!xlHasCtrl($td)) continue;
                $td.addClass('xl-sel');
                if (r === r0) $td.addClass('xl-top');
                if (r === r1) $td.addClass('xl-bottom');
                if (c === c0) $td.addClass('xl-left');
                if (c === c1) $td.addClass('xl-right');
            }
        }
        const $last = xlTdAt($tb, r1, c1);
        if (xlHasCtrl($last)) $last.append('<span class="xl-fill-handle" title="Drag untuk copy ke atas/bawah"></span>');
    }

    function xlIsMulti() { return !!XL.rect && (XL.rect.r1 > XL.rect.r0 || XL.rect.c1 > XL.rect.c0); }

    function xlFocus($td) {
        xlShowRow($td.parent());
        const $c = xlCtrl($td);
        if (!$c.length) return;
        $c[0].focus();
        if ($c.is('input') && $c[0].select) $c[0].select();
    }

    /* ---------- set nilai sel (memicu change -> auto hitung + save) ---------- */
    function xlSet($ctrl, val) {
        val = (val === null || val === undefined) ? '' : String(val);
        if ($ctrl.is('select')) {
            const needle = val.trim().toLowerCase();
            let match = null;
            $ctrl.find('option').each(function () {
                if ($(this).val().toLowerCase() === needle || $(this).text().trim().toLowerCase() === needle) { match = $(this).val(); return false; }
            });
            if (match === null || $ctrl.val() === match) return;
            $ctrl.val(match).trigger('change');
        } else {
            val = val.trim();
            if ($ctrl.val() === val) return;
            $ctrl.val(val).trigger('change');
        }
    }

    function xlText($ctrl) {
        return $ctrl.is('select') ? $.trim($ctrl.find('option:selected').text()) : $ctrl.val();
    }

    function xlRectToTSV() {
        const R = XL.rect, lines = [];
        for (let r = R.r0; r <= R.r1; r++) {
            const cells = [];
            for (let c = R.c0; c <= R.c1; c++) {
                const $td = xlTdAt(XL.$tbody, r, c);
                cells.push(xlHasCtrl($td) ? xlText(xlCtrl($td)) : '');
            }
            lines.push(cells.join('\t'));
        }
        return lines.join('\n');
    }

    /* ---------- fill (drag handle / Ctrl+D) ---------- */
    function xlFillTo(t) {
        const R = XL.rect, $tb = XL.$tbody;
        if (!R) return;
        const n = R.r1 - R.r0 + 1;
        let from, to;
        if (t > R.r1) { from = R.r1 + 1; to = t; }
        else if (t < R.r0) { from = t; to = R.r0 - 1; }
        else return;
        for (let i = from; i <= to; i++) {
            const sr = R.r0 + ((((i - R.r0) % n) + n) % n);
            for (let c = R.c0; c <= R.c1; c++) {
                const $s = xlTdAt($tb, sr, c), $d = xlTdAt($tb, i, c);
                if (xlHasCtrl($s) && xlHasCtrl($d)) xlSet(xlCtrl($d), xlCtrl($s).val());
            }
        }
        const nr0 = Math.min(R.r0, from), nr1 = Math.max(R.r1, to);
        xlSelect(xlTdAt($tb, nr0, R.c0), xlTdAt($tb, nr1, R.c1));
    }

    function xlFillDown() {
        const R = XL.rect, $tb = XL.$tbody;
        if (!R) return;
        if (R.r1 === R.r0) {
            if (R.r0 === 0) return;
            for (let c = R.c0; c <= R.c1; c++) {
                const $s = xlTdAt($tb, R.r0 - 1, c), $d = xlTdAt($tb, R.r0, c);
                if (xlHasCtrl($s) && xlHasCtrl($d)) xlSet(xlCtrl($d), xlCtrl($s).val());
            }
        } else {
            for (let r = R.r0 + 1; r <= R.r1; r++) {
                for (let c = R.c0; c <= R.c1; c++) {
                    const $s = xlTdAt($tb, R.r0, c), $d = xlTdAt($tb, r, c);
                    if (xlHasCtrl($s) && xlHasCtrl($d)) xlSet(xlCtrl($d), xlCtrl($s).val());
                }
            }
        }
    }

    function xlClearValues() {
        const R = XL.rect;
        if (!R) return;
        for (let r = R.r0; r <= R.r1; r++) {
            for (let c = R.c0; c <= R.c1; c++) {
                const $td = xlTdAt(XL.$tbody, r, c);
                if (!xlHasCtrl($td)) continue;
                const $ctrl = xlCtrl($td);
                xlSet($ctrl, $ctrl.is('select') ? '' : ($ctrl.hasClass('text-right') ? '0' : ''));
            }
        }
    }

    /* ---------- hitung ulang & simpan per baris ---------- */
    function xlNum(v) { const n = parseFloat(String(v).replace(/[,%\s]/g, '')); return Number.isFinite(n) ? n : NaN; }
    function xlRound(n) { return Number((Math.round(n * 1e7) / 1e7).toFixed(7)); }

    function xlPut($row, field, value, syncControl) {
        const attr = XL_ATTR[field];
        $row.attr('data-' + attr, value);
        $row.data(attr, value);
        if (XL_TD[field]) $row.find(XL_TD[field]).attr('data-val', value).data('val', value);
        if (syncControl) {
            const $control = $row.find('.costing-inline-control[data-field="' + field + '"]');
            $control.val(value);
            if ($control.hasClass('costing-inline-select')) $control.trigger('change.select2');
        }
    }

    function xlRecalc($row, field) {
        const isOther = $row.attr('data-category') === 'Other Cost';
        const toIdr = parseFloat($('#rate_to_idr').val()) || 0;
        const fromIdr = parseFloat($('#rate_from_idr').val()) || 1;
        const hdr = $('#curr option:selected').text().trim().toUpperCase();
        const g = function (a) { return parseFloat($row.attr('data-' + a)) || 0; };

        if (isOther) {
            if (field === 'value_idr') xlPut($row, 'value_usd', toIdr > 0 ? xlRound(g('value-idr') / toIdr) : 0, true);
            else if (field === 'value_usd') xlPut($row, 'value_idr', xlRound(g('value-usd') * toIdr), true);
            return;
        }
        if (field === 'price') {
            const p = g('price');
            let idr, usd;
            if (hdr === 'USD') { usd = p; idr = p * toIdr; } else { idr = p; usd = p / fromIdr; }
            xlPut($row, 'price_px_idr', xlRound(idr), true);
            xlPut($row, 'price_px_usd', xlRound(usd), true);
        }
        if (['price', 'cons', 'allowance', 'price_px_idr', 'price_px_usd'].indexOf(field) !== -1) {
            const allow = 1 + (g('allowance') / 100);
            xlPut($row, 'value_idr', xlRound(g('px-idr') * g('cons') * allow), true);
            xlPut($row, 'value_usd', xlRound(g('px-usd') * g('cons') * allow), true);
        }
    }

    function saveCostingInlineField($control) {
        const $row = $control.closest('.row-costing');
        const field = $control.data('field');
        if (!XL_ATTR[field]) return;
        let value = $control.val();
        if ($control.is('input')) {
            if (field === 'desc') {
                value = String(value).trim();
            } else {
                const raw = String(value).trim();
                const n = xlNum(raw);
                if (raw !== '' && Number.isNaN(n)) { $control.addClass('is-invalid'); return; }
                value = Number.isNaN(n) ? 0 : n;
                $control.val(value);
            }
        }
        $control.removeClass('is-invalid');
        xlPut($row, field, value, false);
        xlRecalc($row, field);
        calculate_summary();
        xlQueueSave($row, field === 'set');
    }

    function xlQueueSave($row, resort) {
        clearTimeout($row.data('xlTimer'));
        $row.addClass('xl-saving').removeClass('xl-row-error');
        if (resort) $row.data('xlResort', true);
        $row.data('xlTimer', setTimeout(function () { xlSaveRow($row); }, 350));
    }

    function xlSaveRow($row) {
        if ($row.data('xlInflight')) { $row.data('xlDirty', true); return; }
        const m = ($row.attr('id') || '').match(/(\d+)$/);
        if (!m) { Swal.fire('Gagal menyimpan', 'ID detail Costing tidak valid.', 'error'); return; }
        const a = function (k, d) { const v = $row.attr('data-' + k); return (v === undefined || v === null || v === '') ? d : v; };
        $row.data('xlInflight', true);
        $.ajax({
            url: "{{ route('update-detail-costing') }}",
            type: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                id_costing: '{{ $costing->id }}',
                id_detail: m[1],
                category: $row.attr('data-category'),
                item: $row.attr('data-item'),
                desc: a('desc', ''),
                supplier: a('supplier', ''),
                curr: a('curr', $('#curr option:selected').text().trim()),
                price: a('price', 0),
                cons: a('cons', 0),
                unit: a('unit', ''),
                price_px_idr: a('px-idr', 0),
                price_px_usd: a('px-usd', 0),
                allowance: a('allowance', 0),
                set: a('set', ''),
                value_idr: a('value-idr', 0),
                value_usd: a('value-usd', 0)
            },
            success: function (response) {
                if (response.status !== 200) {
                    $row.addClass('xl-row-error');
                    Swal.fire('Gagal menyimpan', response.message || 'Perubahan item tidak tersimpan.', 'error');
                    return;
                }
                if ($row.data('xlResort')) { $row.data('xlResort', false); xlScheduleSort($row.closest('tbody')); }
            },
            error: function (xhr) {
                $row.addClass('xl-row-error');
                const message = xhr.responseJSON && xhr.responseJSON.message;
                Swal.fire('Gagal menyimpan', message || 'Perubahan item tidak tersimpan.', 'error');
            },
            complete: function () {
                $row.data('xlInflight', false);
                if ($row.data('xlDirty')) { $row.data('xlDirty', false); xlSaveRow($row); return; }
                $row.removeClass('xl-saving');
            }
        });
    }

    function xlScheduleSort($tbody) {
        const id = $tbody.attr('id');
        clearTimeout(XL.sortTimers[id]);
        XL.sortTimers[id] = setTimeout(function () {
            if ($tbody.find('tr.xl-saving').length) { xlScheduleSort($tbody); return; }
            xlClear();
            sortTbody('#' + id);
            calculate_summary();
        }, 700);
    }

    /* ---------- pagination per tab ---------- */
    function xlBuildPagers() {
        $('#costing-category-content .costing-tab-panel').each(function () {
            const $panel = $(this);
            if ($panel.find('.xl-pager').length) return;
            $panel.data('perPage', 10).data('page', 1);
            const $pager = $(
                '<div class="xl-pager">' +
                    '<div><span>Item Per Page</span> <select class="xl-per-page">' +
                        '<option value="5">5</option><option value="10" selected>10</option><option value="25">25</option>' +
                        '<option value="50">50</option><option value="0">Semua</option></select> ' +
                        '<span class="xl-total"></span></div>' +
                    '<div class="xl-pages"></div>' +
                '</div>');
            $panel.find('.table-responsive').first().after($pager);
        });
    }

    function xlPaginateAll() {
        $('#costing-category-content .costing-tab-panel').each(function () { xlPaginate($(this)); });
    }

    function xlPaginate($panel) {
        const $rows = $panel.find('tbody').first().children('tr.row-costing');
        const total = $rows.length;
        let per = parseInt($panel.data('perPage'), 10);
        if (Number.isNaN(per)) per = 10;
        const pages = per > 0 ? Math.max(1, Math.ceil(total / per)) : 1;
        let page = Math.min(Math.max(1, parseInt($panel.data('page'), 10) || 1), pages);
        $panel.data('page', page);
        $rows.each(function (i) {
            const show = per === 0 || (i >= (page - 1) * per && i < page * per);
            $(this).toggleClass('xl-hidden', !show);
        });
        const $pager = $panel.find('.xl-pager');
        $pager.find('.xl-total').text('Of ' + total);
        let html = '<button type="button" class="xl-page-btn" data-page="' + (page - 1) + '"' + (page <= 1 ? ' disabled' : '') + '>&lsaquo;</button>';
        const start = Math.max(1, Math.min(page - 2, pages - 4)), end = Math.min(pages, start + 4);
        for (let p = start; p <= end; p++) {
            html += '<button type="button" class="xl-page-btn' + (p === page ? ' active' : '') + '" data-page="' + p + '">' + p + '</button>';
        }
        html += '<button type="button" class="xl-page-btn" data-page="' + (page + 1) + '"' + (page >= pages ? ' disabled' : '') + '>&rsaquo;</button>';
        $pager.find('.xl-pages').html(html);
    }

    function xlShowRow($row) {
        const $panel = $row.closest('.costing-tab-panel');
        if (!$panel.length) return;
        const per = parseInt($panel.data('perPage'), 10);
        if (per > 0) {
            const idx = $panel.find('tbody').first().children('tr.row-costing').index($row);
            if (idx >= 0) $panel.data('page', Math.floor(idx / per) + 1);
        }
        xlPaginate($panel);
    }

    /* ---------- checkbox & hapus massal ---------- */
    function xlUpdateBulk() {
        const n = $('#costing-category-content .xl-row-check:checked').length;
        $('#xl-bulk-delete').prop('hidden', n === 0).html('<i class="fas fa-trash"></i> Hapus (' + n + ')');
    }

    function xlBulkDelete() {
        const list = $('#costing-category-content .xl-row-check:checked').closest('tr.row-costing').get();
        if (!list.length) return;
        Swal.fire({
            title: 'Hapus ' + list.length + ' item?',
            text: 'Data akan dihapus secara permanen dari sistem!',
            icon: 'warning', showCancelButton: true,
            confirmButtonColor: '#d33', cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!', cancelButtonText: 'Batal'
        }).then(function (result) {
            if (!result.isConfirmed) return;
            Swal.fire({ title: 'Menghapus...', allowOutsideClick: false, didOpen: function () { Swal.showLoading(); } });
            let failed = 0;
            const next = function () {
                if (!list.length) {
                    xlClear();
                    $('#costing-category-content tbody').each(function () {
                        $(this).children('tr.row-costing').each(function (i) { $(this).find('.row-number').text(i + 1); });
                    });
                    calculate_summary();
                    xlUpdateBulk();
                    if (failed) Swal.fire('Sebagian gagal', failed + ' item gagal dihapus.', 'error'); else Swal.close();
                    return;
                }
                const $tr = $(list.shift());
                const id = ($tr.attr('id') || '').match(/(\d+)$/)[1];
                $.ajax({
                    url: "{{ route('delete-costing-detail', ':id') }}".replace(':id', id),
                    type: 'POST',
                    data: { _method: 'DELETE', _token: "{{ csrf_token() }}", category: $tr.attr('data-category'), id_costing: '{{ $costing->id }}' }
                }).done(function (res) {
                    if (res.status == 200) $tr.remove(); else failed++;
                }).fail(function () { failed++; }).always(next);
            };
            next();
        });
    }

    /* ---------- bind semua event ---------- */
    function xlInit() {
        xlBuildPagers();
        xlPaginateAll();

        // pagination
        $(document).on('change', '.xl-per-page', function () {
            const $panel = $(this).closest('.costing-tab-panel');
            $panel.data('perPage', parseInt($(this).val(), 10)).data('page', 1);
            xlPaginate($panel);
        });
        $(document).on('click', '.xl-page-btn', function () {
            const $panel = $(this).closest('.costing-tab-panel');
            $panel.data('page', parseInt($(this).data('page'), 10));
            xlPaginate($panel);
        });

        // checkbox
        $(document).on('change', '.xl-check-all', function () {
            const on = this.checked;
            $(this).closest('table').find('tbody tr.row-costing:not(.xl-hidden)').each(function () {
                $(this).find('.xl-row-check').prop('checked', on);
                $(this).toggleClass('xl-row-selected', on);
            });
            xlUpdateBulk();
        });
        $(document).on('change', '.xl-row-check', function () {
            $(this).closest('tr').toggleClass('xl-row-selected', this.checked);
            xlUpdateBulk();
        });
        $('#xl-bulk-delete').on('click', xlBulkDelete);

        // simpan saat nilai sel berubah
        $('#costing-category-content').on('change', '.costing-inline-control', function () {
            saveCostingInlineField($(this));
        });

        // pilih sel dengan mouse
        $(document).on('mousedown', '#costing-category-content .xl-fill-handle', function (e) {
            e.preventDefault(); e.stopPropagation();
            XL.filling = true; XL.fillTarget = null;
            $('body').addClass('xl-dragging');
        });
        $(document).on('mousedown', '#costing-category-content tbody td', function (e) {
            if ($(e.target).hasClass('xl-fill-handle') || e.which !== 1) return;
            const $td = $(this);
            if (!xlHasCtrl($td)) return;
            if (e.shiftKey && XL.anchor && XL.$tbody && XL.$tbody[0] === $td.closest('tbody')[0]) {
                e.preventDefault();
                xlSelect(XL.anchor, $td);
                return;
            }
            XL.dragging = true; XL.moved = false;
            xlSelect($td, $td);
        });
        $(document).on('mouseenter', '#costing-category-content tbody td', function () {
            const $td = $(this);
            if (XL.filling && XL.rect) {
                if ($td.closest('tbody')[0] !== XL.$tbody[0]) return;
                const t = xlPosOf($td).r, R = XL.rect;
                $('#costing-category-content .xl-fill-preview').removeClass('xl-fill-preview');
                XL.fillTarget = t;
                let from = null, to = null;
                if (t > R.r1) { from = R.r1 + 1; to = t; } else if (t < R.r0) { from = t; to = R.r0 - 1; }
                if (from !== null) {
                    for (let r = from; r <= to; r++) for (let c = R.c0; c <= R.c1; c++) {
                        const $d = xlTdAt(XL.$tbody, r, c);
                        if (xlHasCtrl($d)) $d.addClass('xl-fill-preview');
                    }
                }
                return;
            }
            if (XL.dragging && XL.anchor && !$td.is(XL.anchor) && xlHasCtrl($td) && $td.closest('tbody')[0] === XL.$tbody[0]) {
                if (!XL.moved) { XL.moved = true; $('body').addClass('xl-dragging'); }
                xlSelect(XL.anchor, $td);
            }
        });
        $(document).on('mouseup', function () {
            if (XL.filling) {
                XL.filling = false;
                $('#costing-category-content .xl-fill-preview').removeClass('xl-fill-preview');
                if (XL.fillTarget !== null) xlFillTo(XL.fillTarget);
                XL.fillTarget = null;
            }
            if (XL.dragging && XL.moved && XL.anchor) xlCtrl(XL.anchor)[0].focus({ preventScroll: true });
            XL.dragging = false; XL.moved = false;
            $('body').removeClass('xl-dragging');
        });
        $(document).on('mousedown', function (e) {
            if (!$(e.target).closest('#costing-category-content, .swal2-container').length) xlClear();
        });
        $(document).on('focusin', '#costing-category-content .costing-inline-control', function () {
            const $td = $(this).parent();
            if (!$td.hasClass('xl-sel')) xlSelect($td, $td);
        });

        // keyboard
        $(document).on('keydown', '#costing-category-content .costing-inline-control', function (e) {
            const $c = $(this), $td = $c.parent(), key = e.key, ctrl = e.ctrlKey || e.metaKey;
            if (ctrl && (key === 'd' || key === 'D')) { e.preventDefault(); xlFillDown(); return; }
            if (ctrl || e.altKey) return;
            if (key === 'Escape') { xlClear(); return; }
            if (key === 'Delete' && xlIsMulti()) { e.preventDefault(); xlClearValues(); return; }

            const isInput = $c.is('input'), el = $c[0];
            let dr = 0, dc = 0;
            if (key === 'ArrowDown') dr = 1;
            else if (key === 'ArrowUp') dr = -1;
            else if (key === 'Enter') dr = e.shiftKey ? -1 : 1;
            else if (isInput && key === 'ArrowRight' && !e.shiftKey && el.selectionStart === el.selectionEnd && el.selectionEnd === el.value.length) dc = 1;
            else if (isInput && key === 'ArrowLeft' && !e.shiftKey && el.selectionStart === el.selectionEnd && el.selectionStart === 0) dc = -1;
            else return;
            if (!isInput && (key === 'ArrowDown' || key === 'ArrowUp')) return; // biarkan dropdown berganti pilihan

            const $tb = $td.closest('tbody'), p = xlPosOf($td), total = xlRows($tb).length;
            let r = p.r + dr, c = p.c + dc, $target = $();
            if (dr !== 0) {
                if (r >= 0 && r < total) $target = xlTdAt($tb, r, c);
            } else {
                const cols = xlRows($tb).eq(r).children().length;
                while (c >= 0 && c < cols && !xlHasCtrl(xlTdAt($tb, r, c))) c += dc;
                if (c >= 0 && c < cols) $target = xlTdAt($tb, r, c);
            }
            if (!$target.length || !xlHasCtrl($target)) return;
            e.preventDefault();
            if (e.shiftKey && (key === 'ArrowDown' || key === 'ArrowUp') && XL.anchor) {
                xlSelect(XL.anchor, $target);
                xlShowRow($target.parent());
                $target.children('.costing-inline-control')[0].focus();
            } else {
                xlSelect($target, $target);
                xlFocus($target);
            }
        });

        // copy & paste (kompatibel dengan Excel)
        $(document).on('copy', function (e) {
            const ae = document.activeElement;
            if (!XL.rect || !(xlInGrid(ae) || ae === document.body)) return;
            const hasTextSel = ae && typeof ae.selectionStart === 'number' && ae.selectionStart !== ae.selectionEnd;
            if (!xlIsMulti() && hasTextSel) return;
            e.preventDefault();
            e.originalEvent.clipboardData.setData('text/plain', xlRectToTSV());
        });
        $(document).on('paste', function (e) {
            const ae = document.activeElement;
            if (!XL.rect || !(xlInGrid(ae) || ae === document.body)) return;
            const cd = e.originalEvent.clipboardData || window.clipboardData;
            const text = cd ? cd.getData('text') : '';
            if (!text) return;
            const clean = text.replace(/\r/g, '').replace(/\n+$/, '');
            const isGrid = /[\t\n]/.test(clean);
            const multi = xlIsMulti();
            if (!isGrid && !multi && $(ae).is('input')) return; // paste biasa di dalam input
            e.preventDefault();
            const R = XL.rect, $tb = XL.$tbody;
            if (!isGrid) {
                for (let r = R.r0; r <= R.r1; r++) for (let c = R.c0; c <= R.c1; c++) {
                    const $td = xlTdAt($tb, r, c);
                    if (xlHasCtrl($td)) xlSet(xlCtrl($td), clean);
                }
                return;
            }
            const data = clean.split('\n').map(function (l) { return l.split('\t'); });
            let maxC = 0;
            data.forEach(function (cols, i) {
                maxC = Math.max(maxC, cols.length);
                cols.forEach(function (v, j) {
                    const $td = xlTdAt($tb, R.r0 + i, R.c0 + j);
                    if ($td.length && xlHasCtrl($td)) xlSet(xlCtrl($td), v);
                });
            });
            const lastR = Math.min(xlRows($tb).length - 1, R.r0 + data.length - 1);
            let lastC = R.c0 + maxC - 1;
            while (lastC > R.c0 && !xlHasCtrl(xlTdAt($tb, lastR, lastC))) lastC--;
            xlSelect(xlTdAt($tb, R.r0, R.c0), xlTdAt($tb, lastR, lastC));
        });
    }
</script>
@endsection
