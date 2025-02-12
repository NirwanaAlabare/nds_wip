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

    <style>
        .dataTables_wrapper .dataTables_processing {
            position: absolute;
            top: 50px !important;
            /* background: #FFFFCC;
            border: 1px solid black; */
            border-radius: 3px;
            font-weight: bold;
        }
    </style>
@endsection

@section('content')
    <input type="hidden" id="year" value="{{ $year ? $year : date("Y") }}">
    <input type="hidden" id="month" value="{{ $month ? $month : date("m") }}">
    <input type="hidden" id="month-name" value="{{ $monthName ? $monthName : $months[num(date("m"))-1] }}">
    <table class="table table-bordered" id="chief-daily-efficiency-table">
        <thead>
            <tr>
                <th rowspan="2" colspan="2" class="bg-sb text-light align-middle fw-bold fs-4 text-center">Chief Daily Efficiency & RFT {{ $monthName }}</th>
                <th colspan="2" class="bg-sb text-light align-middle text-center" id="day-1">Before</th>
                <th colspan="2" class="bg-sb text-light align-middle text-center" id="day-2">Yesterday</th>
                <th colspan="2" class="bg-sb text-light align-middle text-center" id="day-3">Today</th>
                <th rowspan="2" class="bg-sb text-light align-middle text-center fs-5">Rank</th>
            </tr>
            <tr>
                <th class="bg-sb text-light align-middle text-center">Effy</th>
                <th class="bg-sb text-light align-middle text-center">RFT</th>
                <th class="bg-sb text-light align-middle text-center">Effy</th>
                <th class="bg-sb text-light align-middle text-center">RFT</th>
                <th class="bg-sb text-light align-middle text-center">Effy</th>
                <th class="bg-sb text-light align-middle text-center">RFT</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
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
    <script>
        $('.select2').select2()
        $('.select2bs4').select2({
            theme: 'bootstrap4'
        })

        document.getElementById("loading").classList.remove("d-none");

        $('document').ready(async () => {
            $.ajax({
                url: "{{ route("dashboard-chief-sewing-data") }}",
                type: "get",
                data: {
                    year: $("#year").val(),
                    month: $("#month").val()
                },
                dataType: "json",
                success: async function (response) {
                    // Chief Group By
                    let chiefEfficiency = Object.values(Object.groupBy(response, ({ chief_id }) => chief_id));

                    // Chief Daily Summary
                    let chiefDailyEfficiency = [];
                    chiefEfficiency.forEach(element => {
                        // Date Output
                        let dateOutput = [];
                        element.reduce(function(res, value) {
                            if (!res[value.tanggal]) {
                                res[value.tanggal] = { tanggal: value.tanggal, mins_avail: 0, mins_prod: 0, output: 0, rft: 0 };
                                dateOutput.push(res[value.tanggal]);
                            }
                            res[value.tanggal].mins_avail += value.tanggal == formatDate(new Date()) ? Number(value.cumulative_mins_avail) : Number(value.mins_avail);
                            res[value.tanggal].mins_prod += Number(value.mins_prod);
                            res[value.tanggal].output += Number(value.output);
                            res[value.tanggal].rft += Number(value.rft);

                            return res;
                        }, {});

                        // Leader Output
                        let leaderOutput = [];
                        element.reduce(function(res, value) {
                            if (!res[value.leader_id]) {
                                res[value.leader_id] = { leader_id: value.leader_id, leader_nik: value.leader_nik, leader_name: value.leader_name, mins_avail: 0, mins_prod: 0, output: 0, rft: 0 };
                                leaderOutput.push(res[value.leader_id]);
                            }
                            res[value.leader_id].mins_avail += Number(value.mins_avail);
                            res[value.leader_id].mins_prod += Number(value.mins_prod);
                            res[value.leader_id].output += Number(value.output);
                            res[value.leader_id].rft += Number(value.rft);

                            return res;
                        }, {});

                        let dateOutputFilter = dateOutput.filter((item) => item.mins_avail > 0 && item.mins_prod > 0);
                        let currentFilter = dateOutputFilter.filter((item) => item.tanggal == formatDate(new Date()));
                        let currentData = currentFilter.length > 0 ? currentFilter[0] : dateOutputFilter[dateOutputFilter.length-1];

                        chiefDailyEfficiency.push({"id": element[0].chief_id, "nik": element[0].chief_nik, "name": element[0].chief_name, "data": dateOutput, "leaderData": leaderOutput, "currentEff": (currentData ? currentData.mins_prod/currentData.mins_avail*100 : 0)});
                    });

                    // Sort Chief Daily by Efficiency
                    let sortedChiefDailyEfficiency = chiefDailyEfficiency.sort(function(a,b){
                        if (a.currentEff < b.currentEff) {
                            return 1;
                        }
                        if (a.currentEff  > b.currentEff) {
                            return -1;
                        }
                        return 0;
                    });

                    // Show Chief Daily Data
                    for (let i = 0; i < sortedChiefDailyEfficiency.length; i++) {
                        appendRow(sortedChiefDailyEfficiency[i], i+1);
                    }

                    document.getElementById("loading").classList.add("d-none");
                },
                error: function (jqXHR) {
                    console.error(jqXHR);

                    document.getElementById("loading").classList.add("d-none");
                }
            });
        });

        async function appendRow(data, index) {
            let table = document.querySelector('#chief-daily-efficiency-table tbody');

            // Name
            let tr = document.createElement("tr");
            let tdName = document.createElement("td");
            let employeeContainer = document.createElement("div");
            employeeContainer.classList.add("row");
            employeeContainer.classList.add("justify-content-center");
            employeeContainer.classList.add("align-items-center");
            employeeContainer.style.minWidth= '250px';

            // Chief
            let chiefName = data.name ? data.name.split(" ")[0] : '-';
            let chiefContainer = document.createElement("div");
            chiefContainer.classList.add("col-5");
            let imageElement = document.createElement("img");
            imageElement.src = "http://10.10.5.111/hris/public/storage/app/public/images/"+data.nik+"%20"+data.name+".png"
            imageElement.classList.add("img-fluid");
            imageElement.style.maxWidth = "100%";
            imageElement.style.marginBottom = "10px";
            chiefContainer.appendChild(imageElement);
            chiefContainer.innerHTML += "<span class='text-sb fw-bold'><center>"+data.name.split(" ")[0]+"</center></span>"

            // Leader
            let leaderContainer = document.createElement("div");
            leaderContainer.classList.add("col-7");
            let leadersElement = document.createElement("div");
            leadersElement.classList.add("row");
            leadersElement.classList.add("justify-content-center");
            leadersElement.classList.add("align-items-end");
            data.leaderData.forEach(element => {
                let leaderName = element.leader_name ? element.leader_name.split(" ")[0] : '-';
                let leaderElement = document.createElement("div");
                leaderElement.classList.add("col-4");
                let leaderImageElement = document.createElement("img");
                leaderImageElement.src = "http://10.10.5.111/hris/public/storage/app/public/images/"+element.leader_nik+"%20"+element.leader_name+".png";
                leaderImageElement.setAttribute("onerror", "this.onerror=null; this.src='{{ asset('dist/img/person.png') }}'");
                leaderImageElement.setAttribute("alt", "person")
                leaderImageElement.classList.add("img-fluid");
                // leaderImageElement.style.minWidth = "45px";
                leaderElement.appendChild(leaderImageElement);
                leaderElement.innerHTML += "<span class='text-sb fw-bold' style='font-size: 8px;'><center>"+leaderName+"</center></span>";
                leadersElement.appendChild(leaderElement);
            });
            leaderContainer.appendChild(leadersElement);

            employeeContainer.appendChild(chiefContainer)
            employeeContainer.appendChild(leaderContainer)
            tdName.appendChild(employeeContainer);
            tdName.classList.add("align-middle");
            // tdName.classList.add("pe-5");
            tr.appendChild(tdName);

            // Chart
            let tdChart = document.createElement("td");
            let canvas = document.createElement("div");
            canvas.id = "chart-"+data.id;
            canvas.classList.add("chief-daily-efficiency-chart");
            canvas.style.width = '450px';
            tdChart.appendChild(canvas);
            tdChart.classList.add("align-middle");
            tr.appendChild(tdChart);

            let tglArr = [];
            let efficiencyArr = [];
            let targetEfficiencyArr = [];
            let rftArr = [];

            let dailyData = data.data.filter((item) => item.mins_avail > 0 && item.output > 0);

            dailyData.forEach(item => {
                tglArr.push(item.tanggal.substr(-2));
                efficiencyArr.push((item.mins_prod / item.mins_avail * 100).round(2));
                rftArr.push((item.rft / item.output * 100).round(2));
            });

            var options = {
                series: [
                    {
                        name: "Efficiency",
                        data: efficiencyArr
                    },
                    {
                        name: "RFT",
                        data: rftArr
                    },
                ],
                chart: {
                    height: 230,
                    type: 'line',
                    zoom: {
                        enabled: true
                    },
                    toolbar: {
                        show: false
                    }
                },
                colors: ['#082149', '#238380'],
                dataLabels: {
                    enabled: true,
                    style: {
                        fontSize: "8px",
                    }
                },
                stroke: {
                    curve: 'smooth'
                },
                title: {
                    text: 'Daily '+$("#month-name").val(),
                    align: 'left'
                },
                grid: {
                    borderColor: '#e7e7e7',
                    row: {
                        colors: ['#f3f3f3', 'transparent'], // takes an array which will be repeated on columns
                        opacity: 0.5
                    },
                },
                yaxis: {
                    labels: {
                        style: {
                            fontSize: "8px",
                        }
                    }
                },
                xaxis: {
                    categories: tglArr,
                    labels: {
                        style: {
                            fontSize: "8px",
                        }
                    }
                },
                noData: {
                    text: 'Data Not Found'
                },
                legend: {
                    position: 'top',
                    horizontalAlign: 'right',
                    floating: true,
                    offsetY: -25,
                    offsetX: -5
                },
                redrawOnParentResize: true
            };

            var chart = new ApexCharts(canvas, options);

            let todayFilter = dailyData.filter((item) => item.tanggal <= formatDate(new Date()));
            let today = todayFilter[todayFilter.length-1];

            let yesterdayFilter = dailyData.filter((item) => item.tanggal < formatDate(today ? today.tanggal : new Date(new Date().setDate(new Date().getDate() - 1))));
            let yesterday = yesterdayFilter[yesterdayFilter.length-1]

            let beforeFilter = dailyData.filter((item) => item.tanggal < formatDate(yesterday ? yesterday.tanggal : new Date(new Date().setDate(new Date().getDate() - 2))));
            let before = beforeFilter[beforeFilter.length-1];

            // Before
            let tdBeforeEff = document.createElement("td");
            tdBeforeEff.innerHTML = (before ? (before.mins_prod / before.mins_avail * 100).round(2) : 0)+"%";
            tr.appendChild(tdBeforeEff);
            tdBeforeEff.classList.add("align-middle");
            tdBeforeEff.classList.add("fw-bold");
            (before ? (before.mins_prod / before.mins_avail * 100).round(2) : 0) >= 85 ?  tdBeforeEff.classList.add("text-success") : tdBeforeEff.classList.add("text-danger");
            tdBeforeEff.classList.add("fs-6");
            let tdBeforeRft = document.createElement("td");
            tdBeforeRft.innerHTML = (before ? (before.rft / before.output * 100).round(2) : 0)+"%";
            tr.appendChild(tdBeforeRft);
            tdBeforeRft.classList.add("align-middle");
            tdBeforeRft.classList.add("fw-bold");
            (before ? (before.mins_prod / before.mins_avail * 100).round(2) : 0) >= 97 ?  tdBeforeRft.classList.add("text-success") : tdBeforeRft.classList.add("text-danger");
            tdBeforeRft.classList.add("fs-6");

            // Yesterday
            let tdYesterdayEff = document.createElement("td");
            tdYesterdayEff.innerHTML = (yesterday ? (yesterday.mins_prod / yesterday.mins_avail * 100).round(2) : 0)+"%";
            tr.appendChild(tdYesterdayEff);
            tdYesterdayEff.classList.add("align-middle");
            tdYesterdayEff.classList.add("align-middle");
            tdYesterdayEff.classList.add("fw-bold");
            (before ? (before.mins_prod / before.mins_avail * 100).round(2) : 0) >= 85 ?  tdYesterdayEff.classList.add("text-success") : tdYesterdayEff.classList.add("text-danger");
            tdYesterdayEff.classList.add("fs-6");
            let tdYesterdayRft = document.createElement("td");
            tdYesterdayRft.innerHTML = (yesterday ? (yesterday.rft / yesterday.output * 100).round(2) : 0)+"%";
            tr.appendChild(tdYesterdayRft);
            tdYesterdayRft.classList.add("align-middle");
            tdYesterdayRft.classList.add("align-middle");
            tdYesterdayRft.classList.add("fw-bold");
            (before ? (before.mins_prod / before.mins_avail * 100).round(2) : 0) >= 85 ?  tdYesterdayRft.classList.add("text-success") : tdYesterdayRft.classList.add("text-danger");
            tdYesterdayRft.classList.add("fs-6");

            // Today
            let tdTodayEff = document.createElement("td");
            tdTodayEff.innerHTML = (today ? (today.mins_prod / today.mins_avail * 100).round(2) : 0)+"%";
            tr.appendChild(tdTodayEff);
            tdTodayEff.classList.add("align-middle");
            tdTodayEff.classList.add("fw-bold");
            (before ? (before.mins_prod / before.mins_avail * 100).round(2) : 0) >= 85 ?  tdTodayEff.classList.add("text-success") : tdTodayEff.classList.add("text-danger");
            tdTodayEff.classList.add("fs-6");
            tdTodayEff.classList.add("align-middle");
            let tdTodayRft = document.createElement("td");
            tdTodayRft.innerHTML = (today ? (today.rft / today.output * 100).round(2) : 0)+"%";
            tr.appendChild(tdTodayRft);
            tdTodayRft.classList.add("align-middle");
            tdTodayRft.classList.add("fw-bold");
            (before ? (before.mins_prod / before.mins_avail * 100).round(2) : 0) >= 85 ?  tdTodayRft.classList.add("text-success") : tdTodayRft.classList.add("text-danger");
            tdTodayRft.classList.add("fs-6");

            if (formatDate(new Date()) > today.tanggal) {
                document.getElementById("day-1").innerHTML = formatDateLocal(before.tanggal);
                document.getElementById("day-2").innerHTML = formatDateLocal(yesterday.tanggal);
                document.getElementById("day-3").innerHTML = formatDateLocal(today.tanggal);
            }

            // Rank
            let tdRank = document.createElement("td");
            tdRank.innerHTML = `
                <div class="d-flex flex-column">
                    <span class="text-sb">`+index+`</span>
                    `+(index <= 1 ? ` <i class="fa-solid fa-award text-sb-secondary"></i>` : ``)+`
                </div>
                `;
            tr.appendChild(tdRank);
            tdRank.classList.add("text-center");
            tdRank.classList.add("align-middle");
            tdRank.classList.add("fw-bold");
            tdRank.classList.add("fs-5");

            table.appendChild(tr);

            chart.render();

            // initSparkle();
        }

        async function updateData() {
//
        }
    </script>
@endsection
