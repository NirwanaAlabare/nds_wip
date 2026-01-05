@extends('layouts.index')

@section('custom-link')
    <!-- Your Custom Style for styling your front-end -->

    <!-- DataTables -->
    <link href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}" rel="stylesheet"/>
    <link href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}" rel="stylesheet"/>
    <link href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}" rel="stylesheet"/>
@endsection

@section('content')
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title">Output Sewing Closing</h5>
        </div>
        <div class="card-body">
            <div class="d-flex">
                <div>
                    <label class="form-label">From</label>
                    <input type="date" class="form-control" id="dateFrom" name="dateFrom" value="{{ date('Y-m-d') }}">
                </div>
                <div>
                    <label class="form-label">To</label>
                    <input type="date" class="form-control" id="dateTo" name="dateTo" value="{{ date('Y-m-d') }}">
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="output-sewing-lock-table">
                    <thead>
                        <th>Tanggal</th>
                        <th>Status</th>
                        <th>Closed By</th>
                        <th>Closed At</th>
                    </thead>
                    <tbody></tbody>
                </table> 
            </div>
        </div>
    </div>
@endsection

@section('custom-script')
    <!-- Your Custom Javascript for front-end logics -->

    <!-- DataTables  & Plugins -->
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>

    <script>
        let outputSewingLockTable = $("#output-sewing-lock-table").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            pageLength: 100,
            scrollX: '400px',
            scrollY: '400px',
            ajax: {
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route('line-wip') }}',
                dataType: 'json',
                dataSrc: 'data',
                data: function(d) {
                    d.dateFrom = $('#dateFrom').val();
                    d.dateTo = $('#dateTo').val();
                },
            },
            columns: [
                {data: "tanggal"},
                {data: "locked"},
                {data: "locked_by_username"},
                {data: "locked_at"},
            ],
            columnDefs: [
                {
                    targets: [1],
                    className: "text-nowrap",
                    render: (data, type, row, meta) => (data ? 'Closed' : 'Available')
                },
            ]
        });
    </script>
@endsection