@extends('layouts.index')

@section('custom-link')
<link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

<style>
    .table-custom th {
        background-color: var(--sb-color) !important;
        color: white;
        white-space: nowrap;
        text-align: center;
        vertical-align: middle !important;
        font-size: 13px;
    }
    .table-custom td {
        vertical-align: middle !important;
        font-size: 13px;
    }
</style>
@endsection

@section('content')
<div class="card card-sb">
    <div class="card-header">
        <h5 class="card-title fw-bold mb-0">
            <i class="fas fa-building"></i> Master Kantor
        </h5>
        <div class="card-tools">
            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modal-create">
                <i class="fas fa-plus"></i> Tambah Data Kantor
            </button>
        </div>
    </div>
    <div class="card-body">

        <div class="table-responsive">
            <table class="table table-bordered table-sm table-custom table-hover w-100" id="table-credentials">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode</th>
                        <th>Nama</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data as $index => $data)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $data->kode ?? '-' }}</td>
                        <td>{{ $data->nama ?? '-' }}</td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-warning show_modal_edit" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#modal-edit" 
                                    data-url="{{ route('master_kantor_update', $data->id) }}" 
                                    data-nama="{{$data->nama}}" 
                                    data-kode="{{$data->kode}}">
                                <i class="fas fa-edit"></i>
                            </button>
                           <form action="{{ route('master_kantor_delete', $data->id) }}" method="POST" class="d-inline" id="form-delete-{{ $data->id }}">
                                @csrf
                                <button type="button" class="btn btn-sm btn-danger" onclick="confirmDelete({{ $data->id }})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-create" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form action="{{ route('master_kantor_store') }}" method="POST" class="form-ajax">
                @csrf
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white"><i class="fas fa-plus"></i> Tambah Data Kantor</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-left">
                    <div class="row">
                        <div class="col-md-12 border-end">
                            <div class="mb-3">
                                <label>Kode <span class="text-danger">*</span></label>
                                <input type="text" name="kode" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Nama <span class="text-danger">*</span></label>
                                <input type="text" name="nama" class="form-control" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-edit" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form action="" method="POST" class="form-ajax" id="form-edit">
                @csrf
                <div class="modal-header bg-success">
                    <h5 class="modal-title text-white"><i class="fas fa-plus"></i> Edit Data Kantor</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-left">
                    <div class="row">
                        <div class="col-md-12 border-end">
                            <div class="mb-3">
                                <label>Kode <span class="text-danger">*</span></label>
                                <input type="text" name="kode" class="form-control" id="kode_modal" required>
                            </div>
                            <div class="mb-3">
                                <label>Nama <span class="text-danger">*</span></label>
                                <input type="text" name="nama" class="form-control" id="nama_modal" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

