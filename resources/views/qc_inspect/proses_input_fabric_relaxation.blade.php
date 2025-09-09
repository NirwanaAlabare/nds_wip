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
    <!-- SCAN OPERATOR MODAL -->
    <div class="modal fade" id="startFormModal" tabindex="-1" aria-labelledby="startFormModalLabel" aria-hidden="true">
        <div class="modal-dialog"> <!-- Smaller, scrollable modal -->
            <div class="modal-content">

                <!-- Modal Header -->
                <div class="modal-header bg-sb text-white">
                    <h5 class="modal-title" id="startFormModalLabel">
                        <i class="fas fa-qrcode"></i> Scan ID Operator
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body">

                    <!-- Input Group -->
                    <div class="mb-3">
                        <label for="txtqr_operator" class="form-label label-input">
                            <small><b>QR Code</b></small>
                        </label>
                        <div class="input-group">
                            <input type="text" class="form-control border-input" name="txtqr_operator"
                                id="txtqr_operator" autocomplete="off" enterkeyhint="go" autofocus>
                            <span class="input-group-text bg-light text-muted py-0 px-2">
                                <small>Scan</small>
                            </span>
                            <span id="btnScan" onclick="check_operator()"
                                class="input-group-text bg-light text-muted py-0 px-2" role="button"
                                style="cursor: pointer;">
                                <small>Scan</small>
                            </span>
                        </div>
                    </div>

                    <!-- QR Scanner -->
                    <div class="d-flex justify-content-center">
                        <div id="reader" class="scanner-box"></div>
                    </div>

                </div>

                <!-- Modal Footer -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal"
                        onclick="stopScannerOperator()">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- SCAN FABRIC MODAL -->
    <div class="modal fade" id="startFabricModal" tabindex="-1" aria-labelledby="startFabricModalLabel" aria-hidden="true">
        <div class="modal-dialog"> <!-- Smaller, scrollable modal -->
            <div class="modal-content">

                <!-- Modal Header -->
                <div class="modal-header bg-sb text-white">
                    <h5 class="modal-title" id="startFabricModalLabel">
                        <i class="fas fa-qrcode"></i> Scan Barcode Fabric
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body">

                    <!-- Input Group -->
                    <div class="mb-3">
                        <label for="txtbarcode_scan" class="form-label label-input">
                            <small><b>Barcode</b></small>
                        </label>
                        <div class="input-group">
                            <input type="text" class="form-control border-input" name="txtbarcode_scan"
                                id="txtbarcode_scan" autocomplete="off" enterkeyhint="go" autofocus>
                            <span class="input-group-text bg-light text-muted py-0 px-2">
                                <small>Scan</small>
                            </span>
                            <span id="btnScanFabric" onclick="check_barcode()"
                                class="input-group-text bg-light text-muted py-0 px-2" role="button"
                                style="cursor: pointer;">
                                <small>Scan</small>
                            </span>
                        </div>
                    </div>

                    <!-- QR Scanner -->
                    <div class="d-flex justify-content-center">
                        <div id="reader_fabric" class="scanner-box-fabric"></div>
                    </div>

                </div>

                <!-- Modal Footer -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal"
                        onclick="stopScannerFabric()">Close</button>
                </div>
            </div>
        </div>
    </div>



    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-list"></i> Input Fabric Relaxation</h5>
        </div>
        <div class="card-body pb-0">
            <div class="row mb-3 g-2">
                <div class="col-12 col-md-4">
                    <button type="button" class="btn btn-primary btn-sm w-100" onclick="openScannerModalOperator()">
                        <i class="fas fa-user"></i> Scan Operator
                    </button>
                </div>
                <div class="col-12 col-md-4">
                    <button type="button" class="btn btn-warning btn-sm w-100" onclick="openScannerModalFabric()">
                        <i class="fas fa-barcode"></i> Scan Barcode
                    </button>
                </div>
                <div class="col-12 col-md-4">
                    <button type="button" class="btn btn-success btn-sm w-100" onclick="handleStart()">
                        <i class="fas fa-play"></i> Start
                    </button>
                </div>
            </div>


            <div class="row mb-3">
                <div class="col-md-3">
                    <label for="txtstart"><small><b>Start :</b></small></label>
                    <input type="text" id="txtstart" name="txtstart"
                        class="form-control form-control-sm border-primary"readonly>
                </div>
                <div class="col-md-3">
                    <label for="txtfinish"><small><b>Finish :</b></small></label>
                    <input type="text" id="txtfinish" name="txtfinish"
                        class="form-control form-control-sm border-primary" readonly>
                </div>
                <div class="col-md-3">
                    <label for="txtnik"><small><b>NIK :</b></small></label>
                    <input type="hidden" id="txtenroll_id" name="txtenroll_id"
                        class="form-control form-control-sm border-primary" readonly>
                    <input type="text" id="txtnik" name="txtnik"
                        class="form-control form-control-sm border-primary" readonly>
                </div>
                <div class="col-md-3">
                    <label for="txtoperator"><small><b>Operator :</b></small></label>
                    <input type="text" id="txtoperator" name="txtoperator"
                        class="form-control form-control-sm border-primary" readonly>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-3">
                    <label for="txtno_mesin"><small><b>No. Mesin :</b></small></label>
                    <input type="text" id="txtno_mesin" name="txtno_mesin" value ="{{ $user }}"
                        class="form-control form-control-sm border-primary"readonly>
                </div>
                <div class="col-md-3">
                    <label for="txtno_form"><small><b>No. Form :</b></small></label>
                    <input type="text" id="txtno_form" name="txtno_form"
                        class="form-control form-control-sm border-primary" readonly>
                </div>
                <div class="col-md-3">
                    <label for="txttgl_bpb"><small><b>Tgl BPB :</b></small></label>
                    <input type="text" id="txttgl_bpb" name="txttgl_bpb"
                        class="form-control form-control-sm border-primary" readonly>
                </div>
                <div class="col-md-3">
                    <label for="txtno_pl"><small><b>No. PL :</b></small></label>
                    <input type="text" id="txtno_pl" name="txtno_pl"
                        class="form-control form-control-sm border-primary" readonly>
                </div>
            </div>


            <div class="row mb-3">
                <div class="col-md-3">
                    <label for="txtbuyer"><small><b>Buyer :</b></small></label>
                    <input type="text" id="txtbuyer" name="txtbuyer"
                        class="form-control form-control-sm border-primary"readonly>
                </div>
                <div class="col-md-3">
                    <label for="txtws"><small><b>WS :</b></small></label>
                    <input type="text" id="txtws" name="txtws"
                        class="form-control form-control-sm border-primary" readonly>
                </div>
                <div class="col-md-3">
                    <label for="txtstyle"><small><b>Style :</b></small></label>
                    <input type="text" id="txtstyle" name="txtstyle"
                        class="form-control form-control-sm border-primary" readonly>
                </div>
                <div class="col-md-3">
                    <label for="txtcolor"><small><b>Color :</b></small></label>
                    <input type="text" id="txtcolor" name="txtcolor"
                        class="form-control form-control-sm border-primary" readonly>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-3">
                    <label for="txtno_roll"><small><b>No Roll :</b></small></label>
                    <input type="text" id="txtno_roll" name="txtno_roll"
                        class="form-control form-control-sm border-primary"readonly>
                </div>
                <div class="col-md-3">
                    <label for="txtno_lot"><small><b>No Lot :</b></small></label>
                    <input type="text" id="txtno_lot" name="txtno_lot"
                        class="form-control form-control-sm border-primary" readonly>
                </div>
                <div class="col-md-3">
                    <label for="txtbarcode"><small><b>Barcode :</b></small></label>
                    <input type="text" id="txtbarcode" name="txtbarcode"
                        class="form-control form-control-sm border-primary" readonly>
                </div>
                <div class="col-md-3">
                    <label for="txtid_item"><small><b>Color :</b></small></label>
                    <input type="text" id="txtid_item" name="txtid_item"
                        class="form-control form-control-sm border-primary" readonly>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-9">
                    <label for="txtitem_desc"><small><b>Detail Item :</b></small></label>
                    <input type="text" id="txtitem_desc" name="txtitem_desc"
                        class="form-control form-control-sm border-primary" readonly>
                </div>
                <div class="col-md-3">
                    <label for="txtdurasi_relax"><small><b>Durasi Relax:</b></small></label>
                    <div class="input-group input-group-sm">
                        <input type="number" id="txtdurasi_relax" name="txtdurasi_relax"
                            class="form-control border-primary">
                        <span class="input-group-text">jam</span>
                    </div>
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

        function notif() {
            alert("Maaf, Fitur belum tersedia!");
        }
    </script>
    <script>
        $(document).ready(function() {
            $('#txtstart').val('');
            $('#txtfinish').val('');
            $('#txtenroll_id').val('');
            $('#txtnik').val('');
            $('#txtoperator').val('');
            $('#txtno_mesin').val('');
            $('#txtno_form').val('');
            $('#txttgl_bpb').val('');
            $('#txtno_pl').val('');
            $('#txtbuyer').val('');
            $('#txtws').val('');
            $('#txtstyle').val('');
            $('#txtcolor').val('');
            $('#txtno_roll').val('');
            $('#txtno_lot').val('');
            $('#txtbarcode').val('');
            $('#txtid_item').val('');
            $('#txtitem_desc').val('');
            $('#txtdurasi_relax').val('0');
        });

        function handleStart() {
            const barcode = document.getElementById('txtbarcode').value.trim();
            const enrollId = document.getElementById('txtenroll_id').value.trim();
            const durasi_relax_raw = document.getElementById('txtdurasi_relax').value.trim();
            const durasi_relax = parseFloat(durasi_relax_raw);

            if (!barcode) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Barcode kosong',
                    text: 'Silahkan scan barcode terlebih dahulu.',
                });
                return;
            }

            if (!enrollId) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Operator kosong',
                    text: 'Silahkan scan operator terlebih dahulu.',
                });
                return;
            }

            if (isNaN(durasi_relax) || durasi_relax <= 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Durasi Relax tidak valid',
                    text: 'Silahkan isi durasi relax dengan angka lebih dari 0.',
                });
                return;
            }

            save_form_fabric_relaxation();
            // You can trigger the actual start logic here
            // Example: startProcess();
        }

        function save_form_fabric_relaxation() {
            let barcode = $('#txtbarcode').val();
            let enroll_id = $('#txtenroll_id').val();
            let durasi_relax = $('#txtdurasi_relax').val();

            // Build summary before save
            Swal.fire({
                title: 'Konfirmasi Detail Fabric dengan Barcode',
                html: `
            <p><b>Barcode        :</b> ${barcode}</p>
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
                        url: '{{ route('save_form_fabric_relaxation') }}',
                        data: {
                            _token: '{{ csrf_token() }}',
                            barcode: barcode,
                            enroll_id: enroll_id,
                            durasi_relax: durasi_relax
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Form Disimpan!',
                                html: `
            <p><b>No Form       :</b> ${response.data.no_form}</p>
            <p><b>Barcode       :</b> ${response.data.barcode}</p>
            <p><b>Durasi Relax :</b> ${response.data.durasi_relax} Jam</p>
            <hr>
            <p>Data berhasil disimpan.</p>
        `
                            }).then(() => {
                                window.location.href =
                                    `{{ route('input_fabric_relaxation_det', ['']) }}/${response.data.id}`;
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


        document.getElementById('startFormModal').addEventListener('hidden.bs.modal', function() {
            stopScannerOperator();
        });
        let html5QrcodeScanner = null;

        function openScannerModalOperator() {
            $('#txtqr_operator').val('');
            const modal = new bootstrap.Modal(document.getElementById('startFormModal'));
            modal.show();

            initScan();
        }

        async function stopScannerOperator() {
            if (html5QrcodeScanner) {
                try {
                    await html5QrcodeScanner.clear();
                } catch (e) {
                    console.error("Error stopping Operator scanner:", e);
                }
                html5QrcodeScanner = null;
            }
        }

        function initScan() {
            if (document.getElementById("reader")) {
                if (html5QrcodeScanner) {
                    stopScannerOperator(); // avoid duplicate
                }

                function onScanSuccess(decodedText) {
                    if (decodedText && decodedText.trim() !== "") {
                        document.getElementById('txtqr_operator').value = decodedText;
                        check_operator();
                        stopScannerOperator();
                    }
                }

                function onScanFailure(error) {
                    console.warn(`Scan error: ${error}`);
                }

                html5QrcodeScanner = new Html5QrcodeScanner(
                    "reader", {
                        fps: 10,
                        qrbox: {
                            width: 200,
                            height: 200
                        }
                    },
                    false
                );

                html5QrcodeScanner.render(onScanSuccess, onScanFailure);
            }
        }

        function check_operator() {
            let txtqr_operator = $('#txtqr_operator').val();
            if (!txtqr_operator || txtqr_operator.trim() === '') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan!',
                    text: 'Field "QR Operator" wajib diisi.',
                });
                return;
            }

            // Step 1: Get operator info
            $.ajax({
                type: "POST",
                url: '{{ route('get_operator_info') }}', // route for checking operator
                data: {
                    _token: '{{ csrf_token() }}',
                    txtqr_operator: txtqr_operator
                },
                success: function(response) {
                    if (response.status === 'not_found') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Data Tidak Ditemukan',
                            text: 'Operator tidak terdaftar di HRIS.'
                        });
                        return;
                    }

                    // Step 2: Show confirmation before saving
                    Swal.fire({
                        title: 'Konfirmasi Data Operator',
                        html: `
                    <p><b>Nama:</b> ${response.data.operator}</p>
                    <p><b>NIK:</b> ${response.data.nik}</p>
                    <p><b>Enroll ID:</b> ${response.data.enroll_id}</p>
                    <hr>
                    Lanjutkan update form dengan data ini?
                `,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Lanjutkan',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            document.getElementById('txtnik').value = response.data.nik;
                            document.getElementById('txtoperator').value = response.data.operator;
                            document.getElementById('txtenroll_id').value = response.data.enroll_id;
                            stopScannerOperator();
                            // ✅ Close the modal
                            const modalEl = document.getElementById('startFormModal');
                            const modal = bootstrap.Modal.getInstance(modalEl);
                            if (modal) modal.hide();
                        } else {
                            const modalEl = document.getElementById('startFormModal');
                            const modal = bootstrap.Modal.getInstance(modalEl);
                            if (modal) modal.hide();
                        }
                    });
                },
                error: function(xhr) {
                    console.error('Operator Check Error:', xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Terjadi kesalahan saat mengambil data operator.'
                    });
                }
            });
        }


        document.getElementById('startFabricModal').addEventListener('hidden.bs.modal', function() {
            stopScannerFabric();
        });

        let html5QrcodeScannerFabric = null;

        function openScannerModalFabric() {
            $('#txtbarcode_scan').val('');
            const modal = new bootstrap.Modal(document.getElementById('startFabricModal'));
            modal.show();

            initScanFabric();
        }

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

            // Step 1: Get operator info
            $.ajax({
                type: "POST",
                url: '{{ route('get_barcode_info_fabric_relaxation') }}', // route for checking barcode
                data: {
                    _token: '{{ csrf_token() }}',
                    txtbarcode_scan: txtbarcode_scan
                },
                success: function(response) {
                    if (response.status === 'not_found') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Data Tidak Ditemukan',
                            text: 'Barcode Tidak Ada.'
                        });
                        return;
                    }

                    if (response.status === 'duplicate') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Data Sudah Pernah Di Input',
                            text: 'Barcode Sudah Di Input.'
                        });
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
                            document.getElementById('txttgl_bpb').value = response.data.tgl_dok;
                            document.getElementById('txtno_pl').value = response.data.no_invoice;
                            document.getElementById('txtbuyer').value = response.data.buyer;
                            document.getElementById('txtws').value = response.data.kpno;
                            document.getElementById('txtstyle').value = response.data.styleno;
                            document.getElementById('txtcolor').value = response.data.color;
                            document.getElementById('txtno_roll').value = response.data.no_roll_buyer;
                            document.getElementById('txtno_lot').value = response.data.no_lot;
                            document.getElementById('txtbarcode').value = response.data.no_barcode;
                            document.getElementById('txtid_item').value = response.data.id_item;
                            document.getElementById('txtitem_desc').value = response.data.itemdesc;
                            stopScannerFabric();
                            // ✅ Close the modal
                            const modalEl = document.getElementById('startFabricModal');
                            const modal = bootstrap.Modal.getInstance(modalEl);
                            if (modal) modal.hide();
                        } else {
                            const modalEl = document.getElementById('startFabricModal');
                            const modal = bootstrap.Modal.getInstance(modalEl);
                            if (modal) modal.hide();
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
    </script>
@endsection
