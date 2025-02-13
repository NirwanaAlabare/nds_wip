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
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-list"></i> Mutasi Packing List</h5>
        </div>
        <div class="card-body">

            <div class="d-flex align-items-end gap-3 mb-3">
                <div class="mb-3">
                    <a class="btn btn-outline-primary position-relative btn-sm" onclick="dataTableReload()">
                        <i class="fas fa-search"></i>
                        Cari
                    </a>
                </div>
                <div class="mb-3">
                    <a onclick="export_excel()" class="btn btn-outline-success position-relative btn-sm" id="but_export"
                        name="but_export">
                        <i class="fas fa-file-excel fa-sm"></i>
                        Export Excel
                    </a>
                </div>
            </div>
            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-striped table-sm w-100 text-nowrap">
                    <thead class="table-success">
                        <tr style='text-align:center; vertical-align:middle'>
                            <th>No. Carton</th>
                            <th>Barcode</th>
                            <th>PO</th>
                            <th>Buyer</th>
                            <th>WS</th>
                            <th>Color</th>
                            <th>Size</th>
                            <th>Dest</th>
                            <th>Qty PL</th>
                            <th>Qty Scan</th>
                            <th>Qty FG in</th>
                            <th>Qty FG Out</th>
                            <th>Lokasi</th>
                            <th>Balance</th>
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
    <script src="{{ asset('plugins/export_excel_js/exceljs.min.js') }}"></script>
    <script>
        function notif() {
            alert("Maaf, Fitur belum tersedia!");
        }
    </script>
    <script>
        $(document).ready(function() {
            dataTableReload();
        });

        function dataTableReload() {
            if ($.fn.DataTable.isDataTable('#datatable')) {
                // Destroy the existing DataTable
                $('#datatable').DataTable().destroy();
            }
            datatable = $("#datatable").DataTable({
                processing: true,
                serverSide: true,
                scrollY: "450px",
                scrollX: true,
                scrollCollapse: true,
                deferRender: true,
                paging: true,
                ordering: false,
                ajax: {
                    url: '{{ route('packing_rep_packing_mutasi_load') }}',
                },
                columns: [{
                        data: 'no_carton'
                    },
                    {
                        data: 'barcode'
                    },
                    {
                        data: 'po'

                    },
                    {
                        data: 'buyer'
                    },
                    {
                        data: 'ws'
                    },
                    {
                        data: 'color'
                    },
                    {
                        data: 'size'
                    },
                    {
                        data: 'dest'
                    },
                    {
                        data: 'qty_pl'
                    },
                    {
                        data: 'tot_scan'
                    },
                    {
                        data: 'qty_fg_in'
                    },
                    {
                        data: 'qty_fg_out'
                    },
                    {
                        data: 'lokasi'
                    },
                    {
                        data: 'balance'
                    },
                ],
                columnDefs: [{
                        "className": "dt-left",
                        "targets": [0, 1, 2, 3, 4] // Apply dt-left to columns 0 to 4
                    },
                    {
                        "className": "dt-right",
                        "targets": [7, 8, 9, 10, 11, 12, 13]
                    },
                    {
                        "width": "100px",
                        "targets": 0
                    }
                ]
            }, );
        }

        function export_excel() {
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
                url: '{{ route('export_excel_rep_packing_mutasi') }}',
                success: function(data) {
                    // Create a new workbook and a worksheet
                    const workbook = new ExcelJS.Workbook();
                    const worksheet = workbook.addWorksheet("Packing List");

                    // Define the header row
                    const headers = [
                        "No. Carton", "Barcode", "PO", "Buyer", "WS",
                        "Color", "Size", "Dest", "Qty PL", "Qty Scan",
                        "Qty FG in", "Qty FG Out", "Lokasi", "Balance"
                    ];
                    worksheet.addRow(headers);

                    // Add data rows
                    data.forEach(function(row) {
                        worksheet.addRow([
                            row.no_carton, row.barcode, row.po, row.buyer, row.ws,
                            row.color, row.size, row.dest, row.qty_pl, row.tot_scan,
                            row.qty_fg_in, row.qty_fg_out, row.lokasi, row.balance
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
                        link.download = "Laporan Mutasi Packing List.xlsx";
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
