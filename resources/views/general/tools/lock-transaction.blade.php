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
        /* Penanda form detail sedang mode edit */
        #detailForm {
            transition: background-color .3s ease;
        }

        #detailForm.detail-form-editing {
            background-color: #fff3cd;
            padding-top: .75rem;
        }
    </style>
@endsection

@section('content')
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold">
                <i class="fa-solid fa-lock"></i> Lock Transaction
            </h5>
        </div>

        <div class="card-body">
            <input type="hidden" id="transaction_id">

            <div class="row align-items-end">
                <div class="col-md-9 mb-3">
                    <label for="transaction_name" class="form-label">Nama Transaksi</label>
                    <input type="text" class="form-control" id="transaction_name" placeholder="Masukkan nama transaksi..." maxlength="255">
                </div>

                <div class="col-md-3 mb-3 d-flex gap-1">
                    <button type="button" class="btn btn-success w-100 fw-bold" id="btnSimpan">
                        <i class="fa fa-save"></i> <span id="btnSimpanText">SIMPAN</span>
                    </button>
                    <button type="button" class="btn btn-outline-secondary d-none" id="btnBatalEdit" data-bs-toggle="tooltip" data-bs-title="Batal Edit">
                        <i class="fa fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-sb-secondary">
            <h5 class="card-title">
                <i class="fas fa-list fa-sm"></i> List Transaksi
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-sm w-100" id="list-table">
                    <thead>
                        <tr>
                            <th>Nama Transaksi</th>
                            <th>Status</th>
                            <th>Jumlah Detail</th>
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

    {{-- Modal Detail --}}
    <div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-sb text-light">
                    <h5 class="modal-title fw-bold" id="detailModalLabel">
                        <i class="fa fa-list"></i> Detail Transaksi : <span id="detailTransactionName"></span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="detail_transaction_id">
                    <input type="hidden" id="detail_id">

                    <div class="row align-items-end rounded" id="detailForm">
                        <div class="col-md-5 mb-3">
                            <label for="transaction_detail_name" class="form-label">Nama Detail <small class="text-muted">(opsional)</small></label>
                            <input type="text" class="form-control" id="transaction_detail_name" placeholder="Kosongkan untuk memakai nama route" maxlength="255">
                        </div>
                        <div class="col-md-5 mb-3">
                            <label for="route_name" class="form-label">Route Name</label>
                            <select class="form-control select2bs4-detail" id="route_name" multiple style="width: 100%">
                                @foreach ($routeNames as $routeName)
                                    <option value="{{ $routeName }}">{{ $routeName }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 mb-3 d-flex gap-1">
                            <button type="button" class="btn btn-success w-100 fw-bold" id="btnSimpanDetail">
                                <i class="fa fa-save"></i> <span id="btnSimpanDetailText">SIMPAN</span>
                            </button>
                            <button type="button" class="btn btn-outline-secondary d-none" id="btnBatalEditDetail" data-bs-toggle="tooltip" data-bs-title="Batal Edit">
                                <i class="fa fa-times"></i>
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-sm w-100" id="detail-table">
                            <thead>
                                <tr>
                                    <th>Nama Detail</th>
                                    <th>Route Name</th>
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
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
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
        const csrfHeader = {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        };

        $('.select2bs4-detail').select2({
            theme: 'bootstrap4',
            dropdownParent: $('#detailModal'),
            placeholder: 'Pilih satu atau beberapa route...',
            closeOnSelect: false,
        });

        function escapeHtml(value) {
            return $('<div>').text(value ?? '').html();
        }

        function refreshTooltips() {
            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
                bootstrap.Tooltip.getInstance(el)?.dispose();
                new bootstrap.Tooltip(el);
            });
        }

        function hideTooltips() {
            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
                bootstrap.Tooltip.getInstance(el)?.hide();
            });
        }

        function errorMessage(xhr, fallback) {
            const res = xhr.responseJSON ?? {};

            // Error validasi Laravel: ambil pesan pertama
            if (res.errors) {
                return Object.values(res.errors)[0][0];
            }

            return res.message || fallback;
        }

        // Kirim request lalu reload tabel terkait
        function postAction(url, data, table, fallbackError) {
            return $.ajax({
                url: url,
                type: 'POST',
                headers: csrfHeader,
                data: data,
                beforeSend: function () {
                    showLoading();
                },
                success: function (res) {
                    hideTooltips();

                    iziToast.success({
                        title: 'Success',
                        message: res.message,
                        position: 'topCenter'
                    });

                    table.ajax.reload(null, false);
                },
                error: function (xhr) {
                    iziToast.error({
                        title: 'Error',
                        message: errorMessage(xhr, fallbackError),
                        position: 'topCenter'
                    });
                },
                complete: function () {
                    hideLoading();
                }
            });
        }

        // ===== Lock Transaction =====

        let listTable = $("#list-table").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            ajax: {
                method: 'POST',
                headers: csrfHeader,
                url: '{{ route('get-data-lock-transaction') }}',
                dataType: 'json',
                dataSrc: 'data',
            },
            columns: [
                { data: "transaction_name" },
                {
                    data: "status",
                    render: function (data, type, row) {
                        return row.is_locked == '1'
                            ? '<span class="badge bg-danger"><i class="fa fa-lock"></i> Locked</span>'
                            : '<span class="badge bg-success"><i class="fa fa-unlock"></i> Unlocked</span>';
                    }
                },
                { data: "detail_count", className: "text-center", searchable: false },
                { data: "created_by_username" },
                { data: "created_at" },
                {
                    data: null,
                    searchable: false,
                    className: "text-center",
                    render: function (data, type, row) {
                        const isLocked = row.is_locked == '1';

                        const lockButton = isLocked
                            ? `<button type="button" class="btn btn-warning btn-sm btn-unlock" data-id="${row.id}" data-bs-toggle="tooltip" data-bs-title="Unlock">
                                    <i class="fa fa-unlock"></i>
                               </button>`
                            : `<button type="button" class="btn btn-success btn-sm btn-lock" data-id="${row.id}" data-bs-toggle="tooltip" data-bs-title="Lock">
                                    <i class="fa fa-lock"></i>
                               </button>`;

                        return `
                            <div class="d-flex justify-content-center gap-1">
                                ${lockButton}
                                <button type="button" class="btn btn-info btn-sm btn-detail" data-bs-toggle="tooltip" data-bs-title="Detail">
                                    <i class="fa fa-list"></i>
                                </button>
                                <button type="button" class="btn btn-primary btn-sm btn-edit" data-bs-toggle="tooltip" data-bs-title="Edit">
                                    <i class="fa fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-danger btn-sm btn-delete" data-id="${row.id}" data-bs-toggle="tooltip"
                                    data-bs-title="${isLocked ? 'Transaksi sudah di-lock' : 'Delete'}" ${isLocked ? 'disabled' : ''}>
                                    <i class="fa fa-trash"></i>
                                </button>
                            </div>
                        `;
                    }
                }
            ],
            columnDefs: [
                {
                    targets: [0, 3, 4],
                    render: (data) => escapeHtml(data)
                },
                {
                    targets: "_all",
                    className: "text-nowrap align-middle"
                },
            ]
        });

        listTable.on('draw', refreshTooltips);

        function resetForm() {
            $('#transaction_id').val('');
            $('#transaction_name').val('');
            $('#btnSimpanText').text('SIMPAN');
            $('#btnBatalEdit').addClass('d-none');
        }

        $('#btnBatalEdit').on('click', function () {
            hideTooltips();
            resetForm();
        });

        $('#btnSimpan').on('click', function () {
            const transactionName = $('#transaction_name').val().trim();

            if (!transactionName) {
                iziToast.warning({
                    title: 'Warning',
                    message: 'Nama transaksi wajib diisi!',
                    position: 'topCenter'
                });
                return;
            }

            $('#btnSimpan').prop('disabled', true);

            postAction("{{ route('store-lock-transaction') }}", {
                id: $('#transaction_id').val(),
                transaction_name: transactionName,
            }, listTable, 'Gagal simpan data')
                .done(resetForm)
                .always(() => $('#btnSimpan').prop('disabled', false));
        });

        $('#list-table').on('click', '.btn-edit', function () {
            const row = listTable.row($(this).closest('tr')).data();

            hideTooltips();
            $('#transaction_id').val(row.id);
            $('#transaction_name').val(row.transaction_name).trigger('focus');
            $('#btnSimpanText').text('UPDATE');
            $('#btnBatalEdit').removeClass('d-none');
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });

        $('#list-table').on('click', '.btn-lock', function () {
            postAction("{{ route('locked-lock-transaction') }}", { id: $(this).data('id') }, listTable, 'Gagal lock data');
        });

        $('#list-table').on('click', '.btn-unlock', function () {
            postAction("{{ route('unlocked-lock-transaction') }}", { id: $(this).data('id') }, listTable, 'Gagal unlock data');
        });

        $('#list-table').on('click', '.btn-delete', function () {
            const id = $(this).data('id');

            Swal.fire({
                title: 'Hapus Transaksi?',
                text: 'Transaksi beserta seluruh detailnya akan dihapus dan tidak dapat dikembalikan.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (!result.isConfirmed) return;

                postAction("{{ route('delete-lock-transaction') }}", { id: id }, listTable, 'Gagal menghapus data')
                    .done(() => {
                        // Transaksi yang sedang diedit ikut terhapus
                        if ($('#transaction_id').val() == id) resetForm();
                    });
            });
        });

        // ===== Lock Transaction Detail =====

        let detailTable = $("#detail-table").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            deferLoading: 0,
            ajax: {
                method: 'POST',
                headers: csrfHeader,
                url: '{{ route('get-detail-lock-transaction') }}',
                dataType: 'json',
                dataSrc: 'data',
                data: function (d) {
                    d.transaction_id = $('#detail_transaction_id').val();
                },
            },
            columns: [
                { data: "transaction_detail_name" },
                { data: "route_name" },
                { data: "created_by_username" },
                { data: "created_at" },
                {
                    data: null,
                    searchable: false,
                    className: "text-center",
                    render: function (data, type, row) {
                        return `
                            <div class="d-flex justify-content-center gap-1">
                                <button type="button" class="btn btn-primary btn-sm btn-edit-detail" data-bs-toggle="tooltip" data-bs-title="Edit">
                                    <i class="fa fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-danger btn-sm btn-delete-detail" data-id="${row.id}" data-bs-toggle="tooltip" data-bs-title="Delete">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </div>
                        `;
                    }
                }
            ],
            columnDefs: [
                {
                    targets: [0, 1, 2, 3],
                    render: (data) => escapeHtml(data)
                },
                {
                    targets: "_all",
                    className: "text-nowrap align-middle"
                },
            ]
        });

        detailTable.on('draw', refreshTooltips);

        function resetDetailForm() {
            $('#detailForm').removeClass('detail-form-editing');
            $('#detail-table tbody tr').removeClass('table-warning');
            $('#detail_id').val('');
            $('#transaction_detail_name').val('');
            $('#route_name').val([]).trigger('change');
            $('#btnSimpanDetailText').text('SIMPAN');
            $('#btnBatalEditDetail').addClass('d-none');
        }

        $('#list-table').on('click', '.btn-detail', function () {
            const row = listTable.row($(this).closest('tr')).data();

            hideTooltips();
            resetDetailForm();
            $('#detail_transaction_id').val(row.id);
            $('#detailTransactionName').text(row.transaction_name);
            $('#detailModal').modal('show');
            detailTable.search('').ajax.reload();
        });

        // Jumlah detail di tabel transaksi ikut diperbarui
        $('#detailModal').on('hidden.bs.modal', function () {
            listTable.ajax.reload(null, false);
        });

        $('#detailModal').on('shown.bs.modal', function () {
            detailTable.columns.adjust();
        });

        $('#btnBatalEditDetail').on('click', function () {
            hideTooltips();
            resetDetailForm();
        });

        $('#btnSimpanDetail').on('click', function () {
            const detailName = $('#transaction_detail_name').val().trim();
            const routeNames = $('#route_name').val() || [];

            if (!routeNames.length) {
                iziToast.warning({
                    title: 'Warning',
                    message: 'Route name wajib dipilih!',
                    position: 'topCenter'
                });
                return;
            }

            if ($('#detail_id').val() && routeNames.length > 1) {
                iziToast.warning({
                    title: 'Warning',
                    message: 'Saat edit hanya boleh memilih 1 route!',
                    position: 'topCenter'
                });
                return;
            }

            $('#btnSimpanDetail').prop('disabled', true);

            postAction("{{ route('store-detail-lock-transaction') }}", {
                id: $('#detail_id').val(),
                transaction_id: $('#detail_transaction_id').val(),
                // Kosong => server mengisi dengan nama route
                transaction_detail_name: detailName,
                route_names: routeNames,
            }, detailTable, 'Gagal simpan detail')
                .done(resetDetailForm)
                .always(() => $('#btnSimpanDetail').prop('disabled', false));
        });

        $(document).on('click', '#detail-table .btn-edit-detail', function () {
            const $tr = $(this).closest('tr');
            const row = detailTable.row($tr).data();

            if (!row) return;

            hideTooltips();
            $('#detail_id').val(row.id);
            $('#transaction_detail_name').val(row.transaction_detail_name);

            // Route lama yang sudah tidak terdaftar tetap ditampilkan agar bisa diganti
            if (!$('#route_name option').filter((i, option) => option.value === row.route_name).length) {
                $('#route_name').append(new Option(row.route_name, row.route_name));
            }

            $('#route_name').val([row.route_name]).trigger('change');
            $('#btnSimpanDetailText').text('UPDATE');
            $('#btnBatalEditDetail').removeClass('d-none');

            // Tandai baris yang sedang diedit
            $('#detail-table tbody tr').removeClass('table-warning');
            $tr.addClass('table-warning');

            // Form ada di atas modal (scrollable), arahkan ke form supaya perubahan terlihat
            $('#detailModal .modal-body').animate({ scrollTop: 0 }, 200, function () {
                $('#detailForm').addClass('detail-form-editing');
                $('#transaction_detail_name').trigger('focus');
            });
        });

        $('#detail-table').on('click', '.btn-delete-detail', function () {
            const id = $(this).data('id');

            Swal.fire({
                title: 'Hapus Detail?',
                text: 'Data yang sudah dihapus tidak dapat dikembalikan.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (!result.isConfirmed) return;

                postAction("{{ route('delete-detail-lock-transaction') }}", { id: id }, detailTable, 'Gagal menghapus detail')
                    .done(() => {
                        if ($('#detail_id').val() == id) resetDetailForm();
                    });
            });
        });
    </script>
@endsection
