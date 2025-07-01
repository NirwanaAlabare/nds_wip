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
<form action="{{ route('save-stockopname-fabric') }}" method="post" id="store-opname" onsubmit="submitForm(this, event)">
    @method('POST')
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
            <div class="col-md-12">
                <div class="row">
                    <div class="col-4 col-md-3">
                        <div class="mb-1">
                            <div class="form-group">
                                <label><small>No Dokumen</small></label>
                                <input type="text" class="form-control " id="txt_no_dokumen" name="txt_no_dokumen" value="{{ $no_transaksi }}" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="col-3 col-md-2">
                        <div class="mb-1">
                            <div class="form-group">
                                <label><small>Tgl Saldo</small></label>
                                <input type="date" class="form-control form-control" id="txt_tgl_so" name="txt_tgl_so"
                                value="{{ $tgl_filter }}" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="col-14 col-md-3">
                        <div class="mb-1">
                            <div class="form-group">
                                <label><small>Tipe Item</small></label>
                                <input type="text" class="form-control " id="txt_itemtipe" name="txt_itemtipe" value="{{ $tipe_item }}" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                    <div style="padding-top:30px;">
                        <a onclick="export_excel()" class="btn btn-success position-relative btn">
                            <i class="fas fa-file-excel"></i>
                            Export
                        </a>

                        <a onclick="export_excel_barcode()" class="btn btn-success position-relative btn">
                            <i class="fas fa-file-excel"></i>
                            Export Barcode
                        </a>
                        </div>
                    </div>

                </div>
            </div>

            <!-- <div class="col-md-12">
                <div class="row">
                    <div class="col-6 col-md-2">
                        <div class="mb-1">
                            <div class="form-group">
                                <label><small>Scan</small></label>
                                <input type="text" class="form-control " id="txt_barcode" name="txt_barcode" value="" autofocus onkeyup="getdatabarcode()">
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="mb-1">
                            <div class="form-group">
                                <label><small>Qty</small></label>
                                <input type="text" class="form-control " id="txt_qty_barcode" name="txt_qty_barcode" value="">
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="mb-1">
                            <div class="form-group">
                                <label><small>Item Description</small></label>
                                <input type="text" class="form-control " id="txt_item_barcode" name="txt_item_barcode" value="" readonly>
                                <input type="hidden" class="form-control " id="txt_iditem_barcode" name="txt_iditem_barcode" value="" readonly>
                                <input type="hidden" class="form-control " id="txt_jo_barcode" name="txt_jo_barcode" value="" readonly>
                                <input type="hidden" class="form-control " id="txt_lok_barcode" name="txt_lok_barcode" value="" readonly>
                                <input type="hidden" class="form-control " id="txt_lot_barcode" name="txt_lot_barcode" value="" readonly>
                                <input type="hidden" class="form-control " id="txt_roll_barcode" name="txt_roll_barcode" value="" readonly>
                                <input type="hidden" class="form-control " id="txt_unit_barcode" name="txt_unit_barcode" value="" readonly>
                            </div>
                        </div>
                    </div>

                   <div class="col-2 col-md-1">
                        <div class="mb-1">
                            <div class="form-group">
                                <label><small>Simpan</small></label>
                                <button type="button" class="btn btn-success btn-sm toastsDefaultDanger" onclick="savedatabarcode()"><i class="fa-solid fa-floppy-disk" aria-hidden="true"></i> Add</button>
                            </div>
                        </div>
                    </div> -->

              <!--   </div>
            </div>
 -->
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

            <div class="table-responsive" >
                <table id="datatable" class="table table-bordered table-striped w-100 text-nowrap">
                    <thead>
                        <tr>
                            <th class="text-center" style="font-size: 0.6rem;width: 300px;">Lokasi</th>
                            <th class="text-center" style="font-size: 0.6rem;width: 300px;">ID JO</th>
                            <th class="text-center" style="font-size: 0.6rem;width: 300px;">ID Item</th>
                            <th class="text-center" style="font-size: 0.6rem;width: 300px;">Kode Item</th>
                            <th class="text-center" style="font-size: 0.6rem;width: 300px;">Deskripsi Item</th>
                            <th class="text-center" style="font-size: 0.6rem;width: 300px;">Qty Item</th>
                            <th class="text-center" style="font-size: 0.6rem;width: 300px;">Qty SO</th>
                            <th class="text-center" style="font-size: 0.6rem;width: 300px;">Sisa</th>
                        </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
        </div>
        <div class="mb-1">
            <div class="form-group">
                <button class="btn btn-sb float-end mt-2 ml-2"><i class="fa-solid fa-floppy-disk"></i> Save</button>
                <a href="{{ route('laporan-stok-opname') }}" class="btn btn-danger float-end mt-2">
                    <i class="fas fa-arrow-circle-left"></i> Back</a>
                </div>
            </div>
        </div>
    </div>
