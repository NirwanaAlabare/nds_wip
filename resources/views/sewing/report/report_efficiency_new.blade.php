@extends('layouts.index')

@section('custom-link')
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css">
    <link href="https://cdn.datatables.net/2.0.3/css/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/fixedcolumns/5.0.0/css/fixedColumns.bootstrap4.min.css" rel="stylesheet">
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.3.1.js"></script>

    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@endsection

@section('content')
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-chart-area"></i> Report Efficiency</h5>
        </div>
        <div class="card-body">
            <div class="d-flex align-items-end gap-3 mb-3">
                <div class="mb-3">
                    <label class="form-label"><small><b>Tgl. Awal</b></small></label>
                    <input type="date" class="form-control form-control-sm " id="tgl-awal" name="tgl_awal"
                        value="{{ date('Y-m-d') }}">
                </div>
                <div class="mb-3">
                    <label class="form-label"><small><b>Tgl. Akhir</b></small></label>
                    <input type="date" class="form-control form-control-sm " id="tgl-akhir" name="tgl_akhir"
                        value="{{ date('Y-m-d') }}">
                </div>
                <div class="mb-3">
                    <a onclick="dataTableReload()" class="btn btn-outline-primary position-relative btn-sm">
                        <i class="fas fa-search fa-sm"></i>
                    </a>
                </div>
                <div class="mb-3">
                    <a onclick="export_excel_tracking()" class="btn btn-outline-success position-relative btn-sm">
                        <i class="fas fa-file-excel fa-sm"></i>
                        Export Excel
                    </a>
                </div>
            </div>

            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-striped table-sm w-100 text-nowrap">
                    <thead class="table-success">
                        <tr style='text-align:center; vertical-align:middle'>
                            <th>Tgl. Plan</th>
                            <th>Tgl. Trans</th>
                            <th>Line</th>
                            <th>WS</th>
                            <th>Buyer</th>
                            <th>Style</th>
                            <th>SMV</th>
                            <th>MP</th>
                            <th>Mins Avail</th>
                            <th>Target</th>
                            <th>Output</th>
                            <th>Currency</th>
                            <th>CM Price</th>
                            <th>Earning</th>
                            <th>Mins. Prod</th>
                            <th>Efficiency</th>
                            <th>RFT</th>
                            <th>Jam Kerja Aktual</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th colspan="3"> Total </th>
                            <th colspan="4"></th>
                            <th> <input type = 'text' class="form-control form-control-sm" style="width:75px" readonly
                                    id = 'total_mp'> </th>
                            <th></th>
                            <th> <input type = 'text' class="form-control form-control-sm" style="width:75px" readonly
                                    id = 'total_qty_target'> </th>
                            <th> <input type = 'text' class="form-control form-control-sm" style="width:75px" readonly
                                    id = 'total_qty_output'> </th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th colspan="4"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('custom-script')
    <!-- DataTables & Plugins -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <script src="https://cdn.datatables.net/2.0.3/js/dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/2.0.3/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/fixedcolumns/5.0.0/js/dataTables.fixedColumns.min.js"></script>
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
            containerCssClass: 'form-control-sm rounded'
        });
    </script>
    <script>
        $(document).ready(() => {
            dataTableReload();
        });

        function notif() {
            alert("Maaf, Fitur belum tersedia!");
        }

        let datatable = $("#datatable").DataTable({
            scrollY: "300px",
            scrollX: true,
            scrollCollapse: true,
            paging: false,
            ordering: false,
            fixedColumns: {
                leftColumns: 3 // Fix the first two columns
            },
            ajax: {
                url: '{{ route('reportEfficiencynew') }}',
                dataType: 'json',
                dataSrc: 'data',
                data: function(d) {
                    d.tgl_awal = $('#tgl-awal').val();
                    d.tgl_akhir = $('#tgl-akhir').val();
                },
            },
            columns: [{
                    data: 'tgl_plan_fix'

                },
                {
                    data: 'tgl_trans_fix'

                },
                {
                    data: 'sewing_line'

                },
                {
                    data: 'kpno'
                },
                {
                    data: 'buyer'
                },
                {
                    data: 'styleno'
                },
                {
                    data: 'smv'
                },
                {
                    data: 'man_power'
                },
                {
                    data: 'mins_avail'
                },
                {
                    data: 'target'
                },
                {
                    data: 'tot_output'
                },
                {
                    data: 'curr'
                },
                {
                    data: 'cm_price'
                },
                {
                    data: 'earning'
                },
                {
                    data: 'mins_prod'
                },
                {
                    data: 'eff_line'
                },
                {
                    data: 'rfts'
                },
                {
                    data: 'jam_kerja_act'
                },
            ],
            columnDefs: [{
                "className": "align-left",
                "targets": "_all"
            }, ],
            drawCallback: function(settings) {
                var api = this.api();
                var intVal = function(i) {
                    return typeof i === 'string' ?
                        i.replace(/[\$,]/g, '') * 1 :
                        typeof i === 'number' ?
                        i : 0;
                };
                // Compute column Total of the complete result

                var sumTotalMP = api
                    .column(7, {
                        search: 'applied'
                    })
                    .data()
                    .reduce(function(a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                var sumTotalMinsAvail = api
                    .column(8, {
                        search: 'applied'
                    })
                    .data()
                    .reduce(function(a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                var sumTotalTarget = api
                    .column(9, {
                        search: 'applied'
                    })
                    .data()
                    .reduce(function(a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                var sumTotalOutput = api
                    .column(10, {
                        search: 'applied'
                    })
                    .data()
                    .reduce(function(a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                var sumTotalMinProd = api
                    .column(14, {
                        search: 'applied'
                    })
                    .data()
                    .reduce(function(a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                // Update footer for the "MP" column

                $(api.column(7).footer()).html(sumTotalMP);
                $(api.column(8).footer()).html(sumTotalMinsAvail.toFixed(2));
                $(api.column(9).footer()).html(sumTotalTarget);
                $(api.column(10).footer()).html(sumTotalOutput);
                $(api.column(14).footer()).html(sumTotalMinProd.toFixed(2));
            }
        });


        function dataTableReload() {
            datatable.ajax.reload();
        }

        function export_excel_tracking() {
            let tgl_awal_f = document.getElementById("tgl-awal").value;
            let tgl_akhir_f = document.getElementById("tgl-akhir").value;
            Swal.fire({
                title: 'Please Wait...',
                html: 'Exporting Data...',
                didOpen: () => {
                    Swal.showLoading()
                },
                allowOutsideClick: false,
            });

            $.ajax({
                type: "get",
                url: '{{ route('export_excel_rep_eff_new') }}',
                data: {
                    tgl_awal: tgl_awal_f,
                    tgl_akhir: tgl_akhir_f
                },
                xhrFields: {
                    responseType: 'blob'
                },
                success: function(response) {
                    {
                        swal.close();
                        Swal.fire({
                            title: 'Data Sudah Di Export!',
                            icon: "success",
                            showConfirmButton: true,
                            allowOutsideClick: false
                        });
                        var blob = new Blob([response]);
                        var link = document.createElement('a');
                        link.href = window.URL.createObjectURL(blob);
                        link.download = "Laporan Eff " + tgl_awal_f + " - " + tgl_akhir_f + ".xlsx";
                        link.click();

                    }
                },
            });
        }
    </script>
@endsection
