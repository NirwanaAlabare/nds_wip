@extends('layouts.index')

@section('custom-link')
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@endsection

@section('content')
    @php
        $currentPiping = null;

        if (isset($piping)) {
            $currentPiping = $piping;
        }
    @endphp
    <div class="d-flex justify-content-between mb-3">
        <h5 class="fw-bold text-sb"><i class="fa fa-plus fa-sm"></i> Tambah Proses Piping</h5>
        <a href="{{ route('piping-process') }}" class="btn btn-primary btn-sm px-1 py-1"><i class="fas fa-reply"></i> Kembali ke Data Proses Piping</a>
    </div>
    <input type="hidden" id="process" value="{{ $currentPiping ? $currentPiping->process : null }}"></input>
    {{-- PROCESS ONE --}}
        <form action="{{ route("store-piping-process") }}" method="post" id="piping-process-header" onsubmit="processOne(this, event)">
            @csrf
            <div class="card card-sb">
                <div class="card-header">
                    <h5 class="card-title fw-bold">
                        Header Data
                    </h5>
                </div>
                <div class="card-body">
                    <input type="hidden" id="process_1" name="process_1" value="1" readonly>
                    <div class="row justify-content-start align-items-end g-3">
                        <div class="col-6 col-md-4">
                            <div class="mb-1">
                                <div class="form-group mb-0">
                                    <label>Kode Piping</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="kode_piping" name="kode_piping" value="{{ $currentPiping ? $currentPiping->kode_piping : null }}" readonly>
                                        <button type="button" class="btn btn-sb-secondary" onclick="generateCode()"><i class="fa fa-rotate"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="mb-1">
                                <div class="form-group mb-0">
                                    <label>Buyer</label>
                                    @if ($currentPiping)
                                        <input type="hidden" class="form-control" id="buyer_id" name="buyer_id" value="{{ $currentPiping ? $currentPiping->masterPiping->buyer_id : null }}">
                                        <input type="text" class="form-control" id="buyer" name="buyer" value="{{ $currentPiping ? $currentPiping->masterPiping->buyer : null }}" readonly>
                                    @else
                                        <select class="form-select select2bs4" id="buyer_id" name="buyer_id">
                                            <option selected="selected" value="">Pilih Buyer</option>
                                            @foreach ($buyers as $buyer)
                                                <option value="{{ $buyer->id }}">
                                                    {{ $buyer->buyer }}
                                                </option>
                                            @endforeach
                                        </select>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="mb-1">
                                <div class="form-group mb-0">
                                    <label>No. WS</label>
                                    @if ($currentPiping)
                                        <input type="hidden" class="form-control" id="act_costing_id" name="act_costing_id" value="{{ $currentPiping ? $currentPiping->masterPiping->act_costing_id : null }}">
                                        <input type="text" class="form-control" id="act_costing_ws" name="act_costing_ws" value="{{ $currentPiping ? $currentPiping->masterPiping->act_costing_ws : null }}" readonly>
                                    @else
                                        <select class="form-select select2bs4" id="act_costing_id" name="act_costing_id">
                                            <option selected="selected" value="">Pilih WS</option>
                                            {{-- select 2 option --}}
                                        </select>
                                    @endif
                                </div>
                                <input type="hidden" name="act_costing_ws" id="act_costing_ws">
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="mb-1">
                                <div class="form-group mb-0">
                                    <label>Style</label>
                                    <input type="text" class="form-control" id="style" name="style" value="{{ $currentPiping ? $currentPiping->masterPiping->style : null }}" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="mb-1">
                                <div class="form-group mb-0">
                                    <label>Color</label>
                                    @if ($currentPiping)
                                        <input type="text" class="form-control" id="color" name="color" value="{{ $currentPiping ? $currentPiping->masterPiping->color : null }}" readonly>
                                    @else
                                        <select class="form-select select2bs4" id="color" name="color">
                                            <option selected="selected" value="">Pilih Color</option>
                                            {{-- select 2 option --}}
                                        </select>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="mb-1">
                                <div class="form-group mb-0">
                                    <label>Part</label>
                                    @if ($currentPiping)
                                        <input type="hidden" class="form-control" id="master_piping_id" name="master_piping_id" value="{{ $currentPiping ? $currentPiping->masterPiping->id : null }}">
                                        <input type="text" class="form-control" id="part" name="part" value="{{ $currentPiping ? $currentPiping->masterPiping->part : null }}" readonly>
                                    @else
                                        <select class="form-select select2bs4" id="master_piping_id" name="master_piping_id">
                                            <option selected="selected" value="">Pilih Part</option>
                                            {{-- select 2 option --}}
                                        </select>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="mb-1">
                                <div class="form-group mb-0">
                                    <label>Panjang</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="panjang" name="panjang" value="{{ $currentPiping ? $currentPiping->masterPiping->panjang : null }}" readonly>
                                        <input type="text" class="form-control" id="unit" name="unit" value="{{ $currentPiping ? $currentPiping->masterPiping->unit : null }}" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex justify-content-center">
                                <button type="submit" class="btn btn-success mt-1 mb-3 fw-bold">START</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>

    {{-- PROCESS TWO --}}
        <form action="{{ route("store-piping-process") }}" method="post" id="piping-process-scan" onsubmit="processTwo(this, event)" class="d-none">
            @csrf
            <div class="card card-sb">
                <div class="card-header">
                    <h5 class="card-title fw-bold">
                        Scan Roll
                    </h5>
                </div>
                <div class="card-body">
                    <input type="hidden" id="process_2" name="process_2" value="2" readonly>
                    <div class="row justify-content-start align-items-end g-3 mb-3">
                        {{-- Switch Method --}}
                        <div class="col-12 col-md-12">
                            <div class="d-flex justify-content-center mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="switch-method" checked onchange="switchMethod(this)">
                                    <label class="form-check-label" id="to-scan">Scan Roll</label>
                                    <label class="form-check-label d-none" id="to-multi">Multi Roll</label>
                                </div>
                            </div>
                        </div>
                        {{-- Scan Method --}}
                        <div class="col-md-12" id="scan-method">
                            <div class="mb-3" id="reader"></div>
                            <div class="row align-items-end g-3">
                                <div class="col-12 col-md-12">
                                    <label class="form-label"><small><b>ID Roll</b></small></label>
                                    <div class="input-group">
                                        <input type="text" class="form-control form-control-sm" name="id_roll" id="id_roll">
                                        <button class="btn btn-sm btn-success" type="button" id="get-button" onclick="fetchScan()">Get</button>
                                        <button class="btn btn-sm btn-primary" type="button" id="scan-button" onclick="refreshScan()">Scan</button>
                                    </div>
                                </div>
                                <div class="col-6 col-md-6">
                                    <label class="form-label"><small><b>ID Item</b></small></label>
                                    <input type="text" class="form-control form-control-sm" name="id_item" id="id_item" readonly>
                                </div>
                                <div class="col-6 col-md-6">
                                    <label class="form-label"><small><b>Detail Item</b></small></label>
                                    <input type="text" class="form-control form-control-sm" name="detail_item" id="detail_item" readonly>
                                </div>
                                <div class="col-6 col-md-6">
                                    <label class="form-label"><small><b>Color Act</b></small></label>
                                    <input type="text" class="form-control form-control-sm" name="color_act" id="color_act">
                                </div>
                                <div class="col-6 col-md-6">
                                    <label class="form-label"><small><b>Qty</b></small></label>
                                    <div class="input-group">
                                        <input type="text" class="form-control form-control-sm" name="qty_item" id="qty_item" readonly>
                                        <input type="text" class="form-control form-control-sm" name="unit_item" id="unit_item" readonly>
                                    </div>
                                </div>
                                <div class="col-12 col-md-12">
                                    <button type="submit" class="btn btn-sm btn-success btn-block fw-bold mt-3">NEXT</button>
                                </div>
                            </div>
                        </div>
                        {{-- Multi Method --}}
                        <div class="col-md-12 d-none" id="multi-method">
                            <div id="multi-rolls" class="mb-3">
                                <div class="row align-items-end" id="multi-roll-1">
                                    <div class="col-md-2">
                                        <label class="form-label"><small><b>ID Roll</b></small></label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="id_roll_1" name="id_roll[1]">
                                            <button class="btn btn-success" type="button" id="get_button_1" onclick="getMultiItemForm(1)">Get</button>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label"><small><b>No. Form</b></small></label>
                                        <select class="form-select" id="id_form_roll_1" name="id_form_roll[1]" onchange="getMultiItemPiping(1)">
                                            {{-- Form Options --}}
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label"><small><b>Color</b></small></label>
                                        <input type="text" class="form-control" id="color_roll_1" name="color_roll[1]" readonly>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label"><small><b>Group</b></small></label>
                                        <input type="text" class="form-control" id="group_roll_1" name="group_roll[1]" readonly>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label"><small><b>Lot</b></small></label>
                                        <input type="text" class="form-control" id="lot_roll_1" name="lot_roll[1]" readonly>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label"><small><b>Qty</b></small></label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="qty_roll_1" name="qty[1]" readonly>
                                            <input type="text" class="form-control" id="unit_roll_1" name="unit[1]" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end mb-3">
                                <button type="button" class="btn btn-sb-secondary w-auto" id="add-new-roll" onclick="addMultiRoll()"><i class="fa fa-plus fa-sm"></i></button>
                            </div>
                            <div class="row justify-content-end g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label"><small><b>Total Roll</b></small></label>
                                    <input type="text" class="form-control form-control-sm" id="total_roll" name="total_roll" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label"><small><b>Total Qty</b></small></label>
                                    <div class="input-group">
                                        <input type="text" class="form-control form-control-sm" id="total_qty" name="total_qty" readonly>
                                        <input type="text" class="form-control form-control-sm" id="total_unit" name="total_unit" readonly>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-sm btn-block btn-sb fw-bold">NEXT</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    {{-- END OF PROCESS TWO --}}

    {{-- PROCESS THREE --}}
        <form action="{{ route("store-piping-process") }}" method="post" id="piping-process-final" onsubmit="processThree(this, event)" class="d-none">
            @csrf
            <div class="card card-sb">
                <div class="card-header">
                    <h5 class="card-title fw-bold">
                        Process
                    </h5>
                </div>
                <div class="card-body">
                    <input type="hidden" id="process_3" name="process_3" value="3" readonly>
                    <div class="row justify-content-start align-items-end g-3 mb-3">

                    </div>
                </div>
            </div>
        </form>
    {{-- END OF PROCESS THREE --}}
