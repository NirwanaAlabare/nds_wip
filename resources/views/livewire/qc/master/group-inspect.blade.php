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
        <h5 class="card-title fw-bold mb-0">Data Master Group Inspect</h5>
    </div>
    <div class="card-body">
        <div class="d-flex align-items-end gap-3 mb-3">
            <div class="col-md-6">
            </div>
            <div class="col-md-6 text-end">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-tambah-group-inspect" style="background-color: var(--sb-color) !important;">
                    Tambah Group Inspect
                </button>
            </div>
        </div>

        @if (session()->has('message'))
            <div class="alert alert-success">
                {{ session('message') }}
            </div>
        @endif

        <div class="table-responsive">
            <table id="datatable" class="table table-bordered table-striped w-100">
                <thead>
                    <tr>
                        <th class="text-center">Group Inspect </th>
                        <th class="text-center">Name Fabric Group</th>
                        <th class="text-center">Individu</th>
                        <th class="text-center">Shipment</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Data will be populated by Yajra DataTables -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah Group Inspect -->
<div class="modal fade" id="modal-tambah-group-inspect" tabindex="-1" aria-labelledby="modal-tambah-group-inspectLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('qc-inspect-group-inspect.create') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal-tambah-group-inspectLabel">Tambah Group Inspect</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="group_inspect" class="form-label">Group Inspect </label>
                        <input type="number" name="group_inspect" class="form-control" id="group_inspect" min="0" step="1" required>
                        @error('group_inspect') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-3">
                        <label for="name_fabric_group" class="form-label">Name Fabric Group</label>
                        <input type="text" name="name_fabric_group" class="form-control" id="name_fabric_group" maxlength="250" required>
                        @error('name_fabric_group') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-3">
                        <label for="individu" class="form-label">Individu</label>
                         <input type="number" name="individu" class="form-control" id="individu" min="0" step="1" required>
                        @error('individu') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-3">
                        <label for="shipment" class="form-label">Shipment</label>
                         <input type="number" name="shipment" class="form-control" id="shipment" min="0" step="1" required>
                        @error('shipment') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" style="background-color: var(--sb-color) !important;" class="btn btn-primary">Save</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Group Inspect -->
<div class="modal fade" id="modal-edit-group-inspect" tabindex="-1" aria-labelledby="modal-edit-group-inspectLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="edit-form" action="{{ route('qc-inspect-group-inspect.update') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal-edit-group-inspectLabel">Edit Group Inspect</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit-id">
                    <div class="mb-3">
                        <label for="edit-group_inspect" class="form-label">Group Inspect (Number)</label>
                        <input type="number" name="group_inspect" class="form-control" id="edit-group_inspect" min="0" step="1" required>
                        @error('group_inspect') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-3">
                        <label for="edit-name_fabric_group" class="form-label">Name Fabric Group</label>
                        <input type="text" name="name_fabric_group" class="form-control" id="edit-name_fabric_group" maxlength="250" required>
                        @error('name_fabric_group') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-3">
                        <label for="edit-individu" class="form-label">Individu</label>
                        <input type="number" name="individu" class="form-control" id="edit-individu" min="0" step="1" required>
                        @error('individu') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-3">
                        <label for="edit-shipment" class="form-label">Shipment</label>
                        <input type="number" name="shipment" class="form-control" id="edit-shipment" min="0" step="1" required>
                        @error('shipment') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('custom-script')
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('#datatable').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('qc-inspect-group-inspect.data') }}',
                columns: [
                    { 
                        data: 'group_inspect', 
                        name: 'group_inspect',
                        className: 'text-center'
                    },
                    { 
                        data: 'name_fabric_group', 
                        name: 'name_fabric_group',
                        className: 'text-center'
                    },
                    { 
                        data: 'individu', 
                        name: 'individu',
                        className: 'text-center'
                    },
                    { 
                        data: 'shipment', 
                        name: 'shipment',
                        className: 'text-center'
                    },
                    { 
                        data: 'action', 
                        name: 'action', 
                        orderable: false, 
                        searchable: false,
                        className: 'text-center',
                        render: function(data, type, row) {
                            return `
                                <div class="d-flex gap-1 justify-content-center">
                                    <button class="btn btn-warning btn-sm edit-btn" 
                                            data-id="${row.id}"
                                            data-group_inspect="${row.group_inspect}"
                                            data-name_fabric_group="${row.name_fabric_group}"
                                            data-individu="${row.individu}"
                                            data-shipment="${row.shipment}"
                                            style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-edit fa-sm"></i>
                                    </button>
                                    <form action="{{ route('qc-inspect-group-inspect.delete', '') }}/${row.id}" method="POST" class="delete-form d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" 
                                                style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                            <i class="fas fa-trash fa-sm"></i>
                                        </button>
                                    </form>
                                </div>
                            `;
                        }
                    }
                ]
            });

            // Handle edit button click
            $(document).on('click', '.edit-btn', function() {
                var id = $(this).data('id');
                var groupInspect = $(this).data('group_inspect');
                var nameFabricGroup = $(this).data('name_fabric_group');
                var individu = $(this).data('individu');
                var shipment = $(this).data('shipment');
                
                $('#edit-id').val(id);
                $('#edit-group_inspect').val(groupInspect);
                $('#edit-name_fabric_group').val(nameFabricGroup);
                $('#edit-individu').val(individu);
                $('#edit-shipment').val(shipment);
                
                $('#modal-edit-group-inspect').modal('show');
            });

            // Handle delete form submission
            $(document).on('submit', '.delete-form', function(e) {
                e.preventDefault();
                if (confirm('Are you sure you want to delete this item?')) {
                    this.submit();
                }
            });
        });
    </script>
@endsection