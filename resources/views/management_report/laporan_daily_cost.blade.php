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
    <!-- Update Modal -->
    <div class="modal fade" id="updateModal" tabindex="-1" aria-labelledby="updateModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header bg-sb">
                    <h5 class="modal-title" id="updateModalLabel">Update Data</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">

                    <!-- Select Frequency -->
                    <div class="mb-3">
                        <label for="frequency" class="form-label">Select Type</label>
                        <select class="form-select form-select-sm" id="frequency" onchange="handleFrequencyChange()">
                            <option value="">-- Select --</option>
                            <option value="daily">Daily</option>
                            <option value="monthly">Monthly</option>
                            <option value="yearly">Yearly</option>
                        </select>
                    </div>

                    <!-- Daily Inputs -->
                    <div id="daily-inputs" class="d-none">
                        <div class="mb-3">
                            <label for="startDate" class="form-label">Start Date</label>
                            <input type="date" class="form-control form-control-sm" id="startDate">
                        </div>
                        <div class="mb-3">
                            <label for="endDate" class="form-label">End Date</label>
                            <input type="date" class="form-control form-control-sm" id="endDate">
                        </div>
                    </div>

                    <!-- Monthly Inputs -->
                    <div id="monthly-inputs" class="d-none">
                        <div class="mb-3">
                            <label for="month" class="form-label">Month</label>
                            <select class="form-select form-select-sm" id="month">
                                <option value="">-- Select Month --</option>
                                <option value="01">January</option>
                                <option value="02">February</option>
                                <option value="03">March</option>
                                <option value="04">April</option>
                                <option value="05">May</option>
                                <option value="06">June</option>
                                <option value="07">July</option>
                                <option value="08">August</option>
                                <option value="09">September</option>
                                <option value="10">October</option>
                                <option value="11">November</option>
                                <option value="12">December</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="monthlyYear" class="form-label">Year</label>
                            <select class="form-select form-select-sm" id="monthlyYear"></select>
                        </div>
                    </div>

                    <!-- Yearly Input -->
                    <div id="yearly-inputs" class="d-none">
                        <div class="mb-3">
                            <label for="year" class="form-label">Year</label>
                            <select class="form-select form-select-sm" id="year"></select>
                        </div>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary btn-sm" onclick="confirm_update()">Yes, Update</button>
                </div>

            </div>
        </div>
    </div>




    <form method="GET" action="{{ route('mgt_report_daily_cost') }}" id="form_daily_cost" name="form_daily_cost">
        <div class="card card-sb">
            <div class="card-header">
                <h5 class="card-title fw-bold mb-0"><i class="fas fa-list"></i> Daily Cost</h5>
            </div>

            <div class="card-body">
                @php
                    $user = auth()->user();
                @endphp

                @if ($user && $user->username === 'admin_01')
                    <div class="d-flex align-items-end gap-3 mb-3">
                        <div class="mb-3">
                            <a onclick="update_data()" class="btn btn-outline-primary position-relative btn-sm">
                                <i class="fas fa-sync-alt fa-sm"></i>
                                Update Data
                            </a>
                        </div>
                    </div>
                @endif
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
                                <th class="text-center align-middle" style="color: black;">No. COA</th>
                                <th class="text-center align-middle" style="color: black;">Klasifikasi</th>
                                <th class="text-center align-middle" style="color: black;">Projection</th>
                                <th class="text-center align-middle" style="color: black;">Daily Cost</th>
                                @foreach ($tanggalList as $tanggal)
                                    <th class="text-center align-middle" style="white-space: nowrap;">
                                        {{ $tanggal->translatedFormat('j M Y') }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($groupedData as $coa)
                                <tr>
                                    <td>{{ $coa['no_coa'] }}</td>
                                    <td>{{ $coa['nama_coa'] }}</td>
                                    <td class="text-end">{{ number_format($coa['projection'], 2) }}</td>
                                    <td class="text-end">{{ number_format($coa['daily_cost'], 2) }}</td>
                                    @foreach ($tanggalList as $tgl)
                                        @php
                                            $key = $tgl->format('Y-m-d');
                                            $value = $coa['totals_by_date'][$key] ?? 0;
                                        @endphp
                                        <td class="text-end">{{ number_format($value, 2) }}</td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th class="text-center" colspan="2">Total</th>
                                <!-- For No. COA and Klasifikasi, text only -->
                                <th class="text-end" id="tot_projection"></th> <!-- Projection total here -->
                                <th class="text-end" id="tot_daily_cost"></th> <!-- Daily Cost total here -->
                                @foreach ($tanggalList as $tanggal)
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
        $('#form_daily_cost').on('submit', function(e) {
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
                    leftColumns: 4
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
                    var totalProjection = api
                        .column(2, {
                            search: 'applied'
                        })
                        .data()
                        .reduce(function(a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);

                    // Calculate total for Daily Cost (col index 3)
                    var totalDailyCost = api
                        .column(3, {
                            search: 'applied'
                        })
                        .data()
                        .reduce(function(a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);

                    // Set the totals in footer cells by id
                    $('#tot_projection').html(
                        totalProjection.toLocaleString(undefined, {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        })
                    );
                    $('#tot_daily_cost').html(
                        totalDailyCost.toLocaleString(undefined, {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        })
                    );

                    // Now for dynamic tanggal columns, starting from index 4
                    var colCount = api.columns().count();

                    // Start index for tanggal columns is 4 (0-based)
                    for (var i = 4; i < colCount; i++) {
                        var total = api
                            .column(i, {
                                search: 'applied'
                            })
                            .data()
                            .reduce(function(a, b) {
                                return intVal(a) + intVal(b);
                            }, 0);

                        // Use footer cell at index i and set the total formatted
                        $(api.column(i).footer()).html(
                            total.toLocaleString(undefined, {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            })
                        );
                    }
                }
            });
        });


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
                url: '{{ route('export_excel_laporan_daily_cost') }}',
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
                    link.download = "Laporan Daily Cost " + " bulan " + bulan + " _ " + tahun + ".xlsx";
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

        function update_data() {
            // Any logic before showing the modal
            console.log("Preparing to show update modal...");

            // Show the modal
            var updateModal = new bootstrap.Modal(document.getElementById('updateModal'));
            updateModal.show();
        }
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const yearSelects = {
                monthlyYear: document.getElementById('monthlyYear'),
                year: document.getElementById('year')
            };

            const currentYear = new Date().getFullYear();
            const startYear = currentYear - 1;
            const endYear = currentYear + 5;

            // Populate year dropdowns
            for (const selectId in yearSelects) {
                const select = yearSelects[selectId];
                select.innerHTML = '<option value="">-- Select Year --</option>';
                for (let year = startYear; year <= endYear; year++) {
                    const option = document.createElement('option');
                    option.value = year;
                    option.text = year;
                    select.appendChild(option);
                }
            }

            // When modal is shown, reset fields
            const updateModal = document.getElementById('updateModal');
            updateModal.addEventListener('show.bs.modal', function() {
                // Reset frequency selection
                document.getElementById('frequency').value = '';

                // Hide all dynamic inputs
                handleFrequencyChange();

                // Reset all form fields
                document.getElementById('startDate').value = '';
                document.getElementById('endDate').value = '';
                document.getElementById('month').value = '';
                document.getElementById('monthlyYear').value = currentYear; // ✅ Set default
                document.getElementById('year').value = currentYear; // ✅ Set default
            });
        });

        function handleFrequencyChange() {
            const frequency = document.getElementById('frequency').value;

            document.getElementById('daily-inputs').classList.add('d-none');
            document.getElementById('monthly-inputs').classList.add('d-none');
            document.getElementById('yearly-inputs').classList.add('d-none');

            if (frequency === 'daily') {
                document.getElementById('daily-inputs').classList.remove('d-none');
            } else if (frequency === 'monthly') {
                document.getElementById('monthly-inputs').classList.remove('d-none');
            } else if (frequency === 'yearly') {
                document.getElementById('yearly-inputs').classList.remove('d-none');
            }
        }

        function confirm_update() {
            const frequency = document.getElementById('frequency').value;
            let payload = {
                frequency: frequency
            };

            if (!frequency) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Oops...',
                    text: 'Please select a type before updating.',
                });
                return;
            }

            if (frequency === 'daily') {
                const startDate = document.getElementById('startDate').value;
                const endDate = document.getElementById('endDate').value;

                if (!startDate || !endDate) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Missing Dates',
                        text: 'Please select both start and end dates.',
                    });
                    return;
                }

                payload.start_date = startDate;
                payload.end_date = endDate;

            } else if (frequency === 'monthly') {
                const month = document.getElementById('month').value;
                const monthlyYear = document.getElementById('monthlyYear').value;

                if (!month || !monthlyYear) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Missing Info',
                        text: 'Please select both month and year.',
                    });
                    return;
                }

                payload.bulan = month;
                payload.tahun = monthlyYear;

            } else if (frequency === 'yearly') {
                const yearly = document.getElementById('year').value;
                if (!yearly) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Missing Year',
                        text: 'Please select a year.',
                    });
                    return;
                }
                payload.tahun = yearly;
            }
            // ✅ AJAX request with SweetAlert2 feedback
            $.ajax({
                type: "POST",
                url: '{{ route('update_data_labor') }}',
                data: payload,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                beforeSend: function() {
                    Swal.fire({
                        title: 'Processing...',
                        text: 'Please wait while we update the data.',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                },
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Update Successful',
                        text: 'The data was updated successfully.',
                        timer: 2000,
                        showConfirmButton: false
                    });

                    // Close modal if needed
                    const modal = bootstrap.Modal.getInstance(document.getElementById('updateModal'));
                    modal.hide();
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Update Failed',
                        text: xhr.responseJSON?.message || 'Something went wrong during the update.',
                    });
                }
            });

        }
    </script>
@endsection
