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
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-list"></i> New Form GR Panel</h5>
        </div>
        <div class="card-body pb-0">
            <div class="row mb-3 g-2">
                <div class="col-12 col-md-4">
                    <button type="button" class="btn btn-warning btn-sm w-100" onclick="openScannerModalFabric()">
                        <i class="fas fa-barcode"></i> Scan Barcode
                    </button>
                </div>

                <!-- spacer -->
                <div class="d-none d-md-block col-md-4"></div>

                <div class="col-12 col-md-4">
                    <a type="button" class="btn btn-secondary btn-sm w-100" href="{{ route('form_gr_panel') }}"
                        target="">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                </div>
            </div>


            <div class="row g-2 mb-3">

                <div class="col-12 col-sm-6 col-lg-6">
                    <label for="txtid_item" class="form-label mb-1">
                        <small><strong>ID Item</strong></small>
                    </label>
                    <input type="text" id="txtid_item" name="txtid_item"
                        class="form-control form-control-sm border-primary" readonly>
                </div>

                <div class="col-12 col-sm-12 col-lg-6">
                    <label for="txtitem_name" class="form-label mb-1">
                        <small><strong>Item Name</strong></small>
                    </label>
                    <input type="text" id="txtitem_name" name="txtitem_name"
                        class="form-control form-control-sm border-primary" readonly>
                </div>

                <div class="col-12 col-sm-6 col-lg-4">
                    <label for="txtbarcode" class="form-label mb-1">
                        <small><strong>Barcode</strong></small>
                    </label>
                    <input type="text" id="txtbarcode" name="txtbarcode"
                        class="form-control form-control-sm border-primary" readonly>
                </div>
                <div class="col-12 col-sm-4 col-lg-4">
                    <label for="txtqty_awal" class="form-label mb-1">
                        <small><strong>Qty Awal</strong></small>
                    </label>
                    <input type="text" id="txtqty_awal" name="txtqty_awal"
                        class="form-control form-control-sm border-primary" readonly>
                </div>
                <div class="col-12 col-sm-4 col-lg-4">
                    <label for="txtunit" class="form-label mb-1">
                        <small><strong>Unit</strong></small>
                    </label>
                    <input type="text" id="txtunit" name="txtunit"
                        class="form-control form-control-sm border-primary" readonly>
                </div>


                <div class="col-12 col-sm-4 col-lg-4">
                    <label for="txtqty_pakai" class="form-label mb-1">
                        <small><strong>Qty Pakai</strong></small>
                    </label>
                    <input type="number" id="txtqty_pakai" name="txtqty_pakai"
                        class="form-control form-control-sm border-primary" readonly>
                </div>
                <div class="col-12 col-sm-4 col-lg-4">
                    <label for="txtws_act" class="form-label mb-1">
                        <small><strong>WS Aktual</strong></small>
                    </label>
                    <input type="text" id="txtws_act" name="txtws_act"
                        class="form-control form-control-sm border-primary" readonly>
                </div>
                <div class="col-12 col-sm-4 col-lg-4">
                    <label for="txtstyleno" class="form-label mb-1">
                        <small><strong>Style</strong></small>
                    </label>
                    <input type="text" id="txtstyleno" name="txtstyleno"
                        class="form-control form-control-sm border-primary" readonly>
                </div>


                <div class="col-12 col-sm-6 col-lg-4">
                    <label for="txtcolor" class="form-label mb-1">
                        <small><strong>Color</strong></small>
                    </label>
                    <input type="text" id="txtcolor" name="txtcolor"
                        class="form-control form-control-sm border-primary" readonly>
                </div>
                <div class="col-12 col-sm-6 col-lg-4">
                    <label for="txtno_roll" class="form-label mb-1">
                        <small><strong>No. Roll</strong></small>
                    </label>
                    <input type="text" id="txtno_roll" name="txtno_roll"
                        class="form-control form-control-sm border-primary" readonly>
                </div>
                <div class="col-12 col-sm-6 col-lg-4">
                    <label for="txtno_lot" class="form-label mb-1">
                        <small><strong>No. Lot</strong></small>
                    </label>
                    <input type="text" id="txtno_lot" name="txtno_lot"
                        class="form-control form-control-sm border-primary" readonly>
                </div>

            </div>
        </div>
    </div>

    <div class="card card-sb" id="cardAlokasiPanel">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-list"></i> Alokasi GR Panel</h5>
        </div>
        <div class="card-body pb-0">
            <div class="row g-2 mb-3">
                <div class="col-12 col-sm-6 col-lg-4">
                    <label for="cboalo_tujuan" class="form-label mb-1">
                        <small><strong>Tujuan</strong></small>
                    </label>
                    <select class="form-control form-control-sm select2bs4 select-border-primary visual-input"
                        id="cboalo_tujuan" name="cboalo_tujuan" style="width: 100%;">
                        <option value="">Pilih Tujuan</option>
                        @foreach ($data_dept as $dd)
                            <option value="{{ $dd->isi }}">
                                {{ $dd->tampil }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-lg-4">
                    <label for="cboalo_ws" class="form-label mb-1">
                        <small><strong>WS</strong></small>
                    </label>
                    <select class="form-control form-control-sm select2bs4 select-border-primary visual-input"
                        id="cboalo_ws" name="cboalo_ws" style="width: 100%;" onchange="get_ws_all();">
                        <option value="">Pilih WS</option>
                        @foreach ($data_ws as $dw)
                            <option value="{{ $dw->isi }}">
                                {{ $dw->tampil }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-lg-4">
                    <label for="txtalo_buyer" class="form-label mb-1">
                        <small><strong>Buyer</strong></small>
                    </label>
                    <input type="text" id="txtalo_buyer" name="txtalo_buyer"
                        class="form-control form-control-sm border-primary" readonly>
                </div>

                <div class="col-12 col-sm-6 col-lg-4">
                    <label for="txtalo_style" class="form-label mb-1">
                        <small><strong>Style</strong></small>
                    </label>
                    <input type="text" id="txtalo_style" name="txtalo_style"
                        class="form-control form-control-sm border-primary" readonly>
                </div>
                <div class="col-12 col-sm-12 col-lg-4">
                    <label for="cboalo_color" class="form-label mb-1">
                        <small><strong>Color</strong></small>
                    </label>
                    <select class='form-control select2bs4 form-control-sm' style='width: 100%;' name='cboalo_color'
                        id='cboalo_color'></select>
                </div>
                <div class="col-12 col-sm-4 col-lg-4">
                    <label for="cboalo_panel" class="form-label mb-1">
                        <small><strong>Panel</strong></small>
                    </label>
                    <select class='form-control select2bs4 form-control-sm' style='width: 100%;' name='cboalo_panel'
                        id='cboalo_panel'></select>
                </div>

                <div class="col-12 col-sm-4 col-lg-4">
                    <label for="txtalo_qty_pakai" class="form-label mb-1">
                        <small><strong>Qty Pakai</strong></small>
                    </label>
                    <input type="number" id="txtalo_qty_pakai" name="txtalo_qty_pakai"
                        class="form-control form-control-sm border-primary">
                </div>
                <div class="col-12 col-sm-4 col-lg-4">
                    <label for="txtalo_sisa_kain" class="form-label mb-1">
                        <small><strong>Sisa Kain</strong></small>
                    </label>
                    <input type="number" id="txtalo_sisa_kain" name="txtalo_sisa_kain"
                        class="form-control form-control-sm border-primary" readonly>
                </div>
                <div class="col-12 col-sm-4 col-lg-4">
                    <label for="txtalo_unit" class="form-label mb-1">
                        <small><strong>Unit</strong></small>
                    </label>
                    <input type="text" id="txtalo_unit" name="txtalo_unit"
                        class="form-control form-control-sm border-primary" readonly>
                </div>
            </div>
        </div>
    </div>


    <div class="card card-sb" id="cardDetailGR">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-list"></i> Detail GR Panel</h5>
        </div>
        <div class="card-body pb-0">
            <div class="row g-2 mb-3">

                <div class="col-12 col-sm-6 col-lg-3">
                    <label for="txtinput_part" class="form-label mb-1">
                        <small><strong>Pilih Part</strong></small>
                    </label>
                    <input type="text" id="txtinput_part" name="txtinput_part"
                        class="form-control form-control-sm border-primary">
                </div>
                <div class="col-12 col-sm-6 col-lg-3">
                    <label for="cboinput_size" class="form-label mb-1">
                        <small><strong>Pilih Size</strong></small>
                    </label>
                    <select class='form-control select2bs4 form-control-sm' style='width: 100%;' name='cboinput_size'
                        id='cboinput_size'></select>
                </div>
                <div class="col-12 col-sm-6 col-lg-3">
                    <label for="txtinput_qty_panel" class="form-label mb-1">
                        <small><strong>Qty Panel</strong></small>
                    </label>
                    <input type="number" id="txtinput_qty_panel" name="txtinput_qty_panel"
                        class="form-control form-control-sm border-primary">
                </div>
                <div class="col-12 col-sm-6 col-lg-3">
                    <label class="form-label mb-1">
                        <small><strong>&nbsp;</strong></small>
                    </label>
                    <button class="btn btn-primary btn-sm w-100" type="button" id="btnAddPanel">
                        <i class="fas fa-plus"></i> Add
                    </button>
                </div>
            </div>

            <div class="row g-2 mb-3">
                <div class="table-responsive">
                    <table id="tblTempPanel" class="table table-bordered table-striped w-100 text-wrap">
                        <thead class="bg-sb">
                            <tr style="text-align:center; vertical-align:middle">
                                <th scope="col">No</th>
                                <th scope="col">Part</th>
                                <th scope="col">Size</th>
                                <th scope="col">Qty</th>
                                <th scope="col">Act</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>

            <div class="row g-2 mb-3">
                <div class="col-12 col-sm-6 col-lg-3 ms-auto">
                    <button type="button" class="btn btn-success w-100" id="btnSavePanel" onclick="saveGR()">
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

        function notif() {
            alert("Maaf, Fitur belum tersedia!");
        }

        let wsTimer = null;

        $('#cboalo_ws').on('change', function() {
            clearTimeout(wsTimer);
            wsTimer = setTimeout(() => {
                get_ws_all();
            }, 100);
        });

        function setVal(id, value) {
            const el = document.getElementById(id);
            if (el !== null) {
                el.value = value ?? '';
            }
        }

        let tempPanels = []; // array simpan sementara
        let tblTempPanel = $("#tblTempPanel").DataTable({
            ordering: false,
            responsive: true,
            processing: true,
            serverSide: false,
            paging: false,
            searching: false,
            scrollX: true,
            scrollCollapse: false,
            info: false,
        });

        // Fungsi render tabel ke DataTable
        function renderTable() {
            tblTempPanel.clear(); // clear dulu

            tempPanels.forEach((p, i) => {
                tblTempPanel.row.add([
                    i + 1,
                    p.part,
                    p.size,
                    p.qty,
                    `<button class="btn btn-danger btn-sm btn-remove" data-index="${i}">
                <i class="fas fa-trash"></i>
            </button>`
                ]);
            });

            tblTempPanel.draw();
        }

        // Tambah panel
        $('#btnAddPanel').click(function() {
            const part = $('#txtinput_part').val().trim();
            const size = $('#cboinput_size').val();
            const qty = parseInt($('#txtinput_qty_panel').val());

            if (!part || !size || !qty) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan',
                    text: 'Semua field wajib diisi!'
                });
                return;
            }

            tempPanels.push({
                part,
                size,
                qty
            });
            renderTable();

            // reset input
            $('#txtinput_part').val('');
            $('#cboinput_size').val('').trigger('change.select2');
            $('#txtinput_qty_panel').val('');
        });

        // Hapus panel
        $(document).on('click', '.btn-remove', function() {
            const index = $(this).data('index');
            tempPanels.splice(index, 1);
            renderTable();
        });
    </script>
    <script>
        $(document).ready(function() {
            // 🔹 Reset semua input di card Input Fabric
            const fabricInputs = document.querySelectorAll('#cardInputFabric input');
            fabricInputs.forEach(input => {
                if (input.type === 'number') {
                    input.value = 0; // untuk input number
                } else {
                    input.value = ''; // untuk text / readonly
                }
            });

            // Reset input text & number
            $('#cardAlokasiPanel input').each(function() {
                if (this.type === 'number') {
                    this.value = 0; // kalau number default ke 0
                } else {
                    this.value = ''; // kalau text kosong
                }
            });

            // Reset select normal
            $('#cardAlokasiPanel select').each(function() {
                $(this).val('').trigger('change'); // trigger untuk Select2
            });


            // Reset input text & number
            $('#cardDetailGR input').each(function() {
                if (this.type === 'number') {
                    this.value = 0; // kalau number default ke 0
                } else {
                    this.value = ''; // kalau text kosong
                }
            });
            // Reset select normal
            $('#cardDetailGR select').each(function() {
                $(this).val('').trigger('change'); // trigger untuk Select2
            });

            renderTable();

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

            $.ajax({
                type: "GET",
                dataType: 'json',
                url: '{{ route('get_barcode_form_gr_panel') }}/' + txtbarcode_scan,
                data: {
                    txtbarcode_scan: txtbarcode_scan
                },
                success: function(res) {

                    if (res.status === 'success') {
                        let data = res.data;
                        document.getElementById('txtbarcode').value = data.barcode;
                        document.getElementById('txtno_roll').value = data.roll_buyer;
                        document.getElementById('txtno_lot').value = data.lot;
                        document.getElementById('txtid_item').value = data.id_item;
                        document.getElementById('txtitem_name').value = data.detail_item;
                        document.getElementById('txtqty_awal').value = data.qty_in;
                        document.getElementById('txtqty_pakai').value = data.qty_pakai;
                        document.getElementById('txtunit').value = data.unit;
                        document.getElementById('txtws_act').value = data.ws_act;
                        document.getElementById('txtstyleno').value = data.styleno;
                        document.getElementById('txtcolor').value = data.color;
                        document.getElementById('txtalo_unit').value = data.unit;
                        stopScannerFabric();

                        const modalEl = document.getElementById('startFabricModal');
                        const modal = bootstrap.Modal.getInstance(modalEl);
                        if (modal) modal.hide();

                        iziToast.success({
                            title: 'Berhasil!',
                            message: 'Data berhasil dimasukkan.',
                            position: 'topRight',
                            timeout: 2000
                        });


                    } else {
                        $('#txtbarcode_scan').val('');
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: res.message ?? 'Roll tidak tersedia'
                        });
                    }
                }, // ✅ KOMA PENTING

                error: function(jqXHR) {
                    console.log(jqXHR);

                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: jqXHR.responseText ? jqXHR.responseText : 'Roll tidak tersedia.',
                        confirmButtonText: 'Oke'
                    });
                }
            });
        }

        function get_ws_all() {
            const cboalo_ws = $('#cboalo_ws').val();
            if (!cboalo_ws) return;

            $.ajax({
                type: 'GET',
                dataType: 'json',
                url: '{{ route('get_ws_all_form_gr_panel') }}',
                data: {
                    cboalo_ws
                },
                beforeSend: function() {
                    // optional: reset dulu
                    setVal('txtalo_buyer', '');
                    setVal('txtalo_style', '');
                    $('#cboalo_color').html('<option value="">Loading...</option>');
                    $('#cboalo_panel').html('<option value="">Loading...</option>');
                    $('#cboinput_size').html('<option value="">Loading...</option>');
                },
                success: function(res) {
                    if (res.status === 'success') {
                        const d = res.data;

                        setVal('txtalo_buyer', d.buyer);
                        setVal('txtalo_style', d.styleno);

                        $('#cboalo_color').html(d.colors);
                        $('#cboalo_panel').html(d.panels);
                        $('#cboinput_size').html(d.sizes);

                        // refresh select2 jika dipakai
                        $('#cboalo_color').trigger('change.select2');
                        $('#cboalo_panel').trigger('change.select2');
                        $('#cboinput_size').trigger('change.select2');
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: res.message ?? 'Data WS tidak ditemukan'
                        });
                    }
                },
                error: function(xhr) {
                    console.error(xhr);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Gagal mengambil data WS'
                    });
                }
            });
        }


        $('#txtalo_qty_pakai').on('input', function() {
            hitungSisaKain();
        });

        function hitungSisaKain() {
            const qtyAwal = parseFloat($('#txtqty_pakai').val()) || 0;
            const qtyPakai = parseFloat($('#txtalo_qty_pakai').val()) || 0;

            let sisa = qtyAwal - qtyPakai;
            $('#txtalo_sisa_kain').val(sisa);
        }


        function saveGR() {
            let txtbarcode = $('#txtbarcode').val();
            let txtalo_qty_pakai = $('#txtalo_qty_pakai').val();
            let cboalo_tujuan = $('#cboalo_tujuan').val();
            let cbocolor = $('#cboalo_color').val();
            let cbopanel = $('#cboalo_panel').val();
            let cbows = $('#cboalo_ws').val();

            if (!cboalo_tujuan || !cbocolor || !cbopanel) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Data belum lengkap',
                    text: 'Mohon lengkapi data terlebih dahulu.'
                });
                return;
            }

            const qty = Number(txtalo_qty_pakai);

            if (!qty || qty <= 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Data belum lengkap',
                    text: 'Qty tidak boleh kosong atau 0.'
                });
                return;
            }

            if (tempPanels.length === 0) {
                Swal.fire('Peringatan', 'Tidak ada data untuk disimpan', 'warning');
                return;
            }
            console.log(tempPanels);

            Swal.fire({
                title: 'Simpan data ?',
                text: 'Pastikan data sudah benar.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Simpan',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (!result.isConfirmed) return;

                $('#btnsave').prop('disabled', true);

                $.ajax({
                    type: "POST",
                    dataType: "json",
                    url: '{{ route('save_form_gr_panel') }}',
                    data: {
                        _token: '{{ csrf_token() }}',
                        tempPanels: tempPanels,
                        txtbarcode: txtbarcode,
                        txtalo_qty_pakai: txtalo_qty_pakai,
                        cboalo_tujuan: cboalo_tujuan,
                        cbocolor: cbocolor,
                        cbopanel: cbopanel,
                        cbows: cbows
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            Swal.fire({
                                title: 'Berhasil Disimpan!',
                                html: `No Form: <b>${response.no_form}</b>`,
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                location.reload(); // reload setelah user klik OK
                            });
                        } else {
                            Swal.fire('Gagal', response.message, 'error');
                        }

                    },
                    error: function(xhr) {
                        let message = 'Gagal menyimpan data.';

                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }

                        Swal.fire({
                            icon: 'warning',
                            title: 'Tidak bisa disimpan',
                            text: message
                        });
                    },

                    complete: function() {
                        $('#btnsave').prop('disabled', false);
                    }
                });

            });

        }
    </script>
@endsection
