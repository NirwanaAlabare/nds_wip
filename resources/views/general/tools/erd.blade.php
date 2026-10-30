<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="icon" type="image/png" sizes="16x16" href="{{ asset('dist/img/tabicon.png') }}">
<title>ERD Designer</title>
<link rel="stylesheet" href="{{ asset('plugins/bootstrap/css/bootstrap.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/fontawesome-free-6.4.2/css/all.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/izitoast/dist/css/iziToast.min.css') }}">
<style>
    :root { --sb: #082149; --line: #d5dbe5; --muted: #6b7280; --accent: #2f80ed; --draft: #ef6c00; }
    html, body { height: 100%; margin: 0; }
    body { background: #f4f6fa; font-family: "Segoe UI", Arial, sans-serif; font-size: 13px; overflow: hidden; color: #1f2937; }

    .erd-app { display: grid; grid-template-rows: 52px 1fr 26px; height: 100vh; }
    .erd-toolbar { background: var(--sb); color: #fff; display: flex; align-items: center; gap: 6px; padding: 0 12px; }
    .erd-toolbar .brand { font-weight: 600; font-size: 15px; margin-right: 6px; white-space: nowrap; }
    .erd-toolbar .diagram-name { background: rgba(255,255,255,.08); border: 1px solid transparent; color: #fff; border-radius: 6px; padding: 4px 8px; width: 200px; }
    .erd-toolbar .diagram-name:focus { outline: none; border-color: rgba(255,255,255,.4); background: rgba(255,255,255,.14); }
    .erd-toolbar .diagram-name:disabled { opacity: .5; }
    .conn-badge { background: rgba(255,255,255,.12); border-radius: 20px; padding: 3px 10px; font-size: 12px; color: #dbe4f3; white-space: nowrap; }
    .tb-btn { background: transparent; border: 1px solid transparent; color: #dbe4f3; border-radius: 6px; padding: 5px 9px; font-size: 13px; white-space: nowrap; }
    .tb-btn:hover { background: rgba(255,255,255,.12); color: #fff; }
    .tb-btn:disabled { opacity: .4; pointer-events: none; }
    .tb-btn.primary { background: #fff; color: var(--sb); font-weight: 600; }
    .tb-btn.primary:hover { background: #e8eefb; }
    .tb-btn .count { background: var(--draft); color: #fff; border-radius: 10px; padding: 0 6px; font-size: 11px; margin-left: 4px; }
    .tb-sep { width: 1px; height: 24px; background: rgba(255,255,255,.18); margin: 0 4px; }
    .zoom-label { min-width: 46px; text-align: center; color: #dbe4f3; }
    .online { display: flex; align-items: center; margin-right: 6px; }
    .online .av { margin-left: -6px; border: 2px solid var(--sb); }

    .av { width: 26px; height: 26px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; color: #fff; font-size: 10px; font-weight: 700; flex: none; text-transform: uppercase; }

    .erd-body { display: grid; grid-template-columns: 240px 1fr 340px; min-height: 0; }
    .erd-side { background: #fff; border-right: 1px solid var(--line); display: flex; flex-direction: column; min-height: 0; }
    .erd-panel { background: #fff; border-left: 1px solid var(--line); display: flex; flex-direction: column; min-height: 0; }
    .panel-tabs { display: flex; border-bottom: 1px solid var(--line); }
    .panel-tabs button { flex: 1; border: none; background: none; padding: 10px; font-weight: 600; color: var(--muted); border-bottom: 2px solid transparent; }
    .panel-tabs button.active { color: var(--sb); border-bottom-color: var(--sb); }
    .panel-body { overflow-y: auto; padding: 14px; flex: 1; }
    .side-sec { padding: 12px; border-bottom: 1px solid #eef1f5; }
    .side-title { font-size: 11px; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; color: var(--muted); margin-bottom: 8px; }
    .table-list { overflow-y: auto; flex: 1; padding: 6px; }
    .table-item { display: flex; align-items: center; gap: 6px; padding: 5px 8px 5px 4px; border-radius: 6px; cursor: pointer; }
    .table-item:hover { background: #f1f5fb; }
    .table-item.active { background: #e8f1ff; color: var(--sb); }
    .table-item.active .nm { font-weight: 600; }
    .table-item .dot { width: 10px; height: 10px; border-radius: 3px; flex: none; }
    .table-item .info { flex: 1; min-width: 0; line-height: 1.25; }
    .table-item .nm { display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: 13px; }
    .table-item .meta { display: flex; align-items: center; gap: 6px; color: var(--muted); font-size: 11px; margin-top: 1px; }
    .table-item .meta .badge-draft { font-size: 9px; line-height: 13px; padding: 0 4px; }
    .table-item .eye { border: none; background: none; color: var(--muted); padding: 0 2px; width: 22px; flex: none; }
    .table-item .eye:hover { color: var(--accent); }
    .table-item.is-hidden .info { opacity: .45; }
    .table-item.is-hidden .dot { opacity: .35; }
    .table-item.is-hidden .eye { color: #c0c6d0; }
    .view-tools { display: flex; align-items: center; gap: 6px; margin-top: 8px; font-size: 11px; color: var(--muted); }
    .vt-btn { border: 1px solid var(--line); background: #fff; color: #374151; border-radius: 5px; font-size: 11px; padding: 1px 6px; white-space: nowrap; }
    .vt-btn:hover { border-color: var(--accent); color: var(--accent); }
    .badge-draft { background: #fff3e6; color: var(--draft); border: 1px solid #ffd2a6; border-radius: 4px; font-size: 10px; font-weight: 700; padding: 0 5px; }
    .badge-db { background: #e8f1ff; color: var(--accent); border: 1px solid #c5dbfb; border-radius: 4px; font-size: 10px; font-weight: 700; padding: 0 5px; }

    .erd-canvas-wrap { position: relative; min-width: 0; min-height: 0; }
    #erdSvg { width: 100%; height: 100%; display: block; cursor: grab; user-select: none; touch-action: none; }
    #erdSvg.panning { cursor: grabbing; }
    #erdSvg.space-pan, #erdSvg.space-pan * { cursor: grab !important; }
    #erdSvg.space-pan.panning, #erdSvg.space-pan.panning * { cursor: grabbing !important; }
    #erdSvg .tbl-head { cursor: move; }
    #erdSvg .erd-row { cursor: pointer; }
    #erdSvg .erd-row:hover .row-bg { fill: #f1f6ff; }
    #erdSvg .port { fill: #fff; stroke: var(--accent); stroke-width: 1.5; opacity: 0; cursor: crosshair; transition: opacity .1s; }
    #erdSvg .tbl:hover .port, #erdSvg.connecting .port { opacity: 1; }
    #erdSvg .port:hover { fill: var(--accent); }
    #erdSvg.connecting .erd-row:hover .row-bg { fill: #dcf5e3; }
    #erdSvg .rel { cursor: pointer; }
    #erdSvg .rel { transition: opacity .12s; }
    #erdSvg.hl .rel:not(.on) { opacity: .12; }
    #erdSvg .rel.on .rel-line { stroke-width: 2.6; }
    #erdSvg .rel.on .rel-end { stroke-width: 2; }
    #erdSvg .add-row { cursor: text; }
    #erdSvg .add-row text { opacity: .6; }
    #erdSvg .add-row:hover text { opacity: 1; }
    #erdSvg .add-row:hover .add-bg { fill: #f1f6ff; }

    .inline-editor { position: absolute; z-index: 20; }
    .inline-editor input { display: block; width: 100%; border: 2px solid var(--accent); border-radius: 5px; padding: 0 8px; outline: none; font-family: "Segoe UI", Arial, sans-serif; box-shadow: 0 6px 18px rgba(15,40,90,.18); }
    .inline-hint { display: inline-block; margin-top: 5px; background: #1f2937; color: #e5e7eb; font-size: 12px; padding: 6px 10px; border-radius: 6px; white-space: nowrap; box-shadow: 0 6px 18px rgba(15,40,90,.18); }
    .inline-hint .t { color: #93c5fd; font-family: Consolas, monospace; }
    .inline-hint .t.bad { color: #fca5a5; }
    .inline-hint .f { background: #374151; border-radius: 3px; padding: 0 4px; font-size: 10px; font-weight: 700; margin-left: 3px; }
    .inline-hint .keys { color: #9ca3af; margin-top: 3px; font-size: 11px; }
    .inline-hint kbd { background: #374151; color: #fff; border-color: #4b5563; }

    .empty-state { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; pointer-events: none; }
    .empty-card { pointer-events: auto; background: #fff; border: 1px solid var(--line); border-radius: 14px; padding: 28px 34px; text-align: center; box-shadow: 0 10px 30px rgba(15,40,90,.08); max-width: 420px; }
    .empty-card .ic { font-size: 34px; color: var(--sb); margin-bottom: 10px; }
    .empty-card h5 { color: var(--sb); font-weight: 700; }
    .empty-card p { color: var(--muted); margin-bottom: 16px; }
    .btn-sb { background: var(--sb); color: #fff; }
    .btn-sb:hover, .btn-sb:focus { background: #0d2f66; color: #fff; }
    .canvas-hint { position: absolute; left: 12px; bottom: 12px; background: rgba(8,33,73,.85); color: #fff; padding: 6px 10px; border-radius: 6px; font-size: 12px; pointer-events: none; }

    .erd-status { background: #fff; border-top: 1px solid var(--line); display: flex; align-items: center; gap: 16px; padding: 0 12px; color: var(--muted); font-size: 12px; }
    .erd-status .sync-err { color: #c62828; }

    .p-title { font-weight: 700; color: var(--sb); font-size: 14px; display: flex; align-items: center; gap: 8px; margin-bottom: 12px; }
    .p-label { font-size: 11px; font-weight: 600; color: var(--muted); text-transform: uppercase; letter-spacing: .03em; margin: 12px 0 4px; }
    .p-info { background: #f4f7fc; border-radius: 8px; padding: 10px; color: #374151; margin-top: 10px; font-size: 12px; }
    .p-info.warn { background: #fff7ed; }
    .swatches { display: flex; gap: 6px; flex-wrap: wrap; }
    .swatch { width: 24px; height: 24px; border-radius: 6px; border: 2px solid transparent; cursor: pointer; }
    .swatch.active { border-color: #fff; box-shadow: 0 0 0 2px var(--accent); }
    .colcard { border: 1px solid #e5e9f0; border-radius: 8px; padding: 6px; margin-bottom: 6px; background: #fbfcfe; }
    .colcard.active { border-color: var(--accent); background: #f2f7ff; }
    .colcard .name-input { flex: 1; min-width: 0; }
    .colcard .type-wrap { width: 140px; flex: none; }
    .col-ro { display: flex; align-items: center; gap: 6px; padding: 5px 8px; border-bottom: 1px solid #eef1f5; }
    .col-ro .k { width: 20px; font-size: 9.5px; font-weight: 700; }
    .col-ro .ty { margin-left: auto; font-family: Consolas, monospace; font-size: 11px; color: #8a94a6; }
    .tg { border: 1px solid #d5dbe5; background: #fff; color: var(--muted); font-size: 10px; font-weight: 700; border-radius: 4px; padding: 1px 6px; }
    .tg.on { background: var(--sb); color: #fff; border-color: var(--sb); }
    .tg.on.pk { background: #d4a017; border-color: #d4a017; }
    .ic-btn { border: none; background: transparent; color: var(--muted); padding: 2px 5px; border-radius: 4px; }
    .ic-btn:hover { background: #eef1f5; color: #111; }
    .ic-btn.danger:hover { background: #fdecec; color: #c62828; }
    .card-btns .btn { flex: 1; }
    .card-btns .btn.active { background: var(--sb); color: #fff; border-color: var(--sb); }
    .tips li { margin-bottom: 6px; color: #374151; }
    kbd { background: #eef1f5; color: #111; border: 1px solid #d5dbe5; font-size: 11px; padding: 0 4px; }
    .quick-add { position: relative; }
    .quick-add i { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: var(--accent); font-size: 11px; }
    .quick-add input { padding-left: 26px; border-style: dashed; border-color: #9bbcf0; }
    .quick-add input:focus { border-style: solid; }

    .act { display: flex; gap: 10px; padding: 8px 0; border-bottom: 1px solid #eef1f5; }
    .act .who { font-weight: 600; color: #111827; }
    .act .time { color: var(--muted); font-size: 11px; margin-left: 4px; }
    .act .desc { color: #374151; word-break: break-word; }
    .act code { color: #1d4ed8; background: #eff6ff; padding: 0 3px; border-radius: 3px; font-size: 11.5px; }
    .online-list { display: flex; flex-wrap: wrap; gap: 6px; }
    .online-chip { display: inline-flex; align-items: center; gap: 6px; background: #f1f5fb; border-radius: 20px; padding: 2px 10px 2px 2px; }

    #erdSvg .apply-btn { cursor: pointer; }
    #erdSvg .apply-btn:hover circle { fill: #ffe7cf; }
    .apply-warn { background: #fff7ed; border: 1px solid #fed7aa; color: #9a3412; border-radius: 8px; padding: 10px 12px; margin-bottom: 12px; }
    .apply-table { border: 1px solid var(--line); border-radius: 8px; margin-bottom: 10px; overflow: hidden; }
    .apply-table .head { background: #f4f7fc; padding: 6px 10px; font-weight: 600; color: var(--sb); }
    .apply-table table { width: 100%; font-size: 12px; margin: 0; }
    .apply-table td, .apply-table th { padding: 4px 10px; border-top: 1px solid #eef1f5; }
    .apply-table th { color: var(--muted); font-weight: 600; font-size: 11px; text-transform: uppercase; }
    .apply-table .ty { font-family: Consolas, monospace; }
    .apply-table .changed { background: #fffbeb; }
    .apply-sql summary { cursor: pointer; color: var(--accent); margin: 6px 0; }
    .swal2-checkbox { font-weight: 600; }
    .sql-box { background: #0f172a; color: #e2e8f0; border-radius: 8px; padding: 12px; font-family: Consolas, monospace; font-size: 12px; text-align: left; white-space: pre; overflow: auto; max-height: 320px; }
    .apply-list { text-align: left; font-size: 13px; }

    .select2-container--bootstrap4 .select2-selection--single { height: calc(1.5em + .5rem + 2px) !important; font-size: 12px; }
    .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered { line-height: calc(1.5em + .5rem) !important; }
    .select2-results__option { font-size: 12px; }
    #newDiagramModal .select2-container--bootstrap4 .select2-selection--single { height: calc(1.5em + .75rem + 2px) !important; font-size: 13px; }

    .colcard .sub { display: flex; gap: 4px; margin-top: 4px; }
    .colcard .sub input { font-size: 11.5px; }
    .colcard .sub .def { width: 110px; flex: none; }
    .tg.on.ix { background: #7c3aed; border-color: #7c3aed; }
    .col-ro .meta { font-size: 11px; color: var(--muted); padding: 0 8px 4px 34px; margin-top: -3px; }
    .fk-box { border: 1px dashed #c5dbfb; border-radius: 8px; padding: 8px; background: #f8fbff; margin-top: 10px; }

    .minimap { position: absolute; right: 12px; bottom: 12px; background: rgba(255,255,255,.95); border: 1px solid var(--line); border-radius: 8px; box-shadow: 0 4px 14px rgba(15,40,90,.12); cursor: pointer; overflow: hidden; }
    .minimap svg { display: block; }

    .tour { position: fixed; inset: 0; z-index: 3000; }
    .tour-spot { position: fixed; border-radius: 10px; box-shadow: 0 0 0 9999px rgba(8,33,73,.55); transition: all .25s; pointer-events: none; }
    .tour-card { position: fixed; width: 340px; background: #fff; border-radius: 12px; padding: 16px; box-shadow: 0 12px 34px rgba(0,0,0,.25); transition: top .25s, left .25s; }
    .tour-card h6 { color: var(--sb); font-weight: 700; margin: 4px 0 6px; }
    .tour-step { font-size: 11px; font-weight: 700; color: var(--accent); text-transform: uppercase; letter-spacing: .04em; }
    .tour-text { color: #374151; line-height: 1.5; }
    .tour-text code { color: #1d4ed8; background: #eff6ff; padding: 0 4px; border-radius: 3px; }
    .act .undo-btn { border: none; background: none; color: var(--accent); font-size: 11.5px; font-weight: 600; padding: 0; margin-left: 6px; }
    .act .undo-btn:hover { text-decoration: underline; }
</style>
</head>
<body>
<div class="erd-app">
    <!-- Toolbar -->
    <div class="erd-toolbar">
        <span class="brand"><i class="fa-solid fa-diagram-project"></i> ERD Designer</span>
        <input id="diagramName" class="diagram-name" placeholder="Nama diagram" disabled>
        <span class="conn-badge d-none" id="connBadge"></span>
        <div class="tb-sep"></div>
        <button class="tb-btn" id="btnAddTable" title="Tabel baru" disabled><i class="fa-solid fa-table"></i> Tabel</button>
        <button class="tb-btn" id="btnUndo" title="Batalkan aksi Anda terakhir (Ctrl+Z)" disabled><i class="fa-solid fa-rotate-left"></i></button>
        <div class="tb-sep"></div>
        <button class="tb-btn" id="btnZoomOut" title="Zoom out"><i class="fa-solid fa-magnifying-glass-minus"></i></button>
        <span class="zoom-label" id="zoomLabel">100%</span>
        <button class="tb-btn" id="btnZoomIn" title="Zoom in"><i class="fa-solid fa-magnifying-glass-plus"></i></button>
        <button class="tb-btn" id="btnFit" title="Tampilkan semua"><i class="fa-solid fa-expand"></i></button>
        <button class="tb-btn" id="btnArrange" title="Rapikan otomatis" disabled><i class="fa-solid fa-wand-magic-sparkles"></i> Rapikan</button>
        <div class="ms-auto d-flex align-items-center gap-1">
            <div class="online" id="onlineAvatars"></div>
            <button class="tb-btn" id="btnRefresh" title="Sinkron sekarang" disabled><i class="fa-solid fa-arrows-rotate"></i></button>
            <button class="tb-btn" id="btnSettings" title="Pengaturan (kolom standar, minimap)"><i class="fa-solid fa-gear"></i></button>
            <button class="tb-btn" id="btnGuide" title="Panduan"><i class="fa-regular fa-circle-question"></i> Panduan</button>
            <div class="dropdown">
                <button class="tb-btn dropdown-toggle" data-bs-toggle="dropdown" id="btnExport" disabled><i class="fa-solid fa-file-export"></i> Export</button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="#" data-export="png"><i class="fa-regular fa-image fa-fw"></i> Gambar PNG</a></li>
                    <li><a class="dropdown-item" href="#" data-export="svg"><i class="fa-solid fa-bezier-curve fa-fw"></i> Gambar SVG</a></li>
                    <li><a class="dropdown-item" href="#" data-export="sql"><i class="fa-solid fa-database fa-fw"></i> SQL semua tabel</a></li>
                </ul>
            </div>
            <button class="tb-btn primary" id="btnApply" title="Buat tabel draft di database" disabled><i class="fa-solid fa-database"></i> Buat di DB<span class="count d-none" id="draftCount"></span></button>
        </div>
    </div>

    <div class="erd-body">
        <!-- Kiri: diagram & daftar tabel -->
        <div class="erd-side">
            <div class="side-sec">
                <div class="side-title">Diagram</div>
                <select id="diagramSelect" class="form-select form-select-sm"></select>
                <div class="d-flex gap-1 mt-2">
                    <button class="btn btn-sm btn-outline-secondary flex-fill" id="btnNewDiagram"><i class="fa-solid fa-plus"></i> Diagram Baru</button>
                    <button class="btn btn-sm btn-outline-danger" id="btnDeleteDiagram" title="Hapus diagram (tabel di database tidak ikut terhapus)" disabled><i class="fa-solid fa-trash"></i></button>
                </div>
            </div>
            <div class="side-sec pb-2">
                <div class="side-title">Tabel</div>
                <input id="tableSearch" class="form-control form-control-sm" placeholder="Cari tabel...">
                <div class="view-tools d-none" id="viewTools">
                    <span id="viewCount"></span>
                    <span class="ms-auto d-flex gap-1">
                        <button class="vt-btn d-none" id="btnViewMatch" title="Tampilkan hanya tabel yang cocok dengan pencarian"><i class="fa-solid fa-filter"></i> Hasil cari</button>
                        <button class="vt-btn" id="btnViewAll" title="Tampilkan semua tabel"><i class="fa-regular fa-eye"></i> Semua</button>
                        <button class="vt-btn" id="btnViewNone" title="Sembunyikan semua tabel"><i class="fa-regular fa-eye-slash"></i> Tidak ada</button>
                    </span>
                </div>
            </div>
            <div class="table-list" id="tableList"></div>
        </div>

        <!-- Kanvas -->
        <div class="erd-canvas-wrap">
            <svg id="erdSvg" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <pattern id="gridSmall" width="20" height="20" patternUnits="userSpaceOnUse">
                        <circle cx="1" cy="1" r="1" fill="#d3d9e3"/>
                    </pattern>
                </defs>
                <rect id="gridBg" width="100%" height="100%" fill="url(#gridSmall)"/>
                <g id="viewport" font-family="Segoe UI, Arial, sans-serif">
                    <g id="relLayer"></g>
                    <g id="tblLayer"></g>
                    <path id="tmpLine" fill="none" stroke="#2f80ed" stroke-width="1.6" stroke-dasharray="5 4" pointer-events="none"/>
                </g>
            </svg>
            <div class="empty-state" id="emptyState" style="display:none"><div class="empty-card" id="emptyCard"></div></div>
            <div class="inline-editor d-none" id="inlineEditor">
                <input id="inlineInput" autocomplete="off" spellcheck="false">
                <div class="inline-hint" id="inlineHint"></div>
            </div>
            <div class="minimap d-none" id="minimap" title="Klik / geser untuk pindah area">
                <svg id="minimapSvg" width="180" height="120"></svg>
            </div>
            <div class="canvas-hint" id="canvasHint" style="display:none">Lepas di kolom tujuan, atau di header tabel draft untuk membuat kolom FK otomatis. Tabel tujuan jauh? Tahan <kbd>Spasi</kbd> sambil gerakkan mouse untuk menggeser kanvas.</div>
        </div>

        <!-- Kanan: properti & riwayat -->
        <div class="erd-panel">
            <div class="panel-tabs">
                <button data-tab="props" class="active"><i class="fa-solid fa-sliders"></i> Properti</button>
                <button data-tab="history"><i class="fa-solid fa-clock-rotate-left"></i> Riwayat</button>
            </div>
            <div class="panel-body" id="panel"></div>
        </div>
    </div>

    <div class="erd-status">
        <span id="statTables"></span>
        <span id="statRels"></span>
        <span id="statSync" class="ms-auto"></span>
    </div>
</div>

<!-- Modal diagram baru -->
<div class="modal fade" id="newDiagramModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header text-white" style="background: var(--sb)">
                <h5 class="modal-title"><i class="fa-solid fa-diagram-project"></i> Diagram Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <label class="form-label fw-semibold">Koneksi database <span class="text-danger">*</span></label>
                <select id="newConnection" class="form-select"></select>
                <div class="form-text">Daftar koneksi MySQL dari <code>config/database.php</code>. Satu koneksi hanya punya satu diagram.</div>
                <label class="form-label fw-semibold mt-3">Nama diagram <span class="text-danger">*</span></label>
                <input id="newName" class="form-control" maxlength="150" placeholder="mis. ERD Project Baru">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-sb" id="btnCreateDiagram"><i class="fa-solid fa-plus"></i> Buat Diagram</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal pengaturan (per orang, disimpan di browser) -->
<div class="modal fade" id="settingsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header text-white" style="background: var(--sb)">
                <h5 class="modal-title"><i class="fa-solid fa-gear"></i> Pengaturan</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="fw-semibold mb-1"><i class="fa-solid fa-table-list"></i> Kolom standar untuk tabel baru</div>
                <div class="form-text mt-0 mb-2">Otomatis ditambahkan setiap membuat tabel draft. Kolom baru yang Anda ketik masuk di atas kolom waktu.</div>
                <div class="form-check"><input class="form-check-input" type="checkbox" id="setStdId"><label class="form-check-label" for="setStdId"><code>id</code> BIGINT UNSIGNED, PK, auto increment</label></div>
                <div class="form-check"><input class="form-check-input" type="checkbox" id="setStdBy"><label class="form-check-label" for="setStdBy"><code>created_by</code>, <code>updated_by</code> VARCHAR(100)</label></div>
                <div class="form-check"><input class="form-check-input" type="checkbox" id="setStdTs"><label class="form-check-label" for="setStdTs"><code>created_at</code>, <code>updated_at</code> TIMESTAMP</label></div>
                <div class="form-check"><input class="form-check-input" type="checkbox" id="setStdDel"><label class="form-check-label" for="setStdDel"><code>deleted_at</code> TIMESTAMP (soft delete)</label></div>
                <hr>
                <div class="fw-semibold mb-1"><i class="fa-regular fa-map"></i> Tampilan</div>
                <div class="form-check"><input class="form-check-input" type="checkbox" id="setMinimap"><label class="form-check-label" for="setMinimap">Tampilkan minimap di pojok kanvas</label></div>
                <div class="form-check"><input class="form-check-input" type="checkbox" id="setLineColor"><label class="form-check-label" for="setLineColor">Warna garis relasi berbeda per kolom induk</label></div>
                <div class="mt-2 mb-1">Gaya garis relasi</div>
                <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="setLineStyle" id="setLineOrtho" value="ortho"><label class="form-check-label" for="setLineOrtho">Siku (menghindari tabel)</label></div>
                <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="setLineStyle" id="setLineCurve" value="curve"><label class="form-check-label" for="setLineCurve">Kurva</label></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary me-auto" id="btnGuideFromSettings"><i class="fa-regular fa-circle-question"></i> Lihat panduan</button>
                <button type="button" class="btn btn-sb" id="btnSaveSettings">Simpan</button>
            </div>
        </div>
    </div>
</div>

<!-- Tur panduan -->
<div class="tour d-none" id="tour">
    <div class="tour-spot" id="tourSpot"></div>
    <div class="tour-card" id="tourCard">
        <div class="tour-step" id="tourStep"></div>
        <h6 id="tourTitle"></h6>
        <div class="tour-text" id="tourText"></div>
        <div class="d-flex align-items-center gap-2 mt-3">
            <button class="btn btn-sm btn-link text-muted px-0" id="tourSkip">Lewati panduan</button>
            <button class="btn btn-sm btn-outline-secondary ms-auto" id="tourPrev">Kembali</button>
            <button class="btn btn-sm btn-sb" id="tourNext">Lanjut</button>
        </div>
    </div>
</div>

<script src="{{ asset('plugins/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
<script src="{{ asset('plugins/izitoast/dist/js/iziToast.min.js') }}"></script>
<script src="{{ asset('plugins/sweetalert/dist/sweetalert2.all.min.js') }}"></script>
<script>
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
    const ROUTES = {
        list: "{{ route('list-erd') }}",
        create: "{{ route('create-erd') }}",
        delete: "{{ route('delete-erd') }}",
        state: "{{ route('state-erd', ':id') }}",
        op: "{{ route('op-erd', ':id') }}",
        preview: "{{ route('preview-erd', ':id') }}",
        apply: "{{ route('apply-erd', ':id') }}",
        undo: "{{ route('undo-erd', ':id') }}",
    };
    const ME = @json(auth()->user()->username);
</script>
<script>
    // ===== Konstanta =====
    const HEAD = 32, ROW = 24, ADDROW = 26, PADB = 6, MINW = 190, GRID = 10;
    const POLL_MS = 20000;
    const COLORS = ['#082149', '#1565c0', '#00897b', '#6a1b9a', '#c62828', '#ef6c00', '#455a64', '#2e7d32'];
    const TYPES = ['INT', 'INT UNSIGNED', 'BIGINT UNSIGNED', 'BIGINT', 'TINYINT', 'VARCHAR(50)', 'VARCHAR(100)', 'VARCHAR(255)', 'TEXT', 'DATE', 'DATETIME', 'TIMESTAMP', 'TIME', 'DECIMAL(15,2)', 'DOUBLE', 'BOOLEAN', 'JSON'];
    const CARDS = { '1:1': 'Satu ke satu', '1:N': 'Satu ke banyak', 'N:1': 'Banyak ke satu', 'N:N': 'Banyak ke banyak' };
    const IDENT = /^[A-Za-z_][A-Za-z0-9_]{0,63}$/;
    const TYPE_RE = /^(?:(?:TINYINT|SMALLINT|MEDIUMINT|INT|INTEGER|BIGINT)(?:\(\d{1,3}\))?(?: UNSIGNED)?(?: ZEROFILL)?|(?:VARCHAR|CHAR|BINARY|VARBINARY|BIT)\(\d{1,5}\)|(?:DECIMAL|NUMERIC|DOUBLE|FLOAT)(?:\(\d{1,2}, ?\d{1,2}\))?(?: UNSIGNED)?(?: ZEROFILL)?|(?:DATETIME|TIMESTAMP|TIME)(?:\([0-6]\))?|BOOLEAN|BOOL|DATE|YEAR(?:\(4\))?|TINYTEXT|TEXT|MEDIUMTEXT|LONGTEXT|JSON|TINYBLOB|BLOB|MEDIUMBLOB|LONGBLOB|(?:ENUM|SET)\('[^'\\]*'(?:, ?'[^'\\]*')*\))$/i;
    // Kolom waktu/user di akhir tabel; kolom baru yang diketik disisipkan sebelum kolom-kolom ini
    const STD_TAIL = new Set(['created_by', 'updated_by', 'created_at', 'updated_at', 'deleted_at']);

    // ===== State =====
    let data = null;             // diagram dari server: {id, name, connection, database, version, tables, relations}
    let diagrams = [], connections = [], activities = [], online = [];
    let view = { x: 40, y: 40, k: 1 };
    let sel = null;              // {kind:'table', id, col} | {kind:'rel', id}
    let panelTab = 'props';
    let drag = null, pan = null, conn = null;
    let W = {};                  // cache lebar tabel
    let exporting = false;       // saat export, baris "+ Tambah kolom" tidak digambar
    let opQueue = Promise.resolve(), pendingOps = 0;
    let lastSync = null, syncError = null;
    let revision = null, lastActivityId = 0;   // dikirim saat polling supaya server hanya mengirim yang berubah
    let justCreated = new Set();  // tabel yang baru saja dibuat di database, ditandai sebentar di kanvas
    let hidden = new Set();       // tabel yang disembunyikan; per orang, disimpan di browser (tidak dikirim ke server)
    let undoInfo = { last: null, activities: [] };   // aksi milik saya yang bisa dibatalkan (dari server)

    // ===== Pengaturan per orang (browser) =====
    const SETTINGS_KEY = `erd_settings_${ME}`;
    const settings = Object.assign({ stdId: true, stdBy: false, stdTs: true, stdDel: false, minimap: true, lineColor: true, lineStyle: 'ortho' }, (() => {
        try { return JSON.parse(localStorage.getItem(SETTINGS_KEY)) || {}; } catch (e) { return {}; }
    })());
    function saveSettings() {
        try { localStorage.setItem(SETTINGS_KEY, JSON.stringify(settings)); } catch (e) { /* tetap jalan tanpa penyimpanan */ }
    }

    // ===== Tampil / sembunyi tabel (per orang) =====
    const hiddenKey = () => `erd_hidden_${data.id}_${ME}`;
    const isShown = (id) => !hidden.has(id);
    function loadHidden() {
        try { hidden = new Set(JSON.parse(localStorage.getItem(hiddenKey())) || []); } catch (e) { hidden = new Set(); }
    }
    function setHidden(names) {
        hidden = new Set(names);
        try { localStorage.setItem(hiddenKey(), JSON.stringify([...hidden])); } catch (e) { /* tetap jalan tanpa penyimpanan */ }
        if (sel?.kind === 'table' && !isShown(sel.id)) sel = null;
        if (sel?.kind === 'rel' && rel(sel.id) && !(isShown(rel(sel.id).from.t) && isShown(rel(sel.id).to.t))) sel = null;
        render(); renderPanel();
    }
    const showTables = (names) => setHidden([...hidden].filter((n) => !names.includes(n)));
    const hideTables = (names) => setHidden([...hidden, ...names]);
    // Fokus: hanya tabel ini + tabel yang berelasi langsung dengannya
    function focusTable(id) {
        const keep = new Set([id]);
        data.relations.forEach((r) => {
            if (r.from.t === id) keep.add(r.to.t);
            if (r.to.t === id) keep.add(r.from.t);
        });
        setHidden(data.tables.map((t) => t.id).filter((n) => !keep.has(n)));
        requestAnimationFrame(fit);
    }

    const svg = document.getElementById('erdSvg');
    const vp = document.getElementById('viewport');
    const relLayer = document.getElementById('relLayer');
    const tblLayer = document.getElementById('tblLayer');
    const tmpLine = document.getElementById('tmpLine');
    const panel = document.getElementById('panel');

    const uid = (p) => p + Math.random().toString(36).slice(2, 11);
    const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (m) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[m]));
    const snap = (v) => Math.round(v / GRID) * GRID;
    const tbl = (id) => data?.tables.find((t) => t.id === id);
    const col = (t, id) => t?.columns.find((c) => c.id === id);
    const rel = (id) => data?.relations.find((r) => r.id === id);
    const errMsg = (x) => x.responseJSON?.message || 'Terjadi kesalahan, coba lagi';
    const toast = (msg, type = 'info') => iziToast[type]({ message: esc(msg).replace(/\n/g, '<br>'), position: 'topRight', timeout: type === 'error' ? 5000 : 2200 });

    // ===== Ukuran tabel =====
    const measureCtx = document.createElement('canvas').getContext('2d');
    function textWidth(s, font) { measureCtx.font = font; return measureCtx.measureText(s).width; }
    function tableWidth(t) {
        let nameW = textWidth(t.name, '600 13px Segoe UI, Arial') + (t.draft || justCreated.has(t.id) ? 110 : 44), colW = 0, typeW = 0;
        t.columns.forEach((c) => {
            colW = Math.max(colW, textWidth(c.name, '600 12px Segoe UI, Arial') + (c.def != null ? textWidth(` = ${defaultLabel(c.def)}`, 'italic 10.5px Segoe UI, Arial') + 4 : 0) + (c.comment ? 16 : 0));
            typeW = Math.max(typeW, textWidth(c.type + (c.uq && !c.pk ? ' UQ' : ''), '11px Consolas, monospace'));
        });
        return Math.ceil(Math.max(MINW, nameW, 34 + colW + 20 + typeW + 14));
    }
    const defaultLabel = (d) => (String(d).length > 18 ? String(d).slice(0, 17) + '…' : String(d));
    const hasAddRow = (t) => t.draft && !exporting;
    const tableHeight = (t) => HEAD + t.columns.length * ROW + (hasAddRow(t) ? ADDROW : 0) + PADB;

    // Parent = sisi "1", child = sisi "N" (pemilik FK)
    function parentChild(r) {
        return r.card === 'N:1' ? { parent: r.to, child: r.from } : { parent: r.from, child: r.to };
    }

    // ===== Render kanvas =====
    function applyView() {
        const tf = `translate(${view.x},${view.y}) scale(${view.k})`;
        vp.setAttribute('transform', tf);
        document.getElementById('gridSmall').setAttribute('patternTransform', tf);
        document.getElementById('zoomLabel').textContent = Math.round(view.k * 100) + '%';
        positionEditor();
        renderMinimapView();
    }

    // ===== Minimap: peta kecil semua tabel yang tampil + kotak area yang sedang dilihat =====
    const MM_W = 180, MM_H = 120;
    let mm = null;               // {x0, y0, s} pemetaan dunia -> minimap

    function renderMinimap() {
        const box = document.getElementById('minimap'), mmSvg = document.getElementById('minimapSvg');
        const shown = (data?.tables || []).filter((t) => isShown(t.id));
        box.classList.toggle('d-none', !settings.minimap || shown.length < 2);
        if (box.classList.contains('d-none')) { mm = null; return; }
        const bb = bbox(), pad = 60;
        const s = Math.min(MM_W / (bb.w + pad * 2), MM_H / (bb.h + pad * 2));
        mm = { x0: bb.x - pad - (MM_W / s - bb.w - pad * 2) / 2, y0: bb.y - pad - (MM_H / s - bb.h - pad * 2) / 2, s };
        mmSvg.innerHTML = shown.map((t) => `<rect x="${(t.x - mm.x0) * s}" y="${(t.y - mm.y0) * s}" width="${Math.max(2, W[t.id] * s)}" height="${Math.max(2, tableHeight(t) * s)}" rx="1.5" fill="${t.color}" opacity="${t.draft ? .45 : .75}"/>`).join('')
            + '<rect id="mmView" fill="rgba(47,128,237,.1)" stroke="#2f80ed" stroke-width="1.5" rx="2"/>';
        renderMinimapView();
    }

    function renderMinimapView() {
        const r = document.getElementById('mmView');
        if (!mm || !r) return;
        const b = svg.getBoundingClientRect();
        r.setAttribute('x', (-view.x / view.k - mm.x0) * mm.s);
        r.setAttribute('y', (-view.y / view.k - mm.y0) * mm.s);
        r.setAttribute('width', (b.width / view.k) * mm.s);
        r.setAttribute('height', (b.height / view.k) * mm.s);
    }

    // Klik / geser di minimap = pusatkan layar ke titik itu
    let mmDrag = false;
    function minimapGo(e) {
        if (!mm) return;
        const r = document.getElementById('minimapSvg').getBoundingClientRect(), b = svg.getBoundingClientRect();
        const wx = (e.clientX - r.left) / mm.s + mm.x0, wy = (e.clientY - r.top) / mm.s + mm.y0;
        view.x = b.width / 2 - wx * view.k;
        view.y = b.height / 2 - wy * view.k;
        applyView();
    }
    document.getElementById('minimap').addEventListener('pointerdown', (e) => { mmDrag = true; minimapGo(e); e.preventDefault(); });
    window.addEventListener('pointermove', (e) => { if (mmDrag) minimapGo(e); });
    window.addEventListener('pointerup', () => { mmDrag = false; });

    function render() {
        W = {};
        const tables = data?.tables || [], relations = data?.relations || [];
        tables.forEach((t) => (W[t.id] = tableWidth(t)));

        const fkSet = new Set(), hiSet = new Set();
        relations.forEach((r) => {
            const { child } = parentChild(r);
            fkSet.add(child.t + '|' + child.c);
            if (r.card === 'N:N') fkSet.add(r.from.t + '|' + r.from.c);
            if (sel?.kind === 'rel' && sel.id === r.id) { hiSet.add(r.from.t + '|' + r.from.c); hiSet.add(r.to.t + '|' + r.to.c); }
        });

        applyView();
        computeRoutes();
        relLayer.innerHTML = relations.map(relSvg).join('');
        tblLayer.innerHTML = tables.filter((t) => isShown(t.id)).map((t) => tableSvg(t, fkSet, hiSet)).join('');
        applyHighlight();
        if (!exporting) renderMinimap();
        renderEmptyState();
        renderTableList();
        renderStatus();
        renderToolbar();
    }

    function tableSvg(t, fkSet, hiSet) {
        const w = W[t.id], h = tableHeight(t);
        const isSel = sel?.kind === 'table' && sel.id === t.id;
        const fresh = justCreated.has(t.id);
        const stroke = isSel ? '#2f80ed' : (fresh ? '#16a34a' : (t.draft ? '#f0a35e' : '#d5dbe5'));
        let s = `<g class="tbl" data-t="${esc(t.id)}" transform="translate(${t.x},${t.y})">`;
        if (fresh) s += `<rect x="-5" y="-5" width="${w + 10}" height="${h + 10}" rx="11" fill="#16a34a" opacity=".15"/>`;
        s += `<rect x="2" y="3" width="${w}" height="${h}" rx="7" fill="#0b1f44" opacity=".07"/>`;
        s += `<rect width="${w}" height="${h}" rx="7" fill="#fff" stroke="${stroke}" stroke-width="${isSel || fresh ? 2 : (t.draft ? 1.5 : 1)}"${t.draft ? ' stroke-dasharray="6 4"' : ''}/>`;
        s += `<path class="tbl-head" d="M0 7a7 7 0 0 1 7-7H${w - 7}a7 7 0 0 1 7 7V${HEAD}H0Z" fill="${t.color}"/>`;
        s += `<text class="tbl-head" x="12" y="21" fill="#fff" font-size="13" font-weight="600">${esc(t.name)}</text>`;
        if (t.draft) {
            // Tombol "buat di database" untuk tabel ini
            s += `<g class="apply-btn" data-t="${esc(t.id)}"><title>Tabel ini masih draft. Klik untuk membuat di database.</title>`;
            s += `<circle cx="${w - 72}" cy="16" r="9" fill="#fff"/><path d="M${w - 75} 11.5L${w - 68} 16L${w - 75} 20.5Z" fill="#ef6c00"/></g>`;
            s += `<rect class="tbl-head" x="${w - 54}" y="8" width="44" height="16" rx="8" fill="#ef6c00"/>`;
            s += `<text class="tbl-head" x="${w - 32}" y="20" fill="#fff" font-size="9.5" font-weight="700" text-anchor="middle">DRAFT</text>`;
        } else if (fresh) {
            s += `<rect class="tbl-head" x="${w - 54}" y="8" width="44" height="16" rx="8" fill="#16a34a"/>`;
            s += `<text class="tbl-head" x="${w - 32}" y="20" fill="#fff" font-size="9.5" font-weight="700" text-anchor="middle">BARU</text>`;
        }

        t.columns.forEach((c, i) => {
            const y = HEAD + i * ROW, key = t.id + '|' + c.id;
            const hi = hiSet.has(key) || (isSel && sel.col === c.id);
            s += `<g class="erd-row" data-t="${esc(t.id)}" data-c="${esc(c.id)}">`;
            if (c.comment) s += `<title>${esc(c.name)}: ${esc(c.comment)}</title>`;
            s += `<rect class="row-bg" x="1" y="${y}" width="${w - 2}" height="${ROW}" fill="${hi ? '#e3eeff' : (i % 2 ? '#fafbfd' : '#fff')}"/>`;
            if (c.pk) s += `<text x="10" y="${y + 16}" font-size="9.5" font-weight="700" fill="#d4a017">PK</text>`;
            else if (fkSet.has(key)) s += `<text x="10" y="${y + 16}" font-size="9.5" font-weight="700" fill="#2f80ed">FK</text>`;
            else if (c.ix && !c.uq) s += `<text x="10" y="${y + 16}" font-size="9.5" font-weight="700" fill="#7c3aed">IX</text>`;
            s += `<text x="34" y="${y + 16}" font-size="12" fill="#1f2937" font-weight="${c.pk ? 600 : 400}">${esc(c.name)}${c.nn || c.pk ? '' : '<tspan fill="#9aa3b2">?</tspan>'}`
                + `${c.def != null ? `<tspan fill="#b45309" font-size="10.5" font-style="italic" dx="4">= ${esc(defaultLabel(c.def))}</tspan>` : ''}`
                + `${c.comment ? '<tspan fill="#9aa3b2" font-size="10" dx="3">💬</tspan>' : ''}</text>`;
            s += `<text x="${w - 12}" y="${y + 16}" font-size="11" fill="#8a94a6" text-anchor="end" font-family="Consolas, monospace">${esc(c.type)}${c.uq && !c.pk ? ' UQ' : ''}</text>`;
            s += `<circle class="port" data-t="${esc(t.id)}" data-c="${esc(c.id)}" cx="0" cy="${y + ROW / 2}" r="4.5"><title>Tarik ke kolom lain untuk membuat relasi</title></circle>`;
            s += `<circle class="port" data-t="${esc(t.id)}" data-c="${esc(c.id)}" cx="${w}" cy="${y + ROW / 2}" r="4.5"><title>Tarik ke kolom lain untuk membuat relasi</title></circle>`;
            s += `</g>`;
        });
        if (hasAddRow(t)) {
            const y = HEAD + t.columns.length * ROW;
            s += `<g class="add-row" data-t="${esc(t.id)}"><rect class="add-bg" x="1" y="${y}" width="${w - 2}" height="${ADDROW}" fill="#fff"/>`;
            s += `<text x="12" y="${y + 17}" font-size="12" fill="#2f80ed">+ Tambah kolom</text></g>`;
        }
        return s + '</g>';
    }

    // ===== Rute relasi: garis siku yang mencari jalan lewat celah antar tabel (tidak menembus tabel) =====
    const STUB = 24, GAP = 16, BEND = 36, RADIUS = 6, LANE = 9;
    let routes = {}, routeKey = '';
    let buf = { n: 0 };          // buffer A* dipakai ulang supaya tidak alokasi tiap relasi

    // Area tabel yang tampil: inti (tidak boleh ditembus) + bingkai GAP (boleh, tapi mahal)
    function obstacles() {
        return (data?.tables || []).filter((t) => isShown(t.id)).map((t) => {
            const x1 = t.x + W[t.id], y1 = t.y + tableHeight(t);
            return { x0: t.x, y0: t.y, x1, y1, X0: t.x - GAP, Y0: t.y - GAP, X1: x1 + GAP, Y1: y1 + GAP };
        });
    }

    // Arah: 0 = kanan, 1 = bawah, 2 = kiri, 3 = atas
    const DX = [1, 0, -1, 0], DY = [0, 1, 0, -1];
    const lowerBound = (a, v) => { let lo = 0, hi = a.length; while (lo < hi) { const m = (lo + hi) >> 1; a[m] < v ? (lo = m + 1) : (hi = m); } return lo; };

    // A* di grid yang garisnya dari tepi bingkai tabel; biaya = panjang + penalti tiap belokan
    function routeOrtho(S, sDir, E, eDir, obs) {
        const uniq = (a) => [...new Set(a)].sort((p, q) => p - q);
        const xs = uniq([S.x, E.x, ...obs.flatMap((o) => [o.X0, o.X1])]), ys = uniq([S.y, E.y, ...obs.flatMap((o) => [o.Y0, o.Y1])]);
        const nx = xs.length, ny = ys.length, n = nx * ny;
        if (buf.n < n) buf = { n, node: new Uint8Array(n), h: new Uint8Array(n), v: new Uint8Array(n), g: new Float64Array(n * 4), prev: new Int32Array(n * 4) };
        const { node, h, v, g, prev } = buf;
        node.fill(0, 0, n); h.fill(0, 0, n); v.fill(0, 0, n); g.fill(Infinity, 0, n * 4);

        // Tandai ruas grid: 1 = lewat bingkai tabel, 2 = menembus tabel
        const mark = (x0, y0, x1, y1, lv) => {
            const ia = lowerBound(xs, x0), ib = lowerBound(xs, x1), ja = lowerBound(ys, y0), jb = lowerBound(ys, y1);
            for (let j = Math.max(0, ja - 1); j < jb && j < ny; j++) {
                const yIn = ys[j] > y0 && ys[j] < y1, vIn = j + 1 < ny && ys[j] < y1 && ys[j + 1] > y0;
                for (let i = Math.max(0, ia - 1); i < ib && i < nx; i++) {
                    const k = j * nx + i, xIn = xs[i] > x0 && xs[i] < x1;
                    if (yIn && i + 1 < nx && xs[i] < x1 && xs[i + 1] > x0) h[k] = Math.max(h[k], lv);
                    if (xIn && vIn) v[k] = Math.max(v[k], lv);
                    if (xIn && yIn && lv === 2) node[k] = 1;
                }
            }
        };
        obs.forEach((o) => { mark(o.X0, o.Y0, o.X1, o.Y1, 1); mark(o.x0, o.y0, o.x1, o.y1, 2); });

        const si = lowerBound(xs, S.x) + lowerBound(ys, S.y) * nx, ei = lowerBound(xs, E.x) + lowerBound(ys, E.y) * nx;
        const ex = E.x, ey = E.y, hs = [], hf = [];
        const push = (s, f) => {
            let i = hs.length; hs.push(s); hf.push(f);
            while (i > 0) { const p = (i - 1) >> 1; if (hf[p] <= f) break; hs[i] = hs[p]; hf[i] = hf[p]; i = p; }
            hs[i] = s; hf[i] = f;
        };
        const pop = () => {
            const s = hs[0], f = hf[0], ls = hs.pop(), lf = hf.pop();
            if (hs.length) {
                let i = 0;
                for (;;) {
                    let c = 2 * i + 1; if (c >= hs.length) break;
                    if (c + 1 < hs.length && hf[c + 1] < hf[c]) c++;
                    if (hf[c] >= lf) break;
                    hs[i] = hs[c]; hf[i] = hf[c]; i = c;
                }
                hs[i] = ls; hf[i] = lf;
            }
            return [s, f];
        };
        const hDist = (k) => Math.abs(xs[k % nx] - ex) + Math.abs(ys[(k / nx) | 0] - ey);

        const s0 = si * 4 + sDir;
        g[s0] = 0; prev[s0] = -1; push(s0, hDist(si));
        let best = Infinity, bestState = -1;
        while (hs.length) {
            const [s, f] = pop();
            if (f >= best) break;
            const k = s >> 2, d = s & 3, gs = g[s];
            if (f > gs + hDist(k) + 1e-6) continue;
            if (k === ei) {
                const total = gs + (d === eDir ? 0 : (d === (eDir ^ 2) ? BEND * 2 : BEND));
                if (total < best) { best = total; bestState = s; }
                continue;
            }
            const i = k % nx, j = (k / nx) | 0;
            for (let nd = 0; nd < 4; nd++) {
                if (nd === (d ^ 2)) continue;
                const i2 = i + DX[nd], j2 = j + DY[nd];
                if (i2 < 0 || j2 < 0 || i2 >= nx || j2 >= ny) continue;
                const k2 = j2 * nx + i2;
                const lv = nd === 0 ? h[k] : nd === 2 ? h[k2] : nd === 1 ? v[k] : v[k2];
                if (lv === 2 || (node[k2] && k2 !== ei)) continue;
                const len = Math.abs(xs[i2] - xs[i]) + Math.abs(ys[j2] - ys[j]);
                const gn = gs + len * (lv ? 3 : 1) + (nd === d ? 0 : BEND), s2 = k2 * 4 + nd;
                if (gn < g[s2]) { g[s2] = gn; prev[s2] = s; push(s2, gn + hDist(k2)); }
            }
        }
        if (bestState < 0) return null;
        const pts = [];
        for (let s = bestState; s >= 0; s = prev[s]) { const k = s >> 2; pts.push([xs[k % nx], ys[(k / nx) | 0]]); }
        return { pts: pts.reverse(), cost: best };
    }

    // Buang titik yang segaris supaya tiap ruas = satu garis lurus
    function simplify(pts) {
        const out = [];
        pts.forEach((p) => {
            if (out.length && out[out.length - 1][0] === p[0] && out[out.length - 1][1] === p[1]) return;
            if (out.length >= 2) {
                const a = out[out.length - 2], b = out[out.length - 1];
                if ((a[0] === b[0] && b[0] === p[0]) || (a[1] === b[1] && b[1] === p[1])) out.pop();
            }
            out.push(p);
        });
        return out;
    }

    // Gaya kurva: lengkung langsung antar kolom (tidak menghindari tabel)
    function curveGeometry(A, B, wa, wb, ya, yb) {
        let xa, sa, xb, sb;
        if (A.x + wa + 40 <= B.x) { xa = A.x + wa; sa = 1; xb = B.x; sb = -1; }
        else if (B.x + wb + 40 <= A.x) { xa = A.x; sa = -1; xb = B.x + wb; sb = 1; }
        else { xa = A.x + wa; sa = 1; xb = B.x + wb; sb = 1; }

        let offA, offB;
        if (sa === sb) { const right = Math.max(xa, xb) + 50; offA = right - xa; offB = right - xb; }
        else { offA = offB = Math.max(30, Math.abs(xb - xa) / 2); }

        const p0 = [xa + sa * 18, ya], p1 = [xa + sa * offA, ya], p2 = [xb + sb * offB, yb], p3 = [xb + sb * 18, yb];
        const d = `M${xa} ${ya}H${p0[0]}C${p1[0]} ${p1[1]} ${p2[0]} ${p2[1]} ${p3[0]} ${p3[1]}H${xb}`;
        const mid = [(p0[0] + 3 * p1[0] + 3 * p2[0] + p3[0]) / 8, (p0[1] + 3 * p1[1] + 3 * p2[1] + p3[1]) / 8];
        return { d, xa, ya, sa, xb, yb, sb, mid };
    }

    // Rute satu relasi: pilih sisi tabel (kiri/kanan) yang hasil rutenya paling murah
    function relGeometry(r, obs) {
        const A = tbl(r.from.t), B = tbl(r.to.t);
        if (!A || !B) return null;
        const ia = A.columns.findIndex((c) => c.id === r.from.c), ib = B.columns.findIndex((c) => c.id === r.to.c);
        if (ia < 0 || ib < 0) return null;
        const wa = W[A.id], wb = W[B.id];
        const ya = A.y + HEAD + ia * ROW + ROW / 2, yb = B.y + HEAD + ib * ROW + ROW / 2;
        if (settings.lineStyle === 'curve') return curveGeometry(A, B, wa, wb, ya, yb);
        let sides;
        if (A.x + wa + 40 <= B.x) sides = [[1, -1]];
        else if (B.x + wb + 40 <= A.x) sides = [[-1, 1]];
        else sides = [[1, 1], [-1, -1]];

        let best = null;
        sides.forEach(([sa, sb]) => {
            const xa = sa === 1 ? A.x + wa : A.x, xb = sb === 1 ? B.x + wb : B.x;
            const S = { x: xa + sa * STUB, y: ya }, E = { x: xb + sb * STUB, y: yb };
            const rt = routeOrtho(S, sa === 1 ? 0 : 2, E, sb === 1 ? 2 : 0, obs);
            if (rt && (!best || rt.cost < best.cost)) best = { pts: [[xa, ya], ...rt.pts, [xb, yb]], cost: rt.cost, xa, ya, sa, xb, yb, sb };
        });
        if (!best) {
            // Tidak ada jalan (tabel saling tumpuk): garis siku sederhana
            const [sa, sb] = sides[0];
            const xa = sa === 1 ? A.x + wa : A.x, xb = sb === 1 ? B.x + wb : B.x;
            const mx = sa === sb ? (sa === 1 ? Math.max(xa, xb) : Math.min(xa, xb)) + sa * STUB * 2 : (xa + xb) / 2;
            best = { pts: [[xa, ya], [mx, ya], [mx, yb], [xb, yb]], xa, ya, sa, xb, yb, sb };
        }
        const { parent } = parentChild(r);
        best.pts = simplify(best.pts);
        best.net = parent.t + '|' + parent.c;
        return best;
    }

    // Ruas dari relasi berbeda yang lewat jalur sama digeser supaya tidak menyatu.
    // Relasi dari kolom induk yang sama boleh berbagi jalur (seperti cabang pohon).
    // Lebar lajur memakai ruang kosong di celah sampai 20px dari tabel terdekat.
    function separateLanes(obs) {
        const groups = {};
        Object.values(routes).forEach((g) => {
            if (!g.pts) return;
            const net = g.net;
            for (let k = 1; k < g.pts.length - 2; k++) {
                const a = g.pts[k], b = g.pts[k + 1], hor = a[1] === b[1], c = hor ? a[1] : a[0];
                const lo = hor ? Math.min(a[0], b[0]) : Math.min(a[1], b[1]), hi = hor ? Math.max(a[0], b[0]) : Math.max(a[1], b[1]);
                (groups[(hor ? 'h' : 'v') + c] ??= []).push({ g, k, hor, c, net, lo, hi });
            }
        });
        const shifts = [];
        Object.values(groups).forEach((segs) => {
            segs.sort((p, q) => p.lo - q.lo);
            // Kelompokkan ruas yang saling tumpang tindih
            let cluster = [], end = -Infinity;
            const flush = () => {
                const nets = [...new Set(cluster.map((s) => s.net))];
                if (nets.length > 1) {
                    const { c, hor } = cluster[0], lo = Math.min(...cluster.map((s) => s.lo)), hi = Math.max(...cluster.map((s) => s.hi));
                    let below = -Infinity, above = Infinity;
                    obs.forEach((o) => {
                        const [p0, p1, q0, q1] = hor ? [o.y0, o.y1, o.x0, o.x1] : [o.x0, o.x1, o.y0, o.y1];
                        if (q1 < lo || q0 > hi) return;
                        if (p1 <= c) below = Math.max(below, p1);
                        else if (p0 >= c) above = Math.min(above, p0);
                    });
                    const minC = below + 20, maxC = above - 20;
                    const step = Math.max(2, Math.min(LANE, (maxC - minC) / (nets.length - 1))), width = step * (nets.length - 1);
                    const start = minC <= maxC - width ? Math.min(Math.max(c - width / 2, minC), maxC - width) : c - width / 2;
                    cluster.forEach((s) => shifts.push([s, start + nets.indexOf(s.net) * step - c]));
                }
                cluster = [];
            };
            segs.forEach((s) => { if (s.lo >= end) { flush(); end = -Infinity; } cluster.push(s); end = Math.max(end, s.hi); });
            flush();
        });
        shifts.forEach(([s, off]) => { const ax = s.hor ? 1 : 0; s.g.pts[s.k][ax] += off; s.g.pts[s.k + 1][ax] += off; });
    }

    // Hitung ulang semua rute hanya kalau posisi/ukuran tabel atau relasinya berubah
    function computeRoutes() {
        const shown = (data?.tables || []).filter((t) => isShown(t.id));
        const key = shown.map((t) => `${t.id},${t.x},${t.y},${W[t.id]},${tableHeight(t)},${t.columns.map((c) => c.id).join('/')}`).join(';')
            + '#' + (data?.relations || []).map((r) => `${r.id},${r.from.t},${r.from.c},${r.to.t},${r.to.c},${r.card}`).join(';') + '#' + settings.lineStyle;
        if (key === routeKey) return;
        routeKey = key; routes = {};
        const obs = obstacles();
        (data?.relations || []).forEach((r) => {
            if (!isShown(r.from.t) || !isShown(r.to.t)) return;
            const g = relGeometry(r, obs);
            if (g) routes[r.id] = g;
        });
        separateLanes(obs);
    }

    // Warna garis per kolom induk: tetap sama walau diagram berubah (dari hash nama kolom)
    const LINE_COLORS = ['#2563eb', '#dc2626', '#16a34a', '#d97706', '#7c3aed', '#0891b2', '#db2777', '#65a30d', '#ea580c', '#4f46e5', '#0d9488', '#a16207'];
    function lineColor(r) {
        const { parent } = parentChild(r), key = parent.t + '|' + parent.c;
        let h = 0;
        for (let i = 0; i < key.length; i++) h = (h * 31 + key.charCodeAt(i)) | 0;
        return LINE_COLORS[Math.abs(h) % LINE_COLORS.length];
    }

    // Sorot garis yang terkait dengan yang sedang di-hover / dipilih, garis lain dipudarkan
    let hoverHl = null;          // {r} relasi, {t, c} kolom, atau {t} tabel
    function applyHighlight() {
        let h = exporting ? null : hoverHl || (sel?.kind === 'rel' ? { r: sel.id } : sel?.kind === 'table' ? { t: sel.id, c: sel.col } : null);
        const match = (hh) => new Set((data?.relations || []).filter((r) => (hh.r != null
            ? String(r.id) === String(hh.r)
            : [r.from, r.to].some((e) => e.t === hh.t && (hh.c == null || e.c === hh.c)))).map((r) => String(r.id)));
        let on = h ? match(h) : new Set();
        if (h && h.c != null && !on.size) on = match({ t: h.t });   // kolom tanpa relasi: sorot relasi tabelnya
        svg.classList.toggle('hl', on.size > 0);
        relLayer.querySelectorAll('.rel').forEach((el) => {
            const o = on.has(el.dataset.r);
            el.classList.toggle('on', o);
            if (o) relLayer.appendChild(el);   // bawa ke depan supaya tidak tertutup garis lain
        });
    }

    // Path SVG dengan sudut sedikit membulat + titik tengah (untuk label)
    function routePath(pts) {
        let d = `M${pts[0][0]} ${pts[0][1]}`;
        for (let i = 1; i < pts.length - 1; i++) {
            const [ax, ay] = pts[i - 1], [bx, by] = pts[i], [cx, cy] = pts[i + 1];
            const l1 = Math.abs(bx - ax) + Math.abs(by - ay), l2 = Math.abs(cx - bx) + Math.abs(cy - by), rr = Math.min(RADIUS, l1 / 2, l2 / 2);
            const ux = Math.sign(bx - ax), uy = Math.sign(by - ay), vx = Math.sign(cx - bx), vy = Math.sign(cy - by);
            d += `L${bx - ux * rr} ${by - uy * rr}Q${bx} ${by} ${bx + vx * rr} ${by + vy * rr}`;
        }
        const last = pts[pts.length - 1];
        d += `L${last[0]} ${last[1]}`;

        const segLen = (i) => Math.abs(pts[i + 1][0] - pts[i][0]) + Math.abs(pts[i + 1][1] - pts[i][1]);
        let half = pts.slice(1).reduce((s, _, i) => s + segLen(i), 0) / 2, mid = pts[0];
        for (let i = 0; i < pts.length - 1; i++) {
            const l = segLen(i);
            if (half <= l) { const t = l ? half / l : 0; mid = [pts[i][0] + (pts[i + 1][0] - pts[i][0]) * t, pts[i][1] + (pts[i + 1][1] - pts[i][1]) * t]; break; }
            half -= l;
        }
        return { d, mid };
    }

    // Notasi crow's foot: "1" = dua garis, "N" = kaki gagak + satu garis
    function symbol(x, y, s, kind) {
        if (kind === '1') return `M${x + s * 9} ${y - 6}V${y + 6}M${x + s * 13} ${y - 6}V${y + 6}`;
        return `M${x + s * 13} ${y}L${x} ${y - 7}M${x + s * 13} ${y}L${x} ${y + 7}M${x + s * 13} ${y}L${x} ${y}M${x + s * 16} ${y - 6}V${y + 6}`;
    }

    function relSvg(r) {
        if (!isShown(r.from.t) || !isShown(r.to.t)) return '';
        const g = routes[r.id];
        if (!g) return '';
        if (g.pts) Object.assign(g, routePath(g.pts));
        const isSel = sel?.kind === 'rel' && sel.id === r.id;
        const color = settings.lineColor ? lineColor(r) : (isSel ? '#2f80ed' : (r.source === 'db' ? '#475569' : '#94a3b8'));
        const [ca, cb] = r.card.split(':');
        let s = `<g class="rel" data-r="${esc(r.id)}">`;
        s += `<path d="${g.d}" stroke="transparent" stroke-width="14" fill="none"/>`;
        s += `<path class="rel-line" d="${g.d}" stroke="${color}" stroke-width="${isSel ? 2.2 : 1.5}" fill="none"${r.optional ? ' stroke-dasharray="6 4"' : ''}/>`;
        s += `<path class="rel-end" d="${symbol(g.xa, g.ya, g.sa, ca)}${symbol(g.xb, g.yb, g.sb, cb)}" stroke="${color}" stroke-width="1.5" fill="none"/>`;
        if (r.label) {
            const lw = textWidth(r.label, '11px Segoe UI, Arial') + 14;
            s += `<rect x="${g.mid[0] - lw / 2}" y="${g.mid[1] - 9}" width="${lw}" height="18" rx="9" fill="#fff" stroke="${color}"/>`;
            s += `<text x="${g.mid[0]}" y="${g.mid[1] + 4}" font-size="11" fill="#374151" text-anchor="middle">${esc(r.label)}</text>`;
        }
        return s + '</g>';
    }

    function renderEmptyState() {
        const box = document.getElementById('emptyState'), card = document.getElementById('emptyCard');
        let html = '';
        if (!connections.length) {
            html = `<div class="ic"><i class="fa-solid fa-plug-circle-xmark"></i></div><h5>Belum ada koneksi MySQL</h5>
                <p>Tambahkan koneksi MySQL di <code>config/database.php</code>, lalu refresh halaman ini.</p>`;
        } else if (!data) {
            html = `<div class="ic"><i class="fa-solid fa-diagram-project"></i></div><h5>Pilih atau buat diagram</h5>
                <p>Setiap koneksi database punya satu diagram. Pilih diagram di kiri, atau buat diagram baru.</p>
                <button class="btn btn-sb" data-empty="new"><i class="fa-solid fa-plus"></i> Diagram Baru</button>`;
        } else if (!data.tables.length) {
            html = `<div class="ic"><i class="fa-solid fa-table"></i></div><h5>Mulai buat ERD</h5>
                <p>Database <code>${esc(data.database)}</code> masih kosong. Klik dua kali di kanvas atau tombol di bawah untuk membuat tabel draft.</p>
                <button class="btn btn-sb" data-empty="table"><i class="fa-solid fa-plus"></i> Buat Tabel Pertama</button>`;
        } else if (!data.tables.some((t) => isShown(t.id))) {
            html = `<div class="ic"><i class="fa-regular fa-eye-slash"></i></div><h5>Semua tabel disembunyikan</h5>
                <p>Pilih tabel yang ingin dilihat lewat ikon <i class="fa-regular fa-eye"></i> di daftar kiri, atau tampilkan semuanya.</p>
                <button class="btn btn-sb" data-empty="showall"><i class="fa-regular fa-eye"></i> Tampilkan Semua</button>`;
        }
        card.innerHTML = html;
        box.style.display = html ? '' : 'none';
    }

    // ===== Sidebar, toolbar, status =====
    function renderTableList() {
        const q = document.getElementById('tableSearch').value.toLowerCase();
        const items = [...(data?.tables || [])].sort((a, b) => a.name.localeCompare(b.name)).filter((t) => t.name.toLowerCase().includes(q));
        document.getElementById('tableList').innerHTML = items.map((t) => `
            <div class="table-item ${sel?.kind === 'table' && sel.id === t.id ? 'active' : ''} ${isShown(t.id) ? '' : 'is-hidden'}" data-t="${esc(t.id)}">
                <button class="eye" data-eye="${esc(t.id)}" title="${isShown(t.id) ? 'Sembunyikan dari kanvas' : 'Tampilkan di kanvas'}"><i class="fa-regular ${isShown(t.id) ? 'fa-eye' : 'fa-eye-slash'}"></i></button>
                <span class="dot" style="background:${t.color}"></span>
                <div class="info" title="${esc(t.name)}">
                    <span class="nm">${esc(t.name)}</span>
                    <div class="meta"><span class="cnt">${t.columns.length} kolom</span>${t.draft ? '<span class="badge-draft">DRAFT</span>' : ''}</div>
                </div>
            </div>`).join('') || `<div class="text-muted small p-2">${data ? 'Tidak ada tabel' : ''}</div>`;

        const total = data?.tables.length || 0, shown = data ? data.tables.filter((t) => isShown(t.id)).length : 0;
        document.getElementById('viewTools').classList.toggle('d-none', !total);
        document.getElementById('viewCount').textContent = `${shown} dari ${total} tampil`;
        document.getElementById('btnViewMatch').classList.toggle('d-none', !q);
    }

    function renderToolbar() {
        const has = !!data;
        ['diagramName', 'btnAddTable', 'btnArrange', 'btnRefresh', 'btnExport', 'btnDeleteDiagram'].forEach((id) => (document.getElementById(id).disabled = !has));
        const drafts = data ? data.tables.filter((t) => t.draft).length : 0;
        document.getElementById('btnApply').disabled = !drafts;
        const count = document.getElementById('draftCount');
        count.textContent = drafts;
        count.classList.toggle('d-none', !drafts);
        const badge = document.getElementById('connBadge');
        badge.classList.toggle('d-none', !has);
        if (has) badge.innerHTML = `<i class="fa-solid fa-plug"></i> ${esc(data.connection)} &middot; ${esc(data.database)}`;
        const nameInput = document.getElementById('diagramName');
        if (document.activeElement !== nameInput) nameInput.value = data?.name || '';
        const undoBtn = document.getElementById('btnUndo');
        undoBtn.disabled = !has || !undoInfo.last;
        undoBtn.title = undoInfo.last ? `Batalkan: ${undoInfo.last.replace(/`/g, '')} (Ctrl+Z)` : 'Belum ada aksi Anda yang bisa dibatalkan';
    }

    function avatarColor(name) {
        let h = 0;
        for (const ch of String(name)) h = (h * 31 + ch.charCodeAt(0)) % 360;
        return `hsl(${h} 55% 42%)`;
    }
    const avatar = (name, size = 26) => `<span class="av" style="background:${avatarColor(name)};width:${size}px;height:${size}px" title="${esc(name)}">${esc(String(name).slice(0, 2))}</span>`;

    function renderOnline() {
        document.getElementById('onlineAvatars').innerHTML = online.map((u) => avatar(u)).join('');
    }

    function renderStatus() {
        const tables = data?.tables || [];
        const drafts = tables.filter((t) => t.draft).length;
        document.getElementById('statTables').innerHTML = data ? `<i class="fa-solid fa-table"></i> ${tables.length - drafts} tabel di database${drafts ? ` &middot; <span style="color:var(--draft)">${drafts} draft</span>` : ''}` : '';
        document.getElementById('statRels').innerHTML = data ? `<i class="fa-solid fa-link"></i> ${data.relations.length} relasi` : '';
        document.getElementById('statSync').innerHTML = !data ? '' : syncError
            ? `<span class="sync-err"><i class="fa-solid fa-triangle-exclamation"></i> ${esc(syncError)}</span>`
            : (pendingOps ? '<i class="fa-solid fa-spinner fa-spin"></i> Menyimpan...' : (lastSync ? `<i class="fa-solid fa-check"></i> Tersinkron ${lastSync.toLocaleTimeString('id-ID')}` : ''));
    }

    // ===== Panel properti & riwayat =====
    function initSelect2(scope) {
        $(scope).find('select.s2').each(function () {
            $(this).select2({ theme: 'bootstrap4', width: '100%', tags: $(this).hasClass('s2-tags') });
        });
    }

    function renderPanel() {
        $(panel).find('select.s2').each(function () { if ($(this).data('select2')) $(this).select2('destroy'); });
        document.querySelectorAll('.panel-tabs button').forEach((b) => b.classList.toggle('active', b.dataset.tab === panelTab));

        if (panelTab === 'history') panel.innerHTML = historyPanel();
        else if (sel?.kind === 'table' && tbl(sel.id)) panel.innerHTML = tablePanel(tbl(sel.id));
        else if (sel?.kind === 'rel' && rel(sel.id)) panel.innerHTML = relPanel(rel(sel.id));
        else { sel = null; panel.innerHTML = helpPanel(); }

        initSelect2(panel);
        const active = panel.querySelector('.colcard.active');
        if (active) active.scrollIntoView({ block: 'nearest' });
    }

    function helpPanel() {
        return `
            <div class="p-title"><i class="fa-solid fa-circle-info"></i> Cara Pakai <button class="btn btn-sm btn-outline-secondary ms-auto py-0" data-act="guide"><i class="fa-regular fa-circle-question"></i> Mulai panduan</button></div>
            <ul class="tips ps-3 mb-3">
                <li><b>Klik dua kali</b> area kosong untuk membuat tabel <span class="badge-draft">DRAFT</span>. Kolom standar otomatis ditambahkan (atur di <i class="fa-solid fa-gear"></i>).</li>
                <li>Klik <b>+ Tambah kolom</b>, ketik lalu <kbd>Enter</kbd>, langsung lanjut kolom berikutnya.</li>
                <li><b>Klik dua kali</b> nama tabel / kolom draft untuk mengubahnya.</li>
                <li>Relasi: tarik <b>titik bulat</b> di sisi kolom ke kolom lain, atau pakai <b>Tambah FK ke</b> di panel tabel draft.</li>
                <li>Klik <b>▶</b> di header tabel draft (atau <b>Buat di DB</b>) untuk membuat tabelnya di database.</li>
                <li>Tabel yang sudah ada di database <b>dikunci</b>. Ubah strukturnya langsung di database, diagram mengikuti otomatis.</li>
                <li><kbd>Ctrl</kbd>+<kbd>Z</kbd> membatalkan aksi Anda sendiri. <kbd>Delete</kbd> menghapus draft/relasi.</li>
            </ul>
            <div class="p-label">Cara mengetik kolom</div>
            <div class="small">
                <code>nama tipe penanda =default "keterangan"</code>
                <table class="table table-sm small mt-1 mb-2">
                    <tr><td><code>qty int nn =0</code></td><td>INT, wajib isi, default 0</td></tr>
                    <tr><td><code>kode varchar(50) uq</code></td><td>unik</td></tr>
                    <tr><td><code>tgl date ix</code></td><td>diberi INDEX</td></tr>
                    <tr><td><code>status varchar(20) =aktif "status order"</code></td><td>default + keterangan</td></tr>
                    <tr><td><code>dibuat timestamp =now</code></td><td>default waktu sekarang</td></tr>
                </table>
                Penanda: <b>pk</b> primary key, <b>nn</b> wajib isi, <b>uq</b> unik, <b>ix</b> index, <b>ai</b> auto increment. Tipe boleh dikosongkan, ditebak dari nama kolom.
            </div>
            <div class="p-label">Notasi</div>
            <svg width="300" height="70" font-family="Segoe UI, Arial" font-size="11">
                <path d="M10 18H70${symbol(10, 18, 1, '1')}" stroke="#475569" stroke-width="1.5" fill="none"/>
                <text x="82" y="22" fill="#374151">Satu (wajib tepat satu)</text>
                <path d="M10 50H70${symbol(10, 50, 1, 'N')}" stroke="#475569" stroke-width="1.5" fill="none"/>
                <text x="82" y="54" fill="#374151">Banyak (satu atau lebih)</text>
            </svg>
            <div class="p-label">Keterangan</div>
            <div class="small text-muted"><b style="color:#d4a017">PK</b> primary key &middot; <b style="color:#2f80ed">FK</b> foreign key &middot; <b style="color:#7c3aed">IX</b> index &middot; <b>?</b> boleh kosong &middot; <b>UQ</b> unik &middot; <span style="color:#b45309"><i>= nilai</i></span> default &middot; 💬 ada keterangan<br>
            Garis gelap = FK di database, garis abu = relasi di diagram.</div>`;
    }

    function ago(iso) {
        const d = new Date(String(iso).replace(' ', 'T')), s = Math.max(0, (Date.now() - d) / 1000);
        if (s < 60) return 'baru saja';
        if (s < 3600) return Math.floor(s / 60) + ' mnt lalu';
        if (s < 86400) return Math.floor(s / 3600) + ' jam lalu';
        return d.toLocaleString('id-ID', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' });
    }
    const formatDesc = (s) => esc(s).replace(/`([^`]+)`/g, '<code>$1</code>');

    function historyPanel() {
        if (!data) return '<div class="text-muted">Pilih diagram dulu.</div>';
        const onlineHtml = online.length
            ? online.map((u) => `<span class="online-chip">${avatar(u, 22)}${esc(u)}${u === ME ? ' <span class="text-muted">(Anda)</span>' : ''}</span>`).join('')
            : '<span class="text-muted small">-</span>';
        const acts = activities.map((a) => `
            <div class="act">
                ${a.username ? avatar(a.username) : '<span class="av" style="background:#64748b" title="Database"><i class="fa-solid fa-database"></i></span>'}
                <div>
                    <div><span class="who">${esc(a.username || 'Database')}</span><span class="time">${ago(a.created_at)}</span>
                        ${undoInfo.activities.includes(a.id) ? `<button class="undo-btn" data-undo="${a.id}" title="Batalkan aksi ini"><i class="fa-solid fa-rotate-left"></i> Batalkan</button>` : ''}</div>
                    <div class="desc">${formatDesc(a.description)}</div>
                </div>
            </div>`).join('');
        return `
            <div class="p-label mt-0">Sedang membuka diagram</div>
            <div class="online-list mb-2">${onlineHtml}</div>
            <div class="p-label">Riwayat perubahan</div>
            ${acts || '<div class="text-muted small">Belum ada riwayat</div>'}`;
    }

    function swatches(t) {
        return `<div class="swatches">${COLORS.map((c) => `<span class="swatch ${c === t.color ? 'active' : ''}" style="background:${c}" data-act="color" data-v="${c}"></span>`).join('')}</div>`;
    }

    function tablePanel(t) {
        const common = `
            <div class="d-flex gap-2 mt-2">
                <button class="btn btn-sm btn-outline-secondary flex-fill" data-act="focus" title="Tampilkan hanya tabel ini dan tabel yang berelasi langsung"><i class="fa-solid fa-crosshairs"></i> Fokus + relasinya</button>
                <button class="btn btn-sm btn-outline-secondary" data-act="hide" title="Sembunyikan dari kanvas (hanya untuk Anda)"><i class="fa-regular fa-eye-slash"></i></button>
            </div>
            <div class="p-label">Warna header</div>${swatches(t)}
            <div class="p-label">Catatan</div>
            <textarea class="form-control form-control-sm" rows="2" data-f="t.note" placeholder="Keterangan tabel (opsional)">${esc(t.note)}</textarea>`;
        const dupBtn = `<button class="btn btn-sm btn-outline-secondary flex-fill" data-act="dup" title="Salin struktur tabel ini menjadi tabel draft baru"><i class="fa-regular fa-copy"></i> Duplikat jadi draft</button>`;

        if (!t.draft) {
            const fks = new Set(data.relations.map((r) => parentChild(r).child).filter((e) => e.t === t.id).map((e) => e.c));
            const cols = t.columns.map((c) => {
                const key = c.pk ? ['PK', '#d4a017'] : fks.has(c.id) ? ['FK', '#2f80ed'] : (c.ix && !c.uq) ? ['IX', '#7c3aed'] : ['', ''];
                const meta = [c.def != null ? `default <b>${esc(c.def)}</b>` : '', c.comment ? `💬 ${esc(c.comment)}` : ''].filter(Boolean).join(' &middot; ');
                return `
                <div class="col-ro">
                    <span class="k" style="color:${key[1]}">${key[0]}</span>
                    <span class="${c.pk ? 'fw-semibold' : ''}">${esc(c.name)}${c.nn || c.pk ? '' : '<span class="text-muted">?</span>'}</span>
                    <span class="ty">${esc(c.type)}${c.uq && !c.pk ? ' UQ' : ''}${c.ai ? ' AI' : ''}</span>
                </div>${meta ? `<div class="col-ro meta border-0">${meta}</div>` : ''}`;
            }).join('');
            return `
                <div class="p-title"><i class="fa-solid fa-table" style="color:${t.color}"></i> ${esc(t.name)} <span class="badge-db ms-auto">DATABASE</span></div>
                <div class="p-info"><i class="fa-solid fa-lock"></i> Tabel ini sudah ada di database <code>${esc(data.database)}</code>. Kolom tidak bisa diubah dari sini. Ubah langsung di database, diagram akan mengikuti otomatis.</div>
                ${common}
                <div class="p-label">Kolom (${t.columns.length})</div>
                <div class="border rounded">${cols}</div>
                <div class="d-flex mt-3">${dupBtn}</div>`;
        }

        // Tambah FK: tabel yang punya tepat satu PK bisa jadi parent
        const parents = data.tables.filter((p) => p.columns.filter((c) => c.pk).length === 1).sort((a, b) => a.name.localeCompare(b.name));
        const fkBox = `
            <div class="fk-box">
                <div class="p-label mt-0"><i class="fa-solid fa-link"></i> Tambah FK ke tabel</div>
                <div class="d-flex gap-1">
                    <div class="flex-fill"><select class="s2" id="fkParent" data-f="fk"><option value="">-- pilih tabel parent --</option>${parents.map((p) => `<option value="${esc(p.id)}">${esc(p.name)}${p.draft ? ' (draft)' : ''}</option>`).join('')}</select></div>
                    <button class="btn btn-sm btn-sb" data-act="addfk"><i class="fa-solid fa-plus"></i></button>
                </div>
                <div class="small text-muted mt-1" id="fkHint">Kolom <code>parent_id</code> (tipe sama dengan PK parent) dan relasi 1:N dibuat otomatis.</div>
            </div>`;

        const typeOpts = (v) => [...new Set([...TYPES, v])].map((x) => `<option ${x === v ? 'selected' : ''}>${esc(x)}</option>`).join('');
        const cols = t.columns.map((c, i) => `
            <div class="colcard ${sel.col === c.id ? 'active' : ''}" data-c="${esc(c.id)}">
                <div class="d-flex gap-1">
                    <input class="form-control form-control-sm name-input" data-f="c.name" value="${esc(c.name)}" placeholder="nama_kolom">
                    <div class="type-wrap"><select class="s2 s2-tags" data-f="c.type">${typeOpts(c.type)}</select></div>
                </div>
                <div class="sub">
                    <input class="form-control form-control-sm def" data-f="c.def" value="${esc(c.def ?? '')}" placeholder="default" title="Nilai default. Ketik now untuk waktu sekarang, NULL untuk null.">
                    <input class="form-control form-control-sm" data-f="c.comment" value="${esc(c.comment || '')}" placeholder="keterangan (COMMENT)">
                </div>
                <div class="d-flex align-items-center gap-1 mt-1">
                    <button class="tg pk ${c.pk ? 'on' : ''}" data-act="tog" data-k="pk" title="Primary key">PK</button>
                    <button class="tg ${c.nn || c.pk ? 'on' : ''}" data-act="tog" data-k="nn" title="Wajib isi (NOT NULL)">NN</button>
                    <button class="tg ${c.uq ? 'on' : ''}" data-act="tog" data-k="uq" title="Unik">UQ</button>
                    <button class="tg ix ${c.ix ? 'on' : ''}" data-act="tog" data-k="ix" title="Index (pencarian lebih cepat)">IX</button>
                    <button class="tg ${c.ai ? 'on' : ''}" data-act="tog" data-k="ai" title="Auto increment">AI</button>
                    <span class="ms-auto"></span>
                    <button class="ic-btn" data-act="up" title="Naik" ${i === 0 ? 'disabled' : ''}><i class="fa-solid fa-arrow-up"></i></button>
                    <button class="ic-btn" data-act="down" title="Turun" ${i === t.columns.length - 1 ? 'disabled' : ''}><i class="fa-solid fa-arrow-down"></i></button>
                    <button class="ic-btn danger" data-act="delcol" title="Hapus kolom"><i class="fa-solid fa-trash"></i></button>
                </div>
            </div>`).join('');

        return `
            <div class="p-title"><i class="fa-solid fa-table" style="color:${t.color}"></i> Tabel Draft <span class="badge-draft ms-auto">BELUM DI DATABASE</span></div>
            <div class="p-label">Nama tabel</div>
            <input class="form-control form-control-sm" data-f="t.name" value="${esc(t.name)}">
            ${common}
            <div class="p-label">Kolom (${t.columns.length})</div>
            <div class="mt-1">${cols}</div>
            <div class="quick-add">
                <i class="fa-solid fa-plus"></i>
                <input class="form-control form-control-sm" id="quickCol" placeholder="Ketik nama kolom lalu Enter" autocomplete="off" spellcheck="false">
            </div>
            <div class="small text-muted mt-1">Contoh: <code>qty int nn =0 "jumlah order"</code>. Bisa tempel banyak baris sekaligus.</div>
            ${fkBox}
            <div class="d-flex gap-2 mt-3">${dupBtn}</div>
            <div class="d-flex gap-2 mt-2">
                <button class="btn btn-sm btn-sb flex-fill" data-act="apply"><i class="fa-solid fa-database"></i> Buat di Database</button>
                <button class="btn btn-sm btn-outline-danger" data-act="deltable" title="Hapus draft"><i class="fa-solid fa-trash"></i></button>
            </div>`;
    }

    function columnOptions(selected) {
        return data.tables.map((t) => `<optgroup label="${esc(t.name)}">${t.columns.map((c) => {
            const v = t.id + '|' + c.id;
            return `<option value="${esc(v)}" ${v === selected ? 'selected' : ''}>${esc(t.name)}.${esc(c.name)}</option>`;
        }).join('')}</optgroup>`).join('');
    }

    function relSentence(r) {
        const A = tbl(r.from.t), B = tbl(r.to.t);
        const a = `<b>${esc(A.name)}</b>`, b = `<b>${esc(B.name)}</b>`;
        const txt = {
            '1:1': `Satu ${a} memiliki tepat satu ${b}.`,
            '1:N': `Satu ${a} memiliki banyak ${b}.`,
            'N:1': `Banyak ${a} dimiliki oleh satu ${b}.`,
            'N:N': `Banyak ${a} berelasi dengan banyak ${b} (butuh tabel pivot).`,
        }[r.card];
        return `${txt}<div class="small text-muted mt-1"><code>${esc(A.name)}.${esc(r.from.c)}</code> &rarr; <code>${esc(B.name)}.${esc(r.to.c)}</code></div>`;
    }

    function relFate(r) {
        const { child } = parentChild(r);
        if (r.card === 'N:N') return '<i class="fa-solid fa-circle-info"></i> Relasi N:N tidak dibuat sebagai FK. Buat tabel pivot untuk menyimpannya.';
        if (tbl(child.t)?.draft) return `<i class="fa-solid fa-database"></i> Akan dibuat sebagai <b>FOREIGN KEY</b> saat tabel <code>${esc(child.t)}</code> diterapkan ke database.`;
        return '<i class="fa-solid fa-circle-info"></i> Hanya dokumentasi di diagram. Tabel pemilik FK sudah ada di database, jadi FK tidak dibuat otomatis.';
    }

    function relPanel(r) {
        if (r.source === 'db') {
            return `
                <div class="p-title"><i class="fa-solid fa-link"></i> Relasi <span class="badge-db ms-auto">FK DATABASE</span></div>
                <div class="p-info">${relSentence(r)}</div>
                <div class="p-info"><i class="fa-solid fa-lock"></i> FOREIGN KEY <code>${esc(r.constraint)}</code> sudah ada di database. Ubah atau hapus langsung di database.</div>`;
        }
        return `
            <div class="p-title"><i class="fa-solid fa-link"></i> Properti Relasi</div>
            <div class="p-label">Dari kolom</div>
            <select class="s2" data-f="r.from">${columnOptions(r.from.t + '|' + r.from.c)}</select>
            <div class="p-label">Kardinalitas</div>
            <div class="btn-group btn-group-sm w-100 card-btns">
                ${Object.keys(CARDS).map((k) => `<button class="btn btn-outline-secondary ${r.card === k ? 'active' : ''}" data-act="card" data-v="${k}" title="${CARDS[k]}">${k}</button>`).join('')}
            </div>
            <div class="p-label">Ke kolom</div>
            <select class="s2" data-f="r.to">${columnOptions(r.to.t + '|' + r.to.c)}</select>
            <div class="p-label">Label (opsional)</div>
            <input class="form-control form-control-sm" data-f="r.label" value="${esc(r.label)}" placeholder="mis. memiliki">
            <div class="form-check mt-2">
                <input class="form-check-input" type="checkbox" id="relOptional" data-act="optional" ${r.optional ? 'checked' : ''}>
                <label class="form-check-label" for="relOptional">Relasi opsional (garis putus-putus)</label>
            </div>
            <div class="p-info">${relSentence(r)}</div>
            <div class="p-info warn">${relFate(r)}</div>
            <div class="d-flex gap-2 mt-3">
                <button class="btn btn-sm btn-outline-secondary flex-fill" data-act="swap"><i class="fa-solid fa-right-left"></i> Tukar Arah</button>
                <button class="btn btn-sm btn-outline-danger flex-fill" data-act="delrel"><i class="fa-solid fa-trash"></i> Hapus Relasi</button>
            </div>`;
    }

    // ===== Server: setiap perubahan dikirim sebagai satu aksi, antre berurutan =====
    function send(op) {
        if (!data) return;
        const id = data.id;
        pendingOps++;
        renderStatus();
        opQueue = opQueue.then(() => new Promise((resolve) => {
            $.ajax({ url: ROUTES.op.replace(':id', id) + '?after=' + lastActivityId, type: 'post', contentType: 'application/json', data: JSON.stringify(op) })
                .done((res) => { pendingOps--; if (!pendingOps && data?.id === id) applyState(res); })
                .fail((x) => { pendingOps--; toast(errMsg(x), 'error'); if (!pendingOps) refresh(true); })
                .always(() => { renderStatus(); resolve(); });
        }));
    }

    function refresh(force = false) {
        if (!data) return;
        const busy = pendingOps || drag || conn || editing || panel.contains(document.activeElement) || $('.select2-container--open').length;
        if (!force && busy) return;
        const id = data.id;
        // force (tombol sinkron / setelah gagal simpan) = selalu ambil diagram penuh, buang perubahan lokal yang tertolak
        $.get(ROUTES.state.replace(':id', id), { rev: force ? '' : revision, after: lastActivityId })
            .done((res) => { if (!pendingOps && data?.id === id) applyState(res); })
            .fail((x) => { syncError = errMsg(x); renderStatus(); });
    }

    // res.diagram = null artinya diagram tidak berubah sejak revisi terakhir: cukup perbarui online & riwayat baru
    function applyState(res) {
        const panelFocused = panel.contains(document.activeElement);
        const newActivities = res.activities || [];
        if (newActivities.length) {
            const known = new Set(activities.map((a) => a.id));
            activities = newActivities.filter((a) => !known.has(a.id)).concat(activities).slice(0, 100);
            lastActivityId = Math.max(lastActivityId, ...newActivities.map((a) => a.id));
        }
        online = res.online || [];
        undoInfo = res.undo || undoInfo;
        lastSync = new Date();
        syncError = null;
        renderOnline();

        if (!res.diagram) {
            renderStatus();
            renderToolbar();
            if (newActivities.length && panelTab === 'history' && !panelFocused) renderPanel();
            return;
        }

        data = res.diagram;
        revision = res.revision;
        if (sel && !(sel.kind === 'table' ? tbl(sel.id) : rel(sel.id))) sel = null;
        if (sel?.kind === 'table' && sel.col && !col(tbl(sel.id), sel.col)) sel.col = null;
        render();
        if (!panelFocused) renderPanel();
    }

    setInterval(() => { if (!document.hidden) refresh(); }, POLL_MS);
    window.addEventListener('focus', () => refresh());

    // ===== Diagram: daftar, buka, buat, hapus =====
    function loadList() {
        return $.get(ROUTES.list).done((res) => {
            diagrams = res.diagrams;
            connections = res.connections;
            renderDiagramSelect();
            render();
        }).fail((x) => toast(errMsg(x), 'error'));
    }

    function renderDiagramSelect() {
        const $sel = $('#diagramSelect');
        const options = ['<option value="">-- Pilih diagram --</option>']
            .concat(diagrams.map((d) => `<option value="${d.id}" ${data && d.id === data.id ? 'selected' : ''}>${esc(d.name)} (${esc(d.connection)})</option>`));
        $sel.html(options.join(''));
        if (!$sel.data('select2')) $sel.select2({ theme: 'bootstrap4', width: '100%' });
        else $sel.trigger('change.select2');
    }

    function openDiagram(id) {
        closeEditor();
        sel = null;
        data = null; activities = []; online = []; revision = null; lastActivityId = 0; hidden = new Set();
        if (!id) { setUrl(null); render(); renderOnline(); renderPanel(); return; }
        $.get(ROUTES.state.replace(':id', id)).done((res) => {
            data = res.diagram;
            loadHidden();
            applyState(res);
            renderDiagramSelect();
            setUrl(id);
            requestAnimationFrame(fit);
        }).fail((x) => { toast(errMsg(x), 'error'); setUrl(null); renderDiagramSelect(); });
    }

    function setUrl(id) {
        const url = new URL(location.href);
        id ? url.searchParams.set('d', id) : url.searchParams.delete('d');
        history.replaceState(null, '', url);
    }

    $('#diagramSelect').on('change', function () {
        const id = Number(this.value) || null;
        if (id !== (data?.id ?? null)) openDiagram(id);
    });

    const newModal = new bootstrap.Modal(document.getElementById('newDiagramModal'));
    $('#newConnection').select2({ theme: 'bootstrap4', width: '100%', dropdownParent: $('#newDiagramModal') });

    function showNewDiagram() {
        if (!connections.length) { toast('Belum ada koneksi MySQL di config/database.php', 'warning'); return; }
        const free = connections.filter((c) => !c.diagram_id);
        $('#newConnection').html('<option value="">-- Pilih koneksi --</option>' + connections.map((c) =>
            `<option value="${esc(c.name)}" ${c.diagram_id ? 'disabled' : ''}>${esc(c.name)} - ${esc(c.database)} @ ${esc(c.host)}${c.diagram_id ? ' - sudah punya diagram' : ''}</option>`).join(''));
        $('#newConnection').val(free.length === 1 ? free[0].name : '').trigger('change');
        $('#newName').val('');
        newModal.show();
    }

    $('#newConnection').on('change', function () {
        const c = connections.find((x) => x.name === this.value);
        if (c && !$('#newName').val()) $('#newName').val('ERD ' + c.database);
    });

    document.getElementById('btnCreateDiagram').addEventListener('click', () => {
        const connection = $('#newConnection').val(), name = $('#newName').val().trim();
        if (!connection) return toast('Pilih koneksi database dulu', 'warning');
        if (!name) return toast('Nama diagram wajib diisi', 'warning');
        $.post(ROUTES.create, { connection, name })
            .done((res) => { newModal.hide(); toast(res.message, 'success'); loadList().done(() => openDiagram(res.id)); })
            .fail((x) => toast(errMsg(x), 'error'));
    });

    document.getElementById('btnNewDiagram').addEventListener('click', showNewDiagram);
    document.getElementById('btnDeleteDiagram').addEventListener('click', async () => {
        const res = await Swal.fire({
            icon: 'warning', title: 'Hapus diagram?',
            html: `Diagram <b>${esc(data.name)}</b> beserta riwayatnya akan dihapus.<br>Tabel di database <code>${esc(data.database)}</code> <b>tidak</b> ikut terhapus.`,
            showCancelButton: true, confirmButtonText: 'Hapus', cancelButtonText: 'Batal', confirmButtonColor: '#c62828',
        });
        if (!res.isConfirmed) return;
        $.post(ROUTES.delete, { id: data.id })
            .done((r) => { toast(r.message, 'success'); openDiagram(null); loadList(); })
            .fail((x) => toast(errMsg(x), 'error'));
    });

    // ===== Ketik kolom: "nama [tipe] [pk nn uq ix ai] [=default] ["keterangan"]" =====
    const TYPE_ALIAS = {
        int: 'INT', integer: 'INT', bigint: 'BIGINT', tinyint: 'TINYINT', varchar: 'VARCHAR(255)', str: 'VARCHAR(255)', string: 'VARCHAR(255)',
        text: 'TEXT', date: 'DATE', datetime: 'DATETIME', timestamp: 'TIMESTAMP', time: 'TIME', decimal: 'DECIMAL(15,2)',
        double: 'DOUBLE', float: 'DOUBLE', bool: 'BOOLEAN', boolean: 'BOOLEAN', json: 'JSON', id: 'BIGINT UNSIGNED', fk: 'BIGINT UNSIGNED',
    };
    const FLAGS = ['pk', 'nn', 'uq', 'ix', 'ai', 'null'];
    // "now" = waktu sekarang (CURRENT_TIMESTAMP); kosong = tanpa default
    const normalizeDefault = (v) => (v == null || v === '' ? null : (/^(now|now\(\)|current_timestamp(\(\))?)$/i.test(v) ? 'CURRENT_TIMESTAMP' : v));

    // Tebak tipe dari nama kolom kalau tipe tidak diketik. id & *_id = BIGINT UNSIGNED supaya FK cocok.
    function guessType(name) {
        const n = name.toLowerCase();
        if (n === 'id' || /_id$|^id_/.test(n)) return 'BIGINT UNSIGNED';
        if (/_at$/.test(n)) return 'TIMESTAMP';
        if (/^(tgl|tanggal)|date/.test(n)) return 'DATE';
        if (/^(is|has)_/.test(n)) return 'BOOLEAN';
        if (/harga|price|amount|nominal/.test(n)) return 'DECIMAL(15,2)';
        if (/^(qty|jumlah|jml|total)/.test(n)) return 'INT';
        if (/keterangan|catatan|note|deskripsi|description|alamat/.test(n)) return 'TEXT';
        return 'VARCHAR(100)';
    }

    function normalizeType(type) {
        return type.trim().replace(/'[^']*'|[^']+/g, (m) => (m[0] === "'" ? m : m.toUpperCase())).replace(/\s+/g, ' ');
    }

    function parseColumn(text) {
        let rest = text.trim(), comment = '', def = null;
        rest = rest.replace(/"([^"]*)"/, (m, v) => { comment = v.trim(); return ' '; });
        rest = rest.replace(/(^|\s)=('([^']*)'|\S*)/, (m, sp, raw, quoted) => { def = quoted !== undefined ? quoted : raw; return ' '; });
        const parts = rest.trim().split(/\s+/), name = parts.shift();
        if (!name) return null;
        const c = { name, type: '', pk: false, nn: false, uq: false, ix: false, ai: false, def: normalizeDefault(def), comment }, typeParts = [];
        parts.forEach((p) => {
            const k = p.toLowerCase();
            if (!FLAGS.includes(k)) typeParts.push(p);
            else if (k !== 'null') c[k] = true;
        });
        const type = typeParts.join(' ');
        c.type = type ? (TYPE_ALIAS[type.toLowerCase()] || normalizeType(type)) : guessType(name);
        if (c.pk) c.nn = true;
        return c;
    }

    const columnText = (c) => [
        c.name, c.type, c.pk && 'pk', c.nn && !c.pk && 'nn', c.uq && 'uq', c.ix && 'ix', c.ai && 'ai',
        c.def != null && '=' + (/\s/.test(c.def) ? `'${c.def}'` : c.def), c.comment && `"${c.comment}"`,
    ].filter(Boolean).join(' ');

    // Cek nama & tipe kolom; return pesan error atau null
    function columnError(c, others) {
        if (!IDENT.test(c.name)) return `Nama kolom "${c.name}" tidak valid (huruf, angka, underscore; tidak diawali angka)`;
        if (others.some((x) => x.name.toLowerCase() === c.name.toLowerCase())) return `Kolom "${c.name}" sudah ada`;
        if (!TYPE_RE.test(c.type)) return `Tipe "${c.type}" tidak dikenal`;
        return null;
    }

    // Kolom baru untuk tabel t; null kalau tidak valid
    function makeColumn(columns, text) {
        const c = parseColumn(text);
        if (!c) return null;
        const err = columnError(c, columns);
        if (err) { toast(err, 'warning'); return null; }
        // "id" saja di tabel tanpa PK -> otomatis PK + auto increment
        if (c.name.toLowerCase() === 'id' && text.trim().split(/\s+/).length === 1 && !columns.some((x) => x.pk)) Object.assign(c, { pk: true, nn: true, ai: true });
        c.id = c.name;
        return c;
    }

    // ===== Perubahan lokal + kirim ke server =====
    const columnsPayload = (t) => t.columns.map(({ name, type, pk, nn, uq, ix, ai, def, comment }) => ({ name, type, pk, nn, uq, ix: !!ix, ai, def: def ?? null, comment: comment || '' }));
    const sendColumns = (t, renames = {}) => send({ type: 'table_update', table: t.id, columns: columnsPayload(t), renames });

    // Posisi kolom baru: sebelum kolom standar di akhir tabel (created_by ... deleted_at)
    function insertIndex(t) {
        let i = t.columns.length;
        while (i > 0 && STD_TAIL.has(t.columns[i - 1].name)) i--;
        return i;
    }

    // Kolom standar tabel baru sesuai pengaturan (tombol gear)
    function standardColumns() {
        const C = (name, type, o = {}) => ({ id: name, name, type, pk: false, nn: false, uq: false, ix: false, ai: false, def: null, comment: '', ...o });
        return [
            settings.stdId && C('id', 'BIGINT UNSIGNED', { pk: true, nn: true, ai: true }),
            settings.stdBy && C('created_by', 'VARCHAR(100)'),
            settings.stdBy && C('updated_by', 'VARCHAR(100)'),
            settings.stdTs && C('created_at', 'TIMESTAMP'),
            settings.stdTs && C('updated_at', 'TIMESTAMP'),
            settings.stdDel && C('deleted_at', 'TIMESTAMP'),
        ].filter(Boolean);
    }

    function uniqueTableName(base) {
        let name = base, i = 2;
        while (data.tables.some((t) => t.name.toLowerCase() === name.toLowerCase())) name = base + '_' + i++;
        return name;
    }

    function renameTable(t, name) {
        name = name.trim().replace(/\s+/g, '_');
        if (name === t.id) return true;
        if (!IDENT.test(name)) { toast(`Nama tabel "${name}" tidak valid`, 'warning'); return false; }
        if (data.tables.some((x) => x !== t && x.name.toLowerCase() === name.toLowerCase())) { toast(`Tabel "${name}" sudah ada`, 'warning'); return false; }
        const old = t.id;
        t.id = t.name = name;
        data.relations.forEach((r) => ['from', 'to'].forEach((e) => { if (r[e].t === old) r[e].t = name; }));
        if (sel?.kind === 'table' && sel.id === old) sel.id = name;
        send({ type: 'table_update', table: old, name });
        return true;
    }

    // Ubah satu kolom draft (termasuk ganti nama); return false kalau tidak valid
    function updateColumn(t, c, next) {
        const err = columnError(next, t.columns.filter((x) => x !== c));
        if (err) { toast(err, 'warning'); return false; }
        const old = c.id, renames = {};
        Object.assign(c, next, { id: next.name });
        if (old !== c.name) {
            renames[old] = c.name;
            data.relations.forEach((r) => ['from', 'to'].forEach((e) => { if (r[e].t === t.id && r[e].c === old) r[e].c = c.name; }));
            if (sel?.col === old) sel.col = c.name;
        }
        sendColumns(t, renames);
        return true;
    }

    function addTable(x, y) {
        if (!data) return;
        const t = {
            id: '', name: '', draft: true, color: COLORS[data.tables.length % COLORS.length], note: '', x: snap(x), y: snap(y),
            columns: standardColumns(),
        };
        t.id = t.name = uniqueTableName('tabel_baru');
        data.tables.push(t);
        send({ type: 'table_create', name: t.name, x: t.x, y: t.y, color: t.color, columns: columnsPayload(t) });
        select({ kind: 'table', id: t.id });
        setTimeout(() => openEditor('table', t.id), 0);
    }

    // Salin struktur tabel (draft atau yang sudah di database) menjadi tabel draft baru
    function duplicateTable(t) {
        const copy = {
            id: '', name: '', draft: true, color: t.color, note: '', x: snap(t.x + 40), y: snap(t.y + 40),
            columns: t.columns.map((c) => ({ ...c, id: c.name })),
        };
        copy.id = copy.name = uniqueTableName(t.name.slice(0, 58) + '_copy');
        data.tables.push(copy);
        send({ type: 'table_create', name: copy.name, x: copy.x, y: copy.y, color: copy.color, columns: columnsPayload(copy) });
        select({ kind: 'table', id: copy.id });
        setTimeout(() => openEditor('table', copy.id), 0);
    }

    // Tambah kolom FK ke tabel parent P (tipe = tipe PK parent) + relasi 1:N
    function addForeignKey(t, P) {
        const pk = P.columns.find((c) => c.pk);
        const name = pk.name === 'id' ? `${P.name}_id` : `${P.name}_${pk.name}`;
        let c = col(t, name);
        if (c && data.relations.some((r) => r.from.t === P.id && r.from.c === pk.id && r.to.t === t.id && r.to.c === c.id)) {
            toast(`Relasi ${t.name}.${name} ke ${P.name} sudah ada`, 'info');
            return;
        }
        if (!c) {
            if (!IDENT.test(name)) { toast(`Nama kolom "${name}" terlalu panjang / tidak valid`, 'warning'); return; }
            c = { id: name, name, type: pk.type, pk: false, nn: true, uq: false, ix: false, ai: false, def: null, comment: '' };
            const at = t.columns.reduce((last, x, i) => (x.pk ? i + 1 : last), 0);   // tepat setelah kolom PK
            t.columns.splice(at, 0, c);
            sendColumns(t);
        }
        addRelation({ t: P.id, c: pk.id }, { t: t.id, c: c.id });
        select({ kind: 'table', id: t.id, col: c.id });
        toast(`Kolom ${name} dan relasi ke ${P.name} dibuat`, 'success');
    }

    // Batalkan aksi milik sendiri: terakhir (Ctrl+Z) atau aksi tertentu dari Riwayat
    async function undo(activityId = null) {
        if (!data) return;
        await opQueue;
        $.ajax({ url: ROUTES.undo.replace(':id', data.id) + '?after=' + lastActivityId, type: 'post', contentType: 'application/json', data: JSON.stringify({ activity_id: activityId }) })
            .done((res) => { applyState(res); toast(res.message.replace(/`/g, ''), 'success'); })
            .fail((x) => toast(errMsg(x).replace(/`/g, ''), 'warning'));
    }

    function addRelation(from, to, card = '1:N') {
        const r = { id: uid('r'), from, to, card, label: '', optional: false, source: 'diagram' };
        data.relations.push(r);
        send({ type: 'relation_create', id: r.id, from, to, card });
        select({ kind: 'rel', id: r.id });
    }

    const sendRelation = (r) => send({ type: 'relation_update', id: r.id, from: r.from, to: r.to, card: r.card, label: r.label, optional: r.optional });

    function deleteSelection() {
        if (!sel || !data) return;
        if (sel.kind === 'table') {
            const t = tbl(sel.id);
            if (!t.draft) return toast('Tabel ini sudah ada di database. Hapus langsung di database.', 'warning');
            data.tables = data.tables.filter((x) => x !== t);
            data.relations = data.relations.filter((r) => r.from.t !== t.id && r.to.t !== t.id);
            send({ type: 'table_delete', table: t.id });
        } else {
            const r = rel(sel.id);
            if (r.source === 'db') return toast('FK ini ada di database. Hapus langsung di database.', 'warning');
            data.relations = data.relations.filter((x) => x !== r);
            send({ type: 'relation_delete', id: r.id });
        }
        select(null);
    }

    function select(s) { sel = s; if (s) panelTab = 'props'; render(); renderPanel(); }

    // ===== Event panel =====
    document.querySelectorAll('.panel-tabs button').forEach((b) => b.addEventListener('click', () => { panelTab = b.dataset.tab; renderPanel(); }));

    function addColumnsFromPanel(lines) {
        const t = tbl(sel.id), added = [];
        lines.forEach((line) => { const c = makeColumn([...t.columns, ...added], line); if (c) added.push(c); });
        if (!added.length) return false;
        t.columns.splice(insertIndex(t), 0, ...added);
        sendColumns(t);
        render(); renderPanel();
        document.getElementById('quickCol')?.focus();
        return true;
    }

    panel.addEventListener('keydown', (e) => {
        if (e.target.id !== 'quickCol' || e.key !== 'Enter') return;
        e.preventDefault();
        if (e.target.value.trim()) addColumnsFromPanel([e.target.value]);
    });
    panel.addEventListener('paste', (e) => {
        if (e.target.id !== 'quickCol') return;
        const text = (e.clipboardData || window.clipboardData).getData('text');
        if (!/\n/.test(text.trim())) return;
        e.preventDefault();
        addColumnsFromPanel(text.split(/\r?\n/).filter((l) => l.trim()));
    });
    // Ketik = tampil langsung, kirim ke server saat input selesai (change)
    panel.addEventListener('input', (e) => {
        const f = e.target.dataset.f;
        if (!f || e.target.tagName === 'SELECT') return;
        if (f === 't.note') tbl(sel.id).note = e.target.value;
        else if (f === 'r.label') rel(sel.id).label = e.target.value;
        else return;
        render();
    });
    panel.addEventListener('change', (e) => {
        const f = e.target.dataset.f;
        if (!f || e.target.tagName === 'SELECT') return;
        const t = sel?.kind === 'table' ? tbl(sel.id) : null;
        if (f === 't.note') send({ type: 'table_meta', table: t.id, note: t.note });
        else if (f === 'r.label') sendRelation(rel(sel.id));
        else if (f === 't.name') { if (!renameTable(t, e.target.value)) e.target.value = t.name; render(); renderTableList(); }
        else if (f === 'c.name') {
            const c = col(t, e.target.closest('.colcard').dataset.c);
            if (!updateColumn(t, c, { ...c, name: e.target.value.trim() })) e.target.value = c.name;
            render(); renderPanel();
        } else if (f === 'c.def' || f === 'c.comment') {
            // Tanpa renderPanel supaya fokus tetap jalan saat pindah input pakai Tab
            const c = col(t, e.target.closest('.colcard').dataset.c), v = e.target.value.trim();
            updateColumn(t, c, f === 'c.def' ? { ...c, def: normalizeDefault(v) } : { ...c, comment: v });
            render();
        }
    });
    $(panel).on('change', 'select.s2', function () {
        const f = this.dataset.f, v = $(this).val();
        if (f === 'fk') {
            const P = tbl(v), pk = P?.columns.find((c) => c.pk);
            if (pk) $('#fkHint').html(`Akan menambah kolom <code>${esc(pk.name === 'id' ? `${P.name}_id` : `${P.name}_${pk.name}`)}</code> ${esc(pk.type)} dan relasi <b>1:N</b> ke <code>${esc(P.name)}.${esc(pk.name)}</code>.`);
            return;
        }
        if (f === 'c.type') {
            const t = tbl(sel.id), c = col(t, this.closest('.colcard').dataset.c);
            if (!updateColumn(t, c, { ...c, type: normalizeType(v) })) { renderPanel(); return; }
            render(); return;
        }
        const r = rel(sel.id), [t, c] = v.split('|');
        r[f.slice(2)] = { t, c };
        sendRelation(r);
        render(); renderPanel();
    });
    panel.addEventListener('click', (e) => {
        const undoBtn = e.target.closest('[data-undo]');
        if (undoBtn) { undo(Number(undoBtn.dataset.undo)); return; }
        const btn = e.target.closest('[data-act]');
        if (!btn) return;
        if (btn.dataset.act === 'guide') { startTour(); return; }
        const act = btn.dataset.act, card = btn.closest('.colcard');
        const t = sel?.kind === 'table' ? tbl(sel.id) : null;
        const r = sel?.kind === 'rel' ? rel(sel.id) : null;
        const ci = card ? t.columns.findIndex((c) => c.id === card.dataset.c) : -1;

        switch (act) {
            case 'color': t.color = btn.dataset.v; send({ type: 'table_meta', table: t.id, color: t.color }); break;
            case 'tog': {
                const c = t.columns[ci], k = btn.dataset.k, next = { ...c, [k]: !c[k] };
                if (k === 'pk' && next.pk) next.nn = true;
                if (k === 'nn' && c.pk) next.nn = true;
                if (k === 'ai' && next.ai) t.columns.forEach((x) => { if (x !== c) x.ai = false; });
                sel.col = c.id;
                updateColumn(t, c, next);
                break;
            }
            case 'up': [t.columns[ci - 1], t.columns[ci]] = [t.columns[ci], t.columns[ci - 1]]; sendColumns(t); break;
            case 'down': [t.columns[ci + 1], t.columns[ci]] = [t.columns[ci], t.columns[ci + 1]]; sendColumns(t); break;
            case 'delcol': {
                const cid = t.columns[ci].id;
                t.columns.splice(ci, 1);
                data.relations = data.relations.filter((x) => !(x.from.t === t.id && x.from.c === cid) && !(x.to.t === t.id && x.to.c === cid));
                sendColumns(t);
                break;
            }
            case 'deltable': deleteSelection(); return;
            case 'apply': applyToDb([t.id]); return;
            case 'focus': focusTable(t.id); return;
            case 'hide': hideTables([t.id]); return;
            case 'dup': duplicateTable(t); return;
            case 'addfk': {
                const P = tbl($('#fkParent').val());
                if (!P) { toast('Pilih tabel parent dulu', 'warning'); return; }
                addForeignKey(t, P);
                return;
            }
            case 'card': r.card = btn.dataset.v; sendRelation(r); break;
            case 'optional': r.optional = btn.checked; sendRelation(r); break;
            case 'swap': [r.from, r.to] = [r.to, r.from]; sendRelation(r); break;
            case 'delrel': deleteSelection(); return;
        }
        render(); renderPanel();
    });

    // ===== Editor langsung di kanvas (hanya tabel draft) =====
    const editor = document.getElementById('inlineEditor');
    const editorInput = document.getElementById('inlineInput');
    const editorHint = document.getElementById('inlineHint');
    let editing = null;          // {mode: 'table' | 'col' | 'add', t, c}

    function openEditor(mode, tId, cId = null) {
        const t = tbl(tId);
        if (!t) return;
        if (!t.draft) { toast('Tabel ini sudah ada di database. Ubah strukturnya langsung di database.', 'info'); return; }
        editing = { mode, t: tId, c: cId };
        editorInput.value = mode === 'table' ? t.name : mode === 'col' ? columnText(col(t, cId)) : '';
        editorInput.placeholder = mode === 'table' ? 'nama_tabel' : 'nama_kolom tipe';
        editor.classList.remove('d-none');
        positionEditor();
        editorPreview();
        editorInput.focus();
        editorInput.select();
    }

    function closeEditor() {
        editing = null;
        editor.classList.add('d-none');
    }

    function positionEditor() {
        if (!editing) return;
        const t = tbl(editing.t);
        if (!t) return closeEditor();
        const i = editing.mode === 'col' ? t.columns.findIndex((c) => c.id === editing.c) : t.columns.length;
        const y = editing.mode === 'table' ? 0 : HEAD + i * ROW;
        const h = editing.mode === 'table' ? HEAD : editing.mode === 'add' ? ADDROW : ROW;
        editor.style.left = view.x + t.x * view.k + 'px';
        editor.style.top = view.y + (t.y + y) * view.k + 'px';
        editor.style.width = Math.max((W[t.id] || tableWidth(t)) * view.k, 240) + 'px';
        editorInput.style.height = Math.max(h * view.k, 26) + 'px';
        editorInput.style.fontSize = Math.max(12, (editing.mode === 'table' ? 13 : 12) * view.k) + 'px';
        editorInput.style.fontWeight = editing.mode === 'table' ? 600 : 400;
    }

    function editorPreview() {
        const keys = editing.mode === 'table'
            ? '<kbd>Enter</kbd> simpan & lanjut isi kolom &middot; <kbd>Esc</kbd> batal'
            : '<kbd>Enter</kbd> simpan & lanjut &middot; <kbd>&uarr;</kbd><kbd>&darr;</kbd> pindah kolom &middot; <kbd>Esc</kbd> selesai';
        if (editing.mode === 'table') { editorHint.innerHTML = `Nama tabel<div class="keys">${keys}</div>`; return; }
        const c = parseColumn(editorInput.value);
        const preview = c
            ? `<b>${esc(c.name)}</b> <span class="t ${TYPE_RE.test(c.type) ? '' : 'bad'}">${esc(c.type)}</span>${['pk', 'nn', 'uq', 'ix', 'ai'].filter((k) => c[k]).map((k) => `<span class="f">${k.toUpperCase()}</span>`).join('')}`
                + `${c.def != null ? ` <span style="color:#fcd34d">default ${esc(c.def)}</span>` : ''}${c.comment ? `<br><span style="color:#9ca3af">💬 ${esc(c.comment)}</span>` : ''}`
                + `${TYPE_RE.test(c.type) ? '' : '<br><span style="color:#fca5a5">Tipe tidak dikenal. Contoh: int, varchar(100), decimal(15,2), date</span>'}`
            : 'Ketik <b>nama_kolom</b> lalu tipe (opsional), mis. <span class="t">qty int nn =0 "jumlah"</span>';
        editorHint.innerHTML = `${preview}<div class="keys">${keys}</div>`
            + '<div class="keys">Penanda: <b>pk</b> primary key &middot; <b>nn</b> wajib isi &middot; <b>uq</b> unik &middot; <b>ix</b> index &middot; <b>ai</b> auto increment</div>'
            + '<div class="keys"><b>=nilai</b> default (<b>=now</b> waktu sekarang) &middot; <b>"teks"</b> keterangan</div>';
    }

    // Simpan isi editor. Return false kalau tidak tersimpan (kosong / tidak valid)
    function saveEditor() {
        const t = tbl(editing.t), v = editorInput.value.trim();
        if (!t || !v) return false;
        if (editing.mode === 'table') {
            if (!renameTable(t, v)) return false;
            editing.t = t.id;
            return true;
        }
        if (editing.mode === 'col') {
            const c = col(t, editing.c), next = parseColumn(v);
            if (columnText(c) === columnText(next)) return true;
            if (!updateColumn(t, c, next)) return false;
            editing.c = c.id;
            return true;
        }
        const c = makeColumn(t.columns, v);
        if (!c) return false;
        t.columns.splice(insertIndex(t), 0, c);
        sendColumns(t);
        return true;
    }

    editorInput.addEventListener('input', editorPreview);
    editorInput.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') { e.preventDefault(); closeEditor(); return; }
        if (e.key !== 'Enter' && e.key !== 'ArrowDown' && e.key !== 'ArrowUp') return;
        if (editing.mode === 'table' && e.key !== 'Enter') return;
        e.preventDefault();
        if (e.key === 'Enter' && editing.mode === 'add' && !editorInput.value.trim()) { closeEditor(); return; }

        const before = tbl(editing.t);
        const i = editing.mode === 'col' ? before.columns.findIndex((c) => c.id === editing.c) : before.columns.length;
        if (editorInput.value.trim() && !saveEditor()) return;
        const t = tbl(editing.t);

        // Kolom berikutnya: naik/turun, atau baris tambah kolom di paling bawah
        let next = i + (e.key === 'ArrowUp' ? -1 : 1);
        if (editing.mode === 'table' || (editing.mode === 'add' && e.key === 'Enter')) next = t.columns.length;
        next = Math.max(0, Math.min(next, t.columns.length));
        sel = { kind: 'table', id: t.id };
        render(); renderPanel();
        next < t.columns.length ? openEditor('col', t.id, t.columns[next].id) : openEditor('add', t.id);
    });
    editorInput.addEventListener('blur', () => {
        if (!editing) return;
        saveEditor();
        closeEditor();
        render(); renderPanel();
    });

    // ===== Interaksi kanvas =====
    // Saat drag: geser elemen tabel itu saja + hitung ulang rute relasi yang terhubung (bukan seluruh kanvas).
    // Rute relasi lain dirapikan ulang saat tabel dilepas (render penuh).
    function moveTableFast(t) {
        tblLayer.querySelector(`.tbl[data-t="${CSS.escape(t.id)}"]`)?.setAttribute('transform', `translate(${t.x},${t.y})`);
        const obs = obstacles();
        routeKey = '';
        data.relations.forEach((r) => {
            if (r.from.t !== t.id && r.to.t !== t.id) return;
            if (isShown(r.from.t) && isShown(r.to.t)) routes[r.id] = relGeometry(r, obs);
            const el = relLayer.querySelector(`.rel[data-r="${CSS.escape(r.id)}"]`), html = relSvg(r);
            if (el) el.outerHTML = html;
            else if (html) relLayer.insertAdjacentHTML('beforeend', html);
        });
        applyHighlight();
    }

    // Hover tabel / kolom / garis: sorot relasinya
    svg.addEventListener('pointerover', (e) => {
        if (drag || pan || conn) return;
        const relEl = e.target.closest('.rel'), row = e.target.closest('.erd-row'), tEl = e.target.closest('.tbl');
        const next = relEl ? { r: relEl.dataset.r } : row ? { t: row.dataset.t, c: row.dataset.c } : tEl ? { t: tEl.dataset.t } : null;
        if (JSON.stringify(next) === JSON.stringify(hoverHl)) return;
        hoverHl = next;
        applyHighlight();
    });
    svg.addEventListener('pointerleave', () => { hoverHl = null; applyHighlight(); });

    function toWorld(e) {
        const b = svg.getBoundingClientRect();
        return { x: (e.clientX - b.left - view.x) / view.k, y: (e.clientY - b.top - view.y) / view.k };
    }

    // Tahan Spasi = geser kanvas, juga saat sedang menarik relasi / menggeser tabel
    let spaceDown = false, lastPt = null;
    const typing = () => /INPUT|TEXTAREA|SELECT/.test(document.activeElement.tagName) || document.querySelector('.modal.show, .swal2-container');
    window.addEventListener('keydown', (e) => {
        if (e.code !== 'Space' || typing()) return;
        e.preventDefault();
        if (!spaceDown) { spaceDown = true; svg.classList.add('space-pan'); }
    });
    window.addEventListener('keyup', (e) => {
        if (e.code !== 'Space' || !spaceDown) return;
        e.preventDefault();
        spaceDown = false; svg.classList.remove('space-pan');
    });
    window.addEventListener('blur', () => { spaceDown = false; svg.classList.remove('space-pan'); });

    function drawTmpLine(e) {
        const p = toWorld(e), dx = Math.max(30, Math.abs(p.x - conn.x) / 2) * (p.x > conn.x ? 1 : -1);
        tmpLine.setAttribute('d', `M${conn.x} ${conn.y}C${conn.x + dx} ${conn.y} ${p.x - dx} ${p.y} ${p.x} ${p.y}`);
    }

    svg.addEventListener('pointerdown', (e) => {
        if (e.button !== 0 || !data) return;
        if (spaceDown) {
            pan = { sx: e.clientX, sy: e.clientY, vx: view.x, vy: view.y };
            svg.classList.add('panning');
            return;
        }
        const p = toWorld(e);
        const port = e.target.closest('.port');
        if (port) {
            const t = tbl(port.dataset.t), idx = t.columns.findIndex((c) => c.id === port.dataset.c);
            conn = { t: t.id, c: port.dataset.c, x: t.x + Number(port.getAttribute('cx')), y: t.y + HEAD + idx * ROW + ROW / 2 };
            svg.classList.add('connecting');
            document.getElementById('canvasHint').style.display = '';
            return;
        }
        const applyBtn = e.target.closest('.apply-btn');
        if (applyBtn) { applyToDb([applyBtn.dataset.t]); return; }
        const addRow = e.target.closest('.add-row');
        if (addRow) {
            const id = addRow.dataset.t;
            select({ kind: 'table', id });
            setTimeout(() => openEditor('add', id), 0);
            return;
        }
        const relEl = e.target.closest('.rel');
        if (relEl) { select({ kind: 'rel', id: relEl.dataset.r }); return; }
        const tblEl = e.target.closest('.tbl');
        if (tblEl) {
            const t = tbl(tblEl.dataset.t), row = e.target.closest('.erd-row');
            data.tables = data.tables.filter((x) => x !== t).concat(t); // bawa ke depan
            drag = { t, dx: p.x - t.x, dy: p.y - t.y, moved: false };
            select({ kind: 'table', id: t.id, col: row?.dataset.c });
            return;
        }
        pan = { sx: e.clientX, sy: e.clientY, vx: view.x, vy: view.y };
        svg.classList.add('panning');
        if (sel) select(null);
    });

    window.addEventListener('pointermove', (e) => {
        const mx = lastPt ? e.clientX - lastPt.x : 0, my = lastPt ? e.clientY - lastPt.y : 0;
        lastPt = { x: e.clientX, y: e.clientY };
        if (spaceDown && (conn || drag)) {
            view.x += mx; view.y += my;
            applyView();
            if (conn) drawTmpLine(e);
            return;
        }
        if (drag) {
            const p = toWorld(e), nx = snap(p.x - drag.dx), ny = snap(p.y - drag.dy);
            if (nx === drag.t.x && ny === drag.t.y) return;
            drag.moved = true;
            drag.t.x = nx; drag.t.y = ny;
            moveTableFast(drag.t);
        } else if (pan) {
            view.x = pan.vx + e.clientX - pan.sx;
            view.y = pan.vy + e.clientY - pan.sy;
            applyView();
        } else if (conn) {
            drawTmpLine(e);
        }
    });

    window.addEventListener('pointerup', (e) => {
        if (conn) {
            const el = document.elementFromPoint(e.clientX, e.clientY);
            const row = el?.closest?.('#erdSvg .erd-row'), tblEl = el?.closest?.('#erdSvg .tbl');
            const from = { t: conn.t, c: conn.c };
            if (row && !(row.dataset.t === conn.t && row.dataset.c === conn.c)) {
                addRelation(from, { t: row.dataset.t, c: row.dataset.c });
            } else if (tblEl && tblEl.dataset.t !== conn.t) {
                // Lepas di header tabel draft: buat kolom FK otomatis
                const A = tbl(conn.t), src = col(A, conn.c), B = tbl(tblEl.dataset.t);
                if (!B.draft) {
                    toast(`Tabel ${B.name} sudah ada di database. Tambahkan kolom FK-nya langsung di database.`, 'warning');
                } else {
                    const fk = makeColumn(B.columns, `${A.name}_${src.name} ${src.type} nn`);
                    if (fk) {
                        B.columns.splice(insertIndex(B), 0, fk);
                        sendColumns(B);
                        addRelation(from, { t: B.id, c: fk.id });
                    }
                }
            }
            conn = null;
            tmpLine.setAttribute('d', '');
            svg.classList.remove('connecting');
            document.getElementById('canvasHint').style.display = 'none';
        }
        if (drag?.moved) {
            send({ type: 'move', tables: [{ name: drag.t.id, x: drag.t.x, y: drag.t.y }] });
            render();
        }
        drag = null; pan = null;
        svg.classList.remove('panning');
    });

    svg.addEventListener('dblclick', (e) => {
        if (!data) return;
        // Kanvas di-render ulang saat klik, jadi cek elemen di bawah kursor langsung
        const el = document.elementFromPoint(e.clientX, e.clientY);
        const row = el?.closest?.('.erd-row'), head = el?.closest?.('.tbl-head');
        if (row) return openEditor('col', row.dataset.t, row.dataset.c);
        if (head) return openEditor('table', head.closest('.tbl').dataset.t);
        if (el?.id !== 'gridBg') return;
        const p = toWorld(e);
        addTable(p.x - 95, p.y - 20);
    });

    svg.addEventListener('wheel', (e) => {
        e.preventDefault();
        const b = svg.getBoundingClientRect();
        zoomAt(view.k * Math.exp(-e.deltaY * 0.0015), e.clientX - b.left, e.clientY - b.top);
    }, { passive: false });

    function zoomAt(k, cx, cy) {
        k = Math.min(2.5, Math.max(0.2, k));
        view.x = cx - (cx - view.x) * (k / view.k);
        view.y = cy - (cy - view.y) * (k / view.k);
        view.k = k;
        applyView();
    }
    function zoomCenter(f) { const b = svg.getBoundingClientRect(); zoomAt(view.k * f, b.width / 2, b.height / 2); }

    // Hanya tabel yang tampil (dipakai untuk "tampilkan semua" dan export)
    function bbox() {
        const shown = (data?.tables || []).filter((t) => isShown(t.id));
        if (!shown.length) return { x: 0, y: 0, w: 400, h: 300 };
        let x1 = Infinity, y1 = Infinity, x2 = -Infinity, y2 = -Infinity;
        shown.forEach((t) => {
            x1 = Math.min(x1, t.x); y1 = Math.min(y1, t.y);
            x2 = Math.max(x2, t.x + W[t.id] + 60); y2 = Math.max(y2, t.y + tableHeight(t));
        });
        return { x: x1, y: y1, w: x2 - x1, h: y2 - y1 };
    }

    function fit() {
        const b = svg.getBoundingClientRect(), bb = bbox();
        view.k = Math.min(1.2, Math.max(0.2, Math.min((b.width - 80) / bb.w, (b.height - 80) / bb.h)));
        view.x = (b.width - bb.w * view.k) / 2 - bb.x * view.k;
        view.y = (b.height - bb.h * view.k) / 2 - bb.y * view.k;
        applyView();
    }

    function centerOn(t) {
        const b = svg.getBoundingClientRect();
        view.x = b.width / 2 - (t.x + W[t.id] / 2) * view.k;
        view.y = b.height / 2 - (t.y + tableHeight(t) / 2) * view.k;
        applyView();
    }

    // Rapikan: parent di kiri, child di kanan, bertingkat per level relasi
    function arrange() {
        const lvl = {};
        data.tables.forEach((t) => (lvl[t.id] = 0));
        for (let i = 0; i < data.tables.length; i++) {
            data.relations.forEach((r) => {
                const { parent, child } = parentChild(r);
                if (parent.t !== child.t && lvl[child.t] < lvl[parent.t] + 1) lvl[child.t] = Math.min(lvl[parent.t] + 1, data.tables.length);
            });
        }
        const levels = {};
        data.tables.forEach((t) => (levels[lvl[t.id]] ??= []).push(t));
        const cols = Object.keys(levels).sort((a, b) => a - b).map((k) => levels[k].sort((a, b) => a.name.localeCompare(b.name)));

        // Jarak antar kolom/baris dibuat lega supaya ada jalur untuk garis relasi
        const nb = {};
        data.relations.forEach((r) => {
            if (r.from.t === r.to.t) return;
            (nb[r.from.t] ??= []).push(r.to.t);
            (nb[r.to.t] ??= []).push(r.from.t);
        });
        const stack = (list) => { let y = 40; list.forEach((t) => { t.y = y; y += tableHeight(t) + 60; }); };
        cols.forEach(stack);

        // Urutkan tabel di tiap kolom mendekati posisi tabel relasinya (barycenter) supaya garis jarang bersilangan
        const center = (id) => { const t = tbl(id); return t.y + tableHeight(t) / 2; };
        for (let pass = 0; pass < 4; pass++) {
            (pass % 2 ? [...cols].reverse() : cols).forEach((list) => {
                const bc = new Map(list.map((t) => {
                    const ns = (nb[t.id] || []).filter((id) => lvl[id] !== lvl[t.id]);
                    return [t.id, ns.length ? ns.reduce((s, id) => s + center(id), 0) / ns.length : Infinity];
                }));
                list.sort((a, b) => (bc.get(a.id) - bc.get(b.id)) || a.name.localeCompare(b.name));
                stack(list);
            });
        }

        let x = 40;
        cols.forEach((list) => {
            const maxW = Math.max(...list.map((t) => W[t.id] || tableWidth(t)));
            list.forEach((t) => (t.x = x));
            x += maxW + 180;
        });
        send({ type: 'move', tables: data.tables.map((t) => ({ name: t.id, x: t.x, y: t.y })) });
        render(); fit();
    }

    // ===== Terapkan ke database =====
    async function applyToDb(tables = null) {
        if (!data) return;
        await opQueue;
        let preview;
        try {
            preview = await $.get(ROUTES.preview.replace(':id', data.id), tables ? { tables } : {});
        } catch (x) { toast(errMsg(x), 'error'); return; }

        const list = (items, icon, color) => items.map((m) => `<div class="mb-1"><i class="fa-solid ${icon}" style="color:${color}"></i> ${formatDesc(m)}</div>`).join('');
        const flags = (c) => ['pk', 'nn', 'uq', 'ai'].filter((k) => c[k] && !(k === 'nn' && c.pk)).map((k) => k.toUpperCase()).join(' ');
        const tablesHtml = preview.statements.map((s) => {
            const fkOf = Object.fromEntries(s.fks.map((f) => [f.column, `${f.ref_table}.${f.ref_column}`]));
            return `<div class="apply-table">
                <div class="head"><i class="fa-solid fa-table"></i> ${esc(s.table)} <span class="text-muted fw-normal">(${s.columns.length} kolom)</span></div>
                <table><tr><th>Kolom</th><th>Tipe</th><th>Penanda</th><th>FK ke</th></tr>
                ${s.columns.map((c) => `<tr><td>${esc(c.name)}</td><td class="ty">${esc(c.type)}</td><td>${flags(c)}</td><td>${fkOf[c.name] ? `<code>${esc(fkOf[c.name])}</code>` : ''}</td></tr>`).join('')}
                </table></div>`;
        }).join('');
        const sql = preview.statements.map((s) => esc(s.sql)).join('\n\n');
        const canRun = !preview.errors.length && preview.statements.length > 0;
        const html = `
            <div class="apply-list">
                ${preview.errors.length ? `<div class="alert alert-danger py-2">${list(preview.errors, 'fa-circle-xmark', '#c62828')}</div>` : ''}
                ${canRun ? `<div class="apply-warn"><i class="fa-solid fa-triangle-exclamation"></i> <b>Pastikan struktur di bawah sudah benar.</b>
                    Setelah dibuat di database <code>${esc(data.database)}</code>, tabel <b>tidak bisa di-undo</b> dan kolomnya tidak bisa diubah dari ERD lagi (harus manual di database).</div>` : ''}
                ${preview.notes.length ? `<div class="alert alert-warning py-2">${list(preview.notes, 'fa-circle-info', '#b45309')}</div>` : ''}
                ${tablesHtml || '<div class="text-muted">Tidak ada tabel draft.</div>'}
                ${sql ? `<details class="apply-sql"><summary>Lihat SQL yang akan dijalankan</summary><div class="sql-box">${sql}</div></details>` : ''}
            </div>`;
        const res = await Swal.fire({
            title: 'Buat Tabel di Database?', html, width: 820,
            input: canRun ? 'checkbox' : undefined,
            inputPlaceholder: 'Saya sudah memeriksa dan struktur tabel sudah benar',
            inputValidator: (v) => (!v ? 'Centang dulu konfirmasinya' : undefined),
            showCancelButton: true, cancelButtonText: canRun ? 'Batal, cek lagi' : 'Tutup',
            showConfirmButton: canRun,
            confirmButtonText: '<i class="fa-solid fa-database"></i> Ya, buat di database', confirmButtonColor: '#082149',
        });
        if (!res.isConfirmed) return;

        Swal.fire({ title: 'Membuat tabel...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
        $.ajax({ url: ROUTES.apply.replace(':id', data.id) + '?after=' + lastActivityId, type: 'post', contentType: 'application/json', data: JSON.stringify({ tables }) })
            .done((r) => {
                applyState(r);
                highlightCreated(r.applied);
                showCreated(r, preview);
            })
            .fail((x) => Swal.fire({ icon: 'error', title: 'Gagal', html: esc(errMsg(x)).replace(/\n/g, '<br>') }));
    }

    // Tandai tabel yang baru dibuat di kanvas beberapa detik, lalu arahkan layar ke sana
    function highlightCreated(names) {
        if (!names.length) return;
        justCreated = new Set(names);
        render();
        const first = tbl(names[0]);
        if (first) centerOn(first);
        setTimeout(() => { justCreated = new Set(); render(); }, 8000);
    }

    // Isi tabel setelah dibaca ulang dari database; tandai kolom yang disesuaikan MySQL (mis. BOOLEAN -> TINYINT(1))
    function showCreated(r, preview) {
        const planned = Object.fromEntries(preview.statements.map((s) => [s.table, Object.fromEntries(s.columns.map((c) => [c.name, c]))]));
        const tablesHtml = r.applied.map((name) => {
            const cols = r.created[name] || [];
            const rows = cols.map((c) => {
                const p = planned[name]?.[c.name];
                const adjusted = p && p.type !== c.type;
                return `<tr class="${adjusted ? 'changed' : ''}"><td>${esc(c.name)}</td><td class="ty">${esc(c.type)}</td>
                    <td>${['pk', 'nn', 'uq', 'ai'].filter((k) => c[k] && !(k === 'nn' && c.pk)).map((k) => k.toUpperCase()).join(' ')}</td>
                    <td class="small text-muted">${adjusted ? `disesuaikan MySQL dari ${esc(p.type)}` : ''}</td></tr>`;
            }).join('');
            return `<div class="apply-table"><div class="head"><i class="fa-solid fa-circle-check" style="color:#16a34a"></i> ${esc(name)}</div>
                <table><tr><th>Kolom</th><th>Tipe di database</th><th>Penanda</th><th></th></tr>${rows}</table></div>`;
        }).join('');
        Swal.fire({
            icon: r.error ? 'warning' : 'success',
            title: r.error ? 'Sebagian berhasil dibuat' : `${r.applied.length} tabel berhasil dibuat`,
            width: 760,
            html: `<div class="apply-list">
                ${r.error ? `<div class="alert alert-danger py-2">${esc(r.error)}</div>` : ''}
                ${tablesHtml ? `<div class="mb-2">Isi tabel sudah di-refresh dari database <code>${esc(data.database)}</code>:</div>${tablesHtml}` : ''}
            </div>`,
            confirmButtonText: 'OK', confirmButtonColor: '#082149',
        });
    }

    // ===== Export =====
    function download(name, content, type) {
        const blob = content instanceof Blob ? content : new Blob([content], { type });
        const a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = name;
        a.click();
        setTimeout(() => URL.revokeObjectURL(a.href), 1000);
    }
    const fileBase = () => (data.name || 'erd').replace(/[^\w\-]+/g, '_');

    function buildSvg() {
        const prev = sel; sel = null; exporting = true; render();
        const bb = bbox(), pad = 30;
        const clone = svg.cloneNode(true);
        sel = prev; exporting = false; render();
        clone.querySelectorAll('.port, .apply-btn, #tmpLine, #gridBg').forEach((n) => n.remove());
        clone.querySelector('#viewport').setAttribute('transform', `translate(${pad - bb.x},${pad - bb.y})`);
        const w = Math.ceil(bb.w + pad * 2), h = Math.ceil(bb.h + pad * 2);
        clone.setAttribute('width', w); clone.setAttribute('height', h);
        clone.setAttribute('viewBox', `0 0 ${w} ${h}`);
        clone.removeAttribute('id'); clone.removeAttribute('class');
        clone.insertAdjacentHTML('afterbegin', `<rect width="${w}" height="${h}" fill="#ffffff"/>`);
        return { str: new XMLSerializer().serializeToString(clone), w, h };
    }

    // Dokumentasi: CREATE TABLE semua tabel di diagram (bukan yang dijalankan ke database)
    function buildSql() {
        const q = (s) => '`' + s + '`';
        let out = `-- ${data.name} (${data.connection} / ${data.database})\n-- Export dari ERD Designer, ${new Date().toLocaleString('id-ID')}\n\n`;
        data.tables.forEach((t) => {
            const lines = t.columns.map((c) => `  ${q(c.name)} ${c.type}${c.nn || c.pk ? ' NOT NULL' : ' NULL'}${c.ai ? ' AUTO_INCREMENT' : ''}${c.uq && !c.pk ? ' UNIQUE' : ''}`);
            const pks = t.columns.filter((c) => c.pk).map((c) => q(c.name));
            if (pks.length) lines.push(`  PRIMARY KEY (${pks.join(', ')})`);
            out += `${t.draft ? '-- DRAFT (belum di database)\n' : ''}${t.note ? '-- ' + t.note.replace(/\n/g, ' ') + '\n' : ''}CREATE TABLE ${q(t.name)} (\n${lines.join(',\n')}\n);\n\n`;
        });
        data.relations.forEach((r) => {
            if (r.card === 'N:N') { out += `-- ${r.from.t}.${r.from.c} N:N ${r.to.t}.${r.to.c} -> perlu tabel pivot\n`; return; }
            const { parent, child } = parentChild(r);
            out += `ALTER TABLE ${q(child.t)} ADD CONSTRAINT ${q(r.constraint || 'fk_' + child.t + '_' + child.c)} FOREIGN KEY (${q(child.c)}) REFERENCES ${q(parent.t)} (${q(parent.c)});\n`;
        });
        return out;
    }

    document.querySelectorAll('[data-export]').forEach((a) => a.addEventListener('click', (e) => {
        e.preventDefault();
        if (!data) return;
        const kind = a.dataset.export;
        if (kind === 'sql') return download(fileBase() + '.sql', buildSql(), 'text/plain');
        const { str, w, h } = buildSvg();
        if (kind === 'svg') return download(fileBase() + '.svg', str, 'image/svg+xml');
        const img = new Image();
        img.onload = () => {
            const c = document.createElement('canvas');
            c.width = w * 2; c.height = h * 2;
            const g = c.getContext('2d'); g.scale(2, 2); g.drawImage(img, 0, 0);
            c.toBlob((b) => download(fileBase() + '.png', b));
        };
        img.src = 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent(str);
    }));

    // ===== Toolbar & keyboard =====
    document.getElementById('diagramName').addEventListener('change', (e) => {
        const name = e.target.value.trim();
        if (!name) { e.target.value = data.name; return; }
        data.name = name;
        send({ type: 'diagram_rename', name });
        diagrams = diagrams.map((d) => (d.id === data.id ? { ...d, name } : d));
        renderDiagramSelect();
    });
    function addTableAtCenter() {
        const b = svg.getBoundingClientRect();
        addTable((b.width / 2 - view.x) / view.k - 95, (b.height / 3 - view.y) / view.k);
    }
    document.getElementById('emptyCard').addEventListener('click', (e) => {
        const btn = e.target.closest('[data-empty]');
        if (btn?.dataset.empty === 'new') showNewDiagram();
        if (btn?.dataset.empty === 'table') addTableAtCenter();
        if (btn?.dataset.empty === 'showall') { setHidden([]); requestAnimationFrame(fit); }
    });
    document.getElementById('btnViewAll').addEventListener('click', () => { setHidden([]); requestAnimationFrame(fit); });
    document.getElementById('btnViewNone').addEventListener('click', () => setHidden(data.tables.map((t) => t.id)));
    document.getElementById('btnViewMatch').addEventListener('click', () => {
        const q = document.getElementById('tableSearch').value.toLowerCase();
        setHidden(data.tables.filter((t) => !t.name.toLowerCase().includes(q)).map((t) => t.id));
        requestAnimationFrame(fit);
    });
    document.getElementById('btnAddTable').addEventListener('click', addTableAtCenter);
    document.getElementById('btnZoomIn').addEventListener('click', () => zoomCenter(1.2));
    document.getElementById('btnZoomOut').addEventListener('click', () => zoomCenter(1 / 1.2));
    document.getElementById('btnFit').addEventListener('click', fit);
    document.getElementById('btnArrange').addEventListener('click', arrange);
    document.getElementById('btnRefresh').addEventListener('click', () => refresh(true));
    document.getElementById('btnApply').addEventListener('click', () => applyToDb());
    document.getElementById('tableSearch').addEventListener('input', renderTableList);
    document.getElementById('tableList').addEventListener('click', (e) => {
        const eye = e.target.closest('[data-eye]');
        if (eye) { isShown(eye.dataset.eye) ? hideTables([eye.dataset.eye]) : showTables([eye.dataset.eye]); return; }
        const it = e.target.closest('.table-item');
        if (!it) return;
        const t = tbl(it.dataset.t);
        if (!isShown(t.id)) showTables([t.id]);   // klik nama tabel tersembunyi = tampilkan
        select({ kind: 'table', id: t.id });
        centerOn(t);
    });

    document.getElementById('btnUndo').addEventListener('click', () => undo());

    window.addEventListener('keydown', (e) => {
        if (tourIndex !== null) {
            if (e.key === 'Escape') endTour();
            else if (e.key === 'ArrowRight' || e.key === 'Enter') { e.preventDefault(); tourGo(1); }
            else if (e.key === 'ArrowLeft') tourGo(-1);
            return;
        }
        if (/INPUT|TEXTAREA|SELECT/.test(document.activeElement.tagName) || document.querySelector('.modal.show, .swal2-container')) return;
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'z') { e.preventDefault(); undo(); }
        else if (e.key === 'Delete' || e.key === 'Backspace') { e.preventDefault(); deleteSelection(); }
        else if (e.key === 'Escape') select(null);
    });

    // ===== Pengaturan =====
    const settingsModal = new bootstrap.Modal(document.getElementById('settingsModal'));
    const SETTING_INPUTS = { setStdId: 'stdId', setStdBy: 'stdBy', setStdTs: 'stdTs', setStdDel: 'stdDel', setMinimap: 'minimap', setLineColor: 'lineColor' };
    document.getElementById('btnSettings').addEventListener('click', () => {
        Object.entries(SETTING_INPUTS).forEach(([id, key]) => (document.getElementById(id).checked = !!settings[key]));
        document.getElementById(settings.lineStyle === 'curve' ? 'setLineCurve' : 'setLineOrtho').checked = true;
        settingsModal.show();
    });
    document.getElementById('btnSaveSettings').addEventListener('click', () => {
        Object.entries(SETTING_INPUTS).forEach(([id, key]) => (settings[key] = document.getElementById(id).checked));
        settings.lineStyle = document.getElementById('setLineCurve').checked ? 'curve' : 'ortho';
        saveSettings();
        settingsModal.hide();
        render();
        toast('Pengaturan disimpan', 'success');
    });
    document.getElementById('btnGuideFromSettings').addEventListener('click', () => { settingsModal.hide(); setTimeout(startTour, 300); });

    // ===== Tur panduan: menyorot bagian layar satu per satu =====
    const GUIDE_KEY = `erd_guide_done_${ME}`;
    const TOUR = [
        { sel: '.erd-side .side-sec', title: 'Pilih diagram', text: 'Setiap koneksi database punya <b>satu diagram</b>. Pilih di sini, atau klik <b>Diagram Baru</b> lalu pilih koneksinya.' },
        { sel: '#btnAddTable', title: 'Buat tabel', text: 'Klik <b>Tabel</b>, atau <b>klik dua kali</b> di area kosong kanvas. Tabel baru berstatus <b>DRAFT</b> dan langsung berisi kolom standar (<code>id</code>, <code>created_at</code>, <code>updated_at</code>).' },
        { sel: '#erdSvg', title: 'Isi kolom dengan mengetik', text: 'Klik <b>+ Tambah kolom</b> di tabel, ketik lalu <b>Enter</b>, langsung lanjut kolom berikutnya.<br><br>Contoh: <code>qty int nn =0 "jumlah order"</code><br><b>nn</b> wajib isi &middot; <b>uq</b> unik &middot; <b>ix</b> index &middot; <b>=nilai</b> default &middot; <b>"teks"</b> keterangan.<br>Tipe boleh dikosongkan, ditebak dari nama kolom.' },
        { sel: '#erdSvg', title: 'Buat relasi', text: 'Arahkan kursor ke kolom, lalu <b>tarik titik bulat</b> di sisi kiri/kanan ke kolom tabel lain.<br>Atau pilih tabel draft, lalu pakai <b>Tambah FK ke tabel</b> di panel kanan: kolom <code>parent_id</code> dan relasinya dibuat otomatis.' },
        { sel: '.erd-panel', title: 'Panel properti', text: 'Klik tabel untuk mengubah kolom, tipe, default, keterangan, dan penanda (PK, NN, UQ, IX, AI). Ada juga <b>Duplikat</b>, <b>Fokus</b>, dan warna header.' },
        { sel: '#btnApply', title: 'Buat di database', text: 'Kalau struktur draft sudah benar, klik <b>Buat di DB</b> (atau ▶ di header tabel). Muncul pratinjau, centang konfirmasi, lalu tabel dibuat lengkap dengan FOREIGN KEY.<br>Setelah itu tabel <b>dikunci</b>: ubah strukturnya langsung di database, diagram mengikuti otomatis.' },
        { sel: '.erd-side .side-sec:nth-child(2)', title: 'Pilih tabel yang tampil', text: 'Ikon 👁 di daftar tabel untuk menyembunyikan / menampilkan tabel. Pilihan ini <b>hanya untuk Anda</b>, tidak mengubah tampilan teman.' },
        { sel: '#btnUndo', title: 'Salah? Batalkan', text: '<b>Ctrl+Z</b> atau tombol ini membatalkan aksi <b>Anda sendiri</b>. Di tab <b>Riwayat</b> juga ada tombol <b>Batalkan</b> di setiap aksi Anda.' },
        { sel: '.panel-tabs [data-tab="history"]', title: 'Riwayat & siapa yang online', text: 'Lihat siapa yang sedang membuka diagram dan semua perubahan: siapa, apa, kapan. Perubahan yang dilakukan langsung di database juga tercatat.' },
        { sel: '#minimap', title: 'Minimap', text: 'Peta kecil seluruh diagram. Klik atau geser untuk pindah area dengan cepat.' },
        { sel: '#btnSettings', title: 'Pengaturan', text: 'Atur kolom standar tabel baru dan minimap. Panduan ini bisa dibuka lagi lewat tombol <b>Panduan</b>.' },
    ];
    let tourIndex = null, tourSteps = [];

    function startTour() {
        // Lewati langkah yang elemennya sedang tidak terlihat (mis. minimap belum muncul)
        tourSteps = TOUR.filter((s) => { const el = document.querySelector(s.sel); return el && el.offsetParent !== null; });
        tourIndex = 0;
        document.getElementById('tour').classList.remove('d-none');
        showTourStep();
    }

    function endTour() {
        tourIndex = null;
        document.getElementById('tour').classList.add('d-none');
        try { localStorage.setItem(GUIDE_KEY, '1'); } catch (e) { /* tetap jalan tanpa penyimpanan */ }
    }

    function tourGo(step) {
        const next = tourIndex + step;
        if (next >= tourSteps.length) return endTour();
        if (next < 0) return;
        tourIndex = next;
        showTourStep();
    }

    function showTourStep() {
        const s = tourSteps[tourIndex], el = document.querySelector(s.sel);
        const r = el.getBoundingClientRect(), pad = 6;
        Object.assign(document.getElementById('tourSpot').style, { left: r.left - pad + 'px', top: r.top - pad + 'px', width: r.width + pad * 2 + 'px', height: r.height + pad * 2 + 'px' });
        document.getElementById('tourStep').textContent = `Langkah ${tourIndex + 1} dari ${tourSteps.length}`;
        document.getElementById('tourTitle').textContent = s.title;
        document.getElementById('tourText').innerHTML = s.text;
        document.getElementById('tourPrev').disabled = tourIndex === 0;
        document.getElementById('tourNext').textContent = tourIndex === tourSteps.length - 1 ? 'Selesai' : 'Lanjut';

        // Kartu di samping elemen yang paling lega; elemen besar (kanvas) = kartu di tengah elemen
        const card = document.getElementById('tourCard'), cw = 340, ch = card.offsetHeight || 220, vw = innerWidth, vh = innerHeight;
        let left, top;
        if (r.width > vw * 0.5 && r.height > vh * 0.5) { left = r.left + r.width / 2 - cw / 2; top = r.top + r.height / 2 - ch / 2; }
        else if (r.bottom + ch + 16 < vh) { left = r.left; top = r.bottom + 14; }
        else if (r.top - ch - 16 > 0) { left = r.left; top = r.top - ch - 14; }
        else if (r.left - cw - 16 > 0) { left = r.left - cw - 14; top = r.top; }
        else { left = r.right + 14; top = r.top; }
        card.style.left = Math.max(12, Math.min(left, vw - cw - 12)) + 'px';
        card.style.top = Math.max(12, Math.min(top, vh - ch - 12)) + 'px';
    }

    document.getElementById('tourNext').addEventListener('click', () => tourGo(1));
    document.getElementById('tourPrev').addEventListener('click', () => tourGo(-1));
    document.getElementById('tourSkip').addEventListener('click', endTour);
    document.getElementById('btnGuide').addEventListener('click', startTour);
    window.addEventListener('resize', () => { if (tourIndex !== null) showTourStep(); });

    // ===== Init =====
    (function init() {
        render();
        renderPanel();
        loadList().done(() => {
            const id = Number(new URL(location.href).searchParams.get('d')) || (diagrams.length === 1 ? diagrams[0].id : null);
            if (id) openDiagram(id);
            // Pertama kali membuka ERD: tampilkan panduan otomatis
            let done = false;
            try { done = localStorage.getItem(GUIDE_KEY) === '1'; } catch (e) { done = true; }
            if (!done) setTimeout(startTour, 900);
        });
    })();
</script>
</body>
</html>
