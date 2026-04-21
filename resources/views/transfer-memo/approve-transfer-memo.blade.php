
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
    <form action="{{ route('save-approve-sewing-in') }}" method="post" onsubmit="submitappForm(this, event)">
        @method('GET')
        <div class="card card-sb">
            <div class="card-header">
                <h5 class="card-title fw-bold mb-0 d-flex align-items-center text-white">
    Approval Transfer Memo

    <span class="ms-2 position-relative">
        <!-- Icon lonceng putih -->
        <i class="fa fa-bell text-white" style="font-size:18px;"></i>

        <!-- Badge merah -->
        @if($total_pending > 0)
<span id="badge_pending" 
      class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
    {{ $total_pending }}
</span>
@endif

    </span>
</h5>

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
                                <!--     <button type="submit" class="btn btn-success btn-sm toastsDefaultDanger"><i class="fa fa-thumbs-up" aria-hidden="true"></i> Approve</button>
 -->
        </div>

    </div>
</div>
</div>
</div>

                                        <div class="table-responsive">
                                            <table id="datatable" class="table table-bordered table-striped table-head-fixed table w-100 text-nowrap">
                                                <thead>
                                                    <tr>
                                                        <th class="text-center">No Transfer</th>
                                                        <th class="text-center">Tgl Transfer</th>
                                                        <th class="text-center">Keterangan</th>
                                                        <th class="text-center">User Create</th>
                                                        <th class="text-center">Action</th>
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


 <div class="modal fade" id="modalDetail" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">

      <div class="modal-header bg-sb text-light">
        <h5 class="modal-title">Detail Transfer Memo</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">

        <!-- HEADER -->
        <div class="row">
          <div class="col-6 mb-1">
            <strong>No Transfer</strong><br>
            <span id="d_no_trans">-</span>
          </div>

          <div class="col-6 mb-1">
            <strong>Transfer Date</strong><br>
            <span id="d_tgl_trans">-</span>
          </div>
        </div>

        <hr>

        <table class="table table-bordered table-striped table-sm" id="detailTable">
            <thead class="table-light">
                <tr>
                    <th>No Memo</th>
                    <th>Tgl Memo</th>
                    <th>Supplier</th>
                    <th>Jenis Transaksi</th>
                    <th>Jenis Pengiriman</th>
                    <th>Buyer</th>
                    <th>Description</th>
                    <th>check</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>

      </div>
      <div class="modal-footer">
    <button type="button" class="btn btn-success" id="btnApprove">
        Approve
    </button>
    <button type="button" class="btn btn-danger" id="btnCancelAll">
        Cancel
    </button>
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
                            timer: 2000,
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
                url: '{{ route("get-data-penerimaan") }}',
                type: 'get',
                data: {
                    no_bpb: data,
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
let detailDT = null;
</script>

    <script>
        let datatable = $("#datatable").DataTable({
    ordering: false,
    processing: false,
    serverSide: false,
    paging: false,
    searching: true,
    scrollY: '300px',
    scrollX: '300px',
    scrollCollapse: true,
    ajax: {
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url: '{{ route('approve-transfer-memo') }}',
        dataType: 'json',

        dataSrc: function (json) {

            console.log(json); // 🔥 debug dulu (penting)

            let badge = document.getElementById('badge_pending');
            let parent = document.getElementById('parent_notif');

            let total = parseInt(json.total_pending) || 0;

            if (total > 0) {
                if (!badge) {
                    let span = document.createElement('span');
                    span.id = 'badge_pending';
                    span.className = 'position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger';
                    span.innerText = total;

                    if (parent) parent.appendChild(span);
                } else {
                    badge.innerText = total;
                }
            } else {
                if (badge) badge.remove();
            }

            return json.data;
        },

        data: function(d) {
            d.tgl_awal = $('#tgl_awal').val();
            d.tgl_akhir = $('#tgl_akhir').val();
        },
    },

    columns: [
        { data: 'no_trans' },
        { data: 'tgl_trans' },
        { data: 'keterangan' },
        { data: 'create_user' },
        { data: 'id' }
    ],

    columnDefs: [
        {
            targets: [0,1,2,3],
            render: function(data) {
                return data ? data : "-";
            }
        },
        {
            targets: [4],
            render: function(data, type, row) {
                return `
                <div class="d-flex gap-1 justify-content-center">
                    <a href="javascript:void(0)" 
                       class="btn btn-sm btn-info d-flex align-items-center gap-1"
                       onclick='showDetail("${row.id}")'>
                        <i class="fa-solid fa-circle-info"></i> Action
                    </a>
                </div>`;
            }
        }
    ]
});

async function dataTableReload() {
    datatable.ajax.reload(function () {
        document.getElementById('jumlah_data').value = datatable.data().count();
    });
}


let currentNoTrans = '';



function showDetail(id) {

    let url = "{{ route('get-detail-transfer-memo', ':id') }}".replace(':id', id);

    $.get(url, function(res){

        $("#d_no_trans").text(res?.header?.no_trans ?? '-');
        $("#d_tgl_trans").text(res?.header?.tgl_trans ?? '-');
        currentNoTrans = res?.header?.no_trans ?? '';


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
                    <td>${d.nm_memo ?? ''}</td>
                    <td>${d.tgl_memo ?? ''}</td>
                    <td>${d.supplier ?? ''}</td>
                    <td>${d.jns_trans ?? ''}</td>
                    <td>${d.jns_pengiriman ?? ''}</td>
                    <td>${d.buyer ?? ''}</td>
                    <td>${d.keterangan ?? ''}</td>
                    <td><div class="d-flex justify-content-center">
                <input type="checkbox" class="form-check-input chk-detail" value="${d.id_h}" checked></div></td>
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
                
            ],
        });

        $("#modalDetail").modal('show');
    });
}



