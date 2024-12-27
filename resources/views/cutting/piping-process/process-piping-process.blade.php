@extends('layouts.index')

@section('custom-link')
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@endsection

@section('content')
    <div class="d-flex justify-content-between mb-3">
        <h5 class="fw-bold text-sb"><i class="fa fa-plus fa-sm"></i> Tambah Proses Piping</h5>
        <a href="{{ route('piping-process') }}" class="btn btn-primary btn-sm px-1 py-1"><i class="fas fa-reply"></i> Kembali ke Data Proses Piping</a>
    </div>
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
                                        <input type="text" class="form-control" id="kode_piping" name="kode_piping" value="" readonly>
                                        <button type="button" class="btn btn-sb-secondary" onclick="generateCode()"><i class="fa fa-rotate"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="mb-1">
                                <div class="form-group mb-0">
                                    <label>Buyer</label>
                                    <select class="form-select select2bs4" id="buyer_id" name="buyer_id">
                                        <option selected="selected" value="">Pilih Buyer</option>
                                        @foreach ($buyers as $buyer)
                                            <option value="{{ $buyer->id }}">
                                                {{ $buyer->buyer }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <input type="hidden" name="buyer" id="buyer">
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="mb-1">
                                <div class="form-group mb-0">
                                    <label>No. WS</label>
                                    <select class="form-select select2bs4" id="act_costing_id" name="act_costing_id">
                                        <option selected="selected" value="">Pilih WS</option>
                                        {{-- select 2 option --}}
                                    </select>
                                </div>
                                <input type="hidden" name="act_costing_ws" id="act_costing_ws">
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="mb-1">
                                <div class="form-group mb-0">
                                    <label>Style</label>
                                    <input type="text" class="form-control" id="style" name="style" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="mb-1">
                                <div class="form-group mb-0">
                                    <label>Color</label>
                                    <select class="form-select select2bs4" id="color" name="color">
                                        <option selected="selected" value="">Pilih Color</option>
                                        {{-- select 2 option --}}
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="mb-1">
                                <div class="form-group mb-0">
                                    <label>Part</label>
                                    <select class="form-select select2bs4" id="master_piping_id" name="master_piping_id">
                                        <option selected="selected" value="">Pilih Part</option>
                                        {{-- select 2 option --}}
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="mb-1">
                                <div class="form-group mb-0">
                                    <label>Panjang</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="panjang" name="panjang" value="" readonly>
                                        <input type="text" class="form-control" id="unit" name="unit" value="" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex justify-content-center">
                                <button type="submit" class="btn btn-success mt-1 mb-3 fw-bold">NEXT</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>

    {{-- PROCESS TWO --}}
        <form action="{{ route("store-piping-process") }}" method="post" id="piping-process-scan" onsubmit="processTwo(this, event)">
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
                                    <button type="submit" class="btn btn-sm btn-success btn-block fw-bold">NEXT</button>
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
                                            <button class="btn btn-success" type="button" id="get_button_1">Get</button>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label"><small><b>No. Form</b></small></label>
                                        <select class="form-select form-select-sm select2bs4" id="no_form_roll_1" name="no_form_roll[1]">
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
                                            <input type="text" class="form-control" id="qty_1" name="qty[1]" readonly>
                                            <input type="text" class="form-control" id="unit_1" name="unit[1]" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end mb-3">
                                <button class="btn btn-sb-secondary w-auto" id="add-new-roll"><i class="fa fa-plus fa-sm"></i></button>
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
        <form action="{{ route("store-piping-process") }}" method="post" id="piping-process-scan" onsubmit="processThree(this, event)">
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
                                    <button type="submit" class="btn btn-sm btn-success btn-block fw-bold">NEXT</button>
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
                                            <button class="btn btn-success" type="button" id="get_button_1">Get</button>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label"><small><b>No. Form</b></small></label>
                                        <select class="form-select form-select-sm select2bs4" id="no_form_roll_1" name="no_form_roll[1]">
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
                                            <input type="text" class="form-control" id="qty_1" name="qty[1]" readonly>
                                            <input type="text" class="form-control" id="unit_1" name="unit[1]" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end mb-3">
                                <button class="btn btn-sb-secondary w-auto" id="add-new-roll"><i class="fa fa-plus fa-sm"></i></button>
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
        function initial() {
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

        // Check Current Process
        function checkProcess() {

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
                        document.getElementById("loading").classList.add("d-none");

                        $("#kode_piping").val(response);
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

            // Submit Process
            function processOne(e, event) {
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
                    }
                });
            }
        // END OF PEOCESS ONE

        // PROCESS TWO :
            // -Init Process Two-
            function initProcessTwo() {
                let form = document.getElementById("piping-process-header");

                for (let i = 0; i < form.length; i++) {
                    form[i].setAttribute("disabled", "true");
                }
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

                document.getElementById("multi-method").classList.remove('d-none');
                document.getElementById("to-multi").classList.remove('d-none');

                clearScan();

                location.href = "#piping-process-scan";
            }

            function toScanMethod() {
                method = "scan";

                document.getElementById("multi-method").classList.add('d-none');
                document.getElementById("to-multi").classList.add('d-none');

                document.getElementById("scan-method").classList.remove('d-none');
                document.getElementById("to-scan").classList.remove('d-none');

                initScan();

                location.href = "#piping-process-scan";
            }

            // -Scanner-
            var html5QrcodeScanner = null;

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
            function fetchScan() {
                let kodeBarang = document.getElementById('id_roll').value;

                getScannedItem(kodeBarang);
            }

            // -Get Scanned Item Data-
            function getScannedItem(id) {
                document.getElementById("loading").classList.remove("d-none");

                document.getElementById("id_item").value = "";
                document.getElementById("detail_item").value = "";
                document.getElementById("color_act").value = "";

                if (isNotNull(id)) {
                    return $.ajax({
                        url: '{{ route('get-scanned-form-cut-input') }}/' + id,
                        type: 'get',
                        dataType: 'json',
                        success: function(res) {
                            console.log(res);

                            if (typeof res === 'object' && res !== null) {
                                if (res.qty > 0) {
                                    currentScannedItem = res;

                                    document.getElementById("id_item").value = res.id_item;
                                    document.getElementById("detail_item").value = res.detail_item;
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

                return Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Item tidak ditemukan',
                    showCancelButton: false,
                    showConfirmButton: true,
                    confirmButtonText: 'Oke',
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
