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
    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-sb text-light">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Detail Karton</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class='row'>
                        <div class='col-sm-3'>
                            <div class='form-group'>
                                <label class='form-label'><small>Lokasi</small></label>
                                <input type='text' class='form-control' id='id_l' name='id_l' value=''
                                    readonly>
                            </div>
                        </div>
                        <div class='col-sm-3'>
                            <div class='form-group'>
                                <label class='form-label'><small>No. Karton</small></label>
                                <input type='text' class='form-control' id='id_k' name='id_k' value=''
                                    readonly>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 table-responsive">
                        <table id="datatable_modal" class="table table-bordered table-hover table-sm w-100">
                            <thead>
                                <tr>
                                    <th>Brand</th>
                                    <th>Style</th>
                                    <th>Grade</th>
                                    <th>WS</th>
                                    <th>Color</th>
                                    <th>Style</th>
                                    <th>Size</th>
                                    <th>Saldo</th>

                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-sb">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center ">
                <h5 class="card-title fw-bold mb-0"><i class="fas fa-search"></i> Filter Karton Barang Jadi Stok</h5>
                <a href="{{ route('bpb-fg-stock') }}" class="btn btn-sm btn-primary">
                    <i class="fa fa-reply"></i> Kembali
                </a>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h5 class="card-title"><i class="fas fa-exchange" style="color:blue;"></i> Posisi Asal</h5>
                </div>
                <div class="card-body">

                    <div class="d-flex justify-content-between">
                        <div class="p-2 bd-highlight">
                            <a class="btn btn-outline-warning" onclick="undo()">
                                <i class="fas fa-sync-alt
                            fa-spin"></i>
                                Undo
                            </a>
                        </div>
                        <div class="p-2 bd-highlight">
                            <a class="btn btn-outline-success" onclick="simpan()">
                                <i class="fas fa-check"></i>
                                Simpan
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h5 class="card-title"><i class="fas fa-exchange" style="color:red;"></i> Posisi Tujuan</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="datatable_tmp" class="table table-bordered table-sm w-100">
                            <thead>
                                <tr>
                                    <th>No. Karton</th>
                                    <th>Brand</th>
                                    <th>Style</th>
                                    <th>Grade</th>
                                    <th>WS</th>
                                    <th>Color</th>
                                    <th>Size</th>
                                    <th>Qty</th>
                                    <th>Act</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                    <div class="d-flex justify-content-between">
                        <div class="p-2 bd-highlight">
                            <a class="btn btn-outline-warning" onclick="undo()">
                                <i class="fas fa-sync-alt
                            fa-spin"></i>
                                Undo
                            </a>
                        </div>
                        <div class="p-2 bd-highlight">
                            <a class="btn btn-outline-success" onclick="simpan()">
                                <i class="fas fa-check"></i>
                                Simpan
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form id="form_d" name='form_d' method='post'>
        <div class="card card-primary card-outline">
            <div class="card-header">
                <div class="align-items-center">
                    <h6 class="card-title mb-0"><i class="fas fa-cart-plus"></i> Input Penerimaan Barang Jadi Stok
                    </h6>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <label><small><b>Produk</b></small></label>
                        <select class='form-control  select2bs4 style='width: 100%;' name='cboproduct'
                            id='cboproduct'></select>
                    </div>
                    <div class="col-md-2">
                        <label><small><b>Qty</b></small></label>
                        <div class="input-group  mb-3">
                            <input type="number" class="form-control " name="txtqty" id="txtqty">
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="inputGroup-sizing-sm">PCS</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label><small><b>No. Karton</b></small></label>
                        <input type="number" class="form-control" id="txtno_carton" name="txtno_carton"
                            value="">
                    </div>
                    <div class="col-md-1">
                        <label><small><b>Grade</b></small></label>
                        <select class="form-control select2bs4 " id="cbograde" name="cbograde" style="width: 100%;">
                            @foreach ($data_grade as $datagrade)
                                <option value="{{ $datagrade->isi }}">
                                    {{ $datagrade->tampil }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-1">
                        <label><small><b>&nbsp; &nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b></small></label>
                        <input class="btn btn-outline-primary" type="button" value="Tambah" onclick="tambah_data();">
                        {{-- <button class="btn btn-primary"><i class="fas fa-plus" onclick="tambah_data();"></i></button> --}}
                        {{-- <a class="btn btn-outline-primary" onclick="tambah_data();">
                            <i class="fas fa-plus"></i>
                        </a> --}}
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-8">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h5 class="card-title"><i class="fas fa-box"></i> Temporary Penerimaan</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="datatable_tmp" class="table table-bordered table-sm w-100">
                                <thead>
                                    <tr>
                                        <th>No. Karton</th>
                                        <th>Brand</th>
                                        <th>Style</th>
                                        <th>Grade</th>
                                        <th>WS</th>
                                        <th>Color</th>
                                        <th>Size</th>
                                        <th>Qty</th>
                                        <th>Act</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                        <div class="d-flex justify-content-between">
                            <div class="p-2 bd-highlight">
                                <a class="btn btn-outline-warning" onclick="undo()">
                                    <i class="fas fa-sync-alt
                                    fa-spin"></i>
                                    Undo
                                </a>
                            </div>
                            <div class="p-2 bd-highlight">
                                <a class="btn btn-outline-success" onclick="simpan()">
                                    <i class="fas fa-check"></i>
                                    Simpan
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-info card-outline">
                    <div class="card-header">
                        <h5 class="card-title"><i class="fas fa-box-open"></i> List Karton</h5>
                    </div>
                    <div class="table-responsive">
                        <table id="datatable_karton" class="table table-bordered table-hover ">
                            <thead>
                                <tr>
                                    <th>Lokasi</th>
                                    <th>No. Carton</th>
                                    <th>Total Qty</th>
                                    <th>Act</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </form>
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

        $(document).ready(function() {
            $("#cbolok").val('').trigger('change');
            $("#cbobuyer").val('').trigger('change');
            dataTableReload();
            cleardet();
        })

        function cleardet() {
            document.getElementById('txtno_carton').value = "";
            document.getElementById('txtqty').value = "";
            $("#cboproduct").val('').trigger('change');

        }

        function getno_ws() {
            let cbobuyer = document.form_h.cbobuyer.value;
            let html = $.ajax({
                type: "GET",
                url: '{{ route('getno_ws') }}',
                data: {
                    cbobuyer: cbobuyer
                },
                async: false
            }).responseText;
            // console.log(html != "");
            if (html != "") {
                $("#cbows").html(html);
                // $("#cbomarker").prop("disabled", false);
                // $("#txtqtyply").prop("readonly", false);
            }
        };

        function getcolor() {
            let cbows = document.form_h.cbows.value;
            let html = $.ajax({
                type: "GET",
                url: '{{ route('getcolor') }}',
                data: {
                    cbows: cbows
                },
                async: false
            }).responseText;
            if (html != "") {
                $("#cbocolor").html(html);
            }
        };

        function getsize() {
            let cbows = document.form_h.cbows.value;
            let cbocolor = document.form_h.cbocolor.value;
            let html = $.ajax({
                type: "GET",
                url: '{{ route('getsize') }}',
                data: {
                    cbows: cbows,
                    cbocolor: cbocolor
                },
                async: false
            }).responseText;
            if (html != "") {
                $("#cbosize").html(html);
            }
        };

        function getproduct() {
            let cbobuyer = document.form_h.cbobuyer.value;
            let cbows = document.form_h.cbows.value;
            let cbocolor = document.form_h.cbocolor.value;
            let cbosize = document.form_h.cbosize.value;
            let html = $.ajax({
                type: "GET",
                url: '{{ route('getproduct') }}',
                data: {
                    cbobuyer: cbobuyer,
                    cbows: cbows,
                    cbocolor: cbocolor,
                    cbosize: cbosize
                },
                async: false
            }).responseText;
            if (html != "") {
                $("#cboproduct").html(html);
            }
        };

        function tambah_data() {
            let cboproduct = document.form_d.cboproduct.value;
            let qty = document.form_d.txtqty.value;
            let no_carton = document.form_d.txtno_carton.value;
            let grade = document.form_d.cbograde.value;
            $.ajax({
                type: "post",
                url: '{{ route('store_tmp') }}',
                data: {
                    cboproduct: cboproduct,
                    qty: qty,
                    no_carton: no_carton,
                    grade: grade
                },
                success: function(response) {
                    if (response.icon == 'salah') {
                        iziToast.warning({
                            message: response.msg,
                            position: 'topCenter'
                        });
                    } else {
                        iziToast.success({
                            message: response.msg,
                            position: 'topCenter'
                        });
                    }
                    dataTableReload();
                    cleardet();
                },
                // error: function(request, status, error) {
                //     alert(request.responseText);
                // },
            });
        };

        function dataTableReload() {
            let datatable = $("#datatable_tmp").DataTable({
                ordering: false,
                processing: true,
                serverSide: true,
                paging: false,
                destroy: true,
                ajax: {
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: '{{ route('show_tmp') }}',
                    dataType: 'json',
                    dataSrc: 'data',
                    data: function(d) {
                        d.id = $('#id').val();
                    },
                },
                columns: [{
                        data: 'no_carton',
                    },
                    {
                        data: 'brand',
                    },
                    {
                        data: 'styleno',
                    },
                    {
                        data: 'grade',
                    },
                    {
                        data: 'ws',
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
                        data: 'id',
                    },
                ],
                columnDefs: [{
                    targets: [8],
                    render: (data, type, row, meta) => {
                        return `
                            <div class='d-flex gap-1 justify-content-center'>
                            <a class='btn btn-warning btn-sm' onclick='notif()'><i class='fas fa-edit'></i></a>
                            <a class='btn btn-danger btn-sm' onclick='notif()'><i class='fas fa-trash'></i></a>
                                </div>`;
                    }
                }, ]
            });
        }

        // <a class='btn btn-warning btn-sm' href='{{ route('create-dc-in') }}/` +
    //                     row.id_so_det +
    //                     `' data-bs-toggle='tooltip'><i class='fas fa-edit'></i></a>
        // <a class='btn btn-danger btn-sm' href='{{ route('create-dc-in') }}/` +
    //                     row.id_so_det +
    //                     `' data-bs-toggle='tooltip'><i class='fas fa-trash'></i></a>


        function simpan() {
            let tgl_terima = document.form_h.tgl_terima.value;
            let cbolok = document.form_h.cbolok.value;

            if (cbolok == '') {
                iziToast.warning({
                    message: 'Lokasi masih kosong, Silahkan pilih lokasi',
                    position: 'topCenter'
                });
            } else {
                $.ajax({
                    type: "post",
                    url: '{{ route('store-bpb-fg-stock') }}',
                    data: {
                        tgl_terima: tgl_terima,
                        cbolok: cbolok
                    },
                    success: function(response) {
                        if (response.icon == 'salah') {
                            iziToast.warning({
                                message: response.msg,
                                position: 'topCenter'
                            });
                        } else {
                            Swal.fire({
                                text: response.msg,
                                icon: "success"
                            });
                        }
                        dataTableReload();
                        $("#cbolok").val('').trigger('change');
                        $("#cbobuyer").val('').trigger('change');
                        dataTableReload();
                        cleardet();
                    },
                    error: function(request, status, error) {
                        iziToast.warning({
                            message: 'Data Temporary Kosong cek lagi',
                            position: 'topCenter'
                        });
                    },
                });
            }
        };

        function undo() {
            let user = document.form_h.user.value;
            $.ajax({
                type: "post",
                url: '{{ route('undo') }}',
                data: {
                    user: user
                },
                success: function(response) {
                    if (response.icon == 'salah') {
                        iziToast.warning({
                            message: response.msg,
                            position: 'topCenter'
                        });
                    } else {
                        iziToast.success({
                            message: response.msg,
                            position: 'topCenter'
                        });
                    }
                    dataTableReload();
                },
                // error: function(request, status, error) {
                //     alert(request.responseText);
                // },
            });
        };

        function showlok() {
            let datatable = $("#datatable_karton").DataTable({
                ordering: false,
                processing: true,
                serverSide: true,
                paging: false,
                destroy: true,
                info: false,
                searching: true,
                "dom": 'ftip',
                ajax: {
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: '{{ route('show_lok') }}',
                    dataType: 'json',
                    dataSrc: 'data',
                    data: function(d) {
                        d.cbolok = $('#cbolok').val();
                    },
                },
                columns: [{
                        data: 'lokasi',
                    },
                    {
                        data: 'no_carton',
                    },
                    {
                        data: 'qty_akhir',
                    },
                    {
                        data: 'lokasi',
                    },
                ],
                columnDefs: [{
                    targets: [3],
                    render: (data, type, row, meta) => {
                        return `
                        <div class='d-flex gap-1 justify-content-center'>
                        <a class='btn btn-info btn-sm' data-bs-toggle="modal" data-bs-target="#exampleModal"
                        onclick="getdetail('` + row.lokasi + `','` + row.no_carton + `');"><i class='fas fa-search'></i></a>
                            </div>`;
                    }
                }, ]
            });
        }

        function getdetail(id_l, id_k) {
            document.getElementById('id_l').value = id_l;
            document.getElementById('id_k').value = id_k;
            let datatable = $("#datatable_modal").DataTable({
                ordering: false,
                processing: true,
                serverSide: true,
                paging: false,
                destroy: true,
                info: false,
                searching: true,
                "dom": 'ftip',
                ajax: {
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: '{{ route('getdet_carton') }}',
                    dataType: 'json',
                    dataSrc: 'data',
                    data: function(d) {
                        d.lokasi = id_l,
                            d.karton = id_k;
                    },
                },
                columns: [{
                        data: 'brand',
                    },
                    {
                        data: 'styleno',
                    },
                    {
                        data: 'grade',
                    },
                    {
                        data: 'ws',
                    },
                    {
                        data: 'color',
                    },
                    {
                        data: 'styleno',
                    },
                    {
                        data: 'size',
                    },
                    {
                        data: 'saldo',
                    },
                ],
            });
        }
    </script>
@endsection
