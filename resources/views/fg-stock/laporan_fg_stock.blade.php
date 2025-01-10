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
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-list"></i> List Laporan</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class='col-md-3'>
                    <div class='form-group'>
                        <label class='form-label'><small><b>Jenis Laporan</b></small></label>
                        <select class="form-select form-select-sm" id="cbojns_lap" name="cbojns_lap" style="width: 100%;">
                            <option selected="selected" value="" disabled="true">- Pilih Jenis Laporan -</option>
                            @foreach ($data_laporan as $datalaporan)
                                <option value="{{ $datalaporan->isi }}">
                                    {{ $datalaporan->tampil }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="d-flex align-items-end gap-3 mb-3">
                    <div class="mb-3">
                        <label class="form-label"><small><b>Tgl Awal</b></small></label>
                        <input type="date" class="form-control form-control-sm " id="tgl-awal" name="tgl_awal"
                            value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><small><b>Tgl Akhir</b></small></label>
                        <input type="date" class="form-control form-control-sm" id="tgl-akhir" name="tgl_akhir"
                            value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="mb-3">
                        <a onclick="cari()" class="btn btn-outline-primary position-relative btn-sm">
                            <i class="fas fa-search fa-sm"></i>
                            Cari
                        </a>
                    </div>
                    <div class="mb-3">
                        <a onclick="export_excel()" class="btn btn-outline-success position-relative btn-sm">
                            <i class="fas fa-file-excel fa-sm"></i>
                            Export Excel
                        </a>
                    </div>
                </div>

                <div class="table-responsive" id="table_penerimaan" style="display: none;">
                    <table id="datatable_penerimaan" class="table table-bordered table-sm w-100 table-hover display nowrap">
                        <thead class="table-primary">
                            <tr style='text-align:center; vertical-align:middle'>
                                <th>No. Trans</th>
                                <th>Tgl. Trans</th>
                                <th>Lokasi</th>
                                <th>No. Karton</th>
                                <th>Buyer</th>
                                <th>Brand</th>
                                <th>Style</th>
                                <th>Grade</th>
                                <th>WS</th>
                                <th>Color</th>
                                <th>Size</th>
                                <th>Qty</th>
                                <th>Sumber</th>
                            </tr>
                        </thead>
                    </table>
                </div>

                <div class="table-responsive" id="table_pengeluaran" style="display: none;">
                    <table id="datatable_pengeluaran"
                        class="table table-bordered table-sm w-100 table-hover display nowrap">
                        <thead class="table-primary">
                            <tr style='text-align:center; vertical-align:middle'>
                                <th>No. Trans</th>
                                <th>Tgl. Trans</th>
                                <th>Lokasi</th>
                                <th>No. Karton</th>
                                <th>Buyer</th>
                                <th>Brand</th>
                                <th>Style</th>
                                <th>Grade</th>
                                <th>WS</th>
                                <th>Color</th>
                                <th>Size</th>
                                <th>Qty</th>
                                <th>Tujuan</th>
                                <th>Tujuan Pengeluaran</th>
                            </tr>
                        </thead>
                    </table>
                </div>

                <div class="table-responsive" id="table_mutasi" style="display: none;">
                    <table id="datatable_mutasi" class="table table-bordered table-sm w-100 table-hover display nowrap">
                        <thead class="table-primary">
                            <tr style='text-align:center; vertical-align:middle'>
                                <th>ID So Det</th>
                                <th>Buyer</th>
                                <th>Product Group</th>
                                <th>Product Item</th>
                                <th>WS</th>
                                <th>Brand</th>
                                <th>Style</th>
                                <th>Color</th>
                                <th>Size</th>
                                <th>Dest</th>
                                <th>Grade</th>
                                <th>No. Carton</th>
                                <th>Lokasi</th>
                                <th>Saldo Awal</th>
                                <th>Penerimaan</th>
                                <th>Pengeluaran</th>
                                <th>Saldo Akhir</th>
                            </tr>
                        </thead>
                    </table>
                </div>

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
        });
    </script>
    <script>
        $(document).ready(function() {

            $('#cbojns_lap').val('').trigger('change');

            let datatablePenerimaan = $('#datatable_penerimaan').DataTable();
            let datatablePengeluaran = $('#datatable_pengeluaran').DataTable();
            let datatableMutasi = $('#datatable_mutasi').DataTable();

            $('#cbojns_lap').change(function() {
                // Clear all tables
                datatablePenerimaan.clear().draw();
                datatablePengeluaran.clear().draw();
                datatableMutasi.clear().draw();
                // Hide all tables initially
                $('#table_penerimaan').hide();
                $('#table_pengeluaran').hide();
                $('#table_mutasi').hide();
                // Show the appropriate table based on the selected value
                var selectedValue = $(this).val();
                if (selectedValue === 'Penerimaan') {
                    $('#table_penerimaan').show();
                } else if (selectedValue === 'Pengeluaran') {
                    $('#table_pengeluaran').show();
                } else if (selectedValue === 'Mutasi') {
                    $('#table_mutasi').show();
                }
            });
        });

        function cari() {
            let from = document.getElementById("tgl-awal").value;
            let to = document.getElementById("tgl-akhir").value;
            let cbojns_lap = document.getElementById("cbojns_lap").value;
            if (cbojns_lap == '') {
                Swal.fire({
                    title: 'Jenis Laporan Belum Di isi!',
                    icon: "warning",
                    showConfirmButton: true,
                });
            } else if (cbojns_lap == 'Penerimaan') {
                let datatable = $("#datatable_penerimaan").DataTable({
                    ordering: false,
                    processing: true,
                    serverSide: true,
                    paging: true,
                    searching: true,
                    destroy: true,
                    scrollX: true,
                    ajax: {
                        url: '{{ route('bpb-fg-stock') }}',
                        data: function(d) {
                            d.dateFrom = $('#tgl-awal').val();
                            d.dateTo = $('#tgl-akhir').val();
                        },
                    },
                    columns: [{
                            data: 'no_trans'

                        }, {
                            data: 'tgl_terima_fix'
                        },
                        {
                            data: 'lokasi'
                        },
                        {
                            data: 'no_carton'
                        },
                        {
                            data: 'buyer'
                        },
                        {
                            data: 'brand'
                        },
                        {
                            data: 'styleno'
                        },
                        {
                            data: 'grade'
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
                            data: 'qty'
                        },
                        {
                            data: 'sumber_pemasukan'
                        },
                    ],
                    columnDefs: [{
                        "className": "dt-center",
                        "targets": "_all"
                    }, ]
                });
            } else if (cbojns_lap == 'Pengeluaran') {
                let datatable = $("#datatable_pengeluaran").DataTable({
                    ordering: false,
                    processing: true,
                    serverSide: true,
                    paging: true,
                    searching: true,
                    destroy: true,
                    scrollX: true,
                    ajax: {
                        url: '{{ route('bppb-fg-stock') }}',
                        data: function(d) {
                            d.dateFrom = $('#tgl-awal').val();
                            d.dateTo = $('#tgl-akhir').val();
                        },
                    },
                    columns: [{
                            data: 'no_trans_out'

                        }, {
                            data: 'tgl_pengeluaran_fix'
                        },
                        {
                            data: 'lokasi'
                        },
                        {
                            data: 'no_carton'
                        },
                        {
                            data: 'buyer'
                        },
                        {
                            data: 'brand'
                        },
                        {
                            data: 'styleno'
                        },
                        {
                            data: 'grade'
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
                            data: 'qty_out'
                        },
                        {
                            data: 'tujuan'
                        },
                        {
                            data: 'tujuan_pengeluaran'
                        },
                    ],
                    columnDefs: [{
                        "className": "dt-center",
                        "targets": "_all"
                    }, ]
                });
            } else if (cbojns_lap == 'Mutasi') {
                let datatable = $("#datatable_mutasi").DataTable({
                    ordering: false,
                    processing: true,
                    serverSide: true,
                    paging: true,
                    searching: true,
                    destroy: true,
                    scrollX: true,
                    ajax: {
                        url: '{{ route('rep_mutasi_fg_stock') }}',
                        data: function(d) {
                            d.dateFrom = $('#tgl-awal').val();
                            d.dateTo = $('#tgl-akhir').val();
                        },
                    },
                    columns: [{
                            data: 'id_so_det'

                        }, {
                            data: 'buyer'
                        },
                        {
                            data: 'product_group'
                        },
                        {
                            data: 'product_item'
                        },
                        {
                            data: 'ws'
                        },
                        {
                            data: 'brand'
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
                            data: 'dest'
                        },
                        {
                            data: 'grade'
                        },
                        {
                            data: 'no_carton'
                        },
                        {
                            data: 'lokasi'
                        },
                        {
                            data: 'qty_awal'
                        },
                        {
                            data: 'qty_in'
                        },
                        {
                            data: 'qty_out'
                        },
                        {
                            data: 'saldo_akhir'
                        },
                    ],
                    columnDefs: [{
                        "className": "dt-center",
                        "targets": "_all"
                    }, ]
                });
            }
        }

        function notif() {
            alert("Maaf, Fitur belum tersedia!");
        }

        function export_excel() {
            let from = document.getElementById("tgl-awal").value;
            let to = document.getElementById("tgl-akhir").value;
            let cbojns_lap = document.getElementById("cbojns_lap").value;
            console.log(cbojns_lap);
            if (cbojns_lap == '') {
                Swal.fire({
                    title: 'Jenis Laporan Belum Di isi!',
                    icon: "warning",
                    showConfirmButton: true,
                });
            } else {
                Swal.fire({
                    title: 'Please Wait...',
                    html: 'Exporting Data...',
                    didOpen: () => {
                        Swal.showLoading()
                    },
                    allowOutsideClick: false,
                });

                if (cbojns_lap == 'Penerimaan') {
                    $.ajax({
                        type: "get",
                        url: '{{ route('export_excel_bpb_fg_stok') }}',
                        data: {
                            from: from,
                            to: to
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
                                link.download = from + " sampai " +
                                    to + "Laporan Penerimaan FG Stock.xlsx";
                                link.click();

                            }
                        },
                    });
                } else if (cbojns_lap == 'Pengeluaran') {
                    $.ajax({
                        type: "get",
                        url: '{{ route('export_excel_bppb_fg_stok') }}',
                        data: {
                            from: from,
                            to: to
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
                                link.download = from + " sampai " +
                                    to + "Laporan Pengeluaran FG Stock.xlsx";
                                link.click();

                            }
                        },
                    });
                } else
                if (cbojns_lap == 'Mutasi') {
                    $.ajax({
                        type: "get",
                        url: '{{ route('export_excel_mutasi_fg_stok') }}',
                        data: {
                            from: from,
                            to: to
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
                                link.download = from + " sampai " +
                                    to + "Laporan Mutasi FG Stock.xlsx";
                                link.click();

                            }
                        },
                    });
                }

            }
        }
    </script>
@endsection
