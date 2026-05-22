@extends('layouts.index')

@section('custom-link')
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
@endsection

@section('content')
    <div class="d-flex justify-content-between mb-3">
        <h5 class="fw-bold text-sb"><i class="fa-solid fa-triangle-exclamation"></i> Check Orphan Transaction</h5>
        <a href="{{ route('dc-tools') }}" class="btn btn-primary btn-sm px-1 py-1"><i class="fas fa-reply"></i> Kembali ke Tools</a>
    </div>

    <div class="card card-sb mb-3">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0">Filter</h5>
        </div>
        <div class="card-body">
            <div class="row align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Jenis Transaksi</label>
                    <select class="form-control" id="transaction_type">
                        <option value="dc">DC In</option>
                        <option value="secondary_inhouse_in">Secondary Inhouse In</option>
                        <option value="secondary_inhouse">Secondary Inhouse</option>
                        <option value="secondary_in">Secondary In</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tanggal Dari</label>
                    <input type="date" class="form-control" id="date_from">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tanggal Sampai</label>
                    <input type="date" class="form-control" id="date_to">
                </div>
                <div class="col-md-3">
                    <button class="btn btn-sb w-100" onclick="loadData()">
                        <i class="fa fa-search"></i> Cari
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-sb">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title fw-bold mb-0">Transaksi Tanpa Stocker</h5>
            <span class="badge bg-danger" id="total-badge">0 data</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm" id="datatable" style="width:100%">
                    <thead>
                        <tr class="text-center">
                            <th>No</th>
                            <th>ID QR Stocker</th>
                            <th>Tgl Transaksi</th>
                            <th>WS</th>
                            <th>Color</th>
                            <th>Panel</th>
                            <th>Size</th>
                            <th>Part</th>
                            <th>Qty</th>
                            <th>Ratio</th>
                            <th>Group Stocker</th>
                            <th>Range</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
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

    <script>
        var table;

        $(document).ready(function () {
            let today = new Date();
            let past30 = new Date(today);
            past30.setDate(today.getDate() - 30);

            $('#date_to').val(today.toISOString().slice(0, 10));
            $('#date_from').val(past30.toISOString().slice(0, 10));

            initTable();
        });

        function initTable() {
            if (table) {
                table.destroy();
            }

            table = $('#datatable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('check-orphan-transaction-list') }}',
                    type: 'POST',
                    data: function (d) {
                        d._token           = '{{ csrf_token() }}';
                        d.transaction_type = $('#transaction_type').val();
                        d.date_from        = $('#date_from').val();
                        d.date_to          = $('#date_to').val();
                    },
                    dataSrc: function (json) {
                        $('#total-badge').text(json.recordsTotal + ' data');
                        return json.data;
                    }
                },
                columns: [
                    { data: null, render: function (d, t, r, m) { return m.row + 1; }, orderable: false, searchable: false, className: 'text-center' },
                    { data: 'id_qr_stocker', defaultContent: '-' },
                    { data: 'tgl_trans', className: 'text-center text-nowrap', render: function (d) {
                        return d ? formatDateTime(d) : '-';
                    }},
                    { data: 'act_costing_ws', defaultContent: '-', className: 'text-center' },
                    { data: 'color', defaultContent: '-', className: 'text-center' },
                    { data: 'panel', defaultContent: '-', className: 'text-center' },
                    { data: 'size', defaultContent: '-', className: 'text-center' },
                    { data: 'part', defaultContent: '-' },
                    { data: 'qty', defaultContent: '-', className: 'text-end' },
                    { data: 'ratio', defaultContent: '-', className: 'text-center' },
                    { data: 'group_stocker', defaultContent: '-', className: 'text-center' },
                    { data: 'stocker_range', defaultContent: '-', className: 'text-center text-nowrap' },
                    { data: 'created_at', className: 'text-center text-nowrap', render: function (d) {
                        return d ? formatDateTime(d) : '-';
                    }},
                ],
                order: [[2, 'desc']],
                pageLength: 25,
                language: { processing: '<i class="fa fa-spinner fa-spin"></i> Memuat data...' }
            });
        }

        function loadData() {
            if (table) {
                table.ajax.reload();
            } else {
                initTable();
            }
        }
    </script>
@endsection