@endsection

@section('custom-script')
    <!-- Select2 -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <!-- Page specific script -->
    <script>
        // Initial Window On Load Event
        $(document).ready(async function () {
            initial();
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

        // Init
        async function initial() {
            if (document.getElementById("process").value > 0) {
                // Check Current Process
                await checkProcess(document.getElementById("process").value);
            } else {
                //Reset Form
                if (document.getElementById('piping-process-header')) {
                    document.getElementById('piping-process-header').reset();

                    $("#buyer_id").val(null).trigger("change");
                }

                // Select2 Prevent Step-Jump Input ( Step = WS -> Color -> Panel )
                $("#color").prop("disabled", true);
                $("#master_piping_id").prop("disabled", true);

                // Generate Code
                generateCode();
            }
        }

        // CHECK PROCESS
            function checkProcess(process) {
                switch (process) {
                    case "1" :
                        initProcessTwo();

                        break;
                }
            }

        // PROCESS ONE :
            // Generate Piping Code
            function generateCode() {
                document.getElementById("loading").classList.remove("d-none");

                $.ajax({
                    url: "{{ route("generate-piping-process") }}",
                    type: "get",
                    dataType: "json",
                    success: function (response) {
                        $("#kode_piping").val(response);

                        document.getElementById("loading").classList.add("d-none");
                    },
                    error: function (jqXHR) {
                        console.log(jqXHR);

                        document.getElementById("loading").classList.add("d-none");
                    }
                });
            }

            // Step One (Buyer) on change event
            $('#buyer_id').on('change', function(e) {
                if (this.value) {
                    $('#buyer').val($('#buyer_id').find(":selected").text());

                    updateOrderList();
                }
            });

            // Step Two (Order) on change event
            $('#act_costing_id').on('change', function(e) {
                if (this.value) {
                    $('#act_costing_ws').val($('#act_costing_id').find(":selected").text());

                    updateOrderInfo();
                    updateColorList();
                }
            });

            // Step Three (Color) on change event
            $('#color').on('change', function(e) {
                if (this.value) {
                    updatePartList();
                }
            });

            // Step Four (Part) on change event
            $('#master_piping_id').on('change', function(e) {
                if (this.value) {
                    takeMasterPiping();
                }
            });

            // Get Master Piping Buyer List
            function updateBuyerList() {
                document.getElementById('loading').classList.remove("d-none");

                document.getElementById("buyer_id").value = null;
                document.getElementById("buyer_id").innerHTML = null;

                $.ajax({
                    url: "{{ route("list-master-piping") }}",
                    type: "get",
                    data: {
                        data: 'buyer'
                    },
                    dataType: "json",
                    success: function (response) {
                        if (response) {
                            if (response.length > 0) {
                                let selectElement = document.getElementById("buyer_id");

                                for (let i = 0; i < response.length; i++) {
                                    let newOption = document.createElement("option");
                                    newOption.value = response[i].buyer_id;
                                    newOption.innerHTML = response[i].buyer;

                                    selectElement.prepend(newOption);
                                }

                                $("#buyer_id").val(response[response.length-1].buyer_id).trigger("change");
                            }
                        }

                        document.getElementById("loading").classList.add("d-none");
                    },
                    error: function (jqXHR) {
                        console.error(jqXHR);

                        document.getElementById("loading").classList.add("d-none");
                    }
                });
            }

            // Get Master Piping Order List
            function updateOrderList() {
                document.getElementById('loading').classList.remove("d-none");

                document.getElementById("act_costing_id").value = null;
                document.getElementById("act_costing_id").innerHTML = null;

                $.ajax({
                    url: "{{ route("list-master-piping") }}",
                    type: "get",
                    data: {
                        data: 'worksheet',
                        buyer_id: $('#buyer_id').val()
                    },
                    dataType: "json",
                    success: function (response) {
                        if (response) {
                            if (response.length > 0) {
                                let selectElement = document.getElementById("act_costing_id");

                                for (let i = 0; i < response.length; i++) {
                                    let newOption = document.createElement("option");
                                    newOption.value = response[i].act_costing_id;
                                    newOption.innerHTML = response[i].act_costing_ws;

                                    selectElement.prepend(newOption);
                                }

                                $("#act_costing_id").val(response[response.length-1].act_costing_id).trigger("change");
                            }
                        }

                        document.getElementById('loading').classList.add('d-none');
                    },
                    error: function (jqXHR) {
                        console.error(jqXHR);

                        document.getElementById('loading').classList.add('d-none');
                    }
                });
            }

            // Update Order Information Based on Order WS and Order Color
            function updateOrderInfo() {
                return $.ajax({
                    url: '{{ route("get-general-order") }}',
                    type: 'get',
                    data: {
                        act_costing_id: $('#act_costing_id').val()
                    },
                    dataType: 'json',
                    success: function (res) {
                        if (res) {
                            console.log(res);
                            document.getElementById('act_costing_ws').value = res.kpno;
                            document.getElementById('buyer').value = res.buyer;
                            document.getElementById('style').value = res.styleno;
                        }
                    },
                    error: function (jqXHR) {
                        console.error(jqXHR);

                        document.getElementById('loading').classList.add('d-none');
                    }
                });
            }

            // Get Master Piping Color List
            function updateColorList() {
                document.getElementById('loading').classList.remove("d-none");

                document.getElementById("color").value = null;
                document.getElementById("color").innerHTML = null;

                $.ajax({
                    url: "{{ route("list-master-piping") }}",
                    type: "get",
                    data: {
                        data: 'color',
                        buyer_id: $('#buyer_id').val(),
                        act_costing_id: $('#act_costing_id').val()
                    },
                    dataType: "json",
                    success: function (response) {
                        if (response) {
                            if (response.length > 0) {
                                $("#color").prop("disabled", false);

                                let selectElement = document.getElementById("color");

                                for (let i = 0; i < response.length; i++) {
                                    let newOption = document.createElement("option");
                                    newOption.value = response[i].color;
                                    newOption.innerHTML = response[i].color;

                                    selectElement.prepend(newOption);
                                }

                                $("#color").val(response[response.length-1].color).trigger("change");
                            }
                        }

                        document.getElementById('loading').classList.add('d-none');
                    },
                    error: function (jqXHR) {
                        console.error(jqXHR);

                        document.getElementById('loading').classList.add('d-none');
                    }
                });
            }

            // Get Master Piping Part List
            function updatePartList() {
                document.getElementById('loading').classList.remove("d-none");

                document.getElementById("master_piping_id").value = null;
                document.getElementById("master_piping_id").innerHTML = null;

                $.ajax({
                    url: "{{ route("list-master-piping") }}",
                    type: "get",
                    data: {
                        data: 'part',
                        buyer_id: $('#buyer_id').val(),
                        act_costing_id: $('#act_costing_id').val(),
                        color: $('#color').val(),
                    },
                    dataType: "json",
                    success: function (response) {
                        if (response) {
                            if (response.length > 0) {
                                $("#master_piping_id").prop("disabled", false);

                                let selectElement = document.getElementById("master_piping_id");

                                for (let i = 0; i < response.length; i++) {
                                    let newOption = document.createElement("option");
                                    newOption.value = response[i].id;
                                    newOption.innerHTML = response[i].part.toUpperCase();

                                    selectElement.prepend(newOption);
                                }

                                $("#master_piping_id").val(response[response.length-1].part).trigger("change");
                            }
                        }

                        document.getElementById('loading').classList.add('d-none');
                    },
                    error: function (jqXHR) {
                        console.error(jqXHR);

                        document.getElementById('loading').classList.add('d-none');
                    }
                });
            }

            // Take Master Piping Data
            function takeMasterPiping() {
                $.ajax({
                    url: "{{ route("take-master-piping") }}/"+$("#master_piping_id").val(),
                    type: "get",
                    data: "data",
                    dataType: "json",
                    success: function (response) {
                        document.getElementById("panjang").value = response.panjang;
                        document.getElementById("unit").value = response.unit.toUpperCase();
                    }
                });
            }

            // Prevent Form Submit When Pressing Enter
            document.getElementById("piping-process-header").onkeypress = function(e) {
                var key = e.charCode || e.keyCode || 0;
                if (key == 13) {
                    e.preventDefault();
                }
            }

            // Submit Process One
            function processOne(e, event) {
                document.getElementById("loading").classList.remove("d-none");

                event.preventDefault();

                let form = new FormData(e);

                let dataObj = {
                    "process": 1,
                }

                form.forEach((value, key) => dataObj[key] = value);

                $.ajax({
                    url: "{{ route("store-piping-process") }}",
                    type: "post",
                    data: dataObj,
                    dataType: "json",
                    success: function (response) {
                        if (response.status == 200) {
                            initProcessTwo();
                        }

                        document.getElementById("loading").classList.add("d-none");
                    },
                    error: function (jqXHR) {
                        console.error(jqXHR);

                        document.getElementById("loading").classList.add("d-none");
                    }
                });
            }
        // END OF PEOCESS ONE

        // PROCESS TWO :
            // -Init Process Two-
            function initProcessTwo() {
                initScan();

                resetScanMethod();
                resetMultiMethod();

                document.getElementById("piping-process-scan").classList.remove("d-none");

                let form = document.getElementById("piping-process-header");

                for (let i = 0; i < form.length; i++) {
                    form[i].setAttribute("disabled", "true");
                }

                document.getElementById("switch-method").checked = true;
            }

            // -Scan Roll-
            var method;

            function switchMethod(element) {
                if (element.checked) {
                    toScanMethod();
                } else {
                    toMultiMethod();
                }
            }

            function toMultiMethod() {
                method = "multi";

                document.getElementById("scan-method").classList.add('d-none');
                document.getElementById("to-scan").classList.add('d-none');

                resetScanMethod();

                document.getElementById("multi-method").classList.remove('d-none');
                document.getElementById("to-multi").classList.remove('d-none');

                clearScan();

                location.href = "#piping-process-scan";
            }

            function toScanMethod() {
                method = "scan";

                document.getElementById("multi-method").classList.add('d-none');
                document.getElementById("to-multi").classList.add('d-none');

                resetMultiMethod();

                document.getElementById("scan-method").classList.remove('d-none');
                document.getElementById("to-scan").classList.remove('d-none');

                initScan();

                location.href = "#piping-process-scan";
            }

            async function resetScanMethod() {
                let idRoll = document.getElementById('id_roll');

                idRoll.value = null;

                fetchScan();
            }

            async function resetMultiMethod() {
                let rolls = document.querySelectorAll('#multi-rolls .row');

                for (let i = 0; i < rolls.length; i++) {
                    let roll = document.getElementById('multi-roll-' + (i+1));

                    if (i == 0) {
                        clearMultiItem((i+1));
                    } else {
                        roll.remove()
                    }
                }

                calculateMultiItem();
            }

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

                            document.getElementById('kode_barang').value = breakDecodedText[0];

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
                let kodeBarang = document.getElementById('id_roll').value;

                await getScannedItem(kodeBarang);
            }

            // -Get Scanned Item Data-
            async function getScannedItem(id) {
                document.getElementById("loading").classList.remove("d-none");

                document.getElementById("id_item").value = "";
                document.getElementById("detail_item").value = "";
                document.getElementById("qty_item").value = "";
                document.getElementById("unit_item").value = "";
                document.getElementById("color_act").value = "";

                if (isNotNull(id)) {
                    return $.ajax({
                        url: '{{ route('item-piping') }}/' + id,
                        type: 'get',
                        dataType: 'json',
                        success: function(res) {
                            console.log(res);

                            if (typeof res === 'object' && res !== null) {
                                document.getElementById("id_item").value = res.id_item;
                                document.getElementById("detail_item").value = res.detail_item;
                                document.getElementById("qty_item").value = res.piping;
                                document.getElementById("unit_item").value = res.unit;
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

            // -Add Multi Roll-
            async function addMultiRoll() {
                if (document.getElementById('id_roll_1').value && document.getElementById('id_form_roll_1').value) {
                    // Clone the existing roll element
                    let rollElement = document.getElementById('multi-roll-1');
                    let newRoll = rollElement.cloneNode(true);  // Clone the element including its children

                    // Update the IDs and names to make them unique for the new roll
                    let rollCount = document.querySelectorAll('#multi-rolls .row').length + 1;  // Get the next roll count
                    newRoll.id = 'multi-roll-' + rollCount;  // Update the ID for the new roll

                    // Update all input names and IDs within the new roll to ensure uniqueness
                    let inputs = newRoll.querySelectorAll('input, select, button');
                    inputs.forEach(function(input) {
                        input.value = null;

                        let id = input.getAttribute('id');
                        if (id) {
                            input.setAttribute('id', id.replace(/\d+/, rollCount));  // Update ID attribute
                        }

                        let name = input.getAttribute('name');
                        if (name) {
                            input.setAttribute('name', name.replace(/\d+/, rollCount));  // Update name attribute
                        }

                        if (id.includes("get_button")) {
                            let onclick = input.getAttribute('onclick');
                            if (onclick) {
                                input.setAttribute('onclick', onclick.replace(/\d+/, rollCount));  // Update onclick attribute
                            }
                        }

                        if (id.includes("id_form_roll")) {
                            input.innerHTML = null;

                            let onchange = input.getAttribute('onchange');
                            if (onchange) {
                                input.setAttribute('onchange', onchange.replace(/\d+/, rollCount));  // Update onchange attribute
                            }
                        }
                    });

                    // Append the new roll element to the container
                    document.getElementById('multi-rolls').appendChild(newRoll);
                }
            }

            // -Check Multi Roll-
            function checkMultiItem(check = "all") {
                console.log(check);

                let unitSetting = document.getElementById('unit_roll_1').value;

                if (check > 1) {
                    let roll = document.getElementById('multi-roll-' + (check));

                    let rollUnit = roll.querySelector('#unit_roll_' + (check));

                    if (rollUnit.value != unitSetting) {
                        return false;
                    }
                } else {
                    let rolls = document.querySelectorAll('#multi-rolls .row');

                    for (let i = 1; i < rolls.length; i++) {
                        let roll = document.getElementById('multi-roll-' + (i+1));

                        let rollUnit = roll.querySelector('#unit_roll_' + (i+1));

                        if (rollUnit.value != unitSetting) {
                            return false;
                        }
                    }
                }

                return true;
            }

            // -Calculate Multi Roll-
            function calculateMultiItem() {
                let totalRollQty = document.getElementById("total_qty");
                let totalRollUnit = document.getElementById("total_unit");

                console.log(totalRollQty, totalRollUnit);

                if (totalRollQty && totalRollUnit) {
                    totalRollQty.value = null;
                    totalRollUnit.value = null;

                    let rolls = document.querySelectorAll('#multi-rolls .row');

                    let totalRoll = 0;
                    for (let i = 0; i < rolls.length; i++) {
                        let roll = document.getElementById('multi-roll-' + (i+1));

                        let idRoll = document.getElementById('id_roll_' + (i+1));

                        let rollQty = roll.querySelector('#qty_roll_' + (i+1));
                        let rollUnit = roll.querySelector('#unit_roll_' + (i+1));

                        rollQty.value ? totalRollQty.value = Number(totalRollQty.value) + Number(rollQty.value) : console.log("null value");
                        rollUnit.value ? totalRollUnit.value =  rollUnit.value : console.log("null value");

                        idRoll.value ? totalRoll++ : console.log("null value");
                    }

                    document.getElementById("total_roll").value = totalRoll;
                }
            }

            // -Get Multi Item Form-
            function getMultiItemForm(index) {
                document.getElementById("loading").classList.remove("d-none");

                let id  = document.getElementById("id_roll_"+index).value;

                $.ajax({
                    url: "{{ route("item-forms-piping") }}/"+id,
                    type: "get",
                    dataType: "json",
                    success: function (response) {
                        let select = document.getElementById("id_form_roll_"+index)

                        if (select) {
                            select.innerHTML = null;

                            if (response && response.length > 0) {
                                for (let i = 0; i < response.length ;i++) {
                                    let option = document.createElement("option");
                                    option.setAttribute("value", response[i].id);
                                    option.innerText = response[i].no_form;

                                    select.appendChild(option);
                                }
                            }

                            $("#id_form_roll_"+index).val(response && response[0] ? response[0].id : null).trigger("change");
                        }

                        document.getElementById("loading").classList.add("d-none");
                    },
                    error: function (jqXHR) {
                        console.error(jqXHR);

                        document.getElementById("loading").classList.add("d-none");
                    }
                });
            }

            // -Get Multi Item Piping-
            function getMultiItemPiping(index) {
                document.getElementById("loading").classList.remove("d-none");

                let id  = document.getElementById("id_roll_"+index).value;
                let idForm  = document.getElementById("id_form_roll_"+index).value;

                let totalRollQty = document.getElementById("total_qty");
                let totalRollUnit = document.getElementById("total_unit");

                $.ajax({
                    url: "{{ route("item-piping-piping") }}/"+id+"/"+idForm,
                    type: "get",
                    // dataType: "json",
                    success: async function (response) {
                        console.log("piping", response, typeof response === 'object', response && typeof response === 'object');

                        if (response && typeof response === 'object') {
                            document.getElementById("color_roll_"+index).value = response.color_act;
                            document.getElementById("group_roll_"+index).value = response.group_roll;
                            document.getElementById("lot_roll_"+index).value = response.lot;
                            document.getElementById("qty_roll_"+index).value = response.piping;
                            document.getElementById("unit_roll_"+index).value = response.unit;
                        } else {
                            document.getElementById("color_roll_"+index).value = "";
                            document.getElementById("group_roll_"+index).value = "";
                            document.getElementById("lot_roll_"+index).value = "";
                            document.getElementById("qty_roll_"+index).value = "";
                            document.getElementById("unit_roll_"+index).value = "";
                        }

                        console.log("check multi item", checkMultiItem(index));

                        if (!checkMultiItem(index)) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: 'Unit Roll tidak sesuai.',
                                showCancelButton: false,
                                showConfirmButton: true,
                                confirmButtonText: 'Oke',
                            });

                            clearMultiItem(index);
                        }

                        calculateMultiItem();

                        document.getElementById("loading").classList.add("d-none");
                    },
                    error: function (jqXHR) {
                        console.error(jqXHR);

                        document.getElementById("loading").classList.add("d-none");
                    }
                });
            }

            function clearMultiItem(index) {
                document.getElementById("id_roll_"+index).value = "";
                document.getElementById("id_form_roll_"+index).value = "";
                document.getElementById("color_roll_"+index).value = "";
                document.getElementById("group_roll_"+index).value = "";
                document.getElementById("lot_roll_"+index).value = "";
                document.getElementById("qty_roll_"+index).value = "";
                document.getElementById("unit_roll_"+index).value = "";
            }

            // Submit Process Two
            function processTwo(e, event) {
                document.getElementById("loading").classList.remove("d-none");

                event.preventDefault();

                let form = new FormData(e);

                let dataObj = {
                    "process": 2,
                }

                form.forEach((value, key) => dataObj[key] = value);

                $.ajax({
                    url: "{{ route("store-piping-process") }}",
                    type: "post",
                    data: dataObj,
                    dataType: "json",
                    success: function (response) {
                        if (response.status == 200) {
                            // initProcessThree();
                        }

                        document.getElementById("loading").classList.add("d-none");
                    },
                    error: function (jqXHR) {
                        console.error(jqXHR);

                        document.getElementById("loading").classList.add("d-none");
                    }
                });
            }
        // END OF PROCESS TWO

        // Reset Step
        async function resetStep() {
            await $("#buyer_id").val(null).trigger("change");
            await $("#act_costing_id").val(null).trigger("change");
            await $("#color").val(null).trigger("change");
            await $("#color").prop("disabled", true);
            await $("#master_piping_id").val(null).trigger("change");
            await $("#master_piping_id").prop("disabled", true);
        }
    </script>
@endsection
