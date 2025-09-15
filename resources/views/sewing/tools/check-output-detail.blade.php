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
            <h5 class="card-title">
                <i class="fa fa-solid fa-info-circle fa-sm"></i> Check Output Detail
            </h5>
        </div>
        <div class="card-body">
            <div class="row justify-content-start align-items-start g-3 mb-3">
                <div class="col-md-12">
                    <h6 class="text-sb fw-bold">ORDER INFO</h6>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Buyer</label>
                    <select class="form-select select2bs4" name="buyer" id="buyer">
                        <option value=""></option>
                        @foreach ($buyers as $buyer)
                            <option value="{{ $buyer->buyer }}">{{ $buyer->buyer }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">WS</label>
                    <select class="form-select select2bs4" name="ws" id="ws">
                        <option value=""></option>
                        @foreach ($orders as $order)
                            <option value="{{ $order->id }}">{{ $order->ws }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Style</label>
                    <select class="form-select select2bs4" name="style" id="style">
                        <option value=""></option>
                        @foreach ($orders as $order)
                            <option value="{{ $order->id }}">{{ $order->style }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Color</label>
                    <select class="form-select select2bs4" name="color" id="color">
                        <option value=""></option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Size</label>
                    <select class="form-select select2bs4" multiple="multiple" name="size[]" id="size">
                    </select>
                </div>
                <div class="col-md-12">
                    <hr style="border: 0.1px solid #5a5a5a;">
                    <h6 class="text-sb fw-bold">LOADING</h6>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tanggal Loading Awal</label>
                    <input type="date" class="form-control" name="tanggal_loading_awal" id="tanggal_loading_awal">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tanggal Loading Akhir</label>
                    <input type="date" class="form-control" name="tanggal_loading_akhir" id="tanggal_loading_akhir">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Line Loading</label>
                    <select class="form-select select2bs4" name="line_loading" id="line_loading">
                        <option value=""></option>
                        @foreach ($lines as $line)
                            <option value="{{ $line->username }}">{{ $line->username }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tanggal Plan Awal</label>
                    <input type="date" class="form-control" name="tanggal_plan_awal" id="tanggal_plan_awal">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tanggal Plan Akhir</label>
                    <input type="date" class="form-control" name="tanggal_plan_akhir" id="tanggal_plan_akhir">
                </div>
                <div class="col-md-12">
                    <hr style="border: 0.1px solid #5a5a5a;">
                    <h6 class="text-sb fw-bold">SEWING</h6>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tanggal Output Awal</label>
                    <input type="date" class="form-control" name="tanggal_output_awal" id="tanggal_output_awal">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tanggal Output Akhir</label>
                    <input type="date" class="form-control" name="tanggal_output_akhir" id="tanggal_output_akhir">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Output Line</label>
                    <select class="form-select select2bs4" name="line_output" id="line_output">
                        <option value=""></option>
                        @foreach ($lines as $line)
                            <option value="{{ $line->username }}">{{ $line->username }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select class="form-select select2bs4" multiple="multiple" name="status_output[]" id="status_output">
                        <option value="rft">RFT</option>
                        <option value="defect">DEFECT</option>
                        <option value="reworked">REWORK</option>
                        <option value="rejected">DEFECT-REJECT</option>
                        <option value="reject">REJECT</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Defect Type</label>
                    <select class="form-select select2bs4" multiple="multiple" name="defect_output[]" id="defect_output">
                        @foreach ($defectTypes as $defectType)
                            <option value="{{ $defectType->id }}">{{ $defectType->defect_type }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Defect Allocation</label>
                    <select class="form-select select2bs4" multiple="multiple" name="allocation_output[]" id="allocation_output">
                        <option value="sewing">SEWING</option>
                        <option value="mending">MENDING</option>
                        <option value="spotcleaning">SPOTCLEANING</option>
                    </select>
                </div>
                <div class="col-md-12">
                    <hr style="border: 0.1px solid #5a5a5a;">
                    <h6 class="text-sb fw-bold">FINISHING</h6>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tanggal Finishing Awal</label>
                    <input type="date" class="form-control" name="tanggal_packing_awal" id="tanggal_packing_awal">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tanggal Finishing Akhir</label>
                    <input type="date" class="form-control" name="tanggal_packing_akhir" id="tanggal_packing_akhir">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Output Finishing</label>
                    <select class="form-select select2bs4" name="line_packing" id="line_packing">
                        <option value=""></option>
                        @foreach ($lines as $line)
                            <option value="{{ $line->username }}">{{ $line->username }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select class="form-select select2bs4" multiple="multiple" name="status_packing[]" id="status_packing">
                        <option value="rft">RFT</option>
                        <option value="defect">DEFECT</option>
                        <option value="reworked">REWORK</option>
                        <option value="rejected">DEFECT-REJECT</option>
                        <option value="reject">REJECT</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Defect Type</label>
                    <select class="form-select select2bs4" multiple="multiple" name="defect_packing[]" id="defect_packing">
                        @foreach ($defectTypes as $defectType)
                            <option value="{{ $defectType->id }}">{{ $defectType->defect_type }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Defect Allocation</label>
                    <select class="form-select select2bs4" multiple="multiple" name="allocation_packing[]" id="allocation_packing">
                        <option value="sewing">SEWING</option>
                        <option value="mending">MENDING</option>
                        <option value="spotcleaning">SPOTCLEANING</option>
                    </select>
                </div>
                <div class="col-md-12">
                    <hr style="border: 0.1px solid #5a5a5a;">
                </div>
                <div class="col-md-12">
                    <h6>More Filter : </h6>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="back_date">
                                <label class="form-check-label" for="back_date">
                                    Back-date input
                                </label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="missmatch_code">
                                <label class="form-check-label" for="missmatch_code">
                                    Missmatch Code
                                </label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="crossline_output">
                                <label class="form-check-label" for="crossline_output">
                                    Cross-line Output
                                </label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="crossline_loading">
                                <label class="form-check-label" for="crossline_loading">
                                    Cross-line Loading
                                </label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="back_date_packing">
                                <label class="form-check-label" for="back_date_packing">
                                    Back-date input (Finishing)
                                </label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="missmatch_code_packing">
                                <label class="form-check-label" for="missmatch_code_packing">
                                    Missmatch Code (Finishing)
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="accordion" id="accordionExample">
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                            Numbering
                        </button>
                    </h2>
                    <div id="collapseOne" class="accordion-collapse collapse show" data-bs-parent="#accordionExample">
                        <div class="accordion-body">
                            <label class="form-label">Kode Numbering :</label>
                            <textarea class="form-control" name="kode" id="kode" cols="30" rows="10"></textarea>
                            <div class="form-text">Contoh : <br>&nbsp;&nbsp;&nbsp;<b> {{ date("Y") }}_1_1</b><br>&nbsp;&nbsp;&nbsp;<b> {{ date("Y") }}_1_2</b><br>&nbsp;&nbsp;&nbsp;<b> {{ date("Y") }}_1_3</b></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-footer border-1">
            <button class="btn btn-sb btn-block" onclick="listTableReload()"><i class="fa fa-solid fa-search"></i> Search</button>
        </div>
    </div>
    <div class="card">
        <div class="card-header bg-sb-secondary">
            <h5 class="card-title">
                <i class="fas fa-list fa-sm"></i> List Data
            </h5>
        </div>
        <div class="card-body">
            <button class="btn btn-sm btn-success float-end mb-3" onclick="exportExcel()"><i class="fa fa-file-excel"></i> Export</button>
            <div class="table-responsive">
                <table class="table table-bordered table-sm" id="list-table">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Buyer</th>
                            <th>WS</th>
                            <th>Style</th>
                            <th>Color</th>
                            <th>Size</th>
                            <th>Tanggal Loading</th>
                            <th>Line Loading</th>
                            <th>Tanggal Plan</th>
                            <th>Tanggal Sewing</th>
                            <th>Line Sewing</th>
                            <th>Status Sewing</th>
                            <th>Defect Sewing</th>
                            <th>Alokasi Sewing</th>
                            <th>Tanggal Finishing</th>
                            <th>Line Finishing</th>
                            <th>Status Finishing</th>
                            <th>Defect Finishing</th>
                            <th>Alokasi Finishing</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
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
        theme: 'bootstrap4'
    })

    // WS
    $("#ws").on("change", () => {
        if ($("#style").val() != $("#ws").val()) {
            $("#style").val($("#ws").val()).trigger("change");
        }
    });

    // Style
    $("#style").on("change", () => {
        if ($("#ws").val() != $("#style").val()) {
            $("#ws").val($("#style").val()).trigger("change");
        }

        updateColorList();
    });

    // Style
    $("#color").on("change", () => {
        updateSizeList();
    });

    // Update Color Select Option Based on Order WS
    function updateColorList() {
        document.getElementById("loading").classList.remove("d-none");

        document.getElementById('color').innerHTML = null;
        document.getElementById('color').value = null;

        return $.ajax({
            url: '{{ route("get-colors") }}',
            type: 'get',
            data: {
                act_costing_id: $('#ws').val(),
            },
            success: function (res) {
                if (res && res.length > 0) {
                    // Update this step
                    let select = document.getElementById("color");

                    select.innerHTML = "";

                    let initialOption = document.createElement("option");
                    initialOption.value = "";
                    initialOption.innerHTML = "ALL";
                    select.appendChild(initialOption);

                    for (let i=0; i < res.length; i++) {
                        let newOption = document.createElement("option");
                        newOption.value = res[i].color;
                        newOption.innerHTML = res[i].color;

                        select.appendChild(newOption);
                    }

                    select.removeAttribute("disabled");

                    $("#color").val(res[0].color).trigger("change");
                } else {
                    $("#color").val(null).trigger("change");
                }

                document.getElementById("loading").classList.add("d-none");
            },
        });
    }

    // Update Size Select Option Based on Order WS
    function updateSizeList() {
        document.getElementById("loading").classList.remove("d-none");

        document.getElementById('size').innerHTML = null;
        document.getElementById('size').value = null;

        return $.ajax({
            url: '{{ route("get-sizes") }}',
            type: 'get',
            data: {
                act_costing_id: $('#ws').val(),
                color: $('#color').val(),
            },
            success: function (res) {
                if (res && res.length > 0) {
                    // Update this step
                    let select = document.getElementById("size");

                    select.innerHTML = "";

                    for (let i=0; i < res.length; i++) {
                        let newOption = document.createElement("option");
                        newOption.value = res[i].so_det_id;
                        newOption.innerHTML = res[i].size;

                        select.appendChild(newOption);
                    }

                    select.removeAttribute("disabled");

                    $("#size").val(res[0].size).trigger("change");
                } else {
                    $("#size").val(null).trigger("change");
                }

                document.getElementById("loading").classList.add("d-none");
            },
        });
    }

    let listTable = $("#list-table").DataTable({
        ordering: false,
        processing: true,
        serverSide: true,
        ajax: {
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '{{ route('check-output-detail-list') }}',
            dataType: 'json',
            dataSrc: 'data',
            scrollY: '400px',
            data: function(d) {
                d.buyer = $('#buyer').val();
                d.ws = $('#ws').val();
                d.style = $('#style option:selected').text();
                d.color = $('#color').val();
                d.size = $('#size').val();
                d.tanggal_loading_awal = $('#tanggal_loading_awal').val();
                d.tanggal_loading_akhir = $('#tanggal_loading_akhir').val();
                d.line_loading = $('#line_loading').val();
                d.tanggal_plan_awal = $('#tanggal_plan_awal').val();
                d.tanggal_plan_akhir = $('#tanggal_plan_akhir').val();
                d.tanggal_output_awal = $('#tanggal_output_awal').val();
                d.tanggal_output_akhir = $('#tanggal_output_akhir').val();
                d.line_output = $('#line_output').val();
                d.status_output = $('#status_output').val();
                d.defect_output = $('#defect_output').val();
                d.allocation_output = $('#allocation_output').val();
                d.tanggal_packing_awal = $('#tanggal_packing_awal').val();
                d.tanggal_packing_akhir = $('#tanggal_packing_akhir').val();
                d.line_packing = $('#line_packing').val();
                d.status_packing = $('#status_packing').val();
                d.defect_packing = $('#defect_packing').val();
                d.allocation_packing = $('#allocation_packing').val();
                d.back_date = $('#back_date').is(':checked') ? true : null;
                d.back_date_packing = $('#back_date_packing').is(':checked') ? true : null;
                d.missmatch_code = $('#missmatch_code').is(':checked') ? true : null;
                d.missmatch_code_packing = $('#missmatch_code_packing').is(':checked') ? true : null;
                d.crossline_output = $('#crossline_output').is(':checked') ? true : null;
                d.crossline_loading = $('#crossline_loading').is(':checked') ? true : null;
                d.kode = $('#kode').val();
            },
        },
        columns: [
            {
                data: "kode"
            },
            {
                data: "buyer"
            },
            {
                data: "ws"
            },
            {
                data: "style"
            },
            {
                data: "color"
            },
            {
                data: "size"
            },
            {
                data: "tanggal_loading"
            },
            {
                data: "line_loading"
            },
            {
                data: "tanggal_plan"
            },
            {
                data: "tanggal_output"
            },
            {
                data: "line_output"
            },
            {
                data: "status_output"
            },
            {
                data: "defect_output"
            },
            {
                data: "allocation_output"
            },
            {
                data: "tanggal_output_packing"
            },
            {
                data: "line_output_packing"
            },
            {
                data: "status_output_packing"
            },
            {
                data: "defect_output_packing"
            },
            {
                data: "allocation_output_packing"
            }
        ],
        columnDefs: [
            {
                targets: "_all",
                className: "text-nowrap",
                render: (data, type, row, meta) => data
            },
        ]
    });

    function listTableReload() {
        listTable.ajax.reload();
    }

    async function exportExcel() {
        Swal.fire({
            title: "Exporting",
            html: "Please Wait...",
            timerProgressBar: true,
            didOpen: () => {
                Swal.showLoading();
            },
        });

        await $.ajax({
            url: "{{ route("check-output-detail-export") }}",
            type: "post",
            data: {
                buyer : $('#buyer').val(),
                ws : $('#ws').val(),
                style : $('#style option:selected').text(),
                color : $('#color').val(),
                size : $('#size').val(),
                tanggal_loading_awal : $('#tanggal_loading_awal').val(),
                tanggal_loading_akhir : $('#tanggal_loading_akhir').val(),
                line_loading : $('#line_loading').val(),
                tanggal_plan_awal : $('#tanggal_plan_awal').val(),
                tanggal_plan_akhir : $('#tanggal_plan_akhir').val(),
                tanggal_output_awal : $('#tanggal_output_awal').val(),
                tanggal_output_akhir : $('#tanggal_output_akhir').val(),
                line_output : $('#line_output').val(),
                status_output : $('#status_output').val(),
                defect_output : $('#defect_output').val(),
                allocation_output : $('#allocation_output').val(),
                tanggal_packing_awal : $('#tanggal_packing_awal').val(),
                tanggal_packing_akhir : $('#tanggal_packing_akhir').val(),
                line_packing : $('#line_packing').val(),
                status_packing : $('#status_packing').val(),
                defect_packing : $('#defect_packing').val(),
                allocation_packing : $('#allocation_packing').val(),
                back_date : $('#back_date').is(':checked') ? true : null,
                back_date_packing : $('#back_date_packing').is(':checked') ? true : null,
                missmatch_code : $('#missmatch_code').is(':checked') ? true : null,
                missmatch_code_packing : $('#missmatch_code_packing').is(':checked') ? true : null,
                crossline_output : $('#crossline_output').is(':checked') ? true : null,
                crossline_loading : $('#crossline_loading').is(':checked') ? true : null,
                kode : $('#kode').val()
            },
            xhrFields: { responseType : 'blob' },
            success: function (res) {
                Swal.close();

                iziToast.success({
                    title: 'Success',
                    message: 'Success',
                    position: 'topCenter'
                });

                var blob = new Blob([res]);
                var link = document.createElement('a');
                link.href = window.URL.createObjectURL(blob);
                link.download = "Detail Number.xlsx";
                link.click();
            },
            error: function (jqXHR) {
                console.error(jqXHR);
            }
        });

        Swal.close();
    }
</script>
@endsection
