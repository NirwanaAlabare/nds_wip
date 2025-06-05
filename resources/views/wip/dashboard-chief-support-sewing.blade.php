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

        .profile-frame {
            border-radius: 50%;
            overflow: hidden;
            border: 1px solid #cbcbcb;
            /* box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15); */
            margin-top: auto;
            margin-left: auto;
            margin-right: auto;
            display: flex;
            justify-content: flex-end;
            align-items: flex-end;
        }

        .profile-frame img {
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
            <div class="table-responsive swiper-no-swiping" id="chief-daily-efficiency-table" style="max-height: 100vh;">
                <table class="table table-bordered w-100">
                    <thead>
                        <tr>
                            <th rowspan="2" colspan="2" class="bg-sb text-light align-middle fw-bold text-center" style="font-size: 11px !important; padding: 1px !important;">Chief Daily Efficiency & RFT {{ $monthName }}</th>
                            <th colspan="2" class="bg-sb text-light fw-bold align-middle text-center day-1" style="font-size: 9px !important;padding: 1px !important;">H-2</th>
                            <th colspan="2" class="bg-sb text-light fw-bold align-middle text-center day-2" style="font-size: 9px !important;padding: 1px !important;">H-1</th>
                            <th colspan="2" class="bg-sb text-light fw-bold align-middle text-center day-3" style="font-size: 9px !important;padding: 1px !important;">Hari Ini</th>
                            <th rowspan="2" class="bg-sb text-light fw-bold align-middle text-center" style="font-size: 9px !important;padding: 1px !important;">Rank</th>
                        </tr>
                        <tr>
                            <th class="bg-sb text-light fw-bold align-middle text-center" style="font-size: 9px !important;padding: 1px !important;">Effy</th>
                            <th class="bg-sb text-light fw-bold align-middle text-center" style="font-size: 9px !important;padding: 1px !important;">RFT</th>
                            <th class="bg-sb text-light fw-bold align-middle text-center" style="font-size: 9px !important;padding: 1px !important;">Effy</th>
                            <th class="bg-sb text-light fw-bold align-middle text-center" style="font-size: 9px !important;padding: 1px !important;">RFT</th>
                            <th class="bg-sb text-light fw-bold align-middle text-center" style="font-size: 9px !important;padding: 1px !important;">Effy</th>
                            <th class="bg-sb text-light fw-bold align-middle text-center" style="font-size: 9px !important;padding: 1px !important;">RFT</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </swiper-slide>
    </swiper-container>
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
                    let chiefEfficiency = objectValues(objectGroupBy(response, ({ chief_nik }) => chief_nik));

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

                        // Sort date output efficiency
                        let sortedDateOutput = dateOutput.sort(function(a,b) {
                            if (a.tanggal > b.tanggal) {
                                return 1;
                            }
                            if (a.tanggal < b.tanggal) {
                                return -1;
                            }
                            return 0;
                        });

                        let dateOutputFilter = sortedDateOutput.filter((item) => item.mins_avail > 0 && item.mins_prod > 0);
                        let currentFilter = dateOutputFilter.filter((item) => item.tanggal == formatDate(new Date()));
                        let currentData = currentFilter.length > 0 ? currentFilter[0] : dateOutputFilter[dateOutputFilter.length-1];

                        // Leader Output
                        let leaderOutput = [];
                        let totalMinsAvail = 0;
                        let totalMinsProd = 0;
                        let totalOutput = 0;
                        let totalRft = 0;
                        element.reduce(function(res, value) {
                            let param = value.leader_nik ? value.leader_nik : value.sewing_line;
                            if (!res[param]) {
                                res[param] = { leader_id: value.leader_id, leader_nik: value.leader_nik, leader_name: value.leader_name, sewing_line: "", mins_avail: 0, mins_prod: 0, output: 0, rft: 0 };
                                leaderOutput.push(res[param]);
                            }

                            if (!res[param].tanggal || res[param].tanggal <= value.tanggal) {
                                res[param].tanggal = value.tanggal;
                            }
                            res[param].mins_avail += Number(value.cumulative_mins_avail);
                            res[param].mins_prod += Number(value.mins_prod);
                            res[param].output += Number(value.output);
                            res[param].rft += Number(value.rft);
                            if (!res[param].sewing_line.includes(value.sewing_line)) {
                                res[param].sewing_line += value.sewing_line + "<br>";
                            }

                            totalMinsAvail += Number(value.cumulative_mins_avail);
                            totalMinsProd += Number(value.mins_prod);
                            totalOutput += Number(value.output);
                            totalRft += Number(value.rft);

                            return res;
                        }, {});

                        if (element[0].chief_name == "SUKAMTONO") {
                            console.log(leaderOutput);
                        }

                        // Total Data
                        let totalData = { totalEfficiency : Number((totalMinsProd/totalMinsAvail*100).toFixed(2)), totalRft : Number((totalRft/totalOutput*100).toFixed(2)) };

                        // Sort leader output efficiency
                        let sortedLeaderOutput = leaderOutput.sort(function(a,b){
                            if (((a.mins_prod/a.mins_avail)+(a.rft/a.output)) < ((b.mins_prod/b.mins_avail)+(b.rft/b.output))) {
                                return 1;
                            }
                            if (((a.mins_prod/a.mins_avail)+(a.rft/a.output)) > ((b.mins_prod/b.mins_avail)+(b.rft/b.output))) {
                                return -1;
                            }
                            return 0;
                        });

                        // Support Output
                        let ieOutput = groupByRole(element, currentData?.tanggal, "ie");
                        let leaderqcOutput = groupByRole(element, currentData?.tanggal, "leaderqc");
                        let mechanicOutput = groupByRole(element, currentData?.tanggal, "mechanic");
                        let technicalOutput = groupByRole(element, currentData?.tanggal, "technical");

                        let sortedIeOutput = ieOutput.sort(function(a, b) {
                            let nameA = a && a.ie_name ? a.ie_name.toLowerCase() : '-';
                            let nameB = b && b.ie_name ? b.ie_name.toLowerCase() : '-';
                            if (nameA < nameB) return -1;
                            if (nameA > nameB) return 1;
                            return 0;
                        });

                        let sortedLeaderqcOutput = leaderqcOutput.sort(function(a, b) {
                            let nameA = a && a.leaderqc_name ? a.leaderqc_name.toLowerCase() : '-';
                            let nameB = b && b.leaderqc_name ? b.leaderqc_name.toLowerCase() : '-';
                            if (nameA < nameB) return -1;
                            if (nameA > nameB) return 1;
                            return 0;
                        });

                        let sortedMechanicOutput = mechanicOutput.sort(function(a, b) {
                            let nameA = a && a.mechanic_name ? a.mechanic_name.toLowerCase() : '-';
                            let nameB = b && b.mechanic_name ? b.mechanic_name.toLowerCase() : '-';
                            if (nameA < nameB) return -1;
                            if (nameA > nameB) return 1;
                            return 0;
                        });

                        let sortedTechnicalOutput = technicalOutput.sort(function(a, b) {
                            let nameA = a && a.technical_name ? a.technical_name.toLowerCase() : '-';
                            let nameB = b && b.technical_name ? b.technical_name.toLowerCase() : '-';
                            if (nameA < nameB) return -1;
                            if (nameA > nameB) return 1;
                            return 0;
                        });

                        chiefDailyEfficiency.push({"id": element[0].chief_id ? element[0].chief_id : 'KOSONG', "nik": element[0].chief_nik ? element[0].chief_nik : 'KOSONG', "name": element[0].chief_name ? element[0].chief_name : 'KOSONG', "data": sortedDateOutput, "leaderData": sortedLeaderOutput, "ieData": sortedIeOutput, "leaderqcData": sortedLeaderqcOutput, "mechanicData": sortedMechanicOutput, "technicalData": sortedTechnicalOutput, "currentEff": (totalData ? totalData.totalEfficiency : 0), "currentRft": (totalData ? totalData.totalRft : 0)});
                    });

                    // Sort Chief Daily by Efficiency
                    let sortedChiefDailyEfficiency = chiefDailyEfficiency.sort(function(a,b){
                        if (a.currentEff+a.currentRft < b.currentEff+b.currentRft) {
                            return 1;
                        }
                        if (a.currentEff+a.currentRft  > b.currentEff+b.currentRft) {
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
            console.log("update data");

            // updateData();
        }, 60000);

        var currentDayOne = "";
        var currentDayTwo = "";
        var currentDayThree = "";

        async function appendRow(data, index) {
            let tableElement = document.getElementById('chief-daily-efficiency-table');
            let table = document.querySelector('#chief-daily-efficiency-table tbody');

            // Name
            let tr = document.createElement("tr");
            let tdName = document.createElement("td");
            tdName.style.minWidth = "350px";
            tdName.style.width = "400px";
            tdName.style.padding = "3px 10px";
            let employeeContainer = document.createElement("div");
            employeeContainer.id = "employee-"+index;
            employeeContainer.style.marginLeft = "auto";
            employeeContainer.style.marginRight = "auto";
            employeeContainer.style.height = "100%";
            employeeContainer.classList.add("row");

            // Chief
            let chiefName = data.name ? data.name.split(" ")[0] : '-';
            let chiefContainer = document.createElement("div");
            chiefContainer.classList.add("col-3");
            chiefContainer.classList.add("p-1");
            chiefContainer.classList.add("border");
            let imageContainer = document.createElement("div");
            imageContainer.classList.add("profile-frame");
            imageContainer.style.width = "75px";
            imageContainer.style.height = "75px";
            let imageElement = document.createElement("img");
            imageElement.src = "{{ asset('../storage/employee_profile') }}/"+data.nik+"%20"+data.name+".png"
            imageElement.setAttribute("onerror", "this.onerror=null; this.src='{{ asset('dist/img/person.png') }}'");
            imageElement.setAttribute("alt", "person")
            imageElement.classList.add("img-fluid")
            // imageElement.style.width = "200px";
            // imageElement.style.height = "150px";
            imageElement.style.marginTop = "auto";
            imageElement.style.marginLeft = "auto";
            imageElement.style.marginRight = "auto";
            imageContainer.appendChild(imageElement);
            chiefContainer.appendChild(imageContainer);
            chiefContainer.innerHTML += "<span class='text-sb fw-bold' style='font-size: 8.5px;'><center>"+data.name.split(" ")[0]+"</center></span>"

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

            // Today Date
            let todayDate = null;
            let yesterdayDate = null;
            let beforeDate = null;

            if (index > 1) {
                todayDate = currentDayThree;
                yesterdayDate = currentDayTwo;
                beforeDate = currentDayOne;

                var todayFilter = dailyData.filter((item) => (todayDate ? item.tanggal == formatDate(todayDate) : item.tanggal <= formatDate(new Date())) );
                var today = todayFilter[todayFilter.length-1];

                var yesterdayFilter = dailyData.filter((item) => (yesterdayDate ? item.tanggal == formatDate(yesterdayDate) : (item.tanggal < formatDate(today ? today.tanggal : new Date(new Date().setDate(new Date().getDate() - 1))))) );
                var yesterday = yesterdayFilter[yesterdayFilter.length-1]

                var beforeFilter = dailyData.filter((item) => (beforeDate ? item.tanggal == formatDate(beforeDate) : (item.tanggal < formatDate(yesterday ? yesterday.tanggal : new Date(new Date().setDate(new Date().getDate() - 2))))) );
                var before = beforeFilter[beforeFilter.length-1];
            } else {
                var todayFilter = dailyData.filter((item) => item.tanggal <= formatDate(new Date()));
                var today = todayFilter[todayFilter.length-1];

                var yesterdayFilter = todayFilter.filter((item) => (item.tanggal < formatDate(today ? today.tanggal : new Date(new Date().setDate(new Date().getDate() - 1)))));
                var yesterday = yesterdayFilter[yesterdayFilter.length-1]

                var beforeFilter = yesterdayFilter.filter((item) => (item.tanggal < formatDate(yesterday ? yesterday.tanggal : new Date(new Date().setDate(new Date().getDate() - 2)))));
                var before = beforeFilter[beforeFilter.length-1];
            }

            // Sub Employee
            let subEmployeeContainer = document.createElement("div");
            subEmployeeContainer.classList.add("col-9");
            subEmployeeContainer.classList.add("d-flex");
            subEmployeeContainer.classList.add("flex-wrap");

            // Leader
            let leaderContainer = document.createElement("div");
            leaderContainer.classList.add("w-100");
            let leadersElement = document.createElement("div");
            leadersElement.classList.add("row");
            leadersElement.classList.add("h-100");
            data.leaderData.forEach(element => {
                if (element.tanggal >= (today ? today.tanggal : todayDate)) {
                    let leaderName = element.leader_name ? element.leader_name.split(" ")[0] : 'KOSONG';
                    let leaderElement = document.createElement("div");
                    leaderElement.classList.add("col-2");
                    leaderElement.classList.add("p-1");
                    leaderElement.classList.add("border");
                    leaderElement.classList.add("d-flex");
                    leaderElement.classList.add("flex-column");
                    let leaderImageContainer = document.createElement("div");
                    leaderImageContainer.classList.add("m-auto");
                    let leaderImageSubContainer = document.createElement("div");
                    leaderImageSubContainer.classList.add("profile-frame");
                    leaderImageSubContainer.style.width = "33px";
                    leaderImageSubContainer.style.height = "33px";
                    let leaderImageElement = document.createElement("img");
                    leaderImageElement.src = "{{ asset('../storage/employee_profile') }}/"+element.leader_nik+"%20"+element.leader_name+".png"
                    leaderImageElement.setAttribute("onerror", "this.onerror=null; this.src='{{ asset('dist/img/person.png') }}'");
                    leaderImageElement.setAttribute("alt", "person")
                    leaderImageElement.classList.add("img-fluid")
                    // leaderImageElement.style.width = "50px";
                    // leaderImageElement.style.height = "50px";
                    leaderImageSubContainer.appendChild(leaderImageElement)
                    leaderImageContainer.appendChild(leaderImageSubContainer)
                    leaderElement.appendChild(leaderImageContainer);
                    leaderImageContainer.innerHTML += "<span class='text-sb fw-bold' style='font-size: 7px;'><center>"+(leaderName.length > 10 ? leaderName.slice(0, 9) : leaderName)+"</center></span>";
                    leaderImageContainer.innerHTML += "<span class='text-dark fw-bold' style='font-size: 6.5px;'><center>LEADER</center></span>";
                    leaderImageContainer.innerHTML += "<span class='text-sb-secondary fw-bold' style='font-size: 7px;'><center>"+element.sewing_line.replace(/_/g, " ").toUpperCase()+"</center></span>";
                    leadersElement.appendChild(leaderElement);
                }
            });
            leaderContainer.appendChild(leadersElement);

            let supportEmployeeContainer = document.createElement("div");
            supportEmployeeContainer.classList.add("w-100");
            let supportEmployeeElements = document.createElement("div");
            supportEmployeeElements.classList.add("row");
            supportEmployeeElements.classList.add("h-100");

            // IE
            let ieContainer = document.createElement("div");
            ieContainer.classList.add("col-2");
            ieContainer.classList.add("p-0");
            let iesElement = document.createElement("div");
            iesElement.classList.add("d-flex");
            iesElement.classList.add("flex-column");
            iesElement.classList.add("border");
            // iesElement.classList.add("gap-1");
            iesElement.classList.add("h-100");
            iesElement.innerHTML = '<p style="font-size: 3px;" class="text-light fw-bold bg-info mb-0">&nbsp;</p>';
            data.ieData.forEach(element => {
                if (element.tanggal >= (today ? today.tanggal : todayDate)) {
                    if (element.ie_name) {
                        let ieName = element.ie_name ? element.ie_name.split(" ")[0] : 'KOSONG';
                        let ieElement = document.createElement("div");
                        ieElement.classList.add("w-100");
                        ieElement.classList.add("p-1");
                        // ieElement.classList.add("border");
                        ieElement.classList.add("d-flex");
                        ieElement.classList.add("flex-column");
                        let ieImageContainer = document.createElement("div");
                        ieImageContainer.classList.add("m-auto");
                        let ieImageSubContainer = document.createElement("div");
                        ieImageSubContainer.classList.add("profile-frame");
                        ieImageSubContainer.style.width = "33px";
                        ieImageSubContainer.style.height = "33px";
                        let ieImageElement = document.createElement("img");
                        ieImageElement.src = "{{ asset('../storage/employee_profile') }}/"+element.ie_nik+"%20"+element.ie_name+".png"
                        ieImageElement.setAttribute("onerror", "this.onerror=null; this.src='{{ asset('dist/img/person.png') }}'");
                        ieImageElement.setAttribute("alt", "person")
                        ieImageElement.classList.add("img-fluid")
                        // ieImageElement.style.width = "50px";
                        // ieImageElement.style.height = "50px";
                        ieImageSubContainer.appendChild(ieImageElement)
                        ieImageContainer.appendChild(ieImageSubContainer)
                        ieElement.appendChild(ieImageContainer);
                        ieImageContainer.innerHTML += "<span class='text-sb fw-bold' style='font-size: 7px;'><center>"+(ieName.length > 10 ? ieName.slice(0, 9) : ieName)+"</center></span>";
                        ieImageContainer.innerHTML += "<span class='text-info fw-bold' style='font-size: 6.5px;'><center>IE</center></span>";
                        // ieImageContainer.innerHTML += "<span class='text-sb-secondary fw-bold' style='font-size: 7px;'><center>"+element.sewing_line.replace(/_/g, " ").toUpperCase()+"</center></span>";
                        iesElement.appendChild(ieElement);
                    }
                }
            });
            ieContainer.appendChild(iesElement);

            // leaderqc
            let leaderqcContainer = document.createElement("div");
            leaderqcContainer.classList.add("col-2");
            leaderqcContainer.classList.add("p-0");
            let leaderqcsElement = document.createElement("div");
            leaderqcsElement.classList.add("border");
            leaderqcsElement.classList.add("d-flex");
            leaderqcsElement.classList.add("flex-column");
            // leaderqcsElement.classList.add("gap-1");
            leaderqcsElement.classList.add("h-100");
            leaderqcsElement.innerHTML = '<p style="font-size: 3px;" class="text-light fw-bold bg-danger mb-0">&nbsp;</p>';
            data.leaderqcData.forEach(element => {
                if (element.leaderqc_name) {
                    if (element.tanggal >= (today ? today.tanggal : todayDate)) {
                        let leaderqcName = element.leaderqc_name ? element.leaderqc_name.split(" ")[0] : 'KOSONG';
                        let leaderqcElement = document.createElement("div");
                        leaderqcElement.classList.add("w-100");
                        leaderqcElement.classList.add("p-1");
                        // leaderqcElement.classList.add("border");
                        leaderqcElement.classList.add("d-flex");
                        leaderqcElement.classList.add("flex-column");
                        let leaderqcImageContainer = document.createElement("div");
                        leaderqcImageContainer.classList.add("m-auto");
                        let leaderqcImageSubContainer = document.createElement("div");
                        leaderqcImageSubContainer.classList.add("profile-frame");
                        leaderqcImageSubContainer.style.width = "33px";
                        leaderqcImageSubContainer.style.height = "33px";
                        let leaderqcImageElement = document.createElement("img");
                        leaderqcImageElement.src = "{{ asset('../storage/employee_profile') }}/"+element.leaderqc_nik+"%20"+element.leaderqc_name+".png"
                        leaderqcImageElement.setAttribute("onerror", "this.onerror=null; this.src='{{ asset('dist/img/person.png') }}'");
                        leaderqcImageElement.setAttribute("alt", "person")
                        leaderqcImageElement.classList.add("img-fluid")
                        // leaderqcImageElement.style.width = "50px";
                        // leaderqcImageElement.style.height = "50px";
                        leaderqcImageSubContainer.appendChild(leaderqcImageElement)
                        leaderqcImageContainer.appendChild(leaderqcImageSubContainer)
                        leaderqcElement.appendChild(leaderqcImageContainer);
                        leaderqcImageContainer.innerHTML += "<span class='text-sb fw-bold' style='font-size: 7px;'><center>"+(leaderqcName.length > 10 ? leaderqcName.slice(0, 9) : leaderqcName)+"</center></span>";
                        leaderqcImageContainer.innerHTML += "<span class='text-danger fw-bold' style='font-size: 6px;'><center>LEADER QC</center></span>";
                        // leaderqcImageContainer.innerHTML += "<span class='text-sb-secondary fw-bold' style='font-size: 7px;'><center>"+element.sewing_line.replace(/_/g, " ").toUpperCase()+"</center></span>";
                        leaderqcsElement.appendChild(leaderqcElement);
                    }
                }
            });
            leaderqcContainer.appendChild(leaderqcsElement);

            // mechanic
            let mechanicContainer = document.createElement("div");
            mechanicContainer.classList.add("col-2");
            mechanicContainer.classList.add("p-0");
            let mechanicsElement = document.createElement("div");
            mechanicsElement.classList.add("d-flex");
            mechanicsElement.classList.add("flex-column");
            mechanicsElement.classList.add("border");
            // mechanicsElement.classList.add("gap-1");
            mechanicsElement.classList.add("h-100");
            mechanicsElement.innerHTML = '<p style="font-size: 3px;" class="text-light fw-bold bg-success mb-0">&nbsp;</p>';
            data.mechanicData.forEach(element => {
                if (element.tanggal >= (today ? today.tanggal : todayDate)) {
                    if (element.mechanic_name) {
                        let mechanicName = element.mechanic_name ? element.mechanic_name.split(" ")[0] : 'KOSONG';
                        let mechanicElement = document.createElement("div");
                        mechanicElement.classList.add("w-100");
                        mechanicElement.classList.add("p-1");
                        // mechanicElement.classList.add("border");
                        mechanicElement.classList.add("d-flex");
                        mechanicElement.classList.add("flex-column");
                        let mechanicImageContainer = document.createElement("div");
                        mechanicImageContainer.classList.add("m-auto");
                        let mechanicImageSubContainer = document.createElement("div");
                        mechanicImageSubContainer.classList.add("profile-frame");
                        mechanicImageSubContainer.style.width = "33px";
                        mechanicImageSubContainer.style.height = "33px";
                        let mechanicImageElement = document.createElement("img");
                        mechanicImageElement.src = "{{ asset('../storage/employee_profile') }}/"+element.mechanic_nik+"%20"+element.mechanic_name+".png"
                        mechanicImageElement.setAttribute("onerror", "this.onerror=null; this.src='{{ asset('dist/img/person.png') }}'");
                        mechanicImageElement.setAttribute("alt", "person")
                        mechanicImageElement.classList.add("img-fluid")
                        // mechanicImageElement.style.width = "50px";
                        // mechanicImageElement.style.height = "50px";
                        mechanicImageSubContainer.appendChild(mechanicImageElement)
                        mechanicImageContainer.appendChild(mechanicImageSubContainer)
                        mechanicElement.appendChild(mechanicImageContainer);
                        mechanicImageContainer.innerHTML += "<span class='text-sb fw-bold' style='font-size: 7px;'><center>"+(mechanicName.length > 10 ? mechanicName.slice(0, 9) : mechanicName)+"</center></span>";
                        mechanicImageContainer.innerHTML += "<span class='text-success fw-bold' style='font-size: 6.5px;'><center>MECHANIC</center></span>";
                        // mechanicImageContainer.innerHTML += "<span class='text-sb-secondary fw-bold' style='font-size: 7px;'><center>"+element.sewing_line.replace(/_/g, " ").toUpperCase()+"</center></span>";
                        mechanicsElement.appendChild(mechanicElement);
                    }
                }
            });
            mechanicContainer.appendChild(mechanicsElement);

            // technical
            let technicalContainer = document.createElement("div");
            technicalContainer.classList.add("col-2");
            technicalContainer.classList.add("p-0");
            let technicalsElement = document.createElement("div");
            technicalsElement.classList.add("d-flex");
            technicalsElement.classList.add("flex-column");
            technicalsElement.classList.add("border");
            // technicalsElement.classList.add("gap-1");
            technicalsElement.classList.add("h-100");
            technicalsElement.innerHTML = '<p style="font-size: 3px;" class="text-light fw-bold bg-primary mb-0">&nbsp;</p>';
            data.technicalData.forEach(element => {
                if (element.tanggal >= (today ? today.tanggal : todayDate)) {
                    if (element.technical_name) {
                        let technicalName = element.technical_name ? element.technical_name.split(" ")[0] : 'KOSONG';
                        let technicalElement = document.createElement("div");
                        technicalElement.classList.add("w-100");
                        technicalElement.classList.add("p-1");
                        // technicalElement.classList.add("border");
                        technicalElement.classList.add("d-flex");
                        technicalElement.classList.add("flex-column");
                        let technicalImageContainer = document.createElement("div");
                        technicalImageContainer.classList.add("m-auto");
                        let technicalImageSubContainer = document.createElement("div");
                        technicalImageSubContainer.classList.add("profile-frame");
                        technicalImageSubContainer.style.width = "33px";
                        technicalImageSubContainer.style.height = "33px";
                        let technicalImageElement = document.createElement("img");
                        technicalImageElement.src = "{{ asset('../storage/employee_profile') }}/"+element.technical_nik+"%20"+element.technical_name+".png"
                        technicalImageElement.setAttribute("onerror", "this.onerror=null; this.src='{{ asset('dist/img/person.png') }}'");
                        technicalImageElement.setAttribute("alt", "person")
                        technicalImageElement.classList.add("img-fluid")
                        // technicalImageElement.style.width = "50px";
                        // technicalImageElement.style.height = "50px";
                        technicalImageSubContainer.appendChild(technicalImageElement)
                        technicalImageContainer.appendChild(technicalImageSubContainer)
                        technicalElement.appendChild(technicalImageContainer);
                        technicalImageContainer.innerHTML += "<span class='text-sb fw-bold' style='font-size: 7px;'><center>"+(technicalName.length > 10 ? technicalName.slice(0, 9) : technicalName)+"</center></span>";
                        technicalImageContainer.innerHTML += "<span class='text-primary fw-bold' style='font-size: 6.5px;'><center>TECHNICAL</center></span>";
                        // technicalImageContainer.innerHTML += "<span class='text-sb-secondary fw-bold' style='font-size: 7px;'><center>"+element.sewing_line.replace(/_/g, " ").toUpperCase()+"</center></span>";
                        technicalsElement.appendChild(technicalElement);
                    }
                }
            });
            technicalContainer.appendChild(technicalsElement);

            subEmployeeContainer.appendChild(leaderContainer)
            supportEmployeeElements.appendChild(ieContainer)
            supportEmployeeElements.appendChild(leaderqcContainer)
            supportEmployeeElements.appendChild(mechanicContainer)
            supportEmployeeElements.appendChild(technicalContainer)
            supportEmployeeContainer.appendChild(supportEmployeeElements)
            subEmployeeContainer.appendChild(supportEmployeeContainer)

            employeeContainer.appendChild(chiefContainer)
            // employeeContainer.appendChild(leaderContainer)
            employeeContainer.appendChild(subEmployeeContainer)
            tdName.appendChild(employeeContainer);
            tdName.classList.add("align-middle");
            tr.appendChild(tdName);

            // Chart
            let tdChart = document.createElement("td");
            tdChart.style.minWidth = '350px';
            tdChart.style.width = '400px';
            tdChart.style.padding = '0px 20px 0px 0px';
            let canvas = document.createElement("div");
            // canvas.id = "chart-"+index;
            canvas.classList.add("chief-daily-efficiency-chart");
            tdChart.appendChild(canvas);
            tdChart.classList.add("align-middle");
            tr.appendChild(tdChart);

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
                        fontSize: "5px",
                    }
                },
                stroke: {
                    curve: 'smooth'
                },
                // title: {
                //     enable: false,
                //     text: 'Daily '+$("#month-name").val(),
                //     align: 'left'
                // },
                grid: {
                    borderColor: '#e7e7e7',
                    row: {
                        colors: ['#f3f3f3', 'transparent'], // takes an array which will be repeated on columns
                        opacity: 0.5
                    },
                },
                yaxis: {
                    tickAmount: 1,
                    labels: {
                        style: {
                            fontSize: "5px",
                        }
                    }
                },
                xaxis: {
                    categories: tglArr,
                    labels: {
                        style: {
                            fontSize: "6.5px",
                        }
                    }
                },
                noData: {
                    text: 'Data Not Found'
                },
                legend: {
                    position: 'top',
                    horizontalAlign: 'center',
                    floating: true,
                    offsetY: 5,
                    offsetX: -5,
                    fontSize: "8.5px",
                },
                redrawOnParentResize: true
            };

            var chart = new ApexCharts(canvas, options);

            // Before
            let tdBeforeEff = document.createElement("td");
            tdBeforeEff.id = "before-eff-"+index;
            tdBeforeEff.innerHTML = (before ? (before.mins_prod / before.mins_avail * 100).round(2) : 0)+"%";
            tr.appendChild(tdBeforeEff);
            tdBeforeEff.classList.add("text-center");
            tdBeforeEff.classList.add("align-middle");
            tdBeforeEff.classList.add("fw-bold");
            tdBeforeEff.style.padding = '1px !important';
            colorizeEfficiency(tdBeforeEff, (before ? (before.mins_prod / before.mins_avail * 100).round(2) : 0));
            tdBeforeEff.classList.add("fs-6");
            let tdBeforeRft = document.createElement("td");
            tdBeforeRft.id = "before-rft-"+index;
            tdBeforeRft.innerHTML = (before ? (before.rft / before.output * 100).round(2) : 0)+"%";
            tr.appendChild(tdBeforeRft);
            tdBeforeRft.classList.add("text-center");
            tdBeforeRft.classList.add("align-middle");
            tdBeforeRft.classList.add("fw-bold");
            colorizeRft(tdBeforeRft, (before ? (before.rft / before.output * 100).round(2) : 0))
            tdBeforeRft.classList.add("fs-6");
            tdBeforeRft.style.padding = '1px !important';

            // Yesterday
            let tdYesterdayEff = document.createElement("td");
            tdYesterdayEff.id = "yesterday-eff-"+index;
            tdYesterdayEff.innerHTML = (yesterday ? (yesterday.mins_prod / yesterday.mins_avail * 100).round(2) : 0)+"%";
            tr.appendChild(tdYesterdayEff);
            tdYesterdayEff.classList.add("text-center");
            tdYesterdayEff.classList.add("align-middle");
            tdYesterdayEff.classList.add("fw-bold");
            tdYesterdayEff.style.padding = '1px !important';
            colorizeEfficiency(tdYesterdayEff, (yesterday ? (yesterday.mins_prod / yesterday.mins_avail * 100).round(2) : 0))
            tdYesterdayEff.classList.add("fs-6");
            let tdYesterdayRft = document.createElement("td");
            tdYesterdayRft.id = "yesterday-rft-"+index;
            tdYesterdayRft.innerHTML = (yesterday ? (yesterday.rft / yesterday.output * 100).round(2) : 0)+"%";
            tr.appendChild(tdYesterdayRft);
            tdYesterdayRft.classList.add("text-center");
            tdYesterdayRft.classList.add("align-middle");
            tdYesterdayRft.classList.add("fw-bold");
            colorizeRft(tdYesterdayRft, (yesterday ? (yesterday.rft / yesterday.output * 100).round(2) : 0))
            tdYesterdayRft.classList.add("fs-6");
            tdYesterdayRft.style.padding = '1px !important';

            // Today
            let tdTodayEff = document.createElement("td");
            tdTodayEff.id = "today-eff-"+index;
            tdTodayEff.innerHTML = (today ? (today.mins_prod / today.mins_avail * 100).round(2) : 0)+"%";
            tr.appendChild(tdTodayEff);
            tdTodayEff.classList.add("text-center");
            tdTodayEff.classList.add("align-middle");
            tdTodayEff.classList.add("fw-bold");
            colorizeEfficiency(tdTodayEff, (today ? (today.mins_prod / today.mins_avail * 100).round(2) : 0))
            tdTodayEff.classList.add("fs-6");
            tdTodayEff.classList.add("align-middle");
            tdYesterdayEff.style.padding = "1px !important"
            let tdTodayRft = document.createElement("td");
            tdTodayRft.id = "today-rft-"+index;
            tdTodayRft.innerHTML = (today ? (today.rft / today.output * 100).round(2) : 0)+"%";
            tr.appendChild(tdTodayRft);
            tdTodayRft.classList.add("text-center");
            tdTodayRft.classList.add("align-middle");
            tdTodayRft.classList.add("fw-bold");
            colorizeRft(tdTodayRft, (today ? (today.rft / today.output * 100).round(2) : 0))
            tdTodayRft.classList.add("fs-6");
            tdYesterdayRft.style.padding = "1px !important"

            if (index == 1) {
                currentDayOne = before ? before.tanggal : beforeDate;
                currentDayTwo = yesterday ? yesterday.tanggal : yesterdayDate;
                currentDayThree = today ? today.tanggal : todayDate;

                if (formatDate(new Date()) > today ? today.tanggal : todayDate) {
                    let dayOneElement = document.getElementsByClassName("day-1");
                    for (let i = 0; i < dayOneElement.length; i++) {
                        dayOneElement[i].innerHTML = formatDateLocal(before ? before.tanggal : beforeDate);
                    }

                    let dayTwoElement = document.getElementsByClassName("day-2");
                    for (let i = 0; i < dayTwoElement.length; i++) {
                        dayTwoElement[i].innerHTML = formatDateLocal(yesterday ? yesterday.tanggal : yesterdayDate);
                    }

                    let dayThreeElement = document.getElementsByClassName("day-3");
                    for (let i = 0; i < dayThreeElement.length; i++) {
                        dayThreeElement[i].innerHTML = formatDateLocal(today ? today.tanggal : todayDate);
                    }
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

            // Slide per 2 row
                // if (index % 2 == 0) {
                //     tableElement.id = "chief-daily-efficiency-table-"+((index/2)+1);
                //     let newTable = tableElement.cloneNode();
                //     newTable.id = "chief-daily-efficiency-table";
                //     newTable.innerHTML = `
                //         <table class="table table-bordered w-100">
                //             <thead>
                //                 <tr>
                //                     <th rowspan="2" colspan="2" class="bg-sb text-light align-middle fw-bold text-center" style="font-size: 20px !important; padding: 5px !important;">Chief Daily Efficiency & RFT {{ $monthName }}</th>
                //                     <th colspan="2" class="bg-sb text-light fw-bold align-middle text-center day-1" style="padding: 5px !important;">H-2</th>
                //                     <th colspan="2" class="bg-sb text-light fw-bold align-middle text-center day-2" style="padding: 5px !important;">H-1</th>
                //                     <th colspan="2" class="bg-sb text-light fw-bold align-middle text-center day-3" style="padding: 5px !important;">Hari Ini</th>
                //                     <th rowspan="2" class="bg-sb text-light fw-bold align-middle text-center" style="padding: 5px !important;">Rank</th>
                //                 </tr>
                //                 <tr>
                //                     <th class="bg-sb text-light fw-bold align-middle text-center" style="font-size: 10px;padding: 5px !important;">Effy</th>
                //                     <th class="bg-sb text-light fw-bold align-middle text-center" style="font-size: 10px;padding: 5px !important;">RFT</th>
                //                     <th class="bg-sb text-light fw-bold align-middle text-center" style="font-size: 10px;padding: 5px !important;">Effy</th>
                //                     <th class="bg-sb text-light fw-bold align-middle text-center" style="font-size: 10px;padding: 5px !important;">RFT</th>
                //                     <th class="bg-sb text-light fw-bold align-middle text-center" style="font-size: 10px;padding: 5px !important;">Effy</th>
                //                     <th class="bg-sb text-light fw-bold align-middle text-center" style="font-size: 10px;padding: 5px !important;">RFT</th>
                //                 </tr>
                //             </thead>
                //             <tbody>
                //             </tbody>
                //         </table>
                //     `;

                //     let carouselContainer = document.getElementById("table-carousel");
                //     let carouselElement = document.getElementById("carousel-1").cloneNode();
                //     carouselElement.id = "carousel-"+((index/2)+1);

                //     carouselElement.appendChild(newTable);
                //     carouselContainer.appendChild(carouselElement);
                // }

            // initSparkle();
        }

        // Colorize Efficiency
        function colorizeEfficiency(element, efficiency) {
            if (isElement(element)) {
                switch (true) {
                    case efficiency < 75 :
                        element.style.color = '#dc3545';
                        break;
                    case efficiency >= 75 && efficiency <= 85 :
                        element.style.color = 'rgb(240, 153, 0)';
                        break;
                    case efficiency > 85 :
                        element.style.color = '#28a745';
                        break;
                }
            }
        }

        // Colorize RFT
        function colorizeRft(element, rft) {
            if (isElement(element)) {
                switch (true) {
                    case rft < 97 :
                        element.style.color = '#dc3545';
                        break;
                    case rft >= 97 && rft < 98 :
                        element.style.color = 'rgb(240, 153, 0)';
                        break;
                    case rft >= 98 :
                        element.style.color = '#28a745';
                        break;
                }
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
                    let chiefEfficiency = objectValues(objectGroupBy(response, ({ chief_nik }) => chief_nik));

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

                        // Sort date output efficiency
                        let sortedDateOutput = dateOutput.sort(function(a,b){
                            if (a.tanggal > b.tanggal) {
                                return 1;
                            }
                            if (a.tanggal < b.tanggal) {
                                return -1;
                            }
                            return 0;
                        });

                        let dateOutputFilter = sortedDateOutput.filter((item) => item.mins_avail > 0 && item.mins_prod > 0);
                        let currentFilter = dateOutputFilter.filter((item) => item.tanggal == formatDate(new Date()));
                        let currentData = currentFilter.length > 0 ? currentFilter[0] : dateOutputFilter[dateOutputFilter.length-1];

                        // Leader Output
                        let leaderOutput = [];
                        element.reduce(function(res, value) {
                            if (value.tanggal == (currentData ? currentData.tanggal : formatDate(new Date()))) {
                                let param = value.leader_nik ? value.leader_nik : value.sewing_line;
                                if (!res[param]) {
                                    res[param] = { leader_id: value.leader_id, leader_nik: value.leader_nik, leader_name: value.leader_name, sewing_line: "", mins_avail: 0, mins_prod: 0, output: 0, rft: 0 };
                                    leaderOutput.push(res[param]);
                                }
                                res[param].tanggal = value.tanggal;
                                res[param].mins_avail += Number(value.cumulative_mins_avail);
                                res[param].mins_prod += Number(value.mins_prod);
                                res[param].output += Number(value.output);
                                res[param].rft += Number(value.rft);
                                res[param].sewing_line += value.sewing_line+"<br>";
                            }

                            return res;
                        }, {});

                        // Sort leader output efficiency
                        let sortedLeaderOutput = leaderOutput.sort(function(a,b){
                            if (((a.mins_prod/a.mins_avail)+(a.rft/a.output)) < ((b.mins_prod/b.mins_avail)+(b.rft/b.output))) {
                                return 1;
                            }
                            if (((a.mins_prod/a.mins_avail)+(a.rft/a.output)) > ((b.mins_prod/b.mins_avail)+(b.rft/b.output))) {
                                return -1;
                            }
                            return 0;
                        });

                        // Support Output
                        let ieOutput = groupByRole(element, currentData?.tanggal, "ie");
                        let leaderqcOutput = groupByRole(element, currentData?.tanggal, "leaderqc");
                        let mechanicOutput = groupByRole(element, currentData?.tanggal, "mechanic");
                        let technicalOutput = groupByRole(element, currentData?.tanggal, "technical");

                        let sortedIeOutput = ieOutput.sort(function(a, b) {
                            let nameA = a && a.ie_name ? a.ie_name.toLowerCase() : '-';
                            let nameB = b && b.ie_name ? b.ie_name.toLowerCase() : '-';
                            if (nameA < nameB) return -1;
                            if (nameA > nameB) return 1;
                            return 0;
                        });

                        let sortedLeaderqcOutput = leaderqcOutput.sort(function(a, b) {
                            let nameA = a && a.leaderqc_name ? a.leaderqc_name.toLowerCase() : '-';
                            let nameB = b && b.leaderqc_name ? b.leaderqc_name.toLowerCase() : '-';
                            if (nameA < nameB) return -1;
                            if (nameA > nameB) return 1;
                            return 0;
                        });

                        let sortedMechanicOutput = mechanicOutput.sort(function(a, b) {
                            let nameA = a && a.mechanic_name ? a.mechanic_name.toLowerCase() : '-';
                            let nameB = b && b.mechanic_name ? b.mechanic_name.toLowerCase() : '-';
                            if (nameA < nameB) return -1;
                            if (nameA > nameB) return 1;
                            return 0;
                        });

                        let sortedTechnicalOutput = technicalOutput.sort(function(a, b) {
                            let nameA = a && a.technical_name ? a.technical_name.toLowerCase() : '-';
                            let nameB = b && b.technical_name ? b.technical_name.toLowerCase() : '-';
                            if (nameA < nameB) return -1;
                            if (nameA > nameB) return 1;
                            return 0;
                        });

                        chiefDailyEfficiency.push({"id": element[element.length-1].chief_id ? element[element.length-1].chief_id : 'KOSONG', "nik": element[element.length-1].chief_nik ? element[element.length-1].chief_nik : 'KOSONG', "name": element[element.length-1].chief_name ? element[element.length-1].chief_name : 'KOSONG', "data": sortedDateOutput, "leaderData": sortedLeaderOutput, "ieData": sortedIeOutput, "leaderqcData": sortedLeaderqcOutput, "mechanicData": sortedMechanicOutput, "technicalData": sortedTechnicalOutput, "currentEff": (currentData ? currentData.mins_prod/currentData.mins_avail*100 : 0), "currentRft": (currentData ? currentData.rft/currentData.output*100 : 0)});
                    });

                    // Sort Chief Daily by Efficiency
                    let sortedChiefDailyEfficiency = chiefDailyEfficiency.sort(function(a,b){
                        if ((a.currentEff+a.currentRft) < (b.currentEff+b.currentRft)) {
                            return 1;
                        }
                        if ((a.currentEff+a.currentRft) > (b.currentEff+b.currentRft)) {
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
                chiefContainer.classList.add("col-3");
                chiefContainer.classList.add("p-1");
                chiefContainer.classList.add("border");
                let imageContainer = document.createElement("div");
                imageContainer.classList.add("profile-frame");
                imageContainer.style.width = "75px";
                imageContainer.style.height = "75px";
                let imageElement = document.createElement("img");
                imageElement.src = "{{ asset('../storage/employee_profile') }}/"+data.nik+"%20"+data.name+".png";
                imageElement.setAttribute("onerror", "this.onerror=null; this.src='{{ asset('dist/img/person.png') }}'");
                imageElement.setAttribute("alt", "person")
                imageElement.classList.add("img-fluid")
                // imageElement.style.width = "200px";
                // imageElement.style.height = "150px";
                imageElement.style.marginTop = "auto";
                imageElement.style.marginLeft = "auto";
                imageElement.style.marginRight = "auto";
                imageContainer.appendChild(imageElement);
                chiefContainer.appendChild(imageContainer);
                chiefContainer.innerHTML += "<span class='text-sb fw-bold' style='font-size: 8.5px;'><center>"+data.name.split(" ")[0]+"</center></span>"

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
                let todayDate = null;
                let yesterdayDate = null;
                let beforeDate = null;

                if (index > 1) {
                    todayDate = currentDayThree;
                    yesterdayDate = currentDayTwo;
                    beforeDate = currentDayOne;

                    var todayFilter = dailyData.filter((item) => (todayDate ? item.tanggal == formatDate(todayDate) : item.tanggal <= formatDate(new Date())) );
                    var today = todayFilter[todayFilter.length-1];

                    var yesterdayFilter = dailyData.filter((item) => (yesterdayDate ? item.tanggal == formatDate(yesterdayDate) : (item.tanggal < formatDate(today ? today.tanggal : new Date(new Date().setDate(new Date().getDate() - 1))))) );
                    var yesterday = yesterdayFilter[yesterdayFilter.length-1]

                    var beforeFilter = dailyData.filter((item) => (beforeDate ? item.tanggal == formatDate(beforeDate) : (item.tanggal < formatDate(yesterday ? yesterday.tanggal : new Date(new Date().setDate(new Date().getDate() - 2))))) );
                    var before = beforeFilter[beforeFilter.length-1];
                } else {
                    var todayFilter = dailyData.filter((item) => item.tanggal <= formatDate(new Date()));
                    var today = todayFilter[todayFilter.length-1];

                    var yesterdayFilter = todayFilter.filter((item) => (item.tanggal < formatDate(today ? today.tanggal : new Date(new Date().setDate(new Date().getDate() - 1)))));
                    var yesterday = yesterdayFilter[yesterdayFilter.length-1]

                    var beforeFilter = yesterdayFilter.filter((item) => (item.tanggal < formatDate(yesterday ? yesterday.tanggal : new Date(new Date().setDate(new Date().getDate() - 2)))));
                    var before = beforeFilter[beforeFilter.length-1];
                }

                let subEmployeeContainer = document.createElement("div");
                subEmployeeContainer.classList.add("col-9");
                subEmployeeContainer.classList.add("d-flex");
                subEmployeeContainer.classList.add("flex-wrap");

                // Leader
                let leaderContainer = document.createElement("div");
                leaderContainer.classList.add("w-100");
                let leadersElement = document.createElement("div");
                leadersElement.classList.add("row");
                leadersElement.classList.add("h-100");
                data.leaderData.forEach(element => {
                    if (element.tanggal >= (today ? today.tanggal : todayDate)) {
                        let leaderName = element.leader_name ? element.leader_name.split(" ")[0] : 'KOSONG';
                        let leaderElement = document.createElement("div");
                        leaderElement.classList.add("col-2");
                        leaderElement.classList.add("p-1");
                        leaderElement.classList.add("border");
                        leaderElement.classList.add("d-flex");
                        leaderElement.classList.add("flex-column");
                        let leaderImageContainer = document.createElement("div");
                        leaderImageContainer.classList.add("m-auto");
                        let leaderImageSubContainer = document.createElement("div");
                        leaderImageSubContainer.classList.add("profile-frame");
                        leaderImageSubContainer.style.width = "33px";
                        leaderImageSubContainer.style.height = "33px";
                        let leaderImageElement = document.createElement("img");
                        leaderImageElement.src = "{{ asset('../storage/employee_profile') }}/"+element.leader_nik+"%20"+element.leader_name+".png"
                        leaderImageElement.setAttribute("onerror", "this.onerror=null; this.src='{{ asset('dist/img/person.png') }}'");
                        leaderImageElement.setAttribute("alt", "person")
                        leaderImageElement.classList.add("img-fluid")
                        // leaderImageElement.style.width = "50px";
                        // leaderImageElement.style.height = "50px";
                        leaderImageSubContainer.appendChild(leaderImageElement)
                        leaderImageContainer.appendChild(leaderImageSubContainer)
                        leaderElement.appendChild(leaderImageContainer);
                        leaderImageContainer.innerHTML += "<span class='text-sb fw-bold' style='font-size: 7px;'><center>"+(leaderName.length > 10 ? leaderName.slice(0, 9) : leaderName)+"</center></span>";
                        leaderImageContainer.innerHTML += "<span class='text-dark fw-bold' style='font-size: 6.5px;'><center>LEADER</center></span>";
                        leaderImageContainer.innerHTML += "<span class='text-sb-secondary fw-bold' style='font-size: 7px;'><center>"+element.sewing_line.replace(/_/g, " ").toUpperCase()+"</center></span>";
                        leadersElement.appendChild(leaderElement);
                    }
                });
                leaderContainer.appendChild(leadersElement);

                let supportEmployeeContainer = document.createElement("div");
                supportEmployeeContainer.classList.add("w-100");
                let supportEmployeeElements = document.createElement("div");
                supportEmployeeElements.classList.add("row");
                supportEmployeeElements.classList.add("h-100");

                // IE
                let ieContainer = document.createElement("div");
                ieContainer.classList.add("col-2");
                ieContainer.classList.add("p-0");
                let iesElement = document.createElement("div");
                iesElement.classList.add("d-flex");
                iesElement.classList.add("flex-column");
                iesElement.classList.add("border");
                // iesElement.classList.add("gap-1");
                iesElement.classList.add("h-100");
                iesElement.innerHTML = '<p style="font-size: 3px;" class="text-light fw-bold bg-info mb-0">&nbsp;</p>';
                data.ieData.forEach(element => {
                    if (element.tanggal >= (today ? today.tanggal : todayDate)) {
                        if (element.ie_name) {
                            let ieName = element.ie_name ? element.ie_name.split(" ")[0] : 'KOSONG';
                            let ieElement = document.createElement("div");
                            ieElement.classList.add("w-100");
                            ieElement.classList.add("p-1");
                            // ieElement.classList.add("border");
                            ieElement.classList.add("d-flex");
                            ieElement.classList.add("flex-column");
                            let ieImageContainer = document.createElement("div");
                            ieImageContainer.classList.add("m-auto");
                            let ieImageSubContainer = document.createElement("div");
                            ieImageSubContainer.classList.add("profile-frame");
                            ieImageSubContainer.style.width = "33px";
                            ieImageSubContainer.style.height = "33px";
                            let ieImageElement = document.createElement("img");
                            ieImageElement.src = "{{ asset('../storage/employee_profile') }}/"+element.ie_nik+"%20"+element.ie_name+".png"
                            ieImageElement.setAttribute("onerror", "this.onerror=null; this.src='{{ asset('dist/img/person.png') }}'");
                            ieImageElement.setAttribute("alt", "person")
                            ieImageElement.classList.add("img-fluid")
                            // ieImageElement.style.width = "50px";
                            // ieImageElement.style.height = "50px";
                            ieImageSubContainer.appendChild(ieImageElement)
                            ieImageContainer.appendChild(ieImageSubContainer)
                            ieElement.appendChild(ieImageContainer);
                            ieImageContainer.innerHTML += "<span class='text-sb fw-bold' style='font-size: 7px;'><center>"+(ieName.length > 10 ? ieName.slice(0, 9) : ieName)+"</center></span>";
                            ieImageContainer.innerHTML += "<span class='text-info fw-bold' style='font-size: 6.5px;'><center>IE</center></span>";
                            // ieImageContainer.innerHTML += "<span class='text-sb-secondary fw-bold' style='font-size: 7px;'><center>"+element.sewing_line.replace(/_/g, " ").toUpperCase()+"</center></span>";
                            iesElement.appendChild(ieElement);
                        }
                    }
                });
                ieContainer.appendChild(iesElement);

                // leaderqc
                let leaderqcContainer = document.createElement("div");
                leaderqcContainer.classList.add("col-2");
                leaderqcContainer.classList.add("p-0");
                let leaderqcsElement = document.createElement("div");
                leaderqcsElement.classList.add("border");
                leaderqcsElement.classList.add("d-flex");
                leaderqcsElement.classList.add("flex-column");
                // leaderqcsElement.classList.add("gap-1");
                leaderqcsElement.classList.add("h-100");
                leaderqcsElement.innerHTML = '<p style="font-size: 3px;" class="text-light fw-bold bg-danger mb-0">&nbsp;</p>';
                data.leaderqcData.forEach(element => {
                    if (element.tanggal >= (today ? today.tanggal : todayDate)) {
                        if (element.leaderqc_name) {
                            let leaderqcName = element.leaderqc_name ? element.leaderqc_name.split(" ")[0] : 'KOSONG';
                            let leaderqcElement = document.createElement("div");
                            leaderqcElement.classList.add("w-100");
                            leaderqcElement.classList.add("p-1");
                            // leaderqcElement.classList.add("border");
                            leaderqcElement.classList.add("d-flex");
                            leaderqcElement.classList.add("flex-column");
                            let leaderqcImageContainer = document.createElement("div");
                            leaderqcImageContainer.classList.add("m-auto");
                            let leaderqcImageSubContainer = document.createElement("div");
                            leaderqcImageSubContainer.classList.add("profile-frame");
                            leaderqcImageSubContainer.style.width = "33px";
                            leaderqcImageSubContainer.style.height = "33px";
                            let leaderqcImageElement = document.createElement("img");
                            leaderqcImageElement.src = "{{ asset('../storage/employee_profile') }}/"+element.leaderqc_nik+"%20"+element.leaderqc_name+".png"
                            leaderqcImageElement.setAttribute("onerror", "this.onerror=null; this.src='{{ asset('dist/img/person.png') }}'");
                            leaderqcImageElement.setAttribute("alt", "person")
                            leaderqcImageElement.classList.add("img-fluid")
                            // leaderqcImageElement.style.width = "50px";
                            // leaderqcImageElement.style.height = "50px";
                            leaderqcImageSubContainer.appendChild(leaderqcImageElement)
                            leaderqcImageContainer.appendChild(leaderqcImageSubContainer)
                            leaderqcElement.appendChild(leaderqcImageContainer);
                            leaderqcImageContainer.innerHTML += "<span class='text-sb fw-bold' style='font-size: 7px;'><center>"+(leaderqcName.length > 10 ? leaderqcName.slice(0, 9) : leaderqcName)+"</center></span>";
                            leaderqcImageContainer.innerHTML += "<span class='text-danger fw-bold' style='font-size: 6px;'><center>LEADER QC</center></span>";
                            // leaderqcImageContainer.innerHTML += "<span class='text-sb-secondary fw-bold' style='font-size: 7px;'><center>"+element.sewing_line.replace(/_/g, " ").toUpperCase()+"</center></span>";
                            leaderqcsElement.appendChild(leaderqcElement);
                        }
                    }
                });
                leaderqcContainer.appendChild(leaderqcsElement);

                // mechanic
                let mechanicContainer = document.createElement("div");
                mechanicContainer.classList.add("col-2");
                mechanicContainer.classList.add("p-0");
                let mechanicsElement = document.createElement("div");
                mechanicsElement.classList.add("d-flex");
                mechanicsElement.classList.add("flex-column");
                mechanicsElement.classList.add("border");
                // mechanicsElement.classList.add("gap-1");
                mechanicsElement.classList.add("h-100");
                mechanicsElement.innerHTML = '<p style="font-size: 3px;" class="text-light fw-bold bg-success mb-0">&nbsp;</p>';
                data.mechanicData.forEach(element => {
                    if (element.tanggal >= (today ? today.tanggal : todayDate)) {
                        if (element.mechanic_name) {
                            let mechanicName = element.mechanic_name ? element.mechanic_name.split(" ")[0] : 'KOSONG';
                            let mechanicElement = document.createElement("div");
                            mechanicElement.classList.add("w-100");
                            mechanicElement.classList.add("p-1");
                            // mechanicElement.classList.add("border");
                            mechanicElement.classList.add("d-flex");
                            mechanicElement.classList.add("flex-column");
                            let mechanicImageContainer = document.createElement("div");
                            mechanicImageContainer.classList.add("m-auto");
                            let mechanicImageSubContainer = document.createElement("div");
                            mechanicImageSubContainer.classList.add("profile-frame");
                            mechanicImageSubContainer.style.width = "33px";
                            mechanicImageSubContainer.style.height = "33px";
                            let mechanicImageElement = document.createElement("img");
                            mechanicImageElement.src = "{{ asset('../storage/employee_profile') }}/"+element.mechanic_nik+"%20"+element.mechanic_name+".png"
                            mechanicImageElement.setAttribute("onerror", "this.onerror=null; this.src='{{ asset('dist/img/person.png') }}'");
                            mechanicImageElement.setAttribute("alt", "person")
                            mechanicImageElement.classList.add("img-fluid")
                            // mechanicImageElement.style.width = "50px";
                            // mechanicImageElement.style.height = "50px";
                            mechanicImageSubContainer.appendChild(mechanicImageElement)
                            mechanicImageContainer.appendChild(mechanicImageSubContainer)
                            mechanicElement.appendChild(mechanicImageContainer);
                            mechanicImageContainer.innerHTML += "<span class='text-sb fw-bold' style='font-size: 7px;'><center>"+(mechanicName.length > 10 ? mechanicName.slice(0, 9) : mechanicName)+"</center></span>";
                            mechanicImageContainer.innerHTML += "<span class='text-success fw-bold' style='font-size: 6.5px;'><center>MECHANIC</center></span>";
                            // mechanicImageContainer.innerHTML += "<span class='text-sb-secondary fw-bold' style='font-size: 7px;'><center>"+element.sewing_line.replace(/_/g, " ").toUpperCase()+"</center></span>";
                            mechanicsElement.appendChild(mechanicElement);
                        }
                    }
                });
                mechanicContainer.appendChild(mechanicsElement);

                // technical
                let technicalContainer = document.createElement("div");
                technicalContainer.classList.add("col-2");
                technicalContainer.classList.add("p-0");
                let technicalsElement = document.createElement("div");
                technicalsElement.classList.add("d-flex");
                technicalsElement.classList.add("flex-column");
                technicalsElement.classList.add("border");
                // technicalsElement.classList.add("gap-1");
                technicalsElement.classList.add("h-100");
                technicalsElement.innerHTML = '<p style="font-size: 3px;" class="text-light fw-bold bg-primary mb-0">&nbsp;</p>';
                data.technicalData.forEach(element => {
                    if (element.tanggal >= (today ? today.tanggal : todayDate)) {
                        if (element.technical_name) {
                            let technicalName = element.technical_name ? element.technical_name.split(" ")[0] : 'KOSONG';
                            let technicalElement = document.createElement("div");
                            technicalElement.classList.add("w-100");
                            technicalElement.classList.add("p-1");
                            // technicalElement.classList.add("border");
                            technicalElement.classList.add("d-flex");
                            technicalElement.classList.add("flex-column");
                            let technicalImageContainer = document.createElement("div");
                            technicalImageContainer.classList.add("m-auto");
                            let technicalImageSubContainer = document.createElement("div");
                            technicalImageSubContainer.classList.add("profile-frame");
                            technicalImageSubContainer.style.width = "33px";
                            technicalImageSubContainer.style.height = "33px";
                            let technicalImageElement = document.createElement("img");
                            technicalImageElement.src = "{{ asset('../storage/employee_profile') }}/"+element.technical_nik+"%20"+element.technical_name+".png"
                            technicalImageElement.setAttribute("onerror", "this.onerror=null; this.src='{{ asset('dist/img/person.png') }}'");
                            technicalImageElement.setAttribute("alt", "person")
                            technicalImageElement.classList.add("img-fluid")
                            // technicalImageElement.style.width = "50px";
                            // technicalImageElement.style.height = "50px";
                            technicalImageSubContainer.appendChild(technicalImageElement)
                            technicalImageContainer.appendChild(technicalImageSubContainer)
                            technicalElement.appendChild(technicalImageContainer);
                            technicalImageContainer.innerHTML += "<span class='text-sb fw-bold' style='font-size: 7px;'><center>"+(technicalName.length > 10 ? technicalName.slice(0, 9) : technicalName)+"</center></span>";
                            technicalImageContainer.innerHTML += "<span class='text-primary fw-bold' style='font-size: 6.5px;'><center>TECHNICAL</center></span>";
                            // technicalImageContainer.innerHTML += "<span class='text-sb-secondary fw-bold' style='font-size: 7px;'><center>"+element.sewing_line.replace(/_/g, " ").toUpperCase()+"</center></span>";
                            technicalsElement.appendChild(technicalElement);
                        }
                    }
                });
                technicalContainer.appendChild(technicalsElement);

                subEmployeeContainer.appendChild(leaderContainer)
                supportEmployeeElements.appendChild(ieContainer)
                supportEmployeeElements.appendChild(leaderqcContainer)
                supportEmployeeElements.appendChild(mechanicContainer)
                supportEmployeeElements.appendChild(technicalContainer)
                supportEmployeeContainer.appendChild(supportEmployeeElements)
                subEmployeeContainer.appendChild(supportEmployeeContainer)

                nameElement.appendChild(chiefContainer)
                nameElement.appendChild(subEmployeeContainer)

                // Chart
                let chartElement = document.getElementById("chart-"+index);

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

                // Before
                let beforeEffElement = document.getElementById("before-eff-"+index);
                if (beforeEffElement) {
                    beforeEffElement.innerHTML = (before ? (before.mins_prod / before.mins_avail * 100).round(2) : 0)+"%";
                    colorizeEfficiency(beforeEffElement, (before ? (before.mins_prod / before.mins_avail * 100).round(2) : 0))
                    let beforeRftElement = document.getElementById("before-rft-"+index);
                    beforeRftElement.innerHTML = (before ? (before.rft / before.output * 100).round(2) : 0)+"%";
                    colorizeRft(beforeRftElement, (before ? (before.rft / before.output * 100).round(2) : 0))
                }

                // Yesterday
                let yesterdayEffElement = document.getElementById("yesterday-eff-"+index);
                if (yesterdayEffElement) {
                    yesterdayEffElement.innerHTML = (yesterday ? (yesterday.mins_prod / yesterday.mins_avail * 100).round(2) : 0)+"%";
                    colorizeEfficiency(yesterdayEffElement, (yesterday ? (yesterday.mins_prod / yesterday.mins_avail * 100).round(2) : 0))
                    let yesterdayRftElement = document.getElementById("yesterday-rft-"+index);
                    yesterdayRftElement.innerHTML = (yesterday ? (yesterday.rft / yesterday.output * 100).round(2) : 0)+"%";
                    colorizeRft(yesterdayRftElement, (yesterday ? (yesterday.rft / yesterday.output * 100).round(2) : 0))
                }

                // Today
                let todayEffElement = document.getElementById("today-eff-"+index);
                if (yesterdayEffElement) {
                    todayEffElement.innerHTML = (today ? (today.mins_prod / today.mins_avail * 100).round(2) : 0)+"%";
                    colorizeEfficiency(todayEffElement, (today ? (today.mins_prod / today.mins_avail * 100).round(2) : 0))
                    let todayRftElement = document.getElementById("today-rft-"+index);
                    todayRftElement.innerHTML = (today ? (today.rft / today.output * 100).round(2) : 0)+"%";
                    colorizeRft(todayRftElement, (today ? (today.rft / today.output * 100).round(2) : 0))
                }

                if (index == 1) {
                    currentDayOne = before.tanggal;
                    currentDayTwo = yesterday.tanggal;
                    currentDayThree = today.tanggal;

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
                }
            } else {
                appendRow(data, index);
            }
        }

        function groupByRole(element, currentDate, role) {
            let output = [];
            element.reduce((res, value) => {
                if (value.tanggal === currentDate) {
                    let id = value[`${role}_nik`] ?? value.sewing_line;
                    if (!res[id]) {
                        res[id] = {
                            id: value[`${role}_nik`],
                            [`${role}_nik`]: value[`${role}_nik`],
                            [`${role}_name`]: value[`${role}_name`],
                            sewing_line: "",
                            mins_avail: 0,
                            mins_prod: 0,
                            output: 0,
                            rft: 0
                        };
                        output.push(res[id]);
                    }
                    res[id].tanggal = value.tanggal;
                    res[id].mins_avail += Number(value.cumulative_mins_avail);
                    res[id].mins_prod += Number(value.mins_prod);
                    res[id].output += Number(value.output);
                    res[id].rft += Number(value.rft);
                    res[id].sewing_line += value.sewing_line + "<br>";
                }
                return res;
            }, {});
            return output;
        }
    </script>
@endsection
