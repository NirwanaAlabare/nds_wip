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
<form action="{{ route('update-inmaterial-fabric') }}" method="post" id="store-inmaterial" onsubmit="submitForm(this, event)">
    @method('GET')
    @csrf
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold">
                Data Header
            </h5>
            <div class="card-tools">
              <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
            </div>
        </div>
@foreach ($kode_gr as $kodegr)
    <div class="card-body">
    <div class="form-group row">
    <div class="col-md-4">
        <div class="row">
            <div class="col-md-12">
            <div class="mb-1">
                <div class="form-group">
                <label><small>No BPB</small></label>
                <input type="text" class="form-control " id="txt_gr_dok" name="txt_gr_dok" value="{{ $kodegr->no_dok }}" readonly>
                <input type="hidden" class="form-control " id="txt_idgr" name="txt_idgr" value="{{ $kodegr->id }}" readonly>
                </div>
            </div>
            </div>

            <div class="col-md-6">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Tgl BPB</small></label>
                <input type="date" class="form-control form-control" id="txt_tgl_gr" name="txt_tgl_gr"
                        value="{{ $kodegr->tgl_dok }}">
                </div>
            </div>
            </div>

            <div class="col-md-6">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Tgl Pengiriman</small></label>
                <input type="date" class="form-control form-control" id="txt_tgl_ship" name="txt_tgl_ship"
                        value="{{ $kodegr->tgl_shipp }}">
                </div>
            </div>
            </div>

            <div class="col-md-12">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Supplier</small></label>
                <select class="form-control select2supp" id="txt_supp" name="txt_supp" style="width: 100%;" onchange="settype()" disabled>
                    <option selected="selected" value="{{$kodegr->supplier}}">{{$kodegr->supplier}}</option>
                        @foreach ($msupplier as $msupp)
                    <option value="{{ $msupp->Supplier }}">
                                {{ $msupp->Supplier }}
                    </option>
                        @endforeach
                </select>
                </div>
            </div>
            </div>

            <div class="col-md-12">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Tipe BPB</small></label>
                <select class="form-control select2bs4" id="txt_type_gr" name="txt_type_gr" style="width: 100%;" onchange="settype()" disabled>
                    <option selected="selected" value="{{$kodegr->type_dok}}">{{$kodegr->type_dok}}</option>
                        @foreach ($gr_type as $grtype)
                    <option value="{{ $grtype->nama_pilihan }}">
                                {{ $grtype->nama_pilihan }}
                    </option>
                        @endforeach
                </select>
                </div>
            </div>
            </div>

            <div class="col-md-12">
            <div class="mb-1">
                <div class="form-group">
                <label><small>No PO</small></label>
                @if ($kodegr->type_dok == "CMT")
                <select class="form-control select2bs4" id="txt_po" name="txt_po" style="width: 100%;"  disabled></select>
                @endif
                @if ($kodegr->type_dok == "FOB")
                <select class="form-control select2bs4" id="txt_po" name="txt_po" style="width: 100%;" disabled>
                    <option selected="selected" value="{{$kodegr->no_po}}">{{$kodegr->no_po}}</option>
                </select>
                @endif
                </div>
            </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="row">

            <div class="col-md-12">
            <div class="mb-1">
                <div class="form-group">
                <label><small>WS (Global)</small></label>
                @if ($kodegr->type_dok == "CMT")
                <select class="form-control select2bs4" id="txt_wsglobal" name="txt_wsglobal" style="width: 100%;" disabled>
                    <option selected="selected" value="{{$kodegr->no_ws}}">{{$kodegr->no_ws}}</option>
                </select>
                @endif
                @if ($kodegr->type_dok == "FOB")
                <select class="form-control select2bs4" id="txt_wsglobal" name="txt_wsglobal" style="width: 100%;"  disabled>
                </select>
                @endif
                </div>
            </div>
            </div>

            <div class="col-md-12">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Tipe BC</small></label>
                <select class="form-control select2bs4" id="txt_type_bc" name="txt_type_bc" style="width: 100%;">
                    @if ($kodegr->type_bc == "")
                    <option selected="selected" value="">Pilih Tipe</option>
                    @endif
                    @if ($kodegr->type_bc != "")
                    <option selected="selected" value="{{$kodegr->type_bc}}">{{$kodegr->type_bc}}</option>
                    @endif
                        @foreach ($mtypebc as $bc)
                    <option value="{{ $bc->nama_pilihan }}">
                                {{ $bc->nama_pilihan }}
                    </option>
                        @endforeach
                </select>
                </div>
            </div>
            </div>

            <div class="col-md-12">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Tipe Pembelian</small></label>
                <select class="form-control select2bs4" id="txt_type_pch" name="txt_type_pch" style="width: 100%;">
                    @if ($kodegr->type_pch == "")
                    <option selected="selected" value="">Pilih Tipe</option>
                    @endif
                    @if ($kodegr->type_pch != "")
                    <option selected="selected" value="{{$kodegr->type_pch}}">{{$kodegr->type_pch}}</option>
                    @endif
                        @foreach ($pch_type as $pch)
                    <option value="{{ $pch->nama_pilihan }}">
                                {{ $pch->nama_pilihan }}
                    </option>
                        @endforeach
                </select>
                </div>
            </div>
            </div>

            <div class="col-md-12">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Dokumen Asli</small></label>
                <select class="form-control select2bs4" id="txt_oridok" name="txt_oridok" style="width: 100%;">
                </select>
                </div>
            </div>
            </div>

            <div class="col-md-12">
            <div class="mb-1">
                <div class="form-group">
                <label><small>No Invoice</small></label>
                <input type="text" class="form-control " id="txt_invdok" name="txt_invdok" value="{{$kodegr->no_invoice}}" >
                </div>
            </div>
            </div>
        </div>
    </div>

    <div class="col-md-5">
        <div class="row">

            <div class="col-md-7">
            <div class="mb-1">
                <div class="form-group">
                <label><small>No Aju</small></label>
                <input type="text" class="form-control " id="txt_aju_num" name="txt_aju_num" value="{{$kodegr->no_aju}}" disabled>
                </div>
            </div>
            </div>

            <div class="col-md-5">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Tgl Aju</small></label>
                <input type="date" class="form-control form-control" id="txt_tgl_aju" name="txt_tgl_aju"
                        value="{{$kodegr->tgl_aju}}" disabled>
                </div>
            </div>
            </div>

            <div class="col-md-7">
            <div class="mb-1">
                <div class="form-group">
                <label><small>No Daftar</small></label>
                <input type="text" class="form-control " id="txt_reg_num" name="txt_reg_num" value="{{$kodegr->no_daftar}}" disabled >
                </div>
            </div>
            </div>

            <div class="col-md-5">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Tgl Daftar</small></label>
                <input type="date" class="form-control form-control" id="txt_reg_aju" name="txt_reg_aju"
                        value="{{$kodegr->tgl_daftar}}" disabled>
                </div>
            </div>
            </div>

            <div class="col-md-7">
            <div class="mb-1">
                <div class="form-group">
                <label><small>No Kontrak</small></label>
                <input type="text" class="form-control " id="txt_kontrak" name="txt_kontrak" value="{{$kodegr->no_kontrak}}" disabled >
                </div>
            </div>
            </div>

            <div class="col-md-5">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Jenis Bahan Baku</small></label>
