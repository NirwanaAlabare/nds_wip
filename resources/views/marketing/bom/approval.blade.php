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
</style>

@section('content')
<div class="card card-sb">
    <div class="card-header bg-sb">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-title fw-bold text-white mb-0">
                <i class="fas fa-check-circle"></i> Approval Data BOM
            </h5>
        </div>
    </div>
    <div class="card-body">
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
                <button class="btn btn-primary btn-sm fw-bold" onclick="refreshTable()">
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

<div class="modal fade" id="modalApproval" tabindex="-1" role="dialog" aria-labelledby="modalApprovalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="form-approve-bom" method="POST">
                @csrf
                <div class="modal-header bg-sb text-white">
                    <h5 class="modal-title fw-bold" id="modalApprovalLabel"><i class="fas fa-check"></i> Konfirmasi Approval</h5>
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
           $('[data-dismiss="modal"], [data-bs-dismiss="modal"]').on('click', function() {
                $(this).closest('.modal').modal('hide');
            });

            tableBom = $('#table-bom-approval').DataTable({
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
                                    <button type="button" class="btn btn-sm btn-success py-1 px-2 fw-bold" style="font-size: 12px; white-space: nowrap;" onclick="openApprovalModal(${data}, '${noBom}')">
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
                            $('#modalApproval').modal('hide');
                            Swal.fire('Berhasil!', res.message, 'success');
                            refreshTable();
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

        function refreshTable() {
            tableBom.ajax.reload(null, false);
        }

        function openApprovalModal(id, noBom) {
            $('#approve_id_bom').val(id);
            $('#approve_no_bom').text(noBom);
            $('#notes_approval').val('');
            $('#modalApproval').modal('show');
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
