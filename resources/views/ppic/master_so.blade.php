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
        input[type=file]::file-selector-button {
            margin-right: 20px;
            border: none;
            background: #084cdf;
            padding: 10px 20px;
            border-radius: 10px;
            color: #fff;
            cursor: pointer;
            transition: background .2s ease-in-out;
        }

        input[type=file]::file-selector-button:hover {
            background: #0d45a5;
        }

        .drop-container {
            position: relative;
            display: flex;
            gap: 10px;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 200px;
            padding: 20px;
            border-radius: 10px;
            border: 2px dashed #555;
            color: #444;
            cursor: pointer;
            transition: background .2s ease-in-out, border .2s ease-in-out;
        }

        .drop-container:hover {
            background: #eee;
            border-color: #111;
        }

        .drop-container:hover .drop-title {
            color: #222;
        }

        .drop-title {
            color: #444;
            font-size: 20px;
            font-weight: bold;
            text-align: center;
            transition: color .2s ease-in-out;
        }
    </style>
@endsection

@section('content')
    <!-- Import Excel -->
    <div class="modal fade" id="importExcel" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form method="post" action="{{ route('import-excel-so') }}" enctype="multipart/form-data"
                onsubmit="submitUploadForm(this, event)">
                <div class="modal-content">
                    <div class="modal-header bg-sb text-light">
                        <h5 class="modal-title" id="exampleModalLabel">Import Excel</h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">

                        {{ csrf_field() }}

                        <label for="images" class="drop-container" id="dropcontainer">
                            <span class="drop-title">Drop files here</span>
                            or
                            <input type="file" name="file" required="required">
                        </label>


                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="fa fa-window-close"
                                aria-hidden="true"></i> Close</button>
                        <button type="submit" class="btn btn-primary toastsDefaultDanger"><i class="fa fa-thumbs-up"
                                aria-hidden="true"></i> Import</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="exampleModalEdit" tabindex="-1" role="dialog" aria-labelledby="exampleModalEditLabel"
        aria-hidden="true">
        <form action="{{ route('update_data_ppic_master_so') }}" method="post" onsubmit="submitForm(this, event)"
            name='form' id='form'>
            @method('POST')
            <div class="modal-dialog modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header bg-warning">
                        <h3 class="modal-title fs-5">Update Data</h3>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="recipient-name" class="col-form-label">Tgl Shipment Sebelumnya :</label>
                                    <input type='text' class='form-control form-control-sm' id='txted_tgl_shipment'
                                        name='txted_tgl_shipment' autocomplete="off" readonly>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="recipient-name" class="col-form-label">Tgl Shipment Edit:</label>
                                    <input type='date' class='form-control form-control-sm' id='txted_tgl_shipment_skrg'
                                        name='txted_tgl_shipment_skrg' autocomplete="off">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="recipient-name" class="col-form-label">Qty PO Sebelumnya :</label>
                                    <input type='text' class='form-control form-control-sm' id='txted_qty_po'
                                        name='txted_qty_po' autocomplete="off" readonly>
                                    <input type='hidden' class='form-control form-control-sm' id='txtid_c'
                                        name='txtid_c' autocomplete="off" readonly>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="recipient-name" class="col-form-label">Qty PO Edit:</label>
                                    <input type='text' class='form-control form-control-sm' id='txted_qty_po_skrg'
                                        name='txted_qty_po_skrg' autocomplete="off">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-outline-success btn-sm"><i class="fas fa-check"></i> Simpan
                        </button>
                    </div>

                </div>
            </div>
        </form>
    </div>

    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-sb text-light">
                    <h1 class="modal-title fs-5" id="exampleModalLabel"> <i class="fas fa-chart-line"></i> Tracking
                        Output Packing
                    </h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class='row'>
                        <div class="col-md-12 table-responsive">
                            <table id="datatable_tracking"
                                class="table table-bordered table-striped table-sm w-100 nowrap">
                                <thead>
                                    <tr>
                                        <th>Tgl. Transaksi</th>
                                        <th>Line</th>
                                        <th>Total</th>
                                        <th>Unit</th>
                                        <th>ID SO Det</th>
                                        <th>WS</th>
                                        <th>List PO</th>
                                        <th>Buyer</th>
                                        <th>Color</th>
                                        <th>Size</th>
                                        <th>Style</th>
                                        <th>Dest SB</th>
                                    </tr>
                                </thead>
                                <tfoot>
                                    <tr>
                                        <th colspan="2"></th>
                                        <th> <input type = 'text' class="form-control form-control-sm"
                                                style="width:75px" readonly id = 'total_qty_chk'> </th>
                                        <th>PCS</th>
                                        <th colspan= "7"></th>
                                    </tr>
                                </tfoot>

                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="exampleModalEditMultiple" tabindex="-1" role="dialog"
        aria-labelledby="exampleModalEditMultipleLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-sb text-light">
                    <h1 class="modal-title fs-5" id="exampleModalEditMultipleLabel"> <i class="fas fa-edit"></i> Edit
                        Multiple
                    </h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="card card-primary collapsed-card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="far fa-edit"></i> Edit
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i
                                        class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <form id="form_e" name='form_e' method='post'
                                action="{{ route('edit_multiple_ppic_master_so') }}" onsubmit="submitForm(this, event)">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>WS</label>
                                            <div class="input-group">
                                                <select class="form-control select2bs4 form-control-sm rounded"
                                                    id="cbows_edit_tgl" name="cbows_edit_tgl"
                                                    onchange="dataTableEditReload();getno_po_edit_tgl();"
                                                    style="width: 100%;">
                                                    @foreach ($data_ws as $dataws)
                                                        <option value="{{ $dataws->isi }}">
                                                            {{ $dataws->tampil }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label><small><b>No. PO</b></small></label>
                                            <select class='form-control select2bs4 form-control-sm rounded'
                                                style='width: 100%;' name='cbopo_edit_tgl' id='cbopo_edit_tgl'
                                                onchange="dataTableEditReload()"></select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label><small><b>Tanggal Ubah</b></small></label>
                                            <input type="date" class="form-control form-control-sm" id="tgl_ubah"
                                                name="tgl_ubah" value="{{ date('Y-m-d') }}" min="{{ date('Y-m-d') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>&nbsp; &nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label>
                                            <div class="input-group">
                                                {{-- <button type="submit" class="btn btn-outline-success btn-sm"><i
                                                    class="fas fa-edit"></i>
                                                Update
                                            </button> --}}
                                                <input class="btn btn-outline-success btn-sm" type="button"
                                                    value="Update Tanggal" onclick="update_tgl()">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class='row'>
                                    <div class="col-md-12 table-responsive">
                                        <table id="datatable_edit"
                                            class="table table-bordered table-hover table-sm w-100 text-nowrap">
                                            <thead class="table-primary">
                                                <tr style='text-align:center; vertical-align:middle'>
                                                    <th>ID SO Det</th>
                                                    <th>Buyer</th>
                                                    <th>Tgl. Shipment</th>
                                                    <th>WS</th>
                                                    <th>Style</th>
                                                    <th>Barcode</th>
                                                    <th>Qty PO</th>
                                                    <th>No. PO</th>
                                                    <th>Reff</th>
                                                    <th>Dest</th>
                                                    <th>Desc</th>
                                                    <th>Color</th>
                                                    <th>Size</th>
                                                    <th>Qty Trf Garment</th>
                                                    <th>Qty Packing Out</th>
                                                    <th>User</th>
                                                    <th>Tgl. Upload</th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <div class="p-2 bd-highlight">
                                        </div>
                                        <div class="p-2 bd-highlight">
                                            <button type="submit" class="btn btn-outline-success"><i
                                                    class="fas fa-edit"></i>
                                                Update
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="card card-danger collapsed-card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-trash-alt"></i> Hapus
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i
                                        class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <form id="form_d" name='form_d' method='post'
                                action="{{ route('hapus_multiple_ppic_master_so') }}" onsubmit="submitForm(this, event)">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>WS</label>
                                            <div class="input-group">
                                                <select class="form-control select2bs4 form-control-sm rounded"
                                                    id="cbows_hapus" name="cbows_hapus"
                                                    onchange="dataTableHapusReload();getno_po_hapus();"
                                                    style="width: 100%;">
                                                    @foreach ($data_ws as $dataws)
                                                        <option value="{{ $dataws->isi }}">
                                                            {{ $dataws->tampil }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label><small><b>No. PO</b></small></label>
                                            <select class='form-control select2bs4 form-control-sm rounded'
                                                style='width: 100%;' name='cbopo_hapus' id='cbopo_hapus'
                                                onchange="dataTableHapusReload()"></select>
                                        </div>
                                    </div>
                                </div>
                                <div class='row'>
                                    <div class="col-md-12 table-responsive">
                                        <table id="datatable_hapus"
                                            class="table table-bordered table-striped table-sm w-100 text-nowrap">
                                            <thead class="table-primary">
                                                <tr style='text-align:center; vertical-align:middle'>
                                                    <th>
                                                        <input class="form-check checkbox-xl" type="checkbox"
                                                            onclick="toggle(this);">
                                                    </th>
                                                    <th>Buyer</th>
                                                    <th>Tgl. Shipment</th>
                                                    <th>WS</th>
                                                    <th>Style</th>
                                                    <th>Barcode</th>
                                                    <th>Qty PO</th>
                                                    <th>No. PO</th>
                                                    <th>Reff</th>
                                                    <th>Dest</th>
                                                    <th>Desc</th>
                                                    <th>Color</th>
                                                    <th>Size</th>
                                                    <th>Qty Trf</th>
                                                    <th>User</th>
                                                    <th>Tgl. Upload</th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <div class="p-2 bd-highlight">
                                        </div>
                                        <div class="p-2 bd-highlight">
                                            <button type="submit" class="btn btn-outline-danger"><i
                                                    class="fas fa-trash"></i>
                                                Hapus
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>

    </div>


    <div class="card card-info  collapsed-card" id = "upload-master-card">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-upload"></i> Upload Master SO PPIC</h5>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="d-flex align-items-end gap-3 mb-3">
                <div class="mb-3">
                    <input type="hidden" class="form-control form-control-sm" id="user" name= "user"
                        value="{{ $user }}">
                    <a class="btn btn-outline-info position-relative btn-sm" data-toggle="modal"
                        data-target="#importExcel" onclick="OpenModal()">
                        <i class="fas fa-file-upload fa-sm"></i>
                        Upload
                    </a>
                </div>
                <div class="mb-3">
                    <a class="btn btn-outline-warning position-relative btn-sm"
                        href="{{ route('contoh_upload_ppic_so') }}">
                        <i class="fas fa-file-download fa-sm"></i>
                        Contoh Upload
                    </a>
                </div>
                <div class="mb-3">
                    <a onclick="export_excel_master_so_sb()" class="btn btn-outline-success position-relative btn-sm">
                        <i class="fas fa-file-download fa-sm"></i>
                        Master Data SO SB
                    </a>
                </div>
            </div>
            <label>Preview</label>
            <div class="d-flex align-items-end gap-3 mb-3">
                <div class="mb-3">
                    <label><small><b>Data Double</b></small></label>
                    <input type="text" class="form-control form-control-sm" id="data_cek_tmp" name= "data_cek_tmp"
                        readonly>
                </div>
                <div class="mb-3">
                    <label><small><b>Data Tidak Valid</b></small></label>
                    <input type="text" class="form-control form-control-sm" id="data_cek_avail"
                        name= "data_cek_avail" readonly>
                </div>
            </div>

            <div class="table-responsive">
                <table id="datatable_preview" class="table table-bordered table-sm w-100 text-nowrap">
                    <thead class="table-info">
                        <tr style='text-align:center; vertical-align:middle'>
                            <th>ID SO Det</th>
                            <th>WS</th>
                            <th>Style</th>
                            <th>Desc</th>
                            <th>PO</th>
                            <th>Color</th>
                            <th>Size</th>
                            <th>Dest</th>
                            <th>Barcode</th>
                            <th>Total</th>
                            <th>Buyer</th>
                            <th>Tgl Shipment</th>
                            <th>Status</th>
                            <th>Act</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th colspan="9"></th>
                            <th> <input type = 'text' class="form-control form-control-sm" style="width:75px" readonly
                                    id = 'total_qty_chk'> </th>
                            <th>PCS</th>
                            <th></th>
                            <th></th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
                <div class="d-flex justify-content-between">
                    <div class="p-2 bd-highlight">
                        <a class="btn btn-outline-warning" onclick="undo()">
                            <i class="fas fa-sync-alt
                            fa-spin"></i>
                            Undo
                        </a>
                    </div>
                    <div class="p-2 bd-highlight" id="simpan_tmp" name = "simpan_tmp">
                        <a class="btn btn-outline-success" onclick="simpan()">
                            <i class="fas fa-check"></i>
                            Simpan
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-list"></i> List Master</h5>
        </div>
        <div class="card-body">
            <div class="d-flex align-items-end gap-3 mb-3">
                <div class="mb-3">
                    <label class="form-label"><small><b>Tgl Shipment Awal</b></small></label>
                    <input type="date" class="form-control form-control-sm " id="tgl-awal" name="tgl_awal"
                        value="{{ date('Y-m-d') }}">
                </div>
                <div class="mb-3">
                    <label class="form-label"><small><b>Tgl Shipment Akhir</b></small></label>
                    <input type="date" class="form-control form-control-sm" id="tgl-akhir" name="tgl_akhir"
                        value="{{ date('Y-m-d') }}">
                </div>
                <div class="mb-3">
                    <a class="btn btn-outline-primary position-relative btn-sm" onclick="dataTableReload()">
                        <i class="fas fa-search"></i>
                        Cari
                    </a>
                </div>
                <div class="mb-3">
                    <a onclick="export_excel_master_so_ppic()" class="btn btn-outline-success position-relative btn-sm">
                        <i class="fas fa-file-excel fa-sm"></i>
                        Export Excel
                    </a>
                </div>
                <div class="mb-3">
                    <a class="btn btn-outline-info position-relative btn-sm" data-bs-toggle="modal"
                        data-bs-target="#exampleModal" onclick="dataTableTrackingReload()">
                        <i class="fas fa-chart-line fa-sm"></i>
                        Tracking Output Packing
                    </a>
                </div>
                <div class="mb-3">
                    <a class="btn btn-outline-warning position-relative btn-sm" data-bs-toggle="modal"
                        data-bs-target="#exampleModalEditMultiple">
                        <i class="far fa-edit fa-sm"></i>
                        Edit Multiple
                    </a>
                </div>

                {{-- <div class="mb-3">
                    <a class="btn btn-outline-danger position-relative btn-sm" data-bs-toggle="modal"
                        data-bs-target="#exampleModalEditMultiple">
                        <i class="far fa-window-close fa-sm"></i>
                        Delete Multiple
                    </a>
                </div> --}}
            </div>

            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-striped table-sm w-100 text-nowrap">
                    <thead class="table-primary">
                        <tr style='text-align:center; vertical-align:middle'>
                            <th>ID</th>
                            <th>Buyer</th>
                            <th>Tgl. Shipment</th>
                            <th>WS</th>
                            <th>Style</th>
                            <th>Barcode</th>
                            <th>Reff</th>
                            <th>No. PO</th>
                            <th>Dest</th>
                            <th>Desc</th>
                            <th>Color</th>
                            <th>Size</th>
                            <th>Qty PO</th>
                            <th>Qty Tr Garment</th>
                            <th>Qty Packing In</th>
                            <th>Qty Packing Out</th>
                            <th>User</th>
                            <th>Tgl. Upload</th>
                            <th>Act</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th colspan="12"></th>
                            <th> <input type = 'text' class="form-control form-control-sm" style="width:75px" readonly
                                    id = 'total_qty_po'> </th>
                            <th> <input type = 'text' class="form-control form-control-sm" style="width:75px" readonly
                                    id = 'total_qty_chk'> </th>
                            <th> <input type = 'text' class="form-control form-control-sm" style="width:75px" readonly
                                    id = 'total_qty_p_in'> </th>
                            <th> <input type = 'text' class="form-control form-control-sm" style="width:75px" readonly
                                    id = 'total_qty_p_out'> </th>
                            <th></th>
                            <th></th>
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
            containerCssClass: 'form-control-sm rounded'
        });
    </script>
    <script>
        function notif() {
            alert("Maaf, Fitur belum tersedia!");
        }
    </script>
    <script type="text/javascript">
        function submitUploadForm(e, evt) {
            evt.preventDefault();

            clearModified();

            $.ajax({
                url: e.getAttribute('action'),
                type: e.getAttribute('method'),
                data: new FormData(e),
                processData: false,
                contentType: false,
                success: async function(res) {
                    if (res.status == 200) {
                        console.log(res);

                        e.reset();

                        // $('#cbows').val("").trigger("change");
                        // $("#cbomarker").prop("disabled", true);

                        Swal.fire({
                            icon: 'success',
                            title: 'Data Upload berhasil diupload',
                            showCancelButton: false,
                            showConfirmButton: true,
                            confirmButtonText: 'Oke',
                            timer: 5000,
                            timerProgressBar: true
                        })
                        dataTablePreviewReload();
                        $('#importExcel').modal('hide');
                        dataTableReload();
                        data_cek_double_tmp();
                    }
                },

            });
        }
    </script>
    <script>
        $('#exampleModalEditMultiple').on('show.bs.modal', function(e) {
            // $(document).on('select2:open', () => {
            //     document.querySelector('.select2-search__field').focus();
            // });
            // $('.select2').select2()
            $('.select2bs4').select2({
                theme: 'bootstrap4',
                dropdownParent: $("#exampleModalEditMultiple"),
                containerCssClass: 'form-control-sm rounded'
            })
            $('#cbows_edit_tgl').val('').trigger('change');
            $('#cbows_hapus').val('').trigger('change');

        })


        function OpenModal() {
            $('#importExcel').modal('show');
        }

        function dataTablePreviewReload() {
            datatable_preview.ajax.reload();
        }

        function dataTableReload() {
            datatable.ajax.reload();
        }

        $(document).ready(function() {
            dataTableReload();
            $('#upload-master-card').on('expanded.lte.cardwidget', () => {
                dataTablePreviewReload();
            });
            data_cek_double_tmp();
        })



        $('#datatable_preview thead tr').clone(true).appendTo('#datatable_preview thead');
        $('#datatable_preview thead tr:eq(1) th').each(function(i) {
            var title = $(this).text();
            $(this).html('<input type="text" class="form-control form-control-sm"/>');
            $('input', this).on('keyup change', function() {
                if (datatable_preview.column(i).search() !== this.value) {
                    datatable_preview
                        .column(i)
                        .search(this.value)
                        .draw();
                }
            });
        });

        let datatable_preview = $("#datatable_preview").DataTable({
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

                var countCheck = api
                    .column(12)
                    .data()
                    .reduce(function(count, value) {
                        return value === 'Check' ? count + 1 : count;
                    }, 0);



                // Update footer by showing the total with the reference of the column index
                $(api.column(0).footer()).html('Total');
                $(api.column(9).footer()).html(sumTotal);
                $('input[name="data_cek_avail"]').val(countCheck);
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
                url: '{{ route('show_tmp_ppic_so') }}',
                data: function(d) {
                    d.user = $('#user').val();
                },
            },
            columns: [{
                    data: 'id_so_det'

                },
                {
                    data: 'ws'
                },
                {
                    data: 'style'
                },
                {
                    data: 'desc'
                },
                {
                    data: 'po'
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
                    data: 'barcode'
                },
                {
                    data: 'qty_po'
                },
                {
                    data: 'buyer'
                },
                {
                    data: 'tgl_shipment'
                },
                {
                    data: 'status'
                },
                {
                    data: 'id_tmp'
                },
            ],
            columnDefs: [{
                    "className": "align-left",
                    "targets": "_all"
                },
                {
                    targets: [13],
                    render: (data, type, row, meta) => {
                        return `
                    <div
                    class='d-flex gap-1 justify-content-center'>
                    <a  class='btn btn-sm' data-bs-toggle='tooltip' onclick="hapus('` + row.id_tmp + `');"><i class='fas fa-minus fa-lg' style='color: #ff0000;'></i></a>
                    </div>
                        `;
                    }
                },
                {
                    targets: '_all',
                    className: 'text-nowrap',
                    render: (data, type, row, meta) => {
                        if (row.status == 'Ok') {
                            color = '#087521';
                        } else {
                            color = 'red';
                        }
                        return '<span style="font-weight: 600; color:' + color + '">' + data + '</span>';
                    }
                },

            ]
        });

        function undo() {
            let user = $('#user').val();
            $.ajax({
                type: "post",
                url: '{{ route('undo_tmp_ppic_so') }}',
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
                    dataTablePreviewReload();
                    data_cek_double_tmp();
                },
            });
        };

        function export_excel_master_so_sb() {
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
                url: '{{ route('export_excel_master_sb_so') }}',
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
                        link.download = "Master SO SB.xlsx";
                        link.click();

                    }
                },
            });
        }

        function simpan() {
            let data_cek_tmp = $('#data_cek_tmp').val();
            let data_cek_avail = $('#data_cek_avail').val();

            if (data_cek_tmp == '0' && data_cek_avail == '0') {
                $.ajax({
                    type: "post",
                    url: '{{ route('store_tmp_ppic_so') }}',
                    success: function(response) {
                        if (response.icon == 'salah') {
                            iziToast.warning({
                                message: response.msg,
                                position: 'topCenter'
                            });
                            dataTableReload();
                            dataTablePreviewReload();
                        } else {
                            Swal.fire({
                                text: response.msg,
                                icon: "success"
                            });
                            dataTableReload();
                            dataTablePreviewReload();
                            data_cek_double_tmp();
                        }

                    },
                    error: function(request, status, error) {
                        iziToast.warning({
                            message: 'Silahkan cek lagi',
                            position: 'topCenter'
                        });
                        dataTableReload();
                        dataTablePreviewReload();
                        data_cek_double_tmp();
                    },
                });
            } else {
                iziToast.warning({
                    message: 'Silahkan cek lagi',
                    position: 'topCenter'
                });
            }



        };


        $('#datatable thead tr').clone(true).appendTo('#datatable thead');
        $('#datatable thead tr:eq(1) th').each(function(i) {
            var title = $(this).text();
            $(this).html('<input type="text" class="form-control form-control-sm" />');

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
                var sumTotalPO = api
                    .column(12)
                    .data()
                    .reduce(function(a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                var sumTotalTr = api
                    .column(13)
                    .data()
                    .reduce(function(a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                var sumTotalPin = api
                    .column(14)
                    .data()
                    .reduce(function(a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                var sumTotalPout = api
                    .column(15)
                    .data()
                    .reduce(function(a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                // Update footer by showing the total with the reference of the column index
                $(api.column(0).footer()).html('Total');
                $(api.column(12).footer()).html(sumTotalPO);
                $(api.column(13).footer()).html(sumTotalTr);
                $(api.column(14).footer()).html(sumTotalPin);
                $(api.column(15).footer()).html(sumTotalPout);
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
                url: '{{ route('master-so') }}',
                data: function(d) {
                    d.dateFrom = $('#tgl-awal').val();
                    d.dateTo = $('#tgl-akhir').val();
                },
            },
            columns: [{
                    data: 'id'

                },
                {
                    data: 'buyer'

                }, {
                    data: 'tgl_shipment_fix'
                },
                {
                    data: 'ws'
                },
                {
                    data: 'styleno'
                },
                {
                    data: 'barcode'
                },
                {
                    data: 'reff_no'
                },
                {
                    data: 'po'
                },
                {
                    data: 'dest'
                },
                {
                    data: 'desc'
                },
                {
                    data: 'color'
                },
                {
                    data: 'size'
                },
                {
                    data: 'qty_po'
                },
                {
                    data: 'qty_trf'
                },
                {
                    data: 'qty_packing_in'
                },
                {
                    data: 'qty_packing_out'
                },
                {
                    data: 'created_by'
                },
                {
                    data: 'created_at'
                },
                {
                    data: 'id'
                },
            ],
            columnDefs: [{
                    targets: [18],
                    render: (data, type, row, meta) => {
                        return `
                <div
                class='d-flex gap-1 justify-content-center'>
                <a class='btn btn-warning btn-sm'  data-bs-toggle="modal"
                        data-bs-target="#exampleModalEdit"
                onclick="edit(` + row.id + `)"><i class='fas fa-edit'></i></a>
                </div>
                    `;
                    }
                },
                {
                    "className": "align-middle",
                    "targets": "_all"
                },
            ]
        });

        function edit(id_c) {
            jQuery.ajax({
                url: '{{ route('show_data_ppic_master_so') }}',
                method: 'GET',
                data: {
                    id_c: id_c
                },
                dataType: 'json',
                success: async function(response) {
                    document.getElementById('txted_qty_po').value = response.qty_po;
                    document.getElementById('txted_qty_po_skrg').value = response.qty_po;
                    document.getElementById('txted_tgl_shipment').value = response.tgl_shipment_fix;
                    document.getElementById('txted_tgl_shipment_skrg').value = response.tgl_shipment;
                    document.getElementById('txtid_c').value = id_c;
                },
                error: function(request, status, error) {
                    alert(request.responseText);
                },
            });
        }



        function export_excel_master_so_ppic() {
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
                url: '{{ route('export_excel_master_so_ppic') }}',
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
                        link.download = "Laporan PPIC tgl shipment  " + from + " sampai " +
                            to + ".xlsx";
                        link.click();

                    }
                },
            });
        }

        function hapus(id_tmp) {
            $.ajax({
                type: "post",
                url: '{{ route('hapus-data-temp-ppic-so') }}',
                data: {
                    id_tmp: id_tmp
                },
                success: async function(res) {
                    iziToast.success({
                        message: 'Data Berhasil Dihapus',
                        position: 'topCenter'
                    });
                    dataTablePreviewReload();
                    dataTableReload();
                    data_cek_double_tmp();
                }
            });

        }

        function dataTableTrackingReload() {
            datatable_tracking.ajax.reload();
        }

        function dataTableEditReload() {
            datatable_edit.ajax.reload();
        }

        function dataTableHapusReload() {
            datatable_hapus.ajax.reload();
        }

        let datatable_tracking = $("#datatable_tracking").DataTable({
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
                    .column(2)
                    .data()
                    .reduce(function(a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                // Update footer by showing the total with the reference of the column index
                $(api.column(0).footer()).html('Total');
                $(api.column(2).footer()).html(sumTotal);
            },



            ordering: true,
            processing: true,
            serverSide: true,
            paging: false,
            searching: true,
            scrollY: '300px',
            scrollX: '300px',
            scrollCollapse: true,
            ajax: {
                url: '{{ route('master_so_tracking_output') }}',
            },
            columns: [{
                    data: 'tgl_trans'

                }, {
                    data: 'sewing_line'
                },
                {
                    data: 'tot'
                },
                {
                    data: 'unit'
                },
                {
                    data: 'so_det_id'
                },
                {
                    data: 'ws'
                },
                {
                    data: 'list_po'
                },
                {
                    data: 'buyer'
                },
                {
                    data: 'color'
                },
                {
                    data: 'size'
                },
                {
                    data: 'styleno'
                },
                {
                    data: 'dest'
                },
            ],
            columnDefs: [{
                "className": "dt-left",
                "targets": "_all"
            }, ]
        });


        $('#datatable_edit thead tr').clone(true).appendTo('#datatable_edit thead');
        $('#datatable_edit thead tr:eq(1) th').each(function(i) {
            var title = $(this).text();
            $(this).html('<input type="text" class="form-control form-control-sm"/>');
            $('input', this).on('keyup change', function() {
                if (datatable_edit.column(i).search() !== this.value) {
                    datatable_edit
                        .column(i)
                        .search(this.value)
                        .draw();
                }
            });
        });

        let datatable_edit = $("#datatable_edit").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            paging: false,
            searching: true,
            scrollY: '300px',
            scrollX: '300px',
            scrollCollapse: true,
            ajax: {
                url: '{{ route('list_master_ppic_edit') }}',
                data: function(d) {
                    d.ws = $('#cbows_edit_tgl').val();
                    d.po = $('#cbopo_edit_tgl').val();
                },
            },
            columns: [{
                    data: 'id_so_det'

                },
                {
                    data: 'buyer'

                }, {
                    data: 'tgl_shipment_fix'
                },
                {
                    data: 'ws'
                },
                {
                    data: 'styleno'
                },
                {
                    data: 'barcode'
                },
                {
                    data: 'qty_po'
                },
                {
                    data: 'po'
                },
                {
                    data: 'reff_no'
                },
                {
                    data: 'dest'
                },
                {
                    data: 'desc'
                },
                {
                    data: 'color'
                },
                {
                    data: 'size'
                },
                {
                    data: 'qty_trf'
                },
                // {
                //     data: 'qty_packing_in'
                // },
                {
                    data: 'qty_packing_out'
                },
                {
                    data: 'created_by'
                },
                {
                    data: 'created_at'
                },
            ],
            columnDefs: [{
                    "className": "dt-left",
                    "targets": "_all"
                },
                {
                    targets: [5],
                    render: (data, type, row, meta) => {

                        if (row.qty_packing_out == '0' && row.id_pl === null) {
                            return `
                               <div class='d-flex gap-1 justify-content-center'>
							<input type ='text' style='width:120px' class='form-control form-control-sm'
                            id="barcode` + row.id + `"
                            name="barcode[` + row.id + `]"
                            value="` + row.barcode + `">
                            </div>
                            `
                        } else {
                            return `
                               <div class='d-flex gap-1 justify-content-center'>
							<input type ='text' style='width:120px' class='form-control form-control-sm'
                            id="barcode` + row.id + `"
                            name="barcode[` + row.id + `]"
                            value="` + row.barcode + `" readonly>
                            </div>
                            `
                        }

                    }
                },
                {
                    targets: [2],
                    render: (data, type, row, meta) => {
                        return `
                            <div class='d-flex gap-1 justify-content-center'>
							<input type ='text' style='width:100px' class='form-control form-control-sm'
                            id="tgl_shipment` + row.id + `"
                            name="tgl_shipment[` + row.id + `]"
                            value="` + row.tgl_shipment + `">
							<input type ='hidden' style='width:120px' class='form-control form-control-sm'
                            id="id` + row.id + `"
                            name="id[` + row.id + `]"
                            value="` + row.id + `">
                            <input type ='hidden' style='width:120px' class='form-control form-control-sm'
                            id="id` + row.id + `"
                            name="id[` + row.id + `]"
                            value="` + row.id + `">
                            </div>
                            `
                    }
                },
                {
                    targets: [6],
                    render: (data, type, row, meta) => {
                        return `
                            <div class='d-flex gap-1 justify-content-center'>
							<input type ='text' style='width:100px' class='form-control form-control-sm'
                            id="qty_po` + row.id + `"
                            name="qty_po[` + row.id + `]"
                            value="` + row.qty_po + `">
                            </div>
                            `
                    }
                },
                {
                    targets: [7],
                    render: (data, type, row, meta) => {
                        if (row.qty_trf == '0' && row.id_pl === null) {
                            return `
                            <div class='d-flex gap-1 justify-content-center'>
							<input type ='text' style='width:100px' class='form-control form-control-sm'
                            id="po` + row.id + `"
                            name="po[` + row.id + `]"
                            value="` + row.po + `">
                            </div>
                            `
                        } else {
                            return `
                            <div class='d-flex gap-1 justify-content-center'>
							<input type ='text' style='width:100px' class='form-control form-control-sm' readonly
                            id="po` + row.id + `"
                            name="po[` + row.id + `]"
                            value="` + row.po + `">
                            </div>
                            `
                        }

                    }
                },

            ]
        });

        $('#datatable_hapus thead tr').clone(true).appendTo('#datatable_hapus thead');
        $('#datatable_hapus thead tr:eq(1) th').each(function(i) {
            var title = $(this).text();
            $(this).html('<input type="text" class="form-control form-control-sm"/>');
            $('input', this).on('keyup change', function() {
                if (datatable_hapus.column(i).search() !== this.value) {
                    datatable_hapus
                        .column(i)
                        .search(this.value)
                        .draw();
                }
            });
        });

        let datatable_hapus = $("#datatable_hapus").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            paging: false,
            searching: true,
            scrollY: '300px',
            scrollX: '300px',
            scrollCollapse: true,
            ajax: {
                url: '{{ route('list_master_ppic_edit') }}',
                data: function(d) {
                    d.ws = $('#cbows_hapus').val();
                    d.po = $('#cbopo_hapus').val();
                },
            },
            columns: [{
                    data: 'id_so_det'

                },
                {
                    data: 'buyer'

                }, {
                    data: 'tgl_shipment_fix'
                },
                {
                    data: 'ws'
                },
                {
                    data: 'styleno'
                },
                {
                    data: 'barcode'
                },
                {
                    data: 'qty_po'
                },
                {
                    data: 'po'
                },
                {
                    data: 'reff_no'
                },
                {
                    data: 'dest'
                },
                {
                    data: 'desc'
                },
                {
                    data: 'color'
                },
                {
                    data: 'size'
                },
                // {
                //     data: 'qty_trf'
                // },
                // {
                //     data: 'qty_packing_in'
                // },
                {
                    data: 'qty_trf'
                },
                {
                    data: 'created_by'
                },
                {
                    data: 'created_at'
                },
            ],
            columnDefs: [{
                    "className": "dt-left",
                    "targets": "_all"
                },
                {
                    targets: [0],
                    render: (data, type, row, meta) => {
                        if (row.qty_trf == '0') {
                            return `
                    <div
                        class="form-check checkbox-xl" style="text-align:center">
                        <input class="form-check-input" type="checkbox"
                        value="` + row.id + `" id="cek_data" onchange="ceklis(this)"
                        name="cek_data[` + row.id + `] "/>
                    </div>
                    <div>
                            <input type="hidden" size="10" id="id"
                            name="id[` + row.id + `]" value = "` + row.id + `"/>
                    </div>
                    `;

                        } else {
                            return `
                    <div>
                            <input type="hidden" size="10" id="id"
                            name="id[` + row.id + `]" value = "` + row.id + `"/>
                    </div>
                    `;
                        }

                    }
                },

            ]
        });


        function getno_po_edit_tgl() {
            let cbows_edit_tgl = document.form_e.cbows_edit_tgl.value;
            let html = $.ajax({
                type: "GET",
                url: '{{ route('getpo_ppic_edit_tgl') }}',
                data: {
                    cbows_edit_tgl: cbows_edit_tgl
                },
                async: false
            }).responseText;
            if (html != "") {
                $("#cbopo_edit_tgl").html(html);
            }
        };


        function getno_po_hapus() {
            let cbows_hapus = document.form_d.cbows_hapus.value;
            let html = $.ajax({
                type: "GET",
                url: '{{ route('getpo_ppic_hapus') }}',
                data: {
                    cbows_hapus: cbows_hapus
                },
                async: false
            }).responseText;
            if (html != "") {
                $("#cbopo_hapus").html(html);
            }
        };

        function update_tgl() {
            let cbows_edit_tgl = document.form_e.cbows_edit_tgl.value;
            let cbopo_edit_tgl = document.form_e.cbopo_edit_tgl.value;
            let tgl_ubah = document.form_e.tgl_ubah.value;
            $.ajax({
                type: "post",
                url: '{{ route('update_tgl_ppic_master_so') }}',
                data: {
                    cbows_edit_tgl: cbows_edit_tgl,
                    cbopo_edit_tgl: cbopo_edit_tgl,
                    tgl_ubah: tgl_ubah
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
                    dataTableEditReload();
                    dataTableReload();
                },
                // error: function(request, status, error) {
                //     alert(request.responseText);
                // },
            });
        };

        function ceklis(checkeds) {
            //get id..and check if checked
            console.log($(checkeds).attr("value"), checkeds.checked)

        }

        function data_cek_double_tmp() {
            jQuery.ajax({
                url: '{{ route('data_cek_double_tmp_ppic_so') }}',
                method: 'GET',
                dataType: 'json',
                success: async function(response) {
                    console.log(response);
                    document.getElementById('data_cek_tmp').value = response ? response.tot_cek : 0;
                },
                error: function(request, status, error) {
                    alert(request.responseText);
                },
            });
        }
    </script>
@endsection
