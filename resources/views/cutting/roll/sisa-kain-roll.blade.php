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
            <h5 class="card-title fw-bold mb-0"><i class="fa-solid fa-toilet-paper-slash"></i> Bintex Sisa Kain</h5>
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
                        <button class="btn btn-success" class="input-group-text" id="getScannedItem" onclick="fetchScan()">Get</button>
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
                    <div class="input-group input-group-sm">
                        <input type="text" class="form-control fw-bold" id="sisa_kain" name="sisa_kain" readonly>
                        <span class="input-group-text" id="unit"></span>
                    </div>
                </div>
                <div class="col-md-3">
                    <button type="button" class="btn btn-primary btn-sm btn-block" onclick="printSisaKain()"><i class="fa fa-print"></i> Print Sticker</button>
                </div>
                <div class="col-md-12">
                    <table class="table table-bordered w-100 table" id="datatable">
                        <thead>
                            <tr>
                                <th>No.Form</th>
                                <th>No. Cut</th>
                                <th>ID Roll</th>
                                <th>Qty</th>
                                <th>Unit</th>
                                <th>Total Pemakaian</th>
                                <th>Short Roll</th>
                                <th>Sisa Kain</th>
                                <th>Status</th>
                                <th>Waktu</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-sb">
            <h5 class="card-title">
                Mass Print Sticker
            </h5>
        </div>
        <div class="card-body">
            <div>
                <label class="form-label fw-bold"><b>ID Rolls</b></label>
                <textarea name="id_rolls" id="id_rolls" cols="30" rows="10" class="form-control"></textarea>
            </div>
            <div class="d-flex justify-content-between gap-3 mt-3">
                <div class="form-text">Contoh : <br>&nbsp;&nbsp;&nbsp;<b> F11123</b><br>&nbsp;&nbsp;&nbsp;<b> F11124</b><br>&nbsp;&nbsp;&nbsp;<b> F11125</b></div>
                <button class="btn btn-primary" onclick="massPrintSisaKain()"><i class="fa fa-print"></i> Print</button>
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
                document.getElementById("buyer").value = "";
                document.getElementById("no_ws").value = "";
                document.getElementById("style").value = "";
                document.getElementById("color").value = "";
                document.getElementById("lot").value = "";
                document.getElementById("no_roll").value = "";
                document.getElementById("sisa_kain").value = "";
                document.getElementById("unit").innerText = "";

                if (isNotNull(id)) {
                    return $.ajax({
                        url: '{{ route('sisa_kain_scan_item') }}/' + id,
                        type: 'get',
                        dataType: 'json',
                        success: function(res) {
                            console.log("res", res);

                            datatableReload();

                            if (typeof res === 'object' && res !== null) {
                                document.getElementById("id_item").value = res.id_item;
                                document.getElementById("detail_item").value = res.detail_item;
                                document.getElementById("buyer").value = res.buyer;
                                document.getElementById("no_ws").value = res.no_ws;
                                document.getElementById("style").value = res.style;
                                document.getElementById("color").value = res.color;
                                document.getElementById("lot").value = res.lot;
                                document.getElementById("no_roll").value = res.no_roll;
                                document.getElementById("sisa_kain").value = res.qty;
                                document.getElementById("unit").innerText = res.unit;

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
                            datatableReload();

                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: 'Roll tidak tersedia.',
                                showCancelButton: false,
                                showConfirmButton: true,
                                confirmButtonText: 'Oke',
                            });

                            document.getElementById("loading").classList.add("d-none");
                        }
                    });
                }

                document.getElementById("loading").classList.add("d-none");

                datatableReload();

                return Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Item tidak ditemukan',
                    showCancelButton: false,
                    showConfirmButton: true,
                    confirmButtonText: 'Oke',
                });
            }

            function getOrderInfo() {

            }

        // Kain Roll
            var datatable = $("#datatable").DataTable({
                processing: true,
                serverSide: true,
                ordering: false,
                scrollX: "500px",
                pageLength: 50,
                ajax: {
                    url: '{{ route('sisa_kain_form') }}',
                    method: "GET",
                    data: function(d) {
                        d.id = $("#id_roll").val()
                    },
                },
                columns: [
                    {
                        data: "no_form_cut_input"
                    },
                    {
                        data: "no_cut"
                    },
                    {
                        data: "id_roll"
                    },
                    {
                        data: "qty"
                    },
                    {
                        data: "unit"
                    },
                    {
                        data: "total_pemakaian_roll"
                    },
                    {
                        data: "short_roll"
                    },
                    {
                        data: "sisa_kain"
                    },
                    {
                        data: "status"
                    },
                    {
                        data: "updated_at"
                    },
                ],
                columnDefs: [
                    {
                        targets: [0],
                        render: (data, type, row, meta) => {
                            switch (row.tipe) {
                                case "REGULAR" :
                                    return "<a href = '{{ route("detail-cutting") }}/"+row.id_form+"' target='_blank'>"+data+"</a>"
                                    break;
                                case "PIECE" :
                                    return "<a href = '{{ route("process-cutting-piece") }}/"+row.id_form+"' target='_blank'>"+data+"</a>";
                                    break;
                                default :
                                    return data;
                                    break;
                            }

                            return data;
                        }
                    },
                    {
                        targets: [8],
                        render: (data, type, row, meta) => {
                            switch (data) {
                                case "need extension" :
                                    return "butuh sambungan";
                                    break;
                                case "extension" :
                                    return "sambungan";
                                    break;
                                case "extension complete" :
                                    return "sambungan selesai";
                                    break;
                                case "complete" :
                                    return "selesai";
                                    break;
                                case "not complete" :
                                    return "belum selesai";
                                    break;
                                default :
                                    return data;
                                    break;
                            }
                        }
                    },
                    {
                        targets: [9],
                        render: (data, type, row, meta) => {
                            return formatDateTime(data);
                        }
                    },
                    {
                        targets: '_all',
                        className: 'text-nowrap'
                    }
                ]
            })

            function datatableReload() {
                $("#datatable").DataTable().ajax.reload();
            }

            async function printSisaKain() {
                document.getElementById("loading").classList.remove("d-none");

                $.ajax({
                    url: '{{ route('print_sisa_kain') }}/'+$("#id_roll").val(),
                    method: 'post',
                    xhrFields:
                    {
                        responseType: 'blob'
                    },
                    success: function (res) {
                        document.getElementById("loading").classList.add("d-none");

                        if (res) {
                            var blob = new Blob([res], {type: 'application/pdf'});
                            var link = document.createElement('a');
                            link.href = window.URL.createObjectURL(blob);
                            link.download = "Sisa Kain "+($("#id_roll").val())+".pdf";
                            link.click();
                        }
                    },
                    error: function (jqXHR) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: 'Roll belum dapat di print.',
                            showCancelButton: false,
                            showConfirmButton: true,
                            confirmButtonText: 'Oke',
                        });

                        document.getElementById("loading").classList.add("d-none");

                        console.log(jqXHR);
                    }
                });
            }

            async function massPrintSisaKain() {
                document.getElementById("loading").classList.remove("d-none");

                $.ajax({
                    url: '{{ route('mass_print_sisa_kain') }}',
                    method: 'post',
                    data: {
                        ids: $("#id_rolls").val()
                    },
                    xhrFields:
                    {
                        responseType: 'blob'
                    },
                    success: function (res) {
                        document.getElementById("loading").classList.add("d-none");

                        if (res) {
                            var blob = new Blob([res], {type: 'application/pdf'});
                            var link = document.createElement('a');
                            link.href = window.URL.createObjectURL(blob);
                            link.download = "Mass Sisa Kain "+($("#id_roll").val())+".pdf";
                            link.click();
                        }
                    },
                    error: function (jqXHR) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: 'Roll belum dapat di print.',
                            showCancelButton: false,
                            showConfirmButton: true,
                            confirmButtonText: 'Oke',
                        });

                        document.getElementById("loading").classList.add("d-none");

                        console.log(jqXHR);
                    }
                });
            }
    </script>
@endsection

