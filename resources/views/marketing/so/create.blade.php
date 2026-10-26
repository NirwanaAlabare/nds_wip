@extends('layouts.index')

@section('custom-link')
    <link rel="stylesheet" href="{{ asset('dist/css/marketing-form.css') }}?v={{ @filemtime(public_path('assets/dist/css/marketing-form.css')) }}">
    <link rel="stylesheet" href="{{ asset('dist/css/marketing-buttons.css') }}?v={{ @filemtime(public_path('assets/dist/css/marketing-buttons.css')) }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

<style>
    :root {
        --sb-navy: #0f172a;
        --sb-blue: #3085d6;
        --sb-blue-dark: #1e3a8a;
        --sb-border: #e2e8f0;
    }

    /* ============ CARD UTAMA & CONTAINER ============ */
    .card-sb {
        border: none;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 5px 12px -3px rgba(30, 41, 59, .18), 0 2px 4px rgba(30, 41, 59, .07);
    }

    .card-sb > .card-header {
        background: #fff !important;
        border: none;
        padding: 1.2rem 1.4rem .4rem;
        position: relative;
    }

    .card-sb > .card-header::before {
        content: none;
    }

    .card-sb > .card-header .card-eyebrow {
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 1.4px;
        text-transform: uppercase;
        color: #64748b;
        display: none;
        margin-bottom: 4px;
    }

    .card-sb > .card-header .card-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: #294d68 !important;
        margin: 0;
    }

    /* Preview card header khusus (warning) */
    .card-sb > .card-header.bg-warning {
        background: linear-gradient(135deg, #f0ad4e, #d9822b) !important;
    }
    .card-sb > .card-header.bg-warning::before {
        background: repeating-linear-gradient(180deg, #fff3cd 0px, #fff3cd 10px, transparent 10px, transparent 20px);
    }
    .card-sb > .card-header.bg-warning .card-title {
        color: white !important;
    }

    .card-sb > .card-body {
        background: #fff;
        padding: 1rem 1.4rem 1.4rem;
    }

    .card-footer.text-right {
        border-top: 1px solid #eef1f5;
        background: #fff;
        padding: 1rem 2rem;
    }
    .card-footer .btn {
        border-radius: 8px;
        font-weight: 700;
    }
    .btn-success { background: linear-gradient(135deg, #22c55e, #15803d); border: none; }

    #card-preview {
        display: none;
        margin: 0 1.4rem 1.4rem;
        width: calc(100% - 2.8rem);
        border-radius: 12px;
        box-shadow: 0 5px 12px -3px rgba(30, 41, 59, .18), 0 2px 4px rgba(30, 41, 59, .07);
    }
    #card-preview .card-header { background: #294d68 !important; }
    #card-preview.so-preview-section { margin: 0 1.4rem 1.4rem; width: auto; border: 0; border-radius: 8px; background: #fff; box-shadow: 0 4px 8px -2px rgba(30, 41, 59, .16), 0 1px 2px rgba(30, 41, 59, .06); }
    .so-preview-body { padding: 12px; border-radius: 0; background: #fff; box-shadow: none; }
    .so-preview-pager { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 10px; padding: 12px 4px 2px; font-family: Inter, sans-serif; font-size: 12px; color: #1e293b; }
    .so-preview-pager .dataTables_length label { display: inline-flex; align-items: center; gap: 10px; margin: 0; font-weight: 400; }
    .so-preview-pager .dataTables_length select { width: auto; height: 32px; min-width: 64px; border: 1px solid #94a3b8; border-radius: 6px; font-size: 13px; }
    .so-preview-pager .pagination { margin: 0; gap: 6px; }
    .so-preview-pager .page-link { min-width: 28px; height: 28px; padding: 0 6px; border: 0; border-radius: 4px; background: transparent; color: #64748b; font-size: 13px; line-height: 28px; text-align: center; }
    .so-preview-pager .page-item.active .page-link { background: #274b63; color: #fff; font-weight: 700; }
    .so-preview-pager .page-item.disabled .page-link { opacity: .35; }
    #card-preview .card-body {
        max-height: none;
        overflow: visible;
        padding: 0 1rem 1rem;
    }
    #card-preview .table-responsive { overflow: visible; }
    #card-preview .so-preview-table-wrap { overflow-x: auto; padding: 8px 10px 0; border: 1px solid #edf1f5; border-radius: 8px; background: #fff; }

    /* ============ FORM & INPUT STYLING ============ */
    .form-section-title {
        font-size: 13px;
        font-weight: 700;
        color: var(--sb-navy);
        text-transform: uppercase;
        letter-spacing: 0.4px;
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 1.1rem;
        padding-bottom: 0.7rem;
        border-bottom: 1px solid #eef1f5;
    }
    .form-section-title i { color: var(--sb-blue); font-size: 13px; }

    .form-group label {
        font-size: 12.5px;
        font-weight: 700;
        color: #475569;
        margin-bottom: 0.3px;
    }

    .form-control,
    .select2-container--bootstrap4 .select2-selection {
        height: 42px;
        min-height: 42px;
        border-radius: 8px;
        border: 1.5px solid var(--sb-border);
        font-size: 12px;
    }
    .form-control:focus,
    .select2-container--bootstrap4.select2-container--focus .select2-selection {
        border-color: var(--sb-blue) !important;
        box-shadow: 0 0 0 3px rgba(48, 133, 214, 0.12) !important;
    }

    .form-section.form-section-upload {
        background: #f4f8fd;
        border-color: #dbe7f7;
    }

    .upload-preview-box {
        border: 1.5px dashed #cbd5e1;
        border-radius: 12px;
        background: #ffffff;
        min-height: 180px;
        display: flex;
        justify-content: center;
        align-items: center;
        overflow: hidden;
    }

    /* ============ TABLE PREVIEW ============ */
    #table-preview-so {
        width: 100%;
        min-width: 100%;
        table-layout: fixed;
        border-collapse: separate;
        border-spacing: 0;
        border: none !important;
        margin-bottom: 0;
    }

    #table-preview-so thead th,
    #table-preview-so tbody td,
    #table-preview-so tfoot th {
        box-sizing: border-box;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        vertical-align: middle;
    }

    #table-preview-so thead th {
        background-color: #294d68 !important;
        color: #ffffff !important;
        text-align: center !important;
        border: none !important;
        font-size: 11.5px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        padding: 10px 8px;
    }

    #table-preview-so tbody td {
        padding: 8px 10px;
        font-size: 12px;
    }

    #table-preview-so tfoot th {
        padding: 10px 8px;
        font-size: 11.5px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        background: #ffe0e0;
        border-top: 0;
    }

    #table-preview-so tbody td.preview-col-qty,
    #table-preview-so tfoot th.preview-col-qty,
    #table-preview-so tbody td.preview-col-total,
    #table-preview-so tfoot th.preview-col-total {
        text-align: right;
        font-variant-numeric: tabular-nums;
    }

    #table-preview-so .preview-col-total {
        background-color: #dc3545 !important;
        color: #ffffff !important;
    }

    #table-preview-so thead th:first-child {
        border-top-left-radius: 8px;
        border-bottom-left-radius: 8px;
    }

    /* #table-preview-so thead th:last-child {
        border-top-right-radius: 8px;
        border-bottom-right-radius: 8px;
    } */

    /* ============ CUSTOM COMPONENTS / SO LAYOUT ============ */
    .so-page-heading { display: flex; justify-content: space-between; align-items: flex-start; gap: 18px; margin-bottom: 14px; }
    .so-create-body { font-family: 'Poppins', sans-serif; }
    .so-breadcrumb { margin-bottom: 18px; padding: 8px 10px; border-radius: 4px; background: #284A63; color: #b4b4b4; font-size: 16px; font-weight: 500; }
    .so-breadcrumb a { color: #b4b4b4; text-decoration: none; }
    .so-breadcrumb a:hover { color: #fff; text-decoration: underline; }
    .so-breadcrumb strong { color: #fff; font-weight: 500; }
    .so-page-heading h1 { margin: 0; color: #284a63; font-size: 32px; font-weight: 700; letter-spacing: 0; }
    .so-page-heading h1 i { font-size: 11px; vertical-align: middle; }
    .so-page-heading p { margin: 4px 0 0; color: #284a63; font-size: 12px; font-weight: 600; }
    .so-page-heading p i { margin-right: 6px; color: #a3b2c7; }
    .so-costing-label { color: #64748b; font-size: 12px; font-weight: 700; }
    .so-bom-picker .form-control, .so-bom-picker .btn { height: 36px; font-size: 12px; }
    .so-source-fields { display: none !important; }
    .so-summary-cards { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 12px; margin: 0 0 16px; }
    .so-summary-card { min-width: 0; min-height: 76px; padding: 10px 14px; border: 1px solid #e1e7ef; border-radius: 10px; background: #fff; box-shadow: 0 3px 8px rgba(30, 41, 59, .10); }
    .so-summary-card > span { display: block; margin-bottom: 4px; color: #9e9e9e; font-family: 'Poppins', sans-serif; font-size: 20px; font-weight: 500; letter-spacing: 0.2px; }
    .so-summary-card > strong { display: block; overflow: hidden; color: #284a63; font-family: 'Poppins', sans-serif; font-size: 24px; font-weight: 700; letter-spacing: 0.05em; text-overflow: ellipsis; white-space: nowrap; }
    .so-summary-card.is-navy { border-color: #294d68; background: #294d68; color: #fff; }
    .so-summary-card.is-navy > span, .so-summary-card.is-navy > strong { color: #fff; }
    .so-summary-card.is-orange { border-color: #ffad2f; background: #ffad2f; }
    .so-summary-card.is-orange > span, .so-summary-card.is-orange > strong { color: #fff; }
    .so-summary-card.is-blue { border-color: #49a8f4; background: #49a8f4; }
    .so-summary-card.is-blue > span, .so-summary-card.is-blue > strong { color: #fff; }
    .so-top-layout { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 14px; align-items: stretch; margin-bottom: 14px; }
    .so-info-column { display: flex; min-width: 0; flex-direction: column; gap: 12px; }
    .so-overview-section { min-width: 0; overflow: hidden; border: 1px solid #e1e7ef; border-radius: 10px; background: #fff; box-shadow: 0 3px 8px rgba(30, 41, 59, .10); }
    .so-create-body .so-overview-title,
    .so-create-body .so-summary-item,
    .so-create-body .so-summary-item > span,
    .so-create-body .so-summary-item strong,
    .so-create-body .so-image-caption,
    .so-create-body .so-image-counter { font-family: 'Poppins', sans-serif; }
    .so-overview-title { margin: 0 12px; padding: 9px 0 7px; border-bottom: 1px solid #dce4ec; color: #616161; font-size: 16px; font-weight: 500; }
    #so-information-title, .so-information-title { font-family: 'Poppins', sans-serif; font-size: 16px; font-weight: 700; letter-spacing: 0.05em; }
    .so-overview-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); column-gap: 22px; padding: 8px 12px 10px; }
    .so-summary-item { display: flex; min-width: 0; min-height: 69px; flex-direction: column; justify-content: center; gap: 9px; padding: 3px 0; border-bottom: 1px solid #e7e7e7; color: #3b3336; font-size: 12px; }
    .so-summary-item > span { overflow: hidden; color: #9e9e9e; font-size: 18px; font-weight: 400; text-overflow: ellipsis; white-space: nowrap; }
    .so-summary-item strong { overflow: hidden; color: #284a63; font-size: 18px; font-weight: 600; text-overflow: ellipsis; white-space: nowrap; }
    .so-review-section { display: flex; min-height: 100%; flex-direction: column; }
    .so-review-section .so-image-review { flex: 1; }
    .so-image-thumbs { display: flex; flex-wrap: wrap; justify-content: center; gap: 10px; margin-top: 12px; }
    .so-image-thumbs:empty { display: none; }
    .so-image-thumbs button { width: 76px; height: 97px; padding: 3px; border: 1px solid #dce4ec; border-radius: 6px; background: #fff; cursor: pointer; }
    .so-image-thumbs button.is-active { border: 2px solid #1d6fd8; }
    .so-image-thumbs img { width: 100%; height: 100%; object-fit: contain; }
    .so-image-caption { margin: 6px 0 12px; color: #9aa5ad; font-size: 12px; text-align: center; }
    .so-image-caption:empty { display: none; }
    /* Shipping & Destinations selebar penuh, 6 kolom dalam satu baris. */
    .so-shipping-section { margin-bottom: 14px; }
    .so-overview-grid.so-shipping-grid { grid-template-columns: repeat(6, minmax(0, 1fr)); }
    @media (max-width: 1199.98px) { .so-overview-grid.so-shipping-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
    @media (max-width: 575.98px) { .so-overview-grid.so-shipping-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    .so-section {
        margin-bottom: 14px;
        overflow: hidden;
        border: 1px solid #284a63;
        border-radius: 8px;
        background: #fff;
        box-shadow: 0 4px 8px -2px rgba(30, 41, 59, .16), 0 1px 2px rgba(30, 41, 59, .06);
    }

    .so-section-toggle {
        display: flex; align-items: center; justify-content: space-between; width: 100%;
        padding: 8px 16px; border: 0; border-radius: 0;
        background: #fff; color: #284a63; text-align: left; font-family: 'Poppins', sans-serif; font-size: 25px; font-weight: 500;
    }
    .so-section-toggle:focus { outline: 0; box-shadow: 0 0 0 2px rgba(48, 133, 214, .12); }
    .so-section > .collapse.show { border-top: 1px solid #edf1f5; }
    .so-section.so-heading-only { overflow: hidden; border: 0; border-radius: 8px; background: #fff; box-shadow: 0 4px 8px -2px rgba(30, 41, 59, .16), 0 1px 2px rgba(30, 41, 59, .06); }
    .so-section.so-heading-only .so-section-toggle { border: 1px solid #284a63; border-radius: 6px; }
    .so-section.so-heading-only > .collapse.show { border-top: 0; }

    .so-chevron { display: inline-flex; width: 20px; height: 20px; align-items: center; justify-content: center; border-radius: 5px; background: #aabac5; color: #fff; font-size: 10px; }
    .so-chevron i { transition: transform .2s ease; }
    .so-section-toggle[aria-expanded="false"] .so-chevron i { transform: rotate(180deg); }

    .so-edit-grid {
        display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); column-gap: 18px;
        padding: 14px 12px 4px; border-radius: 0; background: #fff;
    }
    .so-edit-grid .form-group { min-width: 0; margin-bottom: 12px; }
    .so-edit-grid label { display: block; min-height: 27px; margin-bottom: 5px; color: #34282c; font-size: 16px; }
    .so-create-body .so-edit-grid label:not(.form-check-label):not(.custom-file-label) { font-weight: 400; }
    .so-edit-grid .form-control,
    .so-edit-grid .select2-container--bootstrap4 .select2-selection { height: 59px; min-height: 59px; border: 1px solid #e1e5e9; border-radius: 12px; box-shadow: none; font-family: 'Poppins', sans-serif; font-size: 14px; font-weight: 500; line-height: 1; color: #000; }
    .so-edit-grid .form-control { padding: 0 16px; }
    .so-edit-grid .form-control::placeholder { color: #ababab; }
    .so-edit-grid .form-control:focus,
    .so-edit-grid .select2-container--bootstrap4.select2-container--focus .select2-selection,
    .so-edit-grid .select2-container--bootstrap4.select2-container--open .select2-selection { border-color: #284a63 !important; box-shadow: none !important; }
    .so-edit-grid .select2-container--bootstrap4 .select2-selection--single { display: flex; align-items: center; padding: 0 44px 0 16px; }
    .so-edit-grid .select2-container--bootstrap4 .select2-selection__rendered { padding: 0; color: #000; font-size: 14px; font-weight: 500; line-height: 1; }
    .so-edit-grid .select2-container--bootstrap4 .select2-selection__placeholder { color: #ababab; }
    .so-edit-grid .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow { top: 50%; right: 16px; width: 12px; height: 12px; transform: translateY(-50%); }
    .so-edit-grid .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow b { top: 50%; left: 50%; width: 8px; height: 8px; margin: -6px 0 0 -4px; border: solid #284a63; border-width: 0 1.5px 1.5px 0; transform: rotate(45deg); }
    .so-edit-grid .select2-container--bootstrap4.select2-container--open .select2-selection--single .select2-selection__arrow b { margin-top: -2px; transform: rotate(-135deg); }
    /* State 2: ada isi + hover/fokus = border navy dan tanda X untuk menghapus pilihan (allowClear), menggantikan chevron.
       State 3: sudah terpilih & diam = border abu + chevron. */
    .so-edit-grid .form-group .select2-container--bootstrap4 .select2-selection { position: relative; }
    .so-edit-grid .form-group .select2-container--bootstrap4 .select2-selection__clear { display: none; position: absolute; top: 50%; right: 14px; z-index: 2; float: none; width: 18px; height: 18px; margin: 0; padding: 0; transform: translateY(-50%); color: #284a63; font-size: 20px; font-weight: 400; line-height: 18px; text-align: center; cursor: pointer; }
    .so-edit-grid .form-group .select2-container--bootstrap4:not(.select2-container--disabled):is(:hover, .select2-container--focus, .select2-container--open) .select2-selection__clear { display: block; }
    .so-edit-grid .form-group .select2-container--bootstrap4:not(.select2-container--disabled):is(:hover, .select2-container--focus, .select2-container--open) .select2-selection--single:has(.select2-selection__clear) .select2-selection__arrow { display: none; }
    .so-edit-grid .form-group .select2-container--bootstrap4:not(.select2-container--disabled):hover .select2-selection:has(.select2-selection__clear) { border-color: #284a63; }
    /* State 4: disabled / readonly / view only = label ikut pudar. */
    .so-edit-grid .form-group:has(.select2-container--disabled) label, .so-edit-grid .form-group:has(.form-control:disabled) label, .so-edit-grid .form-group:has(.form-control[readonly]) label { color: #c4c4c4; }
    /* Disabled/readonly: teks & panah pudar. */
    .so-edit-grid .form-control[readonly],
    .so-edit-grid .form-control:disabled,
    .so-edit-grid .select2-container--bootstrap4.select2-container--disabled .select2-selection { background-color: #f8f8f8; border-color: #e9ecef; color: #ababab; }
    .so-edit-grid .select2-container--bootstrap4.select2-container--disabled .select2-selection__rendered { color: #ababab; }
    .so-edit-grid .select2-container--bootstrap4.select2-container--disabled .select2-selection__arrow b { border-color: #c4c4c4; }
    /* Error: tambahkan class .is-invalid pada .form-group (label, border, teks, panah merah + "Required"). */
    .so-edit-grid .form-group.is-invalid label { color: #c8161d; }
    .so-edit-grid .form-group.is-invalid .form-control,
    .so-edit-grid .form-group.is-invalid .select2-container--bootstrap4 .select2-selection { border-color: #c8161d !important; color: #c8161d; }
    .so-edit-grid .form-group.is-invalid .select2-container--bootstrap4 .select2-selection__rendered,
    .so-edit-grid .form-group.is-invalid .select2-container--bootstrap4 .select2-selection__placeholder,
    .so-edit-grid .form-group.is-invalid .form-control::placeholder { color: #c8161d; }
    .so-edit-grid .form-group.is-invalid .select2-container--bootstrap4 .select2-selection__arrow b { border-color: #c8161d; }
    .so-edit-grid .form-group.is-invalid::after { content: 'Required'; display: block; margin-top: 4px; color: #c8161d; font-size: 13px; font-weight: 500; }
    .so-file-dropzone.is-invalid { border-color: #c8161d; color: #c8161d; }
    .so-file-dropzone.is-invalid::after { content: 'Required'; color: #c8161d; font-size: 13px; font-weight: 500; }

    /* Banner "N fields are empty" (sama seperti Costing). */
    .mkt-error-alert { display: flex; align-items: center; gap: 8px; margin-bottom: 16px; padding: 10px 12px; border-radius: 4px; background: #ff5f5f; color: #fff; font-family: Inter, sans-serif; font-size: 13px; }
    .mkt-error-alert[hidden] { display: none; }
    .mkt-error-alert > span { flex: 1; }
    .mkt-error-alert-close { flex: 0 0 auto; padding: 0; border: 0; background: none; color: #fff; font-size: 16px; line-height: 1; }

    .so-notes-field { grid-column: span 2; }

    .so-image-review { display: flex; min-height: 270px; align-items: center; justify-content: center; gap: 24px; padding: 12px; }
    .so-image-nav { width: 34px; height: 34px; border: 0; border-radius: 50%; color: #7b8791; background: #f1f3f5; }
    .so-image-preview { display: flex; min-width: 160px; min-height: 190px; align-items: center; justify-content: center; color: #64748b; font-size: 12px; }
    .so-image-preview img { max-width: min(100%, 330px); max-height: 250px; object-fit: contain; }
    .so-image-counter { min-width: 50px; color: #64748b; text-align: center; font-size: 11px; margin-bottom: 10px; }
    .so-overview-section .so-image-counter { margin-bottom: 12px; }

    .so-upload-panel { margin-top: 2px; padding: 18px 12px; border: 1px solid #d0d0d0; border-radius: 4px; }
    .so-upload-toolbar { display: flex; justify-content: flex-end; margin-bottom: 14px; }
    .so-template-btn { width: 350px; gap: 12px; border-radius: 32px; }
    .so-upload-toolbar .btn { border-radius: 24px; background: #284A63; border-color: #284A63; font-size: 18px; font-weight: 700; padding: 8px 22px; font-family: 'SF Pro Rounded', sans-serif; }

    .so-file-dropzone { display: flex; min-height: 105px; flex-direction: column; align-items: center; justify-content: center; gap: 4px; border: 2px dashed #294d68; border-radius: 4px; color: #555; cursor: pointer; font-size: 10px; }
    .so-file-dropzone > i { color: #294d68; font-size: 28px; margin-bottom: 4px; }
    .so-file-dropzone small { font-size: 9px; }
    .so-file-dropzone.dragging { background: #f1f7fc; }

    .so-selected-file { display: flex; align-items: center; gap: 12px; margin-top: 12px; padding: 9px 12px; border: 1px solid #d8dce0; border-radius: 8px; color: #607080; font-size: 11px; }
    .so-selected-file > i { color: #15803d; font-size: 17px; }
    .so-selected-file span { flex: 1; }
    .so-selected-file button { width: 22px; height: 22px; border: 1px solid #333; border-radius: 5px; background: #fff; }

    #btn-upload-so:not(.mkt-btn) { height: 38px; font-size: 11px; font-weight: 700; }

    /* ============ MEDIA QUERIES ============ */
    @media (max-width: 767px) {
        .so-page-heading { flex-direction: column; }
        .so-summary-cards { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px; }
        .so-top-layout { grid-template-columns: 1fr; }
        .so-review-section { min-height: 280px; }
        .so-edit-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 480px) {
        .so-summary-cards, .so-overview-grid, .so-edit-grid { grid-template-columns: 1fr; }
        .so-notes-field { grid-column: auto; }
    }
    /* table-preview-so font size nya kecilin dikit */
    #table-preview-so td, #table-preview-so th {
        font-size: 12px !important;
    }



    /* ============ DETAIL SO (baca-saja) ============ */
    .so-no-badge { display: inline-block; margin-left: 10px; padding: 3px 10px; border-radius: 12px; background: #e2e8f0; color: #284a63; font-size: 12px; font-weight: 600; vertical-align: middle; }
    .so-view .so-edit-grid .form-control,
    .so-view .so-edit-grid .select2-container--bootstrap4 .select2-selection { background: #f4f5f7 !important; cursor: default; }
    .so-view .so-edit-grid .select2-selection__arrow { display: none; }
    .so-lock-alert { display: flex; align-items: center; gap: 14px; margin-bottom: 16px; padding: 8px 12px; border-radius: 4px; background: #ff5f5f; color: #fff; font-size: 12px; }
    .so-lock-alert > span { flex: 1; }
    .so-lock-alert.is-unlocked { background: #198038; }
    .so-lock-alert > span i { margin-right: 6px; }
    .so-lock-alert-close { flex: 0 0 auto; padding: 0; border: 0; background: none; color: #fff; font-size: 18px; line-height: 1; }
    .so-summary-cards.is-four { grid-template-columns: repeat(4, minmax(0, 1fr)); }
    .so-detail-card { margin: 0 0 16px; padding: 12px 12px 4px; border-radius: 10px; background: #fff; box-shadow: 0 3px 10px rgba(15, 23, 42, .10); }
    .so-detail-scroll { overflow-x: auto; }
    .so-detail-scroll::-webkit-scrollbar { height: 8px; }
    .so-detail-scroll::-webkit-scrollbar-track { background: #e2e8f0; border-radius: 4px; }
    .so-detail-scroll::-webkit-scrollbar-thumb { background: #284a63; border-radius: 4px; }
    .so-detail-table { width: 100%; min-width: 900px; margin: 0; border-collapse: collapse; font-family: Inter, sans-serif; }
    .so-detail-table th { padding: 10px 8px; background: #cfe2ff; color: #1e293b; font-size: 12px; font-weight: 500; text-transform: uppercase; white-space: nowrap; }
    .so-detail-table td { padding: 9px 8px; border-bottom: 1px solid #edf1f5; color: #1e293b; font-size: 13px; vertical-align: top; }
    .so-detail-table .so-dt-check { width: 30px; text-align: center; }
    .so-detail-table tfoot td { height: 36px; background: #ffd6dc; border: 0; font-size: 14px; font-weight: 700; }
    .so-detail-pager { display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap; padding: 12px 4px; color: #1e293b; font-family: Inter, sans-serif; font-size: 12px; }
    .so-detail-pager > div:first-child { display: inline-flex; align-items: center; gap: 10px; }
    .so-detail-pager select { height: 32px; min-width: 64px; padding: 0 8px; border: 1px solid #94a3b8; border-radius: 6px; font-size: 13px; }
    .so-detail-pages { display: inline-flex; align-items: center; gap: 6px; }
    .so-detail-pages button { min-width: 28px; height: 28px; padding: 0 5px; border: 0; border-radius: 4px; background: transparent; color: #64748b; font-size: 13px; }
    .so-detail-pages button.active { background: #274b63; color: #fff; font-weight: 700; }
    .so-detail-pages button:disabled { opacity: .35; }
    @media (max-width: 991.98px) { .so-summary-cards.is-four { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 575.98px) { .so-summary-cards.is-four { grid-template-columns: 1fr; } }
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
    .mkt-cf-stage > img.mkt-cf-item { position: absolute; top: 50%; left: 50%; width: 182px; height: 256px; max-width: 46%; max-height: 256px; object-fit: contain; cursor: pointer; will-change: transform, opacity; transition: transform .55s cubic-bezier(.22, .61, .36, 1), opacity .55s ease; }
    .mkt-cf-stage > img.mkt-cf-item.is-active { cursor: default; }
    /* Gambar samping (sebelum/sesudah): 90x192, sejajar tengah dengan gambar aktif 182x256. */
    .mkt-cf-stage > img.mkt-cf-item.is-side { width: 90px; height: 192px; max-height: 192px; }
    .mkt-cf-stage > img.mkt-cf-item { transition-property: transform, opacity, width, height; }
    .mkt-cf-stage > img.mkt-cf-item.is-far { opacity: 0 !important; pointer-events: none; }
    .mkt-cf-dots { display: flex; justify-content: center; align-items: center; gap: 4px; margin-top: 10px; min-height: 4px; }
    .mkt-cf-dot { width: 6px; height: 4px; padding: 0; border: 0; border-radius: 2px; background: #d9dee5; cursor: pointer; transition: width .3s ease, background-color .3s ease; }
    .mkt-cf-dot.is-active { width: 16px; background: #1d6fd8; }
    @media (prefers-reduced-motion: reduce) { .mkt-cf-stage > img.mkt-cf-item { transition: none; } }
    .so-image-review .mkt-cf-stage { flex: 1; min-width: 0; }

    /* ============ RESPONSIVE (tablet & HP) — desktop >= 1200px tidak berubah ============ */
    @media (max-width: 1199.98px) {
        .so-summary-card > span { font-size: 16px; }
        .so-summary-card > strong { font-size: 18px; }
        .so-summary-item > span, .so-summary-item strong { font-size: 16px; }
        .so-edit-grid { column-gap: 14px; }
        .so-edit-grid label { font-size: 15px; }
    }
    @media (max-width: 991.98px) {
        /* 5 kartu: 3 di atas + 2 di bawah; 4 kartu (Detail SO): 2 per baris. */
        .so-summary-cards { grid-template-columns: repeat(6, minmax(0, 1fr)); gap: 10px; }
        .so-summary-cards > .so-summary-card { grid-column: span 2; }
        .so-summary-cards > .so-summary-card:nth-child(n+4) { grid-column: span 3; }
        .so-summary-cards.is-four { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .so-summary-cards.is-four > .so-summary-card { grid-column: auto; }
        .so-summary-card { min-height: 0; }
        .so-summary-card > span { font-size: 14px; }
        .so-summary-card > strong { font-size: 18px; overflow-wrap: anywhere; white-space: normal; }
        .so-top-layout { grid-template-columns: 1fr; }
        .so-review-section { min-height: 0; }
        .so-image-review { min-height: 0; }
        .so-summary-item > span { font-size: 14px; }
        .so-summary-item strong { font-size: 15px; white-space: normal; overflow-wrap: anywhere; }
        .so-edit-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .so-edit-grid label { min-height: 0; font-size: 14px; }
        .so-edit-grid .form-control,
        .so-edit-grid .select2-container--bootstrap4 .select2-selection { height: 48px; min-height: 48px; }
        .so-upload-toolbar .btn { font-size: 15px; }
    }
    @media (max-width: 575.98px) {
        .so-create-body.card-body { padding: .9rem .8rem 1rem; }
        .so-breadcrumb { font-size: 13px; }
        .so-page-heading h1 { font-size: 24px; }
        .so-summary-cards { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px; }
        .so-summary-cards > .so-summary-card, .so-summary-cards > .so-summary-card:nth-child(n+4) { grid-column: auto; }
        .so-summary-cards:not(.is-four) > .so-summary-card:last-child { grid-column: 1 / -1; }
        .so-summary-cards.is-four { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .so-summary-card { padding: 8px 10px; }
        .so-summary-card > span { font-size: 13px; }
        .so-summary-card > strong { font-size: 16px; }
        .so-overview-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); column-gap: 14px; }
        .so-overview-title, .so-section-toggle { font-size: 15px; }
        .so-summary-item > span { font-size: 13px; }
        .so-summary-item strong { font-size: 14px; }
        .so-image-review { gap: 8px; padding: 10px 6px; }
        .so-edit-grid { grid-template-columns: 1fr; }
        .so-notes-field { grid-column: auto; }
        .so-upload-toolbar { justify-content: stretch; }
        .so-template-btn { width: 100%; }
        .so-upload-toolbar .btn { padding: 8px 14px; font-size: 14px; }
        .so-lock-alert { flex-wrap: wrap; }
        .so-lock-alert > span { flex-basis: calc(100% - 30px); }
        .card-footer.text-right { padding: .9rem .8rem; }
        #card-preview { margin: 0 .8rem 1rem; width: calc(100% - 1.6rem); }
    }
    #so-information .so-summary-item:first-child > span { font-family: 'Poppins', sans-serif; font-size: 18px; font-weight: 500; letter-spacing: 0.2px; }
    #so-information .so-summary-item:first-child > strong { font-family: 'Poppins', sans-serif; font-size: 18px; font-weight: 700; letter-spacing: 0.05em; }
    #so-product-title, #so-shipping-title { font-family: 'Poppins', sans-serif; font-size: 16px; font-weight: 700; letter-spacing: 0.05em; }
    #so-product .so-summary-item > span, #so-shipping .so-summary-item > span { font-family: 'Poppins', sans-serif; font-size: 18px; font-weight: 500; letter-spacing: 0.2px; }
    #so-product .so-summary-item > strong, #so-shipping .so-summary-item > strong { font-family: 'Poppins', sans-serif; font-size: 18px; font-weight: 700; letter-spacing: 0.05em; }
    @media (max-width: 359.98px) {
        .so-summary-cards, .so-summary-cards.is-four, .so-overview-grid { grid-template-columns: 1fr; }
    }
    #table-preview-so { margin: 0 !important; font-family: Inter, sans-serif; }
    #table-preview-so thead th { padding: 9px 8px; border: 0 !important; background: #cfe2ff !important; color: #1e293b !important; font-size: 11px !important; font-weight: 600; text-transform: uppercase; white-space: nowrap; }
    #table-preview-so tbody td { padding: 8px; border: 0; border-bottom: 1px solid #edf1f5; color: #1e293b; font-size: 12px !important; background: #fff !important; box-shadow: none !important; }
    #table-preview-so tfoot th { padding: 9px 8px; border: 0 !important; background: #ffd6dc !important; color: #1e293b; font-size: 12px !important; font-weight: 700; }
    #table-preview-so thead th.preview-col-total { background: #cfe2ff !important; color: #1e293b !important; }
    #table-preview-so tfoot th.preview-col-total { background: #ffd6dc !important; color: #1e293b !important; }
    #table-preview-so tbody td.preview-col-total { background: #fff !important; color: #1e293b !important; font-weight: 700; }
    #table-preview-so .preview-col-action { width: 56px; text-align: center; }
    #table-preview-so .preview-row-delete { display: inline-flex; align-items: center; justify-content: center; width: 26px; height: 26px; padding: 0; border: 0; border-radius: 50%; background: #ff0000; color: #fff; font-size: 11px; box-shadow: 0 3px 6px rgba(255, 0, 0, .3); }
    #table-preview-so .preview-row-delete:hover { background: #d9dbe3; color: #ff0000; }
    #table-preview-so tbody tr:last-child td { border-bottom: 0; }
    #card-preview .so-preview-table-wrap::after { display: block; height: 4px; border-radius: 4px; background: #284a63; content: ''; }
    .so-preview-legend { display: flex; flex-wrap: wrap; gap: 14px; margin: 8px 4px 0; color: #64748b; font-family: Inter, sans-serif; font-size: 10px; }
    .so-preview-legend > span { display: inline-flex; align-items: center; gap: 4px; }
    .so-preview-legend i { width: 9px; height: 9px; border: 1px solid #cbd5e1; border-radius: 2px; background: #fff; }
    .so-preview-legend .is-auto { background: #f1f4f8; }
    .so-preview-legend .is-pasted { border-color: #93c5fd; background: #dbeafe; }
    .so-preview-legend .is-error { border-color: #fca5a5; background: #ffe4e6; }

    #so-header-fields .so-edit-grid {
        grid-template-columns: repeat(24, minmax(0, 1fr));
        column-gap: 16px;
        row-gap: 16px;
        padding: 16px;
    }
    #so-header-fields .so-edit-grid .form-group { margin-bottom: 0; }
    #so-header-fields .so-edit-grid .form-group:first-child { grid-column: span 3; }
    #so-header-fields .so-edit-grid .form-group:nth-child(n+2):nth-child(-n+4) { grid-column: span 7; }
    #so-header-fields .so-edit-grid .form-group:nth-child(n+5) { grid-column: span 8; }
    #so-header-fields .so-edit-grid label {
        min-height: 0;
        margin-bottom: 8px;
        color: #34282c;
        font-family: 'Poppins', sans-serif;
        font-size: 16px;
        font-weight: 500;
    }
    #so-header-fields .so-edit-grid .form-control,
    #so-header-fields .so-edit-grid .select2-container--bootstrap4 .select2-selection {
        height: 59px;
        min-height: 59px;
        border: 1px solid #e1e5e9;
        border-radius: 12px;
        padding-left: 16px;
        font-family: 'Poppins', sans-serif;
        font-size: 14px;
    }
    #so-header-fields .so-edit-grid .form-control { padding-right: 16px; }
    #so-header-fields .so-edit-grid .select2-container--bootstrap4 .select2-selection--single { padding-right: 44px; }
    @media (max-width: 991.98px) {
        #so-header-fields .so-edit-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        #so-header-fields .so-edit-grid .form-group:nth-child(n) { grid-column: auto; }
    }
    @media (max-width: 575.98px) {
        #so-header-fields .so-edit-grid { grid-template-columns: 1fr; }
    }
    /* Sales Order Detail ala Excel: putih = bisa diedit, abu = otomatis, biru = baru di-paste, merah = salah. */
    #table-preview-so tbody td.so-cell { cursor: cell; white-space: pre; }  /* pre: spasi ganda tetap terlihat */
    #table-preview-so tbody td.so-cell:focus { outline: none; }
    #table-preview-so tbody td.so-cell-auto { background: #f1f4f8 !important; color: #475569 !important; }
    #table-preview-so tbody td.so-cell-active { box-shadow: inset 0 0 0 2px #1d6fd8 !important; }
    #table-preview-so tbody td.so-cell-pasted { background: #dbeafe !important; }
    #table-preview-so tbody td.so-cell-error { background: #ffe4e6 !important; box-shadow: inset 0 0 0 2px #ef4444 !important; color: #b91c1c !important; }
    #table-preview-so tbody td.so-cell-error.so-cell-active { box-shadow: inset 0 0 0 2px #1d6fd8, inset 0 0 0 4px #ef4444 !important; }
    #table-preview-so tbody td.so-cell-editing { padding: 0 !important; overflow: visible !important; }
    #table-preview-so .so-cell-input { width: 100%; min-height: 34px; padding: 4px 8px; border: 0; outline: 2px solid #1d6fd8; background: #fff; color: #1e293b; font: inherit; }
    .so-preview-toolbar { display: flex; justify-content: flex-end; margin-bottom: 8px; }
    /* Fill handle ala Excel: kotak biru di pojok sel aktif, tarik ke atas/bawah untuk copy nilai (sama dengan tabel costing). */
    #table-preview-so tbody td.so-cell { position: relative; }
    #table-preview-so .so-fill-handle { position: absolute; right: 0; bottom: 0; z-index: 6; width: 8px; height: 8px; border: 1px solid #fff; background: #1a73e8; cursor: crosshair; }    #table-preview-so tbody td.so-fill-preview { background: #fff3c4 !important; box-shadow: inset 0 0 0 1px #e0a800 !important; }
    body.so-filling, body.so-filling * { cursor: crosshair !important; user-select: none; }
    .so-create-body .mkt-btn, .so-create-body .mkt-icon-btn, .card-footer .mkt-btn { font-family: 'Poppins', sans-serif; font-weight: 600; }
    .so-summary-item > span { font-family: 'Poppins', sans-serif; font-weight: 500 !important; }
    .so-summary-item strong { font-weight: 700 !important; }
    #table-preview-so, #table-preview-so th, #table-preview-so td, .so-preview-pager, .so-preview-pager .page-link, .so-preview-legend { font-family: 'Poppins', sans-serif !important; }
    #table-preview-so thead th { font-size: 12px !important; font-weight: 500 !important; }
    #table-preview-so tbody td { font-size: 13px !important; font-weight: 500 !important; }
    #table-preview-so tfoot th, #table-preview-so tbody td.preview-col-total { font-size: 13px !important; font-weight: 600 !important; }
    .so-preview-pager, .so-preview-pager .page-link { font-weight: 500; }
    .so-preview-pager .page-item.active .page-link { font-weight: 600; }
    .so-preview-legend { font-size: 11px; font-weight: 500; }
    .so-file-dropzone span { font-size: 14px; font-weight: 500; }
    .so-file-dropzone strong { font-weight: 600; }
    .so-file-dropzone small { font-size: 12px; font-weight: 500; }
    .so-image-caption { font-weight: 500; }
    #so-header-fields .so-edit-grid { grid-template-columns: repeat(24, minmax(0, 1fr)); }
    #so-header-fields .so-edit-grid > .form-group { grid-column: span 8; }
    #so-header-fields .so-edit-grid > .form-group:nth-child(1) { grid-column: span 3; }
    #so-header-fields .so-edit-grid > .form-group:nth-child(2),
    #so-header-fields .so-edit-grid > .form-group:nth-child(3),
    #so-header-fields .so-edit-grid > .form-group:nth-child(4) { grid-column: span 7; }
    @media (max-width: 991.98px) {
        #so-header-fields .so-edit-grid > .form-group,
        #so-header-fields .so-edit-grid > .form-group:nth-child(n) { grid-column: span 12 !important; }
    }
    @media (max-width: 575.98px) {
        #so-header-fields .so-edit-grid > .form-group:nth-child(n) { grid-column: 1 / -1 !important; }
        .so-file-dropzone span { font-size: 13px; }
    }
    .so-preview-pager label, .so-preview-pager .dataTables_length label { font-weight: 500 !important; }
    /* Label & nilai info seragam di tablet/HP (menimpa aturan per-ID). */
    @media (max-width: 991.98px) {
        .so-overview-section .so-summary-item > span { font-size: 14px !important; }
        .so-overview-section .so-summary-item strong { font-size: 15px !important; }
        .so-overview-section .so-overview-title { font-size: 16px !important; }
    }
    @media (max-width: 575.98px) {
        .so-overview-section .so-summary-item > span { font-size: 13px !important; }
        .so-overview-section .so-summary-item strong { font-size: 14px !important; }
        .so-overview-section .so-overview-title { font-size: 15px !important; }
    }
</style>
@endsection

@section('content')
<div class="card card-sb">
    <form id="form-create-so" action="{{ route('so-store') }}" method="POST" enctype="multipart/form-data" novalidate>
        @csrf
        <div class="card-body so-create-body{{ ($viewOnly ?? false) ? ' so-view' : '' }}">
            {{-- <div class="so-breadcrumb">Marketing &nbsp;›&nbsp; Proses &nbsp;›&nbsp; <a href="{{ route('master-costing') }}">Costing</a> &nbsp;›&nbsp; <a href="{{ route('master-marketing-so') }}">Sales Order</a> &nbsp;›&nbsp; <strong>Create Sales Order</strong></div>
             --}}
            <div class="so-breadcrumb">Marketing &nbsp;›&nbsp; Proses &nbsp;›&nbsp; <a href="{{ route('master-costing') }}">Costing</a> &nbsp;›&nbsp; <strong>{{ ($viewOnly ?? false) ? 'Detail Sales Order' : 'Create Sales Order' }}</strong></div>
            <div class="so-page-heading">
                <div>
                    @if ($viewOnly ?? false)
                        <h1>Detail Sales Order <i class="far fa-question-circle"></i><span class="so-no-badge">{{ $so->so_no }}</span></h1>
                        <p><i class="fas fa-cog"></i>Untuk Melihat Detail SO</p>
                    @else
                        <h1>Create Sales Order <i class="far fa-question-circle"></i></h1>
                        <p><i class="fa-solid fa-circle-info"></i>Untuk Menambah Data SO</p>
                    @endif
                </div>
            </div>
            @unless ($viewOnly ?? false)
            <div class="mkt-error-alert" id="so-error-alert" role="alert" hidden>
                <i class="fas fa-exclamation-circle"></i><span></span>
                <button type="button" class="mkt-error-alert-close" aria-label="Tutup" onclick="this.parentElement.hidden = true"><i class="far fa-times-circle"></i></button>
            </div>
            @endunless
            <input type="hidden" name="id_costing" id="id_costing" value="{{ $costingId }}">

            <div class="so-source-fields" aria-hidden="true">
                <select name="id_buyer" id="id_buyer">
                    <option value="">Pilih Buyer</option>
                    @foreach ($buyers as $buyer)
                        <option value="{{ $buyer->Id_Supplier }}">{{ $buyer->Supplier }}</option>
                    @endforeach
                </select>
                <input type="text" name="style" id="style">
                <input type="text" name="market" id="market">
                <input type="text" name="brand" id="brand">
                <select name="product_group" id="product_group">
                    <option value="">Pilih Product Group</option>
                    @foreach($product_groups as $group)
                        <option value="{{ $group->product_group }}">{{ $group->product_group }}</option>
                    @endforeach
                </select>
                <select name="id_product_item" id="id_product_item"><option value="">Pilih Product Item</option></select>
                <select name="marketing_order" id="marketing_order">
                    <option value="">Pilih Marketing Order</option>
                    @foreach ($marketing_orders as $mo)
                        <option value="{{ $mo->id }}">{{ $mo->mkt_order }}</option>
                    @endforeach
                </select>
                <input type="text" name="smv" id="smv">
                <select name="type" id="type">
                    <option value="single">SINGLE</option>
                    <option value="multiple">MULTIPLE</option>
                </select>
                <select id="product_set" name="product_set[]" multiple>
                    @isset($master_set)
                        @foreach ($master_set as $m_set)
                            <option value="{{ $m_set->id }}">{{ $m_set->nama ?? $m_set->id }}</option>
                        @endforeach
                    @endisset
                </select>
                <input type="text" name="confirm_price" id="confirm_price">
                <input type="hidden" name="image_costing_path" id="image_costing_path" value="">
            </div>

            @if ($viewOnly ?? false)
            <div class="so-lock-alert{{ $soUnlock === 'unlocked' ? ' is-unlocked' : '' }}" id="so-lock-alert" role="alert">
                @if ($soUnlock === 'unlocked')
                    <span><i class="fas fa-lock-open"></i> This SO is unlocked by the marketing manager and can be changed for 2 days.</span>
                @elseif ($soUnlock === 'requested')
                    <span><i class="fas fa-lock"></i> Unlock requested. Waiting for the marketing manager to approve it in the Approval Center.</span>
                    <button type="button" class="mkt-btn mkt-btn--navy mkt-btn--sm" disabled>Unlock Requested</button>
                @elseif ($soUnlock === 'cancel')
                    <span><i class="fas fa-ban"></i> This SO is cancelled.</span>
                @else
                    <span><i class="fas fa-lock"></i> This document is approved and can't be edited. Request an unlock from the marketing manager if you need to change it.</span>
                    <button type="button" class="mkt-btn mkt-btn--navy mkt-btn--sm" id="btn-so-request-unlock">Request Unlock</button>
                @endif
                <button type="button" class="so-lock-alert-close" aria-label="Tutup" onclick="document.getElementById('so-lock-alert').remove()"><i class="far fa-times-circle"></i></button>
            </div>
            <div class="so-summary-cards is-four" aria-label="Ringkasan SO">
                <div class="so-summary-card"><span>No Costing</span><strong id="summary-costing-no">-</strong></div>
                <div class="so-summary-card"><span>No Worksheet</span><strong id="summary-worksheet-no">-</strong></div>
                <div class="so-summary-card"><span>No Sales Order</span><strong>{{ $so->so_no }}</strong></div>
                <div class="so-summary-card"><span>Delivery Date</span><strong id="summary-delivery-date">-</strong></div>
            </div>
            @else
            <div class="so-summary-cards" aria-label="Ringkasan Costing">
                <div class="so-summary-card">
                    <span>No Costing</span>
                    <strong id="summary-costing-no">-</strong>
                </div>
                <div class="so-summary-card">
                    <span>No Worksheet</span>
                    <strong id="summary-worksheet-no">-</strong>
                </div>
                <div class="so-summary-card is-navy">
                    <span>QTY Planned</span>
                    <strong id="summary-planned-qty" class="text-center">-</strong>
                </div>
                <div class="so-summary-card is-orange">
                    <span>Delivery Date</span>
                    <strong id="summary-delivery-date">-</strong>
                </div>
                <div class="so-summary-card is-blue">
                    <span>Ex Factory Date</span>
                    <strong id="summary-exfty-date">-</strong>
                </div>
            </div>
            @endif

            <div class="so-top-layout">
                <div class="so-info-column">
                    <section class="so-overview-section">
                        <h2 id="so-information-title" class="so-overview-title">Information Buyer</h2>
                        <div id="so-information" class="so-overview-grid">
                            <div class="so-summary-item"><span>Buyer Name</span><strong id="summary-buyer">-</strong></div>
                            <div class="so-summary-item"><span>Kode Buyer</span><strong id="summary-buyer-code">-</strong></div>
                            <div class="so-summary-item"><span>Brand</span><strong id="summary-brand">-</strong></div>
                            <div class="so-summary-item"><span>Season</span><strong id="summary-season">-</strong></div>
                            <div class="so-summary-item"><span>Style</span><strong id="summary-style">-</strong></div>
                        </div>
                    </section>

                    <section class="so-overview-section">
                        <h2 id="so-product-title" class="so-overview-title">Product &amp; Type</h2>
                        <div id="so-product" class="so-overview-grid">
                            <div class="so-summary-item"><span>Product Group</span><strong id="summary-product-group">-</strong></div>
                            <div class="so-summary-item"><span>Product Items</span><strong id="summary-product-item">-</strong></div>
                            <div class="so-summary-item"><span>Product Type</span><strong id="summary-product-type">-</strong></div>
                            <div class="so-summary-item"><span>Unit</span><strong id="summary-unit">-</strong></div>
                            <div class="so-summary-item"><span>Costing Type</span><strong id="summary-costing-type">-</strong></div>
                            <div class="so-summary-item"><span>Worksheet Type</span><strong id="summary-worksheet-type">-</strong></div>
                        </div>
                    </section>
                </div>

                <section class="so-overview-section so-review-section">
                    <h2 class="so-overview-title so-information-title">Review Image</h2>
                    <div class="so-image-review">
                        <button type="button" class="so-image-nav" disabled aria-label="Previous image"><i class="fas fa-chevron-left"></i></button>
                        <div class="so-image-preview mkt-cf-stage">
                            <img class="mkt-cf-side is-prev is-empty" alt="Gambar sebelumnya">
                            <img id="preview_images" class="mkt-cf-main" src="" alt="Preview gambar Costing" hidden>
                            <span id="text_preview">Gambar Costing akan ditampilkan di sini.</span>
                            <img class="mkt-cf-side is-next is-empty" alt="Gambar berikutnya">
                        </div>
                        <button type="button" class="so-image-nav" disabled aria-label="Next image"><i class="fas fa-chevron-right"></i></button>
                    </div>
                    <div id="so-image-counter" class="so-image-counter mkt-cf-dots"></div>
                    <div id="so-image-thumbs" class="so-image-thumbs"></div>
                    <div id="so-image-caption" class="so-image-caption"></div>
                </section>
            </div>

            <section class="so-overview-section so-shipping-section">
                <h2 id="so-shipping-title" class="so-overview-title">Shipping &amp; Destinations</h2>
                <div id="so-shipping" class="so-overview-grid so-shipping-grid">
                    <div class="so-summary-item"><span>Ship Mode</span><strong id="summary-ship-mode">-</strong></div>
                    <div class="so-summary-item"><span>Main Destination</span><strong id="summary-destination">-</strong></div>
                    <div class="so-summary-item"><span>Shipment Type</span><strong id="summary-shipment-type">-</strong></div>
                    <div class="so-summary-item"><span>Market</span><strong id="summary-market">-</strong></div>
                    <div class="so-summary-item"><span>Marketing Order</span><strong id="summary-marketing-order">-</strong></div>
                    <div class="so-summary-item"><span>Notes</span><strong id="summary-notes">-</strong></div>
                </div>
            </section>

            <section class="so-section so-heading-only">
                <button type="button" class="so-section-toggle" data-target="#so-header-fields" aria-expanded="true">
                    <span>Sales Order &amp; Purchase Order<span class="text-danger">*</span></span><span class="so-chevron"><i class="fas fa-chevron-down"></i></span>
                </button>
                <div id="so-header-fields" class="collapse show">
                    <div class="so-edit-grid">
                        <div class="form-group">
                            <label>Currency</label>
                            <select name="id_currency" id="id_currency" class="form-control select2bs4">
                                <option value="">Choose Curr</option>
                                @foreach($currency as $curr)
                                    <option value="{{ $curr->id }}">{{ $curr->nama_pilihan }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Sales Order Type</label>
                            <select name="jns_so" id="jns_so" class="form-control select2bs4" required>
                                <option value="">Choose Sales Order Type</option>
                                <option value="FOB">FOB</option>
                                <option value="CMT">CMT</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>FOB</label><input type="text" name="fob" id="fob" class="form-control input-decimal" placeholder="FOB" required>
                        </div>
                        <div class="form-group so-notes-field">
                            <label>Notes</label><input type="text" name="notes" id="notes" class="form-control" placeholder="Notes">
                        </div>
                        <input type="hidden" name="total_qty" id="total_qty">
                        <input type="hidden" name="jumlah_po" id="jumlah_po">
                        <div class="form-group">
                            <label>PO System</label>
                            <select name="po_type" id="po_type" class="form-control select2bs4" required>
                                <option value="single">1 WS 1 PO</option>
                                <option value="multiple">1 WS Beberapa PO</option>
                            </select>
                        </div>
                    </div>
                </div>
            </section>

            @if ($viewOnly ?? false)
            <section class="so-detail-card">
                <div class="so-detail-scroll">
                    <table class="so-detail-table">
                        <thead>
                            <tr>
                                <th class="so-dt-check"><input type="checkbox" id="so-detail-check-all"></th>
                                <th>No</th>
                                <th>Style</th>
                                <th>Description</th>
                                <th>No Purchase Order</th>
                                <th>Market</th>
                                <th>Destination</th>
                                <th>Ex Fty</th>
                                <th>Color</th>
                                <th>Size</th>
                                <th class="text-right">Qty</th>
                            </tr>
                        </thead>
                        <tbody id="so-detail-body"></tbody>
                        <tfoot>
                            <tr>
                                <td colspan="2"></td>
                                <td colspan="8">TOTAL</td>
                                <td class="text-right">{{ number_format(collect($soRows)->sum('qty'), 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="so-detail-pager">
                    <div>
                        <span>Item Per Page</span>
                        <select id="so-detail-per-page">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <span id="so-detail-page-count">Of 1</span>
                    </div>
                    <div class="so-detail-pages" id="so-detail-pages"></div>
                </div>
            </section>
            @else
            <section class="so-section so-heading-only">
                <button type="button" class="so-section-toggle" data-target="#so-upload-file" aria-expanded="true">
                    <span>Upload File<span class="text-danger">*</span></span><span class="so-chevron"><i class="fas fa-chevron-down"></i></span>
                </button>
                <div id="so-upload-file" class="collapse show">
                    <div class="so-upload-panel">
                        <div class="so-upload-toolbar">
                            <a href="{{ asset('template/template_upload_so.xlsx') }}" class="mkt-btn mkt-btn--navy so-template-btn"><i class="fas fa-download"></i> Download Template Excel</a>
                        </div>
                        <input type="file" name="file_so" id="file_so" accept=".xls,.xlsx" hidden>
                        <div id="so-file-dropzone" class="so-file-dropzone" role="button" tabindex="0">
                            <i class="fas fa-file-alt"></i>
                            <span>Drag n Drop File Here Or <strong>Browse</strong></span>
                            <small>Format .xlsx, max 50 MB</small>
                        </div>
                        <div id="so-selected-file" class="so-selected-file" hidden>
                            <i class="fas fa-file-excel"></i><span id="so-selected-file-name"></span>
                            <button type="button" id="so-remove-file" aria-label="Remove selected file"><i class="fas fa-times"></i></button>
                        </div>
                        <button type="button" id="btn-upload-so" class="mkt-btn mkt-btn--navy w-100 mt-3" disabled>Upload Now</button>
                    </div>
                </div>
            </section>
            @endif
        </div>

        @unless ($viewOnly ?? false)
        <section class="so-section so-heading-only so-preview-section" id="card-preview">
            <button type="button" class="so-section-toggle" data-target="#so-preview-body" aria-expanded="true">
                <span>Sales Order Detail</span><span class="so-chevron"><i class="fas fa-chevron-down"></i></span>
            </button>
            <div id="so-preview-body" class="collapse show">
            <div class="so-preview-body">
                {{-- <button type="button" id="btn-reload-preview" class="btn btn-info mb-2" onclick="loadPreviewUpload()"><i class="fas fa-refresh"></i> Reload Table</button> --}}
                @unless ($viewOnly ?? false)
                <div class="so-preview-toolbar">
                    <button type="button" class="mkt-btn mkt-btn--navy mkt-btn--sm" id="so-add-row" title="Tambah baris (Color, Size, Qty diisi di tabel)"><i class="fas fa-plus"></i> Add</button>
                </div>
                @endunless
                <div class="table-responsive">
                    <div class="so-preview-table-wrap">
                    <table class="table table-sm table-striped text-nowrap w-100" id="table-preview-so">
                        <thead class="bg-sb text-white text-center">
                            <tr>
                                <th class="preview-col-style">Style</th>
                                <th class="preview-col-desc">Description</th>
                                <th class="preview-col-po">No PO</th>
                                <th class="preview-col-market">Market</th>
                                <th class="preview-col-destination">Destination</th>
                                <th class="preview-col-exfty">Ex Fty</th>
                                <th class="preview-col-fob">FOB</th>
                                <th class="preview-col-set">Product Set</th>
                                <th class="preview-col-color">Color</th>
                                <th class="preview-col-total">Total</th>
                                <th class="preview-col-action">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                    </div>
                </div>
                <div class="so-preview-legend" aria-label="Keterangan tabel">
                    <span><i></i>Editable</span>
                    <span><i class="is-auto"></i>Auto calculated</span>
                    <span><i class="is-pasted"></i>Just Pasted</span>
                    <span><i class="is-error"></i>Error</span>
                </div>
            </div>
            </div>
        </section>

        @endunless

        @if ($viewOnly ?? false)
            @include('marketing.costing.unlock_request_modal')
        @endif

        @unless ($viewOnly ?? false)
        <div class="card-footer text-right">
            <button type="button" id="btn-back" class="mkt-btn mkt-btn--red mkt-btn--cancel">
                Cancel
            </button>
            <button type="submit" class="mkt-btn mkt-btn--green">Save</button>
        </div>
        @endunless
    </form>
</div>

@endsection

@section('custom-script')
<script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
<script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
<script>
    let previewTable = null;
    let previewUploadRows = [];
    let soFileUploaded = false;
    let soUploadedPos = [];   // daftar No PO dari file upload (untuk cek kecocokan PO System)

    function formatSummaryDate(value) {
        if (!value) return '-';
        const match = String(value).trim().match(/^(\d{4})-(\d{2})-(\d{2})/);
        return match ? `${match[3]}/${match[2]}/${match[1]}` : String(value);
    }

    function loadPreviewUpload() {
        // Dipanggil setelah Apply di Preview Changes: tampilkan data yang tersimpan di section Preview Data Upload.
        $('#card-preview').show();
        $('#table-preview-so tbody').html('<tr><td colspan="12" class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading data...</td></tr>');
        $('#summary-exfty-date').text('-');

        $.ajax({
            url: "{{ route('so-get-temp-data') }}",
            type: 'GET',
            data: { id_costing: $('#id_costing').val() },
            success: function(res) {
                if ($.fn.DataTable.isDataTable('#table-preview-so')) {
                    $('#table-preview-so').DataTable().destroy();
                }
                // Satu baris = satu color + size (temp_so_detail per id), urut sesuai upload.
                previewUploadRows = res.rows || [];
                soDupRows = soDuplicateRows();
                soSizes = res.available_sizes || [];

                $('#table-preview-so thead').html(`<tr>
                    <th class="preview-col-no">No</th>
                    <th class="preview-col-style">Style</th>
                    <th class="preview-col-desc">Description</th>
                    <th class="preview-col-po">No PO</th>
                    <th class="preview-col-market">Market</th>
                    <th class="preview-col-destination">Destination</th>
                    <th class="preview-col-exfty">Ex Fty</th>
                    <th class="preview-col-fob">FOB</th>
                    <th class="preview-col-set">Product Set</th>
                    <th class="preview-col-color">Color</th>
                    <th class="preview-col-size">Size</th>
                    <th class="preview-col-qty">Qty</th>
                    <th class="preview-col-action">Action</th>
                </tr>`);

                let bodyHtml = '';
                if (previewUploadRows.length > 0) {
                    const exFactoryDate = previewUploadRows.find(row => row.ex_fty);
                    $('#summary-exfty-date').text(formatSummaryDate(exFactoryDate ? exFactoryDate.ex_fty : null));
                    previewUploadRows.forEach((row, rowIndex) => {
                        bodyHtml += `<tr data-id="${row.id}">
                            <td class="preview-col-no so-cell-auto">${rowIndex + 1}</td>
                            ${soCell(rowIndex, 'style', 'preview-col-style')}
                            ${soCell(rowIndex, 'desc', 'preview-col-desc')}
                            ${soCell(rowIndex, 'po', 'preview-col-po')}
                            ${soCell(rowIndex, 'market', 'preview-col-market')}
                            ${soCell(rowIndex, 'destination', 'preview-col-destination')}
                            ${soCell(rowIndex, 'ex_fty', 'preview-col-exfty')}
                            ${soCell(rowIndex, 'fob', 'preview-col-fob text-right')}
                            ${soCell(rowIndex, 'product_set', 'preview-col-set')}
                            ${soCell(rowIndex, 'color', 'preview-col-color')}
                            ${soCell(rowIndex, 'size', 'preview-col-size' + (row.error ? ' so-cell-error' : ''))}
                            ${soCell(rowIndex, 'qty', 'preview-col-qty text-right')}
                            <td class="preview-col-action">
                                <button type="button" class="preview-row-delete" data-row-index="${rowIndex}" aria-label="Hapus baris" title="Hapus baris">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </td></tr>`;
                    });
                } else {
                    bodyHtml = '<tr><td colspan="13" class="text-center">Belum ada data di-upload.</td></tr>';
                }
                $('#table-preview-so tbody').html(bodyHtml);

                if ($('#table-preview-so tfoot').length === 0) $('#table-preview-so').append('<tfoot></tfoot>');
                const grandTotal = previewUploadRows.reduce((sum, row) => sum + (Number(row.qty) || 0), 0);
                $('#table-preview-so tfoot').html(`<tr>
                    <th></th>
                    <th class="text-left fw-bold">GRAND TOTAL</th>
                    <th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th>
                    <th class="preview-col-qty fw-bold">${grandTotal.toLocaleString('id-ID')}</th>
                    <th class="preview-col-action"></th>
                </tr>`);

                $('#total_qty').val(res.total_qty || 0);
                if (Number(res.total_qty) > 0) { $('#so-file-dropzone').removeClass('is-invalid'); refreshSoErrorAlert(); }
                $('#jumlah_po').val(res.jumlah_po || 0);
                soUploadedPos = [...new Set(previewUploadRows.map(row => row.po).filter(Boolean))];

                if (previewUploadRows.length > 0) {
                    previewTable = $('#table-preview-so').DataTable({
                        "paging": true,
                        "pageLength": 10,
                        "lengthMenu": [5, 10, 25, 50, 100],
                        "dom": 't<"so-preview-pager"lp>',
                        "language": { "lengthMenu": "Item Per Page _MENU_", "paginate": { "previous": "‹", "next": "›" } },
                        "info": true,
                        "searching": true,
                        "ordering": false,
                        "autoWidth": false,
                        "responsive": false,
                        "scrollX": false,
                        "columnDefs": [
                            { targets: 0, width: '40px' },
                            { targets: 1, width: '145px' },
                            { targets: 2, width: '155px' },
                            { targets: 3, width: '135px' },
                            { targets: 4, width: '95px' },
                            { targets: 5, width: '125px' },
                            { targets: 6, width: '105px' },
                            { targets: 7, width: '85px' },
                            { targets: 8, width: '115px' },
                            { targets: 9, width: '150px' },
                            { targets: 10, width: '80px' },
                            { targets: 11, width: '90px' },
                            { targets: '_all', className: 'text-center' }
                        ],
                        "drawCallback": function(settings) {
                            let api = this.api();
                            setTimeout(function() { api.columns.adjust(); }, 10);
                        }
                    });
                }

                soAfterRender();

                let btnSubmit = $('#form-create-so button[type="submit"]');

                let isSizeMissing = (res.missing_sizes && res.missing_sizes.length > 0);
                let isDataEmpty = (!res.data || res.data.length === 0);

                if (isDataEmpty) {
                    btnSubmit.prop('disabled', true);
                }
                else if (isSizeMissing) {
                    btnSubmit.prop('disabled', false);
                    let htmlError = `<p class="text-danger mb-1" style="font-size:14px;"><b>Size di BOM tapi TIDAK ADA di Excel:</b></p>
                                    <p class="mb-3 text-dark">` + res.missing_sizes.join(', ') + `</p>`;

                    Swal.fire({
                        icon: 'error',
                        title: 'Size Tidak Lengkap!',
                        html: htmlError + '<br><small><b>Solusi:</b> Pastikan Size di Excel Anda lengkap sesuai BOM.</small>',
                        confirmButtonText: 'Tutup'
                    });
                }
                else {
                    btnSubmit.prop('disabled', false);
                    $('#btn-fix-bom').remove();
                }
            },
            error: function(xhr) {
                $('#table-preview-so tbody').html('<tr><td colspan="11" class="text-center text-danger">Gagal memuat data preview.</td></tr>');
            }
        });
    }

    $(document).on('click', '.preview-row-delete', function() {
        const row = previewUploadRows[Number($(this).attr('data-row-index'))];
        if (!row) return;
        const label = [row.color, row.size].filter(Boolean).join(' / ') || 'baris baru';

        Swal.fire({
            icon: 'warning',
            title: 'Hapus baris?',
            text: label + ' (PO ' + (row.po || '-') + ') akan dihapus dari data upload sementara.',
            showCancelButton: true,
            confirmButtonText: 'Hapus',
            cancelButtonText: 'Batal'
        }).then(function(result) {
            if (!result.isConfirmed) return;
            $.ajax({
                url: "{{ route('so-delete-temp-rows') }}",
                type: 'POST',
                data: { _token: '{{ csrf_token() }}', id_costing: $('#id_costing').val(), ids: [row.id] },
                success: function() { loadPreviewUpload(); },
                error: function(xhr) {
                    Swal.fire('Gagal', xhr.responseJSON?.message || 'Baris gagal dihapus.', 'error');
                }
            });
        });
    });

    // Tombol Add: baris baru menyalin PO/Style/Market/dll dari baris yang dipilih (atau baris terakhir);
    // Color, Size & Qty diisi di tabel (merah sampai lengkap, Save diblok).
    $(document).on('click', '#so-add-row', function() {
        const source = soActive ? previewUploadRows[soActive.row] : null;
        const $button = $(this).prop('disabled', true);
        $.ajax({
            url: "{{ route('so-add-temp-row') }}",
            type: 'POST',
            data: { _token: '{{ csrf_token() }}', id_costing: $('#id_costing').val(), source_id: source ? source.id : null }
        }).done(function(res) {
            soReloadState = { page: 'last', focusId: res.id, focusField: 'color', move: null, pasted: [], active: null };
            loadPreviewUpload();
        }).fail(function(xhr) {
            Swal.fire('Gagal', xhr.responseJSON?.message || 'Baris gagal ditambahkan.', 'error');
        }).always(function() {
            $button.prop('disabled', false);
        });
    });


    // ===== Sales Order Detail: edit ala Excel (mirip grid Costing/BOM) =====
    // Klik sel = pilih; ketik / Enter / F2 = edit; panah, Tab, Enter = pindah; Delete = kosongkan; Ctrl+V = paste (biru sebentar).
    // Teks tidak boleh berspasi di awal/akhir atau berspasi ganda; qty angka bulat > 0; color harus ada di master. Sel salah merah dan Save diblok.
    const SO_TEXT_FIELDS = ['desc', 'po', 'market', 'destination', 'ex_fty', 'product_set', 'color', 'size'];
    const SO_REQUIRED_FIELDS = ['po', 'color', 'size'];
    const SO_MASTER_COLORS = new Set(@json(collect($master_colors ?? [])->map(fn ($color) => strtoupper((string) $color->name))->values()));
    const SO_MASTER_SIZES = new Set(@json(collect($master_sizes ?? [])->map(fn ($size) => strtoupper(trim((string) $size->size)))->values()));
    let soSizes = [];
    let soInvalidEdits = {};      // nilai salah yang belum dikirim ke server (tetap merah sampai diperbaiki)
    let soActive = null;          // { row, field }
    let soReloadState = null;     // posisi halaman, sel aktif & sel hasil paste setelah tabel dimuat ulang

    function soEsc(value) { return $('<div>').text(value === null || value === undefined ? '' : String(value)).html(); }
    // Urutan kolom yang bisa diedit (untuk paste dari Excel): satu baris = satu color + size.
    function soFieldOrder() { return ['desc', 'po', 'market', 'destination', 'ex_fty', 'fob', 'product_set', 'color', 'size', 'qty']; }
    function soRowSig(index) {
        const row = previewUploadRows[index] || {};
        return 'id:' + row.id;
    }
    function soCellKey(index, field) { return soRowSig(index) + '|' + field; }

    function soRawValue(index, field) {
        const row = previewUploadRows[index] || {};
        if (field === 'qty') return Number(row.qty) > 0 ? String(row.qty) : '';
        if (field === 'product_set') return !row.product_set || row.product_set === '-' ? '' : String(row.product_set);
        const value = row[field];
        return value === null || value === undefined ? '' : String(value);
    }
    function soCurrentValue(index, field) {
        const key = soCellKey(index, field);
        return Object.prototype.hasOwnProperty.call(soInvalidEdits, key) ? soInvalidEdits[key] : soRawValue(index, field);
    }

    // Angka format Indonesia (titik ribuan, koma desimal); "1.500,25" -> 1500.25, "0.5" -> 0.5, "1.500" -> 1500.
    function soParseNumber(value) {
        let text = String(value === null || value === undefined ? '' : value).trim().replace(/\s/g, '');
        if (text === '') return NaN;
        const lastDot = text.lastIndexOf('.'), lastComma = text.lastIndexOf(',');
        if (lastDot > -1 && lastComma > -1) {
            text = lastComma > lastDot ? text.replace(/\./g, '').replace(',', '.') : text.replace(/,/g, '');
        } else if (lastComma > -1) {
            text = text.replace(/\./g, '').replace(',', '.');
        } else if (/^-?\d{1,3}(\.\d{3})+$/.test(text)) {
            text = text.replace(/\./g, '');
        }
        return /^-?\d*\.?\d+$/.test(text) ? Number(text) : NaN;
    }

    function soCellError(field, value) {
        value = value === null || value === undefined ? '' : String(value);
        if (SO_TEXT_FIELDS.includes(field)) {
            if (value !== value.trim() || /\s{2,}/.test(value)) return 'Tidak boleh ada spasi di awal/akhir atau spasi ganda.';
            if (SO_REQUIRED_FIELDS.includes(field) && value === '') return 'Wajib diisi.';
            if (field === 'color' && !SO_MASTER_COLORS.has(value.toUpperCase())) return 'Color tidak ada di master.';
            if (field === 'size' && !SO_MASTER_SIZES.has(value.toUpperCase())) return 'Size tidak ada di master.';
            return null;
        }
        if (field === 'fob') return value === '' || Number.isFinite(soParseNumber(value)) ? null : 'FOB harus angka.';
        if (field === 'qty') {
            const qty = value.replace(/[.,]/g, '');
            if (value === '') return 'Wajib diisi.';
            return /^\d+$/.test(qty) && Number(qty) > 0 ? null : 'Qty harus angka bulat lebih dari 0.';
        }
        return null;
    }

    // Baris yang sama persis dengan baris lain: PO + Market + Destination + Color + Size (dan Style / Product Set).
    // Beda PO, Market atau Destination -> Color/Size sama boleh. Sel Color & Size baris dobel merah, Save diblok.
    const SO_DUPLICATE_MSG = 'Color + Size sama persis dengan baris lain (PO, Market, Destination sama).';
    let soDupRows = new Set();
    function soDuplicateRows() {
        const groups = {};
        previewUploadRows.forEach((row, index) => {
            const color = soCurrentValue(index, 'color').toUpperCase(), size = soCurrentValue(index, 'size').toUpperCase();
            if (!color || !size) return;
            const key = [row.style || '', soCurrentValue(index, 'po'), soCurrentValue(index, 'market'), soCurrentValue(index, 'destination'),
                soCurrentValue(index, 'product_set'), color, size].join('\u0001');
            (groups[key] = groups[key] || []).push(index);
        });
        const rows = new Set();
        Object.values(groups).forEach(list => { if (list.length > 1) list.forEach(index => rows.add(index)); });
        return rows;
    }

    function soCountErrors() {
        let count = 0;
        previewUploadRows.forEach((row, index) => {
            soFieldOrder().forEach(field => { if (soCellError(field, soCurrentValue(index, field))) count++; });
        });
        return count + soDuplicateRows().size * 2;   // Color + Size per baris dobel
    }

    // HTML satu sel yang bisa diedit (dipakai saat render tabel).
    function soCell(index, field, className) {
        const value = soCurrentValue(index, field);
        if (field === 'style') return `<td class="${className} so-cell-auto" title="${soEsc(value)}">${soEsc(value || '-')}</td>`;
        const error = soCellError(field, value) || ((field === 'color' || field === 'size') && soDupRows.has(index) ? SO_DUPLICATE_MSG : null);
        let shown = value === '' ? '-' : value;
        if (field === 'fob' && Number.isFinite(soParseNumber(value))) shown = soParseNumber(value).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 4 });
        if (field === 'qty' && /^\d+$/.test(value)) shown = Number(value).toLocaleString('id-ID');
        return `<td class="${className} so-cell${error ? ' so-cell-error' : ''}" tabindex="-1" data-row="${index}" data-field="${soEsc(field)}" title="${soEsc(error || value)}">${soEsc(shown)}</td>`;
    }

    function soTd(index, field) {
        return $('#table-preview-so tbody td.so-cell').filter(function () {
            return Number(this.dataset.row) === index && this.dataset.field === field;
        }).first();
    }
    function soSelect($td) {
        if (!$td || !$td.length) return;
        $('#table-preview-so td.so-cell-active').removeClass('so-cell-active');
        $('#table-preview-so .so-fill-handle').remove();
        $td.addClass('so-cell-active').trigger('focus');
        $td.append('<span class="so-fill-handle" title="Tarik ke atas/bawah untuk copy nilai"></span>');
        soActive = { row: Number($td.attr('data-row')), field: $td.attr('data-field') };
    }

    // Sel-sel satu kolom yang tampil (urutan di layar), untuk fill handle & Ctrl+D.
    function soColumnTds(field) {
        return $('#table-preview-so tbody td.so-cell').filter(function () { return this.dataset.field === field; });
    }
    // Copy nilai sel sumber ke sel-sel target; disimpan seperti paste (biru, sel salah tetap merah).
    // targets: [{ row, field }] — sel-sel satu kolom (atas/bawah).
    function soFillCells(sourceRow, sourceField, targets) {
        if (!targets.length) return;
        const value = soCurrentValue(sourceRow, sourceField);
        soCommit(targets.map(target => ({ row: target.row, field: target.field, value: value })), { pasted: true });
    }
    function soMove($td, direction) {
        let $next = $();
        if (direction === 'left') $next = $td.prevAll('td.so-cell').first();
        if (direction === 'right') $next = $td.nextAll('td.so-cell').first();
        if (direction === 'up' || direction === 'down') {
            const $tr = direction === 'up' ? $td.closest('tr').prev('tr') : $td.closest('tr').next('tr');
            $next = $tr.children('td.so-cell').filter(function () { return this.dataset.field === $td.attr('data-field'); }).first();
        }
        if ($next.length) soSelect($next);
    }

    function soEdit($td, firstChar) {
        if (!$td.length || $td.hasClass('so-cell-editing')) return;
        const index = Number($td.attr('data-row'));
        const field = $td.attr('data-field');
        const original = soCurrentValue(index, field);
        const $input = $('<input type="text" class="so-cell-input" autocomplete="off">').val(firstChar !== undefined ? firstChar : original);
        $td.addClass('so-cell-editing').empty().append($input);
        $input.trigger('focus');
        if (firstChar === undefined) $input[0].select();
        let done = false;
        const finish = function (save, move) {
            if (done) return;
            done = true;
            const value = $input.val();
            $td.removeClass('so-cell-editing');
            if (save && value !== original) {
                soCommit([{ row: index, field: field, value: value }], { move: move });
            } else {
                $td.replaceWith(soCell(index, field, $td.attr('class').replace(/\s*so-cell(-active|-error|-pasted)?\b/g, '').trim()));
                const $fresh = soTd(index, field);
                soSelect($fresh);
                if (move) soMove($fresh, move);
            }
        };
        $input.on('keydown', function (e) {
            if (e.key === 'Enter') { e.preventDefault(); finish(true, 'down'); }
            else if (e.key === 'Tab') { e.preventDefault(); finish(true, e.shiftKey ? 'left' : 'right'); }
            else if (e.key === 'Escape') { e.preventDefault(); finish(false); }
            e.stopPropagation();
        }).on('blur', function () { finish(true); });
    }

    // Simpan perubahan: yang salah disimpan lokal (merah), yang benar dikirim ke temp_so_detail lalu tabel dimuat ulang.
    function soCommit(changes, options) {
        options = options || {};
        const toSend = [];
        const sentChanges = [];   // pasangan toSend[i] -> changes, untuk menandai error dari server
        changes.forEach(change => {
            const key = soCellKey(change.row, change.field);
            if (soCellError(change.field, change.value)) {
                soInvalidEdits[key] = change.value;
                return;
            }
            delete soInvalidEdits[key];
            // FOB dikirim sebagai angka mesin (1500.25), tampilannya tetap format Indonesia.
            if (change.field === 'fob' && change.value !== '') change = Object.assign({}, change, { value: String(soParseNumber(change.value)) });
            if (change.value !== soRawValue(change.row, change.field)) {
                const row = previewUploadRows[change.row];
                sentChanges.push(change);
                toSend.push({ id: row.id, field: change.field, value: change.value });
            }
        });

        const active = soActive;
        soReloadState = {
            page: previewTable ? previewTable.page() : 0,
            active: active,
            move: options.move || null,
            pasted: options.pasted ? changes.filter(change => !soCellError(change.field, change.value)).map(change => ({ row: change.row, field: change.field })) : []
        };
        if (!toSend.length) {
            loadPreviewUpload();
            return;
        }
        $.ajax({
            url: "{{ route('so-update-temp-row-cells') }}",
            type: 'POST',
            data: { _token: '{{ csrf_token() }}', id_costing: $('#id_costing').val(), changes: toSend }
        }).done(function (res) {
            (res.errors || []).forEach(error => {
                const change = sentChanges[error.index];
                if (change) soInvalidEdits[soCellKey(change.row, change.field)] = change.value;
            });
            if (res.errors && res.errors.length) {
                Swal.fire({ icon: 'warning', title: 'Sebagian sel tidak disimpan', html: res.errors.map(error => soEsc(error.message)).join('<br>') });
            }
        }).fail(function (xhr) {
            Swal.fire('Gagal!', xhr.responseJSON?.message || 'Perubahan gagal disimpan.', 'error');
        }).always(function () {
            loadPreviewUpload();
        });
    }

    // Setelah tabel dimuat ulang: kembalikan halaman, sel aktif, dan tandai sel hasil paste (biru 4 detik).
    function soAfterRender() {
        const state = soReloadState;
        soReloadState = null;
        if (!state) return;
        if (previewTable && state.page === 'last') previewTable.page('last').draw(false);
        else if (previewTable && state.page) previewTable.page(Math.min(state.page, previewTable.page.info().pages - 1)).draw(false);
        if (state.focusId) {
            const index = previewUploadRows.findIndex(row => row.id === state.focusId);
            if (index !== -1) state.active = { row: index, field: state.focusField || 'color' };
        }
        state.pasted.forEach(cell => soTd(cell.row, cell.field).addClass('so-cell-pasted'));
        if (state.pasted.length) setTimeout(() => $('#table-preview-so td.so-cell-pasted').removeClass('so-cell-pasted'), 4000);
        if (state.active) {
            const $td = soTd(state.active.row, state.active.field);
            soSelect($td);
            if (state.move) soMove($td, state.move);
        }
    }

    // Fill handle: tekan kotak biru, tarik ke atas/bawah di kolom yang sama (preview kuning), lepas = copy nilai.
    let soFill = null;   // { row, field, targets: [{ row, field }] }
    $(document).on('mousedown', '#table-preview-so .so-fill-handle', function (e) {
        e.preventDefault();
        e.stopPropagation();
        if (!soActive || e.which !== 1) return;
        soFill = { row: soActive.row, field: soActive.field, targets: [] };
        $('body').addClass('so-filling');
    });
    $(document).on('mouseenter', '#table-preview-so tbody td.so-cell', function () {
        if (!soFill) return;
        const $source = soTd(soFill.row, soFill.field);
        let $line = $(), from = -1, to = -1;
        if (this.dataset.field === soFill.field) $line = soColumnTds(soFill.field);
        $('#table-preview-so td.so-fill-preview').removeClass('so-fill-preview');
        soFill.targets = [];
        if (!$line.length) return;
        from = $line.index($source);
        to = $line.index(this);
        if (from === -1 || to === from) return;
        const step = to > from ? 1 : -1;
        for (let i = from + step; i !== to + step; i += step) {
            const $cell = $line.eq(i).addClass('so-fill-preview');
            soFill.targets.push({ row: Number($cell.attr('data-row')), field: $cell.attr('data-field') });
        }
    });
    $(document).on('mouseup', function () {
        if (!soFill) return;
        const fill = soFill;
        soFill = null;
        $('body').removeClass('so-filling');
        $('#table-preview-so td.so-fill-preview').removeClass('so-fill-preview');
        soFillCells(fill.row, fill.field, fill.targets);
    });

    $(document).on('mousedown', '#table-preview-so td.so-cell', function () {
        if (!$(this).hasClass('so-cell-editing')) soSelect($(this));
    });
    $(document).on('dblclick', '#table-preview-so td.so-cell', function () { soEdit($(this)); });
    $(document).on('keydown', '#table-preview-so td.so-cell', function (e) {
        const $td = $(this);
        if ($td.hasClass('so-cell-editing')) return;
        const keys = { ArrowLeft: 'left', ArrowRight: 'right', ArrowUp: 'up', ArrowDown: 'down' };
        if (keys[e.key]) { e.preventDefault(); soMove($td, keys[e.key]); return; }
        if (e.key === 'Tab') { e.preventDefault(); soMove($td, e.shiftKey ? 'left' : 'right'); return; }
        if (e.key === 'Enter' || e.key === 'F2') { e.preventDefault(); soEdit($td); return; }
        if (e.key === 'Delete' || e.key === 'Backspace') {
            e.preventDefault();
            soCommit([{ row: Number($td.attr('data-row')), field: $td.attr('data-field'), value: '' }]);
            return;
        }
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'c') {
            if (navigator.clipboard) navigator.clipboard.writeText(soCurrentValue(Number($td.attr('data-row')), $td.attr('data-field')));
            return;
        }
        // Ctrl+D: copy nilai dari sel di atasnya (seperti Excel).
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'd') {
            e.preventDefault();
            const $column = soColumnTds($td.attr('data-field'));
            const $above = $column.eq($column.index($td) - 1);
            if ($column.index($td) > 0 && $above.length) soFillCells(Number($above.attr('data-row')), $td.attr('data-field'), [{ row: Number($td.attr('data-row')), field: $td.attr('data-field') }]);
            return;
        }
        if (e.key.length === 1 && !e.ctrlKey && !e.metaKey && !e.altKey) { e.preventDefault(); soEdit($td, e.key); }
    });
    // Paste dari Excel (tab = kolom, baris baru = baris) mulai dari sel aktif.
    $(document).on('paste', '#table-preview-so td.so-cell', function (e) {
        if ($(this).hasClass('so-cell-editing') || !soActive) return;
        const clipboard = e.originalEvent.clipboardData || window.clipboardData;
        const text = clipboard ? clipboard.getData('text') : '';
        if (!text) return;
        e.preventDefault();
        const lines = text.replace(/\r/g, '').replace(/\n+$/, '').split('\n');
        const order = soFieldOrder();
        const startColumn = order.indexOf(soActive.field);
        const changes = [];
        lines.forEach((line, rowOffset) => {
            line.split('\t').forEach((value, columnOffset) => {
                const row = soActive.row + rowOffset;
                const field = order[startColumn + columnOffset];
                if (row < previewUploadRows.length && field) changes.push({ row: row, field: field, value: value });
            });
        });
        if (changes.length) soCommit(changes, { pasted: true });
    });

    @if ($viewOnly ?? false)
    // Detail SO: isi form dari data SO yang tersimpan lalu kunci semua isian.
    function fillSoView(so) {
        $('#id_currency').val(so.id_currency).trigger('change');
        $('#jns_so').val(so.jns_so).trigger('change');
        $('#po_type').val(so.po_type).trigger('change');
        $('#fob').val(so.fob ?? '');
        $('#notes').val(so.notes ?? '');
        $('#total_qty').val(Number(so.total_qty || 0).toLocaleString('id-ID'));
        $('#jumlah_po').val(so.jumlah_po || 0);
        $('#summary-exfty-date').text(formatSummaryDate(so.ex_fty));
        $('#so-header-fields').find('input, select, textarea').prop('disabled', true);
    }

    // Tabel detail SO: paging di sisi browser (Item Per Page + nomor halaman).
    const SO_DETAIL_ROWS = @json($soRows);
    let soDetailPage = 1;

    function soDetailText(value) {
        return $('<div>').text(value === null || value === undefined || value === '' ? '--' : value).html();
    }

    function renderSoDetail() {
        const perPage = Number($('#so-detail-per-page').val()) || 10;
        const pageCount = Math.max(1, Math.ceil(SO_DETAIL_ROWS.length / perPage));
        soDetailPage = Math.min(Math.max(1, soDetailPage), pageCount);
        const start = (soDetailPage - 1) * perPage;

        const body = SO_DETAIL_ROWS.slice(start, start + perPage).map((row, i) => `<tr>
            <td class="so-dt-check"><input type="checkbox" class="so-detail-check"></td>
            <td>${start + i + 1}</td>
            <td>${soDetailText(row.style)}</td>
            <td>${soDetailText(row.description)}</td>
            <td>${soDetailText(row.po)}</td>
            <td>${soDetailText(row.market)}</td>
            <td>${soDetailText(row.destination)}</td>
            <td>${soDetailText(row.ex_fty)}</td>
            <td>${soDetailText(row.color)}</td>
            <td>${soDetailText(row.size)}</td>
            <td class="text-right">${Number(row.qty || 0).toLocaleString('id-ID')}</td>
        </tr>`).join('');
        $('#so-detail-body').html(body || '<tr><td colspan="11" class="text-center">Tidak ada detail SO.</td></tr>');
        $('#so-detail-check-all').prop('checked', false);
        $('#so-detail-page-count').text('Of ' + pageCount);

        // 1 2 3 … n, sama seperti pager Summary Detail di Costing.
        const pages = [];
        for (let p = 1; p <= pageCount; p++) {
            if (p <= 3 || p > pageCount - 3 || Math.abs(p - soDetailPage) <= 1) pages.push(p);
            else if (pages[pages.length - 1] !== '…') pages.push('…');
        }
        $('#so-detail-pages').html(
            `<button type="button" data-page="${soDetailPage - 1}" ${soDetailPage === 1 ? 'disabled' : ''}><i class="fas fa-chevron-left"></i></button>`
            + pages.map(p => p === '…' ? '<span>…</span>' : `<button type="button" data-page="${p}" class="${p === soDetailPage ? 'active' : ''}">${p}</button>`).join('')
            + `<button type="button" data-page="${soDetailPage + 1}" ${soDetailPage === pageCount ? 'disabled' : ''}><i class="fas fa-chevron-right"></i></button>`
        );
    }

    $(function () {
        renderSoDetail();
        $('#so-detail-per-page').on('change', function () { soDetailPage = 1; renderSoDetail(); });
        $('#so-detail-pages').on('click', 'button[data-page]', function () { soDetailPage = Number($(this).data('page')); renderSoDetail(); });
        $('#so-detail-check-all').on('change', function () { $('.so-detail-check').prop('checked', this.checked); });
        $('#btn-so-request-unlock').on('click', function () {
            openUnlockRequest({
                type: 'so',
                id: {{ $so->id }},
                number: @json($so->so_no),
                buyer: @json($soBuyer),
                onSuccess: () => window.location.reload()
            });
        });
    });
    @endif


    // Banner merah di atas form: "N fields are empty. Fill them in, then save again."
    function showMktErrorAlert(selector, count, text) {
        const $alert = $(selector);
        $alert.find('span').text(text || (count + (count === 1 ? ' field is' : ' fields are') + ' empty. Fill them in, then save again.'));
        $alert.prop('hidden', count === 0);
        if (count > 0) $alert[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function refreshSoErrorAlert() {
        if ($('#so-error-alert').prop('hidden')) return;
        const count = $('.so-edit-grid .form-group.is-invalid').length + $('#so-file-dropzone.is-invalid').length;
        $('#so-error-alert').find('span').text(count + (count === 1 ? ' field is' : ' fields are') + ' empty. Fill them in, then save again.');
        $('#so-error-alert').prop('hidden', count === 0);
    }

    $(document).ready(function() {
        let costingImages = [];
        let selectedCostingImage = 0;

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

        function renderCostingImage() {
            const $preview = $('#preview_images');
            const hasImages = costingImages.length > 0;
            $('#text_preview').text(hasImages ? '' : 'Gambar Costing tidak tersedia.')
                .prop('hidden', hasImages);
            $preview.prop('hidden', !hasImages);
            $('.so-image-nav').prop('disabled', costingImages.length < 2);

            if (!hasImages) {
                $preview.attr('src', '');
                $('#image_costing_path').val('');
                paintCoverflow($preview.closest('.mkt-cf-stage'), [], 0, $.noop);
                paintCoverflowDots($('#so-image-counter'), 0, 0, $.noop);
                $('#so-image-thumbs').empty();
                $('#so-image-caption').text('');
                return;
            }

            selectedCostingImage = (selectedCostingImage + costingImages.length) % costingImages.length;
            $preview.attr('src', costingImages[selectedCostingImage]);
            $('#image_costing_path').val(costingImages[selectedCostingImage]);
            const goTo = index => { selectedCostingImage = index; renderCostingImage(); };
            paintCoverflow($preview.closest('.mkt-cf-stage'), costingImages, selectedCostingImage, goTo);
            paintCoverflowDots($('#so-image-counter'), costingImages.length, selectedCostingImage, goTo);
            paintCostingThumbs(goTo);
        }

        function paintCostingThumbs(goTo) {
            const $thumbs = $('#so-image-thumbs').empty();
            $('#so-image-caption').text(costingImages.length ? `Image ${selectedCostingImage + 1} of ${costingImages.length}` : '');
            if (costingImages.length < 2) return;
            costingImages.forEach((url, index) => {
                $('<button type="button">').toggleClass('is-active', index === selectedCostingImage)
                    .attr('aria-label', `Gambar ${index + 1}`)
                    .append($('<img alt="">').attr('src', url))
                    .on('click', () => goTo(index))
                    .appendTo($thumbs);
            });
        }

        $('.so-image-nav[aria-label="Previous image"]').on('click', function() {
            selectedCostingImage--;
            renderCostingImage();
        });
        $('.so-image-nav[aria-label="Next image"]').on('click', function() {
            selectedCostingImage++;
            renderCostingImage();
        });

        // allowClear = tanda X untuk menghapus pilihan (state 2); hanya select di form SO yang punya opsi kosong.
        $('.select2bs4').each(function () {
            const clearable = $(this).closest('.so-edit-grid').length > 0 && $(this).find('option[value=""]').length > 0;
            $(this).select2(Object.assign({ theme: 'bootstrap4', width: '100%' }, clearable ? {
                allowClear: true,
                placeholder: $(this).data('placeholder') || $(this).find('option[value=""]').first().text() || 'Choose'
            } : {}));
        });
        // State 5 (error) hilang begitu field diisi.
        $(document).on('change input', '.so-edit-grid .form-group.is-invalid :input', function () {
            if (String($(this).val() || '').trim()) $(this).closest('.form-group').removeClass('is-invalid');
            refreshSoErrorAlert();
        });
        $(document).on('select2:open', () => { document.querySelector('.select2-container--open .select2-search__field').focus(); });

        $('.so-section-toggle').on('click', function() {
            const $toggle = $(this);
            const $section = $($toggle.attr('data-target'));
            const isExpanded = $toggle.attr('aria-expanded') === 'true';
            $section.collapse('toggle');
            $toggle.attr('aria-expanded', String(!isExpanded));
        });

        $('#product_group').on('change', function() {
            let selected_grup = $(this).val();
            let select_item = $('#id_product_item');

            if(selected_grup) {
                $.post("{{ route('get-product-items') }}", { _token: "{{ csrf_token() }}", product_group: selected_grup }, function(res) {
                    select_item.empty().append('<option value="">Pilih Product Item</option>');
                    $.each(res, function(key, value) {
                        select_item.append(`<option value="${value.id}">${value.product_item}</option>`);
                    });

                    let autoSelectText = select_item.attr('data-auto-select');
                    if (autoSelectText) {
                        let matchingOption = select_item.find("option").filter(function() {
                            return $(this).text() === autoSelectText || $(this).val() == autoSelectText;
                        });

                        if (matchingOption.length > 0) {
                            select_item.val(matchingOption.val());
                        } else {
                            select_item.append(new Option(autoSelectText, autoSelectText, true, true));
                        }

                        select_item.removeAttr('data-auto-select');
                    }

                    select_item.trigger('change');
                    $('#summary-product-item').text(select_item.find(':selected').text() || '-');
                });
            } else {
                select_item.empty().append('<option value="">Pilih Product Item</option>').trigger('change');
                $('#summary-product-item').text('-');
            }
        });

        $(document).on('input', '.input-decimal', function() {
            let val = $(this).val().replace(/[^0-9.]/g, '');
            let parts = val.split('.');
            if (parts.length > 2) val = parts[0] + '.' + parts.slice(1).join('');
            $(this).val(val);
        });

        // $('#images').on('change', function(e) {
        //     let file = e.target.files[0];
        //     let reader = new FileReader();
        //     if (file) {
        //         if (!file.type.match('image.*')) {
        //             Swal.fire('Peringatan!', 'File harus berupa gambar.', 'warning');
        //             $(this).val(''); return;
        //         }
        //         reader.onload = function(e) {
        //             $('#preview_images').attr('src', e.target.result).show();
        //             $('#text_preview').hide();
        //         }
        //         reader.readAsDataURL(file);
        //     }
        // });

        $('#file_so').on('change', function() {
            const file = this.files[0];
            soFileUploaded = false;
            $('#so-selected-file').prop('hidden', !file);
            $('#so-selected-file-name').text(file ? file.name : '');
            $('#btn-upload-so').prop('disabled', !file).text('Upload Now').removeClass('mkt-btn--green').addClass('mkt-btn--navy');
        });

        $('#so-file-dropzone').on('click keydown', function(event) {
            if (event.type === 'keydown' && event.key !== 'Enter' && event.key !== ' ') return;
            event.preventDefault();
            $('#file_so').trigger('click');
        }).on('dragover', function(event) {
            event.preventDefault();
            $(this).addClass('dragging');
        }).on('dragleave drop', function(event) {
            event.preventDefault();
            $(this).removeClass('dragging');
        }).on('drop', function(event) {
            const files = event.originalEvent.dataTransfer.files;
            if (!files.length) return;
            const transfer = new DataTransfer();
            transfer.items.add(files[0]);
            $('#file_so')[0].files = transfer.files;
            $('#file_so').trigger('change');
        });

        $('#so-remove-file').on('click', function() {
            $('#file_so').val('').trigger('change');
        });

        $('#btn-upload-so').on('click', function() {
            const id_costing = $('#id_costing').val();

            let file = $('#file_so')[0].files[0];
            if (!file) return;

            let inputExcel = $('#file_so');
            const uploadButton = $(this);
            uploadButton.prop('disabled', true).text('Uploading...');
            inputExcel.prop('disabled', true);
            let formData = new FormData();
            formData.append('file_so', file);
            formData.append('id_costing', id_costing);
            formData.append('_token', '{{ csrf_token() }}');

            Swal.fire({ title: 'Memproses Excel...', didOpen: () => { Swal.showLoading(); } });

            $.ajax({
                url: "{{ route('so-upload-excel') }}",
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(res) {
                    if (res.status === 200) {
                        soFileUploaded = true;
                        uploadButton.removeClass('mkt-btn--navy').addClass('mkt-btn--green').text('Uploaded');
                        $('#so-file-dropzone').removeClass('is-invalid');
                        Swal.fire({ icon: 'success', title: 'Berhasil!', text: res.message, timer: 1500, showConfirmButton: false });
                        loadPreviewUpload();
                    }
                },
                error: function(xhr) {
                    let res = xhr.responseJSON;
                    if (res && res.errors) {
                        let errorList = Array.isArray(res.errors) ? res.errors : Object.values(res.errors).flat();
                        let errHtml = errorList.map(e => `<li>${e}</li>`).join('');
                        Swal.fire({ icon: 'error', title: res.message || 'Gagal!', html: `<ul class="text-left" style="font-size: 13px;">${errHtml}</ul>` });
                    } else {
                        Swal.fire('Error!', res.message || 'Gagal memproses file.', 'error');
                    }
                },
                complete: function() {
                    inputExcel.prop('disabled', false);
                    if (!soFileUploaded) uploadButton.prop('disabled', false).text('Upload Now');
                }
            });
        });

        $('#form-create-so').on('submit', function(e) {
            e.preventDefault();

            // Sel merah di Sales Order Detail (spasi ganda/di awal-akhir, qty bukan angka, color tidak ada) -> blok.
            const gridErrors = soCountErrors();
            if (gridErrors) {
                showMktErrorAlert('#so-error-alert', gridErrors, gridErrors + (gridErrors === 1 ? ' cell' : ' cells')
                    + ' di Sales Order Detail belum valid (spasi ganda/di awal-akhir, Color/Size/Qty kosong atau salah, atau Color + Size dobel untuk PO, Market & Destination yang sama). Perbaiki sel merah, lalu save lagi.');
                return;
            }

            let empty_input = [];   // data dari Costing (tidak tampil sebagai isian)
            let visibleEmpty = 0;   // isian di form yang kosong -> ditandai merah

            const checkEmpty = (selector, name) => {
                let val = $(selector).val();
                const empty = !val || val.trim() === '';
                // State 5 (error): field yang tampil di form ikut ditandai merah.
                const $group = $(selector).closest('.so-edit-grid .form-group').toggleClass('is-invalid', empty);
                if (!empty) return;
                if ($group.length) visibleEmpty++;
                else empty_input.push(name);
            };

            checkEmpty('select[name="id_buyer"]', 'Buyer');
            checkEmpty('#product_group', 'Product Group');
            checkEmpty('#id_product_item', 'Product Item');
            checkEmpty('#brand', 'Brand');
            checkEmpty('#style', 'Style');
            checkEmpty('#marketing_order', 'Marketing Order');
            checkEmpty('#smv', 'SMV');
            checkEmpty('#id_currency', 'Currency');
            checkEmpty('#jns_so', 'Jenis SO');
            checkEmpty('#fob', 'FOB');
            checkEmpty('#po_type', 'PO System');

            // if ($('#images').get(0).files.length === 0) {
            //     empty_input.push('Upload Gambar');
            // }

            let totalQty = $('#total_qty').val();
            const uploadEmpty = !totalQty || totalQty == 0;
            $('#so-file-dropzone').toggleClass('is-invalid', uploadEmpty);
            if (uploadEmpty) visibleEmpty++;

            showMktErrorAlert('#so-error-alert', visibleEmpty);
            if (visibleEmpty > 0) return;

            if (empty_input.length > 0) {
                let errHtml = empty_input.map(e => `<li>${e}</li>`).join('');
                Swal.fire({
                    icon: 'warning',
                    title: 'Data Costing Belum Lengkap!',
                    html: `Lengkapi data berikut di Costing sebelum membuat SO:<br><br><ul class="text-left" style="font-size: 14px;">${errHtml}</ul>`,
                    confirmButtonText: 'Mengerti'
                });
                return;
            }

            let formData = new FormData(this);

            // PO System harus sesuai isi file; kalau tidak, Save diblok sampai pilihannya benar.
            const poCount = soUploadedPos.length || Number($('#jumlah_po').val()) || 0;
            const poType = $('#po_type').val();
            const poList = soUploadedPos.map(po => $('<div>').text(po).html()).join(', ');
            let poWarning = null;
            if (poType === 'single' && poCount > 1) {
                poWarning = `PO System <b>1 WS 1 PO</b>, tapi file berisi <b>${poCount} PO</b>${poList ? ' (' + poList + ')' : ''}.<br>Pilih <b>1 WS Beberapa PO</b> atau perbaiki file.`;
            } else if (poType === 'multiple' && poCount === 1) {
                poWarning = `PO System <b>1 WS Beberapa PO</b>, tapi file hanya berisi <b>1 PO</b>${poList ? ' (' + poList + ')' : ''}.<br>Pilih <b>1 WS 1 PO</b>.`;
            }

            $('#po_type').closest('.so-edit-grid .form-group').toggleClass('is-invalid', Boolean(poWarning));
            if (poWarning) {
                Swal.fire({ title: 'PO System tidak sesuai file', html: poWarning, icon: 'error', confirmButtonText: 'Perbaiki' });
                return;
            }

            Swal.fire({ title: 'Simpan Sales Order?', icon: 'question', showCancelButton: true, confirmButtonText: 'Ya, Simpan', cancelButtonText: 'Batal' })
            .then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({ title: 'Sedang Proses...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
                    $.ajax({
                        url: "{{ route('so-store') }}", type: "POST", data: formData, processData: false, contentType: false,
                        success: function(res) {
                            Swal.fire({ icon: 'success', title: 'Berhasil!', text: res.message })
                            .then(() => { window.location.href = "{{ route('master-costing') }}"; });
                        },
                        error: function(xhr) {
                            Swal.fire({ icon: 'error', title: 'Gagal', text: xhr.responseJSON.message || 'Error Server' });
                        }
                    });
                }
            });
        });

        $('#btn-back').on('click', function(e) { window.location.href = "{{ route('master-costing') }}"; });

        $.ajax({
            url: "{{ route('so-get-costing-data') }}",
            type: "GET",
            data: { id_costing: $('#id_costing').val() },
            success: function(res) {
                if (res.status == 200) {
                    let data = res.data;

                    $('#brand').val(data.brand);
                    $('#style').val(data.style);
                    $('#market').val(data.market);
                    $('#smv').val(data.smv);

                    $('#id_buyer').val(data.id_buyer).trigger('change');
                    $('#marketing_order').val(data.marketing_order).trigger('change');
                    $('#id_currency').val(data.id_currency).trigger('change');
                    $('#summary-buyer').text(data.buyer_name || $('#id_buyer option:selected').text() || '-');
                    $('#summary-buyer-code').text(data.buyer_code || data.id_buyer || '-');
                    $('#summary-season').text([data.season_name, data.season_desc].filter(Boolean).join(' - ') || data.season_id || '-');
                    $('#summary-style').text(data.style || '-');
                    $('#summary-brand').text(data.brand || '-');
                    $('#summary-planned-qty').text(data.planned_qty ? Number(data.planned_qty).toLocaleString('id-ID') : '-');
                    $('#summary-delivery-date').text(formatSummaryDate(data.delivery_date));
                    $('#summary-product-group').text(data.product_group || '-');
                    $('#summary-worksheet-type').text(data.tipe_ws || '-');
                    $('#summary-worksheet-no').text(data.kpno || '-');
                    $('#summary-costing-type').text(data.type ? String(data.type).toUpperCase() : '-');
                    $('#summary-costing-no').text(data.cost_no || '-');
                    $('#summary-product-type').text('-');
                    $('#summary-unit').text(data.unit || '-');
                    $('#summary-ship-mode').text(data.ship_mode_name || data.ship_mode || '-');
                    $('#summary-market').text(data.market || '-');
                    $('#summary-destination').text(data.main_dest || '-');
                    $('#summary-shipment-type').text(data.shipment_type ? String(data.shipment_type).toUpperCase() : '-');
                    $('#summary-marketing-order').text(data.marketing_order_name || '-');
                    $('#summary-notes').text(data.notes || '-');

                    costingImages = Array.isArray(data.images) && data.images.length
                        ? data.images
                        : (data.image_url ? [data.image_url] : []);
                    selectedCostingImage = 0;
                    renderCostingImage();

                    if (data.nama_product_item) {
                        $('#id_product_item').attr('data-auto-select', data.nama_product_item);
                    }

                    $('#product_group').val(data.product_group).trigger('change');
                    $('#type').val(data.type).trigger('change');

                    if (data.product_set) {
                        let selectedSets = data.product_set.split(',').map(item => item.trim());
                        $('#product_set').val(selectedSets).trigger('change');
                        $('#summary-product-type').text($('#product_set option:selected').map((_, option) => $(option).text()).get().join(', ') || '-');
                    } else {
                        $('#product_set').val(null).trigger('change');
                        $('#summary-product-type').text('-');
                    }

                    @if ($viewOnly ?? false)
                        fillSoView(@json($soView));
                    @endif

                } else {
                    Swal.fire('Peringatan!', res.message, 'warning');
                }
            },
            error: function() {
                Swal.fire('Error', 'Gagal mengambil detail Costing dari server.', 'error');
            }
        });
    });
</script>
@endsection
