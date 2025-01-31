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
    </style>
@endsection

@section('content')
    <div class="container my-4">
        <div class="row">
            @foreach ($lines as $line)
                <div class="col-md-4 col-lg-3 mb-4">
                    <div class="card shadow-sm border-0">
                        <div class="card-body text-center">
                            <h5 class="card-title fw-bold">{{ $line->name }}</h5>
                            <a href="{{ url('dashboard-wip/wip-line/' . $line->id) }}" class="btn btn-primary btn-sm">View Details</a>
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
    <!-- Page specific script -->
    <script>
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
    </script>
@endsection
