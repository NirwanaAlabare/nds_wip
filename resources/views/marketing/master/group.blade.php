@extends('layouts.index')

@section('custom-link')
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('dist/css/marketing-buttons.css') }}?v={{ @filemtime(public_path('assets/dist/css/marketing-buttons.css')) }}">
    <style>
        .mg-page { padding: 10px 14px 28px; background: #f8fafc; min-height: calc(100vh - 56px); color: #1e293b; }
        .mg-crumb { padding: 9px 12px; border-radius: 4px; background: #284A63; color: #cbd5e1; font-size: 12px; letter-spacing: .2px; margin-bottom: 12px; }
        .mg-crumb b { color: #fff; }
        .mg-back { display: inline-flex; align-items: center; gap: 10px; margin: 12px 0 14px; color: #334155; font-size: 14px; font-weight: 700; text-decoration: none !important; }
        .mg-back i { padding: 4px 6px; border-radius: 5px; background: #e2e8f0; font-size: 11px; }
        .mg-card { padding: 20px 24px 24px; border-radius: 8px; background: #fff; box-shadow: 0 5px 12px -3px rgba(15, 23, 42, .18), 0 2px 4px rgba(15, 23, 42, .07); }
        .mg-title { margin: 0; color: #1e3a5f; font-size: 24px; font-weight: 800; }
        .mg-title i { color: #64748b; font-size: 12px; vertical-align: super; }
        .mg-subtitle { margin: 4px 0 18px; color: #475569; font-size: 11px; font-weight: 600; }
        .mg-shell { padding: 10px 12px 14px; border: 1px solid #e2e8f0; border-radius: 10px; box-shadow: 0 4px 14px rgba(15, 23, 42, .06); }

        .mg-table { margin-bottom: 0; font-family: Inter, sans-serif; font-size: 14px; font-weight: 500; }
        .mg-table thead th { padding: 7px 10px; border: 0; background: #cfe0ff; color: #26394c; font-size: 12px; font-weight: 500; text-transform: uppercase; white-space: nowrap; }
        .mg-table tbody td { padding: 7px 10px; border-top: 0; border-bottom: 1px solid #eef2f7; vertical-align: middle; }

        .mg-empty { color: #94a3b8; }

        .mg-shell .dataTables_info, .mg-shell .dataTables_length, .mg-shell .dataTables_paginate { padding-top: 10px; color: #475569; font-size: 11px; }
        .mg-shell .dataTables_length select { height: 26px; margin: 0 6px; font-size: 11px; }
        .mg-shell .pagination { margin: 0; gap: 2px; }
        .mg-shell .page-link { min-width: 22px; padding: 3px 7px; border: 0; border-radius: 4px; color: #475569; font-size: 11px; text-align: center; }
        .mg-shell .page-item.active .page-link { background: #1e3a5f; color: #fff; }
        .mg-shell .page-item.disabled .page-link { background: none; color: #cbd5e1; }
        .mg-add { min-width: 110px; height: 34px; margin-top: 14px; border: 0; border-radius: 17px; background: #1e3a5f; color: #fff; font-size: 12px; font-weight: 600; box-shadow: 0 3px 8px rgba(30, 58, 95, .3); }

        .mg-modal .modal-content { border: 0; border-radius: 8px; }
        .mg-modal .modal-header { justify-content: center; border-bottom: 1px solid #e2e8f0; }
        .mg-modal .modal-title { color: #1e3a5f; font-size: 14px; font-weight: 700; }
        .mg-modal .mg-form { padding: 14px; border-radius: 6px; box-shadow: 0 2px 10px rgba(15, 23, 42, .1); }
        .mg-modal label { margin-bottom: 4px; color: #1e293b; font-family: Poppins, sans-serif; font-size: 14px; font-weight: 600; }
        .mg-modal .form-control { height: 40px; border-radius: 6px; font-family: Poppins, sans-serif; font-size: 12px; }
        .mg-modal .select2-container--bootstrap4 .select2-selection { min-height: 40px; border-radius: 6px; font-family: Poppins, sans-serif; font-size: 12px; }
        .mg-modal .modal-footer { justify-content: center; gap: 12px; border-top: 0; }
        .mg-btn { min-width: 110px; height: 34px; border: 0; border-radius: 17px; color: #fff; font-size: 12px; font-weight: 600; }
        .mg-btn.is-cancel { background: #e11d48; box-shadow: 0 3px 8px rgba(225, 29, 72, .3); }
        .mg-btn.is-save { background: #15803d; box-shadow: 0 3px 8px rgba(21, 128, 61, .3); }
        .mg-btn:disabled { opacity: .6; }

        @media (max-width: 575.98px) {
            .mg-page { padding: 8px 4px 20px; }
            .mg-card { padding: 14px 12px 16px; }
            .mg-title { font-size: 20px; }
            .mg-shell { padding: 6px 6px 10px; }
            .mg-table { font-size: 13px; }
            .mg-table thead th, .mg-table tbody td { padding: 6px; }
            .mg-table thead th { white-space: normal; }
            .mg-table td:last-child { white-space: nowrap; }
            .mg-modal .modal-dialog { margin-left: auto; margin-right: auto; padding: 0 12px; }
        }

        /* ===== Tampilan sesuai mockup List Group (sama dengan listing costing) ===== */
        .mg-crumb, .mg-crumb b { font-family: 'Poppins', sans-serif; font-size: 16px; font-weight: 500; }
        .mg-title { color: #284a63; font-family: 'Poppins', sans-serif; font-size: 32px; font-weight: 700; }
        .mg-subtitle { color: #284a63; font-family: 'Poppins', sans-serif; font-size: 12px; font-weight: 600; }
        .mg-subtitle i { margin-right: 6px; color: #a3b2c7; }
        .mg-shell { padding: 12px 16px 16px; border: 0; border-radius: 12px; box-shadow: 0 2px 10px rgba(15, 23, 42, .10); }
        /* Kolom Kode & Nama sempit di kiri, Action di sebelahnya, sisa lebar kosong. */
        .mg-table { table-layout: auto; }
        .mg-table thead th { padding: 10px 10px; background: #cfe2ff; color: #1e293b; font-family: Inter, sans-serif !important; font-size: 12px !important; font-weight: 500 !important; letter-spacing: .5px; }
        .mg-table tbody td { padding: 8px 10px; color: #1e293b; font-family: Inter, sans-serif; font-size: 14px !important; font-weight: 400; line-height: 20px; white-space: nowrap; }
        .mg-table th.mg-col-check, .mg-table td.mg-col-check { width: 34px; text-align: center; }
        .mg-table th.mg-col-no, .mg-table td.mg-col-no { width: 54px; }
        .mg-table th.mg-col-kode { width: 140px; }
        .mg-table th.mg-col-nama { width: 260px; }
        .mg-table tbody tr:hover td { background: #f6f9ff; }
        /* Tombol aksi bulat: edit biru, hapus merah. */
        /* Pager sama dengan tabel marketing lain. */
        .mg-shell .dataTables_info, .mg-shell .dataTables_length, .mg-shell .dataTables_paginate { padding-top: 14px; color: #1e293b; font-family: 'Poppins', sans-serif; font-size: 16px; }
        .mg-shell .dataTables_length label { display: inline-flex; align-items: center; gap: 12px; margin: 0; font-size: 16px; font-weight: 400; }
        .mg-shell .dataTables_length select { height: 40px; min-width: 76px; margin: 0; padding: 0 10px; border: 1px solid #284a63; border-radius: 8px; font-family: 'Poppins', sans-serif; font-size: 16px; font-weight: 400; }
        .mg-shell .dataTables_info { margin-left: 12px; }
        .mg-shell .pagination { gap: 8px; }
        .mg-shell .page-link { min-width: 32px; height: 32px; padding: 0 6px; border: 0; border-radius: 6px; background: transparent; color: #64748b; font-size: 16px; font-weight: 500; line-height: 32px; }
        .mg-shell .page-item.active .page-link { background: #274b63; color: #fff; font-weight: 700; }
        /* Khusus halaman ini: menu navbar Inter Medium 16 (QA Master Group). */
        .navbar-nav > .nav-item > .nav-link { font-family: Inter, sans-serif !important; font-size: 16px !important; font-weight: 500 !important; }
        .mg-shell .mg-actions-bar { display: flex; justify-content: flex-end; margin-top: 16px; }
        /* Checkbox seragam dengan halaman marketing lain: 16x16, radius 4, navy saat dicentang. */
        .mg-page input[type="checkbox"]:not(.custom-control-input) { -webkit-appearance: none; appearance: none; flex: 0 0 16px; width: 16px; height: 16px; padding: 0; border: 1.5px solid #cfd4dc; border-radius: 4px; background: #fff no-repeat center / 10px 10px; vertical-align: middle; cursor: pointer; transition: background-color .15s ease, border-color .15s ease, box-shadow .15s ease; }
        .mg-page input[type="checkbox"]:not(.custom-control-input):hover:not(:checked):not(:indeterminate):not(:disabled) { background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12'%3e%3cpath fill='none' stroke='%23cfd4dc' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' d='M2.5 6.2l2.3 2.3 4.7-4.9'/%3e%3c/svg%3e"); }
        .mg-page input[type="checkbox"]:not(.custom-control-input):checked { border-color: #284a63; background-color: #284a63; background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12'%3e%3cpath fill='none' stroke='%23fff' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' d='M2.5 6.2l2.3 2.3 4.7-4.9'/%3e%3c/svg%3e"); }
        .mg-page input[type="checkbox"]:not(.custom-control-input):indeterminate { border-color: #284a63; background-color: #284a63; background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12'%3e%3cpath fill='none' stroke='%23fff' stroke-width='2' stroke-linecap='round' d='M3 6h6'/%3e%3c/svg%3e"); }
        .mg-page input[type="checkbox"]:not(.custom-control-input):focus { outline: 0; }
        .mg-page input[type="checkbox"]:not(.custom-control-input):focus-visible { box-shadow: 0 0 0 4px #b3daf7; }
        .mg-page input[type="checkbox"]:not(.custom-control-input):disabled { opacity: .5; cursor: not-allowed; }
        @media (max-width: 575.98px) {
            .mg-title { font-size: 24px; }
            .mg-table th.mg-col-kode, .mg-table th.mg-col-nama { width: auto; }
            .mg-shell .dataTables_length label, .mg-shell .dataTables_info, .mg-shell .page-link { font-size: 14px; }
        }
    </style>
@endsection

@section('content')
<div class="mg-page">

    <div class="mg-card">
        <div class="mg-crumb">Marketing &nbsp;›&nbsp; Master &nbsp;›&nbsp; <b>Master Group</b></div>

        <h1 class="mg-title">List Group <i class="fas fa-info-circle" title="Group material untuk content, item, dan BOM"></i></h1>
        <p class="mg-subtitle"><i class="fas fa-database"></i> List Data Master Group</p>

        <div class="mg-shell">
            <div class="table-responsive">
                <table class="table mg-table w-100" id="mg-table">
                    <thead>
                        <tr>
                            <th class="mg-col-check"><input type="checkbox" id="mg-check-all" aria-label="Pilih semua"></th>
                            <th class="mg-col-no">No</th>
                            <th class="mg-col-kode">Kode Group</th>
                            <th class="mg-col-nama">Nama Group</th>
                            <th class="mg-col-action">Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
            <div class="mg-actions-bar">
                <button type="button" class="mkt-btn mkt-btn--navy" id="mg-add">Add New</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade mg-modal" id="mg-modal" tabindex="-1" role="dialog" aria-labelledby="mg-modal-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 360px;">
        <form class="modal-content" id="mg-form" novalidate>
            <div class="modal-header">
                <h5 class="modal-title" id="mg-modal-title">Create New Group</h5>
            </div>
            <div class="modal-body">
                <div class="mg-form">
                    <input type="hidden" id="mg-id">
                    <div class="form-group">
                        <label for="mg-kode">Group Code</label>
                        <input type="text" id="mg-kode" class="form-control text-uppercase" maxlength="10" placeholder="Group Code" required>
                    </div>
                    <div class="form-group">
                        <label for="mg-nama">Group Name</label>
                        <input type="text" id="mg-nama" class="form-control text-uppercase" maxlength="255" placeholder="Group Name" required>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="mg-btn is-cancel" id="mg-cancel" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="mg-btn is-save" id="mg-save">Save</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('custom-script')
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        const MG_ROUTES = {
            index: "{{ route('marketing-master-group') }}",
            store: "{{ route('marketing-master-group-store') }}",
            update: "{{ route('marketing-master-group-update', ':id') }}",
            destroy: "{{ route('marketing-master-group-destroy', ':id') }}"
        };
        const MG_CSRF = "{{ csrf_token() }}";
        const mgEscape = value => $('<div>').text(value ?? '').html();

        $(function () {
            const table = $('#mg-table').DataTable({
                processing: true,
                serverSide: true,
                ordering: false,
                searching: false,
                pageLength: 10,
                lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                dom: 'rt<"d-flex justify-content-between align-items-center flex-wrap"<"d-flex align-items-center"li>p>',
                language: {
                    lengthMenu: 'Item Per Page _MENU_',
                    info: 'Of _TOTAL_',
                    infoEmpty: 'Of 0',
                    zeroRecords: 'Belum ada group.',
                    emptyTable: 'Belum ada group.',
                    processing: '<i class="fas fa-spinner fa-spin"></i>',
                    paginate: { previous: '<i class="fas fa-chevron-left"></i>', next: '<i class="fas fa-chevron-right"></i>' }
                },
                ajax: MG_ROUTES.index,
                columns: [
                    { data: 'id', className: 'mg-col-check', render: id => `<input type="checkbox" class="mg-row-check" value="${id}">` },
                    { data: 'DT_RowIndex', className: 'mg-col-no' },
                    { data: 'kode_group', render: mgEscape },
                    { data: 'nama_group', render: mgEscape },
                    {
                        data: null,
                        render: () => '<button type="button" class="mkt-ico mkt-ico--edit is-edit" title="Edit" data-tip="Edit"><i class="far fa-edit"></i></button>'
                            + '<button type="button" class="mkt-ico mkt-ico--delete is-delete" title="Delete" data-tip="Delete"><i class="fas fa-trash"></i></button>'
                    }
                ]
            }).on('draw.dt', () => $('#mg-check-all').prop('checked', false));

            $(document).on('select2:open', () => document.querySelector('.select2-container--open .select2-search__field')?.focus());

            $('#mg-check-all').on('change', function () { $('#mg-table .mg-row-check').prop('checked', this.checked); });

            const openForm = function (row) {
                $('#mg-modal-title').text(row ? 'Edit Group' : 'Create New Group');
                $('#mg-id').val(row ? row.id : '');
                $('#mg-kode').val(row ? row.kode_group : '');
                $('#mg-nama').val(row ? row.nama_group : '');
                $('#mg-modal').modal('show');
            };
            $('#mg-modal').on('shown.bs.modal', () => $('#mg-kode').trigger('focus'));
            $('#mg-cancel').on('click', () => $('#mg-modal').modal('hide'));
            $('#mg-add').on('click', () => openForm(null));
            $('#mg-table').on('click', '.is-edit', function () { openForm(table.row($(this).closest('tr')).data()); });

            $('#mg-form').on('submit', function (event) {
                event.preventDefault();
                const id = $('#mg-id').val();
                const payload = {
                    _token: MG_CSRF,
                    kode_group: $('#mg-kode').val().trim(),
                    nama_group: $('#mg-nama').val().trim()
                };
                if (!payload.kode_group || !payload.nama_group) {
                    Swal.fire('Perhatian', 'Group Code and Group Name wajib diisi.', 'warning');
                    return;
                }
                if (id) payload._method = 'PUT';

                $('#mg-save').prop('disabled', true);
                $.ajax({ url: id ? MG_ROUTES.update.replace(':id', id) : MG_ROUTES.store, method: 'POST', data: payload })
                    .done(function (res) {
                        $('#mg-modal').modal('hide');
                        Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 1500, showConfirmButton: false });
                        table.ajax.reload(null, false);
                    })
                    .fail(showError)
                    .always(() => $('#mg-save').prop('disabled', false));
            });

            $('#mg-table').on('click', '.is-delete', function () {
                const row = table.row($(this).closest('tr')).data();
                Swal.fire({
                    icon: 'warning',
                    title: 'Hapus group?',
                    text: `${row.kode_group} - ${row.nama_group}`,
                    showCancelButton: true,
                    confirmButtonText: 'Hapus',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#e11d48'
                }).then(function (result) {
                    if (!result.isConfirmed) return;
                    $.ajax({ url: MG_ROUTES.destroy.replace(':id', row.id), method: 'POST', data: { _token: MG_CSRF, _method: 'DELETE' } })
                        .done(function (res) {
                            Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 1500, showConfirmButton: false });
                            table.ajax.reload(null, false);
                        })
                        .fail(showError);
                });
            });

            function showError(xhr) {
                const errors = xhr.responseJSON?.errors;
                Swal.fire('Gagal', errors ? Object.values(errors).flat()[0] : (xhr.responseJSON?.message || 'Terjadi kesalahan.'), 'error');
            }
        });
    </script>
@endsection
