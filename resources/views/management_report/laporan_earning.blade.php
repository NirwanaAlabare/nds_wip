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
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-list"></i> Earning</h5>
        </div>

        <div class="card-body">
            <div class="row align-items-end g-3 mb-3">
                <!-- Periode Tahun -->
                <div class="col-md-2">
                    <label class="form-label">
                        <small><b>Periode Tahun</b></small>
                    </label>
                    <select class="form-control form-control-sm select2bs4" id="periode_tahun_view"
                        name="periode_tahun_view" style="width: 100%;">
                        <option value="">-- Pilih Tahun --</option>
                        @for ($year = 2025; $year <= 2030; $year++)
                            <option value="{{ $year }}"
                                {{ $year == request('periode_tahun_view') ? 'selected' : '' }}>
                                {{ $year }}
                            </option>
                        @endfor
                    </select>
                </div>

                <!-- Pilih Bulan -->
                <div class="col-md-2">
                    <label class="form-label">
                        <small><b>Pilih Bulan</b></small>
                    </label>
                    <select class="form-control form-control-sm select2bs4" id="periode_bulan_view"
                        name="periode_bulan_view" style="width: 100%;">
                        <option value="">-- Pilih Bulan --</option>
                        @for ($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}"
                                {{ $m == request('periode_bulan_view') ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                            </option>
                        @endfor
                    </select>
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
                            <th class="text-center align-middle" rowspan="2" scope="col" style="color: black;">Tanggal
                            </th>
                            <th class="text-center align-middle" rowspan="2" scope="col" style="color: black;">Line
                            </th>
                            <th class="text-center align-middle"rowspan="2" scope="col" style="color: black;">WS Number
                            </th>
                            <th class="text-center align-middle" rowspan="2" scope="col" style="color: black;">Buyer
                            </th>
                            <th class="text-center align-middle" rowspan="2" scope="col">Output</th>
                            <th class="text-center align-middle" rowspan="2" scope="col">Mins. Prod</th>
                            <th class="text-center align-middle" rowspan="2" scope="col">Mins. Avail</th>
                            <th class="text-center align-middle" rowspan="2" scope="col">Eff</th>

                            <th class="text-center align-middle" colspan="4" scope="col">Est Earning</th>
                            <th class="text-center align-middle" colspan="5" scope="col">Est Full Earning</th>
                            <th class="text-center align-middle" colspan="4" scope="col">Est Earning Production</th>
                            <th class="text-center align-middle" colspan="4" scope="col">Est Earning Marketing</th>
                        </tr>
                        <tr>
                            <th class="text-center align-middle" scope="col">Est Earning</th>
                            <th class="text-center align-middle" scope="col">Est Total Cost</th>
                            <th class="text-center align-middle" scope="col">Balance</th>
                            <th class="text-center align-middle" scope="col">% Of Earn</th>

                            <th class="text-center align-middle" scope="col">Full CM Price</th>
                            <th class="text-center align-middle" scope="col">Est Full Earning</th>
                            <th class="text-center align-middle" scope="col">Est Total Cost</th>
                            <th class="text-center align-middle" scope="col">Balance</th>
                            <th class="text-center align-middle" scope="col">% Of Earn</th>

                            <th class="text-center align-middle" scope="col">Est Earning Prod</th>
                            <th class="text-center align-middle" scope="col">Est Cost Prod</th>
                            <th class="text-center align-middle" scope="col">Balance</th>
                            <th class="text-center align-middle" scope="col">% Of Earn</th>

                            <th class="text-center align-middle" scope="col">Est Earning Mkt</th>
                            <th class="text-center align-middle" scope="col">Est Cost Mkt</th>
                            <th class="text-center align-middle" scope="col">Balance</th>
                            <th class="text-center align-middle" scope="col">% Of Earn</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th class="text-center" colspan="4">Total</th>
                            @for ($i = 0; $i < 21; $i++)
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
            $('#periode_tahun_view').val('').trigger('change');
            $('#periode_bulan_view').val('').trigger('change');
            dataTableReload()
        });


        function dataTableReload() {
            let tahun = $('#periode_tahun_view').val();
            let bulan = $('#periode_bulan_view').val();

            // Show loading Swal only if both fields are filled
            if (tahun && bulan) {
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
                    leftColumns: 4
                },
                ajax: {
                    url: '{{ route('mgt_report_earning') }}',
                    dataSrc: function(json) {
                        // Close the Swal loading when data is received
                        if (tahun && bulan) {
                            Swal.close();
                        }
                        return json.data;
                    },
                    data: function(d) {
                        d.tahun = tahun;
                        d.bulan = bulan;
                    },
                    error: function(xhr, status, error) {
                        if (tahun && bulan) {
                            Swal.fire('Error', 'Failed to load data.', 'error');
                        }
                    }
                },
                columns: [{
                        data: 'tanggal_fix'
                    },
                    {
                        data: 'sewing_line'
                    },
                    {
                        data: 'kpno'
                    },
                    {
                        data: 'buyer'
                    },
                    {
                        data: 'tot_output',
                        className: 'text-end', // Bootstrap right align
                        render: function(data, type, row) {
                            return parseFloat(data).toLocaleString(undefined, {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                        }
                    },
                    {
                        data: 'mins_prod',
                        className: 'text-end', // Bootstrap right align
                        render: function(data, type, row) {
                            return parseFloat(data).toLocaleString(undefined, {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                        }
                    },
                    {
                        data: 'mins_avail',
                        className: 'text-end', // Bootstrap right align
                        render: function(data, type, row) {
                            return parseFloat(data).toLocaleString(undefined, {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                        }
                    },
                    {
                        data: 'eff_line',
                        className: 'text-end', // Bootstrap right align
                        render: function(data, type, row) {
                            var value = parseFloat(data);
                            if (isNaN(value)) value = 0;
                            return value.toLocaleString(undefined, {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            }) + ' %'; // 👈 Append percentage symbol;
                        }
                    },
                    {
                        data: 'tot_earning_rupiah',
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
                        data: 'percent_est_earn',
                        className: 'text-end', // Bootstrap right align
                        render: function(data, type, row) {
                            var value = parseFloat(data);
                            if (isNaN(value)) value = 0;
                            return value.toLocaleString(undefined, {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            }) + ' %'; // 👈 Append percentage symbol;
                        }
                    },
                    {
                        data: 'full_cm_price',
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
                        data: 'est_full_earning',
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
                        data: 'blc_full_earn',
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
                        data: 'percent_full_earn',
                        className: 'text-end', // Bootstrap right align
                        render: function(data, type, row) {
                            var value = parseFloat(data);
                            if (isNaN(value)) value = 0;
                            return value.toLocaleString(undefined, {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            }) + ' %'; // 👈 Append percentage symbol;
                        }
                    },
                    {
                        data: 'est_earning_prod',
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
                        data: 'est_cost_prod',
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
                        data: 'blc_est_cost_prod',
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
                        data: 'percent_est_cost_prod',
                        className: 'text-end', // Bootstrap right align
                        render: function(data, type, row) {
                            var value = parseFloat(data);
                            if (isNaN(value)) value = 0;
                            return value.toLocaleString(undefined, {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            }) + ' %'; // 👈 Append percentage symbol;
                        }
                    },
                    {
                        data: 'est_earning_mkt',
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
                        data: 'est_cost_mkt',
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
                        data: 'blc_earn_mkt',
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
                        data: 'percent_earn_mkt',
                        className: 'text-end', // Bootstrap right align
                        render: function(data, type, row) {
                            var value = parseFloat(data);
                            if (isNaN(value)) value = 0;
                            return value.toLocaleString(undefined, {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            }) + ' %'; // 👈 Append percentage symbol;
                        }
                    },
                ],
                createdRow: function(row, data, dataIndex) {
                    // Loop through columns after 'buyer' (starting index 4)
                    $('td', row).each(function(colIndex) {
                        if (colIndex >= 4) {
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

                    for (let i = 4; i <= 24; i++) {
                        // Skip columns 7 and 12
                        if (i === 7 || i === 11 || i === 16 || i === 20 || i === 24) {
                            $(api.column(i).footer()).html(''); // clear footer for excluded cols (optional)
                            continue;
                        }

                        let total = api
                            .column(i, {
                                page: 'current'
                            })
                            .data()
                            .reduce((a, b) => {
                                let x = parseFloat(typeof a === 'string' ? a.replace(/,/g, '') : a) || 0;
                                let y = parseFloat(typeof b === 'string' ? b.replace(/,/g, '') : b) || 0;
                                return x + y;
                            }, 0);

                        let formattedTotal = total.toLocaleString(undefined, {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });

                        if (total < 0) {
                            formattedTotal = `<span style="color:red;">${formattedTotal}</span>`;
                        }

                        $(api.column(i).footer()).html(formattedTotal);
                    }

                },

            });
        }

        function export_excel() {
            let bulan = document.getElementById("periode_bulan_view").value;
            let tahun = document.getElementById("periode_tahun_view").value;
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
                url: '{{ route('export_excel_laporan_earning') }}',
                data: {
                    bulan: bulan,
                    tahun: tahun
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
                    link.download = "Laporan Daily Earning " + " bulan " + bulan + " _ " + tahun + ".xlsx";
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
