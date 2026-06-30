@extends('layouts.index')

@section('custom-link')
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@endsection

<style>
    #table-detail thead th {
        position: sticky;
        top: 0;
        z-index: 2;
        background-color: #f8f9fa;
        box-shadow: inset 0 -1px 0 #dee2e6;
    }

    #table-detail thead tr:nth-child(2) th {
        top: 36px;
        z-index: 1;
    }

    .nav-tabs .nav-link.active {
        background-color: #f8f9fa;
        border-bottom-color: #f8f9fa;
        color: #007bff;
    }
</style>

@section('content')
<div class="card card-sb">
    <div class="card-header bg-sb">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-title fw-bold text-white mb-0">
                <i class="fas fa-check-circle"></i> Marketing Approval
            </h5>
        </div>
    </div>
    <div class="card-body bg-light">
        <!-- Nav tabs -->
        <ul class="nav nav-tabs" id="approvalTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active fw-bold px-4 py-2" id="costing-tab" data-toggle="tab" data-bs-toggle="tab" data-target="#costing" data-bs-target="#costing" type="button" role="tab" aria-controls="costing" aria-selected="true">
                    <i class="fa-solid fa-dollar-sign"></i> Approval Costing
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold px-4 py-2" id="bom-tab" data-toggle="tab" data-bs-toggle="tab" data-target="#bom" data-bs-target="#bom" type="button" role="tab" aria-controls="bom" aria-selected="false">
                    <i class="fa-solid fa-receipt"></i> Approval BOM
                </button>
            </li>
        </ul>

        <!-- Tab panes -->
        <div class="tab-content bg-white p-4 border border-top-0" id="approvalTabContent">
            <!-- TAB 1: COSTING -->
            <div class="tab-pane fade show active" id="costing" role="tabpanel" aria-labelledby="costing-tab">
                <div class="row align-items-end mb-4">
                    <div class="col-md-2">
                        <label class="small fw-bold">Tgl Awal</label>
                        <input type="date" id="tgl-awal" name="tgl_awal" class="form-control form-control-sm" value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="small fw-bold">Tgl Akhir</label>
                        <input type="date" id="tgl-akhir" name="tgl_akhir" class="form-control form-control-sm" value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-primary btn-sm fw-bold" onclick="dataTableReloadCosting()">
                            <i class="fas fa-search"></i> Filter
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover w-100" id="table-costing-approval">
                        <thead>
                            <tr class="text-center">
                                <th>No Costing</th>
                                <th>Tgl. Costing</th>
                                <th>Buyer</th>
                                <th>Brand</th>
                                <th>Style</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 2: BOM -->
            <div class="tab-pane fade" id="bom" role="tabpanel" aria-labelledby="bom-tab">
                <div class="row align-items-end mb-4">
                    <div class="col-md-2">
                        <label class="small fw-bold">Tgl Awal</label>
                        <input type="date" id="date_from" class="form-control form-control-sm" value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="small fw-bold">Tgl Akhir</label>
                        <input type="date" id="date_to" class="form-control form-control-sm" value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary btn-sm fw-bold" onclick="refreshTableBom()">
                            <i class="fas fa-search"></i> Filter
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover w-100" id="table-bom-approval">
                        <thead>
                            <tr class="text-center">
                                <th>No</th>
                                <th>No Katalog BOM</th>
                                <th>Buyer</th>
                                <th>Style</th>
                                <th>Market</th>
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
    </div>
</div>

<!-- MODAL APPROVAL COSTING -->
<div class="modal fade" id="modalApprovalCosting" tabindex="-1" role="dialog" aria-labelledby="modalApprovalCostingLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="form-approve-costing" method="POST">
                @csrf
                <div class="modal-header bg-sb text-white">
                    <h5 class="modal-title fw-bold" id="modalApprovalCostingLabel"><i class="fas fa-check"></i> Konfirmasi Approval Costing</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body bg-light">
                    <input type="hidden" name="id_costing" id="approve_id_costing">
                    <div class="text-center mb-3">
                        <h6 class="mb-1">Apakah Anda yakin akan menyetujui Costing ini?</h6>
                        <h4 class="fw-bold text-dark" id="approve_no_costing">-</h4>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-danger fw-bold" data-dismiss="modal">
                        <i class="fas fa-times-circle"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-primary fw-bold"><i class="fas fa-check-circle"></i> Approve</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL APPROVAL BOM -->
