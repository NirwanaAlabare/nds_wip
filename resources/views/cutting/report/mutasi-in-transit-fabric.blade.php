@extends('layouts.index')

@section('custom-link')
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
@endsection

@section('content')
    <h5 class="text-sb fw-bold"><i class="fa fa-list text-sb-secondary"></i> Mutasi In Transit Fabric</h5>

    <div class="card card-body mb-3">
        <div class="d-flex justify-content-between align-items-end flex-wrap gap-3">
            <div class="d-flex flex-wrap align-items-end gap-3">
                <div>
                    <label for="tgl-awal" class="form-label small">Tanggal Awal</label>
                    <input type="date" class="form-control form-control-sm" id="tgl-awal" name="tgl_awal" value="{{ date('Y-m-01') }}">
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
            </div>

            <div class="d-flex gap-2">
                <button type="button" class="btn btn-success btn-sm" id="exportExcel" data-title="Mutasi In Transit Fabric" data-url="{{ route('report-mutasi-in-transit-fabric-export') }}">
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
                            <th>Barcode</th>
                            <th>ID Item</th>
                            <th>Item Name</th>
                            <th>Satuan</th>
                            <th>Saldo Awal</th>
                            <th>Pemasukan</th>
                            <th>Pengeluaran</th>
                            <th>Saldo Akhir</th>
                            <th>Satuan Konv</th>
                            <th>Saldo Awal Konv</th>
                            <th>Pemasukan Konv</th>
                            <th>Pengeluaran Konv</th>
                            <th>Saldo Akhir Konv</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th colspan="4" class="text-end">Total</th>
                            <th class="text-end"></th>
                            <th class="text-end"></th>
                            <th class="text-end"></th>
                            <th class="text-end"></th>
                            <th></th>
                            <th class="text-end"></th>
                            <th class="text-end"></th>
                            <th class="text-end"></th>
                            <th class="text-end"></th>
                        </tr>
                    </tfoot>
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
        const columnKeys = [
            'id_roll', 'id_item', 'itemdesc',
            'satuan', 'saldo_awal', 'qty_out', 'qty_in', 'saldo_akhir',
            'satuan_konv', 'saldo_awal_konv', 'qty_out_konv', 'qty_in_konv', 'saldo_akhir_konv',
        ];
        const numericColumns = [4, 5, 6, 7, 9, 10, 11, 12];

        let totalsInTransit = {};

        const formatQtyInTransit =(value) => Number(value || 0).toLocaleString('id-ID', { maximumFractionDigits: 2 });

        // Thead filter
        $('#datatable thead tr').first().clone(false).appendTo('#datatable thead');
        $('#datatable thead tr:eq(1) th').each(function(index) {
            const columnTitle = $('#datatable thead tr:first th').eq(index).text().trim();

            $(this).html(`<input type="text" class="form-control form-control-sm" data-column="${index}" placeholder="${columnTitle}" aria-label="Filter ${columnTitle}">`);
        });

        const datatable = $('#datatable').DataTable({
            processing: true,
            serverSide: true,
            ordering: false,
            scrollX: true,
            pageLength: 50,
            ajax: {
                url: '{{ route('report-mutasi-in-transit-fabric') }}',
                data: (data) => {
                    data.dateFrom = $('#tgl-awal').val();
                    data.dateTo = $('#tgl-akhir').val();
                },
                dataSrc: (json) => {
                    totalsInTransit = json.totals || {};
                    return json.data;
                },
            },
            columns: columnKeys.map((key) => ({ data: key })),
            columnDefs: [
                {
                    targets: numericColumns,
                    className: 'text-end text-nowrap align-middle',
                    render: (data, type) => type === 'display' ? formatQtyInTransit(data) : data,
                },
                {
                    targets: '_all',
                    defaultContent: '-',
                    className: 'text-nowrap align-middle',
                },
            ],
            footerCallback: function() {
                const api = this.api();

                numericColumns.forEach((index) => {
                    const total = totalsInTransit[columnKeys[index]];

                    $(api.column(index).footer()).html(formatQtyInTransit(total));
                });
            },
        });

        // Debounce agar tidak request ke server setiap ketikan
        $('#datatable_wrapper').on('change', '.dataTables_scrollHead thead tr:eq(1) input', function() {
            const columnIndex = Number(this.dataset.column);
            const value = this.value;

            clearTimeout(this.filterTimeout);
            this.filterTimeout = setTimeout(() => {
                if (datatable.column(columnIndex).search() !== value) {
                    datatable.column(columnIndex).search(value).draw();
                }
            }, 500);
        });

        function dataTableReload() {
            datatable.ajax.reload();
        }

        function getColumnFilters() {
            const filters = {};

            $('#datatable_wrapper .dataTables_scrollHead thead tr:eq(1) input').each(function() {
                if (this.value) {
                    filters[columnKeys[Number(this.dataset.column)]] = this.value;
                }
            });

            return filters;
        }

        document.getElementById('exportExcel').addEventListener('click', function () {
            exportExcelGlobal(this, {
                dateFrom: $('#tgl-awal').val(),
                dateTo: $('#tgl-akhir').val(),
                filters: getColumnFilters(),
            });
        });

        $('#tgl-awal, #tgl-akhir').on('change', dataTableReload);
    </script>
@endsection
