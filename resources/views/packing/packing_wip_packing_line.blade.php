@extends('layouts.index')

@section('custom-link')
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@endsection

@section('content')
<style>
    .wip-page { font-family: 'Segoe UI', Arial, sans-serif; }

    /* ══ TOP BAR ══ */
    .wip-topbar {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,.08);
        padding: 14px 20px;
        display: flex;
        align-items: center;
        gap: 14px;
        flex-wrap: wrap;
        margin-bottom: 16px;
    }
    .wip-stat {
        display: flex; flex-direction: column;
        padding: 8px 18px; border-radius: 8px; min-width: 130px;
    }
    .wip-stat.stat-date  { background: linear-gradient(135deg,#f9c74f,#f3a020); }
    .wip-stat.stat-total { background: linear-gradient(135deg,#4361ee,#3a0ca3); }
    .wip-stat-label { font-size: 10px; font-weight: 700; letter-spacing: 1.2px; text-transform: uppercase; margin-bottom: 3px; }
    .wip-stat.stat-date  .wip-stat-label { color: rgba(0,0,0,.45); }
    .wip-stat.stat-total .wip-stat-label { color: rgba(255,255,255,.65); }
    .wip-stat-value { font-weight: 800; line-height: 1; }
    .wip-stat.stat-date  .wip-stat-value { color: #fff; font-size: 16px; }
    .wip-stat.stat-total .wip-stat-value { color: #fff; font-size: 30px; }

    .wip-divider { width: 1px; height: 44px; background: #eee; flex-shrink: 0; }

    /* filter group */
    .wip-filter-group { display: flex; flex-direction: column; gap: 3px; min-width: 200px; max-width: 260px; flex: 1; }
    .wip-filter-group label { font-size: 10px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; color: #999; margin: 0; }

    /* select2 overrides */
    .select2-container--bootstrap4 .select2-selection--multiple {
        min-height: 36px !important;
        border: 2px solid #e8e8e8 !important;
        border-radius: 8px !important;
        font-size: 12px !important;
    }
    .select2-container--bootstrap4 .select2-selection--multiple:focus,
    .select2-container--bootstrap4.select2-container--focus .select2-selection--multiple {
        border-color: #4361ee !important;
        box-shadow: none !important;
    }
    .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__choice {
        background: #4361ee !important;
        border: none !important;
        border-radius: 5px !important;
        color: #fff !important;
        font-size: 11px !important;
        font-weight: 700 !important;
        padding: 2px 8px !important;
    }
    .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__choice__remove {
        color: rgba(255,255,255,.75) !important;
        margin-right: 4px !important;
    }
    .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__choice__remove:hover { color: #fff !important; }
    .select2-dropdown { border: 2px solid #4361ee !important; border-radius: 8px !important; font-size: 12px !important; }
    .select2-container--bootstrap4 .select2-results__option--highlighted { background: #4361ee !important; }
    .select2-search--dropdown .select2-search__field { border-radius: 6px !important; border: 1.5px solid #e0e0e0 !important; font-size: 12px !important; padding: 5px 8px !important; }

    .wip-badge { background: #eef2ff; color: #4361ee; border-radius: 20px; padding: 4px 12px; font-size: 12px; font-weight: 700; white-space: nowrap; }

    .wip-meta { margin-left: auto; display: flex; align-items: center; gap: 12px; font-size: 11px; color: #aaa; flex-wrap: wrap; }
    .wip-meta b { color: #666; }
    #refresh-btn {
        background: #4361ee; color: #fff; border: none; border-radius: 8px;
        padding: 8px 16px; font-size: 12px; font-weight: 700; cursor: pointer; transition: background .2s;
    }
    #refresh-btn:hover { background: #3a0ca3; }

    /* ══ SCROLL CONTAINER ══ */
    .wip-scroll-outer {
        overflow: auto;
        max-height: calc(100vh - 230px);
        border-radius: 10px;
        border: 1px solid #e8e8e8;
        background: #f8f9fb;
    }
    .wip-scroll-outer::-webkit-scrollbar { width: 6px; height: 8px; }
    .wip-scroll-outer::-webkit-scrollbar-track { background: #f0f0f0; border-radius: 4px; }
    .wip-scroll-outer::-webkit-scrollbar-thumb { background: #c5c5c5; border-radius: 4px; }
    .wip-scroll-outer::-webkit-scrollbar-thumb:hover { background: #999; }

    .wip-table { display: inline-flex; flex-direction: column; min-width: 100%; }

    /* ── Header sticky ── */
    .wip-header-row {
        display: flex; flex-wrap: nowrap; gap: 12px;
        padding: 10px 12px 8px;
        position: sticky; top: 0; z-index: 20;
        background: #f8f9fb;
        border-bottom: 2px solid #e0e0e0;
    }
    .wip-col-header {
        width: 210px; flex-shrink: 0; border-radius: 10px;
        padding: 11px 16px; color: #fff;
        font-size: 15px; font-weight: 800; letter-spacing: .4px;
        display: flex; justify-content: space-between; align-items: center;
    }
    .wip-col-header .line-wip-total {
        background: rgba(255,255,255,.22); border-radius: 20px;
        padding: 3px 10px; font-size: 12px; font-weight: 700;
    }

    /* ── Body ── */
    .wip-body-row { display: flex; flex-wrap: nowrap; gap: 12px; padding: 10px 12px 12px; align-items: flex-start; }
    .wip-col-body { width: 210px; flex-shrink: 0; background: #fff; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,.07); overflow: hidden; }

    .wip-po-item { padding: 12px 16px; border-bottom: 1px solid #f0f0f0; transition: background .15s; cursor: pointer; }
    .wip-po-item:last-child { border-bottom: none; }
    .wip-po-item:hover { background: #eef2ff; }

    .wip-po-label     { font-size: 10px; font-weight: 700; color: #bbb; letter-spacing: 1px; text-transform: uppercase; margin-bottom: 1px; }
    .wip-po-number    { font-size: 14px; font-weight: 800; color: #333; margin-bottom: 6px; }
    .wip-po-wip-label { font-size: 10px; font-weight: 700; color: #bbb; letter-spacing: 1px; text-transform: uppercase; margin-bottom: 1px; }
    .wip-po-wip       { font-size: 38px; font-weight: 900; line-height: 1; margin-bottom: 8px; }
    .wip-po-wip.pos   { color: #1a7f4b; }
    .wip-po-wip.neg   { color: #cc2c2c; }
    .wip-po-wip.zer   { color: #ccc; }

    .wip-po-badges   { display: flex; gap: 5px; flex-wrap: wrap; }
    .wip-badge-sa    { background: #e8eaf6; color: #3949ab; border-radius: 6px; padding: 3px 8px; font-size: 11px; font-weight: 700; }
    .wip-badge-out   { background: #e6f4ea; color: #1a7f4b; border-radius: 6px; padding: 3px 8px; font-size: 11px; font-weight: 700; }
    .wip-badge-trf   { background: #fff4e0; color: #c75a00; border-radius: 6px; padding: 3px 8px; font-size: 11px; font-weight: 700; }

    /* skeleton */
    .wip-sk-hdr  { width: 210px; flex-shrink: 0; height: 46px; background: #dce2f5; border-radius: 10px; animation: sk-pulse 1.4s infinite; }
    .wip-sk-body { width: 210px; flex-shrink: 0; border-radius: 10px; background: #fff; box-shadow: 0 2px 8px rgba(0,0,0,.07); overflow: hidden; animation: sk-pulse 1.4s infinite; }
    .sk-row      { height: 90px; background: #f3f4f8; margin: 10px; border-radius: 8px; }
    @keyframes sk-pulse { 0%,100%{opacity:1} 50%{opacity:.4} }

    .wip-empty { color: #ccc; font-size: 14px; font-weight: 600; padding: 60px 20px; text-align: center; white-space: nowrap; }
    .wip-empty-icon { font-size: 36px; margin-bottom: 10px; }

    /* ── stagger reveal ── */
    .wip-col-header,
    .wip-col-body {
        opacity: 0;
        transform: translateY(16px);
        transition: opacity .35s ease, transform .35s ease;
    }
    .wip-col-header.visible,
    .wip-col-body.visible {
        opacity: 1;
        transform: translateY(0);
    }
</style>

<div class="wip-page">

    {{-- TOP BAR --}}
    <div class="wip-topbar">
        <div class="wip-stat stat-date">
            <div class="wip-stat-label">Tanggal</div>
            <div class="wip-stat-value" id="current-datetime">--</div>
        </div>
        <div class="wip-stat stat-total">
            <div class="wip-stat-label">Total WIP</div>
            <div class="wip-stat-value" id="total-wip">—</div>
        </div>

        <div class="wip-divider"></div>

        {{-- Filter LINE --}}
        <div class="wip-filter-group">
            <label>Filter Line</label>
            <select id="filter-line" multiple="multiple" style="width:100%">
            </select>
        </div>

        {{-- Filter PO --}}
        <div class="wip-filter-group">
            <label>Filter PO</label>
            <select id="filter-po" multiple="multiple" style="width:100%">
            </select>
        </div>

        <div class="wip-badge" id="line-count">0 line</div>

        <div class="wip-meta">
            <span>Update: <b id="last-update">—</b></span>
            <span>Auto: <b id="countdown">120</b>s</span>
            <button id="refresh-btn" onclick="loadWip()">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
            <button id="export-btn"
               style="background:#1a7f4b;color:#fff;border:none;border-radius:8px;padding:8px 16px;font-size:12px;font-weight:700;cursor:pointer;white-space:nowrap;"
               onclick="exportExcel()">
                <i class="fas fa-file-excel"></i> Export Excel
            </button>
        </div>
    </div>

    {{-- SCROLL AREA --}}
    <div class="wip-scroll-outer">
        <div class="wip-table">
            <div class="wip-header-row" id="lines-header">
                @for ($i = 0; $i < 6; $i++)
                <div class="wip-sk-hdr"></div>
                @endfor
            </div>
            <div class="wip-body-row" id="lines-body">
                @for ($i = 0; $i < 6; $i++)
                <div class="wip-sk-body"><div class="sk-row"></div><div class="sk-row"></div></div>
                @endfor
            </div>
        </div>
    </div>

</div>
{{-- Modal Detail PO --}}
<div class="modal fade" id="modalDetailPO" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header" id="modal-header-po" style="background:linear-gradient(135deg,#4361ee,#3a0ca3);">
                <div>
                    <div style="font-size:11px;font-weight:700;letter-spacing:1px;color:rgba(255,255,255,.65);text-transform:uppercase;">Detail WIP</div>
                    <h5 class="modal-title text-white fw-bold mb-0" id="modal-title-po">—</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                {{-- Summary bar --}}
                <div class="d-flex gap-3 px-3 py-2 border-bottom bg-light" id="modal-summary">
                    <div class="text-center px-3 py-1">
                        <div style="font-size:10px;font-weight:700;color:#aaa;letter-spacing:1px;">SALDO AWAL</div>
                        <div style="font-size:22px;font-weight:900;color:#3949ab;" id="ms-sa">—</div>
                    </div>
                    <div class="text-center px-3 py-1">
                        <div style="font-size:10px;font-weight:700;color:#aaa;letter-spacing:1px;">OUTPUT</div>
                        <div style="font-size:22px;font-weight:900;color:#1a7f4b;" id="ms-out">—</div>
                    </div>
                    <div class="text-center px-3 py-1">
                        <div style="font-size:10px;font-weight:700;color:#aaa;letter-spacing:1px;">TRANSFER</div>
                        <div style="font-size:22px;font-weight:900;color:#c75a00;" id="ms-trf">—</div>
                    </div>
                    <div class="text-center px-3 py-1 ms-auto">
                        <div style="font-size:10px;font-weight:700;color:#aaa;letter-spacing:1px;">WIP</div>
                        <div style="font-size:28px;font-weight:900;" id="ms-wip">—</div>
                    </div>
                </div>

                {{-- Tabel detail --}}
                <div class="p-3">
                    <div id="modal-loading" class="text-center py-4 text-muted">
                        <div class="spinner-border spinner-border-sm me-2"></div> Memuat detail...
                    </div>
                    <div id="modal-table-wrap" style="display:none;">
                        <table class="table table-bordered table-hover table-sm mb-0" style="font-size:13px;">
                            <thead class="table-dark">
                                <tr>
                                    <th>Buyer</th>
                                    <th>Style</th>
                                    <th>Color</th>
                                    <th>Size</th>
                                    <th class="text-end">SA</th>
                                    <th class="text-end">Output ▲</th>
                                    <th class="text-end">Transfer ▼</th>
                                    <th class="text-end">WIP</th>
                                </tr>
                            </thead>
                            <tbody id="modal-tbody"></tbody>
                            <tfoot>
                                <tr class="fw-bold table-secondary">
                                    <td colspan="4">Total</td>
                                    <td class="text-end" id="ft-sa">0</td>
                                    <td class="text-end" id="ft-out">0</td>
                                    <td class="text-end" id="ft-trf">0</td>
                                    <td class="text-end" id="ft-wip">0</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div id="modal-empty" style="display:none;" class="text-center py-4 text-muted">Tidak ada data detail.</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('custom-script')
<script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
<script>
var LINE_COLORS = [
    'linear-gradient(135deg,#4361ee,#3a0ca3)',
    'linear-gradient(135deg,#f77f00,#d62828)',
    'linear-gradient(135deg,#1a7f4b,#2d9e68)',
    'linear-gradient(135deg,#7209b7,#560bad)',
    'linear-gradient(135deg,#0077b6,#00b4d8)',
    'linear-gradient(135deg,#c1121f,#e63946)',
    'linear-gradient(135deg,#606c38,#3e4d22)',
    'linear-gradient(135deg,#9d4edd,#5a189a)',
    'linear-gradient(135deg,#005f73,#0a9396)',
    'linear-gradient(135deg,#6d6875,#b5838d)',
];

function padZ(n) { return n < 10 ? '0' + n : n; }
function fmt(n)  { return Number(n).toLocaleString('id-ID'); }
function nowStr() {
    var d = new Date();
    return padZ(d.getDate())+'/'+padZ(d.getMonth()+1)+'/'+d.getFullYear()
         +' '+padZ(d.getHours())+':'+padZ(d.getMinutes());
}
function updateClock() { $('#current-datetime').text(nowStr()); }

var countdownVal, countdownTimer;
function startCountdown() {
    clearInterval(countdownTimer);
    countdownVal = 120;
    $('#countdown').text(countdownVal);
    countdownTimer = setInterval(function() {
        countdownVal--;
        $('#countdown').text(countdownVal);
        if (countdownVal <= 0) loadWip();
    }, 1000);
}

var allLines = {}, allKeys = [];

/* ── populate Select2 options setelah data load ── */
function populateFilters() {
    /* kumpulkan semua PO unik */
    var poSet = {};
    $.each(allKeys, function(_, line) {
        $.each(allLines[line], function(_, item) { poSet[item.po] = true; });
    });
    var allPOs = Object.keys(poSet).sort();

    /* reset & isi ulang option */
    $('#filter-line').empty();
    $('#filter-po').empty();

    $.each(allKeys, function(_, line) {
        $('#filter-line').append(new Option(line, line, false, false));
    });
    $.each(allPOs, function(_, po) {
        $('#filter-po').append(new Option(po, po, false, false));
    });

    $('#filter-line, #filter-po').trigger('change');
}

/* ── build header chip ── */
function buildColHeader(lineName, items, colorIdx) {
    var grad    = LINE_COLORS[colorIdx % LINE_COLORS.length];
    var lineWip = 0;
    $.each(items, function(_, it) { lineWip += it.wip; });
    return $('<div class="wip-col-header">').css('background', grad)
        .append($('<span>').text(lineName))
        .append($('<span class="line-wip-total">').text(fmt(lineWip)));
}

/* ── build kolom PO ── */
function buildColBody(items, lineName) {
    var col = $('<div class="wip-col-body">');
    $.each(items, function(_, item) {
        var cls = item.wip > 0 ? 'pos' : (item.wip < 0 ? 'neg' : 'zer');
        var row = $('<div class="wip-po-item">')
            .attr('data-po', item.po)
            .attr('data-line', lineName)
            .attr('data-sa', item.qty_sa)
            .attr('data-out', item.qty_output)
            .attr('data-trf', item.qty_trf)
            .attr('data-wip', item.wip)
            .on('click', function() { openDetail($(this)); })
            .append($('<div class="wip-po-label">').text('PO'))
            .append($('<div class="wip-po-number">').text(item.po))
            .append($('<div class="wip-po-wip-label">').text('WIP'))
            .append($('<div class="wip-po-wip ' + cls + '">').text(fmt(item.wip)))
            .append($('<div class="wip-po-badges">').html(
                '<span class="wip-badge-sa"  title="Saldo Awal">'           + fmt(item.qty_sa)     + '</span>' +
                '<span class="wip-badge-out" title="Output sewing">▲ '    + fmt(item.qty_output) + '</span>' +
                '<span class="wip-badge-trf" title="Transfer packing">▼ ' + fmt(item.qty_trf)    + '</span>'
            ));
        col.append(row);
    });
    return col;
}

/* ── open detail modal ── */
function openDetail(el) {
    var po   = el.data('po');
    var line = el.data('line');
    var wip  = el.data('wip');

    /* isi summary bar dari data yang sudah ada */
    $('#modal-title-po').text('PO ' + po + '  —  ' + line);
    $('#ms-sa').text(fmt(el.data('sa')));
    $('#ms-out').text(fmt(el.data('out')));
    $('#ms-trf').text(fmt(el.data('trf')));
    $('#ms-wip').text(fmt(wip)).css('color', wip < 0 ? '#cc2c2c' : (wip === 0 ? '#bbb' : '#1a7f4b'));

    /* reset table */
    $('#modal-loading').show();
    $('#modal-table-wrap, #modal-empty').hide();
    $('#modal-tbody').empty();
    $('#modalDetailPO').modal('show');

    /* fetch detail */
    $.get('{{ route('wip_packing_line_detail') }}', { po: po, line: line })
        .done(function(rows) {
            $('#modal-loading').hide();
            if (!rows.length) { $('#modal-empty').show(); return; }

            var totSa = 0, totOut = 0, totTrf = 0, totWip = 0;
            $.each(rows, function(_, r) {
                var w = Number(r.selisih);
                totSa  += Number(r.qty_sa);
                totOut += Number(r.qty_output);
                totTrf += Number(r.qty_trf);
                totWip += w;
                $('#modal-tbody').append(
                    '<tr>' +
                    '<td>' + r.buyer   + '</td>' +
                    '<td>' + r.styleno + '</td>' +
                    '<td>' + r.color   + '</td>' +
                    '<td>' + r.size    + '</td>' +
                    '<td class="text-end">' + fmt(r.qty_sa)     + '</td>' +
                    '<td class="text-end">' + fmt(r.qty_output) + '</td>' +
                    '<td class="text-end">' + fmt(r.qty_trf)    + '</td>' +
                    '<td class="text-end fw-bold" style="color:' + (w < 0 ? '#cc2c2c' : (w === 0 ? '#bbb' : '#1a7f4b')) + '">' + fmt(w) + '</td>' +
                    '</tr>'
                );
            });
            $('#ft-sa').text(fmt(totSa));
            $('#ft-out').text(fmt(totOut));
            $('#ft-trf').text(fmt(totTrf));
            $('#ft-wip').text(fmt(totWip)).css('color', totWip < 0 ? '#cc2c2c' : '#1a7f4b');
            $('#modal-table-wrap').show();
        })
        .fail(function(xhr) {
            $('#modal-loading').hide();
            var msg = 'Gagal memuat detail.';
            try {
                var json = JSON.parse(xhr.responseText);
                if (json.error) msg += '<br><small class="text-danger">' + json.error + '</small>';
            } catch(e) {}
            $('#modal-empty').html(msg).show();
            console.error('Detail error:', xhr.status, xhr.responseText);
        });
}

function showSkeleton() {
    var hdr = $('#lines-header').empty(), body = $('#lines-body').empty();
    for (var i = 0; i < 6; i++) {
        hdr.append('<div class="wip-sk-hdr"></div>');
        body.append('<div class="wip-sk-body"><div class="sk-row"></div><div class="sk-row"></div></div>');
    }
}

/* ── stagger timers (dibersihkan saat re-render) ── */
var staggerTimers = [];
function clearStagger() {
    $.each(staggerTimers, function(_, t) { clearTimeout(t); });
    staggerTimers = [];
}

/* ── render berdasarkan filter, kartu muncul bertahap ── */
function renderLines() {
    clearStagger();

    var selLines = $('#filter-line').val() || [];
    var selPOs   = $('#filter-po').val()   || [];

    var hdr  = $('#lines-header').empty();
    var body = $('#lines-body').empty();

    var filteredKeys = selLines.length
        ? allKeys.filter(function(k) { return selLines.indexOf(k) !== -1; })
        : allKeys;

    /* kumpulkan dulu semua kolom yang akan tampil */
    var cols = [];
    var filteredWip = 0;
    $.each(filteredKeys, function(_, lineName) {
        var items = selPOs.length
            ? allLines[lineName].filter(function(it) { return selPOs.indexOf(it.po) !== -1; })
            : allLines[lineName];
        if (items.length === 0) return;
        $.each(items, function(_, it) { filteredWip += it.wip; });
        cols.push({ lineName: lineName, items: items, origIdx: allKeys.indexOf(lineName) });
    });

    $('#total-wip').text(fmt(filteredWip));
    $('#line-count').text(cols.length + ' line');

    if (cols.length === 0) {
        hdr.html('<div class="wip-empty"><div class="wip-empty-icon">🔍</div>Tidak ada data yang sesuai filter.</div>');
        return;
    }

    /* append semua kolom sekaligus (tersembunyi), lalu reveal satu per satu */
    $.each(cols, function(idx, col) {
        var hdrEl  = buildColHeader(col.lineName, col.items, col.origIdx);
        var bodyEl = buildColBody(col.items, col.lineName);
        hdr.append(hdrEl);
        body.append(bodyEl);

        var t = setTimeout(function() {
            hdrEl.addClass('visible');
            bodyEl.addClass('visible');
        }, idx * 120);          /* 120ms jeda antar line */
        staggerTimers.push(t);
    });
}

var CACHE_KEY = 'wip_packing_line_cache';

function saveCache(res) {
    try { localStorage.setItem(CACHE_KEY, JSON.stringify(res)); } catch(e) {}
}

function loadCache() {
    try { return JSON.parse(localStorage.getItem(CACHE_KEY)); } catch(e) { return null; }
}

function sortKeys(lines) {
    return Object.keys(lines).sort(function(a, b) {
        return a.localeCompare(b, undefined, { numeric: true, sensitivity: 'base' });
    });
}

function applyData(res, dataChanged) {
    allLines = res.lines;
    allKeys  = sortKeys(allLines);
    saveCache(res);
    $('#last-update').text(nowStr());

    if (dataChanged) {
        /* data berubah: refresh filter & render ulang */
        populateFilters();
        renderLines();
    }
    /* data sama: tidak re-render, tidak ada flicker */
}

/* ── main fetch ── */
function loadWip() {
    startCountdown();

    var cached = loadCache();
    if (cached) {
        /* render dari cache langsung — tanpa skeleton, tanpa delay */
        allLines = cached.lines;
        allKeys  = sortKeys(allLines);
        $('#last-update').text(nowStr() + ' (cache)');
        populateFilters();
        renderLines();

        /* fetch baru di background */
        $.get('{{ route('wip_packing_line_data') }}')
            .done(function(res) {
                /* hanya re-render kalau data benar-benar berubah */
                var changed = JSON.stringify(res.lines) !== JSON.stringify(cached.lines);
                applyData(res, changed);
                if (!changed) $('#last-update').text(nowStr()); /* update timestamp saja */
            })
            .fail(function() { /* data lama tetap tampil */ });
    } else {
        /* pertama kali: skeleton → fetch → render */
        showSkeleton();
        $.get('{{ route('wip_packing_line_data') }}')
            .done(function(res) { applyData(res, true); })
            .fail(function(xhr) {
                $('#lines-header').empty();
                $('#lines-body').html(
                    '<div class="wip-empty" style="color:#cc2c2c"><div class="wip-empty-icon">⚠️</div>'
                    + 'Gagal memuat data (HTTP ' + xhr.status + ').</div>'
                );
                console.error('WIP error:', xhr.responseText);
            });
    }
}

function exportExcel() {
    Swal.fire({
        title: 'Mohon Tunggu...',
        html: 'Sedang menyiapkan file Excel...',
        allowOutsideClick: false,
        didOpen: function() { Swal.showLoading(); }
    });

    $.ajax({
        type: 'GET',
        url: '{{ route('wip_packing_line_export') }}',
        xhrFields: { responseType: 'blob' },
        success: function(response) {
            Swal.close();
            var today = new Date();
            var d = today.getFullYear() + '-'
                + String(today.getMonth()+1).padStart(2,'0') + '-'
                + String(today.getDate()).padStart(2,'0');
            var blob = new Blob([response]);
            var link = document.createElement('a');
            link.href = window.URL.createObjectURL(blob);
            link.download = 'WIP_Packing_Line_' + d + '.xlsx';
            link.click();
        },
        error: function() {
            Swal.fire({ icon: 'error', title: 'Gagal', text: 'Export gagal, coba lagi.' });
        }
    });
}

$(document).ready(function() {
    /* init Select2 */
    $('#filter-line').select2({
        theme: 'bootstrap4',
        placeholder: 'Semua line',
        allowClear: true,
    });
    $('#filter-po').select2({
        theme: 'bootstrap4',
        placeholder: 'Semua PO',
        allowClear: true,
    });

    /* trigger render saat filter berubah */
    $('#filter-line, #filter-po').on('change', function() { renderLines(); });

    updateClock();
    setInterval(updateClock, 30000);
    loadWip();
});
</script>
@endsection
