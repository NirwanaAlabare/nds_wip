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
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-list"></i> Report Mutasi Sewing (Production)</h5>
        </div>
        <div class="card-body">
            <div class="d-flex align-items-end gap-3 mb-3 ">
                <div class="mb-3" style="width: 300px;">
                    <label class="form-label"><small><b>Buyer</b></small></label>
                    <select class="form-control select2bs4 form-control-sm" id="cbobuyer" name="cbobuyer"
                        style="width: 100%;">
                        <option selected="selected" value="" disabled="true">Pilih Buyer</option>
                        <option value="">ALL</option>
                        @foreach ($data_buyer as $databuyer)
                            <option value="{{ $databuyer->isi }}">
                                {{ $databuyer->tampil }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label"><small><b>Tgl Awal</b></small></label>
                    <input type="date" class="form-control form-control-sm" id="start_date" name="start_date"
                        min="2026-01-01" value="">
                </div>
                <div class="mb-3">
                    <label class="form-label"><small><b>Tgl Akhir</b></small></label>
                    <input type="date" class="form-control form-control-sm" id="end_date" name="end_date"
                        min="2026-01-01" value="">
                </div>
                <div class="mb-3">
                    <a onclick="dataTableReload();" class="btn btn-outline-primary btn-sm position-relative">
                        <i class="fas fa-search fa-xs"></i> <!-- Use fa-xs for extra small icon -->
                    </a>
                </div>
                <div class="mb-3">
                    <a onclick="export_excel()" class="btn btn-outline-success position-relative btn-sm">
                        <i class="fas fa-file-excel fa-sm"></i>
                        Export Excel
                    </a>
                </div>
            </div>

            <div class="table-responsive">
                <table id="datatable" class="table table-bordered w-100 ">
                    <thead>
                        <tr style='text-align:center; vertical-align:middle'>
                            <th colspan="5" style="background-color: lightblue; text-align:center;">Jenis Produk</th>
                            <th colspan="15" style="background-color: lightyellow; text-align:center;">Sewing</th>
                            <th colspan="12" style="background-color: pink; text-align:center;">QC Finishing</th>
                            <th colspan="8" class="finishing-old" style="background-color: #5DADE2; text-align:center;">Finishing</th>
                            <th colspan="8" class="finishing-process" style="background-color: #90EE90; text-align:center;">Finishing - Pasang Kancing</th>
                            <th colspan="8" class="finishing-process" style="background-color: #87CEEB; text-align:center;">Finishing - Bartack</th>
                            <th colspan="8" class="finishing-process" style="background-color: #D8BFD8; text-align:center;">Finishing - Heatseal</th>
                            <th colspan="8" class="finishing-process" style="background-color: #5DADE2; text-align:center;">Finishing - Snap</th>
                            <th colspan="8" class="finishing-process" style="background-color: #C8E6C9; text-align:center;">Finishing - Embro</th>
                            <th colspan="5" style="background-color: #FFE5B4; text-align:center;">Defect Sewing</th>
                            <th colspan="5" style="background-color: Lavender; text-align:center;">Defect Spotcleaning</th>
                            <th colspan="5" style="background-color: lightyellow; text-align:center;">Defect Mending</th>
                            <th colspan="8" style="background-color: #FFA94D; text-align:center;">Transit Terima QC Reject</th>
                            <th colspan="6" style="background-color: pink; text-align:center;">QC Reject</th>
                        </tr>
                        <tr>
                            <th class="text-center align-middle" style="background-color: lightblue;">Buyer</th>
                            <th class="text-center align-middle" style="background-color: lightblue;">WS</th>
                            <th class="text-center align-middle" style="background-color: lightblue;">Style</th>
                            <th class="text-center align-middle" style="background-color: lightblue;">Color</th>
                            <th class="text-center align-middle" style="background-color: lightblue;">Size</th>

                            <th class="text-center align-middle" style="background-color: lightyellow;">Saldo Awal</th>
                            <th class="text-center align-middle" style="background-color: lightyellow;">Terima Loading</th>
                            <th class="text-center align-middle" style="background-color: lightyellow;">Terima Gudang Stok</th>
                            <th class="text-center align-middle" style="background-color: lightyellow;">Subcont In</th>
                            <th class="text-center align-middle" style="background-color: lightyellow;">Output Rework Sewing</th>
                            <th class="text-center align-middle" style="background-color: lightyellow;">Output Rework Spotcleaning</th>
                            <th class="text-center align-middle" style="background-color: lightyellow;">Output Rework Mending</th>
                            <th class="text-center align-middle" style="background-color: lightyellow;">Defect Sewing</th>
                            <th class="text-center align-middle" style="background-color: lightyellow;">Defect Spotcleaning</th>
                            <th class="text-center align-middle" style="background-color: lightyellow;">Defect Mending</th>
                            <th class="text-center align-middle" style="background-color: lightyellow;">Reject</th>
                            <th class="text-center align-middle" style="background-color: lightyellow;">Output</th>
                            <th class="text-center align-middle" style="background-color: lightyellow;">Subcont Out Production</th>
                            {{-- <th class="text-center align-middle" style="background-color: lightyellow;">Switching OUT</th>
                            <th class="text-center align-middle" style="background-color: lightyellow;">Switching IN</th> --}}
                            <th class="text-center align-middle" style="background-color: lightyellow;">Adjustment</th>
                            <th class="text-center align-middle" style="background-color: lightyellow;">Saldo Akhir</th>

                            <th class="text-center align-middle" style="background-color: pink;">Saldo Awal</th>
                            <th class="text-center align-middle" style="background-color: pink;">Terima Sewing</th>
                            <th class="text-center align-middle" style="background-color: pink;">Output Rework Sewing</th>
                            <th class="text-center align-middle" style="background-color: pink;">Output Rework Spotcleaning</th>
                            <th class="text-center align-middle" style="background-color: pink;">Output Rework Mending</th>
                            <th class="text-center align-middle" style="background-color: pink;">Defect Sewing</th>
                            <th class="text-center align-middle" style="background-color: pink;">Defect Spotcleaning</th>
                            <th class="text-center align-middle" style="background-color: pink;">Defect Mending</th>
                            <th class="text-center align-middle" style="background-color: pink;">Reject</th>
                            <th class="text-center align-middle" style="background-color: pink;">Output</th>
                            {{-- <th class="text-center align-middle" style="background-color: pink;">Switching OUT</th>
                            <th class="text-center align-middle" style="background-color: pink;">Switching IN</th> --}}
                            <th class="text-center align-middle" style="background-color: pink;">Adjustment</th>
                            <th class="text-center align-middle" style="background-color: pink;">Saldo Akhir</th>

                            <th class="text-center align-middle finishing-old" style="background-color: #5DADE2;">Saldo Awal</th>
                            <th class="text-center align-middle finishing-old" style="background-color: #5DADE2;">Terima</th>
                            <th class="text-center align-middle finishing-old" style="background-color: #5DADE2;">Rework</th>
                            <th class="text-center align-middle finishing-old" style="background-color: #5DADE2;">Defect</th>
                            <th class="text-center align-middle finishing-old" style="background-color: #5DADE2;">Reject</th>
                            <th class="text-center align-middle finishing-old" style="background-color: #5DADE2;">Output</th>
                            <th class="text-center align-middle finishing-old" style="background-color: #5DADE2;">Adjustment</th>
                            <th class="text-center align-middle finishing-old" style="background-color: #5DADE2;">Saldo Akhir</th>
                            
                            <!-- Finishing - Pasang Kancing -->
                            <th class="text-center align-middle finishing-process" style="background-color: #90EE90;">Saldo Awal</th>
                            <th class="text-center align-middle finishing-process" style="background-color: #90EE90;">Terima</th>
                            <th class="text-center align-middle finishing-process" style="background-color: #90EE90;">Rework</th>
                            <th class="text-center align-middle finishing-process" style="background-color: #90EE90;">Defect</th>
                            <th class="text-center align-middle finishing-process" style="background-color: #90EE90;">Reject</th>
                            <th class="text-center align-middle finishing-process" style="background-color: #90EE90;">Output</th>
                            <th class="text-center align-middle finishing-process" style="background-color: #90EE90;">Adjustment</th>
                            <th class="text-center align-middle finishing-process" style="background-color: #90EE90;">Saldo Akhir</th>

                            <!-- Finishing - Bartack -->
                            <th class="text-center align-middle finishing-process" style="background-color: #87CEEB;">Saldo Awal</th>
                            <th class="text-center align-middle finishing-process" style="background-color: #87CEEB;">Terima</th>
                            <th class="text-center align-middle finishing-process" style="background-color: #87CEEB;">Rework</th>
                            <th class="text-center align-middle finishing-process" style="background-color: #87CEEB;">Defect</th>
                            <th class="text-center align-middle finishing-process" style="background-color: #87CEEB;">Reject</th>
                            <th class="text-center align-middle finishing-process" style="background-color: #87CEEB;">Output</th>
                            <th class="text-center align-middle finishing-process" style="background-color: #87CEEB;">Adjustment</th>
                            <th class="text-center align-middle finishing-process" style="background-color: #87CEEB;">Saldo Akhir</th>

                            <!-- Finishing - Heatseal -->
                            <th class="text-center align-middle finishing-process" style="background-color: #D8BFD8;">Saldo Awal</th>
                            <th class="text-center align-middle finishing-process" style="background-color: #D8BFD8;">Terima</th>
                            <th class="text-center align-middle finishing-process" style="background-color: #D8BFD8;">Rework</th>
                            <th class="text-center align-middle finishing-process" style="background-color: #D8BFD8;">Defect</th>
                            <th class="text-center align-middle finishing-process" style="background-color: #D8BFD8;">Reject</th>
                            <th class="text-center align-middle finishing-process" style="background-color: #D8BFD8;">Output</th>
                            <th class="text-center align-middle finishing-process" style="background-color: #D8BFD8;">Adjustment</th>
                            <th class="text-center align-middle finishing-process" style="background-color: #D8BFD8;">Saldo Akhir</th>

                            <!-- Finishing - Snap -->
                            <th class="text-center align-middle finishing-process" style="background-color: #5DADE2;">Saldo Awal</th>
                            <th class="text-center align-middle finishing-process" style="background-color: #5DADE2;">Terima</th>
                            <th class="text-center align-middle finishing-process" style="background-color: #5DADE2;">Rework</th>
                            <th class="text-center align-middle finishing-process" style="background-color: #5DADE2;">Defect</th>
                            <th class="text-center align-middle finishing-process" style="background-color: #5DADE2;">Reject</th>
                            <th class="text-center align-middle finishing-process" style="background-color: #5DADE2;">Output</th>
                            <th class="text-center align-middle finishing-process" style="background-color: #5DADE2;">Adjustment</th>
                            <th class="text-center align-middle finishing-process" style="background-color: #5DADE2;">Saldo Akhir</th>

                            <!-- Finishing - Embro -->
                            <th class="text-center align-middle finishing-process" style="background-color: #C8E6C9;">Saldo Awal</th>
                            <th class="text-center align-middle finishing-process" style="background-color: #C8E6C9;">Terima</th>
                            <th class="text-center align-middle finishing-process" style="background-color: #C8E6C9;">Rework</th>
                            <th class="text-center align-middle finishing-process" style="background-color: #C8E6C9;">Defect</th>
                            <th class="text-center align-middle finishing-process" style="background-color: #C8E6C9;">Reject</th>
                            <th class="text-center align-middle finishing-process" style="background-color: #C8E6C9;">Output</th>
                            <th class="text-center align-middle finishing-process" style="background-color: #C8E6C9;">Adjustment</th>
                            <th class="text-center align-middle finishing-process" style="background-color: #C8E6C9;">Saldo Akhir</th>

                            <th class="text-center align-middle" style="background-color: #FFE5B4;">Saldo Awal</th>
                            <th class="text-center align-middle" style="background-color: #FFE5B4;">Terima</th>
                            <th class="text-center align-middle" style="background-color: #FFE5B4;">Keluar</th>
                            {{-- <th class="text-center align-middle" style="background-color: #FFE5B4;">Switching OUT</th>
                            <th class="text-center align-middle" style="background-color: #FFE5B4;">Switching IN</th> --}}
                            <th class="text-center align-middle" style="background-color: #FFE5B4;">Adjustment</th>
                            <th class="text-center align-middle" style="background-color: #FFE5B4;">Saldo Akhir</th>

                            <th class="text-center align-middle" style="background-color: lavender;">Saldo Awal</th>
                            <th class="text-center align-middle" style="background-color: lavender;">Terima</th>
                            <th class="text-center align-middle" style="background-color: lavender;">Keluar</th>
                            {{-- <th class="text-center align-middle" style="background-color: lavender;">Switching OUT</th>
                            <th class="text-center align-middle" style="background-color: lavender;">Switching IN</th> --}}
                            <th class="text-center align-middle" style="background-color: lavender;">Adjustment</th>
                            <th class="text-center align-middle" style="background-color: lavender;">Saldo Akhir</th>

                            <th class="text-center align-middle" style="background-color: lightyellow;">Saldo Awal</th>
                            <th class="text-center align-middle" style="background-color: lightyellow;">Terima</th>
                            <th class="text-center align-middle" style="background-color: lightyellow;">Keluar</th>
                            {{-- <th class="text-center align-middle" style="background-color: lightyellow;">Switching OUT</th>
                            <th class="text-center align-middle" style="background-color: lightyellow;">Switching IN</th> --}}
                            <th class="text-center align-middle" style="background-color: lightyellow;">Adjustment</th>
                            <th class="text-center align-middle" style="background-color: lightyellow;">Saldo Akhir</th>

                            <th class="text-center align-middle" style="background-color: #FFA94D;">Saldo Awal</th>
                            <th class="text-center align-middle" style="background-color: #FFA94D;">Terima Sewing</th>
                            <th class="text-center align-middle" style="background-color: #FFA94D;">Terima QC Finishing</th>
                            <th class="text-center align-middle" style="background-color: #FFA94D;">Terima Finishing</th>
                            <th class="text-center align-middle" style="background-color: #FFA94D;">Keluar QC Reject</th>
                            <th class="text-center align-middle" style="background-color: #FFA94D;">Keluar Packing</th>
                            <th class="text-center align-middle" style="background-color: #FFA94D;">Adjustment</th>
                            <th class="text-center align-middle" style="background-color: #FFA94D;">Saldo Akhir</th>
                            
                            <th class="text-center align-middle" style="background-color: pink;">Saldo Awal</th>
                            <th class="text-center align-middle" style="background-color: pink;">Terima</th>
                            <th class="text-center align-middle" style="background-color: pink;">Keluar Sewing</th>
                            <th class="text-center align-middle" style="background-color: pink;">Keluar Gudang Stok</th>
                            {{-- <th class="text-center align-middle" style="background-color: pink;">Switching OUT</th>
                            <th class="text-center align-middle" style="background-color: pink;">Switching IN</th> --}}
                            <th class="text-center align-middle" style="background-color: pink;">Adjustment</th>
                            <th class="text-center align-middle" style="background-color: pink;">Saldo Akhir</th>

                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th class="text-center" colspan="5">Total</th>
                            @for ($i = 1; $i <=104; $i++)
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
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables 2.0/dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables 2.0/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables 2.0/dataTables.fixedColumns.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-rowsgroup/dataTables.rowsGroup.js') }}"></script>
    <script src="{{ asset('plugins/export_excel_js/exceljs.min.js') }}"></script>
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

        function notif() {
            alert("Maaf, Fitur belum tersedia!");
        }
    </script>
    <script>
        $(document).ready(function() {
            dataTableReload();
        })

        function updateFinishingView() {
            const endDate = $('#end_date').val();

            if (!endDate) {
                return;
            }

            const batasTanggal = '2026-07-01';

            // Pastikan DataTable sudah ada
            if (!$.fn.DataTable.isDataTable('#datatable')) {
                return;
            }

            const table = $('#datatable').DataTable();

            const finishingOld = endDate < batasTanggal;

            // ==========================================
            // FINISHING LAMA
            // Kolom 32 - 39
            // ==========================================
            for (let col = 32; col <= 39; col++) {
                table.column(col).visible(finishingOld, false);
            }

            // ==========================================
            // FINISHING PER PROCESS
            // Kolom 40 - 79
            // ==========================================
            for (let col = 40; col <= 79; col++) {
                table.column(col).visible(!finishingOld, false);
            }
            // ==========================================
            // TRANSIT TERIMA QC REJECT
            // Kolom 95 - 102
            // ==========================================
            for (let col = 95; col <= 102; col++) {
                table.column(col).visible(!finishingOld, false);
            }

            // Sesuaikan ukuran kolom
            table.columns.adjust().draw(false);
        }

        $('#start_date, #end_date').on('change', function () {
            updateFinishingView();
        });

        function dataTableReload() {
            let start_date = $('#start_date').val();
            let end_date = $('#end_date').val();
            let buyer = $('#cbobuyer').val();

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
                paging: true,
                lengthMenu: [
                    [10, 25, 50, -1],
                    [10, 25, 50, "All"]
                ],
                pageLength: 10,
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
                    leftColumns: 5
                },
                ajax: {
                    url: '{{ route('show_mut_output') }}',
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
                        data: 'ws'
                    },
                    {
                        data: 'styleno'
                    },
                    {
                        data: 'color'
                    },
                    {
                        data: 'size'
                    },

                    {
                        data: 'saldo_awal_sewing'
                    },
                    {
                        data: 'qty_loading'
                    },
                    {
                        data: 'terima_gudang'
                    },
                    {
                        data: 'qty_in_subcont'
                    },
                    {
                        data: 'input_rework_sewing'
                    },
                    {
                        data: 'input_rework_spotcleaning'
                    },
                    {
                        data: 'input_rework_mending'
                    },
                    {
                        data: 'defect_sewing'
                    },
                    {
                        data: 'defect_spotcleaning'
                    },
                    {
                        data: 'defect_mending'
                    },
                    {
                        data: 'qty_sew_reject'
                    },
                    {
                        data: 'qty_sewing'
                    },
                    {
                        data: 'qty_out_subcont'
                    },
                    // {
                    //     data: 'sewing_switching_out'
                    // },
                    // {
                    //     data: 'sewing_switching_in'
                    // },
                    {
                        data: 'sewing_adjust'
                    },
                    {
                        data: 'saldo_akhir_sewing'
                    },


                    {
                        data: 'saldo_awal_finishing'
                    },
                    {
                        data: 'qty_sewing'
                    },
                    {
                        data: 'input_rework_sewing_f'
                    },
                    {
                        data: 'input_rework_spotcleaning_f'
                    },
                    {
                        data: 'input_rework_mending_f'
                    },
                    {
                        data: 'defect_sewing_f'
                    },
                    {
                        data: 'defect_spotcleaning_f'
                    },
                    {
                        data: 'defect_mending_f'
                    },
                    {
                        data: 'qty_fin_reject'
                    },
                    {
                        data: 'qty_finishing'
                    },
                    // {
                    //     data: 'qc_finishing_switching_out'
                    // },
                    // {
                    //     data: 'qc_finishing_switching_in'
                    // },
                    {
                        data: 'qc_finishing_adjust'
                    },
                    {
                        data: 'saldo_akhir_finishing'
                    },


                    {
                        data: 'saldo_awal_secondary_proses'
                    },
                    {
                        data: 'total_in_sp'
                    },
                    {
                        data: 'rework_sp'
                    },
                    {
                        data: 'defect_sp'
                    },
                    {
                        data: 'reject_sp'
                    },
                    {
                        data: 'rft_sp'
                    },
                    {
                        data: 'finishing_adjust'
                    },
                    {
                        data: 'saldo_akhir_secondary_proses'
                    },

                    {
                        data: 'saldo_awal_finishing_pasang_kancing'
                    },
                    {
                        data: 'total_in_sp_pasang_kancing'
                    },
                    {
                        data: 'rework_sp_pasang_kancing'
                    },
                    {
                        data: 'defect_sp_pasang_kancing'
                    },
                    {
                        data: 'reject_sp_pasang_kancing'
                    },
                    {
                        data: 'rft_sp_pasang_kancing'
                    },
                    {
                        data: 'finishing_adjust_pasang_kancing'
                    },
                    {
                        data: 'saldo_akhir_finishing_pasang_kancing'
                    },

                    {
                        data: 'saldo_awal_finishing_bartack'
                    },
                    {
                        data: 'total_in_sp_bartack'
                    },
                    {
                        data: 'rework_sp_bartack'
                    },
                    {
                        data: 'defect_sp_bartack'
                    },
                    {
                        data: 'reject_sp_bartack'
                    },
                    {
                        data: 'rft_sp_bartack'
                    },
                    {
                        data: 'finishing_adjust_bartack'
                    },
                    {
                        data: 'saldo_akhir_finishing_bartack'
                    },

                    {
                        data: 'saldo_awal_finishing_heatseal'
                    },
                    {
                        data: 'total_in_sp_heatseal'
                    },
                    {
                        data: 'rework_sp_heatseal'
                    },
                    {
                        data: 'defect_sp_heatseal'
                    },
                    {
                        data: 'reject_sp_heatseal'
                    },
                    {
                        data: 'rft_sp_heatseal'
                    },
                    {
                        data: 'finishing_adjust_heatseal'
                    },
                    {
                        data: 'saldo_akhir_finishing_heatseal'
                    },

                    {
                        data: 'saldo_awal_finishing_snap'
                    },
                    {
                        data: 'total_in_sp_snap'
                    },
                    {
                        data: 'rework_sp_snap'
                    },
                    {
                        data: 'defect_sp_snap'
                    },
                    {
                        data: 'reject_sp_snap'
                    },
                    {
                        data: 'rft_sp_snap'
                    },
                    {
                        data: 'finishing_adjust_snap'
                    },
                    {
                        data: 'saldo_akhir_finishing_snap'
                    },

                    {
                        data: 'saldo_awal_finishing_embro'
                    },
                    {
                        data: 'total_in_sp_embro'
                    },
                    {
                        data: 'rework_sp_embro'
                    },
                    {
                        data: 'defect_sp_embro'
                    },
                    {
                        data: 'reject_sp_embro'
                    },
                    {
                        data: 'rft_sp_embro'
                    },
                    {
                        data: 'finishing_adjust_embro'
                    },
                    {
                        data: 'saldo_akhir_finishing_embro'
                    },


                    {
                        data: 'saldo_awal_defect_sewing'
                    },
                    {
                        data: 'total_defect_sewing'
                    },
                    {
                        data: 'total_input_rework_sewing'
                    },
                    // {
                    //     data: 'defect_sewing_switching_out'
                    // },
                    // {
                    //     data: 'defect_sewing_switching_in'
                    // },
                    {
                        data: 'defect_sewing_adjust'
                    },
                    {
                        data: 'saldo_akhir_defect_sewing'
                    },


                    {
                        data: 'saldo_awal_defect_spotcleaning'
                    },
                    {
                        data: 'total_defect_spotcleaning'
                    },
                    {
                        data: 'total_input_rework_spotcleaning'
                    },
                    // {
                    //     data: 'defect_spotcleaning_switching_out'
                    // },
                    // {
                    //     data: 'defect_spotcleaning_switching_in'
                    // },
                    {
                        data: 'defect_spotcleaning_adjust'
                    },
                    {
                        data: 'saldo_akhir_defect_spotcleaning'
                    },


                    {
                        data: 'saldo_awal_defect_mending'
                    },
                    {
                        data: 'total_defect_mending'
                    },
                    {
                        data: 'total_input_rework_mending'
                    },
                    // {
                    //     data: 'defect_manding_switching_out'
                    // },
                    // {
                    //     data: 'defect_manding_switching_in'
                    // },
                    {
                        data: 'defect_mending_adjust'
                    },
                    {
                        data: 'saldo_akhir_mending'
                    },


                    {
                        data: 'qty_transit_saldo_awal'
                    },
                    {
                        data: 'qty_transit_terima_sewing'
                    },
                    {
                        data: 'qty_transit_terima_qc_finishing'
                    },
                    {
                        data: 'qty_transit_terima_finishing'
                    },
                    {
                        data: 'qty_transit_keluar_qc_reject'
                    },
                    {
                        data: 'qty_transit_keluar_packing'
                    },
                    {
                        data: 'qty_transit_adjustment'
                    },
                    {
                        data: 'qty_transit_keluar_saldo_akhir'
                    },

                    {
                        data: 'saldo_awal_reject'
                    },
                    {
                        data: 'qty_reject_in'
                    },
                    {
                        data: 'qty_reworked'
                    },
                    {
                        data: 'qty_rejected'
                    },
                    // {
                    //     data: 'qc_reject_switching_out'
                    // },
                    // {
                    //     data: 'qc_reject_switching_in'
                    // },
                    {
                        data: 'qc_reject_adjust'
                    },
                    {
                        data: 'saldo_akhir_qc_reject'
                    },
                ],
                footerCallback: function(row, data, start, end, display) {
                    let api = this.api();

                    let intVal = function(i) {
                        return typeof i === 'string' ?
                            i.replace(/[^0-9-]+/g, '') * 1 :
                            typeof i === 'number' ?
                            i :
                            0;
                    };

                    // mulai kolom ke-5
                    for (let col = 5; col <= 108; col++) {
                        let total = api
                            .column(col, {
                                search: 'applied'
                            })
                            .data()
                            .reduce(function(a, b) {
                                return intVal(a) + intVal(b);
                            }, 0);

                        // TANPA format ribuan
                        $(api.column(col).footer()).html(total);
                    }
                }
            });
            updateFinishingView();
        }

        function export_excel() {
            let start_date = $('#start_date').val();
            let end_date = $('#end_date').val();
            let buyer = $('#cbobuyer').val();
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
                url: '{{ route('export_excel_mut_output') }}',
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
                    link.download = "Laporan Mutasi Sewing " + start_date + " _ " + end_date + ".xlsx";
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
