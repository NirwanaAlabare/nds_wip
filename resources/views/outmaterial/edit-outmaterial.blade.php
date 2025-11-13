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
<form action="{{ route('update-outmaterial-fabric') }}" method="post" id="store-outmaterial-fabric" onsubmit="submitForm(this, event)">
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
    <div class="card-body">
    <div class="form-group row">
    <div class="col-md-4">
        <div class="row">
            <div class="col-md-12">
            <div class="mb-1">
                <div class="form-group">
                <label><small>No BPPB</small></label>
                <input type="text" class="form-control " id="txt_nobppb" name="txt_nobppb" value="{{ $data_out->no_bppb }}" readonly>
                <input type="hidden" class="form-control " id="txt_idbppb" name="txt_idbppb" value="{{ $data_out->id }}" readonly>
                </div>
            </div>
            </div>

            <div class="col-md-12">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Tgl BPPB</small></label>
                <input type="date" class="form-control form-control" id="txt_tgl_bppb" name="txt_tgl_bppb"
                        value="{{ $data_out->tgl_bppb }}">
                </div>
            </div>
            </div>

            <div class="col-md-12">
            <div class="mb-1">
                <div class="form-group">
                <label><small>No Request</small></label>
                <input type="text" class="form-control form-control" id="txt_noreq" name="txt_noreq"
                        value="{{ $data_out->no_req }}" readonly>
                </div>
            </div>
            </div>

            <div class="col-md-12">
            <div class="mb-1">
                <div class="form-group">
                    <label><small>Jenis Pengeluaran</small></label>
                    <select class="form-control select2bs4" id="txt_jns_klr" name="txt_jns_klr" style="width: 100%;">
                        @foreach ($jns_klr as $jnsklr)
                            <option value="{{ trim($jnsklr->isi) }}"
                                {{ trim($jnsklr->isi) == trim($data_out->jenis_pengeluaran) ? 'selected' : '' }}>
                                {{ $jnsklr->tampil }}
                            </option>
                        @endforeach
                    </select>
                </div>

            </div>
            </div>

            <div class="col-md-12">
            <div class="mb-1">
                <div class="form-group">
                <label><small>No JO</small></label>
                <input type="text" class="form-control " id="txt_nojo" name="txt_nojo" value="{{ $data_out->no_jo }}" readonly>
                <input type="hidden" class="form-control " id="txt_id_jo" name="txt_id_jo" value="" readonly>
                </div>
            </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="row">

            <div class="col-md-12">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Dikirim Ke</small></label>
                <select class="form-control select2bs4" id="txt_dikirim" name="txt_dikirim" style="width: 100%;">
                    <option selected="selected" value="">Pilih Tujuan</option>
                        @foreach ($msupplier as $supp)
                        <option value="{{ trim($supp->Supplier) }}"
                            {{ trim($supp->Supplier) == trim($data_out->tujuan) ? 'selected' : '' }}>
                            {{ $supp->Supplier }}
                        </option>
                        @endforeach
                </select>
                </div>
            </div>
            </div>

            <div class="col-md-12">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Buyer</small></label>
                <input type="text" class="form-control " id="txt_buyer" name="txt_buyer" value="{{ $data_out->buyer }}" readonly>
                </div>
            </div>
            </div>

            <div class="col-md-12">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Worksheet</small></label>
                <input type="text" class="form-control " id="txt_nows" name="txt_nows" value="{{ $data_out->no_ws }}" readonly>
                </div>
            </div>
            </div>

            <div class="col-md-12">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Worksheet Actual</small></label>
                <input type="text" class="form-control " id="txt_nows_act" name="txt_nows_act" value="{{ $data_out->no_ws_aktual }}" readonly>
                </select>
                </div>
            </div>
            </div>

            <div class="col-md-12">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Style Actual</small></label>
                <input type="text" class="form-control " id="txt_style_act" name="txt_style_act" value="{{ $data_out->style_aktual }}" readonly>
                </div>
            </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="row">

            <div class="col-md-12">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Dokumen BC</small></label>
                <select class="form-control select2bs4" id="txt_dok_bc" name="txt_dok_bc" style="width: 100%;">
                    <option selected="selected" value="">Pilih Dokumen</option>
                        @foreach ($mtypebc as $bc)
                        <option value="{{ trim($bc->nama_pilihan) }}"
                            {{ trim($bc->nama_pilihan) == trim($data_out->dok_bc) ? 'selected' : '' }}>
                            {{ $bc->nama_pilihan }}
                        </option>
                        @endforeach
                </select>
                </div>
            </div>
            </div>

            <!-- <div class="col-md-7">
            <div class="mb-1">
                <div class="form-group">
                <label><small>No Aju</small></label>
                <input type="text" class="form-control " id="txt_no_aju" name="txt_no_aju" value="" >
                </div>
            </div>
            </div>

            <div class="col-md-5">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Tgl Aju</small></label>
                <input type="date" class="form-control form-control" id="txt_tgl_aju" name="txt_tgl_aju"
                        value="{{ date('Y-m-d') }}">
                </div>
            </div>
            </div>

            <div class="col-md-7">
            <div class="mb-1">
                <div class="form-group">
                <label><small>No Daftar</small></label>
                <input type="text" class="form-control " id="txt_no_daftar" name="txt_no_daftar" value="" >
                </div>
            </div>
            </div>

            <div class="col-md-5">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Tgl Daftar</small></label>
                <input type="date" class="form-control form-control" id="txt_tgl_daftar" name="txt_tgl_daftar"
                        value="{{ date('Y-m-d') }}">
                </div>
            </div>
            </div> -->

            <input type="hidden" class="form-control " id="txt_no_aju" name="txt_no_aju" value="" >
            <input type="hidden" class="form-control form-control" id="txt_tgl_aju" name="txt_tgl_aju"
                        value="{{ date('Y-m-d') }}">
            <input type="hidden" class="form-control " id="txt_no_daftar" name="txt_no_daftar" value="" >
            <input type="hidden" class="form-control form-control" id="txt_tgl_daftar" name="txt_tgl_daftar"
                        value="{{ date('Y-m-d') }}">

            <div class="col-md-6">
            <div class="mb-1">
                <div class="form-group">
                <label><small>No Kontrak</small></label>
                <input type="text" class="form-control " id="txt_kontrak" name="txt_kontrak" value="{{ $data_out->no_kontrak }}" >
                </div>
            </div>
            </div>

            <div class="col-md-6">
            <div class="mb-1">
                <div class="form-group">
                <label><small>No Invoice</small></label>
                <input type="text" class="form-control " id="txt_invoice" name="txt_invoice" value="{{ $data_out->no_invoice }}">
               </div>
            </div>
            </div>

            <div class="col-md-12">
            <div class="mb-1">
                <div class="form-group">
                <label><small>PO Subcont</small></label>
                <select class="form-control select2bs4" id="txt_po_sub" name="txt_po_sub" style="width: 100%;">
                    <option selected="selected" value="">Pilih PO Subcont</option>
                        @foreach ($no_po as $po)
                    <option value="{{ $po->pono }}">
                                {{ $po->pono }}
                    </option>
                        @endforeach
                </select>
                </div>
            </div>
            </div>

            <div class="col-md-12">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Catatan</small></label>
                <textarea type="text" rows="5" class="form-control " id="txt_notes" name="txt_notes" value="" >{{ $data_out->catatan }}</textarea>
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
            <table id="datatable" class="table table-bordered table-striped table-head-fixed table w-100 text-nowrap">
                <thead>
                    <tr>
                        <th class="text-center" style="font-size: 0.6rem;width: 300px;">Style</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 300px;">ID Item</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 300px;">Deskripsi</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 300px;">Stok</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 300px;">Qty Request</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 300px;">Qty Out</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 300px;">Sisa Qty Request</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 300px;">Satuan</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 300px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i=1; ?>
                        @foreach ($det_data as $detdata)
                        <tr>
                            <td value="{{$detdata->styleno}}">{{$detdata->styleno}}</td>
                            <td value="{{$detdata->id_item}}">{{$detdata->id_item}}</td>
                            <td value="{{$detdata->item_desc}}">{{$detdata->item_desc}}</td>
                            <td value="{{$detdata->stok}}">{{$detdata->stok}}</td>
                            <td value="{{$detdata->qty_req}}">{{$detdata->qty_req}}</td>
                            <td value="{{$detdata->qty_out}}">{{$detdata->qty_out}}</td>
                            <td value="{{$detdata->qty_sisa_req}}">{{$detdata->qty_sisa_req}}</td>
                            <td value="{{$detdata->satuan}}">{{$detdata->satuan}}</td>
                            <td value="-">
                                <div class='d-flex gap-1 justify-content-center'>
                                    <button 
    type="button" 
    class="btn btn-sm btn-success"
    onclick='
        showlocation(
            @json($detdata->no_bppb),
            @json($detdata->kpno),
            @json($detdata->styleno),
            @json($detdata->id_item),
            @json($detdata->id_jo),
            @json($detdata->qty_out),
            @json($detdata->item_desc)
        );
        getlist_bppbdet(
            @json($detdata->no_bppb),
            @json($detdata->id_jo),
            @json($detdata->id_item)
        );
    '
