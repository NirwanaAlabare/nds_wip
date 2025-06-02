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
<div class="card card-sb card-outline">
    <div class="card-header">
        <h5 class="card-title fw-bold mb-0">Data Permintaan Bahan Baku</h5>
    </div>
    <div class="card-body">
        <div class="d-flex align-items-end gap-1 mb-1">
                <div class="col-md-12">
            <div class="form-group row">

            <div class="col-12 col-md-3">
            <div class="mb-1">
                <div class="form-group">
                <label>Tipe Data</label>
                <select class="form-control select2supp" id="tipe_data" name="tipe_data" style="width: 100%;">
                    <option value="header">HEADER</option>
                    <option value="list">LIST</option>
                </select>
                </div>
            </div>
            </div>

            <div class="col-6 col-md-2">
            <div class="mb-1">
                <div class="form-group">
                <label class="form-label">From Date</label>
                    <input type="date" class="form-control form-control" id="tgl_awal" name="tgl_awal"
                        value="{{ date('Y-m-d') }}">
                </div>
            </div>
            </div>

            <div class="col-6 col-md-2">
            <div class="mb-1">
                <div class="form-group">
                <label class="form-label">To Date</label>
                    <input type="date" class="form-control form-control" id="tgl_akhir" name="tgl_akhir"
                        value="{{ date('Y-m-d') }}">
                </div>
            </div>
            </div>

            <div class=" col-12 col-md-5" style="padding-top: 0.5rem;">
            <div class="mt-4 ">
                <button class="btn btn-primary " onclick="dataTableReload()"> <i class="fas fa-search"></i> Search</button>
                <!-- <button class="btn btn-info" onclick="tambahdata()"> <i class="fas fa-plus"></i> Add Data</button> -->
                <a href="{{ route('create-reqmaterial') }}" class="btn btn-info">
                <i class="fas fa-plus"></i>
                New Data
            </a>
            </div>
        </div>
        </div>
    </div>
</div>
        <div class="d-flex justify-content-between">
            <div class="ml-auto">
                <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
            </div>
                <input type="text"  id="cari_grdok" name="cari_grdok" autocomplete="off" placeholder="Search Data..." onkeyup="carigrdok()">
        </div>
        <div class="table-responsive" style="max-height: 400px">
            <table id="datatable" class="table table-bordered table-striped w-100 text-nowrap">
                <thead>
                    <tr>
                        <th class="text-center">No Request</th>
                        <th class="text-center">Request Date</th>
                        <th class="text-center">Buyer</th>
                        <th class="text-center">Style #</th>
                        <th class="text-center">WS #</th>
                        <th class="text-center">WS Actual #</th>
                        <th class="text-center">Send To</th>
                        <th class="text-center">User Created</th>
                        <th class="text-center">Unit</th>
                        <th class="text-center">Qty Req</th>
                        <th class="text-center">Qty Out</th>
                        <th class="text-center">No Bppb</th>
                        <th class="text-center">Action</th>
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
        ordering: false,
        processing: true,
        serverSide: true,
        paging: false,
        searching: false,
        ajax: {
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '{{ route('req-material') }}',
            dataType: 'json',
            dataSrc: 'data',
            data: function(d) {
                d.tgl_awal = $('#tgl_awal').val();
                d.tgl_akhir = $('#tgl_akhir').val();
                d.tipe_data = $('#tipe_data').val();
            },
        },
        columns: [
            {
                data: 'bppbno'
            },
            {
                data: 'bppbdate'
            },
            {
                data: 'buyer'
            },
            {
                data: 'styleno'
            },
            {
                data: 'kpno'
            },
            {
                data: 'idws_act'
            },
            {
                data: 'supplier'
            },
            {
                data: 'username'
            },
            {
                data: 'unit'
            },
            {
                data: 'qty_req'
            },
            {
                data: 'qty_out'
            },
            {
                data: 'bppbno_int'
            },
            {
                data: 'bppbno'
            }

        ],
        columnDefs: [{
                targets: [9,10],
                className: 'text-right'
            },{
                targets: [4],
                render: (data, type, row, meta) => data ? data.toUpperCase() : "-"
            },
            {
                targets: [6],
                render: (data, type, row, meta) => data ? data : "-"
            },
            {
                targets: [7],
                render: (data, type, row, meta) => data ? data : "-"
            },
            {
                targets: [9],
                render: (data, type, row, meta) => data ? data : "0"
            },
            {
                targets: [10],
                render: (data, type, row, meta) => data ? data : "0"
            },
            {
                targets: [11],
                render: (data, type, row, meta) => data ? data : "-"
            },
            {
                targets: [12],
                render: (data, type, row, meta) => {
                    console.log(row);
                    if ($('#tipe_data').val() == 'header') {
                      return `<div class='d-flex gap-1 justify-content-center'>
                    <a href="{{ route('edit-reqmaterial') }}/`+data+`"><button type='button' class='btn btn-sm btn-danger'><i class="fa-solid fa-pen-to-square"></i></button></a>
                    <button type='button' class='btn btn-sm btn-warning' onclick='printpdf("` + row.bppbno + `")'><i class="fa-solid fa-print "></i></button>
                    </div>`;
                    }
                    return `<div class='d-flex gap-1 justify-content-center'>
                    -
                    </div>`;
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

    function printpdf(bppbno) {

            $.ajax({
                url: '{{ route('print-pdf-reqmaterial') }}/'+bppbno,
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
                        link.download = bppbno+".pdf";
                        link.click();
                    }
                }
            });
        }
</script>
@endsection
