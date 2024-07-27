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
            <h5 class="card-title fw-bold mb-0"><i class="fa fa-plus"></i> Tambah Cutting Plan</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('store-cut-plan-output') }}" method="post" id="store-cut-plan-output" onsubmit="submitForm(this, event)">
                <div class="row mb-3">
                    <div class="col-md-3 mb-3">
                        <label class="form-label"><small>Tanggal</small></label>
                        <input type="date" class="form-control" id="tgl_plan" name="tgl_plan" value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label"><small>No. Meja</small></label>
                        <select class="form-select select2bs4" id="no_meja" name="no_meja">
                            <option value="" selected>Pilih Meja</option>
                            @foreach ($mejas as $meja)
                                <option value="{{ $meja->id }}">{{ strtoupper($meja->name) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label"><small>No. WS</small></label>
                        <select class="form-select select2bs4" id="id_ws" name="id_ws">
                            <option value="" selected>Pilih WS</option>
                            @foreach ($orderList as $order)
                                <option value="{{ $order->id }}">{{ $order->kpno }}</option>
                            @endforeach
                        </select>
                        <input type="hidden" class="form-control" id="ws" name="ws">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label"><small>Buyer</small></label>
                        <input type="text" class="form-control" id="buyer" name="buyer" value="" readonly>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label"><small>Style</small></label>
                        <input type="text" class="form-control" id="style" name="style" value="" readonly>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label"><small>Color</small></label>
                        <select class="form-select select2bs4" id="color" name="color">
                            <option value="" selected>Pilih Color</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label"><small>Panel</small></label>
                        <select class="form-select select2bs4" id="panel" name="panel">
                            <option value="" selected>Pilih Panel</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label"><small>Order Qty</small></label>
                        <input type="number" class="form-control" id="order_qty" name="order_qty" readonly>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label"><small>Target Shift 1</small></label>
                        <input type="number" class="form-control" onchange="calculateTarget()" onkeyup="calculateTarget()" id="target_1" name="target_1">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label"><small>Pending Shift 1</small></label>
                        <input type="number" class="form-control" onchange="calculateTarget()" onkeyup="calculateTarget()" id="pending_1" name="pending_1">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label"><small>Target Shift 2</small></label>
                        <input type="number" class="form-control" onchange="calculateTarget()" onkeyup="calculateTarget()" id="target_2" name="target_2">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label"><small>Pending Shift 2</small></label>
                        <input type="number" class="form-control" onchange="calculateTarget()" onkeyup="calculateTarget()" id="pending_2" name="pending_2">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label"><small>Total Target</small></label>
                        <input type="number" class="form-control" id="total_target" name="total_target" readonly>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label"><small>Material Cons.</small></label>
                        <input type="number" class="form-control" onchange="calculateTarget()" onkeyup="calculateTarget()" id="cons" name="cons" step="0.001">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label"><small>Material Need</small></label>
                        <input type="number" class="form-control" id="need" name="need" step="0.0001">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label"><small>Material Unit</small></label>
                        <select class="form-select select2bs4" id="unit" name="unit">
                            <option value="meter" selected>METER</option>
                            <option value="yard">YARD</option>
                            <option value="kgm">KGM</option>
                        </select>
                    </div>
                </div>
                <div class="d-flex justify-content-between">
                    <a href="{{ route('cut-plan-output') }}" class="btn btn-primary fw-bold"><i class="fa fa-reply"></i> KEMBALI</a>
                    <button type="submit" class="btn btn-success fw-bold"><i class="fa fa-save"></i> SIMPAN</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('custom-script')
    <!-- DataTables  & Plugins -->
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <!-- Select2 -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <!-- Page specific script -->
    <script>
        //Initialize Select2 Elements
        $('.select2').select2();

        //Initialize Select2 Elements
        $('.select2bs4').select2({
            theme: 'bootstrap4'
        });

        $(document).on('select2:open', () => {
            document.querySelector('.select2-search__field').focus();
        });

        document.addEventListener("DOMContentLoaded", () => {
            let oneWeeksBefore = new Date(new Date().setDate(new Date().getDate() - 7));
            let oneWeeksBeforeDate = ("0" + oneWeeksBefore.getDate()).slice(-2);
            let oneWeeksBeforeMonth = ("0" + (oneWeeksBefore.getMonth() + 1)).slice(-2);
            let oneWeeksBeforeYear = oneWeeksBefore.getFullYear();
            let oneWeeksBeforeFull = oneWeeksBeforeYear + '-' + oneWeeksBeforeMonth + '-' + oneWeeksBeforeDate;

            $("#date").val(oneWeeksBeforeFull).trigger("change");

            $("#id_ws").val("").trigger("change");
            $("#ws").val("");
            $("#buyer").val("");
            $("#style").val("");
            $("#color").prop("disabled", true);
            $("#panel").prop("disabled", true);
            $("#order_qty").val("");

            $("#unit").val("meter").trigger("change");
        });

        $("#id_ws").on("change", async function() {
            updateOrderInfo();
            updateColorList();
        });

        $("#color").on("change", async function() {
            updatePanelList();
        });

        $("#panel").on("change", async function() {
            getNumber();
        });

        // Update Order Information Based on Order WS and Order Color
        function updateOrderInfo() {
            return $.ajax({
                url: '{{ route("get-general-order") }}',
                type: 'get',
                data: {
                    act_costing_id: $('#id_ws').val(),
                    color: $('#color').val(),
                },
                dataType: 'json',
                success: function (res) {
                    if (res) {
                        document.getElementById('ws').value = res.kpno;
                        document.getElementById('buyer').value = res.buyer;
                        document.getElementById('style').value = res.styleno;
                    }
                },
            });
        }

        // Get WS cons and qty
        function getNumber() {
            document.getElementById('cons').value = null;
            document.getElementById('order_qty').value = null;
            return $.ajax({
                url: ' {{ route("get-general-number") }}',
                type: 'get',
                dataType: 'json',
                data: {
                    act_costing_id: $('#id_ws').val(),
                    color: $('#color').val(),
                    panel: $('#panel').val()
                },
                success: function (res) {
                    if (res) {
                        document.getElementById('cons').value = res.cons_ws;
                        document.getElementById('order_qty').value = res.order_qty;

                        $('#cons').trigger('change');
                    }
                }
            });
        }

        // Update Color Select Option Based on Order WS
        function updateColorList() {
            return $.ajax({
                url: '{{ route("get-general-colors") }}',
                type: 'get',
                data: {
                    act_costing_id: $('#id_ws').val(),
                },
                success: function (res) {
                    let color = document.getElementById('color');

                    if (res && res.length > 0) {
                        color.innerHTML = null;

                        // Default Option
                        let initOption = document.createElement('option');
                        initOption.innerHTML = "Pilih Color";
                        initOption.setAttribute('value', '');

                        color.append(initOption);

                        // Colors Option
                        res.forEach(item => {
                            let option = document.createElement('option');
                            option.innerHTML = item.color;
                            option.setAttribute('value', item.color);

                            color.append(option);
                        });

                        // Reset next step
                        document.getElementById('panel').innerHTML = null;

                        if ($("#id_ws").val() != '') {
                            console.log("1231");
                            // Open this step
                            $("#color").prop("disabled", false);

                            // Close next step
                            $("#panel").prop("disabled", true);

                            // Default Value
                            $("#color").val("").trigger("change");

                            // Reset order information
                            document.getElementById('cons').value = null;
                        } else {
                            console.log("1232");
                            $("#color").val("").trigger("change");

                            $("#color").prop("disabled", true);
                        }
                    } else {
                        console.log("123");
                        $("#color").val("").trigger("change");

                        $("#color").prop("disabled", true);
                    }
                },
            });
        }

        // Update Panel Select Option Based on Order WS and Color WS
        function updatePanelList() {
            return $.ajax({
                url: '{{ route("get-general-panels") }}',
                type: 'get',
                data: {
                    act_costing_id: $('#id_ws').val(),
                    color: $('#color').val(),
                },
                success: function (res) {
                    let panel = document.getElementById('panel');

                    if (res && res.length > 0) {
                        // Update this step
                        panel.innerHTML = res;

                        if ($('#color').val() != '') {
                            // Open this step
                            $("#panel").prop("disabled", false);

                            // Default value
                            $("#panel").val("").trigger("change");

                            // Reset order information
                            document.getElementById('cons').value = null;
                        } else {
                            $("#panel").val("").trigger("change")

                            $("#panel").prop("disabled", true);
                        }
                    } else {
                        $("#panel").val("").trigger("change")

                        $("#panel").prop("disabled", true);
                    }
                },
            });
        }

        // Calculate Total
        function calculateTarget() {
            let target1 = $("#target_1").val() ? Number($("#target_1").val()) : 0;
            let pending1 = $("#pending_1").val() ? Number($("#pending_1").val()) : 0;
            let target2 = $("#target_2").val() ? Number($("#target_2").val()) : 0;
            let pending2 = $("#pending_2").val() ? Number($("#pending_2").val()) : 0;

            let totalTarget = target1 + pending1 + target2 + pending2;

            let cons = $("#cons").val() ? $("#cons").val() : 0;

            document.getElementById('total_target').value = Number(totalTarget);

            document.getElementById('need').value = Number(totalTarget * cons).round(3);
        }
    </script>
@endsection
