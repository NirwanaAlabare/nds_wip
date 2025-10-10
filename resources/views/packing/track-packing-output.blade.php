@extends('layouts.index', ["containerFluid" => true])

@section('custom-link')
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables 2.0/jquery.dataTables.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables 2.0/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables 2.0/fixedColumns.bootstrap4.min.css') }}">
    <!-- jQuery -->
    <script src="{{ asset('plugins/datatables 2.0/jquery-3.3.1.js') }}"></script>

    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

    <style>
        table.dataTable {
            margin: 0 !important;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="mx-3 my-3">
                <h5 class="card-title fw-bold text-sb text-center"><i class="fa-solid fa-shuffle"></i> Track Order Output</h5>
            </div>
            <div class="card-body">
                @livewire('packing.track-packing-output')
            </div>
        </div>
    </div>
@endsection

@section('custom-script')
    <!-- DataTables & Plugins -->
    <script src="{{ asset('plugins/datatables 2.0/dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables 2.0/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables 2.0/dataTables.fixedColumns.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-rowsgroup/dataTables.rowsGroup.js') }}"></script>

    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        $('.select2').select2({
            theme: 'bootstrap4',
        })

        $('.select2bs4').select2({
            theme: 'bootstrap4',
        })
    </script>
    <script>
        Livewire.on('loadingStart', () => {
            if (document.getElementById('loadingOrderOutput')) {
                $('#loadingOrderOutput').removeClass('hidden');
            }
        });

        Livewire.on('alert', (message) => {
            // Error Alert
            iziToast.warning({
                title: 'Info',
                message: message,
                position: 'topCenter',
                timeout: false,
                closeOnClick: true
            });

            alert("Loading Selesai!");
            // console.log("alert!");
        });
    </script>
@endsection
