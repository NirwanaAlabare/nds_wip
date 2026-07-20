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
        // Cari baris top_rfts & top_eff dari $data
        $topRftRow = null;
        $topEffRow = null;
        foreach ($data as $row) {
            if ($row->top_rfts === 'Y') {
                $topRftRow = $row;
            }
            if ($row->top_eff === 'Y') {
                $topEffRow = $row;
            }
        }
        $sameLine = $topRftRow && $topEffRow && $topRftRow->sewing_line === $topEffRow->sewing_line;

        $first = $topRftRow ?? ($data[0] ?? null);
        $bestLine = $first->sewing_line ?? '-';
        $bestOutput = $first->tot_rfts ?? 0;
        $smv = $first->smv ?? 0;

        $effRow = $topEffRow ?? $first;
        $bestEfficiency = $effRow->eff ?? 0;
        $manPower = $effRow->man_power ?? 0;

        $firstManPower = $first->man_power ?? 0;
        $outputPerPerson = $firstManPower > 0 ? round($bestOutput / $firstManPower, 1) : 0;

        $fmtLineDate = function ($row) {
            if (!$row) {
                return ['line' => '-', 'date' => '-'];
            }
            $tgl = isset($row->tgl_trans)
                ? \Carbon\Carbon::parse($row->tgl_trans)->locale('id')->isoFormat('dddd, DD-MM-YYYY')
                : '-';
            return ['line' => $row->sewing_line ?? '-', 'date' => $tgl];
        };

        // Susun data chart Top RFT (kiri) vs Top Efficiency (kanan) dari $data

        $chartLines = [];
        if ($topRftRow) {
            $chartLines[] = [
                'label' => $topRftRow->sewing_line,
                'output' => (int) $topRftRow->tot_rfts,
                'efficiency' => (float) $topRftRow->eff,
            ];
        }
        if ($topEffRow && !$sameLine) {
            $chartLines[] = [
                'label' => $topEffRow->sewing_line,
                'output' => (int) $topEffRow->tot_rfts,
                'efficiency' => (float) $topEffRow->eff,
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
                        <select name="styleno" id="styleno" class="select2bs4" style="width:100%">
                            @if ($styleno)
                                <option value="{{ $styleno }}" selected>{{ $styleno }}</option>
                            @endif
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
                        @php($d = $fmtLineDate($first))
                        <div class="oppa-stat-sub">
                            {{ $d['line'] }}<br>{{ $d['date'] }}
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg mb-3">
                    <div class="oppa-stat-card">
                        <div class="oppa-stat-label">Best Output</div>
                        <div class="oppa-stat-value">{{ number_format($bestOutput) }}</div>
                        @php($d = $fmtLineDate($first))
                        <div class="oppa-stat-sub">
                            pcs / day<br>{{ $d['line'] }}<br>{{ $d['date'] }}
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg mb-3">
                    <div class="oppa-stat-card">
                        <div class="oppa-stat-label">Best Efficiency</div>
                        <div class="oppa-stat-value">{{ $bestEfficiency }}%</div>
                        @php($d = $fmtLineDate($effRow))
                        <div class="oppa-stat-sub">
                            {{ $d['line'] }}<br>{{ $d['date'] }}
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg mb-3">
                    <div class="oppa-stat-card">
                        <div class="oppa-stat-label">Manpower Best Efficiency</div>
                        <div class="oppa-stat-value">{{ $manPower }}</div>
                        @php($d = $fmtLineDate($effRow))
                        <div class="oppa-stat-sub">
                            operator<br>{{ $d['line'] }}<br>{{ $d['date'] }}
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg mb-3">
                    <div class="oppa-stat-card">
                        <div class="oppa-stat-label">Output / Person</div>
                        <div class="oppa-stat-value">{{ $outputPerPerson }}</div>
                        @php($d = $fmtLineDate($first))
                        <div class="oppa-stat-sub">
                            pcs / person / day<br>{{ $d['line'] }}<br>{{ $d['date'] }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h6 class="fw-bold mb-0">Top RFT vs Top Efficiency Line</h6>
                </div>
                <div class="card-body">
                    @if (count($chartLines) > 0)
                        <div id="oppaChart"></div>
                        <small class="text-muted">Kiri: total RFT (tot rft) | Kanan: efficiency (%). Jika line dengan tot
                            rft tertinggi sama dengan line efficiency tertinggi, hanya tampil 1 bar.</small>
                    @else
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-chart-bar fa-2x mb-2"></i>
                            <div>Belum ada data. Silakan cari style terlebih dahulu.</div>
                        </div>
                    @endif
                </div>
            </div>
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
        $('.select2bs4').not('#styleno').select2({
            theme: 'bootstrap4',
            width: 'resolve' // Ensures it respects the 100% width from inline style or Bootstrap
        });

        // Select2BS4 dengan AJAX suggest untuk Cari Style
        $('#styleno').select2({
            theme: 'bootstrap4',
            width: 'resolve',
            placeholder: 'Ketik style, contoh: GP016433',
            minimumInputLength: 2,
            allowClear: true,
            ajax: {
                url: '{{ route('IE_output_performance_styleno_suggest') }}',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        q: params.term
                    };
                },
                processResults: function(data) {
                    return data;
                },
                cache: true
            }
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

        // Bersihkan query string (?styleno=...) dari address bar setelah hasil tampil,
        // supaya kalau halaman di-refresh (F5), dashboard & card kembali kosong.
        if (window.location.search) {
            window.history.replaceState({}, document.title, window.location.pathname);
        }

        // Chart: Tot RFT (bar, kiri) vs Efficiency (line, kanan) - top_rft & top_eff line
        if (document.querySelector('#oppaChart')) {
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
                    name: 'tot rft',
                    type: 'column',
                    data: oppaOutput
                },
                {
                    name: 'eff (%)',
                    type: 'line',
                    data: oppaEfficiency
                }
            ],
            stroke: {
                width: [0, 3]
            },
            colors: ['#0d6efd', '#0b1f4d'],
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
                        text: 'tot rft'
                    }
                },
                {
                    opposite: true,
                    title: {
                        text: 'eff (%)'
                    }
                }
            ],
            dataLabels: {
                enabled: false
            }
        };

        var oppaChart = new ApexCharts(document.querySelector('#oppaChart'), oppaChartOptions);
        oppaChart.render();
        }
    </script>
@endsection
