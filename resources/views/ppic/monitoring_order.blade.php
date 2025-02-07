@extends('layouts.index')

@section('custom-link')
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables 2.0/jquery.dataTables.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables 2.0/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables 2.0/fixedColumns.bootstrap4.min.css') }}">
    <!-- jQuery -->
    <script src="{{ asset('plugins/datatables 2.0/jquery-3.3.1.js') }}"></script>

    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <style>
        /* Custom styles for the table */

        .table-bordered {

            border: 1px solid black;
            /* Change thickness of the outer border */

        }

        .table-bordered th,
        .table-bordered td {

            border: 1px solid black;
            /* Change thickness of inner borders */

        }
    </style>
@endsection

@section('content')
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-tv"></i> Monitoring Order</h5>
        </div>
        <div class="card-body">
            <div class="d-flex flex-wrap gap-3 mb-3">
                <div class="mb-3 flex-fill" style="width: 200px;">
                    <label class="form-label"><small><b>Buyer</b></small></label>
                    <div class="input-group">
                        <select class="form-control select2bs4 form-control-sm rounded" id="buyer_filter"
                            name="buyer_filter"
                            onchange="get_monitoring_reff();get_monitoring_ws();get_monitoring_color();get_monitoring_size();"
                            style="width: 100%;">
                            @foreach ($data_buyer as $databuyer)
                                <option value="{{ $databuyer->isi }}">
                                    {{ $databuyer->tampil }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                {{-- <div class="mb-3 flex-fill" style="width: 150px;">
                    <label class="form-label"><small><b>Style</b></small></label>
                    <select class='form-control select2bs4 form-control-sm rounded' style='width: 100%;' name='style_filter'
                        id='style_filter'
                        onchange="get_monitoring_reff();get_monitoring_ws();get_monitoring_color();get_monitoring_size();"></select>
                </div> --}}
                <div class="mb-3 flex-fill" style="width: 100px;">
                    <label class="form-label"><small><b>Reff / Style</b></small></label>
                    <select class='form-control select2bs4 form-control-sm rounded' style='width: 100%;' name='reff_filter'
                        id='reff_filter'
                        onchange="get_monitoring_ws();get_monitoring_color();get_monitoring_size();"></select>
                </div>
                <div class="mb-3 flex-fill">
                    <label class="form-label"><small><b>WS</b></small></label>
                    <select class='form-control select2bs4 form-control-sm rounded' style='width: 100%;' name='ws_filter'
                        id='ws_filter' onchange="get_monitoring_color();get_monitoring_size();"></select>
                </div>
                <div class="mb-3 flex-fill">
                    <label class="form-label"><small><b>Color</b></small></label>
                    <select class='form-control select2bs4 form-control-sm rounded' style='width: 100%;' name='color_filter'
                        id='color_filter' onchange="get_monitoring_size();"></select>
                </div>
                <div class="mb-3 flex-fill">
                    <label class="form-label"><small><b>Size</b></small></label>
                    <select class='form-control select2bs4 form-control-sm rounded' style='width: 100%;' name='size_filter'
                        id='size_filter'></select>
                </div>
                <div class="mb-3 flex-fill d-flex align-items-end">
                    <a onclick="dataTableReload()" class="btn btn-outline-primary btn-sm position-relative">
                        <i class="fas fa-search fa-sm"></i>
                    </a>
                </div>
            </div>

            <div id="chart"></div>

            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-striped table-sm w-100 text-nowrap">
                    <thead class="table-primary">
                        <tr style='text-align:center; vertical-align:middle'>
                            <th>Buyer</th>
                            <th>WS</th>
                            <th>Color</th>
                            <th>Size</th>
                            <th>Style</th>
                            <th>Reff</th>
                            <th>Tgl. Shipment</th>
                            <th>Qty PO</th>
                            <th>Cutting</th>
                            <th>Loading</th>
                            <th>Sewing</th>
                            <th>Packing Line</th>
                            <th>Packing Scan</th>
                            <th>Shipment</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th colspan="7">Total</th>
                            <th id="total_qty_po"></th>
                            <th id="total_final_cut"></th>
                            <th id="total_final_loading"></th>
                            <th id="total_final_output_rfts"></th>
                            <th id="total_final_output_rfts_packing"></th>
                            <th id="total_final_scan"></th>
                            <th id="total_final_out"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>

        </div>
    </div>
@endsection

@section('custom-script')
    <!-- DataTables & Plugins -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables 2.0/dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables 2.0/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables 2.0/dataTables.fixedColumns.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-rowsgroup/dataTables.rowsGroup.js') }}"></script>
    <script>
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
    </script>
    <script>
        $(document).ready(() => {

            // setInterval(dataTableReload, 300000);
            // 300000

            $('#buyer_filter').val('');
            $('#buyer_filter').change();
            dataTableReload();
        });

        function notif() {
            alert("Maaf, Fitur belum tersedia!");
        }
        let chart; // Declare chart variable in a higher scope
        function update_chart(newData, maxXValue) {
            // Prepare the series data with colors
            const seriesData = newData.map(item => ({
                x: item.category,
                y: item.actual,
                goals: [{
                    name: 'Expected',
                    value: item.expected,
                    strokeWidth: 5,
                    strokeHeight: 25,
                    strokeColor: '#775DD0' // Color for the expected line
                }],
                color: item.color // Use the color property from newData
            }));

            // Prepare the options with the new data
            var options = {
                series: [{
                    name: 'Actual',
                    data: seriesData // Use the seriesData with colors
                }],
                chart: {
                    height: 350,
                    type: 'bar'
                },
                plotOptions: {
                    bar: {
                        horizontal: true,
                        distributed: true // Add this option to distribute bars
                    }
                },
                dataLabels: {
                    enabled: true,
                    style: {
                        fontSize: '14px', // Increase the font size
                        // fontWeight: 'bold', // Make the text bold
                        colors: ['black'] // Set the font color to black
                    },
                    formatter: function(val, opt) {
                        const goals = opt.w.config.series[opt.seriesIndex].data[opt.dataPointIndex].goals;
                        if (goals && goals.length) {
                            const expectedValue = goals[0].value;
                            const difference = expectedValue - val; // Calculate the difference
                            // Format the difference with parentheses and a sign
                            const formattedDifference = difference >= 0 ? `(-${difference})` : `(${difference})`;
                            return `${val} ${formattedDifference} / ${expectedValue}`; // Format the output
                        }
                        return val;
                    }
                },
                yaxis: {
                    min: 0, // Set the minimum value for the x-axis
                    max: maxXValue, // Set the maximum value for the x-axis
                    labels: {
                        style: {
                            fontSize: '12px', // Increase the font size for category labels
                            fontWeight: 'bold' // Make the category labels bold
                        }
                    }
                },

                legend: {
                    show: true
                },


                colors: newData.map(item => item.color) // Set the colors for the bars

            };

            if (chart) {
                chart.destroy();
            }

            // Create a new chart instance with the updated options
            chart = new ApexCharts(document.querySelector("#chart"), options);

            chart.render();
        }

        function get_monitoring_reff() {
            let buyer_filter = $("#buyer_filter").val();
            $.ajax({
                type: "GET",
                url: '{{ route('get_ppic_monitoring_order_reff') }}',
                data: {
                    buyer: buyer_filter
                },
                success: function(html) {
                    if (html != "") {
                        $("#reff_filter").html(html);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error: ", status, error);
                }
            });
        }

        function get_monitoring_ws() {
            let buyer_filter = $("#buyer_filter").val();
            let reff_filter = $("#reff_filter").val();
            $.ajax({
                type: "GET",
                url: '{{ route('get_ppic_monitoring_order_ws') }}',
                data: {
                    buyer: buyer_filter,
                    // style: style_filter,
                    reff: reff_filter
                },
                success: function(html) {
                    if (html != "") {
                        $("#ws_filter").html(html);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error: ", status, error);
                }
            });
        }

        function get_monitoring_color() {
            let buyer_filter = $("#buyer_filter").val();
            // let style_filter = $("#style_filter").val();
            let reff_filter = $("#reff_filter").val();
            let ws_filter = $("#ws_filter").val();
            $.ajax({
                type: "GET",
                url: '{{ route('get_ppic_monitoring_order_color') }}',
                data: {
                    buyer: buyer_filter,
                    // style: style_filter,
                    reff: reff_filter,
                    ws: ws_filter
                },
                success: function(html) {
                    if (html != "") {
                        $("#color_filter").html(html);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error: ", status, error);
                }
            });
        }

        function get_monitoring_size() {
            let buyer_filter = $("#buyer_filter").val();
            let reff_filter = $("#reff_filter").val();
            let ws_filter = $("#ws_filter").val();
            let color_filter = $("#color_filter").val();
            $.ajax({
                type: "GET",
                url: '{{ route('get_ppic_monitoring_order_size') }}',
                data: {
                    buyer: buyer_filter,
                    reff: reff_filter,
                    ws: ws_filter,
                    color: color_filter
                },
                success: function(html) {
                    if (html != "") {
                        $("#size_filter").html(html);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error: ", status, error);
                }
            });
        }

        function dataTableReload() {
            // Check if DataTable is already initialized
            if ($.fn.DataTable.isDataTable('#datatable')) {
                // Clear the table data
                $('#datatable').DataTable().clear().draw();
                // Destroy the existing DataTable instance
                $('#datatable').DataTable().destroy();
            }

            // Re-initialize the DataTable
            datatable = $("#datatable").DataTable({
                scrollY: "250px",
                serverSide: false,
                processing: true,
                responsive: true,
                scrollX: true,
                scrollCollapse: true,
                paging: false,
                ordering: false,
                autoWidth: true,
                searching: true,
                ajax: {
                    url: '{{ route('show_lap_monitoring_order') }}',
                    data: function(d) {
                        d.buyer_filter = $('#buyer_filter').val();
                        // d.style_filter = $('#style_filter').val();
                        d.reff_filter = $('#reff_filter').val();
                        d.ws_filter = $('#ws_filter').val();
                        d.color_filter = $('#color_filter').val();
                        d.size_filter = $('#size_filter').val();
                    },
                },
                columns: [{
                        data: 'buyer'
                    }, {
                        data: 'ws'
                    },
                    {
                        data: 'color'
                    },
                    {
                        data: 'size'
                    },
                    {
                        data: 'styleno_prod'
                    },
                    {
                        data: 'reff_no'
                    },
                    {
                        data: 'tgl_shipment_fix'
                    },
                    {
                        data: 'qty_po'
                    },
                    {
                        data: 'final_cut'
                    },
                    {
                        data: 'final_loading'
                    },
                    {
                        data: 'final_output_rfts'
                    },
                    {
                        data: 'final_output_rfts_packing'
                    },
                    {
                        data: 'tot_scan'
                    },
                    {
                        data: 'tot_fg_out'
                    },
                ],
                columnDefs: [{
                        targets: [7, 8, 9, 10, 11, 12, 13], // Indices of columns to align right
                        className: 'text-right' // Apply right alignment
                    },
                    {
                        "className": "align-middle",
                        "targets": "_all"
                    }
                ],
                createdRow: function(row, data, dataIndex) {
                    // Set the font weight to bold for all cells
                    $(row).find('td').css('font-weight', 'bold');
                    // Get the specific cells for qty_po and final_cut
                    var qtyPoCell = $(row).find('td').eq(7);
                    var finalCutCell = $(row).find('td').eq(8);
                    var finalLoadingCell = $(row).find('td').eq(9);
                    var finalOutputRftsCell = $(row).find('td').eq(10);
                    var finalOutputRftsPackingCell = $(row).find('td').eq(11);
                    var finalScanCell = $(row).find('td').eq(12);
                    var finalFGOutCell = $(row).find('td').eq(13);


                    if (parseFloat(data.final_cut) < parseFloat(data.qty_po)) {
                        finalCutCell.css('color', 'red');
                    } else {
                        finalCutCell.css('color', 'black');
                    }


                    if (parseFloat(data.final_loading) < parseFloat(data.qty_po)) {
                        // If final_cut is less than qty_po, set text color to red for final_cut
                        finalLoadingCell.css('color', 'red');
                    } else {
                        // Otherwise, set text color to black for final_cut
                        finalLoadingCell.css('color', 'black');
                    }

                    if (parseFloat(data.final_output_rfts) < parseFloat(data.qty_po)) {
                        // If final_cut is less than qty_po, set text color to red for final_cut
                        finalOutputRftsCell.css('color', 'red');
                    } else {
                        // Otherwise, set text color to black for final_cut
                        finalOutputRftsCell.css('color', 'black');
                    }

                    if (parseFloat(data.final_output_rfts_packing) < parseFloat(data.qty_po)) {
                        // If final_cut is less than qty_po, set text color to red for final_cut
                        finalOutputRftsPackingCell.css('color', 'red');
                    } else {
                        // Otherwise, set text color to black for final_cut
                        finalOutputRftsPackingCell.css('color', 'black');
                    }

                    if (parseFloat(data.tot_scan) < parseFloat(data.qty_po)) {
                        // If final_cut is less than qty_po, set text color to red for final_cut
                        finalScanCell.css('color', 'red');
                    } else {
                        // Otherwise, set text color to black for final_cut
                        finalScanCell.css('color', 'black');
                    }

                    if (parseFloat(data.tot_fg_out) < parseFloat(data.qty_po)) {
                        // If final_cut is less than qty_po, set text color to red for final_cut
                        finalFGOutCell.css('color', 'red');
                    } else {
                        // Otherwise, set text color to black for final_cut
                        finalFGOutCell.css('color', 'black');
                    }


                },
                drawCallback: function(settings) {
                    // Calculate totals
                    var api = this.api();

                    var totalQtyPo = api.column(7).data().reduce(function(a, b) {
                        return (parseFloat(a) || 0) + (parseFloat(b) || 0);
                    }, 0);

                    var totalFinalCut = api.column(8).data().reduce(function(a, b) {
                        return (parseFloat(a) || 0) + (parseFloat(b) || 0);
                    }, 0);

                    var totalFinalLoading = api.column(9).data().reduce(function(a, b) {
                        return (parseFloat(a) || 0) + (parseFloat(b) || 0);
                    }, 0);

                    var totalFinalOutputRfts = api.column(10).data().reduce(function(a, b) {
                        return (parseFloat(a) || 0) + (parseFloat(b) || 0);
                    }, 0);

                    var totalFinalOutputRftsPacking = api.column(11).data().reduce(function(a, b) {
                        return (parseFloat(a) || 0) + (parseFloat(b) || 0);
                    }, 0);

                    var totalTotScan = api.column(12).data().reduce(function(a, b) {
                        return (parseFloat(a) || 0) + (parseFloat(b) || 0);
                    }, 0);

                    var totalFGOut = api.column(13).data().reduce(function(a, b) {
                        return (parseFloat(a) || 0) + (parseFloat(b) || 0);
                    }, 0);


                    // Update footer with totals

                    $('#total_qty_po').text(totalQtyPo);
                    $('#total_final_cut').text(totalFinalCut);
                    $('#total_final_loading').text(totalFinalLoading);
                    $('#total_final_output_rfts').text(totalFinalOutputRfts);
                    $('#total_final_output_rfts_packing').text(totalFinalOutputRftsPacking);
                    $('#total_final_scan').text(totalTotScan);
                    $('#total_final_out').text(totalFGOut);
                    // Call the update_chart function with new data and totalQtyPo
                    const newData = [{
                            category: 'Cutting',
                            actual: totalFinalCut,
                            expected: totalQtyPo,
                            color: '#FF9130'
                        },
                        {
                            category: 'Loading',
                            actual: totalFinalLoading,
                            expected: totalQtyPo,
                            color: '#87A2FF'
                        },
                        {
                            category: 'Sewing',
                            actual: totalFinalOutputRfts,
                            expected: totalQtyPo,
                            color: '#FFF574'
                        },
                        {
                            category: 'Packing Line',
                            actual: totalFinalOutputRftsPacking,
                            expected: totalQtyPo,
                            color: '#96E5D1'
                        },
                        {
                            category: 'Packing Scan',
                            actual: totalTotScan,
                            expected: totalQtyPo,
                            color: '#77B254'
                        },
                        {
                            category: 'Shipment',
                            actual: totalFGOut,
                            expected: totalQtyPo,
                            color: '#A19AD3'
                        }
                    ];

                    // Call the function with new data and the total quantity for x-axis max
                    update_chart(newData, totalQtyPo);

                }
            });
        }
    </script>
@endsection
