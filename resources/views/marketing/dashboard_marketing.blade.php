@extends('layouts.index')

@section('custom-link')
    <style>
        .md-page { padding: 6px 4px 24px; color: #1e293b; font-family: Inter, sans-serif; }

        /* Header + time picker (gaya Grafana) */
        .md-head { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 10px; }
        .md-head h1 { margin: 0; color: #1e293b; font-size: 17px; font-weight: 500; }
        .md-toolbar { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; }
        .md-group { position: relative; display: inline-flex; }
        .md-btn { display: inline-flex; align-items: center; gap: 8px; height: 32px; padding: 0 10px; border: 1px solid #cbd5e1; background: #f4f5f7; color: #1e293b; font-size: 13px; font-weight: 500; white-space: nowrap; }
        .md-btn:hover { background: #e9ecf0; }
        .md-btn:focus { outline: 0; box-shadow: 0 0 0 2px rgba(61, 113, 217, .3); }
        .md-group > .md-btn { border-radius: 0; margin-left: -1px; }
        .md-group > .md-btn:first-child { margin-left: 0; border-radius: 4px 0 0 4px; }
        .md-group > .md-btn:last-child { border-radius: 0 4px 4px 0; }
        .md-group > .md-btn:only-child { border-radius: 4px; }
        .md-btn-icon { width: 32px; justify-content: center; padding: 0; }
        .md-range-label .fa-chevron-down { font-size: 10px; transition: transform .15s; }
        .md-range-label[aria-expanded="true"] .fa-chevron-down { transform: rotate(180deg); }
        .md-btn.is-spinning .fa-sync-alt { animation: md-spin .8s linear infinite; }
        @keyframes md-spin { to { transform: rotate(360deg); } }

        .md-popover { position: absolute; top: calc(100% + 6px); right: 0; z-index: 1050; display: none; border: 1px solid #d8dde4; border-radius: 6px; background: #fff; box-shadow: 0 8px 24px rgba(15, 23, 42, .18); }
        .md-popover.show { display: block; }
        .md-picker { width: 545px; max-width: calc(100vw - 24px); }
        .md-picker-body { display: grid; grid-template-columns: minmax(0, 1fr) 218px; min-height: 380px; }
        .md-picker-abs { display: flex; flex-direction: column; padding: 12px 12px 0; border-right: 1px solid #e5e7eb; }
        .md-picker-abs h4 { margin: 0 0 12px; font-size: 14px; font-weight: 500; }
        .md-picker-abs label { display: block; margin: 0 0 4px; font-size: 12px; font-weight: 500 !important; }
        .md-date-field { display: flex; margin-bottom: 14px; }
        .md-date-field input[type="text"] { flex: 1; min-width: 0; height: 32px; padding: 0 8px; border: 1px solid #cbd5e1; border-radius: 4px 0 0 4px; font-size: 13px; outline: none; }
        .md-date-field input[type="text"]:focus { border-color: #3d71d9; }
        .md-date-field input.is-invalid { border-color: #dc2626; }
        .md-date-field button { width: 32px; height: 32px; margin-left: -1px; border: 1px solid #cbd5e1; border-radius: 0 4px 4px 0; background: #f4f5f7; color: #334155; }
        .md-date-field input[type="datetime-local"] { position: absolute; width: 0; height: 0; padding: 0; border: 0; opacity: 0; pointer-events: none; }
        .md-picker-error { margin: -8px 0 10px; color: #dc2626; font-size: 11px; }
        .md-picker-actions { display: flex; gap: 6px; }
        .md-apply { height: 32px; padding: 0 16px; border: 0; border-radius: 4px; background: #3d71d9; color: #fff; font-size: 13px; font-weight: 500; }
        .md-apply:hover { background: #3262c4; }
        .md-recent { margin-top: auto; padding: 12px 0; }
        .md-recent h5 { margin: 0 0 8px; font-size: 13px; font-weight: 500; }
        .md-recent button { display: block; width: 100%; padding: 6px 2px; border: 0; background: none; color: #1e293b; font-size: 13px; text-align: left; }
        .md-recent button:hover { background: #f1f5f9; }
        .md-recent-empty { color: #94a3b8; font-size: 12px; }
        .md-picker-quick { display: flex; flex-direction: column; min-height: 0; padding: 8px 0 0; }
        .md-quick-search { position: relative; margin: 0 8px 6px; }
        .md-quick-search i { position: absolute; top: 50%; left: 9px; transform: translateY(-50%); color: #64748b; font-size: 12px; }
        .md-quick-search input { width: 100%; height: 32px; padding: 0 8px 0 28px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 13px; outline: none; }
        .md-quick-search input:focus { border-color: #3d71d9; box-shadow: 0 0 0 2px rgba(61, 113, 217, .25); }
        .md-quick-list { flex: 1; max-height: 320px; overflow-y: auto; }
        .md-quick-list button { display: block; width: 100%; padding: 8px 12px; border: 0; background: none; color: #1e293b; font-size: 13px; text-align: left; }
        .md-quick-list button:hover, .md-quick-list button.active { background: #eef2f7; }
        .md-quick-list button.active { font-weight: 600; }
        .md-picker-foot { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 9px 12px; border-top: 1px solid #e5e7eb; font-size: 13px; }
        .md-picker-foot small { margin-left: 6px; color: #64748b; font-size: 11px; }
        .md-picker-foot span.md-utc { padding: 3px 6px; border-radius: 3px; background: #f1f5f9; font-size: 12px; }
        .md-refresh-menu { min-width: 90px; padding: 4px 0; }
        .md-refresh-menu button { display: block; width: 100%; padding: 6px 12px; border: 0; background: none; font-size: 13px; text-align: left; }
        .md-refresh-menu button:hover, .md-refresh-menu button.active { background: #eef2f7; }
        @media (max-width: 575.98px) {
            .md-picker-body { grid-template-columns: minmax(0, 1fr); }
            .md-picker-abs { border-right: 0; border-bottom: 1px solid #e5e7eb; }
            .md-range-text { max-width: 150px; overflow: hidden; text-overflow: ellipsis; }
        }
        .md-card { min-width: 0; padding: 12px 14px; border: 1px solid #e2e8f0; border-radius: 10px; background: #fff; box-shadow: 0 1px 3px rgba(15, 23, 42, .04); }
        .md-time-chart { position: relative; cursor: crosshair; }
        /* Pegangan di baris tanggal (sumbu X): kursor tangan, seret kiri/kanan untuk menggeser rentang. */
        .md-axis-drag { position: absolute; z-index: 5; cursor: grab; touch-action: none; }
        .md-axis-drag:hover { background: rgba(40, 74, 99, .06); }
        .md-axis-drag.is-dragging { cursor: grabbing; background: rgba(40, 74, 99, .1); }
        .md-axis-preview { position: absolute; z-index: 6; bottom: 100%; padding: 3px 8px; border-radius: 4px; background: #284a63; color: #fff; font-size: 11px; white-space: nowrap; transform: translate(-50%, -4px); pointer-events: none; }
        .md-card-title { display: flex; align-items: center; gap: 8px; margin: 0 0 6px; color: #1e293b; font-size: 14px; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .md-card-title i { flex: 0 0 auto; color: #64748b; font-size: 12px; cursor: help; }

        .md-kpis { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)) minmax(0, 2fr); gap: 10px; margin-bottom: 10px; }
        .md-kpi strong { display: block; padding: 6px 0 2px; color: #1e293b; font-size: 38px; font-weight: 500; line-height: 1.1; text-align: center; }
        .md-kpi-hero { display: flex; flex-direction: column; padding: 0; overflow: hidden; }
        .md-kpi-hero .md-card-title { padding: 12px 14px 0; }
        .md-kpi-hero strong { display: flex; flex: 1; align-items: center; justify-content: center; margin-top: 8px; padding: 6px 10px; background: linear-gradient(90deg, #284a63, #2c6a7a); color: #fff; font-size: 40px; font-weight: 500; }

        .md-row { display: grid; gap: 10px; margin-bottom: 10px; }
        .md-row-charts { grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); }
        .md-row-bottom { grid-template-columns: minmax(0, 1fr) minmax(0, 1.55fr); }
        .md-stack { display: grid; gap: 10px; min-width: 0; }

        .md-table-wrap { max-height: 470px; overflow: auto; }
        .md-table { width: 100%; margin: 0; border-collapse: collapse; font-size: 14px; }
        .md-table thead th { position: sticky; top: 0; z-index: 1; padding: 10px 8px; border-bottom: 1px solid #e2e8f0; background: #fff; color: #475569; font-size: 12px; font-weight: 500; text-align: left; white-space: nowrap; }
        .md-table tbody td { padding: 9px 8px; border-bottom: 1px solid #edf1f5; font-weight: 500; white-space: nowrap; }
        .md-table tbody tr:hover { background: #f8fafc; }
        .md-table .text-right { text-align: right; }
        .md-empty { padding: 30px 10px; color: #94a3b8; text-align: center; }

        @media (max-width: 1199.98px) {
            .md-kpis { grid-template-columns: repeat(3, minmax(0, 1fr)); }
            .md-kpi-hero { grid-column: span 2; }
            .md-row-charts, .md-row-bottom { grid-template-columns: minmax(0, 1fr); }
        }
        @media (max-width: 575.98px) {
            .md-kpis { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .md-kpi strong { font-size: 28px; }
        }
    </style>
@endsection

@section('content')
<div class="md-page">
    <div class="md-head">
        <h1>Dashboard Marketing</h1>
        <div class="md-toolbar">
            <div class="md-group" id="md-time-group">
                <button type="button" class="md-btn md-btn-icon" id="md-shift-back" title="Mundurkan rentang waktu"><i class="fas fa-angle-double-left"></i></button>
                <button type="button" class="md-btn md-range-label" id="md-range-toggle" aria-haspopup="dialog" aria-expanded="false">
                    <i class="far fa-clock"></i> <span class="md-range-text" id="md-range-text">Last 1 year</span> <i class="fas fa-chevron-down"></i>
                </button>
                <button type="button" class="md-btn md-btn-icon" id="md-shift-forward" title="Majukan rentang waktu"><i class="fas fa-angle-double-right"></i></button>
                <button type="button" class="md-btn md-btn-icon" id="md-zoom-out" title="Perlebar rentang waktu (zoom out)"><i class="fas fa-search-minus"></i></button>

                <div class="md-popover md-picker" id="md-picker" role="dialog" aria-label="Pilih rentang waktu">
                    <div class="md-picker-body">
                        <div class="md-picker-abs">
                            <h4>Absolute time range</h4>
                            <label for="md-from">From</label>
                            <div class="md-date-field">
                                <input type="text" id="md-from" placeholder="YYYY-MM-DD HH:mm:ss" autocomplete="off">
                                <button type="button" class="md-calendar" data-target="#md-from" title="Pilih tanggal"><i class="far fa-calendar-alt"></i></button>
                                <input type="datetime-local" step="1" tabindex="-1" aria-hidden="true">
                            </div>
                            <label for="md-to">To</label>
                            <div class="md-date-field">
                                <input type="text" id="md-to" placeholder="YYYY-MM-DD HH:mm:ss" autocomplete="off">
                                <button type="button" class="md-calendar" data-target="#md-to" title="Pilih tanggal"><i class="far fa-calendar-alt"></i></button>
                                <input type="datetime-local" step="1" tabindex="-1" aria-hidden="true">
                            </div>
                            <div class="md-picker-error" id="md-picker-error" hidden></div>
                            <div class="md-picker-actions">
                                <button type="button" class="md-btn md-btn-icon" id="md-copy-range" title="Copy rentang waktu"><i class="far fa-copy"></i></button>
                                <button type="button" class="md-btn md-btn-icon" id="md-paste-range" title="Paste rentang waktu"><i class="far fa-clipboard"></i></button>
                                <button type="button" class="md-apply" id="md-apply-range">Apply time range</button>
                            </div>
                            <div class="md-recent">
                                <h5>Recently used absolute ranges</h5>
                                <div id="md-recent-list"></div>
                            </div>
                        </div>
                        <div class="md-picker-quick">
                            <div class="md-quick-search">
                                <i class="fas fa-search"></i>
                                <input type="search" id="md-quick-search" placeholder="Search quick ranges" autocomplete="off">
                            </div>
                            <div class="md-quick-list" id="md-quick-list"></div>
                        </div>
                    </div>
                    <div class="md-picker-foot">
                        <span>Browser Time <small id="md-tz-name"></small></span>
                        <span class="md-utc" id="md-tz-offset"></span>
                    </div>
                </div>
            </div>

            <div class="md-group">
                <button type="button" class="md-btn" id="md-refresh"><i class="fas fa-sync-alt"></i> <span id="md-refresh-text">Refresh</span></button>
                <button type="button" class="md-btn md-btn-icon" id="md-refresh-toggle" title="Auto refresh" aria-haspopup="menu" aria-expanded="false"><i class="fas fa-chevron-down"></i></button>
                <div class="md-popover md-refresh-menu" id="md-refresh-menu" role="menu"></div>
            </div>
        </div>
    </div>

    <div class="md-kpis">
        <div class="md-card md-kpi">
            <h3 class="md-card-title">Total Buyers <i class="far fa-question-circle" title="Jumlah buyer yang punya Costing dalam rentang waktu terpilih"></i></h3>
            <strong id="kpi-total-buyers">-</strong>
        </div>
        <div class="md-card md-kpi">
            <h3 class="md-card-title">Costings Created <i class="far fa-question-circle" title="Jumlah Costing dibuat dalam rentang waktu terpilih"></i></h3>
            <strong id="kpi-costings">-</strong>
        </div>
        <div class="md-card md-kpi">
            <h3 class="md-card-title">Incoming SO <i class="far fa-question-circle" title="Jumlah Sales Order (aktif) dalam rentang waktu terpilih"></i></h3>
            <strong id="kpi-incoming-so">-</strong>
        </div>
        <div class="md-card md-kpi">
            <h3 class="md-card-title">Due in 14 Days <i class="far fa-question-circle" title="Qty order (pcs) dengan delivery date hari ini s/d 14 hari ke depan"></i></h3>
            <strong id="kpi-due">-</strong>
        </div>
        <div class="md-card md-kpi md-kpi-hero">
            <h3 class="md-card-title">Total Order Qty <i class="far fa-question-circle" title="Total qty Sales Order (pcs) dalam rentang waktu terpilih"></i></h3>
            <strong id="kpi-total-qty">-</strong>
        </div>
    </div>

    <div class="md-row md-row-charts">
        <div class="md-card">
            <h3 class="md-card-title">Costings vs Incoming SOs per <span id="md-bucket-label">Month</span> <i class="far fa-question-circle" title="Jumlah Costing (tanggal costing) dan SO (tanggal SO) per bulan. Drag di grafik untuk pilih rentang tanggal, Shift + scroll untuk geser, double-click untuk zoom out (filter berlaku ke seluruh dashboard)."></i></h3>
            <div id="chart-costing-so" class="md-time-chart"></div>
        </div>
        <div class="md-card">
            <h3 class="md-card-title">Top 10 Buyers by SO Qty <i class="far fa-question-circle" title="Buyer dengan qty Sales Order terbanyak dalam rentang waktu terpilih"></i></h3>
            <div id="chart-top-buyers"></div>
        </div>
    </div>

    <div class="md-row md-row-bottom">
        <div class="md-stack">
            <div class="md-card">
                <h3 class="md-card-title">Order Value (USD) <i class="far fa-question-circle" title="Total qty × price Sales Order bercurrency USD per bulan. Drag di grafik untuk pilih rentang tanggal, Shift + scroll untuk geser, double-click untuk zoom out (filter berlaku ke seluruh dashboard)."></i></h3>
                <div id="chart-value-usd" class="md-time-chart"></div>
            </div>
            <div class="md-card">
                <h3 class="md-card-title">Order Value (IDR, billion) <i class="far fa-question-circle" title="Total qty × price Sales Order bercurrency IDR per bulan, dalam miliar rupiah. Drag di grafik untuk pilih rentang tanggal, Shift + scroll untuk geser, double-click untuk zoom out (filter berlaku ke seluruh dashboard)."></i></h3>
                <div id="chart-value-idr" class="md-time-chart"></div>
            </div>
        </div>
        <div class="md-card">
            <h3 class="md-card-title">Latest Sales Orders <i class="far fa-question-circle" title="100 Sales Order terbaru dalam rentang waktu terpilih"></i></h3>
            <div class="md-table-wrap">
                <table class="md-table">
                    <thead>
                        <tr>
                            <th>SO No</th>
                            <th>Buyer</th>
                            <th>Style</th>
                            <th class="text-right">Qty</th>
                            <th>Curr</th>
                            <th class="text-right">Order Value</th>
                            <th>Delivery</th>
                            <th>No Costing</th>
                        </tr>
                    </thead>
                    <tbody id="md-recent-so"><tr><td colspan="8" class="md-empty"><i class="fas fa-spinner fa-spin"></i></td></tr></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('custom-script')
    <script src="{{ asset('plugins/apexcharts/apexcharts.min.js') }}"></script>
    <script>
        $(function () {
            const DATA_URL = "{{ route('dashboard-marketing-data') }}";
            const NAVY = '#284a63';
            const RECENT_KEY = 'md-recent-ranges';
            const escape = value => $('<div>').text(value ?? '').html();
            const number = (value, digits = 0) => Number(value || 0).toLocaleString('en-US', { maximumFractionDigits: digits });
            const pad = n => String(n).padStart(2, '0');
            const fmt = d => `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())} ${pad(d.getHours())}:${pad(d.getMinutes())}:${pad(d.getSeconds())}`;

            const QUICK_RANGES = [
                ['now-5m', 'now', 'Last 5 minutes'], ['now-15m', 'now', 'Last 15 minutes'], ['now-30m', 'now', 'Last 30 minutes'],
                ['now-1h', 'now', 'Last 1 hour'], ['now-3h', 'now', 'Last 3 hours'], ['now-6h', 'now', 'Last 6 hours'],
                ['now-12h', 'now', 'Last 12 hours'], ['now-24h', 'now', 'Last 24 hours'], ['now-2d', 'now', 'Last 2 days'],
                ['now-7d', 'now', 'Last 7 days'], ['now-30d', 'now', 'Last 30 days'], ['now-90d', 'now', 'Last 90 days'],
                ['now-6M', 'now', 'Last 6 months'], ['now-1y', 'now', 'Last 1 year'], ['now-2y', 'now', 'Last 2 years'],
                ['now-5y', 'now', 'Last 5 years'], ['now-1d/d', 'now-1d/d', 'Yesterday'], ['now/d', 'now/d', 'Today'],
                ['now/d', 'now', 'Today so far'], ['now/w', 'now/w', 'This week'], ['now/M', 'now/M', 'This month'],
                ['now/M', 'now', 'This month so far'], ['now/y', 'now/y', 'This year'], ['now/y', 'now', 'This year so far'],
                ['now-1M/M', 'now-1M/M', 'Previous month'], ['now-1y/y', 'now-1y/y', 'Previous year']
            ].map(([from, to, label]) => ({ from, to, label }));
            const REFRESH_OPTIONS = [['', 'Off'], ['5s', '5s'], ['10s', '10s'], ['30s', '30s'], ['1m', '1m'], ['5m', '5m'], ['15m', '15m'], ['30m', '30m'], ['1h', '1h'], ['2h', '2h'], ['1d', '1d']];
            const UNIT_MS = { s: 1e3, m: 6e4, h: 36e5, d: 864e5, w: 6048e5 };

            /* ---------- waktu: "now-1d/d" (relatif, gaya Grafana) atau "YYYY-MM-DD HH:mm:ss" ---------- */
            function startOf(d, unit) {
                const r = new Date(d);
                if (unit === 'y') r.setMonth(0, 1);
                if (unit === 'y' || unit === 'M') r.setDate(1);
                if (unit === 'w') r.setDate(r.getDate() - ((r.getDay() + 6) % 7));
                if (['y', 'M', 'w', 'd'].includes(unit)) r.setHours(0, 0, 0, 0);
                if (unit === 'h') r.setMinutes(0, 0, 0);
                if (unit === 'm') r.setSeconds(0, 0);
                return r;
            }
            function endOf(d, unit) {
                const r = startOf(d, unit);
                if (unit === 'y') r.setFullYear(r.getFullYear() + 1);
                else if (unit === 'M') r.setMonth(r.getMonth() + 1);
                else if (unit === 'w') r.setDate(r.getDate() + 7);
                else if (unit === 'd') r.setDate(r.getDate() + 1);
                else r.setTime(r.getTime() + UNIT_MS[unit]);
                return new Date(r.getTime() - 1000);
            }
            function addUnit(d, amount, unit) {
                const r = new Date(d);
                if (unit === 'y') r.setFullYear(r.getFullYear() + amount);
                else if (unit === 'M') r.setMonth(r.getMonth() + amount);
                else if (unit === 'd' || unit === 'w') r.setDate(r.getDate() + amount * (unit === 'w' ? 7 : 1));
                else r.setTime(r.getTime() + amount * UNIT_MS[unit]);
                return r;
            }
            // isEnd: pembulatan "/d" pada To = akhir hari, pada From = awal hari.
            function resolve(spec, isEnd) {
                spec = String(spec || '').trim();
                const rel = /^now(?:([+-])(\d+)([smhdwMy]))?(?:\/([smhdwMy]))?$/.exec(spec);
                if (rel) {
                    let d = new Date();
                    if (rel[1]) d = addUnit(d, (rel[1] === '-' ? -1 : 1) * Number(rel[2]), rel[3]);
                    if (rel[4]) d = isEnd ? endOf(d, rel[4]) : startOf(d, rel[4]);
                    return d;
                }
                const abs = /^(\d{4})-(\d{2})-(\d{2})(?:[ T](\d{2}):(\d{2})(?::(\d{2}))?)?$/.exec(spec);
                if (!abs) return null;
                const d = new Date(+abs[1], +abs[2] - 1, +abs[3], +(abs[4] || (isEnd ? 23 : 0)), +(abs[5] || (isEnd ? 59 : 0)), +(abs[6] || (isEnd ? 59 : 0)));
                return isNaN(d.getTime()) || d.getMonth() !== +abs[2] - 1 ? null : d;
            }
            const isRelative = spec => /^now/.test(String(spec));
            const quickOf = r => QUICK_RANGES.find(q => q.from === r.from && q.to === r.to);
            const rangeLabel = r => (quickOf(r) || {}).label
                || `${isRelative(r.from) ? r.from : fmt(resolve(r.from))} to ${isRelative(r.to) ? r.to : fmt(resolve(r.to, true))}`;

            /* ---------- state + URL ---------- */
            const params = new URLSearchParams(location.search);
            let range = { from: params.get('from') || 'now-1y', to: params.get('to') || 'now' };
            if (!resolve(range.from) || !resolve(range.to, true)) range = { from: 'now-1y', to: 'now' };
            let refreshEvery = REFRESH_OPTIONS.some(([value]) => value === params.get('refresh')) ? params.get('refresh') : '';
            let refreshTimer = null;
            let loading = null;
            const charts = {};

            function syncUrl() {
                const query = new URLSearchParams(location.search);
                query.set('from', range.from);
                query.set('to', range.to);
                refreshEvery ? query.set('refresh', refreshEvery) : query.delete('refresh');
                history.replaceState(null, '', `${location.pathname}?${query.toString()}`);
            }

            function setRange(next, remember) {
                range = next;
                if (remember) rememberRange(next);
                $('#md-range-text').text(rangeLabel(range));
                syncUrl();
                load();
            }

            /* ---------- data & grafik ---------- */
            const base = {
                chart: { toolbar: { show: false }, fontFamily: 'Inter, sans-serif', zoom: { enabled: false }, animations: { enabled: false } },
                dataLabels: { enabled: false },
                grid: { borderColor: '#e2e8f0' },
                legend: { position: 'bottom', horizontalAlign: 'left', fontSize: '12px' },
                tooltip: { theme: 'light' },
                noData: { text: 'No data', style: { fontSize: '16px', color: '#64748b' } }
            };
            const hasData = values => values.some(v => Number(v) !== 0);

            /* ---------- grafik waktu: drag = pilih rentang, Shift+scroll = geser, double-click = zoom out ---------- */
            // Periode dari server: "YYYY-MM-DD" (harian) atau "YYYY-MM" (bulanan) -> timestamp awal periode (waktu lokal).
            let bucket = 'month';
            const periodTime = period => {
                const [y, m, d] = String(period).split('-').map(Number);
                return new Date(y, m - 1, d || 1).getTime();
            };
            const toPoints = (periods, values) => periods.map((period, i) => [periodTime(period), Number(values[i] || 0)]);
            function selectRange(min, max) {
                const unit = bucket === 'day' ? 'd' : 'M';
                const from = startOf(new Date(min), unit), to = endOf(new Date(max), unit);
                if (to > from) setRange({ from: fmt(from), to: fmt(to) }, true);
            }
            const timeChart = options => $.extend(true, {
                chart: {
                    zoom: { enabled: true, type: 'x', autoScaleYaxis: false },
                    toolbar: { show: false, autoSelected: 'zoom' },
                    // Rentang hasil drag dipakai sebagai filter seluruh dashboard; grafik digambar ulang oleh load().
                    events: { zoomed: (ctx, { xaxis }) => xaxis && xaxis.min != null && selectRange(xaxis.min, xaxis.max) }
                },
                xaxis: {
                    // Satu tick per bulan (maks. 12): tanpa ini ApexCharts membuat tick harian di rentang pendek
                    // sehingga labelnya "Feb '25" berulang. Label dibulatkan ke awal bulan terdekat.
                    type: 'datetime',
                    tickAmount: Math.max(1, Math.min(periodCount - 1, 12)),
                    labels: {
                        datetimeUTC: false, rotate: 0, hideOverlappingLabels: true, style: { fontSize: '11px' },
                        formatter: (value, ts) => axisLabel(ts ?? value)
                    },
                    axisBorder: { show: false }, tooltip: { enabled: false }
                },
                // Tooltip seperti Grafana: nilai per bulan (atau per hari), tanpa jam.
                tooltip: { x: { formatter: ts => periodLabel(ts) } }
            }, options);
            const periodLabel = ts => new Date(ts).toLocaleDateString('en-GB', bucket === 'day'
                ? { day: '2-digit', month: 'short', year: 'numeric' }
                : { month: 'long', year: 'numeric' });
            let periodCount = 2;
            const axisLabel = ts => {
                const d = new Date(Number(ts));
                if (bucket === 'day') return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short' });
                if (d.getDate() > 15) d.setMonth(d.getMonth() + 1, 1); // bulatkan ke awal bulan terdekat
                return d.toLocaleDateString('en-GB', { month: 'short' }) + " '" + String(d.getFullYear()).slice(2);
            };

            let panTimer = null, panSteps = 0;
            $('.md-time-chart').on('wheel', function (event) {
                const e = event.originalEvent;
                const horizontal = Math.abs(e.deltaX) > Math.abs(e.deltaY);
                if (!e.shiftKey && !horizontal) return; // scroll biasa tetap menggulir halaman
                event.preventDefault();
                const delta = horizontal ? e.deltaX : e.deltaY;
                if (!delta) return;
                panSteps += delta > 0 ? 1 : -1;
                clearTimeout(panTimer);
                // Kumpulkan beberapa "klik" scroll dulu supaya tidak memuat data berulang-ulang.
                panTimer = setTimeout(function () {
                    const from = resolve(range.from), to = resolve(range.to, true), step = (to - from + 1000) / 4;
                    const move = panSteps * step;
                    panSteps = 0;
                    setRange({ from: fmt(new Date(from.getTime() + move)), to: fmt(new Date(to.getTime() + move)) }, false);
                }, 250);
            });
            $('.md-time-chart').on('dblclick', () => zoomOut());

            function draw(id, options) {
                if (charts[id]) charts[id].destroy();
                charts[id] = new ApexCharts(document.getElementById(id), $.extend(true, {}, base, options));
                const rendered = charts[id].render();
                if ($('#' + id).hasClass('md-time-chart')) rendered.then(() => placeAxisHandle(id));
            }

            // Pegangan transparan tepat di atas label tanggal (di bawah area plot) setiap grafik waktu.
            function placeAxisHandle(id) {
                const g = charts[id] && charts[id].w.globals;
                const $box = $('#' + id);
                $box.children('.md-axis-drag').remove();
                if (!g || !g.gridWidth) return;
                $('<div class="md-axis-drag" title="Seret untuk menggeser tanggal"></div>').css({
                    left: g.translateX, top: g.translateY + g.gridHeight, width: g.gridWidth, height: 28
                }).appendTo($box);
            }

            let axisDrag = null;
            $(document).on('pointerdown', '.md-axis-drag', function (event) {
                event.preventDefault();
                const from = resolve(range.from), to = resolve(range.to, true);
                axisDrag = { $el: $(this), startX: event.clientX, width: $(this).width(), from, to, span: to - from + 1000, move: 0 };
                axisDrag.$el.addClass('is-dragging').append('<div class="md-axis-preview"></div>');
                this.setPointerCapture && this.setPointerCapture(event.pointerId);
            });
            $(document).on('pointermove', function (event) {
                if (!axisDrag) return;
                const dx = event.clientX - axisDrag.startX;
                // Seret ke kanan = mundur ke tanggal sebelumnya (seperti menggeser kertas), sebanding dengan lebar grafik.
                axisDrag.move = -dx / axisDrag.width * axisDrag.span;
                const preview = { from: fmt(new Date(axisDrag.from.getTime() + axisDrag.move)), to: fmt(new Date(axisDrag.to.getTime() + axisDrag.move)) };
                axisDrag.$el.find('.md-axis-preview')
                    .css('left', Math.min(Math.max(event.clientX - axisDrag.$el.offset().left, 0), axisDrag.width))
                    .text(`${preview.from.slice(0, 10)} → ${preview.to.slice(0, 10)}`);
            });
            $(document).on('pointerup pointercancel', function () {
                if (!axisDrag) return;
                const { $el, from, to, move } = axisDrag;
                axisDrag = null;
                $el.removeClass('is-dragging').find('.md-axis-preview').remove();
                if (Math.abs(move) < 1000) return; // klik tanpa seret
                setRange({ from: fmt(new Date(from.getTime() + move)), to: fmt(new Date(to.getTime() + move)) }, false);
            });
            $(window).on('resize', function () {
                Object.keys(charts).forEach(id => $('#' + id).hasClass('md-time-chart') && setTimeout(() => placeAxisHandle(id), 300));
            });

            function load() {
                const from = resolve(range.from), to = resolve(range.to, true);
                if (loading) loading.abort();
                $('#md-refresh').addClass('is-spinning');
                loading = $.getJSON(DATA_URL, { from: fmt(from), to: fmt(to) })
                    .done(render)
                    .fail(function (xhr, status) {
                        if (status === 'abort') return;
                        $('#md-recent-so').html('<tr><td colspan="8" class="md-empty text-danger">Gagal memuat data dashboard.</td></tr>');
                    })
                    .always(function (xhr, status) {
                        if (status !== 'abort') $('#md-refresh').removeClass('is-spinning');
                    });
            }

            function render(res) {
                $('#kpi-total-buyers').text(number(res.kpi.total_buyers));
                $('#kpi-costings').text(number(res.kpi.costings));
                $('#kpi-incoming-so').text(number(res.kpi.incoming_so));
                $('#kpi-due').text(number(res.kpi.due_14_days));
                $('#kpi-total-qty').text(number(res.kpi.total_qty));
                const periods = res.periods;
                bucket = res.range.bucket === 'day' ? 'day' : 'month';
                periodCount = periods.length;
                $('#md-bucket-label').text(bucket === 'day' ? 'Day' : 'Month');
                const series = (name, data) => hasData(data) ? [{ name, data: toPoints(periods, data) }] : [];

                draw('chart-costing-so', timeChart({
                    chart: { type: 'area', height: 220 },
                    series: hasData(res.costing_vs_so.costings) || hasData(res.costing_vs_so.so)
                        ? [{ name: 'Costings', data: toPoints(periods, res.costing_vs_so.costings) }, { name: 'Incoming SOs', data: toPoints(periods, res.costing_vs_so.so) }] : [],
                    colors: [NAVY, '#b6c8d9'],
                    stroke: { curve: 'straight', width: [2, 2], dashArray: [0, 6] },
                    fill: { type: 'solid', opacity: [0.45, 0.12] },
                    markers: { size: 3, strokeWidth: 0 },
                    yaxis: { labels: { formatter: v => number(v) } }
                }));
                draw('chart-top-buyers', {
                    chart: { type: 'bar', height: 230 },
                    series: res.top_buyers.length ? [{ name: 'Qty', data: res.top_buyers.map(row => Number(row.qty)) }] : [],
                    colors: [NAVY],
                    plotOptions: { bar: { horizontal: true, barHeight: '65%' } },
                    legend: { show: true, position: 'right', horizontalAlign: 'center', showForSingleSeries: true },
                    xaxis: { categories: res.top_buyers.map(row => row.buyer), tickAmount: 2, labels: { formatter: v => number(v) } },
                    yaxis: { labels: { maxWidth: 220, style: { fontSize: '11px' } } },
                    tooltip: { y: { formatter: v => number(v) + ' pcs' } }
                });
                draw('chart-value-usd', timeChart({
                    chart: { type: 'area', height: 200 },
                    series: series('Order Value (USD)', res.order_value.usd),
                    colors: ['#94a3b8'],
                    stroke: { curve: 'straight', width: 2, dashArray: 2 },
                    fill: { type: 'gradient', gradient: { shadeIntensity: 0, opacityFrom: 0.4, opacityTo: 0.02 } },
                    markers: { size: 3, strokeWidth: 0, colors: ['#64748b'] },
                    yaxis: { labels: { formatter: v => number(v) } },
                    tooltip: { y: { formatter: v => 'USD ' + number(v, 2) } }
                }));
                draw('chart-value-idr', timeChart({
                    chart: { type: 'area', height: 200 },
                    series: series('Order Value (IDR, billion)', res.order_value.idr_billion),
                    colors: ['#1d6fd8'],
                    stroke: { curve: 'straight', width: 2, dashArray: 6 },
                    fill: { type: 'gradient', gradient: { shadeIntensity: 0, opacityFrom: 0.45, opacityTo: 0.03 } },
                    markers: { size: 3, strokeWidth: 0 },
                    yaxis: { labels: { formatter: v => number(v, 1) } },
                    tooltip: { y: { formatter: v => 'IDR ' + number(v, 2) + ' miliar' } }
                }));

                $('#md-recent-so').html(res.recent_so.length ? res.recent_so.map(row => `
                    <tr>
                        <td>${escape(row.so_no)}</td>
                        <td>${escape(row.buyer || '-')}</td>
                        <td>${escape(row.style || '-')}</td>
                        <td class="text-right">${number(row.qty)}</td>
                        <td>${escape(row.curr || '-')}</td>
                        <td class="text-right">${number(row.value, 2)}</td>
                        <td>${escape(row.deldate || '-')}</td>
                        <td>${escape(row.cost_no || '-')}</td>
                    </tr>`).join('') : '<tr><td colspan="8" class="md-empty">No data</td></tr>');
            }

            /* ---------- popover ---------- */
            function closePopovers(except) {
                $('.md-popover').not(except || null).removeClass('show');
                if (except !== '#md-picker') $('#md-range-toggle').attr('aria-expanded', 'false');
                if (except !== '#md-refresh-menu') $('#md-refresh-toggle').attr('aria-expanded', 'false');
            }
            $(document).on('mousedown', function (event) {
                if (!$(event.target).closest('.md-group').length) closePopovers();
            });
            $(document).on('keydown', function (event) {
                if (event.key === 'Escape') closePopovers();
            });

            /* ---------- time picker ---------- */
            function openPicker() {
                closePopovers('#md-picker');
                $('#md-from').val(isRelative(range.from) ? range.from : fmt(resolve(range.from))).removeClass('is-invalid');
                $('#md-to').val(isRelative(range.to) ? range.to : fmt(resolve(range.to, true))).removeClass('is-invalid');
                pickerError('');
                $('#md-quick-search').val('');
                renderQuickList();
                renderRecent();
                $('#md-picker').addClass('show');
                $('#md-range-toggle').attr('aria-expanded', 'true');
                $('#md-quick-search').trigger('focus');
            }
            $('#md-range-toggle').on('click', function () {
                $('#md-picker').hasClass('show') ? closePopovers() : openPicker();
            });

            function renderQuickList() {
                const keyword = $('#md-quick-search').val().trim().toLowerCase();
                const current = quickOf(range);
                const items = QUICK_RANGES.filter(q => !keyword || q.label.toLowerCase().includes(keyword));
                $('#md-quick-list').html(items.length
                    ? items.map(q => `<button type="button" data-from="${q.from}" data-to="${q.to}" class="${q === current ? 'active' : ''}">${q.label}</button>`).join('')
                    : '<div class="px-3 py-2 text-muted" style="font-size: 12px;">Tidak ada yang cocok.</div>');
            }
            $('#md-quick-search').on('input', renderQuickList);
            $('#md-quick-list').on('click', 'button', function () {
                setRange({ from: String($(this).data('from')), to: String($(this).data('to')) }, false);
                closePopovers();
            });

            function readRecent() {
                try { return JSON.parse(localStorage.getItem(RECENT_KEY)) || []; } catch (e) { return []; }
            }
            function rememberRange(next) {
                if (isRelative(next.from) && isRelative(next.to)) return;
                const list = [next].concat(readRecent().filter(item => item.from !== next.from || item.to !== next.to)).slice(0, 4);
                try { localStorage.setItem(RECENT_KEY, JSON.stringify(list)); } catch (e) { /* storage tidak tersedia */ }
            }
            function renderRecent() {
                const list = readRecent().filter(item => resolve(item.from) && resolve(item.to, true));
                $('#md-recent-list').html(list.length
                    ? list.map(item => `<button type="button" data-from="${escape(item.from)}" data-to="${escape(item.to)}">${escape(rangeLabel(item))}</button>`).join('')
                    : '<div class="md-recent-empty">Belum ada.</div>');
            }
            $('#md-recent-list').on('click', 'button', function () {
                setRange({ from: String($(this).data('from')), to: String($(this).data('to')) }, true);
                closePopovers();
            });

            function pickerError(message) {
                $('#md-picker-error').prop('hidden', !message).text(message || '');
            }
            function applyPicker() {
                const fromText = $('#md-from').val().trim(), toText = $('#md-to').val().trim();
                const from = resolve(fromText), to = resolve(toText, true);
                $('#md-from').toggleClass('is-invalid', !from);
                $('#md-to').toggleClass('is-invalid', !to);
                if (!from || !to) return pickerError('Format tanggal: YYYY-MM-DD HH:mm:ss atau now-1d.');
                if (from > to) return pickerError('From harus sebelum To.');
                pickerError('');
                setRange({ from: fromText, to: toText }, true);
                closePopovers();
            }
            $('#md-apply-range').on('click', applyPicker);
            $('#md-from, #md-to').on('keydown', function (event) {
                if (event.key === 'Enter') { event.preventDefault(); applyPicker(); }
            });

            // Ikon kalender membuka date-time picker bawaan browser.
            $('.md-calendar').on('click', function () {
                const $text = $($(this).data('target'));
                const native = $(this).siblings('input[type="datetime-local"]')[0];
                const current = resolve($text.val(), $text.is('#md-to')) || new Date();
                native.value = fmt(current).replace(' ', 'T');
                if (typeof native.showPicker === 'function') native.showPicker(); else $text.trigger('focus');
            });
            $('.md-date-field input[type="datetime-local"]').on('change', function () {
                if (!this.value) return;
                $(this).siblings('input[type="text"]').val(this.value.replace('T', ' ') + (this.value.length === 16 ? ':00' : '')).removeClass('is-invalid');
            });

            $('#md-copy-range').on('click', function () {
                const text = JSON.stringify({ from: $('#md-from').val().trim(), to: $('#md-to').val().trim() });
                if (!navigator.clipboard) return pickerError('Clipboard tidak tersedia di browser ini.');
                navigator.clipboard.writeText(text).then(() => pickerError(''), () => pickerError('Gagal copy ke clipboard.'));
            });
            $('#md-paste-range').on('click', function () {
                if (!navigator.clipboard || !navigator.clipboard.readText) return pickerError('Clipboard tidak tersedia di browser ini.');
                navigator.clipboard.readText().then(function (text) {
                    let pasted = null;
                    try {
                        pasted = JSON.parse(text);
                    } catch (e) {
                        const parts = String(text).split(/\s+to\s+/i);
                        if (parts.length === 2) pasted = { from: parts[0].trim(), to: parts[1].trim() };
                    }
                    if (!pasted || !resolve(pasted.from) || !resolve(pasted.to, true)) return pickerError('Isi clipboard bukan rentang waktu.');
                    $('#md-from').val(pasted.from);
                    $('#md-to').val(pasted.to);
                    pickerError('');
                }, () => pickerError('Izin membaca clipboard ditolak.'));
            });

            // « » geser sejauh panjang rentang; zoom out = rentang 2x dengan titik tengah tetap.
            function shift(direction) {
                // +1 detik: rentang berdampingan, tidak tumpang tindih (To = detik terakhir).
                const from = resolve(range.from), to = resolve(range.to, true), span = to - from + 1000;
                setRange({ from: fmt(new Date(from.getTime() + direction * span)), to: fmt(new Date(to.getTime() + direction * span)) }, false);
            }
            $('#md-shift-back').on('click', () => shift(-1));
            $('#md-shift-forward').on('click', () => shift(1));
            function zoomOut() {
                const from = resolve(range.from), to = resolve(range.to, true), half = (to - from) / 2;
                setRange({ from: fmt(new Date(from.getTime() - half)), to: fmt(new Date(to.getTime() + half)) }, false);
            }
            $('#md-zoom-out').on('click', zoomOut);

            /* ---------- refresh ---------- */
            function intervalMs(value) {
                const m = /^(\d+)([smhd])$/.exec(value || '');
                return m ? Number(m[1]) * UNIT_MS[m[2]] : 0;
            }
            function applyRefresh() {
                clearInterval(refreshTimer);
                const ms = intervalMs(refreshEvery);
                if (ms) refreshTimer = setInterval(load, ms);
                $('#md-refresh-text').text(refreshEvery ? `Refresh ${refreshEvery}` : 'Refresh');
                $('#md-refresh-menu').html(REFRESH_OPTIONS.map(([value, label]) => `<button type="button" role="menuitem" data-value="${value}" class="${value === refreshEvery ? 'active' : ''}">${label}</button>`).join(''));
            }
            $('#md-refresh').on('click', load);
            $('#md-refresh-toggle').on('click', function () {
                const open = !$('#md-refresh-menu').hasClass('show');
                closePopovers('#md-refresh-menu');
                $('#md-refresh-menu').toggleClass('show', open);
                $(this).attr('aria-expanded', String(open));
            });
            $('#md-refresh-menu').on('click', 'button', function () {
                refreshEvery = String($(this).data('value') || '');
                applyRefresh();
                syncUrl();
                closePopovers();
            });

            /* ---------- init ---------- */
            const offset = -new Date().getTimezoneOffset();
            $('#md-tz-offset').text(`UTC${offset >= 0 ? '+' : '-'}${pad(Math.floor(Math.abs(offset) / 60))}:${pad(Math.abs(offset) % 60)}`);
            $('#md-tz-name').text(new Date().toLocaleTimeString('id-ID', { timeZoneName: 'short' }).split(' ').pop() || '');
            $('#md-range-text').text(rangeLabel(range));
            applyRefresh();
            syncUrl();
            load();
        });
    </script>
@endsection
