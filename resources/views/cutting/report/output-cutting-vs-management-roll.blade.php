@extends('layouts.index', ["containerFluid" => true])

@section('custom-link')
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables 2.0/jquery.dataTables.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables 2.0/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables 2.0/fixedColumns.bootstrap4.min.css') }}">
    <!-- jQuery -->
    <script src="{{ asset('plugins/datatables 2.0/jquery-3.3.1.js') }}"></script>

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
                <h5 class="card-title fw-bold text-sb text-center">
                    <i class="fa-solid fa-file"></i> Output Cutting vs Management Roll
                </h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label>Tanggal Dari</label>
                        <input type="date" id="dateFrom" class="form-control" value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="col-md-3">
                        <label>Tanggal Sampai</label>
                        <input type="date" id="dateTo" class="form-control" value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="button" id="btnFilter" class="btn btn-primary me-2">
                            <i class="fa fa-search"></i> Filter
                        </button>
                        <button type="button" id="btnExport" class="btn btn-success">
                            <i class="fa fa-file-excel"></i> Export
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="table-output-cutting-vs-management-roll" class="table table-bordered table-striped nowrap"
                        style="width:100%">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>No Form</th>
                                <th>Worksheet</th>
                                <th>Style</th>
                                <th>Color</th>
                                <th>Panel</th>
                                <th>Qty Output</th>
                                <th>Qty Manajemen Roll</th>
                                <th>Difference</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('custom-script')
    <!-- DataTables & Plugins -->
    <script src="{{ asset('plugins/datatables 2.0/dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables 2.0/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables 2.0/dataTables.fixedColumns.min.js') }}"></script>

    <script>
        let table = $('#table-output-cutting-vs-management-roll').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('output-cutting-vs-management-roll') }}",
                data: function(d) {
                    d.dateFrom = $('#dateFrom').val();
                    d.dateTo = $('#dateTo').val();
                }
            },
            columns: [
                { data: 'tanggal', name: 'tanggal' },
                { data: 'no_form', name: 'no_form' },
                { data: 'worksheet', name: 'worksheet' },
                { data: 'style', name: 'style' },
                { data: 'color', name: 'color' },
                { data: 'panel', name: 'panel' },
                { data: 'qty_output', name: 'qty_output' },
                { data: 'qty_manajemen', name: 'qty_manajemen' },
                {
                    data: 'difference',
                    name: 'difference',
                    render: function(data) {
                        const value = parseFloat(data) || 0;
                        const cls = value == 0 ? 'text-success' : 'text-danger';
                        return '<span class="' + cls + '">' + value + '</span>';
                    }
                },
            ]
        });

        $('#btnFilter').on('click', function() {
            table.ajax.reload();
        });

        $('#btnExport').on('click', function() {
            let dateFrom = $('#dateFrom').val();
            let dateTo = $('#dateTo').val();

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
                url: "{{ route('export-output-cutting-vs-management-roll') }}",
                data: {
                    dateFrom: dateFrom,
                    dateTo: dateTo
                },
                xhrFields: {
                    responseType: 'blob'
                },
                success: function(response) {
                    Swal.close();

                    var blob = new Blob([response]);
                    var link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = "Output Cutting vs Management Roll " + dateFrom + " _ " + dateTo + ".xlsx";
                    link.click();
                },
                error: function(xhr, status, error) {
                    Swal.close();
                    iziToast.error({
                        title: 'Gagal',
                        message: 'Terjadi kesalahan saat mengekspor data.',
                        position: 'topCenter',
                    });
                    console.error("Export failed:", { status, error, response: xhr.responseText });
                }
            });
        });
    </script>
@endsection
