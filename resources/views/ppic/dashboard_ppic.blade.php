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
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <div class="p-2 bd-highlight">
                    <h5 class="card-title fw-bold mb-0 text-center">Summary Order 2025</h5>
                </div>
            </div>
            <div class="card-body">
                <div id="chart" style="height: 100px; width: 100%;"></div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <div class="p-2 bd-highlight">
                    <h5 class="card-title fw-bold mb-0 text-center">Summary</h5>
                </div>
                <div class="p-2 bd-highlight">
                    <select class="form-control select2bs4 form-control-sm" id="cbobln" name="cbobln"
                        style="width: 100%;" onchange="gettot();dataTableReload();">
                        <option selected="selected" value="" disabled="true"></option>
                        @foreach ($data_bulan as $databulan)
                            <option value="{{ $databulan->isi }}">
                                {{ $databulan->tampil }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-success bg-gradient elevation-1"><i
                                class="fas fa-shopping-cart"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Qty Order</span>
                            <span class="info-box-number">
                                <label id="qty_order" id="qty_order">0</label>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-warning bg-gradient  elevation-1"><i class="fas fa-list"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total PO</span>
                            <span class="info-box-number">
                                <label id="tot_po" id="tot_po">0</label>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-info elevation-1"><i class="fas fa-user-tag"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Buyer</span>
                            <span class="info-box-number">
                                <label id="tot_buyer" id="tot_buyer">0</label>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-primary elevation-1"><i class="fas fa-check"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Order Output</span>
                            <span class="info-box-number">
                                <label id="tot_out" id="tot_out">0</label>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <div class="p-2 bd-highlight">
                    <h5 class="card-title fw-bold mb-0 text-center">Summary Shipment :</h5>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="datatable" class="table table-bordered table-striped table-sm w-100 text-nowrap">
                        <thead class="table-primary">
                            <tr style='text-align:center; vertical-align:middle'>
                                <th>Tgl. Shipment</th>
                                <th>Buyer</th>
                                <th>PO</th>
                                <th>WS</th>
                                <th>Color</th>
                                <th>Style</th>
                                <th>Qty PO</th>
                                <th>Qty Packing Out</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th colspan="6"></th>
                                <th> <input type = 'text' class="form-control form-control-sm" style="width:75px" readonly
                                        id = 'total_qty_po'> </th>
                                <th> <input type = 'text' class="form-control form-control-sm" style="width:75px" readonly
                                        id = 'total_qty_p_out'> </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
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
    {{-- <script src="https://cdn.canvasjs.com/canvasjs.min.js"></script> --}}
    <script src="{{ asset('plugins/apexcharts/apexcharts.min.js') }}"></script>
    <script>
        function notif() {
            alert("Maaf, Fitur belum tersedia!");
        }
    </script>
    <script>
        $(document).ready(() => {
            const today = new Date();
            let month = today.getMonth() + 1;
            $("#cbobln").val(month).trigger('change');
            dataTableReload();
            // gettot();
            // canvas();
        });


        $.ajax({
            url: '{{ route('get_data_dash_ppic') }}',
            type: 'get',
            dataType: 'json',
            success: function(res) {
                let totalDefect = 0;
                let dataArr = [];
                res.forEach(element => {
                    dataArr.push({
                        'x': element.x,
                        'y': element.y
                    });
                });

                // Update the series with a name

                chart.updateSeries([{

                    name: 'Total Qty Order', // Set the series name here

                    data: dataArr

                }], true);

                chart.updateSeries([{
                    data: dataArr
                }], true);
            },
            error: function(jqXHR) {
                let res = jqXHR.responseJSON;
                console.error(res.message);
                iziToast.error({
                    title: 'Error',
                    message: res.message,
                    position: 'topCenter'
                });
            }
        });



        var options = {
            series: [],
            chart: {
                height: 350,
                type: 'bar',
                events: {
                    click: function(chart, w, e) {
                        // console.log(chart, w, e)
                    }
                }
            },
            plotOptions: {
                bar: {
                    columnWidth: '45%',
                    distributed: true,
                }
            },
            dataLabels: {
                enabled: true,
                style: {
                    colors: ['#333']
                },
                // formatter: function(val, opts) {
                //     return val.toLocaleString()
                // },
                position: 'top'
            },
            legend: {
                show: false
            },
            xaxis: {
                categories: [
                    ['Januari'],
                    ['Februari'],
                    ['Maret'],
                    ['April'],
                    ['Mei'],
                    ['Juni'],
                    ['Juli'],
                    ['Agustus'],
                    ['September'],
                    ['Oktober'],
                    ['November'],
                    ['Desember'],
                ],
                labels: {
                    style: {
                        fontSize: '12px'
                    }
                }
            }
        };

        var chart = new ApexCharts(document.querySelector("#chart"), options);
        chart.render();

        function gettot() {
            let blnFilter = $('#cbobln').val();
            $.ajax({
                url: '{{ route('show_tot_dash_ppic') }}',
                method: 'get',
                data: {
                    blnFilter: blnFilter
                },
                dataType: 'json',
                success: function(response) {
                    document.getElementById('qty_order').innerHTML = response.qty_order;
                    document.getElementById('tot_buyer').innerHTML = response.tot_buyer;
                    document.getElementById('tot_po').innerHTML = response.tot_po;
                    document.getElementById('tot_out').innerHTML = response.tot_out;
                },
                error: function(request, status, error) {
                    alert(request.responseText);
                },
            });
        };

        function dataTableReload() {
            datatable.ajax.reload();
        }



        let datatable = $("#datatable").DataTable({
            "footerCallback": function(row, data, start, end, display) {
                var api = this.api(),
                    data;

                // converting to interger to find total
                var intVal = function(i) {
                    return typeof i === 'string' ?
                        i.replace(/[\$,]/g, '') * 1 :
                        typeof i === 'number' ?
                        i : 0;
                };

                // computing column Total of the complete result
                var sumTotalPO = api
                    .column(6)
                    .data()
                    .reduce(function(a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                var sumTotalTr = api
                    .column(7)
                    .data()
                    .reduce(function(a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);


                // Update footer by showing the total with the reference of the column index
                $(api.column(0).footer()).html('Total');
                $(api.column(6).footer()).html(sumTotalPO);
                $(api.column(7).footer()).html(sumTotalTr);
            },

            ordering: true,
            processing: true,
            serverSide: true,
            paging: false,
            searching: true,
            scrollY: '300px',
            scrollX: '300px',
            scrollCollapse: true,
            ajax: {
                url: '{{ route('show_data_dash_ship_hr_ini') }}',
                data: function(d) {
                    d.blnFilter = $('#cbobln').val();
                },
            },
            columns: [{
                    data: 'tgl_shipment_fix'

                }, {
                    data: 'buyer'

                },
                {
                    data: 'po'

                }, {
                    data: 'ws'
                },
                {
                    data: 'color'
                },
                {
                    data: 'styleno'
                },
                {
                    data: 'qty_po'
                },
                {
                    data: 'qty_packing_out'
                },
            ],
            columnDefs: [{
                "className": "align-middle",
                "targets": "_all"
            }, ]
        });
    </script>
@endsection
