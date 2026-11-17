@extends('layouts.index')

@section('custom-link')
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@endsection

@php
    $rejectTypes = [
        ['prefix' => 'in_', 'key' => 'reject_in', 'title' => 'REJECT IN', 'bg' => 'bg-reject', 'col' => 'col-md-4'],
        ['prefix' => 'out_', 'key' => 'reject_out', 'title' => 'REJECT OUT', 'bg' => 'bg-defect', 'col' => 'col-md-4'],
        ['prefix' => 'reworked_', 'key' => 'reject_reworked', 'title' => 'REJECT REWORKED', 'bg' => 'bg-rework', 'col' => 'col-md-4'],
    ];

    $undoNotes = [
        'in_' => 'Reject In beserta detail & posisi akan dihapus.',
        'out_' => 'Reject Out detail (dan gudang stok terkait) akan dihapus, kembali ke REJECT IN. Header Reject Out yang kosong ikut dihapus.',
    ];
@endphp

@section('content')
    <div class="card card-sb">
        <div class="card-header">
            <h5><i class="fa fa-solid fa-ban"></i> Modify Reject In Out</h5>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label">Departemen</label>
                <select class="form-select select2bs4" id="dept" onchange="getMasterPlanRejectInOutSize()">
                    <option value="qc">QC</option>
                    <option value="packing">FINISHING</option>
                    <option value="finishing_proses">FINISHING PROSES</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Tanggal</label>
                <input type="date" class="form-control" id="tanggal" onchange="getMasterPlan()" min="{{ periodeClosingMinDate() }}">
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
                <select class="form-select select2bs4" id="master_plan" onchange="getMasterPlanRejectInOutSize()">
                    <option value="">Select Master Plan</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Grade</label>
                <select class="form-select select2bs4" id="grade" onchange="getMasterPlanRejectInOutSize()">
                    <option value="">Semua Grade</option>
                    @foreach ($grades as $grade)
                        <option value="{{ $grade }}">{{ $grade }}</option>
                    @endforeach
                    <option value="-">Tanpa Grade</option>
                </select>
            </div>
            <div class="row g-3 mt-3">
                @foreach ($rejectTypes as $rejectType)
                    <div class="{{ $rejectType['col'] }}">
                        <a type="button" data-bs-toggle="modal" data-bs-target="#{{ $rejectType['key'] }}Modal" onclick="openRejectModal('{{ $rejectType['prefix'] }}')">
                            <div class="card {{ $rejectType['bg'] }}">
                                <div class="card-body">
                                    <h5 class="card-title text-light fw-bold">
                                        {{ $rejectType['title'] }}
                                    </h5>
                                    <br>
                                    <h5 class="text-light fw-bold" id="{{ $rejectType['key'] }}">
                                        0
                                    </h5>
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    @foreach ($rejectTypes as $modal)
        <!-- {{ $modal['title'] }} Modal -->
        <div class="modal fade" id="{{ $modal['key'] }}Modal" tabindex="-1" aria-labelledby="{{ $modal['key'] }}ModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header {{ $modal['bg'] }} text-light">
                        <h1 class="modal-title fs-5" id="{{ $modal['key'] }}ModalLabel">{{ $modal['title'] }}</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row row-gap-3">
                            <h6 class="text-center fw-bold">FROM : </h6>
                            <div class="col-md-4">
                                <label class="form-label">No. WS</label>
                                <input type="hidden" class="form-control d-none" id="{{ $modal['prefix'] }}id_ws" readonly>
                                <input type="text" class="form-control" id="{{ $modal['prefix'] }}no_ws" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Style</label>
                                <input type="text" class="form-control" id="{{ $modal['prefix'] }}style" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Color</label>
                                <input type="text" class="form-control" id="{{ $modal['prefix'] }}color" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Total</label>
                                <input type="text" class="form-control" id="{{ $modal['prefix'] }}total" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Size</label>
                                <select class="form-select select2bs4Modal" data-modal="#{{ $modal['key'] }}Modal" id="{{ $modal['prefix'] }}size">
                                    <option value="">Pilih Size</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Modify Qty</label>
                                <input type="number" class="form-control" value="0" min="0" id="{{ $modal['prefix'] }}qty">
                            </div>
                            <div class="col-md-12">
                                <hr class="border-dark my-3">
                                <h6 class="text-center fw-bold">MODIFY TO : </h6>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">No. WS</label>
                                <select class="form-select select2bs4Modal" data-modal="#{{ $modal['key'] }}Modal" id="{{ $modal['prefix'] }}mod_id_ws">
                                    <option value="">Pilih WS</option>
                                    @foreach ($orders as $order)
                                        <option value="{{ $order->id }}">{{ $order->kpno }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Style</label>
                                <select class="form-select select2bs4Modal" data-modal="#{{ $modal['key'] }}Modal" id="{{ $modal['prefix'] }}mod_style">
                                    <option value="">Pilih Style</option>
                                    @foreach ($orders as $style)
                                        <option value="{{ $style->id }}">{{ $style->styleno }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Color</label>
                                <select class="form-select select2bs4Modal" data-modal="#{{ $modal['key'] }}Modal" id="{{ $modal['prefix'] }}mod_color">
                                    <option value="">Pilih Color</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Size</label>
                                <select class="form-select select2bs4Modal" data-modal="#{{ $modal['key'] }}Modal" id="{{ $modal['prefix'] }}mod_size">
                                    <option value="">Pilih Size</option>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <small class="text-danger">* MODIFY juga mengubah size reject asal (output_rejects) dan defect asal dari garment tersebut.</small>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">CANCEL</button>
                        <button type="button" class="btn btn-success" onclick="modifyReject('{{ $modal['prefix'] }}')">MODIFY</button>
                        @if ($modal['prefix'] != 'reworked_')
                            <button type="button" class="btn btn-dark" onclick="undoReject('{{ $modal['prefix'] }}')">UNDO</button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endsection

@section('custom-script')
    <!-- Select2 -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        // prefix => { key di response, label }
        const rejectTypes = {
            @foreach ($rejectTypes as $rejectType)
                "{{ $rejectType['prefix'] }}": { key: "{{ $rejectType['key'] }}", label: "{{ $rejectType['title'] }}" },
            @endforeach
        };

        const undoNotes = @json($undoNotes);

        $('.select2bs4').select2({
            theme: 'bootstrap4',
        });

        $('.select2bs4Modal').each(function () {
            $(this).select2({
                theme: 'bootstrap4',
                dropdownParent: $($(this).data("modal"))
            });
        });

        $(document).ready(function () {
            $("#tanggal").val("").trigger("change");
            $("#line").val("").trigger("change");
            $("#master_plan").val("").trigger("change");
            $("#grade").val("").trigger("change");

            Object.keys(rejectTypes).forEach(prefix => clearForm(prefix));
        });

        function clearForm(prefix) {
            $("#"+prefix+"size").val("").trigger("change");
            $("#"+prefix+"qty").val("");
            $("#"+prefix+"mod_id_ws").val("").trigger("change");
            $("#"+prefix+"mod_style").val("").trigger("change");
            $("#"+prefix+"mod_color").val("").trigger("change");
            $("#"+prefix+"mod_size").val("").trigger("change");
        }

        function getMasterPlan() {
            if ($("#tanggal").val() && $("#line").val()) {
                document.getElementById("loading").classList.remove("d-none");

                document.getElementById("master_plan").innerHTML = "<option value=''>Select Master Plan</option>";
                getMasterPlanRejectInOutSize();

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

        // Response: [{ id_ws, ws, style, color, so_det_id, size, dest, wip, rft, defect, rework, reject }]
        var outputs = [];
        function getMasterPlanRejectInOutSize() {
            outputs = [];
            Object.values(rejectTypes).forEach(type => document.getElementById(type.key).innerText = 0);

            if (!$("#master_plan").val()) {
                return;
            }

            document.getElementById("loading").classList.remove("d-none");

            $.ajax({
                type: "get",
                url: "{{ route('get-master-plan-reject-in-out-size') }}",
                data: {
                    id: $("#master_plan").val(),
                    department: $("#dept").val(),
                    grade: $("#grade").val(),
                },
                dataType: "json",
                success: function (response) {
                    document.getElementById("loading").classList.add("d-none");

                    if (response && response.length > 0) {
                        outputs = response;

                        let totals = sumOutputs(response);

                        Object.values(rejectTypes).forEach(type => document.getElementById(type.key).innerText = totals[type.key]);
                    }
                },
                error: function (jqXHR) {
                    console.error(jqXHR);

                    document.getElementById("loading").classList.add("d-none");
                }
            });
        }

        function sumOutputs(items) {
            let totals = {};
            Object.values(rejectTypes).forEach(type => totals[type.key] = 0);

            items.forEach(item => {
                Object.values(rejectTypes).forEach(type => totals[type.key] += Number(item[type.key]));
            });

            return totals;
        }

        function openRejectModal(prefix) {
            let key = rejectTypes[prefix].key;

            let currentOutputs = outputs.filter((item) => Number(item[key]) > 0);

            let selectSize = document.getElementById(prefix+"size");
            selectSize.innerHTML = "<option value=''>Pilih Size</option>";

            if (currentOutputs.length > 0) {
                document.getElementById(prefix+"id_ws").value = currentOutputs[0]['id_ws'];
                document.getElementById(prefix+"no_ws").value = currentOutputs[0]['ws'];
                document.getElementById(prefix+"style").value = currentOutputs[0]['style'];
                document.getElementById(prefix+"color").value = currentOutputs[0]['color'];

                currentOutputs.forEach(item => {
                    let option = document.createElement("option");
                    option.value = item.so_det_id;
                    option.innerText = item.size+(item.dest && item.dest != "-" ? " - "+item.dest : "");

                    selectSize.appendChild(option);
                });

                document.getElementById(prefix+"total").value = sumOutputs(currentOutputs)[key];
            } else {
                document.getElementById(prefix+"id_ws").value = "";
                document.getElementById(prefix+"no_ws").value = "";
                document.getElementById(prefix+"style").value = "";
                document.getElementById(prefix+"color").value = "";
                document.getElementById(prefix+"total").value = 0;
            }
        }

        // Modify To : WS <-> Style sync, then Color -> Size
        Object.keys(rejectTypes).forEach(prefix => {
            let modPrefix = prefix+"mod_";

            $("#"+modPrefix+"id_ws").on("change", async () => {
                await updateWs("ws", modPrefix);
                await updateColorList(modPrefix);
            });

            $("#"+modPrefix+"style").on("change", async () => {
                await updateWs("style", modPrefix);
                await updateColorList(modPrefix);
            });

            $("#"+modPrefix+"color").on("change", () => {
                updateSizeList(modPrefix);
            });

            $("#"+prefix+"size").on("change", () => {
                updateTotalQty(prefix);
            });

            $("#"+prefix+"qty").on("keyup", () => {
                restrictQty(prefix);
            });
        });

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
            if (!$('#'+prefix+'id_ws').val()) {
                return;
            }

            document.getElementById(prefix+'color').value = null;

            return $.ajax({
                url: '{{ route("get-colors") }}',
                type: 'get',
                data: {
                    act_costing_id: $('#'+prefix+'id_ws').val(),
                },
                success: function (res) {
                    if (res && res.length > 0) {
                        let select = document.getElementById(prefix+'color');
                        select.innerHTML = "";

                        for (let i = 0; i < res.length; i++) {
                            let option = document.createElement("option");
                            option.setAttribute("value", res[i].color);
                            option.innerHTML = res[i].color;
                            select.appendChild(option);
                        }

                        $("#"+prefix+"color").val(res[0].color).trigger("change");
                        $("#"+prefix+"color").prop("disabled", false);
                    }
                },
            });
        }

        // Update Size Select Option
        function updateSizeList(prefix) {
            if (!$('#'+prefix+'id_ws').val() || !$('#'+prefix+'color').val()) {
                return;
            }

            document.getElementById(prefix+'size').value = null;

            return $.ajax({
                url: '{{ route("get-sizes") }}',
                type: 'get',
                data: {
                    act_costing_id: $('#'+prefix+'id_ws').val(),
                    color: $('#'+prefix+'color').val(),
                },
                success: function (res) {
                    if (res && res.length > 0) {
                        let select = document.getElementById(prefix+'size');
                        select.innerHTML = "";

                        for (let i = 0; i < res.length; i++) {
                            let option = document.createElement("option");
                            option.setAttribute("value", res[i].so_det_id);
                            option.innerHTML = res[i].size+(res[i].dest && res[i].dest != '-' ? ' - '+res[i].dest : '');
                            option.setAttribute("size", res[i].size);
                            select.appendChild(option);
                        }

                        $("#"+prefix+"size").val(res[0].so_det_id).trigger("change");
                        $("#"+prefix+"size").prop("disabled", false);
                    }
                },
            });
        }

        function updateTotalQty(prefix) {
            let key = rejectTypes[prefix].key;
            let soDetId = $('#'+prefix+'size').val();

            let currentOutputs = soDetId ? outputs.filter((item) => item.so_det_id == soDetId) : outputs;

            document.getElementById(prefix+"total").value = currentOutputs.length > 0 ? sumOutputs(currentOutputs)[key] : 0;

            restrictQty(prefix);
        }

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

        function getFormData(prefix) {
            return {
                tanggal: document.getElementById("tanggal").value,
                line: document.getElementById("line").value,
                master_plan_id: document.getElementById("master_plan").value,
                dept: document.getElementById("dept").value,
                grade: document.getElementById("grade").value,
                so_det_id: document.getElementById(prefix+"size").value,
                qty: document.getElementById(prefix+"qty").value,
                mod_so_det_id: document.getElementById(prefix+"mod_size").value,
                type: prefix,
            };
        }

        function fromText(prefix) {
            return $("#"+prefix+"no_ws").val()+" - "+$("#"+prefix+"color").val()+" - "+$("#"+prefix+"size option:selected").text();
        }

        function submitReject(data, confirmHtml) {
            Swal.fire({
                icon: "info",
                title: "Konfirmasi",
                html: confirmHtml,
                showDenyButton: true,
                showCancelButton: false,
                confirmButtonText: "Lanjut",
                denyButtonText: "Batal"
            }).then((result) => {
                if (!result.isConfirmed) {
                    return;
                }

                document.getElementById("loading").classList.remove("d-none");

                $.ajax({
                    type: "post",
                    url: "{{ route('modify-reject-in-out-action') }}",
                    data: data,
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

                        Swal.fire({
                            icon: "error",
                            html: "Terjadi kesalahan.",
                        });
                    }
                });
            });
        }

        function modifyReject(prefix) {
            let data = getFormData(prefix);

            if (!(data.tanggal && data.line && data.master_plan_id && data.so_det_id && data.mod_so_det_id && Number(data.qty) > 0)) {
                Swal.fire({
                    icon: "warning",
                    html: "Harap isi form dengan lengkap.",
                });

                return;
            }

            let to = $("#"+prefix+"mod_id_ws option:selected").text()+" - "+$("#"+prefix+"mod_color").val()+" - "+$("#"+prefix+"mod_size option:selected").text();

            submitReject(data, "Ubah "+data.qty+" "+rejectTypes[prefix].label+" ("+$("#dept option:selected").text()+" - "+$("#grade option:selected").text()+")<br> Dari "+fromText(prefix)+"<br> Ke "+to+"<br><small class='text-danger'>"+(data.dept == "finishing_proses" ? "Finishing Proses: size QC RFT & Secondary garment tersebut ikut berubah." : "Size reject & defect asal garment tersebut ikut berubah.")+"</small>");
        }

        function undoReject(prefix) {
            let data = getFormData(prefix);
            delete data.mod_so_det_id;
            data.action = "undo";

            if (!(data.tanggal && data.line && data.master_plan_id && data.so_det_id && Number(data.qty) > 0)) {
                Swal.fire({
                    icon: "warning",
                    html: "Harap isi form dengan lengkap.",
                });

                return;
            }

            submitReject(data, "Undo "+data.qty+" "+rejectTypes[prefix].label+" ("+$("#dept option:selected").text()+" - "+$("#grade option:selected").text()+")<br> Dari "+fromText(prefix)+"<br><small>"+undoNotes[prefix]+"</small>");
        }
    </script>
@endsection
