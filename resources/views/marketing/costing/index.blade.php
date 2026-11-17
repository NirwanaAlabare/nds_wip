@extends('layouts.index')

@section('custom-link')
    <link rel="stylesheet" href="{{ asset('dist/css/marketing-buttons.css') }}?v={{ @filemtime(public_path('assets/dist/css/marketing-buttons.css')) }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">

    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

    <style>
        :root {
            --sb-navy: #0f172a;
            --sb-blue: #3085d6;
            --sb-blue-dark: #1e3a8a;
            --sb-border: #e2e8f0;
        }

        /* ============ COSTING LIST ============ */
        .costing-page { padding: 10px 14px 28px; background: #f8fafc; min-height: calc(100vh - 56px); font-family: Inter, sans-serif; }
        /* Satu font (Inter) untuk seluruh listing; input & tombol biasa tidak mewarisi font secara default. Tombol pill tetap font tombol. */
        .costing-page input, .costing-page select, .costing-page textarea, .costing-page button:not(.mkt-btn):not(.mkt-icon-btn), .costing-page .dropdown-menu { font-family: inherit; }
        .costing-crumb { background: #284A63; color: #cbd5e1; padding: 9px 12px; font-size: 12px; letter-spacing: .2px; border-radius: 4px; }
        .costing-crumb b { color: #fff; }

        .card-sb {
            border: 0;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 5px 12px -3px rgba(15, 23, 42, .18), 0 2px 4px rgba(15, 23, 42, .07);
        }

        .card-sb > .card-body {
            background: #fff;
            padding: 20px 24px 28px;
        }

        .costing-title { color: #0f172a; font-size: 24px; font-weight: 800; margin: 0 0 20px; letter-spacing: .2px; }

        /* Toolbar & Search */
        .list-shell { padding: 0; }
        .list-toolbar { display: flex; gap: 9px; align-items: center; position: relative; }
        .toolbar-btn, .search-by { height: 32px; border: 1px solid #e2e8f0; background: #fff; color: #475569; font-size: 12px; border-radius: 6px; transition: 0.2s; }
        .toolbar-btn { padding: 0 12px; font-weight: 600; }
        .search-by { padding: 0 12px; }
        .toolbar-btn:hover, .search-by:hover { background: #f8fafc; color: #0f172a; }
        .toolbar-search { width: 220px; height: 32px; border: 1px solid #e2e8f0; border-radius: 6px; padding: 0 12px; font-size: 12px; }
        .toolbar-search:focus { outline: 0; border-color: #3b82f6; box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1); }

        /* Search Menu Dropdown */
        .search-menu { display: none; position: absolute; z-index: 1035; top: 36px; left: 45px; width: 160px; background: #fff; border-radius: 6px; box-shadow: 0 10px 25px rgba(15, 23, 42, .1); overflow: visible; border: 1px solid #e2e8f0; }
        .search-menu.show { display: block; }
        .search-menu > button { width: 100%; border: 0; background: #fff; text-align: left; padding: 8px 12px; font-size: 12px; color: #475569; border-bottom: 1px solid #f1f5f9; }
        .search-menu > button:hover, .search-menu > button.active { background: #f8fafc; color: #0f172a; font-weight: 600; }
        .search-submenu { display: none; position: absolute; top: 0; left: 100%; width: 400px; max-height: 250px; overflow: auto; padding: 12px; background: #fff; border-radius: 6px; box-shadow: 4px 10px 25px rgba(15, 23, 42, .1); border: 1px solid #e2e8f0; }
        .search-submenu.show { display: block; }
        .search-submenu label { display: block; font-size: 11px; font-weight: 500; margin: 6px 0; color: #475569; }
        .search-submenu .form-control { font-size: 11px; height: 30px; }

        /* Filter & Selected Summary */
        .filter-summary { min-height: 30px; color: #64748b; font-size: 11px; display: flex; align-items: center; gap: 8px; margin-top: 10px; }
        .clear-filters { padding: 0; border: 0; background: transparent; color: #1e293b; font-size: 11px; font-weight: 500; cursor: pointer; }
        .clear-filters:hover { text-decoration: underline; }
        .clear-filters:disabled { color: #cbd5e1; cursor: default; text-decoration: none; }
        .filter-chip { display: inline-flex; align-items: center; padding: 4px 10px; background: #e0f2fe; color: #0369a1; border-radius: 6px; font-weight: 500; font-size: 11px; }
        .filter-chip-remove { border: 0; background: transparent; color: inherit; font-size: 14px; line-height: 1; padding: 0 0 0 6px; cursor: pointer; }
        .filter-chip-remove:hover { color: #b91c1c; }

        .selected-wrapper { font-size: 12px; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 8px; margin-bottom: 10px; margin-top: 5px; }
        .selected-badge { background: #e2e8f0; color: #475569; padding: 2px 10px; border-radius: 12px; font-size: 11px; }

        /* ============ TABLE ============ */
        .costing-table { border-collapse: collapse; margin-top: 5px !important; }
        .costing-table th, .costing-table td { border-top: none; border-left: none; border-right: none; border-bottom: 1px solid #f1f5f9 !important; padding: 12px 10px; vertical-align: middle; }
        .costing-table thead tr { background: #C7DCFFCC; border-bottom: 2px solid #e2e8f0; }
        .costing-table thead th { color: #475569 !important; font-family: Inter, sans-serif; font-size: 12px !important; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase; border-bottom: none !important; }
        .costing-table tbody td { font-family: Inter, sans-serif; font-size: 14px !important; color: #334155; }
        .costing-table tbody tr:hover { background-color: #C7DCFFCC; }
        /* Tabel selalu muat lebar layar: teks boleh turun baris, kolom kurang penting disembunyikan otomatis (fitCostingColumns). */
        .costing-table-wrap, #table-costing_wrapper { width: 100%; }
        #table-costing_wrapper .dataTables_scrollBody { overflow-x: hidden !important; }
        #table-costing_wrapper .dataTables_scrollHeadInner, #table-costing_wrapper .dataTables_scrollHeadInner > table, #table-costing { width: 100% !important; }
        .costing-table td { white-space: normal; overflow-wrap: break-word; }
        .costing-table td.costing-nowrap { white-space: nowrap; }
        /* Tablet & HP (< 1024px): semua kolom tampil, tabel digeser ke kanan di dalam kartu (lihat fitCostingColumns). */
        #table-costing-wrap.is-scroll { overflow-x: auto; -webkit-overflow-scrolling: touch; padding-bottom: 4px; }
        /* scrollY: header & isi tabel terpisah, jadi keduanya melebar bersama dan digeser oleh kartu luar. */
        #table-costing-wrap.is-scroll .dataTables_scroll { width: max-content; min-width: 100%; }
        #table-costing-wrap.is-scroll .dataTables_scrollBody { overflow-x: visible !important; }
        #table-costing-wrap.is-scroll #table-costing { width: max-content !important; min-width: 100%; }
        #table-costing-wrap.is-scroll .costing-table th, #table-costing-wrap.is-scroll .costing-table td { white-space: nowrap; }
        #table-costing-wrap.is-scroll::-webkit-scrollbar { height: 8px; }
        #table-costing-wrap.is-scroll::-webkit-scrollbar-track { background: #e2e8f0; border-radius: 4px; }
        #table-costing-wrap.is-scroll::-webkit-scrollbar-thumb { background: #284a63; border-radius: 4px; }
        .costing-table thead th { white-space: normal; }
        .search-by { white-space: nowrap; }
        /* Search By + Search dalam satu kotak (satu fitur yang saling berkaitan). */
        .search-combo { position: relative; display: flex; align-items: stretch; width: 380px; max-width: 100%; height: 32px; border: 1px solid #e2e8f0; border-radius: 6px; background: #fff; }
        .search-combo .search-menu { top: 36px; left: -1px; }
        .search-combo .search-by { flex: 0 0 auto; height: 100%; border: 0; border-right: 1px solid #e2e8f0; border-radius: 6px 0 0 6px; }
        .search-combo .input-group { flex: 1 1 auto; flex-wrap: nowrap; width: auto !important; min-width: 0; }
        .search-combo .input-group-text { height: 30px !important; border: 0 !important; background: transparent !important; }
        .search-combo .toolbar-search { flex: 1 1 auto; width: auto; min-width: 0; height: 30px; border: 0 !important; background: transparent; }
        @media (max-width: 767.98px) { .search-combo { flex: 1 1 260px; width: auto; } }
        @media (max-width: 767.98px) {
            .list-shell > .d-flex:first-child { flex-wrap: wrap; gap: 8px; }
            .list-toolbar { flex-wrap: wrap; }
            .list-toolbar .input-group { flex: 1 1 160px; width: auto !important; }
            .costing-table .costing-actions { min-width: 0; flex-wrap: wrap; justify-content: flex-start; gap: 4px; }
            /* Submenu Search By turun ke bawah menu (bukan ke kanan) supaya tidak keluar layar. */
            .search-submenu { top: 100%; left: 0; width: min(400px, calc(100vw - 56px)); margin-top: 4px; }
            /* Tab status satu baris, digulir ke samping bila tidak muat. */
            .list-shell .costing-status-tabs { flex-wrap: nowrap; overflow-x: auto; white-space: nowrap; }
            .list-shell .costing-status-tabs > * { flex: 0 0 auto; }
            #btn-batch-print { margin-left: auto; }
            /* Baris pager: item per page + info + halaman boleh turun baris. */
            #table-costing_wrapper > .d-flex { flex-wrap: wrap; gap: 8px; }
        }
        @media (max-width: 575.98px) {
            .costing-page { padding-left: 4px !important; padding-right: 4px !important; }
            .costing-page .card-body { padding: 14px 8px !important; }
            .costing-table th, .costing-table td { padding: 8px 4px; }
            .costing-title { font-size: 20px; margin-bottom: 14px; }
            #table-costing_wrapper > .d-flex > .d-flex { flex-wrap: wrap; gap: 4px 12px !important; }
            #table-costing_wrapper .dataTables_paginate { width: 100%; }
            #table-costing_wrapper .dataTables_paginate .pagination { justify-content: center; flex-wrap: wrap; }
        }
        @media (max-width: 400px) {
            .costing-page .costing-table th, .costing-page .costing-table td { padding: 6px 2px; }
        }
        /* Neon vertikal status approval, menempel di kiri tombol timeline. */
        .costing-timeline-cell { position: relative; }
        .costing-status-neon {
            position: absolute;
            top: 6px;
            bottom: 6px;
            left: 0;
            width: 3px;
            border-radius: 3px;
        }
        .costing-status-neon.is-awaiting,
        .costing-status-tab[data-status="awaiting"] .costing-status-swatch { background: #f59e0b; }
        .costing-status-neon.is-return,
        .costing-status-tab[data-status="return"] .costing-status-swatch { background: #a855f7; }
        .costing-status-neon.is-cancel,
        .costing-status-tab[data-status="cancel"] .costing-status-swatch { background: #ef4444; }
        .costing-status-neon.is-approved,
        .costing-status-tab[data-status="approved"] .costing-status-swatch { background: #22c55e; }
        .costing-status-neon.is-unlocked,
        .costing-status-tab[data-status="unlocked"] .costing-status-swatch { background: #3b82f6; }

        /* Status tab = legend + filter (klik lagi untuk mematikan filter). */
        .costing-status-tabs { display: flex; flex-wrap: wrap; align-items: center; gap: 4px 18px; margin: 4px 0 4px; border-bottom: 1px solid #e2e8f0; font-size: 11px; }
        .costing-status-selected { padding: 6px 0; color: #1e293b; font-weight: 700; }
        .costing-status-tab { display: inline-flex; align-items: center; gap: 6px; padding: 6px 0; border: 0; border-bottom: 2px solid transparent; background: none; color: #334155; font-weight: 500; cursor: pointer; }
        .costing-status-tab:hover { color: #0f172a; }
        .costing-status-tab.active { border-bottom-color: currentColor; font-weight: 700; }
        .costing-status-tab[data-status="return"] { color: #9333ea; }
        .costing-status-tab[data-status="cancel"] { color: #dc2626; }
        .costing-status-tab[data-status="approved"] { color: #16a34a; }
        .costing-status-tab[data-status="unlocked"] { color: #2563eb; }
        .costing-status-swatch { display: inline-block; width: 3px; height: 12px; border-radius: 3px; }
        .costing-status-count { min-width: 18px; padding: 0 6px; border-radius: 9px; background: #e2e8f0; color: #475569; font-size: 10px; font-weight: 600; text-align: center; }
        .costing-status-note { margin: 0 0 10px; color: #94a3b8; font-size: 10px; }
        .costing-table input[type="checkbox"] { width: 15px; height: 15px; cursor: pointer; accent-color: #334155; border-radius: 3px; }

        .date-badge { background-color: #e2e8f0; color: #475569; padding: 4px 12px; border-radius: 12px; font-weight: 600; font-size: 12px; display: inline-block; }

        /* ============ ACTION BUTTONS & BOTTOM BAR ============ */
        /* Bulat 28x28: penuh = sudah dibuat, cincin = langkah berikutnya (Create ...), abu = belum bisa. */
        .action-round { --ar-c: #0ea5e9; --ar-ring: #7dd3fc; position: relative; width: 28px; height: 28px; padding: 0; border-radius: 50%; display: inline-flex; justify-content: center; align-items: center; background: var(--ar-c); color: #fff !important; font-size: 12px; text-decoration: none !important; border: 0; box-shadow: 0 3px 6px rgba(15, 23, 42, .18); transition: transform .15s ease, box-shadow .15s ease; cursor: pointer; }
        .action-round i { font-size: 12px !important; }
        /* BOM perlu diperbarui (SO / Costing berubah setelah BOM disimpan). */
        /* Titik merah: dokumen sebelumnya (Costing / SO) berubah, bagian ini perlu disesuaikan. */
        .action-round.has-stale::after { content: ''; position: absolute; top: -3px; right: -3px; width: 11px; height: 11px; border-radius: 50%; background: #ef4444; border: 2px solid #fff; box-shadow: 0 0 0 2px rgba(239, 68, 68, .25); }
        .action-round svg { width: 14px; height: 14px; }
        .action-round.is-costing { --ar-c: #0a8bd8; --ar-ring: #7dd3fc; background: linear-gradient(180deg, #1ec8f3, #0a8bd8); }
        .action-round.is-so { --ar-c: #ff9800; --ar-ring: #fdc77a; background: linear-gradient(180deg, #ffc61a, #ff9800); }
        .action-round.is-bom { --ar-c: #6b00d6; --ar-ring: #c4a8fb; background: linear-gradient(180deg, #a024ec, #6b00d6); }
        .action-round.is-ws { --ar-c: #1f2b9e; --ar-ring: #a5a8f0; background: linear-gradient(180deg, #4357e6, #1f2b9e); }
        .action-round.is-next { background: #fff; box-shadow: inset 0 0 0 2px var(--ar-c), 0 3px 6px rgba(15, 23, 42, .12); color: var(--ar-c) !important; }
        .action-round.is-off { background: #eef0f5; box-shadow: none; color: #d3d7e0 !important; cursor: not-allowed; }
        .action-round:hover:not(:disabled):not(.is-off) { transform: scale(1.08); }
        .action-round[data-tip]:hover::after { content: attr(data-tip); position: absolute; top: calc(100% + 4px); left: 50%; z-index: 30; padding: 3px 7px; border-radius: 3px; background: #5b6b78; color: #fff; font: 500 11px/1.3 Inter, sans-serif; white-space: nowrap; pointer-events: none; }
        .action-round.dropdown-toggle::after { display: none !important; } /* Sembunyikan panah dropdown bawaan */
        .timeline-toggle { width: 22px; height: 22px; padding: 0; border: 1px solid #cbd5e1; border-radius: 50%; background: #fff; color: #64748b; display: inline-flex; align-items: center; justify-content: center; font-size: 9px; cursor: pointer; transition: .15s ease; }
        .timeline-toggle:hover, .timeline-toggle[aria-expanded="true"] { background: #e0f2fe; border-color: #7dd3fc; color: #0284c7; }
        .costing-actions { display: flex; align-items: center; justify-content: center; gap: 8px; min-width: 140px; }
        .costing-actions-menu { position: fixed !important; z-index: 2000 !important; min-width: 170px; max-height: calc(100vh - 16px); overflow-y: auto; padding: 6px 0; border: 0; border-radius: 10px; background: #fff; box-shadow: 0 6px 18px rgba(15, 23, 42, .18); }
        .costing-actions-menu.show { display: block; }
        .costing-actions-menu .mkt-fab { --fab-from: #1ec8f3; --fab-to: #0a8bd8; --fab-border: #1ec8f3; --fab-hover-from: #7fe3ff; --fab-hover-to: #2db8f6; display: flex; align-items: center; gap: 10px; width: 100%; padding: 5px 14px; color: #334155; font-family: 'Poppins', sans-serif; font-size: 12px; font-weight: 500; line-height: 1.2; text-decoration: none !important; cursor: pointer; }
        .costing-actions-menu .mkt-fab:hover,
        .costing-actions-menu .mkt-fab:focus-visible { background: #f4f6fa; color: #0f172a; outline: 0; }
        .costing-actions-menu .mkt-fab-icon { display: inline-flex; flex: 0 0 28px; align-items: center; justify-content: center; width: 28px; height: 28px; border: 1px solid var(--fab-border); border-radius: 38px; background: linear-gradient(180deg, var(--fab-from), var(--fab-to)); color: #fff; transition: background .15s ease; }
        .costing-actions-menu .mkt-fab-icon i { font-size: 13px; }
        .costing-actions-menu .mkt-fab:hover .mkt-fab-icon,
        .costing-actions-menu .mkt-fab:focus-visible .mkt-fab-icon,
        .costing-actions-menu .mkt-fab:active .mkt-fab-icon { background: linear-gradient(180deg, var(--fab-hover-from), var(--fab-hover-to)); }
        .costing-actions-menu .mkt-fab.is-edit { --fab-from: #1ec8f3; --fab-to: #0a8bd8; --fab-border: #1ec8f3; --fab-hover-from: #7fe3ff; --fab-hover-to: #2db8f6; }
        .costing-actions-menu .mkt-fab.is-delete { --fab-from: #ff3b4d; --fab-to: #e0001b; --fab-border: #ff3b4d; --fab-hover-from: #ff8a96; --fab-hover-to: #f2405a; }
        .costing-actions-menu .mkt-fab.is-copy { --fab-from: #ffc700; --fab-to: #ff9900; --fab-border: #ffc701; --fab-hover-from: #ffe45c; --fab-hover-to: #ffc61f; }
        .costing-actions-menu .mkt-fab.is-pdf { --fab-from: #ff3b4d; --fab-to: #e0001b; --fab-border: #ff3b4d; --fab-hover-from: #ff8a96; --fab-hover-to: #f2405a; }
        .costing-actions-menu .mkt-fab.is-excel { --fab-from: #34c55e; --fab-to: #198038; --fab-border: #34c55e; --fab-hover-from: #7ee29b; --fab-hover-to: #2fa556; }
        .costing-actions-menu .mkt-fab.is-download { --fab-from: #a024ec; --fab-to: #6b00d6; --fab-border: #a024ec; --fab-hover-from: #bb5cf4; --fab-hover-to: #8a2be8; }
        .costing-actions-menu .mkt-fab.is-detail { --fab-from: #4357e6; --fab-to: #1f2b9e; --fab-border: #4357e6; --fab-hover-from: #8d9cff; --fab-hover-to: #4a5ef0; }
        /* Menu action listing: ikon satu warna (glyph gelap tanpa lingkaran warna), font seragam. */
        .costing-actions-menu .mkt-fab { font-weight: 500; }
        .costing-actions-menu .mkt-fab .mkt-fab-icon,
        .costing-actions-menu .mkt-fab:hover .mkt-fab-icon,
        .costing-actions-menu .mkt-fab:focus-visible .mkt-fab-icon,
        .costing-actions-menu .mkt-fab:active .mkt-fab-icon { flex-basis: 18px; width: 18px; height: 18px; border: 0; background: none; color: #1e293b; }
        .costing-actions-menu .mkt-fab .mkt-fab-icon i { font-size: 15px; }
        .costing-actions-menu .mkt-fab-label { display: flex; flex-direction: column; min-width: 0; }
        .costing-actions-menu .mkt-fab-label small { margin-top: 2px; color: #94a3b8; font-size: 10px; font-weight: 400; white-space: normal; }
        .costing-actions-menu .dropdown-divider { margin: 4px 10px; }
        .costing-actions-menu .mkt-fab:disabled { background: transparent; color: #a0a7b4; cursor: not-allowed; }
        .costing-actions-menu .mkt-fab:disabled .mkt-fab-icon { background: none; border: 0; color: #c4c9d4; }

        .costing-timeline { display: block; padding: 16px 24px; border: 0; border-radius: 0; background: #f6f8fb; }
        .timeline-vertical { display: flex; flex-direction: column; gap: 18px; text-align: left; }
        .timeline-v-item { position: relative; display: flex; align-items: flex-start; gap: 16px; min-height: 24px; }
        .timeline-v-item:not(:last-child)::after { position: absolute; top: 24px; bottom: -18px; left: 11px; z-index: 0; width: 2px; background: #d9e0e9; content: ""; }
        .timeline-v-icon { z-index: 1; display: flex; flex: 0 0 24px; width: 24px; height: 24px; align-items: center; justify-content: center; border: 2px solid #e4e9f0; border-radius: 50%; background: #dce3ec; color: #64748b; font-size: 10px; }
        .timeline-v-icon.is-created { border-color: #e4e0ff; background: #dcd6ff; color: #334155; }
        .timeline-v-icon.is-approved { border-color: #dbeafe; background: #dbeafe; color: #2563eb; }
        .timeline-v-content { min-width: 0; padding-top: 2px; color: #334155; font-size: 14px !important; line-height: 1.55; }
        .timeline-v-content b { color: #111827; font-weight: 600; }
        .timeline-v-date { color: #64748b; white-space: nowrap; }
        .timeline-v-costno { color: #526b84; }
        .timeline-v-badge { display: inline-flex; align-items: center; margin: 0 3px; padding: 2px 8px; border-radius: 4px; font-size: 12px; font-weight: 500; line-height: 1.3; white-space: nowrap; }
        .timeline-v-badge i { margin-right: 5px; font-size: 6px; }
        .badge-awaiting { background: #e9edf3; color: #596579; }
        .badge-awaiting i { color: #7b8798; }
        .badge-approved { background: #e1f7eb; color: #16804a; }
        .badge-approved i { color: #2ca66a; }
        .timeline-v-note { display: flex; align-items: flex-start; gap: 8px; margin-top: 6px; padding-left: 16px; color: #536174; font-size: 13px; font-style: italic; }
        .timeline-v-note i { padding-top: 3px; color: #8793a5; font-size: 12px; }

        /* DataTables DOM Overrides */
        #table-costing_wrapper .dataTables_filter { display: none; }
        #table-costing_wrapper .dataTables_length select {
            box-sizing: border-box;
            min-width: 58px;
            height: 28px;
            padding: 2px 22px 2px 8px;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            font-size: 11px;
            line-height: 20px;
            background-position: right 6px center;
        }
        #table-costing_wrapper .dataTables_length, #table-costing_wrapper .dataTables_info { font-size: 11px; color: #475569; margin: 0; }
        #table-costing_wrapper .dataTables_length label { display: flex; align-items: center; gap: 6px; margin: 0; line-height: 28px; }
        #table-costing_wrapper .dataTables_info { padding-top: 0; line-height: 28px; }
        #table-costing_wrapper .dataTables_length select { margin: 0; vertical-align: middle; }
        #table-costing_wrapper .dataTables_paginate .paginate_button { padding: 0; border: 0 !important; font-size: 10px; color: #475569 !important; border-radius: 4px; margin: 0 1px; }
        #table-costing_wrapper .dataTables_paginate .page-link {
            min-width: 26px;
            height: 26px;
            padding: 4px 6px;
            border: 0;
            border-radius: 4px !important;
            font-size: 10px;
            line-height: 18px;
            text-align: center;
        }
        #table-costing_wrapper .dataTables_paginate .paginate_button.current { background: #1e293b !important; color: white !important; font-weight: 600; box-shadow: none; border-radius: 4px; }
        #table-costing_wrapper .dataTables_paginate .paginate_button.current .page-link { background: #1e293b !important; color: #fff !important; }
        #table-costing_wrapper .dataTables_paginate .paginate_button:hover:not(.current) { background: #f1f5f9 !important; color: #0f172a !important; }

        /* Settings Popover */
        .settings-backdrop { display: none; position: fixed; inset: 0; z-index: 1040; background: rgba(15, 23, 42, .4); }
        .table-settings-popover { display: none; position: fixed; z-index: 1050; top: 0; left: 0; width: 500px; max-width: calc(100% - 32px); background: #fff; border-radius: 8px; box-shadow: 0 10px 40px rgba(15, 23, 42, .2); overflow-y: auto; border: 1px solid #e2e8f0; }
        .table-settings-popover.show, .settings-backdrop.show { display: block; }
        .settings-popover-title { padding: 16px 20px 12px; color: #0f172a; font-size: 14px; font-weight: 700; border-bottom: 1px solid #f1f5f9; }
        .settings-popover-body { padding: 16px 20px; font-size: 11px; color: #475569; }
        .settings-label { display: block; margin: 12px 0 6px; font-size: 11px; font-weight: 600; color: #64748b; }
        .column-toggle { margin: 2px 4px 4px 0; font-size: 10px; border-radius: 4px; padding: 4px 10px; }
        .column-toggle input { display: none; } .column-toggle.active { color: #fff; background: #1e293b; border-color: #1e293b; }
        .column-order-list { display: flex; flex-wrap: wrap; gap: 6px; min-height: 24px; margin-bottom: 10px; }
        .column-order-item { border: 1px solid #cbd5e1; background: #f8fafc; color: #334155; font-size: 10px; padding: 4px 10px; border-radius: 4px; cursor: grab; font-weight: 500; }
        .column-order-item:active { cursor: grabbing; background: #e2e8f0; }
        .settings-choice { border: 1px solid #cbd5e1; background: transparent; color: #475569; font-size: 11px; padding: 4px 12px; border-radius: 4px; font-weight: 500; }
        .settings-choice.active { border-color: #3b82f6; background: #eff6ff; color: #1d4ed8; }
        .settings-choice + .settings-choice { margin-left: 4px; }
        .settings-popover-footer { border-top: 1px solid #f1f5f9; padding: 12px 20px; text-align: right; background: #f8fafc; border-radius: 0 0 8px 8px; }
        .settings-popover-footer .mkt-btn { margin-left: 8px; }

        /* Costing item details modal */
        .swal2-popup.batch-print-modal { width: min(540px, calc(100vw - 32px)) !important; }
        .batch-print-modal .swal2-radio { display: flex; width: 100%; flex-direction: column; align-items: stretch; gap: 8px; margin: 1em 0 3px; }
        .batch-print-modal .swal2-radio label { display: flex; align-items: center; gap: 8px; width: 100%; margin: 0; padding: 10px 12px; border: 1px solid #dce4ec; border-radius: 8px; color: #334155; font-size: 14px; line-height: 1.4; text-align: left; }
        .batch-print-modal .swal2-radio label:has(input:checked) { border-color: #284a63; background: #f1f6fa; }
        .batch-print-modal .swal2-radio input { margin: 0; }
        .batch-print-modal .bp-group-title { margin: 14px 0 0; color: #284a63; font-size: 13px; font-weight: 600; text-align: left; }
        .batch-print-modal .bp-docs { flex-direction: row !important; }
        .batch-print-modal .bp-docs label { flex: 1 1 0; flex-direction: column; align-items: flex-start !important; gap: 2px !important; position: relative; padding-left: 32px !important; }
        .batch-print-modal .bp-docs label input { position: absolute; left: 12px; top: 14px; }
        .batch-print-modal label small { color: #64748b; font-size: 11.5px; }
        .batch-print-modal label.is-disabled { opacity: .5; cursor: not-allowed; }
        @media (max-width: 575px) {
        }
        input[type="checkbox"]:not(.custom-control-input) { -webkit-appearance: none; appearance: none; flex: 0 0 16px; width: 16px; height: 16px; padding: 0; border: 1.5px solid #cfd4dc; border-radius: 4px; background: #fff no-repeat center / 10px 10px; vertical-align: middle; cursor: pointer; transition: background-color .15s ease, border-color .15s ease, box-shadow .15s ease; }
        input[type="checkbox"]:not(.custom-control-input):hover:not(:checked):not(:indeterminate):not(:disabled) { background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12'%3e%3cpath fill='none' stroke='%23cfd4dc' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' d='M2.5 6.2l2.3 2.3 4.7-4.9'/%3e%3c/svg%3e"); }
        input[type="checkbox"]:not(.custom-control-input):checked { border-color: #284a63; background-color: #284a63; background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12'%3e%3cpath fill='none' stroke='%23fff' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' d='M2.5 6.2l2.3 2.3 4.7-4.9'/%3e%3c/svg%3e"); }
        input[type="checkbox"]:not(.custom-control-input):indeterminate { border-color: #284a63; background-color: #284a63; background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12'%3e%3cpath fill='none' stroke='%23fff' stroke-width='2' stroke-linecap='round' d='M3 6h6'/%3e%3c/svg%3e"); }
        input[type="checkbox"]:not(.custom-control-input):focus { outline: 0; }
        input[type="checkbox"]:not(.custom-control-input):focus-visible { box-shadow: 0 0 0 4px #b3daf7; }
        input[type="checkbox"]:not(.custom-control-input):disabled { opacity: .5; cursor: not-allowed; }

        /* ===== Tipografi sesuai QA Figma (Marketing List) — berlaku di semua ukuran layar ===== */
        .costing-page .costing-crumb, .costing-page .costing-crumb b { font-family: 'Poppins', sans-serif !important; font-size: 16px !important; font-weight: 500 !important; }
        .costing-page .costing-title { font-family: 'Poppins', sans-serif !important; font-size: 32px !important; font-weight: 700 !important; }
        .costing-page .search-combo { height: 40px; }
        .costing-page .search-combo .search-by { font-family: Inter, sans-serif !important; font-size: 14px !important; font-weight: 500 !important; }
        .costing-page .search-combo .input-group-text { height: 38px !important; }
        .costing-page .search-combo .toolbar-search { height: 38px; font-family: Inter, sans-serif; font-size: 14px !important; font-weight: 400; }
        .costing-page .search-combo .toolbar-search::placeholder { font-family: Inter, sans-serif; font-size: 14px; font-weight: 400; }
        .costing-page .filter-summary { font-family: Inter, sans-serif; font-size: 14px !important; font-weight: 500 !important; }
        .costing-page .filter-summary .clear-filters { font-family: Inter, sans-serif; font-size: 12px !important; font-weight: 500 !important; }
        .costing-page .costing-status-tabs { font-family: Inter, sans-serif; font-size: 14px !important; }
        .costing-page .costing-status-selected { font-size: 14px !important; font-weight: 700 !important; }
        .costing-page .costing-status-tab, .costing-page .costing-status-tab.active { font-size: 14px !important; font-weight: 500 !important; }
        .costing-page .costing-status-count { font-family: Inter, sans-serif; font-size: 10px !important; font-weight: 500 !important; }
        .costing-page .costing-table thead th { font-family: Inter, sans-serif !important; font-size: 12px !important; font-weight: 500 !important; }
        .costing-page .costing-table tbody td { font-family: Inter, sans-serif !important; font-size: 15px !important; font-weight: 400 !important; }
        .costing-page .costing-table tbody td.costing-col-no { font-size: 14px !important; }
        .costing-page .costing-table .date-badge { font-family: Inter, sans-serif; font-size: 12px !important; font-weight: 500 !important; }
        #table-costing_wrapper .dataTables_length, #table-costing_wrapper .dataTables_length label { font-family: 'Poppins', sans-serif !important; font-size: 16px !important; font-weight: 400 !important; line-height: 34px; }
        #table-costing_wrapper .dataTables_length select { height: 34px; min-width: 70px; font-family: 'Poppins', sans-serif !important; font-size: 16px !important; font-weight: 700 !important; line-height: 24px; color: #1e293b; }

        /* Judul & footer tabel disamakan dengan Approval Center. */
        .costing-page .costing-title { color: #284a63; }
        #table-costing_wrapper .dataTables_info, #table-costing_wrapper .dataTables_length, #table-costing_wrapper .dataTables_length label { color: #212121; font-family: 'Poppins', sans-serif !important; font-size: 16px !important; font-weight: 400 !important; }
        #table-costing_wrapper .dataTables_length select { height: 36px; margin: 0 8px; border: 1px solid #284a63; border-radius: 6px; color: #212121; font-weight: 400 !important; }
        #table-costing_wrapper .dataTables_paginate .pagination { margin: 0; gap: 2px; }
        #table-costing_wrapper .dataTables_paginate .paginate_button,
        #table-costing_wrapper .dataTables_paginate .paginate_button.current { background: none !important; box-shadow: none; }
        #table-costing_wrapper .dataTables_paginate .page-link { min-width: 28px; height: auto; padding: 4px 8px; border: 0; border-radius: 4px !important; background: none; color: #475569; font-family: 'Poppins', sans-serif !important; font-size: 16px !important; font-weight: 400 !important; line-height: 1.5; }
        #table-costing_wrapper .dataTables_paginate .paginate_button.current .page-link,
        #table-costing_wrapper .dataTables_paginate .page-item.active .page-link { background: #284a63 !important; color: #fff !important; }
        #table-costing_wrapper .dataTables_paginate .page-item.disabled .page-link { background: none; color: #cbd5e1; }
        @media (max-width: 767.98px) {
            #table-costing_wrapper .dataTables_info, #table-costing_wrapper .dataTables_length, #table-costing_wrapper .dataTables_length label,
            #table-costing_wrapper .dataTables_length select, #table-costing_wrapper .dataTables_paginate .page-link { font-size: 14px !important; }
        }

        /* Dialog polos: rata kiri, tanpa ikon besar, tombol biasa. */
        .swal2-popup.so-preview-modal { width: min(1280px, calc(100vw - 32px)) !important; }
        .so-preview-modal .swal2-html-container { text-align: left; font-family: Inter, sans-serif; font-size: 14px; }
        /* Banner gaya katalog alert (SO-01 dll.): latar muda warna status + ikon bulat gradien. */
        .so-preview-modal .sp-banner { --g1: #00c7e2; --g2: #007cc1; display: flex; align-items: flex-start; gap: 10px; margin: 8px 0; padding: 10px 12px; border-radius: 10px; background: #e8f6fc; color: #0f172a; font-size: 13px; line-height: 1.45; text-align: left; }
        .so-preview-modal .sp-banner.is-warn { --g1: #ffc766; --g2: #f2994a; background: #fff6e8; }
        .so-preview-modal .sp-banner-ic { flex: none; display: inline-flex; align-items: center; justify-content: center; width: 30px; height: 30px; border-radius: 50%; background: linear-gradient(180deg, var(--g1), var(--g2)); color: #fff; }
        .so-preview-modal .sp-banner-ic svg { width: 16px; height: 16px; }
        .so-preview-modal .sp-banner b { display: block; font-weight: 600; }
        .so-preview-modal .sp-banner span { color: #334155; }
        .so-preview-modal .sp-cols { display: grid; grid-template-columns: 3fr 2fr; gap: 16px; }
        .so-preview-modal .sp-cols > div { min-width: 0; }
        @media (max-width: 900px) { .so-preview-modal .sp-cols { grid-template-columns: 1fr; } }
        .so-preview-modal .sp-info { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 6px 16px; margin: 10px 0 4px; font-size: 12.5px; }
        .so-preview-modal .sp-info div { display: flex; flex-direction: column; min-width: 0; }
        .so-preview-modal .sp-info span { color: #64748b; font-size: 11px; }
        .so-preview-modal .sp-info b { color: #0f172a; font-weight: 600; overflow-wrap: anywhere; }
        .so-preview-modal .sp-sub { margin: 12px 0 4px; color: #334155; font-size: 12px; font-weight: 700; text-transform: uppercase; }
        .so-preview-modal .sp-scroll { max-height: max(220px, calc(100vh - 400px)); overflow: auto; border: 1px solid #e2e8f0; border-radius: 6px; }
        .so-preview-modal .sp-table { width: 100%; border-collapse: collapse; font-size: 12px; }
        .so-preview-modal .sp-table th { position: sticky; top: 0; background: #f1f5f9; color: #334155; font-size: 11px; font-weight: 600; text-transform: uppercase; padding: 6px 8px; text-align: left; white-space: nowrap; }
        .so-preview-modal .sp-table td { padding: 5px 8px; border-top: 1px solid #f1f5f9; color: #1e293b; }
        .so-preview-modal .sp-table tr.g td { border-top-color: #cbd5e1; }
        .so-preview-modal .sp-table .n { text-align: right; white-space: nowrap; }
        .so-preview-modal .sp-table .e { color: #94a3b8; text-align: center; }
        .so-preview-modal .sp-note { margin: 8px 0; padding: 8px 10px; border-radius: 6px; background: #fff7e6; color: #92400e; font-size: 12.5px; }
        .so-preview-modal .sp-note.is-err { background: #fdecec; color: #b42318; }
        .swal2-popup.mkt-plain-modal { width: min(420px, calc(100vw - 32px)) !important; padding: 20px 22px 16px !important; border-radius: 8px !important; text-align: left; font-family: Inter, sans-serif; }
        .mkt-plain-modal .swal2-html-container { margin: 0 !important; padding: 0 !important; text-align: left; color: #333; font-size: 14px; line-height: 1.55; }
        .mkt-plain-modal .swal2-html-container p { margin: 0 0 8px; }
        .mkt-plain-title { margin: 0 0 10px; color: #222; font-size: 18px; font-weight: 600; text-align: center; }
        .mkt-plain-modal .swal2-icon.mkt-plain-icon { margin: 4px auto 12px; font-size: 12px; }
        .mkt-plain-actions { justify-content: flex-end; gap: 8px; width: 100%; margin: 14px 0 0 !important; }
        .mkt-plain-actions button { height: 34px; padding: 0 14px; border-radius: 5px; font-size: 13.5px; font-weight: 500; cursor: pointer; }
        .mkt-plain-ok { border: 1px solid #284a63; background: #284a63; color: #fff; }
        .mkt-plain-ok:hover { background: #1f3b50; }
        .mkt-plain-cancel { border: 1px solid #ccc; background: #fff; color: #444; }
        .mkt-plain-cancel:hover { background: #f3f3f3; }
        .mkt-plain-list { display: flex; justify-content: center; gap: 36px; margin: 6px 0 12px; text-align: left; }
        .mkt-plain-list ul { margin: 4px 0 0; padding-left: 18px; }
        .mkt-plain-list li { margin: 2px 0; }
        .mkt-plain-modal .swal2-icon.mkt-plain-icon-copy .swal2-icon-content { font-size: 2.6em; }
        .mkt-plain-desc { display: inline-block; text-align: left; }

        /* ===== Popup tabel detail (Detail Material SO) — gaya tabel marketing ===== */
        .mkt-dt-modal { padding: 22px 24px 20px !important; border-radius: 16px !important; }
        .mkt-dt-modal .mkt-dt-title { padding: 0 0 14px !important; color: #284a63 !important; font-family: 'Poppins', sans-serif; font-size: 24px !important; font-weight: 700 !important; text-align: left !important; }
        .mkt-dt-modal .mkt-dt-body { margin: 0 !important; padding: 0 !important; text-align: left !important; }
        .mkt-dt-modal .swal2-close { top: 14px; right: 14px; color: #94a3b8; }
        .mkt-dt-modal .swal2-close:hover { color: #284a63; }
        .mkt-dt-card { padding: 8px 14px 10px; border-radius: 12px; background: #fff; box-shadow: 0 2px 10px rgba(15, 23, 42, .10); }
        .mkt-dt-scroll { max-height: 58vh; overflow: auto; border-radius: 8px; scrollbar-color: #284a63 #e2e8f0; }
        .mkt-dt-scroll::-webkit-scrollbar { width: 8px; height: 8px; }
        .mkt-dt-scroll::-webkit-scrollbar-track { background: #e2e8f0; border-radius: 4px; }
        .mkt-dt-scroll::-webkit-scrollbar-thumb { background: #284a63; border-radius: 4px; }
        .mkt-dt-table { width: 100%; min-width: 900px; margin: 0; border-collapse: collapse; }
        .mkt-dt-table th { position: sticky; top: 0; z-index: 1; padding: 10px 8px; border: 0; background: #cfe2ff; color: #1e293b; font-family: 'Poppins', sans-serif; font-size: 12px !important; font-weight: 500 !important; line-height: 16px; letter-spacing: .5px; text-align: left; text-transform: uppercase; white-space: nowrap; }
        .mkt-dt-table td { padding: 8px; border: 0; border-bottom: 1px solid #edf1f5; color: #1e293b; font-family: Inter, sans-serif; font-size: 14px !important; font-weight: 400; line-height: 20px; text-align: left; white-space: nowrap; vertical-align: middle; font-variant-numeric: lining-nums tabular-nums; }
        .mkt-dt-table tbody tr:last-child td { border-bottom: 0; }
        .mkt-dt-table tbody tr:hover td { background: #f6f9ff; }
        .mkt-dt-table .is-no { width: 48px; text-align: center; }
        .mkt-dt-table .is-num { text-align: right; }
        .mkt-dt-table td.is-wide { min-width: 220px; max-width: 320px; white-space: normal; overflow-wrap: anywhere; }
        .mkt-dt-empty { padding: 22px 8px !important; color: #64748b !important; text-align: center !important; white-space: normal !important; }
        .mkt-dt-pager { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; padding: 14px 6px 4px; color: #1e293b; font-family: 'Poppins', sans-serif; font-size: 16px; }
        .mkt-dt-pager > div:first-child { display: inline-flex; align-items: center; gap: 12px; }
        .mkt-dt-pager select { height: 40px; min-width: 76px; padding: 0 10px; border: 1px solid #284a63; border-radius: 8px; font-size: 16px; }
        .mkt-dt-pages { display: inline-flex; align-items: center; flex-wrap: wrap; gap: 8px; color: #64748b; }
        .mkt-dt-pages button { min-width: 32px; height: 32px; padding: 0 6px; border: 0; border-radius: 6px; background: transparent; color: #64748b; font-size: 16px; font-weight: 500; }
        .mkt-dt-pages button.active { background: #274b63; color: #fff; font-weight: 600; }
        .mkt-dt-pages button:disabled { opacity: .35; }
        .mkt-dt-modal .mkt-dt-actions { justify-content: flex-end; width: 100%; margin: 18px 0 0; }
        .mkt-dt-modal .mkt-dt-actions .mkt-btn:focus { outline: 0; box-shadow: 0 0 0 3px rgba(40, 74, 99, .18); }
        @media (max-width: 575.98px) {
            .mkt-dt-modal { padding: 16px 12px 14px !important; }
            .mkt-dt-modal .mkt-dt-title { font-size: 20px !important; }
            .mkt-dt-card { padding: 6px 8px 8px; }
        }

    </style>
@endsection

@section('content')
    <div class="costing-page">

        <div class="card card-sb">
            <div class="card-body">
                <div class="costing-crumb"><a href="{{ route('home') }}">Home</a> &nbsp;›&nbsp; <a href="{{ route('dashboard-marketing') }}">Marketing</a> &nbsp;›&nbsp; <a href="{{ route('master-costing') }}">Process</a> &nbsp;›&nbsp; <b>Marketing List</b></div>
                <h1 class="costing-title mt-3">Marketing List</h1>

                <div class="list-shell">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <div class="list-toolbar">
                            <button type="button" class="toolbar-btn" onclick="openTableSettings(event)" title="Atur kolom tabel"><i class="fas fa-table-cells"></i></button>
                            <div class="search-combo">
                            <button type="button" id="search-by" class="search-by"><i class="fas fa-filter mr-1"></i> Search By <i class="fas fa-chevron-down ml-1"></i></button>

                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-white" style="height: 32px; border: 1px solid #e2e8f0; border-right: none; border-radius: 6px 0 0 6px; padding: 0 10px;">
                                        <i class="fas fa-search text-muted" style="font-size: 11px;"></i>
                                    </span>
                                </div>
                                <input id="costing-search" class="form-control toolbar-search m-0" style="border-left: none; border-radius: 0 6px 6px 0; padding-left: 0; box-shadow: none;" placeholder="Search">
                            </div>

                            <div id="search-menu" class="search-menu">
                                <button type="button" data-filter="date">Costing Date <i class="fas fa-chevron-right float-right mt-1"></i></button>
                                <button type="button" data-column="4">No Costing</button>
                                <button type="button" data-filter="buyer">Buyer <i class="fas fa-chevron-right float-right mt-1"></i></button>
                                <button type="button" data-column="5">No SO</button>
                                <button type="button" data-column="8">Brand</button>
                                <button type="button" data-column="9">Style</button>
                                <button type="button" data-column="10">Market</button>
                                <div id="date-submenu" class="search-submenu">
                                    <b class="d-block mb-1 text-dark">Costing Date</b>
                                    <label>Start Date<input type="date" id="menu-tgl-awal" class="form-control" value="{{ now()->subMonths(3)->toDateString() }}"></label>
                                    <label>End Date<input type="date" id="menu-tgl-akhir" class="form-control" value="{{ now()->toDateString() }}"></label>
                                    <button type="button" class="btn btn-sm btn-primary w-100 mt-2" id="apply-date-filter">Apply</button>
                                </div>
                                <div id="buyer-submenu" class="search-submenu">
                                    <b class="d-block mb-1 text-dark">Buyer</b>
                                    <input type="search" id="buyer-search" class="form-control form-control-sm buyer-search mb-2" placeholder="Search buyer...">
                                    <label><input type="checkbox" id="buyer-all"> All</label>
                                    <div id="buyer-options">
                                        @foreach($buyers as $buyer)
                                            <label class="buyer-option"><input type="checkbox" class="buyer-filter" value="{{ $buyer->id }}"> {{ $buyer->name }}</label>
                                        @endforeach
                                    </div>
                                    <button type="button" class="btn btn-sm btn-primary w-100 mt-2" id="apply-buyer-filter">Apply</button>
                                </div>
                            </div>
                            </div>
                        </div>

                        <button type="button" class="mkt-btn mkt-btn--navy mkt-btn--sm" id="btn-batch-print"><i class="fas fa-print mr-1"></i> Print</button>
                    </div>

                    <div class="filter-summary">
                        <span>Filter:</span>
                        <span id="filter-label">None</span>
                        <button type="button" id="clear-filters" class="clear-filters" disabled>Clear all</button>
                    </div>

                    {{-- <div class="selected-wrapper">
                        <span>Selected</span>
                        <span class="selected-badge" id="selected-count">0</span>
                    </div> --}}

                    <div class="costing-status-tabs" aria-label="Status approval costing">
                        <span class="costing-status-selected">Selected <span class="costing-status-count" id="selected-count">0</span></span>
                        <button type="button" class="costing-status-tab" data-status="awaiting" title="Belum di-approve; costing masih bisa diedit">
                            <span class="costing-status-swatch"></span> Awaiting Approval <span class="costing-status-count" data-count="awaiting">0</span>
                        </button>
                        <button type="button" class="costing-status-tab" data-status="return" title="Dikembalikan manager untuk direvisi; setelah disimpan kembali ke Awaiting Approval">
                            <span class="costing-status-swatch"></span> Return to Staff <span class="costing-status-count" data-count="return">0</span>
                        </button>
                        <button type="button" class="costing-status-tab" data-status="cancel" title="Ditolak manager / dibatalkan">
                            <span class="costing-status-swatch"></span> Cancel <span class="costing-status-count" data-count="cancel">0</span>
                        </button>
                        <button type="button" class="costing-status-tab" data-status="approved" title="Sudah di-approve dan terkunci; ajukan Request Unlock untuk mengubah">
                            <span class="costing-status-swatch"></span> Approved <span class="costing-status-count" data-count="approved">0</span>
                        </button>
                        <button type="button" class="costing-status-tab" data-status="unlocked" title="Dibuka manager untuk diedit peminta; Save and lock mengunci lagi dan kembali ke approval manager">
                            <span class="costing-status-swatch"></span> Unlocked <span class="costing-status-count" data-count="unlocked">0</span>
                        </button>
                    </div>

                    <div class="costing-table-wrap" id="table-costing-wrap">
                        <table class="table w-100 costing-table" id="table-costing">
                            <thead>
                                <tr class="text-center">
                                    <th><input type="checkbox" id="check-all-costing"></th>
                                    <th title="Timeline"></th>
                                    <th>No</th>
                                    <th>Costing Date</th>
                                    <th>No Costing</th>
                                    <th>No SO</th>
                                    <th>No Worksheet</th>
                                    <th>Buyer</th>
                                    <th>Brand</th>
                                    <th>Style</th>
                                    <th>Market</th>
                                    <th>Qty</th>
                                    <th>Confirm Price</th>
                                    <th>Product Group</th>
                                    <th>Product Item</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-end align-items-center mt-4">
                        @if (!empty($ndsTrial))
                            <span class="text-muted small me-3">Mode percobaan: Costing tersimpan di DB baru</span>
                        @endif
                        <a href="{{ route('create-costing') }}" class="mkt-btn mkt-btn--navy">Add New</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="settings-backdrop" class="settings-backdrop" onclick="closeTableSettings()"></div>
    <div id="tableSettings" class="table-settings-popover" aria-hidden="true">
        <div class="settings-popover-title">Table settings</div>
        <div class="settings-popover-body">
            <span class="settings-label">Table column</span>
            <div id="column-toggles"></div>

            <span class="settings-label mt-3">Column order</span>
            <div id="column-order" class="column-order-list"></div>

            <span class="settings-label mt-3">Amount format</span>
            <button type="button" class="settings-choice amount-format" data-format="compact">10.5k</button>
            <button type="button" class="settings-choice amount-format active" data-format="full">10.000,00</button>

            <span class="settings-label mt-3">Date format</span>
            <button type="button" class="settings-choice date-format active" data-format="DD/MM/YYYY">DD/MM/YYYY</button>
            <button type="button" class="settings-choice date-format" data-format="MM/DD/YYYY">MM/DD/YYYY</button>
            <button type="button" class="settings-choice date-format" data-format="DD/MMM/YYYY">DD/MMM/YYYY</button>

            <label class="d-block mt-3 mb-0 font-weight-bold text-dark"><input type="checkbox" id="sticky-header" class="mr-1"> Sticky header</label>
        </div>
        <div class="settings-popover-footer">
            <button type="button" class="mkt-btn mkt-btn--gray mkt-btn--sm" id="reset-settings">Reset</button>
            <button type="button" class="mkt-btn mkt-btn--green mkt-btn--sm" id="apply-settings">Apply</button>
        </div>
    </div>

    @include('marketing.costing.unlock_request_modal')
@endsection

@section('custom-script')
<script src="{{ asset('dist/js/marketing-title-case.js') }}?v={{ @filemtime(public_path('assets/dist/js/marketing-title-case.js')) }}"></script>
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>

    <script src="{{ asset('dist/js/marketing-empty-state.js') }}?v={{ @filemtime(public_path('assets/dist/js/marketing-empty-state.js')) }}"></script>
    <script>
        let table;
        let activeSearchColumn = '';
        let selectedBuyers = [];
        let dateFilterApplied = false;
        const defaultCostingStartDate = '{{ now()->subMonths(3)->toDateString() }}';
        const defaultCostingEndDate = '{{ now()->toDateString() }}';
        const defaultColumnOrder = Array.from({ length: 16 }, (_, index) => index);
        let pendingColumnOrder = defaultColumnOrder.slice();
        let appliedColumnOrder = defaultColumnOrder.slice();
        let amountFormat = 'full';
        let dateFormat = 'DD/MM/YYYY';
        let activeCostingMenu = null;
        let activeApprovalStatus = '';
        const COSTING_STATUS = {
            awaiting: { label: 'Awaiting Approval', desc: 'belum di-approve, masih bisa diedit' },
            return: { label: 'Return to Staff', desc: 'perlu direvisi sesuai catatan manager' },
            cancel: { label: 'Cancel', desc: 'ditolak manager / dibatalkan' },
            approved: { label: 'Approved', desc: 'terkunci, perlu Request Unlock untuk mengubah' },
            unlocked: { label: 'Unlocked', desc: 'dibuka untuk diedit peminta, Save and lock mengunci lagi dan kembali ke approval manager' }
        };

        $(document).ready(function() {

            table = $('#table-costing').DataTable({
                processing: true,
                serverSide: true,
                responsive: false,
                scrollX: false,
                autoWidth: false,
                scrollY: '55vh',
                scrollCollapse: true,
                pageLength: 7,
                lengthMenu: [[7, 15, 25, 50], [7, 15, 25, 50]],
                dom: 'rt<"d-flex justify-content-between align-items-center mt-3"<"d-flex align-items-center gap-3"li>p>',
                // language: {
                //     lengthMenu: "Item Per Page _MENU_",
                //     info: "Of _MAX_",
                //     infoEmpty: "Of 0",
                //     paginate: {
                //         previous: "<i class='fas fa-chevron-left'></i>",
                //         next: "<i class='fas fa-chevron-right'></i>"
                //     }
                // },
                ajax: {
                    url: '{{ route("master-costing") }}',
                    data: function(d) {
                        // Costing Date hanya ikut kalau filternya diterapkan (sebelumnya selalu terkirim 3 bulan terakhir walau Filter: None).
                        if (dateFilterApplied) {
                            d.tgl_awal = $('#menu-tgl-awal').val();
                            d.tgl_akhir = $('#menu-tgl-akhir').val();
                        }
                        d.buyers = selectedBuyers;
                        d.approval_status = activeApprovalStatus;
                    }
                },
                columns: [
                    { data: 'id', orderable: false, searchable: false, className: 'text-center align-middle', render: (data, type, row) => `<input type="checkbox" class="costing-check" value="${data}" data-so="${row.so_id || ''}" data-bom="${Number(row.bom_count) ? 1 : 0}">` },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        className: 'text-center align-middle costing-timeline-cell',
                        render: function (data, type, row) {
                            const status = COSTING_STATUS[row.approval_status] ? row.approval_status : 'awaiting';
                            const meta = COSTING_STATUS[status];
                            const note = ['return', 'cancel'].includes(status) && row.approval_note ? `\nCatatan manager: ${row.approval_note}` : '';
                            const title = $('<div>').text(`${meta.label}: ${meta.desc}${note}`).html().replace(/"/g, '&quot;');
                            return `<span class="costing-status-neon is-${status}" title="${title}"></span>` +
                                '<button type="button" class="timeline-toggle" aria-expanded="false" title="Lihat timeline"><i class="fas fa-chevron-right"></i></button>';
                        }
                    },
                    {
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        className: 'text-center align-middle costing-col-no'
                    },
                    {
                        data: 'costing_date',
                        name: 'a.dateinput',
                        className: 'text-center align-middle costing-nowrap',
                        render: function (data, type) {
                            if (type !== 'display' || !data || data === '-') return data || '-';
                            const match = String(data).match(/^(\d{1,2})\s+([A-Za-z]{3})\s+(\d{4})$/);
                            if (!match) return `<span class="date-badge">${data}</span>`;
                            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                            const monthIndex = months.findIndex(month => month.toLowerCase() === match[2].toLowerCase());
                            if (monthIndex < 0) return `<span class="date-badge">${data}</span>`;

                            const day = match[1].padStart(2, '0');
                            const month = String(monthIndex + 1).padStart(2, '0');

                            let formattedDate = dateFormat === 'MM/DD/YYYY'
                                ? `${month}/${day}/${match[3]}`
                                : dateFormat === 'DD/MMM/YYYY'
                                    ? `${day} ${months[monthIndex]} ${match[3]}`
                                    : `${day}/${month}/${match[3]}`;

                            return `<span class="date-badge">${formattedDate}</span>`;
                        }
                    },
                    { data: 'cost_no', name: 'a.cost_no', className: 'text-center align-middle text-dark costing-nowrap' },
                    // Satu baris per SO: Costing dengan beberapa SO tampil beberapa baris (Add SO = baris baru).
                    { data: 'so_no', name: 's.so_no', className: 'text-center align-middle costing-nowrap', render: data => data ? escapeCostingHtml(data) : '-' },
                    {
                        data: 'kpno',
                        name: 'a.kpno',
                        className: 'text-center align-middle costing-nowrap',
                        // SO dengan beberapa set menampilkan semua WS-nya, satu per baris.
                        render: (data, type, row) => {
                            if (!data) return '-';
                            if (type !== 'display') return data;
                            const list = String(data).split('\n').map(escapeCostingHtml).join('<br>');
                            return row.ws_count > 1 ? `${list}<div class="text-muted small">${row.ws_count} WS</div>` : list;
                        }
                    },
                    { data: 'nama_buyer', name: 'b.Supplier', className: 'align-middle', render: costingPlainCase },
                    { data: 'brand', name: 'a.brand', className: 'align-middle', render: costingPlainCase },
                    { data: 'styleno', name: 'a.styleno', className: 'align-middle', render: costingPlainCase },
                    { data: 'market', name: 'a.market', orderable: false, searchable: true, className: 'align-middle' },
                    {
                        data: 'qty',
                        name: 'a.qty',
                        className: 'text-center align-middle costing-nowrap',
                        render: function (data, type) {
                            if (type !== 'display' || data === null || data === '') return data;
                            const value = Number(data);
                            if (!Number.isFinite(value)) return data;
                            return String(Math.round(value)); // Qty selalu angka bulat polos: 15000
                        }
                    },
                    {
                        data: 'cfm_price',
                        name: 'a.cfm_price',
                        className: 'text-center align-middle costing-nowrap',
                        render: function (data, type, row) {
                            if (type !== 'display') return data;
                            const value = data !== null && data !== '' ? parseFloat(data) : 0;
                            if (!Number.isFinite(value)) return data;
                            // Amount format (Table Settings) berlaku untuk Confirm Price: 10,5k atau 10.000,00
                            return amountFormat === 'compact'
                                ? new Intl.NumberFormat('en-US', { notation: 'compact', maximumFractionDigits: 1 }).format(value).replace(/K$/, 'k').replace('.', ',')
                                : new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(value);
                        }
                    },
                    { data: 'product_group_text', name: 'p.product_group', className: 'text-center align-middle', render: costingTitleCase },
                    { data: 'product_item_text', name: 'p.product_item', className: 'text-center align-middle', render: costingTitleCase },
                    {
                        data: 'id',
                        name: 'a.id',
                        orderable: false,
                        searchable: false,
                        className: 'text-center align-middle',
                        render: function (data, type, row) {
                            let editUrl = "{{ route('edit-costing', ':id') }}".replace(':id', data);
                            let detailUrl = "{{ route('detail-costing', ':id') }}".replace(':id', data);
                            let pdfUrl = "{{ route('print-costing-pdf', ':id') }}".replace(':id', data);
                            let excelUrl = "{{ route('print-excel-costing', ':id') }}".replace(':id', data);
                            let copyUrl = "{{ route('copy-costing', ':id') }}".replace(':id', data);

                            const AR_SO_ICON = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="8" y="2" width="8" height="4" rx="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="M9 12h6M9 16h6"/></svg>';
                            const approvedStatus = String(row.app1 || '').trim().toUpperCase();
                            const approved = ['A', 'Y'].includes(approvedStatus);
                            const hasSo = Boolean(row.so_id);
                            const hasBom = Boolean(row.bom_id) && Number(row.bom_item_count) > 0;

                            const ndsTrial = @json(!empty($ndsTrial));
                            const soCreateUrl = "{{ !empty($ndsTrial) ? route('so-nds-create') : route('create-so') }}?id_costing=" + encodeURIComponent(data);
                            const bomCreateUrl = "{{ route('create-bom') }}?id_costing=" + encodeURIComponent(data) + "&id_so=" + encodeURIComponent(row.so_id);

                            const soDetailUrl = hasSo ? "{{ route('so-detail-page', ':id') }}".replace(':id', row.so_id) : '';
                            const soEditUrl = hasSo ? "{{ route('so-edit-page', ':id') }}".replace(':id', row.so_id) : '';
                            const soPdfUrl = hasSo ? "{{ route('print-pdf-so', ':id') }}".replace(':id', row.so_id) : '';
                            const soExcelUrl = hasSo ? "{{ route('export-excel-so', ':id') }}".replace(':id', row.so_id) : '';
                            const bomPdfUrl = hasBom ? "{{ route('print-bom-pdf', ':id') }}".replace(':id', row.bom_id) : '';
                            const bomExcelUrl = hasBom ? "{{ route('export-excel-bom') }}?id=" + encodeURIComponent(row.bom_id) : '';
                            // DB baru: SO dibuat dari menu Costing (cek harga CMT dulu); satu Costing boleh punya beberapa SO.
                            const cmtAttrs = `data-cmt-check="${"{{ route('so-nds-cmt-check', ':id') }}".replace(':id', data)}" data-cmt-url="${editUrl}#cmt"`;
                            const soCount = Number(row.so_count) || 0;
                            // Alur approval: SO hanya bisa dibuat dari Costing yang sudah Approved.
                            const soBlocked = { awaiting: 'Costing belum di-approve', return: 'Costing di-Return ke Staff', cancel: 'Costing di-Cancel', unlocked: 'Costing sedang di-unlock' }[row.approval_status] || 'Costing belum di-approve';
                            // SO pertama dibuat lewat tombol SO (putih); Add SO di menu Costing untuk SO berikutnya.
                            const addSoItem = !ndsTrial || !hasSo ? '' : (Number(row.so_allowed) ? `<div class="dropdown-divider"></div>
                                        <a class="dropdown-item mkt-fab is-add so-add-preview" href="${soCreateUrl}" data-preview="${"{{ route('so-nds-costing-preview', ':id') }}".replace(':id', data)}" data-cmt-url="${editUrl}#cmt"><span class="mkt-fab-icon"><i class="fas fa-plus-circle"></i></span><span class="mkt-fab-label">Add SO${soCount ? `<small>Sudah ada ${soCount} SO di Costing ini</small>` : ''}</span></a>`
                                    : `<div class="dropdown-divider"></div>
                                        <button type="button" class="dropdown-item mkt-fab is-add" disabled title="SO baru bisa dibuat setelah Costing di-approve di Approval Center"><span class="mkt-fab-icon"><i class="fas fa-plus-circle"></i></span><span class="mkt-fab-label">Add SO<small>${soBlocked}</small></span></button>`);

                            let costingBtn = `
                                <div class="dropdown d-inline-block">
                                    <button class="action-round is-costing costing-menu-toggle" data-menu-id="costing-menu-${data}" aria-expanded="false" type="button" title="Costing Menu">
                                        <i class="fas fa-calculator"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right costing-actions-menu" id="costing-menu-${data}">
                                        <a class="dropdown-item mkt-fab is-detail" href="${detailUrl}"><span class="mkt-fab-icon"><i class="fas fa-info-circle"></i></span>Detail</a>
                                        ${row.approval_status === 'approved'
                                            ? unlockMenuItem('costing', data, row.cost_no, row.nama_buyer, row.costing_unlock_pending)
                                            : `<a class="dropdown-item mkt-fab is-edit" href="${editUrl}"><span class="mkt-fab-icon"><i class="fas fa-edit"></i></span>Edit</a>`}
                                        <button type="button" class="dropdown-item mkt-fab is-copy" onclick="confirmCopy(this)" data-url="${copyUrl}" data-no="${escapeCostingHtml(row.cost_no).replace(/"/g, '&quot;')}" data-buyer="${escapeCostingHtml(row.nama_buyer).replace(/"/g, '&quot;')}" data-style="${escapeCostingHtml(row.styleno).replace(/"/g, '&quot;')}"><span class="mkt-fab-icon"><i class="far fa-copy"></i></span>Copy</button>
                                        <a class="dropdown-item mkt-fab is-pdf" href="${pdfUrl}" target="_blank"><span class="mkt-fab-icon"><i class="fas fa-file-pdf"></i></span>PDF</a>
                                        <a class="dropdown-item mkt-fab is-excel" href="${excelUrl}"><span class="mkt-fab-icon"><i class="fas fa-file-excel"></i></span>EXCEL</a>
                                        ${addSoItem}
                                    </div>
                                </div>`;

                            let soMenu = '';
                            if (ndsTrial) {
                                // Tombol putih = belum dibuat (klik untuk Create), warna penuh = sudah ada. BOM dibuka per SO (pilih WS di halamannya). Worksheet masih dikembangkan.
                                const soNdsEditUrl = "{{ route('so-nds-edit', ':id') }}".replace(':id', row.so_id);
                                const fab = (cls, icon, label, href, extra = '') => `<a class="dropdown-item mkt-fab ${cls}" href="${href}" ${extra}><span class="mkt-fab-icon"><i class="${icon}"></i></span>${label}</a>`;
                                const menu = (id, toggle, items) => `<div class="dropdown d-inline-block">${toggle(id)}<div class="dropdown-menu dropdown-menu-right costing-actions-menu" id="${id}">${items}</div></div>`;
                                if (hasSo) {
                                    // SO: Edit (cek CMT dulu), Detail, PDF, Excel. SO tanpa approval; WS sudah dipakai Marker -> Edit memunculkan pop-up hubungi IT.
                                    const soEditItem = row.so_marker_message
                                        ? `<button type="button" class="dropdown-item mkt-fab is-edit so-marker-locked" data-message="${escapeCostingHtml(row.so_marker_message).replace(/"/g, '&quot;')}"><span class="mkt-fab-icon"><i class="fas fa-edit"></i></span>Edit</button>`
                                        : fab('is-edit so-cmt-check', 'fas fa-edit', 'Edit', soNdsEditUrl, cmtAttrs);
                                    // Titik merah: Costing berubah, SO perlu disesuaikan (alasan di tooltip). Hilang setelah SO disimpan ulang.
                                    const soStale = Array.isArray(row.so_stale) ? row.so_stale : [];
                                    const soTitle = soStale.length ? escapeCostingHtml('SO perlu disesuaikan: ' + soStale.join('; ')).replace(/"/g, '&quot;') : 'SO Menu';
                                    soMenu = menu(`so-nds-menu-${row.so_id}`, id => `<button class="action-round is-so costing-menu-toggle${soStale.length ? ' has-stale' : ''}" data-menu-id="${id}" aria-expanded="false" type="button" title="${soTitle}">${AR_SO_ICON}</button>`,
                                        fab('is-detail', 'fas fa-info-circle', 'Detail', "{{ route('so-nds-detail', ':id') }}".replace(':id', row.so_id))
                                        + soEditItem
                                        + fab('is-pdf', 'fas fa-file-pdf', 'PDF', "{{ route('so-nds-pdf', ':id') }}".replace(':id', row.so_id), 'target="_blank"')
                                        + fab('is-excel', 'fas fa-file-excel', 'EXCEL', "{{ route('so-nds-excel', ':id') }}".replace(':id', row.so_id)));
                                } else if (Number(row.so_allowed)) {
                                    // Belum ada SO: tombol putih = langsung ke form Create SO (tanpa pop-up ringkasan; pop-up hanya untuk Add SO di menu Costing).
                                    soMenu = `<a href="${soCreateUrl}" class="action-round is-so is-next" title="Create SO" aria-label="Create SO">${AR_SO_ICON}</a>`;
                                } else {
                                    soMenu = `<button class="action-round is-so is-off" disabled title="Belum bisa membuat SO: ${soBlocked}">${AR_SO_ICON}</button>`;
                                }
                                // BOM: belum ada = tombol putih (Create BOM), sudah ada = menu penuh. Kunci / Request Unlock per WS ada di halaman Input BOM.
                                // Titik merah menempel selama BOM belum sesuai Costing / SO (content Costing ditambah/dihapus, color/size/destinasi SO berubah).
                                const bomStaleDetail = Array.isArray(row.bom_stale_detail) ? row.bom_stale_detail : [];
                                const bomStale = bomStaleDetail.length > 0;
                                // Tooltip singkat; rinciannya ada di banner halaman BOM.
                                const staleTitle = bomStale
                                    ? escapeCostingHtml(`Ada item yang harus disesuaikan (${bomStaleDetail.map(w => w.no_ws).join(', ')})`).replace(/"/g, '&quot;')
                                    : 'BOM Menu';
                                const bomNdsEditUrl = "{{ route('bom-nds-edit', ':id') }}".replace(':id', row.so_id);
                                // Satu BOM (per SO) berisi beberapa WS yang dipindah lewat tab di halaman BOM; approval/kunci per WS.
                                // Edit selama masih ada WS yang belum approved / belum dibuat. Request Unlock satu menu saja:
                                // kalau WS terkunci lebih dari satu, WS-nya dipilih di dialog (lihat .bom-unlock-pick).
                                const bomEditItems = () => {
                                    const list = Array.isArray(row.bom_list) ? row.bom_list : [];
                                    const locked = list.filter(b => b.status === 'approved');
                                    const canEdit = locked.length < list.length || Number(row.ws_count) > list.length;
                                    const edit = canEdit ? fab('is-edit', 'fas fa-edit', 'Edit', bomNdsEditUrl) : '';
                                    if (!locked.length) return edit;
                                    const open = locked.filter(b => !Number(b.unlock_pending));
                                    if (!open.length) return edit + unlockMenuItem('bom', locked[0].id, locked[0].bom_no, row.nama_buyer, 1);
                                    if (open.length === 1) return edit + unlockMenuItem('bom', open[0].id, open[0].bom_no, row.nama_buyer, 0, locked.length > 1 || Number(row.ws_count) > 1 ? open[0].no_ws : '');
                                    const data = escapeCostingHtml(JSON.stringify(open.map(b => ({ id: b.id, bom_no: b.bom_no, no_ws: b.no_ws })))).replace(/"/g, '&quot;');
                                    return edit + `<button type="button" class="dropdown-item mkt-fab is-edit bom-unlock-pick" data-list="${data}" data-buyer="${escapeCostingHtml(row.nama_buyer).replace(/"/g, '&quot;')}"><span class="mkt-fab-icon"><i class="fas fa-lock-open"></i></span><span class="mkt-fab-label">Request Unlock<small>${open.length} WS terkunci</small></span></button>`;
                                };
                                const ndsBomBtn = !hasSo
                                    ? `<button class="action-round is-bom is-off" disabled title="Buat Sales Order dulu"><i class="fas fa-layer-group"></i></button>`
                                    : !Number(row.bom_count)
                                    ? `<a href="${bomNdsEditUrl}" class="action-round is-bom is-next" title="Create BOM" aria-label="Create BOM"><i class="fas fa-layer-group"></i></a>`
                                    : menu(`bom-nds-menu-${row.so_id}`, id => `<button class="action-round is-bom costing-menu-toggle${bomStale ? ' has-stale' : ''}" data-menu-id="${id}" aria-expanded="false" type="button" title="${staleTitle}"><i class="fas fa-layer-group"></i></button>`,
                                        bomEditItems()
                                        + fab('is-detail', 'fas fa-info-circle', 'Detail', "{{ route('bom-nds-detail', ':id') }}".replace(':id', row.so_id))
                                        + fab('is-pdf', 'fas fa-file-pdf', 'PDF', "{{ route('bom-nds-pdf', ':id') }}".replace(':id', row.so_id), 'target="_blank"')
                                        + fab('is-excel', 'fas fa-file-excel', 'EXCEL', "{{ route('bom-nds-excel', ':id') }}".replace(':id', row.so_id)));
                                return `<div class="costing-actions">${costingBtn}${soMenu}${ndsBomBtn}</div>`;
                            }
                            if (hasSo) {
                                soMenu = `
                                <div class="dropdown d-inline-block">
                                    <button class="action-round is-so costing-menu-toggle" data-menu-id="so-menu-${row.so_id}" aria-expanded="false" type="button" title="SO Menu">
                                        ${AR_SO_ICON}
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right costing-actions-menu" id="so-menu-${row.so_id}">
                                        <a class="dropdown-item mkt-fab is-detail" href="${soDetailUrl}"><span class="mkt-fab-icon"><i class="fas fa-info-circle"></i></span>Detail</a>
                                        <a class="dropdown-item mkt-fab is-edit" href="${soEditUrl}"><span class="mkt-fab-icon"><i class="fas fa-edit"></i></span>Edit</a>
                                        <button type="button" class="dropdown-item mkt-fab is-detail" onclick="showCostingSoMaterial(${row.so_id})"><span class="mkt-fab-icon"><i class="fas fa-boxes"></i></span>Detail Material</button>
                                        <a class="dropdown-item mkt-fab is-pdf" href="${soPdfUrl}" target="_blank"><span class="mkt-fab-icon"><i class="fas fa-file-pdf"></i></span>PDF</a>
                                        <a class="dropdown-item mkt-fab is-excel" href="${soExcelUrl}"><span class="mkt-fab-icon"><i class="fas fa-file-excel"></i></span>EXCEL</a>
                                    </div>
                                </div>`;
                            } else if (approved) {
                                soMenu = `<a href="${soCreateUrl}" class="action-round is-so is-next" title="Create SO" aria-label="Create SO">${AR_SO_ICON}</a>`;
                            } else {
                                soMenu = `<button class="action-round is-so is-off" disabled title="Belum bisa membuat SO">${AR_SO_ICON}</button>`;
                            }

                            let bomMenu = '';
                            const canCreateBom = approved && hasSo;
                            if (hasBom && approved) {
                                bomMenu = `
                                <div class="dropdown d-inline-block">
                                    <button class="action-round is-bom costing-menu-toggle" data-menu-id="bom-menu-${row.bom_id}" aria-expanded="false" type="button" title="BOM Menu">
                                        <i class="fas fa-layer-group"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right costing-actions-menu" id="bom-menu-${row.bom_id}">
                                        <button type="button" class="dropdown-item mkt-fab is-detail" onclick="showCostingBomDetail(${row.bom_id})"><span class="mkt-fab-icon"><i class="fas fa-info-circle"></i></span>Detail</button>
                                        ${row.bom_approval_status === 'approved'
                                            ? unlockMenuItem('bom', row.bom_id, row.bom_no, row.nama_buyer, row.bom_unlock_pending)
                                            : (hasSo ? `<a class="dropdown-item mkt-fab is-edit" href="${bomCreateUrl}"><span class="mkt-fab-icon"><i class="fas fa-edit"></i></span>Edit Item</a>` : '')}
                                        <a class="dropdown-item mkt-fab is-pdf" href="${bomPdfUrl}" target="_blank"><span class="mkt-fab-icon"><i class="fas fa-file-pdf"></i></span>PDF</a>
                                        <a class="dropdown-item mkt-fab is-excel" href="${bomExcelUrl}"><span class="mkt-fab-icon"><i class="fas fa-file-excel"></i></span>EXCEL</a>
                                    </div>
                                </div>`;
                            } else if (canCreateBom) {
                                bomMenu = `<a href="${bomCreateUrl}" class="action-round is-bom is-next" title="Create BOM" aria-label="Create BOM"><i class="fas fa-layer-group"></i></a>`;
                            } else {
                                bomMenu = `<button class="action-round is-bom is-off" disabled title="Belum bisa membuat BOM"><i class="fas fa-layer-group"></i></button>`;
                            }

                            // Worksheet butuh SO (color / size) dan BOM berisi item (shell).
                            const wsUrl = "{{ route('marketing-worksheet', ':id') }}".replace(':id', data);
                            let extraBtn = hasSo && hasBom
                                ? `<a href="${wsUrl}" class="action-round is-ws" aria-label="Create Worksheet" title="Create Worksheet"><i class="fa-solid fa-rectangle-list" ></i></a>`
                                : `<button type="button" class="action-round is-ws is-off" disabled title="Butuh Sales Order dan BOM"><i class="fa-solid fa-rectangle-list"></i></button>`;

                            return `<div class="costing-actions">${costingBtn}${soMenu}${bomMenu}${extraBtn}</div>`;
                        }
                    }
                ],
                order: [[3, 'desc']],
                initComplete: function () {
                    const api = this.api();
                    api.columns().every(function (i) {
                        const title = $(this.header()).text().trim() || 'Checkbox';
                        $(this.header()).addClass(`costing-col-${i}`);
                        const shown = !COSTING_HIDDEN_BY_DEFAULT.includes(i);
                        $('#column-toggles').append(`<label class="btn btn-sm btn-outline-secondary column-toggle ${shown ? 'active' : ''}"><input type="checkbox" ${shown ? 'checked' : ''} data-column="${i}"> ${title}</label>`);
                    });
                    $('#table-costing_wrapper thead tr').each(function () {
                        Array.from(this.cells).forEach((cell, index) => {
                            cell.classList.add(`costing-col-${index}`);
                        });
                    });


                    $('#table-costing_wrapper .dataTables_length').addClass('d-flex align-items-center gap-2');

                    renderColumnOrder();
                    applyColumnOrder();
                    fitCostingColumns();
                }
            });
            table.on('draw.dt', applyColumnOrder);
            table.on('draw.dt', alignCostingHeader);

            let costingFitTimer = null;
            $(window).on('resize', function () {
                clearTimeout(costingFitTimer);
                costingFitTimer = setTimeout(fitCostingColumns, 150);
            });

            $(document).on('click', '.costing-menu-toggle', function (event) {
                event.preventDefault();
                event.stopPropagation();
                const menu = document.getElementById(this.dataset.menuId);
                if (!menu) return;

                if (activeCostingMenu && activeCostingMenu.menu === menu) {
                    closeCostingActionMenu();
                    return;
                }
                closeCostingActionMenu();

                const placeholder = document.createComment('costing-action-menu');
                menu.parentNode.insertBefore(placeholder, menu);
                document.body.appendChild(menu);
                menu.classList.add('show');
                this.setAttribute('aria-expanded', 'true');
                activeCostingMenu = { menu: menu, placeholder: placeholder, button: this };

                const buttonRect = this.getBoundingClientRect();
                const menuRect = menu.getBoundingClientRect();
                const top = buttonRect.bottom + menuRect.height <= window.innerHeight - 8
                    ? buttonRect.bottom + 4
                    : Math.max(8, buttonRect.top - menuRect.height - 4);
                const rightPosition = buttonRect.right + 4;
                const leftPosition = buttonRect.left - menuRect.width - 4;
                const left = rightPosition + menuRect.width <= window.innerWidth - 8
                    ? rightPosition
                    : Math.max(8, leftPosition);
                menu.style.top = `${top}px`;
                menu.style.left = `${left}px`;
                menu.style.right = 'auto';
                menu.style.bottom = 'auto';
            });

            $(document).on('click', function (event) {
                if (
                    activeCostingMenu
                    && !activeCostingMenu.menu.contains(event.target)
                    && !$(event.target).closest('.costing-menu-toggle').length
                ) {
                    closeCostingActionMenu();
                }
            });
            $(document).on('click', '.costing-actions-menu .dropdown-item', function () {
                window.setTimeout(closeCostingActionMenu, 0);
            });
            $('#table-costing_wrapper .dataTables_scrollBody').on('scroll', closeCostingActionMenu);
            table.on('preDraw.dt', closeCostingActionMenu);

            $('#table-costing').on('click', '.timeline-toggle', function (event) {
                event.preventDefault();
                event.stopPropagation();
                const row = table.row($(this).closest('tr'));
                const $icon = $(this).find('i');

                if (row.child.isShown()) {
                    row.child.hide();
                    $(this).attr('aria-expanded', 'false');
                    $icon.removeClass('fa-chevron-down').addClass('fa-chevron-right');
                    return;
                }

                row.child('<div class="costing-timeline text-muted"><i class="fas fa-spinner fa-spin mr-2"></i>Memuat riwayat...</div>', 'costing-timeline-row').show();
                $(this).attr('aria-expanded', 'true');
                $icon.removeClass('fa-chevron-right').addClass('fa-chevron-down');

                const timelineUrl = "{{ route('costing-timeline', ':id') }}".replace(':id', row.data().id);
                $.getJSON(timelineUrl)
                    .done(function (response) {
                        if (row.child.isShown()) {
                            row.child(renderCostingTimeline(response.data || [], response.costing || {}), 'costing-timeline-row').show();
                        }
                    })
                    .fail(function (xhr) {
                        if (!row.child.isShown()) return;
                        const message = xhr.responseJSON && xhr.responseJSON.message
                            ? xhr.responseJSON.message
                            : 'Riwayat Costing gagal dimuat.';
                        row.child(`<div class="costing-timeline text-danger">${escapeCostingHtml(message)}</div>`, 'costing-timeline-row').show();
                    });
            });


            // Drag Drop Column Order
            $('#column-order')
                .on('dragstart', '.column-order-item', function (event) {
                    event.originalEvent.dataTransfer.setData('text/plain', String($(this).data('column')));
                    event.originalEvent.dataTransfer.effectAllowed = 'move';
                })
                .on('dragover', '.column-order-item', function (event) {
                    event.preventDefault();
                    event.originalEvent.dataTransfer.dropEffect = 'move';
                })
                .on('drop', '.column-order-item', function (event) {
                    event.preventDefault();
                    const sourceIndex = pendingColumnOrder.indexOf(Number(event.originalEvent.dataTransfer.getData('text/plain')));
                    const targetIndex = pendingColumnOrder.indexOf(Number($(this).data('column')));
                    if (sourceIndex < 0 || targetIndex < 0 || sourceIndex === targetIndex) return;
                    const [movedColumn] = pendingColumnOrder.splice(sourceIndex, 1);
                    const adjustedTargetIndex = pendingColumnOrder.indexOf(Number($(this).data('column')));
                    pendingColumnOrder.splice(adjustedTargetIndex, 0, movedColumn);
                    renderColumnOrder();
                });

            $('#column-toggles').on('change', 'input', function () {
                $(this).closest('.column-toggle').toggleClass('active', this.checked);
            });

            // Update Selected Count Badge
            // Pesan redirect (mis. costing terkunci saat membuka Edit) tidak ditampilkan layout, jadi tampilkan di sini.
            @if (session('warning') || session('error'))
                Swal.fire({ icon: @json(session('error') ? 'error' : 'warning'), title: 'Halaman belum bisa dibuka', text: @json(session('warning') ?? session('error')) });
            @elseif (session('success'))
                // Mis. setelah Save and lock Costing: dikunci dan menunggu approval.
                mktToast('ok', @json(session('success_title', 'Costing menunggu approval')), @json(session('success')));
            @endif

            // Jumlah per status ikut dikirim server (mengikuti filter tanggal/buyer).
            table.on('xhr.dt', function(event, settings, json) {
                const counts = (json && json.status_counts) || {};
                Object.keys(COSTING_STATUS).forEach(function(status) {
                    $(`.costing-status-count[data-count="${status}"]`).text(Number(counts[status] || 0).toLocaleString('id-ID'));
                });
            });

            // Status tab sebagai filter; klik tab yang aktif lagi untuk menampilkan semua.
            $(document).on('click', '.costing-status-tab', function() {
                const status = $(this).data('status');
                activeApprovalStatus = activeApprovalStatus === status ? '' : status;
                $('.costing-status-tab').removeClass('active')
                    .filter(`[data-status="${activeApprovalStatus}"]`).addClass('active');
                table.ajax.reload();
            });

            // SO yang WS-nya sudah dipakai Marker tidak bisa diedit dari Marketing.
            $(document).on('click', '.so-marker-locked', function() {
                Swal.fire({ icon: 'warning', title: 'Sales Order sudah ada di Marker', text: $(this).data('message'), confirmButtonText: 'Tutup' });
            });

            $(document).on('click', '.marketing-request-unlock', function() {
                const $btn = $(this);
                openUnlockRequest({
                    type: $btn.data('type'),
                    id: $btn.data('id'),
                    number: $btn.data('number'),
                    buyer: $btn.data('buyer'),
                    onSuccess: function(res) {
                        mktToast('ok', 'Permintaan unlock terkirim', 'Permintaan menunggu keputusan Marketing Manager di Approval Center.');
                        table.ajax.reload(null, false);
                    }
                });
            });

            // BOM dengan beberapa WS terkunci: pilih WS yang mau di-unlock, lalu dialog alasan yang sama.
            $(document).on('click', '.bom-unlock-pick', function() {
                const list = $(this).data('list') || [];
                const buyer = $(this).data('buyer');
                const options = {};
                list.forEach(b => { options[b.id] = `${b.no_ws} (${b.bom_no})`; });
                Swal.fire({
                    title: 'Request Unlock BOM',
                    text: 'Pilih worksheet yang BOM-nya mau diubah.',
                    input: 'select',
                    inputOptions: options,
                    inputPlaceholder: 'Pilih worksheet',
                    showCancelButton: true,
                    confirmButtonText: 'Lanjut',
                    cancelButtonText: 'Cancel',
                    inputValidator: value => value ? undefined : 'Pilih worksheet dulu',
                }).then(res => {
                    if (!res.isConfirmed) return;
                    const bom = list.find(b => String(b.id) === String(res.value));
                    openUnlockRequest({
                        type: 'bom',
                        id: bom.id,
                        number: `${bom.bom_no} · ${bom.no_ws}`,
                        buyer: buyer,
                        onSuccess: function() {
                            mktToast('ok', 'Permintaan unlock terkirim', 'Permintaan menunggu keputusan Marketing Manager di Approval Center.');
                            table.ajax.reload(null, false);
                        }
                    });
                });
            });

            $(document).on('change', '.costing-check, #check-all-costing', function() {
                let count = $('.costing-check:checked').length;
                $('#selected-count').text(count);
            });

            $('#btn-batch-print').on('click', function () {
                // Satu baris = satu SO. Costing dengan beberapa SO muncul di beberapa baris: id Costing cukup sekali.
                const $checked = $('.costing-check:checked');
                if (!$checked.length) {
                    mktToast('info', 'Belum ada costing yang dipilih', 'Centang minimal satu costing dulu, baru klik Batch Print.');
                    return;
                }
                const uniq = list => [...new Set(list.filter(Boolean))];
                const docs = {
                    costing: { label: 'Costing', ids: uniq($checked.map(function () { return this.value; }).get()),
                        one: "{{ route('print-costing-pdf', ':id') }}", batch: "{{ route('print-costing-batch-pdf') }}" },
                    so: { label: 'Sales Order', ids: uniq($checked.map(function () { return this.dataset.so; }).get()),
                        one: "{{ route('so-nds-pdf', ':id') }}", batch: "{{ route('so-nds-pdf-batch') }}" },
                    bom: { label: 'BOM', ids: uniq($checked.filter(function () { return this.dataset.bom === '1'; }).map(function () { return this.dataset.so; }).get()),
                        one: "{{ route('bom-nds-pdf', ':id') }}", batch: "{{ route('bom-nds-pdf-batch') }}" },
                };
                const radio = (name, value, text, checked, disabled) => `<label class="${disabled ? 'is-disabled' : ''}"><input type="radio" name="${name}" value="${value}"${checked ? ' checked' : ''}${disabled ? ' disabled' : ''}><span>${text}</span></label>`;
                const docOptions = Object.entries(docs).map(([key, d], i) => radio('bp-doc', key,
                    `${d.label} <small>${d.ids.length ? d.ids.length + ' dokumen' : (key === 'so' ? 'belum ada SO' : 'belum ada BOM')}</small>`, i === 0, !d.ids.length)).join('');

                Swal.fire({
                    title: 'Batch Print',
                    html: `<div class="bp-group-title">Dokumen</div><div class="swal2-radio bp-docs">${docOptions}</div>
                        <div class="bp-group-title">Format PDF</div><div class="swal2-radio">${radio('bp-mode', 'merge', 'Gabungkan menjadi satu PDF', true)}${radio('bp-mode', 'separate', 'Buka PDF masing-masing')}</div>`,
                    width: 540,
                    customClass: { popup: 'batch-print-modal' },
                    showCancelButton: true,
                    confirmButtonText: 'Print',
                    cancelButtonText: 'Cancel',
                    preConfirm: () => {
                        const popup = Swal.getPopup();
                        const doc = popup.querySelector('input[name="bp-doc"]:checked');
                        const mode = popup.querySelector('input[name="bp-mode"]:checked');
                        if (!doc || !mode) {
                            Swal.showValidationMessage('Pilih dokumen dan format PDF dulu.');
                            return false;
                        }
                        return { doc: doc.value, mode: mode.value };
                    }
                }).then(result => {
                    if (!result.isConfirmed) return;
                    const d = docs[result.value.doc];
                    const skipped = $checked.length - $checked.filter(function () {
                        return result.value.doc === 'costing' || (result.value.doc === 'so' ? this.dataset.so : this.dataset.so && this.dataset.bom === '1');
                    }).length;
                    if (skipped) mktToast('info', `${skipped} baris dilewati`, `Baris itu belum punya ${d.label}.`);

                    if (result.value.mode === 'merge') {
                        const url = new URL(d.batch, window.location.origin);
                        d.ids.forEach(id => url.searchParams.append('ids[]', id));
                        window.open(url.toString(), '_blank');
                        return;
                    }

                    const openedWindows = [];
                    for (const id of d.ids) {
                        const printWindow = window.open(d.one.replace(':id', id), '_blank');
                        if (!printWindow) {
                            openedWindows.forEach(openedWindow => openedWindow.close());
                            Swal.fire({ icon: 'warning', title: 'Pop-up diblokir browser', text: 'PDF tidak bisa dibuka di tab baru. Izinkan pop-up untuk situs ini, lalu klik Batch Print lagi.' });
                            return;
                        }
                        openedWindows.push(printWindow);
                    }
                });
            });

            function updateFilterSummary() {
                const $filterLabel =$('#filter-label').empty();
                let filterCount = 0;
                const searchValue = $('#costing-search').val().trim();

                function addFilterChip(label, filter) {
                    const $chip =$('<span>').addClass('filter-chip').text(label);
                    const $remove =$('<button>')
                        .attr({ type: 'button', 'aria-label': `Remove ${filter} filter` })
                        .addClass('filter-chip-remove')
                        .data('filter', filter)
                        .html('&times;');
                    $chip.append($remove);
                    $filterLabel.append($chip);
                    filterCount++;
                }

                if (searchValue) addFilterChip(`Search: ${searchValue}`, 'search');
                if (selectedBuyers.length) {
                    addFilterChip(`Buyer: ${selectedBuyers.length} selected`, 'buyer');
                }
                if (dateFilterApplied) {
                    addFilterChip(`Date: ${$('#menu-tgl-awal').val()} to ${$('#menu-tgl-akhir').val()}`, 'date');
                }

                if (!filterCount) $filterLabel.text('None');$('#clear-filters').prop('disabled', filterCount === 0);
            }

            function updateBuyerAllState() {
                const $visibleBuyers =$('.buyer-filter:visible');
                const checkedCount = $visibleBuyers.filter(':checked').length;
                const allChecked = $visibleBuyers.length > 0 && checkedCount ===$visibleBuyers.length;
                $('#buyer-all').prop('checked', allChecked)
                    .prop('indeterminate', checkedCount > 0 && !allChecked);
            }

            $('#costing-search').on('input', function () {
                const value = this.value;
                const column = activeSearchColumn;
                table.search('');
                if (column !== '') table.columns().search('').column(column).search(value).draw();
                else table.columns().search('').search(value).draw();
                updateFilterSummary();
            });

            $('#search-by').on('click', function (e) {
                e.stopPropagation();
                $('#search-menu').toggleClass('show');
            });

            $('#search-menu > button').on('click', function () {
                const type = $(this).data('filter');
                activeSearchColumn = $(this).data('column') !== undefined ? String($(this).data('column')) : '';$('#search-menu > button').removeClass('active'); $(this).addClass('active');$('#date-submenu').toggleClass('show', type === 'date');
                $('#buyer-submenu').toggleClass('show', type === 'buyer');

                if (!type) {
                    $('#search-menu').removeClass('show');
                    $('#costing-search').attr('placeholder', `Search ${$(this).text().trim()}`).trigger('focus').trigger('input');
                }
            });

            $(document).on('click', function (e) {
                if (!$(e.target).closest('.list-toolbar').length) {$('#search-menu').removeClass('show');
                    $('.search-submenu').removeClass('show');
                }
            });

            $('#buyer-search').on('input', function () {
                const query = this.value.trim().toLocaleLowerCase();
                $('#buyer-options .buyer-option').each(function () {
                    $(this).toggle($(this).text().trim().toLocaleLowerCase().includes(query));
                });
                updateBuyerAllState();
            });

            $('#buyer-all').on('change', function () {
                $('.buyer-filter:visible').prop('checked', this.checked);
                updateBuyerAllState();
            });

            $('#buyer-options').on('change', '.buyer-filter', updateBuyerAllState);

            $('#apply-buyer-filter').on('click', function () {
                selectedBuyers = $('.buyer-filter:checked').map((i, el) => el.value).get();
                updateFilterSummary();
                table.ajax.reload();
                $('#search-menu').removeClass('show');
                $('#buyer-submenu').removeClass('show');
            });

            $('#apply-date-filter').on('click', function () {
                dateFilterApplied = true;
                updateFilterSummary();
                table.ajax.reload();
                $('#search-menu').removeClass('show');
                $('#date-submenu').removeClass('show');
            });

            $('#filter-label').on('click', '.filter-chip-remove', function () {
                const filter = $(this).data('filter');

                if (filter === 'search') {
                    $('#costing-search').val('');
                    table.search('').columns().search('').draw();
                } else if (filter === 'buyer') {
                    selectedBuyers = [];
                    $('.buyer-filter, #buyer-all').prop('checked', false).prop('indeterminate', false);
                    table.ajax.reload();
                } else if (filter === 'date') {
                    dateFilterApplied = false;
                    $('#menu-tgl-awal').val(defaultCostingStartDate);
                    $('#menu-tgl-akhir').val(defaultCostingEndDate);
                    table.ajax.reload();
                }

                updateFilterSummary();
            });

            $('#clear-filters').on('click', function () {
                selectedBuyers = [];
                dateFilterApplied = false;
                activeSearchColumn = '';
                $('.buyer-filter, #buyer-all').prop('checked', false).prop('indeterminate', false);
                $('#buyer-search').val('');
                $('#buyer-options .buyer-option').show();
                $('#menu-tgl-awal').val(defaultCostingStartDate);
                $('#menu-tgl-akhir').val(defaultCostingEndDate);
                $('#costing-search').val('').attr('placeholder', 'Search');
                $('#search-menu > button').removeClass('active');
                $('#date-submenu, #buyer-submenu').removeClass('show');
                table.search('').columns().search('');
                updateFilterSummary();
                table.ajax.reload();
            });

            $('#check-all-costing').on('change', function () {
                $('.costing-check').prop('checked', this.checked).trigger('change');
            });

            $('#reset-settings').on('click', function () {
                $('#column-toggles input').each(function () {
                    $(this).prop('checked', !COSTING_HIDDEN_BY_DEFAULT.includes(Number(this.dataset.column))).trigger('change');
                });
                pendingColumnOrder = defaultColumnOrder.slice();
                renderColumnOrder();
                $('.amount-format').removeClass('active').filter('[data-format="full"]').addClass('active');
                $('.date-format').removeClass('active').filter('[data-format="DD/MM/YYYY"]').addClass('active');
                amountFormat = 'full';
                dateFormat = 'DD/MM/YYYY';
                $('#sticky-header').prop('checked', false);
            });

            $('.amount-format').on('click', function () {
                $('.amount-format').removeClass('active');$(this).addClass('active');
                amountFormat = $(this).data('format');
            });

            $('.date-format').on('click', function () {
                $('.date-format').removeClass('active');$(this).addClass('active');
                dateFormat = $(this).data('format');
            });

            $('#apply-settings').on('click', function () {
                appliedColumnOrder = pendingColumnOrder.slice();
                fitCostingColumns();
                $('#table-costing_wrapper .dataTables_scrollHead').toggleClass('sticky-top', $('#sticky-header').is(':checked'));
                table.draw(false);
                closeTableSettings();
            });
        });


        function closeCostingActionMenu() {
            if (!activeCostingMenu) return;

            const { menu, placeholder, button } = activeCostingMenu;
            menu.classList.remove('show');
            menu.style.top = '';
            menu.style.left = '';
            menu.style.right = '';
            menu.style.bottom = '';
            if (placeholder.parentNode) {
                placeholder.parentNode.insertBefore(menu, placeholder);
                placeholder.remove();
            }
            if (button) button.setAttribute('aria-expanded', 'false');
            activeCostingMenu = null;
        }

        function escapeCostingHtml(value) {
            return $('<div>').text(value == null || value === '' ? '-' : String(value)).html();
        }

        function renderTimelineStep(label, isComplete, date, user) {
            const detail = isComplete
                ? `<span class="costing-timeline-date">${escapeCostingHtml(date || 'Selesai')}</span>${user ? `<span class="costing-timeline-user">${escapeCostingHtml(user)}</span>` : ''}`
                : '<span class="costing-timeline-date">Belum dibuat</span>';

            return `<div class="costing-timeline-step ${isComplete ? 'is-complete' : ''}">
                <span class="costing-timeline-dot"></span>
                <span class="costing-timeline-label">${escapeCostingHtml(label)}</span>
                ${detail}
            </div>`;
        }

        function renderCostingTimeline(events, costing) {
            // Timeline tampil berbahasa Indonesia. Kalimat di process_logs tetap disimpan apa adanya (sebagian berbahasa Inggris,
            // dipakai juga untuk menggabungkan log item beruntun), lalu diterjemahkan di sini supaya log lama & baru seragam.
            const MONTHS = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            const EN_MONTHS = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            const pad = n => String(n).padStart(2, '0');
            const formatDateTime = (val) => {
                if (!val) return '';
                const d = new Date(String(val).replace(' ', 'T'));
                if (Number.isNaN(d.getTime())) return escapeCostingHtml(val);
                return `${d.getDate()} ${MONTHS[d.getMonth()]} ${d.getFullYear()} ${pad(d.getHours())}:${pad(d.getMinutes())}`;
            };
            // "13 Nov 2026 @ 9:41 AM" (format di kalimat log) -> "13 Nov 2026 09:41".
            const formatStamp = (stamp) => {
                const m = String(stamp).match(/^(\d{1,2}) ([A-Z][a-z]{2}) (\d{4}) @ (\d{1,2}):(\d{2}) ([AP]M)$/);
                if (!m) return stamp;
                const hours = (Number(m[4]) % 12) + (m[6] === 'PM' ? 12 : 0);
                const month = EN_MONTHS.indexOf(m[2]);
                return `${m[1]} ${month >= 0 ? MONTHS[month] : m[2]} ${m[3]} ${pad(hours)}:${m[5]}`;
            };
            const dateHtml = text => text ? `<span class="timeline-v-date"> pada ${escapeCostingHtml(text)}</span>` : '';

            const costNo = escapeCostingHtml(costing.cost_no || 'Costing');
            const hasSalesOrder = events.some(event => String(event.type).toLowerCase() === 'so');
            const hasCostingReady = events.some(event => String(event.type).toLowerCase() === 'costing' && String(event.action).toLowerCase() === 'ready');
            const approved = Boolean(costing.approved);

            // Histori format baru (kalimat lengkap + "on 23 Oct 2026 @ 2:05 PM", lihat MarketingHistoryService).
            const NEW_FORMAT_DATE = / on (\d{1,2} [A-Z][a-z]{2} \d{4} @ \d{1,2}:\d{2} [AP]M)\.?$/;
            const APPROVED_NOTE = 'This request has been approved.';
            const APPROVED_NOTE_ID = 'Permintaan ini sudah disetujui.';
            const badge = status => status === 'Approved'
                ? '<span class="timeline-v-badge badge-approved"><i class="fas fa-circle"></i>Approved</span>'
                : '<span class="timeline-v-badge badge-awaiting"><i class="fas fa-square"></i>Awaiting Approval</span>';
            const noteHtml = text => `<div class="timeline-v-note"><i class="far fa-comment-alt"></i><span>${escapeCostingHtml(text)}</span></div>`;
            const docLabel = no => /^BOM\//i.test(no) ? 'BOM' : (/^SO\//i.test(no) ? 'Sales Order' : (/^CST\//i.test(no) ? 'Costing' : null));
            const items_ = (n, noun) => `${n} item ${noun}`;
            // Kalimat Inggris (sesudah nama pelaku dibuang) -> Indonesia. Nomor & status dirender belakangan.
            const PHRASES = [
                [/^created costing$/, () => 'membuat Costing'],
                [/^updated costing$/, () => 'mengubah Costing'],
                [/^changed status from Awaiting Approval to Approved$/, () => 'mengubah status dari Awaiting Approval ke Approved'],
                [/^added (\d+) (costing|BOM) items?(?: in (.+))?$/, (m, n, noun, cats) => `menambah ${items_(n, noun === 'costing' ? 'Costing' : 'BOM')}${cats ? ' di ' + cats : ''}`],
                [/^deleted (\d+) (costing|BOM) items?(?: in (.+))?$/, (m, n, noun, cats) => `menghapus ${items_(n, noun === 'costing' ? 'Costing' : 'BOM')}${cats ? ' di ' + cats : ''}`],
                [/^updated (costing|BOM) items?(?: in (.+?))? \((\d+) changes?\)$/, (m, noun, cats, n) => `mengubah item ${noun === 'costing' ? 'Costing' : 'BOM'}${cats ? ' di ' + cats : ''} (${n} perubahan)`],
                [/^created sales order(?: (\S+))?$/i, (m, no) => `membuat Sales Order${no ? ' ' + no : ''}`],
                [/^saved sales order (\S+) details$/i, (m, no) => `menyimpan detail Sales Order ${no}`],
                [/^updated sales order (\S+)(?: \((.+)\))?$/i, (m, no, parts) => `mengubah Sales Order ${no}${parts ? ' (' + parts
                    .replace(/details? updated/g, 'detail diubah').replace(/details? added/g, 'detail ditambah')
                    .replace(/details? deleted/g, 'detail dihapus').replace(/details? restored/g, 'detail dikembalikan') + ')' : ''}`],
                [/^created BOM$/, () => 'membuat BOM'],
                [/^(created|updated) BOM (\S+) for worksheet (.+)$/, (m, verb, no, ws) => `${verb === 'created' ? 'membuat' : 'mengubah'} BOM ${no} untuk WS ${ws}`],
                [/^Request unlock: ?(.*)$/i, (m, reason) => `mengajukan Request Unlock${reason ? ': ' + reason : ''}`],
                [/^(Costing|BOM|Sales Order) (\S+) di-unlock \((.+)\)\.?$/, (m, label, no, until) => `meng-unlock ${label} ${no} (${until})`],
                // Kalimat tanpa pelaku. "Sales Order BOM/..." = label salah di log lama approval BOM.
                [/^(?:Costing|Sales Order|BOM) (\S+) is Approved automatically by the system and ready to be created as a BOM$/, (m, no) => `Sales Order ${no} otomatis Approved oleh sistem dan siap dibuat BOM`],
                [/^(?:Costing|Sales Order|BOM) (\S+) is Approved(?: and ready to be created as an? (.+))?$/, (m, no, next) => {
                    const label = docLabel(no) || 'Dokumen';
                    if (label === 'BOM') return `BOM ${no} sudah Approved`;
                    return `${label} ${no} Approved${next ? ' dan siap dibuat ' + next : ''}`;
                }],
            ];
            const translate = text => {
                for (const [re, fn] of PHRASES) {
                    const m = text.match(re);
                    if (m) return { text: fn(...m), done: true };
                }
                return { text, done: false };
            };
            // Escape lalu tebalkan nomor dokumen dan ganti status dengan badge.
            const decorate = text => escapeCostingHtml(text)
                .replace(/\b((?:CST|SO|BOM)\/\d{4}\/\d+)\b/g, '<span class="timeline-v-costno">$1</span>')
                .replace(/Awaiting Approval|Approved/g, badge);

            function renderNewFormat(event) {
                let text = String(event.desc || '');
                const hasNote = text.endsWith(' ' + APPROVED_NOTE);
                if (hasNote) text = text.slice(0, -(APPROVED_NOTE.length + 1)).replace(/\.$/, '');
                const match = text.match(NEW_FORMAT_DATE);
                const body = match ? text.slice(0, match.index) : text;
                // Pelaku ditulis tebal. Histori lama masih berbunyi "Marketing Staff/Manager", diganti dengan user yang mencatatnya.
                const actor = event.user || '';
                const actorPrefix = ['Marketing Staff ', 'Marketing Manager ', actor ? actor + ' ' : null].find(prefix => prefix && body.startsWith(prefix));
                const rest = actorPrefix ? body.slice(actorPrefix.length) : body;
                let html = (actorPrefix ? `<b>${escapeCostingHtml(actor || actorPrefix.trim())}</b> ` : '') + decorate(translate(rest).text);
                if (match) html += dateHtml(formatStamp(match[1]));
                if (hasNote) html += noteHtml(APPROVED_NOTE_ID);
                return html;
            }
            const newFormatIcons = {
                created: ['fa-plus', 'is-created'],
                approved: ['fa-clipboard-check', 'is-approved'],
                ready: ['fa-check', ''],
                updated: ['fa-pen', ''],
                items_added: ['fa-plus', ''],
                items_updated: ['fa-pen', ''],
                items_deleted: ['fa-trash', '']
            };

            const items = events.map(event => {
                const action = String(event.action || '').toLowerCase();
                const type = String(event.type || '').toLowerCase();
                if (NEW_FORMAT_DATE.test(String(event.desc || '').replace(' ' + APPROVED_NOTE, ''))) {
                    const [newIcon, newIconClass] = newFormatIcons[action] || ['fa-clock', ''];
                    return `<div class="timeline-v-item">
                        <div class="timeline-v-icon ${newIconClass}"><i class="fas ${newIcon}"></i></div>
                        <div class="timeline-v-content">${renderNewFormat(event)}</div>
                    </div>`;
                }
                const username = event.user || '';
                const user = username ? `<b>${escapeCostingHtml(username)}</b> ` : '';
                const desc = String(event.desc || '');
                const date = formatDateTime(event.created_at);
                let icon = 'fa-clock';
                let iconClass = '';
                let description;
                let note = '';

                if (action === 'created' && type !== 'so') {
                    icon = 'fa-plus';
                    iconClass = 'is-created';
                    description = `${user}membuat Costing <span class="timeline-v-costno">${costNo}</span>`;
                } else if (action === 'approved') {
                    icon = 'fa-clipboard-check';
                    iconClass = 'is-approved';
                    description = `${user}mengubah status dari ${badge('Awaiting Approval')} ke ${badge('Approved')}`;
                    note = noteHtml(APPROVED_NOTE_ID);
                } else if (action === 'return' || action === 'cancel') {
                    // Catatan Return to Staff / Cancel berisi alasan dari approver.
                    icon = action === 'return' ? 'fa-undo' : 'fa-ban';
                    description = `${user}mengubah status ke <b>${action === 'return' ? 'Return to Staff' : 'Cancel'}</b>`;
                    note = desc ? noteHtml(desc) : '';
                } else if (action === 'copied') {
                    icon = 'fa-copy';
                    const sourceMatch = desc.match(/(?:dibuat|disalin) dari\s+(.+?)[.!]?$/i);
                    const source = sourceMatch ? ` dari <span class="timeline-v-costno">${escapeCostingHtml(sourceMatch[1])}</span>` : '';
                    description = `${user}menyalin Costing <span class="timeline-v-costno">${costNo}</span>${source}`;
                } else if (action === 'updated' && type === 'costing') {
                    icon = 'fa-pen';
                    description = `${user}mengubah Costing <span class="timeline-v-costno">${costNo}</span>`;
                } else if (type === 'so' && /dibuat untuk (?:Purchase Order|PO)/i.test(desc)) {
                    icon = 'fa-file-alt';
                    const soMatch = desc.match(/Sales Order\s+(.+?)\s+dibuat untuk (?:Purchase Order|PO)\s+(.+?)[.!]?$/i);
                    description = `${user}membuat Sales Order <span class="timeline-v-costno">${escapeCostingHtml(soMatch[1])}</span> untuk PO <span class="timeline-v-costno">${escapeCostingHtml(soMatch[2])}</span>`;
                } else {
                    const actionIcons = {
                        updated: 'fa-pen',
                        item_added: 'fa-plus',
                        item_updated: 'fa-pen',
                        item_deleted: 'fa-trash',
                        unlock_requested: 'fa-unlock-alt',
                        unlock_cancelled: 'fa-times-circle',
                        unlocked: 'fa-unlock',
                        relocked: 'fa-lock',
                        resubmitted: 'fa-paper-plane'
                    };
                    icon = actionIcons[action] || (type === 'so' ? 'fa-file-alt' : icon);
                    const translated = translate(desc.replace(/\.$/, ''));
                    // Kalimat yang sudah lengkap (diawali nama dokumen, "Masa unlock", "Edit ...") tidak diberi nama pelaku di depan.
                    const standalone = !translated.done && /^(Costing|BOM|Sales Order|Request unlock|Masa unlock|Edit )/.test(desc);
                    description = desc
                        ? (standalone ? decorate(desc.replace(/\.$/, '')) : `${user}${decorate(translated.text)}`)
                        : `${user}${escapeCostingHtml(event.action || 'mengubah Costing')}`;
                }

                const dateText = dateHtml(date);

                return `<div class="timeline-v-item">
                    <div class="timeline-v-icon ${iconClass}"><i class="fas ${icon}"></i></div>
                    <div class="timeline-v-content">${description}${dateText}${note}</div>
                </div>`;
            });

            if (approved && !hasSalesOrder && !hasCostingReady) {
                const readyDate = formatDateTime(costing.approved_at);
                items.push(`<div class="timeline-v-item">
                    <div class="timeline-v-icon"><i class="fas fa-plus"></i></div>
                    <div class="timeline-v-content">Costing <span class="timeline-v-costno">${costNo}</span> ${badge('Approved')} dan siap dibuat Sales Order${dateHtml(readyDate)}</div>
                </div>`);
            } else if (!approved) {
                items.push(`<div class="timeline-v-item">
                    <div class="timeline-v-icon"><i class="fas fa-plus"></i></div>
                    <div class="timeline-v-content">Costing <span class="timeline-v-costno">${costNo}</span> masih ${badge('Awaiting Approval')}</div>
                </div>`);
            }

            return `<div class="costing-timeline"><div class="timeline-vertical">${items.join('')}</div></div>`;
        }

        function showCostingDetailTable(title, columns, rows, summary) {
            const tableRows = rows.length
                ? rows.map(row => `<tr>${columns.map(column => `<td>${escapeCostingHtml(row[column.key])}</td>`).join('')}</tr>`).join('')
                : mktEmptyRow(columns.length);
            const headers = columns.map(column => `<th>${escapeCostingHtml(column.label)}</th>`).join('');

            Swal.fire({
                title: title,
                html: `${summary || ''}<div class="table-responsive" style="max-height:60vh; overflow:auto;">
                    <table class="table table-sm table-bordered table-hover mb-0">
                        <thead class="thead-light"><tr>${headers}</tr></thead>
                        <tbody>${tableRows}</tbody>
                    </table>
                </div>`,
                width: 1100,
                showCloseButton: true,
                confirmButtonText: 'Tutup'
            });
        }

        // Isi sel teks ditampilkan Title Case (T-SHIRT LONG SLEEVE -> T-Shirt Long Sleeve; PT.EIGERINDO -> PT. Eigerindo,
        // singkatan PT/CV/LTD tetap kapital); sort/search tetap memakai data asli. Data server sudah di-escape (H&amp;M).
        function costingTitleCase(data, type) {
            if (type !== 'display' || !data) return data || '-';
            return window.mktTitleCaseHtml(data);
        }

        // Buyer, Brand, Style: tanpa huruf kapital semua (KANMAX ENTERPRISES LTD -> Kanmax Enterprises Ltd, PT X -> Pt X).
        // Kata yang mengandung angka (kode style OLD562936, HG21) dibiarkan apa adanya. Sort/search tetap memakai data asli.
        function costingPlainCase(data, type) {
            if (type !== 'display' || !data) return data || '-';
            const box = document.createElement('textarea');
            box.innerHTML = String(data);
            const text = box.value.split(/(\s+)/).map(word => /\d/.test(word) ? word
                : word.toLowerCase().replace(/(^|[\-\/(&.,])([a-z])/g, (m, sep, ch) => sep + ch.toUpperCase())).join('');
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Tabel detail bergaya tabel marketing (header biru muda, isi Inter 14, angka rata kanan id-ID, pager).
        // columns: { key, label, num?: jumlah desimal maksimum (angka), wide?: kolom teks panjang yang boleh turun baris }
        function showCostingStyledTable(title, columns, rows) {
            const fmt = (value, decimals) => {
                const n = Number(value);
                if (value === null || value === undefined || value === '' || !Number.isFinite(n)) return '-';
                return n.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: decimals });
            };
            const cell = (row, column) => column.num !== undefined
                ? `<td class="is-num">${fmt(row[column.key], column.num)}</td>`
                : `<td class="${column.wide ? 'is-wide' : ''}">${escapeCostingHtml(row[column.key] === null || row[column.key] === undefined || row[column.key] === '' ? '-' : row[column.key])}</td>`;
            const headers = '<th class="is-no">No</th>' + columns.map(column => `<th class="${column.num !== undefined ? 'is-num' : ''}">${escapeCostingHtml(column.label)}</th>`).join('');
            let page = 1;

            function render(popup) {
                const perPage = Number(popup.querySelector('.mkt-dt-per-page').value) || 10;
                const pageCount = Math.max(1, Math.ceil(rows.length / perPage));
                page = Math.min(Math.max(1, page), pageCount);
                const start = (page - 1) * perPage;
                popup.querySelector('tbody').innerHTML = rows.length
                    ? rows.slice(start, start + perPage).map((row, i) => `<tr><td class="is-no">${start + i + 1}</td>${columns.map(column => cell(row, column)).join('')}</tr>`).join('')
                    : mktEmptyRow(columns.length + 1);
                popup.querySelector('.mkt-dt-page-count').textContent = 'Of ' + pageCount;
                const pages = [];
                for (let p = 1; p <= pageCount; p++) {
                    if (p <= 3 || p > pageCount - 3 || Math.abs(p - page) <= 1) pages.push(p);
                    else if (pages[pages.length - 1] !== '…') pages.push('…');
                }
                popup.querySelector('.mkt-dt-pages').innerHTML =
                    `<button type="button" data-page="${page - 1}" ${page === 1 ? 'disabled' : ''} aria-label="Sebelumnya"><i class="fas fa-chevron-left"></i></button>`
                    + pages.map(p => p === '…' ? '<span>…</span>' : `<button type="button" data-page="${p}" class="${p === page ? 'active' : ''}">${p}</button>`).join('')
                    + `<button type="button" data-page="${page + 1}" ${page === pageCount ? 'disabled' : ''} aria-label="Berikutnya"><i class="fas fa-chevron-right"></i></button>`;
            }

            Swal.fire({
                title: title,
                html: `<div class="mkt-dt-card">
                    <div class="mkt-dt-scroll">
                        <table class="mkt-dt-table">
                            <thead><tr>${headers}</tr></thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    <div class="mkt-dt-pager">
                        <div>
                            <span>Item Per Page</span>
                            <select class="mkt-dt-per-page" aria-label="Item per page">
                                <option value="10">10</option><option value="25">25</option><option value="50">50</option><option value="100">100</option>
                            </select>
                            <span class="mkt-dt-page-count">Of 1</span>
                        </div>
                        <div class="mkt-dt-pages"></div>
                    </div>
                </div>`,
                width: 1400,
                showCloseButton: true,
                confirmButtonText: 'Tutup',
                customClass: { popup: 'mkt-dt-modal', title: 'mkt-dt-title', htmlContainer: 'mkt-dt-body', actions: 'mkt-dt-actions' },
                didOpen: popup => {
                    render(popup);
                    popup.querySelector('.mkt-dt-per-page').addEventListener('change', () => { page = 1; render(popup); });
                    popup.querySelector('.mkt-dt-pages').addEventListener('click', event => {
                        const button = event.target.closest('button[data-page]');
                        if (!button || button.disabled) return;
                        page = Number(button.dataset.page);
                        render(popup);
                    });
                }
            });
        }

        function showCostingSoMaterial(id) {
            const url = "{{ route('get-detail-material-so', ':id') }}".replace(':id', id);
            $.getJSON(url).done(function (response) {
                showCostingStyledTable('Detail Material SO', [
                    { key: 'panel', label: 'Panel/Content', wide: true },
                    { key: 'item_desc', label: 'Item', wide: true },
                    { key: 'color_gmt', label: 'Color' },
                    { key: 'size_gmt', label: 'Size' },
                    { key: 'product_set', label: 'Product Set' },
                    { key: 'qty_gmt', label: 'Qty SO', num: 0 },
                    { key: 'cons', label: 'Cons', num: 4 },
                    { key: 'qty_bom', label: 'Qty Material', num: 2 },
                    { key: 'unit', label: 'Unit' }
                ], response.data || []);
            }).fail(function (xhr) {
                const message = xhr.responseJSON && xhr.responseJSON.message;
                Swal.fire({ icon: 'error', title: 'Material SO belum bisa dimuat', text: message || 'Data material SO tidak terambil dari server. Coba buka lagi, hubungi IT bila masih gagal.' });
            });
        }

        function showCostingBomDetail(id) {
            const url = "{{ route('show-detail-bom', ':id') }}".replace(':id', id);
            $.ajax({ url: url, type: 'GET', dataType: 'json', data: { start: 0, length: -1 } }).done(function (response) {
                showCostingDetailTable('Detail BOM', [
                    { key: 'content_name', label: 'Content' },
                    { key: 'item_name', label: 'Item Description' },
                    { key: 'color_name', label: 'Color' },
                    { key: 'size_name', label: 'Size' },
                    { key: 'qty', label: 'Cons' },
                    { key: 'unit_name', label: 'Unit' }
                ], response.data || []);
            }).fail(function (xhr) {
                const message = xhr.responseJSON && xhr.responseJSON.message;
                Swal.fire({ icon: 'error', title: 'Detail BOM belum bisa dimuat', text: message || 'Data detail BOM tidak terambil dari server. Coba buka lagi, hubungi IT bila masih gagal.' });
            });
        }

        function renderColumnOrder() {
            if (!table) return;
            const items = pendingColumnOrder.map(index => {
                const title = $(table.column(index).header()).text().trim() || 'Checkbox';
                return `<span class="column-order-item" draggable="true" data-column="${index}"><i class="fas fa-grip-lines mr-2 text-muted"></i>${title}</span>`;
            });
            $('#column-order').html(items.join(''));
        }

        // Lebar tabel (px) di bawah batas ini -> kolom disembunyikan otomatis, supaya tidak perlu scroll ke samping.
        // Index kolom: 0 Checkbox, 1 Timeline, 2 No, 3 Costing Date, 4 No Costing, 5 No SO, 6 No Worksheet, 7 Buyer, 8 Brand,
        // 9 Style, 10 Market, 11 Qty, 12 Confirm Price, 13 Product Group, 14 Product Item, 15 Action.
        // Disembunyikan sejak awal (Market selalu kosong, Product Group sudah tersirat dari Product Item); bisa dicentang lagi di Table Settings.
        const COSTING_HIDDEN_BY_DEFAULT = [10, 13];

        const COSTING_AUTO_HIDE = [
            { below: 1600, columns: [10, 13] },
            { below: 1450, columns: [8, 14] },
            { below: 1100, columns: [3, 12] },
            { below: 860, columns: [2, 6, 11] },
            { below: 576, columns: [9] },
            { below: 360, columns: [1] },
            { below: 260, columns: [0] }
        ];

        // Kolom tampil = dicentang di Table Settings dan tidak kena sembunyi otomatis.
        // Di bawah lebar ini (tablet/HP) tabel tidak dipaksa muat: semua kolom tampil dan bisa di-scroll ke kanan.
        const COSTING_SCROLL_BELOW = 1024;

        function fitCostingColumns() {
            if (!table) return;
            const scrollMode = window.innerWidth < COSTING_SCROLL_BELOW;
            $('#table-costing-wrap').toggleClass('is-scroll', scrollMode);
            const width = $('#table-costing-wrap').width();
            const autoHidden = scrollMode ? new Set() : new Set(COSTING_AUTO_HIDE.filter(rule => width < rule.below).flatMap(rule => rule.columns));
            table.columns().every(function (index) {
                const checked = $(`#column-toggles input[data-column="${index}"]`).prop('checked') !== false;
                const show = checked && !autoHidden.has(index);
                if (this.visible() !== show) this.visible(show, false);
            });
            table.columns.adjust();
            applyColumnOrder();
            alignCostingHeader();
        }

        // Header (tabel terpisah karena scrollY) disisakan selebar scrollbar vertikal isi tabel supaya kolom tetap sejajar.
        function alignCostingHeader() {
            const body = $('#table-costing_wrapper .dataTables_scrollBody')[0];
            if (!body) return;
            $('#table-costing_wrapper .dataTables_scrollHead').css('padding-right', body.offsetWidth - body.clientWidth);
        }

        function applyColumnOrder() {
            if (!table || !appliedColumnOrder.length) return;

            table.columns().every(function (index) {
                $(this.nodes()).addClass(`costing-col-${index}`);
                $(this.header()).addClass(`costing-col-${index}`);
            });

            $('#table-costing_wrapper thead tr, #table-costing tbody tr').each(function () {
                const row = this;
                appliedColumnOrder.forEach(index => {
                    const cell = Array.from(row.cells).find(item => item.classList.contains(`costing-col-${index}`));
                    if (cell) row.appendChild(cell);
                });
            });
        }

        function openTableSettings(event) {
            const buttonRect = event.currentTarget.getBoundingClientRect();
            const $settings =$('#tableSettings');
            pendingColumnOrder = appliedColumnOrder.slice();
            renderColumnOrder();
            $settings.addClass('show').attr('aria-hidden', 'false');

            const popoverWidth = $settings.outerWidth();
            const left = Math.max(8, Math.min(buttonRect.left, window.innerWidth - popoverWidth - 8));
            const top = buttonRect.bottom + 8;
            const maxHeight = Math.max(120, window.innerHeight - top - 8);

            $settings.css({ top: `${top}px`, left: `${left}px`, maxHeight: `${maxHeight}px` });
            $('#settings-backdrop').addClass('show');
        }

        function closeTableSettings() {
            $('#tableSettings, #settings-backdrop').removeClass('show');
            $('#tableSettings').attr('aria-hidden', 'true');
        }

        // Costing / BOM Approved (terkunci): Request Unlock, atau "Menunggu Unlock" bila sudah diajukan.
        // sub: keterangan kecil di bawah label (mis. No WS untuk BOM per WS).
        function unlockMenuItem(type, id, number, buyer, pending, sub = '') {
            const attr = value => $('<div>').text(value || '').html().replace(/"/g, '&quot;');
            const small = sub ? `<small>${attr(sub)}</small>` : '';
            if (Number(pending)) {
                return `<button type="button" class="dropdown-item mkt-fab is-edit" disabled><span class="mkt-fab-icon"><i class="fas fa-hourglass-half"></i></span><span class="mkt-fab-label">Menunggu Unlock${small}</span></button>`;
            }
            return `<button type="button" class="dropdown-item mkt-fab is-edit marketing-request-unlock" data-type="${type}" data-id="${id}" data-number="${attr(number)}" data-buyer="${attr(buyer)}"><span class="mkt-fab-icon"><i class="fas fa-lock-open"></i></span><span class="mkt-fab-label">Request Unlock${small}</span></button>`;
        }


        // Harga CMT dibaca saat diklik, bukan saat tabel dimuat. Kalau pengecekan gagal, halaman SO tetap memeriksanya di server.
        $(document).on('click', '.so-cmt-check', function(event) {
            event.preventDefault();
            const target = this.href;
            const cmtUrl = this.dataset.cmtUrl;
            $.get(this.dataset.cmtCheck).done(function(res) {
                if (res.issues.length) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Harga CMT belum lengkap',
                        showCancelButton: true,
                        focusConfirm: true,
                        confirmButtonText: 'Add CMT Price',
                        cancelButtonText: 'Cancel',
                        html: `${res.issues.map(escapeCostingHtml).join('<br>')}<br>Lengkapi harga CMT semua product item dulu, baru Sales Order bisa dibuat.`,
                    }).then(result => {
                        if (result.isConfirmed) goWithLoading(cmtUrl, "Membuka Costing...");
                    });
                } else {
                    goWithLoading(target, "Membuka Sales Order...");
                }
            }).fail(function() {
                goWithLoading(target, "Membuka Sales Order...");
            });
        });

        // Pindah halaman sambil menampilkan loading, supaya user tahu halaman sedang dibuka (bukan error).
        function goWithLoading(url, title) {
            Swal.fire({ title: title || 'Memuat...', text: 'Mohon tunggu sebentar.', allowOutsideClick: false, allowEscapeKey: false, showConfirmButton: false, didOpen: () => Swal.showLoading() });
            window.location.href = url;
        }
        // Kembali lewat tombol Back (halaman dari cache browser): tutup loading yang tertinggal.
        window.addEventListener('pageshow', e => { if (e.persisted && Swal.isVisible()) Swal.close(); });

        // Add SO: tampilkan ringkasan Costing (header, content, cons, charge, SO yang sudah ada) supaya user yakin Costing-nya benar.
        $(document).on('click', '.so-add-preview', function(event) {
            event.preventDefault();
            const target = this.href;
            const cmtUrl = this.dataset.cmtUrl;
            const esc = escapeCostingHtml;
            const num = v => Number(v || 0).toLocaleString('id-ID', { maximumFractionDigits: 4 });
            Swal.fire({ title: 'Memuat data Costing...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            $.get(this.dataset.preview).done(function(res) {
                if (res.status !== 200) {
                    Swal.fire({ icon: 'error', title: 'Data costing belum bisa dimuat', text: res.message || 'Costing tidak ditemukan. Muat ulang halaman lalu coba lagi.' });
                    return;
                }
                const d = res.data, c = d.costing;
                const info = [
                    ['No Costing', c.costing_no], ['Buyer', c.buyer], ['Brand', c.brand], ['Style', c.style],
                    ['Product Item', (d.sets || []).join(', ')], ['Qty', num(c.planned_qty)], ['FOB', `${c.currency_code || ''} ${num(c.fob_price)}`],
                ].map(([k, v]) => `<div><span>${k}</span><b>${esc(v || '-')}</b></div>`).join('');
                const matRows = d.materials.length
                    ? d.materials.map((m, i) => {
                        const first = i === 0 || d.materials[i - 1].content !== m.content;
                        return `<tr class="${first ? 'g' : ''}"><td>${first ? esc(m.content) : ''}</td><td>${esc(m.set)}</td><td>${esc(m.shell || '-')}</td><td>${esc(m.part || '-')}</td><td class="n">${num(m.cons)}</td><td>${esc(m.unit || '-')}</td><td class="n">${num(m.loss)}%</td></tr>`;
                    }).join('')
                    : '<tr><td colspan="7" class="e">Belum ada material</td></tr>';
                const chgRows = d.charges.length
                    ? d.charges.map(m => `<tr><td>${esc(m.content)}</td><td>${esc(m.set)}</td><td class="n">${num(m.price)}</td></tr>`).join('')
                    : '<tr><td colspan="3" class="e">Belum ada charge</td></tr>';
                // Banner gaya katalog alert: ikon bulat gradien + judul + pesan (pesan sudah berupa HTML aman).
                const banner = (kind, title, messageHtml) => `<div class="sp-banner${kind === 'warn' ? ' is-warn' : ''}"><span class="sp-banner-ic">${kind === 'warn'
                    ? '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5.5v8"/><path d="M12 17.8v.01"/></svg>'
                    : '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 11v7"/><path d="M12 6.5v.01"/></svg>'}</span><div><b>${esc(title)}</b><span>${messageHtml}</span></div></div>`;
                const so01 = d.orders.length ? mktAlertText('SO-01', { count: d.orders.length, next: d.orders.length + 1, costing: c.costing_no }) : null;
                const orders = so01 ? banner('info', so01.title, esc(so01.message)) : '';
                const cmt = d.cmt_issues.length
                    ? banner('warn', 'Harga CMT belum lengkap', `${d.cmt_issues.map(esc).join('<br>')}<br>Lengkapi harga CMT dulu, baru SO bisa dibuat.`)
                    : '';
                Swal.fire({
                    customClass: { popup: 'so-preview-modal' },
                    showCancelButton: true,
                    confirmButtonText: d.cmt_issues.length ? 'Add CMT Price' : 'Create SO',
                    cancelButtonText: 'Cancel',
                    title: 'Create SO dari costing ini?',
                    html: `
                        ${orders}${cmt}
                        <div class="sp-info">${info}</div>
                        <div class="sp-cols">
                            <div><div class="sp-sub">Material</div>
                            <div class="sp-scroll"><table class="sp-table"><thead><tr><th>Content</th><th>Set</th><th>Shell</th><th>Part</th><th class="n">Cons</th><th>Unit</th><th class="n">Loss</th></tr></thead><tbody>${matRows}</tbody></table></div></div>
                            <div><div class="sp-sub">Manufacturing &amp; Other Cost</div>
                            <div class="sp-scroll"><table class="sp-table"><thead><tr><th>Content</th><th>Set</th><th class="n">Price</th></tr></thead><tbody>${chgRows}</tbody></table></div></div>
                        </div>`,
                }).then(result => {
                    if (result.isConfirmed) goWithLoading(d.cmt_issues.length ? cmtUrl : target, d.cmt_issues.length ? "Membuka Costing..." : "Membuka Create SO...");
                });
            }).fail(function() {
                Swal.fire({ icon: 'error', title: 'Data costing belum bisa dimuat', text: 'Ringkasan costing tidak terambil dari server. Klik Add SO lagi, hubungi IT bila masih gagal.' });
            });
        });

        function confirmCopy(btn) {
            const d = Object.fromEntries(Object.entries(btn.dataset).map(([k, v]) => [k, k === 'url' ? v : escapeCostingHtml(v)]));
            Swal.fire({
                icon: 'info',
                showCancelButton: true,
                focusConfirm: true,
                confirmButtonText: 'Copy Costing',
                cancelButtonText: 'Cancel',
                title: `Copy costing ${d.no || ''}?`,
                html: `
                    <p class="mkt-plain-desc">Buyer : ${d.buyer || '-'}<br>Style : ${d.style || '-'}</p>
                    <div class="mkt-plain-list">
                        <div><b>Ikut dicopy</b><ul><li>Header costing</li><li>Set &amp; material</li><li>Biaya lain (charges)</li><li>Gambar costing</li></ul></div>
                        <div><b>Tidak ikut</b><ul><li>Sales Order</li><li>BOM</li></ul></div>
                    </div>
                    <p>Costing baru mendapat nomor baru dan approval dimulai dari awal.</p>`,
            }).then((result) => {
                if (!result.isConfirmed) return;
                Swal.fire({
                    title: `Menyalin ${d.no || 'costing'}...`,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => Swal.showLoading(),
                });

                window.location.href = d.url;
            });
        }
    </script>
@endsection
