@extends('layouts.index')

@php
    // Empat tabel di halaman ini memakai satu template; kolom & aksi diatur di sini.
    $docSections = [
        ['key' => 'costing', 'title' => 'Approval Costing', 'number' => 'No Costing'],
        ['key' => 'bom', 'title' => 'Approval BOM', 'number' => 'No BOM'],
    ];
    $unlockSections = [
        ['key' => 'costing', 'title' => 'Unlock Costing', 'number' => 'No Costing', 'enabled' => true],
        ['key' => 'bom', 'title' => 'Unlock BOM', 'number' => 'No BOM', 'enabled' => true],
        ['key' => 'so', 'title' => 'Unlock SO', 'number' => 'No SO', 'enabled' => true],
    ];
    $searchColumns = ['number' => null, 'worksheet' => 'No Worksheet', 'buyer' => 'Buyer', 'brand' => 'Brand', 'style' => 'Style'];
@endphp

@section('custom-link')
    <link rel="stylesheet" href="{{ asset('dist/css/marketing-buttons.css') }}?v={{ @filemtime(public_path('assets/dist/css/marketing-buttons.css')) }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('dist/css/marketing-fonts.css') }}?v={{ @filemtime(public_path('assets/dist/css/marketing-fonts.css')) }}">
    <style>
        .ac-page { color: #1e293b; }
        .ac-title { margin: 0; font-size: 26px; font-weight: 700; color: #475569; }
        /* Breadcrumb sama dengan SO/BOM create. */
        .ac-breadcrumb { margin-bottom: 18px; padding: 8px 10px; border-radius: 4px; background: #284A63; color: #b4b4b4; font-family: 'Poppins', sans-serif; font-size: 16px; font-weight: 500; }
        .ac-breadcrumb strong { color: #fff; font-weight: 500; }
        .ac-subtitle { margin: 2px 0 18px; color: #64748b; font-size: 12px; }

        /* Kartu ringkasan */
        .ac-summary { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 16px; margin-bottom: 26px; }
        .ac-summary-card { display: block; min-height: 108px; padding: 14px 16px; border: 1px solid transparent; border-radius: 10px; color: inherit; text-decoration: none !important; transition: transform .15s; }
        .ac-summary-card:hover { transform: translateY(-2px); }
        .ac-summary-card span { display: block; font-size: 13px; font-weight: 600; color: #475569; }
        .ac-summary-card strong { display: block; margin: 2px 0; font-size: 40px; line-height: 1.1; text-align: center; color: #1e293b; }
        .ac-summary-card small { color: #94a3b8; font-size: 10px; }
        .ac-summary-card.is-costing { background: #e0f2fe; border-color: #bae6fd; }
        .ac-summary-card.is-bom { background: #fefce8; border-color: #fef08a; }
        .ac-summary-card.is-costing span { color: #0369a1; }
        .ac-summary-card.is-bom span { color: #a16207; }
        .ac-summary-card.is-unlock-costing span { color: #be123c; }
        .ac-summary-card.is-unlock-bom span { color: #6d28d9; }
        .ac-summary-card.is-unlock-costing { background: #ffe4e6; border-color: #fecdd3; }
        .ac-summary-card.is-unlock-bom { background: #ede9fe; border-color: #ddd6fe; }
        .ac-summary-card.is-unlock-so span { color: #c2410c; }
        .ac-summary-card.is-unlock-so { background: #ffedd5; border-color: #fed7aa; }

        .ac-section-title { margin: 0 0 10px; font-size: 17px; font-weight: 700; color: #334155; }
        .ac-card { margin-bottom: 26px; padding: 14px 16px; border: 1px solid #e2e8f0; border-radius: 12px; background: #fff; box-shadow: 0 4px 16px rgba(15, 23, 42, .05); }
        /* Satu tabel: card ringkasan = tab; card aktif diberi border tebal sesuai warnanya. */
        .ac-summary-card { --ac-accent: #475569; cursor: pointer; }
        .ac-summary-card.is-costing { --ac-accent: #0369a1; }
        .ac-summary-card.is-bom { --ac-accent: #a16207; }
        .ac-summary-card.is-unlock-costing { --ac-accent: #be123c; }
        .ac-summary-card.is-unlock-bom { --ac-accent: #6d28d9; }
        .ac-summary-card.is-unlock-so { --ac-accent: #c2410c; }
        .ac-summary-card.is-active { border-color: var(--ac-accent); box-shadow: 0 0 0 1px var(--ac-accent), 0 6px 16px rgba(15, 23, 42, .12); }
        .ac-panel[hidden] { display: none !important; }

        /* Toolbar: tanggal, search by, kata kunci, tombol aksi */
        .ac-toolbar { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; }
        .ac-toolbar .ac-search-by-wrap { position: relative; width: 170px; }
        .ac-toolbar .ac-keyword-wrap { position: relative; width: 220px; }
        .ac-toolbar .ac-keyword { height: 32px; padding-left: 28px; font-size: 12px; }
        .ac-input-icon { position: absolute; top: 16px; left: 10px; z-index: 2; transform: translateY(-50%); color: #64748b; font-size: 11px; pointer-events: none; }
        .ac-search-by-wrap .select2-selection__rendered { padding-left: 22px !important; }
        .ac-actions { display: flex; flex-wrap: wrap; justify-content: flex-end; gap: 10px; margin-left: auto; }
        .ac-actions .mkt-btn--return { height: 50px; border-radius: 25px; font-size: 18px; }
        .ac-date-wrap { position: relative; }
        .ac-date-toggle { width: 32px; height: 32px; border: 1px solid #cbd5e1; border-radius: 6px; background: #fff; color: #475569; }
        .ac-date-panel { position: absolute; top: 36px; left: 0; z-index: 20; display: none; width: 250px; padding: 10px; border: 1px solid #e2e8f0; border-radius: 8px; background: #fff; box-shadow: 0 8px 24px rgba(15, 23, 42, .12); font-size: 11px; }
        .ac-date-panel.show { display: block; }
        .ac-date-panel input { height: 30px; font-size: 11px; }
        .ac-search-by-wrap .select2-container--bootstrap4 .select2-selection { min-height: 32px; font-size: 12px; }

        /* Ringkasan filter */
        .ac-filter-summary { display: flex; flex-wrap: wrap; align-items: center; gap: 6px; min-height: 26px; margin-top: 8px; color: #64748b; font-size: 11px; }
        .ac-chip { display: inline-flex; align-items: center; gap: 4px; padding: 2px 6px 2px 9px; border-radius: 10px; background: #e0f2fe; color: #0369a1; font-weight: 500; }
        .ac-chip-remove { padding: 0 2px; border: 0; background: none; color: inherit; font-size: 12px; line-height: 1; }
        .ac-chip-remove:hover { color: #e11d48; }
        .ac-clear { padding: 0; border: 0; background: none; color: #475569; font-size: 11px; text-decoration: underline; }

        /* Tab status = legend + filter */
        .ac-status-tabs { display: flex; flex-wrap: wrap; align-items: center; gap: 4px 18px; margin: 4px 0 6px; border-bottom: 1px solid #e2e8f0; font-size: 11px; }
        .ac-status-selected { padding: 6px 0; font-size: 14px; font-weight: 700; font-family: 'inter', sans-serif; }
        .ac-status-tab { display: inline-flex; align-items: center; gap: 6px; padding: 6px 0; border: 0; border-bottom: 2px solid transparent; background: none; color: #334155; font-size: 14px; font-weight: 500; font-family: 'inter', sans-serif; cursor: pointer; }
        .ac-status-tab.active { border-bottom-color: currentColor; }
        .ac-status-tab[data-status="return"] { color: #ea580c; }
        .ac-status-tab[data-status="cancel"] { color: #dc2626; }
        .ac-status-tab[data-status="approved"] { color: #16a34a; }
        .ac-status-tab[data-status="unlocked"] { color: #2563eb; }
        .ac-status-tab[data-status="request"] { color: #2563eb; }
        .ac-count { min-width: 18px; padding: 0 6px; border-radius: 9px; background: #e2e8f0; color: #475569; font-size: 10px; font-weight: 600; text-align: center; }

        /* Tabel */
        /* Tabel selalu muat selebar kartu (tanpa scroll ke kanan): teks boleh turun baris, kolom kecil tetap satu baris. */
        .ac-table-wrap { width: 100%; }
        .ac-table { width: 100% !important; margin-bottom: 0; font-size: 12px; font-weight: 500; }
        .ac-table thead th { padding: 6px 8px; border-color: #c9d9f2; background: #cfe0ff; color: #26394c; font-size: 12px; font-weight: 500; text-transform: uppercase; white-space: normal; overflow-wrap: break-word; font-family: 'inter', sans-serif; }
        .ac-table tbody td { padding: 6px 8px; border-color: #eef2f7; vertical-align: middle; white-space: normal; overflow-wrap: anywhere; font-family: 'inter', sans-serif; font-size: 15px; font-weight: 400; }
        .ac-table th:nth-child(-n+3), .ac-table td:nth-child(-n+3), .ac-table td:last-child { width: 1%; white-space: nowrap; }
        .ac-table .ac-date-badge, .ac-table .ac-badge { white-space: nowrap; }
        /* Baris kosong & baris timeline (colspan) tetap melebar normal. */
        .ac-table td[colspan] { width: auto; white-space: normal; }
        .ac-table tbody tr:hover { background: #f8fafc; }
        .ac-table input[type="checkbox"] { width: 14px; height: 14px; accent-color: #334155; }
        .ac-date-badge { display: inline-block; padding: 2px 10px; border-radius: 10px; background: #e2e8f0; color: #475569; font-weight: 500; }
        .ac-badge { display: inline-flex; align-items: center; gap: 4px; padding: 1px 8px; border: 1px solid; border-radius: 10px; font-weight: 500; }
        .ac-badge::before { content: ""; width: 5px; height: 5px; border-radius: 50%; background: currentColor; }
        .ac-badge.is-awaiting { border-color: #e2e8f0; background: #f1f5f9; color: #64748b; }
        .ac-badge.is-return { border-color: #fed7aa; background: #fff7ed; color: #ea580c; }
        .ac-badge.is-cancel { border-color: #fecaca; background: #fef2f2; color: #dc2626; }
        .ac-badge.is-approved { border-color: #bbf7d0; background: #f0fdf4; color: #16a34a; }
        .ac-badge.is-unlocked,
        .ac-badge.is-request { border-color: #bfdbfe; background: #eff6ff; color: #2563eb; }
        .ac-timeline-toggle { width: 20px; height: 20px; padding: 0; border: 1px solid #cbd5e1; border-radius: 50%; background: #fff; color: #64748b; font-size: 8px; }
        .ac-timeline-toggle[aria-expanded="true"] { border-color: #7dd3fc; background: #e0f2fe; color: #0284c7; }
        .ac-timeline { padding: 10px 18px; background: #f6f8fb; font-size: 11px; white-space: normal; }
        .ac-timeline-item { display: flex; gap: 10px; padding: 3px 0; }
        .ac-timeline-item time { flex: 0 0 120px; color: #64748b; }
        .ac-reason { max-width: 220px; overflow: hidden; text-overflow: ellipsis; }
        .ac-empty { padding: 26px 10px; color: #94a3b8; text-align: center; font-size: 12px; }
        .ac-card .dataTables_info, .ac-card .dataTables_length, .ac-card .dataTables_paginate { padding-top: 8px; color: #687582; font-size: 10px; }
        .ac-card .dataTables_length select { height: 25px; margin: 0 4px; font-size: 10px; }
        .ac-card .pagination { margin: 0; gap: 2px; }
        .ac-card .page-link { min-width: 22px; padding: 3px 7px; border: 0; border-radius: 4px; color: #475569; font-size: 10px; text-align: center; }
        .ac-card .page-item.active .page-link { background: #1e3a5f; color: #fff; }
        .ac-card .page-item.disabled .page-link { background: none; color: #cbd5e1; }
        .ac-badges { display: inline-flex; flex-wrap: wrap; gap: 4px; }

        .ac-table-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; }

        @media (max-width: 991.98px) {
            .ac-summary { grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; }
            .ac-summary-card { min-height: 92px; padding: 12px 14px; }
            .ac-summary-card strong { font-size: 32px; }
            .ac-actions { margin-left: 0; }
            .ac-actions .mkt-btn { min-width: 110px; height: 40px; border-radius: 20px; font-size: 15px; }
            .ac-actions .mkt-btn--return { min-width: 150px; }
            .ac-table tbody td { font-size: 13px; }
            .ac-table { min-width: 900px; }
        }
        @media (max-width: 767.98px) {
            .ac-title { font-size: 22px; }
            .ac-breadcrumb { font-size: 14px; }
            .ac-card { padding: 12px; }
            .ac-status-tabs { flex-wrap: nowrap; overflow-x: auto; white-space: nowrap; }
            .ac-status-tab, .ac-status-selected { flex: 0 0 auto; font-size: 13px; }
        }
        @media (max-width: 599.98px) {
            .ac-summary { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; margin-bottom: 18px; }
            .ac-summary-card:last-child:nth-child(odd) { grid-column: 1 / -1; }
            .ac-summary-card { min-height: 0; padding: 10px 12px; }
            .ac-summary-card span { font-size: 12px; }
            .ac-summary-card strong { font-size: 26px; }
            .ac-toolbar .ac-search-by-wrap { flex: 1 1 0; width: auto; min-width: 0; }
            .ac-toolbar .ac-keyword-wrap { flex: 1 1 100%; width: auto; }
            .ac-actions { flex: 1 1 100%; gap: 8px; }
            .ac-actions .mkt-btn { flex: 1 1 0; min-width: 0; height: auto; min-height: 40px; padding-left: 10px; padding-right: 10px; }
            .ac-actions .mkt-btn--return { flex-basis: 100%; order: -1; }
            .ac-card .dataTables_paginate { width: 100%; margin-top: 4px; }
            .ac-card .pagination { justify-content: center; flex-wrap: wrap; }
        }
        input[type="checkbox"]:not(.custom-control-input) { -webkit-appearance: none; appearance: none; flex: 0 0 16px; width: 16px; height: 16px; padding: 0; border: 1.5px solid #cfd4dc; border-radius: 4px; background: #fff no-repeat center / 10px 10px; vertical-align: middle; cursor: pointer; transition: background-color .15s ease, border-color .15s ease, box-shadow .15s ease; }
        input[type="checkbox"]:not(.custom-control-input):hover:not(:checked):not(:indeterminate):not(:disabled) { background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12'%3e%3cpath fill='none' stroke='%23cfd4dc' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' d='M2.5 6.2l2.3 2.3 4.7-4.9'/%3e%3c/svg%3e"); }
        input[type="checkbox"]:not(.custom-control-input):checked { border-color: #284a63; background-color: #284a63; background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12'%3e%3cpath fill='none' stroke='%23fff' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' d='M2.5 6.2l2.3 2.3 4.7-4.9'/%3e%3c/svg%3e"); }
        input[type="checkbox"]:not(.custom-control-input):indeterminate { border-color: #284a63; background-color: #284a63; background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12'%3e%3cpath fill='none' stroke='%23fff' stroke-width='2' stroke-linecap='round' d='M3 6h6'/%3e%3c/svg%3e"); }
        input[type="checkbox"]:not(.custom-control-input):focus { outline: 0; }
        input[type="checkbox"]:not(.custom-control-input):focus-visible { box-shadow: 0 0 0 4px #b3daf7; }
        input[type="checkbox"]:not(.custom-control-input):disabled { opacity: .5; cursor: not-allowed; }

        .ac-breadcrumb { margin-bottom: 16px; }
        .ac-frame { padding: 16px 32px 32px; border-radius: 10px; background: #fff; box-shadow: 0 10px 40px rgba(30, 41, 59, .08); }
        .ac-heading { margin-bottom: 20px; }
        .ac-heading h1 { margin: 0; color: #284a63; font-family: Poppins, sans-serif; font-size: 32px; font-weight: 700; }
        .ac-heading h1 i { font-size: 11px; vertical-align: middle; }
        .ac-heading p { margin: 4px 0 0; color: #284a63; font-family: Poppins, sans-serif; font-size: 12px; font-weight: 600; }
        .ac-heading p i { margin-right: 6px; color: #a3b2c7; }
        .ac-summary { gap: 24px; margin-bottom: 24px; }
        .ac-summary-card { min-height: 140px; padding: 14px 20px; border-radius: 12px; box-shadow: 0 4px 12px rgba(30, 41, 59, .10); }
        .ac-summary-card span { font-family: Poppins, sans-serif; font-size: 20px; font-weight: 600; color: #9e9e9e !important; }
        .ac-summary-card strong { margin: 4px 0; font-family: Poppins, sans-serif; font-size: 56px; font-weight: 700; color: #284a63; }
        .ac-summary-card small { color: #9e9e9e; font-family: Poppins, sans-serif; font-size: 11px; font-weight: 400; }
        .ac-summary-card small b { font-weight: 600; }
        .ac-panel { padding: 16px 32px 24px; border-radius: 12px; background: #fff; box-shadow: 0 4px 20px rgba(30, 41, 59, .06); }
        .ac-section-title { margin: 0 0 16px; color: #284a63; font-family: Poppins, sans-serif; font-size: 25px; font-weight: 600; }
        .ac-card { margin-bottom: 24px; padding: 0; border: 0; border-radius: 0; box-shadow: none; background: transparent; overflow: visible; }
        .ac-card > .ac-toolbar, .ac-card > .ac-filter-summary { padding: 0; }
        .ac-card > .ac-status-tabs { margin: 8px 0 0; padding: 0; border-bottom: 0; }
        .ac-card .dataTables_wrapper > .d-flex { padding: 0; }
        .ac-table thead th { border-left: 0; border-right: 0; }
        .ac-table tbody td { border-left: 0; border-right: 0; }
        .ac-table { border: 0; }
        .ac-date-badge { border-radius: 4px; background: #c7d2db; color: #284a63; }
        .ac-card .dataTables_info, .ac-card .dataTables_length { color: #212121; font-family: Inter, sans-serif; font-size: 14px; }
        .ac-card .dataTables_length select { height: 36px; margin: 0 8px; border: 1px solid #284a63; border-radius: 6px; font-size: 14px; }
        .ac-card .page-link { min-width: 28px; padding: 4px 8px; font-family: Inter, sans-serif; font-size: 14px; }
        .ac-card .page-item.active .page-link { background: #284a63; }
        .ac-table .mkt-ico { width: 28px; height: 28px; font-size: 12px; }
        .ac-table thead th, .ac-table tbody td { white-space: nowrap !important; overflow-wrap: normal; }
        .ac-table thead th { text-align: left !important; vertical-align: middle; }
        .ac-table td[colspan] { white-space: normal !important; }
        .ac-toolbar .ac-keyword-wrap { margin-left: -8px; }
        .ac-search-by-wrap .select2-container--bootstrap4 .select2-selection { border-top-right-radius: 0 !important; border-bottom-right-radius: 0 !important; }
        .ac-toolbar .ac-keyword { border-left: 0; border-top-left-radius: 0; border-bottom-left-radius: 0; }
        @media (max-width: 599.98px) {
            .ac-toolbar .ac-keyword-wrap { margin-left: 0; }
            .ac-search-by-wrap .select2-container--bootstrap4 .select2-selection { border-radius: .25rem !important; }
            .ac-toolbar .ac-keyword { border-left: 1px solid #ced4da; border-radius: .25rem; }
        }
        .ac-toolbar .ac-keyword { height: 38px; }
        .ac-search-by-wrap .select2-container--bootstrap4 .select2-selection--multiple { height: 38px; min-height: 38px; overflow: hidden; }
        .ac-search-by-wrap .select2-selection--multiple .select2-selection__rendered { display: flex !important; flex-wrap: nowrap; align-items: center; height: 36px; overflow: hidden; white-space: nowrap; }
        .ac-search-by-wrap .select2-selection--multiple .select2-selection__choice { flex: 0 0 auto; margin-top: 0 !important; }
        .ac-search-by-wrap .select2-selection--multiple .select2-search__field { height: 34px !important; margin-top: 0 !important; }
        .ac-input-icon { top: 19px; }
        .ac-date-toggle { width: 38px; height: 38px; }
        .ac-date-panel { top: 42px; }
        .ac-search-by-wrap .select2-selection--multiple .select2-search--inline:first-child,
        .ac-search-by-wrap .select2-selection--multiple .select2-search--inline:first-child .select2-search__field { width: 100% !important; }
        @media (max-width: 767.98px) {
            .ac-frame { padding: 12px; }
            .ac-heading h1 { font-size: 24px; }
            .ac-summary { gap: 10px; }
            .ac-summary-card { min-height: 0; }
            .ac-summary-card span { font-size: 15px; }
            .ac-summary-card strong { font-size: 32px; }
            .ac-panel { padding: 12px; }
            .ac-section-title { font-size: 20px; }
        }
        /* Font sesuai Figma (catatan QA Approval Center). */
        .ac-heading h1 { font-family: Poppins, sans-serif !important; font-size: 32px; font-weight: 700; }
        .ac-summary-card span { font-family: 'Instrument Sans', sans-serif !important; font-size: 20px; font-weight: 700 !important; }
        .ac-summary-card strong { font-family: Poppins, sans-serif !important; font-size: 64px; font-weight: 700; line-height: 1.1; }
        .ac-summary-card small { font-family: 'Instrument Sans', sans-serif !important; font-size: 12px; font-weight: 500; }
        .ac-summary-card small b { font-weight: 700; }
        .ac-section-title { font-family: Poppins, sans-serif !important; font-size: 24px; font-weight: 700; }
        .ac-actions .mkt-btn { font-family: 'SF Pro Rounded', ui-rounded, 'MKT Rounded', sans-serif !important; font-size: 18px; font-weight: 700 !important; }
        .ac-toolbar .ac-keyword, .ac-toolbar .ac-keyword::placeholder,
        .ac-search-by-wrap .select2-container--bootstrap4 .select2-selection,
        .ac-search-by-wrap .select2-search__field, .ac-search-by-wrap .select2-search__field::placeholder,
        .ac-search-by-wrap .select2-selection__placeholder { font-family: Inter, sans-serif !important; font-size: 14px !important; font-weight: 400; }
        /* Ringkasan filter disamakan dengan listing Costing (.filter-summary / .filter-chip / .clear-filters). */
        .ac-filter-summary { gap: 8px; min-height: 30px; margin-top: 10px; color: #64748b; font-family: Inter, sans-serif; font-size: 14px; font-weight: 500; }
        .ac-filter-summary .ac-chip { gap: 0; padding: 4px 10px; border-radius: 6px; background: #e0f2fe; color: #0369a1; font-family: Inter, sans-serif; font-size: 11px; font-weight: 500; }
        .ac-filter-summary .ac-chip-remove { padding: 0 0 0 6px; font-size: 14px; cursor: pointer; }
        .ac-filter-summary .ac-chip-remove:hover { color: #b91c1c; }
        .ac-filter-summary .ac-clear { padding: 0; color: #1e293b; font-family: Inter, sans-serif; font-size: 12px; font-weight: 500; text-decoration: none; cursor: pointer; }
        .ac-filter-summary .ac-clear:hover { text-decoration: underline; }
        .ac-filter-summary .ac-clear:disabled { color: #cbd5e1; cursor: default; text-decoration: none; }
        .ac-status-tab { font-family: Inter, sans-serif; font-size: 14px; font-weight: 500; }
        .ac-status-selected { font-family: Inter, sans-serif; font-size: 14px; font-weight: 700; }
        .ac-count { font-family: Inter, sans-serif; font-size: 10px; font-weight: 500; }
        .ac-table thead th { font-family: Inter, sans-serif !important; font-size: 12px !important; font-weight: 500 !important; }
        .ac-table tbody td { font-family: Inter, sans-serif !important; font-size: 16px !important; font-weight: 400 !important; }
        .ac-table thead th:first-child, .ac-table tbody td:first-child { font-size: 15px !important; font-weight: 400 !important; }
        .ac-table .ac-date-badge, .ac-table .ac-badge { font-family: Inter, sans-serif; font-size: 12px; font-weight: 500; }
        .ac-card .dataTables_info, .ac-card .dataTables_length, .ac-card .dataTables_length label,
        .ac-card .dataTables_length select, .ac-card .page-link { font-family: Poppins, sans-serif !important; font-size: 16px !important; font-weight: 400 !important; }
        @media (max-width: 991.98px) {
            .ac-summary-card strong { font-size: 40px; }
            .ac-table tbody td { font-size: 14px !important; }
        }
        @media (max-width: 767.98px) {
            .ac-heading h1 { font-size: 24px; }
            .ac-summary-card span { font-size: 15px; }
            .ac-summary-card strong { font-size: 32px; }
            .ac-section-title { font-size: 20px; }
            .ac-actions .mkt-btn { font-size: 15px; }
            .ac-card .dataTables_info, .ac-card .dataTables_length, .ac-card .dataTables_length label,
            .ac-card .dataTables_length select, .ac-card .page-link { font-size: 14px !important; }
        }
    </style>
@endsection

@section('content')
<div class="ac-page font-inter">
    <div class="ac-breadcrumb">Marketing &nbsp;›&nbsp; <strong>Approval</strong></div>
    <div class="ac-frame">
    <div class="ac-heading">
        <h1>Approval Center <i class="far fa-question-circle"></i></h1>
        <p><i class="fas fa-exclamation-circle"></i>Semua persetujuan dan permintaan unlock dalam satu halaman.</p>
    </div>

    <div class="ac-summary">
        <a href="#ac-doc-costing" class="ac-summary-card is-costing"><span>Approval Costing</span><strong data-summary="costing">{{ $summary['costing'] }}</strong><small><b>Perlu ditinjau</b> oleh Manager</small></a>
        <a href="#ac-doc-bom" class="ac-summary-card is-bom"><span>Approval BOM</span><strong data-summary="bom">{{ $summary['bom'] }}</strong><small><b>Perlu ditinjau</b> oleh Manager</small></a>
        <a href="#ac-unlock-costing" class="ac-summary-card is-unlock-costing"><span>Unlock Costing</span><strong data-summary="unlock-costing">{{ $summary['unlock_costing'] }}</strong><small><b>Perlu ditinjau</b> oleh Manager</small></a>
        <a href="#ac-unlock-bom" class="ac-summary-card is-unlock-bom"><span>Unlock BOM</span><strong data-summary="unlock-bom">{{ $summary['unlock_bom'] }}</strong><small><b>Perlu ditinjau</b> oleh Manager</small></a>
        <a href="#ac-unlock-so" class="ac-summary-card is-unlock-so"><span>Unlock SO</span><strong data-summary="unlock-so">{{ $summary['unlock_so'] }}</strong><small><b>Perlu ditinjau</b> oleh Manager</small></a>
    </div>

    @foreach ($docSections as $section)
        <section id="ac-doc-{{ $section['key'] }}" class="ac-panel" hidden>
            <h2 class="ac-section-title">{{ $section['title'] }}</h2>
            <div class="ac-card" data-ac-kind="doc" data-ac-type="{{ $section['key'] }}" data-ac-number="{{ $section['number'] }}">
                <div class="ac-toolbar">
                    @include('marketing.approval_center_filters', ['numberLabel' => $section['number'], 'searchColumns' => $searchColumns])
                    <div class="ac-actions">
                        <button type="button" class="mkt-btn mkt-btn--green" data-ac-action="approve" disabled>Approve</button>
                        <button type="button" class="mkt-btn mkt-btn--gray mkt-btn--return" data-ac-action="return" disabled>Return to Staff</button>
                        <button type="button" class="mkt-btn mkt-btn--red mkt-btn--cancel" data-ac-action="cancel" disabled>Cancel</button>
                    </div>
                </div>
                <div class="ac-filter-summary"></div>
                <div class="ac-status-tabs">
                    <span class="ac-status-selected">Selected <span class="ac-count" data-selected>0</span></span>
                    <button type="button" class="ac-status-tab" data-status="return">Return to Staff <span class="ac-count" data-count="return">0</span></button>
                    <button type="button" class="ac-status-tab active" data-status="awaiting">Awaiting Approval <span class="ac-count" data-count="awaiting">0</span></button>
                    <button type="button" class="ac-status-tab" data-status="cancel">Cancel <span class="ac-count" data-count="cancel">0</span></button>
                    <button type="button" class="ac-status-tab" data-status="approved">Approved <span class="ac-count" data-count="approved">0</span></button>
                    <button type="button" class="ac-status-tab" data-status="unlocked">Unlocked <span class="ac-count" data-count="unlocked">0</span></button>
                </div>
                <div class="ac-table-wrap">
                    <table class="table table-bordered ac-table w-100">
                        <thead>
                            <tr>
                                <th><input type="checkbox" class="ac-check-all" aria-label="Pilih semua"></th>
                                <th></th>
                                <th class="text-center">No</th>
                                <th class="text-center">Due Date</th>
                                <th class="text-center">{{ $section['number'] }}</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">No Worksheet</th>
                                <th class="text-center">Buyer</th>
                                <th class="text-center">Brand</th>
                                <th class="text-center">Style</th>
                                <th class="text-center">Market</th>
                                <th class="text-center">Qty</th>
                                <th class="text-center">Confirm Price</th>
                                <th class="text-center">Product Group</th>
                                <th class="text-center">Product Items</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </section>
    @endforeach

        @foreach ($unlockSections as $section)
            <section id="ac-unlock-{{ $section['key'] }}" class="ac-panel" hidden>
                <h2 class="ac-section-title">{{ $section['title'] }} <i class="fas fa-lock text-muted" style="font-size: 13px;"></i></h2>
                <div class="ac-card" data-ac-kind="unlock" data-ac-type="{{ $section['key'] }}" data-ac-enabled="{{ $section['enabled'] ? '1' : '0' }}">
                    <div class="ac-toolbar">
                        @include('marketing.approval_center_filters', ['numberLabel' => $section['number'], 'searchColumns' => $searchColumns])
                        <div class="ac-actions">
                            <button type="button" class="mkt-btn mkt-btn--blue" data-ac-action="unlock" disabled>Unlock</button>
                        </div>
                    </div>
                    <div class="ac-filter-summary"></div>
                    <div class="ac-status-tabs">
                        <span class="ac-status-selected">Selected <span class="ac-count" data-selected>0</span></span>
                        <span class="ac-status-tab active" data-status="request">Request Unlock <span class="ac-count" data-count="request">0</span></span>
                    </div>
                    <div class="ac-table-wrap">
                        <table class="table table-bordered ac-table w-100">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" class="ac-check-all" aria-label="Pilih semua" {{ $section['enabled'] ? '' : 'disabled' }}></th>
                                    <th></th>
                                    <th>No</th>
                                    <th>Due Date</th>
                                    <th>{{ $section['number'] }}</th>
                                    <th>Status</th>
                                    <th>No Worksheet</th>
                                    <th>Buyer</th>
                                    <th>Brand</th>
                                    <th>Style</th>
                                    <th>Market</th>
                                    <th>Qty</th>
                                    <th>Confirm Price</th>
                                    <th>Product Group</th>
                                    <th>Product Items</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @unless ($section['enabled'])
                                    <tr><td colspan="16" class="ac-empty"><i class="fas fa-hourglass-half"></i> Unlock BOM belum aktif.</td></tr>
                                @endunless
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        @endforeach
    </div>
</div>
@endsection

@section('custom-script')
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        const AC_ROUTES = {
            documents: "{{ route('marketing-approval-documents', ':type') }}",
            unlockRequests: "{{ route('marketing-approval-unlock-requests', ':type') }}",
            decide: "{{ route('marketing-approval-decide', ':type') }}",
            unlock: "{{ route('marketing-approval-unlock') }}",
            costingDetail: "{{ route('detail-costing', ':id') }}",
            soDetail: "{{ route('so-detail-page', ':id') }}",
            timeline: "{{ route('costing-timeline', ':id') }}"
        };
        const AC_CSRF = "{{ csrf_token() }}";
        const AC_STATUS_LABEL = {
            awaiting: 'Awaiting Approval', return: 'Return to Staff', cancel: 'Cancel',
            approved: 'Approved', unlocked: 'Unlocked', request: 'Request Unlock'
        };
        const AC_ACTION_LABEL = { approve: 'Approve', return: 'Return to Staff', cancel: 'Cancel' };
        const AC_SUMMARY_KEY = { 'doc-costing': 'costing', 'doc-bom': 'bom', 'unlock-costing': 'unlock-costing', 'unlock-bom': 'unlock-bom', 'unlock-so': 'unlock-so' };

        const escapeHtml = value => $('<div>').text(value ?? '').html();
        const acFormatNumber = value => value === null || value === undefined || value === '' ? '-' : Number(value).toLocaleString('id-ID', { maximumFractionDigits: 4 });
        const acFormatDate = value => {
            if (!value) return '-';
            const date = new Date(String(value).replace(' ', 'T'));
            return isNaN(date) ? escapeHtml(value) : date.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
        };
        const statusBadge = (status, title) => `<span class="ac-badge is-${status}" title="${escapeHtml(title || '')}">${AC_STATUS_LABEL[status] || status}</span>`;

        $(function () {
            $('.ac-card').each(function () { initApprovalCard($(this)); });

            // Card ringkasan = tab: hanya tabel milik card yang dipilih yang tampil.
            // Semua tabel tetap dimuat sejak awal supaya angka di setiap card terisi.
            $('.ac-summary').on('click', '.ac-summary-card', function (event) {
                event.preventDefault();
                showApprovalPanel($(this).attr('href'));
            });
            const initialPanel = $(`.ac-summary-card[href="${window.location.hash}"]`).length ? window.location.hash : $('.ac-summary-card').first().attr('href');
            showApprovalPanel(initialPanel);
            $(document).on('select2:open', () => document.querySelector('.select2-container--open .select2-search__field')?.focus());
            $(document).on('click', function (event) {
                if (!$(event.target).closest('.ac-date-wrap').length) $('.ac-date-panel').removeClass('show');
            });
        });

        function showApprovalPanel(target) {
            $('.ac-summary-card').removeClass('is-active').filter(`[href="${target}"]`).addClass('is-active');
            $('.ac-panel').attr('hidden', true);
            const $panel = $(target).removeAttr('hidden');
            $('.ac-date-panel').removeClass('show');
            if (window.history.replaceState) window.history.replaceState(null, '', target);
            // Lebar kolom dihitung ulang karena tabel dibuat saat panel masih tersembunyi.
            $panel.find('table').each(function () {
                if ($.fn.DataTable.isDataTable(this)) $(this).DataTable().columns.adjust();
            });
        }

        function initApprovalCard($card) {
            const kind = $card.data('ac-kind');
            const type = $card.data('ac-type');
            const state = { status: kind === 'doc' ? 'awaiting' : '' };
            $card.find('.ac-search-by').select2({ theme: 'bootstrap4', width: '100%', placeholder: 'Search By', closeOnSelect: false });
            if (kind === 'unlock' && String($card.data('ac-enabled')) !== '1') return;

            const columns = kind === 'doc' ? docColumns() : unlockColumns(type);
            const table = $card.find('table').DataTable({
                processing: true,
                serverSide: true,
                ordering: true,
                order: [],
                searching: false,
                pageLength: 4,
                lengthMenu: [[4, 10, 25, 50], [4, 10, 25, 50]],
                dom: 'rt<"d-flex justify-content-between align-items-center flex-wrap"<"d-flex align-items-center"li>p>',
                language: {
                    lengthMenu: 'Item Per Page _MENU_',
                    info: 'Of _TOTAL_',
                    infoEmpty: 'Of 0',
                    zeroRecords: 'Tidak ada data.',
                    emptyTable: 'Tidak ada data.',
                    processing: '<i class="fas fa-spinner fa-spin"></i>',
                    paginate: { previous: '<i class="fas fa-chevron-left"></i>', next: '<i class="fas fa-chevron-right"></i>' }
                },
                ajax: {
                    url: (kind === 'doc' ? AC_ROUTES.documents : AC_ROUTES.unlockRequests).replace(':type', type),
                    data: function (d) {
                        d.status = state.status;
                        d.tgl_awal = $card.find('.ac-date-from').val();
                        d.tgl_akhir = $card.find('.ac-date-to').val();
                        d.keyword = $card.find('.ac-keyword').val();
                        d.search_by = $card.find('.ac-search-by').val();
                    }
                },
                columns: columns
            });

            const reload = () => table.ajax.reload();
            const updateSelection = function () {
                const count = $card.find('tbody .ac-row-check:checked').length;
                $card.find('[data-selected]').text(count);
                $card.find('[data-ac-action]').prop('disabled', count === 0);
            };

            table.on('xhr.dt', function (event, settings, json) {
                if (!json) return;
                if (kind === 'doc') {
                    const counts = json.status_counts || {};
                    $card.find('[data-count]').each(function () {
                        $(this).text(Number(counts[$(this).data('count')] || 0).toLocaleString('id-ID'));
                    });
                    $(`[data-summary="${AC_SUMMARY_KEY['doc-' + type]}"]`).text(Number(counts.awaiting || 0).toLocaleString('id-ID'));
                } else {
                    $card.find('[data-count="request"]').text(Number(json.recordsTotal || 0).toLocaleString('id-ID'));
                    $(`[data-summary="${AC_SUMMARY_KEY['unlock-' + type]}"]`).text(Number(json.recordsTotal || 0).toLocaleString('id-ID'));
                }
            });
            table.on('draw.dt', function () {
                $card.find('.ac-check-all').prop('checked', false);
                updateSelection();
            });

            // Filter
            let keywordTimer = null;
            $card.on('input', '.ac-keyword', function () {
                clearTimeout(keywordTimer);
                keywordTimer = setTimeout(function () { renderFilterSummary($card); reload(); }, 350);
            });
            $card.on('change', '.ac-search-by', function () {
                renderFilterSummary($card);
                if ($card.find('.ac-keyword').val()) reload();
            });
            $card.on('click', '.ac-date-toggle', function (event) {
                event.stopPropagation();
                $card.find('.ac-date-panel').toggleClass('show');
            });
            $card.on('click', '.ac-date-apply', function () {
                $card.find('.ac-date-panel').removeClass('show');
                renderFilterSummary($card);
                reload();
            });
            $card.on('click', '.ac-clear, .ac-chip-remove', function () {
                const filter = $(this).data('filter');
                if (!filter || filter === 'keyword') $card.find('.ac-keyword').val('');
                if (!filter || filter === 'search-by') $card.find('.ac-search-by').val(null).trigger('change.select2');
                if (!filter || filter === 'date') $card.find('.ac-date-from, .ac-date-to').val('');
                renderFilterSummary($card);
                reload();
            });

            // Tab status (approval): klik tab aktif lagi = tampilkan semua status.
            $card.on('click', 'button.ac-status-tab', function () {
                const status = $(this).data('status');
                state.status = state.status === status ? '' : status;
                $card.find('.ac-status-tab').removeClass('active').filter(`[data-status="${state.status}"]`).addClass('active');
                reload();
            });

            // Seleksi baris
            $card.on('change', '.ac-check-all', function () {
                $card.find('tbody .ac-row-check').prop('checked', this.checked);
                updateSelection();
            });
            $card.on('change', '.ac-row-check', updateSelection);

            // Timeline per baris
            $card.on('click', '.ac-timeline-toggle', function () {
                const $button = $(this);
                const row = table.row($button.closest('tr'));
                if (row.child.isShown()) {
                    row.child.hide();
                    $button.attr('aria-expanded', 'false');
                    return;
                }
                $button.attr('aria-expanded', 'true');
                row.child('<div class="ac-timeline text-muted"><i class="fas fa-spinner fa-spin"></i> Memuat riwayat...</div>').show();
                $.get(AC_ROUTES.timeline.replace(':id', row.data().id_costing))
                    .done(res => row.child(renderTimeline(res.data || [], row.data())).show())
                    .fail(xhr => row.child(`<div class="ac-timeline text-danger">${escapeHtml(xhr.responseJSON?.message || 'Riwayat gagal dimuat.')}</div>`).show());
            });

            // Aksi
            $card.on('click', '[data-ac-action]', function () {
                const action = $(this).data('ac-action');
                const ids = $card.find('tbody .ac-row-check:checked').map((_, el) => el.value).get();
                if (!ids.length) return;
                action === 'unlock' ? submitUnlock(type, ids, reload) : submitDecision(type, action, ids, reload);
            });

            renderFilterSummary($card);
        }

        // Kolom pilih, timeline & nomor urut tidak bisa di-sort; kolom lain di-sort server-side lewat `name`.
        const fixedColumns = () => [
            { data: 'id', className: 'text-center', orderable: false, render: id => `<input type="checkbox" class="ac-row-check" value="${id}">` },
            { data: null, className: 'text-center', orderable: false, render: () => '<button type="button" class="ac-timeline-toggle" aria-expanded="false" title="Lihat timeline"><i class="fas fa-chevron-right"></i></button>' },
            { data: 'DT_RowIndex', className: 'text-center', orderable: false }
        ];
        const textColumn = (data, name) => ({ data: data, name: name, render: value => escapeHtml(value || '-') });

        function docColumns() {
            return fixedColumns().concat([
                { data: 'deldate', name: 'a.deldate', className: 'text-center', render: value => value ? `<span class="ac-date-badge">${acFormatDate(value)}</span>` : '-' },
                textColumn('doc_no', 'doc_no'),
                { data: 'approval_status', name: 'approval_status', render: (status, type, row) => statusBadge(status, row.approval_note ? `Catatan: ${row.approval_note}` : '') },
                textColumn('kpno', 'a.kpno'),
                textColumn('buyer', 'b.Supplier'),
                textColumn('brand', 'a.brand'),
                textColumn('styleno', 'a.styleno'),
                textColumn('market', 'a.market'),
                { data: 'qty', name: 'a.qty', className: 'text-right', render: acFormatNumber },
                { data: 'cfm_price', name: 'a.cfm_price', className: 'text-right', render: acFormatNumber },
                textColumn('product_group', 'p.product_group'),
                textColumn('product_item', 'p.product_item'),
                detailColumn(row => AC_ROUTES.costingDetail.replace(':id', row.id_costing))
            ]);
        }

        // Kolom Action: ikon detail bulat, membuka halaman detail di tab baru.
        const detailColumn = urlOf => ({
            data: null, className: 'text-center', orderable: false,
            render: (value, displayType, row) => `<a href="${urlOf(row)}" target="_blank" class="mkt-ico mkt-ico--detail" title="Detail" data-tip="Detail" aria-label="Detail"><i class="fas fa-info"></i></a>`
        });

        // Kolom sama dengan tabel Approval, hanya nomor dokumen (Costing/BOM/SO) yang berbeda, plus kolom Action (detail).
        function unlockColumns(type) {
            const detailUrl = row => type === 'so'
                ? AC_ROUTES.soDetail.replace(':id', row.ref_id)
                : AC_ROUTES.costingDetail.replace(':id', row.id_costing);
            return fixedColumns().concat([
                { data: 'deldate', name: 'a.deldate', className: 'text-center', render: value => value ? `<span class="ac-date-badge">${acFormatDate(value)}</span>` : '-' },
                textColumn('doc_no', 'doc_no'),
                {
                    data: 'doc_status', name: 'doc_status', render: (status, type, row) =>
                        statusBadge('request', `Alasan: ${row.reason || '-'} · Diajukan ${row.requested_by || '-'} ${acFormatDate(row.requested_at)}`)
                },
                textColumn('kpno', 'a.kpno'),
                textColumn('buyer', 'b.Supplier'),
                textColumn('brand', 'a.brand'),
                textColumn('styleno', 'a.styleno'),
                textColumn('market', 'a.market'),
                { data: 'qty', name: 'a.qty', className: 'text-right', render: acFormatNumber },
                { data: 'cfm_price', name: 'a.cfm_price', className: 'text-right', render: acFormatNumber },
                textColumn('product_group', 'p.product_group'),
                textColumn('product_item', 'p.product_item'),
                detailColumn(detailUrl)
            ]);
        }

        function renderFilterSummary($card) {
            const chips = [];
            const searchBy = $card.find('.ac-search-by').select2('data').map(item => item.text);
            const keyword = $card.find('.ac-keyword').val();
            const from = $card.find('.ac-date-from').val();
            const to = $card.find('.ac-date-to').val();
            if (searchBy.length) chips.push({ filter: 'search-by', text: `Search By: ${searchBy.join(', ')}` });
            if (keyword) chips.push({ filter: 'keyword', text: `Search: "${keyword}"` });
            if (from && to) chips.push({ filter: 'date', text: `Tanggal: ${acFormatDate(from)} - ${acFormatDate(to)}` });
            // Sama dengan listing Costing: "Filter:" + chip (atau None) + Clear all di ujung.
            $card.find('.ac-filter-summary').html(
                '<span>Filter:</span>'
                + (chips.length
                    ? chips.map(chip => `<span class="ac-chip">${escapeHtml(chip.text)}<button type="button" class="ac-chip-remove" data-filter="${chip.filter}" title="Hapus filter">&times;</button></span>`).join('')
                    : '<span>None</span>')
                + `<button type="button" class="ac-clear"${chips.length ? '' : ' disabled'}>Clear all</button>`
            );
        }

        function renderTimeline(history, row) {
            const items = history.slice(-8).map(item => `
                <div class="ac-timeline-item">
                    <time>${acFormatDate(item.created_at)} ${item.created_at ? String(item.created_at).slice(11, 16) : ''}</time>
                    <span>${escapeHtml(item.desc || item.action)}${item.user ? ` <span class="text-muted">— ${escapeHtml(item.user)}</span>` : ''}</span>
                </div>`).join('');
            const reason = row.reason ? `<div class="mb-1"><b>Alasan unlock:</b> ${escapeHtml(row.reason)} <span class="text-muted">— ${escapeHtml(row.requested_by || '-')}</span></div>` : '';
            return `<div class="ac-timeline"><b>${escapeHtml(row.doc_no)}</b>${reason}${items || '<div class="text-muted">Belum ada riwayat.</div>'}</div>`;
        }

        function submitDecision(type, action, ids, reload) {
            const needsNote = action !== 'approve';
            Swal.fire({
                icon: needsNote ? undefined : 'question',
                title: `${AC_ACTION_LABEL[action]} ${ids.length} data?`,
                text: needsNote ? 'Catatan akan terlihat oleh staff.' : 'Data yang di-approve akan terkunci.',
                input: needsNote ? 'textarea' : undefined,
                inputPlaceholder: 'Catatan untuk staff...',
                inputAttributes: { maxlength: 1000 },
                inputValidator: needsNote ? value => (!value || !value.trim()) ? 'Catatan wajib diisi.' : undefined : undefined,
                showCancelButton: true,
                confirmButtonText: AC_ACTION_LABEL[action],
                cancelButtonText: 'Batal'
            }).then(function (result) {
                if (!result.isConfirmed) return;
                postAction(AC_ROUTES.decide.replace(':type', type), { ids: ids, action: action, note: needsNote ? result.value.trim() : '' }, reload);
            });
        }

        function submitUnlock(type, ids, reload) {
            const label = { bom: 'BOM', so: 'SO' }[type] || 'Costing';
            Swal.fire({
                icon: 'question',
                title: `Unlock ${ids.length} ${label}?`,
                text: `${label} bisa diedit hari ini dan besok. Setelah staff menyimpan perubahan, ${label} kembali menunggu approval.`,
                showCancelButton: true,
                confirmButtonText: 'Unlock',
                cancelButtonText: 'Batal'
            }).then(function (result) {
                if (result.isConfirmed) postAction(AC_ROUTES.unlock, { type: type, ids: ids }, reload);
            });
        }

        function postAction(url, payload, reload) {
            Swal.fire({ title: 'Memproses...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            $.ajax({ url: url, method: 'POST', data: Object.assign({ _token: AC_CSRF }, payload) })
                .done(function (res) {
                    Swal.fire('Berhasil', res.message, 'success');
                    reload();
                    // Approve/Unlock mengubah tabel lain (mis. status costing), muat ulang semuanya.
                    $('.ac-card table').each(function () {
                        if ($.fn.DataTable.isDataTable(this)) $(this).DataTable().ajax.reload(null, false);
                    });
                })
                .fail(function (xhr) {
                    const errors = xhr.responseJSON?.errors;
                    Swal.fire('Gagal', errors ? Object.values(errors).flat()[0] : (xhr.responseJSON?.message || 'Terjadi kesalahan.'), 'error');
                });
        }
    </script>
@endsection
