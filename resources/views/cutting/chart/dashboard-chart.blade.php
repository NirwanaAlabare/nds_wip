@extends('layouts.index')

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
    <link rel="stylesheet" href="{{ asset('plugins/apexcharts/apexcharts.css') }}">
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
    }

    .PillList-item input[type="checkbox"] {
    display: none;
    }

    .PillList-item input[type="checkbox"]:checked + .PillList-label {
    background-color:rgb(0, 0, 0);
    border: 1px solid rgb(0, 0, 0);
    color: #fff;
    padding: 7px 15px;
    }
    .PillList-label {
    border: 1px solid rgb(144, 144, 144);
    border-radius: 7px;
    color:rgb(37, 37, 37);
    display: flex; 
    align-items: center;
    padding: 7px 15px;
    text-decoration: none;
    cursor: pointer;
    flex-direction: row;
    }

    .PillList-item
    input[type="checkbox"]:checked
    + .PillList-label
    .Icon--checkLight {
    display: inline-block;
    }

    .PillList-item input[type="checkbox"]:checked + .PillList-label .Icon--addLight,
    .PillList-label .Icon--checkLight,
    .PillList-children {
    display: none;
    }

    .PillList-label .Icon {
    width: 12px;
    height: 12px;
    margin: 0 0 0 20px;
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
      margin-bottom: 2rem;
    }

    .header-title {
      font-size: 1.5rem;
      font-weight: bold;
      margin-bottom: 0.5rem;
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

    .grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 1rem;
    }

    @media (min-width: 768px) {
      .grid {
        grid-template-columns: repeat(3, 1fr);
      }
    }

    @media (min-width: 1024px) {
      .grid {
        grid-template-columns: repeat(5, 1fr);
      }
    }

    #selected-date {
    color: black; 
    font-weight: bold; 
}
    </style>
@endsection

