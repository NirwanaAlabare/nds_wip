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
            grid-template-columns: minmax(0, 1.4fr) minmax(0, 1fr) minmax(0, 1fr);
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

        .ps-po-in .ps-po-val {
            flex: 1;
            min-width: 0;
            font-size: 16px;
            font-weight: 900;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
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
            /* Jadi offsetParent tile, supaya hitungan scroll ke karton terpilih benar */
            position: relative;
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

        /* Tombol header (Panduan & Kembali): hover tetap terang, bukan hitam (timpa style.css global) */
        .ps-head-r .btn-light:hover,
        .ps-head-r .btn-light:focus-visible {
            background-color: #e2e6ea !important;
            border-color: #e2e6ea !important;
            color: #1d2433 !important;
        }

        /* ===== Panduan ===== */
        #psGuide, .ps-spot-l {
            --g-ink: #1d2433; --g-muted: #6b7385; --g-line: #ebe7de; --g-soft: #f7f5f0;
            --g-pri: #4057e3; --g-ok: #1a9d63; --g-wip: #e39a12; --g-err: #e0483e; --g-empty: #e4e1d8; --g-acc: #ff971f;
        }
        .ps-g { padding: 0 20px 20px; color: var(--g-ink); font-size: 13.5px; }
        .ps-g-nav { position: sticky; top: 0; z-index: 2; display: flex; flex-wrap: wrap; gap: 6px; padding: 12px 0; background: #fff; border-bottom: 1px solid var(--g-line); margin-bottom: 4px; }
        .ps-g-nav a { font-size: 12px; font-weight: 600; padding: 4px 10px; border-radius: 20px; background: var(--g-soft); color: var(--g-pri); text-decoration: none; border: 1px solid var(--g-line); }
        .ps-g-nav a:hover { background: var(--g-pri); color: #fff; }
        .ps-g section { padding-top: 16px; scroll-margin-top: 56px; }
        .ps-g h6 { display: flex; align-items: center; justify-content: space-between; gap: 8px; flex-wrap: wrap; font-weight: 700; color: var(--g-pri); border-left: 4px solid var(--g-acc); padding-left: 8px; margin-bottom: 10px; }
        .ps-g ul { padding-left: 18px; margin-bottom: 0; }
        .ps-g li { margin-bottom: 6px; line-height: 1.5; }
        .ps-g-t { margin-bottom: 0; }
        .ps-g-t td { vertical-align: top; padding: 7px 8px; line-height: 1.5; }
        .ps-g-t td:first-child { width: 210px; white-space: nowrap; color: var(--g-pri); }
        .ps-g-btn { display: inline-block; font-size: 12px; padding: 2px 8px; border: 1px solid #c5ccd6; border-radius: .3rem; background: #fff; }
        .ps-g-b { display: inline-block; font-size: 11px; font-weight: 700; letter-spacing: .6px; text-transform: uppercase; padding: 3px 10px; border-radius: 20px; color: #fff; }
        .ps-g-b.ok { background: var(--g-ok); } .ps-g-b.warn { background: var(--g-wip); } .ps-g-b.err { background: var(--g-err); } .ps-g-b.info { background: var(--g-pri); }
        .ps-g-c { display: inline-block; width: 10px; height: 10px; border-radius: 50%; margin: 0 4px 0 2px; vertical-align: middle; }
        .ps-g-flow { display: flex; align-items: stretch; gap: 8px; margin-bottom: 12px; flex-wrap: wrap; }
        .ps-g-flow > div { flex: 1; min-width: 140px; background: var(--g-soft); border: 1px solid var(--g-line); border-radius: .5rem; padding: 8px 12px; }
        .ps-g-flow > div.ok { background: #e7f7ee; border-color: #b9e8cf; }
        .ps-g-flow b { display: block; color: var(--g-pri); } .ps-g-flow span { font-size: 12px; color: var(--g-muted); }
        .ps-g-flow > i { align-self: center; font-style: normal; font-weight: 700; color: var(--g-acc); }
        .ps-g-faq dt { color: var(--g-pri); margin-top: 8px; } .ps-g-faq dd { margin: 2px 0 0; }
        .ps-g kbd { font-size: 11px; padding: 1px 5px; }
        .ps-g-show { border: 1px solid var(--g-pri); background: #fff; color: var(--g-pri); font-size: 11.5px; font-weight: 600; border-radius: 20px; padding: 2px 10px; }
        .ps-g-show:hover, .ps-g-show.pri { background: var(--g-pri); color: #fff; }
        .ps-g-show.pri:hover { filter: brightness(.92); }
        .ps-g-note { font-size: 12.5px; color: var(--g-muted); margin-bottom: 10px; }

        /* Panduan: peta halaman */
        .ps-map { background: var(--g-soft); border: 1px solid var(--g-line); border-radius: .6rem; padding: 14px; max-width: 760px; }
        .ps-map-z { position: relative; cursor: pointer; transition: box-shadow .15s; }
        .ps-map-z:hover { box-shadow: 0 0 0 2px var(--g-acc); z-index: 6; }
        .ps-map-n { position: absolute; top: -9px; left: -9px; z-index: 2; width: 22px; height: 22px; border-radius: 50%; background: var(--g-acc); color: #fff !important; font-size: 12px; font-weight: 700; display: grid; place-items: center; text-decoration: none; box-shadow: 0 0 0 2px #fff; }
        .ps-map-n.sm { width: 18px; height: 18px; font-size: 10.5px; top: -7px; left: -7px; }
        .ps-map-n:hover, .ps-map-n:focus-visible { background: var(--g-pri); z-index: 5; }
        .ps-map-tip { position: absolute; top: calc(100% + 8px); left: -4px; width: 230px; padding: 8px 10px; border-radius: .45rem; background: #1d2433; color: #fff; font-size: 12px; font-weight: 400; line-height: 1.45; text-align: left; box-shadow: 0 6px 18px rgba(0,0,0,.25); opacity: 0; visibility: hidden; transform: translateY(-4px); transition: opacity .15s, transform .15s, visibility .15s; pointer-events: none; }
        .ps-map-tip:before { content: ""; position: absolute; top: -5px; left: 10px; width: 10px; height: 10px; background: #1d2433; transform: rotate(45deg); }
        .ps-map-n.tip-l .ps-map-tip { left: auto; right: -4px; }
        .ps-map-n.tip-l .ps-map-tip:before { left: auto; right: 10px; }
        .ps-map-tip b { display: block; font-size: 12.5px; margin-bottom: 2px; color: #ffc98a; }
        .ps-map-tip small { display: block; margin-top: 5px; font-size: 10.5px; color: rgba(255,255,255,.65); }
        .ps-map-n:hover .ps-map-tip, .ps-map-n:focus-visible .ps-map-tip { opacity: 1; visibility: visible; transform: none; }
        .ps-map-head { display: flex; justify-content: space-between; align-items: center; gap: 10px; margin-bottom: 12px; padding: 6px 8px 6px 10px; border-radius: .4rem; background: var(--sb-color, #082149); }
        .ps-map-head b { font-size: 12px; color: #fff; }
        .ps-map-head .r { display: flex; gap: 4px; padding: 3px; border-radius: .35rem; }
        .ps-map-head .r i { width: 40px; height: 14px; border-radius: 3px; background: #fff; }
        .ps-map-grid { display: grid; grid-template-columns: 1.3fr 1fr; gap: 12px; }
        .ps-map-col { display: flex; flex-direction: column; gap: 12px; }
        .ps-map-card { background: #fff; border: 1px solid var(--g-line); border-radius: .45rem; padding: 8px; }
        .ps-map-card .h { font-size: 9.5px; font-weight: 700; letter-spacing: .6px; text-transform: uppercase; color: var(--g-muted); margin: 0 0 6px 10px; }
        .ps-map-card .po { display: grid; grid-template-columns: repeat(3, 1fr); gap: 4px; margin-bottom: 8px; border-radius: .3rem; }
        .ps-map-card .po i { height: 16px; border-radius: .25rem; border: 1px solid #c5ccd6; background: #fff; }
        .ps-map-card .inp { display: flex; gap: 4px; border-radius: .3rem; margin-bottom: 6px; }
        .ps-map-card .inp span { flex: 1; font-size: 10px; color: #9aa4b1; border: 1.5px solid var(--g-pri); border-radius: .3rem; padding: 4px 6px; }
        .ps-map-card .inp i { font-style: normal; font-size: 10px; border-radius: .3rem; padding: 4px 8px; background: var(--g-pri); color: #fff; }
        .ps-map-card .md { display: flex; gap: 4px; border-radius: .3rem; }
        .ps-map-card .md i { height: 12px; width: 60px; border-radius: 10px; border: 1px solid #c5ccd6; }
        .ps-map-card .md i.on { background: var(--g-pri); border-color: var(--g-pri); }
        .ps-map-card .cc { display: flex; align-items: center; gap: 10px; margin-bottom: 8px; }
        .ps-map-card .cc b { font-size: 20px; line-height: 1; color: var(--g-ink); }
        .ps-map-card .ring { width: 38px; height: 38px; margin-left: auto; border-radius: 50%; background: conic-gradient(var(--g-wip) 0 60%, var(--g-empty) 60% 100%); -webkit-mask: radial-gradient(circle, transparent 12px, #000 13px); mask: radial-gradient(circle, transparent 12px, #000 13px); }
        .ps-map-card .sku { display: flex; align-items: center; gap: 6px; margin-top: 5px; }
        .ps-map-card .sku em { width: 20px; height: 16px; border-radius: .25rem; background: var(--g-soft); border: 1px solid var(--g-line); }
        .ps-map-card .sku s { flex: 1; height: 6px; border-radius: 3px; background: var(--g-empty); text-decoration: none; overflow: hidden; }
        .ps-map-card .sku s i { display: block; height: 100%; background: var(--g-wip); }
        .ps-map-card .sku s i.d { background: var(--g-ok); }
        .ps-map-card .pg { display: flex; height: 7px; border-radius: 4px; overflow: hidden; background: var(--g-empty); margin-bottom: 6px; }
        .ps-map-card .sz { height: 5px; border-radius: 3px; background: var(--g-empty); margin-top: 4px; overflow: hidden; }
        .ps-map-card .sz i { display: block; height: 100%; background: var(--g-pri); }
        .ps-map-card .fl { display: flex; gap: 3px; margin-bottom: 6px; border-radius: .3rem; }
        .ps-map-card .fl i { height: 10px; width: 26px; border-radius: 10px; border: 1px solid #c5ccd6; }
        .ps-map-card .fl i.on { background: var(--g-pri); border-color: var(--g-pri); }
        .ps-map-card .tl { display: grid; grid-template-columns: repeat(8, 1fr); gap: 3px; border-radius: .3rem; }
        .ps-map-card .tl i { aspect-ratio: 1; border-radius: .2rem; background: var(--g-empty); }
        .ps-map-card .tl i.f { background: var(--g-ok); } .ps-map-card .tl i.p { background: var(--g-wip); }
        .ps-map-card .tl i.s { box-shadow: 0 0 0 2px var(--g-pri); }
        .ps-map-card .ac { height: 6px; border-radius: 3px; background: var(--g-empty); margin-top: 5px; width: 85%; }
        .ps-map-card .ac.s { width: 60%; }

        /* Panduan: sorot elemen & tur */
        .ps-spot { position: fixed; z-index: 2000; border-radius: .6rem; box-shadow: 0 0 0 9999px rgba(17,24,39,.6); outline: 3px solid #ff971f; outline-offset: 3px; pointer-events: none; animation: psSpot 1.2s ease-in-out infinite; }
        @keyframes psSpot { 50% { outline-offset: 8px; outline-color: rgba(255,151,31,.6); } }
        .ps-spot-cv { position: fixed; inset: 0; z-index: 1999; cursor: pointer; }
        .ps-spot-l { position: fixed; z-index: 2001; background: #fff; color: var(--g-ink); font-size: 13px; padding: 10px 12px; border-radius: .5rem; box-shadow: 0 6px 18px rgba(0,0,0,.25); width: 340px; max-width: calc(100vw - 16px); }
        .ps-spot-l .st { display: flex; justify-content: space-between; align-items: center; font-size: 10.5px; font-weight: 700; letter-spacing: .6px; text-transform: uppercase; color: var(--g-pri); margin-bottom: 4px; }
        .ps-spot-l .dots { display: flex; gap: 4px; }
        .ps-spot-l .dots i { width: 7px; height: 7px; border-radius: 50%; background: #e2e6ec; }
        .ps-spot-l .dots i.done { background: #b7c2f5; }
        .ps-spot-l .dots i.on { background: var(--g-acc); width: 16px; border-radius: 4px; }
        .ps-spot-l > b { display: block; font-weight: 700; }
        .ps-spot-l > span.d { display: block; color: #3d4450; font-size: 12.5px; line-height: 1.5; margin-top: 3px; }
        .ps-spot-l > span.d b { color: var(--g-ink); }
        .ps-spot-l .nav { display: flex; justify-content: space-between; gap: 8px; margin-top: 10px; }
        .ps-spot-l .nav button { border: 1px solid #c5ccd6; background: #fff; color: var(--g-ink); font-size: 12.5px; font-weight: 600; border-radius: .35rem; padding: 4px 12px; }
        .ps-spot-l .nav button:disabled { opacity: .4; }
        .ps-spot-l .nav button.pri { background: var(--g-pri); border-color: var(--g-pri); color: #fff; }
        .ps-spot-l .nav button:not(:disabled):hover { filter: brightness(.95); }
        .ps-spot-l small { display: block; color: var(--g-muted); font-size: 11.5px; margin-top: 6px; padding-top: 5px; border-top: 1px solid #e2e6ec; }
        .ps-spot-l kbd { font-size: 10px; padding: 0 4px; }

        @media (max-width: 576px) {
            .ps-g-t td:first-child { width: auto; white-space: normal; }
            .ps-g-flow > i { display: none; }
            .ps-map-grid { grid-template-columns: 1fr; }
        }
    </style>
@endsection

@section('content')
    <div class="ps-app" id="psApp">
        <div class="card mb-3">
            <div class="card-header bg-sb d-flex align-items-center">
                <h5 class="card-title fw-bold mb-0">
                    <i class="fas fa-box"></i> Packing Out — Scan Karton
                </h5>
                <div class="ps-head-r d-flex gap-2 ms-auto" style="margin-block: -.5rem;">
                    <button type="button" class="btn btn-light btn-sm" id="guideBtn">
                        <i class="fas fa-book-open"></i> Panduan
                    </button>
                    <a class="btn btn-light btn-sm" href="{{ route('packing-out') }}">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
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
                                <option value="">Pilih PO</option>
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

                        <label class="ps-po-in">
                            <span>TGL SHIPMENT</span>
                            {{-- Diisi dari JS sesuai PO + dest terpilih --}}
                            <b id="tglShipment" class="ps-po-val">-</b>
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

                <div class="ps-card" id="mapCard">
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

                <div class="ps-card" id="actCard">
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

    {{-- Panduan halaman --}}
    <div class="modal fade" id="psGuide" tabindex="-1" aria-labelledby="psGuideTitle" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-sb text-light">
                    <div>
                        <small class="text-uppercase fw-bold" style="letter-spacing: .6px; opacity: .75;">Panduan</small>
                        <h5 class="modal-title fw-bold" id="psGuideTitle">Packing Out — Scan Karton</h5>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body ps-g">
                    <nav class="ps-g-nav">
                        <a href="#psg-peta">Peta halaman</a><a href="#psg-alur">Alur singkat</a><a href="#psg-atas">Tombol atas</a><a href="#psg-po">Pilih PO</a><a href="#psg-scan">Scan SKU</a>
                        <a href="#psg-hasil">Hasil scan</a><a href="#psg-karton">Karton aktif</a><a href="#psg-prog">Progress PO</a><a href="#psg-map">Peta karton</a><a href="#psg-act">Aktivitas</a><a href="#psg-tips">Tips &amp; FAQ</a>
                    </nav>

                    <section id="psg-peta">
                        <h6><span>Peta halaman</span> <button type="button" class="ps-g-show pri" data-tour="0"><i class="fa fa-play"></i> Mulai tur</button></h6>
                        <p class="ps-g-note">Klik <b>nomor</b> untuk membaca penjelasannya, atau klik <b>kotaknya</b> untuk melihat langsung di halaman.</p>
                        <div class="ps-map">
                            <div class="ps-map-head">
                                <b>Packing Out — Scan Karton</b>
                                <div class="ps-map-z r" data-spot=".ps-head-r"><a href="#psg-atas" class="ps-map-n tip-l" aria-label="Tombol di kanan atas">2<span class="ps-map-tip" role="tooltip"><b>Tombol di kanan atas</b>Panduan dan Kembali ke daftar Packing Out.<small>Klik nomor untuk penjelasan lengkap</small></span></a><i></i><i></i></div>
                            </div>
                            <div class="ps-map-grid">
                                <div class="ps-map-col">
                                    <div class="ps-map-card">
                                        <div class="h">Scan di sini</div>
                                        <div class="po ps-map-z" data-spot=".ps-po-row"><a href="#psg-po" class="ps-map-n sm" aria-label="Pilih PO">3<span class="ps-map-tip" role="tooltip"><b>Pilih PO, Dest &amp; Tgl Shipment</b>Pilih PO lalu Dest. Data karton langsung dimuat.<small>Klik nomor untuk penjelasan lengkap</small></span></a><i></i><i></i><i></i></div>
                                        <div class="inp ps-map-z" data-spot=".ps-scan-box"><a href="#psg-scan" class="ps-map-n sm" aria-label="Scan SKU">4<span class="ps-map-tip" role="tooltip"><b>Kolom scan</b>Scan barcode SKU. Setiap scan langsung tersimpan.<small>Klik nomor untuk penjelasan lengkap</small></span></a><span>Scan barcode SKU…</span><i>Simpan</i></div>
                                        <div class="md"><i class="on"></i><i></i><i></i></div>
                                    </div>
                                    <div class="ps-map-z ps-map-card" data-spot="#cartonCard">
                                        <a href="#psg-karton" class="ps-map-n" aria-label="Karton aktif">6<span class="ps-map-tip" role="tooltip"><b>Karton aktif</b>Karton yang sedang diisi beserta daftar SKU dan sisa pcs-nya.<small>Klik nomor untuk penjelasan lengkap</small></span></a>
                                        <div class="h">Karton aktif</div>
                                        <div class="cc"><b>#12</b><div class="ring"></div></div>
                                        <div class="sku"><em></em><s><i class="d" style="width:100%"></i></s></div>
                                        <div class="sku"><em></em><s><i style="width:55%"></i></s></div>
                                        <div class="sku"><em></em><s><i style="width:0"></i></s></div>
                                    </div>
                                </div>
                                <div class="ps-map-col">
                                    <div class="ps-map-z ps-map-card" data-spot="#progCard">
                                        <a href="#psg-prog" class="ps-map-n tip-l" aria-label="Progress PO">7<span class="ps-map-tip" role="tooltip"><b>Progress PO</b>Persen pcs terisi, jumlah karton full, dan progres per size.<small>Klik nomor untuk penjelasan lengkap</small></span></a>
                                        <div class="h">Progress PO</div>
                                        <div class="pg"><i style="width:45%;background:var(--g-ok)"></i><i style="width:20%;background:var(--g-wip)"></i></div>
                                        <div class="sz"><i style="width:80%"></i></div><div class="sz"><i style="width:50%"></i></div>
                                    </div>
                                    <div class="ps-map-z ps-map-card" data-spot="#mapCard">
                                        <a href="#psg-map" class="ps-map-n tip-l" aria-label="Peta karton">8<span class="ps-map-tip" role="tooltip"><b>Peta karton</b>Semua karton PO dengan warna status. Klik kotak untuk pindah karton.<small>Klik nomor untuk penjelasan lengkap</small></span></a>
                                        <div class="h">Peta karton</div>
                                        <div class="fl"><i class="on"></i><i></i><i></i><i></i></div>
                                        <div class="tl"><i class="f"></i><i class="f"></i><i class="f"></i><i class="p s"></i><i class="p"></i><i></i><i></i><i></i></div>
                                    </div>
                                    <div class="ps-map-z ps-map-card" data-spot="#actCard">
                                        <a href="#psg-act" class="ps-map-n tip-l" aria-label="Aktivitas terakhir">9<span class="ps-map-tip" role="tooltip"><b>Aktivitas terakhir</b>Daftar scan di sesi ini dan tombol batalkan scan terakhir.<small>Klik nomor untuk penjelasan lengkap</small></span></a>
                                        <div class="h">Aktivitas terakhir</div>
                                        <div class="ac"></div><div class="ac s"></div><div class="ac"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section id="psg-alur">
                        <h6>1. Alur singkat</h6>
                        <div class="ps-g-flow">
                            <div><b>Pilih PO &amp; Dest</b><span>data karton dimuat</span></div><i>→</i>
                            <div><b>Scan SKU</b><span>barcode pada garmen</span></div><i>→</i>
                            <div><b>Dicek</b><span>ke isi karton aktif</span></div><i>→</i>
                            <div class="ok"><b>Langsung tersimpan</b><span>tanpa tombol simpan tambahan</span></div>
                        </div>
                        <ul>
                            <li>Setiap 1 scan = <b>+1 pcs</b> ke karton aktif.</li>
                            <li>Hasil scan sebelumnya (dari hari atau komputer lain) ikut dimuat saat PO dipilih, jadi angka selalu melanjutkan.</li>
                            <li>Qty per SKU tidak bisa melebihi qty di packing list. Kalau sudah cukup, scan akan ditolak.</li>
                        </ul>
                    </section>

                    <section id="psg-atas">
                        <h6><span>2. Tombol di kanan atas</span> <button type="button" class="ps-g-show" data-spot=".ps-head-r"><i class="fa fa-crosshairs"></i> Tunjukkan di halaman</button></h6>
                        <table class="table table-sm ps-g-t">
                            <tr><td><span class="ps-g-btn">Panduan</span></td><td>Membuka halaman ini.</td></tr>
                            <tr><td><span class="ps-g-btn">Kembali</span></td><td>Kembali ke daftar Packing Out.</td></tr>
                        </table>
                    </section>

                    <section id="psg-po">
                        <h6><span>3. Pilih PO, Dest &amp; Tgl Shipment</span> <button type="button" class="ps-g-show" data-spot=".ps-po-row"><i class="fa fa-crosshairs"></i> Tunjukkan di halaman</button></h6>
                        <table class="table table-sm ps-g-t">
                            <tr><td><b>PO</b></td><td>Pilih nomor PO yang akan di-packing. Bisa diketik untuk mencari.</td></tr>
                            <tr><td><b>Dest</b></td><td>Daftar tujuan otomatis menyesuaikan PO. Dest pertama langsung dipilih dan datanya dimuat. Ganti bila tujuannya berbeda.</td></tr>
                            <tr><td><b>Tgl Shipment</b></td><td>Tampil otomatis sesuai PO + Dest terpilih (hanya informasi).</td></tr>
                        </table>
                        <ul class="mt-2">
                            <li>Setelah dimuat, sistem langsung memilih <b>karton belum full pertama</b> sebagai karton aktif.</li>
                        </ul>
                    </section>

                    <section id="psg-scan">
                        <h6><span>4. Kolom scan</span> <button type="button" class="ps-g-show" data-spot=".ps-scan-box"><i class="fa fa-crosshairs"></i> Tunjukkan di halaman</button></h6>
                        <table class="table table-sm ps-g-t">
                            <tr><td><b>Kolom scan</b></td><td>Scanner otomatis menekan Enter. Kalau mengetik manual, tekan <kbd>Enter</kbd> atau tombol <b>Simpan</b>.</td></tr>
                            <tr><td><b>Fokus otomatis</b></td><td>Mengetik di mana saja langsung masuk ke kolom scan. Klik area kosong di halaman juga mengembalikan kursor ke kolom ini.</td></tr>
                            <tr><td><b>Menyimpan…</b></td><td>Selama scan disimpan, kolom dan tombol dikunci sebentar supaya tidak tersimpan dobel. Tunggu sampai tombol kembali ke <b>Simpan</b>.</td></tr>
                            <tr><td><b>Belum ada karton aktif</b></td><td>Langsung scan saja: sistem memilihkan karton belum full yang masih butuh SKU itu.</td></tr>
                            <tr><td><b>Barcode di beberapa baris</b></td><td>Kalau satu barcode ada di beberapa baris karton (misal beda color), 1 scan menambah +1 ke semua baris yang masih kurang.</td></tr>
                            <tr><td><b>MODE</b> <small class="text-muted">(PO multi-style)</small></td><td>Hanya muncul bila karton berisi lebih dari satu style.<br>
                                <span class="ps-g-btn">1 Set = A + B</span> scan masuk ke style mana pun yang masih butuh.<br>
                                <span class="ps-g-btn">Hanya [style]</span> scan hanya masuk ke style itu; baris style lain tampil pudar.</td></tr>
                        </table>
                    </section>

                    <section id="psg-hasil">
                        <h6><span>5. Arti hasil scan</span> <button type="button" class="ps-g-show" data-spot="#scanCard"><i class="fa fa-crosshairs"></i> Tunjukkan di halaman</button></h6>
                        <p class="ps-g-note">Hasil tampil sebagai notifikasi di atas layar, kartu scan berkedip hijau/merah, dan ada bunyi.</p>
                        <table class="table table-sm ps-g-t">
                            <tr><td><span class="ps-g-b ok">+1 Size · Color</span></td><td>Scan tersimpan. Bunyi <i>bip</i> pendek. Baris SKU di karton aktif ikut berkedip.</td></tr>
                            <tr><td><span class="ps-g-b ok">Karton FULL</span></td><td>Karton sudah terisi penuh. Bunyi tiga nada naik. Klik karton berikutnya di <b>Peta karton</b> untuk pindah.</td></tr>
                            <tr><td><span class="ps-g-b warn">Tidak ada stok</span></td><td>Sebagian baris (size · color) tidak ikut masuk karton karena stoknya habis.</td></tr>
                            <tr><td><span class="ps-g-b err">Bukan isi karton #N</span></td><td>SKU tidak ada di karton aktif. Notifikasi menyebutkan karton belum full yang butuh SKU ini, buka lewat Peta karton.</td></tr>
                            <tr><td><span class="ps-g-b err">Sudah cukup</span></td><td>Size · color itu di karton aktif sudah lengkap. Simpan garmennya untuk karton berikutnya.</td></tr>
                            <tr><td><span class="ps-g-b err">Tidak dibutuhkan</span></td><td>SKU tidak dibutuhkan di karton mana pun. Cek barcode atau PO/Dest yang dipilih.</td></tr>
                            <tr><td><span class="ps-g-b err">Gagal disimpan</span></td><td>Koneksi atau server bermasalah. Data tidak tersimpan, ulangi scan.</td></tr>
                        </table>
                    </section>

                    <section id="psg-karton">
                        <h6><span>6. Karton aktif</span> <button type="button" class="ps-g-show" data-spot="#cartonCard"><i class="fa fa-crosshairs"></i> Tunjukkan di halaman</button></h6>
                        <ul>
                            <li><b>#Nomor karton</b> dan statusnya:
                                <span class="ps-g-c" style="background:var(--g-ok)"></span><b>Full</b>,
                                <span class="ps-g-c" style="background:var(--g-wip)"></span><b>Sedang diisi</b>,
                                <span class="ps-g-c" style="background:var(--g-empty)"></span><b>Belum diisi</b>. Di bawahnya urutan karton dari total karton PO.</li>
                            <li><b>Tipe pack</b> dan <b>Keterangan</b> muncul bila diisi di packing list.</li>
                            <li><b>Lingkaran %</b> dan <b>Sisa pcs</b>: berapa pcs lagi supaya karton ini full.</li>
                            <li><b>Worksheet · Style · Color · Isi</b>: ringkasan isi karton.</li>
                            <li><b>Daftar SKU</b>: tiap baris berisi size, color, SKU, bar progres, dan <i>terisi / qty</i>. Baris yang belum lengkap selalu di atas; yang sudah lengkap bertanda <b>Lengkap ✓</b>.</li>
                        </ul>
                    </section>

                    <section id="psg-prog">
                        <h6><span>7. Progress PO</span> <button type="button" class="ps-g-show" data-spot="#progCard"><i class="fa fa-crosshairs"></i> Tunjukkan di halaman</button></h6>
                        <ul>
                            <li><b>Persen besar</b> = pcs terisi ÷ total pcs seluruh karton PO + Dest terpilih.</li>
                            <li><b>Bar bertumpuk</b>: porsi karton <span class="ps-g-c" style="background:var(--g-ok)"></span>Full dan <span class="ps-g-c" style="background:var(--g-wip)"></span>Sedang diisi.</li>
                            <li><b>Karton full · Pcs terisi · Sisa pcs</b>: ringkasan angka PO.</li>
                            <li><b>Per size</b>: progres tiap size, urut sesuai master size. Bar jadi hijau saat size itu lengkap.</li>
                        </ul>
                    </section>

                    <section id="psg-map">
                        <h6><span>8. Peta karton</span> <button type="button" class="ps-g-show" data-spot="#mapCard"><i class="fa fa-crosshairs"></i> Tunjukkan di halaman</button></h6>
                        <ul>
                            <li><b>Kotak karton</b>: nomor, <i>terisi/total</i>, dan bar. Warna mengikuti status (hijau full, oranye sedang diisi, abu belum diisi). Karton aktif diberi garis tepi. <b>Klik kotak</b> untuk pindah karton aktif.</li>
                            <li><b>Filter</b>: <span class="ps-g-btn">Semua</span> <span class="ps-g-btn">Belum full</span> <span class="ps-g-btn">Sedang diisi</span> <span class="ps-g-btn">Belum diisi</span> <span class="ps-g-btn">Full</span>, lengkap dengan jumlahnya.</li>
                            <li><b>Blok #1–100, #101–200, …</b>: muncul bila karton lebih dari 100. Bar hijau di tiap blok = porsi karton full di blok itu.</li>
                            <li><b># nomor + Buka</b>: ketik nomor karton lalu <kbd>Enter</kbd> untuk langsung membukanya.</li>
                        </ul>
                    </section>

                    <section id="psg-act">
                        <h6><span>9. Aktivitas terakhir</span> <button type="button" class="ps-g-show" data-spot="#actCard"><i class="fa fa-crosshairs"></i> Tunjukkan di halaman</button></h6>
                        <ul>
                            <li>30 scan terakhir di <b>sesi ini</b>: jam, nomor karton, size, color, dan SKU. Klik <b>#nomor</b> untuk membuka kartonnya.</li>
                            <li><span class="ps-g-btn">↺ Batalkan scan terakhir</span> menghapus 1 scan terakhir (setelah konfirmasi). Bisa diulang untuk scan sebelumnya.</li>
                            <li>Daftar ini hanya berisi scan sejak halaman dibuka. Setelah refresh, daftar kosong lagi dan scan lama tidak bisa dibatalkan dari sini.</li>
                        </ul>
                    </section>

                    <section id="psg-tips">
                        <h6>10. Tips &amp; FAQ</h6>
                        <dl class="ps-g-faq">
                            <dt>Salah scan?</dt>
                            <dd>Tekan <b>↺ Batalkan scan terakhir</b> sebelum halaman di-refresh.</dd>
                            <dt>Karton sudah full, lalu bagaimana?</dt>
                            <dd>Klik karton berikutnya di <b>Peta karton</b> (filter <i>Belum full</i> memudahkan), atau langsung scan: kalau tidak ada karton aktif, sistem memilihkannya.</dd>
                            <dt>Muncul "PO ini belum punya packing list"?</dt>
                            <dd>Packing list untuk PO + Dest itu belum diinput. Hubungi admin packing.</dd>
                            <dt>Kursor hilang dari kolom scan?</dt>
                            <dd>Klik area kosong di halaman atau langsung ketik/scan, kursor kembali ke kolom scan.</dd>
                        </dl>
                    </section>
                </div>
            </div>
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
            // Panduan / tur sedang terbuka: jangan pindahkan fokus ke kolom scan
            if (document.querySelector('.modal.show') || spotIdx >= 0) return;

            const a = document.activeElement, inField = a && /INPUT|SELECT|TEXTAREA/.test(a.tagName);

            // Ketik di mana saja langsung masuk ke kolom scan
            if (!inField && e.key.length === 1 && !e.ctrlKey && !e.metaKey && !e.altKey) el('scanInput').focus();
        });

        // ===== Panduan: sorot elemen asli + tur langkah demi langkah =====
        const gEl = el('psGuide'), gBs = bootstrap.Modal.getOrCreateInstance(gEl);
        const TOUR_KEY = 'ps_tour_seen_{{ Auth::id() }}';
        const STEPS = [
            { sel: '.ps-head-r', sec: 'psg-atas', lbl: 'Tombol di kanan atas',
              d: '<b>Panduan</b> membuka halaman bantuan ini · <b>Kembali</b> ke daftar Packing Out.' },
            { sel: '.ps-po-row', sec: 'psg-po', lbl: 'Pilih PO, Dest & Tgl Shipment',
              d: 'Pilih PO lalu Dest. Data karton langsung dimuat dan karton belum full pertama otomatis jadi karton aktif.' },
            { sel: '.ps-scan-box', sec: 'psg-scan', lbl: 'Kolom scan',
              d: 'Scan barcode SKU (atau ketik lalu <b>Enter</b>/<b>Simpan</b>). Setiap scan = +1 pcs dan langsung tersimpan.' },
            { sel: '#scanCard', sec: 'psg-hasil', lbl: 'Hasil scan',
              d: 'Notifikasi muncul di atas layar. Kartu berkedip <b>hijau</b> = tersimpan, <b>merah</b> = ditolak/gagal, disertai bunyi.' },
            { sel: '#cartonCard', sec: 'psg-karton', lbl: 'Karton aktif',
              d: 'Karton yang sedang diisi: status, sisa pcs, dan daftar SKU dengan jumlah terisi / qty.' },
            { sel: '#progCard', sec: 'psg-prog', lbl: 'Progress PO',
              d: 'Persen pcs terisi, jumlah karton full, sisa pcs, dan progres per size.' },
            { sel: '#mapCard', sec: 'psg-map', lbl: 'Peta karton',
              d: 'Semua karton PO dengan warna status. Klik kotak untuk pindah karton, atau ketik nomornya lalu <b>Buka</b>.' },
            { sel: '#actCard', sec: 'psg-act', lbl: 'Aktivitas terakhir',
              d: 'Scan di sesi ini. <b>↺ Batalkan scan terakhir</b> untuk membatalkan salah scan.' },
        ];
        let spotTok = 0, spotIdx = -1, tourFirst = false;

        function gGo(id, smooth) {
            const t = gEl.querySelector(id);
            if (t) t.scrollIntoView({ behavior: smooth ? 'smooth' : 'auto', block: 'start' });
        }

        function spotClear() {
            spotTok++;
            document.querySelectorAll('.ps-spot, .ps-spot-l, .ps-spot-cv').forEach(x => x.remove());
            removeEventListener('scroll', spotOff, true);
            removeEventListener('resize', spotOff);
        }

        // Tutup tur & kembali ke panduan di bagian langkah terakhir yang dilihat
        function spotOff() {
            const s = STEPS[spotIdx], first = tourFirst;
            spotClear();
            spotIdx = -1;
            if (first) {
                tourFirst = false;
                try { localStorage.setItem(TOUR_KEY, '1'); } catch (e) {}
            }
            gEl.addEventListener('shown.bs.modal', () => gGo(first || !s ? '#psg-peta' : '#' + s.sec), { once: true });
            gBs.show();
        }

        function spotGo(i) {
            if (i < 0 || i >= STEPS.length) return;
            spotClear();
            spotIdx = i;

            const s = STEPS[i], n = STEPS.length;
            const target = [...document.querySelectorAll(s.sel)].find(x => x.offsetParent !== null);
            if (!target) { spotOff(); return; }

            const tok = spotTok;
            if (document.activeElement) document.activeElement.blur();
            target.scrollIntoView({ behavior: 'smooth', block: 'center' });

            // Tunggu scroll berhenti (posisi elemen stabil beberapa frame), baru pasang sorotan
            let last = null, still = 0;
            const t0 = performance.now();
            (function wait() {
                if (tok !== spotTok) return;
                const top = target.getBoundingClientRect().top;
                still = last !== null && Math.abs(top - last) < 0.5 ? still + 1 : 0;
                last = top;
                if (still >= 6 || performance.now() - t0 > 2000) draw(); else requestAnimationFrame(wait);
            })();

            function draw() {
                const r = target.getBoundingClientRect(), pad = 6;

                const cv = document.createElement('div');
                cv.className = 'ps-spot-cv';
                cv.onclick = spotOff;

                const box = document.createElement('div');
                box.className = 'ps-spot';
                Object.assign(box.style, { left: r.left - pad + 'px', top: r.top - pad + 'px', width: r.width + pad * 2 + 'px', height: r.height + pad * 2 + 'px' });

                const l = document.createElement('div');
                l.className = 'ps-spot-l';
                l.innerHTML = `<div class="st"><span>Langkah ${i + 1} dari ${n}</span><span class="dots">${STEPS.map((_, k) => `<i class="${k === i ? 'on' : k < i ? 'done' : ''}"></i>`).join('')}</span></div>
                    <b>${esc(s.lbl)}</b><span class="d">${s.d}</span>
                    <div class="nav"><button type="button" data-nav="prev" ${i ? '' : 'disabled'}>‹ Sebelumnya</button>
                        <button type="button" data-nav="next" class="pri">${i === n - 1 ? 'Selesai ✓' : 'Berikutnya ›'}</button></div>
                    <small><kbd>←</kbd><kbd>→</kbd> pindah langkah · <kbd>Esc</kbd>, scroll, atau klik area gelap untuk kembali ke panduan</small>`;
                l.onclick = e => {
                    const b = e.target.closest('[data-nav]');
                    if (!b) return;
                    if (b.dataset.nav === 'prev') spotGo(i - 1);
                    else if (i === n - 1) spotOff();
                    else spotGo(i + 1);
                };

                document.body.append(cv, box, l);

                const lh = l.offsetHeight, below = r.bottom + 16 + lh < innerHeight;
                l.style.left = Math.max(8, Math.min(innerWidth - l.offsetWidth - 8, r.left)) + 'px';
                l.style.top = (below ? r.bottom + 16 : Math.max(8, r.top - lh - 16)) + 'px';

                const nx = l.querySelector('[data-nav="next"]');
                if (nx) nx.focus({ preventScroll: true });

                // Mulai dengarkan scroll user setelah sorotan tampil
                setTimeout(() => {
                    if (tok !== spotTok || !document.querySelector('.ps-spot')) return;
                    addEventListener('scroll', spotOff, true);
                    addEventListener('resize', spotOff);
                }, 250);
            }
        }

        el('guideBtn').addEventListener('click', e => {
            e.stopPropagation();
            gBs.show();
        });

        // Klik di dalam panduan: nomor/nav → lompat ke bagian; kotak/tombol "Tunjukkan" → sorot di halaman
        gEl.addEventListener('click', e => {
            const a = e.target.closest('.ps-map-n, .ps-g-nav a');
            if (a) {
                e.preventDefault();
                e.stopPropagation();
                gGo(a.getAttribute('href'), true);
                return;
            }

            const b = e.target.closest('[data-spot], [data-tour]');
            if (!b) return;

            const idx = b.dataset.tour !== undefined ? 0 : Math.max(0, STEPS.findIndex(x => x.sel === b.dataset.spot));
            gEl.addEventListener('hidden.bs.modal', () => spotGo(idx), { once: true });
            gBs.hide();
        });

        // Panduan ditutup (bukan karena masuk tur): kursor kembali ke kolom scan
        gEl.addEventListener('hidden.bs.modal', () => {
            setTimeout(() => { if (spotIdx < 0) focusScan(); }, 0);
        });

        document.addEventListener('keydown', e => {
            if (spotIdx < 0) return;
            if (e.key === 'Escape') { e.preventDefault(); spotOff(); return; }
            if (!document.querySelector('.ps-spot')) return;
            if (e.key === 'ArrowRight') { e.preventDefault(); spotIdx === STEPS.length - 1 ? spotOff() : spotGo(spotIdx + 1); }
            else if (e.key === 'ArrowLeft') { e.preventDefault(); spotGo(spotIdx - 1); }
        }, true);

        // Tur otomatis saat halaman pertama kali dibuka oleh user ini (per user per browser)
        function autoTour() {
            let seen = false;
            try { seen = localStorage.getItem(TOUR_KEY) === '1'; } catch (e) { seen = true; }
            if (seen) return;

            setTimeout(() => {
                if (spotIdx < 0 && !document.querySelector('.modal.show')) {
                    tourFirst = true;
                    spotGo(0);
                }
            }, 600);
        }

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
            width: 'resolve',
            placeholder: 'Pilih PO'
        }).on('change', function () {
            fillDest(this.value);
        });

        $('#destSelect').select2({
            theme: 'bootstrap4',
            width: 'resolve'
        }).on('change', function () {
            const x = PO_DEST.find(r => String(r.isi) === String(this.value));
            $('#tglShipment').text(x && x.tgl_shipment ? x.tgl_shipment : '-');

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

        // Awal buka halaman: PO masih kosong ("Pilih PO"), karton belum dimuat
        fillDest('');
        autoTour();
    </script>
@endsection
