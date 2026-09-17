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

        /* Modal list mesin dibuat lebar & tinggi supaya banyak baris terlihat sekaligus */
        .modal-detail-opname {
            max-width: 95vw;
        }

        .modal-detail-opname .modal-body {
            max-height: 75vh;
            overflow-y: auto;
        }

        /* Tabel detail: hanya body tabel yang scroll, header tetap terlihat */
        #detailTableWrapper {
            overflow: visible;
        }

        #detailTableWrapper .dataTables_scrollHead th {
            background-color: var(--sb-color);
            color: var(--light-color);
        }

        /* ---- Tampilan HP: tiap baris list mesin jadi satu kartu ---- */
        @media (max-width: 767.98px) {
            .modal-detail-opname {
                max-width: none;
            }

            .modal-detail-opname .modal-body {
                max-height: none;
                padding: .75rem;
                background-color: #f4f6f9;
            }

            #detailTable,
            #detailTable tbody {
                display: block;
                width: 100% !important;
                border: 0;
            }

            #detailTable thead {
                display: none;
            }

            #detailTable tbody tr {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: .45rem .75rem;
                margin-bottom: .75rem;
                padding: .75rem .85rem;
                background-color: #fff;
                border: 1px solid #dee2e6;
                border-left: 5px solid #198754;
                border-radius: .6rem;
                box-shadow: 0 1px 3px rgba(0, 0, 0, .08);
            }

            #detailTable tbody tr[data-ket="TIDAK_SESUAI"] {
                border-left-color: #dc3545;
                background-color: #fff5f5;
            }

            #detailTable tbody tr[data-ket="TIDAK_TERDAFTAR"] {
                border-left-color: #6c757d;
            }

            #detailTable tbody td {
                display: block;
                padding: 0;
                border: 0;
                background-color: transparent;
                box-shadow: none !important;
                text-align: left !important;
                font-size: .85rem;
                word-break: break-word;
            }

            /* Label kecil di atas nilai, diambil dari atribut data-label */
            #detailTable tbody td[data-label]::before {
                content: attr(data-label);
                display: block;
                font-size: .7rem;
                font-weight: 400;
                color: #6c757d;
                text-transform: uppercase;
                letter-spacing: .02em;
            }

            #detailTable td.kolom-no {
                display: none;
            }

            /* Urutan isi kartu (2 kolom):
               QR | Sumber, Jenis, Merk | Tipe, SN | Tgl, Lokasi SO | Lokasi Aktual, Ket | User */
            #detailTable td.kolom-qr { order: 1; font-weight: 700; font-size: 1rem; }
            #detailTable td.kolom-sumber { order: 2; text-align: right !important; }
            #detailTable td.kolom-jenis {
                order: 3;
                grid-column: 1 / -1;
                font-weight: 600;
                padding-bottom: .4rem;
                border-bottom: 1px dashed #dee2e6;
            }
            #detailTable td.kolom-merk { order: 4; }
            #detailTable td.kolom-tipe { order: 5; }
            #detailTable td.kolom-sn { order: 6; }
            #detailTable td.kolom-tgl { order: 7; }
            #detailTable td.kolom-lokasi-so,
            #detailTable td.kolom-lokasi-aktual {
                padding: .4rem .5rem;
                background-color: #f8f9fa !important;
                border-radius: .4rem;
            }
            #detailTable td.kolom-lokasi-so { order: 8; }
            #detailTable td.kolom-lokasi-aktual { order: 9; }
            #detailTable td.kolom-ket {
                order: 10;
                align-self: center;
                padding-top: .4rem;
            }
            #detailTable td.kolom-user {
                order: 11;
                align-self: center;
                text-align: right !important;
                padding-top: .4rem;
            }

            #detailTable td.kolom-ket .badge {
                font-size: .8rem;
                padding: .4em .7em;
            }

            #detailTable tbody td.dataTables_empty {
                grid-column: 1 / -1;
                text-align: center !important;
            }

            /* Kontrol DataTables (jumlah baris, info, halaman) ditumpuk di tengah */
            #DetailOpnameModal .dataTables_wrapper .d-flex {
                flex-direction: column;
                align-items: center !important;
                gap: .5rem;
            }

            #DetailOpnameModal .dataTables_info {
                text-align: center;
                padding-top: 0 !important;
            }

            #DetailOpnameModal .dataTables_paginate .pagination {
                justify-content: center;
                flex-wrap: wrap;
                margin: 0;
            }

            .detail-opname-footer {
                flex-direction: column;
                align-items: stretch;
                gap: .5rem;
            }

            .detail-opname-footer #detailTotal {
                margin-right: 0 !important;
                text-align: center;
                line-height: 1.8;
            }

            .detail-opname-footer .btn {
                width: 100%;
                margin: 0;
            }
        }
    </style>

