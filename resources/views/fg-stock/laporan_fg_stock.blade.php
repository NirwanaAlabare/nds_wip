@extends('layouts.index')

@section('custom-link')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-fixedcolumns/css/fixedColumns.bootstrap4.min.css') }}">

    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

    <style>
        /* ================= Filter Panel ================= */
        .filter-panel {
            background: #fff;
            border: 1px solid #e9ecef;
            border-left: 3px solid var(--sb-color);
            border-radius: .65rem;
            box-shadow: 0 2px 10px rgba(8, 33, 73, .06);
            padding: 1.1rem 1.25rem 1.25rem;
        }

        .filter-panel__title {
            display: flex;
            align-items: center;
            gap: .5rem;
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: var(--sb-color);
            padding-bottom: .65rem;
            margin-bottom: 1rem;
            border-bottom: 1px dashed #e9ecef;
        }

        .filter-panel .form-label {
            font-size: .7rem;
            font-weight: 700;
            letter-spacing: .05em;
            text-transform: uppercase;
            color: #6c757d;
            margin-bottom: .35rem;
        }

        .filter-panel .form-control,
        .filter-panel .input-group-text {
            border-radius: .5rem;
            border-color: #dee2e6;
        }

        .filter-panel .input-group-text {
            background-color: #f8f9fa;
            color: var(--sb-color);
        }

        .filter-panel .input-group .form-control {
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
        }

        .filter-panel .input-group .input-group-text {
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
        }

        .filter-panel .form-control:focus {
            border-color: var(--sb-color);
            box-shadow: 0 0 0 .2rem rgba(8, 33, 73, .12);
        }

        .filter-panel .btn {
            border-radius: .5rem;
            font-size: .85rem;
            font-weight: 600;
            padding: .45rem 1.1rem;
        }

        .btn-filter-primary {
            background-color: var(--sb-color);
            border-color: var(--sb-color);
            color: #fff;
        }

        .filter-panel .btn.disabled {
            opacity: .65;
            pointer-events: none;
        }

        .btn-filter-primary:hover,
        .btn-filter-primary:focus {
            background-color: #0b2f66;
            border-color: #0b2f66;
            color: #fff;
        }

        /* ================= Select2 (bootstrap4 theme) ================= */
        .select2-container--bootstrap4 .select2-selection {
            border-radius: .5rem;
            border-color: #dee2e6;
        }

        .select2-container--bootstrap4.select2-container--focus .select2-selection,
        .select2-container--bootstrap4.select2-container--open .select2-selection {
            border-color: var(--sb-color);
            box-shadow: 0 0 0 .2rem rgba(8, 33, 73, .12);
        }

        .select2-container--bootstrap4 .select2-dropdown {
            border-radius: .5rem;
            border-color: #dee2e6;
            box-shadow: 0 6px 18px rgba(8, 33, 73, .12);
        }

        .select2-container--bootstrap4 .select2-results__option--highlighted {
            background-color: var(--sb-color);
        }

        /* ================= Tabel ================= */
        .table-card {
            border: 1px solid #e9ecef;
            border-radius: .65rem;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(8, 33, 73, .05);
        }

        .table thead th {
            vertical-align: middle;
            text-align: center;
            white-space: nowrap;
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
            border-bottom-width: 1px;
        }

        .table tbody td {
            font-size: .82rem;
            vertical-align: middle;
        }

        /* Perataan sel dikendalikan dari konfigurasi kolom (lihat REPORTS) */
        .table tbody td.dt-center {
            text-align: center;
        }

        .table tbody td.dt-num {
            text-align: right;
            font-variant-numeric: tabular-nums;
        }

        .tbl-dark-head thead th {
            background-color: var(--sb-color);
            color: #fff;
            border-color: rgba(255, 255, 255, .15);
        }

        /* ikon sorting DataTables agar terlihat di header gelap */
        .tbl-dark-head thead th.sorting:after,
        .tbl-dark-head thead th.sorting:before {
            color: rgba(255, 255, 255, .65);
        }

        /* ===== Header tabel tetap terlihat saat body digulir ===== */
        .table-card .dataTables_scrollHead {
            /* header tidak ikut tergulir vertikal, hanya mengikuti scroll horizontal body */
            border-bottom: 1px solid rgba(0, 0, 0, .08);
        }

        .table-card .dataTables_scrollHead table {
            margin-bottom: 0 !important;
        }

        .table-card .dataTables_scrollBody {
            /* garis atas bawaan DataTables dihilangkan agar menyatu dengan header */
            border-top: 0 !important;
        }

        .table-card .dataTables_scrollBody table {
            margin-top: 0 !important;
        }

        /* header grup Mutasi Global ikut menempel juga */
        .table-card .dataTables_scrollHead .th-group-key,
        .table-card .dataTables_scrollHead .th-group-transit,
        .table-card .dataTables_scrollHead .th-group-gudang {
            border-color: rgba(0, 0, 0, .08);
        }

        .table-card .dataTables_wrapper .row:first-child,
        .table-card .dataTables_wrapper .row:last-child {
            padding: .75rem 1rem 0;
        }

        .table-card .dataTables_wrapper .row:last-child {
            padding: .5rem 1rem .75rem;
        }

        .th-group-key {
            background-color: #e7ecf5 !important;
            color: var(--sb-color) !important;
        }

        .th-group-transit {
            background-color: #e3f3e8 !important;
            color: #1c5c33 !important;
        }

        .th-group-gudang {
            background-color: #fdf3d6 !important;
            color: #7a5b00 !important;
        }

        /* ===== Kolom beku (Mutasi Global) ===== */
        /* Sel beku harus punya latar solid, kalau tidak baris di baliknya akan tembus */
        #datatable_mutasi_global tbody .dtfc-fixed-left {
            background-color: #fff;
        }

        #datatable_mutasi_global tbody tr.odd .dtfc-fixed-left {
            background-color: #f7f8fa;
        }

        #datatable_mutasi_global tbody tr:hover .dtfc-fixed-left {
            background-color: #eef2f8;
        }

        /* Garis pemisah antara area beku dan area yang bergulir */
        .table-card .dtfc-fixed-left:last-of-type {
            border-right: 2px solid rgba(8, 33, 73, .18) !important;
        }

        /* ===== Indikator loading ===== */
        .table-card {
            position: relative;
        }

        /* Kolom beku memakai z-index 1, jadi indikator harus di atasnya */
        .table-card .dataTables_processing {
            z-index: 30;
            width: auto !important;
            margin-left: 0 !important;
            margin-top: 0 !important;
            left: 50% !important;
            top: 50% !important;
            transform: translate(-50%, -50%);
            padding: 0 !important;
            background: transparent !important;
            border: 0 !important;
            box-shadow: none !important;
        }

        .dt-loader {
            display: inline-flex;
            align-items: center;
            gap: .65rem;
            padding: .6rem 1.1rem;
            background: #fff;
            border: 1px solid #e9ecef;
            border-radius: 2rem;
            box-shadow: 0 4px 16px rgba(8, 33, 73, .16);
            font-size: .82rem;
            font-weight: 600;
            color: var(--sb-color);
            white-space: nowrap;
        }

        .dt-loader__spinner {
            width: 1.05rem;
            height: 1.05rem;
            border: 2px solid rgba(8, 33, 73, .18);
            border-top-color: var(--sb-color);
            border-radius: 50%;
            animation: dt-spin .7s linear infinite;
        }

        @keyframes dt-spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* Redupkan isi tabel selama memuat supaya jelas datanya sedang diganti */
        .table-card.is-loading .dataTables_scrollBody,
        .table-card.is-loading .dataTables_scrollHead {
            opacity: .35;
            transition: opacity .15s ease;
            pointer-events: none;
        }

        .table-card .dataTables_scrollBody,
        .table-card .dataTables_scrollHead {
            transition: opacity .15s ease;
        }

        .empty-state {
            padding: 3rem 1rem;
            text-align: center;
            color: #adb5bd;
        }

        .empty-state i {
            font-size: 2.5rem;
            margin-bottom: .75rem;
            display: block;
        }
    </style>
