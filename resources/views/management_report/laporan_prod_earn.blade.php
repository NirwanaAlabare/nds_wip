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
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-list"></i> Summary Production Earn</h5>
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
                            <option value="{{ $year }}" {{ $year == now()->year ? 'selected' : '' }}>
                                {{ $year }}
                            </option>
                        @endfor
                    </select>
                </div>



            </div>

            <div class="table-responsive">
                <table id="datatable" class="table table-bordered text-nowrap" style="width: 100%;">
                    <thead class="bg-sb">
                        <tr>
                            <th class="text-center align-middle">Bulan</th>
                            <th class="text-center align-middle">Tahun</th>
                            <th class="text-center align-middle">Total Daily Cost</th>
                            <th class="text-center align-middle">Act</th>
                        </tr>
                    </thead>
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
            dataTableReload();
        })

        function dataTableReload() {
            datatable.ajax.reload();
            // ✅ Wait a bit, then fix layout
            setTimeout(() => {
                datatable.columns.adjust();
            }, 300); // Delay ensures DOM updates are done
        }


        let datatable = $("#datatable").DataTable({
            ordering: true,
            responsive: true,
            processing: true,
            serverSide: false,
            paging: false,
            searching: true,
            scrollY: '300px',
            scrollX: true,
            scrollCollapse: true,
            ajax: {
                url: '{{ route('mgt_report_proses_daily_cost_show_preview') }}',
                data: function(d) {
                    d.working_days = $('#working_days').val();
                    d.bulan = $('#periode_bulan').val();
                    d.tahun = $('#periode_tahun').val();
                    console.log(d.working_days);
                },
            },
            columns: [{
                    data: 'no_coa'
                },
                {
                    data: 'nama_coa'
                },
                {
                    data: 'projection'
                },
                {
                    data: 'daily_cost'
                },
            ],

            // ✅ Highlight rows where cek_valid === 'N'
            rowCallback: function(row, data, index) {
                if (data.cek_valid === 'N') {
                    $(row).css('background-color', '#ff0000'); // Bright red
                    $(row).css('color', 'white'); // Optional: make text readable
                }
            },

            drawCallback: function(settings) {
                let api = this.api();
                let total_projection = 0;
                let total_daily_cost = 0;

                // Loop through all data in daily_cost column
                api.column(2, {
                    page: 'current'
                }).data().each(function(value) {
                    total_projection += parseFloat(value) || 0;
                });

                // Display the total in the footer
                $('#total_projection').html(total_projection.toLocaleString(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));

                // Loop through all data in daily_cost column (index 3)
                api.column(3, {
                    page: 'current'
                }).data().each(function(value) {
                    total_daily_cost += parseFloat(value) || 0;
                });

                // Display the total daily cost in the footer
                $('#total_daily_cost').html(total_daily_cost.toLocaleString(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));

            }
        });
    </script>
@endsection
