@extends('layouts.index')

@section('custom-link')
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables 2.0/jquery.dataTables.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables 2.0/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables 2.0/fixedColumns.bootstrap4.min.css') }}">
    <!-- jQuery -->
    <script src="{{ asset('plugins/datatables 2.0/jquery-3.3.1.js') }}"></script>


    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

    <style>
        .select2-container--bootstrap4 .select2-selection--single {
            height: 31px !important;
            padding: 0.25rem 0.5rem !important;
            font-size: 0.875rem !important;
        }

        .select2-container--bootstrap4 .select2-selection__rendered {
            line-height: 1.5 !important;
        }
    </style>

    <style>
        /* Responsive Scanner Box */
        .scanner-box {
            width: 100%;
            max-width: 320px;
            aspect-ratio: 1 / 1;
            margin: 0 auto;
        }

        @media (max-width: 400px) {
            .scanner-box {
                max-width: 260px;
            }
        }

        .scanner-box-fabric {
            width: 100%;
            max-width: 320px;
            aspect-ratio: 1 / 1;
            margin: 0 auto;
        }

        @media (max-width: 400px) {
            .scanner-box-fabric {
                max-width: 260px;
            }
        }
    </style>
@endsection

@section('content')
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-list"></i> Process Shade Band</h5>
        </div>
        <div class="card-body pb-0">
            <div class="row mb-3  g-2">
                <!-- Input Group -->
                <div class="mb-3">
                    <label for="txtbarcode_scan" class="form-label label-input">
                        <small><b>Barcode</b></small>
                    </label>
                    <input type="hidden" class="form-control form-control-sm" id="txtuser" name="txtuser"
                        value="{{ $user }}">
                    <div class="input-group input-group-sm">
                        <input type="text" class="form-control border-input form-control-sm" name="txtbarcode_scan"
                            id="txtbarcode_scan" autocomplete="off" enterkeyhint="go">

                        <span class="input-group-text bg-light text-muted py-0 px-2">
                            <small>Scan</small>
                        </span>

                        <span id="btnScanFabric" onclick="check_barcode()"
                            class="input-group-text bg-light text-muted py-0 px-2" role="button" style="cursor: pointer;">
                            <small>Scan</small>
                        </span>
                    </div>
                </div>

                <!-- QR Scanner -->
                <div class="d-flex justify-content-center">
                    <div id="reader_fabric" class="scanner-box-fabric"></div>
                </div>
            </div>
        </div>

        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-list"></i> List Barcode</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-hover align-middle text-nowrap w-100">
                    <thead class="bg-sb">
                        <tr>
                            <th scope="col" class="text-center align-middle">Act</th>
                            <th scope="col" class="text-center align-middle">Barcode</th>
                            <th scope="col" class="text-center align-middle">No. PL</th>
                            <th scope="col" class="text-center align-middle">Supplier</th>
                            <th scope="col" class="text-center align-middle">Buyer</th>
                            <th scope="col" class="text-center align-middle">WS</th>
                            <th scope="col" class="text-center align-middle">Style</th>
                            <th scope="col" class="text-center align-middle">Color</th>
                            <th scope="col" class="text-center align-middle">ID Item</th>
                            <th scope="col" class="text-center align-middle">Detail Item</th>
                            <th scope="col" class="text-center align-middle">No. Roll</th>
                            <th scope="col" class="text-center align-middle">Lot</th>
                            <th scope="col" class="text-center align-middle">Qty</th>
                            <th scope="col" class="text-center align-middle">Unit</th>
                        </tr>
                        <tr>
                            <th></th> <!-- Empty cell for Act (no search input) -->
                            <th><input type="text" class="column-search form-control form-control-sm" /></th>
                            <th><input type="text" class="column-search form-control form-control-sm" /></th>
                            <th><input type="text" class="column-search form-control form-control-sm" /></th>
                            <th><input type="text" class="column-search form-control form-control-sm" /></th>
                            <th><input type="text" class="column-search form-control form-control-sm" /></th>
                            <th><input type="text" class="column-search form-control form-control-sm" /></th>
                            <th><input type="text" class="column-search form-control form-control-sm" /></th>
                            <th><input type="text" class="column-search form-control form-control-sm" /></th>
                            <th><input type="text" class="column-search form-control form-control-sm" /></th>
                            <th><input type="text" class="column-search form-control form-control-sm" /></th>
                            <th><input type="text" class="column-search form-control form-control-sm" /></th>
                            <th><input type="text" class="column-search form-control form-control-sm" /></th>
                            <th><input type="text" class="column-search form-control form-control-sm" /></th>
                        </tr>
                    </thead>
                </table>
            </div>

            <div class="row align-items-end g-2 mt-2">
                <div class="col-md-5">
                    <label class="form-label mb-1"><small><b>Group</b></small></label>
                    <select id="group-select" class="form-select form-select-sm select2bs4 w-100">
                        <option value="">-- Pilih Group --</option>
                        @foreach (range('A', 'Z') as $char)
                            <option value="{{ $char }}">{{ $char }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Push button to the right -->
                <div class="col-md-2 ms-auto text-end">
                    <button class="btn btn-outline-primary btn-sm w-100" onclick="save()">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                </div>
            </div>


        </div>
    </div>
@endsection

@section('custom-script')
    <!-- DataTables & Plugins -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables 2.0/dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables 2.0/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables 2.0/dataTables.fixedColumns.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-rowsgroup/dataTables.rowsGroup.js') }}"></script>
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
        });

        $('#group-select').on('select2:open', function() {
            document.querySelector('.select2-search__field').disabled = true;
        });


        function notif() {
            alert("Maaf, Fitur belum tersedia!");
        }
    </script>
    <script>
        $(document).ready(function() {
            initScanFabric();
            $('#txtbarcode_scan').val('');
        });

        let html5QrcodeScannerFabric = null;
        async function stopScannerFabric() {
            if (html5QrcodeScannerFabric) {
                try {
                    await html5QrcodeScannerFabric.clear();
                } catch (e) {
                    console.error("Error stopping Fabric scanner:", e);
                }
                html5QrcodeScannerFabric = null;
            }
        }

        function initScanFabric() {
            if (document.getElementById("reader_fabric")) {
                if (html5QrcodeScannerFabric) {
                    stopScannerFabric(); // avoid duplicate
                }

                function onScanSuccess(decodedText) {
                    if (decodedText && decodedText.trim() !== "") {
                        document.getElementById('txtbarcode_scan').value = decodedText;
                        check_barcode();
                        stopScannerFabric();
                    }
                }

                function onScanFailure(error) {
                    console.warn(`Scan error: ${error}`);
                }

                // ✅ FIXED: use the correct class
                html5QrcodeScannerFabric = new Html5QrcodeScanner(
                    "reader_fabric", {
                        fps: 10,
                        qrbox: {
                            width: 200,
                            height: 200
                        }
                    },
                    false
                );

                html5QrcodeScannerFabric.render(onScanSuccess, onScanFailure);
            }
        }

        function check_barcode() {
            let txtbarcode_scan = $('#txtbarcode_scan').val();
            if (!txtbarcode_scan || txtbarcode_scan.trim() === '') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan!',
                    text: 'Field "Barcode Scan" wajib diisi.',
                });
                return;
            }

            // Step 1: Get barcode info
            $.ajax({
                type: "POST",
                url: '{{ route('get_barcode_info_shade_band') }}', // route for checking barcode
                data: {
                    _token: '{{ csrf_token() }}',
                    txtbarcode_scan: txtbarcode_scan
                },
                success: function(response) {
                    if (response.status === 'not_found') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Data Tidak Ditemukan',
                            text: 'Barcode Tidak Ada.',
                            showConfirmButton: false,
                            timer: 1000,
                            timerProgressBar: true
                        });
                        $('#txtbarcode_scan').val('');
                        initScanFabric();
                        return;
                    }

                    if (response.status === 'duplicate') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Data Sudah Pernah Di Input',
                            text: 'Barcode Sudah Di Input.',
                            showConfirmButton: false,
                            timer: 1000,
                            timerProgressBar: true
                        });
                        $('#txtbarcode_scan').val('');
                        initScanFabric();
                        return;

                    }
                    // Step 2: Show confirmation before saving
                    Swal.fire({
                        title: 'Konfirmasi Data Barcode',
                        html: `
                    <p><b>Barcode:</b> ${response.data.no_barcode}</p>
                    <p><b>ID Item:</b> ${response.data.id_item}</p>
                    <p><b>Desc:</b> ${response.data.itemdesc}</p>
                    <hr>
                    Lanjutkan update form dengan data ini?
                `,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Lanjutkan',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Send data only if confirmed
                            $.ajax({
                                type: "POST",
                                url: '{{ route('insert_tmp_shade_band') }}',
                                data: {
                                    _token: '{{ csrf_token() }}',
                                    barcode: txtbarcode_scan
                                },
                                success: function(response) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Barcode Disimpan!',
                                        html: `
                                        <p><b>Barcode       :</b> ${response.data.barcode}</p>
                                        <hr>
                                        <p>Data berhasil disimpan.</p>`,
                                        showConfirmButton: false,
                                        timer: 1000,
                                        timerProgressBar: true
                                    }).then(() => {
                                        $('#txtbarcode_scan').val('');
                                        $('#datatable').DataTable().ajax.reload();
                                    });
                                },
                                error: function(xhr) {
                                    console.error('Save Error:', xhr.responseText);
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Gagal',
                                        text: 'Gagal menyimpan data.'
                                    });
                                }
                            });
                            initScanFabric();
                            $('#txtbarcode_scan').val('');
                            $('#datatable').DataTable().ajax.reload();
                        } else {
                            initScanFabric();
                            $('#txtbarcode_scan').val('');
                            $('#datatable').DataTable().ajax.reload();
                        }
                    });
                },
                error: function(xhr) {
                    console.error('Barcode Check Error:', xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Terjadi kesalahan saat mengambil data Barcode.'
                    });
                }
            });
        }

        let datatable = $("#datatable").DataTable({
            ordering: false,
            responsive: true,
            processing: true,
            serverSide: false,
            paging: false,
            searching: true,
            scrollY: true,
            scrollX: true,
            scrollCollapse: false,
            fixedColumns: {
                leftColumns: 1
            },
            ajax: {
                url: '{{ route('get_list_shade_band_tmp') }}',
                data: function(d) {
                    d.user = $('#txtuser').val();
                },
            },
            columns: [{
                    data: null,
                    orderable: false,
                    searchable: false,
                    className: 'text-center',
                    render: function(data, type, row) {
                        return `
            <button
                class="btn btn-sm btn-danger"
                onclick="delete_tmp('${data?.no_barcode || ''}')"
                title="Hapus Barcode"
            >
                <i class="fas fa-trash-alt"></i>
            </button>
        `;
                    }
                },
                {
                    data: 'no_barcode'
                },
                {
                    data: 'no_invoice'
                },
                {
                    data: 'supplier'
                },
                {
                    data: 'buyer'
                },
                {
                    data: 'kpno'
                },
                {
                    data: 'styleno'
                },
                {
                    data: 'color'
                },
                {
                    data: 'id_item'
                },
                {
                    data: 'itemdesc'
                },
                {
                    data: 'no_roll_buyer'
                },
                {
                    data: 'no_lot'
                },
                {
                    data: 'qty_aktual'
                },
                {
                    data: 'satuan',
                    className: 'text-center'
                },

            ],
            initComplete: function() {
                this.api().columns().every(function() {
                    var column = this;
                    $('input', this.header()).on('keyup change clear', function() {
                        if (column.search() !== this.value) {
                            column.search(this.value).draw();
                        }
                    });
                });
            }
        });


        function delete_tmp(barcode) {
            if (!barcode) return;

            Swal.fire({
                title: 'Yakin?',
                text: `Hapus barcode ${barcode}?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ route('delete_barcode_tmp_shade_band') }}',
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            barcode: barcode
                        },
                        success: function(response) {
                            if (response.status === 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil',
                                    text: 'Barcode telah dihapus.',
                                    showConfirmButton: false,
                                    timer: 1000,
                                    timerProgressBar: true
                                }).then(() => {
                                    $('#datatable').DataTable().ajax.reload();
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal',
                                    text: response.message || 'Barcode tidak ditemukan.',
                                    showConfirmButton: false,
                                    timer: 1500,
                                    timerProgressBar: true
                                }).then(() => {
                                    $('#datatable').DataTable().ajax.reload();
                                });
                            }
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Terjadi kesalahan saat menghapus.',
                                showConfirmButton: false,
                                timer: 1500,
                                timerProgressBar: true
                            });
                            console.error(xhr.responseText);
                        }
                    });
                }
            });
        }

        function save() {
            const group = $('#group-select').val();
            const table = $('#datatable').DataTable();
            const rowCount = table.rows().count();

            if (!group) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Group belum dipilih!',
                    text: 'Silakan pilih group sebelum menyimpan.',
                    timer: 1500,
                    showConfirmButton: false,
                    timerProgressBar: true
                });
                return;
            }

            // ✅ Check if DataTable has rows
            if (rowCount === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Tidak ada data!',
                    text: 'Tabel masih kosong. Scan barcode terlebih dahulu.',
                    timer: 1500,
                    showConfirmButton: false,
                    timerProgressBar: true
                });
                return;
            }

            Swal.fire({
                title: 'Konfirmasi Simpan Data',
                html: `
            <p><b>Group                :</b> ${group}</p>
            <p><b>Total Barcode        :</b> ${rowCount}</p>
            <hr>
            Lanjutkan simpan data ini?
        `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Simpan',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Send data only if confirmed
                    $.ajax({
                        type: "POST",
                        url: '{{ route('save_proses_shade_band') }}',
                        data: {
                            _token: '{{ csrf_token() }}',
                            group: group
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Form Disimpan!',
                                html: `
                                <p><b>Group                 :</b> ${response.data.group}</p>
                                <p><b>Total Barcode         :</b> ${rowCount}</p>
                                <hr>
                                <p>Data berhasil disimpan.</p>
        `
                            }).then(() => {
                                window.location.reload(true);
                            });
                        },
                        error: function(xhr) {
                            console.error('Save Error:', xhr.responseText);
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: 'Gagal menyimpan data.'
                            });
                        }
                    });
                }
            });
        }
    </script>
@endsection
