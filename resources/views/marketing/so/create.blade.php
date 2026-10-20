@extends('layouts.index')

@section('custom-link')
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
    #card-preview .card-body {
        max-height: none;
        overflow: visible;
        padding: 0 1rem 1rem;
    }
    #card-preview .table-responsive { overflow: visible; }

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
    .so-breadcrumb { margin-bottom: 18px; padding: 8px 10px; border-radius: 4px; background: #294d68; color: #b4b4b4; font-size: 16px; font-weight: 500; }
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
    .so-summary-card > span { display: block; margin-bottom: 4px; color: #9e9e9e; font-size: 20px; }
    .so-summary-card > strong { display: block; overflow: hidden; color: #284a63; font-size: 24px; font-weight: 600; text-overflow: ellipsis; white-space: nowrap; }
    .so-summary-card.is-navy { border-color: #294d68; background: #294d68; color: #fff; }
    .so-summary-card.is-navy > span, .so-summary-card.is-navy > strong { color: #fff; }
    .so-summary-card.is-orange { border-color: #ffad2f; background: #ffad2f; }
    .so-summary-card.is-orange > span, .so-summary-card.is-orange > strong { color: #fff; }
    .so-summary-card.is-blue { border-color: #49a8f4; background: #49a8f4; }
    .so-summary-card.is-blue > span, .so-summary-card.is-blue > strong { color: #fff; }
    .so-top-layout { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 14px; align-items: stretch; margin-bottom: 14px; }
    .so-info-column { display: flex; min-width: 0; flex-direction: column; gap: 12px; }
    .so-overview-section { min-width: 0; overflow: hidden; border: 1px solid #e1e7ef; border-radius: 10px; background: #fff; box-shadow: 0 3px 8px rgba(30, 41, 59, .10); }
    .so-overview-title { margin: 0 12px; padding: 9px 0 7px; border-bottom: 1px solid #dce4ec; color: #616161; font-size: 16px; font-weight: 500; }
    .so-overview-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); column-gap: 22px; padding: 8px 12px 10px; }
    .so-summary-item { display: flex; min-width: 0; min-height: 33px; flex-direction: column; justify-content: center; gap: 2px; padding: 3px 0; border-bottom: 1px solid #e7e7e7; color: #3b3336; font-size: 12px; }
    .so-summary-item > span { overflow: hidden; color: #9e9e9e; font-size: 18px; font-weight: 400; text-overflow: ellipsis; white-space: nowrap; }
    .so-summary-item strong { overflow: hidden; color: #284a63; font-size: 18px; font-weight: 600; text-overflow: ellipsis; white-space: nowrap; }
    .so-review-section { display: flex; min-height: 100%; flex-direction: column; }
    .so-review-section .so-image-review { flex: 1; }
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
        background: #fff; color: #284a63; text-align: left; font-family: 'Poppins', sans-serif; font-size: 16px; font-weight: 500;
    }
    .so-section-toggle:focus { outline: 0; box-shadow: 0 0 0 2px rgba(48, 133, 214, .12); }
    .so-section > .collapse.show { border-top: 1px solid #edf1f5; }

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
    /* Dropdown & input sesuai mockup: tinggi 59, radius 12, border 1px, padding 16, gap teks–panah 16,
       teks Poppins Medium 14 (placeholder #ABABAB, terisi hitam), focus border #284A63. */
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
        /* Checkbox sesuai mockup: 16x16, radius 4. Kosong = putih border abu (hover: centang samar),
           checked/indeterminate = #284A63 + ikon putih, focus = cincin biru muda. */
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
    .mkt-cf-stage { position: relative; display: flex; align-items: center; justify-content: center; min-width: 0; height: 260px; overflow: hidden; }
    .mkt-cf-stage > .mkt-cf-main, .mkt-cf-stage > .mkt-cf-side { display: none !important; }
    .mkt-cf-stage > img.mkt-cf-item { position: absolute; top: 50%; left: 50%; max-width: 46%; max-height: 100%; object-fit: contain; cursor: pointer; will-change: transform, opacity; transition: transform .55s cubic-bezier(.22, .61, .36, 1), opacity .55s ease; }
    .mkt-cf-stage > img.mkt-cf-item.is-active { cursor: default; }
    .mkt-cf-stage > img.mkt-cf-item.is-far { opacity: 0 !important; pointer-events: none; }
    .mkt-cf-dots { display: flex; justify-content: center; align-items: center; gap: 4px; margin-top: 10px; min-height: 4px; }
    .mkt-cf-dot { width: 6px; height: 4px; padding: 0; border: 0; border-radius: 2px; background: #d9dee5; cursor: pointer; transition: width .3s ease, background-color .3s ease; }
    .mkt-cf-dot.is-active { width: 16px; background: #1d6fd8; }
    @media (prefers-reduced-motion: reduce) { .mkt-cf-stage > img.mkt-cf-item { transition: none; } }
    .so-image-review .mkt-cf-stage { flex: 1; min-width: 0; }
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
                        <p><i class="fas fa-cog"></i>Untuk Menambah Data SO</p>
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
                    <strong id="summary-planned-qty">-</strong>
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
                        <h2 class="so-overview-title">Information Buyer</h2>
                        <div id="so-information" class="so-overview-grid">
                            <div class="so-summary-item"><span>Buyer Name</span><strong id="summary-buyer">-</strong></div>
                            <div class="so-summary-item"><span>Kode Buyer</span><strong id="summary-buyer-code">-</strong></div>
                            <div class="so-summary-item"><span>Brand</span><strong id="summary-brand">-</strong></div>
                            <div class="so-summary-item"><span>Season</span><strong id="summary-season">-</strong></div>
                            <div class="so-summary-item"><span>Style</span><strong id="summary-style">-</strong></div>
                        </div>
                    </section>

                    <section class="so-overview-section">
                        <h2 class="so-overview-title">Product &amp; Type</h2>
                        <div id="so-product" class="so-overview-grid">
                            <div class="so-summary-item"><span>Product Group</span><strong id="summary-product-group">-</strong></div>
                            <div class="so-summary-item"><span>Product Items</span><strong id="summary-product-item">-</strong></div>
                            <div class="so-summary-item"><span>Product Type</span><strong id="summary-product-type">-</strong></div>
                            <div class="so-summary-item"><span>Unit</span><strong id="summary-unit">-</strong></div>
                            <div class="so-summary-item"><span>Costing Type</span><strong id="summary-costing-type">-</strong></div>
                            <div class="so-summary-item"><span>Worksheet Type</span><strong id="summary-worksheet-type">-</strong></div>
                        </div>
                    </section>

                    <section class="so-overview-section">
                        <h2 class="so-overview-title">Shipping &amp; Destinations</h2>
                        <div id="so-shipping" class="so-overview-grid">
                            <div class="so-summary-item"><span>Ship Mode</span><strong id="summary-ship-mode">-</strong></div>
                            <div class="so-summary-item"><span>Market</span><strong id="summary-market">-</strong></div>
                            <div class="so-summary-item"><span>Destination</span><strong id="summary-destination">-</strong></div>
                            <div class="so-summary-item"><span>Status</span><strong id="summary-status">-</strong></div>
                        </div>
                    </section>
                </div>

                <section class="so-overview-section so-review-section">
                    <h2 class="so-overview-title">Review Image</h2>
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
                </section>
            </div>

            <section class="so-section">
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
                        <div class="form-group">
                            <label>Total (PCS)</label><input type="text" name="total_qty" id="total_qty" class="form-control bg-light input-decimal" placeholder="QTY (PCS)" readonly>
                        </div>
                        <div class="form-group">
                            <label>Total Purchase Order</label><input type="text" name="jumlah_po" id="jumlah_po" class="form-control bg-light input-decimal" placeholder="Total Purchase Order" readonly>
                        </div>
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
                                <td colspan="7">TOTAL</td>
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
            <section class="so-section">
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
        <div class="card card-sb" id="card-preview">
            <div class="card-header bg-warning">
                <h3 class="card-title" style="font-size: 1.1rem;"><i class="fas fa-table"></i> Preview Data Upload</h3>
            </div>
            <div class="card-body">
                {{-- <button type="button" id="btn-reload-preview" class="btn btn-info mb-2" onclick="loadPreviewUpload()"><i class="fas fa-refresh"></i> Reload Table</button> --}}
                <div class="table-responsive">
                    <table class="table table-sm table-striped text-nowrap w-100" id="table-preview-so">
                        <thead class="bg-sb text-white text-center">
                            <tr>
                                <th class="preview-col-style">Style</th>
                                <th class="preview-col-desc">Description</th>
                                <th class="preview-col-po">Purchase Order</th>
                                <th class="preview-col-market">Market</th>
                                <th class="preview-col-exfty">Ex Fty</th>
                                <th class="preview-col-fob">FOB</th>
                                <th class="preview-col-set">Product Set</th>
                                <th class="preview-col-color">Color</th>
                                <th class="preview-col-total">Total</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

        @endunless

        @if ($viewOnly ?? false)
            @include('marketing.costing.unlock_request_modal')
        @endif

        <div class="card-footer text-right">
            @if ($viewOnly ?? false)
                <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('master-costing') }}" class="mkt-btn mkt-btn--gray">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            @else
            <button type="button" id="btn-back" class="mkt-btn mkt-btn--red mkt-btn--cancel">
                <i class="fas fa-times"></i> Batal
            </button>
            <button type="submit" class="mkt-btn mkt-btn--green">Save</button>
            @endif
        </div>
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
    let soFileUploaded = false;

    function formatSummaryDate(value) {
        if (!value) return '-';
        const match = String(value).trim().match(/^(\d{4})-(\d{2})-(\d{2})/);
        return match ? `${match[3]}/${match[2]}/${match[1]}` : String(value);
    }

    function loadPreviewUpload() {
        $('#card-preview').show();
        $('#table-preview-so tbody').html('<tr><td colspan="15" class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading data...</td></tr>');
        $('#summary-exfty-date').text('-');

        $.ajax({
            url: "{{ route('so-get-temp-data') }}",
            type: 'GET',
            data: { id_costing: $('#id_costing').val() },
            success: function(res) {
                if ($.fn.DataTable.isDataTable('#table-preview-so')) {
                    $('#table-preview-so').DataTable().destroy();
                }

                // Header: 8 kolom non-size (Style, Desc, PO, Market, Ex Fty, FOB, Product Set, Color) + size + Total
                let headerHtml = `<tr>
                    <th class="preview-col-style">Style</th>
                    <th class="preview-col-desc">Description</th>
                    <th class="preview-col-po">Purchase Order</th>
                    <th class="preview-col-market">Market</th>
                    <th class="preview-col-exfty">Ex Fty</th>
                    <th class="preview-col-fob">FOB</th>
                    <th class="preview-col-set">Product Set</th>
                    <th class="preview-col-color">Color</th>`;

                res.available_sizes.forEach(size => {
                    headerHtml += `<th class="preview-col-size">${size}</th>`;
                });

                headerHtml += `<th class="preview-col-total">Total</th>
                </tr>`;

                $('#table-preview-so thead').html(headerHtml);

                let bodyHtml = '';
                if (res.data && res.data.length > 0) {
                    const exFactoryDate = res.data.find(row => row.ex_fty);
                    $('#summary-exfty-date').text(formatSummaryDate(exFactoryDate ? exFactoryDate.ex_fty : null));
                    res.data.forEach(row => {
                        let colorErrorClass = row.color_error ? 'bg-danger text-white font-weight-bold' : '';

                        bodyHtml += `<tr>
                            <td class="preview-col-style" title="${row.style || '-'}">${row.style || '-'}</td>
                            <td class="preview-col-desc" title="${row.desc || '-'}">${row.desc || '-'}</td>
                            <td class="preview-col-po" title="${row.po || '-'}">${row.po || '-'}</td>
                            <td class="preview-col-market" title="${row.market || '-'}">${row.market || '-'}</td>
                            <td class="preview-col-exfty">${row.ex_fty || '-'}</td>
                            <td class="preview-col-fob text-right">${row.fob || '-'}</td>
                            <td class="preview-col-set" title="${row.product_set || '-'}">${row.product_set || '-'}</td>
                            <td class="preview-col-color ${colorErrorClass}" title="${row.color || '-'}">${row.color || '-'}</td>`;

                        let rowTotal = 0;
                        res.available_sizes.forEach(size => {
                            let val = row[size];
                            let qty = val ? Number(val).toLocaleString('id-ID') : '-';
                            let isError = (row.errors && row.errors[size]) ? 'bg-danger text-white font-weight-bold' : '';
                            bodyHtml += `<td class="preview-col-size preview-col-qty ${isError}">${qty}</td>`;

                            if (val && !isNaN(val)) {
                                rowTotal += Number(val);
                            }
                        });
                        let rowTotalFormatted = rowTotal > 0 ? rowTotal.toLocaleString('id-ID') : '-';
                        bodyHtml += `<td class="preview-col-total fw-bold">${rowTotalFormatted}</td></tr>`;
                    });
                } else {
                    let colCount = 9 + (res.available_sizes ? res.available_sizes.length : 0);
                    bodyHtml = `<tr><td colspan="${colCount}" class="text-center">Belum ada data di-upload.</td></tr>`;
                }
                $('#table-preview-so tbody').html(bodyHtml);

                if ($('#table-preview-so tfoot').length === 0) $('#table-preview-so').append('<tfoot></tfoot>');

                let footerHtml = `<tr>
                    <th class="text-left fw-bold">TOTAL</th>
                    <th></th><th></th><th></th><th></th><th></th><th></th><th></th>`;

                res.available_sizes.forEach(size => {
                    footerHtml += `<th class="preview-col-size preview-col-qty fw-bold">0</th>`;
                });

                footerHtml += `<th class="preview-col-total fw-bold">0</th>
                </tr>`;

                $('#table-preview-so tfoot').html(footerHtml);

                $('#total_qty').val(res.total_qty || 0);
                if (Number(res.total_qty) > 0) { $('#so-file-dropzone').removeClass('is-invalid'); refreshSoErrorAlert(); }
                $('#jumlah_po').val(res.jumlah_po || 0);

                if (res.data && res.data.length > 0) {
                    previewTable = $('#table-preview-so').DataTable({
                        "paging": false,
                        "info": true,
                        "searching": true,
                        "ordering": false,
                        "autoWidth": false,
                        "responsive": false,
                        "scrollX": false,
                        "columnDefs": [
                            { targets: 0, width: '145px' },
                            { targets: 1, width: '155px' },
                            { targets: 2, width: '135px' },
                            { targets: 3, width: '95px' },
                            { targets: 4, width: '105px' },
                            { targets: 5, width: '85px' },
                            { targets: 6, width: '115px' },
                            { targets: 7, width: '125px' },
                            { targets: '_all', className: 'text-center' }
                        ],
                        "footerCallback": function (row, data, start, end, display) {
                            let api = this.api();
                            let intVal = function (i) {
                                if (typeof i === 'string') {
                                    let val = i.replace(/\./g, '');
                                    return val === '-' ? 0 : val * 1;
                                }
                                return typeof i === 'number' ? i : 0;
                            };

                            // Kolom non-size = 8 (Style, Desc, PO, Market, Ex Fty, FOB, Product Set, Color)
                            let startColIndex = 8;
                            let numSizes = res.available_sizes.length;
                            for (let i = 0; i < numSizes; i++) {
                                let colIndex = startColIndex + i;
                                let colData = api.column(colIndex, { search: 'applied' }).data();
                                let colTotal = colData.length
                                    ? colData.toArray().reduce((sum, v) => sum + intVal(v), 0)
                                    : 0;
                                $(api.column(colIndex).footer()).html(colTotal.toLocaleString('id-ID'));
                            }

                            let grandTotalColIndex = startColIndex + numSizes;
                            let gtData = api.column(grandTotalColIndex, { search: 'applied' }).data();
                            let grandTotalValue = gtData.length
                                ? gtData.toArray().reduce((sum, v) => sum + intVal(v), 0)
                                : 0;
                            $(api.column(grandTotalColIndex).footer()).html(grandTotalValue.toLocaleString('id-ID'));
                        },
                        "drawCallback": function(settings) {
                            let api = this.api();
                            setTimeout(function() { api.columns.adjust(); }, 10);
                        }
                    });
                }

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
                $('#table-preview-so tbody').html('<tr><td colspan="10" class="text-center text-danger">Gagal memuat data preview.</td></tr>');
            }
        });
    }

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
            <td>${soDetailText(row.ex_fty)}</td>
            <td>${soDetailText(row.color)}</td>
            <td>${soDetailText(row.size)}</td>
            <td class="text-right">${Number(row.qty || 0).toLocaleString('id-ID')}</td>
        </tr>`).join('');
        $('#so-detail-body').html(body || '<tr><td colspan="10" class="text-center">Tidak ada detail SO.</td></tr>');
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
            const gap = stage.clientWidth * 0.36;
            $items.each(function (index) {
                const offset = index - active, far = Math.abs(offset) > 1;
                const x = Math.max(-2, Math.min(2, offset)) * gap;
                const scale = offset === 0 ? 1 : (far ? 0.4 : 0.55);
                if (fresh) this.style.transition = 'none'; // posisi awal tanpa animasi
                this.style.transform = `translate(-50%, -50%) translateX(${x}px) scale(${scale})`;
                this.style.zIndex = String(10 - Math.min(Math.abs(offset), 9));
                this.style.opacity = offset === 0 ? '1' : '0.85';
                $(this).toggleClass('is-active', offset === 0).toggleClass('is-far', far);
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
                return;
            }

            selectedCostingImage = (selectedCostingImage + costingImages.length) % costingImages.length;
            $preview.attr('src', costingImages[selectedCostingImage]);
            $('#image_costing_path').val(costingImages[selectedCostingImage]);
            const goTo = index => { selectedCostingImage = index; renderCostingImage(); };
            paintCoverflow($preview.closest('.mkt-cf-stage'), costingImages, selectedCostingImage, goTo);
            paintCoverflowDots($('#so-image-counter'), costingImages.length, selectedCostingImage, goTo);
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
            checkEmpty('#market', 'Market');
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
            Swal.fire({
                title: 'Simpan Sales Order?', icon: 'question', showCancelButton: true, confirmButtonText: 'Ya, Simpan', cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({ title: 'Sedang Proses...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
                    $.ajax({
                        url: "{{ route('so-store') }}", type: "POST", data: formData, processData: false, contentType: false,
                        success: function(res) {
                            Swal.fire({ icon: 'success', title: 'Berhasil!', text: res.message })
                            .then(() => { window.location.href = "{{ route('master-marketing-so') }}"; });
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
                    $('#summary-status').text(data.status || '-');

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
