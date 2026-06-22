@extends('layouts.index')

@section('custom-link')
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <style>
        .dash-wrap {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .dash-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: #343a40;
            margin: 0;
        }

        .dash-subtitle {
            font-size: 0.78rem;
            color: #6c757d;
        }

        /* Filter bar */
        .dash-filter {
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 10px 16px;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
        }

        .dash-filter label {
            font-size: 0.75rem;
            color: #6c757d;
            margin: 0;
            white-space: nowrap;
        }

        .dash-filter input[type="date"],
        .dash-filter select {
            background: #f8f9fa;
            border: 1px solid #ced4da;
            border-radius: 5px;
            color: #495057;
            padding: 4px 10px;
            font-size: 0.8rem;
            height: 30px;
        }

        .dash-filter input[type="date"]:focus,
        .dash-filter select:focus {
            outline: none;
            border-color: #80bdff;
            box-shadow: 0 0 0 2px rgba(0, 123, 255, .15);
        }

        .btn-load-dash {
            background: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            padding: 5px 14px;
            font-size: 0.8rem;
            cursor: pointer;
            transition: background .2s;
        }

        .btn-load-dash:hover {
            background: #0069d9;
        }

        /* KPI grid */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 10px;
        }

        @media (max-width: 1200px) {
            .kpi-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 768px) {
            .kpi-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        .kpi-card {
            position: relative;
            overflow: hidden;
            background: #fff;
            border: 1px solid #dee2e6;
            border-left: 3px solid #dee2e6;
            border-radius: 8px;
            padding: 14px 14px 10px;
            transition: transform .15s, box-shadow .15s;
        }

        .kpi-icon {
            position: absolute;
            top: 12px;
            right: 14px;
            font-size: 1.8rem;
            opacity: .28;
            text-shadow: 0 0 .4px currentColor, 0 0 .4px currentColor;
        }

        .kpi-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, .08);
        }

        .kpi-card.c-earn {
            border-left-color: #28a745;
        }

        .kpi-card.c-cost {
            border-left-color: #dc3545;
        }

        .kpi-card.c-pos {
            border-left-color: #007bff;
        }

        .kpi-card.c-neg {
            border-left-color: #dc3545;
        }

        .kpi-card.c-margin {
            border-left-color: #6f42c1;
        }

        .kpi-card.c-output {
            border-left-color: #fd7e14;
        }

        .kpi-card.c-active {
            border-left-color: #20c997;
        }

        .kpi-label {
            font-size: 0.67rem;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: .06em;
            margin-bottom: 4px;
        }

        .kpi-value {
            font-size: 1.1rem;
            font-weight: 700;
            color: #343a40;
            line-height: 1.2;
        }

        .kpi-card.c-earn .kpi-value {
            color: #28a745;
        }

        .kpi-card.c-cost .kpi-value {
            color: #dc3545;
        }

        .kpi-card.c-pos .kpi-value {
            color: #007bff;
        }

        .kpi-card.c-neg .kpi-value {
            color: #dc3545;
        }

        .kpi-card.c-margin .kpi-value {
            color: #6f42c1;
        }

        .kpi-card.c-output .kpi-value {
            color: #fd7e14;
        }

        .kpi-card.c-active .kpi-value {
            color: #20c997;
        }

        .kpi-card.c-best {
            border-left-color: #ffc107;
        }

        .kpi-card.c-best .kpi-value {
            color: #d39e00;
        }

        .kpi-card.c-earn .kpi-icon {
            color: #28a745;
        }

        .kpi-card.c-cost .kpi-icon {
            color: #dc3545;
        }

        .kpi-card.c-pos .kpi-icon {
            color: #007bff;
        }

        .kpi-card.c-neg .kpi-icon {
            color: #dc3545;
        }

        .kpi-card.c-margin .kpi-icon {
            color: #6f42c1;
        }

        .kpi-card.c-output .kpi-icon {
            color: #fd7e14;
        }

        .kpi-card.c-active .kpi-icon {
            color: #20c997;
        }

        .kpi-card.c-best .kpi-icon {
            color: #ffc107;
        }

        .kpi-sub {
            font-size: 0.69rem;
            color: #adb5bd;
            margin-top: 2px;
        }

        .section-label {
            font-size: 0.72rem;
            font-weight: 600;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: .06em;
            margin: 4px 2px 8px;
        }

        /* Chart / detail cards */
        .chart-grid {
            display: grid;
            grid-template-columns: 3fr 2fr;
            gap: 10px;
        }

        @media (max-width: 992px) {
            .chart-grid {
                grid-template-columns: 1fr;
            }
        }

        .chart-card {
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 16px;
        }

        .card-heading {
            font-size: 0.72rem;
            font-weight: 600;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: .06em;
            padding-bottom: 8px;
            border-bottom: 1px solid #f0f0f0;
            margin-bottom: 12px;
        }

        /* DataTables */
        .dash-wrap .dataTables_wrapper .dataTables_info,
        .dash-wrap .dataTables_wrapper .dataTables_length label,
        .dash-wrap .dataTables_wrapper .dataTables_filter label {
            font-size: 0.75rem;
            color: #6c757d;
        }

        .dash-wrap .table thead th {
            background: #f8f9fa;
            color: #6c757d;
            border-color: #dee2e6;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: .04em;
            font-weight: 600;
        }

        .dash-wrap .table tbody td {
            border-color: #f0f0f0;
            font-size: 0.8rem;
            color: #495057;
        }

        .dash-wrap .table tbody tr:hover td {
            background: #f8f9fa;
        }

        .col-profit {
            color: #28a745 !important;
            font-weight: 600;
        }

        .col-loss {
            color: #dc3545 !important;
            font-weight: 600;
        }

        .badge-profit {
            background: #d4edda;
            color: #155724;
            padding: 2px 7px;
            border-radius: 20px;
            font-size: 0.68rem;
            font-weight: 600;
        }

        .badge-loss {
            background: #f8d7da;
            color: #721c24;
            padding: 2px 7px;
            border-radius: 20px;
            font-size: 0.68rem;
            font-weight: 600;
        }

        /* Skeleton */
        .skel {
            animation: pulse 1.4s ease-in-out infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: .3
            }

            50% {
                opacity: .7
            }
        }

        /* Period badge */
        #periodBadge {
            background: #e9ecef;
            border: 1px solid #dee2e6;
            color: #6c757d;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.72rem;
        }

        #periodBadge.active {
            background: #cce5ff;
            border-color: #b8daff;
            color: #004085;
        }

        /* Profit Line Ranking */
        .line-row {
            font-size: 0.8rem;
            padding: 7px 2px;
            border-bottom: 1px solid #f0f0f0;
        }

        .line-row:last-child {
            border-bottom: none;
        }

        .line-badge {
            background: #e9ecef;
            border-radius: 20px;
            padding: 2px 10px;
            font-size: 0.72rem;
            font-weight: 600;
            color: #495057;
        }

        /* Line Profit Heatmap */
        .heatmap-legend {
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .heatmap-legend i {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 2px;
        }

        .heatmap-table {
            border-collapse: separate;
            border-spacing: 3px;
            font-size: 0.68rem;
            white-space: nowrap;
        }

        .heatmap-table th {
            color: #6c757d;
            font-weight: 600;
            text-align: center;
            padding: 2px 4px;
            font-size: 0.65rem;
        }

        .heatmap-line {
            color: #495057;
            font-weight: 600;
            padding-right: 8px;
            text-align: right;
        }

        .heatmap-cell {
            width: 22px;
            height: 22px;
            min-width: 22px;
            border-radius: 4px;
            cursor: pointer;
        }

        .heatmap-total {
            font-weight: 700;
            padding-left: 10px;
        }

        .heatmap-tooltip {
            position: fixed;
            display: none;
            background: #343a40;
            color: #fff;
            padding: 6px 10px;
            border-radius: 6px;
            font-size: 0.72rem;
            line-height: 1.4;
            pointer-events: none;
            white-space: nowrap;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .2);
            z-index: 9999;
        }

        /* Sync overlay */
        #syncOverlay {
            position: fixed;
            inset: 0;
            display: none;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, .85);
            z-index: 10000;
        }

        #syncOverlay .sync-box {
            text-align: center;
            color: #343a40;
        }

        #syncOverlay .sync-spinner {
            width: 42px;
            height: 42px;
            border: 4px solid #dee2e6;
            border-top-color: #007bff;
            border-radius: 50%;
            margin: 0 auto 12px;
            animation: sync-spin .8s linear infinite;
        }

        @keyframes sync-spin {
            to {
                transform: rotate(360deg)
            }
        }

        #btnSync:disabled {
            background: #80bdff;
            cursor: not-allowed;
        }
    </style>
