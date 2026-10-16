@extends('layouts.index')

@section('custom-link')
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
    <style>
        .mc-page { padding: 10px 14px 28px; background: #f8fafc; min-height: calc(100vh - 56px); color: #1e293b; }
        .mc-crumb { margin-bottom: 12px; padding: 9px 12px; border-radius: 4px; background: #1e293b; color: #cbd5e1; font-size: 12px; letter-spacing: .2px; }
        .mc-crumb b { color: #fff; }
        .mc-card { padding: 20px 24px 24px; border-radius: 8px; background: #fff; box-shadow: 0 5px 12px -3px rgba(15, 23, 42, .18), 0 2px 4px rgba(15, 23, 42, .07); }
        .mc-title { margin: 0; color: #1e3a5f; font-size: 24px; font-weight: 800; }
        .mc-title i { color: #64748b; font-size: 12px; vertical-align: super; }
        .mc-subtitle { margin: 4px 0 18px; color: #475569; font-size: 11px; font-weight: 600; }

        /* --mc-aside-width = lebar panel Choose Content; bisa diubah dengan drag .mc-resizer. */
        .mc-layout { --mc-aside-width: 300px; display: grid; grid-template-columns: var(--mc-aside-width) 12px minmax(0, 1fr); gap: 4px; align-items: stretch; }
        .mc-layout > .mc-panel { align-self: start; }
        .mc-resizer { position: relative; cursor: col-resize; touch-action: none; }
        .mc-resizer::before { content: ""; position: absolute; top: 0; bottom: 0; left: 50%; width: 2px; border-radius: 2px; background: #e2e8f0; transform: translateX(-50%); transition: background .15s; }
        .mc-resizer:hover::before, .mc-resizer.is-dragging::before { background: #3b82f6; }
        body.mc-resizing { cursor: col-resize; user-select: none; }
        .mc-panel { padding: 10px 12px 14px; border: 1px solid #e2e8f0; border-radius: 10px; box-shadow: 0 4px 14px rgba(15, 23, 42, .06); }
        .mc-panel-title { display: flex; align-items: center; gap: 6px; margin-bottom: 8px; color: #1e293b; font-size: 12px; font-weight: 700; }
        .mc-step { display: inline-flex; align-items: center; justify-content: center; width: 16px; height: 16px; border-radius: 50%; background: #1e3a5f; color: #fff; font-size: 9px; }
        .mc-search { position: relative; margin-bottom: 8px; }
        .mc-search input { height: 28px; padding-right: 26px; font-size: 11px; }
        .mc-search i { position: absolute; top: 50%; right: 9px; transform: translateY(-50%); color: #94a3b8; font-size: 11px; pointer-events: none; }

        .mc-new-content { display: flex; align-items: center; gap: 6px; width: 100%; margin-bottom: 10px; padding: 5px 8px; border: 1px solid #cbd5e1; border-radius: 6px; background: #f1f5f9; color: #1e293b; font-size: 11px; font-weight: 600; }
        .mc-new-content i { color: #1e3a5f; }
        .mc-tree { max-height: 520px; overflow-y: auto; font-size: 11px; }
        .mc-group-name { margin: 8px 0 3px; color: #64748b; font-weight: 600; }
        .mc-content { display: flex; align-items: center; gap: 4px; padding: 3px 6px 3px 12px; border-radius: 4px; cursor: pointer; }
        .mc-content:hover { background: #f1f5f9; }
        .mc-content.active { background: #cfe0ff; font-weight: 700; }
        .mc-content-name { flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .mc-content-tools { display: none; gap: 2px; }
        .mc-content:hover .mc-content-tools, .mc-content.active .mc-content-tools { display: inline-flex; }
        .mc-content-tools button { padding: 0 2px; border: 0; background: none; color: #64748b; font-size: 10px; }
        .mc-content-tools button:hover { color: #1e3a5f; }
        .mc-content-tools .is-delete:hover { color: #e11d48; }
        .mc-count { min-width: 16px; padding: 0 5px; border-radius: 8px; background: #e2e8f0; color: #475569; font-size: 9px; text-align: center; }
        .mc-content.active .mc-count { background: #1e3a5f; color: #fff; }
        .mc-tree-empty { padding: 14px 4px; color: #94a3b8; text-align: center; }

        .mc-table { margin-bottom: 0; font-family: Inter, sans-serif; font-size: 14px; font-weight: 500; }
        .mc-table thead th { padding: 7px 10px; border: 0; background: #cfe0ff; color: #26394c; font-size: 12px; font-weight: 500; text-transform: uppercase; white-space: nowrap; }
        .mc-table tbody td { padding: 7px 10px; border-top: 0; border-bottom: 1px solid #eef2f7; vertical-align: middle; }
        .mc-table input[type="checkbox"] { width: 14px; height: 14px; accent-color: #334155; }
        .mc-item-meta { color: #94a3b8; font-size: 10px; }
        .mc-action { display: inline-flex; align-items: center; justify-content: center; width: 20px; height: 20px; margin-right: 4px; padding: 0; border: 0; border-radius: 50%; color: #fff; font-size: 9px; }
        .mc-action.is-edit { background: #0ea5e9; }
        .mc-action.is-delete { background: #e11d48; }
        .mc-empty { color: #94a3b8; }

        .mc-panel .dataTables_info, .mc-panel .dataTables_length, .mc-panel .dataTables_paginate { padding-top: 10px; color: #475569; font-size: 11px; }
        .mc-panel .dataTables_length select { height: 26px; margin: 0 6px; font-size: 11px; }
        .mc-panel .pagination { margin: 0; gap: 2px; }
        .mc-panel .page-link { min-width: 22px; padding: 3px 7px; border: 0; border-radius: 4px; color: #475569; font-size: 11px; text-align: center; }
        .mc-panel .page-item.active .page-link { background: #1e3a5f; color: #fff; }
        .mc-panel .page-item.disabled .page-link { background: none; color: #cbd5e1; }
        .mc-add { min-width: 110px; height: 34px; margin-top: 14px; border: 0; border-radius: 17px; background: #1e3a5f; color: #fff; font-size: 12px; font-weight: 600; box-shadow: 0 3px 8px rgba(30, 58, 95, .3); }

        .mc-modal .modal-content { border: 0; border-radius: 8px; }
        .mc-modal .modal-header { justify-content: center; border-bottom: 1px solid #e2e8f0; }
        .mc-modal .modal-title { color: #1e3a5f; font-size: 14px; font-weight: 700; }
        .mc-modal .mc-form { padding: 14px; border-radius: 6px; box-shadow: 0 2px 10px rgba(15, 23, 42, .1); }
        .mc-modal label { margin-bottom: 4px; color: #1e293b; font-family: Poppins, sans-serif; font-size: 14px; font-weight: 600; }
        .mc-modal .form-control { height: 40px; border-radius: 6px; font-family: Poppins, sans-serif; font-size: 12px; }
        .mc-modal .form-control[readonly] { background: #f1f5f9; color: #64748b; }
        .mc-modal .select2-container--bootstrap4 .select2-selection { min-height: 40px; border-radius: 6px; font-family: Poppins, sans-serif; font-size: 12px; }
        .mc-modal .modal-footer { justify-content: center; gap: 12px; border-top: 0; }
        .mc-btn { min-width: 110px; height: 34px; border: 0; border-radius: 17px; color: #fff; font-size: 12px; font-weight: 600; }
        .mc-btn.is-cancel { background: #e11d48; box-shadow: 0 3px 8px rgba(225, 29, 72, .3); }
        .mc-btn.is-save { background: #15803d; box-shadow: 0 3px 8px rgba(21, 128, 61, .3); }
        .mc-btn:disabled { opacity: .6; }

        @media (max-width: 767.98px) {
            .mc-layout { grid-template-columns: 1fr; }
            .mc-resizer { display: none; }
        }
    </style>
@endsection

@section('content')
<div class="mc-page">
    <div class="mc-card">
        <div class="mc-crumb">Marketing &nbsp;›&nbsp; Master &nbsp;›&nbsp; <b>Master Content</b></div>

        <h1 class="mc-title">Content & Items List <i class="fas fa-info-circle" title="Content di bawah group, dan item di bawah content"></i></h1>
        <p class="mc-subtitle"><i class="fas fa-database"></i> List Data Master Content & Items</p>

        <div class="mc-layout">
            <aside class="mc-panel">
                <div class="mc-panel-title"><span class="mc-step">1</span> Choose Content</div>
                <div class="mc-search">
                    <input type="search" class="form-control" id="mc-content-search" placeholder="Choose Content">
                    <i class="fas fa-search"></i>
                </div>
                <button type="button" class="mc-new-content" id="mc-content-add"><i class="fas fa-plus-circle"></i> Create New Content</button>
                <div class="mc-tree" id="mc-tree"><div class="mc-tree-empty"><i class="fas fa-spinner fa-spin"></i></div></div>
            </aside>
            <div class="mc-resizer" id="mc-resizer" role="separator" aria-orientation="vertical" title="Geser untuk ubah lebar, klik 2x untuk reset"></div>

            <section class="mc-panel">
                <div class="mc-panel-title"><span class="mc-step">2</span> List Item <span class="mc-item-meta" id="mc-selected-label"></span></div>
                <div class="mc-search" style="max-width: 240px;">
                    <input type="search" class="form-control" id="mc-item-search" placeholder="Choose Item">
                    <i class="fas fa-search"></i>
                </div>
                <div class="table-responsive">
                    <table class="table mc-table w-100" id="mc-item-table">
                        <thead>
                            <tr>
                                <th style="width: 30px;"><input type="checkbox" id="mc-check-all" aria-label="Pilih semua"></th>
                                <th style="width: 40px;">No</th>
                                <th>Kode Group</th>
                                <th>Nama Group</th>
                                <th>Material Type</th>
                                <th>Nama Content</th>
                                <th>Nama Items</th>
                                <th style="width: 70px;">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
                <div class="text-right">
                    <button type="button" class="mc-add" id="mc-item-add">Add New</button>
                </div>
            </section>
        </div>
    </div>
</div>

<div class="modal fade mc-modal" id="mc-content-modal" tabindex="-1" role="dialog" aria-labelledby="mc-content-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 360px;">
        <form class="modal-content" id="mc-content-form" novalidate>
            <div class="modal-header"><h5 class="modal-title" id="mc-content-title">Create New Content</h5></div>
            <div class="modal-body">
                <div class="mc-form">
                    <input type="hidden" id="mc-content-id">
                    <div class="form-group">
                        <label for="mc-content-group">Nama Group</label>
                        <select id="mc-content-group" class="form-control">
                            <option value="">Nama Group</option>
                            @foreach ($groups as $group)
                                <option value="{{ $group->id }}">{{ $group->kode_group }} - {{ $group->nama_group }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mb-0">
                        <label for="mc-content-name">Nama Content</label>
                        <input type="text" id="mc-content-name" class="form-control" maxlength="250" placeholder="Nama Content">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="mc-btn is-cancel" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="mc-btn is-save">Save</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade mc-modal" id="mc-item-modal" tabindex="-1" role="dialog" aria-labelledby="mc-item-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 520px;">
        <form class="modal-content" id="mc-item-form" novalidate>
            <div class="modal-header"><h5 class="modal-title" id="mc-item-title">Create New Item</h5></div>
            <div class="modal-body">
                <div class="mc-form">
                    <input type="hidden" id="mc-item-id">
                    <input type="hidden" id="mc-item-content">
                    <div class="row">
                        <div class="col-6 form-group">
                            <label for="mc-item-group-name">Group Name</label>
                            <input type="text" id="mc-item-group-name" class="form-control" placeholder="Group Name" readonly>
                        </div>
                        <div class="col-6 form-group">
                            <label for="mc-item-content-name">Content Name</label>
                            <input type="text" id="mc-item-content-name" class="form-control" placeholder="Content Name" readonly>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="mc-item-name">Nama Item</label>
                        <input type="text" id="mc-item-name" class="form-control" maxlength="500" placeholder="Nama Item">
                    </div>
                    <div class="row">
                        <div class="col-4 form-group">
                            <label for="mc-item-size">Size</label>
                            <input type="text" id="mc-item-size" class="form-control" maxlength="50" placeholder="Size">
                        </div>
                        <div class="col-4 form-group">
                            <label for="mc-item-color">Color</label>
                            <input type="text" id="mc-item-color" class="form-control" maxlength="50" placeholder="Color">
                        </div>
                        <div class="col-4 form-group">
                            <label for="mc-item-unit">Unit</label>
                            <select id="mc-item-unit" class="form-control">
                                <option value="">Unit</option>
                                @foreach ($units as $unit)
                                    <option value="{{ $unit }}">{{ $unit }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-4 form-group mb-0">
                            <label for="mc-item-width">Width</label>
                            <input type="text" id="mc-item-width" class="form-control" maxlength="50" placeholder="Width">
                        </div>
                        <div class="col-4 form-group mb-0">
                            <label for="mc-item-length">Length</label>
                            <input type="text" id="mc-item-length" class="form-control" maxlength="50" placeholder="Length">
                        </div>
                        <div class="col-4 form-group mb-0">
                            <label for="mc-item-weight">Weight</label>
                            <input type="text" inputmode="decimal" id="mc-item-weight" class="form-control" placeholder="Weight">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="mc-btn is-cancel" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="mc-btn is-save">Save</button>
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
        const MC_ROUTES = {
            contents: "{{ route('marketing-master-content-list') }}",
            contentStore: "{{ route('marketing-master-content-store') }}",
            contentUpdate: "{{ route('marketing-master-content-update', ':id') }}",
            contentDestroy: "{{ route('marketing-master-content-destroy', ':id') }}",
            items: "{{ route('marketing-master-item-list') }}",
            itemStore: "{{ route('marketing-master-item-store') }}",
            itemUpdate: "{{ route('marketing-master-item-update', ':id') }}",
            itemDestroy: "{{ route('marketing-master-item-destroy', ':id') }}"
        };
        const MC_CSRF = "{{ csrf_token() }}";
        const mcEscape = value => $('<div>').text(value ?? '').html();
        let mcGroups = [];
        let mcSelected = null; // content yang dipilih di panel kiri

        $(function () {
            const table = $('#mc-item-table').DataTable({
                processing: true,
                serverSide: true,
                ordering: false,
                searching: false,
                pageLength: 6,
                lengthMenu: [[6, 10, 25, 50], [6, 10, 25, 50]],
                dom: 'rt<"d-flex justify-content-between align-items-center flex-wrap"<"d-flex align-items-center"li>p>',
                language: {
                    lengthMenu: 'Item Per Page _MENU_',
                    info: 'Of _TOTAL_',
                    infoEmpty: 'Of 0',
                    zeroRecords: 'Belum ada item.',
                    emptyTable: 'Belum ada item.',
                    processing: '<i class="fas fa-spinner fa-spin"></i>',
                    paginate: { previous: '<i class="fas fa-chevron-left"></i>', next: '<i class="fas fa-chevron-right"></i>' }
                },
                ajax: {
                    url: MC_ROUTES.items,
                    data: function (d) {
                        d.id_contents = mcSelected ? mcSelected.id_contents : '';
                        d.keyword = $('#mc-item-search').val();
                    }
                },
                columns: [
                    { data: 'id_item', className: 'text-center', render: id => `<input type="checkbox" class="mc-row-check" value="${id}">` },
                    { data: 'DT_RowIndex' },
                    { data: 'kode_group', render: mcEscape },
                    { data: 'nama_group', render: mcEscape },
                    { data: 'mattype', render: (value, type, row) => value ? mcEscape(`${String(value).trim()} - ${row.mattype_label || ''}`) : '<span class="mc-empty">--</span>' },
                    { data: 'nm_contents', render: mcEscape },
                    {
                        data: 'itemdesc',
                        render: (value, type, row) => {
                            const meta = [row.size, row.color, row.kd_satuan].filter(Boolean).join(' · ');
                            return mcEscape(value) + (meta ? `<div class="mc-item-meta">${mcEscape(meta)}</div>` : '');
                        }
                    },
                    {
                        data: null,
                        render: () => '<button type="button" class="mc-action is-edit" title="Edit"><i class="fas fa-pen"></i></button>'
                            + '<button type="button" class="mc-action is-delete" title="Hapus"><i class="fas fa-trash"></i></button>'
                    }
                ]
            }).on('draw.dt', () => $('#mc-check-all').prop('checked', false));

            $('#mc-content-group').select2({ theme: 'bootstrap4', width: '100%', placeholder: 'Nama Group', dropdownParent: $('#mc-content-modal') });
            $('#mc-item-unit').select2({ theme: 'bootstrap4', width: '100%', placeholder: 'Unit', dropdownParent: $('#mc-item-modal') });            $(document).on('select2:open', () => document.querySelector('.select2-container--open .select2-search__field')?.focus());

            // ---------- Panel kiri: content ----------
            const loadContents = function () {
                return $.get(MC_ROUTES.contents).done(function (res) {
                    mcGroups = res.groups || [];
                    if (mcSelected && !findContent(mcSelected.id_contents)) mcSelected = null;
                    renderTree();
                    renderSelectedLabel();
                }).fail(() => $('#mc-tree').html('<div class="mc-tree-empty text-danger">Gagal memuat content.</div>'));
            };
            const findContent = id => mcGroups.flatMap(group => group.contents).find(content => String(content.id_contents) === String(id));
            const renderTree = function () {
                const keyword = $('#mc-content-search').val().trim().toLowerCase();
                const html = mcGroups.map(function (group) {
                    const contents = group.contents.filter(content => !keyword
                        || content.nm_contents.toLowerCase().includes(keyword)
                        || group.name.toLowerCase().includes(keyword));
                    if (!contents.length) return '';
                    return `<div class="mc-group-name">${mcEscape(group.name)}</div>` + contents.map(content => `
                        <div class="mc-content ${mcSelected && String(mcSelected.id_contents) === String(content.id_contents) ? 'active' : ''}" data-id="${content.id_contents}" title="${mcEscape(content.nm_contents)}">
                            <span class="mc-content-name">${mcEscape(content.nm_contents)}</span>
                            <span class="mc-content-tools">
                                <button type="button" class="is-edit" title="Edit content"><i class="fas fa-pen"></i></button>
                                <button type="button" class="is-delete" title="Hapus content"><i class="fas fa-trash"></i></button>
                            </span>
                            <span class="mc-count">${Number(content.item_count) || 0}</span>
                        </div>`).join('');
                }).join('');
                $('#mc-tree').html(html || `<div class="mc-tree-empty">${keyword ? 'Content tidak ditemukan.' : 'Belum ada content.'}</div>`);
            };
            const renderSelectedLabel = () => $('#mc-selected-label').text(mcSelected ? `— ${mcSelected.nama_group} › ${mcSelected.nm_contents}` : '— Semua content');

            $('#mc-content-search').on('input', renderTree);
            $('#mc-tree').on('click', '.mc-content', function (event) {
                if ($(event.target).closest('.mc-content-tools').length) return;
                const content = findContent($(this).data('id'));
                // Klik content yang sedang aktif = kembali ke semua content.
                mcSelected = mcSelected && String(mcSelected.id_contents) === String(content.id_contents) ? null : content;
                renderTree();
                renderSelectedLabel();
                table.ajax.reload();
            });

            const openContentForm = function (content) {
                $('#mc-content-title').text(content ? 'Edit Content' : 'Create New Content');
                $('#mc-content-id').val(content ? content.id_contents : '');
                // Content lama (punya id_type dari native) hanya boleh ganti nama, group-nya dikunci.
                $('#mc-content-group').val(content ? String(content.id_group) : '').prop('disabled', Boolean(content && content.id_type)).trigger('change.select2');
                $('#mc-content-name').val(content ? content.nm_contents : '');
                $('#mc-content-modal').modal('show');
            };
            $('#mc-content-add').on('click', () => openContentForm(null));
            $('#mc-tree').on('click', '.mc-content-tools .is-edit', function () { openContentForm(findContent($(this).closest('.mc-content').data('id'))); });
            $('#mc-tree').on('click', '.mc-content-tools .is-delete', function () {
                const content = findContent($(this).closest('.mc-content').data('id'));
                confirmDelete(`Hapus content ${content.nm_contents}?`, MC_ROUTES.contentDestroy.replace(':id', content.id_contents), function () {
                    loadContents();
                    table.ajax.reload(null, false);
                });
            });
            $('#mc-content-form').on('submit', function (event) {
                event.preventDefault();
                const id = $('#mc-content-id').val();
                const payload = { id_group: $('#mc-content-group').val(), nm_contents: $('#mc-content-name').val().trim() };
                if (!payload.id_group || !payload.nm_contents) {
                    Swal.fire('Perhatian', 'Nama Group dan Nama Content wajib diisi.', 'warning');
                    return;
                }
                submitForm($(this), id ? MC_ROUTES.contentUpdate.replace(':id', id) : MC_ROUTES.contentStore, id ? 'PUT' : 'POST', payload, function () {
                    $('#mc-content-modal').modal('hide');
                    loadContents();
                    table.ajax.reload(null, false);
                });
            });

            const openItemForm = function (item) {
                const content = item ? findContent(item.id_contents) || item : mcSelected;
                if (!content) {
                    Swal.fire('Pilih content dulu', 'Klik salah satu content di panel kiri sebelum menambah item.', 'info');
                    return;
                }
                $('#mc-item-title').text(item ? 'Edit Item' : 'Create New Item');
                $('#mc-item-id').val(item ? item.id_item : '');
                $('#mc-item-content').val(content.id_contents);
                $('#mc-item-group-name').val(content.nama_group);
                $('#mc-item-content-name').val(content.nm_contents);
                $('#mc-item-name').val(item ? item.itemdesc : '');
                $('#mc-item-size').val(item ? item.size : '');
                $('#mc-item-color').val(item ? item.color : '');
                $('#mc-item-unit').val(item ? item.kd_satuan : '').trigger('change.select2');
                $('#mc-item-width').val(item ? item.width : '');
                $('#mc-item-length').val(item ? item.length : '');
                $('#mc-item-weight').val(item && item.gramasi !== null ? Number(item.gramasi) : '');
                $('#mc-item-modal').modal('show');
            };
            $('#mc-item-add').on('click', () => openItemForm(null));
            $('#mc-item-table').on('click', '.is-edit', function () { openItemForm(table.row($(this).closest('tr')).data()); });
            $('#mc-item-table').on('click', '.is-delete', function () {
                const item = table.row($(this).closest('tr')).data();
                confirmDelete(`Hapus item ${item.itemdesc}?`, MC_ROUTES.itemDestroy.replace(':id', item.id_item), function () {
                    table.ajax.reload(null, false);
                    loadContents();
                });
            });
            $('#mc-item-form').on('submit', function (event) {
                event.preventDefault();
                const id = $('#mc-item-id').val();
                const weight = $('#mc-item-weight').val().trim().replace(',', '.');
                const payload = {
                    id_contents: $('#mc-item-content').val(),
                    itemdesc: $('#mc-item-name').val().trim(),
                    size: $('#mc-item-size').val().trim(),
                    color: $('#mc-item-color').val().trim(),
                    kd_satuan: $('#mc-item-unit').val(),
                    width: $('#mc-item-width').val().trim(),
                    length: $('#mc-item-length').val().trim(),
                    gramasi: weight
                };
                if (!payload.itemdesc || !payload.kd_satuan) {
                    Swal.fire('Perhatian', 'Nama Item dan Unit wajib diisi.', 'warning');
                    return;
                }
                if (weight !== '' && (isNaN(Number(weight)) || Number(weight) < 0)) {
                    Swal.fire('Perhatian', 'Weight harus berupa angka.', 'warning');
                    return;
                }
                submitForm($(this), id ? MC_ROUTES.itemUpdate.replace(':id', id) : MC_ROUTES.itemStore, id ? 'PUT' : 'POST', payload, function () {
                    $('#mc-item-modal').modal('hide');
                    table.ajax.reload(null, false);
                    loadContents();
                });
            });

            let itemSearchTimer = null;
            $('#mc-item-search').on('input', function () {
                clearTimeout(itemSearchTimer);
                itemSearchTimer = setTimeout(() => table.ajax.reload(), 350);
            });
            $('#mc-check-all').on('change', function () { $('#mc-item-table .mc-row-check').prop('checked', this.checked); });
            $('.mc-modal .is-cancel').on('click', function () { $(this).closest('.modal').modal('hide'); });

            function submitForm($form, url, method, payload, onSuccess) {
                const $save = $form.find('.is-save').prop('disabled', true);
                $.ajax({ url: url, method: 'POST', data: Object.assign({ _token: MC_CSRF, _method: method }, payload) })
                    .done(function (res) {
                        Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 1500, showConfirmButton: false });
                        onSuccess();
                    })
                    .fail(showError)
                    .always(() => $save.prop('disabled', false));
            }

            function confirmDelete(title, url, onSuccess) {
                Swal.fire({
                    icon: 'warning',
                    title: title,
                    showCancelButton: true,
                    confirmButtonText: 'Hapus',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#e11d48'
                }).then(function (result) {
                    if (!result.isConfirmed) return;
                    $.ajax({ url: url, method: 'POST', data: { _token: MC_CSRF, _method: 'DELETE' } })
                        .done(function (res) {
                            Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 1500, showConfirmButton: false });
                            onSuccess();
                        })
                        .fail(showError);
                });
            }

            function showError(xhr) {
                const errors = xhr.responseJSON?.errors;
                Swal.fire('Gagal', errors ? Object.values(errors).flat()[0] : (xhr.responseJSON?.message || 'Terjadi kesalahan.'), 'error');
            }

            loadContents();
        });
    </script>
@endsection
