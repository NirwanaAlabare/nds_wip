@extends('layouts.index', ["containerFluid" => false])

@section('custom-link')
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
@endsection

@section('content')
    <h5 class="text-sb fw-bold">Riwayat Switching Cutting</h5>

    <div class="card card-body mb-3">
        <div class="d-flex justify-content-between align-items-end flex-wrap gap-3">
            <div class="d-flex flex-wrap align-items-end gap-3">
                <div>
                    <label for="tgl-awal" class="form-label small">Tanggal Awal</label>
                    <input type="date" class="form-control form-control-sm" id="tgl-awal" name="tgl_awal">
                </div>
                <div>
                    <label for="tgl-akhir" class="form-label small">Tanggal Akhir</label>
                    <input type="date" class="form-control form-control-sm" id="tgl-akhir" name="tgl_akhir" value="{{ date('Y-m-d') }}">
                </div>
                <div>
                    <button type="button" class="btn btn-primary btn-sm" onclick="dataTableReload()">
                        <i class="fa fa-search"></i> Cari
                    </button>
                </div>
                <div>
                    <button type="button" class="btn btn-sb-secondary btn-sm" data-bs-toggle="tooltip" data-bs-title="Refresh Data" onclick="dataTableReload()">
                        <i class="fa fa-rotate"></i>
                    </button>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="button" class="btn btn-success btn-sm" id="exportExcel" data-title="Report Log Switching Cutting" data-url="{{ route('export-cutting-switching') }}">
                    <i class="fa fa-file-excel"></i> Export
                </button>

                <a href="{{ route('create-cutting-switching') }}" type="button" class="btn btn-sb btn-sm">
                    <i class="fa fa-plus"></i> Buat Switching
                </a>
            </div>
        </div>
    </div>

    <div class="card card-sb">
        <div class="card-body">
            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-hover table-sm w-100">
                    <thead>
                        <tr>
                            <th>Group Roll</th><th>Tanggal Asal</th><th>No. Form Asal</th><th>WS Asal</th><th>Style Asal</th><th>Color Asal</th><th>Panel Asal</th><th>Size Asal</th>
                            <th>Tanggal Tujuan</th><th>No. Form Tujuan</th><th>WS Tujuan</th><th>Style Tujuan</th><th>Color Tujuan</th><th>Panel Tujuan</th><th>Size Tujuan</th>
                            <th>Qty Transfer</th><th>Status</th><th>Created By</th><th>Created At</th><th>Updated At</th>
                        </tr>
                    </thead>
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
        $(document).ready(() => {
            const sevenDaysAgo = new Date();
            sevenDaysAgo.setDate(sevenDaysAgo.getDate() - 7);
            $('#tgl-awal').val(sevenDaysAgo.toISOString().slice(0, 10));
        });

        const datatable = $('#datatable').DataTable({
            processing: true,
            serverSide: true,
            ordering: false,
            scrollX: true,
            pageLength: 50,
            ajax: {
                url: '{{ route('cutting-switching') }}',
                data: (data) => {
                    data.dateFrom = $('#tgl-awal').val();
                    data.dateTo = $('#tgl-akhir').val();
                },
            },
            columns: [
                { data: 'group_roll_asal' }, { data: 'tanggal_form_asal' }, { data: 'no_form_asal' }, { data: 'ws_asal' },
                { data: 'styleno_asal' }, { data: 'color_asal' }, { data: 'panel_asal' }, { data: 'size_asal' },
                { data: 'tanggal_form_tujuan' }, { data: 'no_form_tujuan' }, { data: 'ws_tujuan' }, { data: 'styleno_tujuan' },
                { data: 'color_tujuan' }, { data: 'panel_tujuan' }, { data: 'size_tujuan' }, { data: 'qty_transfer', className: 'text-end' },
                { data: 'is_active', className: 'text-center' }, { data: 'created_by' }, { data: 'created_at' }, { data: 'updated_at' },
            ],
            columnDefs: [
                {
                    targets: [16],
                    render: (data) => Number(data) === 1
                        ? '<span class="badge bg-success">Aktif</span>'
                        : '<span class="badge bg-secondary">Tidak Aktif</span>',
                },
                {
                    targets: [18, 19],
                    render: (data) => data ? formatDateTime(data) : '-',
                },
                {
                    targets: '_all',
                    defaultContent: '-',
                    className: 'text-nowrap align-middle',
                },
            ],
        });

        function dataTableReload() {
            datatable.ajax.reload();
        }

        document.getElementById('exportExcel').addEventListener('click', function () {
            exportExcelGlobal(this, {
                dateFrom: $('#tgl-awal').val(),
                dateTo: $('#tgl-akhir').val(),
                keyword: datatable.search(),
            });
        });

        $('#tgl-awal, #tgl-akhir').on('change', dataTableReload);
    </script>
@endsection
