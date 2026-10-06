@extends('layouts.index', ["containerFluid" => false])

@section('custom-link')
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
@endsection

@section('content')
    <h5 class="text-sb fw-bold"><i class="fa fa-list text-sb-secondary"></i> Riwayat Switching Adjustment</h5>

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
                <a href="{{ route('switching') }}" type="button" class="btn btn-sb btn-sm" target="_blank">
                    <i class="fa fa-plus"></i> Buat Adjustment Switching
                </a>
                <button type="button" class="btn btn-success btn-sm" id="exportExcel" data-title="Riwayat Switching Adjustment" data-url="{{ route('report-switching-adjustment-export') }}">
                    <i class="fa fa-file-excel"></i> Export
                </button>
            </div>
        </div>
    </div>

    <div class="card card-sb">
        <div class="card-body">
            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-hover table-sm w-100">
                    <thead>
                        <tr>
                            <th>Tgl Saldo Asal</th><th>WS Asal</th><th>Buyer Asal</th><th>Style Asal</th><th>Color Asal</th><th>Size Asal</th><th>Panel Asal</th><th>Part Asal</th><th>Qty Asal</th>
                            <th>Tgl Saldo Tujuan</th><th>WS Tujuan</th><th>Buyer Tujuan</th><th>Style Tujuan</th><th>Color Tujuan</th><th>Size Tujuan</th><th>Panel Tujuan</th><th>Part Tujuan</th><th>Qty Tujuan</th>
                            <th>Status</th>
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
        // Default tanggal awal 7 hari lalu. Harus diisi sebelum DataTable dibuat, supaya request pertama
        // sudah memakai tanggal ini (kalau kosong, controller memakai hari ini saja).
        const sevenDaysAgo = new Date();
        sevenDaysAgo.setDate(sevenDaysAgo.getDate() - 7);
        $('#tgl-awal').val(sevenDaysAgo.toISOString().slice(0, 10));

        const selectFilterColumns = {
            18: [
                { value: '', label: 'Semua' },
                { value: 'Y', label: 'Active' },
                { value: 'N', label: 'Cancel' },
            ],
        };

        $('#datatable thead tr').first().clone(false).appendTo('#datatable thead');
        $('#datatable thead tr:eq(1) th').each(function(index) {
            const columnTitle = $('#datatable thead tr:first th').eq(index).text().trim();

            if (selectFilterColumns[index]) {
                const options = selectFilterColumns[index]
                    .map((option) => `<option value="${option.value}">${option.label}</option>`)
                    .join('');

                $(this).html(`<select class="form-select form-select-sm" data-column="${index}" aria-label="Filter ${columnTitle}">${options}</select>`);
                return;
            }

            $(this).html(`<input type="text" class="form-control form-control-sm" data-column="${index}" placeholder="Filter ${columnTitle}" aria-label="Filter ${columnTitle}">`);
        });

        const datatable = $('#datatable').DataTable({
            processing: true,
            serverSide: true,
            ordering: false,
            scrollX: true,
            pageLength: 50,
            ajax: {
                url: '{{ route('report-switching-adjustment') }}',
                data: (data) => {
                    data.dateFrom = $('#tgl-awal').val();
                    data.dateTo = $('#tgl-akhir').val();
                },
            },
            columns: [
                { data: 'from_tgl_saldo' }, { data: 'from_no_ws' }, { data: 'from_buyer' }, { data: 'from_style' },
                { data: 'from_color' }, { data: 'from_size' }, { data: 'from_panel' }, { data: 'from_part' }, { data: 'from_qty' },
                { data: 'tgl_saldo' }, { data: 'no_ws' }, { data: 'buyer' }, { data: 'style' },
                { data: 'color' }, { data: 'size' }, { data: 'panel' }, { data: 'part' }, { data: 'qty' },
                { data: 'status' },
            ],
            columnDefs: [
                {
                    targets: [8, 17],
                    className: 'text-end',
                    render: (data) => data === null || data === undefined || data === '' ? '-' : Number(data),
                },
                {
                    targets: [18],
                    className: 'text-center',
                    render: (data) => data === 'Y'
                        ? '<span class="badge bg-success">Aktif</span>'
                        : '<span class="badge bg-secondary">Cancel</span>',
                },
                {
                    targets: '_all',
                    defaultContent: '-',
                    className: 'text-nowrap align-middle',
                },
            ],
        });

        $('#datatable_wrapper').on('keyup change', '.dataTables_scrollHead thead tr:eq(1) input, .dataTables_scrollHead thead tr:eq(1) select', function() {
            const columnIndex = Number(this.dataset.column);

            if (datatable.column(columnIndex).search() !== this.value) {
                datatable.column(columnIndex).search(this.value).draw();
            }
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
