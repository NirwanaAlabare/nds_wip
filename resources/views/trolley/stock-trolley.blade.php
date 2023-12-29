@extends('layouts.index')

@section('custom-link')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
@endsection

@section('content')
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold">
                <i class="fas fa-dolly-flatbed"></i> Trolley
            </h5>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <a href="{{ route('allocate-trolley') }}" class="btn btn-success btn-sm">
                    <i class="fa fa-plus"></i> Alokasi Trolley
                </a>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered w-100" id="datatable">
                    <thead>
                        <tr>
                            <th>WS Number</th>
                            <th>Style</th>
                            <th>Color</th>
                            <th>Trolley</th>
                            <th>Qty</th>
                            <th>Act</th>
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
    <!-- DataTables  & Plugins -->
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>

    <script>
        let datatable = $("#datatable").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('stock-trolley') }}',
            },
            columns: [
                {
                    data: 'act_costing_ws',
                },
                {
                    data: 'style',
                },
                {
                    data: 'color',
                },
                {
                    data: 'nama_trolley',
                },
                {
                    data: 'qty',
                },
                {
                    data: 'id'
                },
            ],
            columnDefs: [
                {
                    targets: [3],
                    className: "align-middle",
                    render: (data, type, row, meta) => {
                        return `
                            <div class='d-flex gap-1 justify-content-center'>
                                <a class='btn btn-primary btn-sm' data-bs-toggle="modal" data-bs-target="#editMasterTrolleyModal" onclick='editData(` + JSON.stringify(row) + `, "editMasterTrolleyModal", [{"function" : "datatableReload()"}]);'>
                                    <i class='fa fa-edit'></i>
                                </a>
                                <a class='btn btn-danger btn-sm' data='`+JSON.stringify(row)+`' data-url='{{ route("destroy-trolley") }}/`+row['id']+`' onclick='deleteData(this);'>
                                    <i class='fa fa-trash'></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-secondary" data='`+JSON.stringify(row)+`' data-url='{{ route("print-trolley") }}/`+row['id']+`' onclick="printTrolley(this);">
                                    <i class="fa fa-print fa-s"></i>
                                </button>
                            </div>
                        `;
                    }
                },
            ]
        });

        function datatableReload() {
            datatable.ajax.reload();
        }
    </script>
@endsection
