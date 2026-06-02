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
    <div class="d-flex justify-content-between mb-3">
        <h5 class="fw-bold text-sb"><i class="fa-solid fa-triangle-exclamation"></i> Check Mismatched Master Plan Output</h5>
        <a href="{{ route('sewing-tools') }}" class="btn btn-primary btn-sm px-1 py-1"><i class="fas fa-reply"></i> Kembali ke Tools</a>
    </div>

    <div class="card card-sb mb-3">
        <div class="card-header">
            <h5 class="card-title fw-bold">Filter</h5>
        </div>
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Tanggal Dari</label>
                    <input type="date" class="form-control" id="date_from" value="{{ date('Y-m-d', strtotime('-30 days')) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tanggal Sampai</label>
                    <input type="date" class="form-control" id="date_to" value="{{ date('Y-m-d') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Line</label>
                    <select class="form-select select2bs4" id="line">
                        <option value="">Semua Line</option>
                        @foreach ($lines as $line)
                            <option value="{{ $line->username }}">{{ $line->username }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button class="btn btn-sb w-100" onclick="loadData()">
                        <i class="fa fa-search"></i> Cari
                    </button>
                    <button class="btn btn-danger w-100" onclick="fixData()">
                        <i class="fa fa-wrench"></i> Fix
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-sb">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title fw-bold mb-0">Daftar Output Tidak Sesuai Master Plan</h5>
            <span class="badge bg-danger" id="total-badge">0 data</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm" id="datatable" style="width:100%">
                    <thead>
                        <tr class="text-center">
                            <th>No</th>
                            <th>Tipe</th>
                            <th>Kode Numbering</th>
                            <th>Line</th>
                            <th>Tgl Plan</th>
                            <th>WS Aktual</th>
                            <th>Color Aktual</th>
                            <th>Size</th>
                            <th>WS Plan Terpasang</th>
                            <th>Color Plan Terpasang</th>
                            <th>WS Plan Seharusnya</th>
                            <th>Color Plan Seharusnya</th>
                            <th>Keterangan</th>
                        </tr>
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
        $('.select2bs4').select2({ theme: 'bootstrap4' });

        var table = null;

        function fixData() {
            Swal.fire({
                title: 'Fix Output Master Plan',
                html: '<span class="text-danger"><b>Critical</b></span><br>Yakin akan memperbaiki data output yang master plan-nya tidak sesuai?<br><small class="text-muted">Jika tidak ada master plan yang cocok, akan dibuat master plan baru dengan jam_kerja = 0.</small>',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'FIX',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#dc3545',
            }).then((result) => {
                if (!result.isConfirmed) return;

                Swal.fire({
                    title: 'Please Wait...',
                    html: 'Memperbaiki data... <br><br><b>0</b>s elapsed...',
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
                    url: '{{ route('fix-output-master-plan') }}',
                    data: {
                        _token: '{{ csrf_token() }}',
                        date_from: $('#date_from').val(),
                        date_to: $('#date_to').val(),
                        line: $('#line').val(),
                    },
                    dataType: 'json',
                    success: function(response) {
                        Swal.fire({
                            icon: response.status == 200 ? 'success' : 'error',
                            title: response.status == 200 ? 'Berhasil' : 'Gagal',
                            html: response.message,
                            confirmButtonColor: '#082149',
                        }).then(() => { if (table) table.ajax.reload(); });
                    },
                    error: function(jqXHR) {
                        Swal.fire({ icon: 'error', title: 'Error', html: jqXHR.responseText });
                    }
                });
            });
        }

        function loadData() {
            if (table) {
                table.destroy();
                $('#datatable tbody').empty();
            }

            table = $('#datatable').DataTable({
                processing: true,
                serverSide: false,
                ajax: {
                    url: '{{ route('check-output-master-plan-list') }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        date_from: $('#date_from').val(),
                        date_to: $('#date_to').val(),
                        line: $('#line').val(),
                    },
                    dataSrc: function(json) {
                        $('#total-badge').text(json.recordsTotal + ' data');
                        return json.data;
                    }
                },
                columns: [
                    { data: null, render: function(data, type, row, meta) { return meta.row + 1; }, className: 'text-center', orderable: false },
                    { data: 'tipe', className: 'text-center', render: function(data) {
                        var color = data === 'RFT' ? 'success' : (data === 'Defect' ? 'warning' : 'danger');
                        return '<span class="badge bg-' + color + '">' + data + '</span>';
                    }},
                    { data: 'kode_numbering', className: 'text-center' },
                    { data: 'line', className: 'text-center' },
                    { data: 'tgl_plan', className: 'text-center' },
                    { data: 'ws_actual', defaultContent: '-' },
                    { data: 'color', defaultContent: '-' },
                    { data: 'size', className: 'text-center', defaultContent: '-' },
                    { data: 'ws_plan', defaultContent: '<span class="text-muted">NULL</span>', render: function(data) { return data ?? '<span class="text-muted">NULL</span>'; }},
                    { data: 'plan_color', defaultContent: '<span class="text-muted">NULL</span>', render: function(data) { return data ?? '<span class="text-muted">NULL</span>'; }},
                    { data: 'ws_correct', defaultContent: '<span class="text-muted">Tidak ditemukan</span>', render: function(data) { return data ?? '<span class="text-muted fst-italic">Tidak ditemukan</span>'; }},
                    { data: 'color_correct', defaultContent: '<span class="text-muted">Tidak ditemukan</span>', render: function(data) { return data ?? '<span class="text-muted fst-italic">Tidak ditemukan</span>'; }},
                    { data: 'keterangan', className: 'text-center' },
                ],
                columnDefs: [
                    { targets: [7, 8, 9, 10, 11], createdCell: function(td) { $(td).css('white-space', 'nowrap'); } }
                ],
                order: [[3, 'desc']],
                pageLength: 25,
                language: { processing: '<i class="fa fa-spinner fa-spin"></i> Memuat data...' }
            });
        }
    </script>
@endsection
