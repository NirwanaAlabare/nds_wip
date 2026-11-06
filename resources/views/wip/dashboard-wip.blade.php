@if (!isset($page))
    @php
        $page = '';
    @endphp
@endif

@extends('layouts.index', ["page" => $page, "containerFluid" => true])

@section('custom-link')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">

    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

    <style>
        .wip-hub {
            --hub-accent: #5A67D8;
            --hub-accent-soft: rgba(90, 103, 216, 0.10);
            --hub-text: #1f2937;
            --hub-muted: #6b7280;
            --hub-border: rgba(15, 23, 42, 0.06);
        }

        /* ---------- header section ---------- */
        .hub-section {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            margin: 8px 0 18px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--hub-border);
        }

        .hub-section-title {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 0;
        }

        .hub-section-title .hub-section-icon {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: var(--hub-accent-soft);
            color: var(--hub-accent);
            font-size: 15px;
        }

        .hub-count {
            font-size: 12px;
            font-weight: 600;
            color: var(--hub-accent);
            background: var(--hub-accent-soft);
            border-radius: 999px;
            padding: 3px 10px;
        }

        .hub-search {
            position: relative;
            width: 240px;
            max-width: 100%;
        }

        .hub-search i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--hub-muted);
            font-size: 13px;
            pointer-events: none;
        }

        .hub-search input {
            padding-left: 34px;
            border-radius: 10px;
            border: 1px solid #e5e7eb;
            font-size: 14px;
        }

        .hub-search input:focus {
            border-color: var(--hub-accent);
            box-shadow: 0 0 0 3px var(--hub-accent-soft);
        }

        /* ---------- tile line ---------- */
        .line-tile {
            position: relative;
            display: flex;
            align-items: center;
            gap: 14px;
            height: 100%;
            padding: 16px 18px;
            background: #fff;
            border-radius: 14px;
            border: 1px solid var(--hub-border);
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.05);
            color: var(--hub-text);
            text-decoration: none;
            overflow: hidden;
            transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
        }

        .line-tile::before {
            content: "";
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: var(--hub-accent);
            opacity: 0;
            transition: opacity .2s ease;
        }

        .line-tile:hover,
        .line-tile:focus-visible {
            transform: translateY(-3px);
            box-shadow: 0 10px 24px rgba(90, 103, 216, 0.16);
            border-color: rgba(90, 103, 216, 0.35);
            color: var(--hub-text);
        }

        .line-tile:hover::before,
        .line-tile:focus-visible::before {
            opacity: 1;
        }

        .line-tile-icon {
            flex: 0 0 auto;
            width: 42px;
            height: 42px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: var(--hub-accent-soft);
            color: var(--hub-accent);
            font-size: 17px;
            transition: background .2s ease, color .2s ease;
        }

        .line-tile:hover .line-tile-icon {
            background: var(--hub-accent);
            color: #fff;
        }

        .line-tile-text {
            flex: 1 1 auto;
            min-width: 0;
            line-height: 1.1;
        }

        .line-tile-label {
            display: block;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: var(--hub-muted);
        }

        .line-tile-number {
            display: block;
            font-size: 24px;
            font-weight: 800;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .line-tile-arrow {
            flex: 0 0 auto;
            color: #c4c9d4;
            font-size: 14px;
            transition: color .2s ease, transform .2s ease;
        }

        .line-tile:hover .line-tile-arrow {
            color: var(--hub-accent);
            transform: translate(2px, -2px);
        }

        .hub-empty {
            display: none;
            text-align: center;
            color: var(--hub-muted);
            padding: 28px 0;
        }

        /* ---------- kartu menu dashboard ---------- */
        .menu-card {
            display: flex;
            flex-direction: column;
            gap: 12px;
            width: 100%;
            height: 100%;
            padding: 18px;
            font: inherit;
            cursor: pointer;
            text-align: left;
            background: #fff;
            border-radius: 14px;
            border: 1px solid var(--hub-border);
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.05);
            color: var(--hub-text);
            text-decoration: none;
            transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
        }

        .menu-card:hover,
        .menu-card:focus-visible {
            transform: translateY(-3px);
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.10);
            border-color: rgba(15, 23, 42, 0.12);
            color: var(--hub-text);
        }

        .menu-card-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            color: var(--tone);
            background: var(--tone-soft);
        }

        .menu-card-title {
            font-size: 15px;
            font-weight: 700;
            margin: 0;
        }

        .menu-card-desc {
            font-size: 13px;
            color: var(--hub-muted);
            margin: 0;
            flex: 1 1 auto;
        }

        .menu-card-cta {
            font-size: 13px;
            font-weight: 600;
            color: var(--tone);
        }

        .menu-card-cta i {
            margin-left: 4px;
            transition: transform .2s ease;
        }

        .menu-card:hover .menu-card-cta i {
            transform: translateX(3px);
        }

        .tone-indigo { --tone: #5A67D8; --tone-soft: rgba(90, 103, 216, 0.12); }
        .tone-amber  { --tone: #d97706; --tone-soft: rgba(217, 119, 6, 0.12); }
        .tone-teal   { --tone: #0d9488; --tone-soft: rgba(13, 148, 136, 0.12); }
        .tone-rose   { --tone: #e11d48; --tone-soft: rgba(225, 29, 72, 0.10); }

        /* ---------- hero banner ---------- */
        .hub-hero {
            position: relative;
            overflow: hidden;
            border-radius: 20px;
            padding: 36px 40px;
            margin: 6px 0 28px;
            scroll-margin-top: 90px;
            color: #fff;
            background: linear-gradient(120deg, #4338ca, #6d28d9, #9333ea, #4f46e5);
            background-size: 300% 300%;
            animation: hubGradient 14s ease infinite;
            box-shadow: 0 20px 40px -20px rgba(79, 70, 229, 0.65);
        }

        .hub-blob {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.10);
            pointer-events: none;
            animation: hubFloat 9s ease-in-out infinite;
        }

        .hub-blob.b1 { width: 220px; height: 220px; right: -60px; top: -80px; }
        .hub-blob.b2 { width: 140px; height: 140px; right: 180px; bottom: -70px; animation-delay: -3s; background: rgba(255, 255, 255, 0.07); }
        .hub-blob.b3 { width: 70px; height: 70px; left: 46%; top: 18px; animation-delay: -6s; background: rgba(255, 255, 255, 0.08); }

        .hub-hero-inner {
            position: relative;
            z-index: 1;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 24px;
        }

        .hub-hero-main { flex: 1 1 380px; min-width: 0; }

        .hub-hero-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: .1em;
            text-transform: uppercase;
            padding: 4px 12px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.16);
        }

        .hub-hero-title {
            font-size: 30px;
            font-weight: 800;
            margin: 12px 0 6px;
            color: #fff;
        }

        .hub-hero-sub {
            margin: 0 0 18px;
            opacity: .85;
            max-width: 480px;
        }

        .hub-hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .hub-hero .hub-search { width: 320px; }

        .hub-hero .hub-search i { z-index: 2; color: #6366f1; }

        .hub-hero .hub-search input {
            border: none;
            height: 42px;
            padding-top: 0;
            padding-bottom: 0;
            line-height: 42px;
            color: #1f2937;
            background: rgba(255, 255, 255, 0.96);
            box-shadow: 0 6px 16px -8px rgba(0, 0, 0, 0.35);
        }

        .hub-hero .hub-search input:focus { box-shadow: 0 0 0 4px rgba(255, 255, 255, 0.35); }

        .hub-guide-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            height: 42px;
            padding: 0 16px;
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.35);
            background: rgba(255, 255, 255, 0.12);
            color: #fff;
            font-weight: 600;
            font-size: 14px;
            transition: background .2s ease;
        }

        .hub-guide-btn:hover { background: rgba(255, 255, 255, 0.24); }

        .hub-hero-side { text-align: right; }

        .hub-clock {
            font-size: 38px;
            font-weight: 800;
            line-height: 1;
            font-variant-numeric: tabular-nums;
            letter-spacing: .02em;
        }

        .hub-date {
            margin-top: 6px;
            opacity: .85;
            font-size: 14px;
        }

        .hub-stats {
            display: flex;
            justify-content: flex-end;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 14px;
        }

        .hub-stat {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            font-weight: 600;
            padding: 6px 12px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.16);
            border: 1px solid rgba(255, 255, 255, 0.22);
        }

        @media (max-width: 767.98px) {
            .hub-hero { padding: 26px 22px; }
            .hub-hero-title { font-size: 24px; }
            .hub-hero .hub-search { width: 100%; }
            .hub-hero-side { text-align: left; }
            .hub-stats { justify-content: flex-start; }
            .hub-clock { font-size: 30px; }
        }

        /* ---------- warna gradien ikon tile (bergantian) ---------- */
        .lt-0 { --g1: #6366f1; --g2: #8b5cf6; }
        .lt-1 { --g1: #0ea5e9; --g2: #6366f1; }
        .lt-2 { --g1: #14b8a6; --g2: #0ea5e9; }
        .lt-3 { --g1: #a855f7; --g2: #ec4899; }

        .line-item .line-tile-icon {
            background: linear-gradient(135deg, var(--g1), var(--g2));
            color: #fff;
            box-shadow: 0 6px 14px -6px var(--g1);
        }

        .line-item .line-tile::before { background: linear-gradient(180deg, var(--g1), var(--g2)); }

        .line-item .line-tile:hover .line-tile-icon {
            background: linear-gradient(135deg, var(--g1), var(--g2));
            transform: rotate(-8deg) scale(1.08);
        }

        .line-tile-icon { transition: transform .25s ease; }

        .menu-card:hover { box-shadow: 0 14px 30px -12px var(--tone); }

        /* ---------- animasi masuk bertahap ---------- */
        .hub-rise {
            animation: hubRise .55s cubic-bezier(.2, .8, .2, 1) both;
            animation-delay: min(calc(var(--i, 0) * 35ms), 800ms);
        }

        /* hero: animasi masuk + gradien bergerak (jangan saling menimpa) */
        .hub-hero.hub-rise {
            animation: hubRise .55s cubic-bezier(.2, .8, .2, 1) both, hubGradient 14s ease infinite;
        }

        /* elemen yang di-scroll tur tidak tertutup navbar sticky */
        .line-tile, .menu-card, .hub-guide-btn, .hub-search { scroll-margin-top: 90px; }

        @keyframes hubRise {
            from { opacity: 0; transform: translateY(14px); }
            to { opacity: 1; transform: none; }
        }

        @keyframes hubGradient {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        @keyframes hubFloat {
            0%, 100% { transform: translate(0, 0); }
            50% { transform: translate(-12px, 14px); }
        }

        /* ---------- tur panduan ---------- */
        .tour-overlay {
            position: fixed;
            inset: 0;
            z-index: 2000;
        }

        .tour-spot {
            position: fixed;
            z-index: 2001;
            border-radius: 16px;
            box-shadow: 0 0 0 9999px rgba(15, 23, 42, 0.62);
            pointer-events: none;
            transition: top .4s cubic-bezier(.4, 0, .2, 1), left .4s cubic-bezier(.4, 0, .2, 1),
                        width .4s cubic-bezier(.4, 0, .2, 1), height .4s cubic-bezier(.4, 0, .2, 1);
        }

        .tour-spot::after {
            content: "";
            position: absolute;
            inset: -4px;
            border-radius: 20px;
            border: 2px solid #a5b4fc;
            animation: tourPulse 1.6s ease-out infinite;
        }

        @keyframes tourPulse {
            0% { opacity: .9; transform: scale(1); }
            100% { opacity: 0; transform: scale(1.06); }
        }

        .tour-pop {
            position: fixed;
            z-index: 2002;
            width: 330px;
            max-width: calc(100vw - 32px);
            background: #fff;
            color: #1f2937;
            border-radius: 16px;
            padding: 18px 18px 14px;
            box-shadow: 0 24px 48px -12px rgba(15, 23, 42, 0.45);
            transition: top .4s cubic-bezier(.4, 0, .2, 1), left .4s cubic-bezier(.4, 0, .2, 1);
        }

        .tour-pop.tour-in { animation: tourIn .35s cubic-bezier(.2, .8, .2, 1); }

        @keyframes tourIn {
            from { opacity: 0; transform: translateY(8px) scale(.97); }
            to { opacity: 1; transform: none; }
        }

        .tour-pop-head {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 8px;
        }

        .tour-pop-icon {
            width: 36px;
            height: 36px;
            flex: 0 0 auto;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            background: linear-gradient(135deg, #6366f1, #9333ea);
        }

        .tour-pop-title { font-size: 16px; font-weight: 700; margin: 0; }

        .tour-pop-text { font-size: 14px; color: #4b5563; margin: 0 0 14px; }

        .tour-pop-foot {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }

        .tour-dots { display: flex; gap: 5px; }

        .tour-dots span {
            width: 7px;
            height: 7px;
            border-radius: 999px;
            background: #e5e7eb;
            transition: width .25s ease, background .25s ease;
        }

        .tour-dots span.active { width: 20px; background: #6366f1; }

        .tour-btns { display: flex; gap: 6px; }

        .tour-btns button {
            border: none;
            border-radius: 8px;
            padding: 6px 12px;
            font-size: 13px;
            font-weight: 600;
        }

        .tour-skip { background: transparent; color: #6b7280; }
        .tour-prev { background: #f3f4f6; color: #374151; }
        .tour-next { background: linear-gradient(135deg, #6366f1, #9333ea); color: #fff; }

        @media (prefers-reduced-motion: reduce) {
            .line-tile, .menu-card, .line-tile-arrow, .menu-card-cta i { transition: none; }
            .line-tile:hover, .menu-card:hover { transform: none; }
            .hub-hero, .hub-hero.hub-rise, .hub-blob, .hub-rise, .tour-spot::after, .tour-pop.tour-in { animation: none; }
            .tour-spot, .tour-pop { transition: none; }
        }

        .btn-primary {
            background-color: #5A67D8;
            border: none;
        }

        .btn-primary:hover {
            background-color: #434190;
        }

        table thead th {
            vertical-align: middle;
            text-align: center;
        }
    </style>
@endsection

@section('content')
    <div class="wip-hub px-xl-3 mb-4">
        <div class="row justify-content-start align-items-stretch">
            <div class="col-12">
                <div class="hub-hero hub-rise" id="hub-hero">
                    <span class="hub-blob b1"></span>
                    <span class="hub-blob b2"></span>
                    <span class="hub-blob b3"></span>
                    <div class="hub-hero-inner">
                        <div class="hub-hero-main">
                            <span class="hub-hero-eyebrow"><i class="fa-solid fa-bolt"></i> Sewing Monitoring</span>
                            <h2 class="hub-hero-title">WIP Dashboard Hub</h2>
                            <p class="hub-hero-sub">Pantau output tiap line, performa tim, dan ringkasan factory dari satu tempat.</p>
                            <div class="hub-hero-actions">
                                <div class="hub-search" id="tour-search">
                                    <i class="fa-solid fa-magnifying-glass"></i>
                                    <input type="search" class="form-control" id="line-search" placeholder="Cari line, misal 05..." autocomplete="off">
                                </div>
                                <button type="button" class="hub-guide-btn" id="hub-guide-btn">
                                    <i class="fa-solid fa-circle-question"></i> Panduan
                                </button>
                            </div>
                        </div>
                        <div class="hub-hero-side" id="tour-clock">
                            <div class="hub-clock" id="hub-clock">--:--:--</div>
                            <div class="hub-date" id="hub-date">&nbsp;</div>
                            <div class="hub-stats">
                                <span class="hub-stat"><i class="fa-solid fa-shirt"></i> {{ count($lines) }} Line</span>
                                <span class="hub-stat"><i class="fa-solid fa-gauge-high"></i> <span id="hub-dash-count">0</span> Dashboard</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="hub-section">
                    <h4 class="hub-section-title text-sb fw-bold">
                        <span class="hub-section-icon"><i class="fa-solid fa-shirt"></i></span>
                        Sewing Line Dashboard
                        <span class="hub-count" id="line-count">{{ count($lines) }} line</span>
                    </h4>
                </div>
            </div>
            @foreach ($lines as $line)
                @php
                    $lineLabel = trim(str_replace("SEWING ", "", $line->name));
                    $lineNumber = preg_match('/^LINE\s+(.+)$/i', $lineLabel, $lineMatch) ? $lineMatch[1] : $lineLabel;
                @endphp
                <div class="col-6 col-md-4 col-lg-3 col-xxl-2 mb-3 line-item hub-rise lt-{{ $loop->index % 4 }}" style="--i: {{ $loop->index }}" data-name="{{ strtolower($lineLabel) }}">
                    <a href="{{ url('dashboard-wip/wip-line-smart/' . $line->id) }}" target="_blank" class="line-tile" title="Buka dashboard {{ $lineLabel }}">
                        <span class="line-tile-icon"><i class="fa-solid fa-chart-column"></i></span>
                        <span class="line-tile-text">
                            <span class="line-tile-label">Line</span>
                            <span class="line-tile-number">{{ $lineNumber }}</span>
                        </span>
                        <i class="fa-solid fa-arrow-up-right-from-square line-tile-arrow"></i>
                    </a>
                </div>
            @endforeach
            <div class="col-12 hub-empty" id="line-empty">
                <i class="fa-solid fa-circle-info me-1"></i> Line tidak ditemukan
            </div>

            <div class="col-12 mt-3">
                <div class="hub-section">
                    <h4 class="hub-section-title text-sb fw-bold">
                        <span class="hub-section-icon"><i class="fa-solid fa-users"></i></span>
                        Sewing Team Dashboard
                    </h4>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3 mb-4">
                <button type="button" id="tour-team" data-bs-toggle="modal" data-bs-target="#factoryPerformanceDashboard" class="menu-card tone-indigo">
                    <span class="menu-card-icon"><i class="fa-solid fa-chart-line"></i></span>
                    <h5 class="menu-card-title">Factory Performance</h5>
                    <p class="menu-card-desc">Performa factory per bulan, pilih tahun &amp; bulan.</p>
                    <span class="menu-card-cta">Pilih periode<i class="fa-solid fa-arrow-right"></i></span>
                </button>
            </div>
            {{-- <div class="col-md-4 col-lg-3 mb-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body text-center">
                        <h5 class="card-title fw-bold">CHIEF</h5>
                        <button type="button" data-bs-toggle="modal" data-bs-target="#chiefDashboard" class="btn btn-primary btn-sm">Details</button>
                    </div>
                </div>
            </div> --}}
            {{-- <div class="col-md-4 col-lg-3 mb-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body text-center">
                        <h5 class="card-title fw-bold">CHIEF RANGE</h5>
                        <button type="button" data-bs-toggle="modal" data-bs-target="#chiefRangeDashboard" class="btn btn-primary btn-sm">Details</button>
                    </div>
                </div>
            </div> --}}
            {{-- <div class="col-md-4 col-lg-3 mb-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body text-center">
                        <h5 class="card-title fw-bold">LINE SUPPORT</h5>
                        <button type="button" data-bs-toggle="modal" data-bs-target="#lineSupportDashboard" class="btn btn-primary btn-sm">Details</button>
                    </div>
                </div>
            </div> --}}
            <div class="col-sm-6 col-lg-3 mb-4">
                <a href="{{ route('dashboard-chief-leader-sewing') }}" target="_blank" class="menu-card tone-amber">
                    <span class="menu-card-icon"><i class="fa-solid fa-ranking-star"></i></span>
                    <h5 class="menu-card-title">Ranking Chief Leader</h5>
                    <p class="menu-card-desc">Peringkat chief &amp; leader sewing.</p>
                    <span class="menu-card-cta">Buka<i class="fa-solid fa-arrow-right"></i></span>
                </a>
            </div>

            <div class="col-12 mt-2">
                <div class="hub-section">
                    <h4 class="hub-section-title text-sb fw-bold">
                        <span class="hub-section-icon"><i class="fa-solid fa-industry"></i></span>
                        Sewing Factory Dashboard
                    </h4>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3 mb-4">
                <a href="http://10.10.5.62:8000/dashboard-wip/factory" target="_blank" id="tour-factory" class="menu-card tone-teal">
                    <span class="menu-card-icon"><i class="fa-solid fa-industry"></i></span>
                    <h5 class="menu-card-title">Factory Dashboard</h5>
                    <p class="menu-card-desc">Ringkasan output seluruh line sewing.</p>
                    <span class="menu-card-cta">Buka<i class="fa-solid fa-arrow-right"></i></span>
                </a>
            </div>
            <div class="col-sm-6 col-lg-3 mb-4">
                <a href="http://10.10.5.62:8000/dashboard-wip/factory_defect" target="_blank" class="menu-card tone-rose">
                    <span class="menu-card-icon"><i class="fa-solid fa-triangle-exclamation"></i></span>
                    <h5 class="menu-card-title">Factory Defect Dashboard</h5>
                    <p class="menu-card-desc">Ringkasan defect seluruh line sewing.</p>
                    <span class="menu-card-cta">Buka<i class="fa-solid fa-arrow-right"></i></span>
                </a>
            </div>
            {{-- <div class="col-md-4 col-lg-3 mb-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body text-center">
                        <div class="d-flex justify-content-between align-items-center h-100 gap-3">
                            <h5 class="card-title fw-bold">FACTORY DAILY PERFORMANCE</h5>
                            <button type="button" data-bs-toggle="modal" data-bs-target="#factoryDailyPerformance" class="btn btn-primary btn-sm">Details</button>
                        </div>
                    </div>
                </div>
            </div> --}}
            {{-- <div class="col-12">
                <h4 class="text-sb fw-bold mb-3">Employee of the Month Dashboard</h4>
                <hr style="color: #222">
            </div>
            <div class="col-md-4 col-lg-3 mb-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body text-center">
                        <div class="d-flex justify-content-between align-items-center h-100 gap-3">
                            <h5 class="card-title fw-bold">CHIEF OF THE MONTH</h5>
                            <button type="button" data-bs-toggle="modal" data-bs-target="#topChiefDashboard" class="btn btn-primary btn-sm">Details</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-lg-3 mb-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body text-center">
                        <div class="d-flex justify-content-between align-items-center h-100 gap-3">
                            <h5 class="card-title fw-bold">LEADER OF THE MONTH</h5>
                            <button type="button" data-bs-toggle="modal" data-bs-target="#topLeaderDashboard" class="btn btn-primary btn-sm">Details</button>
                        </div>
                    </div>
                </div>
            </div> --}}

            <!-- Modal -->
            <div class="modal fade" id="factoryPerformanceDashboard" tabindex="-1" aria-labelledby="factoryPerformanceDashboardLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="factoryPerformanceDashboardLabel">Visit Factory Performance Dashboard</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <select class="select2bs4factoryperformance" name="factory-performance-year" id="factory-performance-year">
                                        @foreach ($years as $y)
                                            <option value="{{ $y }}">{{ $y }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <select class="select2bs4factoryperformance" name="factory-performance-month" id="factory-performance-month">
                                        @foreach ($months as $m)
                                            <option value="{{ $m['angka'] }}" {{ $m['angka'] == date("m") ? "selected" : "" }}>{{ $m['nama'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" onclick="visitFactoryPerformanceDashboard()">Visit <i class="fa fa-share"></i></button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal -->
            <div class="modal fade" id="chiefDashboard" tabindex="-1" aria-labelledby="chiefDashboardLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="chiefDashboardLabel">Visit Dashboard Chief</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <select class="select2bs4chief" name="chief-year" id="chief-year">
                                        @foreach ($years as $y)
                                            <option value="{{ $y }}">{{ $y }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <select class="select2bs4chief" name="chief-month" id="chief-month">
                                        @foreach ($months as $m)
                                            <option value="{{ $m['angka'] }}" {{ $m['angka'] == date("m") ? "selected" : "" }}>{{ $m['nama'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" onclick="visitDashboardChief()">Visit <i class="fa fa-share"></i></button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal -->
            <div class="modal fade" id="chiefRangeDashboard" tabindex="-1" aria-labelledby="chiefRangeDashboardLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="chiefRangeDashboardLabel">Visit Dashboard Chief Range</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <input type="date" class="form-control" id="chief-range-dateFrom" name="chief-range-dateFrom" value="{{ date("Y-m-d") }}">
                                </div>
                                <div class="col-md-6">
                                    <input type="date" class="form-control" id="chief-range-dateTo" name="chief-range-dateTo" value="{{ date("Y-m-d") }}">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" onclick="visitDashboardChiefRange()">Visit <i class="fa fa-share"></i></button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal -->
            <div class="modal fade" id="lineSupportDashboard" tabindex="-1" aria-labelledby="lineSupportDashboardLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="lineSupportDashboardLabel">Visit Line Support Range</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <select class="select2bs4linesupport" name="line-support-year" id="line-support-year">
                                        @foreach ($years as $y)
                                            <option value="{{ $y }}">{{ $y }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <select class="select2bs4linesupport" name="line-support-month" id="line-support-month">
                                        @foreach ($months as $m)
                                            <option value="{{ $m['angka'] }}" {{ $m['angka'] == date("m") ? "selected" : "" }}>{{ $m['nama'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" onclick="visitDashboardSupportLine()">Visit <i class="fa fa-share"></i></button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal -->
            <div class="modal fade" id="factoryDailyPerformance" tabindex="-1" aria-labelledby="factoryDailyPerformanceLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="factoryDailyPerformanceLabel">Visit Factory Daily Range</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <select class="select2bs4factorydaily" name="factory-daily-year" id="factory-daily-year">
                                        @foreach ($years as $y)
                                            <option value="{{ $y }}">{{ $y }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <select class="select2bs4factorydaily" name="factory-daily-month" id="factory-daily-month">
                                        @foreach ($months as $m)
                                            <option value="{{ $m['angka'] }}" {{ $m['angka'] == date("m") ? "selected" : "" }}>{{ $m['nama'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" onclick="visitFactoryDailyPerformance()">Visit <i class="fa fa-share"></i></button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal -->
            <div class="modal fade" id="topChiefDashboard" tabindex="-1" aria-labelledby="topChiefDashboardLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="topChiefDashboardLabel">Visit Dashboard Chief</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <select class="select2bs4topchief" name="topchief-year" id="topchief-year">
                                        @foreach ($years as $y)
                                            <option value="{{ $y }}">{{ $y }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <select class="select2bs4topchief" name="topchief-month" id="topchief-month">
                                        @foreach ($months as $m)
                                            <option value="{{ $m['angka'] }}" {{ $m['angka'] == date("m") ? "selected" : "" }}>{{ $m['nama'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" onclick="visitDashboardTopChief()">Visit <i class="fa fa-share"></i></button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal -->
            <div class="modal fade" id="topLeaderDashboard" tabindex="-1" aria-labelledby="topLeaderDashboardLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="topLeaderDashboardLabel">Visit Dashboard Leader</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <select class="select2bs4topleader" name="topleader-year" id="topleader-year">
                                        @foreach ($years as $y)
                                            <option value="{{ $y }}">{{ $y }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <select class="select2bs4topleader" name="topleader-month" id="topleader-month">
                                        @foreach ($months as $m)
                                            <option value="{{ $m['angka'] }}" {{ $m['angka'] == date("m") ? "selected" : "" }}>{{ $m['nama'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" onclick="visitDashboardTopLeader()">Visit <i class="fa fa-share"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('custom-script')
    <!-- DataTables  & Plugins -->
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>

    <!-- Select2 -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>

    <!-- Page specific script -->
    <script>
        $('.select2').select2()
        $('.select2bs4').select2({
            theme: 'bootstrap4'
        })
        $('.select2bs4chief').select2({
            theme: 'bootstrap4',
            dropdownParent: $('#chiefDashboard')
        })
        $('.select2bs4linesupport').select2({
            theme: 'bootstrap4',
            dropdownParent: $('#lineSupportDashboard')
        })
        $('.select2bs4factorydaily').select2({
            theme: 'bootstrap4',
            dropdownParent: $('#factoryDailyPerformance')
        })
        $('.select2bs4topchief').select2({
            theme: 'bootstrap4',
            dropdownParent: $('#topChiefDashboard')
        })
        $('.select2bs4topleader').select2({
            theme: 'bootstrap4',
            dropdownParent: $('#topLeaderDashboard')
        })
        $('.select2bs4factoryperformance').select2({
            theme: 'bootstrap4',
            dropdownParent: $('#factoryPerformanceDashboard')
        })

        // filter kartu line
        $("#line-search").on("input", function () {
            const keyword = $(this).val().toLowerCase().trim();
            let visible = 0;

            $(".line-item").each(function () {
                const match = $(this).data("name").toString().indexOf(keyword) !== -1;
                $(this).toggle(match);
                if (match) visible++;
            });

            $("#line-count").text(visible + " line");
            $("#line-empty").toggle(visible === 0);
        });

        // animasi masuk kartu menu & jumlah dashboard
        $(".menu-card").each(function (i) {
            const col = this.parentElement;
            col.style.setProperty("--i", i + 4);
            col.classList.add("hub-rise");
        });
        $("#hub-dash-count").text($(".menu-card").length);

        // jam & tanggal live di hero
        function updateHubClock() {
            const now = new Date();
            $("#hub-clock").text(now.toLocaleTimeString("id-ID", { hour: "2-digit", minute: "2-digit", second: "2-digit" }).replace(/\./g, ":"));
            $("#hub-date").text(now.toLocaleDateString("id-ID", { weekday: "long", day: "numeric", month: "long", year: "numeric" }));
        }
        updateHubClock();
        setInterval(updateHubClock, 1000);

        // ---------- tur panduan ----------
        const hubTour = (function () {
            const TOUR_KEY = "wipHubTourDone";
            const steps = [
                { el: "#hub-hero", icon: "fa-hand-sparkles", title: "Selamat datang!", text: "Ini WIP Dashboard Hub, tempat untuk membuka semua dashboard sewing dari satu halaman." },
                { el: "#tour-search", icon: "fa-magnifying-glass", title: "Cari line", text: "Ketik nomor line, misalnya 05, dan daftar line langsung tersaring." },
                { el: ".line-item:visible:first .line-tile", icon: "fa-chart-column", title: "Buka dashboard line", text: "Klik kartu line mana saja untuk membuka dashboard smart line di tab baru." },
                { el: "#tour-team", icon: "fa-users", title: "Dashboard tim", text: "Factory Performance meminta tahun dan bulan dulu, lalu klik Visit." },
                { el: "#tour-factory", icon: "fa-industry", title: "Dashboard factory", text: "Ringkasan output dan defect seluruh line ada di bagian ini." },
                { el: "#hub-guide-btn", icon: "fa-circle-question", title: "Lihat lagi kapan saja", text: "Panduan ini bisa dibuka lagi lewat tombol Panduan." }
            ];
            let index = 0;
            let $overlay, $spot, $pop;

            function target() {
                return $(steps[index].el).first();
            }

            function place() {
                const $t = target();
                if (!$t.length) return;

                const r = $t[0].getBoundingClientRect();
                const pad = 8;
                $spot.css({ top: r.top - pad, left: r.left - pad, width: r.width + pad * 2, height: r.height + pad * 2 });

                const vw = window.innerWidth, vh = window.innerHeight;
                const pw = $pop.outerWidth(), ph = $pop.outerHeight();
                let top = r.bottom + pad + 14;
                if (top + ph > vh - 16) top = r.top - pad - 14 - ph;
                if (top < 16) top = Math.max(16, (vh - ph) / 2);
                const left = Math.min(Math.max(16, r.left), vw - pw - 16);
                $pop.css({ top: top, left: left });
            }

            function render() {
                const s = steps[index];
                const dots = steps.map((_, i) => '<span class="' + (i === index ? "active" : "") + '"></span>').join("");
                const last = index === steps.length - 1;

                $pop.html(
                    '<div class="tour-pop-head"><span class="tour-pop-icon"><i class="fa-solid ' + s.icon + '"></i></span>' +
                    '<h6 class="tour-pop-title">' + s.title + '</h6></div>' +
                    '<p class="tour-pop-text">' + s.text + '</p>' +
                    '<div class="tour-pop-foot"><div class="tour-dots">' + dots + '</div><div class="tour-btns">' +
                    (last ? "" : '<button type="button" class="tour-skip">Lewati</button>') +
                    (index > 0 ? '<button type="button" class="tour-prev">Kembali</button>' : "") +
                    '<button type="button" class="tour-next">' + (last ? "Selesai" : "Lanjut") + '</button>' +
                    '</div></div>'
                );
                $pop.removeClass("tour-in");
                void $pop[0].offsetWidth;
                $pop.addClass("tour-in");

                const $t = target();
                if ($t.length) $t[0].scrollIntoView({ behavior: "smooth", block: "center" });
                place();
                setTimeout(place, 450);
            }

            function go(step) {
                if (step < 0) return;
                if (step >= steps.length) return end();
                index = step;
                render();
            }

            function onKey(e) {
                if (e.key === "Escape") end();
                else if (e.key === "ArrowRight" || e.key === "Enter") go(index + 1);
                else if (e.key === "ArrowLeft") go(index - 1);
            }

            function start() {
                if ($overlay) end();
                $("#line-search").val("").trigger("input");

                $overlay = $('<div class="tour-overlay"></div>').appendTo("body");
                $spot = $('<div class="tour-spot"></div>').appendTo("body");
                $pop = $('<div class="tour-pop" role="dialog" aria-live="polite"></div>').appendTo("body");

                $pop.on("click", ".tour-next", () => go(index + 1));
                $pop.on("click", ".tour-prev", () => go(index - 1));
                $pop.on("click", ".tour-skip", end);
                $(window).on("resize.hubtour scroll.hubtour", place);
                $(document).on("keydown.hubtour", onKey);

                go(0);
            }

            function end() {
                if (!$overlay) return;
                $overlay.remove(); $spot.remove(); $pop.remove();
                $overlay = $spot = $pop = null;
                $(window).off(".hubtour");
                $(document).off(".hubtour");
                try { localStorage.setItem(TOUR_KEY, "1"); } catch (e) {}
            }

            function autoStart() {
                let done = false;
                try { done = localStorage.getItem(TOUR_KEY) === "1"; } catch (e) {}
                if (!done) setTimeout(start, 900);
            }

            return { start: start, autoStart: autoStart };
        })();

        $("#hub-guide-btn").on("click", hubTour.start);
        hubTour.autoStart();

        $(function () {
            $("#datatable").DataTable({
                "responsive": true, "autoWidth": false,
            });

            $("#datatable-1").DataTable({
                "responsive": true, "autoWidth": false,
            })

            $("#datatable-2").DataTable({
                "responsive": true, "autoWidth": false,
            })

            $("#datatable-3").DataTable({
                "responsive": true, "autoWidth": false,
            })

            $("#datatable-4").DataTable({
                "responsive": true, "autoWidth": false,
            })
        });

        function visitFactoryPerformanceDashboard() {
            window.open("{{ route("dashboard-factory-performance") }}/"+$("#factory-performance-year").val()+"/"+$("#factory-performance-month").val(), '_blank');
            // window.open("http://10.10.5.62:8000/nds_wip/public/index.php/dashboard-wip/chief-sewing/"+$("#year").val()+"/"+$("#month").val(), '_blank');
        }

        function visitDashboardChief() {
            window.open("{{ route("dashboard-chief-sewing") }}/"+$("#chief-year").val()+"/"+$("#chief-month").val(), '_blank');
            // window.open("http://10.10.5.62:8000/nds_wip/public/index.php/dashboard-wip/chief-sewing/"+$("#year").val()+"/"+$("#month").val(), '_blank');
        }

        function visitDashboardChiefRange() {
            window.open("{{ route("dashboard-chief-sewing-range") }}/"+$("#chief-range-dateFrom").val()+"/"+$("#chief-range-dateTo").val(), '_blank');
            // window.open("http://10.10.5.62:8000/nds_wip/public/index.php/dashboard-wip/chief-sewing/"+$("#year").val()+"/"+$("#month").val(), '_blank');
        }

        function visitDashboardSupportLine() {
            window.open("{{ route("dashboard-support-line-sewing") }}/"+$("#line-support-year").val()+"/"+$("#line-support-month").val(), '_blank');
            // window.open("http://10.10.5.62:8000/nds_wip/public/index.php/dashboard-wip/chief-sewing/"+$("#year").val()+"/"+$("#month").val(), '_blank');
        }

        function visitFactoryDailyPerformance() {
            window.open("{{ route("dashboard-factory-daily-sewing") }}/"+$("#factory-daily-year").val()+"/"+$("#factory-daily-month").val(), '_blank');
            // window.open("http://10.10.5.62:8000/nds_wip/public/index.php/dashboard-wip/chief-sewing/"+$("#year").val()+"/"+$("#month").val(), '_blank');
        }

        function visitDashboardTopChief() {
            window.open("{{ route("dashboard-top-chief-sewing") }}/"+$("#topchief-year").val()+"/"+$("#topchief-month").val(), '_blank');
            // window.open("http://10.10.5.62:8000/nds_wip/public/index.php/dashboard-wip/chief-sewing/"+$("#year").val()+"/"+$("#month").val(), '_blank');
        }

        function visitDashboardTopLeader() {
            window.open("{{ route("dashboard-top-leader-sewing") }}/"+$("#topleader-year").val()+"/"+$("#topleader-month").val(), '_blank');
            // window.open("http://10.10.5.62:8000/nds_wip/public/index.php/dashboard-wip/chief-sewing/"+$("#year").val()+"/"+$("#month").val(), '_blank');
        }
    </script>
@endsection
