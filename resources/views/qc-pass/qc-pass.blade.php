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
        <h5 class="card-title fw-bold mb-0">Data QC Inspection</h5>
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

            <div class="col-md-6" style="padding-top: 0.5rem;">
            <div class="mt-4">
                <button class="btn btn-primary" onclick="dataTableReload()"> <i class="fas fa-search"></i> Search</button>
                <button class="btn btn-info" onclick="tambahdata()"> <i class="fas fa-plus"></i> New Data</button>
                <!-- <a href="{{ route('create-lokasi') }}" class="btn btn-info">
                <i class="fas fa-plus"></i>
                Add
            </a> -->
            </div>
                </div>
            </div>
            </div>
        </div>

        <div class="d-flex justify-content-between">
            <div class="ml-auto">
                <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
            </div>
                <input type="text"  id="cari_grdok" name="cari_grdok" autocomplete="off" placeholder="Search No Inspect..." onkeyup="carigrdok()">
        </div>
        
        <div class="table-responsive" style="max-height: 500px">
            <table id="datatable" class="table table-bordered table-striped table-sm w-100 text-nowrap">
                <thead>
                    <tr>
                        <th class="text-center">No Inspection</th>
                        <th class="text-center">Tgl Inspection</th>
                        <th class="text-center">Style</th>
                        <th class="text-center">No Lot</th>
                        <th class="text-center">Buyer</th>
                        <th class="text-center">Fabric Name</th>
                        <th class="text-center">Inspector</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Action</th>
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


<div class="modal fade" id="modal-tambah-data">
    <form action="{{ route('store-qcpass') }}" method="post" onsubmit="submitForm2(this, event)">
         @method('POST')
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-sb text-light">
                    <h4 class="modal-title">Inspection Header Form</h4>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!--  -->
                    <div class="form-group row">
            <div class="col-md-12">
            <div class="form-group row">
            <div class="col-md-12">
            <div class="mb-1">
                <div class="form-group">
                <label>InsPection Number</label>
                @foreach ($kode_ins as $kodeins)
                <input type="text" class="form-control " id="txt_no_ins" name="txt_no_ins" value="{{ $kodeins->kode }}" readonly>
                @endforeach
                </div>
            </div>
            </div>
            <div class="col-md-12">
            <div class="mb-1">
                <div class="form-group">
                <label>Inspection Date</label>
                <input type="date" class="form-control form-control" id="tgl_ins" name="tgl_ins"
                        value="{{ date('Y-m-d') }}">
                </div>
            </div>
            </div>
            <div class="col-md-12">
            <div class="mb-1">
                <div class="form-group">
                <label>Barcode</label>
                <input type="text" class="form-control " id="txt_barcode" name="txt_barcode" value="" onkeyup ="getdataitem(this.value)">
                <br>
                <div id="reader"></div>
                </div>
            </div>
            </div>
            <div class="col-md-12">
            <div class="mb-1">
                <div class="form-group">
                <label>ID Item</label>
                <input type="text" class="form-control " id="txt_id_item" name="txt_id_item" value="" onkeyup ="getdataitem2(this.value)">
                <br>
                <div id="reader"></div>
                </div>
            </div>
            </div>
            <div class="col-md-12">
            <div class="mb-1">
                <div class="form-group">
                <label>Color</label>
                <input type="text" class="form-control " id="txt_color" name="txt_color" value="" min="0">
                </div>
            </div>
            </div>
        </div>
    </div>
    <div class="col-md-12">
        <div class="form-group row">
            <div class="col-md-12">
            <div class="mb-1">
                <div class="form-group">
                <label>Buyer</label>
                <input type="text" class="form-control " id="txt_buyer" name="txt_buyer" value="" min="0">
                </div>
            </div>
            </div>
            <div class="col-md-12">
            <div class="mb-1">
                <div class="form-group">
                <label>Fabric Name</label>
                <input type="text" class="form-control " id="txt_fab_name" name="txt_fab_name" value="" min="0">
                </div>
            </div>
            </div>
            <div class="col-md-12">
            <div class="mb-1">
                <div class="form-group">
                <label>Style</label>
                <input type="text" class="form-control " id="txt_style" name="txt_style" value="" min="0">
                </div>
            </div>
            </div>
            <div class="col-md-12">
            <div class="mb-1">
                <div class="form-group">
                <label>Lot</label>
                <input type="text" class="form-control " id="txt_lot" name="txt_lot" value="" min="0">
                </div>
            </div>
            </div>
        </div>
    </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="fa fa-window-close" aria-hidden="true"></i> Close</button>
                    <button type="submit" class="btn btn-sb toastsDefaultDanger"><i class="fa-solid fa-floppy-disk"></i> Save</button>
                </div>
            </div>
        </div>
    </form>
