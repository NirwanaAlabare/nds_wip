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


                        <div class="col-md-3" style="padding-top: 0.5rem;">
                            <div class="mt-4 ">
                                <button class="btn btn-primary " onclick="dataTableReload()"> <i class="fas fa-search"></i> Search</button>
                                <!-- <button class="btn btn-info" onclick="tambahdata()"> <i class="fas fa-plus"></i> Add Data</button> -->
                                <a href="{{ route('data-rak') }}" class="btn btn-info">
                                    <i class="fas fa-plus"></i>
                                    Add Data
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            {{-- <div class="d-flex justify-content-between">
                <div class="ml-auto">
                    <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                </div>
                <input type="text"  id="cari_grdok" name="cari_grdok" autocomplete="off" placeholder="Search Data..." onkeyup="carigrdok()">
            </div> --}}
            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-striped w-100 text-nowrap">
                    <thead>
                        <tr>
                            <th class="text-center">No transaksi</th>
                            <th class="text-center">Tipe Item</th>
                            <th class="text-center">Tgl Stok</th>
                            <th class="text-center">Qty Item</th>
                            <th class="text-center">Qty SO</th>
                            <th class="text-center">Sisa</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Created By</th>
                            <th class="text-center">Created Date</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>


    <div class="modal fade" id="modal_tblroll" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-xl">
        <div class="modal-content">
          <div class="modal-header bg-sb">
            <h5 id="modal_title1" class="modal-title"></h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <div class="d-flex mb-3">
                <input type="hidden" class="form-control " id="txt_no_dokumen" name="txt_no_dokumen" value="" readonly>
                <a onclick="export_excel_barcode()" class="btn btn-success shadow-sm">
                    <i class="fas fa-file-excel me-2"></i>
                    Export
                </a>
            </div>
            <div id="table_modal"></div>
        </div>
    </div>
</div>
</div>


<div class="modal fade" id="modal-appv-mutlok">
    <form action="{{ route('cancel-data-opname') }}" method="post" onsubmit="submitForm(this, event)">
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
                    <label for="id_inv" class="col-sm-12 col-form-label" >Sure Cancel Data Stock Opname :</label>
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
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fa fa-window-close" aria-hidden="true"></i> Close</button>
                <button type="submit" class="btn btn-danger toastsDefaultDanger"><i class="fa fa-thumbs-up" aria-hidden="true"></i> Cancel</button>
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
            ajax: {
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route('list-data-stok') }}',
                dataType: 'json',
                dataSrc: 'data',
                data: function(d) {
                    d.tgl_awal = $('#tgl_awal').val();
                    d.tgl_akhir = $('#tgl_akhir').val();
                },
            },
            columns: [{
                data: 'no_transaksi'
            },
            {
                data: 'tipe_item'
            },
            {
                data: 'tgl_saldo'
            },
            {
                data: 'qty_show'
            },
            {
                data: 'qty_so_show'
            },
            {
                data: 'qty_sisa_show'
            },
            {
                data: 'status'
            },
            {
                data: 'copy_user'
            },
            {
                data: 'created_at'
            },
            {
                data: 'no_transaksi'
            }

            ],
            columnDefs: [{
                targets: [9],
                render: (data, type, row, meta) => {
                    console.log(row);
                    // if (row.status != 'CANCEL') {
                    //     return `<div class='d-flex gap-1 justify-content-center'>
                    //     <button type='button' class='btn btn-sm btn-danger' href='javascript:void(0)' onclick='approve_mutlok("` + row.no_transaksi + `")'><i class="fa-solid fa-trash"></i></button>
                    //     <button type='button' class='btn btn-sm btn-info' onclick='showdata("` + data + `")'><i class="fa-solid fa-circle-info"></i></button>
                    // <a href="{{ route('show-detail-so') }}/`+row.id+`"><button type='button' class='btn btn-sm btn-info'><i class="fa-solid fa-table-list"></i></button></a>
                    //     </div>`;
                    // }else{
                    //     return `<div class='d-flex gap-1 justify-content-center'> -
                    //     </div>`;
                    // }

                    if (row.status == 'OPEN') {
                        return `<div class='d-flex gap-1 justify-content-center'>
                        <button type='button' class='btn btn-sm btn-danger' 
                        href='javascript:void(0)' 
                        title="Cancel Data" 
                        onclick='canceldata("` + row.no_transaksi + `","` + row.id + `")'>
                        <i class="fa-solid fa-undo"></i>
                        </button>
                        <button type='button' class='btn btn-sm btn-warning' 
                        href='javascript:void(0)' 
                        title="Change Status" 
                        onclick='draftdata("` + row.no_transaksi + `","` + row.id + `")'>
                        <i class="fa-solid fa-person-circle-check"></i>
                        </button>
                        <button type='button' class='btn btn-sm btn-info' 
                        title="Show Data" 
                        onclick='showdata("` + data + `")'>
                        <i class="fa-solid fa-circle-info"></i>
                        </button>
                        </div>`;
                    } else if (row.status == 'DRAFT') {
                        return `<div class='d-flex gap-1 justify-content-center'>
                        <button type='button' class='btn btn-sm btn-danger' 
                        href='javascript:void(0)' 
                        title="Cancel Data" 
                        onclick='canceldata("` + row.no_transaksi + `","` + row.id + `")'>
                        <i class="fa-solid fa-undo"></i>
                        </button>
                        <button type='button' class='btn btn-sm btn-success' 
                        href='javascript:void(0)' 
                        title="Change Status" 
                        onclick='finaldata("` + row.no_transaksi + `","` + row.id + `")'>
                        <i class="fa-solid fa-person-circle-check"></i>
                        </button>
                        <button type='button' class='btn btn-sm btn-info' 
                        title="Show Data" 
                        onclick='showdata("` + data + `")'>
                        <i class="fa-solid fa-circle-info"></i>
                        </button>
                        </div>`;
                    } else if (row.status == 'FINAL') {
                        return `<div class='d-flex gap-1 justify-content-center'>
                        <button type='button' class='btn btn-sm btn-info' 
                        title="Show Data" 
                        onclick='showdata("` + data + `")'>
                        <i class="fa-solid fa-circle-info"></i>
                        </button>
                        </div>`;
                    } else {
                        return `<div class='d-flex gap-1 justify-content-center'>-</div>`;
                    }


                }
            }

            ]
        });