<!--            <select class="form-control select2bs4" id="txt_tom" name="txt_tom" style="width: 100%;"></select> -->                      <input type="text" class="form-control " id="txt_tom" name="txt_tom" value="Fabric" readonly>
               </div>
            </div>
            </div>

            <div class="col-md-12">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Catatan</small></label>
                <textarea type="text" rows="4" class="form-control " id="txt_notes" name="txt_notes" value="{{$kodegr->deskripsi}}" > </textarea>
                <input type="hidden" class="form-control" id="jumlah_data" name="jumlah_data" readonly>
                <input type="hidden" class="form-control" id="jumlah_qty" name="jumlah_qty" readonly>
                </div>
            </div>
            </div>
        </div>
    </div>
    </div>
</div>
</div>
@endforeach

@foreach ($jml_det as $jmldet)
<input type="hidden" class="form-control " id="txt_jmldet" name="txt_jmldet" value="{{$jmldet->jml_dok}}" readonly>
@endforeach

    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold">
                Data Detail
            </h5>
        </div>
    <div class="card-body">
    <div class="form-group row">
        <div class="d-flex justify-content-between">
            <div class="ml-auto">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
            </div>
                <input type="text"  id="cari_item" name="cari_item" autocomplete="off" placeholder="Search Item..." onkeyup="cariitem()">
        </div>
    <div class="table-responsive"style="max-height: 500px">
            <table id="datatable" class="table table-bordered table-striped w-100 text-nowrap">
                <thead>
                    <tr>
                        <th class="text-center" style="font-size: 0.6rem;width: 300px;">NO WS</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 300px;">ID JO</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 300px;">ID Barang</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 300px;">Kode Barang</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 300px;">Produk</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 300px;">Deskripsi</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 300px;">Qty PO</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 300px;">PO Unit</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 300px;">Balance</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 300px;">Qty GR</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 300px;">GR Unit</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 300px;">Qty Reject</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 300px;">Reject Unit</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i=1; ?>
                    @foreach ($det_data as $detdata)
                    <tr>
                        <td value="{{$detdata->no_ws}}">{{$detdata->no_ws}}</td>
                        <td value="{{$detdata->no_ws}}">{{$detdata->id_jo}}</td>
                        <td value="{{$detdata->no_ws}}">{{$detdata->id_item}}</td>
                        <td value="{{$detdata->no_ws}}">{{$detdata->kode_item}}</td>
                        <td value="{{$detdata->no_ws}}">{{$detdata->produk_item}}</td>
                        <td value="{{$detdata->no_ws}}">{{$detdata->desc_item}}</td>
                        <td value="{{$detdata->no_ws}}">{{$detdata->qty_po}}</td>
                        <td value="{{$detdata->no_ws}}">{{$detdata->unit}}</td>
                        <td value="{{$detdata->no_ws}}">{{$detdata->qty_po}}</td>
                        <td value="{{$detdata->no_ws}}"><input style="width:100px;" class="form-control-sm" type="text" min="0" max="{{$detdata->qty_po}}" id="qty_good<?= $i; ?>" name="qty_good[<?= $i; ?>]" value="{{$detdata->qty_good}}" /></td>
                        <td value="{{$detdata->no_ws}}" onkeyup="tambahqty(this.value)">{{$detdata->unit}}</td>
                        <td value="{{$detdata->no_ws}}"><input style="width:100px;" class="form-control-sm" type="text" min="0" max="{{$detdata->qty_po}}" id="qty_reject<?= $i; ?>" name="qty_reject[<?= $i; ?>]" value="{{$detdata->qty_reject}}" /></td>
                        <td value="{{$detdata->no_ws}}">{{$detdata->unit}}</td>
                        <td hidden><input style="width:100px;" class="form-control-sm" type="text" id="id_det<?= $i; ?>" name="id_det[<?= $i; ?>]" value="{{$detdata->id}}" /></td>
                    </tr>
                    <?php $i++; ?>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
            <div class="mb-1">
                <div class="form-group">
                    <button class="btn btn-sb float-end mt-2 ml-2"><i class="fa-solid fa-floppy-disk"></i> Save</button>
                    <a href="{{ route('in-material') }}" class="btn btn-danger float-end mt-2">
                    <i class="fas fa-arrow-circle-left"></i> Back</a>
                </div>
            </div>
        </div>
        </div>
    </div>
