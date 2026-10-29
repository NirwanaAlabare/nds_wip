@extends('layouts.index')

@section('custom-link')
    <link rel="stylesheet" href="{{ asset('dist/css/marketing-form.css') }}?v={{ @filemtime(public_path('assets/dist/css/marketing-form.css')) }}">
    <link rel="stylesheet" href="{{ asset('dist/css/marketing-buttons.css') }}?v={{ @filemtime(public_path('assets/dist/css/marketing-buttons.css')) }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

    <style>
        :root { --costing-navy: #183f5a; --costing-title: #284a63; --costing-border: #dce4ec; }
        .costing-create-page { padding: 8px 12px 24px; background: #f5f3f3; min-height: calc(100vh - 56px); font-family: 'Poppins', sans-serif; }
        .costing-crumb { background: #284A63; color: #b4b4b4; padding: 8px 10px; margin-bottom: 14px; font-size: 16px; font-weight: 500; }
        .costing-crumb a { color: #b4b4b4; text-decoration: none; }
        .costing-crumb a:hover { color: #fff; text-decoration: underline; }
        .costing-crumb b { color: #fff; font-weight: 500; }

        .costing-create-card { border: 0; border-radius: 4px; box-shadow: 0 5px 12px -3px rgba(30, 41, 59, .18), 0 2px 4px rgba(30, 41, 59, .07); background: #fff; }
        .costing-create-card > .card-body { padding: 20px 24px 28px; }

        .costing-page-title { margin: 0; color: var(--costing-title); font-size: 26px; font-weight: 700; }
        .costing-page-title i { color: #64748b; font-size: 12px; vertical-align: super; }
        .costing-page-subtitle { margin: 2px 0 18px; color: #475569; font-size: 10px; font-weight: 500; }

        .costing-layout { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 0 18px; }
        .costing-layout .span-full { grid-column: 1 / -1; }
        .costing-top { display: grid; grid-template-columns: minmax(0, 1.45fr) minmax(0, 1fr); gap: 18px; grid-column: 1 / -1; }

        .costing-section { margin-bottom: 16px; }
        .costing-section-title {
            display: flex; align-items: center; justify-content: space-between; cursor: pointer; user-select: none;
            margin: 0 0 8px; padding: 6px 12px; border: 1px solid #94a3b8; border-radius: 6px;
            background: #fff; color: var(--costing-title); font-size: 15px; font-weight: 500;
        }
        .costing-section-title .required-mark { color: #dc3545; margin-left: 2px; margin-right: auto; }
        .costing-section-body { padding: 12px 10px 4px; border-radius: 6px; background: #fff; box-shadow: 0 3px 10px rgba(15, 23, 42, .10); }
        .costing-section-chevron { display: flex; align-items: center; justify-content: center; width: 20px; height: 20px; border-radius: 4px; background: #a3b2c7; color: #fff; font-size: 10px; }
        .costing-section-chevron i { transition: transform .2s ease; }
        .costing-section.is-collapsed .costing-section-chevron i { transform: rotate(180deg); }

        .costing-fields { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); column-gap: 12px; }
        .costing-fields.four-columns { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        .costing-fields.price-columns { grid-template-columns: minmax(0, 2fr) repeat(3, minmax(0, 1fr)); }
        .costing-field { min-width: 0; margin-bottom: 10px; }
        .costing-field.span-2 { grid-column: span 2; }
        .costing-field label { display: block; margin-bottom: 4px; color: #1e293b; font-size: 10px; font-weight: 500 !important; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

        .costing-field .form-control,
        .costing-field .select2-container--bootstrap4 .select2-selection { min-height: 34px; height: 34px; border: 1px solid #e1e5e9; border-radius: 6px; font-size: 10px; color: #495057; box-shadow: 0 1px 3px rgba(30, 41, 59, .08); }
        .costing-field .form-control { padding: 6px 10px; }
        .costing-field textarea.form-control { resize: vertical; }
        .costing-field .form-control::placeholder { color: #adb5bd; }
        .costing-field .form-control:focus { border-color: #8aa8bc; box-shadow: 0 0 0 2px rgba(39, 75, 99, .08); }
        .costing-field .form-control[readonly],
        .costing-field .form-control:disabled { background: #e9ecef; color: #6c757d; }
        .costing-field .select2-container { width: 100% !important; }
        .costing-field .select2-container--bootstrap4 .select2-selection--single { padding: 4px 10px; }
        .costing-field .select2-container--bootstrap4 .select2-selection__rendered { padding-left: 0; line-height: 24px; color: #495057; }
        .costing-field .select2-container--bootstrap4 .select2-selection--multiple { height: auto; }
        .costing-field .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__rendered { padding: 0 8px; }
        .costing-field .select2-container--bootstrap4.select2-container--disabled .select2-selection { background: #e9ecef; }

        /* Upload Image */
        .costing-upload-box { padding: 10px; border: 1px solid var(--costing-border); border-radius: 6px; }
        .costing-picture-dropzone {
            display: flex; align-items: center; justify-content: center; gap: 10px; min-height: 64px; padding: 10px;
            border: 1.5px dashed var(--costing-navy); border-radius: 5px; background: #fff;
            color: var(--costing-navy); font-size: 10px; font-weight: 600; text-align: left; cursor: pointer;
        }
        .costing-picture-dropzone i { font-size: 24px; }
        .costing-picture-dropzone b { color: #0056b3; font-weight: 600; }
        .costing-picture-dropzone.dragging { background: #eff7ff; }
        .costing-upload-now { width: 100%; margin-top: 8px; }
        .costing-picture-thumbnails { display: flex; flex-wrap: wrap; gap: 8px; min-height: 70px; margin-top: 10px; padding: 8px; border: 1px dashed #8b5cf6; border-radius: 6px; }
        .costing-picture-clear { margin-top: 6px; }
        .costing-picture-carousel { display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 200px; margin-top: 14px; }
        .costing-picture-main { max-width: 100%; max-height: 200px; object-fit: contain; }
        .costing-picture-nav { flex: 0 0 auto; width: 26px; height: 26px; padding: 0; border: 0; border-radius: 50%; background: #f1f3f5; color: #53687a; box-shadow: 0 2px 4px rgba(0, 0, 0, .1); }
        .costing-picture-nav:hover { background: #e2e6ea; }
        .costing-picture-nav:disabled { opacity: .4; }

        .costing-form-actions { text-align: right; }

        @media (max-width: 1199.98px) {
            .costing-top, .costing-layout { grid-template-columns: minmax(0, 1fr); }
        }
        @media (max-width: 767.98px) {
            .costing-fields, .costing-fields.four-columns, .costing-fields.price-columns { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .costing-field.span-2 { grid-column: auto; }
        }
        @media (max-width: 480px) {
            .costing-fields, .costing-fields.four-columns, .costing-fields.price-columns { grid-template-columns: minmax(0, 1fr); }
        }

        .select2-container .select2-selection--single .select2-selection__arrow {
            top: 50% !important;
            transform: translateY(-50%) !important;
            height: auto !important;
        }

        .costing-page-title { color: #284a63; font-size: 32px; font-weight: 700; }
        .costing-page-subtitle { color: #284a63; font-size: 12px; font-weight: 600; }
        .costing-section-title { color: #284a63; font-size: 25px; font-weight: 500; }
        .costing-field label { font-size: 16px; font-weight: 500 !important; }
        .costing-field .form-control,
        .costing-field .select2-container--bootstrap4 .select2-selection { height: 59px; min-height: 59px; border: 1px solid #e1e5e9; border-radius: 12px; background: #fff; box-shadow: none; font-size: 14px; font-weight: 500; line-height: 1; color: #000; }
        .costing-field .form-control { padding: 0 16px; }
        .costing-field textarea.form-control { padding: 10px 16px; line-height: 1.4; }
        .costing-field .form-control::placeholder { color: #ababab; }
        .costing-field .form-control:focus,
        .costing-field .select2-container--bootstrap4.select2-container--focus .select2-selection,
        .costing-field .select2-container--bootstrap4.select2-container--open .select2-selection { border-color: #284a63; box-shadow: none; }
        .costing-field .select2-container--bootstrap4 .select2-selection--single { display: flex; align-items: center; padding: 0 44px 0 16px; }
        .costing-field .select2-container--bootstrap4 .select2-selection__rendered { padding: 0; line-height: 1; font-size: 14px; font-weight: 500; color: #000; }
        .costing-field .select2-container--bootstrap4 .select2-selection__placeholder { color: #ababab; }
        .costing-field .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow { right: 16px; width: 12px; }
        .costing-field .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow b { top: 50%; left: 50%; width: 8px; height: 8px; margin: -6px 0 0 -4px; border: solid #284a63; border-width: 0 1.5px 1.5px 0; transform: rotate(45deg); }
        .costing-field .select2-container--bootstrap4.select2-container--open .select2-selection--single .select2-selection__arrow b { margin-top: -2px; transform: rotate(-135deg); }
        /* State 2: ada isi + hover/fokus = border navy dan tanda X untuk menghapus pilihan (allowClear), menggantikan chevron.
           State 3: sudah terpilih & diam = border abu + chevron. */
        .costing-field .select2-container--bootstrap4 .select2-selection { position: relative; }
        .costing-field .select2-container--bootstrap4 .select2-selection__clear { display: none; position: absolute; top: 50%; right: 14px; z-index: 2; float: none; width: 18px; height: 18px; margin: 0; padding: 0; transform: translateY(-50%); color: #284a63; font-size: 20px; font-weight: 400; line-height: 18px; text-align: center; cursor: pointer; }
        .costing-field .select2-container--bootstrap4:not(.select2-container--disabled):is(:hover, .select2-container--focus, .select2-container--open) .select2-selection__clear { display: block; }
        .costing-field .select2-container--bootstrap4:not(.select2-container--disabled):is(:hover, .select2-container--focus, .select2-container--open) .select2-selection--single:has(.select2-selection__clear) .select2-selection__arrow { display: none; }
        .costing-field .select2-container--bootstrap4:not(.select2-container--disabled):hover .select2-selection:has(.select2-selection__clear) { border-color: #284a63; }
        /* State 4: disabled / readonly / view only = label ikut pudar. */
        .costing-field:has(.select2-container--disabled) label, .costing-field:has(.form-control:disabled) label, .costing-field:has(.form-control[readonly]) label { color: #c4c4c4; }
        .costing-field .select2-container--bootstrap4 .select2-selection--multiple { display: flex; align-items: center; height: auto; min-height: 59px !important; padding: 4px 16px; }
        .costing-field .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__rendered { padding: 0; }
        .costing-field .select2-container--bootstrap4 .select2-selection--multiple .select2-search__field { margin-top: 0; font-size: 14px; font-weight: 500; color: #000; }
        .costing-field .select2-container--bootstrap4 .select2-selection--multiple .select2-search__field::placeholder { color: #ababab; }

        /* Disabled: latar abu muda, teks & panah pudar. */
        .costing-field .form-control[readonly],
        .costing-field .form-control:disabled,
        .costing-field .select2-container--bootstrap4.select2-container--disabled .select2-selection { background: #f8f8f8; border-color: #e9ecef; color: #ababab; }
        .costing-field .select2-container--bootstrap4.select2-container--disabled .select2-selection__rendered { color: #ababab; }
        .costing-field .select2-container--bootstrap4.select2-container--disabled .select2-selection__arrow b { border-color: #c4c4c4; }

        /* Banner "N fields are empty" di atas form, muncul saat Save dengan field wajib kosong. */
        .costing-error-alert { display: flex; align-items: center; gap: 8px; margin-bottom: 16px; padding: 10px 12px; border-radius: 4px; background: #ff5f5f; color: #fff; font-family: Inter, sans-serif; font-size: 13px; }
        .costing-error-alert[hidden] { display: none; }
        .costing-error-alert > span { flex: 1; }
        .costing-error-alert-close { flex: 0 0 auto; padding: 0; border: 0; background: none; color: #fff; font-size: 16px; line-height: 1; }
        .costing-field.is-invalid label { color: #c8161d; }
        .costing-field.is-invalid .form-control,
        .costing-field.is-invalid .select2-container--bootstrap4 .select2-selection { border-color: #c8161d; color: #c8161d; }
        .costing-field.is-invalid .select2-container--bootstrap4 .select2-selection__rendered,
        .costing-field.is-invalid .select2-container--bootstrap4 .select2-selection__placeholder,
        .costing-field.is-invalid .form-control::placeholder { color: #c8161d; }
        .costing-field.is-invalid .select2-container--bootstrap4 .select2-selection__arrow b { border-color: #c8161d; }
        .costing-field.is-invalid::after { content: 'Required'; display: block; margin-top: 4px; color: #c8161d; font-size: 13px; font-weight: 500; }
        .costing-field.is-invalid[data-error]::after { content: attr(data-error); }

        /* Dropdown list ikut ukuran teks 14. */
        .select2-container--bootstrap4 .select2-results__option { font-family: 'Poppins', sans-serif; font-size: 14px; font-weight: 500; }
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
        /* ===== Responsif tablet & HP (desktop >= 1200px tidak berubah) ===== */
        @media (max-width: 1199.98px) {
            /* Label boleh turun baris (tidak terpotong "..."), chevron tidak ikut menyusut. */
            .costing-field label { white-space: normal; overflow: visible; text-overflow: clip; overflow-wrap: anywhere; }
            .costing-section-chevron { flex: 0 0 auto; margin-left: 8px; }
        }
        @media (max-width: 991.98px) {
            .costing-fields.four-columns, .costing-fields.price-columns { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .costing-page-title { font-size: 28px; }
            .costing-section-title { font-size: 22px; }
            .costing-field label { font-size: 15px; }
            .costing-field .form-control,
            .costing-field .select2-container--bootstrap4 .select2-selection { height: 52px; min-height: 52px; }
            .costing-field .select2-container--bootstrap4 .select2-selection--multiple { min-height: 52px !important; }
        }
        @media (max-width: 767.98px) {
            .costing-create-page { padding: 8px 8px 20px; }
            .costing-create-card > .card-body { padding: 16px 14px 24px; }
            .costing-page-title { font-size: 26px; line-height: 1.25; }
            .costing-section-title { font-size: 20px; }
            .costing-field label { font-size: 14px; }
            .costing-field .form-control,
            .costing-field .select2-container--bootstrap4 .select2-selection { height: 48px; min-height: 48px; }
            .costing-field .form-control { padding: 0 12px; }
            .costing-field textarea.form-control { padding: 8px 12px; }
            .costing-field .select2-container--bootstrap4 .select2-selection--single { padding: 0 38px 0 12px; }
            .costing-field .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow { right: 12px; }
            .costing-field .select2-container--bootstrap4 .select2-selection__clear { right: 10px; }
            .costing-field .select2-container--bootstrap4 .select2-selection--multiple { min-height: 48px !important; padding: 4px 12px; }
            .costing-field.is-invalid::after { font-size: 12px; }
            .costing-error-alert { align-items: flex-start; font-size: 12px; }
        }
        @media (max-width: 480px) {
            .costing-create-card > .card-body { padding: 14px 10px 22px; }
            .costing-page-title { font-size: 24px; }
            .costing-section-title { padding: 6px 10px; font-size: 18px; }
            .costing-section-body { padding: 10px 8px 2px; }
            .costing-fields, .costing-fields.four-columns, .costing-fields.price-columns { grid-template-columns: minmax(0, 1fr); }
        }
        /* QA font (Figma Create Costing): tombol Save = SF Pro Rounded Bold 18 (bawaan .mkt-btn),
           dropzone Inter Regular 12 + Browse Inter Bold 12, Upload Now Inter Bold 14,
           label Marketing Order Poppins Medium 18, input tanggal Poppins Medium 14 — semua ukuran layar. */
        .costing-create-page .costing-actions .mkt-btn, .costing-create-page button[type="submit"].mkt-btn { font-family: "SF Pro Rounded", ui-rounded, "MKT Rounded", sans-serif !important; font-size: 18px !important; font-weight: 700 !important; }
        .costing-create-page .costing-picture-dropzone, .costing-create-page .costing-picture-dropzone span { font-family: Inter, sans-serif !important; font-size: 12px !important; font-weight: 400 !important; }
        .costing-create-page .costing-picture-dropzone b { font-family: Inter, sans-serif !important; font-size: 12px !important; font-weight: 700 !important; }
        .costing-create-page .costing-upload-now { font-family: Inter, sans-serif !important; font-size: 14px !important; font-weight: 700 !important; }
        /* Marketing Order selalu 2px di atas label lain (16/15/14 -> 18/17/16). */
        .costing-create-page .costing-field label[for="marketing_order"] { font-size: 18px !important; font-weight: 500 !important; }
        @media (max-width: 991.98px) { .costing-create-page .costing-field label[for="marketing_order"] { font-size: 17px !important; } }
        @media (max-width: 767.98px) { .costing-create-page .costing-field label[for="marketing_order"] { font-size: 16px !important; } }
        .costing-create-page .costing-field input[type="date"] { font-family: 'Poppins', sans-serif !important; font-size: 14px !important; font-weight: 500 !important; }
        .costing-no-image { color: #ababab; font-size: 14px; font-weight: 500; }
        @media (max-width: 575.98px) {
            .costing-crumb { font-size: 13px; }
            .costing-no-image { font-size: 13px; }
        }
        /* Shipping & Destinations: 2 baris x 3 field (costing-field-md) mengisi penuh lebar kartu, jarak 16px;
           menyusut proporsional di layar sempit, tablet 2 per baris, HP 1 per baris. */
        .costing-fields.three-columns { display: flex !important; flex-wrap: wrap; gap: 16px; }
        .costing-fields.three-columns > .costing-field-md { flex: 1 1 calc((100% - 32px) / 3); min-width: 0; }
        @media (max-width: 991.98px) { .costing-fields.three-columns > .costing-field-md { flex-basis: calc((100% - 16px) / 2); max-width: none; } }
        @media (max-width: 575.98px) { .costing-fields.three-columns > .costing-field-md { flex-basis: 100%; } }

    </style>
@endsection

@section('content')
<div class="costing-create-page">
    <div class="card costing-create-card">
        <form action="{{ route('store-costing') }}" method="POST" id="form-costing" enctype="multipart/form-data">
            @csrf
            <div class="card-body">
                <div class="costing-crumb"><a href="{{ route('master-costing') }}">Marketing &nbsp;›&nbsp; Proses &nbsp;›&nbsp; Costing</a> &nbsp;›&nbsp; <b>Create Costing</b></div>
                <h1 class="costing-page-title">Create Costing <i class="fas fa-info-circle"></i></h1>
                <p class="costing-page-subtitle"><i class="fas fa-database"></i> Untuk Menambah Data Costing</p>
                <div class="costing-error-alert" id="costing-error-alert" role="alert" hidden>
                    <i class="fas fa-exclamation-circle"></i><span id="costing-error-text"></span>
                    <button type="button" class="costing-error-alert-close" aria-label="Tutup" onclick="this.parentElement.hidden = true"><i class="far fa-times-circle"></i></button>
                </div>

                @if ($errors->any())
                    <div class="alert alert-danger py-2" role="alert">
                        <ul class="mb-0 pl-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="costing-layout">
                    <div class="costing-top">
                        <div>
                            <section class="costing-section">
                                <h2 class="costing-section-title">Information Buyer<span class="required-mark">*</span></h2>
                                <div class="costing-section-body">
                                    <div class="costing-fields">
                                        <div class="costing-field">
                                            <label for="buyer">Buyer Name</label>
                                            <select name="buyer" id="buyer" class="form-control select2bs4" data-placeholder="Choose Buyer Name" required>
                                                <option value="">Choose Buyer Name</option>
                                                @foreach ($buyers as $b)
                                                    <option value="{{ $b->id_supplier ?? $b->Id_Supplier }}">{{ $b->supplier ?? $b->Supplier }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="costing-field">
                                            <label for="buyer_code">Kode Buyer</label>
                                            <input type="text" id="buyer_code" class="form-control" placeholder="Buyer" readonly>
                                        </div>
                                        <div class="costing-field">
                                            <label>Brand</label>
                                            <select name="brand" class="form-control select2bs4" required>
                                                <option value="">Choose Brand</option>
                                                <option value="Sample Brand">Sample Brand</option>
                                            </select>
                                        </div>
                                        <div class="costing-field">
                                            <label for="season_id">Season</label>
                                            <select name="season_id" id="season_id" class="form-control select2bs4" required>
                                                <option value="">Choose Seasons</option>
                                                @foreach ($seasons as $s)
                                                    <option value="{{ $s->id_season }}">{{ $s->season }} - {{ $s->season_desc }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="costing-field span-2">
                                            <label>Style</label>
                                            <input type="text" name="style" class="form-control" placeholder="Style" required>
                                        </div>
                                    </div>
                                </div>
                            </section>

                            <section class="costing-section">
                                <h2 class="costing-section-title">Product & Type<span class="required-mark">*</span></h2>
                                <div class="costing-section-body">
                                    <div class="costing-fields">
                                        <div class="costing-field">
                                            <label for="product_group">Product Group</label>
                                            <select name="product_group" id="product_group" class="form-control select2bs4" required>
                                                <option value="">Choose Product Group</option>
                                                @foreach ($product_groups as $pg)
                                                    <option value="{{ $pg->product_group }}">{{ $pg->product_group }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="costing-field">
                                            <label for="product_item">Product Item</label>
                                            <select name="product_item" id="product_item" class="form-control select2bs4" required>
                                                <option value="">Choose Product Item</option>
                                            </select>
                                        </div>
                                        <div class="costing-field">
                                            <label for="type">Costing Type</label>
                                            <select name="type" id="type" class="form-control select2bs4" required>
                                                <option value="">Choose Costing Type</option>
                                                <option value="single">SINGLE</option>
                                                <option value="multiple">MULTIPLE</option>
                                            </select>
                                        </div>
                                        <div class="costing-field">
                                            <label for="product_set">Product Type</label>
                                            <select id="product_set" name="product_set[]" class="form-control select2bs4" multiple disabled data-placeholder="Product Type">
                                                @foreach ($master_set as $m_set)
                                                    <option value="{{ $m_set->id }}">{{ $m_set->nama ?? $m_set->id }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="costing-field">
                                            <label for="tipe_ws">WS Type</label>
                                            <select name="tipe_ws" id="tipe_ws" class="form-control select2bs4" required>
                                                <option value="">Choose WS Type</option>
                                                <option value="GLOBAL">GLOBAL</option>
                                                <option value="STD">STANDAR</option>
                                            </select>
                                        </div>
                                        <div class="costing-field">
                                            <label for="unit">Unit</label>
                                            <select name="unit" id="unit" class="form-control select2bs4" required>
                                                <option value="">Choose Unit</option>
                                                @foreach ($units as $u)
                                                    <option value="{{ $u->nama_pilihan }}" {{ $u->nama_pilihan == 'PCS' ? 'selected' : '' }}>{{ $u->nama_pilihan }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </div>

                        <section class="costing-section">
                            <h2 class="costing-section-title">Upload Image<span class="required-mark">*</span></h2>
                            <div class="costing-section-body pb-2">
                                <input type="file" name="upload_foto[]" id="upload_foto" accept="image/*" multiple hidden>
                                <div class="costing-upload-box">
                                    <div id="picture-dropzone" class="costing-picture-dropzone" role="button" tabindex="0">
                                        <i class="fas fa-file-alt"></i>
                                        <span>Drag n Drop here Or <b>Browse</b></span>
                                    </div>
                                    <button type="button" id="btn-upload-now" class="mkt-btn mkt-btn--navy mkt-btn--sm costing-upload-now">Upload Now</button>
                                </div>

                                <div id="picture-thumbnails" class="costing-picture-thumbnails" aria-label="Selected image previews"></div>
                                <button type="button" id="clear-pictures" class="mkt-btn mkt-btn--red mkt-btn--sm costing-picture-clear" disabled>
                                    <i class="fas fa-trash-alt"></i> Clear All
                                </button>

                                <div class="costing-picture-carousel">
                                    <div class="d-flex align-items-center justify-content-between w-100">
                                        <button type="button" id="picture-prev" class="costing-picture-nav" aria-label="Previous image"><i class="fas fa-chevron-left"></i></button>
                                        <div class="mkt-cf-stage flex-grow-1">
                                            <img class="mkt-cf-side is-prev is-empty" alt="Gambar sebelumnya">
                                            <img id="img_preview" class="costing-picture-main mkt-cf-main" alt="Costing image preview" hidden>
                                            <span id="no_img_text" class="costing-no-image">No image selected</span>
                                            <img class="mkt-cf-side is-next is-empty" alt="Gambar berikutnya">
                                        </div>
                                        <button type="button" id="picture-next" class="costing-picture-nav" aria-label="Next image"><i class="fas fa-chevron-right"></i></button>
                                    </div>
                                    <div id="carousel-dots" class="mkt-cf-dots"></div>
                                </div>
                            </div>
                        </section>
                    </div>

                    <section class="costing-section span-full">
                        <h2 class="costing-section-title">Shipping & Destinations<span class="required-mark">*</span></h2>
                        <div class="costing-section-body">
                            <div class="costing-fields three-columns">
                                <div class="costing-field costing-field-md">
                                    <label for="ship_mode">Ship Mode</label>
                                    <select name="ship_mode" id="ship_mode" class="form-control select2bs4" required>
                                        <option value="">Choose Ship Mode</option>
                                        @foreach ($shipmodes as $sm)
                                            <option value="{{ $sm->id }}">{{ $sm->shipmode }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="costing-field costing-field-md">
                                    <label for="shipment_type">Shipment Type</label>
                                    <select name="shipment_type" id="shipment_type" class="form-control select2bs4" required>
                                        <option value="">Choose Shipment Type</option>
                                        <option value="local">LOCAL</option>
                                        <option value="export">EXPORT</option>
                                    </select>
                                </div>
                                <div class="costing-field costing-field-md">
                                    <label for="deldate">Delivery Date</label>
                                    <input type="date" name="deldate" id="deldate" class="form-control" required>
                                </div>
                                <div class="costing-field costing-field-md">
                                    <label for="marketing_order">Marketing Order</label>
                                    <select name="marketing_order" id="marketing_order" class="form-control select2bs4" required>
                                        <option value="">Choose Marketing Order</option>
                                        @foreach ($marketing_orders as $mo)
                                            <option value="{{ $mo->id }}">{{ $mo->mkt_order }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="costing-field costing-field-md">
                                    <label for="status">Status</label>
                                    <select name="status" id="status" class="form-control select2bs4" required>
                                        <option value="">Choose Status</option>
                                        <option value="CONFIRM">CONFIRM</option>
                                        <option value="CANCEL">CANCEL</option>
                                        <option value="SAMPLE">SAMPLE</option>
                                    </select>
                                </div>
                                <input type="hidden" name="main_dest" value="">
                                <input type="hidden" name="market" value="">
                                <div class="costing-field costing-field-md">
                                    <label for="notes">Notes</label>
                                    <textarea id="notes" name="notes" rows="1" class="form-control" placeholder="Notes"></textarea>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="costing-section">
                        <h2 class="costing-section-title">Parameter Costing<span class="required-mark">*</span></h2>
                        <div class="costing-section-body">
                            <div class="costing-fields four-columns">
                                <div class="costing-field">
                                    <label for="qty_planned">QTY Planned (PCS)</label>
                                    <input type="text" name="qty_planned" id="qty_planned" class="form-control input-decimal" placeholder="QTY Planned (PCS)" required>
                                </div>
                                <div class="costing-field">
                                    <label>QTY (PCS)</label>
                                    <input type="text" name="qty" class="form-control input-decimal" placeholder="QTY (PCS)" required>
                                </div>
                                <div class="costing-field">
                                    <label for="smv">SMV (Min)</label>
                                    <input type="text" name="smv" id="smv" class="form-control input-money" inputmode="decimal" data-max-decimals="3" placeholder="SMV (Min)" required>
                                </div>
                                <div class="costing-field">
                                    <label for="vat">VAT (%)</label>
                                    <input type="text" name="vat" id="vat" class="form-control input-money" inputmode="decimal" data-max-decimals="2" placeholder="VAT (%)" required>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="costing-section">
                        <h2 class="costing-section-title">Price & Currency<span class="required-mark">*</span></h2>
                        <div class="costing-section-body">
                            <div class="costing-fields price-columns">
                                <div class="costing-field">
                                    <label for="curr">Currency</label>
                                    <select name="curr" id="curr" class="form-control select2bs4" required>
                                        <option value="">Choose Currency</option>
                                        @foreach ($currencies as $cur)
                                            <option value="{{ $cur->id }}">{{ $cur->nama_pilihan }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="costing-field">
                                    <label for="confirm_price">Confirm Price</label>
                                    <input type="text" name="confirm_price" id="confirm_price" class="form-control input-money" inputmode="decimal" required placeholder="Confirm Price">
                                </div>
                                <div class="costing-field">
                                    <label>Rate to IDR</label>
                                    <input type="text" name="rate_to_idr" class="form-control input-money" inputmode="decimal" placeholder="Rate to IDR" required>
                                </div>
                                <div class="costing-field">
                                    <label>Rate from IDR</label>
                                    <input type="text" name="rate_from_idr" class="form-control input-money" inputmode="decimal" placeholder="Rate from IDR" required>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>

                <div class="costing-form-actions">
                    <button type="submit" class="mkt-btn mkt-btn--green">Save</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('custom-script')
<script src="{{ asset('dist/js/marketing-title-case.js') }}?v={{ @filemtime(public_path('assets/dist/js/marketing-title-case.js')) }}"></script>
<script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
<script>
    // Angka format Indonesia untuk Confirm Price / Rate / SMV (lihat handler .input-money).
    function mktMoney(selector) {
        return parseFloat(String($(selector).val() || '').replace(/\./g, '').replace(',', '.')) || 0;
    }
    function mktMoneyText(text, maxDecimals) {
        const value = String(text).replace(/[^0-9,]/g, '');
        const comma = value.indexOf(',');
        const int = (comma < 0 ? value : value.slice(0, comma)).replace(/^0+(?=\d)/, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        const dec = value.slice(comma + 1).replace(/,/g, '');
        return comma < 0 ? int : int + ',' + (maxDecimals >= 0 ? dec.slice(0, maxDecimals) : dec);
    }

    $(document).ready(function() {
        // allowClear = tanda X untuk menghapus pilihan (state 2); hanya untuk select yang punya opsi kosong / multiple.
        // Dropdown nama (buyer, brand, season, product, supplier, content) tampil Title Case; singkatan (PT, CV, LTD) tetap kapital.
        // Hanya teks tampilan select2 — <option> & value yang dikirim tetap data asli.
        const COSTING_TC_SELECTS = ['buyer', 'brand', 'season_id', 'product_group', 'product_item', 'shipment_type', 'marketing_order', 'txt_item', 'txt_supplier', 'm_item', 'm_supplier'];
        const costingTcOpts = el => (window.mktTitleCaseSelect2 && COSTING_TC_SELECTS.includes(el.id || el.name)) ? window.mktTitleCaseSelect2() : {};
        $('.select2bs4').each(function () {
            const clearable = $(this).prop('multiple') || $(this).find('option[value=""]').length > 0;
            $(this).select2(Object.assign({ theme: 'bootstrap4' }, clearable ? {
                allowClear: true,
                placeholder: $(this).data('placeholder') || $(this).find('option[value=""]').first().text() || 'Choose'
            } : {}, costingTcOpts(this)));
        });

        // State 5 (error): field wajib yang kosong saat Save diberi .is-invalid (label, border, teks merah + "Required")
        // dan banner "N fields are empty" di atas; tooltip bawaan browser dimatikan. Hilang lagi begitu diisi.
        let firstInvalid = null;
        function refreshCostingErrorAlert() {
            const $invalid = $('.costing-field.is-invalid');
            const spacing = $invalid.filter('[data-error]').length;
            const empty = $invalid.length - spacing;
            const parts = [];
            if (empty) parts.push(empty + (empty === 1 ? ' field is' : ' fields are') + ' empty.');
            if (spacing) parts.push('Style tidak boleh ada spasi di awal/akhir atau spasi ganda.');
            $('#costing-error-text').text(parts.join(' ') + ' Fix them, then save again.');
            $('#costing-error-alert').prop('hidden', $invalid.length === 0);
        }

        // Style masuk ke act_costing.styleno: spasi di awal/akhir atau spasi ganda berpengaruh ke produksi -> field error.
        const STYLE_SPACING_MSG = 'Tidak boleh ada spasi di awal/akhir atau spasi ganda.';
        function checkStyleSpacing(input) {
            const value = input.value;
            const bad = value !== value.trim() || /\s{2,}/.test(value);
            const $field = $(input).closest('.costing-field');
            input.setCustomValidity(bad ? STYLE_SPACING_MSG : '');
            if (bad) {
                $field.attr('data-error', STYLE_SPACING_MSG).addClass('is-invalid');
            } else {
                $field.removeAttr('data-error');
                if (input.checkValidity()) $field.removeClass('is-invalid');
            }
            if (!$('#costing-error-alert').prop('hidden')) refreshCostingErrorAlert();
        }
        $('#form-costing input[name="style"]').on('input change', function () { checkStyleSpacing(this); });
        document.getElementById('form-costing').addEventListener('invalid', function (event) {
            event.preventDefault();
            const $field = $(event.target).closest('.costing-field').addClass('is-invalid');
            if (!firstInvalid) {
                firstInvalid = $field[0];
                setTimeout(() => {
                    refreshCostingErrorAlert();
                    document.getElementById('costing-error-alert').scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstInvalid = null;
                }, 0);
            }
        }, true);
        $(document).on('change input', '.costing-field.is-invalid :input', function () {
            if (this.checkValidity()) {
                $(this).closest('.costing-field').removeClass('is-invalid');
                if (!$('#costing-error-alert').prop('hidden')) refreshCostingErrorAlert();
            }
        });

        $('.costing-section-title')
            .attr({ role: 'button', tabindex: 0 })
            .append('<span class="costing-section-chevron"><i class="fas fa-chevron-down"></i></span>')
            .on('click keydown', function(event) {
                if (event.type === 'keydown' && event.key !== 'Enter' && event.key !== ' ') return;
                event.preventDefault();
                const $section = $(this).closest('.costing-section');
                $section.toggleClass('is-collapsed').children('.costing-section-body').stop(true, true).slideToggle(150);
            });

        let costingImageUrls = [];
        let activeCostingImage = 0;

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

        function renderCostingImages() {
            const $mainImage = $('#img_preview');
            const $thumbnails = $('#picture-thumbnails').empty();
            const $dots = $('#carousel-dots').empty();
            const hasImages = costingImageUrls.length > 0;

            $('#no_img_text').prop('hidden', hasImages);
            $mainImage.prop('hidden', !hasImages);
            $('#picture-prev, #picture-next').prop('disabled', costingImageUrls.length < 2);
            $('#clear-pictures').prop('disabled', !hasImages);

            if (!hasImages) {
                $mainImage.attr('src', '');
                paintCoverflow($mainImage.closest('.mkt-cf-stage'), [], 0, $.noop);
                return;
            }

            activeCostingImage = (activeCostingImage + costingImageUrls.length) % costingImageUrls.length;
            $mainImage.attr('src', costingImageUrls[activeCostingImage].url);
            const goTo = index => { activeCostingImage = index; renderCostingImages(); };
            paintCoverflow($mainImage.closest('.mkt-cf-stage'), costingImageUrls.map(image => image.url), activeCostingImage, goTo);
            paintCoverflowDots($dots, costingImageUrls.length, activeCostingImage, goTo);

            costingImageUrls.forEach((image, index) => {
                // Buat Thumbnail HTML
                let thumb = $('<div>', {
                    class: `costing-picture-thumb`,
                    style: `position: relative; width: 60px; height: 60px; cursor: pointer; border-radius: 4px; overflow: hidden; border: ${index === activeCostingImage ? '2px solid #3085d6' : '1px solid #dce4ec'};`
                });

                thumb.append($('<img>', { src: image.url, style: 'width: 100%; height: 100%; object-fit: cover; opacity: 0.8;' }));

                // Gambar aktif cukup ditandai garis biru (tanpa ikon centang).
                if (index !== activeCostingImage) {
                    let closeBtn = $('<div>', {
                        style: 'position: absolute; top: 3px; right: 3px; background: rgba(0,0,0,0.7); color: white; border-radius: 50%; width: 16px; height: 16px; display: flex; align-items: center; justify-content: center; font-size: 9px;'
                    }).append('<i class="fas fa-times"></i>');

                    closeBtn.on('click', function(e) {
                        e.stopPropagation();
                        costingImageUrls.splice(index, 1);
                        if (activeCostingImage >= index && activeCostingImage > 0) activeCostingImage--;
                        if (costingImageUrls.length === 0) activeCostingImage = 0;
                        renderCostingImages();
                    });
                    thumb.append(closeBtn);
                }

                thumb.on('click', function() {
                    activeCostingImage = index;
                    renderCostingImages();
                });

                $thumbnails.append(thumb);
            });
        }

        renderCostingImages();

        function setCostingFiles(files) {
            const selectedFiles = Array.from(files || []);
            const invalidFile = selectedFiles.find(file => !file.type.startsWith('image/'));
            if (invalidFile) {
                $('#upload_foto').val('');
                Swal.fire('File tidak valid', `${invalidFile.name} bukan file gambar.`, 'error');
                return;
            }

            const allFiles = costingImageUrls.map(image => image.file).concat(selectedFiles);
            const uniqueFiles = allFiles.filter((file, index) => allFiles.findIndex(candidate =>
                candidate.name === file.name && candidate.size === file.size && candidate.lastModified === file.lastModified
            ) === index);
            const transfer = new DataTransfer();
            uniqueFiles.forEach(file => transfer.items.add(file));
            $('#upload_foto')[0].files = transfer.files;
            costingImageUrls.forEach(image => URL.revokeObjectURL(image.url));
            costingImageUrls = uniqueFiles.map(file => ({ url: URL.createObjectURL(file), name: file.name, file }));
            activeCostingImage = selectedFiles.length ? Math.max(0, uniqueFiles.length - selectedFiles.length) : 0;
            renderCostingImages();
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
            setCostingFiles(event.originalEvent.dataTransfer.files);
        });

        $('#upload_foto').on('change', function() {
            setCostingFiles(this.files);
        });

        $('#clear-pictures').on('click', function() {
            costingImageUrls.forEach(image => URL.revokeObjectURL(image.url));
            costingImageUrls = [];
            activeCostingImage = 0;
            $('#upload_foto').val('');
            renderCostingImages();
        });

        $('#picture-prev').on('click', function() {
            activeCostingImage--;
            renderCostingImages();
        });
        $('#picture-next').on('click', function() {
            activeCostingImage++;
            renderCostingImages();
        });

        $('#buyer').on('change', function() {
            $('#buyer_code').val($(this).val() || '');
        }).trigger('change');

        $(document).on('input', '.input-decimal', function() {
            let val = $(this).val().replace(/[^0-9.]/g, '');
            let parts = val.split('.');
            if (parts.length > 2) val = parts[0] + '.' + parts.slice(1).join('');
            const maxDec = parseInt(this.dataset.maxDecimals, 10);
            if (maxDec >= 0 && val.includes('.')) val = val.slice(0, val.indexOf('.') + 1 + maxDec);
            $(this).val(val);
        });

        // Confirm Price / Rate / SMV: format Indonesia (1.234.567,89); titik dari keyboard dianggap koma desimal.
        $(document).on('input', '.input-money', function (e) {
            let text = this.value;
            if (e.originalEvent && e.originalEvent.data === '.' && this.selectionStart > 0) {
                text = text.slice(0, this.selectionStart - 1) + ',' + text.slice(this.selectionStart);
            }
            const fromEnd = this.value.length - this.selectionStart;
            this.value = mktMoneyText(text, parseInt(this.dataset.maxDecimals, 10));
            const pos = Math.max(0, this.value.length - fromEnd);
            this.setSelectionRange(pos, pos);
        });

        $(document).on('select2:open', function(e) {
            setTimeout(function() {
                document.querySelector('.select2-container--open .select2-search__field').focus();
            }, 50);
        });

        function checkShipmentType() {
            let shipmentType = $('#shipment_type').val();
            if (shipmentType === 'local') {
                $('#vat').val('11,00');
            }
        }

        $('#shipment_type').on('change', function() {
            checkShipmentType();
        });
        checkShipmentType();

        $('#main_dest').on('change', function() {
            let curr_val = $(this).val();
            let curr_count = curr_val ? curr_val.length : 0;
            if (curr_count > 1) {
                Swal.fire({
                    title: 'Perhatian!',
                    text: 'Anda memilih lebih dari 1 Destination. Ingat untuk menggabungkan WS jika diperlukan!',
                    icon: 'info',
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, Mengerti'
                });
            }
        });

        $('#product_group').on('change', function() {
            let selected_grup = $(this).val();
            let select_item = $('#product_item');
            if(selected_grup) {
                $.post("{{ route('get-product-items') }}", { _token: "{{ csrf_token() }}", product_group: selected_grup }, function(res) {
                    select_item.empty().append('<option value="">Pilih Product Item</option>');
                    $.each(res, function(key, value) { select_item.append(`<option value="${value.id}">${value.product_item}</option>`); });
                    select_item.trigger('change');
                });
            } else {
                select_item.empty().append('<option value="">Pilih Product Item</option>').trigger('change');
            }
        });

        $('#form-costing').on('submit', function(e) {
            e.preventDefault();
            let form = this;

            Swal.fire({
                title: 'Simpan Data Costing?',
                text: "Pastikan semua data Header sudah terisi dengan benar!",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Simpan!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Menyimpan...',
                        text: 'Mohon tunggu sebentar',
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    form.submit();
                }
            });
        });
    });

    function toggleSetHeader() {
        let type = $('#type').val();
        if (type === 'single') {
            $('#product_set').val('').trigger('change').prop('disabled', true);
        } else {
            $('#product_set').prop('disabled', false);
        }
    }
    toggleSetHeader();
    $('#type').on('change', function() {
        toggleSetHeader();
    });
</script>
@endsection
