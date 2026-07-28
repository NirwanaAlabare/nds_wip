@extends('layouts.index')

@section('custom-link')
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

    <style>
        :root {
            --sb-navy: #0f172a;
            --sb-blue: #3085d6;
            --sb-blue-dark: #1e3a8a;
        }

        #table-detail thead th {
            position: sticky;
            top: 0;
            z-index: 2;
            background-color: #0f172a;
            color: #f8fafc;
            box-shadow: inset 0 -1px 0 #1e293b;
            font-size: 11.5px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        #table-detail thead tr:nth-child(2) th {
            top: 33px;
            z-index: 1;
            background-color: #f8fafc;
            box-shadow: none;
        }

        #table-detail thead tr:nth-child(2) .form-control {
            border-radius: 6px;
            border: 1.5px solid #e2e8f0;
        }

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
            border: 1.5px solid #3085d6;
            color: #1e5da8;
            transition: all 0.2s ease;
        }
        .btn-outline-primary:hover {
            background: #3085d6;
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
        }

        .filter-panel .form-control {
            border-radius: 8px;
            border: 1.5px solid #e2e8f0;
        }
        .filter-panel .form-control:focus {
            border-color: #3085d6;
            box-shadow: 0 0 0 3px rgba(48, 133, 214, 0.12);
        }

        .filter-panel .btn-primary {
            border-radius: 8px;
            font-weight: 600;
            background: linear-gradient(135deg, #3085d6, #1e3a8a);
            border: none;
            box-shadow: 0 4px 12px rgba(48, 133, 214, 0.25);
            transition: all 0.2s ease;
        }
        .filter-panel .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(48, 133, 214, 0.35);
        }

        /* ============ TABLE ============ */
        #table-bom thead tr {
            background: #0f172a;
        }
        #table-bom thead th {
            color: #f8fafc !important;
            font-size: 11.5px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            font-weight: 700;
            vertical-align: middle;
            border-color: #1e293b;
        }
        #table-bom tbody td {
            font-size: 13px;
            vertical-align: middle;
        }
        #table-bom tbody tr:hover {
            background-color: #f4f8fd;
        }

        /* ============ ACTION BUTTONS DI TABEL ============ */
        #table-bom .btn {
            border-radius: 6px;
            font-weight: 600;
        }

        /* ============ MODAL DETAIL ============ */
        #modalDetail .modal-content {
            border: none;
            border-radius: 14px;
            overflow: hidden;
        }
        #modalDetail .modal-header.bg-sb {
            background: #0f172a !important;
            border: none;
            position: relative;
            padding: 1.1rem 1.5rem;
        }
        #modalDetail .modal-header.bg-sb::before {
            content: "";
            position: absolute;
            left: 0; top: 0; bottom: 0;
            width: 4px;
            background: repeating-linear-gradient(180deg, #3085d6 0px, #3085d6 8px, transparent 8px, transparent 16px);
        }
        #modalDetail .modal-title {
            font-size: 1rem;
        }
        #modalDetail .modal-footer {
            border-top: 1px solid #e7ecf3;
            background: #fbfcff;
        }
        #modalDetail .btn-outline-danger {
            border-radius: 8px;
            font-weight: 600;
        }
    </style>
@endsection

