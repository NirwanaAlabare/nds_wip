@extends('layouts.index')

@section('custom-link')
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">

    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@endsection

@section('content')
    <div class="card card-sb">
        <div class="card-header bg-sb">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title fw-bold text-white mb-0">
                    <i class="fas fa-check-circle"></i> Approval Data Costing
                </h5>
            </div>
        </div>

        <div class="card-body">
            <div class="d-flex align-items-end gap-3 mb-4">
                <div class="mr-3">
                    <label class="form-label mb-1"><small><b>Tgl Awal</b></small></label>
                    <input type="date" class="form-control form-control-sm " id="tgl-awal" name="tgl_awal" value="{{ date('Y-m-d') }}">
                </div>
                <div class="mr-3">
                    <label class="form-label mb-1"><small><b>Tgl Akhir</b></small></label>
                    <input type="date" class="form-control form-control-sm" id="tgl-akhir" name="tgl_akhir" value="{{ date('Y-m-d') }}">
                </div>
                <div>
                    <button type="button" class="btn btn-primary btn-sm fw-bold" onclick="dataTableReload()">
                        <i class="fas fa-search"></i> Cari
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
    </div>

    <div class="modal fade" id="modalApproval" tabindex="-1" role="dialog" aria-labelledby="modalApprovalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="form-approve-costing" method="POST">
                    @csrf
                    <div class="modal-header bg-sb text-white">
                        <h5 class="modal-title fw-bold" id="modalApprovalLabel"><i class="fas fa-check"></i> Konfirmasi Approval</h5>
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
@endsection

@section('custom-script')
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>

    <script>
        let table;

        $(document).ready(function() {

            $('[data-dismiss="modal"], [data-bs-dismiss="modal"]').on('click', function() {
                $(this).closest('.modal').modal('hide');
            });

            table = $('#table-costing-approval').DataTable({
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
                                    <button type="button" class="btn btn-sm btn-info py-1 px-2 fw-bold" style="font-size: 12px; white-space: nowrap;" onclick="openApprovalModal(${data}, '${noCosting}')">
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
                            $('#modalApproval').modal('hide');
                            Swal.fire('Berhasil!', res.message, 'success');
                            dataTableReload();
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

        function dataTableReload() {
            table.ajax.reload(null, false);
        }

        function openApprovalModal(id, noCosting) {
            $('#approve_id_costing').val(id);
            $('#approve_no_costing').text(noCosting);
            $('#notes_approval').val('');
            $('#modalApproval').modal('show');
        }
    </script>
@endsection
