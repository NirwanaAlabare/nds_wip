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
            // Initialize Select2 (if used in the page)
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
                    error: function(xhr, error, thrown) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to load data. Please try again later.',
                        });
                    }
                },
                columns: [
                    { data: 'tgl_pl', name: 'tgl_pl', defaultContent: '-' },
                    { data: 'no_pl', name: 'no_pl', defaultContent: '-' },
                    { data: 'no_lot', name: 'no_lot', defaultContent: '-' },
                    { data: 'color', name: 'color', defaultContent: '-' },
                    { data: 'supplier', name: 'supplier', defaultContent: '-' },
                    { data: 'buyer', name: 'buyer', defaultContent: '-' },
                    { data: 'style', name: 'style', defaultContent: '-' },
                    { data: 'qty_roll', name: 'qty_roll', defaultContent: '0' },
                    { data: 'notes', name: 'notes', defaultContent: '-' },
                    {
                        data: null,
                        render: function(data, type, row) {
                            // Use JSON.stringify with proper escaping
                            const rowData = JSON.stringify(row).replace(/'/g, "\\'").replace(/"/g, '&quot;');
                            return `
                                <div class="d-flex gap-1 justify-content-center">
                                    <button type="button" class="btn btn-sm btn-info btn-add-details" 
                                            onclick="addDetails('${rowData}')">
                                        <i class="fa fa-plus"></i>
                                    </button>
                                </div>
                            `;
                        }
                    }
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

            // Function to handle addDetails
            window.addDetails = function(rowData) {
                try {
                    // Parse the rowData back to an object
                    const row = JSON.parse(rowData.replace(/&quot;/g, '"'));
                    localStorage.setItem('selectedInspection', JSON.stringify(row));
                    window.location.href = "{{ route('qc-inspect-inmaterial-generateRoll') }}";
                } catch (e) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to process the selected data. Please try again.',
                    });
                }
            };
        });
    </script>
@endsection