@extends('layouts.index')

@section('custom-link')
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
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
        .mg-table input[type="checkbox"] { width: 14px; height: 14px; accent-color: #334155; }
        .mg-action { display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; margin-right: 4px; padding: 0; border: 0; border-radius: 50%; color: #fff; font-size: 12px; }
        .mg-action.is-edit { background: #0ea5e9; }
        .mg-action.is-delete { background: #e11d48; }
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
            .mg-action { width: 26px; height: 26px; margin-right: 2px; }
            .mg-modal .modal-dialog { margin-left: auto; margin-right: auto; padding: 0 12px; }
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
                            <th style="width: 30px;"><input type="checkbox" id="mg-check-all" aria-label="Pilih semua"></th>
                            <th style="width: 50px;">No</th>
                            <th>Kode Group</th>
                            <th>Nama Group</th>
                            <th style="width: 80px;">Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
            <div class="text-right">
                <button type="button" class="mg-add" id="mg-add">Add New</button>
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
                    { data: 'id', className: 'text-center', render: id => `<input type="checkbox" class="mg-row-check" value="${id}">` },
                    { data: 'DT_RowIndex' },
                    { data: 'kode_group', render: mgEscape },
                    { data: 'nama_group', render: mgEscape },
                    {
                        data: null,
                        render: () => '<button type="button" class="mg-action is-edit" title="Edit"><i class="fas fa-pen"></i></button>'
                            + '<button type="button" class="mg-action is-delete" title="Hapus"><i class="fas fa-trash"></i></button>'
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
