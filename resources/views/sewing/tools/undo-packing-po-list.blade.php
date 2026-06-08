@extends('layouts.index')

@section('custom-link')
    <link rel="stylesheet" href="{{ asset('plugins/datatables 2.0/jquery.dataTables.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables 2.0/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
    <script src="{{ asset('plugins/datatables 2.0/jquery-3.3.1.js') }}"></script>
@endsection

@section('content')
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fa-solid fa-clock-rotate-left"></i> List Undo Packing PO
            </h5>
        </div>
        <div class="card-body">
            <div class="row g-3 mb-3">
                <div class="col-md-3">
                    <label class="form-label"><small><b>Tanggal Awal</b></small></label>
                    <input type="date" class="form-control form-control-sm" id="tgl_awal" value="{{ date('Y-m-d') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label"><small><b>Tanggal Akhir</b></small></label>
                    <input type="date" class="form-control form-control-sm" id="tgl_akhir" value="{{ date('Y-m-d') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label"><small><b>Kode Numbering</b></small></label>
                    <input type="text" class="form-control form-control-sm" id="kode_numbering" placeholder="Filter kode numbering...">
                </div>
                <div class="col-md-2 d-flex align-items-end gap-2">
                    <button class="btn btn-sb btn-sm w-100" onclick="tableReload()">
                        <i class="fas fa-search fa-xs"></i> Cari
                    </button>
                    <a onclick="exportExcel()" class="btn btn-outline-success btn-sm w-100">
                        <i class="fas fa-file-excel fa-sm"></i> Export
                    </a>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-sm w-100" id="datatable-undo-packing-po">
                    <thead>
                        <tr>
                            <th class="text-nowrap">Tanggal</th>
                            <th class="text-nowrap">Kode Numbering</th>
                            <th class="text-nowrap">Type</th>
                            <th class="text-nowrap">WS</th>
                            <th class="text-nowrap">Style</th>
                            <th class="text-nowrap">Color</th>
                            <th class="text-nowrap">Size</th>
                            <th class="text-nowrap">Line</th>
                            <th class="text-nowrap">Tgl Plan</th>
                            <th class="text-nowrap">Alokasi</th>
                            <th class="text-nowrap">Keterangan</th>
                            <th class="text-nowrap">By Username</th>
                            <th class="text-nowrap">By Line</th>
                            <th class="text-nowrap">Created At</th>
                            <th class="text-nowrap">Updated At</th>
                            <th class="text-nowrap">Output Created At</th>
                            <th class="text-nowrap">Output Updated At</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('custom-script')
    <script src="{{ asset('plugins/datatables 2.0/dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables 2.0/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>

    <script>
        let table = null;

        function tableReload() {
            if ($.fn.DataTable.isDataTable('#datatable-undo-packing-po')) {
                $('#datatable-undo-packing-po').DataTable().ajax.reload();
                return;
            }

            table = $('#datatable-undo-packing-po').DataTable({
                processing: true,
                serverSide: true,
                destroy: true,
                ordering: false,
                ajax: {
                    url: "{{ route('get-undo-packing-po-list') }}",
                    type: "GET",
                    data: function(d) {
                        d.tgl_awal       = $('#tgl_awal').val();
                        d.tgl_akhir      = $('#tgl_akhir').val();
                        d.kode_numbering = $('#kode_numbering').val();
                    }
                },
                columns: [
                    { data: 'updated_at' },
                    { data: 'kode_numbering' },
                    { data: 'output_type' },
                    { data: 'ws' },
                    { data: 'style' },
                    { data: 'color' },
                    { data: 'size' },
                    { data: 'line' },
                    { data: 'tgl_plan' },
                    { data: 'alokasi' },
                    { data: 'keterangan' },
                    { data: 'created_by_username' },
                    { data: 'created_by_line' },
                    { data: 'created_at' },
                    { data: 'updated_at' },
                    { data: 'output_created_at' },
                    { data: 'output_updated_at' },
                ],
                columnDefs: [
                    { targets: '_all', className: 'text-nowrap', defaultContent: '-' },
                    {
                        targets: [0, 13, 14, 15, 16],
                        render: function(data, type, row) {
                            return data ? formatDateTime(data) : '-';
                        }
                    },
                ],
                initComplete: function() {
                    var api = this.api();
                    $('#datatable-undo-packing-po thead tr:first').clone(true).appendTo('#datatable-undo-packing-po thead');
                    $('#datatable-undo-packing-po thead tr:eq(1) th').each(function(i) {
                        $(this).html('<input type="text" class="form-control form-control-sm" placeholder="Filter..." />');
                        $('input', this).on('keyup change', function() {
                            if (api.column(i).search() !== this.value) {
                                api.column(i).search(this.value).draw();
                            }
                        });
                    });
                },
            });
        }

        $(document).ready(function() {
            tableReload();
        });

        $('#tgl_awal, #tgl_akhir').on('change', function() {
            tableReload();
        });

        function exportExcel() {
            Swal.fire({
                title: 'Please Wait...',
                html: 'Exporting Data...',
                didOpen: () => { Swal.showLoading(); },
                allowOutsideClick: false,
            });

            $.ajax({
                type: 'GET',
                url: "{{ route('export-undo-packing-po-list') }}",
                data: {
                    tgl_awal       : $('#tgl_awal').val(),
                    tgl_akhir      : $('#tgl_akhir').val(),
                    kode_numbering : $('#kode_numbering').val(),
                },
                xhrFields: { responseType: 'blob' },
                success: function(response) {
                    Swal.close();
                    let blob = new Blob([response]);
                    let link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = 'List_Undo_Packing_PO_' + $('#tgl_awal').val() + '_' + $('#tgl_akhir').val() + '.xlsx';
                    link.click();
                },
                error: function() {
                    Swal.close();
                    Swal.fire({ title: 'Gagal Export', icon: 'error' });
                }
            });
        }
    </script>
@endsection
