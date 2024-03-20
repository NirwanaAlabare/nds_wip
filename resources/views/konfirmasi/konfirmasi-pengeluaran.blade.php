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
<form action="{{ route('approve-pengeluaran-all') }}" method="post" onsubmit="submitappForm(this, event)">
    @method('GET')
<div class="card card-sb">
    <div class="card-header">
        <h5 class="card-title fw-bold mb-0">Konfirmasi Pengeluaran Bahan Baku</h5>
    </div>
    <div class="card-body">
        <div class="d-flex align-items-end gap-3 mb-3">
                <div class="col-md-12">
            <div class="form-group row">
            <div class="col-md-2">
            <div class="mb-1">
                <div class="form-group">
                <label class="form-label">From</label>
                    <input type="date" class="form-control form-control-sm" id="tgl_awal" name="tgl_awal"
                        value="{{ date('Y-m-d') }}">
                </div>
            </div>
            </div>

            <div class="col-md-2">
            <div class="mb-1">
                <div class="form-group">
                <label class="form-label">To</label>
                    <input type="date" class="form-control form-control-sm" id="tgl_akhir" name="tgl_akhir"
                        value="{{ date('Y-m-d') }}">
                </div>
            </div>
            </div>
            
            <div class="col-md-5" style="padding-top: 0.5rem;">
            <div class="mt-4 ">
                <input type='button' class='btn btn-primary btn-sm' onclick="dataTableReload();" value="Search">
                <button type="submit" class="btn btn-success btn-sm toastsDefaultDanger"><i class="fa fa-thumbs-up" aria-hidden="true"></i> Approve</button>
            <!-- <button class="btn btn-success" onclick="printbarcode('SA')"> <i class="fa-solid fa-barcode"></i> Saldo Awal</button> -->
            <!-- <a href="http://10.10.5.49:8082/erp/pages/forms/pdfBarcode_whsSA.php?id='SA'&mode='barcode'" class="btn btn-success">
                <i class="fa-solid fa-barcode"></i>
                Saldo Awal
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
                <input type="text"  id="cari_grdok" name="cari_grdok" autocomplete="off" placeholder="Search GR Document..." onkeyup="carigrdok()">
                <input type="hidden" class="form-control" id="jumlah_data" name="jumlah_data"
                                            readonly>
        </div>
        <div class="table-responsive" style="max-height: 400px">
            <table id="datatable" class="table table-bordered table-striped table-head-fixed table-sm w-100 text-nowrap">
                <thead>
                    <tr>
                        <th class="text-center">No BPPB</th>
                        <th class="text-center">Tgl BPPB</th>
                        <th class="text-center">Tujuan</th>
                        <th class="text-center">Buyer</th>
                        <th class="text-center">Qty</th>
                        <th class="text-center">Satuan</th>
                        <th class="text-center">Create By</th>
                        <th class="text-center">Check</th>
                        <th style="display:none;">Check</th>
                        <th class="text-center">Detail</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>
</form>

<div class="modal fade " id="modal_det" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
        <div class="modal-header bg-info text-light">
                    <h4 class="modal-title" id="modal_title1">11</h4>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
        <div class="modal-body">
            <div class="row">
                <div class="col-lg-12">
                    <!-- style="height: 400px" -->
                <div class="table-responsive" id="table_modal">
                    
                </div> 
                </div>
            </div>
        </div>
    </div>
  </div>
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
<script type="text/javascript">
    function submitappForm(e, evt) {
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

                        // e.reset();

                        // $('#cbows').val("").trigger("change");
                        // $("#cbomarker").prop("disabled", true);

                        Swal.fire({
                            icon: 'success',
                            title: 'Data Berhasil Dikonfirmasi',
                            // html: "No. Form Cut : <br>" + res.message,
                            showCancelButton: false,
                            showConfirmButton: true,
                            confirmButtonText: 'Oke',
                            timer: 5000,
                            timerProgressBar: true
                        })

                        dataTableReload();
                    }
                },

            });
        }
</script>

<script type="text/javascript">
    function showdata(data) {
        return $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route("get-data-pengeluaran") }}',
                type: 'get',
                data: {
                    no_bppb: data,
                },
                success: function (res) {
                    if (res) {
                        $('#modal_det').modal('show');
                        $('#modal_title1').html(data);
                        document.getElementById('table_modal').innerHTML = res;
                        $("#tableshow").DataTable({
                            "responsive": true,
                            "autoWidth": false,
                        })
                    }
                }
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
            url: '{{ route('konfirmasi-pengeluaran') }}',
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
                data: 'tujuan'
            },
            {
                data: 'buyer'
            },
            {
                data: 'qty'
            },
            {
                data: 'satuan'
            },
            {
                data: 'user_create'
            },
            {
                data: 'id'
            },
            {
                data: 'no_bppb'
            },
            {
                data: 'no_bppb'
            }

        ],
        columnDefs: [{
                targets: [4],
                render: (data, type, row, meta) => data ? data : "-"
            },
            {
                targets: [2],
                render: (data, type, row, meta) => data ? data : "-"
            },
            {
                targets: [3],
                render: (data, type, row, meta) => data ? data : "-"
            },
            {
                targets: [5],
                render: (data, type, row, meta) => data ? data : "-"
            },
            {
                targets: [7],
                render: (data, type, row, meta) => {
                    console.log(row);
                    return '<div class="d-flex gap-1 justify-content-center" style="padding-top:5px;"><input type="checkbox" id="chek_id' + meta.row +
                        '" name="chek_id[' + meta.row + ']" class="flat" value="1" ></td></div>';
                }
            },
            {
                targets: [8],
                className: "d-none",
                render: (data, type, row, meta) => {
                    return '<div class="d-flex gap-1 justify-content-center"><input type="text" id="id_bpb' + meta.row +
                        '" name="id_bpb[' + meta.row + ']" class="flat" value="' + data + '" ></td></div>';
                }
            },
            {
                targets: [9],
                render: (data, type, row, meta) => {
                    return `<div class="d-flex gap-1 justify-content-center"><a ><i class="fa-solid fa-circle-info fa-lg" style="color:DarkCyan;" onclick='showdata("` + data + `")'></i></a></div>`;
                }
            }
        ]
    });

    async function dataTableReload() {
            return datatable.ajax.reload(() => {
                document.getElementById('jumlah_data').value = datatable.data().count();
            });
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
