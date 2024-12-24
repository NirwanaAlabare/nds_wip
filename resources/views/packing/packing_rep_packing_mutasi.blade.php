@extends('layouts.index')

@section('custom-link')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">

    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
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
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
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
            containerCssClass: 'form-control-sm rounded'
        });
    </script>
    <script>
        function notif() {
            alert("Maaf, Fitur belum tersedia!");
        }
    </script>
    <script>
        $('#datatable thead tr').clone(true).appendTo('#datatable thead');
        $('#datatable thead tr:eq(1) th').each(
            function(i) {
                if (i >= 8 && i <= 11) {
                    $(this).empty();
                } else {
                    var title = $(this).text();
                    $(this).html('<input type="text" class="form-control form-control-sm"/>');
                    let debounceTimer;
                    $('input', this).on('keyup change', function() {
                        clearTimeout(debounceTimer);
                        debounceTimer = setTimeout(() => {
                            if (datatable.column(i).search() !== this.value) {
                                datatable.column(i).search(this.value).draw();
                            }
                        }, 300);
                    });
                }
            });


        function dataTableReload() {
            let datatable = $("#datatable").DataTable({
                ordering: false,
                processing: true,
                serverSide: true,
                destroy: true,
                paging: true,
                searching: true,
                scrollY: '300px',
                scrollX: '300px',
                scrollCollapse: true,
                deferRender: true,
                ajax: {
                    url: '{{ route('packing_rep_packing_mutasi_load') }}',
                    data: function(d) {

                    },
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
                url: '{{ route('export_excel_rep_packing_mutasi') }}',
                data: {
                    // from: from,
                    // to: to
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
                        link.download = "Laporan Mutasi Packing List " + ".xlsx";
                        link.click();

                    }
                },
            });
        }
    </script>
@endsection
