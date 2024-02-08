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
        <form action="{{ route('store-lokasi-fg-stock') }}" method="post" onsubmit="submitForm(this, event)" name='form'
            id='form'>
            @method('POST')
            <div class="modal-dialog modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header bg-sb text-light">
                        <h3 class="modal-title fs-5">Tambah Lokasi FG Stock</h3>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="form-label">Kode Lokasi :</label>
                            <input type='text' class='form-control form-control-sm' id="txtkode_lok" name="txtkode_lok"
                                value="" readonly>
                        </div>
                        <div class="form-group">
                            <label for="recipient-name" class="col-form-label">Lokasi :</label>
                            <input type='text' class='form-control form-control-sm' id="txtlok" name="txtlok"
                                style="text-transform: uppercase" oninput="setinisial()" value = '' autocomplete="off">
                        </div>
                        <div class="form-group">
                            <label for="recipient-name" class="col-form-label">Tingkat :</label>
                            <input type='number' class='form-control form-control-sm' id='txttingkat' name='txttingkat'
                                oninput="setinisial()" value = '' autocomplete="off">
                        </div>
                        <div class="form-group">
                            <label for="recipient-name" class="col-form-label">Baris :</label>
                            <input type='number' class='form-control form-control-sm' id='txtbaris' name='txtbaris'
                                oninput="setinisial()" value = '' autocomplete="off">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal"><i
                                class="fas fa-times-circle"></i> Tutup</button>
                        <button type="submit" class="btn btn-outline-success"><i class="fas fa-check"></i> Simpan </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="card card-sb">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center ">
                <h5 class="card-title fw-bold mb-0"><i class="fas fa-search"></i> Filter Penerimaan Barang Jadi Stok</h5>
                <a href="{{ route('bpb-fg-stock') }}" class="btn btn-sm btn-primary">
                    <i class="fa fa-reply"></i> Kembali
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <label><small><b>Tanggal Penerimaan</b></small></label>
                    <input type="date" class="form-control form-control-sm" id="tgl_cutting" name="tgl_cutting"
                        value="{{ date('Y-m-d') }}">
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label><small><b>Lokasi</b></small></label>
                        <select class="form-control select2bs4 form-control-sm" id="cbolok" name="cbolok"
                            style="width: 100%;">
                            <option selected="selected" value="" disabled="true">Pilih Lokasi</option>
                            @foreach ($data_lok as $datalok)
                                <option value="{{ $datalok->isi }}">
                                    {{ $datalok->tampil }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-6 col-md-3">
                    <div class="mb-1">
                        <label><small><b>Buyer</b></small></label>
                        <select class="form-control select2bs4 form-control-sm" id="cbobuyer" name="cbobuyer"
                            style="width: 100%;" onchange='getno_ws();'>
                            <option selected="selected" value="" disabled="true">Pilih Buyer</option>
                            @foreach ($data_buyer as $databuyer)
                                <option value="{{ $databuyer->isi }}">
                                    {{ $databuyer->tampil }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="mb-1">
                        <div class="form-group">
                            <label><small><b>No. WS</b></small></label>
                            <select class="form-control select2bs4" id="cbows" name="cbows" style="width: 100%;">
                                <option selected="selected" value="">Pilih No. WS</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="mb-1">
                        <div class="form-group">
                            <label><small><b>Color</b></small></label>
                            <select class="form-control select2bs4 form-control-sm" id="ws_id" name="ws_id"
                                style="width: 100%;">

                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="mb-1">
                        <div class="form-group">
                            <label><small><b>Size</b></small></label>
                            <select class="form-control select2bs4 form-control-sm" id="ws_id" name="ws_id"
                                style="width: 100%;">

                            </select>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div class="card card-primary">
        <div class="card-header">
            <div class="align-items-center">
                <h6 class="card-title fw-bold mb-0"><i class="fas fa-cart-plus"></i> Input Penerimaan Barang Jadi Stok
                </h6>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <label><small><b>Produk</b></small></label>
                    <input type="text" class="form-control form-control-sm" id="id_so_det" name="id_so_det"
                        value="">
                </div>
                <div class="col-md-2">
                    <label><small><b>Qty</b></small></label>
                    <div class="input-group input-group-sm mb-3">
                        <input type="number" class="form-control form-control-sm" name="txtqty" id="txtqty">
                        <div class="input-group-prepend">
                            <span class="input-group-text" id="inputGroup-sizing-sm">PCS</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <label><small><b>No. Carton</b></small></label>
                    <input type="number" class="form-control form-control-sm" id="txtno_carton" name="txtno_carton"
                        value="">
                </div>
                <div class="col-md-1">
                    <label><small><b>Grade</b></small></label>
                    <select class="form-control select2bs4 form-control-sm" id="cbograde" name="cbograde"
                        style="width: 100%;">
                        @foreach ($data_grade as $datagrade)
                            <option value="{{ $datagrade->isi }}">
                                {{ $datagrade->tampil }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1">
                    <label><small><b>&nbsp; &nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b></small></label>
                    <input class="btn btn-primary btn-sm" type="button" value="Tambah">
                </div>
            </div>
            <div class ="row g-3">
                <div class="col-md-8">
                    <div class="card card-warning">
                        <div class="card-header">
                            <h5 class="card-title"><i class="fas fa-box"></i> Temporary Penerimaan</h5>
                        </div>
                        <div class="table-responsive">
                            <table id="datatable_tmp" class="table table-bordered table-sm w-100">
                                <thead>
                                    <tr>
                                        <th>Produk</th>
                                        <th>WS</th>
                                        <th>Color</th>
                                        <th>Size</th>
                                        <th>Qty</th>
                                        <th>Act</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-info">
                        <div class="card-header">
                            <h5 class="card-title"><i class="fas fa-box-open"></i> List Karton</h5>
                        </div>
                        <div class="table-responsive">
                            <table id="datatable_list_karton" class="table table-bordered table-sm w-100">
                                <thead>
                                    <tr>
                                        <th>No. Carton</th>
                                        <th>Total Qty</th>
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
            $("#cbolok").val('').trigger('change');
            document.getElementById('txtno_carton').value = "";
        })

        // function getno_ws() {

        //     let cbows = document.form.cbows.value;
        //     let html = $.ajax({
        //         type: "POST",
        //         url: '{{ route('getno_marker') }}',
        //         data: {
        //             cbows: cbows
        //         },
        //         async: false
        //     }).responseText;

        //     console.log(html != "");

        //     if (html != "") {
        //         $("#cbomarker").html(html);

        //         $("#cbomarker").prop("disabled", false);
        //         $("#txtqtyply").prop("readonly", false);
        //     }
        // };
    </script>
@endsection
