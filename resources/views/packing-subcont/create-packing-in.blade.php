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
<form action="{{ route('store-packing-in-subcont') }}" method="post" id="store-outmaterial-fabric" onsubmit="submitForm(this, event)">
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
                <label><small>No Transaksi</small></label>
                @foreach ($kode_gr as $kodegr)
                <input type="text" class="form-control " id="txt_nobppb" name="txt_nobppb" value="{{ $kodegr->no_bpb }}" readonly>
                @endforeach
                <!-- <input type="text" class="form-control " id="txt_nobppb" name="txt_nobppb" value="SPCK/OUT/1225/00001" readonly> -->
                </div>
            </div>
            </div>

            <div class="col-md-12">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Tgl Transaksi</small></label>
                <input type="date" class="form-control form-control" id="txt_tgl_bppb" name="txt_tgl_bppb"
                        value="{{ date('Y-m-d') }}">
                </div>
            </div>
            </div>

            <div class="col-md-12">
            <div class="mb-1">
                <div class="form-group">
                <label><small>No PO</small></label>
                <select class="form-control select2bs4" id="txt_no_po" name="txt_no_po" style="width: 100%;" onchange="detail_po(this.value)">
                    <option selected="selected" value="">Pilih PO</option>
                        @foreach ($no_po as $po)
                    <option value="{{ $po->pono }}">
                                {{ $po->pono }}
                    </option>
                        @endforeach
                </select>
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
                    <label><small>Supplier</small></label>
                    <input type="text" class="form-control " id="txt_supplier" name="txt_supplier" value="" readonly>
                    <input type="hidden" class="form-control " id="txt_idsupplier" name="txt_idsupplier" value="" readonly>
                    <!-- <select class="form-control select2bs4" id="txt_supp" name="txt_supp" style="width: 100%;">
                        <option selected="selected" value="">Pilih Supplier</option>
                        @foreach ($msupplier as $msupp)
                        <option value="{{ $msupp->id_supplier }}">
                            {{ $msupp->Supplier }}
                        </option>
                        @endforeach
                    </select> -->
                </div>
            </div>
            </div>

            <div class="col-md-12">
            <div class="mb-1">
                <div class="form-group">
                <label><small>Jenis Penerimaan</small></label>
                <select class="form-control select2bs4" id="txt_jns_klr" name="txt_jns_klr" style="width: 100%;">
                    <option selected="selected" value="">Pilih Jenis Penerimaan</option>
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
                <label><small>Dokumen BC</small></label>
                <select class="form-control select2bs4" id="txt_dok_bc" name="txt_dok_bc" style="width: 100%;">
                    <option selected="selected" value="">Pilih Dokumen</option>
                    @foreach ($mtypebc as $bc)
                    <option value="{{ $bc->nama_pilihan }}">
                        {{ $bc->nama_pilihan }}
                    </option>
                    @endforeach
                </select>
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
            <label><small>No Invoice</small></label>
            <input type="text" class="form-control " id="txt_invdok" name="txt_invdok"
            value="">
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
                        <th class="text-center" style="font-size: 0.6rem;width: 300px;">No WS</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 300px;">Styleno</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 300px;">Job Order</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 300px;">ID Item</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 300px;">Item Desc</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 300px;">Unit</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 300px;">Qty Pengiriman</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 300px;">Qty Penerimaan</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 300px;">Qty Input</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 300px;">Qty Reject Input</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 300px;">Outstanding</th>
                        <th class="text-center" style="font-size: 0.6rem;width: 300px;">Add Data</th>
                        <th class="text-center" style="display: none;"></th>
                        <th class="text-center" style="display: none;"></th>
                        <th class="text-center" style="display: none;"></th>
                        <th class="text-center" style="display: none;"></th>
                        <th class="text-center" style="display: none;"></th>
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
                    <a href="{{ route('packing-in-subcont') }}" class="btn btn-danger float-end mt-2" onclick="delete_all_temp()">
                    <i class="fas fa-arrow-circle-left"></i> Kembali</a>
                </div>
            </div>
        </div>
        </div>
    </div>