>
    <i class="fa-solid fa-file-pen"></i>
</button>

                                    @if ($detdata->qty_out != 0)
                                    <button type='button' class='btn btn-sm btn-danger' onclick='deleteData("{{$detdata->no_bppb}}","{{$detdata->id_jo}}","{{$detdata->id_item}}")'>
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                    @endif
                                    @if ($detdata->qty_sisa_req != 0)
                                    <button type='button' class='btn btn-sm btn-info' href='javascript:void(0)' onclick='out_manual("{{$detdata->id_item}}","{{$detdata->id_jo}}","{{$detdata->qty_sisa_req}}","{{$detdata->satuan}}")'><i class="fa-solid fa-table-list"></i></i></button>
                                    <button type='button' class='btn btn-sm btn-info' href='javascript:void(0)' onclick='out_scan("{{$detdata->id_item}}","{{$detdata->id_jo}}","{{$detdata->qty_sisa_req}}","{{$detdata->satuan}}","{{$detdata->no_req}}")'><i class="fa-solid fa-barcode"></i></i></button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        <?php $i++; ?>
                        @endforeach
                </tbody>
            </table>
        </div>
    </div>
            <div class="mb-1">
                <div class="form-group">
                    <button class="btn btn-sb float-end mt-2 ml-2"><i class="fa-solid fa-floppy-disk"></i> Simpan</button>
                    <a href="{{ route('out-material') }}" class="btn btn-danger float-end mt-2" onclick="delete_all_temp()">
                    <i class="fas fa-arrow-circle-left"></i> Kembali</a>
                </div>
            </div>
        </div>
        </div>
    </div>