</div>


<div class="modal fade" id="modal-show-data">
    <form action="{{ route('finish-data-modal') }}" method="post" onsubmit="submitModal(this, event)">
         @method('GET')
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-sb text-light">
                    <h4 class="modal-title" id="modal-title">List Data</h4>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="modal-data">
                    
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
    var html5QrcodeScanner = null;

        // Function List :
        // -Initialize Scanner-
        async function initScan() {
            if (document.getElementById("reader")) {
                if (html5QrcodeScanner) {
                    await html5QrcodeScanner.clear();
                }

                function onScanSuccess(decodedText, decodedResult) {
                    // handle the scanned code as you like, for example:
                    console.log(`Code matched = ${decodedText}`, decodedResult);

                    // store to input text
                    let breakDecodedText = decodedText.split('-');

                    document.getElementById('txt_barcode').value = breakDecodedText[0];
                    getdataitem(breakDecodedText[0]);

                    // getScannedItem(breakDecodedText[0]);

                    html5QrcodeScanner.clear();
                }

                function onScanFailure(error) {
                    // handle scan failure, usually better to ignore and keep scanning.
                    // for example:
                    console.warn(`Code scan error = ${error}`);
                }

                html5QrcodeScanner = new Html5QrcodeScanner(
                    "reader", {
                        fps: 10,
                        qrbox: {
                            width: 250,
                            height: 250
                        }
                    },
                    /* verbose= */
                    false);

                html5QrcodeScanner.render(onScanSuccess, onScanFailure);
            }
        }
</script>

<script type="text/javascript">
    function getdataitem(item) {
           return $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route("get-data-item") }}',
                type: 'get',
                data: {
                    id_item: item,
                },
                success: function (res) {
                    if (res) {
                        console.log(res[0].id_item);
                        $('#txt_id_item').val(res[0].id_item);
                        $('#txt_color').val(res[0].color);
                        $('#txt_buyer').val(res[0].supplier);
                        $('#txt_fab_name').val(res[0].itemdesc);
                        $('#txt_style').val(res[0].styleno);
                        $('#txt_lot').val(res[0].lot_no);
                        // document.getElementById('txt_wsglobal').innerHTML = res;
                    }
                },
            });
        }
</script>

