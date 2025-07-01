@extends('layouts.index')
<style>
    .ui-datepicker {
        z-index: 9999 !important;
        position: absolute !important;
    }

    .btn-close-white {
        filter: invert(1) grayscale(100%) brightness(200%);
    }

</style>
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
            <h5 class="card-title fw-bold mb-0">List REVERSE BPB</h5>
        </div>
        <div class="card-body">
            <div class="d-flex align-items-end gap-3 mb-3">
                <div class="col-md-12">
                    <div class="form-group row">

                        <div class="col-md-3">
                            <div class="mb-1">
                                <div class="form-group">
                                    <label>Status</label>
                                    <select class="form-control  select2supp" id="status" name="status" style="width: 100%;font-size: 14px;">
                                        <option selected="selected" value="ALL">ALL</option>
                                        @foreach ($pilihan as $pilih)
                                        <option value="{{ $pilih->nama_pilihan }}">
                                            {{ $pilih->nama_pilihan }}
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
                                    <input type="date" class="form-control form-control" id="whs_tgl_awal" name="whs_tgl_awal"
                                    value="{{ date('Y-m-d') }}" >
                                </div>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="mb-1">
                                <div class="form-group">
                                    <label class="form-label">To</label>
                                    <input type="date" class="form-control form-control" id="whs_tgl_akhir" name="whs_tgl_akhir"
                                    value="{{ date('Y-m-d') }}">
                                </div>
                            </div>
                        </div>


                        <div class="col-md-3" style="padding-top: 0.5rem;">
                            <div class="mt-4">
                                <button class="btn btn-primary " onclick="dataTableReload()"> <i class="fas fa-search"></i> Search</button>
                                <!-- <button class="btn btn-info" onclick="tambahdata()"> <i class="fas fa-plus"></i> Add Data</button> -->
                                <a href="{{ route('create-maintain-bpb') }}" class="btn btn-info">
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
                <table id="datatable" class="table table-bordered table-striped w-100 text-nowrap">
                    <thead>
                        <tr>
                            <th class="text-center">No Reverse</th>
                            <th class="text-center">Tgl Reverse</th>
                            <th class="text-center">User Create</th>
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

    <div class="modal fade" id="modal-cancel-transfer">
        <form action="{{ route('cancel-maintain') }}" method="post" onsubmit="submitForm(this, event)">
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
                        <label for="id_inv" class="col-sm-12 col-form-label" >Sure Cancel Maintain Number :</label>
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

<div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header bg-sb">
            <h5 class="modal-title" id="detailModalLabel">Detail BPB</h5>
            <button type="button" class="close btn-close-whte" data-bs-dismiss="modal" aria-label="Close">
                <span aria-hidden="true" class="text-white">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <!-- Konten detail akan diisi via JS -->
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
        $(document).ready(function () {
            $('#detailTable').DataTable({
                paging: true,
                searching: true,
                ordering: true,
                scrollX: true,
                autoWidth: false,
                responsive: true
            });
        });
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
                url: '{{ route('maintain-bpb') }}',
                dataType: 'json',
                dataSrc: 'data',
                data: function(d) {
                    d.tgl_awal = $('#whs_tgl_awal').val();
                    d.tgl_akhir = $('#whs_tgl_akhir').val();
                    d.status = $('#status').val();
                },
            },
            columns: [{
                data: 'no_maintain'
            },
            {
                data: 'tgl_maintain'
            },
            {
                data: 'create_user'
            },
            {
                data: 'status'
            },
            {
                data: 'id'
            }

            ],
            columnDefs: [
        // {
        //         targets: [7],
        //         className: "d-none",
        //         render: (data, type, row, meta) => {
        //                 return `<span hidden>` + data + `</span>`;
        //         }
        //     },
        {
            targets: [4],
            render: (data, type, row, meta) => {
                console.log(row);
                if (row.status != 'CANCEL') {
                    return `<div class='d-flex gap-1 justify-content-center'>
                    <button type='button' class='btn btn-sm btn-info' onclick='showDetail("` + data + `")'>
                    <i class="fa-solid fa-eye"></i>
                    </button>
                    <a href="http://10.10.5.60/ap/module/AP/pdf_maintain_bpb.php?doc_number=`+data+`" target="_blank"><button type='button' class='btn btn-sm btn-success'><i class="fa-solid fa-print "></i></button></a>
                    <button type='button' class='btn btn-sm btn-danger' href='javascript:void(0)' onclick='cancel_maintain("` + row.no_maintain + `")'><i class="fa-solid fa-trash"></i></button>
                    </div>`;
                }else{
                    return `<div class='d-flex gap-1 justify-content-center'> <button type='button' class='btn btn-sm btn-info' onclick='showDetail("` + data + `")'>
                    <i class="fa-solid fa-eye"></i>
                    </button>
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
        function showDetail(id) {
        // Ambil data detail via AJAX
        $.ajax({
            url: '{{ route("maintain-bpb-detail") }}', // ganti dengan route detail yang kamu punya
            method: 'GET',
            data: { id: id },
            success: function(response) {
                // Misal tampilkan ke dalam modal
                $('#detailModal .modal-body').html(response.html); // pastikan response sudah berupa HTML
                $('#detailModal').modal('show');
            }
        });
    }
</script>

<script type="text/javascript">
    function cancel_maintain($nodok){
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
