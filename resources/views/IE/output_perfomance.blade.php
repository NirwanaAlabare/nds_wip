@extends('layouts.index')

@section('custom-link')
    {{-- <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}"> --}}

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables 2.0/jquery.dataTables.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables 2.0/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables 2.0/fixedColumns.bootstrap4.min.css') }}">
    <!-- jQuery -->
    <script src="{{ asset('plugins/datatables 2.0/jquery-3.3.1.js') }}"></script>


    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

    <!-- ApexCharts -->
    <link rel="stylesheet" href="{{ asset('plugins/apexcharts/apexcharts.css') }}">

    <style>
        #myTable td.wrap-col {
            white-space: normal !important;
            word-break: break-word;
        }

        .oppa-header {
            background: linear-gradient(90deg, #0b1f4d 0%, #16327a 100%);
            color: #fff;
            border-radius: .5rem .5rem 0 0;
            padding: 1.25rem 1.5rem;
        }

        .oppa-header h5 {
            font-weight: 700;
            margin-bottom: .15rem;
        }

        .oppa-header small {
            opacity: .85;
        }

        .oppa-stat-card {
            border: 1px solid #e9ecef;
            border-radius: .5rem;
            padding: .9rem 1rem;
            height: 100%;
            background: #fff;
        }

        .oppa-stat-label {
            font-size: .75rem;
            color: #6c757d;
            font-weight: 600;
            text-transform: none;
        }

        .oppa-stat-value {
            font-size: 1.6rem;
            font-weight: 700;
            line-height: 1.3;
        }

        .oppa-stat-sub {
            font-size: .72rem;
            color: #9aa2ab;
        }

        .oppa-stat-value.text-success {
            color: #28a745 !important;
        }
    </style>
@endsection

@section('content')
    @php
        $first = $data[0] ?? null;
        $bestLine = $first->username ?? '-';
        $bestOutput = $first->tot_rfts ?? 0;
        $manPower = $first->man_power ?? 0;
        $smv = $first->smv ?? 0;
        // Estimasi efficiency berdasarkan SMV, asumsi 480 menit kerja/hari
        $bestEfficiency = $manPower > 0 ? round((($bestOutput * $smv) / ($manPower * 480)) * 100, 1) : 0;
        $outputPerPerson = $manPower > 0 ? round($bestOutput / $manPower, 1) : 0;

        // Susun data chart per line dari $chartData (semua line untuk style ini)
        $chartLines = [];
        foreach ($chartData as $row) {
            $lineLabel = $row->username ?: ('Line ' . $row->master_plan_id);
            $eff = $row->man_power > 0 ? round((($row->tot_rfts * $row->smv) / ($row->man_power * 480)) * 100, 1) : 0;
            $chartLines[] = [
                'label' => $lineLabel,
                'output' => (int) $row->tot_rfts,
                'efficiency' => $eff,
            ];
        }
    @endphp

    <div class="card card-sb">
        <div class="oppa-header">
            <h5><i class="fas fa-chart-line"></i> Style Output Performance Analyzer</h5>
            <small>Analisa line terbaik berdasarkan output, efficiency, SMV dan output per person.</small>
        </div>

        <div class="card-body">
            <form method="GET" action="{{ route('IE_output_performance') }}">
                <div class="row align-items-end mb-4">
                    <div class="col-12 col-md-5">
                        <label class="fw-bold mb-1" style="font-size:.8rem;">Cari Style</label>
                        <input type="text" name="styleno" id="styleno" class="form-control"
                            placeholder="Ketik style, contoh: GP016433" value="{{ $styleno }}">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="fw-bold mb-1" style="font-size:.8rem;">Urutkan Berdasarkan</label>
                        <select name="sort_by" id="sort_by" class="form-control select2bs4 w-100" style="width: 100%;">
                            <option value="overall_score" selected>Overall Score</option>
                            <option value="output">Output</option>
                            <option value="efficiency">Efficiency</option>
                            <option value="output_per_person">Output / Person</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-3 mt-3 mt-md-0">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Analisa
                        </button>
                    </div>
                </div>
            </form>

            <div class="row mb-4">
                <div class="col-6 col-md-4 col-lg mb-3">
                    <div class="oppa-stat-card">
                        <div class="oppa-stat-label">Best Line</div>
                        <div class="oppa-stat-value text-success">{{ $bestLine }}</div>
                        <div class="oppa-stat-sub">{{ $first->tgl_trans ?? '-' }}</div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg mb-3">
                    <div class="oppa-stat-card">
                        <div class="oppa-stat-label">Best Output</div>
                        <div class="oppa-stat-value">{{ number_format($bestOutput) }}</div>
                        <div class="oppa-stat-sub">pcs / day</div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg mb-3">
                    <div class="oppa-stat-card">
                        <div class="oppa-stat-label">Best Efficiency</div>
                        <div class="oppa-stat-value">{{ $bestEfficiency }}%</div>
                        <div class="oppa-stat-sub">berdasarkan SMV &amp; manpower</div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg mb-3">
                    <div class="oppa-stat-card">
                        <div class="oppa-stat-label">Manpower Best Line</div>
                        <div class="oppa-stat-value">{{ $manPower }}</div>
                        <div class="oppa-stat-sub">operator</div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg mb-3">
                    <div class="oppa-stat-card">
                        <div class="oppa-stat-label">Output / Person</div>
                        <div class="oppa-stat-value">{{ $outputPerPerson }}</div>
                        <div class="oppa-stat-sub">pcs / person / day</div>
                    </div>
                </div>
            </div>

            {{-- <div class="card">
                <div class="card-header">
                    <h6 class="fw-bold mb-0">Perbandingan Output dan Efficiency per Line</h6>
                </div>
                <div class="card-body">
                    <div id="oppaChart"></div>
                    <small class="text-muted">Overall score menggabungkan output, efficiency, dan output/person.</small>
                </div>
            </div> --}}
        </div>
    </div>
@endsection

@section('custom-script')
    <!-- DataTables & Plugins -->
    {{-- <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script> --}}
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables 2.0/dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables 2.0/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables 2.0/dataTables.fixedColumns.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-rowsgroup/dataTables.rowsGroup.js') }}"></script>
    <script src="{{ asset('plugins/apexcharts/apexcharts.min.js') }}"></script>
    <script>
        // Select2 Autofocus
        $(document).on('select2:open', () => {
            document.querySelector('.select2-search__field').focus();
        });

        // Initialize Select2 Elements
        $('.select2').select2();

        // Initialize Select2BS4 Elements
        $('.select2bs4').select2({
            theme: 'bootstrap4',
            width: 'resolve' // Ensures it respects the 100% width from inline style or Bootstrap
        });
        // Now set height and font-size on the Select2 container after init
        $('.select2-container--bootstrap4 .select2-selection--single').css({
            'height': '30px', // your desired height
            'font-size': '12px', // your desired font size
            'line-height': '30px' // vertically center text
        });

        function notif() {
            alert("Maaf, Fitur belum tersedia!");
        }

        /* Chart: Output (bar) vs Efficiency (line) per line
        var oppaCategories = @json(array_column($chartLines, 'label'));
        var oppaOutput = @json(array_column($chartLines, 'output'));
        var oppaEfficiency = @json(array_column($chartLines, 'efficiency'));

        var oppaChartOptions = {
            chart: {
                height: 350,
                type: 'line',
                toolbar: {
                    show: false
                }
            },
            series: [{
                    name: 'Output (pcs)',
                    type: 'column',
                    data: oppaOutput
                },
                {
                    name: 'Efficiency (%)',
                    type: 'line',
                    data: oppaEfficiency
                }
            ],
            stroke: {
                width: [0, 3]
            },
            colors: ['#90caf9', '#e83e8c'],
            plotOptions: {
                bar: {
                    columnWidth: '45%'
                }
            },
            xaxis: {
                categories: oppaCategories
            },
            yaxis: [{
                    title: {
                        text: 'Output'
                    }
                },
                {
                    opposite: true,
                    title: {
                        text: 'Efficiency %'
                    }
                }
            ],
            dataLabels: {
                enabled: false
            }
        };

        var oppaChart = new ApexCharts(document.querySelector('#oppaChart'), oppaChartOptions);
        oppaChart.render();
        */
    </script>
@endsection
