@extends('layouts.index')

@section('custom-link')
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@endsection

@section('content')
    <div class="d-flex justify-content-between mb-3">
        <h5 class="text-sb fw-bold"><i class="fa fa-plus"></i> New Piping Loading</h5>
        <a href="{{ route('piping-loading') }}" class="btn btn-sb-secondary btn-sm"><i class="fa fa-reply"></i> Kembali ke Loading Piping</a>
    </div>
    <form action="{{ route('store-piping-loading') }}" method="post" id="piping-loading-form" onsubmit="storePipingLoading(this, event)">
        <div class="card card-sb">
            <div class="card-header">
                <h5 class="card-title">Load Piping</h5>
            </div>
            <div class="card-body">
                <div class="row justify-content-center g-3 mb-5">
                    <div class="col-12">
                        <label class="form-label">Line</label>
                        <select class="form-select select2bs4" name="line_id" id="line_id">
                            <option value="">Pilih Line</option>
                            @foreach ($lines as $line)
                                <option value="{{ $line->line_id }}">{{ strtoupper(str_replace("_", " ", $line->username)) }}</option>
                            @endforeach
                        </select>
                        <input type="hidden" class="form-control" name="line_name" id="line_name">
                    </div>
                    <div class="col-12">
                        <div id="reader"></div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">ID Piping</label>
                        <div class="input-group">
                            <input type="text" class="form-control" name="kode_piping" id="kode_piping">
                            <button type="button" class="btn btn-success" id="get-btn" onclick="fetchScan()">Get</button>
                            <button type="button" class="btn btn-primary" id="scan-btn" onclick="initScan()">Scan</button>
                        </div>
                        <input type="hidden" name="piping_process_id" id="piping_process_id">
                    </div>
                    <div class="col-3">
                        <label class="form-label">Tanggal</label>
                        <input type="date" class="form-control" name="tanggal" id="tanggal" value="{{ date("Y-m-d") }}" readonly>
                    </div>
                    <div class="col-3">
                        <label class="form-label">Buyer</label>
                        <input type="text" class="form-control" name="buyer" id="buyer" readonly>
                    </div>
                    <div class="col-3">
                        <label class="form-label">Worksheet</label>
                        <input type="text" class="form-control" name="act_costing_ws" id="act_costing_ws" readonly>
                    </div>
                    <div class="col-3">
                        <label class="form-label">Style</label>
                        <input type="text" class="form-control" name="style" id="style" readonly>
                    </div>
                    <div class="col-3">
                        <label class="form-label">Color</label>
                        <input type="text" class="form-control" name="color" id="color" readonly>
                    </div>
                    <div class="col-3">
                        <label class="form-label">Group</label>
                        <input type="text" class="form-control" name="group" id="group" readonly>
                    </div>
                    <div class="col-3">
                        <label class="form-label">Lot</label>
                        <input type="text" class="form-control" name="lot" id="lot" readonly>
                    </div>
                    <div class="col-3">
                        <label class="form-label">Part</label>
                        <input type="text" class="form-control" name="part" id="part" readonly>
                    </div>
                    <div class="col-3">
                        <label class="form-label">Need</label>
                        <div class="input-group">
                            <input type="text" class="form-control" name="panjang" id="panjang" readonly>
                            <input type="text" class="form-control" name="unit" id="unit" readonly>
                        </div>
                    </div>
                    <div class="col-3">
                        <label class="form-label">Lebar Roll</label>
                        <div class="input-group">
                            <input type="text" class="form-control" name="lebar_roll_piping" id="lebar_roll_piping" readonly>
                            <input type="text" class="form-control" name="lebar_roll_piping_unit" id="lebar_roll_piping_unit" readonly>
                        </div>
                    </div>
                    <div class="col-3">
                        <label class="form-label">Panjang Roll</label>
                        <div class="input-group">
                            <input type="text" class="form-control" name="panjang_roll" id="panjang_roll" readonly>
                            <input type="text" class="form-control" name="panjang_roll_unit" id="panjang_roll_unit" readonly>
                        </div>
                    </div>
                    <div class="col-3">
                        <label class="form-label">Qty Awal</label>
                        <div class="input-group">
                            <input type="text" class="form-control" name="output_total_roll_awal" id="output_total_roll_awal" readonly>
                            <input type="text" class="form-control" name="output_total_roll_awal_unit" id="output_total_roll_awal_unit" value="ROLL" readonly>
                        </div>
                    </div>
                    <div class="col-3">
                        <label class="form-label">Qty Stok</label>
                        <div class="input-group">
                            <input type="text" class="form-control" name="output_total_roll" id="output_total_roll" readonly>
                            <input type="text" class="form-control" name="output_total_roll_unit" id="output_total_roll_unit" value="ROLL" readonly>
                        </div>
                    </div>
                    <div class="col-3">
                        <label class="form-label">Qty Loading</label>
                        <div class="input-group">
                            <input type="text" class="form-control" name="qty_roll" id="qty_roll" onkeyup="calculateOutput()">
                            <input type="text" class="form-control" name="qty_roll_unit" id="qty_roll_unit" value="ROLL" readonly>
                        </div>
                    </div>
                    <div class="col-3 d-none">
                        <label class="form-label">Estimasi Output /Roll</label>
                        <div class="input-group">
                            <input type="text" class="form-control" name="estimasi_output_roll" id="estimasi_output_roll" readonly>
                            <input type="text" class="form-control" name="estimasi_output_roll_unit" id="estimasi_output_roll_unit"readonly>
                        </div>
                    </div>
                    <div class="col-3">
                        <label class="form-label">Output</label>
                        <div class="input-group">
                            <input type="text" class="form-control" name="estimasi_output_total" id="estimasi_output_total" readonly>
                            <input type="text" class="form-control" name="estimasi_output_total_unit" id="estimasi_output_total_unit" value="PCS" readonly>
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-center">
                    <button class="btn btn-success fw-bold px-5">LOAD</button>
                </div>
            </div>
        </div>
    </form>