@section('content')
  <div>
    <div id="realtimeUpdateWrap"></div>
  <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-evenly" style="gap: 20px">
                <div class="card" style="width: 70%; height: 340px">
                    <div class="card-body">
                        <div class="header">
                        <div class="header-top">
                            <h1 class="header-title">Dashboard Cutting Chart Progress</h1>
                        </div>
                        <div class="mb-1">
                            <input type="date" class='form-control' id='cutting-form-date-filter' value="{{ date('Y-m-d') }}">
                        </div>
                            <p class="description">Laporan progress cutting tanggal <strong id="selected-date">{{ date('Y-m-d') }}</strong></p>
                        </div>
                        <div class="item-checklist-box col-12 col-md-6 col-lg-12">
                            <label class="PillList-item">
                                <input type="checkbox" name="feature" value="1">
                                <span class="PillList-label">
                                </span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="wrapperDoughnut">
                    <div class="card" >
                        <div class="card-body">
                                <div class="myDoughnutChartDiv">
                                <canvas id="myDoughnutChart" width="50" height="50"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="d-none mb-3" id="loading-cutting-form">
                <div class="loading-container">
                    <div class="loading"></div>
                </div>
            </div>
            <div class="card" >
                <div class="card-body">
                    <canvas id="myChart" width="1000" height="450"></canvas>
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
    <script src="{{ asset('plugins/datatables-rowgroup/js/dataTables.rowGroup.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-rowgroup/js/rowGroup.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-rowsgroup/dataTables.rowsGroup.js') }}"></script>
    <!-- Apex Charts -->
    <script src="{{ asset('plugins/apexcharts/apexcharts.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>



    <!-- Chart.JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.min.js" integrity="sha512-L0Shl7nXXzIlBSUUPpxrokqq4ojqgZFQczTYlGjzONGTDAcLremjwaWv5A+EDLnxhQzY5xUZPWLOLqYRkY0Cbw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

  <!-- SOCKET.IO configuration -->
    <script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
    <script> window.laravel_echo_port='{{env("LARAVEL_ECHO_PORT")}}';</script>
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
    let currentChannel = null;
 
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
    
    $(document).ready(async function () {
            const currentDate = $("#cutting-form-date-filter").val() || new Date().toISOString().split('T')[0];
            await loadMejaCutting([]); 
            await loadCuttingFormChart([], currentDate); 

            const dateInput = document.getElementById('cutting-form-date-filter');
            const selectedDateElement = document.getElementById('selected-date'); 

            function updateDescription() {
                const selectedDate = dateInput.value;  
                selectedDateElement.textContent = formatDate(selectedDate); 
            }
            dateInput.addEventListener('input', updateDescription);

            updateDescription();

           setupChannel(currentDate);
    });



    function generateCheckboxes(noMejaId, selectedCheckboxesIds) {


        const checkboxContainer = document.querySelector('.item-checklist-box');
        const currentDate = $("#cutting-form-date-filter").val();
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
            checkboxInput.disabled = true;

            // Setel status checkbox jika sebelumnya sudah dipilih
            if (selectedCheckboxes.includes(meja)) {
                checkboxInput.checked = true;
            }

            // Membuat label untuk checkbox
            const labelSpan = document.createElement('span');
            labelSpan.classList.add('PillList-label');
            labelSpan.textContent = meja.toUpperCase().replace('_', ' ');

            // Menambahkan ikon plus
            const iconSpan = document.createElement('span');
            iconSpan.classList.add('Icon', 'Icon--plus', 'Icon--smallest');
            const icon = document.createElement('i');
            icon.classList.add('fa', 'fa-plus'); // Ikon plus
            iconSpan.appendChild(icon);

            // Menambahkan event listener pada ikon plus untuk mengarahkan ke halaman lain
            iconSpan.addEventListener('click', function() {
                const url ='{{ route('dashboard-chart') }}/' + meja +'?tgl_plan=' + currentDate;
                window.location.href =  url;
            });

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
        document.getElementById("loading-cutting-form").classList.remove("d-none");
        const checkboxContainer = document.querySelector('.item-checklist-box');
    
        if (!checkboxContainer) {
            return;
        }

        const checkboxes = checkboxContainer.querySelectorAll('input[type="checkbox"]');
        checkboxes.forEach(checkbox => {
            const icon = checkbox.parentElement.querySelector('.Icon i');
            if (icon) {
                icon.classList.remove('fa-plus'); 
                icon.classList.add('fa-spinner', 'fa-spin'); 
            }
            checkbox.disabled = true; 
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
                                const formattedMeja = item.no_meja
                                    ? item.no_meja.toUpperCase().replace('_', ' ')
                                    : 'UNKNOWN';
                                noMejaId.push(item.no_meja ? item.no_meja: 0);
                                mejaArr.push(formattedMeja);
                                totalFormArr.push(item.total_form ? item.total_form : 0 );
                                completedFormArr.push(item.completed_form ? parseInt(item.completed_form) : 0 );
                                incompletedFormArr.push(item.incomplete_form ? parseInt(item.incomplete_form) : 0 );

                                totalCompleted += item.completed_form ? parseInt(item.completed_form) : 0;
                                totalIncompleted += item.incomplete_form ? parseInt(item.incomplete_form) : 0;
                            });
                          
                            const checkboxesAfterLoad = checkboxContainer.querySelectorAll('input[type="checkbox"]');
                            checkboxesAfterLoad.forEach(checkbox => {
                                const icon = checkbox.parentElement.querySelector('.Icon i');
                                if (selectedCheckboxes.includes(checkbox.value)) {
                                    checkbox.checked = true;
                                }
                                icon.classList.remove('fa-spinner', 'fa-spin');
                                icon.classList.add('fa-plus'); 
                                checkbox.disabled = false;
                            });
                            const Utils = {
                                months: function({ count }) {
                                    const months = mejaArr;
                                    return months.slice(0, count);
                                },
                                numbers: function({ count, min, max }) {
                                    return Array.from({ length: count }, () => Math.floor(Math.random() * (max - min + 1)) + min);
                                },
                                CHART_COLORS: {
                                    blue: 'rgb(54, 162, 235)',
                                    green: '#4CAF50',
                                    orange: '#FF5733'
                                }
                                };

                            // Data Chart
                            const DATA_COUNT = mejaArr.length;
                            const NUMBER_CFG = { count: DATA_COUNT, min: -100, max: 100 };

                            const labels = Utils.months({ count: DATA_COUNT });
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
                                    }                                
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
                                            text: 'Grafik dashboard cutting'
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
                                    scales: {
                                        x: {
                                            stacked: true,
                                        },
                                        y: {
                                            stacked: true
                                        }
                                    }
                                },
                                plugins: [ChartDataLabels],
                            };
                         
                            // Membuat chart
                            const ctx = document.getElementById('myChart').getContext('2d');
                            myChart = new Chart(ctx, config);
                            const totalForms = totalCompleted + totalIncompleted;
                            const completedPercentage = totalForms > 0 ? (totalCompleted / totalForms * 100).toFixed(2) : 0;
                            const incompletedPercentage = totalForms > 0 ? (totalIncompleted / totalForms * 100).toFixed(2) : 0;
                            const doughnutData = {
                                labels: ['Completed', 'Incompleted'],
                                datasets: [{
                                    data: [completedPercentage, incompletedPercentage],
                                    backgroundColor: [Utils.CHART_COLORS.green, Utils.CHART_COLORS.orange],
                                }]
                            };

                            const doughnutConfig = {
                                type: 'doughnut',
                                data: doughnutData,
                                options: {
                                    plugins: {
                                        title: {
                                            display: true,
                                             text: 'Komulatif Completed vs Incompleted (%)'
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
                                    }
                                }, error: function (jqXHR) {
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

    function loadMejaCutting(selectedCheckboxes) {
        document.getElementById("loading-cutting-form").classList.remove("d-none");
        return $.ajax({
            url: '{{ route('meja-dashboard-cutting') }}',
            type: 'get',
            data: {
                date: $("#cutting-form-date-filter").val()
            },
            dataType: 'json',
            success: async function(res) {
                if (res) {
                    noMejaId = [];
                    res.forEach(item => {
                        noMejaId.push(item.no_meja ? item.no_meja: 0);
                    });
                    const uniqueNoMejaId = Array.from(new Set(noMejaId));
                    generateCheckboxes(uniqueNoMejaId, selectedCheckboxes);
                    document.getElementById("loading-cutting-form").classList.add("d-none");
                } 
            }, 
            error: function (jqXHR) {
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

    $('#cutting-form-date-filter').on("change", async function () {
                const selectedDate  = $("#cutting-form-date-filter").val();
                await loadMejaCutting([])
                await loadCuttingFormChart([],selectedDate )
                const checkboxContainer = document.querySelector('.item-checklist-box');
                checkboxContainer.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
                    checkbox.checked = false; // Uncheck semua checkbox
                });
                setupChannel(selectedDate);
    });

    const checkboxContainer = document.querySelector('.item-checklist-box');

    checkboxContainer.addEventListener('click', function (e) {
        const currentDate = $("#cutting-form-date-filter").val();

        if (e.target && e.target.closest('.Icon--plus')) {
            e.preventDefault(); // Cegah default action pada label
            const iconSpan = e.target.closest('.Icon--plus');
            const checkboxLabel = iconSpan.closest('.PillList-item');
            const meja = checkboxLabel.querySelector('input[type="checkbox"]').value;

            // Arahkan ke halaman baru
            const url = '{{ route('dashboard-chart') }}/' + meja + '?tgl_plan=' + currentDate;
            window.location.href = url;
            return; // Hentikan eksekusi lebih lanjut
        }
    });

    checkboxContainer.addEventListener('change', function (e) {
        const currentDate = $("#cutting-form-date-filter").val();
        // Jika yang diubah adalah checkbox
        if (e.target && e.target.name === 'feature') {
            // Ambil semua checkbox yang dicentang
            const selectedCheckboxes = Array.from(
                checkboxContainer.querySelectorAll('input[name="feature"]:checked')
            ).map(checkbox => checkbox.value);
            // Kirim array string ke API untuk update chart
            if (selectedCheckboxes.length > 0) {
                loadCuttingFormChart(selectedCheckboxes, currentDate); // Kirim array string
            } else {
                loadCuttingFormChart(selectedCheckboxes, currentDate); // Atau lakukan sesuatu jika semua uncheck
            }
        }
    });

    $('#cutting-form-date-filter').on('focus', function() {
        this.showPicker(); 
    });

    // Fungsi untuk setup dan bind channel
    function setupChannel(date) {
        if (currentChannel) {
            currentChannel.unsubscribe(); 
        }

        let channelName = `cutting-chart-channel-all-${date}`;

        currentChannel = window.Echo.channel(channelName)
            .listen('.UpdatedAllCuttingEvent', (data) => {
                console.log("Data received:", data.data);
                updateChartDataAll(data.data); 
            });
    }

    function updateChartDataAll(dataArray) {
            if (myChart && myDoughnutChart && Array.isArray(dataArray)) {
                // Inisialisasi array untuk menyimpan data Total Form dan Completed Form
                const totalFormArr = [];
                const completedFormArr = [];
                const incompletedFormArr = [];

                let totalCompleted = 0;
                let totalIncompleted = 0;

                const existingData = myChart.data.labels;
                let arr_curr_check_value= [];
                existingData.forEach((meja, index) => {
                    arr_curr_check_value.push(meja.toLowerCase().replace(' ', '_'));
                });

                const filteredData = dataArray.filter(item => arr_curr_check_value.includes(item.no_meja));
                // Iterasi data yang diterima
                filteredData.forEach((data) => {
                    if (data.total_form !== undefined && data.completed_form !== undefined && data.incomplete_form !== undefined) {
                        const totalForm = parseInt(data.total_form);
                        const completedForm = parseInt(data.completed_form);
                        const incompleteForm = parseInt(data.incomplete_form);

                        // Tambahkan ke array masing-masing
                        totalFormArr.push(totalForm);
                        completedFormArr.push(completedForm);
                        incompletedFormArr.push(incompleteForm);

                        // Hitung total Completed dan Incompleted
                        totalCompleted += completedForm;
                        totalIncompleted += incompleteForm;
                    }
                });

                // Update Doughnut Chart
                const totalForms = totalCompleted + totalIncompleted;
                const completedPercentage = totalForms > 0 ? (totalCompleted / totalForms * 100).toFixed(2) : 0;
                const incompletedPercentage = totalForms > 0 ? (totalIncompleted / totalForms * 100).toFixed(2) : 0;

                myDoughnutChart.data.datasets[0].data = [completedPercentage, incompletedPercentage];
                myDoughnutChart.update();

                // Update Bar Chart
                myChart.data.datasets[0].data = totalFormArr; // Total Form
                myChart.data.datasets[1].data = completedFormArr; // Completed Form
                myChart.update();
            }
    }
</script>


  @endsection