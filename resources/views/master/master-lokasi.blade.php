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
<div class="card card-sb card-outline">
    <div class="card-header">
        <h5 class="card-title fw-bold mb-0">Data Master Lokasi</h5>
    </div>
    <div class="card-body">
        <div class="d-flex align-items-end gap-3 mb-3">
            <div class="col-md-12">
            <div class="form-group row">
            <div class="col-md-5">
            <div class="mb-1">
                <div class="form-group">
                <label>Area Lokasi</label>
                <select class="form-control select2master" id="area" name="area" style="width: 100%;">
                    <option selected="selected" value="ALL">ALL</option>
                        @foreach ($arealok as $alok)
                    <option value="{{ $alok->area }}">
                                {{ $alok->area }}
                    </option>
                        @endforeach
                </select>
                </div>
            </div>
            </div>

            <div class="col-md-7" style="padding-top: 0.5rem;">
            <div class="mt-4">
                <button class="btn btn-primary " onclick="dataTableReload()"> <i class="fas fa-search"></i> Tampilkan</button>
                <button class="btn btn-info" onclick="tambahdata()"> <i class="fas fa-plus"></i> Tambah Data</button>
            </div>
            </div>
        </div>
    </div>

        </div>
        
        <div class="table-responsive">
            <table id="datatable" class="table table-bordered table-striped table-sm w-100">
                <thead>
                    <tr>
                        <th class="text-center">Area Lokasi</th>
                        <th class="text-center">Kode Lokasi</th>
                        <th class="text-center">Satuan</th>
                        <th class="text-center">Kapasitas</th>
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

<div class="modal fade" id="modal-active-lokasi">
    <form action="{{ route('updatestatus') }}" method="post" onsubmit="submitForm(this, event)">
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
                        <label for="id_inv" class="col-sm-12 col-form-label" >Sure Change Status Master Location :</label>
                        <br>
                        <div class="col-sm-4">
                        </div>
                        <div class="col-sm-4">
                            <input type="text" class="form-control" id="txt_kode_lok" name="txt_kode_lok" style="border:none;text-align: center;" readonly>
                        </div>
                    </div>
                    <!-- Hidden Text -->
                    <input type="hidden" id="id_lok" name="id_lok" readonly>
                    <input type="hidden" id="status_lok" name="status_lok" readonly>
                    <!--  -->
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="fa fa-window-close" aria-hidden="true"></i> Close</button>
                    <button type="submit" class="btn btn-primary toastsDefaultDanger"><i class="fa fa-thumbs-up" aria-hidden="true"></i> Change Status</button>
                </div>
            </div>
        </div>
    </form>
</div>



<!-- <div class="modal fade" id="modal-edit-lokasi">
    <form action="{{ route('simpan-edit') }}" method="post" onsubmit="submitForm(this, event)">
         @method('GET')
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-sb text-light">
                    <h4 class="modal-title">Edit Location</h4>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group row">

            <div class="col-md-2">
            <div class="mb-1">
                <div class="form-group">
                <label>ID</label>
                <input type="text" class="form-control " id="txt_id" name="txt_id" value="" readonly>
                </div>
            </div>
            </div>

            <div class="col-md-3">
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
            <div class="col-md-2">
            <div class="mb-1">
                <div class="form-group">
                <label>Storage Initial</label>
                <input type="text" class="form-control " id="txt_inisial" name="txt_inisial" value="">
                </div>
            </div>
            </div>
            <div class="col-md-2">
            <div class="mb-1">
                <div class="form-group">
                <label>Row</label>
                <input type="number" class="form-control " id="txt_baris" name="txt_baris" value="" min="0">
                </div>
            </div>
            </div>
            <div class="col-md-2">
            <div class="mb-1">
                <div class="form-group">
                <label>Level</label>
                <input type="number" class="form-control " id="txt_level" name="txt_level" value="" min="0">
                </div>
            </div>
            </div>
            <div class="col-md-2">
            <div class="mb-1">
                <div class="form-group">
                <label>Number</label>
                <input type="text" class="form-control " id="txt_num" name="txt_num" value="" maxlength="2">
                </div>
            </div>
            </div>
            <div class="col-md-3">
            <div class="mb-1">
                <div class="form-group">
                <label>Unit</label>
                <input class="form-control" list="txtunit" id="txt_unit" name="txt_unit" value="">
                <datalist id="txtunit">
                    @foreach ($unit as $un)
                    <option value="{{ $un->nama_unit }}">
                                {{ $un->nama_unit }}
                    </option>
                        @endforeach
                </datalist>
                </div>
            </div>
            </div>
            <div class="col-md-2">
            <div class="mb-1">
                <div class="form-group">
                <label>Capacity</label>
                <input type="number" class="form-control " id="txt_capacity" name="txt_capacity" value="" min="0">
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
 -->

 <div class="modal fade" id="modal-edit-lokasi">
    <form action="{{ route('simpan-edit') }}" method="post" onsubmit="submitForm(this, event)">
         @method('GET')
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-sb text-light">
                    <h4 class="modal-title">Edit Lokasi</h4>
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
                <label>Area Lokasi</label>
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
                <label>Inisial Penyimpanan</label>
                <input type="text" class="form-control " id="txt_inisial" name="txt_inisial" value="">
                </div>
            </div>
            </div>
            <div class="col-md-12">
            <div class="mb-1">
                <div class="form-group">
                <label>Baris</label>
                <input type="number" class="form-control " id="txt_baris" name="txt_baris" value="" min="0">
                </div>
            </div>
            </div>
            <div class="col-md-12">
            <div class="mb-1">
                <div class="form-group">
                <label>Tingkat</label>
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
                <label>Nomor</label>
                <input type="text" class="form-control " id="txt_num" name="txt_num" value="" maxlength="2">
                </div>
            </div>
            </div>
            <div class="col-md-12">
            <div class="mb-1">
                <div class="form-group">
                <label>Satuan</label>
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
                <label>Kapasitas</label>
                <input type="number" class="form-control " id="txt_capacity" name="txt_capacity" value="" min="0">
                </div>
            </div>
            </div>
        </div>
    </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="fa fa-window-close" aria-hidden="true"></i> Tutup</button>
                    <button type="submit" class="btn btn-sb toastsDefaultDanger"><i class="fa-solid fa-floppy-disk"></i> Simpan</button>
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
                    <h4 class="modal-title">Tambah Lokasi</h4>
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
                <label>Kode Lokasi</label>
                <input type="text" class="form-control " id="kode_lok" name="kode_lok" value="" readonly>
                </div>
            </div>
            </div>
            <div class="col-md-12">
            <div class="mb-1">
                <div class="form-group">
                <label>Area Lokasi</label>
                <select class="form-control select2bs4" id="txt_area_new" name="txt_area_new" style="width: 100%;" >
                    <option selected="selected" value="">Pilih Area</option>
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
                <label>Inisial Penyimpanan</label>
                <input type="text" class="form-control " id="txt_inisial_new" name="txt_inisial_new" value="" oninput="setinisial()">
                </div>
            </div>
            </div>
            <div class="col-md-12">
            <div class="mb-1">
                <div class="form-group">
                <label>Baris</label>
                <input type="number" class="form-control " id="txt_baris_new" name="txt_baris_new" value="" min="0" oninput="setinisial()">
                </div>
            </div>
            </div>
            <div class="col-md-12">
            <div class="mb-1">
                <div class="form-group">
                <label>Tingkat</label>
                <input type="number" class="form-control " id="txt_level_new" name="txt_level_new" value="" min="0" oninput="setinisial()">
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
                <label>Nomor</label>
                <input type="text" class="form-control " id="txt_num_new" name="txt_num_new" value="" maxlength="2" oninput="setinisial()">
                </div>
            </div>
            </div>
            <div class="col-md-12">
            <div class="mb-1">
                <div class="form-group">
                <label>Satuan</label>
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
                <label>Kapasitas</label>
                <input type="number" class="form-control " id="txt_capacity_new" name="txt_capacity_new" value="" min="0">
                </div>
            </div>
            </div>
        </div>
    </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="fa fa-window-close" aria-hidden="true"></i> Tutup</button>
                    <button type="submit" class="btn btn-sb toastsDefaultDanger"><i class="fa-solid fa-floppy-disk"></i> Simpan</button>
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
</script>

