@extends('layouts.index', ["containerFluid" => true])

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
            display: flex;
            background: inherit;
            width: 100%;
            align-items: stretch;
            grid-gap: 3px;
        }

        .horizontal-grid-box {
            background: #ffffff;
        }
    </style>
@endsection

@section('content')
    <div class="p-3">
        <div class="d-flex justify-content-between align-items-end mb-1 gap-3">
            <div class="d-flex align-items-end gap-3 w-auto">
                <div>
                    <input type="date" class="form-control" id="from" value="{{ $from ? $from : date("Y-m-d") }}">
                </div>
                <div class="mb-2">
                    <span> - </span>
                </div>
                <div>
                    <input type="date" class="form-control" id="to" value="{{ $to ? $to : date("Y-m-d") }}">
                </div>
                <button class="btn btn-primary" onclick="updateTanggal()">
                    <i class="fa fa-search"></i>
                </button>
            </div>
            <button class="btn btn-success" onclick="exportExcel(this)"><i class="fa fa-file-excel"></i></button>
        </div>
        <div class="d-flex justify-content-center mb-1">
            <span><b>{{ localeDateFormat($from, false) }}</b> s/d <b>{{ localeDateFormat($to, false) }}</b></span>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div id="chart-eff"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div id="chart-rft"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="table-responsive p-1" style="background: #e9e9e9">
                    <div class="horizontal-grid" id="chief-table">
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
            getData();
        });

        var intervalData = setInterval(async function () {
            console.log("data update start");

            await getData();

            console.log("data update finish");
        }, 60000);

        async function getData() {
            await $.ajax({
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
                    let chiefEfficiency = objectValues(objectGroupBy(response, ({ chief_nik }) => chief_nik));

                    // Chief Daily Summary
                    let chiefDailyEfficiency = [];
                    let chiefDailyRft = [];
                    let chiefDaily = [];
                    chiefEfficiency.forEach(element => {
                        // Date Output
                        let dateOutput = [];
                        let totalMinsAvail = 0;
                        let totalMinsProd = 0;
                        let totalOutput = 0;
                        let totalRft = 0;
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

                            totalMinsAvail += Number(value.cumulative_mins_avail);
                            totalMinsProd += Number(value.mins_prod);
                            totalOutput += Number(value.output);
                            totalRft += Number(value.rft);

                            return res;
                        }, {});

                        let dateOutputFilter = dateOutput.filter((item) => item.mins_avail > 0 && item.mins_prod > 0).sort((a, b) => new Date(a.tanggal) - new Date(b.tanggal));
                        let currentFilter = dateOutputFilter.filter((item) => item.tanggal == formatDate(new Date()));

                        let totalData = { totalEfficiency : Number((totalMinsProd/totalMinsAvail*100).toFixed(2)), totalRft : Number((totalRft/totalOutput*100).toFixed(2)) };

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

                        chiefDailyEfficiency.push({"name": element[element.length-1].chief_name ? element[element.length-1].chief_name : 'KOSONG', "data": formattedData});
                        chiefDailyRft.push({"name": element[element.length-1].chief_name ? element[element.length-1].chief_name : 'KOSONG', "data": formattedRftData});
                        chiefDaily.push({"name": element[element.length-1].chief_name ? element[element.length-1].chief_name : 'KOSONG', "eff": formattedData, "rft": formattedRftData, "currentEff": (totalData ? totalData.totalEfficiency : 0), "currentRft": (totalData ? totalData.totalRft : 0)});
                    });

                    let sortChiefDaily = chiefDaily.sort(function(a,b){
                            if ((a.currentEff+a.currentRft) < (b.currentEff+b.currentRft)) {
                                return 1;
                            }
                            if ((a.currentEff+a.currentRft) > (b.currentEff+b.currentRft)) {
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
        }

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
                colors:['#269ffb', '#846cd4', '#febb3b', '#ff576f', '#18e5a0', '#ff3877'],
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
                colors:['#269ffb', '#846cd4', '#febb3b', '#ff576f', '#18e5a0', '#ff3877', '#038f81', '#178501', '#c9bc02', '#d90902'],
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

        async function chiefTable(data) {
            let longestData = data.reduce((a,b)=>a.eff.length>b.eff.length?a:b).eff.map((item)=>item["x"]);

            let parentElement = document.getElementById("chief-table");
            parentElement.innerHTML = `
                <div class="d-flex flex-column gap-1" id="chief-table-header" style="position: sticky; left: -5px; background: #e9e9e9; border-left: 6px solid #e9e9e9; border-right: 6px solid #e9e9e9;">
                    <div class="d-flex justify-content-center align-items-center horizontal-grid-box" style="height: 50px;">
                        <span class="text-nowrap fw-bold p-1">NAMA CHIEF</span>
                    </div>
                </div>
            `;
            let parentElementHeader = document.getElementById("chief-table-header");
            let totalHTML = "";
            let rankHTML = "";

            let headerElement = '';
            let tableHtml = '';
            let i = 0;
            longestData.forEach((value, index) => {
                let formattedValue = formatDate(value);

                let horizontalHtmlStart = `
                    <div>
                        <div class="d-flex flex-column gap-1" style="width: 150px;">
                            <div class="d-flex flex-column gap-1" style="height: 50px;">
                                <div class="d-flex justify-content-center align-items-center horizontal-grid-box h-100">
                                    <span class="text-nowrap text-center fw-bold">`+formattedValue+`</span>
                                </div>
                                <div class="d-flex justify-content-center align-items-center gap-1">
                                    <div class="horizontal-grid-box text-center w-50 h-100">
                                        <span class="text-nowrap fw-bold">Efficiency</span>
                                    </div>
                                    <div class="horizontal-grid-box text-center w-50 h-100">
                                        <span class="text-nowrap fw-bold">RFT</span>
                                    </div>
                                </div>
                            </div>
                `;

                data.forEach((element, i) => {
                    if (index == 0) {
                        parentElementHeader.innerHTML += `
                            <div class="horizontal-grid-box d-flex justify-content-center align-items-center gap-1" style="height: 50px;">
                                <span class="text-nowrap fw-bold p-1">`+element.name+`</span>
                            </div>
                        `;
                    }

                    let eff = element.eff.filter((item) => formatDate(item.x) == formatDate(value))[0];
                    let rft = element.rft.filter((item) => formatDate(item.x) == formatDate(value))[0];

                    // Body
                    let verticalHtml = `
                        <div>
                            <div class="d-flex justify-content-between align-items-center gap-1" style="height: 50px;">
                                <div class="horizontal-grid-box d-flex justify-content-center align-items-center w-50 h-100">
                                    <span class="text-nowrap fw-bold" style="color: `+colorizeEfficiency(eff ? eff.y : 0)+`;">`+(eff ? eff.y+' %' : '-')+`</span>
                                </div>
                                <div class="horizontal-grid-box d-flex justify-content-center align-items-center w-50 h-100">
                                    <span class="text-nowrap fw-bold" style="color: `+colorizeRft(rft ? rft.y : 0)+`;">`+(rft ? rft.y+' %' : '-')+`</span>
                                </div>
                            </div>
                        </div>
                    `;
                    horizontalHtmlStart += verticalHtml;

                    if (index == (longestData.length-1)) {
                        // Total
                        totalHTML += `
                            <div>
                                <div class="d-flex justify-content-between align-items-center gap-1" style="height: 50px;">
                                    <div class="horizontal-grid-box d-flex justify-content-center align-items-center w-50 h-100">
                                        <span class="text-nowrap fw-bold" style="color: `+colorizeEfficiency(element ? element.currentEff : 0)+`;">`+(element ? element.currentEff+' %' : '-')+`</span>
                                    </div>
                                    <div class="horizontal-grid-box d-flex justify-content-center align-items-center w-50 h-100">
                                        <span class="text-nowrap fw-bold" style="color: `+colorizeRft(element ? element.currentRft : 0)+`;">`+(element.currentRft ? element.currentRft+' %' : '-')+`</span>
                                    </div>
                                </div>
                            </div>
                        `;

                        // Rank
                        rankHTML += `
                            <div class="horizontal-grid-box d-flex justify-content-center align-items-center gap-1" style="height: 50px;">
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

            // Table Body
            parentElement.innerHTML += tableHtml;

            // Total
            parentElement.innerHTML += `
                <div>
                    <div class="d-flex flex-column gap-1" style="width: 150px;">
                        <div class="d-flex flex-column gap-1" style="height: 50px;">
                            <div class="d-flex justify-content-center align-items-center horizontal-grid-box h-100">
                                <span class="text-nowrap text-center fw-bold">TOTAL</span>
                            </div>
                            <div class="d-flex justify-content-center align-items-center gap-1">
                                <div class="horizontal-grid-box text-center w-50 h-100">
                                    <span class="text-nowrap fw-bold">Efficiency</span>
                                </div>
                                <div class="horizontal-grid-box text-center w-50 h-100">
                                    <span class="text-nowrap fw-bold">RFT</span>
                                </div>
                            </div>
                        </div>
                        `+totalHTML+`
                    </div>
                </div>
            `;

            // Rank
            parentElement.innerHTML += `
                <div class="d-flex flex-column gap-1" id="chief-table-footer" style="position: sticky; right: -5px; background: #e9e9e9; border-left: 6px solid #e9e9e9; border-right: 6px solid #e9e9e9;">
                    <div class="d-flex flex-column gap-1">
                        <div class="d-flex justify-content-center align-items-center horizontal-grid-box" style="height: 50px;">
                            <span class="text-nowrap fw-bold">RANK</span>
                        </div>
                    </div>
                    `+rankHTML+`
                </div>`;
        }

        function updateTanggal() {
            location.href = "{{ route("dashboard-chief-sewing-range") }}/"+$("#from").val()+"/"+$("#to").val();
        }

        function exportExcel(elm) {
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

            $.ajax({
                url: "{{ route("dashboard-chief-sewing-range-data-export") }}",
                type: 'post',
                data: {
                    from : $("#from").val(),
                    to : $("#to").val()
                },
                xhrFields: { responseType : 'blob' },
                success: function(res) {
                    elm.removeAttribute('disabled');
                    elm.innerText = "Export ";
                    let icon = document.createElement('i');
                    icon.classList.add('fa-solid');
                    icon.classList.add('fa-file-excel');
                    elm.appendChild(icon);

                    iziToast.success({
                        title: 'Success',
                        message: 'Data berhasil di export.',
                        position: 'topCenter'
                    });

                    var blob = new Blob([res]);
                    var link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = "Performance Sewing "+$("#from").val()+" - "+$("#to").val()+".xlsx";
                    link.click();
                }, error: function (jqXHR) {
                    elm.removeAttribute('disabled');
                    elm.innerText = "Export ";
                    let icon = document.createElement('i');
                    icon.classList.add('fa-solid');
                    icon.classList.add('fa-file-excel');
                    elm.appendChild(icon);

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

        // Colorize Efficiency
        function colorizeEfficiency(efficiency) {
            let color = "";
            switch (true) {
                case efficiency < 75 :
                    color = '#dc3545';
                    break;
                case efficiency >= 75 && efficiency <= 85 :
                    color = 'rgb(240, 153, 0)';
                    break;
                case efficiency > 85 :
                    color = '#28a745';
                    break;
            }

            return color;
        }

        // Colorize RFT
        function colorizeRft(rft) {
            let color = "";
            switch (true) {
                case rft < 97 :
                    color = '#dc3545';
                    break;
                case rft >= 97 && rft < 98 :
                    color = 'rgb(240, 153, 0)';
                    break;
                case rft >= 98 :
                    color = '#28a745';
                    break;
            }

            return color;
        }
    </script>
@endsection
