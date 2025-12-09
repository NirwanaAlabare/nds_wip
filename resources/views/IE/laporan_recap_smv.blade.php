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




    <form>
        <div class="card card-sb">
            <div class="card-header">
                <h5 class="card-title fw-bold mb-0"><i class="fas fa-list"></i> Recap SMV</h5>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table id="myTable" class="table table-bordered text-nowrap" style="width:100%">
                        <thead class="bg-sb">
                            <tr>
                                <th class="text-center">Buyer</th>
                                <th class="text-center">WS</th>
                                <th class="text-center">Styleno</th>
                                <th class="text-center">Total Perubahan</th>
                                <th class="text-center">Tgl. Plan</th>
                                <th class="text-center">SMV</th>
                                @php
                                    $maxDetails = 0;
                                    foreach ($groupedData as $ws) {
                                        $maxDetails = max($maxDetails, count($ws['details']));
                                    }
                                @endphp
                                @for ($i = 1; $i < $maxDetails; $i++)
                                    <th class="text-center">Tgl. Plan</th>
                                    <th class="text-center">SMV</th>
                                @endfor
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $colors = ['#fff3cd', '#d1ecf1', '#f8d7da', '#d4edda']; // kuning, biru, merah muda, hijau muda
                                $colorCount = count($colors);
                            @endphp
                            @foreach ($groupedData as $ws)
                                <tr>
                                    <td class="text-left">{{ $ws['buyer'] }}</td>
                                    <td class="text-left">{{ $ws['kpno'] }}</td>
                                    <td class="text-left">{{ $ws['styleno'] }}</td>
                                    <td class="text-center">{{ $ws['total_changes'] }}</td>
                                    @foreach ($ws['details'] as $index => $detail)
                                        @php
                                            if ($index == 0) {
                                                $bgColor = '';
                                            } else {
                                                // Selang-seling dari array warna, kembali ke awal jika >4
                                                $colorIndex = ($index - 1) % $colorCount;
                                                $bgColor = $colors[$colorIndex];
                                            }
                                        @endphp
                                        <td class="text-center" style="background-color: {{ $bgColor }};">
                                            {{ $detail['tgl_plan_fix'] }}</td>
                                        <td class="text-center" style="background-color: {{ $bgColor }};">
                                            {{ $detail['smv'] }}</td>
                                    @endforeach
                                    @php
                                        $missing = $maxDetails - count($ws['details']);
                                    @endphp
                                    @for ($i = 0; $i < $missing; $i++)
                                        <td class="text-center">-</td>
                                        <td class="text-center">-</td>
                                    @endfor
                                </tr>
                            @endforeach
                        </tbody>
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
        $(document).ready(function() {
            var table = $('#myTable').DataTable({
                ordering: true,
                responsive: true,
                processing: true,
                serverSide: false,
                paging: true,
                searching: true,
                scrollY: "800px",
                scrollX: true,
                scrollCollapse: true,

                // Ubah default page length menjadi 25
                pageLength: 15,

                // Menentukan pilihan dropdown untuk jumlah row yang ditampilkan
                lengthMenu: [
                    [15, 50, 100, -1],
                    [15, 50, 100, "All"]
                ],

                initComplete: function() {
                    let api = this.api();

                    setTimeout(function() {
                        api.columns.adjust().draw(false);
                    }, 100);
                }
            });
        });
    </script>
@endsection
