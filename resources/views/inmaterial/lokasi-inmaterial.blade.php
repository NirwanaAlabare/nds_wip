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
<style>
/* Highlight hanya untuk tabel di dalam modal */
.modal-body #tableshow td.drag-highlight {
  background-color: rgba(13, 110, 253, 0.15); /* biru lembut */
  outline: 2px solid #0d6efd; /* garis luar, tidak ubah layout */
  outline-offset: -1px;
}

/* Cursor hanya aktif di tabel dalam modal */
.modal-body #tableshow td.editable {
  cursor: cell;
}
</style>

<form action="{{ route('store-inmaterial-fabric') }}" method="post" id="store-inmaterial" onsubmit="submitForm(this, event)">
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
                                <select class="form-control select2bs4" id="txt_supp" name="txt_supp" style="width: 100%;" onchange="settype()">
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
                                <select class="form-control select2bs4" id="txt_type_gr" name="txt_type_gr" style="width: 100%;" onchange="settype()">
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
                                <select class="form-control select2bs4" id="txt_po" name="txt_po" style="width: 100%;" >
                                    <option selected="selected" value="{{$kodegr->no_po}}">{{$kodegr->no_po}}</option>
                                </select>
                                @endif
                                @if ($kodegr->type_dok == "")
                                <select class="form-control select2bs4" id="txt_po" name="txt_po" style="width: 100%;"  disabled></select>
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
                                <select class="form-control select2bs4" id="txt_wsglobal" name="txt_wsglobal" style="width: 100%;" >
                                    <option selected="selected" value="{{$kodegr->no_ws}}">{{$kodegr->no_ws}}</option>
                                </select>
                                @endif
                                @if ($kodegr->type_dok == "FOB")
                                <select class="form-control select2bs4" id="txt_wsglobal" name="txt_wsglobal" style="width: 100%;"  disabled>
                                </select>
                                @endif
                                @if ($kodegr->type_dok == "")
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
                                    <option selected="selected" value="">Select Type</option>
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
                                    <option selected="selected" value="">Select Type</option>
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
                                <input type="text" class="form-control " id="txt_aju_num" name="txt_aju_num" value="{{$kodegr->no_aju}}" >
                            </div>
                        </div>
                    </div>

                    <div class="col-md-5">
                        <div class="mb-1">
                            <div class="form-group">
                                <label><small>Tgl Aju</small></label>
                                <input type="date" class="form-control form-control" id="txt_tgl_aju" name="txt_tgl_aju"
                                value="{{$kodegr->tgl_aju}}">
                            </div>
                        </div>
                    </div>

                    <div class="col-md-7">
                        <div class="mb-1">
                            <div class="form-group">
                                <label><small>No Daftar</small></label>
                                <input type="text" class="form-control " id="txt_reg_num" name="txt_reg_num" value="{{$kodegr->no_daftar}}" >
                            </div>
                        </div>
                    </div>

                    <div class="col-md-5">
                        <div class="mb-1">
                            <div class="form-group">
                                <label><small>Tgl Daftar</small></label>
                                <input type="date" class="form-control form-control" id="txt_reg_aju" name="txt_reg_aju"
                                value="{{$kodegr->tgl_daftar}}">
                            </div>
                        </div>
                    </div>

                    <div class="col-md-7">
                        <div class="mb-1">
                            <div class="form-group">
                                <label><small>Contract Number</small></label>
                                <input type="text" class="form-control " id="txt_kontrak" name="txt_kontrak" value="{{$kodegr->no_kontrak}}" >
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
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endforeach

