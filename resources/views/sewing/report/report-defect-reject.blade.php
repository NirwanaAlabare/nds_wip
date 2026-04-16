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
    </style>
@endsection

@section('content')
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-list"></i> Report Defect & Reject</h5>
        </div>

        <div class="card-body" id="report-defect-reject">
            <div class="row align-items-end g-3 mb-3">
                <!-- Start Date -->
                <div class="col-md-2">
                    <label class="form-label">
                        <small><b>Start Date</b></small>
                    </label>
                    <input type="date" class="form-control form-control-sm" id="start_date" name="start_date"
                        value="{{ date('Y-m-d') }}">
                </div>
                <!-- End Date -->
                <div class="col-md-2">
                    <label class="form-label">
                        <small><b>End Date</b></small>
                    </label>
                    <input type="date" class="form-control form-control-sm" id="end_date" name="end_date"
                        value="{{ date('Y-m-d') }}">
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">
                        <small><b>Department</b></small>
                    </label>
                    <select class="form-select form-control-sm" name="department" id="department">
                        <option value="" selected>All</option>
                        <option value="_end">END-LINE</option>
                        <option value="_packing">FINISHING-LINE</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">
                        <small><b>No. WS</b></small>
                    </label>
                    <select class="form-select form-control-sm select2bs4base" name="ws" id="ws">
                        <option value="">All</option>
                        @foreach ($orders as $order)
                            <option value="{{ $order }}">{{ $order }}</option>
                        @endforeach
                    </select>
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
                            <th class="text-center align-middle">Kode Numbering</th>
                            <th class="text-center align-middle">Buyer</th>
                            <th class="text-center align-middle">No WS</th>
                            <th class="text-center align-middle">Style</th>
                            <th class="text-center align-middle">Color</th>
                            <th class="text-center align-middle">Size</th>
                            <th class="text-center align-middle">Dest</th>
                            <th class="text-center align-middle">Sewing Line</th>
                            <th class="text-center align-middle">Dept</th>
                            <th class="text-center align-middle">Defect Type</th>
                            <th class="text-center align-middle">Defect Area</th>
                            <th class="text-center align-middle">Status</th>
                            <th class="text-center align-middle">Tgl Defect</th>
                            <th class="text-center align-middle">Tgl Rework</th>
                            <th class="text-center align-middle">Proses Type</th>
                            <th class="text-center align-middle">Proses Status</th>
                            <th class="text-center align-middle">Tgl Proses In</th>
                            <th class="text-center align-middle">Tgl Proses Out</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th colspan="9" class="text-center">TOTAL</th>
                            <th colspan="9" id="totalRows" class="text-end"></th>
                        </tr>
                    </tfoot>
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

         $('.select2bs4base').select2({
            theme: 'bootstrap4',
            dropdownParent: $("#report-defect-reject")
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
            // $('#start_date').val('').trigger('change');
            // $('#end_date').val('').trigger('change');
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
                    url: '{{ route('reportDefectReject') }}',
                    data(d) {
                        d.start_date = start_date;
                        d.end_date = end_date;
                        d.department = $("#department").val();
                        d.ws = $("#ws").val();
                    },
                    dataSrc(json) {
                        if (start_date && end_date) Swal.close();
                        return json.data;
                    },
                    error() {
                        Swal.fire('Error', 'Failed to load data.', 'error');
                    }
                },

                columns: [
                    {
                        data: 'kode_numbering'
                    },
                    {
                        data: 'buyer'
                    },
                    {
                        data: 'ws'
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
                        data: 'dest'
                    },
                    {
                        data: 'sewing_line'
                    },
                    {
                        data: 'dept'
                    },
                    {
                        data: 'defect_type'
                    },
                    {
                        data: 'defect_area'
                    },
                    {
                        data: 'status',
                        render: function(data) {
                            if (!data) return '';
                            return data.replace(/\b\w/g, function(char) {
                                return char.toUpperCase();
                            });
                        }
                    },
                    {
                        data: 'tgl_defect'
                    },
                    {
                        data: 'tgl_rework'
                    },
                    {
                        data: 'proses_type'
                    },
                    {
                        data: 'proses_status',
                        render: function(data) {
                            if (!data) return '';
                            return data.replace(/\b\w/g, function(char) {
                                return char.toUpperCase();
                            });
                        }
                    },
                    {
                        data: 'tgl_proses_in'
                    },
                    {
                        data: 'tgl_proses_out'
                    },
                ],

                footerCallback: function (row, data, start, end, display) {
                    const api = this.api();

                    const totalRows = api.rows({ search: 'applied' }).count();

                    $('#totalRows').html(totalRows);
                },

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
            let department = $("#department").val();
            let ws = $("#ws").val();

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
                url: '{{ route('export_excel_report_defect_reject') }}',
                data: {
                    start_date: start_date,
                    end_date: end_date,
                    department: department,
                    ws: ws
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
                    link.download = "Laporan Defect & Reject " + start_date + " _ " + end_date + ".xlsx";
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
