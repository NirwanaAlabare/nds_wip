@extends('layouts.index')

@section('custom-link')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">

    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@endsection

@section('content')
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title">
                <i class='fa fa-ticket'></i> Stocker Reject
            </h5>
        </div>
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-end gap-3 mb-3">
                <div class="d-flex align-items-end gap-3">
                    <div>
                        <label class="form-label">Dari</label>
                        <input type="date" class="form-control" id="dateFrom" onchange='stockerRejectTableReload()'>
                    </div>
                    <span class='mb-1'> - </span>
                    <div>
                        <label class="form-label">Sampai</label>
                        <input type="date" class="form-control" id="dateTo" onchange='stockerRejectTableReload()'>
                    </div>
                </div>
                <div>
                    <a type="button" href="{{ route('create-stocker-reject') }}" class='btn btn-success'><i class="fa fa-plus"></i> Baru</a>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered" id='stocker-reject-table'>
                    <thead>
                        <th>Action</th>
                        <th>Tanggal</th>
                        <th>Stocker</th>
                        <th>Proses</th>
                        <th>Qty Reject</th>
                    </thead>
                    <tbody></tbody>
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
        $('.select2').select2();
    </script>


    <script>
        // Initial
        $(document).ready(function () {
            let oneWeekBefore = getOneWeekBefore();
            let currentDate = getCurrentDate();
            $('#dateFrom').val(oneWeekBefore).trigger('change');
            $('#dateTo').val(currentDate).trigger('change');
        });

        // Stocker Reject Datatable
        let stockerRejectTable = $("#stocker-reject-table").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('stocker-reject') }}',
                dataType: 'json',
                dataSrc: 'data',
                data: function(d) {
                    d.dateFrom = $('#dateFrom').val();
                    d.dateTo = $('#dateTo').val();
                },
            },
            columns: [
                {
                    data: 'id',
                    searchable: false
                },
                {
                    data: 'tanggal'
                },
                {
                    data: 'id_qr_stocker'
                },
                {
                    data: 'proses'
                },
                {
                    data: 'qty_reject',
                }
            ],
            columnDefs: [
                // Act Column
                {
                    targets: [0],
                    render: (data, type, row, meta) => {
                        // conditional
                        return `<div class='d-flex gap-1 justify-content-center'> <a class='btn btn-primary btn-sm' href='{{ route("show-stocker-reject") }}/`+row.id+`/`+row.proses+`' data-bs-toggle='tooltip'><i class='fa fa-search-plus'></i></a> </div>`;
                    }
                },
                {
                    targets: [2],
                    render: (data, type, row, meta) => {
                        return `<span><b>`+row.id_qr_stocker+`</b>`+(row.id_qr_similar_stocker ? `, `+row.id_qr_similar_stocker : ``)+`</span>`
                    }
                },
                {
                    targets: '_all',
                    className: 'text-nowrap'
                }
            ]
        });

        // Stocker Reject Header Filter
        $('#stocker-reject-table thead tr').clone(true).appendTo('#stocker-reject-table thead');
        $('#stocker-reject-table thead tr:eq(1) th').each(function(i) {
            if (i != 0) {
                var title = $(this).text();
                $(this).html('<input type="text" class="form-control form-control-sm" style="width:100%"/>');

                $('input', this).on('keyup change', function() {
                    if (stockerRejectTable.column(i).search() !== this.value) {
                        stockerRejectTable
                            .column(i)
                            .search(this.value)
                            .draw();
                    }
                });
            } else {
                $(this).empty();
            }
        });

        // Stocker Reject Table Reload
        function stockerRejectTableReload() {
            stockerRejectTable.ajax.reload();
        }
    </script>
@endsection
