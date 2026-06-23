@extends('layouts.index')

@section('custom-link')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">

    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

    <style type="text/css">
        .form-control {
            border: 1.5px solid #ced4da;
            border-radius: 8px;
            padding: 6px 10px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.15rem rgba(13, 110, 253, 0.25);
        }

        .dataTables_length select {
            width: auto;
            min-width: 65px;
            padding-right: 24px;
        }
    </style>
@endsection

@section('content')
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-list-alt"></i> Master Rak Sparepart</h5>
        </div>
        <div class="card-body">
            <div class="row mb-3 align-items-end">
                <div class="col-md-3">
                    <label for="txtnm_rak"><small><b>Nama Rak :</b></small></label>
                    <input type="text" id="txtnm_rak" name="txtnm_rak" class="form-control form-control-sm"
                        style="text-transform: uppercase;" oninput="this.value = this.value.toUpperCase();">
                </div>
                <div class="col-md-3">
                    <label for="txtno_rak"><small><b>No Rak :</b></small></label>
                    <input type="text" id="txtno_rak" name="txtno_rak" class="form-control form-control-sm"
                        style="text-transform: uppercase;" oninput="this.value = this.value.toUpperCase();">
                </div>
                <div class="col-md-4">
                    <label for="txtdesc"><small><b>Deskripsi :</b></small></label>
                    <input type="text" id="txtdesc" name="txtdesc" class="form-control form-control-sm"
                        style="text-transform: uppercase;" oninput="this.value = this.value.toUpperCase();">
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-success btn-sm" id="saveRakButton" onclick="save_rak_sparepart();">
                        <i class="fas fa-save"></i> Save
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-hover align-middle text-nowrap w-100">
                    <thead class="bg-sb">
                        <tr>
                            <th scope="col" class="text-center align-middle">Nama Rak</th>
                            <th scope="col" class="text-center align-middle">No Rak</th>
                            <th scope="col" class="text-center align-middle">Deskripsi</th>
                            <th scope="col" class="text-center align-middle">Act</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Edit Rak Sparepart -->
    <div class="modal fade" id="EditRakModal" tabindex="-1" aria-labelledby="EditRakModalLabel" aria-hidden="true"
        data-bs-backdrop="static">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-sb text-white">
                    <h5 class="modal-title" id="EditRakModalLabel">Edit Rak Sparepart</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="txted_id_rak" name="txted_id_rak" value="">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="txted_nm_rak"><small><b>Nama Rak :</b></small></label>
                            <input type="text" id="txted_nm_rak" name="txted_nm_rak"
                                class="form-control form-control-sm" style="text-transform: uppercase;"
                                oninput="this.value = this.value.toUpperCase();">
                        </div>
                        <div class="col-md-4">
                            <label for="txted_no_rak"><small><b>No Rak :</b></small></label>
                            <input type="text" id="txted_no_rak" name="txted_no_rak"
                                class="form-control form-control-sm" style="text-transform: uppercase;"
                                oninput="this.value = this.value.toUpperCase();">
                        </div>
                        <div class="col-md-4">
                            <label for="txted_desc"><small><b>Deskripsi :</b></small></label>
                            <input type="text" id="txted_desc" name="txted_desc"
                                class="form-control form-control-sm" style="text-transform: uppercase;"
                                oninput="this.value = this.value.toUpperCase();">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-warning btn-sm" id="editRakButton"
                        onclick="update_rak_sparepart();">Edit</button>
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
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
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        // Reset input form to default state on every page load/refresh
        $('#txtnm_rak').val('');
        $('#txtno_rak').val('');
        $('#txtdesc').val('');

        function notif() {
            alert("Maaf, Fitur belum tersedia!");
        }

        function save_rak_sparepart() {
            let nm_rak = $('#txtnm_rak').val();
            let no_rak = $('#txtno_rak').val();
            let desc = $('#txtdesc').val();

            if (!nm_rak || !no_rak) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Nama Rak dan No Rak wajib diisi',
                });
                return;
            }

            let $btn = $('#saveRakButton');
            $btn.prop('disabled', true);

            $.ajax({
                type: "POST",
                url: '{{ route('store_rak_sparepart') }}',
                data: {
                    _token: '{{ csrf_token() }}',
                    nm_rak: nm_rak,
                    no_rak: no_rak,
                    desc: desc
                },
                success: function(response) {
                    $('#txtnm_rak').val('');
                    $('#txtno_rak').val('');
                    $('#txtdesc').val('');
                    dataTableReload();

                    Swal.fire({
                        icon: 'success',
                        title: 'Rak Sparepart Ditambahkan',
                        text: response.message,
                        timer: 1500,
                        showConfirmButton: false
                    });
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.message || 'Terjadi kesalahan saat menyimpan.',
                    });
                },
                complete: function() {
                    $btn.prop('disabled', false);
                }
            });
        }

        function editData(id_c) {
            jQuery.ajax({
                url: '{{ route('show_rak_sparepart') }}',
                method: 'GET',
                data: {
                    id: id_c
                },
                dataType: 'json',
                success: function(res) {
                    $('#txted_id_rak').val(res.id_rak);
                    $('#txted_nm_rak').val(res.nm_rak);
                    $('#txted_no_rak').val(res.no_rak);
                    $('#txted_desc').val(res.desc);
                },
                error: function(request, status, error) {
                    alert(request.responseText);
                },
            });
        }

        function update_rak_sparepart() {
            let id_rak = $('#txted_id_rak').val();
            let nm_rak = $('#txted_nm_rak').val();
            let no_rak = $('#txted_no_rak').val();
            let desc = $('#txted_desc').val();

            if (!nm_rak || !no_rak) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Nama Rak dan No Rak wajib diisi',
                });
                return;
            }

            let $btn = $('#editRakButton');
            $btn.prop('disabled', true);

            $.ajax({
                type: "POST",
                url: '{{ route('update_rak_sparepart') }}',
                data: {
                    _token: '{{ csrf_token() }}',
                    id_rak: id_rak,
                    nm_rak: nm_rak,
                    no_rak: no_rak,
                    desc: desc
                },
                success: function(response) {
                    $('#EditRakModal').modal('hide');
                    dataTableReload();

                    Swal.fire({
                        icon: 'success',
                        title: 'Rak Sparepart Diupdate',
                        text: response.message,
                        timer: 1500,
                        showConfirmButton: false
                    });
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.message || 'Terjadi kesalahan saat mengupdate.',
                    });
                },
                complete: function() {
                    $btn.prop('disabled', false);
                }
            });
        }

        function deleteData(id_c) {
            Swal.fire({
                icon: 'warning',
                title: 'Hapus Rak Sparepart?',
                text: 'Data yang sudah dihapus tidak dapat dikembalikan.',
                showCancelButton: true,
                confirmButtonText: 'Hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (!result.isConfirmed) {
                    return;
                }

                $.ajax({
                    type: "POST",
                    url: '{{ route('delete_rak_sparepart') }}',
                    data: {
                        _token: '{{ csrf_token() }}',
                        id: id_c
                    },
                    success: function(response) {
                        dataTableReload();
                        Swal.fire({
                            icon: 'success',
                            title: 'Rak Sparepart Dihapus',
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false
                        });
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON?.message ||
                                'Terjadi kesalahan saat menghapus.',
                        });
                    }
                });
            });
        }
    </script>
    <script>
        function dataTableReload() {
            datatable.ajax.reload();
        }

        let datatable = $("#datatable").DataTable({
            ordering: false,
            responsive: true,
            processing: true,
            serverSide: false,
            paging: true,
            searching: true,
            scrollY: true,
            scrollX: true,
            scrollCollapse: false,
            ajax: {
                url: '{{ route('asset_master_rak_sparepart') }}',
            },
            columns: [{
                    data: 'nm_rak'
                }, // Nama Rak
                {
                    data: 'no_rak'
                }, // No Rak
                {
                    data: 'desc'
                }, // Deskripsi
                {
                    data: 'id_rak',
                    render: function(data) {
                        return `
                    <div class="text-center">
                        <button class="btn btn-sm btn-warning" onclick="editData(${data})"
                                data-bs-toggle="modal" data-bs-target="#EditRakModal">
                            <i class="fa fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="deleteData(${data})">
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>`;
                    },
                    orderable: false,
                    searchable: false
                }
            ],
        });
    </script>
@endsection