@endsection

@section('content')
    <div class="dash-wrap" id="dashWrap">

        {{-- Header --}}
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
                <h5 class="dash-title"><i class="fas fa-chart-line mr-2" style="color:#007bff"></i>Earning vs Estimated Cost
                </h5>
                <span class="dash-subtitle">PT Nirwana Alabare Garment &mdash; Management Dashboard</span>
            </div>
            <div class="d-flex align-items-center" style="gap:10px;">
                <span id="periodBadge">Pilih periode &amp; klik Load</span>
                @if (in_array(auth()->user()->username, ['admin_01', 'reza']))
                    <button type="button" id="btnSync" class="btn-load-dash">
                        <i class="fas fa-sync-alt mr-1"></i>Sync Data
                    </button>
                @endif
            </div>
        </div>

        {{-- Filter Bar --}}
        <div class="dash-filter mb-3">
            <label>Jenis Report:</label>
            <select id="filterReportType" style="min-width:160px">
                <option value="prod_earn">Production Earning</option>
            </select>
            <label class="ml-3">Periode:</label>
            <input type="date" id="startDate" value="{{ date('Y-m-01') }}">
            <span style="color:#adb5bd;font-size:0.8rem">s/d</span>
            <input type="date" id="endDate" value="{{ date('Y-m-d') }}">
            <label class="ml-3">Buyer:</label>
            <select id="filterBuyer" style="min-width:120px">
                <option value="all">All Buyers</option>
            </select>
            <label class="ml-2">Line:</label>
            <select id="filterLine" style="min-width:120px">
                <option value="all">All Lines</option>
            </select>
        </div>

        {{-- KPI Cards --}}
        <div class="kpi-grid mb-3">
            <div class="kpi-card c-earn">
                <i class="fas fa-money-bill-wave kpi-icon"></i>
                <div class="kpi-label">Total Earning</div>
                <div class="kpi-value skel" id="kpiEarning">—</div>
                <div class="kpi-sub">Est. earning rupiah</div>
            </div>
            <div class="kpi-card c-cost">
                <i class="fas fa-file-invoice-dollar kpi-icon"></i>
                <div class="kpi-label">Est. Cost</div>
                <div class="kpi-value skel" id="kpiCost">—</div>
                <div class="kpi-sub">Total estimated cost</div>
            </div>
            <div class="kpi-card c-pos" id="kpiBalCard">
                <i class="fas fa-wallet kpi-icon"></i>
                <div class="kpi-label">Balance</div>
                <div class="kpi-value skel" id="kpiBalance">—</div>
                <div class="kpi-sub">Earning − Cost</div>
            </div>
            <div class="kpi-card c-margin">
                <i class="fas fa-percentage kpi-icon"></i>
                <div class="kpi-label">Margin</div>
                <div class="kpi-value skel" id="kpiMargin">—</div>
                <div class="kpi-sub">Earn / Cost ratio</div>
            </div>
            <div class="kpi-card c-output">
                <i class="fas fa-boxes kpi-icon"></i>
                <div class="kpi-label">Total Output</div>
                <div class="kpi-value skel" id="kpiOutput">—</div>
                <div class="kpi-sub">pcs produksi</div>
            </div>
            <div class="kpi-card c-active">
                <i class="fas fa-users kpi-icon"></i>
                <div class="kpi-label">Active</div>
                <div class="kpi-value skel" id="kpiActive">—</div>
                <div class="kpi-sub">Lines &amp; Buyers</div>
            </div>
        </div>

        <div class="kpi-grid mb-3">
            <div class="kpi-card c-best" style="grid-column: span 2;">
                <i class="fas fa-trophy kpi-icon"></i>
                <div class="kpi-label">Best Day</div>
                <div class="kpi-value skel" id="kpiBestDay">—</div>
                <div class="kpi-sub">Highest Daily Earning</div>
            </div>

            <div class="kpi-card c-pos" style="grid-column: span 2;">
                <i class="fas fa-crown kpi-icon"></i>
                <div class="kpi-label">Top Buyer</div>
                <div class="kpi-value skel" id="kpiTopBuyer">—</div>
                <div class="kpi-sub">Highest Earning Produksi</div>
            </div>

            <div class="kpi-card c-neg" style="grid-column: span 2;">
                <i class="fas fa-triangle-exclamation kpi-icon"></i>
                <div class="kpi-label">Risk Watch</div>
                <div class="kpi-value skel" id="kpiRiskWatch">—</div>
                <div class="kpi-sub" id="kpiRiskWatchSub">Lowest Earning Produksi</div>
            </div>
        </div>

        {{-- Charts --}}
        <div class="chart-grid mb-3">
            <div class="chart-card">
                <div class="card-heading">Daily Earning vs Estimated Cost</div>
                <div id="chartDaily" style="height:300px;"></div>
            </div>
            <div class="chart-card">
                <div class="card-heading">Buyer Profitability (Top 10)</div>
                <div id="chartBuyer" style="height:300px;"></div>
            </div>
        </div>

        {{-- Profit Line / Daily Efficiency --}}
        <div class="chart-grid mb-3" style="grid-template-columns: repeat(2, 1fr);">
            <div class="chart-card">
                <div class="card-heading d-flex justify-content-between">
                    <span>Profit Line Ranking</span>
                    <span style="font-weight:400;text-transform:none;">from Profit Line sheet</span>
                </div>
                <div id="profitLineRanking" style="max-height:300px;overflow-y:auto;"></div>
            </div>
            <div class="chart-card">
                <div class="card-heading d-flex justify-content-between">
                    <span>Daily Efficiency</span>
                    <span style="font-weight:400;text-transform:none;">earning/min vs cost/min</span>
                </div>
                <div id="chartEfficiency" style="height:300px;"></div>
            </div>
        </div>

        {{-- Product Type Costing Comparison --}}
        <div class="chart-card mb-3">
            <div class="card-heading d-flex justify-content-between align-items-center">
                <span>Product Type Costing Comparison</span>
                <span style="font-weight:400;text-transform:none;">qty Costing vs qty SO - last 6 months</span>
            </div>
            <div class="mb-2" style="max-width:280px;">
                <input type="text" id="searchProductCosting" class="form-control form-control-sm"
                    placeholder="Cari product type... (mis. tshirt)">
            </div>
            <div id="chartProductCosting"></div>
            <div id="noProductCostingResult" style="display:none;text-align:center;color:#6c757d;padding:24px 0;font-size:0.85rem;">
                Tidak ada product type yang cocok.
            </div>
        </div>

        {{-- Line Profit Heatmap --}}
        <div class="chart-card mb-3">
            <div class="card-heading d-flex justify-content-between">
                <span>Line Profit Heatmap</span>
                <span style="font-weight:400;text-transform:none;">
                    <span class="heatmap-legend"><i style="background:#28a745"></i> profit</span>
                    <span class="heatmap-legend ml-3"><i style="background:#dc3545"></i> loss</span>
                </span>
            </div>
            <div id="lineHeatmap" style="overflow-x:auto;"></div>
        </div>

    </div>

    <div id="heatmapTooltip" class="heatmap-tooltip"></div>

    <div id="syncOverlay">
        <div class="sync-box">
            <div class="sync-spinner"></div>
            <div style="font-weight:600;">Sinkronisasi data berjalan...</div>
            <div style="font-size:0.78rem;color:#6c757d;">Mohon tunggu, proses ini bisa memakan waktu beberapa saat.</div>
        </div>
    </div>
