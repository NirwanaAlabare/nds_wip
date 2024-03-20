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
        <h5 class="card-title fw-bold mb-0">Data Pengeluaran Bahan Baku</h5>
    </div>
    <div class="card-body">
        <div class="d-flex align-items-end gap-3 mb-3">
                <div class="col-md-12">
            <div class="form-group row">
            <div class="col-md-2">
            <div class="mb-1">
                <div class="form-group">
                <label class="form-label">From</label>
                    <input type="date" class="form-control form-control" id="tgl_awal" name="tgl_awal"
                        value="{{ date('Y-m-d') }}">
                </div>
            </div>
            </div>

            <div class="col-md-2">
            <div class="mb-1">
                <div class="form-group">
                <label class="form-label">To</label>
                    <input type="date" class="form-control form-control" id="tgl_akhir" name="tgl_akhir"
                        value="{{ date('Y-m-d') }}">
                </div>
            </div>
            </div>

            <div class="col-md-3">
            <div class="mb-1">
                <div class="form-group">
                <label>jenis Pengeluaran</label>
                <select class="form-control select2supp" id="jns_pengeluaran" name="jns_pengeluaran" style="width: 100%;">
                    <option selected="selected" value="ALL">ALL</option>
                        @foreach ($jns_klr as $jklr)
                    <option value="{{ $jklr->isi }}">
                                {{ $jklr->tampil }}
                    </option>
                        @endforeach
                </select>
                </div>
            </div>
            </div>

            <div class="col-md-4">
            <div class="mb-1">
                <div class="form-group">
                <label>Buyer</label>
                <select class="form-control select2pchtype" id="buyer" name="buyer" style="width: 100%;">
                    <option selected="selected" value="ALL">ALL</option>
                        @foreach ($msupplier as $msupp)
                    <option value="{{ $msupp->Supplier }}">
                                {{ $msupp->Supplier }}
                    </option>
                        @endforeach
                </select>
                </div>
            </div>
            </div>

            <div class="col-md-2">
            <div class="mb-1">
                <div class="form-group">
                <label>Jenis BC</label>
                <select class="form-control select2type" id="jenis_bc" name="jenis_bc" style="width: 100%;">
                    <option selected="selected" value="ALL">ALL</option>
                        @foreach ($mtypebc as $type)
                    <option value="{{ $type->nama_pilihan }}">
                                {{ $type->nama_pilihan }}
                    </option>
                        @endforeach
                </select>
                </div>
            </div>
            </div>

            <div class="col-md-2">
            <div class="mb-1">
                <div class="form-group">
                <label>Status</label>
                <select class="form-control select2supp" id="status" name="status" style="width: 100%;">
                    <option selected="selected" value="ALL">ALL</option>
                        @foreach ($status as $sts)
                    <option value="{{ $sts->nama_pilihan }}">
                                {{ $sts->nama_pilihan }}
                    </option>
                        @endforeach
                </select>
                </div>
            </div>
            </div>
            
            <div class="col-md-6" style="padding-top: 0.5rem;">
            <div class="mt-4 ">
                <button class="btn btn-primary " onclick="dataTableReload()"> <i class="fas fa-search"></i> Search</button>
                <!-- <button class="btn btn-info" onclick="tambahdata()"> <i class="fas fa-plus"></i> Add Data</button> -->
                <a href="{{ route('create-outmaterial') }}" class="btn btn-info">
                <i class="fas fa-plus"></i>
                Add Data
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
                <input type="text"  id="cari_grdok" name="cari_grdok" autocomplete="off" placeholder="Cari No BPPB..." onkeyup="carigrdok()">
        </div>
        <div class="table-responsive" style="max-height: 400px">
            <table id="datatable" class="table table-bordered table-striped table-head-fixed table-sm w-100 text-nowrap">
                <thead>
                    <tr>
                        <th class="text-center">No BPPB</th>
                        <th class="text-center">Tgl BPPB</th>
                        <th class="text-center">No Request</th>
                        <th class="text-center">No JO</th>
                        <th class="text-center">Buyer</th>
                        <th class="text-center">Penerima</th>
                        <th class="text-center">Jenis BC</th>
                        <th class="text-center">Jenis Trans</th>
                        <th class="text-center">No Invoice</th>
                        <th class="text-center">Tanggal Daftar</th>
                        <th class="text-center">No Daftar</th>
                        <th class="text-center">User</th>
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
    <form action="{{ route('approve-outmaterial') }}" method="post" onsubmit="submitForm(this, event)">
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
                        <label for="id_inv" class="col-sm-12 col-form-label" >Sure Approve Out Material Number :</label>
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
                <div class="form-group">
                <label>Location Area</label>
                <input class="form-control" list="txtarea" id="txt_area" name="txt_area" value="">
                <datalist id="txtarea">
                    @foreach ($arealok as $alok)
                    <option value="{{ $alok->area }}">
                                {{ $alok->area }}
                    </option>
                        @endforeach
                </datalist>
                </div>
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
                <div class="form-group">
                <label>Unit</label>
                        @foreach ($unit as $un)
                        <br>
                <input type="checkbox" class="ml-2" id="{{ $un->nama_unit }}_edit" name="{{ $un->nama_unit }}_edit" /><label style="font-size: 0.9rem" for="{{ $un->nama_unit }}_edit">  <i>{{ $un->nama_unit }}</i></label>
                        @endforeach
                </div>
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
                <div class="form-group">
                <label>Location Area</label>
                <select class="form-control select2bs4" id="txt_area_new" name="txt_area_new" style="width: 100%;">
                    <option selected="selected" value="">Select Area</option>
                        @foreach ($arealok as $alok)
                    <option value="{{ $alok->area }}">
                                {{ $alok->area }}
                    </option>
                        @endforeach
                </select>
                </div>
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
                <div class="form-group">
                <label>Unit</label>
                        @foreach ($unit as $un)
                        <br>
                <input type="checkbox" class="ml-2" id="{{ $un->nama_unit }}" name="{{ $un->nama_unit }}" /><label style="font-size: 0.9rem" for="{{ $un->nama_unit }}">  <i>{{ $un->nama_unit }}</i></label>
                        @endforeach
                </div>
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
        ordering: false,
        processing: true,
        serverSide: true,
        paging: false,
        searching: false,
        ajax: {
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '{{ route('out-material') }}',
            dataType: 'json',
            dataSrc: 'data',
            data: function(d) {
                d.tgl_awal = $('#tgl_awal').val();
                d.tgl_akhir = $('#tgl_akhir').val();
                d.jns_pengeluaran = $('#jns_pengeluaran').val();
                d.bc_type = $('#jenis_bc').val();
                d.buyer = $('#buyer').val();
                d.status = $('#status').val();
            },
        },
        columns: [{
                data: 'no_bppb'
            },
            {
                data: 'tgl_bppb'
            },
            {
                data: 'no_req'
            },
            {
                data: 'no_jo'
            },
            {
                data: 'buyer'
            },
            {
                data: 'tujuan'
            },
            {
                data: 'dok_bc'
            },
            {
                data: 'jenis_pengeluaran'
            },
            {
                data: 'no_invoice'
            },
            {
                data: 'no_daftar'
            },
            {
                data: 'tgl_daftar'
            },
            {
                data: 'user_create'
            },
            {
                data: 'status'
            },
            {
                data: 'id'
            }

        ],
        columnDefs: [{
                targets: [13],
                render: (data, type, row, meta) => {
                    console.log(row);
                    if (row.status == 'Pending') {
                        return `<div class='d-flex gap-1 justify-content-center'> 
                   <button type='button' class='btn btn-sm btn-warning' onclick='printpdf("` + row.id + `")'><i class="fa-solid fa-print "></i></button>
                    <button type='button' class='btn btn-sm btn-info' href='javascript:void(0)' onclick='approve_outmaterial("` + row.no_bppb + `")'><i class="fa-solid fa-person-circle-check"></i></button> 
                    </div>`;
                    }else{
                        return `<div class='d-flex gap-1 justify-content-center'> 
                        <button type='button' class='btn btn-sm btn-warning' onclick='printpdf("` + row.id + `")'><i class="fa-solid fa-print "></i></button>
                    </div>`;
                    }
                }
            }

        ]
    });

    function dataTableReload() {
        datatable.ajax.reload();
    }
</script>
<script type="text/javascript">
    function approve_outmaterial($no_bppb){
        // alert($id);
        let no_bppb  = $no_bppb;
    
    $('#txt_nodok').val(no_bppb);
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
                url: '{{ route('print-pdf-outmaterial') }}/'+id,
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
