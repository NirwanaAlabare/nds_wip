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

        /* Nama lokasi disimpan uppercase, jadi ketikannya langsung ditampilkan uppercase juga */
        #txtLokasiBaru {
            text-transform: uppercase;
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
        <div class="modal-dialog modal-fullscreen-lg-down modal-detail-opname">
            <div class="modal-content">
                <div class="modal-header bg-sb text-white">
                    <h5 class="modal-title mb-0" id="DetailOpnameModalLabel">List Mesin</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-2 mb-2">
                        <div class="col-md-6">
                            <input type="text" id="detailSearch" class="form-control form-control-sm"
                                placeholder="Cari kode QR / jenis / merk / serial number...">
                        </div>
                        <div class="col-md-3">
                            <select id="detailFilterLokasi" class="form-control form-control-sm select2bs4">
                                <option value="">Semua Lokasi</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select id="detailFilterSumber" class="form-control form-control-sm select2bs4">
                                <option value="">Semua Sumber</option>
                                <option value="PEMBELIAN">Pembelian</option>
                                <option value="SEWA">Sewa</option>
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
                                    <th scope="col">Lokasi</th>
                                    <th scope="col">Tgl. Scan</th>
                                    <th scope="col">User</th>
                                </tr>
                            </thead>
                            <tbody id="detailTableBody"></tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <small class="me-auto text-muted" id="detailTotal"></small>
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal Master Lokasi Mesin -->
    <div class="modal fade" id="MasterLokasiModal" tabindex="-1" aria-labelledby="MasterLokasiModalLabel"
        aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-sb text-white">
                    <h5 class="modal-title mb-0" id="MasterLokasiModalLabel">Master Lokasi Mesin</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="input-group input-group-sm mb-3">
                        <input type="text" id="txtLokasiBaru" class="form-control form-control-sm"
                            placeholder="Nama lokasi baru..." autocomplete="off" enterkeyhint="go">
                        <button type="button" class="btn btn-primary btn-sm" id="btnTambahLokasi">
                            <i class="fas fa-plus"></i> Tambah
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table id="lokasiTable" class="table table-bordered table-sm align-middle mb-0 w-100">
                            <thead class="bg-sb">
                                <tr>
                                    <th scope="col" class="text-center">No</th>
                                    <th scope="col">Lokasi</th>
                                    <th scope="col">Dibuat Oleh</th>
                                    <th scope="col">Waktu Dibuat</th>
                                </tr>
                            </thead>
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
        // Modul Asset: senyapkan alert bawaan DataTables saat ajax gagal, cukup dicatat di console
        $.fn.dataTable.ext.errMode = function(settings, techNote, message) {
            console.error('DataTable ajax error:', message);
        };
    </script>
    <script>
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

                        return `
                            <button type="button" class="btn btn-sm btn-primary btn-view" title="Lihat list mesin">
                                <i class="fas fa-eye"></i>
                            </button>
                            <a href="${urlTambah}" class="btn btn-sm btn-success" title="Tambah / kurangi mesin">
                                <i class="fas fa-plus"></i>
                            </a>`;
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
        $('#detailFilterLokasi, #detailFilterSumber').select2({
            theme: 'bootstrap4',
            width: '100%',
            dropdownParent: $('#DetailOpnameModal')
        });
        $('.select2-container--bootstrap4 .select2-selection--single').css({
            'height': '30px',
            'font-size': '12px',
            'line-height': '30px'
        });

        // ---- Master lokasi mesin ----
        // Tabelnya dibuat sekali saat modal pertama kali dibuka, berikutnya cukup di-reload
        let lokasiTable = null;

        $('#btnMasterLokasi').on('click', function() {
            $('#txtLokasiBaru').val('');

            if (!lokasiTable) {
                lokasiTable = $('#lokasiTable').DataTable({
                    dom: '<"d-flex justify-content-between align-items-center mb-2"lf>rt<"d-flex justify-content-between align-items-center mt-2"ip>',
                    processing: true,
                    serverSide: false,
                    ordering: false,
                    autoWidth: false,
                    pageLength: 10,
                    lengthMenu: [
                        [10, 25, 50, -1],
                        [10, 25, 50, 'All']
                    ],
                    ajax: {
                        url: '{{ route('getdata_lokasi_mesin') }}'
                    },
                    columns: [
                        {
                            data: null,
                            className: 'text-center',
                            render: function(data, type, row, meta) {
                                return meta.row + 1;
                            }
                        }, // No
                        { data: 'lokasi' }, // Lokasi
                        { data: 'created_by', defaultContent: '-' }, // Dibuat Oleh
                        { data: 'created_at', defaultContent: '-' }, // Waktu Dibuat
                    ],
                });
            } else {
                lokasiTable.ajax.reload();
            }

            $('#MasterLokasiModal').modal('show');
        });

        function tambahLokasi() {
            let lokasi = $('#txtLokasiBaru').val().trim();

            if (!lokasi) return;

            $('#btnTambahLokasi').prop('disabled', true);

            $.ajax({
                type: 'POST',
                url: '{{ route('store_lokasi_mesin') }}',
                data: {
                    lokasi: lokasi,
                    _token: '{{ csrf_token() }}'
                },
                success: function(res) {
                    $('#txtLokasiBaru').val('').focus();
                    lokasiTable.ajax.reload(null, false);
                    iziToast.success({
                        title: 'Tersimpan',
                        message: res.message,
                        position: 'topCenter',
                        timeout: 1500,
                        close: false,
                        progressBar: false
                    });
                },
                complete: function() {
                    $('#btnTambahLokasi').prop('disabled', false);
                },
                error: function(xhr) {
                    let res = xhr.responseJSON;
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: res?.message ?? 'Gagal menambahkan lokasi.',
                    });
                }
            });
        }

        $('#btnTambahLokasi').on('click', tambahLokasi);

        $('#txtLokasiBaru').on('keyup', function(e) {
            if (e.keyCode === 13) {
                e.preventDefault();
                tambahLokasi();
            }
        });

        let detailTable = null;

        // Pencarian bebas di modal detail
        $('#detailSearch').on('keyup', function() {
            if (detailTable) detailTable.search(this.value).draw();
        });

        // Filter kolom Lokasi (index 7) & Sumber (index 1), dicocokkan persis
        $('#detailFilterLokasi').on('change', function() {
            if (detailTable) detailTable.column(7).search(this.value ? '^' + this.value + '$' : '', true, false).draw();
        });

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
            $('#detailFilterLokasi').val('').trigger('change.select2');
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

                        $body.append(`
                            <tr>
                                <td class="text-center">${i + 1}</td>
                                <td class="text-center">${badge}</td>
                                <td>${r.kode_qr ?? '-'}</td>
                                <td>${r.nm_jenis ?? '-'}</td>
                                <td>${r.nm_merk ?? '-'}</td>
                                <td>${r.tipe ?? '-'}</td>
                                <td>${r.serial_number ?? '-'}</td>
                                <td>${r.lokasi ?? '-'}</td>
                                <td>${r.tgl_opname ?? '-'}</td>
                                <td>${r.created_by ?? '-'}</td>
                            </tr>`);
                    });

                    // Isi dropdown lokasi dari data yang ada, jadi hanya lokasi terpakai yang muncul
                    let daftarLokasi = [...new Set(rows.map(r => r.lokasi).filter(Boolean))].sort();
                    let $lokasi = $('#detailFilterLokasi');
                    $lokasi.find('option:gt(0)').remove();
                    daftarLokasi.forEach(function(lok) {
                        $lokasi.append(`<option value="${lok}">${lok}</option>`);
                    });
                    $lokasi.val('').trigger('change.select2');

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
                        scrollY: '55vh',
                        scrollX: true,
                        scrollCollapse: true,
                        drawCallback: function() {
                            // Rekap mengikuti hasil filter yang sedang tampil
                            let api = this.api();
                            let data = api.rows({ search: 'applied' }).nodes().toArray();
                            let tampil = data.length;
                            let sewaTampil = data.filter(tr => $(tr).find('td:eq(1)').text().trim() === 'SEWA')
                                .length;

                            $('#detailTotal').text(
                                `Tampil : ${tampil} dari ${rows.length} mesin (Pembelian : ${tampil - sewaTampil}, Sewa : ${sewaTampil})`
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
    </script>
@endsection
