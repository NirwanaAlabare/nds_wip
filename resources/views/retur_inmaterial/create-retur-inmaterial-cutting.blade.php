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
<form action="{{ route('store-retur-inmaterial-fabric') }}" method="post" id="store-inmaterial" onsubmit="submitForm(this, event)">
    @csrf
    <div class="card card-sb card-outline">
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
            <div class="col-6 col-md-4">
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-1">
                            <div class="form-group">
                                <label><small>No RI</small></label>
                                @foreach ($kode_gr as $kodegr)
                                <input type="text" class="form-control " id="txt_no_ri" name="txt_no_ri" value="{{ $kodegr->kode }}" readonly>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-1">
                            <div class="form-group">
                                <label><small>Tgl RI</small></label>
                                <input type="date" class="form-control form-control" id="txt_tgl_ri" name="txt_tgl_ri"
                                value="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-1">
                            <div class="form-group">
                                <label><small>Tgl SJ</small></label>
                                <input type="date" class="form-control form-control" id="txt_tgl_sj" name="txt_tgl_sj"
                                value="{{ date('Y-m-d') }}" onchange="get_nobppb(this.value)">
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="mb-1">
                            <div class="form-group">
                                <label><small>Surat jalan Asal</small></label>
                                <select class="form-control select2bs4" id="txt_sj_asal" name="txt_sj_asal" style="width: 100%;" onchange="get_supplier();getlistdata();">
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="mb-1">
                            <div class="form-group">
                                <label><small>Supplier</small></label>
                                <input type="text" class="form-control " id="txt_supp" name="txt_supp" value="" readonly>
                                <input type="hidden" class="form-control " id="txt_idsupp" name="txt_idsupp" value="" readonly>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="row">

                    <div class="col-md-12">
                        <div class="mb-1">
                            <div class="form-group">
                                <label><small>Jenis Retur</small></label>
                                <select class="form-control select2bs4" id="txt_jns_rtr" name="txt_jns_rtr" style="width: 100%;" >
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="mb-1">
                            <div class="form-group">
                                <label><small>Tipe BC</small></label>
                                <select class="form-control select2bs4" id="txt_type_bc" name="txt_type_bc" style="width: 100%;" onchange="get_tujuan(this.value)">
                                    <option selected="selected" value="">Pilih Tipe</option>
                                    @foreach ($mtypebc as $bc)
                                    <option value="{{ $bc->nama_pilihan }}">
                                        {{ $bc->nama_pilihan }}
                                    </option>
                                    @endforeach
                                </select>

                                <select class="form-control hidden" id="txt_type_pch" name="txt_type_pch" style="width: 100%;">
                                    <option selected="selected" value="Pengembalian dari Produksi">Pengembalian dari Produksi</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="mb-1">
                            <div class="form-group">
                                <label><small>Tujuan Pemasukan</small></label>
                                <select class="form-control select2bs4" id="txt_tujuan" name="txt_tujuan" style="width: 100%;">
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="mb-1">
                            <div class="form-group">
                                <label><small>Nomor KK BC</small></label>
                                <input type="text" class="form-control " id="txt_no_kk" name="txt_no_kk" value="" >
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-5">
                <div class="row">

                    <div class="col-md-7">
                        <div class="mb-1">
                            <div class="form-group">
                                <label><small>No Aju</small></label>
                                <input type="text" class="form-control " id="txt_aju_num" name="txt_aju_num" value="" >
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
                                <label><small>No Faktur Pajak</small></label>
                                <input type="text" class="form-control " id="txt_faktur" name="txt_faktur" value="" >
                            </div>
                        </div>
                    </div>

                    <div class="col-md-5">
                        <div class="mb-1">
                            <div class="form-group">
                                <label><small>Tgl Faktur Pajak</small></label>
                                <input type="date" class="form-control form-control" id="txt_tgl_faktur" name="txt_tgl_faktur"
                                value="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                    </div>

                    <div class="col-md-7">
                        <div class="mb-1">
                            <div class="form-group">
                                <label><small>No Daftar</small></label>
                                <input type="text" class="form-control " id="txt_reg" name="txt_reg" value="" >
                            </div>
                        </div>
                    </div>

                    <div class="col-md-5">
                        <div class="mb-1">
                            <div class="form-group">
                                <label><small>Tgl Daftar</small></label>
                                <input type="date" class="form-control form-control" id="txt_tgl_reg" name="txt_tgl_reg"
                                value="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                    </div>

                    <div class="col-md-7">
                        <div class="mb-1">
                            <div class="form-group">
                                <label><small>No Invoice/ No SJ</small></label>
                                <input type="text" class="form-control " id="txt_noinvoice" name="txt_noinvoice" value="" >
                            </div>
                        </div>
                    </div>

                    <div class="col-md-5">
                        <div class="mb-1">
                            <div class="form-group">
                                <label><small>Jenis Material</small></label>
                                <input type="text" class="form-control " id="txt_tom" name="txt_tom" value="Fabric" readonly>
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

