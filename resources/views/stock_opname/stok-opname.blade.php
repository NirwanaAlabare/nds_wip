@extends('layouts.index')

@section('custom-link')
<!-- DataTables -->
<link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
<!-- <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script> -->

    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
    @endsection

    @section('content')
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0">Data Stock Opname</h5>
        </div>
        <div class="card-body">
            <div class="d-flex align-items-end gap-3 mb-3">
                <div class="col-md-12">
                    <div class="form-group row">
            <!-- <div class="col-md-2">
            <div class="mb-1">
                <div class="form-group">
                <label class="form-label">From</label>
                    <input type="date" class="form-control form-control" id="tgl_awal" name="tgl_awal"
                        value="{{ date('Y-m-d') }}">
                </div>
            </div>
        </div> -->

            <!-- <div class="col-md-2">
            <div class="mb-1">
                <div class="form-group">
                <label class="form-label">To</label>
                    <input type="date" class="form-control form-control" id="tgl_akhir" name="tgl_akhir"
                        value="{{ date('Y-m-d') }}">
                </div>
            </div>
        </div> -->

        <div class="col-md-3">
            <div class="mb-1">
                <div class="form-group">
                    <label>No Transaksi</label>
                    <select class="form-control select2supp" id="no_transaksi" name="no_transaksi" style="width: 100%;">
                        <!-- <option selected="selected" value="">Select no transaksi</option> -->
                        @foreach ($no_transaksi as $notrans)
                        <option value="{{ $notrans->no_transaksi }}">
                            {{ $notrans->no_transaksi }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- <div class="col-md-2">
            <div class="mb-1">
                <div class="form-group">
                    <label>Status</label>
                    <select class="form-control select2supp" id="status" name="status" style="width: 100%;">
                        <option selected="selected" value="ALL">ALL</option>
                        @foreach ($status_so as $sts)
                        <option value="{{ $sts->nama_pilihan }}">
                            {{ $sts->nama_pilihan }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div> -->

        <div class="col-md-6" style="padding-top: 0.5rem;">
            <div class="mt-4 ">
                <button class="btn btn-primary " onclick="dataTableReload()"> <i class="fas fa-search"></i> Search</button>
                <!-- <button class="btn btn-info" onclick="tambahdata()"> <i class="fas fa-plus"></i> Add Data</button> -->
                <!-- <a onclick="export_excel()" class="btn btn-success position-relative">
                    <i class="fas fa-file-excel"></i>
                    Export
                </a> -->
                <!-- <button class="btn btn-success" onclick="printbarcode('SA')"> <i class="fa-solid fa-barcode"></i> Saldo Awal</button> -->
            <!-- <a href="http://10.10.5.62:8080/erp/pages/forms/pdfBarcode_whsSA.php?id='SA'&mode='barcode'" class="btn btn-success">
                <i class="fa-solid fa-barcode"></i>
                Saldo Awal
            </a> -->
        </div>

    </div>
</div>
</div>
</div>
<!-- <div class="d-flex justify-content-between">
    <div class="ml-auto">
        <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
    </div>
    <input type="text"  id="cari_grdok" name="cari_grdok" autocomplete="off" placeholder="Search GR Document..." onkeyup="carigrdok()">
</div> -->
<div class="table-responsive">
    <table id="datatable" class="table table-bordered table-striped table-head-fixed 100 text-nowrap">
        <thead>
            <tr>
                <th class="text-center">No Transaksi</th>
                <th class="text-center">Item Type</th>
                <th class="text-center">Kode Rak</th>
                <th class="text-center">Item Desc</th>
                <th class="text-center">Qty Rak</th>
                <th class="text-center">Qty Roll Rak</th>
                <th class="text-center">Qty Scan</th>
                <th class="text-center">Qty Roll Scan</th>
                <th class="text-center">Status</th>
                <th class="text-center">Aksi</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>
</div>
</div>

<div class="modal fade" id="modal-appv-material">
    <form action="{{ route('approve-material') }}" method="post" onsubmit="submitForm(this, event)">
       @method('GET')
       <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-sb text-light">
                <h4 class="modal-title">Confirm Dialog</h4>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!--  -->
                <div class="form-group row">
                    <label for="id_inv" class="col-sm-12 col-form-label" >Sure Approve Receive material Number :</label>
                    <br>
                    <div class="col-sm-3">
                    </div>
                    <div class="col-sm-6">
                        <input type="text" class="form-control" id="txt_nodok" name="txt_nodok" style="border:none;text-align: center;" readonly>
                    </div>
                </div>
                <!-- Hidden Text -->
                <!--  -->
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="fa fa-window-close" aria-hidden="true"></i> Close</button>
                <button type="submit" class="btn btn-primary toastsDefaultDanger"><i class="fa fa-thumbs-up" aria-hidden="true"></i> Approve</button>
            </div>
        </div>
    </div>
</form>
</div>


<div class="modal fade" id="modal-edit-lokasi">
    <form action="{{ route('simpan-edit') }}" method="post" onsubmit="submitForm(this, event)">
       @method('GET')
       <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-sb text-light">
                <h4 class="modal-title">Add Location</h4>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!--  -->
                <div class="form-group row">
                    <div class="col-md-6">
                        <div class="form-group row">
                            <div class="col-md-12">
                                <div class="mb-1">
                                    <div class="form-group">
                                        <label>ID</label>
                                        <input type="text" class="form-control " id="txt_id" name="txt_id" value="" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-1">

                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-1">
                                    <div class="form-group">
                                        <label>Storage Initial</label>
                                        <input type="text" class="form-control " id="txt_inisial" name="txt_inisial" value="">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-1">
                                    <div class="form-group">
                                        <label>Row</label>
                                        <input type="number" class="form-control " id="txt_baris" name="txt_baris" value="" min="0">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-1">
                                    <div class="form-group">
                                        <label>Level</label>
                                        <input type="number" class="form-control " id="txt_level" name="txt_level" value="" min="0">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group row">
                            <div class="col-md-12">
                                <div class="mb-1">
                                    <div class="form-group">
                                        <label>Number</label>
                                        <input type="text" class="form-control " id="txt_num" name="txt_num" value="" maxlength="2">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-1">

                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-1">
                                    <div class="form-group">
                                        <label>Capacity</label>
                                        <input type="number" class="form-control " id="txt_capacity" name="txt_capacity" value="" min="0">
                                    </div>
                                </div>
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


<div class="modal fade" id="modal-tambah-lokasi">
    <form action="{{ route('store-lokasi') }}" method="post" onsubmit="submitForm(this, event)">
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
                <!--  -->
                <div class="form-group row">
                    <div class="col-md-6">
                        <div class="form-group row">
                            <div class="col-md-12">
                                <div class="mb-1">

                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-1">
                                    <div class="form-group">
                                        <label>Storage Initial</label>
                                        <input type="text" class="form-control " id="txt_inisial_new" name="txt_inisial_new" value="">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-1">
                                    <div class="form-group">
                                        <label>Row</label>
                                        <input type="number" class="form-control " id="txt_baris_new" name="txt_baris_new" value="" min="0">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-1">
                                    <div class="form-group">
                                        <label>Level</label>
                                        <input type="number" class="form-control " id="txt_level_new" name="txt_level_new" value="" min="0">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group row">
                            <div class="col-md-12">
                                <div class="mb-1">
                                    <div class="form-group">
                                        <label>Number</label>
                                        <input type="text" class="form-control " id="txt_num_new" name="txt_num_new" value="" maxlength="2">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-1">

                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-1">
                                    <div class="form-group">
                                        <label>Capacity</label>
                                        <input type="number" class="form-control " id="txt_capacity_new" name="txt_capacity_new" value="" min="0">
                                    </div>
                                </div>
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
@endsection

@section('custom-script')
<!-- DataTables & Plugins -->
<script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
<!-- <script src="{{ asset('plugins/ionicons/js/ionicons.esm.js') }}"></script>
    <script src="{{ asset('plugins/ionicons/js/ionicons.js') }}"></script> -->

    <!-- Select2 -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        $('.select2master').select2({
            theme: 'bootstrap4'
        })
        $('.select2bs4').select2({
            theme: 'bootstrap4'
        })

        $('.select2roll').select2({
            theme: 'bootstrap4'
        })
        $('.select2supp').select2({
            theme: 'bootstrap4'
        })
        $('.select2type').select2({
            theme: 'bootstrap4'
        })

    </script>

    <script type="text/javascript">
        $('.select2pchtype').select2({
            theme: 'bootstrap4'
        })
    </script>

    <script>
        let datatable = $("#datatable").DataTable({
            serverSide: true,
         processing: true,
         ordering: false,
         scrollX: true,
         autoWidth: false,
         scrollY: true,
         pageLength: 10,
            ajax: {
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route('list-stok-opname') }}',
                dataType: 'json',
                dataSrc: 'data',
                data: function(d) {
                    d.no_transaksi = $('#no_transaksi').val();
                    // d.status = $('#status').val();
                },
            },
            columns: [{
                data: 'no_transaksi'
            },
            {
                data: 'tipe_item'
            },
            {
                data: 'kode_lok'
            },
            {
                data: 'itemdesc'
            },
            {
                data: 'qty'
            },
            {
                data: 'qty_roll'
            },
            {
                data: 'qty_scan'
            },
            {
                data: 'qty_roll_scan'
            },
            {
                data: 'status'
            },
            {
                data: 'kode_lok'
            }

            ],
            columnDefs: [{
                targets: [3],
                className: "d-none",
                render: (data, type, row, meta) => data ? data.split(",").join("<br/>") : "-"
            },
            // {
            //     targets: [7],
            //     render: (data, type, row, meta) => data ? data.toUpperCase() : "-"
            // },

            {
                targets: [9],
                render: (data, type, row, meta) => {
                   // if (row.qty_balance == 0) {
                    return `<div class='d-flex gap-1 justify-content-center'>
                   <a href="{{ route('proses-scan-so') }}/`+data+`/`+row.id+`"><button type='button' class='btn btn-sm btn-info'><i class="fa-solid fa-pen-to-square"></i></button></a>
                    </div>`;
                // }
        }
    }

            ]
        });

