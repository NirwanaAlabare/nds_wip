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
@foreach ($d_header as $data_header)
    <div class="card-body">
    <div class="form-group row">
    <div class="col-md-3">
        <div class="row">
            <div class="col-md-12">
            <div class="mb-1">
                <div class="form-group">
                <label><small>No Return</small></label>
                <input type="text" class="form-control " id="txt_no_bppb" name="txt_no_bppb" value="{{ $data_header->no_bppb }}" readonly>
                <input type="hidden" class="form-control " id="txt_idbppb" name="txt_idbppb" value="{{ $data_header->id }}" readonly>
                </div>
            </div>
            </div>

            <div class="col-md-12">
            <div class="mb-1">
                <div class="form-group">
                <label><small>No PO</small></label>
                <input type="text" class="form-control " id="txt_no_po" name="txt_no_po" value="{{ $data_header->no_po }}" readonly>
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
                <label><small>Tgl Return</small></label>
                <input type="date" class="form-control form-control" id="tgl_return" name="tgl_return" value="{{ $data_header->tgl_bppb }}">
                </div>
            </div>
            </div>

            <div class="col-md-12">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Jenis Defect</small></label>
                <input type="text" class="form-control " id="txt_jns_defect" name="txt_jns_defect" value="{{ $data_header->jns_defect }}" readonly>
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
                <label><small>Tujuan</small></label>
                <input type="text" class="form-control " id="txt_tujuan" name="txt_tujuan" value="{{ $data_header->tujuan }}" readonly>
                </div>
            </div>
            </div>

            <div class="col-md-12">
            <div class="mb-1">
                <div class="form-group">
                <label><small>No BPB</small></label>
                <input type="text" class="form-control " id="txt_no_bpb" name="txt_no_bpb" value="{{ $data_header->no_bpb }}" readonly>
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
                <label><small>Dokumen BC</small></label>
                <input type="text" class="form-control " id="txt_dok_bc" name="txt_dok_bc" value="{{ $data_header->dok_bc }}" readonly>
                </div>
            </div>
            </div>

            <div class="col-md-12">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Tgl BPB</small></label>
                <input type="date" class="form-control form-control" id="tgl_bpb" name="tgl_bpb" value="{{ $data_header->tgl_dok }}">
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
        <div class="d-flex justify-content-between">
            <div class="ml-auto">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
            </div>
                <input type="text"  id="cari_item" name="cari_item" autocomplete="off" placeholder="Search Item..." onkeyup="cariitem()">
        </div>
    <div class="table-responsive" style="max-height: 400px">
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
                            <div class='d-flex gap-1 justify-content-center'>
                                <button type='button' class='btn btn-sm btn-warning' href='javascript:void(0)' onclick="out_scan('{{$detdata->id_item}}','{{$detdata->id_jo}}','{{$detdata->qty_sisa}}','{{$detdata->unit}}','{{$detdata->no_ws}}')"><i class="fa-solid fa-circle-plus fa-lg"></i></button>
                            </div>
                            @endif
                            @if($detdata->qty_sisa <= 0)
                            <div class='d-flex gap-1 justify-content-center'>
                                <button type='button' class='btn btn-sm btn-success' href='javascript:void(0)' onclick='showlocation("{{$detdata->no_ws}}","{{$detdata->id_jo}}","{{$detdata->id_item}}","{{$detdata->kode_item}}","{{$detdata->qty_good}}","{{$detdata->unit}}","{{$detdata->qty_good}}","{{$detdata->desc_item}}","{{$detdata->qty_sisa}}");getlist_showlokasi("{{$detdata->no_ws}}","{{$detdata->id_jo}}","{{$detdata->id_item}}")'><i class="fa-solid fa-clipboard-check fa-lg"></i></button>
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
                    <a href="{{ route('retur-material') }}" class="btn btn-danger float-end mt-2">
                    <i class="fas fa-arrow-circle-left"></i> Kembali</a>
                </div>
            </div>
        </div>
        </div>
    </div>
</form>

<div class="modal fade" id="modal-out-barcode">
    <form action="{{ route('save-ro-scan') }}" method="post" onsubmit="submitForm(this, event)">
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

        $('.select2barcode').select2({
            theme: 'bootstrap4'
        })

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

        function out_scan($id_item,$id_jo,$qty_req,$unit,$noreq){
        let id_item = $id_item;
        let id_jo = $id_jo;
        let qty_req = $qty_req;
        let unit = $unit;
        let no_bppb = $('#txt_no_bppb').val();
        let no_bpb = $('#txt_no_bpb').val();
        let tgl_bppb = $('#tgl_return').val();
        let noreq = $noreq;

        getlist_barcode(id_item,id_jo,no_bpb);

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


    function getlist_barcode($id_item,$id_jo,$no_bpb){
        let iditem = $id_item;
        let idjo = $id_jo;
        let no_bpb = $no_bpb;
        return $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route("ro-list-barcode") }}',
                type: 'get',
                data: {
                    id_item: iditem,
                    id_jo: idjo,
                    no_bpb: no_bpb,
                },
                success: function (res) {
                    if (res) {
                        document.getElementById('txt_barcode').innerHTML = res;
                        $("#txt_barcode").focus();
                    }
                }
            });
    }

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
                url: '{{ route("get-data-barcode-ro") }}',
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
                satuan = document.getElementById("tableshow").rows[i].cells[6].children[0].value;
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

                h_qty_out = (sum_out.round(2) || 0) + ' ' + satuan;
                sum_bal = parseFloat(qty_req) - parseFloat(sum_out);
                h_sum_bal = (sum_bal.round(2) || 0) + ' ' + satuan;
                $('#m_qty_out2').val(h_qty_out);
                $('#m_qty_out_h2').val(sum_out.round(2) || 0);
                $('#m_qty_bal2').val(h_sum_bal);
                $('#m_qty_bal_h2').val(sum_bal.round(2) || 0);

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
                            title: 'Add data Succesfully',
                            showCancelButton: false,
                            showConfirmButton: true,
                            confirmButtonText: 'Oke',
                            timer: 5000,
                            timerProgressBar: true
                        })

                        $('#modal-add-lokasi').modal('hide');
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
