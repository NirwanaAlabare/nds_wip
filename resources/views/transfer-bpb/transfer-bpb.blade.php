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
        <h5 class="card-title fw-bold mb-0">Data Transfer BPB</h5>
    </div>
    <div class="card-body">
        <div class="d-flex align-items-end gap-3 mb-3">
                <div class="col-md-12">
            <div class="form-group row">

            <div class="col-md-4">
            <div class="mb-1">
                <div class="form-group">
                <label>Supplier</label>
                <select class="form-control  select2bs4" id="nama_supp" name="nama_supp" style="width: 100%;font-size: 14px;">
                    <option selected="selected" value="ALL">ALL</option>
                        @foreach ($supp as $sup)
                    <option value="{{ $sup->supplier }}">
                                {{ $sup->supplier }}
                    </option>
                        @endforeach
                </select>
                </div>
            </div>
            </div>

            <div class="col-md-2">
            <div class="mb-1">
                <div class="form-group">
                <label class="form-label">From</label>
                    <input type="date" class="form-control form-control" id="tgl_awal" name="tgl_awal"
                        value="{{ date('Y-m-d') }}" >
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
                <a href="{{ route('create-transfer-bpb') }}" class="btn btn-info">
                <i class="fas fa-plus"></i>
                Add Data
            </a>
            </div>
        </div>
        </div>
    </div>
</div>
<!--         <div class="d-flex justify-content-between">
            <div class="ml-auto">
                <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
            </div>
                <input type="text"  id="cari_grdok" name="cari_grdok" autocomplete="off" placeholder="Search Data..." onkeyup="carigrdok()">
        </div> -->
        <div class="table-responsive">
            <table id="datatable" class="table table-bordered table-striped 100 text-nowrap">
                <thead>
                    <tr>
                        <th class="text-center">No Transfer</th>
                        <th class="text-center">Tgl Transfer</th>
                        <th class="text-center">Supplier</th>
                        <th class="text-center">Total Amount</th>
                        <th class="text-center">User Create</th>
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

<div class="modal fade" id="modal-cancel-transfer">
    <form action="{{ route('cancel-transfer') }}" method="post" onsubmit="submitForm(this, event)">
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
                        <label for="id_inv" class="col-sm-12 col-form-label" >Sure Cancel Transfer Number :</label>
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
                    <button type="submit" class="btn btn-danger toastsDefaultDanger"><i class="fa fa-trash" aria-hidden="true"></i> Cancel</button>
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
$(document).on('select2:open', () => {
    document.querySelector('.select2-search__field').focus();
});

$('.select2bs4').select2({
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
        ordering: true,
        processing: true,
        serverSide: true,
        paging: true,
        searching: true,
        ajax: {
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '{{ route('transfer-bpb') }}',
            dataType: 'json',
            dataSrc: 'data',
            data: function(d) {
                d.tgl_awal = $('#tgl_awal').val();
                d.tgl_akhir = $('#tgl_akhir').val();
                d.nama_supp = $('#nama_supp').val();
            },
        },
        columns: [{
                data: 'no_transfer'
            },
            {
                data: 'tgl_transfer'
            },
            {
                data: 'nama_supp'
            },
            {
                data: 'total'
            },
            {
                data: 'create_user'
            },
            {
                data: 'status'
            },
            {
                data: 'no_transfer'
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
                targets: [2],
                className: "d-none",
                render: (data, type, row, meta) => {
                        return `<span hidden>` + data + `</span>`;
                }
            },
            {
                targets: [3],
                className: "d-none",
                render: (data, type, row, meta) => {
                        return `<span hidden>` + data + `</span>`;
                }
            },
            {
                targets: [6],
                render: (data, type, row, meta) => {
                    console.log(row);
                    if (row.status != 'Cancel') {
                        return `<div class='d-flex gap-1 justify-content-center'><a href="http://10.10.5.60/ap/module/AP/pdf_transf_bpb.php?doc_number=`+data+`" target="_blank"><button type='button' class='btn btn-sm btn-success'><i class="fa-solid fa-print "></i></button></a>
                            <button type='button' class='btn btn-sm btn-danger' href='javascript:void(0)' onclick='cancel_transfer("` + data + `")'><i class="fa-solid fa-trash"></i></button>
                    </div>`;
                    }else{
                        return `<div class='d-flex gap-1 justify-content-center'> <a href="http://10.10.5.60/ap/module/AP/pdf_transf_bpb.php?doc_number=`+data+`" target="_blank"><button type='button' class='btn btn-sm btn-success'><i class="fa-solid fa-print "></i></button></a>
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
    function cancel_transfer($nodok){
        // alert($id);
        let nodok  = $nodok;

    $('#txt_nodok').val(nodok);
    $('#modal-cancel-transfer').modal('show');
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
            td = tr[i].getElementsByTagName("td")[7]; //kolom ke berapa
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
