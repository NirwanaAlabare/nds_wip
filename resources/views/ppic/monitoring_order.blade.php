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
    {{-- <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script> --}}
    <script src="{{ asset('plugins/apexcharts/apexcharts_new.js') }}"></script>

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
                    <a onclick="export_excel()" class="btn btn-outline-success btn-sm ms-2">
                        Export Excel
                    </a>
                </div>
            </div>

            <div id="chart"></div>

            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-striped w-100 text-nowrap">
                    <thead class="table-primary">
                        <tr style='text-align:center; vertical-align:middle'>
                            <th>Buyer</th>
                            <th>WS</th>
                            <th>Reff</th>
                            <th>Color</th>
                            <th>Size</th>
                            {{-- <th>Style</th> --}}
                            <th>Tgl. Shipment</th>
                            <th>Qty PO</th>
                            <th>Cutting</th>
                            <th>Blc Cutting</th>
                            <th>Loading</th>
                            <th>Blc Loading</th>
                            <th>Sewing</th>
                            <th>Blc Sewing</th>
                            <th>QC Finishing</th>
                            <th>Blc QC Finishing</th>
                            <th>Packing Scan</th>
                            <th>Blc Packing Scan</th>
                            <th>Shipment</th>
                            <th>Blc Shipment</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th colspan="6">Total</th>
                            <th id="total_qty_po"></th>
                            <th id="total_final_cut"></th>
                            <th id="total_blc_cut"></th>
                            <th id="total_final_loading"></th>
                            <th id="total_blc_loading"></th>
                            <th id="total_final_output_rfts"></th>
                            <th id="total_blc_output_rfts"></th>
                            <th id="total_final_output_rfts_packing"></th>
                            <th id="total_blc_output_rfts_packing"></th>
                            <th id="total_final_scan"></th>
                            <th id="total_blc_scan"></th>
                            <th id="total_final_out"></th>
                            <th id="total_blc_out"></th>
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
    <script src="{{ asset('plugins/export_excel_js/exceljs.min.js') }}"></script>
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
            // dataTableReload();
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
                            const formattedDifference = Number(difference) > 0 ? `(-${difference})` : `(${difference*-1})`;
                            console.log(formattedDifference, difference, val, expectedValue, Number(difference) >= 0);
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

            if (!$("#reff_filter").val()) {
                // Show a warning message if reff_filter is empty
                alert("Harap Isi Reff FIlter"); // You can replace this with a more sophisticated popup if needed
                return; // Exit the function if the condition is not met
            }
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
                fixedColumns: {
                    leftColumns: 7
                },
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
                        data: 'reff_no'
                    },
                    {
                        data: 'color'
                    },
                    {
                        data: 'size'
                    },
                    // {
                    //     data: 'styleno_prod'
                    // },
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
                        data: 'blc_cut'
                    },
                    {
                        data: 'final_loading'
                    },
                    {
                        data: 'blc_loading'
                    },
                    {
                        data: 'final_output_rfts'
                    },
                    {
                        data: 'blc_output_rfts'
                    },
                    {
                        data: 'final_output_rfts_packing'
                    },
                    {
                        data: 'blc_output_rfts_packing'
                    },
                    {
                        data: 'tot_scan'
                    },
                    {
                        data: 'blc_tot_scan'
                    },
                    {
                        data: 'tot_fg_out'
                    },
                    {
                        data: 'blc_tot_fg_out'
                    },
                ],
                columnDefs: [{
                        targets: [6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17,
                            18
                        ], // Indices of columns to align right
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
                    var qtyPoCell = $(row).find('td').eq(6);
                    var finalCutCell = $(row).find('td').eq(7);
                    var blcCutCell = $(row).find('td').eq(8);
                    var finalLoadingCell = $(row).find('td').eq(9);
                    var blcLoadingCell = $(row).find('td').eq(10);
                    var finalOutputRftsCell = $(row).find('td').eq(11);
                    var blcOutputRftsCell = $(row).find('td').eq(12);
                    var finalOutputRftsPackingCell = $(row).find('td').eq(13);
                    var blcOutputRftsPackingCell = $(row).find('td').eq(14);
                    var finalScanCell = $(row).find('td').eq(15);
                    var blcScanCell = $(row).find('td').eq(16);
                    var finalFGOutCell = $(row).find('td').eq(17);
                    var blcFGOutCell = $(row).find('td').eq(18);


                    if (parseFloat(data.blc_cut) < parseFloat(data.qty_po) &&
                        parseFloat(data.blc_cut) < '0') {
                        blcCutCell.css('color', 'red');
                    } else {
                        blcCutCell.css('color', 'black');
                    }


                    if (parseFloat(data.blc_loading) < parseFloat(data.qty_po) &&
                        parseFloat(data.blc_loading) < '0') {
                        // If final_cut is less than qty_po, set text color to red for final_cut
                        blcLoadingCell.css('color', 'red');
                    } else {
                        // Otherwise, set text color to black for final_cut
                        blcLoadingCell.css('color', 'black');
                    }

                    if (parseFloat(data.blc_output_rfts) < parseFloat(data.qty_po) &&
                        parseFloat(data.blc_output_rfts) < '0') {
                        // If final_cut is less than qty_po, set text color to red for final_cut
                        blcOutputRftsCell.css('color', 'red');
                    } else {
                        // Otherwise, set text color to black for final_cut
                        blcOutputRftsCell.css('color', 'black');
                    }

                    if (parseFloat(data.blc_output_rfts_packing) < parseFloat(data.qty_po) &&
                        parseFloat(data.blc_output_rfts_packing) < '0') {
                        // If final_cut is less than qty_po, set text color to red for final_cut
                        blcOutputRftsPackingCell.css('color', 'red');
                    } else {
                        // Otherwise, set text color to black for final_cut
                        blcOutputRftsPackingCell.css('color', 'black');
                    }

                    if (parseFloat(data.blc_tot_scan) < parseFloat(data.qty_po) &&
                        parseFloat(data.blc_tot_scan) < '0') {
                        // If final_cut is less than qty_po, set text color to red for final_cut
                        blcScanCell.css('color', 'red');
                    } else {
                        // Otherwise, set text color to black for final_cut
                        blcScanCell.css('color', 'black');
                    }

                    if (parseFloat(data.blc_tot_fg_out) < parseFloat(data.qty_po) && parseFloat(data
                            .blc_tot_fg_out) < '0') {
                        // If final_cut is less than qty_po, set text color to red for final_cut
                        blcFGOutCell.css('color', 'red');
                    } else {
                        // Otherwise, set text color to black for final_cut
                        blcFGOutCell.css('color', 'black');
                    }


                },
                drawCallback: function(settings) {
                    // Calculate totals
                    var api = this.api();


                    // Calculate totals based on the currently displayed data

                    var totalQtyPo = api.column(6, {
                        search: 'applied'
                    }).data().reduce(function(a, b) {
                        return (parseFloat(a) || 0) + (parseFloat(b) || 0);
                    }, 0)

                    var totalFinalCut = api.column(7, {
                        search: 'applied'
                    }).data().reduce(function(a, b) {
                        return (parseFloat(a) || 0) + (parseFloat(b) || 0);
                    }, 0)

                    var totalblcCut = api.column(8, {
                        search: 'applied'
                    }).data().reduce(function(a, b) {
                        return (parseFloat(a) || 0) + (parseFloat(b) || 0);
                    }, 0)

                    var totalFinalLoading = api.column(9, {
                        search: 'applied'
                    }).data().reduce(function(a, b) {
                        return (parseFloat(a) || 0) + (parseFloat(b) || 0);
                    }, 0)

                    var totalblcLoading = api.column(10, {
                        search: 'applied'
                    }).data().reduce(function(a, b) {
                        return (parseFloat(a) || 0) + (parseFloat(b) || 0);
                    }, 0)

                    var totalFinalOutputRfts = api.column(11, {
                        search: 'applied'
                    }).data().reduce(function(a, b) {
                        return (parseFloat(a) || 0) + (parseFloat(b) || 0);
                    }, 0)

                    var totalblcOutputRfts = api.column(12, {
                        search: 'applied'
                    }).data().reduce(function(a, b) {
                        return (parseFloat(a) || 0) + (parseFloat(b) || 0);
                    }, 0)

                    var totalFinalOutputRftsPacking = api.column(13, {
                        search: 'applied'
                    }).data().reduce(function(a, b) {
                        return (parseFloat(a) || 0) + (parseFloat(b) || 0);
                    }, 0)

                    var totalblcOutputRftsPacking = api.column(14, {
                        search: 'applied'
                    }).data().reduce(function(a, b) {
                        return (parseFloat(a) || 0) + (parseFloat(b) || 0);
                    }, 0)

                    var totalTotScan = api.column(15, {
                        search: 'applied'
                    }).data().reduce(function(a, b) {
                        return (parseFloat(a) || 0) + (parseFloat(b) || 0);
                    }, 0)

                    var totalblcTotScan = api.column(16, {
                        search: 'applied'
                    }).data().reduce(function(a, b) {
                        return (parseFloat(a) || 0) + (parseFloat(b) || 0);
                    }, 0)

                    var totalFGOut = api.column(17, {
                        search: 'applied'
                    }).data().reduce(function(a, b) {
                        return (parseFloat(a) || 0) + (parseFloat(b) || 0);
                    }, 0)

                    var totalblcFGOut = api.column(18, {
                        search: 'applied'
                    }).data().reduce(function(a, b) {
                        return (parseFloat(a) || 0) + (parseFloat(b) || 0);
                    }, 0)

                    // Update footer with totals

                    $('#total_qty_po').text(totalQtyPo);
                    $('#total_final_cut').text(totalFinalCut);
                    $('#total_blc_cut').text(totalblcCut).css('color', totalblcCut < 0 ? 'red' : 'black');
                    $('#total_final_loading').text(totalFinalLoading);
                    $('#total_blc_loading').text(totalblcLoading).css('color', totalblcLoading < 0 ? 'red' :
                        'black');
                    $('#total_final_output_rfts').text(totalFinalOutputRfts);
                    $('#total_blc_output_rfts').text(totalblcOutputRfts).css('color', totalblcOutputRfts < 0 ?
                        'red' : 'black');
                    $('#total_final_output_rfts_packing').text(totalFinalOutputRftsPacking);
                    $('#total_blc_output_rfts_packing').text(totalblcOutputRftsPacking).css('color',
                        totalblcOutputRftsPacking < 0 ? 'red' : 'black');
                    $('#total_final_scan').text(totalTotScan);
                    $('#total_blc_scan').text(totalblcTotScan).css('color', totalblcTotScan < 0 ? 'red' :
                        'black');
                    $('#total_final_out').text(totalFGOut);
                    $('#total_blc_out').text(totalblcFGOut).css('color', totalblcFGOut < 0 ? 'red' : 'black');
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
                            category: 'QC Finishing',
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

        function export_excel() {
            if (!$("#reff_filter").val()) {
                // Show a warning message if reff_filter is empty
                alert("Harap Isi Reff Filter"); // You can replace this with a more sophisticated popup if needed
                return; // Exit the function if the condition is not met
            }

            let buyer_filter = $('#buyer_filter').val();
            let reff_filter = $('#reff_filter').val();
            let ws_filter = $('#ws_filter').val();
            let color_filter = $('#color_filter').val();
            let size_filter = $('#size_filter').val();

            // Start the timer
            const startTime = new Date().getTime();

            Swal.fire({
                title: 'Please Wait...',
                html: 'Exporting Data...',
                didOpen: () => {
                    Swal.showLoading();
                },
                allowOutsideClick: false,
            });

            // Fetch all data from the server
            $.ajax({
                type: "POST",
                url: '{{ route('export_excel_monitoring_order') }}',
                data: {
                    buyer_filter: buyer_filter,
                    reff_filter: reff_filter,
                    ws_filter: ws_filter,
                    color_filter: color_filter,
                    size_filter: size_filter
                },
                success: function(data) {
                    // Create a new workbook and a worksheet
                    const workbook = new ExcelJS.Workbook();
                    const worksheet = workbook.addWorksheet("Mutasi Output Production ");

                    const headers = [
                        "Buyer", "WS", "Reff", "Color", "Size", "Tgl. Shipment",
                        "Qty PO", "Cutting", "Blc Cutting",
                        "Loading", "Blc Loading",
                        "Sewing", "Blc Sewing",
                        "QC Finishing", "Blc QC Finishing",
                        "Packing Scan", "Blc Packing Scan",
                        "Shipment", "Blc Shipment",
                    ];
                    const headerRow = worksheet.addRow(headers);

                    // Make header bold
                    headerRow.eachCell({
                        includeEmpty: true
                    }, function(cell) {
                        cell.font = {
                            bold: true
                        }; // Set font to bold
                    });

                    // Define border style
                    const borderStyle = {
                        top: {
                            style: 'thin'
                        },
                        left: {
                            style: 'thin'
                        },
                        bottom: {
                            style: 'thin'
                        },
                        right: {
                            style: 'thin'
                        }
                    };

                    // Function to apply styles based on value
                    function applyCellStyles(cell) {
                        cell.border = borderStyle; // Apply border style
                        if (typeof cell.value === 'number' && cell.value < 0) {
                            cell.font = {
                                color: {
                                    argb: 'FF0000'
                                }
                            }; // Red color for negative values
                        }
                    }

                    // Initialize sums
                    let sumQtyPO = 0;
                    let sumCutting = 0;
                    let sumBlcCutting = 0;
                    let sumLoading = 0;
                    let sumBlcLoading = 0;
                    let sumSewing = 0;
                    let sumBlcSewing = 0;
                    let sumPackingLine = 0;
                    let sumBlcPackingLine = 0;
                    let sumPackingScan = 0;
                    let sumBlcPackingScan = 0;
                    let sumShipment = 0;
                    let sumBlcShipment = 0;

                    // Add data rows and calculate sums
                    data.forEach(function(row) {
                        const qtyPO = Number(row.qty_po);
                        const cutting = Number(row.final_cut);
                        const blcCutting = Number(row.blc_cut);
                        const loading = Number(row.final_loading);
                        const blcLoading = Number(row.blc_loading);
                        const sewing = Number(row.final_output_rfts);
                        const blcSewing = Number(row.blc_output_rfts);
                        const packingLine = Number(row.final_output_rfts_packing);
                        const blcPackingLine = Number(row.blc_output_rfts_packing);
                        const packingScan = Number(row.tot_scan);
                        const blcPackingScan = Number(row.blc_tot_scan);
                        const shipment = Number(row.tot_fg_out);
                        const blcShipment = Number(row.blc_tot_fg_out);

                        // Update sums
                        sumQtyPO += qtyPO;
                        sumCutting += cutting;
                        sumBlcCutting += blcCutting;
                        sumLoading += loading;
                        sumBlcLoading += blcLoading;
                        sumSewing += sewing;
                        sumBlcSewing += blcSewing;
                        sumPackingLine += packingLine;
                        sumBlcPackingLine += blcPackingLine;
                        sumPackingScan += packingScan;
                        sumBlcPackingScan += blcPackingScan;
                        sumShipment += shipment;
                        sumBlcShipment += blcShipment;

                        // Add the row to the worksheet
                        const newRow = worksheet.addRow([
                            row.buyer,
                            row.ws,
                            row.reff_no,
                            row.color,
                            row.size,
                            row.tgl_shipment_fix,
                            qtyPO,
                            cutting,
                            blcCutting,
                            loading,
                            blcLoading,
                            sewing,
                            blcSewing,
                            packingLine,
                            blcPackingLine,
                            packingScan,
                            blcPackingScan,
                            shipment,
                            blcShipment
                        ]);

                        // Apply styles to each cell in the new row
                        newRow.eachCell({
                            includeEmpty: true
                        }, applyCellStyles);
                    });

                    // Apply border to header row
                    worksheet.getRow(1).eachCell({
                        includeEmpty: true
                    }, function(cell) {
                        cell.border = borderStyle;
                    });

                    // Auto-adjust column widths
                    worksheet.columns.forEach(column => {
                        let maxLength = 0;
                        column.eachCell({
                            includeEmpty: true
                        }, cell => {
                            if (cell.value) {
                                maxLength = Math.max(maxLength, cell.value.toString().length);
                            }
                        });
                        column.width = maxLength + 2; // Adding 2 for padding
                    });

                    // Create a footer row for sums
                    const footerRow = worksheet.addRow([
                        "", "", "", "", "", "", // Empty cells for columns A to F
                        sumQtyPO, // Sum for Qty PO
                        sumCutting, // Sum for Cutting
                        sumBlcCutting, // Sum for Blc Cutting
                        sumLoading, // Sum for Loading
                        sumBlcLoading, // Sum for Blc Loading
                        sumSewing, // Sum for Sewing
                        sumBlcSewing, // Sum for Blc Sewing
                        sumPackingLine, // Sum for Packing Line
                        sumBlcPackingLine, // Sum for Blc Packing Line
                        sumPackingScan, // Sum for Packing Scan
                        sumBlcPackingScan, // Sum for Blc Packing Scan
                        sumShipment, // Sum for Shipment
                        sumBlcShipment // Sum for Blc Shipment
                    ]);

                    // Make footer bold and apply styles
                    footerRow.eachCell({
                        includeEmpty: true
                    }, function(cell) {
                        cell.font = {
                            bold: true
                        }; // Set font to bold
                        applyCellStyles(cell); // Apply styles based on value
                    });

                    // Export the workbook
                    workbook.xlsx.writeBuffer().then(function(buffer) {
                        const sanitizedBuyerFilter = buyer_filter.replace(/[^a-zA-Z0-9]/g, '_');
                        const sanitizedReffFilter = reff_filter.replace(/[^a-zA-Z0-9]/g, '_');
                        const sanitizedWsFilter = ws_filter.replace(/[^a-zA-Z0-9]/g, '_');
                        const sanitizedColorFilter = color_filter.replace(/[^a-zA-Z0-9]/g, '_');
                        const sanitizedSizeFilter = size_filter.replace(/[^a-zA-Z0-9]/g, '_');
                        const blob = new Blob([buffer], {
                            type: "application/octet-stream"
                        });
                        const link = document.createElement('a');
                        link.href = window.URL.createObjectURL(blob);
                        link.download =
                            `Monitoring Order ${sanitizedBuyerFilter} ${sanitizedReffFilter} ${sanitizedWsFilter} ${sanitizedColorFilter} ${sanitizedSizeFilter}.xlsx`;
                        link.click();

                        // Calculate the elapsed time
                        const endTime = new Date().getTime();
                        const elapsedTime = Math.round((endTime - startTime) /
                            1000); // Convert to seconds

                        // Close the loading notification
                        Swal.close();

                        // Show success message with elapsed time
                        Swal.fire({
                            title: 'Success!',
                            text: `Data has been successfully exported in ${elapsedTime} seconds.`,
                            icon: 'success',
                            confirmButtonText: 'Okay'
                        });
                    });
                },
                error: function() {
                    Swal.fire({
                        title: 'Error!',
                        text: 'There was an error exporting the data.',
                        icon: 'error',
                        confirmButtonText: 'Okay'
                    });
                }
            });
        }
    </script>
@endsection
