@extends('layouts.index')

<style type="text/css">
    #datatable {
        table-layout: auto !important;
    }

    table.dataTable {
        width: 100% !important;
        table-layout: fixed; /* force equal layout across all columns */
    }

    table.dataTable td {
        max-width: 150px;
        overflow-wrap: break-word;
        word-wrap: break-word;
        white-space: normal;
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
            <h5 class="card-title fw-bold mb-0">Update Data Ceisa</h5>
        </div>
        <div class="card-body">
            <div class="d-flex align-items-end gap-3 mb-3">
                <div class="col-md-12">
                    <div class="form-group row">
                        <div class="col-md-3">
                            <div class="mb-1">
                                <div class="form-group">
                                    <label>Jenis Dokumen</label>
                                    <select class="form-control select2supp" id="jenis_dok" name="jenis_dok" style="width: 100%;">
                                        <option selected="selected" value="ALL">ALL</option>
                                        @foreach ($jenisdok as $jdok)
                                        <option value="{{ $jdok->nama_pilihan }}">
                                            {{ $jdok->nama_pilihan }}
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

                        <div class="col-md-3" style="display: none;">
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
                                <a href="{{ route('create-update-ceisa') }}" class="btn btn-success">
                                    <i class="fas fa-plus"></i>
                                    Add Data
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-striped text-nowrap" style="width: 100%;">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 12%;">Tgl Update</th>
                            <th class="text-center" style="width: 12%;">Jenis Dokumen</th>
                            <th class="text-center" style="width: 12%;">No Aju</th>
                            <th class="text-center" style="width: 10%;">Tgl Aju</th>
                            <th class="text-center" style="width: 12%;">No Daftar</th>
                            <th class="text-center" style="width: 10%;">Tgl Daftar</th>
                            <th class="text-center" style="width: 20%;">Keterangan</th>
                            <th class="text-center" style="width: 12%;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-cancel-data">
        <form action="{{ route('cancel-keterangan-ceisa') }}" method="post" onsubmit="submitForm(this, event)">
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
                        <label for="id_inv" class="col-sm-12 col-form-label" >Yakin Cancel Data Berikut?</label>
                        <br>
                        <div class="col-sm-3">
                        </div>
                        <label for="id_inv" class="col-sm-12 col-form-label" >Jenis Dokumen: </label>
                        <br>
                        <div class="col-sm-3">
                        </div>
                        <div class="col-sm-8">
                            <input type="hidden" class="form-control" id="txt_nodok" name="txt_nodok" style="border:none;text-align: left;" readonly>
                            <input type="text" class="form-control" id="txt_jenis_dokumen" name="txt_jenis_dokumen" style="border:none;text-align: left;" readonly>
                        </div>
                        <label for="id_inv" class="col-sm-12 col-form-label" >No Aju: </label>
                        <br>
                        <div class="col-sm-3">
                        </div>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="txt_no_aju" name="txt_no_aju" style="border:none;text-align: left;" readonly>
                        </div>
                        <label for="id_inv" class="col-sm-12 col-form-label" >No Daftar: </label>
                        <br>
                        <div class="col-sm-3">
                        </div>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="txt_no_daftar" name="txt_no_daftar" style="border:none;text-align: left;" readonly>
                        </div>
                        <label for="id_inv" class="col-sm-12 col-form-label" >Keterangan: </label>
                        <br>
                        <div class="col-sm-3">
                        </div>
                        <div class="col-sm-8">
                            <textarea class="form-control" id="txt_keterangan" name="txt_no_keterangan"
                            rows="5" readonly style="border:none;text-align:left;"></textarea>
                        </div>
                    </div>
                    <!-- Hidden Text -->
                    <!--  -->
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fa fa-window-close" aria-hidden="true"></i> Close</button>
                    <button type="submit" class="btn btn-danger toastsDefaultDanger"><i class="fa-solid fa-trash" aria-hidden="true"></i> Cancel</button>
                </div>
            </div>
        </div>
    </form>
</div>


<div class="modal fade" id="modal-edit-data">
        <form action="{{ route('edit-keterangan-ceisa') }}" method="post" onsubmit="submitForm(this, event)">
         @method('GET')
         <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-sb text-light">
                    <h4 class="modal-title">Edit Keterangan Ceisa</h4>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!--  -->
                    <div class="form-group row">
                        <label for="id_inv" class="col-sm-12 col-form-label" >Jenis Dokumen: </label>
                        <br>
                        <div class="col-sm-3">
                        </div>
                        <div class="col-sm-8">
                            <input type="hidden" class="form-control" id="edit_nodok" name="edit_nodok" style="border:none;text-align: left;" readonly>
                            <input type="text" class="form-control" id="edit_jenis_dokumen" name="edit_jenis_dokumen" style="border:none;text-align: left;" readonly>
                        </div>
                        <label for="id_inv" class="col-sm-12 col-form-label" >No Aju: </label>
                        <br>
                        <div class="col-sm-3">
                        </div>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="edit_no_aju" name="edit_no_aju" style="border:none;text-align: left;" readonly>
                        </div>
                        <label for="id_inv" class="col-sm-12 col-form-label" >No Daftar: </label>
                        <br>
                        <div class="col-sm-3">
                        </div>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="edit_no_daftar" name="edit_no_daftar" style="border:none;text-align: left;" readonly>
                        </div>
                        <label for="id_inv" class="col-sm-12 col-form-label" >Keterangan: </label>
                        <br>
                        <div class="col-sm-3">
                        </div>
                        <div class="col-sm-8">
                            <textarea class="form-control" id="edit_keterangan" name="edit_keterangan"
                            rows="5" style="border:none;text-align:left;"></textarea>
                        </div>
                    </div>
                    <!-- Hidden Text -->
                    <!--  -->
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fa fa-window-close" aria-hidden="true"></i> Close</button>
                    <button type="submit" class="btn btn-sb toastsDefaultDanger"><i class="fa-solid fa-floppy-disk" aria-hidden="true"></i> Save</button>
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
            autoWidth: false,
            scrollX: false,
            autoWidth: false,
            ajax: {
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route('update-data-ceisa') }}',
                dataType: 'json',
                dataSrc: 'data',
                data: function(d) {
                    d.tgl_awal = $('#tgl_awal').val();
                    d.tgl_akhir = $('#tgl_akhir').val();
                    d.jenis_dok = $('#jenis_dok').val();
                },
            },
            columns: [{
                data: 'tgl_update'
            },
            {
                data: 'jenis_dok'
            },
            {
                data: 'no_aju'
            },
            {
                data: 'tgl_aju'
            },
            {
                data: 'no_daftar'
            },
            {
                data: 'tgl_daftar'
            },
            {
                data: 'keterangan'
            },
            {
                data: 'id'
            }

            ],
            columnDefs: [{
        targets: [0,3,5], // kolom tgl_update
        render: function(data, type, row) {
            if (!data) return '';
            const date = new Date(data);
            return new Intl.DateTimeFormat('id-ID', {
                day: '2-digit',
                month: 'short',
                year: 'numeric'
            }).format(date); // hasil: 25 Jun 2025
        }
    },
    {
        targets: [7],
        render: (data, type, row, meta) => {
            console.log(row);
            if (row.status != 'CANCEL') {
                return `<div class='d-flex gap-1 justify-content-center'>
                <button type='button' class='btn btn-sm btn-danger' href='javascript:void(0)' onclick='cancel_data("` + row.id + `", "` + row.no_aju + `", "` + row.no_daftar + `", "` + row.keterangan + `", "` + row.jenis_dok + `")'><i class="fa-solid fa-trash"></i></button>
                <button type='button' class='btn btn-sm btn-primary' href='javascript:void(0)' onclick='edit_data("` + row.id + `", "` + row.no_aju + `", "` + row.no_daftar + `", "` + row.keterangan + `", "` + row.jenis_dok + `")'><i class="fa-solid fa-pen-to-square" aria-hidden="true"></i></button>
                </div>`;
            }else{
                return `<div class='d-flex gap-1 justify-content-center'> <b><i>Cancelled</i></b>
                </div>`;
            }
        }
    },
    { width: '12%', targets: 0 },
    { width: '12%', targets: 1 },
    { width: '12%', targets: 2 },
    { width: '10%', targets: 3 },
    { width: '12%', targets: 4 },
    { width: '10%', targets: 5 },
    { width: '20%', targets: 6 },
    { width: '12%', targets: 7 },

    ]
});

        function dataTableReload() {
            datatable.ajax.reload();
        }
    </script>
    <script type="text/javascript">
        function cancel_data($id,$no_aju,$no_daftar,$keterangan,$jenis_dok){
        // alert($id);
        let id  = $id;
        let no_aju  = $no_aju;
        let no_daftar  = $no_daftar;
        let keterangan  = $keterangan;
        let jenis_dok  = $jenis_dok;

        $('#txt_nodok').val(id);
        $('#txt_no_aju').val(no_aju);
        $('#txt_no_daftar').val(no_daftar);
        $('#txt_keterangan').val(keterangan);
        $('#txt_jenis_dokumen').val(jenis_dok);
        $('#modal-cancel-data').modal('show');
    }

    function edit_data($id,$no_aju,$no_daftar,$keterangan,$jenis_dok){
        // alert($id);
        let id  = $id;
        let no_aju  = $no_aju;
        let no_daftar  = $no_daftar;
        let keterangan  = $keterangan;
        let jenis_dok  = $jenis_dok;

        $('#edit_nodok').val(id);
        $('#edit_no_aju').val(no_aju);
        $('#edit_no_daftar').val(no_daftar);
        $('#edit_keterangan').val(keterangan);
        $('#edit_jenis_dokumen').val(jenis_dok);
        $('#modal-edit-data').modal('show');
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
