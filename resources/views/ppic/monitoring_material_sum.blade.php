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
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-tv"></i> Monitoring Material Summary</h5>
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


            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-striped table-sm w-100 text-nowrap">
                    <thead class="table-primary">
                    </thead>
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
                url: '{{ route('get_ppic_monitoring_material_sum_style') }}',
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
                alert("Harap Isi Style Filter");
                return;
            }

            // Clear existing thead
            $('#datatable thead').html('');

            // Create the first header row (group titles)
            const headerRow1 = `
<tr>
    <th rowspan="2" style="text-align: center; vertical-align: middle;">ID Item</th>
    <th rowspan="2" style="text-align: center; vertical-align: middle;">Item Desc</th>
    <th colspan="4" style="text-align: center; vertical-align: middle;">Penerimaan</th>
    <th colspan="6" style="background-color: #4682B4; color: white; text-align: center; vertical-align: middle;">Pengeluaran</th>
    <th rowspan="2" style="text-align: center; vertical-align: middle;">Balance</th>
    <th rowspan="2" style="text-align: center; vertical-align: middle;">Unit</th>
</tr>`;



            // Create the second header row (sub-columns)
            const headerRow2 = `
<tr style="text-align: center; vertical-align: middle;">
    <th style="text-align: center; vertical-align: middle;">Pembelian</th>
    <th style="text-align: center; vertical-align: middle;">Pengembalian Dari Subkontraktor</th>
    <th style="text-align: center; vertical-align: middle;">Retur Produksi</th>
    <th style="text-align: center; vertical-align: middle;">Adjustment</th>

    <th style="background-color: #4682B4; color: white; text-align: center; vertical-align: middle;">Produksi</th>
    <th style="background-color: #4682B4; color: white; text-align: center; vertical-align: middle;">Subkontraktor</th>
    <th style="background-color: #4682B4; color: white; text-align: center; vertical-align: middle;">Sample</th>
    <th style="background-color: #4682B4; color: white; text-align: center; vertical-align: middle;">Retur Pembelian</th>
    <th style="background-color: #4682B4; color: white; text-align: center; vertical-align: middle;">Adjustment</th>
    <th style="background-color: #4682B4; color: white; text-align: center; vertical-align: middle;">Lainnya</th>
</tr>`;


            // Append both header rows to the thead
            $('#datatable thead').append(headerRow1 + headerRow2);

            // Now initialize/re-initialize DataTable
            if ($.fn.DataTable.isDataTable('#datatable')) {
                $('#datatable').DataTable().clear().draw();
                $('#datatable').DataTable().destroy();
            }

            $('#datatable').DataTable({
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
                    leftColumns: 2
                },
                columnDefs: [{
                    className: 'align-middle text-center',
                    targets: '_all'
                }],
                ajax: {
                    url: '{{ route('show_lap_monitoring_material_f_sum') }}',
                    data: function(d) {
                        d.buyer_filter = $('#buyer_filter').val();
                        d.style_filter = $('#style_filter').val();
                    },
                    beforeSend: function() {
                        $('#loadingOverlay').fadeIn();
                    },
                    complete: function() {
                        $('#loadingOverlay').fadeOut();
                    },
                    error: function(xhr, status, error) {
                        console.error('DataTable AJAX Error:', error);
                        $('#loadingOverlay').fadeOut();
                    }
                },
                columns: [{
                        data: 'id_item'
                    },
                    {
                        data: 'itemdesc'
                    },
                    {
                        data: 'qty_pembelian'
                    },
                    {
                        data: 'qty_retur_subkon'
                    },
                    {
                        data: 'qty_retur_prod'
                    },
                    {
                        data: 'qty_adj'
                    },
                    {
                        data: 'qty_out_prod'
                    },
                    {
                        data: 'qty_out_subkon'
                    },
                    {
                        data: 'qty_out_sample'
                    },
                    {
                        data: 'qty_retur_pembelian'
                    },
                    {
                        data: 'qty_out_adj'
                    },
                    {
                        data: 'qty_out_lainnya'
                    },
                    {
                        data: 'balance'
                    },
                    {
                        data: 'unit_konv'
                    }
                ]
            });
        }

        async function export_excel() {
            if ($('#datatable tbody tr').length === 0) {
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
            const worksheet = workbook.addWorksheet('Monitoring Material Summary');

            const buyer = $("#buyer_filter").val() || "All Buyers";
            const style = $("#style_filter").val() || "All Styles";
            const fileBuyer = buyer.replace(/\s+/g, '_');
            const fileStyle = style.replace(/\s+/g, '_');
            const today = new Date().toISOString().split('T')[0];

            // === Title Rows ===
            worksheet.mergeCells('A1:N1');
            worksheet.getCell('A1').value = `Buyer : ${buyer}`;
            worksheet.getCell('A1').font = {
                bold: true,
                size: 14
            };
            worksheet.getCell('A1').alignment = {
                vertical: 'middle',
                horizontal: 'left'
            };

            worksheet.mergeCells('A2:N2');
            worksheet.getCell('A2').value = `Style : ${style}`;
            worksheet.getCell('A2').font = {
                bold: true,
                size: 14
            };
            worksheet.getCell('A2').alignment = {
                vertical: 'middle',
                horizontal: 'left'
            };

            worksheet.addRow([]); // Spacer Row

            // === Header Row 1 (Row 4) ===
            const headerRow1 = worksheet.getRow(4);
            let colIndex = 1;

            // ID Item
            worksheet.mergeCells(4, colIndex, 5, colIndex);
            headerRow1.getCell(colIndex++).value = "ID Item";

            // Item Desc
            worksheet.mergeCells(4, colIndex, 5, colIndex);
            headerRow1.getCell(colIndex++).value = "Item Desc";

            // Penerimaan (4 columns)
            worksheet.mergeCells(4, colIndex, 4, colIndex + 3);
            headerRow1.getCell(colIndex).value = "Penerimaan";
            colIndex += 4;

            // Pengeluaran (6 columns)
            worksheet.mergeCells(4, colIndex, 4, colIndex + 5);
            headerRow1.getCell(colIndex).value = "Pengeluaran";
            colIndex += 6;

            // Balance
            worksheet.mergeCells(4, colIndex, 5, colIndex);
            headerRow1.getCell(colIndex++).value = "Balance";

            // Unit
            worksheet.mergeCells(4, colIndex, 5, colIndex);
            headerRow1.getCell(colIndex).value = "Unit";

            // Style the first header row
            for (let i = 1; i <= 14; i++) {
                const cell = headerRow1.getCell(i);
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
                        argb: 'FF4682B4'
                    }
                };
            }

            // === Header Row 2 (Row 5) ===
            const headerRow2 = worksheet.getRow(5);
            const subHeaders = [
                "Pembelian",
                "Pengembalian Dari Subkontraktor",
                "Retur Produksi",
                "Adjustment",
                "Produksi",
                "Subkontraktor",
                "Sample",
                "Retur Pembelian",
                "Adjustment",
                "Lainnya"
            ];
            subHeaders.forEach((text, i) => {
                const cell = headerRow2.getCell(i + 3);
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
                        argb: 'FF4682B4'
                    }
                };
            });

            // === Data Rows ===
            const dataStartRow = 6;
            const tableRows = document.querySelectorAll('#datatable tbody tr');

            tableRows.forEach((row, rowIndex) => {
                const rowData = Array.from(row.querySelectorAll('td')).map((td, i) => {
                    const text = td.innerText.trim();
                    // Columns 3 to 12 (D to M) are numeric
                    if (i >= 2 && i <= 11) {
                        const number = parseFloat(text.replace(/,/g, ''));
                        return isNaN(number) ? 0 : number;
                    }
                    return text;
                });
                worksheet.getRow(dataStartRow + rowIndex).values = rowData;
            });

            // === Column Formatting ===
            worksheet.columns.forEach(col => {
                col.width = 20;
                col.alignment = {
                    vertical: 'middle',
                    horizontal: 'center'
                };
            });

            // Format numeric columns (D to M)
            for (let i = 4; i <= 13; i++) {
                worksheet.getColumn(i).numFmt = '#,##0.00';
            }

            // === Zebra Striping + Borders ===
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

            // === Export the File ===
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
    </script>
@endsection
