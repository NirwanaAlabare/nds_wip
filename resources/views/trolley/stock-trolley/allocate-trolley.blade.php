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
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="text-sb fw-bold">Alokasi Trolley</h5>
        <a href="{{ route('stock-trolley') }}" class="btn btn-success btn-sm">
            <i class="fas fa-reply"></i> Kembali ke Stok Trolley
        </a>
    </div>
    <form action="{{ route('store-allocate-trolley') }}" method="post" onsubmit="submitForm(this, event)" id="stocker-form">
        <div class="card card-sb">
            <div class="card-header">
                <h5 class="card-title fw-bold">Scan Trolley</h5>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body" style="display: block" id="scan-trolley">
                <div id="trolley-reader" onclick="clearTrolleyScan()"></div>
                <div class="my-3">
                    <label class="form-label">List Trolley</label>
                    <div class="input-group">
                        <select class="form-select select2bs4" name="trolley_id" id="trolley_id" onchange="trolleyStockDatatableReload();">
                            <option value="">Pilih Trolley</option>
                            @foreach ($trolleys as $trolley)
                                <option value="{{ $trolley->id }}">{{ $trolley->nama_trolley }}</option>
                            @endforeach
                        </select>
                        <button class="btn btn-sm btn-outline-primary" type="button" onclick="refreshTrolleyScan()">Scan</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="card card-primary">
            <div class="card-header">
                <h5 class="card-title fw-bold">Scan Stocker</h5>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body" id="scan-stocker">
                <div id="stocker-reader" onclick="clearStockerScan()"></div>
                <div class="mb-3">
                    <label class="form-label">Stocker</label>
                    <div class="input-group">
                        <input type="text" class="form-control form-control-sm" name="kode_stocker" id="kode_stocker">
                        <button class="btn btn-sm btn-outline-success" type="button" onclick="getStockerDataInput()">Get</button>
                        <button class="btn btn-sm btn-outline-primary" type="button" onclick="initStockerScan()">Scan</button>
                        <input type="hidden" name="stocker_id" id="stocker_id">
                    </div>
                </div>
                <div class="row">
                    <div class="col col-md-3">
                        <div class="mb-3">
                            <label class="form-label">No. WS</label>
                            <input type="text" class="form-control form-control-sm" name="stocker_act_costing_ws" id="stocker_act_costing_ws" readonly>
                        </div>
                    </div>
                    <div class="col col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Buyer</label>
                            <input type="text" class="form-control form-control-sm" name="stocker_buyer" id="stocker_buyer" readonly>
                        </div>
                    </div>
                    <div class="col col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Style</label>
                            <input type="text" class="form-control form-control-sm" name="stocker_style" id="stocker_style" readonly>
                        </div>
                    </div>
                    <div class="col col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Color</label>
                            <input type="text" class="form-control form-control-sm" name="stocker_color" id="stocker_color" readonly>
                        </div>
                    </div>
                    <div class="col col-md-3">
                        <div class="mb-3">
                            <label class="form-label">No. Stocker</label>
                            <input type="text" class="form-control form-control-sm" name="stocker_id_qr_stocker" id="stocker_id_qr_stocker" readonly>
                        </div>
                    </div>
                    <div class="col col-md-3">
                        <div class="mb-3">
                            <label class="form-label">No. Cut</label>
                            <input type="text" class="form-control form-control-sm" name="stocker_no_cut" id="stocker_no_cut" readonly>
                        </div>
                    </div>
                    <div class="col col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Size</label>
                            <input type="text" class="form-control form-control-sm" name="stocker_size" id="stocker_size" readonly>
                        </div>
                    </div>
                    <div class="col col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Qty</label>
                            <input type="text" class="form-control form-control-sm" name="stocker_qty_ply" id="stocker_qty_ply" readonly>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-sm btn-primary w-100 mt-3 fw-bold">
                    <i class="fa fa-save"></i> SIMPAN
                </button>
            </div>
        </div>
    </form>
    <div class="card card-info" id="stock-trolley">
        <div class="card-header">
            <h5 class="card-title fw-bold">Stock Trolley</h5>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body" style="display: block">
            <table class="table table-bordered" id="trolley-stock-datatable">
                <thead>
                    <tr>
                        <th>Act</th>
                        <th>No. Stocker</th>
                        <th>No. WS</th>
                        <th>No. Cut</th>
                        <th>Style</th>
                        <th>Color</th>
                        <th>Part</th>
                        <th>Size</th>
                        <th>Qty</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
    <button class="btn btn-success btn-block btn-sm mb-3 fw-bold">
        <i class="fas fa-check"></i> SELESAI
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
        // Select2 Autofocus
        $(document).on('select2:open', () => {
            document.querySelector('.select2-search__field').focus();
        });

        // Initialize Select2 Elements
        $('.select2').select2()

        // Initialize Select2BS4 Elements
        $('.select2bs4').select2({
            theme: 'bootstrap4',
        });

        $(document).ready(async () => {
            $('#trolley_id').val("").trigger("change");
            $('#kode_stocker').val("").trigger("change");

            await initTrolleyScan();
            await initStockerScan();

            clearStockerData();
        });

        // Scan QR Module :
            // Variable List :
                var trolleyScanner = new Html5Qrcode("trolley-reader");
                var trolleyScannerInitialized = false;

                var stockerScanner = new Html5Qrcode("stocker-reader");
                var stockerScannerInitialized = false;

            // Function List :
                // -Initialize Trolley Scanner-
                    async function initTrolleyScan() {
                        if (document.getElementById("trolley-reader")) {
                            if (trolleyScannerInitialized == false && stockerScannerInitialized == false) {
                                if (trolleyScanner == null || (trolleyScanner && (trolleyScanner.getState() && trolleyScanner.getState() != 2))) {
                                    const trolleyScanSuccessCallback = (decodedText, decodedResult) => {
                                            // handle the scanned code as you like, for example:
                                        console.log(`Code matched = ${decodedText}`, decodedResult);

                                        // store to input text
                                        let breakDecodedText = decodedText.split('-');

                                        $('#trolley').val(breakDecodedText[0]).trigger('change');

                                        clearTrolleyScan();
                                    };
                                    const trolleyScanConfig = { fps: 10, qrbox: { width: 250, height: 250 } };

                                    // If you want to prefer front camera
                                    await trolleyScanner.start({ facingMode: "environment" }, trolleyScanConfig, trolleyScanSuccessCallback);

                                    trolleyScannerInitialized = true;
                                }
                            }
                        }
                    }

                    async function clearTrolleyScan() {
                        if (trolleyScannerInitialized) {
                            if (trolleyScanner && (trolleyScanner.getState() && trolleyScanner.getState() != 1)) {
                                await trolleyScanner.stop();
                                await trolleyScanner.clear();
                            }

                            trolleyScannerInitialized = false;
                        }
                    }

                    async function refreshTrolleyScan() {
                        await clearTrolleyScan();
                        await initTrolleyScan();
                    }

                // -Initialize Stocker Scanner-
                    async function initStockerScan() {
                        if (document.getElementById("stocker-reader")) {
                            if (stockerScannerInitialized == false && trolleyScannerInitialized == false) {
                                if (stockerScanner == null || (stockerScanner && (stockerScanner.getState() && stockerScanner.getState() != 2))) {
                                    const stockerScanSuccessCallback = (decodedText, decodedResult) => {
                                            // handle the scanned code as you like, for example:
                                        console.log(`Code matched = ${decodedText}`, decodedResult);

                                        // store to input text
                                        let breakDecodedText = decodedText.split('-');

                                        $('#kode_stocker').val(breakDecodedText[0]).trigger('change');

                                        storeScannedStocker(breakDecodedText[0]);

                                        clearStockerScan();
                                    };
                                    const stockerScanConfig = { fps: 10, qrbox: { width: 250, height: 250 } };

                                    // If you want to prefer front camera
                                    await stockerScanner.start({ facingMode: "environment" }, stockerScanConfig, stockerScanSuccessCallback);

                                    stockerScannerInitialized = true;
                                }
                            }
                        }
                    }

                    async function clearStockerScan() {
                        if (stockerScannerInitialized) {
                            if (stockerScanner && (stockerScanner.getState() && stockerScanner.getState() != 1)) {
                                await stockerScanner.stop();
                                await stockerScanner.clear();
                            }

                            stockerScannerInitialized = false;
                        }
                    }

                    async function refreshStockerScan() {
                        await clearStockerScan();
                        await initStockerScan();
                    }

        // Datatable
        let trolleyStockDatatable = $("#trolley-stock-datatable").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('allocate-trolley') }}',
                data: function (d) {
                    d.trolley_id = $("#trolley_id").val();
                }
            },
            columns: [
                {
                    data: 'id'
                },
                {
                    data: 'id_qr_stocker',
                },
                {
                    data: 'act_costing_ws',
                },
                {
                    data: 'no_cut',
                },
                {
                    data: 'style',
                },
                {
                    data: 'color',
                },
                {
                    data: 'nama_part',
                },
                {
                    data: 'size',
                },
                {
                    data: 'qty',
                },
            ],
            columnDefs: [
                {
                    targets: [0],
                    className: "align-middle",
                    render: (data, type, row, meta) => {
                        return `
                            <div class='d-flex gap-1 justify-content-center'>
                                <a class='btn btn-danger btn-sm' data='`+JSON.stringify(row)+`' data-url='{{ route("destroy-trolley-stock") }}/`+row['id']+`' onclick='deleteData(this);'>
                                    <i class='fa fa-trash'></i>
                                </a>
                            </div>
                        `;
                    }
                },
            ]
        });

        function trolleyStockDatatableReload() {
            trolleyStockDatatable.ajax.reload();
        }

        function clearAll() {
            $('#trolley_id').val("").trigger("change");

            clearStockerData();
        }

        function getStockerDataInput() {
            let id = document.getElementById('kode_stocker').value;

            console.log(id)

            getStockerData(id);
        }

        function getStockerData(id) {
            if (checkIfNull(id)) {
                return $.ajax({
                    url: '{{ route('get-stocker-data-trolley-stock') }}/' + id,
                    type: 'get',
                    dataType: 'json',
                    success: function(res) {
                        clearStockerData();

                        if (res && res.status == 200) {
                            setStockerData(res.data);
                        } else {
                            Swal.fire({
                                icon: 'warning',
                                title: res.message,
                                showCancelButton: false,
                                showConfirmButton: true,
                                confirmButtonText: 'Oke',
                            });
                        }
                    },
                    error: function(jqXHR) {
                        console.log(jqXHR);
                    }
                });
            }
        }

        function setStockerData(data) {
            if (data) {
                for (let key in data) {
                    console.log(document.getElementById('stocker_'+key));
                    if (document.getElementById('stocker_'+key)) {
                        document.getElementById('stocker_'+key).value = data[key];
                        document.getElementById('stocker_'+key).setAttribute('value', data[key]);

                        if (document.getElementById('stocker_'+key).classList.contains('select2bs4') || document.getElementById('stocker_'+key).classList.contains('select2') || document.getElementById('stocker_'+key).classList.contains('select2bs4stat')) {
                            $('#stocker_'+key).val(data[key]).trigger('change.select2');
                        }
                    }
                }
            }
        }

        function clearStockerData() {
            document.getElementById("stocker_act_costing_ws").readonly = false;
            document.getElementById("stocker_buyer").readonly = false;
            document.getElementById("stocker_style").readonly = false;
            document.getElementById("stocker_color").readonly = false;
            document.getElementById("stocker_id_qr_stocker").readonly = false;
            document.getElementById("stocker_no_cut").readonly = false;
            document.getElementById("stocker_size").readonly = false;
            document.getElementById("stocker_qty_ply").readonly = false;

            document.getElementById("stocker_act_costing_ws").value = "";
            document.getElementById("stocker_buyer").value = "";
            document.getElementById("stocker_style").value = "";
            document.getElementById("stocker_color").value = "";
            document.getElementById("stocker_id_qr_stocker").value = "";
            document.getElementById("stocker_no_cut").value = "";
            document.getElementById("stocker_size").value = "";
            document.getElementById("stocker_qty_ply").value = "";

            document.getElementById("stocker_act_costing_ws").readonly = true;
            document.getElementById("stocker_buyer").readonly = true;
            document.getElementById("stocker_style").readonly = true;
            document.getElementById("stocker_color").readonly = true;
            document.getElementById("stocker_id_qr_stocker").readonly = true;
            document.getElementById("stocker_no_cut").readonly = true;
            document.getElementById("stocker_size").readonly = true;
            document.getElementById("stocker_qty_ply").readonly = true;
        }
    </script>
@endsection
