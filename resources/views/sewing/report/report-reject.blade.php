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
                <i class="fa-solid fa-file-circle-exclamation"></i> Report Reject
            </h5>
        </div>
        <div class="card-body" id="report-reject-card">
            <div class="d-flex flex-wrap justify-content-between align-items-end gap-3">
                <div class="d-flex align-items-end gap-3">
                    <div>
                        <label class="form-label">Dari </label>
                        <input type="date" class="form-control" id="dateFrom" name="dateFrom" value="{{ date('Y-m-d') }}" onchange="reportRejectDatatableReload(); updateFilterOption();">
                    </div>
                    <span class="mb-2"> - </span>
                    <div>
                        <label class="form-label">Sampai </label>
                        <input type="date" class="form-control" id="dateTo" name="dateTo" value="{{ date('Y-m-d') }}" onchange="reportRejectDatatableReload(); updateFilterOption();">
                    </div>
                    <div>
                        <label class="form-label">Department</label>
                        <select class="form-select" name="department" id="department" onchange="reportRejectDatatableReload(); updateFilterOption();">
                            <option value="" selected>END-LINE</option>
                            <option value="_packing">FINISHING-LINE</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">No. WS</label>
                        <select class="form-select select2bs4base" name="base_ws" id="base_ws" onchange="updateDateFrom();">
                            <option value="">SEMUA</option>
                            @foreach ($orders as $order)
                                <option value="{{ $order }}">{{ $order }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button class="btn btn-primary" onclick="reportRejectDatatableReload(); updateFilterOption();"><i class="fa fa-search"></i></button>
                    <button class="btn btn-sb-secondary" data-bs-toggle="modal" data-bs-target="#filterModal"><i class="fa fa-filter"></i></button>
                </div>
                <button class="btn btn-success" onclick="exportExcel(this)"><i class="fa fa-file-excel"></i></button>
            </div>
            <div id="chart"></div>
            <div class="table-responsive mt-3">
                <table class="table table-bordered table" id="report-reject-table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Department</th>
                            <th>Line</th>
                            <th>Worksheet</th>
                            <th>Buyer</th>
                            <th>Style</th>
                            <th>Color</th>
                            <th>Size</th>
                            <th>Defect Name</th>
                            <th>Qty</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td class="fw-bold" colspan="9">Total</td>
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
    <div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="reportRejectModal" aria-hidden="true">
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
                            <option value="reworked">GOOD</option>
                            <option value="rejected">REJECT</option>
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
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Bersihkan <i class="fa-solid fa-broom"></i></button>
                    <button type="button" class="btn btn-success" onclick="reportRejectDatatableReload()">Simpan <i class="fa-solid fa-check"></i></button>
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

    <!-- Apex Charts -->
    <script src="{{ asset('plugins/apexcharts/apexcharts.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            updateFilterOption();
        });

        // Select2 Autofocus
        $(document).on('select2:open', () => {
            document.querySelector('.select2-search__field').focus();
        });

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
            dropdownParent: $("#report-reject-card")
        });

        // Initialize Select2BS4 Elements Filter Modal
        $('.select2bs4filter').select2({
            theme: 'bootstrap4',
            dropdownParent: $("#filterModal")
        });

        function autoBreak(label) {
            const maxLength = 5;
            const lines = [];

            if (label) {
                for (let word of label.split(" ")) {
                    if (lines.length == 0) {
                        lines.push(word);
                    } else {
                        const i = lines.length - 1
                        const line = lines[i]

                        if (line.length + 1 + word.length <= maxLength) {
                            lines[i] = `${line} ${word}`
                        } else {
                            lines.push(word)
                        }
                    }
                }
            }

            return lines;
        }

        var options = {
            chart: {
                height: 650,
                type: 'bar',
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    dataLabels: {
                        position: 'top',
                    },
                    colors: {
                        ranges: [
                            {
                                from: 500,
                                to: 99999,
                                color: '#333'
                            },{
                                from: 60,
                                to: 499,
                                color: '#d33141'
                            },{
                                from: 30,
                                to: 59,
                                color: '#ff971f'
                            },{
                                from: 0,
                                to: 15,
                                color: '#12be60'
                            }
                        ],
                        backgroundBarColors: [],
                        backgroundBarOpacity: 1,
                        backgroundBarRadius: 0,
                    },
                }
            },
            dataLabels: {
                enabled: true,
                style: {
                    colors: ['#333']
                },
                formatter: function (val, opts) {
                    return val.toLocaleString()
                },
                offsetY: -30
            },
            series: [],
            xaxis: {
                labels: {
                    show: true,
                    hideOverlappingLabels: false,
                    showDuplicates: false,
                    trim: false,
                    style: {
                        fontSize: '12px',
                        fontFamily: 'Helvetica, Arial, sans-serif',
                        fontWeight: 600,
                        cssClass: 'apexcharts-xaxis-label',
                    },
                }
            },
            title: {
                align: 'center',
                style: {
                    fontSize:  '18px',
                    fontWeight:  'bold',
                    fontFamily:  undefined,
                    color:  '#263238'
                },
            },
            noData: {
                text: 'Loading...'
            }
        }
        var chart = new ApexCharts(
            document.querySelector("#chart"),
            options
        );
        chart.render();

        // fetch top reject data function
        function getTopRejectData() {
            $.ajax({
                url: '{{ route('top-reject') }}',
                type: 'get',
                data: {
                    dateFrom: $('#dateFrom').val(),
                    dateTo: $('#dateTo').val(),
                    defect_types: $('#defect_types').val(),
                    defect_status: $('#defect_status').val(),
                    sewing_line: $('#sewing_line').val(),
                    buyer: $('#buyer').val(),
                    ws: $('#ws').val(),
                    base_ws: $('#base_ws').val(),
                    style: $('#style').val(),
                    color: $('#color').val(),
                    size: $('#size').val(),
                    department: $('#department').val()
                },
                dataType: 'json',
                success: function(res) {
                    let totalReject = 0;
                    let dataArr = [];

                    if (res && res.length > 0) {
                        res.forEach(element => {
                            totalReject += element.total_reject;
                            dataArr.push({'x' : (element.defect_types_check ? element.defect_types_check : '-'), 'y' : element.total_reject});
                        });
                    }

                    let cumulativeTotalReject = cumulativeSplitNumber(totalReject);

                    chart.updateSeries([{
                        data: dataArr,
                        name: "Total Reject"
                    }], true);

                    chart.updateOptions({
                        title: {
                            align: 'center',
                            style: {
                                fontSize:  '18px',
                                fontWeight:  'bold',
                                fontFamily:  undefined,
                                color:  '#263238'
                            },
                        },
                        subtitle: {
                            text: ['Total Reject : '+totalReject.toLocaleString()],
                            align: 'center',
                            style: {
                                fontSize:  '13px',
                                fontFamily:  undefined,
                                color:  '#263238'
                            },
                        },
                        plotOptions: {
                            bar: {
                                horizontal: false,
                                dataLabels: {
                                    position: 'top',
                                },
                                colors: {
                                    ranges: [
                                        {
                                            from: 500,
                                            to: 99999,
                                            color: '#333'
                                        },{
                                            from: 60,
                                            to: 499,
                                            color: '#d33141'
                                        },{
                                            from: 30,
                                            to: 59,
                                            color: '#ff971f'
                                        },{
                                            from: 0,
                                            to: 15,
                                            color: '#12be60'
                                        }
                                    ],
                                    backgroundBarColors: [],
                                    backgroundBarOpacity: 1,
                                    backgroundBarRadius: 0,
                                },
                            }
                        },
                    });
                }, error: function (jqXHR) {
                    let res = jqXHR.responseJSON;
                    console.error(res.message);
                    iziToast.error({
                        title: 'Error',
                        message: res.message,
                        position: 'topCenter'
                    });
                }
            });
        }

        var rejectTable = $('#report-reject-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{!! route('report-reject') !!}',
                data: function(d) {
                    d.dateFrom = $('#dateFrom').val();
                    d.dateTo = $('#dateTo').val();
                    d.defect_types = $('#defect_types').val();
                    d.defect_status = $('#defect_status').val();
                    d.sewing_line = $('#sewing_line').val();
                    d.buyer = $('#buyer').val();
                    d.ws = $('#ws').val();
                    d.base_ws = $('#base_ws').val();
                    d.style = $('#style').val();
                    d.color = $('#color').val();
                    d.size = $('#size').val();
                    d.department = $('#department').val();
                }
            },
            columns: [
                {
                    data: 'tanggal',
                    name: 'tanggal'
                },
                {
                    data: 'output_type',
                    name: 'output_type'
                },
                {
                    data: 'sewing_line',
                    name: 'sewing_line'
                },
                {
                    data: 'no_ws',
                    name: 'no_ws'
                },
                {
                    data: 'buyer',
                    name: 'buyer'
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
                    data: 'defect_types_check',
                    name: 'defect_types_check'
                },
                {
                    data: 'qty',
                    name: 'qty'
                },
            ],
            columnDefs: [
                {
                    className: "text-nowrap",
                    targets: "_all"
                },
            ],
            footerCallback: async function(row, data, start, end, display) {
                var api = this.api(),
                    data;

                $(api.column(1).footer()).html('Total');
                $(api.column(9).footer()).html("...");

                $.ajax({
                    url: '{{ route('total-reject') }}',
                    dataType: 'json',
                    dataSrc: 'data',
                    data: {
                        dateFrom: $('#dateFrom').val(),
                        dateTo: $('#dateTo').val(),
                        defect_types: $('#defect_types').val(),
                        defect_status: $('#defect_status').val(),
                        sewing_line: $('#sewing_line').val(),
                        buyer: $('#buyer').val(),
                        ws: $('#ws').val(),
                        base_ws: $('#base_ws').val(),
                        style: $('#style').val(),
                        color: $('#color').val(),
                        size: $('#size').val(),
                        department: $('#department').val()
                    },
                    success: function(response) {
                        console.log(response);
                        if (response) {
                            // Update footer by showing the total with the reference of the column index
                            $(api.column(1).footer()).html("Total");
                            $(api.column(9).footer()).html(response);
                        }
                    },
                    error: function(jqXHR) {
                        console.error(jqXHR);
                    },
                })
            },
        });

        $('#report-reject-table').DataTable().on('xhr.dt', function(e, settings, json, xhr) {
            // you can pass json.data to a chart function if useful
            getTopRejectData();
        });

        async function reportRejectDatatableReload() {
            $('#report-reject-table').DataTable().ajax.reload();

            $('#filterModal').modal('hide');
        }

        async function updateFilterOption() {
            document.getElementById('loading').classList.remove('d-none');

            await $.ajax({
                url: '{{ route('filter-reject') }}',
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
                                $('#sewing_line').append('<option value="' + value + '">' + value + '</option>');
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
                    url: '{{ route('update-date-from-reject') }}',
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
                html: `Exporting Data...`,
                didOpen: () => {
                    Swal.showLoading();
                },
                allowOutsideClick: false,
            });

            $.ajax({
                url: "{{ route('report-reject-export') }}",
                type: 'post',
                data: {
                    dateFrom: $('#dateFrom').val(),
                    dateTo: $('#dateTo').val(),
                    defect_types: $('#defect_types').val(),
                    defect_status: $('#defect_status').val(),
                    sewing_line: $('#sewing_line').val(),
                    buyer: $('#buyer').val(),
                    ws: $('#ws').val(),
                    base_ws: $('#base_ws').val(),
                    style: $('#style').val(),
                    color: $('#color').val(),
                    size: $('#size').val(),
                    department: $('#department').val()
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
                    link.download = "Report Reject " + $("#dateFrom").val() + " - " + $("#dateTo").val() + ".xlsx";
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
                }
            });
        }

        function cumulativeSplitNumber(number) {
            const part1 = Math.floor(number * 0.05);
            const part2 = Math.floor(number * 0.10);
            const part3 = Math.floor(number * 0.20);
            const part4 = number - (part1 + part2 + part3); // Adjust to get the total sum

            return [part1, part1 + part2, part1 + part2 + part3, number];
        }
    </script>
@endsection
