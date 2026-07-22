@extends('layouts.index', ['containerFluid' => true])

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
<style type="text/css">
    .marginnya{
        margin-left: 350px;
        margin-right: 350px;
        margin-top: 10px;
    }
    @media (max-width: 1366px){
        .marginnya{
            margin-left: 20px;
            margin-right: 20px;
        }
    }
</style>
@endsection

@section('content')
<div class="marginnya">
<div class="card card-sb">
  <div class="card-header py-2">
    <h6 class="card-title fw-bold mb-0">Data Mutasi Lokasi</h6>
  </div>

  <div class="card-body">
    <div class="row align-items-end g-2 mb-3">

      <div class="col-md-2 col-sm-4">
        <label class="form-label mb-1">From</label>
        <input type="date" class="form-control form-control-sm" id="tgl_awal" name="tgl_awal"
               value="{{ date('Y-m-d') }}">
      </div>

      <div class="col-md-2 col-sm-4">
        <label class="form-label mb-1">To</label>
        <input type="date" class="form-control form-control-sm" id="tgl_akhir" name="tgl_akhir"
               value="{{ date('Y-m-d') }}">
      </div>

      <div class="col-md-3 col-sm-12 d-flex gap-2 pt-3">
        <button class="btn btn-sm btn-primary flex-grow-1" onclick="dataTableReload()">
          <i class="fas fa-search"></i> Search
        </button>
        <a href="{{ route('create-mutlokasi') }}" class="btn btn-sm btn-info flex-grow-1">
          <i class="fas fa-plus"></i> Add Data
        </a>
      </div>

    </div>

    <div>
      <table id="datatable" class="table table-bordered table-striped w-100" style="table-layout: fixed;">
        <thead class="text-center">
          <tr>
            <th>No Mutasi</th>
            <th>Tgl Mutasi</th>
            <th>Rak Tujuan</th>
            <th>Notes</th>
            <th>User</th>
            <th>Status</th>
            <th>Action</th>
            <th style="display:none;">Aksi</th>
          </tr>
        </thead>
        <tbody></tbody>
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

<script>
    let datatable = $("#datatable").DataTable({
        ordering: false,
        processing: true,
        serverSide: true,
        paging: true,
        searching: true,
        autoWidth: false,
        ajax: {
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '{{ route('mutasi-lokasi') }}',
            dataType: 'json',
            dataSrc: 'data',
            data: function(d) {
                d.tgl_awal = $('#tgl_awal').val();
                d.tgl_akhir = $('#tgl_akhir').val();
                d.no_ws = $('#no_ws').val();
            },
        },
        columns: [{
                data: 'no_mut'
            },
            {
                data: 'tgl_mut'
            },
            {
                data: 'rak_tujuan'
            },
            {
                data: 'deskripsi'
            },
            {
                data: 'user_create'
            },
            {
                data: 'status'
            },
            {
                data: 'id'
            },
            {
                data: 'filter'
            }

        ],
        columnDefs: [{
                targets: [7],
                className: "d-none",
                render: (data, type, row, meta) => {
                        return `<span hidden>` + data + `</span>`;
                }
            },
            {
                targets: [6],
                render: (data, type, row, meta) => {
                    console.log(row);
                    if (row.status == 'Pending') {
                        return `<div class='d-flex gap-1 justify-content-center'>
                   <a href="{{ route('edit-mutlok') }}/`+data+`"><button type='button' class='btn btn-sm btn-danger'><i class="fa-solid fa-pen-to-square"></i></button></a>
                    <button type='button' class='btn btn-sm btn-danger' href='javascript:void(0)' onclick='approve_mutlok("` + row.no_mut + `")'><i class="fa-solid fa-trash"></i></button>
                    <a href="http://10.10.5.62:8080/erp/pages/forms/pdfBarcode_whs_mut.php?id=`+data+`&mode='barcode'" target="_blank"><button type='button' class='btn btn-sm btn-success'><i class="fa-solid fa-barcode"></i></button></a>
                    </div>`;
                    }else{
                        return `<div class='d-flex gap-1 justify-content-center'> -
                    </div>`;
                    }
                }
            }

        ]
    });

    function dataTableReload() {
        datatable.ajax.reload();
    }

    @if(session('error'))
    Swal.fire({
        icon: 'error',
        title: 'Gagal',
        text: @json(session('error'))
    });
    @endif
</script>
<script type="text/javascript">
    function approve_mutlok(nodok){
        Swal.fire({
            icon: 'warning',
            title: 'Confirm Dialog',
            html: 'Sure Cancel Mutasi Location Number : <b>' + nodok + '</b>',
            showCancelButton: true,
            confirmButtonText: '<i class="fa fa-trash"></i> Cancel',
            confirmButtonColor: '#dc3545',
            cancelButtonText: 'Close'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route('approve-mutlok') }}',
                    type: 'get',
                    data: { txt_nodok: nodok },
                    success: function(res) {
                        if (res.status == 200) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                html: res.message,
                                showConfirmButton: true,
                                confirmButtonText: 'Oke',
                                timer: 5000,
                                timerProgressBar: true
                            }).then(() => {
                                dataTableReload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                html: res.message
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            html: 'Terjadi kesalahan saat menghubungi server.'
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
