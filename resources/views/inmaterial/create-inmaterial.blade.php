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
    <div class="card-body">
    <div class="form-group row">
    <div class="col-md-4">
        <div class="row">
            <div class="col-md-12">
            <div class="mb-1">
                <div class="form-group">
                <label><small>No BPB</small></label>
                @foreach ($kode_gr as $kodegr)
                <input type="text" class="form-control " id="txt_gr_dok" name="txt_gr_dok" value="{{ $kodegr->kode }}" readonly>
                @endforeach
                </div>
            </div>
            </div>

            <div class="col-md-6">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Tgl BPB</small></label>
                <input type="date" class="form-control form-control" id="txt_tgl_gr" name="txt_tgl_gr"
                        value="{{ date('Y-m-d') }}">
                </div>
            </div>
            </div>

            <div class="col-md-6">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Tgl Pengiriman</small></label>
                <input type="date" class="form-control form-control" id="txt_tgl_ship" name="txt_tgl_ship"
                        value="{{ date('Y-m-d') }}">
                </div>
            </div>
            </div>

            <div class="col-md-12">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Supplier</small></label>
                <select class="form-control select2supp" id="txt_supp" name="txt_supp" style="width: 100%;" onchange="settype()">
                    <option selected="selected" value="">Pilih Supplier</option>
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
                    <option selected="selected" value="">Pilih Tipe</option>
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
                <select class="form-control select2bs4" id="txt_po" name="txt_po" style="width: 100%;" onchange="getlistdata(this.value)">
                </select>
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
                <select class="form-control select2bs4" id="txt_wsglobal" name="txt_wsglobal" style="width: 100%;" onchange="getlistdata(this.value)">
                </select>
                </div>
            </div>
            </div>

            <div class="col-md-12">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Tipe BC</small></label>
                <select class="form-control select2bs4" id="txt_type_bc" name="txt_type_bc" style="width: 100%;">
                    <option selected="selected" value="">Pilih Tipe</option>
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
                    <option selected="selected" value="">Pilih Tipe</option>
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
                <input type="text" class="form-control " id="txt_invdok" name="txt_invdok" value="" >
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
                <label><small>No Daftar</small></label>
                <input type="text" class="form-control " id="txt_reg_num" name="txt_reg_num" value="" >
                </div>
            </div>
            </div>

            <div class="col-md-5">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Tgl Daftar</small></label>
                <input type="date" class="form-control form-control" id="txt_reg_aju" name="txt_reg_aju"
                        value="{{ date('Y-m-d') }}">
                </div>
            </div>
            </div>

            <div class="col-md-7">
            <div class="mb-1">
                <div class="form-group">
                <label><small>No Kontrak</small></label>
                <input type="text" class="form-control " id="txt_kontrak" name="txt_kontrak" value="" >
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
                <textarea type="text" rows="4" class="form-control " id="txt_notes" name="txt_notes" value="" > </textarea>
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
                        <th class="text-center" style="font-size: 0.6rem;width: 300px;">No WS</th>
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
                        <th class="text-center" style="font-size: 0.6rem;"></th>
                        <th class="text-center" style="font-size: 0.6rem;"></th>
                        <th class="text-center" style="font-size: 0.6rem;"></th>
                        <th class="text-center" style="font-size: 0.6rem;"></th>
                        <th class="text-center" style="font-size: 0.6rem;"></th>
                        <th class="text-center" style="font-size: 0.6rem;"></th>
                        <th class="text-center" style="font-size: 0.6rem;"></th>
                        <th class="text-center" style="font-size: 0.6rem;"></th>
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
                    <a href="{{ route('in-material') }}" class="btn btn-danger float-end mt-2">
                    <i class="fas fa-arrow-circle-left"></i> Kembali</a>
                </div>
            </div>
        </div>
        </div>
    </div>
