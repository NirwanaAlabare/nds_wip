@extends('layouts.index')

@section('custom-link')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">

    @include('asset_management.partials.tab_style')

    <style type="text/css">
        .mon-seg {
            max-width: 420px;
            margin-bottom: 16px;
        }

        .mon-toolbar {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-end;
            gap: 10px;
            margin-bottom: 12px;
        }

        .mon-field label {
            display: block;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #6c757d;
            margin-bottom: 4px;
        }

        .mon-field .form-control,
        .mon-field .form-select {
            height: 38px;
            font-size: 13px;
            border: 1.5px solid #dee2e6;
            border-radius: 10px;
        }

        .mon-field.is-grow {
            flex: 1;
            min-width: 200px;
        }

        .mon-switch {
            display: flex;
            align-items: center;
            gap: 8px;
            height: 38px;
            padding: 0 12px;
            border: 1.5px solid #dee2e6;
            border-radius: 10px;
            font-size: 13px;
            user-select: none;
        }

        /* Menimpa gaya judul field (.mon-field label) untuk teks di dalam kotak centang */
        .mon-field .mon-switch label {
            display: inline;
            margin: 0;
            font-size: 13px;
            font-weight: 400;
            text-transform: none;
            letter-spacing: 0;
            color: inherit;
            cursor: pointer;
            white-space: nowrap;
        }

        .mon-switch .form-check-input {
            position: static;
            float: none;
            flex-shrink: 0;
            margin: 0;
        }

        .mon-export {
            height: 38px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
        }

        .mon-table {
            width: 100% !important;
            font-size: 13px;
        }

        .mon-table thead th {
            background: #f8f9fa;
            color: #6c757d;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            border-bottom-width: 1px;
            white-space: nowrap;
        }

        .mon-table td {
            vertical-align: middle;
        }

        .mon-table tr.is-overdue td {
            background: #fff5f5;
        }

        .mon-sub {
            display: block;
            font-family: SFMono-Regular, Menlo, Consolas, "Liberation Mono", monospace;
            font-size: 11px;
            color: #8a939b;
        }

        .mon-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 9px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            white-space: nowrap;
        }

        .mon-badge.is-ok { background: #e9f7ef; color: #146c43; }
        .mon-badge.is-late { background: #f8d7da; color: #b02a37; }
        .mon-badge.is-ambil { background: #e9f7ef; color: #146c43; }
        .mon-badge.is-kembali { background: #fff3e0; color: #a45a00; }
        .mon-badge.is-repair { background: #fff3cd; color: #856404; }
        .mon-badge.is-selesai { background: #e7f1ff; color: #0a58ca; }
        .mon-badge.is-muted { background: #f1f3f5; color: #6c757d; }

        .mon-panel .dataTables_info,
        .mon-panel .dataTables_paginate {
            font-size: 12px;
            margin-top: 10px;
        }

        .mon-panel {
            padding-bottom: 18px;
        }
    </style>
@endsection

@section('content')
    <div class="tt-wrap is-wide">
        <div class="tt-card">
            <div class="tt-hero">
                <div class="tt-hero-top">
                    <div class="tt-hero-icon"><i class="fa-solid fa-display"></i></div>
                    <div>
                        <h5 class="tt-hero-title">Monitoring Tab</h5>
                        <p class="tt-hero-sub">Posisi tab saat ini &amp; riwayat seluruh transaksi</p>
                    </div>
                    <div class="tt-hero-actions">
                        <a href="{{ route('asset_trans_tab') }}" class="tt-icon-btn" title="Transaksi Tab">
                            <i class="fa-solid fa-tablet-screen-button"></i>
                        </a>
                        <a href="{{ route('asset_opname_tab') }}" class="tt-icon-btn" title="Opname Tab">
                            <i class="fa-solid fa-clipboard-check"></i>
                        </a>
                    </div>
                </div>

                <div class="tt-stats is-4">
                    <div class="tt-stat">
                        <div class="tt-stat-icon"><i class="fa-solid fa-warehouse"></i></div>
                        <div>
                            <div class="tt-stat-value">{{ $idleCount }}</div>
                            <div class="tt-stat-label">Di ruangan</div>
                        </div>
                    </div>
                    <div class="tt-stat">
                        <div class="tt-stat-icon"><i class="fa-solid fa-person-walking-luggage"></i></div>
                        <div>
                            <div class="tt-stat-value">{{ $takenCount }}</div>
                            <div class="tt-stat-label">Di lapangan</div>
                        </div>
                    </div>
                    <div class="tt-stat is-danger">
                        <div class="tt-stat-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
                        <div>
                            <div class="tt-stat-value">{{ $overdueCount }}</div>
                            <div class="tt-stat-label">Belum kembali &gt; {{ $overdueHours }} jam</div>
                        </div>
                    </div>
                    <div class="tt-stat is-warning">
                        <div class="tt-stat-icon"><i class="fa-solid fa-screwdriver-wrench"></i></div>
                        <div>
                            <div class="tt-stat-value">{{ $repairCount }}</div>
                            <div class="tt-stat-label">Repair</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tt-body">
                <div class="tt-seg mon-seg" role="group" aria-label="Tampilan">
                    <input type="radio" class="btn-check" name="monView" id="monViewTaken" autocomplete="off" checked>
                    <label class="tt-seg-btn" for="monViewTaken" onclick="showPanel('taken')">
                        <i class="fa-solid fa-person-walking-luggage"></i> Sedang Dibawa
                    </label>
                    <input type="radio" class="btn-check" name="monView" id="monViewHistory" autocomplete="off">
                    <label class="tt-seg-btn" for="monViewHistory" onclick="showPanel('history')">
                        <i class="fa-solid fa-clock-rotate-left"></i> Riwayat
                    </label>
                </div>

                {{-- ========== Sedang dibawa ========== --}}
                <div class="mon-panel" id="panelTaken">
                    <div class="mon-toolbar">
                        <div class="mon-field">
                            <label for="takenTujuan">Tujuan</label>
                            <select id="takenTujuan" class="form-select" onchange="reloadTaken()">
                                <option value="">Semua tujuan</option>
                                @foreach ($mainLokasiList as $row)
                                    <option value="{{ $row->main_lokasi }}">{{ $row->main_lokasi }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mon-field">
                            <label for="takenOverdue">Status</label>
                            <div class="mon-switch">
                                <input type="checkbox" class="form-check-input" id="takenOverdue" onchange="reloadTaken()"
                                    {{ $onlyOverdue ? 'checked' : '' }}>
                                <label for="takenOverdue">Hanya yang belum kembali &gt; {{ $overdueHours }} jam</label>
                            </div>
                        </div>
                        <div class="mon-field is-grow">
                            <label for="takenSearch">Cari</label>
                            <input type="text" id="takenSearch" class="form-control" placeholder="Tab, RFID, nama, enroll ID...">
                        </div>
                        <button type="button" class="btn btn-outline-success mon-export" onclick="exportTaken()">
                            <i class="fa-solid fa-file-excel"></i> Export
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table id="tableTaken" class="table table-hover mon-table">
                            <thead>
                                <tr>
                                    <th>Tab</th>
                                    <th>Dibawa oleh</th>
                                    <th>Tujuan</th>
                                    <th>Diambil</th>
                                    <th>Lama</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>

                {{-- ========== Riwayat ========== --}}
                <div class="mon-panel d-none" id="panelHistory">
                    <div class="mon-toolbar">
                        <div class="mon-field">
                            <label for="historyFrom">Dari</label>
                            <input type="date" id="historyFrom" class="form-control" value="{{ date('Y-m-d', strtotime('-6 days')) }}"
                                onchange="reloadHistory()">
                        </div>
                        <div class="mon-field">
                            <label for="historyTo">Sampai</label>
                            <input type="date" id="historyTo" class="form-control" value="{{ date('Y-m-d') }}"
                                onchange="reloadHistory()">
                        </div>
                        <div class="mon-field">
                            <label for="historyStatus">Aksi</label>
                            <select id="historyStatus" class="form-select" onchange="reloadHistory()">
                                <option value="">Semua aksi</option>
                                @foreach ($statusLabels as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mon-field">
                            <label for="historyTipe">Tipe</label>
                            <select id="historyTipe" class="form-select" onchange="reloadHistory()">
                                <option value="">Semua tipe</option>
                                <option value="SINGLE">Single</option>
                                <option value="BULK">Bulk</option>
                                <option value="OPNAME">Koreksi Opname</option>
                                <option value="MASTER">Master Tab</option>
                            </select>
                        </div>
                        <div class="mon-field is-grow">
                            <label for="historySearch">Cari</label>
                            <input type="text" id="historySearch" class="form-control" placeholder="Tab, RFID, nama, enroll ID, keterangan...">
                        </div>
                        <button type="button" class="btn btn-outline-success mon-export" onclick="exportHistory()">
                            <i class="fa-solid fa-file-excel"></i> Export
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table id="tableHistory" class="table table-hover mon-table">
                            <thead>
                                <tr>
                                    <th>Waktu</th>
                                    <th>Aksi</th>
                                    <th>Tab</th>
                                    <th>Karyawan</th>
                                    <th>Tujuan</th>
                                    <th>Keterangan</th>
                                    <th>Oleh</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('custom-script')
    <!-- DataTables -->
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>

    <script>
        // Modul Asset: senyapkan alert bawaan DataTables saat ajax gagal, cukup dicatat di console
        $.fn.dataTable.ext.errMode = function (settings, techNote, message) {
            console.error('DataTable ajax error:', message);
        };

        const STATUS_LABELS = @json($statusLabels);
        const STATUS_BADGES = { AMBIL: 'is-ambil', KEMBALI: 'is-kembali', REPAIR: 'is-repair', SELESAI_REPAIR: 'is-selesai' };
        const TIPE_LABELS = { SINGLE: 'Single', BULK: 'Bulk', OPNAME: 'Koreksi opname', MASTER: 'Master Tab' };
        const LANGUAGE = {
            emptyTable: 'Tidak ada data',
            zeroRecords: 'Tidak ada data yang cocok',
            info: '_START_–_END_ dari _TOTAL_ data',
            infoEmpty: '0 data',
            infoFiltered: '(disaring dari _MAX_)',
            processing: '<i class="fa-solid fa-spinner fa-spin"></i> Memuat...',
            paginate: { previous: '‹', next: '›' },
        };

        const esc = (s) => $('<div>').text(s == null ? '' : String(s)).html();

        function durationText(minutes) {
            if (minutes == null) return '-';
            if (minutes < 60) return minutes + ' menit';
            if (minutes < 1440) return Math.floor(minutes / 60) + ' jam ' + (minutes % 60) + ' mnt';
            return Math.floor(minutes / 1440) + ' hari ' + Math.floor((minutes % 1440) / 60) + ' jam';
        }

        function tabCell(row) {
            return `<b>${esc(row.tab_code || '-')}</b>${row.line_code ? ' <span class="text-muted">· ' + esc(row.line_code) + '</span>' : ''}` +
                `<span class="mon-sub">${esc(row.rfid_code)}</span>`;
        }

        function employeeCell(row, emptyText) {
            if (row.employee_name) return `<b>${esc(row.employee_name)}</b><span class="mon-sub">ID ${esc(row.enroll_id)}</span>`;
            if (row.enroll_id) return `ID ${esc(row.enroll_id)}`;
            return `<span class="text-muted">${emptyText}</span>`;
        }

        // ---------- Sedang dibawa ----------
        let tableTaken = $('#tableTaken').DataTable({
            ordering: false,
            processing: true,
            pageLength: 10,
            dom: 'rtip',
            language: LANGUAGE,
            ajax: {
                url: '{{ route('taken_monitoring_tab') }}',
                data: (d) => {
                    d.tujuan = $('#takenTujuan').val();
                    d.overdue = $('#takenOverdue').is(':checked') ? 1 : 0;
                },
            },
            createdRow: (tr, row) => $(tr).toggleClass('is-overdue', !!row.overdue),
            columns: [
                { data: null, render: (d, t, row) => tabCell(row) },
                {
                    data: null,
                    render: (d, t, row) => employeeCell(row, row.taken_at ? 'Bulk (tanpa NIK)' : 'Tidak ada riwayat ambil'),
                },
                { data: 'tujuan', render: (d) => esc(d || '-') },
                { data: 'taken_at', render: (d) => esc(d || '-') },
                { data: 'menit', render: (d) => durationText(d) },
                {
                    data: 'overdue',
                    render: (d) => d ?
                        '<span class="mon-badge is-late"><i class="fa-solid fa-triangle-exclamation"></i> Belum kembali</span>' :
                        '<span class="mon-badge is-ok"><i class="fa-solid fa-circle-check"></i> Normal</span>',
                },
            ],
        });

        function reloadTaken() {
            tableTaken.ajax.reload();
        }

        $('#takenSearch').on('input', function () {
            tableTaken.search(this.value).draw();
        });

        // Lama dibawa terus bertambah, jadi daftar diperbarui tiap menit selama panel ini dibuka
        setInterval(function () {
            if (!$('#panelTaken').hasClass('d-none')) tableTaken.ajax.reload(null, false);
        }, 60000);

        function exportTaken() {
            window.location = '{{ route('export_taken_monitoring_tab') }}?' + $.param({
                tujuan: $('#takenTujuan').val(),
                overdue: $('#takenOverdue').is(':checked') ? 1 : 0,
            });
        }

        // ---------- Riwayat ----------
        let tableHistory = null;

        function historyFilters() {
            return {
                from: $('#historyFrom').val(),
                to: $('#historyTo').val(),
                status: $('#historyStatus').val(),
                tipe: $('#historyTipe').val(),
            };
        }

        // Dibuat saat panel Riwayat pertama kali dibuka supaya halaman awal tidak memuat riwayat yang belum tentu dilihat
        function initHistoryTable() {
            tableHistory = $('#tableHistory').DataTable({
                ordering: false,
                processing: true,
                serverSide: true,
                pageLength: 10,
                dom: 'rtip',
                language: LANGUAGE,
                ajax: {
                    url: '{{ route('history_monitoring_tab') }}',
                    data: (d) => Object.assign(d, historyFilters()),
                },
                columns: [
                    { data: 'created_at', render: (d) => esc(String(d || '').substring(0, 16)) },
                    {
                        data: 'status',
                        render: (d, t, row) => `<span class="mon-badge ${STATUS_BADGES[d] || 'is-muted'}">${esc(STATUS_LABELS[d] || d)}</span>` +
                            (row.tipe_input ? `<span class="mon-sub">${esc(TIPE_LABELS[row.tipe_input] || row.tipe_input)}</span>` : ''),
                    },
                    { data: null, render: (d, t, row) => tabCell(row) },
                    { data: null, render: (d, t, row) => employeeCell(row, '-') },
                    { data: 'tujuan', render: (d) => esc(d || '-') },
                    { data: 'keterangan', render: (d) => d ? esc(d) : '<span class="text-muted">-</span>' },
                    { data: 'created_by', render: (d) => esc(d || '-') },
                ],
            });

            let searchTimer = null;
            $('#historySearch').on('input', function () {
                clearTimeout(searchTimer);
                let value = this.value;
                searchTimer = setTimeout(() => tableHistory.search(value).draw(), 400);
            });
        }

        function reloadHistory() {
            if (tableHistory) tableHistory.ajax.reload();
        }

        function exportHistory() {
            window.location = '{{ route('export_history_monitoring_tab') }}?' + $.param(Object.assign(historyFilters(), {
                q: $('#historySearch').val(),
            }));
        }

        function showPanel(panel) {
            $('#panelTaken').toggleClass('d-none', panel !== 'taken');
            $('#panelHistory').toggleClass('d-none', panel !== 'history');

            if (panel === 'history' && !tableHistory) initHistoryTable();
        }
    </script>
@endsection
