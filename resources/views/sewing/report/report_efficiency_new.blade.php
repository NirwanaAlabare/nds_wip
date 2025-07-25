@extends('layouts.index')

@section('custom-link')
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
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-chart-area"></i> Report Efficiency</h5>
        </div>
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-end gap-3 mb-3">
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
                <div class="mb-3">
                    <button class="btn btn-sb-secondary btn-sm" data-bs-toggle="modal"
                        data-bs-target="#scrapFromOrtaxModal">
                        <i class="fa fa-download"></i>
                        Download Kurs
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-striped table w-100 text-nowrap">
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
                            <th>Kurs Tengah BI</th>
                            <th>Earning Rupiah</th>
                            <th>Mins. Prod</th>
                            <th>Efficiency</th>
                            <th>RFT</th>
                            <th>Jam Kerja Aktual</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th colspan="4"> Total </th>
                            <th colspan="3"></th>
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
    <div class="modal fade" id="scrapFromOrtaxModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-sb text-light">
                    <h1 class="modal-title fs-6">Download Data Kurs BI</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div>
                        <label>Tanggal</label>
                        <input type="date" class="form-control" value="{{ date('Y-m-d') }}" id="date">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-dark" id="scrapFromOrtax">Download</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('custom-script')
    <!-- DataTables & Plugins -->
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

        function intVal(i) {
            return typeof i === 'string' ?
                parseFloat(i.replace(/[\Rp\s,.]/g, '').replace(',', '.')) || 0 :
                typeof i === 'number' ?
                i :
                0;
        }


        let datatable = $("#datatable").DataTable({
            scrollY: "300px",
            scrollX: true,
            processing: true,
            serverSide: false,
            scrollCollapse: true,
            paging: false,
            ordering: false,
            fixedColumns: {
                leftColumns: 4 // Fix the first two columns
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
                    data: 'kurs_tengah'
                },
                {
                    data: 'tot_earning_rupiah'
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


                // Create a map to hold unique lines and their corresponding man_power
                var lineManPower = {};
                var uniqueLines = new Set();

                // Loop through the rows and sum man_power by unique line
                api.rows({
                    search: 'applied'
                }).every(function(rowIdx) {
                    var data = this.data();
                    var line = data.sewing_line; // Get the sewing line
                    var manPower = intVal(data.man_power); // Get the man_power

                    // If the line hasn't been counted yet, add it to the set and sum the man_power
                    if (!uniqueLines.has(line)) {
                        uniqueLines.add(line);
                        lineManPower[line] = manPower; // Initialize the man_power for this line
                    }
                });

                // Calculate the total man_power by summing the values in the lineManPower object
                var totalManPower = Object.values(lineManPower).reduce((a, b) => a + b, 0);

                // Update footer for the "MP" column
                $(api.column(7).footer()).html(totalManPower);


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

                var sumTotalEarning = api
                    .column(15, {
                        search: 'applied'
                    })
                    .data()
                    .reduce(function(a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                var formattedEarning = new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR',
                    minimumFractionDigits: 2
                }).format(sumTotalEarning);

                var sumTotalMinProd = api
                    .column(16, {
                        search: 'applied'
                    })
                    .data()
                    .reduce(function(a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                // Update footer for the "MP" column

                // $(api.column(7).footer()).html(sumTotalMP);
                $(api.column(8).footer()).html(sumTotalMinsAvail.toFixed(2));
                $(api.column(9).footer()).html(sumTotalTarget);
                $(api.column(10).footer()).html(sumTotalOutput);
                $(api.column(15).footer()).html(formattedEarning);
                $(api.column(16).footer()).html(sumTotalMinProd.toFixed(2));
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

        document.getElementById('scrapFromOrtax').addEventListener('click', () => {
            iziToast.info({
                title: 'Downloading...',
                message: 'Harap tunggu',
                multiline: true
            });

            $.ajax({
                type: "POST",
                url: '{!! route('kursBi.scrapData') !!}',
                data: {
                    date: $('#date').val()
                },
                success: function(res) {
                    console.log(res);
                    if (res["status"] == "success") {
                        let success = "";
                        res["tanggalSuccess"].forEach(element => {
                            success += "Tanggal " + element + " Success <br>"
                        });

                        let unavailable = "";
                        res["tanggalUnavailable"].forEach(element => {
                            unavailable += "Tanggal " + element + " Not Found <br>"
                        });

                        console.log(success + unavailable);

                        Swal.fire({
                            icon: res["status"],
                            title: "Data Kurs BI berhasil diperbaharui.",
                            html: success + unavailable,
                            confirmButtonText: 'OK'
                        });

                        iziToast.hide({
                            transitionOut: 'fadeOutUp'
                        }, document.querySelector('.iziToast'));

                        $('#kurs-bi-table').DataTable().ajax.reload();
                    } else {
                        iziToast.error({
                            message: res["message"],
                            multiline: true
                        });
                    }
                }
            });
        });
    </script>
@endsection
