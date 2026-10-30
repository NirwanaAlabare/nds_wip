@extends('layouts.index', ['navbar' => false, 'footer' => false, 'containerFluid' => true])

@section('custom-link')
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

    <style>
        :root {
            --si-navy: #082149;
            --si-navy-2: #123a78;
            --si-bg: #f1f4f9;
            --si-surface: #fff;
            --si-border: #e5e9f0;
            --si-line: #f1f4f8;
            --si-text: #0f172a;
            --si-muted: #64748b;
            --si-faint: #94a3b8;
            --si-success: #059669;
            --si-success-soft: #ecfdf5;
            --si-first: #d97706;
            --si-first-soft: #fffbeb;
            --si-h: 40px;
            --si-radius: 10px;
        }

        .content-wrapper {
            background: var(--si-bg) !important;
        }

        /* ===== Header ===== */
        .si-header {
            position: sticky;
            top: 0;
            z-index: 20;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: .75rem 1rem;
            padding: .75rem 1rem;
            border-radius: var(--si-radius);
            background: linear-gradient(120deg, var(--si-navy), var(--si-navy-2));
            color: #fff;
            box-shadow: 0 4px 14px rgba(8, 33, 73, .18);
        }

        .si-brand {
            display: flex;
            align-items: center;
            gap: .6rem;
            min-width: 170px;
        }

        .si-brand-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            border-radius: 8px;
            background: rgba(255, 255, 255, .12);
        }

        .si-brand h5 {
            margin: 0;
            font-size: 1.05rem;
            font-weight: 700;
            line-height: 1.1;
        }

        .si-brand small {
            font-size: .72rem;
            color: rgba(255, 255, 255, .6);
            font-variant-numeric: tabular-nums;
        }

        .si-controls {
            display: flex;
            flex: 1;
            gap: .5rem;
            min-width: 280px;
        }

        .si-line-select {
            width: 230px;
            flex-shrink: 0;
        }

        .si-header .form-control,
        .si-header .btn,
        .si-header .input-group-text,
        .si-header .select2-container--bootstrap4 .select2-selection--single {
            height: var(--si-h) !important;
            border: none !important;
        }

        .si-header .select2-container--bootstrap4 .select2-selection--single {
            border-radius: 8px !important;
        }

        .si-header .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
            line-height: var(--si-h) !important;
            padding-left: .8rem;
            font-weight: 600;
            color: var(--si-text);
        }

        .si-header .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow {
            height: var(--si-h) !important;
        }

        .si-scan-group {
            flex: 1;
            flex-wrap: nowrap;
        }

        .si-scan-group .input-group-text {
            background: #fff;
            color: var(--si-faint);
            border-radius: 8px 0 0 8px;
        }

        #kode_stocker {
            font-weight: 600;
            letter-spacing: .02em;
        }

        #kode_stocker:focus {
            box-shadow: none;
        }

        .si-scan-group:focus-within {
            border-radius: 8px;
            box-shadow: 0 0 0 3px rgba(255, 255, 255, .35);
        }

        .si-scan-group :disabled,
        .si-scan-group .input-group-text.is-disabled {
            background: rgba(255, 255, 255, .7) !important;
        }

        .btn-si-camera {
            border-radius: 0 8px 8px 0 !important;
            background: #fff;
            color: var(--si-navy);
            font-weight: 600;
            border-left: 1px solid var(--si-border) !important;
        }

        .btn-si-camera:hover {
            background: #eef2f8;
            color: var(--si-navy);
        }

        .btn-si-camera:disabled {
            background: rgba(255, 255, 255, .7);
            color: var(--si-faint);
        }

        #camera_wrapper {
            flex-basis: 100%;
        }

        #stocker-reader {
            max-width: 340px;
            margin: 0 auto;
            border-radius: 10px;
            overflow: hidden;
            background: #000;
        }

        /* ===== Ringkasan ===== */
        .si-summary {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: .75rem 0;
            margin-top: .6rem;
            padding: .6rem .5rem;
            border: 1px solid var(--si-border);
            border-radius: var(--si-radius);
            background: var(--si-surface);
        }

        .si-stat {
            padding: 0 1rem;
            min-width: 0;
        }

        .si-stat + .si-stat {
            border-left: 1px solid var(--si-border);
        }

        .si-stat small,
        .si-qty label {
            display: block;
            margin-bottom: .1rem;
            font-size: .66rem;
            font-weight: 600;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: var(--si-faint);
        }

        .si-stat b {
            display: block;
            font-size: .92rem;
            color: var(--si-text);
            word-break: break-word;
        }

        .si-stat-progress {
            min-width: 150px;
        }

        .si-progress {
            height: 4px;
            margin-top: .35rem;
            border-radius: 99px;
            background: var(--si-line);
        }

        .si-progress .progress-bar {
            background: var(--si-success);
            transition: width .3s ease;
        }

        .si-qty {
            margin-left: auto;
            padding: 0 .5rem 0 1rem;
            border-left: 1px solid var(--si-border);
        }

        .si-qty-row {
            display: flex;
            gap: .4rem;
        }

        .si-qty .form-control,
        .si-qty .btn {
            height: var(--si-h);
        }

        #qty_set {
            width: 120px;
            font-weight: 700;
            text-align: center;
        }

        #qty_set:focus {
            border-color: var(--si-success);
            box-shadow: 0 0 0 3px rgba(5, 150, 105, .15);
        }

        .btn-si-apply {
            padding: 0 1.1rem;
            background: var(--si-success);
            color: #fff;
            font-weight: 600;
        }

        .btn-si-apply:hover {
            background: #047857;
            color: #fff;
        }

        .btn-si-apply:disabled {
            background: #cbd5e1;
            color: #fff;
        }

        .btn-si-ghost {
            width: var(--si-h);
            padding: 0;
            border: 1px solid var(--si-border);
            color: var(--si-muted);
        }

        .btn-si-ghost:hover {
            background: var(--si-line);
            color: var(--si-text);
        }

        #qty_set[readonly] {
            background: var(--si-line);
            color: var(--si-navy);
            font-size: 1.1rem;
            cursor: default;
        }

        /* Qty act: tombol − angka + (ukuran jari) */
        .si-stepper {
            display: inline-flex;
            align-items: stretch;
            border: 1px solid var(--si-border);
            border-radius: 8px;
            background: #fff;
            overflow: hidden;
        }

        .si-stepper button {
            border: none;
            background: transparent;
            touch-action: manipulation;
        }

        .si-step-btn {
            width: 44px;
            height: 44px;
            color: var(--si-navy-2);
        }

        .si-step-btn:active {
            background: var(--si-line);
        }

        .si-step-btn:disabled {
            color: #cbd5e1;
        }

        .si-step-value {
            min-width: 56px;
            border-left: 1px solid var(--si-border) !important;
            border-right: 1px solid var(--si-border) !important;
            font-size: 1rem;
            font-weight: 700;
            color: var(--si-text);
        }

        .si-stepper.is-changed {
            border-color: var(--si-first);
        }

        .si-stepper.is-changed .si-step-value {
            background: var(--si-first-soft);
            color: var(--si-first);
        }

        .si-qty-origin {
            display: block;
            font-size: .66rem;
            color: var(--si-first);
            white-space: nowrap;
        }

        .si-step-diff {
            margin-top: .15rem;
            font-size: .68rem;
            font-weight: 600;
            color: var(--si-first);
        }

        /* Riwayat (modal) */
        .btn-si-history {
            flex-shrink: 0;
            border-radius: 8px !important;
            background: rgba(255, 255, 255, .12);
            color: #fff;
            font-weight: 600;
        }

        .btn-si-history:hover {
            background: rgba(255, 255, 255, .22);
            color: #fff;
        }

        /* Modal riwayat lebar & tinggi tetap supaya tidak "loncat" saat data sedikit */
        .si-history-dialog {
            max-width: min(1600px, 96vw);
        }

        .si-history-dialog .modal-content {
            height: 90vh;
        }

        .si-history-modal {
            border: none;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 24px 60px rgba(8, 33, 73, .35);
        }

        .si-history-head {
            gap: .85rem;
            padding: 1rem 1.25rem;
            border: none;
            background: linear-gradient(120deg, var(--si-navy), var(--si-navy-2));
            color: #fff;
        }

        .si-history-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: rgba(255, 255, 255, .14);
            font-size: 1.05rem;
        }

        .si-history-head h6 {
            font-weight: 700;
        }

        .si-history-head small {
            color: rgba(255, 255, 255, .65);
        }

        .si-tabs {
            display: inline-flex;
            padding: 3px;
            border-radius: 8px;
            background: rgba(255, 255, 255, .12);
        }

        .si-tab {
            padding: .35rem .9rem;
            border: none;
            border-radius: 6px;
            background: transparent;
            color: rgba(255, 255, 255, .75);
            font-size: .8rem;
            font-weight: 600;
        }

        .si-tab.is-active {
            background: #fff;
            color: var(--si-navy);
        }

        .si-sisa {
            display: inline-block;
            min-width: 36px;
            padding: .1rem .5rem;
            border-radius: 99px;
            background: var(--si-first-soft);
            color: var(--si-first);
            font-weight: 700;
            text-align: center;
        }

        .si-history-filter {
            padding: .85rem 1.25rem;
            border-bottom: 1px solid var(--si-border);
            background: #fafbfd;
        }

        .si-history-filter label {
            display: block;
            margin-bottom: .2rem;
            font-size: .66rem;
            font-weight: 600;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: var(--si-faint);
        }

        .si-history-filter .form-control,
        .si-history-filter .select2-container--bootstrap4 .select2-selection--single {
            height: var(--si-h) !important;
        }

        .si-history-filter .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
            line-height: calc(var(--si-h) - 2px) !important;
        }

        .si-history-stats {
            display: flex;
            flex-wrap: wrap;
            gap: .75rem;
            padding: .75rem 1.25rem;
            border-bottom: 1px solid var(--si-border);
        }

        .si-stat {
            --si-stat-color: var(--si-navy-2);
            --si-stat-soft: #eef3fb;
            display: flex;
            align-items: center;
            gap: .65rem;
            min-width: 150px;
            padding: .5rem .9rem .5rem .55rem;
            border: 1px solid var(--si-border);
            border-radius: var(--si-radius);
            background: #fff;
        }

        .si-stat.is-success {
            --si-stat-color: var(--si-success);
            --si-stat-soft: var(--si-success-soft);
        }

        .si-stat.is-danger {
            --si-stat-color: #dc2626;
            --si-stat-soft: #fef2f2;
        }

        .si-stat.is-warning {
            --si-stat-color: var(--si-first);
            --si-stat-soft: var(--si-first-soft);
        }

        .si-stat > i {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            border-radius: 8px;
            background: var(--si-stat-soft);
            color: var(--si-stat-color);
        }

        .si-stat b {
            display: block;
            font-size: 1.15rem;
            line-height: 1.1;
            color: var(--si-text);
            font-variant-numeric: tabular-nums;
        }

        .si-stat small {
            font-size: .66rem;
            font-weight: 600;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: var(--si-faint);
        }

        .si-sub {
            font-size: .72rem;
            color: var(--si-faint);
        }

        .si-line-chip {
            display: inline-block;
            padding: .15rem .55rem;
            border-radius: 6px;
            background: #eef3fb;
            color: var(--si-navy-2);
            font-size: .76rem;
            font-weight: 600;
            white-space: nowrap;
        }

        .si-size {
            display: inline-block;
            min-width: 38px;
            padding: .15rem .45rem;
            border: 1px solid var(--si-border);
            border-radius: 6px;
            font-weight: 700;
        }

        .si-qty-set {
            font-size: 1.05rem;
            font-weight: 800;
            color: var(--si-navy);
            font-variant-numeric: tabular-nums;
        }

        .si-history-table > thead > tr > th {
            position: sticky;
            top: 0;
            z-index: 1;
            padding: .6rem .75rem;
            border-bottom: 1px solid var(--si-border);
            background: #fff;
            color: var(--si-faint);
            font-size: .66rem;
            font-weight: 600;
            letter-spacing: .06em;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .si-history-table > thead > tr > th:first-child,
        .si-history-table > tbody > tr:not(.si-history-detail) > td:first-child {
            padding-left: 1.25rem;
        }

        .si-history-table > thead > tr > th:last-child,
        .si-history-table > tbody > tr:not(.si-history-detail) > td:last-child {
            padding-right: 1.25rem;
        }

        .si-history-table > tbody > tr > td {
            padding: .65rem .75rem;
            border-color: var(--si-line);
            font-size: .84rem;
            vertical-align: middle;
        }

        .si-history-row {
            cursor: pointer;
        }

        .si-history-row .si-caret {
            color: var(--si-faint);
            transition: transform .15s ease;
        }

        .si-history-row.is-open .si-caret {
            transform: rotate(90deg);
        }

        .si-history-row.is-open td {
            background: #f5f8fd;
        }

        .si-history-row.is-cancel td {
            color: var(--si-faint);
        }

        .si-history-row.is-cancel .si-code {
            text-decoration: line-through;
            color: var(--si-faint);
        }

        .si-history-detail > td {
            padding: .25rem 1.25rem .75rem 3.2rem !important;
            background: #f5f8fd;
        }

        .si-history-detail table {
            margin: 0;
            background: #fff;
            border: 1px solid var(--si-border);
            border-radius: 8px;
            overflow: hidden;
        }

        .si-history-detail th {
            padding: .35rem .7rem;
            background: #fafbfd;
            color: var(--si-faint);
            font-size: .62rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .si-history-detail td {
            padding: .35rem .7rem;
            font-size: .78rem;
            border-color: var(--si-line);
        }

        .si-status {
            display: inline-block;
            padding: .1rem .5rem;
            border-radius: 99px;
            font-size: .68rem;
            font-weight: 600;
        }

        .si-status.is-active {
            background: var(--si-success-soft);
            color: var(--si-success);
        }

        .si-status.is-cancel {
            background: #fef2f2;
            color: #dc2626;
        }

        /* Numpad */
        .si-numpad-backdrop {
            position: fixed;
            inset: 0;
            z-index: 1060;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            background: rgba(15, 23, 42, .45);
        }

        .si-numpad {
            width: 100%;
            max-width: 340px;
            padding: 1rem;
            border-radius: 14px;
            background: #fff;
            box-shadow: 0 20px 40px rgba(15, 23, 42, .25);
        }

        .si-numpad-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: .5rem;
            font-size: .75rem;
            color: var(--si-muted);
        }

        .si-numpad-head small {
            display: block;
            font-size: .66rem;
            font-weight: 600;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: var(--si-faint);
        }

        .si-numpad-head b {
            color: var(--si-navy);
        }

        .si-numpad-display {
            margin-top: .75rem;
            padding: .5rem .75rem;
            border: 2px solid var(--si-success);
            border-radius: 10px;
            text-align: right;
            font-size: 2rem;
            font-weight: 700;
            color: var(--si-text);
            font-variant-numeric: tabular-nums;
        }

        .si-numpad-display.is-invalid {
            border-color: #dc2626;
            color: #dc2626;
        }

        .si-numpad-hint {
            min-height: 1.2rem;
            margin: .3rem 0 .6rem;
            font-size: .75rem;
            text-align: right;
            color: var(--si-muted);
        }

        .si-numpad-keys {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: .5rem;
        }

        .si-numpad-keys button {
            height: 56px;
            border: 1px solid var(--si-border);
            border-radius: 10px;
            background: #fff;
            font-size: 1.35rem;
            font-weight: 600;
            color: var(--si-text);
            touch-action: manipulation;
        }

        .si-numpad-keys button:active {
            background: var(--si-line);
        }

        .si-numpad-keys button.is-muted {
            background: #fafbfd;
            font-size: .95rem;
            color: var(--si-muted);
        }

        .si-numpad-actions {
            display: flex;
            gap: .5rem;
            margin-top: .75rem;
        }

        .si-numpad-actions .btn {
            height: 48px;
        }

        #qty_set_note {
            margin-top: .2rem;
            font-size: .7rem;
        }

        /* ===== Tabel Part 1 Set ===== */
        .si-card {
            margin-top: .6rem;
            border: 1px solid var(--si-border);
            border-radius: var(--si-radius);
            background: var(--si-surface);
            overflow: hidden;
        }

        .si-card-head {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: .6rem 1rem;
            border-bottom: 1px solid var(--si-border);
        }

        .si-card-head h6 {
            margin: 0;
            font-size: .9rem;
            font-weight: 700;
            color: var(--si-navy);
        }

        .si-legend {
            display: inline-flex;
            gap: .9rem;
            font-size: .7rem;
            color: var(--si-muted);
        }

        .si-legend span {
            display: inline-flex;
            align-items: center;
            gap: .3rem;
        }

        .si-pill {
            margin-left: auto;
            padding: .15rem .6rem;
            border-radius: 99px;
            background: var(--si-line);
            color: var(--si-navy-2);
            font-size: .72rem;
            font-weight: 700;
        }

        .si-pill.is-complete {
            background: var(--si-success-soft);
            color: var(--si-success);
        }

        .si-table {
            max-height: calc(100vh - 225px);
            min-height: 200px;
            overflow: auto;
        }

        .si-table table {
            margin-bottom: 0;
        }

        .si-table thead th {
            position: sticky;
            top: 0;
            z-index: 1;
            padding: .45rem .75rem;
            border-bottom: 1px solid var(--si-border);
            background: #fafbfd;
            color: var(--si-faint);
            font-size: .66rem;
            font-weight: 600;
            letter-spacing: .06em;
            text-transform: uppercase;
            white-space: nowrap;
        }

        /* Teks panjang (panel, part) turun ke baris baru */
        .si-table tbody td {
            padding: .45rem .75rem;
            border-color: var(--si-line);
            color: var(--si-text);
            font-size: .84rem;
            vertical-align: middle;
            word-break: break-word;
        }

        .si-table .si-divider {
            border-left: 1px solid var(--si-border);
        }

        .si-panel {
            color: var(--si-muted);
            font-size: .8rem;
        }

        .si-part {
            font-weight: 600;
        }

        /* Bulatan status: nomor / centang / bintang */
        .si-dot {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 22px;
            height: 22px;
            border-radius: 50%;
            border: 1px solid var(--si-border);
            color: var(--si-faint);
            font-size: .68rem;
            font-weight: 600;
        }

        .si-dot.is-filled {
            border-color: var(--si-success);
            background: var(--si-success);
            color: #fff;
        }

        .si-dot.is-first {
            border-color: var(--si-first);
            background: var(--si-first);
            color: #fff;
        }

        .si-dot-legend {
            width: 14px;
            height: 14px;
            font-size: .5rem;
        }

        /* Baris terisi: latar + garis warna di kiri sebaris */
        tr.is-filled td {
            background: var(--si-success-soft);
        }

        tr.is-filled td:first-child {
            box-shadow: inset 4px 0 0 var(--si-success);
        }

        tr.is-first td {
            background: var(--si-first-soft);
        }

        tr.is-first td:first-child {
            box-shadow: inset 4px 0 0 var(--si-first);
        }

        .si-code {
            font-weight: 700;
            color: var(--si-navy);
            font-variant-numeric: tabular-nums;
        }

        .si-range {
            margin-left: .35rem;
            font-size: .72rem;
            color: var(--si-faint);
            white-space: nowrap;
        }

        .si-waiting {
            font-size: .76rem;
            font-style: italic;
            color: var(--si-faint);
        }

        .si-tag {
            display: inline-block;
            padding: .1rem .45rem;
            border-radius: 5px;
            font-size: .68rem;
            font-weight: 600;
            white-space: nowrap;
        }

        .si-tag-secondary {
            background: #f5f3ff;
            color: #6d28d9;
        }

        .si-tag-non {
            background: var(--si-line);
            color: var(--si-muted);
        }

        .si-tag-first {
            margin-left: .35rem;
            background: var(--si-first);
            color: #fff;
        }

        .btn-si-remove {
            width: 28px;
            height: 28px;
            padding: 0;
            border: none;
            border-radius: 6px;
            color: var(--si-faint);
            background: transparent;
        }

        .btn-si-remove:hover {
            background: #fef2f2;
            color: #dc2626;
        }

        /* Baris yang baru di-scan berkedip sebentar */
        @keyframes si-flash {
            0% { background: #bbf7d0; }
            100% { background: transparent; }
        }

        tr.is-new td {
            animation: si-flash 1.2s ease-out;
        }

        .si-empty {
            padding: 3rem 1rem;
            text-align: center;
            color: var(--si-muted);
        }

        .si-empty i {
            color: #cbd5e1;
        }

        @media (max-width: 991.98px) {
            .si-legend {
                display: none;
            }

            .si-qty {
                flex-basis: 100%;
                margin-left: 0;
                padding: .6rem .5rem 0;
                border-left: none;
                border-top: 1px solid var(--si-border);
            }

            #qty_set {
                flex: 1;
            }
        }

        @media (max-width: 767.98px) {
            .si-controls {
                flex-wrap: wrap;
            }

            .si-line-select {
                width: 100%;
            }

            #btn_camera span,
            .btn-si-history span {
                display: none;
            }

            .si-history-dialog {
                max-width: none;
                margin: .5rem;
            }

            .si-history-dialog .modal-content {
                height: calc(100vh - 1rem);
            }

            .si-history-icon {
                display: none;
            }

            .si-stat {
                flex: 1;
                min-width: 0;
            }
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid pb-3">
        {{-- Header: Line & Scan --}}
        <div class="si-header">
            <div class="si-brand">
                <span class="si-brand-icon"><i class="fa-solid fa-shirt"></i></span>
                <div>
                    <h5>Sewing In</h5>
                    <small><span id="si_date">-</span> &middot; <span id="si_time">--:--</span></small>
                </div>
            </div>

            <div class="si-controls">
                <div class="si-line-select">
                    <select class="form-select select2bs4" id="line_id" name="line_id" autocomplete="off">
                        <option value="">Pilih Line</option>
                        @foreach ($lines as $line)
                            <option value="{{ $line->line_id }}" data-line="{{ $line->username }}">{{ $line->FullName }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="input-group si-scan-group">
                    <span class="input-group-text is-disabled"><i class="fa-solid fa-barcode"></i></span>
                    <input type="text" class="form-control" id="kode_stocker" name="kode_stocker" placeholder="Pilih line dulu" autocomplete="off" disabled>
                    <button class="btn btn-si-camera" type="button" id="btn_camera" onclick="toggleCamera()" disabled>
                        <i class="fa-solid fa-camera"></i> <span>Kamera</span>
                    </button>
                </div>
                <button class="btn btn-si-history" type="button" onclick="openHistory()" title="Riwayat">
                    <i class="fa-solid fa-clock-rotate-left"></i> <span>Riwayat</span>
                </button>
            </div>

            <div class="d-none" id="camera_wrapper">
                <div id="stocker-reader"></div>
            </div>
        </div>

        {{-- Ringkasan WS & Qty Set (muncul setelah scan pertama) --}}
        <div class="si-summary d-none" id="info_bar">
            <div class="si-stat">
                <small>WS</small>
                <b id="info_ws">-</b>
            </div>
            <div class="si-stat">
                <small>Style</small>
                <b id="info_style">-</b>
            </div>
            <div class="si-stat">
                <small>Color / Size</small>
                <b><span id="info_color">-</span> &middot; <span id="info_size">-</span></b>
            </div>
            <div class="si-stat si-stat-progress">
                <small>Kelengkapan Part</small>
                <b id="part_progress_text">0 / 0</b>
                <div class="progress si-progress">
                    <div class="progress-bar" id="part_progress_bar" style="width: 0%"></div>
                </div>
            </div>

            <div class="si-qty">
                <label for="qty_set">Qty 1 Set <span class="text-lowercase fw-normal">(qty act terkecil)</span></label>
                <div class="si-qty-row">
                    <input type="text" class="form-control" id="qty_set" name="qty_set" placeholder="-" readonly tabindex="-1">
                    <button class="btn btn-si-apply text-nowrap" type="button" id="btn_apply" onclick="applyQty()" disabled>
                        <i class="fa-solid fa-check"></i> Apply
                    </button>
                    <button class="btn btn-si-ghost" type="button" onclick="resetScan()" title="Reset">
                        <i class="fa-solid fa-rotate-left"></i>
                    </button>
                </div>
                <div id="qty_set_note"></div>
            </div>
        </div>

        {{-- Sebelum scan --}}
        <div class="si-empty" id="empty_state">
            <i class="fa-solid fa-qrcode fa-3x mb-3"></i>
            <div class="fw-semibold">Belum ada stocker</div>
            <small>Pilih line, lalu scan stocker untuk memulai</small>
        </div>

        {{-- Part 1 Set: satu baris = satu part + stocker yang mengisinya --}}
        <div class="si-card d-none" id="list_panel">
            <div class="si-card-head">
                <h6>Part 1 Set</h6>
                <span class="si-legend">
                    <span><span class="si-dot si-dot-legend is-first"><i class="fa-solid fa-star"></i></span> Stocker pertama</span>
                    <span><span class="si-dot si-dot-legend is-filled"><i class="fa-solid fa-check"></i></span> Terisi</span>
                    <span><span class="si-dot si-dot-legend"></span> Menunggu scan</span>
                </span>
                <span class="si-pill" id="part_count">0 / 0</span>
            </div>
            <div class="si-table">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 48px;">#</th>
                            <th style="width: 28%;">Panel</th>
                            <th>Part</th>
                            <th>Tujuan</th>
                            <th class="si-divider">Stocker</th>
                            <th class="text-center">Size</th>
                            <th class="text-end">Sisa</th>
                            <th class="text-center" style="width: 160px;">Qty Act</th>
                            <th style="width: 44px;"></th>
                        </tr>
                    </thead>
                    <tbody id="part_list">
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Riwayat Sewing In (read-only) --}}
        <div class="modal fade" id="historyModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable si-history-dialog">
                <div class="modal-content si-history-modal">
                    <div class="modal-header si-history-head">
                        <div class="si-history-icon"><i class="fa-solid fa-clock-rotate-left"></i></div>
                        <div>
                            <h6 class="mb-0">Riwayat Sewing In</h6>
                            <small id="history_subtitle">Tap baris untuk melihat detail stocker</small>
                        </div>
                        <div class="si-tabs ms-auto me-3">
                            <button type="button" class="si-tab is-active" data-tab="riwayat" onclick="switchHistoryTab('riwayat')">Riwayat</button>
                            <button type="button" class="si-tab" data-tab="outstanding" onclick="switchHistoryTab('outstanding')">Outstanding</button>
                        </div>
                        <button type="button" class="btn-close btn-close-white ms-0" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>

                    <div class="si-history-filter">
                        <div class="row g-2 align-items-end">
                            <div class="col-6 col-md-2 si-only-riwayat">
                                <label for="history_dari">Dari</label>
                                <input type="date" class="form-control" id="history_dari" value="{{ date('Y-m-d') }}">
                            </div>
                            <div class="col-6 col-md-2 si-only-riwayat">
                                <label for="history_sampai">Sampai</label>
                                <input type="date" class="form-control" id="history_sampai" value="{{ date('Y-m-d') }}">
                            </div>
                            <div class="col-6 col-md-3">
                                <label for="history_line">Line</label>
                                <select class="form-select select2bs4-history" id="history_line">
                                    <option value="">Semua Line</option>
                                    @foreach ($lines as $line)
                                        <option value="{{ $line->line_id }}">{{ $line->FullName }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6 col-md-2 si-only-riwayat">
                                <label for="history_status">Status</label>
                                <select class="form-select select2bs4-history" id="history_status">
                                    <option value="">Semua</option>
                                    <option value="N" selected>Aktif</option>
                                    <option value="Y">Cancel</option>
                                </select>
                            </div>
                            <div class="col">
                                <label for="history_search">Cari</label>
                                <input type="text" class="form-control" id="history_search" placeholder="Kode / WS / style / color / stocker" autocomplete="off">
                            </div>
                        </div>
                    </div>

                    <div class="si-history-stats si-only-riwayat">
                        <div class="si-stat">
                            <i class="fa-solid fa-layer-group"></i>
                            <div><b id="history_total_set">0</b><small>Set</small></div>
                        </div>
                        <div class="si-stat is-success">
                            <i class="fa-solid fa-shirt"></i>
                            <div><b id="history_total_qty">0</b><small>Pcs</small></div>
                        </div>
                        <div class="si-stat is-danger">
                            <i class="fa-solid fa-ban"></i>
                            <div><b id="history_total_cancel">0</b><small>Cancel</small></div>
                        </div>
                    </div>
                    <div class="si-history-stats si-only-outstanding d-none">
                        <div class="si-stat">
                            <i class="fa-solid fa-ticket"></i>
                            <div><b id="outstanding_total_stocker">0</b><small>Stocker</small></div>
                        </div>
                        <div class="si-stat is-warning">
                            <i class="fa-solid fa-hourglass-half"></i>
                            <div><b id="outstanding_total_sisa">0</b><small>Sisa Pcs</small></div>
                        </div>
                    </div>

                    <div class="modal-body p-0">
                        <table class="table table-hover si-history-table mb-0 si-only-riwayat">
                            <thead>
                                <tr>
                                    <th style="width: 32px;"></th>
                                    <th>Kode</th>
                                    <th>Waktu</th>
                                    <th>Line</th>
                                    <th>WS / Style</th>
                                    <th>Color</th>
                                    <th class="text-center">Size</th>
                                    <th class="text-center">Part</th>
                                    <th class="text-end">Qty Set</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody id="history_list">
                            </tbody>
                        </table>

                        <table class="table table-hover si-history-table mb-0 si-only-outstanding d-none">
                            <thead>
                                <tr>
                                    <th>Stocker</th>
                                    <th>WS</th>
                                    <th>Color</th>
                                    <th class="text-center">Size</th>
                                    <th>Part</th>
                                    <th>Line</th>
                                    <th>Terakhir</th>
                                    <th class="text-end">Qty</th>
                                    <th class="text-end">Terpakai</th>
                                    <th class="text-end">Sisa</th>
                                </tr>
                            </thead>
                            <tbody id="outstanding_list">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Numpad qty act (tanpa keyboard tablet) --}}
        <div class="si-numpad-backdrop d-none" id="numpad" onclick="if (event.target === this) closeNumpad()">
            <div class="si-numpad">
                <div class="si-numpad-head">
                    <div>
                        <small>Qty Act</small>
                        <b id="numpad_title">-</b>
                    </div>
                    <span>maks <b id="numpad_max">-</b></span>
                </div>
                <div class="si-numpad-display" id="numpad_display">0</div>
                <div class="si-numpad-hint" id="numpad_hint"></div>
                <div class="si-numpad-keys">
                    @foreach (['7', '8', '9', '4', '5', '6', '1', '2', '3'] as $key)
                        <button type="button" onclick="numpadPress('{{ $key }}')">{{ $key }}</button>
                    @endforeach
                    <button type="button" class="is-muted" onclick="numpadPress('max')">Max</button>
                    <button type="button" onclick="numpadPress('0')">0</button>
                    <button type="button" class="is-muted" onclick="numpadPress('back')"><i class="fa-solid fa-delete-left"></i></button>
                </div>
                <div class="si-numpad-actions">
                    <button type="button" class="btn btn-si-ghost w-100" onclick="closeNumpad()">Batal</button>
                    <button type="button" class="btn btn-si-apply w-100" id="numpad_ok" onclick="numpadOk()">OK</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('custom-script')
    <!-- Select2 -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        // Setelah refresh line selalu kembali ke "Pilih Line" (browser kadang mengembalikan pilihan lama)
        $('#line_id').val('');

        $('.select2bs4').select2({
            theme: 'bootstrap4'
        })
    </script>

    <script>
        // Jam & tanggal di header
        function updateClock() {
            let now = new Date();

            $("#si_time").text(now.toLocaleTimeString("id-ID", { hour: "2-digit", minute: "2-digit" }));
            $("#si_date").text(now.toLocaleDateString("id-ID", { weekday: "short", day: "numeric", month: "short", year: "numeric" }));
        }

        updateClock();
        setInterval(updateClock, 30000);

        // Scan aktif setelah line dipilih
        $("#line_id").on("change", async function () {
            let lineSelected = !!$(this).val();

            $("#kode_stocker, #btn_camera").prop("disabled", !lineSelected);
            $(".si-scan-group .input-group-text").toggleClass("is-disabled", !lineSelected);
            $("#kode_stocker").attr("placeholder", lineSelected ? "Scan atau ketik kode stocker" : "Pilih line dulu");
            resetScan();

            if (!lineSelected) {
                await stopCamera();
            }
        });

        // Scanner gun biasanya mengirim Enter di akhir
        $("#kode_stocker").on("keydown", function (e) {
            if (e.key === "Enter") {
                e.preventDefault();
                scanStocker($(this).val().trim());
            }
        });

        function scanStocker(kodeStocker) {
            if (!kodeStocker) {
                return;
            }

            document.getElementById("loading").classList.remove("d-none");

            $.ajax({
                url: "{{ route('sewing-in-scan-stocker') }}",
                type: "get",
                data: {
                    kode_stocker: kodeStocker,
                    line_id: $("#line_id").val(),
                },
                success: function (res) {
                    document.getElementById("loading").classList.add("d-none");

                    if (res.status != 200) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: res.message,
                        });

                        return;
                    }

                    renderInfo(res);
                },
                error: function (jqXHR) {
                    document.getElementById("loading").classList.add("d-none");

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Terjadi kesalahan saat mengambil data stocker',
                    });
                },
                complete: function () {
                    $("#kode_stocker").val("").trigger("focus");
                }
            });
        }

        // Data scan (sementara di browser)
        var scannedStockers = [];
        var currentWs = null;
        var parts = [];
        var lastScannedId = null;

        function escapeHtml(text) {
            return $("<div>").text(text ?? "-").html();
        }

        function renderInfo(res) {
            let stocker = res.stocker;

            // Scan pertama menentukan WS, color, size & list part 1 set
            if (!currentWs) {
                currentWs = { ws: stocker.act_costing_ws, color: stocker.color, size: stocker.size };
                parts = res.parts;

                $("#info_ws").text(stocker.act_costing_ws);
                $("#info_style").text(res.style ?? "-");
                $("#info_color").text(stocker.color);
                $("#info_size").text(stocker.size);
            } else if (currentWs.ws != stocker.act_costing_ws || currentWs.color != stocker.color || currentWs.size != stocker.size) {
                // WS, color & size harus sama dengan stocker pertama
                Swal.fire({
                    icon: 'warning',
                    title: 'WS / Color / Size berbeda',
                    html: `
                        Stocker <b>${escapeHtml(stocker.id_qr_stocker)}</b>: ${escapeHtml(stocker.act_costing_ws)} / ${escapeHtml(stocker.color)} / ${escapeHtml(stocker.size)}<br>
                        Set ini: <b>${escapeHtml(currentWs.ws)} / ${escapeHtml(currentWs.color)} / ${escapeHtml(currentWs.size)}</b>
                    `,
                });

                return;
            }

            // Part stocker harus termasuk list part 1 set
            if (!parts.some(part => part.part_detail_id == stocker.part_detail_id)) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Part tidak termasuk set',
                    html: `Part <b>${escapeHtml(stocker.nama_part)}</b> dari stocker <b>${escapeHtml(stocker.id_qr_stocker)}</b> tidak ada di list part set ini.`,
                });

                // Stocker pertama ditolak, jangan jadikan patokan
                if (scannedStockers.length == 0) {
                    currentWs = null;
                    parts = [];
                }

                return;
            }

            if (scannedStockers.some(item => item.id_qr_stocker == stocker.id_qr_stocker)) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Stocker sudah di-scan',
                    showConfirmButton: false,
                    timer: 1500
                });

                return;
            }

            // Satu part hanya boleh satu stocker
            let partStocker = scannedStockers.find(item => item.part_detail_id == stocker.part_detail_id);

            if (partStocker) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Part sudah terisi',
                    html: `Part <b>${escapeHtml(stocker.nama_part)}</b> sudah ada stocker <b>${escapeHtml(partStocker.id_qr_stocker)}</b>.`,
                });

                return;
            }

            // Qty act awalnya = sisa qty stocker, tinggal diubah kalau beda
            stocker.qty_act = stocker.qty_sisa;

            scannedStockers.unshift(stocker);
            lastScannedId = stocker.id_qr_stocker;

            renderLists();
        }

        function removeStocker(idQrStocker) {
            scannedStockers = scannedStockers.filter(item => item.id_qr_stocker != idQrStocker);
            lastScannedId = null;

            // Semua stocker dihapus, WS / color / size boleh ganti lagi
            if (scannedStockers.length == 0) {
                resetScan();

                return;
            }

            renderLists();
        }

        function resetScan() {
            scannedStockers = [];
            currentWs = null;
            parts = [];
            lastScannedId = null;

            $("#info_bar, #list_panel").addClass("d-none");
            $("#empty_state").removeClass("d-none");
            $("#part_list").html("");
            $("#qty_set").val("");
            $("#btn_apply").prop("disabled", true);
            $("#kode_stocker").val("").trigger("focus");
            closeNumpad();
        }

        // Qty act valid: bilangan bulat 1 s/d sisa qty stocker
        function isValidQtyAct(stocker) {
            let qtyAct = Number(stocker.qty_act);

            return Number.isInteger(qtyAct) && qtyAct >= 1 && qtyAct <= Number(stocker.qty_sisa);
        }

        // Qty 1 Set = qty act terkecil, Apply aktif kalau set lengkap & semua qty act valid
        function updateQtySet() {
            let partFilled = parts.filter(part => scannedStockers.some(stocker => stocker.part_detail_id == part.part_detail_id)).length;
            let setComplete = parts.length > 0 && partFilled == parts.length;
            let invalid = scannedStockers.filter(stocker => !isValidQtyAct(stocker));
            let qtySet = scannedStockers.length > 0 ? Math.min(...scannedStockers.map(stocker => Number(stocker.qty_act) || 0)) : 0;

            $("#qty_set").val(scannedStockers.length > 0 ? qtySet : "");
            $("#btn_apply").prop("disabled", !(setComplete && invalid.length == 0 && qtySet > 0));

            if (invalid.length > 0) {
                $("#qty_set_note").html(`<span class="text-danger fw-semibold"><i class="fa-solid fa-triangle-exclamation"></i> Qty act harus 1 s/d sisa stocker (${invalid.length} baris)</span>`);
            } else if (!setComplete) {
                $("#qty_set_note").html(`<span class="text-muted"><i class="fa-solid fa-lock"></i> Lengkapi semua part dulu (${partFilled} / ${parts.length})</span>`);
            } else {
                $("#qty_set_note").html('<span class="fw-semibold" style="color: var(--si-success)"><i class="fa-solid fa-circle-check"></i> 1 set lengkap, siap Apply</span>');
            }
        }

        // ===== Qty act: tombol − / + dan numpad (tanpa keyboard tablet) =====
        function qtyActStepper(stocker) {
            let qtyAct = Number(stocker.qty_act);
            let diff = qtyAct - Number(stocker.qty_sisa);
            let id = escapeHtml(stocker.id_qr_stocker);

            return `
                <div class="si-stepper ${diff != 0 ? 'is-changed' : ''}">
                    <button class="si-step-btn" type="button" onclick="stepQtyAct('${id}', -1)" ${qtyAct <= 1 ? 'disabled' : ''}><i class="fa-solid fa-minus"></i></button>
                    <button class="si-step-value" type="button" onclick="openNumpad('${id}')" title="Ketik qty">${qtyAct}</button>
                    <button class="si-step-btn" type="button" onclick="stepQtyAct('${id}', 1)" ${qtyAct >= Number(stocker.qty_sisa) ? 'disabled' : ''}><i class="fa-solid fa-plus"></i></button>
                </div>
                ${diff != 0 ? `<div class="si-step-diff">${diff} dari sisa ${escapeHtml(stocker.qty_sisa)}</div>` : ''}
            `;
        }

        function setQtyAct(idQrStocker, qtyAct) {
            let stocker = scannedStockers.find(item => item.id_qr_stocker == idQrStocker);

            if (!stocker) {
                return;
            }

            // Qty act 1 s/d sisa qty stocker
            stocker.qty_act = Math.min(Math.max(1, Number(qtyAct) || 1), Number(stocker.qty_sisa));
            lastScannedId = null;

            renderLists();

            // Lepas fokus dari tombol supaya hasil scan berikutnya ditangkap ke kolom scan
            document.activeElement?.blur();
        }

        function stepQtyAct(idQrStocker, step) {
            let stocker = scannedStockers.find(item => item.id_qr_stocker == idQrStocker);

            if (stocker) {
                setQtyAct(idQrStocker, Number(stocker.qty_act) + step);
            }
        }

        // Numpad
        var numpadStocker = null;
        var numpadValue = "";
        var numpadFresh = true;

        function openNumpad(idQrStocker) {
            numpadStocker = scannedStockers.find(item => item.id_qr_stocker == idQrStocker);

            if (!numpadStocker) {
                return;
            }

            numpadValue = String(numpadStocker.qty_act);
            numpadFresh = true;

            $("#numpad_title").text(numpadStocker.id_qr_stocker + " · " + (numpadStocker.nama_part ?? "-"));
            $("#numpad_max").text(numpadStocker.qty_sisa);
            renderNumpad();

            $("#numpad").removeClass("d-none");
            document.activeElement?.blur();
        }

        function closeNumpad() {
            numpadStocker = null;
            $("#numpad").addClass("d-none");
        }

        function renderNumpad() {
            let value = Number(numpadValue);
            let valid = numpadValue !== "" && value >= 1 && value <= Number(numpadStocker.qty_sisa);

            $("#numpad_display").text(numpadValue || "0").toggleClass("is-invalid", numpadValue !== "" && !valid);
            $("#numpad_hint").text(numpadValue === "" ? "Ketik qty" : valid ? (value - Number(numpadStocker.qty_sisa) == 0 ? "Sama dengan sisa stocker" : (value - Number(numpadStocker.qty_sisa)) + " dari sisa stocker") : "Qty harus 1 s/d " + numpadStocker.qty_sisa);
            $("#numpad_ok").prop("disabled", !valid);
        }

        function numpadPress(key) {
            if (!numpadStocker) {
                return;
            }

            if (key == "back") {
                numpadValue = numpadFresh ? "" : numpadValue.slice(0, -1);
            } else if (key == "clear") {
                numpadValue = "";
            } else if (key == "max") {
                numpadValue = String(numpadStocker.qty_sisa);
            } else {
                // Tekan angka pertama langsung mengganti nilai lama
                numpadValue = (numpadFresh ? "" : numpadValue) + key;
                numpadValue = String(Number(numpadValue)).slice(0, 6);
            }

            numpadFresh = false;
            renderNumpad();
        }

        function numpadOk() {
            if (!numpadStocker || $("#numpad_ok").prop("disabled")) {
                return;
            }

            let idQrStocker = numpadStocker.id_qr_stocker;

            closeNumpad();
            setQtyAct(idQrStocker, numpadValue);
        }

        // ===== Tangkap hasil scanner ke kolom scan, di mana pun fokusnya =====
        $(document).on("keydown", function (e) {
            // Numpad terbuka: keyboard fisik dipakai untuk numpad
            if (numpadStocker) {
                if (/^[0-9]$/.test(e.key)) numpadPress(e.key);
                else if (e.key == "Backspace") numpadPress("back");
                else if (e.key == "Enter") numpadOk();
                else if (e.key == "Escape") closeNumpad();
                else return;

                e.preventDefault();
                return;
            }

            // Riwayat terbuka: Esc untuk tutup, jangan tangkap ke kolom scan
            if (historyOpen) {
                return;
            }

            let target = e.target;
            let scanInput = $("#kode_stocker");

            if (target.id == "kode_stocker" || $(target).is("input, textarea, select") || target.isContentEditable) {
                return;
            }

            if (scanInput.prop("disabled") || Swal.isVisible() || e.ctrlKey || e.altKey || e.metaKey) {
                return;
            }

            if (e.key.length == 1) {
                e.preventDefault();
                scanInput.val(scanInput.val() + e.key);
            } else if (e.key == "Enter" && scanInput.val()) {
                e.preventDefault();
                scanStocker(scanInput.val().trim());
            }
        });

        function applyQty() {
            let qty = Number($("#qty_set").val());

            Swal.fire({
                icon: 'question',
                title: 'Apply Qty Set?',
                html: `
                    <div class="text-start">
                        Line: <b>${escapeHtml($("#line_id option:selected").text())}</b><br>
                        WS: <b>${escapeHtml(currentWs.ws)}</b> / ${escapeHtml(currentWs.color)} / ${escapeHtml(currentWs.size)}<br>
                        Stocker: <b>${scannedStockers.length}</b> (${parts.length} part)<br>
                        Qty Set: <b>${qty}</b>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Apply',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#059669',
            }).then((result) => {
                if (result.isConfirmed) {
                    saveSewingIn();
                }
            });
        }

        function saveSewingIn() {
            document.getElementById("loading").classList.remove("d-none");
            $("#btn_apply").prop("disabled", true);

            $.ajax({
                url: "{{ route('sewing-in-store') }}",
                type: "post",
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
                },
                data: {
                    line_id: $("#line_id").val(),
                    // Urutan scan: stocker pertama di index 0
                    stockers: scannedStockers.slice().reverse().map(stocker => ({
                        id_qr_stocker: stocker.id_qr_stocker,
                        qty_act: stocker.qty_act,
                    })),
                },
                success: function (res) {
                    // Antrian simpan masih penuh: tunggu sebentar lalu kirim ulang otomatis
                    if (res.busy) {
                        setTimeout(saveSewingIn, 500);

                        return;
                    }

                    document.getElementById("loading").classList.add("d-none");

                    if (res.status != 200) {
                        updateQtySet();

                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: res.message,
                        });

                        return;
                    }

                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        html: `Sewing In <b>${escapeHtml(res.kode)}</b> berhasil disimpan.`,
                        showConfirmButton: false,
                        timer: 1800
                    });

                    resetScan();
                },
                error: function (jqXHR) {
                    document.getElementById("loading").classList.add("d-none");
                    updateQtySet();

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: jqXHR.responseJSON?.message ?? 'Terjadi kesalahan saat menyimpan Sewing In',
                    });
                }
            });
        }

        function tujuanTag(tujuan) {
            return tujuan == "secondary" ?
                `<span class="si-tag si-tag-secondary">Secondary</span>` :
                `<span class="si-tag si-tag-non">Non Secondary</span>`;
        }

        function renderLists() {
            // Stocker pertama (patokan WS / color / size) ada di urutan paling akhir
            let firstStocker = scannedStockers[scannedStockers.length - 1];

            // Satu baris = satu part + stocker yang mengisinya
            let partRows = "";
            let partFilled = 0;

            parts.forEach((part, i) => {
                let partStocker = scannedStockers.find(stocker => stocker.part_detail_id == part.part_detail_id);
                let isFirst = partStocker && partStocker == firstStocker;
                let isNew = partStocker && partStocker.id_qr_stocker == lastScannedId;

                if (partStocker) {
                    partFilled++;
                }

                let statusDot = isFirst ? '<span class="si-dot is-first"><i class="fa-solid fa-star"></i></span>' :
                    partStocker ? '<span class="si-dot is-filled"><i class="fa-solid fa-check"></i></span>' :
                    `<span class="si-dot">${i + 1}</span>`;

                let stockerCells = partStocker ? `
                    <td class="si-divider">
                        <span class="si-code">${escapeHtml(partStocker.id_qr_stocker)}</span><span class="si-range">${escapeHtml(partStocker.stocker_range)}</span>${isFirst ? '<span class="si-tag si-tag-first">Pertama</span>' : ''}
                    </td>
                    <td class="text-center fw-semibold">${escapeHtml(partStocker.size)}</td>
                    <td class="text-end">
                        <span class="fw-bold">${escapeHtml(partStocker.qty_sisa)}</span>
                        ${Number(partStocker.qty_used) > 0 ? `<span class="si-qty-origin">sisa dari ${escapeHtml(partStocker.qty)}</span>` : ''}
                    </td>
                    <td class="text-center">${qtyActStepper(partStocker)}</td>
                    <td class="text-center">
                        <button class="btn btn-si-remove" type="button" onclick="removeStocker('${escapeHtml(partStocker.id_qr_stocker)}')" title="Hapus stocker">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </td>
                ` : `
                    <td class="si-divider" colspan="5"><span class="si-waiting">Menunggu scan</span></td>
                `;

                partRows += `
                    <tr class="${isFirst ? 'is-first' : partStocker ? 'is-filled' : ''} ${isNew ? 'is-new' : ''}">
                        <td class="text-center">${statusDot}</td>
                        <td class="si-panel">${escapeHtml(part.panel)}</td>
                        <td class="si-part">${escapeHtml(part.nama_part)}</td>
                        <td>${tujuanTag(part.tujuan)}</td>
                        ${stockerCells}
                    </tr>
                `;
            });

            $("#part_list").html(partRows || `<tr><td colspan="9" class="si-empty">Part tidak ditemukan</td></tr>`);

            // Kelengkapan part
            let setComplete = parts.length > 0 && partFilled == parts.length;
            let percent = parts.length > 0 ? Math.round(partFilled / parts.length * 100) : 0;

            $("#part_count, #part_progress_text").text(partFilled + " / " + parts.length);
            $("#part_count").toggleClass("is-complete", setComplete);
            $("#part_progress_bar").css("width", percent + "%");

            updateQtySet();

            $("#empty_state").addClass("d-none");
            $("#info_bar, #list_panel").removeClass("d-none");
        }

        // ===== Riwayat (modal, read-only) =====
        var historyOpen = false;
        var historyData = [];

        $('.select2bs4-history').select2({
            theme: 'bootstrap4',
            dropdownParent: $('#historyModal')
        });

        $("#historyModal").on("shown.bs.modal", () => historyOpen = true);
        $("#historyModal").on("hidden.bs.modal", () => historyOpen = false);

        function openHistory() {
            // Default filter line = line yang sedang dipilih
            $("#history_line").val($("#line_id").val()).trigger("change.select2");

            bootstrap.Modal.getOrCreateInstance(document.getElementById("historyModal")).show();

            switchHistoryTab("riwayat");
            loadHistory();
        }

        // Tanggal & line diambil ulang dari server, status & cari difilter langsung
        $("#history_dari, #history_sampai").on("change", loadHistory);
        $("#history_line").on("change", () => historyTab == "riwayat" ? loadHistory() : loadOutstanding());
        $("#history_status").on("change", renderHistory);
        $("#history_search").on("input", () => historyTab == "riwayat" ? renderHistory() : renderOutstanding());

        function loadHistory() {
            $("#history_list").html(`<tr><td colspan="10" class="si-empty"><i class="fa-solid fa-spinner fa-spin fa-2x mb-2"></i><div>Memuat...</div></td></tr>`);

            $.ajax({
                url: "{{ route('sewing-in-history') }}",
                type: "get",
                data: {
                    tanggal_awal: $("#history_dari").val(),
                    tanggal_akhir: $("#history_sampai").val(),
                    line_id: $("#history_line").val(),
                },
                success: function (res) {
                    historyData = res.data;
                    renderHistory();
                },
                error: function (jqXHR) {
                    historyData = [];
                    let errors = Object.values(jqXHR.responseJSON?.errors ?? {}).flat();

                    $("#history_list").html(`<tr><td colspan="10" class="si-empty text-danger">${escapeHtml(errors[0] ?? 'Gagal memuat riwayat')}</td></tr>`);
                }
            });
        }

        function renderHistory() {
            let status = $("#history_status").val();
            let search = $("#history_search").val().trim().toLowerCase();

            let rows = historyData.filter(item => {
                if (status && item.cancel != status) {
                    return false;
                }

                if (!search) {
                    return true;
                }

                let text = [item.kode, item.act_costing_ws, item.style, item.color, item.size, item.nama_line]
                    .concat(item.details.map(detail => detail.id_qr_stocker))
                    .join(" ")
                    .toLowerCase();

                return text.includes(search);
            });

            let html = rows.map(item => {
                let isCancel = item.cancel == "Y";
                let created = item.created_at ?? "";

                let detailRows = item.details.map(detail => `
                    <tr>
                        <td><span class="si-code">${escapeHtml(detail.id_qr_stocker)}</span></td>
                        <td>${escapeHtml(detail.nama_part)}</td>
                        <td class="text-end">${escapeHtml(detail.qty_stocker ?? '-')}</td>
                        <td class="text-end fw-bold text-primary">${escapeHtml(detail.qty_act)}</td>
                    </tr>
                `).join("");

                return `
                    <tr class="si-history-row ${isCancel ? 'is-cancel' : ''}" onclick="toggleHistoryRow(this)">
                        <td class="text-center"><i class="fa-solid fa-chevron-right si-caret"></i></td>
                        <td><span class="si-code">${escapeHtml(item.kode)}</span></td>
                        <td class="text-nowrap">
                            <div class="fw-semibold">${escapeHtml(created.substring(11, 16))}</div>
                            <small class="si-sub">${escapeHtml(created.substring(8, 10) + "/" + created.substring(5, 7) + "/" + created.substring(0, 4))}</small>
                        </td>
                        <td><span class="si-line-chip">${escapeHtml(item.nama_line)}</span></td>
                        <td>
                            <div class="fw-semibold text-nowrap">${escapeHtml(item.act_costing_ws)}</div>
                            <small class="si-sub">${escapeHtml(item.style ?? '-')}</small>
                        </td>
                        <td>${escapeHtml(item.color)}</td>
                        <td class="text-center"><span class="si-size">${escapeHtml(item.size)}</span></td>
                        <td class="text-center si-sub">${escapeHtml(item.total_part)} part</td>
                        <td class="text-end"><span class="si-qty-set">${escapeHtml(item.qty_set)}</span></td>
                        <td class="text-center"><span class="si-status ${isCancel ? 'is-cancel' : 'is-active'}">${isCancel ? 'Cancel' : 'Aktif'}</span></td>
                    </tr>
                    <tr class="si-history-detail d-none">
                        <td colspan="10">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Stocker</th>
                                        <th>Part</th>
                                        <th class="text-end">Qty Stocker</th>
                                        <th class="text-end">Terpakai</th>
                                    </tr>
                                </thead>
                                <tbody>${detailRows}</tbody>
                            </table>
                        </td>
                    </tr>
                `;
            }).join("");

            $("#history_list").html(html || `<tr><td colspan="10" class="si-empty"><i class="fa-regular fa-folder-open fa-2x mb-2"></i><div>Tidak ada transaksi</div></td></tr>`);

            // Ringkasan dari data yang tampil
            let active = rows.filter(item => item.cancel != "Y");

            $("#history_total_set").text(active.length);
            $("#history_total_qty").text(active.reduce((total, item) => total + Number(item.qty_set), 0));
            $("#history_total_cancel").text(rows.length - active.length);
        }

        function toggleHistoryRow(row) {
            $(row).toggleClass("is-open").next().toggleClass("d-none");
        }

        // ===== Outstanding: stocker yang sudah masuk Sewing In tapi masih ada sisa =====
        var historyTab = "riwayat";
        var outstandingData = [];

        function switchHistoryTab(tab) {
            historyTab = tab;

            $(".si-tab").removeClass("is-active").filter(`[data-tab="${tab}"]`).addClass("is-active");
            $(".si-only-riwayat").toggleClass("d-none", tab != "riwayat");
            $(".si-only-outstanding").toggleClass("d-none", tab != "outstanding");
            $("#history_subtitle").text(tab == "riwayat" ? "Tap baris untuk melihat detail stocker" : "Stocker yang sudah masuk Sewing In tapi qty-nya masih ada sisa");
            $("#history_search").attr("placeholder", tab == "riwayat" ? "Kode / WS / style / color / stocker" : "Stocker / WS / color / part");

            if (tab == "outstanding") {
                loadOutstanding();
            }
        }

        function loadOutstanding() {
            $("#outstanding_list").html(`<tr><td colspan="10" class="si-empty"><i class="fa-solid fa-spinner fa-spin fa-2x mb-2"></i><div>Memuat...</div></td></tr>`);

            $.ajax({
                url: "{{ route('sewing-in-outstanding') }}",
                type: "get",
                data: {
                    line_id: $("#history_line").val(),
                },
                success: function (res) {
                    outstandingData = res.data;
                    renderOutstanding();
                },
                error: function () {
                    outstandingData = [];
                    $("#outstanding_list").html(`<tr><td colspan="10" class="si-empty text-danger">Gagal memuat outstanding</td></tr>`);
                }
            });
        }

        function renderOutstanding() {
            let search = $("#history_search").val().trim().toLowerCase();

            let rows = outstandingData.filter(item => !search ||
                [item.id_qr_stocker, item.act_costing_ws, item.color, item.size, item.nama_part, item.nama_line].join(" ").toLowerCase().includes(search));

            let html = rows.map(item => {
                let last = item.last_at ?? "";

                return `
                    <tr>
                        <td><span class="si-code">${escapeHtml(item.id_qr_stocker)}</span></td>
                        <td class="text-nowrap">${escapeHtml(item.act_costing_ws)}</td>
                        <td>${escapeHtml(item.color)}</td>
                        <td class="text-center fw-semibold">${escapeHtml(item.size)}</td>
                        <td>${escapeHtml(item.nama_part)}</td>
                        <td>${escapeHtml(item.nama_line)}</td>
                        <td class="text-nowrap">${escapeHtml(last.substring(8, 10) + "/" + last.substring(5, 7))} <span class="text-muted">${escapeHtml(last.substring(11, 16))}</span></td>
                        <td class="text-end">${escapeHtml(item.qty)}</td>
                        <td class="text-end">${escapeHtml(item.qty_used)}</td>
                        <td class="text-end"><span class="si-sisa">${escapeHtml(item.qty_sisa)}</span></td>
                    </tr>
                `;
            }).join("");

            $("#outstanding_list").html(html || `<tr><td colspan="10" class="si-empty"><i class="fa-regular fa-circle-check fa-2x mb-2"></i><div>Tidak ada stocker yang masih ada sisa</div></td></tr>`);
            $("#outstanding_total_stocker").text(rows.length);
            $("#outstanding_total_sisa").text(rows.reduce((total, item) => total + Number(item.qty_sisa), 0));
        }

        // Kamera (opsional)
        var stockerScanner = new Html5Qrcode("stocker-reader");
        var cameraActive = false;

        async function toggleCamera() {
            if (cameraActive) {
                await stopCamera();
            } else {
                await startCamera();
            }
        }

        async function startCamera() {
            $("#camera_wrapper").removeClass("d-none");

            try {
                await stockerScanner.start({ facingMode: "environment" }, { fps: 10, qrbox: { width: 250, height: 250 } }, (decodedText) => {
                    scanStocker(decodedText);
                    stopCamera();
                });

                cameraActive = true;
                $("#btn_camera span").text("Tutup Kamera");
            } catch (err) {
                $("#camera_wrapper").addClass("d-none");

                Swal.fire({
                    icon: 'error',
                    title: 'Kamera tidak bisa dibuka',
                    text: err,
                });
            }
        }

        async function stopCamera() {
            if (cameraActive) {
                await stockerScanner.stop();
                stockerScanner.clear();
                cameraActive = false;
            }

            $("#camera_wrapper").addClass("d-none");
            $("#btn_camera span").text("Kamera");
        }
    </script>
@endsection
