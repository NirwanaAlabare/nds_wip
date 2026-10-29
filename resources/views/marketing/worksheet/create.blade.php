@extends('layouts.index')

@section('custom-link')
    <link rel="stylesheet" href="{{ asset('dist/css/marketing-fonts.css') }}?v={{ @filemtime(public_path('assets/dist/css/marketing-fonts.css')) }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('dist/css/marketing-buttons.css') }}?v={{ @filemtime(public_path('assets/dist/css/marketing-buttons.css')) }}">
    <style>
        .ws-page { min-height: calc(100vh - 56px); padding: 10px 14px 24px; background: #f5f5f5; color: #263746; font-family: 'Poppins', sans-serif; }
        .ws-crumb { margin-bottom: 18px; padding: 14px 16px; background: #284A63; color: #b4b4b4; font-size: 16px; font-weight: 500; }
        .ws-crumb a { color: #b4b4b4; text-decoration: none; }
        .ws-crumb a:hover { color: #fff; text-decoration: underline; }
        .ws-crumb b { color: #fff; font-weight: 500; }
        .ws-card { border: 0; border-radius: 10px; background: #fff; box-shadow: 0 5px 12px -3px rgba(30, 41, 59, .12); }
        .ws-card > .card-body { padding: 24px 28px 28px; }
        .ws-title { margin: 0; color: #284a63; font-size: 32px; font-weight: 700; letter-spacing: .04em; }
        .ws-title i { color: #284a63; font-size: 12px; vertical-align: super; }
        .ws-subtitle { display: flex; align-items: center; gap: 8px; margin: 8px 0 18px; color: #284a63; font-size: 12px; font-weight: 600; }
        .ws-subtitle i { color: #a3b2c7; font-size: 16px; }
        .ws-alert { display: flex; align-items: center; gap: 8px; margin-bottom: 14px; padding: 10px 12px; border-radius: 4px; background: #fff4e8; color: #263746; font-family: Inter, sans-serif; font-size: 13px; }
        .ws-alert > i { color: #f59e0b; }

        /* Kartu ringkasan putih (label abu, nilai navy tebal). */
        .ws-summary { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 24px; margin-bottom: 18px; }
        .ws-stat { min-width: 0; padding: 10px 18px 14px; border: 1px solid #edf1f5; border-radius: 10px; background: #fff; box-shadow: 0 3px 8px rgba(30, 41, 59, .10); }
        .ws-stat span { display: block; margin-bottom: 8px; color: #9e9e9e; font-family: 'Instrument Sans', sans-serif; font-size: 20px; font-weight: 500; line-height: 1.25; }
        .ws-stat strong { display: block; overflow: hidden; color: #284a63; font-size: 24px; font-weight: 700; letter-spacing: .05em; line-height: 1.25; text-overflow: ellipsis; white-space: nowrap; }

        .ws-top { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 16px; margin-bottom: 28px; }
        .ws-panel { min-width: 0; padding: 12px 20px 14px; border: 1px solid #edf1f5; border-radius: 10px; background: #fff; box-shadow: 0 3px 8px rgba(30, 41, 59, .10); }
        .ws-panel h2 { margin: 0 -20px 16px 0; padding: 4px 0 10px; border-bottom: 1px solid #c9d1d9; color: #616161; font-size: 16px; font-weight: 700; letter-spacing: .05em; }
        .ws-fields { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0 32px; }
        .ws-field { display: flex; min-width: 0; flex-direction: column; gap: 10px; margin-bottom: 8px; }
        .ws-field span { color: #9e9e9e; font-family: 'Instrument Sans', sans-serif; font-size: 18px; font-weight: 500; }
        .ws-field strong { max-width: 270px; padding-bottom: 2px; border-bottom: 1px solid #c9d1d9; overflow-wrap: anywhere; color: #284a63; font-size: 18px; font-weight: 700; letter-spacing: .05em; }

        /* Form Shell / Part (+ Color, Size, Qty) dengan Cancel / Save di kanan bawah. */
        .ws-form { display: flex; flex-direction: column; }
        .ws-form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px 24px; }
        .ws-form-grid.is-three { grid-template-columns: repeat(3, minmax(0, 1fr)); margin-top: 14px; }
        .ws-form label { display: block; margin-bottom: 8px; color: #1e293b; font-family: Inter, sans-serif; font-size: 18px; font-weight: 500; }
        .ws-form .form-control { height: 59px; border: 1px solid #c4c4c4; border-radius: 12px; padding: 0 12px; font-family: Inter, sans-serif; font-size: 16px; }
        .ws-form .select2-container--bootstrap4 .select2-selection { min-height: 59px; border: 1px solid #c4c4c4; border-radius: 12px; font-family: Inter, sans-serif; font-size: 16px; }
        .ws-form .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered { line-height: 57px; padding-left: 12px; }
        .ws-form .select2-container--bootstrap4 .select2-selection--single { height: 59px !important; }
        .ws-form .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow { top: 0; right: 10px; height: 100%; }
        .ws-form .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow b { top: 50%; }
        .ws-form .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__rendered { padding: 10px 12px; }
        .ws-form .select2-selection__placeholder, .ws-form .form-control::placeholder { color: #ababab; }
        .ws-form-note { margin-top: 8px; color: #64748b; font-family: Inter, sans-serif; font-size: 12px; }
        .ws-form-note.is-edit { color: #b45309; font-weight: 500; }
        .ws-form-actions { display: flex; justify-content: flex-end; gap: 16px; margin-top: auto; padding-top: 18px; }
        .ws-form-actions .mkt-btn { font-family: 'SF Pro Rounded', ui-rounded, 'MKT Rounded', sans-serif; font-size: 18px; font-weight: 700; }

        .ws-legend { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; margin-bottom: 14px; color: #64748b; font-family: Inter, sans-serif; font-size: 12px; }
        .ws-legend span { display: inline-flex; align-items: center; gap: 3px; }
        .ws-legend .mkt-btn { margin-left: auto; }
        .ws-dot { display: inline-block; width: 6px; height: 6px; border-radius: 50%; vertical-align: middle; }
        .ws-dot.is-bom { background: #16a34a; }
        .ws-dot.is-input { background: #f59e0b; }
        .ws-dot.is-auto { background: #94a3b8; }

        .ws-table-card { padding: 8px 20px 4px; border: 1px solid #edf1f5; border-radius: 10px; background: #fff; box-shadow: 0 3px 8px rgba(30, 41, 59, .10); }
        .ws-table-scroll { overflow-x: auto; }
        /* Tabel rapi: lebar kolom tetap, teks panjang dipotong (...). */
        .ws-table { width: 100% !important; min-width: 860px; margin: 0 !important; border-collapse: collapse !important; table-layout: fixed; font-family: Inter, sans-serif; }
        .ws-table thead th { padding: 10px 12px; border: 0 !important; background: #cfe2ff; color: #1e293b; font-size: 12px; font-weight: 500; letter-spacing: .03em; text-transform: uppercase; white-space: nowrap; vertical-align: middle; }
        .ws-table thead th .fa-pen { margin-left: 6px; font-size: 12px; color: #1e293b; }
        .ws-table tbody td { height: 46px; padding: 0 12px; border: 0; border-bottom: 1px solid #edf1f5; color: #1e293b; font-size: 14px; vertical-align: middle; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .ws-table tbody tr:hover td { background: #f8fafc; }
        .ws-table tbody tr.is-editing td { background: #fff7e6; }
        .ws-table td.dataTables_empty { height: auto; padding: 28px 8px; color: #8391a0; text-align: center; }
        .ws-actions { display: inline-flex; gap: 8px; }

        /* Grid ala Excel: pilih sel / drag, edit (dobel klik, Enter, F2, ketik), Ctrl+C / Ctrl+V, fill handle. */
        .ws-table td.ws-cell { position: relative; cursor: cell; user-select: none; border-right: 1px solid #f1f5f9; }
        .ws-table td.ws-cell.is-readonly { color: #64748b; }
        .ws-table td.ws-cell.is-dirty { background: #fff3c4 !important; }
        .ws-table td.ws-cell.is-pasted { background: #dbeafe !important; }
        .ws-table td.ws-cell.is-invalid { background: #fde2e2 !important; color: #b91c1c; }
        .ws-table td.ws-cell.is-selected { background: #e8f0fe !important; }
        .ws-table td.ws-cell.is-active { box-shadow: inset 0 0 0 2px #1a73e8; }
        .ws-table td.ws-cell.is-fill-preview { background: #fff3c4 !important; box-shadow: inset 0 0 0 1px #e0a800; }
        .ws-fill-handle { position: absolute; right: 0; bottom: 0; z-index: 3; width: 8px; height: 8px; border: 1px solid #fff; background: #1a73e8; cursor: crosshair; }
        .ws-table td.ws-cell.is-editing-cell { padding: 0 4px; overflow: visible; }
        .ws-cell-input { width: 100%; height: 36px; padding: 0 8px; border: 1px solid #1a73e8; border-radius: 4px; font-size: 14px; text-align: right; }
        body.ws-dragging, body.ws-dragging * { user-select: none; }
        body.ws-filling, body.ws-filling * { cursor: crosshair !important; user-select: none; }
        .ws-marks { display: flex; flex-wrap: wrap; gap: 16px; margin-top: 10px; color: #64748b; font-family: Inter, sans-serif; font-size: 12px; }
        .ws-marks span { display: inline-flex; align-items: center; gap: 5px; }
        .ws-mark { display: inline-block; width: 10px; height: 10px; border: 1px solid #cbd5e1; border-radius: 2px; vertical-align: middle; }
        .ws-pagebar { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 10px; padding: 14px 4px 10px; font-family: Poppins, sans-serif; font-size: 16px; color: #1e293b; }
        .ws-pagebar .dataTables_length label, .ws-pagebar .dataTables_info { display: inline-flex; align-items: center; gap: 12px; margin: 0; padding: 0; font-weight: 400; }
        .ws-pagebar .dataTables_length select { height: 40px; min-width: 86px; border: 1px solid #284a63; border-radius: 6px; font-size: 16px; }
        .ws-pagebar .pagination { margin: 0; gap: 4px; }
        .ws-pagebar .page-link { min-width: 34px; border: 0; border-radius: 4px; background: transparent; color: #64748b; text-align: center; font-size: 16px; }
        .ws-pagebar .page-item.active .page-link { background: #274b63; color: #fff; font-weight: 700; }
        .ws-pagebar .page-item.disabled .page-link { background: transparent; color: #cbd5e1; }

        @media (max-width: 1399.98px) {
            .ws-stat span { font-size: 16px; }
            .ws-stat strong { font-size: 20px; }
            .ws-field span, .ws-field strong, .ws-form label { font-size: 16px; }
        }
        @media (max-width: 1199.98px) {
            .ws-summary { grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 14px; }
            .ws-top { grid-template-columns: 1fr; }
        }
        @media (max-width: 767.98px) {
            .ws-card > .card-body { padding: 16px; }
            .ws-title { font-size: 24px; }
            .ws-summary, .ws-fields { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .ws-form-grid, .ws-form-grid.is-three { grid-template-columns: 1fr; }
            .ws-stat span, .ws-field span, .ws-form label { font-size: 13px; }
            .ws-stat strong, .ws-field strong { font-size: 15px; }
            .ws-form .form-control, .ws-form .select2-container--bootstrap4 .select2-selection { min-height: 44px; height: auto; }
            .ws-form .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered { line-height: 42px; }
            .ws-form .select2-container--bootstrap4 .select2-selection--single { height: 44px !important; }
            .ws-form-actions .mkt-btn { font-size: 14px; }
        }
    </style>
@endsection

@section('content')
<div class="ws-page">
    <div class="ws-crumb">Marketing &nbsp;›&nbsp; <a href="{{ route('master-costing') }}">Proses</a> &nbsp;› Worksheet &nbsp;› &nbsp; <b>Create Worksheet</b></div>
    <div class="card ws-card">
        <div class="card-body">
            <h1 class="ws-title">Create Worksheet <i class="fas fa-info-circle"></i></h1>
            <p class="ws-subtitle"><i class="fas fa-certificate"></i> Untuk Menambah Data WS</p>

            @if ($shellOptions->isEmpty())
                <div class="ws-alert"><i class="fas fa-exclamation-triangle"></i><span>Belum ada <b>Shell</b> yang dipilih di BOM Costing ini. Isi kolom Shell di BOM dulu sebelum membuat worksheet.</span></div>
            @endif
            @if ($soDetails->isEmpty())
                <div class="ws-alert"><i class="fas fa-exclamation-triangle"></i><span>Costing ini belum punya <b>Sales Order</b>, jadi color / size belum tersedia.</span></div>
            @endif

            <div class="ws-summary">
                <div class="ws-stat"><span>No Costing</span><strong title="{{ $costing->cost_no }}">{{ $costing->cost_no ?: '-' }}</strong></div>
                <div class="ws-stat"><span>No Worksheet</span><strong title="{{ $costing->kpno }}">{{ $costing->kpno ?: '-' }}</strong></div>
                <div class="ws-stat"><span>No Sales Order</span><strong title="{{ $soNumbers }}">{{ $soNumbers ?: '-' }}</strong></div>
                <div class="ws-stat"><span>Delivery Date</span><strong>{{ $costing->deldate ? date('d-m-Y', strtotime($costing->deldate)) : '-' }}</strong></div>
                <div class="ws-stat"><span>No Bill Of Materials</span><strong title="{{ $bom->no_katalog_bom ?? '' }}">{{ $bom->no_katalog_bom ?? '-' }}</strong></div>
            </div>

            <div class="ws-top">
                <section class="ws-panel">
                    <h2>Information Buyer</h2>
                    <div class="ws-fields">
                        <div class="ws-field"><span>Buyer Name</span><strong>{{ $costing->buyer_name ?: '-' }}</strong></div>
                        <div class="ws-field"><span>Kode Buyer</span><strong>{{ $costing->buyer_code ?: '-' }}</strong></div>
                        <div class="ws-field"><span>Brand</span><strong>{{ $costing->brand ?: '-' }}</strong></div>
                        <div class="ws-field"><span>Season</span><strong>{{ trim(($costing->season ?? '') . ' ' . ($costing->season_desc ?? '')) ?: '-' }}</strong></div>
                        <div class="ws-field"><span>Style</span><strong>{{ $costing->style ?: '-' }}</strong></div>
                        <div class="ws-field"><span>QTY Planned</span><strong>{{ number_format((float) $costing->qty_planned, 0, ',', '.') }}</strong></div>
                    </div>
                </section>

                <form class="ws-panel ws-form" id="ws-form" autocomplete="off">
                    <input type="hidden" id="ws-edit-id">
                    <div class="ws-form-grid">
                        <div>
                            <label for="ws-shell">Shell</label>
                            <select id="ws-shell" class="form-control">
                                <option value=""></option>
                                @foreach ($shellOptions as $shell)
                                    <option value="{{ $shell['id'] }}">{{ $shell['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="ws-part">Part</label>
                            <select id="ws-part" class="form-control">
                                <option value=""></option>
                                @foreach ($partOptions as $part)
                                    <option value="{{ $part['id'] }}">{{ $part['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="ws-form-grid is-three">
                        <div>
                            <label for="ws-color">Color</label>
                            <select id="ws-color" class="form-control" multiple></select>
                        </div>
                        <div>
                            <label for="ws-size">Size</label>
                            <select id="ws-size" class="form-control" multiple></select>
                        </div>
                        <div>
                            <label for="ws-qty">Qty</label>
                            <input type="number" id="ws-qty" class="form-control" min="0" step="any">
                        </div>
                    </div>
                    <div class="ws-form-actions">
                        <button type="button" class="mkt-btn mkt-btn--red" id="ws-cancel">Cancel</button>
                        <button type="submit" class="mkt-btn mkt-btn--green" id="ws-save" {{ $shellOptions->isEmpty() || $soDetails->isEmpty() ? 'disabled' : '' }}>Save</button>
                    </div>
                </form>
            </div>

            <div class="ws-legend">
                <span><i class="ws-dot is-bom"></i>BOM</span>
                <span><i class="ws-dot is-input"></i>Input</span>
                <span><i class="ws-dot is-auto"></i>Auto</span>
                <button type="button" class="mkt-btn mkt-btn--red mkt-btn--sm" id="ws-delete-selected" hidden><i class="fas fa-trash"></i> Delete Selected</button>
            </div>
            <div class="ws-table-card">
                <table class="table ws-table" id="ws-table">
                    <colgroup>
                        <col style="width:44px"><col style="width:56px"><col style="width:22%"><col style="width:26%">
                        <col style="width:16%"><col style="width:10%"><col style="width:12%"><col style="width:100px">
                    </colgroup>
                    <thead>
                        <tr>
                            <th class="text-center" style="width:36px"><input type="checkbox" id="ws-check-all" aria-label="Pilih semua"></th>
                            <th style="width:50px">No</th>
                            <th><i class="ws-dot is-bom"></i> Shell</th>
                            <th><i class="ws-dot is-input"></i> Part <i class="fas fa-pen"></i></th>
                            <th><i class="ws-dot is-auto"></i> Color</th>
                            <th><i class="ws-dot is-auto"></i> Size</th>
                            <th class="text-right"><i class="ws-dot is-input"></i> Qty</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
            <div class="ws-marks">
                <span><i class="ws-mark" style="background:#fff3c4"></i>Belum tersimpan</span>
                <span><i class="ws-mark" style="background:#dbeafe"></i>Just Pasted</span>
                <span><i class="ws-mark" style="background:#fde2e2"></i>Error</span>
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
    $(function () {
        const soDetails = @json($soDetails);
        const urls = {
            rows: "{{ route('marketing-worksheet-rows', $costing->id) }}",
            store: "{{ route('marketing-worksheet-store', $costing->id) }}",
            update: "{{ route('marketing-worksheet-update', [$costing->id, ':id']) }}",
            destroy: "{{ route('marketing-worksheet-destroy', $costing->id) }}",
            batch: "{{ route('marketing-worksheet-batch', $costing->id) }}"
        };
        const shellOptions = @json($shellOptions);
        const partOptions = @json($partOptions);

        const GRID = [
            { key: 'shell', field: 'id_panel', options: shellOptions },
            { key: 'part', field: 'id_part', options: partOptions },
            { key: 'color' },
            { key: 'size' },
            { key: 'qty', field: 'qty', number: true }
        ];
        const GRID_COL_OFFSET = 2;
        const pending = new Map();
        const invalid = new Map();
        const pasted = new Set();
        let sel = null;
        let editing = null;
        let drag = null;
        let fillTarget = null;

        const clamp = (n, min, max) => Math.max(min, Math.min(max, n));
        const optionName = (g, id) => (g.options.find(o => o.id === Number(id)) || {}).name || '';
        const gridRows = () => table.rows({ order: 'applied' }).data().toArray();

        function cellValue(row, g) {
            if (!g.field) return row[g.key];
            const p = pending.get(row.id);
            return p && g.field in p ? p[g.field] : row[g.field];
        }
        function cellText(row, g) {
            const key = `${row.id}|${g.field}`;
            if (invalid.has(key)) return invalid.get(key);
            const value = cellValue(row, g);
            if (g.options) return optionName(g, value) || '-';
            if (g.number) return fmt(value);
            return value ?? '-';
        }
        function copyText(row, g) {
            const key = `${row.id}|${g.field}`;
            if (invalid.has(key)) return invalid.get(key);
            const value = cellValue(row, g);
            return g.options ? optionName(g, value) : String(value ?? '');
        }
        function parseNumber(text) {
            let s = String(text ?? '').trim().replace(/\s/g, '');
            if (s === '') return NaN;
            if (s.includes(',') && s.includes('.')) {
                s = s.lastIndexOf(',') > s.lastIndexOf('.') ? s.replace(/\./g, '').replace(',', '.') : s.replace(/,/g, '');
            } else if (s.includes(',')) {
                s = s.replace(',', '.');
            } else if (/^\d{1,3}(\.\d{3})+$/.test(s)) {
                s = s.replace(/\./g, '');
            }
            const n = Number(s);
            return Number.isFinite(n) && n >= 0 ? n : NaN;
        }
        function setCell(row, g, text, isPaste) {
            if (!g.field || !row) return;
            const key = `${row.id}|${g.field}`;
            let value = null;
            if (g.options) {
                const t = String(text ?? '').trim().toLowerCase();
                const option = g.options.find(o => String(o.id) === t || o.name.toLowerCase() === t || (o.label || '').toLowerCase() === t);
                value = option ? option.id : null;
            } else {
                const n = parseNumber(text);
                value = Number.isNaN(n) ? null : n;
            }
            if (value === null) {
                invalid.set(key, String(text ?? '').trim());
            } else {
                invalid.delete(key);
                const p = pending.get(row.id) || {};
                if (value === row[g.field]) delete p[g.field]; else p[g.field] = value;
                Object.keys(p).length ? pending.set(row.id, p) : pending.delete(row.id);
            }
            isPaste ? pasted.add(key) : pasted.delete(key);
        }

        function bounds() {
            if (!sel) return null;
            return {
                r1: Math.min(sel.anchor.r, sel.focus.r), r2: Math.max(sel.anchor.r, sel.focus.r),
                c1: Math.min(sel.anchor.c, sel.focus.c), c2: Math.max(sel.anchor.c, sel.focus.c)
            };
        }
        function posOf(td) {
            return { r: table.page.info().start + $(td).closest('tr').index(), c: Number(td.dataset.c) };
        }
        function cellNode(r, c) {
            const info = table.page.info();
            const tr = table.rows({ page: 'current' }).nodes().toArray()[r - info.start];
            return tr ? tr.cells[c + GRID_COL_OFFSET] : null;
        }
        function ensurePage(r) {
            const info = table.page.info();
            if (r < info.start || r >= info.end) table.page(Math.floor(r / info.length)).draw('page');
        }

        function paintGrid() {
            const info = table.page.info();
            const rows = gridRows();
            const b = bounds();
            $('#ws-table .ws-fill-handle').remove();
            table.rows({ page: 'current' }).nodes().toArray().forEach(function (tr, i) {
                const r = info.start + i;
                const row = rows[r];
                if (!row) return;
                GRID.forEach(function (g, c) {
                    const td = tr.cells[c + GRID_COL_OFFSET];
                    const key = `${row.id}|${g.field}`;
                    const $td = $(td);
                    if (!$td.hasClass('is-editing-cell')) $td.text(cellText(row, g)).attr('title', cellText(row, g));
                    const inSel = b && r >= b.r1 && r <= b.r2 && c >= b.c1 && c <= b.c2;
                    const inFill = fillTarget && b && r >= fillTarget.r1 && r <= fillTarget.r2 && c >= b.c1 && c <= b.c2;
                    $td.toggleClass('is-dirty', Boolean(g.field && pending.get(row.id) && g.field in pending.get(row.id)))
                        .toggleClass('is-invalid', invalid.has(key))
                        .toggleClass('is-pasted', pasted.has(key))
                        .toggleClass('is-selected', Boolean(inSel))
                        .toggleClass('is-active', Boolean(sel && sel.focus.r === r && sel.focus.c === c))
                        .toggleClass('is-fill-preview', Boolean(inFill));
                });
            });
            if (b && !editing && GRID.slice(b.c1, b.c2 + 1).some(g => g.field)) {
                const td = cellNode(b.r2, b.c2);
                if (td) $('<span class="ws-fill-handle" title="Tarik untuk mengisi"></span>').appendTo(td);
            }
        }

        function startEdit(initial) {
            if (!sel) return;
            const { r, c } = sel.focus;
            const g = GRID[c];
            const row = gridRows()[r];
            if (!g.field || !row) return;
            ensurePage(r);
            sel = { anchor: { r, c }, focus: { r, c } };
            paintGrid();
            const td = cellNode(r, c);
            if (!td) return;
            editing = { r, c, td };
            $('#ws-table .ws-fill-handle').remove();
            $(td).addClass('is-editing-cell').empty();
            if (g.number) {
                const $input = $('<input type="text" class="ws-cell-input">').val(initial ?? copyText(row, g)).appendTo(td).trigger('focus');
                if (initial === undefined) $input.trigger('select');
                $input.on('keydown', function (ev) {
                    if (ev.key === 'Enter' || ev.key === 'Tab') {
                        ev.preventDefault();
                        commitEdit();
                        moveFocus(ev.key === 'Enter' ? 1 : 0, ev.key === 'Tab' ? 1 : 0, false);
                    } else if (ev.key === 'Escape') {
                        cancelEdit();
                    }
                });
                $input.on('blur', () => commitEdit());
                return;
            }
            const $select = $('<select class="ws-cell-input"></select>').appendTo(td).append(new Option('', ''));
            g.options.forEach(o => $select.append(new Option(o.label || o.name, o.id, false, Number(cellValue(row, g)) === o.id)));
            $select.select2({ theme: 'bootstrap4', width: '100%', dropdownParent: $('body') }).select2('open');
            if (initial) $('.select2-container--open .select2-search__field').val(initial).trigger('input');
            $select.on('select2:select', () => commitEdit($select.val()));
            $select.on('select2:close', () => setTimeout(() => { if (editing && editing.td === td) cancelEdit(); }, 0));
        }
        function closeEditor() {
            const td = editing.td;
            editing = null;
            const $select = $(td).find('select.ws-cell-input');
            if ($select.data('select2')) $select.select2('destroy');
            $(td).removeClass('is-editing-cell').empty();
        }
        function commitEdit(value) {
            if (!editing) return;
            const { r, c, td } = editing;
            const $input = $(td).find('input.ws-cell-input');
            const text = value !== undefined ? value : ($input.length ? $input.val() : undefined);
            closeEditor();
            if (text === undefined) {
                paintGrid();
                return;
            }
            setCell(gridRows()[r], GRID[c], text, false);
            saveGrid();
        }
        function cancelEdit() {
            if (!editing) return;
            closeEditor();
            paintGrid();
        }
        function moveFocus(dr, dc, extend) {
            if (!sel) return;
            const rows = gridRows();
            const f = { r: clamp(sel.focus.r + dr, 0, rows.length - 1), c: clamp(sel.focus.c + dc, 0, GRID.length - 1) };
            sel = extend ? { anchor: sel.anchor, focus: f } : { anchor: f, focus: { ...f } };
            ensurePage(f.r);
            paintGrid();
        }
        function applyFill() {
            const b = bounds();
            const rows = gridRows();
            const height = b.r2 - b.r1 + 1;
            pasted.clear();
            for (let c = b.c1; c <= b.c2; c++) {
                const g = GRID[c];
                if (!g.field) continue;
                for (let r = fillTarget.r1; r <= fillTarget.r2; r++) {
                    const source = rows[b.r1 + (((r - b.r1) % height) + height) % height];
                    setCell(rows[r], g, copyText(source, g), false);
                }
            }
            sel = { anchor: { r: Math.min(b.r1, fillTarget.r1), c: b.c1 }, focus: { r: Math.max(b.r2, fillTarget.r2), c: b.c2 } };
        }

        $('#ws-table tbody').on('mousedown', 'td.ws-cell', function (e) {
            if (e.button !== 0 || $(e.target).closest('.ws-cell-input, .select2-container, .ws-fill-handle').length) return;
            commitEdit();
            const pos = posOf(this);
            sel = e.shiftKey && sel ? { anchor: sel.anchor, focus: pos } : { anchor: pos, focus: { ...pos } };
            drag = 'select';
            $('body').addClass('ws-dragging');
            if (document.activeElement && document.activeElement !== document.body) document.activeElement.blur();
            paintGrid();
            e.preventDefault();
        });
        $('#ws-table tbody').on('mousedown', '.ws-fill-handle', function (e) {
            drag = 'fill';
            fillTarget = null;
            $('body').addClass('ws-filling');
            e.preventDefault();
            e.stopPropagation();
        });
        $('#ws-table tbody').on('mouseenter', 'td.ws-cell', function () {
            if (!drag || !sel) return;
            const pos = posOf(this);
            if (drag === 'select') {
                sel.focus = pos;
            } else {
                const b = bounds();
                fillTarget = pos.r > b.r2 ? { r1: b.r2 + 1, r2: pos.r } : (pos.r < b.r1 ? { r1: pos.r, r2: b.r1 - 1 } : null);
            }
            paintGrid();
        });
        $('#ws-table tbody').on('dblclick', 'td.ws-cell', () => startEdit());
        $(document).on('mouseup', function () {
            if (!drag) return;
            const filled = drag === 'fill' && fillTarget;
            if (filled) applyFill();
            drag = null;
            fillTarget = null;
            $('body').removeClass('ws-dragging ws-filling');
            filled ? saveGrid() : paintGrid();
        });
        $(document).on('mousedown', function (e) {
            if (!sel || $(e.target).closest('#ws-table tbody, .select2-container, .select2-dropdown').length) return;
            commitEdit();
            sel = null;
            paintGrid();
        });
        $(document).on('keydown', function (e) {
            if (!sel || editing || $(e.target).is('input, textarea, select')) return;
            const moves = { ArrowUp: [-1, 0], ArrowDown: [1, 0], ArrowLeft: [0, -1], ArrowRight: [0, 1] };
            if (moves[e.key]) {
                moveFocus(moves[e.key][0], moves[e.key][1], e.shiftKey);
                e.preventDefault();
            } else if (e.key === 'Tab') {
                moveFocus(0, e.shiftKey ? -1 : 1, false);
                e.preventDefault();
            } else if (e.key === 'Enter' || e.key === 'F2') {
                startEdit();
                e.preventDefault();
            } else if (e.key === 'Escape') {
                sel = null;
                paintGrid();
            } else if (!e.ctrlKey && !e.metaKey && !e.altKey && e.key.length === 1) {
                startEdit(e.key);
                e.preventDefault();
            }
        });
        $(document).on('copy', function (e) {
            if (!sel || editing || $(e.target).is('input, textarea')) return;
            const b = bounds();
            const rows = gridRows();
            const lines = [];
            for (let r = b.r1; r <= b.r2; r++) lines.push(GRID.slice(b.c1, b.c2 + 1).map(g => copyText(rows[r], g)).join('\t'));
            e.originalEvent.clipboardData.setData('text/plain', lines.join('\r\n'));
            e.preventDefault();
        });
        $(document).on('paste', function (e) {
            if (!sel || editing || $(e.target).is('input, textarea')) return;
            const text = e.originalEvent.clipboardData.getData('text/plain');
            if (!text) return;
            e.preventDefault();
            const data = text.replace(/\r/g, '').replace(/\n$/, '').split('\n').map(line => line.split('\t'));
            const rows = gridRows();
            const b = bounds();
            const single = data.length === 1 && data[0].length === 1;
            const rEnd = single ? b.r2 : Math.min(rows.length - 1, b.r1 + data.length - 1);
            const cEnd = single ? b.c2 : Math.min(GRID.length - 1, b.c1 + data[0].length - 1);
            pasted.clear();
            for (let r = b.r1; r <= rEnd; r++) {
                for (let c = b.c1; c <= cEnd; c++) {
                    if (GRID[c].field) setCell(rows[r], GRID[c], single ? data[0][0] : ((data[r - b.r1] || [])[c - b.c1] ?? ''), true);
                }
            }
            sel = { anchor: { r: b.r1, c: b.c1 }, focus: { r: rEnd, c: cEnd } };
            saveGrid();
        });

        function saveGrid() {
            paintGrid();
            const rows = [...pending.entries()]
                .filter(([id]) => !GRID.some(g => g.field && invalid.has(`${id}|${g.field}`)))
                .map(([id, p]) => {
                    const row = rowsById.get(String(id));
                    return { id, id_panel: p.id_panel ?? row.id_panel, id_part: p.id_part ?? row.id_part, qty: p.qty ?? row.qty };
                });
            if (!rows.length) return;
            rows.forEach(row => pending.delete(row.id));
            const reload = () => $.get(urls.rows).done(res => setRows(res.rows || [], true));
            $.ajax({ url: urls.batch, method: 'POST', headers, data: { rows } })
                .done(res => {
                    if (res.status === 200) {
                        const fresh = new Map((res.rows || []).map(row => [row.id, row]));
                        rowsById = new Map((res.rows || []).map(row => [String(row.id), row]));
                        table.rows().every(function () {
                            const row = fresh.get(this.data().id);
                            if (row) this.data(row);
                        });
                        table.draw(false);
                        return;
                    }
                    Swal.fire({ icon: 'warning', title: 'Gagal menyimpan', text: res.message || 'Terjadi kesalahan.' });
                    reload();
                })
                .fail(xhr => {
                    fail(xhr);
                    reload();
                });
        }
        const headers = { 'X-CSRF-TOKEN': '{{ csrf_token() }}' };
        const esc = value => $('<div>').text(value ?? '').html();
        const fmt = value => Number(value || 0).toLocaleString('id-ID', { maximumFractionDigits: 2 });
        const addNote = $('#ws-form-note').text();
        let rowsById = new Map();

        const unique = list => [...new Set(list.filter(Boolean))];
        unique(soDetails.map(d => d.color)).forEach(color => $('#ws-color').append(new Option(color, color)));
        unique(soDetails.map(d => d.size)).forEach(size => $('#ws-size').append(new Option(size, size)));

        $('#ws-shell').select2({ theme: 'bootstrap4', width: '100%', placeholder: 'Choose Shell' });
        $('#ws-part').select2({ theme: 'bootstrap4', width: '100%', placeholder: 'Choose Part' });
        $('#ws-color').select2({ theme: 'bootstrap4', width: '100%', placeholder: 'All Color' });
        $('#ws-size').select2({ theme: 'bootstrap4', width: '100%', placeholder: 'All Size' });

        const table = $('#ws-table').DataTable({
            data: [],
            ordering: false,
            searching: false,
            autoWidth: false,
            pageLength: 4,
            lengthMenu: [[4, 10, 25, 50], [4, 10, 25, 50]],
            dom: '<"ws-table-scroll"t><"ws-pagebar"<"d-flex align-items-center"li>p>',
            language: {
                lengthMenu: 'Item Per Page _MENU_',
                info: 'Of _TOTAL_',
                infoEmpty: 'Of 0',
                emptyTable: 'Belum ada data worksheet.',
                paginate: { previous: '<i class="fas fa-chevron-left"></i>', next: '<i class="fas fa-chevron-right"></i>' }
            },
            columns: [
                { data: 'id', className: 'text-center', render: id => `<input type="checkbox" class="ws-check" value="${id}" aria-label="Pilih baris">` },
                { data: null, render: (data, type, row, meta) => meta.row + 1 },
                ...GRID.map((g, c) => ({
                    data: null,
                    className: 'ws-cell' + (g.field ? '' : ' is-readonly') + (g.number ? ' text-right' : ''),
                    render: (data, type, row) => esc(cellText(row, g)),
                    createdCell: td => { td.dataset.c = c; }
                })),
                {
                    data: 'id', render: id => `<span class="ws-actions">
                        <button type="button" class="mkt-ico mkt-ico--delete ws-delete" data-id="${id}" data-tip="Delete"><i class="fas fa-trash"></i></button>
                    </span>`
                }
            ],
            createdRow: (tr, row) => $(tr).toggleClass('is-editing', String(row.id) === $('#ws-edit-id').val())
        });

        function setRows(rows, keepSel) {
            rowsById = new Map(rows.map(row => [String(row.id), row]));
            [...pending.keys()].forEach(id => { if (!rowsById.has(String(id))) pending.delete(id); });
            [...invalid.keys(), ...pasted].forEach(key => {
                if (!rowsById.has(key.split('|')[0])) { invalid.delete(key); pasted.delete(key); }
            });
            if (!keepSel) sel = null;
            table.clear().rows.add(rows).draw(false);
            updateDeleteSelected();
        }

        function updateDeleteSelected() {
            const count = $('#ws-table .ws-check:checked').length;
            $('#ws-delete-selected').prop('hidden', !count).html(`<i class="fas fa-trash"></i> Hapus terpilih (${count})`);
        }

        function setEditMode(row) {
            $('#ws-edit-id').val(row ? row.id : '');
            $('#ws-color, #ws-size').prop('disabled', Boolean(row));
            if (row) {
                $('#ws-shell').val(String(row.id_panel)).trigger('change.select2');
                $('#ws-part').val(String(row.id_part)).trigger('change.select2');
                $('#ws-color').val([row.color]).trigger('change.select2');
                $('#ws-size').val([row.size]).trigger('change.select2');
                $('#ws-qty').val(row.qty);
                $('#ws-form-note').addClass('is-edit').text(`Edit baris ${row.color} / ${row.size}. Klik Cancel untuk batal.`);
            } else {
                $('#ws-shell, #ws-part, #ws-color, #ws-size').val(null).trigger('change.select2');
                $('#ws-qty').val('');
                $('#ws-form-note').removeClass('is-edit').text(addNote);
            }
            $('#ws-table tbody tr').each(function () {
                const data = table.row(this).data();
                $(this).toggleClass('is-editing', Boolean(row && data && data.id === row.id));
            });
        }

        function handle(res) {
            if (res.status !== 200) {
                Swal.fire({ icon: 'warning', title: 'Gagal', text: res.message || 'Terjadi kesalahan.' });
                return false;
            }
            if (res.rows) setRows(res.rows);
            Swal.fire({ icon: 'success', title: res.message, timer: 1500, showConfirmButton: false });
            return true;
        }

        function fail(xhr) {
            const errors = xhr.responseJSON?.errors;
            Swal.fire({ icon: 'error', title: 'Gagal', text: errors ? Object.values(errors).flat().join('\n') : (xhr.responseJSON?.message || 'Terjadi kesalahan.') });
        }

        $.get(urls.rows).done(res => setRows(res.rows || []));

        $('#ws-form').on('submit', function (event) {
            event.preventDefault();
            const shell = $('#ws-shell').val();
            const part = $('#ws-part').val();
            if (!shell || !part) {
                Swal.fire({ icon: 'warning', title: 'Shell dan Part wajib dipilih.' });
                return;
            }
            const qty = $('#ws-qty').val();
            const editId = $('#ws-edit-id').val();

            if (editId) {
                if (qty === '') {
                    Swal.fire({ icon: 'warning', title: 'Qty wajib diisi.' });
                    return;
                }
                $.ajax({ url: urls.update.replace(':id', editId), method: 'PUT', headers, data: { id_panel: shell, id_part: part, qty } })
                    .done(res => { if (handle(res)) setEditMode(null); })
                    .fail(fail);
                return;
            }

            const colors = $('#ws-color').val();
            const sizes = $('#ws-size').val();
            const ids = soDetails
                .filter(d => (!colors.length || colors.includes(d.color)) && (!sizes.length || sizes.includes(d.size)))
                .map(d => d.id);
            if (!ids.length) {
                Swal.fire({ icon: 'warning', title: 'Tidak ada color / size yang cocok di Sales Order.' });
                return;
            }
            $.ajax({ url: urls.store, method: 'POST', headers, data: { id_panel: shell, id_part: part, id_so_det: ids, qty: qty === '' ? null : qty } })
                .done(res => { if (handle(res)) setEditMode(null); })
                .fail(fail);
        });

        $('#ws-cancel').on('click', () => setEditMode(null));

        $('#ws-table').on('click', '.ws-edit', function () {
            const row = rowsById.get(String($(this).data('id')));
            if (row) setEditMode(row);
        });

        function destroy(ids) {
            Swal.fire({ icon: 'question', title: `Hapus ${ids.length} baris worksheet?`, showCancelButton: true, confirmButtonText: 'Hapus', cancelButtonText: 'Batal' })
                .then(result => {
                    if (!result.isConfirmed) return;
                    $.ajax({ url: urls.destroy, method: 'DELETE', headers, data: { ids } })
                        .done(res => {
                            if (ids.map(String).includes($('#ws-edit-id').val())) setEditMode(null);
                            handle(res);
                        })
                        .fail(fail);
                });
        }

        $('#ws-table').on('click', '.ws-delete', function () { destroy([$(this).data('id')]); });
        $('#ws-delete-selected').on('click', function () {
            destroy($('#ws-table .ws-check:checked').map((i, el) => el.value).get());
        });
        $('#ws-check-all').on('change', function () {
            $('#ws-table .ws-check').prop('checked', this.checked);
            updateDeleteSelected();
        });
        $('#ws-table').on('change', '.ws-check', updateDeleteSelected);
        table.on('draw', function () {
            $('#ws-check-all').prop('checked', false);
            updateDeleteSelected();
            paintGrid();
        });
    });
    </script>
@endsection