</form>

<div class="modal fade" id="modal_add_detail">
    <form action="{{ route('save-in-detail-temp') }}" method="post" onsubmit="submitFormScan(this, event)">
         @method('POST')
        <div class="modal-dialog modal-lg">
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
                    <div class="row">
                        <div class="col-12 col-md-12">
                        <div class="mb-1">
                        <div class="form-group">
                            <label><small>Buyer</small></label>
                                <input type="text" class="form-control " id="mdl_buyer" name="mdl_buyer" value="" readonly>
                        </div>
                        </div>
                        </div>
                        <div class="col-6 col-md-6">
                        <div class="mb-1">
                        <div class="form-group">
                            <label><small>NO WS</small></label>
                                <input type="text" class="form-control " id="mdl_ws" name="mdl_ws" value="" readonly>
                        </div>
                        </div>
                        </div>
                        <div class="col-6 col-md-3">
                        <div class="mb-1">
                        <div class="form-group">
                            <label><small>Qty</small></label>
                                <div class="input-group">
                                    <input type="text" class="form-control " style="text-align:right;" id="mdl_qty" name="mdl_qty" value="" readonly>
                                    <input type="text" class="form-control bg-success text-white text-center" value="PCS" readonly style="max-width: 70px;">
                                </div>
                                <input type="hidden" class="form-control " id="mdl_qty_h" name="mdl_qty_h" value="" readonly>
                        </div>
                        </div>
                        </div>
                        <div class="col-6 col-md-3">
                        <div class="mb-1">
                        <div class="form-group">
                            <label><small>Qty Reject</small></label>
                                <div class="input-group">
                                    <input type="text" class="form-control " style="text-align:right;" id="mdl_qty_reject" name="mdl_qty_reject" value="" readonly>
                                    <input type="text" class="form-control bg-danger text-white text-center" value="PCS" readonly style="max-width: 70px;">
                                </div>
                                <input type="hidden" class="form-control " id="mdl_qty_reject_h" name="mdl_qty_reject_h" value="" readonly>
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

    $("#color").prop("disabled", true);
    $("#panel").prop("disabled", true);
    $('#p_unit').val("yard").trigger('change');

    // Reset Form
    // Reset Form
