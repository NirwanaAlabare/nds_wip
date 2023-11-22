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
    <div class="card card-sb card-outline">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0">Stocker</h5>
        </div>
        <div class="card-body">
            <div class="d-flex align-items-end gap-3 mb-3">
                <div class="mb-3">
                    <label class="form-label"><small>Tgl Awal</small></label>
                    <input type="date" class="form-control form-control-sm" id="tgl-awal" name="tgl_awal" onchange="dataTableReload()" value="{{ date('Y-m-d') }}">
                </div>
                <div class="mb-3">
                    <label class="form-label"><small>Tgl Akhir</small></label>
                    <input type="date" class="form-control form-control-sm" id="tgl-akhir" name="tgl_akhir" onchange="dataTableReload()" value="{{ date('Y-m-d') }}">
                </div>
                <div class="mb-3">
                    <button class="btn btn-primary btn-sm" onclick="dataTableReload()">Tampilkan</button>
                </div>
            </div>
            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-striped table-sm w-100">
                    <thead>
                        <tr>
                            <th>No. Marker</th>
                            <th>No. Form</th>
                            <th>Tgl Spreading</th>
                            <th>Meja</th>
                            <th>WS</th>
                            <th>Buyer</th>
                            <th>No. Cut</th>
                            <th>Style</th>
                            <th>Color</th>
                            <th>Part</th>
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
        $(document).ready(() => {
            let oneWeeksBefore = new Date(new Date().setDate(new Date().getDate() - 7));
            let oneWeeksBeforeDate = ("0" + oneWeeksBefore.getDate()).slice(-2);
            let oneWeeksBeforeMonth = ("0" + (oneWeeksBefore.getMonth() + 1)).slice(-2);
            let oneWeeksBeforeYear = oneWeeksBefore.getFullYear();
            let oneWeeksBeforeFull = oneWeeksBeforeYear + '-' + oneWeeksBeforeMonth + '-' + oneWeeksBeforeDate;

            $("#tgl-awal").val(oneWeeksBeforeFull).trigger("change");
        });

        let datatable = $("#datatable").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            ajax: {
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route('stocker') }}',
                dataType: 'json',
                dataSrc: 'data',
                data: function(d) {
                    d.dateFrom = $('#tgl-awal').val();
                    d.dateTo = $('#tgl-akhir').val();
                },
            },
            columns: [
                {
                    data: 'id_marker'
                },
                {
                    data: 'no_form'
                },
                {
                    data: 'tgl_form_cut'
                },
                {
                    data: 'nama_meja'
                },
                {
                    data: 'act_costing_ws'
                },
                {
                    data: 'buyer'
                },
                {
                    data: 'no_cut'
                },
                {
                    data: 'style'
                },
                {
                    data: 'color'
                },
                {
                    data: 'nama_part'
                },
                {
                    data: 'marker_details'
                },
                {
                    data: 'total_lembar'
                },
                {
                    data: null
                },
            ],
            columnDefs: [
                // Nama Meja
                {
                    targets: [3],
                    render: (data, type, row, meta) => data ? data.toUpperCase() : "-"
                },
                // Last Column
                {
                    targets: [12],
                    render: (data, type, row, meta) => {
                        return `<div class='d-flex gap-1 justify-content-center'> <a class='btn btn-info btn-sm' href='{{ route("show-stocker") }}/`+row.part_detail_id+`/`+row.form_cut_id+`' data-bs-toggle='tooltip'><i class='fa fa-eye'></i></a> </div>`;
                    }
                }
            ]
        });

        function dataTableReload() {
            datatable.ajax.reload();
        }
    </script>
@endsection
