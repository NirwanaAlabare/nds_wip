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
    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <form action="{{ route('store-tambah-karton') }}" method="post" onsubmit="submitForm(this, event)" name='form'
            id='form'>
            @method('POST')
            <div class="modal-dialog modal-dialog-scrollable modal-s">
                <div class="modal-content">
                    <div class="modal-header bg-sb text-light">
                        <h3 class="modal-title fs-5"><i class="fas fa-boxes"></i> Tambah Karton</h3>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label><small><b>PO :</b></small></label>
                            <select class="form-control select2bs4" id="cbopo" name="cbopo" style="width: 100%;"
                                onchange="gettot_karton()">
                                <option selected="selected" value="" disabled="true">Pilih PO</option>
                                @foreach ($data_po as $datapo)
                                    <option value="{{ $datapo->isi }}">
                                        {{ $datapo->tampil }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label><small><b>Notes :</b></small></label>
                            <input type='text' id="txtnotes" name="txtnotes" class='form-control form-control-sm'
                                value = '-'>
                        </div>
                        <div class="row">
                            <div class="col-sm">
                                <div class="form-group">
                                    <label><small><b>Total Karton Sekarang # :</b></small></label>
                                    <input type='number' id="tot_skrg" name="tot_skrg"
                                        class='form-control form-control-sm' id="txtinput_carton" name="txtinput_carton"
                                        value="">
                                </div>
                            </div>
                            <div class="col-sm">
                                <div class="form-group">
                                    <label><small><b>Simulasi Tambah # :</b></small></label>
                                    <input type='number' id="txt_simulasi" name="txt_simulasi"
                                        class='form-control form-control-sm' value="0" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label><small><b>Penambahan Karton # :</b></small></label>
                            <input type='number' min = '0' value = '0' oninput="get_total_tambah()"
                                autocomplete="off" class='form-control form-control-sm' id="txtinput_carton"
                                name="txtinput_carton" value="">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-danger btn-sm" data-bs-dismiss="modal"><i
                                class="fas fa-times-circle"></i> Tutup</button>
                        <button type="submit" class="btn btn-outline-success btn-sm"><i class="fas fa-check"></i> Simpan
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="modal fade" id="exampleModalCheck" tabindex="-1" role="dialog" aria-labelledby="exampleModalCheckLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h3 class="modal-title fs-5">List Detail karton</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class='row'>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="form-label"><small><b>PO</b></small></label>
                                <input type="text" class="form-control form-control-sm" id = "modal_po"
                                    name = "modal_po" readonly>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label"><small><b>Buyer</b></small></label>
                                <input type="text" class="form-control form-control-sm" id = "modal_buyer"
                                    name = "modal_buyer" readonly>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="form-label"><small><b>Tgl.Shipment</b></small></label>
                                <input type="text" class="form-control form-control-sm" id = "modal_tgl_shipment"
                                    name = "modal_tgl_shipment" readonly>
                            </div>
                        </div>
                    </div>
                    <div class='row'>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="form-label"><small><b>Total Karton</b></small></label>
                                <input type="text" class="form-control form-control-sm" id = "modal_tot_karton"
                                    name = "modal_tot_karton" readonly>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="form-label"><small><b>Karton Isi</b></small></label>
                                <input type="text" class="form-control form-control-sm" id = "modal_tot_isi"
                                    name = "modal_tot_isi" readonly>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="form-label"><small><b>Karton Kosong</b></small></label>
                                <input type="text" class="form-control form-control-sm" id = "modal_tot_kosong"
                                    name = "modal_tot_kosong" readonly>
                            </div>
                        </div>
                    </div>
                    <div class='row'>
                        <div class="col-md-12 table-responsive">
                            <table id="datatable_detail_karton"
                                class="table table-bordered table-hover 100 nowrap">
                                <thead>
                                    <tr>
                                        <th>No.carton</th>
                                        <th>Ket</th>
                                        <th>Barcode</th>
                                        <th>WS</th>
                                        <th>Color</th>
                                        <th>Size</th>
                                        <th>Dest</th>
                                        <th>Desc</th>
                                        <th>Style</th>
                                        <th>Product</th>
                                        <th>Qty</th>
                                        <th>Qty Max Karton</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tfoot>
                                    <tr>
                                        <th colspan="10"></th>
                                        <th> <input type = 'text' class="form-control form-control-sm"
                                                style="width:75px" readonly id = 'total_qty_chk'> </th>
                                        <th></th>
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

    <div class="modal fade" id="exampleModalEdit" tabindex="-1" role="dialog" aria-labelledby="exampleModalEditLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h3 class="modal-title fs-5"><i class="far fa-edit"></i> Edit Karton</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="card card-danger collapsed-card" id = "modal_hapus">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-trash"></i> Hapus Isi Karton
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i
                                        class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <form id="form_h" name='form_h' method='post'
                                action="{{ route('hapus_master_karton_det') }}" onsubmit="submitForm(this, event)">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label><small><b>No. PO</b></small></label>
                                            <input type="text" class="form-control form-control-sm" id="txtmodal_h_po"
                                                name="txtmodal_h_po" onchange="getno_carton_modal_hapus();" readonly
                                                style="width: 100%;">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label><small><b>No. Karton</b></small></label>
                                            <select class='form-control select2bs4 form-control-sm rounded'
                                                style='width: 100%;' name='cbomodal_h_no_karton'
                                                id='cbomodal_h_no_karton' onchange="dataTableHapusReload()"></select>
                                        </div>
                                    </div>
                                </div>
                                <div class='row'>
                                    <div class="col-md-12 table-responsive">
                                        <table id="datatable_hapus"
                                            class="table table-bordered table-striped 100 text-nowrap">
                                            <thead class="table-primary">
                                                <tr style='text-align:center; vertical-align:middle'>
                                                    <th>
                                                        <input class="form-check checkbox-xl" type="checkbox"
                                                            onclick="toggle(this);">
                                                    </th>
                                                    <th>Barcode</th>
                                                    <th>Color</th>
                                                    <th>Size</th>
                                                    <th>Dest</th>
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

                    <div class="card card-danger collapsed-card" id = "modal_hapus_karton">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-list"></i> Hapus Karton
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i
                                        class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <form id="form_h_karton" name='form_h_karton' method='post'
                                action="{{ route('hapus_master_karton') }}" onsubmit="submitForm(this, event)">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label><small><b>No. PO</b></small></label>
                                            <input type="text" class="form-control form-control-sm"
                                                id="txtmodal_h_po_karton" name="txtmodal_h_po_karton" readonly
                                                style="width: 100%;">
                                        </div>
                                    </div>
                                </div>
                                <div class='row'>
                                    <div class="col-md-12 table-responsive">
                                        <table id="datatable_hapus_karton"
                                            class="table table-bordered 100 text-nowrap">
                                            <thead class="table-primary">
                                                <tr style='text-align:center; vertical-align:middle'>
                                                    <th>
                                                        <input class="form-check checkbox-xl" type="checkbox"
                                                            onclick="toggle(this);">
                                                    </th>
                                                    <th>PO</th>
                                                    <th>No. Karton</th>
                                                    <th>Notes</th>
                                                    <th>Qty Max Karton</th>
                                                    <th>Qty Karton</th>
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

                    {{-- <div class="card card-primary collapsed-card" id = "modal_tambah">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-plus"></i> Tambah Data
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i
                                        class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <form id="form_p" name='form_p' method='post'>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label><small><b>No. PO</b></small></label>
                                            <input type="text" class="form-control form-control-sm" id="txtmodal_p_po"
                                                name="txtmodal_p_po"
                                                onchange="getno_carton_modal_tambah();getbarcode_modal_tambah();" readonly
                                                style="width: 100%;">
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <label><small><b>Barcode / Item</b></small></label>
                                            <select class='form-control select2bs4 form-control-sm rounded'
                                                style='width: 100%;' name='cbomodal_p_id_ppic_master_so'
                                                id='cbomodal_p_id_ppic_master_so'
                                                onchange="getstok_packing_in();"></select>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label><small><b>Qty</b></small></label>
                                            <input type='number' id="cbomodal_p_qty" name="cbomodal_p_qty"
                                                class='form-control form-control-sm' value="">
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label><small><b>No. Karton</b></small></label>
                                            <select class='form-control select2bs4 form-control-sm rounded'
                                                style='width: 100%;' name='cbomodal_p_no_karton'
                                                id='cbomodal_p_no_karton' onchange="dataTableTambahReload();"></select>
                                        </div>
                                    </div>
                                </div>
                                <div class='row'>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label><small><b>Qty Stok Packing In</b></small></label>
                                            <input type='number' id="cbomodal_p_qty_stok" name="cbomodal_p_qty_stok"
                                                class='form-control form-control-sm' value="" readonly>
                                        </div>
                                    </div>
                                </div>
                                <div class='row'>
                                    <div class="col-md-12 table-responsive">
                                        <table id="datatable_tambah"
                                            class="table table-bordered table-striped 100 text-nowrap">
                                            <thead class="table-primary">
                                                <tr style='text-align:center; vertical-align:middle'>
                                                    <th>PO</th>
                                                    <th>Barcode</th>
                                                    <th>Color</th>
                                                    <th>Size</th>
                                                    <th>Dest</th>
                                                    <th>WS</th>
                                                    <th>Qty</th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <div class="p-2 bd-highlight">
                                        </div>
                                        <div class="p-2 bd-highlight">
                                            <a class="btn btn-outline-primary" onclick="simpan_modal_tambah()">
                                                <i class="fas fa-check"></i>
                                                Simpan
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div> --}}
                    {{-- <div class="card card-info collapsed-card" id = "modal_short">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-box"></i> Short Carton
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i
                                        class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <form id="form_short" name='form_short' method='post'
                                action="{{ route('simpan_short_karton') }}" onsubmit="submitForm(this, event)">
                                <div class='row'>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label><small><b>Jumlah Carton</b></small></label>
                                            <input type="text" class="form-control form-control-sm" id="txtmodal_s_po"
                                                name="txtmodal_s_po" style="width: 100%;" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label><small><b>Jumlah Carton</b></small></label>
                                            <input type="text" class="form-control form-control-sm"
                                                id="txtmodal_s_no_carton" name="txtmodal_s_no_carton"
                                                style="width: 100%;" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label><small><b>Hasil Short Carton</b></small></label>
                                            <input type="text" class="form-control form-control-sm"
                                                id="txtmodal_s_hsl_short" name="txtmodal_s_hsl_short"
                                                style="width: 100%;">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>&nbsp; &nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label>
                                            <div class="input-group">
                                                <button type="submit" class="btn btn-outline-primary btn-sm"><i
                                                        class="fas fa-check"></i>
                                                    Update
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div> --}}
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="exampleModalUpload" tabindex="-1" role="dialog"
        aria-labelledby="exampleModalUploadLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info">
                    <h3 class="modal-title fs-5">Upload Qty Karton</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="post" action="{{ route('upload_qty_karton') }}" enctype="multipart/form-data"
                        onsubmit="submitForm(this, event)" name="form_upload" id="form_upload">
                        @csrf
                        <div class='row'>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label"><small><b>PO</b></small></label>
                                    <input type="text" class="form-control form-control-sm" id = "modal_upload_po"
                                        name = "modal_upload_po" readonly>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label"><small><b>Qty PO</b></small></label>
                                    <input type="text" class="form-control form-control-sm" id = "modal_qty_po"
                                        name = "modal_qty_po" readonly>
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
                        </div>
                        <div class='row'>
                            <div class="col-md-12 table-responsive">
                                <table id="datatable_upload"
                                    class="table table-bordered table-hover 100 nowrap">
                                    <thead>
                                        <tr>
                                            <th>No. Karton</th>
                                            <th>Qty Max Karton</th>
                                        </tr>
                                    </thead>
                                    <tfoot>
                                        <tr>
                                            <th>Total</th>
                                            <th> <input type = 'text' class="form-control form-control-sm"
                                                    style="width:75px" readonly id = 'total_qty_chk'> </th>
                                        </tr>
                                    </tfoot>
                                </table>
                                <div class="d-flex justify-content-between">
                                    <div class="p-2 bd-highlight">
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
            </div>
        </div>
    </div>


    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-list"></i> List Master Karton</h5>
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
                        oninput="dataTableReload()" value="{{ date('Y-m-d') }}">
                </div>
                <div class="mb-3">
                    <label class="form-label"><small><b>Tgl Shipment Akhir</b></small></label>
                    <input type="date" class="form-control form-control-sm" id="tgl-akhir" name="tgl_akhir"
                        oninput="dataTableReload()" value="{{ $tgl_akhir_fix }}">
                </div>
                {{-- <div class="mb-3">
                    <a onclick="export_excel_packing_master_karton()"
                        class="btn btn-outline-success position-relative btn-sm">
                        <i class="fas fa-file-excel fa-sm"></i>
                        Export Excel
                    </a>
                </div> --}}
            </div>

            <div class="table-responsive">
                <table id="datatable" class="table table-bordered 100 table-hover display nowrap">
                    <thead class="table-primary">
                        <tr style='text-align:center; vertical-align:middle'>
                            <th>Tgl. Shipment</th>
                            <th>PO #</th>
                            <th>WS</th>
                            <th>Buyer</th>
                            <th>Style</th>
                            <th>Product Group</th>
                            <th>Product Item</th>
                            <th>Jumlah Karton</th>
                            <th>Karton Isi</th>
                            <th>Karton Kosong</th>
                            <th>Qty PO</th>
                            <th>Tot Scan</th>
                            <th>Act</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th colspan="7"></th>
                            <th> <input type = 'text' class="form-control form-control-sm" style="width:75px" readonly
                                    id = 'total_qty_carton'> </th>
                            <th> <input type = 'text' class="form-control form-control-sm" style="width:75px" readonly
                                    id = 'total_qty_carton_isi'> </th>
                            <th> <input type = 'text' class="form-control form-control-sm" style="width:75px" readonly
                                    id = 'total_qty_carton_kosong'> </th>
                            <th> <input type = 'text' class="form-control form-control-sm" style="width:75px" readonly
                                    id = 'total_qty_po'> </th>
                            <th> <input type = 'text' class="form-control form-control-sm" style="width:75px" readonly
                                    id = 'total_qty_scan'> </th>
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
        })


        $('#exampleModalEdit').on('show.bs.modal', function(e) {

            $('.select2bs4').select2({
                theme: 'bootstrap4',
                dropdownParent: $("#exampleModalEdit"),
                containerCssClass: 'form-control-sm rounded'
            })

        })

        $('#exampleModalUpload').on('show.bs.modal', function(e) {

            $('.select2bs4').select2({
                theme: 'bootstrap4',
                dropdownParent: $("#exampleModalUpload"),
                containerCssClass: 'form-control-sm rounded'
            })

        })
    </script>
    <script>
        function notif() {
            alert("Maaf, Fitur belum tersedia!");
        }

        function reset() {
            $("#form").trigger("reset");
            $("#cbopo").val('').trigger('change');
        }
    </script>
    <script>
        $(document).ready(() => {
            reset();

            const todayA = new Date();
            const yyyyA = todayA.getFullYear();
            let mmA = todayA.getMonth() - 3; // Months start at 0!
            let ddA = todayA.getDate();
            if (ddA < 10) ddA = '0' + ddA;
            if (mmA < 10) mmA = '0' + mmA;
            const formattedTodayA = yyyyA + '-' + mmA + '-' + ddA;
            console.log(formattedTodayA);
            $("#tgl-awal").val(formattedTodayA).trigger("change");

            // const todayE = new Date();
            // const yyyyE = todayE.getFullYear();
            // let mmE = todayE.getMonth() + 3; // Months start at 0!
            // let ddE = todayE.getDate();
            // if (ddE < 10) ddE = '0' + ddE;
            // if (mmE < 10) mmE = '0' + mmE;
            // const formattedTodayE = yyyyE + '-' + mmE + '-' + ddE;
            // console.log(formattedTodayE);

            // $("#tgl-akhir").val(formattedTodayE).trigger("change");

            dataTableReload();
            // dataTablePreviewReload();
            // startCalc();

            $('#modal_hapus').on('expanded.lte.cardwidget', () => {
                dataTableHapusReload();
            });
            $('#modal_tambah').on('expanded.lte.cardwidget', () => {
                dataTableTambahReload();
            });
            $('#modal_hapus_karton').on('expanded.lte.cardwidget', () => {
                dataTableHapusKartonReload();
            });

        });

        function get_total_tambah() {
            let a = document.getElementById('tot_skrg').value;
            let b = document.getElementById('txtinput_carton').value;
            let hasil = document.form.txt_simulasi.value;
            hasil = (parseInt(a)) + (parseInt(b));
            document.form.txt_simulasi.value = Math.floor(hasil);
        }

        function gettot_karton() {
            let cbopo = document.form.cbopo.value;
            $.ajax({
                url: '{{ route('show_tot') }}',
                method: 'get',
                data: {
                    cbopo: cbopo
                },
                dataType: 'json',
                success: function(response) {
                    document.getElementById('tot_skrg').value = response.tot_skrg;
                },
                error: function(request, status, error) {
                    alert(request.responseText);
                },
            });
        };


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
                    .column(7)
                    .data()
                    .reduce(function(a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                var sumTotalI = api
                    .column(8)
                    .data()
                    .reduce(function(a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                var sumTotalK = api
                    .column(9)
                    .data()
                    .reduce(function(a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                var sumTotalQ = api
                    .column(10)
                    .data()
                    .reduce(function(a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                // computing column Total of the complete result
                var sumTotalS = api
                    .column(11)
                    .data()
                    .reduce(function(a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                // Update footer by showing the total with the reference of the column index
                $(api.column(0).footer()).html('Total');
                $(api.column(7).footer()).html(sumTotal);
                $(api.column(8).footer()).html(sumTotalI);
                $(api.column(9).footer()).html(sumTotalK);
                $(api.column(10).footer()).html(sumTotalQ);
                $(api.column(11).footer()).html(sumTotalS);
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
                url: '{{ route('master-karton') }}',
                data: function(d) {
                    d.dateFrom = $('#tgl-awal').val();
                    d.dateTo = $('#tgl-akhir').val();
                },
            },
            columns: [{
                    data: 'tgl_shipment_fix'

                },
                {
                    data: 'po'
                },
                {
                    data: 'ws'
                },
                {
                    data: 'buyer'
                },
                {
                    data: 'styleno'
                },
                {
                    data: 'product_group'
                },
                {
                    data: 'product_item'
                },
                {
                    data: 'tot_karton'
                },
                {
                    data: 'tot_karton_isi'
                },
                {
                    data: 'tot_karton_kosong'
                },
                {
                    data: 'qty_po'
                },
                {
                    data: 'tot_scan'
                },
                {
                    data: 'po'
                },
            ],
            columnDefs: [{
                    "className": "dt-left",
                    "targets": "_all"
                },
                {
                    targets: [12],
                    render: (data, type, row, meta) => {
                        return `
                <div class='d-flex gap-1 justify-content-center'>
                <a class='btn btn-primary btn-sm'  data-bs-toggle="modal"
                        data-bs-target="#exampleModalCheck"
                onclick="show_data('` + row.po + `','` + row.buyer + `','` + row.tgl_shipment_fix + `','` + row
                            .tot_karton + `','` + row.tot_karton_isi + `','` + row.tot_karton_kosong + `' );dataTableDetKartonReload();"><i class='fas fa-search'></i></a>
                <a class='btn btn-danger btn-sm'  data-bs-toggle="modal"
                        data-bs-target="#exampleModalEdit"
                onclick="show_data_edit_h('` + row.po + `','` + row.tot_karton + `');"><i class='fas fa-edit'></i></a>
                <a class='btn btn-info btn-sm'  data-bs-toggle="modal"
                        data-bs-target="#exampleModalUpload"
                onclick="show_data_upload('` + row.po + `','` + row.qty_po + `');"><i class='fas fa-upload'></i></a>
                            </div>
                    `;
                    }
                },


            ]


        }, );


        function dataTableDetKartonReload() {
            datatable_detail_karton.ajax.reload();
        }

        function show_data(po_s, po_b, tgl_shipment_fix, tot_karton, tot_karton_isi, tot_karton_kosong) {
            // console.log(po_s);
            // datatable_detail_karton.ajax.reload();
            $('#modal_po').val(po_s);
            $('#modal_buyer').val(po_b);
            $('#modal_tgl_shipment').val(tgl_shipment_fix);
            $('#modal_tot_karton').val(tot_karton);
            $('#modal_tot_isi').val(tot_karton_isi);
            $('#modal_tot_kosong').val(tot_karton_kosong);
        }

        $('#datatable_detail_karton thead tr').clone(true).appendTo('#datatable_detail_karton thead');
        $('#datatable_detail_karton thead tr:eq(1) th').each(function(i) {
            var title = $(this).text();
            $(this).html('<input type="text" class="form-control form-control-sm"/>');
            $('input', this).on('keyup change', function() {
                if (datatable_detail_karton.column(i).search() !== this.value) {
                    datatable_detail_karton
                        .column(i)
                        .search(this.value)
                        .draw();
                }
            });
        });


        let datatable_detail_karton = $("#datatable_detail_karton").DataTable({

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
                    .column(10)
                    .data()
                    .reduce(function(a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                // Update footer by showing the total with the reference of the column index
                $(api.column(0).footer()).html('Total');
                $(api.column(10).footer()).html(sumTotal);
            },

            ordering: false,
            processing: true,
            serverSide: true,
            paging: false,
            searching: true,
            scrollY: '300px',
            scrollX: '300px',
            scrollCollapse: true,
            destroy: true,
            info: true,
            ajax: {
                url: '{{ route('show_detail_karton') }}',
                method: 'GET',
                data: function(d) {
                    d.po = $('#modal_po').val();
                },
            },
            columns: [{
                    data: 'no_carton'

                },
                {
                    data: 'notes'
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
                    data: 'dest'
                },
                {
                    data: 'desc'
                },
                {
                    data: 'styleno'
                },
                {
                    data: 'product_item'
                },
                {
                    data: 'tot'
                },
                {
                    data: 'qty_isi'
                },
                {
                    data: 'stat'
                },
            ],
            columnDefs: [{
                    "className": "align-middle",
                    "targets": "_all"
                },
                // {
                //     targets: '_all',
                //     className: 'text-nowrap',
                //     render: (data, type, row, meta) => {
                //         if (row.stat == 'isi') {
                //             color = 'green';
                //         } else {
                //             color = '#blue';
                //         }
                //         return '<span style="color:' + color + '">' + data +
                //             '</span>';
                //     }
                // },

            ],
            rowsGroup: [
                0, 11
            ]
        });


        function dataTableHapusReload() {
            datatable_hapus.ajax.reload();
        }

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
                url: '{{ route('list_data_no_carton') }}',
                data: function(d) {
                    d.po = $('#txtmodal_h_po').val();
                    d.no_carton = $('#cbomodal_h_no_karton').val();
                },
            },
            columns: [{
                    data: 'id'

                },
                {
                    data: 'barcode'

                }, {
                    data: 'color'
                },
                {
                    data: 'size'
                },
                {
                    data: 'dest'
                },
            ],
            columnDefs: [{
                    "className": "dt-left",
                    "targets": "_all"
                },
                {
                    targets: [0],
                    render: (data, type, row, meta) => {
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
                },

            ]
        });


        function dataTableHapusKartonReload() {
            datatable_hapus_karton.ajax.reload();
        }

        let datatable_hapus_karton = $("#datatable_hapus_karton").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            paging: false,
            searching: true,
            scrollY: '300px',
            scrollX: '300px',
            scrollCollapse: true,
            ajax: {
                url: '{{ route('list_data_no_carton_hapus') }}',
                data: function(d) {
                    d.po = $('#txtmodal_h_po_karton').val();
                },
            },
            columns: [{
                    data: 'id'

                },
                {
                    data: 'po'

                }, {
                    data: 'no_carton'
                },
                {
                    data: 'notes'
                },
                {
                    data: 'qty_isi'
                },
                {
                    data: 'tot_out'
                },
            ],
            columnDefs: [{
                    "className": "dt-left",
                    "targets": "_all"
                },
                {
                    targets: [0],
                    render: (data, type, row, meta) => {
                        if (row.tot_out >= '1') {
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

            ]
        });



        function dataTableTambahReload() {
            datatable_tambah.ajax.reload();
        }

        let datatable_tambah = $("#datatable_tambah").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            paging: false,
            searching: true,
            scrollY: '300px',
            scrollX: '300px',
            scrollCollapse: true,
            ajax: {
                url: '{{ route('list_data_no_carton_tambah') }}',
                data: function(d) {
                    d.po = $('#txtmodal_p_po').val();
                    d.no_carton = $('#cbomodal_p_no_karton').val();
                },
            },
            columns: [{
                    data: 'po'

                },
                {
                    data: 'barcode'

                }, {
                    data: 'color'
                },
                {
                    data: 'size'
                },
                {
                    data: 'dest'
                },
                {
                    data: 'ws'
                },
                {
                    data: 'tot'
                },
            ],
            columnDefs: [{
                "className": "dt-left",
                "targets": "_all"
            }, ]
        });


        function show_data_edit_h(po_h, tot_k_h) {
            dataTableHapusReload();
            $('#txtmodal_h_po').val(po_h).trigger("change");
            $('#txtmodal_h_po_karton').val(po_h);
            dataTableHapusKartonReload();
            $('#cbomodal_h_no_karton').val('').trigger("change");
            $('#txtmodal_p_po').val(po_h).trigger("change");
            $('#cbomodal_p_no_karton').val('').trigger("change");
            // $('#cbomodal_p_dest').val('').trigger("change");
            $('#cbomodal_p_qty_stok').val('0');
            $('#cbomodal_p_qty').val('');
            $('#txtmodal_s_no_carton').val(tot_k_h);
            $('#txtmodal_s_po').val(po_h);
            dataTableTambahReload();
        }

        function getno_carton_modal_hapus() {
            let txtmodal_h_po = document.form_h.txtmodal_h_po.value;
            let html = $.ajax({
                type: "GET",
                url: '{{ route('getno_carton_hapus') }}',
                data: {
                    txtmodal_h_po: txtmodal_h_po
                },
                async: false
            }).responseText;
            if (html != "") {
                $("#cbomodal_h_no_karton").html(html);
            }
        };

        function getno_carton_modal_tambah() {
            let txtmodal_p_po = document.form_p.txtmodal_p_po.value;
            let html = $.ajax({
                type: "GET",
                url: '{{ route('getno_carton_tambah') }}',
                data: {
                    txtmodal_p_po: txtmodal_p_po
                },
                async: false
            }).responseText;
            if (html != "") {
                $("#cbomodal_p_no_karton").html(html);
            }
        };

        function getbarcode_modal_tambah() {
            let txtmodal_p_po = document.form_p.txtmodal_p_po.value;
            let html = $.ajax({
                type: "GET",
                url: '{{ route('getbarcode_tambah') }}',
                data: {
                    txtmodal_p_po: txtmodal_p_po
                },
                async: false
            }).responseText;
            if (html != "") {
                $("#cbomodal_p_barcode").html(html);
            }
        };

        function getstok_packing_in() {
            let txtmodal_p_po = document.form_p.txtmodal_p_po.value;
            let cbomodal_p_id_ppic_master_so = document.form_p.cbomodal_p_id_ppic_master_so.value;
            jQuery.ajax({
                url: '{{ route('get_data_stok_packing_in') }}',
                method: 'GET',
                data: {
                    po: txtmodal_p_po,
                    id_ppic_master_so: id_ppic_master_so
                },
                dataType: 'json',
                success: async function(response) {
                    document.getElementById('cbomodal_p_qty_stok').value = response.tot_s;
                },
                error: function(request, status, error) {
                    alert(request.responseText);
                },
            });
        }


        function simpan_modal_tambah() {
            let txtmodal_p_po = document.form_p.txtmodal_p_po.value;
            let cbomodal_p_barcode = document.form_p.cbomodal_p_barcode.value;
            let cbomodal_p_dest = document.form_p.cbomodal_p_dest.value;
            let cbomodal_p_qty = document.form_p.cbomodal_p_qty.value;
            let cbomodal_p_no_karton = document.form_p.cbomodal_p_no_karton.value;
            let cbomodal_p_qty_stok = document.form_p.cbomodal_p_qty_stok.value;

            if (cbomodal_p_barcode == '') {
                iziToast.warning({
                    message: 'Barcode masih kosong, Silahkan pilih barcode',
                    position: 'topCenter'
                });
            }
            if (cbomodal_p_dest == '') {
                iziToast.warning({
                    message: 'Dest masih kosong, Silahkan pilih dest',
                    position: 'topCenter'
                });
            }

            if (cbomodal_p_qty == '') {
                iziToast.warning({
                    message: 'Qty masih kosong, Silahkan isi qty',
                    position: 'topCenter'
                });
            }

            if (cbomodal_p_no_karton == '') {
                iziToast.warning({
                    message: 'No Carton masih kosong, Silahkan pilih no carton',
                    position: 'topCenter'
                });
            }

            if (cbomodal_p_barcode != '' && cbomodal_p_qty != '' && cbomodal_p_dest != '') {
                $.ajax({
                    type: "post",
                    url: '{{ route('store_tambah_data_karton_det') }}',
                    data: {
                        txtmodal_p_po: txtmodal_p_po,
                        cbomodal_p_barcode: cbomodal_p_barcode,
                        cbomodal_p_qty: cbomodal_p_qty,
                        cbomodal_p_dest: cbomodal_p_dest,
                        cbomodal_p_no_karton: cbomodal_p_no_karton,
                        cbomodal_p_qty_stok: cbomodal_p_qty_stok
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
                        $("#txtmodal_p_po").val(txtmodal_p_po).trigger('change');
                        // $("#cbomodal_p_no_karton").val(cbomodal_p_no_karton).trigger('change');
                        // $("#cbomodal_p_dest").val(cbomodal_p_dest).trigger('change');
                        $('#cbomodal_p_qty').val('');
                        $('#cbomodal_p_qty_stok').val('0');
                        dataTableTambahReload();
                        // dataTableReload();
                        // cleardet();
                    },
                    error: function(request, status, error) {
                        iziToast.warning({
                            message: 'Stok tidak mencukupi cek lagi',
                            position: 'topCenter'
                        });
                    },
                });
            }
        };


        function ceklis(checkeds) {
            //get id..and check if checked
            console.log($(checkeds).attr("value"), checkeds.checked)
        }


        function export_excel_packing_master_karton() {
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
                url: '{{ route('export_excel_packing_master_carton') }}',
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
                        link.download = "Laporan Master Karton " + from + " sampai " +
                            to + ".xlsx";
                        link.click();

                    }
                },
            });
        }

        function show_data_upload(po_u, qty_po_u) {
            // dataTableUploadReload();
            $('#modal_upload_po').val(po_u);
            $('#modal_qty_po').val(qty_po_u);
            document.getElementById('file').value = "";
            delete_tmp_upload();
            dataTableUploadReload();
        }


        function delete_tmp_upload() {
            let po = $('#modal_upload_po').val();
            let html = $.ajax({
                type: "POST",
                url: '{{ route('delete_upload_po_karton') }}',
                data: {
                    po: po
                },
                async: false
            }).responseText;
        }


        function dataTableUploadReload() {
            datatable_upload.ajax.reload();
        }

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
                    .column(1)
                    .data()
                    .reduce(function(a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                // Update footer by showing the total with the reference of the column index
                $(api.column(0).footer()).html('Total');
                $(api.column(1).footer()).html(sumTotal);
            },

            ordering: false,
            processing: true,
            serverSide: true,
            paging: false,
            searching: true,
            scrollY: '300px',
            scrollX: '300px',
            scrollCollapse: true,
            info: true,
            ajax: {
                url: '{{ route('show_data_upload_karton') }}',
                method: 'GET',
                data: function(d) {
                    d.po = $('#modal_upload_po').val();
                },
            },
            columns: [{
                    data: 'no_carton'

                },
                {
                    data: 'qty_isi'
                },
            ],
            columnDefs: [{
                "className": "dt-left",
                "targets": "_all"
            }, ]
        });

        function export_data_po() {
            let po = $('#modal_upload_po').val();
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
                url: '{{ route('export_data_po_upload') }}',
                data: {
                    po: po
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
                        link.download = "PO " + po + ".xlsx";
                        link.click();

                    }
                },
            });
        }

        function simpan() {
            let po = $('#modal_upload_po').val();
            $.ajax({
                type: "post",
                url: '{{ route('store_upload_qty_karton') }}',
                data: {
                    po: po
                },
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
                        dataTableUploadReload();
                    }

                },
                error: function(request, status, error) {
                    iziToast.warning({
                        message: 'Silahkan cek lagi',
                        position: 'topCenter'
                    });
                    dataTableUploadReload();
                },
            });

        };
    </script>
@endsection