<div class="card card-sb">
    <div class="card-header">
        <h5 class="card-title fw-bold">
            Data Detail
        </h5>
    </div>
    <div class="card-body">
        <div class="form-group row">
        <!-- <div class="d-flex justify-content-between">
            <div class="ml-auto">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
            </div>
                <input type="text"  id="cari_item" name="cari_item" autocomplete="off" placeholder="Search Item..." onkeyup="cariitem()">
            </div> -->
            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-striped w-100 text-nowrap">
                    <thead>
                        <tr>
                            <th class="text-center" style="font-size: 0.6rem;width: 300px;">No Ws</th>
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
                            <th class="text-center" style="font-size: 0.6rem;">Location</th>
                        </tr>
                    </thead>
                    <tbody>
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
                            <td value="{{$detdata->no_ws}}">{{$detdata->qty_sisa}}</td>
                            <td value="{{$detdata->no_ws}}">{{$detdata->qty_good}}</td>
                            <td value="{{$detdata->no_ws}}">{{$detdata->unit}}</td>
                            <td value="{{$detdata->no_ws}}">{{$detdata->qty_reject}}</td>
                            <td value="{{$detdata->no_ws}}">{{$detdata->unit}}</td>
                            <td>
                               @if($detdata->qty_sisa > 0)
    <div class="d-flex gap-1 justify-content-center">
        <button type="button" 
                class="btn btn-sm btn-warning" 
                onclick='addlocation(
                    @json($detdata->no_ws),
                    @json($detdata->id_jo),
                    @json($detdata->id_item),
                    @json($detdata->kode_item),
                    @json($detdata->qty_good),
                    @json($detdata->unit),
                    @json($detdata->qty_good),
                    @json($detdata->desc_item),
                    @json($detdata->qty_sisa)
                )'>
            <i class="fa-solid fa-circle-plus fa-lg"></i>
        </button>

        <a href="{{ route('upload-lokasi', $detdata->id) }}">
            <button type="button" class="btn btn-sm btn-info">
                <i class="fa-solid fa-upload"></i>
            </button>
        </a>

        <button type="button" class="btn btn-sm btn-danger" 
                onclick='deleteData(@json($detdata->no_dok), @json($detdata->id_item))'>
            <i class="fa-solid fa-trash"></i>
        </button>
    </div>
@endif

                                @if($detdata->qty_sisa <= 0)
                                <div class='d-flex gap-1 justify-content-center'>
                                    <button type='button' class='btn btn-sm btn-success' href='javascript:void(0)' onclick='showlocation("{{$detdata->no_ws}}","{{$detdata->id_jo}}","{{$detdata->id_item}}","{{$detdata->kode_item}}","{{$detdata->qty_good}}","{{$detdata->unit}}","{{$detdata->qty_good}}","{{$detdata->desc_item}}","{{$detdata->qty_sisa}}");getlist_showlokasi("{{$detdata->no_ws}}","{{$detdata->id_jo}}","{{$detdata->id_item}}")'><i class="fa-solid fa-clipboard-check fa-lg"></i></button>
                                    <button type='button' class='btn btn-sm btn-danger' onclick='deleteData("{{$detdata->no_dok}}","{{$detdata->id_item}}")'>
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mb-1">
            <div class="form-group">
                <!-- <button class="btn btn-sb float-end mt-2 ml-2"><i class="fa fa-plus" aria-hidden="true"></i> Save</button> -->
                <a href="{{ route('in-material') }}" class="btn btn-danger float-end mt-2">
                    <i class="fas fa-arrow-circle-left"></i> Kembali</a>
                </div>
            </div>
        </div>
    </div>
</div>
</form>

