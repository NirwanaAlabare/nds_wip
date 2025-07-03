@extends('layouts.index')

@section('custom-link')
<!-- DataTables -->
<link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">

<!-- Select2 -->
<link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

<!-- SweetAlert2 -->
<link rel="stylesheet" href="{{ asset('plugins/sweetalert2/sweetalert2.min.css') }}">
@endsection

@section('content')
<div class="card card-sb">
    <div class="card-header">
        <h5 class="card-title fw-bold mb-0">Data Penerimaan </h5>
    </div>
    <div class="card-body">
        <div class="d-flex align-items-end gap-3 mb-3">
            <div class="col-md-12">
                <div class="form-group row">
                    <div class="col-md-2">
                        <div class="mb-1">
                            <div class="form-group">
                                <label class="form-label">From</label>
                                <input type="date" class="form-control" id="tgl_awal" name="tgl_awal" value="{{ now()->subDays(30)->format('Y-m-d') }}">
                            </div>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="mb-1">
                            <div class="form-group">
                                <label class="form-label">To</label>
                                <input type="date" class="form-control" id="tgl_akhir" name="tgl_akhir" value="{{ now()->format('Y-m-d') }}">
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6" style="padding-top: 0.5rem;">
                        <div class="mt-4">
                            <button class="btn btn-primary" onclick="dataTableReload()"><i class="fas fa-search"></i> Search</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="table-responsive" style="max-height: 500px">
            <table id="datatable" class="table table-bordered table-striped w-100 text-nowrap">
                <thead>
                    <tr>
                        <th class="text-center">Date</th>
                        <th class="text-center">No PL</th>
                        <th class="text-center">No Lot</th>
                        <th class="text-center">Color</th>
                        <th class="text-center">Supplier</th>
                        <th class="text-center">Buyer</th>
                        <th class="text-center">Style</th>
                        <th class="text-center">Qty Roll</th>
                        <th class="text-center">Notes</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
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

<!-- SweetAlert2 -->
<script src="{{ asset('plugins/sweetalert2/sweetalert2.min.js') }}"></script>

<script>
$(document).ready(function() {
    // Initialize Select2
    $('.select2').select2({
        theme: 'bootstrap4'
    });

    // DataTable initialization
    let datatable = $("#datatable").DataTable({
        ordering: false,
        processing: true,
        serverSide: true,
        ajax: {
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '{{ route("qc-inspect-inmaterial.data") }}',
            type: 'POST',
            data: function(d) {
                d.tgl_awal = $('#tgl_awal').val();
                d.tgl_akhir = $('#tgl_akhir').val();
                d.cari_grdok = $('#cari_grdok').val(); // Include search input
            },
        },
      columns: [
            { data: 'tgl_dok', searchable: true },
            { data: 'no_pl', searchable: true },
            { data: 'no_lot', searchable: true },
            { data: 'color', searchable: true },
            { data: 'supplier', searchable: true },
            { data: 'buyer', searchable: true },
            { data: 'style', searchable: true },
            { data: 'jumlah_roll', searchable: true },
            { data: 'catatan', searchable: true },
            {
                data: null,
                render: function(data, type, row) {                    
                    if (row.already_inspected) {
                        return `
                            <div class='d-flex gap-1 justify-content-center'>
                                <button type='button' class='btn btn-sm btn-info' disabled>
                                    <i class='fa fa-check'></i> Sudah Diinspect
                                </button>
                            </div>
                        `;
                    } else {
                        return `
                            <div class='d-flex gap-1 justify-content-center'>
                                <button type='button' class='btn btn-sm btn-info' onclick='buatInspect(${JSON.stringify(row).replace(/"/g, '&quot;')})'>
                                    <i class='fa fa-plus'></i> Inspection
                                </button>
                            </div>
                        `;
                    }
                }
            }
        ],
        createdRow: function(row, data, dataIndex) {
            if (data.already_inspected) {
                $(row).addClass('bg-success text-white');
            }
        },
        columnDefs: [
            {
                targets: [0, 1, 2, 3, 4, 5, 6, 7, 8],
                className: 'text-center'
            }
        ]
    });

    // Reload DataTable on search
    window.dataTableReload = function() {
        datatable.ajax.reload();
    };

    // Search by No Document
    $('#cari_grdok').on('keyup', function() {
        datatable.ajax.reload();
    });

    // Optional: Function to handle view details (implement as needed)
    window.buatInspect = function(row) {
        console.log('row', row.no_pl);
        
        Swal.fire({
            title: 'Buat Inspection?',
            html: `
                <div class="text-left">
                    <p>Anda akan membuat inspection untuk:</p>
                    <ul>
                        <li><strong>No PL:</strong> ${row.no_pl}</li>
                        <li><strong>No Lot:</strong> ${row.no_lot}</li>
                        <li><strong>Color:</strong> ${row.color}</li>
                    </ul>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Buat Inspection',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // Tampilkan loading
                Swal.fire({
                    title: 'Memproses',
                    html: 'Sedang membuat inspection...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading()
                    }
                });

                // Kirim data ke server
                $.ajax({
                    url: '{{ route("qc-inspect-inmaterial-header.store") }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        id_whs_lokasi_inmaterial: row.id_whs_lokasi_inmaterial,
                        id_item: row.id_item,
                        tgl_pl: row.tgl_dok,
                        no_pl: row.no_pl,
                        no_lot: row.no_lot,
                        color: row.color,
                        supplier: row.supplier,
                        buyer: row.buyer,
                        style: row.style,
                        qty_roll: row.jumlah_roll,
                        notes: row.catatan,
                        no_dok: row.no_dok,
                    },
                    success: function(response) {
                        var redirectUrl = "{{ route('qc-inspect-inmaterial-header') }}";
                        Swal.fire(
                            'Berhasil!',
                            'Inspection berhasil dibuat.',
                            'success'
                        ).then(() => {
                            window.location.href = redirectUrl;
                        });
                    },
                    error: function(xhr) {
                        Swal.fire(
                            'Gagal!',
                            xhr.responseJSON?.message || 'Terjadi kesalahan saat membuat inspection.',
                            'error'
                        );
                    }
                });
            }
        });
    };
});
</script>
@endsection