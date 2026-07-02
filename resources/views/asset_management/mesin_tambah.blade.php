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

        .unit-foto-btn {
            height: 32px;
            width: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0;
        }

        .unit-qr-img {
            height: 160px;
            width: 160px;
            cursor: pointer;
        }

        .unit-preview-zoom-img {
            max-width: 90vw;
            max-height: 80vh;
            object-fit: contain;
            cursor: zoom-in;
            transition: transform 0.1s ease-out;
        }
    </style>
@endsection

@section('content')
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-plus"></i> Tambah Mesin</h5>
        </div>
        <div class="card-body">
            <div class="mb-3">
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
                <button type="button" class="btn btn-success btn-sm" onclick="export_excel_mesin();">
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
                    <h5 class="modal-title" id="NewMesinModalLabel">Tambah Mesin</h5>
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

    <!-- Modal Detail Mesin (per unit sesuai qty) -->
    <div class="modal fade" id="MesinDetailModal" tabindex="-1" aria-labelledby="MesinDetailModalLabel"
        aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-sb text-white">
                    <div>
                        <h5 class="modal-title mb-0">Detail Mesin :</h5>
                        <small id="MesinDetailModalLabel"></small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3 align-items-center">
                        <label for="cbojenis_detail" class="col-md-3 col-form-label"><small><b>Jenis :</b></small></label>
                        <div class="col-md-9">
                            <select id="cbojenis_detail" class="form-control form-control-sm select2bs4 border-primary"
                                style="width: 100%;">
                                <option value="">-- Pilih Jenis --</option>
                            </select>
                        </div>
                    </div>
                    <p class="text-muted mb-0"><small>Akan menambahkan <b><span id="mesinDetailQtyLabel">0</span> unit</b>
                            mesin ke <b>Asset Penerimaan Mesin</b>.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success btn-sm" id="saveMesinDetailButton"
                        onclick="save_penerimaan_mesin();">Save</button>
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Unit Mesin (input Serial Number & Foto per unit) -->
    <div class="modal fade" id="MesinUnitModal" tabindex="-1" aria-labelledby="MesinUnitModalLabel" aria-hidden="true"
        data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-xl">
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
                                    <th scope="col" style="width: 120px;">Serial Number</th>
                                    <th scope="col" class="text-center" style="width: 280px;">Foto</th>
                                    <th scope="col" class="text-center" style="width: 280px;">QR Code</th>
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
@endsection

