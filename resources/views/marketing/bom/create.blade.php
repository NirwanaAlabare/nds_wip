@extends('layouts.index')

@section('custom-link')
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <style>
        :root {
            --sb-navy: #0f172a;
            --sb-blue: #3085d6;
            --sb-blue-dark: #1e3a8a;
            --sb-border: #e2e8f0;
        }

        /* ============ CARD UTAMA ============ */
        .card-sb,
        .card.card-primary,
        .card.card-success,
        .card.card-outline.card-info {
            border: none;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(30, 41, 59, 0.08);
        }

        .card.card-primary > .card-header,
        .card.card-success > .card-header,
        .card.card-outline.card-info > .card-header,
        .card-header.bg-sb {
            background: var(--sb-navy) !important;
            border: none !important;
            padding: 1.4rem 2rem;
            position: relative;
        }

        .card.card-primary > .card-header::before,
        .card.card-success > .card-header::before {
            content: "";
            position: absolute;
            left: 0; top: 0; bottom: 0;
            width: 5px;
            background: repeating-linear-gradient(180deg, var(--sb-blue) 0px, var(--sb-blue) 10px, transparent 10px, transparent 20px);
        }

        .card.card-primary > .card-header .card-title,
        .card.card-success > .card-header .card-title,
        .card-header.bg-sb .card-title {
            font-size: 1.05rem;
            font-weight: 700;
            color: #f8fafc !important;
            margin: 0;
        }

        .card.card-primary > .card-header .card-eyebrow,
        .card.card-success > .card-header .card-eyebrow {
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 1.4px;
            text-transform: uppercase;
            color: #5aa9f0;
            display: block;
            margin-bottom: 4px;
        }

        .card.card-primary > .card-header .btn-primary,
        .card.card-success > .card-header .btn-primary {
            border-radius: 8px;
            font-weight: 600;
            background: linear-gradient(135deg, var(--sb-blue), var(--sb-blue-dark));
            border: none;
        }

        .card.card-outline.card-info {
            border-top: 3px solid var(--sb-blue) !important;
        }
        .card.card-outline.card-info > .card-header {
            background: #f4f8fd !important;
            border-bottom: 1px solid #e7ecf3;
        }
        .card.card-outline.card-info .card-title {
            color: var(--sb-blue-dark) !important;
        }

        .card-body {
            background: #fbfcff;
        }

        /* ============ FORM CONTROLS ============ */
        .form-label,
        .form-group label {
            font-size: 12.5px;
            font-weight: 700;
            color: #475569;
        }

        .form-control,
        .select2-container--bootstrap4 .select2-selection {
            border-radius: 8px;
            border: 1.5px solid var(--sb-border);
        }
        .form-control:focus,
        .select2-container--bootstrap4.select2-container--focus .select2-selection {
            border-color: var(--sb-blue) !important;
            box-shadow: 0 0 0 3px rgba(48, 133, 214, 0.12) !important;
        }

        /* ============ SECTION CARD (pengelompokan) ============ */
        .form-section {
            background: #fff;
            border: 1px solid #eef1f5;
            border-radius: 14px;
            padding: 1.2rem 1.4rem 0.9rem;
            margin-bottom: 1.3rem;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.03);
        }
        .form-section-title {
            font-size: 12.5px;
            font-weight: 700;
            color: var(--sb-navy);
            text-transform: uppercase;
            letter-spacing: 0.4px;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 1rem;
            padding-bottom: 0.6rem;
            border-bottom: 1px solid #eef1f5;
        }
        .form-section-title i { color: var(--sb-blue); font-size: 12.5px; }

        /* ============ MODAL TAMBAH WARNA/SIZE ============ */
        .modal-content {
            border: none;
            border-radius: 14px;
            overflow: hidden;
        }
        #bom-item-modal .modal-dialog {
            width: calc(100% - 1rem);
            max-width: 1140px;
            margin: .5rem auto;
        }
        #bom-item-modal .modal-content {
            max-height: calc(100vh - 1rem);
            display: flex;
            flex-direction: column;
        }
        #bom-item-modal .modal-body {
            min-height: 0;
            overflow-y: auto;
        }
        #bom-item-modal .modal-footer {
            flex: 0 0 auto;
            background: #fff;
            border-top: 1px solid #e1e7ef;
        }
        .modal-header.bg-sb {
            background: var(--sb-navy) !important;
        }

        .btn-success {
            border-radius: 8px;
            font-weight: 700;
        }

        .card.card-primary > .card-header.bom-create-header {
            padding: 0.9rem 1.4rem;
            background: #fff !important;
        }
        .card.card-primary > .card-header.bom-create-header::before {
            content: none;
        }
        .card.card-primary > .card-header.bom-create-header .card-title {
            color: #294d68 !important;
            font-size: 1.1rem;
        }

        .bom-summary-cards {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 12px;
            margin-bottom: 16px;
        }
        .bom-summary-card {
            min-width: 0;
            min-height: 64px;
            padding: 9px 12px;
            border: 1px solid #e1e7ef;
            border-radius: 10px;
            background: #fff;
            box-shadow: 0 3px 8px rgba(30, 41, 59, .10);
        }
        .bom-summary-card span,
        .bom-summary-item span {
            display: block;
            overflow: hidden;
            color: #ababab;
            font-size: 11px;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .bom-summary-card strong {
            display: block;
            overflow: hidden;
            margin-top: 3px;
            color: #284a63;
            font-size: 14px;
            font-weight: 600;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .bom-top-layout {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
            gap: 14px;
            margin-bottom: 18px;
        }
        .bom-info-column {
            display: flex;
            min-width: 0;
            flex-direction: column;
            gap: 12px;
        }
        .bom-overview-section {
            min-width: 0;
            overflow: hidden;
            border: 1px solid #e1e7ef;
            border-radius: 10px;
            background: #fff;
            box-shadow: 0 3px 8px rgba(30, 41, 59, .10);
        }
        .bom-overview-title {
            margin: 0 12px;
            padding: 8px 0 6px;
            border-bottom: 1px solid #dce4ec;
            color: #284a63;
            font-size: 13px;
            font-weight: 600;
        }
        .bom-overview-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            column-gap: 20px;
            padding: 6px 12px 8px;
        }
        .bom-summary-item {
            display: flex;
            min-width: 0;
            min-height: 30px;
            flex-direction: column;
            justify-content: center;
            gap: 2px;
            padding: 3px 0;
            border-bottom: 1px solid #e7e7e7;
        }
        .bom-summary-item strong {
            overflow: hidden;
            color: #284a63;
            font-size: 11px;
            font-weight: 600;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .bom-review-section {
            display: flex;
            flex-direction: column;
        }
        .bom-image-review {
            display: flex;
            min-height: 205px;
            flex: 1;
            align-items: center;
            justify-content: center;
            gap: 18px;
            padding: 10px;
        }
        .bom-image-nav {
            width: 28px;
            height: 28px;
            flex: 0 0 auto;
            border: 0;
            border-radius: 50%;
            background: #f1f3f5;
            color: #7b8791;
        }
        .bom-image-preview {
            display: flex;
            min-width: 120px;
            min-height: 160px;
            align-items: center;
            justify-content: center;
            color: #64748b;
            font-size: 11px;
            text-align: center;
        }
        .bom-image-preview img {
            max-width: min(100%, 300px);
            max-height: 190px;
            object-fit: contain;
        }
        .bom-image-thumbnails {
            display: flex;
            min-height: 52px;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 0 10px 6px;
            overflow-x: auto;
        }
        .bom-image-thumbnail {
            display: flex;
            width: 42px;
            height: 42px;
            flex: 0 0 auto;
            align-items: center;
            justify-content: center;
            padding: 2px;
            border: 1px solid #d8e0e9;
            border-radius: 4px;
            background: #fff;
            opacity: .72;
        }
        .bom-image-thumbnail.is-active {
            border-color: #3b82f6;
            opacity: 1;
        }
        .bom-image-thumbnail img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        .bom-image-counter {
            min-height: 18px;
            margin-bottom: 8px;
            color: #64748b;
            font-size: 10px;
            text-align: center;
        }
        .bom-breakdown-wrap {
            overflow-x: auto;
            overflow-y: auto;
            max-height: 140px;
            margin: 0 10px 10px;
        }
        #bom-breakdown-table {
            margin-bottom: 0;
            font-size: 10px;
            min-width: 560px;
            table-layout: fixed;
        }
        #bom-breakdown-table thead th {
            position: sticky;
            top: 0;
            z-index: 1;
            background: #294d68;
            color: #fff;
            white-space: nowrap;
        }
        #bom-breakdown-table tfoot th {
            position: sticky;
            bottom: 0;
            background: #e9f5ff;
            font-weight: 700;
            white-space: nowrap;
        }
        #bom-breakdown-table th,
        #bom-breakdown-table td {
            padding: 4px 6px;
            text-align: center;
            vertical-align: middle;
            white-space: nowrap;
        }
        #bom-breakdown-table .bom-breakdown-total { background: #a8c9ff; font-weight: 700; }
        #bom-breakdown-table .bom-breakdown-label { text-align: left; }
        #section-list-item {
            overflow: hidden;
            border: 1px solid #e1e7ef;
            border-radius: 8px;
            box-shadow: 0 4px 14px rgba(30, 41, 59, .08);
        }
        #section-list-item > .card-header {
            padding: 10px 14px;
            border-bottom: 1px solid #dce4ec;
            background: #fff;
        }
        #section-list-item > .card-body { padding: 8px 12px 12px; background: #fff; }
        #bom-content-tabs {
            flex-wrap: nowrap;
            overflow-x: auto;
            border-bottom: 1px solid #dce4ec;
        }
        #bom-content-tabs .nav-link {
            padding: 8px 20px;
            border: 0;
            border-bottom: 2px solid transparent;
            color: #9aa1a8;
            font-size: 12px;
            white-space: nowrap;
        }
        #bom-content-tabs .nav-link.active {
            border-bottom-color: #294d68;
            background: transparent;
            color: #294d68;
            font-weight: 700;
        }
        .bom-content-table {
            min-width: 1250px;
            margin-bottom: 0;
            font-size: 10px;
        }
        .bom-content-table thead th {
            padding: 6px 8px;
            border-color: #c9d9f2;
            background: #cfe0ff;
            color: #26394c;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            white-space: nowrap;
            position: relative;
        }
        .bom-content-table thead th .bom-column-heading {
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .bom-content-table thead th .bom-column-dot {
            width: 5px;
            height: 5px;
            flex: 0 0 5px;
            border-radius: 50%;
        }
        .bom-content-table thead th.th-costing .bom-column-dot { background: #8b5cf6; }
        .bom-content-table thead th.th-so .bom-column-dot { background: #2878ee; }
        .bom-content-table thead th.th-input .bom-column-dot { background: #f59e0b; }
        .bom-content-table thead th.th-auto .bom-column-dot { background: #94a3b8; }
        .bom-content-table thead th .bom-column-edit {
            margin-left: 2px;
            color: #172b46;
            font-size: 11px;
        }
        .bom-content-table tbody td {
            padding: 5px 7px;
            border-color: #e1e5ea;
            vertical-align: middle;
        }
        .bom-content-table tbody tr:hover { background: #f5f8fc; }
        .bom-saved-item-value {
            min-height: 18px;
            padding: 2px 0;
            border-bottom: 1px solid #e1e5ea;
            white-space: normal;
        }
        .bom-saved-item-value:last-child { border-bottom: 0; }

        /* ============ GRID EDIT ALA EXCEL ============ */
        .bom-cell {
            position: relative;
            margin: 0 -4px;
            padding: 2px 4px;
            outline: none;
            cursor: cell;
            user-select: none;
        }
        .bom-cell.is-dirty { background: #fff7e6; }
        .bom-cell.is-pasted { background: #e0ecff; box-shadow: inset 0 0 0 1px #3b82f6; }
        .bom-cell.is-error { background: #fee2e2; box-shadow: inset 0 0 0 1px #ef4444; }
        .bom-cell.is-selected { background: #dbeafe; }
        .bom-cell.is-fill-preview { box-shadow: inset 0 0 0 1px #2563eb; background: #eff6ff; }
        .bom-cell.is-active { box-shadow: inset 0 0 0 2px #2563eb; }
        .bom-cell.is-editing { padding: 0; box-shadow: inset 0 0 0 2px #2563eb; }
        .bom-cell-input {
            width: 100%;
            min-width: 70px;
            height: 20px !important;
            padding: 0 4px !important;
            border-radius: 0;
            font-size: 10px;
        }
        select.bom-cell-input { min-width: 160px; }
        .bom-fill-handle {
            position: absolute;
            right: -4px;
            bottom: -4px;
            z-index: 2;
            width: 7px;
            height: 7px;
            border: 1px solid #fff;
            background: #2563eb;
            cursor: crosshair;
        }
        .bom-auto { background: #f1f5f9; margin: 0 -4px; padding: 2px 4px; }
        .bom-legend {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 14px;
            color: #687582;
            font-size: 10px;
        }
        .bom-legend span { display: inline-flex; align-items: center; gap: 5px; }
        .bom-legend .bom-column-dot { width: 6px; height: 6px; border-radius: 50%; }
        .bom-legend-box { width: 11px; height: 11px; border: 1px solid #cbd5e1; border-radius: 2px; }
        .bom-content-row {
            display: flex;
            min-width: 0;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }
        .bom-content-row > span { min-width: 0; }
        .bom-content-add {
            display: inline-flex !important;
            width: 25px;
            height: 25px;
            min-width: 25px;
            align-items: center;
            justify-content: center;
            padding: 0 !important;
            border: 0;
            border-radius: 4px;
            background: #10264e;
            color: #fff;
            line-height: 1;
            box-shadow: none;
        }
        .bom-content-add:hover,
        .bom-content-add:focus {
            background: #1d3d70;
            color: #fff;
        }
        .bom-content-add:disabled { opacity: .45; }
        #section-list-item .dataTables_scroll { width: 100%; }
        #section-list-item .dataTables_scrollHead,
        #section-list-item .dataTables_scrollBody { width: 100% !important; }
        #section-list-item .dataTables_scrollBody { overflow-y: auto !important; }
        #section-list-item .dataTables_info,
        #section-list-item .dataTables_length,
        #section-list-item .dataTables_paginate {
            padding-top: 8px;
            color: #687582;
            font-size: 10px;
        }
        #section-list-item .dataTables_paginate .paginate_button {
            padding: 2px 6px;
            font-size: 10px;
        }
        #section-list-item .dataTables_length select {
            height: 25px;
            margin: 0 4px;
            padding: 2px 18px 2px 5px;
            font-size: 10px;
        }
        .bom-rule-options { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 10px; }
        .bom-rule-options label {
            display: flex;
            min-height: 46px;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin: 0;
            padding: 8px;
            border: 1px solid #dce4ec;
            border-radius: 8px;
            cursor: pointer;
            text-align: center;
        }
        #bom-modal-items select, #bom-modal-items input { min-width: 110px; }
        @media (max-width: 991.98px) {
            .bom-summary-cards { grid-template-columns: repeat(3, minmax(0, 1fr)); }
            .bom-top-layout { grid-template-columns: 1fr; }
            .bom-rule-options { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (max-width: 575.98px) {
            .bom-summary-cards { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            #section-list-item > .card-body { padding: 6px; }
            #bom-content-tabs .nav-link { padding: 8px 12px; }
            .bom-content-table { min-width: 1000px; }
        }

        .bom-breadcrumb { margin-bottom: 18px; padding: 8px 10px; border-radius: 4px; background: #294d68; color: #b4b4b4; font-size: 16px; font-weight: 500; }
        .bom-breadcrumb a { color: #b4b4b4; text-decoration: none; }
        .bom-breadcrumb a:hover { color: #fff; text-decoration: underline; }
        .bom-breadcrumb strong { color: #fff; font-weight: 500; }
    </style>
@endsection

@section('content')
<form id="form-bom" action="{{ route('store-bom') }}" method="POST">
    @csrf

    <div class="card card-primary ">
        <div class="bom-breadcrumb">Marketing &nbsp;›&nbsp; Proses &nbsp;›&nbsp; <a href="{{ route('master-costing') }}">Costing</a> &nbsp;›&nbsp; <strong>Create BOM</strong></div>
        <div class="card-header bg-sb bom-create-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title">Create BOM</h5>
                    <br>
                    <small class="text-muted"><i class="fas fa-info-circle"></i> Unit: Menambahkan BOM</small>
                </div>
                <input type="hidden" name="id_costing" id="id_costing" value="{{ $selectedCostingId }}">
            </div>
        </div>
        <div class="card-body">
            <div class="bom-summary-cards" aria-label="Ringkasan BOM">
                <div class="bom-summary-card"><span>No Costing</span><strong id="bom-summary-costing">{{ $selectedCostingNo }}</strong></div>
                <div class="bom-summary-card"><span>No Worksheet</span><strong id="bom-summary-worksheet">-</strong></div>
                <div class="bom-summary-card"><span>No Sales Order</span><strong id="bom-summary-so">-</strong></div>
                <div class="bom-summary-card"><span>No Purchase Order</span><strong id="bom-summary-po">-</strong></div>
                <div class="bom-summary-card">
                    <span>No BOM
                        <i id="bom-status-badge" class="badge {{ $existingBomId ? 'badge-success' : 'badge-warning' }} ml-1"
                           title="{{ $existingBomId ? 'BOM sudah tersimpan' : 'Nomor perkiraan, BOM dibuat saat item pertama ditambahkan' }}">{{ $existingBomId ? 'Tersimpan' : 'Draft' }}</i>
                    </span>
                    <strong id="bom-summary-bom">{{ $nextBomNo }}</strong>
                </div>
            </div>
            <div class="bom-top-layout">
                <div class="bom-info-column">
                    <section class="bom-overview-section">
                        <h2 class="bom-overview-title">Information Buyer</h2>
                        <div class="bom-overview-grid">
                            <div class="bom-summary-item"><span>Buyer Name</span><strong id="bom-summary-buyer">-</strong></div>
                            <div class="bom-summary-item"><span>Kode Buyer</span><strong id="bom-summary-buyer-code">-</strong></div>
                            <div class="bom-summary-item"><span>Brand</span><strong id="bom-summary-brand">-</strong></div>
                            <div class="bom-summary-item"><span>Season</span><strong id="bom-summary-season">-</strong></div>
                            <div class="bom-summary-item"><span>Style</span><strong id="bom-summary-style">-</strong></div>
                            <div class="bom-summary-item"><span>QTY Planned</span><strong id="bom-summary-planned-qty">-</strong></div>
                        </div>
                    </section>
                    <section class="bom-overview-section">
                        <h2 class="bom-overview-title">Product &amp; Type</h2>
                        <div class="bom-overview-grid">
                            <div class="bom-summary-item"><span>Product Group</span><strong id="bom-summary-product-group">-</strong></div>
                            <div class="bom-summary-item"><span>Product Items</span><strong id="bom-summary-product-item">-</strong></div>
                            <div class="bom-summary-item"><span>Product Type</span><strong id="bom-summary-product-type">-</strong></div>
                            <div class="bom-summary-item"><span>Unit</span><strong id="bom-summary-unit">-</strong></div>
                            <div class="bom-summary-item"><span>Costing Type</span><strong id="bom-summary-costing-type">-</strong></div>
                            <div class="bom-summary-item"><span>Worksheet Type</span><strong id="bom-summary-worksheet-type">-</strong></div>
                        </div>
                    </section>
                    <section class="bom-overview-section">
                        <h2 class="bom-overview-title">Shipping &amp; Destinations</h2>
                        <div class="bom-overview-grid">
                            <div class="bom-summary-item"><span>Ship Mode</span><strong id="bom-summary-ship-mode">-</strong></div>
                            <div class="bom-summary-item"><span>Shipment Type</span><strong id="bom-summary-shipment-type">-</strong></div>
                            <div class="bom-summary-item"><span>Main Destination</span><strong id="bom-summary-destination">-</strong></div>
                            <div class="bom-summary-item"><span>Market</span><strong id="bom-summary-market">-</strong></div>
                        </div>
                    </section>
                </div>
                <div class="bom-info-column">
                    <section class="bom-overview-section bom-review-section">
                        <h2 class="bom-overview-title">Review Image</h2>
                        <div class="bom-image-review">
                            <button type="button" class="bom-image-nav" id="bom-image-prev" aria-label="Previous image" disabled><i class="fas fa-chevron-left"></i></button>
                            <div class="bom-image-preview">
                                <img id="bom-preview-image" src="" alt="Preview gambar Costing" hidden>
                                <span id="bom-image-empty">Informasi gambar Costing belum tersedia.</span>
                            </div>
                            <button type="button" class="bom-image-nav" id="bom-image-next" aria-label="Next image" disabled><i class="fas fa-chevron-right"></i></button>
                        </div>
                        <div class="bom-image-thumbnails" id="bom-image-thumbnails"></div>
                        <div class="bom-image-counter" id="bom-image-counter"></div>
                    </section>
                    <section class="bom-overview-section">
                        <h2 class="bom-overview-title">Breakdown Order</h2>
                        <div class="bom-breakdown-wrap">
                            <table class="table table-sm table-bordered" id="bom-breakdown-table">
                                <thead></thead>
                                <tbody><tr><td class="text-muted">Memuat detail dari Sales Order terkait Costing.</td></tr></tbody>
                                <tfoot></tfoot>
                            </table>
                        </div>
                    </section>
                </div>
            </div>

            <input type="hidden" name="buyer" id="id_buyer">
            <input type="hidden" name="style" id="style">
            <input type="hidden" name="market" id="market">
            <div id="bom-selected-colors"></div>
            <div id="bom-selected-sizes"></div>
        </div>
    </div>

    <section class="card mt-3" id="section-list-item" style="display: none;">
        <div class="card-header bg-white d-flex align-items-center justify-content-between">
            <h5 class="card-title mb-0">Summary Detail</h5>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse" aria-label="Tutup Summary Detail">
                    <i class="fas fa-chevron-down"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="bom-legend mb-2">
                <span><i class="bom-column-dot" style="background:#8b5cf6"></i>Costing</span>
                <span><i class="bom-column-dot" style="background:#2878ee"></i>SO</span>
                <span><i class="bom-column-dot" style="background:#f59e0b"></i>Input</span>
                <span><i class="bom-column-dot" style="background:#94a3b8"></i>Auto</span>
            </div>
            <ul class="nav nav-tabs" id="bom-content-tabs" role="tablist"></ul>
            <div class="tab-content pt-3" id="bom-content-panels"></div>
            {{-- <div class="bom-legend mt-2">
                <span><i class="bom-legend-box" style="background:#fff"></i>Editable</span>
                <span><i class="bom-legend-box" style="background:#f1f5f9"></i>Auto calculated</span>
                <span><i class="bom-legend-box" style="background:#fff7e6"></i>Belum disimpan</span>
                <span><i class="bom-legend-box" style="background:#e0ecff;border-color:#3b82f6"></i>Just Pasted</span>
                <span><i class="bom-legend-box" style="background:#fee2e2;border-color:#ef4444"></i>Error</span>
                <span class="ml-auto">Klik sel lalu ketik / double-click untuk edit, Ctrl+C &amp; Ctrl+V dari Excel, tarik kotak biru di pojok sel untuk copy ke bawah.</span>
            </div> --}}
        </div>
    </section>

    <div class="d-flex justify-content-end gap-2 mt-4 mb-4">
        <a href="{{ route('master-bom') }}" class="btn btn-danger px-4" style="border-radius: 8px;">Cancel</a>
        <button type="submit" class="btn btn-success px-4" id="bom-grid-save" style="border-radius: 8px;">Save</button>
    </div>
</form>

<div class="modal fade" id="bom-item-modal" tabindex="-1" role="dialog" aria-labelledby="bom-item-modal-title" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bom-item-modal-title">Add Items BOM</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <span class="badge badge-primary">1</span>
                    <strong>Definition Item</strong>
                    <div class="row mt-2">
                        <div class="col-md-4">
                            <label for="bom-modal-shell">Shell</label>
                            <select id="bom-modal-shell" class="form-control">
                                <option value="">Pilih Shell</option>
                                @foreach ($shells as $shell)
                                    <option value="{{ $shell->id }}">{{ $shell->nama_panel }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="bom-modal-unit">Unit</label>
                            <select id="bom-modal-unit" class="form-control">
                                <option value="">Pilih Unit</option>
                                @foreach ($masterUnits as $unit)
                                    <option value="{{ $unit->nama_pilihan }}">{{ $unit->nama_pilihan }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="bom-modal-notes">Notes</label>
                            <input type="text" id="bom-modal-notes" class="form-control" maxlength="1000" placeholder="Notes">
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <span class="badge badge-primary">2</span>
                    <strong>Rule BOM</strong>
                    <div class="bom-rule-options mt-2">
                        <label><input type="radio" name="bom-modal-rule" value="All Color All Size" checked> All Color All Size</label>
                        <label><input type="radio" name="bom-modal-rule" value="All Color Range Size"> All Color Range Size</label>
                        <label><input type="radio" name="bom-modal-rule" value="Per Color All Size"> Per Color All Size</label>
                        <label><input type="radio" name="bom-modal-rule" value="Per Color Range Size"> Per Color Range Size</label>
                    </div>
                </div>
                <div>
                    <span class="badge badge-primary">3</span>
                    <strong>Preview List Items</strong>

                    <div class="d-flex align-items-center flex-wrap gap-2 mt-2 mb-2 p-2 bg-light border rounded">
                        <span class="badge badge-primary px-2 py-1" id="bom-modal-selected-badge">Selected 0</span>
                        <div class="flex-grow-1" style="min-width: 160px;">
                            <select id="bom-batch-item-desc" class="form-control form-control-sm">
                                <option value="">Choose Item Description</option>
                            </select>
                        </div>
                        <div style="min-width: 140px;">
                            <select id="bom-batch-color-select" class="form-control form-control-sm">
                                <option value="">Choose Color</option>
                            </select>
                        </div>
                        <div style="min-width: 120px;">
                            <select id="bom-batch-size-select" class="form-control form-control-sm">
                                <option value="">Choose Size</option>
                            </select>
                        </div>
                        <button type="button" class="btn btn-sm btn-primary" id="bom-batch-update-btn">Update</button>
                    </div>

                    <div class="table-responsive mt-2">
                        <table class="table table-sm table-bordered" id="bom-modal-items">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="bom-modal-check-all"></th>
                                    <th class="th-costing">NO</th>
                                    <th class="th-so">ITEMS DESCRIPTION</th>
                                    <th class="th-input">COLOR MATERIALS</th>
                                    <th class="th-auto">SIZE</th>
                                    <th class="th-costing">CONS/PC</th>
                                </tr>
                            </thead>
                            <tbody><tr><td colspan="6" class="text-center text-muted">Pilih rule BOM untuk memuat item.</td></tr></tbody>
                        </table>
                    </div>
                </div>
                <input type="hidden" id="bom-modal-content-id">
                <input type="hidden" id="bom-modal-content-category">
                <input type="hidden" id="bom-modal-supplier">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="bom-modal-save"><i class="fas fa-save"></i> Save</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('custom-script')
<script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script>
    let currentBomId = @json($existingBomId);
    let bomCostingImages = [];
    let bomSelectedImage = 0;
    let bomCostingRequest = null;
    let bomSelectedColors = [];
    let bomSelectedSizes = [];
    let bomSelectedCombinations = [];
    let bomCostingContents = [];
    let bomModalItems = [];
    let bomModalRequest = null;
    let bomHeaderRequest = null;
    let bomContentTables = [];
    let bomSavedItems = [];

    const BOM_EDIT_FIELDS = ['qty', 'unit', 'allowance', 'id_supplier', 'price'];
    const BOM_NUMBER_RULES = {
        qty: value => value > 0,
        allowance: value => value >= 0 && value <= 100,
        price: value => value >= 0
    };
    const bomUnitOptions = @json($unitOptions);
    const bomSupplierOptions = @json($supplierOptions);
    let bomGridState = new Map();
    let bomSelection = null;
    let bomDragMode = null;
    let bomFillTargetRow = null;
    let bomEditing = null;

    $(document).ready(function() {
        $('#bom-image-prev').on('click', function() {
            bomSelectedImage--;
            renderBomCostingImage();
        });
        $('#bom-image-next').on('click', function() {
            bomSelectedImage++;
            renderBomCostingImage();
        });
        $(document).on('click', '.bom-image-thumbnail', function() {
            bomSelectedImage = Number($(this).data('image-index'));
            renderBomCostingImage();
        });
        $(document).on('click', '.bom-content-add', function() {
            openBomItemModal($(this).data('content'));
        });
        $(document).on('shown.bs.tab', '#bom-content-tabs [data-bs-toggle="tab"]', function(event) {
            const pane = $(event.target).attr('href');
            const $table = $(pane).find('.bom-content-table');
            initializeBomContentTable($table);
            if ($.fn.DataTable.isDataTable($table)) {
                $table.DataTable().columns.adjust().draw(false);
            }
        });
        $('input[name="bom-modal-rule"]').on('change', renderBomModalRows);
        $('#bom-modal-save').on('click', saveBomJoItems);
        initializeBomGridEvents();
        $('#form-bom').on('submit', function(event) {
            event.preventDefault();
            saveBomGridChanges();
        });
        $(window).on('beforeunload', function(event) {
            if (getBomDirtyItems().length || hasBomGridErrors()) {
                event.preventDefault();
                return '';
            }
        });
        $('#id_costing').on('change', loadBomCostingOverview);

        if ($('#id_costing').val()) {
            loadBomCostingOverview();
        } else {
            renderBomBreakdown([]);
        }
    });

    function renderBomCostingImage() {
        let hasImages = bomCostingImages.length > 0;
        $('#bom-image-empty').text(hasImages ? '' : 'Gambar Costing tidak tersedia.')
            .prop('hidden', hasImages);
        $('#bom-preview-image').prop('hidden', !hasImages);
        $('#bom-image-counter').text(hasImages ? `${bomSelectedImage + 1} / ${bomCostingImages.length}` : '');
        $('#bom-image-prev, #bom-image-next').prop('disabled', bomCostingImages.length < 2);

        if (!hasImages) {
            $('#bom-preview-image').attr('src', '');
            $('#bom-image-thumbnails').empty();
            return;
        }

        bomSelectedImage = (bomSelectedImage + bomCostingImages.length) % bomCostingImages.length;
        $('#bom-preview-image').attr('src', bomCostingImages[bomSelectedImage]);
        let thumbnailsHtml = '';
        bomCostingImages.forEach(function(image, index) {
            thumbnailsHtml += `<button type="button" class="bom-image-thumbnail ${index === bomSelectedImage ? 'is-active' : ''}" data-image-index="${index}" aria-label="Tampilkan gambar ${index + 1}">
                <img src="${image}" alt="">
            </button>`;
        });
        $('#bom-image-thumbnails').html(thumbnailsHtml);
    }

    function loadBomCostingOverview() {
        let idCosting = $('#id_costing').val();
        if (bomCostingRequest) bomCostingRequest.abort();

        if (!idCosting) {
            $('.bom-summary-card strong:not(#bom-summary-costing):not(#bom-summary-bom), .bom-summary-item strong').text('-');
            $('#id_buyer, #style, #market').val('');
            $('#bom-selected-colors, #bom-selected-sizes').empty();
            bomSelectedColors = [];
            bomSelectedSizes = [];
            bomSelectedCombinations = [];
            bomCostingImages = [];
            bomSelectedImage = 0;
            renderBomCostingImage();
            $('#bom-image-empty').text('Informasi gambar Costing belum tersedia.').prop('hidden', false);
            $('#bom-summary-so, #bom-summary-po').text('-');
            renderBomBreakdown([]);
            bomCostingContents = [];
            bomSavedItems = [];
            return;
        }

        $('.bom-summary-card strong:not(#bom-summary-costing):not(#bom-summary-bom), .bom-summary-item strong').text('-');
        $('#bom-summary-so, #bom-summary-po').text('-');
        $('#id_buyer, #style, #market').val('');
        $('#bom-selected-colors, #bom-selected-sizes').empty();
        bomSelectedColors = [];
        bomSelectedSizes = [];
        bomSelectedCombinations = [];
        bomCostingImages = [];
        bomSelectedImage = 0;
        renderBomCostingImage();

        bomCostingRequest = $.ajax({
            url: "{{ route('bom-get-costing-overview') }}",
            type: 'GET',
            data: {
                id_costing: idCosting,
                id_so: @json($selectedSoId)
            },
            success: function(res) {
                if (res.status !== 200 || !res.data) {
                    Swal.fire('Gagal', res.message || 'Data Costing tidak dapat dimuat.', 'error');
                    return;
                }

                let data = res.data;
                const setSummary = (selector, value) => $(selector).text(value || '-');
                $('#id_buyer').val(data.id_buyer).trigger('change');
                $('#style').val(data.style || '');
                $('#market').val(data.market || '');
                setSummary('#bom-summary-costing', data.cost_no);
                setSummary('#bom-summary-worksheet', data.kpno);
                setSummary('#bom-summary-so', data.so_no);
                setSummary('#bom-summary-po', data.no_po);
                setSummary('#bom-summary-buyer', data.buyer_name || $('#id_buyer option:selected').text());
                setSummary('#bom-summary-buyer-code', data.buyer_code || data.id_buyer);
                setSummary('#bom-summary-brand', data.brand);
                setSummary('#bom-summary-season', [data.season_name, data.season_desc].filter(Boolean).join(' - ') || data.season_id);
                setSummary('#bom-summary-style', data.style);
                setSummary('#bom-summary-planned-qty', data.planned_qty ? Number(data.planned_qty).toLocaleString('id-ID') : '-');
                setSummary('#bom-summary-product-group', data.product_group);
                setSummary('#bom-summary-product-item', data.nama_product_item);
                setSummary('#bom-summary-product-type', data.type_product);
                setSummary('#bom-summary-unit', data.unit);
                setSummary('#bom-summary-costing-type', data.type ? String(data.type).toUpperCase() : '-');
                setSummary('#bom-summary-worksheet-type', data.tipe_ws);
                setSummary('#bom-summary-ship-mode', data.ship_mode_name || data.ship_mode);
                setSummary('#bom-summary-shipment-type', data.shipment_type);
                setSummary('#bom-summary-destination', data.main_dest);
                setSummary('#bom-summary-market', data.market);

                bomCostingImages = Array.isArray(data.images) ? data.images : (data.image_url ? [data.image_url] : []);
                bomSelectedImage = 0;
                renderBomCostingImage();
                bomSelectedColors = Array.isArray(data.colors) ? data.colors : [];
                bomSelectedSizes = Array.isArray(data.sizes) ? data.sizes : [];
                bomSelectedCombinations = Array.isArray(data.combinations) ? data.combinations : [];
                bomCostingContents = Array.isArray(data.costing_contents) ? data.costing_contents : [];
                bomSavedItems = [];
                bomGridState = new Map();
                renderBomContents(bomCostingContents, bomSavedItems);
                $('#section-list-item').show();
                $('#bom-selected-colors').empty();
                $('#bom-selected-sizes').empty();
                bomSelectedColors.forEach(function(color) {
                    $('<input>').attr({ type: 'hidden', name: 'colors[]', value: color.id }).appendTo('#bom-selected-colors');
                });
                bomSelectedSizes.forEach(function(size) {
                    $('<input>').attr({ type: 'hidden', name: 'sizes[]', value: size.id }).appendTo('#bom-selected-sizes');
                });
                renderBomBreakdown(Array.isArray(data.combinations) ? data.combinations : []);
                if (currentBomId) {
                    loadBomSavedItems().fail(function(xhr) {
                        Swal.fire('Gagal', xhr?.responseJSON?.message || 'Item BOM yang sudah tersimpan gagal dimuat.', 'error');
                    });
                }
            },
            error: function(xhr, status) {
                if (status !== 'abort') {
                    Swal.fire('Gagal', xhr.responseJSON?.message || 'Gagal mengambil informasi Costing.', 'error');
                }
            },
            complete: function() {
                bomCostingRequest = null;
            }
        });
    }

    function renderBomBreakdown(combinations) {
        let $thead =$('#bom-breakdown-table thead').empty();
        let $tbody =$('#bom-breakdown-table tbody').empty();
        let $tfoot =$('#bom-breakdown-table tfoot').empty();
        let sizesById = new Map();
        let colorsById = new Map();

        if (!combinations.length) {
            let $header =$('<tr>');
            $('<th>').text('No').appendTo($header);
            $('<th>').text('Description').appendTo($header);
            $('<th>').text('Color').appendTo($header);
            $thead.append($header);
            $('<tr>').append($('<td>').attr('colspan', 3).addClass('text-muted')
                    .text('Belum ada detail Color dan Size pada Sales Order.')
            ).appendTo($tbody);
            return;
        }

        combinations.forEach(function(combination) {
            if (combination.size_id !== null && combination.size_id !== undefined) {
                sizesById.set(String(combination.size_id), {
                    id: combination.size_id,
                    name: combination.size_name || '-',
                    order: Number(combination.size_order) || 0
                });
            }
            if (combination.color_id !== null && combination.color_id !== undefined) {
                colorsById.set(String(combination.color_id), {
                    id: combination.color_id,
                    name: combination.color_name || '-'
                });
            }
        });

        let sizes = Array.from(sizesById.values()).sort((a, b) => a.order - b.order || a.name.localeCompare(b.name));
        let colors = Array.from(colorsById.values());
        let quantities = new Map();
        combinations.forEach(function(combination) {
            let key = `${combination.color_id ?? ''}_${combination.size_id ?? ''}`;
            quantities.set(key, (quantities.get(key) || 0) + (Number(combination.qty) || 0));
        });

        let $header =$('<tr>');
        $('<th>').text('No').appendTo($header);
        $('<th>').text('Description').appendTo($header);
        $('<th>').text('Color').appendTo($header);
        sizes.forEach(size => $('<th>').text(size.name).appendTo($header));
        $('<th>').text('Total').appendTo($header);
        $thead.append($header);

        let sizeTotals = new Map(sizes.map(size => [String(size.id), 0]));
        let grandTotal = 0;
        colors.forEach(function(color, index) {
            let $row =$('<tr>');
            $('<td>').text(index + 1).appendTo($row);
            $('<td>').addClass('bom-breakdown-label').text('--').appendTo($row);
            $('<td>').addClass('bom-breakdown-label').text(color.name).appendTo($row);
            let rowTotal = 0;
            sizes.forEach(function(size) {
                let qty = quantities.get(`${color.id}_${size.id}`) || 0;
                rowTotal += qty;
                sizeTotals.set(String(size.id), sizeTotals.get(String(size.id)) + qty);
                $('<td>').text(qty ? qty.toLocaleString('id-ID') : '-').appendTo($row);
            });
            grandTotal += rowTotal;
            $('<td>').addClass('bom-breakdown-total').text(rowTotal.toLocaleString('id-ID')).appendTo($row);
            $tbody.append($row);
        });

        let $totalRow =$('<tr>');
        $('<th>').attr('colspan', 3).addClass('text-left').text('TOTAL').appendTo($totalRow);
        sizes.forEach(function(size) {
            $('<th>').text(sizeTotals.get(String(size.id)).toLocaleString('id-ID')).appendTo($totalRow);
        });
        $('<th>').text(grandTotal.toLocaleString('id-ID')).appendTo($totalRow);
        $tfoot.append($totalRow);
    }

    function createBomHeader() {
        let emptyFields = [];
        let costing = $('#id_costing').val();
        let salesOrder = @json($selectedSoId);
        let buyer = $('#id_buyer').val();
        let style = $('#style').val();
        let selectedColors = bomSelectedColors;
        let selectedSizes = bomSelectedSizes;

        if (!costing) emptyFields.push('No Costing');
        if (!salesOrder) emptyFields.push('Sales Order');
        if (!buyer) emptyFields.push('Buyer');
        if (!style || style.trim() === '') emptyFields.push('Style');
        if (selectedColors.length === 0) emptyFields.push('Color dari Sales Order');
        if (selectedSizes.length === 0) emptyFields.push('Size dari Sales Order');

        if (emptyFields.length > 0) {
            let errHtml = emptyFields.map(e => `<li>${e}</li>`).join('');
            Swal.fire({
                icon: 'warning',
                title: 'Data Belum Lengkap!',
                html: `Tidak dapat membuat BOM. Pastikan data berikut tersedia:<br><br><ul class="text-left" style="font-size: 14px;">${errHtml}</ul>`,
                confirmButtonText: 'Mengerti'
            });
            return null;
        }

        return $.post("{{ route('store-bom-header') }}", {
            _token: "{{ csrf_token() }}",
            id_costing: costing,
            id_so: salesOrder,
            buyer: buyer,
            style: style,
            market: $('#market').val(),
            colors: selectedColors.map(color => color.id),
            sizes: selectedSizes.map(size => size.id)
        });
    }

    function activateBomHeader(res) {
        currentBomId = res.id;
        $('#bom-summary-bom').text(res.no_katalog_bom || '-');
        $('#bom-status-badge').removeClass('badge-warning').addClass('badge-success')
            .text('Tersimpan').attr('title', 'BOM sudah tersimpan');

        try {
            const url = new URL(window.location.href);
            url.searchParams.set('id_bom', res.id);
            window.history.replaceState(null, '', url.toString());
        } catch (error) {
        }
    }

    function renderBomContents(contents, savedItems) {
        const categories = ['Fabric', 'Accessories Sewing', 'Accessories Packing', 'Other Cost'];
        bomContentTables.forEach(function(table) {
            if ($.fn.DataTable.isDataTable(table)) $(table).DataTable().destroy();
        });
        bomContentTables = [];
        const $tabs =$('#bom-content-tabs').empty();
        const $panels =$('#bom-content-panels').empty();
        let hasContents = false;

        categories.forEach(function(category, index) {
            const paneId = `bom-content-pane-${index}`;
            const $tab = $('<li>').addClass('nav-item');$('<a>').addClass(`nav-link ${index === 0 ? 'active' : ''}`)
                .attr({
                    href: `#${paneId}`,
                    'data-bs-toggle': 'tab',
                    role: 'tab',
                    'aria-controls': paneId,
                    'aria-selected': index === 0 ? 'true' : 'false'
                })
                .text(category)
                .appendTo($tab);
            $tabs.append($tab);

            const $pane =$('<div>').addClass(`tab-pane fade ${index === 0 ? 'show active' : ''}`)
                .attr({ id: paneId, role: 'tabpanel' });
            const tableId = `bom-content-table-${index}`;
            const $table =$('<table>').attr('id', tableId).addClass('table table-sm table-bordered bom-content-table w-100');
            bomContentTables.push(`#${tableId}`);

            $('<thead>').append($('<tr>').append(
                $('<th>').html('<input type="checkbox" aria-label="Pilih semua baris">'),
                createBomColumnHeader('NO', 'th-costing', false),
                createBomColumnHeader('CONTENT', 'th-costing', false),
                createBomColumnHeader('ITEMS DESCRIPTION', 'th-so', false),
                createBomColumnHeader('COLOR MATERIALS', 'th-so', false),
                createBomColumnHeader('ORIG QTY', 'th-so', false),
                createBomColumnHeader('CONS/PC', 'th-input', true),
                createBomColumnHeader('UNIT', 'th-input', true),
                createBomColumnHeader('QTY REQ', 'th-auto', false),
                createBomColumnHeader('LOSS %', 'th-input', true),
                createBomColumnHeader('VALUE', 'th-auto', false),
                createBomColumnHeader('TOTAL QTY REQ', 'th-auto', false),
                createBomColumnHeader('UNIT', 'th-auto', false),
                createBomColumnHeader('SUPPLIER', 'th-input', true),
                createBomColumnHeader('PRICE', 'th-input', true),
                createBomColumnHeader('PRICE COSTING', 'th-costing', false),
                createBomColumnHeader('ACTION', 'th-costing', false)
            )).appendTo($table);

            const $tbody = $('<tbody>').appendTo($table);
            const categoryContents = contents.filter(content => content.tab === category);
            let rowNumber = 1;
            if (categoryContents.length) {
                hasContents = true;
                categoryContents.forEach(function(content) {
                    const contentItems = (savedItems || []).filter(function(item) {
                        return String(item.id_contents) === String(content.content_id);
                    });
                    const $button =$('<button>').attr({
                        type: 'button',
                        title: content.content_id
                            ? 'Tambah item BOM untuk content ini'
                            : 'Other Cost tidak memiliki content yang dapat disinkronkan ke BOM JO'
                    }).addClass('btn btn-sm btn-primary bom-content-add')
                        .append($('<i>').addClass('fas fa-plus'))
                        .data('content', content);
                    if (!content.content_id) $button.prop('disabled', true);

                    const $description = $('<td>').text('-');
                    const $colorMaterials = $('<td>').text('-');
                    const $origQty = $('<td>').addClass('text-right').text('-');
                    const $cons = $('<td>').addClass('text-right')
                        .text(Number(content.cons || 0).toLocaleString('id-ID'));
                    const $consUnit = $('<td>').text(content.unit || '-');
                    const $qtyReq = $('<td>').addClass('text-right').text('-');
                    const $loss = $('<td>').addClass('text-right').text('-');
                    const $value = $('<td>').addClass('text-right').text('-');
                    const $totalQty = $('<td>').addClass('text-right').text('-');
                    const $totalUnit = $('<td>').text(content.unit || '-');
                    const $supplier = $('<td>').text('-');
                    const $price = $('<td>').addClass('text-right').text('-');
                    const $priceCosting = $('<td>').addClass('text-right')
                        .text(content.price_costing !== null && content.price_costing !== undefined ? formatBomNumber(content.price_costing) : '-');

                    contentItems.forEach(function(item) {
                        const id = String(item.id);
                        appendBomSavedItemValue($description, item.size_name || 'ALL');
                        appendBomSavedItemValue($colorMaterials, item.color_name || 'ALL');
                        appendBomSavedItemValue($origQty, formatBomNumber(bomGridState.get(id)?.origQty || 0));
                        appendBomGridCell($cons, id, 'qty');
                        appendBomGridCell($consUnit, id, 'unit');
                        appendBomAutoCell($qtyReq, id, 'qty_req');
                        appendBomGridCell($loss, id, 'allowance');
                        appendBomAutoCell($value, id, 'value');
                        appendBomAutoCell($totalQty, id, 'total_qty');
                        appendBomAutoCell($totalUnit, id, 'unit');
                        appendBomGridCell($supplier, id, 'id_supplier');
                        appendBomGridCell($price, id, 'price');
                        appendBomSavedItemValue($priceCosting, content.price_costing !== null && content.price_costing !== undefined ? formatBomNumber(content.price_costing) : '-');
                    });

                    $('<tr>').addClass(contentItems.length ? 'bom-content-has-items' : '').append(
                        $('<td>').html('<input type="checkbox">'),
                        $('<td>').text(rowNumber++),
                        $('<td>').append(
                            $('<div>').addClass('bom-content-row').append(
                                $('<span>').text(content.content_name || '-'),
                                $button
                            )
                        ),
                        $description,
                        $colorMaterials,
                        $origQty,
                        $cons,
                        $consUnit,
                        $qtyReq,
                        $loss,
                        $value,
                        $totalQty,
                        $totalUnit,
                        $supplier,
                        $price,
                        $priceCosting,
                        $('<td>').addClass('text-center').text('-')
                    ).appendTo($tbody);
                });
            }
            $pane.append($table);
            $panels.append($pane);
        });

        bomSelection = null;
        $panels.find('.bom-cell').each(function() { paintBomCell($(this)); });
        $panels.find('.bom-auto').each(function() { paintBomAutoCell($(this)); });
        initializeBomContentTable($('#bom-content-pane-0 .bom-content-table'));

        if (!hasContents) {
            $panels.prepend($('<div>').addClass('alert alert-info')
                .text('Costing ini belum memiliki content Fabric/Accessories untuk ditambahkan ke BOM JO.'));
        }
    }

    function appendBomSavedItemValue($cell, value) {
        if ($cell.children('.bom-saved-item-value').length === 0) {
            $cell.empty();
        }
        return $('<div>').addClass('bom-saved-item-value').text(value).appendTo($cell);
    }

    function loadBomSavedItems() {
        if (!currentBomId) {
            return $.Deferred().reject(new Error('Header BOM belum tersedia.')).promise();
        }

        return $.ajax({
            url: "{{ route('get-items', ':id') }}".replace(':id', currentBomId),
            method: 'GET',
            data: { start: 0, length: -1 },
            dataType: 'json'
        }).done(function(res) {
            if (!res || !Array.isArray(res.data)) {
                throw new Error('Format daftar item BOM tidak valid.');
            }
            const activeTab = $('#bom-content-tabs .nav-link.active').attr('href');
            bomSavedItems = res.data;
            syncBomGridState(bomSavedItems);
            renderBomContents(bomCostingContents, bomSavedItems);
            if (activeTab) {
                $('#bom-content-tabs .nav-link[href="' + activeTab + '"]').tab('show');
            }
        });
    }

    function createBomColumnHeader(label, typeClass, editable) {
        const $heading = $('<span>').addClass('bom-column-heading').append(
            $('<span>').addClass('bom-column-dot').attr('aria-hidden', 'true'),
            $('<span>').text(label)
        );
        if (editable) {
            $heading.append($('<i>').addClass('fas fa-pen bom-column-edit').attr({
                'aria-hidden': 'true',
                title: 'Kolom dapat diedit'
            }));
        }
        return $('<th>').addClass(typeClass).append($heading);
    }

    function initializeBomContentTable($table) {
        if (!$table.length || $.fn.DataTable.isDataTable($table)) return;

        $table.DataTable({
            autoWidth: false,
            ordering: false,
            searching: false,
            responsive: false,
            scrollX: true,
            pageLength: 4,
            lengthMenu: [[4, 10, 25, 50], [4, 10, 25, 50]],
            dom: 'rt<"d-flex justify-content-between align-items-center flex-wrap"<"d-flex align-items-center"li>p>',
            language: {
                lengthMenu: 'Item Per Page _MENU_',
                info: 'Of _TOTAL_',
                infoEmpty: 'Of 0',
                zeroRecords: 'Belum ada content dari Costing.',
                emptyTable: 'Belum ada content dari Costing.',
                paginate: {
                    previous: '<i class="fas fa-chevron-left"></i>',
                    next: '<i class="fas fa-chevron-right"></i>'
                }
            },
            columnDefs: [
                { targets: [0, 1, 16], className: 'text-center' },
                { targets: 0, width: '30px' }
            ]
        }).on('draw.dt', function() {
            bomSelection = null;
            paintBomSelection();
        });
    }

    function formatBomNumber(value, grouping = true) {
        return Number(value || 0).toLocaleString('id-ID', { maximumFractionDigits: 4, useGrouping: grouping });
    }

    function parseBomNumber(text) {
        let value = String(text ?? '').replace(/[\s%]/g, '');
        if (value === '') return null;
        const lastComma = value.lastIndexOf(',');
        const lastDot = value.lastIndexOf('.');
        if (lastComma > -1 && lastDot > -1) {
            value = lastComma > lastDot ? value.replace(/\./g, '').replace(',', '.') : value.replace(/,/g, '');
        } else if (lastComma > -1) {
            value = value.split(',').length > 2 ? value.replace(/,/g, '') : value.replace(',', '.');
        } else if (value.split('.').length > 2) {
            value = value.replace(/\./g, '');
        }
        return /^-?\d*\.?\d+$/.test(value) ? Number(value) : null;
    }

    function findBomOption(options, text) {
        const needle = String(text ?? '').trim().toUpperCase();
        return options.find(option => option.id === needle)
            || options.find(option => String(option.name).trim().toUpperCase() === needle)
            || null;
    }

    function getBomOrigQty(item) {
        return bomSelectedCombinations.reduce(function(total, combination) {
            const colorMatch = item.id_color === null || item.id_color === undefined || String(combination.color_id) === String(item.id_color);
            const sizeMatch = item.id_size === null || item.id_size === undefined || item.id_size === '' || String(combination.size_id) === String(item.id_size);
            return colorMatch && sizeMatch ? total + (Number(combination.qty) || 0) : total;
        }, 0);
    }

    function isBomFieldDirty(record, field) {
        return record.values[field] !== record.original[field];
    }

    function getBomDirtyItems() {
        return Array.from(bomGridState.entries())
            .filter(([, record]) => BOM_EDIT_FIELDS.some(field => isBomFieldDirty(record, field)))
            .map(([id, record]) => ({
                id: id,
                qty: record.values.qty,
                unit: record.values.unit,
                allowance: record.values.allowance,
                id_supplier: record.values.id_supplier || null,
                price: record.values.price
            }));
    }

    function hasBomGridErrors() {
        return Array.from(bomGridState.values()).some(record => record.errors.size > 0);
    }

    function syncBomGridState(items) {
        const next = new Map();
        items.forEach(function(item) {
            const id = String(item.id);
            const original = {
                qty: Number(item.qty) || 0,
                unit: item.unit_name ? String(item.unit_name) : '',
                allowance: Number(item.allowance) || 0,
                id_supplier: item.id_supplier ? String(item.id_supplier) : '',
                price: Number(item.price) || 0
            };
            const previous = bomGridState.get(id);
            const keepEdits = previous && (previous.errors.size > 0 || BOM_EDIT_FIELDS.some(field => isBomFieldDirty(previous, field)));
            const record = keepEdits
                ? Object.assign(previous, { original: original })
                : { original: original, values: Object.assign({}, original), text: {}, errors: new Set(), pasted: new Set() };
            record.labels = { unit: item.unit_name || '', id_supplier: item.supplier_name || '' };
            record.origQty = getBomOrigQty(item);
            next.set(id, record);
        });
        bomGridState = next;
    }

    function getBomCellText(record, field) {
        if (Object.prototype.hasOwnProperty.call(record.text, field)) return record.text[field];
        const value = record.values[field];
        if (field === 'unit' || field === 'id_supplier') {
            const option = findBomOption(field === 'unit' ? bomUnitOptions : bomSupplierOptions, value);
            if (option) return option.name;
            return value !== '' && value === record.original[field] && record.labels[field] ? record.labels[field] : '-';
        }
        return formatBomNumber(value);
    }

    function getBomCellRawValue(record, field) {
        if (Object.prototype.hasOwnProperty.call(record.text, field)) return record.text[field];
        if (field === 'unit' || field === 'id_supplier') {
            const text = getBomCellText(record, field);
            return text === '-' ? '' : text;
        }
        return formatBomNumber(record.values[field], false);
    }

    function calculateBomRow(record) {
        const qtyReq = record.origQty * record.values.qty;
        const value = qtyReq * record.values.allowance / 100;
        return { qty_req: qtyReq, value: value, total_qty: qtyReq + value };
    }

    function appendBomGridCell($cell, id, field) {
        return appendBomSavedItemValue($cell, '').addClass('bom-cell')
            .attr({ 'data-id': id, 'data-field': field, tabindex: -1 });
    }

    function appendBomAutoCell($cell, id, key) {
        return appendBomSavedItemValue($cell, '').addClass('bom-auto')
            .attr({ 'data-id': id, 'data-auto': key });
    }

    function paintBomCell($cell) {
        const record = bomGridState.get($cell.attr('data-id'));
        const field = $cell.attr('data-field');
        if (!record || $cell.hasClass('is-editing')) return;
        const hasError = record.errors.has(field);
        $cell.text(getBomCellText(record, field))
            .toggleClass('is-error', hasError)
            .toggleClass('is-pasted', !hasError && record.pasted.has(field))
            .toggleClass('is-dirty', !hasError && isBomFieldDirty(record, field));
    }

    function paintBomAutoCell($cell) {
        const record = bomGridState.get($cell.attr('data-id'));
        const key = $cell.attr('data-auto');
        if (!record) return;
        $cell.text(key === 'unit' ? getBomCellText(record, 'unit') : formatBomNumber(calculateBomRow(record)[key]));
    }

    function paintBomRow(id) {
        $(`.bom-cell[data-id="${id}"]`).each(function() { paintBomCell($(this)); });
        $(`.bom-auto[data-id="${id}"]`).each(function() { paintBomAutoCell($(this)); });
    }

    function setBomCellValue(id, field, raw, pasted) {
        const record = bomGridState.get(String(id));
        if (!record) return;
        const text = String(raw ?? '').trim();
        let value = null;
        let valid;
        if (field === 'unit' || field === 'id_supplier') {
            if (text === '' || text === '-') {
                value = '';
                valid = field === 'id_supplier';
            } else {
                const option = findBomOption(field === 'unit' ? bomUnitOptions : bomSupplierOptions, text);
                value = option ? option.id : (text.toUpperCase() === String(record.labels[field]).toUpperCase() ? record.original[field] : null);
                valid = value !== null;
            }
        } else {
            value = parseBomNumber(text);
            valid = value !== null && BOM_NUMBER_RULES[field](value);
        }

        if (valid) {
            record.values[field] = value;
            delete record.text[field];
            record.errors.delete(field);
        } else {
            record.text[field] = text;
            record.errors.add(field);
        }
        pasted ? record.pasted.add(field) : record.pasted.delete(field);
        paintBomRow(id);
    }

    function getBomGridIds($table) {
        return $table.find('tbody .bom-cell[data-field="qty"]').map((_, cell) => $(cell).attr('data-id')).get();
    }

    function getBomCellAt($table, ids, row, col) {
        return $table.find(`tbody .bom-cell[data-id="${ids[row]}"][data-field="${BOM_EDIT_FIELDS[col]}"]`);
    }

    function getBomCellPosition($cell) {
        const $table = $cell.closest('table');
        return {
            $table: $table,
            row: getBomGridIds($table).indexOf($cell.attr('data-id')),
            col: BOM_EDIT_FIELDS.indexOf($cell.attr('data-field'))
        };
    }

    function getBomSelectionRect() {
        return {
            top: Math.min(bomSelection.anchor.row, bomSelection.focus.row),
            bottom: Math.max(bomSelection.anchor.row, bomSelection.focus.row),
            left: Math.min(bomSelection.anchor.col, bomSelection.focus.col),
            right: Math.max(bomSelection.anchor.col, bomSelection.focus.col)
        };
    }

    function forEachBomSelectedCell(callback) {
        const ids = getBomGridIds(bomSelection.$table);
        const rect = getBomSelectionRect();
        for (let row = rect.top; row <= rect.bottom; row++) {
            for (let col = rect.left; col <= rect.right; col++) {
                callback(ids[row], BOM_EDIT_FIELDS[col], row, col);
            }
        }
    }

    function paintBomSelection() {
        $('.bom-cell.is-selected, .bom-cell.is-active, .bom-cell.is-fill-preview').removeClass('is-selected is-active is-fill-preview');
        $('.bom-fill-handle').remove();
        if (!bomSelection || !$.contains(document, bomSelection.$table[0])) {
            bomSelection = null;
            return;
        }

        const ids = getBomGridIds(bomSelection.$table);
        const rect = getBomSelectionRect();
        forEachBomSelectedCell(function(id, field) {
            bomSelection.$table.find(`tbody .bom-cell[data-id="${id}"][data-field="${field}"]`).addClass('is-selected');
        });
        if (bomDragMode === 'fill' && bomFillTargetRow !== null) {
            const from = Math.min(bomFillTargetRow, rect.top);
            const to = Math.max(bomFillTargetRow, rect.bottom);
            for (let row = from; row <= to; row++) {
                for (let col = rect.left; col <= rect.right; col++) {
                    getBomCellAt(bomSelection.$table, ids, row, col).addClass('is-fill-preview');
                }
            }
        }

        const $active = getBomCellAt(bomSelection.$table, ids, bomSelection.focus.row, bomSelection.focus.col).addClass('is-active');
        if (!bomEditing) $active.trigger('focus');
        getBomCellAt(bomSelection.$table, ids, rect.bottom, rect.right).append($('<span>').addClass('bom-fill-handle'));
    }

    function moveBomSelection(rowDelta, colDelta, extend) {
        if (!bomSelection) return;
        const rowCount = getBomGridIds(bomSelection.$table).length;
        const next = {
            row: Math.min(Math.max(bomSelection.focus.row + rowDelta, 0), rowCount - 1),
            col: Math.min(Math.max(bomSelection.focus.col + colDelta, 0), BOM_EDIT_FIELDS.length - 1)
        };
        bomSelection.focus = next;
        if (!extend) bomSelection.anchor = Object.assign({}, next);
        paintBomSelection();
    }

    function startBomEdit($cell, initialText) {
        if (!$cell || !$cell.length) return;
        commitBomEdit();
        const record = bomGridState.get($cell.attr('data-id'));
        const field = $cell.attr('data-field');
        let $input;
        if (field === 'unit' || field === 'id_supplier') {
            const options = field === 'unit' ? bomUnitOptions : bomSupplierOptions;
            $input = $('<select>').addClass('form-control form-control-sm bom-cell-input')
                .append($('<option>').val('').text(field === 'unit' ? 'Pilih Unit' : '- Tanpa Supplier -'));
            options.forEach(option => $input.append($('<option>').val(option.id).text(option.name)));
            $input.val(record.values[field]);
        } else {
            $input = $('<input>').attr({ type: 'text', inputmode: 'decimal' })
                .addClass('form-control form-control-sm bom-cell-input')
                .val(initialText !== undefined ? initialText : getBomCellRawValue(record, field));
        }
        bomEditing = { $cell: $cell, $input: $input };
        $('.bom-fill-handle').remove();
        $cell.addClass('is-editing').empty().append($input);
        $input.trigger('focus');
        if ($input.is('input')) {
            const input = $input[0];
            initialText === undefined ? input.select() : input.setSelectionRange(input.value.length, input.value.length);
        }
    }

    function commitBomEdit() {
        if (!bomEditing) return;
        const { $cell, $input } = bomEditing;
        bomEditing = null;
        $cell.removeClass('is-editing');
        setBomCellValue($cell.attr('data-id'), $cell.attr('data-field'), $input.val(), false);
    }

    function cancelBomEdit() {
        if (!bomEditing) return;
        const { $cell } = bomEditing;
        bomEditing = null;
        $cell.removeClass('is-editing');
        paintBomCell($cell);
        paintBomSelection();
    }

    function applyBomFill(targetRow) {
        const ids = getBomGridIds(bomSelection.$table);
        const rect = getBomSelectionRect();
        const height = rect.bottom - rect.top + 1;
        const rows = [];
        if (targetRow > rect.bottom) {
            for (let row = rect.bottom + 1; row <= targetRow; row++) rows.push([row, rect.top + (row - rect.bottom - 1) % height]);
        } else if (targetRow < rect.top) {
            for (let row = rect.top - 1; row >= targetRow; row--) rows.push([row, rect.bottom - (rect.top - 1 - row) % height]);
        }
        if (!rows.length) return;

        rows.forEach(function([row, sourceRow]) {
            const sourceRecord = bomGridState.get(ids[sourceRow]);
            for (let col = rect.left; col <= rect.right; col++) {
                const field = BOM_EDIT_FIELDS[col];
                setBomCellValue(ids[row], field, getBomCellRawValue(sourceRecord, field), true);
            }
        });
        bomSelection.anchor = { row: targetRow > rect.bottom ? rect.top : rect.bottom, col: rect.left };
        bomSelection.focus = { row: targetRow, col: rect.right };
    }

    function initializeBomGridEvents() {
        $(document).on('mousedown', '.bom-fill-handle', function(event) {
            event.preventDefault();
            event.stopPropagation();
            bomDragMode = 'fill';
            bomFillTargetRow = null;
        });

        $(document).on('mousedown', '.bom-cell', function(event) {
            if (event.button !== 0 || $(this).hasClass('is-editing')) return;
            event.preventDefault();
            commitBomEdit();
            const position = getBomCellPosition($(this));
            const cell = { row: position.row, col: position.col };
            if (event.shiftKey && bomSelection && bomSelection.$table[0] === position.$table[0]) {
                bomSelection.focus = cell;
            } else {
                bomSelection = { $table: position.$table, anchor: cell, focus: Object.assign({}, cell) };
            }
            bomDragMode = 'select';
            paintBomSelection();
        });

        $(document).on('mouseenter', '.bom-cell', function() {
            if (!bomDragMode || !bomSelection) return;
            const position = getBomCellPosition($(this));
            if (position.$table[0] !== bomSelection.$table[0]) return;
            if (bomDragMode === 'select') {
                bomSelection.focus = { row: position.row, col: position.col };
            } else {
                bomFillTargetRow = position.row;
            }
            paintBomSelection();
        });

        $(document).on('mouseup', function() {
            if (!bomDragMode) return;
            if (bomDragMode === 'fill' && bomFillTargetRow !== null && bomSelection) applyBomFill(bomFillTargetRow);
            bomDragMode = null;
            bomFillTargetRow = null;
            paintBomSelection();
        });

        $(document).on('mousedown', function(event) {
            if ($(event.target).closest('.bom-cell, .bom-fill-handle').length) return;
            commitBomEdit();
            if (bomSelection) {
                bomSelection = null;
                paintBomSelection();
            }
        });

        $(document).on('dblclick', '.bom-cell', function() {
            if (!$(this).hasClass('is-editing')) startBomEdit($(this));
        });

        $(document).on('keydown', '.bom-cell-input', function(event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                commitBomEdit();
                moveBomSelection(1, 0, false);
            } else if (event.key === 'Tab') {
                event.preventDefault();
                commitBomEdit();
                moveBomSelection(0, event.shiftKey ? -1 : 1, false);
            } else if (event.key === 'Escape') {
                event.preventDefault();
                cancelBomEdit();
            }
        });

        $(document).on('blur', '.bom-cell-input', function() {
            commitBomEdit();
        });

        $(document).on('keydown', function(event) {
            if (!bomSelection || bomEditing || $(event.target).is('input, select, textarea')) return;
            const moves = { ArrowUp: [-1, 0], ArrowDown: [1, 0], ArrowLeft: [0, -1], ArrowRight: [0, 1] };
            const ids = getBomGridIds(bomSelection.$table);
            if (moves[event.key]) {
                event.preventDefault();
                moveBomSelection(moves[event.key][0], moves[event.key][1], event.shiftKey);
            } else if (event.key === 'Tab') {
                event.preventDefault();
                moveBomSelection(0, event.shiftKey ? -1 : 1, false);
            } else if (event.key === 'Enter' || event.key === 'F2') {
                event.preventDefault();
                startBomEdit(getBomCellAt(bomSelection.$table, ids, bomSelection.focus.row, bomSelection.focus.col));
            } else if (event.key === 'Delete' || event.key === 'Backspace') {
                event.preventDefault();
                forEachBomSelectedCell((id, field) => setBomCellValue(id, field, '', false));
                paintBomSelection();
            } else if (event.key === 'Escape') {
                bomSelection = null;
                paintBomSelection();
            } else if (event.key.length === 1 && !event.ctrlKey && !event.metaKey && !event.altKey) {
                const $cell = getBomCellAt(bomSelection.$table, ids, bomSelection.focus.row, bomSelection.focus.col);
                const isSelect = ['unit', 'id_supplier'].includes($cell.attr('data-field'));
                event.preventDefault();
                startBomEdit($cell, isSelect ? undefined : event.key);
            }
        });

        $(document).on('copy', function(event) {
            if (!bomSelection || bomEditing || $(event.target).is('input, select, textarea')) return;
            const ids = getBomGridIds(bomSelection.$table);
            const rect = getBomSelectionRect();
            const lines = [];
            for (let row = rect.top; row <= rect.bottom; row++) {
                const record = bomGridState.get(ids[row]);
                const values = [];
                for (let col = rect.left; col <= rect.right; col++) values.push(getBomCellRawValue(record, BOM_EDIT_FIELDS[col]));
                lines.push(values.join('\t'));
            }
            event.originalEvent.clipboardData.setData('text/plain', lines.join('\n'));
            event.preventDefault();
        });

        $(document).on('paste', function(event) {
            if (!bomSelection || bomEditing || $(event.target).is('input, select, textarea')) return;
            const text = event.originalEvent.clipboardData.getData('text/plain');
            if (!text) return;
            event.preventDefault();

            const matrix = text.replace(/\r/g, '').replace(/\n$/, '').split('\n').map(line => line.split('\t'));
            const ids = getBomGridIds(bomSelection.$table);
            const rect = getBomSelectionRect();
            if (matrix.length === 1 && matrix[0].length === 1) {
                forEachBomSelectedCell((id, field) => setBomCellValue(id, field, matrix[0][0], true));
            } else {
                let lastRow = rect.top;
                let lastCol = rect.left;
                matrix.forEach(function(values, rowOffset) {
                    const row = rect.top + rowOffset;
                    if (row >= ids.length) return;
                    values.forEach(function(value, colOffset) {
                        const col = rect.left + colOffset;
                        if (col >= BOM_EDIT_FIELDS.length) return;
                        setBomCellValue(ids[row], BOM_EDIT_FIELDS[col], value, true);
                        lastRow = Math.max(lastRow, row);
                        lastCol = Math.max(lastCol, col);
                    });
                });
                bomSelection.anchor = { row: rect.top, col: rect.left };
                bomSelection.focus = { row: lastRow, col: lastCol };
            }
            paintBomSelection();
        });
    }

    function saveBomGridChanges() {
        commitBomEdit();
        if (!currentBomId) {
            Swal.fire('Belum ada item', 'Tambahkan item BOM melalui tombol + pada content terlebih dahulu.', 'warning');
            return;
        }
        if (hasBomGridErrors()) {
            Swal.fire('Data belum valid', 'Perbaiki sel bertanda merah. Cons/PC harus lebih dari 0, Loss % antara 0-100, Price tidak boleh minus, Unit wajib dipilih, dan Unit/Supplier harus sesuai master.', 'warning');
            return;
        }
        const items = getBomDirtyItems();
        if (!items.length) {
            Swal.fire('Tidak ada perubahan', 'Belum ada nilai yang diubah pada tabel.', 'info');
            return;
        }

        $('#bom-grid-save').prop('disabled', true);
        $.ajax({
            url: "{{ route('update-items-batch-bom') }}",
            method: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                id_bom_marketing: currentBomId,
                items: items
            }
        }).done(function(res) {
            if (res.status !== 200) {
                Swal.fire('Gagal', res.message || 'Perubahan BOM gagal disimpan.', 'error');
                return;
            }
            bomGridState = new Map();
            loadBomSavedItems()
                .done(() => Swal.fire('Berhasil', res.message, 'success'))
                .fail(xhr => Swal.fire('Tersimpan', xhr?.responseJSON?.message || 'Perubahan tersimpan, tetapi tabel belum berhasil dimuat ulang.', 'warning'));
        }).fail(function(xhr) {
            const errors = xhr.responseJSON?.errors;
            Swal.fire('Gagal', errors ? Object.values(errors).flat()[0] : (xhr.responseJSON?.message || 'Perubahan BOM gagal disimpan.'), 'error');
        }).always(function() {
            $('#bom-grid-save').prop('disabled', false);
        });
    }

    function openBomItemModal(content) {
        if (!currentBomId) {
            if (bomHeaderRequest) return;
            Swal.fire({
                icon: 'question',
                title: 'Buat BOM sekarang?',
                html: `BOM untuk Sales Order <b>${$('<div>').text($('#bom-summary-so').text()).html()}</b> belum dibuat.<br>` +
                    'Header BOM akan disimpan dulu, lalu Anda bisa menambahkan item.',
                showCancelButton: true,
                confirmButtonText: 'Ya, buat BOM',
                cancelButtonText: 'Batal'
            }).then(function(result) {
                if (result.isConfirmed) createBomHeaderThenOpenModal(content);
            });
            return;
        }
        loadBomModalContent(content);
    }

    function createBomHeaderThenOpenModal(content) {
        if (bomHeaderRequest) return;
        const request = createBomHeader();
        if (!request) return;
        bomHeaderRequest = request;
        Swal.fire({ title: 'Menyiapkan BOM...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
        request.done(function(res) {
            Swal.close();
            if (res.status !== 200 || !res.id) {
                Swal.fire('Gagal', res.message || 'Gagal membuat BOM.', 'error');
                return;
            }
            activateBomHeader(res);
            loadBomSavedItems().always(function() {
                loadBomModalContent(content);
            });
        }).fail(function(xhr) {
            Swal.fire('Gagal', xhr.responseJSON?.message || 'Gagal membuat header BOM.', 'error');
        }).always(function() {
            bomHeaderRequest = null;
        });
    }

    function loadBomModalContent(content) {
        if (bomModalRequest) bomModalRequest.abort();

        $('#bom-item-modal-title').text(`Add Items BOM - ${content.content_name || content.label}`);
        $('#bom-modal-content-id').val(content.content_id);
        $('#bom-modal-content-category').val(content.category);
        $('#bom-modal-supplier').val(content.supplier_id || '');
        $('#bom-modal-shell, #bom-modal-unit').val('');
        $('#bom-modal-notes').val('');
        $('input[name="bom-modal-rule"][value="All Color All Size"]').prop('checked', true);
        $('#bom-modal-items tbody').html('<tr><td colspan="6" class="text-center"><i class="fas fa-spinner fa-spin"></i> Memuat item...</td></tr>');
        $('#bom-item-modal').modal('show');

        bomModalRequest = $.post("{{ route('get-list-data-bom') }}", {
            _token: "{{ csrf_token() }}",
            id_contents: content.content_id,
            category: content.category
        }).done(function(res) {
            bomModalItems = Array.isArray(res.items) ? res.items : [];
            renderBomModalRows();
        }).fail(function(xhr, status) {
            if (status !== 'abort') {
                $('#bom-modal-items tbody').html('<tr><td colspan="6" class="text-center text-danger">Gagal mengambil daftar item.</td></tr>');
                Swal.fire('Gagal', xhr.responseJSON?.message || 'Tidak dapat mengambil daftar item.', 'error');
            }
        }).always(function() {
            bomModalRequest = null;
        });
    }

    function renderBomModalRows() {
        const rule = $('input[name="bom-modal-rule"]:checked').val();
        const colors = bomSelectedColors;
        const sizes = bomSelectedSizes;
        const rows = [];
        if (rule === 'All Color All Size') {
            rows.push({ colorId: null, sizeId: null, color: 'ALL', size: 'ALL' });
        } else if (rule === 'All Color Range Size') {
            sizes.forEach(size => rows.push({ colorId: null, sizeId: size.id, color: 'ALL', size: size.name }));
        } else if (rule === 'Per Color All Size') {
            colors.forEach(color => rows.push({ colorId: color.id, sizeId: null, color: color.name, size: 'ALL' }));
        } else {
            bomSelectedCombinations.forEach(combination => {
                if (combination.color_id === null || combination.size_id === null) return;
                rows.push({
                    colorId: combination.color_id,
                    sizeId: combination.size_id,
                    color: combination.color_name || '-',
                    size: combination.size_name || '-'
                });
            });
        }

        const $tbody =$('#bom-modal-items tbody').empty();
        if (!rows.length || !bomModalItems.length) {
            $('<tr>').append($('<td>').attr('colspan', 6).addClass('text-center text-muted')
                .text(!rows.length ? 'SO belum memiliki kombinasi Color/Size.' : 'Tidak ada item untuk content ini.'))
                .appendTo($tbody);
            return;
        }

        rows.forEach(function(row, index) {
            const $item =$('<select>').addClass('form-control form-control-sm bom-modal-item-select')
                .append($('<option>').val('').text('Choose Item Description'));
            bomModalItems.forEach(item => $item.append($('<option>').val(item.isi).text(item.tampil)));

            $('<tr>').attr({'data-id-color': row.colorId || '','data-id-size': row.sizeId || ''}).append($('<td>').html('<input type="checkbox" class="bom-row-checkbox">'),
                $('<td>').text(index + 1),
                $('<td>').append($item),
                $('<td>').text(row.color),$('<td>').text(row.size),
                $('<td>').append($('<input>').attr({
                    type: 'number',
                    min: '0.0000000001',
                    step: '0.0000000001',
                    placeholder: '0.0000000000'
                }).addClass('form-control form-control-sm bom-modal-qty'))
            ).appendTo($tbody);
        });
    }

    function saveBomJoItems() {
        const rule = $('input[name="bom-modal-rule"]:checked').val();
        const items = [];
        let invalid = false;
        $('#bom-modal-items tbody tr').each(function() {
            const idItem = $(this).find('.bom-modal-item-select').val();
            const qty = Number($(this).find('.bom-modal-qty').val());
            if (!idItem || !Number.isFinite(qty) || qty <= 0) {
                invalid = true;
                $(this).addClass('table-danger');
                return;
            }
            $(this).removeClass('table-danger');
            items.push({
                id_item: idItem,
                qty: qty,
                id_color: $(this).attr('data-id-color') || null,
                id_size: $(this).attr('data-id-size') || null
            });
        });

        const unit = $('#bom-modal-unit').val();
        if (invalid || !items.length || !unit) {
            Swal.fire('Data belum lengkap', 'Pilih item, isi Cons/PC lebih dari 0, dan pilih Unit untuk setiap baris.', 'warning');
            return;
        }

        $('#bom-modal-save').prop('disabled', true);
        $.ajax({
            url: "{{ route('store-bom-jo-items') }}",
            method: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                id_bom_marketing: currentBomId,
                id_so: @json($selectedSoId),
                id_contents: $('#bom-modal-content-id').val(),
                id_supplier: $('#bom-modal-supplier').val() || null,
                rule_bom: rule,
                shell: $('#bom-modal-shell').val() || null,
                unit: unit,
                notes: $('#bom-modal-notes').val(),
                items: items
            },
            success: function(res) {
                if (res.status !== 200) {
                    Swal.fire('Gagal', res.message || 'Item gagal disimpan ke BOM JO.', 'error');
                    return;
                }
                $('#bom-item-modal').modal('hide');
                loadBomSavedItems()
                    .done(function() {
                        Swal.fire('Berhasil', res.message, 'success');
                    })
                    .fail(function(xhr) {
                        Swal.fire('Item tersimpan', xhr.responseJSON?.message || 'Item tersimpan, tetapi tabel belum berhasil dimuat ulang.', 'warning');
                    });
            },
            error: function(xhr) {
                Swal.fire('Gagal', xhr.responseJSON?.message || 'Item gagal disimpan ke BOM JO.', 'error');
            },
            complete: function() {
                $('#bom-modal-save').prop('disabled', false);
            }
        });
    }
</script>
@endsection
