    @extends('layouts.index')

    @section('custom-link')
        <!-- DataTables -->
        <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
        <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
        <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">

        <!-- Select2 -->
        <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
        <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
    @endsection

    @section('content')
        <div class="card card-sb">
            <div class="card-header">
                <h5 class="card-title fw-bold mb-0"><i class="fa-solid fa-note-sticky"></i> Sisa Kain Roll</h5>
            </div>
            <div class="card-body">
                <div class="row justify-content-start align-items-end g-3">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <div id="reader" onclick="clearQrCodeScanner()"></div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label fw-bold"><small><b>ID Roll</b></small></label>
                        <div class="input-group input-group-sm">
                            <input type="text" class="form-control" id="id_roll" name="id_roll">
                            <button class="btn btn-primary" class="input-group-text" id="openScan" onclick="initScan()">Scan</button>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold"><small><b>ID Item</b></small></label>
                        <input type="text" class="form-control form-control-sm" id="id_item" name="id_item" readonly>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label fw-bold"><small><b>Detail Item</b></small></label>
                        <input type="text" class="form-control form-control-sm" id="detail_item" name="detail_item" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold"><small><b>Supplier</b></small></label>
                        <input type="text" class="form-control form-control-sm" id="buyer" name="buyer" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold"><small><b>No. WS</b></small></label>
                        <input type="text" class="form-control form-control-sm" id="no_ws" name="no_ws" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold"><small><b>Style</b></small></label>
                        <input type="text" class="form-control form-control-sm" id="style" name="style" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold"><small><b>Color</b></small></label>
                        <input type="text" class="form-control form-control-sm" id="color" name="color" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold"><small><b>Lot</b></small></label>
                        <input type="text" class="form-control form-control-sm" id="lot" name="lot" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold"><small><b>No. Roll</b></small></label>
                        <input type="text" class="form-control form-control-sm" id="no_roll" name="no_roll" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold"><small><b>Sisa Kain</b></small></label>
                        <input type="text" class="form-control form-control-sm fw-bold" id="sisa_kain" name="sisa_kain" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold"><small><b>Unit</b></small></label>
                        <input type="text" class="form-control form-control-sm" id="unit" name="unit" readonly>
                    </div>
                    <div class="col-md-6">
                        <button class="btn btn-primary btn-sm btn-block fw-bold mt-3"><i class="fa-solid fa-print"></i> PRINT STOCKER</button>
                    </div>
                </div>
            </div>
        </div>
    @endsection

    @section('custom-script')
        <!-- DataTables  & Plugins -->
        <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
        <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
        <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
        <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
        <script src="{{ asset('plugins/datatables-rowsgroup/dataTables.rowsGroup.js') }}"></script>
        <!-- Select2 -->
        <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>

        <script>
            $(document).ready(() => {
                initScan();
            })

            document.getElementById("id_roll").addEventListener("keyup", (event, element) => {
                if (event.keyCode == '13') {
                    getScannedItem(event.target.value);
                }
            })

            // Scan QR Module :
                // Variable List :
                var html5QrcodeScanner = new Html5Qrcode("reader");
                var scannerInitialized = false;

            // Function List :
                // -Initialize Scanner-
                async function initScan() {
                    if (document.getElementById("reader")) {
                        if (html5QrcodeScanner == null || (html5QrcodeScanner && (html5QrcodeScanner.isScanning == false))) {
                            const qrCodeSuccessCallback = (decodedText, decodedResult) => {
                                // handle the scanned code as you like, for example:
                                console.log(`Code matched = ${decodedText}`, decodedResult);

                                // store to input text

                                document.getElementById('id_roll').value = decodedText;

                                getScannedItem(decodedText);

                                clearQrCodeScanner();
                            };
                            const config = { fps: 10, qrbox: { width: 250, height: 250 } };

                            await html5QrcodeScanner.start({ facingMode: "environment" }, config, qrCodeSuccessCallback);
                        }
                    }
                }

                async function clearQrCodeScanner() {
                    console.log(html5QrcodeScanner, html5QrcodeScanner.getState());
                    if (html5QrcodeScanner && (html5QrcodeScanner.isScanning)) {
                        await html5QrcodeScanner.stop();
                        await html5QrcodeScanner.clear();
                    }
                }

                async function refreshScan() {
                    await clearQrCodeScanner();
                    await initScan();
                }

                // --Clear Scan Item Form--
                function clearScanItemForm() {
                    $("#id_roll").val("");
                    $("#id_item").val("");
                    $("#detail_item").val("");
                }

                function fetchScan() {
                    let idRoll = document.getElementById('id_roll').value;

                    getScannedItem(idRoll);
                }

                async function getScannedItem(id) {
                    document.getElementById("loading").classList.remove("d-none");

                    document.getElementById("id_item").value = "";
                    document.getElementById("detail_item").value = "";
                    document.getElementById("color").value = "";
                    // document.getElementById("buyer").value = "";
                    // document.getElementById("no_ws").value = "";
                    // document.getElementById("style").value = "";
                    // document.getElementById("color").value = "";
                    document.getElementById("lot").value = "";
                    document.getElementById("no_roll").value = "";
                    document.getElementById("sisa_kain").value = "";
                    document.getElementById("unit").value = "";

                    if (isNotNull(id)) {
                        return $.ajax({
                            url: '{{ route('sisa_kain_scan_item') }}/' + id,
                            type: 'get',
                            dataType: 'json',
                            success: function(res) {
                                console.log(res);

                                if (typeof res === 'object' && res !== null) {
                                    document.getElementById("id_item").value = res.id_item;
                                    document.getElementById("detail_item").value = res.detail_item;
                                    // document.getElementById("buyer").value = res.buyer;
                                    // document.getElementById("no_ws").value = res.no_ws;
                                    // document.getElementById("style").value = res.style;
                                    // document.getElementById("color").value = res.color;
                                    document.getElementById("lot").value = res.lot;
                                    document.getElementById("no_roll").value = res.no_roll;
                                    document.getElementById("sisa_kain").value = res.qty;
                                    document.getElementById("unit").value = res.unit;
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Gagal',
                                        text: res ? res : 'Roll tidak tersedia.',
                                        showCancelButton: false,
                                        showConfirmButton: true,
                                        confirmButtonText: 'Oke',
                                    });
                                }

                                document.getElementById("loading").classList.add("d-none");
                            },
                            error: function(jqXHR) {
                                console.log(jqXHR);

                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal',
                                    text: jqXHR.responseText ? jqXHR.responseText : 'Roll tidak tersedia.',
                                    showCancelButton: false,
                                    showConfirmButton: true,
                                    confirmButtonText: 'Oke',
                                });

                                document.getElementById("loading").classList.add("d-none");
                            }
                        });
                    }

                    document.getElementById("loading").classList.add("d-none");

                    return Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Item tidak ditemukan',
                        showCancelButton: false,
                        showConfirmButton: true,
                        confirmButtonText: 'Oke',
                    });
                }

            // Kain Roll
                // document.getElementById("")
        </script>
    @endsection

