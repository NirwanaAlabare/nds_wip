@extends('layouts.index')

@section('custom-link')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
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

        .drop-title {
            color: #444;
            font-size: 20px;
            font-weight: bold;
            text-align: center;
            transition: color .2s ease-in-out;
        }

        .form-control {
            border: 1.5px solid #ced4da;
            border-radius: 8px;
            padding: 6px 10px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.15rem rgba(13, 110, 253, 0.25);
        }

        .dataTables_length select {
            width: auto;
            min-width: 65px;
            padding-right: 24px;
        }

        .unit-qr-img {
            height: 50px;
            width: 50px;
            cursor: pointer;
        }

        .unit-foto-img {
            height: 50px;
            width: 50px;
            object-fit: cover;
            cursor: pointer;
        }

        .unit-preview-zoom-img {
            max-width: 90vw;
            max-height: 80vh;
            object-fit: contain;
            cursor: zoom-in;
            transition: transform 0.1s ease-out;
        }

        .td-truncate {
            max-width: 220px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .filter-row th {
            padding: 4px;
        }

        /* Samakan tinggi semua kontrol di baris filter: select2, input, dan btn-group */
        .filter-bar .form-control-sm,
        .filter-bar .btn-group-sm > .btn,
        .filter-bar .btn-group-sm > .btn-check + .btn {
            height: 31px;
            font-size: 12px;
            line-height: 1.5;
            border-radius: 8px;
        }

        .filter-bar .select2-container--bootstrap4 .select2-selection--single {
            height: 31px;
            font-size: 12px;
            border: 1.5px solid #ced4da;
            border-radius: 8px;
        }

        .filter-bar .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
            line-height: 28px;
            padding-left: 10px;
        }

        .filter-bar .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow {
            height: 29px;
        }

        .filter-bar .select2-container--bootstrap4.select2-container--focus .select2-selection,
        .filter-bar .select2-container--bootstrap4.select2-container--open .select2-selection {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.15rem rgba(13, 110, 253, 0.25);
        }

        .filter-bar label {
            margin-bottom: 2px;
        }

        .filter-bar .btn-group-sm > .btn {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .filter-bar .btn-group > .btn:not(:first-of-type) {
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
        }

        .filter-bar .btn-group > .btn:not(:last-of-type) {
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
        }

        /* Panel filter: dipisah visual dari zona tabel */
        .filter-panel {
            background: #f7f8fa;
            border: 1px solid #e6e8eb;
            border-radius: 10px;
            padding: 12px 14px 14px;
        }

        /* Kartu statistik ringkasan di atas tabel */
        .stat-card {
            border: 1px solid #e6e8eb;
            border-left: 4px solid var(--sb-color);
            border-radius: 10px;
            background: #fff;
            padding: 8px 14px;
            min-width: 140px;
        }

        .stat-card .stat-label {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
            color: #6c757d;
            line-height: 1.2;
        }

        .stat-card .stat-value {
            font-size: 20px;
            font-weight: 700;
            color: var(--sb-color);
            line-height: 1.2;
        }

        /* Input pencarian per kolom di header tabel */
        .filter-row th {
            background: #f7f8fa;
        }

        .filter-row .col-filter {
            height: 28px;
            font-size: 11px;
            font-weight: 400;
            border-radius: 6px;
        }

        /* Pil lembut, bukan badge pekat - supaya tabel tidak ramai warna.
           Konvensi & warna disamakan dengan create_mutasi_mesin.blade.php */
        .pil {
            display: inline-block;
            font-size: .68rem;
            font-weight: 600;
            letter-spacing: .3px;
            padding: 2px 8px;
            border-radius: 999px;
            white-space: nowrap;
        }

        .pil-beli {
            color: #1d4ed8;
            background: #e8eefc;
        }

        .pil-sewa {
            color: #8a5a00;
            background: #fbf0dc;
        }

        .pil-active {
            color: #1b7c50;
            background: #e4f4ec;
        }

        .pil-idle {
            color: #8a5a00;
            background: #fbf0dc;
        }

        .pil-breakdown {
            color: #b42318;
            background: #fce9e7;
        }

        .pil-lain {
            color: #6b7280;
            background: #f1f3f5;
        }

        /* Sel tanpa isi dibedakan dari data terisi */
        .sel-kosong {
            color: #adb5bd;
            font-style: italic;
        }

        /* Zebra + hover: bantu mata mengikuti baris di tabel yang perlu scroll horizontal */
        #datatable tbody tr:nth-of-type(odd) > td {
            background-color: #fbfcfd;
        }

        #datatable tbody tr:hover > td {
            background-color: #eef4ff;
        }

        /* Toolbar bawaan DataTables diselaraskan dengan panel filter */
        #datatable_wrapper .dataTables_length,
        #datatable_wrapper .dataTables_filter,
        #datatable_wrapper .dataTables_info,
        #datatable_wrapper .dataTables_paginate {
            font-size: 12px;
        }

        #datatable_wrapper .dataTables_filter input,
        #datatable_wrapper .dataTables_length select {
            height: 31px;
            font-size: 12px;
            border-radius: 8px;
            border: 1.5px solid #ced4da;
        }

        #datatable_wrapper .dataTables_filter {
            text-align: right;
        }

        #datatable_wrapper .dataTables_filter label {
            margin-bottom: 0;
        }

        #datatable_wrapper .paginate_button {
            font-size: 12px;
        }

        /* Header (judul kolom + baris "Cari...") ada di .dataTables_scrollHead yang terpisah dari
           body, jadi otomatis diam saat body di-scroll. Diberi background sendiri supaya baris
           yang lewat di bawahnya tidak menembus. */
        #datatable_wrapper .dataTables_scrollHead {
            background: #fff;
        }

        #datatable_wrapper .dataTables_scrollHead table {
            margin-bottom: 0 !important;
        }

        #datatable_wrapper .dataTables_scrollBody {
            border-bottom: 1px solid #dee2e6;
        }

        /* Kartu statistik mengecil di layar sempit supaya tetap sebaris */
        @media (max-width: 575.98px) {
            .stat-card {
                min-width: 110px;
                padding: 6px 10px;
            }

            .stat-card .stat-value {
                font-size: 16px;
            }
        }
    </style>
