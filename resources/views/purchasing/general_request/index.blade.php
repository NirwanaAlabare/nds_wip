@extends('layouts.index')

@section('custom-link')
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

    <style>
        /* ================= Palet kalem ================= */
        .gr-page, #modalTracking, #modalBuat {
            --gr-ink: #2f3a4a;
            --gr-muted: #8a94a6;
            --gr-line: #e8ecf2;
            --gr-soft: #f6f8fb;
            --gr-accent: #4a6fa5;
            --gr-accent-soft: #e9f0fa;
            color: var(--gr-ink);
        }

        /* ================= Latar cool (hanya halaman ini) ================= */
        body, .content-wrapper {
            background-color: #e9eff6 !important;
        }
        .content-wrapper {
            background-image:
                radial-gradient(circle at 8% 12%, rgba(134,165,211,.22) 0, transparent 38%),
                radial-gradient(circle at 92% 30%, rgba(124,195,155,.14) 0, transparent 35%),
                radial-gradient(circle at 50% 100%, rgba(138,124,194,.12) 0, transparent 45%),
                linear-gradient(160deg, #edf2f8 0%, #e6edf5 55%, #eaeef6 100%) !important;
            background-attachment: fixed !important;
        }
        .gr-page .card {
            background: rgba(247,249,252,.88);
            border: 1px solid #dde5ef;
            border-radius: 16px;
            box-shadow: 0 6px 24px rgba(63,95,143,.08);
            overflow: hidden;
        }
        .gr-page .card-header.bg-sb {
            background: linear-gradient(135deg, #3f5f8f 0%, #5b7fb5 60%, #7b9bcb 100%) !important;
            border: 0;
        }
        .gr-page .alert-warning { background: #f6efdc; border-color: #ecdfb9; color: #8a6d2b; border-radius: 12px; }
        .gr-page .gr-stat { background: #f9fbfd; border-color: #dde5ef; }
        .gr-page .gr-filter { background: #eef3f9; border-color: #dde5ef; }
        .gr-page .gr-filter .form-control,
        .gr-page .gr-filter .input-group-text,
        .gr-page .gr-filter .select2-container--bootstrap4 .select2-selection { background-color: #f9fbfd; border-color: #d9e2ed; }
        .gr-page .gr-table-wrap { border-color: #dde5ef; background: #f9fbfd; }
        .gr-page #table-request thead th { background: #e8eef6; color: #4f5d72; border-bottom-color: #dde5ef; }
        .gr-page #table-request tbody tr.gr-row { background: #f9fbfd; }
        .gr-page #table-request tbody td { border-bottom-color: #e3e9f1; }
        .gr-page #table-request tbody tr.gr-row:hover { background: #f1f5fa; }
        .gr-page #table-request tbody tr.gr-row.shown { background: #e6eef8; }
        .gr-page .gr-child { background: #f2f6fb; }
        .gr-page .gr-ket-btn { background: #f9fbfd; border-color: #d9e2ed; }

        /* Modal ikut kalem */
        #modalTracking .modal-content, #modalKeterangan .modal-body { background: #f1f5fa; }
        #modalTracking .gr-approval,
        #modalTracking .gr-item-card,
        #modalTracking .gr-tile,
        #modalTracking .gr-item-search input,
        .gr-ket-panel { background: #f9fbfd; border-color: #dde5ef; }
        #modalTracking .gr-detail { background-color: #f9fbfd; border-color: #dde5ef; background-image: radial-gradient(#e2e8f1 1px, transparent 1.2px); }
        #modalTracking .gr-timeline li .tl-ic { box-shadow: 0 0 0 3px #f9fbfd; }
        #modalTracking .gr-item-card.active { background: var(--c-bg); border-color: var(--c-fg); }

        /* ================= Modal Buat Request ================= */
        #modalBuat .modal-body { background: #f1f5fa; padding: 18px 22px; }
        .gr-bf-card { background: #f9fbfd; border: 1px solid #dde5ef; border-radius: 14px; padding: 12px 14px; transition: box-shadow .2s ease, border-color .2s ease; }
        .gr-bf label { font-size: 12px; font-weight: 600; color: var(--gr-muted); margin-bottom: 3px; }
        .gr-bf .form-control, .gr-bf .select2-container--bootstrap4 .select2-selection { border-color: #d9e2ed; border-radius: 8px; background-color: #fff; }
        .gr-bf .form-control:focus { border-color: var(--gr-accent); box-shadow: 0 0 0 3px var(--gr-accent-soft); }
        .gr-bf .form-control[readonly] { background: #eef3f9; }
        .gr-bf-entry:focus-within { border-color: var(--gr-accent); box-shadow: 0 0 0 3px var(--gr-accent-soft); }
        .gr-bf-qty .btn { width: 32px; background: #eef3f9; border: 1px solid #d9e2ed; color: var(--gr-accent); font-weight: 700; }
        .gr-bf-qty .btn:hover { background: var(--gr-accent); color: #fff; }
        .gr-bf-qty .input-group-prepend .btn { border-radius: 8px 0 0 8px; }
        .gr-bf-qty .input-group-append .btn { border-radius: 0 8px 8px 0; }
        .gr-bf-qty .form-control { border-radius: 0 !important; }
        .gr-bf-footer { background: #f1f5fa; border-top: 1px solid #dde5ef; }
        .gr-btn-soft { background: var(--gr-accent-soft); color: var(--gr-accent); border: 0; border-radius: 10px; transition: background .2s ease, color .2s ease; }
        .gr-btn-soft:hover { background: var(--gr-accent); color: #fff; }

        .gr-bf-list { display: flex; flex-direction: column; gap: 8px; }
        .gr-bf-item {
            display: flex; align-items: center; gap: 12px;
            background: #f9fbfd; border: 1px solid #dde5ef; border-radius: 12px; padding: 10px 12px;
            transition: transform .2s ease, box-shadow .2s ease;
        }
        .gr-bf-item:hover { transform: translateX(3px); box-shadow: 0 6px 14px rgba(47,58,74,.06); }
        .gr-bf-item.baru { animation: gr-bf-in .6s ease; }
        .gr-bf-item.keluar { animation: gr-bf-out .28s ease forwards; }
        .gr-bf-item.flash { animation: gr-bf-flash .8s ease; }
        @keyframes gr-bf-in { 0% { opacity: 0; transform: translateX(-14px); background: #e6f3eb; } 60% { opacity: 1; transform: none; background: #e6f3eb; } 100% { background: #f9fbfd; } }
        @keyframes gr-bf-out { to { opacity: 0; transform: translateX(24px); } }
        @keyframes gr-bf-flash { 0%, 100% { background: #f9fbfd; } 30% { background: #fbf3df; } }
        .gr-bf-item .no { width: 28px; height: 28px; border-radius: 9px; background: var(--gr-accent-soft); color: var(--gr-accent); font-weight: 700; font-size: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .gr-bf-item .nm { flex: 1; min-width: 0; font-weight: 600; font-size: 13px; }
        .gr-bf-item .nm small { display: block; font-weight: 400; color: var(--gr-muted); font-size: 11.5px; }
        .gr-bf-item .q { width: 72px; text-align: right; border: 1px solid #d9e2ed; border-radius: 8px; padding: 3px 8px; font-size: 13px; background: #fff; }
        .gr-bf-item .q:focus { outline: 0; border-color: var(--gr-accent); box-shadow: 0 0 0 3px var(--gr-accent-soft); }
        .gr-bf-item .st { width: 50px; font-size: 12px; color: var(--gr-muted); }
        .gr-bf-item .del { width: 30px; height: 30px; border: 0; border-radius: 9px; background: #f9e8ea; color: #c0505f; transition: background .2s ease, color .2s ease, transform .15s ease; }
        .gr-bf-item .del:hover { background: #c0505f; color: #fff; transform: scale(1.08); }

        .gr-bf-empty { text-align: center; color: #9aa6b8; padding: 50px 0; border: 2px dashed #dde5ef; border-radius: 14px; background: #f6f9fc; }
        .gr-bf-empty i { font-size: 36px; display: block; margin-bottom: 8px; animation: gr-bf-float 2.4s ease-in-out infinite; }
        @keyframes gr-bf-float { 50% { transform: translateY(-6px); } }
        .gr-bf-count { transition: transform .25s ease; display: inline-block; }
        .gr-bf-count.bump { transform: scale(1.35); }
        .gr-shake { animation: gr-shake .4s ease; }

        /* Chip status */
        .gr-chip {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 3px 10px; border-radius: 20px;
            font-size: 11.5px; font-weight: 600; white-space: nowrap;
            background: var(--c-bg); color: var(--c-fg);
        }
        .gr-chip::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: var(--c-fg); }
        .gr-chip.pulse::before { animation: gr-dot 1.6s ease-in-out infinite; }
        @keyframes gr-dot { 50% { transform: scale(1.7); opacity: .4; } }

        @keyframes gr-rise {
            from { opacity: 0; transform: translateY(12px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ================= Tombol utama ================= */
        .gr-btn-main {
            background: linear-gradient(135deg, #4a6fa5, #6b8cc2);
            border: 0; color: #fff; border-radius: 10px;
            padding: 8px 18px; font-weight: 600;
            box-shadow: 0 4px 12px rgba(74,111,165,.25);
            transition: transform .2s ease, box-shadow .2s ease;
        }
        .gr-btn-main:hover { color: #fff; transform: translateY(-2px); box-shadow: 0 8px 18px rgba(74,111,165,.32); }
        .gr-btn-main i { transition: transform .3s ease; }
        .gr-btn-main:hover i { transform: rotate(90deg); }

        /* ================= Kartu ringkasan ================= */
        .gr-stat {
            position: relative; overflow: hidden;
            border-radius: 14px; padding: 14px 16px;
            background: #fff; border: 1px solid var(--gr-line);
            cursor: pointer; height: 100%;
            transition: transform .2s ease, box-shadow .2s ease, background .25s ease, border-color .25s ease;
            opacity: 0; animation: gr-rise .45s ease forwards;
        }
        .gr-stat:hover { transform: translateY(-3px); box-shadow: 0 10px 22px rgba(47,58,74,.07); }
        .gr-stat.active { background: var(--c-bg); border-color: var(--c-fg); }
        .gr-stat.active .gr-stat-icon { background: var(--c-fg); color: #fff; }
        .gr-stat .gr-stat-icon {
            width: 40px; height: 40px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            background: var(--c-bg); color: var(--c-fg); font-size: 16px;
            transition: transform .3s ease, background .25s ease, color .25s ease;
        }
        .gr-stat:hover .gr-stat-icon { transform: rotate(-8deg) scale(1.08); }
        .gr-stat .gr-stat-label { font-size: 12px; color: var(--gr-muted); white-space: nowrap; }
        .gr-stat .gr-stat-value { font-size: 24px; font-weight: 700; color: var(--gr-ink); line-height: 1.1; }
        .gr-stat .gr-stat-bg {
            position: absolute; right: -8px; bottom: -12px;
            font-size: 54px; color: var(--c-fg); opacity: .06;
            transition: transform .4s ease, opacity .3s ease;
        }
        .gr-stat:hover .gr-stat-bg { transform: scale(1.15) rotate(-10deg); opacity: .1; }

        /* ================= Filter ================= */
        .gr-filter { background: var(--gr-soft); border: 1px solid var(--gr-line); border-radius: 14px; padding: 12px 14px 14px; }
        .gr-filter label { font-size: 12px; font-weight: 600; color: var(--gr-muted); margin-bottom: 4px; }
        .gr-filter .form-control, .gr-filter .input-group-text { border-color: var(--gr-line); border-radius: 8px; }
        .gr-filter .input-group-text { background: #fff; color: var(--gr-muted); border-right: 0; border-radius: 8px 0 0 8px; }
        .gr-filter .input-group .form-control { border-left: 0; border-radius: 0 8px 8px 0; }
        .gr-filter .form-control:focus { border-color: var(--gr-accent); box-shadow: 0 0 0 3px var(--gr-accent-soft); }

        /* ================= Tabel ================= */
        .gr-table-wrap { border: 1px solid var(--gr-line); border-radius: 14px; overflow: hidden; }
        #table-request { margin: 0 !important; border: 0; }
        #table-request thead th {
            background: var(--gr-soft); color: #5b6576;
            font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .3px;
            border: 0; border-bottom: 1px solid var(--gr-line);
            padding: 10px 8px; text-align: center; white-space: nowrap; vertical-align: middle;
        }
        #table-request tbody td { border: 0; border-bottom: 1px solid var(--gr-line); padding: 10px 8px; font-size: 13px; vertical-align: middle; }
        #table-request tbody tr.gr-row { cursor: pointer; transition: background .15s ease; }
        #table-request tbody tr.gr-row td:first-child { box-shadow: inset 3px 0 0 var(--c-fg); }
        #table-request tbody tr.gr-row:hover { background: #fafbfd; }
        #table-request tbody tr.gr-row:hover td:first-child { box-shadow: inset 5px 0 0 var(--c-fg); }
        #table-request tbody tr.gr-row.shown { background: var(--gr-accent-soft); }
        .gr-no { font-weight: 700; color: var(--gr-accent); letter-spacing: .2px; }
        .gr-caret { transition: transform .25s ease; color: #b4bccb; }
        tr.gr-row:hover .gr-caret { color: var(--gr-accent); }
        tr.shown .gr-caret { transform: rotate(90deg); color: var(--gr-accent); }

        .gr-count-item { background: var(--gr-soft); border: 1px solid var(--gr-line); color: #5b6576; border-radius: 20px; padding: 2px 9px; font-size: 11.5px; font-weight: 600; }

        .gr-progress { height: 7px; min-width: 110px; border-radius: 10px; background: #eef1f5; }
        .gr-progress .progress-bar { width: 0; border-radius: 10px; transition: width 1s cubic-bezier(.25,.8,.25,1); }
        .gr-bar-sage  { background: linear-gradient(90deg, #7cc39b, #5fa880); }
        .gr-bar-peach { background: linear-gradient(90deg, #f2c08f, #e9a46a); }
        .gr-muted-text { color: var(--gr-muted); font-size: 12px; }

        .gr-due { font-size: 11px; font-weight: 600; border-radius: 20px; padding: 1px 9px; display: inline-block; margin-top: 3px; }
        .gr-due.late { background: #fbeaec; color: #c0505f; animation: gr-blink 1.6s ease-in-out infinite; }
        .gr-due.soon { background: #fdf1e4; color: #c27a3a; }
        .gr-due.ok   { background: #e8f4ed; color: #4a8c68; }
        @keyframes gr-blink { 50% { opacity: .5; } }

        .gr-act-btn {
            width: 32px; height: 32px; border-radius: 10px; border: 0;
            display: inline-flex; align-items: center; justify-content: center;
            background: var(--c-bg); color: var(--c-fg); font-size: 13px;
            transition: transform .15s ease, background .2s ease, color .2s ease, box-shadow .2s ease;
        }
        .gr-act-btn:hover { background: var(--c-fg); color: #fff; transform: translateY(-2px); box-shadow: 0 4px 10px rgba(0,0,0,.12); }
        .gr-act-btn + .gr-act-btn { margin-left: 4px; }

        /* Isi baris yang dibuka */
        .gr-child { padding: 6px 16px 12px 48px; background: #fbfcfe; animation: gr-rise .3s ease; }
        .gr-child-item { display: flex; align-items: center; gap: 14px; padding: 8px 0; border-bottom: 1px dashed var(--gr-line); font-size: 13px; }
        .gr-child-item:last-child { border-bottom: 0; }
        .gr-child-item .ic { width: 28px; height: 28px; border-radius: 8px; background: var(--c-bg); color: var(--c-fg); display: flex; align-items: center; justify-content: center; font-size: 12px; }
        .gr-child-item .nama { flex: 1; min-width: 160px; }
        .gr-child-item .angka { width: 130px; font-size: 12px; color: var(--gr-muted); text-align: right; }
        .gr-child-item .bar { width: 160px; }
        .gr-child-item .sisa { width: 80px; text-align: right; font-weight: 700; font-size: 12px; }
        .gr-child-item .nama .ket { font-size: 11.5px; color: var(--gr-muted); margin-top: 1px; }
        .gr-child-item .nama .ket i { color: var(--c-fg); }

        /* Panel keterangan status */
        .gr-ket-btn {
            border: 1px solid var(--gr-line); background: #fff; color: var(--gr-accent);
            border-radius: 20px; padding: 4px 12px; font-size: 12px; font-weight: 600;
            transition: background .2s ease, color .2s ease, transform .15s ease, box-shadow .2s ease;
        }
        .gr-ket-btn:hover { background: var(--gr-accent); color: #fff; transform: translateY(-1px); box-shadow: 0 4px 10px rgba(74,111,165,.25); }
        .gr-ket-btn i { transition: transform .3s ease; }
        .gr-ket-btn:hover i { transform: rotate(-15deg) scale(1.15); }

        /* Modal keterangan */
        .gr-ket-modal { border: 0; border-radius: 18px; overflow: hidden; }
        .gr-ket-hero {
            position: relative; overflow: hidden; color: #fff; flex-shrink: 0;
            padding: 18px 22px 20px;
            background: linear-gradient(135deg, #3f5f8f 0%, #5b7fb5 55%, #86a5d3 100%);
        }
        .gr-ket-hero .blob { position: absolute; border-radius: 50%; background: rgba(255,255,255,.08); animation: gr-float 9s ease-in-out infinite; }
        .gr-ket-hero .b1 { width: 160px; height: 160px; top: -70px; right: 60px; }
        .gr-ket-hero .b2 { width: 100px; height: 100px; bottom: -50px; right: -20px; animation-delay: -4s; }
        .gr-ket-hero .close { position: absolute; top: 10px; right: 14px; color: #fff; opacity: .8; font-size: 26px; text-shadow: none; background: none; border: 0; z-index: 2; }
        .gr-ket-hero .close:hover { opacity: 1; }
        .gr-ket-judul { font-size: 20px; font-weight: 700; position: relative; }
        .gr-ket-hero .gr-hero-label, .gr-ket-hero .gr-hero-kep { position: relative; }
        #modalKeterangan .modal-body { padding: 16px 18px; background: #fbfcfe; }
        /* nama keyframe berbeda supaya animasi diputar ulang tiap modal dibuka */
        #modalKeterangan.show .gr-ket-item { animation-name: gr-rise-ket; }
        @keyframes gr-rise-ket {
            from { opacity: 0; transform: translateY(12px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .gr-ket-tip {
            margin-top: 12px; font-size: 12px; color: #5b6576;
            background: #fbf3df; border-radius: 10px; padding: 8px 12px;
        }
        .gr-ket-tip > i:first-child { color: #b98a2e; margin-right: 4px; }

        .gr-ket-panel { border: 1px solid var(--gr-line); border-radius: 14px; background: #fff; padding: 12px 14px; }
        .gr-ket-row { display: flex; gap: 12px; align-items: flex-start; }
        .gr-ket-row + .gr-ket-row { border-top: 1px dashed var(--gr-line); margin-top: 10px; padding-top: 10px; }
        .gr-ket-title { width: 110px; flex-shrink: 0; font-size: 11.5px; font-weight: 700; color: #5b6576; text-transform: uppercase; letter-spacing: .4px; padding-top: 6px; }
        .gr-ket-list { display: grid; grid-template-columns: repeat(auto-fill, minmax(230px, 1fr)); gap: 8px; flex: 1; }
        .gr-ket-item {
            display: flex; gap: 10px; align-items: flex-start; padding: 6px 8px; border-radius: 10px;
            transition: background .2s ease, transform .2s ease;
            opacity: 0; animation: gr-rise .35s ease forwards;
        }
        .gr-ket-item:hover { background: var(--c-bg); transform: translateY(-1px); }
        .gr-ket-item .ic {
            width: 30px; height: 30px; border-radius: 9px; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center; font-size: 12px;
            background: var(--c-bg); color: var(--c-fg);
        }
        .gr-ket-item .t { font-size: 12.5px; font-weight: 700; color: var(--c-fg); }
        .gr-ket-item .d { font-size: 11.5px; color: var(--gr-muted); line-height: 1.35; }
        @media (max-width: 767px) { .gr-ket-row { flex-direction: column; gap: 4px; } }

        .gr-sisa-ada { color: #c0505f; }
        .gr-sisa-nol { color: #4a8c68; }

        .gr-page .dataTables_info { font-size: 12px; color: var(--gr-muted); padding: 10px 14px !important; }
        .gr-page .dataTables_paginate { padding: 6px 10px; }
        .gr-page .page-link { border: 0; border-radius: 8px !important; margin: 0 2px; color: var(--gr-accent); }
        .gr-page .page-item.active .page-link { background: var(--gr-accent); }

        /* ================= Modal tracking ================= */
        #modalTracking .modal-content { border: 0; border-radius: 18px; overflow: hidden; background: #fbfcfe; }
        #modalTracking .modal-body { padding: 18px 22px; }

        /* Hero header */
        .gr-hero {
            position: relative; overflow: hidden; color: #fff;
            flex-shrink: 0; /* jangan ikut menyusut di modal-dialog-scrollable */
            padding: 20px 24px 22px;
            background: linear-gradient(135deg, #3f5f8f 0%, #5b7fb5 55%, #86a5d3 100%);
        }
        .gr-hero .blob { position: absolute; border-radius: 50%; background: rgba(255,255,255,.08); animation: gr-float 9s ease-in-out infinite; }
        .gr-hero .b1 { width: 220px; height: 220px; top: -90px; right: 120px; }
        .gr-hero .b2 { width: 140px; height: 140px; bottom: -70px; right: -30px; animation-delay: -3s; }
        .gr-hero .b3 { width: 70px; height: 70px; top: 30px; left: 45%; animation-delay: -6s; background: rgba(255,255,255,.06); }
        .gr-hero .dots {
            position: absolute; inset: 0; opacity: .12;
            background-image: radial-gradient(#fff 1px, transparent 1.2px); background-size: 18px 18px;
            -webkit-mask-image: linear-gradient(90deg, transparent, #000 70%);
            mask-image: linear-gradient(90deg, transparent, #000 70%);
        }
        @keyframes gr-float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(14px, -12px) scale(1.06); }
        }
        .gr-hero .close { position: absolute; top: 12px; right: 16px; color: #fff; opacity: .8; z-index: 2; font-size: 26px; text-shadow: none; background: none; border: 0; }
        .gr-hero .close:hover { opacity: 1; }
        .gr-hero-label { font-size: 11px; text-transform: uppercase; letter-spacing: 1.5px; opacity: .8; }
        .gr-hero-no { font-size: 24px; font-weight: 700; letter-spacing: .5px; animation: gr-rise .5s ease; }
        .gr-hero-kep { font-size: 13.5px; opacity: .92; margin-top: 2px; }
        .gr-hero-chips { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 12px; }
        .gr-hero-chip {
            display: inline-flex; align-items: center; gap: 6px;
            background: rgba(255,255,255,.16); border: 1px solid rgba(255,255,255,.22);
            border-radius: 20px; padding: 3px 11px; font-size: 12px;
            opacity: 0; animation: gr-rise .4s ease forwards;
        }
        .gr-hero-ring { position: relative; width: 96px; height: 96px; flex-shrink: 0; }
        .gr-hero-ring svg { transform: rotate(-90deg); }
        .gr-hero-ring circle { fill: none; stroke-width: 8; }
        .gr-hero-ring .bg { stroke: rgba(255,255,255,.2); }
        .gr-hero-ring .fg { stroke: #fff; stroke-linecap: round; transition: stroke-dashoffset 1.4s cubic-bezier(.25,.8,.25,1); }
        .gr-hero-ring .label { position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; }
        .gr-hero-ring .label b { font-size: 22px; line-height: 1; }
        .gr-hero-ring .label small { font-size: 10px; opacity: .85; }

        /* Strip approval (level request) */
        .gr-approval {
            display: flex; align-items: center; flex-wrap: wrap; gap: 10px;
            background: #fff; border: 1px solid var(--gr-line); border-radius: 14px;
            padding: 10px 14px; margin-bottom: 16px;
            opacity: 0; animation: gr-rise .4s ease .15s forwards;
        }
        .gr-appr-step { display: flex; align-items: center; gap: 8px; font-size: 12.5px; }
        .gr-appr-step .ic {
            width: 30px; height: 30px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center; font-size: 12px;
            background: var(--c-bg); color: var(--c-fg);
        }
        .gr-appr-step.wait .ic { animation: gr-pulse-amber 1.8s infinite; }
        .gr-appr-step .t { font-weight: 600; }
        .gr-appr-step .m { font-size: 11px; color: var(--gr-muted); }
        .gr-appr-arrow { color: #c3cad6; }
        .gr-appr-note { margin-left: auto; font-size: 12px; background: #f9e8ea; color: #c0505f; border-radius: 8px; padding: 4px 10px; }

        .gr-section-title { font-size: 12px; font-weight: 700; color: #5b6576; text-transform: uppercase; letter-spacing: .6px; margin-bottom: 10px; display: flex; align-items: center; gap: 8px; }
        .gr-section-title .cnt { background: var(--gr-accent-soft); color: var(--gr-accent); border-radius: 20px; padding: 0 8px; font-size: 11px; }

        /* Daftar item (kiri) */
        .gr-item-list { display: flex; flex-direction: column; gap: 8px; }
        .gr-item-card {
            position: relative; display: flex; align-items: center; gap: 12px;
            background: #fff; border: 1px solid var(--gr-line); border-radius: 14px;
            padding: 10px 12px; cursor: pointer;
            transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease, background .25s ease;
            opacity: 0; animation: gr-rise .4s ease forwards;
        }
        .gr-item-card::before {
            content: ''; position: absolute; left: 0; top: 14px; bottom: 14px; width: 4px;
            border-radius: 0 4px 4px 0; background: var(--c-fg); transform: scaleY(0); transition: transform .25s ease;
        }
        .gr-item-card:hover { transform: translateX(3px); box-shadow: 0 6px 16px rgba(47,58,74,.06); }
        .gr-item-card.active { border-color: var(--c-fg); background: var(--c-bg); transform: translateX(6px); box-shadow: 0 8px 20px rgba(47,58,74,.08); }
        .gr-item-card.active::before { transform: scaleY(1); }
        .gr-item-card .nm { font-weight: 600; font-size: 13px; line-height: 1.3; }
        .gr-item-card .sub { font-size: 11.5px; color: var(--gr-muted); margin-top: 2px; }
        .gr-item-card .arrow { margin-left: auto; color: #c3cad6; transition: transform .25s ease, color .25s ease; }
        .gr-item-card.active .arrow { color: var(--c-fg); transform: translateX(3px); }

        .gr-mini-ring { position: relative; width: 44px; height: 44px; flex-shrink: 0; }
        .gr-mini-ring svg { transform: rotate(-90deg); }
        .gr-mini-ring circle { fill: none; stroke-width: 4.5; }
        .gr-mini-ring .bg { stroke: #eef1f5; }
        .gr-mini-ring .fg { stroke: var(--c-fg); stroke-linecap: round; transition: stroke-dashoffset 1.1s ease; }
        .gr-mini-ring .ic { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; font-size: 13px; color: var(--c-fg); }

        /* Cari & legend item */
        .gr-item-search { position: relative; margin-bottom: 8px; }
        .gr-item-search i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--gr-muted); font-size: 12px; }
        .gr-item-search input {
            width: 100%; border: 1px solid var(--gr-line); border-radius: 10px;
            padding: 7px 30px 7px 32px; font-size: 13px; background: #fff;
            transition: border-color .2s ease, box-shadow .2s ease;
        }
        .gr-item-search input:focus { outline: 0; border-color: var(--gr-accent); box-shadow: 0 0 0 3px var(--gr-accent-soft); }
        .gr-item-search .clear { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); color: var(--gr-muted); cursor: pointer; display: none; }

        .gr-legend { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 10px; }
        .gr-legend .lg {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 3px 4px 3px 10px; border-radius: 20px; cursor: pointer; user-select: none;
            font-size: 11.5px; font-weight: 600;
            background: var(--c-bg); color: var(--c-fg); border: 1px solid transparent;
            transition: transform .15s ease, opacity .2s ease, border-color .2s ease, box-shadow .2s ease;
            opacity: 0; animation: gr-rise .35s ease forwards;
        }
        .gr-legend .lg::before { content: ''; width: 7px; height: 7px; border-radius: 50%; background: var(--c-fg); }
        .gr-legend .lg .n { background: #fff; border-radius: 20px; min-width: 20px; padding: 0 6px; text-align: center; font-size: 11px; }
        .gr-legend .lg:hover { transform: translateY(-1px); }
        .gr-legend .lg.kosong { filter: grayscale(.6); }
        .gr-legend .lg.kosong .n { color: var(--gr-muted); }
        .gr-legend.ada-filter .lg:not(.active) { opacity: .45 !important; }
        .gr-legend .lg.active { border-color: var(--c-fg); box-shadow: 0 2px 8px rgba(47,58,74,.12); }
        .gr-legend-ket { font-size: 11.5px; color: var(--gr-muted); min-height: 16px; margin: -4px 0 10px; transition: opacity .2s ease; }

        .gr-item-card.hide { display: none; }
        .gr-item-empty { text-align: center; color: var(--gr-muted); font-size: 12.5px; padding: 22px 0; border: 1px dashed var(--gr-line); border-radius: 14px; display: none; }
        .gr-item-empty i { font-size: 24px; display: block; margin-bottom: 6px; opacity: .6; }
        .gr-item-card mark { background: #fbf3df; color: inherit; padding: 0 1px; border-radius: 3px; }

        /* Panel detail item (kanan) */
        .gr-detail {
            position: relative; border-radius: 16px; border: 1px solid var(--gr-line);
            background-color: #fff;
            background-image: radial-gradient(#edf0f5 1px, transparent 1.2px);
            background-size: 16px 16px;
            padding: 18px; min-height: 100%;
        }
        .gr-detail-inner.in  { animation: gr-swap-in .4s ease both; }
        .gr-detail-inner.out { animation: gr-swap-out .15s ease both; }
        @keyframes gr-swap-in  { from { opacity: 0; transform: translateY(10px) scale(.99); } to { opacity: 1; transform: none; } }
        @keyframes gr-swap-out { to { opacity: 0; transform: translateY(-6px); } }

        .gr-d-head { display: flex; align-items: center; gap: 14px; margin-bottom: 16px; }
        .gr-d-icon {
            position: relative; width: 52px; height: 52px; border-radius: 16px; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center; font-size: 20px; color: #fff;
            background: var(--c-fg);
            background: linear-gradient(135deg, var(--c-fg), color-mix(in srgb, var(--c-fg) 60%, #fff));
            box-shadow: 0 8px 18px rgba(47,58,74,.15);
            animation: gr-pop .5s cubic-bezier(.34,1.56,.64,1);
        }
        @keyframes gr-pop { from { transform: scale(.4) rotate(-15deg); opacity: 0; } to { transform: none; opacity: 1; } }
        .gr-d-name { font-size: 16px; font-weight: 700; line-height: 1.25; }
        .gr-d-meta { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 5px; }
        .gr-tag { font-size: 11px; background: var(--gr-soft); border: 1px solid var(--gr-line); color: #5b6576; border-radius: 6px; padding: 1px 8px; }

        /* Partikel saat item lengkap */
        .gr-spark { position: absolute; left: 50%; top: 50%; width: 6px; height: 6px; border-radius: 50%; pointer-events: none; animation: gr-burst .8s ease-out forwards; }
        @keyframes gr-burst {
            from { transform: translate(-50%, -50%) scale(1); opacity: 1; }
            to   { transform: translate(calc(-50% + var(--dx)), calc(-50% + var(--dy))) scale(.2); opacity: 0; }
        }

        .gr-tiles { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; margin-bottom: 6px; }
        .gr-tile {
            background: #fff; border: 1px solid var(--gr-line); border-radius: 12px;
            padding: 10px; position: relative; overflow: hidden;
            opacity: 0; animation: gr-rise .4s ease forwards;
        }
        .gr-tile::after { content: ''; position: absolute; left: 0; right: 0; top: 0; height: 3px; background: var(--t-color); }
        .gr-tile .l { font-size: 11px; color: var(--gr-muted); display: flex; align-items: center; gap: 5px; }
        .gr-tile .l i { color: var(--t-color); }
        .gr-tile .v { font-size: 20px; font-weight: 700; color: var(--gr-ink); line-height: 1.2; }
        .gr-tile .v small { font-size: 11px; font-weight: 500; color: var(--gr-muted); }

        /* Stepper per item */
        .gr-stepper { display: flex; justify-content: space-between; position: relative; margin: 18px 0 20px; }
        .gr-stepper::before {
            content: ''; position: absolute; top: 20px; left: 12.5%; right: 12.5%;
            height: 4px; border-radius: 4px; background: #eef1f5; z-index: 0;
        }
        .gr-stepper .gr-line-fill {
            position: absolute; top: 20px; left: 12.5%; height: 4px; border-radius: 4px;
            background: linear-gradient(90deg, #7cc39b, #5fa880); z-index: 0;
            width: 0; transition: width 1.1s ease;
        }
        .gr-step { position: relative; z-index: 1; text-align: center; flex: 1; }
        .gr-step .gr-dot {
            width: 42px; height: 42px; border-radius: 50%; margin: 0 auto 6px;
            display: flex; align-items: center; justify-content: center;
            background: #fff; border: 3px solid #e3e8ef; color: #c3cad6; font-size: 14px;
            transition: all .35s cubic-bezier(.34,1.56,.64,1);
        }
        .gr-step.done .gr-dot { background: #5fa880; border-color: #5fa880; color: #fff; transform: scale(1.06); }
        .gr-step.current .gr-dot { border-color: #4a6fa5; color: #4a6fa5; background: #e9f0fa; animation: gr-pulse 1.8s infinite; }
        .gr-step.partial .gr-dot { border-color: #e9a46a; color: #c47f45; background: #fcefe3; animation: gr-pulse-peach 1.8s infinite; }
        .gr-step.rejected .gr-dot { background: #d26b78; border-color: #d26b78; color: #fff; animation: gr-shake .5s ease; }
        .gr-step .gr-step-label { font-size: 12px; font-weight: 600; color: #b4bccb; transition: color .3s ease; }
        .gr-step.done .gr-step-label, .gr-step.current .gr-step-label, .gr-step.partial .gr-step-label { color: var(--gr-ink); }
        .gr-step.rejected .gr-step-label { color: #c0505f; }
        .gr-step .gr-step-date { font-size: 10.5px; color: var(--gr-muted); min-height: 14px; }
        @keyframes gr-pulse {
            0%   { box-shadow: 0 0 0 0 rgba(74,111,165,.4); }
            70%  { box-shadow: 0 0 0 12px rgba(74,111,165,0); }
            100% { box-shadow: 0 0 0 0 rgba(74,111,165,0); }
        }
        @keyframes gr-pulse-peach {
            0%   { box-shadow: 0 0 0 0 rgba(233,164,106,.45); }
            70%  { box-shadow: 0 0 0 12px rgba(233,164,106,0); }
            100% { box-shadow: 0 0 0 0 rgba(233,164,106,0); }
        }
        @keyframes gr-pulse-amber {
            0%   { box-shadow: 0 0 0 0 rgba(185,138,46,.4); }
            70%  { box-shadow: 0 0 0 10px rgba(185,138,46,0); }
            100% { box-shadow: 0 0 0 0 rgba(185,138,46,0); }
        }
        @keyframes gr-shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-4px); }
            75% { transform: translateX(4px); }
        }

        /* Riwayat per item */
        .gr-timeline { list-style: none; padding-left: 0; margin: 0; }
        .gr-timeline li {
            position: relative; padding: 0 0 14px 28px; border-left: 2px solid var(--gr-line); margin-left: 10px;
            opacity: 0; animation: gr-slide .4s ease forwards;
        }
        .gr-timeline li:last-child { border-left-color: transparent; padding-bottom: 0; }
        .gr-timeline li .tl-ic {
            position: absolute; left: -11px; top: -1px;
            width: 20px; height: 20px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center; font-size: 9px;
            background: var(--tl-bg); color: var(--tl-fg);
            box-shadow: 0 0 0 3px #fff;
        }
        .gr-timeline li.pending .tl-ic { background: #fff; border: 2px dashed #c3cad6; color: #b4bccb; }
        .gr-timeline li.pending .tl-ic i { animation: gr-spin 2s linear infinite; }
        .gr-timeline li.pending .gr-tl-title { color: var(--gr-muted); font-style: italic; }
        @keyframes gr-spin { to { transform: rotate(360deg); } }
        .gr-timeline .gr-tl-title { font-weight: 600; font-size: 13px; }
        .gr-timeline .gr-tl-meta { font-size: 11px; color: var(--gr-muted); }
        .gr-timeline .gr-tl-ket { font-size: 12px; background: var(--gr-soft); border-radius: 6px; padding: 2px 8px; display: inline-block; margin-top: 4px; }
        .gr-timeline .gr-tl-qty { font-weight: 700; color: #4a8c68; }
        @keyframes gr-slide {
            from { opacity: 0; transform: translateX(-10px); }
            to   { opacity: 1; transform: translateX(0); }
        }

        @media (max-width: 767px) {
            .gr-tiles { grid-template-columns: repeat(2, 1fr); }
            .gr-hero-ring { display: none; }
        }
    </style>
@endsection

@section('content')
@php
    // Warna kalem: bg = latar lembut, fg = warna teks/ikon
    $statusList = [
        'Draft'             => ['bg' => '#eef1f5', 'fg' => '#6b7687', 'icon' => 'fa-pencil-alt'],
        'Menunggu Approval' => ['bg' => '#fbf3df', 'fg' => '#b98a2e', 'icon' => 'fa-hourglass-half'],
        'Proses PO'         => ['bg' => '#e9f0fa', 'fg' => '#4a6fa5', 'icon' => 'fa-file-invoice'],
        'Diterima Sebagian' => ['bg' => '#fcefe3', 'fg' => '#c47f45', 'icon' => 'fa-truck-loading'],
        'Selesai'           => ['bg' => '#e6f3eb', 'fg' => '#4a8c68', 'icon' => 'fa-check-double'],
        'Ditolak'           => ['bg' => '#f9e8ea', 'fg' => '#c0505f', 'icon' => 'fa-times-circle'],
    ];
    $counts = collect($requests)->countBy('status');
    $today = strtotime(date('Y-m-d'));
    $chip = fn($s) => "--c-bg: {$statusList[$s]['bg']}; --c-fg: {$statusList[$s]['fg']}";
@endphp

<div class="gr-page">
<div class="alert alert-warning py-2 small mb-3">
    <i class="fas fa-info-circle"></i> <b>Mockup tampilan.</b> Data di halaman ini masih contoh dan belum tersimpan ke database.
</div>

<div class="card card-sb">
    <div class="card-header bg-sb d-flex align-items-center">
        <h5 class="card-title fw-bold mb-0"><i class="fas fa-clipboard-list"></i> Input Request</h5>
    </div>
    <div class="card-body">

        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
            <button type="button" class="btn gr-btn-main" onclick="bukaFormRequest()">
                <i class="fas fa-plus"></i> Buat Request
            </button>
            <small class="gr-muted-text mt-2 mt-md-0"><i class="fas fa-mouse-pointer"></i> Klik baris untuk melihat item, klik <i class="fas fa-search-location"></i> untuk tracking per item</small>
        </div>

        {{-- Ringkasan status --}}
        <div class="row mb-3">
            <div class="col-6 col-md-4 col-lg mb-2">
                <div class="gr-stat active" style="--c-bg: #e9f0fa; --c-fg: #4a6fa5; animation-delay: 0s" data-status="">
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
                    <div class="gr-stat" style="{{ $chip($status) }}; animation-delay: {{ $loop->iteration * 0.07 }}s" data-status="{{ $status }}">
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

        <div class="gr-filter mb-3">
            <div class="row align-items-end">
                <div class="col-md-2 mb-2 mb-md-0">
                    <label>Tanggal Awal</label>
                    <input type="date" id="filter_tgl_awal" class="form-control form-control-sm" value="2026-11-01">
                </div>
                <div class="col-md-2 mb-2 mb-md-0">
                    <label>Tanggal Akhir</label>
                    <input type="date" id="filter_tgl_akhir" class="form-control form-control-sm" value="2026-11-30">
                </div>
                <div class="col-md-3 mb-2 mb-md-0">
                    <label>Status</label>
                    <select id="filter_status" class="form-control form-control-sm select2bs4">
                        <option value="">Semua Status</option>
                        @foreach ($statusList as $status => $cfg)
                            <option value="{{ $status }}">{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-5">
                    <label>Search</label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-search"></i></span></div>
                        <input type="text" id="filter_search" class="form-control" placeholder="Cari No Request, Keperluan, Nama Barang">
                    </div>
                </div>
            </div>
        </div>

        {{-- Keterangan status --}}
        @php
            $ketRequest = [
                'Draft'             => 'Belum diajukan, masih bisa diubah',
                'Menunggu Approval' => 'Sudah diajukan, menunggu persetujuan atasan',
                'Proses PO'         => 'Disetujui, sedang dibuatkan PO / barang belum datang',
                'Diterima Sebagian' => 'Sebagian barang sudah diterima gudang',
                'Selesai'           => 'Semua barang sudah diterima gudang',
                'Ditolak'           => 'Ditolak atasan, lihat alasannya di tracking',
            ];
            $ketItem = [
                'Belum PO'          => ['#eef1f5', '#6b7687', 'fa-file-invoice',  'Sudah disetujui, menunggu dibuatkan PO oleh Purchasing'],
                'Menunggu Barang'   => ['#e9f0fa', '#4a6fa5', 'fa-truck',         'PO sudah dibuat, barang belum datang ke gudang'],
                'Diterima Sebagian' => ['#fcefe3', '#c47f45', 'fa-truck-loading', 'Sebagian barang sudah diterima gudang, masih ada sisa'],
                'Lengkap'           => ['#e6f3eb', '#4a8c68', 'fa-check',         'Semua qty barang sudah diterima gudang'],
            ];
        @endphp
        <div class="d-flex justify-content-end mb-2">
            <button type="button" class="gr-ket-btn" onclick="$('#modalKeterangan').modal('show')">
                <i class="fas fa-info-circle"></i> Keterangan status
            </button>
        </div>

        <div class="modal fade" id="modalKeterangan" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content gr-ket-modal">
                    <div class="gr-ket-hero">
                        <span class="blob b1"></span><span class="blob b2"></span>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
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
                        <div class="gr-ket-item" style="{{ $chip($status) }}; animation-delay: {{ $loop->index * 0.05 }}s">
                            <div class="ic"><i class="fas {{ $statusList[$status]['icon'] }}"></i></div>
                            <div><div class="t">{{ $status }}</div><div class="d">{{ $ket }}</div></div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="gr-ket-row">
                <div class="gr-ket-title">Status Item</div>
                <div class="gr-ket-list">
                    @foreach ($ketItem as $status => $k)
                        <div class="gr-ket-item" style="--c-bg: {{ $k[0] }}; --c-fg: {{ $k[1] }}; animation-delay: {{ 0.3 + $loop->index * 0.05 }}s">
                            <div class="ic"><i class="fas {{ $k[2] }}"></i></div>
                            <div><div class="t">{{ $status }}</div><div class="d">{{ $k[3] }}</div></div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
                        <div class="gr-ket-tip">
                            <i class="fas fa-lightbulb"></i>
                            Satu request bisa berisi beberapa item dengan status berbeda. Klik baris di tabel untuk melihat posisi tiap item,
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
                            <th width="9%">Tgl Request</th>
                            <th width="12%">No Request</th>
                            <th class="text-left">Keperluan</th>
                            <th width="6%">Item</th>
                            <th width="11%">Dibutuhkan</th>
                            <th width="12%">Status</th>
                            <th width="14%">Progress Diterima</th>
                            <th width="9%">Aksi</th>
                            <th class="d-none">Cari</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($requests as $i => $req)
                            @php
                                $totalQty = collect($req['items'])->sum('qty');
                                $totalTerima = collect($req['items'])->sum('qty_terima');
                                $persen = $totalQty > 0 ? round($totalTerima / $totalQty * 100) : 0;
                                $selisih = (int) round((strtotime($req['tgl_butuh']) - $today) / 86400);
                                $aktif = !in_array($req['status'], ['Selesai', 'Ditolak']);
                            @endphp
                            <tr class="gr-row" data-index="{{ $i }}" style="{{ $chip($req['status']) }}">
                                <td class="text-center"><i class="fas fa-chevron-right gr-caret"></i></td>
                                <td class="text-center">{{ date('d-m-Y', strtotime($req['tgl_request'])) }}</td>
                                <td class="text-center gr-no">{{ $req['no_request'] }}</td>
                                <td>{{ $req['keperluan'] }}</td>
                                <td class="text-center"><span class="gr-count-item">{{ count($req['items']) }} item</span></td>
                                <td class="text-center">
                                    {{ date('d-m-Y', strtotime($req['tgl_butuh'])) }}
                                    @if ($aktif)
                                        <br>
                                        @if ($selisih < 0)
                                            <span class="gr-due late"><i class="fas fa-exclamation-circle"></i> Lewat {{ abs($selisih) }} hari</span>
                                        @elseif ($selisih <= 3)
                                            <span class="gr-due soon"><i class="far fa-clock"></i> {{ $selisih == 0 ? 'Hari ini' : $selisih . ' hari lagi' }}</span>
                                        @else
                                            <span class="gr-due ok">{{ $selisih }} hari lagi</span>
                                        @endif
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="gr-chip {{ $aktif && $req['status'] != 'Draft' ? 'pulse' : '' }}">{{ $req['status'] }}</span>
                                </td>
                                <td>
                                    @if (in_array($req['status'], ['Draft', 'Ditolak', 'Menunggu Approval']))
                                        <span class="gr-muted-text">Belum ada PO</span>
                                    @else
                                        <div class="d-flex justify-content-between mb-1" style="font-size:11.5px">
                                            <span class="gr-muted-text" style="font-size:11.5px">{{ $totalTerima }} / {{ $totalQty }}</span>
                                            <b>{{ $persen }}%</b>
                                        </div>
                                        <div class="progress gr-progress">
                                            <div class="progress-bar {{ $persen >= 100 ? 'gr-bar-sage' : 'gr-bar-peach' }}" data-width="{{ $persen }}"></div>
                                        </div>
                                    @endif
                                </td>
                                <td class="text-center text-nowrap">
                                    <button class="gr-act-btn" style="--c-bg:#e9f0fa; --c-fg:#4a6fa5" onclick="showDetail({{ $i }}, event)" title="Tracking">
                                        <i class="fas fa-search-location"></i>
                                    </button>
                                    @if ($req['status'] == 'Draft')
                                        <button class="gr-act-btn" style="--c-bg:#e6f3eb; --c-fg:#4a8c68" onclick="mockAksi('Ajukan', '{{ $req['no_request'] }}', event)" title="Ajukan Approval">
                                            <i class="fas fa-paper-plane"></i>
                                        </button>
                                    @elseif ($req['status'] == 'Menunggu Approval')
                                        <button class="gr-act-btn" style="--c-bg:#f9e8ea; --c-fg:#c0505f" onclick="mockAksi('Batalkan', '{{ $req['no_request'] }}', event)" title="Batalkan">
                                            <i class="fas fa-ban"></i>
                                        </button>
                                    @elseif (in_array($req['status'], ['Selesai', 'Ditolak']))
                                        <button class="gr-act-btn" style="--c-bg:#eef1f5; --c-fg:#6b7687" onclick="event.stopPropagation(); bukaFormRequest({{ $i }})" title="Request ulang dengan item yang sama">
                                            <i class="fas fa-redo"></i>
                                        </button>
                                    @endif
                                </td>
                                <td class="d-none">{{ $req['status'] }} {{ collect($req['items'])->pluck('nama')->implode(' ') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
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
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
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
                <div class="gr-approval" id="md_approval"></div>

                <div class="row">
                    <div class="col-lg-5 mb-3">
                        <div class="gr-section-title"><i class="fas fa-boxes"></i> Item <span class="cnt" id="md_item_count">0</span></div>
                        <div class="gr-item-search">
                            <i class="fas fa-search"></i>
                            <input type="text" id="md_item_search" placeholder="Cari nama barang / No PO / No BPB" autocomplete="off">
                            <span class="clear" id="md_item_clear" title="Hapus"><i class="fas fa-times-circle" style="position:static; transform:none"></i></span>
                        </div>
                        <div class="gr-legend" id="md_legend"></div>
                        <div class="gr-legend-ket" id="md_legend_ket"></div>
                        <div class="gr-item-list" id="md_item_list"></div>
                        <div class="gr-item-empty" id="md_item_empty"><i class="fas fa-search"></i>Tidak ada item yang cocok</div>
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
<div class="modal fade" id="modalBuat" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content gr-ket-modal">
            <div class="gr-ket-hero">
                <span class="blob b1"></span><span class="blob b2"></span>
                <button type="button" class="close" onclick="tutupFormRequest()" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <div class="gr-hero-label"><i class="fas fa-plus-circle"></i> <span id="bf_label">Request Baru</span></div>
                <div class="gr-ket-judul">{{ $no_request }}</div>
                <div class="gr-hero-kep" id="bf_sub">Isi kebutuhan barang, lalu ajukan untuk approval</div>
            </div>
            <div class="modal-body gr-bf">
                <div class="row">
                    {{-- Kiri: info request + input item --}}
                    <div class="col-lg-5 mb-3">
                        <div class="gr-section-title"><i class="fas fa-file-alt"></i> Info Request</div>
                        <div class="gr-bf-card mb-3">
                            <div class="row">
                                <div class="col-6 mb-2">
                                    <label>Tgl Request</label>
                                    <input type="date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" readonly>
                                </div>
                                <div class="col-6 mb-2">
                                    <label>Tgl Dibutuhkan</label>
                                    <input type="date" id="bf_tgl_butuh" class="form-control form-control-sm">
                                </div>
                                <div class="col-12 mb-2">
                                    <label>Departemen</label>
                                    <select id="bf_departemen" class="form-control form-control-sm bf-select2">
                                        <option value="IT" selected>IT</option>
                                        <option value="HRD">HRD</option>
                                        <option value="GA">GA</option>
                                        <option value="Produksi">Produksi</option>
                                        <option value="Finance">Finance</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label>Keperluan</label>
                                    <textarea id="bf_keperluan" class="form-control form-control-sm" rows="2" placeholder="Jelaskan keperluan request ini..."></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="gr-section-title"><i class="fas fa-cart-plus"></i> Tambah Item</div>
                        <div class="gr-bf-card gr-bf-entry" id="bf_entry">
                            <div class="mb-2">
                                <label>Nama Barang</label>
                                <input type="text" id="bf_nama" class="form-control form-control-sm" placeholder="Contoh: Kertas A4 80gr" list="bf_list_barang" autocomplete="off">
                                <datalist id="bf_list_barang">
                                    @foreach (collect($requests)->pluck('items')->flatten(1)->pluck('nama')->unique() as $nama)
                                        <option value="{{ $nama }}">
                                    @endforeach
                                </datalist>
                            </div>
                            <div class="row">
                                <div class="col-6 mb-2">
                                    <label>Qty</label>
                                    <div class="input-group input-group-sm gr-bf-qty">
                                        <div class="input-group-prepend"><button type="button" class="btn" onclick="stepQty(-1)">&minus;</button></div>
                                        <input type="text" id="bf_qty" class="form-control text-center" placeholder="0" oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                                        <div class="input-group-append"><button type="button" class="btn" onclick="stepQty(1)">+</button></div>
                                    </div>
                                </div>
                                <div class="col-6 mb-2">
                                    <label>Satuan</label>
                                    <select id="bf_satuan" class="form-control form-control-sm bf-select2">
                                        <option value="">-- Pilih --</option>
                                        @foreach ($units as $unit)
                                            <option value="{{ $unit }}">{{ $unit }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="mb-2">
                                <label>Spesifikasi / Keterangan</label>
                                <input type="text" id="bf_ket" class="form-control form-control-sm" placeholder="Merk, ukuran, warna, dll">
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="gr-muted-text"><i class="fas fa-keyboard"></i> <kbd>Enter</kbd> untuk tambah</small>
                                <button type="button" class="btn gr-btn-main btn-sm" onclick="addItem()"><i class="fas fa-plus"></i> Tambah</button>
                            </div>
                        </div>
                    </div>

                    {{-- Kanan: daftar item --}}
                    <div class="col-lg-7 mb-3">
                        <div class="gr-section-title"><i class="fas fa-boxes"></i> Daftar Item <span class="cnt gr-bf-count" id="bf_count">0</span></div>
                        <div class="gr-bf-list" id="bf_items"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer gr-bf-footer">
                <button type="button" class="btn btn-light px-3" onclick="tutupFormRequest()">Batal</button>
                <button type="button" class="btn gr-btn-soft px-3 fw-bold" onclick="simpanRequest('Draft')"><i class="fas fa-save"></i> Simpan Draft</button>
                <button type="button" class="btn gr-btn-main px-3" onclick="simpanRequest('Ajukan')"><i class="fas fa-paper-plane"></i> Ajukan Approval</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('custom-script')
<script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>

<script>
    const requests = @json($requests);
    const statusCfg = @json($statusList);

    // Status per item (tiap item bisa berbeda) + keterangan untuk legend
    const itemStatusCfg = {
        'Draft':             { bg: '#eef1f5', fg: '#6b7687', icon: 'fa-pencil-alt',     ket: 'Request masih draft, belum diajukan' },
        'Menunggu Approval': { bg: '#fbf3df', fg: '#b98a2e', icon: 'fa-hourglass-half', ket: 'Request menunggu persetujuan atasan' },
        'Ditolak':           { bg: '#f9e8ea', fg: '#c0505f', icon: 'fa-times',          ket: 'Request ditolak atasan' },
        'Belum PO':          { bg: '#eef1f5', fg: '#6b7687', icon: 'fa-file-invoice',   ket: 'Sudah disetujui, menunggu dibuatkan PO oleh Purchasing' },
        'Menunggu Barang':   { bg: '#e9f0fa', fg: '#4a6fa5', icon: 'fa-truck',          ket: 'PO sudah dibuat, barang belum datang ke gudang' },
        'Diterima Sebagian': { bg: '#fcefe3', fg: '#c47f45', icon: 'fa-truck-loading',  ket: 'Sebagian barang sudah diterima gudang, masih ada sisa' },
        'Lengkap':           { bg: '#e6f3eb', fg: '#4a8c68', icon: 'fa-check',          ket: 'Semua qty sudah diterima gudang' },
    };

    // Filter item di modal
    let itemFilter = { q: '', status: '' };
    let renderToken = 0;

    let tableRequest;
    let curReq = null;

    $(document).ready(function() {
        $('.select2bs4').select2({ theme: 'bootstrap4', width: '100%' });

        tableRequest = $('#table-request').DataTable({
            dom: 'rtip',
            ordering: false,
            pageLength: 10,
            language: {
                info: 'Menampilkan _START_ - _END_ dari _TOTAL_ request',
                infoEmpty: 'Tidak ada request',
                infoFiltered: '',
                zeroRecords: '<div class="py-4 gr-muted-text"><i class="fas fa-inbox fa-2x d-block mb-2"></i>Tidak ada request yang cocok</div>',
                paginate: { previous: '&lsaquo;', next: '&rsaquo;' }
            },
            drawCallback: animateProgress,
        });

        $('.gr-stat-value').each(function() { countUp(this, parseInt($(this).data('count'))); });

        $('#filter_status').on('change', function() {
            const val = $(this).val();
            tableRequest.column(6).search(val ? '^\\s*' + val + '\\s*$' : '', true, false).draw();
            $('.gr-stat').removeClass('active');
            $(`.gr-stat[data-status="${val}"]`).addClass('active');
        });

        $('.gr-stat').on('click', function() {
            $('#filter_status').val($(this).data('status')).trigger('change');
        });

        $('#filter_search').on('keyup', function() {
            tableRequest.search(this.value).draw();
        });

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
        $({ n: 0 }).animate({ n: target }, {
            duration,
            step: now => $(el).text(Math.round(now) + suffix),
            complete: () => $(el).text(target + suffix),
        });
    }

    function animateProgress() {
        setTimeout(() => {
            $('#table-request .progress-bar[data-width]').each(function() {
                $(this).css('width', $(this).data('width') + '%');
            });
        }, 80);
    }

    function itemStatus(it, reqStatus) {
        if (['Draft', 'Menunggu Approval', 'Ditolak'].includes(reqStatus)) return reqStatus;
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
        $(el).css({ 'stroke-dasharray': k, 'stroke-dashoffset': k });
        setTimeout(() => $(el).css('stroke-dashoffset', k * (1 - persen / 100)), delay);
    }

    /* ================= Baris yang dibuka ================= */

    // Kalimat singkat posisi item saat ini
    function ketItem(it, st) {
        const sisa = it.qty - it.qty_terima;
        const akhir = it.terima.length ? it.terima[it.terima.length - 1].tgl.split(' ')[0] : '';
        return {
            'Draft':             'Request belum diajukan',
            'Menunggu Approval': 'Menunggu persetujuan atasan',
            'Ditolak':           'Request ditolak atasan',
            'Belum PO':          'Sudah disetujui, menunggu dibuatkan PO oleh Purchasing',
            'Menunggu Barang':   `PO ${escapeHtml(it.no_po)} sudah dibuat, barang belum datang`,
            'Diterima Sebagian': `${it.qty_terima} dari ${it.qty} ${it.satuan} sudah diterima gudang, sisa ${sisa} lagi`,
            'Lengkap':           `Semua ${it.qty} ${it.satuan} sudah diterima gudang (${akhir})`,
        }[st];
    }

    function childHtml(req) {
        let html = '<div class="gr-child">';
        req.items.forEach(it => {
            const st = itemStatus(it, req.status);
            const c = itemStatusCfg[st];
            const sisa = it.qty - it.qty_terima;
            const persen = it.qty > 0 ? Math.round(it.qty_terima / it.qty * 100) : 0;
            html += `<div class="gr-child-item" style="--c-bg:${c.bg}; --c-fg:${c.fg}">
                <div class="ic"><i class="fas ${c.icon}"></i></div>
                <div class="nama">
                    ${escapeHtml(it.nama)}
                    <div class="ket"><i class="fas fa-angle-right"></i> ${ketItem(it, st)}</div>
                </div>
                <div>${chip(st, c.bg, c.fg)}</div>
                <div class="angka">${it.qty_terima} / ${it.qty} ${it.satuan}</div>
                <div class="bar"><div class="progress gr-progress"><div class="progress-bar ${persen >= 100 ? 'gr-bar-sage' : 'gr-bar-peach'}" data-width="${persen}"></div></div></div>
                <div class="sisa ${sisa > 0 ? 'gr-sisa-ada' : 'gr-sisa-nol'}">Sisa ${sisa}</div>
            </div>`;
        });
        return html + '</div>';
    }

    function mockAksi(aksi, noRequest, e) {
        e.stopPropagation();
        Swal.fire({
            icon: 'question',
            title: `${aksi}?`,
            html: `${aksi} request <b>${noRequest}</b>?<br><small class="text-muted">(Mockup, belum tersimpan)</small>`,
            showCancelButton: true,
            confirmButtonText: 'Ya',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#4a6fa5',
        }).then(r => {
            if (r.isConfirmed) Swal.fire({ icon: 'success', title: 'Berhasil (mockup)', timer: 1200, showConfirmButton: false });
        });
    }

    function formatTgl(tgl) {
        const [y, m, d] = tgl.split('-');
        return `${d}-${m}-${y}`;
    }

    /* ================= Modal tracking per item ================= */

    function showDetail(index, e) {
        if (e) e.stopPropagation();
        const req = requests[index];
        const cfg = statusCfg[req.status];
        curReq = req;

        // Hero
        $('#md_no_request').text(req.no_request);
        $('#md_keperluan').text(req.keperluan);
        const chips = [
            `<i class="fas ${cfg.icon}"></i> ${req.status}`,
            `<i class="far fa-calendar"></i> Request ${formatTgl(req.tgl_request)}`,
            `<i class="far fa-calendar-check"></i> Dibutuhkan ${formatTgl(req.tgl_butuh)}`,
            `<i class="fas fa-building"></i> ${req.departemen}`,
        ];
        $('#md_chips').html(chips.map((c, i) => `<span class="gr-hero-chip" style="animation-delay:${0.15 + i * 0.08}s">${c}</span>`).join(''));

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
            const berjalan = ['Menunggu Barang', 'Diterima Sebagian', 'Menunggu Approval', 'Belum PO'].includes(st);
            const cari = [it.nama, it.no_po ?? '', ...it.terima.map(t => t.no_bpb)].join(' ').toLowerCase();
            list += `<div class="gr-item-card" data-i="${i}" data-status="${st}" data-cari="${escapeHtml(cari)}" style="--c-bg:${c.bg}; --c-fg:${c.fg}; animation-delay:${0.2 + i * 0.08}s" onclick="selectItem(${i})">
                <div class="gr-mini-ring">${ringSvg(44, 18, 'ring-' + i)}<div class="ic"><i class="fas ${c.icon}"></i></div></div>
                <div style="min-width:0">
                    <div class="nm" data-nama="${escapeHtml(it.nama)}">${escapeHtml(it.nama)}</div>
                    <div class="sub">${it.qty_terima} / ${it.qty} ${it.satuan} diterima</div>
                    <div class="mt-1">${chip(st, c.bg, c.fg, berjalan ? 'pulse' : '')}</div>
                </div>
                <i class="fas fa-chevron-right arrow"></i>
            </div>`;
        });
        $('#md_item_list').html(list);
        req.items.forEach((it, i) => setRing('.ring-' + i, 18, 0, 0));

        // Reset pencarian & legend
        itemFilter = { q: '', status: '' };
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
            req.items.forEach((it, i) => setRing('.ring-' + i, 18, it.qty > 0 ? it.qty_terima / it.qty * 100 : 0, 200 + i * 120));
        }, 350);
    }

    function escapeHtml(t) {
        return $('<div>').text(t ?? '').html();
    }

    /* ---------- Legend status item (sekaligus filter) ---------- */
    function renderLegend(req) {
        // Saat request belum disetujui, semua item statusnya sama dengan request
        const daftar = ['Draft', 'Menunggu Approval', 'Ditolak'].includes(req.status)
            ? [req.status]
            : ['Belum PO', 'Menunggu Barang', 'Diterima Sebagian', 'Lengkap'];

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
        $('#md_legend_ket').html('<i class="fas fa-info-circle"></i> Klik status untuk menyaring item, arahkan kursor untuk melihat artinya');
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
        $('#md_legend_ket').html(`<span style="color:${c.fg}"><i class="fas ${c.icon}"></i> <b>${$(this).data('status')}</b></span> &middot; ${c.ket}`);
    });

    $(document).on('mouseleave', '#md_legend', function() {
        const st = itemFilter.status;
        $('#md_legend_ket').html(st
            ? `<i class="fas fa-filter"></i> Menampilkan item <b>${st}</b> &middot; klik lagi untuk menampilkan semua`
            : '<i class="fas fa-info-circle"></i> Klik status untuk menyaring item, arahkan kursor untuk melihat artinya');
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
        const { q, status } = itemFilter;
        let tampil = 0;

        $('#md_item_list .gr-item-card').each(function() {
            const card = $(this);
            const cocok = (!q || String(card.attr('data-cari')).includes(q)) && (!status || card.attr('data-status') === status);
            card.toggleClass('hide', !cocok);
            if (cocok) tampil++;

            // Sorot teks yang dicari di nama barang
            const nm = card.find('.nm');
            const nama = String(nm.attr('data-nama'));
            if (q && cocok) {
                const idx = nama.toLowerCase().indexOf(q);
                nm.html(idx >= 0
                    ? escapeHtml(nama.slice(0, idx)) + '<mark>' + escapeHtml(nama.slice(idx, idx + q.length)) + '</mark>' + escapeHtml(nama.slice(idx + q.length))
                    : escapeHtml(nama));
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

    function renderApproval(req) {
        const ajukan = req.timeline.find(t => t.judul === 'Request diajukan');
        const setuju = req.timeline.find(t => t.judul === 'Disetujui');
        const tolak  = req.timeline.find(t => t.judul === 'Ditolak');

        const step = (cls, bg, fg, icon, judul, meta) => `<div class="gr-appr-step ${cls}" style="--c-bg:${bg}; --c-fg:${fg}">
            <div class="ic"><i class="fas ${icon}"></i></div>
            <div><div class="t">${judul}</div><div class="m">${meta}</div></div>
        </div>`;
        const arrow = '<i class="fas fa-long-arrow-alt-right gr-appr-arrow"></i>';

        let html = '';
        if (!ajukan) {
            html = step('', '#eef1f5', '#6b7687', 'fa-pencil-alt', 'Masih Draft', 'Belum diajukan untuk approval');
        } else {
            html += step('', '#e6f3eb', '#4a8c68', 'fa-paper-plane', 'Diajukan', `${ajukan.tgl} &middot; ${ajukan.oleh}`);
            html += arrow;
            if (setuju) {
                html += step('', '#e6f3eb', '#4a8c68', 'fa-check', 'Disetujui', `${setuju.tgl} &middot; ${setuju.oleh}`);
            } else if (tolak) {
                html += step('', '#f9e8ea', '#c0505f', 'fa-times', 'Ditolak', `${tolak.tgl} &middot; ${tolak.oleh}`);
                if (tolak.ket) html += `<div class="gr-appr-note"><i class="fas fa-comment-dots"></i> ${tolak.ket}</div>`;
            } else {
                html += step('wait', '#fbf3df', '#b98a2e', 'fa-hourglass-half', 'Menunggu Approval', 'Belum diproses atasan');
            }
        }
        $('#md_approval').html(html);
        // ulangi animasi masuk
        const el = document.getElementById('md_approval');
        el.style.animation = 'none'; void el.offsetWidth; el.style.animation = '';
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
        setTimeout(() => { if (token === renderToken) renderItemDetail(curReq, curReq.items[i]); }, 150);
    }

    function renderItemDetail(req, it) {
        const token = ++renderToken;
        const masihAktif = () => token === renderToken;
        const st = itemStatus(it, req.status);
        const c = itemStatusCfg[st];
        const sisa = it.qty - it.qty_terima;
        const setuju = req.timeline.find(t => t.judul === 'Disetujui');
        const tolak  = req.timeline.find(t => t.judul === 'Ditolak');
        const terimaPertama = it.terima[0];
        const terimaTerakhir = it.terima[it.terima.length - 1];

        // Kepala
        let html = `<div class="gr-d-head" style="--c-fg:${c.fg}">
            <div class="gr-d-icon" id="md_d_icon"><i class="fas ${c.icon}"></i></div>
            <div style="min-width:0">
                <div class="gr-d-name">${it.nama}</div>
                <div class="gr-d-meta">
                    ${chip(st, c.bg, c.fg)}
                    <span class="gr-tag"><i class="fas fa-file-invoice"></i> ${it.no_po ?? 'Belum ada PO'}</span>
                    ${it.terima.length ? `<span class="gr-tag"><i class="fas fa-truck"></i> ${it.terima.length}x pengiriman</span>` : ''}
                </div>
            </div>
        </div>`;

        // Angka
        const tiles = [
            ['Request',  it.qty,        '#4a6fa5', 'fa-clipboard-list'],
            ['Qty PO',   it.qty_po,     '#8a7cc2', 'fa-file-invoice'],
            ['Diterima', it.qty_terima, '#5fa880', 'fa-box-open'],
            ['Sisa',     sisa,          sisa > 0 ? '#d26b78' : '#5fa880', 'fa-hourglass-half'],
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
        const steps = [
            { label: 'Disetujui', icon: 'fa-check',          date: setuju ? setuju.tgl : (tolak ? tolak.tgl : '') },
            { label: 'PO Dibuat', icon: 'fa-file-invoice',   date: it.tgl_po ?? '' },
            { label: 'Diterima',  icon: 'fa-truck-loading',  date: terimaPertama ? terimaPertama.tgl : '' },
            { label: 'Lengkap',   icon: 'fa-flag-checkered', date: sisa <= 0 && terimaTerakhir ? terimaTerakhir.tgl : '' },
        ];
        const posisi = { 'Draft': -1, 'Menunggu Approval': 0, 'Ditolak': 0, 'Belum PO': 1, 'Menunggu Barang': 2, 'Diterima Sebagian': 2, 'Lengkap': 4 }[st];

        html += '<div class="gr-stepper"><div class="gr-line-fill"></div>';
        steps.forEach((s, i) => {
            let label = s.label;
            let icon = s.icon;
            if (st === 'Ditolak' && i === 0) { label = 'Ditolak'; icon = 'fa-times'; }
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
        if (setuju) ev.push({ ic: 'fa-check', bg: '#e6f3eb', fg: '#4a8c68', title: 'Request disetujui', meta: `${setuju.tgl} · ${setuju.oleh}` });
        if (tolak)  ev.push({ ic: 'fa-times', bg: '#f9e8ea', fg: '#c0505f', title: 'Request ditolak', meta: `${tolak.tgl} · ${tolak.oleh}`, ket: tolak.ket });
        if (it.tgl_po) ev.push({ ic: 'fa-file-invoice', bg: '#efecf8', fg: '#8a7cc2', title: `PO dibuat (${it.qty_po} ${it.satuan})`, meta: `${it.tgl_po} · Purchasing`, ket: it.no_po });
        it.terima.forEach((t, i) => ev.push({
            ic: 'fa-truck', bg: '#e6f3eb', fg: '#4a8c68',
            title: `Diterima gudang <span class="gr-tl-qty">+${t.qty} ${it.satuan}</span>`,
            meta: `${t.tgl} · Pengiriman ke-${i + 1}`, ket: t.no_bpb,
        }));
        if (st === 'Lengkap') ev.push({ ic: 'fa-flag-checkered', bg: '#e6f3eb', fg: '#4a8c68', title: 'Item lengkap', meta: terimaTerakhir.tgl });
        ev.reverse();

        const pending = {
            'Draft': 'Request belum diajukan',
            'Menunggu Approval': 'Menunggu approval atasan',
            'Belum PO': 'Menunggu dibuatkan PO oleh Purchasing',
            'Menunggu Barang': `Menunggu barang datang (${sisa} ${it.satuan})`,
            'Diterima Sebagian': `Menunggu sisa ${sisa} ${it.satuan} datang`,
        }[st];
        if (pending) ev.unshift({ pending: true, ic: 'fa-circle-notch', title: pending, meta: 'Proses berikutnya' });

        html += '<div class="gr-section-title mt-2"><i class="fas fa-history"></i> Riwayat Item</div><ul class="gr-timeline">';
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

        $('#md_detail .cu').each(function() { countUp(this, $(this).data('v'), 700); });

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
        setTimeout(() => { if (masihAktif()) $('#md_detail .gr-line-fill').css('width', (isi / (steps.length - 1)) * 75 + '%'); }, 300);

        // Item lengkap -> partikel
        if (st === 'Lengkap') setTimeout(() => { if (masihAktif()) burst('#md_d_icon'); }, 450);
    }

    function burst(sel) {
        const el = $(sel);
        const warna = ['#5fa880', '#7cc39b', '#e9a46a', '#86a5d3', '#f2c08f'];
        for (let i = 0; i < 14; i++) {
            const sudut = (Math.PI * 2 / 14) * i;
            const jarak = 34 + Math.random() * 18;
            $(`<span class="gr-spark" style="background:${warna[i % warna.length]}; --dx:${Math.cos(sudut) * jarak}px; --dy:${Math.sin(sudut) * jarak}px"></span>`).appendTo(el);
        }
        setTimeout(() => el.find('.gr-spark').remove(), 900);
    }

    /* ================= Modal Buat Request (mockup) ================= */
    let bfItems = [];

    $(function() {
        $('.bf-select2').select2({ theme: 'bootstrap4', width: '100%', dropdownParent: $('#modalBuat') });

        $('#bf_entry input').on('keydown', function(e) {
            if (e.key === 'Enter') { e.preventDefault(); addItem(); }
        });

        $('#modalBuat').on('shown.bs.modal', () => $('#bf_nama').trigger('focus'));
    });

    // index = request lama untuk "Request Ulang", kosong = request baru
    function bukaFormRequest(index = null) {
        const butuh = new Date(); butuh.setDate(butuh.getDate() + 7);
        $('#bf_tgl_butuh').val(butuh.toISOString().slice(0, 10));
        $('#bf_nama, #bf_qty, #bf_ket').val('');
        $('#bf_satuan').val('').trigger('change');

        if (index !== null) {
            const req = requests[index];
            bfItems = req.items.map(it => ({ nama: it.nama, qty: it.qty, satuan: it.satuan, ket: '' }));
            $('#bf_keperluan').val(req.keperluan);
            $('#bf_departemen').val(req.departemen).trigger('change');
            $('#bf_label').text('Request Ulang');
            $('#bf_sub').text(`Disalin dari ${req.no_request}, silakan sesuaikan item & qty`);
        } else {
            bfItems = [];
            $('#bf_keperluan').val('');
            $('#bf_label').text('Request Baru');
            $('#bf_sub').text('Isi kebutuhan barang, lalu ajukan untuk approval');
        }

        renderBfItems();
        $('#modalBuat').modal('show');
    }

    function tutupFormRequest() {
        if (bfItems.length === 0) { $('#modalBuat').modal('hide'); return; }
        Swal.fire({
            icon: 'question',
            title: 'Tutup form?',
            text: 'Item yang sudah ditambahkan akan hilang.',
            showCancelButton: true,
            confirmButtonText: 'Tutup',
            cancelButtonText: 'Lanjut isi',
            confirmButtonColor: '#c0505f',
        }).then(r => { if (r.isConfirmed) $('#modalBuat').modal('hide'); });
    }

    function stepQty(n) {
        const v = Math.max(0, parseInt($('#bf_qty').val() || 0) + n);
        $('#bf_qty').val(v || '');
    }

    function shake(el) {
        el.removeClass('gr-shake');
        void el[0].offsetWidth;
        el.addClass('gr-shake');
    }

    function addItem() {
        const nama = $('#bf_nama').val().trim();
        const qty = parseInt($('#bf_qty').val() || 0);
        const satuan = $('#bf_satuan').val();
        const ket = $('#bf_ket').val().trim();

        if (!nama || qty <= 0 || !satuan) {
            shake($('#bf_entry'));
            if (!nama) $('#bf_nama').focus();
            else if (qty <= 0) $('#bf_qty').focus();
            else $('#bf_satuan').select2('open');
            return;
        }

        // Barang + satuan sama -> qty digabung
        const ada = bfItems.findIndex(it => it.nama.toLowerCase() === nama.toLowerCase() && it.satuan === satuan);
        if (ada >= 0) {
            bfItems[ada].qty += qty;
            renderBfItems(ada, 'flash');
            Swal.fire({ toast: true, position: 'top-end', icon: 'info', title: `Qty ${nama} digabung`, timer: 1500, showConfirmButton: false });
        } else {
            bfItems.push({ nama, qty, satuan, ket });
            renderBfItems(bfItems.length - 1, 'baru');
        }

        $('#bf_nama').val('').focus();
        $('#bf_qty, #bf_ket').val('');
        $('#bf_satuan').val('').trigger('change');
    }

    function hapusItem(i) {
        $(`#bf_items .gr-bf-item[data-i="${i}"]`).addClass('keluar');
        setTimeout(() => { bfItems.splice(i, 1); renderBfItems(); }, 260);
    }

    function ubahQty(i, val) {
        const v = parseInt(val || 0);
        if (v > 0) bfItems[i].qty = v;
        else $(`#bf_items .gr-bf-item[data-i="${i}"] .q`).val(bfItems[i].qty);
    }

    function renderBfItems(sorot = null, efek = '') {
        const cnt = $('#bf_count');
        cnt.text(bfItems.length).addClass('bump');
        setTimeout(() => cnt.removeClass('bump'), 250);

        if (bfItems.length === 0) {
            $('#bf_items').html(`<div class="gr-bf-empty"><i class="fas fa-box-open"></i>Belum ada item<br><small>Tambahkan barang dari form di sebelah kiri</small></div>`);
            return;
        }

        $('#bf_items').html(bfItems.map((it, i) => `
            <div class="gr-bf-item ${i === sorot ? efek : ''}" data-i="${i}">
                <div class="no">${i + 1}</div>
                <div class="nm">${escapeHtml(it.nama)}<small>${it.ket ? escapeHtml(it.ket) : 'Tanpa keterangan'}</small></div>
                <input type="text" class="q" value="${it.qty}" oninput="this.value = this.value.replace(/[^0-9]/g, '')" onchange="ubahQty(${i}, this.value)">
                <div class="st">${it.satuan}</div>
                <button type="button" class="del" onclick="hapusItem(${i})" title="Hapus"><i class="fas fa-trash"></i></button>
            </div>`).join(''));
    }

    function simpanRequest(mode) {
        if (bfItems.length === 0) {
            shake($('#bf_entry'));
            Swal.fire('Belum ada item', 'Tambahkan minimal 1 item.', 'warning');
            return;
        }
        if (mode === 'Ajukan' && !$('#bf_keperluan').val().trim()) {
            shake($('#bf_keperluan'));
            $('#bf_keperluan').focus();
            return;
        }

        if (mode === 'Draft') {
            $('#modalBuat').modal('hide');
            Swal.fire({ icon: 'success', title: 'Tersimpan sebagai Draft', html: '<small class="text-muted">(Mockup, belum tersimpan ke database)</small>', timer: 1800, showConfirmButton: false });
            return;
        }

        const list = bfItems.map(it => `<tr><td class="text-left">${escapeHtml(it.nama)}</td><td class="text-right text-nowrap">${it.qty} ${it.satuan}</td></tr>`).join('');
        Swal.fire({
            title: 'Ajukan Request?',
            html: `<div class="small text-left mb-2">
                    <b>Keperluan:</b> ${escapeHtml($('#bf_keperluan').val())}<br>
                    <b>Dibutuhkan:</b> ${$('#bf_tgl_butuh').val().split('-').reverse().join('-')}
                </div>
                <table class="table table-sm table-bordered small mb-0">${list}</table>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-paper-plane"></i> Ajukan',
            cancelButtonText: 'Cek lagi',
            confirmButtonColor: '#4a6fa5',
        }).then(r => {
            if (r.isConfirmed) {
                $('#modalBuat').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'Request diajukan!',
                    html: 'Status: <b>Menunggu Approval</b><br><small class="text-muted">(Mockup, belum tersimpan ke database)</small>',
                    confirmButtonColor: '#4a6fa5',
                });
            }
        });
    }
</script>
@endsection
