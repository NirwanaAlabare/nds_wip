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
    <div class="modal fade" id="exampleModalEdit" tabindex="-1" role="dialog" aria-labelledby="exampleModalEditLabel"
        aria-hidden="true">
        <form action="{{ route('update_tmp_dc_in') }}" method="post" onsubmit="submitForm(this, event)">
            @method('PUT')
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header bg-sb text-light">
                        <h1 class="modal-title fs-5" id="exampleModalEditLabel"></h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class='row'>
                            <div class='col-sm-3'>
                                <div class='form-group'>
                                    <label class='form-label'><small>Qty</small></label>
                                    <input type='text' class='form-control' id='txtqty' name='txtqty' value = ''
                                        readonly>
                                </div>
                            </div>
                            <div class='col-sm-3'>
                                <div class='form-group'>
                                    <label class='form-label'><small>Reject</small></label>
                                    <input type='number' class='form-control' id='txtqtyreject' name='txtqtyreject'
                                        oninput='sum();' autocomplete='off'>
                                </div>
                            </div>
                            <div class='col-sm-3'>
                                <div class='form-group'>
                                    <label class='form-label'><small>Replacement</small></label>
                                    <input type='number' class='form-control' id='txtqtyreplace' name='txtqtyreplace'>
                                </div>
                            </div>
                            <div class='col-sm-3'>
                                <div class='form-group'>
                                    <label class='form-label'><small>Qty In</small></label>
                                    <input type='number' class='form-control' id='txtqtyin' name='txtqtyin' value = ''
                                        readonly>
                                    <input type='hidden' class='form-control' id='id_c' name='id_c' value = ''>
                                </div>
                            </div>
                        </div>
                        <div class='row'>
                            <div class='col-sm-12'>
                                <div class='form-group'>
                                    <label class='form-label'><small>Tujuan</small></label>
                                    <select class="form-control select2bs4" id="cbotuj" name="cbotuj"
                                        onchange='getalokasi();' style="width: 100%;">
                                        <option selected="selected" value="">Pilih Tujuan</option>
                                        @foreach ($data_tujuan as $datatujuan)
                                            <option value="{{ $datatujuan->tujuan }}">
                                                {{ $datatujuan->alokasi }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class='row'>
                            <div class='col-sm-12'>
                                <div class='form-group'>
                                    <label class='form-label'><small>Alokasi</small></label>
                                    <select class='form-control select2bs4' style='width: 100%;' name='cboalokasi'
                                        id='cboalokasi'></select>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-sb">Simpan </button>
                    </div>
                </div>
            </div>
        </form>
    </div>




    <form action="{{ route('store_dc_in') }}" method="post" id="store_dc_in" name='form'
        onsubmit="submitDCInForm(this, event)">

        <div class="card card-outline">
            <div class="card-header">
                <h5 style="text-align:center;">
                    <b>Scan DC IN </b>
                </h5>
            </div>
            <div class='row'>
                <div class="col-md-12">
                    <div class="card card-primary collapsed-card">
                        <div class="card-header">
                            <h3 class="card-title">Header Data :</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i
                                        class="fas fa-plus"></i></button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label class="form-label label-input text-xs">No Cut</label>
                                        <input class="form-control form-control-sm" type="text" id="no_cut"
                                            name = "no_cut" value = "{{ $header->no_cut }}" readonly>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label class="form-label label-input text-xs">No Form</label>
                                        <input class="form-control form-control-sm" type="text" id="no_form"
                                            name = "no_form" value = "{{ $header->no_form }}" readonly>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label class="form-label label-input text-xs">WS</label>
                                        <input class="form-control form-control-sm" type="text" id="ws"
                                            name = "ws" value = "{{ $header->act_costing_ws }}" readonly>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label class="form-label label-input text-xs">Buyer</label>
                                        <input class="form-control form-control-sm" type="text" id="buyer"
                                            name = "buyer" value = "{{ $header->buyer }}" readonly>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label class="form-label label-input text-xs">Style</label>
                                        <input class="form-control form-control-sm" type="text" id="style"
                                            name = "style" value = "{{ $header->style }}" readonly>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label class="form-label label-input text-xs">Color</label>
                                        <input class="form-control form-control-sm" type="text" id="color"
                                            name = "color" value = "{{ $header->color }}" readonly>
                                    </div>
                                </div>
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label class="form-label label-input text-xs">List Part</label>
                                        <input class="form-control form-control-sm" type="text" id="list_part"
                                            name = "list_part" value = "{{ $header->list_part }}" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card card-sb card-outline">
                <div class="card-header">
                    <h5 class="card-title fw-bold mb-0">Scan QR Stocker DC In</h5>
                </div>
                <div class="card-body">
                    <div class="row justify-content-center align-items-end">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label label-input"><small><b>Scan QR Stocker</b></small></label>
                                <div class="input-group">
                                    <input type="text" class="form-control form-control-sm border-input"
                                        name="txtqrstocker" id="txtqrstocker" autocomplete="off" enterkeyhint="go"
                                        onkeyup="if (event.keyCode == 13)
                                    document.getElementById('scanqr').click()"
                                        autofocus>
                                    {{-- <input type="button" class="btn btn-sm btn-primary" value="Scan Line" /> --}}
                                    {{-- style="display: none;" --}}
                                    <button class="btn btn-sm btn-primary" type="button" id="scanqr"
                                        onclick="scan_qr()">Scan</button>
                                </div>
                            </div>
                        </div>
                        <div class="col-2">
                            <div></div>
                        </div>
                        <div class="col-8">
                            <div id="reader"></div>
                        </div>
                        <div class="col-2">
                        </div>
                    </div>
                </div>
            </div>
    </form>
    <div class="row">
        <div class="col-md-6">
            <div class="card card-info ">
                <div class="card-header">
                    <h3 class="card-title">Input QR :</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse"><i
                                class="fas fa-minus"></i></button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="datatable-input" class="table table-bordered table-sm w-100 display nowrap">
                            <thead>
                                <tr>
                                    <th>ID QR</th>
                                    <th>Size</th>
                                    <th>Color</th>
                                    <th>Shade</th>
                                    <th>Nama Part</th>
                                    <th>Qty</th>
                                    <th>Reject</th>
                                    <th>Replace</th>
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
        <div class="col-md-6">
            <div class="card card-primary ">
                <div class="card-header">
                    <h3 class="card-title">Stocker Info :</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse"><i
                                class="fas fa-minus"></i></button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="datatable-info" class="table table-bordered table-sm w-100 display nowrap">
                            <thead>
                                <tr>
                                    <th>ID QR</th>
                                    <th>Size</th>
                                    <th>Color</th>
                                    <th>Shade</th>
                                    <th>Nama Part</th>
                                    <th>Range Awal</th>
                                    <th>Range Akhir</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <a href="{{ route('create-mut-karyawan') }}" class="btn btn-success btn-sm mb-3">
        <i class="fas fa-qrcode fa-spin fa-lg"></i>
        Simpan
    </a>
    </div>
@endsection

@section('custom-script')
    <!-- DataTables  & Plugins -->
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <!-- Select2 -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        $(document).on('select2:open', () => {
            document.querySelector('.select2-search__field').focus();
        });

        $('.select2').select2()

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
                    initScan();

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
                            width: 250,
                            height: 250
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
            // $("#nm_line").val('');
            // $("#jml_org").val('');
            initScan();
            getinfo();
            gettmp();

        })

        $(document).ready(function() {
            if (window.location.reload) {
                dataTableReload();
            }
        });


        window.addEventListener("focus", () => {
            dataTableReload();
        });

        function getinfo() {
            let no_form = document.form.no_form.value;
            let datatable = $("#datatable-info").DataTable({
                ordering: false,
                destroy: true,
                processing: true,
                serverSide: true,
                info: false,
                paging: false,
                scrollX: true,
                searching: false,
                ajax: {
                    url: '{{ route('getdata_stocker_info') }}',
                    data: {
                        no_form: no_form
                    },
                },
                columns: [{
                        data: 'id_qr_stocker'
                    },
                    {
                        data: 'size'
                    },
                    {
                        data: 'color'
                    },
                    {
                        data: 'shade'
                    },
                    {
                        data: 'nama_part'
                    },
                    {
                        data: 'range_awal'
                    },
                    {
                        data: 'range_akhir'
                    }
                ],
            });
        };
        let datatable_info = $("#datatable_info").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            info: false,
            paging: false,
            scrollX: true,
            ajax: {
                url: '{{ route('getdata_dc_in') }}'
            },
            "fnCreatedRow": function(row, data, index) {
                $('td', row).eq(0).html(index + 1);
            },
            columns: [{
                    data: 'id_qr_stocker'
                },
                {
                    data: 'id_qr_stocker'
                }
            ],
        });

        function gettmp() {
            let no_form = document.form.no_form.value;
            let datatable_input = $("#datatable-input").DataTable({
                ordering: false,
                destroy: true,
                processing: true,
                serverSide: true,
                info: false,
                paging: false,
                scrollX: true,
                searching: false,
                ajax: {
                    url: '{{ route('getdata_stocker_input') }}',
                    data: {
                        no_form: no_form
                    },
                },
                columns: [{
                        data: 'id_qr_stocker'
                    },
                    {
                        data: 'size'
                    },
                    {
                        data: 'color'
                    },
                    {
                        data: 'shade'
                    },
                    {
                        data: 'nama_part'
                    },
                    {
                        data: 'qty_ply'
                    },
                    {
                        data: 'qty_reject'
                    },
                    {
                        data: 'qty_replace'
                    }
                ],
                columnDefs: [{
                    targets: [8],
                    className: "align-middle",
                    render: (data, type, row, meta) => {
                        return `
                                <div class='d-flex gap-1 justify-content-center'>
                                    <a class='btn btn-primary btn-sm' data-bs-toggle="modal" data-bs-target="#exampleModalEdit"
                                    onclick="getdetail('` + row.id_qr_stocker + `');">
                                        <i class='fa fa-search'></i>
                                    </a>
                                </div>
                            `;
                    }
                }],
            });
        };

        let datatable_input = $("#datatable_input").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            info: false,
            paging: false,
            scrollX: true,
            ajax: {
                url: '{{ route('getdata_stocker_input') }}'
            }
        });


        function scan_qr() {
            let txtqrstocker = document.form.txtqrstocker.value;
            let no_form = document.form.no_form.value;
            $.ajax({
                type: "post",
                url: '{{ route('store_dc_in') }}',
                data: {
                    txtqrstocker: txtqrstocker,
                    no_form: no_form
                },
                success: function(response) {
                    $("#txtqrstocker").val('');
                    getinfo();
                    gettmp();
                    initScan();
                },
                error: function(request, status, error) {
                    alert(request.responseText);
                },
            });
        };

        function getdetail(id_c) {
            $("#exampleModalEditLabel").html(id_c);
            jQuery.ajax({
                url: '{{ route('show_tmp_dc_in') }}',
                method: 'POST',
                data: {
                    id_c: id_c
                },
                dataType: 'json',
                success: async function(response) {
                    sum();
                    document.getElementById('txtqty').value = response.qty_ply;
                    document.getElementById('id_c').value = response.id_qr_stocker;
                    document.getElementById('txtqtyreject').value = response.qty_reject;
                    document.getElementById('txtqtyreplace').value = response.qty_replace;
                    // document.getElementById('cbotuj').value = response.tujuan;
                    $("#cbotuj").val(response.tujuan).trigger('change');
                    document.getElementById('cboalokasi').value = response.alokasi;

                },
                error: function(request, status, error) {
                    alert(request.responseText);
                },
            });
        };

        function sum() {
            let txtqty = document.getElementById('txtqty').value;
            let txtqtyreject = document.getElementById('txtqtyreject').value;
            document.getElementById("txtqtyin").value = +txtqty;
            let result = parseFloat(txtqty) - parseFloat(txtqtyreject);
            let result_fix = Math.ceil(result)
            if (!isNaN(result_fix)) {
                document.getElementById("txtqtyin").value = result_fix;
            }
        }


        function getalokasi() {
            let tujuan = document.getElementById('cbotuj').value;
            let html = $.ajax({
                type: "POST",
                url: '{{ route('get_alokasi') }}',
                data: {
                    tujuan: tujuan
                },
                async: false
            }).responseText;

            console.log(html != "");

            if (html != "") {
                $("#cboalokasi").html(html);
            }
        };

        function dataTableReload() {
            datatable_info.ajax.reload();
        }
    </script>
@endsection