@endsection

@section('content')
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-clipboard-check"></i> Opname Mesin</h5>
        </div>
        <div class="card-body">
            <div class="mb-3 d-flex justify-content-between align-items-center">
                <button type="button" class="btn btn-primary btn-sm" id="btnNewHeader">
                    <i class="fas fa-plus"></i> New
                </button>
                <button type="button" class="btn btn-outline-primary btn-sm" id="btnMasterLokasi">
                    <i class="fas fa-map-marker-alt"></i> Lokasi
                </button>
            </div>
            <div class="mb-3 d-flex align-items-end gap-2 flex-wrap">
                <div>
                    <label for="txttgl_awal" class="col-form-label"><small><b>Tgl Awal :</b></small></label>
                    <input type="date" id="txttgl_awal" class="form-control form-control-sm">
                </div>
                <div>
                    <label for="txttgl_akhir" class="col-form-label"><small><b>Tgl Akhir :</b></small></label>
                    <input type="date" id="txttgl_akhir" class="form-control form-control-sm">
                </div>
                <button type="button" class="btn btn-primary btn-sm" onclick="dataTableReload();">
                    <i class="fas fa-search"></i> Search
                </button>
                <button type="button" class="btn btn-success btn-sm" onclick="export_excel_opname_mesin();">
                    <i class="fas fa-file-excel"></i> Export
                </button>
            </div>

            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-hover align-middle text-nowrap w-100">
                    <thead class="bg-sb">
                        <tr>
                            <th scope="col" class="text-center align-middle">No SO</th>
                            <th scope="col" class="text-center align-middle">Periode</th>
                            <th scope="col" class="text-center align-middle">Keterangan</th>
                            <th scope="col" class="text-center align-middle">Total Mesin</th>
                            <th scope="col" class="text-center align-middle">Dibuat Oleh</th>
                            <th scope="col" class="text-center align-middle">Waktu Dibuat</th>
                            <th scope="col" class="text-center align-middle">Act</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Header Opname Baru -->
    <div class="modal fade" id="NewHeaderModal" tabindex="-1" aria-labelledby="NewHeaderModalLabel" aria-hidden="true"
        data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-sb text-white">
                    <h5 class="modal-title mb-0" id="NewHeaderModalLabel">Buat Opname Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label"><small><b>No SO</b></small></label>
                        <input type="text" class="form-control form-control-sm" value="Dibuat otomatis saat disimpan"
                            disabled>
                    </div>
                    <div class="mb-3">
                        <label for="periode_tgl_awal" class="form-label"><small><b>Periode Tgl Awal</b></small></label>
                        <input type="date" id="periode_tgl_awal" class="form-control form-control-sm">
                    </div>
                    <div class="mb-3">
                        <label for="periode_tgl_akhir" class="form-label"><small><b>Periode Tgl Akhir</b></small></label>
                        <input type="date" id="periode_tgl_akhir" class="form-control form-control-sm">
                    </div>
                    <div class="mb-1">
                        <label for="ket" class="form-label"><small><b>Keterangan</b></small></label>
                        <textarea id="ket" class="form-control form-control-sm" rows="3"
                            placeholder="Opsional..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary btn-sm" id="btnSimpanHeader">
                        <i class="fas fa-save"></i> Simpan & Mulai Scan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal List Mesin per No SO -->
    <div class="modal fade" id="DetailOpnameModal" tabindex="-1" aria-labelledby="DetailOpnameModalLabel"
        aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-fullscreen-md-down modal-detail-opname">
            <div class="modal-content">
                <div class="modal-header bg-sb text-white">
                    <h5 class="modal-title mb-0" id="DetailOpnameModalLabel">List Mesin</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-2 mb-2">
                        <div class="col-md-4">
                            <input type="text" id="detailSearch" class="form-control form-control-sm"
                                placeholder="Cari kode QR / jenis / merk / serial number / sesuai / tidak sesuai...">
                        </div>
                        <div class="col-md-3">
                            <select id="detailFilterLokasi" class="form-control form-control-sm select2bs4">
                                <option value="">Semua Lokasi</option>
                            </select>
                        </div>
                        <div class="col-6 col-md-2">
                            <select id="detailFilterSumber" class="form-control form-control-sm select2bs4">
                                <option value="">Semua Sumber</option>
                                <option value="PEMBELIAN">Pembelian</option>
                                <option value="SEWA">Sewa</option>
                            </select>
                        </div>
                        <div class="col-6 col-md-3">
                            <select id="detailFilterKet" class="form-control form-control-sm select2bs4">
                                <option value="">Semua Keterangan</option>
                                <option value="SESUAI">Sesuai</option>
                                <option value="TIDAK_SESUAI">Tidak Sesuai</option>
                                <option value="TIDAK_TERDAFTAR">Tidak Terdaftar</option>
                            </select>
                        </div>
                    </div>
                    <div class="table-responsive" id="detailTableWrapper">
                        <table id="detailTable" class="table table-bordered table-sm align-middle mb-0 w-100">
                            <thead class="bg-sb">
                                <tr>
                                    <th scope="col" class="text-center">No</th>
                                    <th scope="col" class="text-center">Sumber</th>
                                    <th scope="col">Kode QR</th>
                                    <th scope="col">Jenis</th>
                                    <th scope="col">Merk</th>
                                    <th scope="col">Tipe</th>
                                    <th scope="col">Serial Number</th>
                                    <th scope="col">Lokasi SO</th>
                                    <th scope="col">Lokasi Aktual</th>
                                    <th scope="col" class="text-center">Ket</th>
                                    <th scope="col">Tgl. Scan</th>
                                    <th scope="col">User</th>
                                </tr>
                            </thead>
                            <tbody id="detailTableBody"></tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer detail-opname-footer">
                    <small class="me-auto text-muted" id="detailTotal"></small>
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Konfirmasi Terapkan Hasil SO ke Lokasi Mesin -->
    <div class="modal fade" id="ApplyOpnameModal" tabindex="-1" aria-labelledby="ApplyOpnameModalLabel"
        aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-sb text-white">
                    <h5 class="modal-title mb-0" id="ApplyOpnameModalLabel">Terapkan Hasil Opname</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2">
                        Lokasi mesin di master akan <b>diupdate mengikuti hasil scan</b> pada
                        <b id="applyNoSo">-</b>.
                    </p>

                    <div id="applyRingkasan" class="small text-muted mb-3">
                        Menghitung dampak...
                    </div>

                    <div class="alert alert-warning py-2 mb-0 small">
                        <i class="fas fa-exclamation-triangle"></i>
                        Perubahan ini tidak bisa dibatalkan. Lanjutkan?
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-warning btn-sm" id="btnApplyConfirm" disabled>
                        <i class="fas fa-check"></i> Ya, Update
                    </button>
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
        // Modul Asset: senyapkan alert bawaan DataTables saat ajax gagal, cukup dicatat di console
        $.fn.dataTable.ext.errMode = function(settings, techNote, message) {
            console.error('DataTable ajax error:', message);
        };
    </script>
    <script>
        // Menerapkan hasil opname mengubah lokasi ribuan mesin sekaligus, jadi tombolnya
        // hanya untuk user tertentu. Backend tetap mengecek ulang, ini sekadar sembunyikan UI.
        const bolehApply = @json(in_array(auth()->user()->username ?? '', App\Http\Controllers\AssetMesinOpnameController::USER_APPLY_OPNAME, true));

        // Cuma SO terbaru yang boleh diterapkan; menerapkan SO lama akan menimpa lokasi
        // dengan data yang sudah usang. Backend mengecek ulang hal yang sama.
        const idSoTerbaru = @json($idSoTerbaru);

        // Default filter: awal bulan berjalan s.d. hari ini
        let todayStr = new Date().toISOString().slice(0, 10);
        $('#txttgl_awal').val(todayStr.slice(0, 8) + '01');
        $('#txttgl_akhir').val(todayStr);

        function dataTableReload() {
            datatable.ajax.reload();
        }

        let datatable = $('#datatable').DataTable({
            ordering: true,
            responsive: false,
            processing: true,
            serverSide: false,
            paging: true,
            searching: true,
            scrollX: true,
            ajax: {
                url: '{{ route('asset_mesin_opname') }}',
                data: function(d) {
                    d.tgl_awal = $('#txttgl_awal').val();
                    d.tgl_akhir = $('#txttgl_akhir').val();
                }
            },
            columns: [
                { data: 'no_so', className: 'text-center' }, // No SO
                {
                    data: null,
                    className: 'text-center',
                    render: function(row) {
                        return `${row.periode_awal ?? '-'} s/d ${row.periode_akhir ?? '-'}`;
                    }
                }, // Periode
                { data: 'ket', defaultContent: '-' }, // Keterangan
                { data: 'total_mesin', className: 'text-center' }, // Total Mesin
                { data: 'created_by', defaultContent: '-' }, // Dibuat Oleh
                { data: 'created_at', className: 'text-center' }, // Waktu Dibuat
                {
                    data: null,
                    className: 'text-center',
                    orderable: false,
                    render: function(row) {
                        // Tombol "+" melanjutkan scan pada No SO ini
                        let urlTambah = '{{ route('create_asset_mesin_opname') }}?id_so=' +
                            encodeURIComponent(row.id);

                        // Tombol "terapkan": cuma user yang berhak, dan cuma di baris SO terbaru
                        let btnApply = (bolehApply && Number(row.id) === Number(idSoTerbaru)) ?
                            `<button type="button" class="btn btn-sm btn-warning btn-apply"
                                title="Terapkan hasil SO ke lokasi mesin">
                                <i class="fas fa-check"></i>
                            </button>` :
                            '';

                        return `
                            <button type="button" class="btn btn-sm btn-primary btn-view" title="Lihat list mesin">
                                <i class="fas fa-eye"></i>
                            </button>
                            <a href="${urlTambah}" class="btn btn-sm btn-success" title="Tambah / kurangi mesin">
                                <i class="fas fa-plus"></i>
                            </a>
                            ${btnApply}`;
                    }
                }, // Act
            ],
        });

        // ---- Buat header opname baru ----
        $('#btnNewHeader').on('click', function() {
            $('#periode_tgl_awal').val(todayStr);
            $('#periode_tgl_akhir').val(todayStr);
            $('#ket').val('');
            $('#NewHeaderModal').modal('show');
        });

        $('#btnSimpanHeader').on('click', function() {
            let btn = $(this);
            let tglAwal = $('#periode_tgl_awal').val();
            let tglAkhir = $('#periode_tgl_akhir').val();

            if (!tglAwal || !tglAkhir) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Periode belum lengkap',
                    text: 'Isi periode tanggal awal & akhir terlebih dahulu.',
                });
                return;
            }

            if (tglAkhir < tglAwal) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Periode tidak valid',
                    text: 'Tanggal akhir tidak boleh lebih awal dari tanggal awal.',
                });
                return;
            }

            btn.prop('disabled', true);

            $.ajax({
                type: 'POST',
                url: '{{ route('store_header_asset_mesin_opname') }}',
                data: {
                    periode_tgl_awal: tglAwal,
                    periode_tgl_akhir: tglAkhir,
                    ket: $('#ket').val(),
                    _token: '{{ csrf_token() }}'
                },
                success: function(res) {
                    // Langsung diarahkan ke halaman scan untuk No SO yang baru dibuat
                    window.location.href = res.redirect;
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    btn.prop('disabled', false);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Gagal menyimpan header opname.',
                    });
                }
            });
        });

        // Export Excel mengikuti filter tanggal yang sedang aktif di tabel
        function export_excel_opname_mesin() {
            let tgl_awal = $('#txttgl_awal').val();
            let tgl_akhir = $('#txttgl_akhir').val();

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
                url: '{{ route('export_excel_asset_mesin_opname') }}',
                data: {
                    tgl_awal: tgl_awal,
                    tgl_akhir: tgl_akhir
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
                    link.download = 'Stok Opname Mesin ' + tgl_awal + ' sd ' + tgl_akhir + '.xlsx';
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

        // Select2 Autofocus
        $(document).on('select2:open', () => {
            document.querySelector('.select2-search__field').focus();
        });

        // dropdownParent diarahkan ke modalnya, karena modal Bootstrap 5 menahan focus
        // sehingga dropdown yang nempel di <body> tidak bisa diketik
        $('#detailFilterLokasi, #detailFilterSumber, #detailFilterKet').select2({
            theme: 'bootstrap4',
            width: '100%',
            dropdownParent: $('#DetailOpnameModal')
        });
        $('.select2-container--bootstrap4 .select2-selection--single').css({
            'height': '30px',
            'font-size': '12px',
            'line-height': '30px'
        });

        // ---- Master Lokasi ----
        // Isi modal & init tabelnya diurus partial master_lokasi_script.
        $('#btnMasterLokasi').on('click', function() {
            $('#MasterLokasiModal').modal('show');
        });

        let detailTable = null;

        // Pencarian bebas di modal detail.
        // Kata "sesuai" / "tidak sesuai" / "tidak terdaftar" diarahkan ke filter Keterangan, karena
        // pencarian teks biasa untuk "sesuai" ikut menangkap baris "TIDAK SESUAI".
        const KATA_KET = {
            'sesuai': 'SESUAI',
            'tidak sesuai': 'TIDAK_SESUAI',
            'tidak terdaftar': 'TIDAK_TERDAFTAR',
        };
        let ketDariSearch = false;

        $('#detailSearch').on('keyup', function() {
            if (!detailTable) return;

            let kata = this.value.trim().toLowerCase().replace(/\s+/g, ' ');
            let ket = KATA_KET[kata];

            if (ket) {
                ketDariSearch = true;
                $('#detailFilterKet').val(ket).trigger('change.select2');
                filterKet = ket;
                detailTable.search('').draw();
                return;
            }

            // Kata kunci keterangan dihapus / diganti: lepas lagi filter yang tadi dipasang dari search
            if (ketDariSearch) {
                ketDariSearch = false;
                $('#detailFilterKet').val('').trigger('change.select2');
                filterKet = '';
            }

            detailTable.search(this.value).draw();
        });

        // Filter lokasi dicocokkan lewat id_lokasi yang ditempel di <tr>, bukan lewat teks kolom
        // Lokasi. Nama lokasi hasil gabungan main - sub - status rawan beda spasi / karakter
        // regex, sedangkan id-nya pasti unik & persis.
        let filterLokasiId = '';

        let filterKet = '';

        $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
            if (settings.nTable.id !== 'detailTable') return true;

            let tr = settings.aoData[dataIndex].nTr;
            if (!tr) return true;

            if (filterLokasiId !== '' && String($(tr).attr('data-lokasi-id')) !== String(filterLokasiId)) return false;
            if (filterKet !== '' && $(tr).attr('data-ket') !== filterKet) return false;

            return true;
        });

        // Filter keterangan pembanding lokasi SO vs lokasi aktual, lewat data-ket di <tr>
        $('#detailFilterKet').on('change', function() {
            // Dipilih manual lewat dropdown, jadi bukan lagi hasil kata kunci di kotak search
            ketDariSearch = false;
            filterKet = this.value || '';
            if (detailTable) detailTable.draw();
        });

        $('#detailFilterLokasi').on('change', function() {
            filterLokasiId = this.value || '';
            if (detailTable) detailTable.draw();
        });

        // Filter kolom Sumber (index 1), dicocokkan persis
        $('#detailFilterSumber').on('change', function() {
            if (detailTable) detailTable.column(1).search(this.value).draw();
        });

        // Lihat isi satu No SO (read-only)
        $('#datatable').on('click', '.btn-view', function() {
            let row = datatable.row($(this).closest('tr')).data();

            $('#DetailOpnameModalLabel').text(`List Mesin - ${row.no_so ?? '-'}`);
            $('#detailTotal').text('');

            // DataTables lama dibuang dulu supaya isi tbody bisa diganti untuk No SO yang baru dipilih
            if ($.fn.DataTable.isDataTable('#detailTable')) {
                $('#detailTable').DataTable().destroy();
            }

            $('#detailSearch').val('');
            filterLokasiId = '';
            filterKet = '';
            ketDariSearch = false;
            $('#detailFilterLokasi').val('').trigger('change.select2');
            $('#detailFilterKet').val('').trigger('change.select2');
            $('#detailFilterSumber').val('').trigger('change.select2');

            let $body = $('#detailTableBody').empty();

            $.ajax({
                type: 'GET',
                url: '{{ route('getdata_asset_mesin_opname') }}',
                data: {
                    id_so: row.id
                },
                success: function(res) {
                    let rows = res.data ?? [];
                    let sewa = rows.filter(r => r.sumber === 'SEWA').length;

                    rows.forEach(function(r, i) {
                        let warna = r.sumber === 'SEWA' ? 'bg-warning text-dark' : 'bg-primary';
                        let badge = r.sumber ?
                            `<span class="badge ${warna}">${r.sumber}</span>` :
                            '<span class="badge bg-secondary">-</span>';
                        // Pembanding lokasi hasil scan dengan lokasi aktual di master penerimaan mesin.
                        // Unit tanpa sumber = QR tidak ketemu / sudah tidak aktif di master.
                        let ket = !r.sumber ? 'TIDAK_TERDAFTAR' :
                            (String(r.id_lokasi ?? '') === String(r.id_lokasi_aktual ?? '') ? 'SESUAI' : 'TIDAK_SESUAI');
                        let beda = ket === 'TIDAK_SESUAI';
                        let badgeKet = {
                            SESUAI: '<span class="badge bg-success"><i class="fas fa-check-circle"></i> SESUAI</span>',
                            TIDAK_SESUAI: '<span class="badge bg-danger"><i class="fas fa-times-circle"></i> TIDAK SESUAI</span>',
                            TIDAK_TERDAFTAR: '<span class="badge bg-secondary"><i class="fas fa-question-circle"></i> TIDAK TERDAFTAR</span>',
                        }[ket];

                        $body.append(`
                            <tr data-lokasi-id="${r.id_lokasi ?? ''}" data-ket="${ket}" class="${beda ? 'table-danger' : ''}">
                                <td class="text-center kolom-no">${i + 1}</td>
                                <td class="text-center kolom-sumber">${badge}</td>
                                <td class="kolom-qr">${r.kode_qr ?? '-'}</td>
                                <td class="kolom-jenis">${r.nm_jenis ?? '-'}</td>
                                <td class="kolom-merk" data-label="Merk">${r.nm_merk ?? '-'}</td>
                                <td class="kolom-tipe" data-label="Tipe">${r.tipe ?? '-'}</td>
                                <td class="kolom-sn" data-label="Serial Number">${r.serial_number ?? '-'}</td>
                                <td class="kolom-lokasi-so" data-label="Lokasi SO">${r.lokasi ?? '-'}</td>
                                <td class="kolom-lokasi-aktual ${beda ? 'text-danger fw-bold' : ''}" data-label="Lokasi Aktual">${r.lokasi_aktual ?? '-'}</td>
                                <td class="text-center kolom-ket" data-order="${ket}">${badgeKet}</td>
                                <td class="kolom-tgl" data-label="Tgl. Scan">${r.tgl_opname ?? '-'}</td>
                                <td class="kolom-user" data-label="User">${r.created_by ?? '-'}</td>
                            </tr>`);
                    });

                    // Isi dropdown lokasi dari data yang ada, jadi hanya lokasi terpakai yang muncul.
                    // Value-nya id_lokasi, teksnya nama lokasi.
                    let petaLokasi = new Map();
                    rows.forEach(function(r) {
                        if (r.id_lokasi != null && !petaLokasi.has(String(r.id_lokasi))) {
                            petaLokasi.set(String(r.id_lokasi), (r.lokasi || '-').trim());
                        }
                    });

                    let daftarLokasi = [...petaLokasi.entries()].sort((a, b) => a[1].localeCompare(b[1]));
                    let $lokasi = $('#detailFilterLokasi');
                    $lokasi.find('option:gt(0)').remove();
                    daftarLokasi.forEach(function([id, nama]) {
                        $lokasi.append(`<option value="${id}">${nama}</option>`);
                    });
                    $lokasi.val('').trigger('change.select2');

                    // Di HP baris tampil sebagai kartu (lihat CSS), jadi scroll & header tabel DataTables
                    // tidak dipakai; kartunya cukup ikut scroll modal.
                    let isHp = window.matchMedia('(max-width: 767.98px)').matches;

                    detailTable = $('#detailTable').DataTable({
                        dom: '<"d-flex justify-content-between align-items-center mb-2"l>rt<"d-flex justify-content-between align-items-center mt-2"ip>',
                        paging: true,
                        pageLength: 25,
                        lengthChange: true,
                        // -1 = tampilkan semua baris sekaligus
                        lengthMenu: [
                            [10, 25, 50, 100, -1],
                            [10, 25, 50, 100, 'All']
                        ],
                        searching: true,
                        ordering: true,
                        info: true,
                        autoWidth: false,
                        scrollY: isHp ? '' : '55vh',
                        scrollX: !isHp,
                        scrollCollapse: !isHp,
                        pagingType: isHp ? 'simple' : 'simple_numbers',
                        drawCallback: function() {
                            // Rekap mengikuti hasil filter yang sedang tampil
                            let api = this.api();
                            let data = api.rows({ search: 'applied' }).nodes().toArray();
                            let tampil = data.length;
                            let sewaTampil = data.filter(tr => $(tr).find('td:eq(1)').text().trim() === 'SEWA')
                                .length;

                            let hitungKet = k => data.filter(tr => $(tr).attr('data-ket') === k).length;

                            $('#detailTotal').html(
                                `Tampil : ${tampil} dari ${rows.length} mesin (Pembelian : ${tampil - sewaTampil}, Sewa : ${sewaTampil})` +
                                ` &nbsp;|&nbsp; <span class="badge bg-success">Sesuai : ${hitungKet('SESUAI')}</span>` +
                                ` <span class="badge bg-danger">Tidak Sesuai : ${hitungKet('TIDAK_SESUAI')}</span>` +
                                ` <span class="badge bg-secondary">Tidak Terdaftar : ${hitungKet('TIDAK_TERDAFTAR')}</span>`
                            );
                        }
                    });

                    $('#DetailOpnameModal').one('shown.bs.modal', function() {
                        // Lebar kolom baru bisa dihitung benar setelah modal tampil
                        detailTable.columns.adjust();
                    });

                    $('#DetailOpnameModal').modal('show');
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Gagal memuat list mesin.',
                    });
                }
            });
        });

        // ---- Terapkan hasil opname ke lokasi mesin ----
        let applyIdSo = null;

        $('#datatable').on('click', '.btn-apply', function() {
            let row = datatable.row($(this).closest('tr')).data();

            applyIdSo = row.id;
            $('#applyNoSo').text(row.no_so ?? '-');
            $('#applyRingkasan').html('<i class="fas fa-spinner fa-spin"></i> Menghitung dampak...');
            $('#btnApplyConfirm').prop('disabled', true);
            $('#ApplyOpnameModal').modal('show');

            // Ringkasan dihitung dulu supaya user tahu persis berapa unit yang akan berubah
            $.ajax({
                type: 'GET',
                url: '{{ route('preview_apply_asset_mesin_opname') }}',
                data: {
                    id_so: applyIdSo
                },
                success: function(res) {
                    let baris = [
                        `Hasil scan : <b>${res.total_scan}</b> mesin`,
                        `Cocok di master : <b>${res.cocok_beli}</b> pembelian, <b>${res.cocok_sewa}</b> sewa`,
                        `Lokasi akan berubah : <b>${res.akan_berubah}</b> mesin`,
                    ];

                    if (res.tidak_ketemu) {
                        baris.push(
                            `<span class="text-danger">Tidak ketemu di master : <b>${res.tidak_ketemu}</b> (dilewati)</span>`
                        );
                    }
                    if (res.tanpa_lokasi) {
                        baris.push(
                            `<span class="text-danger">Scan tanpa lokasi : <b>${res.tanpa_lokasi}</b> (dilewati)</span>`
                        );
                    }

                    $('#applyRingkasan').html(baris.join('<br>'));
                    $('#btnApplyConfirm').prop('disabled', res.total_scan === 0);
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    $('#applyRingkasan').html(
                        '<span class="text-danger">Gagal menghitung dampak.</span>');
                }
            });
        });

        $('#btnApplyConfirm').on('click', function() {
            let btn = $(this);

            btn.prop('disabled', true);

            $.ajax({
                type: 'POST',
                url: '{{ route('apply_asset_mesin_opname') }}',
                data: {
                    id_so: applyIdSo,
                    _token: '{{ csrf_token() }}'
                },
                success: function(res) {
                    $('#ApplyOpnameModal').modal('hide');
                    btn.prop('disabled', false);

                    Swal.fire({
                        icon: res.icon ?? 'success',
                        title: 'Berhasil',
                        html: `${res.msg}<br><small class="text-muted">${res.total} mesin diupdate (${res.beli} pembelian, ${res.sewa} sewa)</small>`,
                    });

                    dataTableReload();
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    btn.prop('disabled', false);

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.status === 403 ?
                            'Anda tidak punya akses untuk menerapkan hasil opname.' :
                            'Gagal menerapkan hasil opname.',
                    });
                }
            });
        });
    </script>

    {{-- Modal Master Lokasi: markup modal + script-nya ada di partial ini --}}
    @include('asset_management.partials.master_lokasi_script', [
        'asModal' => true,
        'autoInitMasterLokasi' => false,
        'canDeleteMasterLokasi' => false,
    ])
@endsection