</div>
</form>


<div class="modal fade" id="modal-edit-so">
    <form method="post">
     @method('GET')
     <div class="modal-dialog modal modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-sb text-light">
                <h4 class="modal-title">Edit Data Barcode</h4>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!--  -->
                <div class="form-group row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>No Barcode</label>
                            <input type="text" class="form-control " id="mdl_barcode" name="mdl_barcode" value="" readonly>
                        </div>
                    </div>
                    <div class="col-md-6">

                        <div class="form-group">
                            <label>Qty</label>
                            <input type="text" class="form-control " id="mdl_qty" name="mdl_qty" value="">
                            <input type="hidden" class="form-control " id="mdl_qty_old" name="mdl_qty_old" value="">
                        </div>

                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="fa fa-window-close" aria-hidden="true"></i> Tutup</button>
                <button type="button" class="btn btn-success btn-sm toastsDefaultDanger" onclick="saveeditso()"><i class="fa-solid fa-floppy-disk"></i> Simpan</button>
            </div>
        </div>
    </div>
</form>
</div>

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
<script type="text/javascript">
    function export_excel() {
            let no_transaksi = document.getElementById("txt_no_dokumen").value;
            let itemso = document.getElementById("txt_itemtipe").value;
            Swal.fire({
                title: 'Please Wait,',
                html: 'Exporting Data...',
                didOpen: () => {
                    Swal.showLoading()
                },
                allowOutsideClick: false,
            });

            $.ajax({
                type: "get",
                url: '{{ route('export_excel_laporan_so_detail') }}',
                data: {
                    no_transaksi: no_transaksi,
                    itemso: itemso
                },
                xhrFields: {
                    responseType: 'blob'
                },
                success: function(response) {
                    {
                        swal.close();
                        Swal.fire({
                            title: 'Data Berhasil Di Export!',
                            icon: "success",
                            showConfirmButton: true,
                            allowOutsideClick: false
                        });
                        var blob = new Blob([response]);
                        var link = document.createElement('a');
                        link.href = window.URL.createObjectURL(blob);
                        link.download = "Laporan Stock Opname Detail  " + itemso + ".xlsx";
                        link.click();

                    }
                },
            });
        }

        function export_excel_barcode() {
            let no_transaksi = document.getElementById("txt_no_dokumen").value;
            let itemso = document.getElementById("txt_itemtipe").value;
            Swal.fire({
                title: 'Please Wait,',
                html: 'Exporting Data...',
                didOpen: () => {
                    Swal.showLoading()
                },
                allowOutsideClick: false,
            });

            $.ajax({
                type: "get",
                url: '{{ route('export_excel_laporan_so_detail_barcode') }}',
                data: {
                    no_transaksi: no_transaksi,
                    itemso: itemso
                },
                xhrFields: {
                    responseType: 'blob'
                },
                success: function(response) {
                    {
                        swal.close();
                        Swal.fire({
                            title: 'Data Berhasil Di Export!',
                            icon: "success",
                            showConfirmButton: true,
                            allowOutsideClick: false
                        });
                        var blob = new Blob([response]);
                        var link = document.createElement('a');
                        link.href = window.URL.createObjectURL(blob);
                        link.download = "Laporan Stock Opname Detail Barcode " + itemso + ".xlsx";
                        link.click();

                    }
                },
            });
        }
