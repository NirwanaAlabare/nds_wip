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
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-cart-arrow-down"></i> Pengeluaran Finish Good</h5>
        </div>
        <div class="card-body">
            <div class="d-flex align-items-end gap-3 mb-3">
                <div class="mb-3">
                    <a href="{{ route('create_pengeluaran_finish_good') }}"
                        class="btn btn-outline-primary position-relative">
                        <i class="fas fa-plus"></i>
                        Baru
                    </a>
                </div>
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
                    <a onclick="notif()" class="btn btn-outline-success position-relative btn-sm">
                        <i class="fas fa-file-excel fa-sm"></i>
                        Export Excel
                    </a>
                </div>
            </div>

            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-hover table-sm w-100 text-nowrap">
                    <thead class="table-success">
                        <tr style='text-align:center; vertical-align:middle'>
                            <th>No. SB</th>
                            <th>Tgl. Out</th>
                            <th>Buyer</th>
                            <th>Tot Ctn</th>
                            <th>Tot Qty</th>
                            <th>Jenis Dok</th>
                            <th>Inv No</th>
                            <th>List PO</th>
                            <th>Ket</th>
                            <th>User</th>
                            <th>Tgl. Input</th>
                            <th>Act</th>
                        </tr>
                    </thead>
                    {{-- <tfoot>
                        <tr>
                            <th colspan="7"></th>
                            <th> <input type = 'text' class="form-control form-control-sm" style="width:75px" readonly
                                    id = 'total_qty_chk'> </th>
                            <th>PCS</th>
                            <th></th>
                            <th></th>
                            <th></th>
                        </tr>
                    </tfoot> --}}
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
            // "footerCallback": function(row, data, start, end, display) {
            //     var api = this.api(),
            //         data;

            //     // converting to interger to find total
            //     var intVal = function(i) {
            //         return typeof i === 'string' ?
            //             i.replace(/[\$,]/g, '') * 1 :
            //             typeof i === 'number' ?
            //             i : 0;
            //     };

            //     // computing column Total of the complete result
            //     var sumTotal = api
            //         .column(7)
            //         .data()
            //         .reduce(function(a, b) {
            //             return intVal(a) + intVal(b);
            //         }, 0);

            //     // Update footer by showing the total with the reference of the column index
            //     $(api.column(0).footer()).html('Total');
            //     $(api.column(7).footer()).html(sumTotal);
            // },


            ordering: false,
            processing: true,
            serverSide: true,
            paging: false,
            searching: true,
            scrollY: '300px',
            scrollX: '300px',
            scrollCollapse: true,
            ajax: {
                url: '{{ route('finish_good_pengeluaran') }}',
                data: function(d) {
                    d.dateFrom = $('#tgl-awal').val();
                    d.dateTo = $('#tgl-akhir').val();
                },
            },
            columns: [{
                    data: 'no_sb'

                }, {
                    data: 'tgl_pengeluaran_fix'
                },
                {
                    data: 'buyer'
                },
                {
                    data: 'tot_karton'
                },
                {
                    data: 'tot_qty'
                },
                {
                    data: 'jenis_dok'
                },
                {
                    data: 'invno'
                },
                {
                    data: 'list_po'
                },
                {
                    data: 'remark'
                },
                {
                    data: 'created_by'
                },
                {
                    data: 'created_at'
                },
                {
                    data: 'created_at'
                },
            ],

            columnDefs: [{
                    "className": "align-middle",
                    "targets": "_all"
                },
                {
                    targets: [11],
                    render: (data, type, row, meta) => {
                        return `
                <div
                class='d-flex gap-1 justify-content-center'>
                <a class='btn btn-warning btn-sm'  target="_blank" href="{{ route('edit_fg_out') }}/` + row.id + `"><i class='fas fa-edit'></i></a>
                </div>
                        `;
                    }
                },

            ]

        });


        // $('#datatable thead tr').clone(true).appendTo('#datatable thead');
        // $('#datatable thead tr:eq(1) th').each(function(i) {
        //     var title = $(this).text();
        //     $(this).html('<input type="text" class="form-control form-control-sm"/>');
        //     $('input', this).on('keyup change', function() {
        //         if (datatable.column(i).search() !== this.value) {
        //             datatable
        //                 .column(i)
        //                 .search(this.value)
        //                 .draw();
        //         }
        //     });
        // });

        // let datatable = $("#datatable").DataTable({
        //     // "footerCallback": function(row, data, start, end, display) {
        //     //     var api = this.api(),
        //     //         data;

        //     //     // converting to interger to find total
        //     //     var intVal = function(i) {
        //     //         return typeof i === 'string' ?
        //     //             i.replace(/[\$,]/g, '') * 1 :
        //     //             typeof i === 'number' ?
        //     //             i : 0;
        //     //     };

        //     //     // computing column Total of the complete result
        //     //     var sumTotal = api
        //     //         .column(7)
        //     //         .data()
        //     //         .reduce(function(a, b) {
        //     //             return intVal(a) + intVal(b);
        //     //         }, 0);

        //     //     // Update footer by showing the total with the reference of the column index
        //     //     $(api.column(0).footer()).html('Total');
        //     //     $(api.column(7).footer()).html(sumTotal);
        //     // },


        //     ordering: false,
        //     processing: true,
        //     serverSide: true,
        //     paging: false,
        //     searching: true,
        //     scrollY: '300px',
        //     scrollX: '300px',
        //     scrollCollapse: true,
        //     ajax: {
        //         url: '{{ route('finish_good_pengeluaran') }}',
        //         data: function(d) {
        //             d.dateFrom = $('#tgl-awal').val();
        //             d.dateTo = $('#tgl-akhir').val();
        //         },
        //     },
        //     columns: [{
        //             data: 'no_sb'

        //         }, {
        //             data: 'tgl_pengeluaran_fix'
        //         },
        //         {
        //             data: 'buyer'
        //         },
        //         {
        //             data: 'tot_karton'
        //         },
        //         {
        //             data: 'tot_qty'
        //         },
        //         {
        //             data: 'jenis_dok'
        //         },
        //         {
        //             data: 'invno'
        //         },
        //         {
        //             data: 'remark'
        //         },
        //     ],
        //     columnDefs: [{
        //         "className": "align-left",
        //         "targets": "_all"
        //     }, ]

        // });
    </script>
@endsection
