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
        <h5 class="card-title fw-bold mb-0">Data QC Inspection</h5>
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
            url: '{{ route("qc-inspect-inmaterial-header.data") }}',
            type: 'POST',
            data: function(d) {
                d.tgl_awal = $('#tgl_awal').val();
                d.tgl_akhir = $('#tgl_akhir').val();
            },
        },
        columns: [
            { data: 'tgl_pl', name: 'tgl_pl' },
            { data: 'no_pl', name: 'no_pl' },
            { data: 'no_lot', name: 'no_lot' },
            { data: 'color', name: 'color' },
            { data: 'supplier', name: 'supplier' },
            { data: 'buyer', name: 'buyer' },
            { data: 'style', name: 'style' },
            { data: 'qty_roll', name: 'qty_roll' },
            { data: 'notes', name: 'notes' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        columnDefs: [
            {
                targets: '_all',
                className: 'text-center'
            }
        ]
    });

    // Reload DataTable on search
    window.dataTableReload = function() {
        datatable.ajax.reload();
    };

    // View details function
    window.viewDetails = function(row) {
        // Implement your details view logic here
        console.log(row);
        Swal.fire({
            title: 'Inspection Details',
            html: `
                <div class="text-left">
                    <p><strong>No PL:</strong> ${row.no_pl}</p>
                    <p><strong>No Lot:</strong> ${row.no_lot}</p>
                    <p><strong>Color:</strong> ${row.color}</p>
                    <p><strong>Supplier:</strong> ${row.supplier}</p>
                    <p><strong>Buyer:</strong> ${row.buyer}</p>
                    <p><strong>Style:</strong> ${row.style}</p>
                    <p><strong>Qty Roll:</strong> ${row.qty_roll}</p>
                    <p><strong>Notes:</strong> ${row.notes || '-'}</p>
                </div>
            `,
            icon: 'info',
            confirmButtonText: 'OK'
        });
    };
});
</script>
@endsection