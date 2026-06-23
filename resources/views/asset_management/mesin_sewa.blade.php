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

        .unit-preview-img {
            height: 160px;
            width: 160px;
            object-fit: cover;
            border-radius: 6px;
            border: 1px solid #ced4da;
            cursor: pointer;
        }

        .unit-preview-zoom-img {
            max-width: 90vw;
            max-height: 80vh;
            object-fit: contain;
            cursor: zoom-in;
            transition: transform 0.1s ease-out;
        }

        .unit-foto-btn {
            height: 32px;
            width: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0;
        }

        .qr-code-img {
            width: 50px;
            height: 50px;
            cursor: pointer;
        }
    </style>
@endsection

@section('content')
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-plus"></i> Sewa Mesin</h5>
        </div>
        <div class="card-body">
            <div class="mb-3 d-flex justify-content-between align-items-center">
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                    data-bs-target="#NewMesinModal">
                    <i class="fas fa-plus"></i> New
                </button>
                <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#QrCodeListModal">
                    <i class="fas fa-qrcode"></i> List Kode QR
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
                <button type="button" class="btn btn-success btn-sm" onclick="export_excel_mesin_sewa();">
                    <i class="fas fa-file-excel"></i> Export
                </button>
            </div>
            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-hover align-middle text-nowrap w-100">
                    <thead class="bg-sb">
                        <tr>
                            <th scope="col" class="text-center align-middle">Tgl Transaksi</th>
                            <th scope="col" class="text-center align-middle">BPB</th>
                            <th scope="col" class="text-center align-middle">Supplier</th>
                            <th scope="col" class="text-center align-middle">Jenis</th>
                            <th scope="col" class="text-center align-middle">Merk</th>
                            <th scope="col" class="text-center align-middle">Nama Mesin</th>
                            <th scope="col" class="text-center align-middle">Tipe</th>
                            <th scope="col" class="text-center align-middle">Total</th>
                            <th scope="col" class="text-center align-middle">Terisi</th>
                            <th scope="col" class="text-center align-middle">Act</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal New Mesin -->
    <div class="modal fade" id="NewMesinModal" tabindex="-1" aria-labelledby="NewMesinModalLabel" aria-hidden="true"
        data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-sb text-white">
                    <h5 class="modal-title" id="NewMesinModalLabel">Sewa Mesin</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3 align-items-center">
                        <label for="cbonomor_bpb" class="col-md-3 col-form-label"><small><b>Nomor BPB :</b></small></label>
                        <div class="col-md-9">
                            <select id="cbonomor_bpb" name="cbonomor_bpb"
                                class="form-control form-control-sm select2bs4 border-primary" style="width: 100%;">
                                <option value="">-- Pilih Nomor BPB --</option>
                                @foreach ($bpbList as $row)
                                    <option value="{{ $row->bpbno }}">{{ $row->bpbno_int }} - {{ $row->supplier }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table id="bpbDetailTable"
                                    class="table table-bordered table-hover table-sm align-middle text-nowrap w-100">
                                    <thead class="bg-sb">
                                        <tr>
                                            <th scope="col" class="text-center align-middle">ID Item</th>
                                            <th scope="col" class="text-center align-middle">Nama Barang</th>
                                            <th scope="col" class="text-center align-middle">Qty</th>
                                            <th scope="col" class="text-center align-middle">Unit</th>
                                            <th scope="col" class="text-center align-middle">Act</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Unit Mesin (input Serial Number & Foto per unit) -->
    <div class="modal fade" id="MesinUnitModal" tabindex="-1" aria-labelledby="MesinUnitModalLabel" aria-hidden="true"
        data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-xl" style="max-width: 95vw;">
            <div class="modal-content">
                <div class="modal-header bg-sb text-white">
                    <div>
                        <h5 class="modal-title mb-0" id="MesinUnitModalLabel">Detail Unit Mesin
                            <span id="unitFilledCounter" class="badge bg-light text-dark ms-1"></span>
                        </h5>
                        <small id="MesinUnitModalSubLabel" class="text-white-50"></small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted mb-2"><small><i class="fas fa-circle-info"></i> Ketik Serial Number, lalu
                            tekan <kbd>Enter</kbd> atau pindah fokus (klik/Tab ke luar) untuk langsung menyimpan baris
                            tersebut.</small></p>
                    <div class="mb-2 d-flex gap-2">
                        <input type="text" id="unitSerialSearch" class="form-control form-control-sm"
                            placeholder="Cari Serial Number...">
                        <select id="unitStatusFilter" class="form-select form-select-sm" style="max-width: 180px;">
                            <option value="">Semua Status</option>
                            <option value="completed">Completed</option>
                            <option value="incomplete">Incomplete</option>
                        </select>
                    </div>
                    <div class="table-responsive">
                        <table id="unitTable" class="table table-bordered table-sm align-middle mb-0">
                            <thead class="bg-sb">
                                <tr>
                                    <th scope="col" class="text-center" style="width: 50px;">No</th>
                                    <th scope="col" class="text-center" style="width: 110px;">QR Code</th>
                                    <th scope="col" style="width: 120px;">Serial Number</th>
                                    <th scope="col" class="text-center" style="width: 280px;">Foto</th>
                                    <th scope="col" style="width: 130px;">Jenis</th>
                                    <th scope="col" style="width: 130px;">Merk</th>
                                    <th scope="col" style="width: 110px;">Tipe</th>
                                    <th scope="col" style="width: 130px;">Tanggal Terima</th>
                                    <th scope="col" style="width: 110px;">Masa Kontrak (Hari)</th>
                                    <th scope="col" style="width: 130px;">Tanggal Akhir Kontrak</th>
                                </tr>
                            </thead>
                            <tbody id="unitTableBody"></tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                </div>
            </div>
        </div>
    </div>

    <!-- Modal List Kode QR -->
    <div class="modal fade" id="QrCodeListModal" tabindex="-1" aria-labelledby="QrCodeListModalLabel"
        aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-sb text-white">
                    <h5 class="modal-title" id="QrCodeListModalLabel">Kode QR</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formAddQrCode" class="row g-2 align-items-end mb-3">
                        <div class="col-md-2">
                            <label class="col-form-label"><small><b>Format :</b></small></label>
                            <input type="text" class="form-control form-control-sm" value="RENT_xxx" disabled>
                        </div>
                        <div class="col-md-3">
                            <label for="txtQrDari" class="col-form-label"><small><b>No. Dari :</b></small></label>
                            <input type="number" id="txtQrDari" min="1" class="form-control form-control-sm"
                                placeholder="1" required>
                        </div>
                        <div class="col-md-3">
                            <label for="txtQrSampai" class="col-form-label"><small><b>No. Sampai :</b></small></label>
                            <input type="number" id="txtQrSampai" min="1" class="form-control form-control-sm"
                                placeholder="10" required>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary btn-sm w-100">
                                <i class="fas fa-plus"></i> Tambah
                            </button>
                        </div>
                    </form>
                    <div class="mb-2 d-flex justify-content-between align-items-center">
                        <div class="input-group input-group-sm" style="max-width: 250px;">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" id="txtSearchQr" class="form-control" placeholder="Cari Kode QR...">
                        </div>
                        <button type="button" id="btnPrintSelectedQr" class="btn btn-success btn-sm">
                            <i class="fas fa-print"></i> Print Terpilih
                        </button>
                    </div>
                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table id="qrCodeTable" class="table table-bordered table-hover table-sm align-middle mb-0">
                            <thead class="bg-sb">
                                <tr>
                                    <th scope="col" class="text-center" style="width: 36px;">
                                        <input type="checkbox" id="chkSelectAllQr">
                                    </th>
                                    <th scope="col" class="text-center" style="width: 50px;">No</th>
                                    <th scope="col" class="text-center" style="width: 90px;">QR</th>
                                    <th scope="col">Kode QR</th>
                                    <th scope="col" class="text-center" style="width: 90px;">Status</th>
                                    <th scope="col">Dibuat Oleh</th>
                                    <th scope="col">Tanggal</th>
                                    <th scope="col" class="text-center" style="width: 100px;">Act</th>
                                </tr>
                            </thead>
                            <tbody id="qrCodeTableBody">
                                @forelse ($qrList as $i => $row)
                                    <tr data-kode-qr="{{ $row->kode_qr }}">
                                        <td class="text-center">
                                            <input type="checkbox" class="qr-row-checkbox" value="{{ $row->kode_qr }}">
                                        </td>
                                        <td class="text-center">{{ $i + 1 }}</td>
                                        <td class="text-center">
                                            <img class="qr-code-img" title="Klik untuk print PDF"
                                                src="data:image/svg+xml;base64,{{ $row->qr }}">
                                        </td>
                                        <td class="qr-code-cell"><span class="qr-code-text">{{ $row->kode_qr }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span
                                                class="badge {{ $row->status === 'USED' ? 'bg-danger' : 'bg-success' }}">
                                                {{ $row->status === 'USED' ? 'USED' : 'AVAILABLE' }}
                                            </span>
                                        </td>
                                        <td>{{ $row->created_by ?? '-' }}</td>
                                        <td>{{ $row->created_at ?? '-' }}</td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-1">
                                                <button type="button" class="btn btn-sm btn-outline-primary btn-edit-qr"
                                                    title="Edit Kode QR">
                                                    <i class="fas fa-pen"></i>
                                                </button>
                                                <button type="button"
                                                    class="btn btn-sm btn-outline-secondary btn-history-qr"
                                                    title="Lihat Pemakaian">
                                                    <i class="fas fa-clock-rotate-left"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted">Belum ada Kode QR</td>
                                    </tr>
                                @endforelse
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

        // Default filter tanggal awal & akhir ke tanggal hari ini
        let todayStr = new Date().toISOString().slice(0, 10);
        $('#txttgl_awal').val(todayStr);
        $('#txttgl_akhir').val(todayStr);

        function dataTableReload() {
            datatable.ajax.reload();
        }

        // Export Excel hanya berisi data Total/Terisi yang tampil di tabel utama (bukan detail per unit)
        function export_excel_mesin_sewa() {
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
                url: '{{ route('export_excel_penerimaan_mesin_sewa') }}',
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
                    link.download = 'Laporan Penerimaan Mesin Sewa ' + tgl_awal + ' sd ' + tgl_akhir + '.xlsx';
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
                url: '{{ route('asset_mesin_sewa_list') }}',
                data: function(d) {
                    d.tgl_awal = $('#txttgl_awal').val();
                    d.tgl_akhir = $('#txttgl_akhir').val();
                }
            },
            columns: [{
                    data: 'tgl_trans_fix'
                }, // Tgl Transaksi
                {
                    data: 'bpbno_int'
                }, // BPB
                {
                    data: 'supplier'
                }, // Supplier
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
                    data: 'itemdesc',
                    className: 'td-truncate',
                    render: function(data) {
                        return `<span title="${data ?? ''}">${data ?? '-'}</span>`;
                    }
                }, // Nama Mesin
                {
                    data: 'tipe',
                    render: function(data) {
                        return data ?? '-';
                    }
                }, // Tipe
                {
                    data: 'tot_qty'
                }, // Total
                {
                    data: null,
                    className: 'text-center',
                    render: function(data, type, row) {
                        let total = parseInt(row.tot_qty) || 0;
                        let complete = parseInt(row.tot_complete) || 0;
                        return `<span class="badge bg-success" title="Komplit: Serial Number & Foto sudah terisi">Complete: ${complete}/${total}</span>`;
                    },
                    orderable: false,
                    searchable: false
                }, // Terisi
                {
                    data: null,
                    className: 'text-center',
                    render: function() {
                        return `
                    <button type="button" class="btn btn-sm btn-primary btn-detail-unit">
                        <i class="fas fa-pen"></i> Detail
                    </button>`;
                    },
                    orderable: false,
                    searchable: false
                }, // Act
            ],
        });

        // Klik tombol Detail pada kolom Act: ambil data baris lewat DataTables API (aman dari karakter spesial)
        $('#datatable tbody').on('click', '.btn-detail-unit', function() {
            let row = datatable.row($(this).closest('tr')).data();
            openMesinUnitModal(row);
        });

        // Master daftar Kode QR (asset_master_mesin_sewa_qr) untuk dropdown pilihan di kolom QR Code
        let qrList = @json($qrList);

        // Paksa huruf besar sambil mengetik (dipakai saat edit Kode QR di tabel)
        $(document).on('input', '.qr-code-edit-input', function() {
            let pos = this.selectionStart;
            this.value = this.value.toUpperCase();
            this.setSelectionRange(pos, pos);
        });

        // Tambah Kode QR baru ke asset_master_mesin_sewa_qr lewat range nomor (format tetap RENT_xxx,
        // bisa generate banyak sekaligus, server yang menolak kalau ada yang sudah duplikat)
        $('#formAddQrCode').on('submit', function(e) {
            e.preventDefault();

            let $btn = $(this).find('button[type=submit]');
            let dari = parseInt($('#txtQrDari').val(), 10);
            let sampai = parseInt($('#txtQrSampai').val(), 10);

            if (!dari || !sampai) return;

            if (sampai < dari) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Range Tidak Valid',
                    text: 'No. Sampai harus lebih besar atau sama dengan No. Dari.',
                });
                return;
            }

            $btn.prop('disabled', true);

            $.ajax({
                type: 'POST',
                url: '{{ route('store_mesin_sewa_qr') }}',
                data: {
                    _token: '{{ csrf_token() }}',
                    dari: dari,
                    sampai: sampai
                },
                success: function(response) {
                    $('#txtQrDari').val('');
                    $('#txtQrSampai').val('');
                    reloadQrCodeTable();
                    Swal.fire({
                        icon: 'success',
                        title: 'Kode QR Disimpan',
                        text: response.message,
                        timer: 1800,
                        showConfirmButton: false
                    });
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Menyimpan',
                        text: xhr.responseJSON?.message ||
                            'Terjadi kesalahan saat menyimpan Kode QR.',
                    });
                },
                complete: function() {
                    $btn.prop('disabled', false);
                }
            });
        });

        // Muat ulang tabel & dropdown pilihan Kode QR setelah ada Kode QR yang disimpan/diubah.
        // Baris dibangun lewat method jQuery (bukan template string) supaya nilai Kode QR yang diketik
        // bebas oleh user otomatis ter-escape, tidak disisipkan mentah sebagai HTML.
        function reloadQrCodeTable() {
            $.ajax({
                type: 'GET',
                url: '{{ route('asset_mesin_sewa_qr_list') }}',
                success: function(rows) {
                    qrList = rows;

                    let $body = $('#qrCodeTableBody').empty();
                    $('#chkSelectAllQr').prop('checked', false);

                    if (!rows.length) {
                        $body.append(
                            '<tr><td colspan="8" class="text-center text-muted">Belum ada Kode QR</td></tr>'
                        );
                        return;
                    }

                    rows.forEach(function(row, i) {
                        let $tr = $('<tr>').data('kode-qr', row.kode_qr);

                        $tr.append(
                            $('<td>', {
                                class: 'text-center'
                            }).append($('<input>', {
                                type: 'checkbox',
                                class: 'qr-row-checkbox',
                                value: row.kode_qr
                            }))
                        );
                        $tr.append($('<td>', {
                            class: 'text-center',
                            text: i + 1
                        }));
                        $tr.append(
                            $('<td>', {
                                class: 'text-center'
                            }).append($('<img>', {
                                class: 'qr-code-img',
                                title: 'Klik untuk print PDF',
                                src: `data:image/svg+xml;base64,${row.qr}`
                            }))
                        );
                        $tr.append(
                            $('<td>', {
                                class: 'qr-code-cell'
                            }).append($('<span>', {
                                class: 'qr-code-text',
                                text: row.kode_qr
                            }))
                        );
                        $tr.append(
                            $('<td>', {
                                class: 'text-center'
                            }).append($('<span>', {
                                class: `badge ${row.status === 'USED' ? 'bg-danger' : 'bg-success'}`,
                                text: row.status === 'USED' ? 'USED' : 'AVAILABLE'
                            }))
                        );
                        $tr.append($('<td>', {
                            text: row.created_by ?? '-'
                        }));
                        $tr.append($('<td>', {
                            text: row.created_at ?? '-'
                        }));
                        $tr.append(
                            $('<td>', {
                                class: 'text-center'
                            }).append(
                                $('<div>', {
                                    class: 'd-flex justify-content-center gap-1'
                                }).append($('<button>', {
                                    type: 'button',
                                    class: 'btn btn-sm btn-outline-primary btn-edit-qr',
                                    title: 'Edit Kode QR',
                                    html: '<i class="fas fa-pen"></i>'
                                })).append($('<button>', {
                                    type: 'button',
                                    class: 'btn btn-sm btn-outline-secondary btn-history-qr',
                                    title: 'Lihat Pemakaian',
                                    html: '<i class="fas fa-clock-rotate-left"></i>'
                                }))
                            )
                        );

                        $body.append($tr);
                    });

                    filterQrCodeTable();
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                }
            });
        }

        // Centang/hapus centang semua baris sekaligus
        $(document).on('change', '#chkSelectAllQr', function() {
            $('.qr-row-checkbox').prop('checked', $(this).is(':checked'));
        });

        // Cari Kode QR di tabel modal berdasarkan teks yang diketik (cocok di kolom Kode QR/Dibuat Oleh)
        function filterQrCodeTable() {
            let keyword = $('#txtSearchQr').val().trim().toLowerCase();
            $('#qrCodeTableBody tr').each(function() {
                if (!$(this).data('kode-qr')) {
                    return;
                }
                let text = $(this).text().toLowerCase();
                $(this).toggle(text.indexOf(keyword) !== -1);
            });
        }

        $(document).on('input', '#txtSearchQr', filterQrCodeTable);

        // Setiap modal dibuka, tabel Kode QR dimuat ulang supaya status Used/Available selalu yang terbaru
        $('#QrCodeListModal').on('show.bs.modal', function() {
            reloadQrCodeTable();
        });

        $('#QrCodeListModal').on('hidden.bs.modal', function() {
            $('#txtSearchQr').val('');
            filterQrCodeTable();
        });

        // Print satu Kode QR langsung dengan klik gambar QR-nya
        $(document).on('click', '.qr-code-img', function() {
            let kodeQr = $(this).closest('tr').data('kode-qr');
            openPrintQrCode([kodeQr]);
        });

        // Print beberapa Kode QR sekaligus sesuai baris yang dicentang
        $('#btnPrintSelectedQr').on('click', function() {
            let selected = $('.qr-row-checkbox:checked').map(function() {
                return $(this).val();
            }).get();

            if (!selected.length) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Belum Ada Yang Dipilih',
                    text: 'Centang minimal 1 Kode QR untuk di-print.',
                });
                return;
            }

            openPrintQrCode(selected);
        });

        // Buka PDF QR Code (1 atau beberapa sekaligus) di tab baru untuk di-print
        function openPrintQrCode(kodeQrArray) {
            let query = $.param({
                kode_qr: kodeQrArray
            });
            window.open(`{{ route('print_mesin_sewa_qr') }}?${query}`, '_blank');
        }

        // Klik tombol Edit mengubah sel Kode QR jadi input untuk diedit di tempat
        $(document).on('click', '.btn-edit-qr', function() {
            let $tr = $(this).closest('tr');
            let $cell = $tr.find('.qr-code-cell');
            let oldVal = $tr.data('kode-qr');

            if ($cell.find('input').length) return; // sedang diedit

            let $input = $('<input>', {
                type: 'text',
                class: 'form-control form-control-sm qr-code-edit-input',
                maxlength: 20
            }).val(oldVal).data('old-kode-qr', oldVal);

            $cell.empty().append($input);
            $input.trigger('focus').trigger('select');
        });

        // Klik tombol History menampilkan unit mesin sewa mana yang sedang memakai Kode QR tersebut
        $(document).on('click', '.btn-history-qr', function() {
            let kodeQr = $(this).closest('tr').data('kode-qr');
            let $btn = $(this).prop('disabled', true);

            $.get('{{ route('asset_mesin_sewa_qr_usage') }}', {
                kode_qr: kodeQr
            }, function(unit) {
                if (!unit) {
                    Swal.fire({
                        icon: 'info',
                        title: kodeQr,
                        text: 'Kode QR ini belum dipakai di unit mesin manapun.',
                    });
                    return;
                }

                Swal.fire({
                    icon: 'info',
                    title: `Dipakai di ${kodeQr}`,
                    html: `
                        <table class="table table-sm table-borderless text-start mb-0">
                            <tr><td><b>BPB</b></td><td>: ${unit.bpbno_int ?? '-'}</td></tr>
                            <tr><td><b>Supplier</b></td><td>: ${unit.supplier ?? '-'}</td></tr>
                            <tr><td><b>Nama Mesin</b></td><td>: ${unit.itemdesc ?? '-'}</td></tr>
                            <tr><td><b>Jenis</b></td><td>: ${unit.nm_jenis ?? '-'}</td></tr>
                            <tr><td><b>Merk</b></td><td>: ${unit.nm_merk ?? '-'}</td></tr>
                            <tr><td><b>Tipe</b></td><td>: ${unit.tipe ?? '-'}</td></tr>
                            <tr><td><b>Serial Number</b></td><td>: ${unit.serial_number ?? '-'}</td></tr>
                            <tr><td><b>Tgl Terima</b></td><td>: ${unit.tgl_awal_kontrak ?? '-'}</td></tr>
                            <tr><td><b>Tgl Akhir Kontrak</b></td><td>: ${unit.tgl_akhir_kontrak ?? '-'}</td></tr>
                        </table>
                    `,
                });
            }).fail(function(xhr) {
                console.error(xhr.responseText);
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Memuat',
                    text: 'Terjadi kesalahan saat mengambil data pemakaian Kode QR.',
                });
            }).always(function() {
                $btn.prop('disabled', false);
            });
        });

        // Kembalikan sel Kode QR ke tampilan teks biasa
        function revertQrCodeCell($cell, value) {
            $cell.empty().append($('<span>', {
                class: 'qr-code-text',
                text: value
            }));
        }

        // Simpan perubahan Kode QR ke asset_master_mesin_sewa_qr (kode_qr adalah primary key, tidak boleh duplikat)
        function saveQrCodeEdit($input) {
            let oldVal = $input.data('old-kode-qr');
            let newVal = $input.val().trim();
            let $tr = $input.closest('tr');
            let $cell = $tr.find('.qr-code-cell');

            if (!newVal || newVal === oldVal) {
                revertQrCodeCell($cell, oldVal);
                return;
            }

            $input.prop('disabled', true);

            $.ajax({
                type: 'POST',
                url: '{{ route('update_mesin_sewa_qr') }}',
                data: {
                    _token: '{{ csrf_token() }}',
                    kode_qr_old: oldVal,
                    kode_qr_new: newVal
                },
                success: function() {
                    reloadQrCodeTable();
                    Swal.fire({
                        icon: 'success',
                        title: 'Kode QR Diubah',
                        timer: 1200,
                        showConfirmButton: false
                    });
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    revertQrCodeCell($cell, oldVal);
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Mengubah',
                        text: xhr.responseJSON?.message ||
                            'Terjadi kesalahan saat mengubah Kode QR.',
                    });
                },
                complete: function() {
                    $input.prop('disabled', false);
                }
            });
        }

        $(document).on('blur', '.qr-code-edit-input', function() {
            saveQrCodeEdit($(this));
        });

        $(document).on('keydown', '.qr-code-edit-input', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                $(this).trigger('blur');
            }
            if (e.key === 'Escape') {
                // Hentikan event di sini supaya hanya membatalkan edit, tidak ikut menutup modal
                // (modal ini pakai data-bs-keyboard="false", penutupan modal ditangani handler Esc global)
                e.stopImmediatePropagation();
                let oldVal = $(this).data('old-kode-qr');
                revertQrCodeCell($(this).closest('.qr-code-cell'), oldVal);
            }
        });

        let currentUnitContext = null;

        // Buka modal Unit Mesin: 1 tab per unit (sejumlah tot_qty), input Serial Number & Foto
        function openMesinUnitModal(row) {
            $('#MesinUnitModalLabel').text(row.itemdesc ?? '-');
            $('#MesinUnitModalSubLabel').html(
                `${row.supplier ?? '-'}</b> &nbsp;|&nbsp; BPB: <b>${row.bpbno_int ?? '-'}</b>`
            );
            $('#unitSerialSearch').val('');
            $('#unitStatusFilter').val('');

            currentUnitContext = {
                id_bpb: row.id_bpb,
                id_item: row.id_item
            };

            loadUnitTable(true);
        }

        // Bangun daftar <option> Kode QR: QR yang sudah USED di unit lain disembunyikan total,
        // kecuali QR yang sedang dipakai unit ini sendiri (currentKodeQr) supaya tetap terpilih.
        function buildQrOptionsHtml(currentKodeQr) {
            return qrList
                .filter(function(q) {
                    return q.status !== 'USED' || String(q.kode_qr) === String(currentKodeQr ?? '');
                })
                .map(function(q) {
                    let selected = String(q.kode_qr) === String(currentKodeQr ?? '') ? 'selected' : '';
                    return `<option value="${q.kode_qr}" ${selected}>${q.kode_qr}</option>`;
                })
                .join('');
        }

        // Setelah salah satu dropdown Kode QR disimpan, opsi di semua dropdown unit lain ikut
        // diperbarui (QR yang baru USED disembunyikan) tanpa reload seluruh tabel supaya tidak blink.
        // Trigger 'change' dipakai supaya tampilan Select2 ikut ter-refresh, tapi ditandai
        // isProgrammaticQrRefresh agar handler simpan-ke-server di bawah tidak ikut terpanggil ulang.
        function refreshAllQrSelectOptions() {
            $('.unit-qr-select').each(function() {
                let $sel = $(this);
                let current = $sel.data('current-kode-qr') || '';
                $sel.html('<option value="">-- Pilih --</option>' + buildQrOptionsHtml(current));
                $sel.val(current).trigger($.Event('change', {
                    isProgrammaticQrRefresh: true
                }));
            });
        }

        // Ambil ulang data unit dari server & render tabel. showModal=true dipakai saat pertama kali dibuka,
        // false dipakai untuk refresh setelah simpan (modal sudah terbuka).
        function loadUnitTable(showModal) {
            if ($.fn.DataTable.isDataTable('#unitTable')) {
                $('#unitTable').DataTable().destroy();
            }

            let $body = $('#unitTableBody').empty();

            $.ajax({
                type: 'GET',
                url: '{{ route('asset_mesin_sewa_unit') }}',
                data: currentUnitContext,
                success: function(units) {
                    units.forEach(function(unit, i) {
                        let imgSrc = unit.foto ?
                            `/nds_wip/public/storage/gambar_penerimaan_mesin_sewa/${unit.foto}` : '';

                        $body.append(`
                    <tr>
                        <td class="text-center align-middle">${i + 1}</td>
                        <td class="text-center align-middle">
                            <select class="form-control form-control-sm unit-qr-select" data-unit-id="${unit.id}"
                                data-current-kode-qr="${unit.kode_qr ?? ''}" style="width: 160px; margin: 0 auto;">
                                <option value="">-- Pilih --</option>
                                ${buildQrOptionsHtml(unit.kode_qr)}
                            </select>
                        </td>
                        <td class="align-middle">
                            <input type="text" class="form-control form-control-sm unit-serial-input"
                                data-unit-id="${unit.id}" value="${unit.serial_number ?? ''}"
                                placeholder="Masukkan Serial Number">
                        </td>
                        <td class="text-center align-middle">
                            <div class="d-flex align-items-center justify-content-center gap-2">
                                <input type="file" class="d-none unit-file-input" data-unit-id="${unit.id}"
                                    accept="image/png, image/jpeg, image/jpg">
                                <button type="button" class="btn btn-sm btn-outline-primary unit-foto-btn"
                                    title="${imgSrc ? 'Ganti Foto' : 'Upload Foto'}">
                                    <i class="fas fa-camera"></i>
                                </button>
                                <img class="unit-preview-img" src="${imgSrc}"
                                    style="display:${imgSrc ? 'inline-block' : 'none'};">
                            </div>
                        </td>
                        <td class="align-middle">
                            <input type="text" class="form-control form-control-sm unit-jenis-input"
                                data-unit-id="${unit.id}" value="${unit.nm_jenis ?? ''}" placeholder="Jenis">
                        </td>
                        <td class="align-middle">
                            <input type="text" class="form-control form-control-sm unit-merk-input"
                                data-unit-id="${unit.id}" value="${unit.nm_merk ?? ''}" placeholder="Merk">
                        </td>
                        <td class="align-middle">
                            <input type="text" class="form-control form-control-sm unit-tipe-input"
                                data-unit-id="${unit.id}" value="${unit.tipe ?? ''}" placeholder="Tipe">
                        </td>
                        <td class="align-middle">
                            <input type="date" class="form-control form-control-sm unit-tgl-terima-input"
                                data-unit-id="${unit.id}" value="${unit.tgl_awal_kontrak ?? ''}">
                        </td>
                        <td class="align-middle">
                            <input type="number" min="1" class="form-control form-control-sm unit-masa-kontrak-input"
                                data-unit-id="${unit.id}" value="${unit.masa_kontrak ?? ''}" placeholder="Hari"
                                readonly>
                        </td>
                        <td class="text-center align-middle">
                            <span class="unit-tgl-akhir-kontrak">${unit.tgl_akhir_kontrak ?? '-'}</span>
                        </td>
                    </tr>`);
                    });

                    $body.find('.unit-serial-input').each(function() {
                        $(this).data('last-saved', $(this).val());
                    });
                    updateUnitFilledCounter();

                    // Select2 supaya dropdown Kode QR bisa di-search & tampilannya rapi (tema Bootstrap4,
                    // sama seperti dropdown Nomor BPB di modal Sewa Mesin)
                    $body.find('.unit-qr-select').select2({
                        theme: 'bootstrap4',
                        width: 'resolve',
                        dropdownParent: $('#MesinUnitModal')
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

                    if (showModal) {
                        $('#MesinUnitModal').modal('show');
                    }
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

        // Hitung & tampilkan jumlah unit yang Serial Number-nya sudah terisi pada header modal
        function updateUnitFilledCounter() {
            let total = $('.unit-serial-input').length;
            let filled = $('.unit-serial-input').filter(function() {
                return $(this).val().trim() !== '';
            }).length;

            $('#unitFilledCounter').text(`${filled}/${total} terisi`);
        }

        // Terapkan ulang filter status Completed/Incomplete saat ada Serial Number/Foto yang baru disimpan
        function redrawUnitTableFilter() {
            if ($('#unitStatusFilter').val() && $.fn.DataTable.isDataTable('#unitTable')) {
                $('#unitTable').DataTable().draw(false);
            }
        }

        // Simpan Serial Number ke server, pakai id baris asset_penerimaan_mesin_sewa sebagai patokan update.
        // Dipakai baik saat tekan Enter maupun saat fokus pindah (blur), supaya user tinggal mengetik.
        function saveUnitSerial($input, refocusAfterSave) {
            let id = $input.data('unit-id');
            let value = $input.val();

            if (value === $input.data('last-saved')) return; // tidak berubah, tidak perlu simpan ulang

            $input.prop('disabled', true).removeClass('is-valid is-invalid');

            let formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append(`units[${id}][id]`, id);
            formData.append(`units[${id}][serial_number]`, value);

            $.ajax({
                type: 'POST',
                url: '{{ route('store_penerimaan_mesin_sewa_unit') }}',
                data: formData,
                contentType: false,
                processData: false,
                success: function() {
                    $input.data('last-saved', value);
                    $input.addClass('is-valid');
                    setTimeout(() => $input.removeClass('is-valid'), 1500);
                    updateUnitFilledCounter();
                    redrawUnitTableFilter();
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    $input.addClass('is-invalid');
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal menyimpan',
                        text: xhr.responseJSON?.message ||
                            'Terjadi kesalahan saat menyimpan Serial Number.',
                    });
                },
                complete: function() {
                    $input.prop('disabled', false);
                    if (refocusAfterSave) $input.trigger('focus');
                }
            });
        }

        // Tekan Enter langsung menyimpan & mempertahankan fokus di kolom yang sama
        $(document).on('keydown', '.unit-serial-input', function(e) {
            if (e.key !== 'Enter') return;
            e.preventDefault();
            saveUnitSerial($(this), true);
        });

        // Pindah fokus (klik/Tab ke kolom lain) juga otomatis menyimpan, tanpa perlu tekan Enter
        $(document).on('blur', '.unit-serial-input', function() {
            saveUnitSerial($(this));
        });

        // Sinkronkan badge Terisi pada tabel utama begitu modal unit ditutup
        $('#MesinUnitModal').on('hidden.bs.modal', function() {
            dataTableReload();
        });

        // Filter baris unit berdasarkan Serial Number & status Completed/Incomplete (dibaca dari value input, bukan teks sel)
        $.fn.dataTable.ext.search.push(function(settings, data, dataIndex, rowData, counter) {
            if (settings.sTableId !== 'unitTable') return true;

            let $tr = $(settings.aoData[dataIndex].nTr);
            let keyword = $('#unitSerialSearch').val().trim().toLowerCase();
            let statusFilter = $('#unitStatusFilter').val();

            let serial = $tr.find('.unit-serial-input').val().toLowerCase();
            if (keyword && !serial.includes(keyword)) return false;

            if (statusFilter) {
                let serialFilled = serial.trim() !== '';
                let fotoFilled = !!$tr.find('.unit-preview-img').attr('src');
                let completed = serialFilled && fotoFilled;

                if (statusFilter === 'completed' && !completed) return false;
                if (statusFilter === 'incomplete' && completed) return false;
            }

            return true;
        });

        $(document).on('keyup', '#unitSerialSearch', function() {
            if ($.fn.DataTable.isDataTable('#unitTable')) {
                $('#unitTable').DataTable().draw();
            }
        });

        $(document).on('change', '#unitStatusFilter', function() {
            if ($.fn.DataTable.isDataTable('#unitTable')) {
                $('#unitTable').DataTable().draw();
            }
        });

        // Klik tombol Foto membuka file picker tersembunyi miliknya
        $(document).on('click', '.unit-foto-btn', function() {
            $(this).siblings('.unit-file-input').trigger('click');
        });

        // Auto-save foto begitu dipilih, pakai id baris asset_penerimaan_mesin_sewa sebagai patokan update
        $(document).on('change', '.unit-file-input', function() {
            let file = this.files[0];
            if (!file) return;

            let $input = $(this);
            let $tr = $input.closest('tr');
            let id = $input.data('unit-id');
            let $btn = $tr.find('.unit-foto-btn');

            let reader = new FileReader();
            reader.onload = function(e) {
                $tr.find('.unit-preview-img').attr('src', e.target.result).show();
                redrawUnitTableFilter();
            };
            reader.readAsDataURL(file);

            let formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append(`units[${id}][id]`, id);
            formData.append(`units[${id}][foto]`, file);

            $btn.prop('disabled', true);

            $.ajax({
                type: 'POST',
                url: '{{ route('store_penerimaan_mesin_sewa_unit') }}',
                data: formData,
                contentType: false,
                processData: false,
                success: function() {
                    $btn.attr('title', 'Ganti Foto').removeClass('btn-outline-primary').addClass(
                        'btn-outline-success');
                    setTimeout(() => $btn.removeClass('btn-outline-success').addClass(
                        'btn-outline-primary'), 1500);
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal menyimpan',
                        text: xhr.responseJSON?.message ||
                            'Terjadi kesalahan saat menyimpan foto.',
                    });
                },
                complete: function() {
                    $btn.prop('disabled', false);
                }
            });
        });

        // Klik thumbnail foto untuk melihat versi lebih besar
        // Klik thumbnail foto untuk melihat versi lebih besar, scroll mouse di atas gambar untuk zoom in/out
        $(document).on('click', '.unit-preview-img', function() {
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
                    document.querySelector('.unit-preview-zoom-img').addEventListener('wheel', function(
                        e) {
                        e.preventDefault();
                        scale = Math.min(Math.max(scale + (e.deltaY < 0 ? 0.2 : -0.2), 1), 4);
                        this.style.transform = `scale(${scale})`;
                    }, {
                        passive: false
                    });
                }
            });
        });

        // Simpan Kode QR yang dipilih dari master asset_master_mesin_sewa_qr langsung saat dropdown berubah.
        // Event sintetis dari refreshAllQrSelectOptions() (sekadar refresh tampilan Select2) diabaikan di sini.
        $(document).on('change', '.unit-qr-select', function(e) {
            if (e.isProgrammaticQrRefresh) return;

            let $select = $(this);
            let id = $select.data('unit-id');
            let oldKodeQr = $select.data('current-kode-qr') || '';
            let newKodeQr = $select.val();

            $select.prop('disabled', true).removeClass('is-valid is-invalid');

            $.ajax({
                type: 'POST',
                url: '{{ route('store_penerimaan_mesin_sewa_unit') }}',
                data: {
                    _token: '{{ csrf_token() }}',
                    units: {
                        [id]: {
                            id: id,
                            kode_qr: newKodeQr
                        }
                    }
                },
                success: function() {
                    $select.addClass('is-valid');
                    setTimeout(() => $select.removeClass('is-valid'), 1500);
                    $select.data('current-kode-qr', newKodeQr);

                    // Sinkronkan status AVAILABLE/USED di qrList lokal (tanpa round-trip ke server)
                    // lalu refresh opsi di semua dropdown unit lain, supaya tidak perlu reload tabel & blink.
                    let oldEntry = qrList.find(q => String(q.kode_qr) === String(oldKodeQr));
                    if (oldEntry) oldEntry.status = 'AVAILABLE';

                    let newEntry = qrList.find(q => String(q.kode_qr) === String(newKodeQr));
                    if (newEntry) newEntry.status = 'USED';

                    refreshAllQrSelectOptions();
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    $select.addClass('is-invalid');
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal menyimpan',
                        text: xhr.responseJSON?.message ||
                            'Terjadi kesalahan saat menyimpan Kode QR.',
                    });
                },
                complete: function() {
                    $select.prop('disabled', false);
                }
            });
        });

        // Simpan field Jenis, Merk & Tipe langsung ke kolom nm_jenis/nm_merk/tipe milik unit yang bersangkutan
        function saveUnitTextField($el, field) {
            let id = $el.data('unit-id');

            $el.prop('disabled', true).removeClass('is-valid is-invalid');

            $.ajax({
                type: 'POST',
                url: '{{ route('store_penerimaan_mesin_sewa_unit') }}',
                data: {
                    _token: '{{ csrf_token() }}',
                    units: {
                        [id]: {
                            id: id,
                            [field]: $el.val()
                        }
                    }
                },
                success: function() {
                    $el.addClass('is-valid');
                    setTimeout(() => $el.removeClass('is-valid'), 1500);
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    $el.addClass('is-invalid');
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal menyimpan',
                        text: xhr.responseJSON?.message ||
                            'Terjadi kesalahan saat menyimpan data.',
                    });
                },
                complete: function() {
                    $el.prop('disabled', false);
                }
            });
        }

        $(document).on('keydown', '.unit-jenis-input, .unit-merk-input, .unit-tipe-input', function(e) {
            if (e.key !== 'Enter') return;
            e.preventDefault();
            $(this).trigger('blur');
        });

        // Paksa huruf besar sambil mengetik, tanpa memindahkan posisi kursor
        $(document).on('input', '.unit-jenis-input, .unit-merk-input, .unit-tipe-input', function() {
            let pos = this.selectionStart;
            this.value = this.value.toUpperCase();
            this.setSelectionRange(pos, pos);
        });

        $(document).on('blur', '.unit-jenis-input', function() {
            saveUnitTextField($(this), 'nm_jenis');
        });

        $(document).on('blur', '.unit-merk-input', function() {
            saveUnitTextField($(this), 'nm_merk');
        });

        $(document).on('blur', '.unit-tipe-input', function() {
            saveUnitTextField($(this), 'tipe');
        });

        // Tanggal Akhir Kontrak = Tanggal Terima + Masa Kontrak (hari), dihitung di browser untuk tampilan
        // (nilai final tetap dihitung ulang & disimpan di server agar konsisten)
        function calcTglAkhirKontrak(tglTerima, masaKontrak) {
            if (!tglTerima || !masaKontrak) return '-';
            let d = new Date(tglTerima + 'T00:00:00');
            d.setDate(d.getDate() + parseInt(masaKontrak));
            return d.toISOString().slice(0, 10);
        }

        // Masa Kontrak readonly, otomatis 30 hari begitu Tanggal Terima diisi
        $(document).on('change', '.unit-tgl-terima-input', function() {
            let $input = $(this);
            let $tr = $input.closest('tr');
            let tglTerima = $input.val();
            let $masaKontrak = $tr.find('.unit-masa-kontrak-input');
            let masaKontrakKosong = !$masaKontrak.val();

            if (tglTerima && masaKontrakKosong) {
                $masaKontrak.val(30);
            }

            $tr.find('.unit-tgl-akhir-kontrak').text(calcTglAkhirKontrak(tglTerima, $masaKontrak.val()));

            saveUnitTextField($input, 'tgl_awal_kontrak');
            if (tglTerima && masaKontrakKosong) {
                saveUnitTextField($masaKontrak, 'masa_kontrak');
            }
        });

        let bpbDetailTable = $("#bpbDetailTable").DataTable({
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
                url: '{{ route('asset_mesin_sewa_bpb_detail') }}',
                data: function(d) {
                    d.bpbno = $('#cbonomor_bpb').val();
                }
            },
            columns: [{
                    data: 'id_item'
                }, // ID Item
                {
                    data: 'itemdesc',
                    className: 'td-truncate',
                    render: function(data) {
                        return `<span title="${data ?? ''}">${data ?? '-'}</span>`;
                    }
                }, // Nama Barang
                {
                    data: 'qty'
                }, // Qty
                {
                    data: 'unit'
                }, // Unit
                {
                    data: null,
                    className: 'text-center',
                    render: function(data, type, row) {
                        return `
                    <button type="button" class="btn btn-sm btn-primary btn-simpan-sewa"
                        onclick='save_penerimaan_mesin_sewa(${row.qty}, "${row.bpbno}", "${row.bpbno_int}", ${row.id_item}, ${row.id})'>
                        <i class="fa fa-check"></i> Simpan
                    </button>`;
                    },
                    orderable: false,
                    searchable: false
                }, // Act
            ],
        });

        // Simpan langsung ke asset_penerimaan_mesin_sewa tanpa perlu pilih Jenis (beda dengan flow Tambah Mesin)
        function save_penerimaan_mesin_sewa(qty, bpbno, bpbnoInt, idItem, idBpb) {
            Swal.fire({
                icon: 'question',
                title: 'Simpan Sewa Mesin?',
                text: 'Akan menambahkan ' + qty + ' unit mesin sewa.',
                showCancelButton: true,
                confirmButtonText: 'Ya',
                cancelButtonText: 'Tidak'
            }).then((result) => {
                if (!result.isConfirmed) {
                    return;
                }

                $.ajax({
                    type: 'POST',
                    url: '{{ route('store_penerimaan_mesin_sewa') }}',
                    data: {
                        _token: '{{ csrf_token() }}',
                        id_item: idItem,
                        id_bpb: idBpb,
                        bpbno: bpbno,
                        bpbno_int: bpbnoInt,
                        qty: qty
                    },
                    success: function(response) {
                        $('#NewMesinModal').modal('hide');

                        Swal.fire({
                            icon: 'success',
                            title: 'Mesin Sewa Disimpan',
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            bpbDetailTable.ajax.reload();
                            dataTableReload();
                        });
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON?.message ||
                                'Terjadi kesalahan saat menyimpan.',
                        });
                    }
                });
            });
        }

        // Reload tabel detail BPB saat nomor BPB dipilih
        $('#cbonomor_bpb').on('change', function() {
            bpbDetailTable.ajax.reload();
        });

        // Tekan Esc menutup satu lapisan saja per tekan: popup preview gambar dulu (kalau sedang terbuka),
        // baru modal yang paling atas. Tekan Esc lagi untuk menutup lapisan di bawahnya, dst.
        // Modal diberi data-bs-keyboard="false" supaya Bootstrap tidak ikut menutup sendiri & bertabrakan dengan ini.
        $(document).on('keydown', function(e) {
            if (e.key !== 'Escape') return;

            if (Swal.isVisible()) {
                Swal.close();
                return;
            }

            let $topModal = $('.modal.show').toArray().sort((a, b) =>
                (parseInt($(b).css('z-index')) || 0) - (parseInt($(a).css('z-index')) || 0)
            )[0];

            if ($topModal) {
                $($topModal).modal('hide');
            }
        });

        // Perbaiki lebar kolom saat modal ditampilkan (DataTables tidak bisa hitung lebar saat modal masih hidden)
        $('#NewMesinModal').on('shown.bs.modal', function() {
            bpbDetailTable.columns.adjust();
        });

        // Reset form setiap kali modal dibuka
        $('#NewMesinModal').on('show.bs.modal', function() {
            $('#cbonomor_bpb').val(null).trigger('change');
        });
    </script>
@endsection
