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
        <h5 class="fw-bold text-sb"><i class="fa-solid fa-triangle-exclamation"></i> Check Stocker Tidak Valid</h5>
        <a href="{{ route('stocker-tools') }}" class="btn btn-primary btn-sm px-1 py-1"><i class="fas fa-reply"></i> Kembali ke Tools</a>
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
                <div class="col-md-3 d-flex gap-2">
                    <button class="btn btn-sb w-100" onclick="loadData()">
                        <i class="fa fa-search"></i> Cari
                    </button>
                    {{-- <button class="btn btn-danger w-100" onclick="fixData()">
                        <i class="fa fa-wrench"></i> Fix
                    </button> --}}
                </div>
            </div>
        </div>
    </div>

    <div class="card card-sb">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title fw-bold mb-0">Daftar Stocker Tidak Valid</h5>
            <span class="badge bg-danger" id="total-badge">0 data</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm" id="datatable" style="width:100%">
                    <thead>
                        <tr class="text-center">
                            <th>No</th>
                            <th>ID Qr Stocker</th>
                            <th>Part Detail ID</th>
                            <th>Form Cut ID</th>
                            <th>Form Reject ID</th>
                            <th>Form Piece ID</th>
                            <th>Stocker Reject</th>
                            <th>WS</th>
                            <th>So Det ID</th>
                            <th>Size</th>
                            <th>Color</th>
                            <th>Panel</th>
                            <th>Shade</th>
                            <th>Group Stocker</th>
                            <th>Qty Ply</th>
                            <th>Qty Ply Mod</th>
                            <th>Ratio</th>
                            <th>Qty Cut</th>
                            <th>Range Awal</th>
                            <th>Range Akhir</th>
                            <th>Notes</th>
                            <th>Tujuan</th>
                            <th>Tempat</th>
                            <th>Lokasi</th>
                            <th>Last Lokasi</th>
                            <th>Status</th>
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

        // function fixData() {
        //     Swal.fire({
        //         title: 'Fix Output Master Plan',
        //         html: '<span class="text-danger"><b>Critical</b></span><br>Yakin akan memperbaiki data output yang master plan-nya tidak sesuai?<br><small class="text-muted">Jika tidak ada master plan yang cocok, akan dibuat master plan baru dengan jam_kerja = 0.</small>',
        //         icon: 'warning',
        //         showCancelButton: true,
        //         confirmButtonText: 'FIX',
        //         cancelButtonText: 'Batal',
        //         confirmButtonColor: '#dc3545',
        //     }).then((result) => {
        //         if (!result.isConfirmed) return;

        //         Swal.fire({
        //             title: 'Please Wait...',
        //             html: 'Memperbaiki data... <br><br><b>0</b>s elapsed...',
        //             didOpen: () => {
        //                 Swal.showLoading();
        //                 let t = 0;
        //                 const el = Swal.getPopup().querySelector('b');
        //                 setInterval(() => { t++; el.textContent = t; }, 1000);
        //             },
        //             allowOutsideClick: false,
        //         });

        //         $.ajax({
        //             type: 'POST',
        //             url: '{{ route('fix-output-master-plan') }}',
        //             data: {
        //                 _token: '{{ csrf_token() }}',
        //                 date_from: $('#date_from').val(),
        //                 date_to: $('#date_to').val(),
        //                 line: $('#line').val(),
        //             },
        //             dataType: 'json',
        //             success: function(response) {
        //                 Swal.fire({
        //                     icon: response.status == 200 ? 'success' : 'error',
        //                     title: response.status == 200 ? 'Berhasil' : 'Gagal',
        //                     html: response.message,
        //                     confirmButtonColor: '#082149',
        //                 }).then(() => { if (table) table.ajax.reload(); });
        //             },
        //             error: function(jqXHR) {
        //                 Swal.fire({ icon: 'error', title: 'Error', html: jqXHR.responseText });
        //             }
        //         });
        //     });
        // }

        function loadData() {
            if (table) {
                table.destroy();
                $('#datatable tbody').empty();
            }

            table = $('#datatable').DataTable({
                processing: true,
                serverSide: false,
                ajax: {
                    url: '{{ route('check-stocker-tidak-valid-list') }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        date_from: $('#date_from').val(),
                        date_to: $('#date_to').val(),
                        // line: $('#line').val(),
                    },
                    dataSrc: function(json) {
                        $('#total-badge').text(json.recordsTotal + ' data');
                        return json.data;
                    }
                },
                columns: [
                    {
                        data: null,
                        className: 'text-center',
                        render: function (data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    { data: 'id_qr_stocker' },
                    { data: 'part_detail_id' },
                    { data: 'form_cut_id' },
                    { data: 'form_reject_id' },
                    { data: 'form_piece_id' },
                    { data: 'stocker_reject' },
                    { data: 'act_costing_ws' },
                    { data: 'so_det_id' },
                    { data: 'size' },
                    { data: 'color' },
                    { data: 'panel' },
                    { data: 'shade' },
                    { data: 'group_stocker' },
                    { data: 'qty_ply' },
                    { data: 'qty_ply_mod' },
                    { data: 'ratio' },
                    { data: 'qty_cut' },
                    { data: 'range_awal' },
                    { data: 'range_akhir' },
                    { data: 'notes' },
                    { data: 'tujuan' },
                    { data: 'tempat' },
                    { data: 'lokasi' },
                    { data: 'latest_alokasi' },
                    { data: 'status' }
                ],
                // columns: [
                //     { data: null, render: function(data, type, row, meta) { return meta.row + 1; }, className: 'text-center', orderable: false },
                //     { data: 'tipe', className: 'text-center', render: function(data) {
                //         var color = data === 'RFT' ? 'success' : (data === 'Defect' ? 'warning' : 'danger');
                //         return '<span class="badge bg-' + color + '">' + data + '</span>';
                //     }},
                //     { data: 'line', className: 'text-center' },
                //     { data: 'tgl_plan', className: 'text-center' },
                //     { data: 'ws_actual', defaultContent: '-' },
                //     { data: 'color', defaultContent: '-' },
                //     { data: 'size', className: 'text-center', defaultContent: '-' },
                //     { data: 'ws_plan', defaultContent: '<span class="text-muted">NULL</span>', render: function(data) { return data ?? '<span class="text-muted">NULL</span>'; }},
                //     { data: 'plan_color', defaultContent: '<span class="text-muted">NULL</span>', render: function(data) { return data ?? '<span class="text-muted">NULL</span>'; }},
                //     { data: 'ws_correct', defaultContent: '<span class="text-muted">Tidak ditemukan</span>', render: function(data) { return data ?? '<span class="text-muted fst-italic">Tidak ditemukan</span>'; }},
                //     { data: 'color_correct', defaultContent: '<span class="text-muted">Tidak ditemukan</span>', render: function(data) { return data ?? '<span class="text-muted fst-italic">Tidak ditemukan</span>'; }},
                //     { data: 'keterangan', className: 'text-center' },
                // ],
                // columnDefs: [
                //     { targets: [7, 8, 9, 10, 11], createdCell: function(td) { $(td).css('white-space', 'nowrap'); } }
                // ],
                // order: [[3, 'desc']],
                pageLength: 25,
                language: { processing: '<i class="fa fa-spinner fa-spin"></i> Memuat data...' }
            });
        }
    </script>
@endsection
