@extends('layouts.index')

@section('custom-link')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">

    <style>
        .skeleton-box {
            display: inline-block;
            height: 1.4em;
            width: 65px;
            border-radius: 6px;
            background: linear-gradient(90deg, #e2e8f0 25%, #f8fafc 50%, #e2e8f0 75%);
            background-size: 200% 100%;
            animation: shimmer-loading 1.4s infinite ease-in-out;
            vertical-align: middle;
        }

        @keyframes shimmer-loading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }

        /* Summary cards */
        .sum-card { text-decoration: none; display: block; height: 100%; }
        .sum-box {
            display: flex;
            align-items: center;
            gap: 1rem;
            height: 100%;
            padding: 1rem 1.25rem;
            border-radius: 12px;
            background: #fff;
            border: 1px solid #e9ecef;
            border-left: 5px solid var(--accent);
            box-shadow: 0 1px 3px rgba(0, 0, 0, .06);
            transition: transform .2s ease, box-shadow .2s ease;
        }
        .sum-card:hover .sum-box {
            transform: translateY(-3px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, .10);
        }
        .sum-box .sum-icon {
            flex-shrink: 0;
            width: 52px;
            height: 52px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
            color: var(--accent);
            background: var(--accent-soft);
        }
        .sum-box .sum-label { font-size: .85rem; font-weight: 600; color: #6c757d; }
        .sum-box .sum-value { font-size: 1.75rem; font-weight: 700; line-height: 1.2; color: var(--accent); }
        .sum-box .sum-hint { font-size: .75rem; color: #adb5bd; }
        .sum-card:hover .sum-hint { color: var(--accent); }
        .sum-primary { --accent: #0d6efd; --accent-soft: rgba(13, 110, 253, .12); }
        .sum-danger  { --accent: #dc3545; --accent-soft: rgba(220, 53, 69, .12); }
        .sum-success { --accent: #198754; --accent-soft: rgba(25, 135, 84, .12); }

        /* Tables */
        .pending-table thead tr:first-child th {
            background: #f1f3f5;
            font-size: .8rem;
            text-transform: uppercase;
            letter-spacing: .03em;
            white-space: nowrap;
            vertical-align: middle;
            text-align: center;
        }
        .pending-table thead tr:nth-child(2) th { background: #f8f9fa; padding: .35rem; }
        .pending-table tbody td { vertical-align: middle; }
        .pending-table tbody tr:hover td { background: #f8f9ff; }
        .count-badge {
            display: inline-block;
            min-width: 2.25rem;
            padding: .25em .6em;
            border-radius: 999px;
            font-weight: 600;
            font-size: .85rem;
        }
        .count-muted   { background: #e9ecef; color: #495057; }
        .count-primary { background: rgba(13, 110, 253, .12); color: #0a58ca; }
        .count-danger  { background: rgba(220, 53, 69, .12); color: #b02a37; }
        .count-success { background: rgba(25, 135, 84, .12); color: #146c43; }
        .count-zero { color: #adb5bd; }

        /* Modal form filter */
        .form-filter-panel {
            padding: .75rem 1rem;
            border: 1px solid #e9ecef;
            border-radius: 10px;
            background: #f8f9fa;
        }
        .form-filter-panel .form-label { font-size: .75rem; font-weight: 600; color: #6c757d; }
        .form-filter-chip {
            display: inline-flex;
            align-items: center;
            gap: .3rem;
            padding: .15rem .3rem .15rem .6rem;
            border-radius: 999px;
            background: #e7f1ff;
            border: 1px solid #b6d4fe;
            font-size: .8rem;
        }
        .form-filter-chip-remove {
            border: 0;
            background: transparent;
            line-height: 1;
            font-size: 1rem;
            padding: 0 .25rem;
            color: #6c757d;
            border-radius: 50%;
        }
        .form-filter-chip-remove:hover { color: #dc3545; background: rgba(220, 53, 69, .1); }

        /* Allocate modal */
        .allocate-form-info {
            padding: .75rem 1rem;
            border-radius: 10px;
            background: #f8f9fa;
            border: 1px solid #e9ecef;
        }
        .allocate-form-info small { color: #6c757d; font-size: .75rem; }
        .allocate-part-option {
            display: flex;
            align-items: center;
            gap: .75rem;
            padding: .65rem .9rem;
            margin-bottom: .5rem;
            border: 1px solid #dee2e6;
            border-radius: 10px;
            cursor: pointer;
            transition: border-color .15s ease, background .15s ease;
        }
        .allocate-part-option:hover { border-color: #86b7fe; background: #f8fbff; }
        .allocate-part-option:has(input:checked) { border-color: #0d6efd; background: #e7f1ff; }
        .allocate-part-option .form-check-input { margin: 0; flex-shrink: 0; }
        .allocate-part-option .part-meta { font-size: .8rem; color: #6c757d; }
    </style>
@endsection

@section('content')
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold">
                <i class="fa fa-hourglass-half"></i> Pending Part Form
            </h5>
        </div>
        <div class="card-body">
            <div class="row g-3 mb-4" id="part-form-qty-data">
                <div class="col-md-4">
                    <a href="#" class="sum-card" data-bs-toggle="modal" data-bs-target="#incompleteFormModal">
                        <div class="sum-box sum-primary">
                            <div class="sum-icon"><i class="fa-solid fa-spinner"></i></div>
                            <div>
                                <div class="sum-label">Form Belum Selesai</div>
                                <div class="sum-value" id="incomplete-qty"><div class="skeleton-box"></div></div>
                                <div class="sum-hint">Klik untuk lihat detail <i class="fa-solid fa-arrow-right fa-xs"></i></div>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="#" class="sum-card" data-bs-toggle="modal" data-bs-target="#pendingFormModal">
                        <div class="sum-box sum-danger">
                            <div class="sum-icon"><i class="fa-solid fa-hourglass-half"></i></div>
                            <div>
                                <div class="sum-label">Form Belum Terdaftar</div>
                                <div class="sum-value" id="pending-qty"><div class="skeleton-box"></div></div>
                                <div class="sum-hint">Klik untuk lihat detail <i class="fa-solid fa-arrow-right fa-xs"></i></div>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="#" class="sum-card" data-bs-toggle="modal" data-bs-target="#allocatedFormModal">
                        <div class="sum-box sum-success">
                            <div class="sum-icon"><i class="fa-solid fa-check"></i></div>
                            <div>
                                <div class="sum-label">Form Terdaftar</div>
                                <div class="sum-value" id="allocated-qty"><div class="skeleton-box"></div></div>
                                <div class="sum-hint">Klik untuk lihat detail <i class="fa-solid fa-arrow-right fa-xs"></i></div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
            <div class="table-responsive">
                <table id="datatable-pending-part" class="table table-bordered table-sm pending-table w-100">
                    <thead>
                        <tr>
                            <th>Action</th>
                            <th>Kode</th>
                            <th>No. WS</th>
                            <th>Panel</th>
                            <th>Total Marker</th>
                            <th>Total Form</th>
                            <th>Form Belum Selesai</th>
                            <th>Form Tertunda</th>
                            <th>Form Terdaftar</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Incomplete Form Modal -->
    <div class="modal fade" id="incompleteFormModal" tabindex="-1" aria-labelledby="incompleteFormModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h1 class="modal-title fs-5 fw-bold" id="incompleteFormModalLabel"><i class="fa-solid fa-spinner"></i> Form Belum Selesai</h1>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm pending-table w-100" id="incomplete-form-table">
                            <thead>
                                <tr>
                                    <th>Action</th>
                                    <th>Alokasi</th>
                                    <th>Status</th>
                                    <th>No. Form</th>
                                    <th>No. Marker</th>
                                    <th>No. Part</th>
                                    <th>No. WS</th>
                                    <th>Color</th>
                                    <th>Panel</th>
                                    <th>Waktu Mulai</th>
                                    <th>Waktu Selesai</th>
                                    <th>Tipe Form</th>
                                    <th>No. Cut</th>
                                    <th>Total Lembar</th>
                                    <th>Operator</th>
                                    <th>Edited</th>
                                    <th>Edited Notes</th>
                                    <th>Edited By Username</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fa-solid fa-xmark"></i> Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Form Modal -->
    <div class="modal fade" id="pendingFormModal" tabindex="-1" aria-labelledby="pendingFormModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h1 class="modal-title fs-5 fw-bold" id="pendingFormModalLabel"><i class="fa-solid fa-hourglass-half"></i> Form Belum Terdaftar</h1>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm pending-table w-100" id="pending-form-table">
                            <thead>
                                <tr>
                                    <th class="text-center">Action</th>
                                    <th>Alokasi</th>
                                    <th>Status</th>
                                    <th>No. Form</th>
                                    <th>No. Marker</th>
                                    <th>No. Part</th>
                                    <th>No. WS</th>
                                    <th>Color</th>
                                    <th>Panel</th>
                                    <th>Waktu Mulai</th>
                                    <th>Waktu Selesai</th>
                                    <th>Tipe Form</th>
                                    <th>No. Cut</th>
                                    <th>Total Lembar</th>
                                    <th>Operator</th>
                                    <th>Edited</th>
                                    <th>Edited Notes</th>
                                    <th>Edited By Username</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fa-solid fa-xmark"></i> Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Allocated Form Modal -->
    <div class="modal fade" id="allocatedFormModal" tabindex="-1" aria-labelledby="allocatedFormModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h1 class="modal-title fs-5 fw-bold" id="allocatedFormModalLabel"><i class="fa-solid fa-check"></i> Form Sudah Terdaftar</h1>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm pending-table w-100" id="allocated-form-table">
                            <thead>
                                <tr>
                                    <th class="d-none">Action</th>
                                    <th>Alokasi</th>
                                    <th>Status</th>
                                    <th>No. Form</th>
                                    <th>No. Marker</th>
                                    <th>No. Part</th>
                                    <th>No. WS</th>
                                    <th>Color</th>
                                    <th>Panel</th>
                                    <th>Waktu Mulai</th>
                                    <th>Waktu Selesai</th>
                                    <th>Tipe Form</th>
                                    <th>No. Cut</th>
                                    <th>Total Lembar</th>
                                    <th>Operator</th>
                                    <th>Edited</th>
                                    <th>Edited Notes</th>
                                    <th>Edited By Username</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fa-solid fa-xmark"></i> Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Allocate Form Modal -->
    <div class="modal fade" id="allocateFormModal" tabindex="-1" aria-labelledby="allocateFormModalLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-sb text-white">
                    <h1 class="modal-title fs-5 fw-bold" id="allocateFormModalLabel"><i class="fa-solid fa-file-circle-plus"></i> Alokasi Form ke Part</h1>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="allocate-form-id">
                    <input type="hidden" id="allocate-no-form">

                    <div class="allocate-form-info mb-3">
                        <div class="row g-2">
                            <div class="col-6 col-md-4"><small>No. Form</small><div class="fw-bold" id="allocate-info-no-form">-</div></div>
                            <div class="col-6 col-md-4"><small>No. WS</small><div class="fw-bold" id="allocate-info-ws">-</div></div>
                            <div class="col-6 col-md-4"><small>Color</small><div class="fw-bold" id="allocate-info-color">-</div></div>
                            <div class="col-6 col-md-4"><small>Panel</small><div class="fw-bold" id="allocate-info-panel">-</div></div>
                            <div class="col-6 col-md-4"><small>No. Cut</small><div class="fw-bold" id="allocate-info-no-cut">-</div></div>
                            <div class="col-6 col-md-4"><small>Total Lembar</small><div class="fw-bold" id="allocate-info-lembar">-</div></div>
                        </div>
                    </div>

                    <label class="form-label fw-bold mb-2">Pilih Part</label>
                    <div id="allocate-part-list"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fa-solid fa-xmark"></i> Batal</button>
                    <button type="button" class="btn btn-sb" id="allocate-submit" disabled><i class="fa-solid fa-floppy-disk"></i> Alokasikan</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('custom-script')
    <!-- DataTables  & Plugins -->
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>

    <script>
        // Initial Function
        document.addEventListener("DOMContentLoaded", () => {
            // Set Filter to 1 Week Ago
            let oneWeeksBefore = new Date(new Date().setDate(new Date().getDate() - 7));
            let oneWeeksBeforeDate = ("0" + oneWeeksBefore.getDate()).slice(-2);
            let oneWeeksBeforeMonth = ("0" + (oneWeeksBefore.getMonth() + 1)).slice(-2);
            let oneWeeksBeforeYear = oneWeeksBefore.getFullYear();
            let oneWeeksBeforeFull = oneWeeksBeforeYear + '-' + oneWeeksBeforeMonth + '-' + oneWeeksBeforeDate;

            $("#tgl-awal").val(oneWeeksBeforeFull).trigger("change");

            window.addEventListener("focus", () => {
                $('#datatable').DataTable().ajax.reload(null, false);
            });
        });

        // Part Datatable
        let datatablePendingPart = $("#datatable-pending-part").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('get-pending-part') }}/',
            },
            columns: [
                {
                    data: 'id'
                },
                {
                    data: 'kode'
                },
                {
                    data: 'act_costing_ws'
                },
                {
                    data: 'panel'
                },
                {
                    data: 'total_marker'
                },
                {
                    data: 'total_form'
                },
                {
                    data: 'total_incomplete'
                },
                {
                    data: 'total_pending'
                },
                {
                    data: 'total_allocated',
                }
            ],
            columnDefs: [
                {
                    targets: "_all",
                    className: "text-nowrap align-middle"
                },
                {
                    targets: [4, 5, 6, 7, 8],
                    className: "text-nowrap text-center align-middle",
                    render: function (data, type, row, meta) {
                        if (type !== 'display') return data;

                        const colors = { 4: 'count-muted', 5: 'count-muted', 6: 'count-primary', 7: 'count-danger', 8: 'count-success' };
                        const value = Number(data) || 0;

                        return value > 0
                            ? '<span class="count-badge ' + colors[meta.col] + '">' + value.toLocaleString('id-ID') + '</span>'
                            : '<span class="count-zero">0</span>';
                    }
                },
                {
                    targets: [0],
                    render: function (data, type, row, meta) {
                        return `
                            <div class="d-flex justify-content-center">
                                <a href="{{ route('manage-part-form') }}/`+data+`" target="_blank" class="btn btn-sb-secondary btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="custom-tooltip" data-bs-title="Atur Part Form"><i class="fa-solid fa-file-circle-plus"></i></a>
                            </div>
                        `;
                    }
                }
            ],
            drawCallback: function (settings) {
                $.ajax({
                    type: "get",
                    url: "{{ route('get-pending-part-sum') }}",
                    data: {
                        filter_kode: $("#filter_kode").val(),
                        filter_ws: $("#filter_ws").val(),
                        filter_panel: $("#filter_panel").val()
                    },
                    dataType: "json",
                    success: function (response) {
                        console.log(response);
                        $("#incomplete-qty").text(formatNumber(response.totalIncomplete));
                        $("#pending-qty").text(formatNumber(response.totalPending));
                        $("#allocated-qty").text(formatNumber(response.totalAllocated));

                        datatableIncompleteFormReload();
                        datatablePendingFormReload();
                        datatableAllocatedFormReload();
                    }
                });
            }
        });

        var filterColumn = ['filter_id', 'filter_kode', 'filter_ws', 'filter_panel']

        $('#datatable-pending-part thead tr').clone(true).appendTo('#datatable-pending-part thead');
        $('#datatable-pending-part thead tr:eq(1) th').each(function(i) {
            if (i == 1 || i == 2 || i == 3) {
                var title = $(this).text();
                $(this).html('<input type="text" class="form-control form-control-sm" id="'+(filterColumn[i])+'" placeholder="Cari '+title+'..."/>');

                $('input', this).on('keyup change', function() {
                    if (datatablePendingPart.column(i).search() !== this.value) {
                        datatablePendingPart
                            .column(i)
                            .search(this.value)
                            .draw();
                    }
                });
            } else {
                $(this).empty();
            }
        });

        // Trigger automatically whenever DataTable starts loading
        $('#datatable-pending-part').on('processing.dt', function (e, settings, processing) {
            if (processing) {
                headerLoading();
            }
        });

        function headerLoading() {
            const el = '<span class="skeleton-box"></span>';

            $("#incomplete-qty").html(el);
            $("#pending-qty").html(el);
            $("#allocated-qty").html(el);
        }

        /**
         * Filter tabel form di modal: satu tombol "Filter" yang membuka panel pilihan filter,
         * dan chip untuk tiap filter yang sedang aktif (bisa dihapus satu-satu / reset semua).
         *
         * selectFilters : { colIndex: { options: [...] } } -> dropdown (exact match)
         * excluded      : index kolom yang tidak ditampilkan di panel filter
         */
        function setupFormFilter(tableId, dt, selectFilters = {}, excluded = [0]) {
            const $table = $('#' + tableId);
            const panelId = tableId + '-filter-panel';
            const debounceTimers = {};

            const $toolbar = $(`
                <div class="form-filter mb-2">
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm form-filter-toggle" data-bs-toggle="collapse" data-bs-target="#${panelId}" aria-expanded="false">
                            <i class="fa-solid fa-filter"></i> Filter
                            <span class="badge rounded-pill bg-danger ms-1 form-filter-count d-none">0</span>
                        </button>
                        <div class="form-filter-chips d-flex flex-wrap align-items-center gap-1"></div>
                        <button type="button" class="btn btn-link btn-sm text-danger p-0 ms-1 form-filter-reset d-none">
                            <i class="fa-solid fa-rotate-left"></i> Reset
                        </button>
                    </div>
                    <div class="collapse" id="${panelId}">
                        <div class="form-filter-panel mt-2">
                            <div class="row g-2 form-filter-fields"></div>
                        </div>
                    </div>
                </div>
            `);
            $table.closest('.table-responsive').before($toolbar);

            const $fields = $toolbar.find('.form-filter-fields');

            $table.find('thead tr:first th').each(function (i) {
                if (excluded.includes(i)) return;

                const title = $(this).text().trim();
                const $col = $('<div class="col-6 col-md-4 col-lg-3"></div>');
                $col.append($('<label class="form-label mb-1"></label>').text(title));

                let $field;
                if (selectFilters[i]) {
                    $field = $('<select class="form-select form-select-sm"></select>');
                    $field.append('<option value="">Semua</option>');
                    selectFilters[i].options.forEach(option => {
                        $field.append($('<option></option>').val(option).text(option));
                    });
                } else {
                    $field = $('<input type="text" class="form-control form-control-sm">').attr('placeholder', 'Cari ' + title + '...');
                }

                $field.attr({ 'data-col': i, 'data-title': title, 'data-exact': selectFilters[i] ? 1 : 0 });
                $col.append($field);
                $fields.append($col);
            });

            function applyColumn($field) {
                const i = Number($field.data('col'));
                const value = ($field.val() || '').trim();
                const search = $field.data('exact') == 1
                    // regex ^...$ (exact match) supaya "PENGERJAAN FORM CUTTING" tidak ikut menarik "...DETAIL" / "...SPREAD"
                    ? [value ? '^' + value + '$' : '', true, false]
                    : [value, false, true];

                if (dt.column(i).search() !== search[0]) {
                    dt.column(i).search(...search).draw();
                }
                renderChips();
            }

            function renderChips() {
                const $chips = $toolbar.find('.form-filter-chips').empty();
                let count = 0;

                $fields.find('[data-col]').each(function () {
                    const value = ($(this).val() || '').trim();
                    if (!value) return;

                    count++;
                    const $chip = $('<span class="form-filter-chip"></span>');
                    $chip.append($('<span class="text-muted"></span>').text($(this).data('title') + ':'));
                    $chip.append($('<strong></strong>').text(value));
                    $chip.append(
                        $('<button type="button" class="form-filter-chip-remove" title="Hapus filter">&times;</button>')
                            .attr('data-col', $(this).data('col'))
                    );
                    $chips.append($chip);
                });

                $toolbar.find('.form-filter-count').text(count).toggleClass('d-none', count === 0);
                $toolbar.find('.form-filter-reset').toggleClass('d-none', count === 0);
                $toolbar.find('.form-filter-toggle')
                    .toggleClass('btn-outline-secondary', count === 0)
                    .toggleClass('btn-secondary', count > 0);
            }

            $fields.on('change', 'select', function () {
                applyColumn($(this));
            });

            $fields.on('keyup change', 'input', function () {
                const $field = $(this);
                const i = $field.data('col');
                clearTimeout(debounceTimers[i]);
                debounceTimers[i] = setTimeout(() => applyColumn($field), 400);
            });

            $toolbar.on('click', '.form-filter-chip-remove', function () {
                const $field = $fields.find('[data-col="' + $(this).attr('data-col') + '"]');
                $field.val('');
                applyColumn($field);
            });

            $toolbar.on('click', '.form-filter-reset', function () {
                $fields.find('[data-col]').each(function () {
                    $(this).val('');
                    dt.column(Number($(this).data('col'))).search('');
                });
                dt.draw();
                renderChips();
            });
        }

        // Incomplete Form Datatable
        let datatableIncompleteForm = $("#incomplete-form-table").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('get-part-forms') }}/',
                data: function (d) {
                    d.filter_kode = $("#filter_kode").val();
                    d.filter_ws = $("#filter_ws").val();
                    d.filter_panel = $("#filter_panel").val();
                    d.status = 'incomplete';
                }
            },
            columns: [
                {
                    data: 'id'
                },
                {
                    data: 'alokasi'
                },
                {
                    data: 'status'
                },
                {
                    data: 'no_form'
                },
                {
                    data: 'kode_marker'
                },
                {
                    data: 'kode_part'
                },
                {
                    data: 'act_costing_ws'
                },
                {
                    data: 'color'
                },
                {
                    data: 'panel'
                },
                {
                    data: 'waktu_mulai'
                },
                {
                    data: 'waktu_selesai'
                },
                {
                    data: 'tipe_form_cut'
                },
                {
                    data: 'no_cut'
                },
                {
                    data: 'total_lembar'
                },
                {
                    data: 'operator'
                },
                {
                    data: 'edited'
                },
                {
                    data: 'edit_notes',
                },
                {
                    data: 'edited_by_username',
                },
            ],
            columnDefs: [
                {
                    targets: [0],
                    className: "d-none",
                    render: function (data, type, row, meta) {
                        return `
                            <div class="d-flex justify-content-center">
                                <a href="{{ route('manage-part-form') }}/`+data+`" target="_blank" class="btn btn-primary btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="custom-tooltip" data-bs-title="Atur Part Form"><i class="fa-solid fa-file-circle-plus"></i></a>
                            </div>
                        `;
                    }
                },
                {
                    targets: [2],
                    className: "text-nowrap text-center align-middle",
                    render: (data, type, row, meta) => {
                        console.log(row.marker_details);

                        icon = "";

                        switch (data) {
                            case "SPREADING":
                                if (row.app != 'Y') {
                                    icon = `<i class="fas fa-file fa-lg"></i>`;
                                } else {
                                    icon = `<i class="fas fa-file fa-lg"></i>`;
                                }
                                break;
                            case "PENGERJAAN MARKER":
                            case "PENGERJAAN FORM CUTTING":
                            case "PENGERJAAN FORM CUTTING DETAIL":
                            case "PENGERJAAN FORM CUTTING SPREAD":
                                icon =
                                    `<i class="fas fa-sync-alt fa-spin fa-lg"></i>`;
                                break;
                            case "SELESAI PENGERJAAN":
                                icon = `<i class="fas fa-check fa-lg"></i>`;
                                break;
                        }

                        return icon;
                    }
                },
                {
                    targets: [3],
                    className: "text-nowrap text-center align-middle",
                    render: (data, type, row, meta) => {
                        let linkProcess = "{{ route('process-form-cut-input') }}/" + row.id;
                        if (row.tipe_form_cut == 'MANUAL') {
                            linkProcess = "{{ route('process-manual-form-cut') }}/" +row.id;
                        } else if (row.tipe_form_cut == 'PILOT') {
                            linkProcess = "{{ route('process-pilot-form-cut') }}/" + row.id;
                        }

                        return data ? `<a class='fw-bold' href='`+linkProcess+`' target='_blank'><u>`+data+`</u></a>` : "-";
                    }
                },
                {
                    targets: [4],
                    className: "text-nowrap text-center align-middle",
                    render: (data, type, row, meta) => {
                        return data ? `<a class='fw-bold' href='{{ route('edit-marker') }}/ `+row.marker_id+`' target='_blank'><u>`+data+`</u></a>` : "-";
                    }
                },
                {
                    targets: [5],
                    className: "text-nowrap text-center align-middle",
                    render: (data, type, row, meta) => {
                        return data ? `<a class='fw-bold' href='{{ route('manage-part-form') }}/ `+row.part_id+`' target='_blank'><u>`+data+`</u></a>` : "-";
                    }
                },
                {
                    targets: "_all",
                    className: "text-nowrap text-center align-middle",
                },
            ]
        });

        setupFormFilter('incomplete-form-table', datatableIncompleteForm, {
            2: {
                options: [
                    'SPREADING',
                    'PENGERJAAN MARKER',
                    'PENGERJAAN FORM CUTTING',
                    'PENGERJAAN FORM CUTTING DETAIL',
                    'PENGERJAAN FORM CUTTING SPREAD',
                ],
            },
            11: {
                options: ['NORMAL', 'PILOT', 'MANUAL'],
            },
        }, [0]);

        function datatableIncompleteFormReload() {
            $("#incomplete-form-table").DataTable().ajax.reload(null, false);
        }

        // Pending Form Datatable
        let datatablePendingForm = $("#pending-form-table").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('get-part-forms') }}/',
                data: function (d) {
                    d.filter_kode = $("#filter_kode").val();
                    d.filter_ws = $("#filter_ws").val();
                    d.filter_panel = $("#filter_panel").val();
                    d.status = 'pending';
                }
            },
            columns: [
                {
                    data: 'id'
                },
                {
                    data: 'alokasi'
                },
                {
                    data: 'status'
                },
                {
                    data: 'no_form'
                },
                {
                    data: 'kode_marker'
                },
                {
                    data: 'kode_part'
                },
                {
                    data: 'act_costing_ws'
                },
                {
                    data: 'color'
                },
                {
                    data: 'panel'
                },
                {
                    data: 'waktu_mulai'
                },
                {
                    data: 'waktu_selesai'
                },
                {
                    data: 'tipe_form_cut'
                },
                {
                    data: 'no_cut'
                },
                {
                    data: 'total_lembar'
                },
                {
                    data: 'operator'
                },
                {
                    data: 'edited'
                },
                {
                    data: 'edit_notes',
                },
                {
                    data: 'edited_by_username',
                },
            ],
            columnDefs: [
                {
                    targets: [0],
                    className: "text-nowrap text-center align-middle",
                    render: function (data, type, row, meta) {
                        return `
                            <button type="button" class="btn btn-sb btn-sm btn-allocate-form" data-row="${meta.row}" title="Alokasi ke Part">
                                <i class="fa-solid fa-file-circle-plus"></i> Alokasi
                            </button>
                        `;
                    }
                },
                {
                    targets: [2],
                    className: "text-nowrap text-center align-middle",
                    render: (data, type, row, meta) => {
                        console.log(row.marker_details);

                        icon = "";

                        switch (data) {
                            case "SPREADING":
                                if (row.app != 'Y') {
                                    icon = `<i class="fas fa-file fa-lg"></i>`;
                                } else {
                                    icon = `<i class="fas fa-file fa-lg"></i>`;
                                }
                                break;
                            case "PENGERJAAN MARKER":
                            case "PENGERJAAN FORM CUTTING":
                            case "PENGERJAAN FORM CUTTING DETAIL":
                            case "PENGERJAAN FORM CUTTING SPREAD":
                                icon =
                                    `<i class="fas fa-sync-alt fa-spin fa-lg"></i>`;
                                break;
                            case "SELESAI PENGERJAAN":
                                icon = `<i class="fas fa-check fa-lg"></i>`;
                                break;
                        }

                        return icon;
                    }
                },
                {
                    targets: [3],
                    className: "text-nowrap text-center align-middle",
                    render: (data, type, row, meta) => {
                        let linkProcess = "{{ route('process-form-cut-input') }}/" + row.id;
                        if (row.tipe_form_cut == 'MANUAL') {
                            linkProcess = "{{ route('process-manual-form-cut') }}/" +row.id;
                        } else if (row.tipe_form_cut == 'PILOT') {
                            linkProcess = "{{ route('process-pilot-form-cut') }}/" + row.id;
                        }

                        return data ? `<a class='fw-bold' href='`+linkProcess+`' target='_blank'><u>`+data+`</u></a>` : "-";
                    }
                },
                {
                    targets: [4],
                    className: "text-nowrap text-center align-middle",
                    render: (data, type, row, meta) => {
                        return data ? `<a class='fw-bold' href='{{ route('edit-marker') }}/ `+row.marker_id+`' target='_blank'><u>`+data+`</u></a>` : "-";
                    }
                },
                {
                    targets: [5],
                    className: "text-nowrap text-center align-middle",
                    render: (data, type, row, meta) => {
                        return data ? `<a class='fw-bold' href='{{ route('manage-part-form') }}/ `+row.part_id+`' target='_blank'><u>`+data+`</u></a>` : "-";
                    }
                },
                {
                    targets: "_all",
                    className: "text-nowrap text-center align-middle",
                },
            ]
        });

        setupFormFilter('pending-form-table', datatablePendingForm, {
            11: {
                options: ['NORMAL', 'PILOT', 'MANUAL'],
            },
        }, [0, 2]);

        function datatablePendingFormReload() {
            $("#pending-form-table").DataTable().ajax.reload(null, false);
        }

        // Pending Form Datatable
        let datatableAllocatedForm = $("#allocated-form-table").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('get-part-forms') }}/',
                data: function (d) {
                    d.filter_kode = $("#filter_kode").val();
                    d.filter_ws = $("#filter_ws").val();
                    d.filter_panel = $("#filter_panel").val();
                    d.status = 'allocated';
                }
            },
            columns: [
                {
                    data: 'id'
                },
                {
                    data: 'alokasi'
                },
                {
                    data: 'status'
                },
                {
                    data: 'no_form'
                },
                {
                    data: 'kode_marker'
                },
                {
                    data: 'kode_part'
                },
                {
                    data: 'act_costing_ws'
                },
                {
                    data: 'color'
                },
                {
                    data: 'panel'
                },
                {
                    data: 'waktu_mulai'
                },
                {
                    data: 'waktu_selesai'
                },
                {
                    data: 'tipe_form_cut'
                },
                {
                    data: 'no_cut'
                },
                {
                    data: 'total_lembar'
                },
                {
                    data: 'operator'
                },
                {
                    data: 'edited'
                },
                {
                    data: 'edit_notes',
                },
                {
                    data: 'edited_by_username',
                },
            ],
            columnDefs: [
                {
                    targets: [0],
                    className: "d-none",
                    render: function (data, type, row, meta) {
                        return `
                            <div class="d-flex justify-content-center">
                                <a href="{{ route('manage-part-form') }}/`+data+`" target="_blank" class="btn btn-primary btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="custom-tooltip" data-bs-title="Atur Part Form"><i class="fa-solid fa-file-circle-plus"></i></a>
                            </div>
                        `;
                    }
                },
                {
                    targets: [2],
                    className: "text-nowrap text-center align-middle",
                    render: (data, type, row, meta) => {
                        console.log(row.marker_details);

                        icon = "";

                        switch (data) {
                            case "SPREADING":
                                if (row.app != 'Y') {
                                    icon = `<i class="fas fa-file fa-lg"></i>`;
                                } else {
                                    icon = `<i class="fas fa-file fa-lg"></i>`;
                                }
                                break;
                            case "PENGERJAAN MARKER":
                            case "PENGERJAAN FORM CUTTING":
                            case "PENGERJAAN FORM CUTTING DETAIL":
                            case "PENGERJAAN FORM CUTTING SPREAD":
                                icon =
                                    `<i class="fas fa-sync-alt fa-spin fa-lg"></i>`;
                                break;
                            case "SELESAI PENGERJAAN":
                                icon = `<i class="fas fa-check fa-lg"></i>`;
                                break;
                        }

                        return icon;
                    }
                },
                {
                    targets: [3],
                    className: "text-nowrap text-center align-middle",
                    render: (data, type, row, meta) => {
                        let linkProcess = "{{ route('process-form-cut-input') }}/" + row.id;
                        if (row.tipe_form_cut == 'MANUAL') {
                            linkProcess = "{{ route('process-manual-form-cut') }}/" +row.id;
                        } else if (row.tipe_form_cut == 'PILOT') {
                            linkProcess = "{{ route('process-pilot-form-cut') }}/" + row.id;
                        }

                        return data ? `<a class='fw-bold' href='`+linkProcess+`' target='_blank'><u>`+data+`</u></a>` : "-";
                    }
                },
                {
                    targets: [4],
                    className: "text-nowrap text-center align-middle",
                    render: (data, type, row, meta) => {
                        return data ? `<a class='fw-bold' href='{{ route('edit-marker') }}/ `+row.marker_id+`' target='_blank'><u>`+data+`</u></a>` : "-";
                    }
                },
                {
                    targets: [5],
                    className: "text-nowrap text-center align-middle",
                    render: (data, type, row, meta) => {
                        return data ? `<a class='fw-bold' href='{{ route('manage-part-form') }}/ `+row.part_id+`' target='_blank'><u>`+data+`</u></a>` : "-";
                    }
                },
                {
                    targets: "_all",
                    className: "text-nowrap text-center align-middle",
                },
            ]
        });

        setupFormFilter('allocated-form-table', datatableAllocatedForm, {
            11: {
                options: ['NORMAL', 'PILOT', 'MANUAL'],
            },
        }, [0, 2]);

        function datatableAllocatedFormReload() {
            $("#allocated-form-table").DataTable().ajax.reload(null, false);
        }

        // Allocate Form -> Part
        // Modal pending disembunyikan saat modal alokasi dibuka, lalu dibuka lagi setelah modal alokasi ditutup.
        let reopenPendingModal = false;

        $('#pending-form-table').on('click', '.btn-allocate-form', function () {
            const row = datatablePendingForm.row($(this).data('row')).data();
            if (!row) return;

            $('#allocate-form-id').val(row.id);
            $('#allocate-no-form').val(row.no_form);
            $('#allocate-info-no-form').text(row.no_form || '-');
            $('#allocate-info-ws').text(row.act_costing_ws || '-');
            $('#allocate-info-color').text(row.color || '-');
            $('#allocate-info-panel').text(row.panel || '-');
            $('#allocate-info-no-cut').text(row.no_cut || '-');
            $('#allocate-info-lembar').text(row.total_lembar || '-');

            $('#allocate-submit').prop('disabled', true);
            $('#allocate-part-list').html('<div class="text-center text-muted py-3"><i class="fa-solid fa-spinner fa-spin"></i> Memuat part...</div>');

            reopenPendingModal = true;
            $('#pendingFormModal').modal('hide');
            $('#allocateFormModal').modal('show');

            loadAllocatePartOptions(row.id, row.part_id);
        });

        function loadAllocatePartOptions(formId, suggestedPartId) {
            $.ajax({
                type: 'get',
                url: '{{ route('get-form-part-options') }}',
                data: { form_id: formId },
                dataType: 'json',
                success: function (res) {
                    const $list = $('#allocate-part-list').empty();

                    if (res.status != 200 || !res.parts || res.parts.length < 1) {
                        $list.html('<div class="alert alert-warning mb-0"><i class="fa-solid fa-triangle-exclamation"></i> ' + $('<span>').text(res.message || 'Tidak ada part dengan WS & panel yang sama untuk form ini.').html() + '</div>');
                        return;
                    }

                    res.parts.forEach(part => {
                        const $option = $('<label class="allocate-part-option"></label>');
                        const $radio = $('<input type="radio" class="form-check-input d-none" name="allocate_part_id">').val(part.id);
                        const $info = $('<div class="flex-grow-1"></div>');

                        $info.append($('<div class="fw-bold"></div>').text(part.kode));
                        $info.append($('<div class="part-meta"></div>').text(
                            [part.act_costing_ws, part.style, part.panel].filter(Boolean).join(' / ')
                        ));
                        $option.append($radio, $info);
                        $option.append($('<span class="badge bg-light text-dark border"></span>').text(part.total_form + ' form'));
                        $option.append($('<span class="part-meta text-nowrap"></span>').text(part.tanggal || ''));

                        if (res.parts.length === 1 || part.id == suggestedPartId) {
                            $radio.prop('checked', true);
                        }

                        $list.append($option);
                    });

                    $('#allocate-submit').prop('disabled', $('input[name="allocate_part_id"]:checked').length < 1);
                },
                error: function (jqXHR) {
                    $('#allocate-part-list').html('<div class="alert alert-danger mb-0">Gagal memuat daftar part.</div>');
                    console.error(jqXHR);
                }
            });
        }

        $('#allocate-part-list').on('change', 'input[name="allocate_part_id"]', function () {
            $('#allocate-submit').prop('disabled', false);
        });

        $('#allocate-submit').on('click', function () {
            const $btn = $(this);
            const partId = $('input[name="allocate_part_id"]:checked').val();
            if (!partId) return;

            const originalHtml = $btn.html();
            $btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i> Menyimpan...');

            $.ajax({
                type: 'POST',
                url: '{!! route('store-part-form') !!}',
                data: {
                    part_id: partId,
                    partForms: [{
                        form_id: $('#allocate-form-id').val(),
                        no_form: $('#allocate-no-form').val(),
                        type: 'NORMAL',
                    }],
                },
                success: function (res) {
                    if (res.status == 200) {
                        iziToast.success({ title: 'Success', message: res.message, position: 'topCenter' });
                        $('#allocateFormModal').modal('hide');

                        // Reload tabel part -> drawCallback ikut me-reload summary & tabel form di modal
                        datatablePendingPart.ajax.reload(null, false);
                    } else {
                        iziToast.error({ title: 'Error', message: res.message, position: 'topCenter' });
                    }
                },
                error: function (jqXHR) {
                    iziToast.error({ title: 'Error', message: 'Terjadi kesalahan saat menyimpan.', position: 'topCenter' });
                    console.error(jqXHR);
                },
                complete: function () {
                    $btn.html(originalHtml).prop('disabled', !$('input[name="allocate_part_id"]:checked').val());
                }
            });
        });

        $('#allocateFormModal').on('hidden.bs.modal', function () {
            if (reopenPendingModal) {
                reopenPendingModal = false;
                $('#pendingFormModal').modal('show');
            }
        });
    </script>
@endsection
