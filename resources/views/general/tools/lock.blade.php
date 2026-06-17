@extends('layouts.index')

@section('custom-link')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">

    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

    <style type="text/css">
        input[type=file]::file-selector-button {
            margin-right: 20px;
            border: none;
            background: #084cdf;
            padding: 10px 20px;
            border-radius: 10px;
            color: #fff;
            cursor: pointer;
            transition: background .2s ease-in-out;
        }

        input[type=file]::file-selector-button:hover {
            background: #0d45a5;
        }

        .drop-container {
            position: relative;
            display: flex;
            gap: 10px;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 200px;
            padding: 20px;
            border-radius: 10px;
            border: 2px dashed #555;
            color: #444;
            cursor: pointer;
            transition: background .2s ease-in-out, border .2s ease-in-out;
        }

        .drop-container:hover {
            background: #eee;
            border-color: #111;
        }

        .drop-container:hover .drop-title {
            color: #222;
        }

        .drop-title {
            color: #444;
            font-size: 20px;
            font-weight: bold;
            text-align: center;
            transition: color .2s ease-in-out;
        }
    </style>
@endsection

@section('content')
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold">
                <i class="fa-solid fa-diagram-project"></i> Lock
            </h5>
        </div>

        <div class="card-body">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label for="tanggal_awal" class="form-label">Tanggal Awal</label>
                    <input type="date" class="form-control" id="tanggal_awal" name="tanggal_awal" required>
                </div>

                <div class="col-md-3 mb-3">
                    <label for="tanggal_akhir" class="form-label">Tanggal Akhir</label>
                    <input type="date" class="form-control" id="tanggal_akhir" name="tanggal_akhir" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="deskripsi" class="form-label">Deskripsi</label>
                    <input type="text" class="form-control" id="deskripsi" name="deskripsi" placeholder="Masukkan deskripsi..." required>
                </div>
            </div>

            <div class="col-12 col-md-6 offset-md-3 mt-3 text-center">
                <button type="submit" class="btn btn-success w-100 mb-1 fw-bold" id="btnSimpan">
                    <i class="fa fa-save"></i> SIMPAN
                </button>
            </div>
        </div>
    </div>
        
    <div class="card">
        <div class="card-header bg-sb-secondary">
            <h5 class="card-title">
                <i class="fas fa-list fa-sm"></i> List Data
            </h5>
        </div>
        <div class="card-body">
            {{-- <div class="d-flex align-items-end gap-3 mb-3">
                <div>
                    <label class="form-label"><small>Tanggal Awal</small></label>
                    <input type="date" class="form-control form-control-sm" id="tgl-awal" name="tgl_awal" value="{{ date('Y-m-d') }}">
                </div>
                <div>
                    <label class="form-label"><small>Tanggal Akhir</small></label>
                    <input type="date" class="form-control form-control-sm" id="tgl-akhir" name="tgl_akhir" value="{{ date('Y-m-d') }}">
                </div>
                <div>
                    <button class="btn btn-primary btn-sm" onclick="listTableReload()"> <i class="fa fa-search"></i> </button>
                </div>
                <div class="ms-auto">
                    <button type="button" class="btn btn-danger btn-sm" id="btnDeleteSelected" style="display:none;"> <i class="fa fa-trash"></i> Delete </button>
                </div>
            </div> --}}
            <div class="table-responsive">
                <table class="table table-bordered table-sm" id="list-table">
                    <thead>
                        <tr>
                            <th>Tanggal Awal</th>
                            <th>Tanggal Akhir</th>
                            <th>Deskripsi</th>
                            <th>Status</th>
                            <th>Created By</th>
                            <th>Created At</th>
                            <th>Action</th>
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
            theme: 'bootstrap4'
        })

        let listTable = $("#list-table").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            ajax: {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route('get-data-lock') }}',
                dataType: 'json',
                dataSrc: 'data',
                scrollY: '400px',
                data: function(d) {
                    d.dateFrom = $('#tgl-awal').val();
                    d.dateTo = $('#tgl-akhir').val();
                },
            },
            columns: [
                { data: "start_date" },
                { data: "end_date" },
                { data: "description" },
                { data: "status" },
                { data: "created_by_username" },
                { data: "created_at" },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    className: "text-center",
                    render: function(data, type, row) {

                        let lockButton = row.is_locked == 1
                            ? `
                                <button type="button"
                                    class="btn btn-warning btn-sm btn-unlock"
                                    data-id="${row.id}"
                                    data-bs-toggle="tooltip"
                                    data-bs-title="Unlock">
                                    <i class="fa fa-unlock"></i>
                                </button>
                            `
                            : `
                                <button type="button"
                                    class="btn btn-success btn-sm btn-lock"
                                    data-id="${row.id}"
                                    data-bs-toggle="tooltip"
                                    data-bs-title="Lock">
                                    <i class="fa fa-lock"></i>
                                </button>
                            `;

                        return `
                            <div class="d-flex justify-content-center gap-1">
                                ${lockButton}

                                <button type="button"
                                    class="btn btn-danger btn-sm btn-delete"
                                    data-id="${row.id}"
                                    data-bs-toggle="tooltip"
                                    data-bs-title="Delete">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </div>
                        `;
                    }
                }
            ],
            columnDefs: [
                {
                    targets: "_all",
                    className: "text-nowrap",
                    render: (data, type, row, meta) => data
                },
            ]
        });

        listTable.on('draw', function () {
            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {

                const existingTooltip = bootstrap.Tooltip.getInstance(el);

                if (existingTooltip) {
                    existingTooltip.dispose();
                }

                new bootstrap.Tooltip(el);
            });
        });

        function listTableReload() {
            showLoading();

            listTable.ajax.reload(function () {
                hideLoading();
            });
        }

        $(document).on('click', '#btnSimpan', function () {

            let tanggal_awal = $('#tanggal_awal').val();
            let tanggal_akhir = $('#tanggal_akhir').val();
            let deskripsi = $('#deskripsi').val();

            if (!tanggal_awal || !tanggal_akhir || !deskripsi) {
                iziToast.warning({
                    title: 'Warning',
                    message: 'Semua field wajib diisi!',
                    position: 'topCenter'
                });
                return;
            }

            if (tanggal_akhir < tanggal_awal) {
                iziToast.warning({
                    title: 'Warning',
                    message: 'Tanggal Akhir tidak boleh lebih kecil dari Tanggal Awal!',
                    position: 'topCenter'
                });
                return;
            }

            $.ajax({
                url: "{{ route('store-lock') }}",
                type: "POST",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    tanggal_awal: tanggal_awal,
                    tanggal_akhir: tanggal_akhir,
                    deskripsi: deskripsi
                },
                beforeSend: function () {
                    showLoading();
                    $('#btnSimpan').prop('disabled', true);
                },
                success: function (res) {

                    iziToast.success({
                        title: 'Success',
                        message: 'Data berhasil disimpan',
                        position: 'topCenter'
                    });

                    $('#tanggal_awal').val('');
                    $('#tanggal_akhir').val('');
                    $('#deskripsi').val('');

                    listTableReload();
                },
                error: function (xhr) {
                    iziToast.error({
                        title: 'Error',
                        message: xhr.responseJSON?.message || 'Gagal simpan data',
                        position: 'topCenter'
                    });
                },
                complete: function () {
                    hideLoading();
                    $('#btnSimpan').prop('disabled', false);
                }
            });

        });

        $(document).on('click', '.btn-lock', function() {

            let id = $(this).data('id');

            $.ajax({
                url: "{{ route('locked-lock') }}",
                type: "POST",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    id: id
                },
                success: function(res) {

                    $('[data-bs-toggle="tooltip"]').each(function() {
                        const tooltip = bootstrap.Tooltip.getInstance(this);

                        if (tooltip) {
                            tooltip.hide();
                        }
                    });

                    iziToast.success({
                        title: 'Success',
                        message: res.message,
                        position: 'topCenter'
                    });

                    listTable.ajax.reload(null, false);
                },
                error: function(xhr) {

                    iziToast.error({
                        title: 'Error',
                        message: xhr.responseJSON?.message || 'Gagal lock data',
                        position: 'topCenter'
                    });

                }
            });

        });

        $(document).on('click', '.btn-unlock', function() {

            let id = $(this).data('id');

            $.ajax({
                url: "{{ route('unlocked-lock') }}",
                type: "POST",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    id: id
                },
                success: function(res) {

                    $('[data-bs-toggle="tooltip"]').each(function() {
                        const tooltip = bootstrap.Tooltip.getInstance(this);

                        if (tooltip) {
                            tooltip.hide();
                        }
                    });

                    iziToast.success({
                        title: 'Success',
                        message: res.message,
                        position: 'topCenter'
                    });

                    listTable.ajax.reload(null, false);
                },
                error: function(xhr) {

                    iziToast.error({
                        title: 'Error',
                        message: xhr.responseJSON?.message || 'Gagal unlock data',
                        position: 'topCenter'
                    });

                }
            });

        });

        $(document).on('click', '.btn-delete', function() {

            let id = $(this).data('id');

            Swal.fire({
                title: 'Hapus Data?',
                text: 'Data yang sudah dihapus tidak dapat dikembalikan.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {

                if (!result.isConfirmed) return;

                $.ajax({
                    url: "{{ route('delete-lock') }}",
                    type: "POST",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        id: id
                    },
                    success: function(res) {

                        $('[data-bs-toggle="tooltip"]').each(function() {
                            const tooltip = bootstrap.Tooltip.getInstance(this);

                            if (tooltip) {
                                tooltip.hide();
                            }
                        });

                        iziToast.success({
                            title: 'Success',
                            message: res.message,
                            position: 'topCenter'
                        });

                        listTable.ajax.reload(null, false);
                    },
                    error: function(xhr) {

                        iziToast.error({
                            title: 'Error',
                            message: xhr.responseJSON?.message || 'Gagal menghapus data',
                            position: 'topCenter'
                        });

                    }
                });

            });

        });
    </script>
@endsection
