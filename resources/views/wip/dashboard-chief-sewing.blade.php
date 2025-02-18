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
    </style>
@endsection

@section('content')
    <input type="hidden" id="year" value="{{ $year ? $year : date("Y") }}">
    <input type="hidden" id="month" value="{{ $month ? $month : date("m") }}">
    <input type="hidden" id="month-name" value="{{ $monthName ? $monthName : $months[num(date("m"))-1] }}">
    <swiper-container class="mySwiper" id="table-carousel" autoplay-delay="30000" autoplay-disable-on-interaction="true" space-between="30" centered-slides="true">
        <swiper-slide id="carousel-1">
            <div class="table-responsive" id="chief-daily-efficiency-table">
                <table class="table table-bordered w-100">
                    <thead>
                        <tr>
                            <th rowspan="2" colspan="2" class="bg-sb text-light align-middle fw-bold text-center" style="font-size: 20px !important; padding: 5px !important;">Chief Daily Efficiency & RFT {{ $monthName }}</th>
                            <th colspan="2" class="bg-sb text-light fw-bold align-middle text-center day-1" style="padding: 5px !important;">H-2</th>
                            <th colspan="2" class="bg-sb text-light fw-bold align-middle text-center day-2" style="padding: 5px !important;">H-1</th>
                            <th colspan="2" class="bg-sb text-light fw-bold align-middle text-center day-3" style="padding: 5px !important;">Hari Ini</th>
                            <th rowspan="2" class="bg-sb text-light fw-bold align-middle text-center" style="padding: 5px !important;">Rank</th>
                        </tr>
                        <tr>
                            <th class="bg-sb text-light fw-bold align-middle text-center" style="font-size: 10px;padding: 5px !important;">Effy</th>
                            <th class="bg-sb text-light fw-bold align-middle text-center" style="font-size: 10px;padding: 5px !important;">RFT</th>
                            <th class="bg-sb text-light fw-bold align-middle text-center" style="font-size: 10px;padding: 5px !important;">Effy</th>
                            <th class="bg-sb text-light fw-bold align-middle text-center" style="font-size: 10px;padding: 5px !important;">RFT</th>
                            <th class="bg-sb text-light fw-bold align-middle text-center" style="font-size: 10px;padding: 5px !important;">Effy</th>
                            <th class="bg-sb text-light fw-bold align-middle text-center" style="font-size: 10px;padding: 5px !important;">RFT</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </swiper-slide>
    </swiper-container>
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
        document.body.style.overflow = "hidden";

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
                            // res[value.tanggal].mins_avail += value.tanggal == formatDate(new Date()) ? Number(value.cumulative_mins_avail) : Number(value.mins_avail);
                            res[value.tanggal].mins_avail += Number(value.cumulative_mins_avail);
                            res[value.tanggal].mins_prod += Number(value.mins_prod);
                            res[value.tanggal].output += Number(value.output);
                            res[value.tanggal].rft += Number(value.rft);

                            return res;
                        }, {});

                        // Leader Output
                        let leaderOutput = [];
                        element.reduce(function(res, value) {
                            if (value.tanggal == formatDate(new Date())) {
                                if (!res[value.leader_id]) {
                                    res[value.leader_id] = { leader_id: value.leader_id, leader_nik: value.leader_nik, leader_name: value.leader_name, sewing_line: "", mins_avail: 0, mins_prod: 0, output: 0, rft: 0 };
                                    leaderOutput.push(res[value.leader_id]);
                                }
                                res[value.leader_id].mins_avail += Number(value.cumulative_mins_avail);
                                res[value.leader_id].mins_prod += Number(value.mins_prod);
                                res[value.leader_id].output += Number(value.output);
                                res[value.leader_id].rft += Number(value.rft);
                                res[value.leader_id].sewing_line += value.sewing_line+"<br>";
                            }

                            return res;
                        }, {});

                        // Sort by leader output efficiency
                        let sortedLeaderOutput = leaderOutput.sort(function(a,b){
                            if ((a.mins_prod/a.mins_avail) < (b.mins_prod/b.mins_avail)) {
                                return 1;
                            }
                            if ((a.mins_prod/a.mins_avail)  > (b.mins_prod/b.mins_avail)) {
                                return -1;
                            }
                            return 0;
                        });

                        let dateOutputFilter = dateOutput.filter((item) => item.mins_avail > 0 && item.mins_prod > 0);
                        let currentFilter = dateOutputFilter.filter((item) => item.tanggal == formatDate(new Date()));
                        let currentData = currentFilter.length > 0 ? currentFilter[0] : dateOutputFilter[dateOutputFilter.length-1];

                        chiefDailyEfficiency.push({"id": element[0].chief_id, "nik": element[0].chief_nik, "name": element[0].chief_name, "data": dateOutput, "leaderData": sortedLeaderOutput, "currentEff": (currentData ? currentData.mins_prod/currentData.mins_avail*100 : 0)});
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

        var intervalData = setInterval(() => {
            updateData();
        }, 60000);

        async function appendRow(data, index) {
            let tableElement = document.getElementById('chief-daily-efficiency-table');
            let table = document.querySelector('#chief-daily-efficiency-table tbody');

            // Name
            let tr = document.createElement("tr");
            let tdName = document.createElement("td");
            let employeeContainer = document.createElement("div");
            employeeContainer.id = "employee-"+index;
            employeeContainer.style.width = "350px";
            employeeContainer.style.minHeight = "200px";
            employeeContainer.style.marginLeft = "auto";
            employeeContainer.style.marginRight = "auto";
            employeeContainer.classList.add("row");

            // Chief
            let chiefName = data.name ? data.name.split(" ")[0] : '-';
            let chiefContainer = document.createElement("div");
            chiefContainer.classList.add("col-5");
            chiefContainer.classList.add("border");
            let imageElement = document.createElement("img");
            imageElement.src = "/nds_wip_local/public/storage/employee_profile/"+data.nik+"%20"+data.name+".png"
            imageElement.setAttribute("onerror", "this.onerror=null; this.src='{{ asset('dist/img/person.png') }}'");
            imageElement.setAttribute("alt", "person")
            imageElement.style.width = "100px";
            imageElement.style.height = "165px";
            imageElement.style.marginLeft = "auto";
            imageElement.style.marginRight = "auto";
            chiefContainer.appendChild(imageElement);
            chiefContainer.innerHTML += "<span class='text-sb fw-bold'><center>"+data.name.split(" ")[0]+"</center></span>"

            // Leader
            let leaderContainer = document.createElement("div");
            leaderContainer.classList.add("col-7");
            let leadersElement = document.createElement("div");
            leadersElement.classList.add("row");
            data.leaderData.forEach(element => {
                let leaderName = element.leader_name ? element.leader_name.split(" ")[0] : '-KOSONG-';
                let leaderElement = document.createElement("div");
                leaderElement.classList.add("col-4");
                leaderElement.classList.add("border");
                let leaderImageElement = document.createElement("img");
                leaderImageElement.src = "/nds_wip_local/public/storage/employee_profile/"+element.leader_nik+"%20"+element.leader_name+".png"
                leaderImageElement.setAttribute("onerror", "this.onerror=null; this.src='{{ asset('dist/img/person.png') }}'");
                leaderImageElement.setAttribute("alt", "person")
                leaderImageElement.style.width = "50px";
                leaderImageElement.style.height = "80px";
                leaderImageElement.style.marginLeft = "auto";
                leaderImageElement.style.marginRight = "auto";
                leaderElement.appendChild(leaderImageElement);
                leaderElement.innerHTML += "<span class='text-sb fw-bold' style='font-size: 8px;'><center>"+leaderName+"</center></span>";
                leaderElement.innerHTML += "<span class='text-sb-secondary fw-bold' style='font-size: 8px;'><center>"+element.sewing_line.replace(/_/g, " ").toUpperCase()+"</center></span>";
                leadersElement.appendChild(leaderElement);
            });
            leaderContainer.appendChild(leadersElement);

            employeeContainer.appendChild(chiefContainer)
            employeeContainer.appendChild(leaderContainer)
            tdName.appendChild(employeeContainer);
            tdName.classList.add("align-middle");
            tr.appendChild(tdName);

            // Chart
            let tdChart = document.createElement("td");
            let canvas = document.createElement("div");
            // canvas.id = "chart-"+index;
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
                    id: "chart-"+index,
                    height: 200,
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

            let yesterdayFilter = todayFilter.filter((item) => item.tanggal < formatDate(today ? today.tanggal : new Date(new Date().setDate(new Date().getDate() - 1))));
            let yesterday = yesterdayFilter[yesterdayFilter.length-1]

            let beforeFilter = yesterdayFilter.filter((item) => item.tanggal < formatDate(yesterday ? yesterday.tanggal : new Date(new Date().setDate(new Date().getDate() - 2))));
            let before = beforeFilter[beforeFilter.length-1];

            // Before
            let tdBeforeEff = document.createElement("td");
            tdBeforeEff.id = "before-eff-"+index;
            tdBeforeEff.innerHTML = (before ? (before.mins_prod / before.mins_avail * 100).round(2) : 0)+" %";
            tr.appendChild(tdBeforeEff);
            tdBeforeEff.classList.add("text-center");
            tdBeforeEff.classList.add("align-middle");
            tdBeforeEff.classList.add("fw-bold");
            colorizeEfficiency(tdBeforeEff, (before ? (before.mins_prod / before.mins_avail * 100).round(2) : 0));
            tdBeforeEff.classList.add("fs-6");
            let tdBeforeRft = document.createElement("td");
            tdBeforeRft.id = "before-rft-"+index;
            tdBeforeRft.innerHTML = (before ? (before.rft / before.output * 100).round(2) : 0)+" %";
            tr.appendChild(tdBeforeRft);
            tdBeforeRft.classList.add("text-center");
            tdBeforeRft.classList.add("align-middle");
            tdBeforeRft.classList.add("fw-bold");
            colorizeEfficiency(tdBeforeRft, (before ? (before.rft / before.output * 100).round(2) : 0))
            tdBeforeRft.classList.add("fs-6");

            // Yesterday
            let tdYesterdayEff = document.createElement("td");
            tdYesterdayEff.id = "yesterday-eff-"+index;
            tdYesterdayEff.innerHTML = (yesterday ? (yesterday.mins_prod / yesterday.mins_avail * 100).round(2) : 0)+" %";
            tr.appendChild(tdYesterdayEff);
            tdYesterdayEff.classList.add("text-center");
            tdYesterdayEff.classList.add("align-middle");
            tdYesterdayEff.classList.add("fw-bold");
            colorizeEfficiency(tdYesterdayEff, (yesterday ? (yesterday.mins_prod / yesterday.mins_avail * 100).round(2) : 0))
            tdYesterdayEff.classList.add("fs-6");
            let tdYesterdayRft = document.createElement("td");
            tdYesterdayRft.id = "yesterday-rft-"+index;
            tdYesterdayRft.innerHTML = (yesterday ? (yesterday.rft / yesterday.output * 100).round(2) : 0)+" %";
            tr.appendChild(tdYesterdayRft);
            tdYesterdayRft.classList.add("text-center");
            tdYesterdayRft.classList.add("align-middle");
            tdYesterdayRft.classList.add("fw-bold");
            colorizeEfficiency(tdYesterdayRft, (yesterday ? (yesterday.rft / yesterday.output * 100).round(2) : 0))
            tdYesterdayRft.classList.add("fs-6");

            // Today
            let tdTodayEff = document.createElement("td");
            tdTodayEff.id = "today-eff-"+index;
            tdTodayEff.innerHTML = (today ? (today.mins_prod / today.mins_avail * 100).round(2) : 0)+" %";
            tr.appendChild(tdTodayEff);
            tdTodayEff.classList.add("text-center");
            tdTodayEff.classList.add("align-middle");
            tdTodayEff.classList.add("fw-bold");
            colorizeEfficiency(tdTodayEff, (today ? (today.mins_prod / today.mins_avail * 100).round(2) : 0))
            tdTodayEff.classList.add("fs-6");
            tdTodayEff.classList.add("align-middle");
            let tdTodayRft = document.createElement("td");
            tdTodayRft.id = "today-rft-"+index;
            tdTodayRft.innerHTML = (today ? (today.rft / today.output * 100).round(2) : 0)+" %";
            tr.appendChild(tdTodayRft);
            tdTodayRft.classList.add("text-center");
            tdTodayRft.classList.add("align-middle");
            tdTodayRft.classList.add("fw-bold");
            colorizeEfficiency(tdTodayRft, (today ? (today.rft / today.output * 100).round(2) : 0))
            tdTodayRft.classList.add("fs-6");

            if (formatDate(new Date()) > today.tanggal) {
                let dayOneElement = document.getElementsByClassName("day-1");
                for (let i = 0; i < dayOneElement.length; i++) {
                    dayOneElement[i].innerHTML = formatDateLocal(before.tanggal);
                }

                let dayTwoElement = document.getElementsByClassName("day-2");
                for (let i = 0; i < dayTwoElement.length; i++) {
                    dayTwoElement[i].innerHTML = formatDateLocal(yesterday.tanggal);
                }

                let dayThreeElement = document.getElementsByClassName("day-3");
                for (let i = 0; i < dayThreeElement.length; i++) {
                    dayThreeElement[i].innerHTML = formatDateLocal(today.tanggal);
                }
            }

            // Rank
            let tdRank = document.createElement("td");
            tdRank.innerHTML = `
                <div class="d-flex flex-column">
                    <span class="text-sb">#`+index+`</span>
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

            if (index % 2 == 0) {
                tableElement.id = "chief-daily-efficiency-table-"+((index/2)+1);
                let newTable = tableElement.cloneNode();
                newTable.id = "chief-daily-efficiency-table";
                newTable.innerHTML = `
                    <table class="table table-bordered w-100">
                        <thead>
                            <tr>
                                <th rowspan="2" colspan="2" class="bg-sb text-light align-middle fw-bold text-center" style="font-size: 20px !important; padding: 5px !important;">Chief Daily Efficiency & RFT {{ $monthName }}</th>
                                <th colspan="2" class="bg-sb text-light fw-bold align-middle text-center day-1" style="padding: 5px !important;">H-2</th>
                                <th colspan="2" class="bg-sb text-light fw-bold align-middle text-center day-2" style="padding: 5px !important;">H-1</th>
                                <th colspan="2" class="bg-sb text-light fw-bold align-middle text-center day-3" style="padding: 5px !important;">Hari Ini</th>
                                <th rowspan="2" class="bg-sb text-light fw-bold align-middle text-center" style="padding: 5px !important;">Rank</th>
                            </tr>
                            <tr>
                                <th class="bg-sb text-light fw-bold align-middle text-center" style="font-size: 10px;padding: 5px !important;">Effy</th>
                                <th class="bg-sb text-light fw-bold align-middle text-center" style="font-size: 10px;padding: 5px !important;">RFT</th>
                                <th class="bg-sb text-light fw-bold align-middle text-center" style="font-size: 10px;padding: 5px !important;">Effy</th>
                                <th class="bg-sb text-light fw-bold align-middle text-center" style="font-size: 10px;padding: 5px !important;">RFT</th>
                                <th class="bg-sb text-light fw-bold align-middle text-center" style="font-size: 10px;padding: 5px !important;">Effy</th>
                                <th class="bg-sb text-light fw-bold align-middle text-center" style="font-size: 10px;padding: 5px !important;">RFT</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                `;

                let carouselContainer = document.getElementById("table-carousel");
                let carouselElement = document.getElementById("carousel-1").cloneNode();
                carouselElement.id = "carousel-"+((index/2)+1);

                console.log(carouselElement);

                carouselElement.appendChild(newTable);
                carouselContainer.appendChild(carouselElement);
            }

            // initSparkle();
        }

        // Colorize Efficiency
        function colorizeEfficiency(element, efficiency) {
            if (isElement(element)) {
                console.log("hey", element, efficiency);
                switch (true) {
                    case efficiency < 75 :
                        console.log(efficiency);
                        element.style.color = '#dc3545';
                        break;
                    case efficiency >= 75 && efficiency <= 85 :
                        console.log(efficiency);
                        element.style.color = 'rgb(240, 153, 0)';
                        break;
                    case efficiency > 85 :
                        console.log(efficiency);
                        element.style.color = '#28a745';
                        break;
                }
            } else {
                console.error("hell nah");
            }
        }

        async function updateData() {
            await $.ajax({
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
                            // res[value.tanggal].mins_avail += value.tanggal == formatDate(new Date()) ? Number(value.cumulative_mins_avail) : Number(value.mins_avail);
                            res[value.tanggal].mins_avail += Number(value.cumulative_mins_avail);
                            res[value.tanggal].mins_prod += Number(value.mins_prod);
                            res[value.tanggal].output += Number(value.output);
                            res[value.tanggal].rft += Number(value.rft);

                            return res;
                        }, {});

                        // Leader Output
                        let leaderOutput = [];
                        element.reduce(function(res, value) {
                            if (value.tanggal == formatDate(new Date())) {
                                if (!res[value.leader_id]) {
                                    res[value.leader_id] = { leader_id: value.leader_id, leader_nik: value.leader_nik, leader_name: value.leader_name, sewing_line: "", mins_avail: 0, mins_prod: 0, output: 0, rft: 0 };
                                    leaderOutput.push(res[value.leader_id]);
                                }
                                res[value.leader_id].mins_avail += Number(value.cumulative_mins_avail);
                                res[value.leader_id].mins_prod += Number(value.mins_prod);
                                res[value.leader_id].output += Number(value.output);
                                res[value.leader_id].rft += Number(value.rft);
                                res[value.leader_id].sewing_line += value.sewing_line+"<br>";
                            }

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
                        updateRow(sortedChiefDailyEfficiency[i], i+1);
                    }

                    document.getElementById("loading").classList.add("d-none");
                },
                error: function (jqXHR) {
                    console.error(jqXHR);

                    document.getElementById("loading").classList.add("d-none");
                }
            });
        }

        async function updateRow(data, index) {
            if (document.getElementById("employee-"+index)) {
                // Name
                let nameElement = document.getElementById("employee-"+index);

                nameElement.innerHTML = "";

                // Chief
                let chiefName = data.name ? data.name.split(" ")[0] : '-';
                let chiefContainer = document.createElement("div");
                chiefContainer.classList.add("col-5");
                chiefContainer.classList.add("border");
                let imageElement = document.createElement("img");
                imageElement.src = "/nds_wip_local/public/storage/employee_profile/"+data.nik+"%20"+data.name+".png";
                imageElement.style.width = "100px";
                imageElement.style.height = "150px";
                imageElement.style.marginLeft = "auto";
                imageElement.style.marginRight = "auto";
                imageElement.style.marginBottom = "10px";
                chiefContainer.appendChild(imageElement);
                chiefContainer.innerHTML += "<span class='text-sb fw-bold'><center>"+data.name.split(" ")[0]+"</center></span>"

                // Leader
                let leaderContainer = document.createElement("div");
                leaderContainer.classList.add("col-7");
                let leadersElement = document.createElement("div");
                leadersElement.classList.add("row");
                data.leaderData.forEach(element => {
                    let leaderName = element.leader_name ? element.leader_name.split(" ")[0] : 'KOSONG';
                    let leaderElement = document.createElement("div");
                    leaderElement.classList.add("col-4");
                    leaderElement.classList.add("border");
                    let leaderImageElement = document.createElement("img");
                    leaderImageElement.src = "/nds_wip_local/public/storage/employee_profile/"+element.leader_nik+"%20"+element.leader_name+".png"
                    leaderImageElement.setAttribute("onerror", "this.onerror=null; this.src='{{ asset('dist/img/person.png') }}'");
                    leaderImageElement.setAttribute("alt", "person")
                    leaderImageElement.style.width = "50px";
                    leaderImageElement.style.height = "80px";
                    leaderImageElement.style.marginLeft = "auto";
                    leaderImageElement.style.marginRight = "auto";
                    leaderElement.appendChild(leaderImageElement);
                    leaderElement.innerHTML += "<span class='text-sb fw-bold' style='font-size: 8px;'><center>"+leaderName+"</center></span>";
                    leaderElement.innerHTML += "<span class='text-sb-secondary fw-bold' style='font-size: 8px;'><center>"+element.sewing_line.replace("_", " ").toUpperCase()+"</center></span>";
                    leadersElement.appendChild(leaderElement);
                });
                leaderContainer.appendChild(leadersElement);

                nameElement.appendChild(chiefContainer)
                nameElement.appendChild(leaderContainer)

                // Chart
                let chartElement = document.getElementById("chart-"+index);

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

                await ApexCharts.exec('chart-'+index, 'updateSeries', [
                        {
                            name: 'Efficiency',
                            data: efficiencyArr
                        },
                        {
                            name: 'RFT',
                            data: rftArr
                        }
                    ], true);

                await ApexCharts.exec('mychart', 'updateOptions', {
                        xaxis: {
                            categories: tglArr,
                        },
                        noData: {
                            text: 'Data Not Found'
                        }
                    }, false, true);

                let todayFilter = dailyData.filter((item) => item.tanggal <= formatDate(new Date()));
                let today = todayFilter[todayFilter.length-1];

                let yesterdayFilter = dailyData.filter((item) => item.tanggal < formatDate(today ? today.tanggal : new Date(new Date().setDate(new Date().getDate() - 1))));
                let yesterday = yesterdayFilter[yesterdayFilter.length-1]

                let beforeFilter = dailyData.filter((item) => item.tanggal < formatDate(yesterday ? yesterday.tanggal : new Date(new Date().setDate(new Date().getDate() - 2))));
                let before = beforeFilter[beforeFilter.length-1];

                // Before
                let beforeEffElement = document.getElementById("before-eff-"+index);
                if (beforeEffElement) {
                    beforeEffElement.innerHTML = (before ? (before.mins_prod / before.mins_avail * 100).round(2) : 0)+" %";
                    colorizeEfficiency(beforeEffElement, (before ? (before.mins_prod / before.mins_avail * 100).round(2) : 0))
                    let beforeRftElement = document.getElementById("before-rft-"+index);
                    beforeRftElement.innerHTML = (before ? (before.rft / before.output * 100).round(2) : 0)+" %";
                    colorizeEfficiency(beforeRftElement, (before ? (before.rft / before.output * 100).round(2) : 0))
                }

                // Yesterday
                let yesterdayEffElement = document.getElementById("yesterday-eff-"+index);
                if (yesterdayEffElement) {
                    yesterdayEffElement.innerHTML = (yesterday ? (yesterday.mins_prod / yesterday.mins_avail * 100).round(2) : 0)+" %";
                    colorizeEfficiency(yesterdayEffElement, (yesterday ? (yesterday.mins_prod / yesterday.mins_avail * 100).round(2) : 0))
                    let yesterdayRftElement = document.getElementById("yesterday-rft-"+index);
                    yesterdayRftElement.innerHTML = (yesterday ? (yesterday.rft / yesterday.output * 100).round(2) : 0)+" %";
                    colorizeEfficiency(yesterdayRftElement, (yesterday ? (yesterday.rft / yesterday.output * 100).round(2) : 0))
                }

                // Today
                let todayEffElement = document.getElementById("today-eff-"+index);
                if (yesterdayEffElement) {
                    todayEffElement.innerHTML = (today ? (today.mins_prod / today.mins_avail * 100).round(2) : 0)+" %";
                    colorizeEfficiency(todayEffElement, (today ? (today.mins_prod / today.mins_avail * 100).round(2) : 0))
                    let todayRftElement = document.getElementById("today-rft-"+index);
                    todayRftElement.innerHTML = (today ? (today.rft / today.output * 100).round(2) : 0)+" %";
                    colorizeEfficiency(todayRftElement, (today ? (today.rft / today.output * 100).round(2) : 0))
                }

                if (formatDate(new Date()) > today.tanggal) {
                    let dayOneElement = document.getElementsByClassName("day-1");
                    for (let i = 0; i < dayOneElement.length; i++) {
                        dayOneElement[i].innerHTML = formatDateLocal(before.tanggal);
                    }

                    let dayTwoElement = document.getElementsByClassName("day-2");
                    for (let i = 0; i < dayTwoElement.length; i++) {
                        dayTwoElement[i].innerHTML = formatDateLocal(yesterday.tanggal);
                    }

                    let dayThreeElement = document.getElementsByClassName("day-3");
                    for (let i = 0; i < dayThreeElement.length; i++) {
                        dayThreeElement[i].innerHTML = formatDateLocal(today.tanggal);
                    }
                }
            } else {
                appendRow(data, index);
            }
        }
    </script>
@endsection
