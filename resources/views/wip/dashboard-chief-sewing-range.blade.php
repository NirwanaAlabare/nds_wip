@extends('layouts.index', ["navbar" => false, "footer" => false, "containerFluid" => true])

@section('custom-link')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">

    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

    <!-- Apex Charts -->
    <link rel="stylesheet" href="{{ asset('plugins/apexcharts/apexcharts.css') }}">

    <!-- Swiper -->
    <link rel="stylesheet" href="{{ asset('plugins/swiper/css/swiper-bundle.min.css') }}" />

    <style>
        .dataTables_wrapper .dataTables_processing {
            position: absolute;
            top: 50px !important;
            /* background: #FFFFCC;
            border: 1px solid black; */
            border-radius: 3px;
            font-weight: bold;
        }

        /* SWIPPER */
        swiper-container {
            width: 100%;
            height: 100%;
            background-color: #ffffff !important;
        }

        swiper-slide {
            background: #ffffff;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        swiper-slide img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        swiper-container {
            width: 100%;
            height: 100%;
            background-color: inherit;
        }

        swiper-slide {
            display: flex;
            justify-content: center;
            align-items: start;
            border-radius: 10px;
            min-height: 95vh;
        }

        swiper-slide img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .horizontal-grid {
            background: #919191;
            display: grid;
            background: inherit;
            width: 100%;
            grid-template-columns: repeat(30, 1fr);
            align-items: stretch;
            grid-gap: 3px;
        }

        .horizontal-grid-box {
            background: #ffffff;
        }
    </style>
@endsection

