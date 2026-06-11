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
            background: #fff;
            border: 1px solid #dee2e6;
            border-left: 3px solid #dee2e6;
            border-radius: 8px;
            padding: 14px 14px 10px;
            transition: transform .15s, box-shadow .15s;
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

        .kpi-sub {
            font-size: 0.69rem;
            color: #adb5bd;
            margin-top: 2px;
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
    </style>
@endsection

@section('content')
    <div class="dash-wrap" id="dashWrap">

        {{-- Header --}}
        {{-- <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h5 class="dash-title">Earning vs Estimated Cost</h5>
            <span class="dash-subtitle">PT Nirwana Alabare Garment &mdash; Management Dashboard</span>
        </div>
        <span id="periodBadge">Pilih periode &amp; klik Load</span>
    </div> --}}

        {{-- Filter Bar --}}
        {{-- <div class="dash-filter mb-3">
        <label>Periode:</label>
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
        <button class="btn-load-dash ml-2" onclick="loadDashboard()">
            <i class="fas fa-sync-alt mr-1"></i> Load
        </button>
    </div> --}}

        {{-- KPI Cards --}}
        {{-- <div class="kpi-grid mb-3">
        <div class="kpi-card c-earn">
            <div class="kpi-label">Total Earning</div>
            <div class="kpi-value skel" id="kpiEarning">—</div>
            <div class="kpi-sub">Est. earning rupiah</div>
        </div>
        <div class="kpi-card c-cost">
            <div class="kpi-label">Est. Cost</div>
            <div class="kpi-value skel" id="kpiCost">—</div>
            <div class="kpi-sub">Total estimated cost</div>
        </div>
        <div class="kpi-card c-pos" id="kpiBalCard">
            <div class="kpi-label">Balance</div>
            <div class="kpi-value skel" id="kpiBalance">—</div>
            <div class="kpi-sub">Earning − Cost</div>
        </div>
        <div class="kpi-card c-margin">
            <div class="kpi-label">Margin</div>
            <div class="kpi-value skel" id="kpiMargin">—</div>
            <div class="kpi-sub">Earn / Cost ratio</div>
        </div>
        <div class="kpi-card c-output">
            <div class="kpi-label">Total Output</div>
            <div class="kpi-value skel" id="kpiOutput">—</div>
            <div class="kpi-sub">pcs produksi</div>
        </div>
        <div class="kpi-card c-active">
            <div class="kpi-label">Active</div>
            <div class="kpi-value skel" id="kpiActive">—</div>
            <div class="kpi-sub">Lines &amp; Buyers</div>
        </div>
    </div> --}}

        {{-- Charts --}}
        {{-- <div class="chart-grid mb-3">
        <div class="chart-card">
            <div class="card-heading">Daily Earning vs Estimated Cost</div>
            <div id="chartDaily" style="height:300px;"></div>
        </div>
        <div class="chart-card">
            <div class="card-heading">Buyer Profitability (Top 10)</div>
            <div id="chartBuyer" style="height:300px;"></div>
        </div>
    </div> --}}

        {{-- Detail Table --}}
        {{-- <div class="chart-card">
        <div class="card-heading">
            Detail Earning Records
            <span id="detailCount" style="color:#007bff;font-weight:400;font-size:0.72rem;margin-left:8px;text-transform:none;letter-spacing:0;"></span>
        </div>
        <div class="table-responsive">
            <table id="detailTable" class="table table-hover table-sm w-100">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Line</th>
                        <th>Buyer</th>
                        <th>KP No</th>
                        <th>Output</th>
                        <th>Eff %</th>
                        <th>Earning</th>
                        <th>Est Cost</th>
                        <th>Balance</th>
                        <th>Margin %</th>
                    </tr>
                </thead>
                <tbody id="detailTableBody"></tbody>
            </table>
        </div>
    </div> --}}

    </div>
@endsection

@section('custom-script')
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/apexcharts/apexcharts.min.js') }}"></script>

    {{-- <script>
        let apexDaily = null;
        let apexBuyer = null;
        let dtDetail = null;

        function fmtRp(n) {
            n = parseFloat(n) || 0;
            const abs = Math.abs(n),
                sign = n < 0 ? '-' : '';
            if (abs >= 1e9) return sign + 'Rp ' + (abs / 1e9).toFixed(2) + 'M';
            if (abs >= 1e6) return sign + 'Rp ' + (abs / 1e6).toFixed(1) + 'Jt';
            if (abs >= 1e3) return sign + 'Rp ' + (abs / 1e3).toFixed(0) + 'Rb';
            return sign + 'Rp ' + abs.toFixed(0);
        }

        function fmtNum(n) {
            return Math.round(parseFloat(n) || 0).toLocaleString('id-ID');
        }

        function getParams() {
            return {
                start_date: $('#startDate').val(),
                end_date: $('#endDate').val(),
                buyer: $('#filterBuyer').val() || 'all',
                line: $('#filterLine').val() || 'all',
            };
        }

        /* ---- Main load ---- */
        function loadDashboard() {
            const p = getParams();
            if (!p.start_date || !p.end_date) {
                alert('Pilih periode terlebih dahulu.');
                return;
            }

            ['kpiEarning', 'kpiCost', 'kpiBalance', 'kpiMargin', 'kpiOutput', 'kpiActive']
            .forEach(id => $('#' + id).addClass('skel').text('...'));

            $('#periodBadge').addClass('active').text(p.start_date + '  s/d  ' + p.end_date);

            loadKPI(p);
            loadDailyChart(p);
            loadBuyerChart(p);
            loadDetailTable(p);
        }

        /* ---- KPI ---- */
        function loadKPI(p) {
            $.get('{{ route('dashboard-mgt-report.summary') }}', p)
                .done(function(d) {
                    const bal = parseFloat(d.total_balance) || 0;
                    const margin = parseFloat(d.avg_margin) || 0;

                    $('#kpiEarning').removeClass('skel').text(fmtRp(d.total_earning));
                    $('#kpiCost').removeClass('skel').text(fmtRp(d.total_cost));
                    $('#kpiBalance').removeClass('skel').text(fmtRp(d.total_balance));
                    $('#kpiMargin').removeClass('skel').text(margin.toFixed(1) + '%');
                    $('#kpiOutput').removeClass('skel').text(fmtNum(d.total_output) + ' pcs');
                    $('#kpiActive').removeClass('skel').text(d.active_lines + 'L / ' + d.active_buyers + 'B');

                    $('#kpiBalCard').removeClass('c-pos c-neg').addClass(bal >= 0 ? 'c-pos' : 'c-neg');
                })
                .fail(function() {
                    ['kpiEarning', 'kpiCost', 'kpiBalance', 'kpiMargin', 'kpiOutput', 'kpiActive']
                    .forEach(id => $('#' + id).removeClass('skel').text('—'));
                });
        }

        /* ---- Daily chart ---- */
        function loadDailyChart(p) {
            $.get('{{ route('dashboard-mgt-report.daily-chart') }}', p)
                .done(function(rows) {
                    const labels = rows.map(r => r.label);
                    const earning = rows.map(r => Math.round(parseFloat(r.earning) || 0));
                    const cost = rows.map(r => Math.round(parseFloat(r.cost) || 0));
                    const balance = rows.map(r => Math.round(parseFloat(r.balance) || 0));

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
                                formatter: v => fmtRp(v),
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
                });
        }

        /* ---- Buyer chart ---- */
        function loadBuyerChart(p) {
            $.get('{{ route('dashboard-mgt-report.buyer-chart') }}', p)
                .done(function(rows) {
                    const buyers = rows.map(r => r.buyer);
                    const earning = rows.map(r => Math.round(parseFloat(r.earning) || 0));
                    const cost = rows.map(r => Math.round(parseFloat(r.cost) || 0));

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
                                formatter: v => fmtRp(v),
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
                });
        }

        /* ---- Detail table ---- */
        function loadDetailTable(p) {
            $.get('{{ route('dashboard-mgt-report.detail-table') }}', p)
                .done(function(rows) {
                    $('#detailCount').text('(' + rows.length + ' rows)');

                    if (dtDetail) {
                        dtDetail.destroy();
                        dtDetail = null;
                    }

                    const tbody = $('#detailTableBody').empty();
                    rows.forEach(function(r) {
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
                });
        }

        /* ---- Filter options ---- */
        function loadFilterOptions() {
            const start = $('#startDate').val();
            const end = $('#endDate').val();
            if (!start || !end) return;

            $.get('{{ route('dashboard-mgt-report.filter-options') }}', {
                    start_date: start,
                    end_date: end
                })
                .done(function(data) {
                    const $b = $('#filterBuyer'),
                        prevB = $b.val();
                    const $l = $('#filterLine'),
                        prevL = $l.val();

                    $b.html('<option value="all">All Buyers</option>');
                    data.buyers.forEach(function(b) {
                        $b.append('<option value="' + b.buyer + '">' + b.buyer + '</option>');
                    });
                    if (prevB) $b.val(prevB);

                    $l.html('<option value="all">All Lines</option>');
                    data.lines.forEach(function(l) {
                        $l.append('<option value="' + l.sewing_line + '">' + l.sewing_line + '</option>');
                    });
                    if (prevL) $l.val(prevL);
                });
        }

        $(document).ready(function() {
            $('#startDate, #endDate').on('change', loadFilterOptions);
            loadFilterOptions();
            loadDashboard();
        });
    </script> --}}
@endsection
