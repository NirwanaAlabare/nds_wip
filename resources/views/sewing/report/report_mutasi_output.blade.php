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
            <div class="d-flex align-items-end gap-3 mb-3 ">
                <div class="mb-3" style="width: 300px;">
                    <label class="form-label"><small><b>Buyer</b></small></label>
                    <select class="form-control select2bs4 form-control-sm" id="cbobuyer" name="cbobuyer"
                        style="width: 100%;">
                        <option selected="selected" value="" disabled="true">Pilih Buyer</option>
                        <option value="">ALL</option>
                        @foreach ($data_buyer as $databuyer)
                            <option value="{{ $databuyer->isi }}">
                                {{ $databuyer->tampil }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label"><small><b>Tgl Awal</b></small></label>
                    <input type="date" class="form-control form-control-sm " id="tgl-awal" name="tgl_awal"
                        min="2025-01-01" value="{{ date('Y-m-d') }}">
                </div>
                <div class="mb-3">
                    <label class="form-label"><small><b>Tgl Akhir</b></small></label>
                    <input type="date" class="form-control form-control-sm" id="tgl-akhir" min="2025-01-01"
                        name="tgl_akhir" value="{{ date('Y-m-d') }}">
                </div>
                <div class="mb-3">
                    <a onclick="dataTableReload();" class="btn btn-outline-primary btn-sm position-relative">
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
                <table id="datatable" class="table table-bordered table-striped table table-hover w-100 text-nowrap">
                    <thead>
                        <tr style='text-align:center; vertical-align:middle'>
                            <th colspan="5" style="background-color: lightblue; text-align:center;">Jenis Produk</th>
                            <th colspan="12" style="background-color: lightgreen; text-align:center;">Sewing</th>
                            <th colspan="5" style="background-color: lightsteelblue; text-align:center;">Steam</th>
                            <th colspan="5" style="background-color: Lavender; text-align:center;">Defect Sewing</th>
                            <th colspan="5" style="background-color: Lavender; text-align:center;">Defect Spotcleaning
                            </th>
                            <th colspan="5" style="background-color: Lavender; text-align:center;">Defect Mending</th>
                            </th>
                            <th colspan="5" style="background-color:#FFE5B4; text-align:center;">Defect Packing</th>
                            <th colspan="5" style="background-color:#FFE5B4; text-align:center;">Defect Packing
                                Spotcleaning</th>
                            <th colspan="5" style="background-color:#FFE5B4; text-align:center;">Defect Packing Mending
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
                            <th style="background-color: lightgreen;">Output Rework Sewing</th>
                            <th style="background-color: lightgreen;">Output Spot Cleaning</th>
                            <th style="background-color: lightgreen;">Output Mending</th>
                            <th style="background-color: lightgreen;">Reject</th>
                            <th style="background-color: lightgreen;">Output</th>
                            <th style="background-color: lightgreen;">Adj</th>
                            <th style="background-color: lightgreen;">Saldo Akhir</th>
                            <th style="background-color: lightsteelblue;">Saldo Awal</th>
                            <th style="background-color: lightsteelblue;">Terima</th>
                            <th style="background-color: lightsteelblue;">Keluar</th>
                            <th style="background-color: lightsteelblue;">Adj</th>
                            <th style="background-color: lightsteelblue;">Saldo Akhir</th>
                            <th style="background-color: Lavender;">Saldo Awal</th>
                            <th style="background-color: Lavender;">Terima</th>
                            <th style="background-color: Lavender;">Keluar</th>
                            <th style="background-color: Lavender;">Adj</th>
                            <th style="background-color: Lavender;">Saldo Akhir</th>
                            <th style="background-color: Lavender;">Saldo Awal</th>
                            <th style="background-color: Lavender;">Terima</th>
                            <th style="background-color: Lavender;">Keluar</th>
                            <th style="background-color: Lavender;">Adj</th>
                            <th style="background-color: Lavender;">Saldo Akhir</th>
                            <th style="background-color: Lavender;">Saldo Awal</th>
                            <th style="background-color: Lavender;">Terima</th>
                            <th style="background-color: Lavender;">Keluar</th>
                            <th style="background-color: Lavender;">Adj</th>
                            <th style="background-color: Lavender;">Saldo Akhir</th>
                            <th style="background-color: #FFE5B4;">Saldo Awal</th>
                            <th style="background-color: #FFE5B4;">Terima</th>
                            <th style="background-color: #FFE5B4;">Keluar</th>
                            <th style="background-color: #FFE5B4;">Adj</th>
                            <th style="background-color: #FFE5B4;">Saldo Akhir</th>
                            <th style="background-color: #FFE5B4;">Saldo Awal</th>
                            <th style="background-color: #FFE5B4;">Terima</th>
                            <th style="background-color: #FFE5B4;">Keluar</th>
                            <th style="background-color: #FFE5B4;">Adj</th>
                            <th style="background-color: #FFE5B4;">Saldo Akhir</th>
                            <th style="background-color: #FFE5B4;">Saldo Awal</th>
                            <th style="background-color: #FFE5B4;">Terima</th>
                            <th style="background-color: #FFE5B4;">Keluar</th>
                            <th style="background-color: #FFE5B4;">Adj</th>
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

        function notif() {
            alert("Maaf, Fitur belum tersedia!");
        }
    </script>
    <script>
        $(document).ready(function() {
            // dataTableReload();
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

            datatable = $("#datatable").DataTable({
                processing: true,
                serverSide: false,
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
                        d.dateFrom = tglawal;
                        d.dateTo = tglakhir;
                        d.cbobuyer = $("#cbobuyer").val();
                    },
                },
                columns: [{
                        data: 'ws'
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
                        data: 'defect_sewing_akhir'
                    },
                    {
                        data: 'defect_spotcleaning_akhir'
                    },
                    {
                        data: 'defect_mending_akhir'
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
                        data: 'qty_sew_reject'
                    },
                    {
                        data: 'qty_sew'
                    },
                    {
                        data: 'qty_sew_adj'
                    },
                    {
                        data: 'saldo_akhir_sewing'
                    },
                    {
                        data: 'saldo_awal_steam'
                    },
                    {
                        data: 'in_steam'
                    },
                    {
                        data: 'out_steam'
                    },
                    {
                        data: 'adj_steam'
                    },
                    {
                        data: 'saldo_akhir_steam'
                    },
                    {
                        data: 'saldo_awal_def_sewing'
                    },
                    {
                        data: 'in_def_sewing'
                    },
                    {
                        data: 'out_def_sewing'
                    },
                    {
                        data: 'adj_def_sewing'
                    },
                    {
                        data: 'saldo_akhir_def_sewing'
                    },
                    {
                        data: 'saldo_awal_def_spotcleaning'
                    },
                    {
                        data: 'in_def_spotcleaning'
                    },
                    {
                        data: 'out_def_spotcleaning'
                    },
                    {
                        data: 'adj_def_spotcleaning'
                    },
                    {
                        data: 'saldo_akhir_def_spotcleaning'
                    },
                    {
                        data: 'saldo_awal_def_mending'
                    },
                    {
                        data: 'in_def_mending'
                    },
                    {
                        data: 'out_def_mending'
                    },
                    {
                        data: 'adj_def_mending'
                    },
                    {
                        data: 'saldo_akhir_def_mending'
                    },
                    {
                        data: 'saldo_awal_def_pck_sewing'
                    },
                    {
                        data: 'in_def_pck_sewing'
                    },
                    {
                        data: 'out_def_pck_sewing'
                    },
                    {
                        data: 'adj_def_pck_sewing'
                    },
                    {
                        data: 'saldo_akhir_def_pck_sewing'
                    },
                    {
                        data: 'saldo_awal_def_pck_spotcleaning'
                    },
                    {
                        data: 'in_def_pck_spotcleaning'
                    },
                    {
                        data: 'out_def_pck_spotcleaning'
                    },
                    {
                        data: 'adj_def_pck_spotcleaning'
                    },
                    {
                        data: 'saldo_akhir_def_pck_spotcleaning'
                    },
                    {
                        data: 'saldo_awal_def_pck_mending'
                    },
                    {
                        data: 'in_def_pck_mending'
                    },
                    {
                        data: 'out_def_pck_mending'
                    },
                    {
                        data: 'adj_def_pck_mending'
                    },
                    {
                        data: 'saldo_akhir_def_pck_mending'
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
            let dateFrom = $('#tgl-awal').val();
            let dateTo = $('#tgl-akhir').val();
            let cbobuyer = $("#cbobuyer").val();

            const startTime = new Date().getTime();

            Swal.fire({
                title: 'Please Wait...',
                html: 'Exporting Data...',
                didOpen: () => {
                    Swal.showLoading();
                },
                allowOutsideClick: false,
            });

            $.ajax({
                type: "POST",
                url: '{{ route('export_excel_mut_output') }}',
                data: {
                    dateFrom: dateFrom,
                    dateTo: dateTo,
                    cbobuyer: cbobuyer
                },
                success: function(data) {
                    const workbook = new ExcelJS.Workbook();
                    const worksheet = workbook.addWorksheet("Mutasi Output Production ");

                    const mainTitleRow = worksheet.addRow(["Laporan Mutasi Saldo WIP"]);
                    worksheet.getCell(`A${mainTitleRow.number}`).alignment = {
                        horizontal: 'center',
                        vertical: 'middle'
                    };
                    worksheet.mergeCells(`A${mainTitleRow.number}:E${mainTitleRow.number}`);
                    mainTitleRow.font = {
                        bold: true,
                        size: 14
                    };

                    const titleRow = worksheet.addRow([`Tgl Transaksi: ${dateFrom} - ${dateTo}`]);
                    worksheet.getCell(`A${titleRow.number}`).alignment = {
                        horizontal: 'center',
                        vertical: 'middle'
                    };
                    titleRow.eachCell((cell) => {
                        cell.border = null;
                    });

                    worksheet.addRow([]);

                    const headerRow = worksheet.addRow([
                        "Jenis Produk", "", "", "", "",
                        "Sewing", "", "", "", "", "", "", "", "", "", "", "",
                        "Steam", "", "", "", "",
                        "Defect Sewing", "", "", "", "",
                        "Defect Spotcleaning", "", "", "", "",
                        "Defect Mending", "", "", "", "",
                        "Defect Packing Sewing", "", "", "", "",
                        "Defect Packing Spotcleaning", "", "", "", "",
                        "Defect Packing Mending", "", "", "", "",
                    ]);

                    worksheet.mergeCells(`A${headerRow.number}:E${headerRow.number}`);
                    worksheet.mergeCells(`F${headerRow.number}:Q${headerRow.number}`);
                    worksheet.mergeCells(`R${headerRow.number}:V${headerRow.number}`);
                    worksheet.mergeCells(`W${headerRow.number}:AA${headerRow.number}`);
                    worksheet.mergeCells(`AB${headerRow.number}:AF${headerRow.number}`);
                    worksheet.mergeCells(`AG${headerRow.number}:AK${headerRow.number}`);
                    worksheet.mergeCells(`AL${headerRow.number}:AP${headerRow.number}`);
                    worksheet.mergeCells(`AQ${headerRow.number}:AU${headerRow.number}`);
                    worksheet.mergeCells(`AV${headerRow.number}:AZ${headerRow.number}`);

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
                    worksheet.getCell(`AL${headerRow.number}`).alignment = {
                        horizontal: 'center',
                        vertical: 'middle'
                    };
                    worksheet.getCell(`AQ${headerRow.number}`).alignment = {
                        horizontal: 'center',
                        vertical: 'middle'
                    };
                    worksheet.getCell(`AZ${headerRow.number}`).alignment = {
                        horizontal: 'center',
                        vertical: 'middle'
                    };

                    const headers = [
                        "WS", "Buyer", "Style", "Color", "Size",
                        "Saldo Awal", "Terima Dari Loading", "Rework Sewing",
                        "Rework Spot Cleaning", "Rework Mending", "Defect Sewing",
                        "Defect Spot Cleaning", "Defect Mending", "Reject", "Output", "Adj",
                        "Saldo Akhir",
                        "Saldo Awal", "Terima", "Keluar", "Adj", "Saldo Akhir",
                        "Saldo Awal", "Terima", "Keluar", "Adj", "Saldo Akhir",
                        "Saldo Awal", "Terima", "Keluar", "Adj", "Saldo Akhir",
                        "Saldo Awal", "Terima", "Keluar", "Adj", "Saldo Akhir",
                        "Saldo Awal", "Terima", "Keluar", "Adj", "Saldo Akhir",
                        "Saldo Awal", "Terima", "Keluar", "Adj", "Saldo Akhir",
                        "Saldo Awal", "Terima", "Keluar", "Adj", "Saldo Akhir",
                    ];
                    worksheet.addRow(headers);

                    data.forEach(function(row) {
                        worksheet.addRow([
                            row.ws,
                            row.buyer,
                            row.styleno,
                            row.color,
                            row.size,
                            row.saldo_awal_sewing,
                            row.qty_loading,
                            row.defect_sewing_akhir,
                            row.defect_spotcleaning_akhir,
                            row.defect_mending_akhir,
                            row.input_rework_sewing,
                            row.input_rework_spotcleaning,
                            row.input_rework_mending,
                            row.qty_sew_reject,
                            row.qty_sew,
                            row.qty_sew_adj,
                            row.saldo_akhir_sewing,
                            row.saldo_awal_steam,
                            row.in_steam,
                            row.out_steam,
                            row.adj_steam,
                            row.saldo_akhir_steam,
                            row.saldo_awal_def_sewing,
                            row.in_def_sewing,
                            row.out_def_sewing,
                            row.adj_def_sewing,
                            row.saldo_akhir_def_sewing,
                            row.saldo_awal_def_spotcleaning,
                            row.in_def_spotcleaning,
                            row.out_def_spotcleaning,
                            row.adj_def_spotcleaning,
                            row.saldo_akhir_def_spotcleaning,
                            row.saldo_awal_def_mending,
                            row.in_def_mending,
                            row.out_def_mending,
                            row.adj_def_mending,
                            row.saldo_akhir_def_mending,
                            row.saldo_awal_def_pck_sewing,
                            row.in_def_pck_sewing,
                            row.out_def_pck_sewing,
                            row.adj_def_pck_sewing,
                            row.saldo_akhir_def_pck_sewing,
                            row.saldo_awal_def_pck_spotcleaning,
                            row.in_def_pck_spotcleaning,
                            row.out_def_pck_spotcleaning,
                            row.adj_def_pck_spotcleaning,
                            row.saldo_akhir_def_pck_spotcleaning,
                            row.saldo_awal_def_pck_mending,
                            row.in_def_pck_mending,
                            row.out_def_pck_mending,
                            row.adj_def_pck_mending,
                            row.saldo_akhir_def_pck_mending
                        ]);
                    });

                    worksheet.eachRow({
                        includeEmpty: true
                    }, function(row, rowNumber) {
                        if (rowNumber !== mainTitleRow.number && rowNumber !== titleRow.number &&
                            rowNumber !== 3) {
                            row.eachCell({
                                includeEmpty: true
                            }, function(cell, colNumber) {
                                // Apply borders
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

                                if (colNumber >= 6 && colNumber <= 52) {
                                    cell.numFmt = '#,##0';

                                    // Safely extract the numeric value
                                    let val = cell.value;

                                    // If cell.value is an object with 'result' or 'richText', unwrap it
                                    if (val && typeof val === 'object') {
                                        if ('result' in val) val = val.result;
                                        else if ('richText' in val && Array.isArray(val
                                                .richText) && val.richText.length > 0) {
                                            val = val.richText[0].text;
                                        }
                                    }

                                    // Convert to number if possible
                                    const numVal = Number(val);

                                    if (!isNaN(numVal) && numVal < 0) {
                                        cell.font = {
                                            color: {
                                                argb: 'FFFF0000'
                                            }, // red font
                                            bold: true
                                        };
                                    }
                                }
                            });
                        }
                    });



                    workbook.xlsx.writeBuffer().then(function(buffer) {
                        const blob = new Blob([buffer], {
                            type: "application/octet-stream"
                        });
                        const link = document.createElement('a');
                        link.href = window.URL.createObjectURL(blob);
                        link.download = "Laporan Mutasi Output.xlsx";
                        link.click();

                        const endTime = new Date().getTime();
                        const elapsedTime = Math.round((endTime - startTime) / 1000);

                        Swal.close();
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
