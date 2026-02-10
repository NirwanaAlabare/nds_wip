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
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-list"></i> Report Mutasi Packing (WIP)</h5>
        </div>
        <div class="card-body">
            <div class="d-flex align-items-end gap-3 mb-3">
                <div class="mb-3">
                    <label class="form-label"><small><b>Tgl Awal</b></small></label>
                    <input type="date" class="form-control form-control-sm " id="tgl-awal" name="tgl_awal"
                        value="{{ date('Y-m-d') }}" min="2026-01-01">
                </div>
                <div class="mb-3">
                    <label class="form-label"><small><b>Tgl Akhir</b></small></label>
                    <input type="date" class="form-control form-control-sm" id="tgl-akhir" name="tgl_akhir"
                        value="{{ date('Y-m-d') }}" min="2026-01-01">
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
                {{-- <table id="datatable" class="table table-bordered table-striped w-100 text-nowrap">
                    <thead>
                        <tr style='text-align:center; vertical-align:middle'>
                            <th colspan="5" style="background-color: lightblue; text-align:center;">Jenis Produk</th>
                            <th colspan="12" style="background-color: lightgreen; text-align:center;">Packing Line</th>
                            <th colspan="5" style="background-color: lightsteelblue; text-align:center;">Transfer Garment
                            </th>
                            <th colspan="5" style="background-color: lightgoldenrodyellow; text-align:center;">Packing
                                Central</th>
                        </tr>
                        <tr style='text-align:center; vertical-align:middle'>
                            <th style="background-color: lightblue;">WS</th>
                            <th style="background-color: lightblue;">Buyer</th>
                            <th style="background-color: lightblue;">Style</th>
                            <th style="background-color: lightblue;">Color</th>
                            <th style="background-color: lightblue;">Size</th>
                            <th style="background-color: lightgreen;">Saldo Awal</th>
                            <th style="background-color: lightgreen;">Terima Dari Steam</th>
                            <th style="background-color: lightgreen;">Defect Sewing</th>
                            <th style="background-color: lightgreen;">Defect Spot Cleaning</th>
                            <th style="background-color: lightgreen;">Defect Mending</th>
                            <th style="background-color: lightgreen;">Output Rework Sewing</th>
                            <th style="background-color: lightgreen;">Output Spot Cleaning</th>
                            <th style="background-color: lightgreen;">Output Mending</th>
                            <th style="background-color: lightgreen;">Reject</th>
                            <th style="background-color: lightgreen;">Keluar</th>
                            <th style="background-color: lightgreen;">Adj</th>
                            <th style="background-color: lightgreen;">Saldo Akhir</th>
                            <th style="background-color: lightsteelblue;">Saldo Awal</th>
                            <th style="background-color: lightsteelblue;">Terima</th>
                            <th style="background-color: lightsteelblue;">Keluar</th>
                            <th style="background-color: lightsteelblue;">Adj</th>
                            <th style="background-color: lightsteelblue;">Saldo Akhir</th>
                            <th style="background-color: lightgoldenrodyellow;">Saldo Awal</th>
                            <th style="background-color: lightgoldenrodyellow;">Terima</th>
                            <th style="background-color: lightgoldenrodyellow;">Keluar</th>
                            <th style="background-color: lightgoldenrodyellow;">Adj</th>
                            <th style="background-color: lightgoldenrodyellow;">Saldo Akhir</th>
                        </tr>
                    </thead>
                </table> --}}
                <table id="datatable" class="table table-bordered table-striped w-100 text-nowrap">
                    <thead>
                        <tr style='text-align:center; vertical-align:middle'>
                            <th colspan="5" style="background-color: lightblue; text-align:center;">Jenis Produk</th>
                            <th colspan="5" style="background-color: lightgreen; text-align:center;">Packing Line</th>
                            <th colspan="4" style="background-color: lightsteelblue; text-align:center;">Transfer Garment
                            </th>
                            <th colspan="4" style="background-color: lightgoldenrodyellow; text-align:center;">Packing
                                Central</th>
                        </tr>
                        <tr style='text-align:center; vertical-align:middle'>
                            <th style="background-color: lightblue;">WS</th>
                            <th style="background-color: lightblue;">Buyer</th>
                            <th style="background-color: lightblue;">Style</th>
                            <th style="background-color: lightblue;">Color</th>
                            <th style="background-color: lightblue;">Size</th>
                            <th style="background-color: lightgreen;">Saldo Awal</th>
                            <th style="background-color: lightgreen;">Terima RTF</th>
                            <th style="background-color: lightgreen;">Terima Reject</th>
                            <th style="background-color: lightgreen;">Keluar</th>
                            <th style="background-color: lightgreen;">Saldo Akhir</th>
                            <th style="background-color: lightsteelblue;">Saldo Awal</th>
                            <th style="background-color: lightsteelblue;">Terima</th>
                            <th style="background-color: lightsteelblue;">Keluar</th>
                            <th style="background-color: lightsteelblue;">Saldo Akhir</th>
                            <th style="background-color: lightgoldenrodyellow;">Saldo Awal</th>
                            <th style="background-color: lightgoldenrodyellow;">Terima</th>
                            <th style="background-color: lightgoldenrodyellow;">Packing Scan</th>
                            <th style="background-color: lightgoldenrodyellow;">Saldo Akhir</th>
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
            let datatable = new DataTable('#datatable');
            datatable.clear().draw();
        })

        function dataTableReload() {

        }

        // function dataTableReload() {
        //     if ($.fn.DataTable.isDataTable('#datatable')) {
        //         $('#datatable').DataTable().destroy();
        //     }

        //     let tglawal = $('#tgl-awal').val();
        //     let tglakhir = $('#tgl-akhir').val();
        //     datatable = $("#datatable").DataTable({
        //         processing: true,
        //         serverSide: false,
        //         scrollY: "450px",
        //         scrollX: true,
        //         scrollCollapse: true,
        //         deferRender: true,
        //         paging: true,
        //         ordering: false,
        //         fixedColumns: {
        //             leftColumns: 5 // Fix the first three columns
        //         },
        //         ajax: {
        //             url: '{{ route('packing_rep_packing_mutasi_wip') }}',
        //             data: function(d) {
        //                 d.dateFrom = tglawal;
        //                 d.dateTo = tglakhir;
        //             },
        //         },
        //         columns: [{
        //                 data: 'ws'
        //             },
        //             {
        //                 data: 'buyer'
        //             },
        //             {
        //                 data: 'styleno'
        //             },
        //             {
        //                 data: 'color'
        //             },
        //             {
        //                 data: 'size'
        //             },
        //             {
        //                 data: 'packing_line_awal'
        //             },
        //             {
        //                 data: 'in_pck_line'
        //             },
        //             {
        //                 data: 'defect_sewing_akhir'
        //             },
        //             {
        //                 data: 'defect_spotcleaning_akhir'
        //             },
        //             {
        //                 data: 'defect_mending_akhir'
        //             },
        //             {
        //                 data: 'input_rework_sewing'
        //             },
        //             {
        //                 data: 'input_rework_spotcleaning'
        //             },
        //             {
        //                 data: 'input_rework_mending'
        //             },
        //             {
        //                 data: 'qty_pck_reject'
        //             },
        //             {
        //                 data: 'out_pck_line'
        //             },
        //             {
        //                 data: 'adj_pck_line_akhir'
        //             },
        //             {
        //                 data: 'saldo_akhir_pck_line'
        //             },
        //             {
        //                 data: 'saldo_awal_trf_garment'
        //             },
        //             {
        //                 data: 'in_trf_garment'
        //             },
        //             {
        //                 data: 'out_trf_garment'
        //             },
        //             {
        //                 data: 'adj_trf_garment'
        //             },
        //             {
        //                 data: 'saldo_akhir_trf_garment'
        //             },
        //             {
        //                 data: 'saldo_awal_packing_central'
        //             },
        //             {
        //                 data: 'in_packing_central'
        //             },
        //             {
        //                 data: 'out_packing_central'
        //             },
        //             {
        //                 data: 'adj_packing_central'
        //             },
        //             {
        //                 data: 'saldo_akhir_pck_central'
        //             },

        //         ],
        //         columnDefs: [{
        //             "className": "align-middle",
        //             "targets": "_all"
        //         }],

        //     });
        // }

        function dataTableReload() {

            if ($.fn.DataTable.isDataTable('#datatable')) {
                $('#datatable').DataTable().destroy();
            }

            let tglawal = $('#tgl-awal').val();
            let tglakhir = $('#tgl-akhir').val();
            datatable = $("#datatable").DataTable({
                processing: true,
                serverSide: false,
                scrollY: "450px",
                scrollX: true,
                scrollCollapse: false,
                deferRender: true,
                paging: true,
                ordering: false,
                pageLength: 25,
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "All"]
                ],
                fixedColumns: {
                    leftColumns: 5 // Fix the first three columns
                },
                ajax: {
                    url: '{{ route('packing_rep_packing_mutasi_wip') }}',
                    data: function(d) {
                        d.dateFrom = tglawal;
                        d.dateTo = tglakhir;
                    },
                },
                columns: [{
                        data: 'ws'
                    },
                    {
                        data: 'buyer'
                    },
                    {
                        data: 'style'
                    },
                    {
                        data: 'color'
                    },
                    {
                        data: 'size'
                    },
                    {
                        data: 'pl_saldo_awal'
                    },
                    {
                        data: 'pl_rft'
                    },
                    {
                        data: 'pl_reject'
                    },
                    {
                        data: 'pl_keluar'
                    },
                    {
                        data: 'pl_saldo_akhir'
                    },
                    {
                        data: 'tg_saldo_awal'
                    },
                    {
                        data: 'tg_masuk'
                    },
                    {
                        data: 'tg_keluar'
                    },
                    {
                        data: 'tg_saldo_akhir'
                    },
                    {
                        data: 'pc_saldo_awal'
                    },
                    {
                        data: 'pc_terima'
                    },
                    {
                        data: 'pc_packing_scan'
                    },
                    {
                        data: 'pc_saldo_akhir'
                    },

                ],
                columnDefs: [{
                    "className": "align-middle",
                    "targets": "_all"
                }],

            });
        }

        async function export_excel() {
            Swal.fire({
                title: "Exporting",
                html: "Please Wait...",
                timerProgressBar: true,
                didOpen: () => {
                    Swal.showLoading();
                },
            });

            try {
                const res = await $.ajax({
                    url: '{{ route('export_excel_rep_packing_mutasi_wip') }}',
                    type: "GET",
                    data: {
                        from: $("#tgl-awal").val(),
                        to: $("#tgl-akhir").val(),
                    },
                    xhrFields: { responseType: 'blob' }
                });

                Swal.close();

                iziToast.success({
                    title: 'Success',
                    message: 'Success',
                    position: 'topCenter'
                });

                const blob = new Blob([res]);
                const link = document.createElement('a');
                link.href = window.URL.createObjectURL(blob);
                link.download =
                    "Laporan Mutasi Packing (WIP) " +
                    $("#tgl-awal").val() +
                    " - " +
                    $("#tgl-akhir").val() +
                    ".xlsx";
                link.click();

            } catch (err) {
                Swal.close();
                console.error(err);

                iziToast.error({
                    title: 'Error',
                    message: 'Export gagal',
                    position: 'topCenter'
                });
            }
        }

        // function export_excel() {
        //     let tglawal = $('#tgl-awal').val();
        //     let tglakhir = $('#tgl-akhir').val();

        //     const startTime = new Date().getTime();

        //     Swal.fire({
        //         title: 'Please Wait...',
        //         html: 'Exporting Data...',
        //         didOpen: () => {
        //             Swal.showLoading();
        //         },
        //         allowOutsideClick: false,
        //     });

        //     $.ajax({
        //         type: "GET",
        //         url: '{{ route('export_excel_rep_packing_mutasi_wip') }}',
        //         data: {
        //             dateFrom: tglawal,
        //             dateTo: tglakhir
        //         },
        //         success: function(data) {
        //             const workbook = new ExcelJS.Workbook();
        //             const worksheet = workbook.addWorksheet("Packing List");

        //             // Title
        //             const titleRow = worksheet.addRow([`Tgl Transaksi: ${tglawal} - ${tglakhir}`]);
        //             worksheet.mergeCells(`A${titleRow.number}:AA${titleRow.number}`);
        //             worksheet.getCell(`A${titleRow.number}`).alignment = {
        //                 horizontal: 'center',
        //                 vertical: 'middle'
        //             };
        //             worksheet.addRow([]); // spacer row

        //             // First header row
        //             const groupHeader = worksheet.addRow([
        //                 "Jenis Produk", "", "", "", "",
        //                 "Packing Line", "", "", "", "", "", "", "", "", "", "", "",
        //                 "Transfer Garment", "", "", "", "",
        //                 "Packing Central", "", "", "", ""
        //             ]);

        //             worksheet.mergeCells(`A${groupHeader.number}:E${groupHeader.number}`);
        //             worksheet.mergeCells(`F${groupHeader.number}:Q${groupHeader.number}`);
        //             worksheet.mergeCells(`R${groupHeader.number}:V${groupHeader.number}`);
        //             worksheet.mergeCells(`W${groupHeader.number}:AA${groupHeader.number}`);

        //             ['A', 'F', 'R', 'W'].forEach(col => {
        //                 worksheet.getCell(`${col}${groupHeader.number}`).alignment = {
        //                     horizontal: 'center',
        //                     vertical: 'middle'
        //                 };
        //             });

        //             // Second header row
        //             const headers = [
        //                 "WS", "Buyer", "Style", "Color", "Size",
        //                 "Saldo Awal", "Terima Dari Steam", "Rework Sewing",
        //                 "Rework Mending", "Rework Spot Cleaning", "Defect Sewing",
        //                 "Defect Mending", "Defect Spot Cleaning", "Reject",
        //                 "Keluar", "Adj", "Saldo Akhir",
        //                 "Saldo Awal", "Terima", "Keluar", "Adj", "Saldo Akhir",
        //                 "Saldo Awal", "Terima", "Keluar", "Adj", "Saldo Akhir"
        //             ];
        //             worksheet.addRow(headers);

        //             // Data rows
        //             data.forEach(function(row) {
        //                 const values = [
        //                     row.ws,
        //                     row.buyer,
        //                     row.styleno,
        //                     row.color,
        //                     row.size,
        //                     Number(row.packing_line_awal),
        //                     Number(row.in_pck_line),
        //                     Number(row.input_rework_sewing),
        //                     Number(row.input_rework_mending),
        //                     Number(row.input_rework_spotcleaning),
        //                     Number(row.defect_sewing_akhir),
        //                     Number(row.defect_mending_akhir),
        //                     Number(row.defect_spotcleaning_akhir),
        //                     Number(row.qty_pck_reject),
        //                     Number(row.out_pck_line),
        //                     Number(row.adj_pck_line_akhir),
        //                     Number(row.saldo_akhir_pck_line),
        //                     Number(row.saldo_awal_trf_garment),
        //                     Number(row.in_trf_garment),
        //                     Number(row.out_trf_garment),
        //                     Number(row.adj_trf_garment),
        //                     Number(row.saldo_akhir_trf_garment),
        //                     Number(row.saldo_awal_packing_central),
        //                     Number(row.in_packing_central),
        //                     Number(row.out_packing_central),
        //                     Number(row.adj_packing_central),
        //                     Number(row.saldo_akhir_pck_central),
        //                 ];

        //                 const dataRow = worksheet.addRow(values);

        //                 dataRow.eachCell({
        //                     includeEmpty: true
        //                 }, function(cell, colNumber) {
        //                     if (colNumber >= 6 && colNumber <= 27) {
        //                         // Only format numeric columns
        //                         if (typeof cell.value === 'number' && !isNaN(cell.value)) {
        //                             cell.numFmt = '#,##0'; // thousand separator
        //                             cell.alignment = {
        //                                 horizontal: 'right'
        //                             };

        //                             if (cell.value < 0) {
        //                                 cell.font = {
        //                                     color: {
        //                                         argb: 'FFFF0000'
        //                                     }
        //                                 }; // Red for negative
        //                             }
        //                         }
        //                     }
        //                 });
        //             });

        //             // Apply borders to all cells
        //             worksheet.eachRow({
        //                 includeEmpty: true
        //             }, function(row) {
        //                 row.eachCell({
        //                     includeEmpty: true
        //                 }, function(cell) {
        //                     cell.border = {
        //                         top: {
        //                             style: 'thin'
        //                         },
        //                         left: {
        //                             style: 'thin'
        //                         },
        //                         bottom: {
        //                             style: 'thin'
        //                         },
        //                         right: {
        //                             style: 'thin'
        //                         }
        //                     };
        //                 });
        //             });

        //             // Export
        //             workbook.xlsx.writeBuffer().then(function(buffer) {
        //                 const blob = new Blob([buffer], {
        //                     type: "application/octet-stream"
        //                 });
        //                 const link = document.createElement('a');
        //                 link.href = window.URL.createObjectURL(blob);
        //                 link.download = "Laporan Mutasi Packing List.xlsx";
        //                 link.click();

        //                 const endTime = new Date().getTime();
        //                 const elapsedTime = Math.round((endTime - startTime) / 1000);

        //                 Swal.close();
        //                 Swal.fire({
        //                     title: 'Success!',
        //                     text: `Data has been successfully exported in ${elapsedTime} seconds.`,
        //                     icon: 'success',
        //                     confirmButtonText: 'Okay'
        //                 });
        //             });
        //         },
        //         error: function() {
        //             Swal.fire({
        //                 title: 'Error!',
        //                 text: 'There was an error exporting the data.',
        //                 icon: 'error',
        //                 confirmButtonText: 'Okay'
        //             });
        //         }
        //     });
        // }
    </script>
@endsection
