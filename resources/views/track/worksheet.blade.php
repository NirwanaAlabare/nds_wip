@extends('layouts.index')

@section('custom-link')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">

    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

    <style>
        div.dataTables_wrapper div.dataTables_processing {
            top: 5%;
        }
    </style>
@endsection

@section('content')
    <div class="card card-sb">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title fw-bold mb-0"><i class="fas fa-receipt fa-sm"></i> Worksheet List</h5>
                <div>
                    <a href="{{ route('stocker') }}" class="btn btn-primary btn-sm">
                        <i class="fa fa-reply"></i> Kembali ke Stocker
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive w-100">
                <table class="table table-sm table-bordered W-100" id="ws-table">
                    <thead>
                        <tr>
                            <th>Worksheet</th>
                            <th>Style</th>
                            <th>Color</th>
                            <th>Size</th>
                            <th>Destination</th>
                            <th>Panel</th>
                            <th>Total Lembar Marker</th>
                            <th>Total Cut Marker</th>
                            <th>Total Lembar Form</th>
                            <th>Total Cut Form</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
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
            theme: 'bootstrap4',
            dropdownParent: $("#editMejaModal")
        })
    </script>

    <script>
        let wsTable = $("#ws-table").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            pageLength: 100,
            ajax: {
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route('track-ws') }}',
                dataType: 'json',
                dataSrc: 'data',
                data: function(d) {
                    d.dateFrom = $('#tgl-awal').val();
                    d.dateTo = $('#tgl-akhir').val();
                },
            },
            columns: [
                {
                    data: 'ws',
                },
                {
                    data: 'styleno',
                },
                {
                    data: 'color',
                },
                {
                    data: 'size',
                },
                {
                    data: 'dest',
                },
                {
                    data: 'panel',
                },
                {
                    data: 'total_gelar_marker',
                    searchable: false
                },
                {
                    data: 'total_cut_marker',
                    searchable: false
                },
                {
                    data: 'total_lembar_form',
                    searchable: false
                },
                {
                    data: 'total_cut_form',
                    searchable: false
                },
            ],
            columnDefs: [
                // Act Column
                // {
                //     targets: [0],
                //     render: (data, type, row, meta) => {
                //         return `<div class='d-flex gap-1 justify-content-center'> <a class='btn btn-primary btn-sm' href='{{ route("show-stocker") }}/`+row.form_cut_id+`' data-bs-toggle='tooltip'><i class='fa fa-search-plus'></i></a> </div>`;
                //     }
                // },
                // // No. Meja Column
                // {
                //     targets: [3],
                //     className: "text-nowrap",
                //     render: (data, type, row, meta) => data ? data.toUpperCase() : "-"
                // },
                // Text No Wrap
                {
                    targets: "_all",
                    className: "text-nowrap"
                }
            ]
        });

        $('#ws-table thead tr').clone(true).appendTo('#ws-table thead');
        $('#ws-table thead tr:eq(1) th').each(function(i) {
            if (i != 6 && i != 7 && i != 8 && i != 9) {
                var title = $(this).text();
                $(this).html('<input type="text" class="form-control form-control-sm" style="width:100%"/>');

                $('input', this).on('keyup change', function() {
                    if (wsTable.column(i).search() !== this.value) {
                        wsTable
                            .column(i)
                            .search(this.value)
                            .draw();
                    }
                });
            } else {
                $(this).empty();
            }
        });

        function dataTableReload() {
            wsTable.ajax.reload();
        }
    </script>
@endsection
