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
        }

        #datatable th,
        #datatable td {
            white-space: nowrap;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 12px;
        }

        .status-kosong {
            color: #dc3545;
            background-color: #f8d7da;
            border: 1px solid #f5c2c7;
        }

        .status-tersedia {
            color: #198754;
            background-color: #d1e7dd;
            border: 1px solid #badbcc;
        }

        .select2-container--bootstrap4 .select2-selection--multiple {
            min-height: 31px !important;
            height: auto !important;
            padding: 0 3px !important;
            border-radius: 0.2rem !important;
        }

        /* PO yang dipilih */
        .select2-container--bootstrap4
        .select2-selection--multiple
        .select2-selection__choice {
            height: 20px !important;
            line-height: 18px !important;
            font-size: 0.9rem !important;
            margin-top: 4px !important;
            margin-right: 3px !important;
            margin-bottom: 2px !important;
            padding: 0 4px !important;
        }

        /* X */
        .select2-container--bootstrap4
        .select2-selection--multiple
        .select2-selection__choice__remove {
            font-size: 13px !important;
            line-height: 18px !important;
            margin-right: 3px !important;
            padding: 0 !important;
        }

        /* Input pencarian */
        .select2-container--bootstrap4
        .select2-selection--multiple
        .select2-search--inline {
            height: 22px !important;
        }

        .select2-container--bootstrap4
        .select2-selection--multiple
        .select2-search__field {
            height: 20px !important;
            margin-top: 4px !important;
            padding: 0 !important;
            font-size: 0.9rem !important;
        }

    </style>
@endsection

@section('content')
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-list"></i> Detail Transaksi WIP PO</h5>
        </div>

        <div class="card-body" id="detail-transaksi-wip">
            <div class="row align-items-end g-3 mb-3">
                <!-- Start Date -->
                <div class="col-md-2">
                    <label class="form-label">
                        <small><b>Start Date</b></small>
                    </label>
                    <input type="date" class="form-control form-control-sm" id="start_date" name="start_date" value="">
                </div>
                <!-- End Date -->
                <div class="col-md-2">
                    <label class="form-label">
                        <small><b>End Date</b></small>
                    </label>
                    <input type="date" class="form-control form-control-sm" id="end_date" name="end_date" value="">
                </div>

                <div class="col-md-3">
                    <label class="form-label">
                        <small><b>PO</b></small>
                    </label>

                    <select class="form-select form-select-sm select2bs4base select2-buyer" id="po" name="po[]" multiple>
                        @foreach ($dataPo as $po)
                            <option value="{{ $po->po }}">
                                {{ $po->po }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">
                        <small><b>Ketersediaan Stok</b></small>
                    </label>
                    <select class="form-control form-control-sm select2bs4basestok" id="status" name="status">
                        <option value="" checked>Semua</option>
                        <option value="Tersedia">Tersedia</option>
                        <option value="Kosong">Kosong</option>
                    </select>
                </div>
                <!-- Generate Button -->
                <div class="col-md-3 d-flex gap-2 align-items-end">
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
                            <th class="text-center align-middle">Tgl Shipment</th>
                            <th class="text-center align-middle">PO</th>
                            <th class="text-center align-middle">WS</th>
                            <th class="text-center align-middle">Style</th>
                            <th class="text-center align-middle">Color</th>
                            <th class="text-center align-middle">Size</th>
                            <th class="text-center align-middle">Dest</th>
                            <th class="text-center align-middle">Qty PO</th>
                            <th class="text-center align-middle">Terima Packing Central</th>
                            <th class="text-center align-middle">Retur</th>
                            <th class="text-center align-middle">Switching Out</th>
                            <th class="text-center align-middle">Switching In</th>
                            <th class="text-center align-middle">Scan</th>
                            <th class="text-center align-middle">Qty Sisa WIP</th>
                            <th class="text-center align-middle">Status</th>
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

        $(document).ready(function() {
            $('.select2bs4').select2({
                theme: 'bootstrap4',
                width: '100%',
                placeholder: 'Pilih PO',
                allowClear: true
            });
        });

        // Initialize Select2 Elements
        $('.select2').select2();

        // Initialize Select2BS4 Elements
        $('.select2bs4').select2({
            theme: 'bootstrap4',
            width: 'resolve' // Ensures it respects the 100% width from inline style or Bootstrap
        });

        $('.select2bs4base').select2({
            theme: 'bootstrap4',
            dropdownParent: $("#detail-transaksi-wip"),
            placeholder: 'Pilih PO',
            allowClear: true,
            closeOnSelect: false,
            width: '100%'
        });

        $('#po').on('select2:select', function () {
            $(this).next('.select2-container').find('.select2-search__field').val('');
        });

        $('.select2bs4basestok').select2({
            theme: 'bootstrap4',
            dropdownParent: $("#detail-transaksi-wip")
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

            Swal.fire({
                title: 'Loading...',
                text: 'Please wait while data is loading.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            const table = $('#datatable').DataTable({
                destroy: true,
                ordering: false,
                responsive: false,
                serverSide: false,
                paging: true,
                searching: true,
                scrollX: true,
                scrollY: '500px',
                scrollCollapse: true,
                autoWidth: false,
                deferRender: true,
                processing: false,
                ajax: {
                    url: '{{ route('detail_transaksi_packing_central_switching') }}',
                    data(d) {
                        d.start_date = start_date;
                        d.end_date = end_date;
                        d.status = $("#status").val();
                        d.po = $("#po").val();
                    },
                    dataSrc(json) {
                        Swal.close();
                        return json.data;
                    },
                    error(xhr) {
                        Swal.close();

                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to load data.'
                        });
                    }
                },
                columns: [
                    {
                        data: 'tgl_shipment'
                    },
                    {
                        data: 'po'
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
                        data: 'size'
                    },
                    {
                        data: 'dest'
                    },
                    {
                        data: 'qty_po'
                    },
                    {
                        data: 'qty_pck_in'
                    },
                    {
                        data: 'qty_retur'
                    },
                    {
                        data: 'qty_switch_out'
                    },
                    {
                        data: 'qty_switch_in'
                    },
                    {
                        data: 'qty_scan'
                    },
                    {
                        data: 'qty_sisa'
                    },
                    {
                        data: 'status',
                        render: function(data, type, row) {
                            if (data === 'Tersedia') {
                                return `
                                    <span class="status-badge status-tersedia">
                                        Tersedia
                                    </span>
                                `;
                            }

                            return `
                                <span class="status-badge status-kosong">
                                    Kosong
                                </span>
                            `;
                        }
                    },
                ],
                initComplete: function() {
                    setTimeout(() => {
                        this.api().columns.adjust();
                    }, 100);
                }
            });

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
                url: '{{ route('export_detail_transaksi_packing_central_switching') }}',
                data: {
                    start_date: start_date,
                    end_date: end_date,
                    status: $("#status").val(),
                    po: $("#po").val(),
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
                    link.download = "Detail Transaksi WIP PO " + start_date + " _ " + end_date + ".xlsx";
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
