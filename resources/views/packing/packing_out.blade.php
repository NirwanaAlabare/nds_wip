@extends('layouts.index')

@section('custom-link')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">

    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

    <style type="text/css">
        input::-webkit-outer-spin-button,
        input::-webkit-inner-spin-button {
            /* display: none; <- Crashes Chrome on hover */
            -webkit-appearance: none;
            margin: 0;
            /* <-- Apparently some margin are still there even though it's hidden */
        }

        input[type=number] {
            -moz-appearance: textfield;
            /* Firefox */
        }
    </style>
@endsection

@section('content')
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-list"></i> List Transaksi</h5>
        </div>
        <div class="card-body">
            <div class="d-flex align-items-end gap-3 mb-3">
                <div class="mb-3">
                    <a href="{{ route('create-packing-out') }}" class="btn btn-outline-primary position-relative">
                        <i class="fas fa-barcode"></i>
                        Baru Scan
                    </a>
                </div>
                {{-- <div class="mb-3">
                    <a href="{{ route('create-packing-out') }}" class="btn btn-outline-secondary position-relative">
                        <i class="fas fa-plus"></i>
                        Baru Non Scan
                    </a>
                </div> --}}
            </div>
            <div class="d-flex align-items-end gap-3 mb-3">
                <div class="mb-3">
                    <label class="form-label"><small><b>Tgl Awal</b></small></label>
                    <input type="date" class="form-control form-control-sm " id="tgl-awal" name="tgl_awal"
                        oninput="dataTableReload()" value="{{ date('Y-m-d') }}">
                </div>
                <div class="mb-3">
                    <label class="form-label"><small><b>Tgl Akhir</b></small></label>
                    <input type="date" class="form-control form-control-sm" id="tgl-akhir" name="tgl_akhir"
                        oninput="dataTableReload()" value="{{ date('Y-m-d') }}">
                </div>
                <div class="mb-3">
                    <a onclick="export_excel_packing_out()" class="btn btn-outline-success position-relative btn-sm">
                        <i class="fas fa-file-excel fa-sm"></i>
                        Export Excel
                    </a>
                </div>
            </div>

            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-striped 100 text-nowrap">
                    <thead class="table-primary">
                        <tr style='text-align:center; vertical-align:middle'>
                            <th>Tgl. Trans</th>
                            <th>No. Carton</th>
                            <th>Barcode</th>
                            <th>PO</th>
                            <th>WS</th>
                            <th>Color</th>
                            <th>Size</th>
                            <th>Dest</th>
                            <th>Total</th>
                            <th>User</th>
                            <th>Tgl. Input</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th colspan="8"></th>
                            <th> <input type = 'text' class="form-control form-control-sm" style="width:75px" readonly
                                    id = 'total_qty_chk'> </th>
                            <th>PCS</th>
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
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
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
    </script>
    <script>
        function notif() {
            alert("Maaf, Fitur belum tersedia!");
        }
    </script>
    <script>
        $(document).ready(() => {
            dataTableReload();
        });


        function dataTableReload() {
            datatable.ajax.reload();
        }

        $('#datatable thead tr').clone(true).appendTo('#datatable thead');
        $('#datatable thead tr:eq(1) th').each(function(i) {
            var title = $(this).text();
            $(this).html('<input type="text" class="form-control form-control-sm"/>');
            $('input', this).on('keyup change', function() {
                if (datatable.column(i).search() !== this.value) {
                    datatable
                        .column(i)
                        .search(this.value)
                        .draw();
                }
            });
        });

        let datatable = $("#datatable").DataTable({
            "footerCallback": function(row, data, start, end, display) {
                var api = this.api(),
                    data;

                // converting to interger to find total
                var intVal = function(i) {
                    return typeof i === 'string' ?
                        i.replace(/[\$,]/g, '') * 1 :
                        typeof i === 'number' ?
                        i : 0;
                };

                // computing column Total of the complete result
                var sumTotal = api
                    .column(8)
                    .data()
                    .reduce(function(a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                // Update footer by showing the total with the reference of the column index
                $(api.column(0).footer()).html('Total');
                $(api.column(8).footer()).html(sumTotal);
            },
            ordering: false,
            processing: true,
            serverSide: true,
            paging: false,
            searching: true,
            scrollY: '300px',
            scrollX: '300px',
            scrollCollapse: true,
            ajax: {
                url: '{{ route('packing-out') }}',
                data: function(d) {
                    d.dateFrom = $('#tgl-awal').val();
                    d.dateTo = $('#tgl-akhir').val();
                },
            },
            columns: [{
                    data: 'tgl_trans_fix'

                },
                {
                    data: 'no_carton'
                },
                {
                    data: 'barcode'
                },
                {
                    data: 'po'
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
                    data: 'tot'
                },
                {
                    data: 'created_by'
                },
                {
                    data: 'created_at'
                },
            ],
            columnDefs: [{
                "className": "dt-center",
                "targets": "_all"
            }, ]


        }, );


        function export_excel_packing_out() {
            let from = document.getElementById("tgl-awal").value;
            let to = document.getElementById("tgl-akhir").value;

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
                url: '{{ route('export_excel_packing_out') }}',
                data: {
                    from: from,
                    to: to
                },
                xhrFields: {
                    responseType: 'blob'
                },
                success: function(response) {
                    {
                        swal.close();
                        Swal.fire({
                            title: 'Data Sudah Di Export!',
                            icon: "success",
                            showConfirmButton: true,
                            allowOutsideClick: false
                        });
                        var blob = new Blob([response]);
                        var link = document.createElement('a');
                        link.href = window.URL.createObjectURL(blob);
                        link.download = "Laporan Hasil Scan " + from + " sampai " +
                            to + ".xlsx";
                        link.click();

                    }
                },
            });
        }
    </script>
@endsection