function dataTableReload() {
    datatable.ajax.reload();
}

</script>
<script type="text/javascript">
    function approve_inmaterial($nodok){
        // alert($id);
        let nodok  = $nodok;

        $('#txt_nodok').val(nodok);
        $('#modal-appv-material').modal('show');
    }


    function nonactive_lokasi($id,$status,$kode_lok){
        // alert($id);
        let id  = $id;
        let status  = $status;
        let kode  = $kode_lok;
        let idnya  = $id;

        $('#txt_kode_lok').val(kode);
        $('#id_lok').val(idnya);
        $('#status_lok').val(status);
        $('#modal-active-lokasi').modal('show');
    }


    function editdata($id,$kapasitas,$inisial_lok,$baris,$level,$nomor,$area,$unit,$u_roll,$u_bundle,$u_box,$u_pack){
        // alert($id);
        $("#ROLL_edit").prop("checked", false);
        $("#BUNDLE_edit").prop("checked", false);
        $("#BOX_edit").prop("checked", false);
        $("#PACK_edit").prop("checked", false);
        let kapasitas  = $kapasitas;
        let inisial_lok  = $inisial_lok;
        let idnya  = $id;
        let baris  = $baris;
        let level  = $level;
        let nomor  = $nomor;
        let area  = $area;
        let unit  = $unit;
        let u_roll  = $u_roll;
        let u_bundle  = $u_bundle;
        let u_box  = $u_box;
        let u_pack  = $u_pack;

        console.log(u_roll);

        if (u_roll == 'ROLL') {
            $("#ROLL_edit").prop("checked", true);
        }

        if (u_bundle == 'BUNDLE') {
            $("#BUNDLE_edit").prop("checked", true);
        }

        if (u_box == 'BOX') {
            $("#BOX_edit").prop("checked", true);
        }

        if (u_pack == 'PACK') {
            $("#PACK_edit").prop("checked", true);
        }

        $('#txt_id').val(idnya);
        $('#txt_inisial').val(inisial_lok);
        $('#txt_capacity').val(kapasitas);
        $('#txt_baris').val(baris);
        $('#txt_level').val(level);
        $('#txt_num').val(nomor);
        $('#txt_area').val(area);
    // document.getElementById('txt_area').value=area;
    // document.getElementById('txt_area').selected=true;
    $('#modal-edit-lokasi').modal('show');
}