</form>

<div class="modal fade" id="modal-out-manual">
    <form action="{{ route('save-out-manual-edit') }}" method="post" onsubmit="submitFormScan(this, event)">
         @method('POST')
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-sb text-light">
                    <h4 class="modal-title">List Item</h4>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <div class="modal-body">
                <div class="form-group row">

                    <div class="col-md-12">
                        <input type="hidden" class="form-control " id="m_no_bppb" name="m_no_bppb" value="" readonly>
                        <input type="hidden" class="form-control " id="m_tgl_bppb" name="m_tgl_bppb" value="" readonly>
                        <input type="hidden" class="form-control " id="t_roll" name="t_roll" value="" readonly>
                    <div class="row">
                        <div class="col-4 col-md-4">
                        <div class="mb-1">
                        <div class="form-group">
                            <label><small>Qty Request</small></label>
                                <input type="text" class="form-control " id="m_qty_req" name="m_qty_req" value="" readonly>
                                <input type="hidden" class="form-control " id="m_qty_req_h" name="m_qty_req_h" value="" readonly>
                        </div>
                        </div>
                        </div>
                        <div class="col-4 col-md-4">
                        <div class="mb-1">
                        <div class="form-group">
                            <label><small>Qty Out</small></label>
                                <input type="text" class="form-control " id="m_qty_out" name="m_qty_out" value="" readonly>
                                <input type="hidden" class="form-control " id="m_qty_out_h" name="m_qty_out_h" value="" readonly>
                        </div>
                        </div>
                        </div>
                        <div class="col-4 col-md-4">
                        <div class="mb-1">
                        <div class="form-group">
                            <label><small>Qty Balance</small></label>
                                <input type="text" class="form-control " id="m_qty_bal" name="m_qty_bal" value="" readonly>
                                <input type="hidden" class="form-control " id="m_qty_bal_h" name="m_qty_bal_h" value="" readonly>
                        </div>
                        </div>
                        </div>
                    </div>
                    </div>

                    <div class="col-md-12">
                    <div class="row">
                        <div class="col-md-12" id="detail_showitem">
                        </div>
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


