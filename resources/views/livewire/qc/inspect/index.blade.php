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
<link rel="stylesheet" href="{{ asset('plugins/sweetalert2/sweetalert2.min.css') }}"> <!-- Tambahkan ini -->

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

        <!-- <div class="d-flex justify-content-end mb-3">
            <div class="input-group" style="max-width: 300px;">
                <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                <input type="text" id="cari_grdok" name="cari_grdok" class="form-control" autocomplete="off" placeholder="Search No Document...">
            </div>
        </div> -->

        <div class="table-responsive" style="max-height: 500px">
            <table id="datatable" class="table table-bordered table-striped w-100 text-nowrap">
                <thead>
                    <tr>
                        <!-- Removed No Penerimaan column -->
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
<script src="{{ asset('plugins/sweetalert2/sweetalert2.min.js') }}"></script> <!-- Tambahkan ini -->


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
            type: 'POST', // Ensure POST method for CSRF
            data: function(d) {
                d.tgl_awal = $('#tgl_awal').val();
                d.tgl_akhir = $('#tgl_akhir').val();
                d.cari_grdok = $('#cari_grdok').val();
            },
        },
        columns: [
            // Removed { data: 'no_dok', searchable: true },
            { data: 'tgl_dok', searchable: false },
            { data: 'no_pl', searchable: false },
            { data: 'no_lot', searchable: false },
            { data: 'color', searchable: false },
            { data: 'supplier', searchable: false },
            { data: 'buyer', searchable: false },
            { data: 'style', searchable: false },
            { data: 'jumlah_roll', searchable: false },
            { data: 'catatan', searchable: false },
            {
                data: null,
                render: function(data, type, row) {                    
                    return `
                        <div class='d-flex gap-1 justify-content-center'>
                              <button type='button' class='btn btn-sm btn-info' onclick='buatInspect(${JSON.stringify(row).replace(/"/g, '&quot;')})'>
                                <i class='fa fa-plus'></i> Inspection
                            </button>
                        </div>
                    `;
                }
            }
        ],
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
                        id_whs_lokasi_inmaterial: row.id_item,
                        tgl_pl: row.tgl_dok,
                        no_pl: row.no_pl,
                        no_lot: row.no_lot,
                        color: row.color,
                        supplier: row.supplier,
                        buyer: row.buyer,
                        style: row.style,
                        qty_roll: row.jumlah_roll,
                        notes: row.catatan
                    },
                    success: function(response) {
                        Swal.fire(
                            'Berhasil!',
                            'Inspection berhasil dibuat.',
                            'success'
                        ).then(() => {
                            // Reload tabel setelah berhasil
                            $('#datatable').DataTable().ajax.reload();
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