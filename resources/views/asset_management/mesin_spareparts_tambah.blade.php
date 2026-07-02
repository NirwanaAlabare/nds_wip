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
    </style>
@endsection

@section('content')
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-plus"></i> Tambah Spareparts (Pembelian)</h5>
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
                <button type="button" class="btn btn-success btn-sm" onclick="export_excel_spareparts_mesin();">
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
                            <th scope="col" class="text-center align-middle">Nama Barang</th>
                            <th scope="col" class="text-center align-middle">Rak</th>
                            <th scope="col" class="text-center align-middle">Qty</th>
                            <th scope="col" class="text-center align-middle">Dibuat Oleh</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal New Spareparts Mesin -->
    <div class="modal fade" id="NewMesinModal" tabindex="-1" aria-labelledby="NewMesinModalLabel" aria-hidden="true"
        data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-sb text-white">
                    <h5 class="modal-title" id="NewMesinModalLabel">Tambah Spareparts Mesin</h5>
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
                                            <th scope="col" class="text-center align-middle">Selisih</th>
                                            <th scope="col" class="text-center align-middle">Unit</th>
                                            <th scope="col" class="text-center align-middle">Act</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
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

    <!-- Modal Detail Spareparts Mesin (per unit sesuai qty) -->
    <div class="modal fade" id="MesinDetailModal" tabindex="-1" aria-labelledby="MesinDetailModalLabel"
        aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-sb text-white">
                    <div>
                        <h5 class="modal-title mb-0">Detail Spareparts Mesin :</h5>
                        <small id="MesinDetailModalLabel"></small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info py-2 mb-3 d-flex justify-content-between align-items-center">
                        <span><small>Sisa qty belum diterima :</small></span>
                        <strong><span id="sparepartSisaQtyLabel">0</span> <span id="sparepartUnitLabel"></span></strong>
                    </div>
                    <div class="table-responsive mb-2">
                        <table id="rakAlokasiTable" class="table table-bordered table-sm align-middle mb-0">
                            <thead class="bg-sb">
                                <tr>
                                    <th scope="col">Rak</th>
                                    <th scope="col" style="width: 140px;">Qty</th>
                                    <th scope="col" class="text-center" style="width: 50px;">Act</th>
                                </tr>
                            </thead>
                            <tbody id="rakAlokasiTableBody"></tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <button type="button" class="btn btn-sm btn-outline-primary" id="btnTambahRak">
                            <i class="fas fa-plus"></i> Tambah Rak
                        </button>
                        <small class="text-muted">Total dialokasikan: <strong id="totalAlokasiLabel">0</strong></small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success btn-sm" id="saveMesinDetailButton"
                        onclick="save_penerimaan_spareparts_mesin();">Save</button>
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
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
        $.fn.dataTable.ext.errMode = function (settings, techNote, message) {
            console.error('DataTable ajax error:', message);
        };
    </script>

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

        // Master rak sparepart untuk dropdown Rak di modal Detail Spareparts Mesin, dimuat via AJAX
        let rakList = [];

        function loadRakList() {
            $.ajax({
                type: 'GET',
                url: '{{ route('asset_mesin_spareparts_tambah_rak_select') }}',
                success: function(rows) {
                    rakList = rows;
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Gagal memuat daftar rak.',
                    });
                }
            });
        }

        loadRakList();

        // Default filter tanggal awal & akhir ke tanggal hari ini
        let todayStr = new Date().toISOString().slice(0, 10);
        $('#txttgl_awal').val(todayStr);
        $('#txttgl_akhir').val(todayStr);
    </script>
    <script>
        function dataTableReload() {
            datatable.ajax.reload();
        }

        function export_excel_spareparts_mesin() {
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
                url: '{{ route('export_excel_spareparts_mesin') }}',
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
                    link.download = 'Laporan Penerimaan Spareparts Mesin ' + tgl_awal + ' sd ' + tgl_akhir +
                        '.xlsx';
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
                url: '{{ route('asset_mesin_spareparts_tambah_list') }}',
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
                    data: 'supplier',
                    render: function(data) {
                        return data ?? '-';
                    }
                }, // Supplier
                {
                    data: 'itemdesc',
                    className: 'td-truncate',
                    render: function(data) {
                        return `<span title="${data ?? ''}">${data ?? '-'}</span>`;
                    }
                }, // Nama Barang
                {
                    data: null,
                    render: function(data, type, row) {
                        return [row.no_rak, row.nm_rak].filter(Boolean).join(' - ') || '-';
                    }
                }, // Rak
                {
                    data: 'qty'
                }, // Qty
                {
                    data: 'created_by',
                    render: function(data) {
                        return data ?? '-';
                    }
                }, // Dibuat Oleh
            ],
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
                url: '{{ route('asset_mesin_spareparts_tambah_bpb_detail') }}',
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
                    data: 'selisih'
                }, // Selisih
                {
                    data: 'unit'
                }, // Unit
                {
                    data: null,
                    render: function() {
                        return `
                    <div class="text-center">
                        <button type="button" class="btn btn-sm btn-primary btn-detail-sparepart">
                            <i class="fa fa-check"></i> Simpan
                        </button>
                    </div>`;
                    },
                    orderable: false,
                    searchable: false
                }
            ],
        });

        // Klik Simpan pada baris BPB detail: ambil data baris lewat DataTables API, buka modal alokasi rak
        $('#bpbDetailTable tbody').on('click', '.btn-detail-sparepart', function() {
            let row = bpbDetailTable.row($(this).closest('tr')).data();
            openSparepartDetailModal(row);
        });

        // State modal Detail Spareparts Mesin: qty sisa satu baris BPB bisa displit ke beberapa rak sekaligus
        let sparepartDetailSisa = 0;
        let sparepartDetailBpbno = '';
        let sparepartDetailBpbnoInt = '';
        let sparepartDetailIdItem = null;
        let sparepartDetailIdBpb = null;
        let sparepartDetailUnit = '';
        let rakAlokasiList = [];

        function openSparepartDetailModal(row) {
            sparepartDetailSisa = parseInt(row.selisih) || 0;
            sparepartDetailBpbno = row.bpbno;
            sparepartDetailBpbnoInt = row.bpbno_int;
            sparepartDetailIdItem = row.id_item;
            sparepartDetailIdBpb = row.id;
            sparepartDetailUnit = row.unit ?? '';
            rakAlokasiList = [];

            $('#MesinDetailModalLabel').text(row.itemdesc ?? '-');
            $('#sparepartSisaQtyLabel').text(sparepartDetailSisa);
            $('#sparepartUnitLabel').text(sparepartDetailUnit);

            renderRakAlokasiTable();
            $('#MesinDetailModal').modal('show');
        }

        // Bangun opsi dropdown Rak dari master rakList, opsi yang sedang terpilih ditandai selected
        function buildRakOptions(selectedId) {
            let options = rakList.map(r => {
                let label = `${r.no_rak ?? '-'} - ${r.nm_rak ?? '-'}` + (r.desc ? ` (${r.desc})` : '');
                let selected = String(r.id_rak) === String(selectedId) ? 'selected' : '';

                return `<option value="${r.id_rak}" title="${r.desc ?? ''}" ${selected}>${label}</option>`;
            }).join('');

            return '<option value="">-- Pilih Rak --</option>' + options;
        }

        // Render ulang baris alokasi rak (select Rak + input Qty per baris) & hitung ulang total dialokasikan
        function renderRakAlokasiTable() {
            let $body = $('#rakAlokasiTableBody').empty();

            if (!rakAlokasiList.length) {
                $body.append(
                    '<tr id="rakAlokasiEmptyRow"><td colspan="3" class="text-center text-muted">Belum ada alokasi rak, klik "Tambah Rak"</td></tr>'
                );
            } else {
                rakAlokasiList.forEach(function(alokasi, i) {
                    let $tr = $('<tr>').data('index', i);

                    let $rakSelect = $('<select>', {
                        class: 'form-control form-control-sm rak-alokasi-select'
                    }).html(buildRakOptions(alokasi.id_rak));

                    let $qtyInput = $('<input>', {
                        type: 'number',
                        min: 1,
                        class: 'form-control form-control-sm rak-alokasi-qty',
                        value: alokasi.qty || ''
                    });

                    $tr.append($('<td>').append($rakSelect));
                    $tr.append($('<td>').append($qtyInput));
                    $tr.append(
                        $('<td>', {
                            class: 'text-center'
                        }).append($('<button>', {
                            type: 'button',
                            class: 'btn btn-sm btn-outline-danger btn-remove-rak',
                            html: '<i class="fas fa-trash"></i>'
                        }))
                    );

                    $body.append($tr);
                });

                // Select Rak dirender ulang (elemen baru) tiap kali tabel di-render, jadi select2-nya diinisialisasi
                // ulang di sini supaya tampilannya Bootstrap & bisa dicari/diketik
                $('#rakAlokasiTableBody .rak-alokasi-select').select2({
                    theme: 'bootstrap4',
                    width: 'resolve',
                    dropdownParent: $('#MesinDetailModal')
                });
            }

            updateTotalAlokasi();
        }

        // Hitung total qty yang sudah dialokasikan dari semua baris, tandai merah kalau melebihi sisa
        function updateTotalAlokasi() {
            let total = rakAlokasiList.reduce((sum, a) => sum + (parseInt(a.qty) || 0), 0);

            $('#totalAlokasiLabel').text(total).toggleClass('text-danger', total > sparepartDetailSisa);
        }

        $('#btnTambahRak').on('click', function() {
            rakAlokasiList.push({
                id_rak: '',
                qty: ''
            });
            renderRakAlokasiTable();
        });

        $(document).on('click', '.btn-remove-rak', function() {
            let index = $(this).closest('tr').data('index');
            rakAlokasiList.splice(index, 1);
            renderRakAlokasiTable();
        });

        $(document).on('change', '.rak-alokasi-select', function() {
            let index = $(this).closest('tr').data('index');
            rakAlokasiList[index].id_rak = $(this).val();
        });

        $(document).on('input', '.rak-alokasi-qty', function() {
            let index = $(this).closest('tr').data('index');
            rakAlokasiList[index].qty = $(this).val();
            updateTotalAlokasi();
        });

        function save_penerimaan_spareparts_mesin() {
            if (!rakAlokasiList.length) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Belum Ada Alokasi Rak',
                    text: 'Tambahkan minimal 1 alokasi rak sebelum menyimpan.',
                });
                return;
            }

            if (rakAlokasiList.some(a => !a.id_rak || !a.qty || parseInt(a.qty) <= 0)) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Alokasi Belum Lengkap',
                    text: 'Pilih rak & isi qty (lebih dari 0) untuk setiap baris alokasi.',
                });
                return;
            }

            let totalAlokasi = rakAlokasiList.reduce((sum, a) => sum + parseInt(a.qty), 0);

            if (totalAlokasi > sparepartDetailSisa) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Qty Melebihi Sisa',
                    text: `Total dialokasikan (${totalAlokasi}) melebihi sisa qty yang belum diterima (${sparepartDetailSisa}).`,
                });
                return;
            }

            Swal.fire({
                icon: 'question',
                title: 'Simpan Penerimaan Spareparts Mesin?',
                text: 'Akan menambahkan ' + totalAlokasi + ' ' + sparepartDetailUnit +
                    ' sparepart ke rak yang dipilih.',
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
                    url: '{{ route('store_penerimaan_spareparts_mesin') }}',
                    data: {
                        _token: '{{ csrf_token() }}',
                        id_item: sparepartDetailIdItem,
                        id_bpb: sparepartDetailIdBpb,
                        bpbno: sparepartDetailBpbno,
                        bpbno_int: sparepartDetailBpbnoInt,
                        unit: sparepartDetailUnit,
                        rak: rakAlokasiList.map(a => ({
                            id_rak: a.id_rak,
                            qty: a.qty
                        }))
                    },
                    success: function(response) {
                        // Tutup modal Detail dulu, baru muat ulang daftar baris BPB (selisihnya sudah berubah)
                        $('#MesinDetailModal').one('hidden.bs.modal', function() {
                            bpbDetailTable.ajax.reload();
                        });
                        $('#MesinDetailModal').modal('hide');

                        Swal.fire({
                            icon: 'success',
                            title: 'Spareparts Mesin Diterima',
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
            $('#cbonomor_bpb').val(null).trigger('change');
        });
    </script>
@endsection
