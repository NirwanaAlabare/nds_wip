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

        <!-- Select2 -->
        <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
        <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

        <style>
            .tooltip-inner {
                text-align: left !important;
            }
        </style>
@endsection

@section('content')
    <div >
       <div class="container-fluid">
            <div class="card bg-light">
                <div class="card-body">

                <div class="col-md-12">
                <div class="form-group row">
                     <h3 class="card-title" style="padding-bottom:8px;"><b><u>Stock, In & Out Roll</u></b></h3>
                    <div class="col-md-10">
                    <div class="table-responsive" style="max-height: 370px;overflow-y: hidden;overflow-x: hidden;">
                        <table id="datatable" class="table table-bordered table-striped w-100" style="width: 100%;">
                            <thead>
                                <tr class="bg-dark">
                                    <th class="text-center" style="width: 10%">Kode Rak</th>
                                    <th class="text-center" style="width: 30%">Nama Rak</th>
                                    <th class="text-center" style="width: 10%">Kapasitas</th>
                                    <th class="text-center" style="width: 10%">Stok</th>
                                    <th class="text-center" style="width: 35%">Balance Kapasitas</th>
                                    <th class="text-center" style="width: 5%">Show</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                    </div>
                    <div class="col-md-2">
                        <div class="col-md-12">
                            <div class="card border-success mb-3" style="max-width: 22rem;">
                            <div class="card-header bg-info border-dark"><b style="font-size: 0.9rem;">Qty Stok</b>
                            </div>
                                <div class="card-body text-secondary">
                                @foreach ($tot_roll as $tr)
                                    <i class="fa-solid fa-warehouse fa-2xl" style=" font-size: 13px;text-align: center;"> {{ $tr->stok }}</i>
                                @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="card border-success mb-3" style="max-width: 22rem;">
                            <div class="card-header bg-success border-dark" onclick="showdata2('IN');"><b style="font-size: 0.9rem;">Qty In Today</b>
                            </div>
                                <div class="card-body text-secondary">
                                @foreach ($qty_in as $in)
                                    <i class="fa-solid fa-right-to-bracket" style=" font-size: 13px;text-align: center;"> {{ $in->qty_in }}</i>
                                @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="card border-success mb-3" style="max-width: 22rem;">
                            <div class="card-header bg-danger border-dark" onclick="showdata3('OUT');"><b style="font-size: 0.9rem;">Qty Out Today</b>
                            </div>
                                <div class="card-body text-secondary">
                                @foreach ($qty_out as $out)
                                    <i class="fa-solid fa-right-from-bracket" style=" font-size: 13px;text-align: center;"> {{ $out->qty_out }}</i>
                                @endforeach
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                </div>
                </div>

            </div>
        </div>
    </div>

    <div class="modal fade " id="modal_tblroll" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
        <div class="modal-header bg-dark text-light">
                    <h4 class="modal-title" id="modal_title1">11</h4>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
        <div class="modal-body">
            <div class="row">
                <div class="col-lg-12">
                <div class="table-responsive" style="height: 400px" id="table_modal">

                </div>
                </div>
            </div>
        </div>
    </div>
  </div>
</div>



<div class="modal fade " id="modal_rollin" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
        <div class="modal-header bg-success text-light">
                    <h4 class="modal-title" id="modal_title2"></h4>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
        <div class="modal-body">
            <div class="row">
                <div class="col-lg-12">
                <div class="table-responsive" style="height: 400px" id="table_modal2">

                </div>
                </div>
            </div>
        </div>
    </div>
  </div>
</div>


