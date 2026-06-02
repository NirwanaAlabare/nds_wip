@extends('layouts.index')

@section('custom-link')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">

    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

    <style>
        /* Responsive Scanner Box */
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

        #btnScrollDown {
            position: fixed;
            bottom: 20px;
            right: 20px;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }

        #btnScrollDown.hide {
            opacity: 0;
            visibility: hidden;
        }
    </style>
@endsection

@section('content')
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


    <form action="{{ route('update-cutting-reject') }}" method="post" id="update-cutting-form-reject"
        onsubmit="submitForm(this, event)">
        @method('PUT')
        <div class="card card-sb">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="fa fa-edit"></i> Edit Reject Form
                </h5>
            </div>
            <div class="card-body">
                <div class="row row-gap-3">
                    <div class="col-md-12 d-none">
                        <label class="form-label">ID</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="id" name="id"
                                value="{{ $form->id }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">No. Form</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="no_form" name="no_form"
                                value="{{ $form->no_form }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Tanggal</label>
                        <input type="date" class="form-control" id="tanggal" name="tanggal"
                            value="{{ $form->tanggal }}" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Worksheet</label>
                        <input type="hidden" class="form-control" id="act_costing_id" name="act_costing_id"
                            value="{{ $form->act_costing_id }}" readonly>
                        <input type="text" class="form-control" id="act_costing_ws" name="act_costing_ws"
                            value="{{ $form->act_costing_ws }}" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Buyer</label>
                        <input type="hidden" class="form-control" id="buyer_id" name="buyer_id"
                            value="{{ $form->buyer_id }}" readonly>
                        <input type="text" class="form-control" id="buyer" name="buyer"
                            value="{{ $form->buyer }}" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Style</label>
                        <input type="text" class="form-control" id="style" name="style"
                            value="{{ $form->style }}" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Color</label>
                        <input type="text" class="form-control" id="color" name="color"
                            value="{{ $form->color }}" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Panel</label>
                        <input type="text" class="form-control" id="panel" name="panel"
                            value="{{ $form->panel }}" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Group</label>
                        <input type="text" class="form-control" id="group" name="group"
                            value="{{ $form->group }}" readonly>
                    </div>
                    <div class="col-md-12 table-responsive">
                        <table class="table table-bordered table mt-3" id="cutting-reject-table">
                            <thead>
                                <tr>
                                    <th>So Det ID</th>
                                    <th>Size</th>
                                    <th>Dest</th>
                                    <th>Qty Output</th>
                                </tr>
                            </thead>
                            <tbody>
                                <td colspan="3" class="text-center">Data tidak ditemukan</td>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th></th>
                                    <th></th>
                                    <th>Total</th>
                                    <th id="total-detail-qty">...</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            <div class="card-footer border-top">
                <div class="d-flex justify-content-end gap-1">
                    <a href="{{ route('cutting-reject') }}" class="btn btn-sm btn-danger"><i class="fa fa-times"></i>
                        BATAL</a>
                    <button type="submit" class="btn btn-sm btn-sb-secondary"><i class="fa fa-check"></i>
                        SIMPAN</button>
                </div>
            </div>
        </div>
    </form>


    <div class="card card-sb" id="cardInputFabric">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-list"></i> Input Fabric</h5>
        </div>
        <div class="card-body pb-0">
            <div class="row mb-3 g-2">
                <div class="col-12 col-md-4">
                    <button type="button" class="btn btn-warning btn-sm w-100" onclick="openScannerModalFabric()">
                        <i class="fas fa-barcode"></i> Scan Barcode
                    </button>
                </div>
            </div>
            <div class="row g-2 mb-3">
                <div class="col-12 col-sm-6 col-lg-3">
                    <label for="txtbarcode" class="form-label mb-1">
                        <small><strong>Barcode</strong></small>
                    </label>
                    <input type="text" id="txtbarcode" name="txtbarcode"
                        class="form-control form-control-sm border-primary" readonly>
                </div>

                <div class="col-12 col-sm-6 col-lg-3">
                    <label for="txtno_roll" class="form-label mb-1">
                        <small><strong>No. Roll</strong></small>
                    </label>
                    <input type="text" id="txtno_roll" name="txtno_roll"
                        class="form-control form-control-sm border-primary" readonly>
                </div>

                <div class="col-12 col-sm-6 col-lg-3">
                    <label for="txtno_lot" class="form-label mb-1">
                        <small><strong>No. Lot</strong></small>
                    </label>
                    <input type="text" id="txtno_lot" name="txtno_lot"
                        class="form-control form-control-sm border-primary" readonly>
                </div>

                <div class="col-12 col-sm-6 col-lg-3">
                    <label for="txtid_item" class="form-label mb-1">
                        <small><strong>ID Item</strong></small>
                    </label>
                    <input type="text" id="txtid_item" name="txtid_item"
                        class="form-control form-control-sm border-primary" readonly>
                </div>

                <div class="col-12 col-sm-12 col-lg-3">
                    <label for="txtitem_name" class="form-label mb-1">
                        <small><strong>Item Name</strong></small>
                    </label>
                    <input type="text" id="txtitem_name" name="txtitem_name"
                        class="form-control form-control-sm border-primary" readonly>
                </div>

                <div class="col-12 col-sm-4 col-lg-3">
                    <label for="txtqty_awal" class="form-label mb-1">
                        <small><strong>Qty Awal</strong></small>
                    </label>
                    <input type="text" id="txtqty_awal" name="txtqty_awal"
                        class="form-control form-control-sm border-primary" readonly>
                </div>
                <div class="col-12 col-sm-4 col-lg-3">
                    <label for="txtqty_roll" class="form-label mb-1">
                        <small><strong>Qty Roll</strong></small>
                    </label>
                    <input type="number" id="txtqty_roll" name="txtqty_roll"
                        class="form-control form-control-sm border-primary" readonly>
                </div>
                <div class="col-12 col-sm-4 col-lg-3">
                    <label for="txtunit" class="form-label mb-1">
                        <small><strong>Unit</strong></small>
                    </label>
                    <input type="text" id="txtunit" name="txtunit"
                        class="form-control form-control-sm border-primary" readonly>
                </div>
                <div class="col-12 col-sm-4 col-lg-3">
                    <label for="txtqty_pakai" class="form-label mb-1">
                        <small><strong>Qty Pakai</strong></small>
                    </label>
                    <input type="number" id="txtqty_pakai" name="txtqty_pakai"
                        class="form-control form-control-sm border-primary">
                </div>
                <div class="col-12 col-sm-4 col-lg-3">
                    <label for="txtqty_reject" class="form-label mb-1">
                        <small><strong>Qty Reject</strong></small>
                    </label>
                    <input type="number" id="txtqty_reject" name="txtqty_reject"
                        class="form-control form-control-sm border-primary">
                </div>
                <div class="col-12 col-sm-4 col-lg-3">
                    <label for="txtqty_sisa" class="form-label mb-1">
                        <small><strong>Sisa Kain</strong></small>
                    </label>
                    <input type="number" id="txtqty_sisa" name="txtqty_sisa"
                        class="form-control form-control-sm border-primary">
                </div>
                <div class="col-12 col-sm-4 col-lg-3">
                    <label for="txttot_pemakaian" class="form-label mb-1">
                        <small><strong>Total Pakai</strong></small>
                    </label>
                    <input type="number" id="txttot_pemakaian" name="txttot_pemakaian"
                        class="form-control form-control-sm border-primary" readonly>
                </div>
                <div class="col-12 col-sm-4 col-lg-3">
                    <label for="txtshort_roll" class="form-label mb-1">
                        <small><strong>Short Roll</strong></small>
                    </label>
                    <input type="number" id="txtshort_roll" name="txtshort_roll"
                        class="form-control form-control-sm border-primary" readonly>
                </div>
                <div class="col-12 col-sm-4 col-lg-3">
                    <label for="btnAddBarcode" class="form-label mb-1">
                        <small><strong>&nbsp;</strong></small>
                    </label>
                    <a id="btnAddBarcode" onclick="handleStart()" class="btn btn-sm btn-primary w-100">
                        Add
                    </a>
                </div>

            </div>


            <div class="table-responsive">
                <table id="datatable_list" class="table table-bordered w-100 text-nowrap">
                    <thead class="bg-sb">
                        <tr>
                            <th scope="col" class="text-center align-middle">Barcode</th>
                            <th scope="col" class="text-center align-middle">Lot</th>
                            <th scope="col" class="text-center align-middle">No. Roll</th>
                            <th scope="col" class="text-center align-middle">Qty Awal</th>
                            <th scope="col" class="text-center align-middle">Qty Roll</th>
                            <th scope="col" class="text-center align-middle">Qty Pakai</th>
                            <th scope="col" class="text-center align-middle">Qty Reject</th>
                            <th scope="col" class="text-center align-middle">Sisa Kain</th>
                            <th scope="col" class="text-center align-middle">Short Roll</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th colspan="3" class="text-end fw-bold">TOTAL</th>
                            <th></th> <!-- qty_in -->
                            <th></th> <!-- qty_roll -->
                            <th></th> <!-- qty_pakai -->
                            <th></th> <!-- qty_reject -->
                            <th></th> <!-- sisa_kain -->
                            <th></th> <!-- short_roll -->
                        </tr>
                    </tfoot>
                </table>
            </div>


        </div>
    </div>
    <button id="btnScrollDown" class="btn btn-primary btn-sm shadow d-flex align-items-center gap-1">
        <i class="fas fa-arrow-down"></i>
        <small>Input Fabric</small>
    </button>
