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
    <div class="modal fade" id="exampleModalTambah" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <form action="{{ route('tambah_packing_list') }}" enctype="multipart/form-data" method="post"
            onsubmit="submitForm(this, event)" name='form_upload_tambah' id='form_upload_tambah'>
            @csrf
            @method('POST')
            <div class="modal-dialog modal-dialog-scrollable modal-xl">
                <div class="modal-content">
                    <div class="modal-header bg-sb text-light">
                        <h3 class="modal-title fs-5"><i class="fas fa-list"></i> Tambah Packing List (Tambahan) </h3>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class='row'>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><small><b>Tipe Upload :</b></small></label>
                                    <select class="form-control select2bs4" id="cbotipe_tmbh" name="cbotipe_tmbh"
                                        style="width: 100%;" required>
                                        <option selected="selected" value="" disabled="true">Pilih Tipe Upload
                                        </option>
                                        @foreach ($data_list as $datalist)
                                            <option value="{{ $datalist->isi }}">
                                                {{ $datalist->tampil }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <div class="input-group">
                                        <a onclick="export_data_po_tambah()"
                                            class="btn btn-outline-success position-relative btn-sm">
                                            <i class="fas fa-file-download fa-sm"></i>
                                            Export Data PO
                                        </a>
                                    </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class='row'>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label"><small><b>Buyer</b></small></label>
                                    <input type="text" class="form-control form-control-sm" id = "modal_t_buyer"
                                        name = "modal_t_buyer" readonly>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label"><small><b>PO</b></small></label>
                                    <input type="text" class="form-control form-control-sm" id = "modal_t_po"
                                        name = "modal_t_po" readonly>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label"><small><b>Dest</b></small></label>
                                    <input type="text" class="form-control form-control-sm" id = "modal_t_dest"
                                        name = "modal_t_dest" readonly>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label"><small><b>Style</b></small></label>
                                    <input type="text" class="form-control form-control-sm" id = "modal_t_style"
                                        name = "modal_t_style" readonly>
                                </div>
                            </div>
                        </div>
                        <div class='row'>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label"><small><b>Upload File</b></small></label>
                                    <input type="file" class="form-control form-control-sm" name="file_tmbh"
                                        id="file_tmbh">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <div class="input-group">
                                        <button type="submit" class="btn btn-outline-info btn-sm"><i
                                                class="fas fa-check"></i> Upload
                                        </button>
                                    </div>
                                    </a>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label><small><b>Tidak Terdaftar :</b></small></label>
                                    <input type='text' id="txtnon_upload_tmbh" name="txtnon_upload_tmbh"
                                        class='form-control form-control-sm' value="" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table id="datatable_upload_tambah"
                                class="table table-bordered table-hover display nowrap w-100" >
                                <thead class="table-primary">
                                    <tr style='text-align:center; vertical-align:middle'>
                                        <th>Tgl. Shipment</th>
                                        <th>PO #</th>
                                        <th>No. Carton</th>
                                        <th>Tipe Pack</th>
                                        <th>Color</th>
                                        <th>Size</th>
                                        <th>Qty</th>
                                    </tr>
                                </thead>
                                <tfoot>
                                    <tr>
                                        <th colspan="5"></th>
                                        <th> <input type = 'text' class="form-control form-control-sm"
                                                style="width:75px" readonly id = 'total_qty_carton'> </th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
        </form>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline-danger btn-sm" data-bs-dismiss="modal"><i
                    class="fas fa-times-circle"></i> Tutup</button>
            <a class="btn btn-outline-success btn-sm" onclick="tambah()">
                <i class="fas fa-check"></i>
                Tambah
            </a>
        </div>
    </div>
    </div>
    </div>





    <div class="modal fade" id="exampleModalCheck" tabindex="-1" role="dialog"
        aria-labelledby="exampleModalCheckLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h3 class="modal-title fs-5">List Detail Packing List</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class='row'>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="form-label"><small><b>Buyer</b></small></label>
                                <input type="text" class="form-control form-control-sm" id = "modal_buyer"
                                    name = "modal_buyer" readonly>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="form-label"><small><b>PO</b></small></label>
                                <input type="text" class="form-control form-control-sm" id = "modal_po"
                                    name = "modal_po" readonly>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="form-label"><small><b>Dest</b></small></label>
                                <input type="text" class="form-control form-control-sm" id = "modal_dest"
                                    name = "modal_dest" readonly>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="form-label"><small><b>Style</b></small></label>
                                <input type="text" class="form-control form-control-sm" id = "modal_style"
                                    name = "modal_style" readonly>
                            </div>
                        </div>
                    </div>
                    <div class='row'>
                        <div class="col-md-12 table-responsive">
                            <table id="datatable_detail_packing_list" class="table table-bordered table-hover 100 nowrap">
                                <thead>
                                    <tr>
                                        <th>PO</th>
                                        <th>Dest</th>
                                        <th>Style</th>
                                        <th>Barcode</th>
                                        <th>WS</th>
                                        <th>Color</th>
                                        <th>Size</th>
                                        <th>No.Carton</th>
                                        <th>Tipe Pack</th>
                                        <th>Qty</th>
                                        <th>Qty Scan</th>
                                        <th>Qty FG IN</th>
                                        <th>Qty FG Out</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tfoot>
                                    <tr>
                                        <th colspan="9"></th>
                                        <th> <input type = 'text' class="form-control form-control-sm"
                                                style="width:75px" readonly id = 'total_qty_chk'> </th>
                                        <th> <input type = 'text' class="form-control form-control-sm"
                                                style="width:75px" readonly id = 'total_qty_scan'> </th>
                                        <th> <input type = 'text' class="form-control form-control-sm"
                                                style="width:75px" readonly id = 'total_qty_fg_in'> </th>
                                        <th> <input type = 'text' class="form-control form-control-sm"
                                                style="width:75px" readonly id = 'total_qty_fg_out'> </th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                {{-- <div class="modal-footer">
            <button type="submit" class="btn btn-outline-success btn-sm"><i class="fas fa-check"></i> Simpan
            </button>
        </div> --}}
            </div>
        </div>
    </div>

    <div class="modal fade" id="exampleModalHapus" tabindex="-1" role="dialog"
        aria-labelledby="exampleModalHapusLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h3 class="modal-title fs-5"><i class="far fa-edit"></i> Hapus Karton</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="form_h_karton" name='form_h_karton' method='post'
                        action="{{ route('hapus_packing_list') }}" onsubmit="submitForm(this, event)">
                        <div class='row'>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label"><small><b>Buyer</b></small></label>
                                    <input type="text" class="form-control form-control-sm" id = "modal_h_buyer"
                                        name = "modal_h_buyer" readonly>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label"><small><b>PO</b></small></label>
                                    <input type="text" class="form-control form-control-sm" id = "modal_h_po"
                                        name = "modal_h_po" readonly>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label"><small><b>Dest</b></small></label>
                                    <input type="text" class="form-control form-control-sm" id = "modal_h_dest"
                                        name = "modal_h_dest" readonly>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label"><small><b>Style</b></small></label>
                                    <input type="text" class="form-control form-control-sm" id = "modal_h_style"
                                        name = "modal_h_style" readonly>
                                </div>
                            </div>
                        </div>
                        <div class='row'>
                            <div class="col-md-12 table-responsive">
                                <table id="datatable_detail_packing_list_hapus" class="table table-bordered text-nowrap"
                                    style="width:100%;">
                                    <thead class="table-primary">
                                        <tr style='text-align:center; vertical-align:middle'>
                                            <th>
                                                <input class="form-check checkbox-xl" type="checkbox"
                                                    onclick="toggle(this);">
                                            </th>
                                            <th>PO</th>
                                            <th>Dest</th>
                                            <th>Style</th>
                                            <th>Barcode</th>
                                            <th>WS</th>
                                            <th>Color</th>
                                            <th>Size</th>
                                            <th>No.Carton</th>
                                            <th>Tipe Pack</th>
                                            <th>Qty</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                            <div class="d-flex justify-content-between">
                                <div class="p-2 bd-highlight">
                                </div>
                                <div class="p-2 bd-highlight">
                                    <button type="submit" class="btn btn-outline-danger"><i class="fas fa-trash"></i>
                                        Hapus
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <form action="{{ route('upload-packing-list') }}" enctype="multipart/form-data" method="post"
            onsubmit="submitForm(this, event)" name='form_upload' id='form_upload'>
            @csrf
            @method('POST')
            <div class="modal-dialog modal-dialog-scrollable modal-xl">
                <div class="modal-content">
                    <div class="modal-header bg-sb text-light">
                        <h3 class="modal-title fs-5"><i class="fas fa-list"></i> Tambah Packing List</h3>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class='row'>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><small><b>Tipe Upload :</b></small></label>
                                    <select class="form-control select2bs4" id="cbotipe" name="cbotipe"
                                        style="width: 100%;" required>
                                        <option selected="selected" value="" disabled="true">Pilih Tipe Upload
                                        </option>
                                        @foreach ($data_list as $datalist)
                                            <option value="{{ $datalist->isi }}">
                                                {{ $datalist->tampil }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class='row'>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><small><b>PO :</b></small></label>
                                    <select class="form-control select2bs4" id="cbopo" name="cbopo"
                                        style="width: 100%;"
                                        onchange="delete_tmp_upload();getdatapo();dataTableUploadReload()" required>
                                        <option selected="selected" value="" disabled="true">Pilih PO</option>
                                        @foreach ($data_po as $datapo)
                                            <option value="{{ $datapo->isi }}">
                                                {{ $datapo->tampil }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <div class="input-group">
                                        <a onclick="export_data_po()"
                                            class="btn btn-outline-success position-relative btn-sm">
                                            <i class="fas fa-file-download fa-sm"></i>
                                            Export Data PO
                                        </a>
                                    </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class='row'>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label><small><b>Buyer :</b></small></label>
                                    <input type='text' id="txtbuyer" name="txtbuyer"
                                        class='form-control form-control-sm' value="" readonly>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label><small><b>PO :</b></small></label>
                                    <input type='text' id="txtpo" name="txtpo"
                                        class='form-control form-control-sm' value="" readonly>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label><small><b>Style :</b></small></label>
                                    <input type='text' id="txtstyle" name="txtstyle"
                                        class='form-control form-control-sm' value="" readonly>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label><small><b>Dest :</b></small></label>
                                    <input type='text' id="txtdest" name="txtdest"
                                        class='form-control form-control-sm' value="" readonly>
                                </div>
                            </div>
                        </div>
                        <div class='row'>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label"><small><b>Upload File</b></small></label>
                                    <input type="file" class="form-control form-control-sm" name="file"
                                        id="file">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <div class="input-group">
                                        <button type="submit" class="btn btn-outline-info btn-sm"><i
                                                class="fas fa-check"></i> Upload
                                        </button>
                                    </div>
                                    </a>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label><small><b>Tidak Terdaftar :</b></small></label>
                                    <input type='text' id="txtnon_upload" name="txtnon_upload"
                                        class='form-control form-control-sm' value="" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table id="datatable_upload" class="table table-bordered w-100 table-hover display nowrap">
                                <thead class="table-primary">
                                    <tr style='text-align:center; vertical-align:middle'>
                                        <th>Tgl. Shipment</th>
                                        <th>PO #</th>
                                        <th>No. Carton</th>
                                        <th>Tipe Pack </th>
                                        <th>Color</th>
                                        <th>Size</th>
                                        <th>Qty</th>
                                    </tr>
                                </thead>
                                <tfoot>
                                    <tr>
                                        <th colspan="5"></th>
                                        <th> <input type = 'text' class="form-control form-control-sm"
                                                style="width:75px" readonly id = 'total_qty_carton'> </th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
        </form>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline-danger btn-sm" data-bs-dismiss="modal"><i
                    class="fas fa-times-circle"></i> Tutup</button>
            <a class="btn btn-outline-success btn-sm" onclick="simpan()">
                <i class="fas fa-check"></i>
                Simpan
            </a>
        </div>
    </div>
    </div>
    </div>
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-list"></i> Packing List</h5>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <a class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#exampleModal"
                    onclick="reset()">
                    <i class="fas fa-plus"></i>
                    Baru
                </a>
            </div>
            <div class="d-flex align-items-end gap-3 mb-3">
                <div class="mb-3">
                    <label class="form-label"><small><b>Tgl Shipment Awal</b></small></label>
                    <input type="date" class="form-control form-control-sm " id="tgl-awal" name="tgl_awal"
                        oninput="dataTableReload()" value="{{ $tgl_awal_fix }}">
                </div>
                <div class="mb-3">
                    <label class="form-label"><small><b>Tgl Shipment Akhir</b></small></label>
                    <input type="date" class="form-control form-control-sm" id="tgl-akhir" name="tgl_akhir"
                        oninput="dataTableReload()" value="{{ $tgl_akhir_fix }}">
                </div>
            </div>
            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-striped w-100 text-nowrap">
                    <thead class="table-primary">
                        <tr style='text-align:center; vertical-align:middle'>
                            <th>Tgl. Shipment</th>
                            <th>Buyer</th>
                            <th>PO</th>
                            <th>Dest</th>
                            <th>Style</th>
                            <th>Tot. Carton</th>
                            <th>Tot. Qty</th>
                            <th>Act</th>
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

    <style>
        .checkbox-xl .form-check-input {
            /* top: 1.2rem; */
            scale: 1.5;
            /* margin-right: 0.8rem; */
        }
    </style>
    <script>
        function toggle(source) {
            var checkboxes = document.querySelectorAll('input[type="checkbox"]');
            for (var i = 0; i < checkboxes.length; i++) {
                if (checkboxes[i] != source)
                    checkboxes[i].checked = source.checked;
            }
        }
    </script>
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

        $('#exampleModal').on('show.bs.modal', function(e) {
            $('.select2bs4').select2({
                theme: 'bootstrap4',
                dropdownParent: $("#exampleModal")
            })
            reloadPoData();
        })

        $('#exampleModalHapus').on('show.bs.modal', function(e) {

            $('.select2bs4').select2({
                theme: 'bootstrap4',
                dropdownParent: $("#exampleModalHapus"),
                containerCssClass: 'form-control-sm rounded'
            })

        })

        $('#exampleModalTambah').on('show.bs.modal', function(e) {

            $('.select2bs4').select2({
                theme: 'bootstrap4',
                dropdownParent: $("#exampleModalTambah"),
                containerCssClass: 'form-control-sm rounded'
            })

        })
    </script>
    <script>
        function notif() {
            alert("Maaf, Fitur belum tersedia!");
        }

        function reset() {
            $("#form_upload").trigger("reset");
            $("#cbotipe").val('HORIZONTAL').trigger('change');
            $("#cbopo").val('').trigger('change');
            dataTableUploadReload();
            document.getElementById('file').value = "";
        }
    </script>
    <script>
        $(document).ready(() => {
            reset();
        });

        function getdatapo() {
            document.getElementById('file').value = "";
            let po = document.form_upload.cbopo.value;
            if (!cbopo) {
                document.getElementById('txtbuyer').value = '';
                document.getElementById('txtpo').value = '';
                document.getElementById('txtstyle').value = '';
                document.getElementById('txtdest').value = '';
            } else {
                let cbopo = po.split('_')[0];
                let dest = po.split('_')[1];
                console.log(po);
                $.ajax({
                    url: '{{ route('show_det_po') }}',
                    method: 'get',
                    data: {
                        cbopo: cbopo,
                        dest: dest
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response !== null) {
                            document.getElementById('txtbuyer').value = response.buyer;
                            document.getElementById('txtpo').value = response.po;
                            document.getElementById('txtstyle').value = response.styleno;
                            document.getElementById('txtdest').value = response.dest;
                        } else {
                            document.getElementById('txtbuyer').value = '';
                            document.getElementById('txtpo').value = '';
                            document.getElementById('txtstyle').value = '';
                            document.getElementById('txtdest').value = '';
                        }

                    },
                    error: function(request, status, error) {
                        alert(request.responseText);
                    },
                });
            }


        };

        function export_data_po() {
            let po = $('#txtpo').val();
            let dest = $('#txtdest').val();
            let tipe = $('#cbotipe').val();
            if (!tipe) {
                iziToast.warning({
                    message: 'Tipe masih kosong, Silahkan pilih tipe',
                    position: 'topCenter'
                });
            }

            if (!po) {
                iziToast.warning({
                    message: 'PO masih kosong, Silahkan pilih po',
                    position: 'topCenter'
                });
            } else {
                Swal.fire({
                    title: 'Please Wait...',
                    html: 'Exporting Data...',
                    didOpen: () => {
                        Swal.showLoading()
                    },
                    allowOutsideClick: false,
                });

                if (tipe == 'HORIZONTAL') {
                    $.ajax({
                        type: "get",
                        url: '{{ route('export_data_template_po_packing_list_horizontal') }}',
                        data: {
                            po: po,
                            dest: dest
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
                                link.download = "PO " + po + "_" + dest + "_H.xlsx";
                                link.click();

                            }
                        },
                    });
                } else {
                    $.ajax({
                        type: "get",
                        url: '{{ route('export_data_template_po_packing_list_vertical') }}',
                        data: {
                            po: po,
                            dest: dest
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
                                link.download = "PO " + po + "_" + dest + "_V.xlsx";
                                link.click();

                            }
                        },
                    });
                }


            }
        }

        function delete_tmp_upload() {
            let po = $('#cbopo').val();
            let html = $.ajax({
                type: "POST",
                url: '{{ route('delete_upload_packing_list') }}',
                data: {
                    po: po
                },
                async: false
            }).responseText;
        }

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
            ordering: false,
            processing: true,
            serverSide: true,
            paging: false,
            searching: true,
            scrollY: '300px',
            scrollX: '300px',
            scrollCollapse: true,
            ajax: {
                url: '{{ route('packing-list') }}',
                data: function(d) {
                    d.dateFrom = $('#tgl-awal').val();
                    d.dateTo = $('#tgl-akhir').val();
                },
            },
            columns: [{
                    data: 'tgl_shipment_fix'

                },
                {
                    data: 'buyer'
                },
                {
                    data: 'po'
                },
                {
                    data: 'dest'
                },
                {
                    data: 'styleno'
                },
                {
                    data: 'tot_carton'
                },
                {
                    data: 'tot_qty'
                },
                {
                    data: 'po'
                },
            ],
            columnDefs: [{
                    "className": "dt-center",
                    "targets": "_all"
                },
                {
                    targets: [7],
                    render: (data, type, row, meta) => {
                        return `
                <div class='d-flex gap-1 justify-content-center'>
                <a class='btn btn-primary btn-sm'  data-bs-toggle="modal"
                  data-bs-target="#exampleModalCheck"
                onclick="show_data('` + row.po + `', '` + row.dest + `','` + row.buyer + `','` + row.styleno + `' );dataTableDetailPackingListReload();"><i class='fas fa-search'></i>
                </a>
                <a class='btn btn-danger btn-sm'  data-bs-toggle="modal"
                  data-bs-target="#exampleModalHapus"
                onclick="show_data_h('` + row.po + `', '` + row.dest + `','` + row.buyer + `','` + row.styleno + `' );dataTableDetailPackingListHapusReload();"><i class='fas fa-trash'></i>
                </a>
                <a class='btn btn-primary btn-sm'  data-bs-toggle="modal"
                  data-bs-target="#exampleModalTambah"
                onclick="show_data_t('` + row.po + `', '` + row.dest + `','` + row.buyer + `','` + row.styleno + `' );dataTableUploadTambahReload();"><i class='fas fa-plus'></i>
                </a>
                <a class='btn btn-success btn-sm'
                onclick="export_excel('` + row.po + `', '` + row.dest + `','` + row.buyer + `','` + row.styleno + `' );"><i class='fas fa-file-excel'></i>
                </a>

                     </div>
                    `;
                    }
                },
            ]

        }, );

        function dataTablePackingListReload() {
            datatable_packing_list.ajax.reload();
        }

        $('#datatable_packing_list thead tr').clone(true).appendTo('#datatable_packing_list thead');
        $('#datatable_packing_list thead tr:eq(1) th').each(function(i) {
            var title = $(this).text();
            $(this).html('<input type="text" class="form-control form-control-sm"/>');
            $('input', this).on('keyup change', function() {
                if (datatable_packing_list.column(i).search() !== this.value) {
                    datatable_packing_list
                        .column(i)
                        .search(this.value)
                        .draw();
                }
            });
        });

        let datatable_packing_list = $("#datatable_packing_list").DataTable({
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
                    .column(5)
                    .data()
                    .reduce(function(a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                // Count rows with color red

                var redCount = 0;
                api.rows().every(function() {
                    var row = this.data();
                    if (row.id_ppic_master_so === null) {
                        redCount++;
                    }
                });

                // Update footer by showing the total with the reference of the column index
                $(api.column(0).footer()).html('Total');
                $(api.column(5).footer()).html(sumTotal);
                $('#txtnon_upload').val(redCount);
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
                url: '{{ route('show_datatable_upload_packing_list') }}',
                data: function(d) {
                    d.po = $('#cbopo').val();
                    d.tipe = $('#cbotipe').val();
                },
            },
            columns: [{
                    data: 'tgl_shipment_fix'

                },
                {
                    data: 'po'
                },
                {
                    data: 'no_carton'
                },
                {
                    data: 'tipe_pack'
                },
                {
                    data: 'color'
                },
                {
                    data: 'size'
                },
                {
                    data: 'qty'
                },
            ],
            columnDefs: [{
                    "className": "dt-center",
                    "targets": "_all"
                },
                {
                    targets: '_all',
                    className: 'text-nowrap',
                    render: (data, type, row, meta) => {
                        if (row.id_ppic_master_so === null) {
                            color = 'red';
                        } else {
                            color = '#087521';
                        }
                        return '<span style="font-weight: 600; color:' + color + '">' + data + '</span>';
                    }
                },
            ]


        }, );



        function dataTableUploadReload() {
            datatable_upload.ajax.reload();
        }

        $('#datatable_upload thead tr').clone(true).appendTo('#datatable_upload thead');
        $('#datatable_upload thead tr:eq(1) th').each(function(i) {
            var title = $(this).text();
            $(this).html('<input type="text" class="form-control form-control-sm"/>');
            $('input', this).on('keyup change', function() {
                if (datatable_upload.column(i).search() !== this.value) {
                    datatable_upload
                        .column(i)
                        .search(this.value)
                        .draw();
                }
            });
        });

        let datatable_upload = $("#datatable_upload").DataTable({
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
                    .column(6)
                    .data()
                    .reduce(function(a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                // Count rows with color red

                var redCount = 0;
                api.rows().every(function() {
                    var row = this.data();
                    if (row.id_ppic_master_so === null) {
                        redCount++;
                    }
                });

                // Update footer by showing the total with the reference of the column index
                $(api.column(0).footer()).html('Total');
                $(api.column(5).footer()).html(sumTotal);
                $('#txtnon_upload').val(redCount);
            },
            ordering: false,
            processing: true,
            serverSide: true,
            paging: true,
            searching: true,
            scrollY: '300px',
            scrollX: '300px',
            scrollCollapse: true,
            ajax: {
                url: '{{ route('show_datatable_upload_packing_list') }}',
                data: function(d) {
                    d.po = $('#txtpo').val();
                    d.dest = $('#txtdest').val();
                    d.tipe = $('#cbotipe').val();
                },
            },
            columns: [{
                    data: 'tgl_shipment_fix'

                },
                {
                    data: 'po'
                },
                {
                    data: 'no_carton'
                },
                {
                    data: 'tipe_pack'
                },
                {
                    data: 'color'
                },
                {
                    data: 'size'
                },
                {
                    data: 'qty'
                },
            ],
            columnDefs: [{
                    "className": "dt-center",
                    "targets": "_all"
                },
                {
                    targets: '_all',
                    className: 'text-nowrap',
                    render: (data, type, row, meta) => {
                        if (row.id_ppic_master_so === null) {
                            color = 'red';
                        } else {
                            color = '#087521';
                        }
                        return '<span style="font-weight: 600; color:' + color + '">' + data + '</span>';
                    }
                },
            ]


        }, );


        function simpan() {
            // let po = $('#cbopo').val();
            let txtnon_upload = $('#txtnon_upload').val();
            let tipe = $('#cbotipe').val();
            let txtpo = $('#txtpo').val();
            let txtdest = $('#txtdest').val();
            $.ajax({
                type: "post",
                url: '{{ route('store_upload_packing_list') }}',
                data: {
                    txtnon_upload: txtnon_upload,
                    tipe: tipe,
                    txtpo: txtpo,
                    txtdest: txtdest
                },
                success: function(response) {
                    if (response.icon == 'salah') {
                        iziToast.warning({
                            message: response.msg,
                            position: 'topCenter'
                        });
                        dataTableReload();
                        dataTableUploadReload();
                    } else {
                        Swal.fire({
                            text: response.msg,
                            icon: "success"
                        });
                        dataTableReload();
                        dataTableUploadReload();
                        delete_tmp_upload();
                    }

                },
                error: function(request, status, error) {
                    iziToast.warning({
                        message: 'Silahkan cek lagi',
                        position: 'topCenter'
                    });
                    dataTableUploadReload();
                    delete_tmp_upload();
                    dataTableReload();
                },
            });

        };

        function reloadPoData() {
            const cbopo = $('#cbopo');
            cbopo.empty(); // Clear existing options
            cbopo.append('<option selected="selected" value="" disabled="true">Pilih PO</option>'); // Default option
            $.ajax({
                type: 'GET',
                url: '{{ route('getPoData') }}',
                success: function(data) {
                    console.log('Received data:', data); // Debugging statement
                    $.each(data, function(index, item) {
                        cbopo.append($('<option>', {
                            value: item.isi,
                            text: item.tampil
                        }));
                    });
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching PO data:', error); // Debugging statement
                }
            });
        }

        function show_data(po_s, dest_s, buyer_s, style_s) {

            $('#modal_po').val(po_s);
            $('#modal_dest').val(dest_s);
            $('#modal_buyer').val(buyer_s);
            $('#modal_style').val(style_s);
            dataTableDetailPackingListReload();
        }

        function dataTableDetailPackingListReload() {
            datatable_detail_packing_list.ajax.reload();
        }

        $('#datatable_detail_packing_list thead tr').clone(true).appendTo('#datatable_detail_packing_list thead');
        $('#datatable_detail_packing_list thead tr:eq(1) th').each(function(i) {
            var title = $(this).text();
            $(this).html('<input type="text" class="form-control form-control-sm"/>');
            $('input', this).on('keyup change', function() {
                if (datatable_detail_packing_list.column(i).search() !== this.value) {
                    datatable_detail_packing_list
                        .column(i)
                        .search(this.value)
                        .draw();
                }
            });
        });

        let datatable_detail_packing_list = $("#datatable_detail_packing_list").DataTable({
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
                    .column(9)
                    .data()
                    .reduce(function(a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                var sumTotalS = api
                    .column(10)
                    .data()
                    .reduce(function(a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                var sumTotalT = api
                    .column(11)
                    .data()
                    .reduce(function(a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                var sumTotalU = api
                    .column(12)
                    .data()
                    .reduce(function(a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                // Update footer by showing the total with the reference of the column index
                $(api.column(0).footer()).html('Total');
                $(api.column(9).footer()).html(sumTotal);
                $(api.column(10).footer()).html(sumTotalS);
                $(api.column(11).footer()).html(sumTotalT);
                $(api.column(12).footer()).html(sumTotalU);
            },

            ordering: false,
            processing: true,
            serverSide: true,
            paging: true,
            searching: true,
            scrollY: '300px',
            scrollX: '300px',
            scrollCollapse: true,
            destroy: true,
            info: true,
            ajax: {
                url: '{{ route('show_detail_packing_list') }}',
                method: 'GET',
                data: function(d) {
                    d.po = $('#modal_po').val();
                    d.dest = $('#modal_dest').val();
                },
            },
            columns: [{
                    data: 'po'

                },
                {
                    data: 'dest'
                },
                {
                    data: 'styleno'
                },
                {
                    data: 'barcode'
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
                    data: 'no_carton'
                },
                {
                    data: 'tipe_pack'
                },
                {
                    data: 'qty'
                },
                {
                    data: 'qty_scan'
                },
                {
                    data: 'qty_fg_in'
                },
                {
                    data: 'qty_fg_out'
                },
                {
                    data: 'stat'
                },
            ],
            columnDefs: [{
                "className": "align-middle",
                "targets": "_all"
            }, ],
            rowsGroup: [
                7
            ],
            createdRow: function(row, data, dataIndex) {

                // Compare qty and qty_scan

                if (data.qty == data.qty_scan) {

                    // If they are equal, set font to bold and green for the entire row

                    $(row).css({
                        'font-weight': 'bold',
                        'color': 'green'
                    });

                } else {

                    // If they are not equal, set font color to blue for the entire row

                    $(row).css({
                        'font-weight': 'bold',
                        'color': 'blue'
                    });

                }

            }
        });

        function dataTableDetailPackingListHapusReload() {
            datatable_detail_packing_list_hapus.ajax.reload();
        }

        let datatable_detail_packing_list_hapus = $("#datatable_detail_packing_list_hapus").DataTable({

            ordering: false,
            processing: true,
            serverSide: true,
            paging: true,
            searching: true,
            scrollY: '300px',
            scrollX: '300px',
            scrollCollapse: true,
            destroy: true,
            info: true,
            ajax: {
                url: '{{ route('show_detail_packing_list_hapus') }}',
                method: 'GET',
                data: function(d) {
                    d.po = $('#modal_h_po').val();
                    d.dest = $('#modal_h_dest').val();
                },
            },
            columns: [{
                    data: 'id'

                }, {
                    data: 'po'

                }, {
                    data: 'dest'
                },
                {
                    data: 'styleno'
                },
                {
                    data: 'barcode'
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
                    data: 'no_carton'
                },
                {
                    data: 'tipe_pack'
                },
                {
                    data: 'qty'
                },

            ],
            columnDefs: [{
                    "className": "align-middle",
                    "targets": "_all"
                },
                {
                    targets: [0],
                    render: (data, type, row, meta) => {
                        if (row.qty_fg >= '1') {
                            return ``;
                        } else {
                            return `
                    <div
                        class="form-check checkbox-xl" style="text-align:center">
                        <input class="form-check-input" type="checkbox"
                        value="` + row.id + `" id="cek_data"  class = "chk" onchange="ceklis(this)"
                        name="cek_data[` + row.id + `] "/>
                    </div>
                    <div>
                            <input type="hidden" size="10" id="id"
                            name="id[` + row.id + `]" value = "` + row.id + `"/>
                    </div>
                    `;
                        }

                    }
                },
            ],
        });


        function show_data_h(po_s, dest_s, buyer_s, style_s) {

            $('#modal_h_po').val(po_s);
            $('#modal_h_dest').val(dest_s);
            $('#modal_h_buyer').val(buyer_s);
            $('#modal_h_style').val(style_s);
            dataTableDetailPackingListHapusReload();
        }

        function show_data_t(po_s, dest_s, buyer_s, style_s) {

            $('#modal_t_po').val(po_s);
            $('#modal_t_dest').val(dest_s);
            $('#modal_t_buyer').val(buyer_s);
            $('#modal_t_style').val(style_s);
            $("#cbotipe_tmbh").val('HORIZONTAL').trigger('change');
            delete_tmp_upload_tambah();
            document.getElementById('file_tmbh').value = "";
            dataTableUploadTambahReload();
        }




        function ceklis(checkeds) {
            //get id..and check if checked
            console.log($(checkeds).attr("value"), checkeds.checked)
        }



        function export_data_po_tambah() {
            let po = $('#modal_t_po').val();
            let dest = $('#modal_t_dest').val();
            let tipe = $('#cbotipe_tmbh').val();
            if (!tipe) {
                iziToast.warning({
                    message: 'Tipe masih kosong, Silahkan pilih tipe',
                    position: 'topCenter'
                });
            }

            if (!po) {
                iziToast.warning({
                    message: 'PO masih kosong, Silahkan pilih po',
                    position: 'topCenter'
                });
            } else {
                Swal.fire({
                    title: 'Please Wait...',
                    html: 'Exporting Data...',
                    didOpen: () => {
                        Swal.showLoading()
                    },
                    allowOutsideClick: false,
                });

                if (tipe == 'HORIZONTAL') {
                    $.ajax({
                        type: "get",
                        url: '{{ route('export_data_template_po_packing_list_horizontal') }}',
                        data: {
                            po: po,
                            dest: dest
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
                                link.download = "PO " + po + "_" + dest + "_H.xlsx";
                                link.click();

                            }
                        },
                    });
                } else {
                    $.ajax({
                        type: "get",
                        url: '{{ route('export_data_template_po_packing_list_vertical') }}',
                        data: {
                            po: po,
                            dest: dest
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
                                link.download = "PO " + po + "_" + dest + "_V.xlsx";
                                link.click();

                            }
                        },
                    });
                }


            }
        }

        function dataTableUploadTambahReload() {
            datatable_upload_tambah.ajax.reload();
        }


        $('#datatable_upload_tambah thead tr').clone(true).appendTo('#datatable_upload_tambah thead');
        $('#datatable_upload_tambah thead tr:eq(1) th').each(function(i) {
            var title = $(this).text();
            $(this).html('<input type="text" class="form-control form-control-sm"/>');
            $('input', this).on('keyup change', function() {
                if (datatable_upload_tambah.column(i).search() !== this.value) {
                    datatable_upload_tambah
                        .column(i)
                        .search(this.value)
                        .draw();
                }
            });
        });

        let datatable_upload_tambah = $("#datatable_upload_tambah").DataTable({
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
                    .column(6)
                    .data()
                    .reduce(function(a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                // Count rows with color red

                var redCount = 0;
                api.rows().every(function() {
                    var row = this.data();
                    if (row.id_ppic_master_so === null || row.id_cek != null) {
                        redCount++;
                    }
                });

                // Update footer by showing the total with the reference of the column index
                $(api.column(0).footer()).html('Total');
                $(api.column(5).footer()).html(sumTotal);
                $('#txtnon_upload_tmbh').val(redCount);
            },
            ordering: false,
            processing: true,
            serverSide: true,
            paging: true,
            searching: true,
            scrollY: '300px',
            scrollX: '300px',
            scrollCollapse: true,
            ajax: {
                url: '{{ route('show_datatable_upload_packing_list_tambah') }}',
                data: function(d) {
                    d.po = $('#modal_t_po').val();
                    d.dest = $('#modal_t_dest').val();
                    d.tipe = $('#cbotipe_tmbh').val();
                },
            },
            columns: [{
                    data: 'tgl_shipment_fix'

                },
                {
                    data: 'po'
                },
                {
                    data: 'no_carton'
                },
                {
                    data: 'tipe_pack'
                },
                {
                    data: 'color'
                },
                {
                    data: 'size'
                },
                {
                    data: 'qty'
                },
            ],
            columnDefs: [{
                    "className": "dt-center",
                    "targets": "_all"
                },
                {
                    targets: '_all',
                    className: 'text-nowrap',
                    render: (data, type, row, meta) => {
                        if (row.id_ppic_master_so === null) {
                            color = 'red';
                        } else {
                            color = '#087521';
                        }
                        return '<span style="font-weight: 600; color:' + color + '">' + data + '</span>';
                    }
                },
            ]


        }, );

        function tambah() {
            // let po = $('#cbopo').val();
            let txtnon_upload_tmbh = $('#txtnon_upload_tmbh').val();
            let cbotipe_tmbh = $('#cbotipe_tmbh').val();
            let modal_t_po = $('#modal_t_po').val();
            let modal_t_dest = $('#modal_t_dest').val();
            $.ajax({
                type: "post",
                url: '{{ route('store_upload_packing_list') }}',
                data: {
                    txtnon_upload: txtnon_upload_tmbh,
                    tipe: cbotipe_tmbh,
                    txtpo: modal_t_po,
                    txtdest: modal_t_dest
                },
                success: function(response) {
                    if (response.icon == 'salah') {
                        iziToast.warning({
                            message: response.msg,
                            position: 'topCenter'
                        });
                        dataTableReload();
                        dataTableUploadReload();
                    } else {
                        Swal.fire({
                            text: response.msg,
                            icon: "success"
                        });
                        dataTableUploadTambahReload();
                        delete_tmp_upload_tambah();
                    }

                },
                error: function(request, status, error) {
                    iziToast.warning({
                        message: 'Silahkan cek lagi',
                        position: 'topCenter'
                    });
                    dataTableUploadTambahReload();
                    delete_tmp_upload_tambah();
                    dataTableReload();
                },
            });
        };


        function delete_tmp_upload_tambah() {
            let modal_t_po = $('#modal_t_po').val();
            let modal_t_dest = $('#modal_t_dest').val();
            let po = modal_t_po + '_' + modal_t_dest;
            let html = $.ajax({
                type: "POST",
                url: '{{ route('delete_upload_packing_list') }}',
                data: {
                    po: po
                },
                async: false
            }).responseText;
        }

        function export_excel(po, dest, buyer, styleno) {
            // Step 1: Ask user which format they want
            Swal.fire({
                title: 'Pilih Format Export',
                showDenyButton: true,
                showCancelButton: true,
                confirmButtonText: 'Excel (.xlsx)',
                denyButtonText: 'CSV (.csv)',
                cancelButtonText: 'Batal',
            }).then((result) => {
                if (result.isConfirmed) {
                    sendExportRequest(po, dest, buyer, styleno, 'xlsx');
                } else if (result.isDenied) {
                    sendExportRequest(po, dest, buyer, styleno, 'csv');
                }
            });
        }

        // Step 2: The actual export AJAX request
        function sendExportRequest(po, dest, buyer, styleno, format) {
            Swal.fire({
                title: 'Please Wait...',
                html: 'Exporting Data...',
                didOpen: () => {
                    Swal.showLoading();
                },
                allowOutsideClick: false,
            });

            $.ajax({
                type: "get",
                url: '{{ route('export_excel_packing_list') }}',
                data: {
                    po,
                    dest,
                    buyer,
                    styleno,
                    format // send chosen format to backend
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

                    // Determine MIME type dynamically
                    const mimeType =
                        format === 'csv' ?
                        'text/csv;charset=utf-8;' :
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

                    const blob = new Blob([response], {
                        type: mimeType
                    });
                    const link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = `PO${po}.${format}`;
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
