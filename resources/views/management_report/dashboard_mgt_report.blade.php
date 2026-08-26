@extends('layouts.index')

@section('custom-link')
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
    <style>
        .dash-wrap {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            position: relative;
        }

        /* Non-blocking loading badge: does not cover/disable the page,
           filters stay clickable while data is being fetched/rendered. */
        #dashLoadOverlay {
            position: fixed;
            top: 16px;
            right: 16px;
            display: none;
            align-items: center;
            gap: 10px;
            background: #fff;
            color: #343a40;
            padding: 10px 16px;
            border-radius: 8px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, .18);
            z-index: 10000;
            font-size: 0.85rem;
            font-weight: 600;
            pointer-events: none;
        }

        #dashLoadOverlay .dash-load-spinner {
            width: 18px;
            height: 18px;
            border: 3px solid #dee2e6;
            border-top-color: #007bff;
            border-radius: 50%;
            animation: sync-spin .8s linear infinite;
            flex-shrink: 0;
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

        .btn-load-dash.btn-excel-dash {
            background: #1d6f42;
        }

        .btn-load-dash.btn-excel-dash:hover {
            background: #175633;
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
    @if (in_array(auth()->user()->username, ['reza', 'admin_01', 'nirwana_it']))
    <div class="dash-wrap" id="dashWrap">

        <div id="dashLoadOverlay">
            <div class="dash-load-spinner"></div>
            <span>Memuat data...</span>
        </div>

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
                <button type="button" id="btnExportExcel" class="btn-load-dash btn-excel-dash">
                    <i class="fas fa-file-excel mr-1"></i>Export Excel
                </button>
            </div>
        </div>

        {{-- Filter Bar --}}
        <div class="dash-filter mb-3">
            <label>Jenis Report:</label>
            <select id="filterReportType" class="select2bs4" style="min-width:160px">
                <option value="prod_earn">Production Earning</option>
                <option value="full_earn">Full Earning</option>
            </select>
            <label class="ml-3">Periode:</label>
            <input type="date" id="startDate" value="{{ date('Y-m-01') }}">
            <span style="color:#adb5bd;font-size:0.8rem">s/d</span>
            <input type="date" id="endDate" value="{{ date('Y-m-d') }}">
            <span id="prodEarnFilters" class="d-flex flex-wrap align-items-center" style="gap:8px;">
                <label class="ml-3">Buyer:</label>
                <select id="filterBuyer" class="select2bs4" style="min-width:120px">
                    <option value="all">All Buyers</option>
                </select>
                <label class="ml-2">Line:</label>
                <select id="filterLine" class="select2bs4" style="min-width:120px">
                    <option value="all">All Lines</option>
                </select>
            </span>
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
                <div class="kpi-label" id="kpiOutputLabel">Total Output</div>
                <div class="kpi-value skel" id="kpiOutput">—</div>
                <div class="kpi-sub" id="kpiOutputSub">pcs produksi</div>
            </div>
            <div class="kpi-card c-active">
                <i class="fas fa-users kpi-icon"></i>
                <div class="kpi-label" id="kpiActiveLabel">Active</div>
                <div class="kpi-value skel" id="kpiActive">—</div>
                <div class="kpi-sub" id="kpiActiveSub">Lines &amp; Buyers</div>
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
                <div class="kpi-label" id="kpiTopBuyerLabel">Top Buyer</div>
                <div class="kpi-value skel" id="kpiTopBuyer">—</div>
                <div class="kpi-sub" id="kpiTopBuyerSub">Highest Earning Produksi</div>
            </div>

            <div class="kpi-card c-neg" style="grid-column: span 2;">
                <i class="fas fa-triangle-exclamation kpi-icon"></i>
                <div class="kpi-label" id="kpiRiskWatchLabel">Risk Watch</div>
                <div class="kpi-value skel" id="kpiRiskWatch">—</div>
                <div class="kpi-sub" id="kpiRiskWatchSub">Lowest Earning Produksi</div>
            </div>
        </div>

        {{-- Charts --}}
        <div class="chart-grid mb-3">
            <div class="chart-card">
                <div class="card-heading d-flex justify-content-between">
                    <span>Daily Earning vs Estimated Cost</span>
                    <span id="headingDailyNote" style="font-weight:400;text-transform:none;"></span>
                </div>
                <div id="chartDaily" style="height:300px;"></div>
            </div>
            <div class="chart-card" id="cardBuyerChart">
                <div class="card-heading" id="headingBuyerChart">Buyer Profitability (Top 10)</div>
                <div id="chartBuyer" style="height:300px;"></div>
            </div>
        </div>

        {{-- Profit Line / Daily Efficiency --}}
        <div class="chart-grid mb-3" style="grid-template-columns: repeat(2, 1fr);" id="rowProfitLineEfficiency">
            <div class="chart-card" id="cardProfitLine">
                <div class="card-heading d-flex justify-content-between">
                    <span>Profit Line Ranking</span>
                    <span style="font-weight:400;text-transform:none;">from Profit Line sheet</span>
                </div>
                <div id="profitLineRanking" style="max-height:300px;overflow-y:auto;"></div>
            </div>
            <div class="chart-card" id="cardEfficiency">
                <div class="card-heading" id="headingEfficiency">Daily Efficiency</div>
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
        <div class="chart-card mb-3" id="cardHeatmap">
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

    {{-- Modal Export Excel --}}
    <div class="modal fade" id="modalExportExcel" tabindex="-1" role="dialog"
        aria-labelledby="modalExportExcelLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalExportExcelLabel" style="font-size:0.95rem;font-weight:600;">
                        <i class="fas fa-file-excel me-1" style="color:#1d6f42"></i>Export Excel
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formExportExcel" method="GET" action="{{ route('dashboard-mgt-report.export') }}"
                    target="_blank">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label" style="font-size:0.8rem;font-weight:600;">Tanggal Dari</label>
                                <input type="date" name="start_date" id="exportStartDate"
                                    class="form-control form-control-sm" value="{{ date('Y-m-01') }}" required>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label" style="font-size:0.8rem;font-weight:600;">Tanggal Sampai</label>
                                <input type="date" name="end_date" id="exportEndDate"
                                    class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                        <small class="text-muted" style="font-size:0.75rem;">
                            Data yang di-export mengikuti periode di atas. Pastikan sudah klik
                            <strong>Sync Data</strong> agar data terbaru ikut ter-export.
                        </small>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-sm btn-success">
                            <i class="fas fa-download me-1"></i>Download
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="syncOverlay">
        <div class="sync-box">
            <div class="sync-spinner"></div>
            <div style="font-weight:600;">Sinkronisasi data berjalan...</div>
            <div style="font-size:0.78rem;color:#6c757d;">Mohon tunggu, proses ini bisa memakan waktu beberapa saat.</div>
        </div>
    </div>
    @endif
@endsection

@section('custom-script')
    @if (in_array(auth()->user()->username, ['reza', 'admin_01', 'nirwana_it']))
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/apexcharts/apexcharts.min.js') }}"></script>
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>

    <script>
        let apexDaily = null;
        let apexBuyer = null;
        let apexEfficiency = null;
        let apexProductCosting = null;
        let rawRows = [];
        let dailySummaryRows = []; // rekap harian dari mgt_rep_tmp_sum_prod_earning
        let productCosting = [];
        let reportType = 'prod_earn';

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

        /* =========================================================================
         * REPORT REGISTRY
         * Tiap tipe report (Production Earning / Full Earning) punya definisi
         * sendiri: sumber data, normalisasi row, label UI, KPI, dan widget-nya.
         * Tambah tipe report baru = tambah 1 objek di REPORTS, tanpa menyentuh
         * fungsi shared (loadRawData / renderDashboard / renderKPI).
         * ========================================================================= */

        const PROD_EARN_REPORT = {
            key: 'prod_earn',
            url: '{{ route('dashboard-mgt-report.raw-data') }}',

            /* chart Earning vs Est Cost pakai rekap harian (mgt_rep_tmp_sum_prod_earning),
             * bukan hasil penjumlahan row per line */
            useDailySummary: true,

            dailySeries: function() {
                return dailySummaryRows.map(function(r) {
                    return {
                        tanggal: r.tanggal,
                        earning: parseFloat(r.sum_tot_earning_rupiah) || 0,
                        cost: parseFloat(r.est_tot_cost) || 0,
                        balance: parseFloat(r.blc) || 0,
                    };
                }).sort((a, b) => a.tanggal.localeCompare(b.tanggal));
            },

            /* data sudah pakai nama field standar, tidak perlu dinormalisasi */
            normalizeRow: function(r) {
                return r;
            },

            applyUI: function() {
                $('#prodEarnFilters').show();
                $('#cardProfitLine').show();
                $('#cardHeatmap').show();
                $('#cardEfficiency').css('grid-column', '');

                $('#headingDailyNote').text('from Sum Prod Earn sheet · semua line');
                $('#headingBuyerChart').text('Buyer Profitability (Top 10)');
                $('#headingEfficiency').text('Daily Efficiency');

                $('#kpiOutputLabel').text('Total Output');
                $('#kpiOutputSub').text('pcs produksi');
                $('#kpiActiveLabel').text('Active');
                $('#kpiActiveSub').text('Lines & Buyers');

                $('#kpiTopBuyerLabel').text('Top Buyer');
                $('#kpiTopBuyerSub').text('Highest Earning Produksi');
                $('#kpiRiskWatchLabel').text('Risk Watch');
                $('#kpiRiskWatchSub').text('Lowest Earning Produksi');
            },

            /* dua KPI card terakhir yang isinya beda tiap tipe report */
            renderKpiExtra: function(t) {
                $('#kpiOutput').removeClass('skel').text(fmtNum(t.output) + ' pcs');
                $('#kpiActive').removeClass('skel').text(t.lines.size + 'L / ' + t.buyers.size + 'B');
            },

            render: function(rows) {
                renderProdEarnHighlights(rows);
                renderProdEarnBuyerChart();
                renderProdEarnProfitLineRanking(rows);
                renderProdEarnLineHeatmap(rows);
                renderProdEarnDailyEfficiency(rows);
            },
        };

        const FULL_EARN_REPORT = {
            key: 'full_earn',
            url: '{{ route('dashboard-mgt-report.raw-data-full-earn') }}',

            /* row full earning sudah 1 baris per tanggal, tidak perlu request tambahan */
            useDailySummary: false,

            dailySeries: function(rows) {
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
                return Object.values(byDay).sort((a, b) => a.tanggal.localeCompare(b.tanggal));
            },

            /* samakan nama field ke standar yang dipakai widget shared */
            normalizeRow: function(r) {
                return Object.assign({}, r, {
                    tot_earning_rupiah: r.sum_tot_earning_rupiah,
                    sewing_line: null,
                    buyer: null,
                });
            },

            applyUI: function() {
                $('#prodEarnFilters').hide();
                $('#cardProfitLine').hide();
                $('#cardHeatmap').hide();
                $('#cardEfficiency').css('grid-column', '1 / -1');

                $('#headingDailyNote').text('');
                $('#headingBuyerChart').text('Earning Breakdown by Type');
                $('#headingEfficiency').text('Balance Trend by Earning Type');

                $('#kpiOutputLabel').text('Prod Est. Balance');
                $('#kpiOutputSub').text('Earning Produksi − Cost');
                $('#kpiActiveLabel').text('Mkt Est. Balance');
                $('#kpiActiveSub').text('Earning Market − Cost');

                $('#kpiTopBuyerLabel').text('Full Earning Est');
                $('#kpiTopBuyerSub').text('Estimated Full Earning (periode)');
                $('#kpiRiskWatchLabel').text('Full Earn Balance');
                $('#kpiRiskWatchSub').text('Full Earning − Cost (periode)');
            },

            renderKpiExtra: function(t) {
                $('#kpiOutput').removeClass('skel').text(fmtRp(t.prodBalance));
                $('#kpiActive').removeClass('skel').text(fmtRp(t.mktBalance));
            },

            render: function(rows) {
                renderFullEarnHighlights(rows);
                renderFullEarnBreakdownChart(rows);
                renderFullEarnBalanceTrendChart(rows);
            },
        };

        const REPORTS = {
            prod_earn: PROD_EARN_REPORT,
            full_earn: FULL_EARN_REPORT,
        };

        function currentReport() {
            return REPORTS[reportType] || PROD_EARN_REPORT;
        }

        /* ---- Report type UI toggling ---- */
        function applyReportTypeUI() {
            currentReport().applyUI();
        }

        /* ---- Fetch raw data, then render everything client-side ---- */
        function showDashLoading() {
            $('#dashLoadOverlay').css('display', 'flex');
        }

        function hideDashLoading() {
            $('#dashLoadOverlay').css('display', 'none');
        }

        let rawDataXhr = null;
        let dailySummaryXhr = null;
        let rawDataReqId = 0;

        function loadRawData() {
            const p = getParams();
            if (!p.start_date || !p.end_date) {
                alert('Pilih periode terlebih dahulu.');
                return;
            }

            reportType = $('#filterReportType').val() || 'prod_earn';
            applyReportTypeUI();

            $('#periodBadge').addClass('active').text(p.start_date + '  s/d  ' + p.end_date);

            ['kpiEarning', 'kpiCost', 'kpiBalance', 'kpiMargin', 'kpiOutput', 'kpiActive', 'kpiBestDay', 'kpiTopBuyer',
                'kpiRiskWatch'
            ]
            .forEach(id => $('#' + id).addClass('skel').text('...'));

            showDashLoading();

            // Cancel any in-flight request so a slow/stale response can't
            // overwrite newer data (was causing 0/empty values on quick filter changes).
            if (rawDataXhr) rawDataXhr.abort();
            if (dailySummaryXhr) dailySummaryXhr.abort();

            const reqId = ++rawDataReqId;
            const report = currentReport();

            rawDataXhr = $.get(report.url, p);
            dailySummaryXhr = report.useDailySummary ?
                $.get('{{ route('dashboard-mgt-report.daily-summary') }}', p) :
                null;

            // .then() dipakai supaya tiap request resolve dengan 1 nilai saja,
            // jadi $.when meneruskannya apa adanya (bukan array [data, status, xhr]).
            const rowsReq = rawDataXhr.then(d => (d && d.rows) || []);
            const summaryReq = dailySummaryXhr ?
                dailySummaryXhr.then(d => (d && d.rows) || []) :
                $.Deferred().resolve([]).promise();

            $.when(rowsReq, summaryReq)
                .done(function(rows, summary) {
                    if (reqId !== rawDataReqId) return; // stale response, ignore
                    rawRows = rows.map(report.normalizeRow);
                    dailySummaryRows = summary;
                    loadFilterOptions();
                    renderDashboard();
                })
                .fail(function(jqXHR, textStatus) {
                    if (textStatus === 'abort' || reqId !== rawDataReqId) return;
                    rawRows = [];
                    dailySummaryRows = [];
                    renderDashboard();
                })
                .always(function(jqXHRorData, textStatus) {
                    if (textStatus === 'abort' || reqId !== rawDataReqId) return;
                    hideDashLoading();
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

            // widget shared (dipakai semua tipe report)
            renderKPI(rows);
            renderDailyChart(currentReport().dailySeries(rows));

            // widget khusus tipe report yang sedang dipilih
            currentReport().render(rows);
        }

        /* =========================================================================
         * SHARED WIDGETS
         * ========================================================================= */

        /* ---- KPI: hitung total dari rows ---- */
        function sumKpiTotals(rows) {
            const t = {
                earning: 0,
                cost: 0,
                balance: 0,
                output: 0,
                prodBalance: 0,
                mktBalance: 0,
                lines: new Set(),
                buyers: new Set(),
            };

            rows.forEach(function(r) {
                t.earning += parseFloat(r.tot_earning_rupiah) || 0;
                t.cost += parseFloat(r.est_tot_cost) || 0;
                t.balance += parseFloat(r.blc) || 0;
                t.output += parseFloat(r.tot_output) || 0;
                t.prodBalance += parseFloat(r.blc_est_earn_cost_prod) || 0;
                t.mktBalance += parseFloat(r.blc_est_earn_cost_mkt) || 0;
                if (r.sewing_line) t.lines.add(r.sewing_line);
                if (r.buyer) t.buyers.add(r.buyer);
            });

            return t;
        }

        /* ---- KPI: 4 card pertama shared, 2 card terakhir diserahkan ke report ---- */
        function renderKPI(rows) {
            const ids = ['kpiEarning', 'kpiCost', 'kpiBalance', 'kpiMargin', 'kpiOutput', 'kpiActive'];
            if (!rows.length) {
                ids.forEach(id => $('#' + id).removeClass('skel').text('—'));
                return;
            }

            const t = sumKpiTotals(rows);
            const margin = t.cost > 0 ? (t.earning / t.cost) * 100 : 0;

            $('#kpiEarning').removeClass('skel').text(fmtRp(t.earning));
            $('#kpiCost').removeClass('skel').text(fmtRp(t.cost));
            $('#kpiBalance').removeClass('skel').text(fmtRp(t.balance));
            $('#kpiMargin').removeClass('skel').text(margin.toFixed(1) + '%');

            currentReport().renderKpiExtra(t);

            $('#kpiBalCard').removeClass('c-pos c-neg').addClass(t.balance >= 0 ? 'c-pos' : 'c-neg');
        }

        /* ---- Highlights for Full Earning (Best Day / Full Earning Est / Full Earn Balance) ---- */
        function renderFullEarnHighlights(rows) {
            const ids = ['kpiBestDay', 'kpiTopBuyer', 'kpiRiskWatch'];
            if (!rows.length) {
                ids.forEach(id => $('#' + id).removeClass('skel').text('—'));
                return;
            }

            let bestDay = null;
            let sumFullEarning = 0;
            let sumFullBalance = 0;

            rows.forEach(function(r) {
                const earn = parseFloat(r.tot_earning_rupiah) || 0;
                if (!bestDay || earn > bestDay.total) {
                    bestDay = {
                        label: r.tanggal_fix || r.tanggal,
                        total: earn
                    };
                }
                sumFullEarning += parseFloat(r.sum_est_full_earning) || 0;
                sumFullBalance += parseFloat(r.blc_full_earning) || 0;
            });

            $('#kpiBestDay').removeClass('skel').text(bestDay ? (bestDay.label + ' · ' + fmtRp(bestDay.total)) : '—');
            $('#kpiTopBuyer').removeClass('skel').text(fmtRp(sumFullEarning));
            $('#kpiRiskWatch').removeClass('skel').text(fmtRp(sumFullBalance));
        }

        /* ---- Highlights (Best Day / Top Buyer / Risk Watch) ---- */
        function renderProdEarnHighlights(rows) {
            const ids = ['kpiBestDay', 'kpiTopBuyer', 'kpiRiskWatch'];

            if (!rows.length) {
                ids.forEach(id => $('#' + id).removeClass('skel').text('—'));
                return;
            }

            const byDay = {};
            const byBuyer = {};
            rows.forEach(function(r) {
                const earn = parseFloat(r.tot_earning_rupiah) || 0;

                const dayKey = r.tanggal;
                if (!byDay[dayKey]) byDay[dayKey] = {
                    label: r.tanggal_fix || r.tanggal,
                    total: 0
                };
                byDay[dayKey].total += earn;

                if (!(r.buyer in byBuyer)) byBuyer[r.buyer] = 0;
                byBuyer[r.buyer] += earn;
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

            $('#kpiBestDay').removeClass('skel').text(bestDay ? (bestDay.label + ' · ' + fmtRp(bestDay.total)) : '—');
            $('#kpiTopBuyer').removeClass('skel').text(topBuyer ? (topBuyer.buyer + ' · ' + fmtRp(topBuyer.total)) : '—');

            renderProdEarnRiskWatch(rows);
        }

        /* ---- Buang seluruh row yang jatuh di hari libur ----
         * Satu tanggal dianggap LIBUR kalau tidak ada satu pun row berstatus kerja
         * di tanggal itu, atau total earning-nya nol (tidak ada produksi sama sekali).
         * Row LIBUR di tanggal kerja (mis. line tertentu saja yang libur) ikut dibuang. */
        function excludeHolidayRows(rows) {
            const perDay = {};
            rows.forEach(function(r) {
                const d = r.tanggal;
                if (!perDay[d]) perDay[d] = {
                    hasWork: false,
                    earning: 0
                };
                if (r.stat_kerja !== 'LIBUR') perDay[d].hasWork = true;
                perDay[d].earning += parseFloat(r.tot_earning_rupiah) || 0;
            });

            return rows.filter(function(r) {
                const day = perDay[r.tanggal];
                return r.stat_kerja !== 'LIBUR' && day.hasWork && day.earning > 0;
            });
        }

        /* ---- Risk Watch (selalu mengabaikan hari libur) ----
         * Default: buyer dengan earning produksi terendah.
         * Kalau difilter ke satu buyer: hari kerja dengan earning terendah. */
        function renderProdEarnRiskWatch(rows) {
            const workingRows = excludeHolidayRows(rows);
            const perBuyer = getFilters().buyer === 'all';

            $('#kpiRiskWatchSub').text(perBuyer ? 'Lowest Earning Produksi' : 'Lowest Earning Day');

            if (!workingRows.length) {
                $('#kpiRiskWatch').removeClass('skel').text('—');
                return;
            }

            const totals = {};
            workingRows.forEach(function(r) {
                const key = perBuyer ? r.buyer : r.tanggal;
                if (!totals[key]) totals[key] = {
                    label: perBuyer ? r.buyer : (r.tanggal_fix || r.tanggal),
                    total: 0
                };
                totals[key].total += parseFloat(r.tot_earning_rupiah) || 0;
            });

            let lowest = null;
            Object.values(totals).forEach(function(t) {
                if (!lowest || t.total < lowest.total) lowest = t;
            });

            $('#kpiRiskWatch').removeClass('skel').text(lowest ? (lowest.label + ' · ' + fmtRp(lowest.total)) : '—');
        }

        /* ---- Daily chart ----
         * days: [{ tanggal, earning, cost, balance }] sudah terurut,
         * disiapkan oleh dailySeries() milik masing-masing tipe report. */
        function renderDailyChart(days) {
            const labels = days.map(d => fmtDayLabel(d.tanggal));
            const realEarning = days.map(d => Math.round(d.earning));
            const realCost = days.map(d => Math.round(d.cost));
            const balance = days.map(d => Math.round(d.balance));

            /* Nilai kecil (mis. idle cost hari libur ~500rb) tingginya 0 pixel di
             * sumbu yang skalanya miliaran, jadi terlihat seperti tidak ada data.
             * Batang bernilai > 0 diberi tinggi minimum ~1.5% dari rentang sumbu
             * supaya tetap terlihat; tooltip tetap memakai angka aslinya. */
            const allValues = realEarning.concat(realCost, balance, [0]);
            const span = Math.max.apply(null, allValues) - Math.min.apply(null, allValues);
            const minVisible = span * 0.015;
            const lift = v => (v > 0 && v < minVisible ? Math.round(minVisible) : v);

            const earning = realEarning.map(lift);
            const cost = realCost.map(lift);

            /* tooltip: kembalikan angka asli, bukan nilai yang sudah di-lift */
            const realValueAt = function(seriesIndex, i) {
                if (seriesIndex === 0) return realEarning[i];
                if (seriesIndex === 1) return realCost[i];
                return balance[i];
            };

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
                        formatter: function(v, opts) {
                            if (!opts || typeof opts.dataPointIndex !== 'number') return fmtRp(v);
                            return fmtRp(realValueAt(opts.seriesIndex, opts.dataPointIndex));
                        }
                    }
                },
            };

            if (apexDaily) apexDaily.destroy();
            apexDaily = new ApexCharts(document.querySelector('#chartDaily'), opts);
            apexDaily.render();
        }

        /* ---- Buyer chart (hari libur diabaikan) ---- */
        function renderProdEarnBuyerChart() {
            const rows = excludeHolidayRows(getLineFilteredRows());

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

        /* ---- Earning breakdown by type (Full Earning report) ---- */
        function renderFullEarnBreakdownChart(rows) {
            let actualEarn = 0,
                actualCost = 0,
                fullEarn = 0,
                fullCost = 0,
                prodEarn = 0,
                prodCost = 0,
                mktEarn = 0,
                mktCost = 0;

            rows.forEach(function(r) {
                actualEarn += parseFloat(r.tot_earning_rupiah) || 0;
                actualCost += parseFloat(r.est_tot_cost) || 0;
                fullEarn += parseFloat(r.sum_est_full_earning) || 0;
                fullCost += parseFloat(r.est_tot_cost) || 0;
                prodEarn += parseFloat(r.sum_est_earning_prod) || 0;
                prodCost += parseFloat(r.sum_est_cost_prod) || 0;
                mktEarn += parseFloat(r.sum_est_earning_mkt) || 0;
                mktCost += parseFloat(r.sum_est_cost_mkt) || 0;
            });

            const categories = ['Actual', 'Full Earning', 'Production Est', 'Market Est'];

            const opts = {
                series: [{
                        name: 'Earning',
                        data: [Math.round(actualEarn), Math.round(fullEarn), Math.round(prodEarn), Math.round(mktEarn)]
                    },
                    {
                        name: 'Cost',
                        data: [Math.round(actualCost), Math.round(fullCost), Math.round(prodCost), Math.round(mktCost)]
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
                    categories: categories,
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
                    y: {
                        formatter: v => fmtRp(v)
                    }
                },
            };

            if (apexBuyer) apexBuyer.destroy();
            apexBuyer = new ApexCharts(document.querySelector('#chartBuyer'), opts);
            apexBuyer.render();
        }

        /* ---- Balance trend by earning type (Full Earning report) ---- */
        function renderFullEarnBalanceTrendChart(rows) {
            const sorted = rows.slice().sort((a, b) => a.tanggal.localeCompare(b.tanggal));
            const labels = sorted.map(r => fmtDayLabel(r.tanggal));
            const blc = sorted.map(r => Math.round(parseFloat(r.blc) || 0));
            const blcFull = sorted.map(r => Math.round(parseFloat(r.blc_full_earning) || 0));
            const blcProd = sorted.map(r => Math.round(parseFloat(r.blc_est_earn_cost_prod) || 0));
            const blcMkt = sorted.map(r => Math.round(parseFloat(r.blc_est_earn_cost_mkt) || 0));

            const opts = {
                series: [{
                        name: 'Balance',
                        data: blc
                    },
                    {
                        name: 'Full Earning Balance',
                        data: blcFull
                    },
                    {
                        name: 'Prod Est. Balance',
                        data: blcProd
                    },
                    {
                        name: 'Mkt Est. Balance',
                        data: blcMkt
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
                colors: ['#007bff', '#28a745', '#fd7e14', '#6f42c1'],
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

        /* ---- Profit line ranking ---- */
        function fmtLineLabel(line) {
            const m = String(line).match(/^line_(\d+)$/i);
            if (m) return 'Line ' + parseInt(m[1], 10);
            return line || '—';
        }

        /* ---- Nilai profit per line ----
         * Dipakai bersama oleh Profit Line Ranking & Line Profit Heatmap.
         * Memakai blc_full_earn (full earning - cost) supaya angkanya sama dengan
         * Laporan Profit Line, yang query-nya:
         *   SELECT tanggal, sewing_line, SUM(blc_full_earn) FROM mgt_rep_tmp_earning
         *   GROUP BY tanggal, sewing_line
         * Sebelumnya di sini memakai blc (production earning - cost), jadi nilainya
         * jauh lebih kecil dan tidak cocok dengan laporannya. */
        function profitLineValue(r) {
            return parseFloat(r.blc_full_earn) || 0;
        }

        /* Row tanpa sewing_line (idle cost hari libur / hari tanpa produksi) tidak
         * ditampilkan di ranking maupun heatmap — bukan milik line mana pun. */
        function withSewingLine(rows) {
            return rows.filter(r => r.sewing_line);
        }

        function renderProdEarnProfitLineRanking(rows) {
            const byLine = {};
            withSewingLine(rows).forEach(function(r) {
                const key = r.sewing_line;
                if (!(key in byLine)) byLine[key] = 0;
                byLine[key] += profitLineValue(r);
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

        function renderProdEarnLineHeatmap(rows) {
            const cellMap = {};
            const lineTotals = {};
            const daysSet = new Set();

            withSewingLine(rows).forEach(function(r) {
                const line = r.sewing_line;
                const day = r.tanggal;
                const blc = profitLineValue(r);

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
        function renderProdEarnDailyEfficiency(rows) {
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

        /* ---- Filter options ---- */
        function fillSelectOptions($select, allLabel, values) {
            const prev = $select.val();

            $select.html('<option value="all">' + allLabel + '</option>');
            values.forEach(function(v) {
                $select.append('<option value="' + v + '">' + v + '</option>');
            });
            if (prev) $select.val(prev);
            $select.trigger('change.select2');
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
            $('.select2bs4').select2({
                theme: 'bootstrap4',
                width: 'resolve',
                minimumResultsForSearch: Infinity,
            });
            $('.select2-container--bootstrap4 .select2-selection--single').css({
                'height': '30px',
                'font-size': '0.8rem',
                'line-height': '30px',
            });

            $('#startDate, #endDate, #filterReportType').on('change', loadRawData);
            $('#filterBuyer, #filterLine').on('change', function() {
                showDashLoading();
                setTimeout(function() {
                    renderDashboard();
                    hideDashLoading();
                }, 30);
            });
            $('#btnSync').on('click', syncData);

            const modalExport = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalExportExcel'));

            $('#btnExportExcel').on('click', function() {
                modalExport.show();
            });

            $('#modalExportExcel').on('show.bs.modal', function() {
                $('#exportStartDate').val($('#startDate').val());
                $('#exportEndDate').val($('#endDate').val());
            });

            $('#formExportExcel').on('submit', function(e) {
                const start = $('#exportStartDate').val();
                const end = $('#exportEndDate').val();

                if (!start || !end) {
                    e.preventDefault();
                    alert('Tanggal dari dan sampai wajib diisi.');
                    return;
                }

                if (start > end) {
                    e.preventDefault();
                    alert('Tanggal dari tidak boleh melebihi tanggal sampai.');
                    return;
                }

                modalExport.hide();
            });
            $('#searchProductCosting').on('input', renderProductCosting);

            applyReportTypeUI();

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
    @endif
@endsection
