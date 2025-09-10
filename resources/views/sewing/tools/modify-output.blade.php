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
            <h5><i class="fa fa-shirt"></i> Modify Output</h5>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label">Departemen</label>
                <select class="form-select select2bs4" id="dept">
                    <option value="">QC</option>
                    <option value="_packing">FINISHING</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Tanggal</label>
                <input type="date" class="form-control" id="tanggal" onchange="getMasterPlan()">
            </div>
            <div class="mb-3">
                <label class="form-label">Line</label>
                <select class="form-select select2bs4" id="line" onchange="getMasterPlan()">
                    <option value="">Select Line</option>
                    @foreach ($lines as $line)
                        <option value="{{ $line->username }}">{{ $line->username }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Master Plan</label>
                <select class="form-select select2bs4" id="master_plan" onchange="getMasterPlanOutputSize()">
                    <option value="">Select Master Plan</option>
                </select>
            </div>
            <div class="row">
                <div class="row g-3 mt-3">
                    <div class="col-md-6">
                        <a type="button" data-bs-toggle="modal" data-bs-target="#rftModal" onclick="openRftModal()">
                            <div class="card bg-rft">
                                <div class="card-body">
                                    <h5 class="card-title text-light fw-bold">
                                        RFT
                                    </h5>
                                    <br>
                                    <h5 class="text-light fw-bold" id="rft">
                                        0
                                    </h5>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-6">
                        <a type="button" data-bs-toggle="modal" data-bs-target="#defectModal" onclick="openDefectModal()">
                            <div class="card bg-defect">
                                <div class="card-body">
                                    <h5 class="card-title text-light fw-bold">
                                        DEFECT
                                    </h5>
                                    <br>
                                    <h5 class="text-light fw-bold" id="defect">
                                        0
                                    </h5>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-6">
                        <a type="button" data-bs-toggle="modal" data-bs-target="#reworkModal" onclick="openReworkModal()">
                            <div class="card bg-rework">
                                <div class="card-body">
                                    <h5 class="card-title text-light fw-bold">
                                        REWORK
                                    </h5>
                                    <br>
                                    <h5 class="text-light fw-bold" id="rework">
                                        0
                                    </h5>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-6">
                        <a type="button" data-bs-toggle="modal" data-bs-target="#rejectModal" onclick="openRejectModal()">
                            <div class="card bg-reject">
                                <div class="card-body">
                                    <h5 class="card-title text-light fw-bold">
                                        REJECT
                                    </h5>
                                    <br>
                                    <h5 class="text-light fw-bold" id="reject">
                                        0
                                    </h5>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- RFT Modal -->
    <div class="modal fade" id="rftModal" tabindex="-1" aria-labelledby="rftModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-rft text-light">
                    <h1 class="modal-title fs-5" id="rftModalLabel">RFT</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row row-gap-3">
                        <h6 class="text-center fw-bold">FROM : </h6>
                        <div class="col-md-4">
                            <label class="form-label">No. WS</label>
                            <input type="hidden" class="form-control d-none" name="rft_id_ws" id="rft_id_ws" readonly>
                            <input type="text" class="form-control" name="rft_no_ws" id="rft_no_ws" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Style</label>
                            <input type="text" class="form-control" name="rft_style" id="rft_style" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Color</label>
                            <input type="text" class="form-control" name="rft_color" id="rft_color" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Total</label>
                            <input type="text" class="form-control" name="rft_total" id="rft_total" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Size</label>
                            <select class="form-select select2bs4Rft" name="rft_size" id="rft_size">
                                <option value="">Pilih Size</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Modify Qty</label>
                            <input type="number" class="form-control" value="0" name="rft_qty" id="rft_qty">
                        </div>
                        <div class="col-md-12">
                            <hr class="border-dark my-3">
                            <h6 class="text-center fw-bold">MODIFY TO : </h6>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">No. WS</small></label>
                            <select class="form-select select2bs4Rft" name="rft_mod_id_ws" id="rft_mod_id_ws">
                                <option value="">Pilih WS</option>
                                @foreach ($orders as $order)
                                    <option value="{{ $order->id }}">{{ $order->kpno }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Style</label>
                            <select class="form-select select2bs4Rft" name="rft_mod_style" id="rft_mod_style">
                                <option value="">Pilih Style</option>
                                @foreach ($orders as $style)
                                    <option value="{{ $style->id }}">{{ $style->styleno }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Color</label>
                            <select class="form-select select2bs4Rft" name="rft_mod_color" id="rft_mod_color">
                                <option value="">Pilih Color</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Size</label>
                            <select class="form-select select2bs4Rft" name="rft_mod_size" id="rft_mod_size">
                                <option value="">Pilih Size</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">CANCEL</button>
                    <button type="button" class="btn btn-success" onclick="modifyOutput('rft_')">MODIFY</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Defect Modal -->
    <div class="modal fade" id="defectModal" tabindex="-1" aria-labelledby="defectModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-defect text-light">
                    <h1 class="modal-title fs-5" id="defectModalLabel">DEFECT</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row row-gap-3">
                        <h6 class="text-center fw-bold">FROM : </h6>
                        <div class="col-md-4">
                            <label class="form-label">No. WS</label>
                            <input type="hidden" class="form-control d-none" name="defect_id_ws" id="defect_id_ws" readonly>
                            <input type="text" class="form-control" name="defect_no_ws" id="defect_no_ws" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Style</label>
                            <input type="text" class="form-control" name="defect_style" id="defect_style" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Color</label>
                            <input type="text" class="form-control" name="defect_color" id="defect_color" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Total</label>
                            <input type="text" class="form-control" name="defect_total" id="defect_total" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Size</label>
                            <select class="form-select select2bs4Defect" name="defect_size" id="defect_size">
                                <option value="">Pilih Size</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Modify Qty</label>
                            <input type="number" class="form-control" value="0" name="defect_qty" id="defect_qty">
                        </div>
                        <div class="col-md-12">
                            <hr class="border-dark my-3">
                            <h6 class="text-center fw-bold">MODIFY TO : </h6>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">No. WS</small></label>
                            <select class="form-select select2bs4Defect" name="defect_mod_id_ws" id="defect_mod_id_ws">
                                <option value="">Pilih WS</option>
                                @foreach ($orders as $order)
                                    <option value="{{ $order->id }}">{{ $order->kpno }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Style</label>
                            <select class="form-select select2bs4Defect" name="defect_mod_style" id="defect_mod_style">
                                <option value="">Pilih Style</option>
                                @foreach ($orders as $style)
                                    <option value="{{ $style->id }}">{{ $style->styleno }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Color</label>
                            <select class="form-select select2bs4Defect" name="defect_mod_color" id="defect_mod_color">
                                <option value="">Pilih Color</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Size</label>
                            <select class="form-select select2bs4Defect" name="defect_mod_size" id="defect_mod_size">
                                <option value="">Pilih Size</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">CANCEL</button>
                    <button type="button" class="btn btn-success" onclick="modifyOutput('defect_')">MODIFY</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Rework Modal -->
    <div class="modal fade" id="reworkModal" tabindex="-1" aria-labelledby="reworkModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-rework text-light">
                    <h1 class="modal-title fs-5" id="reworkModalLabel">REWORK</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row row-gap-3">
                        <h6 class="text-center fw-bold">FROM : </h6>
                        <div class="col-md-4">
                            <label class="form-label">No. WS</label>
                            <input type="hidden" class="form-control d-none" name="rework_id_ws" id="rework_id_ws" readonly>
                            <input type="text" class="form-control" name="rework_no_ws" id="rework_no_ws" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Style</label>
                            <input type="text" class="form-control" name="rework_style" id="rework_style" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Color</label>
                            <input type="text" class="form-control" name="rework_color" id="rework_color" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Total</label>
                            <input type="text" class="form-control" name="rework_total" id="rework_total" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Size</label>
                            <select class="form-select select2bs4Rework" name="rework_size" id="rework_size">
                                <option value="">Pilih Size</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Modify Qty</label>
                            <input type="number" class="form-control" value="0" name="rework_qty" id="rework_qty">
                        </div>
                        <div class="col-md-12">
                            <hr class="border-dark my-3">
                            <h6 class="text-center fw-bold">MODIFY TO : </h6>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">No. WS</small></label>
                            <select class="form-select select2bs4Rework" name="rework_mod_id_ws" id="rework_mod_id_ws">
                                <option value="">Pilih WS</option>
                                @foreach ($orders as $order)
                                    <option value="{{ $order->id }}">{{ $order->kpno }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Style</label>
                            <select class="form-select select2bs4Rework" name="rework_mod_style" id="rework_mod_style">
                                <option value="">Pilih Style</option>
                                @foreach ($orders as $style)
                                    <option value="{{ $style->id }}">{{ $style->styleno }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Color</label>
                            <select class="form-select select2bs4Rework" name="rework_mod_color" id="rework_mod_color">
                                <option value="">Pilih Color</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Size</label>
                            <select class="form-select select2bs4Rework" name="rework_mod_size" id="rework_mod_size">
                                <option value="">Pilih Size</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">CANCEL</button>
                    <button type="button" class="btn btn-success" onclick="modifyOutput('rework_')">MODIFY</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-reject text-light">
                    <h1 class="modal-title fs-5" id="rejectModalLabel">REJECT</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row row-gap-3">
                        <h6 class="text-center fw-bold">FROM : </h6>
                        <div class="col-md-4">
                            <label class="form-label">No. WS</label>
                            <input type="hidden" class="form-control d-none" name="reject_id_ws" id="reject_id_ws" readonly>
                            <input type="text" class="form-control" name="reject_no_ws" id="reject_no_ws" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Style</label>
                            <input type="text" class="form-control" name="reject_style" id="reject_style" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Color</label>
                            <input type="text" class="form-control" name="reject_color" id="reject_color" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Total</label>
                            <input type="text" class="form-control" name="reject_total" id="reject_total" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Size</label>
                            <select class="form-select select2bs4Reject" name="reject_size" id="reject_size">
                                <option value="">Pilih Size</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Modify Qty</label>
                            <input type="number" class="form-control" value="0" name="reject_qty" id="reject_qty">
                        </div>
                        <div class="col-md-12">
                            <hr class="border-dark my-3">
                            <h6 class="text-center fw-bold">MODIFY TO : </h6>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">No. WS</small></label>
                            <select class="form-select select2bs4Reject" name="reject_mod_id_ws" id="reject_mod_id_ws">
                                <option value="">Pilih WS</option>
                                @foreach ($orders as $order)
                                    <option value="{{ $order->id }}">{{ $order->kpno }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Style</label>
                            <select class="form-select select2bs4Reject" name="reject_mod_style" id="reject_mod_style">
                                <option value="">Pilih Style</option>
                                @foreach ($orders as $style)
                                    <option value="{{ $style->id }}">{{ $style->styleno }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Color</label>
                            <select class="form-select select2bs4Reject" name="reject_mod_color" id="reject_mod_color">
                                <option value="">Pilih Color</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Size</label>
                            <select class="form-select select2bs4Reject" name="reject_mod_size" id="reject_mod_size">
                                <option value="">Pilih Size</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">CANCEL</button>
                    <button type="button" class="btn btn-success" onclick="modifyOutput('reject_')">MODIFY</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('custom-script')
    <!-- DataTables & Plugins -->
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>

    <!-- Select2 -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        $('.select2').select2()
        $('.select2bs4').select2({
            theme: 'bootstrap4',
        })
        $('.select2bs4Rft').select2({
            theme: 'bootstrap4',
            dropdownParent: $("#rftModal")
        })
        $('.select2bs4Defect').select2({
            theme: 'bootstrap4',
            dropdownParent: $("#defectModal")
        })
        $('.select2bs4Rework').select2({
            theme: 'bootstrap4',
            dropdownParent: $("#reworkModal")
        })
        $('.select2bs4Reject').select2({
            theme: 'bootstrap4',
            dropdownParent: $("#rejectModal")
        })

        $(document).ready(function () {
            $("#tanggal").val("").trigger("change");
            $("#line").val("").trigger("change");
            $("#master_plan").val("").trigger("change");

            clearForm("rft_");
            clearForm("defect_");
            clearForm("rework_");
            clearForm("reject_");
        });

        function clearForm(prefix) {
            $("#"+prefix+"size").val("").trigger("change");
            $("#"+prefix+"qty").val("").trigger("change");
            $("#"+prefix+"mod_id_ws").val("").trigger("change");
            $("#"+prefix+"mod_style").val("").trigger("change");
            $("#"+prefix+"mod_color").val("").trigger("change");
            $("#"+prefix+"mod_size").val("").trigger("change");
        }

        function getMasterPlan() {
            if ($("#tanggal").val() && $("#line").val()) {
                document.getElementById("loading").classList.remove("d-none");

                document.getElementById("master_plan").innerHTML = "<option value=''>Select Master Plan</option>";

                $.ajax({
                    type: "get",
                    url: "{{ route('get-master-plan') }}",
                    data: {
                        tanggal: $("#tanggal").val(),
                        line: $("#line").val(),
                    },
                    dataType: "json",
                    success: function (response) {
                        if (response.length > 0) {
                            let select = document.getElementById("master_plan");

                            response.forEach(item => {
                                let option = document.createElement("option");
                                option.value = item.id;
                                option.innerText = item.no_ws+" | "+item.style+" | "+item.color+(item.cancel == 'Y' ? ' | CANCELLED' : '');

                                select.appendChild(option);
                            });
                        }

                        document.getElementById("loading").classList.add("d-none");
                    },
                    error: function (jqXHR) {
                        console.error(jqXHR);

                        document.getElementById("loading").classList.add("d-none");
                    }
                });
            }
        }

        var outputs = [];
        function getMasterPlanOutputSize() {
            document.getElementById("loading").classList.remove("d-none");

            outputs = [];
            document.getElementById("rft").innerText = 0;
            document.getElementById("defect").innerText = 0;
            document.getElementById("rework").innerText = 0;
            document.getElementById("reject").innerText = 0;

            $.ajax({
                type: "get",
                url: "{{ route('get-master-plan-output-size') }}",
                data: {
                    id: $("#master_plan").val()
                },
                dataType: "json",
                success: function (response) {
                    document.getElementById("loading").classList.add("d-none");

                    if (response.length > 0) {
                        outputs = response;

                        // Total
                        let totals = response.reduce((acc, item) => {
                            acc.rft    += Number(item.rft);
                            acc.defect += Number(item.defect);
                            acc.rework += Number(item.rework);
                            acc.reject += Number(item.reject);

                            return acc;
                        }, { rft: 0, defect: 0, rework: 0, reject: 0});

                        document.getElementById("rft").innerText = totals.rft;
                        document.getElementById("defect").innerText = totals.defect;
                        document.getElementById("rework").innerText = totals.rework;
                        document.getElementById("reject").innerText = totals.reject;
                    }
                },
                error: function (jqXHR) {
                    document.getElementById("loading").classList.add("d-none");
                }
            });
        }

        // RFT
        function openRftModal() {
            let currentOutputs = outputs.filter((item) => {
                return Number(item.rft) > 0
            });

            if (currentOutputs && currentOutputs.length > 0) {
                document.getElementById("rft_id_ws").value = outputs[0]['id_ws'];
                document.getElementById("rft_no_ws").value = outputs[0]['ws'];
                document.getElementById("rft_style").value = outputs[0]['style'];
                document.getElementById("rft_color").value = outputs[0]['color'];

                let totalRft = 0;
                let selectSize = document.getElementById("rft_size");
                selectSize.innerHTML = "<option value=''>Pilih Size</option>";
                currentOutputs.forEach(item => {
                    let option = document.createElement("option");
                    option.value = item.so_det_id;
                    option.innerText = item.size+(item.dest && item.dest != "-" ? item.dest : "");

                    selectSize.appendChild(option);

                    totalRft += Number(item.rft);
                });

                document.getElementById("rft_total").value = totalRft;
            }
        }

        // Defect
        function openDefectModal() {
            let currentOutputs = outputs.filter((item) => {
                return Number(item.defect) > 0
            });

            if (currentOutputs && currentOutputs.length > 0) {
                document.getElementById("defect_id_ws").value = outputs[0]['id_ws'];
                document.getElementById("defect_no_ws").value = outputs[0]['ws'];
                document.getElementById("defect_style").value = outputs[0]['style'];
                document.getElementById("defect_color").value = outputs[0]['color'];

                let totalDefect = 0;
                let selectSize = document.getElementById("defect_size");
                selectSize.innerHTML = "<option value=''>Pilih Size</option>";
                currentOutputs.forEach(item => {
                    let option = document.createElement("option");
                    option.value = item.so_det_id;
                    option.innerText = item.size+(item.dest && item.dest != "-" ? item.dest : "");

                    selectSize.appendChild(option);

                    totalDefect += Number(item.defect);
                });

                document.getElementById("defect_total").value = totalDefect;
            }
        }

        // Rework
        function openReworkModal() {
            let currentOutputs = outputs.filter((item) => {
                return Number(item.rework) > 0
            });

            if (currentOutputs && currentOutputs.length > 0) {
                document.getElementById("rework_id_ws").value = outputs[0]['id_ws'];
                document.getElementById("rework_no_ws").value = outputs[0]['ws'];
                document.getElementById("rework_style").value = outputs[0]['style'];
                document.getElementById("rework_color").value = outputs[0]['color'];

                let totalRework = 0;
                let selectSize = document.getElementById("rework_size");
                selectSize.innerHTML = "<option value=''>Pilih Size</option>";
                currentOutputs.forEach(item => {
                    let option = document.createElement("option");
                    option.value = item.so_det_id;
                    option.innerText = item.size+(item.dest && item.dest != "-" ? item.dest : "");

                    selectSize.appendChild(option);

                    totalRft += Number(item.rft);
                });

                document.getElementById("rework_total").value = totalRft;
            }
        }

        // Reject
        function openRejectModal() {
            let currentOutputs = outputs.filter((item) => {
                return Number(item.reject) > 0
            });

            if (currentOutputs && currentOutputs.length > 0) {
                document.getElementById("reject_id_ws").value = outputs[0]['id_ws'];
                document.getElementById("reject_no_ws").value = outputs[0]['ws'];
                document.getElementById("reject_style").value = outputs[0]['style'];
                document.getElementById("reject_color").value = outputs[0]['color'];

                let totalReject = 0;
                let selectSize = document.getElementById("reject_size");
                selectSize.innerHTML = "<option value=''>Pilih Size</option>";
                currentOutputs.forEach(item => {
                    let option = document.createElement("option");
                    option.value = item.so_det_id;
                    option.innerText = item.size+(item.dest && item.dest != "-" ? item.dest : "");

                    selectSize.appendChild(option);

                    totalReject += Number(item.reject);
                });

                document.getElementById("reject_total").value = totalReject;
            }
        }

        // RFT
        $("#rft_mod_id_ws").on("change", async () => {
            await updateWs("ws", "rft_mod_");
            await updateColorList("rft_mod_");
        })

        $("#rft_mod_style").on("change", async () => {
            await updateWs("style", "rft_mod_");
            await updateColorList("rft_mod_");
        })

        // DEFECT
        $("#defect_mod_id_ws").on("change", async () => {
            await updateWs("ws", "defect_mod_");
            await updateColorList("defect_mod_");
        })

        $("#defect_mod_style").on("change", async () => {
            await updateWs("style", "defect_mod_");
            await updateColorList("defect_mod_");
        })

        // REJECT
        $("#rework_mod_id_ws").on("change", async () => {
            await updateWs("ws", "rework_mod_");
            await updateColorList("rework_mod_");
        })

        $("#rework_mod_style").on("change", async () => {
            await updateWs("style", "rework_mod_");
            await updateColorList("rework_mod_");
        })

        // REJECT
        $("#reject_mod_id_ws").on("change", async () => {
            await updateWs("ws", "reject_mod_");
            await updateColorList("reject_mod_");
        })

        $("#reject_mod_style").on("change", async () => {
            await updateWs("style", "reject_mod_");
            await updateColorList("reject_mod_");
        })

        // Update WS Select Option
        function updateWs(currentVal, prefix) {
            if (currentVal && ($("#"+prefix+"style").val() != $("#"+prefix+"id_ws").val())) {
                if (currentVal == "ws") {
                    $("#"+prefix+"style").val($("#"+prefix+"id_ws").val()).trigger("change");
                }

                if (currentVal == "style") {
                    $("#"+prefix+"id_ws").val($("#"+prefix+"style").val()).trigger("change");
                }
            }
        }

        // Update Color Select Option
        function updateColorList(prefix) {
            document.getElementById(prefix+'color').value = null;

            return $.ajax({
                url: '{{ route("get-colors") }}',
                type: 'get',
                data: {
                    act_costing_id: $('#'+prefix+'id_ws').val(),
                },
                success: function (res) {
                    if (res) {
                        let select = document.getElementById(prefix+'color');
                        select.innerHTML = "";

                        let latestVal = null;
                        for(let i = 0; i < res.length; i++) {
                            let option = document.createElement("option");
                            option.setAttribute("value", res[i].color);
                            option.innerHTML = res[i].color;
                            select.appendChild(option);
                        }

                        $("#"+prefix+"color").val(res[0].color).trigger("change");

                        // Open this step
                        $("#"+prefix+"color").prop("disabled", false);
                    }
                },
            });
        }

        // RFT
        $("#rft_mod_color").on("change", () => {
            updateSizeList("rft_mod_")
        })

        // DEFECT
        $("#defect_mod_color").on("change", () => {
            updateSizeList("defect_mod_")
        })

        // REWORK
        $("#rework_mod_color").on("change", () => {
            updateSizeList("rework_mod_")
        })

        // REJECT
        $("#reject_mod_color").on("change", () => {
            updateSizeList("reject_mod_")
        })

        // Update Color Select Option
        function updateSizeList(prefix) {
            document.getElementById(prefix+'size').value = null;

            return $.ajax({
                url: '{{ route("get-sizes") }}',
                type: 'get',
                data: {
                    act_costing_id: $('#'+prefix+'id_ws').val(),
                    color: $('#'+prefix+'color').val(),
                },
                success: function (res) {
                    if (res) {
                        console.log(res, res[0]);
                        let select = document.getElementById(prefix+'size');
                        select.innerHTML = "";

                        let latestVal = null;
                        for(let i = 0; i < res.length; i++) {
                            let option = document.createElement("option");
                            option.setAttribute("value", res[i].so_det_id);
                            option.innerHTML = res[i].size+(res[i].dest && res[i].dest != '-' ? ' - '+res[i].dest : '');
                            option.setAttribute("size", res[i].size);
                            select.appendChild(option);
                        }

                        $("#"+prefix+"size").val(res[0].so_det_id).trigger("change");

                        // Open this step
                        $("#"+prefix+"size").prop("disabled", false);
                    }
                },
            });
        }

        // RFT
        $("#rft_size").on("change", () => {
            updateTotalQty("rft_");
        })

        // DEFECT
        $("#defect_size").on("change", () => {
            updateTotalQty("defect_");
        })

        // REWORK
        $("#rework_size").on("change", () => {
            updateTotalQty("rework_");
        })

        // REJECT
        $("#reject_size").on("change", () => {
            updateTotalQty("reject_");
        })

        function updateTotalQty(prefix) {
            let currentOutputs = [];
            if ($('#'+prefix+'size').val()) {
                currentOutputs = outputs.filter((item) => {
                    return item.so_det_id == $('#'+prefix+'size').val();
                });
            } else {
                currentOutputs = outputs;
            }

            if (currentOutputs && currentOutputs.length > 0) {
                let totals = currentOutputs.reduce((acc, item) => {
                    acc.rft    += Number(item.rft);
                    acc.defect += Number(item.defect);
                    acc.rework += Number(item.rework);
                    acc.reject += Number(item.reject);

                    return acc;
                }, { rft: 0, defect: 0, rework: 0, reject: 0});

                document.getElementById("rft_total").value = totals.rft;
                document.getElementById("defect_total").value = totals.defect;
                document.getElementById("rework_total").value = totals.rework;
                document.getElementById("reject_total").value = totals.reject;
            }
        }

        // RFT
        $("#rft_qty").on("keyup", () => {
            restrictQty("rft_");
        })

        // DEFECT
        $("#defect_qty").on("keyup", () => {
            restrictQty("defect_");
        })

        // REWORK
        $("#rework_qty").on("keyup", () => {
            restrictQty("rework_");
        })

        // REJECT
        $("#reject_qty").on("keyup", () => {
            restrictQty("reject_");
        })

        function restrictQty(prefix) {
            let qty = document.getElementById(prefix+'qty');
            let total = document.getElementById(prefix+'total');

            if (Number(qty.value) > Number(total.value)) {
                qty.value = total.value;

                iziToast.warning({
                    title: 'Warning',
                    message: 'Maksimal ' + total.value,
                    position: 'topCenter'
                });
            }
        }

        function preModifyOutput(prefix) {
            let qty = document.getElementById(prefix+"qty").value;
            let noWs = document.getElementById(prefix+"no_ws").value;
            let color = document.getElementById(prefix+"color").value;
            let size = document.getElementById(prefix+"size").value;

            let modNoWs = document.getElementById(prefix+"mod_no_ws").value;
            let modColor = document.getElementById(prefix+"mod_color").value;
            let modSize = document.getElementById(prefix+"mod_size").value;

            Swal.fire({
                icon: "info",
                title: "Konfirmasi",
                html: "Ubah "+qty+" Output " + prefix.replace("_", "").toUpperCase() + " " +(dept ? dept.replace("_", "").toUpperCase() : "QC")+ "<br> Dari "+noWs+" - "+color+" - "+size+"<br> Ke "+modNoWs+" - "+modColor+" - "+modSize,
                showDenyButton: true,
                showCancelButton: false,
                confirmButtonText: "Lanjut",
                denyButtonText: "Batal"
            }).then((result) => {
                if (result.isConfirmed) {
                    modifyOutput(prefix);
                }
            })
        }

        function modifyOutput(prefix) {
            let dept = document.getElementById("dept").value;
            let tanggal = document.getElementById("tanggal").value;
            let line = document.getElementById("line").value;
            let masterPlanId = document.getElementById("master_plan").value;
            let soDetId = document.getElementById(prefix+"size").value;
            let qty = document.getElementById(prefix+"qty").value;
            let modSoDetId = document.getElementById(prefix+"mod_size").value;

            if (tanggal && line && masterPlanId && soDetId && modSoDetId && (qty > 0)) {
                document.getElementById("loading").classList.remove("d-none");

                $.ajax({
                    type: "post",
                    url: "{{ route('modify-output-action') }}",
                    data: {
                        tanggal: tanggal,
                        line: line,
                        master_plan_id: masterPlanId,
                        so_det_id: soDetId,
                        mod_so_det_id: modSoDetId,
                        qty: qty,
                        type: prefix,
                        dept: dept,
                    },
                    dataType: "json",
                    success: function (response) {
                        document.getElementById("loading").classList.add("d-none");

                        if (response.status == 200) {
                            Swal.fire({
                                icon: "success",
                                html: response.message,
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: "error",
                                html: response.message,
                            });
                        }
                    },
                    error: function (jqXHR) {
                        document.getElementById("loading").classList.add("d-none");

                        console.error(jqXHR);
                    }
                });
            } else {
                Swal.fire({
                    icon: "warning",
                    html: "Harap isi form dengan lengkap.",
                });
            }
        }
    </script>
@endsection