<script type="text/javascript">
    function setinisial(){
        let ini = $('#txt_inisial_new').val(); 
        let row = $('#txt_baris_new').val(); 
        let level = $('#txt_level_new').val(); 
        let num = $('#txt_num_new').val(); 
        let kode  = ini + '.' + row + '.' + level + '.' + num;
    
    $('#kode_lok').val(kode);
    }
</script>

<script>
    let datatable = $("#datatable").DataTable({
        ordering: false,
        processing: true,
        serverSide: true,
        ajax: {
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '{{ route('master-lokasi') }}',
            dataType: 'json',
            dataSrc: 'data',
            data: function(d) {
                d.area = $('#area').val();
            },
        },
        columns: [{
                data: 'area_lok'
            },
            {
                data: 'kode_lok'
            },
            {
                data: 'unit'
            },
            {
                data: 'kapasitas'
            },
            {
                data: 'create_user'
            },
            {
                data: 'status'
            },
            {
                data: 'id'
            },
            
        ],
        columnDefs: [{
                targets: [2],
                render: (data, type, row, meta) => data ? data.toUpperCase() : "-"
            },
            {
                targets: [6],
                render: (data, type, row, meta) => {
                    console.log(row);
                    if (row.status == 'Active') {
                    return `<div class='d-flex gap-1 justify-content-center'> 
                   <button type='button' class='btn btn-sm btn-warning' href='javascript:void(0)' onclick='editdata("` + row.id + `","` + row.kapasitas + `","` + row.inisial_lok + `","` + row.baris_lok + `","` + row.level_lok + `","` + row.no_lok + `","` + row.area_lok + `","` + row.unit + `","` + row.unit_roll + `","` + row.unit_bundle + `","` + row.unit_box + `","` + row.unit_pack + `")'><i class="fa-solid fa-pen-to-square"></i></button>
                    <button type='button' class='btn btn-sm btn-info' onclick='printlokasi("` + row.id + `")'><i class='fa fa-file-pdf'></i></button>
                    <button type='button' class='btn btn-sm btn-success' href='javascript:void(0)' onclick='nonactive_lokasi("` + row.id + `","` + row.status + `","` + row.kode_lok + `")'><i class='fa fa-unlock-alt'></i></button> 
                    </div>`;
                    }else{
                        return `<div class='d-flex gap-1 justify-content-center'> 
                    <button type='button' class='btn btn-sm btn-danger' href='javascript:void(0)' onclick='nonactive_lokasi("` + row.id + `","` + row.status + `","` + row.kode_lok + `")'><i class='fa fa-lock'></i></button> 
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
    function printlokasi(id) {

            $.ajax({
                url: '{{ route('print-lokasi') }}/'+id,
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