if (document.getElementById('store-outmaterial-fabric')) {
    document.getElementById('store-outmaterial-fabric').reset();

    // Reset semua select2 juga
    $('.select2, .select2bs4, .select2barcode, .select2req').val(null).trigger('change');
    getlistdata();
}

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
                    if (res.redirect != '') {
                        if (res.redirect != 'reload') {
                            location.href = res.redirect;
                        } else {
                            location.reload();
                        }
                    }
                    detail_po();
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

        function getsupplier(){
            var pono = $('#txt_no_po').val();
        return $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route("get-supplier-subcont") }}',
                type: 'get',
                data: {
                    pono: pono,
                },
                success: function (res) {
                    if (res) {
                        $('#txt_supplier').val(res[0].supplier);
                        $('#txt_idsupplier').val(res[0].id_supplier);
                    }
                },
            });
    }

        async function detail_po() {
            return datatable.ajax.reload(() => {
                document.getElementById('jumlah_data').value = datatable.data().count();
                getsupplier();
            });
        }

        let datatable = $("#datatable").DataTable({
            ordering: false,
            processing: true,
            serverSide: false,
            paging: false,
            searching: true,
            scrollY: '300px',
            scrollX: '300px',
            scrollCollapse: true,
            ajax: {
                url: '{{ route("get-detail-item-in-subcont") }}',
                data: function (d) {
                    d.pono = $('#txt_no_po').val();
                    // alert(d.no_jo);
                    console.log(d.pono);
                },
            },
            columns: [
                {
                    data: 'kpno'
                },
                {
                    data: 'styleno'
                } ,
                {
                    data: 'jo_no'
                },
                {
                    data: 'id_item'
                },
                {
                    data: 'itemdesc'
                },
                {
                    data: 'unit'
                },
                {
                    data: 'qty'
                },
                {
                    data: 'qty_terima'
                },
                {
                    data: 'qty_input'
                },
                {
                    data: 'qty_input_reject'
                },
                {
                    data: 'qty_balance'
                },
                {
                    data: 'id_po'
                },
                {
                    data: 'id_jo'
                },
                {
                    data: 'id_item'
                },
                {
                    data: 'unit'
                },
                {
                    data: 'id_po'
                },
                {
                    data: 'kpno'
                }
            ],
            columnDefs: [
                {
                    targets: [6,7,10],
                    render: (data, type, row, meta) => data ? data : "0"
                },
                {
                    targets: [8],
                    // className: "d-none",
                    render: (data, type, row, meta) => '<input style="width:80px;text-align:center;" type="text" id="input_qty' + meta.row + '" name="input_qty['+meta.row+']" value="' + data + '" readonly />'
                },
                {
                    targets: [9],
                    // className: "d-none",
                    render: (data, type, row, meta) => '<input style="width:80px;text-align:center;" type="text" id="input_qty_reject' + meta.row + '" name="input_qty_reject['+meta.row+']" value="' + data + '" readonly />'
                },
                {
                    targets: [11],
                    render: (data, type, row, meta) => {
                    return `<div class='d-flex gap-1 justify-content-center'>
                    <button type='button' class='btn btn-sm btn-info' href='javascript:void(0)' onclick='get_data_detail("` + row.id_po + `","` + row.id_jo + `","` + row.id_item + `","` + row.buyer + `","` + row.kpno + `")'><i class="fas fa-plus-square"></i> Add</button>
                    <button type='button' class='btn btn-sm btn-danger' href='javascript:void(0)' onclick='delete_temp("` + row.id_po + `","` + row.id_jo + `","` + row.id_item + `")'><i class="fa-solid fa-undo"></i> Undo</button>
                    </div>`;
                }
                },
                {
                    targets: [12],
                    className: "d-none",
                    render: (data, type, row, meta) => '<input style="width:80px;text-align:center;" type="text" id="id_jo' + meta.row + '" name="id_jo['+meta.row+']" value="' + data + '" readonly />'
                },
                {
                    targets: [13],
                    className: "d-none",
                    render: (data, type, row, meta) => '<input style="width:80px;text-align:center;" type="text" id="id_item' + meta.row + '" name="id_item['+meta.row+']" value="' + data + '" readonly />'
                },
                {
                    targets: [14],
                    className: "d-none",
                    render: (data, type, row, meta) => '<input style="width:80px;text-align:center;" type="text" id="unit' + meta.row + '" name="unit['+meta.row+']" value="' + data + '" readonly />'
                },
                {
                    targets: [15],
                    className: "d-none",
                    render: (data, type, row, meta) => '<input style="width:80px;text-align:center;" type="text" id="id_po' + meta.row + '" name="id_po['+meta.row+']" value="' + data + '" readonly />'
                },
                {
                    targets: [16],
                    className: "d-none",
                    render: (data, type, row, meta) => '<input style="width:80px;text-align:center;" type="text" id="det_kpno' + meta.row + '" name="det_kpno['+meta.row+']" value="' + data + '" readonly />'
                }
                ]
        });

        function get_data_detail($id_po,$id_jo,$id_item,$buyer,$ws){
        let id_po = $id_po;
        let id_jo = $id_jo;
        let id_item = $id_item;
        let buyer = $buyer;
        let ws = $ws;

        getlist_showitem($id_po, id_item, id_jo);

        $('#mdl_buyer').val(buyer);
        $('#mdl_ws').val(ws);
        $('#mdl_qty').val('');
        $('#mdl_qty_h').val('');
        $('#modal_add_detail').modal('show');
    }

    function getlist_showitem($id_po,$id_item,$id_jo){
        let id_po = $id_po;
        let id_item = $id_item;
        let id_jo = $id_jo;
        return $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route("show-detail-po-in-subcont") }}',
                type: 'get',
                data: {
                    id_po: id_po,
                    id_item: id_item,
                    id_jo: id_jo,
                },
                success: function (res) {
                    if (res) {
                        document.getElementById('detail_showitem').innerHTML = res;
                        $('#tableshow').DataTable({
    paging: false,          // matikan paging
    searching: true,        // tetap bisa filter
    scrollY: '300px',       // tinggi area scroll vertikal
    scrollX: true,          // scroll horizontal
    scrollCollapse: true    // collapse tinggi scroll jika datanya sedikit
});

                    }
                }
            });
    }


    function sum_qty_item(val){
    var table = document.getElementById("tableshow");
    var sum_in = 0;
    var sum_in_reject = 0;

    for (let i = 1; i < table.rows.length; i++) {

        let qty_balance = parseFloat($("#det_qty_balance"+i).val()) || 0;

        let qty_in_txt = $("#det_qty"+i).val();
        let qty_rej_txt = $("#det_qty_reject"+i).val();

        let qty_in = parseFloat(qty_in_txt) || 0;
        let qty_reject = parseFloat(qty_rej_txt) || 0;

        let qty_all = qty_in + qty_reject;

        if (qty_all > qty_balance) {

            $("#det_qty"+i).val(qty_balance);
            $("#det_qty_reject"+i).val('');

            qty_in = qty_balance;
            qty_reject = 0;
        }

        sum_in += qty_in;
        sum_in_reject += qty_reject;
    }

    let rounded = Math.round(sum_in);
    let formatted = new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(rounded);

    let rounded_reject = Math.round(sum_in_reject);
    let formatted_reject = new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(rounded_reject);

    $('#mdl_qty').val(formatted);
    $('#mdl_qty_h').val(rounded);

    $('#mdl_qty_reject').val(formatted_reject);
    $('#mdl_qty_reject_h').val(rounded_reject);
}



