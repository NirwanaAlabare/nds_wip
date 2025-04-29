@extends('layouts.index')

@section('custom-link')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">

    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <div class="p-2 bd-highlight">
                    <h5 class="card-title fw-bold mb-0 text-center">Dashboard Pallet</h5>
                </div>
            </div>
            <div class="card-body">
                <center>
                    <div id="chart" style="height: 100px; width: 100%;"></div>
                </center>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-12 col-sm-6 col-md-4">
                    <div class="info-box">
                        <span class="info-box-icon bg-secondary bg-gradient elevation-1"><i
                                class="fas fa-exchange-alt"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Karton Non Lokasi</span>
                            <span class="info-box-number">
                                <label id="tot_karton_non" id="tot_karton_non">0</label>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-4">
                    <div class="info-box">
                        <span class="info-box-icon bg-warning bg-gradient  elevation-1"><i class="fas fa-dolly"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Karton Lokasi</span>
                            <span class="info-box-number">
                                <label id="tot_karton_lokasi" id="tot_karton_lokasi">0</label>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-4">
                    <div class="info-box">
                        <span class="info-box-icon bg-success elevation-1"><i class="fas fa-dolly-flatbed"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Karton</span>
                            <span class="info-box-number">
                                <label id="tot_karton" id="tot_karton">0</label>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <div class="p-2 bd-highlight">
                    <h5 class="card-title fw-bold mb-0 text-center">Tracking</h5>
                </div>
            </div>
        </div>


        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="form-label"><small><b>Buyer</b></small></label>
                        <select class="form-control select2bs4 form-control-sm" id="cbobuyer" name="cbobuyer"
                            style="width: 100%;" onchange="getws();getpo();">
                            <option selected="selected" value="" disabled="true">Pilih Buyer</option>
                            @foreach ($data_buyer as $databuyer)
                                <option value="{{ $databuyer->isi }}">
                                    {{ $databuyer->tampil }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label class="form-label"><small><b>WS</b></small></label>
                        <select class='form-control select2bs4 form-control-sm rounded' style='width: 100%;' name='cbows'
                            id='cbows'></select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="form-label"><small><b>PO</b></small></label>
                        <select class='form-control select2bs4 form-control-sm rounded' style='width: 100%;' name='cbopo'
                            id='cbopo'></select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label class="form-label"><small><b>No. Karton</b></small></label>
                        <input type="text" class="form-control" id="txtctn" name="txtctn" placeholder="Contoh 1 - 10"
                            style="width: 100%;">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <div class="input-group">
                            <a onclick="dataTableReload()" class="btn btn-outline-primary position-relative btn-sm">
                                <i class="fas fa-search fa-sm"></i>
                                Cari
                            </a>
                        </div>
                        </a>
                    </div>
                </div>
            </div>



            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-striped -wrap w-100 text-nowrap">
                    <thead class="table-success">
                        <tr style='text-align:center; vertical-align:middle'>
                            <th>Buyer</th>
                            <th>WS</th>
                            <th>Color</th>
                            <th>Size</th>
                            <th>Dest</th>
                            <th>PO</th>
                            <th>No Karton</th>
                            <th>Notes</th>
                            <th>Qty</th>
                            <th>Lokasi</th>
                            <th>Tgl. Shipment</th>
                        </tr>
                    </thead>
                    {{-- <tfoot>
                        <tr>
                            <th colspan="4"></th>
                            <th> <input type = 'text' class="form-control form-control-sm" style="width:75px" readonly
                                    id = 'total_qty'> </th>
                            <th></th>
                        </tr>
                    </tfoot> --}}
                </table>
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
    <script src="{{ asset('plugins/apexcharts/apexcharts.min.js') }}"></script>
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        // Select2 Autofocus
        $(document).on('select2:open', () => {
            document.querySelector('.select2-search__field').focus();
        });

        // Initialize Select2 Elements
        $('.select2').select2();

        // Initialize Select2BS4 Elements
        $('.select2bs4').select2({
            theme: 'bootstrap4',
        });
    </script>
    <script>
        function notif() {
            alert("Maaf, Fitur belum tersedia!");
        }
    </script>
    <script>
        $(document).ready(() => {
            gettot();
            $("#cbobuyer").val('').trigger('change');
            $("#txtctn").val('');
            dataTableReload();
        });

        $.ajax({
            url: '{{ route('get_data_dashboard_fg_ekspedisi') }}',
            type: 'get',
            dataType: 'json',
            success: function(res) {

                let newData = res.map(function(element) {
                    return {
                        name: element.x, // assuming 'x' is the label
                        data: element.y // assuming 'y' is the value
                    };
                });
                // console.log(newData);
                chart.updateOptions({
                    // series: newData.map,
                    series: newData.map(function(element) {
                        return element.data;
                    }),
                    labels: newData.map(function(element) {
                        return element.name;
                    })
                });
            },
            error: function(jqXHR) {
                // error handling code
            }
        });

        var options = {
            series: [100],
            chart: {
                width: 380,
                type: 'pie',
            },
            labels: [''],
            responsive: [{
                breakpoint: 480,
                options: {
                    chart: {
                        width: 200
                    },
                    legend: {
                        position: 'top'
                    }
                }
            }]
        };

        var chart = new ApexCharts(document.querySelector("#chart"), options);
        chart.render();

        function dataTableReload() {
            datatable.ajax.reload();
        }


        function gettot() {
            let dateFilter = $('#tgl-filter').val();
            $.ajax({
                url: '{{ route('show_tot_dash_fg_ekspedisi') }}',
                method: 'get',
                data: {
                    dateFilter: dateFilter
                },
                dataType: 'json',
                success: function(response) {
                    document.getElementById('tot_karton_non').innerHTML = response.tot_karton_non;
                    document.getElementById('tot_karton_lokasi').innerHTML = response.tot_karton_lok;
                    document.getElementById('tot_karton').innerHTML = response.tot_karton;
                },
                error: function(request, status, error) {
                    alert(request.responseText);
                },
            });
        };


        // // Truncate a string
        // function strtrunc(str, max, add) {
        //     add = add || '...';
        //     return (typeof str === 'string' && str.length > max ? str.substring(0, max) + add : str);
        // };

        $('#datatable thead tr').clone(true).appendTo('#datatable thead');
        $('#datatable thead tr:eq(1) th').each(function(i) {
            var title = $(this).text();
            $(this).html('<input type="text" class="form-control form-control-sm"/>');
            $('input', this).on('keyup change', function() {
                if (datatable.column(i).search() !== this.value) {
                    datatable
                        .column(i)
                        .search(this.value)
                        .draw();
                }
            });
        });

        let datatable = $("#datatable").DataTable({
            // "footerCallback": function(row, data, start, end, display) {
            //     var api = this.api(),
            //         data;

            //     // converting to interger to find total
            //     var intVal = function(i) {
            //         return typeof i === 'string' ?
            //             i.replace(/[\$,]/g, '') * 1 :
            //             typeof i === 'number' ?
            //             i : 0;
            //     };

            //     // computing column Total of the complete result
            //     var sumTotal = api
            //         .column(4)
            //         .data()
            //         .reduce(function(a, b) {
            //             return intVal(a) + intVal(b);
            //         }, 0);

            //     // Update footer by showing the total with the reference of the column index
            //     $(api.column(0).footer()).html('Total');
            //     $(api.column(4).footer()).html(sumTotal);
            // },
            ordering: false,
            processing: true,
            serverSide: true,
            paging: false,
            searching: true,
            scrollY: '300px',
            scrollX: '300px',
            scrollCollapse: true,
            ajax: {
                url: '{{ route('get_detail_dashboard_ekspedisi') }}',
                data: function(d) {
                    d.buyer = $('#cbobuyer').val();
                    d.ws = $('#cbows').val();
                    d.po = $('#cbopo').val();
                    d.no_karton = $('#txtctn').val();
                },
            },
            columns: [{
                    data: 'buyer'

                }, {
                    data: 'ws'
                },
                {
                    data: 'color'
                },
                {
                    data: 'size'
                },
                {
                    data: 'dest'
                },
                {
                    data: 'po'
                },
                {
                    data: 'no_carton'
                },
                {
                    data: 'notes'
                },
                {
                    data: 'qty'
                },
                {
                    data: 'lokasi'
                },
                {
                    data: 'tgl_shipment'
                },
            ],
            // columnDefs: [{
            //     'targets': 5,
            //     'render': function(data, type, full, meta) {
            //         if (type === 'display') {
            //             data = strtrunc(data, 12);
            //         }

            //         return data;
            //     }
            // }]
        });

        function getws() {
            let cbobuyer = $('#cbobuyer').val();
            let html = $.ajax({
                type: "GET",
                url: '{{ route('getws_dashboard_ekspedisi') }}',
                data: {
                    cbobuyer: cbobuyer
                },
                async: false
            }).responseText;
            if (html != "") {
                $("#cbows").html(html);
            }
        };

        function getpo() {
            let cbobuyer = $('#cbobuyer').val();
            let html = $.ajax({
                type: "GET",
                url: '{{ route('getpo_dashboard_ekspedisi') }}',
                data: {
                    cbobuyer: cbobuyer
                },
                async: false
            }).responseText;
            if (html != "") {
                $("#cbopo").html(html);
            }
        };
    </script>
@endsection