@endsection

@section('custom-script')
    <!-- Select2 -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <!-- Page specific script -->
    <script>
        // Initial Window On Load Event
        $(document).ready(async function () {
            disableFormSubmit("#piping-loading-form");

            let pipingLoadingForm = document.getElementById("piping-loading-form");

            resetForm(pipingLoadingForm);
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

        $('#line_id').on('change', function () {
            $('#line_name').val($("#line_id option:selected").text());
        });

        // -Scanner-
        var html5QrcodeScanner = new Html5Qrcode("reader");
        var scannerInitialized = false;

        // -Init Scanner-
        async function initScan() {
            if (document.getElementById("reader")) {
                if (html5QrcodeScanner == null || (html5QrcodeScanner && (html5QrcodeScanner.isScanning == false))) {
                    const qrCodeSuccessCallback = (decodedText, decodedResult) => {
                        // handle the scanned code as you like, for example:
                        console.log(`Code matched = ${decodedText}`, decodedResult);

                        // store to input text
                        let breakDecodedText = decodedText.split('-');

                        document.getElementById('kode_piping').value = breakDecodedText[0];

                        getScannedItem(breakDecodedText[0]);

                        clearScan();
                    };
                    const config = { fps: 10, qrbox: { width: 250, height: 250 } };

                    await html5QrcodeScanner.start({ facingMode: "environment" }, config, qrCodeSuccessCallback);
                }
            }
        }

        // -Clear Scanner-
        async function clearScan() {
            console.log(html5QrcodeScanner, html5QrcodeScanner.getState());
            if (html5QrcodeScanner && (html5QrcodeScanner.isScanning)) {
                await html5QrcodeScanner.stop();
                await html5QrcodeScanner.clear();
            }
        }

        // -Refresh Scanner-
        async function refreshScan() {
            await clearScan();
            await initScan();
        }

        // -Fetch Scanned Item Data-
        async function fetchScan() {
            let kodePiping = document.getElementById('kode_piping').value;

            await getScannedItem(kodePiping);
        }

        // -Get Scanned Item Data-
        async function getScannedItem(id) {
            document.getElementById("loading").classList.remove("d-none");

            document.getElementById("piping_process_id").value = "";
            document.getElementById("buyer").value = "";
            document.getElementById("act_costing_ws").value = "";
            document.getElementById("style").value = "";
            document.getElementById("color").value = "";
            document.getElementById("group").value = "";
            document.getElementById("lot").value = "";
            document.getElementById("part").value = "";
            document.getElementById("panjang").value = "";
            document.getElementById("unit").value = "";
            document.getElementById("lebar_roll_piping").value = "";
            document.getElementById("lebar_roll_piping_unit").value = "";
            document.getElementById("panjang_roll").value = "";
            document.getElementById("panjang_roll_unit").value = "";
            document.getElementById("output_total_roll_awal").value = "";
            document.getElementById("output_total_roll").value = "";
            document.getElementById("qty_roll").value = "";
            document.getElementById("estimasi_output_total").value = "";
            document.getElementById("estimasi_output_roll").value = "";
            document.getElementById("estimasi_output_roll_unit").value = "";

            if (isNotNull(id)) {
                return $.ajax({
                    url: '{{ route('get-piping-process') }}/' + id,
                    type: 'get',
                    dataType: 'json',
                    success: function(res) {
                        console.log(res);

                        if (typeof res === 'object' && res !== null) {
                            document.getElementById("piping_process_id").value = res.id;
                            document.getElementById("buyer").value = res.buyer;
                            document.getElementById("act_costing_ws").value = res.act_costing_ws;
                            document.getElementById("style").value = res.style;
                            document.getElementById("color").value = res.color;
                            document.getElementById("group").value = res.group;
                            document.getElementById("lot").value = res.lot;
                            document.getElementById("part").value = res.part;
                            document.getElementById("panjang").value = res.panjang;
                            document.getElementById("unit").value = res.unit;
                            document.getElementById("lebar_roll_piping").value = res.lebar_roll_piping;
                            document.getElementById("lebar_roll_piping_unit").value = res.lebar_roll_piping_unit;
                            document.getElementById("panjang_roll").value = res.panjang_roll;
                            document.getElementById("panjang_roll_unit").value = res.panjang_roll_unit;
                            document.getElementById("output_total_roll_awal").value = res.output_total_roll_awal;
                            document.getElementById("output_total_roll").value = res.output_total_roll;
                            document.getElementById("estimasi_output_roll").value = res.estimasi_output_roll;
                            document.getElementById("estimasi_output_roll_unit").value = res.estimasi_output_roll_unit;
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
        }

        async function calculateOutput() {
            let outputTotalRoll = document.getElementById("output_total_roll");
            let estimasiOutputRoll = document.getElementById("estimasi_output_roll");
            let qtyLoading = document.getElementById("qty_roll");
            let outputTotalLoading = document.getElementById("estimasi_output_total");

            let outputTotalRollValue = outputTotalRoll.value ? outputTotalRoll.value : 0;
            let estimasiOutputRollValue = estimasiOutputRoll.value ? estimasiOutputRoll.value : 0;
            let qtyLoadingValue = qtyLoading.value ? qtyLoading.value : 0;

            if (estimasiOutputRollValue && qtyLoadingValue) {
                if (Number(qtyLoadingValue) <= Number(outputTotalRollValue)) {
                    outputTotalLoading.value = Math.floor(estimasiOutputRollValue*qtyLoadingValue);
                } else {
                    qtyLoading.value = Number(outputTotalRollValue);

                    outputTotalLoading.value = Math.floor(estimasiOutputRollValue*outputTotalRollValue);

                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Qty Loading melebihi Qty Stok.',
                        showCancelButton: false,
                        showConfirmButton: true,
                        confirmButtonText: 'Oke',
                    });
                }
            } else {
                outputTotalLoading.value = 0;
            }
        }

        function storePipingLoading(e, event) {
            document.getElementById("loading").classList.remove("d-none");

            event.preventDefault();

            let form = new FormData(e);

            $.ajax({
                url: "{{ route("store-piping-loading") }}",
                type: "post",
                data: form,
                dataType: "json",
                processData: false,
                contentType: false,
                success: function (response) {
                    console.log(response);

                    if (response.status == 200) {
                        if (response.additional) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: 'Data Piping Loading '+response.additional.kode+' berhasil disimpan.',
                                showCancelButton: false,
                                showConfirmButton: true,
                                confirmButtonText: 'Oke',
                            });
                        }
                    }

                    resetForm(e);

                    document.getElementById("loading").classList.add("d-none");
                },
                error: function (jqXHR) {
                    iziToast.error({
                        title: 'Gagal',
                        message: 'Loading Piping gagal disimpan.',
                        position: 'topCenter'
                    });

                    console.error(jqXHR);

                    document.getElementById("loading").classList.add("d-none");
                }
            });
        }

        function resetForm(form) {
            form.reset();

            if (document.getElementsByClassName('select2')) {
                $(".select2").val('').trigger('change');
                $(".select2bs4").val('').trigger('change');
            }
        }
    </script>
@endsection
