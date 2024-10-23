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
    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <form action="{{ route('store-secondary-inhouse') }}" method="post" onsubmit="submitForm(this, event)"
            name='form' id='form'>
            @method('POST')
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header bg-sb text-light">
                        <h1 class="modal-title fs-5">Scan DC IN</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row ">
                            <div class="col-sm-12">
                                <div class="mb-3">
                                    <label class="form-label label-input">Scan QR Stocker</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control form-control-sm border-input"
                                            name="txtqrstocker" id="txtqrstocker" autocomplete="off" enterkeyhint="go"
                                            onkeyup="if (event.keyCode == 13) document.getElementById('scanqr').click()"
                                            autofocus>
                                        {{-- <input type="button" class="btn btn-sm btn-primary" value="Scan Line" /> --}}
                                        {{-- style="display: none;" --}}
                                        <button class="btn btn-sm btn-primary" type="button" id="scanqr" onclick="scan_qr()">Scan</button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-3">
                                <div></div>
                            </div>
                            <div class="col-6">
                                <div id="reader"></div>
                            </div>
                            <div class="col-3">
                            </div>
                        </div>

                        <div class="row">
                            <div class='col-sm-3'>
                                <div class='form-group'>
                                    <label class='form-label'><small>No Stocker</small></label>
                                    <input type='text' class='form-control form-control-sm' id='txtno_stocker'
                                        name='txtno_stocker' value = '' readonly>
                                    <input type='hidden' class='form-control form-control-sm' id='txtno_form'
                                        name='txtno_form' value = '' readonly>
                                </div>
                            </div>
                            <div class='col-sm-3'>
                                <div class='form-group'>
                                    <label class='form-label'><small>WS</small></label>
                                    <input type='text' class='form-control form-control-sm' id='txtws' name='txtws'
                                        value = '' readonly>
                                </div>
                            </div>
                            <div class='col-sm-3'>
                                <div class='form-group'>
                                    <label class='form-label'><small>Buyer</small></label>
                                    <input type='text' class='form-control form-control-sm' id='txtbuyer'
                                        name='txtbuyer' value = '' readonly>
                                </div>
                            </div>
                            <div class='col-sm-3'>
                                <div class='form-group'>
                                    <label class='form-label'><small>No Cut</small></label>
                                    <input type='text' class='form-control form-control-sm' id='txtno_cut'
                                        name='txtno_cut' value = '' readonly>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class='col-sm-12'>
                                <div class="table-responsive">
                                    <table id="datatable-scan" class="table table-bordered  table-sm w-100">
                                        <thead>
                                            <tr>
                                                <th>No Stocker</th>
                                                <th>Part</th>
                                                <th>Tujuan</th>
                                                <th>Tempat</th>
                                                <th>Proses / Lokasi</th>
                                                <th>Qty In</th>
                                                <th>Act</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-success"><i class="fas fa-check"></i> Simpan </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0">DC IN <i class="fas fa-podcast"></i></h5>
        </div>
        <div class="card-body">
            <div class="mb-3">
                {{-- <button class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Baru</button> --}}
                <a href="{{ route('create-dc-in') }}" class="btn btn-primary btn-sm">
                    Baru
                    <i class="fas fa-plus"></i>
                </a>
            </div>
            <div class="d-flex align-items-end gap-3 mb-3">
                <div class="mb-3">
                    <label class="form-label"><small>Tgl Awal</small></label>
                    <input type="date" class="form-control form-control-sm" id="tgl-awal" name="tgl_awal"
                        value="{{ date('Y-m-d') }}" onchange="datatableReload()">
                </div>
                <div class="mb-3">
                    <label class="form-label"><small>Tgl Akhir</small></label>
                    <input type="date" class="form-control form-control-sm" id="tgl-akhir" name="tgl_akhir"
                        value="{{ date('Y-m-d') }}" onchange="datatableReload()">
                </div>
            </div>
            <h5 class="card-title fw-bold mb-3">List Transaksi DC IN</h5>
            <br>
            <ul class="nav nav-tabs mt-3">
                <li class="nav-item">
                  <a class="nav-link active" style="cursor: pointer;" id="list" onclick='switchTable("list")'><i class="fa fa-list"></i> List</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" style="cursor: pointer;" id="list-detail" onclick='switchTable("detail")'><i class="fa fa-list"></i> Detail</a>
                </li>
            </ul>
            <br>
            <div class="table-responsive" id="list-dc">
                <table id="datatable-input" class="table table-bordered table-striped table-sm w-100 text-nowrap">
                    <thead>
                        <tr>
                            <th>Tgl Transaksi</th>
                            <th>ID QR</th>
                            <th>WS</th>
                            <th>Style</th>
                            <th>Color</th>
                            <th>Part</th>
                            <th>Size</th>
                            <th>No. Cut</th>
                            <th>Tujuan</th>
                            <th>Tempat</th>
                            <th>Lokasi</th>
                            <th>Qty Awal</th>
                            <th>Qty Reject</th>
                            <th>Qty Replace</th>
                            <th>Qty In</th>
                            <th>Buyer</th>
                            <th>User</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th colspan="11"></th>
                            <th>
                                <input type = 'text' class="form-control form-control-sm" style="width:75px" readonly id = 'total_qty_awal'>
                            </th>
                            <th>
                                <input type = 'text' class="form-control form-control-sm" style="width:75px" readonly id = 'total_qty_reject'>
                            </th>
                            <th>
                                <input type = 'text' class="form-control form-control-sm" style="width:75px" readonly id = 'total_qty_replace'>
                            </th>
                            <th>
                                <input type = 'text' class="form-control form-control-sm" style="width:75px" readonly id = 'total_qty_in'>
                            </th>
                            <th colspan="2"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="table-responsive" id="detail-dc">
                <table id="datatable-detail" class="table table-bordered table-striped table-sm w-100">
                    <thead>
                        <tr>
                            <th>WS</th>
                            <th>Buyer</th>
                            <th>Style</th>
                            <th>Color</th>
                            <th>In</th>
                            <th>Reject</th>
                            <th>Replace</th>
                            <th>Out</th>
                            <th>Balance</th>
                            <th>Proses</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th colspan="4"></th>
                            <th><input type='text' class="form-control form-control-sm" style="width:75px" readonly id='total_qty_int'> </th>
                            <th><input type='text' class="form-control form-control-sm" style="width:75px" readonly id='total_qty_reject_det'> </th>
                            <th><input type='text' class="form-control form-control-sm" style="width:75px" readonly id='total_qty_replace_det'> </th>
                            <th><input type='text' class="form-control form-control-sm" style="width:75px" readonly id='total_qty_out'> </th>
                            <th><input type='text' class="form-control form-control-sm" style="width:75px" readonly id='total_qty_balance'> </th>
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

    <!-- Select2 -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        var listFilter = [
            'tgl_trans_filter',
            'id_qr_filter',
            'ws_filter',
            'style_filter',
            'color_filter',
            'part_filter',
            'size_filter',
            'no_cut_filter',
            'tujuan_filter',
            'tempat_filter',
            'lokasi_filter',
            'qty_awal_filter',
            'qty_reject_filter',
            'qty_replace_filter',
            'qty_in_filter',
            'buyer_filter',
            'user_filter'
        ];

        $('#datatable-input thead tr').clone(true).appendTo('#datatable-input thead');
        $('#datatable-input thead tr:eq(1) th').each(function(i) {
            var title = $(this).text();
            $(this).html('<input type="text" class="form-control form-control-sm" id="'+listFilter[i]+'"/>');

            $('input', this).on('keyup change', function() {
                if (datatable.column(i).search() !== this.value) {
                    datatable
                        .column(i)
                        .search(this.value)
                        .draw();
                }
            });
        });

        let datatable = $("#datatable-input").DataTable({
            "footerCallback": async function(row, data, start, end, display) {
                var api = this.api(),data;

                $(api.column(0).footer()).html('Total');
                $(api.column(11).footer()).html("...");
                $(api.column(12).footer()).html("...");
                $(api.column(13).footer()).html("...");
                $(api.column(14).footer()).html("...");

                // // converting to interger to find total
                // var intVal = function(i) {
                //     return typeof i === 'string' ?
                //         i.replace(/[\$,]/g, '') * 1 :
                //         typeof i === 'number' ?
                //         i : 0;
                // };

                // // computing column Total of the complete result
                // var sumTotal = api
                //     .column(11)
                //     .data()
                //     .reduce(function(a, b) {
                //         return intVal(a) + intVal(b);
                //     }, 0);

                // var sumTotalAwal = api
                //     .column(11)
                //     .data()
                //     .reduce(function(a, b) {
                //         return intVal(a) + intVal(b);
                //     }, 0);

                // var sumTotalReject = api
                //     .column(12)
                //     .data()
                //     .reduce(function(a, b) {
                //         return intVal(a) + intVal(b);
                //     }, 0);

                // var sumTotalReplace = api
                //     .column(13)
                //     .data()
                //     .reduce(function(a, b) {
                //         return intVal(a) + intVal(b);
                //     }, 0);

                // var sumTotalIn = api
                //     .column(14)
                //     .data()
                //     .reduce(function(a, b) {
                //         return intVal(a) + intVal(b);
                //     }, 0);

                $.ajax({
                    url: '{{ route('total_dc_in') }}',
                    dataType: 'json',
                    dataSrc: 'data',
                    data: {
                        'dateFrom': $('#tgl-awal').val(),
                        'dateTo': $('#tgl-akhir').val(),
                        'tgl_trans': $('#tgl_trans_filter').val(),
                        'id_qr': $('#id_qr_filter').val(),
                        'ws': $('#ws_filter').val(),
                        'style': $('#style_filter').val(),
                        'color': $('#color_filter').val(),
                        'part': $('#part_filter').val(),
                        'size': $('#size_filter').val(),
                        'no_cut': $('#no_cut_filter').val(),
                        'tujuan': $('#tujuan_filter').val(),
                        'tempat': $('#tempat_filter').val(),
                        'lokasi': $('#lokasi_filter').val(),
                        'qty_awal': $('#qty_awal_filter').val(),
                        'qty_reject': $('#qty_reject_filter').val(),
                        'qty_replace': $('#qty_replace_filter').val(),
                        'qty_in': $('#qty_in_filter').val(),
                        'qty_buyer': $('#qty_buyer_filter').val(),
                        'user':$('#user_filter').val()
                    },
                    success: function(response) {
                        console.log(response);

                        if (response && response[0]) {
                            // Update footer by showing the total with the reference of the column index
                            $(api.column(0).footer()).html('Total');
                            $(api.column(11).footer()).html(response[0]['qty_awal']);
                            $(api.column(12).footer()).html(response[0]['qty_reject']);
                            $(api.column(13).footer()).html(response[0]['qty_replace']);
                            $(api.column(14).footer()).html(response[0]['qty_in']);
                        }
                    },
                    error: function(request, status, error) {
                        alert('cek');
                    },
                })
            },
            ordering: false,
            processing: true,
            serverSide: true,
            // paging: false,
            searching: true,
            scrollY: '300px',
            scrollX: '300px',
            scrollCollapse: true,
            ajax: {
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route('dc-in') }}',
                dataType: 'json',
                dataSrc: 'data',
                data: function(d) {
                    d.dateFrom = $('#tgl-awal').val();
                    d.dateTo = $('#tgl-akhir').val();
                },
            },
            columns: [{
                    data: 'tgl_trans_fix',
                },
                {
                    data: 'id_qr_stocker',
                },
                {
                    data: 'act_costing_ws',
                },
                {
                    data: 'style',
                },
                {
                    data: 'color',
                },
                {
                    data: 'nama_part',
                },
                {
                    data: 'size',
                },
                {
                    data: 'no_cut',
                },
                {
                    data: 'tujuan',
                },
                {
                    data: 'tempat',
                },
                {
                    data: 'lokasi',
                },
                {
                    data: 'qty_awal',
                },
                {
                    data: 'qty_reject',
                },
                {
                    data: 'qty_replace',
                },
                {
                    data: 'qty_in',
                },
                {
                    data: 'buyer',
                },
                {
                    data: 'user',
                },
            ],
            columnDefs: [{
                targets: "_all",
                className: "text-nowrap"
            }]
        });

        $('#datatable-detail thead tr').clone(true).appendTo('#datatable-detail thead');
        $('#datatable-detail thead tr:eq(1) th').each(function(i) {
            var title = $(this).text();
            $(this).html('<input type="text" class="form-control form-control-sm"/>');

            $('input', this).on('keyup change', function() {
                if (datatable_detail.column(i).search() !== this.value) {
                    datatable_detail
                        .column(i)
                        .search(this.value)
                        .draw();
                }
            });
        });

        let datatable_detail = $("#datatable-detail").DataTable({
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

                var sumTotalIn = api
                    .column(4)
                    .data()
                    .reduce(function(a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                var sumTotalReject = api
                    .column(5)
                    .data()
                    .reduce(function(a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                var sumTotalReplace = api
                    .column(6)
                    .data()
                    .reduce(function(a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                var sumTotalOut = api
                    .column(7)
                    .data()
                    .reduce(function(a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                var sumTotalBalance = api
                    .column(8)
                    .data()
                    .reduce(function(a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                // Update footer by showing the total with the reference of the column index
                $(api.column(0).footer()).html('Total');
                $(api.column(4).footer()).html(sumTotalIn);
                $(api.column(5).footer()).html(sumTotalReject);
                $(api.column(6).footer()).html(sumTotalReplace);
                $(api.column(7).footer()).html(sumTotalOut);
                $(api.column(8).footer()).html(sumTotalBalance);
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
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route('detail_dc_in') }}',
                dataType: 'json',
                dataSrc: 'data',
                data: function(d) {
                    d.dateFrom = $('#tgl-awal').val();
                    d.dateTo = $('#tgl-akhir').val();
                },
            },
            columns: [
                {
                    data: 'act_costing_ws',
                },
                {
                    data: 'buyer',
                },
                {
                    data: 'styleno',
                },
                {
                    data: 'color',
                },
                {
                    data: 'qty_in',
                },
                {
                    data: 'qty_reject',
                },
                {
                    data: 'qty_replace',
                },
                {
                    data: 'qty_out',
                },
                {
                    data: 'balance',
                },
                {
                    data: 'lokasi',
                },
            ],
            columnDefs: [
                {
                    targets: "_all",
                    className: "text-nowrap"
                },
                {
                    targets: [8],
                    visible: false
                }
            ]
        });

        function datatableReload() {
            $('#datatable-input').DataTable().ajax.reload();
            $('#datatable-detail').DataTable().ajax.reload();
        }

    </script>




    <script>
        // $('.select2bs4').select2({
        //     theme: 'bootstrap4',
        //     dropdownParent: $("#editMejaModal")
        // })

        // Scan QR Module :
        // Variable List :
        var html5QrcodeScanner = null;

        // Function List :
        // -Initialize Scanner-
        async function initScan() {
            if (document.getElementById("reader")) {
                if (html5QrcodeScanner) {
                    await html5QrcodeScanner.clear();
                }

                function onScanSuccess(decodedText, decodedResult) {
                    // handle the scanned code as you like, for example:
                    console.log(`Code matched = ${decodedText}`, decodedResult);

                    // store to input text
                    // let breakDecodedText = decodedText.split('-');

                    document.getElementById('txtqrstocker').value = decodedText;

                    scan_qr();

                    html5QrcodeScanner.clear();

                }

                function onScanFailure(error) {
                    // handle scan failure, usually better to ignore and keep scanning.
                    // for example:
                    console.warn(`Code scan error = ${error}`);
                }

                html5QrcodeScanner = new Html5QrcodeScanner(
                    "reader", {
                        fps: 10,
                        qrbox: {
                            width: 200,
                            height: 200
                        }
                    },
                    /* verbose= */
                    false);


                html5QrcodeScanner.render(onScanSuccess, onScanFailure);
            }
        }
    </script>
    <script>
        $(document).ready(function() {
            reset();

            switchTable("list");
        })

        $('#exampleModal').on('show.bs.modal', function(e) {
            initScan();
            // $(document).on('select2:open', () => {
            //     document.querySelector('.select2-search__field').focus();
            // });
            // $('.select2').select2()
            $('.select2bs4').select2({
                theme: 'bootstrap4',
                dropdownParent: $("#exampleModal")
            })
            $('#cbotuj').val('').trigger('change');

        })
    </script>
    <script>
        function switchTable(type) {
            if (type == 'list') {
                document.getElementById("list-detail").classList.remove("active");
                document.getElementById("list").classList.add("active");

                document.getElementById("detail-dc").classList.add("d-none");
                document.getElementById("list-dc").classList.remove("d-none");
            }

            if (type == 'detail') {
                document.getElementById("list").classList.remove("active");
                document.getElementById("list-detail").classList.add("active");

                document.getElementById("list-dc").classList.add("d-none");
                document.getElementById("detail-dc").classList.remove("d-none");
            }

            datatableReload();
        }

        function reset() {
            $("#form").trigger("reset");
            getdatatmp();
            // initScan();
        }

        function scan_qr() {
            let txtqrstocker = document.form.txtqrstocker.value;
            let html = $.ajax({
                type: "post",
                url: '{{ route('insert_tmp_dc_in') }}',
                data: {
                    txtqrstocker: txtqrstocker
                },
                success: function(response) {
                    $("#txtqrstocker").val('');
                    getdatatmp();
                },
                error: function(request, status, error) {
                    alert('cek');
                },
            });
        };

        function getdatatmp() {
            let datatable = $("#datatable-scan").DataTable({
                ordering: false,
                processing: true,
                serverSide: true,
                destroy: true,
                paging: false,
                ajax: {
                    url: '{{ route('get_tmp_dc_in') }}',
                    dataType: 'json',
                    dataSrc: 'data',
                },
                columns: [
                    {
                        data: 'id_qr_stocker',
                    },
                    {
                        data: 'id_qr_stocker',
                    },
                    {
                        data: 'id_qr_stocker',
                    },
                    {
                        data: 'id_qr_stocker',
                    },
                    {
                        data: 'id_qr_stocker',
                    },
                    {
                        data: 'id_qr_stocker',
                    },
                    {
                        data: 'id_qr_stocker',
                    },
                ],
            });
        };


        function sum() {
            let txtqty = document.getElementById('txtqtyawal').value;
            let txtqtyreject = document.getElementById('txtqtyreject').value;
            let txtqtyreplace = document.getElementById('txtqtyreplace').value;
            document.getElementById("txtqtyin").value = +txtqty;
            let result = parseFloat(txtqty) - parseFloat(txtqtyreject) + parseFloat(txtqtyreplace);
            let result_fix = Math.ceil(result)
            if (!isNaN(result_fix)) {
                document.getElementById("txtqtyin").value = result_fix;
            }
        }
    </script>
@endsection
