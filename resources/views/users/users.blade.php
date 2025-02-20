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
                <i class="fa fa-users"></i> Manage User
            </h5>
        </div>
        <div class="card-body">
            <div>
                <button class="btn btn-sm btn-success mb-3" data-bs-toggle="modal" data-bs-target="#createUserModal"><i class="fa fa-plus"></i> Baru</button>
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
                    <table class="table table-bordered" id="manage-user-table">
                        <thead>
                            <th>Action</th>
                            <th>Name</th>
                            <th>Username</th>
                            <th>Roles</th>
                            <th>Accesses</th>
                            <th>Updated_at</th>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Create User Type --}}
    <div class="modal fade" id="createUserModal" tabindex="-1" aria-labelledby="createUserLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-sb text-light">
                    <h1 class="modal-title fs-5" id="createUserLabel"><i class="fa fa-plus"></i> Tambah User</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('store-user') }}" method="post" onsubmit="submitForm(this, event)">
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" class="form-control" name="name" id="name" value="">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control" name="username" id="username" value="">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" class="form-control" name="password" id="password" value="">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Type</label>
                            <select class="form-select select2bs4-create" name="type" id="type">
                                <option value=""></option>
                                <option value="superadmin">{{ strtoupper("superadmin") }}</option>
                                <option value="admin">{{ strtoupper("admin") }}</option>
                                <option value="marker">{{ strtoupper("marker") }}</option>
                                <option value="spreading">{{ strtoupper("spreading") }}</option>
                                <option value="meja">{{ strtoupper("meja") }}</option>
                                <option value="dc">{{ strtoupper("dc") }}</option>
                                <option value="sewing">{{ strtoupper("sewing") }}</option>
                                <option value="packing">{{ strtoupper("packing") }}</option>
                                <option value="warehouse">{{ strtoupper("warehouse") }}</option>
                                <option value="Qcpass">{{ strtoupper("Qcpass") }}</option>
                                <option value="ga">{{ strtoupper("ga") }}</option>
                                <option value="hr">{{ strtoupper("hr") }}</option>
                                <option value="mechanic">{{ strtoupper("mechanic") }}</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Roles</label>
                            <select class="form-select select2bs4-create" name="roles[]" id="roles" multiple="multiple">
                                <option value=""></option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->id }}">{{ strtoupper($role->nama_role) }}</option>
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

    {{-- Edit User Modal --}}
    <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-sb text-light">
                    <h1 class="modal-title fs-5" id="editUserLabel"><i class="fa fa-edit"></i> Edit User</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route("update-user-detail") }}" method="post" onsubmit="submitForm(this, event)">
                        @method('PUT')
                        <input type="hidden" name="edit_id" id="edit_id">
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" class="form-control" name="edit_name" id="edit_name" value="">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control" name="edit_username" id="edit_username" value="">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" class="form-control" name="edit_password" id="edit_password" value="">
                            <div class="form-text" id="basic-addon4">*Kosongkan jika tidak akan diubah</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Type</label>
                            <select class="form-select select2bs4-edit" name="edit_type" id="edit_type">
                                <option value="superadmin">{{ strtoupper("superadmin") }}</option>
                                <option value="admin">{{ strtoupper("admin") }}</option>
                                <option value="marker">{{ strtoupper("marker") }}</option>
                                <option value="spreading">{{ strtoupper("spreading") }}</option>
                                <option value="meja">{{ strtoupper("meja") }}</option>
                                <option value="dc">{{ strtoupper("dc") }}</option>
                                <option value="sewing">{{ strtoupper("sewing") }}</option>
                                <option value="packing">{{ strtoupper("packing") }}</option>
                                <option value="warehouse">{{ strtoupper("warehouse") }}</option>
                                <option value="Qcpass">{{ strtoupper("Qcpass") }}</option>
                                <option value="ga">{{ strtoupper("ga") }}</option>
                                <option value="hr">{{ strtoupper("hr") }}</option>
                                <option value="mechanic">{{ strtoupper("mechanic") }}</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Add Roles</label>
                            <select class="form-select select2bs4-edit" name="edit_roles[]" id="edit_roles" multiple="multiple">
                                <option value=""></option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->id }}">{{ strtoupper($role->nama_role) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Roles</label>
                            <div class="table-responsive">
                                <table class="table table-bordered w-100" id="user-role-table">
                                    <thead>
                                        <tr>
                                            <th>Action</th>
                                            <th>Role</th>
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
            dropdownParent: $("#createUserModal")
        })

        $('.select2bs4-edit').select2({
            theme: 'bootstrap4',
            dropdownParent: $("#editUserModal")
        })
    </script>

    <script>
        $('#manage-user-table').DataTable({
            ajax: {
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route('manage-user') }}',
                dataType: 'json',
                dataSrc: 'data'
            },
            columns: [
                {
                    data: 'id',
                },
                {
                    data: 'name'
                },
                {
                    data: 'username'
                },
                {
                    data: 'roles'
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
                        let userId = '{{ Auth::user()->id }}';
                        let buttonEdit = "<button type='button' class='btn btn-primary btn-sm' ><i class='fa fa-edit'></i></button>";
                        let buttonDelete = "<button type='button' class='btn btn-danger btn-sm'><i class='fa fa-trash'></i></button>";
                        let disabled = userId == data ? true : false;

                        return `
                            <div class='d-flex gap-1 justify-content-center'>
                                <a class='btn btn-primary btn-sm' data-bs-toggle="modal" data-bs-target="#editUserModal" onclick='editData(` + JSON.stringify(row) + `, "editUserModal", [{"function" : "dataTableUserReload(); dataTableUserRoleReload();"}]);' `+(disabled ? "disabled" : "")+`>
                                    <i class='fa fa-edit'></i>
                                </a>
                                <a class='btn btn-danger btn-sm' data='`+JSON.stringify(row)+`' data-url='{{ route('destroy-user') }}/`+data+`' onclick='deleteData(this)' `+(disabled ? "disabled" : "")+`>
                                    <i class='fa fa-trash'></i>
                                </a>
                            </div>
                        `;
                    }
                },
                // Updated At
                {
                    targets: [5],
                    render: (data, type, row, meta) => {
                        return formatDateTime(data);
                    }
                },
            ]
        });

        function dataTableUserReload() {
            $('#manage-user-table').DataTable().ajax.reload();
        }

        // Edit Role
        $('#user-role-table').DataTable({
            searching: false,
            paging: false,
            ordering: false,
            processing: true,
            serverside: true,
            ajax: {
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route('get-user-role') }}',
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
                    data: 'role'
                },
                {
                    data: 'accesses'
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
                                <a class='btn btn-danger btn-sm' data='`+JSON.stringify(row)+`' data-url='{{ route('destroy-user-role') }}/`+data+`' onclick='deleteData(this)'>
                                    <i class='fa fa-trash'></i>
                                </a>
                            </div>
                        `;
                    }
                },
                {
                    targets: [1,2],
                    render: (data, type, row, meta) => {
                        return data.toUpperCase();
                    }
                }
            ]
        });

        function dataTableUserRoleReload() {
            $('#user-role-table').DataTable().ajax.reload();
        }
    </script>
@endsection
