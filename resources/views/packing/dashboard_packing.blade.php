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
        <div class="card-body">
            <div class="row">
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-secondary bg-gradient elevation-1"><i
                                class="fas fa-exchange-alt"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Packing Line</span>
                            <span class="info-box-number">
                                <label id="tot_p_line" id="tot_p_line">0</label>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-warning bg-gradient  elevation-1"><i class="fas fa-dolly"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Transfer Garment</span>
                            <span class="info-box-number">
                                <label id="tot_trf_garment" id="tot_trf_garment">0</label>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-success elevation-1"><i class="fas fa-dolly-flatbed"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Central In</span>
                            <span class="info-box-number">
                                <label id="tot_central_in" id="tot_central_in">0</label>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-info elevation-1"><i class="fas fa-box-open"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Packing Out</span>
                            <span class="info-box-number">
                                <label id="tot_packing_out" id="tot_packing_out">0</label>
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
                    <h5 class="card-title fw-bold mb-0 text-center">Packing Line Output</h5>
                </div>
                <div class="p-2 bd-highlight"> <input type="date" class="form-control form-control-sm " id="tgl-filter"
                        name="tgl_filter" oninput="dataTableReload();gettot();" value="{{ date('Y-m-d') }}"></div>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-striped -wrap w-100">
                    <thead class="table-success">
                        <tr style='text-align:center; vertical-align:middle'>
                            <th width="10%">Line</th>
                            <th width="15%">WS</th>
                            <th width="40%">Color</th>
                            <th width="10%">Size</th>
                            <th width="10%">Qty</th>
                            <th width="15%">PO</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th colspan="4"></th>
                            <th> <input type = 'text' class="form-control form-control-sm" style="width:75px" readonly
                                    id = 'total_qty'> </th>
                            <th></th>
                        </tr>
                    </tfoot>
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
    <script>
        function notif() {
            alert("Maaf, Fitur belum tersedia!");
        }
    </script>
    <script>
        $(document).ready(() => {
            dataTableReload();
            gettot();
        });

        function dataTableReload() {
            datatable.ajax.reload();
        }


        function gettot() {
            let dateFilter = $('#tgl-filter').val();
            $.ajax({
                url: '{{ route('show_tot_dash_packing') }}',
                method: 'get',
                data: {
                    dateFilter: dateFilter
                },
                dataType: 'json',
                success: function(response) {
                    document.getElementById('tot_p_line').innerHTML = response.tot_p_line;
                    document.getElementById('tot_trf_garment').innerHTML = response.tot_trf_garment;
                    document.getElementById('tot_central_in').innerHTML = response.tot_central_in;
                    document.getElementById('tot_packing_out').innerHTML = response.tot_packing_out;
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
            "footerCallback": function(row, data, start, end, display) {
                var api = this.api(),
                    data;

                // converting to interger to find total
                var intVal = function(i) {
                    return typeof i === 'string' ?
                        i.replace(/[\$,]/g, '') * 1 :
                        typeof i === 'number' ?
                        i : 0;
                };

                // computing column Total of the complete result
                var sumTotal = api
                    .column(4)
                    .data()
                    .reduce(function(a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                // Update footer by showing the total with the reference of the column index
                $(api.column(0).footer()).html('Total');
                $(api.column(4).footer()).html(sumTotal);
            },
            ordering: false,
            processing: true,
            serverSide: true,
            paging: false,
            searching: true,
            scrollY: '300px',
            scrollX: '300px',
            scrollCollapse: true,
            ajax: {
                url: '{{ route('dashboard-packing') }}',
                data: function(d) {
                    d.dateFilter = $('#tgl-filter').val();
                },
            },
            columns: [{
                    data: 'line'

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
                    data: 'tot_qty_p_line'
                },
                {
                    data: 'list_po'
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
    </script>
@endsection
