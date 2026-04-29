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
                    <i class="fas fa-list"></i> List Data Costing
                </h5>
            </div>
        </div>

        <div class="card-body">
            <div class="mb-3">
                <a href="{{ route('create-costing') }}" class="btn btn-outline-primary">
                    <i class="fas fa-plus"></i> Create Costing
                </a>
            </div>
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
                <table class="table table-bordered table-hover w-100" id="table-costing">
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
                    { data: 'nama_buyer', name: 'a.buyer', className: 'align-middle' },
                    { data: 'brand', name: 'a.brand', className: 'align-middle' },
                    { data: 'style', name: 'a.style', className: 'align-middle' },
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
    </script>
@endsection
