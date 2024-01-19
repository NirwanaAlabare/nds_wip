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
                            <th>Act</th>
                            <th>Trolley</th>
                            <th>WS Number</th>
                            <th>Style</th>
                            <th>Color</th>
                            <th>Qty</th>
                            <th>Send</th>
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
    <script src="{{ asset('plugins/datatables-rowsgroup/dataTables.rowsGroup.js') }}"></script>

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
                    name: 'id',
                    data: 'id'
                },
                {
                    data: 'nama_trolley',
                },
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
                    data: 'qty',
                },
                {
                    data: 'id'
                },
            ],
            rowsGroup: [
                // Always the array (!) of the column-selectors in specified order to which rows groupping is applied
                // (column-selector could be any of specified in https://datatables.net/reference/type/column-selector)
                0,
                1,
                6
            ],
            columnDefs: [
                {
                    targets: [0],
                    className: "align-middle",
                    render: (data, type, row, meta) => {
                        return `
                            <div class='d-flex gap-1 justify-content-center'>
                                <a class='btn btn-success btn-sm' href='{{ route('allocate-this-trolley') }}/`+row.id+`'>
                                    <i class='fa fa-plus'></i>
                                </a>
                            </div>
                        `;
                    }
                },
                {
                    targets: [1],
                    className: "align-middle",
                },
                {
                    targets: [6],
                    className: "align-middle",
                    render: (data, type, row, meta) => {
                        return `
                            <div class='d-flex gap-1 justify-content-center'>
                                <a href='{{ route('send-trolley-stock') }}/`+data+`' class='btn btn-primary btn-sm'>
                                    <i class='fa fa-share'></i>
                                </a>
                            </div>
                        `;
                    }
                }
            ],
        });

        function datatableReload() {
            datatable.ajax.reload();
        }
    </script>
@endsection
