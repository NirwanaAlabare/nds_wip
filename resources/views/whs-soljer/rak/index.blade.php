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
                <h4 class="text-sb fw-bold mb-3">Rak Dashboard</h4>
                <hr style="color: #222">
            </div>
            @foreach ($data as $row)
                <div class="col-md-4 col-lg-3 mb-4">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body text-center">
                            <div class="d-flex justify-content-between align-items-center h-100 gap-3">
                                <h5 class="card-title fw-bold">{{ $row->kode_lok }}</h5>
                                <a href="{{ url('dashboard-rak/detail/' . $row->kode_lok) }}" class="btn btn-primary btn-sm" target="_blank">Details</a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
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
    </script>
@endsection