@endsection

@section('custom-script')
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/apexcharts/apexcharts.min.js') }}"></script>

    <script>
        let apexDaily = null;
        let apexBuyer = null;
        let apexEfficiency = null;
        let apexProductCosting = null;
        let dtDetail = null;
        let rawRows = [];
        let productCosting = [];

        const MONTH_ABBR = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        function fmtRp(n) {
            n = parseFloat(n) || 0;
            return 'Rp ' + Math.round(n).toLocaleString('id-ID');
        }

        function fmtNum(n) {
            return Math.round(parseFloat(n) || 0).toLocaleString('id-ID');
        }

        function fmtCompact(n) {
            n = parseFloat(n) || 0;
            const abs = Math.abs(n);
            const opt = {
                maximumFractionDigits: 1
            };
            if (abs >= 1e9) return (n / 1e9).toLocaleString('id-ID', opt) + 'm';
            if (abs >= 1e6) return (n / 1e6).toLocaleString('id-ID', opt) + 'jt';
            if (abs >= 1e3) return (n / 1e3).toLocaleString('id-ID', opt) + 'rb';
            return Math.round(n).toLocaleString('id-ID');
        }

        function fmtDayLabel(tanggal) {
            const parts = String(tanggal).split('-');
            return parts[2] + ' ' + MONTH_ABBR[parseInt(parts[1], 10) - 1];
        }

        function fmtFullDate(tanggal) {
            const parts = String(tanggal).split('-');
            return parts[2] + ' ' + MONTH_ABBR[parseInt(parts[1], 10) - 1] + ' ' + parts[0];
        }

        function getParams() {
            return {
                start_date: $('#startDate').val(),
                end_date: $('#endDate').val(),
            };
        }

        function getFilters() {
            return {
                buyer: $('#filterBuyer').val() || 'all',
                line: $('#filterLine').val() || 'all',
            };
        }

        /* rows matching both buyer & line filters */
        function getFilteredRows() {
            const f = getFilters();
            return rawRows.filter(function(r) {
                if (f.buyer !== 'all' && r.buyer !== f.buyer) return false;
                if (f.line !== 'all' && r.sewing_line !== f.line) return false;
                return true;
            });
        }

        /* buyer chart ignores buyer filter but keeps line filter */
        function getLineFilteredRows() {
            const f = getFilters();
            return rawRows.filter(function(r) {
                if (f.line !== 'all' && r.sewing_line !== f.line) return false;
                return true;
            });
        }

        /* ---- Fetch raw data, then render everything client-side ---- */
        function loadRawData() {
            const p = getParams();
            if (!p.start_date || !p.end_date) {
                alert('Pilih periode terlebih dahulu.');
                return;
            }

            $('#periodBadge').addClass('active').text(p.start_date + '  s/d  ' + p.end_date);

            ['kpiEarning', 'kpiCost', 'kpiBalance', 'kpiMargin', 'kpiOutput', 'kpiActive', 'kpiBestDay', 'kpiTopBuyer',
                'kpiRiskWatch'
            ]
            .forEach(id => $('#' + id).addClass('skel').text('...'));

            $.get('{{ route('dashboard-mgt-report.raw-data') }}', p)
                .done(function(data) {
                    rawRows = (data && data.rows) || [];
                    loadFilterOptions();
                    renderDashboard();
                })
                .fail(function() {
                    rawRows = [];
                    renderDashboard();
                });
        }

        /* ---- Fetch product type costing comparison (fixed 6-month window) ---- */
        function loadProductCostingComparison() {
            $.get('{{ route('dashboard-mgt-report.product-costing-comparison') }}')
                .done(function(data) {
                    productCosting = (data && data.data) || [];
                    renderProductCosting();
                })
                .fail(function() {
                    productCosting = [];
                    renderProductCosting();
                });
        }

        /* ---- Render everything from cached rawRows ---- */
        function renderDashboard() {
            const rows = getFilteredRows();
            renderKPI(rows);
            renderHighlights(rows);
            renderDailyChart(rows);
            renderBuyerChart();
            renderDetailTable(rows);
            renderProfitLineRanking(rows);
            renderLineHeatmap(rows);
            renderDailyEfficiency(rows);
        }

        /* ---- KPI ---- */
        function renderKPI(rows) {
            if (!rows.length) {
                ['kpiEarning', 'kpiCost', 'kpiBalance', 'kpiMargin', 'kpiOutput', 'kpiActive']
                .forEach(id => $('#' + id).removeClass('skel').text('—'));
                return;
            }

            let totalEarning = 0,
                totalCost = 0,
                totalBalance = 0,
                totalOutput = 0;
            const lines = new Set();
            const buyers = new Set();

            rows.forEach(function(r) {
                totalEarning += parseFloat(r.tot_earning_rupiah) || 0;
                totalCost += parseFloat(r.est_tot_cost) || 0;
                totalBalance += parseFloat(r.blc) || 0;
                totalOutput += parseFloat(r.tot_output) || 0;
                if (r.sewing_line) lines.add(r.sewing_line);
                if (r.buyer) buyers.add(r.buyer);
            });

            const margin = totalCost > 0 ? (totalEarning / totalCost) * 100 : 0;

            $('#kpiEarning').removeClass('skel').text(fmtRp(totalEarning));
            $('#kpiCost').removeClass('skel').text(fmtRp(totalCost));
            $('#kpiBalance').removeClass('skel').text(fmtRp(totalBalance));
            $('#kpiMargin').removeClass('skel').text(margin.toFixed(1) + '%');
            $('#kpiOutput').removeClass('skel').text(fmtNum(totalOutput) + ' pcs');
            $('#kpiActive').removeClass('skel').text(lines.size + 'L / ' + buyers.size + 'B');

            $('#kpiBalCard').removeClass('c-pos c-neg').addClass(totalBalance >= 0 ? 'c-pos' : 'c-neg');
        }

        /* ---- Highlights (Best Day / Top Buyer / Risk Watch) ---- */
        function renderHighlights(rows) {
            const ids = ['kpiBestDay', 'kpiTopBuyer', 'kpiRiskWatch'];

            if ($('#filterReportType').val() !== 'prod_earn' || !rows.length) {
                ids.forEach(id => $('#' + id).removeClass('skel').text('—'));
                return;
            }

            const byDay = {};
            const byDayWorking = {};
            const byBuyer = {};
            const byBuyerWorking = {};
            rows.forEach(function(r) {
                const earn = parseFloat(r.tot_earning_rupiah) || 0;

                const dayKey = r.tanggal;
                if (!byDay[dayKey]) byDay[dayKey] = {
                    label: r.tanggal_fix || r.tanggal,
                    total: 0
                };
                byDay[dayKey].total += earn;

                if (r.stat_kerja !== 'LIBUR') {
                    if (!byDayWorking[dayKey]) byDayWorking[dayKey] = {
                        label: r.tanggal_fix || r.tanggal,
                        total: 0
                    };
                    byDayWorking[dayKey].total += earn;
                }

                if (!(r.buyer in byBuyer)) byBuyer[r.buyer] = 0;
                byBuyer[r.buyer] += earn;

                if (r.stat_kerja !== 'LIBUR') {
                    if (!(r.buyer in byBuyerWorking)) byBuyerWorking[r.buyer] = 0;
                    byBuyerWorking[r.buyer] += earn;
                }
            });

            let bestDay = null;
            Object.values(byDay).forEach(function(d) {
                if (!bestDay || d.total > bestDay.total) bestDay = d;
            });

            let topBuyer = null;
            Object.entries(byBuyer).forEach(function([buyer, total]) {
                if (!topBuyer || total > topBuyer.total) topBuyer = {
                    buyer,
                    total
                };
            });

            /* When filtered to a single buyer, show that buyer's lowest-earning day instead */
            const buyerFilter = getFilters().buyer;
            let riskLabel = null;
            let riskValue = null;
            if (buyerFilter !== 'all') {
                let riskDay = null;
                Object.values(byDayWorking).forEach(function(d) {
                    if (!riskDay || d.total < riskDay.total) riskDay = d;
                });
                if (riskDay) {
                    riskLabel = riskDay.label;
                    riskValue = riskDay.total;
                }
                $('#kpiRiskWatchSub').text('Lowest Earning Day');
            } else {
                let riskBuyer = null;
                Object.entries(byBuyerWorking).forEach(function([buyer, total]) {
                    if (!riskBuyer || total < riskBuyer.total) riskBuyer = {
                        buyer,
                        total
                    };
                });
                if (riskBuyer) {
                    riskLabel = riskBuyer.buyer;
                    riskValue = riskBuyer.total;
                }
                $('#kpiRiskWatchSub').text('Lowest Earning Produksi');
            }

            $('#kpiBestDay').removeClass('skel').text(bestDay ? (bestDay.label + ' · ' + fmtRp(bestDay.total)) : '—');
            $('#kpiTopBuyer').removeClass('skel').text(topBuyer ? (topBuyer.buyer + ' · ' + fmtRp(topBuyer.total)) : '—');
            $('#kpiRiskWatch').removeClass('skel').text(riskLabel !== null ? (riskLabel + ' · ' + fmtRp(riskValue)) :
                '—');
        }

        /* ---- Daily chart ---- */
        function renderDailyChart(rows) {
            const byDay = {};
            rows.forEach(function(r) {
                const key = r.tanggal;
                if (!byDay[key]) byDay[key] = {
                    tanggal: key,
                    earning: 0,
                    cost: 0,
                    balance: 0
                };
                byDay[key].earning += parseFloat(r.tot_earning_rupiah) || 0;
                byDay[key].cost += parseFloat(r.est_tot_cost) || 0;
                byDay[key].balance += parseFloat(r.blc) || 0;
            });

            const days = Object.values(byDay).sort((a, b) => a.tanggal.localeCompare(b.tanggal));

            const labels = days.map(d => fmtDayLabel(d.tanggal));
            const earning = days.map(d => Math.round(d.earning));
            const cost = days.map(d => Math.round(d.cost));
            const balance = days.map(d => Math.round(d.balance));

            const opts = {
                series: [{
                        name: 'Earning',
                        type: 'bar',
                        data: earning
                    },
                    {
                        name: 'Est Cost',
                        type: 'bar',
                        data: cost
                    },
                    {
                        name: 'Balance',
                        type: 'line',
                        data: balance
                    },
                ],
                chart: {
                    type: 'bar',
                    height: 300,
                    background: 'transparent',
                    toolbar: {
                        show: false
                    },
                    animations: {
                        enabled: true,
                        speed: 500
                    },
                    fontFamily: 'Segoe UI, sans-serif',
                },
                theme: {
                    mode: 'light'
                },
                colors: ['#28a745', '#dc3545', '#007bff'],
                plotOptions: {
                    bar: {
                        columnWidth: '55%',
                        borderRadius: 2
                    }
                },
                stroke: {
                    width: [0, 0, 2],
                    curve: 'smooth'
                },
                dataLabels: {
                    enabled: false
                },
                xaxis: {
                    categories: labels,
                    labels: {
                        style: {
                            fontSize: '10px',
                            colors: '#6c757d'
                        }
                    },
                    axisBorder: {
                        color: '#dee2e6'
                    },
                    axisTicks: {
                        color: '#dee2e6'
                    }
                },
                yaxis: {
                    labels: {
                        formatter: v => fmtCompact(v),
                        style: {
                            colors: '#6c757d',
                            fontSize: '10px'
                        }
                    }
                },
                grid: {
                    borderColor: '#f0f0f0'
                },
                legend: {
                    labels: {
                        colors: '#495057'
                    },
                    fontSize: '12px'
                },
                tooltip: {
                    theme: 'light',
                    y: {
                        formatter: v => fmtRp(v)
                    }
                },
            };

            if (apexDaily) apexDaily.destroy();
            apexDaily = new ApexCharts(document.querySelector('#chartDaily'), opts);
            apexDaily.render();
        }

        /* ---- Buyer chart ---- */
        function renderBuyerChart() {
            const rows = getLineFilteredRows();

            const byBuyer = {};
            rows.forEach(function(r) {
                const key = r.buyer || '—';
                if (!byBuyer[key]) byBuyer[key] = {
                    buyer: key,
                    earning: 0,
                    cost: 0
                };
                byBuyer[key].earning += parseFloat(r.tot_earning_rupiah) || 0;
                byBuyer[key].cost += parseFloat(r.est_tot_cost) || 0;
            });

            const top = Object.values(byBuyer)
                .sort((a, b) => b.earning - a.earning)
                .slice(0, 10);

            const buyers = top.map(r => r.buyer);
            const earning = top.map(r => Math.round(r.earning));
            const cost = top.map(r => Math.round(r.cost));

            const opts = {
                series: [{
                        name: 'Earning',
                        data: earning
                    },
                    {
                        name: 'Est Cost',
                        data: cost
                    },
                ],
                chart: {
                    type: 'bar',
                    height: 300,
                    background: 'transparent',
                    toolbar: {
                        show: false
                    },
                    animations: {
                        enabled: true,
                        speed: 500
                    },
                    fontFamily: 'Segoe UI, sans-serif',
                },
                theme: {
                    mode: 'light'
                },
                colors: ['#28a745', '#dc3545'],
                plotOptions: {
                    bar: {
                        horizontal: true,
                        barHeight: '55%',
                        borderRadius: 2
                    }
                },
                dataLabels: {
                    enabled: false
                },
                xaxis: {
                    categories: buyers,
                    labels: {
                        formatter: v => fmtCompact(v),
                        style: {
                            fontSize: '10px',
                            colors: '#6c757d'
                        }
                    }
                },
                yaxis: {
                    labels: {
                        style: {
                            colors: '#6c757d',
                            fontSize: '10px'
                        }
                    }
                },
                grid: {
                    borderColor: '#f0f0f0'
                },
                legend: {
                    labels: {
                        colors: '#495057'
                    },
                    fontSize: '12px'
                },
                tooltip: {
                    theme: 'light',
                    x: {
                        show: true
                    },
                    y: {
                        formatter: v => fmtRp(v)
                    }
                },
            };

            if (apexBuyer) apexBuyer.destroy();
            apexBuyer = new ApexCharts(document.querySelector('#chartBuyer'), opts);
            apexBuyer.render();
        }

        /* ---- Profit line ranking ---- */
        function fmtLineLabel(line) {
            const m = String(line).match(/^line_(\d+)$/i);
            if (m) return 'Line ' + parseInt(m[1], 10);
            return line || '—';
        }

        function renderProfitLineRanking(rows) {
            const byLine = {};
            rows.forEach(function(r) {
                const key = r.sewing_line || '—';
                if (!(key in byLine)) byLine[key] = 0;
                byLine[key] += parseFloat(r.blc) || 0;
            });

            const entries = Object.entries(byLine).map(([line, blc]) => ({
                line,
                blc
            }));
            entries.sort((a, b) => b.blc - a.blc);

            const totalAbs = entries.reduce((s, e) => s + Math.abs(e.blc), 0);

            const $el = $('#profitLineRanking').empty();
            if (!entries.length) {
                $el.append('<div class="text-center text-muted py-3" style="font-size:0.8rem;">Tidak ada data</div>');
                return;
            }

            entries.forEach(function(e) {
                const share = totalAbs > 0 ? (Math.abs(e.blc) / totalAbs) * 100 : 0;
                const profit = e.blc >= 0;
                $el.append(
                    `<div class="line-row d-flex align-items-center justify-content-between">
                        <span class="line-badge">${fmtLineLabel(e.line)}</span>
                        <span class="${profit ? 'col-profit' : 'col-loss'}">${fmtRp(e.blc)}</span>
                        <span style="color:#adb5bd;">${share.toFixed(1)}%</span>
                        <span class="${profit ? 'badge-profit' : 'badge-loss'}">${profit ? 'Profit' : 'Loss'}</span>
                    </div>`
                );
            });
        }

        /* ---- Line profit heatmap ---- */
        function heatmapColor(v, maxAbs) {
            if (!v || !maxAbs) return '#eef1f4';
            const intensity = Math.min(Math.abs(v) / maxAbs, 1);
            const alpha = 0.15 + intensity * 0.75;
            return v > 0 ? `rgba(40, 167, 69, ${alpha})` : `rgba(220, 53, 69, ${alpha})`;
        }

        function renderLineHeatmap(rows) {
            const cellMap = {};
            const lineTotals = {};
            const daysSet = new Set();

            rows.forEach(function(r) {
                const line = r.sewing_line || '—';
                const day = r.tanggal;
                const blc = parseFloat(r.blc) || 0;

                daysSet.add(day);
                if (!cellMap[line]) cellMap[line] = {};
                cellMap[line][day] = (cellMap[line][day] || 0) + blc;
                lineTotals[line] = (lineTotals[line] || 0) + blc;
            });

            const days = Array.from(daysSet).sort();
            const lines = Object.keys(cellMap).sort(function(a, b) {
                const na = parseInt((String(a).match(/(\d+)$/) || [])[1], 10);
                const nb = parseInt((String(b).match(/(\d+)$/) || [])[1], 10);
                if (!isNaN(na) && !isNaN(nb)) return na - nb;
                return String(a).localeCompare(String(b));
            });

            const $el = $('#lineHeatmap').empty();
            if (!lines.length || !days.length) {
                $el.append('<div class="text-center text-muted py-3" style="font-size:0.8rem;">Tidak ada data</div>');
                return;
            }

            let maxAbs = 0;
            lines.forEach(line => days.forEach(day => {
                const v = Math.abs(cellMap[line][day] || 0);
                if (v > maxAbs) maxAbs = v;
            }));

            let html = '<table class="heatmap-table"><thead><tr><th>Line</th>';
            days.forEach(d => html += `<th>${d.split('-')[2]}</th>`);
            html += '<th class="text-right">Total</th></tr></thead><tbody>';

            lines.forEach(function(line) {
                html += `<tr><td class="heatmap-line">${fmtLineLabel(line)}</td>`;
                days.forEach(function(day) {
                    const v = cellMap[line][day];
                    const value = (v === undefined) ? '' : v;
                    html +=
                        `<td class="heatmap-cell" style="background:${heatmapColor(v, maxAbs)}" data-line="${fmtLineLabel(line)}" data-day="${day}" data-value="${value}"></td>`;
                });
                const total = lineTotals[line] || 0;
                html +=
                    `<td class="heatmap-total text-right ${total >= 0 ? 'col-profit' : 'col-loss'}">${fmtNum(total)}</td>`;
                html += '</tr>';
            });

            html += '</tbody></table>';
            $el.html(html);
        }

        /* ---- Daily efficiency (earn/min vs cost/min) ---- */
        function renderDailyEfficiency(rows) {
            const byDay = {};
            rows.forEach(function(r) {
                const key = r.tanggal;
                if (!byDay[key]) byDay[key] = {
                    tanggal: key,
                    earn: 0,
                    cost: 0,
                    mins: 0
                };
                byDay[key].earn += parseFloat(r.tot_earning_rupiah) || 0;
                byDay[key].cost += parseFloat(r.est_tot_cost) || 0;
                byDay[key].mins += parseFloat(r.mins_prod) || 0;
            });

            const days = Object.values(byDay).sort((a, b) => a.tanggal.localeCompare(b.tanggal));
            const labels = days.map(d => fmtDayLabel(d.tanggal));
            const earnPerMin = days.map(d => d.mins > 0 ? Math.round(d.earn / d.mins) : 0);
            const costPerMin = days.map(d => d.mins > 0 ? Math.round(d.cost / d.mins) : 0);

            const opts = {
                series: [{
                        name: 'Earn/min',
                        data: earnPerMin
                    },
                    {
                        name: 'Cost/min',
                        data: costPerMin
                    },
                ],
                chart: {
                    type: 'line',
                    height: 300,
                    background: 'transparent',
                    toolbar: {
                        show: false
                    },
                    animations: {
                        enabled: true,
                        speed: 500
                    },
                    fontFamily: 'Segoe UI, sans-serif',
                },
                theme: {
                    mode: 'light'
                },
                colors: ['#17a2b8', '#6f42c1'],
                stroke: {
                    width: 2,
                    curve: 'smooth'
                },
                dataLabels: {
                    enabled: false
                },
                xaxis: {
                    categories: labels,
                    labels: {
                        style: {
                            fontSize: '10px',
                            colors: '#6c757d'
                        }
                    },
                    axisBorder: {
                        color: '#dee2e6'
                    },
                    axisTicks: {
                        color: '#dee2e6'
                    }
                },
                yaxis: {
                    labels: {
                        formatter: v => fmtCompact(v),
                        style: {
                            colors: '#6c757d',
                            fontSize: '10px'
                        }
                    }
                },
                grid: {
                    borderColor: '#f0f0f0'
                },
                legend: {
                    labels: {
                        colors: '#495057'
                    },
                    fontSize: '12px'
                },
                tooltip: {
                    theme: 'light',
                    y: {
                        formatter: v => fmtRp(v)
                    }
                },
            };

            if (apexEfficiency) apexEfficiency.destroy();
            apexEfficiency = new ApexCharts(document.querySelector('#chartEfficiency'), opts);
            apexEfficiency.render();
        }

        /* ---- Product type costing comparison (qty Costing vs qty SO) ---- */
        function renderProductCosting() {
            const search = ($('#searchProductCosting').val() || '').trim().toLowerCase();
            const filtered = search ?
                productCosting.filter(r => (r.product_item || '').toLowerCase().includes(search)) :
                productCosting;

            if (!filtered.length) {
                if (apexProductCosting) {
                    apexProductCosting.destroy();
                    apexProductCosting = null;
                }
                $('#chartProductCosting').hide();
                $('#noProductCostingResult').show();
                return;
            }
            $('#chartProductCosting').show();
            $('#noProductCostingResult').hide();

            const sorted = [...filtered].sort((a, b) =>
                (parseFloat(b.qty_cost) || 0) + (parseFloat(b.qty_so) || 0) -
                ((parseFloat(a.qty_cost) || 0) + (parseFloat(a.qty_so) || 0))
            );

            const items = sorted.map(r => r.product_item);
            const qtyCost = sorted.map(r => Math.round(parseFloat(r.qty_cost) || 0));
            const qtySo = sorted.map(r => Math.round(parseFloat(r.qty_so) || 0));

            // values span several orders of magnitude (tens up to millions), so plot on a
            // signed log scale: this keeps every non-zero bar's length truly proportional
            // to its value (no flattening) while still leaving small bars long enough for
            // their label, instead of being crushed to invisible by the largest outlier.
            const toLogScale = v => v === 0 ? 0 : Math.sign(v) * Math.log10(1 + Math.abs(v));
            const fromLogScale = v => Math.round(Math.pow(10, Math.abs(v)) - 1);

            const qtyCostPlot = qtyCost.map(v => -toLogScale(v));
            const qtySoPlot = qtySo.map(v => toLogScale(v));
            const maxVal = Math.max(...qtyCostPlot.map(Math.abs), ...qtySoPlot.map(Math.abs), 0.1) * 1.15;

            const trueValue = (seriesIndex, dataPointIndex) =>
                seriesIndex === 0 ? qtyCost[dataPointIndex] : qtySo[dataPointIndex];

            const rowHeight = 30;
            const chartHeight = Math.max(320, items.length * rowHeight);
            $('#chartProductCosting').css('height', chartHeight + 'px');

            const opts = {
                series: [{
                    name: 'Qty Costing',
                    data: qtyCostPlot
                }, {
                    name: 'Qty SO',
                    data: qtySoPlot
                }],
                chart: {
                    type: 'bar',
                    height: chartHeight,
                    stacked: true,
                    background: 'transparent',
                    toolbar: {
                        show: false
                    },
                    animations: {
                        enabled: true,
                        speed: 500
                    },
                    fontFamily: 'Segoe UI, sans-serif',
                },
                theme: {
                    mode: 'light'
                },
                colors: ['#a8d5ba', '#a9cce8'],
                plotOptions: {
                    bar: {
                        horizontal: true,
                        barHeight: '65%',
                        borderRadius: 2,
                        dataLabels: {
                            hideOverflowingLabels: false
                        }
                    }
                },
                dataLabels: {
                    enabled: true,
                    style: {
                        colors: ['#495057'],
                        fontSize: '11px'
                    },
                    formatter: (v, o) => fmtNum(trueValue(o.seriesIndex, o.dataPointIndex))
                },
                xaxis: {
                    categories: items,
                    min: -maxVal,
                    max: maxVal,
                    labels: {
                        formatter: v => fmtCompact(fromLogScale(v)),
                        style: {
                            fontSize: '11px',
                            colors: '#6c757d'
                        }
                    }
                },
                yaxis: {
                    labels: {
                        style: {
                            colors: '#495057',
                            fontSize: '11px'
                        },
                        maxWidth: 220
                    }
                },
                grid: {
                    borderColor: '#f0f0f0',
                    xaxis: {
                        lines: {
                            show: true
                        }
                    }
                },
                legend: {
                    show: true,
                    fontSize: '12px',
                    position: 'top',
                    horizontalAlign: 'right'
                },
                tooltip: {
                    theme: 'light',
                    shared: false,
                    y: {
                        formatter: (v, o) => fmtNum(trueValue(o.seriesIndex, o.dataPointIndex))
                    }
                },
            };

            if (apexProductCosting) apexProductCosting.destroy();
            apexProductCosting = new ApexCharts(document.querySelector('#chartProductCosting'), opts);
            apexProductCosting.render();
        }

        /* ---- Detail table ---- */
        function renderDetailTable(rows) {
            $('#detailCount').text('(' + rows.length + ' rows)');

            if (dtDetail) {
                dtDetail.destroy();
                dtDetail = null;
            }

            const sorted = rows.slice().sort(function(a, b) {
                if (a.tanggal !== b.tanggal) return a.tanggal < b.tanggal ? 1 : -1;
                return String(a.sewing_line).localeCompare(String(b.sewing_line));
            });

            const tbody = $('#detailTableBody').empty();
            sorted.forEach(function(r) {
                const bal = parseFloat(r.blc) || 0;
                const margin = parseFloat(r.percent_est_earn) || 0;
                const profit = bal >= 0;
                tbody.append(
                    `<tr>
                    <td>${r.tanggal_fix || r.tanggal}</td>
                    <td>${r.sewing_line}</td>
                    <td>${r.buyer}</td>
                    <td>${r.kpno || '—'}</td>
                    <td class="text-right">${fmtNum(r.tot_output)}</td>
                    <td class="text-right">${parseFloat(r.eff_line || 0).toFixed(1)}%</td>
                    <td class="text-right">${fmtRp(r.tot_earning_rupiah)}</td>
                    <td class="text-right">${fmtRp(r.est_tot_cost)}</td>
                    <td class="text-right ${profit ? 'col-profit' : 'col-loss'}">${fmtRp(r.blc)}</td>
                    <td class="text-right">
                        <span class="${profit ? 'badge-profit' : 'badge-loss'}">${margin.toFixed(1)}%</span>
                    </td>
                </tr>`
                );
            });

            dtDetail = $('#detailTable').DataTable({
                pageLength: 25,
                order: [
                    [0, 'desc']
                ],
                language: {
                    search: 'Cari:',
                    lengthMenu: 'Tampilkan _MENU_ data',
                    info: 'Data _START_–_END_ dari _TOTAL_',
                    paginate: {
                        previous: '‹',
                        next: '›'
                    }
                },
                columnDefs: [{
                    className: 'text-right',
                    targets: [4, 5, 6, 7, 8, 9]
                }]
            });
        }

        /* ---- Filter options ---- */
        function fillSelectOptions($select, allLabel, values) {
            const prev = $select.val();

            $select.html('<option value="all">' + allLabel + '</option>');
            values.forEach(function(v) {
                $select.append('<option value="' + v + '">' + v + '</option>');
            });
            if (prev) $select.val(prev);
        }

        function loadFilterOptions() {
            const buyers = [...new Set(rawRows.map(r => r.buyer).filter(Boolean))].sort();
            const lines = [...new Set(rawRows.map(r => r.sewing_line).filter(Boolean))].sort();

            fillSelectOptions($('#filterBuyer'), 'All Buyers', buyers);
            fillSelectOptions($('#filterLine'), 'All Lines', lines);
        }

        /* ---- Sync data (call mysql_sb refresh procedures) ---- */
        function syncData() {
            const $btn = $('#btnSync');
            const $overlay = $('#syncOverlay');

            $btn.prop('disabled', true);
            $overlay.css('display', 'flex');

            $.post('{{ route('dashboard-mgt-report.sync') }}', {
                    _token: '{{ csrf_token() }}'
                })
                .done(function() {
                    loadRawData();
                    loadProductCostingComparison();
                })
                .fail(function() {
                    alert('Sinkronisasi gagal. Silakan coba lagi.');
                })
                .always(function() {
                    $btn.prop('disabled', false);
                    $overlay.css('display', 'none');
                });
        }

        $(document).ready(function() {
            $('#startDate, #endDate').on('change', loadRawData);
            $('#filterReportType, #filterBuyer, #filterLine').on('change', renderDashboard);
            $('#btnSync').on('click', syncData);
            $('#searchProductCosting').on('input', renderProductCosting);

            const $tooltip = $('#heatmapTooltip');
            $('#lineHeatmap').on('mouseenter', '.heatmap-cell', function(e) {
                const $cell = $(this);
                const line = $cell.data('line');
                const day = $cell.data('day');
                const rawValue = $cell.attr('data-value');
                const valueLabel = rawValue === '' ? 'Tidak ada data' : fmtRp(parseFloat(rawValue));

                $tooltip.html(`<strong>${line}</strong> &middot; ${fmtFullDate(day)}<br>${valueLabel}`);
                $tooltip.css('display', 'block');
            }).on('mousemove', '.heatmap-cell', function(e) {
                $tooltip.css({
                    left: (e.clientX + 12) + 'px',
                    top: (e.clientY + 12) + 'px',
                });
            }).on('mouseleave', '.heatmap-cell', function() {
                $tooltip.css('display', 'none');
            });

            loadRawData();
            loadProductCostingComparison();
        });
    </script>
@endsection
