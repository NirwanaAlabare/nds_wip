@extends('layouts.index')

@section('custom-link')
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <style>
        .line-map-calendar-wrapper {
            overflow: auto;
            max-height: 65vh;
            max-width: 100%;
            border-top: 1px solid #dee2e6;
            border-left: 1px solid #dee2e6;
        }

        .line-map-calendar-inner {
            display: flex;
            align-items: flex-start;
            width: max-content;
        }

        .line-map-fixed-table,
        .line-map-dates-table {
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 0 !important;
            width: auto !important;
        }

        .line-map-fixed-table th,
        .line-map-fixed-table td,
        .line-map-dates-table th,
        .line-map-dates-table td {
            white-space: nowrap;
            vertical-align: middle;
            height: auto;
            border-right: 1px solid #dee2e6;
            border-bottom: 1px solid #dee2e6;
        }

        .line-map-dates-table td {
            vertical-align: top;
        }

        .line-map-fixed-table {
            width: auto !important;
            min-width: 160px;
            flex: 0 0 auto;
            position: sticky;
            left: 0;
            z-index: 3;
            background-color: #fff;
            box-shadow: 2px 0 4px -2px rgba(0, 0, 0, .15);
        }

        .line-map-fixed-table thead th,
        .line-map-dates-table thead th {
            position: sticky;
            top: 0;
            z-index: 2;
            background-color: #fff;
        }

        .line-map-fixed-table thead th {
            z-index: 4;
        }

        .line-map-dates-table th {
            text-align: center;
            width: 1%;
        }

        .line-map-dates-table td {
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
            color: #fff;
            cursor: grab;
            user-select: none;
            box-shadow: 0 1px 2px rgba(0, 0, 0, .15);
        }

        .line-map-box-plan:active {
            cursor: grabbing;
        }

        .line-map-box-plan .row-qty {
            color: #fff;
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

        .line-map-drop-target.drag-over {
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
            <input type="hidden" id="editid" name="editid" value="">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-sb text-light">
                        <h3 class="modal-title fs-5" id="lineMapModalTitle">Tambah Line Map</h3>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Line :</label>
                                    <select class="form-control select2bs4 form-control-sm" id="cboline" name="cboline">
                                        <option value="">- Pilih Line -</option>
                                        @foreach ($line as $row)
                                            <option value="{{ $row->username }}">{{ $row->FullName }}
                                                ({{ $row->username }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Style :</label>
                                    <input type="text" class="form-control form-control-sm" id="txtstyle"
                                        name="txtstyle" placeholder="Cnth: POLO ZIP SIDE SLIT" value=""
                                        autocomplete="off" style="text-transform: uppercase;"
                                        oninput="this.value = this.value.toUpperCase();">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">SMV :</label>
                                    <input type="number" class="form-control form-control-sm" id="txtsmv" name="txtsmv"
                                        placeholder="Cnth: 12.5" value="" autocomplete="off">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Efficiency :</label>
                                    <div class="input-group input-group-sm">
                                        <input type="number" class="form-control form-control-sm" id="txtefficiency"
                                            name="txtefficiency" placeholder="Cnth: 85" value="" autocomplete="off">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Order Qty :</label>
                                    <input type="text" class="form-control form-control-sm" id="txtorderqty"
                                        name="txtorderqty" placeholder="Cnth: 1.000" value="" autocomplete="off"
                                        inputmode="numeric"
                                        oninput="this.value = this.value.replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.');">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Buyer :</label>
                                    <input type="text" class="form-control form-control-sm" id="txtbuyer"
                                        name="txtbuyer" value="" autocomplete="off" style="text-transform: uppercase;"
                                        oninput="this.value = this.value.toUpperCase();">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Man Power :</label>
                                    <input type="number" class="form-control form-control-sm" id="txtmanpower"
                                        name="txtmanpower" placeholder="Cnth: 10" value="" autocomplete="off">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Working Minutes :</label>
                                    <input type="number" class="form-control form-control-sm" id="txtworkingminutes"
                                        name="txtworkingminutes" placeholder="Cnth: 480" value=""
                                        autocomplete="off">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Start Day Calendar :</label>
                                    <input type="date" class="form-control form-control-sm" id="cbodate"
                                        name="cbodate" value="<?= date('Y-m-d') ?>">
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label">Mins Available :</label>
                                    <input type="text" class="form-control form-control-sm bg-light"
                                        id="txtminsavailable" readonly tabindex="-1">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label">Output / Day 100% :</label>
                                    <input type="text" class="form-control form-control-sm bg-light"
                                        id="txtoutputperday100" readonly tabindex="-1">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label">Output / Day based Eff :</label>
                                    <input type="text" class="form-control form-control-sm bg-light"
                                        id="txtoutputperdayefficiency" readonly tabindex="-1">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label">Total Days :</label>
                                    <input type="text" class="form-control form-control-sm bg-light" id="txttotaldays"
                                        readonly tabindex="-1">
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="form-group">
                            <label class="form-label">Ramp Up Efficiency (opsional) :</label>
                            <div id="rampUpContainer"></div>
                            <button type="button" class="btn btn-outline-secondary btn-sm mt-1"
                                onclick="addRampUpRow()">
                                <i class="fas fa-plus"></i> Tambah Hari
                            </button>
                            <small class="form-text text-muted d-block mt-1">
                                Efisiensi bertahap untuk hari-hari awal (mis. operator masih adaptasi style
                                baru). Kosongkan jika tidak perlu, hari setelahnya otomatis pakai Efficiency
                                normal di atas.
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal"><i
                                class="fas fa-times-circle"></i> Tutup</button>
                        <button type="submit" class="btn btn-outline-success"><i class="fas fa-check"></i>
                            Simpan</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-map-marker-alt"></i> PPIC Line Map</h5>
        </div>
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-end flex-wrap gap-2 mb-3">
                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal"
                    data-bs-target="#newLineMapModal" onclick="openNewLineMap()">
                    <i class="fas fa-plus"></i> New
                </button>

                <form action="{{ route('ppic_line_map') }}" method="get" class="d-flex align-items-end gap-2">
                    <div class="form-group mb-0">
                        <label class="form-label mb-0">Dari Tanggal :</label>
                        <input type="date" class="form-control form-control-sm" name="tgl_dari"
                            value="{{ $filterStart }}">
                    </div>
                    <div class="form-group mb-0">
                        <label class="form-label mb-0">Sampai Tanggal :</label>
                        <input type="date" class="form-control form-control-sm" name="tgl_sampai"
                            value="{{ $filterEnd }}">
                    </div>
                    <button type="submit" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <a href="{{ route('ppic_line_map') }}" class="btn btn-outline-danger btn-sm">
                        <i class="fas fa-times"></i> Reset
                    </a>
                </form>
            </div>

            <div class="line-map-calendar-wrapper">
                <div class="line-map-calendar-inner">
                    <table class="table table-sm line-map-fixed-table">
                        <thead>
                            <tr>
                                <th>Line</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($line as $ln)
                                <tr>
                                    <td>
                                        <div class="fw-bold">{{ $ln->FullName ?? $ln->username }}</div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="text-muted">Belum ada data</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <table class="table table-sm line-map-dates-table">
                        <thead>
                            <tr>
                                @foreach ($calendarDates as $date)
                                    <th>{{ date('d M', strtotime($date->tanggal)) }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($line as $ln)
                                <tr>
                                    @foreach ($calendarDates as $date)
                                        @php
                                            $activeEntry = ($lineMapByLine[$ln->username] ?? collect())->first(
                                                fn($e) => $date->tanggal >= $e->tgl_start &&
                                                    $date->tanggal <= $e->tgl_end,
                                            );
                                            $planQty = $activeEntry->daily_plan[$date->tanggal] ?? null;
                                            $effPct = $activeEntry->daily_efficiency[$date->tanggal] ?? null;
                                            $actualEntries =
                                                $actualByLineDate[$ln->username][$date->tanggal] ?? collect();
                                            $hasPlan = $activeEntry && $planQty !== null;
                                            $isPlanStart = $hasPlan && $date->tanggal === $activeEntry->tgl_start;
                                            $isPlanEnd = $hasPlan && $date->tanggal === $activeEntry->tgl_end;
                                            $planCellClasses = collect([
                                                'line-map-drop-target',
                                                $hasPlan ? 'line-map-plan-cell' : null,
                                                $isPlanStart ? 'line-map-plan-start' : null,
                                                $isPlanEnd ? 'line-map-plan-end' : null,
                                            ])
                                                ->filter()
                                                ->implode(' ');
                                            $planTitle = $hasPlan
                                                ? 'Range: ' .
                                                    date('d M Y', strtotime($activeEntry->tgl_start)) .
                                                    ' - ' .
                                                    date('d M Y', strtotime($activeEntry->tgl_end)) .
                                                    ($effPct !== null
                                                        ? ' | Efisiensi: ' .
                                                            rtrim(rtrim(number_format($effPct, 1), '0'), '.') .
                                                            '%'
                                                        : '')
                                                : null;
                                        @endphp
                                        <td class="{{ $planCellClasses }}" data-line="{{ $ln->username }}"
                                            data-date="{{ $date->tanggal }}"
                                            @if ($hasPlan) style="--plan-line-color: {{ $activeEntry->style_color }};" @endif>
                                            @if ($hasPlan || $actualEntries->isNotEmpty())
                                                @php
                                                    $planColor = $activeEntry->style_color ?? '#6f42c1';
                                                @endphp
                                                <div class="line-map-cell-stack">
                                                    @if ($hasPlan)
                                                        <div class="line-map-box line-map-box-plan" draggable="true"
                                                            style="--dot-color: {{ $planColor }};"
                                                            data-id="{{ $activeEntry->id }}"
                                                            data-line="{{ $activeEntry->line }}"
                                                            data-date="{{ $date->tanggal }}"
                                                            data-style="{{ $activeEntry->style }}"
                                                            title="{{ $planTitle }}">
                                                            <div class="line-map-box-header">
                                                                <span
                                                                    class="box-buyer">{{ $activeEntry->buyer ?: '-' }}</span>
                                                                <span>Plan</span>
                                                            </div>
                                                            <div class="line-map-box-row">
                                                                <span class="row-label">{{ $activeEntry->style }}</span>
                                                                <span
                                                                    class="row-qty">{{ number_format($planQty, 0, ',', '.') }}</span>
                                                            </div>
                                                        </div>
                                                    @endif
                                                    @if ($actualEntries->isNotEmpty())
                                                        <div class="line-map-box line-map-box-actual">
                                                            <div class="line-map-box-header">
                                                                <span>Aktual</span>
                                                            </div>
                                                            @foreach ($actualEntries as $actual)
                                                                <div class="line-map-box-row">
                                                                    <span
                                                                        class="row-label">{{ $actual->style ?: '-' }}</span>
                                                                    <span
                                                                        class="row-qty">{{ number_format($actual->tot_rfts, 0, ',', '.') }}</span>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </div>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @empty
                                <tr>
                                    @foreach ($calendarDates as $date)
                                        <td></td>
                                    @endforeach
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
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
                            <th>Style</th>
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
                    <tbody>
                        @forelse ($lineMap as $row)
                            <tr>
                                <td>{{ $lineNameByUsername[$row->line] ?? $row->line }}</td>
                                <td>{{ $row->tgl_start ? date('d-m-Y', strtotime($row->tgl_start)) : '-' }}</td>
                                <td>{{ $row->style }}</td>
                                <td>{{ $row->buyer }}</td>
                                <td>{{ $row->smv }}</td>
                                <td>{{ $row->efficiency !== null ? number_format($row->efficiency * 100, 0) . '%' : '-' }}
                                </td>
                                <td>{{ $row->qty_order !== null ? number_format($row->qty_order, 0, ',', '.') : '-' }}</td>
                                <td>{{ $row->tot_days_rounded }} hari</td>
                                <td>
                                    @if (count($row->ramp_up_efficiency))
                                        {{ count($row->ramp_up_efficiency) }} hari
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $row->created_by }}</td>
                                <td>{{ $row->updated_at ? date('d-m-Y H:i:s', strtotime($row->updated_at)) : '-' }}</td>
                                <td class="text-nowrap">
                                    <button type="button" class="btn btn-outline-warning btn-sm" data-bs-toggle="modal"
                                        data-bs-target="#newLineMapModal"
                                        onclick='openEditLineMap(@json($row->edit_payload))'>
                                        <i class="fas fa-pen"></i> Edit
                                    </button>
                                    <button type="button" class="btn btn-outline-danger btn-sm"
                                        onclick="cancelLineMap({{ $row->id }})">
                                        <i class="fas fa-trash"></i> Hapus
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="text-center text-muted">Belum ada data</td>
                            </tr>
                        @endforelse
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
    <script>
        $(document).on('select2:open', () => {
            document.querySelector('.select2-search__field').focus();
        });

        $('.select2bs4').select2({
            theme: 'bootstrap4',
            containerCssClass: 'form-control-sm rounded',
            dropdownParent: $('#newLineMapModal')
        });

        $('#tblLineMapList').DataTable({
            ordering: false,
            paging: true,
            searching: true,
            responsive: true
        });

        function syncLineMapRowHeights() {
            const fixedRows = document.querySelectorAll('.line-map-fixed-table tbody tr');
            const dateRows = document.querySelectorAll('.line-map-dates-table tbody tr');

            fixedRows.forEach((row, i) => {
                const dateRow = dateRows[i];
                if (!dateRow) return;
                row.style.height = '';
                dateRow.style.height = '';
            });

            fixedRows.forEach((row, i) => {
                const dateRow = dateRows[i];
                if (!dateRow) return;
                const maxHeight = Math.max(row.offsetHeight, dateRow.offsetHeight);
                row.style.height = maxHeight + 'px';
                dateRow.style.height = maxHeight + 'px';
            });
        }

        $(window).on('load', syncLineMapRowHeights);

        let draggedLineMap = null;

        document.querySelectorAll('.line-map-box-plan[draggable="true"]').forEach((badge) => {
            badge.addEventListener('dragstart', (event) => {
                draggedLineMap = {
                    id: badge.dataset.id,
                    line: badge.dataset.line,
                    date: badge.dataset.date,
                    style: badge.dataset.style
                };

                event.dataTransfer.effectAllowed = 'move';
                event.dataTransfer.setData('text/plain', badge.dataset.id);
            });

            badge.addEventListener('dragend', () => {
                draggedLineMap = null;
                document.querySelectorAll('.line-map-drop-target.drag-over').forEach((cell) => {
                    cell.classList.remove('drag-over');
                });
            });
        });

        document.querySelectorAll('.line-map-drop-target').forEach((cell) => {
            cell.addEventListener('dragover', (event) => {
                if (!draggedLineMap) return;
                event.preventDefault();
                event.dataTransfer.dropEffect = 'move';
                cell.classList.add('drag-over');
            });

            cell.addEventListener('dragleave', () => {
                cell.classList.remove('drag-over');
            });

            cell.addEventListener('drop', (event) => {
                event.preventDefault();
                cell.classList.remove('drag-over');

                if (!draggedLineMap) return;

                const targetLine = cell.dataset.line;
                const targetDate = cell.dataset.date;

                if (draggedLineMap.line === targetLine && draggedLineMap.date === targetDate) return;

                moveLineMap(draggedLineMap, targetLine, targetDate);
            });
        });

        function moveLineMap(item, targetLine, targetDate) {
            Swal.fire({
                icon: 'question',
                title: 'Pindahkan Jadwal?',
                text: `${item.style || 'Style ini'} akan dipindahkan ke line dan tanggal yang dipilih.`,
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
                            target_date: targetDate,
                            source_date: item.date
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: data.message,
                                timer: 1200,
                                showConfirmButton: false
                            }).then(() => location.reload());
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

        function addRampUpRow() {
            const dayNumber = $('#rampUpContainer .ramp-up-row').length + 1;
            const row = $(`
                <div class="input-group input-group-sm mb-1 ramp-up-row">
                    <span class="input-group-text ramp-up-day-label">Hari ${dayNumber}</span>
                    <input type="number" class="form-control" name="ramp_efficiency[]"
                        placeholder="Cnth: 50" min="0" max="100">
                    <span class="input-group-text">%</span>
                    <button type="button" class="btn btn-outline-danger" tabindex="-1">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `);
            row.find('button').on('click', function() {
                row.remove();
                renumberRampUpRows();
                calculateLineMap();
            });
            row.find('input').on('input', calculateLineMap);
            $('#rampUpContainer').append(row);
        }

        function renumberRampUpRows() {
            $('#rampUpContainer .ramp-up-row').each(function(index) {
                $(this).find('.ramp-up-day-label').text('Hari ' + (index + 1));
            });
        }

        function getRampUpEfficiencies() {
            return $('#rampUpContainer input[name="ramp_efficiency[]"]').map(function() {
                return parseFloat($(this).val());
            }).get().filter(val => !isNaN(val));
        }

        function openNewLineMap() {
            $('#formLineMap').trigger('reset');
            $('#editid').val('');
            $('#lineMapModalTitle').text('Tambah Line Map');
            $('.select2bs4').val('').trigger('change');
            $('#rampUpContainer').empty();
            calculateLineMap();
        }

        function openEditLineMap(data) {
            $('#formLineMap').trigger('reset');
            $('#editid').val(data.id);
            $('#lineMapModalTitle').text('Edit Line Map');

            $('#cboline').val(data.line).trigger('change');
            $('#txtstyle').val(data.style);
            $('#txtsmv').val(data.smv);
            $('#txtefficiency').val(data.efficiency !== null ? Math.round(data.efficiency * 100) : '');
            $('#txtorderqty').val(data.qty_order !== null ?
                Number(data.qty_order).toLocaleString('id-ID').replace(/,/g, '.') : '');
            $('#txtbuyer').val(data.buyer);
            $('#txtmanpower').val(data.man_power);
            $('#txtworkingminutes').val(data.working_min);
            $('#cbodate').val(data.tgl_start);

            $('#rampUpContainer').empty();
            (data.ramp_up_efficiency || []).forEach(function(eff) {
                addRampUpRow();
                $('#rampUpContainer .ramp-up-row').last().find('input[name="ramp_efficiency[]"]')
                    .val(Math.round(eff * 100));
            });

            calculateLineMap();
        }

        function calculateLineMap() {
            const manPower = parseFloat($('#txtmanpower').val()) || 0;
            const workingMinutes = parseFloat($('#txtworkingminutes').val()) || 0;
            const smv = parseFloat($('#txtsmv').val()) || 0;
            const efficiency = parseFloat($('#txtefficiency').val()) || 0;
            const orderQty = parseFloat(($('#txtorderqty').val() || '').replace(/\./g, '')) || 0;
            const rampUp = getRampUpEfficiencies();

            const minsAvailable = manPower * workingMinutes;
            const outputPerDay100 = smv > 0 ? minsAvailable / smv : 0;
            const outputPerDayEfficiency = outputPerDay100 * (efficiency / 100);

            let totalDays = 0;
            if (outputPerDay100 > 0 && orderQty > 0) {
                let produced = 0;
                const maxDays = 3650;
                while (produced < orderQty && totalDays < maxDays) {
                    const eff = totalDays < rampUp.length ? (rampUp[totalDays] / 100) : (efficiency / 100);
                    const dailyOutput = outputPerDay100 * eff;
                    if (dailyOutput <= 0) break;
                    produced += dailyOutput;
                    totalDays++;
                }
            }

            $('#txtminsavailable').val(minsAvailable.toLocaleString('id-ID', {
                maximumFractionDigits: 0
            }));
            $('#txtoutputperday100').val(outputPerDay100.toLocaleString('id-ID', {
                maximumFractionDigits: 0
            }));
            $('#txtoutputperdayefficiency').val(outputPerDayEfficiency.toLocaleString('id-ID', {
                maximumFractionDigits: 0
            }));
            $('#txttotaldays').val(totalDays.toLocaleString('id-ID', {
                maximumFractionDigits: 0
            }));
        }

        $('#txtmanpower, #txtworkingminutes, #txtsmv, #txtefficiency, #txtorderqty').on('input', calculateLineMap);

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
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: data.message,
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => location.reload());
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

            const formData = new FormData(form);
            if (formData.has('txtorderqty')) {
                formData.set('txtorderqty', formData.get('txtorderqty').replace(/\./g, ''));
            }

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
                        form.reset();
                        $('.select2bs4').val('').trigger('change');
                        $('#rampUpContainer').empty();
                        calculateLineMap();

                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: data.message,
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => location.reload());
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
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Terjadi kesalahan saat menyimpan data',
                        confirmButtonText: 'Tutup'
                    });
                });
        }
    </script>
@endsection