function tambahdata(){
    $('#modal-tambah-lokasi').modal('show');
}
</script>

<script type="text/javascript">

    function carigrdok() {
        // Declare variables
        var input, filter, table, tr, td, i, txtValue;
        input = document.getElementById("cari_grdok");
        filter = input.value.toUpperCase();
        table = document.getElementById("datatable");
        tr = table.getElementsByTagName("tr");

        // Loop through all table rows, and hide those who don't match the search query
        for (i = 0; i < tr.length; i++) {
            td = tr[i].getElementsByTagName("td")[0]; //kolom ke berapa
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
    function printbarcode(id) {

        $.ajax({
            url: '{{ route('print-barcode-inmaterial') }}/'+id,
            type: 'post',
            processData: false,
            contentType: false,
            xhrFields:
            {
                responseType: 'blob'
            },
            success: function(res) {
                if (res) {
                    console.log(res);

                    var blob = new Blob([res], {type: 'application/pdf'});
                    var link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = id+".pdf";
                    link.click();
                }
            }
        });
    }

    function printpdf(id) {

        $.ajax({
            url: '{{ route('print-pdf-inmaterial') }}/'+id,
            type: 'post',
            processData: false,
            contentType: false,
            xhrFields:
            {
                responseType: 'blob'
            },
            success: function(res) {
                if (res) {
                    console.log(res);

                    var blob = new Blob([res], {type: 'application/pdf'});
                    var link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = id+".pdf";
                    link.click();
                }
            }
        });
    }
</script>
@endsection