$('#btnApprove').on('click', function(){

    let approveIds = [];
    let cancelIds  = [];

    $('.chk-detail').each(function(){
        let id = $(this).val();

        if($(this).is(':checked')){
            approveIds.push(id);
        }else{
            cancelIds.push(id);
        }
    });

    let totalApprove = approveIds.length;
    let totalCancel  = cancelIds.length;

    // VALIDASI
    if(totalApprove === 0 && totalCancel === 0){
        Swal.fire('Warning','Tidak ada data','warning');
        return;
    }

    // KONFIRMASI
    Swal.fire({
        title: 'Konfirmasi Approve',
        html: `
            <b>Approve:</b> ${totalApprove} data<br>
            <b>Cancel:</b> ${totalCancel} data
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Proses',
        cancelButtonText: 'Batal'
    }).then((result) => {

        if(result.isConfirmed){

            $.ajax({
                url: "{{ route('update-transfer-memo-approve') }}",
                type: "POST",
                data: {
                    no_trans: currentNoTrans,
                    approve_ids: approveIds,
                    cancel_ids: cancelIds,
                    _token: "{{ csrf_token() }}"
                },
                beforeSend: function(){
                    Swal.fire({
                        title: 'Processing...',
                        text: 'Sedang menyimpan data',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                },
                success: function(res){
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: 'Data berhasil diproses'
                    });

                    $('#modalDetail').modal('hide');
                    dataTableReload();

                    // optional reload
                    // location.reload();
                },
                error: function(){
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Terjadi kesalahan'
                    });
                }
            });

        }

    });

});



$('#btnCancelAll').on('click', function(){

    let allIds = [];

    // ambil semua ID tanpa peduli checkbox
    $('.chk-detail').each(function(){
        allIds.push($(this).val());
    });

    let totalData = allIds.length;

    if(totalData === 0){
        Swal.fire('Warning','Tidak ada data untuk dicancel','warning');
        return;
    }

    // KONFIRMASI
    Swal.fire({
        title: 'Konfirmasi Cancel',
        html: `
            <b>No Transfer:</b> ${currentNoTrans}<br><br>
            Semua data (<b>${totalData}</b>) akan di <b>Cancel</b>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Cancel Semua',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#d33'
    }).then((result) => {

        if(result.isConfirmed){

            $.ajax({
                url: "{{ route('update-transfer-memo-cancel') }}",
                type: "POST",
                data: {
                    no_trans: currentNoTrans,
                    cancel_ids: allIds, // 🔥 semua ID langsung dikirim
                    _token: "{{ csrf_token() }}"
                },
                beforeSend: function(){
                    Swal.fire({
                        title: 'Processing...',
                        text: 'Sedang cancel semua data',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                },
                success: function(res){
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: `Semua data (${totalData}) berhasil di-cancel`
                    });

                    $('#modalDetail').modal('hide');
                     dataTableReload();

                    // optional reload
                    // location.reload();
                },
                error: function(){
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Terjadi kesalahan saat cancel'
                    });
                }
            });

        }

    });

});



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
