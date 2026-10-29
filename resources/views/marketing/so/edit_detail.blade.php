@extends('layouts.index')

@section('custom-link')
    <link rel="stylesheet" href="{{ asset('dist/css/marketing-form.css') }}?v={{ @filemtime(public_path('assets/dist/css/marketing-form.css')) }}">
    <link rel="stylesheet" href="{{ asset('dist/css/marketing-buttons.css') }}?v={{ @filemtime(public_path('assets/dist/css/marketing-buttons.css')) }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

<style>
    /* ===== Edit Sales Order (Marketing) — semua Poppins: judul 700, tombol/subjudul 600, label/isi/tabel 500 ===== */
    .so-page, .so-page input, .so-page select, .so-page button, .so-page table, .so-s2-dropdown { font-family: 'Poppins', sans-serif; }
    .card-sb { border: none; border-radius: 18px; overflow: hidden; box-shadow: 0 5px 12px -3px rgba(30, 41, 59, .18), 0 2px 4px rgba(30, 41, 59, .07); }
    .so-page.card-body { padding: 1rem 1.4rem 1.4rem; background: #fff; }
    .so-breadcrumb { margin-bottom: 18px; padding: 8px 10px; border-radius: 4px; background: #284A63; color: #b4b4b4; font-size: 16px; font-weight: 500; }
    .so-breadcrumb a { color: #b4b4b4; text-decoration: none; }
    .so-breadcrumb a:hover { color: #fff; text-decoration: underline; }
    .so-breadcrumb strong { color: #fff; font-weight: 500; }
    .so-page-heading { margin-bottom: 14px; }
    .so-page-heading h1 { margin: 0; color: #284a63; font-size: 32px; font-weight: 700; letter-spacing: 0; }
    .so-page-heading h1 i { font-size: 11px; vertical-align: middle; }
    .so-page-heading p { margin: 4px 0 0; color: #284a63; font-size: 12px; font-weight: 600; }
    .so-page-heading p i { margin-right: 6px; color: #a3b2c7; }

    /* Kartu ringkasan (sama dengan Detail SO). */
    .so-summary-cards { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; margin: 0 0 16px; }
    .so-summary-card { min-width: 0; min-height: 76px; padding: 10px 14px; border: 1px solid #e1e7ef; border-radius: 10px; background: #fff; box-shadow: 0 3px 8px rgba(30, 41, 59, .10); }
    .so-summary-card > span { display: block; margin-bottom: 4px; color: #9e9e9e; font-size: 20px; font-weight: 500; letter-spacing: 0.2px; }
    .so-summary-card > strong { display: block; overflow: hidden; color: #284a63; font-size: 24px; font-weight: 700; letter-spacing: 0.05em; text-overflow: ellipsis; white-space: nowrap; }
    .so-summary-card > span, .so-summary-card > strong { font-family: "Instrument Sans", 'Poppins', sans-serif; }

    /* Banner info (hijau) dan kunci marker (merah). */
    .so-banner { display: flex; align-items: center; gap: 10px; margin-bottom: 12px; padding: 10px 12px; border-radius: 6px; font-size: 13px; font-weight: 500; }
    .so-banner > span { flex: 1; min-width: 0; }
    .so-banner b { font-weight: 700; }
    .so-banner.is-info { border: 1px solid #abefc6; background: #ecfdf3; color: #05603a; }
    .so-banner.is-info > i { color: #12b76a; font-size: 18px; }
    /* Notif hijau "belum sampai marker": Inter Medium 12. */
    .so-banner.is-info, .so-banner.is-info > span { font-family: Inter, sans-serif; font-size: 12px; font-weight: 500; }
    .so-banner.is-lock { margin-bottom: 16px; background: #ff5f5f; color: #fff; }
    .so-banner.is-lock > i { font-size: 18px; }
    .so-banner.is-lock small { display: block; margin-top: 2px; font-size: 12px; font-weight: 500; opacity: .95; }
    .so-banner-close { flex: 0 0 auto; padding: 0; border: 0; background: none; color: inherit; font-size: 18px; line-height: 1; cursor: pointer; }
    .mkt-error-alert { display: flex; align-items: center; gap: 8px; margin-bottom: 14px; padding: 10px 12px; border-radius: 4px; background: #ff5f5f; color: #fff; font-size: 13px; font-weight: 500; }
    .mkt-error-alert[hidden] { display: none; }
    .mkt-error-alert > span { flex: 1; white-space: pre-line; }
    .mkt-error-alert-close { flex: 0 0 auto; padding: 0; border: 0; background: none; color: #fff; font-size: 16px; line-height: 1; }

    .so-layout { display: grid; grid-template-columns: minmax(0, 3fr) minmax(0, 1fr); gap: 20px; align-items: stretch; margin-bottom: 20px; }
    .so-grid-column { display: flex; flex-direction: column; min-width: 0; }
    .so-grid-column > .so-card { flex: 1 1 auto; }
    .so-card { min-width: 0; padding: 16px 18px; border-radius: 14px; background: #fff; box-shadow: 0 3px 10px rgba(15, 23, 42, .10); }
    /* Tabel + pager dalam kotak putih sendiri di dalam kartu. */
    .so-inner-card { padding: 8px 14px 10px; border-radius: 12px; background: #fff; box-shadow: 0 2px 8px rgba(15, 23, 42, .08); }
    .so-toolbar { display: flex; align-items: center; justify-content: flex-end; gap: 8px; min-height: 0; }
    .so-toolbar:not(:empty) { margin-bottom: 8px; }
    .so-bulk-delete { display: inline-flex; align-items: center; gap: 6px; height: 30px; padding: 0 14px; border: 0; border-radius: 15px; background: #ff0000; color: #fff; font-size: 13px; font-weight: 600; box-shadow: 0 3px 6px rgba(255, 0, 0, .3); }
    .so-bulk-delete:hover { background: #d90000; }
    .so-scroll { overflow-x: auto; border-radius: 8px; }
    .so-scroll::-webkit-scrollbar { height: 8px; }
    .so-scroll::-webkit-scrollbar-track { background: #e2e8f0; border-radius: 4px; }
    .so-scroll::-webkit-scrollbar-thumb { background: #284a63; border-radius: 4px; }
    .so-scroll { scrollbar-color: #284a63 #e2e8f0; }

    .so-table { width: 100%; margin: 0; border-collapse: collapse; }
    /* Header tabel = Overline: 12px, weight 500, huruf kapital (!important karena layout memaksa th 13.5px). */
    .so-table th { padding: 10px 8px; background: #cfe2ff; color: #1e293b; font-size: 12px !important; font-weight: 500 !important; line-height: 16px; letter-spacing: .5px; text-transform: uppercase; white-space: nowrap; vertical-align: middle; }
    /* Isi tabel: Inter Regular 14 / line-height 20, angka lining + tabular (!important karena layout memaksa td 13.5px). */
    .so-table td { padding: 8px; border-bottom: 1px solid #edf1f5; color: #1e293b; font-family: Inter, sans-serif; font-size: 14px !important; font-weight: 400; line-height: 20px; letter-spacing: 0; white-space: nowrap; vertical-align: middle; font-variant-numeric: lining-nums tabular-nums; }
    .so-table tbody tr:last-child td { border-bottom: 0; }
    .so-table .so-col-check { width: 34px; text-align: center; }
    .so-table .so-col-no { width: 54px; }
    .so-table .so-col-action { width: 70px; text-align: center; }
    .so-table .text-right { text-align: right; }
    #so-grid { min-width: 640px; }
    #so-grid th.so-sortable { cursor: pointer; user-select: none; }
    #so-grid th .so-sort { margin-left: 4px; color: #64748b; font-size: 10px; }
    #so-grid th.is-sorted .so-sort { color: #284a63; }
    /* Sel ala Excel: putih = editable, abu = otomatis/terkunci, biru = baru di-paste, merah = salah. */
    #so-grid td.so-cell { position: relative; cursor: cell; white-space: pre; }
    #so-grid td.so-cell:focus { outline: none; }
    #so-grid td.so-cell-auto { background: #f1f4f8; color: #475569; }
    #so-grid td.so-cell-active { box-shadow: inset 0 0 0 2px #1d6fd8; }
    #so-grid td.so-cell-selected { background: #e8f1ff; }
    #so-grid td.so-cell-selected.so-cell-active { background: #dbeafe; box-shadow: inset 0 0 0 2px #1d6fd8; }
    #so-grid td.so-cell-fill-preview { background: #bfdbfe; }
    #so-grid tr.is-deleted td { background: #fff1f2; color: #9f1239; }
    #so-grid td.so-cell-deleted { color: #9f1239; text-decoration: line-through; }
    #so-grid td.so-cell-pasted { background: #dbeafe; }
    #so-grid td.so-cell-error { background: #ffe4e6; box-shadow: inset 0 0 0 2px #ef4444; color: #b91c1c; }
    #so-grid td.so-cell-error.so-cell-active { box-shadow: inset 0 0 0 2px #1d6fd8, inset 0 0 0 4px #ef4444; }
    #so-grid td.so-cell-editing { padding: 0; overflow: visible; }
    #so-grid .so-fill-handle { position: absolute; z-index: 2; right: -4px; bottom: -4px; width: 8px; height: 8px; border: 1px solid #fff; background: #1d6fd8; cursor: crosshair; }
    #so-grid .so-cell-input { width: 100%; min-height: 34px; padding: 4px 8px; border: 0; outline: 2px solid #1d6fd8; background: #fff; color: #1e293b; font: inherit; }
    #so-grid tr.is-new td.so-col-no::after { content: 'NEW'; margin-left: 6px; padding: 1px 5px; border-radius: 6px; background: #dbeafe; color: #1d4ed8; font-size: 9px; font-weight: 600; }
    #so-grid tr.is-locked td.so-col-no i { margin-left: 4px; color: #94a3b8; font-size: 10px; }
    .so-row-restore { display: inline-flex; align-items: center; justify-content: center; min-height: 28px; padding: 0 9px; border: 0; border-radius: 14px; background: #e0f2fe; color: #075985; font-size: 11px; font-weight: 600; }
    .so-row-restore:hover { background: #bae6fd; }
    .so-deleted-badge { display: inline-block; margin-left: 4px; padding: 1px 5px; border-radius: 8px; background: #ffe4e6; color: #be123c; font-size: 9px; font-weight: 700; vertical-align: middle; }
    .so-empty { padding: 18px 8px !important; color: #64748b !important; text-align: center; white-space: normal !important; }

    .so-detail-pager { display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap; padding: 14px 16px 6px; color: #1e293b; font-size: 16px; font-weight: 400; }
    .so-detail-pager > div:first-child { display: inline-flex; align-items: center; gap: 12px; }
    .so-detail-pager select { height: 40px; min-width: 76px; padding: 0 10px; border: 1px solid #284a63; border-radius: 8px; font-size: 16px; }
    .so-detail-pages { display: inline-flex; align-items: center; flex-wrap: wrap; gap: 8px; color: #64748b; font-size: 16px; }
    .so-detail-pages button { min-width: 32px; height: 32px; padding: 0 6px; border: 0; border-radius: 6px; background: transparent; color: #64748b; font-size: 16px; font-weight: 500; }
    .so-detail-pages button.active { background: #274b63; color: #fff; font-weight: 600; }
    .so-detail-pages button:disabled { opacity: .35; }
    .so-preview-legend { display: flex; flex-wrap: wrap; gap: 8px 18px; margin: 16px 2px 0; color: #616161; font-size: 14px; font-weight: 400; }
    .so-preview-legend > span { display: inline-flex; align-items: center; gap: 8px; }
    .so-preview-legend i { width: 16px; height: 16px; border: 1px solid #cbd5e1; border-radius: 3px; background: #fff; }
    .so-preview-legend .is-auto { background: #f1f4f8; }
    .so-preview-legend .is-pasted { border-color: #93c5fd; background: #dbeafe; }
    .so-preview-legend .is-error { border-color: #fca5a5; background: #ffe4e6; }

    /* Panel Add New (tombol Add New di bawah kotak, sejajar akhir kotak kiri) */
    .so-add-form { display: flex; flex-direction: column; padding-top: 22px; }
    /* Size sedikit lebih lebar dari Qty supaya "CHOOSE SIZE" tidak terpotong. */
    .so-add-row { display: grid; grid-template-columns: minmax(0, 3fr) minmax(0, 2fr); gap: 12px; }
    /* Semua field panel Add New setinggi 59px (dropdown & isian) di semua ukuran layar. */
    .so-add-form .select2-container--bootstrap4 .select2-selection--single { height: 59px !important; min-height: 59px !important; }
    .so-add-form .costing-field { min-width: 0; margin-bottom: 12px; }
    /* Label panel Add New: Poppins Medium 14, huruf kapital (semua ukuran layar). */
    .so-add-form .costing-field > label { display: block; margin-bottom: 6px; font-family: 'Poppins', sans-serif !important; font-size: 14px !important; font-weight: 500 !important; text-transform: uppercase; }
    .so-add-form .form-control { height: 59px; padding: 0 16px; border: 1px solid #d9d9d9; border-radius: 12px; box-shadow: none; }
    /* Isian panel Add New (input, dropdown, placeholder): Poppins Medium 12. */
    #so-add-form .costing-field input.form-control, #so-add-form .costing-field input.form-control::placeholder,
    #so-add-form .costing-field .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered,
    #so-add-form .costing-field .select2-container--bootstrap4 .select2-selection__placeholder { font-family: 'Poppins', sans-serif !important; font-size: 12px !important; font-weight: 500 !important; }
    .so-add-form .form-control:focus { border-color: #284a63; box-shadow: none; }
    .so-field-msg { display: none; margin-top: 4px; color: #c8161d; font-size: 13px; font-weight: 500; }
    .costing-field.is-invalid .so-field-msg { display: block; }
    .so-add-actions { display: flex; justify-content: flex-end; margin-top: auto; padding-top: 12px; }

    /* Section Sales Order Detail (pivot, baca-saja). */
    .so-section { padding: 16px 18px 20px; border-radius: 14px; background: #fff; box-shadow: 0 3px 10px rgba(15, 23, 42, .10); }
    .so-section-title { margin-bottom: 14px; padding: 10px 22px; border: 1px solid #284a63; border-radius: 8px; color: #284a63; font-size: 25px; font-weight: 500; }
    #so-pivot { min-width: 760px; }
    /* Baris GRAND TOTAL: Inter Bold 14 / line-height 20, angka lining + tabular (lebar digit sama). */
    #so-pivot tfoot td { height: 36px; border: 0; background: #ffd6dc; font-family: Inter, sans-serif !important; font-size: 14px !important; font-weight: 700 !important; line-height: 20px; letter-spacing: 0; vertical-align: middle; font-variant-numeric: lining-nums tabular-nums; }
    #so-pivot .so-col-total { font-weight: 600; }

    .so-footer { display: flex; justify-content: flex-end; gap: 14px; padding-top: 24px; }
    /* Tombol (Add New, Cancel, Update): SF Pro Rounded Bold 18, line-height 40, teks di tengah. */
    .so-page .mkt-btn { font-family: "SF Pro Rounded", ui-rounded, "MKT Rounded", sans-serif !important; font-size: 18px !important; font-weight: 700 !important; line-height: 40px !important; letter-spacing: 0 !important; text-align: center; vertical-align: middle; }

    /* Checkbox seragam dengan halaman SO. */
    .so-page input[type="checkbox"] { -webkit-appearance: none; appearance: none; width: 16px; height: 16px; margin: 0; padding: 0; border: 1.5px solid #cfd4dc; border-radius: 4px; background: #fff no-repeat center / 10px 10px; vertical-align: middle; cursor: pointer; }
    .so-page input[type="checkbox"]:checked { border-color: #284a63; background-color: #284a63; background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12'%3e%3cpath fill='none' stroke='%23fff' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' d='M2.5 6.2l2.3 2.3 4.7-4.9'/%3e%3c/svg%3e"); }
    .so-page input[type="checkbox"]:indeterminate { border-color: #284a63; background-color: #284a63; background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12'%3e%3cpath fill='none' stroke='%23fff' stroke-width='2' stroke-linecap='round' d='M3 6h6'/%3e%3c/svg%3e"); }
    .so-page input[type="checkbox"]:disabled { opacity: .45; cursor: not-allowed; }
    .so-page input[type="checkbox"]:focus-visible { outline: 0; box-shadow: 0 0 0 4px #b3daf7; }

    @media (max-width: 1199.98px) {
        .so-summary-card > span { font-size: 16px; }
        .so-summary-card > strong { font-size: 18px; }
    }
    @media (max-width: 991.98px) {
        .so-layout { grid-template-columns: minmax(0, 1fr); }
        .so-summary-cards { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
        .so-summary-card { min-height: 0; }
        .so-summary-card > span { font-size: 14px; }
        .so-summary-card > strong { font-size: 18px; overflow-wrap: anywhere; white-space: normal; }
    }
    @media (max-width: 575.98px) {
        .so-page.card-body { padding: .9rem .8rem 1rem; }
        .so-breadcrumb { font-size: 13px; }
        .so-page-heading h1 { font-size: 24px; }
        .so-summary-cards { gap: 8px; }
        .so-summary-card { padding: 8px 10px; }
        .so-summary-card > span { font-size: 13px; }
        .so-summary-card > strong { font-size: 16px; }
        .so-section-title { padding: 8px 14px; font-size: 18px; }
        .so-banner { flex-wrap: wrap; }
        .so-banner > span { flex-basis: calc(100% - 60px); }
        .so-footer .mkt-btn--cancel, .so-footer .mkt-btn { flex: 1 1 0; min-width: 0; }
    }
</style>
@endsection

@section('content')
@php
    $isLocked = (bool) $markerLock;
    $kpno = $costing->kpno ?? '-';
@endphp
<div class="card card-sb">
    <div class="card-body so-page">
        <div class="so-breadcrumb">Marketing &nbsp;›&nbsp; <a href="{{ route('master-costing') }}">Proses</a> &nbsp;›&nbsp; Sales Order &nbsp;›&nbsp; <strong>Edit Sales Order</strong></div>
        <div class="so-page-heading">
            <h1>Edit Sales Order <i class="far fa-question-circle"></i></h1>
            <p><i class="fa-solid fa-circle-info"></i>Untuk Mengubah Detail SO</p>
        </div>

        <div class="so-summary-cards" aria-label="Ringkasan SO">
            <div class="so-summary-card"><span>No Costing</span><strong title="{{ $costing->cost_no ?? '-' }}">{{ $costing->cost_no ?? '-' }}</strong></div>
            <div class="so-summary-card"><span>No Worksheet</span><strong title="{{ $kpno }}">{{ $kpno }}</strong></div>
            <div class="so-summary-card"><span>No Sales Order</span><strong title="{{ $so->so_no }}">{{ $so->so_no }}</strong></div>
            <div class="so-summary-card"><span>Delivery Date</span><strong>{{ !empty($costing->deldate) ? date('d/m/Y', strtotime($costing->deldate)) : '-' }}</strong></div>
        </div>

        @if ($isLocked)
            <div class="so-banner is-lock" id="so-lock-banner" role="alert">
                <i class="fas fa-info-circle"></i>
                <span>WS <b>{{ $kpno }}</b> sudah sampai marker (dibuat <b>{{ $markerLock['created_by'] }}</b>, {{ $markerLock['created_at'] }}).<small>Table hanya read only tidak bisa di edit.</small></span>
                <button type="button" class="so-banner-close" aria-label="Tutup" onclick="this.parentElement.remove()"><i class="far fa-times-circle"></i></button>
            </div>
        @else
            <div class="mkt-error-alert" id="so-error-alert" role="alert" hidden>
                <i class="fas fa-exclamation-circle"></i><span></span>
                <button type="button" class="mkt-error-alert-close" aria-label="Tutup" onclick="this.parentElement.hidden = true"><i class="far fa-times-circle"></i></button>
            </div>
        @endif

        @unless ($isLocked)
        <div class="so-layout">
            <div class="so-grid-column">
                <section class="so-card">
                    <div class="so-banner is-info" id="so-info-banner">
                        <i class="fas fa-check-circle"></i>
                        <span>WS <b>{{ $kpno }}</b> belum sampai marker. <b>Market, Qty,</b> dan <b>FOB</b> bisa diubah langsung di tabel.</span>
                        <button type="button" class="so-banner-close" aria-label="Tutup" onclick="this.parentElement.remove()"><i class="far fa-times-circle"></i></button>
                    </div>
                    <div class="so-toolbar" id="so-toolbar"></div>
                    <div class="so-inner-card">
                    <div class="so-scroll">
                        <table class="so-table" id="so-grid">
                            <thead>
                                <tr>
                                    <th class="so-col-check"><input type="checkbox" id="so-check-all" aria-label="Pilih semua"></th>
                                    <th class="so-col-no so-sortable" data-sort="no">No <i class="fas fa-sort so-sort"></i></th>
                                    <th class="so-sortable" data-sort="color">Color <i class="fas fa-sort so-sort"></i></th>
                                    <th class="so-sortable" data-sort="size">Size <i class="fas fa-sort so-sort"></i></th>
                                    <th class="so-sortable text-right" data-sort="qty">Qty <i class="fas fa-sort so-sort"></i></th>
                                    <th class="so-sortable" data-sort="market">Market <i class="fas fa-sort so-sort"></i></th>
                                    <th>Destination</th>
                                    <th class="so-sortable text-right" data-sort="fob">FOB <i class="fas fa-sort so-sort"></i></th>
                                    <th class="so-col-action">Action</th>
                                </tr>
                            </thead>
                            <tbody id="so-grid-body"></tbody>
                        </table>
                    </div>
                    <div class="so-detail-pager">
                        <div>
                            <span>Item Per Page</span>
                            <select id="so-per-page" aria-label="Item per page">
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                            <span id="so-page-count">Of 1</span>
                        </div>
                        <div class="so-detail-pages" id="so-pages"></div>
                    </div>
                    </div>
                    <div class="so-preview-legend" aria-label="Keterangan tabel">
                        <span><i></i>Editable</span>
                        <span><i class="is-auto"></i>Auto calculated</span>
                        <span><i class="is-pasted"></i>Just Pasted</span>
                        <span><i class="is-error"></i>Error</span>
                    </div>
                </section>
            </div>

            <section class="so-card so-add-form" id="so-add-form">
                <div class="costing-field" data-field="color">
                    <label for="so-add-color">Color</label>
                    <select id="so-add-color" class="form-control" data-placeholder="CHOOSE COLOR">
                        <option value=""></option>
                        @foreach ($masterColors as $color)
                            <option value="{{ $color->id }}">{{ $color->name }}</option>
                        @endforeach
                    </select>
                    <div class="so-field-msg">Required</div>
                </div>
                <div class="so-add-row">
                    <div class="costing-field" data-field="size">
                        <label for="so-add-size">Size</label>
                        <select id="so-add-size" class="form-control" data-placeholder="CHOOSE SIZE">
                            <option value=""></option>
                            @foreach ($masterSizes as $size)
                                <option value="{{ $size->id }}" data-urutan="{{ (int) $size->urutan }}">{{ $size->size }}</option>
                            @endforeach
                        </select>
                        <div class="so-field-msg">Required</div>
                    </div>
                    <div class="costing-field" data-field="qty">
                        <label for="so-add-qty">Qty</label>
                        <input type="text" id="so-add-qty" class="form-control" placeholder="QTY" inputmode="numeric" autocomplete="off">
                        <div class="so-field-msg">Required</div>
                    </div>
                </div>
                <div class="costing-field" data-field="market">
                    <label for="so-add-market">Market</label>
                    <input type="text" id="so-add-market" class="form-control" placeholder="MARKET" autocomplete="off">
                    <div class="so-field-msg">Required</div>
                </div>
                <div class="costing-field" data-field="destination">
                    <label for="so-add-destination">Destination</label>
                    <input type="text" id="so-add-destination" class="form-control" placeholder="DESTINATION" autocomplete="off">
                    <div class="so-field-msg">Optional</div>
                </div>
                <div class="costing-field" data-field="fob">
                    <label for="so-add-fob">FOB</label>
                    <input type="text" id="so-add-fob" class="form-control" placeholder="FOB" inputmode="decimal" autocomplete="off">
                    <div class="so-field-msg">Required</div>
                </div>
                <div class="so-add-actions">
                    <button type="button" class="mkt-btn mkt-btn--navy" id="so-add-btn">Add New</button>
                </div>
            </section>
        </div>
        @endunless

        <section class="so-section">
            <div class="so-section-title">Sales Order Detail</div>
            <div id="so-pivot-wrap">
                <div class="so-inner-card">
                    <div class="so-scroll">
                        <table class="so-table" id="so-pivot">
                            <thead></thead>
                            <tbody></tbody>
                            <tfoot></tfoot>
                        </table>
                    </div>
                    <div class="so-detail-pager">
                        <div>
                            <span>Item Per Page</span>
                            <select id="so-pivot-per-page" aria-label="Item per page">
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                            <span id="so-pivot-page-count">Of 1</span>
                        </div>
                        <div class="so-detail-pages" id="so-pivot-pages"></div>
                    </div>
                </div>
            </div>
            @unless ($isLocked)
            <div class="so-footer">
                <button type="button" id="so-cancel" class="mkt-btn mkt-btn--red mkt-btn--cancel">Cancel</button>
                <button type="button" id="so-update" class="mkt-btn mkt-btn--blue" autocomplete="off">Update</button>
            </div>
            @endunless
        </section>
    </div>
</div>
@endsection

@section('custom-script')
<script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
<script>
    // ===== Edit Sales Order =====
    // Semua perubahan (edit sel, Add New, hapus) disimpan di browser dulu; tombol Update mengirim semuanya sekaligus.
    const SO_LOCKED = @json($isLocked);
    const SO_DATA = { style: @json($so->style), no_po: @json($so->no_po) };
    const SO_SIZE_URUTAN = @json($sizeUrutan);
    const SO_UPDATE_URL = @json(route('so-update-details', $so->id));
    const SO_FIELDS = ['qty', 'market', 'destination', 'fob'];
    let soSeq = 0;
    let soRows = @json($soRows).map(row => soMakeRow(row, false));
    let soChecked = new Set();     // key baris yang dicentang
    let soPasted = new Set();      // key|field yang baru di-paste (biru 4 detik)
    let soActive = null;           // { key, field }
    let soSelection = null;        // { anchor: { key, field }, focus: { key, field } }
    let soVisibleKeys = [];
    let soDragMode = null;
    let soFillTarget = null;
    let soSort = { field: null, dir: 1 };
    let soPage = 1;
    let soSaving = false;
    let soReloading = false;       // true setelah Update berhasil -> halaman sedang di-reload

    function soMakeRow(row, isNew) {
        const vals = {
            qty: row.qty === null || row.qty === undefined ? '' : String(row.qty),
            market: row.market === null || row.market === undefined ? '' : String(row.market),
            destination: row.destination === null || row.destination === undefined ? '' : String(row.destination),
            fob: row.fob === null || row.fob === undefined || row.fob === '' ? '' : String(row.fob)
        };
        return Object.assign({}, row, {
            key: isNew ? 'n' + (++soSeq) : 'd' + row.id,
            seq: ++soSeq,
            isNew: isNew,
            deletedInitially: !isNew && (row.deleted === true || row.deleted === 1 || row.deleted === '1'),
            deleted: !isNew && (row.deleted === true || row.deleted === 1 || row.deleted === '1'),
            vals: vals,
            orig: Object.assign({}, vals)
        });
    }

    function soEsc(value) {
        return $('<div>').text(value === null || value === undefined ? '' : String(value)).html();
    }

    // Angka format Indonesia: titik & koma -> yang terakhir desimal; hanya koma -> koma desimal;
    // hanya titik -> ribuan bila pola 1.234(.567) selain itu desimal. "1.500,25" -> 1500.25, "0.5" -> 0.5, "1.500" -> 1500.
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

    function soQtyError(value) {
        const qty = soParseNumber(value);
        return Number.isInteger(qty) && qty > 0 ? null : 'Qty harus angka bulat lebih dari 0.';
    }
    function soMarketError(value) {
        value = value === null || value === undefined ? '' : String(value);
        if (value === '') return 'Market wajib diisi.';
        if (value !== value.trim() || /\s{2,}/.test(value)) return 'Tidak boleh ada spasi di awal/akhir atau spasi ganda.';
        if (value.length > 60) return 'Market maksimal 60 karakter.';
        return null;
    }
    function soDestinationError(value) {
        value = value === null || value === undefined ? '' : String(value);
        if (value === '') return null;
        if (value !== value.trim() || /\s{2,}/.test(value)) return 'Tidak boleh ada spasi di awal/akhir atau spasi ganda.';
        if (value.length > 60) return 'Destination maksimal 60 karakter.';
        return null;
    }
    function soFobError(value) {
        return value === '' || value === null || value === undefined || Number.isFinite(soParseNumber(value)) ? null : 'FOB harus angka.';
    }
    function soComboKey(color, size, market, destination) {
        return [String(color || '').trim().toUpperCase(), String(size || '').trim().toUpperCase(), String(market || '').toUpperCase(), String(destination || '').toUpperCase()].join('|');
    }
    function soIsDirty(row) { return row.isNew || SO_FIELDS.some(field => row.vals[field] !== row.orig[field]); }

    // Hitung error semua baris yang diubah/baru: { 'key|field': pesan }.
    function soErrors() {
        const errors = {};
        const combos = {};
        soRows.forEach(row => {
            if (row.deleted) return;
            const combo = soComboKey(row.color, row.size, row.vals.market, row.vals.destination);
            (combos[combo] = combos[combo] || []).push(row);
        });
        soRows.forEach(row => {
            if (row.deleted || (!soIsDirty(row) && !row.deletedInitially)) return;
            const qty = soQtyError(row.vals.qty), market = soMarketError(row.vals.market), destination = soDestinationError(row.vals.destination), fob = soFobError(row.vals.fob);
            if (qty) errors[row.key + '|qty'] = qty;
            if (market) errors[row.key + '|market'] = market;
            else if (combos[soComboKey(row.color, row.size, row.vals.market, row.vals.destination)].length > 1) errors[row.key + '|market'] = `${row.color} / ${row.size} / ${row.vals.market} / ${row.vals.destination} sudah ada, ubah qty di tabel.`;
            if (destination) errors[row.key + '|destination'] = destination;
            if (fob) errors[row.key + '|fob'] = fob;
        });
        return errors;
    }

    function soShown(field, value) {
        if (value === '') return '-';
        if (field === 'qty' && !soQtyError(value)) return soParseNumber(value).toLocaleString('id-ID');
        if (field === 'fob' && Number.isFinite(soParseNumber(value))) return soParseNumber(value).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 4 });
        return value;
    }

    function soSortedRows() {
        const rows = soRows.slice();
        if (!soSort.field) return rows;
        const f = soSort.field;
        const value = row => {
            if (f === 'no') return row.seq;
            if (f === 'size') return row.urutan ?? 9999;
            if (f === 'color') return String(row.color || '').toUpperCase();
            if (f === 'market') return String(row.vals.market || '').toUpperCase();
            const n = soParseNumber(row.vals[f]);
            return Number.isFinite(n) ? n : -Infinity;
        };
        return rows.sort((a, b) => {
            const va = value(a), vb = value(b);
            return (va < vb ? -1 : va > vb ? 1 : a.seq - b.seq) * soSort.dir;
        });
    }

    // ===== Render grid (halaman aktif) =====
    function soCell(row, field, errors) {
        const value = row.vals[field];
        const error = errors[row.key + '|' + field];
        const align = field === 'market' || field === 'destination' ? '' : ' text-right';
        if (row.locked || row.deleted) {
            const title = row.deleted ? 'Deleted, belum disimpan. Klik Rollback jika salah hapus.' : 'Sudah masuk PPIC, tidak bisa diubah';
            return `<td class="so-cell-auto${row.deleted ? ' so-cell-deleted' : ''}${align}" title="${title}">${soEsc(soShown(field, value))}</td>`;
        }
        const classes = ['so-cell' + align];
        if (error) classes.push('so-cell-error');
        if (soPasted.has(row.key + '|' + field)) classes.push('so-cell-pasted');
        const position = soCellPosition(row.key, field);
        const rect = soSelectionRect();
        const selected = rect && position && position.row >= rect.top && position.row <= rect.bottom && position.col >= rect.left && position.col <= rect.right;
        const active = soSelection && soSelection.focus.key === row.key && soSelection.focus.field === field;
        const fillHandle = selected && position.row === rect.bottom && position.col === rect.right ? '<span class="so-fill-handle"></span>' : '';
        if (selected) classes.push('so-cell-selected');
        if (active) classes.push('so-cell-active');
        return `<td class="${classes.join(' ')}" tabindex="-1" data-key="${row.key}" data-field="${field}" title="${soEsc(error || value)}">${soEsc(soShown(field, value))}${fillHandle}</td>`;
    }

    function soRender() {
        if (SO_LOCKED) { soRenderPivot(); return; }
        const errors = soErrors();
        const rows = soSortedRows();
        const perPage = Number($('#so-per-page').val()) || 10;
        const pageCount = Math.max(1, Math.ceil(rows.length / perPage));
        soPage = Math.min(Math.max(1, soPage), pageCount);
        const start = (soPage - 1) * perPage;
        const hadFocus = !!$(document.activeElement).closest('#so-grid-body').length;
        soVisibleKeys = rows.slice(start, start + perPage).map(row => row.key);
        const body = rows.slice(start, start + perPage).map((row, i) => `<tr data-key="${row.key}" class="${row.isNew ? 'is-new' : ''}${row.locked ? ' is-locked' : ''}${row.deleted ? ' is-deleted' : ''}">
            <td class="so-col-check"><input type="checkbox" class="so-check" data-key="${row.key}" ${soChecked.has(row.key) ? 'checked' : ''} ${row.locked || row.deleted ? 'disabled title="Baris tidak bisa dipilih"' : ''} aria-label="Pilih baris"></td>
            <td class="so-col-no so-cell-auto">${start + i + 1}${row.locked ? '<i class="fas fa-lock" title="Sudah masuk PPIC"></i>' : ''}${row.deleted ? '<span class="so-deleted-badge">DELETED</span>' : ''}</td>
            <td class="so-cell-auto">${soEsc(row.color || '-')}</td>
            <td class="so-cell-auto">${soEsc(row.size || '-')}</td>
            ${soCell(row, 'qty', errors)}
            ${soCell(row, 'market', errors)}
            ${soCell(row, 'destination', errors)}
            ${soCell(row, 'fob', errors)}
            <td class="so-col-action">${row.deleted
                ? `<button type="button" class="so-row-restore" data-key="${row.key}" title="Rollback"><i class="fas fa-undo"></i></button>`
                : `<button type="button" class="mkt-ico mkt-ico--delete so-row-delete" data-tip="Delete" data-key="${row.key}" ${row.locked ? 'disabled title="Sudah masuk PPIC, tidak bisa dihapus"' : 'title="Tandai untuk dihapus"'} aria-label="Hapus baris"><i class="fas fa-trash"></i></button>`}</td>
        </tr>`).join('');
        $('#so-grid-body').html(body || '<tr><td colspan="9" class="so-empty">Belum ada detail. Tambahkan lewat Add New.</td></tr>');

        $('#so-page-count').text('Of ' + pageCount);
        const pages = [];
        for (let p = 1; p <= pageCount; p++) {
            if (p <= 3 || p > pageCount - 3 || Math.abs(p - soPage) <= 1) pages.push(p);
            else if (pages[pages.length - 1] !== '…') pages.push('…');
        }
        $('#so-pages').html(
            `<button type="button" data-page="${soPage - 1}" ${soPage === 1 ? 'disabled' : ''} aria-label="Sebelumnya"><i class="fas fa-chevron-left"></i></button>`
            + pages.map(p => p === '…' ? '<span>…</span>' : `<button type="button" data-page="${p}" class="${p === soPage ? 'active' : ''}">${p}</button>`).join('')
            + `<button type="button" data-page="${soPage + 1}" ${soPage === pageCount ? 'disabled' : ''} aria-label="Berikutnya"><i class="fas fa-chevron-right"></i></button>`
        );

        $('#so-grid th[data-sort]').each(function () {
            const sorted = soSort.field === this.dataset.sort;
            $(this).toggleClass('is-sorted', sorted).find('.so-sort')
                .attr('class', 'fas so-sort ' + (sorted ? (soSort.dir > 0 ? 'fa-sort-up' : 'fa-sort-down') : 'fa-sort'));
        });

        soChecked.forEach(key => { if (!soRows.some(row => row.key === key && !row.deleted)) soChecked.delete(key); });
        const selectable = soRows.filter(row => !row.locked && !row.deleted).length;
        $('#so-check-all').prop('checked', selectable > 0 && soChecked.size === selectable)
            .prop('indeterminate', soChecked.size > 0 && soChecked.size < selectable);
        $('#so-toolbar').html(soChecked.size
            ? `<button type="button" class="so-bulk-delete" id="so-bulk-delete"><i class="fas fa-trash-alt"></i> Hapus (${soChecked.size})</button>` : '');

        soPaintSelection();
        if (hadFocus && soActive) { const el = soTd(soActive.key, soActive.field)[0]; if (el) el.focus({ preventScroll: true }); }
        soRefreshErrorAlert(errors);
        soRenderPivot();
        // Tombol Update hanya mati selama proses simpan; setiap ada perubahan baru (Add New/edit/hapus) aktif lagi.
        if (!soSaving) $('#so-update').prop('disabled', false);
    }

    // Tombol halaman: < 1 2 3 … 7 8 9 >
    function soPagerHtml(page, pageCount) {
        const pages = [];
        for (let p = 1; p <= pageCount; p++) {
            if (p <= 3 || p > pageCount - 3 || Math.abs(p - page) <= 1) pages.push(p);
            else if (pages[pages.length - 1] !== '…') pages.push('…');
        }
        return `<button type="button" data-page="${page - 1}" ${page === 1 ? 'disabled' : ''} aria-label="Sebelumnya"><i class="fas fa-chevron-left"></i></button>`
            + pages.map(p => p === '…' ? '<span>…</span>' : `<button type="button" data-page="${p}" class="${p === page ? 'active' : ''}">${p}</button>`).join('')
            + `<button type="button" data-page="${page + 1}" ${page === pageCount ? 'disabled' : ''} aria-label="Berikutnya"><i class="fas fa-chevron-right"></i></button>`;
    }

    // ===== Pivot Sales Order Detail (ikut kondisi grid saat ini) =====
    let soPivotPage = 1;
    function soRenderPivot() {
        const sizes = {};
        const groups = new Map();
        soRows.filter(row => !row.deleted).forEach(row => {
            const sizeKey = String(row.size || '-');
            if (!sizes[sizeKey]) sizes[sizeKey] = row.urutan ?? SO_SIZE_URUTAN[sizeKey.trim().toUpperCase()] ?? 9999;
            const market = row.vals.market;
            const destination = row.vals.destination;
            const key = [market, destination, row.ex_fty || '', row.color || '', row.product_set || ''].join('\u0001');
            if (!groups.has(key)) groups.set(key, { market: market, destination: destination, ex_fty: row.ex_fty, color: row.color, product_set: row.product_set, notes: '', qty: {}, total: 0 });
            const group = groups.get(key);
            if (!group.notes && row.notes) group.notes = row.notes;
            const qty = soParseNumber(row.vals.qty);
            const n = Number.isFinite(qty) ? qty : 0;
            group.qty[sizeKey] = (group.qty[sizeKey] || 0) + n;
            group.total += n;
        });
        const sizeList = Object.keys(sizes).sort((a, b) => sizes[a] - sizes[b] || a.localeCompare(b));
        const activeRows = soRows.filter(row => !row.deleted);
        const hasSet = activeRows.some(row => row.product_set && row.product_set !== '-');
        const text = value => soEsc(value === null || value === undefined || value === '' ? '--' : value);
        const num = value => Number(value || 0).toLocaleString('id-ID');

        $('#so-pivot thead').html(`<tr><th class="so-col-no">No</th><th>Style</th><th>Description</th><th>No PO</th><th>Market</th><th>Destination</th><th>Ex Fty</th>${hasSet ? '<th>Product Set</th>' : ''}<th>Color</th>`
            + sizeList.map(size => `<th class="text-right">${soEsc(size)}</th>`).join('') + '<th class="text-right">Total Qty</th></tr>');

        const list = Array.from(groups.values()).sort((a, b) => String(a.color).localeCompare(String(b.color)) || String(a.market).localeCompare(String(b.market)) || String(a.destination).localeCompare(String(b.destination)));
        const colCount = 9 + (hasSet ? 1 : 0) + sizeList.length;
        const perPage = Number($('#so-pivot-per-page').val()) || 10;
        const pageCount = Math.max(1, Math.ceil(list.length / perPage));
        soPivotPage = Math.min(Math.max(1, soPivotPage), pageCount);
        const start = (soPivotPage - 1) * perPage;
        $('#so-pivot-page-count').text('Of ' + pageCount);
        $('#so-pivot-pages').html(soPagerHtml(soPivotPage, pageCount));
        $('#so-pivot tbody').html(list.length ? list.slice(start, start + perPage).map((group, i) => `<tr>
            <td class="so-col-no">${start + i + 1}</td>
            <td>${text(SO_DATA.style)}</td>
            <td>${text(group.notes)}</td>
            <td>${text(SO_DATA.no_po)}</td>
            <td>${text(group.market)}</td>
            <td>${text(group.destination)}</td>
            <td>${text(group.ex_fty)}</td>
            ${hasSet ? `<td>${text(group.product_set)}</td>` : ''}
            <td>${text(group.color)}</td>
            ${sizeList.map(size => `<td class="text-right">${group.qty[size] ? num(group.qty[size]) : '-'}</td>`).join('')}
            <td class="text-right so-col-total">${num(group.total)}</td>
        </tr>`).join('') : `<tr><td colspan="${colCount}" class="so-empty">Tidak ada detail SO.</td></tr>`);

        const sizeTotals = sizeList.map(size => list.reduce((sum, group) => sum + (group.qty[size] || 0), 0));
        $('#so-pivot tfoot').html(`<tr><td></td><td colspan="${7 + (hasSet ? 1 : 0)}">GRAND TOTAL</td>`
            + sizeTotals.map(total => `<td class="text-right">${num(total)}</td>`).join('')
            + `<td class="text-right">${num(list.reduce((sum, group) => sum + group.total, 0))}</td></tr>`);
    }

    function soRefreshErrorAlert(errors) {
        const $alert = $('#so-error-alert');
        if (!$alert.length || $alert.prop('hidden')) return;
        const count = Object.keys(errors || soErrors()).length;
        $alert.find('span').text(count + ' cells belum valid. Perbaiki sel merah lalu Update lagi.');
        $alert.prop('hidden', count === 0);
    }

    // ===== Grid ala Excel =====
    function soRow(key) { return soRows.find(row => row.key === key); }
    function soTd(key, field) { return $(`#so-grid-body td.so-cell[data-key="${key}"][data-field="${field}"]`); }
    function soSelect($td) {
        if (!$td || !$td.length) return;
        soActive = { key: $td.attr('data-key'), field: $td.attr('data-field') };
        soSelection = { anchor: Object.assign({}, soActive), focus: Object.assign({}, soActive) };
        soPaintSelection();
        $td.trigger('focus');
    }
    function soCellPosition(key, field) {
        const row = soVisibleKeys.indexOf(key);
        const col = SO_FIELDS.indexOf(field);
        return row < 0 || col < 0 ? null : { row: row, col: col };
    }
    function soSelectionRect() {
        if (!soSelection) return null;
        const anchor = soCellPosition(soSelection.anchor.key, soSelection.anchor.field);
        const focus = soCellPosition(soSelection.focus.key, soSelection.focus.field);
        if (!anchor || !focus) return null;
        return {
            top: Math.min(anchor.row, focus.row), bottom: Math.max(anchor.row, focus.row),
            left: Math.min(anchor.col, focus.col), right: Math.max(anchor.col, focus.col)
        };
    }
    function soPaintSelection() {
        $('#so-grid td.so-cell-selected, #so-grid td.so-cell-active, #so-grid td.so-cell-fill-preview').removeClass('so-cell-selected so-cell-active so-cell-fill-preview');
        $('#so-grid .so-fill-handle').remove();
        const rect = soSelectionRect();
        if (!rect) {
            soSelection = null;
            soActive = null;
            return;
        }
        for (let row = rect.top; row <= rect.bottom; row++) {
            for (let col = rect.left; col <= rect.right; col++) {
                soTd(soVisibleKeys[row], SO_FIELDS[col]).addClass('so-cell-selected');
            }
        }
        const focus = soSelection.focus;
        soActive = { key: focus.key, field: focus.field };
        const $active = soTd(focus.key, focus.field).addClass('so-cell-active');
        if (soDragMode === 'fill' && soFillTarget !== null) {
            const first = Math.min(soFillTarget, rect.bottom);
            const last = Math.max(soFillTarget, rect.bottom);
            for (let row = first; row <= last; row++) {
                if (row <= rect.bottom && row >= rect.top) continue;
                for (let col = rect.left; col <= rect.right; col++) {
                    soTd(soVisibleKeys[row], SO_FIELDS[col]).addClass('so-cell-fill-preview');
                }
            }
        }
        if (soDragMode !== 'fill' && $active.length) {
            soTd(soVisibleKeys[rect.bottom], SO_FIELDS[rect.right]).append($('<span>').addClass('so-fill-handle'));
        }
    }
    function soSelectionCells(callback) {
        const rect = soSelectionRect();
        if (!rect) return;
        for (let row = rect.top; row <= rect.bottom; row++) {
            for (let col = rect.left; col <= rect.right; col++) callback(soVisibleKeys[row], SO_FIELDS[col], row, col);
        }
    }
    function soApplyFill(targetRow) {
        const rect = soSelectionRect();
        if (!rect || targetRow >= rect.top && targetRow <= rect.bottom) return;
        const height = rect.bottom - rect.top + 1;
        const start = Math.min(targetRow, rect.top);
        const end = Math.max(targetRow, rect.bottom);
        const changes = [];
        for (let row = start; row <= end; row++) {
            if (row >= rect.top && row <= rect.bottom) continue;
            const sourceRow = targetRow > rect.bottom
                ? rect.top + ((row - rect.bottom - 1) % height)
                : rect.bottom - ((rect.top - 1 - row) % height);
            for (let col = rect.left; col <= rect.right; col++) {
                const source = soRow(soVisibleKeys[sourceRow]);
                if (!source) continue;
                changes.push({ key: soVisibleKeys[row], field: SO_FIELDS[col], value: source.vals[SO_FIELDS[col]] });
            }
        }
        soCommit(changes, { pasted: true });
        soSelection = {
            anchor: { key: soVisibleKeys[targetRow > rect.bottom ? rect.top : rect.bottom], field: SO_FIELDS[rect.left] },
            focus: { key: soVisibleKeys[targetRow], field: SO_FIELDS[rect.right] }
        };
        soActive = Object.assign({}, soSelection.focus);
        soRender();
    }
    function soMove($td, direction, extend) {
        let $next = $();
        if (direction === 'left') $next = $td.prevAll('td.so-cell').first();
        if (direction === 'right') $next = $td.nextAll('td.so-cell').first();
        if (direction === 'up' || direction === 'down') {
            let $tr = $td.closest('tr');
            do {
                $tr = direction === 'up' ? $tr.prev('tr') : $tr.next('tr');
                $next = $tr.children('td.so-cell').filter(function () { return this.dataset.field === $td.attr('data-field'); }).first();
            } while ($tr.length && !$next.length);
        }
        if (!$next.length) return false;
        const position = { key: $next.attr('data-key'), field: $next.attr('data-field') };
        if (extend && soSelection) {
            soSelection.focus = position;
            soActive = position;
            soPaintSelection();
            $next.trigger('focus');
        } else {
            soSelect($next);
        }
        return true;
    }
    function soIsMulti() { const rect = soSelectionRect(); return !!rect && (rect.bottom > rect.top || rect.right > rect.left); }
    // Ctrl+D: 1 baris dipilih = copy dari baris di atasnya; banyak baris = copy baris teratas ke bawah (sama dengan costing).
    function soFillDown() {
        const rect = soSelectionRect();
        if (!rect) return;
        const changes = [];
        const copyRow = (from, to) => {
            const source = soRow(soVisibleKeys[from]);
            if (!source) return;
            for (let col = rect.left; col <= rect.right; col++) changes.push({ key: soVisibleKeys[to], field: SO_FIELDS[col], value: source.vals[SO_FIELDS[col]] });
        };
        if (rect.top === rect.bottom) { if (rect.top === 0) return; copyRow(rect.top - 1, rect.top); }
        else for (let row = rect.top + 1; row <= rect.bottom; row++) copyRow(rect.top, row);
        soCommit(changes, { pasted: true });
        soRender();
    }
    function soClearSelection() {
        const changes = [];
        soSelectionCells((key, field) => changes.push({ key: key, field: field, value: '' }));
        soCommit(changes);
        soRender();
    }

    function soEdit($td, firstChar) {
        if (!$td.length || $td.hasClass('so-cell-editing')) return;
        const key = $td.attr('data-key'), field = $td.attr('data-field');
        const row = soRow(key);
        if (!row) return;
        const original = row.vals[field];
        const $input = $('<input type="text" class="so-cell-input" autocomplete="off">').val(firstChar !== undefined ? firstChar : original);
        $td.addClass('so-cell-editing').empty().append($input);
        $input.trigger('focus');
        if (firstChar === undefined) $input[0].select();
        let done = false;
        const finish = function (save, move) {
            if (done) return;
            done = true;
            if (save) soCommit([{ key: key, field: field, value: $input.val() }]);
            soActive = { key: key, field: field };
            soRender();
            const $fresh = soTd(key, field);
            soSelect($fresh);
            if (move) soMove($fresh, move);
        };
        $input.on('keydown', function (e) {
            const el = this, caretAt = el.selectionStart === el.selectionEnd ? el.selectionStart : -1;
            if (e.key === 'Enter') { e.preventDefault(); finish(true, e.shiftKey ? 'up' : 'down'); }
            else if (e.key === 'Tab') { e.preventDefault(); finish(true, e.shiftKey ? 'left' : 'right'); }
            else if (e.key === 'Escape') { e.preventDefault(); finish(false); }
            else if (e.key === 'ArrowUp' || e.key === 'ArrowDown') { e.preventDefault(); finish(true, e.key === 'ArrowUp' ? 'up' : 'down'); }
            else if (e.key === 'ArrowLeft' && caretAt === 0) { e.preventDefault(); finish(true, 'left'); }
            else if (e.key === 'ArrowRight' && caretAt === el.value.length) { e.preventDefault(); finish(true, 'right'); }
            e.stopPropagation();
        }).on('blur', function () { finish(true); });
    }

    // Terapkan perubahan sel ke data lokal (belum ke server).
    function soCommit(changes, options) {
        options = options || {};
        changes.forEach(change => {
            const row = soRow(change.key);
            if (!row || row.locked || row.deleted || !SO_FIELDS.includes(change.field)) return;
            row.vals[change.field] = change.value === null || change.value === undefined ? '' : String(change.value);
            if (options.pasted) soPasted.add(change.key + '|' + change.field);
        });
        if (options.pasted) {
            setTimeout(() => {
                changes.forEach(change => soPasted.delete(change.key + '|' + change.field));
                changes.forEach(change => soTd(change.key, change.field).removeClass('so-cell-pasted'));
            }, 4000);
        }
    }

    function soHasChanges() {
        return soRows.some(row => row.isNew
            ? row.deleted || soIsDirty(row)
            : row.deleted !== row.deletedInitially || (!row.deleted && soIsDirty(row)));
    }

    function soDelete(keys) {
        keys.forEach(key => {
            const row = soRow(key);
            if (!row || row.locked || row.deleted) return;
            row.deleted = true;
            soChecked.delete(key);
            if (soActive && soActive.key === key) soActive = null;
        });
        soSelection = null;
        soRender();
    }

    function soRestore(key) {
        const row = soRow(key);
        if (!row || !row.deleted) return;
        row.deleted = false;
        soRender();
    }

    // ===== Add New =====
    function soFieldError(field, message) {
        const $field = $(`#so-add-form .costing-field[data-field="${field}"]`);
        $field.toggleClass('is-invalid', !!message);
        if (message) $field.find('.so-field-msg').text(message);
        return !!message;
    }

    function soAddNew() {
        const colorId = $('#so-add-color').val(), sizeId = $('#so-add-size').val();
        const qty = $('#so-add-qty').val(), market = $('#so-add-market').val(), destination = $('#so-add-destination').val(), fob = $('#so-add-fob').val();
        let invalid = false;
        invalid = soFieldError('color', colorId ? null : 'Required') || invalid;
        invalid = soFieldError('size', sizeId ? null : 'Required') || invalid;
        invalid = soFieldError('qty', qty.trim() === '' ? 'Required' : soQtyError(qty)) || invalid;
        invalid = soFieldError('market', market === '' ? 'Required' : soMarketError(market)) || invalid;
        invalid = soFieldError('fob', soFobError(fob)) || invalid;
        if (invalid) return;

        const color = $('#so-add-color option:selected').text().trim();
        const $size = $('#so-add-size option:selected');
        const size = $size.text().trim();
        if (soRows.some(row => !row.deleted && soComboKey(row.color, row.size, row.vals.market, row.vals.destination) === soComboKey(color, size, market, destination))) {
            soFieldError('market', `${color} / ${size} / ${market} sudah ada, ubah qty di tabel.`);
            return;
        }

        // Ex Fty, Description & Product Set ikut baris SO yang ada (utamakan warna yang sama) — sama seperti di server.
        const template = soRows.find(row => !row.deleted && !row.isNew && (Number(row.id_color) === Number(colorId) || String(row.color).toUpperCase() === color.toUpperCase()))
            || soRows.find(row => !row.deleted && !row.isNew) || {};
        const row = soMakeRow({
            id: null, color: color, size: size, id_color: Number(colorId), id_size: Number(sizeId),
            qty: String(soParseNumber(qty)), market: market, destination: destination, fob: fob === '' ? '' : String(soParseNumber(fob)),
            ex_fty: template.ex_fty || null, notes: template.notes || null, product_set: template.product_set || null,
            urutan: Number($size.data('urutan')) || 9999, locked: false
        }, true);
        soRows.push(row);
        SO_FIELDS.forEach(field => soPasted.add(row.key + '|' + field));
        setTimeout(() => { SO_FIELDS.forEach(field => { soPasted.delete(row.key + '|' + field); soTd(row.key, field).removeClass('so-cell-pasted'); }); }, 4000);

        // Pindah ke halaman tempat baris baru berada.
        const perPage = Number($('#so-per-page').val()) || 10;
        soPage = Math.floor(soSortedRows().findIndex(item => item.key === row.key) / perPage) + 1;
        soRender();
        $('#so-add-color, #so-add-size').val(null).trigger('change.select2');
        $('#so-add-qty').val('');
        $('#so-add-destination').val('');
    }

    // ===== Update =====
    function soUpdate() {
        if (soSaving) return;
        const errors = soErrors();
        const count = Object.keys(errors).length;
        if (count) {
            const $alert = $('#so-error-alert');
            $alert.find('span').text(count + ' cells belum valid. Perbaiki sel merah lalu Update lagi.');
            $alert.prop('hidden', false)[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }
        $('#so-error-alert').prop('hidden', true);
        const fob = value => value === '' ? null : soParseNumber(value);
        const payload = {
            updates: soRows.filter(row => !row.deleted && !row.isNew && soIsDirty(row))
                .map(row => ({ id: row.id, qty: soParseNumber(row.vals.qty), market: row.vals.market, destination: row.vals.destination, fob: fob(row.vals.fob) })),
            adds: soRows.filter(row => !row.deleted && row.isNew)
                .map(row => ({ id_color: row.id_color, id_size: row.id_size, qty: soParseNumber(row.vals.qty), market: row.vals.market, destination: row.vals.destination, fob: fob(row.vals.fob) })),
            deletes: soRows.filter(row => row.deleted && !row.isNew && !row.deletedInitially).map(row => row.id),
            restores: soRows.filter(row => !row.deleted && !row.isNew && row.deletedInitially).map(row => row.id)
        };
        if (!payload.updates.length && !payload.adds.length && !payload.deletes.length && !payload.restores.length) {
            const locallyDeletedNewRows = soRows.filter(row => row.deleted && row.isNew);
            if (locallyDeletedNewRows.length) {
                const deletedKeys = new Set(locallyDeletedNewRows.map(row => row.key));
                soRows = soRows.filter(row => !deletedKeys.has(row.key));
                soRender();
                return;
            }
            Swal.fire({ icon: 'info', title: 'Tidak ada perubahan', text: 'Belum ada data yang diubah.' });
            return;
        }
        soSaving = true;
        $('#so-update').prop('disabled', true);
        soLoading('Menyimpan...', 'Mohon tunggu, Sales Order sedang diupdate.');
        $.ajax({
            url: SO_UPDATE_URL,
            type: 'POST',
            contentType: 'application/json',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            data: JSON.stringify(payload)
        }).done(function (res) {
            soReloading = true;
            setTimeout(() => { window.location.href = @json(route('master-costing')); }, 800);
            Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message || 'Sales Order berhasil diupdate.', showConfirmButton: false, allowOutsideClick: false, allowEscapeKey: false });
        }).fail(function (xhr) {
            soSaving = false;
            $('#so-update').prop('disabled', false);
            const res = xhr.responseJSON || {};
            const list = res.errors && !Array.isArray(res.errors) ? Object.values(res.errors).flat() : (res.errors || []);
            Swal.fire({ icon: 'error', title: 'Gagal', html: (list.length ? list : [res.message || 'Sales Order gagal diupdate.']).map(soEsc).join('<br>') });
        });
    }

    function soLoading(title, text) {
        Swal.fire({ title: title, text: text, allowOutsideClick: false, allowEscapeKey: false, showConfirmButton: false, didOpen: () => Swal.showLoading() });
    }

    $(function () {
        $('#so-pivot-per-page').on('change', function () { soPivotPage = 1; soRenderPivot(); });
        $('#so-pivot-pages').on('click', 'button[data-page]', function () { soPivotPage = Number($(this).data('page')); soRenderPivot(); });

        soRender();
        if (SO_LOCKED) return;

        $('#so-add-color, #so-add-size').each(function () {
            $(this).select2({ theme: 'bootstrap4', width: '100%', allowClear: true, placeholder: $(this).data('placeholder'), dropdownCssClass: 'so-s2-dropdown' });
        });
        $(document).on('select2:open', () => { const field = document.querySelector('.select2-container--open .select2-search__field'); if (field) field.focus(); });
        $('#so-add-form').on('change input', 'select, input', function () { $(this).closest('.costing-field').removeClass('is-invalid'); });
        $('#so-add-btn').on('click', soAddNew);
        $('#so-add-form').on('keydown', 'input', function (e) { if (e.key === 'Enter') { e.preventDefault(); soAddNew(); } });

        $('#so-per-page').on('change', function () { soSelection = null; soPage = 1; soRender(); });
        $('#so-pages').on('click', 'button[data-page]', function () { soSelection = null; soPage = Number($(this).data('page')); soRender(); });
        $('#so-grid thead').on('click', 'th[data-sort]', function () {
            const field = this.dataset.sort;
            soSelection = null;
            soSort = soSort.field === field ? { field: field, dir: -soSort.dir } : { field: field, dir: 1 };
            soRender();
        });
        $('#so-check-all').on('change', function () {
            soChecked = new Set(this.checked ? soRows.filter(row => !row.locked && !row.deleted).map(row => row.key) : []);
            soRender();
        });
        $('#so-grid-body').on('change', '.so-check', function () {
            if (this.checked) soChecked.add(this.dataset.key); else soChecked.delete(this.dataset.key);
            soRender();
        });
        $('#so-grid-body').on('click', '.so-row-delete', function () { soDelete([this.dataset.key]); });
        $('#so-grid-body').on('click', '.so-row-restore', function () { soRestore(this.dataset.key); });
        $('#so-toolbar').on('click', '#so-bulk-delete', function () { soDelete(Array.from(soChecked)); });

        $('#so-grid-body').on('mousedown', 'td.so-cell', function (event) {
            if (event.button !== 0 || $(event.target).closest('.so-fill-handle').length) return;
            if ($(this).hasClass('so-cell-editing')) return;
            const key = $(this).attr('data-key'), field = $(this).attr('data-field');
            event.preventDefault();
            $('#so-grid .so-cell-input').trigger('blur');
            if (event.shiftKey && soSelection && soCellPosition(soSelection.anchor.key, soSelection.anchor.field)) {
                soSelection.focus = { key: key, field: field };
                soActive = { key: key, field: field };
                soPaintSelection();
                soTd(key, field)[0].focus({ preventScroll: true });
                return;
            }
            soSelection = { anchor: { key: key, field: field }, focus: { key: key, field: field } };
            soActive = { key: key, field: field };
            soDragMode = 'select';
            soPaintSelection();
            soTd(key, field).trigger('focus');
        });
        $('#so-grid-body').on('dblclick', 'td.so-cell', function () { soEdit($(this)); });
        $('#so-grid-body').on('keydown', 'td.so-cell', function (e) {
            const $td = $(this);
            if ($td.hasClass('so-cell-editing')) return;
            const ctrl = e.ctrlKey || e.metaKey;
            if (ctrl && e.key.toLowerCase() === 'd') { e.preventDefault(); soFillDown(); return; }
            if (ctrl || e.altKey) return;   // Ctrl+C / Ctrl+V lewat event copy & paste
            const keys = { ArrowLeft: 'left', ArrowRight: 'right', ArrowUp: 'up', ArrowDown: 'down' };
            if (keys[e.key]) { e.preventDefault(); soMove($td, keys[e.key], e.shiftKey); return; }
            if (e.key === 'Enter') { e.preventDefault(); soMove($td, e.shiftKey ? 'up' : 'down'); return; }
            if (e.key === 'Tab') { e.preventDefault(); soMove($td, e.shiftKey ? 'left' : 'right'); return; }
            if (e.key === 'F2') { e.preventDefault(); soEdit($td); return; }
            if (e.key === 'Escape') { soSelection = null; soActive = null; soPaintSelection(); return; }
            if (e.key === 'Delete') { e.preventDefault(); soClearSelection(); return; }
            if (e.key === 'Backspace') { e.preventDefault(); soIsMulti() ? soClearSelection() : soEdit($td, ''); return; }
            if (e.key.length === 1) { e.preventDefault(); soEdit($td, e.key); }
        });

        $(document).on('mousedown', '.so-fill-handle', function (event) {
            event.preventDefault();
            event.stopPropagation();
            soDragMode = 'fill';
            soFillTarget = null;
        });
        $(document).on('mouseenter', '#so-grid-body td.so-cell', function () {
            if (!soDragMode || !soSelection) return;
            const position = soCellPosition(this.dataset.key, this.dataset.field);
            if (!position) return;
            if (soDragMode === 'select') {
                soSelection.focus = { key: this.dataset.key, field: this.dataset.field };
                soActive = Object.assign({}, soSelection.focus);
            } else {
                soFillTarget = position.row;
            }
            soPaintSelection();
        });
        $(document).on('mouseup', function () {
            if (!soDragMode) return;
            if (soDragMode === 'fill' && soFillTarget !== null) soApplyFill(soFillTarget);
            soDragMode = null;
            soFillTarget = null;
            soPaintSelection();
        });
        $(document).on('mousedown', function (event) {
            if ($(event.target).closest('#so-grid-body td.so-cell, .so-fill-handle').length) return;
            soDragMode = null;
            soFillTarget = null;
            if (soSelection) {
                soSelection = null;
                soActive = null;
                soPaintSelection();
            }
        });
        $(document).on('copy', function (event) {
            if (!soSelection || $('#so-grid .so-cell-input').length || $(event.target).is('input, select, textarea')) return;
            const clipboard = event.originalEvent.clipboardData;
            if (!clipboard) return;
            const rect = soSelectionRect();
            if (!rect) return;
            const lines = [];
            for (let row = rect.top; row <= rect.bottom; row++) {
                const values = [];
                for (let col = rect.left; col <= rect.right; col++) {
                    const source = soRow(soVisibleKeys[row]);
                    values.push(source ? source.vals[SO_FIELDS[col]] : '');
                }
                lines.push(values.join('\t'));
            }
            clipboard.setData('text/plain', lines.join('\n'));
            event.preventDefault();
        });
        $(document).on('paste', function (event) {
            if (!soSelection || $('#so-grid .so-cell-input').length || $(event.target).is('input, select, textarea')) return;
            const clipboard = event.originalEvent.clipboardData || window.clipboardData;
            const text = clipboard ? clipboard.getData('text/plain') : '';
            if (!text) return;
            event.preventDefault();
            const matrix = text.replace(/\r/g, '').replace(/\n+$/, '').split('\n').map(line => line.split('\t'));
            const rect = soSelectionRect();
            if (!rect) return;
            const changes = [];
            let lastRow = rect.top, lastCol = rect.left;
            const fillSelection = matrix.length === 1 && matrix[0].length === 1;
            const rowCount = fillSelection ? rect.bottom - rect.top + 1 : matrix.length;
            const colCount = fillSelection ? rect.right - rect.left + 1 : matrix.reduce((max, row) => Math.max(max, row.length), 0);
            for (let rowOffset = 0; rowOffset < rowCount; rowOffset++) {
                for (let colOffset = 0; colOffset < colCount; colOffset++) {
                    const rowIndex = rect.top + rowOffset;
                    const colIndex = rect.left + colOffset;
                    const row = soRow(soVisibleKeys[rowIndex]);
                    const value = fillSelection ? matrix[0][0] : matrix[rowOffset] && matrix[rowOffset][colOffset];
                    if (!row || row.locked || row.deleted || colIndex >= SO_FIELDS.length || value === undefined) continue;
                    const field = SO_FIELDS[colIndex];
                    changes.push({ key: row.key, field: field, value: field === 'market' ? value : value.trim() });
                    lastRow = Math.max(lastRow, rowIndex);
                    lastCol = Math.max(lastCol, colIndex);
                }
            }
            if (!changes.length) return;
            soCommit(changes, { pasted: true });
            soSelection = {
                anchor: { key: soVisibleKeys[rect.top], field: SO_FIELDS[rect.left] },
                focus: { key: soVisibleKeys[lastRow], field: SO_FIELDS[lastCol] }
            };
            soRender();
        });

        $('#so-update').prop('disabled', false).on('click', soUpdate);
        window.addEventListener('pageshow', function () {
            soSaving = false;
            $('#so-update').prop('disabled', false);
            if (Swal.isVisible()) Swal.close();
        });
        $('#so-cancel').on('click', function () {
            if (!soHasChanges()) { window.location.reload(); return; }
            Swal.fire({
                icon: 'warning', title: 'Buang perubahan?', text: 'Perubahan yang belum di-Update akan hilang.',
                showCancelButton: true, confirmButtonText: 'Ya, buang', cancelButtonText: 'Batal'
            }).then(result => {
                if (!result.isConfirmed) return;
                soRows = [];
                window.location.reload();
            });
        });
        window.addEventListener('beforeunload', function (e) {
            if (soReloading || soSaving || !soHasChanges()) return;
            e.preventDefault();
            e.returnValue = '';
        });
    });
</script>
@endsection