@section('content')
<div class="card card-sb">
    <div class="card-header">
        <span class="card-eyebrow">Marketing &middot; BOM</span>
        <h5 class="card-title fw-bold mb-0"><i class="fas fa-list"></i> List Data BOM</h5>
    </div>
    <div class="card-body">
        <div class="mb-3">
            <a href="{{ route('create-bom') }}" class="btn btn-outline-primary">
                <i class="fas fa-plus"></i>
                Create BOM
            </a>
        </div>

        <div class="filter-panel">
            <div class="row align-items-end">
                <div class="col-md-2">
                    <label class="small fw-bold">Tgl Awal</label>
                    <input type="date" id="date_from" class="form-control form-control-sm" value="{{ date('Y-m-d') }}">
                </div>
                <div class="col-md-2">
                    <label class="small fw-bold">Tgl Akhir</label>
                    <input type="date" id="date_to" class="form-control form-control-sm" value="{{ date('Y-m-d') }}">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary btn-sm w-100" onclick="refreshTable()">
                        <i class="fas fa-search"></i> Filter
                    </button>
                </div>
            </div>
        </div>


        <div class="table-responsive">
            <table class="table table-bordered table-hover w-100" id="table-bom">
                <thead>
                    <tr class="text-center">
                        <th>No</th>
                        <th>No Katalog BOM</th>
                        <th>No Costing</th>
                        <th>Buyer</th>
                        <th>Style</th>
                        <th>Market</th>
                        <th>WS</th>
                        <th>Total Cons</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalDetail" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-sb text-light">
                <h5 class="modal-title fw-bold"><i class="fas fa-info-circle"></i> Detail BOM Marketing</h5>
                <button type="button" class="close text-light" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                    <table class="table table-sm table-striped table-bordered w-100" id="table-detail">
                        <thead class="bg-light">
                            <tr class="text-center">
                                <th width="5%">No</th>
                                <th width="20%">Content</th>
                                <th>Item Description</th>
                                <th width="15%">Color</th>
                                <th width="15%">Size</th>
                                <th width="10%">Cons</th>
                                <th width="10%">Unit</th>
                            </tr>
                            <tr class="bg-light filter-row">
                                <th></th> <th><input type="text" class="form-control form-control-sm column-search" data-column="1"></th>
                                <th><input type="text" class="form-control form-control-sm column-search" data-column="2"></th>
                                <th><input type="text" class="form-control form-control-sm column-search" data-column="3"></th>
                                <th><input type="text" class="form-control form-control-sm column-search" data-column="4"></th>
                                <th></th> <th><input type="text" class="form-control form-control-sm column-search" data-column="6"></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-danger btn-sm" data-bs-dismiss="modal"><i class="fas fa-times-circle"></i> Tutup</button>
            </div>
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
    <script>
        let tableBom;
        let tableDetail = null;

        $(document).ready(function() {
            $(".close").click(function() {
                $("#modalDetail").modal("hide");
            });

            $('#modalDetail').on('shown.bs.modal', function () {
                if ($.fn.DataTable.isDataTable('#table-detail')) {
                    $('#table-detail').DataTable().columns.adjust().responsive.recalc();
                }
            });

            tableBom = $('#table-bom').DataTable({
                processing: true,
                serverSide: false,
                ajax: {
                    url: window.location.pathname,
                    type: "GET",
                    data: function (d) {
                        d.date_from = $('#date_from').val();
                        d.date_to = $('#date_to').val();
                    }
                },
                columns: [
                    {
                        data: null,
                        className: "text-center align-middle",
                        width: "5%",
                        render: (data, type, row, meta) => meta.row + 1
                    },
                    { data: 'no_katalog_bom', className: "align-middle" },
                    { data: 'no_costing', className: "align-middle" },
                    { data: 'nama_buyer', className: "align-middle" },
                    { data: 'style', className: "align-middle" },
                    { data: 'market', className: "align-middle" },
                    { data: 'kpno', className: "align-middle" },
                    {
                        data: 'total_cons',
                        className: "align-middle text-right",
                        render: function (data) {
                            return data ? parseFloat(data).toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2}) : '0,00';
                        }
                    },
                    {
                        data: 'id',
                        className: "text-center align-middle",
                        width: "25%",
                        orderable: false,
                        render: function (data) {
                            let editUrl = "{{ route('edit-bom', ':id') }}".replace(':id', data);
                            let printUrl = "{{ route('print-bom-pdf', ':id') }}".replace(':id', data);
                            return `
                                <div class="d-flex justify-content-center align-items-center" style="gap: 4px;">
                                    <button class="btn btn-sm btn-info py-1 px-2" style="font-size: 12px;" onclick="viewDetail(${data})" title="Detail BOM">
                                        <i class="fas fa-eye"></i> Detail
                                    </button>
                                    <a href="${editUrl}" class="btn btn-sm btn-default py-1 px-2" style="font-size: 12px;" title="Edit BOM">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="${printUrl}" target="_blank" class="btn btn-sm btn-primary py-1 px-2" style="font-size: 12px;" title="Print PDF">
                                        <i class="fas fa-file-pdf"></i> PDF
                                    </a>
                                    <button class="btn btn-sm py-1 px-2 text-white" style="font-size: 12px; background-color: #2e7d32; border-color: #2e7d32;" onclick="exportExcel(${data})" title="Export Excel">
                                        <i class="fas fa-file-excel"></i> Excel
                                    </button>
                                    <button class="btn btn-sm btn-danger py-1 px-2" style="font-size: 12px;" onclick="deleteBom(${data})" title="Hapus BOM">
                                        <i class="fas fa-trash-alt"></i> Delete
                                    </button>
                                </div>
                            `;
                        }
                    }
                ],
                paging: true,
                lengthChange: true,
                searching: true,
                ordering: true,
                info: true,
                autoWidth: false,
                responsive: true
            });

            $('#table-detail .column-search').on('keyup change clear', function() {
                let colIdx = $(this).data('column');
                if (tableDetail && tableDetail.column(colIdx).search() !== this.value) {
                    tableDetail.column(colIdx).search(this.value).draw();
                }
            });
        });

        function refreshTable() {
            tableBom.ajax.reload(null, false);
        }

        function viewDetail(id) {
            $('#modalDetail').modal('show');

            let url = "{{ route('show-detail-bom', ':id') }}".replace(':id', id);

            if ($.fn.DataTable.isDataTable('#table-detail')) {
                $('.column-search').val('');
                tableDetail.search('').columns().search('');
                tableDetail.ajax.url(url).load();
            } else {
                tableDetail = $('#table-detail').DataTable({
                    processing: true,
                    serverSide: true,
                    orderCellsTop: true,
                    ajax: url,
                    columns: [
                        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center align-middle' },
                        {
                            data: 'content_name', name: 'content_name', className: 'align-middle',
                            render: (data) => data ? data : '-'
                        },
                        { data: 'item_name', name: 'i.itemdesc', className: 'align-middle' },
                        { data: 'color_name', name: 'color_name', className: 'text-center align-middle' },
                        { data: 'size_name', name: 'size_name', className: 'text-center align-middle' },
                        {
                            data: 'qty', name: 'qty', className: 'text-center align-middle', searchable: false,
                            render: (data) => parseFloat(data)
                        },
                        {
                            data: 'unit',
                            name: 'unit',
                            className: 'text-center align-middle',
                            render: function(data, type, row) {
                                return data ? data : '-';
                            }
                        }
                    ],
                    language: { emptyTable: "Data tidak ditemukan" },
                    autoWidth: false,
                    responsive: true,
                    lengthMenu: [[5, 10, 25, -1], [5, 10, 25, "All"]],
                    pageLength: -1
                });
            }
        }

        function deleteBom(id) {
            Swal.fire({
                title: 'Hapus BOM?',
                text: "Semua detail item dari BOM ini akan dihapus secara permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Menghapus...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    let url = "{{ route('delete-bom', ':id') }}".replace(':id', id);

                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}",
                            _method: 'DELETE'
                        },
                        success: function(res) {
                            if (res.status == 200) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Terhapus!',
                                    text: res.message || 'BOM berhasil dihapus.',
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                                refreshTable();
                            } else {
                                Swal.fire('Gagal!', res.message || 'Terjadi kesalahan.', 'error');
                            }
                        },
                        error: function(xhr) {
                            let errorMsg = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan sistem';
                            Swal.fire('Error!', errorMsg, 'error');
                        }
                    });
                }
            });
        }

        function exportExcel(id) {
            Swal.fire({
                title: "Mengekspor Excel...",
                html: "Mohon tunggu sebentar, data sedang diproses...",
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            let url = "{{ route('export-excel-bom') }}";

            $.ajax({
                url: url,
                type: "GET",
                data: { id: id },
                xhrFields: {
                    responseType: 'blob'
                },
                success: function(res) {
                    Swal.close();

                    const blob = new Blob([res]);
                    const link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = "Laporan Marketing BOM Item.xlsx";
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);

                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'File Excel berhasil diekspor.',
                        timer: 1500,
                        showConfirmButton: false
                    });
                },
                error: function(err) {
                    Swal.close();
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Ekspor',
                        text: 'Terjadi kesalahan saat memproses ekspor Excel.'
                    });
                }
            });
        }
    </script>
@endsection
