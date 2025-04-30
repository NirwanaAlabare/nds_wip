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
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-chart-line"></i> Laporan Saldo WIP</h5>
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
                    <a onclick="dataTableReload();" class="btn btn-outline-primary position-relative btn-sm">
                        <i class="fas fa-search fa-sm"></i>
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
                <table id="datatable" class="table table-bordered table-striped 100 text-nowrap">
                    <thead class="table-primary                          <tr class="text-center">
                            <th>WS</th>
                            <th>Saldo Awal</th>
                            <th>IN</th>
                            <th>OUT</th>
                            <th>Saldo Akhir</th>
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
        $(document).ready(function() {
            let datatable = new DataTable('#datatable');
            datatable.clear().draw();
        })

        function dataTableReload() {

            if ($.fn.DataTable.isDataTable('#datatable')) {
                // Destroy the existing DataTable
                $('#datatable').DataTable().destroy();
            }
            let tglawal = $('#tgl-awal').val();
            let tglakhir = $('#tgl-akhir').val();
            let dateFrom = tglawal + ' 00:00:00';
            let dateTo = tglakhir + ' 23:59:59';
            let datatable = $("#datatable").DataTable({
                processing: true,
                serverSide: true,
                scrollY: "450px",
                scrollX: true,
                scrollCollapse: true,
                deferRender: true,
                paging: true,
                ordering: false,
                ajax: {
                    url: '{{ route('show_report_doc_lap_wip') }}',
                    dataType: 'json',
                    dataSrc: 'data',
                    data: function(d) {
                        d.dateFrom = dateFrom;
                        d.dateTo = dateTo;
                    },
                },
                columns: [{
                        data: 'ws'

                    },
                    {
                        data: 'saldo_awal'
                    },
                    {
                        data: 'dc_in'
                    },
                    {
                        data: 'pck_out'
                    },
                    {
                        data: 'saldo_akhir'
                    },
                ],
                columnDefs: [{
                        "className": "align-left",
                        "targets": 0
                    },
                    {
                        "className": "text-right",
                        "targets": [1, 2, 3, 4]
                    }
                ]
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
                url: '{{ route('export_excel_doc_lap_wip') }}',
                data: {
                    dateFrom: dateFrom,
                    dateTo: dateTo
                },
                success: function(data) {
                    // Create a new workbook and a worksheet
                    const workbook = new ExcelJS.Workbook();
                    const worksheet = workbook.addWorksheet("Mutasi Saldo WIP ");

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
                    }; // Make the title bold and larger

                    // Add a title row for Tgl Transaksi without borders
                    const titleRow = worksheet.addRow([`Tgl Transaksi: ${tglawal} - ${tglakhir}`]);

                    // Center align the title row
                    worksheet.getCell(`A${titleRow.number}`).alignment = {
                        horizontal: 'center',
                        vertical: 'middle'
                    };

                    titleRow.font = {
                        bold: true
                    }; // Make the title bold


                    const headers = [
                        "WS", "Saldo Awal", "IN", "OUT", "Saldo Akhir"
                    ];
                    worksheet.addRow(headers);

                    // Add data rows
                    data.forEach(function(row) {
                        worksheet.addRow([
                            row.ws,
                            row.saldo_awal,
                            row.dc_in,
                            row.pck_out,
                            row.saldo_akhir,
                            row.sa_sewing
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
                        link.download = "Laporan Mutasi Saldo WIP.xlsx";
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