<script type="text/javascript">
    function getdataitem2(item) {
           return $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route("get-data-item2") }}',
                type: 'get',
                data: {
                    id_item: item,
                },
                success: function (res) {
                    if (res) {
                        console.log(res[0].id_item);
                        $('#txt_color').val(res[0].color);
                        $('#txt_buyer').val(res[0].supplier);
                        $('#txt_fab_name').val(res[0].itemdesc);
                        $('#txt_style').val(res[0].styleno);
                        $('#txt_lot').val(res[0].lot_no);
                        // document.getElementById('txt_wsglobal').innerHTML = res;
                    }
                },
            });
        }
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
            url: '{{ route('qc-pass') }}',
            dataType: 'json',
            dataSrc: 'data',
            data: function(d) {
                d.tglawal = $('#tgl_awal').val();
                d.tglakhir = $('#tgl_akhir').val();
            },
        },
        columns: [{
                data: 'no_insp'
            },
            {
                data: 'tgl_insp'
            },
            {
                data: 'no_style'
            },
            {
                data: 'no_lot'
            },
            {
                data: 'buyer'
            },
            {
                data: 'fabric_name'
            },
            {
                data: 'inspektr'
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
                targets: [3],
                render: (data, type, row, meta) => data ? data.toUpperCase() : "-"
            },
            {
                targets: [4],
                render: (data, type, row, meta) => data ? data.toUpperCase() : "-"
            },
            {
                targets: [5],
                render: (data, type, row, meta) => data ? data.toUpperCase() : "-"
            },
            {
                targets: [6],
                render: (data, type, row, meta) => data ? data.toUpperCase() : "-"
            },
            {
                targets: [8],
                render: (data, type, row, meta) => {
                    console.log(data);
                    //<button type='button' class='btn btn-sm btn-info' href='javascript:void(0)' onclick='showdata_det("` + row.id + `","` + row.no_insp + `")'><i class="fa-solid fa-table-list"></i></button>
                    if (row.inspektr != null) {
                         return `<div class='d-flex gap-1 justify-content-center'> 
                    <a href="{{ route('create-qcpass') }}/`+data+`"><button type='button' class='btn btn-sm btn-danger'><i class="fa-solid fa-pen-to-square"></i></button></a>
                    <a href="{{ route('show-qcpass') }}/`+data+`" target="_blank"><button type='button' class='btn btn-sm btn-info'><i class="fa-solid fa-table-list"></i></button></a>
                    <a href="{{ route('export-qcpass') }}/`+data+`" target="_blank"><button type='button' class='btn btn-sm btn-success'><i class="fa-solid fa-file-pdf"></i></button></a>
                     </div>`;
                    }
                    return `<div class='d-flex gap-1 justify-content-center'> 
                    <a href="{{ route('create-qcpass') }}/`+data+`"><button type='button' class='btn btn-sm btn-danger'><i class="fa-solid fa-pen-to-square"></i></button></a>
                     </div>`;
                   //  if (row.status == 'Active') {
                   //  return `<div class='d-flex gap-1 justify-content-center'> 
                   // <button type='button' class='btn btn-sm btn-warning' href='javascript:void(0)' onclick='editdata("` + row.id + `","` + row.kapasitas + `","` + row.inisial_lok + `","` + row.baris_lok + `","` + row.level_lok + `","` + row.no_lok + `","` + row.area_lok + `","` + row.unit + `","` + row.unit_roll + `","` + row.unit_bundle + `","` + row.unit_box + `","` + row.unit_pack + `")'><i class="fa-solid fa-pen-to-square"></i></button>
                   //  <button type='button' class='btn btn-sm btn-info' onclick='printlokasi("` + row.id + `")'><i class='fa fa-file-pdf'></i></button>
                   //  <button type='button' class='btn btn-sm btn-success' href='javascript:void(0)' onclick='nonactive_lokasi("` + row.id + `","` + row.status + `","` + row.kode_lok + `")'><i class='fa fa-unlock-alt'></i></button> 
                   //  </div>`;
                   //  }else{
                   //      return `<div class='d-flex gap-1 justify-content-center'> 
                   //  <button type='button' class='btn btn-sm btn-danger' href='javascript:void(0)' onclick='nonactive_lokasi("` + row.id + `","` + row.status + `","` + row.kode_lok + `")'><i class='fa fa-lock'></i></button> 
                   //  </div>`;
                   //  }
                }
            }
        ]
    });

    function dataTableReload() {
        datatable.ajax.reload();
    }
</script>
<script type="text/javascript">
    function showdata_det(id,no_insp){

            $('#modal-show-data').modal('show'); 
            document.getElementById('modal-title').innerHTML = no_insp; 
            return $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route("get_data_detailqc") }}',
                type: 'get',
                data: {
                    id_h: id,
                },
                success: function (res) {
                     if (res) {
                        document.getElementById('modal-data').innerHTML = res;
                        $('#tableshow').dataTable({
                            "bFilter": false,
                        });
                    }
                },
            });
    }
</script>
<script type="text/javascript">

    function tambahdata(){
    $('#modal-tambah-data').modal('show'); 
    initScan();
    $('#txt_color').val('');
    $('#txt_buyer').val('');
    $('#txt_fab_name').val('');
    $('#txt_style').val('');
    $('#txt_lot').val('');
    $('#txt_id_item').val('');
    $('#txt_barcode').val('');
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
    function submitForm2(e, evt) {
    evt.preventDefault();

    clearModified();

    $.ajax({
        url: e.getAttribute('action'),
        type: e.getAttribute('method'),
        data: new FormData(e),
        processData: false,
        contentType: false,
        success: function(res) {
            if (res.status == 200) {
                $('.modal').modal('hide');

                // if (res.redirect != '') {
                //     if (res.redirect != 'reload') {
                //         location.href = res.redirect;
                //     } else {
                //         location.reload();
                //     }
                // }

                Swal.fire({
                    icon: 'success',
                    title: res.message2,
                    text: res.message,
                    showCancelButton: false,
                    showConfirmButton: true,
                    confirmButtonText: 'Oke',
                    timer: 5000,
                    timerProgressBar: true
                }).then((result)=>{
                    if (res.redirect != '') {
                    if (res.redirect != 'reload') {
                        location.href = res.redirect;
                    } else {
                        location.reload();
                    }
                }
                })

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