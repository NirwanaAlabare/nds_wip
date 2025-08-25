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
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-chart-area"></i> Summary Transaksi</h5>
        </div>
        <div class="card-body">
            <div class="d-flex align-items-end gap-3 mb-3">
                <div class="col-md-5">
                    <div class="form-group">
                        <label>Buyer</label>
                        <div class="input-group">
                            <select class="form-control select2bs4 form-control-sm rounded" id="cbobuyer" name="cbobuyer"
                                style="width: 100%;">
                                <option selected="selected" value="" disabled="true">Pilih Buyer</option>
                                @foreach ($data_buyer as $databuyer)
                                    <option value="{{ $databuyer->isi }}">
                                        {{ $databuyer->tampil }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>WS</label>
                        <div class="input-group">
                            <select class="form-control select2bs4 form-control-sm rounded" id="cbows" name="cbows"
                                style="width: 100%;">
                                <option selected="selected" value="" disabled="true">Pilih WS</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <a onclick="dataTableReload()" class="btn btn-outline-primary position-relative btn-sm">
                        <i class="fas fa-search fa-sm"></i>
                    </a>
                </div>
                <div class="mb-3">
                    <a onclick="export_excel_tracking()" class="btn btn-outline-success position-relative btn-sm">
                        <i class="fas fa-file-excel fa-sm"></i>
                        Export
                    </a>
                </div>
            </div>

            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-striped table w-100 text-nowrap">
                    <thead class="table-success">
                        <tr style='text-align:center; vertical-align:middle'>
                            <th>Buyer</th>
                            <th>WS</th>
                            <th>Color</th>
                            <th>Size</th>
                            <th>Qc Output</th>
                            <th>QC Finishing Output</th>
                            <th>Qty Trf Garment</th>
                            <th>Qty Packing In</th>
                            <th>Qty Packing Out</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th colspan="4"></th>
                            <th> <input type = 'text' class="form-control form-control-sm" style="width:75px" readonly
                                    id = 'total_qc'> </th>
                            <th> <input type = 'text' class="form-control form-control-sm" style="width:75px" readonly
                                    id = 'total_p_line'> </th>
                            <th> <input type = 'text' class="form-control form-control-sm" style="width:75px" readonly
                                    id = 'total_trf_garment'> </th>
                            <th> <input type = 'text' class="form-control form-control-sm" style="width:75px" readonly
                                    id = 'total_p_in'> </th>
                            <th> <input type = 'text' class="form-control form-control-sm" style="width:75px" readonly
                                    id = 'total_p_out'> </th>
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
            containerCssClass: 'form-control-sm rounded'
        });
    </script>
    <script>
        $(document).ready(() => {
            $('#cbobuyer').val('').trigger('change');
            dataTableReload();
        });

        function notif() {
            alert("Maaf, Fitur belum tersedia!");
        }

        $("#cbobuyer").on("change", () => {
            document.getElementById("loading").classList.remove('d-none');

            let selectWsElement = document.getElementById('cbows');

            $.ajax({
                url: "{{ route('get-orders') }}",
                method: "get",
                data: {
                    buyer: $("#cbobuyer").val()
                },
                dataType: "json",
                success: function(response) {
                    document.getElementById("loading").classList.add('d-none');

                    if (response.length > 0) {
                        selectWsElement.innerHTML = '';

                        let initOption = document.createElement("option");
                        initOption.setAttribute("value", "");
                        initOption.innerHTML = "Pilih WS";

                        selectWsElement.append(initOption);

                        for (let i = 0; i < response.length; i++) {
                            let newOption = document.createElement("option");
                            newOption.setAttribute("value", response[i].ws);
                            newOption.innerHTML = response[i].ws;

                            selectWsElement.append(newOption);
                        }
                    }
                },
                error: function(jqXHR) {
                    document.getElementById("loading").classList.add('d-none');

                    console.log(jqXHR);
                }
            });
        });

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
                var sumTotQc = api
                    .column(4)
                    .data()
                    .reduce(function(a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                var sumTotPLine = api
                    .column(5)
                    .data()
                    .reduce(function(a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                var sumTotTrfGarment = api
                    .column(6)
                    .data()
                    .reduce(function(a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                var sumTotPIn = api
                    .column(7)
                    .data()
                    .reduce(function(a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                var sumTotPOut = api
                    .column(8)
                    .data()
                    .reduce(function(a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                // Update footer by showing the total with the reference of the column index
                $(api.column(0).footer()).html('Total');
                $(api.column(4).footer()).html(sumTotQc);
                $(api.column(5).footer()).html(sumTotPLine);
                $(api.column(6).footer()).html(sumTotTrfGarment);
                $(api.column(7).footer()).html(sumTotPIn);
                $(api.column(8).footer()).html(sumTotPOut);
            },

            ordering: false,
            processing: true,
            serverSide: true,
            paging: false,
            searching: true,
            scrollY: '300px',
            scrollX: '300px',
            scrollCollapse: true,
            destroy: true,
            ajax: {
                url: '{{ route('show_lap_tracking_ppic') }}',
                dataType: 'json',
                dataSrc: 'data',
                data: function(d) {
                    d.buyer = $('#cbobuyer').val();
                    d.ws = $('#cbows').val();
                },
            },
            columns: [{
                    data: 'buyer'

                },
                {
                    data: 'ws'
                },
                {
                    data: 'color'
                },
                {
                    data: 'size'
                },
                {
                    data: 'tot_qc'
                },
                {
                    data: 'tot_p_line'
                },
                {
                    data: 'qty_trf_garment'
                },
                {
                    data: 'qty_packing_in'
                },
                {
                    data: 'qty_packing_out'
                },
            ],
            columnDefs: [{
                "className": "align-left",
                "targets": "_all"
            }, ]
        });


        function dataTableReload() {
            datatable.ajax.reload();
        }

        function export_excel_tracking() {
            let ws = document.getElementById("cbows").value;
            let buyer = document.getElementById("cbobuyer").value;
            Swal.fire({
                title: 'Please Wait...',
                html: 'Exporting Data...',
                didOpen: () => {
                    Swal.showLoading()
                },
                allowOutsideClick: false,
            });

            $.ajax({
                type: "get",
                url: '{{ route('export_excel_tracking') }}',
                data: {
                    ws: ws,
                    buyer: buyer,
                },
                xhrFields: {
                    responseType: 'blob'
                },
                success: function(response) {
                    {
                        swal.close();
                        Swal.fire({
                            title: 'Data Sudah Di Export!',
                            icon: "success",
                            showConfirmButton: true,
                            allowOutsideClick: false
                        });
                        var blob = new Blob([response]);
                        var link = document.createElement('a');
                        link.href = window.URL.createObjectURL(blob);
                        link.download = "Laporan Tracking " + buyer + ".xlsx";
                        link.click();

                    }
                },
            });
        }
    </script>
@endsection
