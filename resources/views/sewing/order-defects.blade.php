@extends('layouts.index')

@section('custom-link')
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
    <!-- Apex Charts -->
    <link rel="stylesheet" href="{{ asset('plugins/apexcharts/apexcharts.css') }}">
@endsection

@section('content')
    <div class="container-fluid">
        <h3 class="my-3 text-sb text-center fw-bold">Pareto Chart</h3>
        <div class="card">
            <div class="card-body">
                <div class="d-flex gap-3 justify-content-between mb-3">
                    <div class="d-flex gap-3 justify-content-start align-items-end">
                        <div>
                            <label>Buyer</label>
                            <select class="form-select form-select-sm" name="supplier" id="supplier">
                                <option value="all">SEMUA</option>
                                @foreach ($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <select class="form-select form-select-sm" name="type" id="type">
                                <option value="qc" selected>QC</option>
                                <option value="packing">PACKING</option>
                            </select>
                        </div>
                    </div>
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
                </div>
                <div id="chart"></div>
            </div>
        </div>
    </div>
@endsection

@section('custom-script')
    <!-- Select2 -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <!-- Apex Charts -->
    <script src="{{ asset('plugins/apexcharts/apexcharts.min.js') }}"></script>

    <script>
        function autoBreak(label) {
            const maxLength = 5;
            const lines = [];

            if (label) {
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
            }

            return lines;
        }

        document.addEventListener('DOMContentLoaded', () => {
            // bar chart options
            var options = {
                chart: {
                    height: 650,
                    type: 'bar',
                },
                plotOptions: {
                    bar: {
                        horizontal: false,
                        dataLabels: {
                            position: 'top',
                        },
                        colors: {
                            ranges: [
                                {
                                    from: 500,
                                    to: 99999,
                                    color: '#333'
                                },{
                                    from: 60,
                                    to: 499,
                                    color: '#d33141'
                                },{
                                    from: 30,
                                    to: 59,
                                    color: '#ff971f'
                                },{
                                    from: 0,
                                    to: 15,
                                    color: '#12be60'
                                }
                            ],
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
                    formatter: function (val, opts) {
                        return val.toLocaleString()
                    },
                    offsetY: -30
                },
                series: [],
                xaxis: {
                    labels: {
                        show: true,
                        hideOverlappingLabels: false,
                        showDuplicates: false,
                        trim: false,
                        style: {
                            fontSize: '12px',
                            fontFamily: 'Helvetica, Arial, sans-serif',
                            fontWeight: 600,
                            cssClass: 'apexcharts-xaxis-label',
                        },
                    }
                },
                title: {
                    text: 'Supplier',
                    align: 'center',
                    style: {
                        fontSize:  '18px',
                        fontWeight:  'bold',
                        fontFamily:  undefined,
                        color:  '#263238'
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
            function getOrderDefectData(idSupplier, namaSupplier, dari, sampai, type) {
                $.ajax({
                    url: '{{ url('order-defects') }}/'+idSupplier+'/'+dari+'/'+sampai+'/'+type,
                    type: 'get',
                    dataType: 'json',
                    success: function(res) {
                        let totalDefect = 0;
                        let dataArr = [];

                        res.forEach(element => {
                            totalDefect += element.total_defect;
                            dataArr.push({'x' : autoBreak(element.defect_type), 'y' : element.total_defect});
                        });

                        let cumulativeTotalDefect = cumulativeSplitNumber(totalDefect);

                        chart.updateSeries([{
                            data: dataArr,
                            name: "Total Defect"
                        }], true);

                        chart.updateOptions({
                            title: {
                                text: namaSupplier,
                                align: 'center',
                                style: {
                                    fontSize:  '18px',
                                    fontWeight:  'bold',
                                    fontFamily:  undefined,
                                    color:  '#263238'
                                },
                            },
                            subtitle: {
                                text: [dari+' / '+sampai, 'Total Defect : '+totalDefect.toLocaleString()],
                                align: 'center',
                                style: {
                                    fontSize:  '13px',
                                    fontFamily:  undefined,
                                    color:  '#263238'
                                },
                            },
                            plotOptions: {
                                bar: {
                                    horizontal: false,
                                    dataLabels: {
                                        position: 'top',
                                    },
                                    colors: {
                                        ranges: [
                                            {
                                                from: cumulativeTotalDefect[2]+1,
                                                to: cumulativeTotalDefect[3],
                                                color: '#333'
                                            },
                                            {
                                                from: cumulativeTotalDefect[1]+1,
                                                to: cumulativeTotalDefect[2],
                                                color: '#d33141'
                                            },
                                            {
                                                from: cumulativeTotalDefect[0]+1,
                                                to: cumulativeTotalDefect[1],
                                                color: '#ff971f'
                                            },
                                            {
                                                from: 0,
                                                to: cumulativeTotalDefect[0],
                                                color: '#12be60'
                                            }
                                        ],
                                        backgroundBarColors: [],
                                        backgroundBarOpacity: 1,
                                        backgroundBarRadius: 0,
                                    },
                                }
                            },
                        });
                    }, error: function (jqXHR) {
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

            function updateBuyerList() {
                $.ajax({
                    url: 'order-defects',
                    type: 'get',
                    data: {
                        dateFrom : $('#date-from').val(),
                        dateTo : $('#date-to').val(),
                    },
                    success: function(res) {
                        // Clear options
                        $("#supplier").html("");

                        let initOption = new Option("SEMUA", "all", true, true);
                        $('#supplier').append(initOption);

                        res.forEach((element, index) => {
                            console.log(element, index);

                            if ($('#supplier').find("option[value='"+element.id+"']").length > 0) {
                                $('#supplier').val(element.id);
                            } else {
                                // Create a DOM Option and pre-select by default
                                var newOption = new Option(element.name, element.id, true, true);
                                // Append it to the select
                                if (index == 0) {
                                    $('#supplier').append(newOption).trigger('change');
                                } else {
                                    $('#supplier').append(newOption);
                                }
                            }
                        });
                    },
                    error: function (jqXHR) {
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

            // select2
            $('#supplier').select2({
                theme: "bootstrap4",
            });

            $('#type').select2({
                theme: "bootstrap4",
            });

            // initial fetch
            let today = new Date();
            let todayDate = ("0" + today.getDate()).slice(-2);
            let todayMonth = ("0" + (today.getMonth() + 1)).slice(-2);
            let todayYear = today.getFullYear();
            let todayFull = todayYear+'-'+todayMonth+'-'+todayDate;
            let twoWeeksBefore = new Date(new Date().setDate(new Date().getDate() - 14));
            let twoWeeksBeforeDate = ("0" + twoWeeksBefore.getDate()).slice(-2);
            let twoWeeksBeforeMonth = ("0" + (twoWeeksBefore.getMonth() + 1)).slice(-2);
            let twoWeeksBeforeYear = twoWeeksBefore.getFullYear();
            let twoWeeksBeforeFull = twoWeeksBeforeYear+'-'+twoWeeksBeforeMonth+'-'+twoWeeksBeforeDate;
            $('#date-to').val(todayFull);
            $('#date-from').val(twoWeeksBeforeFull);

            getOrderDefectData($('#supplier').val(), $('#supplier option:selected').text(), $('#date-from').val(), $('#date-to').val(), $('#type').val());

            // fetch on select supplier
            $('#supplier').on('select2:select', async function (e) {
                document.getElementById('loading').classList.remove('d-none');
                await getOrderDefectData(e.params.data.element.value, e.params.data.element.innerText, $('#date-from').val(), $('#date-to').val(), $('#type').val());
                document.getElementById('loading').classList.add('d-none');
            });

            // fetch on select type
            $('#type').on('select2:select', async function (e) {
                document.getElementById('loading').classList.remove('d-none');
                await getOrderDefectData($('#supplier').val(), $('#supplier option:selected').text(), $('#date-from').val(), $('#date-to').val(), e.params.data.element.value);
                document.getElementById('loading').classList.add('d-none');
            });

            // fetch on select date
            $('#date-from').change(function (e) {
                updateBuyerList();
                getOrderDefectData($('#supplier').val(), $('#supplier option:selected').text(), $('#date-from').val(), $('#date-to').val(), $('#type').val());
            });

            $('#date-to').change(function (e) {
                updateBuyerList();
                getOrderDefectData($('#supplier').val(), $('#supplier option:selected').text(), $('#date-from').val(), $('#date-to').val(), $('#type').val());
            });

            // fetch every 30 second
            setInterval(function(){
                getOrderDefectData($('#supplier').val(), $('#supplier option:selected').text(), $('#date-from').val(), $('#date-to').val(), $('#type').val());
            }, 30000)
        });

        function cumulativeSplitNumber(number) {
            const part1 = Math.floor(number * 0.05);
            const part2 = Math.floor(number * 0.10);
            const part3 = Math.floor(number * 0.20);
            const part4 = number - (part1 + part2 + part3); // Adjust to get the total sum

            return [part1, part1 + part2, part1 + part2 + part3, number];
        }
    </script>
@endsection
