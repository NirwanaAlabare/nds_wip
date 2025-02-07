@extends('layouts.index', ["navbar" => false, "footer" => false, "containerFluid" => true])

@section('custom-link')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">

    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

    <style>
        .dataTables_wrapper .dataTables_processing {
            position: absolute;
            top: 50px !important;
            /* background: #FFFFCC;
            border: 1px solid black; */
            border-radius: 3px;
            font-weight: bold;
        }
    </style>
@endsection

@section('content')
    <input type="date" class="form-control" value="{{ date("Y-m-d") }}" id="tanggal">
    <div class="card">
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th rowspan="2" colspan="2">Chief Daily Efficiency & RFT February</th>
                        <th colspan="2">Before</th>
                        <th colspan="2">Yesterday</th>
                        <th colspan="2">Today</th>
                        <th rowspan="2">Rank</th>
                    </tr>
                    <tr>
                        <th>Effy</th>
                        <th>RFT</th>
                        <th>Effy</th>
                        <th>RFT</th>
                        <th>Effy</th>
                        <th>RFT</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Profil</td>
                        <td>Chart</td>
                        <td>0.0%</td>
                        <td>0.0%</td>
                        <td>0.0%</td>
                        <td>0.0%</td>
                        <td>0.0%</td>
                        <td>0.0%</td>
                        <td>#1</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
@endsection

@section('custom-script')
    <!-- DataTables & Plugins -->
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>

    <!-- Select2 -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        $('.select2').select2()
        $('.select2bs4').select2({
            theme: 'bootstrap4'
        })

        $('document').ready(() => {
            $.ajax({
                url: "{{ route("dashboard-chief-sewing") }}",
                type: "get",
                data: {
                    tanggal: $("#tanggal").val()
                },
                dataType: "json",
                success: function (response) {
                    console.log(response);
                },
                error: function (jqXHR) {
                    console.error(jqXHR);
                }
            });
        });
    </script>
@endsection
