@extends('layouts.index', ["containerFluid" => true])

@section('custom-link')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">

    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

    <style>
        .dataTables_wrapper .dataTables_processing {
            position: absolute;
            top: 50px !important;
            /* background: #FFFFCC;
            border: 1px solid black; */
            border-radius: 3px;
            font-weight: bold;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid">
        <h3 class="my-3 text-sb text-start fw-bold">Sewing Line WIP</h3>
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-end">
                    <div class="d-flex gap-3 align-items-end mb-3">
                        <div>
                            <label class="form-label">Tanggal Awal</label>
                            <input type="date" class="form-control form-control-sm" id="tanggal_awal" value="{{ date('Y-m-d') }}">
                        </div>
                        <span> - </span>
                        <div>
                            <label class="form-label">Tanggal Akhir</label>
                            <input type="date" class="form-control form-control-sm" id="tanggal_akhir" value="{{ date('Y-m-d') }}">
                        </div>
                        <button class="btn btn-sm btn-primary" onclick="lineWipTableReload()"><i class="fa fa-search"></i></button>
                    </div>
                    <div class="d-flex gap-3 align-items-end mb-3">
                        <select class="form-select select2bs4" name="line_id" id="line_id">
                            <option value="">Pilih Line</option>
                            @foreach ($lines as $line)
                                <option value="{{ $line->line_id }}" data-line="{{ $line->username }}">{{ $line->FullName }}</option>
                            @endforeach
                        </select>
                        <button class="btn btn-success" onclick="exportExcel()"><i class="fa fa-file-excel"></i></button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-hover" id="line_wip_table">
                        <thead>
                            <tr>
                                <th>Line</th>
                                <th>Last Shipment Date</th>
                                <th>No. WS</th>
                                <th>Style</th>
                                <th>Color</th>
                                <th>Size</th>
                                <th>Qty Loading</th>
                                <th>WIP Sewing Line</th>
                                <th>Reject</th>
                                <th>Defect</th>
                                <th>Qty Output</th>
                                <th>WIP Steam</th>
                                <th>Qty Packing Line</th>
                                <th>WIP Packing</th>
                                <th>Qty Transfer Garment</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="6">Total</td>
                                <th>...</th>
                                <th>...</th>
                                <th>...</th>
                                <th>...</th>
                                <th>...</th>
                                <th>...</th>
                                <th>...</th>
                                <th>...</th>
                                <th>...</th>
                            </tr>
                        </tfoot>
                    </table>
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
            theme: 'bootstrap4'
        })
    </script>

    <script>
        // Initial Function
        $(document).ready(() => {
            // Set Filter to 1 Week Ago
            let oneWeeksBefore = new Date(new Date().setDate(new Date().getDate() - 7));
            let oneWeeksBeforeDate = ("0" + oneWeeksBefore.getDate()).slice(-2);
            let oneWeeksBeforeMonth = ("0" + (oneWeeksBefore.getMonth() + 1)).slice(-2);
            let oneWeeksBeforeYear = oneWeeksBefore.getFullYear();
            let oneWeeksBeforeFull = oneWeeksBeforeYear + '-' + oneWeeksBeforeMonth + '-' + oneWeeksBeforeDate;

            $("#tgl-awal").val(oneWeeksBeforeFull).trigger("change");
        });

        $("#line_id").on("change", () => {
            lineWipTableReload();
        });

        function lineWipTableReload() {
            $("#line_wip_table").DataTable().ajax.reload();
        }

        // Filter
        const filters = ["line_filter", "tanggal_filter", "ws_filter", "style_filter", "color_filter", "size_filter"];
        $('#line_wip_table thead tr').clone(true).appendTo('#line_wip_table thead');
        $('#line_wip_table thead tr:eq(1) th').each(function(i) {
            if (i == 0 || i == 1 || i == 2 || i == 3 || i == 4 || i == 5) {
                var title = $(this).text();
                $(this).html('<input type="text" class="form-control form-control-sm" style="width:100%" id="'+filters[i]+'"/>');

                $('input', this).on('keyup change', function() {
                    if (lineWipTable.column(i).search() !== this.value) {
                        lineWipTable
                            .column(i)
                            .search(this.value)
                            .draw();
                    }
                });
            } else {
                $(this).empty();
            }
        });

        let lineWipTable = $("#line_wip_table").DataTable({
            ordering: false,
            processing: true,
            serverSide: false,
            pageLength: 100,
            scrollX: '400px',
            scrollY: '400px',
            ajax: {
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route('line-wip') }}',
                dataType: 'json',
                dataSrc: 'data',
                data: function(d) {
                    d.tanggal_awal = $('#tanggal_awal').val();
                    d.tanggal_akhir = $('#tanggal_akhir').val();
                    d.line_id = $('#line_id').val();
                    d.line = $('#line_id').find(":selected").attr("data-line");
                },
            },
            columns: [
                {
                    data: 'nama_line'
                },
                {
                    data: 'tanggal'
                },
                {
                    data: 'ws'
                },
                {
                    data: 'styleno'
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
                {
                    data: null
                },
                {
                    data: 'reject'
                },
                {
                    data: 'defect'
                },
                {
                    data: 'output'
                },
                {
                    data: null
                },
                {
                    data: 'output_packing'
                },
                {
                    data: null
                },
                {
                    data: 'total_transfer_garment'
                },
            ],
            columnDefs: [
                {
                    targets: [0],
                    className: "text-nowrap",
                    render: (data, type, row, meta) => {
                        return data ? data.replace("_", " ").toUpperCase() : '-';
                    }
                },
                {
                    targets: [6, 8, 9, 10, 12, 14],
                    className: "text-nowrap",
                    render: (data, type, row, meta) => {
                        return data ? data.toLocaleString("ID-id") : 0;
                    }
                },
                {
                    targets: [7],
                    className: "text-nowrap",
                    render: (data, type, row, meta) => {
                        return ((row.loading_qty ? Number(row.loading_qty) : 0) -  ((row.reject ? Number(row.reject) : 0) + (row.defect ? Number(row.defect) : 0) + (row.output ? Number(row.output) : 0))).toLocaleString("ID-id");
                    }
                },
                {
                    targets: [11],
                    className: "text-nowrap",
                    render: (data, type, row, meta) => {
                        return ((row.output ? Number(row.output) : 0) -  (row.output_packing ? Number(row.output_packing) : 0)).toLocaleString("ID-id");
                    }
                },
                {
                    targets: [13],
                    className: "text-nowrap",
                    render: (data, type, row, meta) => {
                        return ((row.output_packing ? Number(row.output_packing) : 0) - (row.total_transfer_garment ? Number(row.total_transfer_garment) : 0)).toLocaleString("ID-id");
                    }
                },
                // Text No Wrap
                {
                    targets: "_all",
                    className: "text-nowrap"
                }
            ],
            footerCallback: async function(row, data, start, end, display) {
                var api = this.api(),data;

                $(api.column(0).footer()).html('Total');
                $(api.column(6).footer()).html("...");
                $(api.column(7).footer()).html("...");
                $(api.column(8).footer()).html("...");
                $(api.column(9).footer()).html("...");
                $(api.column(10).footer()).html("...");
                $(api.column(11).footer()).html("...");
                $(api.column(12).footer()).html("...");
                $(api.column(13).footer()).html("...");
                $(api.column(14).footer()).html("...");

                $.ajax({
                    url: '{{ route('total-line-wip') }}',
                    dataType: 'json',
                    dataSrc: 'data',
                    data: {
                        'tanggal_awal': $('#tanggal_awal').val(),
                        'tanggal_akhir': $('#tanggal_akhir').val(),
                        'line_id': $('#line_id').val(),
                        'line': $('#line_id').find(":selected").attr("data-line"),
                        'lineNameFilter': $('#line_filter').val(),
                        'tanggalFilter': $('#tanggal_filter').val(),
                        'wsFilter': $('#ws_filter').val(),
                        'styleFilter': $('#style_filter').val(),
                        'colorFilter': $('#color_filter').val(),
                        'sizeFilter': $('#size_filter').val(),
                        'search' : $('#line_wip_table_filter input').val()
                    },
                    success: function(response) {
                        console.log(response);

                        if (response) {
                            // Update footer by showing the total with the reference of the column index
                            $(api.column(0).footer()).html('Total');
                            $(api.column(6).footer()).html((response['total_loading']).toLocaleString("ID-id"));
                            $(api.column(7).footer()).html((Number(response['total_loading']) - (Number(response['total_reject']) + Number(response['total_defect']) + Number(response['total_output']))).toLocaleString("ID-id"));
                            $(api.column(8).footer()).html((response['total_reject']).toLocaleString("ID-id"));
                            $(api.column(9).footer()).html((response['total_defect']).toLocaleString("ID-id"));
                            $(api.column(10).footer()).html((response['total_output']).toLocaleString("ID-id"));
                            $(api.column(11).footer()).html((Number(response['total_output']) - Number(response['total_output_packing'])).toLocaleString("ID-id"));
                            $(api.column(12).footer()).html((response['total_output_packing']).toLocaleString("ID-id"));
                            $(api.column(13).footer()).html((Number(response['total_output_packing']) - Number(response['total_transfer_garment'])).toLocaleString("ID-id"));
                            $(api.column(14).footer()).html((response['total_transfer_garment']).toLocaleString("ID-id"));
                        }
                    },
                    error: function(jqXHR) {
                        console.error(jqXHR);
                    },
                })
            },
        });

        function exportExcel() {
            Swal.fire({
                title: 'Please Wait...',
                html: 'Exporting Data...',
                didOpen: () => {
                    Swal.showLoading()
                },
                allowOutsideClick: false,
            });

            $.ajax({
                url: "{{ route("export-excel-line-wip") }}",
                type: "get",
                data: {
                    tanggal_awal : $('#tanggal_awal').val(),
                    tanggal_akhir : $('#tanggal_akhir').val(),
                    line_id : $('#line_id').val(),
                    line : $('#line_id').find(":selected").attr("data-line"),
                    lineNameFilter : $('#line_filter').val(),
                    tanggalFilter : $('#tanggal_filter').val(),
                    wsFilter : $('#ws_filter').val(),
                    styleFilter : $('#style_filter').val(),
                    colorFilter : $('#color_filter').val(),
                    sizeFilter : $('#size_filter').val()
                },
                xhrFields: {
                    responseType: 'blob'
                },
                success: function (response) {
                    swal.close();
                    Swal.fire({
                        title: 'Data Sudah Di Export!',
                        icon: "success",
                        showConfirmButton: true,
                        allowOutsideClick: false
                    });
                    var blob = new Blob([response]);
                    var link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = "Sewing Line WIP " + $('#tanggal_awal').val() + " - " + $('#tanggal_akhir').val() + " "+ ($('#line').val() ? $('#line').val() : '') +".xlsx";
                    link.click();
                },
                error: function (jqXHR) {
                    console.error(jqXHR);
                }
            });
        }
    </script>
@endsection