</form>
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

        $('.select2roll').select2({
            theme: 'bootstrap4'
        })

        $('.select2supp').select2({
            theme: 'bootstrap4'
        })

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
                url: '{{ route("get-marker-panels") }}',
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
                getPO();

            }else if(type == 'CMT'){
                // $("#txt_po").prop("disabled", true);
                // $("#txt_po").val('');
                // $("#txt_po").text('');
                getWS();
                getPO();
            }else{
            }
        }


        function getPO() {
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
        }


        function getWS() {
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
                url: '{{ route("get-detail-list") }}',
                data: function (d) {
                    d.txt_supp = $('#txt_supp').val();
                    d.txt_fill = $('#txt_po').val() ? $('#txt_po').val() : $('#txt_wsglobal').val();
                    d.name_fill = $('#txt_po').val() ? 'PO' : 'WS';
                    // alert(d.name_fill);
                },
            },
            columns: [
                {
                    data: 'kpno'
                },
                {
                    data: 'id_jo'
                } ,
                {
                    data: 'id_item'
                },
                {
                    data: 'goods_code'
                },
                {
                    data: 'produk'
                },
                {
                    data: 'itemdesc'
                },
                {
                    data: 'qty'
                },
                {
                    data: 'unit'
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
                    data: 'qty'
                },
                {
                    data: 'unit'
                },
                {
                    data: 'kpno'
                },
                {
                    data: 'id_jo'
                },
                {
                    data: 'id_item'
                },
                {
                    data: 'goods_code'
                },
                {
                    data: 'produk'
                },
                {
                    data: 'itemdesc'
                },
                {
                    data: 'qty'
                },
                {
                    data: 'unit'
                }
            ],
            columnDefs: [
                {
                    targets: [13],
                    className: "d-none",
                    render: (data, type, row, meta) => '<input type="hidden" id="det_kpno' + meta.row + '" name="det_kpno['+meta.row+']" value="' + data + '" readonly />'
                },
                {
                    targets: [14],
                    className: "d-none",
                    render: (data, type, row, meta) => '<input type="hidden" id="det_idjo' + meta.row + '" name="det_idjo['+meta.row+']" value="' + data + '" readonly />'
                },
                {
                    targets: [15],
                    className: "d-none",
                    render: (data, type, row, meta) => '<input type="hidden" id="det_iditem' + meta.row + '" name="det_iditem['+meta.row+']" value="' + data + '" readonly />'
                },
                {
                    targets: [16],
                    className: "d-none",
                    render: (data, type, row, meta) => '<input type="hidden" id="det_code' + meta.row + '" name="det_code['+meta.row+']" value="' + data + '" readonly />'
                },
                {
                    targets: [17],
                    className: "d-none",
                    render: (data, type, row, meta) => '<input type="hidden" id="det_produk' + meta.row + '" name="det_produk['+meta.row+']" value="' + data + '" readonly />'
                },
                {
                    targets: [18],
                    className: "d-none",
                    render: (data, type, row, meta) => '<input type="hidden" id="det_itemdesc' + meta.row + '" name="det_itemdesc['+meta.row+']" value="' + data + '" readonly />'
                },
                {
                    targets: [19],
                    className: "d-none",
                    render: (data, type, row, meta) => '<input type="hidden" id="det_qty' + meta.row + '" name="det_qty['+meta.row+']" value="' + data + '" readonly />'
                },
                {
                    targets: [20],
                    className: "d-none",
                    render: (data, type, row, meta) => '<input type="hidden" id="det_unit' + meta.row + '" name="det_unit['+meta.row+']" value="' + data + '" readonly />'
                },

                {
                    targets: [9],
                    render: (data, type, row, meta) => {
                        // alert(meta.row)
                    return '<input style="width:100px;" class="form-control-sm" type="text" min="0" max="' + data + '" id="qty_good' + meta.row + '" name="qty_good['+meta.row+']" onkeyup="tambahqty(this.value)" />';
                }

                },
                {
                    targets: [11],
                    render: (data, type, row, meta) => '<input style="width:100px;" class="form-control-sm" type="text" min="0" max="' + data + '" id="qty_reject' + meta.row + '" name="qty_reject['+meta.row+']" />'
                }
            ]
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
    </script>
@endsection
