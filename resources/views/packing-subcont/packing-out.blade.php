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
        <h5 class="card-title fw-bold mb-0">Data Packing Out Subcont</h5>
    </div>
    <div class="card-body">
        <div class="d-flex align-items-end gap-1 mb-1">
                <div class="col-md-12">
            <div class="form-group row">
            <div class="col-md-2">
            <div class="mb-1">
                <div class="form-group">
                <label class="form-label">From</label>
                    <input type="date" class="form-control form-control form-control-sm" id="tgl_awal" name="tgl_awal"
                        value="{{ date('Y-m-d') }}">
                </div>
            </div>
            </div>

            <div class="col-md-2">
            <div class="mb-1">
                <div class="form-group">
                <label class="form-label">To</label>
                    <input type="date" class="form-control form-control form-control-sm" id="tgl_akhir" name="tgl_akhir"
                        value="{{ date('Y-m-d') }}">
                </div>
            </div>
            </div>

            <div class="col-md-6" style="padding-top: 0.5rem;">
            <div class="mt-4 ">
                <button class="btn btn-sm btn-primary " onclick="dataTableReload()"> <i class="fas fa-search"></i> Search</button>
                <!-- <button class="btn btn-info" onclick="tambahdata()"> <i class="fas fa-plus"></i> Add Data</button> -->
                <a href="{{ route('create-packing-out-subcont') }}" class="btn btn-sm btn-info">
                <i class="fas fa-plus"></i>
                Add Data
            </a>
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
       <!--  <div class="d-flex justify-content-between">
            <div class="ml-auto">
                <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
            </div>
                <input type="text"  id="cari_grdok" name="cari_grdok" autocomplete="off" placeholder="Search GR Document..." onkeyup="carigrdok()">
        </div> -->
        <div class="table-responsive">
            <table id="datatable" class="table table-bordered table-striped table-head-fixed table w-100 text-nowrap">
                <thead>
                    <tr>
                        <th class="text-center">No Trans</th>
                        <th class="text-center">Tgl Pengeluaran</th>
                        <th class="text-center">No PO</th>
                        <th class="text-center">Supplier</th>
                        <th class="text-center">Buyer</th>
                        <th class="text-center">Jenis Pengeluaran</th>
                        <th class="text-center">Jenis Dokumen</th>
                        <th class="text-center">Dibuat Oleh</th>
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

