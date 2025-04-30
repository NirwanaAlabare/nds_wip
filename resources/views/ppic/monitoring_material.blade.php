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
            $("#loadingOverlay").hide();
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

        async function export_excel() {
            if ($('#datatable_conv tbody tr').length === 0) {
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

            // 🔹 Filter values for title
            const buyer = $("#buyer_filter").val() || "All Buyers";
            const style = $("#style_filter").val() || "All Styles";

            const fileBuyer = buyer.replace(/\s+/g, '_');
            const fileStyle = style.replace(/\s+/g, '_');
            const today = new Date().toISOString().split('T')[0];

            // 🔹 Header DOM extraction
            const headerRows = document.querySelectorAll('#datatable_conv thead tr');
            const firstHeaderCells = headerRows[0].querySelectorAll('th');
            const secondHeaderCells = headerRows[1]?.querySelectorAll('th') || [];

            const staticHeaderCount = 17; // Adjust this if static columns change

            // 🔹 Helper: Excel column name
            function getExcelColumnName(colNumber) {
                let columnName = '';
                while (colNumber > 0) {
                    let remainder = (colNumber - 1) % 26;
                    columnName = String.fromCharCode(65 + remainder) + columnName;
                    colNumber = Math.floor((colNumber - 1) / 26);
                }
                return columnName;
            }

            const totalColumns = firstHeaderCells.length + (secondHeaderCells.length || 0);
            const lastColumn = getExcelColumnName(staticHeaderCount + secondHeaderCells.length);

            // 🔹 Title rows (Buyer + Style)
            worksheet.mergeCells(`A1:${lastColumn}1`);
            worksheet.getCell('A1').value = `Buyer : ${buyer}`;
            worksheet.getCell('A1').font = {
                bold: true,
                size: 14
            };
            worksheet.getCell('A1').alignment = {
                vertical: 'middle',
                horizontal: 'left'
            };

            worksheet.mergeCells(`A2:${lastColumn}2`);
            worksheet.getCell('A2').value = `Style : ${style}`;
            worksheet.getCell('A2').font = {
                bold: true,
                size: 14
            };
            worksheet.getCell('A2').alignment = {
                vertical: 'middle',
                horizontal: 'left'
            };

            worksheet.addRow([]); // Spacer row

            // ─── Header Row 1 (Static Headers + "Shipment") ───
            const headerRow1 = worksheet.getRow(4);
            let colIndex = 1;

            firstHeaderCells.forEach(th => {
                const colspan = parseInt(th.getAttribute('colspan')) || 1;
                const rowspan = parseInt(th.getAttribute('rowspan')) || 1;
                const text = th.innerText.trim();

                const cell = headerRow1.getCell(colIndex);
                cell.value = text;
                cell.font = {
                    bold: true,
                    color: {
                        argb: 'FFFFFFFF'
                    }
                };
                cell.alignment = {
                    vertical: 'middle',
                    horizontal: 'center'
                };
                cell.fill = {
                    type: 'pattern',
                    pattern: 'solid',
                    fgColor: {
                        argb: 'FF007BFF'
                    }
                };

                if (colspan > 1) {
                    worksheet.mergeCells(4, colIndex, 4, colIndex + colspan - 1);
                }
                if (rowspan > 1) {
                    worksheet.mergeCells(4, colIndex, 4 + rowspan - 1, colIndex);
                }

                colIndex += colspan;
            });

            // ─── Header Row 2 (Shipment Months) ───
            const headerRow2 = worksheet.getRow(5);
            secondHeaderCells.forEach((th, i) => {
                const text = th.innerText.trim();
                const cell = headerRow2.getCell(staticHeaderCount + i + 1); // Start after static headers
                cell.value = text;
                cell.font = {
                    bold: true,
                    color: {
                        argb: 'FFFFFFFF'
                    }
                };
                cell.alignment = {
                    vertical: 'middle',
                    horizontal: 'center'
                };
                cell.fill = {
                    type: 'pattern',
                    pattern: 'solid',
                    fgColor: {
                        argb: 'FF007BFF'
                    }
                };
            });

            // ─── Data Rows ───
            const dataStartRow = 6;
            const tableRows = document.querySelectorAll('#datatable_conv tbody tr');

            tableRows.forEach((row, index) => {
                const rowData = Array.from(row.querySelectorAll('td')).map(td => td.innerText.trim());
                worksheet.getRow(dataStartRow + index).values = rowData;
            });

            // ─── Format Columns ───
            worksheet.columns.forEach(col => {
                col.width = 20;
                col.alignment = {
                    vertical: 'middle',
                    horizontal: 'center'
                };
            });

            // ─── Zebra Striping & Borders ───
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
                row.eachCell({
                    includeEmpty: false
                }, cell => {
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

            // ─── Download Excel ───
            const buffer = await workbook.xlsx.writeBuffer();
            const blob = new Blob([buffer], {
                type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
            });

            const link = document.createElement("a");
            link.href = URL.createObjectURL(blob);
            link.download = `Monitoring_${fileBuyer}_${fileStyle}_${today}.xlsx`;
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

            // Destroy existing table if needed
            if ($.fn.DataTable.isDataTable('#datatable_conv')) {
                $('#datatable_conv').DataTable().clear().draw();
                $('#datatable_conv').DataTable().destroy();
            }

            $("#loadingOverlay").fadeIn();
            let buyer_filter = $("#buyer_filter").val();
            let style_filter = $("#style_filter").val();

            $.ajax({
                type: "GET",
                url: '{{ route('show_lap_monitoring_material_f_det') }}',
                data: {
                    buyer: buyer_filter,
                    style: style_filter
                },
                success: function(response) {
                    const columns = response.columns;
                    const data = response.data;

                    // Format the dynamic column headers
                    const dynamicColumnNames = columns.map(col => formatColName(col));

                    // Format the table data
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
                        values: row.values
                    }));

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

            thead.innerHTML = ''; // Clear entire thead
            tbody.innerHTML = ''; // Clear tbody

            // Destroy if already initialized
            if ($.fn.DataTable.isDataTable('#datatable_conv')) {
                $('#datatable_conv').DataTable().clear().destroy();
            }

            // First row (static headers + Shipment colspan)
            const mainHeaderRow = document.createElement('tr');

            const staticHeaders = [
                'ID Item', 'Item Desc', 'Color', 'Color Gmt',
                'Panel', 'Qty Order', 'Cons WS',
                'Need Material', 'Need Total', 'Penerimaan Gudang', 'Return Produksi',
                'Pengeluaran Produksi', 'Blc Material', 'Unit',
                'Output Pcs (Cons WS)', 'Blc To Order', 'Total Pcs (Cons WS)'
            ];

            staticHeaders.forEach(header => {
                const th = document.createElement('th');
                th.innerText = header;
                th.rowSpan = 2;
                th.style.verticalAlign = 'middle';
                th.className = 'align-middle text-center';
                mainHeaderRow.appendChild(th);
            });

            // Shipment grouped header
            const shipmentTh = document.createElement('th');
            shipmentTh.colSpan = columnNames.length;
            shipmentTh.innerText = 'Shipment';
            shipmentTh.className = 'text-center';
            mainHeaderRow.appendChild(shipmentTh);

            // Second row (dynamic columns)
            const shipmentDetailRow = document.createElement('tr');
            columnNames.forEach(name => {
                const th = document.createElement('th');
                th.innerText = name;
                th.className = 'text-center';
                shipmentDetailRow.appendChild(th);
            });

            // Append both header rows to thead
            thead.appendChild(mainHeaderRow);
            thead.appendChild(shipmentDetailRow);

            // Populate tbody
            dataRows.forEach(row => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
            <td>${row.id_item}</td>
            <td>${row.itemdesc}</td>
            <td>${row.color}</td>
            <td>${row.col_gmt}</td>
            <td>${row.nama_panel}</td>
            <td>${row.qty_order}</td>
            <td>${row.cons_ws_konv}</td>
            <td>${row.need_material}</td>
            <td>${row.tot_mat}</td>
            <td>${row.qty_pembelian}</td>
            <td>${row.qty_retur_prod}</td>
            <td>${row.qty_out_prod}</td>
            <td>${row.blc_mat}</td>
            <td>${row.unit_konv}</td>
            <td>${row.output_pcs_cons_ws}</td>
            <td>${row.blc_order}</td>
            <td>${row.total_output_pcs_cons_ws}</td>
        `;
                row.values.forEach(val => {
                    const td = document.createElement('td');
                    td.innerText = val;
                    tr.appendChild(td);
                });
                tbody.appendChild(tr);
            });

            // Re-initialize DataTable
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
                    className: 'align-middle text-center',
                    targets: '_all'
                }]
            });

            $("#loadingOverlay").fadeOut();
        }
    </script>
@endsection
