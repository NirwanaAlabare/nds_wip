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
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-list"></i> Report Mutasi Output (Production)</h5>
        </div>
        <div class="card-body">
            <div class="d-flex align-items-end gap-3 mb-3">
                <div class="mb-3">
                    <label class="form-label"><small><b>Tgl Awal</b></small></label>
                    <input type="date" class="form-control form-control-sm " id="tgl-awal" name="tgl_awal"
                        value="{{ date('Y-m-d') }}">
                </div>
                <div class="mb-3">
                    <label class="form-label"><small><b>Tgl Akhir</b></small></label>
                    <input type="date" class="form-control form-control-sm" id="tgl-akhir" name="tgl_akhir"
                        value="{{ date('Y-m-d') }}">
                </div>
                <div class="mb-3">
                    <a onclick="dataTableReload()" class="btn btn-outline-primary btn-sm position-relative">
                        <i class="fas fa-search fa-xs"></i> <!-- Use fa-xs for extra small icon -->
                    </a>
                </div>
                <div class="mb-3">
                    <a onclick="export_excel()" class="btn btn-outline-success position-relative btn-sm">
                        <i class="fas fa-file-excel fa-sm"></i>
                        Export Excel
                    </a>
                </div>
            </div>

            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-striped table-sm table-hover w-100 text-nowrap">
                    <thead>
                        <tr style='text-align:center; vertical-align:middle'>
                            <th colspan="5" style="background-color: lightblue; text-align:center;">Jenis Produk</th>
                            <th colspan="11" style="background-color: lightgreen; text-align:center;">Sewing</th>
                            <th colspan="4" style="background-color: lightsteelblue; text-align:center;">Steam</th>
                            <th colspan="4" style="background-color: Lavender; text-align:center;">Defect Sewing</th>
                            <th colspan="4" style="background-color: Lavender; text-align:center;">Defect Spotcleaning
                            </th>
                            <th colspan="4" style="background-color: Lavender; text-align:center;">Defect Mending</th>
                            </th>
                            <th colspan="4" style="background-color:#FFE5B4; text-align:center;">Defect Packing</th>
                            <th colspan="4" style="background-color:#FFE5B4; text-align:center;">Defect Packing
                                Spotcleaning</th>
                            <th colspan="4" style="background-color:#FFE5B4; text-align:center;">Defect Packing Mending
                            </th>
                        </tr>
                        <tr style='text-align:center; vertical-align:middle'>
                            <th style="background-color: lightblue;">WS</th>
                            <th style="background-color: lightblue;">Buyer</th>
                            <th style="background-color: lightblue;">Style</th>
                            <th style="background-color: lightblue;">Color</th>
                            <th style="background-color: lightblue;">Size</th>
                            <th style="background-color: lightgreen;">Saldo Awal</th>
                            <th style="background-color: lightgreen;">Terima Loading</th>
                            <th style="background-color: lightgreen;">Defect Sewing</th>
                            <th style="background-color: lightgreen;">Defect Spot Cleaning</th>
                            <th style="background-color: lightgreen;">Defect Mending</th>
                            <th style="background-color: lightgreen;">Rework Sewing</th>
                            <th style="background-color: lightgreen;">Rework Spot Cleaning</th>
                            <th style="background-color: lightgreen;">Rework Mending</th>
                            <th style="background-color: lightgreen;">Reject</th>
                            <th style="background-color: lightgreen;">Output</th>
                            <th style="background-color: lightgreen;">Saldo Akhir</th>
                            <th style="background-color: lightsteelblue;">Saldo Awal</th>
                            <th style="background-color: lightsteelblue;">Terima</th>
                            <th style="background-color: lightsteelblue;">Keluar</th>
                            <th style="background-color: lightsteelblue;">Saldo Akhir</th>
                            <th style="background-color: Lavender;">Saldo Awal</th>
                            <th style="background-color: Lavender;">Terima</th>
                            <th style="background-color: Lavender;">Keluar</th>
                            <th style="background-color: Lavender;">Saldo Akhir</th>
                            <th style="background-color: Lavender;">Saldo Awal</th>
                            <th style="background-color: Lavender;">Terima</th>
                            <th style="background-color: Lavender;">Keluar</th>
                            <th style="background-color: Lavender;">Saldo Akhir</th>
                            <th style="background-color: Lavender;">Saldo Awal</th>
                            <th style="background-color: Lavender;">Terima</th>
                            <th style="background-color: Lavender;">Keluar</th>
                            <th style="background-color: Lavender;">Saldo Akhir</th>
                            <th style="background-color: #FFE5B4;">Saldo Awal</th>
                            <th style="background-color: #FFE5B4;">Terima</th>
                            <th style="background-color: #FFE5B4;">Keluar</th>
                            <th style="background-color: #FFE5B4;">Saldo Akhir</th>
                            <th style="background-color: #FFE5B4;">Saldo Awal</th>
                            <th style="background-color: #FFE5B4;">Terima</th>
                            <th style="background-color: #FFE5B4;">Keluar</th>
                            <th style="background-color: #FFE5B4;">Saldo Akhir</th>
                            <th style="background-color: #FFE5B4;">Saldo Awal</th>
                            <th style="background-color: #FFE5B4;">Terima</th>
                            <th style="background-color: #FFE5B4;">Keluar</th>
                            <th style="background-color: #FFE5B4;">Saldo Akhir</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr style='text-align:center; vertical-align:middle'>
                            <th colspan="5">Total </th>
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
        function notif() {
            alert("Maaf, Fitur belum tersedia!");
        }
    </script>
    <script>
        $(document).ready(function() {
            let datatable = new DataTable('#datatable');
            datatable.clear().draw();
        })

        function dataTableReload() {
            if ($.fn.DataTable.isDataTable('#datatable')) {
                // Destroy the existing DataTable
                $('#datatable').DataTable().destroy();
            }

            // Re-initialize the DataTable
            let tglawal = $('#tgl-awal').val();
            let tglakhir = $('#tgl-akhir').val();
            let dateFrom = tglawal + ' 00:00:00';
            let dateTo = tglakhir + ' 23:59:59';
            console.log(dateFrom, dateTo);
            datatable = $("#datatable").DataTable({
                processing: true,
                serverSide: true,
                scrollY: "450px",
                scrollX: true,
                scrollCollapse: true,
                deferRender: true,
                paging: true,
                ordering: false,
                fixedColumns: {
                    leftColumns: 5 // Fix the first three columns
                },
                ajax: {
                    url: '{{ route('show_mut_output') }}',
                    type: 'post',
                    data: function(d) {
                        d.dateFrom = dateFrom;
                        d.dateTo = dateTo;
                    },
                },
                columns: [{
                        data: 'kpno'
                    },
                    {
                        data: 'buyer'
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
                        data: 'saldo_awal_sewing'
                    },
                    {
                        data: 'qty_loading'
                    },
                    {
                        data: 'defect_sewing'
                    },
                    {
                        data: 'defect_spotcleaning'
                    },
                    {
                        data: 'defect_mending'
                    },
                    {
                        data: 'input_rework_sewing'
                    },
                    {
                        data: 'input_rework_spotcleaning'
                    },
                    {
                        data: 'input_rework_mending'
                    },
                    {
                        data: 'output_rejects'
                    },
                    {
                        data: 'output_rfts'
                    },
                    {
                        data: 'saldo_akhir'
                    },
                    {
                        data: 'saldo_awal_steam'
                    },
                    {
                        data: 'input_steam'
                    },
                    {
                        data: 'output_steam'
                    },
                    {
                        data: 'saldo_akhir_steam'
                    },
                    {
                        data: 'saldo_awal_def_sew'
                    },
                    {
                        data: 'defect_trans_sewing'
                    },
                    {
                        data: 'defect_trans_rew_sewing'
                    },
                    {
                        data: 'saldo_akhir_def_sewing'
                    },
                    {
                        data: 'saldo_awal_def_spotcleaning'
                    },
                    {
                        data: 'defect_trans_spotcleaning'
                    },
                    {
                        data: 'defect_trans_rew_spotcleaning'
                    },
                    {
                        data: 'saldo_akhir_def_spotcleaning'
                    },
                    {
                        data: 'saldo_awal_def_mending'
                    },
                    {
                        data: 'defect_trans_mending'
                    },
                    {
                        data: 'defect_trans_rew_mending'
                    },
                    {
                        data: 'saldo_akhir_def_mending'
                    },
                    {
                        data: 'saldo_awal_def_sew_pck'
                    },
                    {
                        data: 'defect_trans_sewing_pck'
                    },
                    {
                        data: 'defect_trans_rew_sewing_pck'
                    },
                    {
                        data: 'saldo_akhir_def_sewing_pck'
                    },
                    {
                        data: 'saldo_awal_def_spotcleaning_pck'
                    },
                    {
                        data: 'defect_trans_spotcleaning_pck'
                    },
                    {
                        data: 'defect_trans_rew_spotcleaning_pck'
                    },
                    {
                        data: 'saldo_akhir_def_spotcleaning_pck'
                    },
                    {
                        data: 'saldo_awal_def_mending_pck'
                    },
                    {
                        data: 'defect_trans_mending_pck'
                    },
                    {
                        data: 'defect_trans_rew_mending_pck'
                    },
                    {
                        data: 'saldo_akhir_def_mending_pck'
                    },

                ],
                columnDefs: [{
                    "className": "align-middle",
                    "targets": "_all"
                }],
                footerCallback: function(row, data, start, end, display) {
                    let api = this.api();
                    let totalColumns = api.columns().header().length; // Get the total number of columns
                    // Loop through each column
                    for (let i = 5; i < totalColumns; i++) {
                        let columnData = api.column(i).data().toArray(); // Convert to array if not already
                        // console.log(columnData); // Log the column data to inspect its structure
                        // Check if the column data is numeric
                        if (columnData.length > 0 && columnData.every(value => !isNaN(value))) {
                            let total = columnData.reduce(function(a, b) {
                                return parseFloat(a) + parseFloat(b);
                            }, 0);
                            // Update the footer with the total for each column
                            $(api.column(i).footer()).html(total);
                        } else {
                            // If not numeric, you can set the footer to an empty string or a specific message
                            $(api.column(i).footer()).html('');
                        }
                    }
                },
            });
        }

        function export_excel() {
            let tglawal = $('#tgl-awal').val();
            let tglakhir = $('#tgl-akhir').val();
            let dateFrom = tglawal + ' 00:00:00';
            let dateTo = tglakhir + ' 23:59:59';

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
                url: '{{ route('export_excel_mut_output') }}',
                data: {
                    dateFrom: dateFrom,
                    dateTo: dateTo
                },
                success: function(data) {
                    // Create a new workbook and a worksheet
                    const workbook = new ExcelJS.Workbook();
                    const worksheet = workbook.addWorksheet("Mutasi Output Production ");

                    // Add a main title row above the Tgl Transaksi
                    const mainTitleRow = worksheet.addRow(["Laporan Mutasi Saldo WIP"]);
                    // Center align the main title row
                    worksheet.getCell(`A${mainTitleRow.number}`).alignment = {
                        horizontal: 'center',
                        vertical: 'middle'
                    };
                    // Optionally, you can merge cells for the main title
                    worksheet.mergeCells(`A${mainTitleRow.number}:E${mainTitleRow.number}`);
                    mainTitleRow.font = {
                        bold: true,
                        size: 14
                    };

                    // Add a title row for Tgl Transaksi without borders
                    const titleRow = worksheet.addRow([`Tgl Transaksi: ${tglawal} - ${tglakhir}`]);
                    // Center align the title row
                    worksheet.getCell(`A${titleRow.number}`).alignment = {
                        horizontal: 'center',
                        vertical: 'middle'
                    };
                    // Set border to null for the title row
                    titleRow.eachCell((cell) => {
                        cell.border = null;
                    });

                    // Add an empty row for spacing
                    worksheet.addRow([]);
                    const headerRow = worksheet.addRow([
                        "Jenis Produk", "", "", "", "",
                        "Sewing", "", "", "", "", "", "", "", "", "", "",
                        "Steam", "", "", "",
                        "Defect Sewing", "", "", "",
                        "Defect Spotcleaning", "", "", "",
                        "Defect Mending", "", "", "",
                        "Defect Packing Sewing", "", "", "",
                        "Defect Packing Spotcleaning", "", "", "",
                        "Defect Packing Mending", "", "", "",
                    ]);

                    // Merge cells for the first header row
                    worksheet.mergeCells(`A${headerRow.number}:E${headerRow.number}`); // Merge "Jenis Produk"
                    worksheet.mergeCells(`F${headerRow.number}:P${headerRow.number}`); // Merge "Packing Line"
                    worksheet.mergeCells(`Q${headerRow.number}:T${headerRow.number}`);
                    worksheet.mergeCells(`U${headerRow.number}:X${headerRow.number}`);
                    worksheet.mergeCells(`Y${headerRow.number}:AB${headerRow.number}`);
                    worksheet.mergeCells(`AC${headerRow.number}:AF${headerRow.number}`);
                    worksheet.mergeCells(`AG${headerRow.number}:AJ${headerRow.number}`);
                    worksheet.mergeCells(`AK${headerRow.number}:AN${headerRow.number}`);
                    worksheet.mergeCells(`AO${headerRow.number}:AR${headerRow.number}`);

                    // Define the second header row
                    // Center align the merged cells
                    worksheet.getCell(`A${headerRow.number}`).alignment = {
                        horizontal: 'center',
                        vertical: 'middle'
                    };
                    worksheet.getCell(`F${headerRow.number}`).alignment = {
                        horizontal: 'center',
                        vertical: 'middle'
                    };
                    worksheet.getCell(`Q${headerRow.number}`).alignment = {
                        horizontal: 'center',
                        vertical: 'middle'
                    };
                    worksheet.getCell(`U${headerRow.number}`).alignment = {
                        horizontal: 'center',
                        vertical: 'middle'
                    };
                    worksheet.getCell(`Y${headerRow.number}`).alignment = {
                        horizontal: 'center',
                        vertical: 'middle'
                    };
                    worksheet.getCell(`AC${headerRow.number}`).alignment = {
                        horizontal: 'center',
                        vertical: 'middle'
                    };
                    worksheet.getCell(`AG${headerRow.number}`).alignment = {
                        horizontal: 'center',
                        vertical: 'middle'
                    };
                    worksheet.getCell(`AK${headerRow.number}`).alignment = {
                        horizontal: 'center',
                        vertical: 'middle'
                    };
                    worksheet.getCell(`AO${headerRow.number}`).alignment = {
                        horizontal: 'center',
                        vertical: 'middle'
                    };
                    const headers = [
                        "WS", "Buyer", "Style", "Color", "Size",
                        "Saldo Awal", "Terima Dari Loading", "Rework Sewing",
                        "Rework Spot Cleaning", "Rework Mending", "Defect Sewing",
                        "Defect Spot Cleaning", "Defect Mending", "Reject", "Output", "Saldo Akhir",
                        "Saldo Awal", "Terima", "Keluar", "Saldo Akhir",
                        "Saldo Awal", "Terima", "Keluar", "Saldo Akhir",
                        "Saldo Awal", "Terima", "Keluar", "Saldo Akhir",
                        "Saldo Awal", "Terima", "Keluar", "Saldo Akhir",
                        "Saldo Awal", "Terima", "Keluar", "Saldo Akhir",
                        "Saldo Awal", "Terima", "Keluar", "Saldo Akhir",
                        "Saldo Awal", "Terima", "Keluar", "Saldo Akhir",
                    ];
                    worksheet.addRow(headers);

                    // Add data rows
                    data.forEach(function(row) {
                        worksheet.addRow([
                            row.kpno,
                            row.buyer,
                            row.styleno,
                            row.color,
                            row.size,
                            row.saldo_awal_sewing,
                            row.qty_loading,
                            row.input_rework_sewing,
                            row.input_rework_spotcleaning,
                            row.input_rework_mending,
                            row.defect_sewing,
                            row.defect_spotcleaning,
                            row.defect_mending,
                            row.output_rejects,
                            row.output_rfts,
                            row.saldo_akhir,
                            row.saldo_awal_steam,
                            row.input_steam,
                            row.output_steam,
                            row.saldo_akhir_steam,
                            row.saldo_awal_def_sew,
                            row.defect_trans_sewing,
                            row.defect_trans_rew_sewing,
                            row.saldo_akhir_def_sewing,
                            row.saldo_awal_def_spotcleaning,
                            row.defect_trans_spotcleaning,
                            row.defect_trans_rew_spotcleaning,
                            row.saldo_akhir_def_spotcleaning,
                            row.saldo_awal_def_mending,
                            row.defect_trans_mending,
                            row.defect_trans_rew_mending,
                            row.saldo_akhir_def_mending,
                            row.saldo_awal_def_sew_pck,
                            row.defect_trans_sewing_pck,
                            row.defect_trans_rew_sewing_pck,
                            row.saldo_akhir_def_sewing_pck,
                            row.saldo_awal_def_spotcleaning_pck,
                            row.defect_trans_spotcleaning_pck,
                            row.defect_trans_rew_spotcleaning_pck,
                            row.saldo_akhir_def_spotcleaning_pck,
                            row.saldo_awal_def_mending_pck,
                            row.defect_trans_mending_pck,
                            row.defect_trans_rew_mending_pck,
                            row.saldo_akhir_def_mending_pck
                        ]);
                    });

                    // Apply border style to all cells except title and A3

                    worksheet.eachRow({
                        includeEmpty: true
                    }, function(row, rowNumber) {
                        if (rowNumber !== mainTitleRow.number && rowNumber !== titleRow.number &&
                            rowNumber !== 3) {
                            row.eachCell({
                                includeEmpty: true
                            }, function(cell, colNumber) {
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
                        }
                    });

                    // Export the workbook
                    workbook.xlsx.writeBuffer().then(function(buffer) {
                        const blob = new Blob([buffer], {
                            type: "application/octet-stream"
                        });
                        const link = document.createElement('a');
                        link.href = window.URL.createObjectURL(blob);
                        link.download = "Laporan Mutasi Output.xlsx";
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
