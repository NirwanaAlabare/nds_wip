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


        .loading-text {
            font-size: 1.25rem;
            font-weight: 600;
            color: #333;
        }

        .loading-text .dots::after {
            content: '';
            display: inline-block;
            animation: dots 1.5s steps(3, end) infinite;
            width: 1em;
            text-align: left;
        }

        @keyframes dots {
            0% {
                content: '';
            }

            33% {
                content: '.';
            }

            66% {
                content: '..';
            }

            100% {
                content: '...';
            }
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
                    <label class="form-label"><small><b>Style</b></small></label>
                    <div class="input-group">
                        <select class="form-control select2bs4 form-control-sm rounded" id="style_filter"
                            name="style_filter" style="width: 100%;">
                            <option value="" disabled selected hidden>-- Select Style --</option> <!-- Placeholder -->
                            @foreach ($data_style as $datastyle)
                                <option value="{{ $datastyle->isi }}">
                                    {{ $datastyle->tampil }}
                                </option>
                            @endforeach
                        </select>
                    </div>
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

            <div id="loadingOverlay"
                style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background-color: rgba(255, 255, 255, 0.8); z-index: 1050;
            display: flex; align-items: center; justify-content: center; flex-direction: column;">

                <div class="spinner-border text-primary" role="status" style="width: 4rem; height: 4rem;">
                    <span class="visually-hidden">Loading...</span>
                </div>

                <div class="mt-4 loading-text">
                    <strong>Loading data<span class="dots"></span></strong>
                </div>
            </div>


            <div class="table-responsive mt-4">
                <table id="datatable_conv" class="table table-bordered table-striped w-100 text-nowrap">
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
            $('#style_filter').val('').trigger('change'); // Clear the select2 value
            $("#loadingOverlay").hide();
            // dataTableReload();
        });

        function notif() {
            alert("Maaf, Fitur belum tersedia!");
        }
        async function export_excel() {
            if ($('#datatable_conv tbody tr').length === 0 || !window.dataRows?.length) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Oops!',
                    text: 'Silahkan Load Data Terlebih Dahulu',
                    confirmButtonText: 'OK',
                    timer: 3000
                });
                return;
            }

            $("#loadingOverlay").fadeIn();

            const workbook = new ExcelJS.Workbook();
            const worksheet = workbook.addWorksheet('Monitoring Material');

            const style = $("#style_filter").val() || "All Styles";
            const fileStyle = style.replace(/\s+/g, '_');
            const today = new Date().toISOString().split('T')[0];

            const staticHeaders = [
                'ID Item', 'Item Desc', 'Color', 'Color Gmt',
                'Panel', 'Qty Order', 'Cons WS',
                'Need Material', 'Need Total', 'Penerimaan Gudang', 'Return Produksi',
                'Pengeluaran Produksi', 'Blc Material', 'Unit',
                'Output Pcs (Cons WS)', 'Blc To Order', 'Total Pcs (Cons WS)'
            ];

            const dynamicColumnNames = window.dynamicColumnNames || [];
            const dataRows = window.dataRows || [];
            const totalColumns = staticHeaders.length + dynamicColumnNames.length;

            // Column name conversion
            function getExcelColumnName(colNumber) {
                let columnName = '';
                while (colNumber > 0) {
                    let remainder = (colNumber - 1) % 26;
                    columnName = String.fromCharCode(65 + remainder) + columnName;
                    colNumber = Math.floor((colNumber - 1) / 26);
                }
                return columnName;
            }

            const mergedCells = new Set();

            function safeMerge(ws, startRow, startCol, endRow, endCol) {
                const key = `${startRow}:${startCol}:${endRow}:${endCol}`;
                if (!mergedCells.has(key)) {
                    ws.mergeCells(startRow, startCol, endRow, endCol);
                    mergedCells.add(key);
                }
            }

            function styleHeader(cell) {
                cell.font = {
                    bold: true,
                    color: {
                        argb: 'FFFFFFFF'
                    }
                };
                cell.fill = {
                    type: 'pattern',
                    pattern: 'solid',
                    fgColor: {
                        argb: 'FF007BFF'
                    }
                };
                cell.alignment = {
                    vertical: 'middle',
                    horizontal: 'center'
                };
            }

            // Title Rows
            // safeMerge(worksheet, 1, 1, 1, totalColumns);
            // worksheet.getCell('A1').value = `Buyer : ${buyer}`;
            // worksheet.getCell('A1').font = {
            //     bold: true,
            //     size: 14
            // };
            // worksheet.getCell('A1').alignment = {
            //     vertical: 'middle',
            //     horizontal: 'left'
            // };

            safeMerge(worksheet, 2, 1, 2, totalColumns);
            worksheet.getCell('A2').value = `Style : ${style}`;
            worksheet.getCell('A2').font = {
                bold: true,
                size: 14
            };
            worksheet.getCell('A2').alignment = {
                vertical: 'middle',
                horizontal: 'left'
            };

            worksheet.addRow([]); // spacer

            // Header Rows
            const headerRow1 = worksheet.getRow(4);
            staticHeaders.forEach((text, i) => {
                const cell = headerRow1.getCell(i + 1);
                cell.value = text;
                styleHeader(cell);
                safeMerge(worksheet, 4, i + 1, 5, i + 1); // rowspan 2
            });

            const shipmentStart = staticHeaders.length + 1;
            const shipmentEnd = shipmentStart + dynamicColumnNames.length - 1;

            if (dynamicColumnNames.length > 0) {
                const cell = headerRow1.getCell(shipmentStart);
                cell.value = 'Shipment';
                styleHeader(cell);
                safeMerge(worksheet, 4, shipmentStart, 4, shipmentEnd);
            }

            const headerRow2 = worksheet.getRow(5);
            dynamicColumnNames.forEach((name, i) => {
                const cell = headerRow2.getCell(shipmentStart + i);
                cell.value = name;
                styleHeader(cell);
            });

            // Data rows
            const dataStartRow = 6;
            dataRows.forEach((row, idx) => {
                const excelRow = worksheet.getRow(dataStartRow + idx);
                const rowData = [
                    row.id_item, row.itemdesc, row.color, row.col_gmt, row.nama_panel,
                    row.qty_order, row.cons_ws_konv, row.need_material, row.tot_mat,
                    row.qty_pembelian, row.qty_retur_prod, row.qty_out_prod, row.blc_mat,
                    row.unit_konv, row.output_pcs_cons_ws, row.blc_order, row.total_output_pcs_cons_ws,
                    ...(row.values || [])
                ];
                excelRow.values = rowData;

                // Style negative Blc To Order
                const blcToOrderVal = parseFloat(row.blc_order);
                if (!isNaN(blcToOrderVal) && blcToOrderVal < 0) {
                    const cell = excelRow.getCell(16);
                    cell.font = {
                        color: {
                            argb: 'FFFF0000'
                        },
                        bold: true
                    };
                }

                // Style shipment % < 100
                (row.values || []).forEach((val, i) => {
                    const strVal = val.toString();
                    const numVal = parseFloat(strVal.replace('%', '').trim());
                    if (strVal.includes('%') && !isNaN(numVal) && numVal < 100) {
                        const cell = excelRow.getCell(staticHeaders.length + 1 + i);
                        cell.font = {
                            color: {
                                argb: 'FFFF0000'
                            },
                            bold: true
                        };
                    }
                });
            });

            // Freeze pane
            worksheet.views = [{
                state: 'frozen',
                ySplit: dataStartRow - 1,
                xSplit: 3
            }];

            // Merge same values (row grouping simulation)
            const groupColumns = [1, 2, 3, 4, 5, 9, 10, 11, 12, 17];

            function mergeSameValuesVertically(ws, colIdx) {
                let startRow = dataStartRow;
                while (startRow < dataStartRow + dataRows.length) {
                    let endRow = startRow;
                    const val = ws.getCell(startRow, colIdx).value;
                    while (
                        endRow + 1 <= dataStartRow + dataRows.length - 1 &&
                        ws.getCell(endRow + 1, colIdx).value === val
                    ) {
                        endRow++;
                    }
                    if (endRow > startRow) {
                        safeMerge(ws, startRow, colIdx, endRow, colIdx);
                    }
                    startRow = endRow + 1;
                }
            }

            groupColumns.forEach(idx => mergeSameValuesVertically(worksheet, idx));

            // Style columns
            worksheet.columns.forEach(col => {
                col.width = 20;
                col.alignment = {
                    vertical: 'middle',
                    horizontal: 'center'
                };
            });

            // Zebra striping and borders
            worksheet.eachRow({
                includeEmpty: false
            }, (row, rowNumber) => {
                if (rowNumber >= dataStartRow && rowNumber % 2 === 0) {
                    row.fill = {
                        type: 'pattern',
                        pattern: 'solid',
                        fgColor: {
                            argb: 'FFF1F1F1'
                        }
                    };
                }
                row.eachCell(cell => {
                    cell.border = {
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
                });
            });

            // Download
            const buffer = await workbook.xlsx.writeBuffer();
            const blob = new Blob([buffer], {
                type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
            });

            const link = document.createElement("a");
            link.href = URL.createObjectURL(blob);
            link.download = `Monitoring_${fileStyle}_${today}.xlsx`;
            link.click();

            $("#loadingOverlay").fadeOut();
        }


        function formatColName(col) {
            const parts = col.split('_');

            const capitalize = str => str.charAt(0).toUpperCase() + str.slice(1);

            // Format: cap_out_feb_2025
            if (parts.length === 4) {
                const prefix = parts[0] === 'pct' ? '%' :
                    parts[0] === 'cap' ? 'Output' :
                    parts[0] === 'blc' ? 'Balance' : parts[0];
                const month = capitalize(parts[2]);
                const year = parts[3];
                return `${prefix} ${month} ${year}`;
            }

            // Format: feb_2025
            if (parts.length === 2) {
                const month = capitalize(parts[0]);
                const year = parts[1];
                return `Shipment ${month} ${year}`;
            }

            return col;
        }


        function capitalize(str) {
            return str.charAt(0).toUpperCase() + str.slice(1);
        }


        function dataTableReload() {
            if (!$("#style_filter").val()) {
                alert("Harap Isi Style Filter");
                return;
            }

            if ($.fn.DataTable.isDataTable('#datatable_conv')) {
                $('#datatable_conv').DataTable().clear().draw();
                $('#datatable_conv').DataTable().destroy();
            }

            $("#loadingOverlay").fadeIn();
            let style_filter = $("#style_filter").val();

            $.ajax({
                type: "GET",
                url: '{{ route('show_lap_monitoring_material_f_det') }}',
                data: {
                    style: style_filter
                },
                success: function(response) {
                    const columns = response.columns;
                    const data = response.data;

                    const dynamicColumnNames = columns.map(col => formatColName(col));

                    const tableData = data.map(row => ({
                        id_item: row.id_item,
                        itemdesc: row.itemdesc,
                        color: row.color,
                        col_gmt: row.col_gmt,
                        nama_panel: row.nama_panel,
                        qty_order: row.qty_order,
                        cons_ws_konv: row.cons_ws_konv,
                        need_material: row.need_material,
                        tot_mat: row.tot_mat,
                        qty_pembelian: row.qty_pembelian,
                        qty_retur_prod: row.qty_retur_prod,
                        qty_out_prod: row.qty_out_prod,
                        blc_mat: row.blc_mat,
                        unit_konv: row.unit_konv,
                        output_pcs_cons_ws: row.output_pcs_cons_ws,
                        blc_order: row.blc_order,
                        total_output_pcs_cons_ws: row.total_output_pcs_cons_ws,
                        values: row.values || [],
                        group_key: `${row.id_item}_${row.nama_panel}_${row.total_output_pcs_cons_ws}` // Added
                    }));

                    window.dynamicColumnNames = dynamicColumnNames;
                    window.dataRows = tableData;

                    buildDynamicTable(dynamicColumnNames, tableData);
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", status, error);
                    $("#loadingOverlay").fadeOut();
                }
            });
        }

        function buildDynamicTable(columnNames, dataRows) {
            const thead = document.querySelector('#datatable_conv thead');
            const tbody = document.getElementById('tableBody');

            thead.innerHTML = '';
            tbody.innerHTML = '';

            if ($.fn.DataTable.isDataTable('#datatable_conv')) {
                $('#datatable_conv').DataTable().destroy();
            }

            const staticHeaders = [
                'ID Item', 'Item Desc', 'Color', 'Color Gmt',
                'Panel', 'Qty Order', 'Cons WS',
                'Need Material', 'Need Total', 'Penerimaan Gudang', 'Return Produksi',
                'Pengeluaran Produksi', 'Blc Material', 'Unit',
                'Output Pcs (Cons WS)', 'Blc To Order', 'Total Pcs (Cons WS)'
            ];

            const blcOrderIndex = staticHeaders.indexOf('Blc To Order');
            const totalOutputIndex = staticHeaders.indexOf('Total Pcs (Cons WS)');

            // Header row 1
            const mainHeaderRow = document.createElement('tr');
            staticHeaders.forEach((header, index) => {
                const th = document.createElement('th');

                th.innerText = header;
                th.rowSpan = 2;
                th.style.verticalAlign = 'middle';
                th.className = index <= 5 ? 'align-middle text-start' : 'align-middle text-end';
                mainHeaderRow.appendChild(th);
            });

            const shipmentTh = document.createElement('th');
            shipmentTh.colSpan = columnNames.length;
            shipmentTh.innerText = 'Shipment';
            shipmentTh.className = 'text-center';
            mainHeaderRow.appendChild(shipmentTh);

            // Header row 2
            const shipmentDetailRow = document.createElement('tr');
            columnNames.forEach(name => {
                const th = document.createElement('th');
                th.innerText = name;
                th.className = 'text-end align-middle';
                shipmentDetailRow.appendChild(th);
            });

            thead.appendChild(mainHeaderRow);
            thead.appendChild(shipmentDetailRow);

            // Build table body
            dataRows.forEach(row => {
                const tr = document.createElement('tr');

                const rowData = [
                    row.id_item,
                    row.itemdesc,
                    row.color,
                    row.col_gmt,
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
                ];

                rowData.forEach((value, idx) => {
                    const td = document.createElement('td');
                    td.innerText = value;
                    td.className = idx <= 5 ? 'text-start align-middle' : 'text-end align-middle';

                    if (idx === blcOrderIndex && parseFloat(value) < 0) {
                        td.style.color = 'red';
                        td.style.fontWeight = 'bold';
                    }

                    tr.appendChild(td);
                });

                // Dynamic shipment columns
                row.values.forEach(val => {
                    const td = document.createElement('td');
                    td.innerText = val;
                    td.className = 'text-end align-middle';

                    const numericVal = parseFloat(val.toString().replace('%', '').trim());
                    if (val.toString().includes('%') && !isNaN(numericVal) && numericVal < 100) {
                        td.style.color = 'red';
                        td.style.fontWeight = 'bold';
                    }

                    tr.appendChild(td);
                });

                tbody.appendChild(tr);
            });

            // Initialize DataTable
            $('#datatable_conv').DataTable({
                scrollY: "400px",
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
                    leftColumns: 3
                },
                columnDefs: [{
                    targets: '_all',
                    className: 'align-middle'
                }],
                rowsGroup: [
                    0, 1, 2, 3, 8, 9, 10, 11, 12, 13, 4,
                    16 // Adjust this index to the correct column (zero-based)
                ]
                // Removed rowGroup logic as GroupKey is deleted
            });

            $("#loadingOverlay").fadeOut();
        }
    </script>
@endsection
