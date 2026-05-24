@extends('layouts.index')

@section('custom-link')
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
@endsection

@section('content')
    <div class="d-flex justify-content-between mb-3">
        <h5 class="fw-bold text-sb"><i class="fa-solid fa-filter"></i> Check Stocker by WS / Color / Size</h5>
        <a href="{{ route('stocker-tools') }}" class="btn btn-primary btn-sm px-1 py-1"><i class="fas fa-reply"></i> Kembali ke Tools</a>
    </div>

    <div class="card card-sb mb-3">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0">Filter</h5>
        </div>
        <div class="card-body">
            <div class="row align-items-end">
                <div class="col-md-3">
                    <label class="form-label">No. WS</label>
                    <input type="text" class="form-control" id="filter_ws" placeholder="Contoh: WS/2024/001">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Color</label>
                    <input type="text" class="form-control" id="filter_color" placeholder="Contoh: NAVY">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Size</label>
                    <input type="text" class="form-control" id="filter_size" placeholder="Contoh: M">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Additional</label>
                    <select class="form-control" id="filter_additional">
                        <option value="">Semua</option>
                        <option value="Y">Additional</option>
                        <option value="N">Non-Additional</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-sb w-100" onclick="loadData()">
                        <i class="fa fa-search"></i> Cari
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-sb">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title fw-bold mb-0">Stocker (urut No. Cut)</h5>
            <span class="badge bg-primary" id="total-badge">0 data</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm" id="datatable" style="width:100%">
                    <thead>
                        <tr class="text-center">
                            <th>No</th>
                            <th>Jml</th>
                            <th>WS</th>
                            <th>Color</th>
                            <th>Size</th>
                            <th>No. Form</th>
                            <th>No. Cut</th>
                            <th>Group</th>
                            <th>Ratio</th>
                            <th>Range Awal</th>
                            <th>Range Akhir</th>
                            <th>Qty Ply</th>
                            <th>Qty Mod</th>
                            <th>Cancel</th>
                            <th>Notes</th>
                            <th>Created At</th>
                        </tr>
                        <tr id="filter-row">
                            <th></th>
                            <th></th>
                            <th><input type="text" class="form-control form-control-sm col-filter" data-col="2" placeholder="Cari..."></th>
                            <th><input type="text" class="form-control form-control-sm col-filter" data-col="3" placeholder="Cari..."></th>
                            <th><input type="text" class="form-control form-control-sm col-filter" data-col="4" placeholder="Cari..."></th>
                            <th><input type="text" class="form-control form-control-sm col-filter" data-col="5" placeholder="Cari..."></th>
                            <th><input type="text" class="form-control form-control-sm col-filter" data-col="6" placeholder="Cari..."></th>
                            <th><input type="text" class="form-control form-control-sm col-filter" data-col="7" placeholder="Cari..."></th>
                            <th><input type="text" class="form-control form-control-sm col-filter" data-col="8" placeholder="Cari..."></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th>
                                <select class="form-control form-control-sm col-filter" data-col="13">
                                    <option value="">Semua</option>
                                    <option value="Y">Y</option>
                                    <option value="N">N</option>
                                </select>
                            </th>
                            <th><input type="text" class="form-control form-control-sm col-filter" data-col="14" placeholder="Cari..."></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
            <div class="mt-2">
                <span class="badge bg-danger">&nbsp;&nbsp;</span>
                <small class="text-muted ms-1">Range tidak berurutan dengan baris sebelumnya</small>
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
            initTable();
        });

        function initTable() {
            if (table) table.destroy();

            table = $('#datatable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('check-stocker-by-filter-list') }}',
                    type: 'POST',
                    data: function (d) {
                        d._token          = '{{ csrf_token() }}';
                        d.act_costing_ws  = $('#filter_ws').val().trim();
                        d.color           = $('#filter_color').val().trim();
                        d.size            = $('#filter_size').val().trim();
                        d.additional      = $('#filter_additional').val();
                    },
                    dataSrc: function (json) {
                        $('#total-badge').text(json.recordsTotal + ' data');
                        return json.data;
                    }
                },
                columns: [
                    { data: null, render: function (d, t, r, m) { return m.row + 1; }, orderable: false, searchable: false, className: 'text-center' },
                    { data: 'stocker_count', defaultContent: '-', className: 'text-center', searchable: false },
                    { data: 'act_costing_ws', defaultContent: '-', className: 'text-center' },
                    { data: 'color', defaultContent: '-', className: 'text-center' },
                    { data: 'size', defaultContent: '-', className: 'text-center' },
                    { data: 'no_form', defaultContent: '-' },
                    { data: 'no_cut', defaultContent: '-', className: 'text-center' },
                    { data: 'group_stocker', defaultContent: '-', className: 'text-center' },
                    { data: 'ratio', defaultContent: '-', className: 'text-center' },
                    { data: 'range_awal', defaultContent: '-', className: 'text-end', searchable: false },
                    { data: 'range_akhir', defaultContent: '-', className: 'text-end', searchable: false },
                    { data: 'qty_ply', defaultContent: '-', className: 'text-end', searchable: false },
                    { data: 'qty_ply_mod', defaultContent: '-', className: 'text-end', searchable: false },
                    { data: 'cancel', defaultContent: '-', className: 'text-center', render: function (d) {
                        if (!d) return '<span class="badge bg-secondary">-</span>';
                        return d == 'Y' ? '<span class="badge bg-danger">Y</span>' : '<span class="badge bg-success">N</span>';
                    }},
                    { data: 'notes', defaultContent: '-' },
                    { data: 'created_at', className: 'text-center text-nowrap', searchable: false, render: function (d) {
                        return d ? formatDateTime(d) : '-';
                    }},
                ],
                order: [[6, 'asc']],
                pageLength: 50,
                language: { processing: '<i class="fa fa-spinner fa-spin"></i> Memuat data...' },
                drawCallback: function () {
                    var prevRangeAkhir = null;
                    this.api().rows({ page: 'current' }).every(function () {
                        var data = this.data();
                        var row = this.node();
                        $(row).removeClass('table-danger');
                        if (prevRangeAkhir !== null && data.range_awal !== null) {
                            if (parseInt(prevRangeAkhir) + 1 !== parseInt(data.range_awal)) {
                                $(row).addClass('table-danger');
                            }
                        }
                        prevRangeAkhir = data.range_akhir;
                    });
                },
                initComplete: function () {
                    $('#filter-row .col-filter').on('keyup change', function () {
                        table.column($(this).data('col')).search(this.value).draw();
                    });
                }
            });
        }

        function loadData() {
            if (table) {
                table.ajax.reload();
            } else {
                initTable();
            }
        }

        // Enter key on filter inputs
        $('#filter_ws, #filter_color, #filter_size').on('keypress', function (e) {
            if (e.which === 13) loadData();
        });
    </script>
@endsection
