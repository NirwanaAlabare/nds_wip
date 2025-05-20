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
    <div class="card">
        <div class="card-header bg-sb text-light">
            <h5 class="card-title fw-bold mb-0"><i class="fa-solid fa-list-check fa-sm"></i> Summary Loading</h5>
        </div>
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-end">
                <div class="d-flex align-items-end gap-3 mb-3">
                    <div>
                        <label class="form-label"><small>Tanggal Awal</small></label>
                        <input type="date" class="form-control form-control-sm" id="dateFrom" name="dateFrom" value="{{ date('Y-m-d') }}" onchange="datatableLoadingLineReload(); updateFilter();">
                    </div>
                    <div>
                        <label class="form-label"><small>Tanggal Akhir</small></label>
                        <input type="date" class="form-control form-control-sm" id="dateTo" name="dateTo" value="{{ date('Y-m-d') }}" onchange="datatableLoadingLineReload(); updateFilter();">
                    </div>
                    <div>
                        <button class="btn btn-primary btn-sm" onclick="datatableLoadingLineReload()"><i class="fa fa-search"></i></button>
                    </div>
                </div>
                <div class="d-flex align-items-end gap-3 mb-3">
                    <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#filterModal"><i class="fa fa-filter"></i></button>
                    <button class="btn btn-success btn-sm" onclick="exportExcel(this);"><i class="fa fa-file-excel fa-sm"></i> Excel</button>
                </div>
            </div>
            <div class="table-responsive">
                <table id="datatable-loading-line" class="table table-bordered table w-100">
                    <thead>
                        <tr>
                            <th>Tanggal Loading</th>
                            <th>Line</th>
                            <th>No. WS</th>
                            <th>Style</th>
                            <th>Color</th>
                            <th>Size</th>
                            <th>Loading Qty</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="6" class="text-end">TOTAL</th>
                            <th id="total-loading-summary">0</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Filter Modal -->
    <div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-sb text-light">
                    <h1 class="modal-title fs-5" id="filterModalLabel"><i class="fa fa-filter"></i> Filter</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Line</label>
                                <select class="form-select select2bs4filter" name="filter_line[]" id="filter_line" multiple="multiple">
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="mb-3">
                                <label class="form-label fw-bold">No. WS</label>
                                <select class="form-select select2bs4filter" name="filter_ws[]" id="filter_ws" multiple="multiple">
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Style</label>
                                <select class="form-select select2bs4filter" name="filter_style[]" id="filter_style" multiple="multiple">
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Color</label>
                                <select class="form-select select2bs4filter" name="filter_color[]" id="filter_color" multiple="multiple">
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Size</label>
                                <select class="form-select select2bs4filter" name="filter_size[]" id="filter_size" multiple="multiple">
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-sb" onclick="datatableLoadingLineReload()" data-bs-dismiss="modal">Simpan <i class="fa fa-check"></i></button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('custom-script')
    <!-- DataTables  & Plugins -->
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-rowsgroup/dataTables.rowsGroup.js') }}"></script>

    <!-- Select2 -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>

    <script>
        $('.select2bs4filter').select2({
            theme: 'bootstrap4',
            dropdownParent: $("#filterModal")
        });

        $(document).ready(function() {
            updateFilter();
        });

        var datatableLoadingLineParameter = ['tanggal_loading', 'nama_line', 'act_costing_ws', 'style', 'color', 'size', 'loading_qty']

        let datatableLoadingLine = $("#datatable-loading-line").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            pageLength: 25,
            ajax: {
                url: '{{ route('summary-loading') }}',
                data: function (d) {
                    d.dateFrom = $("#dateFrom").val();
                    d.dateTo = $("#dateTo").val();
                    d.filter_line = $('#filter_line').val();
                    d.filter_ws = $('#filter_ws').val();
                    d.filter_style = $('#filter_style').val();
                    d.filter_color = $('#filter_color').val();
                    d.filter_size = $('#filter_size').val();
                    d.size_filter = $('#size_filter').val();
                }
            },
            columns: [
                {
                    data: 'tanggal_loading'
                },
                {
                    data: 'nama_line'
                },
                {
                    data: 'act_costing_ws',
                },
                {
                    data: 'style',
                },
                {
                    data: 'color'
                },
                {
                    data: 'size'
                },
                {
                    data: 'loading_qty'
                },
            ],
            columnDefs: [
                {
                    targets: "_all",
                    className: 'align-middle text-nowrap'
                },
                {
                    targets: [6],
                    render: (data, type, row, meta) => {
                        return Number(data).toLocaleString('id-ID')
                    }
                }
            ],
            rowsGroup: [
                // Always the array (!) of the column-selectors in specified order to which rows groupping is applied
                // (column-selector could be any of specified in https://datatables.net/reference/type/column-selector)
                0,
                1,
            ],
            drawCallback: function (settings) {
                getTotalSummaryLoading();

                $('#size_filter').select2({
                    theme: 'bootstrap4',
                });
            }
        });

        function datatableLoadingLineReload() {
            datatableLoadingLine.ajax.reload();
        }

        $('#datatable-loading-line thead tr').clone(true).appendTo('#datatable-loading-line thead');
        $('#datatable-loading-line thead tr:eq(1) th').each(function(i) {
            if (i < 6) {
                var title = $(this).text();

                if (datatableLoadingLineParameter[i] == "size") {
                    $(this).html('<select class="form-select" id="'+datatableLoadingLineParameter[i]+'_filter" multiple="multiple"></select>');
                } else {
                    $(this).html('<input type="text" class="form-control" id="'+datatableLoadingLineParameter[i]+'_filter" />');

                    $('input', this).on('keyup', function() {
                        if (datatableLoadingLine.column(i).search() !== this.value) {
                            datatableLoadingLine
                                .column(i)
                                .search(this.value)
                                .draw();
                        }
                    });
                }
            } else {
                $(this).html('');
            }
        });

        function getTotalSummaryLoading() {
            $("#total-loading-summary").html("Calculating...");
            $.ajax({
                url: '{{ route('total-summary-loading') }}',
                type: 'get',
                dataType: 'json',
                data: {
                    dateFrom: $("#dateFrom").val(),
                    dateTo: $("#dateTo").val(),
                    tanggal_loading: $("#tanggal_loading_filter").val(),
                    nama_line: $("#nama_line_filter").val(),
                    act_costing_ws: $("#act_costing_ws_filter").val(),
                    style: $("#style_filter").val(),
                    color: $("#color_filter").val(),
                    size: $("#size_filter").val(),
                    filter_line: $('#filter_line').val(),
                    filter_ws: $('#filter_ws').val(),
                    filter_style: $('#filter_style').val(),
                    filter_color: $('#filter_color').val(),
                    filter_size: $('#filter_size').val(),
                    size_filter: $('#size_filter').val()
                },
                success: function(res) {
                    $("#total-loading-summary").html(Number(res).toLocaleString('id-ID'));
                }
            });
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

            let date = new Date();

            let day = date.getDate();
            let month = date.getMonth() + 1;
            let year = date.getFullYear();

            // This arrangement can be altered based on how we want the date's format to appear.
            let currentDate = `${day}-${month}-${year}`;

            $.ajax({
                url: "{{ url("/loading-line/export-excel") }}",
                type: 'post',
                data: {
                    dateFrom : $('#dateFrom').val(),
                    dateTo : $('#dateTo').val(),
                },
                xhrFields: { responseType : 'blob' },
                success: function(res) {
                    elm.removeAttribute('disabled');
                    elm.innerText = "Export ";
                    let icon = document.createElement('i');
                    icon.classList.add('fa-solid');
                    icon.classList.add('fa-file-excel');
                    elm.appendChild(icon);

                    iziToast.success({
                        title: 'Success',
                        message: 'Success',
                        position: 'topCenter'
                    });

                    var blob = new Blob([res]);
                    var link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = "Summary Loading "+$('#dateFrom').val()+" - "+$('#dateTo').val()+".xlsx";
                    link.click();
                }, error: function (jqXHR) {
                    let res = jqXHR.responseJSON;
                    let message = '';
                    console.log(res.message);
                    for (let key in res.errors) {
                        message += res.errors[key]+' ';
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

        async function updateFilter() {
            document.getElementById('loading').classList.remove('d-none');

            $.ajax({
                url: '{{ route('filter-summary-loading') }}',
                dataType: 'json',
                dataSrc: 'data',
                data: {
                    dateFrom : $('#dateFrom').val(),
                    dateTo : $('#dateTo').val(),
                },
                success: function(response) {
                    document.getElementById('loading').classList.add('d-none');

                    if (response) {
                        if (response.lines && response.lines.length > 0) {
                            let lines = response.lines;
                            $("#filter_line").empty();
                            $.each(lines, function(index, value) {
                                $('#filter_line').append('<option value="'+value+'">'+value+'</option>');
                            });
                        }

                        if (response.ws && response.ws.length > 0) {
                            let ws = response.ws;
                            $("#filter_ws").empty();
                            $.each(ws, function(index, value) {
                                $('#filter_ws').append('<option value="'+value+'">'+value+'</option>');
                            });
                        }

                        if (response.style && response.style.length > 0) {
                            let style = response.style;
                            $("#filter_style").empty();
                            $.each(style, function(index, value) {
                                $('#filter_style').append('<option value="'+value+'">'+value+'</option>');
                            });
                        }

                        if (response.color && response.color.length > 0) {
                            let color = response.color;
                            $("#filter_color").empty();
                            $.each(color, function(index, value) {
                                $('#filter_color').append('<option value="'+value+'">'+value+'</option>');
                            });
                        }

                        if (response.size && response.size.length > 0) {
                            let size = response.size;
                            $("#filter_size").empty();
                            $("#size_filter").empty();
                            $.each(size, function(index, value) {
                                $('#filter_size').append('<option value="'+value+'">'+value+'</option>');
                                $('#size_filter').append('<option value="'+value+'">'+value+'</option>');
                            });
                        }
                    }
                },
                error: function(jqXHR) {
                    document.getElementById('loading').classList.add('d-none');

                    console.error(jqXHR);
                },
            });
        }

        $("#size_filter").on("change", function() {
            datatableLoadingLineReload();
        });
    </script>
@endsection
