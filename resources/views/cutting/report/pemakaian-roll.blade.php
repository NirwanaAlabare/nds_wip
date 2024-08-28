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
    <div class="card">
        <div class="card-header bg-sb text-light">
            <h5 class="card-title fw-bold">Pemakaian Kain <i class="fa fa-file"></i></h5>
        </div>
        <div class="card-body">
            <label>Tanggal</label>
            <div class="d-flex">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div>
                        <input type="date" class="form-control form-control-sm" id="dateFrom" onchange="datatableReload()">
                    </div>
                    <span> - </span>
                    <div>
                        <input type="date" class="form-control form-control-sm" id="dateTo" value="{{ date('Y-m-d') }}" onchange="datatableReload()">
                    </div>
                    <button class="btn btn-primary btn-sm"><i class="fa fa-search"></i></button>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-sm" id="datatable">
                    <thead>
                        <tr>
                            <th>No. Request</th>
                            <th>Tanggal Request</th>
                            <th>Tujuan</th>
                            <th>No. WS</th>
                            <th>Style</th>
                            <th>Buyer</th>
                            <th>Item</th>
                            <th>Item Desc</th>
                            <th>Qty Req</th>
                            <th>Unit</th>
                            <th class="d-none">No. Out</th>
                            <th>Roll Out</th>
                            <th>Qty Out</th>
                            <th>Roll Cutting</th>
                            <th>Qty Cutting</th>
                            <th>Pemakaian Cutting</th>
                            <th class="d-none">No. Retur</th>
                            <th>Roll Retur</th>
                            <th>Qty Retur</th>
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
    <!-- DataTables  & Plugins -->
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-rowsgroup/dataTables.rowsGroup.js') }}"></script>
    <!-- Select2 -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            let oneWeeksBefore = new Date(new Date().setDate(new Date().getDate() - 7));
            let oneWeeksBeforeDate = ("0" + oneWeeksBefore.getDate()).slice(-2);
            let oneWeeksBeforeMonth = ("0" + (oneWeeksBefore.getMonth() + 1)).slice(-2);
            let oneWeeksBeforeYear = oneWeeksBefore.getFullYear();
            let oneWeeksBeforeFull = oneWeeksBeforeYear + '-' + oneWeeksBeforeMonth + '-' + oneWeeksBeforeDate;

            $("#dateFrom").val(oneWeeksBeforeFull).trigger("change");

            // window.addEventListener("focus", () => {
            //     $('#datatable').DataTable().ajax.reload(null, false);
            // });

            datatableReload();
        });

        $('#datatable thead tr').clone(true).appendTo('#datatable thead');
        $('#datatable thead tr:eq(1) th').each(function(i) {
            // if (i <= 7) {
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
            // } else {
            //     $(this).empty();
            // }
        });

        let datatable = $("#datatable").DataTable({
            processing: true,
            serverSide: false,
            ordering: false,
            scrollX: "500px",
            scrollY: "500px",
            pageLength: 50,
            ajax: {
                url: '{{ route('pemakaian-roll') }}',
                data: function(d) {
                    d.dateFrom = $('#dateFrom').val();
                    d.dateTo = $('#dateTo').val();
                },
            },
            columns: [
                {
                    data: 'bppbno'
                },
                {
                    data: 'bppbdate'
                },
                {
                    data: 'tujuan'
                },
                {
                    data: 'no_ws'
                },
                {
                    data: 'styleno'
                },
                {
                    data: 'buyer'
                },
                {
                    data: 'id_roll'
                },
                {
                    data: 'itemdesc'
                },
                {
                    data: 'qty_req'
                },
                {
                    data: 'unit'
                },
                {
                    data: 'no_out',
                },
                {
                    data: 'roll_out'
                },
                {
                    data: 'qty_out'
                },
                {
                    data: 'total_roll_cutting'
                },
                {
                    data: 'total_qty_cutting'
                },
                {
                    data: 'total_pakai_cutting'
                },
                {
                    data: 'no_retur'
                },
                {
                    data: 'roll_retur'
                },
                {
                    data: 'qty_retur'
                },
            ],
            columnDefs: [
                {
                    targets: [10, 16],
                    className: "d-none",
                },
                {
                    targets: "_all",
                    className: "text-nowrap",
                }
            ]
        });

        function datatableReload() {
            datatable.ajax.reload();
        }
    </script>
@endsection