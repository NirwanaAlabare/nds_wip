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
        <h5 class="fw-bold text-sb"><i class="fa fa-plus fa-sm"></i> Tambah Data Penerimaan Fabric Cutting</h5>
        <a href="{{ route('penerimaan-cutting') }}" class="btn btn-primary btn-sm px-1 py-1"><i class="fas fa-reply"></i> Kembali ke Penerimaan Fabric Cutting</a>
    </div>
    <form action="{{ route('store-penerimaan-cutting') }}" method="post" id="store-penerimaan-cutting" onsubmit="submitForm(this, event)">
        @csrf
        <div class="card card-sb">
            <div class="card-header">
                <h5 class="card-title fw-bold">
                    Scan Penerimaan Barcode Fabric
                </h5>
            </div>
            <div class="card-body">
                <div class="row align-items-end">
                    <div class="col-12 col-md-12">
                        <div class="mb-1">
                            <label class="form-label"><small>Scan</small></label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="scan_item" name="scan_item" value="">
                                <button class="btn btn-outline-success" type="button" id="get-item" onclick="fetchScan()">Get</button>
                                <button class="btn btn-outline-primary" type="button" id="scan-item" onclick="initScan()">Scan</button>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="my-1">
                            <div id="reader"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card card-sb">
            <div class="card-header">
                <h5 class="card-title fw-bold">
                    Detail Barcode Fabric
                </h5>
            </div>
            <div class="card-body">
                <div class="row align-items-end">
                    {{-- <div class="col-md-12">
                        <div class="mb-1">
                            <div id="reader"></div>
                        </div>
                    </div> --}}

                    <div class="row">
                        <div class="col-6 col-md-2">
                            <div class="mb-1">
                                <label class="form-label"><small>Barcode</small></label>
                                <input type="hidden" id="whs_bppb_det_id" name="whs_bppb_det_id">
                                <input type="text" class="form-control" id="barcode" name="barcode" value="" readonly>
                            </div>
                        </div>
                        <div class="col-6 col-md-2">
                            <div class="mb-1">
                                <label class="form-label"><small>No. Req</small></label>
                                <input type="text" class="form-control" id="no_req" name="no_req" value="" readonly>
                            </div>
                        </div>
                        <div class="col-6 col-md-2">
                            <div class="mb-1">
                                <label class="form-label"><small>No. BPPB</small></label>
                                <input type="text" class="form-control" id="no_bppb" name="no_bppb" value="" readonly>
                            </div>
                        </div>
                        <div class="col-6 col-md-2">
                            <div class="mb-1">
                                <label class="form-label"><small>Tanggal BPPB</small></label>
                                <input type="text" class="form-control" id="tanggal_bppb" name="tanggal_bppb" value="" readonly>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="mb-1">
                                <label class="form-label"><small>Tujuan</small></label>
                                <input type="text" class="form-control" id="tujuan" name="tujuan" value="" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6 col-md-2">
                            <div class="mb-1">
                                <label class="form-label"><small>No. WS</small></label>
                                <input type="text" class="form-control" id="no_ws" name="no_ws" value="" readonly>
                            </div>
                        </div>
                        <div class="col-6 col-md-2">
                            <div class="mb-1">
                                <label class="form-label"><small>No. WS Act</small></label>
                                <input type="text" class="form-control" id="no_ws_act" name="no_ws_act" value="" readonly>
                            </div>
                        </div>
                        <div class="col-6 col-md-2">
                            <div class="mb-1">
                                <label class="form-label"><small>Qty Out</small></label>
                                <input type="text" class="form-control" id="qty_out" name="qty_out" value="" readonly>
                            </div>
                        </div>
                        <div class="col-6 col-md-2">
                            <div class="mb-1">
                                <label class="form-label"><small>Unit</small></label>
                                <input type="text" class="form-control" id="unit" name="unit" value="" readonly>
                            </div>
                        </div>
                        <div class="col-6 col-md-2">
                            <div class="mb-1">
                                <label class="form-label"><small>Qty Konv</small></label>
                                <input type="text" class="form-control" id="qty_konv" name="qty_konv" value="" readonly>
                            </div>
                        </div>
                        <div class="col-6 col-md-2">
                            <div class="mb-1">
                                <label class="form-label"><small>Unit Konv</small></label>
                                <input type="text" class="form-control" id="unit_konv" name="unit_konv" value="" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6 col-md-2">
                            <div class="mb-1">
                                <label class="form-label"><small>Style</small></label>
                                <input type="text" class="form-control" id="style" name="style" value="" readonly>
                            </div>
                        </div>
                        <div class="col-6 col-md-2">
                            <div class="mb-1">
                                <label class="form-label"><small>Warna</small></label>
                                <input type="text" class="form-control" id="warna" name="warna" value="" readonly>
                            </div>
                        </div>
                        <div class="col-6 col-md-2">
                            <div class="mb-1">
                                <label class="form-label"><small>No. Lot</small></label>
                                <input type="text" class="form-control" id="no_lot" name="no_lot" value="" readonly>
                            </div>
                        </div>
                        <div class="col-6 col-md-2">
                            <div class="mb-1">
                                <label class="form-label"><small>No. Roll</small></label>
                                <input type="text" class="form-control" id="no_roll" name="no_roll" value="" readonly>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="mb-1">
                                <label class="form-label"><small>No. Roll Buyer</small></label>
                                <input type="text" class="form-control" id="no_roll_buyer" name="no_roll_buyer" value="" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6 col-md-2">
                            <div class="mb-1">
                                <label class="form-label"><small>ID Item</small></label>
                                <input type="text" class="form-control" id="id_item" name="id_item" value="" readonly>
                            </div>
                        </div>
                        <div class="col-6 col-md-10">
                            <div class="mb-1">
                                <label class="form-label"><small>Nama Barang</small></label>
                                <input type="text" class="form-control" id="nama_barang" name="nama_barang" value="" readonly>
                            </div>
                        </div>
                    </div>

                    {{-- <div class="row">
                        <div class="col-6 mt-3 offset-3 text-center">
                            <div class="btn btn-success btn-block mb-1 fw-bold" id="simpan_list_barcode"><i class="fa fa-save"></i> SIMPAN KE LIST BARCODE</div>
                        </div>
                    </div> --}}
                </div>
            </div>
        </div>

        <div class="card card-sb">
            <div class="card-header">
                <h5 class="card-title fw-bold">
                    List Barcode Fabric
                </h5>
            </div>
            <div class="card-body">
                <div class="row align-items-end">
                    <div class="col-md-12 table-responsive">
                        <table class="table table-bordered w-100 table" id="datatable">
                            <thead>
                                <tr>
                                    <th>Action</th>
                                    <th>Barcode</th>
                                    <th>Qty Out</th>
                                    <th>Unit</th>
                                    <th>Qty Konv</th>
                                    <th>Unit Konv</th>
                                    <th>No. Req</th>
                                    <th>No. BPPB</th>
                                    <th>Tujuan</th>
                                    <th>No. WS</th>
                                    <th>No. WS Act</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                    <input type="hidden" name="items" id="items">
                </div>
                <div class="col-12 col-md-6 offset-md-3 my-2 text-center">
                    <button type="submit" class="btn btn-success w-100 mb-1 fw-bold" id="btnSimpan">
                        <i class="fa fa-save"></i> SIMPAN
                    </button>
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
        let table_list_barcode;

        $(document).ready(async function () {
            initScan();
            
            $("#scan_item").focus();

            table_list_barcode = $('#datatable').DataTable({
                processing: true,
                serverSide: false,
                data: [],
                columns: [
                    { data: 'action' },
                    {
                        data: 'barcode',
                        render: function(data, type, row) {
                            return `<a href="#" class="barcode-click" data-row='${JSON.stringify(row)}' style="color:black; font-weight:bold;">${data}</a>`;
                        }
                    },
                    { data: 'qty_out' },
                    { data: 'unit' },
                    { data: 'qty_konv' },
                    { data: 'unit_konv' },
                    { data: 'no_req' },
                    { data: 'no_bppb' },
                    { data: 'tujuan' },
                    { data: 'no_ws' },
                    { data: 'no_ws_act' },
                    { data: 'whs_bppb_det_id', visible: false }
                ]
            });

            // Klik barcode, ambil data lengkap via AJAX
            $('#datatable tbody').on('click', '.barcode-click', function(e) {
                e.preventDefault();
                let barcode = $(this).text(); 

                document.getElementById("loading").classList.remove("d-none");

                $.ajax({
                    url: '{{ route('get-scanned-penerimaan-cutting') }}/' + barcode,
                    type: 'get',
                    dataType: 'json',
                    success: function(res) {
                        document.getElementById("loading").classList.add("d-none");

                        if (res.status === false) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Warning',
                                text: res.message,
                                confirmButtonText: 'Oke'
                            });
                            return;
                        }

                        $('#barcode').val(res.barcode);
                        $('#no_req').val(res.no_req);
                        $('#no_bppb').val(res.no_bppb);
                        $('#tanggal_bppb').val(res.tanggal_bppb);
                        $('#tujuan').val(res.tujuan);
                        $('#no_ws').val(res.no_ws);
                        $('#no_ws_act').val(res.no_ws_act);
                        $('#qty_out').val(res.qty_out);
                        $('#unit').val(res.unit);
                        
                        if (res.unit == 'YRD' || res.unit == 'YARD') {
                            $('#qty_konv').val((res.qty_out * 0.9144).toFixed(2));
                            $('#unit_konv').val('METER');
                        } else {
                            $('#qty_konv').val(res.qty_out);
                            $('#unit_konv').val(res.unit);
                        }

                        $('#style').val(res.style);
                        $('#warna').val(res.warna);
                        $('#no_lot').val(res.no_lot);
                        $('#no_roll').val(res.no_roll);
                        $('#no_roll_buyer').val(res.no_roll_buyer);
                        $('#id_item').val(res.id_item);
                        $('#nama_barang').val(res.nama_barang);
                        $('#whs_bppb_det_id').val(res.id);

                        highlightRowByBarcode(res.barcode);
                    },
                    error: function(jqXHR) {
                        document.getElementById("loading").classList.add("d-none");
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: jqXHR.responseText ? jqXHR.responseText : 'Roll tidak tersedia.',
                            confirmButtonText: 'Oke'
                        });
                    }
                });
            });

           $('#datatable tbody').on('click', '.hapus', function () {
                let row = table_list_barcode.row($(this).parents('tr'));

                Swal.fire({
                    title: 'Yakin hapus?',
                    text: 'Data akan dihapus dari list',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        row.remove().draw(false);
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: 'Data berhasil dihapus',
                            timer: 1200,
                            showConfirmButton: false
                        });
                    }
                });
            });

            $('#scan_item').on('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();

                    let id = $(this).val();
                    getScannedItem(id);

                    $(this).val('');
                }
            });
        });


        $(document).on('click', '#btnSimpan', function() {
            let data = table_list_barcode.rows().data().toArray();

            if (data.length === 0) {
                Swal.fire('Warning', 'Data masih kosong!', 'warning');
                return false;
            }

            data = data.map(row => {
                delete row.action;
                return row;
            });

            $('#items').val(JSON.stringify(data));
        });

        
        function highlightRowByBarcode(barcode) {
            
            $('#datatable tbody tr').css('background-color', '');

            table_list_barcode.rows().every(function () {
                let data = this.data();
                if (data.barcode === barcode) {
                    $(this.node()).css('background-color', '#b7d3ff'); 
                }
            });
        }
            
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

        // -Fetch Scanned Item Data-
        function fetchScan() {
            let idRoll = document.getElementById('scan_item').value;

            getScannedItem(idRoll);
        }

        // -Get Scanned Item Data-
        function getScannedItem(id) {
            console.log(id)
            document.getElementById("loading").classList.remove("d-none");

            // document.getElementById("id_item").value = "";
            // document.getElementById("detail_item").value = "";

            if (isNotNull(id)) {
                let isExist = false;

                table_list_barcode.rows().every(function () {
                    let data = this.data();

                    if (data.barcode == id) {
                        isExist = true;
                    }
                });

                if (isExist) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Duplicate!',
                        text: 'Barcode sudah ada di list!',
                        confirmButtonText: 'Oke'
                    });

                    $("#scan_item").val('');
                    document.getElementById("loading").classList.add("d-none");

                    return;
                }
                return $.ajax({
                    url: '{{ route('get-scanned-penerimaan-cutting') }}/' + id,
                    type: 'get',
                    dataType: 'json',
                    success: function(res) {
                        console.log(res);

                        if (res.status == false) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Warning',
                                text: res.message, 
                                confirmButtonText: 'Oke'
                            });

                            $("#scan_item").val('');
                            document.getElementById("loading").classList.add("d-none");
                            return;
                        }

                        if (typeof res === 'object' && res !== null) {
                            $("#whs_bppb_det_id").val(res.id);
                            $('#barcode').val(res.barcode);
                            $('#no_req').val(res.no_req);
                            $('#no_bppb').val(res.no_bppb);
                            $('#tanggal_bppb').val(res.tanggal_bppb);
                            $('#tujuan').val(res.tujuan);
                            $('#no_ws').val(res.no_ws);
                            $('#no_ws_act').val(res.no_ws_act);
                            $('#qty_out').val(res.qty_out);
                            $('#unit').val(res.unit);

                            if (res.unit == 'YRD' || res.unit == 'YARD') {
                                $('#qty_konv').val((res.qty_out * 0.9144).round(2));
                                $('#unit_konv').val('METER');
                            } else {
                                $('#qty_konv').val(res.qty_out);
                                $('#unit_konv').val(res.unit);
                            }

                            $('#style').val(res.style);
                            $('#warna').val(res.warna);
                            $('#no_lot').val(res.no_lot);
                            $('#no_roll').val(res.no_roll);
                            $('#no_roll_buyer').val(res.no_roll_buyer);
                            $('#id_item').val(res.id_item);
                            $('#nama_barang').val(res.nama_barang);

                            let dataBaru = {
                                action: `<button type="button" class="btn btn-danger btn-sm hapus">Hapus</button>`,
                                barcode: res.barcode,
                                qty_out: res.qty_out,
                                unit: res.unit,
                                qty_konv: $('#qty_konv').val(),
                                unit_konv: $('#unit_konv').val(),
                                no_req: res.no_req,
                                no_bppb: res.no_bppb,
                                tujuan: res.tujuan,
                                no_ws: res.no_ws,
                                no_ws_act: res.no_ws_act,
                                whs_bppb_det_id: res.id
                            };
                            table_list_barcode.row.add(dataBaru).draw();

                            highlightRowByBarcode(res.barcode);

                            $("#scan_item").val('');

                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: res ? res : 'Roll tidak tersedia.',
                                showCancelButton: false,
                                showConfirmButton: true,
                                confirmButtonText: 'Oke',
                            });

                            // calculatePipingRoll();
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

        // Prevent Form Submit When Pressing Enter
        document.getElementById("store-penerimaan-cutting").onkeypress = function(e) {
            var key = e.charCode || e.keyCode || 0;
            if (key == 13) {
                e.preventDefault();
            }
        }
        
    </script>
@endsection
