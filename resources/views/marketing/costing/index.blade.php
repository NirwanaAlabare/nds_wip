@extends('layouts.index')

@section('custom-link')
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">

    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <style>
        :root {
            --sb-navy: #0f172a;
            --sb-blue: #3085d6;
            --sb-blue-dark: #1e3a8a;
            --sb-border: #e2e8f0;
        }

        /* ============ CARD & HEADER (konsisten dgn halaman lain) ============ */
        .card-sb {
            border: none;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(30, 41, 59, 0.08);
        }

        .card-sb > .card-header {
            background: var(--sb-navy) !important;
            border: none;
            padding: 1.6rem 2rem;
            position: relative;
        }

        .card-sb > .card-header::before {
            content: "";
            position: absolute;
            left: 0; top: 0; bottom: 0;
            width: 5px;
            background: repeating-linear-gradient(180deg, var(--sb-blue) 0px, var(--sb-blue) 10px, transparent 10px, transparent 20px);
        }

        .card-sb > .card-header .card-eyebrow {
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 1.4px;
            text-transform: uppercase;
            color: #5aa9f0;
            display: block;
            margin-bottom: 4px;
        }

        .card-sb > .card-header .card-title {
            font-size: 1.15rem;
            font-weight: 700;
            color: #f8fafc !important;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-sb > .card-body {
            background: #fbfcff;
            padding: 1.75rem 2rem 2rem;
        }

        /* ============ BUTTON CREATE ============ */
        .btn-outline-primary {
            border-radius: 8px;
            font-weight: 600;
            border: 1.5px solid var(--sb-blue);
            color: #1e5da8;
            transition: all 0.2s ease;
        }
        .btn-outline-primary:hover {
            background: var(--sb-blue);
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(48, 133, 214, 0.25);
        }

        /* ============ FILTER PANEL ============ */
        .filter-panel {
            background: #fff;
            border: 1px solid #e7ecf3;
            border-radius: 14px;
            padding: 1rem 1.25rem;
            margin-bottom: 1.4rem;
            box-shadow: 0 2px 10px rgba(15, 23, 42, 0.04);
        }

        .filter-panel label {
            font-weight: 700;
            font-size: 0.72rem;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            margin-bottom: 4px;
        }

        .filter-panel .form-control {
            border-radius: 8px;
            border: 1.5px solid var(--sb-border);
        }
        .filter-panel .form-control:focus {
            border-color: var(--sb-blue);
            box-shadow: 0 0 0 3px rgba(48, 133, 214, 0.12);
        }

        .filter-panel .btn-primary {
            border-radius: 8px;
            font-weight: 600;
            background: linear-gradient(135deg, var(--sb-blue), var(--sb-blue-dark));
            border: none;
            box-shadow: 0 4px 12px rgba(48, 133, 214, 0.25);
            transition: all 0.2s ease;
        }
        .filter-panel .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(48, 133, 214, 0.35);
        }

        /* ============ TABLE ============ */
        #table-costing thead tr {
            background: var(--sb-navy);
        }
        #table-costing thead th {
            color: #f8fafc !important;
            font-size: 11.5px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            font-weight: 700;
            vertical-align: middle;
            border-color: #1e293b;
        }
        #table-costing tbody td {
            font-size: 13px;
            vertical-align: middle;
        }
        #table-costing tbody tr:hover {
            background-color: #f4f8fd;
        }

        /* ============ ACTION BUTTONS DI TABEL ============ */
        #table-costing .btn {
            border-radius: 6px;
            font-weight: 600;
        }
    </style>
@endsection