<div class="modal fade" id="modal-out-barcode">
    <form action="{{ route('save-out-scan-edit') }}" method="post" onsubmit="submitFormScan(this, event)">
         @method('POST')
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-sb text-light">
                    <h4 class="modal-title">List Item</h4>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <div class="modal-body">
                <div class="form-group row">
                    <div class="row">
                        <div class="col-4 col-md-4">
                        <div class="mb-1">
                        <div class="form-group">
                            <label><small>Qty Request</small></label>
                                <input type="text" class="form-control " id="m_qty_req2" name="m_qty_req2" value="" readonly>
                                <input type="hidden" class="form-control " id="m_qty_req_h2" name="m_qty_req_h2" value="" readonly>
                        </div>
                        </div>
                        </div>
                        <div class="col-4 col-md-4">
                        <div class="mb-1">
                        <div class="form-group">
                            <label><small>Qty Out</small></label>
                                <input type="text" class="form-control " id="m_qty_out2" name="m_qty_out2" value="" readonly>
                                <input type="hidden" class="form-control " id="m_qty_out_h2" name="m_qty_out_h2" value="" readonly>
                        </div>
                        </div>
                        </div>
                        <div class="col-4 col-md-4">
                        <div class="mb-1">
                        <div class="form-group">
                            <label><small>Qty Balance</small></label>
                                <input type="text" class="form-control " id="m_qty_bal2" name="m_qty_bal2" value="" readonly>
                                <input type="hidden" class="form-control " id="m_qty_bal_h2" name="m_qty_bal_h2" value="" readonly>
                        </div>
                        </div>
                        </div>
                        <div class="col-md-12">
                            <input type="hidden" class="form-control " id="m_no_bppb2" name="m_no_bppb2" value="" readonly>
                            <input type="hidden" class="form-control " id="m_tgl_bppb2" name="m_tgl_bppb2" value="" readonly>
                        <input type="hidden" class="form-control " id="t_roll2" name="t_roll2" value="" readonly>
                        <div class="mb-1">
                        <div class="form-group">
                            <label><small>Scan Barcode</small></label>
                            <select class='form-control select2barcode' multiple='multiple' style='width: 100%;height: 20px;' name='txt_barcode' id='txt_barcode' onchange='getdatabarcode(this.value)' >
                            </select>
                        </div>
                        </div>
                        </div>
                    </div>

                    <div class="col-md-12">
                    <div class="row">
                        <div class="col-md-12" id="detail_showbarcode">
                        </div>
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


<div class="modal fade" id="modal-det-bppb">
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

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-1">
                                    <div class="form-group">
                                        <label><small>No BPPB</small></label>
                                        <input type="text" class="form-control " id="mdl_no_bppb" name="mdl_no_bppb" value="" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-1">
                                    <div class="form-group">
                                        <label><small>No WS</small></label>
                                        <input type="text" class="form-control " id="mdl_no_ws" name="mdl_no_ws" value="" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-1">
                                    <div class="form-group">
                                        <label><small>Style</small></label>
                                        <input type="text" class="form-control " id="mdl_style" name="mdl_style" value="" readonly>
                                        <input type="hidden" class="form-control " id="mdl_id_item" name="mdl_id_item" value="" readonly>
                                        <input type="hidden" class="form-control " id="mdl_id_jo" name="mdl_id_jo" value="" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-1">
                                    <div class="form-group">
                                        <label><small>Quantity</small></label>
                                        <input type="text" class="form-control" id="mdl_qty_out" name="mdl_qty_out" value="" readonly>
                                        <input type="hidden" class="form-control" id="mdl_qty_out_h" name="mdl_qty_out_h" value="" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="mb-1">
                                    <div class="form-group">
                                        <label><small>Deskripsi</small></label>
                                        <input type="text" class="form-control " id="mdl_catatan" name="mdl_catatan" value="" readonly>
                                    </div>
                                </div>
                            </div>

                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-md-12" id="detail_showlok">
                            </div>
                        </div>

                        <div class="mt-2 text-right">
            <button type="button" class="btn btn-success btn-sm" onclick="saveEditedBppbDet()">
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
       $(document).ready(function() {

    // Fokus otomatis ke kolom search saat Select2 dibuka
    $(document).on('select2:open', function() {
        setTimeout(() => {
            let searchField = document.querySelector('.select2-container--open .select2-search__field');
            if (searchField) searchField.focus();
        }, 0);
    });

    // Initialize Select2
    $('.select2').select2();
    $('.select2bs4').select2({
        theme: 'bootstrap4'
    });
    $('.select2barcode').select2({
        theme: 'bootstrap4'
    });
    $('.select2req').select2({
        theme: 'bootstrap4'
    });

});


        // $('#ws_id').on('change', async function(e) {
        //     await updateColorList();
        //     await updateOrderInfo();
        // });

        // $('#color').on('change', async function(e) {
        //     await updatePanelList();
        //     await updateSizeList();
        // });

        // $('#panel').on('change', async function(e) {
        //     await getMarkerCount();
        //     await getNumber();
        //     await updateSizeList();
        // });

        // $('#p_unit').on('change', async function(e) {
        //     let unit = $('#p_unit').val();
        //     if (unit == 'yard') {
        //         $('#comma_unit').val('INCH');
        //         $('#l_unit').val('inch').trigger("change");
        //     } else if (unit == 'meter') {
        //         $('#comma_unit').val('CM');
        //         $('#l_unit').val('cm').trigger("change");
        //     }
        // });

        // Form Submit
