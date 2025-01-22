@extends('layouts.index', ["navbar" => false, "containerFluid" => true])

@section('custom-link')
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-rowgroup/css/rowGroup.bootstrap4.min.css') }}">
    <!-- Apex Charts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .myDoughnutChartDiv {
            width: 300px;
            height: 300px;
        }

        .myPieChartDiv {
            width: 900px;
            height: 500px;
        }

        .wrapperDoughnut {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .PillList-item {
            cursor: pointer;
            display: inline-block;
            float: left;
            font-size: 14px;
            font-weight: normal;
            line-height: 20px;
            margin: 0 12px 12px 0;
            text-transform: capitalize;
            width: 100%;
        }

        .PillList-item input[type="checkbox"] {
            display: none;
        }

        .PillList-item input[type="checkbox"]:checked+.PillList-label {
            background-color: rgb(0, 0, 0);
            border: 1px solid rgb(0, 0, 0);
            color: #fff;
            padding: 7px 21px;
        }

        .PillList-label {
            border: 1px solid rgb(144, 144, 144);
            border-radius: 7px;
            color: rgb(37, 37, 37);
            display: flex;
            align-items: center;
            padding: 7px 30px;
            text-decoration: none;
            cursor: pointer;
            width: 100%;
            height: 60px;
            justify-content: center;
        }

        .PillList-item input[type="checkbox"]:checked+.PillList-label .Icon--checkLight {
            display: inline-block;
        }

        .PillList-item input[type="checkbox"]:checked+.PillList-label .Icon--addLight,
        .PillList-label .Icon--checkLight,
        .PillList-children {
            display: none;
        }

        .PillList-label .Icon {
            width: 12px;
            height: 12px;
            margin: 0 0 0 6px;
        }

        .Icon--smallest {
            font-size: 12px;
            line-height: 12px;
        }

        .Icon {
            background: transparent;
            display: inline-block;
            font-style: normal;
            vertical-align: baseline;
            position: relative;
        }

        .header {
            /* margin-bottom: 2rem; */
        }

        .header-title {
            font-size: 1.5rem;
            font-weight: bold;
            /* margin-bottom: 0.5rem; */
        }

        .header-date {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border: 1px solid #ccc;
            border-radius: 4px;
            background: white;
        }

        .header-date span {
            font-size: 0.875rem;
            color: #666;
        }

        .description {
            font-size: 0.875rem;
            color: #666;
        }

        #selected-date {
            color: black;
            font-weight: bold;
        }

        /* SWIPPER */

        swiper-container {
            width: 100%;
            height: 100%;
            background-color: #ffffff !important;
        }

        swiper-slide {
            text-align: left;
            font-size: 18px;
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
    </style>
@endsection

@section('content')
    <div class="loading-container-fullscreen d-none" id="loading-cutting-form">
        <div class="loading-container">
            <div class="loading"></div>
        </div>
    </div>
    <div id="realtimeUpdateWrap"></div>
    <button type="button" onclick="window.history.back()" class="btn btn-primary mb-3 d-none">Kembali</button>
    <div class="card card-sb">
        <div class="card-body">
            <div class="d-flex">
                <div class="card " style="width: 100%;">
                    <div class="card-body mb-1 pb-1 d-flex justify-content-between">
                        <div class="header">
                            <div class="header-top">
                                <h1 class="header-title">Dashboard Cutting Chart Progress</h1>
                            </div>
                            <p class="description">Laporan progress cutting <strong
                                    id="selected-date">{{ localeDateFormat($tglPlan) }}</strong></p>
                        </div>
                        <div class="item-checklist-box w-50">
                            <label class="PillList-item">
                                <input type="checkbox" name="feature" value="1">
                                <span class="PillList-label">
                                </span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <swiper-container class="mySwiper" autoplay-delay="15000" autoplay-disable-on-interaction="false" space-between="30" centered-slides="true">
                <swiper-slide>
                    <div class="card m-3 w-100">
                        <div class="card-body">
                            <div>
                                <div class="d-flex justify-content-between align-items-end">
                                    <p class="mb-0 fw-bold">{{ strtoupper(str_replace('_', ' ', $mejaId)) }}</p>
                                    <div class="d-flex flex-column align-items-end">
                                        <p class="mb-0 fw-bold">{{ localeDateFormat($tglPlan) }}</p>
                                        <p class="mb-0 fw-bold clock"></p>
                                    </div>
                                </div>
                                <table class="table table-bordered" id="datatable-detail-form">
                                    <thead>
                                        <tr>
                                            <th colspan="8" class="text-center">DETAIL FORM</th>
                                        </tr>
                                        <tr>
                                            <th>NO. FORM</th>
                                            <th>PANEL</th>
                                            <th>STYLE</th>
                                            <th>COLOR</th>
                                            <th>RASIO</th>
                                            <th>QTY PLY</th>
                                            <th>OUTPUT</th>
                                            <th>STATUS</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="8" class="text-center">Data Not Available</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </swiper-slide>
                <swiper-slide>
                    <div class="card m-3 w-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-middle mb-3">
                                <p class="mb-0 fw-bold">{{ strtoupper(str_replace('_', ' ', $mejaId)) }}</p>
                                <div class="d-flex flex-column align-items-end">
                                    <p class="mb-0 fw-bold">{{ localeDateFormat($tglPlan) }}</p>
                                    <p class="mb-0 fw-bold clock"></p>
                                </div>
                            </div>
                            <div class="d-flex justify-content-evenly" style="gap: 20px">
                                <div class="card" style="width: 100%; height: 340px">
                                    <canvas id="myChart" width="1000" height="450"></canvas>
                                </div>
                                <div class="wrapperDoughnut">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="myDoughnutChartDiv">
                                                <canvas id="myDoughnutChart" width="50" height="50"></canvas>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </swiper-slide>
                <swiper-slide>
                    <div class="card m-3 w-100">
                        <div class="card-body">
                            <div>
                                <div class="d-flex justify-content-between align-items-middle">
                                    <p class="mb-0 fw-bold">{{ strtoupper(str_replace('_', ' ', $mejaId)) }}</p>
                                    <div class="d-flex flex-column align-items-end">
                                        <p class="mb-0 fw-bold">{{ localeDateFormat($tglPlan) }}</p>
                                        <p class="mb-0 fw-bold clock"></p>
                                    </div>
                                </div>
                                <table class="table table-bordered" id="datatable-detail-form-ws">
                                    <thead>
                                        <tr>
                                            <th colspan="6" class="text-center">DETAIL FORM</th>
                                        </tr>
                                        <tr>
                                            <th>WS</th>
                                            <th>BUYER</th>
                                            <th>STYLE</th>
                                            <th>COLOR</th>
                                            <th>PANEL</th>
                                            <th>OUTPUT</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="6" class="text-center">Data Not Available</td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="5" class="text-center fw-bold">Total</td>
                                            <td class="fw-bold">...</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </swiper-slide>
            </swiper-container>
        </div>
    </div>
@endsection

@section('custom-script')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-element-bundle.min.js"></script>

    <!-- DataTables & Plugins -->
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-rowgroup/js/dataTables.rowGroup.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-rowgroup/js/rowGroup.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-rowsgroup/dataTables.rowsGroup.js') }}"></script>
    <!-- Apex Charts -->
    <script src="{{ asset('plugins/apexcharts/apexcharts.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>

    <!-- Chart.JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.min.js"
        integrity="sha512-L0Shl7nXXzIlBSUUPpxrokqq4ojqgZFQczTYlGjzONGTDAcLremjwaWv5A+EDLnxhQzY5xUZPWLOLqYRkY0Cbw=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <script>
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

        let datatableDetailForm = $("#datatable-detail-form").DataTable({
            processing: true,
            ordering: false,
            searching: false,
            paging: false,
            serverSide: true,
            ajax: {
                url: '{{ route('cutting-form-list') }}',
                data: function(d) {
                    d.meja_id = @json($mejaId);
                    d.date = @json($tglPlan);
                },
            },
            columns: [
                {
                    data: 'no_form'
                },
                {
                    data: 'panel'
                },
                {
                    data: 'style'
                },
                {
                    data: 'color'
                },
                {
                    data: 'total_ratio'
                },
                {
                    data: 'total_lembar'
                },
                {
                    data: 'total_output'
                },
                {
                    data: 'status'
                }
            ],
            columnDefs: [
                {
                    targets: [5],
                    className: "text-center",
                    render: (data, type, row, meta) => {
                        var color = 'black';

                        if (row.status == 'SELESAI PENGERJAAN') {
                            color = '#087521';
                        } else if (row.status == 'PENGERJAAN MARKER') {
                            color = '#2243d6';
                        } else if (row.status == 'PENGERJAAN FORM CUTTING') {
                            color = '#2243d6';
                        } else if (row.status == 'PENGERJAAN FORM CUTTING DETAIL') {
                            color = '#2243d6';
                        } else if (row.status == 'PENGERJAAN FORM CUTTING SPREAD') {
                            color = '#2243d6';
                        } else {
                            if (row.app != 'Y') {
                                color = '#616161';
                            }
                        }

                        return '<span style="font-weight: 600; color:' + color + ';">' + (data ? data : '-') + '</span>';
                    }
                },
                {
                    targets: [6],
                    className: "text-center",
                    render: (data, type, row, meta) => {
                        var color = 'black';

                        if (row.status == 'SELESAI PENGERJAAN') {
                            color = '#087521';
                        } else if (row.status == 'PENGERJAAN MARKER') {
                            color = '#2243d6';
                        } else if (row.status == 'PENGERJAAN FORM CUTTING') {
                            color = '#2243d6';
                        } else if (row.status == 'PENGERJAAN FORM CUTTING DETAIL') {
                            color = '#2243d6';
                        } else if (row.status == 'PENGERJAAN FORM CUTTING SPREAD') {
                            color = '#2243d6';
                        } else {
                            if (row.app != 'Y') {
                                color = '#616161';
                            }
                        }

                        return '<span style="font-weight: 600; color:' + color + ';">' + (data ? data : '-') + '</span>';
                    }
                },
                {
                    targets: [7],
                    className: "text-center align-middle",
                    render: (data, type, row, meta) => {
                        icon = "";

                        switch (data) {
                            case "SPREADING":
                                if (row.app != 'Y') {
                                    icon = `<i class="fas fa-file fa-lg" style="color: #616161;"></i>`;
                                } else {
                                    icon = `<i class="fas fa-file fa-lg"></i>`;
                                }
                                break;
                            case "PENGERJAAN MARKER":
                            case "PENGERJAAN FORM CUTTING":
                            case "PENGERJAAN FORM CUTTING DETAIL":
                            case "PENGERJAAN FORM CUTTING SPREAD":
                                icon =
                                    `<i class="fas fa-sync-alt fa-spin fa-lg" style="color: #2243d6;"></i>`;
                                break;
                            case "SELESAI PENGERJAAN":
                                icon = `<i class="fas fa-check fa-lg" style="color: #087521;"></i>`;
                                break;
                        }

                        return icon;
                    }
                },
                {
                    targets: '_all',
                    className: "text-nowrap",
                    render: (data, type, row, meta) => {
                        var color = 'black';

                        if (row.status == 'SELESAI PENGERJAAN') {
                            color = '#087521';
                        } else if (row.status == 'PENGERJAAN MARKER') {
                            color = '#2243d6';
                        } else if (row.status == 'PENGERJAAN FORM CUTTING') {
                            color = '#2243d6';
                        } else if (row.status == 'PENGERJAAN FORM CUTTING DETAIL') {
                            color = '#2243d6';
                        } else if (row.status == 'PENGERJAAN FORM CUTTING SPREAD') {
                            color = '#2243d6';
                        } else {
                            if (row.app != 'Y') {
                                color = '#616161';
                            }
                        }

                        return '<span style="font-weight: 600; color:' + color + ';">' + (data ? data : "-") + '</span>';
                    }
                }
            ],
        });

        function datatableDetailFormReload() {
            $("#datatable-detail-form").DataTable().ajax.reload();
        }

        let datatableDetailFormWs = $("#datatable-detail-form-ws").DataTable({
            processing: true,
            ordering: false,
            searching: false,
            paging: false,
            serverSide: true,
            ajax: {
                url: '{{ route('cutting-worksheet-list') }}',
                data: function(d) {
                    d.meja_id = @json($mejaId);
                    d.date = @json($tglPlan);
                },
            },
            columns: [
                {
                    data: 'act_costing_ws'
                },
                {
                    data: 'buyer'
                },
                {
                    data: 'style'
                },
                {
                    data: 'color'
                },
                {
                    data: 'panel'
                },
                {
                    data: 'output'
                },
            ],
            columnDefs: [{
                targets: [5],
                className: "text-center",
                render: (data, type, row, meta) => {
                    return data ? data : '-';
                }
            }, ],
            footerCallback: async function(row, data, start, end, display) {
                var api = this.api(),
                    data;

                $(api.column(0).footer()).html('Total');
                $(api.column(5).footer()).html("...");

                $.ajax({
                    url: '{{ route('cutting-worksheet-total') }}',
                    dataType: 'json',
                    dataSrc: 'data',
                    data: {
                        meja_id: @json($mejaId),
                        date: @json($tglPlan)
                    },
                    success: function(response) {
                        console.log(response);

                        if (response && response[0]) {
                            // Update footer by showing the total with the reference of the column index
                            $(api.column(0).footer()).html('Total');
                            $(api.column(5).footer()).html(response[0]['total_output']);
                        }
                    },
                    error: function(jqXHR) {
                        console.log(jqXHR);
                    },
                });
            },
        });

        function datatableDetailFormWsReload() {
            $("#datatable-detail-form-ws").DataTable().ajax.reload();
        }

        // Function to update the clock every second
        function updateClock() {
            const now = new Date();
            let hours = now.getHours();
            let minutes = now.getMinutes();

            // Format hours and minutes to always show two digits
            hours = hours < 10 ? '0' + hours : hours;
            minutes = minutes < 10 ? '0' + minutes : minutes;

            // Display the time in hh:mm format
            const timeString = hours + ':' + minutes;

            // Update the content of the clock element
            for (let i = 0; i < document.getElementsByClassName('clock').length; i++) {
                document.getElementsByClassName('clock')[i].innerHTML = timeString;
            }
        }

        // Update the clock immediately
        updateClock();

        var clockInterval = setInterval(updateClock, 1000);
    </script>

    <!-- SOCKET.IO configuration -->
    <script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4="
        crossorigin="anonymous"></script>
    <script>
        window.laravel_echo_port = '{{ env('LARAVEL_ECHO_PORT') }}';
    </script>
    <script src="http://{{ Request::getHost() }}:{{ config('redis.echo_port') }}/socket.io/socket.io.js"></script>
    <script src="{{ config('redis.redis_url_public') }}/js/laravel-echo-setup.js" type="text/javascript"></script>

    <script>
        let myChart;
        let myDoughnutChart;
        let mejaArr = [];
        let tglArr = [];
        let noMejaId = [];
        let totalFormArr = [];
        let completedFormArr = [];
        let incompletedFormArr = [];
        let totalCompleted = 0;
        let totalIncompleted = 0;
        let previousDate = '';
        var mejaId = @json($mejaId);

        var tglPlan = @json($tglPlan);

        if (!tglPlan) {
            alert("Tanggal Plan: " + tglPlan);
        }

        function formatDate(date) {
            const months = [
                'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
            ];
            const dateObj = new Date(date);
            const day = String(dateObj.getDate()).padStart(2, '0');
            const month = months[dateObj.getMonth()];
            const year = dateObj.getFullYear();

            return `${day} ${month} ${year}`;
        }

        $(document).ready(async function() {
            document.getElementById("loading-cutting-form").classList.remove("d-none");

            await loadCuttingFormChart([mejaId], tglPlan);

            const selectedDateElement = document.getElementById('selected-date');

            function updateDescription() {
                const selectedDate = tglPlan;
                selectedDateElement.textContent = formatDate(tglPlan);
            }

            dateInput.addEventListener('input', updateDescription);

            updateDescription();

            document.getElementById("loading-cutting-form").classList.add("d-none");
        });

        function generateCheckboxes(noMejaId, selectedCheckboxesIds) {
            const checkboxContainer = document.querySelector('.item-checklist-box');

            // Simpan checkbox yang sudah dipilih
            const selectedCheckboxes = Array.from(checkboxContainer.querySelectorAll('input[type="checkbox"]:checked'))
                .map(checkbox => checkbox.value);

            checkboxContainer.innerHTML = ''; // Menghapus konten lama
            noMejaId.forEach((meja, index) => {
                const checkboxLabel = document.createElement('label');
                checkboxLabel.classList.add('PillList-item');

                // Membuat checkbox
                const checkboxInput = document.createElement('input');
                checkboxInput.type = 'checkbox';
                checkboxInput.name = 'feature';
                checkboxInput.value = meja;
                checkboxInput.checked = true; // Checkbox selalu tercentang

                checkboxInput.addEventListener('change', (e) => {
                    if (!checkboxInput.checked) {
                        checkboxInput.checked = true; // Pastikan tetap tercentang
                    }
                });

                // Membuat label untuk checkbox
                const labelSpan = document.createElement('span');
                labelSpan.classList.add('PillList-label');
                labelSpan.textContent = meja.toUpperCase().replace('_', ' ');

                // Menambahkan ikon check
                const iconSpan = document.createElement('span');
                iconSpan.classList.add('Icon', 'Icon--checkLight', 'Icon--smallest');
                const icon = document.createElement('i');
                icon.classList.add('fa', 'fa-spinner', 'fa-spin'); // Ikon loading
                iconSpan.appendChild(icon);

                // Menambahkan ikon dan label ke checkbox
                labelSpan.appendChild(iconSpan);

                // Menambahkan input checkbox dan label ke dalam label kontainer
                checkboxLabel.appendChild(checkboxInput);
                checkboxLabel.appendChild(labelSpan);

                // Menambahkan label checkbox ke dalam container
                checkboxContainer.appendChild(checkboxLabel);
            });
        }


        function loadCuttingFormChart(selectedCheckboxes, currentDate) {
            const checkboxContainer = document.querySelector('.item-checklist-box');

            if (!checkboxContainer) {
                return;
            }

            const checkboxes = checkboxContainer.querySelectorAll('input[type="checkbox"]');
            checkboxes.forEach(checkbox => {
                const icon = checkbox.parentElement.querySelector('.Icon i');
                if (icon) {
                    icon.classList.remove('fa-check'); // Menghapus ikon check
                    icon.classList.add('fa-spinner', 'fa-spin'); // Menambahkan ikon loading
                }
                checkbox.disabled = true; // Menonaktifkan checkbox selama loading
            });
            return $.ajax({
                url: '{{ route('cutting-chart-by-mejaid') }}',
                type: 'get',
                data: {
                    date: currentDate,
                    meja_id: selectedCheckboxes
                },
                dataType: 'json',
                success: async function(res) {
                    if (res) {
                        console.log('loadCuttingFormChart', res);
                        mejaArr = [];
                        totalFormArr = [];
                        completedFormArr = [];
                        incompletedFormArr = [];
                        totalCompleted = 0;
                        totalIncompleted = 0;
                        if (myChart) {
                            myChart.destroy();
                            myChart = null;
                        }
                        res.forEach(item => {
                            const formattedMeja = item.no_meja ? item.no_meja.toUpperCase().replace('_', ' ') : 'UNKNOWN';
                            noMejaId.push(item.no_meja ? item.no_meja : 0);
                            mejaArr.push(formattedMeja);
                            totalFormArr.push(item.total_form ? item.total_form : 0);
                            completedFormArr.push(item.completed_form ? parseInt(item.completed_form) : 0);
                            incompletedFormArr.push(item.incomplete_form ? parseInt(item.incomplete_form) : 0);

                            totalCompleted += item.completed_form ? parseInt(item.completed_form) : 0;
                            totalIncompleted += item.incomplete_form ? parseInt(item.incomplete_form) :0;
                        });

                        const uniqueNoMejaId = Array.from(new Set([mejaId]));
                        generateCheckboxes(uniqueNoMejaId, selectedCheckboxes);

                        const checkboxesAfterLoad = checkboxContainer.querySelectorAll('input[type="checkbox"]');
                        checkboxesAfterLoad.forEach(checkbox => {
                            const icon = checkbox.parentElement.querySelector('.Icon i');
                            if (selectedCheckboxes.includes(checkbox.value)) {
                                checkbox.checked = true;
                            }
                            icon.classList.remove('fa-spinner', 'fa-spin');
                            icon.classList.add('fa-check'); // Ganti ikon loading dengan check
                            checkbox.disabled = false; // Mengaktifkan kembali checkbox
                        });
                        // Fungsi Utilitas untuk mendukung data
                        const Utils = {
                            months: function({
                                count
                            }) {
                                const months = mejaArr;
                                return months.slice(0, count);
                            },
                            numbers: function({
                                count,
                                min,
                                max
                            }) {
                                return Array.from({
                                    length: count
                                }, () => Math.floor(Math.random() * (max - min + 1)) + min);
                            },
                            CHART_COLORS: {
                                blue: 'rgb(54, 162, 235)',
                                green: '#4CAF50',
                                orange: '#FF5733'
                            }
                        };

                        // Data Chart
                        const DATA_COUNT = mejaArr.length;
                        const NUMBER_CFG = {
                            count: DATA_COUNT,
                            min: -100,
                            max: 100
                        };

                        const labels = Utils.months({
                            count: DATA_COUNT
                        });
                        const data = {
                            labels: labels,
                            datasets: [
                                {
                                    label: 'Total form',
                                    data: totalFormArr,
                                    backgroundColor: Utils.CHART_COLORS.blue,
                                },
                                {
                                    label: 'Completed form',
                                    data: completedFormArr,
                                    backgroundColor: Utils.CHART_COLORS.green,
                                },
                                // {
                                //     label: 'Incompleted form',
                                //     data: incompletedFormArr,
                                //     backgroundColor: Utils.CHART_COLORS.orange,
                                // },
                            ]
                        };

                        // Konfigurasi Chart.js
                        const config = {
                            type: 'bar',
                            data: data,
                            options: {
                                plugins: {
                                    title: {
                                        display: true,
                                        text: 'Form Cutting'
                                    },
                                    datalabels: {
                                        color: '#fff', // Warna teks
                                        formatter: (value, context) => {
                                            if (value === 0) {
                                                return null;
                                            }
                                            const label = context.chart.data.labels[context.dataIndex];
                                            const datasetLabel = context.dataset.label;
                                            return value;
                                        },
                                        font: {
                                            weight: 'bold',
                                            size: 14 // Ukuran font
                                        }
                                    },
                                    legend: {
                                        position: 'top', // Posisi legend
                                    }
                                },
                                responsive: true,
                            },
                            plugins: [ChartDataLabels],
                        };

                        // Membuat chart
                        const ctx = document.getElementById('myChart').getContext('2d');
                        myChart = new Chart(ctx, config);
                        const totalForms = totalCompleted + totalIncompleted;
                        const completedPercentage = totalForms > 0 ? (totalCompleted / totalForms * 100)
                            .toFixed(2) : 0;
                        const incompletedPercentage = totalForms > 0 ? (totalIncompleted / totalForms * 100)
                            .toFixed(2) : 0;
                        const doughnutData = {
                            labels: ['Completed', 'Incompleted'],
                            datasets: [{
                                data: [completedPercentage, incompletedPercentage],
                                backgroundColor: [Utils.CHART_COLORS.green, Utils.CHART_COLORS
                                    .orange
                                ],
                            }]
                        };

                        const doughnutConfig = {
                            type: 'doughnut',
                            data: doughnutData,
                            options: {
                                plugins: {
                                    title: {
                                        display: true,
                                        text: 'Progress (%)'
                                    },
                                    datalabels: { // Plugin untuk menampilkan label
                                        color: '#fff', // Warna teks label
                                        formatter: (value, context) => {
                                            return `${value}%`; // Format label sebagai persentase
                                        },
                                        font: {
                                            weight: 'bold',
                                            size: 14 // Ukuran font label
                                        },
                                        anchor: 'center', // Posisi label
                                        align: 'center', // Penyelarasan label
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(context) {
                                                const label = context.label || '';
                                                const value = context.raw || 0;
                                                return `${label}: ${value}%`;
                                            }
                                        }
                                    },
                                    legend: {
                                        position: 'top',
                                    }
                                },
                                responsive: true,
                                aspectRatio: 1,
                            },
                            plugins: [ChartDataLabels],
                        };

                        // Membuat chart doughnut
                        if (myDoughnutChart) {
                            myDoughnutChart.destroy();
                            myDoughnutChart = null;
                        }
                        const ctxDoughnut = document.getElementById('myDoughnutChart').getContext('2d');
                        myDoughnutChart = new Chart(ctxDoughnut, doughnutConfig);

                        document.getElementById("loading-cutting-form").classList.add("d-none");

                        // updateChartData();
                    }
                },
                error: function(jqXHR) {
                    let res = jqXHR.responseJSON;
                    iziToast.error({
                        title: 'Error',
                        message: res.message,
                        position: 'topCenter'
                    });
                    document.getElementById("loading-cutting-form").classList.add("d-none");
                }
            });
        }

        // Mendengarkan channel dinamis
        let channelName = `cutting-chart-channel-${mejaId}-${tglPlan}`;
        window.Echo.channel(channelName)
            .listen('.UpdatedCuttingEvent', (data) => {
                console.log("Data received:", data.data[0]);
                updateChartData(data.data[0]);
            });

        function updateChartData(data) {
            datatableDetailFormReload();

            if (myChart && myDoughnutChart && data) {
                // Cek apakah data yang diterima valid
                if (data.completed_form !== undefined && data.incomplete_form !== undefined && data.total_form !==
                    undefined) {

                    // Update "Completed form" dan "Total form" pada Bar Chart
                    const completedForm = parseInt(data.completed_form);
                    const totalForm = parseInt(data.total_form);
                    const incompleteForm = parseInt(data.incomplete_form);

                    // Update Doughnut Chart
                    const totalForms = completedForm + incompleteForm;
                    const completedPercentage = totalForms > 0 ? (completedForm / totalForms * 100).toFixed(2) : 0;
                    const incompletedPercentage = totalForms > 0 ? (incompleteForm / totalForms * 100).toFixed(2) : 0;

                    myDoughnutChart.data.datasets[0].data = [completedPercentage, incompletedPercentage];
                    myDoughnutChart.update(); // Update Doughnut Chart

                    // Perbarui Bar Chart
                    myChart.data.datasets[0].data = [totalForm]; // Total form
                    myChart.data.datasets[1].data = [completedForm]; // Completed form
                    myChart.update(); // Update Bar Chart

                    console.log(
                        `Updated data for ${data.no_meja}: total_form = ${data.total_form}, completed_form = ${data.completed_form}`
                    );
                }
            }
        }
    </script>
@endsection
