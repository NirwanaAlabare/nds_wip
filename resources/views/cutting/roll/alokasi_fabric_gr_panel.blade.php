@extends('layouts.index')

@section('custom-link')
    {{-- <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}"> --}}

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
        .checkbox-cell-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 50px;
            height: 100%;
            padding: 0;
        }
    </style>
@endsection

@section('content')
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-list"></i> Alokasi Fabric GR Panel</h5>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <a href="{{ route('create_alokasi_fabric_gr_panel') }}" target=""
                    class="btn btn-outline-primary position-relative btn-sm">
                    <i class="fas fa-plus"></i>
                    New
                </a>
            </div>

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
                    <a class="btn btn-outline-primary position-relative btn-sm" onclick="dataTableReload()">
                        <i class="fas fa-search"></i>
                        Cari
                    </a>
                </div>

                {{-- <div class="mb-3">
                    <a onclick="export_excel()" class="btn btn-outline-success position-relative btn-sm">
                        <i class="fas fa-file-excel fa-sm"></i>
                        Export Excel
                    </a>
                </div> --}}
            </div>
            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-hover align-middle text-nowrap w-100">
                    <thead class="bg-sb">
                        <tr>
                            <th scope="col" class="text-center align-middle">Tgl</th>
                            <th scope="col" class="text-center align-middle">Barcode</th>
                            <th scope="col" class="text-center align-middle">ID Item</th>
                            <th scope="col" class="text-center align-middle">Buyer</th>
                            <th scope="col" class="text-center align-middle">WS</th>
                            <th scope="col" class="text-center align-middle">Style</th>
                            <th scope="col" class="text-center align-middle">Color</th>
                            <th scope="col" class="text-center align-middle">Qty</th>
                            <th scope="col" class="text-center align-middle">Unit</th>
                            <th scope="col" class="text-center align-middle">Created At</th>
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
        // Select2 Autofocus
        $(document).on('select2:open', () => {
            document.querySelector('.select2-search__field').focus();
        });

        // Initialize Select2 Elements
        $('.select2').select2();

        // Initialize Select2BS4 Elements
        $('.select2bs4').select2({
            theme: 'bootstrap4',
        });

        function notif() {
            alert("Maaf, Fitur belum tersedia!");
        }
    </script>
    <script>
        function dataTableReload() {
            datatable.ajax.reload();
        }

        $(document).ready(function() {
            dataTableReload();
        })

        let datatable = $("#datatable").DataTable({
            ordering: false,
            responsive: true,
            processing: true,
            serverSide: false,
            paging: true,
            searching: true,
            scrollY: true,
            scrollX: true,
            scrollCollapse: false,

            ajax: {
                url: '{{ route('alokasi_fabric_gr_panel') }}',
                data: function(d) {
                    d.dateFrom = $('#tgl-awal').val();
                    d.dateTo = $('#tgl-akhir').val();
                },
            },
            columns: [{
                    data: 'tgl_trans_fix'
                },
                {
                    data: 'barcode'
                },
                {
                    data: 'id_item'
                },
                {
                    data: 'buyer'
                },
                {
                    data: 'ws'
                },
                {
                    data: 'styleno'
                },
                {
                    data: 'color'
                },
                {
                    data: 'qty_pakai'
                },
                {
                    data: 'unit'
                },
                {
                    data: null,
                    render: function(data, type, row) {
                        return `
      <span style="font-weight:600; vertical-align: middle;">${row.created_by}</span>
      <span style="color: #666; font-size: 0.9em; margin-left: 6px; vertical-align: middle;">&bull; ${row.updated_at}</span>
    `;
                    }
                },
            ],
        });
    </script>

    <script>
        async function export_excel() {
            const workbook = new ExcelJS.Workbook();
            const worksheet = workbook.addWorksheet('Fabric Relaxation');

            // Decode HTML entities helper
            function decodeHTMLEntities(text) {
                const txt = document.createElement('textarea');
                txt.innerHTML = text;
                return txt.value;
            }

            // Get date range from input fields
            const dateFrom = $('#tgl-awal').val();
            const dateTo = $('#tgl-akhir').val();

            // 1️⃣ Title Row
            const title = `Laporan Fabric Relaxation`;
            const dateRange = `Periode: ${dateFrom} - ${dateTo}`;

            worksheet.mergeCells('A1:U1');
            const titleRow = worksheet.getCell('A1');
            titleRow.value = title;
            titleRow.font = {
                size: 14,
                bold: true
            };
            titleRow.alignment = {
                vertical: 'middle',
                horizontal: 'center'
            };

            worksheet.mergeCells('A2:U2');
            const dateRow = worksheet.getCell('A2');
            dateRow.value = dateRange;
            dateRow.font = {
                size: 12,
                italic: true
            };
            dateRow.alignment = {
                vertical: 'middle',
                horizontal: 'center'
            };

            // 2️⃣ Header labels
            const headers = [
                "Tgl. Relax", "Operator", "No. Mesin", "No. Form", "Tgl. BPB",
                "No. PL", "Buyer", "WS", "Style", "Color", "Lot", "ID Item", "Detail Item",
                "Barcode", "No. Roll", "Durasi Relax (Jam)", "Start Date", "Start Time", "Finish Date",
                "Finish Time", "Total (Hari)"
            ];

            const headerRow = worksheet.addRow(headers);

            // Style header row
            headerRow.eachCell(cell => {
                cell.font = {
                    bold: true
                };
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
                cell.alignment = {
                    vertical: 'middle',
                    horizontal: 'center'
                };
            });

            // 3️⃣ Get DataTable data
            const data = $('#datatable').DataTable().rows({
                search: 'applied'
            }).data().toArray();

            data.forEach(row => {
                const dataRow = worksheet.addRow([
                    row.tgl_form_fix,
                    row.operator,
                    row.no_mesin,
                    row.no_form,
                    row.tgl_dok,
                    row.no_invoice,
                    row.buyer,
                    row.kpno,
                    row.styleno,
                    row.color,
                    row.no_lot,
                    row.id_item,
                    decodeHTMLEntities(row.itemdesc),
                    row.barcode,
                    row.no_roll_buyer,
                    row.durasi_relax,
                    row.finish_date,
                    row.finish_time,
                    row.finish_relax_date,
                    row.finish_relax_time,
                    row.days_diff
                ]);

                dataRow.eachCell(cell => {
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

            // 4️⃣ Auto width
            worksheet.columns.forEach(column => {
                let maxLength = 10;
                column.eachCell({
                    includeEmpty: true
                }, cell => {
                    const value = cell.value ? cell.value.toString() : '';
                    maxLength = Math.max(maxLength, value.length);
                });
                column.width = maxLength + 2;
            });

            // 5️⃣ Download file
            const buffer = await workbook.xlsx.writeBuffer();
            const blob = new Blob([buffer], {
                type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            });

            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = 'fabric_relaxation_export.xlsx';
            link.click();
        }
    </script>
@endsection