<div class="modal fade" id="modalApprovalBom" tabindex="-1" role="dialog" aria-labelledby="modalApprovalBomLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="form-approve-bom" method="POST">
                @csrf
                <div class="modal-header bg-sb text-white">
                    <h5 class="modal-title fw-bold" id="modalApprovalBomLabel"><i class="fas fa-check"></i> Konfirmasi Approval BOM</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body bg-light">
                    <input type="hidden" name="id_bom" id="approve_id_bom">
                    <div class="text-center mb-3">
                        <h6 class="mb-1">Apakah Anda yakin akan menyetujui BOM ini?</h6>
                        <h4 class="fw-bold text-dark" id="approve_no_bom">-</h4>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-danger fw-bold" data-dismiss="modal">
                        <i class="fas fa-times-circle"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-primary fw-bold"><i class="fas fa-check-circle"></i> Approve</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL DETAIL BOM -->
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
        let tableCosting;
        let tableBom;
        let tableDetail = null;

        $(document).ready(function() {
            $('[data-dismiss="modal"], [data-bs-dismiss="modal"]').on('click', function() {
                $(this).closest('.modal').modal('hide');
            });

            $('button[data-toggle="tab"], button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
                $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust().responsive.recalc();
            });

            // ==========================================
            // DATATABLES COSTING APPROVAL
            // ==========================================
            tableCosting = $('#table-costing-approval').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: '{{ route("master-costing-approval") }}',
                    data: function(d) {
                        d.tgl_awal = $('#tgl-awal').val();
                        d.tgl_akhir = $('#tgl-akhir').val();
                    }
                },
                columns: [
                    { data: 'no_costing', name: 'a.no_costing', className: 'text-center align-middle' },
                    { data: 'tgl_costing', name: 'a.created_at', className: 'text-center align-middle' },
                    { data: 'nama_buyer', name: 'a.buyer', className: 'align-middle' },
                    { data: 'brand', name: 'a.brand', className: 'align-middle' },
                    { data: 'style', name: 'a.style', className: 'align-middle' },
                    {
                        data: 'id',
                        name: 'a.id',
                        orderable: false,
                        searchable: false,
                        className: 'text-center align-middle',
                        render: function (data, type, row) {
                            let pdfUrl = "{{ route('print-costing-pdf', ':id') }}".replace(':id', data);
                            let excelUrl = "{{ route('print-excel-costing', ':id') }}".replace(':id', data);
                            let noCosting = row.no_costing || 'Unknown';

                            return `
                                <div style="display: flex; justify-content: center; gap: 5px; flex-wrap: nowrap;">
                                    <button type="button" class="btn btn-sm btn-info py-1 px-2 fw-bold" style="font-size: 12px; white-space: nowrap;" onclick="openApprovalCostingModal(${data}, '${noCosting}')">
                                        <i class="fas fa-check-circle"></i> Approve
                                    </button>

                                    <a href="${pdfUrl}" target="_blank" class="btn btn-sm btn-danger py-1 px-2" style="font-size: 12px; white-space: nowrap;">
                                        <i class="fas fa-file-pdf"></i> PDF
                                    </a>
                                    <a href="${excelUrl}" target="_blank" class="btn btn-sm btn-success py-1 px-2" style="font-size: 12px; white-space: nowrap;">
                                        <i class="fas fa-file-excel"></i> Excel
                                    </a>
                                </div>
                            `;
                        }
                    }
                ],
                order: [[2, 'desc']]
            });

            $('#form-approve-costing').on('submit', function(e) {
                e.preventDefault();
                let id = $('#approve_id_costing').val();
                let actionUrl = "{{ route('submit-costing-approval', ':id') }}".replace(':id', id);

                Swal.fire({
                    title: 'Memproses...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                $.ajax({
                    url: actionUrl,
                    type: "POST",
                    data: $(this).serialize(),
                    success: function(res) {
                        if (res.status == 200) {
                            $('#modalApprovalCosting').modal('hide');
                            Swal.fire('Berhasil!', res.message, 'success');
                            dataTableReloadCosting();
                        } else {
                            Swal.fire('Gagal!', res.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error!', 'Terjadi kesalahan sistem server.', 'error');
                    }
                });
            });

            // ==========================================
            // DATATABLES BOM APPROVAL
            // ==========================================
            tableBom = $('#table-bom-approval').DataTable({
                processing: true,
                serverSide: false,
                ajax: {
                    url: '{{ route("master-bom-approval") }}',
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
                    { data: 'nama_buyer', className: "align-middle" },
                    { data: 'style', className: "align-middle" },
                    { data: 'market', className: "align-middle" },
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
                        width: "15%",
                        orderable: false,
                        render: function (data, type, row) {
                            let noBom = row.no_katalog_bom || 'Unknown';

                            return `
                                <div class="d-flex justify-content-center align-items-center">
                                    <button class="btn btn-sm btn-info mr-1 py-1 px-2 fw-bold" style="font-size: 12px;" onclick="viewDetail(${data})">
                                        <i class="fas fa-eye"></i> Detail
                                    </button>
                                    <button type="button" class="btn btn-sm btn-success py-1 px-2 fw-bold" style="font-size: 12px; white-space: nowrap;" onclick="openApprovalBomModal(${data}, '${noBom}')">
                                        <i class="fas fa-check-circle"></i> Approve
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

            $('#form-approve-bom').on('submit', function(e) {
                e.preventDefault();
                let id = $('#approve_id_bom').val();
                let actionUrl = "{{ route('submit-bom-approval', ':id') }}".replace(':id', id);

                Swal.fire({
                    title: 'Memproses...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                $.ajax({
                    url: actionUrl,
                    type: "POST",
                    data: $(this).serialize(),
                    success: function(res) {
                        if (res.status == 200) {
                            $('#modalApprovalBom').modal('hide');
                            Swal.fire('Berhasil!', res.message, 'success');
                            refreshTableBom();
                        } else {
                            Swal.fire('Gagal!', res.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error!', 'Terjadi kesalahan sistem server.', 'error');
                    }
                });
            });
        });

        function dataTableReloadCosting() {
            tableCosting.ajax.reload(null, false);
        }

        function refreshTableBom() {
            tableBom.ajax.reload(null, false);
        }

        function openApprovalCostingModal(id, noCosting) {
            $('#approve_id_costing').val(id);
            $('#approve_no_costing').text(noCosting);
            $('#modalApprovalCosting').modal('show');
        }

        function openApprovalBomModal(id, noBom) {
            $('#approve_id_bom').val(id);
            $('#approve_no_bom').text(noBom);
            $('#modalApprovalBom').modal('show');
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
    </script>
@endsection
