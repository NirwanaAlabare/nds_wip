@extends('layouts.index')

@section('custom-link')
    <link rel="stylesheet" href="{{ asset('dist/css/marketing-form.css') }}?v={{ @filemtime(public_path('assets/dist/css/marketing-form.css')) }}">
    <link rel="stylesheet" href="{{ asset('dist/css/marketing-buttons.css') }}?v={{ @filemtime(public_path('assets/dist/css/marketing-buttons.css')) }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
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

        .bom-modal-fields .form-control,
        .bom-modal-fields .select2-container--bootstrap4 .select2-selection { height: 59px; min-height: 59px; border: 1px solid #e1e5e9; border-radius: 12px; background: #fff; box-shadow: none; font-family: 'Poppins', sans-serif; font-size: 14px; font-weight: 500; line-height: 1; color: #000; }
        .bom-modal-fields .form-control { padding: 0 16px; }
        .bom-modal-fields .form-control::placeholder { color: #ababab; }
        .bom-modal-fields .form-control:focus,
        .bom-modal-fields .select2-container--bootstrap4.select2-container--focus .select2-selection,
        .bom-modal-fields .select2-container--bootstrap4.select2-container--open .select2-selection { border-color: #284a63 !important; box-shadow: none !important; }
        .bom-modal-fields .select2-container--bootstrap4 .select2-selection--single { display: flex; align-items: center; padding: 0 44px 0 16px; }
        .bom-modal-fields .select2-container--bootstrap4 .select2-selection__rendered { padding: 0; color: #000; font-size: 14px; font-weight: 500; line-height: 1; }
        .bom-modal-fields .select2-container--bootstrap4 .select2-selection__placeholder { color: #ababab; }
        .bom-modal-fields .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow { top: 50%; right: 16px; width: 12px; height: 12px; transform: translateY(-50%); }
        .bom-modal-fields .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow b { top: 50%; left: 50%; width: 8px; height: 8px; margin: -6px 0 0 -4px; border: solid #284a63; border-width: 0 1.5px 1.5px 0; transform: rotate(45deg); }
        .bom-modal-fields .select2-container--bootstrap4.select2-container--open .select2-selection--single .select2-selection__arrow b { margin-top: -2px; transform: rotate(-135deg); }
        /* State 2: ada isi + hover/fokus = border navy dan tanda X untuk menghapus pilihan (allowClear), menggantikan chevron.
           State 3: sudah terpilih & diam = border abu + chevron. */
        .bom-modal-fields > [class*="col-"] .select2-container--bootstrap4 .select2-selection { position: relative; }
        .bom-modal-fields > [class*="col-"] .select2-container--bootstrap4 .select2-selection__clear { display: none; position: absolute; top: 50%; right: 14px; z-index: 2; float: none; width: 18px; height: 18px; margin: 0; padding: 0; transform: translateY(-50%); color: #284a63; font-size: 20px; font-weight: 400; line-height: 18px; text-align: center; cursor: pointer; }
        .bom-modal-fields > [class*="col-"] .select2-container--bootstrap4:not(.select2-container--disabled):is(:hover, .select2-container--focus, .select2-container--open) .select2-selection__clear { display: block; }
        .bom-modal-fields > [class*="col-"] .select2-container--bootstrap4:not(.select2-container--disabled):is(:hover, .select2-container--focus, .select2-container--open) .select2-selection--single:has(.select2-selection__clear) .select2-selection__arrow { display: none; }
        .bom-modal-fields > [class*="col-"] .select2-container--bootstrap4:not(.select2-container--disabled):hover .select2-selection:has(.select2-selection__clear) { border-color: #284a63; }
        /* State 4: disabled / readonly / view only = label ikut pudar. */
        .bom-modal-fields > [class*="col-"]:has(.select2-container--disabled) label, .bom-modal-fields > [class*="col-"]:has(.form-control:disabled) label, .bom-modal-fields > [class*="col-"]:has(.form-control[readonly]) label { color: #c4c4c4; }

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
            width: 76px;
            height: 97px;
            flex: 0 0 auto;
            align-items: center;
            justify-content: center;
            padding: 2px;
            border: 1px solid #d8e0e9;
            border-radius: 4px;
            background: #fff;
            opacity: .72;
        }
        .bom-image-caption { margin: 6px 0 4px; color: #9aa5ad; font-size: 12px; text-align: center; }
        .bom-image-caption:empty { display: none; }
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
        /* Tinggi mengikuti jumlah color (tanpa scroll atas-bawah); scroll samping hanya bila size sangat banyak. */
        .bom-breakdown-wrap {
            overflow-x: auto;
            overflow-y: visible;
            margin: 0 10px 10px;
        }
        #bom-breakdown-table {
            margin-bottom: 0;
            font-size: 10px;
            min-width: 560px;
            table-layout: fixed;
        }
        #bom-breakdown-table thead th {
            background: #294d68;
            color: #fff;
            white-space: nowrap;
        }
        #bom-breakdown-table tfoot th {
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
        #bom-breakdown-table .bom-breakdown-label {
            text-align: left;
            white-space: normal;
            overflow-wrap: anywhere;
        }
        #bom-breakdown-table thead th:nth-child(1) { width: 36px; }
        #bom-breakdown-table thead th:nth-child(2) { width: 90px; }
        #bom-breakdown-table thead th:nth-child(3) { width: 130px; }
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
        /* Tab kategori sama dengan Edit Costing (.costing-category-tab). */
        #bom-content-tabs {
            flex-wrap: nowrap;
            margin: 10px 0 6px;
            overflow-x: auto;
            border-bottom: 1px solid #e3e8ee;
        }
        #bom-content-tabs .nav-link {
            padding: 11px 20px;
            border: 0;
            border-bottom: 3px solid transparent;
            background: transparent;
            color: #d3d3d3;
            font-family: 'Poppins', sans-serif;
            font-size: 25px;
            font-weight: 600;
            white-space: nowrap;
            transition: color .15s ease;
        }
        #bom-content-tabs .nav-link:hover { color: #6f8799; }
        #bom-content-tabs .nav-link.active {
            border-bottom-color: #284a63;
            background: transparent;
            color: #284a63;
        }
        .bom-content-table {
            width: 100% !important;
            margin-bottom: 0;
            font-size: 10px;
        }
        .bom-content-table thead th {
            padding: 6px 5px;
            border-color: #c9d9f2;
            background: #cfe0ff;
            color: #26394c;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            white-space: normal;
            position: relative;
        }
        .bom-content-table thead th .bom-column-heading {
            display: inline-flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 2px 4px;
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
            padding: 5px 5px;
            border-color: #e1e5ea;
            vertical-align: middle;
        }
        .bom-content-table tbody tr:hover { background: #f5f8fc; }
        /* Satu baris per item; tinggi tiap item disamakan antar kolom oleh alignBomItemRows(). */
        .bom-saved-item-value {
            box-sizing: border-box;
            min-height: 22px;
            max-width: 200px;
            padding: 2px 0;
            overflow: hidden;
            border-bottom: 1px solid #e1e5ea;
            line-height: 17px;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .bom-saved-item-value:last-child { border-bottom: 0; }
        /* Kolom teks panjang boleh turun baris. */
        .bom-wrap-cell .bom-saved-item-value {
            white-space: normal;
            overflow-wrap: anywhere;
        }
        .bom-desc-cell { min-width: 100px; }
        .bom-desc-cell .bom-saved-item-value { max-width: 320px; }
        .bom-supplier-cell { min-width: 80px; }
        .bom-supplier-cell .bom-saved-item-value { max-width: 190px; }

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
        /* select2 di dalam sel grid: tampil sekecil input sel, dropdown-nya lebar mengikuti isi. */
        .bom-cell .select2-container--bootstrap4 .select2-selection {
            height: 20px !important;
            min-height: 20px;
            padding: 0 4px;
            border-radius: 0;
            font-size: 10px;
        }
        .bom-cell .select2-container--bootstrap4 .select2-selection__rendered { padding: 0; line-height: 18px; }
        .bom-cell .select2-container--bootstrap4 .select2-selection__arrow { height: 18px; }
        .select2-container--bootstrap4 .select2-results__option { font-size: 12px; }
        .bom-fill-handle {
            position: absolute;
            right: 0;
            bottom: 0;
            z-index: 2;
            width: 7px;
            height: 7px;
            border: 1px solid #fff;
            background: #2563eb;
            cursor: crosshair;
        }
        .bom-auto { background: #f1f5f9; margin: 0 -4px; padding: 2px 4px; }
        .bom-item-delete:not(.mkt-icon-btn) {
            padding: 0 4px;
            border: 0;
            background: transparent;
            color: #dc2626;
            font-size: 10px;
            line-height: 14px;
        }
        .bom-item-delete:not(.mkt-icon-btn):hover { color: #991b1b; }
        .bom-item-delete:not(.mkt-icon-btn):disabled { opacity: .4; }
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
        .bom-content-add:not(.mkt-icon-btn) {
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
        .bom-content-add:not(.mkt-icon-btn):hover,
        .bom-content-add:not(.mkt-icon-btn):focus {
            background: #1d3d70;
            color: #fff;
        }
        .bom-content-add:not(.mkt-icon-btn):disabled { opacity: .45; }
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
        #bom-modal-items select, #bom-modal-items input:not([type="checkbox"]) { min-width: 110px; }
        #bom-modal-items input[type="checkbox"] { width: 16px; min-width: 0; height: 16px; margin: 0; vertical-align: middle; }
        #bom-modal-items th:first-child, #bom-modal-items td:first-child { width: 40px; text-align: center; }
        #bom-modal-items .select2-container { min-width: 280px; }
        /* Summary Detail tetap muat di layar yang lebih sempit: padding & huruf dikecilkan bertahap. */
        @media (max-width: 1280px) {
            .bom-content-table thead th, .bom-content-table tbody td { padding-left: 3px; padding-right: 3px; }
            .bom-content-table thead th { font-size: 8px; }
            .bom-content-table thead th .bom-column-edit { font-size: 9px; }
        }
        @media (max-width: 1100px) {
            .bom-content-table { font-size: 9px; }
        }
        @media (max-width: 1199.98px) {
            /* Kolom hitungan otomatis & PRICE COSTING disembunyikan (hanya CSS, sel tetap ada sehingga index kolom grid tidak berubah). */
            .bom-content-table tr > :nth-child(10), .bom-content-table tr > :nth-child(12),
            .bom-content-table tr > :nth-child(13), .bom-content-table tr > :nth-child(14),
            .bom-content-table tr > :nth-child(17) { display: none; }
            .bom-desc-cell { min-width: 80px; }
            .bom-supplier-cell { min-width: 70px; }
        }
        @media (max-width: 800px) {
            .bom-desc-cell, .bom-supplier-cell { min-width: 0; }
            .bom-content-table { font-size: 8.5px; }
            #section-list-item > .card-body { padding-left: 4px; padding-right: 4px; }
            .bom-content-table thead th, .bom-content-table tbody td { padding-left: 2px; padding-right: 2px; }
        }
        @media (max-width: 991.98px) {
            .bom-summary-cards { grid-template-columns: repeat(3, minmax(0, 1fr)); }
            .bom-top-layout { grid-template-columns: 1fr; }
            .bom-rule-options { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (max-width: 575.98px) {
            .bom-summary-cards { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            #section-list-item > .card-body { padding: 6px; }
            #bom-content-tabs .nav-link { padding: 8px 12px; font-size: 18px; }
        }

        .bom-breadcrumb { margin-bottom: 18px; padding: 8px 10px; border-radius: 4px; background: #284A63; color: #b4b4b4; font-size: 16px; font-weight: 500; }
        .bom-breadcrumb a { color: #b4b4b4; text-decoration: none; }
        .bom-breadcrumb a:hover { color: #fff; text-decoration: underline; }
        .bom-breadcrumb strong { color: #fff; font-weight: 500; }

        /* Ukuran teks disamakan dengan SO create (so-page-heading, so-summary-card, so-overview-*, so-section-toggle). */
        #form-bom { font-family: 'Poppins', sans-serif; }
        .bom-page-heading { margin-bottom: 14px; }
        .bom-page-heading h1 { margin: 0; color: #284a63; font-size: 32px; font-weight: 700; letter-spacing: 0; }
        .bom-page-heading h1 i { font-size: 11px; vertical-align: middle; }
        .bom-page-heading p { margin: 4px 0 0; color: #284a63; font-size: 12px; font-weight: 600; }
        .bom-page-heading p i { margin-right: 6px; color: #a3b2c7; }
        .bom-summary-card { min-height: 76px; padding: 10px 14px; }
        .bom-summary-card span { margin-bottom: 4px; color: #9e9e9e; font-size: 20px; }
        .bom-summary-card strong { margin-top: 0; font-size: 24px; font-weight: 600; }
        .bom-overview-title { padding: 9px 0 7px; color: #616161; font-size: 16px; font-weight: 500; }
        .bom-overview-grid { column-gap: 22px; padding: 8px 12px 10px; }
        .bom-summary-item { min-height: 33px; }
        .bom-summary-item span { color: #9e9e9e; font-size: 18px; font-weight: 400; }
        .bom-summary-item strong { font-size: 18px; font-weight: 600; }
        #section-list-item > .card-header .card-title { color: #284a63; font-size: 16px; font-weight: 500; }
        input[type="checkbox"]:not(.custom-control-input) { -webkit-appearance: none; appearance: none; flex: 0 0 16px; width: 16px; height: 16px; padding: 0; border: 1.5px solid #cfd4dc; border-radius: 4px; background: #fff no-repeat center / 10px 10px; vertical-align: middle; cursor: pointer; transition: background-color .15s ease, border-color .15s ease, box-shadow .15s ease; }
        input[type="checkbox"]:not(.custom-control-input):hover:not(:checked):not(:indeterminate):not(:disabled) { background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12'%3e%3cpath fill='none' stroke='%23cfd4dc' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' d='M2.5 6.2l2.3 2.3 4.7-4.9'/%3e%3c/svg%3e"); }
        input[type="checkbox"]:not(.custom-control-input):checked { border-color: #284a63; background-color: #284a63; background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12'%3e%3cpath fill='none' stroke='%23fff' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' d='M2.5 6.2l2.3 2.3 4.7-4.9'/%3e%3c/svg%3e"); }
        input[type="checkbox"]:not(.custom-control-input):indeterminate { border-color: #284a63; background-color: #284a63; background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12'%3e%3cpath fill='none' stroke='%23fff' stroke-width='2' stroke-linecap='round' d='M3 6h6'/%3e%3c/svg%3e"); }
        input[type="checkbox"]:not(.custom-control-input):focus { outline: 0; }
        input[type="checkbox"]:not(.custom-control-input):focus-visible { box-shadow: 0 0 0 4px #b3daf7; }
        input[type="checkbox"]:not(.custom-control-input):disabled { opacity: .5; cursor: not-allowed; }

        /* Carousel gambar (coverflow): semua gambar dirender sekali di satu panggung. Pindah gambar hanya mengubah posisi &
           skala dengan transition CSS (gambar benar-benar bergeser, tanpa ganti src), jadi gerakannya halus.
           Aktif = besar di tengah, sebelum/sesudah = kecil di samping (klik untuk pindah), titik aktif memanjang. */
        .mkt-cf-stage { position: relative; display: flex; align-items: center; justify-content: center; min-width: 0; height: 288px; overflow: hidden; }
        .mkt-cf-stage > .mkt-cf-main, .mkt-cf-stage > .mkt-cf-side { display: none !important; }
        .mkt-cf-stage > img.mkt-cf-item { position: absolute; top: 50%; left: 50%; max-width: 46%; max-height: 100%; object-fit: contain; cursor: pointer; will-change: transform, opacity; transition: transform .55s cubic-bezier(.22, .61, .36, 1), opacity .55s ease; }
        .mkt-cf-stage > img.mkt-cf-item.is-active { cursor: default; }
        /* Gambar samping (sebelum/sesudah): 90x192, sejajar tengah dengan gambar aktif 182x256. */
        .mkt-cf-stage > img.mkt-cf-item.is-side { width: 90px; height: 192px; max-height: 192px; }
        .mkt-cf-stage > img.mkt-cf-item.is-active { width: 182px; height: 256px; max-height: 256px; }
        .mkt-cf-stage > img.mkt-cf-item { transition-property: transform, opacity, width, height; }
        .mkt-cf-stage > img.mkt-cf-item.is-far { opacity: 0 !important; pointer-events: none; }
        .mkt-cf-dots { display: flex; justify-content: center; align-items: center; gap: 4px; margin-top: 10px; min-height: 4px; }
        .mkt-cf-dot { width: 6px; height: 4px; padding: 0; border: 0; border-radius: 2px; background: #d9dee5; cursor: pointer; transition: width .3s ease, background-color .3s ease; }
        .mkt-cf-dot.is-active { width: 16px; background: #1d6fd8; }
        @media (prefers-reduced-motion: reduce) { .mkt-cf-stage > img.mkt-cf-item { transition: none; } }
        .bom-image-review .mkt-cf-stage { flex: 1; min-width: 0; }

        /* Banner "N fields are empty" (sama seperti Costing). */
        .mkt-error-alert { display: flex; align-items: center; gap: 8px; margin-bottom: 16px; padding: 10px 12px; border-radius: 4px; background: #ff5f5f; color: #fff; font-family: Inter, sans-serif; font-size: 13px; }
        .mkt-error-alert[hidden] { display: none; }
        .mkt-error-alert > span { flex: 1; }
        .mkt-error-alert-close { flex: 0 0 auto; padding: 0; border: 0; background: none; color: #fff; font-size: 16px; line-height: 1; }
        /* Modal Add Items: field kosong merah + "Required" (sama seperti Costing). */
        .bom-modal-fields .is-invalid label { color: #c8161d; }
        .bom-modal-fields .is-invalid .select2-container--bootstrap4 .select2-selection { border-color: #c8161d !important; color: #c8161d; }
        .bom-modal-fields .is-invalid .select2-selection__rendered,
        .bom-modal-fields .is-invalid .select2-selection__placeholder { color: #c8161d !important; }
        .bom-modal-fields .is-invalid .select2-selection__arrow b { border-color: #c8161d; }
        .bom-modal-fields .is-invalid::after { content: 'Required'; display: block; margin-top: 4px; color: #c8161d; font-size: 13px; font-weight: 500; }
        #bom-modal-items td.is-invalid .select2-container--bootstrap4 .select2-selection,
        #bom-modal-items td.is-invalid .form-control { border-color: #c8161d !important; }
        #bom-modal-items td.is-invalid .select2-selection__placeholder { color: #c8161d !important; }
        #bom-modal-items td.is-invalid::after { content: 'Required'; display: block; margin-top: 2px; color: #c8161d; font-size: 11px; font-weight: 500; text-align: left; }

        /* ============ RESPONSIVE (tablet & HP) — desktop >= 1200px tidak berubah ============ */
        @media (max-width: 1199.98px) {
            .bom-summary-card { padding: 10px 12px; }
            .bom-summary-card span { font-size: 15px; }
            .bom-summary-card strong { font-size: 17px; }
            .bom-summary-item span, .bom-summary-item strong { font-size: 16px; }
            #bom-content-tabs { overflow-y: hidden; }
            #bom-content-tabs .nav-link { padding: 10px 16px; font-size: 21px; }
        }
        @media (max-width: 991.98px) {
            /* 5 kartu: 3 di atas + 2 di bawah. */
            .bom-summary-cards { grid-template-columns: repeat(6, minmax(0, 1fr)); gap: 10px; }
            .bom-summary-cards > .bom-summary-card { grid-column: span 2; }
            .bom-summary-cards > .bom-summary-card:nth-child(n+4) { grid-column: span 3; }
            .bom-summary-card { min-height: 0; }
            .bom-summary-card span { font-size: 14px; }
            .bom-summary-card strong { font-size: 18px; overflow-wrap: anywhere; white-space: normal; }
            .bom-summary-item span { font-size: 14px; }
            .bom-summary-item strong { font-size: 15px; overflow-wrap: anywhere; white-space: normal; }
            #bom-content-tabs .nav-link { padding: 8px 14px; font-size: 18px; }
            /* Tabel Summary Detail digeser ke samping di dalam card, bukan terpotong. */
            #bom-content-panels .dataTables_wrapper { overflow-x: auto; }
            #bom-content-panels .dataTables_wrapper > .d-flex { position: sticky; left: 0; }
        }
        @media (max-width: 575.98px) {
            #form-bom > .card > .card-body { padding: .9rem .8rem 1rem; }
            .bom-breadcrumb { font-size: 13px; }
            .bom-page-heading h1 { font-size: 24px; }
            .bom-summary-cards { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px; }
            .bom-summary-cards > .bom-summary-card, .bom-summary-cards > .bom-summary-card:nth-child(n+4) { grid-column: auto; }
            .bom-summary-cards > .bom-summary-card:last-child { grid-column: 1 / -1; }
            .bom-summary-card { padding: 8px 10px; }
            .bom-summary-card span { font-size: 13px; }
            .bom-summary-card strong { font-size: 16px; }
            .bom-overview-grid { column-gap: 14px; }
            .bom-overview-title, #section-list-item > .card-header .card-title { font-size: 15px; }
            .bom-summary-item span { font-size: 13px; }
            .bom-summary-item strong { font-size: 14px; }
            .bom-image-review { gap: 8px; padding: 10px 6px; }
            #bom-content-tabs .nav-link { padding: 8px 12px; font-size: 16px; }
            /* Modal Add Items di HP: rule 1 kolom, toolbar batch memenuhi lebar. */
            #bom-item-modal .modal-body { padding: .8rem; }
            .bom-rule-options { grid-template-columns: 1fr; gap: 8px; }
            .bom-rule-options label { min-height: 40px; justify-content: flex-start; text-align: left; }
            .bom-modal-fields .form-control,
            .bom-modal-fields .select2-container--bootstrap4 .select2-selection { height: 48px; min-height: 48px; }
            #bom-item-modal .modal-body .bg-light.border.rounded > div { flex: 1 1 100%; width: auto !important; min-width: 0 !important; }
            #bom-item-modal .modal-body .bg-light.border.rounded > #bom-batch-update-btn { flex: 1 1 100%; }
        }
        @media (max-width: 359.98px) {
            .bom-summary-cards, .bom-overview-grid { grid-template-columns: 1fr; }
        }

        /* ===== Tipografi QA Figma (Create BOM) — berlaku di semua ukuran layar ===== */
        .bom-summary-card > span { font-family: "Instrument Sans", 'Poppins', sans-serif !important; font-size: 20px !important; font-weight: 500 !important; }
        .bom-summary-card > strong { font-family: 'Poppins', sans-serif !important; font-size: 24px !important; font-weight: 700 !important; }
        /* Di HP nomor 24px tidak muat 2 kolom (terpotong di tengah) -> kartu ringkasan 1 kolom. */
        @media (max-width: 575.98px) {
            .bom-summary-cards { grid-template-columns: minmax(0, 1fr) !important; }
            .bom-summary-cards > .bom-summary-card:last-child { grid-column: auto; }
        }
        .bom-overview-title { font-family: 'Poppins', sans-serif !important; font-size: 16px !important; font-weight: 700 !important; }
        .bom-summary-item > span { font-family: "Instrument Sans", 'Poppins', sans-serif !important; font-size: 18px !important; font-weight: 500 !important; }
        .bom-summary-item > strong { font-family: 'Poppins', sans-serif !important; font-size: 18px !important; font-weight: 700 !important; }
        #bom-content-tabs .nav-link { font-family: 'Poppins', sans-serif !important; font-size: 25px !important; font-weight: 600 !important; }
        .bom-legend > span { font-family: Inter, sans-serif !important; font-size: 12px !important; font-weight: 400 !important; }
        #bom-image-empty, #bom-image-counter { font-family: "Instrument Sans", 'Poppins', sans-serif !important; font-size: 12px !important; font-weight: 500 !important; }
        #section-list-item > .card-header .card-title { font-family: 'Poppins', sans-serif !important; font-size: 25px !important; font-weight: 500 !important; }
        /* Tabel (tab konten & Breakdown Order): header Inter Medium 12, isi Inter Regular 14, baris TOTAL Inter Bold 14. */
        .bom-create-page table thead th, #bom-content-panels table thead th, #bom-breakdown-table thead th { font-family: Inter, sans-serif !important; font-size: 12px !important; font-weight: 500 !important; }
        #bom-content-panels table tbody td, #bom-breakdown-table tbody td { font-family: Inter, sans-serif !important; font-size: 14px !important; font-weight: 400 !important; font-variant-numeric: lining-nums tabular-nums; }
        #bom-breakdown-table tfoot td, #bom-breakdown-table tfoot th { font-family: Inter, sans-serif !important; font-size: 14px !important; font-weight: 700 !important; font-variant-numeric: lining-nums tabular-nums; }
        /* Pagination tabel konten: Poppins 16 (nomor halaman Medium, label/dropdown/info Regular). */
        #bom-content-panels .dataTables_paginate, #bom-content-panels .dataTables_paginate .paginate_button, #bom-content-panels .dataTables_paginate .page-link { font-family: 'Poppins', sans-serif !important; font-size: 16px !important; font-weight: 500 !important; }
        #bom-content-panels .dataTables_length, #bom-content-panels .dataTables_length label, #bom-content-panels .dataTables_info { font-family: 'Poppins', sans-serif !important; font-size: 16px !important; font-weight: 400 !important; }
        #bom-content-panels .dataTables_length select { height: 36px; min-width: 70px; font-family: 'Poppins', sans-serif !important; font-size: 16px !important; font-weight: 400 !important; }
    </style>
@endsection

@section('content')
<form id="form-bom" action="{{ route('store-bom') }}" method="POST">
    @csrf

    <div class="card card-primary ">
        {{-- Breadcrumb + judul di atas body card, sama seperti SO create. --}}
        <div class="card-body">
            <div class="bom-breadcrumb">Marketing &nbsp;›&nbsp; Proses &nbsp;›&nbsp; <a href="{{ route('master-costing') }}">Costing</a> &nbsp;›&nbsp; <strong>{{ $isEditBom ? 'Edit BOM' : 'Create BOM' }}</strong></div>
            <div class="bom-page-heading">
                <h1>{{ $isEditBom ? 'Edit Bill of Material' : 'Create Bill of Material' }} <i class="far fa-question-circle"></i></h1>
                <p><i class="fas fa-cog"></i>{{ $isEditBom ? 'Untuk Mengubah Data BOM' : 'Untuk Menambah Data BOM' }}</p>
            </div>
            <div class="mkt-error-alert" id="bom-error-alert" role="alert" hidden>
                <i class="fas fa-exclamation-circle"></i><span></span>
                <button type="button" class="mkt-error-alert-close" aria-label="Tutup" onclick="this.parentElement.hidden = true"><i class="far fa-times-circle"></i></button>
            </div>
            <input type="hidden" name="id_costing" id="id_costing" value="{{ $selectedCostingId }}">
            <div class="bom-summary-cards" aria-label="Ringkasan BOM">
                <div class="bom-summary-card"><span>No Costing</span><strong id="bom-summary-costing">{{ $selectedCostingNo }}</strong></div>
                <div class="bom-summary-card"><span>No Worksheet</span><strong id="bom-summary-worksheet">-</strong></div>
                <div class="bom-summary-card"><span>No Sales Order</span><strong id="bom-summary-so">-</strong></div>
                <div class="bom-summary-card"><span>No Purchase Order</span><strong id="bom-summary-po">-</strong></div>
                <div class="bom-summary-card"><span>No BOM</span><strong id="bom-summary-bom">{{ $bomNo }}</strong></div>
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
                            <div class="bom-image-preview mkt-cf-stage">
                                <img class="mkt-cf-side is-prev is-empty" alt="Gambar sebelumnya">
                                <img id="bom-preview-image" class="mkt-cf-main" src="" alt="Preview gambar Costing" hidden>
                                <span id="bom-image-empty">Informasi gambar Costing belum tersedia.</span>
                                <img class="mkt-cf-side is-next is-empty" alt="Gambar berikutnya">
                            </div>
                            <button type="button" class="bom-image-nav" id="bom-image-next" aria-label="Next image" disabled><i class="fas fa-chevron-right"></i></button>
                        </div>
                        <div class="bom-image-thumbnails" id="bom-image-thumbnails" aria-label="Daftar gambar Costing"></div>
                        <div class="bom-image-caption" id="bom-image-caption"></div>
                        <div class="bom-image-counter mkt-cf-dots" id="bom-image-counter"></div>
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
                <button type="button" class="mkt-btn mkt-btn--red mkt-btn--sm ml-auto" id="bom-delete-selected" disabled>
                    <i class="fas fa-trash" style="font-size:10px;"></i> Hapus item terpilih <span id="bom-delete-selected-count"></span>
                </button>
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
        <a href="{{ route('master-costing') }}" class="mkt-btn mkt-btn--red mkt-btn--cancel">Cancel</a>
        <span id="bom-pending-sync" class="align-self-center small text-warning mr-2" hidden>
            {{-- <i class="fas fa-exclamation-circle"></i> Ada perubahan yang belum disimpan --}}
        </span>
        <button type="submit" class="mkt-btn mkt-btn--green ml-2" id="bom-grid-save">Save</button>
    </div>
</form>

<div class="modal fade" id="bom-item-modal" tabindex="-1" role="dialog" aria-labelledby="bom-item-modal-title" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bom-item-modal-title">Add Items BOM</h5>
                <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="mkt-error-alert" id="bom-modal-error-alert" role="alert" hidden>
                    <i class="fas fa-exclamation-circle"></i><span></span>
                    <button type="button" class="mkt-error-alert-close" aria-label="Tutup" onclick="this.parentElement.hidden = true"><i class="far fa-times-circle"></i></button>
                </div>
                <div class="mb-3">
                    <span class="badge badge-primary">1</span>
                    <strong>Definition Item</strong>
                    <div class="row mt-2 bom-modal-fields">
                        <div class="col-md-4">
                            <label for="bom-modal-shell">Shell</label>
                            <select id="bom-modal-shell" class="form-control bom-modal-select2">
                                <option value="">Pilih Shell</option>
                                @foreach ($shells as $shell)
                                    <option value="{{ $shell->id }}">{{ $shell->nama_panel }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="bom-modal-unit">Unit</label>
                            <select id="bom-modal-unit" class="form-control bom-modal-select2">
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
                            <select id="bom-batch-item-desc" class="form-control form-control-sm bom-modal-select2">
                                <option value="">Choose Item Description</option>
                            </select>
                        </div>
                        <div style="min-width: 140px;">
                            <select id="bom-batch-color-select" class="form-control form-control-sm bom-modal-select2">
                                <option value="">Choose Color</option>
                            </select>
                        </div>
                        <div style="min-width: 120px;">
                            <select id="bom-batch-size-select" class="form-control form-control-sm bom-modal-select2">
                                <option value="">Choose Size</option>
                            </select>
                        </div>
                        <div style="width: 130px;">
                            <input type="text" inputmode="decimal" id="bom-batch-cons" class="form-control form-control-sm" placeholder="Cons/PC">
                        </div>
                        <button type="button" class="mkt-btn mkt-btn--green mkt-btn--sm" id="bom-batch-update-btn">Update</button>
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
                <button type="button" class="mkt-btn mkt-btn--red mkt-btn--cancel" data-dismiss="modal" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="mkt-btn mkt-btn--green ml-2" id="bom-modal-save">Save</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('custom-script')
<script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script>
    // Header BOM sudah dibuat saat Costing disimpan.
    const currentBomId = @json($bomId);
    // true setelah add/delete item: bom_marketing_detail sudah berubah, BOM JO baru terkirim saat Save.
    let bomPendingSync = false;
    let bomCostingImages = [];
    let bomSelectedImage = 0;
    let bomCostingRequest = null;
    let bomSelectedColors = [];
    let bomSelectedSizes = [];
    let bomSelectedCombinations = [];
    let bomCostingContents = [];
    let bomModalItems = [];
    let bomModalRequest = null;
    let bomContentTables = [];
    let bomSavedItems = [];

    // Urutan = urutan kolom di tabel (dipakai seleksi, copy/paste, dan navigasi panah).
    const BOM_EDIT_FIELDS = ['shell', 'qty', 'unit', 'allowance', 'id_supplier', 'price'];
    const BOM_NUMBER_RULES = {
        qty: value => value > 0,
        allowance: value => value >= 0 && value <= 100,
        price: value => value >= 0
    };
    const bomUnitOptions = @json($unitOptions);
    const bomSupplierOptions = @json($supplierOptions);
    const bomShellOptions = @json($shellOptions);
    // Kolom dropdown di grid: opsi, teks pilihan kosong, dan boleh kosong atau tidak.
    const BOM_SELECT_FIELDS = {
        unit: { options: bomUnitOptions, placeholder: 'Pilih Unit', optional: false },
        id_supplier: { options: bomSupplierOptions, placeholder: '- Tanpa Supplier -', optional: true },
        shell: { options: bomShellOptions, placeholder: '- Tanpa Shell -', optional: true }
    };
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
            console.log('Tab shown:', pane, $table.length);
            if ($.fn.DataTable.isDataTable($table)) {
                $table.DataTable().columns.adjust().draw(false);
            }
        });
        $('input[name="bom-modal-rule"]').on('change', renderBomModalRows);
        $('#bom-modal-check-all').on('change', function() {
            $('#bom-modal-items tbody .bom-row-checkbox').prop('checked', $(this).prop('checked'));
            updateBomModalSelectedBadge();
        });
        $(document).on('change', '#bom-modal-items .bom-row-checkbox', updateBomModalSelectedBadge);
        $('#bom-batch-color-select, #bom-batch-size-select').on('change', selectBomModalRowsByFilter);
        $('#bom-batch-update-btn').on('click', applyBomBatchItem);
        $('#bom-modal-save').on('click', saveBomJoItems);
        initializeBomGridEvents();
        // Semua dropdown di modal memakai select2; dropdownParent wajib agar kotak cari bisa diketik di dalam modal.
        // Shell/Unit di Definition Item: allowClear = tanda X untuk menghapus pilihan (state 2).
        $('.bom-modal-select2').each(function () {
            const clearable = $(this).closest('.bom-modal-fields').length > 0 && $(this).find('option[value=""]').length > 0;
            $(this).select2(Object.assign({ theme: 'bootstrap4', width: '100%', dropdownParent: $('#bom-item-modal') }, clearable ? {
                allowClear: true,
                placeholder: $(this).find('option[value=""]').first().text()
            } : {}));
        });
        $(document).on('select2:open', function() {
            const search = document.querySelector('.select2-container--open .select2-search__field');
            if (search) search.focus();
        });
        $(document).on('click', '.bom-item-delete', function() {
            deleteBomItems([$(this).attr('data-id')], $(this).attr('data-label'));
        });
        $(document).on('change', '.bom-content-check-all', function() {
            const checked = $(this).prop('checked');
            getBomContentRows($(this).closest('.tab-pane'))
                .find('.bom-content-check:not(:disabled)').prop('checked', checked);
            updateBomDeleteSelectedButton();
        });
        $(document).on('change', '.bom-content-check', updateBomDeleteSelectedButton);
        $('#bom-delete-selected').on('click', function() {
            const ids = getBomCheckedItemIds();
            deleteBomItems(ids, `${ids.length} item dari content yang dicentang`);
        });
        $('#form-bom').on('submit', function(event) {
            event.preventDefault();
            saveBomGridChanges();
        });
        let bomAlignTimer = null;
        $(window).on('resize', function() {
            clearTimeout(bomAlignTimer);
            bomAlignTimer = setTimeout(() => alignBomItemRows($('#bom-content-panels .bom-content-table tbody tr')), 150);
        });
        $(window).on('beforeunload', function(event) {
            if (bomPendingSync || getBomDirtyItems().length || hasBomGridErrors()) {
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

    // Carousel coverflow: semua gambar dirender sekali; pindah gambar hanya menggeser & menskala (transition CSS).
    // Sebelum/sesudah tidak berputar: di gambar pertama sisi kiri kosong, di gambar terakhir sisi kanan kosong.
    function paintCoverflow($stage, urls, active, goTo) {
        const stage = $stage[0];
        if (!stage) return;
        let $items = $stage.children('img.mkt-cf-item');
        const key = urls.join('\n');
        let fresh = false;
        if ($stage.data('cfKey') !== key) {
            $items.remove();
            $items = $(urls.map((url, index) => $('<img class="mkt-cf-item" alt="">').attr('src', url)
                .on('click', () => $stage.data('cfGoTo')(index))[0]));
            $stage.append($items).data('cfKey', key);
            fresh = true;
        }
        $stage.data({ cfGoTo: goTo, cfActive: active, cfUrls: urls });
        // Jarak tengah gambar aktif ke tengah gambar samping 195px (182/2 + celah 59 + 90/2); menyempit di layar kecil.
            const gap = Math.min(195, Math.max(0, stage.clientWidth / 2 - 49));
        $items.each(function (index) {
            const offset = index - active, far = Math.abs(offset) > 1;
            const x = Math.max(-2, Math.min(2, offset)) * gap;
            const scale = far ? 0.8 : 1;   // samping pakai ukuran sendiri (90x192), bukan diskala
            if (fresh) this.style.transition = 'none'; // posisi awal tanpa animasi
            this.style.transform = `translate(-50%, -50%) translateX(${x}px) scale(${scale})`;
            this.style.zIndex = String(10 - Math.min(Math.abs(offset), 9));
            this.style.opacity = offset === 0 ? '1' : '0.85';
            $(this).toggleClass('is-active', offset === 0).toggleClass('is-side', offset !== 0).toggleClass('is-far', far);
        });
        if (fresh) { void stage.offsetWidth; $items.css('transition', ''); }
        // Hitung ulang posisi saat ukuran panggung berubah (resize, atau bagian yang tadinya tersembunyi dibuka).
        if (!$stage.data('cfObserver') && window.ResizeObserver) {
            const observer = new ResizeObserver(() => {
                const urlsNow = $stage.data('cfUrls');
                if (urlsNow) paintCoverflow($stage, urlsNow, $stage.data('cfActive'), $stage.data('cfGoTo'));
            });
            observer.observe(stage);
            $stage.data('cfObserver', observer);
        }
    }
    function paintCoverflowDots($dots, count, active, goTo) {
        $dots.empty();
        if (count < 2) return;
        for (let i = 0; i < count; i++) {
            $('<button type="button" class="mkt-cf-dot"></button>')
                .toggleClass('is-active', i === active)
                .attr('aria-label', `Gambar ${i + 1}`)
                .on('click', () => goTo(i))
                .appendTo($dots);
        }
    }

    function renderBomCostingImage() {
        let hasImages = bomCostingImages.length > 0;
        const $thumbnails = $('#bom-image-thumbnails').empty().toggleClass('d-none', !hasImages);
        $('#bom-image-empty').text(hasImages ? '' : 'Gambar Costing tidak tersedia.')
            .prop('hidden', hasImages);
        $('#bom-preview-image').prop('hidden', !hasImages);
        $('#bom-image-prev, #bom-image-next').prop('disabled', bomCostingImages.length < 2);
        const $stage = $('#bom-preview-image').closest('.mkt-cf-stage');

        if (!hasImages) {
            $('#bom-preview-image').attr('src', '');
            paintCoverflow($stage, [], 0, $.noop);
            paintCoverflowDots($('#bom-image-counter'), 0, 0, $.noop);
            $('#bom-image-caption').text('');
            return;
        }

        bomSelectedImage = (bomSelectedImage + bomCostingImages.length) % bomCostingImages.length;
        $('#bom-preview-image').attr('src', bomCostingImages[bomSelectedImage]);
        const goTo = index => { bomSelectedImage = index; renderBomCostingImage(); };
        paintCoverflow($stage, bomCostingImages, bomSelectedImage, goTo);
        bomCostingImages.forEach((url, index) => {
            const $thumbnail = $('<button type="button" class="bom-image-thumbnail"></button>')
                .toggleClass('is-active', index === bomSelectedImage)
                .attr({
                    'aria-label': `Tampilkan gambar ${index + 1}`,
                    'aria-pressed': index === bomSelectedImage ? 'true' : 'false',
                    'data-image-index': index
                });
            $('<img alt="">').attr('src', url).appendTo($thumbnail);
            $thumbnails.append($thumbnail);
        });
        paintCoverflowDots($('#bom-image-counter'), bomCostingImages.length, bomSelectedImage, goTo);
        $('#bom-image-caption').text(`Image ${bomSelectedImage + 1} of ${bomCostingImages.length}`);
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
                setSummary('#bom-summary-planned-qty', data.planned_qty ? formatBomNumber(data.planned_qty) : '-');
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
                $('<td>').text(qty ? formatBomNumber(qty) : '-').appendTo($row);
            });
            grandTotal += rowTotal;
            $('<td>').addClass('bom-breakdown-total').text(formatBomNumber(rowTotal)).appendTo($row);
            $tbody.append($row);
        });

        let $totalRow =$('<tr>');
        $('<th>').attr('colspan', 3).addClass('text-left').text('TOTAL').appendTo($totalRow);
        sizes.forEach(function(size) {
            $('<th>').text(formatBomNumber(sizeTotals.get(String(size.id)))).appendTo($totalRow);
        });
        $('<th>').text(formatBomNumber(grandTotal)).appendTo($totalRow);
        $tfoot.append($totalRow);
    }

    function renderBomContents(contents, savedItems) {
        const categories = ['Fabric', 'Accessories Sewing', 'Accessories Packing'];
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
                $('<th>').html('<input type="checkbox" class="bom-content-check-all" aria-label="Pilih semua content">'),
                createBomColumnHeader('NO', 'th-costing', false),
                createBomColumnHeader('CONTENT', 'th-costing', false),
                createBomColumnHeader('ITEMS DESCRIPTION', 'th-so', false),
                createBomColumnHeader('COLOR MATERIALS', 'th-so', false),
                createBomColumnHeader('SHELL', 'th-input', true),
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
                            : 'Other Cost tidak bisa ditambahkan item BOM'
                    }).addClass('mkt-icon-btn mkt-btn--navy bom-content-add')
                        .append($('<i>').addClass('fas fa-plus'))
                        .data('content', content);
                    if (!content.content_id) $button.prop('disabled', true);

                    const $description = $('<td>').addClass('bom-desc-cell bom-wrap-cell').text('-');
                    const $colorMaterials = $('<td>').addClass('bom-wrap-cell').text('-');
                    const $shell = $('<td>').addClass('bom-wrap-cell').text('-');
                    const $origQty = $('<td>').addClass('text-right').text('-');
                    const $cons = $('<td>').addClass('text-right').text('-');
                    const $consUnit = $('<td>').text('-');
                    const $qtyReq = $('<td>').addClass('text-right').text('-');
                    const $loss = $('<td>').addClass('text-right').text('-');
                    const $value = $('<td>').addClass('text-right').text('-');
                    const $totalQty = $('<td>').addClass('text-right').text('-');
                    const $totalUnit = $('<td>').text('-');
                    const $supplier = $('<td>').addClass('bom-supplier-cell bom-wrap-cell').text('-');
                    const $price = $('<td>').addClass('text-right').text('-');
                    const $action = $('<td>').addClass('text-center').text('-');
                    const $priceCosting = $('<td>').addClass('text-right')
                        .text(content.price_costing !== null && content.price_costing !== undefined ? formatBomMoney(content.price_costing) : '-');

                    contentItems.forEach(function(item) {
                        const id = String(item.id);
                        appendBomSavedItemValue($description, `${decodeBomEntities(item.item_name || '-').trim()} - ${item.size_name || 'ALL'}`);
                        appendBomSavedItemValue($colorMaterials, item.color_name || 'ALL');
                        appendBomGridCell($shell, id, 'shell');
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
                        appendBomSavedItemValue($priceCosting, content.price_costing !== null && content.price_costing !== undefined ? formatBomMoney(content.price_costing) : '-');
                        appendBomSavedItemValue($action, '').append(
                            $('<button>').attr({
                                type: 'button',
                                title: `Hapus item ${item.size_name || 'ALL'} / ${item.color_name || 'ALL'}`,
                                'data-id': id,
                                'data-label': `${content.content_name || '-'} (${item.size_name || 'ALL'} / ${item.color_name || 'ALL'})`
                            }).addClass('mkt-icon-btn mkt-btn--red bom-item-delete').append($('<i>').addClass('fas fa-trash'))
                        );
                    });

                    $('<tr>').addClass(contentItems.length ? 'bom-content-has-items' : '').append(
                        $('<td>').append($('<input>').attr({
                            type: 'checkbox',
                            'aria-label': `Pilih semua item ${content.content_name || ''}`,
                            'data-ids': contentItems.map(item => item.id).join(',')
                        }).addClass('bom-content-check').prop('disabled', !contentItems.length)),
                        $('<td>').text(rowNumber++),
                        $('<td>').append(
                            $('<div>').addClass('bom-content-row').append(
                                $('<span>').text(content.content_name || '-'),
                                $button
                            )
                        ),
                        $description,
                        $colorMaterials,
                        $shell,
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
                        $action
                    ).appendTo($tbody);
                });
            }
            $pane.append($table);
            $panels.append($pane);
        });

        bomSelection = null;
        updateBomDeleteSelectedButton();
        updateBomPendingIndicator();
        $panels.find('.bom-cell').each(function() { paintBomCell($(this)); });
        $panels.find('.bom-auto').each(function() { paintBomAutoCell($(this)); });
        initializeBomContentTable($('#bom-content-pane-0 .bom-content-table'));

        if (!hasContents) {
            $panels.prepend($('<div>').addClass('alert alert-info')
                .text('Costing ini belum memiliki content Fabric/Accessories untuk ditambahkan ke BOM.'));
        }
    }

    function appendBomSavedItemValue($cell, value) {
        if ($cell.children('.bom-saved-item-value').length === 0) {
            $cell.empty();
        }
        return $('<div>').addClass('bom-saved-item-value').text(value).attr('title', value || null).appendTo($cell);
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
            scrollX: false,
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
                { targets: [0, 1, 17], className: 'text-center' },
                { targets: 0, width: '30px' }
            ]
        }).on('draw.dt', function() {
            bomSelection = null;
            paintBomSelection();
            alignBomItemRows($table.find('tbody tr'));
        });
        alignBomItemRows($table.find('tbody tr'));
    }

    // Item ditumpuk per sel; samakan tinggi item ke-i di semua kolom supaya garis pemisah tetap lurus
    // walau ITEMS DESCRIPTION / SUPPLIER turun baris. Hanya bisa mengukur baris yang sedang terlihat.
    function alignBomItemRows($rows) {
        $rows.filter(':visible').each(function() {
            const columns = $(this).children('td').get()
                .map(td => Array.from(td.children).filter(el => el.classList.contains('bom-saved-item-value')))
                .filter(items => items.length);
            if (!columns.length) return;

            columns.forEach(items => items.forEach(item => { item.style.height = ''; }));
            const count = Math.max(...columns.map(items => items.length));
            for (let index = 0; index < count; index++) {
                const height = Math.max(...columns.map(items => items[index] ? items[index].getBoundingClientRect().height : 0));
                columns.forEach(items => {
                    if (items[index]) items[index].style.height = `${Math.ceil(height)}px`;
                });
            }
        });
    }

    // itemdesc di masteritem tersimpan dengan entity HTML (mis. &quot;); textarea hanya mendekode, tidak mengeksekusi.
    function decodeBomEntities(text) {
        const textarea = document.createElement('textarea');
        textarea.innerHTML = String(text ?? '');
        return textarea.value;
    }

    // Format angka Indonesia: '.' ribuan, ',' desimal (17.000 ; 0,5 ; 1.234.567,5).
    // grouping=false untuk isi editor/copy; maxDigits default 4 untuk tampilan tabel.
    const bomNumberFormats = {};
    function formatBomNumber(value, grouping = true, maxDigits = 4) {
        const key = `${grouping ? 1 : 0}_${maxDigits}`;
        bomNumberFormats[key] = bomNumberFormats[key]
            || new Intl.NumberFormat('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: maxDigits, useGrouping: grouping });
        return bomNumberFormats[key].format(Number(value) || 0);
    }

    // Harga (Price / Price Costing) minimal 2 desimal, sama dengan tabel Costing: 17000 -> 17.000,00.
    function formatBomMoney(value) {
        bomNumberFormats.money = bomNumberFormats.money
            || new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 4 });
        return bomNumberFormats.money.format(Number(value) || 0);
    }

    // Presisi penuh untuk isi editor, copy, dan input modal, agar simpan ulang tanpa perubahan tidak membulatkan nilai.
    function formatBomEditNumber(value) {
        return formatBomNumber(value, false, 10);
    }

    // Terima format Indonesia maupun Excel/US. Ada '.' dan ',' -> pemisah terakhir = desimal;
    // hanya ',' -> koma desimal; hanya '.' -> ribuan bila pola 1.234(.567) selain itu titik desimal (0.5).
    function parseBomNumber(text) {
        let value = String(text ?? '').replace(/[\s %]/g, '');
        if (value === '') return null;
        const lastComma = value.lastIndexOf(',');
        const lastDot = value.lastIndexOf('.');
        if (lastComma > -1 && lastDot > -1) {
            value = lastComma > lastDot ? value.replace(/\./g, '').replace(',', '.') : value.replace(/,/g, '');
        } else if (lastComma > -1) {
            value = value.replace(',', '.');
        } else if (/^-?\d{1,3}(\.\d{3})+$/.test(value)) {
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
                price: record.values.price,
                shell: record.values.shell || null
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
                price: Number(item.price) || 0,
                shell: item.shell ? String(item.shell) : ''
            };
            const previous = bomGridState.get(id);
            const keepEdits = previous && (previous.errors.size > 0 || BOM_EDIT_FIELDS.some(field => isBomFieldDirty(previous, field)));
            const record = keepEdits
                ? Object.assign(previous, { original: original })
                : { original: original, values: Object.assign({}, original), text: {}, errors: new Set(), pasted: new Set() };
            record.labels = { unit: item.unit_name || '', id_supplier: item.supplier_name || '', shell: item.nama_panel || '' };
            record.origQty = getBomOrigQty(item);
            next.set(id, record);
        });
        bomGridState = next;
    }

    function getBomCellText(record, field) {
        if (Object.prototype.hasOwnProperty.call(record.text, field)) return record.text[field];
        const value = record.values[field];
        if (BOM_SELECT_FIELDS[field]) {
            const option = findBomOption(BOM_SELECT_FIELDS[field].options, value);
            if (option) return option.name;
            return value !== '' && value === record.original[field] && record.labels[field] ? record.labels[field] : '-';
        }
        return field === 'price' ? formatBomMoney(value) : formatBomNumber(value);
    }

    function getBomCellRawValue(record, field) {
        if (Object.prototype.hasOwnProperty.call(record.text, field)) return record.text[field];
        if (BOM_SELECT_FIELDS[field]) {
            const text = getBomCellText(record, field);
            return text === '-' ? '' : text;
        }
        return formatBomEditNumber(record.values[field]);
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
        const text = getBomCellText(record, field);
        $cell.text(text).attr('title', text)
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
        alignBomItemRows($(`.bom-cell[data-id="${id}"]`).first().closest('tr'));
        updateBomPendingIndicator();
    }

    function setBomCellValue(id, field, raw, pasted) {
        const record = bomGridState.get(String(id));
        if (!record) return;
        const text = String(raw ?? '').trim();
        let value = null;
        let valid;
        if (BOM_SELECT_FIELDS[field]) {
            if (text === '' || text === '-') {
                value = '';
                valid = BOM_SELECT_FIELDS[field].optional;
            } else {
                const option = findBomOption(BOM_SELECT_FIELDS[field].options, text);
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
        if (BOM_SELECT_FIELDS[field]) {
            const { options, placeholder } = BOM_SELECT_FIELDS[field];
            $input = $('<select>').addClass('form-control form-control-sm bom-cell-input')
                .append($('<option>').val('').text(placeholder));
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
        if ($input.is('select')) {
            openBomCellSelect($input, initialText);
            return;
        }
        $input.trigger('focus');
        const input = $input[0];
        initialText === undefined ? input.select() : input.setSelectionRange(input.value.length, input.value.length);
    }

    // Unit/Supplier diedit dengan select2 supaya bisa dicari; huruf yang diketik untuk mulai edit jadi kata kunci.
    function openBomCellSelect($select, initialText) {
        const isCurrent = () => bomEditing && bomEditing.$input === $select;
        const originalValue = $select.val();
        $select.select2({ theme: 'bootstrap4', width: '100%', dropdownAutoWidth: true })
            .on('select2:select', function() {
                setTimeout(function() {
                    if (!isCurrent()) return;
                    commitBomEdit();
                    paintBomSelection();
                });
            })
            .on('select2:close', function() {
                // Ditutup tanpa memilih (Esc / klik di luar) = batal.
                setTimeout(function() {
                    if (isCurrent() && $select.val() === originalValue) cancelBomEdit();
                });
            });
        $select.select2('open');
        const $search = $('.select2-container--open .select2-search__field');
        $search.trigger('focus');
        if (initialText !== undefined) $search.val(initialText).trigger('input');
    }

    function destroyBomCellSelect($input) {
        if ($input.is('select') && $input.data('select2')) $input.select2('destroy');
    }

    function commitBomEdit() {
        if (!bomEditing) return;
        const { $cell, $input } = bomEditing;
        bomEditing = null;
        const value = $input.val();
        destroyBomCellSelect($input);
        $cell.removeClass('is-editing');
        setBomCellValue($cell.attr('data-id'), $cell.attr('data-field'), value, false);
    }

    function cancelBomEdit() {
        if (!bomEditing) return;
        const { $cell, $input } = bomEditing;
        bomEditing = null;
        destroyBomCellSelect($input);
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
            // Dropdown select2 dirender di <body>, jadi klik di dalamnya bukan "klik di luar sel".
            if ($(event.target).closest('.bom-cell, .bom-fill-handle, .select2-container, .select2-dropdown').length) return;
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
                event.preventDefault();
                startBomEdit(getBomCellAt(bomSelection.$table, ids, bomSelection.focus.row, bomSelection.focus.col), event.key);
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

    // Baris di halaman DataTables lain tidak ada di DOM, jadi ambil lewat API DataTables.
    function getBomContentRows($scope) {
        const $table = $scope.find('table[id^="bom-content-table-"]').first();
        return $.fn.DataTable.isDataTable($table) ? $table.DataTable().rows().nodes().to$() : $table.find('tbody tr');
    }

    function getBomCheckedItemIds() {
        const ids = [];
        $('#bom-content-panels .tab-pane').each(function() {
            getBomContentRows($(this)).find('.bom-content-check:checked').each(function() {
                ids.push(...String($(this).attr('data-ids') || '').split(',').filter(Boolean));
            });
        });
        return ids;
    }

    function updateBomDeleteSelectedButton() {
        const count = getBomCheckedItemIds().length;
        $('#bom-delete-selected').prop('disabled', count === 0);
        $('#bom-delete-selected-count').text(count ? `(${count})` : '');
    }

    function deleteBomItems(ids, label) {
        if (!ids.length || !currentBomId) return;
        Swal.fire({
            icon: 'warning',
            title: 'Hapus item BOM?',
            html: `${$('<div>').text(label).html()} akan dihapus dari BOM.<br>Perubahan yang belum disimpan pada item ini ikut hilang.`,
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Ya, hapus',
            cancelButtonText: 'Batal'
        }).then(function(result) {
            if (!result.isConfirmed) return;
            Swal.fire({ title: 'Menghapus item...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            $.ajax({
                url: "{{ route('delete-items-bom') }}",
                method: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    id_bom_marketing: currentBomId,
                    ids: ids
                }
            }).done(function(res) {
                if (res.status !== 200) {
                    Swal.fire('Gagal', res.message || 'Item BOM gagal dihapus.', 'error');
                    return;
                }
                ids.forEach(id => bomGridState.delete(String(id)));
                bomPendingSync = true;
                loadBomSavedItems()
                    .done(() => Swal.fire('Berhasil', res.message, 'success'))
                    .fail(xhr => Swal.fire('Terhapus', xhr?.responseJSON?.message || 'Item terhapus, tetapi tabel belum berhasil dimuat ulang.', 'warning'));
            }).fail(function(xhr) {
                const errors = xhr.responseJSON?.errors;
                Swal.fire('Gagal', errors ? Object.values(errors).flat()[0] : (xhr.responseJSON?.message || 'Item BOM gagal dihapus.'), 'error');
            });
        });
    }

    // Save form: simpan perubahan tabel (bila ada) lalu kirim seluruh BOM ke BOM JO.
    function saveBomGridChanges() {
        commitBomEdit();
        if (hasBomGridErrors()) {
            const count = Array.from(bomGridState.values()).reduce((total, record) => total + record.errors.size, 0);
            showMktErrorAlert('#bom-error-alert', count, count + (count === 1 ? ' cell is' : ' cells are')
                + ' not valid. Fix the red cells (Cons/PC > 0, Loss % 0-100, Price not negative, Unit/Supplier from master), then save again.');
            return;
        }
        $('#bom-error-alert').prop('hidden', true);
        if (!bomSavedItems.length && !bomPendingSync) {
            Swal.fire('Belum ada item', 'Tambahkan item BOM melalui tombol + pada content terlebih dahulu.', 'warning');
            return;
        }
        const items = getBomDirtyItems();

        $('#bom-grid-save').prop('disabled', true);
        Swal.fire({ title: 'Menyimpan BOM...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
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
            bomPendingSync = false;
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
        if (!bomSelectedCombinations.length) {
            Swal.fire('Sales Order belum lengkap', 'Sales Order belum memiliki kombinasi Color dan Size, jadi item BOM belum bisa ditambahkan.', 'warning');
            return;
        }
        loadBomModalContent(content);
    }

    function updateBomPendingIndicator() {
        $('#bom-pending-sync').prop('hidden', !(bomPendingSync || getBomDirtyItems().length));
    }

    function loadBomModalContent(content) {
        if (bomModalRequest) bomModalRequest.abort();

        $('#bom-item-modal-title').text(`Add Items BOM - ${content.content_name || content.label}`);
        $('#bom-modal-content-id').val(content.content_id);
        $('#bom-modal-content-category').val(content.category);
        $('#bom-modal-supplier').val(content.supplier_id || '');
        $('#bom-modal-shell, #bom-modal-unit').val('').trigger('change.select2');
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

        $('#bom-modal-items .bom-modal-item-select').each(function() {
            if ($(this).data('select2')) $(this).select2('destroy');
        });
        const $tbody =$('#bom-modal-items tbody').empty();
        $('#bom-modal-check-all').prop('checked', false);
        fillBomBatchOptions(rows);
        if (!rows.length || !bomModalItems.length) {
            $('<tr>').append($('<td>').attr('colspan', 6).addClass('text-center text-muted')
                .text(!rows.length ? 'SO belum memiliki kombinasi Color/Size.' : 'Tidak ada item untuk content ini.'))
                .appendTo($tbody);
            updateBomModalSelectedBadge();
            return;
        }

        rows.forEach(function(row, index) {
            const $item =$('<select>').addClass('form-control form-control-sm bom-modal-item-select')
                .append($('<option>').val('').text('Choose Item Description'));
            bomModalItems.forEach(item => $item.append($('<option>').val(item.isi).text(item.tampil)));

            $('<tr>').attr({
                'data-id-color': row.colorId || '',
                'data-id-size': row.sizeId || '',
                'data-color': row.color,
                'data-size': row.size
            }).append($('<td>').html('<input type="checkbox" class="bom-row-checkbox">'),
                $('<td>').text(index + 1),
                $('<td>').append($item),
                $('<td>').text(row.color),$('<td>').text(row.size),
                $('<td>').append($('<input>').attr({
                    type: 'text',
                    inputmode: 'decimal',
                    placeholder: '0,0000'
                }).addClass('form-control form-control-sm bom-modal-qty'))
            ).appendTo($tbody);
        });
        $tbody.find('.bom-modal-item-select').select2({
            theme: 'bootstrap4',
            width: '100%',
            dropdownParent: $('#bom-item-modal')
        });
        updateBomModalSelectedBadge();
    }

    // Bar batch: Color/Size untuk mencentang baris yang cocok, Update mengisi Item Description ke baris tercentang.
    function fillBomBatchOptions(rows) {
        const $item = $('#bom-batch-item-desc').empty().append($('<option>').val('').text('Choose Item Description'));
        bomModalItems.forEach(item => $item.append($('<option>').val(item.isi).text(item.tampil)));

        [
            ['#bom-batch-color-select', 'color', 'Choose Color', 'All Color'],
            ['#bom-batch-size-select', 'size', 'Choose Size', 'All Size']
        ].forEach(function([selector, key, placeholder, allLabel]) {
            const $select = $(selector).empty()
                .append($('<option>').val('').text(placeholder))
                .append($('<option>').val('*').text(allLabel));
            [...new Set(rows.map(row => row[key]))]
                .filter(value => value !== 'ALL')
                .forEach(value => $select.append($('<option>').val(value).text(value)));
        });
        $('#bom-batch-item-desc, #bom-batch-color-select, #bom-batch-size-select').trigger('change.select2');
        $('#bom-batch-cons').val('');
    }

    function updateBomModalSelectedBadge() {
        const $boxes = $('#bom-modal-items tbody .bom-row-checkbox');
        const checked = $boxes.filter(':checked').length;
        $('#bom-modal-selected-badge').text(`Selected ${checked}`);
        $('#bom-modal-check-all').prop('checked', $boxes.length > 0 && checked === $boxes.length);
    }

    function selectBomModalRowsByFilter() {
        const color = $('#bom-batch-color-select').val();
        const size = $('#bom-batch-size-select').val();
        if (!color && !size) return;
        // '*' = All; kosong = belum dipilih, juga dianggap semua.
        const matches = (filter, value) => !filter || filter === '*' || value === filter;
        $('#bom-modal-items tbody tr').each(function() {
            const match = matches(color, $(this).attr('data-color')) && matches(size, $(this).attr('data-size'));
            $(this).find('.bom-row-checkbox').prop('checked', match);
        });
        updateBomModalSelectedBadge();
    }

    function applyBomBatchItem() {
        const idItem = $('#bom-batch-item-desc').val();
        const consText = $('#bom-batch-cons').val().trim();
        const cons = consText === '' ? null : parseBomNumber(consText);
        const $checked = $('#bom-modal-items tbody .bom-row-checkbox:checked');
        if (!idItem && consText === '') {
            Swal.fire('Belum ada nilai', 'Pilih Item Description dan/atau isi Cons/PC yang akan diterapkan.', 'warning');
            return;
        }
        if (consText !== '' && (cons === null || cons <= 0)) {
            Swal.fire('Cons/PC tidak valid', 'Cons/PC harus berupa angka lebih dari 0.', 'warning');
            return;
        }
        if (!$checked.length) {
            Swal.fire('Belum ada baris', 'Centang baris yang akan diisi, atau pilih Color/Size untuk mencentang otomatis.', 'warning');
            return;
        }
        const $rows = $checked.closest('tr');
        if (idItem) $rows.find('.bom-modal-item-select').val(idItem).trigger('change.select2').closest('td').removeClass('is-invalid');
        if (cons !== null) $rows.find('.bom-modal-qty').val(formatBomEditNumber(cons)).trigger('input');
    }

    // Banner merah di atas form: "N fields are empty. Fill them in, then save again."
    function showMktErrorAlert(selector, count, text) {
        const $alert = $(selector);
        $alert.find('span').text(text || (count + (count === 1 ? ' field is' : ' fields are') + ' empty. Fill them in, then save again.'));
        $alert.prop('hidden', count === 0);
        if (count > 0) $alert[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    // Isian modal yang sudah diisi: merahnya hilang dan jumlah di banner berkurang.
    $(document).on('change input', '#bom-item-modal .is-invalid :input', function () {
        const $cell = $(this).closest('.is-invalid');
        const value = $(this).val();
        const filled = $(this).hasClass('bom-modal-qty') ? parseBomNumber(value) > 0 :String(value || '').trim() !== '';
        if (!filled) return;
        $cell.removeClass('is-invalid');
        const count = $('#bom-item-modal .is-invalid').length;
        const $alert = $('#bom-modal-error-alert');
        if (!$alert.prop('hidden')) {
            $alert.find('span').text(count + (count === 1 ? ' field is' : ' fields are') + ' empty. Fill them in, then save again.');
            $alert.prop('hidden', count === 0);
        }
    });

    function saveBomJoItems() {
        const rule = $('input[name="bom-modal-rule"]:checked').val();
        const items = [];
        let invalid = false;
        let emptyCount = 0;
        $('#bom-modal-items tbody tr').each(function() {
            const idItem = $(this).find('.bom-modal-item-select').val();
            const qty = parseBomNumber($(this).find('.bom-modal-qty').val());
            const itemEmpty = !idItem;
            const qtyEmpty = qty === null || !Number.isFinite(qty) || qty <= 0;
            $(this).find('.bom-modal-item-select').closest('td').toggleClass('is-invalid', itemEmpty);
            $(this).find('.bom-modal-qty').closest('td').toggleClass('is-invalid', qtyEmpty);
            if (itemEmpty || qtyEmpty) {
                invalid = true;
                emptyCount += itemEmpty + qtyEmpty;
                return;
            }
            items.push({
                id_item: idItem,
                qty: qty,
                id_color: $(this).attr('data-id-color') || null,
                id_size: $(this).attr('data-id-size') || null
            });
        });

        const unit = $('#bom-modal-unit').val();
        $('#bom-modal-unit').closest('.col-md-4').toggleClass('is-invalid', !unit);
        if (!unit) emptyCount++;
        if (invalid || !items.length || !unit) {
            if (!emptyCount && !items.length) {
                Swal.fire('Belum ada item', 'Tidak ada baris item untuk disimpan.', 'warning');
                return;
            }
            showMktErrorAlert('#bom-modal-error-alert', emptyCount);
            return;
        }
        $('#bom-modal-error-alert').prop('hidden', true);

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
                    Swal.fire('Gagal', res.message || 'Item gagal disimpan ke BOM.', 'error');
                    return;
                }
                $('#bom-item-modal').modal('hide');
                bomPendingSync = true;
                loadBomSavedItems()
                    .done(function() {
                        Swal.fire('Berhasil', res.message, 'success');
                    })
                    .fail(function(xhr) {
                        Swal.fire('Item tersimpan', xhr.responseJSON?.message || 'Item tersimpan, tetapi tabel belum berhasil dimuat ulang.', 'warning');
                    });
            },
            error: function(xhr) {
                Swal.fire('Gagal', xhr.responseJSON?.message || 'Item gagal disimpan ke BOM.', 'error');
            },
            complete: function() {
                $('#bom-modal-save').prop('disabled', false);
            }
        });
    }
</script>
@endsection
