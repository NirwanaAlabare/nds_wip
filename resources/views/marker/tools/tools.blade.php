@extends('layouts.index')

@section('custom-link')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">

    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

    <style type="text/css">
        input[type=file]::file-selector-button {
        margin-right: 20px;
        border: none;
        background: #084cdf;
        padding: 10px 20px;
        border-radius: 10px;
        color: #fff;
        cursor: pointer;
        transition: background .2s ease-in-out;
        }

        input[type=file]::file-selector-button:hover {
        background: #0d45a5;
        }

        .drop-container {
        position: relative;
        display: flex;
        gap: 10px;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        height: 200px;
        padding: 20px;
        border-radius: 10px;
        border: 2px dashed #555;
        color: #444;
        cursor: pointer;
        transition: background .2s ease-in-out, border .2s ease-in-out;
        }

        .drop-container:hover {
        background: #eee;
        border-color: #111;
        }

        .drop-container:hover .drop-title {
        color: #222;
        }

        .drop-title {
        color: #444;
        font-size: 20px;
        font-weight: bold;
        text-align: center;
        transition: color .2s ease-in-out;
        }
    </style>
@endsection

@section('content')
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0">
                <i class="fa-solid fa-clock-rotate-left"></i> Marker Activity Log
            </h5>
        </div>
        <div class="card-body">
            {{-- Filters --}}
            <div class="row g-3 mb-3 align-items-end">
                <div class="col-md-2">
                    <label class="form-label"><small><b>Dari</b></small></label>
                    <input type="date" id="dateFrom" class="form-control form-control-sm" value="{{ date('Y-m-d') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label"><small><b>Sampai</b></small></label>
                    <input type="date" id="dateTo" class="form-control form-control-sm" value="{{ date('Y-m-d') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label"><small><b>Model</b></small></label>
                    <select id="filterModel" class="form-control form-control-sm select2bs4">
                        <option value="">Semua Model</option>
                        <option value="App\Models\Marker\Marker">Marker</option>
                        <option value="App\Models\Marker\MarkerDetail">Marker Detail</option>
                        <option value="App\Models\Part\Part">Part</option>
                        <option value="App\Models\Part\PartDetail">Part Detail</option>
                        <option value="App\Models\Part\PartDetailSecondary">Part Detail Secondary</option>
                        <option value="App\Models\Part\PartForm">Part Form</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label"><small><b>Event</b></small></label>
                    <select id="filterEvent" class="form-control form-control-sm select2bs4">
                        <option value="">Semua Event</option>
                        <option value="created">Created</option>
                        <option value="updated">Updated</option>
                        <option value="deleted">Deleted</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <button class="btn btn-primary btn-sm w-100" onclick="tableReload()">
                        <i class="fa fa-search fa-xs"></i>
                    </button>
                </div>
                <div class="col-md-1">
                    <button class="btn btn-success btn-sm w-100" onclick="exportLog()">
                        <i class="fa fa-file-excel fa-xs"></i>
                    </button>
                </div>
            </div>

            {{-- DataTable --}}
            <div class="table-responsive">
                <table id="activity-log-table" class="table table-bordered table-sm w-100">
                    <thead>
                        <tr>
                            <th class="text-center">#</th>
                            <th class="text-center">Tanggal</th>
                            <th class="text-center">Subject ID</th>
                            <th class="text-center">Event</th>
                            <th class="text-center">Model</th>
                            <th class="text-center">Action</th>
                            <th class="text-center">Perubahan</th>
                            <th class="text-center">Oleh</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('custom-script')
    <!-- DataTables & Plugins -->
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>

    <!-- Select2 -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        $('.select2bs4').select2({ theme: 'bootstrap4', containerCssClass: 'form-control-sm rounded' });

        let table;

        $(document).ready(function () {
            tableReload();
        });

        // Toggle perubahan — delegated karena DataTable render dinamis
        $(document).on('click', '.btn-toggle-perubahan', function (e) {
            e.preventDefault();
            let $wrap = $(this).prev('.perubahan-wrap');
            let expanded = $wrap.data('expanded');
            if (expanded) {
                $wrap.css('max-height', '42px').data('expanded', false);
                $(this).html('<i class="fas fa-chevron-down fa-xs"></i> Lihat');
            } else {
                $wrap.css('max-height', $wrap[0].scrollHeight + 'px').data('expanded', true);
                $(this).html('<i class="fas fa-chevron-up fa-xs"></i> Sembunyikan');
            }
        });

        function exportLog() {
            Swal.fire({
                title: 'Please Wait...',
                html: 'Menyiapkan file Excel...',
                didOpen: () => { Swal.showLoading(); },
                allowOutsideClick: false,
            });

            $.ajax({
                type: 'GET',
                url: '{{ route("marker-activity-log-export") }}',
                data: {
                    dateFrom : $('#dateFrom').val(),
                    dateTo   : $('#dateTo').val(),
                    model    : $('#filterModel').val(),
                    event    : $('#filterEvent').val(),
                },
                xhrFields: { responseType: 'blob' },
                success: function(response) {
                    Swal.close();
                    let link = document.createElement('a');
                    link.href = window.URL.createObjectURL(new Blob([response]));
                    link.download = 'Marker_Activity_Log_' + $('#dateFrom').val() + '_' + $('#dateTo').val() + '.xlsx';
                    link.click();
                },
                error: function() { Swal.fire({ title: 'Gagal Export', icon: 'error' }); }
            });
        }

        function tableReload() {
            if (table) table.destroy();

            table = $('#activity-log-table').DataTable({
                ordering: false,
                searching: false,
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route("marker-activity-log") }}',
                    data: function (d) {
                        d.dateFrom = $('#dateFrom').val();
                        d.dateTo   = $('#dateTo').val();
                        d.model    = $('#filterModel').val();
                        d.event    = $('#filterEvent').val();
                    },
                },
                columns: [
                    { data: 'id' },
                    { data: 'created_at' },
                    { data: 'subject_id' },
                    { data: 'description' },
                    { data: 'model_name' },
                    { data: 'request_info', orderable: false, searchable: false },
                    { data: 'properties_formatted', orderable: false, searchable: false },
                    { data: 'causer_name' },
                ],
                columnDefs: [
                    {
                        targets: [0],
                        className: 'text-center',
                        render: (data, type, row, meta) => meta.row + 1,
                    },
                    {
                        targets: [1],
                        className: 'text-center',
                        render: (data) => data ? data.substring(0, 19).replace('T', ' ') : '-',
                    },
                    {
                        targets: [2],
                        className: 'text-center',
                    },
                    {
                        targets: [3],
                        className: 'text-center',
                        render: (data) => {
                            const map = { created: 'success', updated: 'warning', deleted: 'danger' };
                            const c   = map[data] ?? 'secondary';
                            return `<span class="badge badge-${c}">${data}</span>`;
                        },
                    },
                    {
                        targets: [4],
                        className: 'text-center text-nowrap',
                        render: (data) => `<span class="badge badge-info">${data}</span>`,
                    },
                    {
                        targets: [5],
                        className: 'text-nowrap',
                    },
                    {
                        targets: [6],
                        render: (data) => {
                            if (!data || data === '-') return '-';
                            return `
                                <div class="perubahan-wrap" style="max-height:45px;overflow:hidden;transition:max-height .3s ease;">
                                    <small>${data}</small>
                                </div>
                                <a href="#" class="btn-toggle-perubahan text-primary" style="font-size:11px;cursor:pointer;">
                                    <i class="fas fa-chevron-down fa-xs"></i> Lihat
                                </a>`;
                        },
                    },
                    {
                        targets: [7],
                        className: 'text-nowrap',
                        render: (data) => data ?? '<span class="text-muted">System</span>',
                    },
                    {
                        targets: '_all',
                        defaultContent: '-',
                    },
                ],
            });

            // Header filter — skip kolom #, Action, Perubahan
            const skipCols = [0, 5, 6];
            $('#activity-log-table thead tr:eq(1)').remove();
            $('#activity-log-table thead tr:eq(0)').clone(false).appendTo('#activity-log-table thead');
            $('#activity-log-table thead tr:eq(1) th').each(function (i) {
                if (skipCols.includes(i)) { $(this).html(''); return; }
                $(this).html('<input type="text" class="form-control form-control-sm" style="width:100%;">');
                $('input', this).on('keyup change', function () {
                    if (table.column(i).search() !== this.value) {
                        table.column(i).search(this.value).draw();
                    }
                });
            });
        }
    </script>
@endsection
