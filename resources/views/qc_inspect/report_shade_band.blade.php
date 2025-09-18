@extends('layouts.index')

@section('custom-link')
    {{-- <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}"> --}}

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables 2.0/jquery.dataTables.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables 2.0/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables 2.0/fixedColumns.bootstrap4.min.css') }}">
    <!-- jQuery -->
    <script src="{{ asset('plugins/datatables 2.0/jquery-3.3.1.js') }}"></script>


    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

    <style>
        .checkbox-cell-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 50px;
            height: 100%;
            padding: 0;
        }
    </style>
@endsection

@section('content')
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-list"></i> Report Shade Band</h5>
        </div>
        <div class="card-body">
            <div class="d-flex align-items-end gap-3 mb-3">
                <div class="mb-3">
                    <label class="form-label"><small><b>Tgl Awal</b></small></label>
                    <input type="date" class="form-control form-control-sm " id="tgl-awal" name="tgl_awal"
                        value="{{ $tgl_skrg_min_sebulan }}">
                </div>
                <div class="mb-3">
                    <label class="form-label"><small><b>Tgl Akhir</b></small></label>
                    <input type="date" class="form-control form-control-sm" id="tgl-akhir" name="tgl_akhir"
                        value="{{ date('Y-m-d') }}">
                </div>
                <div class="mb-3">
                    <a class="btn btn-outline-primary position-relative btn-sm" onclick="dataTableReload()">
                        <i class="fas fa-search"></i>
                        Cari
                    </a>
                </div>

                {{-- <div class="mb-3">
                    <a onclick="notif()" class="btn btn-outline-success position-relative btn-sm">
                        <i class="fas fa-file-excel fa-sm"></i>
                        Export Excel
                    </a>
                </div> --}}
            </div>
            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-hover align-middle text-nowrap w-100">
                    <thead class="bg-sb">
                        <tr>
                            <th scope="col" class="text-center align-middle text-dark">Act</th>
                            <th scope="col" class="text-center align-middle">Tgl Update</th>
                            <th scope="col" class="text-center align-middle">Supplier</th>
                            <th scope="col" class="text-center align-middle">Buyer</th>
                            <th scope="col" class="text-center align-middle">WS</th>
                            <th scope="col" class="text-center align-middle">Style</th>
                            <th scope="col" class="text-center align-middle">Color</th>
                            <th scope="col" class="text-center align-middle">ID Item</th>
                            <th scope="col" class="text-center align-middle">Detail Item</th>
                            <th scope="col" class="text-center align-middle">Group</th>
                            <th scope="col" class="text-center align-middle">Jml Roll</th>
                            <th scope="col" class="text-center align-middle">Result</th>
                        </tr>
                        <tr>
                            <th></th> <!-- Empty cell for Act (no search input) -->
                            <th><input type="text" class="column-search form-control form-control-sm" /></th>
                            <th><input type="text" class="column-search form-control form-control-sm" /></th>
                            <th><input type="text" class="column-search form-control form-control-sm" /></th>
                            <th><input type="text" class="column-search form-control form-control-sm" /></th>
                            <th><input type="text" class="column-search form-control form-control-sm" /></th>
                            <th><input type="text" class="column-search form-control form-control-sm" /></th>
                            <th><input type="text" class="column-search form-control form-control-sm" /></th>
                            <th><input type="text" class="column-search form-control form-control-sm" /></th>
                            <th><input type="text" class="column-search form-control form-control-sm" /></th>
                            <th><input type="text" class="column-search form-control form-control-sm" /></th>
                            <th><input type="text" class="column-search form-control form-control-sm" /></th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('custom-script')
    <!-- DataTables & Plugins -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables 2.0/dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables 2.0/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables 2.0/dataTables.fixedColumns.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-rowsgroup/dataTables.rowsGroup.js') }}"></script>
    <script>
        // Select2 Autofocus
        $(document).on('select2:open', () => {
            document.querySelector('.select2-search__field').focus();
        });

        // Initialize Select2 Elements
        $('.select2').select2();

        // Initialize Select2BS4 Elements
        $('.select2bs4').select2({
            theme: 'bootstrap4',
        });

        function notif() {
            alert("Maaf, Fitur belum tersedia!");
        }

        const addRouteTemplate =
            "{{ route('qc_inspect_report_shade_band_add', ['id_item' => '__id_item__', 'id_jo' => '__id_jo__', 'group' => '__group__']) }}";

        const printRouteTemplate =
            "{{ route('qc_inspect_report_shade_band_print', ['id_item' => '__id_item__', 'id_jo' => '__id_jo__', 'group' => '__group__']) }}";
    </script>

    <script>
        function dataTableReload() {
            datatable.ajax.reload();
        }

        $(document).ready(function() {
            dataTableReload();
        })

        let datatable = $("#datatable").DataTable({
            ordering: false,
            responsive: true,
            processing: true,
            serverSide: false,
            paging: true,
            searching: true,
            scrollY: true,
            scrollX: true,
            scrollCollapse: false,
            fixedColumns: {
                leftColumns: 1
            },
            lengthMenu: [
                [10, 25, 50, -1],
                [10, 25, 50, "All"]
            ],
            pageLength: 10, // Default rows per page
            ajax: {
                url: '{{ route('qc_inspect_report_shade_band') }}',
                data: function(d) {
                    d.dateFrom = $('#tgl-awal').val();
                    d.dateTo = $('#tgl-akhir').val();
                },
            },
            columns: [{
                    data: null,
                    orderable: false,
                    searchable: false,
                    className: 'text-start',
                    render: function(data, type, row) {
                        const idItem = row.id_item || '';
                        const idJo = row.id_jo || '';
                        const group = row.group || '';

                        const addUrl = addRouteTemplate
                            .replace('__id_item__', idItem)
                            .replace('__id_jo__', idJo)
                            .replace('__group__', group);

                        const printUrl = printRouteTemplate
                            .replace('__id_item__', idItem)
                            .replace('__id_jo__', idJo)
                            .replace('__group__', group);

                        let buttons = `
        <a
            href="${addUrl}" target="_blank"
            class="btn btn-sm btn-outline-primary"
            style="margin-right: 5px;"
            title="Add">
            <i class="fas fa-search fa-sm"></i>
        </a>
    `;

                        if (row.result) {
                            buttons += `
            <a
                href="${printUrl}"
                class="btn btn-sm btn-outline-danger"
                title="Print PDF"
                target="_blank">
                <i class="fas fa-print fa-sm"></i> Print
            </a>
        `;
                        }

                        return buttons;
                    }
                },
                {
                    data: 'tgl_update_fix'
                },
                {
                    data: 'supplier'
                },
                {
                    data: 'buyer'
                },
                {
                    data: 'kpno'
                },
                {
                    data: 'styleno'
                },
                {
                    data: 'color'
                },
                {
                    data: 'id_item'
                },
                {
                    data: 'itemdesc'
                },
                {
                    data: 'group'
                },
                {
                    data: 'jml_roll'
                },
                {
                    data: 'result'
                },
            ],
            initComplete: function() {
                this.api().columns().every(function() {
                    var column = this;
                    $('input', this.header()).on('keyup change clear', function() {
                        if (column.search() !== this.value) {
                            column.search(this.value).draw();
                        }
                    });
                });
            }
        });
    </script>
@endsection
