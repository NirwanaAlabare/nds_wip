@extends('layouts.index')

@section('custom-link')
    <style type="text/css">
        .hbar-list {
            max-height: 340px;
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
            cursor: pointer;
            padding: 6px;
            margin: 0 -6px;
            border-radius: 6px;
            transition: background-color .15s ease-in-out;
        }

        .hbar-row:hover {
            background-color: #f7f9fc;
        }

        .hbar-row+.hbar-row {
            margin-top: 6px;
        }

        /* Label & angka ditaruh sebaris di ATAS bar, bukan di sampingnya. Waktu masih
           bersebelahan, label cuma kebagian 110px sehingga nama yang berawalan sama
           ("GEDUNG 1 - LINE ...") terpotong jadi kelihatan identik semua. */
        .hbar-head {
            display: flex;
            align-items: baseline;
            gap: 10px;
            margin-bottom: 5px;
        }

        .hbar-label {
            flex: 1 1 auto;
            min-width: 0;
            font-size: 12.5px;
            color: #495363;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .hbar-track {
            width: 100%;
            height: 8px;
            border-radius: 4px;
            background-color: #eef0f3;
            overflow: hidden;
        }

        .hbar-fill {
            height: 100%;
            border-radius: 4px;
            min-width: 2px;
        }

        .hbar-value {
            flex: 0 0 auto;
            margin-left: auto;
            font-size: 12.5px;
            font-weight: 700;
            color: #1e2b3c;
        }

        /* Mesin yang belum didata lokasinya dipisah dari daftar bar. Jumlahnya jauh lebih
           besar dari area mana pun, jadi kalau ikut diadu di skala yang sama semua bar
           area lain jadi rata-rata kelihatan kosong. */
        .hbar-note {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 10px;
            margin-bottom: 10px;
            border: 1px dashed #d7dce3;
            border-radius: 6px;
            background-color: #fbfcfd;
            color: #8a94a6;
            font-size: 12.5px;
            cursor: pointer;
            transition: background-color .15s ease-in-out, color .15s ease-in-out;
        }

        .hbar-note:hover {
            background-color: #f2f5f9;
            color: #1e2b3c;
        }

        .hbar-note b {
            margin-left: auto;
            color: #1e2b3c;
        }

        /* Toolbar cari + urutkan di kartu daftar (Area & Jenis Mesin) */
        .hbar-toolbar {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 10px;
        }

        .hbar-toolbar .hbar-search {
            flex: 1 1 auto;
            min-width: 0;
        }

        .hbar-sort {
            flex: 0 0 auto;
            white-space: nowrap;
        }

        .hbar-empty {
            display: none;
            padding: 10px 0;
            color: #8a94a6;
            font-size: 12.5px;
        }

        /* ---- Kartu Status Mesin ---- */
        .stat-hero {
            display: flex;
            align-items: baseline;
            gap: 8px;
            margin-bottom: 10px;
        }

        .stat-hero-value {
            font-size: 30px;
            font-weight: 700;
            line-height: 1;
            color: #1e2b3c;
        }

        .stat-hero-label {
            font-size: 12.5px;
            color: #8a94a6;
        }

        /* Semua status dalam satu bar, jadi komposisinya kebaca sekali lihat */
        .stat-stack {
            display: flex;
            height: 10px;
            border-radius: 5px;
            background-color: #eef0f3;
            overflow: hidden;
            margin-bottom: 14px;
        }

        .stat-stack-seg {
            height: 100%;
            cursor: pointer;
            transition: opacity .15s ease-in-out;
        }

        .stat-stack-seg:hover {
            opacity: .75;
        }

        .stat-split {
            display: flex;
            gap: 8px;
            margin-top: 14px;
            padding-top: 12px;
            border-top: 1px solid #eef0f3;
        }

        .stat-chip {
            flex: 1 1 0;
            padding: 8px 10px;
            border: 1px solid #eef0f3;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color .15s ease-in-out, border-color .15s ease-in-out;
        }

        .stat-chip:hover {
            background-color: #f7f9fc;
            border-color: #d7dce3;
        }

        .stat-chip-label {
            font-size: 11px;
            letter-spacing: .03em;
            text-transform: uppercase;
            color: #8a94a6;
        }

        .stat-chip-value {
            font-size: 18px;
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
            margin-top: 10px;
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
                    <h5 class="card-title fw-bold mb-0"><i class="fas fa-map-marker-alt"></i> Qty per Area</h5>
                </div>
                <div class="card-body">
                    @php
                        // id_lokasi 0 = belum didata. Dipisah dari daftar bar & tidak ikut
                        // menentukan skala, supaya area yang sudah didata tetap terbaca.
                        $lokasiBelum = collect($tot_per_lokasi)->first(fn($r) => (int) $r->id_lokasi === 0);
                        $lokasiTerdata = collect($tot_per_lokasi)->filter(fn($r) => (int) $r->id_lokasi !== 0)->values();
                        $maxLokasi = $lokasiTerdata->max('total') ?: 1;
                    @endphp

                    @if ($lokasiBelum)
                        <div class="hbar-note" data-id-lokasi="0">
                            <i class="fas fa-exclamation-circle"></i>
                            <span>Belum didata lokasinya</span>
                            <b>{{ $lokasiBelum->total }}</b>
                        </div>
                    @endif

                    <div class="hbar-toolbar">
                        <input type="text" class="form-control form-control-sm hbar-search"
                            data-target="#listArea" placeholder="Cari area...">
                        <button type="button" class="btn btn-sm btn-outline-secondary hbar-sort"
                            data-target="#listArea" data-mode="qty" title="Urutkan: Qty terbanyak">
                            <i class="fas fa-sort-amount-down"></i>
                        </button>
                    </div>

                    <div class="hbar-list" id="listArea">
                        @forelse ($lokasiTerdata as $row)
                            @php
                                $percent = round(($row->total / $maxLokasi) * 100);
                            @endphp
                            <div class="hbar-row" data-id-lokasi="{{ $row->id_lokasi }}">
                                <div class="hbar-head">
                                    <div class="hbar-label" title="{{ $row->lokasi }}">{{ $row->lokasi }}</div>
                                    <div class="hbar-value">{{ $row->total }}</div>
                                </div>
                                <div class="hbar-track">
                                    <div class="hbar-fill" style="width: {{ $percent }}%; background-color: #238380;">
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted mb-0">Belum ada mesin yang lokasinya terdata.</p>
                        @endforelse
                    </div>
                    <div class="hbar-empty" data-for="#listArea">Tidak ada area yang cocok.</div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card card-sb h-100">
                <div class="card-header">
                    <h5 class="card-title fw-bold mb-0"><i class="fas fa-layer-group"></i> Qty per Jenis Mesin</h5>
                </div>
                <div class="card-body">
                    @php
                        $maxTotal = collect($tot_jenis)->max('total') ?: 1;
                    @endphp

                    <div class="hbar-toolbar">
                        <input type="text" class="form-control form-control-sm hbar-search"
                            data-target="#listJenis" placeholder="Cari jenis mesin...">
                        <button type="button" class="btn btn-sm btn-outline-secondary hbar-sort"
                            data-target="#listJenis" data-mode="qty" title="Urutkan: Qty terbanyak">
                            <i class="fas fa-sort-amount-down"></i>
                        </button>
                    </div>

                    <div class="hbar-list" id="listJenis">
                        @forelse ($tot_jenis as $row)
                            @php
                                $percent = round(($row->total / $maxTotal) * 100);
                            @endphp
                            <div class="hbar-row" data-jenis="{{ $row->nm_jenis }}">
                                <div class="hbar-head">
                                    <div class="hbar-label" title="{{ $row->nm_jenis ?: '(Tanpa Jenis)' }}">
                                        {{ $row->nm_jenis ?: '(Tanpa Jenis)' }}</div>
                                    <div class="hbar-value">{{ $row->total }}</div>
                                </div>
                                <div class="hbar-track">
                                    <div class="hbar-fill" style="width: {{ $percent }}%; background-color: #3987e5;">
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted mb-0">Tidak ada data.</p>
                        @endforelse
                    </div>
                    <div class="hbar-empty" data-for="#listJenis">Tidak ada jenis mesin yang cocok.</div>
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
                        $sortedStatus = collect($tot_per_status)->sortBy(
                            fn($row) => $statusMeta[$row->status]['order'] ?? 99,
                        );
                        $kepemilikan = collect($tot_per_kepemilikan)->keyBy('kepemilikan');
                    @endphp

                    <div class="stat-hero">
                        <span class="stat-hero-value">{{ number_format(collect($tot_per_status)->sum('total'), 0, ',', '.') }}</span>
                        <span class="stat-hero-label">mesin tercatat di pabrik</span>
                    </div>

                    {{-- Komposisi seluruh status dalam satu bar, tiap segmen bisa diklik.
                         Kalau statusnya cuma satu, bar ini cuma jadi duplikat bar di bawahnya. --}}
                    @if ($sortedStatus->count() > 1)
                        <div class="stat-stack">
                            @foreach ($sortedStatus as $row)
                                @php
                                    $meta = $statusMeta[$row->status] ?? ['label' => $row->status, 'color' => '#8a94a6'];
                                @endphp
                                <div class="stat-stack-seg" data-status="{{ $row->status }}"
                                    title="{{ $meta['label'] }} : {{ $row->total }}"
                                    style="width: {{ ($row->total / $grandTotal) * 100 }}%; background-color: {{ $meta['color'] }};">
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @forelse ($sortedStatus as $row)
                        @php
                            $meta = $statusMeta[$row->status] ?? [
                                'label' => ucfirst(strtolower($row->status)),
                                'color' => '#8a94a6',
                            ];
                            $percent = round(($row->total / $grandTotal) * 100);
                        @endphp
                        <div class="status-row" data-status="{{ $row->status }}">
                            <div>
                                <span class="status-dot" style="background-color: {{ $meta['color'] }};"></span>
                                <span class="status-row-label ms-2">{{ $meta['label'] }}</span>
                                <span class="status-row-count ms-1">{{ $row->total }} mesin
                                    ({{ $percent }}%)
                                </span>
                            </div>
                            <div class="status-progress-track">
                                <div class="status-progress-fill"
                                    style="width: {{ $percent }}%; background-color: {{ $meta['color'] }};"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted mb-0">Tidak ada data.</p>
                    @endforelse

                    <div class="stat-split">
                        <div class="stat-chip" data-kepemilikan="PEMBELIAN" title="Lihat detail mesin pembelian">
                            <div class="stat-chip-label">Pembelian</div>
                            <div class="stat-chip-value">{{ number_format($kepemilikan['PEMBELIAN']->total ?? 0, 0, ',', '.') }}</div>
                        </div>
                        <div class="stat-chip" data-kepemilikan="SEWA" title="Lihat detail mesin sewa">
                            <div class="stat-chip-label">Sewa</div>
                            <div class="stat-chip-value">{{ number_format($kepemilikan['SEWA']->total ?? 0, 0, ',', '.') }}</div>
                        </div>
                    </div>
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
                $matrixCells = collect($tot_area_x_jenis_mesin)->groupBy('id_lokasi');
            @endphp

            <div class="matrix-wrap">
                <table class="matrix-table" id="matrixTable">
                    <thead>
                        <tr>
                            <th data-key="0" data-type="text">Area <span class="sort-icon"></span></th>
                            @foreach ($matrixJenisCols as $i => $nmJenis)
                                <th data-key="{{ $i + 1 }}" data-type="number">{{ $nmJenis ?: '(Tanpa Jenis)' }} <span
                                        class="sort-icon"></span></th>
                            @endforeach
                            <th class="matrix-total" data-key="{{ $matrixJenisCols->count() + 1 }}" data-type="number">
                                Total <span class="sort-icon"></span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($tot_per_lokasi as $lokasiRow)
                            @php
                                $rowCells = $matrixCells
                                    ->get($lokasiRow->id_lokasi ?? 0, collect())
                                    ->pluck('total', 'nm_jenis');
                                $lokasiKey = $lokasiRow->id_lokasi ?? 0;
                                $lokasiLabel = $lokasiRow->lokasi ?? '(Belum Didata)';
                            @endphp
                            <tr data-id-lokasi="{{ $lokasiKey }}">
                                <th>{{ $lokasiLabel }}</th>
                                @foreach ($matrixJenisCols as $nmJenis)
                                    @php $cellTotal = $rowCells->get($nmJenis, 0); @endphp
                                    <td
                                        @if ($cellTotal) class="matrix-cell-clickable" data-id-lokasi="{{ $lokasiKey }}"
                                        data-jenis="{{ $nmJenis }}" @endif>
                                        {{ $cellTotal ?: '-' }}</td>
                                @endforeach
                                <td class="matrix-total" data-id-lokasi="{{ $lokasiKey }}">{{ $lokasiRow->total }}
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
                    <div class="mb-2 d-flex align-items-center gap-2 flex-wrap">
                        <input type="text" id="areaJenisUnitSearch" class="form-control form-control-sm flex-grow-1"
                            placeholder="Cari Serial Number / Merk / Tipe / No BPB / Status..." style="min-width: 220px;">
                        <div class="btn-group btn-group-sm" role="group" id="areaJenisUnitKepemilikanFilter">
                            <button type="button" class="btn btn-outline-secondary active"
                                data-kepemilikan="ALL">Semua</button>
                            <button type="button" class="btn btn-outline-secondary"
                                data-kepemilikan="PEMBELIAN">Pembelian</button>
                            <button type="button" class="btn btn-outline-secondary"
                                data-kepemilikan="SEWA">Sewa</button>
                        </div>
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
                                    <th>Kepemilikan</th>
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
        function openAreaJenisUnitModal(idLokasi, nmJenis, status, titleText, kepemilikan) {
            $('#areaJenisUnitModalLabel').text(titleText);
            $('#areaJenisUnitSearch').val('');
            // Tombol kepemilikan di dalam modal ikut disetel, biar sejalan dengan yang diklik
            $('#areaJenisUnitKepemilikanFilter button').removeClass('active');
            $('#areaJenisUnitKepemilikanFilter button[data-kepemilikan="' + (kepemilikan || 'ALL') + '"]')
                .addClass('active');
            let $body = $('#areaJenisUnitTableBody').empty();

            $.ajax({
                type: 'GET',
                url: '{{ route('asset_mesin_report_area_jenis_unit') }}',
                data: {
                    id_lokasi: idLokasi,
                    nm_jenis: nmJenis,
                    status: status,
                    kepemilikan: kepemilikan
                },
                success: function(units) {
                    if (!units.length) {
                        $body.append(
                            '<tr><td colspan="7" class="text-center text-muted">Tidak ada data.</td></tr>');
                    } else {
                        units.forEach(function(unit, i) {
                            let isSewa = unit.kepemilikan === 'SEWA';
                            let badgeClass = isSewa ? 'bg-warning text-dark' : 'bg-success';
                            let badgeLabel = isSewa ? 'Sewa' : 'Pembelian';
                            $body.append(`
                        <tr data-kepemilikan="${unit.kepemilikan ?? ''}">
                            <td class="text-center align-middle">${i + 1}</td>
                            <td class="align-middle">${unit.serial_number ?? '-'}</td>
                            <td class="align-middle">${unit.nm_merk ?? '-'}</td>
                            <td class="align-middle">${unit.tipe ?? '-'}</td>
                            <td class="align-middle">${unit.bpbno_int ?? '-'}</td>
                            <td class="align-middle">${unit.status ?? '-'}</td>
                            <td class="align-middle"><span class="badge ${badgeClass}">${badgeLabel}</span></td>
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

        // Terapkan filter Semua/Milik/Sewa + kata kunci search sekaligus ke baris tabel modal
        function applyAreaJenisUnitFilters() {
            let keyword = $('#areaJenisUnitSearch').val().toLowerCase();
            let kepemilikan = $('#areaJenisUnitKepemilikanFilter button.active').data('kepemilikan');

            $('#areaJenisUnitTableBody tr').each(function() {
                let $row = $(this);
                let matchKeyword = $row.text().toLowerCase().indexOf(keyword) !== -1;
                let matchKepemilikan = kepemilikan === 'ALL' || $row.data('kepemilikan') === kepemilikan;
                $row.toggleClass('d-none', !(matchKeyword && matchKepemilikan));
            });
        }

        // Klik tombol filter Semua/Milik/Sewa di dalam modal detail unit
        $('#areaJenisUnitKepemilikanFilter').on('click', 'button', function() {
            $('#areaJenisUnitKepemilikanFilter button').removeClass('active');
            $(this).addClass('active');
            applyAreaJenisUnitFilters();
        });

        // Klik segmen di bar komposisi status -> sama dengan klik baris status-nya
        $('.stat-stack-seg[data-status]').on('click', function() {
            let status = $(this).data('status');
            openAreaJenisUnitModal(null, null, status, `Status ${status} - Semua Mesin`);
        });

        // Klik kartu Pembelian / Sewa -> detail unit menurut kepemilikan
        $('.stat-chip[data-kepemilikan]').on('click', function() {
            let kepemilikan = $(this).data('kepemilikan');
            let label = $(this).find('.stat-chip-label').text();
            openAreaJenisUnitModal(null, null, null, `Mesin ${label} - Semua Area`, kepemilikan);
        });

        // Cari di daftar bar (Area / Jenis Mesin). Pencocokan pakai teks label.
        $('.hbar-search').on('keyup', function() {
            let keyword = $(this).val().toLowerCase();
            let target = $(this).data('target');
            let cocok = 0;

            $(target).find('.hbar-row').each(function() {
                let label = $(this).find('.hbar-label').text().trim().toLowerCase();
                let tampil = label.indexOf(keyword) !== -1;

                $(this).toggleClass('d-none', !tampil);
                if (tampil) cocok++;
            });

            $(`.hbar-empty[data-for="${target}"]`).toggle(cocok === 0);
        });

        // Urutkan daftar bar: qty terbanyak <-> nama A-Z.
        // data-mode = urutan yang akan dipakai kalau tombol diklik, jadi isinya selalu sama
        // dengan tooltip & ikon yang sedang tampil.
        $('.hbar-sort').on('click', function() {
            let btn = $(this);
            let target = btn.data('target');
            let mode = btn.data('mode');
            let modeBerikutnya = mode === 'qty' ? 'nama' : 'qty';

            btn.data('mode', modeBerikutnya);
            btn.attr('title', modeBerikutnya === 'qty' ? 'Urutkan: Qty terbanyak' : 'Urutkan: Nama A-Z');
            btn.find('i').attr('class',
                modeBerikutnya === 'qty' ? 'fas fa-sort-amount-down' : 'fas fa-sort-alpha-down');

            let $list = $(target);
            let $rows = $list.find('.hbar-row').get();

            $rows.sort(function(a, b) {
                if (mode === 'qty') {
                    return Number($(b).find('.hbar-value').text()) - Number($(a).find('.hbar-value').text());
                }
                return $(a).find('.hbar-label').text().trim()
                    .localeCompare($(b).find('.hbar-label').text().trim());
            });

            $list.append($rows);
        });

        // Klik baris "Belum didata lokasinya" -> detail mesin yang id_lokasi-nya masih kosong
        $('.hbar-note[data-id-lokasi]').on('click', function() {
            openAreaJenisUnitModal($(this).data('id-lokasi'), null, null,
                'Belum Didata Lokasinya - Semua Jenis Mesin');
        });

        // Klik bar di "Report Qty per Area" -> detail semua jenis mesin di area tersebut
        $('.hbar-row[data-id-lokasi]').on('click', function() {
            let idLokasi = $(this).data('id-lokasi');
            let label = $(this).find('.hbar-label').text();
            openAreaJenisUnitModal(idLokasi, null, null, `${label} - Semua Jenis Mesin`);
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
            let idLokasi = $(this).data('id-lokasi');
            let nmJenis = $(this).data('jenis');
            let label = $(this).closest('tr').find('th').first().text();
            openAreaJenisUnitModal(idLokasi, nmJenis, null, `${label} - ${nmJenis}`);
        });

        // Klik kolom Total per baris -> detail semua jenis mesin di area itu
        $('#matrixTable').on('click', 'td.matrix-total', function() {
            let idLokasi = $(this).data('id-lokasi');
            let label = $(this).closest('tr').find('th').first().text();
            openAreaJenisUnitModal(idLokasi, null, null, `${label} - Semua Jenis Mesin`);
        });

        // Search di dalam modal detail unit: filter baris yang sudah dimuat
        $('#areaJenisUnitSearch').on('keyup', function() {
            applyAreaJenisUnitFilters();
        });

        // Search: filter baris matrix berdasarkan nama Area
        $('#matrixSearch').on('keyup', function() {
            let keyword = $(this).val().toLowerCase();
            $('#matrixTable tbody tr[data-id-lokasi]').each(function() {
                let lokasi = $(this).find('th').first().text().toLowerCase();
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

            let $rows = $('#matrixTable tbody tr[data-id-lokasi]').get();

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
