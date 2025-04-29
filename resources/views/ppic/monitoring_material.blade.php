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
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-tv"></i> Monitoring Material</h5>
        </div>
        <div class="card-body">
            <div class="d-flex flex-wrap gap-3 mb-3">
                <div class="mb-3 flex-fill" style="width: 200px;">
                    <label class="form-label"><small><b>Buyer</b></small></label>
                    <div class="input-group">
                        <select class="form-control select2bs4 form-control-sm rounded" id="buyer_filter"
                            name="buyer_filter" onchange="get_monitoring_material_style();" style="width: 100%;">
                            @foreach ($data_buyer as $databuyer)
                                <option value="{{ $databuyer->isi }}">
                                    {{ $databuyer->tampil }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="mb-3 flex-fill" style="width: 100px;">
                    <label class="form-label"><small><b>Style</b></small></label>
                    <select class='form-control select2bs4 form-control-sm rounded' style='width: 100%;' name='style_filter'
                        id='style_filter'></select>
                </div>
                <div class="mb-3 flex-fill d-flex align-items-end">
                    <a onclick="dataTableReload();get_column()" class="btn btn-outline-primary btn-sm position-relative">
                        <i class="fas fa-search fa-sm"></i>
                    </a>
                    <a onclick="export_excel()" class="btn btn-outline-success btn-sm ms-2">
                        Export Excel
                    </a>
                </div>
            </div>

            <label class="form-label"><b>Fabric - Detail</b></label>

            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-striped 100 text-nowrap">
                    <thead class="table-primary">
                        <tr style='text-align:center; vertical-align:middle'>
                            <th>ID Item</th>
                            <th>Item Desc</th>
                            <th>Color</th>
                            <th>Panel</th>
                            <th>Qty Order</th>
                            <th>Cons WS</th>
                            <th>Need Material</th>
                            <th>Need Total</th>
                            <th>Penerimaan Gudang</th>
                            <th>Return Produksi</th>
                            <th>Pengeluaran Produksi</th>
                            <th>Blc Material</th>
                            <th>Unit</th>
                            <th>Output Pcs (Cons WS)</th>
                            <th>Blc To Order</th>
                            <th>Total Pcs (Cons WS)</th>
                        </tr>
                    </thead>
                    {{-- <tfoot>
                        <tr>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                        </tr>
                    </tfoot> --}}
                </table>
            </div>

            <label class="form-label"><b>Fabric - Conv Pcs To Shipment</b></label>

            <div class="table-responsive mt-4">
                <table id="datatable_conv" class="table table-bordered table-striped 100 text-nowrap">
                    <thead class="table-primary">
                        <tr id="tableHeadRow" style="text-align: center; vertical-align: middle"></tr>
                    </thead>
                    <tbody id="tableBody"></tbody>
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
            $('#buyer_filter').val('');
            $('#buyer_filter').change();
            // dataTableReload();
        });

        function notif() {
            alert("Maaf, Fitur belum tersedia!");
        }

        function get_monitoring_material_style() {
            let buyer_filter = $("#buyer_filter").val();
            $.ajax({
                type: "GET",
                url: '{{ route('get_ppic_monitoring_material_style') }}',
                data: {
                    buyer: buyer_filter
                },
                success: function(html) {
                    if (html != "") {
                        $("#style_filter").html(html);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error: ", status, error);
                }
            });
        }

        function dataTableReload() {

            if (!$("#style_filter").val()) {
                // Show a warning message if reff_filter is empty
                alert("Harap Isi Style FIlter"); // You can replace this with a more sophisticated popup if needed
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
                scrollY: "300px",
                serverSide: false,
                processing: true,
                responsive: true,
                scrollX: true,
                scrollCollapse: true,
                paging: false,
                ordering: false,
                autoWidth: true,
                searching: true,
                // fixedColumns: {
                //     leftColumns: 4
                // },
                ajax: {
                    url: '{{ route('show_lap_monitoring_material_f_det') }}',
                    data: function(d) {
                        d.buyer_filter = $('#buyer_filter').val();
                        d.style_filter = $('#style_filter').val();
                    },
                },
                columns: [{
                        data: 'id_item'
                    },
                    {
                        data: 'itemdesc'
                    },
                    {
                        data: 'color'
                    },
                    {
                        data: 'nama_panel'
                    },
                    {
                        data: 'qty_order'
                    },
                    {
                        data: 'cons_ws_konv'
                    },
                    {
                        data: 'need_material'
                    },
                    {
                        data: 'tot_mat'
                    },
                    {
                        data: 'qty_pembelian'
                    },
                    {
                        data: 'qty_retur_prod'
                    },
                    {
                        data: 'qty_out_prod'
                    },
                    {
                        data: 'blc_mat'
                    },
                    {
                        data: 'unit_konv'
                    },
                    {
                        data: 'output_pcs_cons_ws'
                    },
                    {
                        data: 'blc_order'
                    },
                    {
                        data: 'total_output_pcs_cons_ws'
                    },
                ],
                rowsGroup: [
                    7, 8, 9, 10, 11, 15 // Adjust this index to the correct column (zero-based)
                ],
                columnDefs: [{
                    "className": "align-middle",
                    "targets": "_all"
                }]
            });
        }

        function export_excel() {
            if (!$("#style_filter").val()) {
                // Show a warning message if reff_filter is empty
                alert("Harap Isi Style Filter"); // You can replace this with a more sophisticated popup if needed
                return; // Exit the function if the condition is not met
            }

            let buyer_filter = $('#buyer_filter').val();
            let style_filter = $('#style_filter').val();

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
                url: '{{ route('export_excel_monitoring_material') }}',
                data: {
                    buyer_filter: buyer_filter,
                    style_filter: style_filter
                },
                success: function(data) {
                    // Create a new workbook and a worksheet
                    const workbook = new ExcelJS.Workbook();
                    const worksheet = workbook.addWorksheet("Mutasi Output Production ");

                    const headers = [
                        "ID Item", "Item Desc", "Color", "Panel", "Qty Order", "Cons WS",
                        "Need Material", "Total", "Penerimaan Gudang", "Return Produksi",
                        "Pengeluaran Produksi",
                        "Blc Material", "Unit",
                        "Output Pcs (Cons WS)", "Blc To Order", "Total Pcs (Cons WS)",
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

                    data.forEach(function(row) {

                        // Add the row to the worksheet
                        const newRow = worksheet.addRow([
                            row.id_item,
                            row.itemdesc,
                            row.color,
                            row.nama_panel,
                            row.qty_order,
                            row.cons_ws_konv,
                            row.need_material,
                            row.tot_mat,
                            row.qty_pembelian,
                            row.qty_retur_prod,
                            row.qty_out_prod,
                            row.blc_mat,
                            row.unit_konv,
                            row.output_pcs_cons_ws,
                            row.blc_order,
                            row.total_output_pcs_cons_ws
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

                    // // Create a footer row for sums
                    // const footerRow = worksheet.addRow([
                    //     "", "", "", "", "", "", // Empty cells for columns A to F
                    //     sumQtyPO, // Sum for Qty PO
                    //     sumCutting, // Sum for Cutting
                    //     sumBlcCutting, // Sum for Blc Cutting
                    //     sumLoading, // Sum for Loading
                    //     sumBlcLoading, // Sum for Blc Loading
                    //     sumSewing, // Sum for Sewing
                    //     sumBlcSewing, // Sum for Blc Sewing
                    //     sumPackingLine, // Sum for Packing Line
                    //     sumBlcPackingLine, // Sum for Blc Packing Line
                    //     sumPackingScan, // Sum for Packing Scan
                    //     sumBlcPackingScan, // Sum for Blc Packing Scan
                    //     sumShipment, // Sum for Shipment
                    //     sumBlcShipment // Sum for Blc Shipment
                    // ]);

                    // // Make footer bold and apply styles
                    // footerRow.eachCell({
                    //     includeEmpty: true
                    // }, function(cell) {
                    //     cell.font = {
                    //         bold: true
                    //     }; // Set font to bold
                    //     applyCellStyles(cell); // Apply styles based on value
                    // });

                    // Export the workbook
                    workbook.xlsx.writeBuffer().then(function(buffer) {
                        const sanitizedBuyerFilter = buyer_filter.replace(/[^a-zA-Z0-9]/g, '_');
                        const sanitizedStyleFilter = style_filter.replace(/[^a-zA-Z0-9]/g, '_');
                        const blob = new Blob([buffer], {
                            type: "application/octet-stream"
                        });
                        const link = document.createElement('a');
                        link.href = window.URL.createObjectURL(blob);
                        link.download =
                            `Monitoring Material ${sanitizedBuyerFilter} ${sanitizedStyleFilter} .xlsx`;
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

        function get_column() {
            let buyer_filter = $("#buyer_filter").val();
            let style_filter = $("#style_filter").val();

            $.ajax({
                type: "GET",
                url: '{{ route('get_ppic_monitoring_material_column') }}',
                data: {
                    buyer: buyer_filter,
                    style: style_filter
                },
                success: function(response) {
                    const columns = response.columns;
                    const data = response.data;

                    const monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun",
                        "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"
                    ];

                    const dynamicColumnNames = columns.map(c => `${monthNames[c.bln - 1]} ${c.thn}`);

                    const tableData = data.map(row => {
                        return {
                            id: row.id_item,
                            month: row.nama_panel,
                            values: dynamicColumnNames.map(col => {
                                const key = col.replace(' ', '_'); // example: 'Feb_2025'
                                return row[key] ?? 0;
                            })
                        };
                    });

                    buildDynamicTable(dynamicColumnNames, tableData);
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", status, error);
                }
            });
        }

        function buildDynamicTable(columnNames, dataRows) {
            const headRow = document.getElementById('tableHeadRow');
            const tableBody = document.getElementById('tableBody');

            headRow.innerHTML = '';
            tableBody.innerHTML = '';

            ['ID Item', 'Nama Panel'].forEach(header => {
                const th = document.createElement('th');
                th.innerText = header;
                headRow.appendChild(th);
            });

            columnNames.forEach(name => {
                const th = document.createElement('th');
                th.innerText = name;
                headRow.appendChild(th);
            });

            dataRows.forEach(row => {
                const tr = document.createElement('tr');
                tr.innerHTML = `<td>${row.id}</td><td>${row.month}</td>`;

                row.values.forEach(val => {
                    const td = document.createElement('td');
                    td.innerText = val;
                    tr.appendChild(td);
                });

                tableBody.appendChild(tr);
            });
        }
    </script>
@endsection