@endsection

@section('content')
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-list-alt"></i> Master Mesin</h5>
        </div>
        <div class="card-body">
            <div class="filter-panel mb-3">
                <div class="row g-2 align-items-end filter-bar">
                    <div class="col-sm-6 col-md-2">
                        <label for="cbosumber"><small><b>Sumber :</b></small></label>
                        <select id="cbosumber" class="form-control form-control-sm select2bs4">
                            <option value="">Semua Sumber</option>
                            <option value="PEMBELIAN">Pembelian</option>
                            <option value="SEWA">Sewa</option>
                        </select>
                    </div>
                    <div class="col-sm-6 col-md-2">
                        <label for="cbojenis"><small><b>Jenis :</b></small></label>
                        <select id="cbojenis" class="form-control form-control-sm select2bs4">
                            <option value="">Semua Jenis</option>
                            @foreach ($jenisList as $row)
                                <option value="{{ $row->nama }}">{{ $row->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-6 col-md-2">
                        <label for="cbomerk"><small><b>Merk :</b></small></label>
                        <select id="cbomerk" class="form-control form-control-sm select2bs4">
                            <option value="">Semua Merk</option>
                            @foreach ($merkList as $row)
                                <option value="{{ $row->nama }}">{{ $row->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-6 col-md-2">
                        <label for="cbosupplier"><small><b>Supplier :</b></small></label>
                        <select id="cbosupplier" class="form-control form-control-sm select2bs4">
                            <option value="">Semua Supplier</option>
                            @foreach ($supplierList as $row)
                                <option value="{{ $row->id_supplier }}">{{ $row->Supplier }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-6 col-md-2">
                        <label for="cbolokasi"><small><b>Lokasi :</b></small></label>
                        <select id="cbolokasi" class="form-control form-control-sm select2bs4">
                            <option value="">Semua Lokasi</option>
                            <option value="0">(Belum Didata)</option>
                            @foreach ($lokasiList as $row)
                                <option value="{{ $row->id }}">{{ $row->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-6 col-md-2">
                        <label for="cbostatus"><small><b>Status :</b></small></label>
                        {{-- Isi option diisi refreshStatusOptions() karena daftarnya ikut pilihan Sumber --}}
                        <select id="cbostatus" class="form-control form-control-sm select2bs4"></select>
                    </div>
                </div>

                <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mt-2 filter-bar">
                    <div>
                        <label class="d-block"><small><b>Tampilan :</b></small></label>
                        <div class="btn-group btn-group-sm" role="group">
                            <input type="radio" class="btn-check" name="viewMode" id="viewModeGroup" value="group"
                                autocomplete="off" checked>
                            <label class="btn btn-outline-primary" for="viewModeGroup">Per Jenis</label>
                            <input type="radio" class="btn-check" name="viewMode" id="viewModeDetail" value="detail"
                                autocomplete="off">
                            <label class="btn btn-outline-primary" for="viewModeDetail">List Detail</label>
                        </div>
                    </div>
                    <button type="button" id="btnResetFilter" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-undo"></i> Reset Filter
                    </button>
                </div>
            </div>

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <div class="d-flex flex-wrap gap-2">
                    <div class="stat-card">
                        <div class="stat-label">Total Unit</div>
                        <div class="stat-value" id="statTotalUnit">-</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">Total Jenis</div>
                        <div class="stat-value" id="statTotalJenis">-</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">Total Merk</div>
                        <div class="stat-value" id="statTotalMerk">-</div>
                    </div>
                </div>
                <button type="button" id="btnExportDetail" class="btn btn-success btn-sm d-none"
                    onclick="exportMasterMesinDetail();">
                    <i class="fas fa-file-excel"></i> Export Excel
                </button>
            </div>

            {{-- Tanpa .table-responsive: DataTables sudah bikin area scroll sendiri lewat scrollX/scrollY.
                 Dua lapis overflow bikin header hasil clone ikut tergeser saat tabel di-scroll. --}}
            <table id="datatable" class="table table-bordered table-hover align-middle text-nowrap w-100">
                <thead class="bg-sb">
                    <tr>
                        <th scope="col" class="text-center align-middle">Sumber</th>
                        <th scope="col" class="text-center align-middle">Kode Jenis</th>
                        <th scope="col" class="text-center align-middle">Jenis</th>
                        <th scope="col" class="text-center align-middle">Kode Merk</th>
                        <th scope="col" class="text-center align-middle">Merk</th>
                        <th scope="col" class="text-center align-middle">Tipe</th>
                        <th scope="col" class="text-center align-middle">Total Unit</th>
                        <th scope="col" class="text-center align-middle">Act</th>
                    </tr>
                    <tr class="filter-row">
                        <th><input type="text" class="form-control form-control-sm col-filter" data-col="0"
                                placeholder="Cari..."></th>
                        <th></th>
                        <th><input type="text" class="form-control form-control-sm col-filter" data-col="2"
                                placeholder="Cari..."></th>
                        <th></th>
                        <th><input type="text" class="form-control form-control-sm col-filter" data-col="4"
                                placeholder="Cari..."></th>
                        <th><input type="text" class="form-control form-control-sm col-filter" data-col="5"
                                placeholder="Cari..."></th>
                        <th></th>
                        <th></th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    <!-- Modal Detail Unit per Jenis -->
    <div class="modal fade" id="MesinUnitModal" tabindex="-1" aria-labelledby="MesinUnitModalLabel" aria-hidden="true"
        data-bs-backdrop="static">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-sb text-white">
                    <h5 class="modal-title mb-0" id="MesinUnitModalLabel">Detail Unit Mesin</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <input type="text" id="unitSearch" class="form-control form-control-sm"
                            placeholder="Cari Serial Number / Lokasi / Supplier...">
                    </div>
                    <div class="table-responsive">
                        <table id="unitTable" class="table table-bordered table-sm align-middle mb-0">
                            <thead class="bg-sb">
                                <tr>
                                    <th scope="col" class="text-center">No</th>
                                    <th scope="col" class="text-center">Foto</th>
                                    <th scope="col" class="text-center">QR Code</th>
                                    <th scope="col">Serial Number</th>
                                    <th scope="col">Lokasi</th>
                                    <th scope="col">Supplier</th>
                                    <th scope="col">No BPB</th>
                                    <th scope="col">Status</th>
                                </tr>
                            </thead>
                            <tbody id="unitTableBody"></tbody>
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
    <!-- DataTables & Plugins -->
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>

    <script>
        // Daftar lokasi ratusan baris, jadi dropdown-nya dibikin bisa diketik
        $(function () {
            // Option Status diisi dulu supaya select2 tidak dipasang ke <select> yang masih kosong
            refreshStatusOptions();

            $('#cbojenis, #cbomerk, #cbosupplier, #cbolokasi').select2({
                theme: 'bootstrap4',
                width: '100%',
            });
            // Sumber & Status cuma sedikit pilihannya, kotak pencariannya cuma bikin ramai
            $('#cbosumber, #cbostatus').select2({
                theme: 'bootstrap4',
                width: '100%',
                minimumResultsForSearch: Infinity,
            });
        });

        // Tabel sewa tidak mengenal BREAKDOWN/SERVICE, jadi isi dropdown Status menyesuaikan
        // pilihan Sumber. Kunci '' dipakai saat Sumber masih "Semua".
        const statusPerSumber = @json($statusPerSumber);

        function refreshStatusOptions() {
            const daftar = statusPerSumber[$('#cbosumber').val()] ?? statusPerSumber[''];
            const terpilih = $('#cbostatus').val() ?? '';
            // Pilihan lama dipertahankan kalau masih berlaku di sumber yang baru; kalau tidak,
            // dikosongkan supaya tidak menghasilkan tabel kosong tanpa sebab yang jelas
            const masihBerlaku = terpilih === '' || terpilih === 'KOSONG' || daftar.includes(terpilih);

            const $status = $('#cbostatus').empty()
                .append(new Option('Semua Status', ''))
                .append(new Option('(Belum Didata)', 'KOSONG'));
            daftar.forEach(function(nilai) {
                $status.append(new Option(nilai, nilai));
            });

            // change.select2 cuma menyegarkan tampilan select2, tidak memicu handler change
            // di bawah - reload datatable-nya diurus pemanggil
            $status.val(masihBerlaku ? terpilih : '').trigger('change.select2');
        }


        $(document).on('select2:open', () => {
            document.querySelector('.select2-search__field').focus();
        });
    </script>

    <script>
        // Modul Asset: senyapkan alert bawaan DataTables saat ajax gagal, cukup dicatat di console
        $.fn.dataTable.ext.errMode = function (settings, techNote, message) {
            console.error('DataTable ajax error:', message);
        };
    </script>
    <script>
        function dataTableReload() {
            datatable.ajax.reload();
        }

        // Thead per mode tampilan: "group" = ringkasan per jenis mesin (Total Unit), "detail" = seluruh unit tanpa grouping
        const theadGroup = `
            <tr>
                <th scope="col" class="text-center align-middle">Sumber</th>
                <th scope="col" class="text-center align-middle">Kode Jenis</th>
                <th scope="col" class="text-center align-middle">Jenis</th>
                <th scope="col" class="text-center align-middle">Kode Merk</th>
                <th scope="col" class="text-center align-middle">Merk</th>
                <th scope="col" class="text-center align-middle">Tipe</th>
                <th scope="col" class="text-center align-middle">Total Unit</th>
                <th scope="col" class="text-center align-middle">Act</th>
            </tr>
            <tr class="filter-row">
                <th><input type="text" class="form-control form-control-sm col-filter" data-col="0" placeholder="Cari..."></th>
                <th></th>
                <th><input type="text" class="form-control form-control-sm col-filter" data-col="2" placeholder="Cari..."></th>
                <th></th>
                <th><input type="text" class="form-control form-control-sm col-filter" data-col="4" placeholder="Cari..."></th>
                <th><input type="text" class="form-control form-control-sm col-filter" data-col="5" placeholder="Cari..."></th>
                <th></th>
                <th></th>
            </tr>`;

        const theadDetail = `
            <tr>
                <th scope="col" class="text-center align-middle">Sumber</th>
                <th scope="col" class="text-center align-middle">Jenis</th>
                <th scope="col" class="text-center align-middle">Merk</th>
                <th scope="col" class="text-center align-middle">Tipe</th>
                <th scope="col" class="text-center align-middle">Serial Number</th>
                <th scope="col" class="text-center align-middle">Kode QR</th>
                <th scope="col" class="text-center align-middle">Lokasi</th>
                <th scope="col" class="text-center align-middle">Supplier</th>
                <th scope="col" class="text-center align-middle">No BPB</th>
                <th scope="col" class="text-center align-middle">Status</th>
            </tr>
            <tr class="filter-row">
                <th><input type="text" class="form-control form-control-sm col-filter" data-col="0" placeholder="Cari..."></th>
                <th><input type="text" class="form-control form-control-sm col-filter" data-col="1" placeholder="Cari..."></th>
                <th><input type="text" class="form-control form-control-sm col-filter" data-col="2" placeholder="Cari..."></th>
                <th><input type="text" class="form-control form-control-sm col-filter" data-col="3" placeholder="Cari..."></th>
                <th><input type="text" class="form-control form-control-sm col-filter" data-col="4" placeholder="Cari..."></th>
                <th><input type="text" class="form-control form-control-sm col-filter" data-col="5" placeholder="Cari..."></th>
                <th><input type="text" class="form-control form-control-sm col-filter" data-col="6" placeholder="Cari..."></th>
                <th><input type="text" class="form-control form-control-sm col-filter" data-col="7" placeholder="Cari..."></th>
                <th><input type="text" class="form-control form-control-sm col-filter" data-col="8" placeholder="Cari..."></th>
                <th><input type="text" class="form-control form-control-sm col-filter" data-col="9" placeholder="Cari..."></th>
            </tr>`;

        // Dipakai kedua mode. Konvensi pil & warnanya disamakan dengan create_mutasi_mesin.blade.php
        function badgeSumber(sumber) {
            if (!sumber) return '<span class="pil pil-lain">-</span>';
            return `<span class="pil ${sumber === 'SEWA' ? 'pil-sewa' : 'pil-beli'}">${sumber}</span>`;
        }

        function badgeStatus(status) {
            let kelas = {
                ACTIVE: 'pil-active',
                IDLE: 'pil-idle',
                BREAKDOWN: 'pil-breakdown',
            }[status] ?? 'pil-lain';
            return `<span class="pil ${kelas}">${status || '-'}</span>`;
        }

        // Nilai kosong ditampilkan redup supaya kebedaan dengan data yang benar-benar terisi.
        // Dibungkus sebagai render agar hanya memengaruhi tampilan, bukan nilai yang difilter/diurut.
        function teksAtauKosong(data, type) {
            if (type !== 'display') return data;
            if (data === null || data === undefined || String(data).trim() === '') {
                return '<span class="sel-kosong">-</span>';
            }
            return $('<div>').text(data).html();
        }

        const groupColumns = [
            { data: 'sumber', className: 'text-center', width: '110px', render: function(d, t) { return t === 'display' ? badgeSumber(d) : d; } }, // Sumber
            { data: 'kd_jenis', className: 'text-center', width: '90px' }, // Kode Jenis
            { data: 'nm_jenis' }, // Jenis
            { data: 'kd_merk', className: 'text-center', width: '90px' }, // Kode Merk
            { data: 'nm_merk' }, // Merk
            { data: 'tipe' }, // Tipe
            {
                data: 'total_unit',
                className: 'text-end',
                width: '100px',
                render: function(data, type) {
                    if (type !== 'display') return data;
                    return Number(data || 0).toLocaleString('id-ID');
                }
            }, // Total Unit
            {
                data: null,
                className: 'text-center',
                width: '100px',
                render: function() {
                    return `
                <button type="button" class="btn btn-sm btn-primary btn-detail-unit">
                    <i class="fas fa-eye"></i> Detail
                </button>`;
                },
                orderable: false,
                searchable: false
            }, // Act
        ];

        const detailColumns = [
            { data: 'sumber', className: 'text-center', width: '110px', render: function(d, t) { return t === 'display' ? badgeSumber(d) : d; } }, // Sumber
            { data: 'nm_jenis', defaultContent: '-', render: teksAtauKosong }, // Jenis
            { data: 'nm_merk', defaultContent: '-', render: teksAtauKosong }, // Merk
            { data: 'tipe', defaultContent: '-', render: teksAtauKosong }, // Tipe
            { data: 'serial_number', defaultContent: '-', render: teksAtauKosong }, // Serial Number
            { data: 'kode_qr', defaultContent: '-', render: teksAtauKosong }, // Kode QR
            { data: 'lokasi', defaultContent: '-', render: teksAtauKosong }, // Lokasi
            { data: 'supplier', defaultContent: '-', render: teksAtauKosong }, // Supplier
            { data: 'bpbno_int', defaultContent: '-', render: teksAtauKosong }, // No BPB
            { data: 'status', className: 'text-center', defaultContent: '-', render: function(d, t) { return t === 'display' ? badgeStatus(d) : d; } }, // Status
        ];

        let datatable;

        // Ringkasan dihitung dari baris yang lolos filter (serverSide: false, jadi semua data
        // sudah ada di client). Mode "group" punya kolom total_unit, mode "detail" 1 baris = 1 unit.
        function updateStatCards(api, mode) {
            const jenisSet = new Set();
            const merkSet = new Set();
            let totalUnit = 0;

            api.rows({ search: 'applied' }).data().each(function(row) {
                totalUnit += mode === 'detail' ? 1 : (parseInt(row.total_unit, 10) || 0);
                if (row.nm_jenis) jenisSet.add(String(row.nm_jenis).trim().toUpperCase());
                if (row.nm_merk) merkSet.add(String(row.nm_merk).trim().toUpperCase());
            });

            $('#statTotalUnit').text(totalUnit.toLocaleString('id-ID'));
            $('#statTotalJenis').text(jenisSet.size.toLocaleString('id-ID'));
            $('#statTotalMerk').text(merkSet.size.toLocaleString('id-ID'));
        }

        function initDataTable(mode) {
            $('#datatable thead').html(mode === 'detail' ? theadDetail : theadGroup);

            datatable = $('#datatable').DataTable({
                ordering: true,
                responsive: false,
                processing: true,
                serverSide: false,
                paging: true,
                searching: true,
                // DataTables butuh tinggi CSS (bukan boolean); 55vh bikin header tetap terlihat saat body di-scroll
                scrollY: '55vh',
                scrollX: true,
                scrollCollapse: false,
                orderCellsTop: true,
                ajax: {
                    url: '{{ route('asset_mesin_master') }}',
                    data: function(d) {
                        d.mode = mode;
                        d.sumber = $('#cbosumber').val();
                        d.nm_jenis = $('#cbojenis').val();
                        d.nm_merk = $('#cbomerk').val();
                        d.id_supplier = $('#cbosupplier').val();
                        d.id_lokasi = $('#cbolokasi').val();
                        d.status = $('#cbostatus').val();
                    }
                },
                columns: mode === 'detail' ? detailColumns : groupColumns,
                // Length & Search sebaris di atas, Info & pagination sebaris di bawah
                dom: '<"d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2"lf>rt' +
                    '<"d-flex flex-wrap justify-content-between align-items-center gap-2 mt-2"ip>',
                drawCallback: function() {
                    updateStatCards(this.api(), mode);
                },
            });
        }

        let currentViewMode = 'group';
        initDataTable(currentViewMode);

        // Ganti tampilan: destroy datatable lama, ganti thead sesuai mode, lalu init ulang
        $('input[name="viewMode"]').on('change', function() {
            currentViewMode = $(this).val();
            datatable.destroy();
            $('#datatable tbody').remove();
            initDataTable(currentViewMode);
            // Export Excel cuma relevan buat "List Detail" (per unit); mode "Per Jenis" cuma ringkasan/total
            $('#btnExportDetail').toggleClass('d-none', currentViewMode !== 'detail');
        });

        // Export Excel List Detail mengikuti filter (Sumber/Jenis/Merk/Supplier/Lokasi/Status) yang sedang aktif
        function exportMasterMesinDetail() {
            Swal.fire({
                title: 'Please Wait,',
                html: 'Exporting Data...',
                didOpen: () => {
                    Swal.showLoading();
                },
                allowOutsideClick: false,
            });

            $.ajax({
                type: 'get',
                url: '{{ route('export_excel_master_mesin_detail') }}',
                data: {
                    sumber: $('#cbosumber').val(),
                    nm_jenis: $('#cbojenis').val(),
                    nm_merk: $('#cbomerk').val(),
                    id_supplier: $('#cbosupplier').val(),
                    id_lokasi: $('#cbolokasi').val(),
                    status: $('#cbostatus').val()
                },
                xhrFields: {
                    responseType: 'blob'
                },
                success: function(response) {
                    Swal.close();
                    Swal.fire({
                        title: 'Data Berhasil Di Export!',
                        icon: 'success',
                        showConfirmButton: true,
                        allowOutsideClick: false
                    });
                    let blob = new Blob([response]);
                    let link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = 'List Detail Mesin.xlsx';
                    link.click();
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    Swal.close();
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Gagal export data ke Excel.',
                    });
                }
            });
        }

        // Filter teks per kolom langsung di data yang sudah dimuat.
        // Delegated dari document (bukan #datatable) karena scrollX/scrollY meng-clone thead ke
        // tabel terpisah (.dataTables_scrollHead) di luar #datatable untuk efek header "fixed" saat
        // body di-scroll - input yang benar-benar terlihat & diketik user ada di klonanya itu, bukan
        // di #datatable asli, jadi delegate ke #datatable saja tidak akan pernah menangkap event-nya.
        $(document).on('keyup', '.col-filter', function() {
            datatable.column($(this).data('col')).search(this.value).draw();
        });

        // Filter dropdown (Sumber, Jenis, Merk, Supplier, Lokasi, Status) memuat ulang data dari server sesuai pilihan
        $('#cbosumber, #cbojenis, #cbomerk, #cbosupplier, #cbolokasi, #cbostatus').on('change', function() {
            if (this.id === 'cbosumber') {
                refreshStatusOptions();
            }
            dataTableReload();
        });

        // Reset semua filter sekaligus: 6 dropdown atas + pencarian per kolom + search bawaan datatable.
        // Dropdown di-reset tanpa trigger change satu-satu supaya ajax reload cuma jalan sekali.
        $('#btnResetFilter').on('click', function() {
            $('#cbosumber, #cbojenis, #cbomerk, #cbosupplier, #cbolokasi, #cbostatus').val('').trigger('change.select2');
            refreshStatusOptions();
            $('.col-filter').val('');
            datatable.columns().search('');
            datatable.search('');
            dataTableReload();
        });

        // Cegah klik di filter row memicu sorting/seleksi baris datatable (lihat catatan scrollHead di atas)
        $(document).on('click', '.filter-row', function(e) {
            e.stopPropagation();
        });

        // Klik tombol Detail (mode "group"): buka modal berisi daftar unit (per-unit) untuk jenis mesin tersebut
        $('#datatable').on('click', '.btn-detail-unit', function() {
            let row = datatable.row($(this).closest('tr')).data();
            openMesinUnitModal(row);
        });

        function openMesinUnitModal(row) {
            $('#MesinUnitModalLabel').text(`${row.nm_jenis ?? '-'} - ${row.nm_merk ?? '-'} - ${row.tipe ?? '-'}`);
            $('#unitSearch').val('');

            if ($.fn.DataTable.isDataTable('#unitTable')) {
                $('#unitTable').DataTable().destroy();
            }

            let $body = $('#unitTableBody').empty();

            $.ajax({
                type: 'GET',
                url: '{{ route('asset_mesin_master_unit') }}',
                data: {
                    id_jenis: row.id_jenis,
                    sumber: row.sumber,
                    nm_jenis: row.nm_jenis,
                    nm_merk: row.nm_merk,
                    tipe: row.tipe
                },
                success: function(units) {
                    // Folder upload foto unit berbeda antara mesin pembelian & sewa
                    let fotoFolder = row.sumber === 'SEWA' ? 'gambar_penerimaan_mesin_sewa' :
                        'gambar_penerimaan_mesin';

                    units.forEach(function(unit, i) {
                        let qrCell = unit.qr ?
                            `<img class="unit-qr-img" src="data:image/svg+xml;base64,${unit.qr}" data-unit-id="${unit.id}" title="Klik untuk print PDF">` :
                            `<span class="text-muted" title="Lengkapi Serial Number & Foto dahulu"><i class="fas fa-lock"></i></span>`;

                        let fotoCell = unit.foto ?
                            `<img class="unit-foto-img" src="/nds_wip/public/storage/${fotoFolder}/${unit.foto}" title="Klik untuk lihat foto">` :
                            `<span class="text-muted">-</span>`;

                        $body.append(`
                    <tr>
                        <td class="text-center align-middle">${i + 1}</td>
                        <td class="text-center align-middle">${fotoCell}</td>
                        <td class="text-center align-middle">${qrCell}</td>
                        <td class="align-middle">${teksAtauKosong(unit.serial_number, 'display')}</td>
                        <td class="align-middle">${teksAtauKosong(unit.lokasi, 'display')}</td>
                        <td class="align-middle">${teksAtauKosong(unit.supplier, 'display')}</td>
                        <td class="align-middle">${teksAtauKosong(unit.bpbno_int, 'display')}</td>
                        <td class="text-center align-middle">${badgeStatus(unit.status)}</td>
                    </tr>`);
                    });

                    $('#unitTable').DataTable({
                        dom: 'rt<"d-flex justify-content-between align-items-center"ip>',
                        paging: true,
                        pageLength: 10,
                        lengthChange: false,
                        searching: true,
                        ordering: false,
                        info: true,
                        autoWidth: false
                    });

                    $('#MesinUnitModal').modal('show');
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

        // Cari di tabel unit pada modal
        $(document).on('keyup', '#unitSearch', function() {
            if ($.fn.DataTable.isDataTable('#unitTable')) {
                $('#unitTable').DataTable().search(this.value).draw();
            }
        });

        // Klik QR Code untuk membuka PDF di tab baru, siap di-print / disimpan sebagai PDF
        $(document).on('click', '.unit-qr-img', function() {
            let unitId = $(this).data('unit-id');
            window.open(`{{ url('/asset_mesin_tambah/unit') }}/${unitId}/print_qr`, '_blank');
        });

        // Klik thumbnail foto untuk melihat versi lebih besar, scroll mouse di atas gambar untuk zoom in/out
        $(document).on('click', '.unit-foto-img', function() {
            Swal.fire({
                imageUrl: this.src,
                imageAlt: 'Preview',
                width: 'auto',
                showConfirmButton: false,
                showCloseButton: true,
                background: '#fff',
                customClass: {
                    image: 'unit-preview-zoom-img'
                },
                didOpen: () => {
                    let scale = 1;
                    document.querySelector('.unit-preview-zoom-img').addEventListener('wheel', function(e) {
                        e.preventDefault();
                        scale = Math.min(Math.max(scale + (e.deltaY < 0 ? 0.2 : -0.2), 1), 4);
                        this.style.transform = `scale(${scale})`;
                    }, {
                        passive: false
                    });
                }
            });
        });
    </script>
@endsection
