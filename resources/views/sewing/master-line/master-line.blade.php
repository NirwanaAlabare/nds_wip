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
            <h5 class="card-title"><i class="fa-solid fa-people-group"></i> Master Line</h5>
        </div>
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-end mb-3">
                <div class="d-flex align-items-end gap-3">
                    <div>
                        <label class="form-label">Dari</label>
                        <input type="date" class="form-control" name="from" id="from" value="{{ date('Y-m-d') }}">
                    </div>
                    <span class="mb-2">
                        -
                    </span>
                    <div>
                        <label class="form-label">Sampai</label>
                        <input type="date" class="form-control" name="to" id="to" value="{{ date('Y-m-d') }}">
                    </div>
                </div>
                <div>
                    <button class="btn btn-success fw-bold" data-bs-toggle="modal" data-bs-target="#storeMasterLineModal"><i class="fa fa-plus"></i> NEW</a>
                </div>
            </div>
            <table id="datatable" class="table table-bordered">
                <thead>
                    <tr>
                        <th>Action</th>
                        <th>Tanggal</th>
                        <th>Line</th>
                        <th>Leader</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create Modal -->
    <form method="post" action="{{ route("store-master-line") }}" id="store-master-line" onsubmit="submitForm(this, event)">
        <div class="modal fade" id="storeMasterLineModal" tabindex="-1" aria-labelledby="storeMasterLineModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-sb">
                        <h1 class="modal-title fs-5" id="storeMasterLineModalLabel"><i class="fa fa-plus"></i> New Master Line</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3 mb-3">
                            <div class="col-md-12">
                                <label class="form-label">Tanggal</label>
                                <input type="date" class="form-control" name="tanggal" id="tanggal" value="{{ date("Y-m-d") }}">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Line</label>
                                <select class="form-select select2bs4-create-modal" name="line_id" id="line_id">
                                    <option value="">Pilih Line</option>
                                    @foreach ($lines as $line)
                                        <option value="{{ $line->line_id }}">{{ strtoupper(str_replace("_", " ", $line->username)) }}</option>
                                    @endforeach
                                </select>
                                <input type="hidden" class="form-control" name="line_name" id="line_name" readonly>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Leader</label>
                                <select class="form-select select2bs4-create-modal" name="employee_id" id="employee_id">
                                    <option value="">Pilih Leader</option>
                                    @foreach ($employees as $employee)
                                        <option value="{{ $employee->enroll_id }}" data-nik="{{ $employee->nik }}" data-name="{{ $employee->employee_name }}">{{ $employee->nik." - ".$employee->employee_name }}</option>
                                    @endforeach
                                </select>
                                <input type="hidden" class="form-control" name="employee_nik" id="employee_nik" readonly>
                                <input type="hidden" class="form-control" name="employee_name" id="employee_name" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="fa fa-times"></i> BATAL</button>
                        <button type="submit" class="btn btn-success"><i class="fa fa-check"></i> SIMPAN</button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Edit Modal -->
    <form method="post" action="{{ route("update-master-line") }}" id="update-master-line" onsubmit="submitForm(this, event)">
        @method("PUT")
        <div class="modal fade" id="updateMasterLineModal" tabindex="-1" aria-labelledby="updateMasterLineModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-sb">
                        <h1 class="modal-title fs-5" id="updateMasterLineModalLabel"><i class="fa fa-edit"></i> Edit Master Line</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3 mb-3">
                            <div class="col-md-12 d-none">
                                <label class="form-label">ID</label>
                                <input type="tex" class="form-control" name="edit_id" id="edit_id" value="" readonly>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Tanggal</label>
                                <input type="date" class="form-control" name="edit_tanggal" id="edit_tanggal">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Line</label>
                                <select class="form-select select2bs4-edit-modal" name="edit_line_id" id="edit_line_id">
                                    <option value="">Pilih Line</option>
                                    @foreach ($lines as $line)
                                        <option value="{{ $line->line_id }}">{{ strtoupper(str_replace("_", " ", $line->username)) }}</option>
                                    @endforeach
                                </select>
                                <input type="hidden" class="form-control" name="edit_line_name" id="edit_line_name" readonly>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Leader</label>
                                <select class="form-select select2bs4-edit-modal" name="edit_employee_id" id="edit_employee_id">
                                    <option value="">Pilih Leader</option>
                                    @foreach ($employees as $employee)
                                        <option value="{{ $employee->enroll_id }}" data-nik="{{ $employee->nik }}" data-name="{{ $employee->employee_name }}">{{ $employee->nik." - ".$employee->employee_name }}</option>
                                    @endforeach
                                </select>
                                <input type="hidden" class="form-control" name="edit_employee_nik" id="edit_employee_nik" readonly>
                                <input type="hidden" class="form-control" name="edit_employee_name" id="edit_employee_name" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="fa fa-times"></i> BATAL</button>
                        <button type="submit" class="btn btn-primary"><i class="fa fa-edit"></i> UBAH</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@section('custom-script')
    <!-- DataTables & Plugins -->
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-rowsgroup/dataTables.rowsGroup.js') }}"></script>

    <!-- Select2 -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        $('.select2bs4').select2({
            theme: 'bootstrap4'
        })
        $('.select2bs4-create-modal').select2({
            theme: 'bootstrap4',
            dropdownParent: $('#storeMasterLineModal')
        })
        $('.select2bs4-edit-modal').select2({
            theme: 'bootstrap4',
            dropdownParent: $('#updateMasterLineModal')
        })
    </script>

    {{-- General --}}
    <script>
        let datatable = $("#datatable").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            paging: false,
            ajax: {
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route('master-line') }}',
                dataType: 'json',
                dataSrc: 'data',
                data: function(d) {
                    d.from = $('#from').val();
                    d.to = $('#to').val();
                },
            },
            columns: [
                {
                    data: 'id'
                },
                {
                    data: 'tanggal'
                },
                {
                    data: 'line_name'
                },
                {
                    data: 'employee_name'
                },
            ],
            columnDefs: [
                {
                    targets: [0],
                    className: 'align-middle',
                    render: (data, type, row, meta) => {
                        return `
                            <div class='d-flex gap-1 justify-content-center'>
                                <a class='btn btn-primary btn-sm' data-bs-toggle="modal" data-bs-target="#updateMasterLineModal" onclick='editData(` + JSON.stringify(row) + `, "updateMasterLineModal");'>
                                    <i class='fa fa-edit'></i>
                                </a>
                                <a class='btn btn-danger btn-sm' data='`+JSON.stringify(row)+`' data-url='{{ route('destroy-master-line') }}/`+row['id']+`' onclick='deleteData(this)'>
                                    <i class='fa fa-trash'></i>
                                </a>
                            </div>
                        `;
                    }
                },
            ]
        });

        function datatableReload() {
            datatable.ajax.reload();
        }

        // CREATE SELECT2
        $("#line_id").on("change", function () {
            $("#line_name").val($("#line_id").find(":selected").text());
        })

        $("#employee_id").on("change", function () {
            $("#employee_nik").val($("#employee_id").find(":selected").attr("data-nik"));
            $("#employee_name").val($("#employee_id").find(":selected").attr("data-name"));
        })

        // EDIT SELECT2
        $("#edit_line_id").on("change", function () {
            $("#edit_line_name").val($("#edit_line_id").find(":selected").text());
        })

        $("#edit_employee_id").on("change", function () {
            $("#edit_employee_nik").val($("#edit_employee_id").find(":selected").attr("data-nik"));
            $("#edit_employee_name").val($("#edit_employee_id").find(":selected").attr("data-name"));
        })
    </script>
@endsection
