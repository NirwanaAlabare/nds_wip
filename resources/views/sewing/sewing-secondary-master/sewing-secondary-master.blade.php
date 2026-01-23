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
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title fw-bold mb-0"><i class="fa-solid fa-diagram-project"></i> Sewing Secondary Master</h5>
            </div>
        </div>
        <div class="card-body">
            @role('superadmin')
                <button type="button" class="btn btn-success btn-sm mb-3" data-bs-toggle="modal" data-bs-target="#createSecondaryMasterModal">
                    <i class="fas fa-plus"></i>
                    Create Secondary Master
                </button>
            @endrole
            <div class="table-responsive">
                <table id="datatable-secondary-master" class="table table-bordered table w-100">
                    <thead>
                        <tr>
                            <th>Act</th>
                            <th>Secondary</th>
                            <th>Created By</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Create Secondary Master --}}
    <div class="modal fade" id="createSecondaryMasterModal" tabindex="-1" aria-labelledby="createSecondaryMasterLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('store-sewing-secondary-master') }}" method="post" onsubmit="submitForm(this, event)">
                    <div class="modal-header bg-sb text-light">
                        <h1 class="modal-title fs-5" id="createSecondaryMasterLabel"><i class="fa fa-plus-square"></i> Create Secondary Master Data</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Secondary</label>
                            <input type="text" class="form-control" name="secondary" id="secondary" value="">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="fa fa-times"></i> Tutup</button>
                        <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Edit Secondary Master --}}
    <div class="modal fade" id="editSecondaryMasterModal" tabindex="-1" aria-labelledby="editSecondaryMasterLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('update-sewing-secondary-master') }}" method="post" onsubmit="submitForm(this, event)">
                    @method('PUT')
                    <div class="modal-header bg-sb text-light">
                        <h1 class="modal-title fs-5" id="editSecondaryMasterLabel"><i class="fa fa-edit"></i> Edit Secondary Master</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="edit_id" id="edit_id">
                        <div class="mb-3">
                            <label class="form-label">Secondary</label>
                            <input type="text" class="form-control" name="edit_secondary" id="edit_secondary" value="">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="fa fa-times"></i> Tutup</button>
                        <button type="submit" class="btn btn-success fw-bold"><i class="fa fa-save"></i> Simpan</button>
                    </div>
                </form>
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
    <script src="{{ asset('plugins/datatables-rowsgroup/dataTables.rowsGroup.js') }}"></script>

    <!-- Select2 -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        $('.select2').select2({
            theme: 'bootstrap4'
        });
        $('.select2bs4').select2({
            theme: 'bootstrap4'
        });
        $('.select2bs4merge').select2({
            theme: 'bootstrap4',
            dropdownParent: $('#mergeDefectTypeModal')
        });
    </script>

    <script>
        let datatableSecondaryMaster = $("#datatable-secondary-master").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            paging: 10,
            ajax: {
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route('sewing-secondary-master') }}',
                dataType: 'json',
                dataSrc: 'data',
                data: {
                    type:"type"
                },
            },
            columns: [
                {
                    data: 'id'
                },
                {
                    data: 'secondary'
                },
                {
                    data: 'created_by_username'
                },
                {
                    data: 'updated_at'
                },
            ],
            columnDefs: [
                {
                    targets: [0],
                    className: 'align-middle',
                    render: (data, type, row, meta) => {
                        return `
                            <div class='d-flex gap-1 justify-content-center'>
                                <a class='btn btn-primary btn-sm' data-bs-toggle="modal" data-bs-target="#editSecondaryMasterModal" onclick='editData(` + JSON.stringify(row) + `, "editSecondaryMasterModal", [{"function" : "dataTableSecondaryMasterReload()"}]);'>
                                    <i class='fa fa-edit'></i>
                                </a>
                                <a class='btn btn-danger btn-sm' data='`+JSON.stringify(row)+`' data-url='{{ route('destroy-sewing-secondary-master') }}/`+row['id']+`' onclick='deleteData(this)'>
                                    <i class='fa fa-trash'></i>
                                </a>
                            </div>
                        `;
                    },
                },
                {
                    targets: [3],
                    render: (data, type, row, meta) => {
                        return formatDateTime(data);
                    }
                }
            ],
        });

        function dataTableSecondaryMasterReload() {
            datatableSecondaryMaster.ajax.reload();
        }
    </script>
@endsection
