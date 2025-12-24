@extends('layouts.index')

@section('custom-link')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">

    <!-- jQuery -->
    <script src="{{ asset('plugins/datatables 2.0/jquery-3.3.1.js') }}"></script>

    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@endsection

@section('content')
    <!-- Master Table -->
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-list"></i> Loading Out / WIP Out</h5>
        </div>
        <div class="card-body">
            <div class="d-flex justify-content-between mb-3">
                <a href="{{ route('input_loading_out') }}" target="_blank"
                    class="btn btn-outline-primary position-relative btn-sm">
                    <i class="fas fa-plus"></i>
                    New
                </a>
            </div>

            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-hover align-middle text-nowrap w-100">
                    <thead class="bg-sb">
                        <tr>
                            <th class="text-center">No. Trans</th>
                            <th class="text-center">Tgl Pengeluaran</th>
                            <th class="text-center">PO</th>
                            <th class="text-center">Supplier</th>
                            <th class="text-center">Buyer</th>
                            <th class="text-center">Worksheet</th>
                            <th class="text-center">Style</th>
                            <th class="text-center">Color</th>
                            <th class="text-center">Size</th>
                            <th class="text-center">Qty</th>
                            <th class="text-center">Jenis Pengeluaran</th>
                            <th class="text-center">User</th>
                            <th class="text-center">Act</th>
                        </tr>

                    </thead>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('custom-script')
    <!-- DataTables & Select2 -->
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>

    <script>
        // Select2 Initialization
        $(document).ready(function() {
            $('.select2').select2();
            $('.select2bs4').select2({
                theme: 'bootstrap4',
                width: 'resolve'
            });
        });
        // Datatable
        let datatable = $("#datatable").DataTable({
            ordering: false,
            processing: true,
            serverSide: false,
            paging: true,
            searching: true,
            scrollY: "600px",
            scrollCollapse: true,
            scrollX: true,
        });

        function dataTableReload() {
            datatable.ajax.reload();
        }
    </script>
@endsection
