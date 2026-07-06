@extends('layouts.index')

@section('custom-link')
    <style type="text/css">
        .hbar-list {
            max-height: 300px;
            overflow-y: auto;
            padding-right: 6px;
        }

        .hbar-list::-webkit-scrollbar {
            width: 6px;
        }

        .hbar-list::-webkit-scrollbar-thumb {
            background-color: #d7dce3;
            border-radius: 3px;
        }

        .hbar-row {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            padding: 4px 6px;
            margin: 0 -6px;
            border-radius: 6px;
            transition: background-color .15s ease-in-out;
        }

        .hbar-row:hover {
            background-color: #f7f9fc;
        }

        .hbar-row+.hbar-row {
            margin-top: 8px;
        }

        .hbar-label {
            flex: 0 0 110px;
            font-size: 12.5px;
            color: #495363;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .hbar-track {
            flex: 1 1 auto;
            height: 8px;
            border-radius: 4px;
            background-color: #eef0f3;
            overflow: hidden;
        }

        .hbar-fill {
            height: 100%;
            border-radius: 4px;
        }

        .hbar-value {
            flex: 0 0 28px;
            text-align: right;
            font-size: 12.5px;
            font-weight: 700;
            color: #1e2b3c;
        }

        .status-row {
            cursor: pointer;
            padding: 6px;
            margin: 0 -6px;
            border-radius: 6px;
            transition: background-color .15s ease-in-out;
        }

        .status-row:hover {
            background-color: #f7f9fc;
        }

        .status-row+.status-row {
            margin-top: 12px;
        }

        .status-dot {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
        }

        .status-row-label {
            font-weight: 700;
            color: #1e2b3c;
        }

        .status-row-count {
            color: #8a94a6;
        }

        .status-progress-track {
            margin-top: 8px;
            height: 6px;
            border-radius: 3px;
            background-color: #eef0f3;
            overflow: hidden;
        }

        .status-progress-fill {
            height: 100%;
            border-radius: 3px;
        }

        .matrix-search {
            max-width: 220px;
        }

        .matrix-wrap {
            max-height: 420px;
            overflow: auto;
        }

        .matrix-table {
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
            font-size: 13px;
            white-space: nowrap;
        }

        .matrix-table th,
        .matrix-table td {
            padding: 10px 14px;
            border-bottom: 1px solid #eef0f3;
        }

        .matrix-table thead th {
            position: sticky;
            top: 0;
            z-index: 2;
            background-color: #fff;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .03em;
            color: #8a94a6;
            text-transform: uppercase;
            text-align: left;
            cursor: pointer;
            user-select: none;
        }

        .matrix-table thead th:hover {
            color: #1e2b3c;
        }

        .matrix-table thead th .sort-icon {
            margin-left: 4px;
            opacity: .4;
        }

        .matrix-table thead th.sort-asc .sort-icon::after {
            content: '\2191';
            opacity: 1;
        }

        .matrix-table thead th.sort-desc .sort-icon::after {
            content: '\2193';
            opacity: 1;
        }

        .matrix-table thead th:not(.sort-asc):not(.sort-desc) .sort-icon::after {
            content: '\2195';
        }

        .matrix-table thead th.matrix-total,
        .matrix-table td.matrix-total {
            text-align: right;
        }

        .matrix-table tbody th {
            position: sticky;
            left: 0;
            z-index: 1;
            background-color: #fff;
            font-weight: 700;
            color: #1e2b3c;
            text-align: left;
        }

        .matrix-table thead th:first-child {
            position: sticky;
            left: 0;
            z-index: 3;
        }

        .matrix-table td {
            color: #495363;
            text-align: left;
        }

        .matrix-table td.matrix-total,
        .matrix-table th.matrix-total {
            font-weight: 700;
            color: #1e2b3c;
        }

        .matrix-table td.matrix-cell-clickable {
            cursor: pointer;
        }

        .matrix-table td.matrix-cell-clickable:hover {
            background-color: #eef4fd;
            text-decoration: underline;
        }

        .matrix-table td.matrix-total {
            cursor: pointer;
        }

        .matrix-table td.matrix-total:hover {
            background-color: #eef4fd;
            text-decoration: underline;
        }

        .matrix-table tbody tr:hover th,
        .matrix-table tbody tr:hover td {
            background-color: #f7f9fc;
        }

        .matrix-table tbody tr.d-none {
            display: none;
        }

        .unit-modal-table-wrap {
            max-height: 55vh;
            overflow: auto;
        }

        .unit-modal-table-wrap table {
            border-collapse: separate;
            border-spacing: 0;
        }

        .unit-modal-table-wrap table thead th {
            position: sticky;
            top: 0;
            z-index: 1;
            background-color: var(--sb-color);
            color: var(--light-color);
        }

        #areaJenisUnitTableBody tr.d-none {
            display: none;
        }
    </style>