<div class="modal fade modal-add-lokasi" id="modal-add-lokasi">
    <form action="{{ route('save-lokasi') }}" method="post" onsubmit="submitForm(this, event)">
       @method('POST')
       <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-sb text-light">
                <h4 class="modal-title">Set Lokasi</h4>
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
                                        <label><small>No BPB</small></label>
                                        <input type="text" class="form-control " id="m_gr_dok" name="m_gr_dok" value="" readonly>
                                        <input type="hidden" class="form-control " id="txtidgr" name="txtidgr" value="" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-1">
                                    <div class="form-group">
                                        <label><small>No WS</small></label>
                                        <input type="text" class="form-control " id="m_no_ws" name="m_no_ws" value="" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-1">
                                    <div class="form-group">
                                        <label><small>Kode barang</small></label>
                                        <input type="text" class="form-control " id="m_kode_item" name="m_kode_item" value="" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-1">
                                    <div class="form-group">
                                        <label><small>Balance</small></label>
                                        <input type="text" class="form-control" id="m_balance" name="m_balance" value="" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-1">
                                    <div class="form-group">
                                        <label><small>Qty Lokasi</small></label>
                                        <input type="text" class="form-control" id="ttl_qty_sj" name="ttl_qty_sj" value="" readonly>
                                        <!-- <input style="width:100%;text-align:right;" class="form-control-sm" type="text" id="ttl_qty_sj" name="ttl_qty_sj" value="" readonly/> -->
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-1">
                                    <div class="form-group">
                                        <label><small>Deskripsi</small></label>
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
                                        <label><small>Quantity</small></label>
                                        <input type="text" class="form-control" id="m_qty" name="m_qty" value="" readonly>
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
                                        <input type="text" class="form-control " id="m_lot" name="m_lot" value="" onkeyup="getlist_addlokasi()">
                                        <input type="hidden" id="m_idjo" name="m_idjo" value="">
                                        <input type="hidden" id="m_iditem" name="m_iditem" value="">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-1">
                                    <div class="form-group">
                                        <label><small>Lokasi</small></label>
                                        <select class="form-control select2lok" id="m_location" name="m_location" style="width: 100%;" onchange="getlist_addlokasi()">
                                            <option selected="selected" value="">Pilih Lokasi</option>
                                            @foreach ($lokasi as $lok)
                                            <option value="{{ $lok->kode_lok }}">{{ $lok->lokasi }}</option>
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
                            <input style="width:100%;text-align:right;" class="form-control-sm" type="hidden" id="ttl_qty_ak" name="ttl_qty_ak" value="" readonly/>
                        </div>
                    </div>

                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="fa fa-window-close" aria-hidden="true"></i> Tutup</button>
                <button type="submit" class="btn btn-primary toastsDefaultDanger"><i class="fa fa-thumbs-up" aria-hidden="true"></i> Simpan</button>
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
                <h4 class="modal-title">Info Lokasi</h4>
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
                                        <label><small>No BPB</small></label>
                                        <input type="text" class="form-control " id="m_gr_dok2" name="m_gr_dok2" value="" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-1">
                                    <div class="form-group">
                                        <label><small>No WS</small></label>
                                        <input type="text" class="form-control " id="m_no_ws2" name="m_no_ws2" value="" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-1">
                                    <div class="form-group">
                                        <label><small>Kode Barang</small></label>
                                        <input type="text" class="form-control " id="m_kode_item2" name="m_kode_item2" value="" readonly>
                                        <input type="hidden" class="form-control " id="m_iditem2" name="m_iditem2" value="" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-1">
                                    <div class="form-group">
                                        <label><small>Quantity</small></label>
                                        <input type="hidden" class="form-control" id="m_qty2" name="m_qty2" value="" readonly>
                                        <input type="text" class="form-control" id="m_qty2_new" name="m_qty2_new" value="" readonly>
                                        <input type="hidden" class="form-control" id="m_qty2_diff" name="m_qty2_diff" value="" readonly>
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
                                        <label><small>Deskripsi</small></label>
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

                        <div class="mt-2 text-right">
            <button type="button" class="btn btn-success btn-sm" onclick="saveEditedLokasi()">
                <i class="fa fa-save"></i> Update
            </button>
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

    $(document).ready(function () {
        $("#datatable").DataTable({
            ordering: false,
            processing: true,
            serverSide: false,
            paging: false,
            searching: true,
            scrollY: '300px',
            scrollX: true,        
            scrollCollapse: true,
            dom: "lfrtip"     
        });
    });

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

        $('.select2lok').select2({
            theme: 'bootstrap4',
            dropdownParent: $('.modal-add-lokasi')
        });

        $("#color").prop("disabled", true);
        $("#panel").prop("disabled", true);
        $('#p_unit').val("yard").trigger('change');

        //Reset Form
        if (document.getElementById('store-inmaterial')) {
            document.getElementById('store-inmaterial').reset();
        }


        function sum_qty_aktual(){
            var table2 = document.getElementById("datatable_list");
            var qty2 = 0;
            var jml_qty2 = 0;

            for (let j = 1; j < (table2.rows.length); j++) {
                qty2 = document.getElementById("datatable_list").rows[j].cells[3].children[0].value || 0;
                jml_qty2 += parseFloat(qty2) ;

                $('#ttl_qty_ak').val(jml_qty2);
            }

        }

        function sum_qty_sj(){
            var table = document.getElementById("datatable_list");
            var qty = 0;
            var jml_qty = 0;

            for (let i = 1; i < (table.rows.length); i++) {
                qty = document.getElementById("qty_sj"+i).value || 0;
                jml_qty += parseFloat(qty) ;

                $('#ttl_qty_sj').val(jml_qty);
            }

        }

        // function sumqtylokasi(val){
        //     var table = document.getElementById("datatable_list");
        //     var qty_stok = 0;

        //     for (let i = 1; i < (table.rows.length); i++) {
        //         qty_stok = document.getElementById("qty_stok"+i).value || 0;
        //         sisa_qty = parseFloat(qty_stok) - parseFloat(qty_out) ;
        //         // alert(sisa_qty);
        //         $('#m_qty_bal_h2').val(sum_bal);

        // }



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
    function addlocation($ws,$id_jo, $id_item,$kode_item, $qty, $unit, $balance,$desc,$qty_sisa){
        let ws = $ws;
        let id_jo = $id_jo;
        let id_item = $id_item;
        let kode_item = $kode_item;
        let qty = $qty;
        let unit = $unit;
        let balance = $qty_sisa;
        let desc = $desc;
        let no_dok = $('#txt_gr_dok').val();
        let id = $('#txt_idgr').val();
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
        $('#txtidgr').val(id);
        $('#modal-add-lokasi').modal('show');

        $('#m_qty_det').val('');
        $('#m_lot').val('');
        $('#ttl_qty_sj').val('');
        $('#ttl_qty_aktual').val('');
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
                lot: $('#m_lot').val(),
                no_dok: $('#m_gr_dok').val(),
                no_ws: $('#m_no_ws').val(),
                id_jo: $('#m_idjo').val(),
                id_item: $('#m_iditem').val()
            },
            success: function (res) {
                if (res) {
                    document.getElementById('detail_addlok').innerHTML = res;
                    $('.select2lok').select2({
                        theme: 'bootstrap4',
                        dropdownParent: $('.modal-add-lokasi')
                    });
                }
            }
        });
    }

