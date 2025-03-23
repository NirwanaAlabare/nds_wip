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
            <button class="btn btn-success" onclick="exportExcel(this)" disabled><i class="fa fa-file-excel"></i></button>
        </div>
        <div class="d-flex justify-content-center mb-1">
            <span><b>{{ localeDateFormat($from, false) }}</b> s/d <b>{{ localeDateFormat($to, false) }}</b></span>
        </div>
        <div class="row g-3" id="leader-line-charts">
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

            await updateData();

            document.getElementById("loading").classList.add("d-none");
        }

        // Get Data
        async function getData() {
            document.getElementById("loading").classList.remove("d-none");

            await $.ajax({
                url: "{{ route("dashboard-leader-sewing-data") }}",
                type: "get",
                data: {
                    from: $("#from").val(),
                    to: $("#to").val()
                },
                dataType: "json",
                success: async function (response) {
                    console.log(response);

                    document.getElementById('leader-line-charts').innerHTML = "";

                    let lineEfficiency = Object.values(Object.groupBy(response, ({ line_id }) => line_id));

                    let lineDailyEfficiency = [];
                    lineEfficiency.forEach(element => {
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

                        lineDailyEfficiency.push({"id": element[0].leader_id ? element[0].leader_id : 'KOSONG', "nik": element[0].leader_nik ? element[0].leader_nik : 'KOSONG', "name": element[0].leader_name ? element[0].leader_name : 'KOSONG', "line": element[0].line_name, "data": dateOutput});
                    });

                    // Show Chief Daily Data
                    for (let i = 0; i < lineDailyEfficiency.length; i++) {
                        appendRow(lineDailyEfficiency[i], i+1);
                    }
                },
                error: function (jqXHR) {
                    console.error(jqXHR);
                }
            });

            document.getElementById("loading").classList.add("d-none");
        }

        // Update Data
        async function updateData() {
            await $.ajax({
                url: "{{ route("dashboard-leader-sewing-data") }}",
                type: "get",
                data: {
                    from: $("#from").val(),
                    to: $("#to").val()
                },
                dataType: "json",
                success: async function (response) {
                    let lineEfficiency = Object.values(Object.groupBy(response, ({ line_id }) => line_id));

                    let lineDailyEfficiency = [];
                    lineEfficiency.forEach(element => {
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

                        lineDailyEfficiency.push({"id": element[0].leader_id ? element[0].leader_id : 'KOSONG', "nik": element[0].leader_nik ? element[0].leader_nik : 'KOSONG', "name": element[0].leader_name ? element[0].leader_name : 'KOSONG', "line": element[0].line_name, "data": dateOutput});
                    });

                    // Show Chief Daily Data
                    for (let i = 0; i < lineDailyEfficiency.length; i++) {
                        updateRow(lineDailyEfficiency[i], i+1);
                    }
                },
                error: function (jqXHR) {
                    console.error(jqXHR);
                }
            });
        }

        // Generate Element
        function appendRow(data, index) {
            let parentElement = document.getElementById('leader-line-charts');

            // Container
            let div = document.createElement("div");
            div.classList.add("col-md-4");
            let card = document.createElement("div");
            card.classList.add("card");
            card.classList.add("card-sb");
            card.classList.add("h-100");
            let cardHeader = document.createElement("div")
            cardHeader.classList.add("card-header");
            cardHeader.innerHTML = "<h5 class='card-title fw-bold' id='line-"+index+"'>"+data.line+"</h5>";
            let cardBody = document.createElement("div")
            cardBody.classList.add("card-body");
            let container = document.createElement("div");
            container.classList.add("row");
            container.classList.add("align-items-center");

            // Image
            let employeeContainer = document.createElement("div");
            employeeContainer.id = "employee-"+index;
            employeeContainer.classList.add("col-md-3");
            let imageContainer = document.createElement("div");
            imageContainer.classList.add("d-flex")
            imageContainer.classList.add("flex-column")
            imageContainer.classList.add("align-items-center")
            let imageElement = document.createElement("img");
            imageElement.src = "{{ asset('../storage/employee_profile') }}/"+data.nik+"%20"+data.name+".png"
            imageElement.setAttribute("onerror", "this.onerror=null; this.src='{{ asset('dist/img/person.png') }}'");
            imageElement.setAttribute("alt", "person")
            imageElement.classList.add("img-fluid")
            imageElement.style.marginLeft = "auto";
            imageElement.style.marginRight = "auto";
            imageElement.style.height = "100px";
            imageContainer.appendChild(imageElement);
            imageContainer.innerHTML += "<span class='text-sb fw-bold mt-1'><center>"+data.name.split(" ")[0]+"</center></span>"
            employeeContainer.appendChild(imageContainer);

            // Chart
            let chartContainer = document.createElement("div");
            chartContainer.classList.add("col-md-9");
            let canvasContainer = document.createElement("div");
            let canvas = document.createElement("div");
            // canvas.id = "chart-"+index;
            canvas.classList.add("line-efficiency-chart");
            canvasContainer.appendChild(canvas);
            chartContainer.appendChild(canvasContainer);

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
                        fontSize: "11px",
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
                            fontSize: "11px",
                        }
                    }
                },
                xaxis: {
                    categories: tglArr,
                    labels: {
                        style: {
                            fontSize: "11px",
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
                    fontSize: "11px",
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

            parentElement.appendChild(div);

            chart.render();
        }

        // Update Element
        async function updateRow(data, index) {
            if (document.getElementById("line-"+index)) {
                // Name
                let lineElement = document.getElementById("line-"+index);
                let nameElement = document.getElementById("employee-"+index);

                lineElement.innerHTML = data.line;
                nameElement.innerHTML = "";

                // Image
                let imageContainer = document.createElement("div");
                imageContainer.classList.add("d-flex")
                imageContainer.classList.add("flex-column")
                imageContainer.classList.add("align-items-center")
                let imageElement = document.createElement("img");
                imageElement.src = "{{ asset('../storage/employee_profile') }}/"+data.nik+"%20"+data.name+".png"
                imageElement.setAttribute("onerror", "this.onerror=null; this.src='{{ asset('dist/img/person.png') }}'");
                imageElement.setAttribute("alt", "person")
                imageElement.classList.add("img-fluid")
                imageElement.style.marginLeft = "auto";
                imageElement.style.marginRight = "auto";
                imageElement.style.height = "100px";
                imageContainer.appendChild(imageElement);
                imageContainer.innerHTML += "<span class='text-sb fw-bold mt-1'><center>"+data.name.split(" ")[0]+"</center></span>"
                nameElement.appendChild(imageContainer);

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

                await ApexCharts.exec('chart-'+index, 'updateOptions', {
                        xaxis: {
                            categories: tglArr,
                        },
                        noData: {
                            text: 'Data Not Found'
                        }
                    }, false, true);
            } else {
                appendRow(data, index);
            }
        }
    </script>
@endsection
