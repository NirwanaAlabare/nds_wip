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

        .td-truncate {
            max-width: 220px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
    </style>
@endsection

@section('content')
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-minus"></i> Pengeluaran Mesin (Pembelian)
            </h5>
        </div>
        <div class="card-body">
            <div class="mb-3 d-flex justify-content-between align-items-center">
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                    data-bs-target="#NewMesinModal">
                    <i class="fas fa-plus"></i> New
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
                <button type="button" class="btn btn-success btn-sm" onclick="export_excel_pengeluaran_mesin();">
                    <i class="fas fa-file-excel"></i> Export
                </button>
            </div>
            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-hover align-middle text-nowrap w-100">
                    <thead class="bg-sb">
                        <tr>
                            <th scope="col" class="text-center align-middle">Tgl Keluar</th>
                            <th scope="col" class="text-center align-middle">Serial Number</th>
                            <th scope="col" class="text-center align-middle">Nama Mesin</th>
                            <th scope="col" class="text-center align-middle">Jenis</th>
                            <th scope="col" class="text-center align-middle">Merk</th>
                            <th scope="col" class="text-center align-middle">Tipe</th>
                            <th scope="col" class="text-center align-middle">BPB</th>
                            <th scope="col" class="text-center align-middle">Supplier</th>
                            <th scope="col" class="text-center align-middle">Tgl Terima</th>
                            <th scope="col" class="text-center align-middle">Status</th>
                            <th scope="col" class="text-center align-middle">Dibuat Oleh</th>
                            <th scope="col" class="text-center align-middle">Act</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal New Pengeluaran Mesin -->
    <div class="modal fade" id="NewMesinModal" tabindex="-1" aria-labelledby="NewMesinModalLabel" aria-hidden="true"
        data-bs-backdrop="static">
        <div class="modal-dialog modal-xl" style="max-width: 95vw;">
            <div class="modal-content">
                <div class="modal-header bg-sb text-white">
                    <h5 class="modal-title" id="NewMesinModalLabel">Pengeluaran Mesin (Pembelian)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="input-group input-group-sm" style="max-width: 280px;">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" id="txtSearchMesinAvailable" class="form-control"
                                placeholder="Cari mesin yang tersedia...">
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-secondary">Tersedia: <span id="mesinAvailableCounter">0</span></span>
                            <button type="button" id="btnAddMesinKeluar" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus"></i> Tambah Terpilih
                            </button>
                        </div>
                    </div>
                    <div class="table-responsive mb-3" style="max-height: 260px; overflow-y: auto;">
                        <table id="mesinAvailableTable"
                            class="table table-bordered table-hover table-sm align-middle text-nowrap w-100 mb-0">
                            <thead class="bg-sb" style="position: sticky; top: 0;">
                                <tr>
                                    <th scope="col" class="text-center" style="width: 40px;">
                                        <input type="checkbox" id="chkSelectAllMesin">
                                    </th>
                                    <th scope="col">Serial Number</th>
                                    <th scope="col">Nama Mesin</th>
                                    <th scope="col">Jenis / Merk / Tipe</th>
                                    <th scope="col">BPB</th>
                                    <th scope="col">Tgl Terima</th>
                                </tr>
                            </thead>
                            <tbody id="mesinAvailableTableBody">
                                <tr id="mesinAvailableEmptyRow">
                                    <td colspan="6" class="text-center text-muted">Memuat data...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="mb-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="input-group input-group-sm" style="max-width: 280px;">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" id="txtSearchMesinKeluar" class="form-control"
                                placeholder="Cari mesin yang dipilih...">
                        </div>
                        <span class="badge bg-primary">Total Unit: <span id="mesinKeluarCounter">0</span></span>
                    </div>
                    <div class="table-responsive">
                        <table id="mesinKeluarTable"
                            class="table table-bordered table-hover table-sm align-middle text-nowrap w-100 mb-0">
                            <thead class="bg-sb">
                                <tr>
                                    <th scope="col" class="text-center" style="width: 40px;">No</th>
                                    <th scope="col">Serial Number</th>
                                    <th scope="col">Nama Mesin</th>
                                    <th scope="col">Jenis / Merk / Tipe</th>
                                    <th scope="col">BPB</th>
                                    <th scope="col">Tgl Terima</th>
                                    <th scope="col" style="width: 150px;">Status Keluar</th>
                                    <th scope="col" class="text-center" style="width: 60px;">Act</th>
                                </tr>
                            </thead>
                            <tbody id="mesinKeluarTableBody">
                                <tr id="mesinKeluarEmptyRow">
                                    <td colspan="8" class="text-center text-muted">Belum ada mesin yang dipilih</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" id="btnSimpanMesinKeluar" class="btn btn-success btn-sm">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Sparepart Service: diisi saat status keluar mesin = SERVICE -->
    <div class="modal fade" id="ServiceSparepartModal" tabindex="-1" aria-labelledby="ServiceSparepartModalLabel"
        aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-sb text-white">
                    <h5 class="modal-title" id="ServiceSparepartModalLabel">Sparepart Service</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3 align-items-center">
                        <label for="cboItemSparepart" class="col-md-3 col-form-label"><small><b>Item :</b></small></label>
                        <div class="col-md-9">
                            <select id="cboItemSparepart" class="form-control form-control-sm border-primary"
                                style="width: 100%;">
                                <option value="">-- Cari Item --</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3 align-items-center">
                        <label for="txtDescSparepart" class="col-md-3 col-form-label"><small><b>Keterangan
                                    :</b></small></label>
                        <div class="col-md-7">
                            <input type="text" id="txtDescSparepart" class="form-control form-control-sm"
                                placeholder="Ketik keterangan sparepart...">
                        </div>
                        <div class="col-md-2">
                            <button type="button" id="btnAddSparepart" class="btn btn-primary btn-sm w-100">
                                <i class="fas fa-plus"></i> Add
                            </button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table id="sparepartServiceTable"
                            class="table table-bordered table-sm align-middle text-nowrap w-100 mb-0">
                            <thead class="bg-sb">
                                <tr>
                                    <th scope="col" class="text-center" style="width: 40px;">No</th>
                                    <th scope="col">Kode</th>
                                    <th scope="col">Nama Item</th>
                                    <th scope="col">Keterangan</th>
                                    <th scope="col" class="text-center" style="width: 50px;">Act</th>
                                </tr>
                            </thead>
                            <tbody id="sparepartServiceTableBody">
                                <tr id="sparepartServiceEmptyRow">
                                    <td colspan="5" class="text-center text-muted">Belum ada sparepart ditambahkan</td>
                                </tr>
                            </tbody>
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
        // Select2 Autofocus
        $(document).on('select2:open', () => {
            document.querySelector('.select2-search__field').focus();
        });

        // dropdownParent diarahkan ke modal terdekat agar pencarian tetap bisa diketik
        // (modal Bootstrap 5 menahan focus, sehingga dropdown yang nempel di <body> jadi tidak bisa diketik)
        $('.select2bs4').each(function() {
            let $modal = $(this).closest('.modal');

            $(this).select2({
                theme: 'bootstrap4',
                width: 'resolve',
                dropdownParent: $modal.length ? $modal : $(document.body)
            });
        });
        $('.select2-container--bootstrap4 .select2-selection--single').css({
            'height': '30px',
            'font-size': '12px',
            'line-height': '30px'
        });

        // Ubah format tanggal dari 'YYYY-MM-DD' (format DB) jadi 'DD-MM-YYYY' untuk ditampilkan
        function formatTglIndo(dateStr) {
            if (!dateStr) return '-';
            let parts = dateStr.split('-');
            if (parts.length !== 3) return dateStr;
            return `${parts[2]}-${parts[1]}-${parts[0]}`;
        }

        // Daftar status yang bisa dipilih sebagai alasan mesin keluar, dipasang sebagai dropdown di tiap baris
        let statusOptions = ['REPLACE', 'BREAKDOWN', 'DISPOSE', 'SELL', 'RETUR', 'SERVICE'];

        // Pencarian item sparepart (id_item & nama item dari master item ERP, mysql_sb),
        // sedangkan "Keterangan" tetap diketik manual oleh admin (lihat #txtDescSparepart)
        $('#cboItemSparepart').select2({
            theme: 'bootstrap4',
            width: 'resolve',
            dropdownParent: $('#ServiceSparepartModal'),
            minimumInputLength: 2,
            placeholder: '-- Cari Item --',
            ajax: {
                url: '{{ route('asset_mesin_pengeluaran_search_item') }}',
                dataType: 'json',
                delay: 300,
                cache: true,
                data: function(params) {
                    return {
                        q: params.term
                    };
                },
                processResults: function(data) {
                    return {
                        results: data.map(function(item) {
                            return {
                                id: item.id_item,
                                text: (item.goods_code ? item.goods_code + ' - ' : '') +
                                    (item.itemdesc || '-'),
                                goods_code: item.goods_code,
                                itemdesc: item.itemdesc
                            };
                        })
                    };
                }
            }
        });

        // Default filter tanggal awal & akhir ke tanggal hari ini
        let todayStr = new Date().toISOString().slice(0, 10);
        $('#txttgl_awal').val(todayStr);
        $('#txttgl_akhir').val(todayStr);

        function dataTableReload() {
            datatable.ajax.reload();
        }

        // Export Excel mengikuti filter tanggal yang sedang aktif di tabel utama
        function export_excel_pengeluaran_mesin() {
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
                url: '{{ route('export_excel_pengeluaran_mesin') }}',
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
                    link.download = 'Laporan Pengeluaran Mesin ' + tgl_awal + ' sd ' + tgl_akhir + '.xlsx';
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

        let datatable = $("#datatable").DataTable({
            ordering: false,
            responsive: false,
            processing: true,
            serverSide: false,
            paging: true,
            searching: true,
            scrollY: true,
            scrollX: true,
            scrollCollapse: false,
            ajax: {
                url: '{{ route('asset_mesin_pengeluaran_list') }}',
                data: function(d) {
                    d.tgl_awal = $('#txttgl_awal').val();
                    d.tgl_akhir = $('#txttgl_akhir').val();
                }
            },
            columns: [{
                    data: 'tgl_keluar_fix'
                }, // Tgl Keluar
                {
                    data: 'serial_number',
                    render: function(data) {
                        return data ?? '-';
                    }
                }, // Serial Number
                {
                    data: 'itemdesc',
                    className: 'td-truncate',
                    render: function(data) {
                        return `<span title="${data ?? ''}">${data ?? '-'}</span>`;
                    }
                }, // Nama Mesin
                {
                    data: 'nm_jenis',
                    render: function(data) {
                        return data ?? '-';
                    }
                }, // Jenis
                {
                    data: 'nm_merk',
                    render: function(data) {
                        return data ?? '-';
                    }
                }, // Merk
                {
                    data: 'tipe',
                    render: function(data) {
                        return data ?? '-';
                    }
                }, // Tipe
                {
                    data: 'bpbno_int',
                    render: function(data) {
                        return data ?? '-';
                    }
                }, // BPB
                {
                    data: 'supplier',
                    render: function(data) {
                        return data ?? '-';
                    }
                }, // Supplier
                {
                    data: 'tgl_terima_fix',
                    render: function(data) {
                        return data ?? '-';
                    }
                }, // Tgl Terima
                {
                    data: 'status',
                    render: function(data) {
                        return data ?? '-';
                    }
                }, // Status
                {
                    data: 'created_by',
                    render: function(data) {
                        return data ?? '-';
                    }
                }, // Dibuat Oleh
                {
                    data: null,
                    orderable: false,
                    className: 'text-center',
                    render: function(data, type, row) {
                        if (row.status !== 'SERVICE') return '-';

                        return '<button type="button" class="btn btn-sm btn-outline-secondary btn-lihat-sparepart" data-id="' +
                            row.id_penerimaan + '" data-serial="' + (row.serial_number ?? '-') +
                            '"><i class="fas fa-wrench"></i> Sparepart</button>';
                    }
                }, // Act
            ],
        });

        // Tombol Sparepart di tabel riwayat utama (hanya muncul untuk transaksi berstatus SERVICE):
        // buka modal sparepart untuk unit yang sudah tersimpan tersebut
        $(document).on('click', '.btn-lihat-sparepart', function() {
            let id = $(this).data('id');
            let serial = $(this).data('serial');
            openServiceModal(id, serial);
        });

        // Map id unit -> data mesin (hasil load dropdown), dipakai saat tombol Add diklik supaya tidak perlu request ulang
        let activeMesinMap = {};

        // Mesin yang sudah ditambahkan ke list "mau dikeluarkan" pada sesi modal New saat ini
        let mesinKeluarList = [];

        // Daftar mesin (pembelian) yang masih aktif (belum di-replace), dimuat ulang setiap modal New dibuka
        function loadActiveMesinList() {
            activeMesinMap = {};

            $.ajax({
                type: 'GET',
                url: '{{ route('asset_mesin_pengeluaran_unit_list') }}',
                success: function(rows) {
                    rows.forEach(function(row) {
                        activeMesinMap[row.id] = row;
                    });

                    renderMesinAvailableTable();
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Gagal memuat daftar mesin.',
                    });
                }
            });
        }

        // Bangun ulang tabel mesin yang masih tersedia (checkbox per baris), mesin yang sudah ada di
        // mesinKeluarList disembunyikan supaya tidak bisa dipilih/ditambahkan dobel
        function renderMesinAvailableTable() {
            let $body = $('#mesinAvailableTableBody').empty();
            let pickedIds = mesinKeluarList.map(row => String(row.id));
            let available = Object.values(activeMesinMap).filter(row => !pickedIds.includes(String(row.id)));

            $('#mesinAvailableCounter').text(available.length);
            $('#chkSelectAllMesin').prop('checked', false);

            if (!available.length) {
                $body.append(
                    '<tr id="mesinAvailableEmptyRow"><td colspan="6" class="text-center text-muted">Tidak ada mesin tersedia</td></tr>'
                );
                return;
            }

            available.forEach(function(row) {
                let $tr = $('<tr>').data('unit-id', row.id);

                $tr.append($('<td>', {
                    class: 'text-center'
                }).append($('<input>', {
                    type: 'checkbox',
                    class: 'chk-mesin-available',
                    'data-id': row.id
                })));
                $tr.append($('<td>', {
                    text: row.serial_number || '-'
                }));
                $tr.append($('<td>', {
                    class: 'td-truncate',
                    title: row.itemdesc || '-',
                    text: row.itemdesc || '-'
                }));
                $tr.append($('<td>', {
                    text: [row.nm_jenis, row.nm_merk, row.tipe].filter(Boolean).join(' ') || '-'
                }));
                $tr.append($('<td>', {
                    text: row.bpbno_int || '-'
                }));
                $tr.append($('<td>', {
                    text: formatTglIndo(row.tgl_trans)
                }));

                $body.append($tr);
            });

            filterMesinAvailableTable();
        }

        // Filter baris tabel mesin yang tersedia berdasarkan teks yang diketik di kotak pencarian
        function filterMesinAvailableTable() {
            let keyword = $('#txtSearchMesinAvailable').val().trim().toLowerCase();

            $('#mesinAvailableTableBody tr').each(function() {
                if (!$(this).data('unit-id')) return; // baris status kosong, tidak ikut difilter

                let text = $(this).text().toLowerCase();
                $(this).toggle(text.indexOf(keyword) !== -1);
            });
        }

        $(document).on('input', '#txtSearchMesinAvailable', filterMesinAvailableTable);

        // Checkbox "select all" hanya menandai baris yang sedang terlihat (tidak ikut yang sedang difilter)
        $(document).on('change', '#chkSelectAllMesin', function() {
            let checked = $(this).prop('checked');

            $('#mesinAvailableTableBody tr:visible .chk-mesin-available').prop('checked', checked);
        });

        // Filter baris tabel mesin yang sudah dipilih berdasarkan teks yang diketik di kotak pencarian
        function filterMesinKeluarTable() {
            let keyword = $('#txtSearchMesinKeluar').val().trim().toLowerCase();

            $('#mesinKeluarTableBody tr').each(function() {
                if (!$(this).data('unit-id')) return; // baris status kosong, tidak ikut difilter

                let text = $(this).text().toLowerCase();
                $(this).toggle(text.indexOf(keyword) !== -1);
            });
        }

        $(document).on('input', '#txtSearchMesinKeluar', filterMesinKeluarTable);

        // Status keluar per baris disimpan langsung ke mesinKeluarList saat dropdownnya diganti
        $(document).on('change', '.select-status-keluar', function() {
            let id = $(this).closest('tr').data('unit-id');
            let row = mesinKeluarList.find(r => String(r.id) === String(id));
            if (row) row.status = $(this).val();
        });

        // Unit (id_penerimaan) yang sedang diisi sparepart-nya di ServiceSparepartModal saat ini.
        // Sparepart hanya bisa diisi setelah transaksi pengeluarannya tersimpan (lewat tombol Sparepart
        // di tabel riwayat utama), karena tiap baris langsung tersimpan ke DB lewat AJAX, bukan ditampung dulu di memory
        let currentServiceUnitId = null;

        // Buka modal sparepart untuk unit tertentu (id_penerimaan), dipanggil dari tombol Sparepart di tabel riwayat
        function openServiceModal(unitId, serialNumber) {
            currentServiceUnitId = unitId;

            $('#ServiceSparepartModalLabel').text('Sparepart Service - ' + (serialNumber || '-'));
            $('#cboItemSparepart').val(null).trigger('change');
            $('#txtDescSparepart').val('');
            loadSparepartServiceList();

            $('#ServiceSparepartModal').modal('show');
        }

        // Muat ulang daftar sparepart yang sudah tersimpan untuk currentServiceUnitId dari server
        function loadSparepartServiceList() {
            $('#sparepartServiceTableBody').html(
                '<tr><td colspan="5" class="text-center text-muted">Memuat data...</td></tr>'
            );

            $.ajax({
                type: 'GET',
                url: '{{ route('asset_mesin_pengeluaran_service') }}',
                data: {
                    id_penerimaan: currentServiceUnitId
                },
                success: function(rows) {
                    renderSparepartServiceTable(rows);
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Gagal memuat daftar sparepart.',
                    });
                }
            });
        }

        // Render ulang tabel sparepart di dalam modal, berdasarkan data dari server
        function renderSparepartServiceTable(list) {
            let $body = $('#sparepartServiceTableBody').empty();

            if (!list.length) {
                $body.append(
                    '<tr id="sparepartServiceEmptyRow"><td colspan="5" class="text-center text-muted">Belum ada sparepart ditambahkan</td></tr>'
                );
                return;
            }

            list.forEach(function(item, i) {
                let $tr = $('<tr>');

                $tr.append($('<td>', {
                    class: 'text-center',
                    text: i + 1
                }));
                $tr.append($('<td>', {
                    text: item.goods_code || '-'
                }));
                $tr.append($('<td>', {
                    text: item.itemdesc || '-'
                }));
                $tr.append($('<td>', {
                    text: item.desc || '-'
                }));
                $tr.append(
                    $('<td>', {
                        class: 'text-center'
                    }).append($('<button>', {
                        type: 'button',
                        class: 'btn btn-sm btn-outline-danger btn-remove-sparepart',
                        html: '<i class="fas fa-trash"></i>',
                        'data-id': item.id
                    }))
                );

                $body.append($tr);
            });
        }

        // Tombol Add di modal sparepart: simpan langsung ke server (id_penerimaan + item yang dipilih +
        // keterangan yang diketik manual), lalu muat ulang daftarnya
        $('#btnAddSparepart').on('click', function() {
            let data = $('#cboItemSparepart').select2('data')[0];

            if (!data || !data.id) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Pilih Item Dahulu',
                    text: 'Silakan cari & pilih item sparepart sebelum klik Add.',
                });
                return;
            }

            let $btn = $(this).prop('disabled', true);

            $.ajax({
                type: 'POST',
                url: '{{ route('store_pengeluaran_mesin_service') }}',
                data: {
                    _token: '{{ csrf_token() }}',
                    id_penerimaan: currentServiceUnitId,
                    id_item: data.id,
                    desc: $('#txtDescSparepart').val().trim()
                },
                success: function() {
                    $('#cboItemSparepart').val(null).trigger('change');
                    $('#txtDescSparepart').val('');
                    loadSparepartServiceList();
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Menyimpan',
                        text: xhr.responseJSON?.message || 'Terjadi kesalahan saat menyimpan sparepart.',
                    });
                },
                complete: function() {
                    $btn.prop('disabled', false);
                }
            });
        });

        // Tombol hapus per baris sparepart: hapus langsung di server, lalu muat ulang daftarnya
        $(document).on('click', '.btn-remove-sparepart', function() {
            let id = $(this).data('id');

            $.ajax({
                type: 'DELETE',
                url: '{{ route('delete_pengeluaran_mesin_service', '__ID__') }}'.replace('__ID__', id),
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function() {
                    loadSparepartServiceList();
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Menghapus',
                        text: xhr.responseJSON?.message || 'Terjadi kesalahan saat menghapus sparepart.',
                    });
                }
            });
        });

        // Saat modal sparepart ditutup, refresh tabel riwayat utama
        $('#ServiceSparepartModal').on('hidden.bs.modal', function() {
            dataTableReload();
            currentServiceUnitId = null;
        });

        // Render ulang tabel mesin yang mau dikeluarkan berdasarkan isi mesinKeluarList saat ini
        function renderMesinKeluarTable() {
            let $body = $('#mesinKeluarTableBody').empty();
            $('#mesinKeluarCounter').text(mesinKeluarList.length);

            if (!mesinKeluarList.length) {
                $body.append(
                    '<tr id="mesinKeluarEmptyRow"><td colspan="8" class="text-center text-muted">Belum ada mesin yang dipilih</td></tr>'
                );
                return;
            }

            mesinKeluarList.forEach(function(row, i) {
                let $tr = $('<tr>').data('unit-id', row.id);

                $tr.append($('<td>', {
                    class: 'text-center',
                    text: i + 1
                }));
                $tr.append($('<td>', {
                    text: row.serial_number || '-'
                }));
                $tr.append($('<td>', {
                    class: 'td-truncate',
                    title: row.itemdesc || '-',
                    text: row.itemdesc || '-'
                }));
                $tr.append($('<td>', {
                    text: [row.nm_jenis, row.nm_merk, row.tipe].filter(Boolean).join(' ') || '-'
                }));
                $tr.append($('<td>', {
                    text: row.bpbno_int || '-'
                }));
                $tr.append($('<td>', {
                    text: formatTglIndo(row.tgl_trans)
                }));

                let $statusSelect = $('<select>', {
                    class: 'form-control form-control-sm select-status-keluar',
                    style: 'width: 100%;'
                }).append('<option value="">-- Pilih --</option>');

                statusOptions.forEach(function(opt) {
                    $statusSelect.append($('<option>', {
                        value: opt,
                        text: opt,
                        selected: row.status === opt
                    }));
                });

                $tr.append($('<td>').append($statusSelect));

                $tr.append(
                    $('<td>', {
                        class: 'text-center'
                    }).append($('<button>', {
                        type: 'button',
                        class: 'btn btn-sm btn-outline-danger btn-remove-mesin-keluar',
                        html: '<i class="fas fa-trash"></i>'
                    }))
                );

                $body.append($tr);
            });

            filterMesinKeluarTable();
        }

        // Tombol Tambah Terpilih: ambil semua mesin yang dicentang di tabel tersedia, masukkan ke list
        $('#btnAddMesinKeluar').on('click', function() {
            let ids = $('.chk-mesin-available:checked').map(function() {
                return $(this).data('id');
            }).get();

            if (!ids.length) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Pilih Mesin Dahulu',
                    text: 'Silakan centang minimal 1 mesin sebelum klik Tambah Terpilih.',
                });
                return;
            }

            ids.forEach(function(id) {
                mesinKeluarList.push(activeMesinMap[id]);
            });

            renderMesinKeluarTable();
            renderMesinAvailableTable(); // mesin yang baru ditambahkan disembunyikan dari tabel tersedia
        });

        // Tombol hapus per baris: keluarkan mesin tersebut dari list & munculkan lagi di tabel tersedia
        $(document).on('click', '.btn-remove-mesin-keluar', function() {
            let id = $(this).closest('tr').data('unit-id');
            mesinKeluarList = mesinKeluarList.filter(row => String(row.id) !== String(id));
            renderMesinKeluarTable();
            renderMesinAvailableTable();
        });

        // Tombol Simpan: kirim id unit yang sudah dikumpulkan ke asset_pengeluaran_mesin
        $('#btnSimpanMesinKeluar').on('click', function() {
            if (!mesinKeluarList.length) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Belum Ada Mesin',
                    text: 'Tambahkan minimal 1 mesin sebelum menyimpan.',
                });
                return;
            }

            if (mesinKeluarList.some(row => !row.status)) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Status Belum Lengkap',
                    text: 'Pilih status keluar untuk setiap mesin sebelum menyimpan.',
                });
                return;
            }

            let $btn = $(this).prop('disabled', true);

            $.ajax({
                type: 'POST',
                url: '{{ route('store_pengeluaran_mesin') }}',
                data: {
                    _token: '{{ csrf_token() }}',
                    units: mesinKeluarList.map(row => ({
                        id: row.id,
                        status: row.status
                    }))
                },
                success: function(response) {
                    $('#NewMesinModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil Disimpan',
                        text: response.message,
                        timer: 1800,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Menyimpan',
                        text: xhr.responseJSON?.message ||
                            'Terjadi kesalahan saat menyimpan data.',
                    });
                },
                complete: function() {
                    $btn.prop('disabled', false);
                }
            });
        });

        // Reset list & pencarian setiap kali modal New dibuka
        $('#NewMesinModal').on('show.bs.modal', function() {
            mesinKeluarList = [];
            $('#txtSearchMesinKeluar').val('');
            $('#txtSearchMesinAvailable').val('');
            renderMesinKeluarTable();
            loadActiveMesinList();
        });
    </script>
@endsection