@section('custom-script')
    <!-- DataTables & Plugins -->
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>

    <script>
        // Modul Asset: senyapkan alert bawaan DataTables saat ajax gagal, cukup dicatat di console
        $.fn.dataTable.ext.errMode = function (settings, techNote, message) {
            console.error('DataTable ajax error:', message);
        };
    </script>
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    {{-- <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables 2.0/dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables 2.0/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables 2.0/dataTables.fixedColumns.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-rowsgroup/dataTables.rowsGroup.js') }}"></script> --}}
    <script>
        // Select2 Autofocus
        $(document).on('select2:open', () => {
            document.querySelector('.select2-search__field').focus();

            // Pastikan dropdown select2 tampil di atas modal/backdrop yang sedang terbuka (modal bertumpuk)
            let maxZ = 1051;
            $('.modal.show').each(function() {
                let z = parseInt($(this).css('z-index')) || 0;
                if (z > maxZ) maxZ = z;
            });
            $('.select2-dropdown').last().css('z-index', maxZ + 10);
        });

        // Initialize Select2 Elements
        $('.select2').select2();

        // Initialize Select2BS4 Elements
        // dropdownParent diarahkan ke modal terdekat agar pencarian tetap bisa diketik
        // (modal Bootstrap 5 menahan focus, sehingga dropdown yang nempel di <body> jadi tidak bisa diketik)
        $('.select2bs4').each(function() {
            let $modal = $(this).closest('.modal');

            $(this).select2({
                theme: 'bootstrap4',
                width: 'resolve', // Ensures it respects the 100% width from inline style or Bootstrap
                dropdownParent: $modal.length ? $modal : $(document.body)
            });
        });
        // Now set height and font-size on the Select2 container after init
        $('.select2-container--bootstrap4 .select2-selection--single').css({
            'height': '30px', // your desired height
            'font-size': '12px', // your desired font size
            'line-height': '30px' // vertically center text
        });

        function notif() {
            alert("Maaf, Fitur belum tersedia!");
        }

        // Master jenis mesin untuk dropdown Jenis di modal Detail Mesin (difilter per id_supplier)
        const jenisList = @json($jenisList);

        // Default filter tanggal awal & akhir ke tanggal hari ini
        let todayStr = new Date().toISOString().slice(0, 10);
        $('#txttgl_awal').val(todayStr);
        $('#txttgl_akhir').val(todayStr);

        // Klik di mana saja pada input tanggal akan membuka date picker
        document.getElementById('txttanggal_transaksi').addEventListener('click', function() {
            this.showPicker();
        });
    </script>
    <script>
        function dataTableReload() {
            datatable.ajax.reload();
        }

        // Export Excel hanya berisi data Total/Terisi yang tampil di tabel utama (bukan detail per unit)
        function export_excel_mesin() {
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
                url: '{{ route('export_excel_penerimaan_mesin') }}',
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
                    link.download = 'Laporan Penerimaan Mesin ' + tgl_awal + ' sd ' + tgl_akhir + '.xlsx';
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
                url: '{{ route('asset_mesin_tambah_list') }}',
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
                    data: 'nm_jenis'
                }, // Jenis
                {
                    data: 'nm_merk'
                }, // Merk
                {
                    data: 'itemdesc',
                    className: 'td-truncate',
                    render: function(data) {
                        return `<span title="${data ?? ''}">${data ?? '-'}</span>`;
                    }
                }, // Nama Mesin
                {
                    data: 'tipe'
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
                url: '{{ route('asset_mesin_tambah_bpb_detail') }}',
                data: function(d) {
                    d.bpbno = $('#cbonomor_bpb').val();
                }
            },
            columns: [{
                    data: 'id_item'
                }, // Bpb No
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
                    render: function(data, type, row) {
                        return `
                    <div class="text-center">
                        <button type="button" class="btn btn-sm btn-primary"
                            onclick='openMesinDetailModal(${row.qty}, "${row.bpbno}", "${row.bpbno_int}", ${row.id_supplier}, "${row.itemdesc}", ${row.id_item}, ${row.id})'>
                            <i class="fa fa-check"></i> Simpan
                        </button>
                    </div>`;
                    },
                    orderable: false,
                    searchable: false
                }
            ],
        });

        // Buka modal Detail Mesin: dropdown Jenis difilter berdasarkan id_supplier BPB yang sama
        let mesinDetailQty = 0;
        let mesinDetailBpbno = '';
        let mesinDetailBpbnoInt = '';
        let mesinDetailIdItem = null;
        let mesinDetailIdBpb = null;

        function openMesinDetailModal(qty, bpbno, bpbnoInt, idSupplier, itemdesc, idItem, idBpb) {
            mesinDetailQty = parseInt(qty) || 0;
            mesinDetailBpbno = bpbno;
            mesinDetailBpbnoInt = bpbnoInt;
            mesinDetailIdItem = idItem;
            mesinDetailIdBpb = idBpb;

            let options = jenisList
                .filter(j => String(j.id_supplier) === String(idSupplier))
                .map(j => `<option value="${j.id_jenis}">${j.jenis} - ${j.merk} - ${j.tipe}</option>`)
                .join('');

            $('#cbojenis_detail').html('<option value="">-- Pilih Jenis --</option>' + options).val('')
                .trigger('change');
            $('#mesinDetailQtyLabel').text(mesinDetailQty);
            $('#MesinDetailModalLabel').text(itemdesc);
            $('#MesinDetailModal').modal('show');
        }

        function save_penerimaan_mesin() {
            let id_jenis = $('#cbojenis_detail').val();

            if (!id_jenis) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Jenis wajib diisi',
                });
                return;
            }

            Swal.fire({
                icon: 'question',
                title: 'Simpan Penerimaan Mesin?',
                text: 'Akan menambahkan ' + mesinDetailQty + ' unit mesin sesuai jenis yang dipilih.',
                showCancelButton: true,
                confirmButtonText: 'Ya',
                cancelButtonText: 'Tidak'
            }).then((result) => {
                if (!result.isConfirmed) {
                    return;
                }

                let $btn = $('#saveMesinDetailButton');
                $btn.prop('disabled', true);

                $.ajax({
                    type: "POST",
                    url: '{{ route('store_penerimaan_mesin') }}',
                    data: {
                        _token: '{{ csrf_token() }}',
                        id_item: mesinDetailIdItem,
                        id_bpb: mesinDetailIdBpb,
                        bpbno: mesinDetailBpbno,
                        bpbno_int: mesinDetailBpbnoInt,
                        id_jenis: id_jenis,
                        qty: mesinDetailQty
                    },
                    success: function(response) {
                        // Tutup modal anak dulu, baru modal induk setelah benar-benar selesai (hidden.bs.modal),
                        // supaya backdrop modal yang bertumpuk tidak ada yang tersisa nyangkut
                        $('#MesinDetailModal').one('hidden.bs.modal', function() {
                            $('#NewMesinModal').modal('hide');
                        });
                        $('#MesinDetailModal').modal('hide');

                        Swal.fire({
                            icon: 'success',
                            title: 'Mesin Diterima',
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
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
                    },
                    complete: function() {
                        $btn.prop('disabled', false);
                    }
                });
            });
        }

        // Buka modal Unit Mesin: 1 tab per unit (sejumlah tot_qty), input Serial Number & Foto
        function openMesinUnitModal(row) {
            $('#MesinUnitModalLabel').text(row.itemdesc ?? '-');
            $('#MesinUnitModalSubLabel').html(
                `Jenis: <b>${row.nm_jenis ?? '-'} - ${row.nm_merk ?? '-'} - ${row.tipe ?? '-'}</b> &nbsp;|&nbsp; Supplier: <b>${row.supplier ?? '-'}</b> &nbsp;|&nbsp; BPB: <b>${row.bpbno_int ?? '-'}</b>`
            );

            if ($.fn.DataTable.isDataTable('#unitTable')) {
                $('#unitTable').DataTable().destroy();
            }

            let $body = $('#unitTableBody').empty();
            $('#unitSerialSearch').val('');
            $('#unitStatusFilter').val('');

            $.ajax({
                type: 'GET',
                url: '{{ route('asset_mesin_tambah_unit') }}',
                data: {
                    id_bpb: row.id_bpb,
                    id_item: row.id_item
                },
                success: function(units) {
                    units.forEach(function(unit, i) {
                        let imgSrc = unit.foto ?
                            `/nds_wip/public/storage/gambar_penerimaan_mesin/${unit.foto}` : '';
                        let qrSrc = unit.qr ? `data:image/svg+xml;base64,${unit.qr}` : '';

                        $body.append(`
                    <tr>
                        <td class="text-center align-middle">${i + 1}</td>
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
                        <td class="text-center align-middle">
                            ${qrSrc ? `
                                                    <img class="unit-qr-img" src="${qrSrc}" data-unit-id="${unit.id}"
                                                        title="Klik untuk print PDF" style="display:none;">
                                                    <span class="unit-qr-locked text-muted"
                                                        title="Isi Serial Number & Foto dahulu"><i class="fas fa-lock"></i></span>
                                                ` : '<span class="text-muted">-</span>'}
                        </td>
                    </tr>`);
                    });

                    $body.find('tr').each(function() {
                        refreshUnitQrState($(this));
                        let $serialInput = $(this).find('.unit-serial-input');
                        $serialInput.data('last-saved', $serialInput.val());
                    });
                    updateUnitFilledCounter();

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

        // Hitung & tampilkan jumlah unit yang Serial Number-nya sudah terisi pada header modal
        function updateUnitFilledCounter() {
            let total = $('.unit-serial-input').length;
            let filled = $('.unit-serial-input').filter(function() {
                return $(this).val().trim() !== '';
            }).length;

            $('#unitFilledCounter').text(`${filled}/${total} terisi`);
        }

        // QR Code hanya ditampilkan kalau Serial Number & Foto unit tersebut sudah terisi
        function refreshUnitQrState($tr) {
            let $qrImg = $tr.find('.unit-qr-img');
            if (!$qrImg.length) return; // unit ini tidak punya kode_qr sama sekali

            let serialFilled = $tr.find('.unit-serial-input').val().trim() !== '';
            let fotoFilled = !!$tr.find('.unit-preview-img').attr('src');

            $qrImg.toggle(serialFilled && fotoFilled);
            $tr.find('.unit-qr-locked').toggle(!(serialFilled && fotoFilled));
        }

        // Terapkan ulang filter status Completed/Incomplete saat ada Serial Number/Foto yang baru disimpan
        function redrawUnitTableFilter() {
            if ($('#unitStatusFilter').val() && $.fn.DataTable.isDataTable('#unitTable')) {
                $('#unitTable').DataTable().draw(false);
            }
        }

        // Simpan Serial Number ke server, pakai id baris asset_penerimaan_mesin sebagai patokan update.
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
                url: '{{ route('store_penerimaan_mesin_unit') }}',
                data: formData,
                contentType: false,
                processData: false,
                success: function() {
                    $input.data('last-saved', value);
                    $input.addClass('is-valid');
                    setTimeout(() => $input.removeClass('is-valid'), 1500);
                    updateUnitFilledCounter();
                    refreshUnitQrState($input.closest('tr'));
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

        // Auto-save foto begitu dipilih, pakai id baris asset_penerimaan_mesin sebagai patokan update
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
                refreshUnitQrState($tr);
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
                url: '{{ route('store_penerimaan_mesin_unit') }}',
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

        // Klik QR Code untuk membuka PDF di tab baru, siap di-print / disimpan sebagai PDF
        $(document).on('click', '.unit-qr-img', function() {
            let unitId = $(this).data('unit-id');
            window.open(`{{ url('/asset_mesin_tambah/unit') }}/${unitId}/print_qr`, '_blank');
        });

        // Stacking z-index agar modal kedua tampil rapi di atas modal pertama
        $(document).on('show.bs.modal', '.modal', function() {
            let zIndex = 1050 + (10 * $('.modal.show').length);
            $(this).css('z-index', zIndex);
        });

        $(document).on('shown.bs.modal', '.modal', function() {
            $('.modal-backdrop').not('.modal-stack').css('z-index', $(this).css('z-index') - 1).addClass(
                'modal-stack');
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

        // Reload tabel detail BPB saat nomor BPB dipilih
        $('#cbonomor_bpb').on('change', function() {
            bpbDetailTable.ajax.reload();
        });

        // Perbaiki lebar kolom saat modal ditampilkan (DataTables tidak bisa hitung lebar saat modal masih hidden)
        $('#NewMesinModal').on('shown.bs.modal', function() {
            bpbDetailTable.columns.adjust();
        });

        // Reset form setiap kali modal dibuka
        $('#NewMesinModal').on('show.bs.modal', function() {
            $('#txttanggal_transaksi').val('{{ now()->format('Y-m-d') }}');
            $('#cbonomor_bpb').val(null).trigger('change');
        });
    </script>
@endsection
