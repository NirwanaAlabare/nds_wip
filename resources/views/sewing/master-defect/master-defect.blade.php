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
                <h5 class="card-title fw-bold mb-0"><i class="fa-solid fa-circle-exclamation"></i> Master Defect</h5>
                <button type="button" class="btn btn-sb-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#mergeDefectTypeModal">
                    <i class="fas fa-copy"></i>
                    Merge Defect Type
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12 col-lg-6">
                    <button type="button" class="btn btn-success btn-sm mb-3" data-bs-toggle="modal" data-bs-target="#createDefectTypeModal">
                        <i class="fas fa-plus"></i>
                        Defect Type Baru
                    </button>
                    <div class="table-responsive">
                        <table id="datatable-defect-type" class="table table-bordered table-sm w-100">
                            <thead>
                                <tr>
                                    <th>Act</th>
                                    <th>Defect Type</th>
                                    <th>Alokasi</th>
                                    <th>Updated At</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-12 col-lg-6">
                    <button type="button" class="btn btn-success btn-sm mb-3" data-bs-toggle="modal" data-bs-target="#createDefectAreaModal">
                        <i class="fas fa-plus"></i>
                        Defect Area Baru
                    </button>
                    <div class="table-responsive">
                        <table id="datatable-defect-area" class="table table-bordered table-sm w-100">
                            <thead>
                                <tr>
                                    <th>Act</th>
                                    <th>Defect Area</th>
                                    <th>Updated At</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Create Defect Type --}}
    <div class="modal fade" id="createDefectTypeModal" tabindex="-1" aria-labelledby="createDefectTypeLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('store-defect-type') }}" method="post" onsubmit="submitForm(this, event)">
                    <div class="modal-header bg-sb text-light">
                        <h1 class="modal-title fs-5" id="createDefectTypeLabel"><i class="fa fa-plus-square"></i> Tambah Data Defect Type</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">DefectType</label>
                            <input type="text" class="form-control" name="defect_type" id="defect_type" value="">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Alokasi</label>
                            <select class="form-select" name="allocation" id="allocation">
                                <option value="SEWING">SEWING</option>
                                <option value="MENDING">MENDING</option>
                                <option value="SPOTCLEANING">SPOTCLEANING</option>
                            </select>
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

    {{-- Create Defect Area --}}
    <div class="modal fade" id="createDefectAreaModal" tabindex="-1" aria-labelledby="createDefectAreaLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('store-defect-area') }}" method="post" onsubmit="submitForm(this, event)">
                    <div class="modal-header bg-sb text-light">
                        <h1 class="modal-title fs-5" id="createDefectAreaLabel"><i class="fa fa-plus-square"></i> Tambah Data Defect Area</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Defect Area</label>
                            <input type="text" class="form-control" name="defect_area" id="defect_area" value="">
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

    {{-- Defect Type Modal --}}
    <div class="modal fade" id="editDefectTypeModal" tabindex="-1" aria-labelledby="editDefectTypeLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('update-defect-type') }}" method="post" onsubmit="submitForm(this, event)">
                    @method('PUT')
                    <div class="modal-header bg-sb text-light">
                        <h1 class="modal-title fs-5" id="editDefectTypeLabel"><i class="fa fa-edit"></i> Edit Defect Type</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="edit_id" id="edit_id">
                        <div class="mb-3">
                            <label class="form-label">Defect Type</label>
                            <input type="text" class="form-control" name="edit_defect_type" id="edit_defect_type" value="">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Alokasi</label>
                            <select class="form-select" name="edit_allocation" id="edit_allocation">
                                <option value="SEWING">SEWING</option>
                                <option value="MENDING">MENDING</option>
                                <option value="SPOTCLEANING">SPOTCLEANING</option>
                            </select>
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

    {{-- Defect Area Modal --}}
    <div class="modal fade" id="editDefectAreaModal" tabindex="-1" aria-labelledby="editDefectAreaLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('update-defect-area') }}" method="post" onsubmit="submitForm(this, event)">
                    @method('PUT')
                    <div class="modal-header bg-sb text-light">
                        <h1 class="modal-title fs-5" id="editDefectAreaLabel"><i class="fa fa-edit"></i> Edit Defect Area</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="edit_id" id="edit_id">
                        <div class="mb-3">
                            <label class="form-label">Defect Area</label>
                            <input type="text" class="form-control" name="edit_defect_area" id="edit_defect_area" value="">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="fa fa-times"></i> Close</button>
                        <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Merge Defect Type --}}
    <div class="modal fade" id="mergeDefectTypeModal" tabindex="-1" aria-labelledby="mergeDefectTypeLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('merge-defect-type') }}" method="post" onsubmit="submitForm(this, event)">
                    <div class="modal-header bg-sb text-light">
                        <h1 class="modal-title fs-5" id="mergeDefectTypeLabel"><i class="fa fa-plus-square"></i> Merge Defect Type</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">From</label>
                            <select class="form-select select2bs4merge" name="defect_type_from" id="defect_type_from">
                                <option value="">Pilih Defect</option>
                                @foreach ($defectTypes as $defectType)
                                    <option value="{{ $defectType->id }}">{{ $defectType->defect_type }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">To</label>
                            <select class="form-select select2bs4merge" name="defect_type_to" id="defect_type_to">
                                <option value="">Pilih Defect</option>
                                @foreach ($defectTypes as $defectType)
                                    <option value="{{ $defectType->id }}">{{ $defectType->defect_type }}</option>
                                @endforeach
                            </select>
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
        let datatableDefectType = $("#datatable-defect-type").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            paging: 10,
            ajax: {
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route('master-defect') }}',
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
                    data: 'defect_type'
                },
                {
                    data: 'allocation'
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
                                <a class='btn btn-primary btn-sm' data-bs-toggle="modal" data-bs-target="#editDefectTypeModal" onclick='editData(` + JSON.stringify(row) + `, "editDefectTypeModal", [{"function" : "dataTableDefectTypeReload()"}]);'>
                                    <i class='fa fa-edit'></i>
                                </a>
                                <a class='btn btn-danger btn-sm' data='`+JSON.stringify(row)+`' data-url='{{ route('destroy-defect-type') }}/`+row['id']+`' onclick='deleteData(this)'>
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

        function dataTableDefectTypeReload() {
            datatableDefectType.ajax.reload();
        }

        let datatableDefectArea = $("#datatable-defect-area").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            paging: 10,
            ajax: {
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route('master-defect') }}',
                dataType: 'json',
                dataSrc: 'data',
                data: {
                    type:"area"
                },
            },
            columns: [
                {
                    data: 'id'
                },
                {
                    data: 'defect_area'
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
                                <a class='btn btn-primary btn-sm' data-bs-toggle="modal" data-bs-target="#editDefectAreaModal" onclick='editData(` + JSON.stringify(row) + `, "editDefectAreaModal", [{"function" : "dataTableDefectAreaReload()"}]);'>
                                    <i class='fa fa-edit'></i>
                                </a>
                                <a class='btn btn-danger btn-sm' data='`+JSON.stringify(row)+`' data-url='{{ route('destroy-defect-area') }}/`+row['id']+`' onclick='deleteData(this)'>
                                    <i class='fa fa-trash'></i>
                                </a>
                            </div>
                        `;
                    }
                },
                {
                    targets: [2],
                    render: (data, type, row, meta) => {
                        return formatDateTime(data);
                    }
                }
            ],
        });

        function dataTableDefectAreaReload() {
            datatableDefectArea.ajax.reload();
        }
    </script>
@endsection
