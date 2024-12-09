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
    <h5 class="text-sb fw-bold mb-3"><i class="fa fa-rotate-left fa-sm"></i> Undo History</h5>
    <div class="card card-sb">
        <div class="card-body">
            <div class="d-flex w-auto gap-3 mb-3">
                <input type="date" class="form form-control form-control-sm mb-1" id="tanggal_awal" name="tanggal_awal" value="{{ date("Y-m-d") }}" onchange="datatableReload()">
                <span>-</span>
                <input type="date" class="form form-control form-control-sm mb-1" id="tanggal_akhir" name="tanggal_akhir" value="{{ date("Y-m-d") }}" onchange="datatableReload()">
            </div>
            <table class="table table-bordered table-sm w-100" id="undo-history-table">
                <thead>
                    <th>Tanggal Plan</th>
                    <th>Line</th>
                    <th>Worksheet</th>
                    <th>Style</th>
                    <th>Color</th>
                    <th>Size</th>
                    <th>Ket.</th>
                    <th>Total</th>
                    <th>Timestamp</th>
                </thead>
                <tbody>
                </tbody>
            </table>
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
        $('.select2').select2({
            theme: 'bootstrap4'
        })
        $('.select2bs4').select2({
            theme: 'bootstrap4'
        })
    </script>

    <script>
        $('#undo-history-table thead tr').clone(true).appendTo('#undo-history-table thead');
        $('#undo-history-table thead tr:eq(1) th').each(function(i) {
            if (i != 7) {
                var title = $(this).text();
                $(this).html('<input type="text" class="form-control form-control-sm"/>');

                $('input', this).on('keyup change', function() {
                    if (undoHistoryTable.column(i).search() !== this.value) {
                        undoHistoryTable
                            .column(i)
                            .search(this.value)
                            .draw();
                    }
                });
            } else {
                $(this).html('');
            }
        });

        var undoHistoryTable = $('#undo-history-table').DataTable({
            processing: true,
            serverSide: true,
            scrollX: '500px',
            scrollY: '350px',
            ordering: false,
            pageLength: 50,
            ajax: {
                url: '{!! route('undo-output-history') !!}',
                data: function (d) {
                    d.tanggal_awal = $('#tanggal_awal').val();
                    d.tanggal_akhir = $('#tanggal_akhir').val();
                }
            },
            columns: [
                {data: 'tgl_plan', name: 'tgl_plan'},
                {data: 'line', name: 'line'},
                {data: 'ws', name: 'ws'},
                {data: 'style', name: 'style'},
                {data: 'color', name: 'color'},
                {data: 'size', name: 'size'},
                {data: 'keterangan', name: 'keterangan'},
                {data: 'total_undo', name: 'total_undo'},
                {data: 'updated_at', name: 'updated_at'},
            ],
            columnDefs: [
                {
                    className: "text-nowrap",
                    targets: [0,2,3,4,5,7,8]
                },
                {
                    className: "text-nowrap",
                    targets: [1],
                    render: function (data) {
                        return data.toUpperCase().replace('_', ' ');
                    }
                },
                {
                    className: "text-nowrap",
                    targets: [6],
                    render: function (data) {
                        let color = "";

                        switch (data) {
                            case "rft" :
                                color = 'text-rft';
                                break;
                            case "defect" :
                                color = 'text-defect';
                                break;
                            case "rework" :
                                color = 'text-rework';
                                break;
                            case "reject" :
                                color = 'text-reject';
                                break;
                        }

                        return "<span class='"+color+" fw-bold'>"+data.toUpperCase().replace('_', ' ')+"</span>";
                    }
                },
            ],
            order: [
                [6, 'desc']
            ],
        });

        function datatableReload() {
            $('#undo-history-table').DataTable().ajax.reload();
        }
    </script>
@endsection

