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
    <form method="GET" action="{{ route('mgt_report_daily_earn_buyer') }}" id="form_daily_earn_buyer"
        name="form_daily_earn_buyer">
        <div class="card card-sb">
            <div class="card-header">
                <h5 class="card-title fw-bold mb-0"><i class="fas fa-list"></i> Daily Earn Per Buyer</h5>
            </div>

            <div class="card-body">
                <div class="row align-items-end g-3 mb-3">
                    <div class="col-md-2">
                        <label class="form-label">
                            <small><b>Start Date</b></small>
                        </label>
                        <input type="date" class="form-control form-control-sm" id="start_date" name="start_date"
                            value="{{ $start_date }}">
                    </div>
                    <!-- End Date -->
                    <div class="col-md-2">
                        <label class="form-label">
                            <small><b>End Date</b></small>
                        </label>
                        <input type="date" class="form-control form-control-sm" id="end_date" name="end_date"
                            value="{{ $end_date }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label"><small><b>Buyer</b></small></label>
                        <div class="input-group">
                            <select class="form-control select2bs4 form-control-sm rounded" id="buyer_filter"
                                name="buyer_filter" style="width: 100%;">
                                <option value="">-- Select Buyer --</option>
                                @foreach ($data_buyer as $databuyer)
                                    <option value="{{ $databuyer->isi }}"
                                        {{ $buyer_filter == $databuyer->isi ? 'selected' : '' }}>
                                        {{ $databuyer->tampil }}
                                    </option>
                                @endforeach
                            </select>

                        </div>
                    </div>

                    <!-- Generate Button -->
                    <div class="col-md-4 d-flex gap-2 align-items-end">
                        <button type="submit" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-search"></i> Submit
                        </button>

                        <a onclick="export_excel()" class="btn btn-outline-success position-relative btn-sm">
                            <i class="fas fa-file-excel fa-sm"></i>
                            Export Excel
                        </a>
                    </div>

                </div>
                <div class="table-responsive">
                    <table id="myTable" class="table table-bordered text-nowrap" style="width: 100%;">
                        <thead class="bg-sb">
                            <tr>
                                <th class="text-center align-middle" style="color: black;">Date</th>
                                <th class="text-center align-middle" style="color: black;">Total Balance</th>
                                <th class="text-center align-middle" style="color: black;">Day Off (Without Output)</th>
                                @php
                                    // Get all possible buyer keys from pivotData rows (exclude known fields)
                                    $allBuyers = collect($pivotData)
                                        ->flatMap(function ($row) {
                                            return collect($row)
                                                ->except([
                                                    'tanggal',
                                                    'tanggal_fix',
                                                    'blc_full_earning',
                                                    'day_without_output',
                                                ])
                                                ->keys();
                                        })
                                        ->unique();

                                    // Filter buyers that have at least one non-zero/non-null value in any row
                                    $buyers = $allBuyers
                                        ->filter(function ($buyer) use ($pivotData) {
                                            foreach ($pivotData as $row) {
                                                if (isset($row[$buyer]) && $row[$buyer]) {
                                                    // value exists and is truthy (not 0/null)
                                                    return true;
                                                }
                                            }
                                            return false;
                                        })
                                        ->sort()
                                        ->values();
                                @endphp

                                @foreach ($buyers as $buyer)
                                    <th>{{ $buyer }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($pivotData as $row)
                                <tr>
                                    <td>{{ $row['tanggal_fix'] }}</td>
                                    <td class="text-end">{{ number_format($row['blc_full_earning'], 2) }}</td>
                                    <td class="text-end">{{ number_format($row['day_without_output'], 2) }}</td>
                                    @foreach ($buyers as $buyer)
                                        <td class="text-end {{ ($row[$buyer] ?? 0) < 0 ? 'text-danger' : '' }}">
                                            {{ number_format($row[$buyer] ?? 0, 2) }}
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th class="text-center">Total</th>
                                <th class="text-end" id="tot_blc"></th>
                                <th class="text-end" id="tot_day_off"></th>
                                @foreach ($buyers as $buyer)
                                    <th class="text-end"></th> <!-- Totals for dynamic tanggal columns -->
                                @endforeach
                            </tr>
                        </tfoot>

                    </table>
                </div>
            </div>
    </form>
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
        $('#form_daily_earn_buyer').on('submit', function(e) {
            Swal.fire({
                title: 'Please Wait...',
                html: 'Loading data...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        });

        $(document).ready(function() {
            var table = $('#myTable').DataTable({
                ordering: false,
                responsive: true,
                processing: true,
                serverSide: false,
                paging: false,
                searching: true,
                scrollY: "500px",
                scrollX: true,
                scrollCollapse: true,
                fixedColumns: {
                    leftColumns: 3
                },


                footerCallback: function(row, data, start, end, display) {
                    var api = this.api();

                    // Helper function to parse numbers safely
                    var intVal = function(i) {
                        if (typeof i === 'string') {
                            return parseFloat(i.replace(/[^0-9.-]+/g, '')) || 0;
                        } else if (typeof i === 'number') {
                            return i;
                        }
                        return 0;
                    };

                    // Calculate total for Projection (col index 2)
                    var totalBlc = api
                        .column(1, {
                            search: 'applied'
                        })
                        .data()
                        .reduce(function(a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);

                    // Calculate total for Daily Cost (col index 3)
                    var totalDayOff = api
                        .column(2, {
                            search: 'applied'
                        })
                        .data()
                        .reduce(function(a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);

                    // Set the totals in footer cells by id
                    $('#tot_blc')
                        .html(totalBlc.toLocaleString(undefined, {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        }))
                        .css('color', totalBlc < 0 ? 'red' :
                            ''); // Or use .addClass('text-danger') if using Bootstrap
                    $('#tot_day_off')
                        .html(totalDayOff.toLocaleString(undefined, {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        }))
                        .css('color', totalDayOff < 0 ? 'red' : '');


                    // Now for dynamic tanggal columns, starting from index 4
                    var colCount = api.columns().count();

                    // Start index for tanggal columns is 4 (0-based)
                    for (var i = 2; i < colCount; i++) {
                        var total = api
                            .column(i, {
                                search: 'applied'
                            })
                            .data()
                            .reduce(function(a, b) {
                                return intVal(a) + intVal(b);
                            }, 0);

                        // Use footer cell at index i and set the total formatted
                        $(api.column(i).footer())
                            .html(total.toLocaleString(undefined, {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            }))
                            .css('color', total < 0 ? 'red' : '');

                    }
                },
                // ✅ Ensure layout is drawn properly before calling draw()
                initComplete: function() {
                    let api = this.api();

                    // Small delay ensures scroll and fixedColumns are applied correctly
                    setTimeout(function() {
                        api.columns.adjust().draw(false);
                    }, 100); // 100ms is usually enough; adjust if needed
                }
            });
        });


        function export_excel() {
            let start_date = document.getElementById("start_date").value;
            let end_date = document.getElementById("end_date").value;
            let buyer_filter = document.getElementById("buyer_filter").value;
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
                url: '{{ route('export_excel_laporan_daily_earn_buyer') }}',
                data: {
                    start_date: start_date,
                    end_date: end_date,
                    buyer_filter: buyer_filter
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
                    link.download = "Laporan Daily Earn Per Buyer " + start_date + " _ " + end_date + ".xlsx";
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
