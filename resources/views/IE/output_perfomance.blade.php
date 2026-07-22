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

        .oppa-hero-card2 {
            border-radius: 1rem;
            padding: 1.5rem;
            height: 100%;
            background: linear-gradient(160deg, #eafcf1 0%, #ffffff 65%);
            border: 1px solid #d7f5e3;
        }

        .oppa-hero-card2.eff {
            background: linear-gradient(160deg, #eaf2ff 0%, #ffffff 65%);
            border: 1px solid #d7e6ff;
        }

        .oppa-hero-pill2 {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            background: #37b06a;
            color: #fff;
            padding: .4rem 1rem;
            border-radius: 2rem;
            font-weight: 700;
            font-size: .78rem;
            letter-spacing: .03em;
        }

        .oppa-hero-pill2.eff {
            background: #3d7ce0;
        }

        .oppa-hero-number2 {
            font-size: 2.4rem;
            font-weight: 800;
            line-height: 1;
        }

        .oppa-hero-number2.rft {
            color: #1f9d55;
        }

        .oppa-hero-number2.eff {
            color: #2f6fe0;
        }

        .oppa-hero-unit {
            font-size: .7rem;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: .05em;
            text-align: right;
        }

        .oppa-hero-line2 {
            font-size: 1.6rem;
            font-weight: 800;
            margin-top: .75rem;
        }

        .oppa-hero-line2.rft {
            color: #1f9d55;
        }

        .oppa-hero-line2.eff {
            color: #2f6fe0;
        }

        .oppa-hero-divider {
            border: none;
            border-top: 2px solid;
            opacity: .5;
            margin: .5rem 0 .85rem;
            width: 55%;
        }

        .oppa-hero-divider.rft {
            border-color: #37b06a;
        }

        .oppa-hero-divider.eff {
            border-color: #3d7ce0;
        }

        .oppa-hero-caption2 {
            font-size: .8rem;
            color: #6c757d;
            margin-bottom: .9rem;
        }

        .oppa-hero-row2 {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: .6rem .9rem;
            background: rgba(255, 255, 255, .65);
            border-radius: .6rem;
            margin-bottom: .5rem;
            font-size: .85rem;
        }

        .oppa-hero-row2 .left {
            display: flex;
            align-items: center;
            gap: .65rem;
            color: #495057;
        }

        .oppa-hero-row2 .icon {
            width: 1.7rem;
            height: 1.7rem;
            min-width: 1.7rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .72rem;
        }

        .oppa-hero-row2 .icon.rft {
            background: #d7f5e3;
            color: #1f9d55;
        }

        .oppa-hero-row2 .icon.eff {
            background: #d7e6ff;
            color: #2f6fe0;
        }

        .oppa-hero-row2 .value2 {
            font-weight: 700;
        }

        .oppa-hero-row2 .value2.rft {
            color: #1f9d55;
        }

        .oppa-hero-row2 .value2.eff {
            color: #2f6fe0;
        }

        .oppa-hero-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #f8f9fa;
            border-radius: .6rem;
            padding: .65rem 1.1rem;
            font-size: .78rem;
            color: #6c757d;
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

        $effOutput = $effRow->tot_rfts ?? 0;
        $effOutputPerPerson = $manPower > 0 ? round($effOutput / $manPower, 1) : 0;

        $firstJamKerjaAct = $first->jam_kerja_act ?? 0;
        $firstJamKerjaActMenit = round($firstJamKerjaAct * 60, 1);

        $effJamKerjaAct = $effRow->jam_kerja_act ?? 0;
        $effJamKerjaActMenit = round($effJamKerjaAct * 60, 1);

        $fmtLineDate = function ($row) {
            if (!$row) {
                return ['line' => '-', 'date' => '-', 'date_short' => '-'];
            }
            $carbon = isset($row->tgl_trans) ? \Carbon\Carbon::parse($row->tgl_trans) : null;
            return [
                'line' => $row->sewing_line ?? '-',
                'date' => $carbon ? $carbon->locale('id')->isoFormat('dddd, DD-MM-YYYY') : '-',
                'date_short' => $carbon ? $carbon->locale('id')->isoFormat('DD MMM YYYY') : '-',
            ];
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

            <div class="row mb-2">
                <div class="col-md-6 mb-3">
                    <div class="oppa-hero-card2 rft">
                        @php($d = $fmtLineDate($first))
                        <div class="d-flex justify-content-between align-items-start">
                            <span class="oppa-hero-pill2"><i class="fas fa-arrow-trend-up"></i> Top RFT</span>
                            <div class="text-right">
                                <div class="oppa-hero-number2 rft">{{ number_format($bestOutput) }}</div>
                                <div class="oppa-hero-unit">pcs</div>
                            </div>
                        </div>
                        <div class="oppa-hero-line2 rft">{{ $d['line'] }}</div>
                        <hr class="oppa-hero-divider rft">
                        <div class="oppa-hero-caption2">Total Output</div>

                        <div class="oppa-hero-row2">
                            <span class="left"><span class="icon rft"><i
                                        class="fas fa-calendar-alt"></i></span>Tanggal</span>
                            <span class="value2">{{ $d['date_short'] }}</span>
                        </div>
                        <div class="oppa-hero-row2">
                            <span class="left"><span class="icon rft"><i class="fas fa-users"></i></span>Operator</span>
                            <span class="value2 rft">{{ $firstManPower }} Orang</span>
                        </div>
                        <div class="oppa-hero-row2">
                            <span class="left"><span class="icon rft"><i class="fas fa-bolt"></i></span>Efficiency
                                (%)</span>
                            <span class="value2 rft">{{ $first->eff ?? 0 }} %</span>
                        </div>
                        <div class="oppa-hero-row2">
                            <span class="left"><span class="icon rft"><i class="fas fa-user"></i></span>Output /
                                Operator</span>
                            <span class="value2 rft">{{ $outputPerPerson }} pcs/orang</span>
                        </div>
                        <div class="oppa-hero-row2">
                            <span class="left"><span class="icon rft"><i class="fas fa-clock"></i></span>Jam Kerja
                                Act</span>
                            <span class="value2 rft">{{ $firstJamKerjaAct }} jam ({{ $firstJamKerjaActMenit }}
                                menit)</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="oppa-hero-card2 eff">
                        @php($d = $fmtLineDate($effRow))
                        <div class="d-flex justify-content-between align-items-start">
                            <span class="oppa-hero-pill2 eff"><i class="fas fa-arrow-trend-up"></i> Top
                                Efficiency</span>
                            <div class="text-right">
                                <div class="oppa-hero-number2 eff">{{ $bestEfficiency }}%</div>
                                <div class="oppa-hero-unit">efficiency</div>
                            </div>
                        </div>
                        <div class="oppa-hero-line2 eff">{{ $d['line'] }}</div>
                        <hr class="oppa-hero-divider eff">
                        <div class="oppa-hero-caption2">Total Output</div>

                        <div class="oppa-hero-row2">
                            <span class="left"><span class="icon eff"><i
                                        class="fas fa-calendar-alt"></i></span>Tanggal</span>
                            <span class="value2">{{ $d['date_short'] }}</span>
                        </div>
                        <div class="oppa-hero-row2">
                            <span class="left"><span class="icon eff"><i class="fas fa-users"></i></span>Operator</span>
                            <span class="value2 eff">{{ $manPower }} Orang</span>
                        </div>
                        <div class="oppa-hero-row2">
                            <span class="left"><span class="icon eff"><i class="fas fa-box"></i></span>Output
                                (pcs)</span>
                            <span class="value2 eff">{{ number_format($effOutput) }} pcs</span>
                        </div>
                        <div class="oppa-hero-row2">
                            <span class="left"><span class="icon eff"><i class="fas fa-user"></i></span>Output /
                                Operator</span>
                            <span class="value2 eff">{{ $effOutputPerPerson }} pcs/orang</span>
                        </div>
                        <div class="oppa-hero-row2">
                            <span class="left"><span class="icon eff"><i class="fas fa-clock"></i></span>Jam Kerja
                                Act</span>
                            <span class="value2 eff">{{ $effJamKerjaAct }} jam ({{ $effJamKerjaActMenit }}
                                menit)</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="oppa-hero-footer mb-4">
                <span><i class="fas fa-sync-alt"></i> Data diperbarui:
                    {{ $lastUpdated ? \Carbon\Carbon::parse($lastUpdated)->locale('id')->isoFormat('DD MMM YYYY HH:mm') : '-' }}</span>
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
                colors: [
                    function({
                        dataPointIndex
                    }) {
                        // chartLines[0] selalu baris top_rfts (kalau ada)
                        return dataPointIndex === 0 ? '#37b06a' : '#3d7ce0';
                    },
                    '#3d7ce0'
                ],
                plotOptions: {
                    bar: {
                        columnWidth: '45%',
                        distributed: true
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
