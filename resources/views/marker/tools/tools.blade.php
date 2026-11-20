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

        .sub-size-checkbox {
        position: static;
        margin: 0;
        width: 16px;
        height: 16px;
        vertical-align: middle;
        cursor: pointer;
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
    <ul class="nav nav-tabs mb-3" id="markerToolsTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="tab-activity-log-btn" data-bs-toggle="tab" data-bs-target="#tab-activity-log" type="button" role="tab">
                <i class="fa-solid fa-clock-rotate-left"></i> Activity Log
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-split-size-btn" data-bs-toggle="tab" data-bs-target="#tab-split-size" type="button" role="tab">
                <i class="fa-solid fa-scissors"></i> Split Size
            </button>
        </li>
    </ul>

    <div class="tab-content" id="markerToolsTabContent">
        <div class="tab-pane fade show active" id="tab-activity-log" role="tabpanel">
            <div class="card card-sb">
                <div class="card-header">
                    <h5 class="card-title fw-bold mb-0">
                        <i class="fa-solid fa-clock-rotate-left"></i> Marker Activity Log
                    </h5>
                </div>
                <div class="card-body">
                    {{-- Filters --}}
            <div class="row g-3 mb-3 align-items-end">
                <div class="col-md-2">
                    <label class="form-label"><small><b>Dari</b></small></label>
                    <input type="date" id="dateFrom" class="form-control form-control-sm" value="{{ date('Y-m-d') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label"><small><b>Sampai</b></small></label>
                    <input type="date" id="dateTo" class="form-control form-control-sm" value="{{ date('Y-m-d') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label"><small><b>Model</b></small></label>
                    <select id="filterModel" class="form-control form-control-sm select2bs4">
                        <option value="">Semua Model</option>
                        <option value="App\Models\Marker\Marker">Marker</option>
                        <option value="App\Models\Marker\MarkerDetail">Marker Detail</option>
                        <option value="App\Models\Part\Part">Part</option>
                        <option value="App\Models\Part\PartDetail">Part Detail</option>
                        <option value="App\Models\Part\PartDetailSecondary">Part Detail Secondary</option>
                        <option value="App\Models\Part\PartForm">Part Form</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label"><small><b>Event</b></small></label>
                    <select id="filterEvent" class="form-control form-control-sm select2bs4">
                        <option value="">Semua Event</option>
                        <option value="created">Created</option>
                        <option value="updated">Updated</option>
                        <option value="deleted">Deleted</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <button class="btn btn-primary btn-sm w-100" onclick="tableReload()">
                        <i class="fa fa-search fa-xs"></i>
                    </button>
                </div>
                <div class="col-md-1">
                    <button class="btn btn-success btn-sm w-100" onclick="exportLog()">
                        <i class="fa fa-file-excel fa-xs"></i>
                    </button>
                </div>
            </div>

                    {{-- DataTable --}}
                    <div class="table-responsive">
                        <table id="activity-log-table" class="table table-bordered table-sm w-100">
                            <thead>
                                <tr>
                                    <th class="text-center">#</th>
                                    <th class="text-center">Tanggal</th>
                                    <th class="text-center">Subject ID</th>
                                    <th class="text-center">Event</th>
                                    <th class="text-center">Model</th>
                                    <th class="text-center">Action</th>
                                    <th class="text-center">Perubahan</th>
                                    <th class="text-center">Oleh</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="tab-split-size" role="tabpanel">
            <div class="card card-sb">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title fw-bold mb-0">
                            <i class="fa-solid fa-scissors"></i> Split Size
                        </h5>
                        <div class="d-flex gap-1">
                            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalImportSubSize">
                                <i class="fa fa-file-excel fa-xs"></i> Import
                            </button>
                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalSplitSize">
                                <i class="fa fa-plus fa-xs"></i> Tambah Sub Size
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    {{-- DataTable --}}
                    <div class="table-responsive">
                        <table id="sub-size-table" class="table table-bordered table-sm w-100">
                            <thead>
                                <tr>
                                    <th class="text-center align-middle" style="width:40px;"><input type="checkbox" id="subSizeCheckAll" class="sub-size-checkbox" title="Pilih semua di halaman ini"></th>
                                    <th class="text-center">Action</th>
                                    <th class="text-center">#</th>
                                    <th class="text-center">No. WS</th>
                                    <th class="text-center">Color</th>
                                    <th class="text-center">Size Asli</th>
                                    <th class="text-center">Sub Size</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-center">Dibuat</th>
                                    <th class="text-center">Oleh</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>

                    {{-- Selection toolbar (muncul hanya saat ada baris dipilih) --}}
                    <div id="subSizeSelectionBar" class="d-none justify-content-end align-items-center gap-2 mt-2">
                        <small class="fw-bold"><span id="subSizeSelectedCount">0</span> baris dipilih</small>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="clearSubSizeSelection()">
                            <i class="fa fa-times fa-xs"></i> Batal
                        </button>
                        <button type="button" id="btnMassDeleteSubSize" class="btn btn-danger btn-sm" onclick="massDeleteSubSize()">
                            <i class="fa fa-trash fa-xs"></i> Hapus Terpilih
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal: Tambah Sub Size --}}
    <div class="modal fade" id="modalSplitSize" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="formSplitSize">
                    <div class="modal-header bg-sb-secondary">
                        <h5 class="modal-title fw-bold"><i class="fa-solid fa-scissors"></i> Tambah Sub Size</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted mb-3"><small><i class="fa-solid fa-circle-info"></i> Pilih size order yang ingin dipecah, lalu tambahkan satu atau lebih nama sub size di bawah.</small></p>

                        <label class="form-label mb-2"><small><b>Pilih Size Order</b></small></label>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <select class="form-control select2bs4" id="split_ws_id" style="width: 100%;">
                                    <option selected value="">Pilih WS</option>
                                    @foreach ($orders as $order)
                                        <option value="{{ $order->id }}">{{ $order->kpno }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <select class="form-control select2bs4" id="split_color" style="width: 100%;" disabled>
                                    <option selected value="">Pilih Color</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <select class="form-control select2bs4" id="split_size" style="width: 100%;" disabled>
                                    <option selected value="">Pilih Size</option>
                                </select>
                            </div>
                            <input type="hidden" name="so_det_id" id="split_so_det_id" required>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label mb-0"><small><b>Nama Sub Size &amp; Qty</b></small></label>
                            <button type="button" id="btnAddSubSize" class="btn btn-outline-primary btn-sm" disabled>
                                <i class="fa fa-plus fa-xs"></i> Tambah Baris
                            </button>
                        </div>
                        <div id="subSizeRows">
                            <div class="input-group input-group-sm mb-2 sub-size-row">
                                <span class="input-group-text bg-light text-muted sub-size-row-number">1</span>
                                <input type="text" name="sub_size[]" class="form-control" placeholder="Sub Size" maxlength="20" required disabled>
                                <input type="number" name="qty[]" class="form-control" placeholder="Qty" min="1" step="1" style="max-width:120px;" required disabled>
                                <button type="button" class="btn btn-outline-danger btn-remove-sub-size" disabled>
                                    <i class="fa fa-trash fa-xs"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" id="btnSubmitSplitSize" class="btn btn-primary btn-sm" disabled>
                            <i class="fa fa-save fa-xs"></i> Simpan Sub Size
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal: Import Sub Size --}}
    <div class="modal fade" id="modalImportSubSize" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="formImportSubSize" enctype="multipart/form-data">
                    <div class="modal-header bg-sb-secondary">
                        <h5 class="modal-title fw-bold"><i class="fa fa-file-excel"></i> Import Sub Size</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted mb-3"><small><i class="fa-solid fa-circle-info"></i> Kolom (baris pertama sebagai header): <b>ws</b>, <b>color</b>, <b>size</b>, <b>sub_size</b>, <b>qty</b>, dan <b>dest</b> (opsional, wajib bila satu ws/color/size punya lebih dari satu dest). Jika ada satu baris saja yang ws/color/size-nya tidak ditemukan atau qty-nya tidak valid, seluruh import dibatalkan.</small></p>
                        <label class="drop-container">
                            <span class="drop-title">Pilih file Excel</span>
                            <input type="file" name="file" id="importSubSizeFile" accept=".xlsx,.xls,.csv" required>
                        </label>
                        <div class="mt-2">
                            <a href="{{ route('marker-tools-split-size-template') }}" class="btn btn-sb-secondary btn-sm">
                                <i class="fa fa-download fa-xs"></i> Download Template
                            </a>
                        </div>
                        <div id="importSubSizeResult" class="mt-3"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" id="btnSubmitImportSubSize" class="btn btn-success btn-sm">
                            <i class="fa fa-upload fa-xs"></i> Import
                        </button>
                    </div>
                </form>
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
        $('.select2bs4').not('#modalSplitSize .select2bs4').select2({ theme: 'bootstrap4', containerCssClass: 'form-control-sm rounded' });

        // Select2 inside a Bootstrap modal needs dropdownParent, otherwise the search input can't receive keyboard focus
        $('#modalSplitSize .select2bs4').select2({
            theme: 'bootstrap4',
            containerCssClass: 'form-control-sm rounded',
            dropdownParent: $('#modalSplitSize'),
        });

        let table;
        let subSizeTable;

        $(document).ready(function () {
            tableReload();
            subSizeTableReload();
        });

        // Step One (WS) on change — load Color list
        $('#split_ws_id').on('change', function () {
            $('#split_color').html('<option value="">Pilih Color</option>').val('').trigger('change').prop('disabled', true);
            $('#split_size').html('<option value="">Pilih Size</option>').val('').trigger('change').prop('disabled', true);
            $('#split_so_det_id').val('');

            if (!this.value) return;

            $.ajax({
                url: '{{ route("get-marker-colors") }}',
                type: 'get',
                data: { act_costing_id: this.value },
                success: function (res) {
                    $('#split_color').html(res).prop('disabled', false);
                },
            });
        });

        // Step Two (Color) on change — load Size list
        $('#split_color').on('change', function () {
            $('#split_size').html('<option value="">Pilih Size</option>').val('').trigger('change').prop('disabled', true);
            $('#split_so_det_id').val('');

            if (!this.value) return;

            $.ajax({
                url: '{{ route("get-marker-sizes") }}',
                type: 'get',
                data: {
                    act_costing_id: $('#split_ws_id').val(),
                    color: this.value,
                },
                success: function (res) {
                    let data = JSON.parse(res).data;
                    let options = '<option value="">Pilih Size</option>';
                    data.forEach(function (row) {
                        options += `<option value="${row.so_det_id}">${row.size}</option>`;
                    });
                    $('#split_size').html(options).prop('disabled', false);
                },
            });
        });

        // Step Three (Size) on change — set hidden so_det_id, unlock Sub Size section
        $('#split_size').on('change', function () {
            $('#split_so_det_id').val(this.value);

            let unlocked = !!this.value;
            $('#btnAddSubSize').prop('disabled', !unlocked);
            $('#btnSubmitSplitSize').prop('disabled', !unlocked);
            $('#subSizeRows input[name="sub_size[]"], #subSizeRows input[name="qty[]"]').prop('disabled', !unlocked);
        });

        // Renumber Sub Size rows
        function renumberSubSizeRows() {
            $('#subSizeRows .sub-size-row').each(function (i) {
                $(this).find('.sub-size-row-number').text(i + 1);
            });
            $('#subSizeRows .btn-remove-sub-size').prop('disabled', $('#subSizeRows .sub-size-row').length <= 1);
        }

        // Add a new Sub Size row
        $('#btnAddSubSize').on('click', function () {
            let $row = $(`
                <div class="input-group input-group-sm mb-2 sub-size-row">
                    <span class="input-group-text bg-light text-muted sub-size-row-number"></span>
                    <input type="text" name="sub_size[]" class="form-control" placeholder="Sub Size" maxlength="20" required>
                    <input type="number" name="qty[]" class="form-control" placeholder="Qty" min="1" step="1" style="max-width:120px;" required>
                    <button type="button" class="btn btn-outline-danger btn-remove-sub-size">
                        <i class="fa fa-trash fa-xs"></i>
                    </button>
                </div>
            `);
            $('#subSizeRows').append($row);
            renumberSubSizeRows();
            $row.find('input').trigger('focus');
        });

        // Remove a Sub Size row
        $('#subSizeRows').on('click', '.btn-remove-sub-size', function () {
            $(this).closest('.sub-size-row').remove();
            renumberSubSizeRows();
        });

        $('#formSplitSize').on('submit', function (e) {
            e.preventDefault();

            let $submitBtn = $('#btnSubmitSplitSize');
            $submitBtn.prop('disabled', true);

            $.ajax({
                type: 'POST',
                url: '{{ route("marker-tools-split-size") }}',
                data: $(this).serialize(),
                success: function (response) {
                    Swal.fire({ title: response.message, icon: 'success' });
                    $('#formSplitSize')[0].reset();
                    $('#subSizeRows .sub-size-row:gt(0)').remove();
                    $('#split_ws_id').val('').trigger('change');
                    $('#split_color, #split_size').prop('disabled', true).html('<option value="">Pilih Color</option>').trigger('change');
                    $('#btnAddSubSize, #btnSubmitSplitSize, #subSizeRows input[name="sub_size[]"], #subSizeRows input[name="qty[]"]').prop('disabled', true);
                    $('#modalSplitSize').modal('hide');
                    subSizeTableReload();
                },
                error: function (xhr) {
                    let message = xhr.responseJSON?.message ?? 'Gagal menambahkan sub size';
                    Swal.fire({ title: message, icon: 'error' });
                },
                complete: function () {
                    $submitBtn.prop('disabled', false);
                }
            });
        });

        // Toggle perubahan — delegated karena DataTable render dinamis
        $(document).on('click', '.btn-toggle-perubahan', function (e) {
            e.preventDefault();
            let $wrap = $(this).prev('.perubahan-wrap');
            let expanded = $wrap.data('expanded');
            if (expanded) {
                $wrap.css('max-height', '42px').data('expanded', false);
                $(this).html('<i class="fas fa-chevron-down fa-xs"></i> Lihat');
            } else {
                $wrap.css('max-height', $wrap[0].scrollHeight + 'px').data('expanded', true);
                $(this).html('<i class="fas fa-chevron-up fa-xs"></i> Sembunyikan');
            }
        });

        function exportLog() {
            Swal.fire({
                title: 'Please Wait...',
                html: 'Menyiapkan file Excel...',
                didOpen: () => { Swal.showLoading(); },
                allowOutsideClick: false,
            });

            $.ajax({
                type: 'GET',
                url: '{{ route("marker-activity-log-export") }}',
                data: {
                    dateFrom : $('#dateFrom').val(),
                    dateTo   : $('#dateTo').val(),
                    model    : $('#filterModel').val(),
                    event    : $('#filterEvent').val(),
                },
                xhrFields: { responseType: 'blob' },
                success: function(response) {
                    Swal.close();
                    let link = document.createElement('a');
                    link.href = window.URL.createObjectURL(new Blob([response]));
                    link.download = 'Marker_Activity_Log_' + $('#dateFrom').val() + '_' + $('#dateTo').val() + '.xlsx';
                    link.click();
                },
                error: function() { Swal.fire({ title: 'Gagal Export', icon: 'error' }); }
            });
        }

        function tableReload() {
            if (table) table.destroy();

            table = $('#activity-log-table').DataTable({
                ordering: false,
                searching: false,
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route("marker-activity-log") }}',
                    data: function (d) {
                        d.dateFrom = $('#dateFrom').val();
                        d.dateTo   = $('#dateTo').val();
                        d.model    = $('#filterModel').val();
                        d.event    = $('#filterEvent').val();
                    },
                },
                columns: [
                    { data: 'id' },
                    { data: 'created_at' },
                    { data: 'subject_id' },
                    { data: 'description' },
                    { data: 'model_name' },
                    { data: 'request_info', orderable: false, searchable: false },
                    { data: 'properties_formatted', orderable: false, searchable: false },
                    { data: 'causer_name' },
                ],
                columnDefs: [
                    {
                        targets: [0],
                        className: 'text-center',
                        render: (data, type, row, meta) => meta.row + 1,
                    },
                    {
                        targets: [1],
                        className: 'text-center',
                        render: (data) => data ? data.substring(0, 19).replace('T', ' ') : '-',
                    },
                    {
                        targets: [2],
                        className: 'text-center',
                    },
                    {
                        targets: [3],
                        className: 'text-center',
                        render: (data) => {
                            const map = { created: 'success', updated: 'warning', deleted: 'danger' };
                            const c   = map[data] ?? 'secondary';
                            return `<span class="badge badge-${c}">${data}</span>`;
                        },
                    },
                    {
                        targets: [4],
                        className: 'text-center text-nowrap',
                        render: (data) => `<span class="badge badge-info">${data}</span>`,
                    },
                    {
                        targets: [5],
                        className: 'text-nowrap',
                    },
                    {
                        targets: [6],
                        render: (data) => {
                            if (!data || data === '-') return '-';
                            return `
                                <div class="perubahan-wrap" style="max-height:45px;overflow:hidden;transition:max-height .3s ease;">
                                    <small>${data}</small>
                                </div>
                                <a href="#" class="btn-toggle-perubahan text-primary" style="font-size:11px;cursor:pointer;">
                                    <i class="fas fa-chevron-down fa-xs"></i> Lihat
                                </a>`;
                        },
                    },
                    {
                        targets: [7],
                        className: 'text-nowrap',
                        render: (data) => data ?? '<span class="text-muted">System</span>',
                    },
                    {
                        targets: '_all',
                        defaultContent: '-',
                    },
                ],
            });

            // Header filter — skip kolom #, Action, Perubahan
            const skipCols = [0, 5, 6];
            $('#activity-log-table thead tr:eq(1)').remove();
            $('#activity-log-table thead tr:eq(0)').clone(false).appendTo('#activity-log-table thead');
            $('#activity-log-table thead tr:eq(1) th').each(function (i) {
                if (skipCols.includes(i)) { $(this).html(''); return; }
                $(this).html('<input type="text" class="form-control form-control-sm" style="width:100%;">');
                $('input', this).on('keyup change', function () {
                    if (table.column(i).search() !== this.value) {
                        table.column(i).search(this.value).draw();
                    }
                });
            });
        }

        // ===== Sub Size: selection & mass delete =====
        let subSizeSelected = new Map(); // id => label

        function escapeHtml(str) {
            return $('<div>').text(str ?? '').html();
        }

        function updateSubSizeSelectionUI() {
            let count = subSizeSelected.size;
            $('#subSizeSelectedCount').text(count);
            $('#subSizeSelectionBar').toggleClass('d-none', count === 0).toggleClass('d-flex', count > 0);

            // Sinkronkan checkbox baris & header dengan halaman yang tampil
            let $rowChecks = $('#sub-size-table tbody .sub-size-check');
            $rowChecks.each(function () {
                let checked = subSizeSelected.has(String($(this).val()));
                this.checked = checked;
                $(this).closest('tr').toggleClass('table-active', checked);
            });
            let checkedOnPage = $rowChecks.filter(':checked').length;
            $('#subSizeCheckAll')
                .prop('checked', $rowChecks.length > 0 && checkedOnPage === $rowChecks.length)
                .prop('indeterminate', checkedOnPage > 0 && checkedOnPage < $rowChecks.length);
        }

        function setSubSizePageSelection(checked) {
            if (!subSizeTable) return;
            subSizeTable.rows({ page: 'current' }).data().each(function (row) {
                if (checked) {
                    subSizeSelected.set(String(row.id), row.no_ws + ' / ' + row.color + ' / ' + row.original_size + ' / ' + row.sub_size);
                } else {
                    subSizeSelected.delete(String(row.id));
                }
            });
            updateSubSizeSelectionUI();
        }

        function clearSubSizeSelection() {
            subSizeSelected.clear();
            updateSubSizeSelectionUI();
        }

        $('#subSizeCheckAll').on('click', function (e) {
            e.stopPropagation();
            setSubSizePageSelection(this.checked);
        });

        $('#sub-size-table tbody').on('change', '.sub-size-check', function () {
            let row = subSizeTable.row($(this).closest('tr')).data();
            if (this.checked) {
                subSizeSelected.set(String(row.id), row.no_ws + ' / ' + row.color + ' / ' + row.original_size + ' / ' + row.sub_size);
            } else {
                subSizeSelected.delete(String(row.id));
            }
            updateSubSizeSelectionUI();
        });

        // Klik baris (selain tombol/input) juga toggle pilihan
        $('#sub-size-table tbody').on('click', 'tr', function (e) {
            if ($(e.target).closest('button, a, input, .sub-size-check').length) return;
            let $check = $(this).find('.sub-size-check');
            if ($check.length) $check.prop('checked', !$check.prop('checked')).trigger('change');
        });

        function massDeleteSubSize() {
            if (subSizeSelected.size === 0) return;

            let list = Array.from(subSizeSelected.values()).slice(0, 20).map(l => '<li>' + escapeHtml(l) + '</li>').join('');
            let more = subSizeSelected.size > 20 ? '<li>... dan ' + (subSizeSelected.size - 20) + ' lainnya</li>' : '';

            Swal.fire({
                icon: 'warning',
                title: 'Hapus ' + subSizeSelected.size + ' sub size?',
                html: '<ul class="text-start small mb-0" style="max-height:200px;overflow:auto;">' + list + more + '</ul>',
                showCancelButton: true,
                confirmButtonText: 'Hapus',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#dc3545',
            }).then((result) => {
                if (!result.isConfirmed) return;

                Swal.fire({ title: 'Please Wait...', didOpen: () => Swal.showLoading(), allowOutsideClick: false });

                $.ajax({
                    type: 'POST',
                    url: '{{ route("marker-tools-split-size-mass-delete") }}',
                    data: {
                        _token: '{{ csrf_token() }}',
                        ids: Array.from(subSizeSelected.keys()),
                    },
                    success: function (res) {
                        Swal.fire({ title: res.message, icon: 'success' });
                        clearSubSizeSelection();
                        subSizeTable.ajax.reload(null, false);
                    },
                    error: function (xhr) {
                        Swal.fire({ title: 'Gagal', text: xhr.responseJSON?.message ?? 'Gagal menghapus sub size', icon: 'error' });
                        subSizeTable.ajax.reload(null, false);
                    },
                });
            });
        }

        // ===== Sub Size: import =====
        $('#modalImportSubSize').on('hidden.bs.modal', function () {
            $('#formImportSubSize')[0].reset();
            $('#importSubSizeResult').html('');
        });

        $('#formImportSubSize').on('submit', function (e) {
            e.preventDefault();

            let $btn = $('#btnSubmitImportSubSize');
            let formData = new FormData(this);
            formData.append('_token', '{{ csrf_token() }}');

            $btn.prop('disabled', true);
            $('#importSubSizeResult').html('<div class="text-muted small"><i class="fa fa-spinner fa-spin"></i> Memproses import...</div>');

            $.ajax({
                type: 'POST',
                url: '{{ route("marker-tools-split-size-import") }}',
                data: formData,
                processData: false,
                contentType: false,
                success: function (res) {
                    let skipped = (res.skipped || []).map(s => '<li>' + escapeHtml(s) + '</li>').join('');
                    $('#importSubSizeResult').html(
                        '<div class="alert alert-success py-2 mb-0"><b>' + escapeHtml(res.message) + '</b>' +
                        (skipped ? '<ul class="small mb-0 mt-1" style="max-height:200px;overflow:auto;">' + skipped + '</ul>' : '') +
                        '</div>'
                    );
                    $('#importSubSizeFile').val('');
                    Swal.fire({ title: res.message, icon: 'success' });
                    subSizeTableReload();
                },
                error: function (xhr) {
                    let res = xhr.responseJSON || {};
                    let message = res.message ?? 'Import gagal';

                    // Error validasi Laravel (mis. file salah format)
                    if (xhr.status === 422 && res.errors && !Array.isArray(res.errors)) {
                        res.errors = Object.values(res.errors).flat();
                    }

                    let details = (res.errors || []).map(s => '<li>' + escapeHtml(s) + '</li>').join('');
                    $('#importSubSizeResult').html(
                        '<div class="alert alert-danger py-2 mb-0"><b>' + escapeHtml(message) + '</b>' +
                        (details ? '<ul class="small mb-0 mt-1" style="max-height:250px;overflow:auto;">' + details + '</ul>' : '') +
                        '</div>'
                    );
                    Swal.fire({
                        title: message,
                        html: details ? '<ul class="text-start small mb-0" style="max-height:250px;overflow:auto;">' + details + '</ul>' : '',
                        icon: 'error',
                        width: 700,
                    });
                },
                complete: function () {
                    $btn.prop('disabled', false);
                },
            });
        });

        function subSizeTableReload() {
            if (subSizeTable) subSizeTable.destroy();

            subSizeTable = $('#sub-size-table').DataTable({
                ordering: false,
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route("marker-tools-split-size-list") }}',
                },
                columns: [
                    { data: 'id', searchable: false },
                    { data: 'id', searchable: false },
                    { data: 'id', searchable: false },
                    { data: 'no_ws' },
                    { data: 'color' },
                    { data: 'original_size' },
                    { data: 'sub_size' },
                    { data: 'qty' },
                    { data: 'created_at' },
                    { data: 'created_by_username' },
                ],
                drawCallback: function () {
                    updateSubSizeSelectionUI();
                },
                columnDefs: [
                    {
                        targets: [0],
                        className: 'text-center align-middle',
                        render: (data) => `<input type="checkbox" class="sub-size-checkbox sub-size-check" value="${data}">`,
                    },
                    {
                        targets: [1],
                        className: 'text-center',
                        render: (data, type, row, meta) => {
                            let dataMessage = row.no_ws + " / " + row.color + " / " + row.sub_size;
                            return `
                                <button class='btn btn-danger btn-sm' data='`+JSON.stringify(row)+`' data-url='{{ route('marker-tools-split-size-delete') }}/`+row['id']+`' onclick='deleteData(this, null, "`+dataMessage+`")' data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Hapus Sub Size">
                                    <i class='fa fa-trash'></i>
                                </button>
                            `;
                        },
                    },
                    {
                        targets: [2],
                        className: 'text-center',
                        render: (data, type, row, meta) => meta.settings._iDisplayStart + meta.row + 1,
                    },
                    {
                        targets: [3, 4, 5],
                        className: 'text-center text-nowrap',
                    },
                    {
                        targets: [6],
                        className: 'text-center text-nowrap',
                        render: (data) => `<span class="badge badge-info">${data}</span>`,
                    },
                    {
                        targets: [7],
                        className: 'text-end text-nowrap',
                        render: (data) => (data === null || data === undefined) ? '-' : Number(data).toLocaleString('id-ID'),
                    },
                    {
                        targets: [8],
                        className: 'text-center',
                        render: (data) => data ? data.substring(0, 19).replace('T', ' ') : '-',
                    },
                    {
                        targets: [9],
                        className: 'text-center',
                        render: (data) => data ?? '<span class="text-muted">System</span>',
                    },
                    {
                        targets: '_all',
                        defaultContent: '-',
                    },
                ],
            });

            // Header filter — skip kolom checkbox, Action, #
            const subSizeSkipCols = [0, 1, 2];
            $('#sub-size-table thead tr:eq(1)').remove();
            $('#sub-size-table thead tr:eq(0)').clone(false).appendTo('#sub-size-table thead');
            $('#sub-size-table thead tr:eq(1) th').each(function (i) {
                if (subSizeSkipCols.includes(i)) { $(this).html(''); return; }
                $(this).html('<input type="text" class="form-control form-control-sm" style="width:100%;">');
                $('input', this).on('keyup change', function () {
                    if (subSizeTable.column(i).search() !== this.value) {
                        subSizeTable.column(i).search(this.value).draw();
                    }
                });
            });
        }
    </script>
@endsection
