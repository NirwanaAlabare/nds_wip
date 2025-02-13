@extends('layouts.index')

@section('custom-link')
<!-- DataTables -->
<link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">

<!-- Select2 -->
<link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@endsection

@section('content')
<div class="card card-sb">
    <div class="card-header">
        <h5 class="card-title fw-bold mb-0">Data Warehouse</h5>
    </div>
    <div class="card-body">
        <div class="d-flex align-items-end gap-3 mb-3">
            <div class="mb-3">
                <label class="form-label"><small>Tgl Awal</small></label>
                <input type="date" class="form-control form-control-sm" id="tgl-awal" name="tgl_awal" value="{{ date('Y-m-d') }}">
            </div>
            <div class="mb-3">
                <label class="form-label"><small>Tgl Akhir</small></label>
                <input type="date" class="form-control form-control-sm" id="tgl-akhir" name="tgl_akhir" value="{{ date('Y-m-d') }}">
            </div>
            <div class="mb-3">
                <button class="btn btn-primary btn-sm" onclick="dataTableReload()">Tampilkan</button>
                <a href="{{ route('create-marker') }}" class="btn btn-info btn-sm">
                <i class="fas fa-plus"></i>
                Baru
            </a>
            </div>
        </div>
        <div class="table-responsive">
            <table id="datatable" class="table table-bordered table-striped table-sm w-100">
                <thead>
                    <tr>
                        <th>No Form</th>
                        <th>Tgl Form</th>
                        <th>No. Meja</th>
                        <th>Marker</th>
                        <th>WS</th>
                        <th>Color</th>
                        <th>Panel</th>
                        <th>Size Ratio</th>
                        <th>Total Lembar</th>
                        <th>Act</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
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
    $('.select2').select2()
    $('.select2bs4').select2({
        theme: 'bootstrap4',
        dropdownParent: $("#editMejaModal")
    })
</script>

<script>
    let datatable = $("#datatable").DataTable({
        ordering: false,
        processing: true,
        serverSide: true,
        paging: false,
        ajax: {
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '{{ route('warehouse') }}',
            dataType: 'json',
            dataSrc: 'data',
            data: function(d) {
                d.dateFrom = $('#tgl-awal').val();
                d.dateTo = $('#tgl-akhir').val();
            },
        },
        columns: [{
                data: 'no_form'
            },
            {
                data: 'tgl_form_cut'
            },
            {
                data: 'nama_meja'
            },
            {
                data: 'id_marker'
            },
            {
                data: 'ws'
            },
            {
                data: 'color'
            },
            {
                data: 'panel'
            },
            {
                data: 'marker_details'
            },
            {
                data: 'total_lembar'
            },
            {
                data: 'id'
            },
        ],
        columnDefs: [{
                targets: [2],
                render: (data, type, row, meta) => data ? data.toUpperCase() : "-"
            },
            {
                targets: [9],
                render: (data, type, row, meta) => {
                    return `<div class='d-flex gap-1 justify-content-center'> <a class='btn btn-info btn-sm' href='{{ route("show-stocker") }}/` + data + `' data-bs-toggle='tooltip'><i class='fa fa-eye'></i></a> </div>`;
                }
            }
        ]
    });

    function dataTableReload() {
        datatable.ajax.reload();
    }
</script>
@endsection