function sum_qty_item_reject(val){
    var table = document.getElementById("tableshow");
    var sum_in = 0;
    var sum_in_reject = 0;

    for (let i = 1; i < table.rows.length; i++) {

        let qty_balance = parseFloat($("#det_qty_balance"+i).val()) || 0;

        let qty_in_txt = $("#det_qty"+i).val();
        let qty_rej_txt = $("#det_qty_reject"+i).val();

        let qty_in = parseFloat(qty_in_txt) || 0;
        let qty_reject = parseFloat(qty_rej_txt) || 0;

        let qty_all = qty_in + qty_reject;

        if (qty_all > qty_balance) {

            $("#det_qty"+i).val(qty_balance);
            $("#det_qty_reject"+i).val('');

            qty_in = qty_balance;
            qty_reject = 0;
        }

        sum_in += qty_in;
        sum_in_reject += qty_reject;
    }

    let rounded = Math.round(sum_in);
    let formatted = new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(rounded);

    let rounded_reject = Math.round(sum_in_reject);
    let formatted_reject = new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(rounded_reject);

    $('#mdl_qty').val(formatted);
    $('#mdl_qty_h').val(rounded);

    $('#mdl_qty_reject').val(formatted_reject);
    $('#mdl_qty_reject_h').val(rounded_reject);
}




function delete_temp($id_po,$id_jo,$id_item){
        let id_po = $id_po;
        let id_jo = $id_jo;
        let id_item = $id_item;
        return $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route("delete-out-detail-temp") }}',
                type: 'get',
                data: {
                    id_po: id_po,
                    id_jo: id_jo,
                    id_item: id_item,
                },
                success: function (res) {
                    detail_po();
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
