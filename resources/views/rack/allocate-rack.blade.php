@extends('layouts.index')

@section('custom-link')
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@endsection

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="text-sb fw-bold">Alokasi Rak</h5>
        <a href="{{ route('stock-rack') }}" class="btn btn-success btn-sm">
            <i class="fas fa-reply"></i> Kembali ke Stok Rak
        </a>
    </div>
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold">Scan Rak</h5>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body" style="display: block" id="scan-rack">
            <div id="rack-reader" onclick="clearRackScan()"></div>
            <div class="my-3">
                <label class="form-label">List Rak</label>
                <div class="input-group">
                    <select class="form-select select2bs4" name="rack" id="rack">
                        <option value="">Pilih Rak</option>
                        @foreach ($racks as $rack)
                            @foreach ($rack->rackDetails as $rackDetail)
                                <option value="{{ $rackDetail->id }}">{{ $rackDetail->nama_detail_rak }}</option>
                            @endforeach
                        @endforeach
                    </select>
                    <button class="btn btn-sm btn-outline-primary" type="button" onclick="refreshRackScan()">Scan</button>
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
        <div class="card-body" style="display: block" id="scan-stocker">
            <div id="stocker-reader" onclick="clearStockerScan()"></div>
            <div class="mb-3">
                <label class="form-label">Stocker</label>
                <div class="input-group">
                    <input type="text" class="form-control form-control-sm" name="kode_stocker" id="kode_stocker">
                    <button class="btn btn-sm btn-outline-success" type="button">Get</button>
                    <button class="btn btn-sm btn-outline-primary" type="button" onclick="initStockerScan()">Scan</button>
                </div>
            </div>
        </div>
    </div>
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
            <table class="table" id="rack-stock-datatable">
                <thead>
                    <tr>
                        <th>No. Stocker</th>
                        <th>No. WS</th>
                        <th>No. Cut</th>
                        <th>Style</th>
                        <th>Color</th>
                        <th>Part</th>
                        <th>Size</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
    <button class="btn btn-dark btn-block mb-3 fw-bold">
        <i class="fas fa-undo-alt"></i> RESET
    </button>
@endsection

@section('custom-script')
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
            $('#rack').val("").trigger("change");
            $('#kode_stocker').val("").trigger("change");

            await initRackScan();
            await initStockerScan();
        });

        var step = "";

        // Scan QR Module :
            // Variable List :
                var rackScanner = new Html5Qrcode("rack-reader");
                var rackScannerInitialized = false;

                var stockerScanner = new Html5Qrcode("stocker-reader");
                var stockerScannerInitialized = false;

            // Function List :
                // -Initialize Rack Scanner-
                    async function initRackScan() {
                        if (document.getElementById("rack-reader")) {
                            if (rackScannerInitialized == false && stockerScannerInitialized == false) {
                                if (rackScanner == null || (rackScanner && (rackScanner.getState() && rackScanner.getState() != 2))) {
                                    const rackScanSuccessCallback = (decodedText, decodedResult) => {
                                            // handle the scanned code as you like, for example:
                                        console.log(`Code matched = ${decodedText}`, decodedResult);

                                        // store to input text
                                        let breakDecodedText = decodedText.split('-');

                                        $('#rack').val(breakDecodedText[0]).trigger('change');

                                        getScannedRack(breakDecodedText[0]);

                                        clearRackScan();
                                    };
                                    const rackScanConfig = { fps: 10, qrbox: { width: 250, height: 250 } };

                                    // If you want to prefer front camera
                                    await rackScanner.start({ facingMode: "environment" }, rackScanConfig, rackScanSuccessCallback);

                                    rackScannerInitialized = true;
                                }
                            }
                        }
                    }

                    async function clearRackScan() {
                        if (rackScannerInitialized) {
                            if (rackScanner && (rackScanner.getState() && rackScanner.getState() != 1)) {
                                await rackScanner.stop();
                                await rackScanner.clear();
                            }

                            rackScannerInitialized = false;
                        }
                    }

                    async function refreshRackScan() {
                        await clearRackScan();
                        await initRackScan();
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

                                        clearStockerScan()();
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
    </script>
@endsection