@endsection

@section('content')
    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card card-sb h-100">
                <div class="card-header">
                    <h5 class="card-title fw-bold mb-0"><i class="fas fa-map-marker-alt"></i> Report Qty per Area</h5>
                </div>
                <div class="card-body">
                    @php
                        $maxLokasi = collect($tot_per_lokasi)->max('total') ?: 1;
                    @endphp

                    <div class="hbar-list">
                        @forelse ($tot_per_lokasi as $row)
                            @php
                                $percent = round(($row->total / $maxLokasi) * 100);
                            @endphp
                            <div class="hbar-row" data-lokasi="{{ $row->lokasi }}">
                                <div class="hbar-label" title="{{ $row->lokasi }}">{{ $row->lokasi }}</div>
                                <div class="hbar-track">
                                    <div class="hbar-fill" style="width: {{ $percent }}%; background-color: #238380;">
                                    </div>
                                </div>
                                <div class="hbar-value">{{ $row->total }}</div>
                            </div>
                        @empty
                            <p class="text-muted mb-0">Tidak ada data.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card card-sb h-100">
                <div class="card-header">
                    <h5 class="card-title fw-bold mb-0"><i class="fas fa-layer-group"></i> Report per Jenis Mesin</h5>
                </div>
                <div class="card-body">
                    @php
                        $maxTotal = collect($tot_jenis)->max('total') ?: 1;
                    @endphp

                    <div class="hbar-list">
                        @forelse ($tot_jenis as $row)
                            @php
                                $percent = round(($row->total / $maxTotal) * 100);
                            @endphp
                            <div class="hbar-row" data-jenis="{{ $row->nm_jenis }}">
                                <div class="hbar-label" title="{{ $row->nm_jenis }}">{{ $row->nm_jenis }}</div>
                                <div class="hbar-track">
                                    <div class="hbar-fill" style="width: {{ $percent }}%; background-color: #3987e5;">
                                    </div>
                                </div>
                                <div class="hbar-value">{{ $row->total }}</div>
                            </div>
                        @empty
                            <p class="text-muted mb-0">Tidak ada data.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card card-sb h-100">
                <div class="card-header">
                    <h5 class="card-title fw-bold mb-0"><i class="fas fa-chart-simple"></i> Status Mesin</h5>
                </div>
                <div class="card-body">
                    @php
                        $statusMeta = [
                            'ACTIVE' => ['label' => 'Active', 'color' => '#22c55e', 'order' => 1],
                            'IDLE' => ['label' => 'Idle', 'color' => '#f2b90c', 'order' => 2],
                            'BREAKDOWN' => ['label' => 'Breakdown', 'color' => '#ea5455', 'order' => 3],
                        ];
                        $grandTotal = collect($tot_per_status)->sum('total') ?: 1;
                        $sortedStatus = collect($tot_per_status)->sortBy(fn($row) => $statusMeta[$row->status]['order'] ?? 99);
                    @endphp

                    @forelse ($sortedStatus as $row)
                        @php
                            $meta = $statusMeta[$row->status] ?? ['label' => ucfirst(strtolower($row->status)), 'color' => '#8a94a6'];
                            $percent = round(($row->total / $grandTotal) * 100);
                        @endphp
                        <div class="status-row" data-status="{{ $row->status }}">
                            <div>
                                <span class="status-dot" style="background-color: {{ $meta['color'] }};"></span>
                                <span class="status-row-label ms-2">{{ $meta['label'] }}</span>
                                <span class="status-row-count ms-1">{{ $row->total }} mesin ({{ $percent }}%)</span>
                            </div>
                            <div class="status-progress-track">
                                <div class="status-progress-fill"
                                    style="width: {{ $percent }}%; background-color: {{ $meta['color'] }};"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted mb-0">Tidak ada data.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="card card-sb mt-3">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-table-cells"></i> Matrix Area x Jenis Mesin</h5>
            <input type="text" id="matrixSearch" class="form-control form-control-sm matrix-search"
                placeholder="Cari Area...">
        </div>
        <div class="card-body p-0">
            @php
                $matrixJenisCols = collect($tot_jenis)->pluck('nm_jenis');
                $matrixCells = collect($tot_area_x_jenis_mesin)->groupBy('lokasi');
            @endphp

            <div class="matrix-wrap">
                <table class="matrix-table" id="matrixTable">
                    <thead>
                        <tr>
                            <th data-key="0" data-type="text">Area <span class="sort-icon"></span></th>
                            @foreach ($matrixJenisCols as $i => $nmJenis)
                                <th data-key="{{ $i + 1 }}" data-type="number">{{ $nmJenis }} <span
                                        class="sort-icon"></span></th>
                            @endforeach
                            <th class="matrix-total" data-key="{{ $matrixJenisCols->count() + 1 }}" data-type="number">
                                Total <span class="sort-icon"></span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($tot_per_lokasi as $lokasiRow)
                            @php
                                $rowCells = $matrixCells->get($lokasiRow->lokasi, collect())->pluck('total', 'nm_jenis');
                            @endphp
                            <tr data-lokasi="{{ $lokasiRow->lokasi }}">
                                <th>{{ $lokasiRow->lokasi }}</th>
                                @foreach ($matrixJenisCols as $nmJenis)
                                    @php $cellTotal = $rowCells->get($nmJenis, 0); @endphp
                                    <td @if ($cellTotal) class="matrix-cell-clickable" data-lokasi="{{ $lokasiRow->lokasi }}"
                                        data-jenis="{{ $nmJenis }}" @endif>{{ $cellTotal ?: '-' }}</td>
                                @endforeach
                                <td class="matrix-total" data-lokasi="{{ $lokasiRow->lokasi }}">{{ $lokasiRow->total }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $matrixJenisCols->count() + 2 }}" class="text-muted">Tidak ada data.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Detail Unit Area x Jenis Mesin -->
    <div class="modal fade" id="areaJenisUnitModal" tabindex="-1" aria-labelledby="areaJenisUnitModalLabel"
        aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-sb text-white">
                    <h5 class="modal-title mb-0" id="areaJenisUnitModalLabel">Detail Unit Mesin</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <input type="text" id="areaJenisUnitSearch" class="form-control form-control-sm"
                            placeholder="Cari Serial Number / Merk / Tipe / No BPB / Status...">
                    </div>
                    <div class="unit-modal-table-wrap">
                        <table class="table table-bordered table-sm align-middle mb-0">
                            <thead class="bg-sb">
                                <tr>
                                    <th class="text-center">No</th>
                                    <th>Serial Number</th>
                                    <th>Merk</th>
                                    <th>Tipe</th>
                                    <th>No BPB</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="areaJenisUnitTableBody"></tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('custom-script')
    <script>
        function openAreaJenisUnitModal(lokasi, nmJenis, status, titleText) {
            $('#areaJenisUnitModalLabel').text(titleText);
            $('#areaJenisUnitSearch').val('');
            let $body = $('#areaJenisUnitTableBody').empty();

            $.ajax({
                type: 'GET',
                url: '{{ route('asset_mesin_report_area_jenis_unit') }}',
                data: {
                    lokasi: lokasi,
                    nm_jenis: nmJenis,
                    status: status
                },
                success: function(units) {
                    if (!units.length) {
                        $body.append(
                            '<tr><td colspan="6" class="text-center text-muted">Tidak ada data.</td></tr>');
                    } else {
                        units.forEach(function(unit, i) {
                            $body.append(`
                        <tr>
                            <td class="text-center align-middle">${i + 1}</td>
                            <td class="align-middle">${unit.serial_number ?? '-'}</td>
                            <td class="align-middle">${unit.nm_merk ?? '-'}</td>
                            <td class="align-middle">${unit.tipe ?? '-'}</td>
                            <td class="align-middle">${unit.bpbno_int ?? '-'}</td>
                            <td class="align-middle">${unit.status ?? '-'}</td>
                        </tr>`);
                        });
                    }
                    $('#areaJenisUnitModal').modal('show');
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Gagal memuat data unit mesin.',
                    });
                }
            });
        }

        // Klik bar di "Report Qty per Area" -> detail semua jenis mesin di area tersebut
        $('.hbar-row[data-lokasi]').on('click', function() {
            let lokasi = $(this).data('lokasi');
            openAreaJenisUnitModal(lokasi, null, null, `${lokasi} - Semua Jenis Mesin`);
        });

        // Klik bar di "Report per Jenis Mesin" -> detail semua area untuk jenis tersebut
        $('.hbar-row[data-jenis]').on('click', function() {
            let nmJenis = $(this).data('jenis');
            openAreaJenisUnitModal(null, nmJenis, null, `${nmJenis} - Semua Area`);
        });

        // Klik baris di "Status Mesin" -> detail semua mesin dengan status tersebut
        $('.status-row[data-status]').on('click', function() {
            let status = $(this).data('status');
            let label = $(this).find('.status-row-label').text();
            openAreaJenisUnitModal(null, null, status, `Status ${label} - Semua Mesin`);
        });

        // Klik cell matrix (area x jenis) -> detail kombinasi spesifik
        $('#matrixTable').on('click', 'td.matrix-cell-clickable', function() {
            let lokasi = $(this).data('lokasi');
            let nmJenis = $(this).data('jenis');
            openAreaJenisUnitModal(lokasi, nmJenis, null, `${lokasi} - ${nmJenis}`);
        });

        // Klik kolom Total per baris -> detail semua jenis mesin di area itu
        $('#matrixTable').on('click', 'td.matrix-total', function() {
            let lokasi = $(this).data('lokasi');
            openAreaJenisUnitModal(lokasi, null, null, `${lokasi} - Semua Jenis Mesin`);
        });

        // Search di dalam modal detail unit: filter baris yang sudah dimuat
        $('#areaJenisUnitSearch').on('keyup', function() {
            let keyword = $(this).val().toLowerCase();
            $('#areaJenisUnitTableBody tr').each(function() {
                let text = $(this).text().toLowerCase();
                $(this).toggleClass('d-none', text.indexOf(keyword) === -1);
            });
        });

        // Search: filter baris matrix berdasarkan nama Area
        $('#matrixSearch').on('keyup', function() {
            let keyword = $(this).val().toLowerCase();
            $('#matrixTable tbody tr[data-lokasi]').each(function() {
                let lokasi = ($(this).data('lokasi') + '').toLowerCase();
                $(this).toggleClass('d-none', lokasi.indexOf(keyword) === -1);
            });
        });

        // Sort: klik header kolom matrix untuk sort baris (toggle asc/desc)
        $('#matrixTable thead th').on('click', function() {
            let $th = $(this);
            let key = $th.data('key');
            let type = $th.data('type');
            let asc = !$th.hasClass('sort-asc');

            $('#matrixTable thead th').removeClass('sort-asc sort-desc');
            $th.addClass(asc ? 'sort-asc' : 'sort-desc');

            let $rows = $('#matrixTable tbody tr[data-lokasi]').get();

            $rows.sort(function(a, b) {
                let cellA = $(a).find('th, td').eq(key).text().trim();
                let cellB = $(b).find('th, td').eq(key).text().trim();

                if (type === 'number') {
                    let valA = cellA === '-' ? 0 : parseFloat(cellA) || 0;
                    let valB = cellB === '-' ? 0 : parseFloat(cellB) || 0;
                    return asc ? valA - valB : valB - valA;
                }

                return asc ? cellA.localeCompare(cellB) : cellB.localeCompare(cellA);
            });

            $.each($rows, function(i, row) {
                $('#matrixTable tbody').append(row);
            });
        });
    </script>
@endsection
