@if (!isset($page))
    @php
        $page = '';
    @endphp
@endif

@extends('layouts.index', ["page" => $page])

@section('custom-link')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">

    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

    <style>
        .card {
            border-radius: 12px;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        .btn-primary {
            background-color: #5A67D8;
            border: none;
        }

        .btn-primary:hover {
            background-color: #434190;
        }

        table thead th {
            vertical-align: middle;
            text-align: center;
        }
    </style>
@endsection

@section('content')
    <div class="container my-4">
        <div class="row justify-content-start align-items-center">
            <div class="col-12">
                <h4 class="text-sb fw-bold mb-3">Sewing Line Dashboard</h4>
                <hr style="color: #222">
            </div>
            @foreach ($lines as $line)
                <div class="col-md-4 col-lg-3 mb-4">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body text-center">
                            <div class="d-flex justify-content-between align-items-center h-100 gap-3">
                                <h5 class="card-title fw-bold">{{ str_replace("SEWING ", "", $line->name) }}</h5>
                                <a href="{{ url('dashboard-wip/wip-line/' . $line->id) }}" class="btn btn-primary btn-sm" target="_blank">Details</a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
            <div class="col-12">
                <h4 class="text-sb fw-bold mb-3">Sewing Team Dashboard</h4>
                <hr style="color: #222">
            </div>
            <div class="col-md-4 col-lg-3 mb-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body text-center">
                        <h5 class="card-title fw-bold">CHIEF</h5>
                        <button type="button" data-bs-toggle="modal" data-bs-target="#chiefDashboard" class="btn btn-primary btn-sm">Details</button>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-lg-3 mb-4 d-none">
                <div class="card shadow-sm border-0">
                    <div class="card-body text-center">
                        <h5 class="card-title fw-bold">CHIEF RANGE</h5>
                        <button type="button" data-bs-toggle="modal" data-bs-target="#chiefRangeDashboard" class="btn btn-primary btn-sm">Details</button>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-lg-3 mb-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body text-center">
                        <h5 class="card-title fw-bold">LINE SUPPORT</h5>
                        <button type="button" data-bs-toggle="modal" data-bs-target="#lineSupportDashboard" class="btn btn-primary btn-sm">Details</button>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-lg-3 mb-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body text-center">
                        <div class="d-flex justify-content-between align-items-center h-100 gap-3">
                            <h5 class="card-title fw-bold">RANKING CHIEF LEADER</h5>
                            <a href="{{ route('dashboard-chief-leader-sewing') }}" target="_blank" class="btn btn-primary btn-sm">Details</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <h4 class="text-sb fw-bold mb-3">Sewing Factory Dashboard</h4>
                <hr style="color: #222">
            </div>
            <div class="col-md-4 col-lg-3 mb-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body text-center">
                        <div class="d-flex justify-content-between align-items-center h-100 gap-3">
                            <h5 class="card-title fw-bold">FACTORY DASHBOARD</h5>
                            <a href="http://10.10.5.62:8000/dashboard-wip/factory" target="_blank" class="btn btn-primary btn-sm">Details</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-lg-3 mb-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body text-center">
                        <div class="d-flex justify-content-between align-items-center h-100 gap-3">
                            <h5 class="card-title fw-bold">FACTORY DEFECT DASHBOARD</h5>
                            <a href="http://10.10.5.62:8000/dashboard-wip/factory_defect" target="_blank" class="btn btn-primary btn-sm">Details</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-lg-3 mb-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body text-center">
                        <div class="d-flex justify-content-between align-items-center h-100 gap-3">
                            <h5 class="card-title fw-bold">FACTORY DAILY PERFORMANCE</h5>
                            <button type="button" data-bs-toggle="modal" data-bs-target="#factoryDailyPerformance" class="btn btn-primary btn-sm">Details</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <h4 class="text-sb fw-bold mb-3">Employee of the Month Dashboard</h4>
                <hr style="color: #222">
            </div>
            <div class="col-md-4 col-lg-3 mb-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body text-center">
                        <div class="d-flex justify-content-between align-items-center h-100 gap-3">
                            <h5 class="card-title fw-bold">CHIEF OF THE MONTH</h5>
                            <button type="button" data-bs-toggle="modal" data-bs-target="#topChiefDashboard" class="btn btn-primary btn-sm">Details</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-lg-3 mb-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body text-center">
                        <div class="d-flex justify-content-between align-items-center h-100 gap-3">
                            <h5 class="card-title fw-bold">LEADER OF THE MONTH</h5>
                            <button type="button" data-bs-toggle="modal" data-bs-target="#topLeaderDashboard" class="btn btn-primary btn-sm">Details</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal -->
            <div class="modal fade" id="chiefDashboard" tabindex="-1" aria-labelledby="chiefDashboardLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="chiefDashboardLabel">Visit Dashboard Chief</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <select class="select2bs4chief" name="chief-year" id="chief-year">
                                        @foreach ($years as $y)
                                            <option value="{{ $y }}">{{ $y }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <select class="select2bs4chief" name="chief-month" id="chief-month">
                                        @foreach ($months as $m)
                                            <option value="{{ $m['angka'] }}" {{ $m['angka'] == date("m") ? "selected" : "" }}>{{ $m['nama'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" onclick="visitDashboardChief()">Visit <i class="fa fa-share"></i></button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal -->
            <div class="modal fade" id="chiefRangeDashboard" tabindex="-1" aria-labelledby="chiefRangeDashboardLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="chiefRangeDashboardLabel">Visit Dashboard Chief Range</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <input type="date" class="form-control" id="chief-range-dateFrom" name="chief-range-dateFrom" value="{{ date("Y-m-d") }}">
                                </div>
                                <div class="col-md-6">
                                    <input type="date" class="form-control" id="chief-range-dateTo" name="chief-range-dateTo" value="{{ date("Y-m-d") }}">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" onclick="visitDashboardChiefRange()">Visit <i class="fa fa-share"></i></button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal -->
            <div class="modal fade" id="lineSupportDashboard" tabindex="-1" aria-labelledby="lineSupportDashboardLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="lineSupportDashboardLabel">Visit Line Support Range</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <select class="select2bs4linesupport" name="line-support-year" id="line-support-year">
                                        @foreach ($years as $y)
                                            <option value="{{ $y }}">{{ $y }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <select class="select2bs4linesupport" name="line-support-month" id="line-support-month">
                                        @foreach ($months as $m)
                                            <option value="{{ $m['angka'] }}" {{ $m['angka'] == date("m") ? "selected" : "" }}>{{ $m['nama'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" onclick="visitDashboardSupportLine()">Visit <i class="fa fa-share"></i></button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal -->
            <div class="modal fade" id="factoryDailyPerformance" tabindex="-1" aria-labelledby="factoryDailyPerformanceLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="factoryDailyPerformanceLabel">Visit Factory Daily Range</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <select class="select2bs4factorydaily" name="factory-daily-year" id="factory-daily-year">
                                        @foreach ($years as $y)
                                            <option value="{{ $y }}">{{ $y }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <select class="select2bs4factorydaily" name="factory-daily-month" id="factory-daily-month">
                                        @foreach ($months as $m)
                                            <option value="{{ $m['angka'] }}" {{ $m['angka'] == date("m") ? "selected" : "" }}>{{ $m['nama'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" onclick="visitFactoryDailyPerformance()">Visit <i class="fa fa-share"></i></button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal -->
            <div class="modal fade" id="topChiefDashboard" tabindex="-1" aria-labelledby="topChiefDashboardLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="topChiefDashboardLabel">Visit Dashboard Chief</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <select class="select2bs4topchief" name="topchief-year" id="topchief-year">
                                        @foreach ($years as $y)
                                            <option value="{{ $y }}">{{ $y }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <select class="select2bs4topchief" name="topchief-month" id="topchief-month">
                                        @foreach ($months as $m)
                                            <option value="{{ $m['angka'] }}" {{ $m['angka'] == date("m") ? "selected" : "" }}>{{ $m['nama'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" onclick="visitDashboardTopChief()">Visit <i class="fa fa-share"></i></button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal -->
            <div class="modal fade" id="topLeaderDashboard" tabindex="-1" aria-labelledby="topLeaderDashboardLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="topLeaderDashboardLabel">Visit Dashboard Leader</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <select class="select2bs4topleader" name="topleader-year" id="topleader-year">
                                        @foreach ($years as $y)
                                            <option value="{{ $y }}">{{ $y }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <select class="select2bs4topleader" name="topleader-month" id="topleader-month">
                                        @foreach ($months as $m)
                                            <option value="{{ $m['angka'] }}" {{ $m['angka'] == date("m") ? "selected" : "" }}>{{ $m['nama'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" onclick="visitDashboardTopLeader()">Visit <i class="fa fa-share"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('custom-script')
    <!-- DataTables  & Plugins -->
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>

    <!-- Select2 -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>

    <!-- Page specific script -->
    <script>
        $('.select2').select2()
        $('.select2bs4').select2({
            theme: 'bootstrap4'
        })
        $('.select2bs4chief').select2({
            theme: 'bootstrap4',
            dropdownParent: $('#chiefDashboard')
        })
        $('.select2bs4linesupport').select2({
            theme: 'bootstrap4',
            dropdownParent: $('#lineSupportDashboard')
        })
        $('.select2bs4factorydaily').select2({
            theme: 'bootstrap4',
            dropdownParent: $('#factoryDailyPerformance')
        })
        $('.select2bs4topchief').select2({
            theme: 'bootstrap4',
            dropdownParent: $('#topChiefDashboard')
        })
        $('.select2bs4topleader').select2({
            theme: 'bootstrap4',
            dropdownParent: $('#topLeaderDashboard')
        })

        $(function () {
            $("#datatable").DataTable({
                "responsive": true, "autoWidth": false,
            });

            $("#datatable-1").DataTable({
                "responsive": true, "autoWidth": false,
            })

            $("#datatable-2").DataTable({
                "responsive": true, "autoWidth": false,
            })

            $("#datatable-3").DataTable({
                "responsive": true, "autoWidth": false,
            })

            $("#datatable-4").DataTable({
                "responsive": true, "autoWidth": false,
            })
        });

        function visitDashboardChief() {
            window.open("{{ route("dashboard-chief-sewing") }}/"+$("#chief-year").val()+"/"+$("#chief-month").val(), '_blank');
            // window.open("http://10.10.5.62:8000/nds_wip/public/index.php/dashboard-wip/chief-sewing/"+$("#year").val()+"/"+$("#month").val(), '_blank');
        }

        function visitDashboardChiefRange() {
            window.open("{{ route("dashboard-chief-sewing-range") }}/"+$("#chief-range-dateFrom").val()+"/"+$("#chief-range-dateTo").val(), '_blank');
            // window.open("http://10.10.5.62:8000/nds_wip/public/index.php/dashboard-wip/chief-sewing/"+$("#year").val()+"/"+$("#month").val(), '_blank');
        }

        function visitDashboardSupportLine() {
            window.open("{{ route("dashboard-support-line-sewing") }}/"+$("#line-support-year").val()+"/"+$("#line-support-month").val(), '_blank');
            // window.open("http://10.10.5.62:8000/nds_wip/public/index.php/dashboard-wip/chief-sewing/"+$("#year").val()+"/"+$("#month").val(), '_blank');
        }

        function visitFactoryDailyPerformance() {
            window.open("{{ route("dashboard-factory-daily-sewing") }}/"+$("#factory-daily-year").val()+"/"+$("#factory-daily-month").val(), '_blank');
            // window.open("http://10.10.5.62:8000/nds_wip/public/index.php/dashboard-wip/chief-sewing/"+$("#year").val()+"/"+$("#month").val(), '_blank');
        }

        function visitDashboardTopChief() {
            window.open("{{ route("dashboard-top-chief-sewing") }}/"+$("#topchief-year").val()+"/"+$("#topchief-month").val(), '_blank');
            // window.open("http://10.10.5.62:8000/nds_wip/public/index.php/dashboard-wip/chief-sewing/"+$("#year").val()+"/"+$("#month").val(), '_blank');
        }

        function visitDashboardTopLeader() {
            window.open("{{ route("dashboard-top-leader-sewing") }}/"+$("#topleader-year").val()+"/"+$("#topleader-month").val(), '_blank');
            // window.open("http://10.10.5.62:8000/nds_wip/public/index.php/dashboard-wip/chief-sewing/"+$("#year").val()+"/"+$("#month").val(), '_blank');
        }
    </script>
@endsection
