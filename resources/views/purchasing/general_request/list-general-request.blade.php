@extends('layouts.index')

@section('custom-link')
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

    <style>
        /* ================= Palet kalem ================= */
        .gr-page,
        #modalTracking,
        #modalBuat {
            --gr-ink: #2f3a4a;
            --gr-muted: #8a94a6;
            --gr-line: #e8ecf2;
            --gr-soft: #f6f8fb;
            --gr-accent: #4a6fa5;
            --gr-accent-soft: #e9f0fa;
            color: var(--gr-ink);
        }

        /* ================= Latar cool (hanya halaman ini) ================= */
        body,
        .content-wrapper {
            background-color: #e9eff6 !important;
        }

        .content-wrapper {
            background-image:
                radial-gradient(circle at 8% 12%, rgba(134, 165, 211, .22) 0, transparent 38%),
                radial-gradient(circle at 92% 30%, rgba(124, 195, 155, .14) 0, transparent 35%),
                radial-gradient(circle at 50% 100%, rgba(138, 124, 194, .12) 0, transparent 45%),
                linear-gradient(160deg, #edf2f8 0%, #e6edf5 55%, #eaeef6 100%) !important;
            background-attachment: fixed !important;
        }

        .gr-page .card {
            background: rgba(247, 249, 252, .88);
            border: 1px solid #dde5ef;
            border-radius: 16px;
            box-shadow: 0 6px 24px rgba(63, 95, 143, .08);
            overflow: hidden;
        }

        .gr-page .card-header.bg-sb {
            background: linear-gradient(135deg, #3f5f8f 0%, #5b7fb5 60%, #7b9bcb 100%) !important;
            border: 0;
        }

        .gr-page .alert-warning {
            background: #f6efdc;
            border-color: #ecdfb9;
            color: #8a6d2b;
            border-radius: 12px;
        }

        .gr-page .gr-stat {
            background: #f9fbfd;
            border-color: #dde5ef;
        }

        .gr-page .gr-filter {
            background: #eef3f9;
            border-color: #dde5ef;
        }

        .gr-page .gr-filter .form-control,
        .gr-page .gr-filter .input-group-text,
        .gr-page .gr-filter .select2-container--bootstrap4 .select2-selection {
            background-color: #f9fbfd;
            border-color: #d9e2ed;
        }

        .gr-page .gr-table-wrap {
            border-color: #dde5ef;
            background: #f9fbfd;
        }

        .gr-page #table-request thead th {
            background: #e8eef6;
            color: #4f5d72;
            border-bottom-color: #dde5ef;
        }

        .gr-page #table-request tbody tr.gr-row {
            background: #f9fbfd;
        }

        .gr-page #table-request tbody td {
            border-bottom-color: #e3e9f1;
        }

        .gr-page #table-request tbody tr.gr-row:hover {
            background: #f1f5fa;
        }

        .gr-page #table-request tbody tr.gr-row.shown {
            background: #e6eef8;
        }

        .gr-page .gr-child {
            background: #f2f6fb;
        }

        .gr-page .gr-ket-btn {
            background: #f9fbfd;
            border-color: #d9e2ed;
        }

        /* Modal ikut kalem */
        #modalTracking .modal-content,
        #modalKeterangan .modal-body {
            background: #f1f5fa;
        }

        #modalTracking .gr-approval,
        #modalTracking .gr-item-card,
        #modalTracking .gr-tile,
        #modalTracking .gr-item-search input,
        .gr-ket-panel {
            background: #f9fbfd;
            border-color: #dde5ef;
        }

        #modalTracking .gr-detail {
            background-color: #f9fbfd;
            border-color: #dde5ef;
            background-image: radial-gradient(#e2e8f1 1px, transparent 1.2px);
        }

        #modalTracking .gr-timeline li .tl-ic {
            box-shadow: 0 0 0 3px #f9fbfd;
        }

        #modalTracking .gr-item-card.active {
            background: var(--c-bg);
            border-color: var(--c-fg);
        }

        /* ================= Modal Buat Request ================= */
        #modalBuat .modal-body {
            background: #f1f5fa;
            padding: 18px 22px;
        }

        .gr-bf-card {
            background: #f9fbfd;
            border: 1px solid #dde5ef;
            border-radius: 14px;
            padding: 12px 14px;
            transition: box-shadow .2s ease, border-color .2s ease;
        }

        .gr-bf label {
            font-size: 12px;
            font-weight: 600;
            color: var(--gr-muted);
            margin-bottom: 3px;
        }

        .gr-bf .form-control,
        .gr-bf .select2-container--bootstrap4 .select2-selection {
            border-color: #d9e2ed;
            border-radius: 8px;
            background-color: #fff;
        }

        .gr-bf .form-control:focus {
            border-color: var(--gr-accent);
            box-shadow: 0 0 0 3px var(--gr-accent-soft);
        }

        .gr-bf .form-control[readonly] {
            background: #eef3f9;
        }

        .gr-bf-entry:focus-within {
            border-color: var(--gr-accent);
            box-shadow: 0 0 0 3px var(--gr-accent-soft);
        }

        .gr-bf-qty .btn {
            width: 32px;
            background: #eef3f9;
            border: 1px solid #d9e2ed;
            color: var(--gr-accent);
            font-weight: 700;
        }

        .gr-bf-qty .btn:hover {
            background: var(--gr-accent);
            color: #fff;
        }

        .gr-bf-qty .input-group-prepend .btn {
            border-radius: 8px 0 0 8px;
        }

        .gr-bf-qty .input-group-append .btn {
            border-radius: 0 8px 8px 0;
        }

        .gr-bf-qty .form-control {
            border-radius: 0 !important;
        }

        /* Tinggi & huruf kontrol diseragamkan (input, select2, tombol qty) */
        .gr-bf .form-control,
        .gr-bf .input-group-sm>.form-control {
            height: 36px;
            font-size: 13.5px;
            padding: 6px 10px;
            color: #2f3a4a;
        }

        .gr-bf .form-control::placeholder {
            color: #a3aebe;
        }

        .gr-bf .select2-container--bootstrap4 .select2-selection--single {
            height: 36px !important;
        }

        .gr-bf .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
            line-height: 34px;
            font-size: 13.5px;
            padding-left: 10px;
            padding-right: 24px;
            color: #2f3a4a;
        }

        .gr-bf .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow {
            height: 34px;
        }

        .gr-bf .select2-container--bootstrap4.select2-container--focus .select2-selection,
        .gr-bf .select2-container--bootstrap4.select2-container--open .select2-selection {
            border-color: var(--gr-accent);
            box-shadow: 0 0 0 3px var(--gr-accent-soft);
        }

        .gr-bf .select2-container--bootstrap4 .select2-selection--single {
            position: relative;
        }

        .gr-bf .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow {
            position: absolute;
            top: 1px;
            right: 6px;
            height: 34px;
            width: 20px;
        }

        .gr-bf .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow b {
            top: 50%;
        }

        .gr-bf-qty .btn {
            height: 36px;
            padding: 0;
        }

        /* Teks pilihan select2 tidak dipotong: turun baris kalau panjang */
        .gr-bf .select2-container--bootstrap4 .select2-selection--single {
            height: auto !important;
            min-height: 36px;
        }

        .gr-bf .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
            white-space: normal;
            overflow: visible;
            text-overflow: clip;
            line-height: 1.35;
            padding-top: 8px;
            padding-bottom: 8px;
        }

        /* Satuan: tampil saja, ikut master item */
        .gr-satuan-box {
            display: flex;
            align-items: center;
            gap: 8px;
            height: 36px;
            padding: 0 11px;
            border: 1px dashed #cfd9e6;
            border-radius: 8px;
            background: #eef3f9;
            font-size: 13.5px;
            font-weight: 600;
            color: #2f3a4a;
        }

        .gr-satuan-box i {
            color: var(--gr-accent);
            font-size: 12px;
        }

        .gr-satuan-box .kosong {
            font-weight: 400;
            color: #a3aebe;
        }

        .gr-satuan-box .salah {
            font-weight: 500;
            color: #c0505f;
        }

        /* Daftar item: tgl dibutuhkan, qty + satuan */
        .gr-bf-item .tgl {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-shrink: 0;
            width: 132px;
            border-radius: 9px;
            padding: 5px 9px;
            background: #e8f4ed;
            color: #4a8c68;
        }

        .gr-bf-item .tgl.soon {
            background: #fdf1e4;
            color: #c27a3a;
        }

        .gr-bf-item .tgl i {
            font-size: 14px;
        }

        .gr-bf-item .tgl b {
            display: block;
            font-size: 12px;
            line-height: 1.2;
            color: #2f3a4a;
            font-weight: 600;
        }

        .gr-bf-item .tgl span {
            display: block;
            font-size: 10.5px;
            line-height: 1.2;
            font-weight: 600;
        }

        .gr-bf-item .qwrap {
            display: flex;
            align-items: center;
            flex-shrink: 0;
            border: 1px solid #d9e2ed;
            border-radius: 8px;
            background: #fff;
            overflow: hidden;
        }

        .gr-bf-item .qwrap:focus-within {
            border-color: var(--gr-accent);
            box-shadow: 0 0 0 3px var(--gr-accent-soft);
        }

        .gr-bf-item .qwrap .q {
            border: 0;
            border-radius: 0;
            width: 60px;
            height: 32px;
            box-shadow: none !important;
        }

        .gr-bf-item .qwrap .st {
            width: auto;
            min-width: 44px;
            height: 32px;
            line-height: 32px;
            padding: 0 8px;
            background: #eef3f9;
            border-left: 1px solid #d9e2ed;
            text-align: center;
            font-weight: 600;
        }

        .gr-bf-item .nm small {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        @media (max-width: 575px) {
            .gr-bf-item {
                flex-wrap: wrap;
            }

            .gr-bf-item .nm {
                flex-basis: calc(100% - 40px);
            }
        }

        .gr-bf label {
            display: block;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-bottom: 4px;
        }

        .gr-opsional {
            font-weight: 500;
            font-size: 10.5px;
            color: #8a96a8;
            background: #eef3f9;
            border-radius: 10px;
            padding: 1px 7px;
            margin-left: 2px;
        }

        .gr-row-rapat {
            margin-left: -6px;
            margin-right: -6px;
        }

        .gr-row-rapat>[class*="col"] {
            padding-left: 6px;
            padding-right: 6px;
        }

        #modalBuat .gr-bf-card {
            padding: 14px 16px;
        }

        #modalBuat .gr-bf-entry .mb-2 {
            margin-bottom: 12px !important;
        }

        .gr-bf-aksi {
            border-top: 1px dashed #dde5ef;
            margin-top: 2px;
            padding-top: 12px;
        }

        .gr-bf-aksi .gr-btn-main {
            padding: 6px 16px;
        }

        .gr-bf-footer {
            background: #f1f5fa;
            border-top: 1px solid #dde5ef;
        }

        .gr-btn-soft {
            background: var(--gr-accent-soft);
            color: var(--gr-accent);
            border: 0;
            border-radius: 10px;
            transition: background .2s ease, color .2s ease;
        }

        .gr-btn-soft:hover {
            background: var(--gr-accent);
            color: #fff;
        }

        .gr-bf-list {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .gr-bf-item {
            display: flex;
            align-items: center;
            gap: 12px;
            background: #f9fbfd;
            border: 1px solid #dde5ef;
            border-radius: 12px;
            padding: 10px 12px;
            transition: transform .2s ease, box-shadow .2s ease;
        }

        .gr-bf-item:hover {
            transform: translateX(3px);
            box-shadow: 0 6px 14px rgba(47, 58, 74, .06);
        }

        .gr-bf-item.baru {
            animation: gr-bf-in .6s ease;
        }

        .gr-bf-item.keluar {
            animation: gr-bf-out .28s ease forwards;
        }

        .gr-bf-item.flash {
            animation: gr-bf-flash .8s ease;
        }

        @keyframes gr-bf-in {
            0% {
                opacity: 0;
                transform: translateX(-14px);
                background: #e6f3eb;
            }

            60% {
                opacity: 1;
                transform: none;
                background: #e6f3eb;
            }

            100% {
                background: #f9fbfd;
            }
        }

        @keyframes gr-bf-out {
            to {
                opacity: 0;
                transform: translateX(24px);
            }
        }

        @keyframes gr-bf-flash {

            0%,
            100% {
                background: #f9fbfd;
            }

            30% {
                background: #fbf3df;
            }
        }

        .gr-bf-item .no {
            width: 28px;
            height: 28px;
            border-radius: 9px;
            background: var(--gr-accent-soft);
            color: var(--gr-accent);
            font-weight: 700;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .gr-bf-item .nm {
            flex: 1;
            min-width: 0;
            font-weight: 600;
            font-size: 13px;
        }

        .gr-bf-item .nm small {
            display: block;
            font-weight: 400;
            color: var(--gr-muted);
            font-size: 11.5px;
        }

        .gr-bf-item .q {
            width: 72px;
            text-align: right;
            border: 1px solid #d9e2ed;
            border-radius: 8px;
            padding: 3px 8px;
            font-size: 13px;
            background: #fff;
        }

        .gr-bf-item .q:focus {
            outline: 0;
            border-color: var(--gr-accent);
            box-shadow: 0 0 0 3px var(--gr-accent-soft);
        }

        .gr-bf-item .st {
            width: 50px;
            font-size: 12px;
            color: var(--gr-muted);
        }

        .gr-bf-item .del {
            width: 30px;
            height: 30px;
            border: 0;
            border-radius: 9px;
            background: #f9e8ea;
            color: #c0505f;
            transition: background .2s ease, color .2s ease, transform .15s ease;
        }

        .gr-bf-item .del:hover {
            background: #c0505f;
            color: #fff;
            transform: scale(1.08);
        }

        .gr-bf-empty {
            text-align: center;
            color: #9aa6b8;
            padding: 50px 0;
            border: 2px dashed #dde5ef;
            border-radius: 14px;
            background: #f6f9fc;
        }

        .gr-bf-empty i {
            font-size: 36px;
            display: block;
            margin-bottom: 8px;
            animation: gr-bf-float 2.4s ease-in-out infinite;
        }

        @keyframes gr-bf-float {
            50% {
                transform: translateY(-6px);
            }
        }

        /* Toolbar daftar item: cari + filter tgl + tombol buka panel */
        .gr-bf-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 12px;
        }

        .gr-bf-filter {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .gr-bf-cari {
            position: relative;
            width: 260px;
            max-width: 100%;
        }

        .gr-bf-cari i {
            position: absolute;
            left: 11px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 12px;
        }

        .gr-bf-cari input {
            width: 100%;
            height: 34px;
            border: 1px solid #d9e2ed;
            border-radius: 8px;
            padding: 0 10px 0 31px;
            font-size: 13px;
            background: #fff;
        }

        .gr-bf-cari input:focus {
            outline: 0;
            border-color: var(--gr-accent);
            box-shadow: 0 0 0 3px var(--gr-accent-soft);
        }

        .gr-bf-filter .select2-container--bootstrap4 .select2-selection--single {
            min-height: 34px !important;
            height: 34px !important;
        }

        .gr-bf-filter .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
            line-height: 32px !important;
            padding-top: 0 !important;
            padding-bottom: 0 !important;
            font-size: 13px;
        }

        .gr-bf-filter .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow {
            height: 32px;
            top: 0;
        }

        .gr-bf-toolbar .gr-btn-main {
            height: 34px;
            padding: 0 14px;
        }

        .gr-bf-item.cocok .nm mark {
            background: #fbf3df;
            color: inherit;
            padding: 0 1px;
            border-radius: 3px;
        }

        /* ===== Katalog barang layar penuh (menutupi isi modal Form Request, bukan modal kedua) ===== */
        #modalBuat .modal-content {
            position: relative;
        }

        /* Saat katalog terbuka modal dibuat setinggi layar supaya katalog lega walau item masih sedikit */
        #modalBuat.panel-buka .modal-content {
            min-height: calc(100vh - 3.5rem);
        }

        .gr-kat {
            position: absolute;
            inset: 0;
            z-index: 6;
            display: flex;
            flex-direction: column;
            background: #fff;
            opacity: 0;
            visibility: hidden;
            transform: translateY(28px);
            pointer-events: none;
            transition: opacity .22s ease, transform .3s cubic-bezier(.2, .8, .2, 1), visibility 0s linear .3s;
        }

        .gr-kat.buka {
            opacity: 1;
            visibility: visible;
            transform: none;
            pointer-events: auto;
            transition: opacity .22s ease, transform .3s cubic-bezier(.2, .8, .2, 1), visibility 0s;
        }

        /* Header: kembali + judul + cari */
        .gr-kat-head {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px 22px;
            color: #fff;
            background: linear-gradient(135deg, #3f5f8f 0%, #5b7fb5 60%, #7c9dcc 100%);
            flex-shrink: 0;
        }

        .gr-kat-head .kembali {
            width: 38px;
            height: 38px;
            border: 1px solid rgba(255, 255, 255, .3);
            border-radius: 8px;
            background: rgba(255, 255, 255, .14);
            color: #fff;
            flex-shrink: 0;
            transition: background .15s ease, transform .15s ease;
        }

        .gr-kat-head .kembali:hover {
            background: rgba(255, 255, 255, .26);
            transform: translateX(-2px);
        }

        .gr-kat-head .jdw {
            flex-shrink: 0;
            min-width: 0;
        }

        .gr-kat-head .lbl {
            font-size: 10.5px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            opacity: .8;
        }

        .gr-kat-head .jd {
            font-size: 16px;
            font-weight: 700;
            line-height: 1.2;
            white-space: nowrap;
        }

        .gr-kat-head .gr-kat-cari {
            flex: 1;
            max-width: 560px;
            margin-left: auto;
        }

        .gr-kat-cari {
            position: relative;
        }

        .gr-kat-cari>i {
            position: absolute;
            left: 13px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 13px;
            pointer-events: none;
        }

        .gr-kat-cari input {
            width: 100%;
            height: 40px;
            border: 1px solid transparent;
            border-radius: 8px;
            padding: 0 66px 0 37px;
            font-size: 14px;
            background: #fff;
            color: #1f2937;
            transition: box-shadow .15s ease;
        }

        .gr-kat-cari input:focus {
            outline: 0;
            box-shadow: 0 0 0 3px rgba(255, 255, 255, .35);
        }

        .gr-kat-cari .putar {
            position: absolute;
            right: 40px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gr-accent);
            font-size: 13px;
            display: none;
        }

        .gr-kat-cari .putar.on {
            display: block;
        }

        .gr-kat-cari .hapus {
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            width: 26px;
            height: 26px;
            border: 0;
            border-radius: 6px;
            background: #eef2f7;
            color: #8a96a8;
            font-size: 11px;
        }

        .gr-kat-cari .hapus:hover {
            background: #e2e8f0;
            color: #374151;
        }

        /* Baris pengaturan: berlaku untuk barang yang ditambahkan */
        .gr-kat-set {
            display: flex;
            align-items: center;
            gap: 14px;
            flex-wrap: wrap;
            padding: 10px 22px;
            background: #f2f5f9;
            border-bottom: 1px solid #d5dee9;
            flex-shrink: 0;
        }

        .gr-kat-set .cap {
            font-size: 10.5px;
            font-weight: 700;
            color: #6b7687;
            text-transform: uppercase;
            letter-spacing: .6px;
        }

        .gr-kat-set .cap i {
            color: var(--gr-accent);
            margin-right: 4px;
        }

        .gr-kat-set .fld {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .gr-kat-set .fld>span {
            font-size: 12px;
            font-weight: 600;
            color: #4b5563;
            white-space: nowrap;
        }

        .gr-kat-set .fld>span i {
            color: var(--gr-accent);
            margin-right: 3px;
        }

        .gr-kat-set #bf_tgl_butuh {
            width: 160px;
        }

        .gr-kat-set .fld.uang .select2-container {
            width: 110px !important;
        }

        /* Isi: sidebar sub group + daftar barang */
        .gr-kat-main {
            flex: 1;
            min-height: 0;
            display: grid;
            grid-template-columns: 230px minmax(0, 1fr);
        }

        .gr-kat-side {
            border-right: 1px solid #e3e9f1;
            background: #fafbfd;
            overflow-y: auto;
            padding: 12px 10px;
        }

        .gr-kat-side .cap {
            font-size: 10.5px;
            font-weight: 700;
            color: #8a96a8;
            text-transform: uppercase;
            letter-spacing: .6px;
            padding: 0 8px 8px;
        }

        .gr-kat-sub {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .gr-kat-sub button {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            width: 100%;
            text-align: left;
            border: 0;
            border-left: 3px solid transparent;
            border-radius: 6px;
            background: transparent;
            color: #374151;
            padding: 8px 10px;
            font-size: 12.5px;
            font-weight: 600;
            transition: background .15s ease, color .15s ease, border-color .15s ease;
        }

        .gr-kat-sub button span {
            flex-shrink: 0;
            min-width: 24px;
            padding: 1px 6px;
            border-radius: 4px;
            background: #eef2f7;
            color: #6b7687;
            font-size: 10.5px;
            font-family: 'Consolas', monospace;
            text-align: center;
        }

        .gr-kat-sub button:hover {
            background: #eef3fa;
            color: var(--gr-accent);
        }

        .gr-kat-sub button.aktif {
            background: #e6eef9;
            color: var(--gr-accent);
            border-left-color: var(--gr-accent);
        }

        .gr-kat-sub button.aktif span {
            background: var(--gr-accent);
            color: #fff;
        }

        .gr-kat-isi {
            display: flex;
            flex-direction: column;
            min-width: 0;
            min-height: 0;
        }

        .gr-bf-panel-body {
            flex: 1;
            overflow-y: auto;
            background: #fff;
            padding-bottom: 8px;
        }

        /* ===== Katalog gaya toko online: tampilan daftar & gambar ===== */
        .gr-kat-hasil {
            position: sticky;
            top: 0;
            z-index: 2;
            padding: 9px 20px;
            background: rgba(255, 255, 255, .96);
            border-bottom: 1px solid #edf1f6;
            font-size: 12px;
            color: #8a96a8;
        }

        .gr-kat-hasil b {
            color: var(--gr-ink);
        }

        /* Tampilan daftar (default) */
        .gr-kat-item {
            display: grid;
            grid-template-columns: 56px minmax(0, 1fr) auto;
            column-gap: 14px;
            align-items: center;
            padding: 10px 20px;
            border-bottom: 1px solid #f0f3f7;
            transition: background .15s ease;
            animation: gr-fade-up .22s ease both;
        }

        .gr-kat-item:hover {
            background: #f8fafd;
        }

        .gr-kat-item.ada {
            background: #f4faf6;
        }

        .gr-kat-item.mati {
            opacity: .55;
        }

        .gr-kat-item .gb {
            position: relative;
            width: 56px;
            height: 56px;
            border-radius: 10px;
            overflow: hidden;
            background: #f1f4f8;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #c3ccd8;
            font-size: 20px;
        }

        .gr-kat-item .gb img {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            opacity: 0;
            transition: opacity .25s ease;
        }

        .gr-kat-item .gb img.ok {
            opacity: 1;
        }

        .gr-kat-item .gb .cek {
            position: absolute;
            top: 4px;
            right: 4px;
            z-index: 1;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background: #2f8a5f;
            color: #fff;
            font-size: 10px;
            display: none;
            align-items: center;
            justify-content: center;
        }

        .gr-kat-item.ada .gb .cek {
            display: flex;
        }

        .gr-kat-item .isi {
            min-width: 0;
        }

        .gr-kat-item .nm {
            font-size: 14px;
            font-weight: 600;
            color: var(--gr-ink);
            line-height: 1.3;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .gr-kat-item mark {
            padding: 0 1px;
            border-radius: 3px;
            background: #fdf0c4;
            color: inherit;
        }

        .gr-kat-item .meta {
            margin-top: 2px;
            font-size: 12px;
            color: #8a96a8;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .gr-kat-item .meta .sat {
            display: inline-block;
            margin-left: 6px;
            padding: 0 6px;
            border-radius: 4px;
            background: #eef2f7;
            color: #5b6576;
            font-size: 10.5px;
            font-weight: 700;
            line-height: 17px;
            vertical-align: 1px;
        }

        .gr-kat-item .meta .sat:first-child {
            margin-left: 0;
        }

        .gr-kat-item .meta .salah {
            color: #c0505f;
            font-weight: 600;
        }

        .gr-kat-item .aksi {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            min-width: 132px;
        }

        /* Tombol tambah ke keranjang */
        .gr-kat-item .tbh {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            height: 34px;
            padding: 0 14px;
            border: 1.5px solid var(--gr-accent);
            border-radius: 9px;
            background: #fff;
            color: var(--gr-accent);
            font-size: 13px;
            font-weight: 600;
            transition: background .15s ease, color .15s ease, transform .1s ease;
        }

        .gr-kat-item .tbh:hover {
            background: var(--gr-accent);
            color: #fff;
        }

        .gr-kat-item .tbh:active {
            transform: scale(.95);
        }

        /* Atur jumlah (setelah masuk keranjang) */
        .gr-kat-item .kq {
            display: inline-flex;
            align-items: center;
            height: 34px;
            border: 1.5px solid #2f8a5f;
            border-radius: 9px;
            background: #fff;
            overflow: hidden;
            animation: gr-kat-pop .25s ease;
        }

        .gr-kat-item .kq button {
            width: 34px;
            height: 100%;
            border: 0;
            background: transparent;
            color: #2f8a5f;
            font-size: 13px;
            transition: background .15s ease;
        }

        .gr-kat-item .kq button:hover {
            background: #e6f3eb;
        }

        .gr-kat-item .kq button.hapus {
            color: #c0505f;
        }

        .gr-kat-item .kq button.hapus:hover {
            background: #fbeaec;
        }

        .gr-kat-item .kq input {
            width: 42px;
            height: 100%;
            border: 0;
            outline: 0;
            text-align: center;
            font-size: 14px;
            font-weight: 700;
            color: var(--gr-ink);
        }

        .gr-kat-item .kq .st {
            padding-right: 8px;
            font-size: 11px;
            font-weight: 600;
            color: #8a96a8;
        }

        .gr-kat-item .multi {
            text-align: right;
            font-size: 12px;
            color: #2f8a5f;
            line-height: 1.35;
        }

        .gr-kat-item .multi small {
            display: block;
            color: #8a96a8;
        }

        .gr-kat-item .ket-mati {
            font-size: 11.5px;
            color: #c0505f;
        }

        @keyframes gr-kat-pop {
            0% {
                transform: scale(.85);
            }

            60% {
                transform: scale(1.05);
            }

            100% {
                transform: none;
            }
        }

        /* Tampilan gambar (grid kartu) */
        #bf_katalog.grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            align-content: start;
            gap: 14px;
            padding: 0 16px 16px;
        }

        #bf_katalog.grid .gr-kat-hasil,
        #bf_katalog.grid .gr-kat-info {
            grid-column: 1 / -1;
            margin: 0 -16px;
        }

        #bf_katalog.grid .gr-kat-item {
            display: flex;
            flex-direction: column;
            padding: 0;
            border: 1px solid #e6ebf2;
            border-radius: 12px;
            background: #fff;
            overflow: hidden;
            transition: box-shadow .2s ease, transform .2s ease, border-color .2s ease;
        }

        #bf_katalog.grid .gr-kat-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(47, 58, 74, .08);
        }

        #bf_katalog.grid .gr-kat-item.ada {
            border-color: #8cc5a4;
        }

        #bf_katalog.grid .gr-kat-item .gb {
            width: 100%;
            height: auto;
            aspect-ratio: 1 / 1;
            border-radius: 0;
            font-size: 38px;
        }

        #bf_katalog.grid .gr-kat-item .gb .cek {
            top: 8px;
            right: 8px;
            width: 24px;
            height: 24px;
            font-size: 12px;
        }

        #bf_katalog.grid .gr-kat-item .isi {
            flex: 1;
            padding: 10px 12px 0;
        }

        #bf_katalog.grid .gr-kat-item .nm {
            white-space: normal;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            min-height: 36px;
        }

        #bf_katalog.grid .gr-kat-item .aksi {
            min-width: 0;
            padding: 10px 12px 12px;
        }

        #bf_katalog.grid .gr-kat-item .tbh,
        #bf_katalog.grid .gr-kat-item .kq {
            width: 100%;
        }

        #bf_katalog.grid .gr-kat-item .kq input {
            flex: 1;
            min-width: 0;
        }

        #bf_katalog.grid .gr-kat-item .multi {
            width: 100%;
            text-align: center;
        }

        /* Pilihan tampilan daftar / gambar */
        .gr-kat-view {
            display: inline-flex;
            flex-shrink: 0;
            padding: 3px;
            border-radius: 10px;
            background: rgba(255, 255, 255, .18);
        }

        .gr-kat-view button {
            width: 36px;
            height: 32px;
            border: 0;
            border-radius: 8px;
            background: transparent;
            color: rgba(255, 255, 255, .8);
            font-size: 14px;
            transition: background .15s ease, color .15s ease;
        }

        .gr-kat-view button.aktif {
            background: #fff;
            color: var(--gr-accent);
        }

        .gr-kat-info {
            text-align: center;
            color: #9aa6b8;
            font-size: 12.5px;
            padding: 40px 20px;
        }

        .gr-kat-info i {
            display: block;
            font-size: 30px;
            margin-bottom: 8px;
            color: #cbd2dc;
        }

        .gr-kat-info.kecil {
            padding: 12px 0;
            font-size: 11.5px;
        }

        /* Footer: isi keranjang + selesai */
        .gr-kat-foot {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 12px 22px;
            border-top: 1px solid #d5dee9;
            background: #f2f5f9;
            flex-shrink: 0;
        }

        .gr-kat-foot .ringkas {
            flex: 1;
            min-width: 0;
            font-size: 13px;
            color: #4b5563;
        }

        .gr-kat-foot .ringkas .krj {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            margin-right: 8px;
            border-radius: 50%;
            background: var(--gr-accent-soft);
            color: var(--gr-accent);
            vertical-align: middle;
        }

        .gr-kat-foot .ringkas .krj.goyang {
            animation: gr-kat-pop .35s ease;
        }

        .gr-kat-foot .ringkas b {
            color: var(--gr-ink);
        }

        .gr-kat-foot .ringkas small {
            color: #b98a2e;
        }

        .gr-kat-foot .gr-btn-main {
            padding: 8px 20px;
            border-radius: 9px;
        }

        @media (max-width: 991px) {
            .gr-kat-main {
                grid-template-columns: 1fr;
                grid-template-rows: auto minmax(0, 1fr);
            }

            .gr-kat-side {
                border-right: 0;
                border-bottom: 1px solid #e3e9f1;
                padding: 8px 12px;
                overflow-x: auto;
                overflow-y: hidden;
            }

            .gr-kat-side .cap {
                display: none;
            }

            .gr-kat-sub {
                flex-direction: row;
                gap: 6px;
            }

            .gr-kat-sub button {
                width: auto;
                flex-shrink: 0;
                border-left: 0;
                border: 1px solid #d5dee9;
            }

            .gr-kat-sub button.aktif {
                border-color: var(--gr-accent);
            }

            .gr-kat-head .jdw {
                display: none;
            }

            #bf_katalog.grid {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
                gap: 10px;
                padding: 0 10px 12px;
            }

            #bf_katalog.grid .gr-kat-hasil,
            #bf_katalog.grid .gr-kat-info {
                margin: 0 -10px;
            }

            .gr-kat-item .aksi {
                min-width: 0;
            }
        }

        /* ===== Daftar item gaya tabel (industrial: datar, garis tegas, kode monospace) ===== */
        .gr-bf-list {
            gap: 0;
            background: #fff;
            border: 1px solid #d5dee9;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 1px 2px rgba(31, 41, 55, .04);
        }

        .gr-bf-list.kosong {
            border: 0;
            background: transparent;
            box-shadow: none;
        }

        .gr-bf-head,
        .gr-bf-item {
            display: grid;
            grid-template-columns: 30px minmax(0, 1fr) 170px 112px 150px 120px 34px;
            column-gap: 12px;
            align-items: center;
        }

        .gr-bf-head {
            padding: 8px 14px 8px 17px;
            background: #f2f5f9;
            border-bottom: 1px solid #d5dee9;
            font-size: 10.5px;
            font-weight: 700;
            letter-spacing: .7px;
            text-transform: uppercase;
            color: #6b7687;
        }

        .gr-bf-item {
            position: relative;
            background: #fff;
            border: 0;
            border-bottom: 1px solid #edf1f6;
            border-left: 3px solid transparent;
            border-radius: 0;
            padding: 10px 14px;
            row-gap: 7px;
            transition: background .15s ease, border-color .15s ease;
        }

        .gr-bf-item:last-child {
            border-bottom: 0;
        }

        .gr-bf-item:hover {
            transform: none;
            box-shadow: none;
            background: #f8fafd;
            border-left-color: #b9cbe3;
        }

        .gr-bf-item:focus-within {
            background: #f4f8fd;
            border-left-color: var(--gr-accent);
        }

        @keyframes gr-bf-in {
            0% {
                opacity: 0;
                transform: translateX(-10px);
                background: #e3f1e9;
            }

            60% {
                opacity: 1;
                transform: none;
                background: #e3f1e9;
            }

            100% {
                background: #fff;
            }
        }

        @keyframes gr-bf-flash {

            0%,
            100% {
                background: #fff;
            }

            30% {
                background: #fbf0d4;
            }
        }

        .gr-bf-item .no {
            width: auto;
            height: auto;
            background: none;
            border-radius: 0;
            justify-content: flex-start;
            font-family: 'Consolas', monospace;
            font-size: 12px;
            font-weight: 700;
            color: #9aa6b8;
        }

        .gr-bf-item .nm {
            font-size: 13px;
            font-weight: 700;
            color: #1f2937;
            line-height: 1.3;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .gr-bf-item .nm small {
            display: block;
            margin-top: 2px;
            font-family: 'Consolas', monospace;
            font-size: 11px;
            font-weight: 400;
            color: #8a96a8;
            letter-spacing: .2px;
        }

        .gr-bf-item .tgl {
            display: block;
            width: auto;
            padding: 1px 0 1px 8px;
            background: none;
            border-radius: 0;
            border-left: 3px solid #4a8c68;
            color: #4a8c68;
        }

        .gr-bf-item .tgl.soon {
            background: none;
            border-left-color: #c27a3a;
            color: #c27a3a;
        }

        .gr-bf-item .tgl b {
            display: block;
            font-family: 'Consolas', monospace;
            font-size: 12px;
            font-weight: 700;
            color: #1f2937;
            line-height: 1.3;
        }

        .gr-bf-item .tgl span {
            display: block;
            font-size: 10.5px;
            font-weight: 600;
            line-height: 1.2;
            margin-top: 2px;
        }

        /* Tgl butuh bisa direvisi langsung di baris */
        .gr-bf-item .tgl .tg {
            width: 100%;
            height: 28px;
            padding: 0 6px;
            border: 1px solid #d5dee9;
            border-radius: 6px;
            background: #fff;
            font-family: 'Consolas', monospace;
            font-size: 12px;
            font-weight: 700;
            color: #1f2937;
        }

        .gr-bf-item .tgl .tg:focus {
            outline: 0;
            border-color: var(--gr-accent);
            box-shadow: 0 0 0 3px var(--gr-accent-soft);
        }

        /* Sudah lewat / belum diisi: wajib direvisi sebelum simpan */
        .gr-bf-item .tgl.lewat {
            border-left-color: #c0505f;
            color: #c0505f;
        }

        .gr-bf-item .tgl.lewat .tg {
            border-color: #e3a3ab;
            background: #fdf3f4;
            color: #c0505f;
        }

        /* ===== Baris item: semua kontrol 1 garis, tinggi sama (34px) ===== */
        #bf_items .gr-bf-item {
            align-items: center;
        }

        #bf_items .gr-bf-item .tgl,
        #bf_items .gr-bf-item .qwrap,
        #bf_items .gr-bf-item .hg {
            display: flex;
            align-items: stretch;
            height: 34px;
            margin: 0;
            padding: 0;
            border: 1px solid #d5dee9;
            border-left-width: 1px;
            border-radius: 6px;
            background: #fff;
            overflow: hidden;
            color: inherit;
        }

        #bf_items .gr-bf-item .tgl:focus-within,
        #bf_items .gr-bf-item .qwrap:focus-within,
        #bf_items .gr-bf-item .hg:focus-within {
            border-color: var(--gr-accent);
            box-shadow: 0 0 0 3px var(--gr-accent-soft);
        }

        #bf_items .gr-bf-item .tgl .tg,
        #bf_items .gr-bf-item .qwrap .q,
        #bf_items .gr-bf-item .hg input {
            flex: 1;
            min-width: 0;
            height: 100%;
            margin: 0;
            border: 0;
            border-radius: 0;
            box-shadow: none;
            background: transparent;
        }

        #bf_items .gr-bf-item .tgl .tg {
            padding: 0 4px 0 8px;
            color: #1f2937;
        }

        /* Label sisa hari: H-7 / Hari H / Lewat */
        #bf_items .gr-bf-item .tgl .hr,
        #bf_items .gr-bf-item .qwrap .st,
        #bf_items .gr-bf-item .hg>span:not(.select2) {
            display: flex;
            align-items: center;
            justify-content: center;
            height: auto;
            line-height: 1;
            margin: 0;
            padding: 0 8px;
            font-size: 10.5px;
            font-weight: 700;
            white-space: nowrap;
        }

        #bf_items .gr-bf-item .tgl .hr {
            min-width: 46px;
            border-left: 1px solid #d5dee9;
            background: #e6f3eb;
            color: #2f8a5f;
            font-family: 'Consolas', monospace;
        }

        #bf_items .gr-bf-item .tgl.soon .hr {
            background: #fcefe1;
            color: #c27a3a;
        }

        #bf_items .gr-bf-item .tgl.lewat {
            border-color: #e3a3ab;
        }

        #bf_items .gr-bf-item .tgl.lewat .tg {
            background: #fdf3f4;
            color: #c0505f;
        }

        #bf_items .gr-bf-item .tgl.lewat .hr {
            background: #c0505f;
            color: #fff;
            border-left-color: #c0505f;
        }

        #bf_items .gr-bf-item .del {
            width: 34px;
            height: 34px;
        }

        #bf_items .gr-bf-item .sub {
            line-height: 1.2;
        }

        #bf_items .gr-bf-item .sub.kosong {
            color: #c0c7d2;
        }

        .gr-bf-item .qwrap {
            border-radius: 6px;
            border-color: #d5dee9;
        }

        .gr-bf-item .qwrap .q,
        .gr-bf-item .q {
            width: 100%;
            min-width: 0;
            height: 30px;
            padding: 0 8px;
            border: 0;
            border-radius: 0;
            text-align: right;
            font-family: 'Consolas', monospace;
            font-size: 13px;
            font-weight: 700;
            color: #1f2937;
        }

        .gr-bf-item .qwrap .st,
        .gr-bf-item .st {
            width: auto;
            min-width: 40px;
            height: 30px;
            line-height: 30px;
            padding: 0 7px;
            background: #f2f5f9;
            border-left: 1px solid #d5dee9;
            font-size: 10.5px;
            font-weight: 700;
            color: #6b7687;
            text-align: center;
        }

        .gr-bf-item .hg {
            display: flex;
            align-items: center;
            margin: 0;
            border: 1px solid #d5dee9;
            border-radius: 6px;
            background: #fff;
            overflow: hidden;
        }

        .gr-bf-item .hg>span:not(.select2) {
            height: 30px;
            line-height: 30px;
            padding: 0 7px;
            background: #f2f5f9;
            border-right: 1px solid #d5dee9;
            font-size: 10.5px;
            font-weight: 700;
            color: var(--gr-accent);
        }

        .gr-bf-item .hg input {
            flex: 1;
            min-width: 0;
            height: 30px;
            padding: 0 8px;
            border: 0;
            text-align: right;
            font-family: 'Consolas', monospace;
            font-size: 12.5px;
            color: #1f2937;
        }

        .gr-bf-item .hg:focus-within,
        .gr-bf-item .qwrap:focus-within {
            border-color: var(--gr-accent);
            box-shadow: 0 0 0 3px var(--gr-accent-soft);
        }

        .gr-bf-item input:focus {
            outline: 0;
        }

        .gr-bf-item input::placeholder {
            color: #b8c1ce;
            font-family: inherit;
            font-weight: 400;
        }

        .gr-bf-item .del {
            width: 30px;
            height: 30px;
            border: 1px solid #efd3d7;
            border-radius: 6px;
            background: #fff;
            color: #c0505f;
            font-size: 12px;
        }

        .gr-bf-item .del:hover {
            background: #c0505f;
            border-color: #c0505f;
            color: #fff;
            transform: none;
        }

        /* Baris ke-2 item: sub dept (wajib) + keterangan */
        .gr-bf-item .brs2 {
            grid-column: 2 / 7;
            display: flex;
            gap: 8px;
            align-items: center;
            min-width: 0;
        }

        .gr-bf-item .brs2 .sdw {
            width: 260px;
            flex-shrink: 0;
        }

        .gr-bf-item .brs2 .select2-container--bootstrap4 .select2-selection--single {
            height: 28px !important;
            min-height: 28px !important;
            border-color: #d5dee9;
            border-radius: 6px;
        }

        .gr-bf-item .brs2 .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
            line-height: 26px !important;
            padding-top: 0 !important;
            padding-bottom: 0 !important;
            padding-left: 26px !important;
            font-size: 12px;
            font-weight: 600;
            color: #374151;
        }

        .gr-bf-item .brs2 .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow {
            height: 26px;
            top: 0;
        }

        .gr-bf-item .brs2 .sdw {
            position: relative;
        }

        .gr-bf-item .brs2 .sdw > i {
            position: absolute;
            left: 9px;
            top: 50%;
            transform: translateY(-50%);
            z-index: 2;
            font-size: 10px;
            color: var(--gr-accent);
            pointer-events: none;
        }

        /* Sub dept belum dipilih: wajib */
        .gr-bf-item .brs2 .sdw.kosong .select2-selection--single {
            border-color: #e3a3ab !important;
            background: #fdf3f4 !important;
        }

        .gr-bf-item .brs2 .sdw.kosong > i {
            color: #c0505f;
        }

        .gr-bf-item .ktw {
            flex: 1;
            min-width: 0;
            position: relative;
        }

        /* ===== Sub dept per barang: pilih banyak (chip), lebih dari 1 = dipecah per sub dept ===== */
        .gr-bf-item .brs2 .sdw {
            width: auto;
            flex: 0 1 380px;
            min-width: 240px;
        }

        .gr-bf-item .brs2 .select2-container--bootstrap4 .select2-selection--multiple {
            min-height: 30px !important;
            padding: 2px 6px 2px 26px;
            border: 1px solid #d5dee9;
            border-radius: 6px;
            background: #fff;
            cursor: pointer;
        }

        .gr-bf-item .brs2 .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__rendered {
            display: flex !important;
            flex-wrap: wrap;
            align-items: center;
            gap: 4px;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .gr-bf-item .brs2 .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__choice {
            display: inline-flex;
            align-items: center;
            margin: 0 !important;
            padding: 0 7px 0 4px;
            height: 22px;
            border: 1px solid #c9d7ea;
            border-radius: 4px;
            background: #eef3f9;
            color: var(--gr-accent);
            font-size: 11.5px;
            font-weight: 700;
            line-height: 20px;
        }

        .gr-bf-item .brs2 .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__choice__remove {
            position: static;
            margin: 0 4px 0 0;
            padding: 0 3px;
            border: 0;
            background: none;
            color: #8a96a8;
            font-size: 14px;
            font-weight: 700;
            line-height: 1;
        }

        .gr-bf-item .brs2 .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__choice__remove:hover {
            color: #c0505f;
            background: none;
        }

        .gr-bf-item .brs2 .select2-container--bootstrap4 .select2-selection--multiple .select2-search--inline,
        .gr-bf-item .brs2 .select2-container--bootstrap4 .select2-selection--multiple .select2-search__field {
            margin: 0 !important;
            height: 22px;
            font-size: 12px;
            line-height: 22px;
        }

        .gr-bf-item .brs2 .select2-container--bootstrap4 .select2-selection--multiple .select2-search__field::placeholder {
            color: #a3aebe;
        }

        /* Masih ada sub dept lain: petunjuk "+ Sub dept lain" tampil penuh dan berwarna tema */
        .gr-bf-item .brs2 .select2-container.ada-sisa .select2-search__field {
            min-width: 110px;
        }

        .gr-bf-item .brs2 .select2-container.ada-sisa .select2-search__field::placeholder {
            color: var(--gr-accent);
            font-weight: 600;
            opacity: .75;
        }

        .gr-bf-item .brs2 .sdw.kosong .select2-selection--multiple {
            border-color: #e3a3ab !important;
            border-style: dashed;
            background: #fdf3f4 !important;
        }

        .gr-bf-item .brs2 .info-pecah {
            font-size: 11.5px;
            font-weight: 600;
            color: #6b7687;
            white-space: nowrap;
        }

        .gr-bf-item .brs2 .info-pecah i {
            color: var(--gr-accent);
            margin-right: 3px;
        }

        /* Mata uang per barang: label di depan harga yang bisa diklik untuk ganti */
        #bf_items .gr-bf-item .hg .select2-container {
            flex: 0 0 auto;
            width: auto !important;
            height: 100%;
        }

        #bf_items .gr-bf-item .hg .select2-container--bootstrap4 .select2-selection--single {
            display: flex !important;
            align-items: center;
            height: 100% !important;
            min-height: 0 !important;
            padding: 0;
            border: 0 !important;
            border-right: 1px solid #d5dee9 !important;
            border-radius: 0 !important;
            background: #f2f5f9 !important;
            box-shadow: none !important;
            cursor: pointer;
        }

        #bf_items .gr-bf-item .hg .select2-container--bootstrap4 .select2-selection--single:hover {
            background: #e6edf6 !important;
        }

        #bf_items .gr-bf-item .hg .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
            padding: 0 20px 0 8px !important;
            line-height: normal !important;
            font-size: 10.5px;
            font-weight: 700;
            color: var(--gr-accent);
        }

        #bf_items .gr-bf-item .hg .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow {
            width: 16px !important;
            border-left: 0;
        }

        #bf_items .gr-bf-item .hg .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow b {
            width: 5px;
            height: 5px;
            border-width: 0 1.5px 1.5px 0;
        }

        /* Qty total (barang dipecah): hanya tampilan, diisi per sub dept di bawahnya */
        .gr-bf-item .qtot {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 6px;
            height: 34px;
            padding-right: 2px;
        }

        .gr-bf-item .qtot b {
            font-family: 'Consolas', monospace;
            font-size: 14px;
            color: #1f2937;
        }

        .gr-bf-item .qtot span {
            padding: 2px 6px;
            border-radius: 4px;
            background: #f2f5f9;
            font-size: 10.5px;
            font-weight: 700;
            color: #6b7687;
        }

        /* Baris per sub dept */
        .gr-bf-item .anak-wrap {
            grid-column: 1 / -1;
            border-top: 1px dashed #dfe6ef;
        }

        .gr-bf-item .anak {
            display: grid;
            grid-template-columns: 30px minmax(0, 1fr) 170px 112px 150px 120px 34px;
            column-gap: 12px;
            align-items: center;
            padding: 6px 0;
        }

        .gr-bf-item .anak+.anak {
            border-top: 1px dashed #edf1f6;
        }

        .gr-bf-item .anak.keluar {
            animation: gr-bf-out .22s ease forwards;
        }

        .gr-bf-item .anak .cab::before {
            content: '';
            display: block;
            width: 12px;
            height: 14px;
            margin: -10px 0 0 10px;
            border-left: 2px solid #c9d7ea;
            border-bottom: 2px solid #c9d7ea;
            border-bottom-left-radius: 5px;
        }

        .gr-bf-item .anak .anm {
            grid-column: 2 / 4;
            display: flex;
            align-items: center;
            gap: 8px;
            min-width: 0;
        }

        .gr-bf-item .anak .sdn {
            flex: 0 0 220px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-size: 12px;
            font-weight: 700;
            color: #374151;
        }

        .gr-bf-item .anak .sdn i {
            margin-right: 4px;
            font-size: 10px;
            color: var(--gr-accent);
        }

        .gr-bf-item .anak .sdn.kosong,
        .gr-bf-item .anak .sdn.kosong i {
            color: #c0505f;
        }

        .gr-bf-item .anak .ktw {
            flex: 1;
        }

        #bf_items .gr-bf-item .anak .qwrap {
            height: 30px;
        }

        #bf_items .gr-bf-item .anak .sub {
            font-size: 12px;
        }

        /* Qty belum diisi: wajib */
        #bf_items .gr-bf-item .qwrap.kosong {
            border-color: #e3a3ab;
            background: #fdf3f4;
        }

        #bf_items .gr-bf-item .qwrap.kosong .st {
            background: #f9e3e6;
            color: #c0505f;
        }

        #bf_items .gr-bf-item .del.kecil {
            width: 26px;
            height: 26px;
            justify-self: center;
            border-color: #e5e9ef;
            color: #9aa6b8;
            font-size: 11px;
        }

        #bf_items .gr-bf-item .del.kecil:hover {
            background: #c0505f;
            border-color: #c0505f;
            color: #fff;
        }

        @media (max-width: 575px) {
            .gr-bf-item .anak {
                grid-template-columns: 24px 1fr 1fr 30px;
                row-gap: 6px;
            }

            .gr-bf-item .anak .anm {
                grid-column: 2 / 5;
                flex-wrap: wrap;
            }

            .gr-bf-item .anak .anm .sdn {
                flex-basis: 100%;
            }
        }

        /* Select2 di baris pengaturan katalog: tinggi sama dengan input tanggal, teks satu baris */
        .gr-kat-set .form-control,
        .gr-kat-set .select2-container--bootstrap4 .select2-selection--single {
            height: 34px !important;
            min-height: 34px;
            border: 1px solid #d5dee9;
            border-radius: 6px;
            background: #fff;
            transition: border-color .15s ease, box-shadow .15s ease;
        }

        .gr-kat-set .select2-container--bootstrap4 .select2-selection--single:hover {
            border-color: #b9cbe3;
        }

        .gr-kat-set .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
            height: 32px;
            line-height: 32px !important;
            padding: 0 30px 0 10px !important;
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            font-size: 13px;
            font-weight: 600;
            color: #1f2937;
        }

        .gr-kat-set .select2-container--bootstrap4 .select2-selection__placeholder {
            color: #a3aebe;
            font-weight: 400;
        }

        /* Semua select2 di modal request: hover, teks pilihan tebal, placeholder pudar */
        #modalBuat .select2-container--bootstrap4 .select2-selection--single {
            transition: border-color .15s ease, box-shadow .15s ease;
        }

        #modalBuat .select2-container--bootstrap4 .select2-selection--single:hover {
            border-color: #b9cbe3;
        }

        #modalBuat .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
            font-weight: 600;
            color: #1f2937;
            padding-right: 34px !important;
        }

        #modalBuat .select2-container--bootstrap4 .select2-selection__placeholder {
            color: #a3aebe;
            font-weight: 400;
        }

        /* Panah: chevron tipis dengan sekat, berputar saat terbuka (semua select2 di modal request) */
        #modalBuat .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow {
            top: 50% !important;
            right: 0 !important;
            width: 28px !important;
            height: 20px !important;
            margin-top: -10px;
            border-left: 1px solid #e3e9f1;
        }

        #modalBuat .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow b,
        .gr-kat-set .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow b {
            position: absolute;
            left: 50%;
            top: 50%;
            width: 7px;
            height: 7px;
            margin: 0;
            border: solid #6b7687;
            border-width: 0 2px 2px 0;
            transform: translate(-50%, -70%) rotate(45deg);
            transition: transform .2s ease;
        }

        #modalBuat .select2-container--open .select2-selection--single .select2-selection__arrow b {
            border-color: var(--gr-accent);
            transform: translate(-50%, -30%) rotate(-135deg);
        }

        .gr-kat-set .select2-container--bootstrap4.select2-container--focus .select2-selection,
        .gr-kat-set .select2-container--bootstrap4.select2-container--open .select2-selection {
            border-color: var(--gr-accent);
            box-shadow: 0 0 0 3px var(--gr-accent-soft);
        }

        .gr-kat-set #bf_tgl_butuh {
            font-size: 13px;
            font-weight: 600;
            color: #1f2937;
            padding: 4px 10px;
        }

        /* Daftar pilihan select2 di modal request: rapat, rata, warna tema */
        #modalBuat .select2-container--bootstrap4 .select2-dropdown {
            border: 1px solid #d5dee9;
            border-radius: 8px;
            box-shadow: 0 10px 28px rgba(31, 41, 55, .16);
            overflow: hidden;
            margin-top: 4px;
        }

        #modalBuat .select2-container--bootstrap4 .select2-dropdown--above {
            margin-top: -4px;
        }

        #modalBuat .select2-container--bootstrap4 .select2-search--dropdown {
            padding: 8px;
            background: #f2f5f9;
            border-bottom: 1px solid #e3e9f1;
        }

        #modalBuat .select2-container--bootstrap4 .select2-search--dropdown .select2-search__field {
            height: 30px;
            border: 1px solid #d5dee9;
            border-radius: 6px;
            font-size: 12.5px;
            padding: 0 9px;
        }

        #modalBuat .select2-container--bootstrap4 .select2-search--dropdown .select2-search__field:focus {
            outline: 0;
            border-color: var(--gr-accent);
            box-shadow: 0 0 0 3px var(--gr-accent-soft);
        }

        #modalBuat .select2-container--bootstrap4 .select2-results>.select2-results__options {
            max-height: 260px;
            padding: 4px;
        }

        #modalBuat .select2-container--bootstrap4 .select2-results__option {
            padding: 7px 10px;
            margin: 1px 0;
            border-radius: 5px;
            font-size: 13px;
            color: #374151;
        }

        #modalBuat .select2-container--bootstrap4 .select2-results__option[aria-selected=true],
        #modalBuat .select2-container--bootstrap4 .select2-results__option--selected {
            background: #eef3f9;
            color: var(--gr-accent);
            font-weight: 700;
        }

        #modalBuat .select2-container--bootstrap4 .select2-results__option--highlighted,
        #modalBuat .select2-container--bootstrap4 .select2-results__option--highlighted[aria-selected] {
            background: var(--gr-accent) !important;
            color: #fff !important;
        }

        #modalBuat .select2-container--bootstrap4 .select2-results__option[aria-disabled=true] {
            color: #b8c1ce;
        }

        @media (max-width: 575px) {
            .gr-bf-item .brs2 {
                grid-column: 2 / 4;
                flex-wrap: wrap;
            }

            .gr-bf-item .brs2 .sdw {
                width: 100%;
            }
        }

        /* Subtotal per baris (qty x harga) */
        .gr-bf-item .sub {
            text-align: right;
            font-family: 'Consolas', monospace;
            font-size: 13px;
            font-weight: 700;
            color: #1f2937;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .gr-bf-item .sub small {
            display: block;
            font-family: inherit;
            font-size: 10px;
            font-weight: 600;
            color: #8a96a8;
        }

        .gr-bf-item .sub.kosong {
            color: #c0c7d2;
            font-weight: 400;
        }

        /* Ringkasan total ala nota */
        .gr-bf-total {
            display: flex;
            justify-content: flex-end;
            gap: 24px;
            flex-wrap: wrap;
            padding: 14px 58px 16px 17px;
            background: #fafbfd;
            border-top: 2px dashed #d5dee9;
        }

        .gr-bf-total .kiri {
            margin-right: auto;
            display: flex;
            flex-direction: column;
            gap: 4px;
            font-size: 12px;
            color: #6b7687;
        }

        .gr-bf-total .kiri b {
            color: #1f2937;
            font-family: 'Consolas', monospace;
        }

        .gr-bf-total .kiri .tanpa {
            color: #c27a3a;
            font-weight: 600;
        }

        .gr-bf-total .nota {
            min-width: 280px;
        }

        .gr-bf-total .nota .brs {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            padding: 3px 0;
            font-size: 12.5px;
            color: #4b5563;
        }

        .gr-bf-total .nota .brs span:last-child {
            font-family: 'Consolas', monospace;
            font-weight: 600;
            color: #1f2937;
        }

        .gr-bf-total .nota .brs.grand {
            margin-top: 6px;
            padding-top: 8px;
            border-top: 1px solid #1f2937;
        }

        .gr-bf-total .nota .brs.grand span:first-child {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .8px;
            text-transform: uppercase;
            align-self: center;
            color: #6b7687;
        }

        .gr-bf-total .nota .brs.grand span:last-child {
            font-size: 15px;
            font-weight: 800;
        }

        .gr-bf-total .nota .ket {
            font-size: 10.5px;
            color: #9aa6b8;
            text-align: right;
            margin-top: 4px;
        }

        .gr-bf-item .ktw i {
            position: absolute;
            left: 9px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 10px;
            color: #b8c1ce;
            pointer-events: none;
        }

        .gr-bf-item .kt {
            width: 100%;
            height: 28px;
            padding: 0 9px 0 26px;
            border: 1px dashed #d5dee9;
            border-radius: 6px;
            background: #fafbfd;
            font-size: 12px;
            color: #374151;
            transition: border-color .15s ease, background .15s ease;
        }

        .gr-bf-item .kt:focus {
            border-style: solid;
            border-color: var(--gr-accent);
            background: #fff;
            box-shadow: 0 0 0 3px var(--gr-accent-soft);
        }

        .gr-bf-item .kt:not(:placeholder-shown) {
            border-style: solid;
            background: #fff;
        }

        @media (max-width: 575px) {
            .gr-bf-head {
                display: none;
            }

            .gr-bf-item {
                grid-template-columns: 24px 1fr 1fr 30px;
            }

            .gr-bf-item .nm {
                grid-column: 2 / 4;
            }

            .gr-bf-item .del {
                grid-column: 4;
                grid-row: 1;
            }

            .gr-bf-item .tgl {
                grid-column: 2 / 3;
            }

            .gr-bf-item .qwrap {
                grid-column: 3 / 4;
            }

            .gr-bf-item .hg,
            .gr-bf-item .ktw,
            .gr-bf-item .sub {
                grid-column: 2 / 4;
            }

            .gr-bf-total {
                padding: 14px 17px;
            }

            .gr-bf-total .nota {
                min-width: 0;
                width: 100%;
            }
        }


        .gr-bf-count {
            transition: transform .25s ease;
            display: inline-block;
        }

        .gr-bf-count.bump {
            transform: scale(1.35);
        }

        .gr-shake {
            animation: gr-shake .4s ease;
        }

        /* Chip status */
        .gr-chip {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11.5px;
            font-weight: 600;
            white-space: nowrap;
            background: var(--c-bg);
            color: var(--c-fg);
        }

        .gr-chip::before {
            content: '';
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--c-fg);
        }

        .gr-chip.pulse::before {
            animation: gr-dot 1.6s ease-in-out infinite;
        }

        @keyframes gr-dot {
            50% {
                transform: scale(1.7);
                opacity: .4;
            }
        }

        @keyframes gr-rise {
            from {
                opacity: 0;
                transform: translateY(12px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ================= Tombol utama ================= */
        .gr-btn-main {
            background: linear-gradient(135deg, #4a6fa5, #6b8cc2);
            border: 0;
            color: #fff;
            border-radius: 10px;
            padding: 8px 18px;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(74, 111, 165, .25);
            transition: transform .2s ease, box-shadow .2s ease;
        }

        .gr-btn-main:hover {
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 8px 18px rgba(74, 111, 165, .32);
        }

        .gr-btn-main i {
            transition: transform .3s ease;
        }

        .gr-btn-main:hover i {
            transform: rotate(90deg);
        }

        /* ================= Kartu ringkasan ================= */
        .gr-stat {
            position: relative;
            overflow: hidden;
            border-radius: 14px;
            padding: 14px 16px;
            background: #fff;
            border: 1px solid var(--gr-line);
            cursor: pointer;
            height: 100%;
            transition: transform .2s ease, box-shadow .2s ease, background .25s ease, border-color .25s ease;
            opacity: 0;
            animation: gr-rise .45s ease forwards;
        }

        .gr-stat:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 22px rgba(47, 58, 74, .07);
        }

        .gr-stat.active {
            background: var(--c-bg);
            border-color: var(--c-fg);
        }

        .gr-stat.active .gr-stat-icon {
            background: var(--c-fg);
            color: #fff;
        }

        .gr-stat .gr-stat-icon {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--c-bg);
            color: var(--c-fg);
            font-size: 16px;
            transition: transform .3s ease, background .25s ease, color .25s ease;
        }

        .gr-stat:hover .gr-stat-icon {
            transform: rotate(-8deg) scale(1.08);
        }

        .gr-stat .gr-stat-label {
            font-size: 12px;
            color: var(--gr-muted);
            white-space: nowrap;
        }

        .gr-stat .gr-stat-value {
            font-size: 24px;
            font-weight: 700;
            color: var(--gr-ink);
            line-height: 1.1;
        }

        .gr-stat .gr-stat-bg {
            position: absolute;
            right: -8px;
            bottom: -12px;
            font-size: 54px;
            color: var(--c-fg);
            opacity: .06;
            transition: transform .4s ease, opacity .3s ease;
        }

        .gr-stat:hover .gr-stat-bg {
            transform: scale(1.15) rotate(-10deg);
            opacity: .1;
        }

        /* ================= Filter ================= */
        /* Catatan request (header) */
        /* Kolom Notes di daftar: maksimal 2 baris, lengkapnya di tooltip / tracking */
        .gr-catatan {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            max-width: 260px;
            font-size: 12px;
            line-height: 1.4;
            color: #5b6472;
            white-space: pre-line;
        }

        .gr-catatan i,
        .gr-hero-catatan i {
            margin-right: 3px;
            font-style: normal;
        }

        .gr-hero-catatan {
            display: inline-block;
            max-width: 100%;
            margin: 2px 0 8px;
            padding: 4px 10px;
            border-radius: 6px;
            background: rgba(255, 255, 255, .16);
            color: #fff;
            font-size: 12.5px;
            font-style: italic;
            white-space: pre-line;
        }

        /* Tracking: catatan request (header) di atas alur approval */
        .gr-md-catatan {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 12px;
            padding: 12px 16px;
            border: 1px solid #ecdcb4;
            border-left: 4px solid #d4a73c;
            border-radius: 12px;
            background: #fdf8ec;
        }

        .gr-md-catatan .ic {
            flex-shrink: 0;
            width: 34px;
            height: 34px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f7ebcb;
            color: #b98a2e;
        }

        .gr-md-catatan .t {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .6px;
            text-transform: uppercase;
            color: #b98a2e;
        }

        .gr-md-catatan .v {
            margin-top: 2px;
            font-size: 13.5px;
            color: #374151;
            white-space: pre-line;
        }

        .gr-md-catatan.kosong {
            border-color: #e3e9f1;
            border-left-color: #c3cedc;
            background: #f9fbfd;
        }

        .gr-md-catatan.kosong .ic {
            background: #eef1f5;
            color: #9aa6b8;
        }

        .gr-md-catatan.kosong .t {
            color: #8a96a8;
        }

        .gr-md-catatan.kosong .v {
            color: #a3aebe;
            font-style: italic;
        }

        .gr-bf-catatan {
            padding: 12px 14px;
            border: 1px solid #e3e9f1;
            border-radius: 12px;
            background: #fff;
        }

        .gr-bf-catatan label {
            font-size: 12px;
            font-weight: 600;
            color: var(--gr-muted);
            margin-bottom: 5px;
        }

        .gr-bf-catatan label i {
            color: var(--gr-accent);
        }

        .gr-bf-catatan label small {
            font-weight: 400;
            color: #a3aebe;
        }

        .gr-bf #bf_catatan {
            height: auto;
            min-height: 58px;
            resize: vertical;
            line-height: 1.45;
        }

        /* Request dibatalkan: tetap tampil, latar bergaris + stempel BATAL supaya langsung terlihat */
        #table-request tr.gr-row-batal>td {
            background: repeating-linear-gradient(135deg, #fbf4f5 0 10px, #fff 10px 20px) !important;
            color: #8a93a1;
        }

        #table-request tr.gr-row-batal>td:first-child {
            box-shadow: inset 4px 0 0 #c0505f;
        }

        #table-request tr.gr-row-batal .gr-no {
            color: #9aa3b0;
            text-decoration: line-through;
            text-decoration-color: #c0505f;
        }

        #table-request tr.gr-row-batal .gr-chip {
            background: #c0505f;
            color: #fff;
        }

        .gr-stempel-batal {
            display: inline-block;
            margin-top: 4px;
            padding: 1px 8px;
            border: 1.5px solid #c0505f;
            border-radius: 4px;
            color: #c0505f;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 1.5px;
            text-decoration: none;
            transform: rotate(-4deg);
        }

        .gr-approver-info {
            margin-top: 4px;
            font-size: 10.5px;
            color: #6b7687;
            white-space: nowrap;
        }

        .gr-approver-info i {
            color: var(--gr-accent);
        }

        .gr-approver-info i.ok {
            color: #4a8c68;
        }

        .gr-approver-info i.tolak {
            color: #c0505f;
        }

        .gr-approver-info i.tunggu {
            color: #b98a2e;
        }

        .gr-batal-info {
            margin-top: 4px;
            font-size: 10.5px;
            color: #c0505f;
        }

        .gr-filter {
            background: var(--gr-soft);
            border: 1px solid var(--gr-line);
            border-radius: 14px;
            padding: 12px 14px 14px;
        }

        .gr-filter label {
            font-size: 12px;
            font-weight: 600;
            color: var(--gr-muted);
            margin-bottom: 4px;
        }

        .gr-filter .form-control,
        .gr-filter .input-group-text {
            border-color: var(--gr-line);
            border-radius: 8px;
        }

        .gr-filter .input-group-text {
            background: #fff;
            color: var(--gr-muted);
            border-right: 0;
            border-radius: 8px 0 0 8px;
        }

        .gr-filter .input-group .form-control {
            border-left: 0;
            border-radius: 0 8px 8px 0;
        }

        .gr-filter .form-control:focus {
            border-color: var(--gr-accent);
            box-shadow: 0 0 0 3px var(--gr-accent-soft);
        }

        /* ================= Tabel ================= */
        .gr-table-wrap {
            border: 1px solid var(--gr-line);
            border-radius: 14px;
            overflow: hidden;
        }

        #table-request {
            margin: 0 !important;
            border: 0;
        }

        #table-request thead th {
            background: var(--gr-soft);
            color: #5b6576;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .3px;
            border: 0;
            border-bottom: 1px solid var(--gr-line);
            padding: 10px 8px;
            text-align: center;
            white-space: nowrap;
            vertical-align: middle;
        }

        #table-request tbody td {
            border: 0;
            border-bottom: 1px solid var(--gr-line);
            padding: 10px 8px;
            font-size: 13px;
            vertical-align: middle;
        }

        #table-request tbody tr.gr-row {
            cursor: pointer;
            transition: background .15s ease;
        }

        #table-request tbody tr.gr-row td:first-child {
            box-shadow: inset 3px 0 0 var(--c-fg);
        }

        #table-request tbody tr.gr-row:hover {
            background: #fafbfd;
        }

        #table-request tbody tr.gr-row:hover td:first-child {
            box-shadow: inset 5px 0 0 var(--c-fg);
        }

        #table-request tbody tr.gr-row.shown {
            background: var(--gr-accent-soft);
        }

        .gr-no {
            font-weight: 700;
            color: var(--gr-accent);
            letter-spacing: .2px;
        }

        .gr-caret {
            transition: transform .25s ease;
            color: #b4bccb;
        }

        tr.gr-row:hover .gr-caret {
            color: var(--gr-accent);
        }

        tr.shown .gr-caret {
            transform: rotate(90deg);
            color: var(--gr-accent);
        }

        .gr-count-item {
            background: var(--gr-soft);
            border: 1px solid var(--gr-line);
            color: #5b6576;
            border-radius: 20px;
            padding: 2px 9px;
            font-size: 11.5px;
            font-weight: 600;
        }

        .gr-progress {
            height: 7px;
            min-width: 110px;
            border-radius: 10px;
            background: #eef1f5;
        }

        .gr-progress .progress-bar {
            width: 0;
            border-radius: 10px;
            transition: width 1s cubic-bezier(.25, .8, .25, 1);
        }

        .gr-bar-sage {
            background: linear-gradient(90deg, #7cc39b, #5fa880);
        }

        .gr-bar-peach {
            background: linear-gradient(90deg, #f2c08f, #e9a46a);
        }

        .gr-muted-text {
            color: var(--gr-muted);
            font-size: 12px;
        }

        .gr-due {
            font-size: 11px;
            font-weight: 600;
            border-radius: 20px;
            padding: 1px 9px;
            display: inline-block;
            margin-top: 3px;
        }

        .gr-due.late {
            background: #fbeaec;
            color: #c0505f;
            animation: gr-blink 1.6s ease-in-out infinite;
        }

        .gr-due.soon {
            background: #fdf1e4;
            color: #c27a3a;
        }

        .gr-due.ok {
            background: #e8f4ed;
            color: #4a8c68;
        }

        @keyframes gr-blink {
            50% {
                opacity: .5;
            }
        }

        .gr-act-btn {
            width: 32px;
            height: 32px;
            border-radius: 10px;
            border: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: var(--c-bg);
            color: var(--c-fg);
            font-size: 13px;
            transition: transform .15s ease, background .2s ease, color .2s ease, box-shadow .2s ease;
        }

        .gr-act-btn:hover {
            background: var(--c-fg);
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, .12);
        }

        .gr-act-btn+.gr-act-btn {
            margin-left: 4px;
        }

        /* ================= Filter ringkas ================= */
        .gr-cari-bar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px;
        }

        .gr-cari,
        .gr-rentang {
            display: flex;
            align-items: center;
            gap: 8px;
            height: 38px;
            margin: 0;
            padding: 0 12px;
            background: #fff;
            border: 1px solid var(--gr-line);
            border-radius: 10px;
            color: var(--gr-muted);
            font-weight: 400;
            transition: border-color .15s ease, box-shadow .15s ease;
        }

        .gr-cari {
            flex: 1 1 280px;
            cursor: text;
        }

        .gr-cari:focus-within,
        .gr-rentang:focus-within {
            border-color: var(--gr-accent);
            box-shadow: 0 0 0 3px var(--gr-accent-soft);
        }

        .gr-cari input,
        .gr-rentang input {
            border: 0;
            outline: 0;
            background: transparent;
            color: var(--gr-ink);
            font-size: 13px;
        }

        .gr-cari input {
            flex: 1;
            min-width: 0;
        }

        .gr-cari input::placeholder {
            color: #a3aebe;
        }

        .gr-rentang input {
            width: 128px;
        }

        .gr-cari-bar .gr-ket-btn {
            height: 38px;
        }

        @media (max-width: 576px) {
            .gr-rentang {
                flex: 1 1 100%;
            }

            .gr-rentang input {
                flex: 1;
                width: auto;
                min-width: 0;
            }
        }

        /* ================= Baris berwarna sesuai status ================= */
        #table-request tbody tr.gr-row>td {
            background: color-mix(in srgb, var(--c-bg) 55%, #fff);
            transition: background .15s ease;
        }

        #table-request tbody tr.gr-row:hover>td {
            background: var(--c-bg);
        }

        #table-request tbody tr.gr-row.shown>td {
            background: var(--c-bg);
        }

        #table-request tbody tr.gr-row td:first-child,
        #table-request tbody tr.gr-row:hover td:first-child {
            box-shadow: inset 4px 0 0 var(--c-fg);
        }

        /* Langkah status: Draft - Approval - PO - Diterima */
        .gr-langkah {
            display: flex;
            align-items: center;
            width: 112px;
            margin: 7px 0 1px 2px;
        }

        .gr-langkah span {
            flex-shrink: 0;
            width: 9px;
            height: 9px;
            border-radius: 50%;
            border: 2px solid color-mix(in srgb, var(--c-fg) 35%, #fff);
            background: #fff;
        }

        .gr-langkah span.lewat {
            border-color: var(--c-fg);
            background: var(--c-fg);
        }

        .gr-langkah span.kini {
            border-color: var(--c-fg);
            background: #fff;
            box-shadow: 0 0 0 3px color-mix(in srgb, var(--c-fg) 22%, transparent);
            animation: gr-langkah-kini 1.6s ease-in-out infinite;
        }

        .gr-langkah i {
            flex: 1;
            height: 2px;
            background: color-mix(in srgb, var(--c-fg) 25%, #fff);
        }

        .gr-langkah i.lewat {
            background: var(--c-fg);
        }

        .gr-langkah.tolak span.kini {
            background: var(--c-fg);
            animation: none;
        }

        .gr-langkah {
            cursor: help;
            padding: 3px 0;
        }

        /* Kotak riwayat langkah (melayang) */
        #grRiwayat {
            position: fixed;
            z-index: 1060;
            width: 270px;
            padding: 12px 14px 4px;
            background: #fff;
            border: 1px solid var(--gr-line);
            border-radius: 12px;
            box-shadow: 0 12px 30px rgba(47, 58, 74, .16);
            font-size: 12px;
            color: #5b6576;
            pointer-events: none;
            animation: gr-rise .15s ease;
        }

        #grRiwayat .r {
            position: relative;
            padding: 0 0 10px 20px;
        }

        /* titik + garis penghubung */
        #grRiwayat .r::before {
            content: '';
            position: absolute;
            left: 0;
            top: 3px;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            border: 2px solid #d5dde8;
            background: #fff;
        }

        #grRiwayat .r:not(:last-child)::after {
            content: '';
            position: absolute;
            left: 4px;
            top: 15px;
            bottom: 1px;
            width: 2px;
            background: #e6ebf2;
        }

        #grRiwayat .r.lewat::before {
            border-color: var(--c-fg);
            background: var(--c-fg);
        }

        #grRiwayat .r.lewat::after {
            background: var(--c-fg);
        }

        #grRiwayat .r.kini::before {
            border-color: var(--c-fg);
            box-shadow: 0 0 0 3px color-mix(in srgb, var(--c-fg) 22%, transparent);
        }

        #grRiwayat .r.tolak::before {
            background: var(--c-fg);
        }

        #grRiwayat .r b {
            display: block;
            color: var(--gr-ink);
            font-size: 12.5px;
        }

        #grRiwayat .r:not(.lewat):not(.kini) b,
        #grRiwayat .r:not(.lewat):not(.kini) div {
            color: #a3aebe;
        }

        #grRiwayat .r i.ok {
            color: #4a8c68;
        }

        #grRiwayat .r i.fa-hourglass-half {
            color: #b98a2e;
        }

        @keyframes gr-langkah-kini {
            50% {
                box-shadow: 0 0 0 5px color-mix(in srgb, var(--c-fg) 10%, transparent);
            }
        }

        /* ================= Tabel ringkas: teks utama + keterangan kecil ================= */
        #table-request_processing {
            display: none !important;
        }

        .gr-table-wrap tbody {
            transition: opacity .2s ease;
        }

        .gr-table-wrap.gr-memuat tbody {
            opacity: .45;
            pointer-events: none;
        }

        /* Garis loading tipis di atas tabel saat filter memuat */
        .gr-table-wrap {
            position: relative;
        }

        .gr-table-wrap::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            z-index: 5;
            width: 35%;
            height: 3px;
            border-radius: 3px;
            background: var(--gr-accent);
            opacity: 0;
            pointer-events: none;
        }

        .gr-table-wrap.gr-memuat::before {
            opacity: 1;
            animation: gr-loading-bar 1s ease-in-out infinite;
        }

        @keyframes gr-loading-bar {
            from {
                left: -35%;
            }

            to {
                left: 100%;
            }
        }

        .gr-excel-btn:disabled {
            opacity: .7;
            cursor: wait;
        }

        #table-request thead th {
            text-align: left;
            font-size: 11px;
            letter-spacing: .5px;
            color: #6b7687;
            padding: 11px 10px;
        }

        #table-request tbody td {
            padding: 12px 10px;
        }

        #table-request .gr-no {
            font-family: SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 12.5px;
            color: var(--gr-ink);
        }

        #table-request .gr-main {
            color: var(--gr-ink);
            font-size: 13px;
            line-height: 1.35;
        }

        #table-request .gr-sub {
            margin-top: 3px;
            color: var(--gr-muted);
            font-size: 11.5px;
            line-height: 1.3;
        }

        #table-request .gr-sub.gr-due-soon {
            color: #c27a3a;
        }

        #table-request .gr-sub.gr-due-late {
            color: #c0505f;
            font-weight: 600;
        }

        #table-request .gr-stempel-batal {
            margin: 0 0 0 4px;
            font-size: 9px;
        }

        #table-request .gr-catatan {
            font-size: 12.5px;
        }

        #table-request .gr-terima {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        #table-request .gr-terima .gr-progress {
            display: inline-flex;
            min-width: 0;
            width: 60px;
            height: 5px;
        }

        /* Isi baris yang dibuka */
        .gr-child {
            padding: 6px 16px 12px 48px;
            background: #fbfcfe;
            animation: gr-rise .3s ease;
        }

        .gr-child-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 8px 0;
            border-bottom: 1px dashed var(--gr-line);
            font-size: 13px;
        }

        .gr-child-item:last-child {
            border-bottom: 0;
        }

        .gr-child-item .ic {
            width: 28px;
            height: 28px;
            border-radius: 8px;
            background: var(--c-bg);
            color: var(--c-fg);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
        }

        .gr-child-item .nama {
            flex: 1;
            min-width: 160px;
        }

        .gr-child-item .angka {
            width: 130px;
            font-size: 12px;
            color: var(--gr-muted);
            text-align: right;
        }

        .gr-child-item .bar {
            width: 160px;
        }

        .gr-child-item .sisa {
            width: 80px;
            text-align: right;
            font-weight: 700;
            font-size: 12px;
        }

        .gr-child-item .nama .ket {
            font-size: 11.5px;
            color: var(--gr-muted);
            margin-top: 1px;
        }

        .gr-child-item .nama .ket i {
            color: var(--c-fg);
        }

        /* Panel keterangan status */
        .gr-page .gr-excel-btn,
        .gr-excel-btn {
            color: #2f7a52 !important;
        }

        .gr-excel-btn:hover {
            background: #e5f4ec !important;
        }

        .gr-ket-btn {
            border: 1px solid var(--gr-line);
            background: #fff;
            color: var(--gr-accent);
            border-radius: 20px;
            padding: 4px 12px;
            font-size: 12px;
            font-weight: 600;
            transition: background .2s ease, color .2s ease, transform .15s ease, box-shadow .2s ease;
        }

        .gr-ket-btn:hover {
            background: var(--gr-accent);
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(74, 111, 165, .25);
        }

        .gr-ket-btn i {
            transition: transform .3s ease;
        }

        .gr-ket-btn:hover i {
            transform: rotate(-15deg) scale(1.15);
        }

        /* Modal keterangan */
        .gr-ket-modal {
            border: 0;
            border-radius: 18px;
            overflow: hidden;
        }

        .gr-ket-hero {
            position: relative;
            overflow: hidden;
            color: #fff;
            flex-shrink: 0;
            padding: 18px 22px 20px;
            background: linear-gradient(135deg, #3f5f8f 0%, #5b7fb5 55%, #86a5d3 100%);
        }

        .gr-ket-hero .blob {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, .08);
            animation: gr-float 9s ease-in-out infinite;
        }

        .gr-ket-hero .b1 {
            width: 160px;
            height: 160px;
            top: -70px;
            right: 60px;
        }

        .gr-ket-hero .b2 {
            width: 100px;
            height: 100px;
            bottom: -50px;
            right: -20px;
            animation-delay: -4s;
        }

        .gr-ket-hero .close {
            position: absolute;
            top: 10px;
            right: 14px;
            color: #fff;
            opacity: .8;
            font-size: 26px;
            text-shadow: none;
            background: none;
            border: 0;
            z-index: 2;
        }

        .gr-ket-hero .close:hover {
            opacity: 1;
        }

        .gr-ket-judul {
            font-size: 20px;
            font-weight: 700;
            position: relative;
        }

        .gr-ket-hero .gr-hero-label,
        .gr-ket-hero .gr-hero-kep {
            position: relative;
        }

        #modalKeterangan .modal-body {
            padding: 16px 18px;
            background: #fbfcfe;
        }

        /* nama keyframe berbeda supaya animasi diputar ulang tiap modal dibuka */
        #modalKeterangan.show .gr-ket-item {
            animation-name: gr-rise-ket;
        }

        @keyframes gr-rise-ket {
            from {
                opacity: 0;
                transform: translateY(12px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .gr-ket-tip {
            margin-top: 12px;
            font-size: 12px;
            color: #5b6576;
            background: #fbf3df;
            border-radius: 10px;
            padding: 8px 12px;
        }

        .gr-ket-tip>i:first-child {
            color: #b98a2e;
            margin-right: 4px;
        }

        .gr-ket-panel {
            border: 1px solid var(--gr-line);
            border-radius: 14px;
            background: #fff;
            padding: 12px 14px;
        }

        .gr-ket-row {
            display: flex;
            gap: 12px;
            align-items: flex-start;
        }

        .gr-ket-row+.gr-ket-row {
            border-top: 1px dashed var(--gr-line);
            margin-top: 10px;
            padding-top: 10px;
        }

        .gr-ket-title {
            width: 110px;
            flex-shrink: 0;
            font-size: 11.5px;
            font-weight: 700;
            color: #5b6576;
            text-transform: uppercase;
            letter-spacing: .4px;
            padding-top: 6px;
        }

        .gr-ket-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(230px, 1fr));
            gap: 8px;
            flex: 1;
        }

        .gr-ket-item {
            display: flex;
            gap: 10px;
            align-items: flex-start;
            padding: 6px 8px;
            border-radius: 10px;
            transition: background .2s ease, transform .2s ease;
            opacity: 0;
            animation: gr-rise .35s ease forwards;
        }

        .gr-ket-item:hover {
            background: var(--c-bg);
            transform: translateY(-1px);
        }

        .gr-ket-item .ic {
            width: 30px;
            height: 30px;
            border-radius: 9px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            background: var(--c-bg);
            color: var(--c-fg);
        }

        .gr-ket-item .t {
            font-size: 12.5px;
            font-weight: 700;
            color: var(--c-fg);
        }

        .gr-ket-item .d {
            font-size: 11.5px;
            color: var(--gr-muted);
            line-height: 1.35;
        }

        @media (max-width: 767px) {
            .gr-ket-row {
                flex-direction: column;
                gap: 4px;
            }
        }

        .gr-sisa-ada {
            color: #c0505f;
        }

        .gr-sisa-nol {
            color: #4a8c68;
        }

        .gr-page .dataTables_info {
            font-size: 12px;
            color: var(--gr-muted);
            padding: 10px 14px !important;
        }

        .gr-page .dataTables_paginate {
            padding: 6px 10px;
        }

        .gr-page .page-link {
            border: 0;
            border-radius: 8px !important;
            margin: 0 2px;
            color: var(--gr-accent);
        }

        .gr-page .page-item.active .page-link {
            background: var(--gr-accent);
        }

        /* ================= Modal tracking ================= */
        #modalTracking .modal-content {
            border: 0;
            border-radius: 18px;
            overflow: hidden;
            background: #fbfcfe;
        }

        #modalTracking .modal-body {
            padding: 18px 22px;
        }

        /* Hero header */
        .gr-hero {
            position: relative;
            overflow: hidden;
            color: #fff;
            flex-shrink: 0;
            /* jangan ikut menyusut di modal-dialog-scrollable */
            padding: 20px 24px 22px;
            background: linear-gradient(135deg, #3f5f8f 0%, #5b7fb5 55%, #86a5d3 100%);
        }

        .gr-hero .blob {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, .08);
            animation: gr-float 9s ease-in-out infinite;
        }

        .gr-hero .b1 {
            width: 220px;
            height: 220px;
            top: -90px;
            right: 120px;
        }

        .gr-hero .b2 {
            width: 140px;
            height: 140px;
            bottom: -70px;
            right: -30px;
            animation-delay: -3s;
        }

        .gr-hero .b3 {
            width: 70px;
            height: 70px;
            top: 30px;
            left: 45%;
            animation-delay: -6s;
            background: rgba(255, 255, 255, .06);
        }

        .gr-hero .dots {
            position: absolute;
            inset: 0;
            opacity: .12;
            background-image: radial-gradient(#fff 1px, transparent 1.2px);
            background-size: 18px 18px;
            -webkit-mask-image: linear-gradient(90deg, transparent, #000 70%);
            mask-image: linear-gradient(90deg, transparent, #000 70%);
        }

        @keyframes gr-float {

            0%,
            100% {
                transform: translate(0, 0) scale(1);
            }

            50% {
                transform: translate(14px, -12px) scale(1.06);
            }
        }

        .gr-hero .close {
            position: absolute;
            top: 12px;
            right: 16px;
            color: #fff;
            opacity: .8;
            z-index: 2;
            font-size: 26px;
            text-shadow: none;
            background: none;
            border: 0;
        }

        .gr-hero .close:hover {
            opacity: 1;
        }

        .gr-hero-label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            opacity: .8;
        }

        .gr-hero-no {
            font-size: 24px;
            font-weight: 700;
            letter-spacing: .5px;
            animation: gr-rise .5s ease;
        }

        .gr-hero-kep {
            font-size: 13.5px;
            opacity: .92;
            margin-top: 2px;
        }

        .gr-hero-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 12px;
        }

        .gr-hero-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255, 255, 255, .16);
            border: 1px solid rgba(255, 255, 255, .22);
            border-radius: 20px;
            padding: 3px 11px;
            font-size: 12px;
            opacity: 0;
            animation: gr-rise .4s ease forwards;
        }

        .gr-hero-ring {
            position: relative;
            width: 96px;
            height: 96px;
            flex-shrink: 0;
        }

        .gr-hero-ring svg {
            transform: rotate(-90deg);
        }

        .gr-hero-ring circle {
            fill: none;
            stroke-width: 8;
        }

        .gr-hero-ring .bg {
            stroke: rgba(255, 255, 255, .2);
        }

        .gr-hero-ring .fg {
            stroke: #fff;
            stroke-linecap: round;
            transition: stroke-dashoffset 1.4s cubic-bezier(.25, .8, .25, 1);
        }

        .gr-hero-ring .label {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .gr-hero-ring .label b {
            font-size: 22px;
            line-height: 1;
        }

        .gr-hero-ring .label small {
            font-size: 10px;
            opacity: .85;
        }

        /* Strip approval (level request) */
        .gr-approval {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
            background: #fff;
            border: 1px solid var(--gr-line);
            border-radius: 14px;
            padding: 10px 14px;
            margin-bottom: 16px;
            opacity: 0;
            animation: gr-rise .4s ease .15s forwards;
        }

        .gr-appr-step {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12.5px;
        }

        .gr-appr-step .ic {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            background: var(--c-bg);
            color: var(--c-fg);
        }

        .gr-appr-step.nanti {
            opacity: .55;
        }

        .gr-appr-step.wait .ic {
            animation: gr-pulse-amber 1.8s infinite;
        }

        .gr-appr-step .t {
            font-weight: 600;
        }

        .gr-appr-step .m {
            font-size: 11px;
            color: var(--gr-muted);
        }

        .gr-appr-arrow {
            color: #c3cad6;
        }

        .gr-appr-note {
            margin-left: auto;
            font-size: 12px;
            background: #f9e8ea;
            color: #c0505f;
            border-radius: 8px;
            padding: 4px 10px;
        }

        .gr-section-title {
            font-size: 12px;
            font-weight: 700;
            color: #5b6576;
            text-transform: uppercase;
            letter-spacing: .6px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .gr-section-title .cnt {
            background: var(--gr-accent-soft);
            color: var(--gr-accent);
            border-radius: 20px;
            padding: 0 8px;
            font-size: 11px;
        }

        /* Daftar item (kiri) */
        .gr-item-list {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .gr-item-card {
            position: relative;
            display: flex;
            align-items: center;
            gap: 12px;
            background: #fff;
            border: 1px solid var(--gr-line);
            border-radius: 14px;
            padding: 10px 12px;
            cursor: pointer;
            transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease, background .25s ease;
            opacity: 0;
            animation: gr-rise .4s ease forwards;
        }

        .gr-item-card::before {
            content: '';
            position: absolute;
            left: 0;
            top: 14px;
            bottom: 14px;
            width: 4px;
            border-radius: 0 4px 4px 0;
            background: var(--c-fg);
            transform: scaleY(0);
            transition: transform .25s ease;
        }

        .gr-item-card:hover {
            transform: translateX(3px);
            box-shadow: 0 6px 16px rgba(47, 58, 74, .06);
        }

        .gr-item-card.active {
            border-color: var(--c-fg);
            background: var(--c-bg);
            transform: translateX(6px);
            box-shadow: 0 8px 20px rgba(47, 58, 74, .08);
        }

        .gr-item-card.active::before {
            transform: scaleY(1);
        }

        .gr-item-card .nm {
            font-weight: 600;
            font-size: 13px;
            line-height: 1.3;
        }

        .gr-item-card .sub {
            font-size: 11.5px;
            color: var(--gr-muted);
            margin-top: 2px;
        }

        .gr-item-card .arrow {
            margin-left: auto;
            color: #c3cad6;
            transition: transform .25s ease, color .25s ease;
        }

        .gr-item-card.active .arrow {
            color: var(--c-fg);
            transform: translateX(3px);
        }

        .gr-mini-ring {
            position: relative;
            width: 44px;
            height: 44px;
            flex-shrink: 0;
        }

        .gr-mini-ring svg {
            transform: rotate(-90deg);
        }

        .gr-mini-ring circle {
            fill: none;
            stroke-width: 4.5;
        }

        .gr-mini-ring .bg {
            stroke: #eef1f5;
        }

        .gr-mini-ring .fg {
            stroke: var(--c-fg);
            stroke-linecap: round;
            transition: stroke-dashoffset 1.1s ease;
        }

        .gr-mini-ring .ic {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            color: var(--c-fg);
        }

        /* Cari & legend item */
        .gr-item-search {
            position: relative;
            margin-bottom: 8px;
        }

        .gr-item-search i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gr-muted);
            font-size: 12px;
        }

        .gr-item-search input {
            width: 100%;
            border: 1px solid var(--gr-line);
            border-radius: 10px;
            padding: 7px 30px 7px 32px;
            font-size: 13px;
            background: #fff;
            transition: border-color .2s ease, box-shadow .2s ease;
        }

        .gr-item-search input:focus {
            outline: 0;
            border-color: var(--gr-accent);
            box-shadow: 0 0 0 3px var(--gr-accent-soft);
        }

        .gr-item-search .clear {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gr-muted);
            cursor: pointer;
            display: none;
        }

        .gr-legend {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-bottom: 10px;
        }

        .gr-legend .lg {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 3px 4px 3px 10px;
            border-radius: 20px;
            cursor: pointer;
            user-select: none;
            font-size: 11.5px;
            font-weight: 600;
            background: var(--c-bg);
            color: var(--c-fg);
            border: 1px solid transparent;
            transition: transform .15s ease, opacity .2s ease, border-color .2s ease, box-shadow .2s ease;
            opacity: 0;
            animation: gr-rise .35s ease forwards;
        }

        .gr-legend .lg::before {
            content: '';
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: var(--c-fg);
        }

        .gr-legend .lg .n {
            background: #fff;
            border-radius: 20px;
            min-width: 20px;
            padding: 0 6px;
            text-align: center;
            font-size: 11px;
        }

        .gr-legend .lg:hover {
            transform: translateY(-1px);
        }

        .gr-legend .lg.kosong {
            filter: grayscale(.6);
        }

        .gr-legend .lg.kosong .n {
            color: var(--gr-muted);
        }

        .gr-legend.ada-filter .lg:not(.active) {
            opacity: .45 !important;
        }

        .gr-legend .lg.active {
            border-color: var(--c-fg);
            box-shadow: 0 2px 8px rgba(47, 58, 74, .12);
        }

        .gr-legend-ket {
            font-size: 11.5px;
            color: var(--gr-muted);
            min-height: 16px;
            margin: -4px 0 10px;
            transition: opacity .2s ease;
        }

        .gr-item-card.hide {
            display: none;
        }

        .gr-item-empty {
            text-align: center;
            color: var(--gr-muted);
            font-size: 12.5px;
            padding: 22px 0;
            border: 1px dashed var(--gr-line);
            border-radius: 14px;
            display: none;
        }

        .gr-item-empty i {
            font-size: 24px;
            display: block;
            margin-bottom: 6px;
            opacity: .6;
        }

        .gr-item-card mark {
            background: #fbf3df;
            color: inherit;
            padding: 0 1px;
            border-radius: 3px;
        }

        /* Panel detail item (kanan) */
        .gr-detail {
            position: relative;
            border-radius: 16px;
            border: 1px solid var(--gr-line);
            background-color: #fff;
            background-image: radial-gradient(#edf0f5 1px, transparent 1.2px);
            background-size: 16px 16px;
            padding: 18px;
            min-height: 100%;
        }

        .gr-detail-inner.in {
            animation: gr-swap-in .4s ease both;
        }

        .gr-detail-inner.out {
            animation: gr-swap-out .15s ease both;
        }

        @keyframes gr-swap-in {
            from {
                opacity: 0;
                transform: translateY(10px) scale(.99);
            }

            to {
                opacity: 1;
                transform: none;
            }
        }

        @keyframes gr-swap-out {
            to {
                opacity: 0;
                transform: translateY(-6px);
            }
        }

        .gr-d-head {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 16px;
        }

        .gr-d-icon {
            position: relative;
            width: 52px;
            height: 52px;
            border-radius: 16px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: #fff;
            background: var(--c-fg);
            background: linear-gradient(135deg, var(--c-fg), color-mix(in srgb, var(--c-fg) 60%, #fff));
            box-shadow: 0 8px 18px rgba(47, 58, 74, .15);
            animation: gr-pop .5s cubic-bezier(.34, 1.56, .64, 1);
        }

        /* Ada gambar barang: foto mengisi kotak, ikon status pindah ke pojok */
        .gr-d-icon img {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            border-radius: inherit;
            object-fit: cover;
            background: #fff;
            opacity: 0;
            transition: opacity .25s ease;
        }

        .gr-d-icon .st {
            position: absolute;
            right: -5px;
            bottom: -5px;
            width: 22px;
            height: 22px;
            border: 2px solid #fff;
            border-radius: 50%;
            background: var(--c-fg);
            color: #fff;
            font-size: 9px;
            display: none;
            align-items: center;
            justify-content: center;
        }

        .gr-d-icon.gb-ok {
            width: 64px;
            height: 64px;
            background: #fff;
        }

        .gr-d-icon.gb-ok>i {
            visibility: hidden;
        }

        .gr-d-icon.gb-ok img {
            opacity: 1;
        }

        .gr-d-icon.gb-ok .st {
            display: flex;
        }

        @keyframes gr-pop {
            from {
                transform: scale(.4) rotate(-15deg);
                opacity: 0;
            }

            to {
                transform: none;
                opacity: 1;
            }
        }

        .gr-d-name {
            font-size: 16px;
            font-weight: 700;
            line-height: 1.25;
        }

        .gr-d-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 5px;
        }

        .gr-tag {
            font-size: 11px;
            background: var(--gr-soft);
            border: 1px solid var(--gr-line);
            color: #5b6576;
            border-radius: 6px;
            padding: 1px 8px;
        }

        /* Partikel saat item lengkap */
        .gr-spark {
            position: absolute;
            left: 50%;
            top: 50%;
            width: 6px;
            height: 6px;
            border-radius: 50%;
            pointer-events: none;
            animation: gr-burst .8s ease-out forwards;
        }

        @keyframes gr-burst {
            from {
                transform: translate(-50%, -50%) scale(1);
                opacity: 1;
            }

            to {
                transform: translate(calc(-50% + var(--dx)), calc(-50% + var(--dy))) scale(.2);
                opacity: 0;
            }
        }

        .gr-tiles {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
            margin-bottom: 6px;
        }

        .gr-tile {
            background: #fff;
            border: 1px solid var(--gr-line);
            border-radius: 12px;
            padding: 10px;
            position: relative;
            overflow: hidden;
            opacity: 0;
            animation: gr-rise .4s ease forwards;
        }

        .gr-tile::after {
            content: '';
            position: absolute;
            left: 0;
            right: 0;
            top: 0;
            height: 3px;
            background: var(--t-color);
        }

        .gr-tile .l {
            font-size: 11px;
            color: var(--gr-muted);
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .gr-tile .l i {
            color: var(--t-color);
        }

        .gr-tile .v {
            font-size: 20px;
            font-weight: 700;
            color: var(--gr-ink);
            line-height: 1.2;
        }

        .gr-tile .v small {
            font-size: 11px;
            font-weight: 500;
            color: var(--gr-muted);
        }

        /* Stepper per item */
        .gr-stepper {
            display: flex;
            justify-content: space-between;
            position: relative;
            margin: 18px 0 20px;
        }

        .gr-stepper::before {
            content: '';
            position: absolute;
            top: 20px;
            left: 12.5%;
            right: 12.5%;
            height: 4px;
            border-radius: 4px;
            background: #eef1f5;
            z-index: 0;
        }

        .gr-stepper .gr-line-fill {
            position: absolute;
            top: 20px;
            left: 12.5%;
            height: 4px;
            border-radius: 4px;
            background: linear-gradient(90deg, #7cc39b, #5fa880);
            z-index: 0;
            width: 0;
            transition: width 1.1s ease;
        }

        .gr-step {
            position: relative;
            z-index: 1;
            text-align: center;
            flex: 1;
        }

        .gr-step .gr-dot {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            margin: 0 auto 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fff;
            border: 3px solid #e3e8ef;
            color: #c3cad6;
            font-size: 14px;
            transition: all .35s cubic-bezier(.34, 1.56, .64, 1);
        }

        .gr-step.done .gr-dot {
            background: #5fa880;
            border-color: #5fa880;
            color: #fff;
            transform: scale(1.06);
        }

        .gr-step.current .gr-dot {
            border-color: #4a6fa5;
            color: #4a6fa5;
            background: #e9f0fa;
            animation: gr-pulse 1.8s infinite;
        }

        .gr-step.partial .gr-dot {
            border-color: #e9a46a;
            color: #c47f45;
            background: #fcefe3;
            animation: gr-pulse-peach 1.8s infinite;
        }

        .gr-step.rejected .gr-dot {
            background: #d26b78;
            border-color: #d26b78;
            color: #fff;
            animation: gr-shake .5s ease;
        }

        .gr-step .gr-step-label {
            font-size: 12px;
            font-weight: 600;
            color: #b4bccb;
            transition: color .3s ease;
        }

        .gr-step.done .gr-step-label,
        .gr-step.current .gr-step-label,
        .gr-step.partial .gr-step-label {
            color: var(--gr-ink);
        }

        .gr-step.rejected .gr-step-label {
            color: #c0505f;
        }

        .gr-step .gr-step-date {
            font-size: 10.5px;
            color: var(--gr-muted);
            min-height: 14px;
        }

        @keyframes gr-pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(74, 111, 165, .4);
            }

            70% {
                box-shadow: 0 0 0 12px rgba(74, 111, 165, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(74, 111, 165, 0);
            }
        }

        @keyframes gr-pulse-peach {
            0% {
                box-shadow: 0 0 0 0 rgba(233, 164, 106, .45);
            }

            70% {
                box-shadow: 0 0 0 12px rgba(233, 164, 106, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(233, 164, 106, 0);
            }
        }

        @keyframes gr-pulse-amber {
            0% {
                box-shadow: 0 0 0 0 rgba(185, 138, 46, .4);
            }

            70% {
                box-shadow: 0 0 0 10px rgba(185, 138, 46, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(185, 138, 46, 0);
            }
        }

        @keyframes gr-shake {

            0%,
            100% {
                transform: translateX(0);
            }

            25% {
                transform: translateX(-4px);
            }

            75% {
                transform: translateX(4px);
            }
        }

        /* Riwayat per item */
        .gr-timeline {
            list-style: none;
            padding-left: 0;
            margin: 0;
        }

        .gr-timeline li {
            position: relative;
            padding: 0 0 14px 28px;
            border-left: 2px solid var(--gr-line);
            margin-left: 10px;
            opacity: 0;
            animation: gr-slide .4s ease forwards;
        }

        .gr-timeline li:last-child {
            border-left-color: transparent;
            padding-bottom: 0;
        }

        .gr-timeline li .tl-ic {
            position: absolute;
            left: -11px;
            top: -1px;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 9px;
            background: var(--tl-bg);
            color: var(--tl-fg);
            box-shadow: 0 0 0 3px #fff;
        }

        .gr-timeline li.pending .tl-ic {
            background: #fff;
            border: 2px dashed #c3cad6;
            color: #b4bccb;
        }

        .gr-timeline li.pending .tl-ic i {
            animation: gr-spin 2s linear infinite;
        }

        .gr-timeline li.pending .gr-tl-title {
            color: var(--gr-muted);
            font-style: italic;
        }

        @keyframes gr-spin {
            to {
                transform: rotate(360deg);
            }
        }

        .gr-timeline .gr-tl-title {
            font-weight: 600;
            font-size: 13px;
        }

        .gr-timeline .gr-tl-meta {
            font-size: 11px;
            color: var(--gr-muted);
        }

        .gr-timeline .gr-tl-ket {
            font-size: 12px;
            background: var(--gr-soft);
            border-radius: 6px;
            padding: 2px 8px;
            display: inline-block;
            margin-top: 4px;
        }

        .gr-timeline .gr-tl-qty {
            font-weight: 700;
            color: #4a8c68;
        }

        @keyframes gr-slide {
            from {
                opacity: 0;
                transform: translateX(-10px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* Role Access */
        /* ================= Modal Role Access ================= */
        .ra-modal {
            border: 0;
            border-radius: 14px;
            overflow: hidden;
            color: #1f2937;
        }

        .ra-head {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 18px 24px;
            background: #fff;
            border-bottom: 1px solid #e5e9f0;
        }

        .ra-head-ic {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: #eef3fa;
            color: var(--gr-accent);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            flex-shrink: 0;
        }

        .ra-head-judul {
            font-size: 17px;
            font-weight: 700;
            color: #111827;
            line-height: 1.2;
        }

        .ra-head-sub {
            font-size: 12.5px;
            color: #6b7280;
            margin-top: 2px;
        }

        .ra-close {
            width: 34px;
            height: 34px;
            border: 0;
            border-radius: 8px;
            background: transparent;
            color: #9ca3af;
            transition: background .15s ease, color .15s ease;
        }

        .ra-close:hover {
            background: #f3f4f6;
            color: #374151;
        }

        #modalRoleAccess .ra-body {
            padding: 0;
            display: grid;
            grid-template-columns: 360px 1fr;
            height: min(560px, 68vh);
            background: #fff;
            overflow: hidden;
        }

        .ra-side {
            background: #f8fafc;
            border-right: 1px solid #e5e9f0;
            padding: 20px 22px;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
        }

        .ra-main {
            display: flex;
            flex-direction: column;
            min-width: 0;
            min-height: 0;
        }

        .ra-cap {
            font-size: 11.5px;
            font-weight: 700;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: .6px;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .ra-count {
            background: #eef3fa;
            color: var(--gr-accent);
            border-radius: 20px;
            padding: 1px 8px;
            font-size: 11px;
            letter-spacing: 0;
        }

        .ra-field {
            margin-bottom: 14px;
        }

        #modalRoleAccess .ra-field label {
            font-size: 12.5px;
            font-weight: 600 !important;
            color: #374151;
            margin-bottom: 5px;
        }

        /* Role Access: daftar centang departemen */
        .ra-cek {
            border: 1px solid #d9e2ed;
            border-radius: 10px;
            background: #fff;
            overflow: hidden;
        }

        .ra-cek-head {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 10px;
            background: #f2f5f9;
            border-bottom: 1px solid #e3e9f1;
        }

        .ra-cek-all {
            display: flex;
            align-items: center;
            gap: 6px;
            margin: 0;
            font-size: 12.5px;
            font-weight: 700;
            color: #1f2937;
            white-space: nowrap;
            cursor: pointer;
        }

        .ra-cek-all span {
            font-weight: 400;
            color: #8a96a8;
        }

        .ra-cek-cari {
            position: relative;
            flex: 1;
            min-width: 0;
        }

        .ra-cek-cari i {
            position: absolute;
            left: 9px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 11px;
            color: #a3aebe;
        }

        .ra-cek-cari input {
            width: 100%;
            height: 28px;
            padding: 0 8px 0 26px;
            border: 1px solid #d5dee9;
            border-radius: 6px;
            font-size: 12px;
        }

        .ra-cek-cari input:focus {
            outline: 0;
            border-color: var(--gr-accent);
            box-shadow: 0 0 0 3px var(--gr-accent-soft);
        }

        .ra-cek input[type=checkbox] {
            width: 15px;
            height: 15px;
            margin: 0;
            accent-color: var(--gr-accent);
            cursor: pointer;
            flex-shrink: 0;
        }

        .ra-cek-list {
            max-height: 290px;
            overflow-y: auto;
            padding-bottom: 4px;
        }

        .ra-cek .grp.sembunyi,
        .ra-cek .it.sembunyi {
            display: none !important;
        }

        .ra-cek .gh {
            position: sticky;
            top: 0;
            z-index: 1;
            display: flex;
            align-items: center;
            gap: 6px;
            margin: 0;
            padding: 6px 10px;
            background: #fff;
            border-bottom: 1px dashed #e3e9f1;
            font-size: 11.5px;
            cursor: pointer;
        }

        .ra-cek .gh b {
            padding: 1px 7px;
            border-radius: 4px;
            font-size: 10.5px;
            letter-spacing: .5px;
        }

        .ra-cek .gh.site-nag b {
            background: #e3edfb;
            color: #3b6bb5;
        }

        .ra-cek .gh.site-nak b {
            background: #e2f4ec;
            color: #2f8a5f;
        }

        .ra-cek .gh .pt {
            flex: 1;
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            color: #8a96a8;
            font-size: 10.5px;
        }

        .ra-cek .gh .n {
            font-family: 'Consolas', monospace;
            font-size: 11px;
            font-weight: 700;
            color: #6b7687;
        }

        .ra-cek .isi {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2px 6px;
            padding: 4px 8px 8px;
        }

        .ra-cek .it {
            display: flex;
            align-items: center;
            gap: 7px;
            margin: 0;
            padding: 5px 6px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
            color: #374151;
            cursor: pointer;
            transition: background .12s ease;
        }

        .ra-cek .it span {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .ra-cek .it:hover {
            background: #f4f8fd;
        }

        .ra-cek .it.dipilih {
            background: #eef3f9;
            color: var(--gr-accent);
            font-weight: 700;
        }

        .ra-cek-foot {
            padding: 6px 10px;
            border-top: 1px solid #e3e9f1;
            background: #fafbfd;
            font-size: 11.5px;
            color: #8a96a8;
        }

        .ra-cek-foot.ada {
            color: var(--gr-accent);
        }

        .ra-hint {
            font-size: 11.5px;
            color: #9ca3af;
            margin-top: 4px;
        }

        .ra-note {
            display: flex;
            gap: 8px;
            font-size: 12px;
            color: #6b7280;
            line-height: 1.45;
            background: #fff;
            border: 1px solid #e5e9f0;
            border-radius: 8px;
            padding: 9px 11px;
        }

        .ra-note i {
            color: #9ca3af;
            margin-top: 2px;
        }

        .ra-aksi {
            margin-top: auto;
            padding-top: 18px;
            display: flex;
            justify-content: flex-end;
            gap: 8px;
        }

        .ra-btn-ghost {
            background: #fff;
            border: 1px solid #d9e0ea;
            color: #374151;
            border-radius: 8px;
            font-size: 13px;
            padding: 7px 16px;
        }

        .ra-btn-ghost:hover {
            background: #f3f4f6;
        }

        .ra-btn-primary {
            background: var(--gr-accent);
            border: 1px solid var(--gr-accent);
            color: #fff;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            padding: 7px 18px;
            transition: filter .15s ease;
        }

        .ra-btn-primary:hover {
            color: #fff;
            filter: brightness(.93);
        }

        .ra-btn-primary:disabled {
            opacity: .6;
        }

        /* select2 di Role Access: panah sejajar, multiple rapi */
        #modalRoleAccess .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow {
            position: absolute;
            top: 1px;
            right: 6px;
            height: 34px;
        }

        #modalRoleAccess .select2-container--bootstrap4 .select2-selection--multiple {
            min-height: 36px;
            border-color: #d9e2ed;
            border-radius: 8px;
            padding: 2px 6px;
        }

        #modalRoleAccess .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__choice {
            background: #eef3fa;
            border: 1px solid #d6e2f2;
            color: #2f4f7f;
            border-radius: 6px;
            font-size: 12px;
            padding: 1px 8px;
            margin-top: 4px;
        }

        #modalRoleAccess .select2-container--bootstrap4 .select2-selection--multiple .select2-search__field {
            font-size: 13.5px;
            margin-top: 5px;
        }

        #modalRoleAccess .select2-container--bootstrap4 .select2-selection--multiple .select2-search__field::placeholder {
            color: #a3aebe;
        }

        .ra-main-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 16px 22px;
            border-bottom: 1px solid #eef1f5;
        }

        .ra-search {
            position: relative;
            width: 260px;
            max-width: 100%;
        }

        .ra-search i {
            position: absolute;
            left: 11px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 12px;
        }

        .ra-search input {
            width: 100%;
            height: 34px;
            border: 1px solid #d9e2ed;
            border-radius: 8px;
            padding: 0 10px 0 31px;
            font-size: 13px;
            background: #fff;
            transition: border-color .15s ease, box-shadow .15s ease;
        }

        .ra-search input:focus {
            outline: 0;
            border-color: var(--gr-accent);
            box-shadow: 0 0 0 3px var(--gr-accent-soft);
        }

        .ra-list {
            flex: 1;
            overflow-y: auto;
        }

        .ra-user {
            display: flex;
            gap: 12px;
            align-items: flex-start;
            padding: 12px 22px;
            border-bottom: 1px solid #f0f2f6;
            box-shadow: inset 3px 0 0 transparent;
            transition: background .15s ease, box-shadow .15s ease;
            animation: gr-slide .3s ease both;
        }

        .ra-user:hover {
            background: #f8fafc;
        }

        .ra-user.aktif {
            background: #f3f7fc;
            box-shadow: inset 3px 0 0 var(--gr-accent);
        }

        .ra-user .av {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #eef3fa;
            color: var(--gr-accent);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 13px;
            flex-shrink: 0;
            text-transform: uppercase;
        }

        .ra-user .isi {
            flex: 1;
            min-width: 0;
        }

        .ra-user .nm {
            font-weight: 600;
            font-size: 13.5px;
            color: #111827;
            line-height: 32px;
        }

        .ra-user .un {
            font-weight: 400;
            font-size: 12px;
            color: #9ca3af;
            margin-left: 4px;
        }

        .ra-user .chips {
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
        }

        .ra-user .chips span {
            font-size: 11px;
            background: #fff;
            border: 1px solid #e2e7ef;
            color: #4b5563;
            border-radius: 5px;
            padding: 1px 7px;
        }

        .ra-user .chips span.mati {
            background: #fdf2f3;
            border-color: #f3d3d7;
            color: #c0505f;
            text-decoration: line-through;
        }

        .ra-user .btns {
            display: flex;
            gap: 2px;
            flex-shrink: 0;
            padding-top: 2px;
            opacity: .55;
            transition: opacity .15s ease;
        }

        .ra-user:hover .btns,
        .ra-user.aktif .btns {
            opacity: 1;
        }

        .ra-ic {
            width: 28px;
            height: 28px;
            border: 0;
            border-radius: 6px;
            background: transparent;
            color: #6b7280;
            font-size: 12px;
            transition: background .15s ease, color .15s ease;
        }

        .ra-ic:hover {
            background: #eef3fa;
            color: var(--gr-accent);
        }

        .ra-ic.hapus:hover {
            background: #fdecee;
            color: #c0505f;
        }

        .ra-empty {
            text-align: center;
            color: #9ca3af;
            padding: 60px 0;
            font-size: 13px;
        }

        .ra-empty i {
            font-size: 26px;
            display: block;
            margin-bottom: 8px;
            color: #cbd2dc;
        }

        /* --- Aksen warna --- */
        /* Header biru seperti modal Request Baru */
        .ra-head {
            position: relative;
            overflow: hidden;
            padding: 20px 24px;
            border-bottom: 0;
            color: #fff;
            background: linear-gradient(135deg, #3f5f8f 0%, #5b7fb5 55%, #86a5d3 100%);
        }

        .ra-head>* {
            position: relative;
            z-index: 1;
        }

        .ra-head .blob {
            position: absolute;
            z-index: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, .08);
            animation: gr-float 9s ease-in-out infinite;
        }

        .ra-head .b1 {
            width: 170px;
            height: 170px;
            top: -80px;
            right: 70px;
        }

        .ra-head .b2 {
            width: 100px;
            height: 100px;
            bottom: -55px;
            right: -20px;
            animation-delay: -4s;
        }

        .ra-head-ic {
            background: rgba(255, 255, 255, .18);
            border: 1px solid rgba(255, 255, 255, .28);
            color: #fff;
        }

        .ra-head-judul {
            color: #fff;
        }

        .ra-head-sub {
            color: rgba(255, 255, 255, .85);
        }

        .ra-close {
            color: rgba(255, 255, 255, .8);
        }

        .ra-close:hover {
            background: rgba(255, 255, 255, .18);
            color: #fff;
        }

        .ra-side {
            background: linear-gradient(180deg, #f3f7fc 0%, #f8fafc 100%);
        }

        .ra-cap i {
            color: var(--gr-accent);
        }

        .ra-count {
            background: var(--gr-accent);
            color: #fff;
        }

        .ra-note {
            background: #fff8e8;
            border-color: #f2e2b6;
            color: #8a6d2b;
        }

        .ra-note i {
            color: #d4a032;
        }

        .ra-btn-primary {
            background: linear-gradient(135deg, #4a6fa5, #5a86c0);
            box-shadow: 0 4px 12px rgba(74, 111, 165, .25);
        }

        .ra-btn-primary:hover {
            filter: brightness(1.05);
            box-shadow: 0 6px 16px rgba(74, 111, 165, .32);
        }

        .ra-user .av {
            background: var(--av-bg, #eef3fa);
            color: var(--av-fg, var(--gr-accent));
        }

        .ra-user.aktif {
            background: #eef4fc;
        }

        .ra-user .btns {
            opacity: 1;
        }

        .ra-ic {
            color: #3b6bb5;
            background: #eef3fa;
        }

        .ra-ic:hover {
            background: #3b6bb5;
            color: #fff;
        }

        .ra-ic.hapus {
            color: #c0505f;
            background: #fdeef0;
        }

        .ra-ic.hapus:hover {
            background: #c0505f;
            color: #fff;
        }

        /* Warna per site: NAG biru, NAK hijau */
        .ra-user .chips span.site-nag,
        .ra-legend .site-nag,
        #modalRoleAccess .select2-selection__choice.site-nag {
            background: #e8f0fb !important;
            border-color: #c9dbf3 !important;
            color: #2f5d9e !important;
        }

        .ra-user .chips span.site-nak,
        .ra-legend .site-nak,
        #modalRoleAccess .select2-selection__choice.site-nak {
            background: #e4f5ee !important;
            border-color: #bfe5d3 !important;
            color: #2b7d57 !important;
        }

        .ra-legend {
            gap: 6px;
            margin-left: auto;
            margin-right: 4px;
        }

        .ra-legend span {
            font-size: 10.5px;
            font-weight: 700;
            border: 1px solid;
            border-radius: 5px;
            padding: 1px 7px;
        }

        /* Filter site: tombol bisa diklik, yang nonaktif jadi abu-abu */
        .ra-legend button {
            font-size: 10.5px;
            font-weight: 700;
            border: 1px solid;
            border-radius: 5px;
            padding: 2px 9px;
            cursor: pointer;
            transition: transform .12s ease, background .15s ease, color .15s ease, border-color .15s ease;
        }

        .ra-legend button:hover {
            transform: translateY(-1px);
        }

        .ra-legend button:active {
            transform: scale(.95);
        }

        .ra-legend button:focus {
            outline: 0;
        }

        .ra-legend button:not(.aktif) {
            background: #fff !important;
            border-color: #dde2ea !important;
            color: #b3bbc7 !important;
            text-decoration: line-through;
        }

        .ra-user .chips span.luar-filter {
            opacity: .35;
        }

        /* Role Department */
        .rd-body {
            display: flex;
            flex-direction: column;
            background: #fff;
        }

        .rd-list {
            max-height: 58vh;
            overflow-y: auto;
        }

        .rd-row {
            display: grid;
            grid-template-columns: 56px 1fr 120px;
            align-items: center;
            gap: 12px;
            padding: 9px 22px;
            border-bottom: 1px solid #f1f4f8;
            animation: gr-fade-up .25s ease both;
        }

        .rd-row:hover {
            background: #f8fafc;
        }

        .rd-row.berubah {
            background: #fffaf0;
        }

        .rd-site {
            font-size: 10.5px;
            font-weight: 700;
            border: 1px solid;
            border-radius: 5px;
            padding: 1px 7px;
            text-align: center;
        }

        .rd-site.site-nag {
            background: #e8f0fb;
            border-color: #c9dbf3;
            color: #2f5d9e;
        }

        .rd-site.site-nak {
            background: #e4f5ee;
            border-color: #bfe5d3;
            color: #2b7d57;
        }

        .rd-nama {
            font-size: 13px;
            font-weight: 600;
            color: #1f2937;
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .rd-kode {
            width: 100%;
            height: 32px;
            border: 1px solid #d9e2ed;
            border-radius: 8px;
            padding: 0 10px;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            text-align: center;
            font-family: 'Consolas', monospace;
            transition: border-color .15s ease, box-shadow .15s ease;
        }

        .rd-kode:focus {
            outline: 0;
            border-color: var(--gr-accent);
            box-shadow: 0 0 0 3px var(--gr-accent-soft);
        }

        .rd-kode::placeholder {
            font-weight: 400;
            letter-spacing: 0;
            color: #c0c7d2;
        }

        .rd-kode.salah {
            border-color: #c0505f;
            box-shadow: 0 0 0 3px rgba(192, 80, 95, .15);
        }

        .rd-foot {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 12px 22px;
            border-top: 1px solid #e5e9f0;
            background: #f8fafc;
        }

        .rd-info {
            margin-right: auto;
            font-size: 12px;
            color: #6b7280;
        }

        .rd-info.salah {
            color: #c0505f;
            font-weight: 600;
        }

        @keyframes gr-fade-up {
            from {
                opacity: 0;
                transform: translateY(4px);
            }

            to {
                opacity: 1;
                transform: none;
            }
        }

        @media (max-width: 991px) {
            #modalRoleAccess .ra-body {
                grid-template-columns: 1fr;
                height: auto;
                overflow: visible;
            }

            .ra-side {
                border-right: 0;
                border-bottom: 1px solid #e5e9f0;
            }

            .ra-list {
                max-height: 50vh;
            }

            .ra-main-head {
                flex-wrap: wrap;
            }

            .ra-search {
                width: 100%;
            }
        }

        @media (max-width: 767px) {
            .gr-tiles {
                grid-template-columns: repeat(2, 1fr);
            }

            .gr-hero-ring {
                display: none;
            }
        }
    </style>
@endsection

@section('content')
    @php
        // Warna kalem: bg = latar lembut, fg = warna teks/ikon
        $statusList = [
            'Draft' => ['bg' => '#eef1f5', 'fg' => '#6b7687', 'icon' => 'fa-pencil-alt'],
            'Menunggu Approval' => ['bg' => '#fbf3df', 'fg' => '#b98a2e', 'icon' => 'fa-hourglass-half'],
            'Proses PO' => ['bg' => '#e9f0fa', 'fg' => '#4a6fa5', 'icon' => 'fa-file-invoice'],
            'Diterima Sebagian' => ['bg' => '#fcefe3', 'fg' => '#c47f45', 'icon' => 'fa-truck-loading'],
            'Selesai' => ['bg' => '#e6f3eb', 'fg' => '#4a8c68', 'icon' => 'fa-check-double'],
            'Ditolak' => ['bg' => '#f9e8ea', 'fg' => '#c0505f', 'icon' => 'fa-times-circle'],
            'Dibatalkan' => ['bg' => '#e7e9ee', 'fg' => '#525c6b', 'icon' => 'fa-ban'],
        ];
        $counts = collect($requests)->countBy('status');
        // Status di luar daftar (kalau ada) tetap tampil dengan warna netral
        $statusCfg = fn($s) => $statusList[$s] ?? ['bg' => '#eef1f5', 'fg' => '#6b7687', 'icon' => 'fa-question'];
        $chip = fn($s) => "--c-bg: {$statusCfg($s)['bg']}; --c-fg: {$statusCfg($s)['fg']}";
        $today = strtotime(date('Y-m-d'));
    @endphp

    <div class="gr-page">
        @if (count($departemen) === 0 || !$ada_barang)
            <div class="alert alert-warning py-2 small mb-3">
                <i class="fas fa-exclamation-triangle"></i>
                @if (count($departemen) === 0)
                    User Anda belum punya departemen di Role
                    Access{{ $boleh_role_access ? ' (atur lewat tombol Role Access)' : ', minta admin untuk mengaturnya' }}.
                @endif
                @if (count($departemen) > 0 && !collect($departemen)->contains(fn($d) => $d->kode))
                    Departemen Anda belum punya
                    alias{{ $boleh_role_access ? ' (atur lewat tombol Role Department)' : ', minta admin mengisinya di Role Department' }}.
                @endif
                @if (!$ada_barang)
                    Master item general masih kosong.
                @endif
                Request belum bisa dibuat sebelum datanya diisi.
            </div>
        @endif

        <div class="card card-sb">
            <div class="card-header bg-sb d-flex align-items-center">
                <h5 class="card-title fw-bold mb-0"><i class="fas fa-clipboard-list"></i> Input Request</h5>
                {{-- Pengaturan user -> departemen / sub dept, hanya admin tertentu --}}
                @if ($boleh_role_access)
                    <button type="button" class="btn btn-sm btn-light fw-bold ml-auto ms-auto mr-2 me-2 px-3"
                        onclick="bukaRoleDepartment()" title="Atur alias (department code) tiap departemen">
                        <i class="fas fa-building"></i> Role Department
                    </button>
                    <button type="button" class="btn btn-sm btn-light fw-bold px-3" onclick="bukaRoleAccess()"
                        title="Atur user masuk departemen apa">
                        <i class="fas fa-user-shield"></i> Role Access
                    </button>
                @endif
            </div>
            <div class="card-body">

                <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                    <button type="button" class="btn gr-btn-main" onclick="bukaFormRequest()">
                        <i class="fas fa-plus"></i> Buat Request
                    </button>
                    <small class="gr-muted-text mt-2 mt-md-0"><i class="fas fa-mouse-pointer"></i> Klik baris untuk melihat
                        item, klik <i class="fas fa-search-location"></i> untuk tracking per item</small>
                </div>

                {{-- Ringkasan status --}}
                <div class="row mb-3">
                    <div class="col-6 col-md-4 col-lg mb-2">
                        <div class="gr-stat active" style="--c-bg: #e9f0fa; --c-fg: #4a6fa5; animation-delay: 0s"
                            data-status="">
                            <i class="fas fa-layer-group gr-stat-bg"></i>
                            <div class="d-flex align-items-center" style="gap:10px">
                                <div class="gr-stat-icon"><i class="fas fa-layer-group"></i></div>
                                <div>
                                    <div class="gr-stat-label">Semua</div>
                                    <div class="gr-stat-value" data-count="{{ count($requests) }}">0</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @foreach ($statusList as $status => $cfg)
                        <div class="col-6 col-md-4 col-lg mb-2">
                            <div class="gr-stat"
                                style="{{ $chip($status) }}; animation-delay: {{ $loop->iteration * 0.07 }}s"
                                data-status="{{ $status }}">
                                <i class="fas {{ $cfg['icon'] }} gr-stat-bg"></i>
                                <div class="d-flex align-items-center" style="gap:10px">
                                    <div class="gr-stat-icon"><i class="fas {{ $cfg['icon'] }}"></i></div>
                                    <div>
                                        <div class="gr-stat-label">{{ $status }}</div>
                                        <div class="gr-stat-value" data-count="{{ $counts[$status] ?? 0 }}">0</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Filter ringkas: cari + rentang tanggal (status dipilih lewat kartu di atas) --}}
                <div class="gr-cari-bar mb-3">
                    <label class="gr-cari">
                        <i class="fas fa-search"></i>
                        <input type="text" id="filter_search" autocomplete="off"
                            placeholder="Cari no request, departemen, nama barang...">
                    </label>
                    <div class="gr-rentang" title="Rentang tanggal request">
                        <i class="far fa-calendar"></i>
                        <input type="date" id="filter_tgl_awal" value="{{ $tgl_awal }}">
                        <span>&ndash;</span>
                        <input type="date" id="filter_tgl_akhir" value="{{ $tgl_akhir }}">
                    </div>
                    {{-- Export Excel mengikuti filter tanggal & status yang sedang dipilih --}}
                    <button type="button" class="gr-ket-btn gr-excel-btn" onclick="exportExcel()">
                        <i class="fas fa-file-excel"></i> Export Excel
                    </button>
                    <button type="button" class="gr-ket-btn" onclick="$('#modalKeterangan').modal('show')">
                        <i class="fas fa-info-circle"></i> Keterangan status
                    </button>
                    <select id="filter_status" class="d-none" aria-hidden="true">
                        <option value="">Semua Status</option>
                        @foreach ($statusList as $status => $cfg)
                            <option value="{{ $status }}">{{ $status }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Keterangan status --}}
                @php
                    $ketRequest = [
                        'Draft' => 'Belum diajukan, masih bisa diubah',
                        'Menunggu Approval' => 'Sudah diajukan, menunggu persetujuan atasan',
                        'Proses PO' => 'Disetujui, sedang dibuatkan PO / barang belum datang',
                        'Diterima Sebagian' => 'Sebagian barang sudah diterima gudang',
                        'Selesai' => 'Semua barang sudah diterima gudang',
                        'Ditolak' => 'Ditolak atasan, bisa dibuat request ulang',
                        'Dibatalkan' => 'Dibatalkan pemohon, tetap tersimpan & bisa dibuat request ulang',
                    ];
                    $ketItem = [
                        'Belum PO' => [
                            '#eef1f5',
                            '#6b7687',
                            'fa-file-invoice',
                            'Sudah disetujui, menunggu dibuatkan PO oleh Purchasing',
                        ],
                        'Menunggu Barang' => [
                            '#e9f0fa',
                            '#4a6fa5',
                            'fa-truck',
                            'PO sudah dibuat, barang belum datang ke gudang',
                        ],
                        'Diterima Sebagian' => [
                            '#fcefe3',
                            '#c47f45',
                            'fa-truck-loading',
                            'Sebagian barang sudah diterima gudang, masih ada sisa',
                        ],
                        'Lengkap' => ['#e6f3eb', '#4a8c68', 'fa-check', 'Semua qty barang sudah diterima gudang'],
                    ];
                @endphp

                <div class="modal fade" id="modalKeterangan" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                        <div class="modal-content gr-ket-modal">
                            <div class="gr-ket-hero">
                                <span class="blob b1"></span><span class="blob b2"></span>
                                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"><span
                                        aria-hidden="true">&times;</span></button>
                                <div class="gr-hero-label"><i class="fas fa-info-circle"></i> Panduan</div>
                                <div class="gr-ket-judul">Keterangan Status</div>
                                <div class="gr-hero-kep">Arti setiap status pada request dan item</div>
                            </div>
                            <div class="modal-body">
                                <div class="gr-ket-panel">
                                    <div class="gr-ket-row">
                                        <div class="gr-ket-title">Status Request</div>
                                        <div class="gr-ket-list">
                                            @foreach ($ketRequest as $status => $ket)
                                                <div class="gr-ket-item"
                                                    style="{{ $chip($status) }}; animation-delay: {{ $loop->index * 0.05 }}s">
                                                    <div class="ic"><i
                                                            class="fas {{ $statusList[$status]['icon'] }}"></i></div>
                                                    <div>
                                                        <div class="t">{{ $status }}</div>
                                                        <div class="d">{{ $ket }}</div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="gr-ket-row">
                                        <div class="gr-ket-title">Status Item</div>
                                        <div class="gr-ket-list">
                                            @foreach ($ketItem as $status => $k)
                                                <div class="gr-ket-item"
                                                    style="--c-bg: {{ $k[0] }}; --c-fg: {{ $k[1] }}; animation-delay: {{ 0.3 + $loop->index * 0.05 }}s">
                                                    <div class="ic"><i class="fas {{ $k[2] }}"></i></div>
                                                    <div>
                                                        <div class="t">{{ $status }}</div>
                                                        <div class="d">{{ $k[3] }}</div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                <div class="gr-ket-tip">
                                    <i class="fas fa-lightbulb"></i>
                                    Satu request bisa berisi beberapa item dengan status berbeda. Klik baris di tabel untuk
                                    melihat posisi tiap item,
                                    atau klik <i class="fas fa-search-location"></i> untuk tracking lengkap per item.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="gr-table-wrap">
                    <div class="table-responsive">
                        <table class="table w-100" id="table-request">
                            <thead>
                                <tr>
                                    <th width="3%"></th>
                                    <th width="14%">Request</th>
                                    <th width="15%">Departemen</th>
                                    <th>Catatan</th>
                                    <th width="6%" class="text-center">Item</th>
                                    <th width="12%">Dibutuhkan</th>
                                    <th width="17%">Status</th>
                                    <th width="13%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            {{-- Baris dimuat per halaman dari server (requestData) --}}
                            <tbody></tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- Modal tracking per item --}}
    <div class="modal fade" id="modalTracking" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="gr-hero">
                    <span class="blob b1"></span><span class="blob b2"></span><span class="blob b3"></span>
                    <span class="dots"></span>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                    <div class="d-flex align-items-center position-relative" style="gap:20px">
                        <div class="flex-grow-1">
                            <div class="gr-hero-label"><i class="fas fa-search-location"></i> Tracking Request</div>
                            <div class="gr-hero-no" id="md_no_request"></div>
                            <div class="gr-hero-kep" id="md_keperluan"></div>
                            <div class="gr-hero-chips" id="md_chips"></div>
                        </div>
                        <div class="gr-hero-ring mr-4">
                            <svg width="96" height="96">
                                <circle class="bg" cx="48" cy="48" r="40"></circle>
                                <circle class="fg" id="md_hero_ring" cx="48" cy="48" r="40"></circle>
                            </svg>
                            <div class="label"><b id="md_persen">0%</b><small>diterima</small></div>
                        </div>
                    </div>
                </div>
                <div class="modal-body">
                    {{-- Catatan keseluruhan request (header) --}}
                    <div class="gr-md-catatan" id="md_catatan"></div>
                    <div class="gr-approval" id="md_approval"></div>

                    <div class="row">
                        <div class="col-lg-5 mb-3">
                            <div class="gr-section-title"><i class="fas fa-boxes"></i> Item <span class="cnt"
                                    id="md_item_count">0</span></div>
                            <div class="gr-item-search">
                                <i class="fas fa-search"></i>
                                <input type="text" id="md_item_search" placeholder="Cari nama barang / No PO / No BPB"
                                    autocomplete="off">
                                <span class="clear" id="md_item_clear" title="Hapus"><i class="fas fa-times-circle"
                                        style="position:static; transform:none"></i></span>
                            </div>
                            <div class="gr-legend" id="md_legend"></div>
                            <div class="gr-legend-ket" id="md_legend_ket"></div>
                            <div class="gr-item-list" id="md_item_list"></div>
                            <div class="gr-item-empty" id="md_item_empty"><i class="fas fa-search"></i>Tidak ada item
                                yang cocok</div>
                        </div>
                        <div class="col-lg-7 mb-3">
                            <div class="gr-section-title"><i class="fas fa-route"></i> Tracking Item</div>
                            <div class="gr-detail">
                                <div class="gr-detail-inner" id="md_detail"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Buat Request --}}
    <div class="modal fade" id="modalBuat" tabindex="-1" aria-hidden="true" data-bs-backdrop="static"
        data-bs-keyboard="false" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content gr-ket-modal">
                <div class="gr-ket-hero">
                    <span class="blob b1"></span><span class="blob b2"></span>
                    <button type="button" class="close" onclick="tutupFormRequest()" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                    <div class="gr-hero-label"><i class="fas fa-plus-circle"></i> <span id="bf_label">Request
                            Baru</span></div>
                    <div class="gr-ket-judul" id="bf_no">{{ $no_request }}</div>
                    <div class="gr-hero-kep" id="bf_sub">Isi kebutuhan barang, lalu ajukan untuk approval</div>
                </div>
                <div class="modal-body gr-bf" id="bf_body">
                    {{-- Info request: 1 baris --}}
                    <div class="gr-section-title"><i class="fas fa-file-alt"></i> Info Request</div>
                    <div class="gr-bf-card mb-3">
                        <div class="row gr-row-rapat">
                            <div class="col-md-2 mb-2 mb-md-0">
                                <label>Tgl Request</label>
                                <input type="date" id="bf_tgl_request" class="form-control form-control-sm"
                                    value="{{ date('Y-m-d') }}" readonly>
                            </div>
                            <div class="col-md-4 mb-2 mb-md-0">
                                <label>Departemen</label>
                                <select id="bf_departemen" class="form-control form-control-sm bf-select2">
                                    <option value="">-- Pilih Departemen --</option>
                                    @foreach ($departemen as $d)
                                        {{-- Tanpa alias (department_code) belum bisa dipakai: nomor request memakai alias --}}
                                        <option value="{{ $d->id }}" data-kode="{{ $d->kode }}"
                                            data-no="{{ $d->no_berikut }}" {{ $d->kode ? '' : 'disabled' }}>
                                            {{ $d->nama }}{{ $d->kode ? '' : ' (belum ada alias)' }}</option>
                                    @endforeach
                                </select>
                            </div>
                            {{-- Approver dipilih pemohon per request: 1 wajib saat diajukan, 2 opsional (approve bersamaan) --}}
                            @foreach (['bf_approver' => 'Approver 1', 'bf_approver2' => 'Approver 2'] as $idSel => $judulSel)
                                <div class="col-md-3 {{ $loop->first ? 'mb-2 mb-md-0' : '' }}">
                                    <label>{{ $judulSel }}
                                        @if ($loop->first)
                                            <b class="text-danger">*</b>
                                        @else
                                            <small class="text-muted">(opsional)</small>
                                        @endif
                                    </label>
                                    <select id="{{ $idSel }}" class="form-control form-control-sm bf-select2">
                                        <option value="">{{ $loop->first ? '-- Pilih Approver --' : '-- Tanpa Approver 2 --' }}</option>
                                        @foreach ($approver_list as $u)
                                            <option value="{{ $u->username }}">{{ $u->name ?: $u->username }}
                                                ({{ $u->username }})</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Catatan keseluruhan request (header) --}}
                    <div class="gr-bf-catatan mb-3">
                        <label for="bf_catatan"><i class="fas fa-sticky-note"></i> Catatan Request <small>(opsional,
                                untuk keseluruhan request)</small></label>
                        <textarea id="bf_catatan" class="form-control form-control-sm" rows="2" maxlength="1000"
                            placeholder="Mis. keperluan / alasan request, lokasi pemakaian, info untuk Purchasing"></textarea>
                    </div>

                    {{-- Daftar item: lebar penuh, bisa dicari & difilter --}}
                    <div class="gr-bf-toolbar">
                        <div class="gr-section-title mb-0"><i class="fas fa-boxes"></i> Daftar Item <span
                                class="cnt gr-bf-count" id="bf_count">0</span></div>
                        <div class="gr-bf-filter">
                            <div class="gr-bf-cari">
                                <i class="fas fa-search"></i>
                                <input type="text" id="bf_cari" placeholder="Cari barang / kode / keterangan"
                                    autocomplete="off">
                            </div>
                            <button type="button" class="btn gr-btn-main btn-sm text-nowrap"
                                onclick="bukaPanelItem()"><i class="fas fa-plus"></i> Tambah Item</button>
                        </div>
                    </div>
                    <div class="gr-bf-list" id="bf_items"></div>
                </div>

                {{-- Katalog barang layar penuh: menutupi isi form, kembali ke form lewat tombol kembali / Esc --}}
                <div class="gr-kat" id="bf_panel" aria-hidden="true">
                    <div class="gr-kat-head">
                        <button type="button" class="kembali" onclick="tutupPanelItem()"
                            title="Kembali ke request (Esc)"><i class="fas fa-arrow-left"></i></button>
                        <div class="jdw">
                            <div class="lbl"><i class="fas fa-store"></i> Pilih Barang</div>
                            <div class="jd" id="bf_kat_no"></div>
                        </div>
                        <div class="gr-kat-cari">
                            <i class="fas fa-search"></i>
                            <input type="text" id="bf_kat_cari" placeholder="Cari barang..."
                                title="Bisa cari nama, kode, warna, atau ukuran" autocomplete="off">
                            <span class="putar" id="bf_kat_putar"><i class="fas fa-circle-notch fa-spin"></i></span>
                            <button type="button" class="hapus" id="bf_kat_hapus" title="Kosongkan" hidden><i
                                    class="fas fa-times"></i></button>
                        </div>
                        <div class="gr-kat-view" id="bf_kat_view">
                            <button type="button" data-view="grid" title="Tampilan gambar"><i class="fas fa-th-large"></i></button>
                            <button type="button" data-view="list" title="Tampilan daftar"><i class="fas fa-list"></i></button>
                        </div>
                    </div>

                    <div class="gr-kat-main">
                        {{-- Sidebar sub group --}}
                        <aside class="gr-kat-side">
                            <div class="cap">Kategori</div>
                            <div class="gr-kat-sub" id="bf_kat_sub">
                                <button type="button" class="aktif" data-sub="">Semua Barang
                                    <span>{{ collect($sub_group)->sum('jumlah') }}</span></button>
                                @foreach ($sub_group as $s)
                                    <button type="button" data-sub="{{ $s->id }}"
                                        title="{{ $s->grup ? $s->grup . ' / ' : '' }}{{ $s->nama }}">{{ $s->nama }}
                                        <span>{{ $s->jumlah }}</span></button>
                                @endforeach
                            </div>
                        </aside>

                        {{-- Daftar barang: hasil cari dari server, dimuat bertahap saat di-scroll --}}
                        <div class="gr-kat-isi">
                            <div class="gr-bf-panel-body" id="bf_katalog"></div>
                        </div>
                    </div>

                    <div class="gr-kat-foot">
                        <div class="ringkas" id="bf_kat_ringkas"></div>
                        <button type="button" class="btn gr-btn-main" onclick="tutupPanelItem()"><i
                                class="fas fa-check"></i> Selesai <span id="bf_kat_selesai_n"></span></button>
                    </div>
                </div>
                <div class="modal-footer gr-bf-footer">
                    <button type="button" class="btn btn-light px-3" onclick="tutupFormRequest()">Batal</button>
                    <button type="button" class="btn gr-btn-soft px-3 fw-bold bf-simpan"
                        onclick="simpanRequest('draft')"><i class="fas fa-save"></i> Simpan Draft</button>
                    <button type="button" class="btn gr-btn-main px-3 bf-simpan" onclick="simpanRequest('ajukan')"><i
                            class="fas fa-paper-plane"></i> Ajukan Approval</button>
                </div>
            </div>
        </div>
    </div>
    @if ($boleh_role_access)
        {{-- Modal Role Access: user -> departemen / sub dept (HRIS) --}}
        <div class="modal fade" id="modalRoleAccess" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content ra-modal">
                    <div class="ra-head">
                        <span class="blob b1"></span><span class="blob b2"></span>
                        <div class="ra-head-ic"><i class="fas fa-user-shield"></i></div>
                        <div class="flex-grow-1">
                            <div class="ra-head-judul">Role Access</div>
                            <div class="ra-head-sub">Tentukan departemen yang bisa dipakai user saat membuat General
                                Request.</div>
                        </div>
                        <button type="button" class="ra-close" data-bs-dismiss="modal" data-dismiss="modal"
                            aria-label="Tutup"><i class="fas fa-times"></i></button>
                    </div>
                    <div class="modal-body ra-body gr-bf">
                        {{-- Kiri: form --}}
                        <div class="ra-side" id="ra_form">
                            <div class="ra-cap"><i class="fas fa-user-edit"></i> <span id="ra_judul_form">Atur Akses
                                    User</span></div>
                            <div class="ra-field">
                                <label for="ra_user">User</label>
                                <select id="ra_user" class="form-control form-control-sm ra-select2">
                                    <option value="">-- Pilih User --</option>
                                </select>
                            </div>
                            <div class="ra-field">
                                <label>Departemen</label>
                                {{-- Daftar centang per site (NAG / NAK), bisa cari & pilih semua --}}
                                <div class="ra-cek" id="ra_dept">
                                    <div class="ra-cek-head">
                                        <label class="ra-cek-all"><input type="checkbox" id="ra_all"> Pilih semua
                                            <span id="ra_all_n"></span></label>
                                        <div class="ra-cek-cari">
                                            <i class="fas fa-search"></i>
                                            <input type="text" id="ra_dept_cari" placeholder="Cari departemen"
                                                autocomplete="off">
                                        </div>
                                    </div>
                                    <div class="ra-cek-list" id="ra_dept_list"></div>
                                    <div class="ra-cek-foot" id="ra_dept_info">Belum ada departemen dipilih</div>
                                </div>
                            </div>
                            <div class="ra-note">
                                <i class="fas fa-info-circle"></i>
                                <span>Departemen yang dihapus dari pilihan akan dicabut aksesnya. Request lama tetap
                                    tercatat.</span>
                            </div>
                            <div class="ra-aksi">
                                <button type="button" class="btn ra-btn-ghost" onclick="resetRoleForm()">Reset</button>
                                <button type="button" class="btn ra-btn-primary" id="ra_simpan"
                                    onclick="simpanRoleAccess()"><i class="fas fa-check"></i> Simpan</button>
                            </div>
                        </div>

                        {{-- Kanan: daftar --}}
                        <div class="ra-main">
                            <div class="ra-main-head">
                                <div class="ra-cap mb-0"><i class="fas fa-users"></i> Daftar Akses <span class="ra-count"
                                        id="ra_count">0</span></div>
                                <div class="ra-legend d-flex" id="ra_site" title="Klik untuk tampilkan site ini saja, klik lagi untuk semua"><button
                                        type="button" class="site-nag aktif" data-site="NAG">NAG</button><button
                                        type="button" class="site-nak aktif" data-site="NAK">NAK</button></div>
                                <div class="ra-search">
                                    <i class="fas fa-search"></i>
                                    <input type="text" id="ra_cari" placeholder="Cari user / departemen"
                                        autocomplete="off">
                                </div>
                            </div>
                            <div id="ra_list" class="ra-list"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal Role Department: alias (department_code) per departemen HRIS --}}
        <div class="modal fade" id="modalRoleDept" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content ra-modal">
                    <div class="ra-head">
                        <span class="blob b1"></span><span class="blob b2"></span>
                        <div class="ra-head-ic"><i class="fas fa-building"></i></div>
                        <div class="flex-grow-1">
                            <div class="ra-head-judul">Role Department</div>
                            <div class="ra-head-sub">Beri alias (department code) wajib 4 karakter untuk tiap departemen
                                HRIS.</div>
                        </div>
                        <button type="button" class="ra-close" data-bs-dismiss="modal" data-dismiss="modal"
                            aria-label="Tutup"><i class="fas fa-times"></i></button>
                    </div>
                    <div class="modal-body p-0 rd-body">
                        <div class="ra-main-head">
                            <div class="ra-cap mb-0"><i class="fas fa-tags"></i> Departemen <span class="ra-count"
                                    id="rd_count">0</span></div>
                            <div class="ra-legend d-flex" id="rd_site" title="Klik untuk tampilkan site ini saja, klik lagi untuk semua"><button
                                    type="button" class="site-nag aktif" data-site="NAG">NAG</button><button
                                    type="button" class="site-nak aktif" data-site="NAK">NAK</button></div>
                            <div class="ra-search">
                                <i class="fas fa-search"></i>
                                <input type="text" id="rd_cari" placeholder="Cari departemen / alias"
                                    autocomplete="off">
                            </div>
                        </div>
                        <div id="rd_list" class="rd-list"></div>
                    </div>
                    <div class="rd-foot">
                        <span class="rd-info" id="rd_info"><i class="fas fa-info-circle"></i> Wajib 4 karakter: huruf, angka, dan titik.
                            Contoh: <b>I.T.</b>, <b>SEWI</b></span>
                        <button type="button" class="btn ra-btn-ghost" data-bs-dismiss="modal"
                            data-dismiss="modal">Tutup</button>
                        <button type="button" class="btn ra-btn-primary" id="rd_simpan"
                            onclick="simpanRoleDepartment()" disabled><i class="fas fa-check"></i> Simpan</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@section('custom-script')
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>

    <script>
        // Request di halaman tabel yang sedang tampil (diganti setiap pindah halaman / cari / filter)
        let requests = [];
        const statusCfg = @json($statusList);

        // Status per item (tiap item bisa berbeda) + keterangan untuk legend
        const itemStatusCfg = {
            'Draft': {
                bg: '#eef1f5',
                fg: '#6b7687',
                icon: 'fa-pencil-alt',
                ket: 'Request masih draft, belum diajukan'
            },
            'Menunggu Approval': {
                bg: '#fbf3df',
                fg: '#b98a2e',
                icon: 'fa-hourglass-half',
                ket: 'Request menunggu persetujuan atasan'
            },
            'Ditolak': {
                bg: '#f9e8ea',
                fg: '#c0505f',
                icon: 'fa-times',
                ket: 'Request ditolak atasan'
            },
            'Dibatalkan': {
                bg: '#e7e9ee',
                fg: '#525c6b',
                icon: 'fa-ban',
                ket: 'Request dibatalkan pemohon'
            },
            'Belum PO': {
                bg: '#eef1f5',
                fg: '#6b7687',
                icon: 'fa-file-invoice',
                ket: 'Sudah disetujui, menunggu dibuatkan PO oleh Purchasing'
            },
            'Menunggu Barang': {
                bg: '#e9f0fa',
                fg: '#4a6fa5',
                icon: 'fa-truck',
                ket: 'PO sudah dibuat, barang belum datang ke gudang'
            },
            'Diterima Sebagian': {
                bg: '#fcefe3',
                fg: '#c47f45',
                icon: 'fa-truck-loading',
                ket: 'Sebagian barang sudah diterima gudang, masih ada sisa'
            },
            'Lengkap': {
                bg: '#e6f3eb',
                fg: '#4a8c68',
                icon: 'fa-check',
                ket: 'Semua qty sudah diterima gudang'
            },
        };

        // Filter item di modal
        let itemFilter = {
            q: '',
            status: ''
        };
        let renderToken = 0;

        let tableRequest;
        let curReq = null;

        $(document).ready(function() {
            $('.select2bs4').select2({
                theme: 'bootstrap4',
                width: '100%'
            });

            // Paging, cari & filter status di server: hanya 1 halaman yang dimuat
            tableRequest = $('#table-request').DataTable({
                dom: 'rtip',
                ordering: false,
                pageLength: 10,
                serverSide: true,
                processing: true,
                searchDelay: 400,
                ajax: {
                    url: "{{ route('general-request-data') }}",
                    data: d => {
                        d.tgl_awal = $('#filter_tgl_awal').val();
                        d.tgl_akhir = $('#filter_tgl_akhir').val();
                        d.status = $('#filter_status').val() || '';
                    },
                    dataSrc: res => {
                        // Respons lama (filter sudah diganti lagi) tidak boleh menimpa data baris terbaru
                        if (tableRequest && res.draw < tableRequest.settings()[0].iDraw) return [];
                        gagalMuatTabel = 0;
                        requests = res.requests || [];
                        perbaruiJumlahStatus(res.jumlah || {});
                        return res.data;
                    },
                    error: (xhr, textStatus) => {
                        $('.gr-table-wrap').removeClass('gr-memuat');
                        selesaiMuatTabel();
                        // Request lama dibatalkan karena filter diganti cepat -> bukan error, abaikan
                        if (textStatus === 'abort' || xhr.status === 0) return;
                        // Gagal sesaat di server lokal (401/419/5xx acak saat request bersamaan): ulang diam-diam maks 2x
                        if ((xhr.status >= 500 || xhr.status === 401 || xhr.status === 419) && gagalMuatTabel < 2) {
                            gagalMuatTabel++;
                            setTimeout(() => muatTabel(false), 400 * gagalMuatTabel);
                            return;
                        }
                        gagalMuatTabel = 0;
                        if (xhr.status === 401 || xhr.status === 419) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Sesi berakhir',
                                text: 'Silakan muat ulang halaman lalu login kembali.',
                                confirmButtonColor: '#4a6fa5'
                            });
                            return;
                        }
                        pesanGagal(xhr);
                    },
                },
                columnDefs: [
                    { targets: [0, 4], className: 'text-center' },
                    { targets: 7, className: 'text-center text-nowrap' },
                ],
                // Atribut baris: warna status, index data, tanda dibatalkan
                createdRow: (row, data, i) => {
                    const req = requests[i];
                    if (!req) return;
                    const cfg = statusCfg[req.status] || { bg: '#eef1f5', fg: '#6b7687' };
                    $(row).addClass('gr-row' + (req.status === 'Dibatalkan' ? ' gr-row-batal' : ''))
                        .attr('data-index', i).attr('style', `--c-bg: ${cfg.bg}; --c-fg: ${cfg.fg}`);
                },
                language: {
                    processing: '<i class="fas fa-circle-notch fa-spin"></i> Memuat...',
                    info: 'Menampilkan _START_ - _END_ dari _TOTAL_ request',
                    infoEmpty: 'Tidak ada request',
                    infoFiltered: '',
                    zeroRecords: '<div class="py-4 gr-muted-text"><i class="fas fa-inbox fa-2x d-block mb-2"></i>Tidak ada request yang cocok</div>',
                    paginate: {
                        previous: '&lsaquo;',
                        next: '&rsaquo;'
                    }
                },
                drawCallback: animateProgress,
            });


            // Rentang tanggal: cukup muat ulang isi tabel (tanpa reload halaman), URL ikut diperbarui
            let tundaTgl = null;
            $('#filter_tgl_awal, #filter_tgl_akhir').on('change', function() {
                clearTimeout(tundaTgl);
                tundaTgl = setTimeout(() => {
                    const awal = $('#filter_tgl_awal').val();
                    const akhir = $('#filter_tgl_akhir').val();
                    if (!awal || !akhir) return;
                    history.replaceState(null, '', `{{ route('general-request') }}?tgl_awal=${awal}&tgl_akhir=${akhir}`);
                    muatTabel();
                }, 350);
            });

            // Antrean muat tabel: hanya 1 request ke server dalam satu waktu.
            // Klik filter beruntun saat masih memuat -> cukup 1 muat ulang lagi dengan filter terakhir.
            // (Server lokal Apache + PHP ZTS bisa error 500 kalau menerima banyak request bersamaan)
            $('#table-request').on('preXhr.dt', () => { sedangMuatTabel = true; });
            $('#table-request').on('xhr.dt', selesaiMuatTabel);

            // Saat memuat: baris lama tetap tampil (sedikit redup), tanpa kotak "Memuat..."
            $('#table-request').on('processing.dt', (e, s, sedang) => {
                $('.gr-table-wrap').toggleClass('gr-memuat', sedang);
            });

            $('#filter_status').on('change', function() {
                const val = $(this).val();
                muatTabel();
                $('.gr-stat').removeClass('active');
                $(`.gr-stat[data-status="${val}"]`).addClass('active');
            });

            $('.gr-stat').on('click', function() {
                $('#filter_status').val($(this).data('status')).trigger('change');
            });

            // Cari di server (ditunda sebentar selama mengetik)
            let tundaCariReq = null;
            $('#filter_search').on('input', function() {
                clearTimeout(tundaCariReq);
                const v = this.value;
                tundaCariReq = setTimeout(() => {
                    tableRequest.search(v);
                    muatTabel();
                }, 400);
            });

            // Riwayat langkah status: kotak melayang di body (tidak terpotong tabel) saat kursor di atas titik
            const tutupRiwayat = () => $('#grRiwayat').remove();
            $('#table-request tbody')
                .on('mouseenter', '.gr-langkah', function() {
                    tutupRiwayat();
                    const isi = $(this).find('template.gr-riwayat-isi')[0];
                    if (!isi) return;
                    const warna = $(this).closest('tr').attr('style') || '';
                    const kotak = $('<div id="grRiwayat"></div>').attr('style', warna)
                        .append(isi.content.cloneNode(true)).appendTo('body');
                    const r = this.getBoundingClientRect();
                    const tinggi = kotak.outerHeight(), lebar = kotak.outerWidth();
                    const atas = r.bottom + 8 + tinggi > window.innerHeight ? r.top - tinggi - 8 : r.bottom + 8;
                    kotak.css({ top: atas, left: Math.min(r.left - 8, window.innerWidth - lebar - 8) });
                })
                .on('mouseleave', '.gr-langkah', tutupRiwayat);
            $(window).on('scroll', tutupRiwayat);
            $('#table-request').on('draw.dt', tutupRiwayat);

            // Klik baris -> buka/tutup daftar item
            $('#table-request tbody').on('click', 'tr.gr-row', function() {
                const row = tableRequest.row(this);
                if (row.child.isShown()) {
                    row.child.hide();
                    $(this).removeClass('shown');
                } else {
                    row.child(childHtml(requests[$(this).data('index')]), 'p-0').show();
                    $(this).addClass('shown');
                    animateProgress();
                }
            });
        });

        /* ================= Helper ================= */

        function chip(text, bg, fg, extra = '') {
            return `<span class="gr-chip ${extra}" style="--c-bg:${bg}; --c-fg:${fg}">${text}</span>`;
        }

        function countUp(el, target, duration = 900, suffix = '') {
            $({
                n: 0
            }).animate({
                n: target
            }, {
                duration,
                step: now => $(el).text(Math.round(now) + suffix),
                complete: () => $(el).text(target + suffix),
            });
        }

        // Kartu ringkasan status: jumlah dari server (rentang tanggal + kata kunci)
        let jumlahAwal = true;
        function perbaruiJumlahStatus(jumlah) {
            $('.gr-stat').each(function() {
                const st = $(this).data('status');
                const n = st ? (jumlah[st] || 0) : (jumlah.semua || 0);
                const el = $(this).find('.gr-stat-value');
                if (jumlahAwal) countUp(el[0], n);
                else el.text(n);
            });
            jumlahAwal = false;
        }

        function animateProgress() {
            setTimeout(() => {
                $('#table-request .progress-bar[data-width]').each(function() {
                    $(this).css('width', $(this).data('width') + '%');
                });
            }, 80);
        }

        function itemStatus(it, reqStatus) {
            if (['Draft', 'Menunggu Approval', 'Ditolak', 'Dibatalkan'].includes(reqStatus)) return reqStatus;
            if (it.qty_po <= 0) return 'Belum PO';
            if (it.qty_terima <= 0) return 'Menunggu Barang';
            if (it.qty_terima < it.qty) return 'Diterima Sebagian';
            return 'Lengkap';
        }

        function ringSvg(size, r, cls) {
            const c = size / 2;
            return `<svg width="${size}" height="${size}">
            <circle class="bg" cx="${c}" cy="${c}" r="${r}"></circle>
            <circle class="fg ${cls}" cx="${c}" cy="${c}" r="${r}"></circle>
        </svg>`;
        }

        function setRing(el, r, persen, delay = 200) {
            const k = 2 * Math.PI * r;
            $(el).css({
                'stroke-dasharray': k,
                'stroke-dashoffset': k
            });
            setTimeout(() => $(el).css('stroke-dashoffset', k * (1 - persen / 100)), delay);
        }

        /* ================= Baris yang dibuka ================= */

        // Kalimat singkat posisi item saat ini
        function ketItem(it, st) {
            const sisa = it.qty - it.qty_terima;
            const akhir = it.terima.length ? it.terima[it.terima.length - 1].tgl.split(' ')[0] : '';
            return {
                'Draft': 'Request belum diajukan',
                'Menunggu Approval': 'Menunggu persetujuan atasan',
                'Ditolak': 'Request ditolak atasan',
                'Dibatalkan': 'Request dibatalkan pemohon',
                'Belum PO': 'Sudah disetujui, menunggu dibuatkan PO oleh Purchasing',
                'Menunggu Barang': `PO ${escapeHtml(it.no_po)} sudah dibuat, barang belum datang`,
                'Diterima Sebagian': `${it.qty_terima} dari ${it.qty} ${it.satuan} sudah diterima gudang, sisa ${sisa} lagi`,
                'Lengkap': `Semua ${it.qty} ${it.satuan} sudah diterima gudang (${akhir})`,
            } [st];
        }

        function childHtml(req) {
            let html = '<div class="gr-child">';
            // Barang sama dari beberapa sub dept digabung jadi 1 baris (sama seperti Tracking)
            gabungItemTracking(req.items).forEach(it => {
                const st = itemStatus(it, req.status);
                const c = itemStatusCfg[st];
                const sisa = it.qty - it.qty_terima;
                const persen = it.qty > 0 ? Math.round(it.qty_terima / it.qty * 100) : 0;
                html += `<div class="gr-child-item" style="--c-bg:${c.bg}; --c-fg:${c.fg}">
                <div class="ic"><i class="fas ${c.icon}"></i></div>
                <div class="nama">
                    ${escapeHtml(it.nama)}
                    <div class="ket"><i class="fas fa-angle-right"></i> ${ketItem(it, st)}${it.rincian_sub.some(r => r.nama) ? ` &middot; <i class="fas fa-sitemap"></i> ${it.rincian_sub.map(r => escapeHtml(r.nama) + (it.rincian_sub.length > 1 ? ` <b>${formatAngka(r.qty)}</b>` : '')).join(', ')}` : ''}${it.tgl_butuh ? ` &middot; <i class="far fa-calendar-check"></i> ${formatTgl(it.tgl_butuh)}` : ''}</div>
                </div>
                <div>${chip(st, c.bg, c.fg)}</div>
                <div class="angka">${it.qty_terima} / ${it.qty} ${it.satuan}</div>
                <div class="bar"><div class="progress gr-progress"><div class="progress-bar ${persen >= 100 ? 'gr-bar-sage' : 'gr-bar-peach'}" data-width="${persen}"></div></div></div>
                <div class="sisa ${sisa > 0 ? 'gr-sisa-ada' : 'gr-sisa-nol'}">Sisa ${sisa}</div>
            </div>`;
            });
            return html + '</div>';
        }

        /* ================= Role Access (user -> departemen / sub dept) ================= */
        let raData = null;
        let raBerubah = false; // ada simpanan -> halaman dimuat ulang saat modal ditutup
        let raNamaKembar = new Set();

        $(function() {
            if (!$('#modalRoleAccess').length) return;

            $('#ra_user').select2({
                theme: 'bootstrap4',
                width: '100%',
                dropdownParent: $('#modalRoleAccess')
            });
            // Daftar centang departemen
            $('#ra_dept_list').on('change', '.it input', raDeptSegarkan);
            $('#ra_dept_list').on('change', '.gh input', function() {
                $(this).closest('.grp').find('.it:not(.sembunyi) input').prop('checked', this.checked);
                raDeptSegarkan();
            });
            $('#ra_all').on('change', function() {
                $('#ra_dept_list .it:not(.sembunyi) input').prop('checked', this.checked);
                raDeptSegarkan();
            });
            $('#ra_dept_cari').on('input', function() {
                const q = this.value.trim().toLowerCase();
                $('#ra_dept_list .it').each(function() {
                    $(this).toggleClass('sembunyi', !!q && !String($(this).data('cari')).includes(q));
                });
                $('#ra_dept_list .grp').each(function() {
                    $(this).toggleClass('sembunyi', !$(this).find('.it:not(.sembunyi)').length);
                });
                raDeptSegarkan();
            });

            // Pilih user -> isi departemen yang sudah dimiliki
            $('#ra_user').on('change', function() {
                const u = $(this).val();
                const ada = (raData?.akses || []).find(a => a.username === u);
                raDeptSet(ada ? ada.departemen.map(d => d.department_id) : []);
                $('#ra_judul_form').text(ada ? 'Ubah Akses User' : 'Atur Akses User');
                $('#ra_list .ra-user').removeClass('aktif');
                if (u) $(`#ra_list .ra-user[data-u="${CSS.escape(u)}"]`).addClass('aktif');
            });

            $('#ra_cari').on('input', renderRoleList);
            $('#ra_site').on('click', 'button', function() {
                toggleSite(this, renderRoleList);
            });

            $('#modalRoleAccess').on('hidden.bs.modal', () => {
                if (raBerubah) window.location.reload();
            });
        });

        // Departemen tercentang (semua, termasuk yang tersembunyi oleh pencarian)
        function raDeptVal() {
            return $('#ra_dept_list .it input:checked').map((_, el) => el.value).get();
        }

        function raDeptSet(ids) {
            const pilih = new Set(ids.map(String));
            $('#ra_dept_list .it input').each(function() {
                this.checked = pilih.has(this.value);
            });
            raDeptSegarkan();
        }

        // Status centang "Pilih semua" & per site (sebagian = garis), jumlah terpilih
        function raDeptSegarkan() {
            const atur = (cek, kotak) => {
                const n = kotak.filter(':checked').length;
                cek.prop('checked', kotak.length > 0 && n === kotak.length).prop('indeterminate', n > 0 && n < kotak.length);
            };
            $('#ra_dept_list .grp').each(function() {
                const semua = $(this).find('.it input');
                const n = semua.filter(':checked').length;
                atur($(this).find('.gh input'), $(this).find('.it:not(.sembunyi) input'));
                $(this).find('.gh .n').text(`${n} / ${semua.length}`);
                $(this).find('.it').each(function() {
                    $(this).toggleClass('dipilih', $(this).find('input').prop('checked'));
                });
            });
            const tampil = $('#ra_dept_list .it:not(.sembunyi) input');
            atur($('#ra_all'), tampil);
            $('#ra_all_n').text(`(${tampil.length})`);
            const total = raDeptVal().length;
            $('#ra_dept_info').toggleClass('ada', total > 0)
                .html(total ? `<i class="fas fa-check-circle"></i> <b>${total}</b> departemen dipilih` : 'Belum ada departemen dipilih');
        }

        function bukaRoleAccess() {
            $('#modalRoleAccess').modal('show');
            muatRoleAccess();
        }

        function muatRoleAccess(pilihUser = null) {
            $('#ra_list').html('<div class="ra-empty"><i class="fas fa-circle-notch fa-spin"></i>Memuat...</div>');
            $.getJSON("{{ route('general-request-role-access') }}")
                .done(res => {
                    raData = res;

                    // User
                    const optUser = res.users.map(u =>
                        `<option value="${escapeHtml(u.username)}">${escapeHtml(u.username)}${u.name ? ' - ' + escapeHtml(u.name) : ''}</option>`
                        ).join('');
                    $('#ra_user').html('<option value="">-- Pilih User --</option>' + optUser);

                    // Departemen (nama saja), dikelompokkan per site karena ada nama yang sama di NAG & NAK
                    const grup = {};
                    const hitung = {};
                    res.departemen.forEach(d => {
                        (grup[d.site] ??= []).push(d);
                        const n = d.department_name.toUpperCase();
                        hitung[n] = (hitung[n] || 0) + 1;
                    });
                    raNamaKembar = new Set(Object.keys(hitung).filter(n => hitung[n] > 1));
                    $('#ra_dept_cari').val('');
                    $('#ra_dept_list').html(Object.entries(grup).map(([site, isi]) => `
                        <div class="grp" data-site="${escapeHtml(isi[0].site_id)}">
                            <label class="gh site-${escapeHtml(String(isi[0].site_id).toLowerCase())}">
                                <input type="checkbox"> <b>${escapeHtml(isi[0].site_id)}</b> <span class="pt">${escapeHtml(site)}</span>
                                <span class="n"></span>
                            </label>
                            <div class="isi">${isi.map(d => `
                                <label class="it" data-cari="${escapeHtml(d.department_name.toLowerCase())}" title="${escapeHtml(d.department_name)} (${escapeHtml(d.site_id)})">
                                    <input type="checkbox" value="${escapeHtml(d.department_id)}"> <span>${escapeHtml(d.department_name)}</span>
                                </label>`).join('')}
                            </div>
                        </div>`).join(''));

                    $('#ra_user').val(pilihUser || '').trigger('change');
                    renderRoleList();
                })
                .fail(xhr => {
                    $('#ra_list').html(
                        '<div class="ra-empty"><i class="fas fa-exclamation-triangle"></i>Gagal memuat data</div>');
                    pesanGagal(xhr);
                });
        }

        function renderRoleList() {
            if (!raData) return;
            const q = $('#ra_cari').val().trim().toLowerCase();
            const site = siteAktif('#ra_site');
            // User tampil kalau punya minimal 1 departemen di site yang aktif
            const daftar = raData.akses.filter(a =>
                (site.length === 2 || a.departemen.some(d => site.includes(d.site_id))) &&
                (!q || [a.username, a.nama ?? '', ...a.departemen.map(d => d.label)].join(' ').toLowerCase().includes(
                    q)));

            $('#ra_count').text(daftar.length !== raData.akses.length ? `${daftar.length} / ${raData.akses.length}` : raData
                .akses.length);

            if (!daftar.length) {
                $('#ra_list').html(
                    `<div class="ra-empty"><i class="fas fa-user-slash"></i>${raData.akses.length ? 'Tidak ada yang cocok' : 'Belum ada user yang diatur'}</div>`
                    );
                return;
            }

            const pilih = $('#ra_user').val();
            $('#ra_list').html(daftar.map((a, i) => `
            <div class="ra-user ${a.username === pilih ? 'aktif' : ''}" data-u="${escapeHtml(a.username)}" style="animation-delay:${Math.min(i, 10) * 0.03}s; ${warnaAvatar(a.username)}">
                <div class="av">${escapeHtml((a.nama || a.username).charAt(0))}</div>
                <div class="isi">
                    <div class="nm">${escapeHtml(a.nama || a.username)} <span class="un">@${escapeHtml(a.username)}</span></div>
                    <div class="chips">${a.departemen.map(d => `<span class="${d.aktif_hris ? 'site-' + escapeHtml((d.site_id || '').toLowerCase()) : 'mati'}${site.length < 2 && !site.includes(d.site_id) ? ' luar-filter' : ''}"${d.aktif_hris ? `title="${escapeHtml(d.site_id || '')}"` : 'title="Sudah tidak aktif di HRIS"'}>${escapeHtml(d.label)}</span>`).join('')}</div>
                </div>
                <div class="btns">
                    <button type="button" class="ra-ic" title="Ubah" onclick="ubahRoleUser(this)"><i class="fas fa-pen"></i></button>
                    <button type="button" class="ra-ic hapus" title="Cabut semua akses" onclick="cabutRoleUser(this)"><i class="fas fa-trash-alt"></i></button>
                </div>
            </div>`).join(''));
        }

        // Warna avatar tetap per username (bukan peringkat, hanya pembeda visual)
        function warnaAvatar(username) {
            const palet = [
                ['#e3edfb', '#3b6bb5'],
                ['#e2f4ec', '#2f8a5f'],
                ['#fdeee2', '#c2692a'],
                ['#f1e8fb', '#7a4fb5'],
                ['#e0f3f6', '#25808f'],
                ['#fbe6ec', '#b8466a']
            ];
            let h = 0;
            for (const c of String(username)) h = (h * 31 + c.charCodeAt(0)) >>> 0;
            const [bg, fg] = palet[h % palet.length];
            return `--av-bg:${bg}; --av-fg:${fg};`;
        }

        // Filter site NAG / NAK: boleh dua-duanya atau salah satu, minimal 1 tetap aktif
        function siteAktif(wadah) {
            return $(wadah).find('button.aktif').map((_, b) => $(b).data('site')).get();
        }

        function toggleSite(btn, render) {
            const $b = $(btn);
            if ($b.hasClass('aktif') && $b.siblings('.aktif').length === 0) {
                // Site yang sedang dipilih diklik lagi -> kembali tampil semua site
                $b.siblings().addBack().addClass('aktif');
            } else {
                // Yang diklik = yang dipilih (hanya site itu yang tampil)
                $b.addClass('aktif').siblings().removeClass('aktif');
            }
            render();
        }

        function ubahRoleUser(btn) {
            $('#ra_user').val($(btn).closest('.ra-user').data('u')).trigger('change');
        }

        function resetRoleForm() {
            $('#ra_user').val('').trigger('change');
        }

        function kirimRoleAccess(username, deptIds) {
            $('#ra_simpan').prop('disabled', true);
            return kirim("{{ route('general-request-role-access-simpan') }}", {
                    username,
                    department_ids: deptIds
                })
                .done(res => {
                    raBerubah = true;
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: res.message,
                        timer: 1600,
                        showConfirmButton: false
                    });
                    muatRoleAccess(deptIds.length ? username : null);
                })
                .fail(pesanGagal)
                .always(() => $('#ra_simpan').prop('disabled', false));
        }

        function simpanRoleAccess() {
            const username = $('#ra_user').val();
            const deptIds = raDeptVal();
            if (!raData) return;
            if (!username) {
                $('#ra_user').select2('open');
                return;
            }

            const ada = raData.akses.find(a => a.username === username);
            if (!deptIds.length) {
                if (!ada) {
                    shake($('#ra_form'));
                    $('#ra_dept_cari').trigger('focus');
                    return;
                }
                konfirmasiCabut(username);
                return;
            }
            kirimRoleAccess(username, deptIds);
        }

        function cabutRoleUser(btn) {
            konfirmasiCabut($(btn).closest('.ra-user').data('u'));
        }

        function konfirmasiCabut(username) {
            Swal.fire({
                icon: 'warning',
                title: 'Cabut semua akses?',
                html: `User <b>${escapeHtml(username)}</b> tidak bisa membuat request lagi sampai diberi departemen. Request lama tetap tercatat.`,
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-user-times"></i> Cabut',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#c0505f',
            }).then(r => {
                if (r.isConfirmed) kirimRoleAccess(username, []);
            });
        }

        /* ================= Role Department (alias / department_code) ================= */
        let rdData = null;
        let rdPanjang = 4;
        let rdUbah = {}; // department_id -> alias baru (yang beda dari tersimpan)

        $(function() {
            if (!$('#modalRoleDept').length) return;

            $('#rd_cari').on('input', renderRoleDept);
            $('#rd_site').on('click', 'button', function() {
                toggleSite(this, renderRoleDept);
            });

            $('#rd_list').on('input', '.rd-kode', function() {
                const v = this.value.toUpperCase().replace(/[^A-Z0-9.]/g, '').slice(0, rdPanjang);
                if (this.value !== v) this.value = v;
                const id = String($(this).data('id'));
                const d = rdData.departemen.find(x => String(x.department_id) === id);
                if ((d.department_code || '') === v) delete rdUbah[id];
                else rdUbah[id] = v;
                $(this).closest('.rd-row').toggleClass('berubah', id in rdUbah);
                cekRoleDept();
            });

            $('#modalRoleDept').on('hide.bs.modal', function(e) {
                if (!Object.keys(rdUbah).length || $(this).data('paksa')) return;
                e.preventDefault();
                Swal.fire({
                    icon: 'question',
                    title: 'Buang perubahan?',
                    text: 'Ada alias yang belum disimpan.',
                    showCancelButton: true,
                    confirmButtonText: 'Buang',
                    cancelButtonText: 'Kembali',
                    confirmButtonColor: '#c0505f',
                }).then(r => {
                    if (!r.isConfirmed) return;
                    rdUbah = {};
                    $('#modalRoleDept').data('paksa', true).modal('hide');
                });
            });
            $('#modalRoleDept').on('hidden.bs.modal', function() {
                $(this).data('paksa', false);
            });
        });

        function bukaRoleDepartment() {
            rdUbah = {};
            $('#rd_cari').val('');
            $('#modalRoleDept').modal('show');
            muatRoleDept();
        }

        function muatRoleDept() {
            $('#rd_list').html('<div class="ra-empty"><i class="fas fa-circle-notch fa-spin"></i>Memuat...</div>');
            $.getJSON("{{ route('general-request-role-department') }}")
                .done(res => {
                    rdData = res;
                    rdPanjang = res.panjang_kode || 4;
                    rdUbah = {};
                    renderRoleDept();
                    cekRoleDept();
                })
                .fail(xhr => {
                    $('#rd_list').html(
                        '<div class="ra-empty"><i class="fas fa-exclamation-triangle"></i>Gagal memuat data</div>');
                    pesanGagal(xhr);
                });
        }

        function renderRoleDept() {
            if (!rdData) return;
            const q = $('#rd_cari').val().trim().toLowerCase();
            const nilai = d => (String(d.department_id) in rdUbah ? rdUbah[d.department_id] : d.department_code) || '';
            const site = siteAktif('#rd_site');
            const daftar = rdData.departemen.filter(d =>
                (site.length === 2 || site.includes(d.site_id)) &&
                (!q || [d.label, d.site_id, nilai(d)].join(' ').toLowerCase().includes(q)));

            $('#rd_count').text(daftar.length !== rdData.departemen.length ?
                `${daftar.length} / ${rdData.departemen.length}` : rdData.departemen.length);
            if (!daftar.length) {
                $('#rd_list').html('<div class="ra-empty"><i class="fas fa-search"></i>Tidak ada yang cocok</div>');
                return;
            }

            $('#rd_list').html(daftar.map((d, i) => `
            <div class="rd-row ${String(d.department_id) in rdUbah ? 'berubah' : ''}" style="animation-delay:${Math.min(i, 12) * 0.02}s">
                <span class="rd-site site-${escapeHtml((d.site_id || '').toLowerCase())}">${escapeHtml(d.site_id || '-')}</span>
                <span class="rd-nama" title="${escapeHtml(d.label)}">${escapeHtml(d.label)}</span>
                <input type="text" class="rd-kode" data-id="${escapeHtml(d.department_id)}" maxlength="${rdPanjang}" placeholder="Alias" value="${escapeHtml(nilai(d))}" autocomplete="off">
            </div>`).join(''));
            cekRoleDept();
        }

        // Tandai alias dobel; tombol Simpan aktif hanya kalau ada perubahan & tidak ada yang dobel
        function cekRoleDept() {
            if (!rdData) return;
            const hitung = {};
            rdData.departemen.forEach(d => {
                const v = (String(d.department_id) in rdUbah ? rdUbah[d.department_id] : d.department_code) || '';
                if (v) hitung[v] = (hitung[v] || 0) + 1;
            });
            const dobel = Object.keys(hitung).filter(k => hitung[k] > 1);
            // Alias yang diubah wajib tepat rdPanjang karakter (kosong = alias dihapus, boleh)
            const kurang = Object.values(rdUbah).filter(v => v && v.length !== rdPanjang);

            $('#rd_list .rd-kode').each(function() {
                const diubah = String($(this).data('id')) in rdUbah;
                const pendek = diubah && this.value !== '' && this.value.length !== rdPanjang;
                $(this).toggleClass('salah', dobel.includes(this.value) || pendek);
            });
            const jml = Object.keys(rdUbah).length;
            if (dobel.length) {
                $('#rd_info').addClass('salah').html(
                    `<i class="fas fa-exclamation-circle"></i> Alias ${dobel.map(escapeHtml).join(', ')} dipakai lebih dari satu departemen`
                    );
            } else if (kurang.length) {
                $('#rd_info').addClass('salah').html(
                    `<i class="fas fa-exclamation-circle"></i> Alias wajib ${rdPanjang} karakter: ${kurang.map(escapeHtml).join(', ')}`
                    );
            } else {
                $('#rd_info').removeClass('salah').html(jml ?
                    `<i class="fas fa-pen"></i> ${jml} departemen diubah` :
                    '<i class="fas fa-info-circle"></i> Wajib 4 karakter: huruf, angka, dan titik. Contoh: <b>I.T.</b>, <b>SEWI</b>');
            }
            $('#rd_simpan').prop('disabled', !jml || dobel.length > 0 || kurang.length > 0);
        }

        function simpanRoleDepartment() {
            const items = Object.entries(rdUbah).map(([department_id, department_code]) => ({
                department_id,
                department_code
            }));
            if (!items.length) return;
            $('#rd_simpan').prop('disabled', true);
            kirim("{{ route('general-request-role-department-simpan') }}", {
                    items
                })
                .done(res => {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: res.message,
                        timer: 1600,
                        showConfirmButton: false
                    });
                    muatRoleDept();
                })
                .fail(xhr => {
                    pesanGagal(xhr);
                    cekRoleDept();
                });
        }

        const urlAksi = {
            simpan: "{{ route('general-request-simpan') }}",
            ajukan: "{{ route('general-request-ajukan') }}",
            batal: "{{ route('general-request-batal') }}",
        };

        // Export Excel: rentang tanggal + status sesuai filter halaman (departemen mengikuti hak lihat user)
        // Export: file diambil lewat fetch supaya ada notif "mengekspor" sampai file benar-benar siap
        function exportExcel() {
            const btn = $('.gr-excel-btn');
            if (btn.prop('disabled')) return;
            const q = new URLSearchParams({
                tgl_awal: $('#filter_tgl_awal').val() || '',
                tgl_akhir: $('#filter_tgl_akhir').val() || '',
                status: $('#filter_status').val() || '',
            });
            const isiAwal = btn.html();
            btn.prop('disabled', true).html('<i class="fas fa-circle-notch fa-spin"></i> Mengekspor...');
            const toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false });
            toast.fire({ title: 'Mengekspor Excel...', didOpen: () => Swal.showLoading() });

            const ambil = (sisa) => fetch(`{{ route('general-request-export-excel') }}?${q}`, { credentials: 'same-origin' })
                .then(res => (!res.ok || (res.headers.get('Content-Type') || '').includes('text/html')) && sisa > 0
                    ? new Promise(r => setTimeout(r, 500)).then(() => ambil(sisa - 1))
                    : res);
            ambil(1)
                .then(res => {
                    const jenis = res.headers.get('Content-Type') || '';
                    if (!res.ok || jenis.includes('text/html') || jenis.includes('application/json')) {
                        throw new Error(res.status === 401 || res.status === 419 ? 'Sesi berakhir, silakan muat ulang halaman.' : 'Export gagal, silakan coba lagi.');
                    }
                    const cd = res.headers.get('Content-Disposition') || '';
                    const m = cd.match(/filename\*?=(?:UTF-8'')?"?([^";]+)"?/i);
                    const nama = m ? decodeURIComponent(m[1]) : 'General Request.xlsx';
                    return res.blob().then(blob => ({ blob, nama }));
                })
                .then(({ blob, nama }) => {
                    const url = URL.createObjectURL(blob);
                    const a = $('<a>').attr({ href: url, download: nama }).appendTo('body');
                    a[0].click();
                    a.remove();
                    setTimeout(() => URL.revokeObjectURL(url), 1000);
                    toast.fire({ icon: 'success', title: 'Export selesai', timer: 1800 });
                })
                .catch(err => {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: err.message, confirmButtonColor: '#4a6fa5' });
                })
                .finally(() => btn.prop('disabled', false).html(isiAwal));
        }

        function kirim(url, data) {
            return $.ajax({
                url,
                type: 'POST',
                data: JSON.stringify(data),
                contentType: 'application/json',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Accept': 'application/json'
                },
            });
        }

        function pesanGagal(xhr) {
            const msg = xhr.responseJSON?.message || 'Terjadi kesalahan, silakan coba lagi.';
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: msg,
                confirmButtonColor: '#4a6fa5'
            });
        }

        let sedangMuatTabel = false;
        let gagalMuatTabel = 0;
        let antreMuatTabel = null;

        // resetHalaman: true = kembali ke halaman 1 (ganti filter), false = tetap di halaman sekarang
        function muatTabel(resetHalaman = true) {
            if (!tableRequest) return;
            if (sedangMuatTabel) {
                antreMuatTabel = (antreMuatTabel === true) || resetHalaman;
                return;
            }
            tableRequest.ajax.reload(null, resetHalaman);
        }

        function selesaiMuatTabel() {
            if (!sedangMuatTabel) return;
            sedangMuatTabel = false;
            if (antreMuatTabel !== null) {
                const reset = antreMuatTabel;
                antreMuatTabel = null;
                setTimeout(() => muatTabel(reset));
            }
        }

        function suksesLaluMuat(judul, html) {
            Swal.fire({
                icon: 'success',
                title: judul,
                html: html || '',
                timer: 1600,
                showConfirmButton: false
            });
            // Cukup muat ulang isi tabel, tetap di halaman yang sama (tanpa reload halaman)
            muatTabel(false);
        }

        // aksi: 'ajukan' | 'batal'
        function aksiRequest(aksi, index, e) {
            e.stopPropagation();
            const req = requests[index];
            if (aksi === 'ajukan' && !req.approver) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Belum ada approver',
                    html: `Request <b>${escapeHtml(req.no_request)}</b> belum punya approver. Ubah Draft lalu pilih approver.`,
                    showCancelButton: true,
                    confirmButtonText: '<i class="fas fa-pencil-alt"></i> Ubah Draft',
                    cancelButtonText: 'Nanti',
                    confirmButtonColor: '#4a6fa5',
                }).then(r => { if (r.isConfirmed) bukaFormRequest(index, 'ubah'); });
                return;
            }
            const teks = aksi === 'ajukan' ?
                {
                    judul: 'Ajukan Approval?',
                    isi: `Request <b>${escapeHtml(req.no_request)}</b> diajukan ke <b>${req.approvers.map(a => escapeHtml(a.nama)).join(' &amp; ')}</b> untuk approval. Setelah diajukan tidak bisa diubah lagi.`,
                    tombol: '<i class="fas fa-paper-plane"></i> Ajukan',
                    warna: '#4a6fa5'
                } :
                {
                    judul: 'Batalkan Request?',
                    isi: `Request <b>${escapeHtml(req.no_request)}</b> dibatalkan. Request tetap tersimpan dengan status Dibatalkan dan bisa dibuat request ulang.`,
                    tombol: '<i class="fas fa-ban"></i> Batalkan',
                    warna: '#c0505f'
                };

            Swal.fire({
                icon: 'question',
                title: teks.judul,
                html: teks.isi,
                showCancelButton: true,
                confirmButtonText: teks.tombol,
                cancelButtonText: 'Tidak',
                confirmButtonColor: teks.warna,
                showLoaderOnConfirm: true,
                allowOutsideClick: () => !Swal.isLoading(),
                preConfirm: () => kirim(urlAksi[aksi], {
                    id: req.id
                }).catch(xhr => {
                    Swal.showValidationMessage(xhr.responseJSON?.message ||
                        'Terjadi kesalahan, silakan coba lagi.');
                }),
            }).then(r => {
                if (r.isConfirmed && r.value) suksesLaluMuat(r.value.message,
                    `<b>${escapeHtml(r.value.no_request)}</b>`);
            });
        }

        // Approval diturunkan dari status + approvers (approver_1/2 & approve_1_at/2_at)
        function infoApproval(req) {
            const belum = ['Draft', 'Menunggu Approval', 'Dibatalkan'].includes(req.status);
            return {
                diajukan: !['Draft', 'Dibatalkan'].includes(req.status),
                batal: req.status === 'Dibatalkan',
                disetujui: !belum && req.status !== 'Ditolak',
                ditolak: req.status === 'Ditolak',
                // Sudah di-escape: approver yang sudah approve (nama · waktu); penolakan tidak mencatat siapa
                oleh: req.status === 'Ditolak' ? `Ditolak ${escapeHtml(req.diubah || '')}` :
                    ((req.approvers || []).filter(a => a.hasil).map(a => escapeHtml(a.nama) + (a.waktu ? ` · ${escapeHtml(a.waktu)}` : '')).join('<br>') || '-'),
            };
        }

        function formatTgl(tgl) {
            const [y, m, d] = tgl.split('-');
            return `${d}-${m}-${y}`;
        }

        /* ================= Modal tracking per item ================= */

        // Tracking: barang yang sama (satuan & tgl butuh sama) dari beberapa sub dept digabung jadi 1 item departemen.
        // Qty / PO / penerimaan dijumlah; rincian per sub dept disimpan di `rincian_sub`
        function gabungItemTracking(items) {
            const peta = new Map();
            items.forEach(it => {
                const k = `${it.item_id}|${it.unit_id}|${it.tgl_butuh || ''}`;
                const sub = { nama: it.sub_dept || 'Tanpa sub dept', qty: it.qty };
                if (!peta.has(k)) {
                    peta.set(k, { ...it, terima: [...it.terima], rincian_sub: [sub], ket: it.ket || '' });
                    return;
                }
                const g = peta.get(k);
                g.qty += it.qty;
                g.qty_po += it.qty_po;
                g.qty_terima += it.qty_terima;
                g.terima = g.terima.concat(it.terima).sort((a, b) => String(a.tgl).localeCompare(String(b.tgl)));
                g.no_po = [...new Set([g.no_po, it.no_po].filter(Boolean))].join(', ') || null;
                g.tgl_po = [g.tgl_po, it.tgl_po].filter(Boolean).sort()[0] || null;
                if (it.ket && !g.ket.split('; ').includes(it.ket)) g.ket = [g.ket, it.ket].filter(Boolean).join('; ');
                if (g.harga === null && it.harga !== null) g.harga = it.harga;
                g.rincian_sub.push(sub);
            });
            return [...peta.values()].map(g => ({ ...g, ket: g.ket || null }));
        }

        function showDetail(index, e) {
            if (e) e.stopPropagation();
            const req = { ...requests[index], items: gabungItemTracking(requests[index].items) };
            const cfg = statusCfg[req.status] ?? {
                icon: 'fa-question'
            };
            curReq = req;

            if (!req.items.length) {
                Swal.fire({
                    icon: 'info',
                    title: 'Belum ada item',
                    text: `Request ${req.no_request} belum punya item.`,
                    confirmButtonColor: '#4a6fa5'
                });
                return;
            }

            // Hero
            $('#md_no_request').text(req.no_request);
            $('#md_keperluan').text(req.departemen);
            $('#md_catatan').toggleClass('kosong', !req.catatan).html(
                `<div class="ic"><i class="fas fa-sticky-note"></i></div>
                <div><div class="t">Catatan Request</div><div class="v">${req.catatan ? escapeHtml(req.catatan) : 'Tidak ada catatan'}</div></div>`);
            const chips = [
                `<i class="fas ${cfg.icon}"></i> ${escapeHtml(req.status)}`,
                `<i class="far fa-calendar"></i> Request ${formatTgl(req.tgl_request)}`,
                `<i class="fas fa-building"></i> ${escapeHtml(req.departemen)}`,
                `<i class="fas fa-user"></i> ${escapeHtml(req.pemohon)}`,
                `<i class="fas fa-boxes"></i> ${req.items.length} item`,
            ];
            if (req.tgl_butuh) chips.splice(2, 0,
                `<i class="far fa-calendar-check"></i> Dibutuhkan ${formatTgl(req.tgl_butuh)}`);
            $('#md_chips').html(chips.map((c, i) =>
                `<span class="gr-hero-chip" style="animation-delay:${0.15 + i * 0.08}s">${c}</span>`).join(''));

            const totQty = req.items.reduce((a, it) => a + it.qty, 0);
            const totTerima = req.items.reduce((a, it) => a + it.qty_terima, 0);
            const persen = totQty > 0 ? Math.round(totTerima / totQty * 100) : 0;
            $('#md_persen').text('0%');
            setRing('#md_hero_ring', 40, 0, 0);

            renderApproval(req);

            // Daftar item
            $('#md_item_count').text(req.items.length);
            let list = '';
            req.items.forEach((it, i) => {
                const st = itemStatus(it, req.status);
                const c = itemStatusCfg[st];
                const berjalan = ['Menunggu Barang', 'Diterima Sebagian', 'Menunggu Approval', 'Belum PO'].includes(
                    st);
                const cari = [it.nama, it.no_po ?? '', ...it.terima.map(t => t.no_bpb)].join(' ').toLowerCase();
                list += `<div class="gr-item-card" data-i="${i}" data-status="${st}" data-cari="${escapeHtml(cari)}" style="--c-bg:${c.bg}; --c-fg:${c.fg}; animation-delay:${0.2 + i * 0.08}s" onclick="selectItem(${i})">
                <div class="gr-mini-ring">${ringSvg(44, 18, 'ring-' + i)}<div class="ic"><i class="fas ${c.icon}"></i></div></div>
                <div style="min-width:0">
                    <div class="nm" data-nama="${escapeHtml(it.nama)}">${escapeHtml(it.nama)}</div>
                    <div class="sub">${it.qty_terima} / ${it.qty} ${it.satuan} diterima${it.rincian_sub.length > 1 ? ` &middot; ${it.rincian_sub.length} sub dept` : ''}</div>
                    <div class="mt-1">${chip(st, c.bg, c.fg, berjalan ? 'pulse' : '')}</div>
                </div>
                <i class="fas fa-chevron-right arrow"></i>
            </div>`;
            });
            $('#md_item_list').html(list);
            req.items.forEach((it, i) => setRing('.ring-' + i, 18, 0, 0));

            // Reset pencarian & legend
            itemFilter = {
                q: '',
                status: ''
            };
            $('#md_item_search').val('');
            renderLegend(req);
            applyItemFilter();

            // Pilih item pertama yang belum lengkap
            const awal = req.items.findIndex(it => itemStatus(it, req.status) !== 'Lengkap');
            selectItem(awal < 0 ? 0 : awal, true);

            $('#modalTracking').modal('show');

            setTimeout(() => {
                setRing('#md_hero_ring', 40, persen, 0);
                countUp('#md_persen', persen, 1300, '%');
                req.items.forEach((it, i) => setRing('.ring-' + i, 18, it.qty > 0 ? it.qty_terima / it.qty * 100 :
                    0, 200 + i * 120));
            }, 350);
        }

        function escapeHtml(t) {
            return $('<div>').text(t ?? '').html();
        }

        /* ---------- Legend status item (sekaligus filter) ---------- */
        function renderLegend(req) {
            // Saat request belum disetujui, semua item statusnya sama dengan request
            const daftar = ['Draft', 'Menunggu Approval', 'Ditolak', 'Dibatalkan'].includes(req.status) ?
                [req.status] :
                ['Belum PO', 'Menunggu Barang', 'Diterima Sebagian', 'Lengkap'];

            const jumlah = {};
            req.items.forEach(it => {
                const st = itemStatus(it, req.status);
                jumlah[st] = (jumlah[st] || 0) + 1;
            });

            const html = daftar.map((st, i) => {
                const c = itemStatusCfg[st];
                const n = jumlah[st] || 0;
                return `<span class="lg ${n ? '' : 'kosong'}" data-status="${st}" title="${c.ket}"
                style="--c-bg:${c.bg}; --c-fg:${c.fg}; animation-delay:${0.15 + i * 0.06}s">
                ${st} <span class="n">${n}</span>
            </span>`;
            }).join('');

            $('#md_legend').html(html).removeClass('ada-filter');
            $('#md_legend_ket').html(
                '<i class="fas fa-info-circle"></i> Klik status untuk menyaring item, arahkan kursor untuk melihat artinya'
                );
        }

        $(document).on('click', '#md_legend .lg', function() {
            const st = $(this).data('status');
            itemFilter.status = itemFilter.status === st ? '' : st;

            $('#md_legend .lg').removeClass('active');
            if (itemFilter.status) $(this).addClass('active');
            $('#md_legend').toggleClass('ada-filter', !!itemFilter.status);

            applyItemFilter();
        });

        $(document).on('mouseenter', '#md_legend .lg', function() {
            const c = itemStatusCfg[$(this).data('status')];
            $('#md_legend_ket').html(
                `<span style="color:${c.fg}"><i class="fas ${c.icon}"></i> <b>${$(this).data('status')}</b></span> &middot; ${c.ket}`
                );
        });

        $(document).on('mouseleave', '#md_legend', function() {
            const st = itemFilter.status;
            $('#md_legend_ket').html(st ?
                `<i class="fas fa-filter"></i> Menampilkan item <b>${st}</b> &middot; klik lagi untuk menampilkan semua` :
                '<i class="fas fa-info-circle"></i> Klik status untuk menyaring item, arahkan kursor untuk melihat artinya'
                );
        });

        /* ---------- Pencarian item ---------- */
        $(document).on('input', '#md_item_search', function() {
            itemFilter.q = this.value.trim().toLowerCase();
            $('#md_item_clear').toggle(!!itemFilter.q);
            applyItemFilter();
        });

        $(document).on('click', '#md_item_clear', function() {
            $('#md_item_search').val('').trigger('input').focus();
        });

        function applyItemFilter() {
            const {
                q,
                status
            } = itemFilter;
            let tampil = 0;

            $('#md_item_list .gr-item-card').each(function() {
                const card = $(this);
                const cocok = (!q || String(card.attr('data-cari')).includes(q)) && (!status || card.attr(
                    'data-status') === status);
                card.toggleClass('hide', !cocok);
                if (cocok) tampil++;

                // Sorot teks yang dicari di nama barang
                const nm = card.find('.nm');
                const nama = String(nm.attr('data-nama'));
                if (q && cocok) {
                    const idx = nama.toLowerCase().indexOf(q);
                    nm.html(idx >= 0 ?
                        escapeHtml(nama.slice(0, idx)) + '<mark>' + escapeHtml(nama.slice(idx, idx + q
                        .length)) + '</mark>' + escapeHtml(nama.slice(idx + q.length)) :
                        escapeHtml(nama));
                } else {
                    nm.text(nama);
                }
            });

            $('#md_item_empty').toggle(tampil === 0);
            $('#md_item_count').text(q || status ? `${tampil} / ${curReq.items.length}` : curReq.items.length);

            // Kalau item yang sedang dibuka tersaring, pindah ke item pertama yang tampil
            const aktif = $('#md_item_list .gr-item-card.active');
            if (tampil > 0 && aktif.hasClass('hide')) {
                selectItem($('#md_item_list .gr-item-card:not(.hide)').first().data('i'));
            }
        }

        // Riwayat request (kiri ke kanan): Dibuat -> Diajukan -> Approver -> PO -> Diterima.
        // Disetujui dibaca dari waktu approve tiap approver (approve_x_at)
        function renderApproval(req) {
            const ap = infoApproval(req);
            const items = req.items || [];
            const adaPo = items.some(it => (it.qty_po || 0) > 0);
            const qty = items.reduce((n, it) => n + (it.qty || 0), 0);
            const terima = items.reduce((n, it) => n + (it.qty_terima || 0), 0);

            const step = (cls, bg, fg, icon, judul, meta) => `<div class="gr-appr-step ${cls}" style="--c-bg:${bg}; --c-fg:${fg}">
            <div class="ic"><i class="fas ${icon}"></i></div>
            <div><div class="t">${judul}</div><div class="m">${meta}</div></div>
        </div>`;
            const arrow = '<i class="fas fa-long-arrow-alt-right gr-appr-arrow"></i>';
            const OK = ['#e6f3eb', '#4a8c68'], TUNGGU = ['#fbf3df', '#b98a2e'], NANTI = ['#eef1f5', '#9aa3b0'];
            const langkah = [];
            const tambah = (cls, warna, icon, judul, meta) => langkah.push(step(cls, warna[0], warna[1], icon, judul, meta));

            tambah('', OK, 'fa-pencil-alt', 'Dibuat', `${escapeHtml(req.dibuat || '-')} &middot; ${escapeHtml(req.pemohon || '-')}`);

            if (ap.batal) {
                tambah('', ['#e7e9ee', '#525c6b'], 'fa-ban', 'Dibatalkan', escapeHtml(req.diubah || '-'));
            } else {
                // Diajukan (waktu ajukan belum dicatat; tampil tgl request)
                if (ap.diajukan) tambah('', OK, 'fa-paper-plane', 'Diajukan', `Request ${formatTgl(req.tgl_request)}`);
                else tambah('wait', TUNGGU, 'fa-paper-plane', 'Belum diajukan', 'Masih Draft');

                // Approver: 1 langkah per approver (approve bersamaan)
                const daftarAp = req.approvers || [];
                if (daftarAp.length) {
                    daftarAp.forEach(a => {
                        const nama = escapeHtml(a.nama);
                        if (a.waktu) tambah('', OK, 'fa-check', `Disetujui${daftarAp.length > 1 ? ` (Approver ${a.ke})` : ''}`, `${nama} &middot; ${escapeHtml(a.waktu)}`);
                        else if (ap.ditolak) tambah('', NANTI, 'fa-minus', `Approver ${a.ke}`, nama);
                        else if (ap.diajukan) tambah('wait', TUNGGU, 'fa-hourglass-half', `Menunggu Approver ${daftarAp.length > 1 ? a.ke : ''}`, nama);
                        else tambah('nanti', NANTI, 'fa-user-check', `Approver ${daftarAp.length > 1 ? a.ke : ''}`, nama);
                    });
                } else if (ap.disetujui) {
                    tambah('', OK, 'fa-check', 'Disetujui', 'Approver tidak tercatat');
                } else {
                    tambah(ap.diajukan ? 'wait' : 'nanti', ap.diajukan ? TUNGGU : NANTI, 'fa-user-check', 'Approval', 'Belum ada approver');
                }

                if (ap.ditolak) {
                    tambah('', ['#f9e8ea', '#c0505f'], 'fa-times', 'Ditolak', escapeHtml(req.diubah || '-'));
                } else {
                    // PO & penerimaan
                    if (!ap.disetujui) tambah('nanti', NANTI, 'fa-file-invoice', 'PO', 'Setelah disetujui');
                    else if (adaPo) tambah('', OK, 'fa-file-invoice', 'PO Dibuat', 'Oleh Purchasing');
                    else tambah('wait', TUNGGU, 'fa-file-invoice', 'Menunggu PO', 'Dibuatkan oleh Purchasing');

                    if (req.status === 'Selesai') tambah('', OK, 'fa-flag-checkered', 'Diterima Lengkap', `${terima} / ${qty}`);
                    else if (terima > 0) tambah('wait', TUNGGU, 'fa-truck-loading', 'Diterima Sebagian', `${terima} / ${qty}`);
                    else tambah('nanti', NANTI, 'fa-truck-loading', 'Diterima', 'Setelah barang datang');
                }
            }

            $('#md_approval').html(langkah.join(arrow));
            // ulangi animasi masuk
            const el = document.getElementById('md_approval');
            el.style.animation = 'none';
            void el.offsetWidth;
            el.style.animation = '';
        }

        function selectItem(i, langsung = false) {
            $('#md_item_list .gr-item-card').removeClass('active');
            $(`#md_item_list .gr-item-card[data-i="${i}"]`).addClass('active');

            if (langsung) {
                renderItemDetail(curReq, curReq.items[i]);
                return;
            }
            // Token: klik cepat berpindah item tidak boleh tertimpa animasi item sebelumnya
            const token = ++renderToken;
            $('#md_detail').removeClass('in').addClass('out');
            setTimeout(() => {
                if (token === renderToken) renderItemDetail(curReq, curReq.items[i]);
            }, 150);
        }

        function renderItemDetail(req, it) {
            const token = ++renderToken;
            const masihAktif = () => token === renderToken;
            const st = itemStatus(it, req.status);
            const c = itemStatusCfg[st];
            const sisa = it.qty - it.qty_terima;
            const ap = infoApproval(req);
            const terimaPertama = it.terima[0];
            const terimaTerakhir = it.terima[it.terima.length - 1];

            // Kepala
            let html = `<div class="gr-d-head" style="--c-fg:${c.fg}">
            <div class="gr-d-icon ${it.gambar ? 'ada-gb' : ''}" id="md_d_icon"><i class="fas ${c.icon}"></i>${it.gambar ? `<img src="${escapeHtml(it.gambar)}" alt="" onload="this.parentNode.classList.add('gb-ok')" onerror="this.parentNode.classList.remove('ada-gb'); this.remove()"><span class="st"><i class="fas ${c.icon}"></i></span>` : ''}</div>
            <div style="min-width:0">
                <div class="gr-d-name">${escapeHtml(it.nama)}</div>
                <div class="gr-d-meta">
                    ${chip(st, c.bg, c.fg)}
                    ${it.kode ? `<span class="gr-tag"><i class="fas fa-barcode"></i> ${escapeHtml(it.kode)}</span>` : ''}
                    ${(it.rincian_sub || []).filter(r => r.nama).map(r => `<span class="gr-tag" title="Sub dept"><i class="fas fa-sitemap"></i> ${escapeHtml(r.nama)}${it.rincian_sub.length > 1 ? ` <b>${formatAngka(r.qty)} ${escapeHtml(it.satuan)}</b>` : ''}</span>`).join('')}
                    ${it.tgl_butuh ? `<span class="gr-tag"><i class="far fa-calendar-check"></i> Dibutuhkan ${formatTgl(it.tgl_butuh)}</span>` : ''}
                    <span class="gr-tag"><i class="fas fa-file-invoice"></i> ${escapeHtml(it.no_po ?? 'Belum ada PO')}</span>
                    ${it.harga !== null ? `<span class="gr-tag"><i class="fas fa-tag"></i> ${escapeHtml(it.mata_uang ?? '')} ${formatAngka(it.harga)}</span>` : ''}
                    ${it.ket ? `<span class="gr-tag"><i class="fas fa-sticky-note"></i> ${escapeHtml(it.ket)}</span>` : ''}
                    ${it.terima.length ? `<span class="gr-tag"><i class="fas fa-truck"></i> ${it.terima.length}x pengiriman</span>` : ''}
                </div>
            </div>
        </div>`;

            // Angka
            const tiles = [
                ['Request', it.qty, '#4a6fa5', 'fa-clipboard-list'],
                ['Qty PO', it.qty_po, '#8a7cc2', 'fa-file-invoice'],
                ['Diterima', it.qty_terima, '#5fa880', 'fa-box-open'],
                ['Sisa', sisa, sisa > 0 ? '#d26b78' : '#5fa880', 'fa-hourglass-half'],
            ];
            html += '<div class="gr-tiles">';
            tiles.forEach((t, i) => {
                html += `<div class="gr-tile" style="--t-color:${t[2]}; animation-delay:${0.1 + i * 0.07}s">
                <div class="l"><i class="fas ${t[3]}"></i> ${t[0]}</div>
                <div class="v"><span class="cu" data-v="${t[1]}">0</span> <small>${it.satuan}</small></div>
            </div>`;
            });
            html += '</div>';

            // Stepper per item
            const steps = [{
                    label: 'Disetujui',
                    icon: 'fa-check',
                    date: ''
                },
                {
                    label: 'PO Dibuat',
                    icon: 'fa-file-invoice',
                    date: it.tgl_po ?? ''
                },
                {
                    label: 'Diterima',
                    icon: 'fa-truck-loading',
                    date: terimaPertama ? terimaPertama.tgl : ''
                },
                {
                    label: 'Lengkap',
                    icon: 'fa-flag-checkered',
                    date: sisa <= 0 && terimaTerakhir ? terimaTerakhir.tgl : ''
                },
            ];
            const posisi = {
                'Draft': -1,
                'Dibatalkan': -1,
                'Menunggu Approval': 0,
                'Ditolak': 0,
                'Belum PO': 1,
                'Menunggu Barang': 2,
                'Diterima Sebagian': 2,
                'Lengkap': 4
            } [st];

            html += '<div class="gr-stepper"><div class="gr-line-fill"></div>';
            steps.forEach((s, i) => {
                let label = s.label;
                let icon = s.icon;
                if (st === 'Ditolak' && i === 0) {
                    label = 'Ditolak';
                    icon = 'fa-times';
                }
                if (st === 'Diterima Sebagian' && i === 2) label = `Diterima ${it.qty_terima}/${it.qty}`;
                html += `<div class="gr-step" data-step="${i}">
                <div class="gr-dot"><i class="fas ${icon}"></i></div>
                <div class="gr-step-label">${label}</div>
                <div class="gr-step-date">${s.date ? s.date.split(' ')[0] : ''}</div>
            </div>`;
            });
            html += '</div>';

            // Riwayat item (terbaru di atas)
            const ev = [];
            ev.push({
                ic: 'fa-plus',
                bg: '#eef1f5',
                fg: '#6b7687',
                title: 'Request dibuat',
                meta: req.dibuat ?? formatTgl(req.tgl_request)
            });
            if (ap.diajukan) ev.push({
                ic: 'fa-paper-plane',
                bg: '#e6f3eb',
                fg: '#4a8c68',
                title: 'Request diajukan',
                meta: formatTgl(req.tgl_request)
            });
            if (ap.disetujui) ev.push({
                ic: 'fa-check',
                bg: '#e6f3eb',
                fg: '#4a8c68',
                title: 'Request disetujui',
                meta: ap.oleh
            });
            if (ap.batal) ev.push({
                ic: 'fa-ban',
                bg: '#e7e9ee',
                fg: '#525c6b',
                title: 'Request dibatalkan',
                meta: `${req.diubah ?? ''} · ${escapeHtml(req.pemohon)}`
            });
            if (ap.ditolak) ev.push({
                ic: 'fa-times',
                bg: '#f9e8ea',
                fg: '#c0505f',
                title: 'Request ditolak',
                meta: ap.oleh
            });
            if (it.tgl_po) ev.push({
                ic: 'fa-file-invoice',
                bg: '#efecf8',
                fg: '#8a7cc2',
                title: `PO dibuat (${it.qty_po} ${it.satuan})`,
                meta: `${it.tgl_po} · Purchasing`,
                ket: it.no_po
            });
            it.terima.forEach((t, i) => ev.push({
                ic: 'fa-truck',
                bg: '#e6f3eb',
                fg: '#4a8c68',
                title: `Diterima gudang <span class="gr-tl-qty">+${t.qty} ${it.satuan}</span>`,
                meta: `${t.tgl} · Pengiriman ke-${i + 1}`,
                ket: t.no_bpb,
            }));
            if (st === 'Lengkap') ev.push({
                ic: 'fa-flag-checkered',
                bg: '#e6f3eb',
                fg: '#4a8c68',
                title: 'Item lengkap',
                meta: terimaTerakhir.tgl
            });
            ev.reverse();

            const pending = {
                'Draft': 'Request belum diajukan',
                'Menunggu Approval': 'Menunggu approval atasan',
                'Belum PO': 'Menunggu dibuatkan PO oleh Purchasing',
                'Menunggu Barang': `Menunggu barang datang (${sisa} ${it.satuan})`,
                'Diterima Sebagian': `Menunggu sisa ${sisa} ${it.satuan} datang`,
            } [st];
            if (pending) ev.unshift({
                pending: true,
                ic: 'fa-circle-notch',
                title: pending,
                meta: 'Proses berikutnya'
            });

            html +=
                '<div class="gr-section-title mt-2"><i class="fas fa-history"></i> Riwayat Item</div><ul class="gr-timeline">';
            ev.forEach((e, i) => {
                html += `<li class="${e.pending ? 'pending' : ''}" style="animation-delay:${0.5 + i * 0.1}s; --tl-bg:${e.bg ?? '#fff'}; --tl-fg:${e.fg ?? '#b4bccb'}">
                <span class="tl-ic"><i class="fas ${e.ic}"></i></span>
                <div class="gr-tl-title">${e.title}</div>
                <div class="gr-tl-meta"><i class="far fa-clock"></i> ${e.meta}</div>
                ${e.ket ? `<div class="gr-tl-ket">${e.ket}</div>` : ''}
            </li>`;
            });
            html += '</ul>';

            $('#md_detail').html(html).removeClass('out in');
            void document.getElementById('md_detail').offsetWidth;
            $('#md_detail').addClass('in');

            $('#md_detail .cu').each(function() {
                countUp(this, $(this).data('v'), 700);
            });

            // Stepper menyala bertahap
            steps.forEach((s, i) => {
                setTimeout(() => {
                    if (!masihAktif()) return;
                    const el = $(`#md_detail .gr-step[data-step="${i}"]`);
                    if (st === 'Ditolak' && i === 0) el.addClass('rejected');
                    else if (st === 'Diterima Sebagian' && i === 2) el.addClass('partial');
                    else if (i < posisi) el.addClass('done');
                    else if (i === posisi) el.addClass('current');
                }, 300 + i * 200);
            });
            const isi = posisi <= 0 ? 0 : Math.min(posisi, steps.length - 1);
            setTimeout(() => {
                if (masihAktif()) $('#md_detail .gr-line-fill').css('width', (isi / (steps.length - 1)) * 75 + '%');
            }, 300);

            // Item lengkap -> partikel
            if (st === 'Lengkap') setTimeout(() => {
                if (masihAktif()) burst('#md_d_icon');
            }, 450);
        }

        function burst(sel) {
            const el = $(sel);
            const warna = ['#5fa880', '#7cc39b', '#e9a46a', '#86a5d3', '#f2c08f'];
            for (let i = 0; i < 14; i++) {
                const sudut = (Math.PI * 2 / 14) * i;
                const jarak = 34 + Math.random() * 18;
                $(`<span class="gr-spark" style="background:${warna[i % warna.length]}; --dx:${Math.cos(sudut) * jarak}px; --dy:${Math.sin(sudut) * jarak}px"></span>`)
                    .appendTo(el);
            }
            setTimeout(() => el.find('.gr-spark').remove(), 900);
        }

        /* ================= Modal Buat / Ubah Request ================= */
        let bfItems = [];
        let bfId = null; // id request yang sedang diubah (Draft), null = request baru
        let bfMenyimpan = false;
        const noBaru = @json($no_request);
        const tglHariIni = @json(date('Y-m-d'));
        const mataUangDefault = @json($mata_uang_default);
        // [{id, kode}] pilihan mata uang per barang (default IDR)
        const daftarMataUang = @json($mata_uang);
        // Tgl dibutuhkan untuk barang baru: ikut tgl terakhir yang dipakai (ditambah / direvisi), awalnya H+7
        let tglTerakhir = null;
        // department_id HRIS -> [{id, nama}] sub dept aktif
        const subDeptPerDept = @json((object) $sub_dept);

        function daftarSubDept() {
            return subDeptPerDept[$('#bf_departemen').val()] || [];
        }

        function namaSubDeptJs(id) {
            const s = daftarSubDept().find(x => x.id === id);
            return s ? s.nama : '';
        }

        // Sub dept otomatis untuk barang dari katalog: hanya kalau departemen cuma punya 1 sub dept
        function subDeptOtomatis() {
            const daftar = daftarSubDept();
            return daftar.length === 1 ? daftar[0].id : '';
        }

        $(function() {
            $('.bf-select2').select2({
                theme: 'bootstrap4',
                width: '100%',
                dropdownParent: $('#modalBuat')
            });
            // Ganti departemen (request baru): sub dept item yang bukan milik departemen baru dikosongkan
            $('#bf_departemen').on('change', function() {
                const valid = daftarSubDept().map(s => s.id);
                let dikosongkan = 0;
                bfItems.forEach(it => {
                    if (it.sub_dept_id && !valid.includes(it.sub_dept_id)) { it.sub_dept_id = ''; it.sub_dept = ''; dikosongkan++; }
                    else if (!it.sub_dept_id && valid.length === 1) { it.sub_dept_id = valid[0]; it.sub_dept = namaSubDeptJs(valid[0]); }
                });
                if (bfItems.length) { rapikanItem(); renderBfItems(); }
                if (dikosongkan) Swal.fire({ toast: true, position: 'top-end', icon: 'warning', title: `${dikosongkan} item perlu pilih sub dept lagi`, timer: 2200, showConfirmButton: false });
            });

            // Katalog barang: cari (ditunda sebentar selama mengetik), scroll untuk memuat berikutnya
            let tundaCari = null;
            $('#bf_kat_cari').on('input', function() {
                $('#bf_kat_hapus').prop('hidden', !this.value);
                clearTimeout(tundaCari);
                tundaCari = setTimeout(() => muatKatalog(true), 300);
            }).on('keydown', function(e) {
                if (e.key === 'ArrowDown' || e.key === 'Enter') {
                    e.preventDefault();
                    clearTimeout(tundaCari);
                    // Hasil belum sesuai kata kunci / masih dimuat: cari ulang lalu fokus ke baris pertama
                    if (katKunci !== kunciKatalog() || katXhr) muatKatalog(true);
                }
            });
            $('#bf_kat_hapus').on('click', function() {
                $('#bf_kat_cari').val('').trigger('focus');
                $(this).prop('hidden', true);
                clearTimeout(tundaCari);
                muatKatalog(true);
            });
            // Chip sub group: 1 aktif
            $('#bf_kat_sub').on('click', 'button', function() {
                if ($(this).hasClass('aktif')) return;
                $(this).addClass('aktif').siblings().removeClass('aktif');
                this.scrollIntoView({
                    block: 'nearest',
                    inline: 'nearest'
                });
                clearTimeout(tundaCari);
                muatKatalog(true);
                $('#bf_kat_cari').trigger('focus');
            });
            $('#bf_katalog').on('scroll', function() {
                if (this.scrollTop + this.clientHeight >= this.scrollHeight - 120) muatKatalog(false);
            });
            // Katalog gaya keranjang: + untuk masukkan, lalu atur jumlah dengan - / +
            $('#bf_katalog')
                .on('click', '.tbh', function() {
                    tambahKeranjang($(this).closest('.gr-kat-item'));
                })
                .on('click', '.kq button', function() {
                    ubahKeranjang(Number($(this).closest('.gr-kat-item').data('id')), Number($(this).data('n')));
                })
                .on('input', '.kq input', function() {
                    this.value = this.value.replace(/[^0-9]/g, '');
                    const v = parseInt(this.value || 0);
                    if (v > 0) aturQtyKeranjang(Number($(this).closest('.gr-kat-item').data('id')), v, false);
                })
                .on('change', '.kq input', function() {
                    aturQtyKeranjang(Number($(this).closest('.gr-kat-item').data('id')), parseInt(this.value || 0), true);
                })
                .on('keydown', '.kq input', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        this.blur();
                    }
                });

            // Tampilan gambar / daftar (diingat per browser)
            let viewKat = 'grid';
            try { viewKat = localStorage.getItem('gr_kat_view') || 'grid'; } catch (e) {}
            const pasangView = v => {
                $('#bf_katalog').toggleClass('grid', v === 'grid');
                $('#bf_kat_view button').removeClass('aktif').filter(`[data-view="${v}"]`).addClass('aktif');
            };
            pasangView(viewKat);
            $('#bf_kat_view').on('click', 'button', function() {
                const v = $(this).data('view');
                pasangView(v);
                try { localStorage.setItem('gr_kat_view', v); } catch (e) {}
                pasangGambar();
            });

            // Nomor request mengikuti alias departemen yang dipilih (kecuali ubah Draft: nomor sudah tetap)
            $('#bf_departemen').on('change', function() {
                if (bfId) return;
                $('#bf_no').text($(this).find(':selected').data('no') || noBaru);
            });

            $('#modalBuat').on('shown.bs.modal', () => {
                if (!$('#bf_departemen').val()) $('#bf_departemen').select2('open');
                else if ($('#bf_panel').hasClass('buka')) $('#bf_kat_cari').trigger('focus');
            });
            $('#modalBuat').on('hidden.bs.modal', () => tutupPanelItem());

            // Cari & filter daftar item
            $('#bf_cari').on('input', () => renderBfItems());
        });

        // Default tgl dibutuhkan = 7 hari dari tgl request; tgl terakhir yang dipakai diingat untuk item berikutnya
        function tglTambah(tgl, hari) {
            const d = new Date(tgl + 'T00:00:00');
            d.setDate(d.getDate() + hari);
            return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
        }

        // Tgl dibutuhkan barang baru: tgl terakhir yang dipakai (kalau belum lewat), selain itu H+7
        function tglBaru() {
            return tglTerakhir && tglTerakhir >= tglHariIni ? tglTerakhir : tglTambah(tglHariIni, 7);
        }

        function aturTglButuh() {
            tglTerakhir = null;
        }

        function mataUangAwal() {
            const ada = daftarMataUang.find(c => String(c.id) === String(mataUangDefault)) || daftarMataUang[0];
            return ada ? { currency_id: Number(ada.id), mata_uang: ada.kode } : { currency_id: 0, mata_uang: '' };
        }

        function resetEntry() {
            // Katalog kembali ke "Semua" tanpa kata kunci (hasilnya biasanya sudah ada di cache)
            $('#bf_kat_cari').val('');
            $('#bf_kat_hapus').prop('hidden', true);
            $('#bf_kat_sub button').removeClass('aktif').first().addClass('aktif');
        }

        /* ---------- Katalog barang (panel Tambah Item) ---------- */
        let katHal = 0,
            katQ = null,
            katKunci = null,
            katAdaLagi = true,
            katXhr = null,
            katJumlah = 0;
        const katData = {}; // id -> data barang dari server
        const katCache = new Map(); // "sub|kata kunci|halaman" -> hasil; cari ulang yang sama tidak memanggil server lagi

        function subKatalog() {
            return String($('#bf_kat_sub button.aktif').data('sub') || '');
        }

        function kunciKatalog() {
            return subKatalog() + '|' + $('#bf_kat_cari').val().trim().toLowerCase();
        }

        // reset = cari ulang dari halaman 1; lalu = fokus ke baris pertama setelah hasil datang
        function muatKatalog(reset, lalu = false) {
            const q = $('#bf_kat_cari').val().trim();
            const sub = subKatalog();
            if (!reset && (!katAdaLagi || katXhr)) return;
            if (reset) {
                if (katXhr) katXhr.abort();
                katXhr = null;
                katHal = 0;
                katAdaLagi = true;
                katQ = q;
                katKunci = kunciKatalog();
                katJumlah = 0;
                $('#bf_katalog').scrollTop(0);
            }
            const hal = katHal + 1;
            const kunciCache = `${katKunci}|${hal}`;

            const tampilkan = res => {
                katHal = hal;
                katAdaLagi = res.ada_lagi;
                katJumlah += res.items.length;
                res.items.forEach(b => {
                    katData[b.id] = b;
                });
                const html = res.items.map(barisKatalog).join('');
                if (reset) {
                    const judul = $('#bf_kat_sub button.aktif').data('sub') ? $('#bf_kat_sub button.aktif').contents()
                        .first().text().trim() : 'Semua barang';
                    $('#bf_katalog').html(html ?
                        `<div class="gr-kat-hasil" id="bf_kat_jml"></div>${html}` :
                        `<div class="gr-kat-info"><i class="fas fa-search"></i>${q ? `Barang "<b>${escapeHtml(q)}</b>" tidak ditemukan` : 'Belum ada barang di kategori ini'}</div>`
                        );
                    $('#bf_katalog').data('judul', judul);
                } else {
                    $('#bf_katalog').find('.gr-kat-muat').remove().end().append(html);
                }
                // Jumlah yang sudah tampil; "+" berarti masih ada lagi saat di-scroll (tanpa hitung total di server)
                $('#bf_kat_jml').html(
                    `<b>${escapeHtml($('#bf_katalog').data('judul'))}</b> &middot; ${katJumlah}${katAdaLagi ? '+' : ''} barang${q ? ` untuk "${escapeHtml(q)}"` : ''}`
                    );
                if (!katAdaLagi && katHal > 1) $('#bf_katalog').append(
                    '<div class="gr-kat-info kecil">Semua barang sudah tampil</div>');
                pasangGambar();
                // Layar lebar (tampilan gambar): 30 barang belum memenuhi layar -> tidak bisa di-scroll, jadi muat berikutnya
                const wadah = $('#bf_katalog')[0];
                if (katAdaLagi && wadah.clientHeight > 0 && wadah.scrollHeight <= wadah.clientHeight + 120) {
                    setTimeout(() => muatKatalog(false));
                }
            };

            if (katCache.has(kunciCache)) {
                tampilkan(katCache.get(kunciCache));
                return;
            }

            $('#bf_kat_putar').addClass('on');
            if (!reset) $('#bf_katalog').append(
                '<div class="gr-kat-info kecil gr-kat-muat"><i class="fas fa-circle-notch fa-spin"></i>Memuat...</div>');

            katXhr = $.getJSON("{{ route('general-request-barang') }}", {
                    q,
                    sub,
                    hal
                })
                .done(res => {
                    // Cache dibatasi supaya memori browser tidak membengkak
                    if (katCache.size > 60) katCache.delete(katCache.keys().next().value);
                    katCache.set(kunciCache, res);
                    tampilkan(res);
                })
                .fail((xhr, status) => {
                    if (status === 'abort') return;
                    $('#bf_katalog .gr-kat-muat').remove();
                    if (reset) $('#bf_katalog').html(
                        '<div class="gr-kat-info"><i class="fas fa-exclamation-triangle"></i>Gagal memuat barang</div>'
                        );
                    pesanGagal(xhr);
                })
                .always((_, status) => {
                    if (status === 'abort') return;
                    katXhr = null;
                    $('#bf_kat_putar').removeClass('on');
                });
        }

        // Qty barang ini yang sudah ada di daftar (semua tgl)
        function qtyDiDaftar(id) {
            return bfItems.filter(it => it.item_id === id).reduce((n, it) => n + (it.qty || 0), 0);
        }

        // Baris di daftar request (bfItems) untuk barang ini
        function barisKeranjang(id) {
            return bfItems.map((it, i) => it.item_id === id ? i : -1).filter(i => i >= 0);
        }

        // Tombol di kartu: belum di keranjang = Tambah; 1 baris = atur jumlah; >1 baris (beda tgl / sub dept) = atur di daftar
        function aksiKatalog(b) {
            if (!b.unit_id || !b.satuan) return '<span class="ket-mati">Satuan belum diatur</span>';
            const baris = barisKeranjang(b.id);
            const st = escapeHtml(b.satuan);
            if (!baris.length) return '<button type="button" class="tbh"><i class="fas fa-plus"></i> Tambah</button>';
            if (baris.length > 1) {
                const total = baris.reduce((n, i) => n + (bfItems[i].qty || 0), 0);
                return `<div class="multi"><b>${formatAngka(total)} ${st}</b> di keranjang<small>atur di daftar request</small></div>`;
            }
            const q = bfItems[baris[0]].qty || 0;
            return `<div class="kq">
                    <button type="button" data-n="-1" class="${q <= 1 ? 'hapus' : ''}" title="${q <= 1 ? 'Keluarkan dari keranjang' : 'Kurangi'}"><i class="fas fa-${q <= 1 ? 'trash-alt' : 'minus'}"></i></button>
                    <input type="text" inputmode="numeric" value="${q || ''}" aria-label="Jumlah">
                    <span class="st">${st}</span>
                    <button type="button" data-n="1" title="Tambah"><i class="fas fa-plus"></i></button>
                </div>`;
        }

        function kunciAksiKatalog(id) {
            const baris = barisKeranjang(id);
            return baris.length === 1 ? `1|${bfItems[baris[0]].qty}` : String(baris.length);
        }

        function barisKatalog(b) {
            const q = katQ ? katQ.toLowerCase().split(/\s+/)[0] : '';
            const ada = barisKeranjang(b.id).length > 0;
            const bisa = !!(b.unit_id && b.satuan);
            const info = [b.kode ? sorotTeks(b.kode, q) : '', b.varian ? sorotTeks(b.varian, q) : ''].filter(Boolean).join(' &middot; ') +
                (bisa ? `<span class="sat">${escapeHtml(b.satuan)}</span>` : '<span class="salah"> &middot; Satuan belum diatur</span>');
            return `
            <div class="gr-kat-item ${ada ? 'ada' : ''} ${bisa ? '' : 'mati'}" data-id="${b.id}" data-k="${kunciAksiKatalog(b.id)}">
                <div class="gb"><i class="fas fa-box-open"></i>${b.gambar ? `<img data-src="${escapeHtml(b.gambar)}" alt="">` : ''}<span class="cek"><i class="fas fa-check"></i></span></div>
                <div class="isi">
                    <div class="nm" title="${escapeHtml(b.nama)}">${sorotTeks(b.nama, q)}</div>
                    <div class="meta">${info}</div>
                </div>
                <div class="aksi">${aksiKatalog(b)}</div>
            </div>`;
        }

        // Tandai ulang kartu setelah isi keranjang berubah (kartu yang sedang diketik qty-nya tidak digambar ulang)
        function segarkanTandaKatalog() {
            $('#bf_katalog .gr-kat-item').each(function() {
                const id = Number($(this).data('id'));
                const b = katData[id];
                if (!b) return;
                $(this).toggleClass('ada', barisKeranjang(id).length > 0);
                const k = kunciAksiKatalog(id);
                if (String($(this).attr('data-k')) === k) return;
                if ($(this).find('.kq input').is(':focus') && k.startsWith('1|')) {
                    $(this).attr('data-k', k);
                    return;
                }
                $(this).attr('data-k', k).find('.aksi').html(aksiKatalog(b));
            });
            // Ringkasan keranjang di footer
            const n = susunGrup().length;
            const tanpaSub = bfItems.some(it => !it.sub_dept_id);
            $('#bf_kat_ringkas').html(n ?
                `<span class="krj"><i class="fas fa-shopping-basket"></i></span><b>${n}</b> barang di keranjang` +
                (tanpaSub ? ' <small>&middot; sub dept dipilih di daftar request</small>' : '') :
                '<span class="krj"><i class="fas fa-shopping-basket"></i></span>Keranjang masih kosong, klik <b>Tambah</b> pada barang');
            $('#bf_kat_selesai_n').text(n ? `(${n})` : '');
        }

        function goyangKeranjang() {
            const el = $('#bf_kat_ringkas .krj');
            el.removeClass('goyang');
            if (el[0]) void el[0].offsetWidth;
            el.addClass('goyang');
        }

        // Masukkan ke keranjang dengan qty 1
        function tambahKeranjang(kartu) {
            const b = katData[Number(kartu.data('id'))];
            if (!b || !b.unit_id || !b.satuan) return;
            const uang = mataUangAwal();
            if (!uang.currency_id) {
                Swal.fire({ toast: true, position: 'top-end', icon: 'warning', title: 'Master mata uang kosong', timer: 2000, showConfirmButton: false });
                return;
            }
            // Sub dept dipilih di daftar item (otomatis kalau departemen cuma punya 1 sub dept)
            const subDeptId = subDeptOtomatis();
            tambahItem({
                item_id: b.id,
                kode: b.kode,
                nama: b.nama,
                varian: b.varian,
                qty: 1,
                unit_id: b.unit_id,
                satuan: b.satuan,
                currency_id: uang.currency_id,
                mata_uang: uang.mata_uang,
                harga: null,
                tgl_butuh: tglBaru(),
                sub_dept_id: subDeptId,
                sub_dept: namaSubDeptJs(subDeptId),
                ket: '',
            });
            goyangKeranjang();
        }

        // Tombol - / + di kartu (hanya untuk barang yang ada 1 baris di keranjang)
        function ubahKeranjang(id, n) {
            const baris = barisKeranjang(id);
            if (baris.length !== 1) return;
            aturQtyKeranjang(id, (bfItems[baris[0]].qty || 0) + n, true);
        }

        // qty <= 0 (dan sudah selesai mengetik) = keluarkan dari keranjang
        function aturQtyKeranjang(id, qty, selesai) {
            const baris = barisKeranjang(id);
            if (baris.length !== 1) return;
            if (qty > 0) {
                bfItems[baris[0]].qty = qty;
            } else if (selesai) {
                bfItems.splice(baris[0], 1);
            } else {
                return;
            }
            renderBfItems();
            segarkanTandaKatalog();
        }

        // Gambar dimuat saat kartu terlihat, paling banyak 2 sekaligus (server lokal rawan error kalau banyak request bersamaan)
        let gbObs = null;
        const gbAntre = [];
        let gbJalan = 0;

        function pasangGambar() {
            const wadah = document.getElementById('bf_katalog');
            if (!wadah) return;
            if (!gbObs) {
                gbObs = new IntersectionObserver(entri => entri.forEach(e => {
                    if (!e.isIntersecting) return;
                    gbObs.unobserve(e.target);
                    gbAntre.push(e.target);
                    jalankanGambar();
                }), { root: wadah, rootMargin: '200px' });
            }
            wadah.querySelectorAll('img[data-src]:not([data-amati])').forEach(img => {
                img.dataset.amati = '1';
                gbObs.observe(img);
            });
        }

        function jalankanGambar() {
            while (gbJalan < 2 && gbAntre.length) {
                const img = gbAntre.shift();
                if (!img.isConnected) continue;
                gbJalan++;
                let coba = 0;
                const selesai = () => {
                    gbJalan--;
                    jalankanGambar();
                };
                img.onload = () => {
                    img.classList.add('ok');
                    selesai();
                };
                img.onerror = () => {
                    if (coba < 1) {
                        coba++;
                        setTimeout(() => { img.src = img.dataset.src + '&r=1'; }, 500);
                        return;
                    }
                    img.remove();
                    selesai();
                };
                img.src = img.dataset.src;
            }
        }

        // mode: null = baru, 'ubah' = ubah Draft, 'ulang' = request ulang (salin item ke request baru)
        function bukaFormRequest(index = null, mode = null) {
            resetEntry();
            bfId = null;
            $('#bf_cari').val('');
            // Catatan header ikut disalin (ubah Draft / request ulang), request baru kosong
            $('#bf_catatan').val(index !== null ? (requests[index].catatan || '') : '');
            // Approver ikut disalin; request baru memakai approver terakhir yang dipilih user ini
            [['#bf_approver', 'approver', 'gr_approver'], ['#bf_approver2', 'approver2', 'gr_approver2']].forEach(([sel, kunci, simpan]) => {
                const awal = index !== null ? (requests[index][kunci] || '') : (localStorage.getItem(simpan) || '');
                $(sel).val($(`${sel} option[value="${CSS.escape(awal)}"]`).length ? awal : '').trigger('change');
            });
            $('#bf_filter_tgl').val('');

            if (index !== null) {
                const req = requests[index];
                // Request ulang: tgl lama sudah lewat, diganti default baru
                const tglUlang = tglTambah(tglHariIni, 7);
                bfItems = req.items.map(it => ({
                    item_id: it.item_id,
                    kode: it.kode,
                    nama: it.nama,
                    qty: it.qty,
                    unit_id: it.unit_id,
                    satuan: it.satuan,
                    currency_id: it.currency_id,
                    mata_uang: it.mata_uang,
                    harga: it.harga,
                    // Ubah Draft: tgl lama dipertahankan; kalau sudah lewat ditandai merah supaya direvisi
                    tgl_butuh: mode === 'ubah' ? (it.tgl_butuh || tglUlang) : tglUlang,
                    // Sub dept lama (Draft sebelum ada sub dept = kosong, wajib dipilih sebelum simpan)
                    sub_dept_id: it.sub_dept_id || '',
                    sub_dept: it.sub_dept || '',
                    ket: it.ket ?? '',
                }));
                // Departemen lama dipakai kalau masih ada di Role Access user ini & sudah punya alias;
                // kalau tidak, pakai satu-satunya (jika cuma 1). Ubah Draft: departemen dikunci (nomor sudah memakai aliasnya)
                if (mode === 'ubah') bfId = req.id;
                const opsiDept = $('#bf_departemen option[value!=""]' + (mode === 'ubah' ? '' : ':not(:disabled)'));
                const deptLama = opsiDept.filter((_, o) => o.value === String(req.departemen_id)).val();
                $('#bf_departemen').prop('disabled', mode === 'ubah')
                    .val(deptLama || (opsiDept.length === 1 ? opsiDept.val() : '')).trigger('change');

                if (mode === 'ubah') {
                    $('#bf_label').text('Ubah Draft');
                    $('#bf_no').text(req.no_request);
                    $('#bf_tgl_request').val(req.tgl_request);
                    aturTglButuh();
                    $('#bf_sub').text('Ubah item lalu simpan lagi sebagai Draft atau langsung ajukan');
                } else {
                    $('#bf_label').text('Request Ulang');
                    $('#bf_tgl_request').val(tglHariIni);
                    aturTglButuh();
                    $('#bf_sub').text(`Disalin dari ${req.no_request}, silakan sesuaikan item, qty & tgl dibutuhkan`);
                }
            } else {
                bfItems = [];
                // Kalau user hanya punya 1 departemen (yang sudah ada alias), langsung terpilih; nomor ikut berubah
                const opsiDept = $('#bf_departemen option[value!=""]:not(:disabled)');
                $('#bf_departemen').prop('disabled', false).val(opsiDept.length === 1 ? opsiDept.val() : '').trigger(
                    'change');
                $('#bf_label').text('Request Baru');
                $('#bf_tgl_request').val(tglHariIni);
                aturTglButuh();
                $('#bf_sub').text('Isi kebutuhan barang, lalu ajukan untuk approval');
            }

            rapikanItem();
            renderBfItems();
            // Katalog tertutup dulu: departemen dipilih di form, lalu klik Tambah Item
            tutupPanelItem();
            $('#modalBuat').modal('show');
        }

        function bukaPanelItem(fokus = true) {
            // Departemen harus dipilih dulu: sub dept (wajib per item) mengikuti departemen
            if (!$('#bf_departemen').val()) {
                Swal.fire({ toast: true, position: 'top-end', icon: 'info', title: 'Pilih departemen dulu', timer: 1800, showConfirmButton: false });
                $('#bf_departemen').select2('open');
                return;
            }
            $('#bf_panel').addClass('buka').attr('aria-hidden', 'false');
            $('#modalBuat').addClass('panel-buka');
            $('#bf_kat_no').text('untuk ' + $('#bf_no').text().trim());
            // Katalog dimuat saat pertama kali dibuka; berikutnya cukup tandai ulang
            segarkanTandaKatalog();
            if (katKunci !== kunciKatalog()) muatKatalog(true);
            // Tunggu animasi selesai baru kolom cari difokuskan
            if (fokus) setTimeout(() => $('#bf_kat_cari').trigger('focus').trigger('select'), 330);
        }

        function tutupPanelItem() {
            $('#bf_panel').removeClass('buka').attr('aria-hidden', 'true');
            $('#modalBuat').removeClass('panel-buka');
        }

        // ESC menutup modal paling atas. Dicek di fase capture supaya ESC yang menutup
        // dropdown select2 / SweetAlert tidak ikut menutup modal. Form Request lewat
        // tutupFormRequest() agar tetap ada konfirmasi kalau sudah ada item.
        const grModalTumpuk = [];
        $(document).on('shown.bs.modal', '.modal', function() {
            const i = grModalTumpuk.indexOf(this);
            if (i > -1) grModalTumpuk.splice(i, 1);
            grModalTumpuk.push(this);
        });
        $(document).on('hidden.bs.modal', '.modal', function() {
            const i = grModalTumpuk.indexOf(this);
            if (i > -1) grModalTumpuk.splice(i, 1);
        });
        document.addEventListener('keydown', e => {
            if (e.key !== 'Escape' || e.repeat) return;
            if (window.Swal && Swal.isVisible()) return;
            if (document.querySelector('.select2-container--open')) return;
            const atas = grModalTumpuk.filter(m => $(m).hasClass('show')).pop();
            if (!atas) return;
            e.preventDefault();
            e.stopPropagation();
            // Form Request: ESC menutup panel tambah item dulu, baru form-nya
            if (atas.id === 'modalBuat' && $('#bf_panel').hasClass('buka')) tutupPanelItem();
            else if (atas.id === 'modalBuat') tutupFormRequest();
            else $(atas).modal('hide');
        }, true);

        function tutupFormRequest() {
            if (bfMenyimpan) return;
            if (bfItems.length === 0) {
                $('#modalBuat').modal('hide');
                return;
            }
            Swal.fire({
                icon: 'question',
                title: 'Tutup form?',
                text: 'Perubahan yang belum disimpan akan hilang.',
                showCancelButton: true,
                confirmButtonText: 'Tutup',
                cancelButtonText: 'Lanjut isi',
                confirmButtonColor: '#c0505f',
            }).then(r => {
                if (r.isConfirmed) $('#modalBuat').modal('hide');
            });
        }

        function shake(el) {
            el.removeClass('gr-shake');
            void el[0].offsetWidth;
            el.addClass('gr-shake');
        }

        function formatAngka(n) {
            return Number(n).toLocaleString('id-ID', {
                maximumFractionDigits: 2
            });
        }

        /*
         * bfItems tetap datar: 1 baris = 1 barang untuk 1 sub dept (sama dengan yang disimpan ke server).
         * Tampilan dikelompokkan per barang + satuan + tgl butuh: nama barang, tgl & harga tampil sekali,
         * di bawahnya 1 baris per sub dept (qty & keterangan sendiri).
         */
        function kunciGrup(it) {
            return `${it.item_id}|${it.unit_id}|${it.tgl_butuh || ''}`;
        }

        // [{g, idx: [index bfItems]}] urut kemunculan pertama
        function susunGrup() {
            const peta = new Map();
            bfItems.forEach((it, i) => {
                const g = kunciGrup(it);
                if (!peta.has(g)) peta.set(g, []);
                peta.get(g).push(i);
            });
            return [...peta].map(([g, idx]) => ({ g, idx }));
        }

        function anggotaGrup(g) {
            return bfItems.map((it, i) => kunciGrup(it) === g ? i : -1).filter(i => i >= 0);
        }

        function grupDari(el) {
            return String($(el).closest('.gr-bf-item').attr('data-g'));
        }

        // Baris lain dengan barang + satuan + tgl butuh + sub dept yang sama (kecuali index `kecuali`)
        function cariKembar(it, kecuali = -1) {
            return bfItems.findIndex((x, j) => j !== kecuali && x.item_id === it.item_id && x.unit_id === it.unit_id &&
                x.tgl_butuh === it.tgl_butuh && (x.sub_dept_id || '') === (it.sub_dept_id || ''));
        }

        // Rapikan: baris kembar (sub dept sama) digabung, harga & mata uang 1 kelompok disamakan. Hasil: jumlah yang digabung
        function rapikanItem() {
            let digabung = 0;
            for (let i = 0; i < bfItems.length; i++) {
                for (let j = bfItems.length - 1; j > i; j--) {
                    if (cariKembar(bfItems[j], j) !== i) continue;
                    const a = bfItems[i], b = bfItems[j];
                    a.qty = ((a.qty || 0) + (b.qty || 0)) || null;
                    if (b.ket && a.ket !== b.ket) a.ket = [a.ket, b.ket].filter(Boolean).join('; ');
                    if (a.harga === null || a.harga === undefined) a.harga = b.harga;
                    bfItems.splice(j, 1);
                    digabung++;
                }
            }
            susunGrup().forEach(({ idx }) => {
                const acuan = bfItems[idx.find(i => bfItems[i].harga !== null && bfItems[i].harga !== undefined) ?? idx[0]];
                idx.forEach(i => {
                    bfItems[i].harga = acuan.harga ?? null;
                    bfItems[i].currency_id = acuan.currency_id;
                    bfItems[i].mata_uang = acuan.mata_uang;
                });
            });
            return digabung;
        }

        function tambahItem(baru) {
            // Filter daftar dikosongkan supaya item yang baru ditambah langsung kelihatan
            $('#bf_cari').val('');
            $('#bf_filter_tgl').val('');
            const g = kunciGrup(baru);
            const grup = anggotaGrup(g);
            // Ikut harga & mata uang kelompok barang yang sudah ada
            if (grup.length) {
                const acuan = bfItems[grup[0]];
                baru.harga = acuan.harga;
                baru.currency_id = acuan.currency_id;
                baru.mata_uang = acuan.mata_uang;
            }
            // Barang + satuan + tgl dibutuhkan + sub dept sama -> qty digabung.
            // Dari katalog (tanpa sub dept) ke barang yang baru 1 sub dept -> qty masuk ke sub dept itu
            let ada = cariKembar(baru);
            if (ada < 0 && !baru.sub_dept_id && grup.length === 1) ada = grup[0];
            if (ada >= 0) {
                bfItems[ada].qty = (bfItems[ada].qty || 0) + baru.qty;
                renderBfItems(g, 'flash');
                Swal.fire({ toast: true, position: 'top-end', icon: 'info', title: `Qty ${baru.nama} digabung`, timer: 1500, showConfirmButton: false });
            } else if (grup.length) {
                // Barang sama untuk sub dept lain: masuk ke kelompoknya
                bfItems.splice(grup[grup.length - 1] + 1, 0, baru);
                renderBfItems(g, 'flash');
            } else {
                bfItems.push(baru);
                renderBfItems(g, 'baru');
            }
            // Gulir daftar ke item yang baru masuk
            const el = $(`#bf_items .gr-bf-item[data-g="${g}"]`)[0];
            if (el) el.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
            segarkanTandaKatalog();
        }

        // Sub dept ditambah di kelompok: isi baris yang sub dept-nya masih kosong, atau pecah jadi baris baru (qty kosong, wajib diisi)
        function tambahSubDept(g, id) {
            const grup = anggotaGrup(g);
            if (!grup.length || grup.some(i => bfItems[i].sub_dept_id === id)) return;
            const kosong = grup.find(i => !bfItems[i].sub_dept_id);
            let fokus;
            if (kosong !== undefined) {
                bfItems[kosong].sub_dept_id = id;
                bfItems[kosong].sub_dept = namaSubDeptJs(id);
                fokus = kosong;
            } else {
                fokus = grup[grup.length - 1] + 1;
                bfItems.splice(fokus, 0, { ...bfItems[grup[0]], sub_dept_id: id, sub_dept: namaSubDeptJs(id), qty: null, ket: '' });
            }
            renderBfItems();
            const q = $(`#bf_items .q[data-i="${fokus}"]`);
            if (!q.val()) q.trigger('focus');
        }

        // Sub dept dilepas dari kelompok: barisnya hilang (kalau tinggal 1, sub dept-nya dikosongkan)
        function lepasSubDept(g, id) {
            const grup = anggotaGrup(g);
            const i = grup.find(j => bfItems[j].sub_dept_id === id);
            if (i === undefined) return;
            if (grup.length === 1) {
                bfItems[i].sub_dept_id = '';
                bfItems[i].sub_dept = '';
                renderBfItems();
                return;
            }
            hapusBaris(i);
        }

        function hapusBaris(i) {
            const grup = anggotaGrup(kunciGrup(bfItems[i]));
            if (grup.length === 1) return hapusGrup($(`#bf_items .q[data-i="${i}"]`)[0]);
            $(`#bf_items .anak[data-i="${i}"]`).addClass('keluar');
            setTimeout(() => {
                bfItems.splice(i, 1);
                renderBfItems();
                segarkanTandaKatalog();
            }, 220);
        }

        function hapusGrup(el) {
            const g = grupDari(el);
            $(`#bf_items .gr-bf-item[data-g="${g}"]`).addClass('keluar');
            setTimeout(() => {
                bfItems = bfItems.filter(it => kunciGrup(it) !== g);
                renderBfItems();
                segarkanTandaKatalog();
            }, 260);
        }

        // Qty boleh kosong sementara (ditandai merah, menahan simpan)
        function ubahQty(i, el) {
            el.value = el.value.replace(/[^0-9]/g, '');
            const v = parseInt(el.value || 0);
            bfItems[i].qty = v > 0 ? v : null;
            $(el).closest('.qwrap').toggleClass('kosong', !bfItems[i].qty);
            perbaruiGrup(kunciGrup(bfItems[i]));
            segarkanTandaKatalog();
        }

        // Harga perkiraan berlaku untuk semua sub dept di kelompok
        function ubahHarga(el) {
            const g = grupDari(el);
            const t = el.value.trim();
            const v = t === '' ? null : parseFloat(t);
            const grup = anggotaGrup(g);
            if (v !== null && (isNaN(v) || v < 0)) {
                el.value = bfItems[grup[0]].harga ?? '';
                return;
            }
            grup.forEach(i => bfItems[i].harga = v);
            perbaruiGrup(g);
        }

        function ubahMataUang(el) {
            const g = grupDari(el);
            const kode = $(el).find('option:selected').text().trim();
            anggotaGrup(g).forEach(i => {
                bfItems[i].currency_id = Number(el.value);
                bfItems[i].mata_uang = kode;
            });
            perbaruiGrup(g);
        }

        function ubahKet(i, el) {
            bfItems[i].ket = el.value.trim();
        }

        // Sisa hari dari hari ini: lewat = merah (wajib direvisi), <= 3 hari = oranye
        function infoTglButuh(tgl) {
            if (!tgl) return { kelas: 'lewat', teksHari: 'Belum diisi', singkat: '?' };
            const hari = Math.round((new Date(tgl + 'T00:00:00') - new Date(tglHariIni + 'T00:00:00')) / 86400000);
            if (hari < 0) return { kelas: 'lewat', teksHari: 'Sudah lewat, wajib direvisi', singkat: 'Lewat' };
            return {
                kelas: hari <= 3 ? 'soon' : 'ok',
                teksHari: hari === 0 ? 'Dibutuhkan hari ini' : `${hari} hari lagi`,
                singkat: hari === 0 ? 'Hari H' : `H-${hari}`,
            };
        }

        // Revisi tgl dibutuhkan 1 kelompok; kalau jadi sama dengan kelompok lain (barang & satuan sama) digabung
        function ubahTgl(el) {
            const g = grupDari(el);
            const grup = anggotaGrup(g);
            const v = el.value;
            if (!v || v < tglHariIni) {
                el.value = bfItems[grup[0]].tgl_butuh || '';
                Swal.fire({ toast: true, position: 'top-end', icon: 'warning', title: 'Tgl dibutuhkan tidak boleh sebelum hari ini', timer: 2000, showConfirmButton: false });
                return;
            }
            tglTerakhir = v;
            const gBaru = kunciGrup({ ...bfItems[grup[0]], tgl_butuh: v });
            const adaLain = bfItems.some((x, j) => !grup.includes(j) && kunciGrup(x) === gBaru);
            grup.forEach(i => bfItems[i].tgl_butuh = v);
            if (adaLain) {
                rapikanItem();
                renderBfItems(gBaru, 'flash');
                Swal.fire({ toast: true, position: 'top-end', icon: 'info', title: `${bfItems[anggotaGrup(gBaru)[0]].nama} digabung ke baris yang sama`, timer: 1800, showConfirmButton: false });
                segarkanTandaKatalog();
                return;
            }
            const { kelas, teksHari, singkat } = infoTglButuh(v);
            $(el).closest('.gr-bf-item').attr('data-g', gBaru).find('.tgl').first().removeClass('ok soon lewat').addClass(kelas)
                .attr('title', `${teksHari} · bisa direvisi, paling cepat hari ini`).find('.hr').text(singkat);
            isiFilterTgl();
        }

        // Pilihan filter tgl = tgl dibutuhkan yang ada di daftar item (jumlah = jumlah barang)
        function isiFilterTgl() {
            const tglPilih = $('#bf_filter_tgl').val();
            const grup = susunGrup().map(({ idx }) => bfItems[idx[0]].tgl_butuh).filter(Boolean);
            const daftarTgl = [...new Set(grup)].sort();
            $('#bf_filter_tgl').html('<option value="">Semua tgl butuh</option>' +
                    daftarTgl.map(t => `<option value="${t}">${formatTgl(t)} (${grup.filter(x => x === t).length})</option>`).join(''))
                .val(daftarTgl.includes(tglPilih) ? tglPilih : '').trigger('change.select2');
        }

        function opsiSubDeptMulti(dipilih) {
            return daftarSubDept().map(s =>
                `<option value="${escapeHtml(s.id)}" ${dipilih.includes(s.id) ? 'selected' : ''}>${escapeHtml(s.nama)}</option>`).join('');
        }

        function inputQty(it, i) {
            return `
                <div class="qwrap ${it.qty ? '' : 'kosong'}">
                    <input type="text" inputmode="numeric" class="q" data-i="${i}" value="${it.qty ?? ''}" placeholder="0" oninput="ubahQty(${i}, this)">
                    <div class="st">${escapeHtml(it.satuan)}</div>
                </div>`;
        }

        function inputKet(it, i) {
            return `
                <div class="ktw">
                    <i class="fas fa-pen"></i>
                    <input type="text" class="kt" placeholder="Spesifikasi / keterangan" value="${escapeHtml(it.ket || '')}" onchange="ubahKet(${i}, this)">
                </div>`;
        }

        function renderBfItems(sorot = null, efek = '') {
            // select2 lama dibuang dulu supaya dropdown-nya tidak tertinggal
            $('#bf_items select.sd, #bf_items select.mu').each(function() {
                if ($(this).data('select2')) $(this).select2('destroy');
            });
            isiFilterTgl();

            const q = $('#bf_cari').val().trim().toLowerCase();
            const tgl = $('#bf_filter_tgl').val();
            const semua = susunGrup();
            // Kelompok tampil kalau salah satu sub dept-nya cocok dengan kata kunci
            const tampil = semua.map((gr, n) => ({ ...gr, n })).filter(({ idx }) =>
                (!tgl || bfItems[idx[0]].tgl_butuh === tgl) &&
                (!q || idx.some(i => [bfItems[i].nama, bfItems[i].kode, bfItems[i].ket, bfItems[i].sub_dept].join(' ').toLowerCase().includes(q))));

            const cnt = $('#bf_count');
            cnt.text(tampil.length !== semua.length ? `${tampil.length} / ${semua.length}` : semua.length).addClass('bump');
            setTimeout(() => cnt.removeClass('bump'), 250);

            // Bingkai tabel hanya kalau ada baris
            $('#bf_items').toggleClass('kosong', !tampil.length);
            if (bfItems.length === 0) {
                $('#bf_items').html(
                    `<div class="gr-bf-empty"><i class="fas fa-box-open"></i>Belum ada item<br><small>Klik <b>Tambah Item</b> untuk menambahkan barang</small></div>`
                );
                return;
            }
            if (!tampil.length) {
                $('#bf_items').html(
                    `<div class="gr-bf-empty"><i class="fas fa-search"></i>Tidak ada item yang cocok<br><small>Ubah kata kunci atau filter tgl</small></div>`
                );
                return;
            }

            const kepala = `
            <div class="gr-bf-head">
                <div>#</div><div>Barang</div><div>Tgl butuh</div><div class="text-right">Qty</div><div>Harga perkiraan</div><div class="text-right">Subtotal</div><div></div>
            </div>`;
            $('#bf_items').html(kepala + tampil.map(({ g, idx, n }) => {
                const it = bfItems[idx[0]];
                const pecah = idx.length > 1;
                const info = [it.kode, it.varian || null].filter(Boolean).join(' · ');
                const { kelas, teksHari, singkat } = infoTglButuh(it.tgl_butuh);
                const subDipilih = idx.map(i => bfItems[i].sub_dept_id).filter(Boolean);
                const anak = pecah ? `
                <div class="anak-wrap">${idx.map(i => {
                    const a = bfItems[i];
                    return `
                    <div class="anak" data-i="${i}">
                        <div class="cab"></div>
                        <div class="anm">
                            <span class="sdn ${a.sub_dept_id ? '' : 'kosong'}" title="${escapeHtml(a.sub_dept || 'Sub dept belum dipilih')}"><i class="fas fa-sitemap"></i> ${a.sub_dept ? sorotTeks(a.sub_dept, q) : 'Pilih sub dept'}</span>
                            ${inputKet(a, i)}
                        </div>
                        ${inputQty(a, i)}
                        <div></div>
                        <div class="sub ${a.harga === null ? 'kosong' : ''}">${teksSubtotal(a)}</div>
                        <button type="button" class="del kecil" onclick="hapusBaris(${i})" title="Hapus sub dept ini"><i class="fas fa-times"></i></button>
                    </div>`;
                }).join('')}
                </div>` : '';
                return `
            <div class="gr-bf-item ${pecah ? 'pecah' : ''} ${g === sorot ? efek : ''} ${q ? 'cocok' : ''}" data-g="${g}">
                <div class="no">${String(n + 1).padStart(2, '0')}</div>
                <div class="nm" title="${escapeHtml(it.nama)}">${sorotTeks(it.nama, q)}<small>${sorotTeks(info || '-', q)}</small></div>
                <div class="tgl ${kelas}" title="${teksHari} · bisa direvisi, paling cepat hari ini">
                    <input type="date" class="tg" value="${escapeHtml(it.tgl_butuh || '')}" min="${tglHariIni}" onchange="ubahTgl(this)">
                    <span class="hr">${singkat}</span>
                </div>
                ${pecah
                    ? `<div class="qtot" title="Total qty semua sub dept"><b>${formatAngka(idx.reduce((s, i) => s + (bfItems[i].qty || 0), 0))}</b><span>${escapeHtml(it.satuan)}</span></div>`
                    : inputQty(it, idx[0])}
                <div class="hg" title="Harga perkiraan per ${escapeHtml(it.satuan)} (opsional, berlaku untuk semua sub dept)">
                    <select class="mu" title="Ganti mata uang">${daftarMataUang.map(c =>
                        `<option value="${c.id}" ${String(c.id) === String(it.currency_id) ? 'selected' : ''}>${escapeHtml(c.kode)}</option>`).join('')}</select>
                    <input type="text" inputmode="decimal" placeholder="opsional" value="${it.harga ?? ''}"
                        oninput="this.value = this.value.replace(/[^0-9.]/g, ''); ubahHarga(this)" onchange="ubahHarga(this)">
                </div>
                <div class="sub ${it.harga === null ? 'kosong' : ''}">${teksSubtotalGrup(idx)}</div>
                <button type="button" class="del" onclick="hapusGrup(this)" title="Hapus barang ini"><i class="fas fa-trash-alt"></i></button>
                <div class="brs2">
                    <div class="sdw ${subDipilih.length ? '' : 'kosong'}" title="Sub dept (wajib). Pilih lebih dari 1 untuk memecah qty per sub dept">
                        <i class="fas fa-sitemap"></i>
                        <select class="sd" multiple>${opsiSubDeptMulti(subDipilih)}</select>
                    </div>
                    ${pecah ? `<div class="info-pecah"><i class="fas fa-code-branch"></i> Dipecah ke ${idx.length} sub dept</div>` : inputKet(it, idx[0])}
                </div>
                ${anak}
            </div>`;
            }).join('') + `<div class="gr-bf-total" id="bf_total"></div>`);

            // Sub dept per barang: select2 multiple; pilih lebih dari 1 = baris dipecah per sub dept
            $('#bf_items select.sd').select2({
                theme: 'bootstrap4',
                width: '100%',
                dropdownParent: $('#modalBuat'),
                placeholder: 'Pilih sub dept',
                closeOnSelect: true,
            }).on('select2:select', function(e) {
                const g = grupDari(this), id = e.params.data.id;
                setTimeout(() => tambahSubDept(g, id), 0);
            }).on('select2:unselect', function(e) {
                const g = grupDari(this), id = e.params.data.id;
                // Lepas chip jangan sampai membuka dropdown
                $(this).one('select2:opening', ev => ev.preventDefault());
                setTimeout(() => lepasSubDept(g, id), 0);
            });
            // Mata uang per barang (berlaku untuk semua sub dept-nya)
            $('#bf_items select.mu').select2({
                theme: 'bootstrap4',
                width: 'style',
                dropdownParent: $('#modalBuat'),
                dropdownAutoWidth: true,
                minimumResultsForSearch: 8,
            }).on('change', function() { ubahMataUang(this); });
            // Petunjuk "+ Sub dept lain" di sebelah chip, hanya kalau masih ada sub dept yang bisa dipilih
            $('#bf_items select.sd').each(function() {
                const sisa = $(this).find('option:not(:selected)').length;
                const adaPilihan = $(this).find('option:selected').length > 0;
                $(this).next('.select2-container').toggleClass('ada-sisa', sisa > 0 && adaPilihan)
                    .find('.select2-search__field').attr('placeholder', adaPilihan ? (sisa ? '+ Sub dept lain' : '') : 'Pilih sub dept');
            });
            renderTotal();
        }

        function teksSubtotal(it) {
            if (it.harga === null || it.harga === undefined) return '—';
            return `${formatAngka((it.qty || 0) * it.harga)}<small>${escapeHtml(it.mata_uang || '')}</small>`;
        }

        function teksSubtotalGrup(idx) {
            const it = bfItems[idx[0]];
            if (it.harga === null || it.harga === undefined) return '—';
            const qty = idx.reduce((s, i) => s + (bfItems[i].qty || 0), 0);
            return `${formatAngka(qty * it.harga)}<small>${escapeHtml(it.mata_uang || '')}</small>`;
        }

        // Subtotal 1 kelompok + total diperbarui tanpa render ulang (supaya fokus input tidak hilang)
        function perbaruiGrup(g) {
            const idx = anggotaGrup(g);
            const el = $(`#bf_items .gr-bf-item[data-g="${g}"]`);
            if (!idx.length || !el.length) return renderTotal();
            const kosong = bfItems[idx[0]].harga === null;
            el.children('.sub').toggleClass('kosong', kosong).html(teksSubtotalGrup(idx));
            el.find('.qtot b').text(formatAngka(idx.reduce((s, i) => s + (bfItems[i].qty || 0), 0)));
            idx.forEach(i => el.find(`.anak[data-i="${i}"] .sub`).toggleClass('kosong', kosong).html(teksSubtotal(bfItems[i])));
            renderTotal();
        }

        // Ringkasan ala nota: selalu dari SEMUA item (bukan hanya yang tampil karena filter)
        function renderTotal() {
            const el = $('#bf_total');
            if (!el.length) return;

            const perUang = {}; // mata uang -> total
            const perSatuan = {}; // satuan -> total qty
            bfItems.forEach(it => {
                perSatuan[it.satuan] = (perSatuan[it.satuan] || 0) + (it.qty || 0);
                if (it.harga === null || it.harga === undefined) return;
                const u = it.mata_uang || '-';
                perUang[u] = (perUang[u] || 0) + (it.qty || 0) * it.harga;
            });
            const grup = susunGrup();
            const tanpaHarga = grup.filter(({ idx }) => bfItems[idx[0]].harga === null || bfItems[idx[0]].harga === undefined).length;

            const uang = Object.entries(perUang);
            const qtySatuan = Object.entries(perSatuan).map(([s, n]) => `<b>${formatAngka(n)}</b> ${escapeHtml(s)}`).join(' · ');
            const filter = $('#bf_cari').val().trim() || $('#bf_filter_tgl').val();

            el.html(`
            <div class="kiri">
                <div>Jumlah item: <b>${grup.length}</b>${bfItems.length !== grup.length ? ` <small>(${bfItems.length} baris sub dept)</small>` : ''}${filter ? ' <small>(termasuk yang tersembunyi oleh filter)</small>' : ''}</div>
                <div>Total qty: ${qtySatuan || '-'}</div>
                ${tanpaHarga ? `<div class="tanpa"><i class="fas fa-exclamation-circle"></i> ${tanpaHarga} item belum diisi harga perkiraan</div>` : ''}
            </div>
            <div class="nota">
                ${uang.length > 1 ? uang.map(([u, n]) => `<div class="brs"><span>Subtotal ${escapeHtml(u)}</span><span>${formatAngka(n)}</span></div>`).join('') : ''}
                ${uang.length
                    ? uang.map(([u, n]) => `<div class="brs grand"><span>Total perkiraan</span><span>${escapeHtml(u)} ${formatAngka(n)}</span></div>`).join('')
                    : '<div class="brs grand"><span>Total perkiraan</span><span>—</span></div>'}
                <div class="ket">Harga perkiraan, bukan harga final PO</div>
            </div>`);
        }

        // Tandai kata yang dicari (teks tetap di-escape)
        function sorotTeks(teks, q) {
            teks = String(teks ?? '');
            if (!q) return escapeHtml(teks);
            const pos = teks.toLowerCase().indexOf(q);
            if (pos < 0) return escapeHtml(teks);
            return escapeHtml(teks.slice(0, pos)) + '<mark>' + escapeHtml(teks.slice(pos, pos + q.length)) + '</mark>' +
                escapeHtml(teks.slice(pos + q.length));
        }

        // mode: 'draft' | 'ajukan'
        function simpanRequest(mode) {
            if (bfMenyimpan) return;
            if (mode === 'ajukan' && !$('#bf_approver').val()) {
                Swal.fire({ toast: true, position: 'top-end', icon: 'warning', title: 'Pilih approver dulu', timer: 1800, showConfirmButton: false });
                $('#bf_approver').select2('open');
                return;
            }
            if ($('#bf_approver2').val() && $('#bf_approver2').val() === $('#bf_approver').val()) {
                Swal.fire({ toast: true, position: 'top-end', icon: 'warning', title: 'Approver 2 tidak boleh sama dengan approver 1', timer: 2000, showConfirmButton: false });
                $('#bf_approver2').select2('open');
                return;
            }
            if ($('#bf_approver').val()) localStorage.setItem('gr_approver', $('#bf_approver').val());
            localStorage.setItem('gr_approver2', $('#bf_approver2').val() || '');
            if (!$('#bf_departemen').val()) {
                $('#bf_departemen').select2('open');
                return;
            }
            if (bfItems.length === 0) {
                Swal.fire('Belum ada item', 'Tambahkan minimal 1 item.', 'warning').then(() => bukaPanelItem());
                return;
            }
            // Tgl dibutuhkan yang sudah lewat (mis. Draft lama) harus direvisi dulu
            const lewat = bfItems.map((it, i) => ({ it, i })).filter(({ it }) => !it.tgl_butuh || it.tgl_butuh < tglHariIni);
            if (lewat.length) {
                $('#bf_cari').val('');
                $('#bf_filter_tgl').val('');
                renderBfItems();
                const pertama = $(`#bf_items .gr-bf-item[data-g="${kunciGrup(lewat[0].it)}"]`);
                if (pertama.length) {
                    pertama[0].scrollIntoView({ block: 'center', behavior: 'smooth' });
                    shake(pertama);
                    pertama.find('.tg').trigger('focus');
                }
                Swal.fire({
                    icon: 'warning',
                    title: 'Revisi tgl dibutuhkan',
                    html: `${lewat.length} item punya tgl dibutuhkan sebelum hari ini:<br><b>${lewat.map(({ it }) => escapeHtml(it.nama)).join(', ')}</b>`,
                    confirmButtonColor: '#4a6fa5',
                });
                return;
            }
            // Sub dept wajib per item
            const tanpaSub = bfItems.map((it, i) => ({ it, i })).filter(({ it }) => !it.sub_dept_id);
            if (tanpaSub.length) {
                $('#bf_cari').val('');
                $('#bf_filter_tgl').val('');
                renderBfItems();
                const pertama = $(`#bf_items .gr-bf-item[data-g="${kunciGrup(tanpaSub[0].it)}"]`);
                if (pertama.length) {
                    pertama[0].scrollIntoView({ block: 'center', behavior: 'smooth' });
                    shake(pertama);
                }
                Swal.fire({
                    icon: 'warning',
                    title: 'Pilih sub dept',
                    html: `${tanpaSub.length} item belum dipilih sub dept-nya:<br><b>${tanpaSub.map(({ it }) => escapeHtml(it.nama)).join(', ')}</b>`,
                    confirmButtonColor: '#4a6fa5',
                }).then(() => { if (pertama.length) pertama.find('select.sd').select2('open'); });
                return;
            }
            // Qty wajib diisi per sub dept (sub dept yang tidak jadi beli dihapus dari barangnya)
            const tanpaQty = bfItems.map((it, i) => ({ it, i })).filter(({ it }) => !(it.qty > 0));
            if (tanpaQty.length) {
                $('#bf_cari').val('');
                $('#bf_filter_tgl').val('');
                renderBfItems();
                const q = $(`#bf_items .q[data-i="${tanpaQty[0].i}"]`);
                const baris = q.closest('.anak').length ? q.closest('.anak') : q.closest('.gr-bf-item');
                if (q.length) {
                    baris[0].scrollIntoView({ block: 'center', behavior: 'smooth' });
                    shake(baris);
                }
                Swal.fire({
                    icon: 'warning',
                    title: 'Isi qty',
                    html: `${tanpaQty.length} baris belum diisi qty:<br><b>${tanpaQty.map(({ it }) => escapeHtml(it.nama) + (it.sub_dept ? ' (' + escapeHtml(it.sub_dept) + ')' : '')).join(', ')}</b><br><small class="text-muted">Kalau sub dept tidak jadi beli, hapus sub dept tersebut.</small>`,
                    confirmButtonColor: '#4a6fa5',
                }).then(() => q.trigger('focus'));
                return;
            }

            const data = {
                id: bfId,
                mode,
                department_id: $('#bf_departemen').val(),
                catatan: $('#bf_catatan').val().trim(),
                approver: $('#bf_approver').val(),
                approver2: $('#bf_approver2').val(),
                items: bfItems.map(it => ({
                    item_id: it.item_id,
                    qty: it.qty,
                    unit_id: it.unit_id,
                    currency_id: it.currency_id,
                    harga: it.harga,
                    tgl_butuh: it.tgl_butuh,
                    sub_dept_id: it.sub_dept_id,
                    ket: it.ket
                })),
            };

            const proses = () => {
                bfMenyimpan = true;
                $('.bf-simpan').prop('disabled', true);
                return kirim(urlAksi.simpan, data)
                    .done(res => {
                        $('#modalBuat').modal('hide');
                        suksesLaluMuat(res.message,
                            `<b>${escapeHtml(res.no_request)}</b><br>Status: <b>${escapeHtml(res.status_request)}</b>`
                            );
                    })
                    .fail(pesanGagal)
                    .always(() => {
                        bfMenyimpan = false;
                        $('.bf-simpan').prop('disabled', false);
                    });
            };

            if (mode === 'draft') {
                proses();
                return;
            }

            const list = bfItems.map(it =>
                `<tr><td class="text-left">${escapeHtml(it.nama)}<br><small class="text-muted">${escapeHtml(it.sub_dept || '')}</small></td><td class="text-right text-nowrap">${it.qty} ${escapeHtml(it.satuan)}</td><td class="text-nowrap">${formatTgl(it.tgl_butuh)}</td></tr>`
                ).join('');
            Swal.fire({
                title: 'Ajukan Request?',
                html: `<div class="small text-left mb-2">
                    <b>Departemen:</b> ${escapeHtml($('#bf_departemen option:selected').text().trim())}<br>
                    <b>Approver:</b> ${['#bf_approver', '#bf_approver2'].filter(sel => $(sel).val()).map(sel => escapeHtml($(sel + ' option:selected').text().trim())).join(' &amp; ')}<br>
                    <small class="text-muted">Setelah diajukan, request tidak bisa diubah lagi.</small>
                </div>
                <table class="table table-sm table-bordered small mb-0">${list}</table>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-paper-plane"></i> Ajukan',
                cancelButtonText: 'Cek lagi',
                confirmButtonColor: '#4a6fa5',
            }).then(r => {
                if (r.isConfirmed) proses();
            });
        }
    </script>
@endsection
