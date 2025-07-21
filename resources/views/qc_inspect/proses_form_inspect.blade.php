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
@endsection

@section('content')
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-list"></i> Form Inspect</h5>
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
                <div class="mb-3">
                    <a onclick="notif()" class="btn btn-outline-success position-relative btn-sm">
                        <i class="fas fa-file-excel fa-sm"></i>
                        Export Excel
                    </a>
                </div>
            </div>
            <div class="table-responsive">
                <table id="datatable" class="table table-bordered w-100 text-nowrap">
                    <thead class="bg-sb">
                        <tr style="text-align:center; vertical-align:middle">
                            <th scope="col"style="color: black;">Act</th>
                            <th scope="col">Tanggal</th>
                            <th scope="col">No. Mesin</th>
                            <th scope="col">No. Form</th>
                            <th scope="col">No. PL</th>
                            <th scope="col">Buyer</th>
                            <th scope="col">WS</th>
                            <th scope="col">Style</th>
                            <th scope="col">Color</th>
                            <th scope="col">Group Inspect</th>
                            <th scope="col">Lot</th>
                            <th scope="col">No. Roll</th>
                            <th scope="col">Point / Max Point</th>
                            <th scope="col">Result</th>
                            <th scope="col">Note</th>
                            <th scope="col">Status</th>
                            <th scope="col">Proses</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('custom-script')
    <!-- DataTables & Plugins -->
    {{-- <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script> --}}
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
            ajax: {
                url: '{{ route('qc_inspect_proses_form_inspect') }}',
                data: function(d) {
                    d.dateFrom = $('#tgl-awal').val();
                    d.dateTo = $('#tgl-akhir').val();
                },
            },
            columns: [{
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        return `
                    <a class="btn btn-outline-primary position-relative btn-sm" href="{{ route('qc_inspect_proses_form_inspect_det') }}/` +
                            data.id + `" title="Detail" target="_blank">
                        Detail
                    </a>`;
                    }
                },
                {
                    data: 'tgl_form_fix'
                },
                {
                    data: 'no_mesin'
                },
                {
                    data: 'no_form'
                },
                {
                    data: 'no_invoice'
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
                    data: 'group_inspect'
                },
                {
                    data: 'no_lot'
                },
                {
                    data: 'no_lot'
                },
                {
                    data: 'no_lot'
                },
                {
                    data: 'no_lot'
                },
                {
                    data: 'type_pch'
                },
                {
                    data: 'type_pch'
                },
                {
                    data: 'proses'
                },
            ],
        });
    </script>
@endsection
