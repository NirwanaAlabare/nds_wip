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
    <div class="card card-info">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center ">
                <h5 class="card-title fw-bold mb-0"><i class="fas fa-compress"></i> Scan Packing Out</h5>
                <a href="{{ route('transfer-garment') }}" class="btn btn-sm btn-primary">
                    <i class="fa fa-reply"></i> Kembali
                </a>
            </div>
        </div>
        <form id="form_h" name='form_h'>
            <div class="row">
                <div class="col-md-6">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h5 class="card-title"><i class="fas fa-search"></i> Filter </h5>
                        </div>
                        <div class="card-body">
                            <div class="col-sm">
                                <div class="form-group">
                                    <label><small><b>PO # :</b></small></label>
                                    <input type="hidden" class="form-control" id="user" name="user"
                                        value="{{ $user }}">
                                    <input type="hidden" class="form-control" id="cbopo_det" name="cbopo_det">
                                    <input type="hidden" class="form-control" id="txtdest" name="txtdest">
                                    <select class="form-control select2bs4 form-control-sm" id="cbopo" name="cbopo"
                                        style="width: 100%;" onchange="getpo();getno_carton();dataTableHistoryReload();">
                                        <option selected="selected" value="" disabled="true">Pilih PO</option>
                                        @foreach ($data_po as $datapo)
                                            <option value="{{ $datapo->isi }}">
                                                {{ $datapo->tampil }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm">
                                <div class="form-group">
                                    <label><small><b>No. Carton # :</b></small></label>
                                    <select class='form-control select2bs4 form-control-sm' style='width: 100%;'
                                        name='cbono_carton' id='cbono_carton'
                                        onchange = "dataTableSummaryReload();dataTableHistoryReload();"></select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label label-input"><small><b>Barcode</b></small></label>
                                <div class="input-group">
                                    <input type="text" class="form-control form-control-sm border-input" name="barcode"
                                        id="barcode" data-prevent-submit="true" autocomplete="off" enterkeyhint="enter"
                                        autofocus>
                                    <button class="btn btn-sm btn-primary" type="button" id="scan_qr"
                                        onclick="scan_barcode()">Scan</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h5 class="card-title"><i class="fas fa-list"></i> Summary </h5>
                        </div>
                        <div class="card-body">
                            {{-- <div class='row'>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="form-label"><small><b>Qty Max Karton</b></small></label>
                                        <input type="text" class="form-control form-control-sm" id = "qty_max_carton"
                                            name = "qty_max_carton" readonly>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="form-label"><small><b>Qty Karton Terisi </b></small></label>
                                        <input type="text" class="form-control form-control-sm" id = "qty_terisi_carton"
                                            name = "qty_terisi_carton" readonly>
                                    </div>
                                </div>
                            </div> --}}
                            <div class="table-responsive">
                                <table id="datatable_summary" class="table table-bordered 100 text-nowrap">
                                    <thead>
                                        <tr>
                                            <th>PO #</th>
                                            <th>Color</th>
                                            <th>Size</th>
                                            <th>Qty Packing List</th>
                                            <th>Qty Input</th>
                                        </tr>
                                    </thead>
                                    <tfoot>
                                        <tr>
                                            <th>Total</th>
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
                </div>
            </div>
        </form>
        <div class="row">
            <div class="col-md-6">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h5 class="card-title"><i class="fas fa-list"></i> Total Input Hari Ini</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <input class="form-control form-control-lg" style="text-align:center;" id="tot_input"
                                name="tot_input" type="text" readonly>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h5 class="card-title"><i class="fas fa-history"></i> History Transaksi </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="datatable_history" class="table table-bordered 100 text-nowrap">
                                <thead>
                                    <tr>
                                        <th>Tgl. Input</th>
                                        <th>PO</th>
                                        <th>Barcode</th>
                                        <th>Color</th>
                                        <th>Size</th>
                                        <th>Act</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
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
        $(document).ready(function() {
            $("#cbopo").val('').trigger('change');
            // $("#cbono_carton").val('').trigger('change');
            $("#cbopo_det").val('');
            $("#txtdest").val('');
            gettotal_input();
            $("#qty_max_carton").val('');
            $("#qty_terisi_carton").val('');
        })

        document.getElementById("barcode").onkeypress = function(e) {
            var key = e.charCode || e.keyCode || 0;
            if (key == 13) {
                e.preventDefault();

                if (e.target.getAttribute('data-prevent-submit') == "true") {
                    scan_barcode();
                }
            }
        }


        function gettotal_input() {
            let user = document.form_h.user.value;
            $.ajax({
                url: '{{ route('packing_out_show_tot_input') }}',
                method: 'get',
                data: {
                    user: user
                },
                dataType: 'json',
                success: function(response) {
                    document.getElementById('tot_input').value = response.tot_input;
                },
                error: function(request, status, error) {
                    alert(request.responseText);
                },
            });
        };

        function getno_carton() {
            let cbopo = document.form_h.cbopo.value;

            let html = $.ajax({
                type: "GET",
                url: '{{ route('getno_carton') }}',
                data: {
                    cbopo: cbopo
                },
                async: false
            }).responseText;
            // console.log(html != "");
            console.log(cbopo);
            if (html != "") {
                $("#cbono_carton").html(html);
            }
        };

        function getpo() {
            let cbopo = document.form_h.cbopo.value;
            let html = $.ajax({
                type: "get",
                url: '{{ route('getpo') }}',
                data: {
                    cbopo: cbopo
                },
                dataType: 'json',
                success: function(response) {
                    console.log(response);
                    document.getElementById('cbopo_det').value = response.po;
                    document.getElementById('txtdest').value = response.dest;
                },
                error: function(request, status, error) {

                },
            });
        };


        function dataTableSummaryReload() {
            let datatable = $("#datatable_summary").DataTable({

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
                        .column(3)
                        .data()
                        .reduce(function(a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);

                    var sumTotalA = api
                        .column(4)
                        .data()
                        .reduce(function(a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);

                    // Update footer by showing the total with the reference of the column index
                    $(api.column(0).footer()).html('Total');
                    $(api.column(3).footer()).html(sumTotal);
                    $(api.column(4).footer()).html(sumTotalA);
                },

                ordering: false,
                processing: true,
                serverSide: true,
                paging: false,
                destroy: true,
                info: false,
                searching: false,
                ajax: {
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: '{{ route('packing_out_show_summary') }}',
                    dataType: 'json',
                    dataSrc: 'data',
                    data: function(d) {
                        d.cbopo = $('#cbopo_det').val();
                        d.cbono_carton = $('#cbono_carton').val();
                        d.txtdest = $('#txtdest').val();
                    },
                },
                columns: [{
                        data: 'po',
                    },
                    {
                        data: 'color',
                    },
                    {
                        data: 'size',
                    },
                    {
                        data: 'qty',
                    },
                    {
                        data: 'tot_scan',
                    },
                ],

                // Add createdRow callback

                createdRow: function(row, data, dataIndex) {

                    // Check if qty and tot_scan are equal

                    if (data.qty == data.tot_scan) {
                        $(row).css({
                            'color': 'green',
                            'font-weight': 'bold'
                        });
                    } else if (data.qty <= data.tot_scan) {
                        $(row).css({
                            'color': 'blue',
                            'font-weight': 'bold'
                        });
                    } else {
                        $(row).css({
                            'color': 'black',
                            'font-weight': 'bold'
                        });
                    }

                },

            });
        }

        function dataTableHistoryReload() {
            let datatable = $("#datatable_history").DataTable({
                ordering: false,
                processing: true,
                serverSide: true,
                paging: true,
                destroy: true,
                info: true,
                searching: true,
                ajax: {
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: '{{ route('packing_out_show_history') }}',
                    dataType: 'json',
                    dataSrc: 'data',
                    data: function(d) {
                        d.cbopo = $('#cbopo_det').val();
                        d.cbono_carton = $('#cbono_carton').val();
                        d.txtdest = $('#txtdest').val();
                    },
                },
                columns: [{
                        data: 'created_at',
                    },
                    {
                        data: 'po',
                    },
                    {
                        data: 'barcode',
                    },
                    {
                        data: 'color',
                    },
                    {
                        data: 'size',
                    },
                    {
                        data: 'id',
                    },
                ],
                columnDefs: [{
                        targets: [5],
                        render: (data, type, row, meta) => {
                            if (row.cek_stat == 'ok') {
                                return `
                <div
                class='d-flex gap-1 justify-content-center'>
                <a class='btn btn-danger btn-sm'  data-bs-toggle="tooltip"
                onclick="hapus(` + row.id + `)"><i class='fas fa-trash'></i></a>
                </div>
                    `;
                            } else {
                                return `
                <div
                </div>
                    `;
                            }

                        }
                    },
                    {
                        "className": "align-middle",
                        "targets": "_all"
                    },
                ]
            });
        }

        function hapus(id_history) {
            $.ajax({
                type: "post",
                url: '{{ route('packing_out_hapus_history') }}',
                data: {
                    id_history: id_history
                },
                success: async function(res) {
                    iziToast.success({
                        message: 'Data Berhasil Dihapus',
                        position: 'topCenter'
                    });
                    dataTableSummaryReload();
                    dataTableHistoryReload();
                    document.getElementById('barcode').value = "";
                    document.getElementById("barcode").focus();
                    gettotal_input();
                }
            });

        }


        function scan_barcode() {
            let cbopo = document.form_h.cbopo.value;
            let cbono_carton = document.form_h.cbono_carton.value;
            let txtdest = document.form_h.txtdest.value;
            let barcode = document.form_h.barcode.value;

            if (cbopo == '') {
                iziToast.warning({
                    message: 'PO masih kosong',
                    position: 'topCenter'
                });
                document.getElementById('barcode').value = "";
            }

            if (cbono_carton == '') {
                iziToast.warning({
                    message: 'No Carton masih kosong',
                    position: 'topCenter'
                });
                document.getElementById('barcode').value = "";
            }

            if (cbopo != '' && cbono_carton != '') {
                $.ajax({
                    type: "post",
                    url: '{{ route('store_packing_out') }}',
                    data: {
                        cbopo: cbopo,
                        cbono_carton: cbono_carton,
                        barcode: barcode,
                        txtdest: txtdest
                    },
                    success: function(response) {
                        console.log(response);
                        if (response.icon == 'salah') {
                            iziToast.warning({
                                message: response.msg,
                                position: 'topCenter',
                                timeout: 800,
                            });
                        } else if (response.icon == 'lebih') {
                            iziToast.info({
                                message: response.msg,
                                position: 'topCenter',
                                timeout: 1000,
                            });
                            gettotal_input();
                        } else {
                            iziToast.success({
                                message: response.msg,
                                position: 'topCenter',
                                timeout: 800,
                            });
                            gettotal_input();
                        }
                        dataTableSummaryReload();
                        dataTableHistoryReload();
                        document.getElementById('barcode').value = "";
                        document.getElementById("barcode").focus();
                        gettotal_input();
                        // get_sum_carton();
                    },
                    // error: function(request, status, error) {
                    //     alert(request.responseText);
                    // },
                });

            }

        };


        // function get_sum_carton() {
        //     let po_data = $('#cbopo_det').val();
        //     let no_carton_data = document.form_h.cbono_carton.value;
        //     $.ajax({
        //         url: '{{ route('show_sum_max_carton') }}',
        //         method: 'get',
        //         data: {
        //             po_data: po_data,
        //             no_carton_data: no_carton_data
        //         },
        //         dataType: 'json',
        //         success: function(response) {
        //             document.getElementById('qty_max_carton').value = response.qty_isi;
        //             document.getElementById('qty_terisi_carton').value = response.tot_out;
        //         },
        //         error: function(request, status, error) {
        //             alert(request.responseText);
        //         },
        //     });
        // };
    </script>
@endsection
