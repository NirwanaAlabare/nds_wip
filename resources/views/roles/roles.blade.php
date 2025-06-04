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
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title">
                <i class="fa fa-user-gear"></i> Manage Role
            </h5>
        </div>
        <div class="card-body">
            <div>
                <button class="btn btn-sm btn-success mb-3" data-bs-toggle="modal" data-bs-target="#createRoleModal"><i class="fa fa-plus"></i> Baru</button>
                <div class="d-flex gap-3 mb-3">
                    <div class="mb-3">
                        <label><small>Tanggal Awal</small></label>
                        <input type="date" class="form-control form-control-sm" id="dateFrom" name="dateFrom" value="{{ date("Y-m-d") }}">
                    </div>
                    <div class="mb-3">
                        <label><small>Tanggal Akhir</small></label>
                        <input type="date" class="form-control form-control-sm" id="dateTo" name="dateTo" value="{{ date("Y-m-d") }}">
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered" id="manage-role-table">
                        <thead>
                            <th>Action</th>
                            <th>Role Name</th>
                            <th>Accesses</th>
                            <th>Timestamp</th>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Create Role --}}
    <div class="modal fade" id="createRoleModal" tabindex="-1" aria-labelledby="createRoleLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-sb text-light">
                    <h1 class="modal-title fs-5" id="createRoleLabel"><i class="fa fa-plus"></i> Tambah Role</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('store-role') }}" method="post" onsubmit="submitForm(this, event)">
                        <div class="mb-3">
                            <label class="form-label">Role Name</label>
                            <input type="text" class="form-control" name="nama_role" id="nama_role" value="">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Accesses</label>
                            <select class="form-select select2bs4-create" name="roles[]" id="roles" multiple="multiple">
                                <option value=""></option>
                                @foreach ($accesses as $access)
                                    <option value="{{ $access->id }}">{{ strtoupper($access->access) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="d-flex justify-content-end gap-3">
                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="fa fa-times"></i> Tutup</button>
                            <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Edit Role Modal --}}
    <div class="modal fade" id="editRoleModal" tabindex="-1" aria-labelledby="editRoleLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-sb text-light">
                    <h1 class="modal-title fs-5" id="editRoleLabel"><i class="fa fa-edit"></i> Edit Role</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route("update-role") }}" method="post" onsubmit="submitForm(this, event)">
                        @method('PUT')
                        <input type="hidden" name="edit_id" id="edit_id">
                        <div class="mb-3">
                            <label class="form-label">Role Name</label>
                            <input type="text" class="form-control" name="edit_nama_role" id="edit_nama_role" value="">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Add Accesses</label>
                            <select class="form-select select2bs4-edit" name="edit_acceses[]" id="edit_acceses" multiple="multiple">
                                <option value=""></option>
                                @foreach ($accesses as $access)
                                    <option value="{{ $access->id }}">{{ strtoupper($access->access) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Accesses</label>
                            <div class="table-responsive">
                                <table class="table table-bordered w-100" id="role-access-table">
                                    <thead>
                                        <tr>
                                            <th>Action</th>
                                            <th>Access</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="3">No Data</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-3 mb-3">
                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="fa fa-times"></i> Batal</button>
                            <button type="submit" class="btn btn-success fw-bold"><i class="fa fa-save"></i> Simpan</button>
                        </div>
                    </form>
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
        $('.select2').select2()

        $('.select2bs4-create').select2({
            theme: 'bootstrap4',
            dropdownParent: $("#createRoleModal")
        })

        $('.select2bs4-edit').select2({
            theme: 'bootstrap4',
            dropdownParent: $("#editRoleModal")
        })
    </script>

    <script>
        $('#manage-role-table').DataTable({
            ajax: {
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route('manage-role') }}',
                dataType: 'json',
                dataSrc: 'data'
            },
            columns: [
                {
                    data: 'id',
                },
                {
                    data: 'nama_role'
                },
                {
                    data: 'accesses',
                },
                {
                    data: 'updated_at'
                }
            ],
            columnDefs: [
                {
                    targets: "_all",
                    className: "text-nowrap"
                },
                // Act Column
                {
                    targets: [0],
                    render: (data, type, row, meta) => {
                        let userId = '{{ Auth::user()->roles->implode("role") }}';
                        let buttonEdit = "<button type='button' class='btn btn-primary btn-sm' ><i class='fa fa-edit'></i></button>";
                        let buttonDelete = "<button type='button' class='btn btn-danger btn-sm'><i class='fa fa-trash'></i></button>";
                        let disabled = userId == data ? true : false;

                        return `
                            <div class='d-flex gap-1 justify-content-center'>
                                <a class='btn btn-primary btn-sm' data-bs-toggle="modal" data-bs-target="#editRoleModal" onclick='editData(` + JSON.stringify(row) + `, "editRoleModal", [{"function" : "dataTableRoleReload(); dataTableRoleAccessReload();"}]);' `+(disabled ? "disabled" : "")+`>
                                    <i class='fa fa-edit'></i>
                                </a>
                                <a class='btn btn-danger btn-sm' data='`+JSON.stringify(row)+`' data-url='{{ route('destroy-role') }}/`+data+`' onclick='deleteData(this)' `+(disabled ? "disabled" : "")+`>
                                    <i class='fa fa-trash'></i>
                                </a>
                            </div>
                        `;
                    }
                },
                // Updated At
                {
                    targets: [3],
                    render: (data, type, row, meta) => {
                        return formatDateTime(data);
                    }
                },
            ]
        });

        function dataTableRoleReload() {
            $('#manage-role-table').DataTable().ajax.reload();
        }

        // Edit Role
        $('#role-access-table').DataTable({
            searching: false,
            paging: false,
            ordering: false,
            processing: true,
            serverside: true,
            ajax: {
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route('get-role-access') }}',
                dataType: 'json',
                dataSrc: 'data',
                data: function (d) {
                    d.id = $("#edit_id").val();
                }
            },
            columns: [
                {
                    data: 'id',
                },
                {
                    data: 'access'
                },
            ],
            columnDefs: [
                {
                    targets: "_all",
                    className: "text-nowrap"
                },
                // Act Column
                {
                    targets: [0],
                    render: (data, type, row, meta) => {
                        let buttonDelete = "<button type='button' class='btn btn-danger btn-sm'><i class='fa fa-trash'></i></button>";

                        return `
                            <div class='d-flex gap-1 justify-content-center'>
                                <a class='btn btn-danger btn-sm' data='`+JSON.stringify(row)+`' data-url='{{ route('destroy-role-access') }}/`+data+`' onclick='deleteData(this)'>
                                    <i class='fa fa-trash'></i>
                                </a>
                            </div>
                        `;
                    }
                },
                {
                    targets: [1],
                    render: (data, type, row, meta) => {
                        return data.toUpperCase();
                    }
                }
            ]
        });

        function dataTableRoleAccessReload() {
            $('#role-access-table').DataTable().ajax.reload();
        }
    </script>
@endsection
