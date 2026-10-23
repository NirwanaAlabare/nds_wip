@extends('layouts.index', ['containerFluid' => true])

@section('custom-link')
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

    <style>
        :root {
            --sm-navy: #082149;
            --sm-navy-2: #123a78;
            --sm-navy-soft: #eef3fb;
            --sm-border: #e5e9f0;
            --sm-line: #f1f4f8;
            --sm-text: #0f172a;
            --sm-muted: #64748b;
            --sm-faint: #94a3b8;
            --sm-success: #059669;
            --sm-success-soft: #ecfdf5;
            --sm-danger: #dc2626;
            --sm-danger-soft: #fef2f2;
            --sm-amber: #d97706;
            --sm-amber-soft: #fffbeb;
            --sm-blue: #2563eb;
            --sm-blue-soft: #eff6ff;
            --sm-indigo: #4f46e5;
            --sm-teal: #0d9488;
            --sm-teal-soft: #f0fdfa;
            --sm-violet: #7c3aed;
            --sm-violet-soft: #f5f3ff;
            --sm-pink: #db2777;
            --sm-h: 40px;
            --sm-radius: 12px;
        }

        /* Header halaman */
        .sm-page-head {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: .9rem;
            margin: 1rem 0;
        }

        .sm-page-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 46px;
            height: 46px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--sm-blue), var(--sm-violet));
            color: #fff;
            font-size: 1.15rem;
            box-shadow: 0 8px 20px rgba(79, 70, 229, .35);
        }

        .sm-page-head h3 {
            margin: 0;
            font-weight: 800;
            color: var(--sm-navy);
        }

        .sm-page-head p {
            margin: 0;
            font-size: .82rem;
            color: var(--sm-muted);
        }

        /* Tab Mutasi / Riwayat */
        .sm-tabs {
            display: inline-flex;
            gap: .25rem;
            margin-bottom: 1rem;
            padding: 4px;
            border: 1px solid var(--sm-border);
            border-radius: 10px;
            background: #fff;
        }

        .sm-tab {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            padding: .45rem 1.1rem;
            border: none;
            border-radius: 7px;
            background: transparent;
            color: var(--sm-muted);
            font-size: .85rem;
            font-weight: 600;
        }

        .sm-tab:hover {
            color: var(--sm-navy);
        }

        .sm-tab.is-active {
            background: linear-gradient(120deg, var(--sm-blue), var(--sm-indigo));
            color: #fff;
            box-shadow: 0 4px 10px rgba(37, 99, 235, .3);
        }

        .sm-tab.is-active[data-tab="riwayat"] {
            background: linear-gradient(120deg, var(--sm-violet), var(--sm-pink));
            box-shadow: 0 4px 10px rgba(124, 58, 237, .3);
        }

        .sm-tab-count {
            min-width: 20px;
            padding: 0 .4rem;
            border-radius: 99px;
            background: var(--sm-navy-soft);
            color: var(--sm-navy-2);
            font-size: .7rem;
            line-height: 18px;
        }

        .sm-tab.is-active .sm-tab-count {
            background: rgba(255, 255, 255, .2);
            color: #fff;
        }

        /* Card */
        .sm-card {
            margin-bottom: 1.25rem;
            border: 1px solid var(--sm-border);
            border-radius: var(--sm-radius);
            background: #fff;
            box-shadow: 0 1px 3px rgba(15, 23, 42, .05);
            overflow: hidden;
        }

        /* Garis warna di atas card, beda per bagian */
        .sm-card.is-blue::before,
        .sm-card.is-violet::before {
            content: "";
            display: block;
            height: 4px;
            background: linear-gradient(90deg, var(--sm-blue), var(--sm-teal));
        }

        .sm-card.is-violet::before {
            background: linear-gradient(90deg, var(--sm-violet), var(--sm-pink));
        }

        .sm-card-head {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-end;
            gap: .75rem;
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--sm-border);
            background: linear-gradient(180deg, #f8fbff, #fff);
        }

        .sm-card.is-violet .sm-card-head {
            background: linear-gradient(180deg, #fbf9ff, #fff);
        }

        .sm-label i {
            margin-right: .25rem;
        }

        .sm-swatch {
            display: inline-block;
            width: 11px;
            height: 11px;
            margin-right: .35rem;
            border: 1px solid rgba(15, 23, 42, .2);
            border-radius: 99px;
            vertical-align: -1px;
        }

        .sm-card-title {
            margin: 0;
            font-size: .95rem;
            font-weight: 700;
            color: var(--sm-text);
        }

        .sm-card-sub {
            font-size: .75rem;
            color: var(--sm-faint);
        }

        .sm-label {
            display: block;
            margin-bottom: .25rem;
            font-size: .66rem;
            font-weight: 600;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: var(--sm-faint);
        }

        .sm-card .select2-container {
            width: 100% !important;
        }

        .sm-card .form-control,
        .sm-card .select2-container--bootstrap4 .select2-selection--single {
            height: var(--sm-h) !important;
            border-radius: 8px;
        }

        .sm-card .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
            line-height: calc(var(--sm-h) - 2px) !important;
        }

        .sm-search {
            position: relative;
        }

        .sm-search i {
            position: absolute;
            bottom: calc(var(--sm-h) / 2);
            left: .8rem;
            transform: translateY(50%);
            color: var(--sm-faint);
        }

        .sm-search .form-control {
            padding-left: 2.2rem;
        }

        .btn-sm-icon {
            width: var(--sm-h);
            height: var(--sm-h);
            padding: 0;
            border: 1px solid var(--sm-border);
            border-radius: 8px;
            background: #fff;
            color: var(--sm-muted);
        }

        .btn-sm-icon:hover {
            background: var(--sm-blue-soft);
            color: var(--sm-blue);
            border-color: #bfdbfe;
        }

        .sm-closed {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .35rem .8rem;
            border-radius: 99px;
            background: var(--sm-danger-soft);
            color: var(--sm-danger);
            font-size: .78rem;
            font-weight: 600;
        }

        /* Tabel */
        .sm-table-wrap {
            max-height: 62vh;
            overflow: auto;
        }

        .sm-table > thead > tr > th {
            position: sticky;
            top: 0;
            z-index: 1;
            padding: .6rem .75rem;
            border-bottom: 1px solid var(--sm-border);
            background: #fafbfd;
            color: var(--sm-faint);
            font-size: .66rem;
            font-weight: 600;
            letter-spacing: .06em;
            text-transform: uppercase;
            white-space: nowrap;
            vertical-align: middle;
        }

        .sm-table > tbody > tr > td {
            padding: .7rem .75rem;
            border-color: var(--sm-line);
            font-size: .85rem;
            vertical-align: middle;
        }

        .sm-table > thead > tr > th:first-child,
        .sm-table > tbody > tr:not(.sm-detail) > td:first-child {
            padding-left: 1.25rem;
        }

        .sm-table > thead > tr > th:last-child,
        .sm-table > tbody > tr:not(.sm-detail) > td:last-child {
            padding-right: 1.25rem;
        }

        /* Checkbox global (AdminLTE) memakai position absolute, dikembalikan ke posisi normal */
        .sm-table .form-check-input {
            position: static;
            float: none;
            width: 1.15rem;
            height: 1.15rem;
            margin: 0;
            vertical-align: middle;
            cursor: pointer;
        }

        .sm-group-row {
            cursor: pointer;
        }

        .sm-group-row > td {
            transition: background .12s ease;
        }

        .sm-group-row.is-selected > td {
            background: var(--sm-blue-soft);
        }

        .sm-group-row.is-selected > td:first-child {
            box-shadow: inset 4px 0 0 var(--sm-blue);
        }

        .sm-table .form-check-input:checked {
            background-color: var(--sm-blue);
            border-color: var(--sm-blue);
        }

        .sm-caret {
            color: var(--sm-faint);
            transition: transform .15s ease;
        }

        .sm-group-row.is-open .sm-caret {
            transform: rotate(90deg);
        }

        .sm-group-row.is-open > td {
            background: #f5f8fd;
        }

        .sm-code {
            font-weight: 700;
            color: var(--sm-navy);
            font-variant-numeric: tabular-nums;
        }

        .sm-sub {
            font-size: .72rem;
            color: var(--sm-faint);
        }

        .sm-size {
            display: inline-block;
            min-width: 38px;
            padding: .15rem .45rem;
            border: 1px solid var(--sm-border);
            border-radius: 6px;
            background: #fff;
            font-weight: 800;
            text-align: center;
        }

        .sm-faded {
            color: #cbd5e1;
        }

        .sm-wip {
            display: inline-block;
            min-width: 52px;
            padding: .2rem .6rem;
            border-radius: 8px;
            background: linear-gradient(135deg, #dbeafe, #e0e7ff);
            color: var(--sm-indigo);
            font-size: 1rem;
            font-weight: 800;
            text-align: center;
            font-variant-numeric: tabular-nums;
        }

        .sm-wip.is-minus {
            background: var(--sm-danger-soft);
            color: var(--sm-danger);
        }

        .sm-flow {
            display: flex;
            flex-wrap: wrap;
            gap: .25rem .6rem;
            margin-top: .25rem;
            font-size: .7rem;
            color: var(--sm-faint);
        }

        .sm-flow .is-plus {
            color: var(--sm-success);
            font-weight: 600;
        }

        .sm-flow .is-minus {
            color: var(--sm-danger);
            font-weight: 600;
        }

        .sm-stocker-badge {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .25rem .7rem;
            border: 1px solid #fde68a;
            border-radius: 99px;
            background: var(--sm-amber-soft);
            color: var(--sm-amber);
            font-size: .75rem;
            font-weight: 600;
            white-space: nowrap;
        }

        .sm-stocker-badge:hover {
            background: #fef3c7;
        }

        .sm-stocker-badge .fa-chevron-down {
            transition: transform .15s ease;
        }

        .sm-stocker-badge.is-open .fa-chevron-down {
            transform: rotate(180deg);
        }

        .sm-detail > td {
            padding: .25rem 1.25rem .8rem 3.2rem !important;
            background: #f5f8fd;
        }

        .sm-detail table {
            margin: 0;
            background: #fff;
            border: 1px solid var(--sm-border);
            border-radius: 8px;
            overflow: hidden;
        }

        .sm-detail th {
            padding: .4rem .7rem !important;
            background: #fafbfd;
            color: var(--sm-faint);
            font-size: .62rem;
            font-weight: 600;
            letter-spacing: .05em;
            text-transform: uppercase;
        }

        .sm-detail td {
            padding: .4rem .7rem !important;
            border-color: var(--sm-line);
            font-size: .8rem;
        }

        .sm-sisa {
            display: inline-block;
            min-width: 34px;
            padding: .05rem .45rem;
            border-radius: 99px;
            background: var(--sm-amber-soft);
            color: var(--sm-amber);
            font-weight: 700;
            text-align: center;
        }

        .sm-empty {
            padding: 3rem 1rem !important;
            text-align: center;
            color: var(--sm-muted);
        }

        .sm-empty i {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 56px;
            height: 56px;
            margin: 0 auto .75rem;
            border-radius: 99px;
            background: linear-gradient(135deg, #dbeafe, #ede9fe);
            color: var(--sm-indigo);
            font-size: 1.4rem;
        }

        .sm-empty.text-danger i {
            background: var(--sm-danger-soft);
            color: var(--sm-danger);
        }

        /* Panel pindah (seperti keranjang) */
        .sm-panel {
            position: sticky;
            top: 1rem;
            display: flex;
            flex-direction: column;
            max-height: calc(100vh - 2rem);
        }

        .sm-panel-head {
            display: flex;
            align-items: center;
            gap: .6rem;
            padding: .9rem 1.25rem;
            background: linear-gradient(120deg, var(--sm-teal), var(--sm-blue));
            color: #fff;
        }

        .sm-panel-head i {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: rgba(255, 255, 255, .2);
        }

        .sm-panel-head h6 {
            margin: 0;
            font-weight: 700;
        }

        .sm-panel-body {
            flex: 1;
            padding: 1rem 1.25rem;
            overflow: auto;
        }

        .sm-route-v {
            position: relative;
            padding: .75rem;
            border: 1px solid var(--sm-border);
            border-radius: 10px;
            background: linear-gradient(180deg, var(--sm-blue-soft), var(--sm-teal-soft));
        }

        .sm-route-from {
            display: flex;
            align-items: center;
            gap: .55rem;
            height: var(--sm-h);
            padding: 0 .8rem;
            border: 1px dashed var(--sm-border);
            border-radius: 8px;
            background: #fff;
            color: var(--sm-faint);
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sm-route-from.is-set {
            border: 1px solid #bfdbfe;
            border-left: 4px solid var(--sm-blue);
            color: var(--sm-blue);
        }

        .sm-route-v .select2-container--bootstrap4 .select2-selection--single {
            border-left: 4px solid var(--sm-teal) !important;
        }

        .sm-route-down {
            display: flex;
            justify-content: center;
            margin: .35rem 0;
            color: var(--sm-navy-2);
        }

        .sm-route-down span {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 26px;
            height: 26px;
            border-radius: 99px;
            background: linear-gradient(135deg, var(--sm-blue), var(--sm-teal));
            color: #fff;
            font-size: .75rem;
            box-shadow: 0 3px 8px rgba(13, 148, 136, .3);
        }

        .sm-section-title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin: 1.1rem 0 .5rem;
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .05em;
            text-transform: uppercase;
            color: var(--sm-muted);
        }

        .sm-selected-list {
            display: flex;
            flex-direction: column;
            gap: .4rem;
        }

        .sm-selected-item {
            display: flex;
            align-items: center;
            gap: .6rem;
            padding: .5rem .6rem .5rem .75rem;
            border: 1px solid var(--sm-border);
            border-left: 4px solid var(--sm-accent, var(--sm-blue));
            border-radius: 8px;
            background: #fff;
            font-size: .8rem;
        }

        .sm-selected-item .btn-sm-remove {
            flex-shrink: 0;
            width: 26px;
            height: 26px;
            margin-left: auto;
            padding: 0;
            border: none;
            border-radius: 6px;
            background: transparent;
            color: var(--sm-faint);
        }

        .sm-selected-item .btn-sm-remove:hover {
            background: var(--sm-danger-soft);
            color: var(--sm-danger);
        }

        .sm-selected-empty {
            padding: 1.25rem .75rem;
            border: 1px dashed var(--sm-border);
            border-radius: 8px;
            text-align: center;
            font-size: .8rem;
            color: var(--sm-faint);
        }

        .sm-totals {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .5rem;
            margin-top: .75rem;
        }

        .sm-total {
            position: relative;
            padding: .6rem .75rem;
            border-radius: 10px;
            background: linear-gradient(135deg, #dbeafe, #e0e7ff);
            overflow: hidden;
        }

        .sm-total.is-amber {
            background: linear-gradient(135deg, #fef3c7, #ffedd5);
        }

        .sm-total > i {
            position: absolute;
            top: 50%;
            right: .7rem;
            transform: translateY(-50%);
            font-size: 1.6rem;
            color: var(--sm-indigo);
            opacity: .25;
        }

        .sm-total.is-amber > i {
            color: var(--sm-amber);
        }

        .sm-total b {
            display: block;
            font-size: 1.35rem;
            line-height: 1.1;
            color: var(--sm-indigo);
            font-variant-numeric: tabular-nums;
        }

        .sm-total.is-amber b {
            color: var(--sm-amber);
        }

        .sm-total small {
            font-size: .66rem;
            font-weight: 600;
            letter-spacing: .05em;
            text-transform: uppercase;
            color: var(--sm-muted);
        }

        .sm-panel-foot {
            padding: 1rem 1.25rem;
            border-top: 1px solid var(--sm-border);
            background: #fafbfd;
        }

        .btn-sm-move {
            height: 46px;
            border: none;
            border-radius: 10px;
            background: linear-gradient(120deg, var(--sm-blue), var(--sm-teal));
            color: #fff;
            font-weight: 700;
            box-shadow: 0 6px 16px rgba(13, 148, 136, .3);
        }

        .btn-sm-move:hover {
            color: #fff;
            filter: brightness(1.1);
        }

        .btn-sm-move:disabled {
            background: #cbd5e1;
            color: #fff;
            box-shadow: none;
        }

        .sm-move-hint {
            margin-top: .5rem;
            font-size: .75rem;
            text-align: center;
            color: var(--sm-faint);
        }

        /* Riwayat */
        .sm-line-chip {
            display: inline-block;
            padding: .15rem .55rem;
            border-radius: 6px;
            background: var(--sm-navy-soft);
            color: var(--sm-navy-2);
            font-size: .76rem;
            font-weight: 600;
            white-space: nowrap;
        }

        .sm-line-chip {
            border: 1px solid transparent;
        }

        .sm-route-arrow-sm {
            margin: 0 .35rem;
            font-size: .7rem;
            color: var(--sm-faint);
        }

        .sm-user {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            white-space: nowrap;
        }

        .sm-avatar {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 26px;
            height: 26px;
            border-radius: 99px;
            background: linear-gradient(135deg, var(--sm-violet), var(--sm-pink));
            color: #fff;
            font-size: .7rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .sm-tag {
            display: inline-block;
            padding: .1rem .5rem;
            border-radius: 5px;
            font-size: .68rem;
            font-weight: 600;
            white-space: nowrap;
        }

        .sm-tag.is-set {
            background: #e0e7ff;
            color: var(--sm-indigo);
        }

        .sm-tag.is-stocker {
            background: var(--sm-amber-soft);
            color: var(--sm-amber);
        }

        .sm-status {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .15rem .6rem;
            border-radius: 99px;
            font-size: .7rem;
            font-weight: 600;
        }

        .sm-status::before {
            content: "";
            width: 6px;
            height: 6px;
            border-radius: 99px;
            background: currentColor;
        }

        .sm-status.is-active {
            background: var(--sm-success-soft);
            color: var(--sm-success);
        }

        .sm-status.is-cancel {
            background: var(--sm-danger-soft);
            color: var(--sm-danger);
        }

        .sm-history-row.is-cancel .sm-code {
            text-decoration: line-through;
            color: var(--sm-faint);
        }

        .sm-history-row.is-cancel > td {
            color: var(--sm-faint);
        }

        @media (max-width: 991.98px) {
            .sm-panel {
                position: static;
                max-height: none;
            }
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="sm-page-head">
            <div class="sm-page-icon"><i class="fa-solid fa-right-left"></i></div>
            <div>
                <h3>Mutasi Line Sewing In</h3>
                <p>Pindahkan WIP set & stocker outstanding dari satu line ke line lain</p>
            </div>
            <span class="sm-closed ms-auto d-none" id="closed_badge"><i class="fa-solid fa-lock"></i> Line asal sudah closing hari ini</span>
        </div>

        <div class="sm-tabs">
            <button type="button" class="sm-tab is-active" data-tab="mutasi" onclick="switchTab('mutasi')"><i class="fa-solid fa-right-left"></i> Mutasi</button>
            <button type="button" class="sm-tab" data-tab="riwayat" onclick="switchTab('riwayat')"><i class="fa-solid fa-clock-rotate-left"></i> Riwayat <span class="sm-tab-count" id="history_count">0</span></button>
        </div>

        {{-- Tab Mutasi --}}
        <div class="row g-3" id="tab_mutasi">
            {{-- Stok line asal --}}
            <div class="col-lg-8">
                <div class="sm-card is-blue mb-0">
                    <div class="sm-card-head">
                        <div style="flex: 1 1 260px;">
                            <label class="sm-label" for="line_asal"><i class="fa-solid fa-industry" style="color: var(--sm-blue)"></i>Line Asal</label>
                            <select class="form-select select2bs4" id="line_asal">
                                <option value="">Pilih Line Asal</option>
                                @foreach ($lines as $line)
                                    <option value="{{ $line->line_id }}">{{ $line->FullName }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="sm-search" style="flex: 1 1 220px;">
                            <label class="sm-label" for="stock_search"><i class="fa-solid fa-filter" style="color: var(--sm-teal)"></i>Cari</label>
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <input type="text" class="form-control" id="stock_search" placeholder="WS / color / size" autocomplete="off">
                        </div>
                        <button class="btn btn-sm-icon" type="button" onclick="loadStock()" title="Muat ulang"><i class="fa-solid fa-rotate"></i></button>
                    </div>

                    <div class="sm-table-wrap">
                        <table class="table table-hover sm-table mb-0">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 44px;"><input type="checkbox" class="form-check-input" id="check_all" title="Pilih semua"></th>
                                    <th>WS / Color</th>
                                    <th class="text-center">Size</th>
                                    <th title="WIP = saldo closing terakhir + Sewing In + mutasi masuk − mutasi keluar − output">
                                        WIP (set) <i class="fa-regular fa-circle-question ms-1"></i>
                                    </th>
                                    <th class="text-end">Stocker Outstanding</th>
                                </tr>
                            </thead>
                            <tbody id="stock_list">
                                <tr><td colspan="5" class="sm-empty"><i class="fa-solid fa-hand-pointer"></i>Pilih line asal dulu</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Panel pindah --}}
            <div class="col-lg-4">
                <div class="sm-card sm-panel mb-0">
                    <div class="sm-panel-head">
                        <i class="fa-solid fa-truck-ramp-box"></i>
                        <h6>Pindahkan Stok</h6>
                    </div>

                    <div class="sm-panel-body">
                        <div class="sm-route-v">
                            <label class="sm-label" style="color: var(--sm-blue)">Dari</label>
                            <div class="sm-route-from" id="route_from"><i class="fa-solid fa-industry"></i> <span>Pilih line asal</span></div>
                            <div class="sm-route-down"><span><i class="fa-solid fa-arrow-down"></i></span></div>
                            <label class="sm-label" for="line_tujuan" style="color: var(--sm-teal)">Ke</label>
                            <select class="form-select select2bs4" id="line_tujuan">
                                <option value="">Pilih Line Tujuan</option>
                                @foreach ($lines as $line)
                                    <option value="{{ $line->line_id }}">{{ $line->FullName }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="sm-section-title">
                            <span>Yang dipindah</span>
                            <a href="javascript:void(0)" class="text-danger text-decoration-none d-none" id="btn_clear" onclick="clearSelection()">Hapus semua</a>
                        </div>
                        <div class="sm-selected-list" id="selected_list"></div>

                        <div class="sm-totals">
                            <div class="sm-total">
                                <i class="fa-solid fa-layer-group"></i>
                                <b id="total_set">0</b>
                                <small>WIP set</small>
                            </div>
                            <div class="sm-total is-amber">
                                <i class="fa-solid fa-ticket"></i>
                                <b id="total_stocker">0</b>
                                <small>Stocker</small>
                            </div>
                        </div>

                        <div class="mt-3">
                            <label class="sm-label" for="keterangan"><i class="fa-regular fa-note-sticky" style="color: var(--sm-amber)"></i>Keterangan</label>
                            <input type="text" class="form-control" id="keterangan" maxlength="255" placeholder="Opsional, misal alasan pindah line">
                        </div>
                    </div>

                    <div class="sm-panel-foot">
                        <button class="btn btn-sm-move w-100" type="button" id="btn_move" onclick="confirmMove()" disabled>
                            <i class="fa-solid fa-right-left me-1"></i> Pindahkan
                        </button>
                        <div class="sm-move-hint" id="move_hint">Pilih line asal dulu</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tab Riwayat --}}
        <div class="sm-card is-violet d-none" id="tab_riwayat">
            <div class="sm-card-head">
                <div class="me-auto">
                    <h6 class="sm-card-title">Riwayat Mutasi</h6>
                    <div class="sm-card-sub" id="history_sub">Klik baris untuk melihat isi mutasi</div>
                </div>
                <div class="sm-search" style="flex: 0 1 320px;">
                    <label class="sm-label" for="history_search"><i class="fa-solid fa-filter" style="color: var(--sm-violet)"></i>Cari</label>
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" class="form-control" id="history_search" placeholder="Kode / line / WS / color / stocker / user" autocomplete="off">
                </div>
                <div>
                    <label class="sm-label" for="history_dari"><i class="fa-regular fa-calendar" style="color: var(--sm-pink)"></i>Dari</label>
                    <input type="date" class="form-control" id="history_dari" value="{{ date('Y-m-d') }}">
                </div>
                <div>
                    <label class="sm-label" for="history_sampai"><i class="fa-regular fa-calendar-check" style="color: var(--sm-pink)"></i>Sampai</label>
                    <input type="date" class="form-control" id="history_sampai" value="{{ date('Y-m-d') }}">
                </div>
            </div>
            <div class="sm-table-wrap">
                <table class="table table-hover sm-table mb-0">
                    <thead>
                        <tr>
                            <th style="width: 32px;"></th>
                            <th>Kode</th>
                            <th>Waktu</th>
                            <th>Rute</th>
                            <th class="text-end">WIP (set)</th>
                            <th class="text-end">Stocker</th>
                            <th>Keterangan</th>
                            <th>User</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody id="history_list">
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('custom-script')
    <!-- Select2 -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        $('.select2bs4').select2({
            theme: 'bootstrap4'
        })
    </script>

    <script>
        var stockData = [];
        var historyData = [];
        var lineClosed = false;

        function escapeHtml(text) {
            return $("<div>").text(text ?? "-").html();
        }

        // Warna tetap per teks (line / size sama = warna sama)
        function hueOf(text) {
            let hue = 0;

            for (let char of String(text ?? "")) {
                hue = (hue * 31 + char.charCodeAt(0)) % 360;
            }

            return hue;
        }

        function chipStyle(text) {
            let hue = hueOf(text);

            return `background: hsl(${hue} 85% 95%); color: hsl(${hue} 65% 35%); border-color: hsl(${hue} 70% 85%);`;
        }

        // Titik warna sesuai nama color (fallback warna dari teks)
        var colorMap = {
            navy: "#1e3a8a", white: "#ffffff", black: "#111827", red: "#dc2626", maroon: "#7f1d1d",
            pink: "#ec4899", purple: "#7c3aed", blue: "#2563eb", tosca: "#14b8a6", green: "#16a34a",
            olive: "#65a30d", mint: "#a7f3d0", yellow: "#facc15", orange: "#f97316", brown: "#92400e",
            khaki: "#c3b091", beige: "#e7dcc5", cream: "#f5f0dc", grey: "#9ca3af", gray: "#9ca3af",
            silver: "#cbd5e1",
        };

        function swatch(color) {
            let name = String(color ?? "").toLowerCase();
            let key = Object.keys(colorMap).find(key => name.includes(key));

            return `<span class="sm-swatch" style="background: ${key ? colorMap[key] : `hsl(${hueOf(color)} 60% 55%)`}"></span>`;
        }

        function formatTime(datetime) {
            datetime = datetime ?? "";

            return `
                <div class="fw-semibold">${escapeHtml(datetime.substring(11, 16))}</div>
                <small class="sm-sub">${escapeHtml(datetime.substring(8, 10) + "/" + datetime.substring(5, 7) + "/" + datetime.substring(0, 4))}</small>
            `;
        }

        // Rincian WIP di bawah angka: hanya yang tidak 0
        function flowText(group) {
            let flows = [
                ["Awal", group.saldo_awal, ""],
                ["Sewing In", group.qty_sewing_in, "+"],
                ["Mutasi In", group.qty_mutasi_in, "+"],
                ["Mutasi Out", group.qty_mutasi_out, "-"],
                ["Output", group.qty_output, "-"],
            ].filter(flow => Number(flow[1]) != 0);

            if (flows.length < 1) {
                return "";
            }

            return `<div class="sm-flow">${flows.map(([label, value, sign]) => `
                <span>${label} <span class="${sign == '+' ? 'is-plus' : (sign == '-' ? 'is-minus' : '')}">${sign == '-' ? '−' : sign}${escapeHtml(Number(value))}</span></span>
            `).join("")}</div>`;
        }

        // ===== Tab =====
        function switchTab(tab) {
            $(".sm-tab").removeClass("is-active").filter(`[data-tab="${tab}"]`).addClass("is-active");
            $("#tab_mutasi").toggleClass("d-none", tab != "mutasi");
            $("#tab_riwayat").toggleClass("d-none", tab != "riwayat");

            if (tab == "riwayat") {
                loadHistory();
            }
        }

        $("#line_asal").on("change", function () {
            let selected = $(this).val();

            $("#route_from").toggleClass("is-set", !!selected).find("span").text(selected ? $("#line_asal option:selected").text() : "Pilih line asal");

            // Line asal tidak bisa dipilih jadi tujuan
            $("#line_tujuan option").prop("disabled", false);

            if (selected) {
                $(`#line_tujuan option[value="${selected}"]`).prop("disabled", true);

                if ($("#line_tujuan").val() == selected) {
                    $("#line_tujuan").val("");
                }
            }

            $("#line_tujuan").trigger("change.select2");

            loadStock();
            loadHistory();
        });

        $("#line_tujuan").on("change", updateSelection);
        $("#history_dari, #history_sampai").on("change", loadHistory);
        $("#stock_search").on("input", filterStock);
        $("#history_search").on("input", renderHistory);

        // ===== Stok line asal =====
        function loadStock() {
            let lineId = $("#line_asal").val();

            stockData = [];
            updateSelection();

            if (!lineId) {
                $("#stock_list").html(`<tr><td colspan="5" class="sm-empty"><i class="fa-solid fa-hand-pointer"></i>Pilih line asal dulu</td></tr>`);
                $("#closed_badge").addClass("d-none");
                return;
            }

            $("#stock_list").html(`<tr><td colspan="5" class="sm-empty"><i class="fa-solid fa-spinner fa-spin"></i>Menghitung stok...</td></tr>`);

            $.ajax({
                url: "{{ route('sewing-in-mutasi-stock') }}",
                type: "get",
                data: { line_id: lineId },
                success: function (res) {
                    stockData = res.data;
                    lineClosed = res.closed;

                    $("#closed_badge").toggleClass("d-none", !lineClosed);
                    renderStock();
                },
                error: function () {
                    $("#stock_list").html(`<tr><td colspan="5" class="sm-empty text-danger"><i class="fa-solid fa-triangle-exclamation"></i>Gagal menghitung stok</td></tr>`);
                }
            });
        }

        function renderStock() {
            let html = stockData.map((group, i) => {
                let stockerRows = group.stockers.map(stocker => `
                    <tr>
                        <td><span class="sm-code">${escapeHtml(stocker.id_qr_stocker)}</span></td>
                        <td>${escapeHtml(stocker.nama_part)}</td>
                        <td class="text-end">${escapeHtml(stocker.qty_stocker)}</td>
                        <td class="text-end">${escapeHtml(stocker.qty_used)}</td>
                        <td class="text-end"><span class="sm-sisa">${escapeHtml(stocker.qty_sisa)}</span></td>
                    </tr>
                `).join("");

                let searchText = [group.act_costing_ws, group.color, group.size].join(" ").toLowerCase();

                return `
                    <tr class="sm-group-row" data-index="${i}" data-search="${escapeHtml(searchText)}">
                        <td class="text-center"><input type="checkbox" class="form-check-input sm-check" data-index="${i}" ${lineClosed ? 'disabled' : ''}></td>
                        <td>
                            <div class="fw-semibold text-nowrap">${escapeHtml(group.act_costing_ws)}</div>
                            <small class="sm-sub">${swatch(group.color)}${escapeHtml(group.color)}</small>
                        </td>
                        <td class="text-center"><span class="sm-size" style="${chipStyle(group.size)}">${escapeHtml(group.size)}</span></td>
                        <td>
                            <span class="sm-wip ${group.saldo_akhir < 0 ? 'is-minus' : ''}">${escapeHtml(group.saldo_akhir)}</span>
                            ${flowText(group)}
                        </td>
                        <td class="text-end">
                            ${group.total_stocker > 0 ?
                                `<span class="sm-stocker-badge" onclick="event.stopPropagation(); toggleStockers(this, ${i})" role="button"><i class="fa-solid fa-ticket"></i>${group.total_stocker} stocker · sisa ${group.qty_stocker} <i class="fa-solid fa-chevron-down"></i></span>` :
                                '<span class="sm-faded">-</span>'}
                        </td>
                    </tr>
                    ${group.total_stocker > 0 ? `
                        <tr class="sm-detail d-none" id="stockers_${i}">
                            <td colspan="5">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Stocker</th>
                                            <th>Part</th>
                                            <th class="text-end">Qty</th>
                                            <th class="text-end">Terpakai</th>
                                            <th class="text-end">Sisa</th>
                                        </tr>
                                    </thead>
                                    <tbody>${stockerRows}</tbody>
                                </table>
                            </td>
                        </tr>
                    ` : ''}
                `;
            }).join("");

            $("#stock_list").html(html || `<tr><td colspan="5" class="sm-empty"><i class="fa-regular fa-folder-open"></i>Tidak ada stok di line ini</td></tr>`);
            $("#check_all").prop("checked", false).prop("disabled", lineClosed || stockData.length < 1);

            filterStock();
            updateSelection();
        }

        // Cari: sembunyikan baris yang tidak cocok (centangan tetap)
        function filterStock() {
            let search = $("#stock_search").val().trim().toLowerCase();

            $("#stock_list .sm-group-row").each(function () {
                let match = !search || String($(this).data("search")).includes(search);

                $(this).toggleClass("d-none", !match);

                if (!match) {
                    $(`#stockers_${$(this).data("index")}`).addClass("d-none");
                    $(this).find(".sm-stocker-badge").removeClass("is-open");
                }
            });

            syncCheckAll();
        }

        function toggleStockers(badge, i) {
            $(badge).toggleClass("is-open");
            $(`#stockers_${i}`).toggleClass("d-none");
        }

        // Klik baris = centang
        $(document).on("click", "#stock_list .sm-group-row", function (e) {
            if ($(e.target).is("input") || lineClosed) {
                return;
            }

            let check = $(this).find(".sm-check");
            check.prop("checked", !check.prop("checked")).trigger("change");
        });

        $(document).on("change", ".sm-check", function () {
            $(this).closest("tr").toggleClass("is-selected", $(this).prop("checked"));
            syncCheckAll();
            updateSelection();
        });

        // Pilih semua = semua baris yang sedang tampil
        $("#check_all").on("change", function () {
            let checked = $(this).prop("checked");

            $("#stock_list .sm-group-row:not(.d-none) .sm-check").prop("checked", checked).each(function () {
                $(this).closest("tr").toggleClass("is-selected", checked);
            });

            updateSelection();
        });

        function syncCheckAll() {
            let visible = $("#stock_list .sm-group-row:not(.d-none) .sm-check");

            $("#check_all").prop("checked", visible.length > 0 && visible.filter(":not(:checked)").length == 0);
        }

        function uncheckGroup(i) {
            $(`.sm-check[data-index="${i}"]`).prop("checked", false).trigger("change");
        }

        function clearSelection() {
            $(".sm-check").prop("checked", false).closest("tr").removeClass("is-selected");
            syncCheckAll();
            updateSelection();
        }

        function selectedIndexes() {
            return $(".sm-check:checked").map((i, el) => $(el).data("index")).get();
        }

        function selectedGroups() {
            return selectedIndexes().map(i => stockData[i]);
        }

        function updateSelection() {
            let indexes = selectedIndexes();
            let groups = indexes.map(i => stockData[i]);
            let qtySet = groups.reduce((total, group) => total + Math.max(Number(group.saldo_akhir), 0), 0);
            let stockers = groups.reduce((total, group) => total + Number(group.total_stocker), 0);
            let tujuan = $("#line_tujuan").val();
            let asal = $("#line_asal").val();

            // Daftar yang dipilih di panel
            $("#selected_list").html(groups.length > 0 ?
                groups.map((group, n) => `
                    <div class="sm-selected-item" style="--sm-accent: hsl(${hueOf(group.act_costing_ws + group.color + group.size)} 70% 50%)">
                        <div style="min-width: 0;">
                            <div class="fw-semibold text-truncate">${escapeHtml(group.act_costing_ws)}</div>
                            <div class="sm-sub text-truncate">${swatch(group.color)}${escapeHtml(group.color)} · ${escapeHtml(group.size)}</div>
                        </div>
                        <div class="text-end text-nowrap ms-auto">
                            <div class="fw-bold" style="color: var(--sm-indigo)">${Math.max(Number(group.saldo_akhir), 0)} set</div>
                            <div class="sm-sub">${group.total_stocker} stocker</div>
                        </div>
                        <button type="button" class="btn-sm-remove" onclick="uncheckGroup(${indexes[n]})" title="Batal pilih"><i class="fa-solid fa-xmark"></i></button>
                    </div>
                `).join("") :
                `<div class="sm-selected-empty"><i class="fa-regular fa-square-check d-block mb-1" style="font-size: 1.3rem;"></i>Centang stok di tabel sebelah kiri</div>`);

            $("#btn_clear").toggleClass("d-none", groups.length < 2);
            $("#total_set").text(qtySet);
            $("#total_stocker").text(stockers);

            // Alasan tombol belum bisa diklik
            let hint = "";

            if (!asal) {
                hint = "Pilih line asal dulu";
            } else if (lineClosed) {
                hint = "Line asal sudah closing hari ini";
            } else if (groups.length < 1) {
                hint = "Centang stok yang mau dipindah";
            } else if (!tujuan) {
                hint = "Pilih line tujuan";
            } else {
                hint = `${groups.length} WS/size siap dipindah`;
            }

            $("#move_hint").text(hint);
            $("#btn_move").prop("disabled", !(groups.length > 0 && tujuan && tujuan != asal && !lineClosed));
        }

        // ===== Simpan mutasi =====
        function confirmMove() {
            let groups = selectedGroups();
            let tujuan = $("#line_tujuan").val();

            if (tujuan == $("#line_asal").val()) {
                Swal.fire({ icon: 'warning', title: 'Line tujuan harus berbeda dengan line asal' });
                return;
            }

            let list = groups.map(group => `<li>${escapeHtml(group.act_costing_ws)} / ${escapeHtml(group.color)} / ${escapeHtml(group.size)}: <b>${Math.max(group.saldo_akhir, 0)}</b> set, <b>${group.total_stocker}</b> stocker</li>`).join("");

            Swal.fire({
                icon: 'question',
                title: 'Pindahkan stok?',
                html: `
                    <div class="text-start">
                        <b>${escapeHtml($("#line_asal option:selected").text())}</b> <i class="fa-solid fa-arrow-right mx-1"></i> <b>${escapeHtml($("#line_tujuan option:selected").text())}</b>
                        <ul class="mt-2 mb-0 small">${list}</ul>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Pindahkan',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#082149',
            }).then((result) => {
                if (result.isConfirmed) {
                    saveMove(groups);
                }
            });
        }

        function saveMove(groups) {
            document.getElementById("loading").classList.remove("d-none");
            $("#btn_move").prop("disabled", true);

            $.ajax({
                url: "{{ route('sewing-in-mutasi-store') }}",
                type: "post",
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
                },
                data: {
                    line_id_asal: $("#line_asal").val(),
                    line_id_tujuan: $("#line_tujuan").val(),
                    keterangan: $("#keterangan").val(),
                    items: groups.map(group => ({
                        act_costing_ws: group.act_costing_ws,
                        color: group.color,
                        size: group.size,
                    })),
                },
                success: function (res) {
                    // Antrian simpan penuh: tunggu lalu kirim ulang otomatis
                    if (res.busy) {
                        setTimeout(() => saveMove(groups), 500);
                        return;
                    }

                    document.getElementById("loading").classList.add("d-none");

                    if (res.status != 200) {
                        updateSelection();
                        Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
                        return;
                    }

                    Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message });

                    $("#keterangan").val("");
                    loadStock();
                    loadHistory();
                },
                error: function (jqXHR) {
                    document.getElementById("loading").classList.add("d-none");
                    updateSelection();

                    let errors = Object.values(jqXHR.responseJSON?.errors ?? {}).flat();

                    Swal.fire({ icon: 'error', title: 'Error', text: errors[0] ?? jqXHR.responseJSON?.message ?? 'Terjadi kesalahan saat menyimpan mutasi' });
                }
            });
        }

        // ===== Riwayat mutasi =====
        function loadHistory() {
            $("#history_list").html(`<tr><td colspan="9" class="sm-empty"><i class="fa-solid fa-spinner fa-spin"></i>Memuat...</td></tr>`);

            $.ajax({
                url: "{{ route('sewing-in-mutasi-history') }}",
                type: "get",
                data: {
                    tanggal_awal: $("#history_dari").val(),
                    tanggal_akhir: $("#history_sampai").val(),
                    line_id: $("#line_asal").val(),
                },
                success: function (res) {
                    historyData = res.data;
                    renderHistory();
                },
                error: function (jqXHR) {
                    historyData = [];
                    $("#history_count").text(0);

                    let errors = Object.values(jqXHR.responseJSON?.errors ?? {}).flat();

                    $("#history_list").html(`<tr><td colspan="9" class="sm-empty text-danger"><i class="fa-solid fa-triangle-exclamation"></i>${escapeHtml(errors[0] ?? 'Gagal memuat riwayat')}</td></tr>`);
                }
            });
        }

        function renderHistory() {
            let lineName = $("#line_asal").val() ? $("#line_asal option:selected").text() : "semua line";
            let search = $("#history_search").val().trim().toLowerCase();

            // Cari di header + isi mutasi (stocker, WS, color, size)
            let data = historyData.filter(item => !search ||
                [item.kode, item.nama_line_asal, item.nama_line_tujuan, item.keterangan, item.created_by_username]
                    .concat(item.items.flatMap(row => [row.id_qr_stocker, row.act_costing_ws, row.color, row.size]))
                    .join(" ")
                    .toLowerCase()
                    .includes(search));

            $("#history_count").text(historyData.length);
            $("#history_sub").text(search ?
                `${data.length} dari ${historyData.length} mutasi · ${lineName}` :
                `${historyData.length} mutasi · ${lineName} · klik baris untuk melihat isinya`);

            let html = data.map((item, i) => {
                let isCancel = item.cancel == "Y";
                let user = item.created_by_username ?? "";

                let itemRows = item.items.map(row => `
                    <tr>
                        <td>${row.tipe == "set" ? '<span class="sm-tag is-set">WIP set</span>' : '<span class="sm-tag is-stocker">Stocker</span>'}</td>
                        <td>${row.id_qr_stocker ? `<span class="sm-code">${escapeHtml(row.id_qr_stocker)}</span>` : '<span class="sm-faded">-</span>'}</td>
                        <td>${escapeHtml(row.act_costing_ws)}</td>
                        <td>${swatch(row.color)}${escapeHtml(row.color)}</td>
                        <td class="text-center"><span class="sm-size" style="${chipStyle(row.size)}">${escapeHtml(row.size)}</span></td>
                        <td class="text-end fw-bold">${escapeHtml(row.qty)}</td>
                    </tr>
                `).join("");

                return `
                    <tr class="sm-group-row sm-history-row ${isCancel ? 'is-cancel' : ''}" onclick="$(this).toggleClass('is-open'); $('#history_detail_${i}').toggleClass('d-none')">
                        <td class="text-center"><i class="fa-solid fa-chevron-right sm-caret"></i></td>
                        <td><span class="sm-code">${escapeHtml(item.kode)}</span></td>
                        <td class="text-nowrap">${formatTime(item.created_at)}</td>
                        <td class="text-nowrap">
                            <span class="sm-line-chip" style="${chipStyle(item.nama_line_asal)}">${escapeHtml(item.nama_line_asal)}</span>
                            <i class="fa-solid fa-arrow-right sm-route-arrow-sm"></i>
                            <span class="sm-line-chip" style="${chipStyle(item.nama_line_tujuan)}">${escapeHtml(item.nama_line_tujuan)}</span>
                        </td>
                        <td class="text-end"><span class="sm-wip">${escapeHtml(item.qty_set)}</span></td>
                        <td class="text-end text-nowrap"><b>${escapeHtml(item.total_stocker)}</b> <span class="sm-sub">(${escapeHtml(item.qty_stocker)} pcs)</span></td>
                        <td>${item.keterangan ? escapeHtml(item.keterangan) : '<span class="sm-faded">-</span>'}</td>
                        <td><span class="sm-user"><span class="sm-avatar">${escapeHtml(user.substring(0, 1) || "?")}</span>${escapeHtml(user)}</span></td>
                        <td class="text-center"><span class="sm-status ${isCancel ? 'is-cancel' : 'is-active'}">${isCancel ? 'Cancel' : 'Aktif'}</span></td>
                    </tr>
                    <tr class="sm-detail d-none" id="history_detail_${i}">
                        <td colspan="9">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th style="width: 90px;">Tipe</th>
                                        <th>Stocker</th>
                                        <th>WS</th>
                                        <th>Color</th>
                                        <th class="text-center">Size</th>
                                        <th class="text-end">Qty</th>
                                    </tr>
                                </thead>
                                <tbody>${itemRows}</tbody>
                            </table>
                        </td>
                    </tr>
                `;
            }).join("");

            $("#history_list").html(html || (search ?
                `<tr><td colspan="9" class="sm-empty"><i class="fa-solid fa-magnifying-glass"></i>Tidak ada mutasi yang cocok dengan "${escapeHtml(search)}"</td></tr>` :
                `<tr><td colspan="9" class="sm-empty"><i class="fa-regular fa-folder-open"></i>Belum ada mutasi</td></tr>`));
        }

        updateSelection();
        loadHistory();
    </script>
@endsection
