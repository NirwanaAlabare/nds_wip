@extends('layouts.index')

@section('custom-link')
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap">
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
    .so-page-heading p { margin: 4px 0 0; color: #294d68; font-size: 11px; font-weight: 500; }
    .so-page-heading p i { margin-right: 6px; color: #a3b2c7; }
    .so-costing-label { color: #64748b; font-size: 12px; font-weight: 700; }
    .so-bom-picker .form-control, .so-bom-picker .btn { height: 36px; font-size: 12px; }
    .so-source-fields { display: none !important; }
    .so-summary-cards { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 12px; margin: 0 0 16px; }
    .so-summary-card { min-width: 0; min-height: 76px; padding: 10px 14px; border: 1px solid #e1e7ef; border-radius: 10px; background: #fff; box-shadow: 0 3px 8px rgba(30, 41, 59, .10); }
    .so-summary-card > span { display: block; margin-bottom: 4px; color: #9a9a9a; font-size: 12px; }
    .so-summary-card > strong { display: block; overflow: hidden; color: #284a63; font-size: 16px; font-weight: 600; text-overflow: ellipsis; white-space: nowrap; }
    .so-summary-card.is-navy { border-color: #294d68; background: #294d68; color: #fff; }
    .so-summary-card.is-navy > span, .so-summary-card.is-navy > strong { color: #fff; }
    .so-summary-card.is-orange { border-color: #ffad2f; background: #ffad2f; }
    .so-summary-card.is-orange > span, .so-summary-card.is-orange > strong { color: #fff; }
    .so-summary-card.is-blue { border-color: #49a8f4; background: #49a8f4; }
    .so-summary-card.is-blue > span, .so-summary-card.is-blue > strong { color: #fff; }
    .so-top-layout { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 14px; align-items: stretch; margin-bottom: 14px; }
    .so-info-column { display: flex; min-width: 0; flex-direction: column; gap: 12px; }
    .so-overview-section { min-width: 0; overflow: hidden; border: 1px solid #e1e7ef; border-radius: 10px; background: #fff; box-shadow: 0 3px 8px rgba(30, 41, 59, .10); }
    .so-overview-title { margin: 0 12px; padding: 9px 0 7px; border-bottom: 1px solid #dce4ec; color: #284a63; font-size: 16px; font-weight: 500; }
    .so-overview-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); column-gap: 22px; padding: 8px 12px 10px; }
    .so-summary-item { display: flex; min-width: 0; min-height: 33px; flex-direction: column; justify-content: center; gap: 2px; padding: 3px 0; border-bottom: 1px solid #e7e7e7; color: #3b3336; font-size: 12px; }
    .so-summary-item > span { overflow: hidden; color: #ababab; font-size: 11px; font-weight: 400; text-overflow: ellipsis; white-space: nowrap; }
    .so-summary-item strong { overflow: hidden; color: #284a63; font-size: 12px; font-weight: 600; text-overflow: ellipsis; white-space: nowrap; }
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
    .so-edit-grid label { display: block; min-height: 27px; margin-bottom: 5px; color: #ababab; font-size: 14px; }
    .so-create-body .so-edit-grid label:not(.form-check-label):not(.custom-file-label) { font-weight: 400; }
    .so-edit-grid .select2-container--bootstrap4 .select2-selection__rendered { color: #ababab; font-size: 14px; font-weight: 500; line-height: 40px; }
    .so-notes-field { grid-column: span 2; }

    .so-image-review { display: flex; min-height: 270px; align-items: center; justify-content: center; gap: 24px; padding: 12px; }
    .so-image-nav { width: 34px; height: 34px; border: 0; border-radius: 50%; color: #7b8791; background: #f1f3f5; }
    .so-image-preview { display: flex; min-width: 160px; min-height: 190px; align-items: center; justify-content: center; color: #64748b; font-size: 12px; }
    .so-image-preview img { max-width: min(100%, 330px); max-height: 250px; object-fit: contain; }
    .so-image-counter { min-width: 50px; color: #64748b; text-align: center; font-size: 11px; margin-bottom: 10px; }
    .so-overview-section .so-image-counter { margin-bottom: 12px; }

    .so-upload-panel { margin-top: 2px; padding: 18px 12px; border: 1px solid #d0d0d0; border-radius: 4px; }
    .so-upload-toolbar { display: flex; justify-content: flex-end; margin-bottom: 14px; }
    .so-upload-toolbar .btn { border-radius: 24px; background: #294d68; border-color: #294d68; font-size: 11px; font-weight: 700; padding: 8px 22px; }

    .so-file-dropzone { display: flex; min-height: 105px; flex-direction: column; align-items: center; justify-content: center; gap: 4px; border: 2px dashed #294d68; border-radius: 4px; color: #555; cursor: pointer; font-size: 10px; }
    .so-file-dropzone > i { color: #294d68; font-size: 28px; margin-bottom: 4px; }
    .so-file-dropzone small { font-size: 9px; }
    .so-file-dropzone.dragging { background: #f1f7fc; }

    .so-selected-file { display: flex; align-items: center; gap: 12px; margin-top: 12px; padding: 9px 12px; border: 1px solid #d8dce0; border-radius: 8px; color: #607080; font-size: 11px; }
    .so-selected-file > i { color: #15803d; font-size: 17px; }
    .so-selected-file span { flex: 1; }
    .so-selected-file button { width: 22px; height: 22px; border: 1px solid #333; border-radius: 5px; background: #fff; }

    #btn-upload-so { height: 38px; font-size: 11px; font-weight: 700; }

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
</style>
@endsection

@section('content')
<div class="card card-sb">
    <form id="form-create-so" action="{{ route('so-store') }}" method="POST" enctype="multipart/form-data" novalidate>
        @csrf
        <div class="card-body so-create-body">
            {{-- <div class="so-breadcrumb">Marketing &nbsp;›&nbsp; Proses &nbsp;›&nbsp; <a href="{{ route('master-costing') }}">Costing</a> &nbsp;›&nbsp; <a href="{{ route('master-marketing-so') }}">Sales Order</a> &nbsp;›&nbsp; <strong>Create Sales Order</strong></div>
             --}}
            <div class="so-breadcrumb">Marketing &nbsp;›&nbsp; Proses &nbsp;›&nbsp; <a href="{{ route('master-costing') }}">Costing</a> &nbsp;›&nbsp; <strong>Create Sales Order</strong></div>
            <div class="so-page-heading">
                <div>
                    <h1>Create Sales Order <i class="far fa-question-circle"></i></h1>
                    <p><i class="fas fa-cog"></i>Untuk Menambah Data SO</p>
                </div>
            </div>
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
                        <div class="so-image-preview">
                            <img id="preview_images" src="" alt="Preview gambar Costing" hidden>
                            <span id="text_preview">Gambar Costing akan ditampilkan di sini.</span>
                        </div>
                        <button type="button" class="so-image-nav" disabled aria-label="Next image"><i class="fas fa-chevron-right"></i></button>
                    </div>
                    <div id="so-image-counter" class="so-image-counter"></div>
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
                            <label>FOB</label><input type="text" name="fob" id="fob" class="form-control input-decimal" placeholder="FOB">
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

            <section class="so-section">
                <button type="button" class="so-section-toggle" data-target="#so-upload-file" aria-expanded="true">
                    <span>Upload File<span class="text-danger">*</span></span><span class="so-chevron"><i class="fas fa-chevron-down"></i></span>
                </button>
                <div id="so-upload-file" class="collapse show">
                    <div class="so-upload-panel">
                        <div class="so-upload-toolbar">
                            <a href="{{ asset('template/template_upload_so.xlsx') }}" class="btn btn-primary btn-sm"><i class="fas fa-download mr-1"></i> Download Template Excel</a>
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
                        <button type="button" id="btn-upload-so" class="btn btn-primary btn-block mt-3" disabled>Upload Now</button>
                    </div>
                </div>
            </section>
        </div>

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

        <div class="card-footer text-right">
            <button type="button" id="btn-back" class="btn btn-secondary">
                <i class="fas fa-times"></i> Batal
            </button>
            <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Simpan</button>
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

    $(document).ready(function() {
        let costingImages = [];
        let selectedCostingImage = 0;

        function renderCostingImage() {
            const $preview = $('#preview_images');
            const hasImages = costingImages.length > 0;
            $('#text_preview').text(hasImages ? '' : 'Gambar Costing tidak tersedia.')
                .prop('hidden', hasImages);
            $preview.prop('hidden', !hasImages);
            $('#so-image-counter').text(hasImages ? `${selectedCostingImage + 1} / ${costingImages.length}` : '');
            $('.so-image-nav').prop('disabled', costingImages.length < 2);

            if (!hasImages) {
                $preview.attr('src', '');
                $('#image_costing_path').val('');
                return;
            }

            selectedCostingImage = (selectedCostingImage + costingImages.length) % costingImages.length;
            $preview.attr('src', costingImages[selectedCostingImage]);
            $('#image_costing_path').val(costingImages[selectedCostingImage]);
        }

        $('.so-image-nav[aria-label="Previous image"]').on('click', function() {
            selectedCostingImage--;
            renderCostingImage();
        });
        $('.so-image-nav[aria-label="Next image"]').on('click', function() {
            selectedCostingImage++;
            renderCostingImage();
        });

        $('.select2bs4').select2({ theme: 'bootstrap4', width: '100%' });
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
            $('#btn-upload-so').prop('disabled', !file).text('Upload Now').removeClass('btn-success').addClass('btn-primary');
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
                        uploadButton.removeClass('btn-primary').addClass('btn-success').text('Uploaded');
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

            let empty_input = [];

            const checkEmpty = (selector, name) => {
                let val = $(selector).val();
                if (!val || val.trim() === '') {
                    empty_input.push(name);
                }
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
            checkEmpty('#po_type', 'PO System');

            // if ($('#images').get(0).files.length === 0) {
            //     empty_input.push('Upload Gambar');
            // }

            let totalQty = $('#total_qty').val();
            if (!totalQty || totalQty == 0) {
                empty_input.push('Upload File Excel (Data Preview masih kosong)');
            }

            if (empty_input.length > 0) {
                let errHtml = empty_input.map(e => `<li>${e}</li>`).join('');
                Swal.fire({
                    icon: 'warning',
                    title: 'Data Belum Lengkap!',
                    html: `Silakan isi kolom berikut sebelum menyimpan:<br><br><ul class="text-left" style="font-size: 14px;">${errHtml}</ul>`,
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
