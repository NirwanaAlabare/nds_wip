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
                    <h5 class="card-title fw-bold mb-0 text-center">Summary</h5>
                </div>
                <div class="p-2 bd-highlight">
                    <select class="form-control select2bs4 form-control-sm" id="cbobln" name="cbobln"
                        style="width: 100%;" onchange="gettot()">
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
                        <span class="info-box-icon bg-info elevation-1"><i class="fas fa-store-alt"></i></span>
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

    {{-- <div id="chartContainer" style="height: 370px; width: 100%;"></div> --}}
    <div class="card">
        <div class="card-header">
            <div class="card-body">
                <div id="chart" style="height: 370px; width: 100%;"></div>
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
            // dataTableReload();
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
    </script>
@endsection
