@extends('layouts.index')

@section('custom-link')
    <link rel="stylesheet" href="{{ asset('dist/css/marketing-buttons.css') }}?v={{ @filemtime(public_path('assets/dist/css/marketing-buttons.css')) }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">

    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <style>
        :root {
            --sb-navy: #0f172a;
            --sb-blue: #3085d6;
            --sb-blue-dark: #1e3a8a;
            --sb-border: #e2e8f0;
        }

        /* ============ COSTING LIST ============ */
        .costing-page { padding: 10px 14px 28px; background: #f8fafc; min-height: calc(100vh - 56px); }
        .costing-crumb { background: #1e293b; color: #cbd5e1; padding: 9px 12px; font-size: 12px; letter-spacing: .2px; border-radius: 4px; }
        .costing-crumb b { color: #fff; }
        .costing-back { display: inline-flex; align-items: center; gap: 10px; margin: 12px 0 16px; color: #334155; font-size: 14px; font-weight: 700; text-decoration: none; }
        .costing-back i { background: #e2e8f0; border-radius: 5px; padding: 4px 6px; font-size: 11px; }

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
        .filter-chip { display: inline-flex; align-items: center; padding: 4px 10px; background: #e0f2fe; color: #0369a1; border-radius: 6px; font-weight: 500; font-size: 11px; }
        .filter-chip-remove { border: 0; background: transparent; color: inherit; font-size: 14px; line-height: 1; padding: 0 0 0 6px; cursor: pointer; }
        .filter-chip-remove:hover { color: #b91c1c; }

        .selected-wrapper { font-size: 12px; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 8px; margin-bottom: 10px; margin-top: 5px; }
        .selected-badge { background: #e2e8f0; color: #475569; padding: 2px 10px; border-radius: 12px; font-size: 11px; }

        /* ============ TABLE ============ */
        .costing-table { border-collapse: collapse; margin-top: 5px !important; }
        .costing-table th, .costing-table td { border-top: none; border-left: none; border-right: none; border-bottom: 1px solid #f1f5f9 !important; padding: 12px 10px; vertical-align: middle; }
        .costing-table thead tr { background: #f8fafc; border-bottom: 2px solid #e2e8f0; }
        .costing-table thead th { color: #475569 !important; font-family: Inter, sans-serif; font-size: 12px !important; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase; border-bottom: none !important; }
        .costing-table tbody td { font-family: Inter, sans-serif; font-size: 14px !important; color: #334155; }
        .costing-table tbody tr:hover { background-color: #f8fafc; }
        /* Tabel selalu muat lebar layar: teks boleh turun baris, kolom kurang penting disembunyikan otomatis (fitCostingColumns). */
        .costing-table-wrap, #table-costing_wrapper { width: 100%; }
        #table-costing_wrapper .dataTables_scrollBody { overflow-x: hidden !important; }
        #table-costing_wrapper .dataTables_scrollHeadInner, #table-costing_wrapper .dataTables_scrollHeadInner > table, #table-costing { width: 100% !important; }
        .costing-table td { white-space: normal; overflow-wrap: break-word; }
        .costing-table td.costing-nowrap { white-space: nowrap; }
        .costing-table thead th { white-space: normal; }
        .search-by { white-space: nowrap; }
        @media (max-width: 767.98px) {
            .list-shell > .d-flex:first-child { flex-wrap: wrap; gap: 8px; }
            .list-toolbar { flex-wrap: wrap; }
            .list-toolbar .input-group { flex: 1 1 160px; width: auto !important; }
            .costing-table .costing-actions { min-width: 0; flex-wrap: wrap; justify-content: flex-start; gap: 4px; }
        }
        @media (max-width: 575.98px) {
            .costing-page { padding-left: 4px !important; padding-right: 4px !important; }
            .costing-page .card-body { padding: 14px 8px !important; }
            .costing-table th, .costing-table td { padding: 8px 4px; }
        }
        @media (max-width: 400px) {
            .costing-page .costing-table tbody td { font-size: 12px !important; }
            .costing-page .costing-table th, .costing-page .costing-table td { padding: 6px 2px; }
        }
        @media (max-width: 340px) {
            .costing-page .costing-table tbody td { font-size: 11px !important; }
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

        .date-badge { background-color: #e2e8f0; color: #475569; padding: 4px 12px; border-radius: 12px; font-weight: 600; font-size: 10px; display: inline-block; }

        /* ============ ACTION BUTTONS & BOTTOM BAR ============ */
        /* Bulat 28x28: penuh = sudah dibuat, cincin = langkah berikutnya (Create ...), abu = belum bisa. */
        .action-round { --ar-c: #0ea5e9; --ar-ring: #7dd3fc; position: relative; width: 28px; height: 28px; padding: 0; border-radius: 50%; display: inline-flex; justify-content: center; align-items: center; background: var(--ar-c); color: #fff !important; font-size: 12px; text-decoration: none !important; border: 0; box-shadow: 0 3px 6px rgba(15, 23, 42, .18); transition: transform .15s ease, box-shadow .15s ease; cursor: pointer; }
        .action-round i { font-size: 12px !important; }
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
        /* Floating menu: daftar ke bawah, tiap baris = ikon bulat 28x28 bergradien (sesuai mockup) + teks di sampingnya.
           Warna ikon: is-edit biru muda, is-delete merah, is-copy kuning, is-pdf merah, is-excel hijau, is-download ungu, is-detail biru tua.
           Hover/klik baris = gradien ikon lebih terang. */
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
        .costing-actions-menu .mkt-fab:disabled { background: transparent; color: #a0a7b4; cursor: not-allowed; }
        .costing-actions-menu .mkt-fab:disabled .mkt-fab-icon { background: #eef0f5; border-color: #eef0f5; color: #c4c9d4; }

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
        .table-settings-popover { display: none; position: fixed; z-index: 1050; top: 0; left: 0; width: 500px; max-width: calc(100vw - 32px); background: #fff; border-radius: 8px; box-shadow: 0 10px 40px rgba(15, 23, 42, .2); overflow-y: auto; border: 1px solid #e2e8f0; }
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
        .swal2-popup.batch-print-modal { width: min(480px, calc(100vw - 32px)) !important; padding: 24px; border-radius: 12px; }
        .batch-print-modal .swal2-title { margin: 0 0 8px; color: #284a63; font-size: 22px; }
        .batch-print-modal .swal2-html-container { margin: 0 0 18px; color: #64748b; font-size: 14px; line-height: 1.5; }
        .batch-print-modal .swal2-radio { display: flex; width: 100%; flex-direction: column; align-items: stretch; gap: 8px; margin: 1em 0 3px; }
        .batch-print-modal .swal2-radio label { display: flex; align-items: center; gap: 8px; width: 100%; margin: 0; padding: 10px 12px; border: 1px solid #dce4ec; border-radius: 8px; color: #334155; font-size: 14px; line-height: 1.4; text-align: left; }
        .batch-print-modal .swal2-radio label:has(input:checked) { border-color: #284a63; background: #f1f6fa; }
        .batch-print-modal .swal2-radio input { margin: 0; }
        .batch-print-modal .swal2-actions { gap: 8px; margin-top: 20px; }
        .batch-print-modal .swal2-styled { min-width: 88px; border-radius: 20px; font-size: 13px; }
        @media (max-width: 575px) {
        }
        /* Checkbox sesuai mockup: 16x16, radius 4. Kosong = putih border abu (hover: centang samar),
           checked/indeterminate = #284A63 + ikon putih, focus = cincin biru muda. */
        input[type="checkbox"]:not(.custom-control-input) { -webkit-appearance: none; appearance: none; flex: 0 0 16px; width: 16px; height: 16px; padding: 0; border: 1.5px solid #cfd4dc; border-radius: 4px; background: #fff no-repeat center / 10px 10px; vertical-align: middle; cursor: pointer; transition: background-color .15s ease, border-color .15s ease, box-shadow .15s ease; }
        input[type="checkbox"]:not(.custom-control-input):hover:not(:checked):not(:indeterminate):not(:disabled) { background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12'%3e%3cpath fill='none' stroke='%23cfd4dc' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' d='M2.5 6.2l2.3 2.3 4.7-4.9'/%3e%3c/svg%3e"); }
        input[type="checkbox"]:not(.custom-control-input):checked { border-color: #284a63; background-color: #284a63; background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12'%3e%3cpath fill='none' stroke='%23fff' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' d='M2.5 6.2l2.3 2.3 4.7-4.9'/%3e%3c/svg%3e"); }
        input[type="checkbox"]:not(.custom-control-input):indeterminate { border-color: #284a63; background-color: #284a63; background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12'%3e%3cpath fill='none' stroke='%23fff' stroke-width='2' stroke-linecap='round' d='M3 6h6'/%3e%3c/svg%3e"); }
        input[type="checkbox"]:not(.custom-control-input):focus { outline: 0; }
        input[type="checkbox"]:not(.custom-control-input):focus-visible { box-shadow: 0 0 0 4px #b3daf7; }
        input[type="checkbox"]:not(.custom-control-input):disabled { opacity: .5; cursor: not-allowed; }

    </style>
@endsection

@section('content')
    <div class="costing-page">
        {{-- <a href="javascript:history.back()" class="costing-back"><i class="fas fa-chevron-left"></i> Back</a> --}}

        <div class="card card-sb">
            <div class="card-body">
                <div class="costing-crumb">Marketing &nbsp;›&nbsp; Proses &nbsp;›&nbsp; <b>Costing</b></div>
                <h1 class="costing-title mt-3">Marketing List</h1>

                <div class="list-shell">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <div class="list-toolbar">
                            <button type="button" class="toolbar-btn" onclick="openTableSettings(event)" title="Atur kolom tabel"><i class="fas fa-table"></i></button>
                            <button type="button" id="search-by" class="search-by"><i class="fas fa-filter mr-1"></i> Search By <i class="fas fa-chevron-down ml-1"></i></button>

                            <div class="input-group" style="width: 250px;">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-white" style="height: 32px; border: 1px solid #e2e8f0; border-right: none; border-radius: 6px 0 0 6px; padding: 0 10px;">
                                        <i class="fas fa-search text-muted" style="font-size: 11px;"></i>
                                    </span>
                                </div>
                                <input id="costing-search" class="form-control toolbar-search m-0" style="border-left: none; border-radius: 0 6px 6px 0; padding-left: 0; box-shadow: none;" placeholder="Search">
                            </div>

                            <div id="search-menu" class="search-menu">
                                <button type="button" data-filter="date">Due Date <i class="fas fa-chevron-right float-right mt-1"></i></button>
                                <button type="button" data-column="3">No Costing</button>
                                <button type="button" data-filter="buyer">Buyer <i class="fas fa-chevron-right float-right mt-1"></i></button>
                                <button type="button" data-column="7">Brand</button>
                                <button type="button" data-column="8">Style</button>
                                <button type="button" data-column="9">Market</button>
                                <div id="date-submenu" class="search-submenu">
                                    <b class="d-block mb-1 text-dark">Due Date</b>
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

                        <button type="button" class="mkt-btn mkt-btn--navy mkt-btn--sm" id="btn-batch-print"><i class="fas fa-print mr-1"></i> Print</button>
                    </div>

                    <div class="filter-summary">
                        <span>Filter:</span>
                        <span id="filter-label">None</span>
                        <button type="button" id="clear-filters" class="mkt-btn mkt-btn--red mkt-btn--sm" disabled>Clear all</button>
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
                        <button type="button" class="costing-status-tab" data-status="unlocked" title="Dibuka manager untuk diedit peminta; Save and lock mengunci lagi dan tetap Approved">
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
                                    <th>No Costing</th>
                                    <th>Due Date</th>
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
                    <div class="d-flex justify-content-end mt-4">
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
            <button type="button" class="settings-choice amount-format active" data-format="full">10,500</button>

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
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        let table;
        let activeSearchColumn = '';
        let selectedBuyers = [];
        let dateFilterApplied = false;
        const defaultCostingStartDate = '{{ now()->subMonths(3)->toDateString() }}';
        const defaultCostingEndDate = '{{ now()->toDateString() }}';
        const defaultColumnOrder = Array.from({ length: 15 }, (_, index) => index);
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
            unlocked: { label: 'Unlocked', desc: 'dibuka untuk diedit peminta, Save and lock mengunci lagi (tetap Approved)' }
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
                pageLength: 10,
                lengthMenu: [[10, 15, 25, 50], [10, 15, 25, 50]],
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
                        d.tgl_awal = $('#menu-tgl-awal').val();
                        d.tgl_akhir = $('#menu-tgl-akhir').val();
                        d.buyers = selectedBuyers;
                        d.approval_status = activeApprovalStatus;
                    }
                },
                columns: [
                    { data: 'id', orderable: false, searchable: false, className: 'text-center align-middle', render: data => `<input type="checkbox" class="costing-check" value="${data}">` },
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
                        className: 'text-center align-middle'
                    },
                    { data: 'cost_no', name: 'a.cost_no', className: 'text-center align-middle font-weight-bold text-dark costing-nowrap' },
                    {
                        data: 'due_date',
                        name: 'a.deldate',
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
                    { data: 'kpno', name: 'a.kpno', className: 'text-center align-middle costing-nowrap', render: data => data || '-' },
                    { data: 'nama_buyer', name: 'b.Supplier', className: 'align-middle' },
                    { data: 'brand', name: 'a.brand', className: 'align-middle' },
                    { data: 'styleno', name: 'a.styleno', className: 'align-middle' },
                    { data: 'market', orderable: false, searchable: false, className: 'align-middle' },
                    {
                        data: 'qty',
                        name: 'a.qty',
                        className: 'text-center align-middle costing-nowrap',
                        render: function (data, type) {
                            if (type !== 'display' || data === null || data === '') return data;
                            const value = Number(data);
                            if (!Number.isFinite(value)) return data;
                            return amountFormat === 'compact'
                                ? new Intl.NumberFormat('en-US', { notation: 'compact', maximumFractionDigits: 1 }).format(value).replace(/K$/, 'k')
                                : new Intl.NumberFormat('en-US', { maximumFractionDigits: 2 }).format(value);
                        }
                    },
                    {
                        data: 'cfm_price',
                        name: 'a.cfm_price',
                        className: 'text-center align-middle costing-nowrap',
                        render: function (data, type, row) {
                            if (data !== null && data !== '') {
                                return parseFloat(data).toFixed(6);
                            }
                            return '0';
                        }
                    },
                    { data: 'product_group_text', name: 'p.product_group', className: 'text-center align-middle' },
                    { data: 'product_item_text', name: 'p.product_item', className: 'text-center align-middle' },
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

                            const soCreateUrl = "{{ route('create-so') }}?id_costing=" + encodeURIComponent(data);
                            const bomCreateUrl = "{{ route('create-bom') }}?id_costing=" + encodeURIComponent(data) + "&id_so=" + encodeURIComponent(row.so_id);

                            const soDetailUrl = hasSo ? "{{ route('so-detail-page', ':id') }}".replace(':id', row.so_id) : '';
                            const soPdfUrl = hasSo ? "{{ route('print-pdf-so', ':id') }}".replace(':id', row.so_id) : '';
                            const soExcelUrl = hasSo ? "{{ route('export-excel-so', ':id') }}".replace(':id', row.so_id) : '';
                            const bomPdfUrl = hasBom ? "{{ route('print-bom-pdf', ':id') }}".replace(':id', row.bom_id) : '';
                            const bomExcelUrl = hasBom ? "{{ route('export-excel-bom') }}?id=" + encodeURIComponent(row.bom_id) : '';

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
                                        <button type="button" class="dropdown-item mkt-fab is-copy" onclick="confirmCopy('${copyUrl}')"><span class="mkt-fab-icon"><i class="far fa-copy"></i></span>Copy</button>
                                        <a class="dropdown-item mkt-fab is-pdf" href="${pdfUrl}" target="_blank"><span class="mkt-fab-icon"><i class="fas fa-file-pdf"></i></span>PDF</a>
                                        <a class="dropdown-item mkt-fab is-excel" href="${excelUrl}"><span class="mkt-fab-icon"><i class="fas fa-file-excel"></i></span>Excel</a>
                                    </div>
                                </div>`;

                            let soMenu = '';
                            if (hasSo) {
                                soMenu = `
                                <div class="dropdown d-inline-block">
                                    <button class="action-round is-so costing-menu-toggle" data-menu-id="so-menu-${row.so_id}" aria-expanded="false" type="button" title="SO Menu">
                                        ${AR_SO_ICON}
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right costing-actions-menu" id="so-menu-${row.so_id}">
                                        <a class="dropdown-item mkt-fab is-detail" href="${soDetailUrl}"><span class="mkt-fab-icon"><i class="fas fa-info-circle"></i></span>Detail</a>
                                        <button type="button" class="dropdown-item mkt-fab is-detail" onclick="showCostingSoMaterial(${row.so_id})"><span class="mkt-fab-icon"><i class="fas fa-boxes"></i></span>Detail Material</button>
                                        <a class="dropdown-item mkt-fab is-pdf" href="${soPdfUrl}" target="_blank"><span class="mkt-fab-icon"><i class="fas fa-file-pdf"></i></span>PDF</a>
                                        <a class="dropdown-item mkt-fab is-excel" href="${soExcelUrl}"><span class="mkt-fab-icon"><i class="fas fa-file-excel"></i></span>Excel</a>
                                    </div>
                                </div>`;
                            } else if (approved) {
                                soMenu = `<a href="${soCreateUrl}" class="action-round is-so is-next" data-tip="Create SO" aria-label="Create SO">${AR_SO_ICON}</a>`;
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
                                        <a class="dropdown-item mkt-fab is-excel" href="${bomExcelUrl}"><span class="mkt-fab-icon"><i class="fas fa-file-excel"></i></span>Excel</a>
                                    </div>
                                </div>`;
                            } else if (canCreateBom) {
                                bomMenu = `<a href="${bomCreateUrl}" class="action-round is-bom is-next" data-tip="Create BOM" aria-label="Create BOM"><i class="fas fa-layer-group"></i></a>`;
                            } else {
                                bomMenu = `<button class="action-round is-bom is-off" disabled title="Belum bisa membuat BOM"><i class="fas fa-layer-group"></i></button>`;
                            }

                            let extraBtn = `<button type="button" class="action-round is-ws is-off" disabled><i class="fa-solid fa-rectangle-list"></i></button>`;

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
                        $('#column-toggles').append(`<label class="btn btn-sm btn-outline-secondary column-toggle active"><input type="checkbox" checked data-column="${i}"> ${title}</label>`);
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
                Swal.fire('Perhatian', @json(session('warning') ?? session('error')), 'warning');
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

            $(document).on('click', '.marketing-request-unlock', function() {
                const $btn = $(this);
                openUnlockRequest({
                    type: $btn.data('type'),
                    id: $btn.data('id'),
                    number: $btn.data('number'),
                    buyer: $btn.data('buyer'),
                    onSuccess: function(res) {
                        Swal.fire({ icon: 'success', title: res.message, timer: 2000, showConfirmButton: false });
                        table.ajax.reload(null, false);
                    }
                });
            });

            $(document).on('change', '.costing-check, #check-all-costing', function() {
                let count = $('.costing-check:checked').length;
                $('#selected-count').text(count);
            });

            $('#btn-batch-print').on('click', function () {
                const selectedIds = $('.costing-check:checked').map(function () {
                    return this.value;
                }).get();
                if (!selectedIds.length) {
                    Swal.fire('Pilih Costing', 'Centang minimal satu Costing untuk dicetak.', 'warning');
                    return;
                }

                Swal.fire({
                    title: 'Batch Print',
                    text: `${selectedIds.length} Costing dipilih. Pilih format PDF:`,
                    input: 'radio',
                    width: 480,
                    customClass: { popup: 'batch-print-modal' },
                    inputOptions: {
                        merge: 'Gabungkan menjadi satu PDF',
                        separate: 'Buka PDF masing-masing'
                    },
                    inputValue: 'merge',
                    inputValidator: value => value ? undefined : 'Pilih format PDF terlebih dahulu.',
                    showCancelButton: true,
                    confirmButtonText: 'Print',
                    cancelButtonText: 'Batal'
                }).then(result => {
                    if (!result.isConfirmed) return;

                    if (result.value === 'merge') {
                        const url = new URL("{{ route('print-costing-batch-pdf') }}", window.location.origin);
                        selectedIds.forEach(id => url.searchParams.append('ids[]', id));
                        window.open(url.toString(), '_blank');
                        return;
                    }

                    const openedWindows = [];
                    for (const id of selectedIds) {
                        const pdfUrl = "{{ route('print-costing-pdf', ':id') }}".replace(':id', id);
                        const printWindow = window.open(pdfUrl, '_blank');
                        if (!printWindow) {
                            openedWindows.forEach(openedWindow => openedWindow.close());
                            Swal.fire('Popup diblokir', 'Izinkan pop-up untuk situs ini, lalu coba batch print lagi.', 'warning');
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
                $('#column-toggles input').prop('checked', true).trigger('change');
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
            const formatDateTime = (val) => {
                if (!val) return '';
                const d = new Date(String(val).replace(' ', 'T'));
                if (Number.isNaN(d.getTime())) return escapeCostingHtml(val);
                const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                let hours = d.getHours();
                const ampm = hours >= 12 ? 'PM' : 'AM';
                hours = hours % 12 || 12;
                const mins = d.getMinutes().toString().padStart(2, '0');
                return `${d.getDate()} ${months[d.getMonth()]} ${d.getFullYear()} @ ${hours}:${mins} ${ampm}`;
            };

            const costNo = escapeCostingHtml(costing.cost_no || 'Costing');
            const hasSalesOrder = events.some(event => String(event.type).toLowerCase() === 'so');
            const approved = Boolean(costing.approved);
            const items = events.map(event => {
                const action = String(event.action || '').toLowerCase();
                const type = String(event.type || '').toLowerCase();
                const username = event.user || 'Marketing Staff';
                const user = `<b>${escapeCostingHtml(username)}</b>`;
                const date = formatDateTime(event.created_at);
                let icon = 'fa-clock';
                let iconClass = '';
                let description;

                if (action === 'created' && type !== 'so') {
                    icon = 'fa-plus';
                    iconClass = 'is-created';
                    description = `${user} created costing <span class="timeline-v-costno">${costNo}</span>`;
                } else if (action === 'approved') {
                    icon = 'fa-clipboard-check';
                    iconClass = 'is-approved';
                    description = `${user} changed status from <span class="timeline-v-badge badge-awaiting"><i class="fas fa-square"></i>Awaiting Approval</span> to <span class="timeline-v-badge badge-approved"><i class="fas fa-circle"></i>Approved</span>`;
                } else if (action === 'copied') {
                    icon = 'fa-copy';
                    const sourceMatch = String(event.desc || '').match(/(?:dibuat|disalin) dari\s+(.+?)[.!]?$/i);
                    const source = sourceMatch ? ` from <span class="timeline-v-costno">${escapeCostingHtml(sourceMatch[1])}</span>` : '';
                    description = `${user} copied costing <span class="timeline-v-costno">${costNo}</span>${source}`;
                } else if (action === 'updated' && type === 'costing') {
                    icon = 'fa-pen';
                    description = `${user} updated costing <span class="timeline-v-costno">${costNo}</span>`;
                } else if (type === 'so') {
                    icon = 'fa-file-alt';
                    const soMatch = String(event.desc || '').match(/Sales Order\s+(.+?)\s+dibuat untuk (?:Purchase Order|PO)\s+(.+?)[.!]?$/i);
                    description = soMatch
                        ? `${user} created Sales Order <span class="timeline-v-costno">${escapeCostingHtml(soMatch[1])}</span> for Purchase Order <span class="timeline-v-costno">${escapeCostingHtml(soMatch[2])}</span>`
                        : `${user} ${escapeCostingHtml(event.desc || 'created a Sales Order.')}`;
                } else {
                    const actionIcons = {
                        updated: 'fa-pen',
                        item_added: 'fa-plus',
                        item_updated: 'fa-pen',
                        item_deleted: 'fa-trash'
                    };
                    icon = actionIcons[action] || icon;
                    description = `${user} ${escapeCostingHtml(event.desc || event.action || 'updated costing')}`;
                }

                const dateText = date ? `<span class="timeline-v-date"> on ${escapeCostingHtml(date)}</span>` : '';
                const note = action === 'approved'
                    ? '<div class="timeline-v-note"><i class="far fa-comment-alt"></i><span>This request has been approved.</span></div>'
                    : '';

                return `<div class="timeline-v-item">
                    <div class="timeline-v-icon ${iconClass}"><i class="fas ${icon}"></i></div>
                    <div class="timeline-v-content">${description}${dateText}${note}</div>
                </div>`;
            });

            if (approved && !hasSalesOrder) {
                const readyDate = formatDateTime(costing.approved_at);
                const readyDateText = readyDate ? `<span class="timeline-v-date"> on ${escapeCostingHtml(readyDate)}</span>` : '';
                items.push(`<div class="timeline-v-item">
                    <div class="timeline-v-icon"><i class="fas fa-plus"></i></div>
                    <div class="timeline-v-content">Costing <span class="timeline-v-costno">${costNo}</span> is <span class="timeline-v-badge badge-approved"><i class="fas fa-circle"></i>Approved</span> and ready to be created as a Sales Order${readyDateText}</div>
                </div>`);
            } else if (!approved) {
                items.push(`<div class="timeline-v-item">
                    <div class="timeline-v-icon"><i class="fas fa-plus"></i></div>
                    <div class="timeline-v-content">Costing <span class="timeline-v-costno">${costNo}</span> is <span class="timeline-v-badge badge-awaiting"><i class="fas fa-square"></i>Awaiting Approval</span></div>
                </div>`);
            }

            return `<div class="costing-timeline"><div class="timeline-vertical">${items.join('')}</div></div>`;
        }

        function showCostingDetailTable(title, columns, rows, summary) {
            const tableRows = rows.length
                ? rows.map(row => `<tr>${columns.map(column => `<td>${escapeCostingHtml(row[column.key])}</td>`).join('')}</tr>`).join('')
                : `<tr><td colspan="${columns.length}" class="text-center text-muted py-4">Data tidak ditemukan.</td></tr>`;
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

        function showCostingSoMaterial(id) {
            const url = "{{ route('get-detail-material-so', ':id') }}".replace(':id', id);
            $.getJSON(url).done(function (response) {
                showCostingDetailTable('Detail Material SO', [
                    { key: 'panel', label: 'Panel/Content' },
                    { key: 'item_desc', label: 'Item' },
                    { key: 'color_gmt', label: 'Color' },
                    { key: 'size_gmt', label: 'Size' },
                    { key: 'product_set', label: 'Product Set' },
                    { key: 'qty_gmt', label: 'Qty SO' },
                    { key: 'cons', label: 'Cons' },
                    { key: 'qty_bom', label: 'Qty Material' },
                    { key: 'unit', label: 'Unit' }
                ], response.data || []);
            }).fail(function (xhr) {
                const message = xhr.responseJSON && xhr.responseJSON.message;
                Swal.fire('Gagal memuat material SO', message || 'Terjadi kesalahan saat mengambil material SO.', 'error');
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
                Swal.fire('Gagal memuat detail BOM', message || 'Terjadi kesalahan saat mengambil detail BOM.', 'error');
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
        // Index kolom: 1 Timeline, 2 No, 4 Due Date, 5 No Worksheet, 7 Brand, 8 Style, 9 Market, 10 Qty,
        // 11 Confirm Price, 12 Product Group, 13 Product Item.
        const COSTING_AUTO_HIDE = [
            { below: 1600, columns: [9, 12] },
            { below: 1450, columns: [7, 13] },
            { below: 1100, columns: [4, 11] },
            { below: 860, columns: [2, 5, 10] },
            { below: 576, columns: [8] },
            { below: 360, columns: [1] },
            { below: 260, columns: [0] }
        ];

        // Kolom tampil = dicentang di Table Settings dan tidak kena sembunyi otomatis.
        function fitCostingColumns() {
            if (!table) return;
            const width = $('#table-costing-wrap').width();
            const autoHidden = new Set(COSTING_AUTO_HIDE.filter(rule => width < rule.below).flatMap(rule => rule.columns));
            table.columns().every(function (index) {
                const checked = $(`#column-toggles input[data-column="${index}"]`).prop('checked') !== false;
                const show = checked && !autoHidden.has(index);
                if (this.visible() !== show) this.visible(show, false);
            });
            table.columns.adjust();
            applyColumnOrder();
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
        function unlockMenuItem(type, id, number, buyer, pending) {
            const attr = value => $('<div>').text(value || '').html().replace(/"/g, '&quot;');
            if (Number(pending)) {
                return '<button type="button" class="dropdown-item mkt-fab is-edit" disabled><span class="mkt-fab-icon"><i class="fas fa-hourglass-half"></i></span>Menunggu Unlock</button>';
            }
            return `<button type="button" class="dropdown-item mkt-fab is-edit marketing-request-unlock" data-type="${type}" data-id="${id}" data-number="${attr(number)}" data-buyer="${attr(buyer)}"><span class="mkt-fab-icon"><i class="fas fa-lock-open"></i></span>Request Unlock</button>`;
        }


        function confirmCopy(url) {
            Swal.fire({
                title: 'Copy Costing?',
                text: 'Data header dan semua detail akan dicopy ke nomor costing baru.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-copy"></i> Ya, Copy!',
                cancelButtonText: 'Batal',
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Sedang menyalin...',
                        text: 'Mohon tunggu sebentar.',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading(); }
                    });
                    window.location.href = url;
                }
            });
        }
    </script>
@endsection