@section('content')
    <input type="hidden" id="from" value="{{ $from ? $from : date("Y-m-d") }}">
    <input type="hidden" id="to" value="{{ $to ? $to : date("Y-m-d") }}">
    <div class="row">
        <div class="col-md-6">
            <div id="chart-eff"></div>
        </div>
        <div class="col-md-6">
            <div id="chart-rft"></div>
        </div>
    </div>
    <div class="table-responsive">
        <div class="horizontal-grid" id="chief-table">
            <div>
                <div class="d-flex flex-column" id="chief-table-header" style="postion: sticky; gap: 3px;">
                    <div class="d-flex justify-content-center align-items-center horizontal-grid-box" style="height: 50px;">
                        <span class="text-nowrap fw-bold">NAMA CHIEF</span>
                    </div>
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

    <!-- Apex Charts -->
    <script src="{{ asset('plugins/apexcharts/apexcharts.min.js') }}"></script>

    <!-- Select2 -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>

    <!-- Swiper  -->
    <script src="{{ asset('plugins/swiper/js/swiper-bundle.min.js') }}"></script>
    <script src="{{ asset('plugins/swiper/js/swiper-element-bundle.min.js') }}"></script>

    <script>
        // Set Custom Dashboard View
        document.body.style.maxHeight = "100vh";
        // document.body.style.overflow = "hidden";

        document.querySelector(".content-wrapper").classList.remove("pt-3");
        document.querySelector(".content-wrapper").classList.add("pt-1");

        // Slide
        const swiper = new Swiper('.swiper', {
            direction: 'vertical',
            loop: true,

            pagination: {
                el: '.swiper-pagination',
            },

            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },

            scrollbar: {
                el: '.swiper-scrollbar',
            },
        });

        $('.select2').select2()
        $('.select2bs4').select2({
            theme: 'bootstrap4'
        })

        document.getElementById("loading").classList.remove("d-none");

        $('document').ready(async () => {
            $.ajax({
                url: "{{ route("dashboard-chief-sewing-range-data") }}",
                type: "get",
                data: {
                    from: $("#from").val(),
                    to: $("#to").val()
                },
                dataType: "json",
                success: async function (response) {
                    let dateOutput = [];

                    // Chief Group By
                    let chiefEfficiency = Object.values(Object.groupBy(response, ({ chief_id }) => chief_id));

                    // Chief Daily Summary
                    let chiefDailyEfficiency = [];
                    let chiefDailyRft = [];
                    let chiefDaily = [];
                    chiefEfficiency.forEach(element => {
                        // Date Output
                        let dateOutput = [];
                        element.reduce(function(res, value) {
                            if (!res[value.tanggal]) {
                                res[value.tanggal] = { tanggal: value.tanggal, mins_avail: 0, mins_prod: 0, output: 0, rft: 0 };
                                dateOutput.push(res[value.tanggal]);
                            }
                            // res[value.tanggal].mins_avail += value.tanggal == formatDate(new Date()) ? Number(value.cumulative_mins_avail) : Number(value.mins_avail);
                            res[value.tanggal].mins_avail += Number(value.cumulative_mins_avail);
                            res[value.tanggal].mins_prod += Number(value.mins_prod);
                            res[value.tanggal].output += Number(value.output);
                            res[value.tanggal].rft += Number(value.rft);

                            return res;
                        }, {});

                        let dateOutputFilter = dateOutput.filter((item) => item.mins_avail > 0 && item.mins_prod > 0);
                        let currentFilter = dateOutputFilter.filter((item) => item.tanggal == formatDate(new Date()));
                        let currentData = currentFilter.length > 0 ? currentFilter[0] : dateOutputFilter[dateOutputFilter.length-1];

                        // Format the data as [{ x: date, y: efficiency }]
                        let formattedData = dateOutputFilter.map(item => {
                            return {
                                x: new Date(item.tanggal), // Date as x
                                y: ((item.mins_prod / item.mins_avail) * 100).toFixed(2) // Efficiency as y
                            };
                        });

                        // Format the data as [{ x: date, y: rft }]
                        let formattedRftData = dateOutputFilter.map(item => {
                            return {
                                x: new Date(item.tanggal), // Date as x
                                y: ((item.rft / item.output) * 100).toFixed(2) // Efficiency as y
                            };
                        });

                        chiefDailyEfficiency.push({"name": element[0].chief_name ? element[0].chief_name : 'KOSONG', "data": formattedData});
                        chiefDailyRft.push({"name": element[0].chief_name ? element[0].chief_name : 'KOSONG', "data": formattedRftData});
                        chiefDaily.push({"name": element[0].chief_name ? element[0].chief_name : 'KOSONG', "eff": formattedData, "rft": formattedRftData, "currentEff": (currentData ? currentData.mins_prod/currentData.mins_avail*100 : 0)});
                    });

                    let sortChiefDaily = chiefDaily.sort(function(a,b){
                            if (a.currentEff < b.currentEff) {
                                return 1;
                            }
                            if (a.currentEff > b.currentEff) {
                                return -1;
                            }
                            return 0;
                        });

                    // Chart
                    generateEffChart(chiefDailyEfficiency);
                    generateRftChart(chiefDailyRft);

                    // Table
                    chiefTable(sortChiefDaily);

                    document.getElementById("loading").classList.add("d-none");
                },
                error: function (jqXHR) {
                    // console.error(jqXHR);

                    document.getElementById("loading").classList.add("d-none");
                }
            });
        });

        // var intervalData = setInterval(() => {
        //     updateData();
        // }, 60000);

        function generateEffChart(data) {

            let chartEffElement = document.getElementById("chart-eff");

            let chiefDailyEfficiencyChart = [];

            chartEffElement.classList.add("chief-daily-efficiency-chart");

            var options = {
                series: data,
                chart: {
                    height: 350,
                    type: 'line',
                    zoom: {
                        enabled: false
                    },
                    toolbar: {
                        show: false
                    }
                },
                xaxis: {
                    type: 'datetime',
                },
                dataLabels: {
                    enabled: true
                },
                title: {
                    text: 'Chief Efficiency',
                    align: 'center'
                },
                legend: {
                    tooltipHoverFormatter: function(val, opts) {
                        return val + ' - <strong>' + opts.w.globals.series[opts.seriesIndex][opts.dataPointIndex] + '</strong>'
                    }
                },
                grid: {
                    borderColor: '#f1f1f1',
                }
            };

            var chart = new ApexCharts(document.querySelector("#chart-eff"), options);
            chart.render();
        }

        function generateRftChart(data) {

            let chartRftElement = document.getElementById("chart-rft");

            let chiefDailyRftChart = [];

            chartRftElement.classList.add("chief-daily-rft-chart");

            var options = {
                series: data,
                chart: {
                    height: 350,
                    type: 'line',
                    zoom: {
                        enabled: false
                    },
                    toolbar: {
                        show: false
                    }
                },
                xaxis: {
                    type: 'datetime',
                },
                dataLabels: {
                    enabled: true
                },
                title: {
                    text: 'Chief RFT',
                    align: 'center'
                },
                legend: {
                    tooltipHoverFormatter: function(val, opts) {
                        return val + ' - <strong>' + opts.w.globals.series[opts.seriesIndex][opts.dataPointIndex] + '</strong>'
                    }
                },
                grid: {
                    borderColor: '#f1f1f1',
                }
            };

            var chart = new ApexCharts(document.querySelector("#chart-rft"), options);
            chart.render();
        }

        async function chiefTable(dataEff) {
            console.log(dataEff);

            let longestData = dataEff.reduce((a,b)=>a.eff.length>b.eff.length?a:b).eff.map((item)=>item["x"]);

            console.log("longestData", longestData);

            let parentElement = document.getElementById("chief-table");
            let parentElementHeader = document.getElementById("chief-table-header");
            let parentElementFooter = "";

            let headerElement = '';
            let tableHtml = '';
            let i = 0;
            longestData.forEach((value, index) => {
                let formattedValue = formatDate(value);

                let horizontalHtmlStart = `
                    <div>
                        <div class="d-flex flex-column" style="width: 150px; gap: 3px;">
                            <div class="d-flex flex-column" style="height: 50px; gap: 3px;">
                                <div class="d-flex justify-content-center align-items-center horizontal-grid-box h-100" style="gap: 3px; ">
                                    <span class="text-nowrap text-center fw-bold">`+formattedValue+`</span>
                                </div>
                                <div class="d-flex justify-content-center align-items-center" style="gap: 3px; ">
                                    <div class="horizontal-grid-box text-center w-50 h-100">
                                        <span class="text-nowrap fw-bold">Efficiency</span>
                                    </div>
                                    <div class="horizontal-grid-box text-center w-50 h-100">
                                        <span class="text-nowrap fw-bold">RFT</span>
                                    </div>
                                </div>
                            </div>
                `;

                dataEff.forEach((element, i) => {
                    if (index == 0) {
                        parentElementHeader.innerHTML += `
                            <div class="horizontal-grid-box d-flex justify-content-center align-items-center" style="height: 50px; gap: 3px;">
                                <span class="text-nowrap fw-bold">`+element.name+`</span>
                            </div>
                        `;
                    }

                    let eff = element.eff.filter((item) => formatDate(item.x) == formatDate(value))[0];
                    let rft = element.rft.filter((item) => formatDate(item.x) == formatDate(value))[0];

                    let verticalHtml = `
                        <div>
                            <div class="d-flex justify-content-between align-items-center" style="height: 50px; gap: 3px;">
                                <div class="horizontal-grid-box d-flex justify-content-center align-items-center w-50 h-100">
                                    <span class="text-nowrap">`+(eff ? eff.y+' %' : '-')+`</span>
                                </div>
                                <div class="horizontal-grid-box d-flex justify-content-center align-items-center w-50 h-100">
                                    <span class="text-nowrap">`+(rft ? rft.y+' %' : '-')+`</span>
                                </div>
                            </div>
                        </div>
                    `;

                    horizontalHtmlStart += verticalHtml;

                    if (index == (longestData.length-1)) {
                        parentElementFooter += `
                            <div class="horizontal-grid-box d-flex justify-content-center align-items-center" style="height: 50px; gap: 3px;">
                                <span class="text-nowrap fw-bold">`+(i+1)+`</span>
                            </div>
                        `;

                        i++;
                    }
                });

                let horizontalHtmlEnd = `
                        </div>
                    </div>
                `;

                tableHtml += horizontalHtmlStart+horizontalHtmlEnd;
            });

            parentElement.innerHTML += tableHtml;

            parentElement.innerHTML += `
                <div>
                    <div class="d-flex flex-column" id="chief-table-header" style="postion: sticky; gap: 3px;">
                        <div class="d-flex justify-content-center align-items-center horizontal-grid-box" style="height: 50px;">
                            <span class="text-nowrap fw-bold">RANK</span>
                        </div>
                    </div>
                    `+parentElementFooter+`
                </div>`;
        }
    </script>
@endsection
