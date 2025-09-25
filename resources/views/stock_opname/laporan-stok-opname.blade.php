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
<form action="{{ route('export_excel_pemasukan') }}" method="get">
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-file-alt fa-sm"></i> Report Stock Opname</h5>
        </div>
        <div class="card-body">
            <div class="d-flex align-items-end gap-1 mb-3">
                <div class="col-md-3">
                    <div class="mb-3">
                        <label>Tipe Item</label>
                        <select class="form-control select2supp" id="item_so" name="item_so" style="width: 100%;">
                            <option selected="selected" value="">Select item</option>
                            @foreach ($item_so as $item)
                            <option value="{{ $item->nama_pilihan }}">{{ $item->nama_pilihan }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="mb-3">
                        <label class="form-label">From</label>
                        <input type="date" class="form-control form-control" id="from" name="from"
                        value="{{ date('Y-m-d') }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="mb-3">
                        <label class="form-label">To</label>
                        <input type="date" class="form-control form-control" id="to" name="to"
                        value="{{ date('Y-m-d') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        {{-- <button class="btn btn-primary btn" onclick="export_excel()">Search</button> --}}
                        <input type='button' class='btn btn-primary btn' onclick="dataTableReload();" value="Search">
                        <a onclick="export_excel()" class="btn btn-success position-relative btn">
                            <i class="fas fa-file-excel"></i>
                            Export
                        </a>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-striped table-head-fixed w-100 text-nowrap">
                    <thead>
                        <tr>
                            <th>No Transaksi</th>
                            <th>Item Tipe</th>
                            <th>Tgl Saldo</th>
                            <th>Lokasi</th>
                            <th>ID JO</th>
                            <th>ID Item</th>
                            <th>Kode Item</th>
                            <th>Deskripsi Item</th>
                            <th>Qty Item</th>
                            <th>Qty SO</th>
                            <th>Sisa</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</form>
@endsection

@section('custom-script')
<!-- DataTables  & Plugins -->
<script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>

<!-- Select2 -->
<script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
<script type="text/javascript">
    $('.select2supp').select2({
        theme: 'bootstrap4'
    })
</script>
<script>
    let datatable = $("#datatable").DataTable({
        ordering: true,
        processing: true,
        serverSide: false,
        paging: true,
        searching: true,
        scrollY: '300px',
        scrollX: '300px',
        scrollCollapse: true,
        deferLoading: 0,
        ajax: {
            url: '{{ route('laporan-stok-opname') }}',
            data: function(d) {
                d.itemSO = $('#item_so').val();
                d.dateFrom = $('#from').val();
                d.dateTo = $('#to').val();
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
            data: 'kode_lok'
        },
        {
            data: 'id_jo'
        },
        {
            data: 'id_item'
        },
        {
            data: 'goods_code'
        },
        {
            data: 'itemdesc'
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
            data: 'id'
        }
        ],
        columnDefs: [
        {
            targets: [3,4,5,6,7],
            className: "d-none",
            render: (data, type, row, meta) => data ? data : "-"
        },
        {
            targets: [12],
            render: (data, type, row, meta) => {
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
                    <a href="{{ route('show-detail-so') }}/`+data+`" title="Show Data">
                    <button type='button' class='btn btn-sm btn-info'>
                    <i class="fa-solid fa-table-list"></i>
                    </button>
                    </a>
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
                    <a href="{{ route('show-detail-so') }}/`+data+`" title="Show Data">
                    <button type='button' class='btn btn-sm btn-info'>
                    <i class="fa-solid fa-table-list"></i>
                    </button>
                    </a>
                    </div>`;
                } else if (row.status == 'FINAL') {
                    return `<div class='d-flex gap-1 justify-content-center'>
                    <a href="{{ route('show-detail-so') }}/`+data+`" title="Show Data">
                    <button type='button' class='btn btn-sm btn-info'>
                    <i class="fa-solid fa-table-list"></i>
                    </button>
                    </a>
                    </div>`;
                } else {
                    return `<div class='d-flex gap-1 justify-content-center'>-</div>`;
                }


            }
        },

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

function caridata() {
        // Declare variables
        var input, filter, table, tr, td, i, txtValue;
        input = document.getElementById("cari_item");
        filter = input.value.toUpperCase();
        table = document.getElementById("datatable");
        tr = table.getElementsByTagName("tr");

        // Loop through all table rows, and hide those who don't match the search query
        for (i = 0; i < tr.length; i++) {
            td = tr[i].getElementsByTagName("td")[25]; //kolom ke berapa
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
