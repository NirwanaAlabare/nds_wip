@if (!isset($page))
    @php
        $page = '';
    @endphp
@endif

@extends('layouts.index', ['page' => $page])

@section('custom-link')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
    <!-- Apex Charts -->
    <link rel="stylesheet" href="{{ asset('plugins/apexcharts/apexcharts.css') }}">

    @if ($page == 'dashboard-dc')
        <!-- Select2 -->
        <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
        <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

        <style>
            .tooltip-inner {
                text-align: left !important;
            }
        </style>
    @endif
@endsection

@section('content')
    <div style="{{ $page ? 'height: 100%;' : 'height: 75vh;' }}">
        @if ($page == 'dashboard-cutting')
            <div style="height: 75vh;"></div>
        @endif

        @if ($page == 'dashboard-stocker')
            <div style="height: 75vh;"></div>
        @endif

        @if ($page == 'dashboard-dc')
            <div style="height: 75vh;"></div>
        @endif

        <div class="container-fluid">
            <div class="card">
                <div class="card-body">
                    {{-- <div class="d-flex gap-3 justify-content-between mb-3">
                            <div class="d-flex gap-3">
                                <div>
                                    <label>Dari</label>
                                    <input class="form-control form-control-sm" type="date" id="date-from">
                                </div>
                                <div>
                                    <label>Sampai</label>
                                    <input class="form-control form-control-sm" type="date" id="date-to">
                                </div>
                            </div>
                        </div> --}}
                    <div id="chart"></div>
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

    <!-- Page specific script -->
    <script>
        $(function() {
            $("#datatable").DataTable({
                "responsive": true,
                "autoWidth": false,
            });

            $("#datatable-1").DataTable({
                "responsive": true,
                "autoWidth": false,
            })

            $("#datatable-2").DataTable({
                "responsive": true,
                "autoWidth": false,
            })

            $("#datatable-3").DataTable({
                "responsive": true,
                "autoWidth": false,
            })

            $("#datatable-4").DataTable({
                "responsive": true,
                "autoWidth": false,
            })
        });
    </script>
    <script>
        function autoBreak(label) {
            const maxLength = 5;
            const lines = [];

            for (let word of label.split(" ")) {
                if (lines.length == 0) {
                    lines.push(word);
                } else {
                    const i = lines.length - 1
                    const line = lines[i]

                    if (line.length + 1 + word.length <= maxLength) {
                        lines[i] = `${line} ${word}`
                    } else {
                        lines.push(word)
                    }
                }
            }

            return lines;
        }

        document.addEventListener('DOMContentLoaded', () => {
            // bar chart options
            var options = {
                chart: {
                    height: 550,
                    type: 'bar',
                },
                plotOptions: {
                    bar: {
                        horizontal: false,
                        dataLabels: {
                            position: 'top',
                        },
                        colors: {
                            ranges: [{
                                from: 0,
                                to: 100,
                                color: '#1640D6'
                            }],
                            backgroundBarColors: [],
                            backgroundBarOpacity: 1,
                            backgroundBarRadius: 0,
                        },
                    }
                },
                dataLabels: {
                    enabled: true,
                    style: {
                        colors: ['#333']
                    },
                    formatter: function(val, opts) {
                        return val.toLocaleString()
                    },
                    offsetY: -30
                },
                series: [],
                xaxis: {
                    labels: {
                        show: true,
                        rotate: 0,
                        rotateAlways: false,
                        hideOverlappingLabels: false,
                        showDuplicates: false,
                        trim: false,
                        minHeight: undefined,
                        style: {
                            fontSize: '12px',
                            fontFamily: 'Helvetica, Arial, sans-serif',
                            fontWeight: 600,
                            cssClass: 'apexcharts-xaxis-label',
                        },
                    }
                },
                title: {
                    text: 'Data Line ',
                    align: 'center',
                    style: {
                        fontSize: '18px',
                        fontWeight: 'bold',
                        fontFamily: undefined,
                        color: '#263238'
                    },
                },
                noData: {
                    text: 'Loading...'
                }
            }
            var chart = new ApexCharts(
                document.querySelector("#chart"),
                options
            );
            chart.render();

            // fetch order defect data function
            function getLineData() {
                $.ajax({
                    url: '{{ route('line-chart-data') }}',
                    type: 'get',
                    dataType: 'json',
                    success: function(res) {
                        let totalEmployee = 0;
                        let dataArr = [];
                        res.forEach(element => {
                            totalEmployee += element.tot_mesin;
                            dataArr.push({
                                'x': autoBreak(element.line),
                                'y': element.tot_mesin
                            });
                        });

                        chart.updateSeries([{
                            name: "Mesin Line",
                            data: dataArr
                        }], true);

                        chart.updateOptions({
                            title: {
                                text: "Data Line",
                                align: 'center',
                                style: {
                                    fontSize: '18px',
                                    fontWeight: 'bold',
                                    fontFamily: undefined,
                                    color: '#263238'
                                },
                            },
                            subtitle: {
                                // text: [dari+' / '+sampai, 'Total Orang : '+totalEmployee.toLocaleString()],
                                text: ['Total Mesin : ' + totalEmployee.toLocaleString()],
                                align: 'center',
                                style: {
                                    fontSize: '13px',
                                    fontFamily: undefined,
                                    color: '#263238'
                                },
                            }
                        });
                    },
                    error: function(jqXHR) {
                        let res = jqXHR.responseJSON;
                        console.error(res.message);
                        iziToast.error({
                            title: 'Error',
                            message: res.message,
                            position: 'topCenter'
                        });
                    }
                });
            }

            // initial fetch
            // let today = new Date();
            // let todayDate = ("0" + today.getDate()).slice(-2);
            // let todayMonth = ("0" + (today.getMonth() + 1)).slice(-2);
            // let todayYear = today.getFullYear();
            // let todayFull = todayYear+'-'+todayMonth+'-'+todayDate;
            // let twoWeeksBefore = new Date(new Date().setDate(new Date().getDate() - 14));
            // let twoWeeksBeforeDate = ("0" + twoWeeksBefore.getDate()).slice(-2);
            // let twoWeeksBeforeMonth = ("0" + (twoWeeksBefore.getMonth() + 1)).slice(-2);
            // let twoWeeksBeforeYear = twoWeeksBefore.getFullYear();
            // let twoWeeksBeforeFull = twoWeeksBeforeYear+'-'+twoWeeksBeforeMonth+'-'+twoWeeksBeforeDate;
            // $('#date-to').val(todayFull);
            // $('#date-from').val(twoWeeksBeforeFull);

            getLineData()

            // fetch on select supplier
            // $('#supplier').on('select2:select', function (e) {
            //     getOrderDefectData(e.params.data.element.value, e.params.data.element.innerText, $('#date-from').val(), $('#date-to').val());
            // });

            // fetch on select date
            // $('#date-from').change(function (e) {
            //     updateBuyerList();
            //     getOrderDefectData($('#supplier').val(), $('#supplier option:selected').text(), $('#date-from').val(), $('#date-to').val());
            // });

            // $('#date-to').change(function (e) {
            //     updateBuyerList();
            //     getOrderDefectData($('#supplier').val(), $('#supplier option:selected').text(), $('#date-from').val(), $('#date-to').val());
            // });

            // fetch every 30 second
            setInterval(function() {
                getLineData();
            }, 30000)
        });
    </script>
@endsection
