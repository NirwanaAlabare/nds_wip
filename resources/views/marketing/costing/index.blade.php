@extends('layouts.index')

@section('custom-link')
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">

    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <style>
        :root {
            --sb-navy: #0f172a;
            --sb-blue: #3085d6;
            --sb-blue-dark: #1e3a8a;
            --sb-border: #e2e8f0;
        }

        /* ============ COSTING LIST — mengikuti desain referensi ============ */
        .costing-page { padding: 10px 14px 28px; background: #f5f3f3; min-height: calc(100vh - 56px); }
        .costing-crumb { background: #274b63; color: #dbe8ee; padding: 9px 10px; font-size: 11px; letter-spacing: .2px; }
        .costing-crumb b { color: #fff; }
        .costing-back { display: inline-flex; align-items: center; gap: 10px; margin: 12px 0 16px; color: #274b63; font-size: 14px; font-weight: 700; text-decoration: none; }
        .costing-back i { background: #adbdc8; border-radius: 5px; padding: 3px 5px; font-size: 11px; }
        .card-sb {
            border: 0;
            border-radius: 2px;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(30, 41, 59, 0.08);
        }

        .card-sb > .card-body {
            background: #fff;
            padding: 10px 16px 28px;
        }

        .costing-title { color: #274b63; font-size: 24px; font-weight: 700; margin: 0 0 10px; letter-spacing: .2px; }
        .list-shell { border: 1px solid #edf0f5; border-radius: 13px; padding: 12px 10px 10px; }
        .list-toolbar { display: flex; gap: 9px; align-items: center; margin-bottom: 6px; position: relative; }
        .toolbar-btn, .search-by, .toolbar-search { height: 27px; border: 1px solid #dce2e9; background: #fff; color: #30465a; font-size: 10px; }
        .toolbar-btn { border-radius: 6px; padding: 0 10px; font-weight: 600; }
        .search-by { border-radius: 5px; padding: 0 9px; }
        .toolbar-search { width: 220px; border-radius: 5px; padding: 0 9px; }
        .toolbar-search:focus { outline: 0; border-color: #7b61d8; }
        .search-menu { display: none; position: absolute; z-index: 1035; top: 28px; left: 39px; width: 145px; background: #fff; border-radius: 0 0 6px 6px; box-shadow: 0 4px 12px rgba(22, 34, 48, .18); overflow: visible; }
        .search-menu.show { display: block; } .search-menu button { width: 100%; border: 0; background: #fff; text-align: left; padding: 6px 10px; font-size: 10px; color: #34495a; }
        .search-menu button:hover, .search-menu button.active { background: #eeeeff; } .search-submenu { display: none; position: absolute; top: 0; left: 100%; width: 175px; max-height: 230px; overflow: auto; padding: 8px; background: #fff; border-radius: 0 6px 6px 0; box-shadow: 3px 4px 12px rgba(22, 34, 48, .16); }
        .search-submenu.show { display: block; } .search-submenu label { display: block; font-size: 10px; font-weight: 400; margin: 4px 0; } .search-submenu .form-control { font-size: 10px; height: 27px; }
        .search-submenu .buyer-search { margin: 5px 0; }
        #buyer-options { max-height: 145px; overflow-y: auto; }
        #buyer-options .buyer-option { display: block; }
        .filter-summary { min-height: 39px; color: #53687a; font-size: 9px; display: flex; align-items: flex-start; gap: 5px; }
        .filter-chip { display: inline-flex; align-items: center; padding: 3px 6px; margin: 4px 3px 0 0; background: #d9effb; color: #31556d; }
        .filter-chip-remove { border: 0; background: transparent; color: inherit; font-size: 12px; line-height: 1; padding: 0 0 0 5px; cursor: pointer; }
        .filter-chip-remove:hover { color: #a12622; }
        .clear-filters { font-size: 9px; line-height: 1.4; padding: 1px 4px; }

        /* ============ TABLE ============ */
        #table-costing thead tr {
            background: #f7f9fc;
        }
        #table-costing thead th {
            color: #26384a !important;
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            font-weight: 700;
            vertical-align: middle;
            border-color: #edf0f4;
        }
        #table-costing tbody td {
            font-size: 10px;
            vertical-align: middle;
        }
        #table-costing tbody tr:hover {
            background-color: #f6fbff;
        }

        /* ============ ACTION BUTTONS DI TABEL ============ */
        #table-costing { margin-top: 0 !important; }
        #table-costing tbody td { border-color: #f0f2f5; white-space: nowrap; padding: 9px 7px; }
        .action-round { width: 19px; height: 19px; border: 0; border-radius: 50%; color: #fff; display: inline-flex; align-items: center; justify-content: center; font-size: 9px; margin: 0 2px; }
        .action-edit { background: #2447bd; } .action-pdf { background: #00a2c8; } .action-excel { background: #e4a000; } .action-copy { background: #6200d1; }
        .add-new { float: right; margin-top: 10px; border-radius: 18px; background: #274b63; border: 0; padding: 7px 20px; font-size: 10px; font-weight: 700; box-shadow: 0 4px 8px #d5dce2; }
        #table-costing_wrapper .dataTables_filter { display: none; }
        #table-costing_wrapper .dataTables_length, #table-costing_wrapper .dataTables_info, #table-costing_wrapper .dataTables_paginate { font-size: 10px; color: #34495a; margin-top: 8px; }
        #table-costing_wrapper .dataTables_paginate .paginate_button { padding: 2px 7px; border: 0 !important; }
        .settings-backdrop { display: none; position: fixed; inset: 0; z-index: 1040; background: rgba(24, 32, 42, .42); }
        .table-settings-popover { display: none; position: fixed; z-index: 1050; top: 0; left: 0; width: 586px; max-width: calc(100vw - 16px); max-height: calc(100vh - 16px); background: #fff; border-radius: 8px; box-shadow: 0 12px 28px rgba(20, 31, 45, .28); overflow-y: auto; }
        .table-settings-popover.show, .settings-backdrop.show { display: block; }
        .settings-popover-title { padding: 14px 14px 8px; color: #202d3a; font-size: 13px; font-weight: 700; }
        .settings-popover-body { padding: 4px 14px 12px; font-size: 10px; color: #34495a; }
        .settings-label { display: block; margin: 10px 0 5px; font-size: 10px; color: #536171; }
        .column-toggle { margin: 2px 2px 2px 0; font-size: 9px; border-radius: 4px; padding: 2px 7px; }
        .column-toggle input { display: none; } .column-toggle.active { color: #fff; background: #274b63; border-color: #274b63; }
        .column-order-list { display: flex; flex-wrap: wrap; gap: 4px; min-height: 24px; }
        .column-order-item { border: 1px solid #e6e9ee; background: #fff; color: #34495a; font-size: 9px; padding: 2px 6px; border-radius: 4px; cursor: grab; }
        .column-order-item:active { cursor: grabbing; }
        .settings-choice { border: 1px solid transparent; background: transparent; color: #34495a; font-size: 10px; padding: 4px 7px; border-radius: 4px; }
        .settings-choice.active { border-color: #d4dbe4; background: #fff; color: #274b63; box-shadow: 0 1px 3px #cfd6df; }
        .settings-choice + .settings-choice { margin-left: 2px; }
        .settings-popover-footer { border-top: 1px solid #edf0f4; padding: 10px 14px; text-align: right; }
        .settings-popover-footer .btn { font-size: 10px; padding: 4px 10px; margin-left: 8px; }
        @media (max-width: 600px) {
            .table-settings-popover { max-height: 94vh; }
        }
    </style>
@endsection

@section('content')
    <div class="costing-page">
        <div class="costing-crumb">Marketing &nbsp;›&nbsp; Proses &nbsp;›&nbsp; <b>Costing</b></div>
        <a href="javascript:history.back()" class="costing-back"><i class="fas fa-chevron-left"></i> Back</a>
    <div class="card card-sb">
        <div class="card-body">
            <h1 class="costing-title">Costing List</h1>
            <div class="list-shell">
                <div class="list-toolbar">
                    <button type="button" class="toolbar-btn" onclick="openTableSettings(event)" title="Atur kolom tabel"><i class="fas fa-table mr-1"></i></button>
                    <button type="button" id="search-by" class="search-by"><i class="fas fa-filter mr-1"></i> Search By <i class="fas fa-chevron-down ml-1"></i></button>
                    <input id="costing-search" class="toolbar-search" placeholder="⌕  Search">
                    <div id="search-menu" class="search-menu">
                        <button type="button" data-filter="date">Due Date <i class="fas fa-chevron-right float-right"></i></button>
                        <button type="button" data-column="2">No Costing</button>
                        <button type="button" data-filter="buyer">Buyer <i class="fas fa-chevron-right float-right"></i></button>
                        <button type="button" data-column="6">Brand</button>
                        <button type="button" data-column="7">Style</button>
                        <button type="button" data-column="8">Market</button>
                        <div id="date-submenu" class="search-submenu"><b class="d-block mb-1">Due Date</b><label>Start Date<input type="date" id="menu-tgl-awal" class="form-control" value="{{ date('Y-m-d') }}"></label><label>End Date<input type="date" id="menu-tgl-akhir" class="form-control" value="{{ date('Y-m-d') }}"></label><button type="button" class="btn btn-sm btn-primary mt-2" id="apply-date-filter">Apply</button></div>
                        <div id="buyer-submenu" class="search-submenu">
                            <b class="d-block mb-1">Buyer</b>
                            <input type="search" id="buyer-search" class="form-control form-control-sm buyer-search" placeholder="Search buyer">
                            <label><input type="checkbox" id="buyer-all"> All</label>
                            <div id="buyer-options">
                                @foreach($buyers as $buyer)
                                    <label class="buyer-option"><input type="checkbox" class="buyer-filter" value="{{ $buyer->id }}"> {{ $buyer->name }}</label>
                                @endforeach
                            </div>
                            <button type="button" class="btn btn-sm btn-primary mt-2" id="apply-buyer-filter">Apply</button>
                        </div>
                    </div>
                </div>
                <div class="filter-summary">
                    <span>Filter:</span>
                    <span id="filter-label">None</span>
                    <button type="button" id="clear-filters" class="btn btn-link clear-filters" disabled>Clear all</button>
                </div>
            <div class="table-responsive">
                <table class="table table-bordered table-hover w-100" id="table-costing">
                    <thead>
                        <tr class="text-center">
                            <th><input type="checkbox" id="check-all-costing"></th>
                            <th>No</th>
                            <th>No Costing</th>
                            <th>Due Date</th>
                            <th>No Worksheet</th>
                            <th>Buyer</th>
                            <th>Brand</th>
                            <th>Style</th>
                            <th>Market</th>
                            <th>Qty</th>
                            <th>Confirm Price</th>
                            <th>Product Group</th>
                            <th>Product Item</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
            <a href="{{ route('create-costing') }}" class="btn btn-primary add-new">Add New</a>
            <div class="clearfix"></div>
            </div>
        </div>
    </div>
    </div>

    <div id="settings-backdrop" class="settings-backdrop" onclick="closeTableSettings()"></div>
    <div id="tableSettings" class="table-settings-popover" aria-hidden="true">
        <div class="settings-popover-title">Table settings</div>
        <div class="settings-popover-body">
            <span class="settings-label">Table column</span><div id="column-toggles"></div>
            <span class="settings-label">Column order</span><div id="column-order" class="column-order-list"></div>
            <span class="settings-label">Amount</span><button type="button" class="settings-choice amount-format active" data-format="compact">10.5k</button><button type="button" class="settings-choice amount-format" data-format="full">10,500</button>
            <span class="settings-label">Date format</span><button type="button" class="settings-choice date-format active" data-format="DD/MM/YYYY">DD/MM/YYYY</button><button type="button" class="settings-choice date-format" data-format="MM/DD/YYYY">MM/DD/YYYY</button><button type="button" class="settings-choice date-format" data-format="DD/MMM/YYYY">DD/MMM/YYYY</button>
            <label class="mt-2 mb-0"><input type="checkbox" id="sticky-header"> Sticky header</label>
        </div>
        <div class="settings-popover-footer"><button type="button" class="btn btn-light btn-sm" id="reset-settings">Reset</button><button type="button" class="btn btn-primary btn-sm" id="apply-settings">Apply</button></div>
    </div>
@endsection

@section('custom-script')
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        let table;
        let activeSearchColumn = '';
        let selectedBuyers = [];
        let dateFilterApplied = false;
        const defaultCostingDate = '{{ date('Y-m-d') }}';
        const defaultColumnOrder = Array.from({ length: 14 }, (_, index) => index);
        let pendingColumnOrder = defaultColumnOrder.slice();
        let appliedColumnOrder = defaultColumnOrder.slice();
        let amountFormat = 'compact';
        let dateFormat = 'DD/MM/YYYY';

        $(document).ready(function() {

            table = $('#table-costing').DataTable({
                processing: true,
                serverSide: true,
                responsive: false,
                scrollX: true,
                scrollY: '55vh',
                scrollCollapse: true,
                pageLength: 10,
                lengthMenu: [[7, 10, 25, 50], [7, 10, 25, 50]],
                ajax: {
                    url: '{{ route("master-costing") }}',
                    data: function(d) {
                        d.tgl_awal = $('#menu-tgl-awal').val();
                        d.tgl_akhir = $('#menu-tgl-akhir').val();
                        d.buyers = selectedBuyers;
                    }
                },
                columns: [
                    { data: 'id', orderable: false, searchable: false, className: 'text-center align-middle', render: data => `<input type="checkbox" class="costing-check" value="${data}">` },
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center align-middle' },
                    { data: 'cost_no', name: 'a.cost_no', className: 'text-center align-middle' },
                    {
                        data: 'due_date',
                        name: 'a.deldate',
                        className: 'text-center align-middle',
                        render: function (data, type) {
                            if (type !== 'display' || !data || data === '-') return data || '-';
                            const match = String(data).match(/^(\d{1,2})\s+([A-Za-z]{3})\s+(\d{4})$/);
                            if (!match) return data;
                            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                            const monthIndex = months.findIndex(month => month.toLowerCase() === match[2].toLowerCase());
                            if (monthIndex < 0) return data;
                            const day = match[1].padStart(2, '0');
                            const month = String(monthIndex + 1).padStart(2, '0');
                            return dateFormat === 'MM/DD/YYYY'
                                ? `${month}/${day}/${match[3]}`
                                : dateFormat === 'DD/MMM/YYYY'
                                    ? `${day}/${months[monthIndex]}/${match[3]}`
                                    : `${day}/${month}/${match[3]}`;
                        }
                    },
                    { data: 'marketing_order', name: 'a.mkt_order', className: 'text-center align-middle', render: data => data || '-' },
                    { data: 'nama_buyer', name: 'b.Supplier', className: 'align-middle' },
                    { data: 'brand', name: 'a.brand', className: 'align-middle' },
                    { data: 'styleno', name: 'a.styleno', className: 'align-middle' },
                    { data: 'market', orderable: false, searchable: false, className: 'align-middle' },
                    {
                        data: 'qty',
                        name: 'a.qty',
                        className: 'text-center align-middle',
                        render: function (data, type) {
                            if (type !== 'display' || data === null || data === '') return data;
                            const value = Number(data);
                            if (!Number.isFinite(value)) return data;
                            return amountFormat === 'compact'
                                ? new Intl.NumberFormat('en-US', { notation: 'compact', maximumFractionDigits: 1 }).format(value).replace(/K$/, 'k')
                                : new Intl.NumberFormat('en-US', { maximumFractionDigits: 2 }).format(value);
                        }
                    },
                    {
                        data: 'cfm_price',
                        name: 'a.cfm_price',
                        className: 'text-center align-middle',
                        render: function (data, type, row) {
                            if (data !== null && data !== '') {
                                return parseFloat(data).toFixed(6);
                            }
                            return '0';
                        }
                    },
                    { data: 'product_group_text', name: 'p.product_group', className: 'text-center align-middle' },
                    { data: 'product_item_text', name: 'p.product_item', className: 'text-center align-middle' },
                    {
                        data: 'id',
                        name: 'a.id',
                        orderable: false,
                        searchable: false,
                        className: 'text-center align-middle',
                        render: function (data) {
                            let editUrl = "{{ route('edit-costing', ':id') }}".replace(':id', data);
                            let pdfUrl = "{{ route('print-costing-pdf', ':id') }}".replace(':id', data);
                            let excelUrl = "{{ route('print-excel-costing', ':id') }}".replace(':id', data);
                            let copyUrl = "{{ route('copy-costing', ':id') }}".replace(':id', data);

                            return `<div class="text-nowrap">
                                <a href="${editUrl}" class="action-round action-edit" title="Edit"><i class="fas fa-pen"></i></a>
                                <a href="${pdfUrl}" target="_blank" class="action-round action-pdf" title="PDF"><i class="fas fa-file-pdf"></i></a>
                                <a href="${excelUrl}" target="_blank" class="action-round action-excel" title="Excel"><i class="fas fa-file-excel"></i></a>
                                <button onclick="confirmCopy('${copyUrl}')" class="action-round action-copy" title="Copy"><i class="fas fa-copy"></i></button>
                            </div>`;
                        }
                    }
                ],
                order: [[3, 'desc']],
                initComplete: function () {
                    const api = this.api();
                    api.columns().every(function (i) {
                        const title = $(this.header()).text().trim() || 'Checkbox';
                        $(this.header()).addClass(`costing-col-${i}`);
                        $('#column-toggles').append(`<label class="btn btn-sm btn-outline-secondary column-toggle active"><input type="checkbox" checked data-column="${i}"> ${title}</label>`);
                    });
                    $('#table-costing_wrapper thead tr').each(function () {
                        Array.from(this.cells).forEach((cell, index) => {
                            cell.classList.add(`costing-col-${index}`);
                        });
                    });
                    renderColumnOrder();
                    applyColumnOrder();
                }
            });
            table.on('draw.dt', applyColumnOrder);

            $('#column-order')
                .on('dragstart', '.column-order-item', function (event) {
                    event.originalEvent.dataTransfer.setData('text/plain', String($(this).data('column')));
                    event.originalEvent.dataTransfer.effectAllowed = 'move';
                })
                .on('dragover', '.column-order-item', function (event) {
                    event.preventDefault();
                    event.originalEvent.dataTransfer.dropEffect = 'move';
                })
                .on('drop', '.column-order-item', function (event) {
                    event.preventDefault();
                    const sourceIndex = pendingColumnOrder.indexOf(Number(event.originalEvent.dataTransfer.getData('text/plain')));
                    const targetIndex = pendingColumnOrder.indexOf(Number($(this).data('column')));
                    if (sourceIndex < 0 || targetIndex < 0 || sourceIndex === targetIndex) return;
                    const [movedColumn] = pendingColumnOrder.splice(sourceIndex, 1);
                    const adjustedTargetIndex = pendingColumnOrder.indexOf(Number($(this).data('column')));
                    pendingColumnOrder.splice(adjustedTargetIndex, 0, movedColumn);
                    renderColumnOrder();
                });

            $('#column-toggles').on('change', 'input', function () {
                $(this).closest('.column-toggle').toggleClass('active', this.checked);
            });
            function updateFilterSummary() {
                const $filterLabel = $('#filter-label').empty();
                let filterCount = 0;
                const searchValue = $('#costing-search').val().trim();

                function addFilterChip(label, filter) {
                    const $chip = $('<span>').addClass('filter-chip').text(label);
                    const $remove = $('<button>')
                        .attr({ type: 'button', 'aria-label': `Remove ${filter} filter` })
                        .addClass('filter-chip-remove')
                        .data('filter', filter)
                        .text('×');
                    $chip.append($remove);
                    $filterLabel.append($chip);
                    filterCount++;
                }

                if (searchValue) addFilterChip(`Search: ${searchValue}`, 'search');
                if (selectedBuyers.length) {
                    addFilterChip(`Buyer: ${selectedBuyers.length} selected`, 'buyer');
                }
                if (dateFilterApplied) {
                    addFilterChip(`Due Date: ${$('#menu-tgl-awal').val()} - ${$('#menu-tgl-akhir').val()}`, 'date');
                }

                if (!filterCount) $filterLabel.text('None');
                $('#clear-filters').prop('disabled', filterCount === 0);
            }

            function updateBuyerAllState() {
                const $visibleBuyers = $('.buyer-filter:visible');
                const checkedCount = $visibleBuyers.filter(':checked').length;
                const allChecked = $visibleBuyers.length > 0 && checkedCount === $visibleBuyers.length;
                $('#buyer-all').prop('checked', allChecked)
                    .prop('indeterminate', checkedCount > 0 && !allChecked);
            }

            $('#costing-search').on('input', function () {
                const value = this.value;
                const column = activeSearchColumn;
                table.search('');
                if (column !== '') table.columns().search('').column(column).search(value).draw();
                else table.columns().search('').search(value).draw();
                updateFilterSummary();
            });
            $('#search-by').on('click', function (e) { e.stopPropagation(); $('#search-menu').toggleClass('show'); });
            $('#search-menu > button').on('click', function () {
                const type = $(this).data('filter');
                activeSearchColumn = $(this).data('column') !== undefined ? String($(this).data('column')) : '';
                $('#search-menu > button').removeClass('active'); $(this).addClass('active');
                $('#date-submenu').toggleClass('show', type === 'date');
                $('#buyer-submenu').toggleClass('show', type === 'buyer');
                if (!type) { $('#search-menu').removeClass('show'); $('#costing-search').attr('placeholder', `⌕  Search ${$(this).text().trim()}`).trigger('focus').trigger('input'); }
            });
            $(document).on('click', function (e) { if (!$(e.target).closest('.list-toolbar').length) $('#search-menu').removeClass('show'); });
            $('#buyer-search').on('input', function () {
                const query = this.value.trim().toLocaleLowerCase();
                $('#buyer-options .buyer-option').each(function () {
                    $(this).toggle($(this).text().trim().toLocaleLowerCase().includes(query));
                });
                updateBuyerAllState();
            });
            $('#buyer-all').on('change', function () {
                $('.buyer-filter:visible').prop('checked', this.checked);
                updateBuyerAllState();
            });
            $('#buyer-options').on('change', '.buyer-filter', updateBuyerAllState);
            $('#apply-buyer-filter').on('click', function () {
                selectedBuyers = $('.buyer-filter:checked').map((i, el) => el.value).get();
                updateFilterSummary();
                table.ajax.reload(); $('#search-menu').removeClass('show');
            });
            $('#apply-date-filter').on('click', function () {
                dateFilterApplied = true;
                updateFilterSummary();
                table.ajax.reload(); $('#search-menu').removeClass('show');
            });
            $('#filter-label').on('click', '.filter-chip-remove', function () {
                const filter = $(this).data('filter');

                if (filter === 'search') {
                    $('#costing-search').val('');
                    table.search('').columns().search('').draw();
                } else if (filter === 'buyer') {
                    selectedBuyers = [];
                    $('.buyer-filter, #buyer-all').prop('checked', false).prop('indeterminate', false);
                    table.ajax.reload();
                } else if (filter === 'date') {
                    dateFilterApplied = false;
                    $('#menu-tgl-awal, #menu-tgl-akhir').val(defaultCostingDate);
                    table.ajax.reload();
                }

                updateFilterSummary();
            });
            $('#clear-filters').on('click', function () {
                selectedBuyers = [];
                dateFilterApplied = false;
                activeSearchColumn = '';
                $('.buyer-filter, #buyer-all').prop('checked', false).prop('indeterminate', false);
                $('#buyer-search').val('');
                $('#buyer-options .buyer-option').show();
                $('#menu-tgl-awal, #menu-tgl-akhir').val(defaultCostingDate);
                $('#costing-search').val('').attr('placeholder', '⌕  Search');
                $('#search-menu > button').removeClass('active');
                $('#date-submenu, #buyer-submenu').removeClass('show');
                table.search('').columns().search('');
                updateFilterSummary();
                table.ajax.reload();
            });
            $('#check-all-costing').on('change', function () { $('.costing-check').prop('checked', this.checked); });
            $('#reset-settings').on('click', function () {
                $('#column-toggles input').prop('checked', true).trigger('change');
                pendingColumnOrder = defaultColumnOrder.slice();
                renderColumnOrder();
                $('.amount-format').removeClass('active').filter('[data-format="compact"]').addClass('active');
                $('.date-format').removeClass('active').filter('[data-format="DD/MM/YYYY"]').addClass('active');
                amountFormat = 'compact';
                dateFormat = 'DD/MM/YYYY';
                $('#sticky-header').prop('checked', false);
            });
            $('.amount-format').on('click', function () {
                $('.amount-format').removeClass('active');
                $(this).addClass('active');
                amountFormat = $(this).data('format');
            });
            $('.date-format').on('click', function () {
                $('.date-format').removeClass('active');
                $(this).addClass('active');
                dateFormat = $(this).data('format');
            });
            $('#apply-settings').on('click', function () {
                $('#column-toggles input').each(function () { table.column($(this).data('column')).visible(this.checked, false); });
                appliedColumnOrder = pendingColumnOrder.slice();
                applyColumnOrder();
                $('#table-costing_wrapper .dataTables_scrollHead').toggleClass('sticky-top', $('#sticky-header').is(':checked'));
                table.draw(false);
                closeTableSettings();
            });
        });


        function renderColumnOrder() {
            if (!table) return;
            const items = pendingColumnOrder.map(index => {
                const title = $(table.column(index).header()).text().trim() || 'Checkbox';
                return `<span class="column-order-item" draggable="true" data-column="${index}"><i class="fas fa-grip-lines mr-1 text-muted"></i>${title}</span>`;
            });
            $('#column-order').html(items.join(''));
        }

        function applyColumnOrder() {
            if (!table || !appliedColumnOrder.length) return;

            table.columns().every(function (index) {
                $(this.nodes()).addClass(`costing-col-${index}`);
                $(this.header()).addClass(`costing-col-${index}`);
            });

            $('#table-costing_wrapper thead tr, #table-costing tbody tr').each(function () {
                const row = this;
                appliedColumnOrder.forEach(index => {
                    const cell = Array.from(row.cells).find(item => item.classList.contains(`costing-col-${index}`));
                    if (cell) row.appendChild(cell);
                });
            });
        }

        function openTableSettings(event) {
            const buttonRect = event.currentTarget.getBoundingClientRect();
            const $settings = $('#tableSettings');
            pendingColumnOrder = appliedColumnOrder.slice();
            renderColumnOrder();
            $settings.addClass('show').attr('aria-hidden', 'false');
            const popoverWidth = $settings.outerWidth();
            const popoverHeight = $settings.outerHeight();
            const left = Math.max(8, Math.min(buttonRect.left, window.innerWidth - popoverWidth - 8));
            const belowTop = buttonRect.bottom + 8;
            const top = belowTop + popoverHeight <= window.innerHeight - 8
                ? belowTop
                : Math.max(8, buttonRect.top - popoverHeight - 8);
            $settings.css({ top: `${top}px`, left: `${left}px` });
            $('#settings-backdrop').addClass('show');
        }

        function closeTableSettings() {
            $('#tableSettings, #settings-backdrop').removeClass('show');
            $('#tableSettings').attr('aria-hidden', 'true');
        }

        function confirmCopy(url) {
            Swal.fire({
                title: 'Copy Costing?',
                text: 'Data header dan semua detail akan disalin ke nomor costing baru.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-copy"></i> Ya, Copy!',
                cancelButtonText: 'Batal',
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Sedang menyalin...',
                        text: 'Mohon tunggu sebentar.',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading(); }
                    });
                    window.location.href = url;
                }
            });
        }
    </script>
@endsection
