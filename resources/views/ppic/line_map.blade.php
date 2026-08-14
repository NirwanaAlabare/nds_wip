@extends('layouts.index')

@section('custom-link')
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <!-- Flatpickr (Start Day Calendar picker only) -->
    <link rel="stylesheet" href="{{ asset('plugins/flatpickr/flatpickr.min.css') }}">
    <style>
        .flatpickr-day.has-line-plan {
            position: relative;
        }

        .flatpickr-day.has-line-plan::after {
            content: '';
            position: absolute;
            bottom: 4px;
            left: 50%;
            transform: translateX(-50%);
            width: 5px;
            height: 5px;
            border-radius: 50%;
            background: #1e5f2e;
        }

        .flatpickr-day.has-line-plan.selected::after,
        .flatpickr-day.has-line-plan.today::after {
            background: #fff;
        }

        .flatpickr-day.is-holiday {
            color: #dc3545;
        }

        .flatpickr-day.is-holiday.selected {
            color: #fff;
        }
    </style>
    <style>
        .line-map-calendar-wrapper {
            overflow: auto;
            max-height: 65vh;
            max-width: 100%;
            border-top: 1px solid #dee2e6;
            border-left: 1px solid #dee2e6;
        }

        .line-map-table {
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 0 !important;
            width: max-content !important;
        }

        .line-map-table th,
        .line-map-table td {
            white-space: nowrap;
            vertical-align: middle;
            border-right: 1px solid #dee2e6;
            border-bottom: 1px solid #dee2e6;
        }

        .line-map-table tbody td {
            vertical-align: top;
        }

        .line-map-line-col {
            min-width: 160px;
            position: sticky;
            left: 0;
            z-index: 2;
            background-color: #fff !important;
            box-shadow: 2px 0 4px -2px rgba(0, 0, 0, .15);
            white-space: normal !important;
            vertical-align: top !important;
        }

        .line-map-history-product-group {
            font-size: .7rem;
            color: #6c757d;
            white-space: normal;
        }

        .line-map-table thead th {
            position: sticky;
            top: 0;
            z-index: 3;
            background-color: #fff !important;
        }

        .line-map-table thead th.line-map-line-col {
            z-index: 4;
        }

        .line-map-table thead th:not(.line-map-line-col) {
            text-align: center;
            width: 1%;
        }

        .line-map-date-day {
            font-size: .7rem;
            font-weight: 600;
            color: #6c757d;
            text-transform: uppercase;
        }

        .line-map-date-num {
            font-size: .8rem;
        }

        .line-map-table th.is-sunday .line-map-date-day,
        .line-map-table th.is-sunday .line-map-date-num {
            color: #dc3545;
        }

        .line-map-table th.is-today,
        .line-map-table td.is-today {
            background-color: #eaf2ff !important;
        }

        .line-map-table th.is-today {
            box-shadow: inset 0 -2px 0 0 #0d6efd;
        }

        .line-map-table td:not(.line-map-line-col) {
            text-align: center;
            width: 1%;
            padding: 3px 4px;
        }

        .line-map-plan-cell {
            position: relative;
        }

        .line-map-plan-cell::before {
            content: '';
            position: absolute;
            left: 0;
            right: 0;
            top: 10px;
            height: 4px;
            background-color: var(--plan-line-color, #6f42c1);
            opacity: .45;
            z-index: 0;
        }

        .line-map-plan-start::before {
            left: 50%;
            border-radius: 4px 0 0 4px;
        }

        .line-map-plan-end::before {
            right: 50%;
            border-radius: 0 4px 4px 0;
        }

        .line-map-plan-start.line-map-plan-end::before {
            left: 35%;
            right: 35%;
            border-radius: 4px;
        }

        .line-map-cell-stack {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 4px;
            position: relative;
            z-index: 1;
        }

        .line-map-box {
            min-width: 100px;
            max-width: 170px;
            border-radius: 10px;
            padding: 4px 8px;
            text-align: left;
            font-size: 10.5px;
        }

        .line-map-box-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 6px;
            font-size: 11px;
            font-weight: 700;
            white-space: nowrap;
            margin-bottom: 2px;
        }

        .line-map-box-header .box-buyer {
            overflow: hidden;
            text-overflow: ellipsis;
            min-width: 0;
        }

        .line-map-box-row {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 8px;
            white-space: nowrap;
            line-height: 1.5;
        }

        .line-map-box-row .row-label {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            min-width: 0;
        }

        .line-map-box-row .row-qty {
            font-weight: 700;
            flex: 0 0 auto;
        }

        .line-map-box-plan {
            background-color: var(--dot-color, #6f42c1);
            color: var(--font-color, #fff);
            cursor: grab;
            user-select: none;
            box-shadow: 0 1px 2px rgba(0, 0, 0, .15);
        }

        .line-map-box-plan:active {
            cursor: grabbing;
        }

        .line-map-box-plan-readonly {
            cursor: default;
        }

        .line-map-box-plan .row-qty {
            color: var(--font-color, #fff);
        }

        .line-map-box-actual {
            background-color: #fff;
            border: 2px solid #198754;
        }

        .line-map-box-actual .line-map-box-header {
            color: #146c43;
        }

        .line-map-box-actual .row-qty {
            color: #198754;
        }

        .line-map-drop-target {
            transition: background-color .15s ease, box-shadow .15s ease;
        }

        .line-map-box-actual-detail {
            cursor: pointer;
        }

        .line-map-box-actual-detail:hover {
            text-decoration: underline;
        }

        .line-map-drop-target.drag-over {
            background-color: rgba(13, 110, 253, .08);
            box-shadow: inset 0 0 0 2px rgba(13, 110, 253, .35);
        }

        .line-map-ghost-target {
            position: relative;
        }

        .line-map-ghost-box {
            position: absolute;
            inset: 2px;
            border-radius: 6px;
            border: 2px dashed;
            font-size: 9px;
            font-weight: 700;
            padding: 2px 3px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            line-height: 1.1;
            pointer-events: none;
            z-index: 5;
            overflow: hidden;
        }

        .line-map-ghost-box-drag {
            border-color: #0d6efd;
            color: #0450c7;
            background-color: rgba(13, 110, 253, .15);
        }

        .line-map-ghost-box-push {
            border-color: #fd7e14;
            color: #b85c00;
            background-color: rgba(253, 126, 20, .15);
        }

        .line-map-section-card {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 12px 14px;
            background-color: #f8f9fa;
        }

        .line-map-section-title {
            font-weight: 700;
            font-size: .8rem;
            text-transform: uppercase;
            letter-spacing: .03em;
            color: #495057;
            margin-bottom: 10px;
        }

        #qtyAllocationContainer .qty-alloc-row {
            border: 1px solid #dee2e6;
            border-left: 3px solid #0d6efd;
            border-radius: 6px;
        }

        #qtyAllocationContainer .qty-alloc-row .card-body {
            padding: 10px 12px;
        }

        #qtyAllocationContainer .qty-alloc-row+.qty-alloc-row {
            margin-top: 8px;
        }

        #qtyAllocationContainer .ramp-up-row .input-group-text.ramp-up-day-label {
            min-width: 62px;
            justify-content: center;
        }

        .line-map-temp-card {
            border: 1px dashed #adb5bd;
            border-radius: 8px;
            padding: 10px 14px;
            background-color: #f8f9fa;
            margin-bottom: 12px;
        }

        .line-map-temp-title {
            font-weight: 700;
            font-size: .8rem;
            text-transform: uppercase;
            letter-spacing: .03em;
            color: #495057;
            margin-bottom: 8px;
        }

        .line-map-temp-slots {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .line-map-temp-slot {
            width: 170px;
            min-height: 54px;
        }

        .line-map-temp-slot .line-map-box {
            max-width: none;
            width: 100%;
        }

        .line-map-temp-empty {
            width: 100%;
            height: 100%;
            min-height: 54px;
            border: 1px dashed #ced4da;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-size: 11px;
            transition: background-color .15s ease, box-shadow .15s ease;
        }

        .line-map-temp-dropzone.drag-over .line-map-temp-empty {
            background-color: rgba(13, 110, 253, .08);
            box-shadow: inset 0 0 0 2px rgba(13, 110, 253, .35);
        }
    </style>
@endsection

@section('content')
    <div class="modal fade" id="newLineMapModal" tabindex="-1" role="dialog" aria-labelledby="newLineMapModalLabel"
        data-bs-backdrop="static" aria-hidden="true">
        <form action="{{ route('store_ppic_line_map') }}" method="post" onsubmit="submitLineMapForm(this, event)"
            name="formLineMap" id="formLineMap">
            @csrf
            <input type="hidden" id="groupid" name="group_id" value="">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header bg-sb text-light">
                        <h3 class="modal-title fs-5" id="lineMapModalTitle">Tambah Line Map</h3>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="line-map-section-card mb-3">
                            <div class="line-map-section-title">Informasi Order</div>
                            <div class="row g-2">
                                <div class="col-md-3">
                                    <div class="form-group mb-0">
                                        <label class="form-label">Style :</label>
                                        <input type="text" class="form-control form-control-sm" id="txtstyle"
                                            name="txtstyle" placeholder="Cnth: POLO ZIP SIDE SLIT" value=""
                                            autocomplete="off" style="text-transform: uppercase;"
                                            oninput="this.value = this.value.toUpperCase();">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-0">
                                        <label class="form-label">Buyer :</label>
                                        <input type="text" class="form-control form-control-sm" id="txtbuyer"
                                            name="txtbuyer" value="" autocomplete="off"
                                            style="text-transform: uppercase;"
                                            oninput="this.value = this.value.toUpperCase();">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-0">
                                        <label class="form-label">Product Group :</label>
                                        <select class="form-control select2bs4 form-control-sm" id="cboproductgroup"
                                            name="cboproductgroup">
                                            <option value="">- Pilih Product Group -</option>
                                            @foreach ($productGroupList as $pg)
                                                <option value="{{ $pg }}">{{ $pg }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-0">
                                        <label class="form-label">SMV :</label>
                                        <input type="number" class="form-control form-control-sm" id="txtsmv"
                                            name="txtsmv" placeholder="Cnth: 12.5" value="" autocomplete="off"
                                            step="any" oninput="recalcAllQtyRows();" onchange="recalcAllQtyRows();">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="line-map-section-card mb-3">
                            <div class="line-map-section-title">Warna Custom</div>
                            <div class="row g-2">
                                <div class="col-md-3">
                                    <div class="form-check mb-1">
                                        <input class="form-check-input" type="checkbox" id="chkCustomColor"
                                            onchange="toggleCustomColor();">
                                        <label class="form-check-label" for="chkCustomColor">Gunakan Warna Custom</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-0">
                                        <label class="form-label">Warna Latar :</label>
                                        <input type="color" class="form-control form-control-sm form-control-color w-100"
                                            id="txtcolor" name="txtcolor" value="#6f42c1" disabled>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-0">
                                        <label class="form-label">Warna Font :</label>
                                        <input type="color"
                                            class="form-control form-control-sm form-control-color w-100"
                                            id="txtfontcolor" name="txtfontcolor" value="#ffffff" disabled>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="line-map-section-card">
                            <div class="line-map-section-title">Alokasi Line, Kapasitas &amp; Qty</div>

                            <div class="form-group">
                                <label class="form-label">Line (bisa pilih lebih dari satu) :</label>
                                <select multiple class="form-control select2bs4" id="cbolinemulti">
                                    @foreach ($line as $row)
                                        <option value="{{ $row->username }}">{{ $row->FullName }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Order Qty Total :</label>
                                <input type="text" class="form-control form-control-sm" id="txtorderqtytotal"
                                    placeholder="Cnth: 1.000" autocomplete="off" inputmode="numeric"
                                    oninput="this.value = this.value.replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.'); updateQtyTotals();"
                                    onchange="updateQtyTotals();">
                            </div>

                            <div class="form-group mb-0">
                                <label class="form-label">Kapasitas &amp; Qty per Line :</label>
                                <div id="qtyAllocationContainer"></div>
                                <div id="qtyAllocationEmpty" class="text-muted small fst-italic">
                                    Pilih line di atas untuk mengatur kapasitas &amp; qty per line.
                                </div>
                                <small id="qtyTotalSummary" class="fw-bold d-block mt-1"></small>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal"><i
                                class="fas fa-times-circle"></i> Tutup</button>
                        <button type="submit" class="btn btn-outline-success" id="btnSimpanLineMap"><i
                                class="fas fa-check"></i> Simpan</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="modal fade" id="lineMapColorModal" tabindex="-1" role="dialog"
        aria-labelledby="lineMapColorModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-sb text-light">
                    <h3 class="modal-title fs-5" id="lineMapColorModalLabel">Atur Warna Line Map</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <small class="text-muted d-block mb-2">Menampilkan order sesuai filter tanggal yang sedang
                        aktif di Daftar Line Map. Warna berlaku untuk seluruh line &amp; tanggal pada order
                        tersebut.</small>
                    <table class="table table-sm table-bordered align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width: 80px;">Warna Latar</th>
                                <th style="width: 80px;">Warna Font</th>
                                <th>Style</th>
                                <th>Buyer</th>
                                <th>Line</th>
                                <th>Periode</th>
                            </tr>
                        </thead>
                        <tbody id="lineMapColorRows"></tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal"><i
                            class="fas fa-times-circle"></i> Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-sb">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-map-marker-alt"></i> PPIC Line Map</h5>
            <a href="{{ route('ppic_line_map_live', ['tgl_dari' => $filterStart, 'tgl_sampai' => $filterEnd]) }}"
                target="_blank" class="btn btn-outline-light btn-sm">
                <i class="fas fa-tv"></i> Live View
            </a>
        </div>
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-end flex-wrap gap-2 mb-3">
                @if ($canEditLineMap)
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal"
                            data-bs-target="#newLineMapModal" onclick="openNewLineMap()">
                            <i class="fas fa-plus"></i> New
                        </button>
                        <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal"
                            data-bs-target="#lineMapColorModal" onclick="renderColorModal()">
                            <i class="fas fa-palette"></i> Atur Warna
                        </button>
                        <button type="button" class="btn btn-outline-warning" id="btnUndoLineMap"
                            onclick="undoLineMap()" disabled>
                            <i class="fas fa-undo"></i> Undo <span id="undoLineMapCount"></span>
                        </button>
                    </div>
                @else
                    <div></div>
                @endif

                <form action="{{ route('ppic_line_map') }}" method="get" class="d-flex align-items-end gap-2">
                    <div class="form-group mb-0">
                        <label class="form-label mb-0 d-block">&nbsp;</label>
                        <small class="text-muted" id="lineMapLastUpdated">Last Update:
                            {{ $lastUpdated ? date('d-m-Y H:i:s', strtotime($lastUpdated)) : '-' }}</small>
                    </div>
                    <div class="form-group mb-0">
                        <label class="form-label mb-0">Dari Tanggal :</label>
                        <input type="text" class="form-control form-control-sm line-map-filter-date" name="tgl_dari"
                            value="{{ $filterStart }}" autocomplete="off">
                    </div>
                    <div class="form-group mb-0">
                        <label class="form-label mb-0">Sampai Tanggal :</label>
                        <input type="text" class="form-control form-control-sm line-map-filter-date" name="tgl_sampai"
                            value="{{ $filterEnd }}" autocomplete="off">
                    </div>
                    <button type="submit" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <a href="{{ route('ppic_line_map') }}" class="btn btn-outline-danger btn-sm">
                        <i class="fas fa-times"></i> Reset
                    </a>
                </form>
            </div>

            <div id="lineMapTempWrapper">
                @include('ppic.partials.line_map_temp_holding')
            </div>

            <div class="line-map-calendar-wrapper" id="lineMapCalendarWrapper">
                @include('ppic.partials.line_map_calendar')
            </div>
        </div>
    </div>
    </div>

    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-list"></i> Daftar Line Map</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="tblLineMapList" class="table table-bordered table-sm align-middle">
                    <thead>
                        <tr>
                            <th>Line</th>
                            <th>Tgl Plan</th>
                            <th>Tgl Finish</th>
                            <th>Style</th>
                            <th>Product Group</th>
                            <th>Buyer</th>
                            <th>SMV</th>
                            <th>Efficiency</th>
                            <th>Order Qty</th>
                            <th>Total Days</th>
                            <th>Ramp Up</th>
                            <th>Created By</th>
                            <th>Updated At</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="lineMapListBody">
                        @include('ppic.partials.line_map_list_rows')
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('custom-script')
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <!-- DataTables -->
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <!-- Flatpickr (Start Day Calendar picker only) -->
    <script src="{{ asset('plugins/flatpickr/flatpickr.min.js') }}"></script>
    <script>
        $(document).on('select2:open', () => {
            document.querySelector('.select2-search__field').focus();
        });

        $('.select2bs4').select2({
            theme: 'bootstrap4',
            containerCssClass: 'form-control-sm rounded',
            dropdownParent: $('#newLineMapModal')
        });

        let productGroupByLine = @json($productGroupByLine);
        const lineFullNameByUsername = @json($lineNameByUsername);
        let lineMapColorGroups = @json($colorGroups);
        let lineNextAvailableDate = @json($lineNextAvailableDate);
        const holidayDateSet = new Set(@json($holidayDates));
        let undoCount = @json($undoCount ?? 0);
        // Dates that already have a plan sitting on them, per line, keyed by
        // "YYYY-MM-DD" -> [{style, buyer, qty}]. Powers the dot marker + tooltip
        // on the "Start Day Calendar" picker so users can see occupied dates
        // without leaving the New/Edit Line Map form.
        let occupiedDatesByLine = @json($occupiedDatesByLine);

        document.querySelectorAll('.line-map-filter-date').forEach(function(el) {
            flatpickr(el, {
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: 'd-m-Y',
                allowInput: true,
                defaultDate: el.value || null,
            });
        });

        let previousProductGroup = '';
        let suppressProductGroupWarning = false;

        function lineHasProductGroupHistory(line, productGroup) {
            if (!line || !productGroup) return true;
            const groups = productGroupByLine[line] || [];
            return groups.some(g => g.product_group === productGroup);
        }

        function selectedLines() {
            return $('#cbolinemulti').val() || [];
        }

        // Product Group and the Line multiselect both apply to the whole order, so
        // either changing re-checks every currently selected line's history in one
        // go (instead of one popup per line), and reverts the product group on cancel.
        $('#cboproductgroup').on('change', function() {
            const selected = $(this).val();

            if (suppressProductGroupWarning) {
                previousProductGroup = selected;
                return;
            }

            if (!selected) {
                previousProductGroup = '';
                return;
            }

            const mismatchedLines = selectedLines().filter(line => !lineHasProductGroupHistory(line, selected));

            if (mismatchedLines.length) {
                const names = mismatchedLines.map(u => lineFullNameByUsername[u] || u).join(', ');
                Swal.fire({
                    icon: 'warning',
                    title: 'Product Group Belum Pernah Dikerjakan',
                    text: `Line berikut belum pernah mengerjakan product group : ${selected}. (${names}) Apakah anda yakin akan melanjutkan?`,
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Lanjutkan',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        previousProductGroup = selected;
                    } else {
                        suppressProductGroupWarning = true;
                        $('#cboproductgroup').val(previousProductGroup).trigger('change');
                        suppressProductGroupWarning = false;
                    }
                });
                return;
            }

            previousProductGroup = selected;
        });

        let suppressLineMultiWarning = false;
        let previousSelectedLines = [];

        $('#cbolinemulti').on('change', function() {
            syncQtyAllocationRows();

            if (suppressLineMultiWarning) {
                previousSelectedLines = selectedLines();
                return;
            }

            const productGroup = $('#cboproductgroup').val();
            const newlyAdded = selectedLines().filter(line => !previousSelectedLines.includes(line));
            const mismatchedLines = newlyAdded.filter(line => !lineHasProductGroupHistory(line, productGroup));

            if (mismatchedLines.length) {
                const names = mismatchedLines.map(u => lineFullNameByUsername[u] || u).join(', ');
                Swal.fire({
                    icon: 'warning',
                    title: 'Product Group Belum Pernah Dikerjakan',
                    text: `Line berikut belum pernah mengerjakan product group : ${productGroup}. (${names})`,
                    confirmButtonText: 'OK'
                });
            }

            previousSelectedLines = selectedLines();
        });

        function addRowRampUpRow($row, initialValue = null) {
            const container = $row.find('.qty-row-rampup-container');
            const dayNumber = container.find('.ramp-up-row').length + 1;
            const rampRow = $(`
                <div class="input-group input-group-sm mb-1 ramp-up-row">
                    <span class="input-group-text ramp-up-day-label">Hari ${dayNumber}</span>
                    <input type="number" class="form-control ramp-up-input" placeholder="Cnth: 50" min="0" max="100">
                    <span class="input-group-text">%</span>
                    <input type="text" class="form-control bg-light ramp-up-qty" readonly tabindex="-1"
                        placeholder="Qty/Hari">
                    <button type="button" class="btn btn-outline-danger" tabindex="-1">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `);
            rampRow.find('button').on('click', function() {
                rampRow.remove();
                renumberRowRampUpRows($row);
                recalcQtyRow($row);
            });
            rampRow.find('input').on('input change keyup', function() {
                recalcQtyRow($row);
            });
            container.append(rampRow);

            if (initialValue !== null) {
                rampRow.find('.ramp-up-input').val(initialValue);
            }

            recalcQtyRow($row);
        }

        function renumberRowRampUpRows($row) {
            $row.find('.qty-row-rampup-container .ramp-up-row').each(function(index) {
                $(this).find('.ramp-up-day-label').text('Hari ' + (index + 1));
            });
        }

        function getRowRampUpEfficiencies($row) {
            return $row.find('.qty-row-rampup-container .ramp-up-input').map(function() {
                return parseFloat($(this).val());
            }).get().filter(val => !isNaN(val));
        }

        function buildQtyAllocRow(line, rowId = null) {
            const name = lineFullNameByUsername[line] || line;
            const defaultStartDate = lineNextAvailableDate[line] || '<?= date('Y-m-d') ?>';
            const $row = $(`
                <div class="card mb-2 qty-alloc-row" data-line="${line}">
                    <div class="card-body py-2">
                        <input type="hidden" class="qty-row-id" value="${rowId ?? ''}">
                        <div class="row align-items-end">
                            <div class="col-md-3 fw-semibold">${$('<div>').text(name).html()}</div>
                            <div class="col-md-2">
                                <label class="small text-muted mb-1">Qty Order :</label>
                                <input type="text" class="form-control form-control-sm qty-row-qty" placeholder="Qty"
                                    inputmode="numeric" autocomplete="off">
                            </div>
                            <div class="col-md-2">
                                <label class="small text-muted mb-1">Qty/Hari :</label>
                                <input type="text" class="form-control form-control-sm bg-light qty-row-qtyperhari"
                                    readonly tabindex="-1" placeholder="Qty/Hari"
                                    title="Output per hari di efisiensi steady-state (setelah hari-hari ramp up).">
                            </div>
                            <div class="col-md-2">
                                <label class="small text-muted mb-1">Total Hari :</label>
                                <input type="text" class="form-control form-control-sm bg-light qty-row-totaldays"
                                    readonly tabindex="-1" placeholder="Total Hari">
                            </div>
                            <div class="col-md-3">
                                <label class="small text-muted mb-1">Tgl Finish :</label>
                                <input type="text" class="form-control form-control-sm bg-light qty-row-tglfinish"
                                    readonly tabindex="-1" placeholder="Tgl Finish"
                                    title="Hari libur sudah dilewati dari perhitungan. Tanggal pasti dihitung ulang saat disimpan.">
                            </div>
                        </div>
                        <div class="row align-items-center mt-2">
                            <div class="col-md-3">
                                <label class="small text-muted mb-1">Man Power :</label>
                                <input type="number" class="form-control form-control-sm qty-row-manpower"
                                    placeholder="Cnth: 10" autocomplete="off">
                            </div>
                            <div class="col-md-3">
                                <label class="small text-muted mb-1">Working Minutes :</label>
                                <input type="number" class="form-control form-control-sm qty-row-workingminutes"
                                    placeholder="Cnth: 480" autocomplete="off">
                            </div>
                            <div class="col-md-3">
                                <label class="small text-muted mb-1">Efficiency :</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" class="form-control qty-row-efficiency"
                                        min="0">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="small text-muted mb-1">Start Day Calendar :</label>
                                <input type="text" class="form-control form-control-sm qty-row-startdate"
                                    value="${defaultStartDate}" autocomplete="off"
                                    title="Default: hari kerja berikutnya setelah plan terakhir line ini selesai. Tanggal bertanda titik merah sudah ada plan di line ini.">
                            </div>
                        </div>
                        <div class="mt-2 mb-0">
                            <label class="small text-muted mb-1">Ramp Up Efficiency (opsional) :</label>
                            <div class="d-flex small text-muted mb-1">
                                <span class="text-center" style="min-width: 62px;">Hari</span>
                                <span class="flex-grow-1 text-center">Efisiensi</span>
                                <span style="width: 34px;"></span>
                                <span class="flex-grow-1 text-center">Qty/Hari</span>
                                <span style="width: 34px;"></span>
                            </div>
                            <div class="qty-row-rampup-container"></div>
                            <button type="button" class="btn btn-outline-secondary btn-sm btn-add-row-rampup">
                                <i class="fas fa-plus"></i> Tambah Hari
                            </button>
                        </div>
                    </div>
                </div>
            `);

            $row.find('.qty-row-qty').on('input', function() {
                this.value = this.value.replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                recalcQtyRow($row);
                updateQtyTotals();
            }).on('change', updateQtyTotals);

            $row.find('.qty-row-manpower, .qty-row-workingminutes, .qty-row-efficiency, .qty-row-startdate')
                .on('input change', function() {
                    recalcQtyRow($row);
                });

            $row.find('.btn-add-row-rampup').on('click', function() {
                addRowRampUpRow($row);
            });

            initRowStartDatePicker($row, line);

            return $row;
        }

        // Marks dates that already have a plan on this line with a dot + tooltip,
        // so the user can see it directly in the picker instead of cross-checking
        // the calendar grid separately.
        function initRowStartDatePicker($row, line) {
            const input = $row.find('.qty-row-startdate')[0];
            const occupied = occupiedDatesByLine[line] || {};

            flatpickr(input, {
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: 'd-m-Y',
                allowInput: true,
                defaultDate: input.value || null,
                onChange: function() {
                    recalcQtyRow($row);
                },
                onDayCreate: function(dObj, dStr, fp, dayElem) {
                    const dateKey = dayElem.dateObj.getFullYear() + '-' +
                        String(dayElem.dateObj.getMonth() + 1).padStart(2, '0') + '-' +
                        String(dayElem.dateObj.getDate()).padStart(2, '0');

                    const titleParts = [];

                    if (holidayDateSet.has(dateKey)) {
                        dayElem.classList.add('is-holiday');
                        titleParts.push('Hari libur');
                    }

                    const entries = occupied[dateKey];
                    if (entries && entries.length) {
                        dayElem.classList.add('has-line-plan');
                        titleParts.push(...entries
                            .map(e => `${e.style || 'Style'} (${e.buyer || '-'}): ${e.qty ?? '-'} pcs`));
                    }

                    if (titleParts.length) {
                        dayElem.title = titleParts.join('\n');
                    }
                },
            });
        }

        function syncQtyAllocationRows() {
            const lines = selectedLines();
            const $container = $('#qtyAllocationContainer');

            $container.find('.qty-alloc-row').each(function() {
                if (!lines.includes($(this).data('line'))) {
                    const startDateInput = $(this).find('.qty-row-startdate')[0];
                    if (startDateInput && startDateInput._flatpickr) {
                        startDateInput._flatpickr.destroy();
                    }
                    $(this).remove();
                }
            });

            lines.forEach(function(line) {
                if (!$container.find(`.qty-alloc-row[data-line="${CSS.escape(line)}"]`).length) {
                    $container.append(buildQtyAllocRow(line));
                }
            });

            lines.forEach(function(line) {
                $container.append($container.find(`.qty-alloc-row[data-line="${CSS.escape(line)}"]`));
            });

            $('#qtyAllocationEmpty').toggle(!lines.length);

            recalcAllQtyRows();
            updateQtyTotals();
        }

        function recalcQtyRow($row) {
            const manPower = parseFloat($row.find('.qty-row-manpower').val()) || 0;
            const workingMinutes = parseFloat($row.find('.qty-row-workingminutes').val()) || 0;
            const smv = parseFloat($('#txtsmv').val()) || 0;
            const efficiency = parseFloat($row.find('.qty-row-efficiency').val()) || 0;
            const qty = parseFloat(($row.find('.qty-row-qty').val() || '').replace(/\./g, '')) || 0;
            const rampUp = getRowRampUpEfficiencies($row);

            const minsAvailable = manPower * workingMinutes;
            const outputPerDay100 = smv > 0 ? minsAvailable / smv : 0;
            const steadyDailyQty = outputPerDay100 * (efficiency / 100);

            $row.find('.qty-row-qtyperhari').val(
                steadyDailyQty > 0 ? Math.round(steadyDailyQty).toLocaleString('id-ID') : ''
            );

            $row.find('.qty-row-rampup-container .ramp-up-row').each(function() {
                const $rampRow = $(this);
                const rampEff = parseFloat($rampRow.find('.ramp-up-input').val());
                const rampDailyQty = !isNaN(rampEff) ? outputPerDay100 * (rampEff / 100) : NaN;
                $rampRow.find('.ramp-up-qty').val(
                    !isNaN(rampDailyQty) && rampDailyQty > 0 ? Math.round(rampDailyQty).toLocaleString('id-ID') : ''
                );
            });

            let totalDays = 0;
            if (outputPerDay100 > 0 && qty > 0) {
                let produced = 0;
                const maxDays = 3650;
                while (produced < qty && totalDays < maxDays) {
                    const eff = totalDays < rampUp.length ? (rampUp[totalDays] / 100) : (efficiency / 100);
                    const dailyOutput = outputPerDay100 * eff;
                    if (dailyOutput <= 0) break;
                    produced += dailyOutput;
                    totalDays++;
                }
            }

            $row.find('.qty-row-totaldays').val(totalDays > 0 ? totalDays.toLocaleString('id-ID') + ' hari' : '');

            const startDateStr = $row.find('.qty-row-startdate').val();
            $row.find('.qty-row-tglfinish').val(
                totalDays > 0 && startDateStr ? addWorkingDaysID(startDateStr, totalDays) : ''
            );
        }

        function recalcAllQtyRows() {
            $('#qtyAllocationContainer .qty-alloc-row').each(function() {
                recalcQtyRow($(this));
            });
        }

        function updateQtyTotals() {
            const qtyTotal = parseInt(($('#txtorderqtytotal').val() || '0').replace(/\./g, ''), 10) || 0;
            const qtySum = $('#qtyAllocationContainer .qty-alloc-row').toArray().reduce((sum, el) => {
                const v = parseInt(($(el).find('.qty-row-qty').val() || '0').replace(/\./g, ''), 10) || 0;
                return sum + v;
            }, 0);
            const sisa = qtyTotal - qtySum;

            const isMatch = qtyTotal > 0 && sisa === 0;
            $('#qtyTotalSummary')
                .text(
                    `Total dialokasikan: ${qtySum.toLocaleString('id-ID')} / ${qtyTotal.toLocaleString('id-ID')} (sisa: ${sisa.toLocaleString('id-ID')})`
                )
                .toggleClass('text-success', isMatch)
                .toggleClass('text-danger', !isMatch);

            $('#btnSimpanLineMap').prop('disabled', !isMatch);
        }

        let lineMapDataTable = null;

        function initLineMapDataTable() {
            lineMapDataTable = $('#tblLineMapList').DataTable({
                ordering: false,
                paging: true,
                searching: true,
                responsive: true
            });
        }

        initLineMapDataTable();

        let draggedLineMap = null;

        // Safety net: while one of our plan boxes is being dragged, swallow
        // dragover/drop on anything that ISN'T one of our own calendar cells, so a
        // stray drop (e.g. on the "Dari Tanggal" filter input) never lets the
        // browser handle it natively (Chrome auto-fills date inputs from dropped
        // text). Scoped to outside-cell targets only, so it never overrides the
        // per-cell "can't drop in the middle of a plan" logic below.
        document.addEventListener('dragover', (event) => {
            if (draggedLineMap && !event.target.closest('.line-map-drop-target, .line-map-temp-dropzone')) {
                event.preventDefault();
                event.dataTransfer.dropEffect = 'none';
            }
        });
        document.addEventListener('drop', (event) => {
            if (draggedLineMap && !event.target.closest('.line-map-drop-target, .line-map-temp-dropzone')) {
                event.preventDefault();
            }
        });

        function bindPlanDragEvents() {
            document.querySelectorAll('.line-map-box-plan[draggable="true"]').forEach((badge) => {
                badge.addEventListener('dragstart', (event) => {
                    draggedLineMap = {
                        id: badge.dataset.id,
                        line: badge.dataset.line,
                        date: badge.dataset.date,
                        style: badge.dataset.style,
                        productGroup: badge.dataset.productGroup
                    };

                    event.dataTransfer.effectAllowed = 'move';
                    // Custom MIME type on purpose: 'text/plain' gets auto-consumed by
                    // native inputs (e.g. Chrome fills a stray <input type="date"> with
                    // the dragged id if the drop lands outside the calendar), causing
                    // the date filter above the table to jump to an unrelated date.
                    event.dataTransfer.setData('application/x-ppic-linemap-id', badge.dataset.id);
                });

                badge.addEventListener('dragend', () => {
                    draggedLineMap = null;
                    dragPointer = null;
                    lastPreviewKey = null;
                    lastPreviewMoves = null;
                    clearCascadeGhosts();
                    document.querySelectorAll('.line-map-drop-target.drag-over').forEach((cell) => {
                        cell.classList.remove('drag-over');
                    });
                });
            });
        }

        bindPlanDragEvents();

        const calendarWrapper = document.querySelector('.line-map-calendar-wrapper');
        let dragPointer = null;
        let autoScrollFrame = null;

        function stepAutoScroll() {
            if (!draggedLineMap || !dragPointer) {
                autoScrollFrame = null;
                return;
            }

            const edge = 60;
            const maxSpeed = 22;
            const rect = calendarWrapper.getBoundingClientRect();

            const topGap = dragPointer.y - rect.top;
            const bottomGap = rect.bottom - dragPointer.y;
            const leftGap = dragPointer.x - rect.left;
            const rightGap = rect.right - dragPointer.x;

            if (topGap < edge) {
                calendarWrapper.scrollTop -= maxSpeed * ((edge - topGap) / edge);
            } else if (bottomGap < edge) {
                calendarWrapper.scrollTop += maxSpeed * ((edge - bottomGap) / edge);
            }

            if (leftGap < edge) {
                calendarWrapper.scrollLeft -= maxSpeed * ((edge - leftGap) / edge);
            } else if (rightGap < edge) {
                calendarWrapper.scrollLeft += maxSpeed * ((edge - rightGap) / edge);
            }

            autoScrollFrame = requestAnimationFrame(stepAutoScroll);
        }

        calendarWrapper.addEventListener('dragover', (event) => {
            if (!draggedLineMap) return;
            dragPointer = {
                x: event.clientX,
                y: event.clientY
            };
            if (!autoScrollFrame) {
                autoScrollFrame = requestAnimationFrame(stepAutoScroll);
            }
        });

        let lastPreviewKey = null;
        let lastPreviewMoves = null;
        let previewRequestToken = 0;

        function clearCascadeGhosts() {
            document.querySelectorAll('.line-map-ghost-box').forEach((el) => el.remove());
            document.querySelectorAll('.line-map-ghost-target').forEach((el) => {
                el.classList.remove('line-map-ghost-target');
            });
        }

        function renderCascadeGhosts(targetLine, moves) {
            clearCascadeGhosts();

            (moves || []).forEach((move) => {
                if (!move.is_dragged && !move.shifted) return;

                (move.dates || []).forEach((date) => {
                    const cell = document.querySelector(
                        `.line-map-drop-target[data-line="${CSS.escape(targetLine)}"][data-date="${CSS.escape(date)}"]`
                    );
                    if (!cell) return;

                    cell.classList.add('line-map-ghost-target');

                    const box = document.createElement('div');
                    box.className = 'line-map-ghost-box ' +
                        (move.is_dragged ? 'line-map-ghost-box-drag' : 'line-map-ghost-box-push');
                    box.textContent = move.style || '-';
                    cell.appendChild(box);
                });
            });
        }

        function requestCascadePreview(targetLine, targetDate) {
            const key = targetLine + '|' + targetDate;
            if (key === lastPreviewKey || !draggedLineMap) return;
            lastPreviewKey = key;

            const token = ++previewRequestToken;

            fetch(@json(route('preview_move_ppic_line_map')), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                    },
                    body: JSON.stringify({
                        id: draggedLineMap.id,
                        target_line: targetLine,
                        target_date: targetDate
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (token !== previewRequestToken || !draggedLineMap || !data.success) return;
                    lastPreviewMoves = data.moves;
                    renderCascadeGhosts(targetLine, data.moves);
                })
                .catch(() => {});
        }

        function isMiddleOfPlan(cell) {
            if (!cell.classList.contains('line-map-plan-cell')) return false;
            if (cell.classList.contains('line-map-plan-start')) return false;
            if (draggedLineMap && cell.dataset.planId === String(draggedLineMap.id)) return false;
            return true;
        }

        function bindDropTargetEvents() {
            document.querySelectorAll('.line-map-drop-target').forEach((cell) => {
                cell.addEventListener('dragover', (event) => {
                    if (!draggedLineMap) return;

                    if (isMiddleOfPlan(cell)) {
                        event.dataTransfer.dropEffect = 'none';
                        cell.classList.remove('drag-over');
                        return;
                    }

                    event.preventDefault();
                    event.dataTransfer.dropEffect = 'move';
                    cell.classList.add('drag-over');

                    requestCascadePreview(cell.dataset.line, cell.dataset.date);
                });

                cell.addEventListener('dragleave', () => {
                    cell.classList.remove('drag-over');
                });

                cell.addEventListener('drop', (event) => {
                    event.preventDefault();
                    cell.classList.remove('drag-over');
                    clearCascadeGhosts();

                    if (!draggedLineMap) return;
                    if (isMiddleOfPlan(cell)) return;

                    const targetLine = cell.dataset.line;
                    const targetDate = cell.dataset.date;

                    if (draggedLineMap.line === targetLine && draggedLineMap.date === targetDate) return;

                    const key = targetLine + '|' + targetDate;
                    const moves = key === lastPreviewKey ? lastPreviewMoves : null;

                    confirmMoveLineMap(draggedLineMap, targetLine, targetDate, moves);
                });
            });
        }

        bindDropTargetEvents();

        function bindTempDropEvents() {
            document.querySelectorAll('.line-map-temp-dropzone').forEach((zone) => {
                zone.addEventListener('dragover', (event) => {
                    if (!draggedLineMap) return;
                    event.preventDefault();
                    event.dataTransfer.dropEffect = 'move';
                    zone.classList.add('drag-over');
                });

                zone.addEventListener('dragleave', () => {
                    zone.classList.remove('drag-over');
                });

                zone.addEventListener('drop', (event) => {
                    event.preventDefault();
                    zone.classList.remove('drag-over');
                    if (!draggedLineMap) return;

                    moveToTempLineMap(draggedLineMap);
                });
            });
        }

        bindTempDropEvents();

        function moveToTempLineMap(item) {
            Swal.fire({
                icon: 'question',
                title: 'Pindahkan ke Area Temporary?',
                text: `${item.style || 'Style ini'} akan dilepas dari line & tanggal saat ini dan disimpan sementara di area temporary.`,
                showCancelButton: true,
                confirmButtonText: 'Ya, Pindahkan',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (!result.isConfirmed) return;

                fetch(@json(route('move_to_temp_ppic_line_map')), {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                        },
                        body: JSON.stringify({
                            id: item.id
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            refreshLineMapData();
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: data.message,
                                timer: 1200,
                                showConfirmButton: false
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: data.message ?? 'Plan gagal dipindahkan ke area temporary',
                                confirmButtonText: 'Tutup'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Pindah ke Temporary error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: 'Terjadi kesalahan saat memindahkan plan ke area temporary',
                            confirmButtonText: 'Tutup'
                        });
                    });
            });
        }

        function updateUndoButton() {
            const $btn = $('#btnUndoLineMap');
            if (!$btn.length) return;

            $btn.prop('disabled', undoCount <= 0);
            $('#undoLineMapCount').text(undoCount > 0 ? `(${undoCount})` : '');
        }

        updateUndoButton();

        function undoLineMap() {
            if (undoCount <= 0) return;

            Swal.fire({
                icon: 'question',
                title: 'Undo Perpindahan Terakhir?',
                text: 'Perpindahan jadwal (termasuk yang ikut tergeser) akan dikembalikan seperti sebelumnya.',
                showCancelButton: true,
                confirmButtonText: 'Ya, Undo',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (!result.isConfirmed) return;

                fetch(@json(route('undo_ppic_line_map')), {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            refreshLineMapData();
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: data.message,
                                timer: 1200,
                                showConfirmButton: false
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: data.message ?? 'Undo gagal dilakukan',
                                confirmButtonText: 'Tutup'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Undo Line Map error:', error);
                        refreshLineMapData();
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: 'Terjadi kesalahan saat melakukan undo. Data di layar sudah disegarkan, mohon dicek.',
                            confirmButtonText: 'Tutup'
                        });
                    });
            });
        }

        function refreshLineMapData() {
            const scrollLeft = calendarWrapper.scrollLeft;
            const scrollTop = calendarWrapper.scrollTop;

            const params = new URLSearchParams();
            const dari = $('input[name="tgl_dari"]').val();
            const sampai = $('input[name="tgl_sampai"]').val();
            if (dari) params.set('tgl_dari', dari);
            if (sampai) params.set('tgl_sampai', sampai);

            return fetch(@json(route('ppic_line_map_refresh')) + '?' + params.toString(), {
                    cache: 'no-store',
                    headers: {
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    if (!response.ok) throw new Error('HTTP ' + response.status);
                    return response.json();
                })
                .then(data => {
                    if (!data.success) throw new Error(data.message || 'Refresh gagal');

                    document.getElementById('lineMapCalendarWrapper').innerHTML = data.calendar;
                    document.getElementById('lineMapTempWrapper').innerHTML = data.tempHolding;
                    calendarWrapper.scrollLeft = scrollLeft;
                    calendarWrapper.scrollTop = scrollTop;
                    bindPlanDragEvents();
                    bindDropTargetEvents();
                    bindTempDropEvents();

                    if (lineMapDataTable) {
                        lineMapDataTable.destroy();
                    }
                    document.getElementById('lineMapListBody').innerHTML = data.listRows;
                    initLineMapDataTable();

                    document.getElementById('lineMapLastUpdated').textContent = 'Last Update: ' + data.lastUpdated;
                    productGroupByLine = data.productGroupByLine || {};
                    lineMapColorGroups = data.colorGroups || [];
                    lineNextAvailableDate = data.lineNextAvailableDate || {};
                    occupiedDatesByLine = data.occupiedDatesByLine || {};
                    renderColorModal();

                    undoCount = data.undoCount ?? undoCount;
                    updateUndoButton();
                })
                .catch(error => {
                    // Ajax refresh failed for some reason (network hiccup, session expired,
                    // etc). Falling back to a full reload guarantees the calendar/list
                    // still end up in sync instead of silently going stale.
                    console.error('Refresh Line Map error:', error);
                    location.reload();
                });
        }

        function renderColorModal() {
            const $body = $('#lineMapColorRows');
            if (!$body.length) return;

            $body.empty();

            if (!lineMapColorGroups.length) {
                $body.append(
                    '<tr><td colspan="6" class="text-center text-muted">Tidak ada order pada rentang tanggal ini</td></tr>'
                );
                return;
            }

            lineMapColorGroups.forEach(function(group) {
                const $row = $(`
                    <tr>
                        <td class="text-center">
                            <input type="color" class="form-control form-control-color form-control-sm line-map-color-bg"
                                value="${group.color}" title="Warna Latar">
                        </td>
                        <td class="text-center">
                            <input type="color" class="form-control form-control-color form-control-sm line-map-color-font"
                                value="${group.font_color}" title="Warna Font">
                        </td>
                        <td>${$('<div>').text(group.style || '-').html()}</td>
                        <td>${$('<div>').text(group.buyer || '-').html()}</td>
                        <td>${$('<div>').text((group.lines || []).join(', ')).html()}</td>
                        <td>${formatDateID(group.tgl_start)} - ${formatDateID(group.tgl_end)}</td>
                    </tr>
                `);

                $row.find('input[type="color"]').on('change', function() {
                    saveLineMapColor(
                        group.row_id,
                        $row.find('.line-map-color-bg').val(),
                        $row.find('.line-map-color-font').val()
                    );
                });

                $body.append($row);
            });
        }

        function saveLineMapColor(rowId, color, fontColor) {
            fetch(@json(route('set_ppic_line_map_color')), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                    },
                    body: JSON.stringify({
                        row_id: rowId,
                        color: color,
                        font_color: fontColor
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        refreshLineMapData();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: data.message ?? 'Warna gagal disimpan',
                            confirmButtonText: 'Tutup'
                        });
                    }
                })
                .catch(error => {
                    console.error('Simpan Warna Line Map error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Terjadi kesalahan saat menyimpan warna',
                        confirmButtonText: 'Tutup'
                    });
                });
        }

        // Formats a Date as 'YYYY-MM-DD' in local time. Deliberately avoids
        // Date#toISOString() here: it converts to UTC, which silently shifts the
        // date by a day in timezones ahead of UTC (e.g. WIB, UTC+7) — a real bug
        // we hit computing Tgl Finish.
        function dateKeyLocal(d) {
            const y = d.getFullYear();
            const m = String(d.getMonth() + 1).padStart(2, '0');
            const day = String(d.getDate()).padStart(2, '0');
            return `${y}-${m}-${day}`;
        }

        // Mirrors the backend's workingDatesFrom(): day 1 is the start date itself
        // (kept even if it lands on a holiday, e.g. planned overtime), then every
        // day after that skips dates in holidayDateSet until `totalDays' working
        // days have been counted.
        function addWorkingDaysID(dateStr, totalDays) {
            const d = new Date(dateStr + 'T00:00:00');
            let counted = 1;
            let guard = 0;
            while (counted < totalDays && guard < 3650) {
                d.setDate(d.getDate() + 1);
                if (!holidayDateSet.has(dateKeyLocal(d))) {
                    counted++;
                }
                guard++;
            }
            return d.toLocaleDateString('id-ID', {
                day: '2-digit',
                month: 'short',
                year: 'numeric'
            });
        }

        function formatDateID(dateStr) {
            if (!dateStr) return '-';
            const d = new Date(dateStr + 'T00:00:00');
            return d.toLocaleDateString('id-ID', {
                day: '2-digit',
                month: 'short',
                year: 'numeric'
            });
        }

        function buildCascadeSummaryHtml(moves) {
            const shiftedOthers = (moves || []).filter(m => !m.is_dragged && m.shifted);
            if (!shiftedOthers.length) return '';

            const rows = shiftedOthers.map(m =>
                `<div>&bull; ${$('<div>').text(m.style || '-').html()} akan digeser ke ${formatDateID(m.new_start)}</div>`
            ).join('');

            return `<div class="text-start mt-2"><strong>${shiftedOthers.length} jadwal lain ikut digeser:</strong>${rows}</div>`;
        }

        function confirmMoveLineMap(item, targetLine, targetDate, moves) {
            const productGroup = item.productGroup;
            const groups = productGroupByLine[targetLine] || [];
            const hasHistory = !productGroup || groups.some(g => g.product_group === productGroup);

            if (!hasHistory) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Product Group Belum Pernah Dikerjakan',
                    text: `Line tujuan belum pernah mengerjakan product group : ${productGroup}. Apakah anda yakin akan melanjutkan?`,
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Lanjutkan',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        moveLineMap(item, targetLine, targetDate, moves);
                    }
                });
                return;
            }

            moveLineMap(item, targetLine, targetDate, moves);
        }

        function showWsBreakdown(styleno, wsBreakdown) {
            const rows = (wsBreakdown || []).map(row => `
                <tr>
                    <td class="text-left">${row.ws || '-'}</td>
                    <td class="text-right">${Number(row.tot_rfts || 0).toLocaleString('id-ID')}</td>
                </tr>
            `).join('');

            const total = (wsBreakdown || []).reduce((sum, row) => sum + Number(row.tot_rfts || 0), 0);

            Swal.fire({
                icon: 'info',
                title: styleno || '-',
                html: `
                    <table class="table table-sm table-bordered mb-0">
                        <thead>
                            <tr>
                                <th class="text-left">WS</th>
                                <th class="text-right">Qty</th>
                            </tr>
                        </thead>
                        <tbody>${rows || '<tr><td colspan="2" class="text-center text-muted">Tidak ada data</td></tr>'}</tbody>
                        <tfoot>
                            <tr>
                                <th class="text-left">Total</th>
                                <th class="text-right">${total.toLocaleString('id-ID')}</th>
                            </tr>
                        </tfoot>
                    </table>
                `,
                confirmButtonText: 'Tutup'
            });
        }

        function moveLineMap(item, targetLine, targetDate, moves) {
            Swal.fire({
                icon: 'question',
                title: 'Pindahkan Jadwal?',
                html: `${item.style || 'Style ini'} akan dipindahkan ke line dan tanggal yang dipilih.` +
                    buildCascadeSummaryHtml(moves),
                showCancelButton: true,
                confirmButtonText: 'Ya, Pindahkan',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (!result.isConfirmed) return;

                fetch(@json(route('move_ppic_line_map')), {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                        },
                        body: JSON.stringify({
                            id: item.id,
                            target_line: targetLine,
                            target_date: targetDate
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            refreshLineMapData();
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: data.message,
                                timer: 1200,
                                showConfirmButton: false
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: data.message ?? 'Jadwal gagal dipindahkan',
                                confirmButtonText: 'Tutup'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Pindah Line Map error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: 'Terjadi kesalahan saat memindahkan jadwal',
                            confirmButtonText: 'Tutup'
                        });
                    });
            });
        }

        function toggleCustomColor() {
            const enabled = $('#chkCustomColor').is(':checked');
            $('#txtcolor, #txtfontcolor').prop('disabled', !enabled);
        }

        function openNewLineMap() {
            $('#formLineMap').trigger('reset');
            $('#groupid').val('');
            $('#lineMapModalTitle').text('Tambah Line Map');
            $('#cboproductgroup').val('').trigger('change');
            suppressLineMultiWarning = true;
            $('#cbolinemulti').val([]).trigger('change');
            suppressLineMultiWarning = false;
            previousSelectedLines = [];
            $('#chkCustomColor').prop('checked', false);
            $('#txtcolor').val('#6f42c1');
            $('#txtfontcolor').val('#ffffff');
            toggleCustomColor();
        }

        function openEditLineMap(data) {
            $('#formLineMap').trigger('reset');
            $('#groupid').val(data.group_id || '');
            $('#lineMapModalTitle').text('Edit Line Map');

            $('#txtstyle').val(data.style);
            $('#txtbuyer').val(data.buyer);
            $('#txtsmv').val(data.smv);
            $('#txtorderqtytotal').val(data.qty_order_total !== null && data.qty_order_total !== undefined ?
                Number(data.qty_order_total).toLocaleString('id-ID').replace(/,/g, '.') : '');

            $('#chkCustomColor').prop('checked', !!data.has_custom_color);
            $('#txtcolor').val(data.color || '#6f42c1');
            $('#txtfontcolor').val(data.font_color || '#ffffff');
            toggleCustomColor();

            suppressProductGroupWarning = true;
            $('#cboproductgroup').val(data.product_group || '').trigger('change');
            suppressProductGroupWarning = false;
            previousProductGroup = data.product_group || '';

            const lines = data.lines || [];
            suppressLineMultiWarning = true;
            $('#cbolinemulti').val(lines.map(l => l.line)).trigger('change');
            suppressLineMultiWarning = false;
            previousSelectedLines = selectedLines();

            const $container = $('#qtyAllocationContainer');
            lines.forEach(function(line) {
                const $row = $container.find(`.qty-alloc-row[data-line="${CSS.escape(line.line)}"]`);
                $row.find('.qty-row-id').val(line.id);
                $row.find('.qty-row-qty').val(line.qty_order !== null ?
                    Number(line.qty_order).toLocaleString('id-ID').replace(/,/g, '.') : '');
                $row.find('.qty-row-manpower').val(line.man_power);
                $row.find('.qty-row-workingminutes').val(line.working_min);
                $row.find('.qty-row-efficiency').val(line.efficiency !== null ? line.efficiency : 100);
                const startDateInput = $row.find('.qty-row-startdate')[0];
                if (startDateInput._flatpickr) {
                    startDateInput._flatpickr.setDate(line.tgl_start, true);
                } else {
                    $(startDateInput).val(line.tgl_start);
                }

                // The row may be a reused DOM node from a previous open of this (or
                // another) modal — syncQtyAllocationRows only rebuilds rows for lines
                // that just got (de)selected, so a still-selected line's ramp-up rows
                // survive across opens and would otherwise pile up on every reopen.
                $row.find('.qty-row-rampup-container').empty();
                (line.ramp_up_efficiency || []).forEach(function(eff) {
                    addRowRampUpRow($row, Math.round(eff * 100));
                });
            });

            recalcAllQtyRows();
            updateQtyTotals();
        }

        function cancelLineMap(id) {
            Swal.fire({
                icon: 'warning',
                title: 'Hapus Data?',
                text: 'Data ini tidak akan tampil lagi di tabel maupun kalender.',
                showCancelButton: true,
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (!result.isConfirmed) return;

                const url = @json(route('cancel_ppic_line_map', ':id')).replace(':id', id);

                fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            refreshLineMapData();
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: data.message,
                                timer: 1500,
                                showConfirmButton: false
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: data.message ?? 'Data gagal dihapus',
                                confirmButtonText: 'Tutup'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Hapus Line Map error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: 'Terjadi kesalahan saat menghapus data',
                            confirmButtonText: 'Tutup'
                        });
                    });
            });
        }

        function submitLineMapForm(form, event) {
            event.preventDefault();

            const errors = [];
            const style = ($('#txtstyle').val() || '').trim();
            const buyer = ($('#txtbuyer').val() || '').trim();
            const smv = $('#txtsmv').val();
            const qtyTotalRaw = $('#txtorderqtytotal').val();

            if (!style) errors.push('Style');
            if (!buyer) errors.push('Buyer');
            if (!smv) errors.push('SMV');
            if (!qtyTotalRaw) errors.push('Order Qty Total');

            const $rows = $('#qtyAllocationContainer .qty-alloc-row');
            if (!$rows.length) errors.push('Minimal pilih 1 Line');

            const incompleteLines = [];
            $rows.each(function() {
                const $r = $(this);
                const lineName = lineFullNameByUsername[$r.data('line')] || $r.data('line');
                const missing = [];
                if (!$r.find('.qty-row-qty').val()) missing.push('Qty');
                if (!$r.find('.qty-row-manpower').val()) missing.push('Man Power');
                if (!$r.find('.qty-row-workingminutes').val()) missing.push('Working Minutes');
                if ($r.find('.qty-row-efficiency').val() === '') missing.push('Efficiency');
                if (!$r.find('.qty-row-startdate').val()) missing.push('Start Day Calendar');

                if (missing.length) {
                    incompleteLines.push(`${lineName} (${missing.join(', ')})`);
                }
            });

            if (incompleteLines.length) {
                errors.push('Data belum lengkap untuk: ' + incompleteLines.join(', '));
            }

            const qtyTotal = parseInt((qtyTotalRaw || '0').replace(/\./g, ''), 10) || 0;
            const qtySum = $rows.toArray().reduce((sum, el) => {
                const v = parseInt(($(el).find('.qty-row-qty').val() || '0').replace(/\./g, ''), 10) || 0;
                return sum + v;
            }, 0);

            if (qtyTotal > 0 && qtySum !== qtyTotal) {
                errors.push(
                    `Total Qty per Line (${qtySum.toLocaleString('id-ID')}) harus sama dengan Order Qty Total (${qtyTotal.toLocaleString('id-ID')}), sisa ${(qtyTotal - qtySum).toLocaleString('id-ID')}`
                );
            }

            if (errors.length) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Data Belum Lengkap / Tidak Sesuai',
                    html: errors.map(e => `&bull; ${e}`).join('<br>'),
                    confirmButtonText: 'Tutup'
                });
                return;
            }

            const formData = new FormData();
            formData.append('_token', document.querySelector('input[name="_token"]').value);
            formData.append('group_id', $('#groupid').val());
            formData.append('txtstyle', style);
            formData.append('txtbuyer', buyer);
            formData.append('cboproductgroup', $('#cboproductgroup').val());
            formData.append('txtsmv', smv);
            formData.append('txtorderqtytotal', qtyTotal);

            if ($('#chkCustomColor').is(':checked')) {
                formData.append('txtcolor', $('#txtcolor').val());
                formData.append('txtfontcolor', $('#txtfontcolor').val());
            }

            $rows.each(function(i) {
                const $r = $(this);
                formData.append('line_row_id[]', $r.find('.qty-row-id').val() || '');
                formData.append('cboline[]', $r.data('line'));
                formData.append('txtorderqtyline[]', ($r.find('.qty-row-qty').val() || '').replace(/\./g, ''));
                formData.append('txtmanpower[]', $r.find('.qty-row-manpower').val() || '');
                formData.append('txtworkingminutes[]', $r.find('.qty-row-workingminutes').val() || '');
                formData.append('txtefficiency[]', $r.find('.qty-row-efficiency').val() || '');
                formData.append('cbodate[]', $r.find('.qty-row-startdate').val() || '');

                $r.find('.qty-row-rampup-container .ramp-up-input').each(function() {
                    formData.append(`ramp_efficiency[${i}][]`, $(this).val());
                });
            });

            fetch(form.getAttribute('action'), {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        $('#newLineMapModal').modal('hide');
                        refreshLineMapData();

                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: data.message,
                            timer: 1500,
                            showConfirmButton: false
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: data.message ?? 'Data gagal disimpan',
                            confirmButtonText: 'Tutup'
                        });
                    }
                })
                .catch(error => {
                    console.error('Simpan Line Map error:', error);
                    // A network hiccup here can mean the request never reached the
                    // server, but it can just as easily mean the server finished
                    // the save and the response just didn't make it back — so
                    // refresh regardless, and let the user see what actually landed
                    // instead of leaving stale data on screen next to a scary error.
                    refreshLineMapData();
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Terjadi kesalahan saat menyimpan data. Data di layar sudah disegarkan, mohon dicek apakah datanya sudah tersimpan.',
                        confirmButtonText: 'Tutup'
                    });
                });
        }
    </script>
@endsection
