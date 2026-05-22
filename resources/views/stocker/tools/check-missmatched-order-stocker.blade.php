@extends('layouts.index')

@section('custom-link')
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
    <style>
        #filter-row th { padding: 4px 4px; }
        #filter-row input,
        #filter-row select { min-width: 60px; }
    </style>
@endsection

@section('content')
    <div class="d-flex justify-content-between mb-3">
        <h5 class="fw-bold text-sb"><i class="fa-solid fa-triangle-exclamation"></i> Cek Stocker dengan Order yang Tidak Cocok</h5>
        <a href="{{ route('stocker-tools') }}" class="btn btn-primary btn-sm px-1 py-1"><i class="fas fa-reply"></i> Kembali ke Tools</a>
    </div>

    <div class="card card-sb">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title fw-bold mb-0">Stocker dengan Order yang Tidak Cocok</h5>
            <span class="badge bg-danger" id="total-badge">0 data</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm" id="datatable" style="width:100%">
                    <thead>
                        <tr class="text-center">
                            <th class="text-nowrap">No</th>
                            <th class="text-nowrap">Created At</th>
                            <th class="text-nowrap">ID QR Stocker</th>
                            <th class="text-nowrap">No. Form</th>
                            <th class="text-nowrap">So Det ID</th>
                            <th class="text-nowrap">Notes</th>
                            <th class="text-nowrap">Cancel</th>
                            <th class="text-nowrap">Stocker WS</th>
                            <th class="text-nowrap">Stocker Color</th>
                            <th class="text-nowrap">Stocker Size</th>
                            <th class="text-nowrap">Stocker Actual WS</th>
                            <th class="text-nowrap">Stocker Actual Color</th>
                            <th class="text-nowrap">Stocker Actual Size</th>
                            <th class="text-nowrap">Marker WS</th>
                            <th class="text-nowrap">Marker Color</th>
                        </tr>
                        <tr id="filter-row">
                            <th></th>
                            <th></th>
                            <th><input type="text" class="form-control form-control-sm col-filter" data-col="2" placeholder="Cari..."></th>
                            <th><input type="text" class="form-control form-control-sm col-filter" data-col="3" placeholder="Cari..."></th>
                            <th><input type="text" class="form-control form-control-sm col-filter" data-col="4" placeholder="Cari..."></th>
                            <th><input type="text" class="form-control form-control-sm col-filter" data-col="5" placeholder="Cari..."></th>
                            <th>
                                <select class="form-control form-control-sm col-filter" data-col="6">
                                    <option value="">Semua</option>
                                    <option value="Y">Y</option>
                                </select>
                            </th>
                            <th><input type="text" class="form-control form-control-sm col-filter" data-col="7" placeholder="Cari..."></th>
                            <th><input type="text" class="form-control form-control-sm col-filter" data-col="8" placeholder="Cari..."></th>
                            <th><input type="text" class="form-control form-control-sm col-filter" data-col="9" placeholder="Cari..."></th>
                            <th><input type="text" class="form-control form-control-sm col-filter" data-col="10" placeholder="Cari..."></th>
                            <th><input type="text" class="form-control form-control-sm col-filter" data-col="11" placeholder="Cari..."></th>
                            <th><input type="text" class="form-control form-control-sm col-filter" data-col="12" placeholder="Cari..."></th>
                            <th><input type="text" class="form-control form-control-sm col-filter" data-col="13" placeholder="Cari..."></th>
                            <th><input type="text" class="form-control form-control-sm col-filter" data-col="14" placeholder="Cari..."></th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('custom-script')
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>

    <script>
        var table;
        $(document).ready(function () {
            table = $('#datatable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('check-missmatched-order-stocker-list') }}',
                    type: 'POST',
                    data: { _token: '{{ csrf_token() }}' },
                    dataSrc: function (json) {
                        $('#total-badge').text(json.recordsTotal + ' data');
                        return json.data;
                    }
                },
                columns: [
                    { data: null, render: function (d, t, r, m) { return m.row + 1; }, orderable: false, searchable: false, className: 'text-center' },
                    { data: 'created_at', className: 'text-center text-nowrap', searchable: false, render: function (d) {
                        return d ? formatDateTime(d) : '-';
                    }},
                    { data: 'id_qr_stocker', defaultContent: '-' },
                    { data: 'no_form', defaultContent: '-' },
                    { data: 'so_det_id', defaultContent: '-', className: 'text-center' },
                    { data: 'notes', className: 'text-nowrap', defaultContent: '-' },
                    { data: 'cancel', defaultContent: '-', className: 'text-center', render: function (d) {
                        return d ? '<span class="badge bg-danger">' + d + '</span>' : '<span class="badge bg-secondary">NULL</span>';
                    }},
                    { data: 'stocker_ws', className: 'text-nowrap', defaultContent: '-' },
                    { data: 'stocker_color', className: 'text-nowrap', defaultContent: '-' },
                    { data: 'stocker_size', className: 'text-nowrap', defaultContent: '-' },
                    { data: 'actual_stocker_ws', className: 'text-nowrap', defaultContent: '-' },
                    { data: 'actual_stocker_color', className: 'text-nowrap', defaultContent: '-' },
                    { data: 'actual_stocker_size', className: 'text-nowrap', defaultContent: '-' },
                    { data: 'marker_ws', className: 'text-nowrap', defaultContent: '-' },
                    { data: 'marker_color', className: 'text-nowrap', defaultContent: '-' },
                ],
                order: [[1, 'desc']],
                pageLength: 25,
                language: { processing: '<i class="fa fa-spinner fa-spin"></i> Memuat data...' },
                initComplete: function () {
                    $('#filter-row .col-filter').on('keyup change', function () {
                        table.column($(this).data('col')).search(this.value).draw();
                    });
                }
            });
        });
    </script>
@endsection