<div class="card card-sb card-outline">
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
                <table id="datatable" class="table table-bordered table-head-fixed table-striped table-sm w-100 text-nowrap">
                    <thead>
                        <tr>
                            <th class="text-center" style="font-size: 0.6rem;width: 300px;">WS</th>
                            <th class="text-center" style="font-size: 0.6rem;width: 300px;">Kode Barang</th>
                            <th class="text-center" style="font-size: 0.6rem;width: 300px;">Deskripsi</th>
                            <th class="text-center" style="font-size: 0.6rem;width: 300px;">Qty SJ</th>
                            <th class="text-center" style="font-size: 0.6rem;width: 300px;">Qty Return</th>
                            <th class="text-center" style="font-size: 0.6rem;width: 300px;">Qty Reject</th>
                            <th class="text-center" style="font-size: 0.6rem;width: 300px;">Satuan</th>
                            <th class="text-center" style="font-size: 0.6rem;width: 300px;">Berat Kotor</th>
                            <th class="text-center" style="font-size: 0.6rem;width: 300px;">Berat Bersih</th>
                            <th class="text-center" style="font-size: 0.6rem;width: 300px;">Keterangan</th>
                            <th class="text-center" style="display: none;">Keterangan</th>
                            <th class="text-center" style="display: none;">Keterangan</th>
                            <th class="text-center" style="display: none;">Keterangan</th>
                            <th class="text-center" style="display: none;">Keterangan</th>
                            <th class="text-center" style="font-size: 0.6rem;width: 300px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mb-1">
            <div class="form-group">
                <button class="btn btn-sb float-end mt-2 ml-2"><i class="fa-solid fa-floppy-disk"></i> Simpan</button>
                <a href="{{ route('retur-inmaterial') }}" class="btn btn-danger float-end mt-2">
                    <i class="fas fa-arrow-circle-left"></i> Kembali</a>
                </div>
            </div>
        </div>
    </div>
</div>
</form>

