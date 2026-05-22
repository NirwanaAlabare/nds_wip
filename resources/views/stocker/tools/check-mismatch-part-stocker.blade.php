@extends('layouts.index')

@section('custom-link')
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
@endsection

@section('content')
    <div class="d-flex justify-content-between mb-3">
        <h5 class="fw-bold text-sb"><i class="fa-solid fa-triangle-exclamation"></i> Check Mismatch Part Stocker</h5>
        <a href="{{ route('stocker-tools') }}" class="btn btn-primary btn-sm px-1 py-1"><i class="fas fa-reply"></i> Kembali ke Tools</a>
    </div>

    <div class="card card-sb">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title fw-bold mb-0">Stocker dengan Part WS tidak sesuai Stocker WS</h5>
            <span class="badge bg-danger" id="total-badge">0 data</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm" id="datatable" style="width:100%">
                    <thead>
                        <tr class="text-center">
                            <th>No</th>
                            <th>ID QR Stocker</th>
                            <th>No. Form</th>
                            <th>So Det ID</th>
                            <th>Part Name</th>
                            <th>WS Stocker</th>
                            <th>WS Part</th>
                            <th>Notes</th>
                            <th>Cancel</th>
                            <th>Created At</th>
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
                    url: '{{ route('check-mismatch-part-stocker-list') }}',
                    type: 'POST',
                    data: { _token: '{{ csrf_token() }}' },
                    dataSrc: function (json) {
                        $('#total-badge').text(json.recordsTotal + ' data');
                        return json.data;
                    }
                },
                columns: [
                    { data: null, render: function (d, t, r, m) { return m.row + 1; }, orderable: false, className: 'text-center' },
                    { data: 'id_qr_stocker', defaultContent: '-' },
                    { data: 'no_form', defaultContent: '-' },
                    { data: 'so_det_id', defaultContent: '-', className: 'text-center' },
                    { data: 'part_name', defaultContent: '-' },
                    { data: 'stocker_ws', defaultContent: '-', className: 'text-center', render: function (d) {
                        return d ? '<span class="badge bg-primary">' + d + '</span>' : '-';
                    }},
                    { data: 'part_ws', defaultContent: '-', className: 'text-center', render: function (d) {
                        return d ? '<span class="badge bg-warning text-dark">' + d + '</span>' : '-';
                    }},
                    { data: 'notes', defaultContent: '-' },
                    { data: 'cancel', defaultContent: '-', className: 'text-center', render: function (d) {
                        return d ? '<span class="badge bg-danger">' + d + '</span>' : '<span class="badge bg-secondary">NULL</span>';
                    }},
                    { data: 'created_at', className: 'text-center', render: function (d) {
                        return d ? formatDateTime(d) : '-';
                    }},
                ],
                order: [[9, 'desc']],
                pageLength: 25,
                language: { processing: '<i class="fa fa-spinner fa-spin"></i> Memuat data...' }
            });
        });
    </script>
@endsection