</script>
<script type="text/javascript">
    function savedatabarcode() {
        var lokasi_scan = document.getElementById('txt_lokasi_h').value;
        var no_barcode = document.getElementById('txt_barcode').value;
        var qty = document.getElementById('txt_qty_barcode').value;
        var id_item = document.getElementById('txt_iditem_barcode').value;
        var id_jo = document.getElementById('txt_jo_barcode').value;
        var lokasi_so = document.getElementById('txt_lok_barcode').value;
        var no_lot = document.getElementById('txt_lot_barcode').value;
        var no_roll = document.getElementById('txt_roll_barcode').value;
        var unit = document.getElementById('txt_unit_barcode').value;

                // clearModified();

                return $.ajax({
                    url: '{{ route('simpan-scan-barcode-so') }}',
                    type: 'GET',
                    dataType: 'json',
                    data: {
                        lokasi_scan: lokasi_scan,
                        no_barcode: no_barcode,
                        qty: qty,
                        id_item: id_item,
                        id_jo: id_jo,
                        lokasi_so: lokasi_so,
                        no_lot: no_lot,
                        no_roll: no_roll,
                        unit: unit,
                    },
                    success: function(res) {
                        if (res) {
                            if (res.status == 200) {
                                // Swal.fire({
                                //     icon: 'success',
                                //     title: 'Berhasil',
                                //     text: 'Data Berhasil Disimpan',
                                //     showCancelButton: false,
                                //     showConfirmButton: true,
                                //     confirmButtonText: 'Oke',
                                //     timer: 1500,
                                //     timerProgressBar: true
                                // }).then(async (result) => {
                                    dataTableReload();
                                    document.getElementById('txt_qty_barcode').value = '';
                                    document.getElementById('txt_barcode').value = '';
                                    document.getElementById('txt_item_barcode').value = '';
                                    document.getElementById('txt_iditem_barcode').value = '';
                                    document.getElementById('txt_jo_barcode').value = '';
                                    document.getElementById('txt_lok_barcode').value = '';
                                    document.getElementById('txt_lot_barcode').value = '';
                                    document.getElementById('txt_roll_barcode').value = '';
                                    document.getElementById('txt_unit_barcode').value = '';
                                // });

                            }
                        }
                    }
                });
            }
        </script>
        <script>
            let datatable = $("#datatable").DataTable({
                serverSide: true,
                processing: true,
                ordering: false,
                scrollX: '400px',
                scrollY: true,
                pageLength: 10,
                ajax: {
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: '{{ route('list-so-detail-show') }}',
                    dataType: 'json',
                    dataSrc: 'data',
                    data: function(d) {
                        d.no_dokumen = $('#txt_no_dokumen').val();

                    },
                },
                columns: [{
                    data: 'kode_lok'
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
                    data: 'itemdesc'
                },
                {
                    data: 'qty_show'
                },

                {
                    data: 'qty_so_show'
                },
                {
                    data: 'qty_sisa_show'
                }

                ],
                columnDefs: [

        ]
    });

            function dataTableReload() {
                datatable.ajax.reload();
                return $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: '{{ route('get-sum-barcodeso') }}',
                    type: 'get',
                    data: {
                        kode_lok: $('#txt_lokasi_h').val(),
                    },
                    dataType: 'json',
                    success: function(res) {
                        if (res) {

                            document.getElementById('txt_qty_scan').value = res.qty;
                        }
                    },
                });
            }

            function delete_scan($no_barcode){
                let no_barcode = $no_barcode;
                return $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: '{{ route("delete-so-temp") }}',
                    type: 'get',
                    data: {
                        no_barcode: no_barcode,
                    },
                    success: function (res) {
                        dataTableReload();
                    }
                });

            }

            function editdata($no_barcode,$qty_scan,$qty_old){

                let no_barcode  = $no_barcode;
                let qty_scan  = $qty_scan;
                let qty_old  = $qty_old;
                if (qty_old > 0) {

                    Swal.fire({
                        icon: 'info',
                        title: 'Peringatan!!',
                        text: 'Data sudah pernah diedit',
                        showCancelButton: false,
                        showConfirmButton: true,
                        confirmButtonText: 'Oke',
                    });

                }else{

                    $('#mdl_barcode').val(no_barcode);
                    $('#mdl_qty').val(qty_scan);
                    $('#mdl_qty_old').val(qty_scan);
                // document.getElementById('txt_area').value=area;
                // document.getElementById('txt_area').selected=true;
                $('#modal-edit-so').modal('show');
            }
        }

        $( document ).ready(function() {
            dataTableReload();
        });
    </script>
    <script type="text/javascript">
        function saveeditso() {
            var barcode = document.getElementById('mdl_barcode').value;
            var qty = document.getElementById('mdl_qty').value;
            var qty_old = document.getElementById('mdl_qty_old').value;

            return $.ajax({
                url: '{{ route('simpan-edit-barcode-so') }}',
                type: 'GET',
                dataType: 'json',
                data: {
                    barcode: barcode,
                    qty: qty,
                    qty_old: qty_old,
                },
                success: function(res) {
                    if (res) {
                        $('#modal-edit-so').modal('hide');
                        dataTableReload();
                    }
                }
            });
        }
    </script>
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

        // async function getlistdata() {
        //     return datatable.ajax.reload(() => {
        //         document.getElementById('jumlah_data').value = datatable.data().count();
        //     });
        // }

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
