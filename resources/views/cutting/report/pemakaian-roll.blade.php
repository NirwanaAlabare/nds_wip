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
                            <th>Action</th>
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
                            <th>Roll In</th>
                            <th>Qty In</th>
                            <th>Roll Cutting</th>
                            <th>Qty Cutting</th>
                            <th>Roll Balance</th>
                            <th>Qty Balance</th>
                            <th class="d-none">No. Return</th>
                            <th>Roll Return</th>
                            <th>Qty Return</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="modal fade" tabindex="-1" id="detailPemakaianKainModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-sb text-light fw-bold">
                    <h5 class="modal-title"><i class="fa-solid fa-circle-info"></i> Detail Pemakaian Kain</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">No. Request</label>
                            <input type="text" class="form-control form-control-sm" id="detail_no_req" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">ID Item</label>
                            <input type="text" class="form-control form-control-sm" id="detail_id_item" readonly>
                        </div>
                    </div>
                    <table class="table table-sm table-bordered w-100" id="datatable-detail">
                        <thead>
                            <th>ID Roll</th>
                            <th>ID Item</th>
                            <th>Detail Item</th>
                            <th>Lot</th>
                            <th>Roll</th>
                            <th>Qty</th>
                            <th>Total Pemakaian Kain</th>
                            <th>Total Short Roll</th>
                            <th>Unit</th>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="fa fa-times"></i> Tutup</button>
                </div>
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
                    data: 'id_item'
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
                    data: 'total_pakai_cutting'
                },
                {
                    data: 'total_roll_balance'
                },
                {
                    data: 'total_pakai_balance'
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
                    targets: [0],
                    render: (data, type, row, meta) => {
                        let button = `
                            <div class='d-flex justify-content-center align-item-middle'>
                                <button class="btn btn-sm btn-primary" onclick="showDetail('`+row.bppbno+`', '`+row.id_item+`')"> <i class="fa-solid fa-circle-info"></i> </button>
                            </div>
                        `;

                        return button;
                    }
                },
                {
                    targets: [11, 18],
                    className: "d-none",
                },
                {
                    targets: [9, 10, 12, 13, 14, 16, 19, 20],
                    className: "fw-bold",
                },
                {
                    targets: [15, 17],
                    className: "fw-bold",
                    render: (data, type, row, meta) => {
                        let color = "";

                        // if (data > 0) {
                        //     color = '#05b01f;';
                        // } else if (data < 0) {
                        //     color = '#d60d0d;';
                        // } else {
                        //     color = '#000;';
                        // }

                        color = '#212529;';

                        return "<span style='color: "+color+"'>"+data+"</span>";
                    }
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

        $('#datatable-detail thead tr').clone(true).appendTo('#datatable-detail thead');
        $('#datatable-detail thead tr:eq(1) th').each(function(i) {
            // if (i <= 7) {
                var title = $(this).text();
                $(this).html('<input type="text" class="form-control form-control-sm" />');

                $('input', this).on('keyup change', function() {
                    if (datatableDetail.column(i).search() !== this.value) {
                        datatableDetail
                            .column(i)
                            .search(this.value)
                            .draw();
                    }
                });
            // } else {
            //     $(this).empty();
            // }
        });

        let datatableDetail = $("#datatable-detail").DataTable({
            processing: true,
            serverSide: false,
            ordering: false,
            scrollX: "500px",
            scrollY: "500px",
            pageLength: 50,
            ajax: {
                url: '{{ route('detail-pemakaian-roll') }}',
                data: function(d) {
                    d.no_req = $("#detail_no_req").val();
                    d.id_item = $("#detail_id_item").val();
                },
            },
            columns: [
                {
                    data: 'id_roll'
                },
                {
                    data: 'id_item'
                },
                {
                    data: 'detail_item'
                },
                {
                    data: 'lot'
                },
                {
                    data: 'roll'
                },
                {
                    data: 'qty'
                },
                {
                    data: 'total_pemakaian_roll'
                },
                {
                    data: 'total_short_roll'
                },
                {
                    data: 'unit'
                },
            ]
        });

        function datatableDetailReload() {
            $("#datatable-detail").DataTable().ajax.reload();
        }

        function showDetail(no_req, id_item) {
            $("#detail_no_req").val(no_req).trigger("change");
            $("#detail_id_item").val(id_item).trigger("change");

            datatableDetailReload();

            $("#detailPemakaianKainModal").modal('show');
        }
    </script>
@endsection
