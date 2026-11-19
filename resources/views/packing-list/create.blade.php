@extends('layouts.index')

@section('custom-link')
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

    <style type="text/css">
        .t2-container {
            --t2-muted: #6b7a90;
            --t2-line: #d9e3ef;
            color: #172033;
            font-size: 13px;
        }

        .t2-muted {
            color: var(--t2-muted);
        }

        .t2-card {
            background: #fff;
            border: 1px solid var(--t2-line);
            border-radius: 10px;
            padding: 17px;
            margin-bottom: 16px;
            box-shadow: 0 2px 8px rgba(29, 61, 101, 0.06);
        }

        /* Info PO */
        .t2-order {
            background: linear-gradient(110deg, #f4f8ff, #fff);
            border-color: #cbdcf5;
        }

        .t2-ordergrid {
            display: grid;
            grid-template-columns: 1.05fr 1fr 1.2fr 1.1fr 1.1fr .9fr;
        }

        .t2-oi {
            padding: 0 15px;
            border-right: 1px solid #dce5f0;
        }

        .t2-oi:first-child {
            padding-left: 0;
        }

        .t2-oi:last-child {
            border: 0;
        }

        .t2-lbl {
            font-size: 10px;
            font-weight: 800;
            color: #718096;
            text-transform: uppercase;
            margin-bottom: 6px;
        }

        .t2-val {
            font-weight: 700;
            line-height: 1.35;
        }

        .t2-tag {
            display: inline-block;
            background: #e8f1ff;
            color: #2563eb;
            border-radius: 12px;
            padding: 4px 8px;
            font-size: 10px;
            font-weight: 800;
            margin-top: 6px;
        }

        /* Layout atas: Tentukan Carton + Progress */
        .t2-top-layout {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 300px;
            gap: 16px;
            margin-bottom: 16px;
            align-items: stretch;
        }

        .t2-top-layout .t2-card {
            margin-bottom: 0;
            height: 100%;
        }

        .t2-title {
            font-size: 15px;
            font-weight: 800;
            margin-bottom: 13px;
        }

        .t2-toolbar {
            display: grid;
            grid-template-columns: 110px 110px 120px 150px minmax(160px, 1fr);
            gap: 12px;
            align-items: end;
        }

        .t2-field label {
            display: block;
            font-size: 11px;
            font-weight: 800;
            color: #52637e;
            margin-bottom: 5px;
        }

        .t2-container .t2-input {
            height: 38px;
            border: 1px solid #cdd9e8;
            border-radius: 7px;
            padding: 0 10px;
            width: 100%;
            background: #fff;
            color: #253956;
        }

        .t2-container .t2-input:focus {
            outline: 0;
            border-color: #5b91ef;
            box-shadow: 0 0 0 3px #eaf2ff;
        }

        .t2-order .select2-container--bootstrap4 .select2-selection--single {
            height: 36px !important;
            font-size: 13px;
            font-weight: 700;
            border-color: #bfcfe4;
            border-radius: 7px;
        }

        .t2-order .select2-container--bootstrap4 .select2-selection__rendered {
            line-height: 34px !important;
        }

        .t2-btn {
            height: 38px;
            border-radius: 7px;
            border: 1px solid #cad8ea;
            background: #fff;
            color: #2563eb;
            font-weight: 800;
            padding: 0 13px;
            cursor: pointer;
        }

        .t2-btn.primary {
            background: #2563eb;
            color: #fff;
            border-color: #2563eb;
        }

        .t2-btn.soft {
            background: #eef5ff;
            border-color: #d2e1fb;
        }

        .t2-btn.danger {
            color: #d83c43;
            background: #fff5f5;
            border-color: #ffd9dc;
        }

        .t2-help {
            font-size: 10px;
            color: #7c8ba1;
            margin-top: 8px;
        }

        .t2-chips {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
            margin-top: 8px;
        }

        .t2-chip {
            background: #e8f1ff;
            color: #2563eb;
            border-radius: 14px;
            padding: 5px 8px;
            font-size: 10px;
            font-weight: 800;
        }

        /* Progress */
        .t2-sidecard .t2-stat {
            display: flex;
            justify-content: space-between;
            padding: 7px 0;
        }

        .t2-miniProgress {
            height: 9px;
            background: #e9eef5;
            border-radius: 10px;
            overflow: hidden;
            margin: 8px 0 14px;
        }

        .t2-miniBar {
            height: 100%;
            width: 0;
            background: #20ae74;
            transition: width .3s;
        }

        /* 2. Isi Packing per SKU */
        .t2-info {
            font-size: 11px;
            line-height: 1.5;
            color: #64748b;
            background: #f7f9fc;
            border-radius: 8px;
            padding: 10px;
            margin-bottom: 12px;
        }

        .t2-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 7px;
            margin: 14px 0 9px;
        }

        .t2-tab {
            border: 1px solid #d4dfec;
            background: #fff;
            padding: 7px 11px;
            border-radius: 7px;
            font-size: 11px;
            font-weight: 800;
            cursor: pointer;
        }

        .t2-tab.active {
            background: #eaf2ff;
            color: #2563eb;
            border-color: #c7dcfb;
        }

        .t2-tablewrap {
            border: 1px solid #dce5ef;
            border-radius: 8px;
            overflow: auto;
        }

        .t2-matrix {
            width: 100%;
            border-collapse: collapse;
        }

        .t2-matrix th {
            background: #f3f7fc;
            color: #304666;
            text-align: left;
            font-size: 10px;
            padding: 9px;
            border-bottom: 1px solid #dce5ef;
            white-space: nowrap;
        }

        .t2-matrix td {
            padding: 7px 8px;
            border-bottom: 1px solid #edf1f5;
            font-size: 11px;
            vertical-align: middle;
        }

        .t2-matrix tr:last-child td {
            border: 0;
        }

        .t2-auto {
            background: #f8fbff;
        }

        .t2-color-badge {
            display: inline-block;
            background: #e6f8ee;
            color: #188057;
            border-radius: 12px;
            padding: 4px 8px;
            font-size: 10px;
            font-weight: 700;
        }

        .t2-ws {
            font-weight: 800;
            color: #40587c;
        }

        .t2-sizeMatrix .t2-sizeHead {
            background: #eaf2ff;
            color: #245da9;
            font-size: 10px;
            padding: 8px 3px;
            text-align: center;
            min-width: 125px;
        }

        .t2-sizeCell {
            text-align: center;
            background: #fbfdff;
            vertical-align: top !important;
            padding: 6px 4px !important;
        }

        .t2-sizeInfo {
            font-size: 12px;
            line-height: 1.45;
            text-align: left;
            margin-bottom: 4px;
        }

        .t2-miniLabel {
            display: inline-block;
            width: 36px;
            color: #8090a5;
            font-size: 12px;
        }

        .t2-container .t2-qtyInput {
            width: 100%;
            height: 30px;
            padding: 0 5px;
            font-size: 12px;
            border: 1px solid #cdd9e8;
            border-radius: 7px;
        }

        .t2-container .t2-qtyInput:disabled {
            background: #eef1f5;
            color: #a1adbd;
            cursor: not-allowed;
        }

        .t2-container .t2-over {
            border-color: #e5484d;
            background: #fff3f3;
        }

        .t2-barcode {
            font-size: 11px;
            color: #8290a4;
            margin-top: 3px;
        }

        .t2-sizeMatrix td:first-child,
        .t2-sizeMatrix th:first-child {
            position: sticky;
            left: 0;
            background: #fff;
            z-index: 2;
            min-width: 100px;
        }

        .t2-sizeMatrix th:first-child {
            background: #f3f7fc;
        }

        .t2-footerActions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 13px;
        }

        .t2-total b {
            font-size: 17px;
            margin-left: 6px;
        }

        /* 3. Hasil Packing List */
        .t2-savedTable {
            width: 100%;
            border-collapse: collapse;
        }

        .t2-savedTable th {
            background: #f3f7fc;
            text-align: left;
            padding: 9px;
            font-size: 10px;
        }

        .t2-savedTable td {
            padding: 8px;
            border-bottom: 1px solid #edf1f5;
            font-size: 11px;
        }

        /* Modal Preview */
        .t2-cartonPreviewGrid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 16px;
            margin-top: 14px;
        }

        .t2-cartonCard {
            border: 1px solid #d8e2ee;
            border-radius: 12px;
            background: #fff;
            padding: 12px;
            box-shadow: 0 2px 8px rgba(29, 61, 101, 0.06);
        }

        .t2-cartonHead {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .t2-cartonNo {
            font-size: 14px;
            font-weight: 800;
            color: #172b4d;
        }

        .t2-cartonTotal {
            font-size: 10px;
            font-weight: 800;
            color: #2563eb;
            background: #edf4ff;
            padding: 5px 8px;
            border-radius: 12px;
        }

        .t2-cartonBox {
            position: relative;
            background: #d9a86c;
            border: 3px solid #b98245;
            border-radius: 7px;
            padding: 17px 10px 11px;
            min-height: 145px;
            box-shadow: inset 0 0 0 3px #e7bd83;
        }

        .t2-cartonBox::before {
            content: "";
            position: absolute;
            left: 10%;
            right: 10%;
            top: 7px;
            height: 12px;
            border: 2px solid #b98245;
            border-bottom: 0;
            border-radius: 5px 5px 0 0;
            background: #e8bd80;
        }

        .t2-cartonContents {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 7px;
            position: relative;
            z-index: 1;
        }

        .t2-itemSymbol {
            background: #fff8e9;
            border: 1px solid #c99450;
            border-radius: 7px;
            padding: 7px 3px 5px;
            text-align: center;
            min-height: 65px;
            min-width: 0;
        }

        .t2-itemIcon {
            font-size: 20px;
            line-height: 20px;
        }

        .t2-itemSize {
            font-size: 10px;
            font-weight: 800;
            color: #172b4d;
            margin-top: 3px;
        }

        .t2-itemQty {
            font-size: 10px;
            font-weight: 800;
            color: #188057;
        }

        .t2-itemMeta {
            font-size: 8px;
            line-height: 1.3;
            color: #64748b;
            overflow-wrap: anywhere;
            margin-top: 2px;
        }

        .t2-cartonStyle {
            font-size: 10px;
            color: #66758b;
            margin: 8px 0 0;
            line-height: 1.35;
        }

        .t2-cartonStyle b {
            color: #314767;
        }

        .t2-previewSummary {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin: 8px 0 14px;
        }

        .t2-summaryPill {
            background: #f2f6fb;
            border: 1px solid #dbe5ef;
            border-radius: 8px;
            padding: 7px 10px;
            font-size: 10px;
            color: #52637e;
        }

        .t2-summaryPill b {
            color: #172b4d;
        }

        @media (max-width: 1050px) {
            .t2-top-layout {
                grid-template-columns: 1fr;
            }

            .t2-ordergrid {
                grid-template-columns: repeat(2, 1fr);
                gap: 12px;
            }

            .t2-oi {
                border: 0;
                padding: 0;
            }
        }

        @media (max-width: 700px) {
            .t2-ordergrid {
                grid-template-columns: 1fr;
            }

            .t2-toolbar {
                grid-template-columns: 1fr;
            }
        }

        /* Tombol header (Panduan & Kembali): hover tetap terang, bukan hitam (timpa style.css global) */
        .pl-head-r .btn-light:hover,
        .pl-head-r .btn-light:focus-visible {
            background-color: #e2e6ea !important;
            border-color: #e2e6ea !important;
            color: #1d2433 !important;
        }

        /* ===== Panduan ===== */
        #plGuide, .pl-spot-l {
            --g-ink: #1d2433; --g-muted: #6b7385; --g-line: #ebe7de; --g-soft: #f7f5f0;
            --g-pri: #4057e3; --g-ok: #1a9d63; --g-wip: #e39a12; --g-err: #e0483e; --g-empty: #e4e1d8; --g-acc: #ff971f;
        }
        .pl-g { padding: 0 20px 20px; color: var(--g-ink); font-size: 13.5px; }
        .pl-g-nav { position: sticky; top: 0; z-index: 2; display: flex; flex-wrap: wrap; gap: 6px; padding: 12px 0; background: #fff; border-bottom: 1px solid var(--g-line); margin-bottom: 4px; }
        .pl-g-nav a { font-size: 12px; font-weight: 600; padding: 4px 10px; border-radius: 20px; background: var(--g-soft); color: var(--g-pri); text-decoration: none; border: 1px solid var(--g-line); }
        .pl-g-nav a:hover { background: var(--g-pri); color: #fff; }
        .pl-g section { padding-top: 16px; scroll-margin-top: 56px; }
        .pl-g h6 { display: flex; align-items: center; justify-content: space-between; gap: 8px; flex-wrap: wrap; font-weight: 700; color: var(--g-pri); border-left: 4px solid var(--g-acc); padding-left: 8px; margin-bottom: 10px; }
        .pl-g ul { padding-left: 18px; margin-bottom: 0; }
        .pl-g li { margin-bottom: 6px; line-height: 1.5; }
        .pl-g-t { margin-bottom: 0; }
        .pl-g-t td { vertical-align: top; padding: 7px 8px; line-height: 1.5; }
        .pl-g-t td:first-child { width: 210px; white-space: nowrap; color: var(--g-pri); }
        .pl-g-btn { display: inline-block; font-size: 12px; padding: 2px 8px; border: 1px solid #c5ccd6; border-radius: .3rem; background: #fff; }
        .pl-g-flow { display: flex; align-items: stretch; gap: 8px; margin-bottom: 12px; flex-wrap: wrap; }
        .pl-g-flow > div { flex: 1; min-width: 130px; background: var(--g-soft); border: 1px solid var(--g-line); border-radius: .5rem; padding: 8px 12px; }
        .pl-g-flow > div.ok { background: #e7f7ee; border-color: #b9e8cf; }
        .pl-g-flow b { display: block; color: var(--g-pri); } .pl-g-flow span { font-size: 12px; color: var(--g-muted); }
        .pl-g-flow > i { align-self: center; font-style: normal; font-weight: 700; color: var(--g-acc); }
        .pl-g-faq dt { color: var(--g-pri); margin-top: 8px; } .pl-g-faq dd { margin: 2px 0 0; }
        .pl-g kbd { font-size: 11px; padding: 1px 5px; }
        .pl-g-show { border: 1px solid var(--g-pri); background: #fff; color: var(--g-pri); font-size: 11.5px; font-weight: 600; border-radius: 20px; padding: 2px 10px; }
        .pl-g-show:hover, .pl-g-show.pri { background: var(--g-pri); color: #fff; }
        .pl-g-show.pri:hover { filter: brightness(.92); }
        .pl-g-note { font-size: 12.5px; color: var(--g-muted); margin-bottom: 10px; }

        /* Panduan: peta halaman */
        .pl-map { background: var(--g-soft); border: 1px solid var(--g-line); border-radius: .6rem; padding: 14px; max-width: 760px; display: flex; flex-direction: column; gap: 12px; }
        .pl-map-z { position: relative; cursor: pointer; transition: box-shadow .15s; }
        .pl-map-z:hover { box-shadow: 0 0 0 2px var(--g-acc); z-index: 6; }
        .pl-map-n { position: absolute; top: -9px; left: -9px; z-index: 2; width: 22px; height: 22px; border-radius: 50%; background: var(--g-acc); color: #fff !important; font-size: 12px; font-weight: 700; display: grid; place-items: center; text-decoration: none; box-shadow: 0 0 0 2px #fff; }
        .pl-map-n:hover, .pl-map-n:focus-visible { background: var(--g-pri); z-index: 5; }
        .pl-map-tip { position: absolute; top: calc(100% + 8px); left: -4px; width: 230px; padding: 8px 10px; border-radius: .45rem; background: #1d2433; color: #fff; font-size: 12px; font-weight: 400; line-height: 1.45; text-align: left; box-shadow: 0 6px 18px rgba(0,0,0,.25); opacity: 0; visibility: hidden; transform: translateY(-4px); transition: opacity .15s, transform .15s, visibility .15s; pointer-events: none; }
        .pl-map-tip:before { content: ""; position: absolute; top: -5px; left: 10px; width: 10px; height: 10px; background: #1d2433; transform: rotate(45deg); }
        .pl-map-n.tip-l .pl-map-tip { left: auto; right: -4px; }
        .pl-map-n.tip-l .pl-map-tip:before { left: auto; right: 10px; }
        .pl-map-tip b { display: block; font-size: 12.5px; margin-bottom: 2px; color: #ffc98a; }
        .pl-map-tip small { display: block; margin-top: 5px; font-size: 10.5px; color: rgba(255,255,255,.65); }
        .pl-map-n:hover .pl-map-tip, .pl-map-n:focus-visible .pl-map-tip { opacity: 1; visibility: visible; transform: none; }
        .pl-map-head { display: flex; justify-content: space-between; align-items: center; gap: 10px; padding: 6px 8px 6px 10px; border-radius: .4rem; background: var(--sb-color, #082149); }
        .pl-map-head b { font-size: 12px; color: #fff; }
        .pl-map-head .r { display: flex; gap: 4px; padding: 3px; border-radius: .35rem; }
        .pl-map-head .r i { width: 40px; height: 14px; border-radius: 3px; background: #fff; }
        .pl-map-grid { display: grid; grid-template-columns: 1.6fr 1fr; gap: 12px; }
        .pl-map-card { background: #fff; border: 1px solid var(--g-line); border-radius: .45rem; padding: 8px; }
        .pl-map-card .h { font-size: 9.5px; font-weight: 700; letter-spacing: .6px; text-transform: uppercase; color: var(--g-muted); margin: 0 0 6px 10px; }
        .pl-map-card .row6 { display: grid; grid-template-columns: repeat(6, 1fr); gap: 4px; }
        .pl-map-card .row6 i { height: 16px; border-radius: .25rem; background: var(--g-soft); border: 1px solid var(--g-line); }
        .pl-map-card .row6 i.sel { border: 1.5px solid var(--g-pri); background: #fff; }
        .pl-map-card .inp { display: grid; grid-template-columns: 1fr 1fr auto 1fr 1fr; gap: 4px; margin-bottom: 6px; }
        .pl-map-card .inp i { height: 14px; border-radius: .25rem; border: 1px solid #c5ccd6; background: #fff; }
        .pl-map-card .inp em { width: 34px; height: 14px; border-radius: .25rem; background: #dfe5ff; }
        .pl-map-card .chips { display: flex; gap: 3px; }
        .pl-map-card .chips i { width: 30px; height: 10px; border-radius: 10px; background: #dfe5ff; }
        .pl-map-card .st { height: 6px; border-radius: 3px; background: var(--g-empty); margin-top: 6px; }
        .pl-map-card .bar { height: 7px; border-radius: 4px; background: var(--g-empty); margin-top: 8px; overflow: hidden; }
        .pl-map-card .bar i { display: block; height: 100%; width: 45%; background: var(--g-pri); }
        .pl-map-card .tabs { display: flex; gap: 3px; margin-bottom: 6px; }
        .pl-map-card .tabs i { height: 10px; width: 36px; border-radius: 10px; border: 1px solid #c5ccd6; }
        .pl-map-card .tabs i.on { background: var(--g-pri); border-color: var(--g-pri); }
        .pl-map-card .mx { display: grid; grid-template-columns: 2fr repeat(5, 1fr); gap: 3px; }
        .pl-map-card .mx i { height: 14px; border-radius: .2rem; background: var(--g-soft); border: 1px solid var(--g-line); }
        .pl-map-card .mx i.q { background: #fff; border-color: #c5ccd6; }
        .pl-map-card .ft { display: flex; justify-content: flex-end; margin-top: 6px; }
        .pl-map-card .ft i { width: 90px; height: 14px; border-radius: .25rem; background: var(--g-pri); }
        .pl-map-card .hd { display: flex; justify-content: flex-end; gap: 4px; margin-bottom: 6px; }
        .pl-map-card .hd i { width: 34px; height: 12px; border-radius: .25rem; background: var(--g-soft); border: 1px solid var(--g-line); }
        .pl-map-card .hd i.d { background: #fde3e1; border-color: #f6c2be; }
        .pl-map-card .hd i.p { background: var(--g-pri); border-color: var(--g-pri); }
        .pl-map-card .rw { height: 6px; border-radius: 3px; background: var(--g-empty); margin-top: 5px; }

        /* Panduan: sorot elemen & tur */
        .pl-spot { position: fixed; z-index: 2000; border-radius: .6rem; box-shadow: 0 0 0 9999px rgba(17,24,39,.6); outline: 3px solid #ff971f; outline-offset: 3px; pointer-events: none; animation: plSpot 1.2s ease-in-out infinite; }
        @keyframes plSpot { 50% { outline-offset: 8px; outline-color: rgba(255,151,31,.6); } }
        .pl-spot-cv { position: fixed; inset: 0; z-index: 1999; cursor: pointer; }
        .pl-spot-l { position: fixed; z-index: 2001; background: #fff; color: var(--g-ink); font-size: 13px; padding: 10px 12px; border-radius: .5rem; box-shadow: 0 6px 18px rgba(0,0,0,.25); width: 340px; max-width: calc(100vw - 16px); }
        .pl-spot-l .st { display: flex; justify-content: space-between; align-items: center; font-size: 10.5px; font-weight: 700; letter-spacing: .6px; text-transform: uppercase; color: var(--g-pri); margin-bottom: 4px; }
        .pl-spot-l .dots { display: flex; gap: 4px; }
        .pl-spot-l .dots i { width: 7px; height: 7px; border-radius: 50%; background: #e2e6ec; }
        .pl-spot-l .dots i.done { background: #b7c2f5; }
        .pl-spot-l .dots i.on { background: var(--g-acc); width: 16px; border-radius: 4px; }
        .pl-spot-l > b { display: block; font-weight: 700; }
        .pl-spot-l > span.d { display: block; color: #3d4450; font-size: 12.5px; line-height: 1.5; margin-top: 3px; }
        .pl-spot-l > span.d b { color: var(--g-ink); }
        .pl-spot-l .nav { display: flex; justify-content: space-between; gap: 8px; margin-top: 10px; }
        .pl-spot-l .nav button { border: 1px solid #c5ccd6; background: #fff; color: var(--g-ink); font-size: 12.5px; font-weight: 600; border-radius: .35rem; padding: 4px 12px; }
        .pl-spot-l .nav button:disabled { opacity: .4; }
        .pl-spot-l .nav button.pri { background: var(--g-pri); border-color: var(--g-pri); color: #fff; }
        .pl-spot-l .nav button:not(:disabled):hover { filter: brightness(.95); }
        .pl-spot-l small { display: block; color: var(--g-muted); font-size: 11.5px; margin-top: 6px; padding-top: 5px; border-top: 1px solid #e2e6ec; }
        .pl-spot-l kbd { font-size: 10px; padding: 0 4px; }

        @media (max-width: 576px) {
            .pl-g-t td:first-child { width: auto; white-space: normal; }
            .pl-g-flow > i { display: none; }
            .pl-map-grid { grid-template-columns: 1fr; }
        }
    </style>
@endsection

@section('content')
    <div class="card mb-3">
        <div class="card-header bg-sb d-flex align-items-center">
            <h5 class="card-title fw-bold mb-0">
                <i class="fas fa-box-open"></i> Packing List — Baru
            </h5>
            <div class="pl-head-r d-flex gap-2 ms-auto" style="margin-block: -.5rem;">
                <button type="button" class="btn btn-light btn-sm" id="guideBtn">
                    <i class="fas fa-book-open"></i> Panduan
                </button>
                <a class="btn btn-light btn-sm" href="{{ route('packing-list-karton') }}">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
    </div>

    <div class="t2-container">
        {{-- Info PO --}}
        <div class="t2-card t2-order">
            <div class="t2-ordergrid">
                <div class="t2-oi">
                    <div class="t2-lbl">Pilih No. PO</div>
                    <select class="form-control select2bs4" id="poSelect" style="width: 100%;">
                        <option value="" selected disabled>Pilih PO</option>
                        @foreach ($data_po as $datapo)
                            <option value="{{ $datapo->isi }}">{{ $datapo->tampil }}</option>
                        @endforeach
                    </select>
                    <div class="t2-help">Pilih PO terlebih dahulu.</div>
                </div>
                <div class="t2-oi">
                    <div class="t2-lbl">Tgl. Shipment</div>
                    <div class="t2-val" id="poShipment">-</div>
                </div>
                <div class="t2-oi">
                    <div class="t2-lbl">Buyer</div>
                    <div class="t2-val" id="poBuyer">-</div>
                </div>
                <div class="t2-oi">
                    <div class="t2-lbl">Worksheet</div>
                    <div class="t2-val" id="poWorksheet">-</div>                
                </div>
                <div class="t2-oi">
                    <div class="t2-lbl">Style</div>
                    <div class="t2-val" id="poStyle">-</div>
                </div>
                <div class="t2-oi">
                    <div class="t2-lbl">Dest</div>
                    <div class="t2-val" id="poDest">-</div>
                </div>
            </div>
        </div>

        {{-- 1. Tentukan Carton + Progress Packing --}}
        <div class="t2-top-layout">
            <div class="t2-card" id="plCartonCard">
                <div class="t2-title">1. Tentukan Carton</div>
                <div class="t2-toolbar">
                    <div class="t2-field">
                        <label>Carton Mulai <span class="text-danger">*</span></label>
                        <input class="t2-input" id="start" type="number" min="1" step="1" value="1" placeholder="Contoh: 1" onkeydown="onlyInteger(event)">
                    </div>
                    <div class="t2-field">
                        <label>Carton Sampai <span class="text-danger">*</span></label>
                        <input class="t2-input" id="end" type="number" min="1" step="1" value="5" placeholder="Contoh: 5" onkeydown="onlyInteger(event)">
                    </div>
                    <button type="button" class="t2-btn soft" onclick="setRange()">Pilih Carton</button>
                    <div class="t2-field">
                        <label>Tipe Packing <span class="text-danger">*</span></label>
                        <input class="t2-input" id="tipePack" type="text" maxlength="255" placeholder="Contoh: SOLID">
                    </div>
                    <div class="t2-field">
                        <label>Keterangan</label>
                        <input class="t2-input" id="keterangan" type="text" maxlength="255" placeholder="Opsional">
                    </div>
                </div>
                <div class="t2-help" style="font-size:13px">Bisa memilih banyak carton sekaligus. Data qty yang diinput akan diterapkan ke seluruh carton yang dipilih.</div>
                <div class="t2-help" style="font-size:13px" id="usedCartons"></div>
                <div class="t2-chips" id="chips"></div>
            </div>

            <div class="t2-card t2-sidecard" id="plProgCard">
                <div class="t2-title">Progress Packing</div>
                <div class="t2-stat"><span>Total Qty PO</span><b id="totalPoVal">0 pcs</b></div>
                <div class="t2-stat"><span>Sudah Packing</span><b id="packed">0 pcs</b></div>
                <div class="t2-stat"><span>Balance</span><b id="balance">0 pcs</b></div>
                <div class="t2-miniProgress">
                    <div class="t2-miniBar" id="miniBar"></div>
                </div>
                <div class="t2-stat"><span>Progress</span><b id="pct">0%</b></div>
            </div>
        </div>

        {{-- 2. Isi Packing per Barcode --}}
        <div class="t2-card" id="plIsiCard">
            <div class="t2-title">2. Isi Packing per Barcode</div>
            <div class="t2-info">
                Semua detail dari Sales Order sudah ditampilkan otomatis. <b>Semua size ditampilkan horizontal</b>.
                User cukup isi Qty Packing pada size yang masuk ke carton.
            </div>
            <div class="t2-tabs" id="wsTabs"></div>
            <div class="t2-tablewrap">
                <table class="t2-matrix t2-sizeMatrix">
                    <thead id="masterHead"></thead>
                    <tbody id="masterRows"></tbody>
                </table>
            </div>
            <div class="t2-footerActions">
                <div class="t2-total">Total Qty per Carton <b id="cartonTotal">0</b></div>
                <button type="button" class="t2-btn primary" onclick="saveSelected()">
                    <i class="fas fa-save"></i> Simpan ke Carton Terpilih
                </button>
            </div>
        </div>

        {{-- 3. Hasil Packing List --}}
        <div class="t2-card" id="plHasilCard">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="t2-title mb-0">3. Hasil Packing List</div>
                <div class="d-flex gap-2 align-items-center">
                    <div class="t2-summaryPill" style="font-size:13px">Jumlah Carton <b id="savedCartonCount">0</b></div>
                    <div class="t2-summaryPill" style="font-size:13px">Total Qty <b id="savedQtyTotal">0 pcs</b></div>
                    <button type="button" class="t2-btn soft" onclick="preview()">
                        <i class="fas fa-eye"></i> Preview
                    </button>
                    <button type="button" class="t2-btn danger" onclick="clearPacking()">Hapus Semua</button>
                    <button type="button" class="t2-btn primary" onclick="storePacking()">
                        <i class="fas fa-database"></i> Simpan Packing List
                    </button>
                </div>
            </div>
            <div id="saved"></div>
        </div>
    </div>

    {{-- Modal Preview Packing List --}}
    <div class="modal fade" id="modalPreviewPacking" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-sb text-light">
                    <h3 class="modal-title fs-5"><i class="fas fa-box-open"></i> Preview Packing List</h3>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body t2-container" id="previewBody"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-primary btn-sm" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Panduan halaman --}}
    <div class="modal fade" id="plGuide" tabindex="-1" aria-labelledby="plGuideTitle" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-sb text-light">
                    <div>
                        <small class="text-uppercase fw-bold" style="letter-spacing: .6px; opacity: .75;">Panduan</small>
                        <h5 class="modal-title fw-bold" id="plGuideTitle">Packing List — Baru</h5>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body pl-g">
                    <nav class="pl-g-nav">
                        <a href="#plg-peta">Peta halaman</a><a href="#plg-alur">Alur singkat</a><a href="#plg-atas">Tombol atas</a><a href="#plg-po">Pilih PO</a><a href="#plg-carton">Tentukan carton</a>
                        <a href="#plg-prog">Progress packing</a><a href="#plg-isi">Isi packing</a><a href="#plg-hasil">Hasil &amp; simpan</a><a href="#plg-tips">Tips &amp; FAQ</a>
                    </nav>

                    <section id="plg-peta">
                        <h6><span>Peta halaman</span> <button type="button" class="pl-g-show pri" data-tour="0"><i class="fa fa-play"></i> Mulai tur</button></h6>
                        <p class="pl-g-note">Klik <b>nomor</b> untuk membaca penjelasannya, atau klik <b>kotaknya</b> untuk melihat langsung di halaman.</p>
                        <div class="pl-map">
                            <div class="pl-map-head">
                                <b>Packing List — Baru</b>
                                <div class="pl-map-z r" data-spot=".pl-head-r"><a href="#plg-atas" class="pl-map-n tip-l" aria-label="Tombol di kanan atas">2<span class="pl-map-tip" role="tooltip"><b>Tombol di kanan atas</b>Panduan dan Kembali ke daftar Packing List.<small>Klik nomor untuk penjelasan lengkap</small></span></a><i></i><i></i></div>
                            </div>
                            <div class="pl-map-z pl-map-card" data-spot=".t2-order">
                                <a href="#plg-po" class="pl-map-n" aria-label="Pilih PO">3<span class="pl-map-tip" role="tooltip"><b>Pilih No. PO</b>Pilih PO, lalu info shipment, buyer, worksheet, style &amp; dest tampil otomatis.<small>Klik nomor untuk penjelasan lengkap</small></span></a>
                                <div class="h">Info PO</div>
                                <div class="row6"><i class="sel"></i><i></i><i></i><i></i><i></i><i></i></div>
                            </div>
                            <div class="pl-map-grid">
                                <div class="pl-map-z pl-map-card" data-spot="#plCartonCard">
                                    <a href="#plg-carton" class="pl-map-n" aria-label="Tentukan carton">4<span class="pl-map-tip" role="tooltip"><b>1. Tentukan Carton</b>Isi nomor carton mulai–sampai dan Tipe Packing, lalu Pilih Carton.<small>Klik nomor untuk penjelasan lengkap</small></span></a>
                                    <div class="h">1. Tentukan carton</div>
                                    <div class="inp"><i></i><i></i><em></em><i></i><i></i></div>
                                    <div class="chips"><i></i><i></i><i></i><i></i></div>
                                </div>
                                <div class="pl-map-z pl-map-card" data-spot="#plProgCard">
                                    <a href="#plg-prog" class="pl-map-n tip-l" aria-label="Progress packing">5<span class="pl-map-tip" role="tooltip"><b>Progress Packing</b>Total qty PO, qty yang sudah dimasukkan, balance, dan persen.<small>Klik nomor untuk penjelasan lengkap</small></span></a>
                                    <div class="h">Progress packing</div>
                                    <div class="st"></div><div class="st" style="width:70%"></div>
                                    <div class="bar"><i></i></div>
                                </div>
                            </div>
                            <div class="pl-map-z pl-map-card" data-spot="#plIsiCard">
                                <a href="#plg-isi" class="pl-map-n" aria-label="Isi packing per barcode">6<span class="pl-map-tip" role="tooltip"><b>2. Isi Packing per Barcode</b>Isi qty per size untuk 1 carton, lalu Simpan ke Carton Terpilih.<small>Klik nomor untuk penjelasan lengkap</small></span></a>
                                <div class="h">2. Isi packing per barcode</div>
                                <div class="tabs"><i class="on"></i><i></i><i></i></div>
                                <div class="mx"><i></i><i class="q"></i><i class="q"></i><i class="q"></i><i class="q"></i><i class="q"></i><i></i><i class="q"></i><i class="q"></i><i class="q"></i><i class="q"></i><i class="q"></i></div>
                                <div class="ft"><i></i></div>
                            </div>
                            <div class="pl-map-z pl-map-card" data-spot="#plHasilCard">
                                <a href="#plg-hasil" class="pl-map-n" aria-label="Hasil packing list">7<span class="pl-map-tip" role="tooltip"><b>3. Hasil Packing List</b>Daftar carton yang sudah diisi. Cek dengan Preview, lalu Simpan Packing List.<small>Klik nomor untuk penjelasan lengkap</small></span></a>
                                <div class="h">3. Hasil packing list</div>
                                <div class="hd"><i></i><i></i><i></i><i class="d"></i><i class="p"></i></div>
                                <div class="rw"></div><div class="rw" style="width:80%"></div><div class="rw"></div>
                            </div>
                        </div>
                    </section>

                    <section id="plg-alur">
                        <h6>1. Alur singkat</h6>
                        <div class="pl-g-flow">
                            <div><b>Pilih PO</b><span>detail SKU dimuat</span></div><i>→</i>
                            <div><b>Tentukan carton</b><span>no. mulai–sampai + tipe</span></div><i>→</i>
                            <div><b>Isi qty per size</b><span>isi untuk 1 carton</span></div><i>→</i>
                            <div><b>Simpan ke carton</b><span>masuk Hasil Packing List</span></div><i>→</i>
                            <div class="ok"><b>Simpan Packing List</b><span>baru tersimpan ke database</span></div>
                        </div>
                        <ul>
                            <li>Ulangi langkah <b>Tentukan carton → Isi qty → Simpan ke carton</b> untuk setiap komposisi isi carton yang berbeda.</li>
                            <li>Data baru benar-benar tersimpan setelah menekan <b>Simpan Packing List</b>. Sebelum itu, data hanya ada di halaman ini.</li>
                        </ul>
                    </section>

                    <section id="plg-atas">
                        <h6><span>2. Tombol di kanan atas</span> <button type="button" class="pl-g-show" data-spot=".pl-head-r"><i class="fa fa-crosshairs"></i> Tunjukkan di halaman</button></h6>
                        <table class="table table-sm pl-g-t">
                            <tr><td><span class="pl-g-btn">Panduan</span></td><td>Membuka halaman ini.</td></tr>
                            <tr><td><span class="pl-g-btn">Kembali</span></td><td>Kembali ke daftar Packing List. Data yang belum disimpan akan hilang.</td></tr>
                        </table>
                    </section>

                    <section id="plg-po">
                        <h6><span>3. Pilih No. PO</span> <button type="button" class="pl-g-show" data-spot=".t2-order"><i class="fa fa-crosshairs"></i> Tunjukkan di halaman</button></h6>
                        <table class="table table-sm pl-g-t">
                            <tr><td><b>Pilih No. PO</b></td><td>Pilih PO yang akan dibuat packing list-nya. Bisa diketik untuk mencari.</td></tr>
                            <tr><td><b>Tgl. Shipment, Buyer, Worksheet, Style, Dest</b></td><td>Tampil otomatis sesuai PO terpilih (hanya informasi).</td></tr>
                        </table>
                        <ul class="mt-2">
                            <li>Mengganti PO akan mengosongkan isian carton dan Hasil Packing List yang belum disimpan.</li>
                        </ul>
                    </section>

                    <section id="plg-carton">
                        <h6><span>4. Tentukan Carton</span> <button type="button" class="pl-g-show" data-spot="#plCartonCard"><i class="fa fa-crosshairs"></i> Tunjukkan di halaman</button></h6>
                        <table class="table table-sm pl-g-t">
                            <tr><td><b>Carton Mulai / Sampai</b></td><td>Nomor carton pertama dan terakhir yang isinya sama. Contoh 1 s/d 5 = carton 1, 2, 3, 4, 5.</td></tr>
                            <tr><td><span class="pl-g-btn">Pilih Carton</span></td><td>Menandai carton tersebut sebagai carton terpilih (tampil sebagai label di bawahnya).</td></tr>
                            <tr><td><b>Tipe Packing</b> <span class="text-danger">*</span></td><td>Wajib diisi, misal <b>SOLID</b>. Otomatis huruf besar.</td></tr>
                            <tr><td><b>Keterangan</b></td><td>Opsional.</td></tr>
                        </table>
                        <ul class="mt-2">
                            <li>Carton yang sudah ada di Hasil Packing List tidak bisa dipilih lagi; nomornya tampil di bawah form.</li>
                        </ul>
                    </section>

                    <section id="plg-prog">
                        <h6><span>5. Progress Packing</span> <button type="button" class="pl-g-show" data-spot="#plProgCard"><i class="fa fa-crosshairs"></i> Tunjukkan di halaman</button></h6>
                        <table class="table table-sm pl-g-t">
                            <tr><td><b>Total Qty PO</b></td><td>Jumlah qty seluruh SKU di PO.</td></tr>
                            <tr><td><b>Sudah Packing</b></td><td>Total qty yang sudah dimasukkan ke Hasil Packing List di halaman ini.</td></tr>
                            <tr><td><b>Balance &amp; Progress</b></td><td>Sisa qty dan persentasenya.</td></tr>
                        </table>
                    </section>

                    <section id="plg-isi">
                        <h6><span>6. Isi Packing per Barcode</span> <button type="button" class="pl-g-show" data-spot="#plIsiCard"><i class="fa fa-crosshairs"></i> Tunjukkan di halaman</button></h6>
                        <table class="table table-sm pl-g-t">
                            <tr><td><b>Tab Worksheet</b></td><td>Menyaring baris per worksheet. <b>Semua Worksheet</b> menampilkan semuanya.</td></tr>
                            <tr><td><b>PO / Pack / Bal</b></td><td>Di tiap size: qty PO, qty yang sudah di-packing (termasuk yang tersimpan sebelumnya), dan sisanya.</td></tr>
                            <tr><td><b>Kolom Qty</b></td><td>Isi qty <b>untuk 1 carton</b>. Qty ini diterapkan ke setiap carton terpilih. Kotak jadi merah bila melebihi balance.</td></tr>
                            <tr><td><b>Barcode Kosong</b></td><td>Size tanpa barcode tidak bisa diisi.</td></tr>
                            <tr><td><span class="pl-g-btn">Simpan ke Carton Terpilih</span></td><td>Memasukkan isian ke Hasil Packing List. Form carton &amp; qty dikosongkan lagi untuk carton berikutnya.</td></tr>
                        </table>
                        <ul class="mt-2">
                            <li>Qty × jumlah carton tidak boleh melebihi balance. Contoh: qty 10 untuk 5 carton = butuh balance 50.</li>
                        </ul>
                    </section>

                    <section id="plg-hasil">
                        <h6><span>7. Hasil Packing List &amp; simpan</span> <button type="button" class="pl-g-show" data-spot="#plHasilCard"><i class="fa fa-crosshairs"></i> Tunjukkan di halaman</button></h6>
                        <table class="table table-sm pl-g-t">
                            <tr><td><b>Jumlah Carton / Total Qty</b></td><td>Ringkasan isi Hasil Packing List.</td></tr>
                            <tr><td><span class="pl-g-btn">Preview</span></td><td>Melihat isi tiap carton dalam bentuk visual sebelum disimpan.</td></tr>
                            <tr><td><span class="pl-g-btn">Hapus</span> <small class="text-muted">(per baris)</small></td><td>Menghapus satu carton dari hasil, supaya nomornya bisa diisi ulang.</td></tr>
                            <tr><td><span class="pl-g-btn">Hapus Semua</span></td><td>Mengosongkan seluruh hasil (ada konfirmasi).</td></tr>
                            <tr><td><span class="pl-g-btn">Simpan Packing List</span></td><td>Menyimpan semua carton ke database (ada konfirmasi). Setelah berhasil, data PO dimuat ulang.</td></tr>
                        </table>
                    </section>

                    <section id="plg-tips">
                        <h6>8. Tips &amp; FAQ</h6>
                        <dl class="pl-g-faq mb-0">
                            <dt>Salah isi qty di satu carton?</dt>
                            <dd>Klik <b>Hapus</b> di baris carton itu pada Hasil Packing List, lalu pilih ulang nomor carton dan isi lagi.</dd>
                            <dt>Muncul "Carton … sudah ada di packing list"?</dt>
                            <dd>Nomor carton itu sudah ada di Hasil Packing List. Pakai nomor lain atau hapus dulu carton tersebut.</dd>
                            <dt>Muncul "melebihi balance"?</dt>
                            <dd>Qty × jumlah carton lebih besar dari sisa PO. Kurangi qty atau jumlah carton terpilih.</dd>
                            <dt>Halaman tertutup sebelum Simpan Packing List?</dt>
                            <dd>Data di Hasil Packing List hilang dan perlu diisi ulang. Simpan setelah semua carton selesai.</dd>
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
        $('#poSelect').select2({
            theme: 'bootstrap4',
            width: 'resolve'
        });

        $('#tipePack, #keterangan').on('input', function() {
            const start = this.selectionStart;
            const end = this.selectionEnd;
            this.value = this.value.toUpperCase();
            this.setSelectionRange(start, end);
        });

        // Detail SKU PO terpilih (dari ppic_master_so + master_sb_ws)
        let master = [];
        let poInfo = {
            worksheets: []
        };

        let selected = [];
        let packing = {};
        let filter = "ALL";
        let inputQty = {};
        let activePo = null;

        function toast(msg, type = 'info') {
            iziToast[type]({
                message: msg,
                position: 'topCenter'
            });
        }

        function fmt(n) {
            return Number(n).toLocaleString("id-ID");
        }

        function escHtml(v) {
            return $('<div>').text(v ?? '').html();
        }

        function key(x) {
            return String(x.id);
        }

        function currentMaster() {
            return master;
        }

        function totalPo() {
            return currentMaster().reduce((a, x) => a + Number(x.po), 0);
        }

        function uniq(arr) {
            return [...new Set(arr.filter(v => v !== null && v !== ''))];
        }

        function resetPoInfo() {
            $('#poShipment, #poBuyer, #poWorksheet, #poStyle, #poDest').text('-');
        }

        $('#poSelect').on('change', function() {
            selectPO($(this).val());
        });

        function selectPO(po) {
            if (!po) return;

            activePo = po;
            master = [];
            poInfo = {
                worksheets: []
            };
            filter = "ALL";
            inputQty = {};
            packing = {};
            resetPoInfo();
            renderTabs();
            renderAll();

            $.ajax({
                url: '{{ route('get-po-detail-packing-list-karton') }}',
                method: 'GET',
                data: {
                    po: po
                },
                beforeSend: function() {
                    showLoading();
                },
                success: function(rows) {
                    master = rows.map(r => ({
                        id: r.id_ppic_master_so,
                        id_so_det: r.id_so_det,
                        dest: r.dest,
                        ws: r.ws,
                        style: r.styleno,
                        color: r.color,
                        size: r.size,
                        urutan: r.urutan == null ? 9999 : +r.urutan,
                        sku: r.barcode,
                        po: parseInt(r.qty_po) || 0,
                        existing: parseInt(r.qty_packing) || 0
                    }));

                    const worksheets = uniq(rows.map(r => r.ws));
                    const styles = uniq(rows.map(r => r.styleno));
                    const buyers = uniq(rows.map(r => r.buyer));
                    poInfo = {
                        worksheets
                    };

                    $('#poShipment').text(uniq(rows.map(r => r.tgl_shipment)).join(', ') || '-');
                    $('#poBuyer').text(buyers.join(', ') || '-');
                    $('#poWorksheet').html(worksheets.map(escHtml).join('<br>') || '-');
                    $('#poStyle').html(styles.map(escHtml).join('<br>') || '-');
                    $('#poDest').html(uniq(rows.map(r => r.dest)).map(escHtml).join('<br>') || '-');

                    renderTabs();
                    renderAll();
                },
                error: function() {
                    toast('Gagal memuat data PO', 'error');
                },
                complete: function() {
                    hideLoading();
                }
            });
        }

        // carton yang sudah masuk Hasil Packing List
        function usedCartons() {
            return Object.keys(packing).map(Number).sort((a, b) => a - b);
        }

        function renderUsedCartons() {
            $('#usedCartons').html(`Carton yang sudah ada (tidak bisa diinput lagi): <b>${cartonRanges(usedCartons()) || '-'}</b>`);
        }

        // [1, 2, 3, 7] => "1–3, 7"
        function cartonRanges(list) {
            const ranges = [];
            list.forEach(c => {
                const last = ranges[ranges.length - 1];
                if (last && last[1] === c - 1) last[1] = c;
                else ranges.push([c, c]);
            });
            return ranges.map(([a, b]) => a === b ? a : `${a}–${b}`).join(', ');
        }

        // no carton & qty hanya bilangan bulat, type number masih menerima . , e - +
        function onlyInteger(e) {
            if (['.', ',', 'e', 'E', '-', '+'].includes(e.key)) e.preventDefault();
        }

        function setRange() {
            if (!$('#start').val()) return toast("Isi Carton Mulai terlebih dahulu", 'warning');
            let a = Math.max(1, +$('#start').val() || 1);
            let b = +$('#end').val() || a;
            if (!Number.isInteger(a) || !Number.isInteger(b)) return toast("No carton tidak boleh desimal", 'warning');
            if (b < a) return toast("Carton Sampai tidak boleh lebih kecil dari Carton Mulai", 'warning');
            const bentrok = usedCartons().filter(c => c >= a && c <= b);
            if (bentrok.length) return toast(`Carton ${cartonRanges(bentrok)} sudah ada di packing list`, 'warning');
            selected = [];
            for (let i = a; i <= b; i++) selected.push(i);
            renderAll();
            toast(`Carton ${a}–${b} dipilih`, 'success');
        }

        function renderAll() {
            renderUsedCartons();
            renderChips();
            renderMaster();
            renderSaved();
            updateSummary();
        }

        function renderChips() {
            $('#chips').html(selected.length ?
                selected.map(c => `<span class="t2-chip">Carton ${c}</span>`).join("") :
                '<span class="t2-muted">Belum ada carton.</span>');
        }

        function renderTabs() {
            const ws = poInfo.worksheets;
            let html = `<button type="button" class="t2-tab ${filter === 'ALL' ? 'active' : ''}" onclick="filterWs('ALL')">Semua Worksheet</button>`;
            html += ws.map(w =>
                `<button type="button" class="t2-tab ${filter === w ? 'active' : ''}" onclick="filterWs('${w}')">${escHtml(w)}</button>`
            ).join("");
            $('#wsTabs').html(html);
        }

        function filterWs(ws) {
            filter = ws;
            renderTabs();
            renderMaster();
        }

        // qty sudah packing = yang sudah tersimpan di DB + yang baru diisi di form
        function packedFor(x) {
            return (x.existing || 0) + Object.values(packing).flat()
                .filter(r => r.id === x.id)
                .reduce((a, r) => a + Number(r.qty), 0);
        }

        function renderMaster() {
            const list = currentMaster();
            const arr = filter === "ALL" ? list : list.filter(x => x.ws === filter);
            const groups = [];
            const seen = new Set();
            arr.forEach(x => {
                const g = x.ws + "|" + x.style + "|" + x.color + "|" + x.dest;
                if (!seen.has(g)) {
                    seen.add(g);
                    groups.push({
                        ws: x.ws,
                        style: x.style,
                        color: x.color,
                        dest: x.dest
                    });
                }
            });
            const sizes = [...new Set(list.map(x => x.size))];

            $('#masterHead').html(`<tr>
                <th>Worksheet</th>
                <th style="min-width:150px">Style</th>
                <th style="min-width:115px">Color</th>
                <th style="min-width:100px">Dest</th>
                ${sizes.map(s => `<th class="t2-sizeHead">${s}</th>`).join("")}
            </tr>`);

            if (!groups.length) {
                $('#masterRows').html(
                    `<tr><td colspan="${4 + sizes.length}" class="text-center t2-muted py-3">Pilih PO terlebih dahulu.</td></tr>`
                );
            } else {
                $('#masterRows').html(groups.map(g => `<tr>
                    <td class="t2-ws">${g.ws}</td>
                    <td class="t2-auto">${g.style}</td>
                    <td class="t2-auto"><span class="t2-color-badge">${g.color}</span></td>
                    <td class="t2-auto">${g.dest ?? '-'}</td>
                    ${sizes.map(size => {
                        const x = list.find(m => m.ws === g.ws && m.style === g.style && m.color === g.color && m.dest === g.dest && m.size === size);
                        if (!x) return `<td class="t2-auto text-center" style="color:#a1adbd">—</td>`;
                        const k = key(x),
                            done = packedFor(x),
                            bal = Math.max(0, x.po - done),
                            val = inputQty[k] ?? "";
                        return `<td class="t2-sizeCell">
                            <div class="t2-sizeInfo">
                                <div><span class="t2-miniLabel">PO</span> <b>${fmt(x.po)}</b></div>
                                <div><span class="t2-miniLabel">Pack</span> ${fmt(done)}</div>
                                <div style="color:#000"><span class="t2-miniLabel">Bal</span> ${fmt(bal)}</div>
                            </div>
                            ${x.sku
                                ? `<input class="t2-qtyInput ${+val > bal ? 't2-over' : ''}" type="number" min="0" step="1" max="${bal}" onkeydown="onlyInteger(event)"
                                    value="${val}" placeholder="Qty" onchange="setQty('${k}', this.value, this)">`
                                : `<input class="t2-qtyInput" type="number" placeholder="Qty" disabled>`}
                            <div class="t2-barcode">${x.sku ? `Barcode ${escHtml(x.sku)}` : 'Barcode Kosong'}</div>
                        </td>`;
                    }).join("")}
                </tr>`).join(""));
            }

            $('#cartonTotal').text(fmt(Object.values(inputQty).reduce((a, b) => a + (+b || 0), 0)));
        }

        // tidak render ulang tabel, supaya klik ke input lain tidak hilang fokusnya
        function setQty(k, v, el) {
            const x = currentMaster().find(m => key(m) === k);
            if (!x) return;
            const bal = x.po - packedFor(x);
            const n = Math.max(0, +v || 0);
            if (!Number.isInteger(n)) {
                $(el).val(inputQty[k] ?? '');
                return toast("Qty tidak boleh desimal", 'warning');
            }
            inputQty[k] = n;
            $(el).toggleClass('t2-over', n > bal);
            $('#cartonTotal').text(fmt(Object.values(inputQty).reduce((a, b) => a + (+b || 0), 0)));
            if (n > bal) toast(`Qty ${x.sku} melebihi balance PO (${fmt(bal)})`, 'warning');
        }

        function saveSelected() {
            if (!selected.length) return toast("Pilih carton terlebih dahulu", 'warning');
            const entries = Object.entries(inputQty).filter(([k, v]) => +v > 0);
            if (!entries.length) return toast("Isi minimal satu Qty Packing", 'warning');

            if (!$('#tipePack').val().trim()) {
                $('#tipePack').focus();
                return toast("Tipe Packing wajib diisi", 'warning');
            }

            const list = currentMaster();
            for (const [k, v] of entries) {
                const x = list.find(m => key(m) === k);
                const bal = x.po - packedFor(x);
                if (+v * selected.length > bal) {
                    return toast(`Qty ${x.ws} / ${x.sku}: ${fmt(v)} x ${selected.length} carton melebihi balance (${fmt(bal)})`, 'warning');
                }
            }

            const keterangan = $('#keterangan').val().trim();
            const tipe_pack = $('#tipePack').val().trim().toUpperCase();
            const carton_awal = Math.min(...selected);
            const carton_akhir = Math.max(...selected);
            const jumlahCarton = selected.length;
            selected.forEach(c => {
                packing[c] = entries.map(([k, v]) => ({
                    ...list.find(m => key(m) === k),
                    qty: +v,
                    tipe_pack,
                    keterangan,
                    carton_awal,
                    carton_akhir
                }));
            });
            // carton yang sudah masuk hasil tidak boleh terpilih lagi
            selected = [];
            inputQty = {};
            $('#start, #end, #keterangan, #tipePack').val('');
            renderAll();
            toast(`Komposisi disimpan ke ${jumlahCarton} carton`, 'success');
        }

        // urutan isi carton: ws, color, master_size_new.urutan
        function byWsColorSize(a, b) {
            return String(a.ws).localeCompare(String(b.ws)) ||
                String(a.color).localeCompare(String(b.color)) ||
                a.urutan - b.urutan;
        }

        function renderSaved() {
            const all = [];
            Object.keys(packing).sort((a, b) => a - b)
                .forEach(c => [...packing[c]].sort(byWsColorSize).forEach(r => all.push({
                    carton: c,
                    ...r
                })));

            $('#savedCartonCount').text(fmt(Object.keys(packing).length));
            $('#savedQtyTotal').text(fmt(all.reduce((a, r) => a + r.qty, 0)) + " pcs");

            if (!all.length) {
                $('#saved').html('<div class="t2-muted text-center" style="padding:25px">Belum ada carton yang disimpan.</div>');
                return;
            }

            $('#saved').html(`<div class="t2-tablewrap"><table class="t2-savedTable">
                <thead><tr><th>Carton</th><th>WS</th><th>Style</th><th>Color</th><th>Dest</th><th>Size</th><th>Barcode</th><th>Qty</th><th>Tipe Packing</th><th>Keterangan</th><th>Aksi</th></tr></thead>
                <tbody>
                ${all.map(r => `<tr>
                    <td><b>Carton ${r.carton}</b></td>
                    <td>${r.ws}</td>
                    <td>${r.style}</td>
                    <td>${r.color}</td>
                    <td>${r.dest ?? '-'}</td>
                    <td>${r.size}</td>
                    <td>${r.sku ? escHtml(r.sku) : 'Kosong'}</td>
                    <td><b>${fmt(r.qty)}</b></td>
                    <td>${r.tipe_pack ? escHtml(r.tipe_pack) : '-'}</td>
                    <td>${escHtml(r.keterangan)}</td>
                    <td><button type="button" class="t2-btn danger" style="height:29px;padding:0 9px" onclick="deleteCarton(${r.carton})">Hapus</button></td>
                </tr>`).join("")}
                </tbody></table></div>`);
        }

        function deleteCarton(c) {
            delete packing[c];
            renderAll();
            toast(`Carton ${c} dihapus`, 'success');
        }

        function updateSummary() {
            const packed = Object.values(packing).flat().reduce((a, r) => a + Number(r.qty), 0);
            const po = totalPo();
            const bal = Math.max(0, po - packed);
            const pct = po ? (packed / po) * 100 : 0;

            $('#totalPoVal').text(fmt(po) + " pcs");
            $('#packed').text(fmt(packed) + " pcs");
            $('#balance').text(fmt(bal) + " pcs");
            $('#pct').text(pct.toFixed(1) + "%");
            $('#miniBar').css('width', Math.min(100, pct) + "%");
        }

        function clearPacking() {
            if (!Object.keys(packing).length) return;
            Swal.fire({
                title: 'Hapus semua hasil packing?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#dc3545',
            }).then(result => {
                if (!result.isConfirmed) return;
                packing = {};
                renderAll();
                toast("Semua packing dihapus", 'success');
            });
        }

        function storePacking() {
            const cartons = Object.keys(packing).sort((a, b) => a - b);
            if (!activePo) return toast("Pilih PO terlebih dahulu", 'warning');
            if (!cartons.length) return toast("Belum ada data packing", 'warning');

            const rows = cartons.flatMap(c => packing[c].map(r => ({
                carton: +c,
                carton_awal: r.carton_awal,
                carton_akhir: r.carton_akhir,
                id: r.id,
                qty: r.qty,
                tipe_pack: r.tipe_pack,
                keterangan: r.keterangan || null
            })));

            Swal.fire({
                title: 'Simpan packing list?',
                html: `PO <b>${escHtml(activePo)}</b><br>${cartons.length} carton akan disimpan`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, simpan',
                cancelButtonText: 'Batal',
            }).then(result => {
                if (!result.isConfirmed) return;

                $.ajax({
                    url: '{{ route('store-packing-list-karton') }}',
                    method: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        po: activePo,
                        rows: rows
                    },
                    beforeSend: function() {
                        showLoading();
                    },
                    success: function(res) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: res.message
                        });
                        selectPO(activePo);
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: xhr.responseJSON?.message || 'Gagal menyimpan packing list'
                        });
                    },
                    complete: function() {
                        hideLoading();
                    }
                });
            });
        }

        function preview() {
            const cartons = Object.keys(packing).sort((a, b) => a - b);
            if (!cartons.length) return toast("Belum ada data packing", 'warning');

            let grandTotal = 0;
            const cards = cartons.map(c => {
                const rows = [...(packing[c] || [])].sort(byWsColorSize);
                const total = rows.reduce((a, r) => a + r.qty, 0);
                grandTotal += total;

                const items = rows.map(r => `
                    <div class="t2-itemSymbol" title="${r.style} • ${r.ws}">
                        <div class="t2-itemIcon"><i class="fas fa-tshirt"></i></div>
                        <div class="t2-itemSize">${r.size}</div>
                        <div class="t2-itemQty">${fmt(r.qty)} pcs</div>
                        <div class="t2-itemMeta">${r.sku ? escHtml(r.sku) : 'Barcode Kosong'}<br>${escHtml(r.ws)}</div>
                    </div>
                `).join("");

                const styles = [...new Set(rows.map(r => r.style))];
                return `
                    <div class="t2-cartonCard">
                        <div class="t2-cartonHead">
                            <div class="t2-cartonNo">Carton ${c}</div>
                            <div class="t2-cartonTotal">${fmt(total)} pcs</div>
                        </div>
                        <div class="t2-cartonBox">
                            <div class="t2-cartonContents">${items}</div>
                        </div>
                        <div class="t2-cartonStyle">${styles.map(s => `<div><b>${s}</b></div>`).join("")}</div>
                        ${rows[0]?.tipe_pack ? `<div class="t2-muted" style="font-size:11px">Tipe: ${escHtml(rows[0].tipe_pack)}</div>` : ''}
                        ${rows[0]?.keterangan ? `<div class="t2-muted" style="font-size:11px">Ket: ${escHtml(rows[0].keterangan)}</div>` : ''}
                    </div>
                `;
            }).join("");

            $('#previewBody').html(`
                <div class="t2-muted mb-1">PO <b>${activePo}</b></div>
                <div class="t2-previewSummary">
                    <div class="t2-summaryPill">Jumlah Carton <b>${cartons.length}</b></div>
                    <div class="t2-summaryPill">Total Isi <b>${fmt(grandTotal)} pcs</b></div>
                    <div class="t2-summaryPill">Visualisasi Isi Carton</div>
                </div>
                <div class="t2-cartonPreviewGrid">${cards}</div>
            `);
            $('#modalPreviewPacking').modal('show');
        }

        // ===== Panduan: sorot elemen asli + tur langkah demi langkah =====
        const gEl = document.getElementById('plGuide'), gBs = bootstrap.Modal.getOrCreateInstance(gEl);
        const TOUR_KEY = 'pl_tour_seen_{{ Auth::id() }}';
        const STEPS = [
            { sel: '.pl-head-r', sec: 'plg-atas', lbl: 'Tombol di kanan atas',
              d: '<b>Panduan</b> membuka halaman bantuan ini · <b>Kembali</b> ke daftar Packing List.' },
            { sel: '.t2-order', sec: 'plg-po', lbl: 'Pilih No. PO',
              d: 'Pilih PO terlebih dahulu. Info shipment, buyer, worksheet, style &amp; dest tampil otomatis.' },
            { sel: '#plCartonCard', sec: 'plg-carton', lbl: '1. Tentukan Carton',
              d: 'Isi <b>Carton Mulai–Sampai</b> lalu klik <b>Pilih Carton</b>. Isi juga <b>Tipe Packing</b> (wajib).' },
            { sel: '#plProgCard', sec: 'plg-prog', lbl: 'Progress Packing',
              d: 'Total qty PO, qty yang sudah dimasukkan ke hasil, balance, dan persentasenya.' },
            { sel: '#plIsiCard', sec: 'plg-isi', lbl: '2. Isi Packing per Barcode',
              d: 'Isi qty per size <b>untuk 1 carton</b>, lalu <b>Simpan ke Carton Terpilih</b>. Qty diterapkan ke semua carton terpilih.' },
            { sel: '#plHasilCard', sec: 'plg-hasil', lbl: '3. Hasil Packing List',
              d: 'Cek dengan <b>Preview</b>, lalu <b>Simpan Packing List</b> agar tersimpan ke database.' },
        ];
        let spotTok = 0, spotIdx = -1, tourFirst = false;

        function gGo(id, smooth) {
            const t = gEl.querySelector(id);
            if (t) t.scrollIntoView({ behavior: smooth ? 'smooth' : 'auto', block: 'start' });
        }

        function spotClear() {
            spotTok++;
            document.querySelectorAll('.pl-spot, .pl-spot-l, .pl-spot-cv').forEach(x => x.remove());
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
            gEl.addEventListener('shown.bs.modal', () => gGo(first || !s ? '#plg-peta' : '#' + s.sec), { once: true });
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
                cv.className = 'pl-spot-cv';
                cv.onclick = spotOff;

                const box = document.createElement('div');
                box.className = 'pl-spot';
                Object.assign(box.style, { left: r.left - pad + 'px', top: r.top - pad + 'px', width: r.width + pad * 2 + 'px', height: r.height + pad * 2 + 'px' });

                const l = document.createElement('div');
                l.className = 'pl-spot-l';
                l.innerHTML = `<div class="st"><span>Langkah ${i + 1} dari ${n}</span><span class="dots">${STEPS.map((_, k) => `<i class="${k === i ? 'on' : k < i ? 'done' : ''}"></i>`).join('')}</span></div>
                    <b>${escHtml(s.lbl)}</b><span class="d">${s.d}</span>
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
                    if (tok !== spotTok || !document.querySelector('.pl-spot')) return;
                    addEventListener('scroll', spotOff, true);
                    addEventListener('resize', spotOff);
                }, 250);
            }
        }

        document.getElementById('guideBtn').addEventListener('click', e => {
            e.stopPropagation();
            gBs.show();
        });

        // Klik di dalam panduan: nomor/nav → lompat ke bagian; kotak/tombol "Tunjukkan" → sorot di halaman
        gEl.addEventListener('click', e => {
            const a = e.target.closest('.pl-map-n, .pl-g-nav a');
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

        document.addEventListener('keydown', e => {
            if (spotIdx < 0) return;
            if (e.key === 'Escape') { e.preventDefault(); spotOff(); return; }
            if (!document.querySelector('.pl-spot')) return;
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

        // Init
        renderTabs();
        renderAll();
        autoTour();
    </script>
@endsection
