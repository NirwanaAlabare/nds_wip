@extends('layouts.index')

@section('custom-link')
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
    <style>
        .bap-stat-card {
            background: #fff;
            border-radius: 12px;
            padding: 18px 20px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .08);
            height: 100%;
        }

        .bap-stat-icon {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
        }

        .bap-stat-value {
            font-size: 28px;
            font-weight: 700;
            line-height: 1;
            margin: 6px 0 14px;
        }

        .bap-stat-bar {
            height: 4px;
            border-radius: 2px;
            background: #eef0f3;
            overflow: hidden;
        }

        .bap-stat-bar>div {
            height: 100%;
            border-radius: 2px;
            transition: width .4s ease;
        }

        .bap-panel {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .08);
            height: 100%;
        }

        .bap-activity-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #f0f1f3;
        }

        .bap-activity-item:last-child {
            border-bottom: none;
        }

        .bap-activity-icon {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            color: #fff;
            margin-right: 12px;
        }

        .bap-status-pill {
            font-size: 11px;
            padding: 2px 8px;
            border-radius: 10px;
            font-weight: 600;
        }
    </style>
@endsection

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0"><i class="fas fa-chart-bar"></i> Dashboard BAP</h5>
        <select class="form-control form-control-sm select2bs4" id="filter-tahun" style="width: 120px;">
            @for ($y = date('Y'); $y >= date('Y') - 5; $y--)
                <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>{{ $y }}</option>
            @endfor
        </select>
    </div>

    <div class="row mb-3">
        <div class="col-md-3 col-6 mb-3">
            <div class="bap-stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="text-muted" style="font-size: 13px;">Total BAP</span>
                    <div class="bap-stat-icon" style="background:#f1f2f4; color:#555;"><i class="fas fa-file-alt"></i></div>
                </div>
                <div class="bap-stat-value" id="stat-total">0</div>
                <div class="bap-stat-bar">
                    <div id="bar-total" style="width:100%; background:#14b8a6;"></div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <div class="bap-stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="text-muted" style="font-size: 13px;">Dalam Proses</span>
                    <div class="bap-stat-icon" style="background:#f1f2f4; color:#666;"><i class="fas fa-hourglass-half"></i>
                    </div>
                </div>
                <div class="bap-stat-value" id="stat-proses">0</div>
                <div class="bap-stat-bar">
                    <div id="bar-proses" style="width:0%; background:#94a3b8;"></div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <div class="bap-stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="text-muted" style="font-size: 13px;">Selesai</span>
                    <div class="bap-stat-icon" style="background:#e5f7f1; color:#0f9d68;"><i class="fas fa-check"></i></div>
                </div>
                <div class="bap-stat-value text-success" id="stat-selesai">0</div>
                <div class="bap-stat-bar">
                    <div id="bar-selesai" style="width:0%; background:#22c55e;"></div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <div class="bap-stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="text-muted" style="font-size: 13px;">Dibatalkan</span>
                    <div class="bap-stat-icon" style="background:#fdecec; color:#e0362f;"><i class="fas fa-times"></i></div>
                </div>
                <div class="bap-stat-value text-danger" id="stat-cancel">0</div>
                <div class="bap-stat-bar">
                    <div id="bar-cancel" style="width:0%; background:#ef4444;"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-6 mb-3">
            <div class="bap-panel">
                <h6 class="fw-bold mb-3">BAP per Departement</h6>
                <div id="chart-bap-department"></div>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="bap-panel">
                <h6 class="fw-bold mb-3">BAP per Bulan</h6>
                <div id="chart-bap-monthly"></div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="bap-panel">
                <h6 class="fw-bold mb-3">Riwayat Aktivitas Terakhir BAP</h6>
                <div id="bap-activity-list">
                    <div class="text-muted text-center py-3">Memuat data...</div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('custom-script')
    <script src="{{ asset('plugins/apexcharts/apexcharts.min.js') }}"></script>
    <!-- Select2 -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        function notif() {
            alert("Maaf, Fitur belum tersedia!");
        }
    </script>
    <script>
        const departmentPalette = ['#14b8a6', '#a78bfa', '#f59e0b', '#fb7185', '#60a5fa', '#34d399', '#f472b6', '#818cf8'];

        let chartDepartment, chartMonthly;

        function initChartDepartment() {
            const options = {
                series: [{
                    name: 'Total BAP',
                    data: []
                }],
                chart: {
                    type: 'bar',
                    height: 320,
                    toolbar: {
                        show: false
                    }
                },
                plotOptions: {
                    bar: {
                        horizontal: true,
                        barHeight: '55%',
                        distributed: true
                    }
                },
                colors: departmentPalette,
                legend: {
                    show: false
                },
                dataLabels: {
                    enabled: true,
                    formatter: function(val) {
                        return Math.round(val);
                    },
                    style: {
                        colors: ['#333']
                    },
                    offsetX: 16
                },
                xaxis: {
                    categories: [],
                    forceNiceScale: false,
                    tickAmount: 1,
                    min: 0,
                    labels: {
                        formatter: function(val) {
                            return Math.round(val);
                        }
                    }
                },
                tooltip: {
                    y: {
                        formatter: function(val) {
                            return Math.round(val);
                        }
                    }
                }
            };

            chartDepartment = new ApexCharts(document.querySelector("#chart-bap-department"), options);
            chartDepartment.render();
        }

        function initChartMonthly() {
            const options = {
                series: [{
                    name: 'Total BAP',
                    data: []
                }],
                chart: {
                    type: 'bar',
                    height: 320,
                    toolbar: {
                        show: false
                    }
                },
                plotOptions: {
                    bar: {
                        columnWidth: '45%',
                        borderRadius: 4
                    }
                },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shade: 'light',
                        type: 'vertical',
                        gradientToColors: ['#a78bfa'],
                        stops: [0, 100]
                    }
                },
                colors: ['#14b8a6'],
                dataLabels: {
                    enabled: true,
                    offsetY: -20,
                    style: {
                        colors: ['#333']
                    }
                },
                xaxis: {
                    categories: []
                },
                yaxis: {
                    labels: {
                        formatter: function(val) {
                            return Math.round(val);
                        }
                    }
                }
            };

            chartMonthly = new ApexCharts(document.querySelector("#chart-bap-monthly"), options);
            chartMonthly.render();
        }

        function loadChartDepartment() {
            $.ajax({
                type: "GET",
                url: '{{ route('chart-bap-department') }}',
                data: {
                    tahun: $('#filter-tahun').val()
                },
                success: function(response) {
                    const categories = response.map(item => item.department || '-');
                    const data = response.map(item => item.total);
                    const maxValue = Math.max(1, ...data);

                    chartDepartment.updateOptions({
                        xaxis: {
                            categories: categories,
                            tickAmount: Math.min(maxValue, 5)
                        }
                    }, false, true);
                    chartDepartment.updateSeries([{
                        name: 'Total BAP',
                        data: data
                    }]);
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                }
            });
        }

        function loadChartMonthly() {
            $.ajax({
                type: "GET",
                url: '{{ route('chart-bap-monthly') }}',
                data: {
                    tahun: $('#filter-tahun').val()
                },
                success: function(response) {
                    const categories = response.map(item => item.bulan);
                    const data = response.map(item => item.total);

                    chartMonthly.updateOptions({
                        xaxis: {
                            categories: categories
                        }
                    }, false, true);
                    chartMonthly.updateSeries([{
                        name: 'Total BAP',
                        data: data
                    }]);
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                }
            });
        }

        function loadSummary() {
            $.ajax({
                type: "GET",
                url: '{{ route('summary-bap-helpdesk') }}',
                data: {
                    tahun: $('#filter-tahun').val()
                },
                success: function(response) {
                    const total = response.total || 0;
                    $('#stat-total').text(total);
                    $('#stat-proses').text(response.proses);
                    $('#stat-selesai').text(response.selesai);
                    $('#stat-cancel').text(response.cancel);

                    const pct = (val) => total > 0 ? Math.max((val / total) * 100, 3) : 0;
                    $('#bar-proses').css('width', pct(response.proses) + '%');
                    $('#bar-selesai').css('width', pct(response.selesai) + '%');
                    $('#bar-cancel').css('width', pct(response.cancel) + '%');
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                }
            });
        }

        function loadRecentActivity() {
            $.ajax({
                type: "GET",
                url: '{{ route('recent-activity-bap') }}',
                data: {
                    tahun: $('#filter-tahun').val()
                },
                success: function(response) {
                    const $list = $('#bap-activity-list');
                    $list.empty();

                    if (!response.length) {
                        $list.append('<div class="text-muted text-center py-3">Belum ada data</div>');
                        return;
                    }

                    const statusMap = {
                        proses: {
                            icon: 'fa-hourglass-half',
                            bg: '#94a3b8',
                            pillBg: '#eef1f4',
                            pillColor: '#555',
                            label: 'Proses'
                        },
                        selesai: {
                            icon: 'fa-check',
                            bg: '#22c55e',
                            pillBg: '#e5f7f1',
                            pillColor: '#0f9d68',
                            label: 'Selesai'
                        },
                        cancel: {
                            icon: 'fa-times',
                            bg: '#ef4444',
                            pillBg: '#fdecec',
                            pillColor: '#e0362f',
                            label: 'Dibatalkan'
                        }
                    };

                    response.forEach(function(item) {
                        const s = statusMap[item.status];
                        const row = `
                            <div class="bap-activity-item">
                                <div class="d-flex align-items-center">
                                    <div class="bap-activity-icon" style="background:${s.bg};">
                                        <i class="fas ${s.icon}"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold" style="font-size: 13px;">
                                            ${item.no_form} (${item.department || '-'})
                                            <span class="bap-status-pill" style="background:${s.pillBg}; color:${s.pillColor};">${s.label}</span>
                                            <span class="text-muted" style="font-size: 12px;">(${item.bulan})</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-muted" style="font-size: 12px;">
                                    <i class="far fa-clock"></i> ${item.updated_at}
                                </div>
                            </div>`;
                        $list.append(row);
                    });
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                }
            });
        }

        function reloadAll() {
            loadSummary();
            loadChartDepartment();
            loadChartMonthly();
            loadRecentActivity();
        }

        $(document).ready(() => {
            $('#filter-tahun').select2({
                theme: 'bootstrap4',
                width: 'resolve'
            });
            $('.select2-container--bootstrap4 .select2-selection--single').css({
                'height': '31px',
                'font-size': '12px',
                'line-height': '30px'
            });

            initChartDepartment();
            initChartMonthly();
            reloadAll();

            $('#filter-tahun').on('change', reloadAll);
        });
    </script>
@endsection
