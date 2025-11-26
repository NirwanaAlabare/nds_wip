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
                <i class="fa-solid fa-file-circle-exclamation"></i> Report Defect
            </h5>
        </div>
        <div class="card-body" id="report-defect-card">
            <div class="d-flex flex-wrap justify-content-start align-items-end gap-3">
                <div class="d-flex align-items-end gap-3">
                    <div>
                        <label class="form-label">Dari </label>
                        <input type="date" class="form-control" id="dateFrom" name="dateFrom" value="{{ date('Y-m-d') }}" onchange="reportDefectDatatableReload(); updateFilterOption();">
                    </div>
                    <span class="mb-2"> - </span>
                    <div>
                        <label class="form-label">Sampai </label>
                        <input type="date" class="form-control" id="dateTo" name="dateTo" value="{{ date('Y-m-d') }}" onchange="reportDefectDatatableReload(); updateFilterOption();">
                    </div>
                    <div>
                        <label class="form-label">Department</label>
                        <select class="form-select" name="department" id="department" onchange="reportDefectDatatableReload(); updateFilterOption();">
                            <option value="" selected>END-LINE</option>
                            <option value="_packing">FINISHING-LINE</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">No. WS</label>
                        <select class="form-select select2bs4base" name="base_ws" id="base_ws"
                            onchange="updateDateFrom();">
                            <option value="">SEMUA</option>
                            @foreach ($orders as $order)
                                <option value="{{ $order }}">{{ $order }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button class="btn btn-primary" onclick="reportDefectDatatableReload(); updateFilterOption();"><i class="fa fa-search"></i></button>
                    <button class="btn btn-sb-secondary" data-bs-toggle="modal" data-bs-target="#filterModal"><i class="fa fa-filter"></i></button>
                </div>
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#reportDefectModal"><i class="fa fa-file-excel"></i></button>
            </div>
            <div class="table-responsive mt-3">
                <table class="table table-bordered table" id="report-defect-table">
                    <thead>
                        <tr>
                            <th>Kode Numbering</th>
                            <th>Buyer</th>
                            <th>No. WS</th>
                            <th>Style</th>
                            <th>Color</th>
                            <th>Size</th>
                            <th>Dest</th>
                            <th>Sewing Line</th>
                            <th>Defect Type</th>
                            <th>Defect Area</th>
                            <th>Status</th>
                            <th>Rework ID</th>
                            <th>External ID</th>
                            <th>External Type</th>
                            <th>External Status</th>
                            <th>External IN</th>
                            <th>External OUT</th>
                            <th>Created At</th>
                            <th>Updated At</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td class="fw-bold" colspan="18">Total</td>
                            <td class="fw-bold" id="total_data">
                                ...
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="reportDefectModal" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-sb">
                    <h1 class="modal-title fs-5" id="filterModalLabel"><i class="fa fa-filter"></i> Filter</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Defect Types</label>
                        <select class="select2bs4filter" name="defect_types[]" multiple="multiple" id="defect_types">
                            @foreach ($defectTypes as $defectType)
                                <option value="{{ $defectType->id }}">{{ $defectType->defect_type }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Defect Areas</label>
                        <select class="select2bs4filter" name="defect_areas[]" multiple="multiple" id="defect_areas">
                            @foreach ($defectAreas as $defectArea)
                                <option value="{{ $defectArea->id }}">{{ $defectArea->defect_area }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Defect Status</label>
                        <select class="select2bs4filter" name="defect_status[]" multiple="multiple" id="defect_status">
                            <option value="">SEMUA</option>
                            <option value="defect">DEFECT</option>
                            <option value="reworked">REWORKED</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Sewing Line</label>
                        <select class="select2bs4filter" name="sewing_line[]" multiple="multiple" id="sewing_line">
                            <option value="">SEMUA</option>
                            @foreach ($lines as $line)
                                <option value="{{ $line }}">{{ strtoupper(str_replace('_', ' ', $line)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Buyer</label>
                        <select class="select2bs4filter" name="buyer[]" multiple="multiple" id="buyer">
                            <option value="">Buyer</option>
                            @foreach ($suppliers as $supplier)
                                <option value="{{ $supplier }}">{{ $supplier }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">No. WS</label>
                        <select class="select2bs4filter" name="ws[]" multiple="multiple" id="ws">
                            <option value="">SEMUA</option>
                            @foreach ($orders as $order)
                                <option value="{{ $order }}">{{ $order }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Style</label>
                        <select class="select2bs4filter" name="style[]" multiple="multiple" id="style">
                            <option value="">SEMUA</option>
                            @foreach ($styles as $style)
                                <option value="{{ $style }}">{{ $style }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Color</label>
                        <select class="select2bs4filter" name="color[]" multiple="multiple" id="color">
                            <option value="">SEMUA</option>
                            @foreach ($colors as $color)
                                <option value="{{ $color }}">{{ $color }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Size</label>
                        <select class="select2bs4filter" name="size[]" multiple="multiple" id="size">
                            <option value="">SEMUA</option>
                            @foreach ($sizes as $size)
                                <option value="{{ $size }}">{{ $size }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">External Type</label>
                        <select class="select2bs4filter" name="external_type[]" multiple="multiple" id="external_type">
                            <option value="">SEMUA</option>
                            @foreach ($externalTypes as $externalType)
                                <option value="{{ $externalType }}">
                                    {{ $externalType ? strtoupper($externalType) : 'SEWING' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">External IN</label>
                        <input type="date" class="form-control" name="external_in">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">External OUT</label>
                        <input type="date" class="form-control" name="external_out">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Bersihkan <i
                            class="fa-solid fa-broom"></i></button>
                    <button type="button" class="btn btn-success" onclick="reportDefectDatatableReload()">Simpan <i
                            class="fa-solid fa-check"></i></button>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Defect -->
    <div class="modal fade" id="reportDefectModal" tabindex="-1" aria-labelledby="reportDefectModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-sb">
                    <h1 class="modal-title fs-5" id="reportDefectModalLabel">Report Defect</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label">Report Type</label>
                    <select class="select2bs4report" name="report_type[]" id="report_type" multiple="multiple">
                        <option value="defect_rate">Defect Rate</option>
                        <option value="top_defect">Defect</option>
                        <option value="top_reject">Reject</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-success" onclick="exportExcel(this)"><i
                            class="fa fa-file-excel"></i> Export</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('custom-script')
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>

    <!-- DataTables & Plugins -->
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-rowsgroup/dataTables.rowsGroup.js') }}"></script>

    <!-- Select2 -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            updateFilterOption();
        });

        // Select2 Autofocus
        // $(document).on('select2:open', () => {
        //     document.querySelector('.select2-search__field').focus();
        // });

        // Initialize Select2 Elements
        $('.select2').select2();

        // Initialize Select2BS4 Elements
        $('.select2bs4').select2({
            theme: 'bootstrap4',
            containerCssClass: 'form-control-sm rounded'
        });

        // Initialize Select2BS4 Elements Filter Modal
        $('.select2bs4base').select2({
            theme: 'bootstrap4',
            dropdownParent: $("#report-defect-card")
        });

        // Initialize Select2BS4 Elements Filter Modal
        $('.select2bs4filter').select2({
            theme: 'bootstrap4',
            dropdownParent: $("#filterModal")
        });

        $('.select2bs4report').select2({
            theme: 'bootstrap4',
            dropdownParent: $("#reportDefectModal", )
        });

        $('#report-defect-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{!! route('report-defect') !!}',
                data: function(d) {
                    d.dateFrom = $('#dateFrom').val();
                    d.dateTo = $('#dateTo').val();
                    d.defect_types = $('#defect_types').val();
                    d.defect_areas = $('#defect_areas').val();
                    d.defect_status = $('#defect_status').val();
                    d.sewing_line = $('#sewing_line').val();
                    d.buyer = $('#buyer').val();
                    d.ws = $('#ws').val();
                    d.base_ws = $('#base_ws').val();
                    d.style = $('#style').val();
                    d.color = $('#color').val();
                    d.size = $('#size').val();
                    d.external_type = $('#external_type').val();
                    d.external_in = $('#external_in').val();
                    d.external_out = $('#external_out').val();
                    d.department = $('#department').val();
                }
            },
            columns: [{
                    data: 'kode_numbering',
                    name: 'kode_numbering'
                },
                {
                    data: 'buyer',
                    name: 'buyer'
                },
                {
                    data: 'ws',
                    name: 'ws'
                },
                {
                    data: 'style',
                    name: 'style'
                },
                {
                    data: 'color',
                    name: 'color'
                },
                {
                    data: 'size',
                    name: 'size'
                },
                {
                    data: 'dest',
                    name: 'dest'
                },
                {
                    data: 'sewing_line',
                    name: 'sewing_line'
                },
                {
                    data: 'defect_type',
                    name: 'defect_type'
                },
                {
                    data: 'defect_area',
                    name: 'defect_area'
                },
                {
                    data: 'defect_status',
                    name: 'defect_status'
                },
                {
                    data: 'rework_id',
                    name: 'rework_id'
                },
                {
                    data: 'external_id',
                    name: 'external_id'
                },
                {
                    data: 'external_type',
                    name: 'external_type'
                },
                {
                    data: 'external_status',
                    name: 'external_status'
                },
                {
                    data: 'external_in',
                    name: 'external_in'
                },
                {
                    data: 'external_out',
                    name: 'external_out'
                },
                {
                    data: 'created_at',
                    name: 'created_at'
                },
                {
                    data: 'updated_at',
                    name: 'updated_at'
                }
            ],
            columnDefs: [{
                    className: "text-nowrap",
                    targets: "_all"
                },
                {
                    targets: [0],
                    render: function(data, type, row) {
                        return data ? data : "-";
                    }
                },
                {
                    targets: [11, 12, 13, 14, 15, 16],
                    render: function(data, type, row) {
                        return data ? data : "-";
                    }
                },
                {
                    targets: [17, 18],
                    render: function(data, type, row) {
                        return formatDateTime(data);
                    },
                }
            ],
            footerCallback: async function(row, data, start, end, display) {
                var api = this.api(),
                    data;

                $(api.column(16).footer()).html('Total');
                $(api.column(17).footer()).html("...");

                $.ajax({
                    url: '{{ route('total-defect') }}',
                    dataType: 'json',
                    dataSrc: 'data',
                    data: {
                        dateFrom: $('#dateFrom').val(),
                        dateTo: $('#dateTo').val(),
                        defect_types: $('#defect_types').val(),
                        defect_areas: $('#defect_areas').val(),
                        defect_status: $('#defect_status').val(),
                        sewing_line: $('#sewing_line').val(),
                        buyer: $('#buyer').val(),
                        ws: $('#ws').val(),
                        base_ws: $('#base_ws').val(),
                        style: $('#style').val(),
                        color: $('#color').val(),
                        size: $('#size').val(),
                        external_type: $('#external_type').val(),
                        external_in: $('#external_in').val(),
                        external_out: $('#external_out').val(),
                        department: $('#department').val()
                    },
                    success: function(response) {
                        console.log(response);
                        if (response) {
                            // Update footer by showing the total with the reference of the column index
                            $(api.column(17).footer()).html("Total");
                            $(api.column(18).footer()).html(response);
                        }
                    },
                    error: function(jqXHR) {
                        console.error(jqXHR);
                    },
                })
            },
        });

        async function reportDefectDatatableReload() {
            $('#report-defect-table').DataTable().ajax.reload();

            $('#filterModal').modal('hide');
        }

        async function updateFilterOption() {
            document.getElementById('loading').classList.remove('d-none');

            await $.ajax({
                url: '{{ route('filter-defect') }}',
                dataType: 'json',
                dataSrc: 'data',
                data: {
                    dateFrom: $('#dateFrom').val(),
                    dateTo: $('#dateTo').val(),
                    department: $('#department').val()
                },
                success: async function(response) {
                    document.getElementById('loading').classList.add('d-none');

                    let baseWs = $("#base_ws").val();

                    if (response) {
                        console.log(response.lines && response.lines.length > 0);
                        // lines options
                        if (response.lines && response.lines.length > 0) {
                            let lines = response.lines;
                            $('#sewing_line').empty();
                            $.each(lines, function(index, value) {
                                $('#sewing_line').append('<option value="' + value + '">' + value +
                                    '</option>');
                            });
                        }
                        // suppliers option
                        if (response.suppliers && response.suppliers.length > 0) {
                            let suppliers = response.suppliers;
                            $('#buyer').empty();
                            $.each(suppliers, function(index, value) {
                                $('#buyer').append('<option value="' + value + '">' + value +
                                    '</option>');
                            });
                        }
                        // orders option
                        if (response.orders && response.orders.length > 0) {
                            let orders = response.orders;
                            $('#ws').empty();
                            $('#base_ws').empty();
                            $('#base_ws').append('<option value="">SEMUA</option>');
                            $.each(orders, function(index, value) {
                                $('#ws').append('<option value="' + value + '">' + value +
                                    '</option>');
                                $('#base_ws').append('<option value="' + value + '">' + value +
                                    '</option>');
                            });
                            $('#base_ws').val(baseWs).trigger('change');
                        }
                        // styles option
                        if (response.styles && response.styles.length > 0) {
                            let styles = response.styles;
                            $('#style').empty();
                            $.each(styles, function(index, value) {
                                $('#style').append('<option value="' + value + '">' + value +
                                    '</option>');
                            });
                        }
                        // colors option
                        if (response.colors && response.colors.length > 0) {
                            let colors = response.colors;
                            $('#color').empty();
                            $.each(colors, function(index, value) {
                                $('#color').append('<option value="' + value + '">' + value +
                                    '</option>');
                            });
                        }
                        // sizes option
                        if (response.sizes && response.sizes.length > 0) {
                            let sizes = response.sizes;
                            $('#size').empty();
                            $.each(sizes, function(index, value) {
                                $('#size').append('<option value="' + value + '">' + value +
                                    '</option>');
                            });
                        }
                    }
                },
                error: function(jqXHR) {
                    document.getElementById('loading').classList.add('d-none');

                    console.error(jqXHR);
                },
            })
        }

        async function updateDateFrom() {
            const baseWs = $('#base_ws').val();
            const department = $('#department').val();

            if (!baseWs) return;

            document.getElementById('loading').classList.remove('d-none');

            try {
                const response = await $.ajax({
                    url: '{{ route('update-date-from-defect') }}',
                    dataType: 'json',
                    data: {
                        base_ws: baseWs,
                        department: department
                    }
                });

                document.getElementById('loading').classList.add('d-none');

                if (response && response.dateTo && response.dateTo !== $("#dateTo").val()) {
                    $("#dateTo").val(response.dateTo).trigger('change');
                }
                if (response && response.dateFrom && response.dateFrom !== $("#dateFrom").val()) {
                    $("#dateFrom").val(response.dateFrom).trigger('change');
                }
            } catch (error) {
                document.getElementById('loading').classList.add('d-none');

                console.error(error);
            }
        }

        function exportExcel(elm) {
            elm.setAttribute('disabled', 'true');
            elm.innerText = "";
            let loading = document.createElement('div');
            loading.classList.add('loading-small');
            elm.appendChild(loading);

            iziToast.info({
                title: 'Exporting...',
                message: 'Data sedang di export. Mohon tunggu...',
                position: 'topCenter'
            });

            Swal.fire({
                title: 'Please Wait...',
                html: `Exporting Data... <br><br> Total Defect : ` + (document.getElementById("total_data")
                        .innerHTML) +
                    ` <br> <div class="progress mt-3"><div class="progress-bar bg-sb" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div></div>`,
                didOpen: () => {
                    Swal.showLoading();

                    let divider = 0
                    if ($("#report_type").val().includes("defect_rate") && !$("#report_type").val().includes(
                            "top_defect")) {
                        divider = 560;
                    } else if (!$("#report_type").val().includes("defect_rate") && $("#report_type").val()
                        .includes("top_defect")) {
                        divider = 230;
                    } else if ($("#report_type").val().includes("defect_rate") && $("#report_type").val()
                        .includes("top_defect")) {
                        divider = 100;
                    }

                    if ($("#report_type").val().includes("top_reject")) {
                        divider -= 75;
                    }

                    let times = Number(document.getElementById("total_data").innerText) / divider;
                    let cumulative = Number(100 / times).round(2);
                    let percent = 0;
                    const progress = Swal.getPopup().querySelector("div.progress-bar");

                    estimatedTimeInterval = setInterval(() => {
                        if (percent < 90) {
                            percent += cumulative;
                            progress.style.width = percent + "%";
                        }
                    }, 1000);
                },
                allowOutsideClick: false,
            });

            $.ajax({
                url: "{{ route('report-defect-export') }}",
                type: 'post',
                data: {
                    dateFrom: $("#dateFrom").val(),
                    dateTo: $("#dateTo").val(),
                    sewingLine: $("#sewing_line").val(),
                    buyer: $("#buyer").val(),
                    ws: $("#ws").val(),
                    base_ws: $("#base_ws").val(),
                    style: $("#style").val(),
                    color: $("#color").val(),
                    types: $("#report_type").val(),
                    department: $("#department").val()
                },
                xhrFields: {
                    responseType: 'blob'
                },
                success: async function(res) {
                    elm.removeAttribute('disabled');
                    elm.innerText = "Export ";
                    let icon = document.createElement('i');
                    icon.classList.add('fa-solid');
                    icon.classList.add('fa-file-excel');
                    elm.appendChild(icon);

                    iziToast.success({
                        title: 'Success',
                        message: 'Data berhasil di export.',
                        position: 'topCenter'
                    });

                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        html: 'Data berhasil di export.',
                        showCancelButton: false,
                        showConfirmButton: true,
                        confirmButtonText: 'Oke',
                        confirmButtonColor: "#082149",
                    });

                    var blob = new Blob([res]);
                    var link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = "Output Defect Rate " + $("#dateFrom").val() + " - " + $("#dateTo").val() +
                        ".xlsx";
                    link.click();
                },
                error: function(jqXHR) {
                    elm.removeAttribute('disabled');
                    elm.innerText = "Export ";
                    let icon = document.createElement('i');
                    icon.classList.add('fa-solid');
                    icon.classList.add('fa-file-excel');
                    elm.appendChild(icon);

                    let res = jqXHR.responseJSON;
                    let message = '';
                    console.log(res.message);
                    for (let key in res.errors) {
                        message += res.errors[key] + ' ';
                        document.getElementById(key).classList.add('is-invalid');
                    };
                    iziToast.error({
                        title: 'Error',
                        message: message,
                        position: 'topCenter'
                    });

                    Swal.close();
                }
            });
        }
    </script>
@endsection
