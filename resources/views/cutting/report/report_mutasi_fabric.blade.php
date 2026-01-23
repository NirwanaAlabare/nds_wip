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
        #datatable {
            width: 100% !important;
            table-layout: fixed;
            /* KUNCI layout */
        }

        #datatable th,
        #datatable td {
            white-space: normal !important;
            word-break: break-word;
            vertical-align: middle;
        }
    </style>
@endsection

@section('content')
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-list"></i> Report Mutasi Fabric</h5>
        </div>

        <div class="card-body">
            <div class="row align-items-end g-3 mb-3">
                <!-- Start Date -->
                <div class="col-md-2">
                    <label class="form-label">
                        <small><b>Start Date</b></small>
                    </label>
                    <input type="date" class="form-control form-control-sm" id="start_date" name="start_date"
                        value="">
                </div>
                <!-- End Date -->
                <div class="col-md-2">
                    <label class="form-label">
                        <small><b>End Date</b></small>
                    </label>
                    <input type="date" class="form-control form-control-sm" id="end_date" name="end_date"
                        value="">
                </div>

                <!-- Generate Button -->
                <div class="col-md-4 d-flex gap-2 align-items-end">
                    <a class="btn btn-outline-primary position-relative btn-sm" onclick="dataTableReload()">
                        <i class="fas fa-search"></i>
                        Submit
                    </a>

                    <a onclick="export_excel()" class="btn btn-outline-success position-relative btn-sm">
                        <i class="fas fa-file-excel fa-sm"></i>
                        Export Excel
                    </a>
                </div>
            </div>


            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-striped table-hover w-100">
                    <thead class="bg-sb">
                        <tr>
                            <th class="text-center align-middle">Worksheet</th>
                            <th class="text-center align-middle">Buyer</th>
                            <th class="text-center align-middle">Style</th>
                            <th class="text-center align-middle">Color</th>
                            <th class="text-center align-middle">ID Item</th>
                            <th class="text-center align-middle">Item Name</th>
                            <th class="text-center align-middle">Saldo Awal</th>
                            <th class="text-center align-middle">Penerimaan</th>
                            <th class="text-center align-middle">Pemakaian Cutting</th>
                            <th class="text-center align-middle">Ganti Reject Set</th>
                            <th class="text-center align-middle">Ganti Reject Panel</th>
                            <th class="text-center align-middle">Retur</th>
                            <th class="text-center align-middle">Short Roll</th>
                            <th class="text-center align-middle">Saldo Akhir</th>
                            <th class="text-center align-middle">Satuan</th>
                        </tr>
                    </thead>
                </table>
            </div>


        </div>
    </div>
@endsection

@section('custom-script')
    <!-- DataTables & Plugins -->
    {{-- <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script> --}}
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables 2.0/dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables 2.0/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables 2.0/dataTables.fixedColumns.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-rowsgroup/dataTables.rowsGroup.js') }}"></script>
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
            width: 'resolve' // Ensures it respects the 100% width from inline style or Bootstrap
        });
        // Now set height and font-size on the Select2 container after init
        $('.select2-container--bootstrap4 .select2-selection--single').css({
            'height': '30px', // your desired height
            'font-size': '12px', // your desired font size
            'line-height': '30px' // vertically center text
        });

        function notif() {
            alert("Maaf, Fitur belum tersedia!");
        }
    </script>

    <script>
        $(document).ready(function() {
            $('#start_date').val('').trigger('change');
            $('#end_date').val('').trigger('change');
            dataTableReload()
        });

        function dataTableReload() {
            let start_date = $('#start_date').val();
            let end_date = $('#end_date').val();

            if (start_date && end_date) {
                Swal.fire({
                    title: 'Loading...',
                    text: 'Please wait while data is loading.',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });
            }

            const table = $('#datatable').DataTable({
                destroy: true,
                ordering: false,
                responsive: false,
                serverSide: false,
                paging: false,
                searching: true,

                scrollX: true,
                scrollY: '500px',
                scrollCollapse: true,

                autoWidth: false, // WAJIB false
                deferRender: true,

                processing: false,

                ajax: {
                    url: '{{ route('report_cutting_mutasi_fabric') }}',
                    data(d) {
                        d.start_date = start_date;
                        d.end_date = end_date;
                    },
                    dataSrc(json) {
                        if (start_date && end_date) Swal.close();
                        return json.data;
                    },
                    error() {
                        Swal.fire('Error', 'Failed to load data.', 'error');
                    }
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
                        data: 'id_item',
                    },
                    {
                        data: 'itemdesc'
                    },
                    {
                        data: 'qty_sawal',
                        className: 'text-end'
                    },
                    {
                        data: 'qty_out',
                        className: 'text-end'
                    },
                    {
                        data: 'qty_pakai',
                        className: 'text-end'
                    },
                    {
                        data: 'qty_reject_set',
                        className: 'text-end'
                    },
                    {
                        data: 'qty_reject_panel',
                        className: 'text-end'
                    },
                    {
                        data: 'qty_retur',
                        className: 'text-end'
                    },
                    {
                        data: 'short_roll',
                        className: 'text-end'
                    },
                    {
                        data: 'saldo_akhir',
                        className: 'text-end'
                    },
                    {
                        data: 'satuan',
                        className: 'text-center'
                    }
                ],

                initComplete: function() {
                    // 🔥 PAKSA RECALC SETELAH LOAD
                    setTimeout(() => {
                        this.api().columns.adjust();
                    }, 100);
                }
            });

            // 🔥 FIX ZOOM IN / OUT
            $(window).off('resize.dt').on('resize.dt', function() {
                table.columns.adjust();
            });
        }


        function export_excel() {
            let start_date = $('#start_date').val();
            let end_date = $('#end_date').val();
            Swal.fire({
                title: 'Please Wait...',
                html: 'Exporting Data...',
                didOpen: () => {
                    Swal.showLoading()
                },
                allowOutsideClick: false,
            });

            $.ajax({
                type: "get",
                url: '{{ route('export_excel_report_cutting_mutasi_fabric') }}',
                data: {
                    start_date: start_date,
                    end_date: end_date
                },
                xhrFields: {
                    responseType: 'blob'
                },
                success: function(response) {
                    Swal.close();
                    Swal.fire({
                        title: 'Data Sudah Di Export!',
                        icon: "success",
                        showConfirmButton: true,
                        allowOutsideClick: false
                    });

                    var blob = new Blob([response]);
                    var link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = "Laporan Mutasi Fabric " + start_date + " _ " + end_date + ".xlsx";
                    link.click();
                },
                error: function(xhr, status, error) {
                    Swal.close();
                    Swal.fire({
                        title: 'Gagal Mengekspor Data',
                        text: 'Terjadi kesalahan saat mengekspor. Silakan coba lagi.',
                        icon: 'error',
                        showConfirmButton: true,
                        allowOutsideClick: false
                    });

                    console.error("Export failed:", {
                        status: status,
                        error: error,
                        response: xhr.responseText
                    });
                }
            });
        }
    </script>
@endsection