<div class="modal fade modal-out-barcode" id="modal-out-barcode">
    <form action="{{ route('save-barcode-ri-scan') }}" method="post" onsubmit="submitFormScan(this, event)">
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
                            <label><small>Qty RI</small></label>
                                <input type="text" class="form-control " id="m_qty_out2" name="m_qty_out2" value="" readonly>
                                <input type="hidden" class="form-control " id="m_qty_out_h2" name="m_qty_out_h2" value="" readonly>
                                <input type="hidden" class="form-control " id="m_qty_req2" name="m_qty_req2" value="" readonly>
                                <input type="hidden" class="form-control " id="m_qty_req_h2" name="m_qty_req_h2" value="" readonly>
                                <input type="hidden" class="form-control " id="m_qty_bal2" name="m_qty_bal2" value="" readonly>
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
        })

        $('.select2barcode').select2({
            theme: 'bootstrap4'
        })

        $('.select2roll').select2({
            theme: 'bootstrap4'
        })

        $('.select2supp').select2({
            theme: 'bootstrap4'
        })

        $('.select2lok').select2({
            theme: 'bootstrap4',
            dropdownParent: $('.modal-out-barcode')
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

        

        function get_nobppb(val) {
           return $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '{{ route("get-no-bppb-cutting") }}',
            type: 'get',
            data: {
                tgl_ri: $('#txt_tgl_sj').val(),
            },
            success: function (res) {
                if (res) {
                    document.getElementById('txt_sj_asal').innerHTML = res;
                }
            },
        });
       }


       function get_tujuan(val) {
           return $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '{{ route("get-tujuan-pemasukan") }}',
            type: 'get',
            data: {
                type_bc: $('#txt_type_bc').val(),
            },
            success: function (res) {
                if (res) {
                    document.getElementById('txt_tujuan').innerHTML = res;
                }
            },
        });
       }

       function get_supplier() {
           return $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '{{ route("get-supplier-ri") }}',
            type: 'get',
            data: {
                no_bppb: $('#txt_sj_asal').val(),
            },
            success: function (res) {
                if (res) {
                        // console.log(res[0].jml)
                        $('#txt_supp').val(res[0].supplier);
                        $('#txt_idsupp').val(res[0].id_supplier);
                    }
                },
            });
       }


        // function getlistdata(val){
        //     datatable.ajax.reload();
        // }

        async function getlistdata() {
            return datatable.ajax.reload(() => {
                document.getElementById('jumlah_data').value = datatable.data().count();
            });
        }

        let datatable = $("#datatable").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            paging: false,
            searching: false,
            ajax: {
                url: '{{ route("get-list-bppb") }}',
                data: function (d) {
                    d.sj_asal = $('#txt_sj_asal').val();
                    // alert(d.name_fill);
                },
            },
            columns: [
            {
                data: 'kpno'
            },
            {
                data: 'goods_code'
            } ,
            {
                data: 'itemdesc'
            },
            {
                data: 'qty'
            },
            {
                data: 'qty'
            },
            {
                data: 'qty'
            },
            {
                data: 'unit'
            },
            {
                data: 'id_item'
            },
            {
                data: 'id_item'
            },
            {
                data: 'id_item'
            },
            {
                data: 'id_item'
            },
            {
                data: 'id_jo'
            },
            {
                data: 'id_bppb'
            },
            {
                data: 'kpno'
            },
            {
                data: 'id_item'
            }
            ],
            columnDefs: [
            {
                targets: [4],
                render: (data, type, row, meta) => '<input style="width:100px;text-align:right;" class="form-control-sm" type="text" min="0" max="' + data + '" id="qty_retur' + meta.row + '" name="qty_retur['+meta.row+']" value = "'+row.qty_temp+'" onkeyup="tambahqty(this.value)" readonly />'
            },
            {
                targets: [5],
                className: 'd-none',
                render: (data, type, row, meta) => '<input style="width:100px;text-align:right;" class="form-control-sm" type="text" min="0" max="' + data + '" id="qty_reject' + meta.row + '" name="qty_reject['+meta.row+']" />'
            },
            {
                targets: [7],
                className: 'd-none',
                render: (data, type, row, meta) => '<input style="width:100px;text-align:right;" class="form-control-sm" type="text" id="bruto' + meta.row + '" name="bruto['+meta.row+']" />'
            },
            {
                targets: [8],
                className: 'd-none',
                render: (data, type, row, meta) => '<input style="width:100px;text-align:right;" class="form-control-sm" type="text" id="neto' + meta.row + '" name="neto['+meta.row+']" />'
            },
            {
                targets: [9],
                render: (data, type, row, meta) => '<input style="width:250px;" class="form-control-sm" type="text" id="keterangan' + meta.row + '" name="keterangan['+meta.row+']" />'
            },
            {
                targets: [10],
                className: "d-none",
                render: (data, type, row, meta) => '<input type="hidden" id="id_item' + meta.row + '" name="id_item['+meta.row+']" value="' + data + '" readonly />'
            },
            {
                targets: [11],
                className: "d-none",
                render: (data, type, row, meta) => '<input type="hidden" id="id_jo' + meta.row + '" name="id_jo['+meta.row+']" value="' + data + '" readonly />'
            },
            {
                targets: [12],
                className: "d-none",
                render: (data, type, row, meta) => '<input type="hidden" id="id_bppb' + meta.row + '" name="id_bppb['+meta.row+']" value="' + data + '" readonly />'
            },
            {
                targets: [13],
                className: "d-none",
                render: (data, type, row, meta) => '<input type="hidden" id="no_ws' + meta.row + '" name="no_ws['+meta.row+']" value="' + data + '" readonly />'
            },
            {
                targets: [14],
                render: (data, type, row, meta) => {
                    return `<div class='d-flex gap-1 justify-content-center'>
                    <button type='button' class='btn btn-sm btn-success' href='javascript:void(0)' onclick='ri_scan("` + row.id_item + `","` + row.id_jo + `","` + row.bppbno_int + `")'><i class="fa-solid fa-barcode"></i></i></button>
                    <button type='button' class='btn btn-sm btn-danger' href='javascript:void(0)' onclick='delete_scan("` + row.id_item + `","` + row.id_jo + `")'><i class="fa-solid fa-undo"></i></i></button>
                    </div>`;
                }
            }

            ]
        });

