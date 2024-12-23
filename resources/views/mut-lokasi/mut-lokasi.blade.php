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
        <h5 class="card-title fw-bold mb-0">Data Mutasi Lokasi</h5>
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
                <label>Worksheet</label>
                <select class="form-control select2supp" id="no_ws" name="no_ws" style="width: 100%;">
                    <option selected="selected" value="ALL">ALL</option>
                        @foreach ($nows as $ws)
                    <option value="{{ $ws->no_ws }}">
                                {{ $ws->no_ws }}
                    </option>
                        @endforeach
                </select>
                </div>
            </div>
            </div>



            <div class="col-md-3" style="padding-top: 0.5rem;">
            <div class="mt-4 ">
                <button class="btn btn-primary " onclick="dataTableReload()"> <i class="fas fa-search"></i> Search</button>
                <!-- <button class="btn btn-info" onclick="tambahdata()"> <i class="fas fa-plus"></i> Add Data</button> -->
                <a href="{{ route('create-mutlokasi') }}" class="btn btn-info">
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
            <table id="datatable" class="table table-bordered table-striped table-sm w-100 text-nowrap">
                <thead>
                    <tr>
                        <th class="text-center">No Mutasi</th>
                        <th class="text-center">Tgl Mutasi</th>
                        <th class="text-center">No WS</th>
                        <th class="text-center">Notes</th>
                        <th class="text-center">User</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Action</th>
                        <th style="display:none;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-appv-mutlok">
    <form action="{{ route('approve-mutlok') }}" method="post" onsubmit="submitForm(this, event)">
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
                        <label for="id_inv" class="col-sm-12 col-form-label" >Sure Approve Mutasi Location Number :</label>
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
                data: 'no_ws'
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
                    <button type='button' class='btn btn-sm btn-info' href='javascript:void(0)' onclick='approve_mutlok("` + row.no_mut + `")'><i class="fa-solid fa-person-circle-check"></i></button>
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
</script>
<script type="text/javascript">
    function approve_mutlok($nodok){
        // alert($id);
        let nodok  = $nodok;

    $('#txt_nodok').val(nodok);
    $('#modal-appv-mutlok').modal('show');
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
