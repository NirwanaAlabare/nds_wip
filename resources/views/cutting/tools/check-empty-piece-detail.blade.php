@extends('layouts.index')

@section('custom-link')
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
@endsection

@section('content')
    <div class="d-flex justify-content-between mb-3">
        <h5 class="fw-bold text-sb"><i class="fa-solid fa-triangle-exclamation"></i> Check Empty Piece Detail</h5>
        <a href="{{ route('cutting-tools') }}" class="btn btn-primary btn-sm px-1 py-1"><i class="fas fa-reply"></i> Kembali ke Tools</a>
    </div>

    <div class="card card-sb">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title fw-bold mb-0">Form Cut Piece Detail Tanpa Size</h5>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-danger" id="total-badge">0 data</span>
                <button class="btn btn-danger btn-sm" onclick="deleteAll()">
                    <i class="fa fa-trash"></i> Hapus Semua
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm" id="datatable" style="width:100%">
                    <thead>
                        <tr class="text-center">
                            <th>No</th>
                            <th>No. Form</th>
                            <th>ID Roll</th>
                            <th>Lot</th>
                            <th>Group Roll</th>
                            <th>Group Stocker</th>
                            <th>Qty</th>
                            <th>Qty Pemakaian</th>
                            <th>Qty Sisa</th>
                            <th>Status</th>
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
        function deleteAll() {
            Swal.fire({
                title: 'Hapus Empty Piece Detail',
                html: '<span class="text-danger"><b>Critical</b></span><br>Yakin akan menghapus semua Form Cut Piece Detail yang tidak memiliki size?<br><small class="text-muted">Qty pemakaian akan di-reset dan chained qty akan diperbaiki sebelum dihapus.</small>',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'HAPUS',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#dc3545',
            }).then((result) => {
                if (!result.isConfirmed) return;

                Swal.fire({
                    title: 'Please Wait...',
                    html: 'Menghapus data...<br><br><b>0</b>s elapsed...',
                    didOpen: () => {
                        Swal.showLoading();
                        let t = 0;
                        const el = Swal.getPopup().querySelector('b');
                        setInterval(() => { t++; el.textContent = t; }, 1000);
                    },
                    allowOutsideClick: false,
                });

                $.ajax({
                    type: 'POST',
                    url: '{{ route('delete-empty-piece-detail') }}',
                    data: { _token: '{{ csrf_token() }}' },
                    dataType: 'json',
                    success: function (response) {
                        Swal.fire({
                            icon: response.status == 200 ? 'success' : 'error',
                            title: response.status == 200 ? 'Berhasil' : 'Gagal',
                            html: response.message,
                            confirmButtonColor: '#082149',
                        }).then(() => { if (table) table.ajax.reload(); });
                    },
                    error: function (jqXHR) {
                        Swal.fire({ icon: 'error', title: 'Error', html: jqXHR.responseText });
                    }
                });
            });
        }

        var table;
        $(document).ready(function () {
            table = $('#datatable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('check-empty-piece-detail-list') }}',
                    type: 'POST',
                    data: { _token: '{{ csrf_token() }}' },
                    dataSrc: function (json) {
                        $('#total-badge').text(json.recordsTotal + ' data');
                        return json.data;
                    }
                },
                columns: [
                    { data: null, render: function (d, t, r, m) { return m.row + 1; }, orderable: false, className: 'text-center' },
                    { data: 'no_form', defaultContent: '-' },
                    { data: 'id_roll', defaultContent: '-' },
                    { data: 'lot', defaultContent: '-' },
                    { data: 'group_roll', defaultContent: '-', className: 'text-center' },
                    { data: 'group_stocker', defaultContent: '-', className: 'text-center' },
                    { data: 'qty', className: 'text-end' },
                    { data: 'qty_pemakaian', className: 'text-end' },
                    { data: 'qty_sisa', className: 'text-end' },
                    { data: 'status', className: 'text-center', render: function (d) {
                        return d ? '<span class="badge bg-secondary">' + d + '</span>' : '-';
                    }},
                    { data: 'created_at', className: 'text-center' },
                ],
                order: [[10, 'desc']],
                pageLength: 25,
                language: { processing: '<i class="fa fa-spinner fa-spin"></i> Memuat data...' }
            });
        });
    </script>
@endsection