function submitFormScan(e, evt) {
    $("input[type=submit][clicked=true]").attr('disabled', true);

    evt.preventDefault();

    clearModified();

    $.ajax({
        url: e.getAttribute('action'),
        type: e.getAttribute('method'),
        data: new FormData(e),
        processData: false,
        contentType: false,
        success: function(res) {
            $("input[type=submit][clicked=true]").removeAttr('disabled');

            if (res.status == 200) {
                $('.modal').modal('hide');

                Swal.fire({
                    icon: 'success',
                    title: res.message,
                    showCancelButton: false,
                    showConfirmButton: true,
                    confirmButtonText: 'Oke',
                    timer: 5000,
                    timerProgressBar: true
                }).then(() => {
                    location.reload();
                });

                e.reset();

                if (res.callback != '') {
                    eval(res.callback);
                }

                if (document.getElementsByClassName('select2')) {
                    $(".select2").val('').trigger('change');
                }
            }
          else if (res.status == 300) {
            $('.modal').modal('hide');

            iziToast.success({
                title: 'success',
                message: res.message,
                position: 'topCenter'
            });

            e.reset();

            if (document.getElementsByClassName('select2')) {
                $(".select2").val('').trigger('change');
            }
        } else {
                for(let i = 0;i < res.errors; i++) {
                    document.getElementById(res.errors[i]).classList.add('is-invalid');
                    modified.push([res.errors[i], 'classList', 'remove(', "'is-invalid')"])
                }

                iziToast.error({
                    title: 'Error',
                    message: res.message,
                    position: 'topCenter'
                });
            }

            if (res.table != '') {
                $('#'+res.table).DataTable().ajax.reload();
            }

            if (Object.keys(res.additional).length > 0 ) {
                for (let key in res.additional) {
                    if (document.getElementById(key)) {
                        document.getElementById(key).classList.add('is-invalid');

                        if (res.additional[key].hasOwnProperty('message')) {
                            document.getElementById(key+'_error').classList.remove('d-none');
                            document.getElementById(key+'_error').innerHTML = res.additional[key]['message'];
                        }

                        if (res.additional[key].hasOwnProperty('value')) {
                            document.getElementById(key).value = res.additional[key]['value'];
                        }

                        modified.push(
                            [key, '.classList', '.remove(', "'is-invalid')"],
                            [key+'_error', '.classList', '.add(', "'d-none')"],
                            [key+'_error', '.innerHTML = ', "''"],
                        )
                    }
                }
            }
        }, error: function (jqXHR) {
            $("input[type=submit][clicked=true]").removeAttr('disabled');

            let res = jqXHR.responseJSON;
            let message = '';

            for (let key in res.errors) {
                message = res.errors[key];
                document.getElementById(key).classList.add('is-invalid');
                document.getElementById(key+'_error').classList.remove('d-none');
                document.getElementById(key+'_error').innerHTML = res.errors[key];

                modified.push(
                    [key, '.classList', '.remove(', "'is-invalid')"],
                    [key+'_error', '.classList', '.add(', "'d-none')"],
                    [key+'_error', '.innerHTML = ', "''"],
                )
            };

            iziToast.error({
                title: 'Error',
                message: 'Terjadi kesalahan.',
                position: 'topCenter'
            });
        }
    });
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

        // enableinput
        function enableinput() {
    var table = document.getElementById("tableshow");
    var t_roll = 0;

    for (let i = 1; i < table.rows.length; i++) {
        var cek = document.getElementById("pil_item" + i);
        var qtyStokInput = document.getElementById("qty_stok" + i);
        var qtyOutInput = document.getElementById("qty_out" + i);
        var qtySisaInput = document.getElementById("qty_sisa" + i);

        // Cek jika elemen checkbox dan qty stok ada
        if (!cek || !qtyStokInput || !qtyOutInput || !qtySisaInput) {
            continue;
        }

        var qtyroll = parseFloat(qtyStokInput.value) || 0;

        if (cek.checked === true) {
            t_roll += qtyroll;

            qtyOutInput.disabled = false;
            qtyOutInput.value = qtyroll;
            qtySisaInput.value = 0;
        } else {
            qtyOutInput.value = '';
            qtySisaInput.value = '';
            qtyOutInput.disabled = true;
        }
    }

    document.getElementById('t_roll').value = t_roll;

    // Hanya panggil sekali setelah selesai loop
    sum_qty_item('1');
}


        function sum_qty_item(val){
            var table = document.getElementById("tableshow");
            var qty_stok = 0;
            var satuan = '';
            var qty_out = 0;
            var qty = 0;
            var sisa_qty = 0;
            var nol = 0;
            var qty_req = $('#m_qty_req_h').val();
            var h_qty_out = '';
            var h_sum_bal = '';
            var sum_bal = 0;
            var sum_out = 0;

            for (let i = 1; i < (table.rows.length); i++) {
                var cek =  document.getElementById("pil_item"+i);
                satuan = document.getElementById("unit"+i).value;
                qty_stok = document.getElementById("qty_stok"+i).value || 0;
                qty_out = document.getElementById("qty_out"+i).value || 0;
                sisa_qty = parseFloat(qty_stok) - parseFloat(qty_out) ;

                if (cek.checked == true && qty_out > 0) {
                    if (parseFloat(qty_out) > parseFloat(qty_stok)) {
                        $('#qty_out'+i).val(qty_stok);
                        $('#qty_sisa'+i).val(nol);
                    }else{
                        $('#qty_out'+i).val(qty_out);
                        $('#qty_sisa'+i).val(sisa_qty.round(2) || 0);
                    }
                    sum_out += parseFloat(qty_out);
                }
            }
                h_qty_out = sum_out.round(2) + ' ' + satuan;
                sum_bal = parseFloat(qty_req) - parseFloat(sum_out);
                h_sum_bal = sum_bal.round(2) + ' ' + satuan;
                $('#m_qty_out').val(h_qty_out);
                $('#m_qty_out_h').val(sum_out.round(2));
                $('#m_qty_bal').val(h_sum_bal || 0);
                $('#m_qty_bal_h').val(sum_bal.round(2) || 0);

        }

        function sum_qty_barcode(val){
            var table = document.getElementById("tableshow");
            var qty_stok = 0;
            var satuan = '';
            var qty_out = 0;
            var qty = 0;
            var sisa_qty = 0;
            var nol = 0;
            var qty_req = $('#m_qty_req_h2').val();
            var h_qty_out = '';
            var h_sum_bal = '';
            var sum_bal = 0;
            var sum_out = 0;

            for (let i = 1; i < (table.rows.length); i++) {
                satuan = document.getElementById("unit"+i).value;
                qty_stok = document.getElementById("qty_stok"+i).value || 0;
                qty_out = document.getElementById("qty_out"+i).value || 0;
                sisa_qty = parseFloat(qty_stok) - parseFloat(qty_out) ;
                // alert(sisa_qty);

                if ( qty_out > 0) {
                    if (parseFloat(qty_out) > parseFloat(qty_stok)) {
                        $('#qty_out'+i).val(qty_stok);
                        $('#qty_sisa'+i).val(nol);
                    }else{
                        $('#qty_out'+i).val(qty_out);
                        $('#qty_sisa'+i).val(sisa_qty.round(2) || 0);
                    }
                    sum_out += parseFloat(qty_out);
                }
            }

                h_qty_out = sum_out.round(2) + ' ' + satuan;
                sum_bal = parseFloat(qty_req) - parseFloat(sum_out);
                h_sum_bal = sum_bal.round(2) + ' ' + satuan;
                $('#m_qty_out2').val(h_qty_out);
                $('#m_qty_out_h2').val(sum_out.round(2));
                $('#m_qty_bal2').val(h_sum_bal);
                $('#m_qty_bal_h2').val(sum_bal.round(2));

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

        function det_request(val){
        return $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route("get-detail_req") }}',
                type: 'get',
                data: {
                    no_req: val,
                },
                success: function (res) {
                    if (res) {
                        $('#txt_id_jo').val(res[0].id_jo);
                        $('#txt_nojo').val(res[0].jo_no);
                        $('#txt_dikirim').val(res[0].supplier);
                        $('#txt_idsupp').val(res[0].id_supplier);
                        $('#txt_nows').val(res[0].idws);
                        $('#txt_buyer').val(res[0].buyer);
                        $('#txt_nows_act').val(res[0].idws_act);
                        $('#txt_style_act').val(res[0].style_act);
                        getlistdata();
                    }
                },
            });
    }

        async function getlistdata() {
            return datatable.ajax.reload(() => {
                document.getElementById('jumlah_data').value = datatable.data().count();
            });
        }

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

    function getdatabarcode(val){
        let id_barcode = $('#txt_barcode').val();
        let text1 = "'";
        let kodenya = text1.concat(id_barcode, "'");
        let kodebarcode = kodenya.toString();
        let barcode = kodebarcode.replace(/,/g,"','");
        return $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route("get-data-barcode") }}',
                type: 'get',
                data: {
                    id_barcode: barcode,
                },
                success: function (res) {
                    if (res) {
                        document.getElementById('detail_showbarcode').innerHTML = res;
                        sum_qty_barcode('1');
                    }
                }
            });

    }

    function delete_scan($id_item,$id_jo){
        let id_item = $id_item;
        let id_jo = $id_jo;
        return $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route("delete-scan-temp") }}',
                type: 'get',
                data: {
                    id_item: id_item,
                    id_jo: id_jo,
                },
                success: function (res) {
                    getlistdata();
                }
            });

    }

    function delete_all_temp(){
        let no_bppb = $('#txt_nobppb').val();
        return $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route("delete-all-temp") }}',
                type: 'get',
                data: {
                    no_bppb: no_bppb,
                },
                success: function (res) {
                    // getlistdata();
                }
            });

    }

    function out_scan($id_item,$id_jo,$qty_req,$unit,$noreq){
        let id_item = $id_item;
        let id_jo = $id_jo;
        let qty_req = $qty_req;
        let unit = $unit;
        let no_bppb = $('#txt_nobppb').val();
        let tgl_bppb = $('#txt_tgl_bppb').val();
        let noreq = $noreq;

        getlist_barcode(id_item,id_jo,noreq);

        // $('#m_qty_req').val(qty_req + ' ' + unit);
        document.getElementById('txt_barcode').innerHTML = '';
        document.getElementById('detail_showbarcode').innerHTML = '';
        $('#m_qty_req2').val(qty_req + ' ' + unit);
        $('#m_qty_req_h2').val(qty_req);
        $('#m_no_bppb2').val(no_bppb);
        $('#m_tgl_bppb2').val(tgl_bppb);
        $('#m_qty_out2').val('');
        $('#m_qty_out_h2').val('');
        $('#m_qty_bal2').val('');
        $('#m_qty_bal_h2').val('');
        $('#modal-out-barcode').modal('show');
    }

    function getlist_barcode($id_item,$id_jo,$noreq){
        let iditem = $id_item;
        let idjo = $id_jo;
        let noreq = $noreq;
        return $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route("get-list-barcode") }}',
                type: 'get',
                data: {
                    id_item: iditem,
                    id_jo: idjo,
                    noreq: noreq,
                },
                success: function (res) {
                    if (res) {
                        document.getElementById('txt_barcode').innerHTML = res;
                        $("#txt_barcode").focus();
                    }
                }
            });
    }

    function out_manual($id_item,$id_jo,$qty_req,$unit){
        let id_item = $id_item;
        let id_jo = $id_jo;
        let qty_req = $qty_req;
        let unit = $unit;
        let no_bppb = $('#txt_nobppb').val();
        let tgl_bppb = $('#txt_tgl_bppb').val();

        getlist_showitem(id_item,id_jo);

        $('#m_qty_req').val(qty_req + ' ' + unit);
        $('#m_qty_req_h').val(qty_req);
        $('#m_no_bppb').val(no_bppb);
        $('#m_tgl_bppb').val(tgl_bppb);
        $('#m_qty_out').val('');
        $('#m_qty_out_h').val('');
        $('#m_qty_bal').val('');
        $('#m_qty_bal_h').val('');
        $('#modal-out-manual').modal('show');
    }

    function getlist_showitem($id_item,$id_jo){
        let iditem = $id_item;
        let idjo = $id_jo;
        return $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route("get-detail-showitem") }}',
                type: 'get',
                data: {
                    id_item: iditem,
                    id_jo: idjo,
                },
                success: function (res) {
                    if (res) {
                        document.getElementById('detail_showitem').innerHTML = res;
                        // $('#tableshow').dataTable({
                        //     "bFilter": false,
                        // });
                    }
                }
            });
    }

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

        // function calculateRatio(id) {
        //     let ratio = document.getElementById('ratio-'+id).value;
        //     let gelarQty = document.getElementById('gelar_marker_qty').value;
        //     document.getElementById('cut-qty-'+id).value = ratio * gelarQty;
        // }

        // function calculateAllRatio(element) {
        //     let gelarQty = element.value;

        //     for (let i = 0; i < datatable.data().count(); i++) {
        //         let ratio = document.getElementById('ratio-'+i).value;
        //         document.getElementById('cut-qty-'+i).value = ratio * gelarQty;
        //     }
        // }

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


    function showlocation($no_bppb, $no_ws, $styleno, $id_item, $id_jo, $qty_out, $item_desc){
        let no_bppb = $no_bppb;
        let no_ws = $no_ws;
        let styleno = $styleno;
        let id_item = $id_item;
        let id_jo = $id_jo;
        let qty_out = $qty_out;
        let item_desc = $item_desc;
        // alert(id_item);
        $('#mdl_no_bppb').val(no_bppb);
        $('#mdl_no_ws').val(no_ws);
        $('#mdl_style').val(styleno);
        $('#mdl_id_item').val(id_item);
        $('#mdl_id_jo').val(id_jo);
        $('#mdl_qty_out').val(Number(qty_out).toLocaleString('en-US'));
        $('#mdl_qty_out_h').val(qty_out);
        $('#mdl_catatan').val(item_desc);
        $('#modal-det-bppb').modal('show');
    }

    function getlist_bppbdet($no_bppb ,$id_jo, $id_item){
        let no_bppb = $no_bppb;
        let id_jo = $id_jo;
        let id_item = $id_item;
        return $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '{{ route("get-detail-bppb") }}',
            type: 'get',
            data: {
                no_bppb: no_bppb,
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
            let val = parseFloat($(this).find('td:eq(5)').text()) || 0; // kolom 5 = Qty Aktual
            total += val;
        });
        $('#mdl_qty_out').val(total.toFixed(2));
        $('#mdl_qty_out_h').val(total.toFixed(2));

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
        let val = parseFloat($(this).find('td:eq(5)').text()) || 0;
        total += val;
    });
    
    $('#mdl_qty_out').val(total.toFixed(2));
        $('#mdl_qty_out_h').val(total.toFixed(2));

}


    // Saat user mengubah qty di kolom qty aktual
    $(document).on('keyup blur', '#tableshow td:nth-child(6)', function() {
    let value = $(this).text().trim();

    if (value === '' || isNaN(value)) {
        $(this).css('background-color', '#f8d7da'); // merah muda
        $(this).text(''); // biarkan kosong
        updateQtyTotal(); // tetap hitung ulang total
    } else {
        $(this).css('background-color', '');
        updateQtyTotal();
    }
});


    // Jalankan pertama kali saat tabel dimuat
    updateQtyTotal();
});


function saveEditedBppbDet() {
    const rows = [];
    $("#tableshow tbody tr").each(function() {
        const id_roll = $(this).data('id_roll');
        const cols = $(this).find('td');
        rows.push({
            id_roll: id_roll,
            id_bppbdet: $(cols[6]).text().trim(),
            qty_out: $(cols[5]).text().trim(),
            no_bppb: $('#mdl_no_bppb').val(),
            id_item: $('#mdl_id_item').val(),
            id_jo: $('#mdl_id_jo').val(),
            qty_out_h: $('#mdl_qty_out_h').val(),
        });
    });

    Swal.fire({
        title: 'Konfirmasi',
        text: 'Simpan semua perubahan data?',
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
                url: '{{ route("update-detail-barcode-bppb") }}',
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

function deleteData(no_bppb, id_jo, id_item) {
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
                url: '{{ route("delete-detail-barcode-bppb") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    no_bppb: no_bppb,
                    id_jo: id_jo,
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

    </script>
@endsection
