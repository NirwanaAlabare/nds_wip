@extends('layouts.index')
@livewireScripts
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
        <h5 class="card-title fw-bold mb-0">Data Master Satuan</h5>
    </div>
    <div class="card-body">
        <div class="d-flex align-items-end gap-3 mb-3">
            <div class="col-md-6">
                <input type="text" wire:model="search" class="form-control" placeholder="Search Satuan...">
            </div>
            <div class="col-md-6 text-end">
<button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-tambah-satuan" style="background-color: var(--sb-color) !important;">Tambah Satuan</button>            </div>
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
                        <th class="text-center">Satuan</th>
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

<!-- Modal Tambah Satuan -->
<div class="modal fade" id="modal-tambah-satuan" tabindex="-1" aria-labelledby="modal-tambah-satuanLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('qc-inspect-satuan.create') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal-tambah-satuanLabel">Tambah Satuan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="satuan" class="form-label">Satuan</label>
                        <input type="text" name="satuan" class="form-control" id="satuan" required>
                        @error('satuan') <span class="text-danger">{{ $message }}</span> @enderror
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

<!-- Modal Edit Satuan -->
<div class="modal fade" id="modal-edit-satuan" tabindex="-1" aria-labelledby="modal-edit-satuanLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="edit-form" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal-edit-satuanLabel">Edit Satuan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit-satuan" class="form-label">Satuan</label>
                        <input type="text" name="satuan" class="form-control" id="edit-satuan" required>
                        @error('satuan') <span class="text-danger">{{ $message }}</span> @enderror
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
                ajax: '{{ route('qc-inspect-satuan.data') }}',
                columns: [
                    { data: 'satuan', name: 'satuan' },
                    { 
                        data: 'action', 
                        name: 'action', 
                        orderable: false, 
                        searchable: false,
                        render: function(data, type, row) {
                            return `
                                <div class="d-flex gap-1 justify-content-center">
                                    <button class="btn btn-warning btn-sm edit-btn" 
                                            data-id="${row.id}"
                                            data-satuan="${row.satuan}"
                                            style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-edit fa-sm"></i>
                                    </button>
                                    <form action="{{ route('qc-inspect-satuan.delete', '') }}/${row.id}" method="POST" class="delete-form d-inline">
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
                var satuan = $(this).data('satuan');
                
                $('#edit-satuan').val(satuan);
                $('#edit-form').attr('action', '{{ route("qc-inspect-satuan.update") }}/' + id);
                
                $('#modal-edit-satuan').modal('show');
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