function deleteData(no_dok, id_item) {
    Swal.fire({
        title: 'Hapus Data?',
        text: "Data ini akan dihapus secara permanen.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '{{ route("delete-detail-barcode-rak") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    no_dok: no_dok,
                    id_item: id_item
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: 'Data berhasil dihapus.',
                            timer: 1500,
                            timerProgressBar: true,
                            showConfirmButton: false
                        }).then(() => {
                            if ($('#datatable').length) {
                                location.reload();
                            } else {
                                location.reload();
                            }
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            timer: 1500,
                            timerProgressBar: true,
                            text: response.message ?? 'Data gagal dihapus.'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        timer: 1500,
                        timerProgressBar: true,
                        text: 'Terjadi kesalahan saat menghapus data.'
                    });
                    console.error(error);
                }
            });
        }
    });
}



    function showlocation($ws,$id_jo, $id_item,$kode_item, $qty, $unit, $balance,$desc,$qty_sisa){
        let ws = $ws;
        let id_jo = $id_jo;
        let id_item = $id_item;
        let kode_item = $kode_item;
        let qty = $qty;
        let unit = $unit;
        let balance = $qty_sisa;
        let desc = $desc;
        let no_dok = $('#txt_gr_dok').val();
        // alert(id_item);
        $('#m_gr_dok2').val(no_dok);
        $('#m_no_ws2').val(ws);
        $('#m_kode_item2').val(kode_item);
        $('#m_qty2').val(qty);
        $('#m_qty2_new').val(qty);
        $('#m_qty2_diff').val('0');
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
                        "paging": false,
                        "info": false,
                        "ordering": false
                    });
                }
            }
        });
    }

function saveEditedLokasi() {
    const rows = [];
    $("#tableshow tbody tr").each(function() {
        const barcode = $(this).data('barcode');
        const cols = $(this).find('td');
        rows.push({
            no_barcode: barcode,
            no_roll: $(cols[1]).text().trim(),
            no_roll_buyer: $(cols[2]).text().trim(),
            no_lot: $(cols[3]).text().trim(),
            qty_aktual: $(cols[4]).text().trim(),
            kode_lok: $(cols[5]).text().trim(),
            m_qty: $('#m_qty2_new').val(),
            m_qty_diff: $('#m_qty2_diff').val(),
            m_gr_dok: $('#m_gr_dok2').val(),
            m_iditem: $('#m_iditem2').val(),
        });
    });

    Swal.fire({
        title: 'Konfirmasi',
        text: 'Simpan semua perubahan data lokasi?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Simpan',
        cancelButtonText: 'Batal',
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: 'POST',
                url: '{{ route("update-all-barcode-rak") }}',
                data: { data: rows },
                success: function(res) {
                    if (res.success) {
                        Swal.fire({
                        title: 'Berhasil',
                        text: 'Data berhasil disimpan!',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            location.reload(); 
                        }
                    });
                    } else {
                        Swal.fire('Gagal', 'Terjadi kesalahan saat menyimpan.', 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Tidak dapat terhubung ke server!', 'error');
                }
            });
        }
    });
}


