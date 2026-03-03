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
    <form action="{{ route('save-approve-packing-in') }}" method="post" onsubmit="submitappForm(this, event)">
        @method('GET')
        <div class="card card-sb">
            <div class="card-header">
                <h5 class="card-title fw-bold mb-0">Approval Packing In Subcont</h5>
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

        </div>

    </div>
</div>
</div>
</div>

                                        <div class="table-responsive">
                                            <table id="datatable" class="table table-bordered table-striped table-head-fixed table w-100 text-nowrap">
                                                <thead>
                                                    <tr>
                                                        <th class="text-center">No Trans</th>
                                                        <th class="text-center">Tgl Penerimaan</th>
                                                        <th class="text-center">No PO</th>
                                                        <th class="text-center">Supplier</th>
                                                        <th class="text-center">Buyer</th>
                                                        <th class="text-center">Jenis Penerimaan</th>
                                                        <th class="text-center">Jenis Dokumen</th>
                                                        <th class="text-center">Dibuat Oleh</th>
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


 <div class="modal fade" id="modalDetail" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">

      <div class="modal-header bg-sb text-light">
        <h5 class="modal-title">Detail Packing In</h5>
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
                    <th class="text-end">Qty Reject</th>
                    <th>Unit</th>
                </tr>
            </thead>
            <tbody></tbody>

            <tfoot>
              <tr>
                <th colspan="6" class="text-end">Total</th>
                <th class="text-end" id="tfoot_qty">0</th>
                <th class="text-end" id="tfoot_qty_reject">0</th>
                <th></th>
              </tr>
            </tfoot>
        </table>

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
                url: '{{ route('approve-packing-in-subcont') }}',
                dataType: 'json',
                dataSrc: 'data',
                data: function(d) {
                    d.tgl_awal = $('#tgl_awal').val();
                    d.tgl_akhir = $('#tgl_akhir').val();
                },
            },
            columns: [{
                data: 'no_bpb'
            },
            {
                data: 'tgl_bpb'
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
                data: 'jenis_penerimaan'
            },
            {
                data: 'jenis_dok'
            },
            {
                data: 'created_by'
            },
            {
                data: 'id'
            },
            {
                data: 'no_bpb'
            },
            {
                data: 'no_bpb'
            }

            ],
            columnDefs: [{
                targets: [4],
                render: (data, type, row, meta) => data ? data.toUpperCase() : "-"
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
                render: (data, type, row, meta) => data ? data : "-"
            },
        {
            targets: [8],
            render: (data, type, row, meta) => {
                console.log(row);
    
                        return '<div class="d-flex gap-1 justify-content-center" style="padding-top:5px;"><input type="checkbox" id="chek_id' + meta.row +
                        '" name="chek_id[' + meta.row + ']" class="flat" value="1" ></td></div>';
                   
            }
        },
        {
            targets: [9],
            className: "d-none",
            render: (data, type, row, meta) => {
                return '<div class="d-flex gap-1 justify-content-center"><input type="text" id="id_bpb' + meta.row +
                '" name="id_bpb[' + meta.row + ']" class="flat" value="' + data + '" ></td></div>';
            }
        },
        {
            targets: [10],
            render: (data, type, row, meta) => {
                return `<div class="d-flex gap-1 justify-content-center"><a ><i class="fa-solid fa-circle-info fa-lg" style="color:DarkCyan;" onclick='showDetail("${row.id}")'></i></a></div>`;
            }
        }
        ]
    });

async function dataTableReload() {
    return datatable.ajax.reload(() => {
        document.getElementById('jumlah_data').value = datatable.data().count();
    });
}


function showDetail(id) {

    let url = "{{ route('get-detail-packing-in', ':id') }}".replace(':id', id);

    $.get(url, function(res){

        $("#d_no_bppb").text(res?.header?.no_bpb ?? '-');
        $("#d_tgl_bppb").text(res?.header?.tgl_bpb ?? '-');
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
                    <td>${parseFloat(d.qty_reject ?? 0)}</td>
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
                },
                {
                    targets:7, 
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

                let total_reject = api
                    .column(7, { search:'applied' })
                    .data()
                    .reduce(function(a,b){
                        return toNum(a) + toNum(b);
                    }, 0);

                let formatted_reject = total_reject.toLocaleString('en-US');

                $(api.column(7).footer()).html(formatted_reject);
                $("#tfoot_qty_reject").text(formatted_reject);

            }
        });

        $("#modalDetail").modal('show');
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
