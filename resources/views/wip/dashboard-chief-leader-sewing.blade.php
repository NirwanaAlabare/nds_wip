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
            display: flex;
            background: inherit;
            width: 100%;
            align-items: stretch;
            grid-gap: 3px;
        }

        .horizontal-grid-box {
            background: #ffffff;
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
    <div class="p-3">
        <div class="d-flex justify-content-between align-items-end mb-1 gap-3">
            <div class="d-flex align-items-end gap-3 w-auto">
                <div>
                    <input type="date" class="form-control" id="from" value="{{ $from ? $from : date("Y-m-d", strtotime(date("Y-m-d")." -14 days")) }}">
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
            <div class="d-flex align-items-end gap-3">
                <div>
                    <label class="form-label">Buyer</label>
                    <select name="buyer_id" id="buyer_id" class="form-select select2bs4" onchange="updateTanggal()">
                        <option value="">SEMUA</option>
                        @foreach ($buyers as $buyer)
                            <option value="{{ $buyer->id }}">{{ $buyer->name }}</option>
                        @endforeach
                    </select>
                </div>
                {{-- <button class="btn btn-success" onclick="exportExcel(this)"><i class="fa fa-file-excel"></i></button> --}}
            </div>
        </div>
        <div class="d-flex justify-content-center mb-1">
            <b><span id="from-label">{{ localeDateFormat($from, false) }}</span> <span>s/d</span> <span id="to-label">{{ localeDateFormat($to, false) }}</span></b>
        </div>
        <div class="row g-3" id="chief-leader-line-charts">
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
        function formatDateTick(dateString) {
            const date = new Date(dateString);
            const day = date.getDate();
            const month = date.toLocaleString('default', { month: 'short' }); // e.g., "Apr"
            return `${day} ${month}`;
        }

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

        $('document').ready(async () => {
            getData();
        });

        // Interval Update
        var intervalData = setInterval(async function () {
            console.log("data update start");

            await updateData();

            console.log("data update finish");
        }, 60000);

        // Date Update
        async function updateTanggal() {
            document.getElementById("loading").classList.remove("d-none");

            await getData();

            document.getElementById("loading").classList.add("d-none");
        }

        // Get Data
        async function getData() {
            document.getElementById("loading").classList.remove("d-none");

            document.getElementById("from-label").innerHTML = $("#from").val()+" ";
            document.getElementById("to-label").innerHTML = " "+$("#to").val();

            await $.ajax({
                url: "{{ route("dashboard-leader-sewing-data") }}",
                type: "get",
                data: {
                    from: $("#from").val(),
                    to: $("#to").val(),
                    buyer_id: $("#buyer_id").val(),
                },
                dataType: "json",
                success: async function (response) {
                    console.log("data", response);

                    document.getElementById('chief-leader-line-charts').innerHTML = "";

                    // leader group by
                    let leaderEfficiency = objectValues(objectGroupBy(response, ({ leader_nik }) => leader_nik));
                    leaderEfficiency = leaderEfficiency.map(element => {
                        let total_mins_avail = 0;
                        let total_mins_prod = 0;
                        let total_output = 0;
                        let total_rft = 0;

                        element.reduce(function(res, value) {
                            total_mins_avail += Number(value.cumulative_mins_avail);
                            total_mins_prod += Number(value.mins_prod);
                            total_output += Number(value.output);
                            total_rft += Number(value.rft);

                            return res;
                        }, {});

                        let totalEfficiency = (total_mins_prod/total_mins_avail*100);
                        let totalRft = (total_rft/total_output*100);

                        return {
                            "id": element[0].leader_nik,
                            "totalValue": totalEfficiency+totalRft,
                        }
                    });

                    // Sort leader output efficiency
                    let sortedLeaderEfficiency = leaderEfficiency.sort(function(a,b){
                        if (a.totalValue < b.totalValue) {
                            return 1;
                        }
                        if (a.totalValue > b.totalValue) {
                            return -1;
                        }
                        return 0;
                    });

                    // Line
                    let lineEfficiency = objectValues(objectGroupBy(response, ({ line_id }) => line_id));

                    let lineDailyEfficiency = [];
                    lineEfficiency.forEach(element => {
                        // Date Output
                        let dateOutput = [];
                        let lineLeaderList = [];
                        let currentLeader = {
                            "tanggal": element[0].tanggal,
                            "leader_nik": element[0].leader_nik,
                            "leader_name": (element[0].leader_name ? element[0].leader_name.split(" ")[0] : "KOSONG"),
                        };
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

                            if (value.leader_nik != currentLeader.leader_nik) {
                                lineLeaderList.push(
                                        {
                                        x: formatDateTick(currentLeader.tanggal),
                                        borderColor: '#00E396',
                                        label: {
                                            style: {
                                                fontSize: "7px",
                                            },
                                            borderColor: '#00E396',
                                            text: currentLeader.leader_name
                                        },
                                    }
                                );
                            }

                            currentLeader = {
                                "tanggal": value.tanggal,
                                "leader_nik": value.leader_nik,
                                "leader_name": (value.leader_name ? value.leader_name.split(" ")[0] : 'KOSONG'),
                            };

                            return res;
                        }, {});

                        // get leader
                        let leaderRank = sortedLeaderEfficiency.map(e => e.id).indexOf(element[element.length-1].leader_nik ? element[element.length-1].leader_nik : null);

                        lineDailyEfficiency.push({"id": element[element.length-1].leader_id ? element[element.length-1].leader_id : 'KOSONG', "nik": element[element.length-1].leader_nik ? element[element.length-1].leader_nik : 'KOSONG', "name": element[element.length-1].leader_name ? element[element.length-1].leader_name : 'KOSONG', "leader_rank": leaderRank+1, "line": element[element.length-1].line_name, "data": dateOutput, "leaders": lineLeaderList, "chief_id": element[element.length-1].chief_id, "chief_nik": element[element.length-1].chief_nik});
                    });

                    // Sort line output efficiency
                    let sortedLineEfficiency = lineDailyEfficiency.sort(function(a,b){
                        if (a.leader_rank > b.leader_rank) {
                            return 1;
                        }
                        if (a.leader_rank < b.leader_rank) {
                            return -1;
                        }
                        return 0;
                    });

                    // Chief Group By
                    let chiefEfficiency = objectValues(objectGroupBy(response, ({ chief_nik }) => chief_nik));

                    let chiefLineEfficiency = [];
                    chiefEfficiency.forEach(element => {
                        // Sort date output efficiency
                        let sortedDateOutput = element.sort(function(a,b){
                            if (a.tanggal > b.tanggal) {
                                return 1;
                            }
                            if (a.tanggal < b.tanggal) {
                                return -1;
                            }
                            return 0;
                        });

                        // get current date
                        let dateOutputFilter = sortedDateOutput.filter((item) => item.mins_avail > 0 && item.mins_prod > 0);
                        let currentFilter = dateOutputFilter.filter((item) => item.tanggal == formatDate(new Date()));
                        let currentData = currentFilter.length > 0 ? currentFilter[0] : dateOutputFilter[dateOutputFilter.length-1];

                        // get line data
                        let lineEfficiency = sortedLineEfficiency.filter((item) => item.chief_nik == currentData.chief_nik);

                        // total
                        let total_mins_avail = 0;
                        let total_mins_prod = 0;
                        let total_output = 0;
                        let total_rft = 0;
                        element.reduce(function(res, value) {
                            total_mins_avail += Number(value.cumulative_mins_avail);
                            total_mins_prod += Number(value.mins_prod);
                            total_output += Number(value.output);
                            total_rft += Number(value.rft);

                            return res;
                        }, {});

                        let totalEfficiency = total_mins_prod/total_mins_avail * 100;
                        let totalRft = total_rft/total_output * 100;

                        chiefLineEfficiency.push({"id": element[0].chief_id ? element[0].chief_id : 'KOSONG', "nik": element[0].chief_nik ? element[0].chief_nik : 'KOSONG', "name": element[0].chief_name ? element[0].chief_name : 'KOSONG', "data": lineEfficiency, "totalValue": totalEfficiency+totalRft});
                    });

                    let sortedChiefLineEfficiency = chiefLineEfficiency.sort(function(a,b){
                        if (a.totalValue < b.totalValue) {
                            return 1;
                        }
                        if (a.totalValue > b.totalValue) {
                            return -1;
                        }
                        return 0;
                    });

                    // Show Chief Daily Data
                    for (let i = 0; i < sortedChiefLineEfficiency.length; i++) {
                        appendRow(sortedChiefLineEfficiency[i], i+1);
                    }

                    document.getElementById("loading").classList.add("d-none");
                },
                error: function (jqXHR) {
                    console.error(jqXHR);
                }
            });

            document.getElementById("loading").classList.add("d-none");
        }

        // Update Data
        async function updateData() {
            document.getElementById("from-label").innerHTML = $("#from").val()+" ";
            document.getElementById("to-label").innerHTML = " "+$("#to").val();

            await $.ajax({
                url: "{{ route("dashboard-leader-sewing-data") }}",
                type: "get",
                data: {
                    from: $("#from").val(),
                    to: $("#to").val(),
                    buyer_id: $("#buyer_id").val(),
                },
                dataType: "json",
                success: async function (response) {
                    console.log("data", response);

                    document.getElementById('chief-leader-line-charts').innerHTML = "";

                    // leader group by
                    let leaderEfficiency = objectValues(objectGroupBy(response, ({ leader_nik }) => leader_nik));
                    leaderEfficiency = leaderEfficiency.map(element => {
                        let total_mins_avail = 0;
                        let total_mins_prod = 0;
                        let total_output = 0;
                        let total_rft = 0;

                        element.reduce(function(res, value) {
                            total_mins_avail += Number(value.cumulative_mins_avail);
                            total_mins_prod += Number(value.mins_prod);
                            total_output += Number(value.output);
                            total_rft += Number(value.rft);

                            return res;
                        }, {});

                        let totalEfficiency = (total_mins_prod/total_mins_avail*100);
                        let totalRft = (total_rft/total_output*100);

                        return {
                            "id": element[0].leader_nik,
                            "totalValue": totalEfficiency+totalRft,
                        }
                    });

                    // Sort leader output efficiency
                    let sortedLeaderEfficiency = leaderEfficiency.sort(function(a,b){
                        if (a.totalValue < b.totalValue) {
                            return 1;
                        }
                        if (a.totalValue > b.totalValue) {
                            return -1;
                        }
                        return 0;
                    });

                    // Line
                    let lineEfficiency = objectValues(objectGroupBy(response, ({ line_id }) => line_id));

                    let lineDailyEfficiency = [];
                    lineEfficiency.forEach(element => {
                        // Date Output
                        let dateOutput = [];
                        let lineLeaderList = [];
                        let currentLeader = {
                            "tanggal": element[0].tanggal,
                            "leader_nik": element[0].leader_nik,
                            "leader_name": (element[0].leader_name ? element[0].leader_name.split(" ")[0] : "KOSONG"),
                        };
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

                            if (value.leader_nik != currentLeader.leader_nik) {
                                lineLeaderList.push(
                                        {
                                        x: formatDateTick(currentLeader.tanggal),
                                        borderColor: '#00E396',
                                        label: {
                                            style: {
                                                fontSize: "7px",
                                            },
                                            borderColor: '#00E396',
                                            text: currentLeader.leader_name
                                        },
                                    }
                                );
                            }

                            currentLeader = {
                                "tanggal": value.tanggal,
                                "leader_nik": value.leader_nik,
                                "leader_name": (value.leader_name ? value.leader_name.split(" ")[0] : 'KOSONG'),
                            };

                            return res;
                        }, {});

                        // get leader
                        console.log(sortedLeaderEfficiency);
                        let leaderRank = sortedLeaderEfficiency.map(e => e.id).indexOf(element[element.length-1].leader_nik ? element[element.length-1].leader_nik : null);

                        lineDailyEfficiency.push({"id": element[element.length-1].leader_id ? element[element.length-1].leader_id : 'KOSONG', "nik": element[element.length-1].leader_nik ? element[element.length-1].leader_nik : 'KOSONG', "name": element[element.length-1].leader_name ? element[element.length-1].leader_name : 'KOSONG', "leader_rank": leaderRank+1, "line": element[element.length-1].line_name, "data": dateOutput, "leaders": lineLeaderList, "chief_id": element[element.length-1].chief_id, "chief_nik": element[element.length-1].chief_nik});
                    });

                    // Sort line output efficiency
                    let sortedLineEfficiency = lineDailyEfficiency.sort(function(a,b){
                        if (a.leader_rank > b.leader_rank) {
                            return 1;
                        }
                        if (a.leader_rank < b.leader_rank) {
                            return -1;
                        }
                        return 0;
                    });

                    // Chief Group By
                    let chiefEfficiency = objectValues(objectGroupBy(response, ({ chief_nik }) => chief_nik));

                    let chiefLineEfficiency = [];
                    chiefEfficiency.forEach(element => {
                        // Sort date output efficiency
                        let sortedDateOutput = element.sort(function(a,b){
                            if (a.tanggal > b.tanggal) {
                                return 1;
                            }
                            if (a.tanggal < b.tanggal) {
                                return -1;
                            }
                            return 0;
                        });

                        // get current date
                        let dateOutputFilter = sortedDateOutput.filter((item) => item.mins_avail > 0 && item.mins_prod > 0);
                        let currentFilter = dateOutputFilter.filter((item) => item.tanggal == formatDate(new Date()));
                        let currentData = currentFilter.length > 0 ? currentFilter[0] : dateOutputFilter[dateOutputFilter.length-1];

                        // get line data
                        let lineEfficiency = sortedLineEfficiency.filter((item) => item.chief_nik == currentData.chief_nik);

                        // total
                        let total_mins_avail = 0;
                        let total_mins_prod = 0;
                        let total_output = 0;
                        let total_rft = 0;
                        element.reduce(function(res, value) {
                            total_mins_avail += Number(value.cumulative_mins_avail);
                            total_mins_prod += Number(value.mins_prod);
                            total_output += Number(value.output);
                            total_rft += Number(value.rft);

                            return res;
                        }, {});

                        let totalEfficiency = total_mins_prod/total_mins_avail * 100;
                        let totalRft = total_rft/total_output * 100;

                        chiefLineEfficiency.push({"id": element[0].chief_id ? element[0].chief_id : 'KOSONG', "nik": element[0].chief_nik ? element[0].chief_nik : 'KOSONG', "name": element[0].chief_name ? element[0].chief_name : 'KOSONG', "data": lineEfficiency, "totalValue": totalEfficiency+totalRft});
                    });

                    let sortedChiefLineEfficiency = chiefLineEfficiency.sort(function(a,b){
                        if (a.totalValue < b.totalValue) {
                            return 1;
                        }
                        if (a.totalValue > b.totalValue) {
                            return -1;
                        }
                        return 0;
                    });

                    // Show Chief Daily Data
                    for (let i = 0; i < sortedChiefLineEfficiency.length; i++) {
                        updateRow(sortedChiefLineEfficiency[i], i+1);
                    }

                    document.getElementById("loading").classList.add("d-none");
                },
                error: function (jqXHR) {
                    console.error(jqXHR);
                }
            });
        }

        // Generate Element
        function appendRow(data, index) {
            let parentElement = document.getElementById('chief-leader-line-charts');

            // Chief
            let chiefElement = document.createElement("div");
            chiefElement.id = "chief-"+index;
            chiefElement.classList.add("col-md-2");
            let chiefName = data.name ? data.name.split(" ")[0] : '-';
            let chiefContainer = document.createElement("div");
            chiefContainer.classList.add("w-100");
            chiefContainer.classList.add("h-100");
            chiefContainer.classList.add("p-3");
            chiefContainer.classList.add("border");
            chiefContainer.classList.add("border-sb");
            chiefContainer.classList.add("rounded");
            chiefContainer.classList.add("bg-white");
            let chiefImageContainer = document.createElement("div");
            chiefImageContainer.classList.add("profile-frame");
            chiefImageContainer.style.width = "100px";
            chiefImageContainer.style.height = "100px";
            let chiefImageElement = document.createElement("img");
            chiefImageElement.src = "{{ asset('../storage/employee_profile') }}/"+data.nik+"%20"+data.name+".png"
            chiefImageElement.setAttribute("onerror", "this.onerror=null; this.src='{{ asset('dist/img/person.png') }}'");
            chiefImageElement.setAttribute("alt", "person")
            chiefImageElement.classList.add("img-fluid")
            // chiefImageElement.style.width = "200px";
            // chiefImageElement.style.height = "150px";
            chiefImageElement.style.marginTop = "auto";
            chiefImageElement.style.marginLeft = "auto";
            chiefImageElement.style.marginRight = "auto";
            chiefImageContainer.appendChild(chiefImageElement);
            chiefContainer.appendChild(chiefImageContainer);
            chiefContainer.innerHTML += "<h5 class='text-sb fw-bold mt-3 text-center'>"+data.name.split(" ")[0]+"</h5>"
            chiefContainer.innerHTML += "<h5 class='text-dark mt-1 text-center' style='font-weight: 400 !important;'>RANK "+index+"</h5>"
            chiefElement.appendChild(chiefContainer);

            // Line
            let lineContainer = document.createElement("div");
            lineContainer.classList.add("col-md-10");
            let lineSubContainer = document.createElement("div");
            lineSubContainer.id = "line-con-"+index;
            lineSubContainer.classList.add("row");
            lineSubContainer.classList.add("g-3");
            lineContainer.appendChild(lineSubContainer);

            parentElement.appendChild(chiefElement);
            parentElement.appendChild(lineContainer);

            // line data
            let i = 0;
            data.data.forEach(d => {
                i++;

                appendSubRow(lineSubContainer, d, index, i);
            });
        }

        function appendSubRow(lineSubContainer, d, index, i) {
            // Container
            let div = document.createElement("div");
            div.setAttribute("data-i", i);
            div.classList.add("div-"+index);
            div.classList.add("col-md-4");
            let card = document.createElement("div");
            card.classList.add("card");
            card.classList.add("card-sb");
            card.classList.add("h-100");
            let cardHeader = document.createElement("div")
            cardHeader.classList.add("card-header");
            cardHeader.innerHTML = "<h5 class='card-title fw-bold' id='line-"+index+"-"+i+"'>"+d.line+"</h5>";
            let cardBody = document.createElement("div")
            cardBody.classList.add("card-body");
            let container = document.createElement("div");
            container.classList.add("row");
            container.classList.add("align-items-center");

            // Image
            let employeeContainer = document.createElement("div");
            employeeContainer.id = "employee-"+index+"-"+i;
            employeeContainer.classList.add("col-md-3");
            let imageContainer = document.createElement("div");
            imageContainer.classList.add("d-flex")
            imageContainer.classList.add("flex-column")
            imageContainer.classList.add("align-items-center")
            let imageSubContainer = document.createElement("div")
            imageSubContainer.classList.add("profile-frame");
            imageSubContainer.style.width = "40px";
            imageSubContainer.style.height = "40px";
            let imageElement = document.createElement("img");
            imageElement.src = "{{ asset('../storage/employee_profile') }}/"+d.nik+"%20"+d.name+".png"
            imageElement.setAttribute("onerror", "this.onerror=null; this.src='{{ asset('dist/img/person.png') }}'");
            imageElement.setAttribute("alt", "person")
            imageElement.classList.add("img-fluid")
            imageElement.style.marginLeft = "auto";
            imageElement.style.marginRight = "auto";
            // imageElement.style.height = "100px";
            imageSubContainer.appendChild(imageElement);
            imageContainer.appendChild(imageSubContainer);
            imageContainer.innerHTML += "<span class='text-sb fw-bold mt-1'><center>"+d.name.split(" ")[0]+"</center></span>"
            imageContainer.innerHTML += "<span class='text-dark mt-1'><center>RANK "+d.leader_rank+"</center></span>"
            employeeContainer.appendChild(imageContainer);

            // Chart
            let chartContainer = document.createElement("div");
            chartContainer.classList.add("col-md-9");
            let canvasContainer = document.createElement("div");
            let canvas = document.createElement("div");
            // canvas.id = "chart-"+index+"-"+i;
            canvas.classList.add("line-efficiency-chart");
            canvasContainer.appendChild(canvas);
            chartContainer.appendChild(canvasContainer);

            let tglArr = [];
            let efficiencyArr = [];
            let targetEfficiencyArr = [];
            let rftArr = [];

            let dailyData = d.data.filter((item) => item.mins_avail > 0 && item.output > 0);

            dailyData.forEach(item => {
                tglArr.push(formatDateTick(item.tanggal));
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
                    id: "chart-"+index+"-"+i,
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
                        fontSize: "7px",
                    }
                },
                annotations: {
                    xaxis: d.leaders,
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
                            fontSize: "7px",
                        }
                    }
                },
                xaxis: {
                    categories: tglArr,
                    labels: {
                        style: {
                            fontSize: "7px",
                        },
                        rotate: -90
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
                    fontSize: "7px",
                },
                redrawOnParentResize: true
            };

            var chart = new ApexCharts(canvas, options);

            container.appendChild(employeeContainer);
            container.appendChild(chartContainer);
            cardBody.appendChild(container);
            card.appendChild(cardHeader);
            card.appendChild(cardBody);
            div.appendChild(card);

            lineSubContainer.appendChild(div);

            chart.render();
        }

        // Update Element
        function updateRow(data, index) {
            if (document.getElementById("chief-"+index)) {
                // Chief
                let chiefElement = document.getElementById("chief-"+index);
                chiefElement.innerHTML = "";
                chiefElement.classList.add("col-md-2");
                let chiefName = data.name ? data.name.split(" ")[0] : '-';
                let chiefContainer = document.createElement("div");
                chiefContainer.classList.add("w-100");
                chiefContainer.classList.add("h-100");
                chiefContainer.classList.add("p-3");
                chiefContainer.classList.add("border");
                chiefContainer.classList.add("border-sb");
                chiefContainer.classList.add("rounded");
                chiefContainer.classList.add("bg-white");
                let chiefImageContainer = document.createElement("div");
                chiefImageContainer.classList.add("profile-frame");
                chiefImageContainer.style.width = "100px";
                chiefImageContainer.style.height = "100px";
                let chiefImageElement = document.createElement("img");
                chiefImageElement.src = "{{ asset('../storage/employee_profile') }}/"+data.nik+"%20"+data.name+".png"
                chiefImageElement.setAttribute("onerror", "this.onerror=null; this.src='{{ asset('dist/img/person.png') }}'");
                chiefImageElement.setAttribute("alt", "person")
                chiefImageElement.classList.add("img-fluid")
                // chiefImageElement.style.width = "200px";
                // chiefImageElement.style.height = "150px";
                chiefImageElement.style.marginTop = "auto";
                chiefImageElement.style.marginLeft = "auto";
                chiefImageElement.style.marginRight = "auto";
                chiefImageContainer.appendChild(chiefImageElement);
                chiefContainer.appendChild(chiefImageContainer);
                chiefContainer.innerHTML += "<h5 class='text-sb fw-bold mt-3 text-center'>"+data.name.split(" ")[0]+"</h5>"
                chiefContainer.innerHTML += "<h5 class='text-dark mt-1 text-center' style='font-weight: 400 !important;'>RANK "+index+"</h5>"
                chiefElement.appendChild(chiefContainer);

                // delete extra line
                let subElement = document.getElementsByClassName("div-"+index);
                for (let i = 0; i < subElement.length; i++) {
                    if (subElement[i].getAttribute("data-i") > (data.data.length+1)) {
                        subElement[i].remove();
                    }
                }

                // line data
                let i = 0;
                data.data.forEach(d => {
                    i++;

                    updateSubRow(d, index, i);
                })
            } else {
                appendRow(data, index);
            }
        }

        async function updateSubRow(data, index, i) {
            if (document.getElementById("line-"+index+"-"+i)) {
                // Name
                let lineElement = document.getElementById("line-"+index+"-"+i);
                let nameElement = document.getElementById("employee-"+index+"-"+i);

                lineElement.innerHTML = data.line;
                nameElement.innerHTML = "";

                // Image
                let imageContainer = document.createElement("div");
                imageContainer.classList.add("d-flex")
                imageContainer.classList.add("flex-column")
                imageContainer.classList.add("align-items-center")
                let imageSubContainer = document.createElement("div");
                imageSubContainer.classList.add("profile-frame");
                imageSubContainer.style.width = "83px";
                imageSubContainer.style.height = "83px";
                let imageElement = document.createElement("img");
                imageElement.src = "{{ asset('../storage/employee_profile') }}/"+data.nik+"%20"+data.name+".png"
                imageElement.setAttribute("onerror", "this.onerror=null; this.src='{{ asset('dist/img/person.png') }}'");
                imageElement.setAttribute("alt", "person")
                imageElement.classList.add("img-fluid")
                imageElement.style.marginLeft = "auto";
                imageElement.style.marginRight = "auto";
                // imageElement.style.height = "100px";
                imageSubContainer.appendChild(imageElement);
                imageContainer.appendChild(imageSubContainer);
                imageContainer.innerHTML += "<span class='text-sb fw-bold mt-1'><center>"+data.name.split(" ")[0]+"</center></span>"
                imageContainer.innerHTML += "<span class='text-dark mt-1'><center>RANK "+data.leader_rank+"</center></span>"
                nameElement.appendChild(imageContainer);

                // Chart
                let chartElement = document.getElementById("chart-"+index+"-"+i);

                let tglArr = [];
                let efficiencyArr = [];
                let targetEfficiencyArr = [];
                let rftArr = [];

                let dailyData = data.data.filter((item) => item.mins_avail > 0 && item.output > 0);

                dailyData.forEach(item => {
                    tglArr.push(formatDateTick(item.tanggal));
                    efficiencyArr.push((item.mins_prod / item.mins_avail * 100).round(2));
                    rftArr.push((item.rft / item.output * 100).round(2));
                });

                await ApexCharts.exec('chart-'+index+"-"+i, 'updateSeries', [
                        {
                            name: 'Efficiency',
                            data: efficiencyArr
                        },
                        {
                            name: 'RFT',
                            data: rftArr
                        },
                    ], true);

                await ApexCharts.exec('chart-'+index+"-"+i, 'updateOptions', {
                        xaxis: {
                            categories: tglArr,
                        },
                        noData: {
                            text: 'Data Not Found'
                        },
                        annotations: {
                            xaxis: data.leaders
                        },
                    }, false, true);
            } else {
                if (document.getElementById("line-con-"+index)) {
                    let lineSubContainer = document.getElementById("line-con-"+index);

                    appendSubRow(lineSubContainer, data, index, i);
                }
            }
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
                url: "{{ route("dashboard-leader-sewing-range-data-export") }}",
                type: 'post',
                data: {
                    from : $("#from").val(),
                    to : $("#to").val(),
                    buyer_id : $("#buyer_id").val(),
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
    </script>
@endsection