function dataTableReload() {
    datatable.ajax.reload();
}


function canceldata($no_transaksi,$id){

    let no_transaksi  = $no_transaksi;
    let id  = $id;

    Swal.fire({
      title: "Are you sure?",
      text: "Cancel Transaksi " + no_transaksi,
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#3085d6",
      cancelButtonColor: "#d33",
      confirmButtonText: "Yes, Cancel it!",
      cancelButtonText: "Close"
  }).then((result) => {
      if (result.isConfirmed) {

        return $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '{{ route("cancel-report-so") }}',
            type: 'get',
            data: {
                no_transaksi: no_transaksi,
            },
            success: function (res) {
                Swal.fire({
                    title: "Cancelled!",
                    text: "Data has been Cancelled.",
                    icon: "success"
                }).then(async (result) => {
                    window.location.reload()
                });
            }
        });

    }
});
}

function draftdata($no_transaksi,$id){

    let no_transaksi  = $no_transaksi;
    let id  = $id;

    Swal.fire({
      title: "Are you sure?",
      text: "Draft Transaksi " + no_transaksi,
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#3085d6",
      cancelButtonColor: "#d33",
      confirmButtonText: "Yes, Draft it!",
      cancelButtonText: "Close"
  }).then((result) => {
      if (result.isConfirmed) {

        return $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '{{ route("draft-report-so") }}',
            type: 'get',
            data: {
                no_transaksi: no_transaksi,
            },
            success: function (res) {
                Swal.fire({
                    title: "Changed!",
                    text: "Status Data has been Changed.",
                    icon: "success"
                }).then(async (result) => {
                    window.location.reload()
                });
            }
        });

    }
});
}


function finaldata($no_transaksi,$id){

    let no_transaksi  = $no_transaksi;
    let id  = $id;

    Swal.fire({
      title: "Are you sure?",
      text: "Final Transaksi " + no_transaksi,
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#3085d6",
      cancelButtonColor: "#d33",
      confirmButtonText: "Yes, Final it!",
      cancelButtonText: "Close"
  }).then((result) => {
      if (result.isConfirmed) {

        return $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '{{ route("final-report-so") }}',
            type: 'get',
            data: {
                no_transaksi: no_transaksi,
            },
            success: function (res) {
                Swal.fire({
                    title: "Changed!",
                    text: "Status Data has been Changed.",
                    icon: "success"
                }).then(async (result) => {
                    window.location.reload()
                });
            }
        });

    }
});
}