$(document).ready(function(){
    let isDragging = false;
    let isNumbering = false; // mode numbering (ALT + klik kanan)
    let startCell = null;
    let startText = '';
    let startRowIndex = null;
    let startColIndex = null;
    let lastRowIndex = null;
    let lastColIndex = null;

    // === FUNGSI UPDATE TOTAL QTY ===
    function updateQtyTotal() {
        let total = 0;
        $('#tableshow tbody tr').each(function(){
            let val = parseFloat($(this).find('td:eq(4)').text()) || 0; // kolom 5 = Qty Aktual
            total += val;
        });
        $('#m_qty2_new').val(total.toFixed(2));

        // bandingkan dengan qty lama
        let awal = parseFloat($('#m_qty2').val()) || 0;
        let diff = total - awal;
        $('#m_qty2_diff').val(diff.toFixed(2));
    }

    function getCellPosition(cell){
        const row = $(cell).closest('tr').index();
        const col = $(cell).index();
        return { row, col };
    }

    // === MULAI DRAG (ALT + klik kiri = copy text | ALT + klik kanan = numbering) ===
    $(document).on('mousedown', '#tableshow td.editable', function(e){
        if(e.altKey && (e.which === 1 || e.which === 3)){
            e.preventDefault();

            isDragging = true;
            isNumbering = (e.which === 3);
            startCell = this;
            startText = $(this).text();

            const pos = getCellPosition(this);
            startRowIndex = pos.row;
            startColIndex = pos.col;
            lastRowIndex = startRowIndex;
            lastColIndex = startColIndex;

            $('#tableshow td.editable').removeClass('drag-highlight');
            $(this).addClass('drag-highlight');
        }
    });

    // === HIGHLIGHT AREA ===
    $(document).on('mouseenter', '#tableshow td.editable', function(){
        if(isDragging && startCell){
            const pos = getCellPosition(this);
            lastRowIndex = pos.row;
            lastColIndex = pos.col;

            const minRow = Math.min(startRowIndex, lastRowIndex);
            const maxRow = Math.max(startRowIndex, lastRowIndex);
            const minCol = Math.min(startColIndex, lastColIndex);
            const maxCol = Math.max(startColIndex, lastColIndex);

            $('#tableshow td.editable').removeClass('drag-highlight');
            for(let r = minRow; r <= maxRow; r++){
                for(let c = minCol; c <= maxCol; c++){
                    $('#tableshow tr').eq(r).find('td').eq(c).addClass('drag-highlight');
                }
            }
        }
    });

    // === AUTO SCROLL SAAT DRAG ===
    $(document).on('mousemove', function(e){
        if(!isDragging) return;
        const $container = $('.modal-body .table-responsive');
        const offset = $container.offset();
        const scrollTop = $container.scrollTop();
        const height = $container.height();
        const scrollSpeed = 25;

        if(e.pageY < offset.top + 30){
            $container.scrollTop(scrollTop - scrollSpeed);
        } else if(e.pageY > offset.top + height - 30){
            $container.scrollTop(scrollTop + scrollSpeed);
        }
    });

    // === LEPAS DRAG ===
    $(document).on('mouseup', function(){
        if(isDragging && startCell){
            const selectedCells = $('#tableshow td.drag-highlight');

            if(isNumbering){
                // 🔢 MODE NUMBERING
                let num = 1;
                selectedCells.each(function(){
                    $(this).text(num++);
                });
            } else {
                // 📝 MODE COPY TEXT
                selectedCells.each(function(){
                    $(this).text(startText);
                });
            }

            $('#tableshow td.editable').removeClass('drag-highlight');

            const table = $('#tableshow').DataTable();
            table.rows().invalidate().draw(false);

            // Update total setelah drag selesai
            updateQtyTotal();
        }

        isDragging = false;
        isNumbering = false;
        startCell = null;
    });


    // Fungsi hitung total qty aktual
    function updateQtyTotal() {
    let total = 0;
    $('#tableshow tbody tr').each(function(){
        let val = parseFloat($(this).find('td:eq(4)').text()) || 0;
        total += val;
    });
    $('#m_qty2_new').val(total.toFixed(2));

    // hitung selisih
    let awal = parseFloat($('#m_qty2').val()) || 0;
    let selisih = total - awal;
    $('#m_qty2_diff').val(selisih.toFixed(2));
}


    // Saat user mengubah qty di kolom qty aktual
    $(document).on('input', '#tableshow td:nth-child(5)', function(){
        let value = $(this).text();

        // Validasi angka saja
        if(isNaN(value) || value.trim() === ''){
            $(this).css('background-color', '#f8d7da'); // merah muda (error)
        } else {
            $(this).css('background-color', ''); // reset
            updateQtyTotal(); // hitung ulang total
        }
    });

    // Jalankan pertama kali saat tabel dimuat
    updateQtyTotal();
});


</script>
@endsection
