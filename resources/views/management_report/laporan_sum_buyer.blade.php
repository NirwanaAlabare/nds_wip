@extends('layouts.index')

@section('custom-link')
    {{-- <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}"> --}}

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables 2.0/jquery.dataTables.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables 2.0/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables 2.0/fixedColumns.bootstrap4.min.css') }}">
    <!-- jQuery -->
    <script src="{{ asset('plugins/datatables 2.0/jquery-3.3.1.js') }}"></script>


    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@endsection

@section('content')
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-list"></i> Summary Buyer</h5>
        </div>

        <div class="card-body">
            <div class="row align-items-end g-3 mb-3">
                <!-- Start Date -->
                <div class="col-md-2">
                    <label class="form-label">
                        <small><b>Start Date</b></small>
                    </label>
                    <input type="date" class="form-control form-control-sm" id="start_date" name="start_date"
                        value="">
                </div>
                <!-- End Date -->
                <div class="col-md-2">
                    <label class="form-label">
                        <small><b>End Date</b></small>
                    </label>
                    <input type="date" class="form-control form-control-sm" id="end_date" name="end_date"
                        value="">
                </div>

                <div class="col-md-3">
                    <label class="form-label"><small><b>Buyer</b></small></label>
                    <div class="input-group">
                        <select class="form-control select2bs4 form-control-sm rounded" id="buyer_filter"
                            name="buyer_filter" style="width: 100%;">
                            <option value="">-- Select Buyer --</option>
                            @foreach ($data_buyer as $databuyer)
                                <option value="{{ $databuyer->isi }}">
                                    {{ $databuyer->tampil }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Generate Button -->
                <div class="col-md-4 d-flex gap-2 align-items-end">
                    <a class="btn btn-outline-primary position-relative btn-sm" onclick="dataTableReload()">
                        <i class="fas fa-search"></i>
                        Submit
                    </a>

                    <a onclick="export_excel()" class="btn btn-outline-success position-relative btn-sm">
                        <i class="fas fa-file-excel fa-sm"></i>
                        Export Excel
                    </a>
                </div>
            </div>


            <div class="table-responsive">
                <table id="datatable" class="table table-bordered w-100 text-nowrap">
                    <thead class="bg-sb">
                        <tr>
                            <th class="text-center align-middle" scope="col" style="color: black;">Buyer
                            </th>
                            <th class="text-center align-middle" scope="col">Qty Target</th>
                            <th class="text-center align-middle" scope="col">Qty Produced</th>
                            <th class="text-center align-middle" scope="col">Mins. Avail.</th>
                            <th class="text-center align-middle" scope="col">Mins. Prod</th>
                            <th class="text-center align-middle" scope="col">Effy</th>
                            <th class="text-center align-middle" scope="col">Est Earning Prd</th>
                            <th class="text-center align-middle" scope="col">Est Total Cost</th>
                            <th class="text-center align-middle" scope="col">Balance</th>
                            <th class="text-center align-middle" scope="col">% of Earning</th>

                            <th class="text-center align-middle" scope="col">Est Full Earning</th>
                            <th class="text-center align-middle" scope="col">Est Total Cost</th>
                            <th class="text-center align-middle" scope="col">Balance</th>
                            <th class="text-center align-middle" scope="col">% of Earning</th>

                            <th class="text-center align-middle" scope="col">Est Earning Prd</th>
                            <th class="text-center align-middle" scope="col">Est Cost Prd</th>
                            <th class="text-center align-middle" scope="col">Balance</th>
                            <th class="text-center align-middle" scope="col">% of Earning</th>

                            <th class="text-center align-middle" scope="col">Est Earning Mkt</th>
                            <th class="text-center align-middle" scope="col">Est Cost Prd</th>
                            <th class="text-center align-middle" scope="col">Balance</th>
                            <th class="text-center align-middle" scope="col">% of Earning</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th class="text-center">Total</th>
                            @for ($i = 1; $i <= 21; $i++)
                                <th></th>
                            @endfor
                        </tr>
                    </tfoot>
                </table>
            </div>

        </div>
    </div>
@endsection

@section('custom-script')
    <!-- DataTables & Plugins -->
    {{-- <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script> --}}
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables 2.0/dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables 2.0/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables 2.0/dataTables.fixedColumns.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-rowsgroup/dataTables.rowsGroup.js') }}"></script>
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
            width: 'resolve' // Ensures it respects the 100% width from inline style or Bootstrap
        });
        // Now set height and font-size on the Select2 container after init
        $('.select2-container--bootstrap4 .select2-selection--single').css({
            'height': '30px', // your desired height
            'font-size': '12px', // your desired font size
            'line-height': '30px' // vertically center text
        });

        function notif() {
            alert("Maaf, Fitur belum tersedia!");
        }
    </script>

    <script>
        $(document).ready(function() {
            $('#start_date').val('').trigger('change');
            $('#end_date').val('').trigger('change');
            $('#buyer_filter').val('').trigger('change');
            dataTableReload()
        });


        function dataTableReload() {
            let start_date = $('#start_date').val();
            let end_date = $('#end_date').val();
            let buyer = $('#buyer_filter').val();

            // Show loading Swal only if both fields are filled
            if (start_date && end_date) {
                Swal.fire({
                    title: 'Loading...',
                    text: 'Please wait while data is loading.',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
            }

            let table = $('#datatable').DataTable({
                destroy: true,
                ordering: false,
                responsive: true,
                serverSide: false,
                paging: false,
                searching: true,
                scrollY: "500px",
                scrollX: true,
                scrollCollapse: true,
                language: {
                    loadingRecords: "",
                    processing: ""
                },
                processing: false, // keep processing true if you want to use processing events, just hide the text

                fixedColumns: {
                    leftColumns: 1
                },
                ajax: {
                    url: '{{ route('mgt_report_sum_buyer') }}',
                    dataSrc: function(json) {
                        // Close the Swal loading when data is received
                        if (start_date && end_date) {
                            Swal.close();
                        }
                        return json.data;
                    },
                    data: function(d) {
                        d.start_date = start_date;
                        d.end_date = end_date;
                        d.buyer = buyer;
                    },
                    error: function(xhr, status, error) {
                        if (start_date && end_date) {
                            Swal.fire('Error', 'Failed to load data.', 'error');
                        }
                    }
                },
                columns: [{
                        data: 'buyer'
                    },
                    {
                        data: 'tot_target',
                        className: 'text-end', // Bootstrap right align
                        render: function(data, type, row) {
                            var value = parseFloat(data);
                            if (isNaN(value)) value = 0;
                            return value.toLocaleString(undefined, {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                        }
                    },
                    {
                        data: 'tot_output',
                        className: 'text-end', // Bootstrap right align
                        render: function(data, type, row) {
                            var value = parseFloat(data);
                            if (isNaN(value)) value = 0;
                            return value.toLocaleString(undefined, {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                        }
                    },
                    {
                        data: 'sum_mins_avail',
                        className: 'text-end', // Bootstrap right align
                        render: function(data, type, row) {
                            var value = parseFloat(data);
                            if (isNaN(value)) value = 0;
                            return value.toLocaleString(undefined, {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                        }
                    },
                    {
                        data: 'sum_mins_prod',
                        className: 'text-end', // Bootstrap right align
                        render: function(data, type, row) {
                            var value = parseFloat(data);
                            if (isNaN(value)) value = 0;
                            return value.toLocaleString(undefined, {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                        }
                    },
                    {
                        data: 'eff',
                        className: 'text-end', // Bootstrap right align
                        render: function(data, type, row) {
                            var value = parseFloat(data);
                            if (isNaN(value)) value = 0;
                            return value.toLocaleString(undefined, {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                        }
                    },
                    {
                        data: 'earn_prod',
                        className: 'text-end', // Bootstrap right align
                        render: function(data, type, row) {
                            var value = parseFloat(data);
                            if (isNaN(value)) value = 0;
                            return value.toLocaleString(undefined, {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                        }
                    },
                    {
                        data: 'est_tot_cost',
                        className: 'text-end', // Bootstrap right align
                        render: function(data, type, row) {
                            var value = parseFloat(data);
                            if (isNaN(value)) value = 0;
                            return value.toLocaleString(undefined, {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                        }
                    },
                    {
                        data: 'blc',
                        className: 'text-end', // Bootstrap right align
                        render: function(data, type, row) {
                            var value = parseFloat(data);
                            if (isNaN(value)) value = 0;
                            return value.toLocaleString(undefined, {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                        }
                    },
                    {
                        data: 'percent_earning',
                        className: 'text-end',
                        render: function(data, type, row) {
                            var value = parseFloat(data);
                            if (isNaN(value)) value = 0;
                            return value.toLocaleString(undefined, {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                        }
                    },
                    {
                        data: 'sum_est_full_earning',
                        className: 'text-end', // Bootstrap right align
                        render: function(data, type, row) {
                            var value = parseFloat(data);
                            if (isNaN(value)) value = 0;
                            return value.toLocaleString(undefined, {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                        }
                    },
                    {
                        data: 'est_tot_cost',
                        className: 'text-end', // Bootstrap right align
                        render: function(data, type, row) {
                            var value = parseFloat(data);
                            if (isNaN(value)) value = 0;
                            return value.toLocaleString(undefined, {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                        }
                    },
                    {
                        data: 'blc_full_earn_cost_prod',
                        className: 'text-end', // Bootstrap right align
                        render: function(data, type, row) {
                            var value = parseFloat(data);
                            if (isNaN(value)) value = 0;
                            return value.toLocaleString(undefined, {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                        }
                    },
                    {
                        data: 'percent_full_earning_cost',
                        className: 'text-end', // Bootstrap right align
                        render: function(data, type, row) {
                            var value = parseFloat(data);
                            if (isNaN(value)) value = 0;
                            return value.toLocaleString(undefined, {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                        }
                    },

                    {
                        data: 'sum_est_earning_prod',
                        className: 'text-end', // Bootstrap right align
                        render: function(data, type, row) {
                            var value = parseFloat(data);
                            if (isNaN(value)) value = 0;
                            return value.toLocaleString(undefined, {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                        }
                    },
                    {
                        data: 'sum_est_cost_prod',
                        className: 'text-end', // Bootstrap right align
                        render: function(data, type, row) {
                            var value = parseFloat(data);
                            if (isNaN(value)) value = 0;
                            return value.toLocaleString(undefined, {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                        }
                    },
                    {
                        data: 'blc_earn_cost_prod',
                        className: 'text-end', // Bootstrap right align
                        render: function(data, type, row) {
                            var value = parseFloat(data);
                            if (isNaN(value)) value = 0;
                            return value.toLocaleString(undefined, {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                        }
                    },
                    {
                        data: 'percent_earn_cost_prod',
                        className: 'text-end', // Bootstrap right align
                        render: function(data, type, row) {
                            var value = parseFloat(data);
                            if (isNaN(value)) value = 0;
                            return value.toLocaleString(undefined, {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                        }
                    },
                    {
                        data: 'sum_est_earning_mkt',
                        className: 'text-end', // Bootstrap right align
                        render: function(data, type, row) {
                            var value = parseFloat(data);
                            if (isNaN(value)) value = 0;
                            return value.toLocaleString(undefined, {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                        }
                    },
                    {
                        data: 'sum_est_cost_mkt',
                        className: 'text-end', // Bootstrap right align
                        render: function(data, type, row) {
                            var value = parseFloat(data);
                            if (isNaN(value)) value = 0;
                            return value.toLocaleString(undefined, {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                        }
                    },
                    {
                        data: 'blc_earn_cost_mkt',
                        className: 'text-end', // Bootstrap right align
                        render: function(data, type, row) {
                            var value = parseFloat(data);
                            if (isNaN(value)) value = 0;
                            return value.toLocaleString(undefined, {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                        }
                    },
                    {
                        data: 'percent_earn_cost_mkt',
                        className: 'text-end', // Bootstrap right align
                        render: function(data, type, row) {
                            var value = parseFloat(data);
                            if (isNaN(value)) value = 0;
                            return value.toLocaleString(undefined, {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                        }
                    },

                ],
                createdRow: function(row, data, dataIndex) {
                    // Loop through columns after 'buyer' (starting index 4)
                    $('td', row).each(function(colIndex) {
                        if (colIndex >= 1) {
                            let cellValue = $(this).text().replace(/,/g, '');
                            let number = parseFloat(cellValue);

                            if (!isNaN(number) && number < 0) {
                                $(this).css('color', 'red');
                            }
                        }
                    });
                },

                footerCallback: function(row, data, start, end, display) {
                    let api = this.api();

                    // Loop from column index 1 to 23 (second column to last)
                    for (let i = 1; i <= 21; i++) {

                        let total = api
                            .column(i, {
                                page: 'current'
                            }) // Only current page rows
                            .data()
                            .reduce((a, b) => {
                                const clean = val => {
                                    if (typeof val === 'string') {
                                        return parseFloat(val.replace(/[,|%]/g, '')) || 0;
                                    }
                                    return parseFloat(val) || 0;
                                };
                                return clean(a) + clean(b);
                            }, 0);

                        // Format the total with 2 decimal places
                        let formattedTotal = total.toLocaleString(undefined, {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });

                        // Optional: show negative numbers in red
                        if (total < 0) {
                            formattedTotal = `<span style="color:red;">${formattedTotal}</span>`;
                        }

                        // Set the formatted total in the correct footer cell
                        $(api.column(i).footer()).html(formattedTotal);
                    }
                }


            });
        }

        function export_excel() {
            let start_date = $('#start_date').val();
            let end_date = $('#end_date').val();
            let buyer = $('#buyer_filter').val();
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
                url: '{{ route('export_excel_laporan_sum_buyer') }}',
                data: {
                    start_date: start_date,
                    end_date: end_date,
                    buyer: buyer
                },
                xhrFields: {
                    responseType: 'blob'
                },
                success: function(response) {
                    Swal.close();
                    Swal.fire({
                        title: 'Data Sudah Di Export!',
                        icon: "success",
                        showConfirmButton: true,
                        allowOutsideClick: false
                    });

                    var blob = new Blob([response]);
                    var link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = "Laporan Summary Buyer " + start_date + " _ " + end_date +
                        ".xlsx";
                    link.click();
                },
                error: function(xhr, status, error) {
                    Swal.close();
                    Swal.fire({
                        title: 'Gagal Mengekspor Data',
                        text: 'Terjadi kesalahan saat mengekspor. Silakan coba lagi.',
                        icon: 'error',
                        showConfirmButton: true,
                        allowOutsideClick: false
                    });

                    console.error("Export failed:", {
                        status: status,
                        error: error,
                        response: xhr.responseText
                    });
                }
            });
        }
    </script>
@endsection