function export_excel() {
    let itemso = document.getElementById("item_so").value;
    let from = document.getElementById("from").value;
    let to = document.getElementById("to").value;

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
        url: '{{ route('export_excel_laporan_so') }}',
        data: {
            itemso: itemso,
            from: from,
            to: to
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
                link.download = "Laporan Stock Opname  " + itemso + ".xlsx";
                link.click();

            }
        },
    });
}


function export_excel_barcode() {
    let no_transaksi = document.getElementById("txt_no_dokumen").value;
    let itemso = 'Fabric';
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
    function approve_mutlok($nodok){
        // alert($id);
        let nodok  = $nodok;

        $('#txt_nodok').val(nodok);
        $('#modal-appv-mutlok').modal('show');
    }

    function number_format(number, decimals = 0, dec_point = '.', thousands_sep = ',') {
        number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
        let n = !isFinite(+number) ? 0 : +number,
        prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
        sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
        dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
        s = '';

        s = (prec ? n.toFixed(prec) : Math.round(n).toString()).split('.');
        if (s[0].length > 3) {
            s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
        }
        if ((s[1] || '').length < prec) {
            s[1] = s[1] || '';
            s[1] += new Array(prec - s[1].length + 1).join('0');
        }
        return s.join(dec);
    }


    function showdata(data) {
        return $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '{{ route("get-detail-opname") }}',
            type: 'get',
            data: { no_transaksi: data },
            success: function (res) {
                if (res) {
                    $('#modal_tblroll').modal('show');
                    $('#txt_no_dokumen').val(data);
                    $('#modal_title1').html('DETAIL ' + data);
                    document.getElementById('table_modal').innerHTML = res;

                // destroy DataTable lama
                if ($.fn.DataTable.isDataTable("#tableshow")) {
                    $("#tableshow").DataTable().clear().destroy();
                }

                // tambahkan input search di header
                $('#tableshow thead th').each(function () {
                    var title = $(this).text();
                    if (title !== "Qty" && title !== "Action") { 
                        $(this).html(title + '<br><input type="text" class="form-control form-control-sm" placeholder="Search"/>');
                    }
                });

                // init DataTable baru dengan paging
                var table = $("#tableshow").DataTable({
                    responsive: false,
                    autoWidth: false,
                    scrollY: "300px",
                    scrollCollapse: true,
                    paging: true,        // ✅ aktifkan paging
                    pageLength: 25,      // tampilkan 25 per page
                    lengthMenu: [10, 25, 50, 100], // opsi user
                    ordering: false,
                    info: true,
                    searching: true,
                    drawCallback: function () {
                        // hitung total qty dari ALL DATA (bukan hanya page ini)
                        var total = this.api()
                            .column(8, { search: 'applied' }) // ikut filter/search
                            .data()
                            .reduce(function (a, b) {
                                return parseFloat(a) + parseFloat(b);
                            }, 0);

                            $(this.api().column(7).footer())
                            .html(number_format(total, 2, '.', ','));
                        }
                    });

                // event untuk filter per kolom
                table.columns().every(function () {
                    var that = this;
                    $('input', this.header()).on('keyup change clear', function () {
                        if (that.search() !== this.value) {
                            that.search(this.value).draw();
                        }
                    });
                });

                // trigger pertama kali
                table.draw();
            }
        }
    });
    }


// fix header setelah modal benar-benar tampil
$('#modal_tblroll').on('shown.bs.modal', function () {
    if ($.fn.DataTable.isDataTable("#tableshow")) {
        $("#tableshow").DataTable().columns.adjust().draw();
    }
});



function deleteBarcode(id, barcode, no_transaksi) {
    Swal.fire({
        title: 'Are you sure?',
        text: "Delete barcode " + barcode + " ?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '{{ route("delete-barcode-saldo-opname") }}',
                type: 'GET',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    id: id
                },
                success: function(res) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted!',
                        text: 'The barcode has been deleted.',
                        timer: 1500,
                        showConfirmButton: false
                    });
                    // reload table
                    showdata(no_transaksi);
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Failed to delete data'
                    });
                }
            });
        }
    });
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

@endsection
