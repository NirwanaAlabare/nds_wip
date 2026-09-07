@extends('layouts.index')

@section('custom-link')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
@endsection

@section('content')
    <div class="card">
        <div class="card-header bg-sb text-light">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-list fa-sm"></i> List Part Detail</h5>
        </div>
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-end mb-3 gap-3 flex-wrap">
                <div class="d-flex align-items-end gap-2 flex-wrap">
                    <div>
                        <label class="form-label small">Tanggal Awal</label>
                        <input type="date" class="form-control form-control-sm" id="filter-date-from" value="{{ date("Y-m-d", strtotime("-30 days")) }}" onchange="datatablePartDetailReload()">
                    </div>
                    <div>
                        <label class="form-label small">Tanggal Akhir</label>
                        <input type="date" class="form-control form-control-sm" id="filter-date-to" value="{{ date("Y-m-d") }}" onchange="datatablePartDetailReload()">
                    </div>
                    <button class="btn btn-primary btn-sm" onclick="datatablePartDetailReload()"><i class="fa fa-search"></i></button>
                </div>
                <div class="d-flex align-items-end gap-1">
                    <button class="btn btn-success btn-sm" data-bs-toggle="tooltip" data-bs-title="Export Excel" onclick="exportPartDetail(this)" data-url="{{ route('export-part-detail-list') }}" data-title="Part Detail List">
                        <i class="fas fa-file-excel"></i> Export
                    </button>
                    <button class="btn btn-sb-secondary btn-sm" data-bs-toggle="tooltip" data-bs-title="Refresh Data" onclick="datatablePartDetailReload()"><i class="fa fa-rotate"></i></button>
                </div>
            </div>
            <div class="table-responsive">
                <table id="datatable-part-detail" class="table table-bordered table w-100">
                    <thead>
                        <tr>
                            <th>No. WS</th>
                            <th>Style</th>
                            <th>Color</th>
                            <th>Panel</th>
                            <th>Status Panel</th>
                            <th>Part</th>
                            <th>Status Part</th>
                            <th>Proses</th>
                            <th>Created At</th>
                        </tr>
                        <tr class="column-filter-row">
                            <th><input type="text" class="form-control form-control-sm column-filter" data-column="0" placeholder="Cari..."></th>
                            <th><input type="text" class="form-control form-control-sm column-filter" data-column="1" placeholder="Cari..."></th>
                            <th><input type="text" class="form-control form-control-sm column-filter" data-column="2" placeholder="Cari..."></th>
                            <th><input type="text" class="form-control form-control-sm column-filter" data-column="3" placeholder="Cari..."></th>
                            <th><input type="text" class="form-control form-control-sm column-filter" data-column="4" placeholder="Cari..."></th>
                            <th><input type="text" class="form-control form-control-sm column-filter" data-column="5" placeholder="Cari..."></th>
                            <th>
                                <select class="form-select form-select-sm column-filter" data-column="6">
                                    <option value="">Semua</option>
                                    <option value="main">Main</option>
                                    <option value="complement">Complement</option>
                                    <option value="regular">Reqgular</option>
                                </select>
                            </th>
                            <th><input type="text" class="form-control form-control-sm column-filter" data-column="7" placeholder="Cari..."></th>
                            <th><input type="text" class="form-control form-control-sm column-filter" data-column="8" placeholder="Cari..."></th>
                        </tr>
                    </thead>
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
        // Dipakai bersama oleh datatable & export supaya isinya selalu sama
        function currentFilter() {
            return {
                dateFrom: $("#filter-date-from").val(),
                dateTo: $("#filter-date-to").val(),
                ws: $("#filter-ws").val(),
                color: $("#filter-color").val(),
                panel: $("#filter-panel").val(),
                part: $("#filter-part").val(),
                part_status: $("#filter-part-status").val(),
            };
        }

        let datatablePartDetail = $("#datatable-part-detail").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('part-detail-list') }}',
                data: function(d) {
                    Object.assign(d, currentFilter());
                }
            },
            columns: [
                { data: 'ws', defaultContent: '-' },
                { data: 'styleno', defaultContent: '-' },
                { data: 'color', defaultContent: '-' },
                { data: 'panel', defaultContent: '-' },
                { data: 'panel_status', defaultContent: '-' },
                { data: 'nama_part', defaultContent: '-' },
                {
                    data: 'part_status',
                    defaultContent: '-',
                    render: (data) => {
                        if (!data) {
                            return '-';
                        }

                        let badge = data == 'main' ? 'bg-primary' : (data == 'complement' ? 'bg-warning' : 'bg-secondary');

                        return '<span class="badge ' + badge + '">' + data + '</span>';
                    }
                },
                { data: 'proses', defaultContent: '-' },
                { data: 'created_at_text', defaultContent: '-' },
            ],
            // Baris judul tetap di atas, baris filter tidak ikut jadi kontrol sorting
            orderCellsTop: true,
        });

        // Filter per kolom di header tabel. Diberi jeda supaya tiap ketikan huruf
        // tidak langsung memicu satu request ke server.
        let columnFilterTimer = null;

        $('#datatable-part-detail thead').on('keyup change', '.column-filter', function() {
            let column = datatablePartDetail.column($(this).data('column'));
            let value = this.value;

            if (column.search() === value) {
                return;
            }

            clearTimeout(columnFilterTimer);

            columnFilterTimer = setTimeout(function() {
                column.search(value).draw();
            }, 400);
        });

        function datatablePartDetailReload() {
            datatablePartDetail.ajax.reload();
        }

        function resetFilter() {
            $("#filter-date-from").val('');
            $("#filter-date-to").val('');
            $("#filter-ws").val('');
            $("#filter-color").val('');
            $("#filter-panel").val('');
            $("#filter-part").val('');
            $("#filter-part-status").val('');

            // Filter di header tabel ikut dikosongkan
            $(".column-filter").val('');
            datatablePartDetail.columns().search('');

            datatablePartDetailReload();
        }

        function exportPartDetail(element) {
            let params = currentFilter();

            exportExcelGlobal(element, params);
        }
    </script>
@endsection