@section('content')
    <div class="card card-sb">
        <div class="card-header">
            <span class="card-eyebrow">Marketing &middot; Costing</span>
            <h5 class="card-title mb-0">
                <i class="fas fa-list"></i> List Data Costing
            </h5>
        </div>

        <div class="card-body">
            <div class="mb-3">
                <a href="{{ route('create-costing') }}" class="btn btn-outline-primary">
                    <i class="fas fa-plus"></i> Create Costing
                </a>
            </div>

            <div class="filter-panel">
                <div class="row align-items-end">
                    <div class="col-md-2 col-6 mb-2 mb-md-0">
                        <label class="d-block">Tgl Awal</label>
                        <input type="date" class="form-control form-control-sm" id="tgl-awal" name="tgl_awal" value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="col-md-2 col-6 mb-2 mb-md-0">
                        <label class="d-block">Tgl Akhir</label>
                        <input type="date" class="form-control form-control-sm" id="tgl-akhir" name="tgl_akhir" value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="col-md-2 col-6">
                        <button type="button" class="btn btn-primary btn-sm w-100" onclick="dataTableReload()">
                            <i class="fas fa-search"></i> Filter
                        </button>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover w-100" id="table-costing">
                    <thead>
                        <tr class="text-center">
                            <th>No Costing</th>
                            <th>Tgl. Costing</th>
                            <th>Buyer</th>
                            <th>Brand</th>
                            <th>Style</th>
                            <th>Market</th>
                            <th>Qty</th>
                            <th>Confirm Price</th>
                            <th>Product Group</th>
                            <th>Product Item</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
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
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        let table;

        $(document).ready(function() {

            table = $('#table-costing').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: '{{ route("master-costing") }}',
                    data: function(d) {
                        d.tgl_awal = $('#tgl-awal').val();
                        d.tgl_akhir = $('#tgl-akhir').val();
                    }
                },
                columns: [
                    { data: 'no_costing', name: 'a.no_costing', className: 'text-center align-middle' },
                    { data: 'tgl_costing', name: 'a.created_at', className: 'text-center align-middle' },
                    { data: 'nama_buyer', name: 'b.Supplier', className: 'align-middle' },
                    { data: 'brand', name: 'a.brand', className: 'align-middle' },
                    { data: 'style', name: 'a.style', className: 'align-middle' },
                    { data: 'market', name: 'a.market', className: 'align-middle' },
                    { data: 'qty', name: 'a.qty', className: 'text-center align-middle' },
                    {
                        data: 'confirm_price',
                        name: 'a.confirm_price',
                        className: 'text-center align-middle',
                        render: function (data, type, row) {
                            if (data !== null && data !== '') {
                                return parseFloat(data).toFixed(6);
                            }
                            return '0';
                        }
                    },
                    { data: 'product_group_text', name: 'p.product_group', className: 'text-center align-middle' },
                    { data: 'product_item_text', name: 'p.product_item', className: 'text-center align-middle' },
                    {
                        data: 'id',
                        name: 'a.id',
                        orderable: false,
                        searchable: false,
                        className: 'text-center align-middle',
                        render: function (data) {
                            let editUrl = "{{ route('edit-costing', ':id') }}".replace(':id', data);
                            let pdfUrl = "{{ route('print-costing-pdf', ':id') }}".replace(':id', data);
                            let excelUrl = "{{ route('print-excel-costing', ':id') }}".replace(':id', data);
                            let copyUrl = "{{ route('copy-costing', ':id') }}".replace(':id', data);

                            return `
                                <div style="display: flex; justify-content: center; gap: 5px; flex-wrap: nowrap;">
                                    <a href="${editUrl}" class="btn btn-sm btn-primary py-1 px-2" style="font-size: 12px; white-space: nowrap;">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="${pdfUrl}" target="_blank" class="btn btn-sm btn-danger py-1 px-2" style="font-size: 12px; white-space: nowrap;">
                                        <i class="fas fa-file-pdf"></i> PDF
                                    </a>
                                    <a href="${excelUrl}" target="_blank" class="btn btn-sm btn-success py-1 px-2" style="font-size: 12px; white-space: nowrap;">
                                        <i class="fas fa-file-excel"></i> Excel
                                    </a>
                                    <button onclick="confirmCopy('${copyUrl}')" class="btn btn-sm btn-warning py-1 px-2" style="font-size: 12px; white-space: nowrap;">
                                        <i class="fas fa-copy"></i> Copy
                                    </button>
                                </div>
                            `;
                        }
                    }
                ],
                order: [[2, 'desc']]
            });
        });


        function dataTableReload() {
            table.ajax.reload();
        }

        function confirmCopy(url) {
            Swal.fire({
                title: 'Copy Costing?',
                text: 'Data header dan semua detail akan disalin ke nomor costing baru.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-copy"></i> Ya, Copy!',
                cancelButtonText: 'Batal',
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Sedang menyalin...',
                        text: 'Mohon tunggu sebentar.',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading(); }
                    });
                    window.location.href = url;
                }
            });
        }
    </script>
@endsection
