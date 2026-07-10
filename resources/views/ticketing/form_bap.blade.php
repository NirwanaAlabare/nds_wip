@extends('layouts.index')

@section('custom-link')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
@endsection

@section('content')
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-file-alt"></i> Form BAP</h5>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#ModalFormBap">
                    <i class="fas fa-plus"></i> New
                </button>
            </div>
            <div class="d-flex align-items-end gap-3 mb-4">
                <div class="mr-3">
                    <label class="form-label mb-1"><small><b>Tgl Awal</b></small></label>
                    <input type="date" class="form-control form-control-sm" id="tgl-awal" name="tgl_awal" value="{{ date('Y-m-01') }}">
                </div>
                <div class="mr-3">
                    <label class="form-label mb-1"><small><b>Tgl Akhir</b></small></label>
                    <input type="date" class="form-control form-control-sm" id="tgl-akhir" name="tgl_akhir" value="{{ date('Y-m-t') }}">
                </div>
                <div>
                    <button type="button" class="btn btn-primary btn-sm fw-bold" onclick="dataTableReload()">
                        <i class="fas fa-search"></i> Cari
                    </button>
                </div>
            </div>
            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-hover align-middle text-nowrap w-100">
                    <thead class="bg-sb">
                        <tr>
                            <th scope="col" class="text-center align-middle">No. Form</th>
                            <th scope="col" class="text-center align-middle">Tgl. Form</th>
                            <th scope="col" class="text-center align-middle">Department</th>
                            <th scope="col" class="text-center align-middle">Modul</th>
                            <th scope="col" class="text-center align-middle">No. Dokumen</th>
                            <th scope="col" class="text-center align-middle">Tgl. Masalah</th>
                            <th scope="col" class="text-center align-middle">Masalah</th>
                            <th scope="col" class="text-center align-middle">Penyebab</th>
                            <th scope="col" class="text-center align-middle">Usulan</th>
                            <th scope="col" class="text-center align-middle">Keterangan</th>
                            <th scope="col" class="text-center align-middle">Status</th>
                            <th scope="col" class="text-center align-middle">Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal New Form BAP -->
    <div class="modal fade" id="ModalFormBap" tabindex="-1" aria-labelledby="ModalFormBapLabel" aria-hidden="true"
        data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-sb text-white">
                    <h5 class="modal-title" id="ModalFormBapLabel">Form BAP Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="txtedit_id" name="edit_id" value="">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="txtno_form" class="form-label"><small><b>No. Form</b></small></label>
                            <input type="text" id="txtno_form" name="no_form" class="form-control form-control-sm"
                                placeholder="Auto Generate" readonly>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="txtdepartment" class="form-label"><small><b>Department</b></small></label>
                            <input type="text" id="txtdepartment" name="department" class="form-control form-control-sm">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="txtmodul" class="form-label"><small><b>Modul</b></small></label>
                            <input type="text" id="txtmodul" name="modul" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="txtno_dokumen" class="form-label"><small><b>No. Dokumen</b></small></label>
                            <input type="text" id="txtno_dokumen" name="no_dokumen" class="form-control form-control-sm">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="txttgl_masalah" class="form-label"><small><b>Tgl. Masalah</b></small></label>
                            <input type="date" id="txttgl_masalah" name="tgl_masalah"
                                class="form-control form-control-sm">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="txtmasalah" class="form-label"><small><b>Masalah</b></small></label>
                        <textarea id="txtmasalah" name="masalah" class="form-control form-control-sm" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="txtpenyebab" class="form-label"><small><b>Penyebab</b></small></label>
                        <textarea id="txtpenyebab" name="penyebab" class="form-control form-control-sm" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="txtusulan" class="form-label"><small><b>Usulan</b></small></label>
                        <textarea id="txtusulan" name="usulan" class="form-control form-control-sm" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="txtketerangan" class="form-label"><small><b>Keterangan</b></small></label>
                        <textarea id="txtketerangan" name="keterangan" class="form-control form-control-sm" rows="8"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary btn-sm" id="saveFormBapButton"
                        onclick="save_form_bap();">
                        <i class="fas fa-save"></i>
                        Simpan
                    </button>
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
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
    <script>
        function notif() {
            alert("Maaf, Fitur belum tersedia!");
        }

        $('#ModalFormBap').on('hidden.bs.modal', function() {
            $('#txtedit_id').val('');
            $('#txtno_form').val('');
            $('#txtdepartment').val('');
            $('#txtmodul').val('');
            $('#txtno_dokumen').val('');
            $('#txtmasalah').val('');
            $('#txttgl_masalah').val('');
            $('#txtpenyebab').val('');
            $('#txtusulan').val('');
            $('#txtketerangan').val('');
            $('#ModalFormBapLabel').text('Form BAP Baru');
        });

        function save_form_bap() {
            let department = $('#txtdepartment').val();

            if (!department) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Department wajib diisi',
                });
                return;
            }

            let editId = $('#txtedit_id').val();
            let $btn = $('#saveFormBapButton');
            $btn.prop('disabled', true);

            $.ajax({
                type: "POST",
                url: editId ? '{{ route('update-form-bap') }}' : '{{ route('store-form-bap') }}',
                data: {
                    _token: '{{ csrf_token() }}',
                    id: editId,
                    department: department,
                    modul: $('#txtmodul').val(),
                    no_dokumen: $('#txtno_dokumen').val(),
                    tgl_masalah: $('#txttgl_masalah').val(),
                    masalah: $('#txtmasalah').val(),
                    penyebab: $('#txtpenyebab').val(),
                    usulan: $('#txtusulan').val(),
                    keterangan: $('#txtketerangan').val(),
                },
                success: function(response) {
                    $('#ModalFormBap').modal('hide');
                    dataTableReload();

                    Swal.fire({
                        icon: 'success',
                        title: editId ? 'Form BAP Diupdate' : 'Form BAP Disimpan',
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

        function editFormBap(id) {
            $.ajax({
                type: "GET",
                url: '{{ route('edit-form-bap') }}',
                data: {
                    id: id
                },
                success: function(response) {
                    $('#txtedit_id').val(response.id);
                    $('#txtno_form').val(response.no_form);
                    $('#txtdepartment').val(response.department);
                    $('#txtmodul').val(response.modul);
                    $('#txtno_dokumen').val(response.no_dokumen);
                    $('#txttgl_masalah').val(response.tgl_masalah);
                    $('#txtmasalah').val(response.masalah);
                    $('#txtpenyebab').val(response.penyebab);
                    $('#txtusulan').val(response.usulan);
                    $('#txtketerangan').val(response.keterangan);
                    $('#ModalFormBapLabel').text('Edit Form BAP');
                    $('#ModalFormBap').modal('show');
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.message || 'Terjadi kesalahan saat mengambil data.',
                    });
                }
            });
        }

        function cancelFormBap(id) {
            Swal.fire({
                icon: 'warning',
                title: 'Cancel Form BAP ini?',
                text: 'Kasus ini akan ditandai sebagai Cancel.',
                showCancelButton: true,
                confirmButtonText: 'Ya, Cancel',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (!result.isConfirmed) {
                    return;
                }

                $.ajax({
                    type: "POST",
                    url: '{{ route('cancel-form-bap') }}',
                    data: {
                        _token: '{{ csrf_token() }}',
                        id: id
                    },
                    success: function(response) {
                        dataTableReload();
                        Swal.fire({
                            icon: 'success',
                            title: response.message,
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
                                'Terjadi kesalahan saat membatalkan.',
                        });
                    }
                });
            });
        }

        function toggleStatusFormBap(id, currentStatus) {
            Swal.fire({
                icon: 'question',
                title: currentStatus ? 'Tandai kasus ini belum selesai?' : 'Tandai kasus ini sudah selesai?',
                showCancelButton: true,
                confirmButtonText: 'Ya',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (!result.isConfirmed) {
                    return;
                }

                $.ajax({
                    type: "POST",
                    url: '{{ route('toggle-status-form-bap') }}',
                    data: {
                        _token: '{{ csrf_token() }}',
                        id: id,
                        current_status: currentStatus
                    },
                    success: function(response) {
                        dataTableReload();
                        Swal.fire({
                            icon: 'success',
                            title: response.message,
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
                                'Terjadi kesalahan saat mengupdate status.',
                        });
                    }
                });
            });
        }

        function dataTableReload() {
            datatable.ajax.reload();
        }

        let datatable = $("#datatable").DataTable({
            ordering: false,
            responsive: true,
            processing: true,
            serverSide: true,
            paging: true,
            searching: true,
            scrollX: true,
            ajax: {
                url: '{{ route('form-bap') }}',
                data: function(d) {
                    d.tgl_awal = $('#tgl-awal').val();
                    d.tgl_akhir = $('#tgl-akhir').val();
                }
            },
            columns: [{
                    data: 'no_form'
                },
                {
                    data: 'tgl_form'
                },
                {
                    data: 'department'
                },
                {
                    data: 'modul'
                },
                {
                    data: 'no_dokumen'
                },
                {
                    data: 'tgl_masalah'
                },
                {
                    data: 'masalah'
                },
                {
                    data: 'penyebab'
                },
                {
                    data: 'usulan'
                },
                {
                    data: 'keterangan'
                },
                {
                    data: 'status',
                    orderable: false,
                    searchable: false
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    className: 'text-center',
                    render: function(data, type, row) {
                        let btnEdit =
                            `<button type="button" class="btn btn-sm btn-info mr-1" title="Edit" onclick="editFormBap(${row.id})"><i class="fas fa-edit"></i></button>`;

                        if (row.is_cancel) {
                            return `<div class="d-flex justify-content-center">${btnEdit}</div>`;
                        }

                        let btnToggle = row.is_selesai ?
                            `<button type="button" class="btn btn-sm btn-success mr-1" title="Tandai Belum Selesai" onclick="toggleStatusFormBap(${row.id}, 1)"><i class="fas fa-check"></i></button>` :
                            `<button type="button" class="btn btn-sm btn-primary mr-1" title="Tandai Selesai" onclick="toggleStatusFormBap(${row.id}, 0)"><i class="fas fa-check"></i></button>`;
                        let btnCancel =
                            `<button type="button" class="btn btn-sm btn-danger" title="Cancel" onclick="cancelFormBap(${row.id})"><i class="fas fa-ban"></i></button>`;

                        return `<div class="d-flex justify-content-center">${btnToggle}${btnEdit}${btnCancel}</div>`;
                    }
                },
            ],
        });
    </script>
@endsection