function delete_scan($id_item,$id_jo){
        let id_item = $id_item;
        let id_jo = $id_jo;
        return $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route("delete-scanri-temp") }}',
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

function tambahqty($val){
    var table = document.getElementById("datatable");
    var qty = 0;
    var jml_qty = 0;

    for (var i = 1; i < (table.rows.length); i++) {
        qty = document.getElementById("datatable").rows[i].cells[4].children[0].value || 0;
        jml_qty += parseFloat(qty) ;
    }

    $('#jumlah_qty').val(jml_qty);

}

function ri_scan($id_item,$id_jo,$bppbno_int){
    let id_item = $id_item;
    let id_jo = $id_jo;
    let bppbno_int = $bppbno_int;
    let tgl_bpb = $('#txt_tgl_ri').val();

    getlist_barcode(id_item,id_jo,bppbno_int);

        // $('#m_qty_req').val(qty_req + ' ' + unit);
        document.getElementById('txt_barcode').innerHTML = '';
        document.getElementById('detail_showbarcode').innerHTML = '';
        $('#m_qty_req2').val('');
        $('#m_qty_req_h2').val('');
        $('#m_no_bppb2').val(bppbno_int);
        $('#m_tgl_bppb2').val(tgl_bpb);
        $('#m_qty_out2').val('');
        $('#m_qty_out_h2').val('');
        $('#m_qty_bal2').val('');
        $('#m_qty_bal_h2').val('');
        $('#modal-out-barcode').modal('show');  
    }

    function getlist_barcode($id_item,$id_jo,$bppbno_int){
        let iditem = $id_item;
        let idjo = $id_jo;
        let bppbno_int = $bppbno_int;
        return $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route("get-list-barcode-out") }}',
                type: 'get',
                data: {
                    id_item: iditem,
                    id_jo: idjo,
                    bppbno_int: bppbno_int,
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
        let no_bppb = $('#m_no_bppb2').val();
        let text1 = "'";
        let kodenya = text1.concat(id_barcode, "'");
        let kodebarcode = kodenya.toString();
        let barcode = kodebarcode.replace(/,/g,"','");
        return $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route("get-data-barcode-out") }}',
                type: 'get',
                data: {
                    id_barcode: barcode,
                    no_bppb: no_bppb,
                },
                success: function (res) {
                    if (res) {
                        document.getElementById('detail_showbarcode').innerHTML = res;
                        $('.select2lok').select2({
                            theme: 'bootstrap4',
                            dropdownParent: $('.modal-out-barcode')
                        });
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

                h_qty_out = sum_out.round(2) + ' ' + satuan;
                sum_bal = parseFloat(qty_req) - parseFloat(sum_out);
                h_sum_bal = sum_bal.round(2) + ' ' + satuan;
                $('#m_qty_out2').val(h_qty_out);
                $('#m_qty_out_h2').val(sum_out.round(2));
                $('#m_qty_bal2').val(h_sum_bal);
                $('#m_qty_bal_h2').val(sum_bal.round(2));

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
                    if (res.redirect != '') {
                        if (res.redirect != 'reload') {
                            location.href = res.redirect;
                        } else {
                            location.reload();
                        }
                    }
                    getlistdata();
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
</script>
@endsection
