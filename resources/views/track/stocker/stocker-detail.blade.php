@extends('layouts.index')

@section('custom-link')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">

    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

    <!-- apexchart CSS -->
    <link href="{{ asset('plugins/apexcharts/apexcharts.css') }}" rel="stylesheet">

    <style>
        div.sticky {
            position: -webkit-sticky; /* Safari */
            position: sticky;
            z-index:999;
            top: 0;
        }

        th.dtfc-fixed-left,
        th.dtfc-fixed-right {
            z-index: 1 !important;
        }
    </style>
@endsection

@section('content')
    @php
        $firstWs = $ws->first();
    @endphp
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="text-sb fw-bold">{{ $firstWs ? $firstWs->ws : '-' }}</h5>
        <a href="{{ route('track-stocker') }}" class="btn btn-primary btn-sm"><i class="fa fa-reply"></i> Kembali ke Stock List</a>
    </div>
    <input type="hidden" value="{{ $firstWs ? $firstWs->id_act_cost : '-' }}" id="id">
    <div class="row sticky" style="background: #f4f6f9;">
        <div class="col-md-3">
            <div class="mb-3">
                <label class="form-label form-label-sm">Worksheet</label>
                <input type="text" class="form-control" value="{{ $firstWs ? $firstWs->ws : '-' }}" id="ws" readonly>
            </div>
        </div>
        <div class="col-md-3">
            <div class="mb-3">
                <label class="form-label form-label-sm">Style</label>
                <input type="text" class="form-control" value="{{ $firstWs ? $firstWs->styleno : '-' }}" id="style" readonly>
            </div>
        </div>
        <div class="col-md-2">
            <div class="mb-3">
                <label class="form-label form-label-sm">Color</label>
                <select class="form-select select2bs4" id="ws-color-filter">
                    <option value="" selected>All Color</option>
                    @foreach ($ws->groupBy("color")->keys() as $color)
                        <option value="{{ $color }}">{{ $color }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-2">
            <div class="mb-3">
                <label class="form-label form-label-sm">Panel</label>
                <select class="form-select select2bs4" id="ws-panel-filter">
                    <option value="" selected>All Panel</option>
                    @foreach ($panels as $panel)
                        <option value="{{ $panel->panel }}">{{ $panel->panel }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-2">
            <div class="mb-3">
                <label class="form-label form-label-sm">Size</label>
                <select class="form-select select2bs4" id="ws-size-filter">
                    <option value="" selected>All Size</option>
                    @foreach ($ws->sortBy("id")->groupBy("size")->keys() as $size)
                        <option value="{{ $size }}">{{ $size }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- STOCKER START --}}
        <div class="card card-sb" id="stocker-card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title fw-bold mb-0"><i class="fa-solid fa-ticket fa-sm"></i> Stocker</h5>
                    <div class="d-flex justify-content-end align-items-center gap-1">
                        <input type="date" class="form-control form-control-sm" id="stocker-from">
                        <span>-</span>
                        <input type="date" class="form-control form-control-sm" id="stocker-to">
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="stocker-table" class="table table-bordered table-sm w-100">
                        <thead>
                            <tr>
                                <th>Color</th>
                                <th>Size</th>
                                <th>Nama Part</th>
                                <th>No. Form</th>
                                <th>No. Cut</th>
                                <th>Group</th>
                                <th>No. Stocker</th>
                                <th>Qty</th>
                                <th>Range</th>
                                <th>Difference</th>
                                <th>Secondary</th>
                                <th>Rak</th>
                                <th>Trolley</th>
                                <th>Line</th>
                                <th>Updated At</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th class="text-end" colspan="6">Total</th>
                                <th id="total-stocker"></th>
                                <th id="total-stocker-qty"></th>
                                <th id="total-stocker-range"></th>
                                <th id="total-stocker-difference"></th>
                                <th colspan="5"></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    {{-- STOCKER END --}}
@endsection

@section('custom-script')
    <!-- apexchart JavaScript -->
    <script src="{{ asset('plugins/apexcharts/apexcharts.min.js') }}"></script>

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
    </script>

    <script>
        document.getElementById("loading").classList.remove("d-none");

        $(document).ready(async function() {

            $('#stocker-card').on('expanded.lte.cardwidget', () => {
                stockerTableReload();
            });

            // $('#output-line-card').on('expanded.lte.cardwidget', () => {
            //     getLineOutput();
            // });

            let today = new Date();
            let todayDate = ("0" + today.getDate()).slice(-2);
            let todayMonth = ("0" + (today.getMonth() + 1)).slice(-2);
            let todayYear = today.getFullYear();
            let todayFullDate = todayYear + '-' + todayMonth + '-' + todayDate;

            $('#ws-color-filter').on("change", () => {
                stockerTableReload();
            });

            $('#ws-panel-filter').on("change", () => {
                stockerTableReload();
            });

            $('#ws-size-filter').on("change", () => {
                stockerTableReload();
            });

            // Stocker
            stockerTableReload();
            $('#stocker-from').on("change", () => {
                stockerTableReload();
            });
            $('#stocker-to').on("change", () => {
                stockerTableReload();
            });

            document.getElementById("loading").classList.add("d-none");
        });

        // Stocker
            var stockerTableParameter = ['stk_color', 'stk_panel', 'stk_part', 'stk_no_form', 'stk_no_cut', 'stk_size', 'stk_group', 'stk_no_stocker', 'stk_qty', 'stk_range', 'stk_difference', 'stk_secondary','stk_rack', 'stk_trolley', 'stk_line', 'stk_updated_at'];

            $('#stocker-table thead tr').clone(true).appendTo('#stocker-table thead');
                $('#stocker-table thead tr:eq(1) th').each(function(i) {
                    if (i != 15) {
                        var title = $(this).text();
                        $(this).html('<input type="text" class="form-control form-control-sm" id="'+stockerTableParameter[i]+'" />');

                        $('input', this).on('keyup change', function() {
                            if (stockerTable.column(i).search() !== this.value) {
                                stockerTable
                                    .column(i)
                                    .search(this.value)
                                    .draw();
                            }
                        });
                    } else {
                        $(this).empty();
                    }
                });

                let stockerTable = $("#stocker-table").DataTable({
                    ordering: false,
                    processing: true,
                    serverSide: true,
                    pageLength: 50,
                    dom: 'rtip',
                    scrollX: "500px",
                    scrollY: "350px",
                    ajax: {
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        url: '{{ route('track-ws-stocker') }}',
                        dataType: 'json',
                        dataSrc: 'data',
                        data: function(d) {
                            d.actCostingId = $('#id').val();
                            d.panel = $('#ws-panel-filter').val();
                            d.color = $('#ws-color-filter').val();
                            d.size = $('#ws-size-filter').val();
                            d.dateFrom = $('#stocker-from').val();
                            d.dateTo = $('#stocker-to').val();
                        },
                    },
                    columns: [
                        {
                            data: 'color',
                        },
                        {
                            data: 'size',
                        },
                        {
                            data: 'nama_part',
                        },
                        {
                            data: 'no_form',
                        },
                        {
                            data: 'no_cut',
                        },
                        {
                            data: 'shade',
                        },
                        {
                            data: 'id_qr_stocker',
                        },
                        {
                            data: 'qty_ply',
                        },
                        {
                            data: 'stocker_range',
                        },
                        {
                            data: 'difference_qty',
                        },
                        {
                            data: 'secondary',
                        },
                        {
                            data: 'rak',
                        },
                        {
                            data: 'troli',
                        },
                        {
                            data: 'line',
                        },
                        {
                            data: 'latest_update',
                        },
                    ],
                    columnDefs: [
                        {
                            targets: [0, 1, 2, 3, 4, 5],
                            className: "text-nowrap"
                        },
                        {
                            targets: [7, 8, 9],
                            render: (data, type, row, meta) => {
                                return `<div style="min-width: 100px;">`+data+`</div>`;
                            }
                        },
                        {
                            targets: "_all",
                            className: "text-nowrap colorize"
                        }
                    ],
                    rowCallback: function( row, data, index ) {
                        if (data['line'] != '-') {
                            $('td.colorize', row).css('color', '#2e8a57');
                            $('td.colorize', row).css('font-weight', '600');
                        } else if (!data['dc_in_id'] && data['troli'] == '-') {
                            $('td.colorize', row).css('color', '#da4f4a');
                            $('td.colorize', row).css('font-weight', '600');
                        }
                    },
                    footerCallback: async function (row, data, start, end, display) {
                        let api = this.api();

                        api.column(6).footer().innerHTML = "...";
                        api.column(7).footer().innerHTML = "...";
                        api.column(8).footer().innerHTML = "...";
                        api.column(9).footer().innerHTML = "...";

                        let totalStocker = await getTotalStocker();

                        if (await totalStocker) {
                            api.column(6).footer().innerHTML = totalStocker.totalStocker+" Bundle";
                            api.column(7).footer().innerHTML = totalStocker.totalQtyPly;
                            api.column(8).footer().innerHTML = totalStocker.totalRange;
                            api.column(9).footer().innerHTML = totalStocker.totalDifference;

                                // document.getElementById("total-stocker").innerText = totalStocker.totalStocker;
                                // document.getElementById("total-stocker-qty").innerText = totalStocker.totalQtyPly;
                                // document.getElementById("total-stocker-range").innerText = totalStocker.totalRange;
                                // document.getElementById("total-stocker-difference").innerText = totalStocker.totalDifference;
                        }
                    }
                });

                async function stockerTableReload() {
                    document.getElementById('loading').classList.remove("d-none");

                    $("#stocker-table").DataTable() .ajax.reload();

                    document.getElementById('loading').classList.add("d-none");
                }

                async function getTotalStocker() {
                    return $.ajax({
                        url: '{{ route('track-ws-stocker-total') }}',
                        type: 'GET',
                        dataType: 'json',
                        data: {
                            actCostingId : $('#id').val(),
                            dateFrom : $('#stocker-from').val(),
                            dateTo : $('#stocker-to').val(),
                            color : $('#ws-color-filter').val(),
                            panel : $('#ws-panel-filter').val(),
                            size : $('#ws-size-filter').val(),
                            stkColor : $('#stk_color').val(),
                            stkPanel : $('#stk_panel').val(),
                            stkPart : $('#stk_part').val(),
                            stkNoForm : $('#stk_no_form').val(),
                            stkNoCut : $('#stk_no_cut').val(),
                            stkSize : $('#stk_size').val(),
                            stkGroup : $('#stk_group').val(),
                            stkNoStocker : $('#stk_no_stocker').val(),
                            stkSecondary : $('#stk_secondary').val(),
                            stkRack : $('#stk_rack').val(),
                            stkTrolley : $('#stk_trolley').val(),
                            stkLine : $('#stk_line').val(),
                            stkDifference : $('#stk_difference').val()
                        },
                    });
                }
    </script>
@endsection
