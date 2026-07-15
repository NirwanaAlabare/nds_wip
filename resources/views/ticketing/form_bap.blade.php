@extends('layouts.index')

@section('custom-link')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
    <style>
        #datatable td.text-wrap {
            max-width: 180px;
        }

        #datatable td.text-wrap span {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            white-space: normal;
            word-break: break-word;
        }
    </style>
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
            <div class="d-flex align-items-end justify-content-between flex-wrap gap-3 mb-4">
                <div class="d-flex align-items-end gap-3">
                    <div class="mr-3">
                        <label class="form-label mb-1"><small><b>Tgl Awal</b></small></label>
                        <input type="date" class="form-control form-control-sm" id="tgl-awal" name="tgl_awal"
                            value="{{ date('Y-m-01') }}">
                    </div>
                    <div class="mr-3">
                        <label class="form-label mb-1"><small><b>Tgl Akhir</b></small></label>
                        <input type="date" class="form-control form-control-sm" id="tgl-akhir" name="tgl_akhir"
                            value="{{ date('Y-m-t') }}">
                    </div>
                    <div class="mr-3">
                        <label class="form-label mb-1"><small><b>Status</b></small></label>
                        <select class="form-control form-control-sm select2bs4" id="filter-status" name="status" style="width: 160px;">
                            <option value="">Semua</option>
                            <option value="proses">Proses</option>
                            <option value="selesai">Selesai</option>
                            <option value="cancel">Cancel</option>
                        </select>
                    </div>
                    <div>
                        <button type="button" class="btn btn-primary btn-sm fw-bold" onclick="dataTableReload()">
                            <i class="fas fa-search"></i> Cari
                        </button>
                    </div>
                </div>
                <div class="d-flex gap-4" id="bap-summary">
                    <div class="text-center">
                        <div class="fw-bold" style="font-size: 22px;" id="summary-total">0</div>
                        <small class="text-muted">Total Form</small>
                    </div>
                    <div class="text-center">
                        <div class="fw-bold text-secondary" style="font-size: 22px;" id="summary-proses">0</div>
                        <small class="text-muted">Proses</small>
                    </div>
                    <div class="text-center">
                        <div class="fw-bold text-success" style="font-size: 22px;" id="summary-selesai">0</div>
                        <small class="text-muted">Selesai</small>
                    </div>
                    <div class="text-center">
                        <div class="fw-bold text-danger" style="font-size: 22px;" id="summary-cancel">0</div>
                        <small class="text-muted">Cancel</small>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-hover align-middle w-100">
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
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
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
                            <select id="txtdepartment" name="department" class="form-control form-control-sm select2bs4" style="width: 100%;">
                                <option value="">Pilih Department</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="txtmodul" class="form-label"><small><b>Modul</b></small></label>
                            <input type="text" id="txtmodul" name="modul" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="txtno_dokumen" class="form-label"><small><b>No. Dokumen</b></small></label>
                            <input type="text" id="txtno_dokumen" name="no_dokumen"
                                class="form-control form-control-sm">
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
    <!-- Select2 -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        function notif() {
            alert("Maaf, Fitur belum tersedia!");
        }

        $(function() {
            loadSummary();

            $('#filter-status').select2({
                theme: 'bootstrap4',
                width: 'resolve'
            });

            $('#txtdepartment').select2({
                theme: 'bootstrap4',
                width: 'resolve',
                dropdownParent: $('#ModalFormBap')
            });

            $('.select2-container--bootstrap4 .select2-selection--single').css({
                'height': '31px',
                'font-size': '12px',
                'line-height': '30px'
            });

            $.ajax({
                type: "GET",
                url: '{{ route('departments-form-bap') }}',
                success: function(response) {
                    let $select = $('#txtdepartment');
                    response.forEach(function(item) {
                        $select.append(
                            $('<option>').val(item.DEPARTEMEN).text(item.DEPARTEMEN)
                        );
                    });
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                }
            });
        });

        $('#ModalFormBap').on('hidden.bs.modal', function() {
            $('#txtedit_id').val('');
            $('#txtno_form').val('');
            $('#txtdepartment').val('').trigger('change');
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
                    $('#txtdepartment').val(response.department).trigger('change');
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

        function printFormBap(id) {
            window.open('{{ url('/dashboard_bap/form/print') }}/' + id, '_blank');
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
            loadSummary();
        }

        function loadSummary() {
            $.ajax({
                type: "GET",
                url: '{{ route('summary-form-bap') }}',
                data: {
                    tgl_awal: $('#tgl-awal').val(),
                    tgl_akhir: $('#tgl-akhir').val()
                },
                success: function(response) {
                    $('#summary-total').text(response.total);
                    $('#summary-proses').text(response.proses);
                    $('#summary-selesai').text(response.selesai);
                    $('#summary-cancel').text(response.cancel);
                }
            });
        }

        let datatable = $("#datatable").DataTable({
            ordering: false,
            responsive: false,
            processing: true,
            serverSide: true,
            paging: true,
            searching: true,
            scrollX: false,
            autoWidth: false,
            ajax: {
                url: '{{ route('form-bap') }}',
                data: function(d) {
                    d.tgl_awal = $('#tgl-awal').val();
                    d.tgl_akhir = $('#tgl-akhir').val();
                    d.status = $('#filter-status').val();
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
                    data: 'masalah',
                    className: 'text-wrap',
                    render: function(data, type, row) {
                        if (type !== 'display' || !data) {
                            return data;
                        }
                        let escaped = $('<div>').text(data).html();
                        return `<span title="${escaped}">${escaped}</span>`;
                    }
                },
                {
                    data: 'penyebab',
                    className: 'text-wrap',
                    render: function(data, type, row) {
                        if (type !== 'display' || !data) {
                            return data;
                        }
                        let escaped = $('<div>').text(data).html();
                        return `<span title="${escaped}">${escaped}</span>`;
                    }
                },
                {
                    data: 'usulan',
                    className: 'text-wrap',
                    render: function(data, type, row) {
                        if (type !== 'display' || !data) {
                            return data;
                        }
                        let escaped = $('<div>').text(data).html();
                        return `<span title="${escaped}">${escaped}</span>`;
                    }
                },
                {
                    data: 'keterangan',
                    className: 'text-wrap',
                    render: function(data, type, row) {
                        if (type !== 'display' || !data) {
                            return data;
                        }
                        let escaped = $('<div>').text(data).html();
                        return `<span title="${escaped}">${escaped}</span>`;
                    }
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
                        let btnPrint =
                            `<button type="button" class="btn btn-sm btn-secondary mr-1" title="Print PDF" onclick="printFormBap(${row.id})"><i class="fas fa-print"></i></button>`;

                        if (row.is_cancel) {
                            return `<div class="d-flex justify-content-center">${btnEdit}${btnPrint}</div>`;
                        }

                        let canMarkSelesai = @json(in_array(auth()->user()->username, ['admin_01', 'nirwana_it']));
                        let btnToggle = '';
                        if (canMarkSelesai) {
                            btnToggle = row.is_selesai ?
                                `<button type="button" class="btn btn-sm btn-success mr-1" title="Tandai Belum Selesai" onclick="toggleStatusFormBap(${row.id}, 1)"><i class="fas fa-check"></i></button>` :
                                `<button type="button" class="btn btn-sm btn-primary mr-1" title="Tandai Selesai" onclick="toggleStatusFormBap(${row.id}, 0)"><i class="fas fa-check"></i></button>`;
                        }

                        if (row.is_selesai) {
                            return `<div class="d-flex justify-content-center">${btnToggle}${btnPrint}</div>`;
                        }

                        let btnCancel =
                            `<button type="button" class="btn btn-sm btn-danger" title="Cancel" onclick="cancelFormBap(${row.id})"><i class="fas fa-ban"></i></button>`;

                        return `<div class="d-flex justify-content-center">${btnToggle}${btnEdit}${btnPrint}${btnCancel}</div>`;
                    }
                },
            ],
        });
    </script>
@endsection
