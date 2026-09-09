@extends('layouts.index')

@section('custom-link')
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@endsection

@section('content')
    @php
        $currentProcess = $currentFormCutScrap ? intval($currentFormCutScrap->process) : 0;
        $savedDetails = $currentFormCutScrap ? $currentFormCutScrap->formCutScrapDetails->where('status', 'complete') : collect();
        $savedParts = $currentFormCutScrap ? $currentFormCutScrap->formCutScrapFormParts : collect();
        // Roll yang barangnya sudah diinput tapi size-nya belum tersimpan
        $activeDetail = $currentFormCutScrap ? $currentFormCutScrap->formCutScrapDetails->firstWhere('status', 'incomplete') : null;
    @endphp

    <div class="d-flex justify-content-between mb-3">
        <div class="d-flex flex-column">
            <h5 class="fw-bold text-sb mb-0">
                Create Cutting Scrap
            </h5>
            <span class="text-sb-secondary fw-bold" id="no-form-badge">{{ $currentFormCutScrap ? $currentFormCutScrap->no_form : '-' }}</span>
        </div>
        <div class="d-flex align-items-center gap-1">
            <a href="{{ route('create-new-cutting-scrap') }}" class="btn btn-success btn-sm px-1 py-1"><i class="fas fa-plus"></i> Buat Form Baru</a>
            <a href="{{ route('cutting-scrap') }}" class="btn btn-primary btn-sm px-1 py-1"><i class="fas fa-reply"></i> Kembali ke Form Cut Scrap</a>
        </div>
    </div>

    <input type="hidden" id="id" name="id" value="{{ $currentFormCutScrap ? $currentFormCutScrap->id : null }}">
    <input type="hidden" id="no_form" name="no_form" value="{{ $currentFormCutScrap ? $currentFormCutScrap->no_form : null }}">
    <input type="hidden" id="process" value="{{ $currentProcess }}">
    <input type="hidden" id="id_detail" value="{{ $activeDetail ? $activeDetail->id : null }}">

    {{-- ================= Process 1 : header form ================= --}}
    <form action="{{ route('store-cutting-scrap') }}" method="POST" id="process-one-form" class="mb-3" onsubmit="processOne(this, event)">
        <div class="card card-sb mb-3">
            <div class="card-header">
                <h5 class="card-title fw-bold">1. Header Data</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-6 col-md-4">
                        <label class="form-label fw-bold text-sm">No. WS</label>
                        <select class="form-select select2bs4" id="act_costing_id" name="act_costing_id" style="width: 100%;" required>
                            @if ($currentFormCutScrap && $currentFormCutScrap->act_costing_id)
                                <option value="{{ $currentFormCutScrap->act_costing_id }}" selected>{{ $currentFormCutScrap->act_costing_ws }}</option>
                            @else
                                <option value="">Pilih WS</option>
                                @foreach ($orders as $order)
                                    <option value="{{ $order->id }}">{{ $order->kpno }}</option>
                                @endforeach
                            @endif
                        </select>
                        <input type="hidden" id="act_costing_ws" name="act_costing_ws" value="{{ $currentFormCutScrap ? $currentFormCutScrap->act_costing_ws : null }}">
                    </div>
                    <div class="col-6 col-md-4">
                        <label class="form-label fw-bold text-sm">Style</label>
                        <input type="text" class="form-control " id="style" name="style" value="{{ $currentFormCutScrap ? $currentFormCutScrap->style : null }}" readonly>
                    </div>
                    <div class="col-6 col-md-4">
                        <label class="form-label fw-bold text-sm">Color</label>
                        <select class="form-select select2bs4" id="color" name="color" style="width: 100%;" required>
                            @if ($currentFormCutScrap && $currentFormCutScrap->color)
                                <option value="{{ $currentFormCutScrap->color }}" selected>{{ $currentFormCutScrap->color }}</option>
                            @else
                                <option value="">Pilih Color</option>
                            @endif
                        </select>
                    </div>
                    <div class="col-6 col-md-4">
                        <label class="form-label fw-bold text-sm">Panel</label>
                        <select class="form-select select2bs4" id="panel" name="panel" style="width: 100%;" required>
                            @if ($currentFormCutScrap && $currentFormCutScrap->panel)
                                <option value="{{ $currentFormCutScrap->panel }}" selected>{{ $currentFormCutScrap->panel }}</option>
                            @else
                                <option value="">Pilih Panel</option>
                            @endif
                        </select>
                    </div>
                    <div class="col-6 col-md-4">
                        <label class="form-label fw-bold text-sm">Part</label>
                        <select class="form-select select2bs4" id="parts" name="parts[]" style="width: 100%;" multiple="multiple" required>
                            @foreach ($savedParts as $savedPart)
                                <option value="{{ $savedPart->part_detail_id }}" selected>{{ $savedPart->nama_part }}{{ $savedPart->part_status ? ' ('.$savedPart->part_status.')' : '' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-4">
                        <label class="form-label fw-bold">Tanggal</label>
                        <input type="date" class="form-control " id="tanggal" name="tanggal" value="{{ $currentFormCutScrap && $currentFormCutScrap->tanggal ? $currentFormCutScrap->tanggal : date('Y-m-d') }}" required readonly>
                    </div>
                    <div class="col-12 col-md-12">
                        <label class="form-label fw-bold text-sm">Keterangan</label>
                        <textarea class="form-control " id="ket" name="ket" rows="2">{{ $currentFormCutScrap ? $currentFormCutScrap->ket : null }}</textarea>
                    </div>
                </div>
            </div>
            <div class="card-footer border-1">
                <button type="submit" class="btn btn-primary float-end btn-sm fw-bold" id="process-one-button"><i class="fa fa-arrow-right"></i> Lanjut ke Operator</button>
            </div>
        </div>
    </form>

    {{-- ================= Process 2 : Scan Operator ================= --}}
    <form action="{{ route('store-cutting-scrap') }}" method="POST" id="process-two-form" class="mb-3 {{ $currentProcess >= 1 ? '' : 'd-none' }}" onsubmit="processTwo(this, event)">
        <div class="card card-sb mb-3">
            <div class="card-header">
                <h5 class="card-title fw-bold">2. Verifikasi Operator Scrap</h5>
            </div>
            <div class="card-body">
                <!-- Form Row 1 Baris dengan Label -->
                <div class="row g-3">
                    <!-- Kolom 1: Input NIK + Button Fetch -->
                    <div class="col-md-4">
                        <label for="inputNik" class="form-label fw-bold">ID Operator</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="employee_id" name="employee_id" placeholder="Masukkan Enroll ID Karyawan..." value="{{ $currentFormCutScrap ? $currentFormCutScrap->employee_id : null }}">
                            <button class="btn btn-success" type="button" id="btnFetch" onclick="fetchScanOperator()">Get</button>
                            <button class="btn btn-primary" type="button" id="btnScan" onclick="refreshScanOperator()">Scan</button>
                        </div>
                    </div>

                    <!-- Kolom 2: Hasil NIK -->
                    <div class="col-md-3">
                        <label for="resultNik" class="form-label fw-bold text-muted">Hasil NIK</label>
                        <input type="text" class="form-control bg-white" id="employee_nik" name="employee_nik" placeholder="-" value="{{ $currentFormCutScrap ? $currentFormCutScrap->employee_nik : null }}" readonly>
                    </div>

                    <!-- Kolom 3: Nama Karyawan -->
                    <div class="col-md-5">
                        <label for="resultName" class="form-label fw-bold text-muted">Nama Karyawan</label>
                        <input type="text" class="form-control bg-white text-success fw-bold" id="employee_name" name="employee_name" placeholder="-" value="{{ $currentFormCutScrap ? $currentFormCutScrap->employee_name : null }}" readonly>
                    </div>

                    <!-- Kamera scan, terisi saat tombol Scan ditekan -->
                    <div class="col-12">
                        <div id="reader-operator" style="max-width: 320px;"></div>
                    </div>
                </div>
            </div>
            <div class="card-footer border-1">
                <button type="submit" class="btn btn-primary btn-sm float-end fw-bold "><i class="fa fa-arrow-right"></i> Mulai Proses Scrap</button>
            </div>
        </div>
    </form>

    <!-- CARD 3: INFORMASI BARANG SCRAP -->
    <form action="{{ route('store-cutting-scrap') }}" method="POST" id="process-three-form" class="mb-3 {{ $currentProcess >= 2 ? '' : 'd-none' }}" onsubmit="processThree(this, event)">
        <div class="card card-sb mb-4 shadow-sm">
            <!-- Card Header -->
            <div class="card-header py-3">
                <h6 class="mb-0 fw-bold">
                    3. INFORMASI BARANG SCRAP
                    <i class="bi bi-check-circle text-success ms-1"></i>
                </h6>
            </div>

            <!-- Card Body -->
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label">ITEM FABRIC <span class="text-danger">*</span></label>
                        <select class="form-select form-select-sm select2bs4" id="id_item" name="id_item" required>
                            @if ($activeDetail && $activeDetail->id_item)
                                <option value="{{ $activeDetail->id_item }}" selected>{{ $activeDetail->itemdesc }}</option>
                            @else
                                <option value="">Pilih Item</option>
                            @endif
                        </select>
                        <input type="hidden" id="itemdesc" name="itemdesc" value="{{ $activeDetail ? $activeDetail->itemdesc : null }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">SATUAN / UNIT <span class="text-danger">*</span></label>
                        <select class="form-select form-select-sm" id="unit" name="unit" required>
                            @foreach (['KGM', 'METER', 'YARD'] as $unitOption)
                                <option value="{{ $unitOption }}" {{ $activeDetail && $activeDetail->unit == $unitOption ? 'selected' : '' }}>{{ $unitOption }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">QTY ROLL</label>
                        <input type="number" step="0.01" min="0" class="form-control form-control-sm" id="qty_roll" name="qty_roll" value="{{ $activeDetail ? $activeDetail->qty_roll : 1 }}" required>
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">LOT</label>
                        <input type="text" class="form-control form-control-sm" id="lot" name="lot" value="{{ $activeDetail ? $activeDetail->lot : 1 }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">GROUP</label>
                        <input type="text" class="form-control form-control-sm" id="group_roll" name="group_roll" value="{{ $activeDetail ? $activeDetail->group_roll : 1 }}">
                    </div>
                </div>
            </div>
            <div class="card-footer border-1 text-end py-3">
                <button class="btn btn-primary btn-sm fw-bold" type="submit"><i class="fa fa-arrow-right"></i> Lanjut Input Size</button>
            </div>
        </div>
    </form>

    <!-- CARD 4: INPUT QUANTITY SCRAP PER SIZE -->
    <form action="{{ route('store-cutting-scrap') }}" method="POST" id="process-four-form" class="mb-3 {{ $currentProcess >= 3 ? '' : 'd-none' }}" onsubmit="processFour(this, event)">
        <div class="card card-sb mb-4 shadow-sm">
            <!-- Card Header -->
            <div class="card-header py-3">
                <h6 class="mb-1 fw-bold">4. INPUT QUANTITY SCRAP PER SIZE</h6>
                <small class="text-light fw-bold" id="active-roll-badge">
                    @if ($activeDetail)
                        Barang Selected: {{ $activeDetail->itemdesc }} ({{ $activeDetail->unit }}) | Lot: {{ $activeDetail->lot ?: '-' }} | Group: {{ $activeDetail->group_roll ?: '-' }}
                    @endif
                </small>
            </div>

            <!-- Card Body -->
            <div class="card-body">
                <!-- Info Box Part Terpilih -->
                <div class="alert bg-sb-secondary py-2 px-3 mb-3" role="alert">
                    <div class="d-flex align-items-center fw-bold" style="font-size: 0.85rem;">
                        <i class="fa fa-layers me-2 fs-5"></i> PART TERPILIH DI HEADER (<span id="selected-part-count">{{ $savedParts->count() }}</span>):
                    </div>
                    <div class="mt-2" id="selected-part-badge">
                        @foreach ($savedParts as $savedPart)
                            <span class="badge bg-sb me-1 px-3 py-2">{{ $savedPart->nama_part ?: $savedPart->part_detail_id }}</span>
                        @endforeach
                    </div>
                </div>

                <p class="text-muted fst-italic mb-3" style="font-size: 0.75rem;">
                    *Input Qty Size di bawah ini akan otomatis berlaku sama untuk semua part terpilih di atas.
                </p>

                <hr class="text-muted">

                <!-- Size Inputs (Menggunakan row & col bawaan Bootstrap) -->
                <div class="row g-2" id="size-container">
                    <div class="col-12"><small class="text-muted">Pilih color terlebih dahulu untuk memuat size.</small></div>
                </div>
            </div>

            <!-- Card Footer -->
            <div class="card-footer border-1 text-end py-3">
                <button class="btn btn-sm text-white fw-bold" style="background-color: #fd7e14;" type="submit">
                    <i class="bi bi-plus-lg"></i> Simpan &amp; Tambah Roll Lain
                </button>
            </div>
        </div>
    </form>

    {{-- ================= Roll yang sudah tersimpan ================= --}}
    <div class="card card-sb mb-3">
        <div class="card-header">
            <h5 class="card-title fw-bold">Daftar Roll Tersimpan</h5>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-sm table-bordered mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>ID Item</th>
                        <th>Item</th>
                        <th>Qty Roll</th>
                        <th>Lot</th>
                        <th>Group</th>
                        <th>Size &amp; Qty</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="roll-table-body">
                    @foreach ($savedDetails as $savedDetail)
                        <tr id="roll-row-{{ $savedDetail->id }}">
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $savedDetail->id_item ?: '-' }}</td>
                            <td>{{ $savedDetail->itemdesc ?: '-' }}</td>
                            <td>{{ $savedDetail->qty_roll }} {{ $savedDetail->unit }}</td>
                            <td>{{ $savedDetail->lot ?: '-' }}</td>
                            <td>{{ $savedDetail->group_roll ?: '-' }}</td>
                            <td>
                                @php
                                    // Size setiap part isinya sama, jadi cukup diambil dari part pertama
                                    $firstPart = $savedDetail->formCutScrapParts->first();
                                @endphp
                                @forelse ($firstPart ? $firstPart->formCutScrapSizes : [] as $savedSize)
                                    <span class="badge bg-light text-dark border">{{ $savedSize->size }} : {{ $savedSize->qty }}</span>
                                @empty
                                    -
                                @endforelse
                            </td>
                            <td>
                                <button type="button" class="btn btn-danger btn-sm" onclick="deleteRoll({{ $savedDetail->id }})"><i class="fa fa-trash"></i></button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- BUTTON FINISH -->
    <button type="button" class="btn btn-success w-100 py-1 mb-3 fw-bold fs-6" onclick="finishProcess()">
        <i class="bi bi-floppy"></i> FINISH & SIMPAN SETELAH SEMUA ROLL SELESAI
    </button>
@endsection

@section('custom-script')
    <!-- Select2 -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        let sizeRows = [];      // diisi setelah color dipilih
        let partOptions = "";   // diisi setelah panel dipilih
        let rollCount = {{ $savedDetails->count() }};

        $('.select2bs4').select2({
            theme: 'bootstrap4',
            containerCssClass: ' rounded'
        });

        // Cegah submit saat menekan enter
        ['process-one-form', 'process-two-form', 'process-three-form', 'process-four-form'].forEach((id) => {
            let form = document.getElementById(id);

            if (!form) {
                return;
            }

            form.onkeypress = function(e) {
                if ((e.charCode || e.keyCode || 0) == 13) {
                    e.preventDefault();
                }
            }
        });

        $('#act_costing_id').on('change', function() {
            $('#act_costing_ws').val($('#act_costing_id option:selected').text());

            if (this.value) {
                updateOrderInfo();
                updateColorList();
            }
        });

        $('#color').on('change', function() {
            if (this.value) {
                updatePanelList();
                updateSizeList();
            }
        });

        $('#panel').on('change', function() {
            if (this.value) {
                updatePartList();
                updateItemList();
            }
        });

        $('#id_item').on('change', function() {
            $('#itemdesc').val(this.value ? $('#id_item option:selected').text() : '');
        });

        function fillSelect(id, rows, valueKey, textKey) {
            let select = document.getElementById(id);

            select.innerHTML = "";

            let placeholder = document.createElement("option");
            placeholder.value = "";
            placeholder.innerHTML = "Pilih";
            select.appendChild(placeholder);

            (rows || []).forEach((row) => {
                let option = document.createElement("option");
                option.value = row[valueKey];
                option.innerHTML = row[textKey];
                select.appendChild(option);
            });

            $('#' + id).val(null).trigger('change.select2');
        }

        function updateOrderInfo() {
            showLoading();
            $.ajax({
                url: '{{ route('get-general-order') }}',
                type: 'get',
                dataType: 'json',
                data: { act_costing_id: $('#act_costing_id').val() },
                success: function(res) {
                    if (res) {
                        $('#style').val(res.styleno);
                        $('#act_costing_ws').val(res.kpno);
                        hideLoading();
                    }
                }
            });
        }

        function updateColorList() {
            showLoading();
            $.ajax({
                url: '{{ route('get-colors') }}',
                type: 'get',
                dataType: 'json',
                data: { act_costing_id: $('#act_costing_id').val() },
                success: function(res) {
                    fillSelect('color', res, 'color', 'color');
                    hideLoading();
                }
            });
        }

        function updatePanelList() {
            showLoading();
            $.ajax({
                url: '{{ route('get-panels') }}',
                type: 'get',
                dataType: 'json',
                data: {
                    act_costing_id: $('#act_costing_id').val(),
                    color: $('#color').val()
                },
                success: function(res) {
                    fillSelect('panel', res, 'panel', 'panel');
                    hideLoading();
                }
            });
        }

        // Size dipakai ulang tiap roll, jadi cukup diambil sekali per color
        function updateSizeList() {
            showLoading();

            $.ajax({
                url: '{{ route('get-sizes') }}',
                type: 'get',
                dataType: 'json',
                data: {
                    act_costing_id: $('#act_costing_id').val(),
                    color: $('#color').val()
                },
                success: function(res) {
                    sizeRows = res || [];

                    renderSizeInputs();

                    hideLoading();
                }
            });
        }

        // Item fabric dibatasi WS, color & panel yang sedang dipilih
        function updateItemList() {
            showLoading();

            $.ajax({
                url: '{{ route('get-item-by-ws-color-panel') }}',
                type: 'get',
                dataType: 'json',
                data: {
                    act_costing_ws: $('#act_costing_ws').val(),
                    // color: $('#color').val(),
                    panel: $('#panel').val()
                },
                success: function(res) {
                    let select = document.getElementById('id_item');
                    let selected = select.value;

                    select.innerHTML = '<option value="">Pilih Item</option>';

                    (res || []).forEach((row) => {
                        let option = document.createElement("option");
                        option.value = row.id_item;
                        option.innerHTML = row.itemdesc;
                        select.appendChild(option);
                    });

                    select.value = selected;
                    $('#itemdesc').val(select.value ? select.options[select.selectedIndex].text : '');

                    hideLoading();
                }
            });
        }

        // Opsi part dipakai select part di header
        function updatePartList() {
            showLoading();

            $.ajax({
                url: '{{ route('get-parts-cutting-scrap') }}',
                type: 'get',
                dataType: 'json',
                data: {
                    act_costing_ws: $('#act_costing_ws').val(),
                    color: $('#color').val(),
                    panel: $('#panel').val()
                },
                success: function(res) {
                    // Pilihan yang sudah tersimpan tidak boleh hilang saat opsi dimuat ulang
                    let selected = $('#parts').val() || [];

                    partOptions = "";

                    (res || []).forEach((row) => {
                        partOptions += '<option value="' + row.part_detail_id + '">' + row.nama_part + ' (' + row.part_status + ')</option>';
                    });

                    document.getElementById('parts').innerHTML = partOptions;

                    $('#parts').val(selected).trigger('change.select2');

                    hideLoading();
                }
            });
        }

        // ================= Process 1 =================
        function processOne(e, event) {
            event.preventDefault();
            showLoading();

            let dataObj = { "process": 1, "id": $('#id').val() };

            $.each($(e).serializeArray(), function(i, field) {
                dataObj[field.name] = field.value;
            });

            // parts[] bernilai jamak, serializeArray hanya menyisakan yang terakhir
            dataObj["parts"] = $('#parts').val() || [];

            $.ajax({
                url: '{{ route('store-cutting-scrap') }}',
                type: 'post',
                dataType: 'json',
                data: dataObj,
                success: function(res) {
                    if (res.status == 200) {
                        // Nomor & id form baru terbit di langkah ini
                        $('#id').val(res.additional.id);
                        $('#no_form').val(res.additional.no_form);
                        $('#no-form-badge').text(res.additional.no_form);

                        $('#process').val(1);
                        $('#process-two-form').removeClass('d-none');

                        renderSelectedParts(res.additional.form_cut_scrap_form_parts || []);

                        $('#employee_id').focus();
                    } else {
                        swalError(res.message);
                    }

                    hideLoading();
                },
                error: function(jqXHR) {
                    handleError(jqXHR.responseJSON);
                    hideLoading();
                }
            });
        }

        function renderSelectedParts(parts) {
            let html = "";

            parts.forEach((part) => {
                html += '<span class="badge text-bg-primary me-1 px-3 py-2">' + (part.nama_part || part.part_detail_id) + '</span>';
            });

            document.getElementById('selected-part-badge').innerHTML = html;
            document.getElementById('selected-part-count').innerHTML = parts.length;
        }

        // ================= Process 2 =================
        // Check employee ID
        $("#employee_id").on("keyup", function(e) {
            if (e.keyCode === 13) {
                e.preventDefault();

                fetchScanOperator();
            }
        });

        // Scan Operator
        var html5QrcodeScannerOperator = new Html5Qrcode("reader-operator");
        var scannerInitializedOperator = false;

        // Initialize Scan Operator
        async function initScanOperator() {
            if (document.getElementById("reader-operator")) {
                if (html5QrcodeScannerOperator == null || (html5QrcodeScannerOperator && (html5QrcodeScannerOperator.isScanning == false))) {
                    const qrCodeSuccessCallback = (decodedText, decodedResult) => {
                            // handle the scanned code as you like, for example:
                        console.log(`Code matched = ${decodedText}`, decodedResult);

                        // store to input text
                        let breakDecodedText = decodedText.split('-');

                        document.getElementById('employee_id').value = breakDecodedText[0];

                        getScannedOperator(breakDecodedText[0]);

                        clearQrCodeScannerOperator();
                    };
                    const config = { fps: 10, qrbox: { width: 250, height: 250 } };

                    await html5QrcodeScannerOperator.start({ facingMode: "environment" }, config, qrCodeSuccessCallback);
                }
            }
        }

        // Stop and Clear Scan Operator
        async function clearQrCodeScannerOperator() {
            if (html5QrcodeScannerOperator && (html5QrcodeScannerOperator.isScanning)) {
                await html5QrcodeScannerOperator.stop();
                await html5QrcodeScannerOperator.clear();
            }
        }

        // Refresh Scan Operator
        async function refreshScanOperator() {
            await clearQrCodeScannerOperator();
            await initScanOperator();
        }

        // Fetch Scanned ID Operator
        function fetchScanOperator() {
            let idOperator = document.getElementById('employee_id').value;

            getScannedOperator(idOperator);
        }

        function getScannedOperator(id) {
            document.getElementById("loading").classList.remove("d-none");

            document.getElementById("employee_nik").value = "";
            document.getElementById("employee_name").value = "";

            if (isNotNull(id)) {
                return $.ajax({
                    url: '{{ route('get-scanned-employee') }}/' + id,
                    type: 'get',
                    dataType: 'json',
                    success: function(res) {
                        if (res) {
                            if (res.enroll_id) {
                                document.getElementById("employee_id").value = res.enroll_id;
                                document.getElementById("employee_nik").value = res.nik;
                                document.getElementById("employee_name").value = res.employee_name;
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal',
                                    text: 'Operator tidak ditemukan.',
                                    showCancelButton: false,
                                    showConfirmButton: true,
                                    confirmButtonText: 'Oke',
                                });
                            }
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: 'Terjadi kesalahan.',
                                showCancelButton: false,
                                showConfirmButton: true,
                                confirmButtonText: 'Oke',
                            });
                        }

                        document.getElementById("loading").classList.add("d-none");
                    },
                    error: function(jqXHR) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: jqXHR.responseText ? jqXHR.responseText : 'Terjadi kesalahan.',
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
                text: 'Operator tidak ditemukan',
                showCancelButton: false,
                showConfirmButton: true,
                confirmButtonText: 'Oke',
            });
        }

        function processTwo(e, event) {
            event.preventDefault();
            showLoading();

            let dataObj = { "process": 2, "id": $('#id').val() };

            $.each($(e).serializeArray(), function(i, field) {
                dataObj[field.name] = field.value;
            });

            $.ajax({
                url: '{{ route('store-cutting-scrap') }}',
                type: 'post',
                dataType: 'json',
                data: dataObj,
                success: function(res) {
                    if (res.status == 200) {
                        $('#process').val(2);
                        $('#process-three-form').removeClass('d-none');
                        $('#id_item').focus();
                    } else {
                        swalError(res.message);
                    }

                    hideLoading();
                },
                error: function(jqXHR) {
                    handleError(jqXHR.responseJSON);
                    hideLoading();
                }
            });
        }

        // ================= Process 3 =================
        function processThree(e, event) {
            event.preventDefault();
            showLoading();

            let dataObj = { "process": 3, "id": $('#id').val() };

            $.each($(e).serializeArray(), function(i, field) {
                dataObj[field.name] = field.value;
            });

            $.ajax({
                url: '{{ route('store-cutting-scrap') }}',
                type: 'post',
                dataType: 'json',
                data: dataObj,
                success: function(res) {
                    if (res.status == 200) {
                        $('#process').val(3);
                        $('#id_detail').val(res.additional.id);
                        $('#active-roll-badge').text(
                            'Barang Selected: ' + (res.additional.itemdesc || '-') +
                            ' (' + (res.additional.unit || '-') + ')' +
                            ' | Lot: ' + (res.additional.lot || '-') +
                            ' | Group: ' + (res.additional.group_roll || '-')
                        );

                        renderSizeInputs();

                        $('#process-four-form').removeClass('d-none');
                    } else {
                        swalError(res.message);
                    }

                    hideLoading();
                },
                error: function(jqXHR) {
                    handleError(jqXHR.responseJSON);
                    hideLoading();
                }
            });
        }

        // ================= Process 4 =================
        function renderSizeInputs() {
            let container = document.getElementById('size-container');

            if (!sizeRows.length) {
                container.innerHTML = '<div class="col-12"><small class="text-muted">Size belum tersedia untuk color ini.</small></div>';

                return;
            }

            let html = "";

            sizeRows.forEach((row, index) => {
                html += `
                    <div class="col-auto">
                        <div class="border rounded p-2" style="width: 120px;">
                            <label class="form-label mb-1">Size: ${row.size}</label>
                            <input type="hidden" name="size[${index}][so_det_id]" value="${row.so_det_id ?? ''}">
                            <input type="hidden" name="size[${index}][size]" value="${row.size}">
                            <input type="number" class="form-control form-control-sm text-center" name="size[${index}][qty]" value="">
                        </div>
                    </div>
                `;
            });

            container.innerHTML = html;
        }

        function processFour(e, event) {
            event.preventDefault();
            showLoading();

            let dataObj = {
                "process": 4,
                "id": $('#id').val(),
                "id_detail": $('#id_detail').val()
            };

            $.each($(e).serializeArray(), function(i, field) {
                dataObj[field.name] = field.value;
            });

            $.ajax({
                url: '{{ route('store-cutting-scrap') }}',
                type: 'post',
                dataType: 'json',
                data: dataObj,
                success: function(res) {
                    if (res.status == 200) {
                        appendRollRow(res.additional);
                        resetRollForm();
                    } else {
                        swalError(res.message);
                    }

                    hideLoading();
                },
                error: function(jqXHR) {
                    handleError(jqXHR.responseJSON);
                    hideLoading();
                }
            });
        }

        function appendRollRow(detail) {
            rollCount++;

            // Size setiap part isinya sama, jadi cukup diambil dari part pertama
            let firstPart = (detail.form_cut_scrap_parts || [])[0];
            let sizes = ((firstPart && firstPart.form_cut_scrap_sizes) || []).map((size) => {
                return '<span class="badge bg-light text-dark border">' + size.size + ' : ' + size.qty + '</span>';
            }).join(' ') || '-';

            let html = `
                <tr id="roll-row-${detail.id}">
                    <td>${rollCount}</td>
                    <td>${detail.id_item ?? '-'}</td>
                    <td>${detail.itemdesc ?? '-'}</td>
                    <td>${detail.qty_roll ?? 0} ${detail.unit ?? ''}</td>
                    <td>${detail.lot ?? '-'}</td>
                    <td>${detail.group_roll ?? '-'}</td>
                    <td>${sizes}</td>
                    <td><button type="button" class="btn btn-danger btn-sm" onclick="deleteRoll(${detail.id})"><i class="fa fa-trash"></i></button></td>
                </tr>
            `;

            document.getElementById('roll-table-body').insertAdjacentHTML('beforeend', html);
        }

        // Kembali ke langkah 3 supaya roll berikutnya bisa langsung diinput
        function resetRollForm() {
            $('#process').val(2);
            $('#id_detail').val('');
            $('#active-roll-badge').text('');

            $('#id_item').val('');
            $('#itemdesc').val('');
            $('#qty_roll').val(1);

            renderSizeInputs();

            $('#process-four-form').addClass('d-none');
            $('#process-three-form').removeClass('d-none');
            $('#id_item').focus();
        }

        function deleteRoll(idDetail) {
            showLoading();

            $.ajax({
                url: '{{ route('delete-cutting-scrap-detail') }}',
                type: 'post',
                dataType: 'json',
                data: {
                    _token: '{{ csrf_token() }}',
                    _method: 'delete',
                    id_detail: idDetail
                },
                success: function(res) {
                    if (res.status == 200) {
                        let row = document.getElementById('roll-row-' + idDetail);

                        if (row) {
                            row.remove();
                        }
                    } else {
                        swalError(res.message);
                    }

                    hideLoading();
                },
                error: function(jqXHR) {
                    handleError(jqXHR.responseJSON);
                    hideLoading();
                }
            });
        }

        // ================= Selesai =================
        function finishProcess() {
            Swal.fire({
                icon: 'question',
                title: 'Selesaikan Form?',
                text: 'Roll yang belum diisi size-nya akan dihapus.',
                showCancelButton: true,
                confirmButtonText: 'Selesai',
                cancelButtonText: 'Batal',
            }).then((result) => {
                if (!result.isConfirmed) {
                    return;
                }

                showLoading();

                $.ajax({
                    url: '{{ route('finish-process-cutting-scrap') }}',
                    type: 'post',
                    dataType: 'json',
                    data: {
                        _token: '{{ csrf_token() }}',
                        id: $('#id').val()
                    },
                    success: function(res) {
                        if (res.status == 200) {
                            window.location.href = res.redirect;

                            return;
                        }

                        swalError(res.message);

                        hideLoading();
                    },
                    error: function(jqXHR) {
                        handleError(jqXHR.responseJSON);
                        hideLoading();
                    }
                });
            });
        }

        function swalError(message) {
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: message,
                showConfirmButton: true,
                confirmButtonText: 'Oke',
            });
        }

        // ================= Init =================
        $(document).ready(function() {
            // Form lanjutan : opsi size, part & item disiapkan dari header yang tersimpan
            if ({{ $currentProcess }} >= 1) {
                updateSizeList();
                updatePartList();
                updateItemList();
            }
        });
    </script>
@endsection
