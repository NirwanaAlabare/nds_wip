@extends('layouts.index')

@section('custom-link')
<link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
@endsection

@section('content')
<div class="card card-sb">
    <div class="card-header">
        <h5 class="card-title fw-bold mb-0">Memo Permintaan Pembayaran</h5>
    </div>
    <div class="card-body">
        <div class="form-group row mb-2">
            <div class="col-md-2">
                <label class="form-label"><small>From</small></label>
                <input type="date" class="form-control form-control-sm" id="tgl_awal" value="{{ date('Y-m-01') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label"><small>To</small></label>
                <input type="date" class="form-control form-control-sm" id="tgl_akhir" value="{{ date('Y-m-d') }}">
            </div>
            <div class="col-md-4" style="padding-top:0.5rem;">
                <div class="mt-4">
                    <button class="btn btn-sm btn-primary" onclick="reload()"><i class="fas fa-search"></i> Search</button>
                    <a href="{{ route('accounting-memo-create') }}" class="btn btn-sm btn-info"><i class="fas fa-plus"></i> New Memo</a>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table id="datatable" class="table table-bordered table-striped w-100 text-nowrap">
                <thead>
                    <tr>
                        <th class="text-center">Nomor</th>
                        <th class="text-center">Kepada</th>
                        <th class="text-center">Perihal</th>
                        <th class="text-center">Tujuan Pembayaran</th>
                        <th class="text-center">Jumlah</th>
                        <th class="text-center">Tgl Bayar</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Dibuat</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('custom-script')
<script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>

<script>
let dt = $("#datatable").DataTable({
    ordering: false,
    processing: true,
    serverSide: true,
    paging: true,
    searching: true,
    scrollX: true,
    ajax: {
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        url: '{{ route("accounting-memo") }}',
        dataType: 'json',
        dataSrc: 'data',
        data: d => {
            d.tgl_awal  = $('#tgl_awal').val();
            d.tgl_akhir = $('#tgl_akhir').val();
        }
    },
    columns: [
        { data: 'nomor' },
        { data: 'kepada' },
        { data: 'perihal' },
        { data: 'kepada_detail' },
        { data: 'jumlah' },
        { data: 'tgl_pembayaran' },
        { data: 'status' },
        { data: 'created_info' },
        { data: 'id' },
    ],
    columnDefs: [
        { targets: [4], className: 'text-right',
          render: (d, t, row) => row.mata_uang + ' ' + parseFloat(d||0).toLocaleString('en-US', {minimumFractionDigits:2}) },
        { targets: [6], render: d => {
            if (d === 'DRAFT')    return `<span class="badge bg-warning text-dark">DRAFT</span>`;
            if (d === 'APPROVED') return `<span class="badge bg-success">APPROVED</span>`;
            return `<span class="badge bg-secondary">${d}</span>`;
        }},
        { targets: [8], render: (d, t, row) => {
            let pdf  = "{{ route('accounting-memo-pdf', ':id') }}".replace(':id', d);
            let edit = "{{ route('accounting-memo-edit', ':id') }}".replace(':id', d);
            if (row.status === 'DRAFT') {
                return `<div class='d-flex gap-1 justify-content-center'>
                    <a href='${edit}' class='btn btn-sm btn-warning'><i class='fa-solid fa-pen-to-square'></i></a>
                    <a href='${pdf}' class='btn btn-sm btn-secondary' target='_blank'><i class='fa-solid fa-print'></i></a>
                    <button class='btn btn-sm btn-danger' onclick='deleteMemo(${d})'><i class='fa-solid fa-trash'></i></button>
                </div>`;
            }
            return `<div class='d-flex gap-1 justify-content-center'>
                <a href='${pdf}' class='btn btn-sm btn-secondary' target='_blank'><i class='fa-solid fa-print'></i></a>
            </div>`;
        }}
    ]
});

function reload() { dt.ajax.reload(); }

function deleteMemo(id) {
    Swal.fire({
        title: 'Hapus memo ini?', icon: 'warning',
        showCancelButton: true, confirmButtonColor: '#d33', cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, hapus', cancelButtonText: 'Batal', reverseButtons: true
    }).then(r => {
        if (r.isConfirmed) {
            $.ajax({
                url: "{{ route('accounting-memo-delete', ':id') }}".replace(':id', id),
                type: 'DELETE',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: res => {
                    Swal.fire({ icon:'success', title:'Dihapus', timer:1500, showConfirmButton:false });
                    dt.ajax.reload(null, false);
                }
            });
        }
    });
}
</script>
@endsection
