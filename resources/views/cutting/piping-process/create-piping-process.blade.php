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
    <form action="{{ route("store-piping-process") }}" method="post" id="store-piping-process" onsubmit="submitForm(this, event)">
        @csrf
        <div class="card card-sb">
            <div class="card-header">
                <h5 class="card-title fw-bold">
                    Header Data
                </h5>
            </div>
            <div class="card-body">
                <input type="hidden" id="process" name="process" value="1" readonly>
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
@endsection

@section('custom-script')
    <!-- Select2 -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <!-- Page specific script -->
    <script>
        // Initial Window On Load Event
        $(document).ready(async function () {
            //Reset Form
            if (document.getElementById('store-master-piping')) {
                document.getElementById('store-master-piping').reset();

                $("#buyer_id").val(null).trigger("change");
            }

            // Select2 Prevent Step-Jump Input ( Step = WS -> Color -> Panel )
            $("#color").prop("disabled", true);
            $("#master_piping_id").prop("disabled", true);

            generateCode();
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
        document.getElementById("store-piping-process").onkeypress = function(e) {
            var key = e.charCode || e.keyCode || 0;
            if (key == 13) {
                e.preventDefault();
            }
        }

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
