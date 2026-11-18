@extends('layouts.index')

@section('custom-link')
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

    <style>
        /* -Summary: drag & drop roll- */
        #scannedItemTable tbody tr {
            cursor: pointer;
        }

        #scannedItemTable tbody tr:hover > td {
            background: #f4f8fd;
        }

        #scannedItemTable tbody tr.selected > td {
            background: #e3efff;
        }

        #scannedItemTable .roll-drag-handle {
            cursor: grab;
            color: #adb5bd;
            padding: 6px 4px;
            border-radius: 4px;
            transition: color .15s, background .15s;
        }

        #scannedItemTable tr:hover .roll-drag-handle {
            color: #3c8dbc;
            background: #e9f2ff;
        }

        #scannedItemTable .roll-drag-handle:active {
            cursor: grabbing;
        }

        #scannedItemTable .roll-sort-placeholder td {
            background: #e9f2ff;
            border: 2px dashed #3c8dbc !important;
            height: 44px;
        }

        #scannedItemTable tr.roll-continuation > td:first-child {
            border-left: 3px solid #17a2b8;
        }

        #scannedItemTable .roll-link-icon {
            color: #17a2b8;
            font-size: 12px;
        }

        .summary-toolbar {
            display: flex;
            flex-wrap: wrap;
            gap: .5rem;
            justify-content: space-between;
            align-items: center;
        }

        .summary-toolbar .summary-hint {
            font-size: 12px;
            color: #6c757d;
        }

        .summary-toolbar .summary-hint i {
            color: #3c8dbc;
        }

        /* -Spreading: mode tambah roll & roll sambungan- */
        .add-roll-banner {
            display: flex;
            gap: .75rem;
            align-items: flex-start;
            justify-content: space-between;
            border-left: 4px solid #17a2b8;
            background: #eef9fb;
            border-radius: 6px;
            padding: .75rem 1rem;
            margin-bottom: 1rem;
        }

        .add-roll-banner .add-roll-title {
            font-weight: 700;
            color: #117a8b;
        }

        .add-roll-banner .add-roll-meta {
            font-size: 12px;
            color: #495057;
        }

        .ext-card {
            border: 1px solid #b8e3ea;
            border-radius: 8px;
            margin-bottom: 1rem;
            overflow: hidden;
        }

        .ext-card .ext-card-header {
            background: #eef9fb;
            border-bottom: 1px solid #b8e3ea;
            padding: .6rem 1rem;
        }

        .ext-card .ext-card-header .ext-card-title {
            font-weight: 700;
            color: #117a8b;
        }

        .ext-card .ext-card-header small {
            color: #6c757d;
        }

        .ext-card .ext-card-body {
            padding: 1rem;
        }

        .ext-step {
            display: flex;
            align-items: center;
            gap: .5rem;
            font-size: 13px;
            font-weight: 700;
            color: #495057;
            margin-bottom: .5rem;
        }

        .ext-step .ext-step-number {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background: #17a2b8;
            color: #fff;
            font-size: 12px;
        }

        .ext-step-hint {
            font-weight: 400;
            font-size: 12px;
            color: #6c757d;
        }

        .ext-summary {
            display: flex;
            flex-wrap: wrap;
            gap: .5rem;
            margin-top: .25rem;
        }

        .ext-summary .ext-summary-item {
            flex: 1 1 140px;
            background: #f8f9fa;
            border-radius: 6px;
            padding: .5rem .75rem;
        }

        .ext-summary .ext-summary-item small {
            display: block;
            color: #6c757d;
            font-size: 11px;
        }

        .ext-summary .ext-summary-item input {
            border: 0;
            background: transparent;
            padding: 0;
            font-weight: 700;
            font-size: 15px;
            width: 100%;
            box-shadow: none;
        }

        .ext-summary .ext-summary-item.is-negative input {
            color: #dc3545;
        }

        /* -Panduan- */
        :root {
            --cg-pri: var(--sb-color, #082149);
            --cg-sec: var(--sb-secondary-color, #238380);
            --cg-acc: #ff971f;
            --cg-ink: #212529;
            --cg-muted: #6c757d;
            --cg-line: #e2e6ec;
            --cg-soft: #f4f6f9;
        }
        .cg { padding: 0 20px 20px; color: var(--cg-ink); font-size: 13.5px; }
        .cg-nav { position: sticky; top: 0; z-index: 2; display: flex; flex-wrap: wrap; gap: 6px; padding: 12px 0; background: #fff; border-bottom: 1px solid var(--cg-line); margin-bottom: 4px; }
        .cg-nav a { font-size: 12px; font-weight: 600; padding: 4px 10px; border-radius: 20px; background: var(--cg-soft); color: var(--cg-pri); text-decoration: none; border: 1px solid var(--cg-line); }
        .cg-nav a:hover { background: var(--cg-pri); color: #fff; }
        .cg section { padding-top: 16px; scroll-margin-top: 56px; }
        .cg h6 { display: flex; align-items: center; justify-content: space-between; gap: 8px; flex-wrap: wrap; font-weight: 700; color: var(--cg-pri); border-left: 4px solid var(--cg-sec); padding-left: 8px; margin-bottom: 10px; }
        .cg ul { padding-left: 18px; margin-bottom: 0; }
        .cg li { margin-bottom: 6px; line-height: 1.5; }
        .cg kbd { font-size: 11px; padding: 1px 5px; }
        .cg-note { font-size: 12.5px; color: var(--cg-muted); margin-bottom: 10px; }
        .cg-show { border: 1px solid var(--cg-sec); background: #fff; color: var(--cg-sec); font-size: 11.5px; font-weight: 600; border-radius: 20px; padding: 2px 10px; }
        .cg-show:hover, .cg-show.pri { background: var(--cg-sec); color: #fff; }
        .cg-show.pri:hover { filter: brightness(.92); }
        .cg-flow { display: flex; align-items: stretch; gap: 8px; margin-bottom: 12px; flex-wrap: wrap; }
        .cg-flow > div { flex: 1; min-width: 140px; background: var(--cg-soft); border: 1px solid var(--cg-line); border-radius: .5rem; padding: 8px 12px; }
        .cg-flow b { display: block; color: var(--cg-pri); } .cg-flow span { font-size: 12px; color: var(--cg-muted); }
        .cg-flow > i { align-self: center; font-style: normal; font-weight: 700; color: var(--cg-sec); }
        .cg-leg { display: flex; flex-wrap: wrap; gap: 4px 16px; margin: 2px 0 4px; font-size: 12.5px; }
        .cg-leg span { display: inline-flex; align-items: center; gap: 6px; }
        .cg-leg i { display: inline-block; width: 26px; height: 14px; border-radius: 3px; border: 2px solid; background: #fff; }
        .cg-faq dt { color: var(--cg-pri); margin-top: 8px; } .cg-faq dd { margin: 2px 0 0; }
        @media (max-width: 576px) { .cg-flow > i { display: none; } }
        /* peta halaman */
        .cg-map { background: var(--cg-soft); border: 1px solid var(--cg-line); border-radius: .6rem; padding: 14px; max-width: 760px; display: flex; flex-direction: column; gap: 10px; }
        .cg-z { position: relative; cursor: pointer; transition: box-shadow .15s; }
        .cg-z:hover { box-shadow: 0 0 0 2px var(--cg-acc); z-index: 6; }
        .cg-n { position: absolute; top: -9px; left: -9px; z-index: 2; width: 22px; height: 22px; border-radius: 50%; background: var(--cg-acc); color: #fff !important; font-size: 12px; font-weight: 700; display: grid; place-items: center; text-decoration: none; box-shadow: 0 0 0 2px #fff; }
        .cg-n.sm { width: 18px; height: 18px; font-size: 10.5px; top: -7px; left: -7px; }
        .cg-n:hover, .cg-n:focus-visible { background: var(--cg-pri); z-index: 5; }
        .cg-tip { position: absolute; top: calc(100% + 8px); left: -4px; width: 230px; padding: 8px 10px; border-radius: .45rem; background: var(--cg-pri); color: #fff; font-size: 12px; font-weight: 400; line-height: 1.45; text-align: left; box-shadow: 0 6px 18px rgba(8,33,73,.3); opacity: 0; visibility: hidden; transform: translateY(-4px); transition: opacity .15s, transform .15s, visibility .15s; pointer-events: none; }
        .cg-tip:before { content: ""; position: absolute; top: -5px; left: 10px; width: 10px; height: 10px; background: var(--cg-pri); transform: rotate(45deg); }
        .cg-n.tip-l .cg-tip { left: auto; right: -4px; } .cg-n.tip-l .cg-tip:before { left: auto; right: 10px; }
        .cg-tip b { display: block; font-size: 12.5px; margin-bottom: 2px; color: #9fd6d3; }
        .cg-n:hover .cg-tip, .cg-n:focus-visible .cg-tip { opacity: 1; visibility: visible; transform: none; }
        .cg-m-head { display: flex; justify-content: space-between; align-items: center; gap: 10px; }
        .cg-m-head b { font-size: 12.5px; color: var(--cg-pri); }
        .cg-m-head .r { display: flex; gap: 4px; padding: 4px; border-radius: .35rem; }
        .cg-m-head .r i { width: 56px; height: 14px; border-radius: 3px; border: 1px solid var(--cg-sec); background: #fff; }
        .cg-m-head .r i.p { background: var(--cg-sec); }
        .cg-m-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 10px; }
        .cg-card { background: #fff; border: 1px solid var(--cg-line); border-radius: .45rem; padding: 0 8px 8px; }
        .cg-card .h { margin: 0 -8px 8px; padding: 4px 8px 4px 18px; font-size: 10px; font-weight: 700; letter-spacing: .6px; text-transform: uppercase; background: var(--cg-pri); color: #fff; border-radius: .45rem .45rem 0 0; }
        .cg-card.min { padding-bottom: 0; } .cg-card.min .h { margin-bottom: 0; border-radius: .45rem; }
        .cg-f { display: grid; grid-template-columns: repeat(3, 1fr); gap: 4px; margin-bottom: 6px; }
        .cg-f.c2 { grid-template-columns: repeat(2, 1fr); }
        .cg-f i { height: 12px; border-radius: 3px; border: 1.5px solid #3169e4; }
        .cg-f i.in { border-color: #9aa4b1; } .cg-f i.ca { border-color: #fa4456; }
        .cg-sv { height: 12px; border-radius: 3px; background: var(--cg-sec); }
        .cg-tbl { border: 1px solid var(--cg-line); border-radius: .3rem; overflow: hidden; }
        .cg-tbl s { display: flex; align-items: center; gap: 6px; height: 14px; padding: 0 6px; border-top: 1px solid var(--cg-line); text-decoration: none; font-size: 9px; color: #adb5bd; }
        .cg-tbl s:first-child { border-top: 0; background: #e9edf2; }
        .cg-tbl s.sel { background: #e3efff; } .cg-tbl s.ct { border-left: 3px solid #17a2b8; }
        .cg-tbl s em { flex: 1; height: 5px; border-radius: 3px; background: #e9edf2; }
        .cg-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px; font-size: 9.5px; color: var(--cg-muted); }
        .cg-bar u { text-decoration: none; font-size: 9.5px; color: #fff; background: #28a745; border-radius: .25rem; padding: 1px 6px; }
        .cg-rc { height: 18px; border-radius: .3rem; background: #343a40; color: #fff; font-size: 10px; display: grid; place-items: center; }
        .cg-ext { border: 1px dashed #17a2b8; background: #eef9fb; border-radius: .35rem; padding: 6px 8px; margin-bottom: 6px; }
        .cg-ext em { font-style: normal; font-size: 9.5px; font-weight: 700; color: #117a8b; }
        .cg-btns { display: flex; justify-content: space-between; } .cg-btns i { width: 30%; height: 12px; border-radius: 3px; background: #fa4456; } .cg-btns i + i { background: #2e8a57; }
        @media (max-width: 576px) { .cg-m-grid { grid-template-columns: 1fr; } }
        /* sorotan & tur */
        .cg-spot { position: fixed; z-index: 2000; border-radius: .6rem; box-shadow: 0 0 0 9999px rgba(8,33,73,.6); outline: 3px solid #ff971f; outline-offset: 3px; pointer-events: none; animation: cgSpot 1.2s ease-in-out infinite; }
        @keyframes cgSpot { 50% { outline-offset: 8px; outline-color: rgba(255,151,31,.6) } }
        .cg-spot-cv { position: fixed; inset: 0; z-index: 1999; cursor: pointer; }
        .cg-spot-l { position: fixed; z-index: 2001; background: #fff; color: #082149; font-size: 13px; padding: 10px 12px; border-radius: .5rem; box-shadow: 0 6px 18px rgba(0,0,0,.25); width: 340px; max-width: calc(100vw - 16px); }
        .cg-spot-l .st { display: flex; justify-content: space-between; align-items: center; font-size: 10.5px; font-weight: 700; letter-spacing: .6px; text-transform: uppercase; color: #238380; margin-bottom: 4px; }
        .cg-spot-l .dots { display: flex; gap: 4px; }
        .cg-spot-l .dots i { width: 7px; height: 7px; border-radius: 50%; background: #e2e6ec; }
        .cg-spot-l .dots i.done { background: #9fd6d3; }
        .cg-spot-l .dots i.on { background: #ff971f; width: 16px; border-radius: 4px; }
        .cg-spot-l > b { display: block; font-weight: 700; }
        .cg-spot-l > span.d { display: block; color: #3d4450; font-size: 12.5px; line-height: 1.5; margin-top: 3px; }
        .cg-spot-l > span.d b { color: #082149; }
        .cg-spot-l .nav { display: flex; justify-content: space-between; gap: 8px; margin-top: 10px; }
        .cg-spot-l .nav button { border: 1px solid #c5ccd6; background: #fff; color: #082149; font-size: 12.5px; font-weight: 600; border-radius: .35rem; padding: 4px 12px; }
        .cg-spot-l .nav button:disabled { opacity: .4; }
        .cg-spot-l .nav button.pri { background: #238380; border-color: #238380; color: #fff; }
        .cg-spot-l small { display: block; color: #6c757d; font-size: 11.5px; margin-top: 6px; padding-top: 5px; border-top: 1px solid #e2e6ec; }
        .cg-spot-l kbd { font-size: 10px; padding: 0 4px; }
        @media (prefers-reduced-motion: reduce) { .cg-spot { animation: none; } }
    </style>
@endsection

@section('content')
    <div class="row g-3 mb-3">
        <div class="d-flex gap-3 justify-content-between align-items-center">
            <h5 class="text-sb fw-bold mb-1">Form Cut / {{ $formCutInputData->no_form . " / ". strtoupper($formCutInputData->name) }}</h5>
            <div class="d-flex gap-1" id="cf-head-r">
                <button type="button" class="btn btn-outline-primary btn-sm" id="cf-guide-btn"><i class="fa fa-book-open"></i> Panduan</button>
                <a href="{{ route('manage-cutting') }}" class="btn btn-sb-secondary btn-sm"><i class="fa fa-reply"></i> Kembali ke Completed Form</a>
            </div>
        </div>
        <div class="visually-hidden">
            <input type="checkbox" name="bypass" id="bypass" value="bypass">
            <label for="bypass">Bypass Stocker</label>
        </div>

        {{-- Header Data PROCESS 1 --}}
        <div class="col-md-8">
            <div class="card card-sb h-100" id="header-data-card">
                <div class="card-header">
                    <h3 class="card-title">Header Data</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                    </div>
                </div>
                <div class="card-body" style="display: block;">
                    @php
                        $thisActCosting = $actCostingData->where('id', $formCutInputData->act_costing_id)->first();
                        $thisMarkerDetails = $markerDetailData->where('kode_marker', $formCutInputData->id_marker)->where("ratio", ">", 0);
                    @endphp
                    <div class="row align-items-end">
                        <input type="hidden" name="id" id="id" value="{{ $id }}" readonly>
                        <input type="hidden" name="act_costing_id" id="act_costing_id" value="{{ $formCutInputData->act_costing_id }}" readonly>
                        <input type="hidden" name="status" id="status" value="{{ $formCutInputData->status }}" readonly>
                        <div class="col-12 col-md-12">
                            <div class="mb-3">
                            <label class="form-label small">Meja</label>
                                <select class="form-control select2bs4" id="no_meja" name="no_meja" style="width: 100%;">
                                    <option value="">Pilih Meja</option>
                                        @foreach ($meja as $m)
                                            <option value="{{ $m->id }}" {{ isset($formCutInputData) ? ($formCutInputData->no_meja ? ($formCutInputData->no_meja == $m->id ? "selected" : "") : "") : "" }}>{{ strtoupper($m->name) }}</option>
                                        @endforeach
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="mb-3">
                                <label class="form-label small">Start</label>
                                <input type="datetime" class="form-control form-control-sm" name="start" id="start-time" value="{{ ($formCutInputData->waktu_mulai ? $formCutInputData->waktu_mulai : $formCutInputData->waktu_selesai) }}">
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="mb-3">
                                <label class="form-label small">Finish</label>
                                <input type="datetime" class="form-control form-control-sm" name="finish" id="finish-time" value="{{ $formCutInputData->waktu_selesai ? $formCutInputData->waktu_selesai : $formCutInputData->waktu_mulai }}">
                            </div>
                        </div>
                        <div class="col-6 col-md-4 d-none">
                            <div class="mb-3">
                                <label class="form-label small">Shell</label>
                                <input type="text" class="form-control form-control-sm" name="shell" id="shell" value="{{ $formCutInputData->shell ? strtoupper($formCutInputData->shell) : "-" }}" readonly>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="mb-3">
                                <label class="form-label small label-fetch">No. WS</label>
                                <input type="text" class="form-control form-control-sm border-fetch" name="no_ws" id="no_ws" value="{{ $formCutInputData->act_costing_ws }}" readonly>
                            </div>
                        </div>
                        <div class="col-4 col-md-4">
                            <div class="mb-3">
                                <label class="form-label label-fetch">No. Form</label>
                                <input type="text" class="form-control form-control-lg border-fetch" name="no_form" id="no_form" value="{{ $formCutInputData->no_form }}" readonly>
                            </div>
                        </div>
                        <div class="col-4 col-md-4">
                            <div class="mb-3">
                                <label class="form-label label-fetch">Panel</label>
                                <input type="text" class="form-control form-control-lg border-fetch" name="panel" id="panel" value="{{ $formCutInputData->panel }}" readonly>
                            </div>
                        </div>
                        <div class="col-4 col-md-4">
                            <div class="mb-3">
                                <label class="form-label label-fetch">Color</label>
                                <input type="text" class="form-control form-control-lg border-fetch" name="color" id="color" value="{{ $formCutInputData->color }}" readonly>
                            </div>
                        </div>
                        <div class="col-6 col-md-6 d-none">
                            <div class="mb-3">
                                <label class="form-label small">Tanggal</label>
                                <input type="date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" readonly>
                            </div>
                        </div>
                        <div class="col-4 col-md-4">
                            <div class="mb-3">
                                <label class="form-label small label-fetch">Buyer</label>
                                <input type="text" class="form-control form-control-sm border-fetch" name="buyer" value="{{ $thisActCosting ? $thisActCosting->buyer : '-' }}" readonly>
                            </div>
                        </div>
                        <div class="col-4 col-md-4">
                            <div class="mb-3">
                                <label class="form-label small label-fetch">Style</label>
                                <input type="text" class="form-control form-control-sm border-fetch" name="style" value="{{ $thisActCosting ? $thisActCosting->style : '-' }}" readonly>
                            </div>
                        </div>
                        <div class="col-4 col-md-4">
                            <div class="mb-3">
                                <label class="form-label small label-fetch">PO</label>
                                <input type="text" class="form-control form-control-sm border-fetch" name="po" value="{{ $formCutInputData->po_marker }}" readonly>
                            </div>
                        </div>
                        <div class="col-4 col-md-4">
                            <div class="mb-3">
                                <label class="form-label small label-fetch">Tipe Marker</label>
                                <input type="text" class="form-control form-control-sm border-fetch" name="tipe_marker" id="tipe_marker" value="{{ $formCutInputData->tipe_marker ? strtoupper($formCutInputData->tipe_marker) : '-' }}" readonly>
                            </div>
                        </div>
                        <div class="col-4 col-md-4">
                            <div class="mb-3">
                                <label class="form-label small label-fetch">Kode Marker</label>
                                <input type="text" class="form-control form-control-sm border-fetch" value="{{ $formCutInputData->id_marker }}" readonly>
                            </div>
                        </div>
                        <div class="col-4 col-md-4">
                            <div class="mb-3">
                                <label class="form-label small label-fetch">Urutan Marker</label>
                                <input type="text" class="form-control form-control-sm border-fetch" value="{{ $formCutInputData->urutan_marker." / ".$formCutInputData->nomor }}" readonly>
                            </div>
                        </div>
                        <div class="col-4 col-md-4">
                            <div class="mb-3">
                                <label class="form-label small label-fetch">QTY Gelar Marker</label>
                                <div class="input-group input-group-sm">
                                    <input type="text" class="form-control form-control-sm border-fetch" name="gelar_qty" value="{{ $formCutInputData->gelar_qty }}" readonly>
                                    <span class="input-group-text border-fetch">Ply</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-4 col-md-4">
                            <div class="mb-3">
                                <label class="form-label small label-fetch">QTY Cut Ply</label>
                                <div class="input-group input-group-sm">
                                    <input type="text" class="form-control form-control-sm border-fetch" id="qty_ply" name="qty_ply" value="{{ $formCutInputData->qty_ply }}">
                                    <span class="input-group-text border-fetch">Ply</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-4 col-md-4">
                            <div class="mb-3">
                                <label class="form-label small label-fetch">Color Fabric</label>
                                <input type="text" class="form-control form-control-sm border-fetch" name="color_fabric" id="color_fabric" value="{{ $formCutInputData->color }}" readonly>
                            </div>
                        </div>
                    </div>
                    <table id="ratio-datatable" class="table table-striped table-bordered table w-100 text-center mt-3">
                        <thead>
                            <tr>
                                <th class="label-fetch">Size</th>
                                <th class="label-fetch">Ratio</th>
                                <th class="label-fetch">Qty Cut Marker</th>
                                <th class="label-fetch">Qty Output</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $totalRatio = 0;
                                $totalCutQty = 0;
                                $totalCutQtyPly = 0;
                            @endphp
                            @foreach ($thisMarkerDetails as $item)
                                <tr>
                                    @php
                                        $totalRatio += $item->ratio;
                                        $totalCutQty += $item->cut_qty;
                                        $qtyPly = $item->ratio*$formCutInputData->qty_ply;
                                        $totalCutQtyPly += $qtyPly;
                                    @endphp
                                    <td>{{ $item->size }}</td>
                                    <td>{{ $item->ratio }}</td>
                                    <td>{{ $item->cut_qty }}</td>
                                    <td>{{ $qtyPly }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th class="text-center">Total</th>
                                <th id="totalRatio">{{ $totalRatio }}</th>
                                <th id="totalQtyCutMarker">{{ $totalCutQty }}</th>
                                <th id="totalQtyCutPly">{{ $totalCutQtyPly }}</th>
                            </tr>
                        </tfoot>
                    </table>
                    <input type="hidden" name="total_ratio" id="total_ratio" value="{{ $totalRatio }}">
                    <input type="hidden" name="total_qty_cut" id="total_qty_cut" value="{{ $totalCutQty }}">
                    <input type="hidden" name="total_qty_cut_ply" id="total_qty_cut_ply" value="{{ $totalCutQtyPly }}">
                    <div class="my-3">
                        <button class="btn btn-sb-secondary btn-block fw-bold btn-sm" onclick="updateHeaderData()"><i class="fa fa-save"></i> SIMPAN</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Detail Data PROCESS 2 --}}
        <div class="col-md-4">
            <div class="card card-sb h-100" id="detail-data-card">
                <div class="card-header">
                    <h3 class="card-title">Detail Data</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                    </div>
                </div>
                <div class="card-body" style="display: block;">
                    <div class="row">
                        <div class="col-6 col-md-6">
                            <div class="mb-3">
                                <label class="form-label small label-fetch">Panjang Marker</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" class="form-control form-control-sm border-fetch" value="{{ $formCutInputData->panjang_marker }}" readonly>
                                    <input type="text" class="form-control form-control-sm border-fetch" value="{{ strtoupper($formCutInputData->unit_panjang_marker) }}" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-6">
                            <div class="mb-3">
                                <label class="form-label small label-input">Panjang Actual</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" class="form-control form-control-sm border-input" name="p_act" id="p_act" value="{{ $formCutInputData->p_act }}"
                                        onkeyup="
                                            calculateSambungan();
                                            calculateShortRoll();
                                            calculatePemakaianLembar();
                                            calculateTotalPemakaian();
                                            calculateEstAmpar();
                                            // calculateSisaKain();
                                        "

                                        onchange="
                                            calculateSambungan();
                                            calculateShortRoll();
                                            calculatePemakaianLembar();
                                            calculateTotalPemakaian();
                                            calculateEstAmpar();
                                            // calculateSisaKain();
                                        "
                                    >
                                    <input type="text" class="form-control form-control-sm border-input" name="unit_p_act" id="unit_p_act" value="{{ strtoupper($formCutInputData->unit_panjang_marker) }}" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-6">
                            <div class="mb-3">
                                <label class="form-label small label-fetch">Comma Marker</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" class="form-control form-control-sm border-fetch" value="{{ $formCutInputData->comma_marker }}" readonly>
                                    <input type="text" class="form-control form-control-sm border-fetch" value="{{ strtoupper($formCutInputData->unit_comma_marker) }}" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-6">
                            <div class="mb-3">
                                <label class="form-label small label-input">Comma Actual</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" class="form-control form-control-sm border-input" name="comma_act" id="comma_act" value="{{ $formCutInputData->comma_p_act }}"
                                        onkeyup="
                                            calculateSambungan();
                                            calculateShortRoll();
                                            calculatePemakaianLembar();
                                            calculateTotalPemakaian();
                                            calculateEstAmpar();
                                            // calculateSisaKain();
                                        "

                                        onchange="
                                            calculateSambungan();
                                            calculateShortRoll();
                                            calculatePemakaianLembar();
                                            calculateTotalPemakaian();
                                            calculateEstAmpar();
                                            // calculateSisaKain();
                                        "
                                    >
                                    <input type="text" class="form-control form-control-sm border-input" name="unit_comma_act" id="unit_comma_act" value="{{ strtoupper($formCutInputData->unit_comma_marker) }}" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-6">
                            <div class="mb-3">
                                <label class="form-label small label-fetch">Lebar Marker</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" class="form-control form-control-sm border-fetch" value="{{ strtoupper($formCutInputData->lebar_marker) }}" readonly>
                                    <input type="text" class="form-control form-control-sm border-fetch" value="{{ strtoupper($formCutInputData->unit_lebar_marker) }}" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-6">
                            <div class="mb-3">
                                <label class="form-label small label-input">Lebar Actual</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" class="form-control form-control-sm border-input" name="l_act" id="l_act" value="{{ $formCutInputData->l_act }}">
                                    <input type="text" class="form-control form-control-sm border-input" name="unit_l_act" id="unit_l_act" value="{{ strtoupper($formCutInputData->unit_lebar_marker) }}" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-6">
                            <div class="mb-3">
                                <label class="form-label small label-fetch">Lebar WS</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" class="form-control form-control-sm border-fetch" name="lebar_ws" id="lebar_ws" value="{{ $formCutInputData->lebar_ws }}" readonly>
                                    <input type="text" class="form-control form-control-sm border-fetch" name="unit_lebar_ws" id="unit_lebar_ws" value="{{ strtoupper($formCutInputData->unit_lebar_ws) }}" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-6">
                            <div class="mb-3">
                                <label class="form-label small label-calc">Cons Ampar</label>
                                <div class="input-group">
                                    <input type="number" class="form-control form-control-sm border-calc" name="cons_act" id="cons_act" value="{{ round($formCutInputData->cons_ampar, 2) > 0 ? $formCutInputData->cons_ampar : 0 }}" step=".01" readonly>
                                    <input type="text" class="form-control form-control-sm border-calc" name="unit_cons_ampar" id="unit_cons_ampar" value="{{ $formCutInputData->unit_cons_marker }}" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-6">
                            <div class="mb-3">
                                <label class="form-label small label-fetch">Cons WS</label>
                                <div class="input-group">
                                    <input type="text" class="form-control form-control-sm border-fetch" name="cons_ws" id="cons_ws" readonly>
                                    <input type="text" class="form-control form-control-sm border-fetch" name="unit_cons_ws" id="unit_cons_ws" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-6">
                            <div class="mb-3">
                                <label class="form-label small label-fetch">Cons Marker</label>
                                <div class="input-group">
                                    <input type="text" class="form-control form-control-sm border-fetch" name="cons_marker" id="cons_marker" value="{{ $formCutInputData->cons_marker }}" readonly>
                                    <input type="text" class="form-control form-control-sm border-fetch" name="unit_cons_marker" id="unit_cons_marker" value="{{ $formCutInputData->unit_cons_marker }}" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-6">
                            <div class="mb-3">
                                <label class="form-label small label-fetch">Cons Piping</label>
                                <div class="input-group">
                                    <input type="number" class="form-control form-control-sm border-fetch" step=".01" name="cons_pipping" id="cons_pipping" value="{{ $formCutInputData->cons_piping ? $formCutInputData->cons_piping : 0 }}" readonly>
                                    <input type="text" class="form-control form-control-sm border-fetch" name="unit_cons_pipping" id="unit_cons_pipping" value="{{ $formCutInputData->unit_cons_piping }}" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-6">
                            <div class="mb-3">
                                <label class="form-label small label-fetch">Gramasi</label>
                                <div class="input-group input-group-sm">
                                    <input type="text" class="form-control form-control-sm border-fetch" name="gramasi" id="gramasi" value="{{ $formCutInputData->gramasi }}" readonly>
                                    <span class="input-group-text border-fetch">gr/cm²</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="form-group">
                                <div id="marker_items">
                                    <label class="form-label small label-fetch">ID Item yang harus digunakan</label>
                                    <div class="marker_item mb-3" id="marker_items_0">
                                        <div class="input-group">
                                            <input type='text' class='form-control form-control-sm border-fetch w-25' id='txt_id_item_0' name='txt_id_item[0]' readonly>
                                            <input type='text' class='form-control form-control-sm border-fetch w-75' id='txt_detail_item_0' name='txt_detail_item[0]' readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-6 d-none">
                            <div class="mb-3">
                                <label class="form-label small label-calc">Cons 1 Ampar</label>
                                <div class="row">
                                    <div class="col-8">
                                        <input type="number" class="form-control form-control-sm border-calc" step=".01" name="cons_ampar" id="cons_ampar" value="{{ $formCutInputData->cons_ampar }}" readonly>
                                    </div>
                                    <div class="col-4">
                                        <input type="text" class="form-control form-control-sm border-calc" name="unit_cons_ampar" id="unit_cons_ampar" value="KGM" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label small label-calc">Est. Kebutuhan Kain Piping</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" class="form-control form-control-sm border-calc" step=".01" name="est_pipping" id="est_pipping" value="{{ $formCutInputData->est_pipping ? $formCutInputData->est_pipping : "0.00" }}" readonly>
                                    <input type="text" class="form-control form-control-sm border-calc" name="est_pipping_unit" id="est_pipping_unit" value="{{ strtoupper($formCutInputData->unit_panjang_marker) }}" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label small label-calc">Est. Kebutuhan Kain</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" class="form-control form-control-sm border-calc" step=".01" name="est_kain" id="est_kain" value="{{ $formCutInputData->cons_marker * $totalCutQtyPly }}" readonly>
                                    <input type="text" class="form-control form-control-sm border-calc" name="est_kain_unit" id="est_kain_unit" value="{{ strtoupper($formCutInputData->unit_panjang_marker) }}" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <button class="btn btn-sb-secondary btn-block fw-bold btn-sm" onclick="updateDetailData()"><i class="fa fa-save"></i> SIMPAN</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Loss Time --}}
        <div class="col-md-12">
            <div class="card card-sb collapsed-card h-100" id="lost-time-card">
                <div class="card-header">
                    <h3 class="card-title">Loss Time</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                    </div>
                </div>
                <div class="card-body" style="display: block;">
                    <div class="w-100 h-100 table-responsive mt-3">
                        <form action="#" method="post" id="lost-time-form">
                            <input type="hidden" id="current_lost_time" name="current_lost_time">
                            <table class="table table-bordered table" id="lostTimeTable">
                                <thead>
                                    <tr>
                                        <th>No.</th>
                                        <th>Waktu</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if ($lostTimeData->count() < 1)
                                        <tr>
                                            <th colspan="2" class="text-center">Data tidak ada</th>
                                        </tr>
                                    @else
                                        @foreach ($lostTimeData as $lost)
                                            <tr>
                                                <td>{{ $lost->lost_time_ke }}</td>
                                                <td>{{ $lost->waktu }}</td>
                                            </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <button class="btn btn-dark btn-block" id="recalculate-btn" onclick="recalculateForm()">Recalculate Form</button>
        </div>

        {{-- Spreading Summary List PROCESS 3 --}}
        <div class="col-md-12">
            <div class="card card-sb" id="summary-card">
                <div class="card-header">
                    <h3 class="card-title">Summary</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse"><i
                                class="fas fa-minus"></i></button>
                    </div>
                </div>
                <div class="card-body" style="display: block;">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="summary-toolbar">
                                <span class="summary-hint">
                                    <i class="fa-solid fa-grip-vertical"></i> Geser untuk mengatur urutan roll
                                    &nbsp;&middot;&nbsp;
                                    <i class="fa-solid fa-link"></i> bagian dari sambungan
                                    &nbsp;&middot;&nbsp;
                                    Klik baris untuk edit
                                </span>
                                <button type="button" class="btn btn-success btn-sm" onclick="startAddRoll()"><i class="fa fa-plus"></i> Tambah Roll</button>
                            </div>
                            <div class="w-100 table-responsive my-3">
                                <table class="table table-bordered table" id="scannedItemTable">
                                    <thead>
                                        <tr>
                                            <th title="Drag untuk atur urutan roll"><i class="fa-solid fa-up-down"></i></th>
                                            <th>No.</th>
                                            <th>Group</th>
                                            <th>Group Number</th>
                                            <th class="label-scan">ID Roll</th>
                                            <th class="label-scan">ID Item</th>
                                            <th class="label-scan">Lot</th>
                                            <th class="label-scan">Roll Buyer</th>
                                            <th class="label-scan">Qty Awal</th>
                                            <th class="label-scan">Qty</th>
                                            <th class="label-scan">Unit</th>
                                            <th>Sisa Gelaran</th>
                                            <th>Sambungan</th>
                                            <th>Sambungan Roll</th>
                                            <th class="label-calc">Estimasi Amparan</th>
                                            <th>Lembar Gelaran</th>
                                            <th>Average Time</th>
                                            <th>Kepala Kain</th>
                                            <th>Sisa Tidak Bisa</th>
                                            <th>Reject</th>
                                            <th>Piping</th>
                                            <th>Sisa Kain</th>
                                            <th class="label-calc">Pemakaian Lembar</th>
                                            <th class="label-calc">Total Pemakaian Per Roll</th>
                                            <th class="label-calc">Short Roll +/-</th>
                                            <th class="label-calc">Short Roll (%)</th>
                                            <th id="th-berat-amparan">Berat 1 Amparan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="9" class="text-center">Total</th>
                                            <th id="total-qty"></th>
                                            <th id="total-unit"></th>
                                            <th id="total-sisa-gelaran"></th>
                                            <th id="total-sambungan"></th>
                                            <th id="total-sambungan-roll"></th>
                                            <th id="total-est-amparan"></th>
                                            <th id="total-lembar"></th>
                                            <th id="total-average-time"></th>
                                            <th id="total-kepala-kain"></th>
                                            <th id="total-sisa-tidak-bisa"></th>
                                            <th id="total-reject"></th>
                                            <th id="total-piping"></th>
                                            <th id="total-sisa-kain"></th>
                                            <th id="total-pemakaian-lembar"></th>
                                            <th id="total-total-pemakaian"></th>
                                            <th id="total-short-roll"></th>
                                            <th id="total-short-roll-percentage"></th>
                                            <th id="total-berat-amparan"></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="row align-items-end">
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label small label-calc">Cons. Actual</label>
                                        <input type="text" class="form-control form-control-sm border-calc" name="cons_actual_gelaran" id="cons_actual_gelaran" readonly>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label small label-calc">Unit</label>
                                        <select class="form-select form-select-sm border-calc"
                                            name="unit_cons_actual_gelaran" id="unit_cons_actual_gelaran" disabled>
                                            <option value="meter">METER</option>
                                            <option value="yard">YARD</option>
                                            <option value="kgm">KGM</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label small label-calc">Kenaikan Cons. WS</label>
                                        <div class="input-group input-group-sm mb-3">
                                            <input type="text" class="form-control border-calc" name="cons_ws_uprate" id="cons_ws_uprate" readonly>
                                            <span class="input-group-text border-calc">%</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label small label-calc">Kenaikan Cons. Marker</label>
                                        <div class="input-group input-group-sm mb-3">
                                            <input type="text" class="form-control border-calc" name="cons_marker_uprate" id="cons_marker_uprate" readonly>
                                            <span class="input-group-text border-calc">%</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 d-none">
                                    <div class="mb-3">
                                        <label class="form-label small label-calc">Cons. Actual Tanpa Short Roll</label>
                                        <input type="text" class="form-control form-control-sm border-calc" name="cons_actual_gelaran_short_rolless" id="cons_actual_gelaran_short_rolless" readonly>
                                    </div>
                                </div>
                                <div class="col-md-3 d-none">
                                    <div class="mb-3">
                                        <label class="form-label small label-calc">Unit</label>
                                        <select class="form-select form-select-sm border-calc"
                                            name="unit_cons_actual_gelaran_short_rolless" id="unit_cons_actual_gelaran_short_rolless" disabled>
                                            <option value="meter">METER</option>
                                            <option value="yard">YARD</option>
                                            <option value="kgm">KGM</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3 d-none">
                                    <div class="mb-3">
                                        <label class="form-label small label-calc">Kenaikan Cons. WS</label>
                                        <div class="input-group input-group-sm mb-3">
                                            <input type="text" class="form-control border-calc" name="cons_ws_uprate_nosr" id="cons_ws_uprate_nosr" readonly>
                                            <span class="input-group-text border-calc">%</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 d-none">
                                    <div class="mb-3">
                                        <label class="form-label small label-calc">Kenaikan Cons. Marker</label>
                                        <div class="input-group input-group-sm mb-3">
                                            <input type="text" class="form-control border-calc" name="cons_marker_uprate_nosr" id="cons_marker_uprate_nosr" readonly>
                                            <span class="input-group-text border-calc">%</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label class="form-label small label-input">Operator</label>
                                        <input type="text" class="form-control form-control-sm border-input"
                                            name="operator" id="operator" value="{{ $formCutInputData->operator }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Edit Selected Spreading Form --}}
        <div class="col-md-12">
            <div class="card card-sb" id="spreading-form-card">
                <div class="card-header">
                    <h3 class="card-title">Spreading</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                    </div>
                </div>
                <div class="card-body" style="display: block;">
                    <form action="#" method="post" id="spreading-form">
                        <div class="row">
                            <input type="hidden" class="form-control" id="current_id" name="current_id">
                            <input type="hidden" id="current_created_at_ori">
                            <input type="hidden" id="current_has_extension">
                            <input type="hidden" id="current_ext_child_roll">
                            <input type="hidden" id="current_ext_parent_roll">
                            <input type="hidden" id="add_roll_insert_after">
                            <input type="hidden" id="add_roll_insert_at">
                            <div class="col-12 d-none" id="add-roll-banner">
                                <div class="add-roll-banner">
                                    <div>
                                        <div class="add-roll-title"><i class="fa fa-plus-circle"></i> Mode Tambah Roll</div>
                                        <div>Roll baru disisipkan <b id="add-roll-position-text"></b>.</div>
                                        <div class="add-roll-meta">
                                            <i class="fa-regular fa-clock"></i> Waktu gelar: <span id="add-roll-time-text"></span>
                                            &nbsp;&middot;&nbsp; Scan ID Roll lalu klik <b>Get</b>. Isi <b>Sisa Gelaran</b> jika roll ini disambung.
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-secondary text-nowrap" onclick="cancelAddRoll()"><i class="fa fa-times"></i> Batal</button>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="mb-3">
                                    <label class="form-label small" id="current_id_roll_label">Id Roll</label>
                                    <div class="input-group input-group-sm">
                                        <input type="hidden" class="form-control" id="current_id_roll_ori" name="current_id_roll_ori">
                                        <input type="text" class="form-control" id="current_id_roll" name="current_id_roll" onchange="fetchScan()">
                                        <button class="btn btn-success text-light" type="button" id="get_scanned_item" onclick="fetchScan()">Get</button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="mb-3">
                                    <label class="form-label small" id="current_id_item_label">Id Item</label>
                                    <input type="text" class="form-control form-control-sm" id="current_id_item" name="current_id_item">
                                </div>
                            </div>
                            <div class="col-3 d-none">
                                <div class="mb-3">
                                    <label class="form-label small" id="current_detail_item_label">Detail Item</label>
                                    <input type="hidden" class="form-control form-control-sm" id="current_detail_item" name="current_detail_item" readonly>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="mb-3">
                                    <label class="form-label small" id="current_lot_label">Lot</label>
                                    <input type="text" class="form-control form-control-sm" id="current_lot" name="current_lot">
                                </div>
                            </div>
                            <div class="col-3 d-none">
                                <div class="mb-3">
                                    <label class="form-label small" id="current_roll_label">Roll</label>
                                    <input type="text" class="form-control form-control-sm" id="current_roll" name="current_roll">
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="mb-3">
                                    <label class="form-label small" id="current_roll_buyer_label">Roll Buyer</label>
                                    <input type="text" class="form-control form-control-sm" id="current_roll_buyer" name="current_roll_buyer">
                                </div>
                            </div>
                            <div class="col-6">
                                @php
                                    $disableQty = true;
                                @endphp
                                @role("superadmin")
                                    @php
                                        $disableQty = false;
                                    @endphp
                                @endrole
                                <label class="form-label small" id="current_qty_real_label" {{ $disableQty ? 'readonly' : '' }}>Qty</label>
                                <input type="hidden" id="current_qty_ori" name="current_qty_ori">
                                <input type="hidden" id="current_unit_ori" name="current_unit_ori">
                                <div class="d-flex mb-3">
                                    <div style="width: 60%;">
                                        <input type="number" class="form-control form-control-sm" style="border-radius: 3px 0 0 3px" id="current_qty_real" name="current_qty_real" onchange="setRollQtyConversion(this.value); calculateEstAmpar(); calculateShortRoll()" onkeyup="setRollQtyConversion(this.value); calculateEstAmpar(); calculateShortRoll()">
                                    </div>
                                    <div style="width: 40%;">
                                        <input type="text" class="form-control form-control-sm" style="border-radius: 0 3px 3px 0" id="current_unit" name="current_unit" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="form-label small label-calc" id="current_qty_label">Qty Konversi</label>
                                    <div class="d-flex mb-3">
                                        <div style="width: 60%">
                                            <input type="number" class="form-control form-control-sm border-calc" style="border-radius: 3px 0 0 3px" id="current_qty" name="current_qty" readonly>
                                        </div>
                                        <div style="width: 40%">
                                            <input type="text" class="form-control form-control-sm border-calc" style="border-radius: 0 3px 3px 0" id="current_unit_convert" name="current_unit_convert" value="METER" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6" id="sisa-gelaran-col">
                                <div class="mb-3">
                                    <label class="form-label small label-input">Sisa Gelaran</label>
                                    <div class="d-flex mb-3">
                                        <div style="width: 60%;">
                                            <input type="number" class="form-control form-control-sm border-input" style="border-radius: 3px 0 0 3px" id="current_sisa_gelaran" name="current_sisa_gelaran" step=".01" readonly
                                                onkeyup="
                                                    // restrictRemainPly();
                                                    calculatePemakaianLembar();
                                                    calculateTotalPemakaian();
                                                    calculateShortRoll();
                                                "
                                                onchange="
                                                    // restrictRemainPly();
                                                    calculatePemakaianLembar();
                                                    calculateTotalPemakaian();
                                                    calculateShortRoll();
                                                ">
                                        </div>
                                        <div style="width: 40%;">
                                            <input type="text" class="form-control form-control-sm border-input" style="border-radius: 0 3px 3px 0" id="current_sisa_gelaran_unit" name="current_sisa_gelaran_unit" step=".01" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="form-label small label-input">Sambungan</label>
                                    <div class="d-flex">
                                        <div style="width: 60%">
                                            <input type="number" class="form-control form-control-sm border-input" style="border-radius: 3px 0 0 3px" id="current_sambungan" name="current_sambungan" step=".01" readonly
                                                onkeyup="
                                                    calculatePemakaianLembar();
                                                    calculateTotalPemakaian();
                                                    calculateShortRoll();
                                                    // calculateSisaKain();
                                                "
                                                onchange="
                                                    calculatePemakaianLembar();
                                                    calculateTotalPemakaian();
                                                    calculateShortRoll();
                                                    // calculateSisaKain();
                                                ">
                                        </div>
                                        <div style="width: 40%">
                                            <input type="text" class="form-control form-control-sm border-input" style="border-radius: 0 3px 3px 0" id="current_sambungan_unit" name="current_sambungan_unit" step=".01" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="row">
                                    <div class="col d-none" id="berat_amparan">
                                        <div class="mb-3">
                                            <label class="form-label small label-input">Berat Amparan</label>
                                            <div class="input-group input-group-sm mb-3">
                                                <input type="number" class="form-control form-control-sm " id="current_berat_amparan" name="current_berat_amparan" step=".01"
                                                    onkeyup="
                                                        calculateSambungan();
                                                        calculateShortRoll();
                                                        calculatePemakaianLembar();
                                                        calculateTotalPemakaian();
                                                        calculateEstAmpar();
                                                        // calculateSisaKain();
                                                    "

                                                    onchange="
                                                        calculateSambungan();
                                                        calculateShortRoll();
                                                        calculatePemakaianLembar();
                                                        calculateTotalPemakaian();
                                                        calculateEstAmpar();
                                                        // calculateSisaKain();
                                                    "
                                                >
                                                <span class="input-group-text">KG</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="mb-3">
                                            <label class="form-label small label-input">Group</label>
                                            <input type="text" class="form-control form-control-sm border-input" id="current_group" name="current_group">
                                        </div>
                                    </div>
                                    <div class="col d-none">
                                        <div class="mb-3">
                                            <label class="form-label small label-input">Group Number</label>
                                            <input type="text" class="form-control form-control-sm border-input" id="current_group_stocker" name="current_group_stocker">
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="mb-3">
                                            <label class="form-label small label-calc">Estimasi Amparan</label>
                                            <input type="number" class="form-control form-control-sm border-calc"
                                                id="current_est_amparan" name="current_est_amparan" step=".01" readonly>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="mb-3">
                                            <label class="form-label small label-sb">Lembar Gelaran</label>
                                            <input type="hidden" id="lembar_gelaran" name="lembar_gelaran">
                                            <input type="number" class="form-control form-control-sm border-sb" id="current_lembar_gelaran" name="current_lembar_gelaran" {{ $stockerData ? 'readonly' : '' }}
                                                onkeyup="
                                                    calculatePemakaianLembar();
                                                    calculateTotalPemakaian();
                                                    calculateShortRoll();
                                                    updatePlyProgress();
                                                    // calculateSisaKain();
                                                "
                                                onchange="
                                                    calculatePemakaianLembar();
                                                    calculateTotalPemakaian();
                                                    calculateShortRoll();
                                                    updatePlyProgress();
                                                    // calculateSisaKain();
                                                "
                                            >
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="form-label small label-sb">Ply Progress</label>
                                    <div class="progress border border-sb" style="height: 31px">
                                        <p class="position-absolute" style="top: 59%;left: 50%;transform: translate(-50%, -50%);" id="current_ply_progress_txt"></p>
                                        <div class="progress-bar" style="background-color: #75baeb;" role="progressbar" id="current_ply_progress"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="mb-3">
                                    <label class="form-label small label-input">Kepala Kain</label>
                                    <div class="input-group input-group-sm mb-3">
                                        <input type="number" class="form-control border-input" id="current_kepala_kain" name="current_kepala_kain" step=".01"
                                            onkeyup="
                                                calculatePemakaianLembar();
                                                calculateTotalPemakaian();
                                                calculateShortRoll();
                                                // calculateSisaKain();
                                            "
                                            onchange="
                                                calculatePemakaianLembar();
                                                calculateTotalPemakaian();
                                                calculateShortRoll();
                                                // calculateSisaKain();
                                            ">
                                        <span class="input-group-text input-group-unit"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="mb-3">
                                    <label class="form-label small label-input">Sisa Tidak Bisa</label>
                                    <div class="input-group input-group-sm mb-3">
                                        <input type="number" class="form-control border-input" id="current_sisa_tidak_bisa" name="current_sisa_tidak_bisa" step=".01"
                                            onkeyup="
                                                calculatePemakaianLembar();
                                                calculateTotalPemakaian();
                                                calculateShortRoll();
                                                // calculateSisaKain();
                                            "
                                            onchange="
                                                calculatePemakaianLembar();
                                                calculateTotalPemakaian();
                                                calculateShortRoll();
                                                // calculateSisaKain();
                                            ">
                                        <span class="input-group-text input-group-unit"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="mb-3">
                                    <label class="form-label small label-input">Reject</label>
                                    <div class="input-group input-group-sm mb-3">
                                        <input type="number" class="form-control form-control-sm border-input" id="current_reject" name="current_reject" step=".01"
                                            onkeyup="
                                                calculatePemakaianLembar();
                                                calculateTotalPemakaian();
                                                calculateShortRoll();
                                                calculateSisaKain();
                                            "
                                            onchange="
                                                calculatePemakaianLembar();
                                                calculateTotalPemakaian();
                                                calculateShortRoll();
                                                calculateSisaKain();
                                            ">
                                        <span class="input-group-text input-group-unit"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="mb-3">
                                    <label class="form-label small label-input">Piping</label>
                                    <div class="input-group input-group-sm mb-3">
                                        <input type="number" class="form-control form-control-sm border-input" id="current_piping" name="current_piping" step=".01"
                                            onkeyup="
                                                calculatePemakaianLembar();
                                                calculateTotalPemakaian();
                                                calculateShortRoll();
                                                calculateSisaKain();
                                            "
                                            onchange="
                                                calculatePemakaianLembar();
                                                calculateTotalPemakaian();
                                                calculateShortRoll();
                                                calculateSisaKain();
                                            ">
                                        <span class="input-group-text input-group-unit"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-4" id="total-sambungan-section">
                                <label class="form-label small label-input">Sambungan Roll</label>
                                <div class="input-group input-group-sm mb-3">
                                    <input type="number" class="form-control form-control-sm" id="current_total_sambungan_roll" name="current_total_sambungan_roll" onkeyup="calculatePemakaianLembar();calculatePemakaianLembar();calculateTotalPemakaian();calculateShortRoll();" onchange="calculatePemakaianLembar();calculatePemakaianLembar();calculateTotalPemakaian();calculateShortRoll();">
                                    <span class="input-group-text input-group-unit"></span>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="mb-3">
                                    <label class="form-label small label-input">Sisa Kain</label>
                                    <div class="input-group input-group-sm mb-3">
                                        <input type="hidden" id="current_sisa_kain_ori" name="current_sisa_kain_ori">
                                        <input type="number" class="form-control form-control-sm border-input" id="current_sisa_kain" name="current_sisa_kain" step=".01"
                                            onkeyup="
                                                calculateShortRoll();
                                            "
                                            onchange="
                                                calculateShortRoll();
                                            ">
                                        <span class="input-group-text input-group-unit"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="row align-items-end">
                                    <div class="col-8">
                                        <div class="mb-3">
                                            <label class="form-label small label-calc">Short Roll +/-</label>
                                            <div class="input-group input-group-sm mb-3">
                                                <input type="number" class="form-control form-control-sm border-calc" id="current_short_roll" name="current_short_roll" step=".01" readonly>
                                                <span class="input-group-text input-group-unit"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="mb-3">
                                            <label class="form-label small label-calc">(%)</label>
                                            <input type="number" class="form-control form-control-sm border-calc" id="current_short_roll_percentage" name="current_short_roll_percentage" step=".01" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="form-label small label-calc">Pemakaian Lembar</label>
                                    <div class="input-group input-group-sm mb-3">
                                        <input type="number" class="form-control form-control-sm border-calc" id="current_pemakaian_lembar" name="current_pemakaian_lembar" step=".01" readonly>
                                        <span class="input-group-text input-group-unit"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="form-label small label-calc">Tot. Pemakaian Roll</label>
                                    <div class="input-group input-group-sm mb-3">
                                        <input type="number" class="form-control form-control-sm border-calc" id="current_total_pemakaian_roll" name="current_total_pemakaian_roll" step=".01" readonly>
                                        <span class="input-group-text input-group-unit"></span>
                                    </div>
                                </div>
                            </div>
                            {{-- Roll Sambungan (tambah roll / edit menjadi need extension, muncul jika sisa gelaran > 0) --}}
                            <div class="col-12 d-none" id="add-roll-ext-section">
                                <div class="ext-card">
                                    <div class="ext-card-header">
                                        <div class="ext-card-title"><i class="fa-solid fa-link"></i> Roll Sambungan</div>
                                        <small>Roll ini punya sisa gelaran, jadi perlu roll lain untuk menyambung lembar terakhirnya.</small>
                                    </div>
                                    <div class="ext-card-body">
                                        <input type="hidden" id="ext_detail_item" name="ext_detail_item">
                                        <input type="hidden" id="ext_roll" name="ext_roll">

                                        {{-- 1. Scan roll sambungan --}}
                                        <div class="ext-step">
                                            <span class="ext-step-number">1</span> Scan roll sambungan
                                            <span class="ext-step-hint">&mdash; unit harus sama dengan roll lain (yard otomatis dikonversi ke meter)</span>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label small">Id Roll</label>
                                                    <div class="input-group input-group-sm">
                                                        <input type="text" class="form-control" id="ext_id_roll" name="ext_id_roll" placeholder="Scan / ketik ID roll">
                                                        <button class="btn btn-success text-light" type="button" onclick="fetchExtScan()">Get</button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label small">Id Item</label>
                                                    <input type="text" class="form-control form-control-sm" id="ext_id_item" name="ext_id_item" readonly>
                                                </div>
                                            </div>
                                            <div class="col-md-2 col-6">
                                                <div class="mb-3">
                                                    <label class="form-label small">Lot</label>
                                                    <input type="text" class="form-control form-control-sm" id="ext_lot" name="ext_lot" readonly>
                                                </div>
                                            </div>
                                            <div class="col-md-2 col-6">
                                                <div class="mb-3">
                                                    <label class="form-label small">Roll Buyer</label>
                                                    <input type="text" class="form-control form-control-sm" id="ext_roll_buyer" name="ext_roll_buyer" readonly>
                                                </div>
                                            </div>
                                            <div class="col-md-4 col-6">
                                                <div class="mb-3">
                                                    <label class="form-label small">Qty</label>
                                                    <div class="input-group input-group-sm">
                                                        <input type="number" class="form-control" id="ext_qty_real" name="ext_qty_real" readonly>
                                                        <input type="text" class="form-control" style="max-width: 80px" id="ext_unit" name="ext_unit" readonly>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4 col-6">
                                                <div class="mb-3">
                                                    <label class="form-label small label-calc">Qty Konversi</label>
                                                    <div class="input-group input-group-sm">
                                                        <input type="number" class="form-control border-calc" id="ext_qty" name="ext_qty" readonly>
                                                        <span class="input-group-text ext-unit-suffix"></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- 2. Bagian yang menyambung --}}
                                        <div class="ext-step mt-1">
                                            <span class="ext-step-number">2</span> Bagian yang menyambung
                                            <span class="ext-step-hint">&mdash; panjang sambungan = panjang amparan &minus; sisa gelaran</span>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4 col-6">
                                                <div class="mb-3">
                                                    <label class="form-label small label-calc">Panjang Sambungan</label>
                                                    <div class="input-group input-group-sm">
                                                        <input type="number" class="form-control border-calc" id="ext_sambungan" readonly>
                                                        <span class="input-group-text ext-unit-suffix"></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4 col-6">
                                                <div class="mb-3">
                                                    <label class="form-label small label-input">Lembar Sambungan</label>
                                                    <input type="number" class="form-control form-control-sm border-input ext-calc" id="ext_lembar_sambungan" name="ext_lembar_sambungan" value="1" step="1" min="1">
                                                </div>
                                            </div>
                                        </div>

                                        {{-- 3. Sisa roll sambungan yang lanjut digelar --}}
                                        <div class="ext-step mt-1">
                                            <span class="ext-step-number">3</span> Sisa roll yang lanjut digelar
                                            <span class="ext-step-hint">&mdash; isi 0 jika tidak ada</span>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-3 col-6">
                                                <div class="mb-3">
                                                    <label class="form-label small label-input">Lembar Gelaran</label>
                                                    <input type="number" class="form-control form-control-sm border-input ext-calc" id="ext_lembar_gelaran" name="ext_lembar_gelaran" value="0" step="1" min="0">
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-6 d-none" id="ext_berat_amparan_col">
                                                <div class="mb-3">
                                                    <label class="form-label small label-input">Berat 1 Amparan</label>
                                                    <div class="input-group input-group-sm">
                                                        <input type="number" class="form-control border-input ext-calc" id="ext_berat_amparan" name="ext_berat_amparan" value="0" step=".01" min="0">
                                                        <span class="input-group-text">KG</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-6">
                                                <div class="mb-3">
                                                    <label class="form-label small label-input">Kepala Kain</label>
                                                    <div class="input-group input-group-sm">
                                                        <input type="number" class="form-control border-input ext-calc" id="ext_kepala_kain" name="ext_kepala_kain" value="0" step=".01" min="0">
                                                        <span class="input-group-text ext-unit-suffix"></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-6">
                                                <div class="mb-3">
                                                    <label class="form-label small label-input">Sisa Tidak Bisa</label>
                                                    <div class="input-group input-group-sm">
                                                        <input type="number" class="form-control border-input ext-calc" id="ext_sisa_tidak_bisa" name="ext_sisa_tidak_bisa" value="0" step=".01" min="0">
                                                        <span class="input-group-text ext-unit-suffix"></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-6">
                                                <div class="mb-3">
                                                    <label class="form-label small label-input">Reject</label>
                                                    <div class="input-group input-group-sm">
                                                        <input type="number" class="form-control border-input ext-calc" id="ext_reject" name="ext_reject" value="0" step=".01" min="0">
                                                        <span class="input-group-text ext-unit-suffix"></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-6">
                                                <div class="mb-3">
                                                    <label class="form-label small label-input">Piping</label>
                                                    <div class="input-group input-group-sm">
                                                        <input type="number" class="form-control border-input ext-calc" id="ext_piping" name="ext_piping" value="0" step=".01" min="0">
                                                        <span class="input-group-text ext-unit-suffix"></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-6">
                                                <div class="mb-3">
                                                    <label class="form-label small label-input">Sisa Kain</label>
                                                    <div class="input-group input-group-sm">
                                                        <input type="number" class="form-control border-input ext-calc" id="ext_sisa_kain" name="ext_sisa_kain" value="0" step=".01" min="0">
                                                        <span class="input-group-text ext-unit-suffix"></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Ringkasan --}}
                                        <div class="ext-summary">
                                            <div class="ext-summary-item">
                                                <small>Qty Roll</small>
                                                <input type="text" id="ext_summary_qty" readonly tabindex="-1">
                                            </div>
                                            <div class="ext-summary-item">
                                                <small>Dipakai Menyambung</small>
                                                <input type="text" id="ext_usage" readonly tabindex="-1">
                                            </div>
                                            <div class="ext-summary-item" id="ext_rest_qty_item">
                                                <small>Sisa Roll (setelah menyambung)</small>
                                                <input type="number" id="ext_rest_qty" readonly tabindex="-1">
                                            </div>
                                            <div class="ext-summary-item" id="ext_short_roll_item">
                                                <small>Short Roll +/-</small>
                                                <input type="number" id="ext_short_roll" readonly tabindex="-1">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 d-none">
                                <div class="mb-3">
                                    <label class="form-label small label-calc">Last Status</label>
                                    <input type="text" class="form-control form-control-sm" id="current_latest_status" name="current_latest_status" step=".01" readonly>
                                </div>
                            </div>
                            <div class="col-md-12">
                                {{-- <div class="row justify-content-between">
                                    <div class="col-md-4">
                                        <button type="button" class="btn btn-no btn-sm btn-block my-3 fw-bold" id="stopLapButton" onclick="deleteTimeRecord()" {{ $stockerData ? 'disabled' : '' }}><i class="fa fa-trash"></i> HAPUS</button>
                                    </div>
                                    <div class="col-md-4">
                                        <button type="button" class="btn btn-yes btn-sm btn-block my-3 fw-bold" id="stopLapButton" onclick="storeTimeRecord()" {{ $stockerData ? 'disabled' : '' }}><i class="fa fa-save"></i> SIMPAN</button>
                                    </div>
                                </div> --}}
                                <div class="row justify-content-between">
                                    <div class="col-md-4">
                                        @role("superadmin")
                                            <button type="button" class="btn btn-no btn-sm btn-block my-3 fw-bold btn-delete-roll" id="stopLapButton" onclick="deleteTimeRecord()"><i class="fa fa-trash"></i> HAPUS</button>
                                        @endrole
                                    </div>
                                    <div class="col-md-4">
                                        <button type="button" class="btn btn-yes btn-sm btn-block my-3 fw-bold" id="stopLapButton" onclick="storeTimeRecord()"><i class="fa fa-save"></i> SIMPAN</button>
                                    </div>
                                </div>
                            </div>
                            {{-- @if ($stockerData)
                                <div class="col-md-12">
                                    <div class="alert alert-danger mt-3" role="alert">
                                        Tidak dapat mengubah form. Form sudah menjadi stocker. <a href="{{ route('show-stocker') }}/{{ $id }}" target="_blank"><b>Ke Halaman Stocker.</b></a>
                                    </div>
                                </div>
                            @endif --}}
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Update Form Status --}}
    @if (Auth::user()->roles->whereIn("nama_role", ["superadmin"])->count() > 0)
        <div class="card" id="status-card">
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label small">UBAH STATUS</label>
                    <div class="d-flex gap-1 mb-3">
                        <select class="form-control select2bs4" name="edit_status" id="edit_status">
                            <option value="SPREADING">SPREADING</option>
                            <option value="PENGERJAAN FORM CUTTING">PENGERJAAN FORM CUTTING</option>
                            <option value="PENGERJAAN FORM CUTTING DETAIL">PENGERJAAN FORM CUTTING DETAIL</option>
                            <option value="PENGERJAAN FORM CUTTING SPREAD">PENGERJAAN FORM CUTTING SPREAD</option>
                            <option value="SELESAI PENGERJAAN" selected>SELESAI PENGERJAAN</option>
                        </select>
                        <button class="btn btn-success btn-sm" onclick="updateStatus()"><i class="fa fa-save"></i></button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Panduan --}}
    @php $cgSuperadmin = Auth::user()->roles->whereIn("nama_role", ["superadmin"])->count() > 0; @endphp
    <div class="modal fade" id="cfGuide" tabindex="-1" aria-labelledby="cfGuideTitle" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-sb text-light">
                    <div>
                        <small class="d-block text-uppercase fw-bold" style="font-size: 11px; letter-spacing: .6px; opacity: .8">Panduan singkat</small>
                        <h5 class="modal-title fw-bold" id="cfGuideTitle">Detail Completed Form</h5>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body cg">
                    <nav class="cg-nav">
                        <a href="#cg-peta">Peta halaman</a><a href="#cg-alur">Alur & warna</a><a href="#cg-header">Header</a><a href="#cg-detail">Detail</a><a href="#cg-recalc">Loss Time & Recalculate</a>
                        <a href="#cg-summary">Summary</a><a href="#cg-spread">Spreading</a><a href="#cg-ext">Roll sambungan</a>@if ($cgSuperadmin)<a href="#cg-status">Ubah status</a>@endif<a href="#cg-tips">Tips & FAQ</a>
                    </nav>

                    <section id="cg-peta">
                        <h6><span>Peta halaman</span> <button type="button" class="cg-show pri" data-tour="0"><i class="fa fa-play"></i> Mulai tur</button></h6>
                        <p class="cg-note">Klik <b>nomor</b> untuk membaca penjelasannya, atau klik <b>kotaknya</b> untuk melihat langsung di halaman.</p>
                        <div class="cg-map">
                            <div class="cg-m-head">
                                <b>Form Cut / No. Form / Meja</b>
                                <div class="cg-z r" data-spot="#cf-head-r"><i></i><i class="p"></i></div>
                            </div>
                            <div class="cg-m-grid">
                                <div class="cg-z cg-card" data-spot="#header-data-card">
                                    <a href="#cg-header" class="cg-n" aria-label="Header Data">1<span class="cg-tip" role="tooltip"><b>Header Data</b>Meja, jam Start/Finish dan QTY Cut Ply.</span></a>
                                    <div class="h">Header Data</div>
                                    <div class="cg-f"><i class="in"></i><i class="in"></i><i class="in"></i><i></i><i></i><i></i><i></i><i></i><i class="in"></i></div>
                                    <div class="cg-sv"></div>
                                </div>
                                <div class="cg-z cg-card" data-spot="#detail-data-card">
                                    <a href="#cg-detail" class="cg-n tip-l" aria-label="Detail Data">2<span class="cg-tip" role="tooltip"><b>Detail Data</b>Panjang, comma & lebar actual. Cons Ampar dihitung otomatis.</span></a>
                                    <div class="h">Detail Data</div>
                                    <div class="cg-f c2"><i></i><i class="in"></i><i></i><i class="in"></i><i></i><i class="ca"></i></div>
                                    <div class="cg-sv"></div>
                                </div>
                            </div>
                            <div class="cg-z cg-card min" data-spot="#lost-time-card">
                                <a href="#cg-recalc" class="cg-n" aria-label="Loss Time">3<span class="cg-tip" role="tooltip"><b>Loss Time</b>Daftar waktu loss time saat pengerjaan (hanya lihat).</span></a>
                                <div class="h">Loss Time</div>
                            </div>
                            <div class="cg-z" data-spot="#recalculate-btn">
                                <a href="#cg-recalc" class="cg-n sm" aria-label="Recalculate Form">4<span class="cg-tip" role="tooltip"><b>Recalculate Form</b>Menghitung ulang seluruh form dari data roll.</span></a>
                                <div class="cg-rc">Recalculate Form</div>
                            </div>
                            <div class="cg-z cg-card" data-spot="#summary-card">
                                <a href="#cg-summary" class="cg-n" aria-label="Summary">5<span class="cg-tip" role="tooltip"><b>Summary</b>Daftar roll. Klik baris untuk edit, geser untuk ubah urutan, Tambah Roll untuk menyisipkan.</span></a>
                                <div class="h">Summary</div>
                                <div class="cg-bar"><span>⋮⋮ geser · 🔗 sambungan · klik baris</span><u>+ Tambah Roll</u></div>
                                <div class="cg-tbl"><s></s><s>⋮⋮<em></em></s><s class="sel">⋮⋮<em></em></s><s class="ct">🔗<em></em></s><s>⋮⋮<em></em></s></div>
                            </div>
                            <div class="cg-z cg-card" data-spot="#spreading-form-card">
                                <a href="#cg-spread" class="cg-n" aria-label="Spreading">6<span class="cg-tip" role="tooltip"><b>Spreading</b>Form edit roll terpilih: qty, lembar gelaran, sisa kain, dll. Lalu SIMPAN.</span></a>
                                <div class="h">Spreading</div>
                                <div class="cg-f"><i class="in"></i><i class="in"></i><i class="in"></i><i class="in"></i><i class="ca"></i><i class="in"></i></div>
                                <div class="cg-z cg-ext" data-spot="#add-roll-ext-section:not(.d-none)|#sisa-gelaran-col">
                                    <a href="#cg-ext" class="cg-n sm" aria-label="Roll sambungan">7<span class="cg-tip" role="tooltip"><b>Roll sambungan</b>Muncul bila Sisa Gelaran diisi: scan roll yang menyambung lembar terakhir.</span></a>
                                    <em>🔗 ROLL SAMBUNGAN</em>
                                </div>
                                <div class="cg-btns"><i></i><i></i></div>
                            </div>
                            @if ($cgSuperadmin)
                                <div class="cg-z cg-card min" data-spot="#status-card">
                                    <a href="#cg-status" class="cg-n" aria-label="Ubah status">8<span class="cg-tip" role="tooltip"><b>Ubah status</b>Khusus superadmin: mengubah status form.</span></a>
                                    <div class="h">Ubah Status</div>
                                </div>
                            @endif
                        </div>
                    </section>

                    <section id="cg-alur">
                        <h6>Alur singkat & arti warna</h6>
                        <div class="cg-flow">
                            <div><b>Isi Operator</b><span>di bawah tabel Summary</span></div><i>→</i>
                            <div><b>Klik roll</b><span>di Summary</span></div><i>→</i>
                            <div><b>Ubah di Spreading</b><span>angka hitungan ikut berubah</span></div><i>→</i>
                            <div><b>SIMPAN</b><span>form dihitung ulang</span></div>
                        </div>
                        <div class="cg-leg">
                            <span><i style="border-color:#3169e4"></i><b style="color:#3169e4">Biru</b> data dari marker/WS</span>
                            <span><i style="border-color:#9aa4b1"></i><b>Hitam</b> diisi manual</span>
                            <span><i style="border-color:#fa4456"></i><b style="color:#fa4456">Merah</b> hasil hitungan otomatis</span>
                            <span><i style="border-color:#12be60"></i><b style="color:#0e8845">Hijau</b> data hasil scan roll</span>
                        </div>
                    </section>

                    <section id="cg-header">
                        <h6><span>1. Header Data</span> <button type="button" class="cg-show" data-spot="#header-data-card"><i class="fa fa-crosshairs"></i> Tunjukkan di halaman</button></h6>
                        <ul>
                            <li>Yang bisa diubah: <b>Meja</b>, <b>Start</b>, <b>Finish</b>, dan <b>QTY Cut Ply</b>. Kolom biru lainnya hanya informasi.</li>
                            <li>Tabel ratio menunjukkan qty per size. <b>Qty Output</b> = ratio × QTY Cut Ply.</li>
                            <li>Tekan <b>SIMPAN</b> di kartu ini untuk menyimpan perubahan header.</li>
                        </ul>
                    </section>

                    <section id="cg-detail">
                        <h6><span>2. Detail Data</span> <button type="button" class="cg-show" data-spot="#detail-data-card"><i class="fa fa-crosshairs"></i> Tunjukkan di halaman</button></h6>
                        <ul>
                            <li>Isi <b>Panjang Actual</b>, <b>Comma Actual</b>, dan <b>Lebar Actual</b> sesuai kondisi di meja.</li>
                            <li><b>Cons Ampar</b>, <b>Est. Kebutuhan Kain</b> dan perhitungan roll ikut berubah otomatis.</li>
                            <li>Tekan <b>SIMPAN</b> di kartu ini.</li>
                        </ul>
                    </section>

                    <section id="cg-recalc">
                        <h6><span>3–4. Loss Time & Recalculate Form</span> <button type="button" class="cg-show" data-spot="#recalculate-btn"><i class="fa fa-crosshairs"></i> Tunjukkan di halaman</button></h6>
                        <ul>
                            <li><b>Loss Time</b> (tertutup, klik <i class="fas fa-plus"></i> untuk membuka): daftar loss time saat pengerjaan. Hanya untuk dilihat.</li>
                            <li><b>Recalculate Form</b>: menghitung ulang total, cons actual, dan short roll dari semua roll. Pakai bila angka di Summary terlihat tidak sesuai. Halaman akan dimuat ulang.</li>
                        </ul>
                    </section>

                    <section id="cg-summary">
                        <h6><span>5. Summary</span> <button type="button" class="cg-show" data-spot="#summary-card"><i class="fa fa-crosshairs"></i> Tunjukkan di halaman</button></h6>
                        <ul>
                            <li><b>Klik baris</b> roll untuk memuatnya ke form <b>Spreading</b> di bawah. Baris yang sedang diedit berwarna biru muda.</li>
                            <li><b><i class="fa-solid fa-grip-vertical"></i> Geser</b> pegangan di kolom pertama untuk mengubah urutan roll. Setelah dikonfirmasi, urutan disimpan dan form dihitung ulang.</li>
                            <li>Baris dengan <b><i class="fa-solid fa-link"></i></b> dan garis kiri biru adalah <b>roll sambungan</b>; ikut pindah bersama roll di atasnya.</li>
                            <li><b><i class="fa fa-plus"></i> Tambah Roll</b>: pilih posisi sisip, lalu scan ID Roll di form Spreading dan simpan.</li>
                            <li><b>Operator</b> dan <b>Cons. Actual</b> wajib terisi. Tanpa itu, simpan akan ditolak.</li>
                        </ul>
                    </section>

                    <section id="cg-spread">
                        <h6><span>6. Spreading</span> <button type="button" class="cg-show" data-spot="#spreading-form-card"><i class="fa fa-crosshairs"></i> Tunjukkan di halaman</button></h6>
                        <ul>
                            <li>Ubah data roll terpilih: <b>Lembar Gelaran</b>, <b>Kepala Kain</b>, <b>Sisa Tidak Bisa</b>, <b>Reject</b>, <b>Piping</b>, <b>Sisa Kain</b>, <b>Group</b>.</li>
                            <li>Kolom merah (<b>Pemakaian</b>, <b>Short Roll</b>) dihitung otomatis. Muncul peringatan bila Short Roll kurang dari <b>-2%</b>.</li>
                            <li>Untuk mengganti roll, scan/ketik <b>Id Roll</b> lalu tekan <kbd>Enter</kbd> atau <b>Get</b>.</li>
                            <li><b>SIMPAN</b> menyimpan roll. <b>HAPUS</b> hanya tersedia untuk superadmin.</li>
                        </ul>
                    </section>

                    <section id="cg-ext">
                        <h6><span>7. Roll sambungan</span> <button type="button" class="cg-show" data-spot="#add-roll-ext-section:not(.d-none)|#sisa-gelaran-col"><i class="fa fa-crosshairs"></i> Tunjukkan di halaman</button></h6>
                        <ul>
                            <li>Bila roll habis di tengah lembar, isi <b>Sisa Gelaran</b>. Kotak <b>Roll Sambungan</b> akan muncul.</li>
                            <li>Ikuti 3 langkahnya: <b>scan roll sambungan</b> → isi <b>Lembar Sambungan</b> → isi sisa roll yang lanjut digelar (isi 0 bila tidak ada).</li>
                            <li>Unit roll sambungan harus sama; yard otomatis dikonversi ke meter.</li>
                            <li>Untuk melepas sambungan: buka roll pertama, kosongkan <b>Sisa Gelaran</b>, lalu simpan.</li>
                        </ul>
                    </section>

                    @if ($cgSuperadmin)
                        <section id="cg-status">
                            <h6><span>8. Ubah status</span> <button type="button" class="cg-show" data-spot="#status-card"><i class="fa fa-crosshairs"></i> Tunjukkan di halaman</button></h6>
                            <ul>
                                <li>Khusus superadmin. Pilih status lalu tekan <i class="fa fa-save"></i>. Halaman pengerjaan form terbuka di tab baru.</li>
                            </ul>
                        </section>
                    @endif

                    <section id="cg-tips">
                        <h6>Tips & FAQ</h6>
                        <dl class="cg-faq">
                            <dt>Muncul peringatan "WARNING" soal stocker saat simpan?</dt>
                            <dd>Form ini sudah dibuatkan stocker, perubahan bisa memengaruhi stocker. Tekan <b>Lanjutkan</b> hanya bila memang perlu, selain itu <b>Batalkan</b>.</dd>
                            <dt>Roll tidak bisa dihapus?</dt>
                            <dd>Roll yang masih punya / menjadi sambungan harus dilepas dulu sambungannya (kosongkan Sisa Gelaran roll pertama lalu simpan).</dd>
                            <dt>"Unit roll tidak sesuai"?</dt>
                            <dd>Semua roll dalam satu form harus satuan yang sama (meter/yard atau kg).</dd>
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
        var id = document.getElementById("id").value;

        var summaryData = null;
        var totalSummaryData = 0;

        // -Ratio & Qty Cuy-
        var totalRatio = document.getElementById('total_ratio').value;
        var totalQtyCut = document.getElementById('total_qty_cut_ply').value;

        $(document).ready(async () => {
            document.getElementById("loading").classList.remove("d-none");

            await getNumberData();

            await clearSpreadingForm();

            await lockItemSpreading();

            await getSummary();

            document.getElementById("bypass").checked = false;

            document.getElementById("loading").classList.add("d-none");

            // Select2 Autofocus
            $(document).on('select2:open', () => {
                document.querySelector('.select2-search__field').focus();
            });

            // Initialize Select2 Elements
            $('.select2').select2()

            // Initialize Select2BS4 Elements
            $('.select2bs4').select2({
                theme: 'bootstrap4',
                containerCssClass: 'form-control-sm'
            })
        });

        function fetchScan() {
            let idRollElement = document.getElementById('current_id_roll');
            let idRollOriElement = document.getElementById('current_id_roll_ori');

            if (idRollElement.value.length > 0 && idRollElement.value != '-') {
                getScannedItem(idRollElement.value);
                // if (idRollElement.value != idRollOriElement.value) {
                //     getScannedItem(idRollElement.value);
                // } else {
                //     $("#current_unit").val($("#current_unit_ori").val()).trigger("change");
                //     $("#current_qty_real").val($("#current_qty_ori").val()).trigger("change");
                // }
            }
        }

        document.getElementById("current_id_roll").addEventListener("keyup", (event) => {
            if (event.keyCode === 13) {
                fetchScan();
            }
        });

        // -Get Scanned Item Data-
        function getScannedItem(id) {
            if (isNotNull(id)) {
                document.getElementById("loading").classList.remove("d-none");

                return $.ajax({
                    url: '{{ route('get-scanned-form-cut-input') }}/' + id,
                    type: 'get',
                    dataType: 'json',
                    data: {
                        "form_cut_detail_id": document.getElementById("current_id").value,
                        "at_time": isAddRollMode() ? document.getElementById("add_roll_insert_at").value : ""
                    },
                    success: function(res) {
                        document.getElementById("loading").classList.add("d-none");

                        console.log(res);

                        // unit roll harus sesuai dengan roll lain di form
                        let unitMessage = res && res.qty > 0 ? rollUnitMismatchMessage(id, res.unit, formRollUnit(document.getElementById("current_id").value)) : null;

                        if (unitMessage) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Unit roll tidak sesuai',
                                html: unitMessage,
                                confirmButtonText: 'Mengerti',
                            });

                            document.getElementById("current_id_roll").value = document.getElementById("current_id_roll_ori").value;
                        } else if (res && res.qty > 0) {
                            setSpreadingForm(res, true, null, true);
                        } else {
                            let message = res && res.message ? res.message : 'Roll tidak tersedia atau sudah habis.';
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                html: message,
                                showCancelButton: false,
                                showConfirmButton: true,
                                confirmButtonText: 'Oke',
                            });

                            document.getElementById("current_id_roll").value = document.getElementById("current_id_roll_ori").value;
                        }

                    }, error: function(jqXHR) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: ajaxErrorMessage(jqXHR, 'Roll tidak tersedia atau sudah habis.'),
                            showCancelButton: false,
                            showConfirmButton: true,
                            confirmButtonText: 'Oke',
                        });

                        document.getElementById("current_id_roll").value = document.getElementById("current_id_roll_ori").value;

                        document.getElementById("loading").classList.add("d-none");
                    }
                });
            }

            return Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: 'Item tidak ditemukan',
                showCancelButton: false,
                showConfirmButton: true,
                confirmButtonText: 'Oke',
            });
        }

        // -Calculate Cons. Actual 1 Gelaran-
        function calculateConsActualGelaran(unit, totalQtyFabric, totalKepalaKain, totalSisaTidakBisa, totalReject, totalSisaKain, totalPiping, totalShortRoll, totalLembar, totalTotalPemakaian) {
            let unitVar = unit;
            let totalQtyFabricVar = totalQtyFabric ? Number(totalQtyFabric) : 0;
            let totalKepalaKainVar = totalKepalaKain ? Number(totalKepalaKain) : 0;
            let totalSisaTidakBisaVar = totalSisaTidakBisa ? Number(totalSisaTidakBisa) : 0;
            let totalRejectVar = totalReject ? Number(totalReject) : 0;
            let totalSisaKainVar = totalSisaKain ? Number(totalSisaKain) : 0;
            let totalPipingVar = totalPiping ? Number(totalPiping) : 0;
            let totalShortRollVar = totalShortRoll ? Number(totalShortRoll) : 0;
            let totalLembarVar = totalLembar ? Number(totalLembar) : 0;
            let totalTotalPemakaianVar = totalTotalPemakaian ? Number(totalTotalPemakaian) : 0;

            let consActualGelaran = (totalLembar * totalRatio) > 0 ? (totalQtyFabricVar - totalSisaKainVar)/(totalLembarVar * totalRatio) : 0;
            let consActualGelaranShortRolless = (totalLembar * totalRatio) > 0 ? (totalQtyFabricVar - totalSisaKainVar + totalShortRollVar)/(totalLembarVar * totalRatio) : 0;

            document.getElementById('cons_actual_gelaran').value = Number(consActualGelaran).round(3);
            document.getElementById('cons_actual_gelaran_short_rolless').value = Number(consActualGelaranShortRolless).round(3);

            document.getElementById("unit_cons_actual_gelaran").value = unitVar.toLowerCase();
            document.getElementById("unit_cons_actual_gelaran_short_rolless").value = unitVar.toLowerCase();

            calculateConsAmpar();

            consUpRate();
        }

        function consUpRate() {
            let consActualGelaran = document.getElementById('cons_actual_gelaran').value;
            let unitConsActualGelaran = document.getElementById('unit_cons_actual_gelaran').value;

            let consActualGelaranShortRolless = document.getElementById('cons_actual_gelaran_short_rolless').value;
            let unitConsActualGelaranShortRolless = document.getElementById('unit_cons_actual_gelaran_short_rolless').value;

            let consWs = document.getElementById('cons_ws').value;
            let consMarker = document.getElementById('cons_marker').value;

            let consWsUpRate = 0;
            let consMarkerUpRate = 0;
            let consWsUpRateNoSr = 0;
            let consMarkerUpRateNoSr = 0;

            if ((unitConsActualGelaran == "YARD" || unitConsActualGelaran == "YRD") && (unitConsActualGelaranShortRolless == "YARD" || unitConsActualGelaranShortRolless == "YRD")) {
                let consActualGelaranConverted = conversion(consActualGelaran, "METER", unitConsActualGelaran.toUpperCase());
                let consActualGelaranShortRollessConverted = conversion(consActualGelaranShortRolless, "METER", unitConsActualGelaranShortRolless.toUpperCase());

                consWsUpRate = (consActualGelaranConverted - consWs)/consWs * 100;
                consMarkerUpRate = ((consActualGelaranConverted - consMarker)/consMarker) * 100;

                consWsUpRateNoSr = ((consActualGelaranShortRollessConverted - consWs)/consWs) * 100;
                consMarkerUpRateNoSr = ((consActualGelaranShortRollessConverted - consMarker)/consMarker) * 100;

                // console.log("cons actual gelaran converted "+consActualGelaranConverted, consWs, consMarker, consWsUpRate, consMarkerUpRate);
            } else {
                consWsUpRate = ((consActualGelaran - consWs)/consWs) * 100;
                consMarkerUpRate = ((consActualGelaran - consMarker)/consMarker) * 100;

                consWsUpRateNoSr = ((consActualGelaranShortRolless - consWs)/consWs) * 100;
                consMarkerUpRateNoSr = ((consActualGelaranShortRolless - consMarker)/consMarker) * 100;

                // console.log("cons actual gelaran "+consActualGelaran, consWs, consMarker);
            }

            document.getElementById('cons_ws_uprate').value = Number(consWsUpRate).round(2);
            document.getElementById('cons_marker_uprate').value = Number(consMarkerUpRate).round(2);
            document.getElementById('cons_ws_uprate_nosr').value = Number(consWsUpRateNoSr).round(2);
            document.getElementById('cons_marker_uprate_nosr').value = Number(consMarkerUpRateNoSr).round(2);
        }

        // -Get Summary Data-
        function getSummary(refresh = false) {
            if (refresh) {
                summaryData = null;
            }

            if (summaryData == null) {
                let id = document.getElementById("id").value;
                let noForm = document.getElementById("no_form").value;

                return $.ajax({
                    url: '{{ route('get-time-form-cut-input') }}/'+ id +'/' + noForm,
                    type: 'get',
                    dataType: 'json',
                    success: function(res) {
                        if (res) {
                            summaryData = res;
                            setSummary(summaryData);
                        }
                    },
                    error: function(jqXHR) {
                        showAjaxError(jqXHR, 'Gagal memuat data waktu.');
                    }
                });
            }
        }

        // -Set Summary Data-
        function setSummary(data) {
            resetSummary();

            summaryData.forEach((data) => {
                appendSummaryItem(data)
            });
        }

        var summaryItemTable = document.getElementById("scannedItemTable");
        var summaryItemTableTbody = summaryItemTable.getElementsByTagName("tbody")[0];
        var totalRow = 0;
        var totalScannedItem = 0;
        var totalSisaGelaran = 0;
        var totalSambungan = 0;
        var totalSambunganRoll = 0;
        var totalEstAmparan = 0;
        var totalAverageTime = 0;
        var totalKepalaKain = 0;
        var totalSisaTidakBisa = 0;
        var totalReject = 0;
        var totalSisaKain = 0;
        var totalPemakaianLembar = 0;
        var totalTotalPemakaian = 0;
        var totalShortRoll = 0;
        var totalShortRollPercentage = 0;
        var totalLembar = 0;
        var totalPiping = 0;
        var totalQtyFabric = 0;
        var totalBeratAmparan = 0;

        var latestKepalaKain = 0;
        var latestSisaTidakBisa = 0;
        var latestReject = 0;
        var latestSisaKain = 0;
        var latestPiping = 0;
        var latestSambungan = 0;
        var latestSambunganRoll = 0;
        var latestLembarSambunganRoll = 0;
        var latestPemakaianLembar = 0;
        var latestTotalPemakaian = 0;
        var latestShortRoll = 0;

        var latestStatus = "";
        var latestQty = 0;
        var latestUnit = "";
        var latestBerat = 0;
        var latestEst = 0;
        var currentRollHeadId = null;
        var latestRollId = null;

        // -Reorder Roll (drag & drop)-
        $(summaryItemTableTbody).sortable({
            items: '> tr.roll-head',
            handle: '.roll-drag-handle',
            axis: 'y',
            cursor: 'move',
            placeholder: 'roll-sort-placeholder',
            helper: function(e, tr) {
                // jaga lebar kolom saat di-drag
                let originals = tr.children();
                let helper = tr.clone();
                helper.children().each(function(index) {
                    $(this).width(originals.eq(index).outerWidth());
                });
                return helper;
            },
            start: function(e, ui) {
                ui.placeholder.html('<td colspan="' + ui.item.children().length + '">&nbsp;</td>');
                // sembunyikan baris lanjutan sambungan selama drag
                $(summaryItemTableTbody).children('tr.roll-continuation[data-head="' + ui.item.data('id') + '"]').hide();
            },
            stop: function(e, ui) {
                normalizeRollRows();
                $(summaryItemTableTbody).children('tr.roll-continuation').show();
            },
            update: function(e, ui) {
                saveRollOrder();
            }
        });

        // Pastikan baris lanjutan sambungan selalu tepat di bawah baris kepalanya
        function normalizeRollRows() {
            let tbody = $(summaryItemTableTbody);
            let heads = tbody.children('tr.roll-head').toArray();
            let continuations = tbody.children('tr.roll-continuation').toArray();

            heads.forEach((head) => {
                tbody.append(head);
                continuations.filter((row) => row.dataset.head == head.dataset.id).forEach((row) => tbody.append(row));
            });
        }

        async function saveRollOrder() {
            let order = $(summaryItemTableTbody).children('tr.roll-head').toArray().map((row) => row.dataset.id);

            // Konfirmasi bypass stocker (sama seperti simpan / hapus roll)
            let proceed = await checkStockerForm();
            if (!proceed) {
                location.reload();
                return;
            }

            Swal.fire({
                icon: 'question',
                title: 'Simpan urutan roll?',
                text: 'Urutan roll (created_at) akan diubah dan form akan di-recalculate.',
                showCancelButton: true,
                confirmButtonText: 'Simpan',
                cancelButtonText: 'Batal',
            }).then((result) => {
                if (!result.isConfirmed) {
                    location.reload();
                    return;
                }

                document.getElementById("loading").classList.remove("d-none");

                $.ajax({
                    url: '{{ route('reorder-spreading-roll') }}',
                    type: 'post',
                    dataType: 'json',
                    data: {
                        id: document.getElementById("id").value,
                        order: order,
                    },
                    success: function(res) {
                        document.getElementById("loading").classList.add("d-none");

                        Swal.fire({
                            icon: res && res.status == 200 ? 'success' : 'info',
                            title: res && res.status == 200 ? 'Berhasil' : 'Info',
                            text: res ? res.message : 'Terjadi Kesalahan.',
                            showConfirmButton: true,
                            confirmButtonText: 'Oke',
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(jqXHR) {
                        document.getElementById("loading").classList.add("d-none");

                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: ajaxErrorMessage(jqXHR, 'Gagal menyimpan urutan roll.'),
                            showConfirmButton: true,
                            confirmButtonText: 'Oke',
                        }).then(() => {
                            location.reload();
                        });
                    }
                });
            });
        }

        // -Unit Roll-
        // kelompok unit: METER/YARD (yard dikonversi ke meter) atau KG
        function rollUnitGroup(unit) {
            unit = (unit ?? '').toString().trim().toUpperCase();

            if (['KGM', 'KG'].includes(unit)) {
                return 'KG';
            }

            if (['METER', 'M', 'MTR', 'YARD', 'YRD', 'YD'].includes(unit)) {
                return 'METER';
            }

            return unit;
        }

        // unit roll lain di form (selain roll yang sedang diedit)
        function formRollUnit(excludeId = '') {
            let row = (summaryData ?? []).find((item) => item.id != excludeId && item.unit);

            return row ? row.unit : null;
        }

        // pesan jika unit roll tidak sekelompok dengan unit form, null jika sesuai
        function rollUnitMismatchMessage(idRoll, unit, formUnit) {
            if (!formUnit || !unit || rollUnitGroup(unit) == rollUnitGroup(formUnit)) {
                return null;
            }

            return 'Roll <b>' + (idRoll || '-') + '</b> memakai unit <b>' + unit.toUpperCase() + '</b>, sedangkan roll lain di form ini memakai unit <b>' + formUnit.toUpperCase() + '</b>.' +
                '<br><br>' + (rollUnitGroup(formUnit) == 'KG'
                    ? 'Form ini hanya bisa memakai roll <b>KG</b>.'
                    : 'Form ini hanya bisa memakai roll <b>METER</b> atau <b>YARD</b> (yard otomatis dikonversi ke meter). Roll <b>KG</b> tidak bisa dipakai.');
        }

        // -Tambah Roll-
        function isAddRollMode() {
            return document.getElementById("add_roll_insert_at").value != "";
        }

        function setAddRollMode(active, insertAfter = "", insertAt = "", positionText = "") {
            document.getElementById("add_roll_insert_after").value = active ? insertAfter : "";
            document.getElementById("add_roll_insert_at").value = active ? insertAt : "";
            document.getElementById("add-roll-position-text").innerText = active ? positionText : "";
            document.getElementById("add-roll-time-text").innerText = active ? insertAt : "";
            document.getElementById("add-roll-banner").classList.toggle("d-none", !active);

            let deleteButton = document.querySelector(".btn-delete-roll");
            if (deleteButton) {
                deleteButton.classList.toggle("d-none", active);
            }

            if (!active) {
                clearExtSection();
            }
            toggleExtSection();
        }

        function applyAddRollLocks() {
            // roll pertama boleh punya sisa gelaran (-> roll sambungan), tapi bukan sambungan
            if (document.getElementById("current_sisa_gelaran").value === "") {
                document.getElementById("current_sisa_gelaran").value = 0;
            }
            document.getElementById("current_sambungan").value = 0;
            document.getElementById("current_sisa_gelaran").removeAttribute("readonly");
            document.getElementById("current_sambungan").setAttribute("readonly", true);
            document.getElementById("current_lembar_gelaran").removeAttribute("readonly");

            toggleExtSection();
        }

        // -Tambah Roll: Roll Sambungan-
        // Bagian Roll Sambungan dipakai jika roll punya sisa gelaran tapi belum punya roll sambungan:
        // - tambah roll, atau
        // - edit roll (bukan baris sambungan) yang diubah menjadi need extension
        function hasAddRollExtension() {
            if (Number(document.getElementById("current_sisa_gelaran").value) <= 0) {
                return false;
            }

            if (isAddRollMode()) {
                return true;
            }

            return document.getElementById("current_id").value != "" &&
                Number(document.getElementById("current_sambungan").value) <= 0 &&
                document.getElementById("current_has_extension").value != "1";
        }

        // waktu acuan qty roll sambungan
        function extAtTime() {
            return isAddRollMode() ? document.getElementById("add_roll_insert_at").value : document.getElementById("current_created_at_ori").value;
        }

        // validasi bagian roll sambungan, mengembalikan Swal jika tidak valid
        function validateExtSection() {
            if (!hasAddRollExtension()) {
                return null;
            }

            let extIdRoll = document.getElementById("ext_id_roll").value;
            if (!extIdRoll || extIdRoll == '-' || !document.getElementById("ext_unit").value) {
                return Swal.fire({
                    icon: 'info',
                    title: 'Info',
                    text: 'Roll punya sisa gelaran. Harap isi ID Roll Sambungan lalu klik "Get" terlebih dahulu.',
                    confirmButtonText: 'Oke',
                });
            }

            if (Number(document.getElementById("ext_sambungan").value) <= 0) {
                return Swal.fire({
                    icon: 'info',
                    title: 'Info',
                    text: 'Sisa gelaran tidak boleh lebih besar atau sama dengan panjang amparan.',
                    confirmButtonText: 'Oke',
                });
            }

            if (Number(document.getElementById("ext_sisa_kain").value) > Number(document.getElementById("ext_rest_qty").value)) {
                return Swal.fire({
                    icon: 'info',
                    title: 'Info',
                    text: 'Sisa kain roll sambungan tidak bisa lebih besar dari sisa roll.',
                    confirmButtonText: 'Oke',
                });
            }

            return null;
        }

        function toggleExtSection() {
            document.getElementById("add-roll-ext-section").classList.toggle("d-none", !hasAddRollExtension());
            calculateExt();
        }

        function clearExtSection() {
            ["ext_id_roll", "ext_id_item", "ext_detail_item", "ext_lot", "ext_roll", "ext_roll_buyer", "ext_qty", "ext_qty_real", "ext_unit", "ext_sambungan", "ext_rest_qty", "ext_short_roll", "ext_summary_qty", "ext_usage"].forEach((id) => {
                document.getElementById(id).value = "";
            });
            document.getElementById("ext_rest_qty_item").classList.remove("is-negative");
            document.getElementById("ext_short_roll_item").classList.remove("is-negative");
            document.querySelectorAll(".ext-unit-suffix").forEach((element) => element.innerText = "");
            ["ext_lembar_gelaran", "ext_kepala_kain", "ext_sisa_tidak_bisa", "ext_reject", "ext_piping", "ext_sisa_kain", "ext_berat_amparan"].forEach((id) => {
                document.getElementById(id).value = 0;
            });
            document.getElementById("ext_lembar_sambungan").value = 1;
            document.getElementById("ext_berat_amparan_col").classList.add("d-none");
        }

        function formPAct() {
            return Number(document.getElementById("p_act").value) + (Number(document.getElementById("comma_act").value) / 100);
        }

        // sama dengan perhitungan di backend (storeAddedRoll / recalculateForm)
        function calculateExt() {
            if (!hasAddRollExtension()) {
                return;
            }

            let pAct = formPAct();
            let firstBerat = Number(document.getElementById("current_berat_amparan").value);
            if (document.getElementById("current_unit").value == "KGM" && firstBerat > 0) {
                pAct = firstBerat;
            }

            let sambungan = pAct - Number(document.getElementById("current_sisa_gelaran").value);
            document.getElementById("ext_sambungan").value = Number(sambungan).round(2);

            let extQty = Number(document.getElementById("ext_unit").value == "KGM" ? document.getElementById("ext_qty_real").value : document.getElementById("ext_qty").value);
            let extPAct = formPAct();
            let extBerat = Number(document.getElementById("ext_berat_amparan").value);
            if (document.getElementById("ext_unit").value == "KGM" && extBerat > 0) {
                extPAct = extBerat;
            }

            let restQty = extQty - (sambungan * Number(document.getElementById("ext_lembar_sambungan").value));
            let restUsage = (extPAct * Number(document.getElementById("ext_lembar_gelaran").value)) +
                Number(document.getElementById("ext_kepala_kain").value) +
                Number(document.getElementById("ext_sisa_tidak_bisa").value) +
                Number(document.getElementById("ext_reject").value) +
                Number(document.getElementById("ext_piping").value);

            let shortRoll = restUsage + Number(document.getElementById("ext_sisa_kain").value) - restQty;
            let usage = sambungan * Number(document.getElementById("ext_lembar_sambungan").value);
            let unitLabel = document.getElementById("ext_unit").value == "KGM" ? "KG" : "M";
            let hasRoll = document.getElementById("ext_unit").value != "";

            document.getElementById("ext_rest_qty").value = hasRoll ? Number(restQty).round(2) : "";
            document.getElementById("ext_short_roll").value = hasRoll ? Number(shortRoll).round(2) : "";
            document.getElementById("ext_summary_qty").value = hasRoll ? Number(extQty).round(2) + " " + unitLabel : "-";
            document.getElementById("ext_usage").value = hasRoll ? Number(usage).round(2) + " " + unitLabel : "-";

            document.getElementById("ext_rest_qty_item").classList.toggle("is-negative", hasRoll && restQty < 0);
            document.getElementById("ext_short_roll_item").classList.toggle("is-negative", hasRoll && shortRoll < 0);

            document.querySelectorAll(".ext-unit-suffix").forEach((element) => {
                element.innerText = hasRoll ? unitLabel : "";
            });
        }

        function fetchExtScan() {
            let extIdRoll = document.getElementById("ext_id_roll").value;

            if (!extIdRoll || extIdRoll == '-') {
                return;
            }

            if (extIdRoll == document.getElementById("current_id_roll").value) {
                return Swal.fire({
                    icon: 'info',
                    title: 'Info',
                    text: 'Roll sambungan harus berbeda dengan roll pertama.',
                    confirmButtonText: 'Oke',
                });
            }

            document.getElementById("loading").classList.remove("d-none");

            $.ajax({
                url: '{{ route('get-scanned-form-cut-input') }}/' + extIdRoll,
                type: 'get',
                dataType: 'json',
                data: {
                    "at_time": extAtTime()
                },
                success: function(res) {
                    document.getElementById("loading").classList.add("d-none");

                    // unit roll sambungan harus sesuai dengan roll yang disambung
                    let compareUnit = document.getElementById("current_unit").value || formRollUnit(document.getElementById("current_id").value);
                    let unitMessage = res && res.qty > 0 ? rollUnitMismatchMessage(extIdRoll, res.unit, compareUnit) : null;

                    if (unitMessage) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Unit roll tidak sesuai',
                            html: unitMessage,
                            confirmButtonText: 'Mengerti',
                        });

                        document.getElementById("ext_id_roll").value = "";
                    } else if (res && res.qty > 0) {
                        document.getElementById("ext_id_item").value = res.id_item ?? "";
                        document.getElementById("ext_detail_item").value = res.detail_item ?? "";
                        document.getElementById("ext_lot").value = res.lot ?? "";
                        document.getElementById("ext_roll").value = res.roll ?? "";
                        document.getElementById("ext_roll_buyer").value = res.roll_buyer ?? "";
                        document.getElementById("ext_qty_real").value = res.qty;
                        document.getElementById("ext_unit").value = res.unit ?? "";
                        document.getElementById("ext_qty").value = rollQtyConversion(res.qty, res.unit);
                        document.getElementById("ext_berat_amparan_col").classList.toggle("d-none", !(res.unit == "KGM" || res.unit == "KG"));

                        calculateExt();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            html: res && res.message ? res.message : 'Roll tidak tersedia atau sudah habis.',
                            confirmButtonText: 'Oke',
                        });
                    }
                },
                error: function(jqXHR) {
                    document.getElementById("loading").classList.add("d-none");

                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: ajaxErrorMessage(jqXHR, 'Roll tidak tersedia atau sudah habis.'),
                        confirmButtonText: 'Oke',
                    });
                }
            });
        }

        document.getElementById("ext_id_roll").addEventListener("keyup", (event) => {
            if (event.keyCode === 13) {
                fetchExtScan();
            }
        });

        document.querySelectorAll(".ext-calc").forEach((element) => {
            element.addEventListener("input", calculateExt);
        });

        ["current_sisa_gelaran", "current_berat_amparan"].forEach((id) => {
            document.getElementById(id).addEventListener("input", toggleExtSection);
        });

        async function startAddRoll() {
            let positions = new Map();
            positions.set("_start", "Di awal (sebelum roll pertama)");
            $(summaryItemTableTbody).children('tr.roll-head').each(function() {
                positions.set(this.dataset.id, "Setelah " + this.dataset.label);
            });

            let lastHead = $(summaryItemTableTbody).children('tr.roll-head').last();

            const result = await Swal.fire({
                icon: 'question',
                title: 'Tambah Roll',
                text: 'Sisipkan roll baru di posisi:',
                input: 'select',
                inputOptions: positions,
                inputValue: lastHead.length > 0 ? lastHead.data('id') : '_start',
                showCancelButton: true,
                confirmButtonText: 'Lanjut',
                cancelButtonText: 'Batal',
            });

            if (!result.isConfirmed) {
                return;
            }

            let insertAfter = result.value;

            document.getElementById("loading").classList.remove("d-none");

            $.ajax({
                url: '{{ route('get-insert-time-spreading-roll') }}',
                type: 'get',
                dataType: 'json',
                data: {
                    id: document.getElementById("id").value,
                    insert_after: insertAfter,
                },
                success: function(res) {
                    document.getElementById("loading").classList.add("d-none");

                    if (!res || res.status != 200) {
                        Swal.fire({
                            icon: res && res.title ? 'warning' : 'info',
                            title: res && res.title ? res.title : 'Info',
                            html: res ? res.message : 'Terjadi Kesalahan.',
                            confirmButtonText: 'Oke',
                        });

                        return;
                    }

                    clearSpreadingForm();

                    // lepas referensi roll yang sedang diedit
                    document.getElementById("current_id").value = "";
                    document.getElementById("current_qty_ori").value = "";
                    document.getElementById("current_unit_ori").value = "";
                    document.getElementById("current_sisa_kain_ori").value = "";

                    for (let i = 0; i < summaryItemTableTbody.children.length; i++) {
                        summaryItemTableTbody.children[i].classList.remove('selected');
                    }

                    setAddRollMode(true, insertAfter, res.insert_at, positions.get(insertAfter).replace(/^Setelah /, 'setelah ').replace(/^Di awal/, 'di awal'));

                    // default group mengikuti roll sebelumnya
                    let beforeData = summaryData ? summaryData.find((item) => item.id == insertAfter) : null;
                    if (beforeData && beforeData.group_roll) {
                        document.getElementById("current_group").value = beforeData.group_roll;
                    }

                    openItemSpreading();
                    applyAddRollLocks();

                    location.href = '#spreading-form-card';
                    document.getElementById("current_id_roll").focus();
                },
                error: function(jqXHR) {
                    document.getElementById("loading").classList.add("d-none");

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: ajaxErrorMessage(jqXHR, 'Gagal menentukan posisi roll.'),
                        confirmButtonText: 'Oke',
                    });
                }
            });
        }

        function cancelAddRoll() {
            clearSpreadingForm();
        }

        async function storeAddedRoll() {
            let idRoll = document.getElementById("current_id_roll").value;
            if (!idRoll || idRoll == '-' || !document.getElementById("current_unit").value) {
                return Swal.fire({
                    icon: 'info',
                    title: 'Info',
                    text: 'Harap isi ID Roll lalu klik "Get" terlebih dahulu.',
                    confirmButtonText: 'Oke',
                });
            }

            let extIdRoll = document.getElementById("ext_id_roll").value;
            let extInvalid = validateExtSection();
            if (extInvalid) {
                return extInvalid;
            }

            let proceed = await checkStockerForm();
            if (!proceed) {
                return;
            }

            const confirmResult = await Swal.fire({
                icon: 'question',
                title: 'Tambah roll?',
                html: 'Roll <b>' + idRoll + '</b>' + (hasAddRollExtension() ? ' + roll sambungan <b>' + extIdRoll + '</b>' : '') +
                    ' akan disisipkan ' + document.getElementById("add-roll-position-text").innerText +
                    '.<br><br><small>Qty roll (dan pemakaian roll ini di form lain setelahnya) akan dihitung ulang sesuai urutan waktu.</small>',
                showCancelButton: true,
                confirmButtonText: 'Simpan',
                cancelButtonText: 'Batal',
            });

            if (!confirmResult.isConfirmed) {
                return;
            }

            document.getElementById("loading").classList.remove("d-none");

            let dataObj = {
                "id": $("#id").val(),
                "insert_after": document.getElementById("add_roll_insert_after").value,
            };

            new FormData(document.getElementById("spreading-form")).forEach((value, key) => dataObj[key] = value);

            $.ajax({
                url: '{{ route('store-added-spreading-roll') }}',
                type: 'post',
                dataType: 'json',
                data: dataObj,
                success: async function(res) {
                    if (res && res.status == 200) {
                        await clearSpreadingForm();

                        await getSummary(true);

                        await finishProcess();
                    } else {
                        document.getElementById("loading").classList.add("d-none");

                        Swal.fire({
                            icon: res && res.title ? 'warning' : 'info',
                            title: res && res.title ? res.title : 'Info',
                            html: res ? res.message : 'Terjadi Kesalahan.',
                            confirmButtonText: 'Oke',
                        });
                    }
                },
                error: function(jqXHR) {
                    document.getElementById("loading").classList.add("d-none");

                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        html: ajaxErrorMessage(jqXHR, 'Gagal menambah roll.'),
                        confirmButtonText: 'Oke',
                    });
                }
            });
        }

        function appendSummaryItem(data) {
            totalLembar += Number(data.lembar_gelaran);
            latestStatus != 'extension complete' ? totalQtyFabric += Number(data.qty) : '';
            latestUnit = data.unit;
            latestBerat = data.berat_amparan;

            let tr = document.createElement('tr');
            let td1 = document.createElement('td');
            let td2 = document.createElement('td');
            let td3 = document.createElement('td');
            let td4 = document.createElement('td');
            let td5 = document.createElement('td');
            let td6 = document.createElement('td');
            let td7 = document.createElement('td');
            let td8 = document.createElement('td');
            let td9 = document.createElement('td');
            let td10 = document.createElement('td');
            let td11 = document.createElement('td');
            let td12 = document.createElement('td');
            let td13 = document.createElement('td');
            let td14 = document.createElement('td');
            let td15 = document.createElement('td');
            let td16 = document.createElement('td');
            let td17 = document.createElement('td');
            let td18 = document.createElement('td');
            let td19 = document.createElement('td');
            let td20 = document.createElement('td');
            let td21 = document.createElement('td');
            let td22 = document.createElement('td');
            let td23 = document.createElement('td');
            let td24 = document.createElement('td');
            let td25 = document.createElement('td');

            // Drag handle (baris lanjutan sambungan ikut baris kepalanya)
            let tdHandle = document.createElement('td');
            tdHandle.classList.add('text-center', 'align-middle');
            tr.dataset.id = data.id;
            let isRollContinuation = latestStatus == 'need extension' || (latestStatus == 'extension complete' && data.id_roll && data.id_roll == latestRollId);
            if (!isRollContinuation) {
                currentRollHeadId = data.id;
                tr.classList.add('roll-head');
                tr.dataset.label = (totalScannedItem + 1) + '. ' + (data.id_roll ? data.id_roll : '-') + ' (Group ' + (data.group_roll ? data.group_roll : '-') + ')';
                tdHandle.innerHTML = '<i class="fa-solid fa-grip-vertical roll-drag-handle" title="Drag untuk atur urutan roll"></i>';
            } else {
                tr.classList.add('roll-continuation');
                tdHandle.innerHTML = '<i class="fa-solid fa-link roll-link-icon" title="Bagian dari sambungan, ikut pindah bersama roll di atasnya"></i>';
            }
            tr.dataset.head = currentRollHeadId;
            tdHandle.onclick = (e) => e.stopPropagation();
            tr.appendChild(tdHandle);

            if (latestStatus != 'need extension') {
                td1.innerHTML = (latestStatus != 'need extension' ? totalScannedItem + 1 : '');
                td2.innerHTML = data.group_roll ? data.group_roll : '-';
                td3.innerHTML = data.group_stocker ? data.group_stocker : '-';
                td4.innerHTML = data.id_roll ? data.id_roll : '-';
                td5.innerHTML = data.id_item ? data.id_item : '-';
                td6.innerHTML = data.lot ? data.lot : '-';
                td7.innerHTML = data.roll_buyer ? data.roll_buyer : '-';
                td8.innerHTML = data.qty_awal > data.qty ? data.qty_awal : (latestStatus != 'extension complete' ? data.qty : latestQty);
                td9.innerHTML = (latestStatus != 'extension complete' ? data.qty : latestQty);
                td10.innerHTML = data.unit ? data.unit : '-';
                td11.innerHTML = data.sisa_gelaran ? data.sisa_gelaran : 0;
                td12.innerHTML = (latestStatus != 'extension complete' ? 0 : (latestSambungan ? latestSambungan : 0));
                td13.innerHTML = (latestStatus != 'extension complete' ? (data.sambungan_roll ? data.sambungan_roll : 0) : ((data.sambungan_roll ? data.sambungan_roll : 0)+latestSambunganRoll));
                td14.innerHTML =  (latestStatus != 'extension complete' ? Number(data.est_amparan).round(2) : (Number(latestEst).round(2) ? Number(latestEst).round(2) : Number(data.est_amparan).round(2)));
                td15.innerHTML = data.lembar_gelaran ? data.lembar_gelaran : '-';
                td16.innerHTML = data.average_time ? data.average_time : 0;
                td17.innerHTML = (latestStatus != 'extension complete' ? (data.kepala_kain ? data.kepala_kain : 0) : Number(data.kepala_kain ? data.kepala_kain : 0)+Number(latestKepalaKain));
                td18.innerHTML = (latestStatus != 'extension complete' ? (data.sisa_tidak_bisa ? data.sisa_tidak_bisa : 0) : Number(data.sisa_tidak_bisa ? data.sisa_tidak_bisa : 0)+Number(latestSisaTidakBisa));
                td19.innerHTML = (latestStatus != 'extension complete' ? (data.reject ? data.reject : 0) : Number(data.reject ? data.reject : 0)+Number(latestReject));
                td20.innerHTML = (latestStatus != 'extension complete' ? (data.piping ? data.piping : 0) : Number(data.piping ? data.piping : 0)+Number(latestPiping));
                td21.innerHTML = (latestStatus != 'extension complete' ? (data.sisa_kain ? data.sisa_kain : 0) : Number(data.sisa_kain ? data.sisa_kain : 0)+Number(latestSisaKain));
                td22.innerHTML = (latestStatus != 'extension complete' ? Number(data.pemakaian_lembar ? data.pemakaian_lembar : 0).round(2) : Number(Number(data.pemakaian_lembar ? data.pemakaian_lembar : 0)+Number(latestPemakaianLembar)).round(2));
                td23.innerHTML = (latestStatus != 'extension complete' ? Number(data.total_pemakaian_roll ? data.total_pemakaian_roll : 0).round(2) : Number(Number(data.total_pemakaian_roll ? data.total_pemakaian_roll : 0)+Number(latestTotalPemakaian)).round(2));
                td24.innerHTML = Number((
                    latestStatus != 'extension complete'
                        ? ((Number(data.total_pemakaian_roll) || 0) + (Number(data.sisa_kain) || 0) - (Number(data.qty) || 0))
                        : ((Number(data.total_pemakaian_roll) || 0) + (Number(latestTotalPemakaian) || 0) + (Number(data.sisa_kain) || 0) - (Number(latestQty) || 0))
                ).toFixed(2)) || 0;
                td25.innerHTML = Number.isFinite(
                        ((latestStatus != 'extension complete'
                            ? ((Number(data.total_pemakaian_roll) || 0) + (Number(data.sisa_kain) || 0) - (Number(data.qty) || 0)) /
                                (Number(data.qty) || 0)
                            : ((Number(data.total_pemakaian_roll) || 0) + (Number(latestTotalPemakaian) || 0) + (Number(data.sisa_kain) || 0) - (Number(latestQty) || 0)) /
                                (Number(latestQty) || 0)
                        ) * 100)
                    )
                        ? (((latestStatus != 'extension complete'
                            ? ((Number(data.total_pemakaian_roll) || 0) + (Number(data.sisa_kain) || 0) - (Number(data.qty) || 0)) /
                                (Number(data.qty) || 0)
                            : ((Number(data.total_pemakaian_roll) || 0) + (Number(latestTotalPemakaian) || 0) + (Number(data.sisa_kain) || 0) - (Number(latestQty) || 0)) /
                                (Number(latestQty) || 0)
                        ) * 100).toFixed(2))
                        : "0.00";
            } else {
                td1.innerHTML = '';
                td15.innerHTML = data.lembar_gelaran ? data.lembar_gelaran : '';
            }

            tr.appendChild(td1);
            tr.appendChild(td2);
            tr.appendChild(td3);
            tr.appendChild(td4);
            tr.appendChild(td5);
            tr.appendChild(td6);
            tr.appendChild(td7);
            tr.appendChild(td8);
            tr.appendChild(td9);
            tr.appendChild(td10);
            tr.appendChild(td11);
            tr.appendChild(td12);
            tr.appendChild(td13);
            tr.appendChild(td14);
            tr.appendChild(td15);
            tr.appendChild(td16);
            tr.appendChild(td17);
            tr.appendChild(td18);
            tr.appendChild(td19);
            tr.appendChild(td20);
            tr.appendChild(td21);
            tr.appendChild(td22);
            tr.appendChild(td23);
            tr.appendChild(td24);
            tr.appendChild(td25);

            if (latestUnit == "KGM" || latestUnit == "KG") {
                let td26 = document.createElement('td');
                td26.innerHTML = latestStatus != 'need extension' ? (data.berat_amparan ? data.berat_amparan : '-') : '';
                tr.appendChild(td26);

                document.getElementById("th-berat-amparan").classList.remove("d-none");
                document.getElementById("total-berat-amparan").classList.remove("d-none");
            } else {

                document.getElementById("th-berat-amparan").classList.add("d-none");
                document.getElementById("total-berat-amparan").classList.add("d-none");
            }

            summaryItemTableTbody.appendChild(tr);

            totalRow++;
            latestStatus != 'need extension' ? totalScannedItem++ : '';

            totalSisaGelaran += Number(data.sisa_gelaran);
            totalSambungan += Number(data.sambungan);
            totalSambunganRoll += Number(data.sambungan_roll);
            latestStatus != 'extension complete' ? totalEstAmparan += Number(data.est_amparan) : '';
            totalAverageTime += (Number(data.average_time.slice(0, 2)) * 60) + Number(data.average_time.slice(3, 5));
            totalKepalaKain += Number(data.kepala_kain);
            totalSisaTidakBisa += Number(data.sisa_tidak_bisa);
            totalReject += Number(data.reject);
            totalPiping += Number(data.piping);
            totalSisaKain += Number(data.sisa_kain);
            totalPemakaianLembar += Number(data.pemakaian_lembar);
            totalTotalPemakaian += Number(data.total_pemakaian_roll);
            latestStatus != 'extension complete' ? totalBeratAmparan += Number(data.berat_amparan) : '';
            // console.log(Number(Number((data.total_pemakaian_roll ? data.total_pemakaian_roll : 0) + ( data.sisa_kain ? data.sisa_kain : 0 )) - ( data.qty ? data.qty : 0 )));
            Number(data.short_roll) < 0 ? totalShortRoll += (latestStatus != 'extension complete' ? Number(Number((data.total_pemakaian_roll ? data.total_pemakaian_roll : 0) + ( data.sisa_kain ? data.sisa_kain : 0 )) - ( data.qty ? data.qty : 0 )) : Number(Number((data.total_pemakaian_roll ? data.total_pemakaian_roll : 0) + (latestTotalPemakaian ? latestTotalPemakaian : 0) + ( data.sisa_kain ? data.sisa_kain : 0 )) - ( latestQty ? latestQty : 0 ))) : "";
            Number(data.short_roll) < 0 ? totalShortRollPercentage += (latestStatus != 'extension complete' ? Number((Number((data.total_pemakaian_roll ? data.total_pemakaian_roll : 0) + ( data.sisa_kain ? data.sisa_kain : 0 )) - ( data.qty ? data.qty : 0 )) / ( data.qty ? data.qty : 0 ) * 100 ) : Number((Number((data.total_pemakaian_roll ? data.total_pemakaian_roll : 0 )  + (latestTotalPemakaian ? latestTotalPemakaian : 0) + ( data.sisa_kain ? data.sisa_kain : 0 )) - ( latestQty ? latestQty : 0 )) / ( latestQty ? latestQty : 0 ) * 100)) : 0;

            let averageTotalAverageTime = totalAverageTime / totalRow;
            let averageTotalAverageTimeMinute = averageTotalAverageTime.round(0) >= 60 ? pad((averageTotalAverageTime.round(0) / 60).round(0)) : pad(0);
            let averageTotalAverageTimeSecond = averageTotalAverageTime.round(0) >= 60 ? pad((averageTotalAverageTime.round(0) % 60).round(0)) : pad(averageTotalAverageTime.round(0));

            document.getElementById("total-qty").innerText = Number(totalQtyFabric).round(2);
            document.getElementById("total-unit").innerText = latestUnit;
            document.getElementById("total-sisa-gelaran").innerText = Number(totalSisaGelaran).round(2);
            document.getElementById("total-sambungan").innerText = Number(totalSambungan).round(2);
            document.getElementById("total-sambungan-roll").innerText = Number(totalSambunganRoll).round(2);
            document.getElementById("total-est-amparan").innerText = Number(totalEstAmparan).round(2).round(2);
            document.getElementById("total-lembar").innerText = Number(totalLembar).round(2);
            document.getElementById("total-average-time").innerText = (averageTotalAverageTimeMinute + ":" +averageTotalAverageTimeSecond);
            document.getElementById("total-kepala-kain").innerText = Number(totalKepalaKain).round(2);
            document.getElementById("total-sisa-tidak-bisa").innerText = Number(totalSisaTidakBisa).round(2);
            document.getElementById("total-reject").innerText = Number(totalReject).round(2);
            document.getElementById("total-sisa-kain").innerText = Number(totalSisaKain).round(2);
            document.getElementById("total-pemakaian-lembar").innerText = Number(totalPemakaianLembar).round(2);
            document.getElementById("total-total-pemakaian").innerText = Number(totalTotalPemakaian).round(2);
            document.getElementById("total-short-roll").innerText = Number(totalShortRoll).round(2);
            document.getElementById("total-short-roll-percentage").innerText = Number(totalShortRollPercentage).round(2);
            document.getElementById("total-piping").innerText = Number(totalPiping).round(2);
            document.getElementById("total-berat-amparan").innerText = Number(totalBeratAmparan).round(3);

            calculateConsActualGelaran(latestUnit, totalQtyFabric, totalKepalaKain, totalSisaTidakBisa, totalReject, totalSisaKain, totalPiping, totalShortRoll, totalLembar, totalTotalPemakaian);

            latestKepalaKain = Number(data.kepala_kain);
            latestSisaTidakBisa = Number(data.sisa_tidak_bisa);
            latestReject = Number(data.reject);
            latestPiping = Number(data.piping);
            latestSambungan = Number(data.sambungan);
            latestSambunganRoll = Number(data.sambungan_roll);
            latestLembarSambunganRoll = Number(data.lembar_sambungan);
            latestPemakaianLembar = Number(data.pemakaian_lembar);
            latestTotalPemakaian = Number(data.total_pemakaian_roll);
            latestShortRoll = Number(data.short_roll);

            let currentLatestStatus = latestStatus;
            latestStatus = data.status;
            latestRollId = data.id_roll;
            latestQty = Number(data.qty);
            latestUnit = data.unit;
            latestEst = data.est_amparan ? data.est_amparan : 0;

            tr.onclick = async function() {
                clearSpreadingForm();

                for (let i = 0; i < summaryItemTableTbody.children.length; i++) {
                    summaryItemTableTbody.children[i].classList.remove('selected');
                }

                this.classList.add('selected');

                setSpreadingForm(data, false, currentLatestStatus);

                location.href = '#spreading-form-card';
            };
        }

        function resetSummary() {
            totalSisaGelaran = 0;
            totalSambungan = 0;
            totalEstAmparan = 0;
            totalAverageTime = 0;
            totalKepalaKain = 0;
            totalSisaTidakBisa = 0;
            totalReject = 0;
            totalSisaKain = 0;
            totalTotalPemakaian = 0;
            totalShortRoll = 0;
            totalLembar = 0;
            totalPiping = 0;
            totalQtyFabric = 0;
            latestUnit = "";
            summaryItemTableTbody.innerHTML = '';
        }

        // -Set Spreading Form-
        function setSpreadingForm(data, item, latestStatus = null, noBerat = false) {
            openItemSpreading(latestStatus);

            // spreading form data set
            let convertedQty = rollQtyConversion(data.qty, data.unit);

            if (!item) {
                data.id ? document.getElementById("current_id").value = data.id : '';
                data.id_roll ? document.getElementById("current_id_roll_ori").value = data.id_roll : '';
                data.qty ? document.getElementById("current_qty_ori").value = data.qty : '';
                data.unit ? document.getElementById("current_unit_ori").value = data.unit : '';
                data.sisa_kain ? document.getElementById("current_sisa_kain_ori").value = data.sisa_kain : '';
                document.getElementById("current_created_at_ori").value = data.created_at ?? '';

                // apakah roll ini sudah punya roll sambungan (baris berikutnya 'extension complete')
                let summaryIndex = summaryData ? summaryData.findIndex((item) => item.id == data.id) : -1;
                let nextSummary = summaryIndex > -1 ? summaryData[summaryIndex + 1] : null;
                let hasExtension = data.status == 'need extension' && nextSummary && nextSummary.status == 'extension complete';
                document.getElementById("current_has_extension").value = hasExtension ? '1' : '';
                document.getElementById("current_ext_child_roll").value = hasExtension ? (nextSummary.id_roll ?? '-') : '';

                // apakah roll ini bagian dari roll sambungan ('extension complete' / sisa roll sambungan)
                let prevSummary = summaryIndex > 0 ? summaryData[summaryIndex - 1] : null;
                let isExtensionChild = data.status == 'extension complete' ||
                    (prevSummary && prevSummary.status == 'extension complete' && data.id_roll && prevSummary.id_roll == data.id_roll);
                let parentRoll = '';
                if (isExtensionChild) {
                    for (let i = summaryIndex - 1; i >= 0; i--) {
                        if (summaryData[i].status == 'need extension') {
                            parentRoll = summaryData[i].id_roll ?? '-';
                            break;
                        }
                    }
                }
                document.getElementById("current_ext_parent_roll").value = isExtensionChild ? (parentRoll || '-') : '';
            }
            data.id_roll ? document.getElementById("current_id_roll").value = data.id_roll : '';
            data.group_roll ? document.getElementById("current_group").value = data.group_roll : '';
            data.group_stocker ? document.getElementById("current_group_stocker").value = data.group_stocker : '';
            data.id_item ? document.getElementById("current_id_item").value = data.id_item : '';
            data.detail_item ? document.getElementById("current_detail_item").value = data.detail_item : '';
            data.lot ? document.getElementById("current_lot").value = data.lot : '';
            data.roll ? document.getElementById("current_roll").value = data.roll : '';
            data.roll_buyer ? document.getElementById("current_roll_buyer").value = data.roll_buyer : '';
            data.qty ? document.getElementById("current_qty").value = convertedQty : '';
            data.qty ? document.getElementById("current_qty_real").value = data.qty : '';
            data.unit ? document.getElementById("current_unit").value = data.unit : '';
            data.unit ? document.getElementById("current_sisa_gelaran_unit").value = (data.unit != "KGM" ? "METER" : "KGM") : '';
            data.unit ? document.getElementById("current_sambungan_unit").value = (data.unit != "KGM" ? "METER" : "KGM") : '';
            data.sisa_gelaran ? document.getElementById("current_sisa_gelaran").value = data.sisa_gelaran : '';
            data.sambungan ? document.getElementById("current_sambungan").value = data.sambungan : '';
            data.sambungan_roll ? document.getElementById("current_total_sambungan_roll").value = data.sambungan_roll : '';
            data.est_amparan ? document.getElementById("current_est_amparan").value = data.est_amparan : '';
            data.lembar_gelaran ? document.getElementById("lembar_gelaran").value = data.lembar_gelaran : '';
            data.lembar_gelaran ? document.getElementById("current_lembar_gelaran").value = data.lembar_gelaran : '';
            data.kepala_kain ? document.getElementById("current_kepala_kain").value = data.kepala_kain : '';
            data.sisa_tidak_bisa ? document.getElementById("current_sisa_tidak_bisa").value = data.sisa_tidak_bisa : '';
            data.reject ? document.getElementById("current_reject").value = data.reject : '';
            data.sisa_kain ? document.getElementById("current_sisa_kain").value = data.sisa_kain : '';
            data.pemakaian_lembar ? document.getElementById("current_pemakaian_lembar").value = data.pemakaian_lembar : '';
            data.total_pemakaian_roll ? document.getElementById("current_total_pemakaian_roll").value = data.total_pemakaian_roll : '';
            data.short_roll ? document.getElementById("current_short_roll").value = data.short_roll : '';
            data.piping ? document.getElementById("current_piping").value = data.piping : '';
            document.getElementById("current_latest_status").value = latestStatus;
            if (!noBerat) {
                data.berat_amparan ? document.getElementById("current_berat_amparan").value = data.berat_amparan : '';
            }

            if (data.unit == "KGM" || data.unit == "KG") {
                document.getElementById("berat_amparan").classList.remove("d-none");
                document.getElementById("th-berat-amparan").classList.remove("d-none");
                document.getElementById("total-berat-amparan").classList.remove("d-none");
            } else if (data.unit == "METER" || data.unit == "YARD" || data.unit == "YRD") {
                document.getElementById("berat_amparan").classList.add("d-none");
                document.getElementById("th-berat-amparan").classList.add("d-none");
                document.getElementById("total-berat-amparan").classList.add("d-none");
            }

            // simplified unit name
            let unitSimplified = data.unit != "KGM" ? "M" : "KG";

            let inputGroupUnit = document.getElementsByClassName("input-group-unit");

            for (var i = 0; i < inputGroupUnit.length; i++) {
                inputGroupUnit[i].innerText = unitSimplified;
            }

            // updating est ampar & updating short roll & ply progress bar
            calculateEstAmpar();

            // updating consumption
            calculatePemakaianLembar();
            calculateTotalPemakaian();
            calculateShortRoll();
            updatePlyProgress();

            // lock extension
            checkLockExtension();

            // lock qty input
            checkLockQtyInput(latestStatus);

            // tambah roll: tanpa sisa gelaran / sambungan
            if (isAddRollMode()) {
                applyAddRollLocks();
            }

            // roll sambungan (tambah roll / edit menjadi need extension)
            toggleExtSection();
        }

        // -Clear Spreading Form-
        function clearSpreadingForm() {
            lockItemSpreading();

            document.getElementById("current_created_at_ori").value = "";
            document.getElementById("current_has_extension").value = "";
            document.getElementById("current_ext_child_roll").value = "";
            document.getElementById("current_ext_parent_roll").value = "";

            setAddRollMode(false);

            document.getElementById("current_group").value = "";
            document.getElementById("current_group_stocker").value = "";
            document.getElementById("current_id_roll_ori").value = "";
            document.getElementById("current_id_roll").value = "";
            document.getElementById("current_id_item").value = "";
            document.getElementById("current_lot").value = "";
            document.getElementById("current_roll").value = "";
            document.getElementById("current_roll_buyer").value = "";
            document.getElementById("current_qty").value = "";
            document.getElementById("current_qty_real").value = "";
            document.getElementById("current_unit").value = "";
            document.getElementById("current_sisa_gelaran").value = 0;
            document.getElementById("current_sisa_gelaran_unit").value = "";
            document.getElementById("current_sambungan").value = 0;
            document.getElementById("current_sambungan_unit").value = "";
            document.getElementById("current_total_sambungan_roll").value = 0;
            document.getElementById("current_est_amparan").value = 0;
            document.getElementById("current_lembar_gelaran").value = 0;
            document.getElementById("current_kepala_kain").value = 0;
            document.getElementById("current_sisa_tidak_bisa").value = 0;
            document.getElementById("current_reject").value = 0;
            document.getElementById("current_sisa_kain").value = "";
            document.getElementById("current_pemakaian_lembar").value = 0;
            document.getElementById("current_total_pemakaian_roll").value = 0;
            document.getElementById("current_short_roll").value = 0;
            document.getElementById("current_short_roll_percentage").value = 0;
            document.getElementById("current_piping").value = 0;

            let inputGroupUnit = document.getElementsByClassName("input-group-unit");

            for (var i = 0; i < inputGroupUnit.length; i++) {
                inputGroupUnit[i].innerText = "";
            }

            toggleExtSection();
        }

        // -Calculate P. Actual + Comma Actual-
        function pActualCommaActual(pActualVar, unitPActualVar, commaActualVar) {
            let pActualFinal = 0;

            if (unitPActualVar == "YARD" || unitPActualVar == "YRD") {
                let commaMeter = commaActualVar / 36;

                pActualFinal = (pActualVar + commaMeter);
            } else if (unitPActualVar == "METER") {
                let commaMeter = commaActualVar / 100;

                pActualFinal = (pActualVar + commaMeter);
            }

            return pActualFinal;
        }

        // -Convert P. Actual-
        function pActualConversion(pActualVar, unitPActualVar, commaActualVar, lActualVar, gramasiVar, unitQtyVar) {
            let pActualConverted = 0;

            if (unitQtyVar == unitPActualVar) {
                if (unitPActualVar == "YARD" || unitPActualVar == "YRD") {
                    pActualConverted = pActualVar + (commaActualVar / 36);
                } else if (unitPActualVar == "METER") {
                    pActualConverted = pActualVar + (commaActualVar / 100);
                }
            } else {
                // YARD
                if (unitPActualVar == "YARD" || unitPActualVar == "YRD") {
                    let pActualInch = ((pActualVar * 36 / 1) + commaActualVar)

                    if (unitQtyVar == "METER") {
                        pActualConverted = pActualInch * 0.0254;
                    } else if (unitQtyVar == "KGM") {
                        let gramasiInch = gramasiVar / 1550;

                        pActualConverted = ((gramasiInch * (pActualInch * lActualVar)) / 1000);
                    } else {
                        pActualConverted = pActualVar + (commaActualVar / 36);
                    }

                    // METER
                } else if (unitPActualVar == "METER") {
                    let pActualInch = ((pActualVar * 39.3701) + (commaActualVar / 2.54));
                    let lActualInch = lActualVar / 2.54;

                    if (unitQtyVar == "YARD" || unitQtyVar == "YRD") {
                        pActualConverted = pActualInch / 36;
                    } else if (unitQtyVar == "KGM") {
                        let gramasiInch = gramasiVar / 1550;

                        pActualConverted = ((gramasiInch * (pActualInch * lActualInch)) / 1000);
                    } else {
                        pActualConverted = pActualVar + (commaActualVar / 100);
                    }
                }
            }

            return pActualConverted;
        }

        // -Convert Roll Qty Actual-
        function rollQtyConversion(rollQtyVar, unitQtyVar, gramasi = 0, pActual = 0, lActual = 0, commaActual = 0) {
            let rollQtyConverted = 0;
            let gramasiVar = gramasi > 0 ? Number(gramasi) : Number(document.getElementById("gramasi").value);
            let pActualVar = pActual > 0 ? Number(pActual) : Number(document.getElementById("p_act").value);
            let lActualVar = lActual > 0 ? Number(lActual) : Number(document.getElementById("l_act").value);
            let commaActualVar = commaActual > 0 ? Number(commaActual) : Number(document.getElementById("comma_act").value);

            if (rollQtyVar && unitQtyVar) {
                if (unitQtyVar == "YARD" || unitQtyVar == "YRD") {
                    // YARD
                    rollQtyConverted = rollQtyVar * 0.9144;

                } else if (unitQtyVar == "KGM") {
                    // KGM
                    let gramasiConverted = gramasiVar / 1000;
                    let lActualConverted = lActualVar / 100;

                    rollQtyConverted = rollQtyVar / (gramasiConverted * lActualConverted);

                } else {
                    // METER

                    rollQtyConverted = rollQtyVar;
                }

                return Number(rollQtyConverted).round(2);
            }

            return null;
        }

        function setRollQtyConversion(rollQty = 0, unitQty) {
            let rollQtyVar = rollQty > 0 ? Number(rollQty) : Number(document.getElementById("current_qty_real").value);
            let unitQtyVar = unitQty ? unitQty : document.getElementById("current_unit").value;

            document.getElementById("current_qty").value = rollQtyConversion(rollQtyVar, unitQtyVar);
        }

        function conversion(qty, unit, unitBefore) {

            let gramasiVar = Number(document.getElementById("gramasi").value);
            let pActualVar = Number(document.getElementById("p_act").value);
            let lActualVar = Number(document.getElementById("l_act").value);
            let commaActualVar = Number(document.getElementById("comma_act").value);

            let qtyConverted = 0;

            if (qty && unit && unitBefore) {
                if (unit == unitBefore) {
                    qtyConverted = qty;
                } else {
                    if (unitBefore == "KGM" && unit == "METER") {
                        // KGM
                        let gramasiConverted = gramasiVar / 1000;
                        let lActualConverted = lActualVar / 100;

                        qtyConverted = qty / (gramasiConverted * lActualConverted);
                    } else if (unitBefore == "METER" && unit == "KGM") {
                        let gramasiInch = gramasiVar / 1550;
                        let qtyInch = qty * 39.3701;
                        let lActualInch = lActualVar / 2.54;

                        qtyConverted = (gramasiInch * (qtyInch * lActualInch)) / 1000;
                    }
                }

                return Number(qtyConverted);
            }

            return null;
        }

        // -Calculate Cons Ampar-
        function calculateConsAmpar() {
            let pActualVar = Number(document.getElementById("p_act").value);
            let unitPActualVar = document.getElementById("unit_p_act").value;
            let commaActualVar = Number(document.getElementById("comma_act").value);
            let lActualVar = Number(document.getElementById("l_act").value);
            let gramasiVar = Number(document.getElementById("gramasi").value);
            let lActualMeter = lActualVar / 100;

            let pActualFinal = pActualCommaActual(pActualVar, unitPActualVar, commaActualVar);

            consAmpar = totalRatio > 0 ? (gramasiVar * pActualFinal * lActualMeter) / 1000 : 0;

            document.getElementById('cons_ampar').value = consAmpar;
        }

        // -Calculate Cons Act-
        function calculateConsAct() {
            let pActualVar = Number(document.getElementById("p_act").value);
            let unitPActualVar = document.getElementById("unit_p_act").value;
            let commaActualVar = Number(document.getElementById("comma_act").value);

            let consActual = 0;

            let pActualFinal = pActualCommaActual(pActualVar, unitPActualVar, commaActualVar);

            console.log(pActualFinal);

            // consActual = totalQtyCut > 0 ? pActualFinal / totalQtyCut : 0;
            consActual = totalQtyCut > 0 ? pActualFinal / totalRatio : 0;

            document.getElementById('cons_act').value = consActual.round(3);
        }

        // -Calculate Est. Piping-
        function calculateEstPipping(consPipping = 0) {
            let consPippingVar = consPipping;

            let estPipping = consPippingVar * totalQtyCut;

            document.getElementById('est_pipping').value = estPipping;
        }

        // -Calculate Est. Kain-
        function calculateEstKain(consMarker = 0) {
            let consMarkerVar = consMarker;

            let estKain = consMarkerVar * totalQtyCut

            document.getElementById('est_kain').value = estKain;
        }

        // -Calculate Est. Ampar-
        function calculateEstAmpar() {
            let qtyVar = Number(document.getElementById("current_qty").value);
            let pActualVar = Number(document.getElementById("p_act").value);
            let lActualVar = Number(document.getElementById("l_act").value);
            let commaActualVar = Number(document.getElementById("comma_act").value);
            let unitQtyVar = document.getElementById("current_unit").value;
            let unitPActualVar = document.getElementById("unit_p_act").value;
            let unitCommaActualVar = document.getElementById("unit_comma_act").value;
            let gramasiVar = Number(document.getElementById("gramasi").value);

            let pActualConverted = 0;

            if (unitQtyVar != "KGM") {
                pActualConverted = pActualCommaActual(pActualVar, unitPActualVar, commaActualVar);
            } else {
                qtyVar = Number(document.getElementById("current_qty_real").value);

                // pActualConverted = pActualConversion(pActualVar, unitPActualVar, commaActualVar, lActualVar, gramasiVar, unitQtyVar);
                pActualConverted = Number(document.getElementById("current_berat_amparan").value);
            }

            let estAmpar = pActualConverted > 0 ? qtyVar / pActualConverted : 0;

            document.getElementById("current_est_amparan").value = Number(estAmpar).round(2);
        }

        // -Calculate Pemakaian Lembar
        function calculatePemakaianLembar() {
            let lembarGelaranVar = Number(document.getElementById("current_lembar_gelaran").value);
            let pActualVar = Number(document.getElementById("p_act").value);
            let sisaGelaranVar = Number(document.getElementById("current_sisa_gelaran").value);
            let sambunganVar = Number(document.getElementById("current_sambungan").value);
            let sambunganRollVar = Number(document.getElementById("current_total_sambungan_roll").value);
            let qtyVar = Number(document.getElementById("current_qty").value);
            let unitQtyVar = document.getElementById("current_unit").value;
            let gramasiVar = Number(document.getElementById("gramasi").value);
            let unitPActualVar = document.getElementById("unit_p_act").value;
            let lActualVar = Number(document.getElementById("l_act").value);
            let commaActualVar = Number(document.getElementById("comma_act").value);

            let pActualConverted = 0;

            if (document.getElementById("current_sambungan").value != 0) {
                pActualConverted = document.getElementById("current_sambungan").value;
            } else {
                if (unitQtyVar != "KGM") {
                    pActualConverted = pActualCommaActual(pActualVar, unitPActualVar, commaActualVar);
                } else {
                    qtyVar = Number(document.getElementById("current_qty_real").value);

                    // pActualConverted = pActualConversion(pActualVar, unitPActualVar, commaActualVar, lActualVar, gramasiVar, unitQtyVar);
                    pActualConverted = Number(document.getElementById("current_berat_amparan").value);
                }
            }

            let totalPemakaian = ((pActualConverted * lembarGelaranVar) + sambunganRollVar + sisaGelaranVar);

            document.getElementById("current_pemakaian_lembar").value = totalPemakaian ? Number(totalPemakaian).round(2) : 0;
        }

        // -Calculate Total Pemakaian Roll-
        function calculateTotalPemakaian() {
            // Legacy
                // let lembarGelaranVar = Number(document.getElementById("current_lembar_gelaran").value);
                // let pActualVar = Number(document.getElementById("p_act").value);
                // let kepalaKainVar = Number(document.getElementById("current_kepala_kain").value);
                // let sisaTidakBisaVar = Number(document.getElementById("current_sisa_tidak_bisa").value);
                // let rejectVar = Number(document.getElementById("current_reject").value);
                // let lActualVar = Number(document.getElementById("l_act").value);
                // let gramasiVar = Number(document.getElementById("gramasi").value);
                // let unitQtyVar = document.getElementById("current_unit").value;
                // let unitPActualVar = document.getElementById("unit_p_act").value;
                // let commaActualVar = Number(document.getElementById("comma_act").value);

                // let pActualConverted = 0;

                // if (document.getElementById("status_sambungan").value == "extension") {
                //     pActualConverted = document.getElementById("current_sambungan").value;
                // } else {
                //     if (unitQtyVar != "KGM") {
                //         pActualConverted = pActualCommaActual(pActualVar, unitPActualVar, commaActualVar);
                //     } else {
                //         qtyVar = Number(document.getElementById("current_qty_real").value);

                //         // pActualConverted = pActualConversion(pActualVar, unitPActualVar, commaActualVar, lActualVar, gramasiVar, unitQtyVar);
                //         pActualConverted = Number(document.getElementById("current_berat_amparan").value);
                //     }
                // }

                // // let totalPemakaian = lembarGelaranVar * pActualConverted + kepalaKainVar + sisaTidakBisaVar + rejectVar;
                // let totalPemakaian = lembarGelaranVar * pActualConverted;

                // document.getElementById("current_total_pemakaian_roll").value = totalPemakaian;

            let lembarGelaranVar = Number(document.getElementById("current_lembar_gelaran").value);
            let pActualVar = Number(document.getElementById("p_act").value);
            let unitPActualVar = document.getElementById("unit_p_act").value;
            let lActualVar = Number(document.getElementById("l_act").value);
            let commaActualVar = Number(document.getElementById("comma_act").value);
            let qtyVar = Number(document.getElementById("current_qty").value);
            let unitQtyVar = document.getElementById("current_unit").value;
            let gramasiVar = Number(document.getElementById("gramasi").value);

            let kepalaKainVar = Number(document.getElementById("current_kepala_kain").value);
            let pipingVar = Number(document.getElementById("current_piping").value);
            let sisaKainVar = Number(document.getElementById("current_sisa_kain").value);
            let rejectVar = Number(document.getElementById("current_reject").value);
            let sambunganVar = Number(document.getElementById("current_sambungan").value);
            let sambunganRollVar = Number(document.getElementById("current_total_sambungan_roll").value);
            let sisaGelaranVar = Number(document.getElementById("current_sisa_gelaran").value);
            let sisaTidakBisaVar = Number(document.getElementById("current_sisa_tidak_bisa").value);

            let pActualConverted = 0;

            if (document.getElementById("current_sambungan").value != 0) {
                pActualConverted = document.getElementById("current_sambungan").value;
            } else {
                if (unitQtyVar != "KGM") {
                    pActualConverted = pActualCommaActual(pActualVar, unitPActualVar, commaActualVar);
                } else {
                    qtyVar = Number(document.getElementById("current_qty_real").value);

                    // pActualConverted = pActualConversion(pActualVar, unitPActualVar, commaActualVar, lActualVar, gramasiVar, unitQtyVar);
                    pActualConverted = Number(document.getElementById("current_berat_amparan").value);
                }
            }

            let totalPemakaian = ((pActualConverted * lembarGelaranVar) + sisaGelaranVar + kepalaKainVar + sisaTidakBisaVar + rejectVar + pipingVar + sambunganRollVar);

            document.getElementById("current_total_pemakaian_roll").value = totalPemakaian ? Number(totalPemakaian).round(2) : 0;
        }

        // -Calculate Short Roll-
        function calculateShortRoll() {
            var toast = document.querySelector('.iziToast'); // Selector of your toast

            if (toast) {
                iziToast.hide({}, toast);
            }

            let lembarGelaranVar = Number(document.getElementById("current_lembar_gelaran").value);
            let pActualVar = Number(document.getElementById("p_act").value);
            let unitPActualVar = document.getElementById("unit_p_act").value;
            let lActualVar = Number(document.getElementById("l_act").value);
            let commaActualVar = Number(document.getElementById("comma_act").value);
            let qtyVar =  latestStatus == "extension complete" ? Number(latestQty) : Number(document.getElementById("current_qty").value);
            let unitQtyVar = document.getElementById("current_unit").value;
            let gramasiVar = Number(document.getElementById("gramasi").value);

            let kepalaKainVar = latestStatus == "extension complete" ? Number(latestKepalaKain) + Number(document.getElementById("current_kepala_kain").value) : Number(document.getElementById("current_kepala_kain").value);
            let pipingVar = latestStatus == "extension complete" ? Number(latestPiping) + Number(document.getElementById("current_piping").value) : Number(document.getElementById("current_piping").value);
            let sisaKainVar = Number(document.getElementById("current_sisa_kain").value);
            let rejectVar = latestStatus == "extension complete" ? Number(latestReject) + Number(document.getElementById("current_reject").value) : Number(document.getElementById("current_reject").value);
            let sambunganVar = latestStatus == "extension complete" ? Number(latestSambungan) + Number(document.getElementById("current_sambungan").value) : Number(document.getElementById("current_sambungan").value);
            let sambunganRollVar = latestStatus == "extension complete" ? Number(latestSambunganRoll) + Number(document.getElementById("current_total_sambungan_roll").value) : Number(document.getElementById("current_total_sambungan_roll").value);
            let sisaGelaranVar = Number(document.getElementById("current_sisa_gelaran").value);
            let sisaTidakBisaVar = latestStatus == "extension complete" ? Number(latestSisaTidakBisa) + Number(document.getElementById("current_sisa_tidak_bisa").value) : Number(document.getElementById("current_sisa_tidak_bisa").value);

            let pActualConverted = 0;

            if (document.getElementById("current_sambungan").value != 0) {
                pActualConverted = document.getElementById("current_sambungan").value;
            } else {
                if (unitQtyVar != "KGM") {
                    pActualConverted = pActualCommaActual(pActualVar, unitPActualVar, commaActualVar);
                } else {
                    qtyVar = Number(document.getElementById("current_qty_real").value);

                    // pActualConverted = pActualConversion(pActualVar, unitPActualVar, commaActualVar, lActualVar, gramasiVar, unitQtyVar);
                    pActualConverted = Number(document.getElementById("current_berat_amparan").value);
                }
            }

            // let shortRoll = pActualConverted * lembarGelaranVar + kepalaKainVar + pipingVar + sisaKainVar + rejectVar + sambunganVar - qtyVar;
            let shortRoll = Number(((pActualConverted * lembarGelaranVar) + sambunganVar + sisaGelaranVar + sambunganRollVar + kepalaKainVar + sisaTidakBisaVar + rejectVar + sisaKainVar + pipingVar) - qtyVar).round(2);

            if (sambunganVar != 0) {
                shortRoll = 0;
            }

            let shortRollPercentage = qtyVar > 0 ? (shortRoll / qtyVar) * 100 : 0;

            if (!($('#unlocked_by').val() && $('#unlocked_by').val() > 0)) {
                if (status == "PENGERJAAN FORM CUTTING SPREAD") {
                    if (!isNaN(shortRollPercentage) && shortRollPercentage < -2) {
                        iziToast.warning({
                            title: 'Warning',
                            message: 'Short Roll kurang dari -2 %',
                            position: 'topCenter'
                        });
                    } else {
                        iziToast.success({
                            title: 'Aman',
                            message: 'Short Roll tidak kurang dari -2 %',
                            position: 'topCenter'
                        });
                    }
                }
            }

            document.getElementById("current_short_roll").value = isNaN(shortRoll) ? 0 : Number(shortRoll).round(2);
            document.getElementById("current_short_roll_percentage").value = isNaN(shortRollPercentage) ? 0 : Number(shortRollPercentage).round(2);
        }

        // -Calculate Sisa Kain-
        function calculateSisaKain() {
            let lembarGelaranVar = Number(document.getElementById("current_lembar_gelaran").value);
            let kepalaKainVar = Number(document.getElementById("current_kepala_kain").value);
            let rejectVar = Number(document.getElementById("current_reject").value);
            let pipingVar = Number(document.getElementById("current_piping").value);
            let sisaTidakBisaVar = Number(document.getElementById("current_sisa_tidak_bisa").value);

            let pActualVar = Number(document.getElementById("p_act").value);
            let lActualVar = Number(document.getElementById("l_act").value);
            let unitPActualVar = document.getElementById("unit_p_act").value;
            let commaActualVar = Number(document.getElementById("comma_act").value);
            let gramasiVar = Number(document.getElementById("gramasi").value);

            let qtyVar = Number(document.getElementById("current_qty").value);
            let unitQtyVar = document.getElementById("current_unit").value;

            let pActualConverted = 0;

            if (unitQtyVar != "KGM") {
                pActualConverted = pActualCommaActual(pActualVar, unitPActualVar, commaActualVar);
            } else {
                qtyVar = Number(document.getElementById("current_qty_real").value);

                // pActualConverted = pActualConversion(pActualVar, unitPActualVar, commaActualVar, lActualVar, gramasiVar, unitQtyVar);
                pActualConverted = Number(document.getElementById("current_berat_amparan").value);
            }

            let sisaKain = qtyVar - ((pActualConverted * lembarGelaranVar) + kepalaKainVar + sisaTidakBisaVar + rejectVar + rejectVar + pipingVar);

            document.getElementById("current_sisa_kain").value = sisaKain;
        }

        // -Calculate Sambungan-
        function calculateSambungan(sisaGelaranParam, unitSisaGelaranParam) {
            let sisaGelaranVar = sisaGelaranParam > 0 ? Number(sisaGelaranParam) : (Number(document.getElementById("current_sisa_gelaran").value));
            let unitSisaGelaranVar = unitSisaGelaranParam ? unitSisaGelaranParam : (document.getElementById("current_sisa_gelaran_unit").value);
            let qtyVar = Number(document.getElementById("current_qty").value);
            let unitQtyVar = document.getElementById("current_unit").value;
            let pActualVar = Number(document.getElementById('p_act').value);
            let unitPActualVar = document.getElementById('unit_p_act').value;
            let commaActualVar = Number(document.getElementById('comma_act').value);
            let lActualVar = Number(document.getElementById('l_act').value);
            let gramasiVar = Number(document.getElementById('gramasi').value);

            let pActualConverted = 0;
            let sisaGelaranConverted = 0;

            // Convert P Actual
            if (unitQtyVar != "KGM") {
                pActualConverted = pActualCommaActual(pActualVar, unitPActualVar, commaActualVar);
            } else {
                // pActualConverted = pActualConversion(pActualVar, unitPActualVar, commaActualVar, lActualVar, gramasiVar, unitQtyVar);
                pActualConverted = Number(document.getElementById("current_berat_amparan").value);
            }

            // Convert Sisa Gelaran
            if (unitSisaGelaranVar == unitQtyVar) {
                sisaGelaranConverted = sisaGelaranVar;
            } else {
                if (unitQtyVar == "YARD" || unitQtyVar == "YRD") {
                    unitQtyVar = "METER";
                }

                sisaGelaranConverted = conversion(sisaGelaranVar, unitQtyVar, unitSisaGelaranVar);
            }

            console.log("sisa gelaran = "+sisaGelaranVar, "kain actual = "+pActualConverted);

            let estSambungan = pActualConverted - sisaGelaranConverted;

            if (latestStatus == "need extension") {
                document.getElementById("current_sambungan").value = estSambungan;
                document.getElementById("current_total_pemakaian_roll").value = estSambungan;
            }

            return estSambungan;
        }

        // -Get Cons. WS Data-
        function getNumberData() {
            return $.ajax({
                url: '{{ route('get-number-form-cut-input') }}',
                type: 'get',
                data: {
                    act_costing_id: $("#act_costing_id").val(),
                    color: $("#color").val(),
                    panel: $("#panel").val(),
                },
                dataType: 'json',
                success: function(res) {
                    // if (res) {
                    //     let consWs = res.cons_ws;

                    //     document.getElementById("cons_ws").value = consWs;
                    // }

                    if (res) {
                        let cons = res.cons_ws;
                        let consUnit = "";
                        console.log((res.unit_cons_ws || '').toUpperCase())
                        switch ((res.unit_cons_ws || '').toUpperCase()) {
                            case 'KG' :
                                break;
                            case 'KGM' :
                                consUnit = 'KGM';

                                break;
                            case 'MT' :
                            case 'MTR' :
                            case 'METER' :
                                consUnit = 'METER';

                                break;
                            case 'YRD' :
                            case 'YARD' :
                                cons = Number(cons * 0.9144).round(3);
                                consUnit = 'METER';

                                break;
                            default :
                                consUnit = res.unit_cons_ws;
                                break;
                        }

                        $('#cons_ws').val(cons).trigger("change");
                        $('#unit_cons_ws').val(consUnit).trigger("change");
                        // $('#cons_ws_marker').val(cons).trigger("change");
                        // $("#unit_cons_ws_marker").val(consUnit).trigger("change");
                        // $('#cons_marker').val(cons).trigger("change");
                        // $("#unit_cons_marker").val(consUnit).trigger("change");

                        getItemByWsColorPanel();
                    }
                },
                error: function(jqXHR) {
                    showAjaxError(jqXHR, 'Gagal memuat data cons. WS.');
                }
            });
        }

        function getItemByWsColorPanel(ws = null, color = null, panel = null) {
            deleteAfterFirstMarkerItem();

            let wsVal = ws || document.getElementById('no_ws').value;
            let colorVal = color || document.getElementById('color').value;
            let panelVal = panel || document.getElementById('panel').value;

            $.ajax({
                url: '{{ route('get-item-by-ws-color-panel') }}',
                method: 'get',
                data: {
                    act_costing_ws: wsVal,
                    color: colorVal,
                    panel: panelVal
                },
                dataType: 'json',
                success: function(response) {
                    console.log(response);
                    if (response.length > 0) {
                        response.forEach((item, index) => {
                            console.log(item, index);
                            if (index == 0) {
                                document.getElementById('txt_id_item_0').value = item.id_item;
                                document.getElementById('txt_detail_item_0').value = item.itemdesc;
                            } else {
                                addMarkerItem(item.id_item, item.itemdesc);
                            }
                        });
                    }
                },
                error: function(jqXHR) {
                    showAjaxError(jqXHR, 'Gagal memuat data item.');
                }
            });
        }

        function addMarkerItem(id = "", detail = "") {
            const container = document.getElementById('marker_items');
            const items = container.getElementsByClassName('marker_item');

            // Get last item
            const lastItem = items[items.length - 1];

            // Clone it
            const newItem = lastItem.cloneNode(true);

            // New index
            const newIndex = items.length;

            // Update IDs and names
            newItem.id = 'marker_items_' + newIndex;

            const inputs = newItem.querySelectorAll('input');
            inputs.forEach((input) => {
                // Update id
                if (input.id.includes('txt_id_item')) {
                    input.id = 'txt_id_item_' + newIndex;
                    input.name = 'txt_id_item[' + newIndex + ']';
                    input.value = id;
                } else if (input.id.includes('txt_detail_item')) {
                    input.id = 'txt_detail_item_' + newIndex;
                    input.name = 'txt_detail_item[' + newIndex + ']';
                    input.value = detail;
                }
            });

            // Append to container
            container.appendChild(newItem);
        }

        function deleteAfterFirstMarkerItem() {
            const container = document.getElementById('marker_items');
            const items = container.getElementsByClassName('marker_item');

            // Convert HTMLCollection to array to safely remove elements
            const itemsArray = Array.from(items);

            itemsArray.forEach((item, index) => {
                if (index > 0) {
                    item.remove();
                }
            });
        }

        // -Update Ply Progress-
        function updatePlyProgress() {
            let originalLembar = Number($("#lembar_gelaran").val());
            let currentLembar = Number($("#current_lembar_gelaran").val());
            let qtyPly = Number($("#qty_ply").val());

            document.getElementById("current_ply_progress_txt").innerText = ((totalLembar - originalLembar) + currentLembar) + "/" + qtyPly;
            document.getElementById("current_ply_progress").style.width = Number(qtyPly) > 0 ? ((Number( totalLembar - originalLembar) + currentLembar) / Number(qtyPly) * 100) + "%" : "0%";
        }

        async function checkStockerForm() {
            document.getElementById("loading").classList.remove("d-none");

            return new Promise((resolve, reject) => {
                let superadmin = '{{ Auth::user()->roles->whereIn("nama_role", ["superadmin"])->count() }}'
                if (Number(superadmin) > 0) {
                    $.ajax({
                        type: "get",
                        url: "{{ route('check-stocker-form') }}",
                        data: { id: $("#id").val() },
                        dataType: "json",
                        success: function (response) {
                            document.getElementById("loading").classList.add("d-none");

                            document.getElementById("bypass").checked = false;

                            if (response.status == 200) {
                                resolve(true);
                            } else {
                                if(response.additional == 'Closing'){
                                    Swal.fire({
                                        icon: 'info',
                                        title: 'Info',
                                        text: response.message
                                    }).then(() => {
                                        resolve(false);
                                    });
                                }else{
                                    Swal.fire({
                                        icon: 'warning',
                                        title: 'WARNING',
                                        html: response.message,
                                        showCancelButton: true,
                                        showConfirmButton: true,
                                        cancelButtonText: 'Batalkan',
                                        confirmButtonText: 'Lanjutkan',
                                        confirmButtonColor: '#fa4456',
                                    }).then(result => {
                                        if (result.isConfirmed) {
                                            resolve(true);
                                        } else {
                                            resolve(false);
                                        }
                                    });
                                }
                            }
                        },
                        error: function (jqXHR) {
                            document.getElementById("loading").classList.add("d-none");

                            console.error(jqXHR);

                            Swal.fire({
                                icon: 'error',
                                title: 'ERROR',
                                text: ajaxErrorMessage(jqXHR, 'Terjadi Kesalahan.'),
                                showCancelButton: false,
                                showConfirmButton: true,
                                confirmButtonText: 'Oke',
                            });

                            resolve(false);
                        }
                    });
                } else {
                    document.getElementById("loading").classList.add("d-none");

                    resolve(true);
                }
            });
        }

        // -Store Time Record Transaction-
        async function storeTimeRecord() {
            if (isAddRollMode()) {
                return storeAddedRoll();
            }

            // edit roll menjadi need extension -> roll sambungan wajib lengkap
            let extInvalid = validateExtSection();
            if (extInvalid) {
                return extInvalid;
            }

            if (hasAddRollExtension()) {
                const extConfirm = await Swal.fire({
                    icon: 'question',
                    title: 'Ubah menjadi need extension?',
                    html: 'Roll sambungan <b>' + document.getElementById("ext_id_roll").value + '</b> akan disisipkan tepat setelah roll ini.' +
                        '<br><br><small>Qty roll sambungan (dan pemakaiannya di form lain setelahnya) akan dihitung ulang sesuai urutan waktu.</small>',
                    showCancelButton: true,
                    confirmButtonText: 'Lanjut',
                    cancelButtonText: 'Batal',
                });

                if (!extConfirm.isConfirmed) {
                    return;
                }
            }

            // lepas sambungan: roll need extension yang sisa gelarannya dikosongkan
            if (document.getElementById("current_has_extension").value == '1' && Number(document.getElementById("current_sisa_gelaran").value) <= 0) {
                let parentRoll = document.getElementById("current_id_roll").value || '-';
                let childRoll = document.getElementById("current_ext_child_roll").value || '-';

                const releaseConfirm = await Swal.fire({
                    icon: 'warning',
                    title: 'Lepas sambungan?',
                    html: 'Sisa Gelaran roll <b>' + parentRoll + '</b> dikosongkan, sehingga sambungannya akan <b>dilepas</b>:' +
                        '<ul class="text-start mt-2">' +
                            '<li>Baris roll sambungan <b>' + childRoll + '</b> (sambungan &amp; sisa roll) akan <b>dihapus</b> dari form ini.</li>' +
                            '<li>Qty roll <b>' + childRoll + '</b> dikembalikan dan dihitung ulang.</li>' +
                            '<li>Roll <b>' + parentRoll + '</b> menjadi <b>complete</b>.</li>' +
                        '</ul>' +
                        '<small>Periksa kembali lembar, sisa tidak bisa, dan sisa kain roll <b>' + parentRoll + '</b> sebelum melanjutkan.</small>',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, lepas sambungan',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#fa4456',
                });

                if (!releaseConfirm.isConfirmed) {
                    return;
                }
            }

            let proceed = await checkStockerForm();

            if (proceed) {
                document.getElementById("loading").classList.remove("d-none");

                clearModified();

                let spreadingForm = new FormData(document.getElementById("spreading-form"));

                let dataObj = {
                    "id": $("#id").val(),
                    "p_act": $("#p_act").val(),
                    "comma_act": $("#comma_act").val(),
                    "l_act": $("#l_act").val(),
                    "no_form_cut_input": $("#no_form").val(),
                    "start": $("#start-time").val(),
                    "finish": $("#finish-time").val(),
                    "no_meja": $("#no_meja").val(),
                    "color_act": $("#color_act").val(),
                    "detail_item": $("#detail_item").val(),
                    "operator": $('#operator').val(),
                    "consAct": $('#cons_actual_gelaran').val(),
                    "unitConsAct": $('#unit_cons_actual_gelaran').val(),
                    "totalLembar": totalLembar
                }

                spreadingForm.forEach((value, key) => dataObj[key] = value);

                return $.ajax({
                    url: '{{ route('update-spreading-form') }}',
                    type: 'post',
                    dataType: 'json',
                    data: dataObj,
                    success: async function(res) {
                        if (res) {
                            if (res.status == 200) {
                                // notifikasi perubahan sambungan (tambah / lepas)
                                if (res.message && res.message != 'alright') {
                                    iziToast.success({
                                        title: 'Berhasil',
                                        message: res.message,
                                        position: 'topCenter',
                                        timeout: 8000,
                                    });
                                }

                                await clearSpreadingForm();

                                await getSummary(true);

                                await finishProcess();
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: res.title ?? 'Gagal',
                                    html: res.message ?? 'Terjadi Kesalahan',
                                    showConfirmButton: true,
                                    confirmButtonText: 'Oke',
                                    confirmButtonColor: "#6531a0",
                                });

                                document.getElementById("loading").classList.add("d-none");
                            }
                        } else {
                            document.getElementById("loading").classList.add("d-none");
                        }
                    },
                    error: function(jqXHR) {
                        document.getElementById("loading").classList.add("d-none");

                        console.log(jqXHR);

                        let res = jqXHR.responseJSON;
                        let message = '';
                        let i = 0;

                        iziToast.error({
                            title: 'Gagal',
                            message: ajaxErrorMessage(jqXHR, 'Gagal menyimpan data.'),
                            position: 'topCenter'
                        });

                        for (let key in (res?.errors ?? {})) {
                            message = res.errors[key];
                            if (!document.getElementById(key)) continue;
                            document.getElementById(key).classList.add('is-invalid');
                            modified.push(
                                [key, '.classList', '.remove(', "'is-invalid')"],
                            )

                            if (i == 0) {
                                document.getElementById(key).focus();
                                i++;
                            }
                        };
                    }
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Form sudah memiliki Stocker',
                    showConfirmButton: true,
                    confirmButtonText: 'Oke',
                    confirmButtonColor: "#6531a0",
                });
            }
        }

        function checkLockExtension() {
            let sambunganElement = document.getElementById("current_sambungan");
            let sisaGelaranElement = document.getElementById("current_sisa_gelaran");
            let lembarGelaranElement = document.getElementById("current_lembar_gelaran");

            if (sambunganElement.value > 0) {
                sambunganElement.removeAttribute("readonly");
                sisaGelaranElement.setAttribute("readonly", true);
                lembarGelaranElement.setAttribute("readonly", true);
            } else {
                sisaGelaranElement.removeAttribute("readonly");
                lembarGelaranElement.removeAttribute("readonly");
                sambunganElement.setAttribute("readonly", true);
            }
        }

        function checkLockQtyInput(latestStatus = null) {
            let superadmin = '{{ Auth::user()->roles->whereIn("nama_role", ["superadmin"])->count() }}'

            let idRollElement = document.getElementById("current_id_roll");
            let idItemElement = document.getElementById("current_id_item");
            let lotElement = document.getElementById("current_lot");
            let rollBuyerElement = document.getElementById("current_roll_buyer");
            let qtyElement = document.getElementById("current_qty");
            let qtyRealElement = document.getElementById("current_qty_real");

            if (idRollElement.value.length > 0 && idRollElement.value != '-') {
                idItemElement.setAttribute("readonly", true);
                lotElement.setAttribute("readonly", true);
                rollBuyerElement.setAttribute("readonly", true);
                if (superadmin < 1) {
                    qtyElement.setAttribute("readonly", true);
                    qtyRealElement.setAttribute("readonly", true);
                }
            } else {
                idItemElement.removeAttribute("readonly");
                lotElement.removeAttribute("readonly");
                rollBuyerElement.removeAttribute("readonly");
                if (superadmin < 1) {
                    qtyElement.removeAttribute("readonly");
                    qtyRealElement.removeAttribute("readonly");
                }
            }

            if (latestStatus == 'extension complete') {
                qtyElement.setAttribute("readonly");
                qtyRealElement.setAttribute("readonly");
            }
        }

        // -Lock Item input on Spreading Form-
        function lockItemSpreading() {
            document.getElementById("current_id_roll").setAttribute("readonly", true);
            document.getElementById("current_id_item").setAttribute("readonly", true);
            document.getElementById("current_lot").setAttribute("readonly", true);
            document.getElementById("current_roll").setAttribute("readonly", true);
            document.getElementById("current_qty").setAttribute("readonly", true);
            document.getElementById("current_qty_real").setAttribute("readonly", true);
        }

        // -Open Item input on Spreading Form-
        function openItemSpreading(latestStatus = null) {
            // alert(latestStatus);
            if (latestStatus != 'extension complete') {
                document.getElementById("current_id_roll").removeAttribute("readonly");
            } else {
                document.getElementById("current_id_roll").setAttribute("readonly");
                document.getElementById("current_qty_real").setAttribute("readonly");
                document.getElementById("current_qty").setAttribute("readonly");
            }
            document.getElementById("current_id_item").removeAttribute("readonly");
            document.getElementById("current_lot").removeAttribute("readonly");
            document.getElementById("current_roll").removeAttribute("readonly");
            document.getElementById("current_qty").removeAttribute("readonly");
            document.getElementById("current_qty_real").removeAttribute("readonly");
        }

        async function deleteTimeRecord() {
            let currentIdRoll = document.getElementById('current_id_roll_ori').value || document.getElementById('current_id_roll').value || '-';
            let extParentRoll = document.getElementById('current_ext_parent_roll').value;
            let extChildRoll = document.getElementById('current_ext_child_roll').value;

            // Baris roll sambungan tidak bisa dihapus langsung
            if (extParentRoll) {
                return Swal.fire({
                    icon: 'warning',
                    title: 'Roll sambungan tidak bisa dihapus',
                    html: 'Roll <b>' + currentIdRoll + '</b> adalah roll sambungan untuk roll <b>' + extParentRoll + '</b>.' +
                        '<br><br>Untuk melepas sambungan: buka roll <b>' + extParentRoll + '</b>, kosongkan <b>Sisa Gelaran</b>, lalu simpan. ' +
                        'Baris roll sambungan akan terhapus otomatis.',
                    confirmButtonText: 'Mengerti',
                });
            }

            // Roll yang masih punya sambungan tidak bisa dihapus
            if (document.getElementById('current_has_extension').value == '1') {
                return Swal.fire({
                    icon: 'warning',
                    title: 'Roll masih memiliki sambungan',
                    html: 'Roll <b>' + currentIdRoll + '</b> masih disambung oleh roll <b>' + extChildRoll + '</b>.' +
                        '<br><br>Lepas sambungan terlebih dahulu: kosongkan <b>Sisa Gelaran</b> roll ini lalu simpan. Setelah itu roll ini bisa dihapus.',
                    confirmButtonText: 'Mengerti',
                });
            }

            let proceed = await checkStockerForm();

            if (proceed) {
                let idRoll = document.getElementById("current_id").value;

                Swal.fire({
                    icon: 'error',
                    title: 'Hapus roll?',
                    showCancelButton: true,
                    showConfirmButton: true,
                    confirmButtonText: 'Hapus',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#fa4456',
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById("loading").classList.remove("d-none");

                        $.ajax({
                            url: '{{ route('destroy-spreading-roll') }}/'+idRoll,
                            type: 'POST',
                            data: {
                                _method: 'DELETE'
                            },
                            success: async function(res) {
                                if (res.status == 200) {

                                    await clearSpreadingForm();

                                    await getSummary(true);

                                    await finishProcess();

                                } else if (res.status == 201) {

                                    document.getElementById("loading").classList.add("d-none");

                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Berhasil',
                                        text: res.message,
                                        showCancelButton: false,
                                        showConfirmButton: true,
                                        confirmButtonText: 'Oke',
                                    }).then(() => {
                                        window.open(res.redirect, '_blank');
                                    });

                                } else {
                                    document.getElementById("loading").classList.add("d-none");

                                    if (res.status == 400) {
                                        if (res.title) {
                                            // penolakan terkait sambungan
                                            Swal.fire({
                                                icon: 'warning',
                                                title: res.title,
                                                html: res.message,
                                                confirmButtonText: 'Mengerti',
                                            });
                                        } else {
                                            iziToast.error({
                                                title: 'Error',
                                                message: res.message,
                                                position: 'topCenter'
                                            });
                                        }
                                    }
                                }
                            },
                            error: function (jqXHR) {
                                document.getElementById("loading").classList.add("d-none");

                                iziToast.error({
                                    title: 'Error',
                                    message: ajaxErrorMessage(jqXHR, 'Terjadi kesalahan.'),
                                    position: 'topCenter'
                                });
                            }
                        })
                    }
                })
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Form sudah memiliki Stocker',
                    showConfirmButton: true,
                    confirmButtonText: 'Oke',
                    confirmButtonColor: "#6531a0",
                });
            }
        }

        // -Finish Process-
        async function finishProcess() {
            if ($("#operator").val() == "" || $("#cons_actual_gelaran").val() == "") {
                document.getElementById("loading").classList.add("d-none");

                return Swal.fire({
                    icon: 'error',
                    title: 'Tidak Dapat Menyelesaikan Proses',
                    text: 'Harap pastikan data "Operator" dan "Cons. Actual" telah terisi',
                    showConfirmButton: true,
                    confirmButtonText: 'Oke',
                    confirmButtonColor: "#6531a0",
                });
            }

            updateToFinishProcess();
        }

        // -Finish Process Transaction-
        async function updateToFinishProcess() {
            $.ajax({
                url: '{{ route('finish-update-spreading-form') }}/' + id,
                type: 'put',
                dataType: 'json',
                data: {
                    operator: $('#operator').val(),
                    consAct: $('#cons_actual_gelaran').val(),
                    unitConsAct: $('#unit_cons_actual_gelaran').val(),
                    consActNosr: $('#cons_actual_gelaran_short_rolless').val(),
                    unitConsActNosr: $('#unit_cons_actual_gelaran_short_rolless').val(),
                    consWsUprate: $('#cons_ws_uprate').val(),
                    consMarkerUprate: $('#cons_marker_uprate').val(),
                    consWsUprateNoSr: $('#cons_ws_uprate_nosr').val(),
                    consMarkerUprateNoSr: $('#cons_marker_uprate_nosr').val(),
                    totalLembar: totalLembar
                },
                success: function(res) {
                    document.getElementById("loading").classList.add("d-none");

                    if (res && res.status && res.status == 400) {
                        Swal.fire({
                            icon: 'info',
                            title: 'Info',
                            text: res.message,
                            showCancelButton: false,
                            showConfirmButton: true,
                            confirmButtonText: 'Oke',
                        });
                    } else {
                        if (res) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: 'Data berhasil diubah',
                                showCancelButton: false,
                                showConfirmButton: true,
                                confirmButtonText: 'Oke',
                                timer: 5000,
                                timerProgressBar: true
                            }).then((result) => {
                                location.reload();
                            })
                        }
                    }

                },
                error: function(jqXHR) {
                    document.getElementById("loading").classList.add("d-none");

                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: ajaxErrorMessage(jqXHR, 'Terjadi kesalahan'),
                        showCancelButton: false,
                        showConfirmButton: true,
                        confirmButtonText: 'Oke',
                    });
                }
            });
        }

        // Update Header Data
        function updateHeaderData() {
            document.getElementById("loading").classList.remove("d-none");

            let dataObj = {
                "id": $("#id").val(),
                "no_form_cut_input": $("#no_form").val(),
                "start": $("#start-time").val(),
                "finish": $("#finish-time").val(),
                "no_meja": $("#no_meja").val(),
                "qty_ply": $("#qty_ply").val(),
            }

            return $.ajax({
                url: '{{ route('update-header-form') }}',
                type: 'post',
                dataType: 'json',
                data: dataObj,
                success: async function(res) {
                    if (res && res.status && res.status == 400) {
                        document.getElementById("loading").classList.add("d-none");

                        Swal.fire({
                            icon: 'info',
                            title: 'Info',
                            text: res.message,
                            showCancelButton: false,
                            showConfirmButton: true,
                            confirmButtonText: 'Oke',
                        });
                    } else {
                        if (res) {
                            await clearSpreadingForm();

                            await getSummary(true);

                            await finishProcess();
                        } else {
                            document.getElementById("loading").classList.add("d-none");
                        }
                    }
                },
                error: function(jqXHR) {
                    document.getElementById("loading").classList.add("d-none");

                    console.log(jqXHR);

                    let res = jqXHR.responseJSON;
                    let message = '';
                    let i = 0;

                    iziToast.error({
                        title: 'Gagal',
                        message: ajaxErrorMessage(jqXHR, 'Gagal menyimpan data.'),
                        position: 'topCenter'
                    });

                    for (let key in (res?.errors ?? {})) {
                        message = res.errors[key];
                        if (!document.getElementById(key)) continue;
                        document.getElementById(key).classList.add('is-invalid');
                        modified.push(
                            [key, '.classList', '.remove(', "'is-invalid')"],
                        )

                        if (i == 0) {
                            document.getElementById(key).focus();
                            i++;
                        }
                    };
                }
            });
        }

        // Update Detail Data
        function updateDetailData() {
            document.getElementById("loading").classList.remove("d-none");

            let dataObj = {
                "id": $("#id").val(),
                "no_form_cut_input": $("#no_form").val(),
                "p_act": $("#p_act").val(),
                "comma_act": $("#comma_act").val(),
                "l_act": $("#l_act").val(),
            }

            return $.ajax({
                url: '{{ route('update-detail-form') }}',
                type: 'post',
                dataType: 'json',
                data: dataObj,
                success: async function(res) {
                    if (res && res.status && res.status == 400) {
                        document.getElementById("loading").classList.add("d-none");

                        Swal.fire({
                            icon: 'info',
                            title: 'Info',
                            text: res.message,
                            showCancelButton: false,
                            showConfirmButton: true,
                            confirmButtonText: 'Oke',
                        });
                    } else {
                        if (res) {
                            await clearSpreadingForm();

                            await getSummary(true);

                            await finishProcess();
                        } else {
                            document.getElementById("loading").classList.add("d-none");
                        }
                    }
                },
                error: function(jqXHR) {
                    document.getElementById("loading").classList.add("d-none");

                    console.log(jqXHR);

                    let res = jqXHR.responseJSON;
                    let message = '';
                    let i = 0;

                    iziToast.error({
                        title: 'Gagal',
                        message: ajaxErrorMessage(jqXHR, 'Gagal menyimpan data.'),
                        position: 'topCenter'
                    });

                    for (let key in (res?.errors ?? {})) {
                        message = res.errors[key];
                        if (!document.getElementById(key)) continue;
                        document.getElementById(key).classList.add('is-invalid');
                        modified.push(
                            [key, '.classList', '.remove(', "'is-invalid')"],
                        )

                        if (i == 0) {
                            document.getElementById(key).focus();
                            i++;
                        }
                    };
                }
            });
        }

        // Sambungan Module
            // var sambunganSection = document.getElementById("sambungan-section");
            // var totalSambunganSection = document.getElementById("total-sambungan-section");
            // var jumlahSambungan = document.getElementById("current_jumlah_sambungan");

            // function showSambungan() {
            //     sambunganSection.classList.remove('d-none');
            //     totalSambunganSection.classList.remove('d-none');
            // }

            // function hideSambungan() {
            //     sambunganSection.classList.add('d-none');
            //     totalSambunganSection.classList.add('d-none');
            // }

            // async function addNewSambungan() {
            //     let currentSambunganRoll = document.getElementById('sambungan_roll_'+(jumlahSambungan.value-1));

            //     console.log(currentSambunganRoll, jumlahSambungan.value);

            //     if ((currentSambunganRoll) && (currentSambunganRoll.value > 0)) {
            //         document.getElementById("loading").classList.remove("d-none");

            //         await storeSambungan();

            //         document.getElementById("loading").classList.add("d-none");

            //         // row
            //         let divRow = document.createElement('div');
            //         divRow.setAttribute('class', 'row');

            //         // 1
            //         let divCol1 = document.createElement('div');
            //         divCol1.setAttribute('class', 'col-12 mb-1');

            //         divCol1.innerHTML=`
            //             <div class="input-group input-group-sm">
            //                 <input type="number" class="form-control form-control-sm border-input sambungan_roll" id="sambungan_roll_`+jumlahSambungan.value+`" name="sambungan_roll[`+jumlahSambungan.value+`]" step=".0001" onkeyup="sumNewSambungan()" onchange="sumNewSambungan();"
            //                 <span class="input-group-text input-group-unit"></span>
            //             </div>
            //         `;

            //         divRow.appendChild(divCol1);
            //         sambunganSection.appendChild(divRow);

            //         jumlahSambungan.value++;
            //     }
            // }

            // var totalCurrentSambunganRoll = 0;

            // async function sumNewSambungan() {
            //     totalCurrentSambunganRoll = 0;

            //     let sambunganRollElements = document.getElementsByClassName("sambungan_roll");

            //     for (let i = 0; i < sambunganRollElements.length; i++) {
            //         totalCurrentSambunganRoll += Number(sambunganRollElements[i].value);
            //     }

            //     $("#current_total_sambungan_roll").val(Number(totalCurrentSambunganRoll)).trigger("change");
            // }

            // async function storeSambungan() {
            //     document.getElementById("loading").classList.remove("d-none");

            //     let spreadingForm = new FormData(document.getElementById("spreading-form"));

            //     let dataObj = {
            //         "no_form_cut_input": $("#no_form").val(),
            //         "color_act": $("#color_act").val(),
            //         "detail_item": $("#detail_item").val(),
            //         "no_meja": $("#no_meja").val(),
            //         "metode": method,
            //         "lap": lap
            //     }

            //     spreadingForm.forEach((value, key) => dataObj[key] = value);

            //     return $.ajax({
            //         url: '{{ route('store-this-time-form-cut-input') }}',
            //         type: 'post',
            //         dataType: 'json',
            //         data: dataObj,
            //         success: function(res) {
            //             document.getElementById("loading").classList.add("d-none");

            //             if (res) {
            //                 console.log(res);
            //             }
            //         }, error: function(jqXHR) {
            //             document.getElementById("loading").classList.add("d-none");

            //             console.log(jqXHR);

            //             Swal.fire({
            //                 icon: 'error',
            //                 title: 'Koneksi Hilang',
            //                 text: 'Terjadi Kesalahan',
            //                 showConfirmButton: true,
            //                 confirmButtonText: 'Oke',
            //                 confirmButtonColor: "#6531a0",
            //             }).then(() => {
            //                 // location.reload();
            //             });
            //         }
            //     });
            // }

            // function checkSambungan(detailId) {
            //     $.ajax({
            //         url: '{{ route('check-sambungan') }}/' + detailId,
            //         type: 'get',
            //         dataType: 'json',
            //         success: async function(res) {
            //             if (res.count > 0) {
            //                 await setSambungan(res.data);

            //                 sumNewSambungan();
            //             }
            //         }
            //     });
            // }

            // async function setSambungan(data) {
            //     jumlahSambungan.value = 0;

            //     sambunganSection.innerHTML = "";

            //     let divRow = document.createElement('div');
            //     divRow.setAttribute('class', 'row');

            //     data.forEach((element, index, array) => {
            //         // 1
            //         let divCol1 = document.createElement('div');
            //         divCol1.setAttribute('class', 'col-12 mb-1');

            //         divCol1.innerHTML=`
            //             `+(index == 0 ? `<label class="form-label small">Sambungan Roll</label>`: `` )+`
            //             <div class="input-group input-group-sm">
            //                 <input type="number" class="form-control form-control-sm border-input sambungan_roll" id="sambungan_roll_`+index+`" name="sambungan_roll[`+index+`]" step=".0001" onkeyup="sumNewSambungan()" onchange="sumNewSambungan()" value="`+element.sambungan_roll+`">
            //             </div>
            //         `;

            //         // row
            //         divRow.appendChild(divCol1);
            //         sambunganSection.appendChild(divRow);

            //         jumlahSambungan.value++;
            //     });

            //     // 1
            //     let divCol1 = document.createElement('div');
            //     divCol1.setAttribute('class', 'col-12 mb-1');

            //     divCol1.innerHTML=`
            //         <div class="input-group input-group-sm">
            //             <input type="number" class="form-control form-control-sm border-input sambungan_roll" id="sambungan_roll_`+jumlahSambungan.value+`" name="sambungan_roll[`+jumlahSambungan.value+`]" step=".0001" onkeyup="sumNewSambungan()" onchange="sumNewSambungan()" value="">
            //         </div>
            //     `;

            //     // row
            //     divRow.appendChild(divCol1);
            //     sambunganSection.appendChild(divRow);

            //     jumlahSambungan.value++;
            // }

            // function resetSambungan() {
            //     $("#current_total_sambungan_roll").val("0");

            //     sambunganSection.innerHTML = "";

            //     // row
            //     let divRow = document.createElement('div');
            //     divRow.setAttribute('class', 'row');

            //     // 1
            //     let divCol1 = document.createElement('div');
            //     divCol1.setAttribute('class', 'col-12 mb-1');

            //     divCol1.innerHTML=`
            //         <label class="form-label small">Sambungan Roll</label>
            //         <div class="input-group input-group-sm">
            //             <input type="number" class="form-control form-control-sm border-input sambungan_roll" id="sambungan_roll_0" name="sambungan_roll[0]" step=".0001" onkeyup="sumNewSambungan()" onchange="sumNewSambungan()">
            //             <span class="input-group-text input-group-unit"></span>
            //         </div>
            //     `;

            //     // row
            //     divRow.appendChild(divCol1);

            //     sambunganSection.appendChild(divRow);

            //     jumlahSambungan.value = 1;
            // }

        async function recalculateForm() {
            let proceed = await checkStockerForm();

            if (proceed) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Recalculate Form?',
                    showCancelButton: true,
                    showConfirmButton: true,
                    confirmButtonText: 'Recalculate',
                    cancelButtonText: 'Cancel',
                }).then((result) => {
                    document.getElementById("loading").classList.remove('d-none');

                    $.ajax({
                        url: '{{ route('recalculate-spreading-form') }}/' + id,
                        type: 'post',
                        dataType: 'json',
                        success: function(res) {
                            document.getElementById("loading").classList.add('d-none');

                            console.log(res);

                            Swal.fire({
                                icon: 'info',
                                title: 'Info',
                                text: 'Recalculate Finished.',
                                showCancelButton: false,
                                showConfirmButton: true,
                                confirmButtonText: 'Oke',
                            }).then(() => {
                                location.reload();
                            });
                        },
                        error: function(jqXHR) {
                            document.getElementById("loading").classList.add('d-none');

                            console.error(jqXHR);

                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: ajaxErrorMessage(jqXHR, 'Terjadi Kesalahan.'),
                                showCancelButton: false,
                                showConfirmButton: true,
                                confirmButtonText: 'Oke',
                            });
                        }
                    })
                })
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Form sudah memiliki Stocker',
                    showConfirmButton: true,
                    confirmButtonText: 'Oke',
                    confirmButtonColor: "#6531a0",
                });
            }
        }

        async function updateStatus() {
            let proceed = await checkStockerForm();

            if (proceed) {
                $.ajax({
                    url: '{{ route('update-status-redirect') }}',
                    type: 'put',
                    data: {
                        edit_id_status: id,
                        edit_status: $('#edit_status').val(),
                    },
                    dataType: 'json',
                    success: function(res) {
                        document.getElementById("loading").classList.add('d-none');

                        if (res.redirect) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: 'Status form berhasil diubah.',
                                showCancelButton: false,
                                showConfirmButton: true,
                                confirmButtonText: 'Oke',
                            }).then(() => {
                                window.open(res.redirect, '_blank');
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: res.message,
                                showCancelButton: false,
                                showConfirmButton: true,
                                confirmButtonText: 'Oke',
                            });
                        }
                    },
                    error: function(jqXHR) {
                        document.getElementById("loading").classList.add('d-none');

                        console.error(jqXHR);

                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: ajaxErrorMessage(jqXHR, 'Terjadi Kesalahan.'),
                            showCancelButton: false,
                            showConfirmButton: true,
                            confirmButtonText: 'Oke',
                        });
                    }
                })
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Form sudah memiliki Stocker',
                    showConfirmButton: true,
                    confirmButtonText: 'Oke',
                    confirmButtonColor: "#6531a0",
                });
            }
        }

        // -Panduan: sorot elemen asli + tur langkah demi langkah-
        (function () {
            const gEl = document.getElementById('cfGuide');
            if (!gEl) return;
            const gBs = bootstrap.Modal.getOrCreateInstance(gEl);
            const gGo = (sel, smooth) => { const t = gEl.querySelector(sel); if (t) t.scrollIntoView({ behavior: smooth ? 'smooth' : 'auto', block: 'start' }); };
            // sel: beberapa selector dipisah "|" dipakai berurutan, yang pertama tampil yang disorot
            const ALL_STEPS = [
                { sel: '#cf-head-r', sec: 'cg-peta', lbl: 'Panduan & Kembali',
                  d: '<b>Panduan</b> membuka halaman bantuan ini kapan saja · <b>Kembali ke Completed Form</b> ke daftar form.' },
                { sel: '#header-data-card', sec: 'cg-header', lbl: '1. Header Data',
                  d: 'Ubah <b>Meja</b>, <b>Start</b>, <b>Finish</b>, dan <b>QTY Cut Ply</b>, lalu tekan <b>SIMPAN</b> di kartu ini.' },
                { sel: '#detail-data-card', sec: 'cg-detail', lbl: '2. Detail Data',
                  d: 'Isi <b>Panjang</b>, <b>Comma</b>, dan <b>Lebar Actual</b>. Cons Ampar dihitung otomatis. Tekan <b>SIMPAN</b>.' },
                { sel: '#lost-time-card', sec: 'cg-recalc', lbl: '3. Loss Time',
                  d: 'Daftar loss time saat pengerjaan. Hanya untuk dilihat.' },
                { sel: '#recalculate-btn', sec: 'cg-recalc', lbl: '4. Recalculate Form',
                  d: 'Menghitung ulang seluruh form dari data roll. Pakai bila angka terlihat tidak sesuai.' },
                { sel: '#summary-card', sec: 'cg-summary', lbl: '5. Summary',
                  d: '<b>Klik baris</b> untuk edit roll · <b>geser</b> pegangan untuk ubah urutan · <b>Tambah Roll</b> untuk menyisipkan roll. <b>Operator</b> wajib diisi.' },
                { sel: '#spreading-form-card', sec: 'cg-spread', lbl: '6. Spreading',
                  d: 'Form roll terpilih. Ubah lembar gelaran, kepala kain, sisa kain, dll. Kolom merah dihitung otomatis. Tekan <b>SIMPAN</b>.' },
                { sel: '#add-roll-ext-section:not(.d-none)|#sisa-gelaran-col', sec: 'cg-ext', lbl: '7. Roll sambungan',
                  d: 'Isi <b>Sisa Gelaran</b> bila roll habis di tengah lembar. Kotak <b>Roll Sambungan</b> muncul: scan roll penyambungnya.' },
                { sel: '#status-card', sec: 'cg-status', lbl: '8. Ubah status',
                  d: 'Khusus superadmin: pilih status form lalu simpan.' },
            ];
            const findEl = sel => {
                for (const s of sel.split('|')) {
                    const el = [...document.querySelectorAll(s)].find(x => x.offsetParent !== null);
                    if (el) return el;
                }
                return null;
            };
            let STEPS = ALL_STEPS, spotTok = 0, spotIdx = -1;

            document.getElementById('cf-guide-btn').onclick = () => gBs.show();

            function spotClear() {
                spotTok++;
                document.querySelectorAll('.cg-spot, .cg-spot-l, .cg-spot-cv').forEach(x => x.remove());
                removeEventListener('scroll', spotOff, true); removeEventListener('resize', spotOff);
            }
            // tutup tur & kembali ke panduan di bagian langkah terakhir yang dilihat
            function spotOff() {
                const st = STEPS[spotIdx];
                spotClear(); spotIdx = -1;
                gEl.addEventListener('shown.bs.modal', () => { if (st) gGo('#' + st.sec); }, { once: true });
                gBs.show();
            }
            function spotGo(i) {
                if (i < 0 || i >= STEPS.length) return;
                spotClear(); spotIdx = i;
                const st = STEPS[i], n = STEPS.length;
                const el = findEl(st.sel);
                if (!el) { iziToast.info({ message: 'Bagian ini belum tampil di halaman', position: 'topCenter' }); spotOff(); return; }
                const tok = spotTok;
                el.scrollIntoView({ behavior: 'smooth', block: el.offsetHeight > innerHeight * .7 ? 'start' : 'center' });
                // tunggu scroll berhenti (posisi elemen stabil beberapa frame), baru pasang sorotan
                let last = null, still = 0;
                const t0 = performance.now();
                (function wait() {
                    if (tok !== spotTok) return;
                    const top = el.getBoundingClientRect().top;
                    still = last !== null && Math.abs(top - last) < 0.5 ? still + 1 : 0;
                    last = top;
                    if (still >= 6 || performance.now() - t0 > 2000) draw(); else requestAnimationFrame(wait);
                })();
                function draw() {
                    const r = el.getBoundingClientRect(), pad = 6;
                    // elemen lebih tinggi dari layar: sorot bagian yang terlihat saja
                    const top = Math.max(8, r.top - pad), bottom = Math.min(innerHeight - 8, r.bottom + pad);
                    const cv = document.createElement('div'); cv.className = 'cg-spot-cv'; cv.onclick = spotOff;
                    const s = document.createElement('div'); s.className = 'cg-spot';
                    Object.assign(s.style, { left: r.left - pad + 'px', top: top + 'px', width: r.width + pad * 2 + 'px', height: Math.max(0, bottom - top) + 'px' });
                    const l = document.createElement('div'); l.className = 'cg-spot-l';
                    l.innerHTML = `<div class="st"><span>Langkah ${i + 1} dari ${n}</span><span class="dots">${STEPS.map((_, k) => `<i class="${k === i ? 'on' : k < i ? 'done' : ''}"></i>`).join('')}</span></div>
                        <b>${st.lbl}</b><span class="d">${st.d}</span>
                        <div class="nav"><button type="button" data-nav="prev" ${i ? '' : 'disabled'}>‹ Sebelumnya</button>
                            <button type="button" data-nav="next" class="pri">${i === n - 1 ? 'Selesai ✓' : 'Berikutnya ›'}</button></div>
                        <small><kbd>←</kbd><kbd>→</kbd> pindah langkah · <kbd>Esc</kbd>, scroll, atau klik area gelap untuk kembali ke panduan</small>`;
                    l.onclick = e => {
                        const b = e.target.closest('[data-nav]');
                        if (!b) return;
                        if (b.dataset.nav === 'prev') spotGo(i - 1); else if (i === n - 1) spotOff(); else spotGo(i + 1);
                    };
                    document.body.append(cv, s, l);
                    const lh = l.offsetHeight, below = bottom + 16 + lh < innerHeight;
                    l.style.left = Math.max(8, Math.min(innerWidth - l.offsetWidth - 8, r.left)) + 'px';
                    l.style.top = (below ? bottom + 16 : Math.max(8, top - lh - 16)) + 'px';
                    const nx = l.querySelector('[data-nav="next"]'); if (nx) nx.focus({ preventScroll: true });
                    // mulai dengarkan scroll user setelah sorotan tampil
                    setTimeout(() => {
                        if (tok !== spotTok || !document.querySelector('.cg-spot')) return;
                        addEventListener('scroll', spotOff, true); addEventListener('resize', spotOff);
                    }, 250);
                }
            }

            gEl.addEventListener('click', e => {
                const a = e.target.closest('.cg-n, .cg-nav a');
                if (a) { e.preventDefault(); e.stopPropagation(); gGo(a.getAttribute('href'), true); return; }
                const b = e.target.closest('[data-spot], [data-tour]');
                if (!b) return;
                // langkah yang elemennya tidak ada di halaman (mis. khusus superadmin) dilewati
                STEPS = ALL_STEPS.filter(x => x.sel.split('|').some(s => document.querySelector(s.replace(':not(.d-none)', ''))));
                const idx = b.dataset.tour !== undefined ? 0 : Math.max(0, STEPS.findIndex(x => x.sel === b.dataset.spot));
                gEl.addEventListener('hidden.bs.modal', () => spotGo(idx), { once: true });
                gBs.hide();
            });
            document.addEventListener('keydown', e => {
                if (spotIdx < 0) return;
                if (e.key === 'Escape') { e.preventDefault(); spotOff(); return; }
                if (!document.querySelector('.cg-spot')) return;
                if (e.key === 'ArrowRight') { e.preventDefault(); spotIdx === STEPS.length - 1 ? spotOff() : spotGo(spotIdx + 1); }
                else if (e.key === 'ArrowLeft') { e.preventDefault(); spotGo(spotIdx - 1); }
            }, true);
        })();
    </script>
@endsection
