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
    <div class="card-header bg-sb">
        <h5 class="card-title fw-bold mb-0"><i class="fas fa-list"></i> Booking Stock</h5>
    </div>
    <div class="card-body">

        <div class="mb-3">
            <a href="{{ route('booking-stock-create') }}" class="btn btn-outline-primary">
                <i class="fas fa-plus"></i> Create Booking
            </a>
        </div>

        <div class="row align-items-end mb-3">
            <div class="col-md-2">
                <label class="small fw-bold">Tanggal Awal</label>
                <input type="date" id="filter_tgl_awal" class="form-control form-control-sm" value="{{ date('Y-m-01') }}">
            </div>
            <div class="col-md-2">
                <label class="small fw-bold">Tanggal Akhir</label>
                <input type="date" id="filter_tgl_akhir" class="form-control form-control-sm" value="{{ date('Y-m-t') }}">
            </div>
            <div class="col-md-2">
                <label class="small fw-bold">Status</label>
                <select id="filter_status" class="form-control form-control-sm select2bs4">
                    <option value="">Semua Status</option>
                    <option value="Draft">Draft</option>
                    <option value="Approved">Approved</option>
                    <option value="Canceled">Canceled</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="small fw-bold">Jenis</label>
                <select id="filter_jenis" class="form-control form-control-sm select2bs4">
                    <option value="">Semua Jenis</option>
                    <option value="fabric">Fabric</option>
                    <option value="accessoris">Accessoris</option>
                </select>
            </div>
        </div>

        <div class="row align-items-end mb-4">
            <div class="col-md-4">
                <label class="small fw-bold">Search</label>
                <input type="text" id="filter_search" class="form-control form-control-sm" placeholder="Cari Nama Barang, Jenis, No WS">
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button class="btn btn-primary btn-sm mr-2" onclick="refreshTable()">
                    <i class="fas fa-search"></i> Cari
                </button>
                <button class="btn btn-success btn-sm btn-export-excel">
                    <i class="fas fa-file-excel"></i> Export Excel
                </button>
            </div>
        </div>

        <hr>

        <div class="table-responsive">
            <table class="table table-bordered table-sm table-custom table-hover w-100" id="table-booking">
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th width="10%">Tgl Booking</th>
                        <th width="15%">No Booking</th>
                        <th width="10%">Jenis</th>
                        <th width="18%">Nama Barang</th>
                        <th width="10%">Qty</th>
                        <th width="8%">Satuan</th>
                        <th width="8%">WS</th>
                        <th width="8%">Status</th>
                        <th width="8%">Act</th>
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
<script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>

<script>
    let tableBooking;

    $(document).ready(function() {

        $('.select2bs4').select2({ theme: 'bootstrap4', width: '100%' });

        tableBooking = $('#table-booking').DataTable({
            processing: true,
            serverSide: true,
            searching: false,
            ajax: {
                url: '{{ route("booking-stock") }}',
                data: function (d) {
                    d.tgl_awal = $('#filter_tgl_awal').val();
                    d.tgl_akhir = $('#filter_tgl_akhir').val();
                    d.status = $('#filter_status').val();
                    d.jenis = $('#filter_jenis').val();
                    d.search_text = $('#filter_search').val();
                }
            },
            columns: [
                {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center'},
                {data: 'tgl_booking', name: 'tgl_booking', className: 'text-center'},
                {data: 'no_booking', name: 'bs.no_booking', className: 'text-center'},
                {
                    data: 'jenis',
                    name: 'bs.jenis',
                    className: 'text-center',
                    render: function(data) {
                        if (data === 'fabric') return 'Fabric';
                        if (data === 'accessoris') return 'Accessoris';
                        return data || '-';
                    }
                },
                {data: 'nama_barang', name: 'bsd.nama_barang'},
                {data: 'qty', name: 'bsd.qty', className: 'text-right'},
                {data: 'satuan', name: 'bsd.satuan', className: 'text-center'},
                {data: 'ws', name: 'bsd.ws', className: 'text-center'},
                {
                    data: 'status',
                    name: 'bs.status',
                    className: 'text-center',
                    render: function(data, type, row) {
                        let badgeClass = 'warning';
                        if(data === 'Approved') badgeClass = 'success';
                        else if(data === 'Canceled') badgeClass = 'danger';

                        return `<span class="badge badge-${badgeClass}">${data}</span>`;
                    }
                },
                {data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center'},
            ]
        });

        $('#filter_search').on('keypress', function(e) {
            if(e.which === 13) {
                refreshTable();
            }
        });
    });

    function refreshTable() {
        tableBooking.ajax.reload(null, false);
    }

    $(document).on('click', '.btn-export-excel', function() {
        let tgl_awal = $('#filter_tgl_awal').val();
        let tgl_akhir = $('#filter_tgl_akhir').val();
        let status = $('#filter_status').val();
        let jenis = $('#filter_jenis').val();
        let search = $('#filter_search').val();

        Swal.fire('Info', 'Fitur Export Excel belum disambungkan ke Controller!', 'info');
    });

    function deleteBooking(id) {
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Item booking ini akan dihapus",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: 'Menghapus Data...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); }});

                let url = '{{ route("booking-stock-delete", ":id") }}';
                url = url.replace(':id', id);

                $.ajax({
                    url: url,
                    type: "DELETE",
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(res) {
                        Swal.close();
                        if(res.status == 200) {
                            Swal.fire('Terhapus!', res.message, 'success');
                            refreshTable();
                        } else {
                            Swal.fire('Gagal!', res.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.close();
                        Swal.fire('Error!', 'Terjadi kesalahan pada server saat menghapus data.', 'error');
                    }
                });
            }
        });
    }
</script>
@endsection
