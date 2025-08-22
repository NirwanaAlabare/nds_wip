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
    <div class="d-flex justify-content-between mb-3">
        <h5 class="fw-bold text-sb"><i class="fa fa-plus fa-sm"></i> Ubah Data Piping</h5>
        <a href="{{ route('form-cut-piping') }}" class="btn btn-primary btn-sm px-1 py-1"><i class="fas fa-reply"></i> Kembali ke Piping</a>
    </div>
    <form action="{{ route('update-piping') }}" method="post" id="update-piping" onsubmit="submitForm(this, event)">
        @csrf
        <div class="card card-sb">
            <div class="card-header">
                <h5 class="card-title fw-bold">
                    Edit Data
                </h5>
            </div>
            <div class="card-body">
                <input type="hidden" class="form-control d-none" id="edit_id" name="edit_id" value="{{ $piping->id }}">
                <div class="row align-items-end">
                    <div class="col-6 col-md-6">
                        <div class="mb-1">
                            <label class="form-label"><small>ID</small></label>
                            <input type="text" class="form-control" id="edit_id" name="edit_id" value="{{ $piping->id }}" readonly>
                        </div>
                    </div>
                    <div class="col-6 col-md-6">
                        <div class="mb-1">
                            <label class="form-label"><small>Tanggal</small></label>
                            <input type="date" class="form-control" id="edit_tanggal" name="edit_tanggal" value="{{ $piping->tanggal_piping }}">
                        </div>
                    </div>
                    <div class="col-6 col-md-6">
                        <div class="mb-1">
                            <label class="form-label"><small>ID Roll</small></label>
                            <input type="text" class="form-control" id="edit_id_roll" name="edit_id_roll" value="{{ $piping->id_roll }}" readonly>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="mb-1">
                            <label class="form-label"><small>ID Item</small></label>
                            <input type="text" class="form-control" id="edit_id_item" name="edit_id_item" value="{{ $piping->scannedItem->id_item }}" readonly>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="mb-1">
                            <label class="form-label"><small>Lot</small></label>
                            <input type="text" class="form-control" id="edit_lot" name="edit_lot" value="{{ $piping->scannedItem->lot }}" readonly>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="mb-1">
                            <label class="form-label"><small>No. Roll</small></label>
                            <input type="text" class="form-control" id="edit_roll" name="edit_roll" value="{{ $piping->scannedItem->roll }}" readonly>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="mb-1">
                            <label class="form-label"><small>No. Roll Buyer</small></label>
                            <input type="text" class="form-control" id="edit_roll_buyer" name="edit_roll_buyer" value="{{ $piping->scannedItem->roll_buyer }}" readonly>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="mb-1">
                            <label class="form-label"><small>Detail Item</small></label>
                            <input type="text" class="form-control" id="edit_detail_item" name="edit_detail_item" value="{{ $piping->scannedItem->detail_item }}" readonly>
                        </div>
                    </div>
                    <div class="col-6 col-md-6">
                        <div class="mb-1">
                            <div class="form-group mb-0">
                                <label><small>No. WS</small></label>
                                <input type="hidden" class="form-control d-none" id="edit_ws_id" name="edit_ws_id" value="{{ $piping->act_costing_id }}" readonly>
                                <input type="text" class="form-control" id="edit_ws" name="edit_ws" value="{{ $piping->act_costing_ws }}" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="mb-1">
                            <div class="form-group mb-0">
                                <label><small>Color</small></label>
                                <input type="text" class="form-control" id="edit_color" name="edit_color" value="{{ $piping->color }}" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="mb-1">
                            <div class="form-group mb-0">
                                <label><small>Panel</small></label>
                                <input type="text" class="form-control" id="edit_panel" name="edit_panel" value="{{ $piping->panel }}" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-1">
                            <label class="form-label"><small>Buyer</small></label>
                            <input type="text" class="form-control" id="edit_buyer" name="edit_buyer" value="{{ $piping->buyer }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-1">
                            <label class="form-label"><small>Style</small></label>
                            <input type="text" class="form-control" id="edit_style" name="edit_style" value="{{ $piping->style }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-1">
                            <label class="form-label"><small>Cons. Piping</small></label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="edit_cons_piping" name="edit_cons_piping" value="{{ $piping->cons_piping }}" readonly>
                                <span class="input-group-text unit-text">METER</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="mb-1">
                            <label class="form-label"><small>Qty Roll</small></label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="edit_qty_item" name="edit_qty_item" value="{{ $piping->qty }}" readonly>
                                <span class="input-group-text unit-text">METER</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="mb-1">
                            <label class="form-label"><small>Piping</small></label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="edit_piping" name="edit_piping" step=".001" oninput="calculatePipingRoll()" onkeyup="calculatePipingRoll()" onchange="calculatePipingRoll()" value="{{ $piping->piping }}">
                                <span class="input-group-text unit-text">METER</span>
                            </div>
                            <input type="hidden" class="form-control d-none" id="edit_unit" name="edit_unit" value="{{ $piping->unit }}" readonly>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="mb-1">
                            <label class="form-label"><small>Qty Sisa</small></label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="edit_qty_sisa" name="edit_qty_sisa" step=".001" oninput="calculateShortRoll()" onkeyup="calculateShortRoll()" onchange="calculateShortRoll()" value="{{ $piping->qty_sisa }}">
                                <span class="input-group-text unit-text">METER</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="mb-1">
                            <label class="form-label"><small>Short Roll</small></label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="edit_short_roll" name="edit_short_roll" step=".001" value="{{ $piping->short_roll }}" readonly>
                                <span class="input-group-text unit-text">METER</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-6">
                        <div class="mb-1">
                            <label class="form-label"><small>Operator</small></label>
                            <input type="text" class="form-control" id="edit_operator" name="edit_operator" value="{{ $piping->operator }}">
                        </div>
                    </div>
                    <div class="col-6 col-md-6">
                        <button type="submit" class="btn btn-success btn-block mb-1 fw-bold"><i class="fa fa-save"></i> SIMPAN</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@section('custom-script')
    <!-- DataTables & Plugins -->
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <!-- Select2 -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <!-- Page specific script -->
    <script>
        // Initial Window On Load Event
        $(document).ready(async function () {
            initScan();

            //Reset Form
            if (document.getElementById('update-piping')) {
                document.getElementById('update-piping').reset();
            }
        });

        // Select2 Autofocus
        $(document).on('select2:open', () => {
            document.querySelector('.select2-search__field').focus();
        });

        // Initialize Select2 Elements
        $('.select2').select2()

        // Initialize Select2BS4 Elements
        $('.select2bs4').select2({
            theme: 'bootstrap4',
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
                            let breakDecodedText = decodedText.split('-');

                            document.getElementById('scan_item').value = breakDecodedText[0];

                            getScannedItem(breakDecodedText[0]);

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
                $("#edit_id_roll").val("");
                $("#edit_id_item").val("");
                $("#edit_detail_item").val("");
                $("#edit_color_act").val("");
            }

            // --Lock Scan Item Form then Clear Scanner--
            function lockScanItemForm() {
                document.getElementById("edit_kode_barang").setAttribute("readonly", true);
                document.getElementById("edit_color_act").setAttribute("disabled", true);
                document.getElementById("get-button").setAttribute("disabled", true);
                document.getElementById("scan-button").setAttribute("disabled", true);
                document.getElementById("switch-method").setAttribute("disabled", true);
                document.getElementById("reader").classList.add("d-none");

                clearQrCodeScanner();
            }

            // --Open Scan Item Form then Open Scanner--
            function openScanItemForm() {
                if (status != "SELESAI PENGERJAAN") {

                    document.getElementById("kode_barang").removeAttribute("readonly");
                    document.getElementById("color_act").removeAttribute("disabled");
                    document.getElementById("get-button").removeAttribute("disabled");
                    document.getElementById("scan-button").removeAttribute("disabled");
                    document.getElementById("switch-method").removeAttribute("disabled");
                    document.getElementById("reader").classList.remove("d-none");

                    initScan();
                }
            }

        // -Fetch Scanned Item Data-
        function fetchScan() {
            let idRoll = document.getElementById('scan_item').value;

            getScannedItem(idRoll);
        }

        // -Get Scanned Item Data-
        function getScannedItem(id) {
            document.getElementById("loading").classList.remove("d-none");

            document.getElementById("edit_id_item").value = "";
            document.getElementById("edit_detail_item").value = "";

            if (isNotNull(id)) {
                return $.ajax({
                    url: '{{ route('get-scanned-form-cut-input') }}/' + id,
                    type: 'get',
                    dataType: 'json',
                    success: function(res) {
                        console.log(res);

                        if (typeof res === 'object' && res !== null) {
                            if (res.qty > 0) {
                                // if (res.unit.toLowerCase() != ($("#unit_cons_actual_gelaran").val()).toLowerCase()) {
                                //     Swal.fire({
                                //         icon: 'error',
                                //         title: 'Gagal',
                                //         text: 'Unit tidak sesuai',
                                //         showCancelButton: false,
                                //         showConfirmButton: true,
                                //         confirmButtonText: 'Oke',
                                //     });
                                // } else {
                                //     currentScannedItem = res;

                                //     document.getElementById("id_item").value = res.id_item;
                                //     document.getElementById("detail_item").value = res.detail_item;
                                // }

                                document.getElementById("edit_id_roll").value = res.id_roll;
                                document.getElementById("edit_id_item").value = res.id_item;
                                document.getElementById("edit_detail_item").value = res.detail_item;
                                document.getElementById("edit_lot").value = res.lot;
                                document.getElementById("edit_roll").value = res.roll ? res.roll : '-';
                                document.getElementById("edit_roll_buyer").value = res.roll_buyer ? res.roll_buyer : '-';

                                if (res.unit == 'YRD' || res.unit == 'YARD') {
                                    document.getElementById("edit_qty_item").value = (res.qty * 0.9144).round(2);
                                    document.getElementById("edit_unit").value = 'METER';

                                    for(let i = 0; i < document.getElementsByClassName('unit-text').length; i++) {
                                        document.getElementsByClassName('unit-text')[i].innerHTML = 'METER';
                                    }
                                } else {
                                    document.getElementById("edit_qty_item").value = res.qty;
                                    document.getElementById("edit_unit").value = res.unit;

                                    for(let i = 0; i < document.getElementsByClassName('unit-text').length; i++) {
                                        document.getElementsByClassName('unit-text')[i].innerHTML = res.unit;
                                    }
                                }

                                calculatePipingRoll();
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal',
                                    text: 'Qty sudah habis.',
                                    showCancelButton: false,
                                    showConfirmButton: true,
                                    confirmButtonText: 'Oke',
                                });

                                calculatePipingRoll();
                            }
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: res ? res : 'Roll tidak tersedia.',
                                showCancelButton: false,
                                showConfirmButton: true,
                                confirmButtonText: 'Oke',
                            });

                            calculatePipingRoll();
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

            return Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: 'Item tidak ditemukan',
                showCancelButton: false,
                showConfirmButton: true,
                confirmButtonText: 'Oke',
            });
        }

        // Step One (WS) on change event
        $('#edit_ws_id').on('change', function(e) {
            if (this.value) {
                updateColorList();
                updateOrderInfo();
            }
        });

        // Step Two (Color) on change event
        $('#edit_color').on('change', function(e) {
            if (this.value) {
                updatePanelList();
            }
        });

        // Step Three (Panel) on change event
        $('#edit_panel').on('change', function(e) {
            if (this.value) {
                console.log(this.value);
                getNumber();
            }
        });

        // Update Order Information Based on Order WS and Order Color
        function updateOrderInfo() {
            return $.ajax({
                url: '{{ route("get-marker-order") }}',
                type: 'get',
                data: {
                    act_costing_id: $('#edit_ws_id').val(),
                    color: $('#edit_color').val(),
                },
                dataType: 'json',
                success: function (res) {
                    if (res) {
                        document.getElementById('edit_ws').value = res.kpno;
                        document.getElementById('edit_buyer').value = res.buyer;
                        document.getElementById('edit_style').value = res.styleno;
                    }
                },
            });
        }

        // Update Color Select Option Based on Order WS
        function updateColorList() {
            document.getElementById('color').value = null;

            return $.ajax({
                url: '{{ route("get-marker-colors") }}',
                type: 'get',
                data: {
                    act_costing_id: $('#edit_ws_id').val(),
                },
                success: function (res) {
                    if (res) {
                        // Update this step
                        document.getElementById('edit_color').innerHTML = res;

                        // Reset next step
                        document.getElementById('edit_panel').innerHTML = null;
                        document.getElementById('edit_panel').value = null;

                        // Open this step
                        $("#edit_color").prop("disabled", false);

                        // Close next step
                        $("#edit_panel").prop("disabled", true);

                        // Reset order information
                        document.getElementById('edit_piping').value = null;
                    }
                },
            });
        }

        // Update Panel Select Option Based on Order WS and Color WS
        function updatePanelList() {
            document.getElementById('edit_panel').value = null;
            return $.ajax({
                url: '{{ route("get-general-panels") }}',
                type: 'get',
                data: {
                    act_costing_id: $('#edit_ws_id').val(),
                    color: $('#edit_color').val(),
                },
                success: function (res) {
                    if (res) {
                        // Update this step
                        document.getElementById('edit_panel').innerHTML = res;

                        // Open this step
                        $("#edit_panel").prop("disabled", false);

                        // Reset order information
                        document.getElementById('edit_piping').value = null;
                    }
                },
            });
        }

        // Get & Set Order WS Cons and Order Qty Based on Order WS, Order Color and Order Panel
        function getNumber() {
            document.getElementById('piping').value = null;
            return $.ajax({
                url: ' {{ route("get-marker-piping") }}',
                type: 'get',
                dataType: 'json',
                data: {
                    act_costing_id: $('#edit_ws_id').val(),
                    color: $('#edit_color').val(),
                    panel: $('#edit_panel').val()
                },
                success: function (res) {
                    console.log("marker", res);
                    if (res) {
                        document.getElementById('edit_cons_piping').value = res.cons_piping;
                    } else {
                        document.getElementById('edit_cons_piping').value = 0;
                    }
                },
                error: function (jqXHR) {
                    console.error(jqXHR);

                    document.getElementById('edit_cons_piping').value = 0;
                }
            });
        }

        // Prevent Form Submit When Pressing Enter
        document.getElementById("update-piping").onkeypress = function(e) {
            var key = e.charCode || e.keyCode || 0;
            if (key == 13) {
                e.preventDefault();
            }
        }

        // Reset Step
        async function resetStep() {
            await $("#edit_ws_id").val(null).trigger("change");
            await $("#edit_color").val(null).trigger("change");
            await $("#edit_panel").val(null).trigger("change");
            await $("#edit_color").prop("disabled", true);
            await $("#edit_panel").prop("disabled", true);
        }

        async function calculatePipingRoll() {
            let qtyItem = document.getElementById("edit_qty_item").value;
            let piping = document.getElementById("edit_piping").value;

            let qtySisa = qtyItem - piping;

            console.log(qtySisa);

            document.getElementById("edit_qty_sisa").value = qtySisa.round(2);

            calculateShortRoll();
        }

        function calculateShortRoll() {
            let qtyItem = document.getElementById("edit_qty_item").value;
            let piping = document.getElementById("edit_piping").value;
            let qtySisa = document.getElementById("edit_qty_sisa").value;

            let shortRoll = (Number(piping) + Number(qtySisa)) - Number(qtyItem);

            console.log(qtyItem, piping, qtySisa, shortRoll);

            document.getElementById("short_roll").value = shortRoll.round(2);
        }
    </script>
@endsection
