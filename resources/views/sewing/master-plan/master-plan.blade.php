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
            <h5 class="card-title fw-bold mb-0"><i class="fa fa-cogs"></i> Data Master Plan</h5>
        </div>
        <div class="card-body">
            <div class="d-flex align-items-end gap-3">
                <div class="mb-1">
                    <div>
                        <label>Tanggal</label>
                        <input type="date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" id="master-plan-date" onchange="dataTableMasterPlanReload()">
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table id="datatable-master-plan" class="table table-bordered table w-100">
                    <thead>
                        <tr>
                            <th>Act</th>
                            <th>Line</th>
                            <th>No. WS</th>
                            <th>Style</th>
                            <th>Style Production</th>
                            <th>Color</th>
                            <th>SMV</th>
                            <th>Jam Kerja</th>
                            <th>Man Power</th>
                            <th>Plan Target</th>
                            <th>Target Efficiency</th>
                            {{-- <th>Total Jam</th> --}}
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
<script src="{{ asset('plugins/datatables-rowsgroup/dataTables.rowsGroup.js') }}"></script>

<!-- Select2 -->
<script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
<script>
    $('.select2master').select2({
        theme: 'bootstrap4'
    })
    $('.select2bs4').select2({
        theme: 'bootstrap4'
    })

    $('.select2roll').select2({
        theme: 'bootstrap4'
    })
</script>

<script>
    // $( document ).ready(function() {

    // });
    let datatableMasterPlan = $("#datatable-master-plan").DataTable({
        ordering: false,
        processing: true,
        serverSide: true,
        paging: false,
        ajax: {
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '{{ route('master-plan') }}',
            dataType: 'json',
            dataSrc: 'data',
            data: function(d) {
                d.date = $('#master-plan-date').val();
            },
        },
        columns: [
            {
                data: 'sewing_line'
            },
            {
                data: 'sewing_line'
            },
            {
                data: 'no_ws'
            },
            {
                data: 'style'
            },
            {
                data: 'style_production'
            },
            {
                data: 'color'
            },
            {
                data: 'smv'
            },
            {
                data: 'jam_kerja'
            },
            {
                data: 'man_power'
            },
            {
                data: 'plan_target'
            },
            {
                data: 'target_effy'
            },
            // {
            //     data: 'sewing_line'
            // },
        ],
        columnDefs: [
            {
                targets: [0],
                className: 'align-middle text-nowrap',
                render: (data, type, row, meta) => {
                    return "<a class='btn btn-primary btn-sm' href='{{ route('master-plan-detail') }}/"+row.sewing_line+"/"+row.tgl_plan+"'><i class='fa fa-edit'></i></a>";
                }
            },
            {
                targets: [1],
                className: 'align-middle text-nowrap',
                render: (data, type, row, meta) => {
                    return data ? (data.replace(/_/g, " ")).toUpperCase() : "-"
                }
            },
            {
                targets: [2, 3, 4, 5, 6, 7, 8, 9],
                className: 'align-middle text-nowrap',
            },
            {
                targets: [10],
                className: 'align-middle text-nowrap',
                render: (data, type, row, meta) => {
                    return data ? data.toLocaleString("ID-id")+' %' : '0 %'
                }
            },
            // {
            //     targets: [9],
            //     visible: false,
            //     render: (data, type, row, meta) => {
            //         console.log(meta);
            //         // return row.filter((item) => item.sewing_line == data).reduce((sum, record) => sum + record.jam_kerja);
            //     }
            // }
        ],
        rowsGroup: [
            0,
            1
        ]
    });

    function dataTableMasterPlanReload() {
        datatableMasterPlan.ajax.reload();
    }
</script>
@endsection
