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
    <form method="GET" action="{{ route('mgt_report_profit_line') }}" id="form_profit_line" name="form_profit_line">
        <div class="card card-sb">
            <div class="card-header">
                <h5 class="card-title fw-bold mb-0"><i class="fas fa-list"></i> Profit Line</h5>
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
                                <th class="text-center align-middle" style="color: black;">Line</th>
                                @foreach ($fixHeaders as $tanggal => $fix)
                                    <th>{{ $fix }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($pivotData as $row)
                                <tr>
                                    <td>{{ $row['sewing_line'] }}</td>
                                    @foreach (array_keys($fixHeaders) as $tanggal)
                                        <td class="text-end {{ ($row[$tanggal] ?? 0) < 0 ? 'text-danger' : '' }}">
                                            {{ number_format($row[$tanggal] ?? 0, 2) }}
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th class="text-center">Total</th>
                                @foreach ($fixHeaders as $tanggal)
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
        $('#form_profit_line').on('submit', function(e) {
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
                    leftColumns: 1
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

                    // Now for dynamic tanggal columns, starting from index 4
                    var colCount = api.columns().count();

                    // Start index for tanggal columns is 4 (0-based)
                    for (var i = 1; i < colCount; i++) {
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
                url: '{{ route('export_excel_laporan_profit_line') }}',
                data: {
                    start_date: start_date,
                    end_date: end_date
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
                    link.download = "Laporan Profit Line " + start_date + " _ " + end_date + ".xlsx";
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
