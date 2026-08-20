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
            max-width: 260px;
            min-width: 140px;
        }

        #datatable td.text-wrap span {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            white-space: normal;
            word-break: break-word;
        }

        #datatable td,
        #datatable th {
            font-size: 12px;
            vertical-align: middle;
        }

        #datatable .btn-sm {
            padding: 2px 7px;
        }

        .select2-container--bootstrap4 .select2-selection--single {
            height: 31px;
            font-size: 12px;
            line-height: 30px;
        }

        .summary-box {
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 8px 6px;
            text-align: center;
            background: #fff;
            height: 100%;
        }

        .summary-value {
            font-size: 22px;
            font-weight: 700;
            line-height: 1.1;
        }

        @media (max-width: 767.98px) {
            .summary-value {
                font-size: 18px;
            }

            #datatable td.text-wrap {
                max-width: none;
                min-width: 0;
            }

            #datatable td.text-wrap span {
                -webkit-line-clamp: 4;
            }

            table.dtr-inline.collapsed>tbody>tr>td.dtr-control {
                padding-left: 26px;
            }

            #datatable .dtr-details {
                display: block;
                width: 100%;
            }

            #datatable .dtr-details li {
                display: flex;
                gap: 6px;
                padding: 3px 0 !important;
                border-bottom: 1px solid #f1f3f5;
            }

            #datatable .dtr-details .dtr-title {
                min-width: 92px;
                font-weight: 600;
            }

            #datatable .dtr-details .dtr-data {
                flex: 1;
                word-break: break-word;
            }

            .dataTables_wrapper .dataTables_filter,
            .dataTables_wrapper .dataTables_length {
                text-align: left;
                float: none;
            }

            .dataTables_wrapper .dataTables_filter input {
                width: 100%;
            }
        }
    </style>
@endsection