<div class="modal fade" id="modalDetail" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">

      <div class="modal-header bg-sb text-light">
        <h5 class="modal-title">Detail Packing Out</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">

        <!-- HEADER -->
        <div class="row">
          <div class="col-6 mb-1">
            <strong>No Trans</strong><br>
            <span id="d_no_bppb">-</span>
          </div>

          <div class="col-6 mb-1">
            <strong>Supplier</strong><br>
            <span id="d_supplier">-</span>
          </div>

          <div class="col-6 mb-1">
            <strong>PO</strong><br>
            <span id="d_no_po">-</span>
          </div>

          <div class="col-6 mb-1">
            <strong>Buyer</strong><br>
            <span id="d_buyer">-</span>
          </div>

          <div class="col-6 mb-1">
            <strong>Tgl Trans</strong><br>
            <span id="d_tgl_bppb">-</span>
          </div>

          <div class="col-6 mb-1">
            <strong>Status</strong><br>
            <span id="d_status" class="badge bg-secondary">-</span>
          </div>
        </div>

        <hr>

        <table class="table table-bordered table-striped table-sm" id="detailTable">
            <thead class="table-light">
                <tr>
                    <th>No</th>
                    <th>No WS</th>
                    <th>Style</th>
                    <th>Item Desc</th>
                    <th>Color</th>
                    <th>Size</th>
                    <th class="text-end">Qty</th>
                    <th>Unit</th>
                </tr>
            </thead>
            <tbody></tbody>

            <tfoot>
              <tr>
                <th colspan="6" class="text-end">Total</th>
                <th class="text-end" id="tfoot_qty">0</th>
                <th></th>
              </tr>
            </tfoot>
        </table>

      </div>

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
                        <label for="id_inv" class="col-sm-12 col-form-label" >Sure Cancel BPB Number :</label>
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
                    <button type="button" class="btn btn-sb" data-bs-dismiss="modal"><i class="fa fa-window-close" aria-hidden="true"></i> Close</button>
                    <button type="submit" class="btn btn-danger toastsDefaultDanger"><i class="fa fa-trash" aria-hidden="true"></i> Cancel</button>
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
            paging: true,
            searching: true,
            scrollY: '300px',
            scrollX: '300px',
            scrollCollapse: true,
        ajax: {
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '{{ route('packing-out-subcont') }}',
            dataType: 'json',
            dataSrc: 'data',
            data: function(d) {
                d.tgl_awal = $('#tgl_awal').val();
                d.tgl_akhir = $('#tgl_akhir').val();
            },
        },
        columns: [{
                data: 'no_bppb'
            },
            {
                data: 'tgl_bppb'
            },
            {
                data: 'no_po'
            },
            {
                data: 'supplier'
            },
            {
                data: 'buyer'
            },
            {
                data: 'jenis_pengeluaran'
            },
            {
                data: 'jenis_dok'
            },
            {
                data: 'created_by'
            },
            {
                data: 'status'
            },
            {
                data: 'id'
            }

        ],
        columnDefs: [{
                targets: [3,4,5,6,8],
                render: (data, type, row, meta) => data ? data.toUpperCase() : "-"
            },
            {
                targets: [9],
                render: (data, type, row, meta) => {
                    let exportUrl = "{{ route('export-pl-packing-out', ':id') }}"
            .replace(':id', row.id);

                 if (row.status == 'DRAFT') {
                    return `<div class='d-flex gap-1 justify-content-center'>
                    <button type='button' class='btn btn-sm btn-danger' href='javascript:void(0)' onclick='CancelPackingOut("` + row.no_bppb + `")'><i class="fa-solid fa-trash"></i></button>
                    <button type='button' class='btn btn-sm btn-info' onclick='showDetail("${row.id}")'> <i class="fa-solid fa-eye"></i> Detail</button>
                    <a href='${exportUrl}' class='btn btn-sm btn-success'> <i class="fa-solid fa-file-excel"></i> PL</a>
                    </div>`;
                }else if (row.status == 'APPROVED') {
                    return `<div class='d-flex gap-1 justify-content-center'>
                    <button type='button' class='btn btn-sm btn-info' onclick='showDetail("${row.id}")'> <i class="fa-solid fa-eye"></i> Detail</button>
                    <a href='${exportUrl}' class='btn btn-sm btn-success'> <i class="fa-solid fa-file-excel"></i> PL</a>
                    </div>`;
                }else{
                    return `<div class='d-flex gap-1 justify-content-center'>
                    -
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
<script>
let detailDT = null;
</script>
<script type="text/javascript">

    function CancelPackingOut(no_bppb) {

    if (!no_bppb) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'No BPB tidak valid'
        });
        return;
    }

    Swal.fire({
        title: 'Konfirmasi',
        text: 'Yakin ingin membatalkan Packing Out ' + no_bppb + ' ?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Batalkan',
        cancelButtonText: 'Tidak',
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        reverseButtons: true
    }).then((result) => {

        if (result.isConfirmed) {

            $.ajax({
                url: "{{ route('cancel-packing-out-subcont') }}",
                type: "POST",
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    no_bppb: no_bppb
                },
                success: function(res) {
                    if (res.status === 200) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: res.message,
                            timer: 1500,
                            showConfirmButton: false
                        });

                        $('#datatable').DataTable().ajax.reload(null, false);

                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: res.message
                        });
                    }
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.message ?? 'Terjadi kesalahan'
                    });
                }
            });

        }

    });
}

function showDetail(id) {

    let url = "{{ route('get-detail-packing-out', ':id') }}".replace(':id', id);

    $.get(url, function(res){

        $("#d_no_bppb").text(res?.header?.no_bppb ?? '-');
        $("#d_tgl_bppb").text(res?.header?.tgl_bppb ?? '-');
        $("#d_no_po").text(res?.header?.no_po ?? '-');
        $("#d_supplier").text(res?.header?.supplier ?? '-');
        $("#d_buyer").text(res?.header?.buyer ?? '-');

        // STATUS BADGE
        let status = (res?.header?.status ?? '-').toUpperCase();
        let badge = 'bg-secondary';
        if(status === 'APPROVED') badge = 'bg-success';
        else if(status === 'DRAFT') badge = 'bg-warning text-dark';
        else if(status === 'REJECTED') badge = 'bg-danger';

        $("#d_status").removeClass().addClass('badge '+badge).text(status);

        // reset datatable
        if (detailDT !== null) detailDT.clear().destroy();
        $("#detailTable tbody").html('');

        // isi rows
        let rows = '';
        res.detail.forEach((d,i)=>{
            rows += `
                <tr>
                    <td>${i+1}</td>
                    <td>${d.kpno ?? ''}</td>
                    <td>${d.styleno ?? ''}</td>
                    <td>${d.itemdesc ?? ''}</td>
                    <td>${d.color ?? ''}</td>
                    <td>${d.size ?? ''}</td>
                    <td>${parseFloat(d.qty ?? 0)}</td>
                    <td>${d.unit ?? ''}</td>
                </tr>
            `;
        });
        $("#detailTable tbody").html(rows);

        // init datatable
        detailDT = $("#detailTable").DataTable({

            searching: true,
            paging: true,
            ordering: true,
            info: false,
            lengthChange: false,
            pageLength: 10,

            columnDefs:[
                {
                    targets:6, 
                    className:'text-end',
                    render:function(data){
                        return (parseFloat(data)||0).toLocaleString('en-US');
                    }
                }
            ],

            // >>> TOTAL TIDAK TERPENGARUH PAGING <<<
            footerCallback: function ( row, data, start, end, display ) {

                let api = this.api();

                let toNum = function(i){
                    if(typeof i === 'string'){
                        return parseFloat(i.replace(/,/g,'')) || 0;
                    }
                    return typeof i === 'number' ? i : 0;
                };

                // TOTAL dari semua data yang terfilter (search applied)
                let total = api
                    .column(6, { search:'applied' })
                    .data()
                    .reduce(function(a,b){
                        return toNum(a) + toNum(b);
                    }, 0);

                let formatted = total.toLocaleString('en-US');

                $(api.column(6).footer()).html(formatted);
                $("#tfoot_qty").text(formatted);
            }
        });

        $("#modalDetail").modal('show');
    });
}





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
