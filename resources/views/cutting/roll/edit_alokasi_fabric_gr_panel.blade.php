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


    <div class="card card-sb" id="cardInputFabric">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-list"></i> Edit Alokasi Fabric GR Panel</h5>
        </div>
        <div class="card-body pb-0">
            <div class="row mb-3 g-2">
                <div class="col-12 col-md-4">
                    <button type="button" class="btn btn-warning btn-sm w-100" onclick="openScannerModalFabric()" >
                        <i class="fas fa-barcode"></i> Scan Barcode
                    </button>
                </div>

                <!-- spacer -->
                <div class="d-none d-md-block col-md-4"></div>

                <div class="col-12 col-md-4">
                    <a type="button" class="btn btn-secondary btn-sm w-100"
                        href="{{ route('alokasi_fabric_gr_panel') }}" target="">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                </div>
            </div>


            <div class="row g-2 mb-3">
                <div class="col-12 col-sm-6 col-lg-3">
                    <label for="txtbarcode" class="form-label mb-1">
                        <small><strong>Barcode</strong></small>
                    </label>
                    <input type="text" id="txtbarcode" name="txtbarcode"
                        class="form-control form-control-sm border-primary" value="{{ $data->barcode }}" readonly>
                </div>

                <div class="col-12 col-sm-6 col-lg-3">
                    <label for="txtno_roll" class="form-label mb-1">
                        <small><strong>No. Roll</strong></small>
                    </label>
                    <input type="text" id="txtno_roll" name="txtno_roll"
                        class="form-control form-control-sm border-primary" value="{{ $data->no_roll }}" readonly>
                </div>

                <div class="col-12 col-sm-6 col-lg-3">
                    <label for="txtno_lot" class="form-label mb-1">
                        <small><strong>No. Lot</strong></small>
                    </label>
                    <input type="text" id="txtno_lot" name="txtno_lot"
                        class="form-control form-control-sm border-primary" value="{{ $data->no_lot }}" readonly>
                </div>

                <div class="col-12 col-sm-6 col-lg-3">
                    <label for="txtid_item" class="form-label mb-1">
                        <small><strong>ID Item</strong></small>
                    </label>
                    <input type="text" id="txtid_item" name="txtid_item"
                        class="form-control form-control-sm border-primary" value="{{ $data->id_item }}" readonly>
                </div>

                <div class="col-12 col-sm-12 col-lg-3">
                    <label for="txtitem_name" class="form-label mb-1">
                        <small><strong>Item Name</strong></small>
                    </label>
                    <input type="text" id="txtitem_name" name="txtitem_name"
                        class="form-control form-control-sm border-primary" value="{{ $data->detail_item }}" readonly>
                </div>

                <div class="col-12 col-sm-4 col-lg-3">
                    <label for="txtqty_awal" class="form-label mb-1">
                        <small><strong>Qty Awal</strong></small>
                    </label>
                    <div class="input-group input-group-sm">
                        <input type="text" id="txtqty_awal" name="txtqty_awal" class="form-control form-control-sm border-primary" value="{{ $data->qty_in ? $data->qty_in : $data->qty }}" readonly>
                        <input type="text" id="txtunit_awal" name="txtunit_awal" class="form-control form-control-sm border-primary" value="{{ $data->unit_awal }}" readonly>
                    </div>
                </div>
                <div class="col-12 col-sm-4 col-lg-3">
                    <label for="txtqty_roll" class="form-label mb-1">
                        <small><strong>Qty Roll</strong></small>
                    </label>
                    <div class="input-group input-group-sm">
                        <input type="number" id="txtqty_roll" name="txtqty_roll" class="form-control form-control-sm border-primary" value="{{ $data->qty_roll }}" readonly>
                        <span class="input-group-text unit-text border-primary"> {{ $data->unit }} </span>
                    </div>
                </div>
                <div class="col-12 col-sm-4 col-lg-3">
                    <label for="txtunit" class="form-label mb-1">
                        <small><strong>Unit</strong></small>
                    </label>
                    <input type="text" id="txtunit" name="txtunit"
                        class="form-control form-control-sm border-primary" value="{{ $data->unit }}" readonly>
                </div>
                <div class="col-12 col-sm-4 col-lg-3">
                    <label for="txtqty_pakai" class="form-label mb-1">
                        <small><strong>Qty Pakai</strong></small>
                    </label>
                    <div class="input-group input-group-sm">
                        <input type="number" id="txtqty_pakai" name="txtqty_pakai" class="form-control form-control-sm border-primary" value="{{ $data->qty_pakai }}">
                        <span class="input-group-text unit-text border-primary"> {{ $data->unit }} </span>
                    </div>
                </div>
                <div class="col-12 col-sm-4 col-lg-3">
                    <label for="txtqty_sisa" class="form-label mb-1">
                        <small><strong>Sisa Kain</strong></small>
                    </label>
                    <div class="input-group input-group-sm">
                        <input type="number" id="txtqty_sisa" name="txtqty_sisa" class="form-control form-control-sm border-primary" value="{{ $data->sisa_kain }}">
                        <span class="input-group-text unit-text border-primary"> {{ $data->unit }} </span>
                    </div>
                </div>
                <div class="col-12 col-sm-4 col-lg-3">
                    <label for="txtshort_roll" class="form-label mb-1">
                        <small><strong>Short Roll</strong></small>
                    </label>
                    <div class="input-group input-group-sm">
                        <input type="number" id="txtshort_roll" name="txtshort_roll" class="form-control form-control-sm border-primary" readonly>
                        <span class="input-group-text unit-text border-primary"> {{ $data->unit }} </span>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-lg-4">
                    <label for="cbows_act" class="form-label mb-1">
                        <small><strong>WS</strong></small>
                    </label>
                    <select class="form-control form-control-sm select2bs4 select-border-primary visual-input"
                        id="cbows_act" name="cbows_act" style="width: 100%;">
                        <option value="">Pilih WS Aktual</option>
                        @foreach ($data_ws as $dw)
                            <option value="{{ $dw->isi }}"
                                {{ $data->ws == $dw->isi ? 'selected' : '' }}>
                                {{ $dw->tampil }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-lg-4">
                    <label for="cbopanel" class="form-label mb-1">
                        <small><strong>Panel</strong></small>
                    </label>
                    <select class="form-control form-control-sm select2bs4 select-border-primary visual-input"
                        id="cbopanel" name="cbopanel" style="width: 100%;">
                        <option value="">Pilih Panel</option>
                    </select>
                </div>
                <div class="col-12 col-sm-12 col-lg-4 d-flex align-items-end">
                    <a id="btnAddBarcode" onclick="handleStart()" class="btn btn-sm btn-primary w-100 py-2">
                        <i class="fas fa-save me-1"></i> Update
                    </a>
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

        // Panel Select Option Based on WS Aktual
        function updatePanelList(selectedPanel = null) {
            return $.ajax({
                url: '{{ route('get_panel_list_alokasi_fabric_gr_panel') }}',
                type: 'get',
                data: {
                    ws_act: $('#cbows_act').val(),
                },
                success: function(res) {
                    if (res) {
                        document.getElementById('cbopanel').innerHTML = res;
                        if (selectedPanel) {
                            $('#cbopanel').val(selectedPanel).trigger('change');
                        }
                    }
                },
            });
        }

        $('#cbows_act').on('change', function(e) {
            if (this.value) {
                updatePanelList();
            } else {
                $('#cbopanel').html('<option value="">Pilih Panel</option>').val(null).trigger('change');
            }
        });

        $(document).ready(function() {
            // Load panel on edit
            if ($('#cbows_act').val()) {
                updatePanelList("{{ $data->panel }}");
            }
        });

        function notif() {
            alert("Maaf, Fitur belum tersedia!");
        }
    </script>
    <script>
        $(document).ready(function() {
            // 🔹 Reset semua input di card Input Fabric
            // const fabricInputs = document.querySelectorAll('#cardInputFabric input');
            // fabricInputs.forEach(input => {
            //     if (input.type === 'number') {
            //         input.value = 0; // untuk input number
            //     } else {
            //         input.value = ''; // untuk text / readonly
            //     }
            // });
        });

        document.addEventListener('DOMContentLoaded', () => {
            const qtyPakaiEl = document.getElementById('txtqty_pakai');
            const qtySisaEl = document.getElementById('txtqty_sisa');
            const qtyRollEl = document.getElementById('txtqty_roll');
            const shortRollEl = document.getElementById('txtshort_roll');

            function updateTotals() {
                const qtyPakai = parseFloat(qtyPakaiEl.value) || 0;
                const qtySisa = parseFloat(qtySisaEl.value) || 0;
                const qtyRoll = parseFloat(qtyRollEl.value) || 0;

                // Short roll = qty pakai + qty reject + sisa kain - qty roll
                shortRollEl.value = Math.round((qtyPakai + qtySisa - qtyRoll) * 100) / 100;

            }

            // Pasang event listener untuk semua input yang mempengaruhi
            [qtyPakaiEl, qtySisaEl, qtyRollEl].forEach(el => {
                el.addEventListener('input', updateTotals);
            });

            // Hitung sekali saat halaman load
            updateTotals();
        });


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
                        console.log(res)
                        if (res.qty > 0) {
                            currentScannedItem = res;
                            document.getElementById('txtbarcode').value = res.id_roll;
                            document.getElementById('txtno_roll').value = res.roll_buyer;
                            document.getElementById('txtno_lot').value = res.lot;
                            document.getElementById('txtid_item').value = res.id_item;
                            document.getElementById('txtitem_name').value = res.detail_item;
                            document.getElementById('txtqty_awal').value = res.qty_in ? res.qty_in : res.qty;
                            document.getElementById('txtunit_awal').value = res.unit;

                            if (res.unit == 'YRD' || res.unit == 'YARD') {
                                document.getElementById("txtqty_roll").value = (res.qty * 0.9144).round(2);
                                document.getElementById("txtunit").value = 'METER';

                                for(let i = 0; i < document.getElementsByClassName('unit-text').length; i++) {
                                    document.getElementsByClassName('unit-text')[i].innerHTML = 'METER';
                                }
                            } else {
                                document.getElementById("txtqty_roll").value = res.qty;
                                document.getElementById("txtunit").value = res.unit;

                                for(let i = 0; i < document.getElementsByClassName('unit-text').length; i++) {
                                    document.getElementsByClassName('unit-text')[i].innerHTML = res.unit;
                                }
                            }

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
            const ws_act = document.getElementById('cbows_act').value.trim();
            const panel = document.getElementById('cbopanel').value.trim();

            if (!barcode) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Barcode kosong',
                    text: 'Silahkan scan barcode terlebih dahulu.',
                });
                return;
            }

            if (!ws_act) {
                Swal.fire({
                    icon: 'warning',
                    title: 'WS Actual kosong',
                    text: 'Silahkan isi ws terlebih dahulu.',
                });
                return;
            }

            // if (!panel) {
            //     Swal.fire({
            //         icon: 'warning',
            //         title: 'Panel kosong',
            //         text: 'Silahkan pilih panel terlebih dahulu.',
            //     });
            //     return;
            // }

            update_barcode();
        }

        function update_barcode() {
            const id = "{{ $data->id }}";

            const barcode = document.getElementById('txtbarcode').value.trim();
            const ws_act = document.getElementById('cbows_act').value.trim();
            const panel = document.getElementById('cbopanel').value.trim();
            const qty_roll = parseFloat(document.getElementById('txtqty_roll').value) || 0;
            const qty_sisa = parseFloat(document.getElementById('txtqty_sisa').value) || 0;
            const qty_pakai = parseFloat(document.getElementById('txtqty_pakai').value) || 0;
            const unit = document.getElementById('txtunit').value;

            Swal.fire({
                title: 'Konfirmasi Perubahan',
                html: `
                    <p><b>Barcode :</b> ${barcode}</p>
                    <p><b>WS Actual :</b> ${ws_act}</p>
                    <p><b>Panel :</b> ${panel}</p>
                    <hr>
                    <p>Lanjutkan memperbarui data ini?</p>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Update',
                cancelButtonText: 'Batal'
            }).then((result) => {

                if (result.isConfirmed) {

                    Swal.fire({
                        title: 'Memproses...',
                        text: 'Sedang memperbarui data.',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        type: "PUT",
                        url: "{{ route('update_alokasi_fabric_gr_panel', ':id') }}".replace(':id', id),
                        data: {
                            _token: "{{ csrf_token() }}",
                            barcode: barcode,
                            ws_act: ws_act,
                            panel: panel,
                            qty_roll: qty_roll,
                            qty_sisa: qty_sisa,
                            qty_pakai: qty_pakai,
                            unit: unit
                        },
                        success: function(response) {

                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: 'Data berhasil diperbarui.',
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.href =
                                    "{{ route('alokasi_fabric_gr_panel') }}";
                            });

                        },
                        error: function(xhr) {

                            console.error('Update Error:', xhr.responseText);

                            let message = 'Gagal memperbarui data.';

                            if (xhr.responseJSON?.message) {
                                message = xhr.responseJSON.message;
                            }

                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: message
                            });
                        }
                    });
                }
            });
        }
    </script>
@endsection