@section('content')
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fa-solid fa-snowflake"></i> Form Maintenance AC</h5>
        </div>
        <div class="card-body">
            <div class="row g-2 align-items-end mb-3">
                <div class="col-6 col-md-3 col-lg-2">
                    <label class="form-label mb-1"><small><b>Tgl Awal</b></small></label>
                    <input type="date" class="form-control form-control-sm" id="tgl-awal" name="tgl_awal"
                        value="{{ date('Y-m-01') }}">
                </div>
                <div class="col-6 col-md-3 col-lg-2">
                    <label class="form-label mb-1"><small><b>Tgl Akhir</b></small></label>
                    <input type="date" class="form-control form-control-sm" id="tgl-akhir" name="tgl_akhir"
                        value="{{ date('Y-m-t') }}">
                </div>
                <div class="col-8 col-md-3 col-lg-2">
                    <label class="form-label mb-1"><small><b>Status</b></small></label>
                    <select class="form-control form-control-sm select2bs4" id="filter-status" name="status"
                        style="width: 100%;">
                        <option value="">Semua</option>
                        <option value="DRAFT">Draft</option>
                        <option value="ON PROGRESS">On Progress</option>
                        <option value="DONE">Done</option>
                        <option value="CANCEL">Cancel</option>
                    </select>
                </div>
                <div class="col-4 col-md-3 col-lg-2">
                    <button type="button" class="btn btn-outline-primary btn-sm fw-bold w-100"
                        onclick="dataTableReload()">
                        <i class="fas fa-search"></i> Cari
                    </button>
                </div>
                <div class="col-12 col-md-auto ms-md-auto">
                    <button type="button" class="btn btn-primary btn-sm fw-bold w-100" data-bs-toggle="modal"
                        data-bs-target="#ModalFormMaintenanceAc">
                        <i class="fas fa-plus-circle"></i> Buat Form
                    </button>
                </div>
            </div>

            <div class="row row-cols-2 row-cols-md-5 g-2 mb-3" id="maintenance-ac-summary">
                <div class="col">
                    <div class="summary-box">
                        <div class="summary-value" id="summary-total">0</div>
                        <small class="text-muted">Total Form</small>
                    </div>
                </div>
                <div class="col">
                    <div class="summary-box">
                        <div class="summary-value text-secondary" id="summary-draft">0</div>
                        <small class="text-muted">Draft</small>
                    </div>
                </div>
                <div class="col">
                    <div class="summary-box">
                        <div class="summary-value text-warning" id="summary-on-progress">0</div>
                        <small class="text-muted">On Progress</small>
                    </div>
                </div>
                <div class="col">
                    <div class="summary-box">
                        <div class="summary-value text-success" id="summary-done">0</div>
                        <small class="text-muted">Done</small>
                    </div>
                </div>
                <div class="col">
                    <div class="summary-box">
                        <div class="summary-value text-danger" id="summary-cancel">0</div>
                        <small class="text-muted">Cancel</small>
                    </div>
                </div>
            </div>

            <div>
                <table id="datatable" class="table table-bordered table-hover align-middle w-100">
                    <thead class="bg-sb">
                        <tr>
                            <th scope="col" class="text-center align-middle">No. Form</th>
                            <th scope="col" class="text-center align-middle">Department</th>
                            <th scope="col" class="text-center align-middle">Tgl. Form</th>
                            <th scope="col" class="text-center align-middle">Keterangan</th>
                            <th scope="col" class="text-center align-middle">Usulan</th>
                            <th scope="col" class="text-center align-middle">Penyelesaian</th>
                            <th scope="col" class="text-center align-middle">Durasi</th>
                            <th scope="col" class="text-center align-middle">Status</th>
                            <th scope="col" class="text-center align-middle">Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal New Form Maintenance AC -->
    <div class="modal fade" id="ModalFormMaintenanceAc" tabindex="-1" aria-labelledby="ModalFormMaintenanceAcLabel"
        aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-sb text-white">
                    <h5 class="modal-title" id="ModalFormMaintenanceAcLabel">Form Maintenance AC Baru</h5>
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
                            <select id="txtdepartment" name="sub_dept_id" class="form-control form-control-sm select2bs4"
                                style="width: 100%;">
                                <option value="">Pilih Department</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="txttgl_form" class="form-label"><small><b>Tgl. Form</b></small></label>
                            <input type="date" id="txttgl_form" name="tgl_form" class="form-control form-control-sm"
                                value="{{ date('Y-m-d') }}" min="{{ date('Y-m-d') }}">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="txtketerangan" class="form-label"><small><b>Keterangan</b></small></label>
                        <textarea id="txtketerangan" name="keterangan" class="form-control form-control-sm" rows="6"></textarea>
                    </div>
                    <div class="mb-3 d-none" id="wrapUsulan">
                        <label for="txtusulan" class="form-label"><small><b>Usulan</b></small></label>
                        <textarea id="txtusulan" name="usulan" class="form-control form-control-sm" rows="6"></textarea>
                    </div>
                    <div class="mb-3 d-none" id="wrapPenyelesaian">
                        <label for="txtpenyelesaian" class="form-label"><small><b>Penyelesaian</b></small></label>
                        <textarea id="txtpenyelesaian" name="penyelesaian" class="form-control form-control-sm" rows="6"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary btn-sm" id="saveFormMaintenanceAcButton"
                        onclick="save_form_maintenance_ac();">
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
        $(function() {
            loadSummary();

            $('#filter-status').select2({
                theme: 'bootstrap4',
                width: 'resolve'
            });

            $('#txtdepartment').select2({
                theme: 'bootstrap4',
                width: 'resolve',
                dropdownParent: $('#ModalFormMaintenanceAc')
            });

            $('.select2-container--bootstrap4 .select2-selection--single').css({
                'height': '31px',
                'font-size': '12px',
                'line-height': '30px'
            });

            $.ajax({
                type: "GET",
                url: '{{ route('my-departments-form-bap') }}',
                success: function(response) {
                    let $select = $('#txtdepartment');
                    response.forEach(function(item) {
                        $select.append(
                            $('<option>').val(item.sub_dept_id).text(item.sub_dept_name)
                        );
                    });
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                }
            });
        });

        const isPrivilegedUser = @json(in_array(auth()->user()->username, ['admin_01', 'nirwana_it']));
        const isTeknisiUser = {{ in_array(auth()->user()->username, ['admin_01', 'nirwana_it', 'roy']) ? 'true' : 'false' }};

        function setDepartmentEditable(editable) {
            $('#txtdepartment').prop('disabled', !editable).trigger('change.select2');
        }

        function setFormMode(mode) {
            let isProgressMode = mode === 'ON PROGRESS';
            let isDoneMode = mode === 'DONE';
            let isDraftMode = !isProgressMode && !isDoneMode;

            setDepartmentEditable(isDraftMode && isPrivilegedUser);
            $('#txttgl_form').prop('readonly', !isDraftMode);
            $('#txtketerangan').prop('readonly', !isDraftMode);

            $('#wrapUsulan').toggleClass('d-none', isDraftMode);
            $('#txtusulan').prop('readonly', !isProgressMode);

            $('#wrapPenyelesaian').toggleClass('d-none', !isDoneMode);
            $('#txtpenyelesaian').prop('readonly', !isDoneMode);
        }

        $('#ModalFormMaintenanceAc').on('hidden.bs.modal', function() {
            $('#txtedit_id').val('');
            $('#txtno_form').val('');
            $('#txtdepartment').val('').trigger('change');
            $('#txttgl_form').val('{{ date('Y-m-d') }}');
            $('#txtketerangan').val('');
            $('#txtusulan').val('');
            $('#txtpenyelesaian').val('');
            setFormMode('DRAFT');
            setDepartmentEditable(true);
            $('#ModalFormMaintenanceAcLabel').text('Form Maintenance AC Baru');
        });

        function save_form_maintenance_ac() {
            let subDeptId = $('#txtdepartment').val();
            let tglForm = $('#txttgl_form').val();

            if (!subDeptId) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Department wajib diisi',
                });
                return;
            }

            if (!$('#txttgl_form').prop('readonly')) {
                if (!tglForm) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Tgl. Form wajib diisi',
                    });
                    return;
                }

                if (tglForm < '{{ date('Y-m-d') }}') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Tgl. Form tidak boleh mundur dari hari ini',
                    });
                    return;
                }
            }

            let editId = $('#txtedit_id').val();
            let $btn = $('#saveFormMaintenanceAcButton');
            $btn.prop('disabled', true);

            $.ajax({
                type: "POST",
                url: editId ? '{{ route('update-form-maintenance-ac') }}' :
                    '{{ route('store-form-maintenance-ac') }}',
                data: {
                    _token: '{{ csrf_token() }}',
                    id: editId,
                    sub_dept_id: subDeptId,
                    tgl_form: tglForm,
                    keterangan: $('#txtketerangan').val(),
                    usulan: $('#txtusulan').val(),
                    penyelesaian: $('#txtpenyelesaian').val(),
                },
                success: function(response) {
                    $('#ModalFormMaintenanceAc').modal('hide');
                    dataTableReload();

                    Swal.fire({
                        icon: 'success',
                        title: editId ? 'Form Maintenance AC Diupdate' : 'Form Maintenance AC Disimpan',
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

        function editFormMaintenanceAc(id) {
            $.ajax({
                type: "GET",
                url: '{{ route('edit-form-maintenance-ac') }}',
                data: {
                    id: id
                },
                success: function(response) {
                    $('#txtedit_id').val(response.id);
                    $('#txtno_form').val(response.no_form);
                    $('#txtdepartment').val(response.sub_dept_id).trigger('change');
                    $('#txttgl_form').val(response.tgl_form);
                    $('#txtketerangan').val(response.keterangan);
                    $('#txtusulan').val(response.usulan);
                    $('#txtpenyelesaian').val(response.penyelesaian);
                    setFormMode(response.status);

                    let title = 'Ubah Form Maintenance AC';
                    if (response.status === 'ON PROGRESS') {
                        title = 'Isi Usulan - ' + response.no_form;
                    } else if (response.status === 'DONE') {
                        title = 'Isi Penyelesaian - ' + response.no_form;
                    }
                    $('#ModalFormMaintenanceAcLabel').text(title);
                    $('#ModalFormMaintenanceAc').modal('show');
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

        function startProgressFormMaintenanceAc(id) {
            Swal.fire({
                icon: 'question',
                title: 'Mulai Pengerjaan',
                text: 'Form ini akan diubah menjadi ON PROGRESS.',
                showCancelButton: true,
                confirmButtonText: 'Mulai Pengerjaan',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (!result.isConfirmed) {
                    return;
                }

                $.ajax({
                    type: "POST",
                    url: '{{ route('start-progress-form-maintenance-ac') }}',
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
                                'Terjadi kesalahan saat memulai pengerjaan.',
                        });
                    }
                });
            });
        }

        function finishProgressFormMaintenanceAc(id) {
            Swal.fire({
                icon: 'question',
                title: 'Pekerjaan Selesai',
                text: 'Form ini akan diubah menjadi DONE.',
                showCancelButton: true,
                confirmButtonText: 'Pekerjaan Selesai',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (!result.isConfirmed) {
                    return;
                }

                $.ajax({
                    type: "POST",
                    url: '{{ route('finish-progress-form-maintenance-ac') }}',
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
                                'Terjadi kesalahan saat menyelesaikan pengerjaan.',
                        });
                    }
                });
            });
        }

        function cancelFormMaintenanceAc(id) {
            Swal.fire({
                icon: 'warning',
                title: 'Cancel Form Maintenance AC ini?',
                text: 'Form ini akan ditandai sebagai Cancel.',
                showCancelButton: true,
                confirmButtonText: 'Ya, Cancel',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (!result.isConfirmed) {
                    return;
                }

                $.ajax({
                    type: "POST",
                    url: '{{ route('cancel-form-maintenance-ac') }}',
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

        function restoreCancelFormMaintenanceAc(id) {
            Swal.fire({
                icon: 'question',
                title: 'Kembalikan status Cancel?',
                text: 'Form ini akan dikembalikan menjadi Draft.',
                showCancelButton: true,
                confirmButtonText: 'Ya, Kembalikan',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (!result.isConfirmed) {
                    return;
                }

                $.ajax({
                    type: "POST",
                    url: '{{ route('restore-cancel-form-maintenance-ac') }}',
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
                                'Terjadi kesalahan saat mengembalikan status.',
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
                url: '{{ route('summary-form-maintenance-ac') }}',
                data: {
                    tgl_awal: $('#tgl-awal').val(),
                    tgl_akhir: $('#tgl-akhir').val()
                },
                success: function(response) {
                    $('#summary-total').text(response.total);
                    $('#summary-draft').text(response.draft);
                    $('#summary-on-progress').text(response.on_progress);
                    $('#summary-done').text(response.done);
                    $('#summary-cancel').text(response.cancel);
                }
            });
        }

        function escHtml(v) {
            if (!v) return '';
            let decoded = $('<textarea>').html(v).text();
            return $('<div>').text(decoded).html();
        }

        let datatable = $("#datatable").DataTable({
            ordering: false,
            responsive: true,
            processing: true,
            serverSide: false,
            paging: true,
            pageLength: 25,
            searching: true,
            autoWidth: false,
            language: {
                emptyTable: 'Belum ada form maintenance AC',
                zeroRecords: 'Data tidak ditemukan',
                search: 'Cari:',
                lengthMenu: 'Tampil _MENU_ data',
                info: 'Menampilkan _START_-_END_ dari _TOTAL_ data',
                infoEmpty: 'Tidak ada data',
                paginate: {
                    previous: '&laquo;',
                    next: '&raquo;'
                }
            },
            ajax: {
                url: '{{ route('dashboard-mt-ac') }}',
                data: function(d) {
                    d.tgl_awal = $('#tgl-awal').val();
                    d.tgl_akhir = $('#tgl-akhir').val();
                    d.status = $('#filter-status').val();
                }
            },
            columns: [{
                    data: 'no_form',
                    responsivePriority: 1,
                    render: function(data, type, row) {
                        if (type !== 'display') {
                            return [data, row.created_by].filter(Boolean).join(' ');
                        }
                        let creator = row.created_by ?
                            `<br><small class="text-muted"><i class="fas fa-user fa-xs"></i> ${escHtml(row.created_by)}</small>` :
                            '';
                        return `<div class="fw-bold">${escHtml(data) || '-'}</div>` + creator;
                    }
                },
                {
                    data: 'department',
                    responsivePriority: 3
                },
                {
                    data: 'tgl_form',
                    className: 'text-center text-nowrap'
                },
                {
                    data: 'keterangan',
                    className: 'text-wrap',
                    responsivePriority: 5,
                    render: function(data, type, row) {
                        if (type !== 'display' || !data) {
                            return data;
                        }
                        let escaped = escHtml(data);
                        return `<span title="${escaped}">${escaped}</span>`;
                    }
                },
                {
                    data: 'usulan',
                    className: 'text-wrap',
                    responsivePriority: 6,
                    render: function(data, type, row) {
                        if (type !== 'display' || !data) {
                            return data;
                        }
                        let escaped = escHtml(data);
                        return `<span title="${escaped}">${escaped}</span>`;
                    }
                },
                {
                    data: 'penyelesaian',
                    className: 'text-wrap',
                    responsivePriority: 7,
                    render: function(data, type, row) {
                        if (type !== 'display' || !data) {
                            return data;
                        }
                        let escaped = escHtml(data);
                        return `<span title="${escaped}">${escaped}</span>`;
                    }
                },
                {
                    data: 'durasi',
                    className: 'text-center text-nowrap',
                    responsivePriority: 4
                },
                {
                    data: 'status_badge',
                    className: 'text-center',
                    responsivePriority: 2,
                    orderable: false,
                    searchable: false
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    className: 'text-center text-nowrap',
                    responsivePriority: 1,
                    render: function(data, type, row) {
                        let currentUserName = @json(auth()->user()->name);
                        let isOwner = row.created_by === currentUserName;

                        let btnEdit =
                            `<button type="button" class="btn btn-sm btn-info mr-1" title="Ubah" onclick="editFormMaintenanceAc(${row.id})"><i class="fas fa-edit"></i></button>`;
                        let btnCancel =
                            `<button type="button" class="btn btn-sm btn-danger" title="Cancel" onclick="cancelFormMaintenanceAc(${row.id})"><i class="fas fa-ban"></i></button>`;
                        let btnRestore =
                            `<button type="button" class="btn btn-sm btn-warning mr-1" title="Tidak Jadi Cancel" onclick="restoreCancelFormMaintenanceAc(${row.id})"><i class="fas fa-undo"></i></button>`;
                        let btnStart =
                            `<button type="button" class="btn btn-sm btn-success mr-1" title="Mulai Pengerjaan" onclick="startProgressFormMaintenanceAc(${row.id})"><i class="fas fa-play"></i></button>`;
                        let btnFinish =
                            `<button type="button" class="btn btn-sm btn-success mr-1" title="Pekerjaan Selesai" onclick="finishProgressFormMaintenanceAc(${row.id})"><i class="fas fa-check"></i></button>`;

                        let buttons = [];

                        if (row.status === 'CANCEL') {
                            if (isPrivilegedUser || isOwner) {
                                buttons.push(btnRestore);
                            }
                        } else if (row.status === 'ON PROGRESS') {
                            if (isTeknisiUser) {
                                buttons.push(btnFinish);
                                buttons.push(btnEdit);
                            }
                        } else if (row.status === 'DONE') {
                            if (isTeknisiUser) {
                                buttons.push(btnEdit);
                            }
                        } else if (row.status === 'DRAFT') {
                            if (isTeknisiUser) {
                                buttons.push(btnStart);
                            }
                            if (isPrivilegedUser || isOwner) {
                                buttons.push(btnEdit);
                                buttons.push(btnCancel);
                            }
                        }

                        if (!buttons.length) {
                            return '-';
                        }

                        return `<div class="d-flex justify-content-center">${buttons.join('')}</div>`;
                    }
                },
            ],
        });
    </script>
@endsection
