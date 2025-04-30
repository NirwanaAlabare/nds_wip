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
            <div class="d-flex justify-content-between align-items-end">
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
                <button class="btn btn-success btn-sm mb-3" onclick="exportExcel(this)"><i class="fa fa-file-excel"></i> Export</button>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table" id="datatable">
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
                    <tfoot>
                        <tr>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                        </tr>
                    </tfoot>
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
                    <div class="row justify-content-start align-items-end mb-3 g-1">
                        <div class="col-md-3">
                            <label class="form-label">Tanggal</label>
                            <input type="text" class="form-control form-control-sm" id="detail_tanggal" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">No. Request</label>
                            <input type="text" class="form-control form-control-sm" id="detail_no_req" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">ID Item</label>
                            <input type="text" class="form-control form-control-sm" id="detail_id_item" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Tujuan</label>
                            <input type="text" class="form-control form-control-sm" id="detail_tujuan" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">No. WS</label>
                            <input type="text" class="form-control form-control-sm" id="detail_no_ws" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Style</label>
                            <input type="text" class="form-control form-control-sm" id="detail_style" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Buyer</label>
                            <input type="text" class="form-control form-control-sm" id="detail_buyer" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Qty Req</label>
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control form-control-sm" id="detail_qty_req" readonly>
                                <span class="input-group-text detail-unit"></span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Total Roll In</label>
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control form-control-sm" id="detail_roll_in" readonly>
                                <span class="input-group-text">Roll</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Total Qty In</label>
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control form-control-sm" id="detail_qty_in" readonly>
                                <span class="input-group-text detail-unit"></span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Total Pemakaian Roll</label>
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control form-control-sm" id="detail_roll_cutting" readonly>
                                <span class="input-group-text">Roll</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Total Pemakaian Kain</label>
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control form-control-sm" id="detail_qty_cutting" readonly>
                                <span class="input-group-text detail-unit"></span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Total Short</label>
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control form-control-sm" id="detail_short_cutting" readonly>
                                <span class="input-group-text detail-unit"></span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Total Roll Balance</label>
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control form-control-sm" id="detail_roll_balance" readonly>
                                <span class="input-group-text">Roll</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Total Qty Balance</label>
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control form-control-sm" id="detail_qty_balance" readonly>
                                <span class="input-group-text detail-unit"></span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Total Roll Return</label>
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control form-control-sm" id="detail_roll_return" readonly>
                                <span class="input-group-text">Roll</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Total Qty Return</label>
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control form-control-sm" id="detail_qty_return" readonly>
                                <span class="input-group-text detail-unit"></span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <button type="button" class="btn btn-success btn-sm btn-block" onclick="exportExcelDetail(this)"><i class="fa fa-file-excel"></i> Export</button>
                        </div>
                    </div>
                    <table class="table table table-bordered w-100" id="datatable-detail">
                        <thead>
                            <tr>
                                <th>ID Roll</th>
                                <th>ID Item</th>
                                <th>Detail Item</th>
                                <th>Lot</th>
                                <th>No. Roll</th>
                                <th>Qty Roll</th>
                                <th>Pemakaian Kain</th>
                                <th>Sisa Kain</th>
                                <th>Short Roll</th>
                                <th>Short Roll (%)</th>
                                <th>Unit</th>
                                <th>Tanggal Return</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th></th>
                            </tr>
                        </tfoot>
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

        var pemakaianKainFilter = ['bppbno', 'bppbdate', 'tujuan', 'no_ws', 'styleno', 'buyer', 'id_item', 'itemdesc'];

        $('#datatable thead tr').clone(true).appendTo('#datatable thead');
        $('#datatable thead tr:eq(1) th').each(function(i) {
            if (i == 1 || i == 2 || i == 3 || i == 4 || i == 5 || i == 6 || i == 7 || i == 8) {
                var title = $(this).text();
                $(this).html('<input type="text" class="form-control form-control-sm" id="'+pemakaianKainFilter[(i-1)]+'" />');

                $('input', this).on('keyup change', function() {
                    if (datatable.column(i).search() !== this.value) {
                        datatable
                            .column(i)
                            .search(this.value)
                            .draw();

                        // datatableReload();
                    }
                });
            } else {
                $(this).empty();
            }
        });

        let datatable = $("#datatable").DataTable({
            processing: true,
            serverSide: false,
            ordering: false,
            scrollX: "500px",
            scrollY: "350px",
            pageLength: 25,
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
                        let button = "<div class='d-flex justify-content-center align-item-middle'><button class='btn btn-sm btn-primary' onclick='showDetail(`"+JSON.stringify(row)+"`)'> <i class='fa-solid fa-circle-info'></i> </button></div>";

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
            ],
            footerCallback : function(row, data, start, end, display) {
                var api = this.api(), data;

                // converting to interger to find total
                var intVal = function(i) {
                    return typeof i === 'string' ?
                        i.replace(/[\$,]/g, '') * 1 :
                        typeof i === 'number' ?
                        i : 0;
                };

                // computing column Total of the complete result
                var sumTotalQty = api
                    .column(9, { search:'applied' })
                    .data()
                    .reduce(function(a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                var sumRollIn = api
                    .column(12, { search:'applied' })
                    .data()
                    .reduce(function(a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                var sumQtyIn = api
                    .column(13, { search:'applied' })
                    .data()
                    .reduce(function(a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                var sumRollCutting = api
                    .column(14, { search:'applied' })
                    .data()
                    .reduce(function(a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                var sumQtyCutting = api
                    .column(15, { search:'applied' })
                    .data()
                    .reduce(function(a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                var sumRollBalance = api
                    .column(16, { search:'applied' })
                    .data()
                    .reduce(function(a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                var sumQtyBalance = api
                    .column(17, { search:'applied' })
                    .data()
                    .reduce(function(a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                var sumRollReturn = api
                    .column(19, { search:'applied' })
                    .data()
                    .reduce(function(a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                var sumQtyReturn = api
                    .column(20, { search:'applied' })
                    .data()
                    .reduce(function(a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                // Update footer by showing the total with the reference of the column index
                // $(api.column(0).footer()).html('Total');
                $(api.column(9).footer()).html(sumTotalQty.round(2));
                $(api.column(12).footer()).html(sumRollIn.round(2));
                $(api.column(13).footer()).html(sumQtyIn.round(2));
                $(api.column(14).footer()).html(sumRollCutting.round(2));
                $(api.column(15).footer()).html(sumQtyCutting.round(2));
                $(api.column(16).footer()).html(sumRollBalance.round(2));
                $(api.column(17).footer()).html(sumQtyBalance.round(2));
                $(api.column(19).footer()).html(sumRollReturn ? sumRollReturn.round(2) : 0);
                $(api.column(20).footer()).html(sumQtyReturn ? sumQtyReturn.round(2) : 0);
            }
        });

        function datatableReload() {
            datatable.ajax.reload();

            totalPemakaianRoll();
        }

        function totalPemakaianRoll() {
            $.ajax({
                url: "{{ route("total-pemakaian-roll") }}",
                type: 'get',
                data: {
                    dateFrom : $("#dateFrom").val(),
                    dateTo : $("#dateTo").val(),
                    bppbno : $("#bppbno").val(),
                    bppbdate : $("#bppbdate").val(),
                    tujuan : $("#tujuan").val(),
                    no_ws : $("#no_ws").val(),
                    styleno : $("#styleno").val(),
                    buyer : $("#buyer").val(),
                    id_item : $("#id_item").val(),
                    itemdesc : $("#itemdesc").val(),
                },
                success: function(res) {
                    console.log(res);
                },
                error: function(jqXHR) {
                    console.log(jqXHR);
                }
            });
        }

        function exportExcel (elm) {
            elm.setAttribute('disabled', 'true');
            elm.innerText = "";
            let loading = document.createElement('div');
            loading.classList.add('loading-small');
            elm.appendChild(loading);

            iziToast.info({
                title: 'Exporting...',
                message: 'Data sedang di export. Mohon tunggu...',
                position: 'topCenter'
            });

            let date = new Date();

            let day = date.getDate();
            let month = date.getMonth() + 1;
            let year = date.getFullYear();

            // This arrangement can be altered based on how we want the date's format to appear.
            let currentDate = `${day}-${month}-${year}`;

            $.ajax({
                url: "{{ route("pemakaian-roll-export") }}",
                type: 'post',
                data: {
                    dateFrom : $("#dateFrom").val(),
                    dateTo : $("#dateTo").val()
                },
                xhrFields: { responseType : 'blob' },
                success: function(res) {
                    elm.removeChild(loading);
                    elm.removeAttribute('disabled');
                    let icon = document.createElement('i');
                    icon.classList.add('fa-solid');
                    icon.classList.add('fa-file-excel');
                    elm.appendChild(icon);
                    elm.innerText += " Export";

                    iziToast.success({
                        title: 'Success',
                        message: 'Success',
                        position: 'topCenter'
                    });

                    var blob = new Blob([res]);
                    var link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = "Request Pemakaian Kain "+$("#dateFrom").val()+" - "+$("#dateTo").val()+".xlsx";
                    link.click();
                }, error: function (jqXHR) {
                    elm.removeChild(loading);
                    elm.removeAttribute('disabled');
                    let icon = document.createElement('i');
                    icon.classList.add('fa-solid');
                    icon.classList.add('fa-file-excel');
                    elm.appendChild(icon);
                    elm.innerText += " Export";

                    let res = jqXHR.responseJSON;
                    let message = '';
                    console.log(res.message);
                    for (let key in res.errors) {
                        message += res.errors[key]+' ';
                        document.getElementById(key).classList.add('is-invalid');
                    };
                    iziToast.error({
                        title: 'Error',
                        message: message,
                        position: 'topCenter'
                    });
                }
            });
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
                    data: 'total_sisa_kain'
                },
                {
                    data: 'total_short_roll'
                },
                {
                    data: 'total_short_roll_percentage'
                },
                {
                    data: 'unit'
                },
                {
                    data: 'tanggal_return'
                },
            ]
        });

        function datatableDetailReload() {
            $("#datatable-detail").DataTable().ajax.reload();
        }

        function showDetail(jsonData) {
            console.log(jsonData);

            let data = JSON.parse(jsonData);

            if (document.getElementsByClassName('detail-unit').length > 0) {
                for (let i = 0; i < document.getElementsByClassName('detail-unit').length; i++) {
                    document.getElementsByClassName('detail-unit')[i].innerHTML = data.unit ? data.unit : '-';
                }
            }

            $("#detail_tanggal").val(data.bppbdate ? data.bppbdate : '-').trigger("change");
            $("#detail_no_req").val(data.bppbno ? data.bppbno : '-').trigger("change");
            $("#detail_id_item").val(data.id_item ? data.id_item : '-').trigger("change");
            $("#detail_tujuan").val(data.tujuan ? data.tujuan : '-').trigger("change");
            $("#detail_no_ws").val(data.no_ws ? data.no_ws : '-').trigger("change");
            $("#detail_style").val(data.styleno ? data.styleno : '-').trigger("change");
            $("#detail_buyer").val(data.buyer ? data.buyer : '-').trigger("change");
            $("#detail_qty_req").val(data.qty_req ? data.qty_req : 0).trigger("change");
            // $("#detail_unit").val(data.unit ? data.unit : '-').trigger("change");
            $("#detail_roll_in").val(data.roll_out ? data.roll_out : 0).trigger("change");
            $("#detail_qty_in").val(data.qty_out ? data.qty_out : 0).trigger("change");
            $("#detail_roll_cutting").val(data.total_roll_cutting ? data.total_roll_cutting : 0).trigger("change");
            $("#detail_qty_cutting").val(data.total_pakai_cutting ? data.total_pakai_cutting : 0).trigger("change");
            $("#detail_short_cutting").val(data.total_short_cutting ? data.total_short_cutting : 0).trigger("change");
            $("#detail_roll_balance").val(data.total_roll_balance ? data.total_roll_balance : 0).trigger("change");
            $("#detail_qty_balance").val(data.total_pakai_balance ? data.total_pakai_balance : 0).trigger("change");
            $("#detail_roll_return").val(data.roll_retur ? data.roll_retur : 0).trigger("change");
            $("#detail_qty_return").val(data.qty_retur ? data.qty_retur : 0).trigger("change");

            datatableDetailReload();

            $("#detailPemakaianKainModal").modal('show');
        }

        function exportExcelDetail (elm) {
            let no_req = $("#detail_no_req").val();
            let id_item = $("#detail_id_item").val();

            elm.setAttribute('disabled', 'true');
            elm.innerText = "";
            let loading = document.createElement('div');
            loading.classList.add('loading-small');
            elm.appendChild(loading);

            iziToast.info({
                title: 'Exporting...',
                message: 'Data sedang di export. Mohon tunggu...',
                position: 'topCenter'
            });

            let date = new Date();

            let day = date.getDate();
            let month = date.getMonth() + 1;
            let year = date.getFullYear();

            // This arrangement can be altered based on how we want the date's format to appear.
            let currentDate = `${day}-${month}-${year}`;

            $.ajax({
                url: "{{ route("detail-pemakaian-roll-export") }}",
                type: 'post',
                data: {
                    no_req : $("#detail_no_req").val(),
                    id_item : $("#detail_id_item").val()
                },
                xhrFields: { responseType : 'blob' },
                success: function(res) {
                    elm.removeChild(loading);
                    elm.removeAttribute('disabled');
                    let icon = document.createElement('i');
                    icon.classList.add('fa-solid');
                    icon.classList.add('fa-file-excel');
                    elm.appendChild(icon);
                    elm.innerText += " Export";

                    iziToast.success({
                        title: 'Success',
                        message: 'Success',
                        position: 'topCenter'
                    });

                    var blob = new Blob([res]);
                    var link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = "Pemakaian Kain '"+$("#detail_no_req").val()+"' '"+$("#detail_id_item").val()+"'.xlsx";
                    link.click();
                }, error: function (jqXHR) {
                    elm.removeChild(loading);
                    elm.removeAttribute('disabled');
                    let icon = document.createElement('i');
                    icon.classList.add('fa-solid');
                    icon.classList.add('fa-file-excel');
                    elm.appendChild(icon);
                    elm.innerText += " Export";

                    let res = jqXHR.responseJSON;
                    let message = '';
                    console.log(res.message);
                    for (let key in res.errors) {
                        message += res.errors[key]+' ';
                        document.getElementById(key).classList.add('is-invalid');
                    };
                    iziToast.error({
                        title: 'Error',
                        message: message,
                        position: 'topCenter'
                    });
                }
            });
        }
    </script>
@endsection