{{-- @foreach($data as $data_kantor)
<div class="modal fade" id="modal-edit-{{ $data_kantor->id }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form action="{{ route('master-ceisa.update', $data_kantor->id) }}" method="POST" class="form-ajax">
                @csrf
                @method('PUT')
                <div class="modal-header bg-warning">
                    <h5 class="modal-title text-dark"><i class="fas fa-edit"></i> Edit Kredensial CEISA</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-left">
                    <div class="alert alert-info bg-light text-dark border-info p-2 mb-3">
                        <i class="fas fa-info-circle text-info"></i> <strong>Info API Key:</strong><br>
                        <small>Ambil dari <b>Portal Ceisa</b> &rarr; <b>Apps Manager</b> &rarr; <b>Webhook</b></small>
                    </div>

                    <div class="mb-3">
                        <label>User (Login)</label>
                        <input type="text" class="form-control" value="{{ $data_kantor->user_name ?? '-' }} ({{ $data_kantor->username }})" disabled>
                    </div>

                    <div class="row">
                        <!-- LIVE -->
                        <div class="col-md-6 border-end">
                            <h6 class="fw-bold text-success mb-3"><i class="fas fa-server"></i> Live</h6>
                            <div class="mb-3">
                                <label>CEISA Username (Live) <span class="text-danger">*</span></label>
                                <input type="text" name="ceisa_username" class="form-control" value="{{ $data_kantor->ceisa_username }}" required>
                            </div>
                            <div class="mb-3">
                                <label>CEISA Password (Live) <span class="text-danger">*</span></label>
                                <input type="password" name="ceisa_password" class="form-control" value="{{ $data_kantor->ceisa_password }}" required>
                            </div>
                            <div class="mb-3">
                                <label>CEISA API Key (Live) <span class="text-danger">*</span></label>
                                <textarea name="ceisa_api_key" class="form-control" rows="3" required>{{ $data_kantor->ceisa_api_key }}</textarea>
                            </div>
                        </div>

                        <!-- DEV -->
                        <div class="col-md-6">
                            <h6 class="fw-bold text-secondary mb-3"><i class="fas fa-flask"></i> Dev</h6>
                            <div class="mb-3">
                                <label>CEISA Username (Dev)</label>
                                <input type="text" name="ceisa_username_dev" class="form-control" value="{{ $data_kantor->ceisa_username_dev }}">
                            </div>
                            <div class="mb-3">
                                <label>CEISA Password (Dev)</label>
                                <input type="password" name="ceisa_password_dev" class="form-control" value="{{ $data_kantor->ceisa_password_dev }}">
                            </div>
                            <div class="mb-3">
                                <label>CEISA API Key (Dev)</label>
                                <textarea name="ceisa_api_key_dev" class="form-control" rows="3">{{ $data_kantor->ceisa_api_key_dev }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

<!-- Create Modal -->
<div class="modal fade" id="modal-create" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form action="{{ route('master-ceisa.store') }}" method="POST" class="form-ajax">
                @csrf
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white"><i class="fas fa-plus"></i> Tambah Kredensial CEISA</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-left">
                    <div class="alert alert-info bg-light text-dark border-info p-2 mb-3">
                        <i class="fas fa-info-circle text-info"></i> <strong>Info API Key:</strong><br>
                        <small>Ambil dari <b>Portal Ceisa</b> &rarr; <b>Apps Manager</b> &rarr; <b>Webhook</b></small>
                    </div>

                    @if($isAdmin)
                    <div class="mb-3">
                        <label>Pilih User <span class="text-danger">*</span></label>
                        <select name="username" class="form-control select2bs4" style="width: 100%;" required>
                            <option value="">-- Pilih User --</option>
                            @foreach($users as $user)
                                <option value="{{ $user->username }}" {{ auth()->user()->username == $user->username ? 'selected' : '' }}>{{ $user->nama ?? $user->name ?? '-' }} ({{ $user->username }})</option>
                            @endforeach
                        </select>
                    </div>
                    @else
                    <div class="mb-3">
                        <label>User (Login)</label>
                        <input type="text" class="form-control" value="{{ auth()->user()->nama ?? auth()->user()->name ?? '-' }} ({{ auth()->user()->username }})" disabled>
                    </div>
                    @endif

                    <div class="row">
                        <!-- LIVE -->
                        <div class="col-md-6 border-end">
                            <h6 class="fw-bold text-success mb-3"><i class="fas fa-server"></i> Live</h6>
                            <div class="mb-3">
                                <label>CEISA Username (Live) <span class="text-danger">*</span></label>
                                <input type="text" name="ceisa_username" class="form-control" placeholder="Masukkan Username CEISA Live" required>
                            </div>
                            <div class="mb-3">
                                <label>CEISA Password (Live) <span class="text-danger">*</span></label>
                                <input type="password" name="ceisa_password" class="form-control" placeholder="Masukkan Password CEISA Live" required>
                            </div>
                            <div class="mb-3">
                                <label>CEISA API Key (Live) <span class="text-danger">*</span></label>
                                <textarea name="ceisa_api_key" class="form-control" rows="3" placeholder="Masukkan API Key CEISA Live" required></textarea>
                            </div>
                        </div>

                        <!-- DEV -->
                        <div class="col-md-6">
                            <h6 class="fw-bold text-secondary mb-3"><i class="fas fa-flask"></i> Dev</h6>
                            <div class="mb-3">
                                <label>CEISA Username (Dev)</label>
                                <input type="text" name="ceisa_username_dev" class="form-control" placeholder="Masukkan Username CEISA Dev">
                            </div>
                            <div class="mb-3">
                                <label>CEISA Password (Dev)</label>
                                <input type="password" name="ceisa_password_dev" class="form-control" placeholder="Masukkan Password CEISA Dev">
                            </div>
                            <div class="mb-3">
                                <label>CEISA API Key (Dev)</label>
                                <textarea name="ceisa_api_key_dev" class="form-control" rows="3" placeholder="Masukkan API Key CEISA Dev"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection --}}

@section('custom-script')
<script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
<script src="{{ asset('plugins/sweetalert2/sweetalert2.min.js') }}"></script>

<script>
    $(document).ready(function() {
        $('#table-credentials').DataTable({
            "paging": true,
            "lengthChange": true,
            "searching": true,
            "ordering": true,
            "info": true,
            "autoWidth": false,
            "responsive": true,
        });

        $('.select2bs4').select2({
            theme: 'bootstrap4',
            dropdownParent: $('#modal-create')
        });

        @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Berhasil',
            text: '{{ session('success') }}',
            showConfirmButton: false,
            timer: 1500
        });
        @endif

        @if($errors->any())
        Swal.fire({
            icon: 'error',
            title: 'Gagal Menyimpan',
            html: '{!! implode("<br>", $errors->all()) !!}',
        });
        @endif
    });

    function confirmDelete(id) {
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Kredensial CEISA ini akan dihapus permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                let form = $('#form-delete-' + id);
                $.ajax({
                    url: form.attr('action'),
                    method: form.attr('method'),
                    data: form.serialize(),
                    success: function(response) {
                        if(response.status == 200) {
                            Swal.fire('Berhasil!', response.message, 'success').then(() => location.reload());
                        } else {
                            Swal.fire('Gagal!', response.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error!', xhr.responseJSON?.message || 'Terjadi kesalahan sistem', 'error');
                    }
                });
            }
        });
    }

    $(document).on('submit', '.form-ajax', function(e) {
        e.preventDefault();
        let form = $(this);
        let btnSubmit = form.find('button[type="submit"]');
        let originalText = btnSubmit.html();

        btnSubmit.html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...').prop('disabled', true);

        $.ajax({
            url: form.attr('action'),
            method: form.attr('method'),
            data: form.serialize(),
            success: function(response) {
                if(response.status == 200) {
                    Swal.fire('Berhasil!', response.message, 'success').then(() => location.reload());
                } else {
                    Swal.fire('Gagal!', response.message, 'error');
                    btnSubmit.html(originalText).prop('disabled', false);
                }
            },
            error: function(xhr) {
                let msg = xhr.responseJSON?.message || 'Terjadi kesalahan sistem';
                Swal.fire('Error!', msg, 'error');
                btnSubmit.html(originalText).prop('disabled', false);
            }
        });
    });

    $('.show_modal_edit').on('click', function(){
        let url = $(this).data('url');
        let kode = $(this).data('kode');
        let nama = $(this).data('nama');

        $('#form-edit').attr('action', url);
        
        $('#kode_modal').val(kode);
        $('#nama_modal').val(nama);
    });
</script>
@endsection
