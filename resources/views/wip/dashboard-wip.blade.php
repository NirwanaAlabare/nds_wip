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
            @foreach ($lines as $line)
                <div class="col-md-4 col-lg-3 mb-4">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body text-center">
                            <div class="d-flex justify-content-between align-items-center h-100 gap-3">
                                <h5 class="card-title fw-bold">{{ str_replace("SEWING ", "", $line->name) }}</h5>
                                <a href="{{ url('dashboard-wip/wip-line/' . $line->id) }}" class="btn btn-primary btn-sm">Details</a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
            <div class="col-md-4 col-lg-3 mb-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body text-center">
                        <h5 class="card-title fw-bold">CHIEF</h5>
                        <button type="button" data-bs-toggle="modal" data-bs-target="#chiefDashboard" class="btn btn-primary btn-sm">Details</button>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-lg-3 mb-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body text-center">
                        <h5 class="card-title fw-bold">CHIEF RANGE</h5>
                        <button type="button" data-bs-toggle="modal" data-bs-target="#chiefRangeDashboard" class="btn btn-primary btn-sm">Details</button>
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
                                    <select class="select2bs4chief" name="year" id="year">
                                        @foreach ($years as $y)
                                            <option value="{{ $y }}">{{ $y }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <select class="select2bs4chief" name="month" id="month">
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
                                    <input type="date" class="form-control" id="dateFrom" name="dateFrom" value="{{ date("Y-m-d") }}">
                                </div>
                                <div class="col-md-6">
                                    <input type="date" class="form-control" id="dateTo" name="dateTo" value="{{ date("Y-m-d") }}">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" onclick="visitDashboardChiefRange()">Visit <i class="fa fa-share"></i></button>
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
            window.open("{{ route("dashboard-chief-sewing") }}/"+$("#year").val()+"/"+$("#month").val(), '_blank');
            // window.open("http://10.10.5.62:8000/nds_wip/public/index.php/dashboard-wip/chief-sewing/"+$("#year").val()+"/"+$("#month").val(), '_blank');
        }

        function visitDashboardChiefRange() {
            window.open("{{ route("dashboard-chief-sewing-range") }}/"+$("#dateFrom").val()+"/"+$("#dateTo").val(), '_blank');
            // window.open("http://10.10.5.62:8000/nds_wip/public/index.php/dashboard-wip/chief-sewing/"+$("#year").val()+"/"+$("#month").val(), '_blank');
        }
    </script>
@endsection
