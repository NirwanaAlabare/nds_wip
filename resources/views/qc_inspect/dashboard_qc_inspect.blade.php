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
                <div class="p-3 rounded shadow-sm">
                    <div id="totalQty" class="display-7 text-primary fw-bold">
                        Total Qty: 0 pcs
                    </div>
                </div>

                <div class="p-2 bd-highlight">
                    <select class="form-control select2bs4 form-control-sm" id="cbothn" name="cbothn"
                        style="width: 100%;" onchange="load_chart();">
                        <option selected="selected" value="" disabled="true"></option>
                        @foreach ($data_tahun as $datatahun)
                            <option value="{{ $datatahun->isi }}">
                                {{ $datatahun->tampil }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="card-body">
                <div id="chart" style="height: 100px; width: 100%;"></div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12 mb-4">
            <div class="card">
                <div class="card-header fw-bold"> Top 5 Buyer
                </div>
                <div class="card-body">
                    <div id="chart_buyer" style="height: 350px; width: 100%;"></div>

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
            let year = today.getFullYear(); // Get current year
            $("#cbobln").val(month).trigger('change');
            $("#cbothn").val(year).trigger('change');
            initChart();
            load_chart();
            loadTopBuyerChart();
            // Reload chart data on year change
            $('#cbothn').on('change', function() {
                load_chart();
                loadTopBuyerChart();
            });
        });

        function formatNumberWithDots(x) {
            // Ensure the value is a clean integer first
            const num = Math.floor(Number(x));
            return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }

        let chart;

        // Initialize chart once on page load
        function initChart() {
            const options = {
                series: [],
                chart: {
                    height: 350,
                    type: 'bar',
                    animations: {
                        enabled: true,
                        easing: 'easeinout',
                        speed: 800,
                        animateGradually: {
                            enabled: true,
                            delay: 150
                        },
                        dynamicAnimation: {
                            enabled: true,
                            speed: 500
                        }
                    },
                    events: {
                        click: function(chart, w, e) {
                            // Optional click event
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
                    formatter: function(val) {
                        return formatNumberWithDots(Math.round(val));
                    },
                    style: {
                        colors: ['#333']
                    },
                    position: 'top'
                },
                yaxis: {
                    labels: {
                        formatter: function(val) {
                            return formatNumberWithDots(Math.round(val));
                        }
                    }
                },
                legend: {
                    show: false
                },
                xaxis: {
                    categories: [], // start empty, will update dynamically
                    labels: {
                        style: {
                            fontSize: '12px'
                        }
                    }
                }
            };

            chart = new ApexCharts(document.querySelector("#chart"), options);
            chart.render();
        }

        function load_chart() {
            $.ajax({
                url: '{{ route('get_data_dash_marketing') }}',
                type: 'get',
                dataType: 'json',
                data: {
                    tahun: $('#cbothn').val()
                },
                success: function(res) {
                    const categories = res.map(element => element.x);
                    const yValues = res.map(element => element.y);

                    const totalQty = yValues.reduce((acc, val) => acc + val, 0);
                    const formattedTotal = formatNumberWithDots(totalQty);
                    $('#totalQty').text('Total Qty Sales Order: ' + formattedTotal + ' pcs');

                    chart.updateOptions({
                        xaxis: {
                            categories: categories
                        }
                    }, false, true);

                    chart.updateSeries([{
                        name: 'Total Qty Order',
                        data: yValues
                    }], true);
                },


                error: function(err) {
                    console.error("Failed to load chart data", err);
                }
            });
        }

        let chartBuyer; // Global variable

        function loadTopBuyerChart() {
            $.ajax({
                url: '{{ route('get_data_dash_marketing_top_buyer') }}',
                type: 'GET',
                dataType: 'json',
                data: {
                    tahun: $('#cbothn').val()
                },
                success: function(res) {
                    const categories = res.map(item => item.Supplier);
                    const dataSeries = res.map(item => parseInt(item.qty_order));

                    const options = {
                        series: [{
                            data: dataSeries
                        }],
                        legend: {
                            show: false // Disable the legend
                        },
                        chart: {
                            type: 'bar',
                            height: 300
                        },
                        plotOptions: {
                            bar: {
                                barHeight: '100%',
                                distributed: true, // ✅ Enables individual bar colors
                                horizontal: true,
                                dataLabels: {
                                    position: 'bottom'
                                }
                            }
                        },
                        // ✅ Use a wider range of distinct bar colors
                        colors: ['#008FFB', '#00E396', '#FEB019', '#FF4560', '#775DD0'],
                        dataLabels: {
                            enabled: true,
                            textAnchor: 'start',
                            style: {
                                // ✅ Set text color to black for better readability
                                colors: ['#000']
                            },
                            formatter: function(val, opt) {
                                return opt.w.globals.labels[opt.dataPointIndex] + ": " +
                                    formatNumberWithDots(val) + ' pcs';
                            },
                            offsetX: 0,
                            dropShadow: {
                                enabled: false // ✅ Disable drop shadow for cleaner text
                            }
                        },
                        stroke: {
                            width: 1,
                            colors: ['#fff']
                        },
                        xaxis: {
                            categories: categories,
                            labels: {
                                style: {
                                    colors: '#000',
                                    fontWeight: 'bold'
                                },
                                formatter: function(val) {
                                    return formatNumberWithDots(val);
                                }
                            }
                        },
                        yaxis: {
                            labels: {
                                show: false
                            }
                        },
                        title: {
                            text: 'Top 5 Buyers',
                            align: 'center',
                            style: {
                                color: '#000', // ✅ Title text color
                                fontWeight: 'bold'
                            }
                        },
                        tooltip: {
                            theme: 'light', // ✅ Use light tooltip for better contrast
                            x: {
                                show: false
                            },
                            y: {
                                title: {
                                    formatter: () => ''
                                }
                            }
                        }
                    };

                    // Render or update the chart
                    if (chartBuyer) {
                        chartBuyer.destroy(); // Force re-render with full styling
                    }

                    chartBuyer = new ApexCharts(document.querySelector("#chart_buyer"), options);
                    chartBuyer.render();
                },
                error: function(err) {
                    console.error("Error loading Top Buyer chart:", err);
                }
            });
        }
    </script>
@endsection