</form>

<div class="modal fade" id="modal-add-lokasi">
    <form action="{{ route('save-lokasi') }}" method="post" onsubmit="submitForm(this, event)">
         @method('POST')
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-sb text-light">
                    <h4 class="modal-title">Add Location</h4>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <div class="modal-body">
                <div class="form-group row">

                    <div class="col-md-7">
                    <div class="row">
                        <div class="col-md-6">
                        <div class="mb-1">
                        <div class="form-group">
                            <label><small>GR Document</small></label>
                                <input type="text" class="form-control " id="m_gr_dok" name="m_gr_dok" value="" readonly>
                        </div>
                        </div>
                        </div>
                        <div class="col-md-6">
                        <div class="mb-1">
                        <div class="form-group">
                            <label><small>WS Number</small></label>
                                <input type="text" class="form-control " id="m_no_ws" name="m_no_ws" value="" readonly>
                        </div>
                        </div>
                        </div>
                        <div class="col-md-6">
                        <div class="mb-1">
                        <div class="form-group">
                            <label><small>Item Code</small></label>
                                <input type="text" class="form-control " id="m_kode_item" name="m_kode_item" value="" readonly>
                        </div>
                        </div>
                        </div>
                        <div class="col-md-6">
                        <div class="mb-1">
                        <div class="form-group">
                            <label><small>Quantity</small></label>
                                <input type="text" class="form-control" id="m_qty" name="m_qty" value="" readonly>
                        </div>
                        </div>
                        </div>
                        <div class="col-md-12">
                        <div class="mb-1">
                        <div class="form-group">
                            <label><small>Description</small></label>
                                <input type="text" class="form-control " id="m_desc" name="m_desc" value="" readonly>
                        </div>
                        </div>
                        </div>
                    </div>
                    </div>

                    <div class="col-md-5">
                    <div class="row">
                        <div class="col-md-6">
                        <div class="mb-1">
                        <div class="form-group">
                            <label><small>Balance</small></label>
                                <input type="text" class="form-control" id="m_balance" name="m_balance" value="" readonly>
                        </div>
                        </div>
                        </div>
                        <div class="col-md-6">
                        <div class="mb-1">
                        <div class="form-group">
                            <label><small>Unit Detail</small></label>
                                <input type="text" class="form-control " id="m_unit" name="m_unit" value="" readonly>
                        </div>
                        </div>
                        </div>
                        <div class="col-md-6">
                        <div class="mb-1">
                        <div class="form-group">
                            <label><small>Quantity Detail</small></label>
                                <input type="text" class="form-control " style="text-align:right;" id="m_qty_det" name="m_qty_det" value="" onkeyup="getlist_addlokasi()">
                        </div>
                        </div>
                        </div>
                        <div class="col-md-6">
                        <div class="mb-1">
                        <div class="form-group">
                            <label><small>Lot</small></label>
                                <input type="text" class="form-control " id="m_lot" name="m_lot" value="">
                                <input type="hidden" id="m_idjo" name="m_idjo" value="">
                                <input type="hidden" id="m_iditem" name="m_iditem" value="">
                        </div>
                        </div>
                        </div>
                        <div class="col-md-12">
                        <div class="mb-1">
                        <div class="form-group">
                            <label><small>Location</small></label>
                                <select class="form-control select2supp" id="m_location" name="m_location" style="width: 100%;" onchange="getlist_addlokasi()">
                                    <option selected="selected" value="">Select Location</option>
                                        @foreach ($lokasi as $lok)
                                    <option value="{{ $lok->kode_lok }}">{{ $lok->kode_lok }}</option>
                                        @endforeach
                                </select>
                        </div>
                        </div>
                        </div>
                    </div>
                    </div>

                    <div class="col-md-12">
                    <div class="row">
                        <div class="col-md-12" id="detail_addlok">
                        </div>
                    </div>
                    </div>

                </div>
            </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="fa fa-window-close" aria-hidden="true"></i> Close</button>
                    <button type="submit" class="btn btn-primary toastsDefaultDanger"><i class="fa fa-thumbs-up" aria-hidden="true"></i> Save</button>
                </div>
            </div>
        </div>
    </form>
