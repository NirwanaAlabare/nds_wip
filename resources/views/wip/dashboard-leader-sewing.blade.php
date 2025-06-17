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
                <button class="btn btn-success" onclick="exportExcel(this)"><i class="fa fa-file-excel"></i></button>
            </div>
        </div>
        <div class="d-flex justify-content-center mb-1">
            <b><span id="from-label">{{ localeDateFormat($from, false) }}</span> <span>s/d</span> <span id="to-label">{{ localeDateFormat($to, false) }}</span></b>
        </div>
        <div class="row g-3" id="leader-line-charts">
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="leaderSewingFilterModal" tabindex="-1" aria-labelledby="leaderSewingFilterModal" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-sb">
                    <h1 class="modal-title fs-5" id="leaderSewingFilterModalLabel"><i class="fa fa-filter"></i> Filter</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Defect Types</label>
                        <select class="select2bs4filter" name="defect_types[]" multiple="multiple" id="defect_types">
                            @foreach ($defectTypes as $defectType)
                                <option value="{{ $defectType->id }}">{{ $defectType->defect_type }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Defect Areas</label>
                        <select class="select2bs4filter" name="defect_areas[]" multiple="multiple" id="defect_areas">
                            @foreach ($defectAreas as $defectArea)
                                <option value="{{ $defectArea->id }}">{{ $defectArea->defect_area }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Defect Status</label>
                        <select class="select2bs4filter" name="defect_status[]" multiple="multiple" id="defect_status">
                            <option value="">SEMUA</option>
                            <option value="defect">DEFECT</option>
                            <option value="reworked">REWORKED</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Sewing Line</label>
                        <select class="select2bs4filter" name="sewing_line[]" multiple="multiple" id="sewing_line">
                            <option value="">SEMUA</option>
                            @foreach ($lines as $line)
                                <option value="{{ $line }}">{{ strtoupper(str_replace("_", " ", $line)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Buyer</label>
                        <select class="select2bs4filter" name="buyer[]" multiple="multiple" id="buyer">
                            <option value="">Buyer</option>
                            @foreach ($suppliers as $supplier)
                                <option value="{{ $supplier }}">{{ $supplier }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">No. WS</label>
                        <select class="select2bs4filter" name="ws[]" multiple="multiple" id="ws">
                            <option value="">SEMUA</option>
                            @foreach ($orders as $order)
                                <option value="{{ $order }}">{{ $order }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Style</label>
                        <select class="select2bs4filter" name="style[]" multiple="multiple" id="style">
                            <option value="">SEMUA</option>
                            @foreach ($styles as $style)
                                <option value="{{ $style }}">{{ $style }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Color</label>
                        <select class="select2bs4filter" name="color[]" multiple="multiple" id="color">
                            <option value="">SEMUA</option>
                            @foreach ($colors as $color)
                                <option value="{{ $color }}">{{ $color }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Size</label>
                        <select class="select2bs4filter" name="size[]" multiple="multiple" id="size">
                            <option value="">SEMUA</option>
                            @foreach ($sizes as $size)
                                <option value="{{ $size }}">{{ $size }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">External Type</label>
                        <select class="select2bs4filter" name="external_type[]" multiple="multiple" id="external_type">
                            <option value="">SEMUA</option>
                            @foreach ($externalTypes as $externalType)
                                <option value="{{ $externalType }}">{{ ($externalType ? strtoupper($externalType) : "SEWING") }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">External IN</label>
                        <input type="date" class="form-control" name="external_in">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">External OUT</label>
                        <input type="date" class="form-control" name="external_out">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Bersihkan <i class="fa-solid fa-broom"></i></button>
                    <button type="button" class="btn btn-success" onclick="reportDefectDatatableReload()">Simpan <i class="fa-solid fa-check"></i></button>
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
                    console.log(response);

                    document.getElementById('leader-line-charts').innerHTML = "";

                    let lineEfficiency = objectValues(objectGroupBy(response, ({ line_id }) => line_id));

                    if (lineEfficiency.length > 0) {
                        document.getElementById('leader-line-charts').style.justifyContent = "start";
                        document.getElementById('leader-line-charts').style.paddingTop = "15px";

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
                                                borderColor: '#00E396',
                                                text: currentLeader.leader_name
                                            }
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

                            lineDailyEfficiency.push({"id": element[element.length-1].leader_id ? element[element.length-1].leader_id : 'KOSONG', "nik": element[element.length-1].leader_nik ? element[element.length-1].leader_nik : 'KOSONG', "name": element[element.length-1].leader_name ? element[element.length-1].leader_name : 'KOSONG', "line": element[element.length-1].line_name, "data": dateOutput, "leaders": lineLeaderList});
                        });

                        // Show Chief Daily Data
                        for (let i = 0; i < lineDailyEfficiency.length; i++) {
                            appendRow(lineDailyEfficiency[i], i+1);
                        }
                    } else {
                        document.getElementById('leader-line-charts').innerHTML = "Data tidak ditemukan.";
                        document.getElementById('leader-line-charts').style.justifyContent = "center";
                        document.getElementById('leader-line-charts').style.paddingTop = "15px";
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
                                            borderColor: '#00E396',
                                            text: currentLeader.leader_name
                                        }
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

                        lineDailyEfficiency.push({"id": element[element.length-1].leader_id ? element[element.length-1].leader_id : 'KOSONG', "nik": element[element.length-1].leader_nik ? element[element.length-1].leader_nik : 'KOSONG', "name": element[element.length-1].leader_name ? element[element.length-1].leader_name : 'KOSONG', "line": element[element.length-1].line_name, "data": dateOutput, "leaders": lineLeaderList});
                    });

                    console.log(lineDailyEfficiency);

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
            console.log("data", data);

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
            let imageSubContainer = document.createElement("div")
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
                annotations: {
                    xaxis: data.leaders
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
                nameElement.appendChild(imageContainer);

                // Chart
                let chartElement = document.getElementById("chart-"+index);

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

                await ApexCharts.exec('chart-'+index, 'updateSeries', [
                        {
                            name: 'Efficiency',
                            data: efficiencyArr
                        },
                        {
                            name: 'RFT',
                            data: rftArr
                        },
                    ], true);

                await ApexCharts.exec('chart-'+index, 'updateOptions', {
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
                appendRow(data, index);
            }
        }

        async function updateFilterOption() {
            document.getElementById('loading').classList.remove('d-none');

            await $.ajax({
                url: '{{ route('filter-leader-sewing') }}',
                dataType: 'json',
                dataSrc: 'data',
                data: {
                    dateFrom : $('#dateFrom').val(),
                    dateTo : $('#dateTo').val(),
                    department : $('#department').val()
                },
                success: async function(response) {
                    document.getElementById('loading').classList.add('d-none');

                    if (response) {
                        // lines options
                        if (response.lines && response.lines.length > 0) {
                            let lines = response.lines;
                            $('#sewing_line_filter').empty();
                            $.each(lines, function(index, value) {
                                $('#sewing_line_filter').append('<option value="'+value+'">'+value+'</option>');
                            });
                        }
                        // suppliers option
                        if (response.suppliers && response.suppliers.length > 0) {
                            let suppliers = response.suppliers;
                            $('#buyer_filter').empty();
                            $.each(suppliers, function(index, value) {
                                $('#buyer_filter').append('<option value="'+value+'">'+value+'</option>');
                            });
                        }
                        // orders option
                        if (response.orders && response.orders.length > 0) {
                            let orders = response.orders;
                            $('#ws_filter').empty();
                            $.each(orders, function(index, value) {
                                $('#ws_filter').append('<option value="'+value+'">'+value+'</option>');
                            });
                        }
                        // styles option
                        if (response.styles && response.styles.length > 0) {
                            let styles = response.styles;
                            $('#style_filter').empty();
                            $.each(styles, function(index, value) {
                                $('#style_filter').append('<option value="'+value+'">'+value+'</option>');
                            });
                        }
                        // colors option
                        if (response.colors && response.colors.length > 0) {
                            let colors = response.colors;
                            $('#color_filter').empty();
                            $.each(colors, function(index, value) {
                                $('#color_filter').append('<option value="'+value+'">'+value+'</option>');
                            });
                        }
                        // sizes option
                        if (response.sizes && response.sizes.length > 0) {
                            let sizes = response.sizes;
                            $('#size_filter').empty();
                            $.each(sizes, function(index, value) {
                                $('#size_filter').append('<option value="'+value+'">'+value+'</option>');
                            });
                        }
                    }
                },
                error: function(jqXHR) {
                    document.getElementById('loading').classList.add('d-none');

                    console.error(jqXHR);
                },
            })
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
