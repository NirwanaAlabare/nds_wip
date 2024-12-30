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
                <table id="datatable" class="table table-bordered table-striped table-sm w-100 text-nowrap">
                    <thead>
                        <tr style='text-align:center; vertical-align:middle'>
                            <th colspan="5" style="background-color: lightblue; text-align:center;">Jenis Produk</th>
                            <th colspan="12" style="background-color: lightgreen; text-align:center;">Sewing</th>
                            <th colspan="4" style="background-color: lightsteelblue; text-align:center;">Steam</th>
                        </tr>
                        <tr style='text-align:center; vertical-align:middle'>
                            <th style="background-color: lightblue;">WS</th>
                            <th style="background-color: lightblue;">Buyer</th>
                            <th style="background-color: lightblue;">Style</th>
                            <th style="background-color: lightblue;">Color</th>
                            <th style="background-color: lightblue;">Size</th>
                            <th style="background-color: lightgreen;">Saldo Awal</th>
                            <th style="background-color: lightgreen;">Terima Loading</th>
                            <th style="background-color: lightgreen;">Rework Sewing</th>
                            <th style="background-color: lightgreen;">Rework Mending</th>
                            <th style="background-color: lightgreen;">Rework Spot Cleaning</th>
                            <th style="background-color: lightgreen;">Defect Sewing</th>
                            <th style="background-color: lightgreen;">Defect Mending</th>
                            <th style="background-color: lightgreen;">Defect Spot Cleaning</th>
                            <th style="background-color: lightgreen;">Reject</th>
                            <th style="background-color: lightgreen;">Keluar RFT</th>
                            <th style="background-color: lightgreen;">Keluar Rework</th>
                            <th style="background-color: lightgreen;">Saldo Akhir</th>
                            <th style="background-color: lightsteelblue;">Saldo Awal</th>
                            <th style="background-color: lightsteelblue;">Terima</th>
                            <th style="background-color: lightsteelblue;">Keluar</th>
                            <th style="background-color: lightsteelblue;">Saldo Akhir</th>
                        </tr>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/exceljs/4.2.0/exceljs.min.js"></script>
    <script>
        function notif() {
            alert("Maaf, Fitur belum tersedia!");
        }
    </script>
    <script>
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
                    url: '{{ route('report_mut_output') }}',
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
                        data: 'sa_sewing'
                    },
                    {
                        data: 'qty_loading'
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
                        data: 'output_def_sewing'
                    },
                    {
                        data: 'output_def_spotcleaning'
                    },
                    {
                        data: 'output_def_mending'
                    },
                    {
                        data: 'qty_reject'
                    },
                    {
                        data: 'out_sew_rft'
                    },
                    {
                        data: 'out_sew_rework'
                    },
                    {
                        data: 'saldo_akhir_qc_line'
                    },
                    {
                        data: 'sa_steam'
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

                ],
                columnDefs: [{
                    "className": "align-middle",
                    "targets": "_all"
                }],

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
                type: "GET",
                url: '{{ route('export_excel_mut_output') }}',
                data: {
                    dateFrom: dateFrom,
                    dateTo: dateTo
                },
                success: function(data) {
                    // Create a new workbook and a worksheet
                    const workbook = new ExcelJS.Workbook();
                    const worksheet = workbook.addWorksheet("Mutasi Packing Out");
                    // Add a title row for Tgl Transaksi without borders
                    const titleRow = worksheet.addRow([`Tgl Transaksi: ${tglawal} - ${tglakhir}`]);
                    // Center align the title row
                    worksheet.getCell(`A${titleRow.number}`).alignment = {
                        horizontal: 'center',
                        vertical: 'middle'
                    };
                    // Add an empty row for spacing
                    worksheet.addRow([]);
                    const headerRow = worksheet.addRow([
                        "Jenis Produk", "", "", "", "",
                        "Sewing", "", "", "", "", "", "", "", "", "", "", "",
                        "Steam", "", "", "",
                    ]);

                    // Merge cells for the first header row
                    worksheet.mergeCells(`A${headerRow.number}:E${headerRow.number}`); // Merge "Jenis Produk"
                    worksheet.mergeCells(`F${headerRow.number}:P${headerRow.number}`); // Merge "Packing Line"
                    worksheet.mergeCells(`R${headerRow.number}:U${headerRow.number}`);

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
                    const headers = [
                        "WS", "Buyer", "Style", "Color", "Size",
                        "Saldo Awal", "Terima Dari Loading", "Rework Sewing",
                        "Rework Spot Cleaning", "Rework Mending", "Defect Sewing",
                        "Defect Spot Cleaning", "Defect Mending", "Reject",
                        "Keluar RFT", "Keluar Rework", "Saldo Akhir", "Saldo Awal",
                        "Terima", "Keluar", "Saldo Akhir"
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
                            row.sa_sewing,
                            row.qty_loading,
                            row.input_rework_sewing,
                            row.input_rework_spotcleaning,
                            row.input_rework_mending,
                            row.output_def_sewing,
                            row.output_def_spotcleaning,
                            row.output_def_mending,
                            row.qty_reject,
                            row.out_sew_rft,
                            row.out_sew_rework,
                            row.saldo_akhir_qc_line,
                            row.sa_steam,
                            row.input_steam,
                            row.output_steam,
                            row.saldo_akhir_steam
                        ]);
                    });

                    // Apply border style to all cells
                    worksheet.eachRow({
                        includeEmpty: true
                    }, function(row, rowNumber) {
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