@endsection

@section('content')
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-list"></i> List Laporan</h5>
        </div>
        <div class="card-body">
            <div class="filter-panel mb-4">
                <div class="filter-panel__title">
                    <i class="fas fa-sliders-h"></i> Filter Laporan
                </div>
                <div class="row g-3 align-items-end">
                    <div class="col-xl-4 col-lg-5 col-md-12">
                        <label class="form-label" for="cbojns_lap">Jenis Laporan</label>
                        <select class="form-control select2bs4" id="cbojns_lap" name="cbojns_lap"
                            style="width: 100%;">
                            <option selected="selected" value="" disabled="true">- Pilih Jenis Laporan -</option>
                            @foreach ($data_laporan as $datalaporan)
                                <option value="{{ $datalaporan->isi }}">
                                    {{ $datalaporan->tampil }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-xl-2 col-lg-3 col-md-6">
                        <label class="form-label" for="tgl-awal">Tgl Awal</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
                            <input type="date" class="form-control" id="tgl-awal" name="tgl_awal"
                                value="{{ date('Y-m-d') }}">
                        </div>
                    </div>
                    <div class="col-xl-2 col-lg-3 col-md-6">
                        <label class="form-label" for="tgl-akhir">Tgl Akhir</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
                            <input type="date" class="form-control" id="tgl-akhir" name="tgl_akhir"
                                value="{{ date('Y-m-d') }}">
                        </div>
                    </div>
                    <div class="col-xl-4 col-lg-12">
                        <div class="d-flex gap-2 flex-wrap justify-content-xl-end">
                            <a onclick="cari()" id="btn-cari" class="btn btn-filter-primary" style="cursor: pointer;">
                                <i class="fas fa-search me-1"></i> <span>Tampilkan</span>
                            </a>
                            <a onclick="export_excel()" class="btn btn-outline-success" style="cursor: pointer;">
                                <i class="fas fa-file-excel me-1"></i> Export Excel
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div id="empty-state" class="empty-state">
                <i class="fas fa-folder-open"></i>
                Pilih jenis laporan lalu klik <b>Tampilkan</b> untuk melihat data.
            </div>

            <!-- Semua tabel dibangun dari konfigurasi REPORTS, lihat custom-script -->
            <div id="tables-container"></div>
        </div>
    </div>
@endsection

@section('custom-script')
    <!-- DataTables & Plugins -->
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-fixedcolumns/js/dataTables.fixedColumns.min.js') }}"></script>
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <script src="{{ asset('plugins/export_excel_js/exceljs.min.js') }}"></script>
    <script>
        // Select2 Autofocus
        $(document).on('select2:open', () => {
            document.querySelector('.select2-search__field').focus();
        });

        // Initialize Select2 Elements
        $('.select2').select2();

        // Initialize Select2BS4 Elements
        $('.select2bs4').select2({
            theme: 'bootstrap4',
            width: '100%',
            placeholder: '- Pilih Jenis Laporan -',
            allowClear: true,
        });
    </script>
    <script>
        // ============ Konfigurasi tiap jenis laporan ============
        //
        // Satu-satunya tempat yang perlu diubah kalau ada kolom baru / judul berubah:
        //   data  -> nama field pada JSON dari controller (WAJIB sama dengan alias di SQL)
        //   title -> judul kolom yang tampil di header
        //   num   -> true kalau kolom angka (rata kanan + pemisah ribuan)
        //   group -> nama grup header bertingkat, lihat COLUMN_GROUPS
        // Markup <table> dan <thead> dibuat otomatis dari daftar ini.

        // Grup header bertingkat (dipakai Mutasi Global)
        const COLUMN_GROUPS = {
            transit: {
                label: 'Transit Terima Gudang Stok',
                cls: 'th-group-transit'
            },
            gudang: {
                label: 'Gudang Stok',
                cls: 'th-group-gudang'
            },
        };

        const REPORTS = {
            'Penerimaan': {
                slug: 'penerimaan',
                url: '{{ route('get-data-penerimaan-laporan-fg-stock') }}',
                export: {
                    url: '{{ route('export-penerimaan-laporan-fg-stock') }}',
                    filename: 'Laporan Penerimaan FG Stock.xlsx',
                },
                columns: [
                    { data: 'no_trans', title: 'No. Trans' },
                    { data: 'tgl_terima_fix', title: 'Tgl. Trans' },
                    { data: 'lokasi', title: 'Lokasi' },
                    { data: 'no_carton', title: 'No. Karton' },
                    { data: 'buyer', title: 'Buyer' },
                    { data: 'brand', title: 'Brand' },
                    { data: 'styleno', title: 'Style' },
                    { data: 'grade', title: 'Grade' },
                    { data: 'ws', title: 'WS' },
                    { data: 'color', title: 'Color' },
                    { data: 'size', title: 'Size' },
                    { data: 'qty', title: 'Qty', num: true },
                    { data: 'sumber_pemasukan', title: 'Sumber' },
                ],
            },
            'Pengeluaran': {
                slug: 'pengeluaran',
                url: '{{ route('bppb-fg-stock') }}',
                export: {
                    url: '{{ route('export_excel_bppb_fg_stok') }}',
                    filename: 'Laporan Pengeluaran FG Stock.xlsx',
                },
                columns: [
                    { data: 'no_trans_out', title: 'No. Trans' },
                    { data: 'tgl_pengeluaran_fix', title: 'Tgl. Trans' },
                    { data: 'lokasi', title: 'Lokasi' },
                    { data: 'no_carton', title: 'No. Karton' },
                    { data: 'buyer', title: 'Buyer' },
                    { data: 'brand', title: 'Brand' },
                    { data: 'styleno', title: 'Style' },
                    { data: 'grade', title: 'Grade' },
                    { data: 'ws', title: 'WS' },
                    { data: 'color', title: 'Color' },
                    { data: 'size', title: 'Size' },
                    { data: 'qty_out', title: 'Qty', num: true },
                    { data: 'tujuan', title: 'Tujuan' },
                    { data: 'tujuan_pengeluaran', title: 'Tujuan Pengeluaran' },
                ],
            },
            'Mutasi': {
                slug: 'mutasi',
                url: '{{ route('rep_mutasi_fg_stock') }}',
                columns: [
                    { data: 'id_so_det', title: 'ID So Det' },
                    { data: 'buyer', title: 'Buyer' },
                    { data: 'product_group', title: 'Product Group' },
                    { data: 'product_item', title: 'Product Item' },
                    { data: 'ws', title: 'WS' },
                    { data: 'brand', title: 'Brand' },
                    { data: 'styleno', title: 'Style' },
                    { data: 'color', title: 'Color' },
                    { data: 'size', title: 'Size' },
                    { data: 'dest', title: 'Dest' },
                    { data: 'grade', title: 'Grade' },
                    { data: 'no_carton', title: 'No. Carton' },
                    { data: 'lokasi', title: 'Lokasi' },
                    { data: 'qty_awal', title: 'Saldo Awal', num: true },
                    { data: 'qty_in', title: 'Penerimaan', num: true },
                    { data: 'qty_out', title: 'Pengeluaran', num: true },
                    { data: 'saldo_akhir', title: 'Saldo Akhir', num: true },
                ],
            },
            'Mutasi Global': {
                slug: 'mutasi_global',
                url: '{{ route('rep_mutasi_global_fg_stock') }}',
                export: {
                    url: '{{ route('export_excel_rep_mutasi_global_fg_stock') }}',
                    filename: 'Laporan Mutasi FG Stock Global.xlsx',
                },
                // Header gelap dimatikan karena tabel ini memakai header grup berwarna
                darkHead: false,
                keyClass: 'th-group-key',
                // Buyer, WS, Style, Color, Size ikut tergeser saat scroll horizontal
                fixedLeft: 5,
                // Data mutasi global baru tersedia mulai tanggal ini
                minDate: '2026-05-01',
                columns: [
                    { data: 'buyer', title: 'Buyer' },
                    { data: 'ws', title: 'WS' },
                    { data: 'styleno', title: 'Style' },
                    { data: 'color', title: 'Color' },
                    { data: 'size', title: 'Size' },
                    { data: 'saldo_awal_transit', title: 'Saldo Awal', group: 'transit', num: true },
                    { data: 'qty_in_qc_reject', title: 'In Qc Reject', group: 'transit', num: true },
                    { data: 'qty_in_ekspedisi', title: 'In Ekspedisi', group: 'transit', num: true },
                    { data: 'qty_out_qc_reject', title: 'Out Qc Reject', group: 'transit', num: true },
                    { data: 'qty_out_ekspedisi', title: 'Out Ekspedisi', group: 'transit', num: true },
                    { data: 'qty_adjustment', title: 'Adjustment', group: 'transit', num: true },
                    { data: 'saldo_akhir_transit', title: 'Saldo Akhir', group: 'transit', num: true },
                    { data: 'saldo_awal_gudang_stok', title: 'Saldo Awal', group: 'gudang', num: true },
                    { data: 'qty_terima_qc_reject', title: 'Terima Qc Reject', group: 'gudang', num: true },
                    { data: 'qty_terima_ekspedisi', title: 'Terima Ekspedisi', group: 'gudang', num: true },
                    { data: 'qty_keluar_sewing', title: 'Keluar Sewing', group: 'gudang', num: true },
                    { data: 'qty_keluar_qa', title: 'Keluar QA', group: 'gudang', num: true },
                    { data: 'qty_keluar_ekspedisi', title: 'Keluar Ekspedisi', group: 'gudang', num: true },
                    { data: 'saldo_akhir_gudang_stok', title: 'Saldo Akhir', group: 'gudang', num: true },
                ],
            },
        };

        // Selector tabel & pembungkusnya diturunkan dari slug, tidak perlu ditulis manual
        $.each(REPORTS, function(jenis, cfg) {
            cfg.wrapper = '#table_' + cfg.slug;
            cfg.table = '#datatable_' + cfg.slug;
        });

        // ============ Pembuatan markup tabel dari konfigurasi ============

        function fmtNumber(value) {
            if (value === null || value === '' || typeof value === 'undefined') {
                return '';
            }

            const n = Number(value);

            return isNaN(n) ? value : n.toLocaleString('id-ID');
        }

        function th(label, cls, attrs) {
            return '<th class="text-center ' + (cls || '') + '"' + (attrs || '') + '>' + label + '</th>';
        }

        // Header satu baris, atau dua baris kalau ada kolom yang punya `group`
        function buildHead(cfg) {
            const cols = cfg.columns;

            if (!cols.some(function(c) { return c.group; })) {
                return '<tr>' + cols.map(function(c) { return th(c.title); }).join('') + '</tr>';
            }

            let top = '';
            let bottom = '';
            let i = 0;

            while (i < cols.length) {
                // Kolom tanpa grup memanjang ke bawah menutupi dua baris header
                if (!cols[i].group) {
                    top += th(cols[i].title, cfg.keyClass, ' rowspan="2"');
                    i++;
                    continue;
                }

                // Kolom bergrup yang berurutan digabung, colspan dihitung otomatis
                const name = cols[i].group;
                const meta = COLUMN_GROUPS[name] || { label: name, cls: '' };
                let span = 0;

                while (i + span < cols.length && cols[i + span].group === name) {
                    span++;
                }

                top += th(meta.label, meta.cls, ' colspan="' + span + '"');

                for (let j = i; j < i + span; j++) {
                    bottom += th(cols[j].title, meta.cls);
                }

                i += span;
            }

            return '<tr>' + top + '</tr><tr>' + bottom + '</tr>';
        }

        // Semua <div class="table-card"> + <table> + <thead> dibuat di sini
        function renderTables() {
            const html = $.map(REPORTS, function(cfg, jenis) {
                const tableClass =
                    'table table-bordered table-striped table-hover table-sm display nowrap w-100' +
                    (cfg.darkHead === false ? '' : ' tbl-dark-head');

                return '<div class="table-card mt-3" id="table_' + cfg.slug + '" style="display: none;">' +
                    '<table id="datatable_' + cfg.slug + '" class="' + tableClass + '">' +
                    '<thead>' + buildHead(cfg) + '</thead>' +
                    '</table>' +
                    '</div>';
            }).join('');

            $('#tables-container').html(html);
        }

        // Tabel diinisialisasi sekali saja (lazy), pencarian berikutnya cukup reload ajax
        const dtInstances = {};

        // Error ajax ditangani sendiri, jangan pakai alert bawaan DataTables
        $.fn.dataTable.ext.errMode = 'none';

        function setButtonLoading(loading) {
            const $btn = $('#btn-cari');

            if (loading) {
                $btn.addClass('disabled')
                    .find('i').removeClass('fa-search').addClass('fa-circle-notch fa-spin');
                $btn.find('span').text('Memuat...');
            } else {
                $btn.removeClass('disabled')
                    .find('i').removeClass('fa-circle-notch fa-spin').addClass('fa-search');
                $btn.find('span').text('Tampilkan');
            }
        }

        function buildTable(jenis) {
            const cfg = REPORTS[jenis];

            const options = {
                ordering: false,
                processing: true,
                serverSide: true,
                paging: true,
                searching: true,
                scrollX: true,
                scrollY: '55vh',
                scrollCollapse: true,
                deferRender: true,
                searchDelay: 600,
                pageLength: 25,
                lengthMenu: [
                    [10, 25, 50, 100],
                    [10, 25, 50, 100]
                ],
                ajax: {
                    url: cfg.url,
                    data: function(d) {
                        d.dateFrom = $('#tgl-awal').val();
                        d.dateTo = $('#tgl-akhir').val();
                    },
                },
                // Perataan & format angka mengikuti flag `num` pada konfigurasi kolom
                columns: cfg.columns.map(function(c) {
                    return {
                        data: c.data,
                        className: c.num ? 'dt-num' : 'dt-center',
                        render: c.num ? fmtNumber : null,
                    };
                }),
                language: {
                    processing: '<div class="dt-loader"><div class="dt-loader__spinner"></div>' +
                        '<span>Memuat data...</span></div>',
                    zeroRecords: 'Tidak ada data pada rentang tanggal ini',
                    emptyTable: 'Tidak ada data pada rentang tanggal ini',
                    info: 'Menampilkan _START_ - _END_ dari _TOTAL_ baris',
                    infoEmpty: 'Menampilkan 0 baris',
                    infoFiltered: '(disaring dari _MAX_ baris)',
                    lengthMenu: 'Tampilkan _MENU_ baris',
                    search: 'Cari:',
                    paginate: {
                        first: 'Awal',
                        last: 'Akhir',
                        next: 'Berikutnya',
                        previous: 'Sebelumnya'
                    }
                },
            };

            if (cfg.fixedLeft) {
                options.fixedColumns = {
                    left: cfg.fixedLeft
                };
            }

            const dt = $(cfg.table).DataTable(options);

            // Redupkan tabel selama request berjalan
            $(cfg.table).on('processing.dt', function(e, settings, isProcessing) {
                $(cfg.wrapper).toggleClass('is-loading', isProcessing);
                setButtonLoading(isProcessing);
            });

            $(cfg.table).on('error.dt', function(e, settings, techNote, message) {
                const xhr = settings.jqXHR;
                const status = xhr ? xhr.status : 0;
                const body = xhr && xhr.responseText ? xhr.responseText : '';

                // Ambil pesan error Laravel kalau responsnya JSON, kalau HTML ambil <title>
                let detail = '';
                try {
                    detail = JSON.parse(body).message || '';
                } catch (err) {
                    const m = body.match(/<title>(.*?)<\/title>/i);
                    detail = m ? m[1] : body.substring(0, 300);
                }

                let title = 'Gagal Memuat Data';
                let text;

                if (status === 0) {
                    text = 'Koneksi ke server terputus atau permintaan kehabisan waktu. Coba persempit rentang tanggal.';
                } else if (status === 419) {
                    title = 'Sesi Berakhir';
                    text = 'Sesi login sudah kedaluwarsa. Silakan muat ulang halaman dan login kembali.';
                } else if (status === 500) {
                    text = 'Terjadi error di server (500). Detail: ' + (detail || 'tidak tersedia');
                } else if (status === 200) {
                    text = 'Server membalas dengan format yang tidak dikenali. Detail: ' + (detail ||
                        '(respons kosong)');
                } else {
                    text = 'Server membalas dengan status ' + status + '. Detail: ' + (detail ||
                        'tidak tersedia');
                }

                $(cfg.wrapper).removeClass('is-loading');
                setButtonLoading(false);

                Swal.close();
                Swal.fire({
                    title: title,
                    text: text,
                    icon: 'error',
                    confirmButtonText: 'OK',
                });

                console.error('DataTables error', {
                    jenis: jenis,
                    url: cfg.url,
                    status: status,
                    techNote: techNote,
                    message: message,
                    response: body,
                });
            });

            return dt;
        }

        $(document).ready(function() {
            renderTables();

            $('#cbojns_lap').val('').trigger('change');

            $('#cbojns_lap').change(function() {
                const selectedValue = $(this).val();

                // Sembunyikan semua tabel, tampilkan yang dipilih saja
                $.each(REPORTS, function(jenis, cfg) {
                    $(cfg.wrapper).toggle(jenis === selectedValue);
                });

                $('#empty-state').toggle(!selectedValue);

                // Ratakan ulang lebar kolom karena tabel baru saja ditampilkan
                if (selectedValue && dtInstances[selectedValue]) {
                    dtInstances[selectedValue].columns.adjust();
                }
            });

            // Batas tanggal minimal diambil dari `minDate` pada konfigurasi laporan
            $('#tgl-awal').change(function() {
                const cfg = REPORTS[$('#cbojns_lap').val()];

                if (!cfg || !cfg.minDate || $(this).val() >= cfg.minDate) {
                    return;
                }

                Swal.fire({
                    title: 'Perhatian!',
                    text: 'Tanggal awal ' + $('#cbojns_lap').val() + ' minimal ' + cfg.minDate,
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });

                $(this).val(cfg.minDate);
            });
        });

        function cari() {
            if ($('#btn-cari').hasClass('disabled')) {
                return;
            }

            const jenis = $('#cbojns_lap').val();
            const from = $('#tgl-awal').val();
            const to = $('#tgl-akhir').val();

            if (!jenis) {
                Swal.fire({
                    title: 'Jenis Laporan Belum Di isi!',
                    icon: 'warning',
                    showConfirmButton: true,
                });
                return;
            }

            if (from && to && from > to) {
                Swal.fire({
                    title: 'Rentang Tanggal Tidak Valid!',
                    text: 'Tgl Awal tidak boleh lebih besar dari Tgl Akhir.',
                    icon: 'warning',
                    showConfirmButton: true,
                });
                return;
            }

            if (!REPORTS[jenis]) return;

            // Bangun sekali, selanjutnya cukup muat ulang datanya
            if (!dtInstances[jenis]) {
                dtInstances[jenis] = buildTable(jenis);
            } else {
                dtInstances[jenis].ajax.reload();
            }
        }

        function notif() {
            alert("Maaf, Fitur belum tersedia!");
        }

        // Unduh file export dari server, dengan penanganan error supaya loader tidak menggantung
        function downloadExport(url, from, to, filename) {
            $.ajax({
                type: 'get',
                url: url,
                data: {
                    from: from,
                    to: to
                },
                xhrFields: {
                    responseType: 'blob'
                },
                success: function(response) {
                    Swal.close();

                    const blobUrl = window.URL.createObjectURL(new Blob([response]));
                    const link = document.createElement('a');
                    link.href = blobUrl;
                    link.download = from + ' sampai ' + to + ' ' + filename;
                    link.click();
                    // Bebaskan memori blob setelah unduhan dipicu
                    setTimeout(function() {
                        window.URL.revokeObjectURL(blobUrl);
                    }, 1000);

                    Swal.fire({
                        title: 'Data Sudah Di Export!',
                        icon: 'success',
                        showConfirmButton: true,
                    });
                },
                error: function() {
                    Swal.close();
                    Swal.fire({
                        title: 'Export Gagal',
                        text: 'Data gagal diexport. Rentang tanggal kemungkinan terlalu lebar. Coba persempit rentang tanggalnya.',
                        icon: 'error',
                        confirmButtonText: 'OK',
                    });
                },
            });
        }

        function export_excel() {
            const from = document.getElementById("tgl-awal").value;
            const to = document.getElementById("tgl-akhir").value;
            const jenis = document.getElementById("cbojns_lap").value;
            const cfg = REPORTS[jenis];

            if (!jenis) {
                Swal.fire({
                    title: 'Jenis Laporan Belum Di isi!',
                    icon: "warning",
                    showConfirmButton: true,
                });
                return;
            }

            Swal.fire({
                title: 'Please Wait...',
                html: 'Exporting Data...',
                didOpen: () => {
                    Swal.showLoading()
                },
                allowOutsideClick: false,
            });

            // Mutasi dirakit di browser, laporan lain filenya sudah jadi dari server
            if (jenis === 'Mutasi') {
                exportMutasiExcel(from, to);
            } else if (cfg && cfg.export) {
                downloadExport(cfg.export.url, from, to, cfg.export.filename);
            } else {
                Swal.close();
            }
        }

        // Export Mutasi disusun di sisi klien memakai ExcelJS
        function exportMutasiExcel(from, to) {
            const startTime = new Date().getTime();
            // Fetch all data from the server
            $.ajax({
                type: "POST",
                url: '{{ route('export_excel_mutasi_fg_stock') }}',
                data: {
                    from: from,
                    to: to
                },
                success: function(data) {
                    // Create a new workbook and a worksheet
                    const workbook = new ExcelJS.Workbook();
                    const worksheet = workbook.addWorksheet("Mutasi FG Stock ");

                    // Add a main title row above the Tgl Transaksi
                    const mainTitleRow = worksheet.addRow(["Laporan Mutasi FG Stock"]);
                    // Center align the main title row
                    worksheet.getCell(`A${mainTitleRow.number}`).alignment = {
                        horizontal: 'center',
                        vertical: 'middle'
                    };
                    // Optionally, you can merge cells for the main title
                    worksheet.mergeCells(`A${mainTitleRow.number}:E${mainTitleRow.number}`);
                    mainTitleRow.font = {
                        bold: true,
                        size: 14
                    };

                    // Add a title row for Tgl Transaksi without borders
                    const titleRow = worksheet.addRow([`Tgl Transaksi: ${from} - ${to}`]);
                    // Center align the title row
                    worksheet.getCell(`A${titleRow.number}`).alignment = {
                        horizontal: 'center',
                        vertical: 'middle'
                    };
                    // Set border to null for the title row
                    titleRow.eachCell((cell) => {
                        cell.border = null;
                    });

                    worksheet.addRow([]);
                    const headers = [
                        "No", "ID SO Det", "Buyer", "Product Group", "Product Item",
                        "WS", "Brand", "Style",
                        "Color", "Size", "Dest",
                        "Grade", "No. Carton", "Lokasi",
                        "Saldo Awal", "Penerimaan", "Pengeluaran", "Saldo Akhir",
                    ];

                    const headerRow = worksheet.addRow(headers);


                    // Set background color for header row

                    headerRow.eachCell((cell) => {

                        cell.fill = {

                            type: 'pattern',

                            pattern: 'solid',

                            fgColor: {
                                argb: 'FFFF00'
                            }, // Yellow background

                            bgColor: {
                                argb: 'FFFF00'
                            } // Optional: set background color

                        };

                        cell.font = {

                            bold: true

                        };

                    });

                    // Add data rows
                    data.forEach(function(row) {
                        worksheet.addRow([
                            row.no_urut,
                            row.id_so_det,
                            row.buyer,
                            row.product_group,
                            row.product_item,
                            row.ws,
                            row.brand,
                            row.styleno,
                            row.color,
                            row.size,
                            row.dest,
                            row.grade,
                            row.no_carton,
                            row.lokasi,
                            row.qty_awal,
                            row.qty_in,
                            row.qty_out,
                            row.saldo_akhir
                        ]);
                    });

                    // Apply border style to all cells except title and A3

                    worksheet.eachRow({
                        includeEmpty: true
                    }, function(row, rowNumber) {
                        if (rowNumber !== mainTitleRow.number && rowNumber !== titleRow
                            .number &&
                            rowNumber !== 3) {
                            row.eachCell({
                                includeEmpty: true
                            }, function(cell, colNumber) {
                                cell.border = {
                                    top: {
                                        style: 'thin'
                                    },
                                    left: {
                                        style: 'thin'
                                    },
                                    bottom: {
                                        style: 'thin'
                                    },
                                    right: {
                                        style: 'thin'
                                    }
                                };
                            });
                        }
                    });
                    // Auto-adjust column widths for specific columns
                    const columnsToAdjust = [6, 5, 7,
                        8
                    ];
                    columnsToAdjust.forEach(colIndex => {
                        let maxLength = 0;
                        worksheet.getColumn(colIndex).eachCell({
                            includeEmpty: true
                        }, cell => {
                            if (cell.value) {
                                maxLength = Math.max(maxLength, cell.value.toString()
                                    .length);
                            }
                        });
                        worksheet.getColumn(colIndex).width = maxLength + 2; // Add padding
                    });

                    // Export the workbook
                    workbook.xlsx.writeBuffer().then(function(buffer) {
                        const blob = new Blob([buffer], {
                            type: "application/octet-stream"
                        });
                        const link = document.createElement('a');
                        link.href = window.URL.createObjectURL(blob);
                        link.download = "Laporan Mutasi FG Stock.xlsx";
                        link.click();

                        // Calculate the elapsed time
                        const endTime = new Date().getTime();
                        const elapsedTime = Math.round((endTime - startTime) /
                            1000); // Convert to seconds

                        // Close the loading notification
                        Swal.close();

                        // Show success message with elapsed time
                        Swal.fire({
                            title: 'Success!',
                            text: `Data has been successfully exported in ${elapsedTime} seconds.`,
                            icon: 'success',
                            confirmButtonText: 'Okay'
                        });
                    });
                },
                error: function() {
                    Swal.fire({
                        title: 'Error!',
                        text: 'There was an error exporting the data.',
                        icon: 'error',
                        confirmButtonText: 'Okay'
                    });
                }
            });
        }
    </script>
@endsection
