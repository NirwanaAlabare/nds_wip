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
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-map-location fa-sm"></i> Cutting Plan Output</h5>
        </div>
        <div class="card-body">
            <a href="{{ route('create-cut-plan-output') }}" class="btn btn-success btn-sm mb-3">
                <i class="fa fa-plus"></i>
                Plan Output
            </a>
            <div class="d-flex align-items-end gap-3 mb-3">
                <div class="mb-3">
                    <label class="form-label"><small>Tanggal</small></label>
                    <input type="date" class="form-control form-control-sm" id="date" name="date" onchange="datatableCutPlanOutputReload()">
                </div>
                <div class="mb-3">
                    <button class="btn btn-primary btn-sm" onclick="datatableCutPlanOutputReload()"><i class="fa fa-search"></i></button>
                </div>
            </div>
            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table w-100">
                    <thead>
                        <tr>
                            <th>Action</th>
                            <th>Tanggal</th>
                            <th>Meja</th>
                            <th>No. WS</th>
                            <th>Style</th>
                            <th>Color</th>
                            <th>Target Shift 1</th>
                            <th>Pending Shift 1</th>
                            <th>Target Shift 2</th>
                            <th>Pending Shift 2</th>
                            <th>Balance</th>
                            <th>Material Cons</th>
                            <th>Material Need</th>
                            <th>Material In</th>
                            <th>Material Total In</th>
                            <th>Material Balance</th>
                            <th>Material Use</th>
                            <th>Material Sisa</th>
                            <th>Material unit</th>
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
    <!-- Select2 -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <!-- Page specific script -->
    <script>
        //Initialize Select2 Elements
        $('.select2').select2();

        //Initialize Select2 Elements
        $('.select2bs4').select2({
            theme: 'bootstrap4'
        });

        $(document).on('select2:open', () => {
            document.querySelector('.select2-search__field').focus();
        });

        document.addEventListener("DOMContentLoaded", () => {
            let oneWeeksBefore = new Date(new Date().setDate(new Date().getDate() - 7));
            let oneWeeksBeforeDate = ("0" + oneWeeksBefore.getDate()).slice(-2);
            let oneWeeksBeforeMonth = ("0" + (oneWeeksBefore.getMonth() + 1)).slice(-2);
            let oneWeeksBeforeYear = oneWeeksBefore.getFullYear();
            let oneWeeksBeforeFull = oneWeeksBeforeYear + '-' + oneWeeksBeforeMonth + '-' + oneWeeksBeforeDate;

            $("#tgl-awal").val(oneWeeksBeforeFull).trigger("change");

            window.addEventListener("focus", () => {
                $('#datatable').DataTable().ajax.reload(null, false);
            });
        });

        let datatable = $("#datatable").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('cut-plan-output') }}',
                data: function(d) {
                    d.date = $('#date').val();
                },
            },
            columns: [
                {
                    data: 'id'
                },
                {
                    data: 'tgl_plan',
                },
                {
                    data: 'nama_meja',
                },
                {
                    data: 'ws',
                },
                {
                    data: 'style',
                },
                {
                    data: 'color',
                },
                {
                    data: 'target_1'
                },
                {
                    data: 'pending_1'
                },
                {
                    data: 'target_2'
                },
                {
                    data: 'pending_2'
                },
                {
                    data: 'balance'
                },
                {
                    data: 'cons'
                },
                {
                    data: 'need'
                },
                {
                    data: 'in'
                },
                {
                    data: 'total_in'
                },
                {
                    data: 'material_balance'
                },
                {
                    data: 'use_act'
                },
                {
                    data: 'sisa'
                },
                {
                    data: 'unit'
                },
            ],
            columnDefs: [
                {
                    targets: [0],
                    render: (data, type, row, meta) => {
                        return `
                            <div class='d-flex gap-1 justify-content-center'>
                                <a class='btn btn-primary btn-sm' href='{{ route('detail-cut-plan-output') }}/`+row.id+`'>
                                    <i class='fa fa-search'></i>
                                </a>
                            </div>
                        `;
                    }
                },
                {
                    targets: [2],
                    render: (data, type, row, meta) => data.replace(/_/g, '').toUpperCase()
                },
                {
                    targets: [6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17],
                    render: (data, type, row, meta) => Number(data).toLocaleString("ID-id")
                },
                {
                    targets: [18],
                    render: (data, type, row, meta) => data.toUpperCase()
                },
                {
                    targets: '_all',
                    className: "text-nowrap"
                }
            ],
        });

        function filterTable() {
            datatable.ajax.reload();
        }

        $('#datatable thead tr').clone(true).appendTo('#datatable thead');
        $('#datatable thead tr:eq(1) th').each(function(i) {
            if (i == 1 || i == 2 || i == 3 || i == 4 || i == 5) {
                var title = $(this).text();
                $(this).html('<input type="text" class="form-control form-control-sm" />');

                $('input', this).on('keyup change', function() {
                    if (datatable.column(i).search() !== this.value) {
                        datatable
                            .column(i)
                            .search(this.value)
                            .draw();
                    }
                });
            } else {
                $(this).empty();
            }
        });
    </script>
@endsection
