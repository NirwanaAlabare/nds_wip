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
        <input type="hidden" id="year" value="{{ $year ? $year : date("Y") }}">
        <input type="hidden" id="month" value="{{ $month ? $month : date("m") }}">
        <input type="hidden" id="month-name" value="{{ $monthName ? $monthName : $months[num(date("m"))-1] }}">
        <div class="d-flex justify-content-center mb-3">
            <h5 class="text-dark fw-bold">FACTORY DAILY PERFORMANCE - <span id="month-label">{{ strtoupper($monthName) }}</span> <span id="year-label">{{ $year }}</span></h5>
        </div>
        <div class="row bg-light g-3 pb-3" id="factory-daily-charts">
            <div id="factory-daily-chart"></div>
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
            const year = date.getFullYear();
            return `${day} ${month} ${year}`;
        }

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

            await $.ajax({
                url: "{{ route("dashboard-factory-daily-sewing-data") }}",
                type: "get",
                data: {
                    month: $("#month").val(),
                    year: $("#year").val()
                },
                dataType: "json",
                success: async function (response) {
                    console.log(response);

                    document.getElementById('factory-daily-charts').innerHTML = '';

                    let dailyEfficiency = response;

                    if (dailyEfficiency.length > 0) {
                        document.getElementById('factory-daily-charts').style.justifyContent = "center";
                        document.getElementById('factory-daily-charts').style.paddingTop = "15px";

                        generateChart(dailyEfficiency);
                    } else {
                        document.getElementById('factory-daily-charts').innerHTML = "Data tidak ditemukan.";
                        document.getElementById('factory-daily-charts').style.justifyContent = "center";
                        document.getElementById('factory-daily-charts').style.paddingTop = "15px";
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
                url: "{{ route("dashboard-factory-daily-sewing-data") }}",
                type: "get",
                data: {
                    month: $("#month").val(),
                    year: $("#year").val()
                },
                dataType: "json",
                success: async function (response) {
                    console.log(response);

                    let dailyEfficiency = response;

                    if (dailyEfficiency.length > 0) {
                        document.getElementById('factory-daily-charts').style.justifyContent = "center";
                        document.getElementById('factory-daily-charts').style.paddingTop = "15px";

                        updateChart(dailyEfficiency);
                    } else {
                        document.getElementById('factory-daily-charts').innerHTML = "Data tidak ditemukan.";
                        document.getElementById('factory-daily-charts').style.justifyContent = "center";
                        document.getElementById('factory-daily-charts').style.paddingTop = "15px";
                    }
                },
                error: function (jqXHR) {
                    console.error(jqXHR);
                }
            });
        }

        // Generate Chart
        async function generateChart(data) {
            let parentElement = document.getElementById('factory-daily-charts');

            let chartContainer = document.createElement("div");
            chartContainer.classList.add("col-md-12");
            let canvasContainer = document.createElement("div");
            let canvas = document.createElement("div");
            // canvas.id = "chart-"+index;
            canvas.classList.add("factory-daily-chart");
            canvasContainer.appendChild(canvas);
            chartContainer.appendChild(canvasContainer);

            let tglArr = [];
            let efficiencyArr = [];
            let rftArr = [];

            let dailyData = data.filter((item) => item.cumulative_mins_avail > 0 && item.output > 0);

            dailyData.forEach(item => {
                tglArr.push(formatDateTick(item.tanggal));
                efficiencyArr.push((item.mins_prod / item.cumulative_mins_avail * 100).round(2));
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
                    id: "factory-daily-chart",
                    height: 500,
                    type: 'line',
                    zoom: {
                        enabled: true
                    },
                    toolbar: {
                        show: false
                    }
                },
                colors: ['#359cae', '#fd7f2a'],
                dataLabels: {
                    enabled: true,
                    style: {
                        fontSize: "14px",
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
                    min: 0,
                    tickAmount: 10,
                    labels: {
                        style: {
                            fontSize: "12px",
                        }
                    }
                },
                xaxis: {
                    categories: tglArr,
                    labels: {
                        style: {
                            fontSize: "12px",
                        },
                    },
                    rotate: 0,             // Start with no rotation
                    rotateAlways: false,   // Auto-rotate only if they overlap
                },
                noData: {
                    text: 'Data Not Found'
                },
                legend: {
                    position: 'bottom',
                    horizontalAlign: 'center',
                    // floating: true,
                    offsetY: 5,
                    offsetX: -5,
                    fontSize: "14px",
                },
                redrawOnParentResize: true
            };

            var chart = new ApexCharts(canvas, options);

            parentElement.appendChild(chartContainer);

            chart.render();
        }

        async function updateChart(data) {
            let tglArr = [];
            let efficiencyArr = [];
            let rftArr = [];

            let dailyData = data.filter((item) => item.cumulative_mins_avail > 0 && item.output > 0);

            dailyData.forEach(item => {
                tglArr.push(formatDateTick(item.tanggal));
                efficiencyArr.push((item.mins_prod / item.cumulative_mins_avail * 100).round(2));
                rftArr.push((item.rft / item.output * 100).round(2));
            });

            await ApexCharts.exec('factory-daily-chart', 'updateSeries', [
                    {
                        name: 'Efficiency',
                        data: efficiencyArr
                    },
                    {
                        name: 'RFT',
                        data: rftArr
                    },
                ], true);

            await ApexCharts.exec('factory-daily-chart', 'updateOptions', {
                    xaxis: {
                        categories: tglArr,
                        rotate: 0,             // Start with no rotation
                        rotateAlways: false,   // Auto-rotate only if they overlap
                    },
                    noData: {
                        text: 'Data Not Found'
                    },
                }, false, true);
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