</div>


<div class="modal fade" id="modal-show-lokasi">
    <form method="post" onsubmit="submitForm(this, event)">
         @method('POST')
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-sb text-light">
                    <h4 class="modal-title">Location Info</h4>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <div class="modal-body">
                <div class="form-group row">

                    <div class="col-md-7">
                    <div class="row">
                        <div class="col-md-6">
                        <div class="mb-1">
                        <div class="form-group">
                            <label><small>GR Document</small></label>
                                <input type="text" class="form-control " id="m_gr_dok2" name="m_gr_dok2" value="" readonly>
                        </div>
                        </div>
                        </div>
                        <div class="col-md-6">
                        <div class="mb-1">
                        <div class="form-group">
                            <label><small>WS Number</small></label>
                                <input type="text" class="form-control " id="m_no_ws2" name="m_no_ws2" value="" readonly>
                        </div>
                        </div>
                        </div>
                        <div class="col-md-6">
                        <div class="mb-1">
                        <div class="form-group">
                            <label><small>Item Code</small></label>
                                <input type="text" class="form-control " id="m_kode_item2" name="m_kode_item2" value="" readonly>
                        </div>
                        </div>
                        </div>
                        <div class="col-md-6">
                        <div class="mb-1">
                        <div class="form-group">
                            <label><small>Quantity</small></label>
                                <input type="text" class="form-control" id="m_qty2" name="m_qty2" value="" readonly>
                        </div>
                        </div>
                        </div>
                    </div>
                    </div>

                    <div class="col-md-5">
                    <div class="row">
                        <div class="col-md-6">
                        <div class="mb-1">
                        <div class="form-group">
                            <label><small>Balance</small></label>
                                <input type="text" class="form-control" id="m_balance2" name="m_balance2" value="" readonly>
                        </div>
                        </div>
                        </div>
                        <div class="col-md-6">
                        <div class="mb-1">
                        <div class="form-group">
                            <label><small>Unit Detail</small></label>
                                <input type="text" class="form-control " id="m_unit2" name="m_unit2" value="" readonly>
                        </div>
                        </div>
                        </div>
                        <div class="col-md-12">
                        <div class="mb-1">
                        <div class="form-group">
                            <label><small>Description</small></label>
                                <input type="text" class="form-control " id="m_desc2" name="m_desc2" value="" readonly>
                        </div>
                        </div>
                        </div>
                    </div>
                    </div>

                    <div class="col-md-12">
                    <div class="row">
                        <div class="col-md-12" id="detail_showlok">
                        </div>
                    </div>
                    </div>

                </div>
            </div>

            </div>
        </div>
    </form>
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
    <!-- Page specific script -->
    <script>

        $(document).on('select2:open', () => {
            document.querySelector('.select2-search__field').focus();
        });

        //Initialize Select2 Elements
        $('.select2').select2()

        //Initialize Select2 Elements
        $('.select2bs4').select2({
            theme: 'bootstrap4'
        });

        $('.select2roll').select2({
            theme: 'bootstrap4'
        });

        $('.select2supp').select2({
            theme: 'bootstrap4'
        });

        $("#color").prop("disabled", true);
        $("#panel").prop("disabled", true);
        $('#p_unit').val("yard").trigger('change');

        //Reset Form
        if (document.getElementById('store-inmaterial')) {
            document.getElementById('store-inmaterial').reset();
        }

        $('#ws_id').on('change', async function(e) {
            await updateColorList();
            await updateOrderInfo();
        });

        $('#color').on('change', async function(e) {
            await updatePanelList();
            await updateSizeList();
        });

        $('#panel').on('change', async function(e) {
            await getMarkerCount();
            await getNumber();
            await updateSizeList();
        });

        $('#p_unit').on('change', async function(e) {
            let unit = $('#p_unit').val();
            if (unit == 'yard') {
                $('#comma_unit').val('INCH');
                $('#l_unit').val('inch').trigger("change");
            } else if (unit == 'meter') {
                $('#comma_unit').val('CM');
                $('#l_unit').val('cm').trigger("change");
            }
        });

        function tambahqty($val){
            var table = document.getElementById("datatable");
            var qty = 0;
            var jml_qty = 0;

            for (var i = 1; i < (table.rows.length); i++) {
                qty = document.getElementById("datatable").rows[i].cells[9].children[0].value || 0;
                jml_qty += parseFloat(qty) ;
            }

            $('#jumlah_qty').val(jml_qty);

        }

        function updateOrderInfo() {
            return $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route("get-marker-order") }}',
                type: 'get',
                data: {
                    act_costing_id: $('#ws_id').val(),
                    color: $('#color').val(),
                },
                dataType: 'json',
                success: function (res) {
                    if (res) {
                        document.getElementById('ws').value = res.kpno;
                        document.getElementById('buyer').value = res.buyer;
                        document.getElementById('style').value = res.styleno;
                    }
                },
            });
        }

        function updateColorList() {
            document.getElementById('color').value = null;
            return $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route("get-marker-colors") }}',
                type: 'get',
                data: {
                    act_costing_id: $('#ws_id').val(),
                },
                success: function (res) {
                    if (res) {
                        document.getElementById('color').innerHTML = res;
                        document.getElementById('panel').innerHTML = null;
                        document.getElementById('panel').value = null;

                        $("#color").prop("disabled", false);
                        $("#panel").prop("disabled", true);

                        // input text
                        document.getElementById('no_urut_marker').value = null;
                        document.getElementById('cons_ws').value = null;
                        document.getElementById('order_qty').value = null;
                    }
                },
            });
        }

        function updatePanelList() {
            document.getElementById('panel').value = null;
            return $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route("get-general-panels") }}',
                type: 'get',
                data: {
                    act_costing_id: $('#ws_id').val(),
                    color: $('#color').val(),
                },
                success: function (res) {
                    if (res) {
                        document.getElementById('panel').innerHTML = res;
                        $("#panel").prop("disabled", false);

                        // input text
                        document.getElementById('no_urut_marker').value = null;
                        document.getElementById('cons_ws').value = null;
                        document.getElementById('order_qty').value = null;
                    }
                },
            });
        }


        function settype(){
            let type = $('#txt_type_gr').val();
            $("#txt_wsglobal").prop("disabled", false);
            $("#txt_po").prop("disabled", false);
            if (type == 'FOB') {

                $("#txt_wsglobal").prop("disabled", true);
                $("#txt_wsglobal").val('');
                $("#txt_wsglobal").text('');
                return $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route("get-po-list") }}',
                type: 'get',
                data: {
                    txt_supp: $('#txt_supp').val(),
                },
                success: function (res) {
                    if (res) {
                        document.getElementById('txt_po').innerHTML = res;
                    }
                },
            });

            }else if(type == 'CMT'){
                $("#txt_po").prop("disabled", true);
                $("#txt_po").val('');
                $("#txt_po").text('');

                return $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route("get-ws-list") }}',
                type: 'get',
                data: {
                    txt_supp: $('#txt_supp').val(),
                },
                success: function (res) {
                    if (res) {
                        document.getElementById('txt_wsglobal').innerHTML = res;
                    }
                },
            });
            }else{
            }
        }


        // function getlistdata(val){
        //     datatable.ajax.reload();
        // }

        async function getlistdata() {
            return datatable.ajax.reload(() => {
                document.getElementById('jumlah_data').value = datatable.data().count();
            });
        }

        function getMarkerCount() {
            document.getElementById('no_urut_marker').value = "";
            return $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route("get-marker-count") }}',
                type: 'get',
                data: {
                    act_costing_id: $('#ws_id').val(),
                    color: $('#color').val(),
                    panel: $('#panel').val()
                },
                success: function (res) {
                    if (res) {
                        document.getElementById('no_urut_marker').value = res;
                    }
                }
            });
        }

        function getNumber() {
            document.getElementById('cons_ws').value = null;
            document.getElementById('order_qty').value = null;
            return $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: ' {{ route("get-marker-number") }}',
                type: 'get',
                dataType: 'json',
                data: {
                    act_costing_id: $('#ws_id').val(),
                    color: $('#color').val(),
                    panel: $('#panel').val()
                },
                success: function (res) {
                    if (res) {
                        document.getElementById('cons_ws').value = res.cons_ws;
                        document.getElementById('order_qty').value = res.order_qty;
                    }
                }
            });

        }

        function calculateRatio(id) {
            let ratio = document.getElementById('ratio-'+id).value;
            let gelarQty = document.getElementById('gelar_marker_qty').value;
            document.getElementById('cut-qty-'+id).value = ratio * gelarQty;
        }

        function calculateAllRatio(element) {
            let gelarQty = element.value;

            for (let i = 0; i < datatable.data().count(); i++) {
                let ratio = document.getElementById('ratio-'+i).value;
                document.getElementById('cut-qty-'+i).value = ratio * gelarQty;
            }
        }

        // document.getElementById("store-marker").onkeypress = function(e) {
        //     var key = e.charCode || e.keyCode || 0;
        //     if (key == 13) {
        //         e.preventDefault();
        //     }
        // }

        function submitLokasiForm(e, evt) {
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
                            title: 'Data Spreading berhasil disimpan',
                            html: "No. Form Cut : <br>" + res.message,
                            showCancelButton: false,
                            showConfirmButton: true,
                            confirmButtonText: 'Oke',
                            timer: 5000,
                            timerProgressBar: true
                        })

                        datatable.ajax.reload();
                    }
                },

            });
        }

        function cariitem() {
        // Declare variables
        var input, filter, table, tr, td, i, txtValue;
        input = document.getElementById("cari_item");
        filter = input.value.toUpperCase();
        table = document.getElementById("datatable");
        tr = table.getElementsByTagName("tr");

        // Loop through all table rows, and hide those who don't match the search query
        for (i = 0; i < tr.length; i++) {
            td = tr[i].getElementsByTagName("td")[5]; //kolom ke berapa
            if (td) {
                txtValue = td.textContent || td.innerText;
                if (txtValue.toUpperCase().indexOf(filter) > -1) {
                    tr[i].style.display = "";
                } else {
                    tr[i].style.display = "none";
                }
            }
        }
    }
    </script>
    <script type="text/javascript">
    function addlocation($ws,$id_jo, $id_item,$kode_item, $qty, $unit, $balance,$desc){
        let ws = $ws;
        let id_jo = $id_jo;
        let id_item = $id_item;
        let kode_item = $kode_item;
        let qty = $qty;
        let unit = $unit;
        let balance = $balance;
        let desc = $desc;
        let no_dok = $('#txt_gr_dok').val();
        // alert(id_item);
    $('#m_gr_dok').val(no_dok);
    $('#m_no_ws').val(ws);
    $('#m_kode_item').val(kode_item);
    $('#m_qty').val(qty);
    $('#m_desc').val(desc);
    $('#m_balance').val(balance);
    $('#m_unit').val(unit);
    $('#m_idjo').val(id_jo);
    $('#m_iditem').val(id_item);
    $('#modal-add-lokasi').modal('show');
    }

    function getlist_addlokasi(){
        let lokasi = $('#m_location').val();
        return $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route("get-detail-addlok") }}',
                type: 'get',
                data: {
                    lokasi: $('#m_location').val(),
                    jml_baris: $('#m_qty_det').val(),
                    lot: $('#m_lot').val()
                },
                success: function (res) {
                    if (res) {
                        document.getElementById('detail_addlok').innerHTML = res;
                        $('.select2lok').select2({
                            theme: 'bootstrap4'
                        });
                    }
                }
            });
    }


    function showlocation($ws,$id_jo, $id_item,$kode_item, $qty, $unit, $balance,$desc){
        let ws = $ws;
        let id_jo = $id_jo;
        let id_item = $id_item;
        let kode_item = $kode_item;
        let qty = $qty;
        let unit = $unit;
        let balance = $balance;
        let desc = $desc;
        let no_dok = $('#txt_gr_dok').val();
        // alert(id_item);
    $('#m_gr_dok2').val(no_dok);
    $('#m_no_ws2').val(ws);
    $('#m_kode_item2').val(kode_item);
    $('#m_qty2').val(qty);
    $('#m_desc2').val(desc);
    $('#m_balance2').val(balance);
    $('#m_unit2').val(unit);
    $('#m_idjo2').val(id_jo);
    $('#m_iditem2').val(id_item);
    $('#modal-show-lokasi').modal('show');
    }

    function getlist_showlokasi($ws,$id_jo, $id_item){
        let ws = $ws;
        let id_jo = $id_jo;
        let id_item = $id_item;
        return $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route("get-detail-showlok") }}',
                type: 'get',
                data: {
                    no_dok: $('#txt_gr_dok').val(),
                    no_ws: ws,
                    id_jo: id_jo,
                    id_item: id_item
                },
                success: function (res) {
                    if (res) {
                        document.getElementById('detail_showlok').innerHTML = res;
                        $('#tableshow').dataTable({
                            "bFilter": false,
                        });
                    }
                }
            });
    }
</script>
@endsection
