@extends('layouts.index')

@section('custom-link')
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
    <style>
        .mc-page { padding: 0 0 28px; background: #f5f3f3; min-height: calc(100vh - 56px); color: #1e293b; }
        .mc-crumb { padding: 10px 14px; background: #284a63; color: #cbd5e1; font-size: 12px; letter-spacing: .2px; margin-bottom: 20px; }
        .mc-crumb b { color: #fff; font-weight: 500; }
        .mc-back { display: inline-flex; align-items: center; gap: 8px; margin: 12px 14px; color: #1e293b; font-size: 14px; font-weight: 700; text-decoration: none !important; }
        .mc-back i { padding: 4px 6px; border-radius: 5px; background: #a3b2c7; color: #fff; font-size: 10px; }
        .mc-card { margin: 0 14px; padding: 18px 20px 24px; border-radius: 8px; background: #fff; box-shadow: 0 5px 12px -3px rgba(15, 23, 42, .18), 0 2px 4px rgba(15, 23, 42, .07); }
        .mc-title { margin: 0; color: #1e3a5f; font-size: 22px; font-weight: 800; }
        .mc-title i { color: #64748b; font-size: 12px; vertical-align: super; }
        .mc-subtitle { display: flex; align-items: center; gap: 6px; margin: 4px 0 16px; color: #475569; font-size: 10px; font-weight: 600; }
        .mc-subtitle i { color: #94a3b8; }

        /* Search By + kata kunci */
        .mc-search { display: flex; width: 340px; max-width: 100%; margin-bottom: 18px; border: 1px solid #cbd5e1; border-radius: 6px; }
        .mc-search-by { position: relative; flex: 0 0 auto; border-right: 1px solid #cbd5e1; }
        .mc-search-by-btn { display: inline-flex; align-items: center; gap: 6px; height: 30px; padding: 0 10px; border: 0; border-radius: 6px 0 0 6px; background: #fff; color: #1e293b; font-size: 11px; font-weight: 600; }
        .mc-search-by-btn .fa-filter { font-size: 10px; }
        .mc-search-by-btn .fa-chevron-down { font-size: 8px; }
        .mc-search-by-btn.is-filtered { color: #1d4ed8; }
        .mc-search-menu { position: absolute; top: calc(100% + 4px); left: 0; z-index: 30; min-width: 130px; padding: 8px 12px; border-radius: 6px; background: #fff; box-shadow: 0 6px 18px rgba(15, 23, 42, .18); }
        .mc-search-menu label { display: flex; align-items: center; gap: 8px; margin: 0; padding: 3px 0; color: #1e293b; font-size: 11px; font-weight: 500 !important; cursor: pointer; }
        .mc-search-menu input { width: 13px; height: 13px; accent-color: #284a63; }
        .mc-keyword { position: relative; flex: 1; }
        .mc-keyword i { position: absolute; top: 50%; left: 10px; transform: translateY(-50%); color: #94a3b8; font-size: 11px; pointer-events: none; }
        .mc-keyword input { width: 100%; height: 30px; padding: 0 10px 0 28px; border: 0; border-radius: 0 6px 6px 0; font-size: 11px; outline: none; }

        .mc-layout { display: grid; grid-template-columns: 290px minmax(0, 1fr); gap: 16px; align-items: start; }

        /* Panel kiri: tab New Content / New Item */
        .mc-tabs { display: grid; grid-template-columns: 1fr 1fr; border-bottom: 1px solid #e2e8f0; }
        .mc-tab { padding: 8px 4px; border: 0; border-bottom: 2px solid transparent; background: none; color: #cbd5e1; font-family: Poppins, sans-serif; font-size: 13px; font-weight: 500; }
        .mc-tab.active { border-bottom-color: #284a63; color: #284a63; }
        .mc-form { display: none; padding: 14px 12px 10px; border-radius: 0 0 6px 6px; box-shadow: 0 4px 12px rgba(15, 23, 42, .1); }
        .mc-form.active { display: block; }
        .mc-form label { display: block; margin-bottom: 6px; color: #1e293b; font-family: Poppins, sans-serif; font-size: 14px; font-weight: 600 !important; }
        .mc-form .form-control { height: 42px; border-radius: 6px; font-family: Poppins, sans-serif; font-size: 12px; }
        .mc-form .form-control:disabled { background: #f1f5f9; }
        .mc-form .select2-container--bootstrap4 .select2-selection { min-height: 42px; border-radius: 6px; font-family: Poppins, sans-serif; font-size: 12px; }
        .mc-form .select2-container--bootstrap4 .select2-selection__rendered { line-height: 40px; }
        .mc-form .form-group { margin-bottom: 14px; }
        .mc-form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0 12px; }
        .mc-form-note { margin: -6px 0 10px; color: #64748b; font-size: 10px; }
        .mc-form-actions { display: flex; justify-content: center; gap: 14px; margin-top: 26px; }
        .mc-btn { min-width: 104px; height: 40px; border: 0; border-radius: 20px; color: #fff; font-size: 14px; font-weight: 600; }
        .mc-btn.is-cancel { background: #ff0000; box-shadow: 0 4px 10px rgba(255, 0, 0, .3); }
        .mc-btn.is-save { background: #198038; box-shadow: 0 4px 10px rgba(25, 128, 56, .3); }
        .mc-btn:disabled { opacity: .6; }

        /* Tabel ala Excel */
        .mc-legend { display: flex; gap: 12px; margin-bottom: 6px; color: #64748b; font-size: 9px; }
        .mc-legend span { display: inline-flex; align-items: center; }
        .mc-dot { display: inline-block; width: 6px; height: 6px; margin-right: 4px; border-radius: 50%; vertical-align: middle; }
        .mc-dot.is-group { background: #7c3aed; }
        .mc-dot.is-input { background: #f97316; }
        .mc-table-box { padding: 6px 8px 8px; border-radius: 8px; background: #fff; box-shadow: 0 3px 12px rgba(30, 41, 59, .09); }
        .mc-table-wrap { overflow-x: auto; }
        .mc-table-wrap::-webkit-scrollbar { height: 8px; }
        .mc-table-wrap::-webkit-scrollbar-track { background: #e2e8f0; border-radius: 4px; }
        .mc-table-wrap::-webkit-scrollbar-thumb { background: #284a63; border-radius: 4px; }
        .mc-table { width: 100%; min-width: 1000px; margin: 0; border-collapse: collapse; font-family: Inter, sans-serif; }
        .mc-table thead th { padding: 8px; background: #cfe0ff; color: #1e293b; font-size: 12px !important; font-weight: 500; text-align: left; text-transform: uppercase; white-space: nowrap; }
        .mc-table thead th:first-child { border-radius: 6px 0 0 0; }
        .mc-table thead th:last-child { border-radius: 0 6px 0 0; text-align: center; }
        .mc-table tbody td { height: 34px; padding: 4px 8px; border-bottom: 1px solid #edf1f5; color: #1e293b; font-size: 14px !important; font-weight: 500; white-space: nowrap; vertical-align: middle; }
        .mc-table tbody td.mc-group-cell { border-right: 2px solid #fff; border-left: 2px solid #fff; background: #f1f4f8; }
        .mc-table tbody td.mc-wide { min-width: 180px; }
        .mc-table tbody td.mc-off { color: #94a3b8; }
        .mc-table input[type="checkbox"] { width: 14px; height: 14px; accent-color: #334155; }
        .mc-action { display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; margin: 0 2px; padding: 0; border: 0; border-radius: 50%; color: #fff; font-size: 12px; }
        .mc-action.is-edit { background: #17b3d4; }
        .mc-action.is-delete { background: #e5173f; }
        .mc-empty-row td { padding: 26px; color: #94a3b8; text-align: center; }

        .mc-pager { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 10px; padding: 12px 6px 4px; color: #1e293b; font-family: Inter, sans-serif; font-size: 12px; }
        .mc-pager-size { display: inline-flex; align-items: center; gap: 10px; }
        .mc-pager select { height: 32px; min-width: 64px; padding: 0 8px; border: 1px solid #94a3b8; border-radius: 6px; font-size: 13px; }
        .mc-pages { display: inline-flex; align-items: center; gap: 6px; }
        .mc-page-btn { min-width: 28px; height: 28px; padding: 0 6px; border: 0; border-radius: 4px; background: transparent; color: #64748b; font-size: 13px; }
        .mc-page-btn.active { background: #284a63; color: #fff; font-weight: 600; }
        .mc-page-btn:disabled { opacity: .35; }
        .mc-page-gap { min-width: 20px; color: #94a3b8; text-align: center; }


        @media (max-width: 991.98px) {
            .mc-layout { grid-template-columns: 1fr; }
        }
    </style>
@endsection

@section('content')
<div class="mc-page">
    <div class="mc-card">
        <div class="mc-crumb">Marketing &nbsp;›&nbsp; Master &nbsp;›&nbsp; <b>Master Content</b></div>
        <h1 class="mc-title">Content & Items List <i class="fas fa-info-circle" title="Content di bawah group, item di bawah content"></i></h1>
        <p class="mc-subtitle"><i class="fas fa-database"></i> List Data Master Content & Items</p>
        <div class="mc-search">
            <div class="mc-search-by">
                <button type="button" class="mc-search-by-btn" id="mc-search-by-btn" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-filter"></i> Search By <i class="fas fa-chevron-down"></i>
                </button>
                <div class="mc-search-menu" id="mc-search-menu" hidden>
                    <label><input type="checkbox" value="all"> ALL</label>
                    <label><input type="checkbox" value="group"> GROUP</label>
                    <label><input type="checkbox" value="content"> CONTENT</label>
                    <label><input type="checkbox" value="item"> ITEMS</label>
                </div>
            </div>
            <div class="mc-keyword">
                <i class="fas fa-search"></i>
                <input type="search" id="mc-keyword" placeholder="Search">
            </div>
        </div>

        <div class="mc-layout">
            <aside>
                <div class="mc-tabs" role="tablist">
                    <button type="button" class="mc-tab active" data-tab="content">New Content</button>
                    <button type="button" class="mc-tab" data-tab="item">New Item</button>
                </div>

                <form class="mc-form active" id="mc-content-form" data-tab="content" novalidate>
                    <p class="mc-form-note" hidden></p>
                    <div class="form-group">
                        <label for="mc-content-group">Nama Group</label>
                        <select id="mc-content-group" class="form-control">
                            <option value="">Nama Group</option>
                            @foreach ($groups as $group)
                                <option value="{{ $group->id }}">{{ $group->nama_group }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mb-0">
                        <label for="mc-content-name">Nama Content</label>
                        <input type="text" id="mc-content-name" class="form-control" maxlength="250" placeholder="Nama Content">
                    </div>
                </form>

                <form class="mc-form" id="mc-item-form" data-tab="item" novalidate>
                    <p class="mc-form-note" hidden></p>
                    <div class="form-group">
                        <label for="mc-item-group">Group Name</label>
                        <select id="mc-item-group" class="form-control">
                            <option value="">Group Name</option>
                            @foreach ($groups as $group)
                                <option value="{{ $group->id }}">{{ $group->nama_group }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="mc-item-content">Content Name</label>
                        <select id="mc-item-content" class="form-control"><option value="">Content Name</option></select>
                    </div>
                    <div class="form-group">
                        <label for="mc-item-name">Nama Item</label>
                        <input type="text" id="mc-item-name" class="form-control" maxlength="500" placeholder="Nama Item">
                    </div>
                    <div class="mc-form-grid">
                        <div class="form-group">
                            <label for="mc-item-size">Size</label>
                            <input type="text" id="mc-item-size" class="form-control" maxlength="50" placeholder="Size">
                        </div>
                        <div class="form-group">
                            <label for="mc-item-color">Color</label>
                            <input type="text" id="mc-item-color" class="form-control" maxlength="50" placeholder="Color">
                        </div>
                        <div class="form-group">
                            <label for="mc-item-unit">Unit</label>
                            <select id="mc-item-unit" class="form-control">
                                <option value="">Unit</option>
                                @foreach ($units as $unit)
                                    <option value="{{ $unit }}">{{ $unit }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="mc-item-weight">Weight</label>
                            <input type="text" inputmode="decimal" id="mc-item-weight" class="form-control" placeholder="Weight">
                        </div>
                        <div class="form-group mb-0">
                            <label for="mc-item-width">Width</label>
                            <input type="text" id="mc-item-width" class="form-control" maxlength="50" placeholder="Width">
                        </div>
                        <div class="form-group mb-0">
                            <label for="mc-item-length">Length</label>
                            <input type="text" id="mc-item-length" class="form-control" maxlength="50" placeholder="Length">
                        </div>
                    </div>
                </form>

                <div class="mc-form-actions">
                    <button type="button" class="mc-btn is-cancel" id="mc-cancel">Cancel</button>
                    <button type="button" class="mc-btn is-save" id="mc-save">Save</button>
                </div>
            </aside>

            <section>
                <div class="mc-legend">
                    <span><span class="mc-dot is-group"></span>Master Group</span>
                    <span><span class="mc-dot is-input"></span>Input</span>
                </div>
                <div class="mc-table-box">
                    <div class="mc-table-wrap">
                        <table class="mc-table" id="mc-table">
                            <thead>
                                <tr>
                                    <th style="width: 30px;"><input type="checkbox" id="mc-check-all" aria-label="Pilih semua"></th>
                                    <th style="width: 40px;">No</th>
                                    <th><span class="mc-dot is-group"></span>Nama Group</th>
                                    <th><span class="mc-dot is-input"></span>Content</th>
                                    <th><span class="mc-dot is-input"></span>Items</th>
                                    <th><span class="mc-dot is-input"></span>Size</th>
                                    <th><span class="mc-dot is-input"></span>Color</th>
                                    <th><span class="mc-dot is-input"></span>Unit</th>
                                    <th><span class="mc-dot is-input"></span>Width</th>
                                    <th><span class="mc-dot is-input"></span>Length</th>
                                    <th><span class="mc-dot is-input"></span>Weight</th>
                                    <th style="width: 70px;">Action</th>
                                </tr>
                            </thead>
                            <tbody id="mc-tbody"><tr class="mc-empty-row"><td colspan="12"><i class="fas fa-spinner fa-spin"></i></td></tr></tbody>
                        </table>
                    </div>
                    <div class="mc-pager">
                        <div class="mc-pager-size">
                            <span>Item Per Page</span>
                            <select id="mc-per-page">
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                            <span id="mc-total">Of 0</span>
                        </div>
                        <div class="mc-pages" id="mc-pages"></div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>
@endsection

@section('custom-script')
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        const MC_ROUTES = {
            rows: "{{ route('marketing-master-content-rows') }}",
            contents: "{{ route('marketing-master-content-list') }}",
            contentStore: "{{ route('marketing-master-content-store') }}",
            contentUpdate: "{{ route('marketing-master-content-update', ':id') }}",
            contentDestroy: "{{ route('marketing-master-content-destroy', ':id') }}",
            itemStore: "{{ route('marketing-master-item-store') }}",
            itemUpdate: "{{ route('marketing-master-item-update', ':id') }}",
            itemDestroy: "{{ route('marketing-master-item-destroy', ':id') }}"
        };
        const MC_CSRF = "{{ csrf_token() }}";
        const MC_COLS = ['nm_contents', 'itemdesc', 'size', 'color', 'kd_satuan', 'width', 'length', 'gramasi'];
        const mcEscape = value => $('<div>').text(value ?? '').html();

        const mcState = { page: 1, perPage: 10, total: 0, rows: [] };
        let mcGroups = [];          // group -> contents (untuk dropdown Content Name)
        let mcEditing = null;       // { type: 'content' | 'item', row } saat form kiri dipakai untuk edit

        $(function () {
            $('#mc-content-group').select2({ theme: 'bootstrap4', width: '100%', placeholder: 'Nama Group' });
            $('#mc-item-group').select2({ theme: 'bootstrap4', width: '100%', placeholder: 'Group Name' });
            $('#mc-item-content').select2({ theme: 'bootstrap4', width: '100%', placeholder: 'Content Name' });
            $('#mc-item-unit').select2({ theme: 'bootstrap4', width: '100%', placeholder: 'Unit' });
            $(document).on('select2:open', () => document.querySelector('.select2-container--open .select2-search__field')?.focus());

            /* ---------- data ---------- */
            function loadRows() {
                return $.get(MC_ROUTES.rows, {
                    page: mcState.page,
                    per_page: mcState.perPage,
                    search_by: searchBy(),
                    keyword: $('#mc-keyword').val().trim()
                }).done(function (res) {
                    Object.assign(mcState, { page: res.page, perPage: res.per_page, total: res.total, rows: res.rows });
                    renderRows();
                    renderPager();
                }).fail(() => $('#mc-tbody').html('<tr class="mc-empty-row"><td colspan="12" class="text-danger">Gagal memuat data.</td></tr>'));
            }

            function loadContents() {
                return $.get(MC_ROUTES.contents).done(res => { mcGroups = res.groups || []; });
            }

            /* ---------- render tabel ---------- */
            function cellText(row, field) {
                const value = row[field];
                if (value === null || value === undefined || String(value).trim() === '') return '--';
                if (field === 'gramasi') return Number(value).toLocaleString('en-US', { maximumFractionDigits: 4 });
                return value;
            }

            function cellHtml(row, field) {
                // Baris content tanpa item: kolom item kosong (tambah item lewat form New Item).
                const filled = field === 'nm_contents' || row.id_item;
                const wide = field === 'nm_contents' || field === 'itemdesc' ? ' mc-wide' : '';
                return filled ? `<td class="${wide.trim()}">${mcEscape(cellText(row, field))}</td>` : `<td class="mc-off${wide}">--</td>`;
            }

            function renderRows() {
                $('#mc-check-all').prop('checked', false);
                if (!mcState.rows.length) {
                    $('#mc-tbody').html('<tr class="mc-empty-row"><td colspan="12">Belum ada data.</td></tr>');
                    return;
                }
                const start = (mcState.page - 1) * mcState.perPage;
                $('#mc-tbody').html(mcState.rows.map((row, index) => `
                    <tr data-index="${index}">
                        <td class="text-center"><input type="checkbox" class="mc-row-check"></td>
                        <td>${start + index + 1}</td>
                        <td class="mc-group-cell">${mcEscape(row.nama_group)}</td>
                        ${MC_COLS.map(field => cellHtml(row, field)).join('')}
                        <td class="text-center">
                            <button type="button" class="mc-action is-edit" title="Edit"><i class="fas fa-pen"></i></button> <button type="button" class="mc-action is-delete" title="Hapus"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>`).join(''));
            }

            function renderPager() {
                const pages = Math.max(1, Math.ceil(mcState.total / mcState.perPage));
                const page = mcState.page;
                $('#mc-per-page').val(String(mcState.perPage));
                $('#mc-total').text('Of ' + mcState.total.toLocaleString('en-US'));
                const shown = new Set([1, 2, 3, pages - 2, pages - 1, pages, page - 1, page, page + 1]);
                let html = `<button type="button" class="mc-page-btn" data-page="${page - 1}" ${page <= 1 ? 'disabled' : ''}><i class="fas fa-chevron-left"></i></button>`;
                let last = 0;
                for (let p = 1; p <= pages; p++) {
                    if (!shown.has(p)) continue;
                    if (p - last > 1) html += '<span class="mc-page-gap">&hellip;</span>';
                    html += `<button type="button" class="mc-page-btn ${p === page ? 'active' : ''}" data-page="${p}">${p}</button>`;
                    last = p;
                }
                html += `<button type="button" class="mc-page-btn" data-page="${page + 1}" ${page >= pages ? 'disabled' : ''}><i class="fas fa-chevron-right"></i></button>`;
                $('#mc-pages').html(html);
            }

            const rowOf = $el => mcState.rows[Number($el.closest('tr').data('index'))];

            /* ---------- tabel: aksi, paging, search ---------- */
            $('#mc-tbody').on('click', '.is-edit', function () {
                const row = rowOf($(this));
                row.id_item ? editItem(row) : editContent(row);
            });
            $('#mc-tbody').on('click', '.is-delete', function () {
                const row = rowOf($(this));
                const url = row.id_item ? MC_ROUTES.itemDestroy.replace(':id', row.id_item) : MC_ROUTES.contentDestroy.replace(':id', row.id_contents);
                const label = row.id_item ? `item ${row.itemdesc}` : `content ${row.nm_contents}`;
                Swal.fire({ icon: 'warning', title: `Hapus ${label}?`, showCancelButton: true, confirmButtonText: 'Hapus', cancelButtonText: 'Batal', confirmButtonColor: '#e11d48' })
                    .then(result => {
                        if (!result.isConfirmed) return;
                        request(url, 'DELETE', {}).done(res => { toast(res.message); loadRows(); loadContents(); }).fail(showError);
                    });
            });
            $('#mc-pages').on('click', '.mc-page-btn:not(:disabled)', function () {
                mcState.page = Number($(this).data('page'));
                loadRows();
            });
            $('#mc-per-page').on('change', function () {
                mcState.perPage = Number(this.value);
                mcState.page = 1;
                loadRows();
            });
            let searchTimer = null;
            $('#mc-keyword').on('input', function () {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(() => { mcState.page = 1; loadRows(); }, 350);
            });
            // Search By: tidak ada yang dicentang = cari di semua kolom. ALL mencentang/mengosongkan semuanya.
            function searchBy() {
                return $('#mc-search-menu input:checked').not('[value="all"]').map((i, el) => el.value).get();
            }
            $('#mc-search-by-btn').on('click', function () {
                const $menu = $('#mc-search-menu');
                $menu.prop('hidden', !$menu.prop('hidden'));
                $(this).attr('aria-expanded', String(!$menu.prop('hidden')));
            });
            $(document).on('mousedown', function (event) {
                if (!$(event.target).closest('.mc-search-by').length) {
                    $('#mc-search-menu').prop('hidden', true);
                    $('#mc-search-by-btn').attr('aria-expanded', 'false');
                }
            });
            $('#mc-search-menu').on('change', 'input', function () {
                const $fields = $('#mc-search-menu input').not('[value="all"]');
                if (this.value === 'all') $fields.prop('checked', this.checked);
                $('#mc-search-menu input[value="all"]').prop('checked', $fields.length === $fields.filter(':checked').length);
                $('#mc-search-by-btn').toggleClass('is-filtered', searchBy().length > 0);
                mcState.page = 1;
                loadRows();
            });
            $('#mc-check-all').on('change', function () { $('#mc-tbody .mc-row-check').prop('checked', this.checked); });

            /* ---------- form kiri: New Content / New Item ---------- */
            function showTab(tab) {
                $('.mc-tab').removeClass('active').filter(`[data-tab="${tab}"]`).addClass('active');
                $('.mc-form').removeClass('active').filter(`[data-tab="${tab}"]`).addClass('active');
            }

            function fillContentOptions(groupId, selected) {
                const group = mcGroups.find(g => String(g.id) === String(groupId));
                const $select = $('#mc-item-content').empty().append('<option value=""></option>');
                (group ? group.contents : []).forEach(content => $select.append(new Option(content.nm_contents, content.id_contents)));
                $select.val(selected ? String(selected) : '').trigger('change.select2');
            }

            function resetForms() {
                mcEditing = null;
                $('.mc-tab[data-tab="content"]').text('New Content');
                $('.mc-tab[data-tab="item"]').text('New Item');
                $('.mc-form-note').prop('hidden', true).text('');
                $('#mc-content-group').prop('disabled', false).val('').trigger('change.select2');
                $('#mc-content-name').val('');
                $('#mc-item-group, #mc-item-content').prop('disabled', false);
                $('#mc-item-group').val('').trigger('change.select2');
                fillContentOptions('', null);
                $('#mc-item-name, #mc-item-size, #mc-item-color, #mc-item-weight, #mc-item-width, #mc-item-length').val('');
                $('#mc-item-unit').val('').trigger('change.select2');
            }

            function editContent(row) {
                resetForms();
                mcEditing = { type: 'content', row };
                showTab('content');
                $('.mc-tab[data-tab="content"]').text('Edit Content');
                // Content lama (dari native, punya id_type) hanya boleh ganti nama.
                $('#mc-content-group').val(String(row.id_group)).prop('disabled', Boolean(row.id_type)).trigger('change.select2');
                $('#mc-content-name').val(row.nm_contents).trigger('focus');
                if (row.id_type) $('#mc-content-form .mc-form-note').prop('hidden', false).text('Content lama: group tidak bisa diganti.');
            }

            function editItem(row) {
                resetForms();
                mcEditing = { type: 'item', row };
                showTab('item');
                $('.mc-tab[data-tab="item"]').text('Edit Item');
                $('#mc-item-group').val(String(row.id_group)).trigger('change.select2');
                fillContentOptions(row.id_group, row.id_contents);
                $('#mc-item-group, #mc-item-content').prop('disabled', true);
                $('#mc-item-name').val(row.itemdesc);
                $('#mc-item-size').val(row.size ?? '');
                $('#mc-item-color').val(row.color ?? '');
                $('#mc-item-unit').val(row.kd_satuan ?? '').trigger('change.select2');
                $('#mc-item-weight').val(row.gramasi !== null && row.gramasi !== undefined ? Number(row.gramasi) : '');
                $('#mc-item-width').val(row.width ?? '');
                $('#mc-item-length').val(row.length ?? '');
                $('#mc-item-name').trigger('focus');
            }

            $('.mc-tab').on('click', function () {
                if (mcEditing && mcEditing.type !== $(this).data('tab')) resetForms();
                showTab($(this).data('tab'));
            });
            $('#mc-item-group').on('change', function () { fillContentOptions(this.value, null); });
            $('#mc-cancel').on('click', resetForms);

            $('#mc-save').on('click', function () {
                const tab = $('.mc-tab.active').data('tab');
                const $btn = $(this).prop('disabled', true);
                const done = () => $btn.prop('disabled', false);
                let call;

                if (tab === 'content') {
                    const payload = { id_group: $('#mc-content-group').val(), nm_contents: $('#mc-content-name').val().trim() };
                    if (!payload.id_group || !payload.nm_contents) { done(); Swal.fire('Perhatian', 'Nama Group dan Nama Content wajib diisi.', 'warning'); return; }
                    const editing = mcEditing && mcEditing.type === 'content' ? mcEditing.row : null;
                    call = editing ? request(MC_ROUTES.contentUpdate.replace(':id', editing.id_contents), 'PUT', payload) : request(MC_ROUTES.contentStore, 'POST', payload);
                } else {
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
                    if (!payload.id_contents || !payload.itemdesc || !payload.kd_satuan) { done(); Swal.fire('Perhatian', 'Content, Nama Item, dan Unit wajib diisi.', 'warning'); return; }
                    if (weight !== '' && (isNaN(Number(weight)) || Number(weight) < 0)) { done(); Swal.fire('Perhatian', 'Weight harus berupa angka.', 'warning'); return; }
                    const editing = mcEditing && mcEditing.type === 'item' ? mcEditing.row : null;
                    call = editing ? request(MC_ROUTES.itemUpdate.replace(':id', editing.id_item), 'PUT', payload) : request(MC_ROUTES.itemStore, 'POST', payload);
                }

                call.done(res => { toast(res.message); resetForms(); loadRows(); loadContents(); }).fail(showError).always(done);
            });

            /* ---------- helper ---------- */
            function request(url, method, payload) {
                return $.ajax({ url: url, method: 'POST', data: Object.assign({ _token: MC_CSRF, _method: method }, payload) });
            }

            function toast(message) {
                Swal.fire({ icon: 'success', title: 'Berhasil', text: message, timer: 1500, showConfirmButton: false });
            }

            function showError(xhr) {
                const errors = xhr.responseJSON?.errors;
                Swal.fire('Gagal', errors ? Object.values(errors).flat()[0] : (xhr.responseJSON?.message || 'Terjadi kesalahan.'), 'error');
            }

            loadContents();
            loadRows();
        });
    </script>
@endsection