@endsection

@section('custom-script')
    <!-- DataTables  & Plugins -->
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>

    <!-- Select2 -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        document.getElementById("loading").classList.remove("d-none");

        // Initial Window On Load Event
        $(document).ready(async function() {
            //Reset Form
            if (document.getElementById('store-cutting-form-reject')) {
                document.getElementById('store-cutting-form-reject').reset();

                $("#act_costing_id").val(null).trigger("change");
            }

            // 🔹 Reset semua input di card Input Fabric
            const fabricInputs = document.querySelectorAll('#cardInputFabric input');
            fabricInputs.forEach(input => {
                if (input.type === 'number') {
                    input.value = 0; // untuk input number
                } else {
                    input.value = ''; // untuk text / readonly
                }
            });

            datatable_list.ajax.reload();

            // Select2 Prevent Step-Jump Input ( Step = WS -> Color -> Panel )
            // $("#color").prop("disabled", true);
            // $("#panel").prop("disabled", true);

            // cuttingRejectCode();

            document.getElementById("loading").classList.add("d-none");
        });

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

        // Step One (WS) on change event
        $('#act_costing_id').on('change', function(e) {
            if (this.value) {
                updateColorList();
                updateOrderInfo();
            }
        });

        // Step Two (Color) on change event
        $('#color').on('change', function(e) {
            if (this.value) {
                updatePanelList();
                cuttingRejectTableReload();
            }
        });

        // Update Order Information Based on Order WS and Order Color
        function updateOrderInfo() {
            return $.ajax({
                url: '{{ route('get-general-order') }}',
                type: 'get',
                data: {
                    act_costing_id: $('#act_costing_id').val(),
                    color: $('#color').val(),
                },
                dataType: 'json',
                success: function(res) {
                    if (res) {
                        document.getElementById('act_costing_ws').value = res.kpno;
                        document.getElementById('buyer_id').value = res.id_buyer;
                        document.getElementById('buyer').value = res.buyer;
                        document.getElementById('style').value = res.styleno;
                    }
                },
            });
        }

        // Update Color Select Option Based on Order WS
        function updateColorList() {
            document.getElementById('color').value = null;

            return $.ajax({
                url: '{{ route('get-colors') }}',
                type: 'get',
                data: {
                    act_costing_id: $('#act_costing_id').val(),
                },
                success: function(res) {
                    if (res) {
                        // Update this step
                        let select = document.getElementById("color");

                        select.innerHTML = "";

                        for (let i = 0; i < res.length; i++) {
                            let newOption = document.createElement("option");
                            newOption.value = res[i].color;
                            newOption.innerHTML = res[i].color;

                            select.appendChild(newOption);
                        }

                        // select.removeAttribute("disabled");

                        $("#color").val(res[0].color).trigger("change");
                    }
                },
            });
        }

        // Update Panel Select Option Based on Order WS and Color WS
        function updatePanelList() {
            document.getElementById('panel').value = null;
            return $.ajax({
                url: '{{ route('get-panels') }}',
                type: 'get',
                data: {
                    act_costing_id: $('#act_costing_id').val(),
                    color: $('#color').val(),
                },
                success: function(res) {
                    if (res) {
                        // Update this step
                        let select = document.getElementById("panel");

                        select.innerHTML = "";

                        for (let i = 0; i < res.length; i++) {
                            let newOption = document.createElement("option");
                            newOption.value = res[i].panel;
                            newOption.innerHTML = res[i].panel;

                            select.appendChild(newOption);
                        }

                        // select.removeAttribute("disabled");

                        $("#panel").val(res[0].color).trigger("change");
                    }
                },
            });
        }

        let cuttingRejectTable = $("#cutting-reject-table").DataTable({
            processing: true,
            ordering: false,
            serverSide: true,
            paging: false,
            ajax: {
                url: '{{ route('get-form-reject-sizes') }}',
                data: function(d) {
                    d.id = $("#id").val();
                    d.act_costing_id = $("#act_costing_id").val();
                    d.color = $("#color").val();
                },
            },
            columns: [{
                    data: 'so_det_id',
                },
                {
                    data: 'size',
                },
                {
                    data: 'dest',
                },
                {
                    data: 'qty',
                },
            ],
            columnDefs: [{
                    targets: [0],
                    className: "d-none",
                    render: (data, type, row, meta) => {
                        let input =
                            `<input type='text' class='form-control form-control-sm' id='so_det_id_` + meta
                            .row + `' name='so_det_id[` + meta.row + `]' value='` + data + `' readonly>`

                        return input;
                    }
                },
                {
                    targets: [1, 2],
                    className: "text-nowrap",
                    render: (data, type, row, meta) => {
                        return data;
                    }
                },
                {
                    targets: [3],
                    className: "text-nowrap",
                    render: (data, type, row, meta) => {
                        let input =
                            `<input type='number' class='form-control form-control-sm detail-qty' id='qty_` +
                            meta.row + `' name='qty[` + meta.row + `]' value="` + data +
                            `" onkeyup="calculateTotalDetailQty()" onchange="calculateTotalDetailQty()">`

                        return input;
                    }
                },
            ],
            drawCallback: async function(settings) {
                await calculateTotalDetailQty();
            }
        });

        function cuttingRejectTableReload() {
            $("#cutting-reject-table").DataTable().ajax.reload();
        }

        function cuttingRejectCode() {
            $.ajax({
                url: "{{ route('generate-code-cutting-reject') }}",
                type: "get",
                success: function(response) {
                    document.getElementById("no_form").value = response
                },
                error: function(jqXHR) {
                    console.error(jqXHR);
                }
            });
        }

        function calculateTotalDetailQty() {
            let detailQtyElements = document.getElementsByClassName("detail-qty");

            console.log(detailQtyElements.length);

            let totalQty = 0;
            for (let i = 0; i < detailQtyElements.length; i++) {
                console.log(i, detailQtyElements[i].value, detailQtyElements[i]);
                totalQty += Number(detailQtyElements[i].value);
            }

            document.getElementById("total-detail-qty").innerHTML = totalQty;
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
                type: "GET",
                dataType: 'json',
                url: '{{ route('get-scanned-form-cut-input') }}/' + txtbarcode_scan, // route for checking barcode
                data: {
                    _token: '{{ csrf_token() }}',
                    txtbarcode_scan: txtbarcode_scan
                },
                success: function(res) {

                    if (typeof res === 'object' && res !== null) {
                        if (res.qty > 0) {
                            currentScannedItem = res;
                            document.getElementById('txtbarcode').value = res.id_roll;
                            document.getElementById('txtno_roll').value = res.roll_buyer;
                            document.getElementById('txtno_lot').value = res.lot;
                            document.getElementById('txtid_item').value = res.id_item;
                            document.getElementById('txtitem_name').value = res.detail_item;
                            document.getElementById('txtqty_awal').value = res.qty_in;
                            document.getElementById('txtqty_roll').value = res.qty;
                            document.getElementById('txtunit').value = res.unit;
                            stopScannerFabric();
                            // ✅ Close the modal
                            const modalEl = document.getElementById('startFabricModal');
                            const modal = bootstrap.Modal.getInstance(modalEl);
                            if (modal) modal.hide();

                            iziToast.success({
                                title: 'Berhasil!',
                                message: 'Data berhasil dimasukkan.',
                                position: 'topRight',
                                timeout: 2000,
                                pauseOnHover: true
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: 'Qty sudah habis.',
                                showCancelButton: false,
                                showConfirmButton: true,
                                confirmButtonText: 'Oke',
                            });
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

        function handleStart() {
            const barcode = document.getElementById('txtbarcode').value.trim();
            const id = document.getElementById('id').value.trim();

            if (!barcode) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Barcode kosong',
                    text: 'Silahkan scan barcode terlebih dahulu.',
                });
                return;
            }

            add_barcode();

        }

        function add_barcode() {
            const barcode = document.getElementById('txtbarcode').value.trim();
            const id = document.getElementById('id').value.trim();
            const qty_roll = parseFloat(document.getElementById('txtqty_roll').value) || 0;
            const qty_sisa = parseFloat(document.getElementById('txtqty_sisa').value) || 0;
            const qty_pakai = parseFloat(document.getElementById('txtqty_pakai').value) || 0;
            const qty_reject = parseFloat(document.getElementById('txtqty_reject').value) || 0;
            const tot_pakai = parseFloat(document.getElementById('txttot_pemakaian').value) || 0;
            console.log(qty_sisa, qty_pakai, qty_reject);


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
                        url: '{{ route('save_fabric_form_reject') }}',
                        data: {
                            _token: '{{ csrf_token() }}',
                            barcode: barcode,
                            id: id,
                            qty_roll: qty_roll,
                            qty_sisa: qty_sisa,
                            qty_pakai: qty_pakai,
                            qty_reject: qty_reject,
                            tot_pakai: tot_pakai
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Form Disimpan!',
                                html: `<p>Data berhasil disimpan.</p>`
                            }).then(() => {
                                window.location.reload();
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


        let datatable_list = $("#datatable_list").DataTable({
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
                url: '{{ route('show_fabric_form_reject') }}',
                data: function(d) {
                    d.id = $('#id').val();
                },
            },
            columns: [{
                    data: 'barcode'
                },
                {
                    data: 'lot'
                },
                {
                    data: 'roll_buyer'
                },
                {
                    data: 'qty_in'
                },
                {
                    data: 'qty_roll'
                },
                {
                    data: 'qty_pakai'
                },
                {
                    data: 'qty_reject'
                },
                {
                    data: 'sisa_kain'
                },
                {
                    data: 'short_roll'
                },
            ],
            columnDefs: [{
                    targets: [0, 1, 2],
                    className: 'text-center'
                },
                {
                    targets: [3, 4, 5, 6, 7, 8],
                    className: 'text-end'
                }
            ],

            // 🔹 TOTAL FOOTER
            footerCallback: function(row, data, start, end, display) {
                const api = this.api();

                // helper convert ke number
                const intVal = i => typeof i === 'string' ?
                    i.replace(/[^0-9.-]/g, '') * 1 :
                    typeof i === 'number' ?
                    i :
                    0;

                // kolom yang mau ditotal
                const cols = [3, 4, 5, 6, 7, 8];

                cols.forEach(col => {
                    const total = api
                        .column(col, {
                            page: 'current'
                        })
                        .data()
                        .reduce((a, b) => intVal(a) + intVal(b), 0);

                    $(api.column(col).footer()).html(
                        total.toLocaleString('id-ID')
                    );
                });
            }
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const qtyPakaiEl = document.getElementById('txtqty_pakai');
            const qtyRejectEl = document.getElementById('txtqty_reject');
            const qtySisaEl = document.getElementById('txtqty_sisa');
            const qtyRollEl = document.getElementById('txtqty_roll');
            const totPakaiEl = document.getElementById('txttot_pemakaian');
            const shortRollEl = document.getElementById('txtshort_roll');

            function updateTotals() {
                const qtyPakai = parseFloat(qtyPakaiEl.value) || 0;
                const qtyReject = parseFloat(qtyRejectEl.value) || 0;
                const qtySisa = parseFloat(qtySisaEl.value) || 0;
                const qtyRoll = parseFloat(qtyRollEl.value) || 0;

                // Total pakai = qty pakai + qty reject
                totPakaiEl.value = qtyPakai + qtyReject;

                // Short roll = qty pakai + qty reject + sisa kain - qty roll
                shortRollEl.value = Math.round((qtyPakai + qtyReject + qtySisa - qtyRoll) * 100) / 100;

            }

            // Pasang event listener untuk semua input yang mempengaruhi
            [qtyPakaiEl, qtyRejectEl, qtySisaEl, qtyRollEl].forEach(el => {
                el.addEventListener('input', updateTotals);
            });

            // Hitung sekali saat halaman load
            updateTotals();
        });

        document.addEventListener('DOMContentLoaded', () => {
            const btn = document.getElementById('btnScrollDown');
            const target = document.getElementById('cardInputFabric');

            if (!btn || !target) return;

            const observer = new IntersectionObserver(
                ([entry]) => {
                    if (entry.isIntersecting) {
                        btn.style.display = 'none';
                    } else {
                        btn.style.display = 'flex';
                    }
                }, {
                    threshold: 0.1 // card mulai terlihat 10%
                }
            );

            observer.observe(target);

            btn.addEventListener('click', () => {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            });
        });
    </script>
@endsection