<div class="modal fade " id="modal_rollout" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
        <div class="modal-header bg-danger text-light">
                    <h4 class="modal-title" id="modal_title3"></h4>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
        <div class="modal-body">
            <div class="row">
                <div class="col-lg-12">
                <div class="table-responsive" style="height: 400px" id="table_modal3">

                </div>
                </div>
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
    <!-- Apex Charts -->
    <script src="{{ asset('plugins/apexcharts/apexcharts.min.js') }}"></script>

    <!-- Page specific script -->
    <script>
    let datatable = $("#datatable").DataTable({
        ordering: false,
        processing: true,
        serverSide: true,
        paging: true,
        searching: true,
        ajax: {
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '{{ route('dashboard-warehouse') }}',
            dataType: 'json',
            dataSrc: 'data',
            data: function(d) {
                // d.area = $('#area').val();
            },
        },
        columns: [{
                data: 'kode_lok'
            },
            {
                data: 'nama_lok'
            },
            {
                data: 'kapasitas'
            },
            {
                data: 'stok'
            },
            {
                data: 'balance'
            },
            {
                data: 'kode_lok'
            }

        ],
        columnDefs: [{
                targets: [4],
                render: (data, type, row, meta) => {
                    console.log(row);
                    if (row.balance > 90) {

                    return '<div class=" progress"><div class="progress-bar bg-danger progress-bar-striped progress-bar-animated" role="progressbar" style="width: '+data+'%" aria-valuenow="'+data+'" aria-valuemin="0" aria-valuemax="100">'+row.persentase+'</div></div>';
                    }else if(row.balance > 60 && row.balance <= 90){
                        return '<div class=" progress"><div class="progress-bar bg-warning progress-bar-striped progress-bar-animated" role="progressbar" style="width: '+data+'%" aria-valuenow="'+data+'" aria-valuemin="0" aria-valuemax="100">'+row.persentase+'</div></div>';
                    }else if(row.balance > 30 && row.balance <= 60){
                        return '<div class=" progress"><div class="progress-bar bg-info progress-bar-striped progress-bar-animated" role="progressbar" style="width: '+data+'%" aria-valuenow="'+data+'" aria-valuemin="0" aria-valuemax="100">'+row.persentase+'</div></div>';
                    }
                    else if(row.balance >= 0 && row.balance <= 30){
                        return '<div class=" progress"><div class="progress-bar bg-success progress-bar-striped progress-bar-animated" role="progressbar" style="width: '+data+'%" aria-valuenow="'+data+'" aria-valuemin="0" aria-valuemax="100">'+row.persentase+'</div></div>';
                    }
                }
            },
            {
                targets: [5],
                render: (data, type, row, meta) => {
                    return `<button type='button' class='btn btn-sm btn-info' onclick='showdata("` + data + `")'><i class="fa-solid fa-circle-info"></i></button>`;
                }
            }
        ]
    });

    // function dataTableReload() {
    //     datatable.ajax.reload();
    // }
</script>

<!-- <script type="text/javascript">
    $('#datatable').on('click', 'td:eq(0)', function(){
    $('#modal_tblroll').modal('show');
    // $('#txt_title').html('test');
});
</script> -->

<script type="text/javascript">
    function showdata(data) {
        return $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route("get-data-rak") }}',
                type: 'get',
                data: {
                    kode_lok: data,
                },
                success: function (res) {
                    if (res) {
                        $('#modal_tblroll').modal('show');
                        $('#modal_title1').html('DETAIL ' + data + ' FABRIC WAREHOUSE RACK');
                        document.getElementById('table_modal').innerHTML = res;
                        $("#tableshow").DataTable({
                            "responsive": true,
                            "autoWidth": false,
                        })
                    }
                }
            });
     }


     function showdata2(data) {
        return $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route("get-data-rak2") }}',
                type: 'get',
                data: {
                    kode_lok: data,
                },
                success: function (res) {
                    if (res) {
                        $('#modal_rollin').modal('show');
                        $('#modal_title2').html('DETAIL ' + data + ' FABRIC WAREHOUSE TODAY');
                        document.getElementById('table_modal2').innerHTML = res;
                        $("#tableshow2").DataTable({
                            "responsive": true,
                            "autoWidth": false,
                        })
                    }
                }
            });
     }


     function showdata3(data) {
        return $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route("get-data-rak3") }}',
                type: 'get',
                data: {
                    kode_lok: data,
                },
                success: function (res) {
                    if (res) {
                        $('#modal_rollout').modal('show');
                        $('#modal_title3').html('DETAIL ' + data + ' FABRIC WAREHOUSE TODAY');
                        document.getElementById('table_modal3').innerHTML = res;
                        $("#tableshow3").DataTable({
                            "responsive": true,
                            "autoWidth": false,
                        })
                    }
                }
            });
     }
</script>

    <script>
        $(function() {
            // $("#datatable").DataTable({
            //     "responsive": true,
            //     "autoWidth": false,
            // });

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

    @if ($page == 'dashboard-mut-karyawan')
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
                                totalEmployee += element.tot_orang;
                                dataArr.push({
                                    'x': autoBreak(element.line),
                                    'y': element.tot_orang
                                });
                            });

                            chart.updateSeries([{
                                name: "Karyawan Line",
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
                                    text: ['Total Orang : ' + totalEmployee.toLocaleString()],
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


                getLineData()

                setInterval(function() {
                    getLineData();
                }, 30000)
            });
        </script>
    @endif
@endsection
