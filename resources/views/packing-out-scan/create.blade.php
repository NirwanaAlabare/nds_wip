@extends('layouts.index')

@section('custom-link')
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

    <style type="text/css">
        .ps-app {
            --ink: #1d2433;
            --muted: #6b7385;
            --line: #ebe7de;
            --soft: #f7f5f0;
            --pri: #4057e3;
            --pri-bg: #eef1ff;
            --ok: #1a9d63;
            --ok-bg: #e7f7ee;
            --ok-line: #b9e8cf;
            --wip: #e39a12;
            --wip-ink: #9a6400;
            --wip-bg: #fff5de;
            --wip-line: #f3d690;
            --err: #e0483e;
            --err-bg: #fdecea;
            --empty: #e4e1d8;

            color: var(--ink);
            font-variant-numeric: tabular-nums;
        }

        .ps-app button {
            cursor: pointer;
        }

        /* Layout */
        .ps-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.3fr) minmax(380px, 1fr);
            gap: 16px;
            align-items: start;
        }

        .ps-col {
            display: flex;
            flex-direction: column;
            gap: 16px;
            min-width: 0;
        }

        .ps-card {
            padding: 18px;
            border: 1px solid var(--line);
            border-radius: 20px;
            background: #fff;
            box-shadow: 0 1px 0 rgba(0, 0, 0, .02), 0 12px 30px -14px rgba(40, 40, 60, .18);
        }

        .ps-row {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .ps-sp {
            flex: 1;
        }

        .ps-title {
            font-size: 16px;
            font-weight: 900;
        }

        .ps-label {
            font-size: 12px;
            font-weight: 800;
            color: var(--muted);
            letter-spacing: .02em;
        }

        .ps-none {
            padding: 24px;
            text-align: center;
            color: var(--muted);
            font-weight: 700;
            font-size: 13px;
        }

        /* Scan card */
        .ps-scan-card {
            border: 2px solid var(--pri);
            transition: border-color .15s, box-shadow .15s;
        }

        .ps-scan-card.ok {
            border-color: var(--ok);
            box-shadow: 0 0 0 6px var(--ok-bg);
        }

        .ps-scan-card.err {
            border-color: var(--err);
            box-shadow: 0 0 0 6px var(--err-bg);
            animation: ps-shake .3s;
        }

        @keyframes ps-shake {
            25% { transform: translateX(-5px); }
            50% { transform: translateX(5px); }
            75% { transform: translateX(-3px); }
        }

        .ps-po-row {
            display: grid;
            grid-template-columns: minmax(0, 1.4fr) minmax(0, 1fr);
            gap: 10px;
            margin-top: 10px;
        }

        .ps-po-in {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 0;
            padding: 8px 14px;
            border: 2px solid transparent;
            border-radius: 14px;
            background: var(--soft);
            cursor: pointer;
        }

        .ps-po-in:focus-within {
            border-color: var(--pri-bg);
            background: #fff;
        }

        .ps-po-in > span {
            font-size: 11px;
            font-weight: 800;
            color: var(--muted);
            letter-spacing: .04em;
        }

        .ps-po-in .select2-container {
            flex: 1;
            min-width: 0;
        }

        .ps-po-in .select2-container--bootstrap4 .select2-selection {
            border: 0;
            background: transparent;
            box-shadow: none;
            font-weight: 900;
            font-size: 16px;
        }

        .ps-po-in .select2-container--bootstrap4 .select2-selection__rendered {
            padding-left: 0;
        }

        .ps-scan-box {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }

        .ps-scan-input {
            flex: 1;
            min-width: 0;
            font-size: 30px;
            font-weight: 900;
            padding: 16px 20px;
            border: 2px solid transparent;
            background: var(--soft);
            border-radius: 16px;
            outline: none;
            letter-spacing: .03em;
        }

        .ps-scan-input:focus {
            border-color: var(--pri-bg);
            background: #fff;
        }

        .ps-scan-input::placeholder {
            color: #b3b6bf;
            font-weight: 700;
            font-size: 19px;
            letter-spacing: 0;
        }

        .ps-scan-btn {
            border: 0;
            border-radius: 16px;
            padding: 0 28px;
            background: var(--pri);
            color: #fff;
            font-weight: 900;
            font-size: 17px;
        }

        .ps-scan-btn:active {
            transform: scale(.97);
        }

        /* Loading saat scan sedang disimpan */
        .ps-scan-btn:disabled {
            opacity: .75;
            cursor: wait;
        }

        .ps-scan-card.saving .ps-scan-input {
            opacity: .6;
            cursor: wait;
        }

        .ps-spin {
            display: inline-block;
            width: 16px;
            height: 16px;
            margin-right: 6px;
            border: 2px solid rgba(255, 255, 255, .4);
            border-top-color: #fff;
            border-radius: 50%;
            vertical-align: -3px;
            animation: ps-spin .7s linear infinite;
        }

        @keyframes ps-spin {
            to { transform: rotate(360deg); }
        }

        .ps-opts {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px;
            margin-top: 12px;
        }

        .ps-opts:empty {
            display: none;
        }

        .ps-mode-hint {
            flex-basis: 100%;
            padding: 7px 11px;
            border-radius: 10px;
            background: var(--pri-bg);
            font-size: 12px;
            font-weight: 600;
            color: var(--muted);
        }

        .ps-mode-hint b {
            color: var(--pri);
        }

        /* Chip (mode & filter) */
        .ps-chip {
            border: 1px solid var(--line);
            background: #fff;
            border-radius: 999px;
            padding: 7px 13px;
            font-size: 13px;
            font-weight: 800;
            color: var(--ink);
        }

        .ps-chip:hover {
            border-color: #cfcabd;
        }

        .ps-chip.on {
            background: var(--ink);
            border-color: var(--ink);
            color: #fff;
        }

        .ps-chip .n {
            opacity: .55;
            margin-left: 3px;
        }

        /* Karton aktif */
        .ps-cc-head {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .ps-cc-lbl {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .ps-cc-no b {
            display: block;
            font-size: 60px;
            line-height: 1;
            font-weight: 900;
            letter-spacing: -.02em;
            margin: 2px 0;
        }

        .ps-cc-no small {
            font-size: 12px;
            color: var(--muted);
            font-weight: 700;
        }

        .ps-cc-ket {
            flex: 1 1 200px;
            min-width: 0;
            padding: 0 8px;
            text-align: center;
            font-size: clamp(18px, 1.8vw, 28px);
            line-height: 1.2;
            font-weight: 900;
            color: var(--ink);
            word-break: keep-all;
        }

        /* Tipe pack & keterangan: masing-masing dengan label kecil, dipisah garis */
        .ps-cc-ket {
            display: flex;
            align-items: stretch;
            justify-content: center;
        }

        .ps-cc-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 4px;
            min-width: 0;
            padding: 0 14px;
        }

        .ps-cc-item .ps-label { font-size: 11px; white-space: nowrap; }

        .ps-cc-item + .ps-cc-item {
            border-left: 2px dashed var(--line);
        }

        .ps-cc-stat {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 12px 22px 12px 12px;
            border-radius: 18px;
            background: var(--soft);
        }

        .ps-ring {
            position: relative;
            flex: none;
            width: 92px;
            height: 92px;
        }

        .ps-ring svg {
            width: 100%;
            height: 100%;
        }

        .ps-ring .rb {
            fill: none;
            stroke: #fff;
            stroke-width: 10;
        }

        .ps-ring .rf {
            fill: none;
            stroke: var(--wip);
            stroke-width: 10;
            stroke-linecap: round;
            transition: stroke-dashoffset .35s;
        }

        .ps-ring.full .rf {
            stroke: var(--ok);
        }

        .ps-ring .rt {
            position: absolute;
            inset: 0;
            display: grid;
            place-content: center;
            text-align: center;
        }

        .ps-ring .rt b {
            font-size: 19px;
            font-weight: 900;
            line-height: 1;
        }

        .ps-ring .rt span {
            font-size: 11px;
            color: var(--muted);
            font-weight: 800;
        }

        .ps-big-left span {
            display: block;
            font-size: 12px;
            font-weight: 800;
            color: var(--muted);
        }

        .ps-big-left b {
            display: block;
            font-size: 40px;
            font-weight: 900;
            line-height: 1.05;
            color: var(--wip-ink);
        }

        .ps-big-left b small {
            font-size: 16px;
            font-weight: 800;
            color: var(--muted);
        }

        .ps-big-left.full b { color: var(--ok); }
        .ps-big-left.empty b { color: var(--ink); }

        .ps-big-left em {
            display: block;
            margin-top: 2px;
            font-style: normal;
            font-size: 12px;
            font-weight: 700;
            color: var(--muted);
        }

        .ps-app .ps-pill {
            display: inline-flex;
            padding: 4px 11px;
            border-radius: 99px;
            font-size: 12px;
            font-weight: 900;
            background: var(--soft);
            color: var(--muted);
        }

        .ps-app .ps-pill.full { background: var(--ok-bg); color: var(--ok); }
        .ps-app .ps-pill.process { background: var(--wip-bg); color: var(--wip-ink); }

        .ps-link {
            border: 0;
            background: var(--pri-bg);
            color: var(--pri);
            font-weight: 900;
            font-size: 13px;
            border-radius: 11px;
            padding: 9px 12px;
        }

        .ps-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin: 16px 0 4px;
            padding-top: 14px;
            border-top: 1px dashed var(--line);
        }

        .ps-tag {
            background: var(--soft);
            border-radius: 10px;
            padding: 6px 10px;
            font-size: 12px;
            font-weight: 800;
        }

        .ps-tag span {
            color: var(--muted);
            font-weight: 700;
            margin-right: 4px;
        }

        .ps-sku-list {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-top: 10px;
        }

        .ps-sku-row {
            display: grid;
            grid-template-columns: 66px 1fr auto;
            gap: 14px;
            align-items: center;
            width: 100%;
            padding: 12px;
            border: 1px solid var(--line);
            border-radius: 16px;
            background: #fff;
            text-align: left;
            transition: background .2s;
        }

        .ps-sku-row:hover {
            border-color: #cfcabd;
        }

        .ps-sku-row .ps-size {
            display: grid;
            place-items: center;
            min-height: 58px;
            padding: 4px;
            border-radius: 14px;
            background: var(--pri-bg);
            color: var(--pri);
            font-size: 22px;
            font-weight: 900;
            line-height: 1.1;
            text-align: center;
            overflow-wrap: anywhere;
        }

        /* Size panjang (mis. ALL COLOR ALL SIZE) dikecilkan supaya muat */
        .ps-sku-row .ps-size.long {
            font-size: 11px;
        }

        .ps-sku-row.wip .ps-size { background: var(--wip-bg); color: var(--wip-ink); }
        .ps-sku-row.done { background: var(--ok-bg); border-color: var(--ok-line); opacity: .8; }
        .ps-sku-row.done .ps-size { background: #fff; color: var(--ok); }
        .ps-sku-row.dim { opacity: .4; }
        .ps-sku-row.hit { animation: ps-hit .7s ease-out; }

        @keyframes ps-hit {
            0% { box-shadow: 0 0 0 6px var(--ok-line); transform: scale(1.015); }
            100% { box-shadow: 0 0 0 0 transparent; transform: none; }
        }

        .ps-sku-info b {
            font-size: 16px;
            font-weight: 900;
        }

        .ps-sku-info span {
            display: block;
            font-size: 12px;
            color: var(--muted);
            font-weight: 700;
        }

        .ps-mbar {
            height: 8px;
            margin-top: 8px;
            border-radius: 99px;
            background: var(--soft);
            overflow: hidden;
        }

        .ps-mbar i {
            display: block;
            height: 100%;
            border-radius: 99px;
            background: var(--wip);
            transition: width .3s;
        }

        .ps-sku-row.done .ps-mbar i { background: var(--ok); }

        .ps-sku-count {
            min-width: 92px;
            text-align: right;
        }

        .ps-sku-count b {
            font-size: 30px;
            font-weight: 900;
        }

        .ps-sku-count span {
            font-size: 16px;
            color: var(--muted);
            font-weight: 800;
        }

        .ps-sku-count em {
            display: block;
            font-style: normal;
            font-size: 12px;
            font-weight: 900;
            color: var(--wip-ink);
        }

        .ps-sku-row.done .ps-sku-count em { color: var(--ok); }

        .ps-empty-big {
            text-align: center;
            padding: 34px 10px;
        }

        .ps-empty-big svg {
            width: 64px;
            height: 64px;
            color: #c9c3b3;
        }

        .ps-empty-big h5 {
            margin: 10px 0 4px;
            font-size: 20px;
            font-weight: 900;
        }

        .ps-empty-big p {
            max-width: 420px;
            margin: 0 auto 14px;
            color: var(--muted);
            font-weight: 600;
        }

        /* Progress PO */
        .ps-pg-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
        }

        .ps-pct {
            font-size: 40px;
            font-weight: 900;
            line-height: 1;
        }

        .ps-pct small {
            font-size: 20px;
        }

        .ps-stack {
            display: flex;
            height: 14px;
            margin: 12px 0 8px;
            border-radius: 99px;
            background: var(--empty);
            overflow: hidden;
        }

        .ps-stack i {
            display: block;
            height: 100%;
        }

        .ps-legend {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            font-size: 12px;
            font-weight: 800;
            color: var(--muted);
        }

        .ps-legend b {
            color: var(--ink);
        }

        .ps-dot {
            display: inline-block;
            width: 9px;
            height: 9px;
            margin-right: 5px;
            border-radius: 3px;
        }

        .ps-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            margin-top: 14px;
        }

        .ps-stat {
            min-width: 0;
            padding: 10px 13px;
            border-radius: 14px;
            background: var(--soft);
        }

        .ps-stat span {
            display: block;
            margin-bottom: 2px;
            font-size: 12px;
            font-weight: 800;
            color: var(--muted);
        }

        .ps-stat b {
            display: block;
            font-size: 20px;
            font-weight: 900;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .ps-stat b small {
            font-size: 13px;
            font-weight: 800;
            color: var(--muted);
        }

        .ps-stat.hl { background: var(--wip-bg); }
        .ps-stat.hl b { color: var(--wip-ink); }

        .ps-sizes {
            margin-top: 14px;
            padding-top: 12px;
            border-top: 1px dashed var(--line);
        }

        .ps-size-row {
            display: grid;
            grid-template-columns: 46px 1fr 118px;
            gap: 10px;
            align-items: center;
            padding: 3px 0;
            font-size: 12px;
            font-weight: 800;
        }

        .ps-size-row .sb {
            height: 9px;
            border-radius: 99px;
            background: var(--soft);
            overflow: hidden;
        }

        .ps-size-row .sb i {
            display: block;
            height: 100%;
            border-radius: 99px;
            background: var(--pri);
        }

        .ps-size-row .sv {
            text-align: right;
            color: var(--muted);
        }

        .ps-size-row .sv b {
            color: var(--ink);
        }

        /* Peta karton */
        .ps-jump {
            display: flex;
            align-items: center;
            padding: 3px 3px 3px 12px;
            border-radius: 12px;
            background: var(--soft);
            font-weight: 900;
        }

        .ps-jump input {
            width: 92px;
            border: 0;
            background: transparent;
            padding: 7px 4px;
            outline: none;
            font-weight: 900;
        }

        .ps-jump button {
            border: 0;
            border-radius: 9px;
            padding: 6px 11px;
            background: var(--ink);
            color: #fff;
            font-weight: 900;
            font-size: 13px;
        }

        .ps-filters {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin: 12px 0;
        }

        .ps-blocks {
            display: flex;
            gap: 6px;
            padding: 12px 0 4px;
            overflow-x: auto;
            scrollbar-width: thin;
        }

        .ps-blk {
            flex: none;
            min-width: 92px;
            padding: 6px 10px;
            border: 1px solid var(--line);
            border-radius: 12px;
            background: #fff;
            font-size: 12px;
            font-weight: 800;
            text-align: left;
        }

        .ps-blk.on {
            border-color: var(--ink);
            background: var(--ink);
            color: #fff;
        }

        .ps-blk .bb {
            height: 4px;
            margin-top: 5px;
            border-radius: 99px;
            background: var(--empty);
            overflow: hidden;
        }

        .ps-blk .bb i {
            display: block;
            height: 100%;
            background: var(--ok);
        }

        .ps-tiles {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(62px, 1fr));
            gap: 6px;
            max-height: 330px;
            margin-top: 10px;
            padding: 3px;
            overflow: auto;
        }

        .ps-tile {
            padding: 7px 7px 6px;
            border: 1px solid var(--line);
            border-radius: 12px;
            background: #fff;
            text-align: left;
        }

        .ps-tile:hover { border-color: #cfcabd; }

        .ps-tile b {
            display: block;
            font-size: 15px;
            font-weight: 900;
            line-height: 1.1;
        }

        .ps-tile small {
            font-size: 10px;
            color: var(--muted);
            font-weight: 800;
        }

        .ps-tile .tb {
            height: 4px;
            margin-top: 4px;
            border-radius: 99px;
            background: var(--soft);
            overflow: hidden;
        }

        .ps-tile .tb i {
            display: block;
            height: 100%;
            background: var(--wip);
        }

        .ps-tile.full { background: var(--ok-bg); border-color: var(--ok-line); }
        .ps-tile.full .tb i { background: var(--ok); }
        .ps-tile.full b { color: var(--ok); }
        .ps-tile.process { background: var(--wip-bg); border-color: var(--wip-line); }
        .ps-tile.sel { outline: 3px solid var(--pri); outline-offset: 1px; }

        /* Aktivitas */
        .ps-act-list {
            max-height: 300px;
            margin-top: 6px;
            overflow: auto;
        }

        .ps-act {
            display: grid;
            grid-template-columns: 64px 58px 1fr auto;
            gap: 10px;
            align-items: center;
            padding: 9px 2px;
            border-top: 1px dashed var(--line);
            font-size: 13px;
            font-weight: 700;
        }

        .ps-act .tm {
            font-size: 12px;
            color: var(--muted);
        }

        .ps-act .cn {
            border: 0;
            border-radius: 8px;
            padding: 3px 7px;
            background: var(--soft);
            font-weight: 900;
            font-size: 12px;
        }

        .ps-act .sz {
            display: inline-block;
            margin-right: 6px;
            padding: 1px 7px;
            border-radius: 7px;
            background: var(--pri-bg);
            color: var(--pri);
            font-weight: 900;
        }

        .ps-act .sk {
            color: var(--muted);
            font-weight: 600;
        }

        .ps-act .pl {
            color: var(--ok);
            font-weight: 900;
        }

        .ps-undo {
            border: 1px solid var(--line);
            border-radius: 11px;
            padding: 7px 11px;
            background: #fff;
            font-weight: 900;
            font-size: 12px;
        }

        .ps-undo:hover {
            background: var(--err-bg);
            border-color: #f4c1bc;
            color: #a2261e;
        }

        .ps-undo:disabled {
            opacity: .5;
            pointer-events: none;
        }

        @media (max-width: 1100px) {
            .ps-grid { grid-template-columns: 1fr; }
        }

        @media (max-width: 640px) {
            .ps-cc-no b { font-size: 46px; }
            .ps-cc-stat { width: 100%; }
            .ps-scan-input { font-size: 22px; }
            .ps-po-row { grid-template-columns: 1fr; }
            .ps-sku-row { grid-template-columns: 52px 1fr auto; }
            .ps-sku-row .ps-size { min-height: 48px; font-size: 18px; }
            .ps-sku-row .ps-size.long { font-size: 10px; }
            .ps-act { grid-template-columns: 52px 50px 1fr; }
            .ps-act .pl { display: none; }
        }
    </style>
@endsection

@section('content')
    <div class="ps-app" id="psApp">
        <div class="card mb-3">
            <div class="card-header bg-sb">
                <h5 class="card-title fw-bold mb-0">
                    <i class="fas fa-box"></i> Packing Out — Scan Karton
                </h5>
            </div>
        </div>

        <div class="ps-grid">
            {{-- Kolom kiri --}}
            <section class="ps-col">
                <div class="ps-card ps-scan-card" id="scanCard">
                    <span class="ps-label">SCAN DI SINI</span>

                    <div class="ps-po-row">
                        <label class="ps-po-in">
                            <span>PO</span>
                            <select class="form-control form-control-sm select2bs4" id="poSelect" style="width: 100%;">
                                @foreach (collect($data_po)->pluck('po')->unique() as $po)
                                    <option value="{{ $po }}">{{ $po }}</option>
                                @endforeach
                            </select>
                        </label>

                        <label class="ps-po-in">
                            <span>DEST</span>
                            {{-- Diisi dari JS sesuai PO terpilih; value = id ppic_master_so --}}
                            <select class="form-control form-control-sm select2bs4" id="destSelect" style="width: 100%;"></select>
                        </label>
                    </div>

                    <div class="ps-scan-box">
                        <input type="text" class="ps-scan-input" id="scanInput" autocomplete="off" spellcheck="false"
                            placeholder="Scan barcode SKU…">
                        <button type="button" class="ps-scan-btn" id="scanBtn">Simpan</button>
                    </div>
                    <div class="ps-opts" id="modeOpts"></div>
                </div>

                <div class="ps-card" id="cartonCard"></div>
            </section>

            {{-- Kolom kanan --}}
            <aside class="ps-col">
                <div class="ps-card" id="progCard"></div>

                <div class="ps-card">
                    <div class="ps-row">
                        <span class="ps-title">Peta Karton</span>
                        <span class="ps-sp"></span>
                        <div class="ps-jump">
                            #<input type="text" id="jumpInput" inputmode="numeric" placeholder="nomor">
                            <button type="button" id="jumpBtn">Buka</button>
                        </div>
                    </div>
                    <div class="ps-filters" id="filters"></div>
                    <div class="ps-blocks" id="blocks"></div>
                    <div class="ps-tiles" id="tiles"></div>
                </div>

                <div class="ps-card">
                    <div class="ps-row">
                        <span class="ps-title">Aktivitas Terakhir</span>
                        <span class="ps-sp"></span>
                        <button type="button" class="ps-undo" id="undoBtn">↺ Batalkan scan terakhir</button>
                    </div>
                    <div class="ps-act-list" id="activity"></div>
                </div>
            </aside>
        </div>
    </div>
@endsection

@section('custom-script')
    <!-- Select2 -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        const el = id => document.getElementById(id);
        const fmt = n => Number(n).toLocaleString('id-ID');
        const esc = s => String(s).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c]);
        const uniq = a => [...new Set(a)];
        const short = s => String(s).split(' ')[0];
        const ST_LABEL = { full: 'Full', process: 'Sedang diisi', empty: 'Belum diisi' };

        function makePO(key, label, multi, rows) {
            const byC = new Map();

            rows.forEach(r => {
                r.q = +r['Qty PO'];
                const c = +r['Carton Number'];
                if (!byC.has(c)) byC.set(c, []);
                r.i = byC.get(c).length;
                byC.get(c).push(r);
            });

            const list = [...byC.keys()].sort((a, b) => a - b);
            const tot = new Map(list.map(c => [c, byC.get(c).reduce((s, r) => s + r.q, 0)]));

            return { key, label, multi, rows, byC, list, tot, done: new Map(), cnt: new Map(), times: [], hist: [] };
        }

        const POS = {};
        const addPO = p => POS[p.key] = p;

        // ===== State =====
        const BLOCK = 100;
        const state = { po: null, carton: null, mode: 'set', style: null, filter: 'all', block: 0, autoNext: false, sound: true, session: 0, lastHit: null };

        // PO yang belum dimuat dianggap kosong
        const P = () => POS[state.po] || addPO(makePO(state.po, state.po ?? '-', false, []));
        const need = c => P().byC.get(c) || [];
        const tot = c => P().tot.get(c) || 0;
        const done = c => P().done.get(c) || 0;
        const cnt = c => {
            const p = P();
            if (!p.cnt.has(c)) p.cnt.set(c, need(c).map(() => 0));
            return p.cnt.get(c);
        };
        const st = c => {
            const d = done(c), t = tot(c);
            return t > 0 && d >= t ? 'full' : d > 0 ? 'process' : 'empty';
        };

        function filtered() {
            const l = P().list, f = state.filter;
            return f === 'all' ? l : l.filter(c => f === 'open' ? st(c) !== 'full' : st(c) === f);
        }

        function nextOpen(from, dir = 1) {
            const l = P().list, n = l.length;
            const i = from == null ? (dir > 0 ? -1 : n) : l.indexOf(from);

            for (let k = 1; k <= n; k++) {
                const c = l[((i + dir * k) % n + n) % n];
                if (st(c) !== 'full') return c;
            }
            return null;
        }

        function syncBlock() {
            if (state.carton == null) return;

            let l = filtered(), i = l.indexOf(state.carton);
            if (i < 0) {
                state.filter = 'all';
                l = P().list;
                i = l.indexOf(state.carton);
            }
            state.block = Math.floor(i / BLOCK);
        }

        // ===== Aksi =====
        function selectCarton(c) {
            c = Number(c);

            if (!P().byC.has(c)) {
                fail(`Karton #${c} tidak ada di PO ini`, `PO ini punya karton #${P().list[0]} sampai #${P().list[P().list.length - 1]}.`);
                return;
            }

            state.carton = c;
            const styles = uniq(need(c).map(r => r.Style));
            if (!styles.includes(state.style)) state.style = styles[0];

            syncBlock();
            renderAll();
        }

        function matchRows(c, v) {
            const p = P();
            return need(c).filter(r => String(r.SKU) === v && (!p.multi || state.mode === 'set' || r.Style === state.style));
        }

        function onScan(raw) {
            const v = String(raw).trim();
            if (!v || saving) return;

            const p = P();
            let c = state.carton, note = '';

            if (c == null) {
                c = p.list.find(x => st(x) !== 'full' && matchRows(x, v).some(r => cnt(x)[r.i] < r.q));
                if (c == null) return fail(`SKU ${v} tidak dibutuhkan di karton mana pun`, 'Cek lagi barcode atau PO yang dipilih.');

                state.carton = c;
                syncBlock();
                note = ` · otomatis dipilihkan karton #${c}`;
            }

            const ms = matchRows(c, v);
            if (!ms.length) {
                const alt = p.list.find(x => x !== c && st(x) !== 'full' && matchRows(x, v).length);
                return fail(`SKU ${v} bukan isi karton #${c}`,
                    alt ? `Karton belum full yang butuh SKU ini: #${alt} — buka lewat Peta karton untuk pindah.` : 'SKU ini tidak ada di karton lain yang masih kosong.');
            }

            // Barcode sama di beberapa baris (mis. beda color): 1 scan = +1 ke semua baris yang masih kurang
            const a = cnt(c), hits = ms.filter(r => a[r.i] < r.q);
            if (!hits.length) {
                const r = ms[0];
                return fail(`${r.Size} · ${r.Color} di karton #${c} sudah cukup (${r.q} pcs)`, 'Jangan dimasukkan lagi ya — simpan untuk karton berikutnya.');
            }

            // Simpan ke server dulu, tampilan baru bertambah setelah berhasil
            saveScan(p, c, hits, v, note);

            // DEBUG: untuk DEBUG cek QTY
            // saveScan(P(), state.carton, null, v, '');
        }

        // Selama scan disimpan: tombol loading & kolom scan dikunci supaya tidak dobel
        let saving = false;

        function setSaving(on) {
            saving = on;
            el('scanCard').classList.toggle('saving', on);
            el('scanInput').readOnly = on;
            el('scanBtn').disabled = on;
            el('scanBtn').innerHTML = on ? '<span class="ps-spin"></span>Menyimpan…' : 'Simpan';
        }

        function saveScan(p, c, hits, v, note) {
            $.ajax({
                url: '{{ route('store-packing-out-scan') }}',
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: { cbopo: p.key, barcode: v, no_carton: c, id_so_det: (hits || []).map(h => h.id_so_det) },
                dataType: 'json',
                beforeSend: function () {
                    setSaving(true);
                },
                success: function (res) {
                    // PO sudah diganti saat request berjalan
                    if (P() !== p) return;

                    if (res.icon === 'benar') {
                        // Baris yang stoknya habis dilewati server (tidak ada di res.ids)
                        const ids = res.ids || {}, ok = hits.filter(h => ids[h.id_so_det] != null);
                        applyHit(p, c, ok, v, note, ids, hits.filter(h => !ok.includes(h)));
                    } else {
                        fail(res.msg, `Karton #${c} · SKU ${v}`);
                    }
                },
                error: function (xhr) {
                    fail('Scan gagal disimpan', xhr.responseJSON?.message ?? 'Periksa koneksi lalu scan ulang.');
                },
                complete: function () {
                    setSaving(false);
                    focusScan();
                }
            });
        }

        // Tambah 1 pcs ke tiap baris karton yang kena scan di tampilan
        function applyHit(p, c, hits, v, note = '', ids = {}, skipped = []) {
            const a = cnt(c);

            hits.forEach(hit => {
                a[hit.i]++;
                p.done.set(c, done(c) + 1);
                p.times.push(Date.now());
                p.hist.unshift({ id: ids[hit.id_so_det] ?? null, t: new Date(), c, i: hit.i, sku: v, size: hit.Size, color: hit.Color, style: hit.Style });
                state.session++;
            });
            while (p.hist.length > 500) p.hist.pop();

            state.lastHit = { c, i: hits.map(h => h.i) };

            if (st(c) === 'full') {
                beep('win');
                const nx = state.autoNext ? nextOpen(c) : null;

                setFeedback('win', `Mantap! Karton #${c} sudah FULL (${tot(c)} pcs)`,
                    nx ? `Lanjut otomatis ke karton #${nx} — butuh ${fmt(tot(nx) - done(nx))} pcs.`
                        : (nextOpen(c) == null ? 'Semua karton di PO ini sudah full. Kerja bagus!' : 'Klik karton berikutnya di Peta Karton untuk pindah.'));

                if (nx) {
                    state.carton = nx;
                    state.lastHit = null;
                    syncBlock();
                }
            } else {
                beep('ok');
                const hit = hits[0], left = hit.q - a[hit.i];
                setFeedback('ok', hits.map(h => `+1 ${h.Size} · ${h.Color}${p.multi ? ' · ' + short(h.Style) : ''}`).join('  '),
                    `Karton #${c}: ${done(c)}/${tot(c)} pcs` + (hits.length === 1 ? ` · size ini ${left ? `kurang ${left}` : 'sudah lengkap ✓'}` : '') + note);
            }

            if (skipped.length) {
                iziToast.warning({
                    title: 'Tidak ada stok',
                    message: esc(skipped.map(h => `${h.Size} · ${h.Color}`).join(', ')) + ' tidak ikut masuk karton.',
                    position: 'topCenter'
                });
            }

            renderAll();
        }

        // Batalkan scan terakhir di sesi ini (simpan ke log lalu hapus di server)
        let undoBusy = false;

        function undo() {
            const p = P(), h = p.hist[0];

            if (!h) {
                iziToast.info({ message: 'Belum ada scan untuk dibatalkan', position: 'topCenter' });
                return;
            }

            if (undoBusy) return;

            Swal.fire({
                title: 'Batalkan scan terakhir?',
                html: `<b>${esc(h.size)} · ${esc(h.color)}</b> dari karton <b>#${h.c}</b><br>SKU ${esc(h.sku)}`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, batalkan',
                cancelButtonText: 'Tidak',
                confirmButtonColor: '#dc3545',
            }).then(result => {
                if (!result.isConfirmed) return focusScan();

                undoBusy = true;

                $.ajax({
                    url: '{{ route('destroy-packing-out-scan') }}',
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: { id: h.id },
                    dataType: 'json',
                    success: function () {
                        // Kurangi tampilan sesuai scan yang dihapus
                        const i = p.hist.indexOf(h);
                        if (i >= 0) p.hist.splice(i, 1);

                        p.cnt.get(h.c)[h.i]--;
                        p.done.set(h.c, (p.done.get(h.c) || 0) - 1);
                        p.times.pop();
                        state.session = Math.max(0, state.session - 1);

                        if (P() === p) {
                            state.carton = h.c;
                            syncBlock();
                            setFeedback('ok', 'Scan terakhir dibatalkan', `−1 ${h.size} · ${h.color} dari karton #${h.c}`);
                            renderAll();
                        }
                    },
                    error: function (xhr) {
                        fail('Scan gagal dibatalkan', xhr.responseJSON?.message ?? 'Periksa koneksi lalu coba lagi.');
                    },
                    complete: function () {
                        undoBusy = false;
                        focusScan();
                    }
                });
            });
        }

        function fail(title, sub) {
            beep('err');
            setFeedback('err', title, sub);
            el('scanInput').select();
        }

        // ===== Feedback =====
        let fbTimer;

        function setFeedback(kind, title, sub = '') {
            const type = { ok: 'success', win: 'success', err: 'error', info: 'info' }[kind];

            iziToast.destroy();
            iziToast[type]({ title: esc(title), message: esc(sub), position: 'topCenter' });

            const card = el('scanCard');
            card.classList.remove('ok', 'err');
            void card.offsetWidth;
            if (kind === 'ok' || kind === 'win') card.classList.add('ok');
            if (kind === 'err') card.classList.add('err');

            clearTimeout(fbTimer);
            fbTimer = setTimeout(() => card.classList.remove('ok', 'err'), kind === 'err' ? 1400 : 600);
        }

        let AC;

        function beep(type) {
            if (!state.sound) return;

            try {
                AC = AC || new (window.AudioContext || window.webkitAudioContext)();
                const seq = type === 'ok' ? [[1046, .07]] : type === 'win' ? [[784, .09], [1046, .09], [1318, .16]] : [[196, .28]];
                let t = AC.currentTime;

                seq.forEach(([f, d]) => {
                    const o = AC.createOscillator(), g = AC.createGain();
                    o.type = type === 'err' ? 'square' : 'sine';
                    o.frequency.value = f;
                    g.gain.setValueAtTime(.12, t);
                    g.gain.exponentialRampToValueAtTime(.001, t + d);
                    o.connect(g).connect(AC.destination);
                    o.start(t);
                    o.stop(t + d);
                    t += d;
                });
            } catch (e) {}
        }

        // ===== Render =====
        function renderModes() {
            const p = P();
            let h = '';

            if (p.multi) {
                const styles = state.carton != null ? uniq(need(state.carton).map(r => r.Style)) : uniq(p.rows.map(r => r.Style));
                const isSet = state.mode === 'set';

                h += `<span class="ps-label">MODE</span><button type="button" class="ps-chip ${isSet ? 'on' : ''}" data-mode="set">1 Set = ${esc(styles.map(short).join(' + '))}</button>`;
                styles.forEach(s => h += `<button type="button" class="ps-chip ${!isSet && state.style === s ? 'on' : ''}" data-style="${esc(s)}">Hanya ${esc(s)}</button>`);
                h += `<div class="ps-mode-hint">${isSet
                    ? `<b>Mode set:</b> scan masuk ke style mana pun (${esc(styles.map(short).join(' + '))}) yang masih butuh.`
                    : `<b>Mode per style:</b> 1× scan hanya masuk ke <b>${esc(state.style)}</b>.`}</div>`;
            }

            el('modeOpts').innerHTML = h;
        }

        function renderCarton() {
            const box = el('cartonCard'), p = P(), c = state.carton;
            const boxIcon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"><path d="M3 7.5 12 3l9 4.5v9L12 21l-9-4.5z"/><path d="M3 7.5 12 12l9-4.5M12 12v9"/></svg>';

            if (!p.list.length) {
                box.innerHTML = `
                    <div class="ps-empty-big">
                        ${boxIcon}
                        <h5>Belum ada data karton</h5>
                        <p>PO <b>${esc(p.label)}</b> belum punya packing list.</p>
                    </div>`;
                return;
            }

            if (c == null) {
                const allFull = nextOpen(null) == null;
                box.innerHTML = `
                    <div class="ps-empty-big">
                        ${boxIcon}
                        <h5>${allFull ? 'Semua karton sudah full' : 'Belum ada karton aktif'}</h5>
                        <p>Langsung scan SKU saja — sistem pilihkan karton yang masih butuh. Atau pilih karton di <b>Peta karton</b>.</p>
                        ${allFull ? '' : '<button type="button" class="ps-link" data-go="first">Mulai dari karton belum full pertama →</button>'}
                    </div>`;
                return;
            }

            const rs = need(c), a = cnt(c), t = tot(c), d = done(c), s = st(c), pct = t ? d / t : 0, C = 251.3;
            const order = rs.slice().sort((x, y) => (a[x.i] >= x.q) - (a[y.i] >= y.q));
            const hit = state.lastHit && state.lastHit.c === c ? state.lastHit.i : [];
            state.lastHit = null;

            box.innerHTML = `
                <div class="ps-cc-head">
                    <div class="ps-cc-no">
                        <div class="ps-cc-lbl"><span class="ps-label">KARTON AKTIF</span><span class="ps-pill ${s}">${ST_LABEL[s]}</span></div>
                        <b>#${c}</b>
                        <small>urutan ${fmt(p.list.indexOf(c) + 1)} dari ${fmt(p.list.length)} karton</small>
                    </div>
                    <div class="ps-cc-ket">${(() => {
                        const tp = uniq(rs.map(r => r.tipe_pack).filter(Boolean)).join(' / ');
                        const kt = uniq(rs.map(r => r.keterangan).filter(Boolean)).join(' / ');
                        return (tp ? `<div class="ps-cc-item"><span class="ps-label">TIPE PACK</span><span>${esc(tp)}</span></div>` : '')
                            + (kt ? `<div class="ps-cc-item"><span class="ps-label">KETERANGAN</span><span>${esc(kt)}</span></div>` : '');
                    })()}</div>
                    <div class="ps-cc-stat">
                        <div class="ps-ring ${s}">
                            <svg viewBox="0 0 100 100">
                                <circle cx="50" cy="50" r="40" class="rb"/>
                                <circle cx="50" cy="50" r="40" class="rf" style="opacity:${d ? 1 : 0}" stroke-dasharray="${C}" stroke-dashoffset="${C * (1 - pct)}" transform="rotate(-90 50 50)"/>
                            </svg>
                            <div class="rt"><b>${Math.floor(pct * 100)}%</b><span>${d}/${t}</span></div>
                        </div>
                        <div class="ps-big-left ${s}">
                            <span>Sisa</span>
                            <b>${fmt(t - d)}<small> pcs</small></b>
                            <em>terisi ${fmt(d)} dari ${fmt(t)} pcs</em>
                        </div>
                    </div>
                </div>
                <div class="ps-tags">
                    <div class="ps-tag"><span>Worksheet</span>${esc(uniq(rs.map(r => r.ws)).join(' / '))}</div>
                    <div class="ps-tag"><span>Style</span>${esc(uniq(rs.map(r => r.Style)).join(' / '))}</div>
                    <div class="ps-tag"><span>Color</span>${esc(uniq(rs.map(r => r.Color)).join(' / '))}</div>
                    <div class="ps-tag"><span>Isi</span>${rs.length} baris SKU</div>
                </div>
                <div class="ps-sku-list">
                    ${order.map(r => {
                        const n = a[r.i], left = r.q - n, dn = left <= 0;
                        const dim = p.multi && state.mode === 'style' && r.Style !== state.style;
                        return `
                            <div class="ps-sku-row ${dn ? 'done' : n > 0 ? 'wip' : ''} ${hit.includes(r.i) ? 'hit' : ''} ${dim ? 'dim' : ''}">
                                <div class="ps-size${String(r.Size).length > 4 ? ' long' : ''}">${esc(r.Size)}</div>
                                <div class="ps-sku-info">
                                    <b>${esc(r.Color)}</b>
                                    <span>SKU ${esc(r.SKU)}${p.multi ? ' · ' + esc(r.Style) : ''}</span>
                                    <div class="ps-mbar"><i style="width:${Math.min(100, n / r.q * 100)}%"></i></div>
                                </div>
                                <div class="ps-sku-count">
                                    <div><b>${n}</b><span> / ${r.q}</span></div>
                                    <em>${dn ? 'Lengkap ✓' : 'kurang ' + left}</em>
                                </div>
                            </div>`;
                    }).join('')}
                </div>`;
        }

        function renderProgress() {
            const p = P();
            let full = 0, proc = 0, emp = 0, T = 0, D = 0;

            p.list.forEach(c => {
                const s = st(c);
                s === 'full' ? full++ : s === 'process' ? proc++ : emp++;
                T += tot(c);
                D += done(c);
            });

            const n = p.list.length, pct = T ? D / T * 100 : 0;

            const sz = {};
            p.rows.forEach(r => {
                const c = +r['Carton Number'], k = r.Size;
                sz[k] = sz[k] || { q: 0, d: 0, o: r.sizeOrder };
                sz[k].q += r.q;
                sz[k].d += (p.cnt.get(c) || [])[r.i] || 0;
            });

            // Urutan size dari master_size_new; size tanpa urutan di paling bawah
            const keys = Object.keys(sz).sort((a, b) => (sz[a].o ?? Infinity) - (sz[b].o ?? Infinity) || a.localeCompare(b));

            el('progCard').innerHTML = `
                <div class="ps-pg-top">
                    <div class="ps-label">PROGRESS PO</div>
                    <div class="ps-pct">${pct.toFixed(pct > 0 && pct < 10 ? 1 : 0)}<small>%</small></div>
                </div>
                <div class="ps-stack">
                    <i style="width:${full / (n || 1) * 100}%;background:var(--ok)"></i>
                    <i style="width:${proc / (n || 1) * 100}%;background:var(--wip)"></i>
                </div>
                <div class="ps-legend">
                    <span><i class="ps-dot" style="background:var(--ok)"></i>Full <b>${fmt(full)}</b></span>
                    <span><i class="ps-dot" style="background:var(--wip)"></i>Sedang diisi <b>${fmt(proc)}</b></span>
                    <span><i class="ps-dot" style="background:var(--empty)"></i>Belum <b>${fmt(emp)}</b></span>
                </div>
                <div class="ps-stats">
                    <div class="ps-stat"><span>Karton full</span><b>${fmt(full)}<small> / ${fmt(n)}</small></b></div>
                    <div class="ps-stat"><span>Pcs terisi</span><b>${fmt(D)}<small> / ${fmt(T)}</small></b></div>
                    <div class="ps-stat hl"><span>Sisa pcs</span><b>${fmt(T - D)}</b></div>
                </div>
                <div class="ps-sizes">
                    <div class="ps-label mb-1">PER SIZE</div>
                    ${keys.map(k => {
                        const x = sz[k];
                        return `
                            <div class="ps-size-row">
                                <span>${esc(k)}</span>
                                <div class="sb"><i style="width:${x.d / x.q * 100}%;${x.d >= x.q ? 'background:var(--ok)' : ''}"></i></div>
                                <span class="sv"><b>${fmt(x.d)}</b> / ${fmt(x.q)}</span>
                            </div>`;
                    }).join('')}
                </div>`;
        }

        function renderNav() {
            const p = P(), all = p.list;
            let cF = 0, cP = 0, cE = 0;

            all.forEach(c => {
                const s = st(c);
                s === 'full' ? cF++ : s === 'process' ? cP++ : cE++;
            });

            const fl = [['all', 'Semua', all.length], ['open', 'Belum full', cP + cE], ['process', 'Sedang diisi', cP], ['empty', 'Belum diisi', cE], ['full', 'Full', cF]];
            el('filters').innerHTML = fl.map(([k, l, n]) =>
                `<button type="button" class="ps-chip ${state.filter === k ? 'on' : ''}" data-filter="${k}">${l}<span class="n">${fmt(n)}</span></button>`
            ).join('');

            const list = filtered(), nb = Math.max(1, Math.ceil(list.length / BLOCK));
            state.block = Math.min(state.block, nb - 1);
            const chunk = list.slice(state.block * BLOCK, (state.block + 1) * BLOCK);

            // Blok per 100 karton
            if (nb > 1) {
                let h = '';
                for (let b = 0; b < nb; b++) {
                    const ck = list.slice(b * BLOCK, (b + 1) * BLOCK), f = ck.filter(c => st(c) === 'full').length;
                    h += `<button type="button" class="ps-blk ${b === state.block ? 'on' : ''}" data-block="${b}">#${ck[0]}–${ck[ck.length - 1]}<div class="bb"><i style="width:${f / ck.length * 100}%"></i></div></button>`;
                }
                el('blocks').innerHTML = h;
                el('blocks').style.display = '';

                const on = el('blocks').querySelector('.ps-blk.on');
                if (on) el('blocks').scrollLeft = on.offsetLeft - el('blocks').offsetLeft - 40;
            } else {
                el('blocks').innerHTML = '';
                el('blocks').style.display = 'none';
            }

            el('tiles').innerHTML = chunk.length
                ? chunk.map(c => {
                    const d = done(c), t = tot(c);
                    return `<button type="button" class="ps-tile ${st(c)}${c === state.carton ? ' sel' : ''}" data-c="${c}"><b>${c}</b><small>${d}/${t}</small><div class="tb"><i style="width:${t ? d / t * 100 : 0}%"></i></div></button>`;
                }).join('')
                : '<div class="ps-none" style="grid-column:1/-1">Tidak ada karton di filter ini.</div>';

            const sel = el('tiles').querySelector('.ps-tile.sel');
            if (sel) {
                const T = el('tiles');
                if (sel.offsetTop < T.scrollTop || sel.offsetTop > T.scrollTop + T.clientHeight - 50) {
                    T.scrollTop = sel.offsetTop - T.clientHeight / 2 + 30;
                }
            }
        }

        function renderActivity() {
            const h = P().hist.slice(0, 30);

            el('activity').innerHTML = h.length
                ? h.map(x => `
                    <div class="ps-act">
                        <span class="tm">${x.t.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' })}</span>
                        <button type="button" class="cn" data-c="${x.c}">#${x.c}</button>
                        <span><span class="sz">${esc(x.size)}</span>${esc(x.color)} <span class="sk">· ${esc(x.sku)}</span></span>
                        <span class="pl">+1</span>
                    </div>`).join('')
                : '<div class="ps-none">Belum ada scan di sesi ini. Yuk mulai!</div>';

            el('undoBtn').disabled = !h.length;
        }

        function renderAll() {
            renderModes();
            renderCarton();
            renderProgress();
            renderNav();
            renderActivity();
        }

        const focusScan = () => setTimeout(() => el('scanInput').focus(), 0);

        // ===== Events =====
        $('#scanInput').on('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const v = this.value;
                this.value = '';
                onScan(v);
            }
        });

        $('#scanBtn').on('click', function () {
            const v = el('scanInput').value;
            el('scanInput').value = '';
            onScan(v);
            focusScan();
        });

        $('#jumpInput').on('keydown', function (e) {
            if (e.key === 'Enter') $('#jumpBtn').click();
        });

        $('#jumpBtn').on('click', function () {
            const v = el('jumpInput').value.replace(/\D/g, '');
            el('jumpInput').value = '';
            if (v) {
                selectCarton(+v);
                focusScan();
            }
        });

        $('#undoBtn').on('click', function () {
            undo();
            focusScan();
        });

        $('#psApp').on('click', function (e) {
            const t = e.target.closest('[data-c],[data-go],[data-filter],[data-block],[data-mode],[data-style]');

            if (!t) {
                if (!e.target.closest('input,select,label,.select2-container')) focusScan();
                return;
            }

            const d = t.dataset, p = P();

            if (d.c) {
                selectCarton(d.c);
            } else if (d.go) {
                const c = d.go === 'first' ? nextOpen(null) : nextOpen(state.carton);

                if (c != null) selectCarton(c);
                else iziToast.info({ message: 'Semua karton sudah full', position: 'topCenter' });
            } else if (d.filter) {
                state.filter = d.filter;
                state.block = 0;
                const i = filtered().indexOf(state.carton);
                if (i >= 0) state.block = Math.floor(i / BLOCK);
                renderNav();
            } else if (d.block) {
                state.block = +d.block;
                renderNav();
            } else if (d.mode) {
                state.mode = 'set';
                renderAll();
            } else if (d.style) {
                state.mode = 'style';
                state.style = d.style;
                renderAll();
            }

            focusScan();
        });

        $(document).on('keydown', function (e) {
            const a = document.activeElement, inField = a && /INPUT|SELECT|TEXTAREA/.test(a.tagName);

            // Ketik di mana saja langsung masuk ke kolom scan
            if (!inField && e.key.length === 1 && !e.ctrlKey && !e.metaKey && !e.altKey) el('scanInput').focus();
        });

        // ===== Init =====

        // Daftar PO + dest (isi = id ppic_master_so yang dipakai sebagai cbopo)
        const PO_DEST = @json($data_po);

        // Isi pilihan dest sesuai PO terpilih, lalu dest pertama langsung dimuat
        function fillDest(po) {
            const $d = $('#destSelect').empty();
            PO_DEST.filter(x => String(x.po) === String(po)).forEach(x => $d.append(new Option(x.dest, x.isi)));
            $d.trigger('change');
        }

        $('#poSelect').select2({
            theme: 'bootstrap4',
            width: 'resolve'
        }).on('change', function () {
            fillDest(this.value);
        });

        $('#destSelect').select2({
            theme: 'bootstrap4',
            width: 'resolve'
        }).on('change', function () {
            if (this.value) loadPO(this.value);
            else applyPO(null);
        });

        // Tampilkan PO terpilih: reset state & pilih karton belum full pertama
        function applyPO(key) {
            state.po = key;
            state.filter = 'all';
            state.block = 0;
            state.mode = 'set';
            state.carton = nextOpen(null);
            state.style = null;

            if (state.carton != null) {
                state.style = need(state.carton)[0].Style;
                syncBlock();
            }

            renderAll();
            focusScan();
        }

        // Ambil packing list + qty scan PO dari server
        function loadPO(key) {
            $.ajax({
                url: '{{ route('get-carton-data-packing-out-scan') }}',
                type: 'GET',
                data: { cbopo: key },
                dataType: 'json',
                beforeSend: function () {
                    showLoading();
                },
                success: function (res) {
                    const rows = res.data.map(r => ({
                        'Carton Number': r.no_carton,
                        SKU: r.barcode,
                        id_so_det: r.id_so_det,
                        Size: r.size ?? '-',
                        sizeOrder: r.size_order == null ? null : +r.size_order,
                        Style: r.styleno ?? '-',
                        Color: r.color ?? '-',
                        'Qty PO': r.qty,
                        ws: r.ws ?? '-',
                        keterangan: r.keterangan ?? '',
                        tipe_pack: r.tipe_pack ?? '',
                        qty_scan: +r.qty_scan,
                    }));

                    const multi = uniq(rows.map(r => r.Style)).length > 1;
                    const p = makePO(key, `${res.po} - ${res.dest}`, multi, rows);

                    // Qty yang sudah di-scan sebelumnya
                    p.list.forEach(c => {
                        const arr = p.byC.get(c).map(r => r.qty_scan);
                        p.cnt.set(c, arr);
                        p.done.set(c, arr.reduce((a, b) => a + b, 0));
                    });

                    addPO(p);
                    applyPO(key);
                },
                error: function (xhr) {
                    iziToast.error({
                        title: 'Gagal',
                        message: xhr.responseJSON?.message ?? 'Gagal mengambil data karton',
                        position: 'topCenter'
                    });

                    addPO(makePO(key, key, false, []));
                    applyPO(key);
                },
                complete: function () {
                    hideLoading();
                }
            });
        }

        // PO pertama + dest pertamanya langsung dimuat
        fillDest($('#poSelect').val());
    </script>
@endsection
