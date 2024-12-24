@extends('layouts.index')

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
        /* Custom styles for the table */

        .table-bordered {

            border: 1px solid black;
            /* Change thickness of the outer border */

        }

        .table-bordered th,
        .table-bordered td {

            border: 1px solid black;
            /* Change thickness of inner borders */

        }
    </style>
@endsection

@section('content')
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-list"></i> Report Mutasi Packing (WIP)</h5>
        </div>
        <div class="card-body">
            <div class="d-flex align-items-end gap-3 mb-3">
                <div class="mb-3">
                    <label class="form-label"><small><b>Tgl Awal</b></small></label>
                    <input type="date" class="form-control form-control-sm " id="tgl-awal" name="tgl_awal"
                        value="{{ date('Y-m-d') }}">
                </div>
                <div class="mb-3">
                    <label class="form-label"><small><b>Tgl Akhir</b></small></label>
                    <input type="date" class="form-control form-control-sm" id="tgl-akhir" name="tgl_akhir"
                        value="{{ date('Y-m-d') }}">
                </div>
                <div class="mb-3">
                    <a onclick="dataTableReload()" class="btn btn-outline-primary btn-sm position-relative">
                        <i class="fas fa-search fa-xs"></i> <!-- Use fa-xs for extra small icon -->
                    </a>
                </div>
                <div class="mb-3">
                    <a onclick="export_excel_trf_garment()" class="btn btn-outline-success position-relative btn-sm">
                        <i class="fas fa-file-excel fa-sm"></i>
                        Export Excel
                    </a>
                </div>
            </div>

            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-striped table-sm w-100 text-nowrap">
                    <thead>
                        <tr style='text-align:center; vertical-align:middle'>
                            <th colspan="5" style="background-color: lightblue; text-align:center;">Jenis Produk</th>
                            <th colspan="11" style="background-color: lightgreen; text-align:center;">Packing Line</th>
                            <th colspan="4" style="background-color: lightsteelblue; text-align:center;">Transfer Garment
                            </th>
                            <th colspan="4" style="background-color: lightgoldenrodyellow; text-align:center;">Packing
                                Central</th>
                        </tr>
                        <tr style='text-align:center; vertical-align:middle'>
                            <th style="background-color: lightblue;">WS</th>
                            <th style="background-color: lightblue;">Buyer</th>
                            <th style="background-color: lightblue;">Style</th>
                            <th style="background-color: lightblue;">Color</th>
                            <th style="background-color: lightblue;">Size</th>
                            <th style="background-color: lightgreen;">Saldo Awal</th>
                            <th style="background-color: lightgreen;">Terima Dari Steam</th>
                            <th style="background-color: lightgreen;">Rework Sewing</th>
                            <th style="background-color: lightgreen;">Rework Mending</th>
                            <th style="background-color: lightgreen;">Rework Spot Cleaning</th>
                            <th style="background-color: lightgreen;">Defect Sewing</th>
                            <th style="background-color: lightgreen;">Defect Mending</th>
                            <th style="background-color: lightgreen;">Defect Spot Cleaning</th>
                            <th style="background-color: lightgreen;">Reject</th>
                            <th style="background-color: lightgreen;">Keluar</th>
                            <th style="background-color: lightgreen;">Saldo Akhir</th>
                            <th style="background-color: lightsteelblue;">Saldo Awal</th>
                            <th style="background-color: lightsteelblue;">Terima</th>
                            <th style="background-color: lightsteelblue;">Keluar</th>
                            <th style="background-color: lightsteelblue;">Saldo Akhir</th>
                            <th style="background-color: lightgoldenrodyellow;">Saldo Awal</th>
                            <th style="background-color: lightgoldenrodyellow;">Terima</th>
                            <th style="background-color: lightgoldenrodyellow;">Keluar</th>
                            <th style="background-color: lightgoldenrodyellow;">Saldo Akhir</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('custom-script')
    <!-- DataTables & Plugins -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables 2.0/dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables 2.0/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables 2.0/dataTables.fixedColumns.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-rowsgroup/dataTables.rowsGroup.js') }}"></script>
    <script>
        function notif() {
            alert("Maaf, Fitur belum tersedia!");
        }
    </script>
    <script>
        function dataTableReload() {
            // Re-initialize the DataTable
            let tglawal = $('#tgl-awal').val();
            let tglakhir = $('#tgl-akhir').val();
            let dateFrom = tglawal + ' 00:00:00';
            let dateTo = tglakhir + ' 23:59:59';
            datatable = $("#datatable").DataTable({
                processing: true,
                serverSide: true,
                scrollY: "450px",
                scrollX: true,
                scrollCollapse: true,
                deferRender: true,
                paging: true,
                ordering: false,
                fixedColumns: {
                    leftColumns: 5 // Fix the first three columns
                },
                ajax: {
                    url: '{{ route('packing_rep_packing_mutasi_wip') }}',
                    data: function(d) {
                        d.dateFrom = dateFrom;
                        d.dateTo = dateTo;
                    },
                },
                columns: [{
                        data: 'kpno'
                    },
                    {
                        data: 'buyer'
                    },
                    {
                        data: 'styleno'
                    },
                    {
                        data: 'color'
                    },
                    {
                        data: 'size'
                    },
                    {
                        data: 'sa_pck_line_awal'
                    },
                    {
                        data: 'qty_in_pck_line'
                    },
                    {
                        data: 'input_rework_sewing'
                    },
                    {
                        data: 'input_rework_mending'
                    },
                    {
                        data: 'input_rework_spotcleaning'
                    },
                    {
                        data: 'output_def_sewing'
                    },
                    {
                        data: 'output_def_mending'
                    },
                    {
                        data: 'output_def_spotcleaning'
                    },
                    {
                        data: 'qty_reject'
                    },
                    {
                        data: 'qty_trf_gmt'
                    },
                    {
                        data: 'saldo_akhir_pck_line'
                    },
                    {
                        data: 'sa_trf_gmt'
                    },
                    {
                        data: 'qty_trf_gmt_in'
                    },
                    {
                        data: 'qty_trf_gmt_out'
                    },
                    {
                        data: 'saldo_akhir_trf_gmt'
                    },
                    {
                        data: 'sa_pck_in'
                    },
                    {
                        data: 'qty_pck_in'
                    },
                    {
                        data: 'qty_pck_out'
                    },
                    {
                        data: 'saldo_akhir_packing_central'
                    },

                ],
                columnDefs: [{
                    "className": "align-middle",
                    "targets": "_all"
                }],

            });
        }







        // let datatable = $("#datatable").DataTable({
        //     ordering: false,
        //     processing: true,
        //     serverSide: true,
        //     paging: false,
        //     searching: true,
        //     scrollY: '300px',
        //     scrollX: '300px',
        //     scrollCollapse: true,
        //     ajax: {
        //         url: '{{ route('packing_rep_packing_mutasi_wip') }}',
        //         data: function(d) {
        //             d.dateFrom = $('#tgl-awal').val();
        //             d.dateTo = $('#tgl-akhir').val();
        //         },
        //     },
        //     columns: [{
        //             data: 'kpno'

        //         }, {
        //             data: 'buyer'
        //         },
        //         {
        //             data: 'styleno'
        //         },
        //         {
        //             data: 'color'
        //         },
        //         {
        //             data: 'size'
        //         },
        //         {
        //             data: 'sa_pck_line'
        //         },
        //         {
        //             data: 'qty_in'
        //         },
        //         {
        //             data: 'input_rework_sewing'
        //         },
        //         {
        //             data: 'input_rework_spotcleaning'
        //         },
        //         {
        //             data: 'input_rework_mending'
        //         },
        //         {
        //             data: 'output_def_sewing'
        //         },
        //         {
        //             data: 'output_def_spotcleaning'
        //         },
        //         {
        //             data: 'output_def_mending'
        //         },
        //         {
        //             data: 'qty_reject'
        //         },
        //         {
        //             data: 'sa_trf_garment'
        //         },
        //         {
        //             data: 'qty_pck_in'
        //         },
        //         {
        //             data: 'sa_pck_in'
        //         },
        //         {
        //             data: 'qty_pck_out'
        //         },
        //         {
        //             data: 'qty_pck_out'
        //         },
        //         {
        //             data: 'qty_pck_out'
        //         },
        //         {
        //             data: 'qty_pck_out'
        //         },
        //         {
        //             data: 'qty_pck_out'
        //         },
        //         {
        //             data: 'qty_pck_out'
        //         },
        //         {
        //             data: 'qty_pck_out'
        //         },

        //     ],

        //     columnDefs: [{
        //         "className": "align-left",
        //         "targets": "_all"
        //     }, ]

        // });


        function export_excel_trf_garment() {
            let from = document.getElementById("tgl-awal").value;
            let to = document.getElementById("tgl-akhir").value;

            Swal.fire({
                title: 'Please Wait...',
                html: 'Exporting Data...',
                didOpen: () => {
                    Swal.showLoading()
                },
                allowOutsideClick: false,
            });

            $.ajax({
                type: "get",
                url: '{{ route('export_excel_trf_garment') }}',
                data: {
                    from: from,
                    to: to
                },
                xhrFields: {
                    responseType: 'blob'
                },
                success: function(response) {
                    {
                        swal.close();
                        Swal.fire({
                            title: 'Data Sudah Di Export!',
                            icon: "success",
                            showConfirmButton: true,
                            allowOutsideClick: false
                        });
                        var blob = new Blob([response]);
                        var link = document.createElement('a');
                        link.href = window.URL.createObjectURL(blob);
                        link.download = "Laporan Trf Garment " + from + " sampai " +
                            to + ".xlsx";
                        link.click();

                    }
                },
            });
        }
    </script>
@endsection
