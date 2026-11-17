@extends('layouts.index')

@section('custom-link')
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('dist/css/marketing-form.css') }}?v={{ @filemtime(public_path('assets/dist/css/marketing-form.css')) }}">
    <link rel="stylesheet" href="{{ asset('dist/css/marketing-buttons.css') }}?v={{ @filemtime(public_path('assets/dist/css/marketing-buttons.css')) }}">
    {{-- CSS halaman BOM lama (marketing/bom/create): header, info, gambar, breakdown, tab, dan tombol tampil sama. --}}
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
        .bom-modal-fields > [class*="col-"] .select2-container--bootstrap4 .select2-selection { position: relative; }
        .bom-modal-fields > [class*="col-"] .select2-container--bootstrap4 .select2-selection__clear { display: none; position: absolute; top: 50%; right: 14px; z-index: 2; float: none; width: 18px; height: 18px; margin: 0; padding: 0; transform: translateY(-50%); color: #284a63; font-size: 20px; font-weight: 400; line-height: 18px; text-align: center; cursor: pointer; }
        .bom-modal-fields > [class*="col-"] .select2-container--bootstrap4:not(.select2-container--disabled):is(:hover, .select2-container--focus, .select2-container--open) .select2-selection__clear { display: block; }
        .bom-modal-fields > [class*="col-"] .select2-container--bootstrap4:not(.select2-container--disabled):is(:hover, .select2-container--focus, .select2-container--open) .select2-selection--single:has(.select2-selection__clear) .select2-selection__arrow { display: none; }
        .bom-modal-fields > [class*="col-"] .select2-container--bootstrap4:not(.select2-container--disabled):hover .select2-selection:has(.select2-selection__clear) { border-color: #284a63; }
        .bom-modal-fields > [class*="col-"]:has(.select2-container--disabled) label, .bom-modal-fields > [class*="col-"]:has(.form-control:disabled) label, .bom-modal-fields > [class*="col-"]:has(.form-control[readonly]) label { color: #c4c4c4; }

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
            grid-template-columns: repeat(4, minmax(0, 1fr));
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
        #bom-breakdown-table thead th:nth-child(2) { width: 130px; }
        #bom-breakdown-table thead th:nth-child(3) { width: 130px; }
        #section-list-item > #bom-content-tabs { margin: 0 0 14px; }
        #section-list-item .bom-detail-card {
            margin: 0;
            overflow: hidden;
            border: 1px solid #e1e7ef;
            border-radius: 8px;
            box-shadow: 0 4px 14px rgba(30, 41, 59, .08);
        }
        #section-list-item .bom-detail-card > .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin: 12px 12px 4px;
            padding: 8px 16px;
            border: 1px solid #284a63;
            border-radius: 6px;
            background: #fff;
        }
        #section-list-item .bom-detail-card > .card-header .card-tools { margin: 0; }
        #section-list-item .bom-detail-card > .card-header .btn-tool {
            display: inline-flex; width: 20px; height: 20px; align-items: center; justify-content: center;
            padding: 0; border-radius: 5px; background: #aabac5; color: #fff; font-size: 10px;
        }
        #section-list-item .bom-detail-card > .card-header .btn-tool:hover { background: #8fa3b1; color: #fff; }
        #section-list-item .bom-detail-card > .card-header .btn-tool i { transition: transform .2s ease; }
        #section-list-item .bom-detail-card.collapsed-card > .card-header .btn-tool i { transform: rotate(180deg); }
        #section-list-item .bom-detail-card > .card-body { padding: 8px 12px 12px; background: #fff; }
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
        #bom-content-tabs > .nav-item { flex: 1 1 0; min-width: 0; }
        #bom-content-tabs .nav-link { display: block; width: 100%; text-align: center; cursor: pointer; }
        #bom-content-panels.is-all > .tab-pane { display: block; opacity: 1; }
        #bom-content-panels.is-all > .tab-pane + .tab-pane { margin-top: 22px; }
        .bom-pane-title { display: none; margin: 0 0 8px; color: #284a63; font-family: 'Poppins', sans-serif; font-size: 18px; font-weight: 600; }
        #bom-content-panels.is-all .bom-pane-title { display: block; }
        @media (max-width: 767.98px) {
            #bom-content-tabs > .nav-item { flex: 0 0 auto; }
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
        .bom-wrap-cell .bom-saved-item-value {
            white-space: normal;
            overflow-wrap: anywhere;
        }
        .bom-desc-cell { min-width: 100px; }
        .bom-desc-cell .bom-saved-item-value { max-width: 320px; }
        .bom-supplier-cell { min-width: 80px; }
        .bom-supplier-cell .bom-saved-item-value { max-width: 190px; }

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
        .bom-item-delete:not(.mkt-ico) {
            padding: 0 4px;
            border: 0;
            background: transparent;
            color: #dc2626;
            font-size: 10px;
            line-height: 14px;
        }
        .bom-item-delete:not(.mkt-ico):hover { color: #991b1b; }
        .bom-item-delete:not(.mkt-ico):disabled { opacity: .4; }
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
            #section-list-item .bom-detail-card > .card-body { padding-left: 4px; padding-right: 4px; }
            .bom-content-table thead th, .bom-content-table tbody td { padding-left: 2px; padding-right: 2px; }
        }
        @media (max-width: 991.98px) {
            .bom-summary-cards { grid-template-columns: repeat(3, minmax(0, 1fr)); }
            .bom-top-layout { grid-template-columns: 1fr; }
            .bom-rule-options { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (max-width: 575.98px) {
            .bom-summary-cards { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            #section-list-item .bom-detail-card > .card-body { padding: 6px; }
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
        #section-list-item .bom-detail-card > .card-header .card-title { color: #284a63; font-size: 16px; font-weight: 500; }
        input[type="checkbox"]:not(.custom-control-input) { -webkit-appearance: none; appearance: none; flex: 0 0 16px; width: 16px; height: 16px; padding: 0; border: 1.5px solid #cfd4dc; border-radius: 4px; background: #fff no-repeat center / 10px 10px; vertical-align: middle; cursor: pointer; transition: background-color .15s ease, border-color .15s ease, box-shadow .15s ease; }
        input[type="checkbox"]:not(.custom-control-input):hover:not(:checked):not(:indeterminate):not(:disabled) { background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12'%3e%3cpath fill='none' stroke='%23cfd4dc' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' d='M2.5 6.2l2.3 2.3 4.7-4.9'/%3e%3c/svg%3e"); }
        input[type="checkbox"]:not(.custom-control-input):checked { border-color: #284a63; background-color: #284a63; background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12'%3e%3cpath fill='none' stroke='%23fff' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' d='M2.5 6.2l2.3 2.3 4.7-4.9'/%3e%3c/svg%3e"); }
        input[type="checkbox"]:not(.custom-control-input):indeterminate { border-color: #284a63; background-color: #284a63; background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12'%3e%3cpath fill='none' stroke='%23fff' stroke-width='2' stroke-linecap='round' d='M3 6h6'/%3e%3c/svg%3e"); }
        input[type="checkbox"]:not(.custom-control-input):focus { outline: 0; }
        input[type="checkbox"]:not(.custom-control-input):focus-visible { box-shadow: 0 0 0 4px #b3daf7; }
        input[type="checkbox"]:not(.custom-control-input):disabled { opacity: .5; cursor: not-allowed; }

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
        /* Costing tanpa content Fabric/Accessories: alert oranye muda di atas legend + ilustrasi tabel kosong. */
        .bom-empty-alert { display: flex; align-items: center; gap: 8px; margin-bottom: 14px; padding: 10px 12px; border-radius: 4px; background: #fff4e8; color: #263746; font-family: Inter, sans-serif; font-size: 13px; }
        .bom-empty-alert[hidden], .bom-content-add[hidden] { display: none !important; }
        .bom-empty-alert > i { color: #f59e0b; }
        .bom-empty-alert > span { flex: 1; }
        .bom-empty-alert b { font-weight: 700; }
        .bom-empty-alert-close { flex: 0 0 auto; padding: 0; border: 0; background: none; color: #475569; font-size: 16px; line-height: 1; }
        .bom-content-table td.dataTables_empty { padding: 28px 8px !important; border: 0; background: #fff !important; }
        .dataTables_wrapper.is-empty .dataTables_length, .dataTables_wrapper.is-empty .dataTables_info, .dataTables_wrapper.is-empty .dataTables_paginate { display: none; }
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

        @media (max-width: 1199.98px) {
            .bom-summary-card { padding: 10px 12px; }
            .bom-summary-card span { font-size: 15px; }
            .bom-summary-card strong { font-size: 17px; }
            .bom-summary-item span, .bom-summary-item strong { font-size: 16px; }
            #bom-content-tabs { overflow-y: hidden; }
            #bom-content-tabs .nav-link { padding: 10px 16px; font-size: 21px; }
        }
        @media (max-width: 991.98px) {
            .bom-summary-cards { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
            .bom-summary-card { min-height: 0; }
            .bom-summary-card span { font-size: 14px; }
            .bom-summary-card strong { font-size: 18px; overflow-wrap: anywhere; white-space: normal; }
            .bom-summary-item span { font-size: 14px; }
            .bom-summary-item strong { font-size: 15px; overflow-wrap: anywhere; white-space: normal; }
            #bom-content-tabs .nav-link { padding: 8px 14px; font-size: 18px; }
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
            .bom-overview-title, #section-list-item .bom-detail-card > .card-header .card-title { font-size: 15px; }
            .bom-summary-item span { font-size: 13px; }
            .bom-summary-item strong { font-size: 14px; }
            .bom-image-review { gap: 8px; padding: 10px 6px; }
            #bom-content-tabs .nav-link { padding: 8px 12px; font-size: 16px; }
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

        .bom-summary-card > span { font-family: "Instrument Sans", 'Poppins', sans-serif !important; font-size: 20px !important; font-weight: 500 !important; }
        .bom-summary-card > strong { font-family: 'Poppins', sans-serif !important; font-size: 24px !important; font-weight: 700 !important; }
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
        #section-list-item .bom-detail-card > .card-header .card-title { font-family: 'Poppins', sans-serif !important; font-size: 25px !important; font-weight: 500 !important; }
        #bom-content-panels .bom-table-scroll { overflow-x: auto; padding-bottom: 4px; scrollbar-color: #284a63 #e2e8f0; }
        #bom-content-panels .bom-table-scroll::-webkit-scrollbar { height: 8px; }
        #bom-content-panels .bom-table-scroll::-webkit-scrollbar-track { background: #e2e8f0; border-radius: 4px; }
        #bom-content-panels .bom-table-scroll::-webkit-scrollbar-thumb { background: #284a63; border-radius: 4px; }
        #bom-content-panels .bom-content-table { width: auto !important; min-width: 100%; table-layout: auto !important; }
        #bom-content-panels .bom-content-table thead th { white-space: nowrap !important; }
        #bom-content-panels .bom-content-table thead th .bom-column-heading { flex-wrap: nowrap; white-space: nowrap; }
        #bom-content-panels .bom-content-table tr > :nth-child(n) { display: table-cell; }
        #bom-content-panels .bom-desc-cell { min-width: 240px; }
        #bom-content-panels .bom-supplier-cell { min-width: 160px; }
        .bom-cell-legend { display: flex; flex-wrap: wrap; gap: 8px 18px; margin: 12px 2px 0; color: #616161; font-family: Inter, sans-serif; font-size: 14px; font-weight: 400; }
        .bom-cell-legend > span { display: inline-flex; align-items: center; gap: 8px; }
        .bom-cell-legend i { width: 16px; height: 16px; border: 1px solid #cbd5e1; border-radius: 3px; background: #fff; }
        .bom-cell-legend i.is-auto { border-color: #e2e8f0; background: #f1f5f9; }
        .bom-cell-legend i.is-pasted { border-color: #3b82f6; background: #e0ecff; }
        .bom-cell-legend i.is-error { border-color: #ef4444; background: #fee2e2; }
        #section-list-item .bom-detail-card > .card-header::after { display: none; }
        .bom-create-page table thead th, #bom-content-panels table thead th, #bom-breakdown-table thead th { font-family: Inter, sans-serif !important; font-size: 12px !important; font-weight: 500 !important; }
        #bom-content-panels table tbody td, #bom-breakdown-table tbody td { font-family: Inter, sans-serif !important; font-size: 14px !important; font-weight: 400 !important; font-variant-numeric: lining-nums tabular-nums; }
        #bom-breakdown-table tfoot td, #bom-breakdown-table tfoot th { font-family: Inter, sans-serif !important; font-size: 14px !important; font-weight: 700 !important; font-variant-numeric: lining-nums tabular-nums; }
        #bom-content-panels .dataTables_paginate, #bom-content-panels .dataTables_paginate .paginate_button, #bom-content-panels .dataTables_paginate .page-link { font-family: 'Poppins', sans-serif !important; font-size: 16px !important; font-weight: 500 !important; }
        #bom-content-panels .dataTables_length, #bom-content-panels .dataTables_length label, #bom-content-panels .dataTables_info { font-family: 'Poppins', sans-serif !important; font-size: 16px !important; font-weight: 400 !important; }
        #bom-content-panels .dataTables_length select { height: 36px; min-width: 70px; font-family: 'Poppins', sans-serif !important; font-size: 16px !important; font-weight: 400 !important; }
        #form-bom > .bom-breadcrumb { max-width: 1856px; margin-bottom: 16px; }
        #form-bom > .card.card-primary { max-width: 1856px; border-radius: 10px; background: #fff; }
        #form-bom > .card.card-primary > .card-body { padding: 16px 32px; background: #fff; }
        #section-list-item { margin-top: 24px; }
        #section-list-item > #bom-content-tabs { margin: 0 0 24px; }
        #section-list-item .bom-detail-card { margin: 0; border: 0 !important; border-radius: 0 !important; box-shadow: none !important; background: transparent; overflow: visible; }
        #section-list-item .bom-detail-card > .card-header { margin: 0 0 8px; }
        #section-list-item .bom-detail-card > .card-body { padding: 0 !important; background: transparent; }
        #section-list-item #bom-content-panels { padding: 8px 20px; border: .5px solid #c9d3dd; border-radius: 16px; background: #fff; box-shadow: 0 2px 8px rgba(30, 41, 59, .12); }
        #form-bom .bom-cell-legend { margin: 12px 0 0; }
        @media (max-width: 767.98px) {
            #form-bom > .card.card-primary > .card-body { padding: 12px; }
            #section-list-item #bom-content-panels { padding: 6px 10px; }
        }
    </style>
<style>
#bomx{color-scheme:light}
#bomx{
  --bg:#f0efee;--card:#fff;--ink:#1f3b57;--txt:#2b2f36;--mute:#8a8f98;--line:#e3e6ec;--soft:#f6f7fa;
  --crumb:#2a4b66;--navy:#1f3b57;--th:#dde7f7;--accent:#2a4b66;--accent-soft:#e8eef7;
  --c-cost:#8b5cf6;--c-so:#3b82f6;--c-in:#f59e0b;--c-auto:#9ca3af;
  --ro:#f5f3fd;--autobg:#f3f4f8;--exc:#fff4d6;--warn:#dc2626;--tot:#a9c6f5;--tot2:#c9c9c9;--tot-row:#e9f0fb;
  --red:#f50f0f;--green:#1a7f3c;--shadow:0 2px 8px rgba(20,30,50,.08);
}
#bomx,#bomx *{box-sizing:border-box}
#bomx{margin:0;background:var(--bg);color:var(--txt);font:14px/1.5 Poppins,system-ui,sans-serif}
#bomx .page{padding:8px 16px 40px}
#bomx .crumb{background:var(--crumb);color:#b9c7d6;padding:14px 12px;font-size:14px}
#bomx .crumb b{color:#fff;font-weight:500}
#bomx .crumb span{margin:0 8px}
#bomx .main{background:var(--card);padding:20px 16px 30px 32px}
#bomx h1{font-size:26px;font-weight:700;color:var(--navy);margin:0;letter-spacing:.02em}
#bomx h1 sup{font-size:12px;font-weight:500;border:1.5px solid var(--navy);border-radius:50%;padding:0 5px;margin-left:6px;vertical-align:6px}
#bomx .sub{font-size:11px;font-weight:600;color:var(--navy);margin:6px 0 16px;display:flex;align-items:center;gap:8px}
#bomx .sub i{width:16px;height:16px;border-radius:50%;background:#9aa6b2;color:#fff;font-size:10px;display:grid;place-items:center;font-style:normal}
#bomx .hc{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:40px;margin-bottom:12px}
#bomx .hc div{background:var(--card);border-radius:8px;box-shadow:0 1px 6px rgba(20,30,50,.12);padding:10px 20px 14px}
#bomx .hc span{display:block;color:var(--mute);font-size:15px}
#bomx .hc b{display:block;color:var(--navy);font-size:19px;font-weight:700;letter-spacing:.06em;margin-top:6px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
#bomx .two{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:20px 40px}
#bomx .box{background:var(--card);border-radius:10px;box-shadow:0 1px 6px rgba(20,30,50,.12);padding:12px 20px 14px}
#bomx .box h3{margin:0 0 6px;padding-bottom:8px;border-bottom:1.5px solid var(--line);font-size:13.5px;font-weight:600;color:var(--txt);letter-spacing:.02em}
#bomx .f{display:grid;grid-template-columns:1fr 1fr;column-gap:24px}
#bomx .f div{padding-top:8px}
#bomx .f span{display:block;color:var(--mute);font-size:14px}
#bomx .f b{display:block;color:var(--navy);font-size:15px;font-weight:600;letter-spacing:.03em;border-bottom:1px solid var(--line);width:62%;padding:6px 0 1px}
#bomx .stack{display:flex;flex-direction:column;gap:20px}
#bomx .img{display:flex;align-items:center;justify-content:space-between;padding:20px 30px 6px}
#bomx .img button{border:0;background:var(--soft);border-radius:8px;width:26px;height:48px;color:var(--mute);cursor:pointer}
#bomx .shots{display:flex;gap:60px;align-items:center}
#bomx .dots{text-align:center;margin:10px 0}
#bomx .dots i{display:inline-block;width:6px;height:6px;border-radius:3px;background:#d6dae2;margin:0 2px}
#bomx .dots i:first-child{width:22px;background:#3b5bdb}
#bomx .thumbs{display:flex;justify-content:center;gap:50px}
#bomx .cap{text-align:center;color:var(--mute);font-size:11px;margin-top:8px}
#bomx .bo{width:100%;border-collapse:collapse;font-size:13px;margin-top:6px;font-variant-numeric:tabular-nums}
#bomx .bo th{background:var(--crumb);color:#fff;font-weight:500;font-size:11.5px;text-align:left;padding:8px 6px}
#bomx .bo th small{opacity:.7;margin-left:3px}
#bomx .bo td{padding:12px 6px;color:var(--txt)}
#bomx .bo tr:nth-child(even) td{background:var(--tot-row)}
#bomx .bo td.t{background:var(--tot)!important;font-weight:700;color:var(--navy)}
#bomx .bo tr.tr td{font-weight:700;color:var(--navy);background:var(--tot-row)}
#bomx .bo tr.tr td.t{background:var(--tot2)!important}
#bomx .bo td.n,#bomx .bo th.n{text-align:right}
#bomx .tabs{display:flex;border-bottom:1px solid var(--line);margin:34px 0 18px}
#bomx .tabs button{flex:1;border:0;background:none;font:inherit;font-size:22px;font-weight:500;color:#c3c6cc;padding:8px 0 10px;border-bottom:4px solid transparent;cursor:pointer}
#bomx .tabs button[aria-selected="true"]{color:var(--navy);border-bottom-color:var(--navy)}
#bomx .summ{border:1px solid var(--navy);border-radius:6px;padding:12px 22px;display:flex;align-items:center;justify-content:space-between;cursor:pointer;background:var(--card)}
#bomx .summ h4{margin:0;font-size:21px;font-weight:500;color:var(--navy)}
#bomx .summ i{width:26px;height:26px;border-radius:5px;background:#c9d1db;color:var(--navy);display:grid;place-items:center;font-style:normal;font-size:12px;transition:transform .15s}
#bomx .summ[aria-expanded="true"] i{transform:rotate(180deg)}
#bomx .sumbody{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:12px;padding:14px 4px 0}
#bomx .sumbody[hidden]{display:none}
#bomx .sumbody div{background:var(--soft);border-radius:8px;padding:10px 14px}
#bomx .sumbody span{display:block;font-size:11.5px;color:var(--mute)}
#bomx .sumbody b{font-size:16px;color:var(--navy)}
#bomx .bar{display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin:14px 0 8px}
#bomx .legend{font-size:11.5px;color:var(--mute);display:flex;gap:12px}
#bomx .dot{display:inline-block;width:7px;height:7px;border-radius:50%;margin-right:4px;vertical-align:1px}
#bomx .d-cost{background:var(--c-cost)}
#bomx .d-so{background:var(--c-so)}
#bomx .d-in{background:var(--c-in)}
#bomx .d-auto{background:var(--c-auto)}
#bomx .destq{font-size:11.5px;color:var(--mute)}
#bomx .destq b{color:var(--navy)}
#bomx .sp{flex:1}
#bomx .bx-btn{border:1px solid var(--line);background:var(--card);color:var(--navy);border-radius:6px;padding:6px 11px;font:inherit;font-size:12px;cursor:pointer}
#bomx .bx-btn:hover{border-color:var(--navy)}
#bomx .bx-btn.pri{background:var(--navy);border-color:var(--navy);color:var(--card)}
#bomx .tblcard{border-radius:10px;box-shadow:0 1px 6px rgba(20,30,50,.12);padding:0 0 4px;background:var(--card)}
#bomx .scroll{overflow-x:auto}
#bomx .bt{border-collapse:collapse;font-variant-numeric:tabular-nums;min-width:100%}
#bomx .bt th{background:var(--th);font-size:11px;font-weight:500;color:var(--txt);text-align:left;padding:10px 6px;white-space:nowrap;text-transform:uppercase}
#bomx .bt td{border-bottom:1px solid var(--line);padding:4px 6px;vertical-align:middle;white-space:nowrap;color:var(--txt)}
#bomx .bt td.ro{background:var(--ro)}
#bomx .bt td.num{text-align:right}
#bomx .bt td.auto{background:var(--autobg);text-align:right;font-weight:600;color:var(--navy)}
#bomx .bt tr.grp td{background:var(--soft);font-weight:600;font-size:11.5px;color:var(--mute);letter-spacing:.04em}
#bomx .bt input,#bomx .bt select{font:inherit;font-size:13.5px;color:var(--txt);background:transparent;border:1px solid transparent;border-radius:4px;padding:3px 4px;width:100%}
#bomx .bt input:not([type=checkbox]),#bomx .bt select{border-color:#d5dbe4;background:var(--card)}
#bomx .bt input:hover,#bomx .bt select:hover{border-color:var(--c-in)}
#bomx .bt input:focus,#bomx .bt select:focus{outline:none;border-color:var(--c-in);background:var(--card)}
#bomx .bt select option{background:var(--card);color:var(--txt)}
#bomx .bt input.n{text-align:right}
#bomx .chip.pc{cursor:pointer;font-family:inherit;color:var(--txt)}
#bomx .chip.pc:hover{border-color:var(--c-in);background:var(--exc)}
#bomx .chip{display:inline-block;border:1px solid var(--line);border-radius:10px;padding:0 6px;font-size:11px;margin-right:2px;background:var(--card)}
#bomx .exp{border:1px solid var(--line);background:var(--card);color:var(--navy);border-radius:4px;font-size:11px;padding:1px 6px;cursor:pointer;white-space:nowrap;font-family:inherit}
#bomx .exp.has{background:var(--exc);border-color:transparent}
#bomx .del{border:0;background:var(--red);color:#fff;width:24px;height:24px;border-radius:50%;cursor:pointer;font-size:12px}
#bomx .over{color:var(--warn)!important;font-weight:600}
#bomx tr.mx>td{background:var(--soft);padding:6px 10px 10px 300px;white-space:normal}
#bomx .mxt{border-collapse:collapse}
#bomx .mxt td,#bomx .mxt th{border:1px solid var(--line);padding:2px 4px;font-size:12px}
#bomx .mxt th{background:var(--card);color:var(--mute);font-weight:500}
#bomx .mxt input{width:64px;text-align:right;border:1px solid var(--line);background:var(--card);border-radius:3px;padding:2px 4px;font:inherit;font-size:12px;color:var(--txt)}
#bomx .mxt input.it{width:140px;text-align:left}
#bomx .mxt input.x{background:var(--exc);font-weight:600}
#bomx .bt td.cb{white-space:nowrap;font-size:12.5px}
#bomx .bt td.cb label{display:inline-flex;margin-right:6px;gap:6px;align-items:center;cursor:pointer}
#bomx .bt td.cb input{width:16px;height:16px;accent-color:var(--c-in)}
#bomx .bt tr.first td{border-top:1.5px solid #c9d1db}
#bomx .exp.up{font-size:11px}
#bomx .merge{font-size:12px;color:var(--mute);padding:8px 20px 0}
#bomx .szp{display:flex;gap:6px;align-items:center;margin-bottom:8px;font-size:12.5px;flex-wrap:wrap}
#bomx .szp span{color:var(--mute);margin-right:4px}
#bomx .sz{border:1px solid #c9d1db;background:var(--card);color:var(--txt);border-radius:6px;padding:4px 12px;font:inherit;font-size:12.5px;cursor:pointer;min-width:52px}
#bomx .sz small{display:block;font-size:10px;font-weight:400;opacity:.8}
#bomx .sz.on{background:var(--c-in);border-color:var(--c-in);color:#fff;font-weight:600}
#bomx .cs{display:inline-block;font-size:11px;border-radius:4px;padding:0 5px;margin:1px 2px;background:var(--card);border:1px solid #e6c97a;color:var(--txt)}
#bomx .cs.ok{background:#fde7b0;font-weight:600}
#bomx .bt td.cb{white-space:normal;min-width:150px}
#bomx .hint{font-size:11px;color:var(--mute);margin:6px 0 0}
#bomx .pg{display:flex;align-items:center;gap:10px;padding:12px 20px 6px;font-size:14px;color:var(--txt)}
#bomx .pg .ipp{border:1px solid var(--navy);border-radius:6px;padding:4px 12px}
#bomx .pg nav{margin-left:auto;display:flex;gap:16px;align-items:center;color:var(--mute)}
#bomx .pg nav b{background:var(--navy);color:var(--card);border-radius:5px;padding:2px 9px}
#bomx .leg2{display:flex;gap:16px;font-size:13px;color:var(--mute);margin:14px 0}
#bomx .leg2 i{display:inline-block;width:13px;height:13px;border:1px solid var(--line);border-radius:2px;vertical-align:-2px;margin-right:6px}
#bomx .leg2 .e{background:var(--card)}
#bomx .leg2 .a{background:var(--autobg)}
#bomx .leg2 .x{background:var(--exc);border-color:#e6c97a}
#bomx .leg2 .p{border-color:#3b82f6}
#bomx .leg2 .r{border-color:var(--red)}
#bomx .act{display:flex;justify-content:flex-end;gap:16px;margin-top:20px}
#bomx .pill{border:0;border-radius:24px;padding:12px 38px;font:inherit;font-size:16px;font-weight:600;color:#fff;cursor:pointer;box-shadow:0 4px 10px rgba(0,0,0,.15)}
#bomx .pill.c{background:var(--red)}
#bomx .pill.s{background:var(--green)}
#bomx dialog{border:1px solid var(--line);border-radius:10px;background:var(--card);color:var(--txt);width:min(560px,92vw);font-family:inherit}
#bomx dialog textarea{width:100%;height:110px;font:12px monospace;background:var(--soft);color:var(--txt);border:1px solid var(--line);border-radius:6px}
#bomx .bx-toast{position:fixed;bottom:18px;left:50%;transform:translateX(-50%);background:var(--crumb);color:#fff;padding:8px 14px;border-radius:6px;font-size:12px;max-width:90vw}
#bomx .bx-toast[hidden]{display:none}
@media (max-width:1100px){#bomx .nav{padding:10px 16px;flex-wrap:wrap}#bomx .search{display:none}#bomx .hc{grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}#bomx .two{grid-template-columns:1fr}#bomx .main{padding:16px}#bomx .tabs button{font-size:15px}#bomx .sumbody{grid-template-columns:repeat(2,minmax(0,1fr))}}
#bomx .sheetbar{display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin:6px 0 8px}
#bomx .tip{font-size:12px;color:var(--mute);background:var(--soft);border-radius:6px;padding:6px 10px}
#bomx .tip kbd{font:inherit;font-size:11px;background:var(--card);border:1px solid var(--line);border-bottom-width:2px;border-radius:4px;padding:0 4px;color:var(--txt)}
#bomx .fx{display:flex;align-items:center;border:1px solid #c9d1db;border-bottom:0;background:var(--card);font-size:13px}
#bomx .fx .nm{width:70px;text-align:center;border-right:1px solid #c9d1db;padding:5px;color:var(--mute);font-weight:600}
#bomx .fx .fl{padding:5px 10px;color:var(--mute);border-right:1px solid #c9d1db;font-style:italic}
#bomx .fx .fv{padding:5px 10px;flex:1;min-height:28px;color:var(--txt)}
#bomx .wrapS{overflow:auto;border:1px solid #c9d1db;max-height:560px;background:var(--card)}
#bomx .xs{border-collapse:separate;border-spacing:0;font-size:13px;font-variant-numeric:tabular-nums;user-select:none;min-width:100%}
#bomx .xs th{position:sticky;top:0;z-index:2;background:#eef1f5;color:#3d4653;font-weight:600;font-size:12px;border-right:1px solid #d3d8df;border-bottom:1px solid #b8c0cc;padding:0}
#bomx .xs th .L{display:block;font-size:10px;color:#8a93a0;font-weight:500;border-bottom:1px solid #d3d8df;padding:2px 0;text-align:center;background:#e6eaf0}
#bomx .xs th .T{display:block;padding:5px 8px;text-align:left;white-space:nowrap}
#bomx .xs td{border-right:1px solid #e1e5ea;border-bottom:1px solid #e1e5ea;padding:0 8px;height:30px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:260px;cursor:cell;color:var(--txt)}
#bomx .xs td.rn,#bomx .xs th.rn{position:sticky;left:0;z-index:1;background:#eef1f5;color:#8a93a0;text-align:center;font-size:11px;width:40px;min-width:40px;padding:0;border-right:1px solid #b8c0cc;cursor:default}
#bomx .xs th.rn{z-index:3}
#bomx .xs td.ro{background:#f7f5fd;color:#4b4f58}
#bomx .xs td.au{background:#f3f4f7;color:var(--navy);font-weight:600;text-align:right}
#bomx .xs td.n{text-align:right}
#bomx .xs td.inh{color:#a7adb7;font-style:italic}
#bomx .xs td.lst{position:relative;padding-right:18px}
#bomx .xs td.lst::after{content:"▾";position:absolute;right:6px;top:50%;transform:translateY(-50%);color:#b0b7c2;font-size:10px}
#bomx .xs tr.xsub td.ro{background:#fffaf0}
#bomx .xs tr.xsub td.c0{color:#b45309;font-weight:600}
#bomx .xs tr.xsub td.rn{color:#b45309}
#bomx .xs tr.base td.c0{font-weight:600;color:var(--navy)}
#bomx .xs tr.gs td{border-top:2px solid #c9d1db}
#bomx .xs td.sel{background:#e3edff!important}
#bomx .xs td.acell{outline:2px solid #1a73e8;outline-offset:-2px;background:var(--card)!important;position:relative}
#bomx .xs td.err{box-shadow:inset 0 0 0 1px var(--red)}
#bomx .xs td.pasted{box-shadow:inset 0 0 0 1px #3b82f6}
#bomx .xs td input{width:100%;border:0;outline:0;font:inherit;background:transparent;color:var(--txt);padding:0;height:28px}
#bomx .xs td.over{color:var(--red);font-weight:600}
#bomx .sw{display:inline-block;width:10px;height:10px;border-radius:3px;border:1px solid #9aa3ad;margin-right:5px;vertical-align:-1px}
#bomx .status{display:flex;gap:18px;justify-content:flex-end;background:#eef1f5;border:1px solid #c9d1db;border-top:0;padding:4px 12px;font-size:12px;color:#5b6472}
#bomx .status b{color:var(--navy)}
#bomx .dim{color:#a7adb7;font-style:italic}
#bomx .xs tr.rgs td{background:#fffaf0;padding:4px 10px;height:auto;border-bottom:1px solid #f0e2c4}
#bomx .rgbar{display:flex;align-items:center;gap:2px;font-size:12px;color:#8a6d3b;flex-wrap:wrap}
#bomx .rgbar>span:first-child{margin-right:8px}
#bomx .rgbar small{color:#a7adb7}
#bomx .rz{display:inline-block;background:var(--card);border:1px solid #e6c97a;border-radius:4px;padding:1px 8px;font-weight:600;color:var(--txt);text-align:center;min-width:44px}
#bomx .rz small{display:block;font-size:9.5px;font-weight:400}
#bomx .cut{border:0;background:none;width:16px;cursor:pointer;color:#c9a54a;font-size:16px;line-height:1;padding:0}
#bomx .cut.on{color:#b45309;font-weight:700}
#bomx .cut:hover{color:var(--red)}
#bomx .rgn{margin-left:12px;color:#8a6d3b}
#bomx .mode{display:inline-flex;border:1px solid var(--navy);border-radius:7px;overflow:hidden}
#bomx .mode button{border:0;background:var(--card);color:var(--navy);font:inherit;font-size:12.5px;padding:6px 12px;cursor:pointer}
#bomx .mode button[aria-pressed="true"]{background:var(--navy);color:#fff}
#bomx .pvbar{display:flex;align-items:center;gap:12px;font-size:12.5px;margin-bottom:6px;color:var(--txt)}
#bomx .pvbar label{display:flex;gap:6px;align-items:center;cursor:pointer}
#bomx .pv{border-collapse:collapse;font-size:12.5px;font-variant-numeric:tabular-nums;min-width:100%}
#bomx .pv th{position:sticky;top:0;background:var(--crumb);color:#fff;font-weight:500;font-size:11px;text-align:left;padding:7px 8px;white-space:nowrap}
#bomx .pv td{padding:5px 8px;border-bottom:1px solid #e6e9ee;white-space:nowrap;color:var(--txt)}
#bomx .pv td.n{text-align:right}
#bomx .pv tr.g td{border-top:2px solid #c9d1db}
#bomx .pv td.k{color:var(--navy);font-weight:600}
#bomx .pv tr.sec td{background:var(--th);font-weight:700;color:var(--navy);letter-spacing:.04em}
#bomx .pv tr.tot td{background:var(--tot-row);font-weight:700;color:var(--navy)}
#bomx .ctx{position:fixed;z-index:20;background:var(--card);border:1px solid var(--line);border-radius:8px;box-shadow:0 6px 20px rgba(0,0,0,.15);padding:4px;min-width:220px}
#bomx .ctx[hidden]{display:none}
#bomx .ctx button{display:block;width:100%;text-align:left;border:0;background:none;padding:7px 10px;font:inherit;font-size:13px;color:var(--txt);border-radius:5px;cursor:pointer}
#bomx .ctx button:hover{background:var(--soft)}
#bomx .ctx hr{border:0;border-top:1px solid var(--line);margin:4px 0}
#bomx .fx{display:none}
#bomx .wrapS{border:0;border-radius:10px;box-shadow:0 1px 6px rgba(20,30,50,.12);max-height:620px}
#bomx .xs{font-size:13px}
#bomx .xs th{background:var(--th);border:0;border-bottom:1px solid #c5d3ea;color:var(--txt)}
#bomx .xs th .T{padding:12px 10px;font-size:11px;font-weight:500;text-transform:uppercase;letter-spacing:.02em;display:flex;align-items:center;gap:2px}
#bomx .xs th .pen{margin-left:6px;font-size:13px;color:var(--navy)}
#bomx .xs td{height:46px;border-right:0;border-bottom:1px solid #edf0f4;padding:0 10px}
#bomx .xs td.rn,#bomx .xs th.rn{background:var(--card);border-right:0;color:var(--txt);font-size:13px;width:52px;min-width:52px}
#bomx .xs th.rn{background:var(--th)}
#bomx .xs td.ro{background:var(--card);color:var(--txt)}
#bomx .xs tr.base td.c0{font-weight:600;color:var(--txt)}
#bomx .xs td.au{background:#f4f5f8;border-left:1px solid #e3e6ec;border-right:1px solid #e3e6ec;color:var(--txt);font-weight:500}
#bomx .xs tr.xsub td{background:#fbfcfe}
#bomx .xs tr.xsub td.ro{background:#fbfcfe}
#bomx .xs tr.xsub td.au{background:#f4f5f8}
#bomx .xs tr.xsub td.c0{color:var(--navy);padding-left:22px}
#bomx .xs tr.xsub td.rn{color:#a7adb7}
#bomx .xs tr.gs td{border-top:1.5px solid #dfe5ee}
#bomx .xs td.sel{background:#eaf1fd!important}
#bomx .xs td.acell{outline:2px solid var(--navy);border-radius:2px}
#bomx .xs td.acell .select2-container{display:block}
#bomx .xs td.acell .select2-selection{height:100%!important;min-height:22px;padding:0 14px 0 4px;border:0;border-radius:0;background:transparent;box-shadow:none!important;font-size:inherit}
#bomx .xs td.acell .select2-selection__rendered{padding:0;font-size:inherit;line-height:22px}
#bomx .xs td.acell .select2-selection__arrow{height:22px;right:2px}
.bom-combo-drop{min-width:170px;font-size:12px}
.bom-combo-drop .select2-search__field{height:28px;font-size:12px}
.bom-combo-drop .select2-results__options{max-height:240px}
#bomx .xs td.lst::after{color:var(--navy);content:"⌄";font-size:13px;top:42%}
#bomx .nil{color:#9aa1ab}
#bomx .xs tr.rgs td{background:#fbfcfe}
#bomx .status{background:var(--card);border:0;justify-content:space-between;padding:12px 16px 4px;font-size:13px;color:var(--txt)}
#bomx .wrapS{max-height:none;overflow-x:auto;overflow-y:visible}
#bomx .xs{table-layout:auto;width:max-content;min-width:100%;font-size:12.5px}
#bomx .xs th.fz,#bomx .xs td.fz{position:sticky;z-index:1}
#bomx .xs th.fz{z-index:3}
#bomx .xs td.fz.last,#bomx .xs th.fz.last{box-shadow:3px 0 4px -2px rgba(20,30,50,.15)}
#bomx .ia{cursor:pointer;margin-right:8px;font-size:13px;color:var(--navy)}
#bomx .ia.del{color:var(--red)}
#bomx .xs th{position:static}
#bomx .xs th .T{padding:10px 8px;font-size:10.5px;white-space:nowrap}
#bomx .xs th .pen{margin-left:4px;font-size:12px}
#bomx .xs th .dot{flex:none;margin-top:3px}
#bomx .xs td{height:36px;padding:0 8px;max-width:260px}
#bomx .xs td.lst{padding-right:14px}
#bomx .xs td.lst::after{right:3px;font-size:11px}
#bomx .xs td.rn,#bomx .xs th.rn{width:40px;min-width:40px;font-size:11px;position:sticky;left:0;z-index:2}
#bomx .xs th.rn{z-index:4}
#bomx .xs tr.xsub td.c0{padding-left:12px}
#bomx .xs td input{height:28px;font-size:12px}
#bomx .sw{width:9px;height:9px;margin-right:3px}
#bomx .xs tr.rgs td{padding:3px 6px}
#bomx .rz{padding:0 6px;min-width:36px;font-size:11px}
#bomx .rgbar{font-size:11px}
#bomx .sheetbar{margin:4px 0 6px}
#bomx .status{padding:8px 4px 0;font-size:12px}
#bomx .pv{font-size:11.5px}
#bomx .pv td{padding:4px 6px}
#bomx .pv th{padding:6px;font-size:10px;position:static}
#bomx #pvBox .wrapS{overflow-x:auto}
#bomx .rgbar{position:sticky;left:56px;width:max-content}
#bomx .ia.del{display:inline-flex;align-items:center;justify-content:center;width:26px;height:26px;padding:0;border-radius:50%;background:var(--red);color:#fff;vertical-align:middle;box-shadow:0 3px 6px rgba(255,0,0,.3)}
#bomx .ia.del i{line-height:1!important;font-size:11px!important}
#bomx .ia[data-up]{display:inline-flex;align-items:center;justify-content:center;width:22px;height:26px;margin-right:6px;vertical-align:middle}
#bomx .xs td.rn,#bomx .xs th.rn{width:64px;min-width:64px;max-width:64px;text-align:left;padding:0 8px}
#bomx .ckT{display:flex;align-items:center;gap:8px}
#bomx .ck{width:14px;height:14px;margin:0;accent-color:var(--navy);cursor:pointer}
#bomx .xs td input.ck,#bomx .xs th input.ck{width:14px!important;height:14px!important;min-width:0;padding:0!important;border:0;box-shadow:none;flex:none}
#bomx .srt{color:#9aa1ab;font-size:11px;margin-left:2px}
#bomx .xs th.addc{background:#e3ecfb}
#bomx .xs tr.chk td{background:#eef3ff}
#bomx .xs{font-size:11px}
#bomx .xs th .T{padding:6px 6px;font-size:9.5px;gap:3px}
#bomx .xs th .pen{font-size:10px;margin-left:3px}
#bomx .xs th .dot{width:6px;height:6px}
#bomx .xs td{height:26px;padding:0 6px;font-size:11px}
#bomx .xs td input{height:22px;font-size:11px}
#bomx .xs td.rn,#bomx .xs th.rn{width:56px;min-width:56px;max-width:56px;padding:0 6px;font-size:10px}
#bomx .ckT{gap:6px}
#bomx .xs td input.ck,#bomx .xs th input.ck{width:12px!important;height:12px!important}
#bomx .xs td.lst::after{font-size:10px}
#bomx .xs tr.xsub td.c0{padding-left:10px}
#bomx .sw{width:8px;height:8px}
#bomx .xs tr.rgs td{padding:2px 6px}
#bomx .rz{font-size:10px;min-width:30px}
#bomx .rgbar{font-size:10px}
#bomx .cut{font-size:10px}
#bomx .pv{font-size:11px}
#bomx .pv th{background:var(--th);color:var(--txt);font-weight:600;font-size:9.5px;text-transform:uppercase;letter-spacing:.02em;padding:6px;border-bottom:1px solid #d6dbe4}
#bomx .pv th .dot{width:6px;height:6px;margin-right:4px}
#bomx .pv th.n{text-align:right}
#bomx .pv td{padding:0 6px;height:26px;font-size:11px}
#bomx .xs th .T{padding:6px 4px;font-size:9px;letter-spacing:0}
#bomx .xs td{padding:0 4px;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
#bomx .xs td.lst{padding-right:12px}
#bomx .xs td.rn,#bomx .xs th.rn{width:50px;min-width:50px;max-width:50px;padding:0 4px}
#bomx .ckT{gap:4px}
#bomx .srt{display:none}
#bomx .xs td{padding:0 6px;max-width:240px}
#bomx .xs td.lst{padding-right:14px}
#bomx .xs th .T{padding:6px}
#bomx .xs{width:max-content;min-width:0}
#bomx .xs th,#bomx .xs td{border-right:1px solid #dfe4ec!important;border-bottom:1px solid #dfe4ec!important;border-left:0!important}
#bomx .xs th{border-bottom:1px solid #c5d3ea!important}
#bomx .xs td.au{border-left:0!important}
#bomx .xs tr.gs td{border-top:1.5px solid #c9d1db!important}
#bomx .xs td.lst{position:relative}
#bomx .xs td.lst::after{position:absolute;right:4px;top:50%;transform:translateY(-50%);line-height:1}
#bomx .xs td.fz.last,#bomx .xs th.fz.last{border-right:1px solid #c5ccd8!important}
#bomx .wrapS{border:1px solid #dfe4ec;border-radius:8px;width:max-content;max-width:100%}
#bomx .xs{table-layout:fixed;width:100%!important}
#bomx .wrapS{width:auto;max-width:none}
#bomx .xs td,#bomx .xs th{max-width:none!important;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
#bomx .xs th .T{overflow:hidden;text-overflow:ellipsis}
#bomx label{font-weight:400;margin:0}
#bomx .page{padding:0 0 40px}
#bomx .box.rv{display:flex;flex-direction:column}
#bomx .box.rv .img{flex:1;min-height:260px;position:relative;padding:8px 0 0}
#bomx .box.rv .shots{position:absolute;inset:8px 52px 0;display:flex;justify-content:center;align-items:center}
#bomx .box.rv .shots img{max-height:100%;max-width:100%;object-fit:contain;border-radius:6px}
#bomx .thumbs{flex-wrap:wrap;gap:16px}
#bomx .thumbs img{height:80px;max-width:80px;object-fit:contain;cursor:pointer;opacity:.6;border-radius:4px}
#bomx .thumbs img.on{opacity:1}
#bomx .xs td.fhc:not(.fz){position:relative}
#bomx .xs td.wrapc{white-space:normal;overflow-wrap:anywhere;text-overflow:clip;line-height:1.3;padding-top:4px;padding-bottom:4px}
#bomx .xs .fh{position:absolute;right:0;bottom:0;width:7px;height:7px;background:var(--navy);border:1px solid #fff;cursor:crosshair;z-index:2}
#bomx .xs td.fillt{outline:1px dashed var(--navy);outline-offset:-2px;background:#f3f7ff!important}
#bomx .dots i.on{width:22px;background:#3b5bdb}
#bomx .dots i:first-child:not(.on){width:6px;background:#d6dae2}
#bomx{background:transparent;font-family:Inter,Poppins,sans-serif}
#section-list-item #bomx{padding:8px 20px;border:.5px solid #c9d3dd;border-radius:16px;background:#fff;box-shadow:0 2px 8px rgba(30,41,59,.12)}
#bomx .sheetbar .mkt-btn{font-family:Poppins,sans-serif}
.bom-ws-picker{display:flex;flex-wrap:wrap;align-items:center;gap:8px;margin:-4px 0 16px;color:#284a63;font-size:14px;font-weight:600}
.bom-ws-picker .mkt-btn{text-decoration:none;position:relative}
.bom-ws-picker.is-loading{opacity:.6;pointer-events:none}
.bom-ws-dot{position:absolute;top:-3px;right:-3px;width:11px;height:11px;border-radius:50%;background:#f59e0b;border:2px solid #fff}
.bom-stale{margin:0 0 16px;padding:12px 16px;border:1px solid #f0c36d;border-radius:10px;background:#fff8e6;color:#7a5300;font-size:14px}
.bom-stale .t{font-weight:600}
.bom-stale ul{margin:6px 0 0;padding-left:22px}
.bom-stale .i{margin-top:6px;color:#6b5a2e}
.bom-stale .i:first-child{margin-top:0}
#bomx .xs tr.chg td{box-shadow:inset 0 0 0 9999px rgba(245,158,11,.12)}
#bom-content-tabs .nav-link{text-decoration:none}
#bom-content-tabs{overflow-y:hidden}
#form-bom #bom-content-tabs .nav-link{font-size:clamp(15px,1.3vw,25px)!important;padding-left:6px;padding-right:6px;overflow:hidden;text-overflow:ellipsis}
#bomx #wrap:focus,#bomx #wrap:focus-visible{outline:none}
#form-bom > .bom-breadcrumb, #form-bom > .card.card-primary{max-width:none}
#bomx .xs td input.ck,#bomx .xs th input.ck{border:1.5px solid #cfd4dc!important;border-radius:3px}
#bomx .xs input.ck:checked{border-color:#284a63!important}
#bomx .notready{border:1px solid #f0c36d;background:#fff8e6;color:#7a5300;border-radius:10px;padding:16px 20px;font-size:15px}
#form-bom #bomx table.xs,#form-bom #bomx table.xs thead th,#form-bom #bomx table.xs tbody td,#form-bom #bomx table.xs td *,#form-bom #bomx table.xs th *,#form-bom #bomx table.pv,#form-bom #bomx table.pv thead th,#form-bom #bomx table.pv tbody td,#form-bom #bomx table.pv td *{font-size:10px!important}
/* Gaya tabel disamakan dengan tabel Costing (warna, garis, sorotan sel); ukuran baris, kolom & font tetap seperti di atas. */
#bomx{--c-cost:#7c3aed;--c-so:#2563eb;--c-in:#f97316;--c-auto:#94a3b8}
#bomx .wrapS{padding:10px 12px 8px;border:1px solid #edf0f5!important;border-radius:8px;background:#fff;box-shadow:0 3px 12px rgba(30,41,59,.09)}
#bomx .xs,#bomx .pv{font-family:Inter,sans-serif;background:#fff}
#bomx .xs th,#bomx .pv th{background:#cfe0ff!important;color:#1e293b!important;font-weight:500!important;text-transform:uppercase;border:0!important}
#bomx .xs thead th:first-child,#bomx .pv tr:first-child th:first-child{border-radius:6px 0 0 0}
#bomx .xs thead th:last-child,#bomx .pv tr:first-child th:last-child{border-radius:0 6px 0 0}
#bomx .xs th.addc{background:#bcd3fb!important}
#bomx .xs th .pen{color:#1e293b}
#bomx .xs td,#bomx .pv td{border:0!important;border-bottom:1px solid #edf1f5!important;color:#1e293b;font-weight:500}
#bomx .xs td.rn,#bomx .xs td.ro,#bomx .xs td.fz{background:#fff}
#bomx .xs tr.base td.c0{font-weight:600}
#bomx .xs td.au,#bomx .xs tr.xsub td.au{border-left:2px solid #fff!important;border-right:2px solid #fff!important;background:#f1f4f8;color:#475569}
#bomx .xs tr.xsub td,#bomx .xs tr.xsub td.ro,#bomx .xs tr.xsub td.fz,#bomx .xs tr.rgs td{background:#fbfcfe}
#bomx .xs tr.gs td{border-top:1px solid #dfe5ee!important}
#bomx .xs tbody tr:hover > td:not(.au):not(.sel):not(.acell){background:#fafcff}
#bomx .xs td.sel{background:#e8f2fb!important}
#bomx .xs td.acell{outline:2px solid #1a73e8;outline-offset:-2px;border-radius:0}
#bomx .xs td.lst::after{color:#1e293b}
#bomx .xs td.edt{position:relative;padding-right:18px}
#bomx .xs td.edt::after{content:"\f303";font-family:"Font Awesome 5 Free";font-weight:900;position:absolute;right:5px;top:50%;transform:translateY(-50%);font-size:9px;line-height:1;color:#b6bfcc}
#bomx .xs td.edt:hover::after,#bomx .xs td.edt.acell::after{color:#284a63}
#bomx .xs td.lst:hover::after{color:#284a63}
#bomx .xs .fh{background:#1a73e8}
#bomx .xs td.fillt{outline-color:#e0a800;background:#fff3c4!important}
#bomx .xs td.pasted{box-shadow:none;background:#dbeafe!important}
#bomx .xs td.err{background:#ffe4e6!important;box-shadow:inset 0 0 0 2px #dc3545}
#bomx .xs td.fz.last,#bomx .xs th.fz.last{border-right:0!important}
#bomx .xs tr.chk td:not(.sel):not(.acell){background:#eef3ff!important}
/* Centang baris: aturan 'td input{background:transparent}' menimpa warna centang, jadi dipaksa sama dengan header. */#bomx .xs td input.ck:checked{background-color:#284a63!important;border-color:#284a63!important;background-image:url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12'%3e%3cpath fill='none' stroke='%23fff' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' d='M2.5 6.2l2.3 2.3 4.7-4.9'/%3e%3c/svg%3e")!important;background-repeat:no-repeat!important;background-position:center!important;background-size:10px 10px!important}#bomx .xs tr.chk td:not(.sel):not(.acell){background:#e3ecfb!important}#bomx .xs tr.chk td.rn{box-shadow:inset 3px 0 0 #284a63}
/* Lebar kolom ikut isi terpanjang; teks panjang turun baris, tidak ada yang terpotong "..." (kolom beku tetap selebar aturannya). */
#form-bom #bomx table.xs{table-layout:auto!important;width:max-content!important;min-width:100%!important}
#form-bom #bomx .xs td{white-space:normal!important;overflow:visible!important;text-overflow:clip!important;overflow-wrap:break-word;word-break:normal;line-height:1.35;padding-top:4px;padding-bottom:4px}
#form-bom #bomx .xs td:not(.fz){max-width:320px!important}
#form-bom #bomx .xs th{white-space:nowrap!important;overflow:visible!important}
#form-bom #bomx .xs th .T{overflow:visible!important;text-overflow:clip!important}
/* Font sama dengan tabel Costing: Inter, header 12px, isi 14px (menimpa aturan 10px di atas). */
#form-bom #bomx table.xs,#form-bom #bomx table.xs thead th,#form-bom #bomx table.xs th *,#form-bom #bomx table.xs tbody td,#form-bom #bomx table.xs td *,
#form-bom #bomx table.pv,#form-bom #bomx table.pv thead th,#form-bom #bomx table.pv th *,#form-bom #bomx table.pv tbody td,#form-bom #bomx table.pv td *{font-family:Inter,sans-serif!important}
#form-bom #bomx table.xs thead th,#form-bom #bomx table.xs th *,#form-bom #bomx table.pv thead th,#form-bom #bomx table.pv th *{font-size:12px!important}
#form-bom #bomx table.xs tbody td,#form-bom #bomx table.xs td *,#form-bom #bomx table.pv tbody td,#form-bom #bomx table.pv td *{font-size:14px!important}
#form-bom #bomx table.xs i.fas,#form-bom #bomx table.xs i.far,#form-bom #bomx table.pv i.fas,#form-bom #bomx table.pv i.far{font-family:"Font Awesome 5 Free"!important;font-size:11px!important}
#form-bom #bomx table.xs i.fas,#form-bom #bomx table.pv i.fas{font-weight:900!important}
#form-bom #bomx table.xs th .pen{font-size:10px!important}
#form-bom #bomx table.xs .ia,#form-bom #bomx table.xs .ia *{font-size:11px!important}
#form-bom #bomx .xs td{height:34px}
#form-bom #bomx .xs td input{height:30px}
</style>
@endsection

@section('content')
@php($isEdit = $bomPage && $bomPage['header']['bom_no'])
<div id="form-bom">
    <div class="bom-breadcrumb"><a href="{{ route('home') }}">Home</a> &nbsp;›&nbsp; <a href="{{ route('dashboard-marketing') }}">Marketing</a> &nbsp;›&nbsp; <a href="{{ route('master-costing') }}">Process</a> &nbsp;›&nbsp; Bill of Material &nbsp;›&nbsp; <strong data-bom-title data-edit="Edit BOM" data-create="Create BOM">{{ $isEdit ? 'Edit BOM' : 'Create BOM' }}</strong></div>
    <div class="card card-primary">
        <div class="card-body">
            <div class="bom-page-heading">
                <h1><span data-bom-title data-edit="Edit Bill of Material" data-create="Create Bill of Material">{{ $isEdit ? 'Edit Bill of Material' : 'Create Bill of Material' }}</span> <i class="far fa-question-circle"></i></h1>
                <p><i class="fas fa-cog"></i>{{ $isEdit ? 'Untuk Mengubah Data BOM' : 'Untuk Menambah Data BOM' }}</p>
            </div>

            @if ($lines->count() > 1)
                <div class="bom-ws-picker">Worksheet:
                    @foreach ($lines as $ws)
                        <a href="{{ route('bom-nds-edit', $soId) }}?line={{ $ws->id }}" data-line="{{ $ws->id }}" class="mkt-btn mkt-btn--sm {{ $ws->id == $line->id ? 'mkt-btn--navy' : 'mkt-btn--gray' }}">{{ $ws->no_ws }} ({{ strtoupper(trim((string) $ws->product_set)) }})@if (!empty($staleWs[$ws->id]))<span class="bom-ws-dot" title="BOM WS ini perlu diperbarui"></span>@endif</a>
                    @endforeach
                </div>
            @endif

            @if (!$schemaReady)
                <div id="bomx"><div class="notready">Tabel BOM di database belum siap. Minta IT menjalankan query <b>Input BOM</b> di <code>database/sql/nds_erd_penyesuaian.sql</code>, lalu buka halaman ini lagi.</div></div>
            @else
            {{-- Status approval BOM WS ini (terkunci / menunggu unlock / unlocked / revisi menunggu approval), diisi renderLock(). --}}
            <div id="bom-lock-banner"></div>
            <div class="bom-summary-cards" aria-label="Ringkasan BOM">
                <div class="bom-summary-card"><span>No Costing</span><strong id="h-costing">-</strong></div>
                <div class="bom-summary-card"><span>No Worksheet</span><strong id="h-ws">-</strong></div>
                <div class="bom-summary-card"><span>No Sales Order</span><strong id="h-so">-</strong></div>
                <div class="bom-summary-card"><span>No BOM</span><strong id="h-bom">-</strong></div>
            </div>
            <div class="bom-top-layout">
                <div class="bom-info-column">
                    <section class="bom-overview-section">
                        <h2 class="bom-overview-title">Information Buyer</h2>
                        <div class="bom-overview-grid" id="f-buyer"></div>
                    </section>
                    <section class="bom-overview-section">
                        <h2 class="bom-overview-title">Product &amp; Type</h2>
                        <div class="bom-overview-grid" id="f-product"></div>
                    </section>
                </div>
                <div class="bom-info-column">
                    <section class="bom-overview-section bom-review-section">
                        <h2 class="bom-overview-title">Review Image</h2>
                        <div class="bom-image-review">
                            <button type="button" class="bom-image-nav" id="bom-image-prev" aria-label="Previous image" disabled><i class="fas fa-chevron-left"></i></button>
                            <div class="bom-image-preview mkt-cf-stage" id="bom-image-stage">
                                <span id="bom-image-empty">Gambar Costing tidak tersedia.</span>
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
                                <tbody></tbody>
                                <tfoot></tfoot>
                            </table>
                        </div>
                    </section>
                </div>
            </div>

            <div class="bom-stale" id="bom-stale" role="alert" hidden></div>
            <section id="section-list-item">
                <ul class="nav nav-tabs" id="bom-content-tabs" role="tablist"></ul>
                <div class="card bom-detail-card">
                    <div class="card-header bg-white d-flex align-items-center justify-content-between">
                        <h5 class="card-title mb-0">Summary Detail</h5>
                    </div>
                    <div class="card-body">
                        <div id="bomx">
                            <div class="sheetbar">
                                <div class="legend"><span><span class="dot d-cost"></span>Costing</span><span><span class="dot d-so"></span>SO</span><span><span class="dot d-in"></span>Input</span><span><span class="dot d-auto"></span>Auto</span></div>
                                <span class="sp"></span>
                                <div class="mode" role="tablist"><button type="button" data-mode="in" aria-pressed="true">&#9998; Input</button><button type="button" data-mode="pv" aria-pressed="false">&#128065; Preview per color &times; size</button></div>
                                <button type="button" class="mkt-btn mkt-btn--red mkt-btn--sm" id="bClear"><i class="fas fa-trash-alt"></i> Hapus</button>
                            </div>
                            <div id="pvBox" hidden style="margin-top:8px"><div class="pvbar"><label><input type="checkbox" id="pvAll"> Pecah semua item per color &times; size</label><span class="sp"></span><span id="pvInfo"></span><button type="button" class="mkt-btn mkt-btn--green mkt-btn--sm" id="pvXls"><i class="fas fa-file-excel"></i> Export Excel</button></div>
                                <div class="wrapS"><table class="pv" id="pv"></table></div></div>
                            <div id="inBox" style="margin-top:8px">
                                <div class="wrapS" id="wrap" tabindex="0"><table class="xs" id="xs"></table></div>
                                <div class="status" id="status"></div>
                            </div>
                            <div class="ctx" id="ctx" hidden>
                                <button type="button" data-a="up">&#11014; Sama dengan baris atas</button>
                                <button type="button" data-a="fill">&#11015; Isi ke bawah (Ctrl+D)</button>
                                <hr><button type="button" data-a="clr">Hapus isi sel (Del)</button>
                            </div>
                            <div class="bx-toast" id="toast" hidden></div>
                        </div>
                    </div>
                </div>
            </section>

            <div class="bom-cell-legend" aria-label="Keterangan tabel">
                <span><i class="is-edit"></i>Editable</span>
                <span><i class="is-auto"></i>Auto calculated</span>
                <span><i class="is-pasted"></i>Just Pasted</span>
                <span><i class="is-error"></i>Error</span>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4 mb-2">
                <span id="dirty" class="align-self-center small text-warning mr-2" hidden><i class="fas fa-exclamation-circle"></i> Ada perubahan yang belum disimpan</span>
                <button type="button" class="mkt-btn mkt-btn--red mkt-btn--cancel" id="bCancel">Cancel</button>
                <button type="button" class="mkt-btn mkt-btn--green ml-2" id="bSave">Save</button>
            </div>
            @endif
        </div>
    </div>
</div>
<style>
    #bom-lock-banner:not(:empty) { margin-bottom: 14px; }
    .bom-lock-alert { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 10px 14px; border: 1px solid #fecaca; border-radius: 8px; background: #fef2f2; color: #991b1b; font-size: 13px; }
    .bom-lock-alert.is-wait { border-color: #fde68a; background: #fffbeb; color: #92400e; }
    .bom-lock-alert.is-open { border-color: #bfdbfe; background: #eff6ff; color: #1e40af; }
    .bom-lock-alert i { margin-right: 6px; }
    /* BOM terkunci: sel tidak bisa diklik / diedit (scroll tabel tetap jalan). */
    #form-bom.bom-locked #xs td, #form-bom.bom-locked #bom-breakdown-table td { pointer-events: none; }
    #form-bom.bom-locked #ctx { display: none !important; }
</style>
@include('marketing.costing.unlock_request_modal')
@endsection
@section('custom-script')
@if ($schemaReady)
<script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
<script>
(() => {
    let SAVE_URL = @json(route('bom-nds-save', $line->id));
    const IMAGES = @json($costingImages);
    let PAGE = @json($bomPage);

    // ===== data master & SO
    let SIZES, COLORS, DESTS, QTY, ITEMS, UNITS, SUPS, ALL_DESTS;
    const byId = list => Object.fromEntries(list.map(x => [x.id, x]));
    let ITEM_BY, UNIT_BY, SUP_BY, DEST_BY;
    function loadMasters() {
        ({ sizes: SIZES, colors: COLORS, dests: DESTS, qty: QTY } = PAGE.breakdown);
        ({ items: ITEMS, units: UNITS, suppliers: SUPS } = PAGE.masters);
        ITEM_BY = byId(ITEMS);
        UNIT_BY = byId(UNITS);
        SUP_BY = byId(SUPS);
        // Pilihan kolom Destinasi = destinasi SO saja, labelnya "NEGARA - DESTINASI" dari master.
        const DEST_LABEL = byId(PAGE.masters.dests || []);
        ALL_DESTS = DESTS.map(d => ({ id: d.id, name: (DEST_LABEL[d.id] || d).name }));
        DEST_BY = byId(ALL_DESTS);
    }
    const q = (d, c, i) => ((QTY[d] || {})[c] || [])[i] || 0;
    const TABS = [
        ['FAB', 'Fabric'],
        ['SEW', 'Accessories Sewing'],
        ['PACK', 'Accessories Packing'],
        ['MFG', 'Manufacturing'],
        ['OTH', 'Other Cost'],
        ['DOCS', 'View All Docs'],
    ];

    let uid = 0,
        rows = [];
    // 4 rule lama = semua destinasi; 4 rule "... Per Destination" memecah sub-baris per destinasi SO juga (sama dengan MarketingNdsBomService::RULES).
    const RULES = ['All Color All Size', 'All Color Range Size', 'Per Color All Size', 'Per Color Range Size',
        'All Color All Size Per Destination', 'All Color Range Size Per Destination', 'Per Color All Size Per Destination', 'Per Color Range Size Per Destination'];
    const perC = b => /^Per Color/.test(b.rule || ''),
        perR = b => /Range Size/.test(b.rule || ''),
        perD = b => /Per Destination$/.test(b.rule || '');
    const SUB_KEYS = PAGE.detailFull ? ['item', 'dest', 'cons', 'unit', 'loss', 'sup', 'price'] : ['item', 'cons'];
    const baseOf = r => (r.parent ? rows.find(x => x.uid === r.parent) : r);
    const eff = (r, k) => {
        const v = r[k];
        return r.parent && !SUB_KEYS.includes(k) && (v === null || v === '' || v === undefined) ? baseOf(r)[k] : v;
    };
    const sizeIds = () => SIZES.map(z => z.id);
    const sizeName = id => (SIZES.find(z => z.id === id) || {}).name || '?';
    const rangeTxt = a => (a.length === 1 ? sizeName(a[0]) : sizeName(a[0]) + '–' + sizeName(a[a.length - 1]));
    const colorName = id => (COLORS.find(c => c.id === id) || {}).name || '?';
    // Kolom Rule BOM hanya menampilkan 4 rule color/size; "per destinasi" dipilih di kolom Destinasi.
    // Yang disimpan tetap 8 rule (rule + " Per Destination"), jadi DB tidak berubah.
    const RULE_BASE = RULES.slice(0, 4),
        PD_SUFFIX = ' Per Destination',
        PER_DEST = 'Per destinasi',
        ruleBase = r => (r.rule || RULES[0]).replace(/ Per Destination$/, '');
    const sigOf = b => b.rule + '|' + (b.dest ?? '') + '|' + (perR(b) && b.ranges ? b.ranges.map(a => a.join(',')).join(';') : '');
    function loadRows() {
        uid = 0;
        rows = [];
        [...PAGE.rows, ...PAGE.charges].forEach(({ id, subs, ...rest }) => {
            const b = {
                uid: ++uid,
                dbid: id,
                parent: null,
                dest: null,
                loss: null,
                rule: RULES[0],
                ranges: null,
                ...rest,
            };
            rows.push(b);
            if (isOth(b)) return;
            const ids = sizeIds();
            const kids = (subs || []).map(s => {
                const i0 = ids.indexOf(s.sfrom),
                    i1 = ids.indexOf(s.sto);
                return {
                    uid: ++uid,
                    parent: b.uid,
                    tab: b.tab,
                    color: s.color,
                    sizes: s.sfrom && i0 >= 0 && i1 >= i0 ? ids.slice(i0, i1 + 1) : null,
                    ...Object.fromEntries(SUB_KEYS.map(k => [k, s[k] ?? null])),
                };
            });
            if (perR(b)) {
                const seen = {};
                b.ranges = kids
                    .filter(k => k.sizes)
                    .map(k => k.sizes)
                    .filter(a => !seen[a.join()] && (seen[a.join()] = 1));
            }
            rows.push(...kids);
            genSubs(b);
        });
    }
    // Sub-baris dibuat otomatis dari Rule BOM + Breakdown Order. Isian sub lama dipertahankan kalau color/size-nya masih ada.
    function genSubs(b) {
        if (b.parent || isOth(b)) return;
        if (perR(b)) {
            const ids = sizeIds();
            if (!b.ranges || !b.ranges.length) b.ranges = ids.map(z => [z]);
            b.ranges = b.ranges.map(a => a.filter(z => ids.includes(z))).filter(a => a.length);
            const got = b.ranges.flat();
            ids.filter(z => !got.includes(z)).forEach(z => b.ranges.push([z]));
            b.ranges.sort((x, y) => ids.indexOf(x[0]) - ids.indexOf(y[0]));
        }
        b.sig = sigOf(b);
        const want = [];
        const byD = perD(b);
        (byD ? DESTS.map(d => d.id) : [null]).forEach(d =>
            (perC(b) ? COLORS.map(c => c.id) : [null]).forEach(c =>
                (perR(b) ? b.ranges : [null]).forEach(rg => want.push({ color: c, sizes: rg, dest: d })),
            ),
        );
        // Hanya kombinasi yang benar-benar ada qty-nya di SO (mis. CANADA tanpa XXL tidak dibuatkan baris).
        const has = w =>
            (w.dest ? [w.dest] : b.dest ? [b.dest] : DESTS.map(d => d.id)).some(d =>
                (w.color ? [w.color] : COLORS.map(c => c.id)).some(c =>
                    SIZES.some((z, i) => (!w.sizes || w.sizes.includes(z.id)) && q(d, c, i) > 0),
                ),
            );
        for (let i = want.length - 1; i >= 0; i--) if (!has(want[i])) want.splice(i, 1);
        const old = rows.filter(x => x.parent === b.uid),
            used = new Set(),
            sameD = (x, w) => !byD || x.dest === w.dest,
            key = x => (x.color ?? '') + '|' + (x.sizes ? x.sizes.join(',') : '') + (byD ? '|' + (x.dest ?? '') : '');
        const take = o => {
            if (o) used.add(o);
            return o;
        };
        const keep = want.map(w => {
            const o =
                take(old.find(x => !used.has(x) && key(x) === key(w))) ||
                take(
                    old.find(
                        x =>
                            !used.has(x) &&
                            sameD(x, w) &&
                            x.color === w.color &&
                            x.sizes &&
                            w.sizes &&
                            x.sizes.some(z => w.sizes.includes(z)),
                    ),
                );
            if (o) return Object.assign(o, { sizes: w.sizes }, byD ? { dest: w.dest } : {});
            // Sub baru mewarisi isian sub lama yang mencakup color/size-nya (mis. Per Color -> Per Color Range Size).
            const p =
                old.find(
                    x =>
                        (!x.color || x.color === w.color) &&
                        (!byD || !x.dest || x.dest === w.dest) &&
                        (!x.sizes || !w.sizes || x.sizes.some(z => w.sizes.includes(z))),
                ) || {};
            return {
                uid: ++uid,
                parent: b.uid,
                tab: b.tab,
                color: w.color,
                sizes: w.sizes,
                fresh: true,
                ...Object.fromEntries(SUB_KEYS.map(k => [k, p[k] ?? b[k] ?? null])),
                ...(byD ? { dest: w.dest } : {}),
            };
        });
        rows = rows.filter(x => x.parent !== b.uid);
        if (b.rule !== RULES[0]) rows.splice(rows.indexOf(b) + 1, 0, ...keep);
    }

    // ===== kolom
    const ITEMLIST = r => {
        const own = ITEMS.filter(x => x.content === baseOf(r).contentId);
        return (own.length ? own : ITEMS).map(x => x.name);
    };
    const UNITLIST = () => UNITS.map(x => x.name),
        SUPLIST = () => SUPS.map(x => x.name),
        // Baris induk bisa pilih "Per destinasi" (rincian dipecah per destinasi SO) kalau SO punya lebih dari 1 destinasi.
        DESTLIST = r => ['Semua', ...(canPerDest(r) ? [PER_DEST] : []), ...ALL_DESTS.map(x => x.name)];
    const canPerDest = r => !r.parent && !CHG(r.tab) && DESTS.length > 1 && PAGE.ruleReady && PAGE.detailReady;

    const NO_ERD = 'belum ada tempatnya di ERD BOM, jadi belum bisa diubah';
    const NO_DETAIL = 'Color & size otomatis dari Rule BOM + Breakdown Order.';
    const COLS_M = [
        { k: 'content', t: 'Content', src: 'cost', ro: 1, w: 170, fz: 1, wrap: 1 },
        { k: 'shell', t: 'Shell', src: 'cost', ro: 1, w: 120, fz: 1, wrap: 1 },
        {
            k: 'rule',
            t: 'Rule BOM',
            src: 'in',
            // Dropdown hanya 4 rule color/size; per destinasi dipilih di kolom Destinasi.
            list: () => RULE_BASE,
            fixed: PAGE.ruleReady && PAGE.detailReady ? null : 'Rule BOM ' + NO_ERD,
            w: 134,
            add: 1,
        },
        { k: 'color', t: 'Color', src: 'so', gen: 1, w: 112, add: 1 },
        { k: 'size', t: 'Size', src: 'so', gen: 1, w: 84, add: 1 },
        { k: 'item', t: 'Item', src: 'in', list: ITEMLIST, w: 200, wrap: 1 },
        { k: 'dest', t: 'Destinasi', src: 'in', list: DESTLIST, w: 160, add: 1, wrap: 1 },
        { k: 'oq', t: 'Orig qty', src: 'so', auto: 1, w: 76 },
        // Cons costing = batas atas Cons/pc BOM (lihat capCons).
        { k: 'consC', t: 'Cons costing', src: 'cost', ro: 1, num: 1, w: 92 },
        { k: 'cons', t: 'Cons/pc', src: 'in', num: 1, w: 76 },
        { k: 'unit', t: 'Unit', src: 'in', list: UNITLIST, w: 62 },
        { k: 'qr', t: 'Qty req', src: 'auto', auto: 1, w: 76 },
        { k: 'loss', t: 'Loss %', src: 'in', num: 1, w: 66 },
        { k: 'lv', t: 'Value', src: 'auto', auto: 1, w: 66 },
        { k: 'tq', t: 'Total qty req', src: 'auto', auto: 1, w: 100 },
        {
            k: 'punit',
            t: 'Unit',
            src: 'in',
            list: 1,
            fixed: 'Unit ini mengikuti Unit di kiri (unit beli ' + NO_ERD + ')',
            w: 62,
        },
        { k: 'sup', t: 'Supplier', src: 'in', list: SUPLIST, w: 132, wrap: 1 },
        { k: 'price', t: 'Price', src: 'in', num: 1, w: 70 },
        { k: 'pc', t: 'Price costing', src: 'cost', ro: 1, num: 1, w: 108, pen: 1 },
        { k: 'act', t: 'Action', src: 'none', ro: 1, w: 80 },
    ];
    const COLS_O = [
        { k: 'content', t: 'Charge', src: 'cost', ro: 1, w: 150, wrap: 1 },
        { k: 'item', t: 'Keterangan', src: 'in', list: ITEMLIST, w: 200, wrap: 1 },
        { k: 'dest', t: 'Destinasi', src: 'in', list: 1, fixed: 'Destinasi Other Cost ' + NO_ERD, w: 86 },
        { k: 'cons', t: 'Qty/pc', src: 'in', num: 1, w: 76 },
        { k: 'unit', t: 'Unit', src: 'in', list: UNITLIST, w: 62 },
        { k: 'sup', t: 'Vendor', src: 'in', list: SUPLIST, w: 132, wrap: 1 },
        { k: 'price', t: 'Price', src: 'in', num: 1, w: 70 },
        { k: 'pc', t: 'Price costing', src: 'cost', ro: 1, num: 1, w: 108 },
        { k: 'tq', t: 'Total qty', src: 'auto', auto: 1, w: 100 },
        { k: 'val', t: 'Value', src: 'auto', auto: 1, w: 90 },
    ];
    const COPY_KEYS = {
        MAT: ['item', 'dest', 'cons', 'unit', 'loss', 'sup', 'price'],
        OTH: ['item', 'cons', 'unit', 'sup', 'price'],
    };
    const keysOf = r => (r.parent ? SUB_KEYS : isOth(r) ? COPY_KEYS.OTH : COPY_KEYS.MAT);
    const byName = list => v => list().find(x => x.name.toLowerCase() === v.toLowerCase());
    const NAMED = { unit: byName(() => UNITS), sup: byName(() => SUPS), dest: byName(() => ALL_DESTS) };

    const chk = new Set();
    let tab = 'FAB',
        act = { r: 0, c: 0 },
        anc = { r: 0, c: 0 },
        editing = null,
        pasted = new Set(),
        dirty = false,
        mode = 'in';
    const $ = s => document.querySelector(s);
    const esc = s =>
        String(s ?? '').replace(/[&<>"]/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' })[c]);
    const fmt = (n, d = 2) => Number(n).toLocaleString('id-ID', { minimumFractionDigits: d, maximumFractionDigits: d });
    const toast = m => {
        const t = $('#toast');
        t.textContent = m;
        t.hidden = false;
        clearTimeout(toast.t);
        toast.t = setTimeout(() => (t.hidden = true), 2600);
    };
    // Tab charge (bom_charges): Manufacturing dan Other Cost, kolomnya sama.
    const CHG = t => t === 'MFG' || t === 'OTH';
    const cols = () => (CHG(tab) ? COLS_O : COLS_M);
    const vis = () => rows.filter(r => r.tab === tab);
    // Cons/pc BOM (baris induk & rincian color/size) tidak boleh melebihi Cons costing: yang lebih besar dipaksa 0.
    // Dipanggil dari markDirty, jadi berlaku untuk ketik, paste, fill, salin baris atas, dll.
    function capCons() {
        const over = [];
        rows.forEach(r => {
            if (isOth(r)) return;
            const limit = baseOf(r).consC;
            if (limit === null || limit === undefined || r.cons === null || r.cons === undefined) return;
            if (Number(r.cons) > Number(limit) + 1e-9) {
                over.push(`${baseOf(r).content} (${String(r.cons).replace('.', ',')} > ${String(limit).replace('.', ',')})`);
                r.cons = 0;
            }
        });
        if (!over.length) return;
        const list = [...new Set(over)];
        const msg = `Cons BOM tidak boleh lebih besar dari Cons costing, jadi dikembalikan ke 0: ${list.slice(0, 3).join('; ')}${list.length > 3 ? ` dan ${list.length - 3} lainnya` : ''}.`;
        if (window.mktToast) window.mktToast('err', 'Cons melebihi Costing', msg, { duration: 5000 });
        else toast(msg);
    }
    // Price BOM (material & charges, induk & rincian) tidak boleh melebihi Price costing: yang lebih besar disamakan dengan Price costing.
    function capPrice() {
        const over = [];
        const fmt2 = x => Number(x).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        rows.forEach(r => {
            const limit = baseOf(r).pc;
            if (limit === null || limit === undefined || r.price === null || r.price === undefined || r.price === '') return;
            if (Number(r.price) > Number(limit) + 1e-9) {
                over.push(`${baseOf(r).content} (${fmt2(r.price)} > ${fmt2(limit)})`);
                r.price = Number(limit);
            }
        });
        if (!over.length) return;
        const list = [...new Set(over)];
        const msg = `Price BOM tidak boleh lebih besar dari Price costing, jadi disamakan dengan Price costing: ${list.slice(0, 3).join('; ')}${list.length > 3 ? ` dan ${list.length - 3} lainnya` : ''}.`;
        if (window.mktToast) window.mktToast('err', 'Price melebihi Costing', msg, { duration: 5000 });
        else toast(msg);
    }
    const markDirty = () => {
        capCons();
        capPrice();
        dirty = true;
        $('#dirty').hidden = false;
    };
    const isOth = r => CHG(r.tab);
    const unitName = id => (UNIT_BY[id] || {}).name || '';

    // Nama yang tampil di sel untuk kolom yang menyimpan id master.
    function label(k, v) {
        if (v === null || v === '' || v === undefined) return '';
        if (k === 'item') return (ITEM_BY[v] || {}).name || '';
        if (k === 'unit') return unitName(v);
        if (k === 'sup') return (SUP_BY[v] || {}).name || '';
        if (k === 'dest') return (DEST_BY[v] || {}).name || '';
        return v;
    }

    // ===== hitung: qty SO (destinasi baris itu, atau semua) per color x size masuk ke sub-baris yang cocok (atau induk), x cons
    let ORIG = {},
        NEED = {};
    // Sub-baris yang menampung qty color/size itu (sub terakhir yang cocok), atau induknya sendiri.
    const bestOf = (b, kids, colorId, sizeId, destId) => {
        let best = b;
        kids.forEach(k => {
            if (perD(b) && k.dest && k.dest !== destId) return;
            if (k.color && k.color !== colorId) return;
            if (k.sizes && !k.sizes.includes(sizeId)) return;
            best = k;
        });
        return best;
    };
    // Qty SO per destinasi x color x size ke baris penampungnya; destinasi dipakai dari sub-baris (kalau dipecah) atau induk.
    function eachQty(b, kids, fn) {
        DESTS.forEach(d =>
            COLORS.forEach(c =>
                SIZES.forEach((z, i) => {
                    const qq = q(d.id, c.id, i);
                    if (!qq) return;
                    const best = bestOf(b, kids, c.id, z.id, d.id),
                        dest = eff(best, 'dest');
                    if (dest && dest !== d.id) return;
                    fn(best, qq, c, z, i);
                }),
            ),
        );
    }
    function calc() {
        const out = {};
        ORIG = {};
        rows.filter(r => !r.parent).forEach(b => {
            const kids = rows.filter(x => x.parent === b.uid);
            [b, ...kids].forEach(x => {
                out[x.uid] = 0;
                ORIG[x.uid] = 0;
            });
            eachQty(b, kids, (best, qq) => {
                ORIG[best.uid] += qq;
                out[best.uid] += qq * (eff(best, 'cons') || 0);
            });
            [b, ...kids].forEach(x => {
                if (unitName(eff(x, 'unit')) === 'LOT') out[x.uid] = eff(x, 'cons') || 0;
            });
        });
        return out;
    }
    const totQ = r => {
        const n = NEED[r.uid] || 0;
        return isOth(r) ? n : Math.ceil(n * (1 + (eff(r, 'loss') || 0) / 100));
    };
    const valOf = r => totQ(r) * (eff(r, 'price') || 0);

    // ===== header, gambar, breakdown
    function renderHeader() {
        const h = PAGE.header,
            v = x => (x === null || x === undefined || x === '' ? '-' : esc(x));
        $('#h-costing').innerHTML = v(h.costing_no);
        $('#h-ws').innerHTML = v(h.no_ws);
        $('#h-so').innerHTML = v(h.so_no);
        $('#h-bom').innerHTML = h.bom_no ? esc(h.bom_no) : '-';
        const f = list =>
            list
                .map(
                    ([l, x]) =>
                        `<div class="bom-summary-item"><span>${l}</span><strong title="${esc(x ?? '')}">${v(x)}</strong></div>`,
                )
                .join('');
        $('#f-buyer').innerHTML = f([
            ['Buyer Name', h.buyer],
            ['Kode Buyer', h.buyer_code],
            ['Brand', h.brand],
            ['Season', h.season],
            ['Style', h.style],
            ['QTY Planned', h.planned_qty ? fmt(h.planned_qty, 0) : null],
        ]);
        $('#f-product').innerHTML = f([
            ['Product Group', h.product_group],
            ['Product Items', h.product_item],
            ['Product Type', h.product_set],
            ['Unit', h.unit],
        ]);
    }
    // Review Image: coverflow sama dengan halaman BOM lama (semua gambar dirender sekali, pindah gambar cukup geser & skala).
    let imgAt = 0;
    function renderImages() {
        const stage = $('#bom-image-stage'),
            has = IMAGES.length > 0;
        $('#bom-image-empty').hidden = has;
        $('#bom-image-prev').disabled = $('#bom-image-next').disabled = IMAGES.length < 2;
        $('#bom-image-thumbnails').classList.toggle('d-none', !has);
        if (!stage.querySelector('img.mkt-cf-item') && has) {
            stage.insertAdjacentHTML(
                'beforeend',
                IMAGES.map(
                    (u, i) =>
                        `<img class="mkt-cf-item" data-img="${i}" src="${esc(u)}" alt="" style="transition:none">`,
                ).join(''),
            );
        }
        const items = [...stage.querySelectorAll('img.mkt-cf-item')];
        const gap = Math.min(195, Math.max(0, stage.clientWidth / 2 - 49));
        items.forEach((el, i) => {
            const off = i - imgAt,
                far = Math.abs(off) > 1;
            el.style.transform = `translate(-50%, -50%) translateX(${Math.max(-2, Math.min(2, off)) * gap}px) scale(${far ? 0.8 : 1})`;
            el.style.zIndex = String(10 - Math.min(Math.abs(off), 9));
            el.style.opacity = off === 0 ? '1' : '0.85';
            el.classList.toggle('is-active', off === 0);
            el.classList.toggle('is-side', off !== 0);
            el.classList.toggle('is-far', far);
        });
        if (items.length) {
            void stage.offsetWidth;
            items.forEach(el => {
                el.style.transition = '';
            });
        }
        $('#bom-image-thumbnails').innerHTML = IMAGES.map(
            (u, i) =>
                `<button type="button" class="bom-image-thumbnail${i === imgAt ? ' is-active' : ''}" data-img="${i}" aria-label="Tampilkan gambar ${i + 1}"><img src="${esc(u)}" alt=""></button>`,
        ).join('');
        $('#bom-image-counter').innerHTML =
            IMAGES.length < 2
                ? ''
                : IMAGES.map(
                      (u, i) =>
                          `<button type="button" class="mkt-cf-dot${i === imgAt ? ' is-active' : ''}" data-img="${i}" aria-label="Gambar ${i + 1}"></button>`,
                  ).join('');
    }
    if (window.ResizeObserver) new ResizeObserver(() => renderImages()).observe($('#bom-image-stage'));
    function renderBO() {
        const tb = $('#bom-breakdown-table');
        if (!COLORS.length) {
            tb.tHead.innerHTML = '<tr><th>No</th><th>Destination</th><th>Color</th></tr>';
            tb.tFoot.innerHTML = '';
            tb.tBodies[0].innerHTML =
                '<tr><td colspan="3" class="text-muted">Belum ada qty di Sales Order WS ini.</td></tr>';
            return;
        }
        tb.tHead.innerHTML = `<tr><th>No</th><th>Destination</th><th>Color</th>${SIZES.map(s => `<th>${esc(s.name)}</th>`).join('')}<th>Total</th></tr>`;
        const col = SIZES.map(() => 0);
        let body = '',
            no = 0;
        // Satu baris per destinasi x color (yang ada qty-nya), destinasi tampil "NEGARA - DESTINASI".
        DESTS.forEach(d =>
            COLORS.forEach(c => {
                const vals = SIZES.map((s, i) => q(d.id, c.id, i));
                if (!vals.some(v => v > 0)) return;
                let t = 0;
                const cells = vals
                    .map((v, i) => {
                        t += v;
                        col[i] += v;
                        return `<td>${v ? fmt(v, 0) : '-'}</td>`;
                    })
                    .join('');
                body += `<tr><td>${++no}</td><td class="bom-breakdown-label">${esc((DEST_BY[d.id] || d).name)}</td><td class="bom-breakdown-label">${esc(c.name)}</td>${cells}<td class="bom-breakdown-total">${fmt(t, 0)}</td></tr>`;
            }),
        );
        tb.tBodies[0].innerHTML = body;
        tb.tFoot.innerHTML = `<tr><th colspan="3" class="text-left">TOTAL</th>${col.map(v => `<th>${fmt(v, 0)}</th>`).join('')}<th>${fmt(
            col.reduce((a, b) => a + b, 0),
            0,
        )}</th></tr>`;
    }
    function renderStale() {
        const s = PAGE.stale || { changes: [], info: [] },
            box = $('#bom-stale');
        box.hidden = !s.changes.length && !s.info.length;
        box.innerHTML =
            (s.changes.length
                ? `<div class="t"><i class="fas fa-exclamation-triangle"></i> BOM perlu diperbarui: Sales Order / Costing berubah setelah BOM disimpan. Tabel di bawah sudah disesuaikan (baris kuning = baru), klik <b>Save</b> untuk menyimpannya.</div><ul>${s.changes.map(c => `<li>${esc(c)}</li>`).join('')}</ul>`
                : '') + s.info.map(i => `<div class="i"><i class="fas fa-info-circle"></i> ${esc(i)}</div>`).join('');
    }
    function renderTabs() {
        $('#bom-content-tabs').innerHTML = TABS.map(
            ([k, l]) =>
                `<li class="nav-item"><a href="#" class="nav-link${k === tab ? ' active' : ''}" role="tab" data-t="${k}" aria-selected="${k === tab}">${l}</a></li>`,
        ).join('');
    }
    // ===== tabel input
    function nf(k, v) {
        return fmt(
            v,
            k === 'pc' || k === 'price'
                ? 2
                : k === 'cons' || k === 'consC'
                  ? 3
                  : k === 'loss'
                  ? 0
                  : Number.isInteger(v)
                    ? 0
                    : Math.max(2, String(v).split('.')[1]?.length || 0),
        );
    }
    function disp(r, c) {
        if (c.k === 'tq') {
            const v = totQ(r);
            return v ? fmt(v, 0) : '--';
        }
        if (c.k === 'qr') {
            const v = NEED[r.uid] || 0;
            return v ? fmt(v) : '--';
        }
        if (c.k === 'lv') {
            const v = ((NEED[r.uid] || 0) * (eff(r, 'loss') || 0)) / 100;
            return v ? fmt(v) : '--';
        }
        if (c.k === 'val') {
            const v = valOf(r);
            return v ? fmt(v) : '--';
        }
        if (c.k === 'oq') {
            const v = ORIG[r.uid];
            return v
                ? fmt(v, 0)
                : r.parent || isOth(r) || r.rule === RULES[0]
                  ? '--'
                  : '<span class="dim">&darr; rincian</span>';
        }
        if (c.k === 'act')
            return r.parent
                ? ''
                : `<span class="ia" data-up="${r.uid}" title="Sama dengan baris atas">&#11014;</span><span class="ia del" data-rst="${r.uid}" title="Hapus baris"><i class="fas fa-trash-alt"></i></span>`;
        if (c.k === 'rule')
            return r.parent ? '' : `<span title="${esc(ruleBase(r))}">${esc(ruleBase(r))}</span>`;
        if (c.k === 'dest' && !r.parent && perD(r)) return '<span class="dim">per destinasi &darr;</span>';
        if (r.parent && (c.k === 'content' || c.k === 'shell')) return c.k === 'content' ? '&#8627;' : '';
        if (c.k === 'color')
            return r.parent
                ? r.color
                    ? esc(colorName(r.color))
                    : '<span class="dim">Semua</span>'
                : perC(r)
                  ? '<span class="dim">per warna &darr;</span>'
                  : 'Semua';
        if (c.k === 'size')
            return r.parent
                ? r.sizes
                    ? esc(rangeTxt(r.sizes))
                    : '<span class="dim">Semua</span>'
                : perR(r)
                  ? '<span class="dim">per size &darr;</span>'
                  : 'Semua';
        if (c.k === 'punit') {
            const u = eff(r, 'unit');
            return u ? esc(unitName(u)) : '<span class="nil">--</span>';
        }
        // Sub-baris: kolom yang bukan milik sub (unit, loss, supplier, price, destinasi) tampil nilai induk (abu miring).
        if (r.parent && !SUB_KEYS.includes(c.k) && (r[c.k] === null || r[c.k] === undefined || r[c.k] === '')) {
            const b = baseOf(r),
                v = b[c.k];
            return v === null || v === undefined || v === ''
                ? ''
                : `<span title="ikut baris induk">${disp(b, c)}</span>`;
        }
        if (c.k === 'dest' && isOth(r)) return 'Semua';
        if (c.k === 'shell' && r.parts)
            return `${esc(r.shell)}${r.shell ? ' · ' : ''}<span class="dim">${esc(r.parts)}</span>`;
        const v = r[c.k];
        if (c.k === 'dest') return v ? esc(label('dest', v)) : 'Semua';
        if (v === null || v === '' || v === undefined) return '<span class="nil">--</span>';
        if (c.k === 'pc') return esc(nf('pc', v)) + (r.cur ? ` <span class="dim">${esc(r.cur)}</span>` : '');
        return c.num ? nf(c.k, v) : esc(label(c.k, v));
    }
    function cls(r, c, ri, ci) {
        const a = [];
        if (c.wrap) a.push('wrapc');
        const subRo = (r.parent && !SUB_KEYS.includes(c.k)) || lockedDest(r, c);
        if (c.ro || c.gen || subRo) a.push('ro');
        if (c.auto) a.push('au');
        if (c.num && !c.auto) a.push('n');
        if (ci === 0) a.push('c0');
        if (c.list && !c.ro && !subRo) a.push('lst');
        else if (!c.ro && !c.gen && !c.auto && !subRo) a.push('edt');
        const [r0, r1] = [Math.min(act.r, anc.r), Math.max(act.r, anc.r)],
            [c0, c1] = [Math.min(act.c, anc.c), Math.max(act.c, anc.c)];
        if (ri >= r0 && ri <= r1 && ci >= c0 && ci <= c1) a.push('sel');
        if (ri === act.r && ci === act.c) a.push('acell');
        if (pasted.has(r.uid + '|' + c.k)) a.push('pasted');
        if (fill && ri > fill.r1 && ri <= fill.to && ci >= fill.c0 && ci <= fill.c1) a.push('fillt');
        if (!editing && ri === r1 && ci === c1) a.push('fhc');
        return a.join(' ');
    }
    function render() {
        rows.filter(r => !r.parent && !isOth(r) && r.sig !== sigOf(r)).forEach(genSubs); // Rule BOM / range berubah
        NEED = calc();
        const C = cols(),
            V = vis();
        act.r = Math.max(0, Math.min(act.r, V.length - 1));
        anc.r = Math.max(0, Math.min(anc.r, V.length - 1));
        act.c = Math.min(act.c, C.length - 1);
        anc.c = Math.min(anc.c, C.length - 1);
        const dot = { cost: 'd-cost', so: 'd-so', in: 'd-in', auto: 'd-auto', none: '' };
        const LEFT = [];
        let acc = 50;
        C.forEach((c, i) => {
            if (c.fz) {
                LEFT[i] = acc;
                acc += c.w;
            }
        });
        const lastFz = C.map(c => !!c.fz).lastIndexOf(true);
        // Kolom beku lebarnya tetap (posisi left dihitung dari lebar ini); kolom lain lebarnya ikut isi.
        const fzA = i => (C[i].fz ? ` fz${i === lastFz ? ' last' : ''}" style="left:${LEFT[i]}px;width:${C[i].w}px!important;min-width:${C[i].w}px!important;max-width:${C[i].w}px!important` : '');
        const FLEX = C.findIndex(c => c.k === 'item');
        $('#xs').style.minWidth = 50 + C.reduce((a, c) => a + c.w, 0) + 'px';
        const colg = `<colgroup><col style="width:50px">${C.map((c, i) => (i === FLEX ? '<col>' : `<col style="width:${c.w}px">`)).join('')}</colgroup>`;
        const allCk = V.length && V.every(r => chk.has(r.uid));
        let h =
            colg +
            `<thead><tr><th class="rn"><span class="T ckT"><input type="checkbox" class="ck" data-ck="all" ${allCk ? 'checked' : ''}>NO<span class="srt">&#8645;</span></span></th>${C.map((c, i) => `<th title="${c.t}" class="${fzA(i)}${c.add ? ' addc' : ''}"><span class="T">${c.src !== 'none' ? `<span class="dot ${dot[c.src]}"></span>` : ''}${c.t}${(c.src === 'in' && !c.auto) || c.pen ? '<span class="pen">&#9998;</span>' : ''}</span></th>`).join('')}</tr></thead><tbody>`;
        if (!V.length)
            h += `<tr><td class="rn"></td><td colspan="${C.length}" class="dim" style="height:60px;text-align:center">Belum ada ${tab === 'OTH' ? 'Other Cost' : tab === 'MFG' ? 'Manufacturing' : 'material'} di Costing untuk WS ini.</td></tr>`;
        let prev = null;
        const selR1 = Math.max(act.r, anc.r),
            selC1 = Math.max(act.c, anc.c);
        V.forEach((r, ri) => {
            const gs = !r.parent && prev && prev.content !== r.content;
            if (!r.parent) prev = r;
            // Baris yang belum ada di BOM tersimpan (material baru di Costing / color-size baru di SO / rule baru) ditandai kuning.
            const chg = PAGE.header.bom_no && (r.parent ? r.fresh : !r.dbid);
            h += `<tr class="${r.parent ? 'xsub' : 'base'}${chg ? ' chg' : ''}${gs ? ' gs' : ''}${chk.has(r.uid) ? ' chk' : ''}"><td class="rn"><span class="ckT"><input type="checkbox" class="ck" data-ck="${r.uid}" ${chk.has(r.uid) ? 'checked' : ''}>${ri + 1}</span></td>${C.map(
                (c, ci) =>
                    editing && editing.r === ri && editing.c === ci
                        ? `<td class="acell${fzA(ci)}">${editing.combo ? comboHtml() : `<input id="ed" autocomplete="off" value="${esc(editing.v)}">`}</td>`
                        : `<td class="${cls(r, c, ri, ci)}${fzA(ci)}" data-r="${ri}" data-c="${ci}">${disp(r, c)}${!editing && ri === selR1 && ci === selC1 ? '<span class="fh" title="Tarik ke bawah untuk menyalin"></span>' : ''}</td>`,
            ).join('')}</tr>`;
            // Range size: klik garis di antara size untuk gabung / pisah range.
            if (!r.parent && perR(r) && r.ranges) h += rangeBar(r, C.length);
        });
        dropCombo();
        $('#xs').innerHTML = h + '</tbody>';
        renderStatus();
        if (editing && editing.combo) openCombo($('#ed'));
        else if (editing) {
            const e = $('#ed');
            e.focus();
            if (editing.sel) e.select();
            else e.setSelectionRange(e.value.length, e.value.length);
        } else if (
            !$('#wrap').contains(document.activeElement) &&
            (document.activeElement === document.body || !document.activeElement)
        )
            $('#wrap').focus({ preventScroll: true });
    }
    function rangeBar(r, span) {
        const sizes = SIZES.map((z, i) => {
            const end = r.ranges.some(a => a[a.length - 1] === z.id);
            const qty = COLORS.reduce((a, c) => a + DESTS.reduce((y, d) => y + q(d.id, c.id, i), 0), 0);
            const cut =
                i < SIZES.length - 1
                    ? `<button type="button" class="cut${end ? ' on' : ''}" data-cut="${r.uid}|${i}" title="${end ? 'Gabung' : 'Pisah'}">${end ? '&#9475;' : '&middot;'}</button>`
                    : '';
            return `<b class="rz">${esc(z.name)}<small>${fmt(qty, 0)}</small></b>${cut}`;
        }).join('');
        const lines = r.ranges.length * (perC(r) ? COLORS.length : 1);
        return `<tr class="rgs"><td class="rn"></td><td colspan="${span}"><div class="rgbar"><span>Range size <small>(klik garis buat gabung/pisah)</small>:</span>${sizes}<span class="rgn">${r.ranges.length} range &rarr; ${lines} baris</span></div></td></tr>`;
    }
    function renderStatus() {
        const C = cols(),
            V = vis();
        let s = 0,
            n = 0;
        for (let i = Math.min(act.r, anc.r); i <= Math.max(act.r, anc.r); i++)
            for (let j = Math.min(act.c, anc.c); j <= Math.max(act.c, anc.c); j++) {
                const c = C[j],
                    r = V[i];
                if (!r) continue;
                const v = c.k === 'tq' ? totQ(r) : c.k === 'val' ? valOf(r) : c.num ? r[c.k] : null;
                if (typeof v === 'number') {
                    s += v;
                    n++;
                }
            }
        const bases = V.filter(r => !r.parent),
            ok = bases.filter(r => {
                const kids = V.filter(k => k.parent === r.uid);
                return kids.length
                    ? kids.every(k => k.item && k.cons && eff(k, 'unit') && eff(k, 'price') != null)
                    : r.item && r.cons && r.unit && r.price != null;
            }).length;
        const val = V.reduce((a, r) => a + valOf(r), 0);
        $('#status').innerHTML =
            `<span style="display:flex;align-items:center;gap:10px">Item Per Page <span class="ipp" style="border:1px solid var(--navy);border-radius:6px;padding:3px 12px">25 &#10005;</span> Of ${V.length}</span><span style="display:flex;gap:16px;color:var(--mute)"><span>Lengkap <b>${ok}/${bases.length}</b></span>${n > 1 ? `<span>Jumlah <b>${fmt(s)}</b></span>` : ''}<span>Value <b>${fmt(val)}</b></span><span style="color:var(--navy)">&lsaquo; <b style="background:var(--navy);color:#fff;border-radius:5px;padding:1px 8px">1</b> &rsaquo;</span></span>`;
    }

    // ===== preview (read-only): kebutuhan tiap material, bisa dipecah per color x size
    // Satu baris preview per sub-baris (atau per color x size kalau dicentang); src = baris yang dipakai cons & item-nya.
    function explode(r, all) {
        const M = {},
            kids = rows.filter(x => x.parent === r.uid);
        eachQty(r, kids, (src, qq, c, z, i) => {
            const key = all ? c.id + '|' + z.id : src.uid;
            (M[key] = M[key] || {
                src,
                oq: 0,
                at: all ? COLORS.indexOf(c) * 1000 + i : rows.indexOf(src),
                color: all ? c.name : src.color ? colorName(src.color) : 'ALL',
                size: all ? z.name : src.sizes ? rangeTxt(src.sizes) : 'ALL',
            }).oq += qq;
        });
        return Object.values(M).sort((x, y) => x.at - y.at);
    }
    function renderPv() {
        const all = $('#pvAll').checked;
        const SEC = {
            FAB: 'FABRIC',
            SEW: 'ACCESSORIES SEWING',
            PACK: 'ACCESSORIES PACKING',
            MFG: 'MANUFACTURING',
            OTH: 'OTHER COST',
        };
        const tabs = tab === 'DOCS' ? ['FAB', 'SEW', 'PACK', 'MFG', 'OTH'] : [tab];
        const PH = [
            ['NO', ''],
            ['Content', 'cost'],
            ['Shell', 'cost'],
            ['Rule BOM', 'in'],
            ['Color', 'so'],
            ['Size', 'so'],
            ['Item', 'in'],
            ['Destinasi', 'in'],
            ['Orig qty', 'so', 1],
            ['Cons/pc', 'in', 1],
            ['Unit', 'in'],
            ['Qty req', 'auto', 1],
            ['Loss %', 'in', 1],
            ['Value', 'auto', 1],
            ['Total qty req', 'auto', 1],
            ['Unit', 'in'],
            ['Supplier', 'in'],
            ['Price', 'in', 1],
            ['Price costing', 'cost', 1],
            ['Total price', 'auto', 1],
        ];
        const D = { cost: 'd-cost', so: 'd-so', in: 'd-in', auto: 'd-auto' },
            NC = PH.length;
        let h = `<tr>${PH.map(([t, src, n]) => `<th class="${n ? 'n' : ''}">${src ? `<span class="dot ${D[src]}"></span>` : ''}${t}</th>`).join('')}</tr>`;
        let lines = 0,
            grand = 0;
        tabs.forEach(tb => {
            h += `<tr class="sec"><td colspan="${NC}">${SEC[tb]}</td></tr>`;
            let no = 0;
            rows.filter(r => r.tab === tb && !r.parent).forEach(r => {
                no++;
                explode(r, all).forEach((e, i) => {
                    const cons = eff(e.src, 'cons') || 0,
                        loss = CHG(tb) ? 0 : eff(e.src, 'loss') || 0,
                        u = eff(e.src, 'unit'),
                        pr = eff(e.src, 'price'),
                        req = unitName(u) === 'LOT' ? cons : e.oq * cons,
                        lv = (req * loss) / 100,
                        tot = CHG(tb) ? req : Math.ceil(req * (1 + loss / 100)),
                        val = tot * (pr || 0);
                    grand += val;
                    lines++;
                    h += `<tr class="${i === 0 ? 'g' : ''}"><td>${i === 0 ? no : ''}</td><td class="k">${i === 0 ? esc(r.content) : ''}</td><td>${i === 0 ? esc(r.shell || '') : ''}</td><td>${i === 0 && !CHG(tb) ? esc(ruleBase(r)) : ''}</td>
                <td>${esc(e.color)}</td><td>${esc(e.size)}</td><td>${esc(label('item', eff(e.src, 'item'))) || '<span class="dim">—</span>'}</td><td>${CHG(tb) ? '' : esc(label('dest', eff(e.src, 'dest'))) || 'Semua'}</td>
                <td class="n">${fmt(e.oq, 0)}</td><td class="n">${cons ? nf('cons', cons) : '--'}</td><td>${esc(unitName(u))}</td><td class="n">${req ? fmt(req) : '--'}</td><td class="n">${CHG(tb) ? '' : loss}</td><td class="n">${lv ? fmt(lv) : '--'}</td><td class="n">${tot ? fmt(tot, 0) : '--'}</td>
                <td>${esc(unitName(u))}</td><td>${esc(label('sup', eff(e.src, 'sup')))}</td><td class="n">${pr != null ? nf('price', pr) : '--'}</td><td class="n">${r.pc != null ? nf('price', r.pc) : '--'}</td><td class="n">${val ? fmt(val) : '--'}</td></tr>`;
                });
            });
        });
        h += `<tr class="tot"><td colspan="${NC - 1}">TOTAL VALUE</td><td class="n">${fmt(grand)}</td></tr>`;
        $('#pv').innerHTML = h;
        $('#pvInfo').textContent =
            `${lines} baris preview · dari ${rows.filter(r => tabs.includes(r.tab) && !r.parent).length} baris input`;
    }
    function setMode(m) {
        mode = m;
        document
            .querySelectorAll('#bomx [data-mode]')
            .forEach(b => b.setAttribute('aria-pressed', b.dataset.mode === m));
        $('#pvBox').hidden = m !== 'pv';
        $('#inBox').hidden = m !== 'in';
        if (m === 'pv') {
            NEED = calc();
            renderPv();
        } else {
            if (tab === 'DOCS') {
                tab = 'FAB';
                renderTabs();
            }
            render();
        }
    }

    // ===== edit
    // Sub-baris induk "Per destinasi": destinasinya dibentuk otomatis, tidak bisa diubah.
    const lockedDest = (r, c) => c.k === 'dest' && !!r.parent && perD(baseOf(r));
    const editable = (r, c) => !(c.ro || c.auto || c.gen || c.fixed) && !lockedDest(r, c) && (!r.parent || SUB_KEYS.includes(c.k));
    // Isian baris induk diturunkan ke sub-barisnya yang masih kosong / masih sama dengan nilai induk sebelumnya.
    // Sub yang sudah diisi beda (mis. zipper S–M 15 cm, L–XL 16 cm) tidak ketimpa.
    function cascade(r, k, old) {
        if (r.parent || !SUB_KEYS.includes(k) || (k === 'dest' && perD(r))) return;
        rows.filter(x => x.parent === r.uid).forEach(x => {
            if (x[k] === null || x[k] === undefined || x[k] === '' || x[k] === old) x[k] = r[k];
        });
    }
    function assignCascade(r, k, v) {
        const old = r[k];
        r[k] = v;
        cascade(r, k, old);
    }
    function startEdit(v, sel) {
        if (lockedNotice()) return;
        const r = vis()[act.r],
            c = cols()[act.c];
        if (!r) return;
        if (!editable(r, c)) {
            toast(
                c.fixed ||
                    (c.auto
                        ? 'Kolom ini dihitung otomatis'
                        : c.gen
                          ? NO_DETAIL
                          : lockedDest(r, c)
                            ? 'Destinasi baris ini dari pilihan "Per destinasi" di baris induk. Ganti Destinasi di baris induknya'
                          : r.parent && !c.ro
                            ? 'Unit, Loss, Supplier, Price, dan Destinasi baris ini ikut baris induk. Ubah di baris induknya'
                            : 'Kolom ini dari Costing, tidak bisa diubah di sini'),
            );
            return;
        }
        const cur = c.num
            ? String(r[c.k] ?? '').replace('.', ',')
            : c.k === 'rule'
              ? ruleBase(r)
              : c.k === 'dest' && !r.parent && perD(r)
                ? PER_DEST
                : label(c.k, r[c.k]);
        // Kolom berdaftar memakai select2; huruf yang diketik di sel jadi isi awal kotak cari.
        editing = c.list
            ? { r: act.r, c: act.c, combo: true, keep: cur, seed: v || '', opts: c.list(r), chosen: false, move: null }
            : { r: act.r, c: act.c, v: v !== undefined ? v : cur, sel, keep: null };
        render();
    }
    function comboHtml() {
        // Unit: daftar tampil "kode - nama", yang tersimpan & tampil di sel tetap kodenya.
        const unitCol = ['unit', 'punit'].includes((cols()[editing.c] || {}).k),
            full = x => (unitCol && (UNITS.find(u => u.name === x) || {}).full) || x;
        const opts = editing.opts.map(x => `<option value="${esc(x)}"${x === editing.keep ? ' selected' : ''}>${esc(full(x))}</option>`);
        return `<select id="ed"><option></option>${opts.join('')}</select>`;
    }
    function dropCombo() {
        const el = $('#ed');
        if (!el || el.tagName !== 'SELECT' || !jQuery(el).data('select2')) return;
        const $el = jQuery(el).off('.bomx');
        if ($el.select2('isOpen')) $el.select2('close');
        $el.select2('destroy');
    }
    function openCombo(el) {
        const edit = editing,
            $el = jQuery(el);
        $el.select2({
            theme: 'bootstrap4',
            width: '100%',
            placeholder: edit.keep || 'Pilih',
            dropdownAutoWidth: true,
            dropdownCssClass: 'bom-combo-drop',
            // Pilihan terpilih di sel cukup nilainya (Unit: kode saja, bukan "kode - nama").
            templateSelection: d => d.id || d.text,
        });
        $el.on('change.bomx', () => (edit.chosen = true)).on('select2:close.bomx', () =>
            // Ditunda: commit menggambar ulang tabel, jadi select2 harus selesai menutup dulu.
            setTimeout(() => {
                if (editing === edit) commit(edit.move);
            }, 0),
        );
        // Ditunda: mousedown yang membuka sel masih merambat ke body, dan select2 menutup dropdown yang terbuka di sana.
        setTimeout(() => {
            if (editing !== edit) return;
            $el.select2('open');
            const search = document.querySelector('.bom-combo-drop .select2-search__field');
            if (!search) return;
            search.addEventListener(
                'keydown',
                ev => {
                    if (ev.key !== 'Tab') {
                        edit.move = ev.key === 'Enter' ? 'down' : null;
                        return;
                    }
                    ev.preventDefault();
                    ev.stopImmediatePropagation();
                    edit.move = 'right';
                    $el.data('select2').trigger('results:select', {});
                    $el.select2('close');
                },
                true,
            );
            search.focus();
            if (edit.seed) {
                search.value = edit.seed;
                jQuery(search).trigger('input');
            }
        }, 0);
    }
    function parseNum(v) {
        let s = String(v).trim().replace(/\s|%/g, '');
        if (s.includes(',') && s.includes('.'))
            s = s.lastIndexOf(',') > s.lastIndexOf('.') ? s.replace(/\./g, '').replace(',', '.') : s.replace(/,/g, '');
        else s = s.replace(',', '.');
        return s === '' || isNaN(s) ? NaN : parseFloat(s);
    }
    function setVal(r, c, v) {
        const unit = r.unit,
            old = r[c.k];
        if (!setValue(r, c, v)) return false;
        cascade(r, c.k, old);
        if (r.unit !== unit) cascade(r, 'unit', unit); // pilih item mengisi unit kosong
        return true;
    }
    function setValue(r, c, v) {
        // BOM Approved terkunci: ketik / paste / fill / Delete diabaikan (server juga menolak Save).
        if (bomLocked()) return false;
        v = String(v).trim();
        if (c.num) {
            if (v === '') {
                r[c.k] = null;
                return true;
            }
            const n = parseNum(v);
            if (isNaN(n) || n < 0 || (c.k === 'loss' && n > 100)) return false;
            // Cons 3 desimal, price 2 desimal (sama dengan yang disimpan server).
            const dp = { cons: 3, consC: 3, price: 2, pc: 2 }[c.k];
            r[c.k] = dp === undefined ? n : Number(n.toFixed(dp));
            return true;
        }
        // Destinasi baris induk: "Per destinasi" = rule + " Per Destination" (rincian dipecah per destinasi SO).
        if (c.k === 'dest' && !r.parent && !isOth(r)) {
            const lv = v.toLowerCase();
            if (lv === PER_DEST.toLowerCase()) {
                if (!canPerDest(r)) return false;
                r.rule = ruleBase(r) + PD_SUFFIX;
                r.dest = null;
                return true;
            }
            let id = null;
            if (lv !== '' && lv !== 'semua') {
                const m = NAMED.dest(v);
                if (!m) return false;
                id = m.id;
            }
            if (perD(r)) {
                // Keluar dari per destinasi: rincian per destinasi digabung, destinasinya ikut pilihan baru.
                r.rule = ruleBase(r);
                rows.filter(x => x.parent === r.uid).forEach(x => (x.dest = id));
                toast('Rincian per destinasi digabung jadi satu');
            }
            r.dest = id;
            return true;
        }
        if (c.k === 'dest' && (v === '' || v.toLowerCase() === 'semua')) {
            r.dest = null;
            return true;
        }
        if (c.k === 'rule') {
            // Terima juga nama 8 rule lama (mis. hasil copy/paste); "Per Destination" ikut pilihan Destinasi.
            const pdIn = / per destination$/i.test(v),
                base = v.replace(/ per destination$/i, '');
            const m = RULE_BASE.find(x => x.toLowerCase() === base.toLowerCase()) || (base === '' ? RULES[0] : null);
            if (!m) return false;
            if (pdIn && !canPerDest(r)) return false;
            r.rule = m + (pdIn || perD(r) ? PD_SUFFIX : '');
            if (perD(r)) r.dest = null;
            return true;
        }
        if (v === '') {
            r[c.k] = null;
            return true;
        }
        if (c.k === 'item') {
            const own = ITEMS.filter(x => x.content === baseOf(r).contentId),
                lv = v.toLowerCase();
            const m = own.find(x => x.name.toLowerCase() === lv) || ITEMS.find(x => x.name.toLowerCase() === lv);
            if (!m) return false;
            r.item = m.id;
            if (!r.parent && m.unit && !r.unit) r.unit = m.unit;
            return true;
        }
        const m = NAMED[c.k] && NAMED[c.k](v);
        if (!m) return false;
        r[c.k] = m.id;
        return true;
    }
    function commit(move) {
        if (!editing) return;
        // Dropdown ditutup tanpa memilih: nilai lama tetap.
        if (editing.combo && !editing.chosen) {
            editing = null;
            if (move === 'down') mv(1, 0);
            else if (move === 'right') mv(0, 1);
            else render();
            return;
        }
        const r = vis()[editing.r],
            c = cols()[editing.c],
            v = $('#ed').value;
        if (editing.keep !== null && v.trim() === '') {
            editing = null;
            render();
            return;
        } // tidak memilih apa-apa = tetap nilai lama
        if (setVal(r, c, v)) markDirty();
        else toast(`"${v}" tidak ada di daftar ${c.t}` + (c.num ? ' (harus angka)' : ''));
        editing = null;
        if (move === 'down') mv(1, 0);
        else if (move === 'right') mv(0, 1);
        else render();
    }
    function mv(dr, dc, ext) {
        const V = vis(),
            C = cols();
        act.r = Math.max(0, Math.min(V.length - 1, act.r + dr));
        act.c = Math.max(0, Math.min(C.length - 1, act.c + dc));
        if (!ext) anc = { ...act };
        render();
        const td = document.querySelector(`#xs td[data-r="${act.r}"][data-c="${act.c}"]`);
        td && td.scrollIntoView({ block: 'nearest', inline: 'nearest' });
    }
    function range() {
        return {
            r0: Math.min(act.r, anc.r),
            r1: Math.max(act.r, anc.r),
            c0: Math.min(act.c, anc.c),
            c1: Math.max(act.c, anc.c),
        };
    }
    function clearSel() {
        if (lockedNotice()) return;
        const { r0, r1, c0, c1 } = range(),
            V = vis(),
            C = cols();
        for (let i = r0; i <= r1; i++)
            for (let j = c0; j <= c1; j++)
                if (V[i] && editable(V[i], C[j])) {
                    assignCascade(V[i], C[j].k, null);
                }
        markDirty();
        render();
    }
    function fillDown() {
        if (lockedNotice()) return;
        const { r0, r1, c0, c1 } = range(),
            V = vis(),
            C = cols();
        if (r0 === r1) {
            if (r0 === 0) return;
            for (let j = c0; j <= c1; j++)
                if (editable(V[r0], C[j])) {
                    assignCascade(V[r0], C[j].k, eff(V[r0 - 1], C[j].k) ?? null);
                }
        } else
            for (let i = r0 + 1; i <= r1; i++)
                for (let j = c0; j <= c1; j++)
                    if (editable(V[i], C[j])) {
                        assignCascade(V[i], C[j].k, eff(V[r0], C[j].k) ?? null);
                    }
        markDirty();
        render();
        toast('Diisi ke bawah');
    }
    // Fill handle: sel terpilih disalin berulang ke baris di bawahnya (pola baris ikut berulang, seperti Excel).
    let fill = null;
    function applyFill() {
        const f = fill;
        fill = null;
        if (!f || f.to <= f.r1 || lockedNotice()) {
            render();
            return;
        }
        const V = vis(),
            C = cols(),
            h = f.r1 - f.r0 + 1;
        let n = 0;
        for (let i = f.r1 + 1; i <= f.to; i++)
            for (let j = f.c0; j <= f.c1; j++) {
                if (!V[i] || !editable(V[i], C[j])) continue;
                assignCascade(V[i], C[j].k, eff(V[f.r0 + ((i - f.r1 - 1) % h)], C[j].k) ?? null);
                n++;
            }
        anc = { r: f.r0, c: f.c0 };
        act = { r: f.to, c: f.c1 };
        if (n) {
            markDirty();
            toast(`${n} sel diisi`);
        }
        render();
    }
    document.addEventListener('mouseup', () => {
        if (fill) applyFill();
    });
    function copyFromAbove(r) {
        if (lockedNotice()) return;
        const V = vis(),
            p = V[V.indexOf(r) - 1];
        if (!p) {
            toast('Ini baris pertama');
            return;
        }
        keysOf(r).forEach(k => {
            assignCascade(r, k, eff(p, k) ?? null);
        });
        if (!r.parent && !p.parent) {
            r.rule = p.rule;
            r.ranges = p.ranges ? p.ranges.map(a => [...a]) : null;
        }
        markDirty();
        toast('Disalin dari baris atas');
    }

    // ===== events
    const wrap = $('#wrap'),
        xs = $('#xs');
    xs.addEventListener('change', e => {
        const k = e.target.dataset && e.target.dataset.ck;
        if (!k) return;
        if (k === 'all') {
            const on = e.target.checked;
            vis().forEach(r => (on ? chk.add(r.uid) : chk.delete(r.uid)));
        } else {
            const id = +k;
            e.target.checked ? chk.add(id) : chk.delete(id);
        }
        render();
        if (window.bomUpdClear) window.bomUpdClear();
    });
    xs.addEventListener('mousedown', e => {
        if (e.button !== 0 || e.target.classList.contains('ck') || e.target.id === 'ed') return;
        if (e.target.classList.contains('fh')) {
            e.preventDefault();
            if (lockedNotice()) return;
            const g = range();
            fill = { ...g, to: g.r1 };
            return;
        }
        const ia = e.target.closest('.ia');
        if (ia) {
            e.preventDefault();
            if (lockedNotice()) return;
            const r = rows.find(x => x.uid == (ia.dataset.up || ia.dataset.rst));
            if (ia.dataset.up) copyFromAbove(r);
            else {
                keysOf(r).forEach(k => (r[k] = null));
                r.rule = RULES[0];
                r.ranges = null;
                rows = rows.filter(x => x.parent !== r.uid);
                markDirty();
                toast('Baris dikosongkan');
            }
            render();
            return;
        }
        const td = e.target.closest('td[data-r]');
        if (!td) return;
        if (editing) commit();
        const nr = +td.dataset.r,
            nc = +td.dataset.c,
            same = nr === act.r && nc === act.c && !e.shiftKey;
        act = { r: nr, c: nc };
        if (!e.shiftKey) anc = { ...act };
        wrap.focus({ preventScroll: true });
        e.preventDefault();
        if (same || e.detail >= 2) startEdit(undefined, false);
        else render();
    });
    xs.addEventListener('mouseover', e => {
        if (e.buttons !== 1 || editing) return;
        const td = e.target.closest('td[data-r]');
        if (!td) return;
        if (fill) {
            const to = Math.max(fill.r1, +td.dataset.r);
            if (to !== fill.to) {
                fill.to = to;
                render();
            }
            return;
        }
        act = { r: +td.dataset.r, c: +td.dataset.c };
        render();
    });
    wrap.addEventListener('contextmenu', e => {
        const td = e.target.closest('td[data-r]');
        if (!td) return;
        e.preventDefault();
        act = { r: +td.dataset.r, c: +td.dataset.c };
        anc = { ...act };
        render();
        const m = $('#ctx');
        m.hidden = false;
        m.style.left = Math.min(e.clientX, innerWidth - 240) + 'px';
        m.style.top = Math.min(e.clientY, innerHeight - 140) + 'px';
    });
    document.addEventListener('click', e => {
        if (!e.target.closest('#ctx')) $('#ctx').hidden = true;
    });
    $('#ctx').addEventListener('click', e => {
        const a = e.target.dataset.a;
        if (!a) return;
        $('#ctx').hidden = true;
        wrap.focus({ preventScroll: true });
        if (a === 'fill') fillDown();
        else if (a === 'clr') clearSel();
        else if (a === 'up') {
            const r = vis()[act.r];
            if (r) {
                copyFromAbove(r);
                render();
            }
        }
    });
    wrap.addEventListener('keydown', e => {
        if (editing) {
            if (e.key === 'Enter') {
                e.preventDefault();
                commit('down');
            } else if (e.key === 'Tab') {
                e.preventDefault();
                commit('right');
            } else if (e.key === 'Escape') {
                editing = null;
                render();
                wrap.focus();
            } else if ((e.key === 'ArrowDown' || e.key === 'ArrowUp') && !cols()[editing.c].list) {
                e.preventDefault();
                commit();
                mv(e.key === 'ArrowDown' ? 1 : -1, 0);
            }
            return;
        }
        const k = e.key;
        if (k === 'ArrowDown' || k === 'ArrowUp' || k === 'ArrowLeft' || k === 'ArrowRight') {
            e.preventDefault();
            mv(
                k === 'ArrowDown' ? 1 : k === 'ArrowUp' ? -1 : 0,
                k === 'ArrowRight' ? 1 : k === 'ArrowLeft' ? -1 : 0,
                e.shiftKey,
            );
        } else if (k === 'Enter' || k === 'F2') {
            e.preventDefault();
            e.shiftKey ? mv(-1, 0) : startEdit(undefined, false);
        } else if (k === 'Tab') {
            e.preventDefault();
            mv(0, e.shiftKey ? -1 : 1);
        } else if (k === 'Delete' || k === 'Backspace') {
            e.preventDefault();
            clearSel();
        } else if ((e.ctrlKey || e.metaKey) && k.toLowerCase() === 'd') {
            e.preventDefault();
            fillDown();
        } else if (k.length === 1 && !e.ctrlKey && !e.metaKey && !e.altKey) {
            e.preventDefault();
            startEdit(k, false);
        }
    });
    wrap.addEventListener('focusout', e => {
        if (editing && e.target.id === 'ed')
            setTimeout(() => {
                if (editing && document.activeElement.id !== 'ed') commit();
            }, 0);
    });
    document.addEventListener('copy', e => {
        if (editing || !wrap.contains(document.activeElement)) return;
        const { r0, r1, c0, c1 } = range(),
            V = vis(),
            C = cols();
        const tx = [];
        for (let i = r0; i <= r1; i++) {
            const l = [];
            for (let j = c0; j <= c1; j++) {
                const c = C[j],
                    r = V[i];
                if (!r) continue;
                l.push(
                    c.k === 'tq'
                        ? totQ(r)
                        : c.k === 'val'
                          ? valOf(r)
                          : c.num
                            ? (r[c.k] ?? '')
                            : c.k === 'rule'
                              ? (r.parent ? '' : ruleBase(r))
                              : c.k === 'dest' && !r.parent && perD(r)
                                ? PER_DEST
                                : label(c.k, r[c.k]),
                );
            }
            tx.push(l.join('\t'));
        }
        e.clipboardData.setData('text/plain', tx.join('\n'));
        e.preventDefault();
        toast(`${(r1 - r0 + 1) * (c1 - c0 + 1)} sel disalin`);
    });
    document.addEventListener('paste', e => {
        if (editing || !wrap.contains(document.activeElement)) return;
        e.preventDefault();
        if (lockedNotice()) return;
        const lines = e.clipboardData
            .getData('text')
            .replace(/\r/g, '')
            .replace(/\n$/, '')
            .split('\n')
            .map(l => l.split('\t'));
        const V = vis(),
            C = cols();
        pasted.clear();
        let n = 0,
            bad = 0;
        lines.forEach((l, i) =>
            l.forEach((v, j) => {
                const r = V[act.r + i],
                    c = C[act.c + j];
                if (!r || !c || !editable(r, c)) return;
                if (setVal(r, c, v)) {
                    n++;
                    pasted.add(r.uid + '|' + c.k);
                } else bad++;
            }),
        );
        if (n) markDirty();
        anc = {
            r: Math.min(V.length - 1, act.r + lines.length - 1),
            c: Math.min(C.length - 1, act.c + lines[0].length - 1),
        };
        render();
        toast(`${n} sel ditempel` + (bad ? ` · ${bad} tidak cocok dengan daftar` : ''));
        setTimeout(() => {
            pasted.clear();
            render();
        }, 2500);
    });
    document.querySelector('#form-bom').addEventListener('click', e => {
        const img = e.target.closest('[data-img]');
        if (img) {
            imgAt = +img.dataset.img;
            renderImages();
            return;
        }
        const t = e.target.closest('button, a[data-t]');
        if (!t) return;
        if (t.matches('a')) e.preventDefault();
        if (t.id === 'bom-image-prev' || t.id === 'bom-image-next') {
            imgAt = Math.max(0, Math.min(IMAGES.length - 1, imgAt + (t.id === 'bom-image-next' ? 1 : -1)));
            renderImages();
            return;
        }
        if (t.dataset.mode) {
            setMode(t.dataset.mode);
            return;
        }
        if (t.dataset.cut) {
            if (lockedNotice()) return;
            const [id, i] = t.dataset.cut.split('|');
            const b = rows.find(x => x.uid == id),
                z = SIZES[+i].id;
            const k = b.ranges.findIndex(a => a[a.length - 1] === z);
            if (k >= 0 && b.ranges[k + 1]) {
                b.ranges[k] = b.ranges[k].concat(b.ranges[k + 1]);
                b.ranges.splice(k + 1, 1);
            } else {
                const k2 = b.ranges.findIndex(a => a.includes(z)),
                    a = b.ranges[k2],
                    p = a.indexOf(z);
                b.ranges.splice(k2, 1, a.slice(0, p + 1), a.slice(p + 1));
            }
            markDirty();
            render();
            return;
        }
        if (t.dataset.t) {
            if (editing) commit();
            if (t.dataset.t === 'DOCS') {
                if (mode !== 'pv') setMode('pv');
                tab = 'DOCS';
                renderTabs();
                renderPv();
                return;
            }
            tab = t.dataset.t;
            act = { r: 0, c: 0 };
            anc = { ...act };
            editing = null;
            renderTabs();
            mode === 'pv' ? renderPv() : render();
        }
    });
    document.addEventListener('change', e => {
        if (e.target.id === 'pvAll') renderPv();
    });
    // Tombol Hapus = kosongkan isian baris yang dicentang (centang header = semua baris tab ini).
    window.bomUpdClear = function () {
        const btn = document.getElementById('bClear');
        if (!btn) return;
        let n = 0;
        try { n = (rows || []).filter(r => chk.has(r.uid)).length; } catch (err) { n = 0; }
        btn.disabled = n === 0;
        btn.innerHTML = '<i class="fas fa-trash-alt"></i> Hapus' + (n ? ' (' + n + ')' : '');
    };
    $('#bClear').addEventListener('click', () => {
        if (lockedNotice()) return;
        const list = rows.filter(r => chk.has(r.uid));
        if (!list.length) { toast('Centang baris yang mau dihapus dulu'); return; }
        Swal.fire({
            icon: 'warning',
            title: `Hapus isian ${list.length} baris?`,
            text: 'Item, cons, unit, loss, supplier, price, dan Rule BOM di baris yang dicentang dikosongkan. Belum tersimpan sebelum klik Save.',
            showCancelButton: true,
            confirmButtonText: 'Delete',
            cancelButtonText: 'Cancel',
        }).then(res => {
            if (!res.isConfirmed) return;
            editing = null;
            const parents = new Set(list.filter(r => !r.parent).map(r => r.uid));
            rows = rows.filter(r => !(r.parent && parents.has(r.parent)));
            rows.forEach(r => {
                if (!chk.has(r.uid)) return;
                keysOf(r).forEach(k => (r[k] = null));
                if (!r.parent) { r.rule = RULES[0]; r.ranges = null; }
            });
            chk.clear();
            markDirty();
            mode === 'pv' ? renderPv() : render();
            window.bomUpdClear();
            toast(`${list.length} baris dikosongkan`);
        });
    });
    window.bomUpdClear();
    $('#pvXls').addEventListener('click', () => {
        const css = `\u003cstyle>
        table,th,td{font-family:Calibri,Arial,sans-serif;font-size:11pt;}
        th{font-weight:bold;background:#f0f0f0;}
    \u003c/style>`;

        const doc = `\u003chtml>\u003chead>\u003cmeta charset="utf-8">${css}\u003c/head>\u003cbody>\u003ctable border="1">${$('#pv').innerHTML}\u003c/table>\u003c/body>\u003c/html>`;

        const a = document.createElement('a');
        a.href = URL.createObjectURL(new Blob([doc], { type: 'application/vnd.ms-excel' }));
        a.download = `BOM ${PAGE.header.no_ws || ''}`.trim().replace(/[\\/:*?"<>|]+/g, '-') + '.xls';
        document.body.appendChild(a);
        a.click();
        a.remove();
    });

    // ===== simpan
    function payload() {
        const pickV = r => ({ id: r.dbid, item: r.item, cons: r.cons, unit: r.unit, sup: r.sup, price: r.price });
        return {
            rows: rows
                .filter(r => !isOth(r) && !r.parent)
                .map(r => ({
                    ...pickV(r),
                    cm: r.cm,
                    rule: r.rule,
                    dest: r.dest,
                    loss: r.loss,
                    subs: rows
                        .filter(k => k.parent === r.uid)
                        .map(k => ({
                            color: k.color,
                            sfrom: k.sizes ? k.sizes[0] : null,
                            sto: k.sizes ? k.sizes[k.sizes.length - 1] : null,
                            ...Object.fromEntries(SUB_KEYS.map(x => [x, k[x]])),
                        })),
                })),
            charges: rows.filter(isOth).map(r => ({ ...pickV(r), cc: r.cc })),
        };
    }
    let saving = false;
    // Simpan BOM WS yang sedang dibuka; true kalau berhasil (dipakai juga saat pindah WS).
    // toListing: dari tombol Save -> setelah berhasil kembali ke listing Costing.
    async function saveBom(toListing = false) {
        if (saving) return false;
        if (editing) commit();
        saving = true;
        const btn = $('#bSave');
        btn.disabled = true;
        btn.textContent = 'Menyimpan...';
        Swal.fire({ title: 'Menyimpan BOM...', text: 'Mohon tunggu, jangan tutup halaman ini.', allowOutsideClick: false, allowEscapeKey: false, didOpen: () => Swal.showLoading() });
        try {
            let res, body;
            try {
                res = await fetch(SAVE_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': @json(csrf_token()),
                    },
                    body: JSON.stringify({ ...payload(), to_listing: toListing }),
                });
                body = await res.json().catch(() => ({}));
            } catch (_) {
                Swal.fire({ icon: 'error', title: 'BOM belum tersimpan', text: 'Koneksi ke server terputus. Klik Save lagi.' });
                return false;
            }
            if (!res.ok || (body.status && body.status !== 200)) {
                Swal.fire({
                    icon: 'error',
                    title: 'BOM belum tersimpan',
                    text: body.message || (body.errors ? Object.values(body.errors)[0][0] : 'Server belum bisa menyimpan BOM ini. Klik Save lagi.'),
                });
                return false;
            }
            if (body.redirect) {
                dirty = false; // supaya beforeunload tidak bertanya
                location.href = body.redirect;
                return true;
            }
            Swal.close(); // tutup loading; notifikasi sukses BOM-04 berupa toast
            PAGE = { ...body.data, masters: body.data.masters || PAGE.masters };
            loadMasters();
            loadRows();
            dirty = false;
            $('#dirty').hidden = true;
            renderHeader();
            renderStale();
            renderTabs();
            mode === 'pv' ? renderPv() : render();
            renderLock();
            mktAlert('BOM-04');
            return true;
        } catch (err) {
            // Data sudah tersimpan di server; yang gagal hanya menampilkan ulang tabel.
            console.error(err);
            Swal.fire({ icon: 'warning', title: 'BOM sudah tersimpan', text: 'Tampilan tabel belum ter-update. Muat ulang halaman untuk melihat data terbaru.', confirmButtonText: 'Muat ulang' }).then(() => location.reload());
            return true;
        } finally {
            saving = false;
            btn.disabled = false;
            btn.textContent = 'Save';
        }
    }
    // ===== Approval BOM per WS (PAGE.lock dari MarketingNdsApprovalService::state): Approved = terkunci, Request Unlock.
    function bomLocked() {
        return !!(PAGE && PAGE.lock && !PAGE.lock.can_edit);
    }
    // Guard semua jalur ubah data (hapus sel, fill, paste, potong size, clear): BOM approved harus Request Unlock dulu.
    function lockedNotice() {
        if (!bomLocked()) return false;
        toast(PAGE.lock.state === 'requested' ? 'Request unlock BOM masih menunggu Marketing Manager' : 'BOM sudah di-approve. Klik Request Unlock untuk mengubahnya');
        return true;
    }
    function renderLock() {
        const lock = (PAGE && PAGE.lock) || { state: 'editable', can_edit: true };
        const locked = !lock.can_edit;
        document.getElementById('form-bom').classList.toggle('bom-locked', locked);
        ['bSave', 'bCancel', 'bClear'].forEach(id => { const el = document.getElementById(id); if (el) el.hidden = locked; });
        const box = document.getElementById('bom-lock-banner');
        if (!box) return;
        // esc dari atas ($ di halaman ini = querySelector, bukan jQuery).
        const fmt = d => d ? new Date(String(d).replace(' ', 'T')).toLocaleString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : '-';
        const html = {
            locked: `<div class="bom-lock-alert"><span><i class="fas fa-lock"></i> BOM ini sudah di-approve dan tidak bisa diubah. Ajukan Request Unlock ke Marketing Manager kalau perlu mengubahnya.</span><button type="button" class="mkt-btn mkt-btn--navy mkt-btn--sm" id="bom-request-unlock">Request Unlock</button></div>`,
            requested: `<div class="bom-lock-alert is-wait"><span><i class="far fa-clock"></i> Request unlock dikirim ${fmt(lock.request && lock.request.dateinput)} (${esc(lock.request && lock.request.reason)}). Menunggu keputusan Marketing Manager.</span></div>`,
            unlocked: `<div class="bom-lock-alert is-open"><span><i class="fas fa-lock-open"></i> BOM di-unlock oleh ${esc((lock.unlock && lock.unlock.approved_by) || 'Marketing Manager')}. Setelah Save, BOM menunggu approval lagi (Rev ${lock.rev}).</span></div>`,
            submitted: `<div class="bom-lock-alert is-wait"><span><i class="far fa-clock"></i> Revisi BOM ini menunggu approval Marketing Manager. BOM masih bisa diubah.</span></div>`,
        }[lock.state] || '';
        box.innerHTML = html;
    }
    document.addEventListener('click', e => {
        if (!e.target.closest('#bom-request-unlock')) return;
        openUnlockRequest({
            type: 'bom',
            id: PAGE.lock.bom_id,
            number: PAGE.header.bom_no,
            buyer: PAGE.header.buyer,
            onSuccess: () => openWs(location.href, false),
        });
    });

    // Konfirmasi SV-04: jumlah baris material (baris induk yang item-nya terisi) dan No WS yang dibuka.
    $('#bSave').addEventListener('click', () => {
        if (saving) return;
        if (editing) commit();
        const count = rows.filter(r => !r.parent && !isOth(r) && r.item).length;
        mktAlert('SV-04', { count: count, ws: PAGE.header.no_ws || '-' }).then(r => {
            if (r.isConfirmed) saveBom(true);
        });
    });

    // ===== pindah WS tanpa reload halaman
    let switching = false;
    async function openWs(url, push = true) {
        if (switching) return;
        switching = true;
        const picker = document.querySelector('.bom-ws-picker');
        picker && picker.classList.add('is-loading');
        try {
            const res = await fetch(url, { headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
            if (!res.ok) throw new Error();
            const body = await res.json();
            PAGE = { ...body.page, masters: body.page.masters || PAGE.masters };
            SAVE_URL = body.save_url;
            editing = null;
            pasted = new Set();
            act = { r: 0, c: 0 };
            anc = { ...act };
            if (tab === 'DOCS') tab = 'FAB';
            loadMasters();
            loadRows();
            dirty = false;
            $('#dirty').hidden = true;
            renderHeader();
            renderStale();
            renderBO();
            renderTabs();
            mode === 'pv' ? renderPv() : render();
            renderLock();
            document.querySelectorAll('.bom-ws-picker a[data-line]').forEach(a => {
                const on = a.dataset.line == body.line_id;
                a.classList.toggle('mkt-btn--navy', on);
                a.classList.toggle('mkt-btn--gray', !on);
            });
            document.querySelectorAll('[data-bom-title]').forEach(el => {
                el.textContent = body.is_edit ? el.dataset.edit : el.dataset.create;
            });
            if (push) history.pushState({ bomLine: body.line_id }, '', url);
        } catch (_) {
            location.href = url; // gagal ambil data: buka biasa
        } finally {
            switching = false;
            picker && picker.classList.remove('is-loading');
        }
    }
    document.addEventListener('click', async e => {
        const a = e.target.closest('.bom-ws-picker a[data-line]');
        if (!a || e.ctrlKey || e.metaKey || e.shiftKey || e.button !== 0) return;
        e.preventDefault();
        if (a.classList.contains('mkt-btn--navy')) return;
        if (editing) commit();
        if (dirty) {
            const r = await Swal.fire({
                icon: 'warning',
                title: 'Ada perubahan yang belum disimpan',
                text: 'Perubahan di BOM ini hilang kalau pindah worksheet tanpa disimpan. Simpan dulu, atau pindah tanpa menyimpan.',
                showDenyButton: true,
                showCancelButton: true,
                confirmButtonText: 'Save',
                denyButtonText: 'Pindah tanpa simpan',
                cancelButtonText: 'Cancel',
            });
            if (r.isDismissed) return;
            if (r.isConfirmed) {
                saveBom(); // simpan saja, tetap di WS ini
                return;
            }
        }
        openWs(a.href);
    });
    window.addEventListener('popstate', () => {
        dirty = false; // tombol back/forward browser: ikut URL
        openWs(location.href, false);
    });
    $('#bCancel').addEventListener('click', () => {
        if (!dirty) {
            toast('Tidak ada perubahan');
            return;
        }
        Swal.fire({
            icon: 'warning',
            title: 'Buang perubahan ini?',
            text: 'Isi tabel kembali ke data yang terakhir disimpan.',
            showCancelButton: true,
            confirmButtonText: 'Delete',
            cancelButtonText: 'Cancel',
        }).then(r => {
            if (!r.isConfirmed) return;
            editing = null;
            loadRows();
            dirty = false;
            $('#dirty').hidden = true;
            renderTabs();
            mode === 'pv' ? renderPv() : render();
        });
    });
    window.addEventListener('beforeunload', e => {
        if (dirty) {
            e.preventDefault();
            e.returnValue = '';
        }
    });

    loadMasters();
    loadRows();
    renderHeader();
    renderStale();
    renderImages();
    renderBO();
    renderTabs();
    render();
    renderLock();
})();
</script>
@endif
@endsection
