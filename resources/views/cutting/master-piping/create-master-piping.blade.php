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
        <h5 class="fw-bold text-sb"><i class="fa fa-plus fa-sm"></i> Tambah Master Piping</h5>
        <a href="{{ route('master-piping') }}" class="btn btn-primary btn-sm px-1 py-1"><i class="fas fa-reply"></i> Kembali ke Master Piping</a>
    </div>
    <form action="{{ route('store-master-piping') }}" method="post" id="store-master-piping" onsubmit="submitForm(this, event)">
        @csrf
        <div class="card card-sb">
            <div class="card-header">
                <h5 class="card-title fw-bold">
                    Detail Data
                </h5>
            </div>
            <div class="card-body">
                <div class="row justify-content-center align-items-end g-3">
                    <div class="col-6 col-md-4">
                        <div class="mb-1">
                            <div class="form-group mb-0">
                                <label>Buyer</label>
                                <select class="form-select select2bs4" id="buyer_id" name="buyer_id" style="width: 100%;">
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
                                <select class="form-select select2bs4" id="act_costing_id" name="act_costing_id" style="width: 100%;">
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
                                <select class="form-select select2bs4" id="color" name="color" style="width: 100%;">
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
                                <input type="text" class="form-control" id="part" name="part" value="">
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="mb-1">
                            <div class="form-group mb-0">
                                <label>Panjang</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="panjang" name="panjang" value="">
                                    <select class="form-select" id="unit" name="unit">
                                        <option value="cm">CM</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="d-flex justify-content-between">
                            <a href="{{ route("master-piping") }}" class="btn btn-danger my-3 fw-bold"><i class="fa fa-times"></i> BATAL</a>
                            <button type="submit" class="btn btn-success my-3 fw-bold"><i class="fa fa-save"></i> SIMPAN</button>
                        </div>
                    </div>
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
        $(document).ready(async function () {
            //Reset Form
            if (document.getElementById('store-master-piping')) {
                document.getElementById('store-master-piping').reset();

                $("#buyer_id").val(null).trigger("change");
            }

            // Select2 Prevent Step-Jump Input ( Step = WS -> Color -> Panel )
            $("#color").prop("disabled", true);
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

        // Update Order Select Option Based on Order WS
        function updateOrderList() {
            document.getElementById('loading').classList.remove("d-none");

            document.getElementById('act_costing_id').value = null;
            document.getElementById('act_costing_id').innerHTML = null;

            return $.ajax({
                url: '{{ route("get-orders") }}',
                type: 'get',
                data: {
                    buyer: $('#buyer').val(),
                },
                success: function (res) {
                    if (res) {
                        if (res.length > 0) {
                            let selectElement = document.getElementById("act_costing_id");

                            for (let i = 0; i < res.length; i++) {
                                let newOptionElement = document.createElement("option");
                                newOptionElement.value = res[i].id_act_cost;
                                newOptionElement.innerHTML = res[i].ws;

                                selectElement.prepend(newOptionElement);
                            }

                            $("#act_costing_id").val(res[res.length-1].id_act_cost).trigger("change");

                            document.getElementById('loading').classList.add('d-none');
                        }
                    }
                },
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

        // Update Color Select Option Based on Order WS
        function updateColorList() {
            document.getElementById('loading').classList.remove("d-none");

            document.getElementById('color').value = null;
            document.getElementById('color').innerHTML = null;

            return $.ajax({
                url: '{{ route("get-colors") }}',
                type: 'get',
                data: {
                    act_costing_id: $('#act_costing_id').val(),
                },
                success: function (res) {
                    if (res) {

                        if (res.length > 0) {
                            // Update this step
                            let selectElement = document.getElementById("color");

                            for (let i = 0; i < res.length; i++) {
                                let newOptionElement = document.createElement("option");
                                newOptionElement.value = res[i].color;
                                newOptionElement.innerHTML = res[i].color;

                                selectElement.prepend(newOptionElement);
                            }

                            $("#color").val(res[res.length-1].id_act_cost).trigger("change");

                            // Open this step
                            $("#color").prop("disabled", false);

                            document.getElementById('loading').classList.add('d-none');
                        }
                    }
                },
            });
        }

        // Prevent Form Submit When Pressing Enter
        document.getElementById("store-master-piping").onkeypress = function(e) {
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
        }
    </script>
@endsection
