@extends('layouts.index')

@section('custom-link')
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@endsection

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="text-sb fw-bold">Alokasi Trolley</h5>
        <a href="#" class="btn btn-success btn-sm">
            <i class="fas fa-reply"></i> Kembali ke Stok Trolley
        </a>
    </div>
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
                    <select class="form-select select2bs4" name="trolley" id="trolley">
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
            <table class="table" id="trolley-stock-datatable">
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
            $('#trolley').val("").trigger("change");
            $('#kode_stocker').val("").trigger("change");

            await initTrolleyScan();
            await initStockerScan();
        });

        var step = "";

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

                                        getScannedTrolley(breakDecodedText[0]);

                                        clearTrolleyScan()();
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
