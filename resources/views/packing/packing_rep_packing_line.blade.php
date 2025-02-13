@extends('layouts.index')

@section('custom-link')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">

    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

    <style type="text/css">
        input::-webkit-outer-spin-button,
        input::-webkit-inner-spin-button {
            /* display: none; <- Crashes Chrome on hover */
            -webkit-appearance: none;
            margin: 0;
            /* <-- Apparently some margin are still there even though it's hidden */
        }

        input[type=number] {
            -moz-appearance: textfield;
            /* Firefox */
        }
    </style>
@endsection

@section('content')
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-list"></i> Summary Packing Line</h5>
        </div>
        <div class="card-body">
            <div class="d-flex align-items-end gap-3 mb-3">
                <div class="mb-3" style="width: 300px;">
                    <select class="form-control select2bs4" id="cbopil" name="cbopil" style="width: 100%;"
                        onchange="handleSelectChange()">
                        <option selected="selected" value="" disabled="true">Pilih Tipe</option>
                        @foreach ($data_tipe as $data_t)
                            <option value="{{ $data_t->isi }}">
                                {{ $data_t->tampil }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="d-flex align-items-end gap-3 mb-3">
                <div class="mb-3" id= "tgl_awal_f" style="display: none;">
                    <label class="form-label"><small><b>Tgl Input Packing Line</b></small></label>
                    <input type="date" class="form-control form-control-sm " id="tgl-awal" name="tgl_awal"
                        value="{{ $tgl_skrg }}">
                </div>
                <div class="mb-3" id= "tgl_akhir_f" style="display: none;">
                    <label class="form-label"><small><b>Tgl Input Packing Line</b></small></label>
                    <input type="date" class="form-control form-control-sm" id="tgl-akhir" name="tgl_akhir"
                        value="{{ $tgl_skrg }}">
                </div>
                <div class="mb-3" id="txt_buyer_f" style="display: none;">
                    <select class="form-control select2bs4" id="cbobuyer" name="cbobuyer" style="width: 300px;">
                        <option selected="selected" value="" disabled="true">Pilih Buyer</option>
                        @foreach ($data_po as $data_p)
                            <option value="{{ $data_p->isi }}">
                                {{ $data_p->tampil }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3" id="but_cari" name="but_cari" style="display: none;">
                    <a class="btn btn-outline-primary position-relative btn-sm" onclick="cari()">
                        <i class="fas fa-search"></i>
                        Cari
                    </a>
                </div>
                <div class="mb-3">
                    <a onclick="export_excel()" class="btn btn-outline-success position-relative btn-sm" id="but_export"
                        name="but_export">
                        <i class="fas fa-file-excel fa-sm"></i>
                        Export Excel
                    </a>
                </div>

            </div>
            <div class="table-responsive" id="table_range" style="display: none;">
                <table id="datatable_range" class="table table-bordered table-striped table-sm w-100 text-nowrap">
                    <thead class="table-primary">
                        <tr style='text-align:center; vertical-align:middle'>
                            <th>Line</th>
                            <th>Buyer</th>
                            <th>WS</th>
                            <th>Color</th>
                            <th>Size</th>
                            <th>Qty</th>
                        </tr>
                    </thead>
                </table>
            </div>
            <div class="table-responsive" id="table_buyer" style="display: none;">
                <table id="datatable_buyer" class="table table-bordered table-striped table-sm w-100 text-nowrap">
                    <thead class="table-success">
                        <tr style='text-align:center; vertical-align:middle'>
                            <th>Buyer</th>
                            <th>WS</th>
                            <th>Color</th>
                            <th>Size</th>
                            <th>Qty</th>
                        </tr>
                    </thead>
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
            containerCssClass: 'form-control-sm rounded'
        });
    </script>
    <script>
        function notif() {
            alert("Maaf, Fitur belum tersedia!");
        }
    </script>
    <script>
        $(document).ready(() => {
            $("#cbopil").val('').trigger('change');
        });


        function handleSelectChange() {
            let selectElement = document.getElementById('cbopil');
            let selectedValue = selectElement.value;
            let tglAwalDiv = document.getElementById('tgl_awal_f');
            let tglAkhirDiv = document.getElementById('tgl_akhir_f');
            let txtBuyerDiv = document.getElementById('txt_buyer_f');
            let but_cariDiv = document.getElementById('but_cari');
            let t_rangeDiv = document.getElementById('table_range');
            let t_buyerDiv = document.getElementById('table_buyer');

            // Hide all inputs initially
            tglAwalDiv.style.display = 'none';
            tglAkhirDiv.style.display = 'none';
            txtBuyerDiv.style.display = 'none';
            but_cariDiv.style.display = 'none';
            t_rangeDiv.style.display = 'none';
            t_buyerDiv.style.display = 'none';

            if (selectedValue === 'RANGE') {
                tglAwalDiv.style.display = 'block';
                tglAkhirDiv.style.display = 'block';
                but_cariDiv.style.display = 'block';
                t_rangeDiv.style.display = 'block';
            } else if (selectedValue === 'BUYER') {
                txtBuyerDiv.style.display = 'block';
                but_cariDiv.style.display = 'block';
                t_buyerDiv.style.display = 'block';
            }
        }

        function cari() {
            let selectElement = document.getElementById('cbopil');
            let selectedValue = selectElement.value;
            if (selectedValue === 'RANGE') {
                dataTableRangeReload();
            } else if (selectedValue === 'BUYER') {
                dataTableBuyerReload();
            }
        }


        function dataTableRangeReload() {

            $('#datatable_range thead tr').clone(true).appendTo('#datatable_range thead');
            $('#datatable_range thead tr:eq(1) th').each(function(i) {
                var title = $(this).text();
                $(this).html('<input type="text" class="form-control form-control-sm"/>');
                $('input', this).on('keyup change', function() {
                    if (datatable_range.column(i).search() !== this.value) {
                        datatable_range
                            .column(i)
                            .search(this.value)
                            .draw();
                    }
                });
            });

            let datatable_range = $("#datatable_range").DataTable({
                ordering: false,
                processing: true,
                serverSide: true,
                destroy: true,
                paging: false,
                searching: true,
                scrollY: '300px',
                scrollX: '300px',
                scrollCollapse: true,
                ajax: {
                    url: '{{ route('packing_rep_packing_line_sum_range') }}',
                    data: function(d) {
                        d.dateFrom = $('#tgl-awal').val();
                        d.dateTo = $('#tgl-akhir').val();
                    },
                },
                columns: [{
                        data: 'sew_line'

                    },
                    {
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
                        data: 'qty'
                    },
                ],
                columnDefs: [{
                        "className": "dt-left",
                        "targets": [0, 1, 2, 3, 4] // Apply dt-left to columns 0 to 4
                    },
                    {
                        "className": "dt-right",
                        "targets": [5] // Apply dt-right to column 5
                    }
                ]
            }, );
        }


        function dataTableBuyerReload() {

            $('#datatable_buyer thead tr').clone(true).appendTo('#datatable_buyer thead');
            $('#datatable_buyer thead tr:eq(1) th').each(function(i) {
                var title = $(this).text();
                $(this).html('<input type="text" class="form-control form-control-sm"/>');
                $('input', this).on('keyup change', function() {
                    if (datatable_buyer.column(i).search() !== this.value) {
                        datatable_buyer
                            .column(i)
                            .search(this.value)
                            .draw();
                    }
                });
            });

            let datatable_buyer = $("#datatable_buyer").DataTable({
                ordering: false,
                processing: true,
                serverSide: true,
                destroy: true,
                paging: false,
                searching: true,
                scrollY: '300px',
                scrollX: '300px',
                scrollCollapse: true,
                ajax: {
                    url: '{{ route('packing_rep_packing_line_sum_buyer') }}',
                    data: function(d) {
                        d.cbobuyer = $('#cbobuyer').val();
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
                        data: 'qty'
                    },
                ],
                columnDefs: [{
                        "className": "dt-left",
                        "targets": [0, 1, 2, 3] // Apply dt-left to columns 0 to 4
                    },
                    {
                        "className": "dt-right",
                        "targets": [4] // Apply dt-right to column 5
                    }
                ]
            }, );
        }


        function export_excel() {

            let selectElement = document.getElementById('cbopil');
            let selectedValue = selectElement.value;
            if (selectedValue === 'RANGE') {
                export_excel_range();
            } else if (selectedValue === 'BUYER') {
                export_excel_buyer();
            }

        }

        function export_excel_range() {
            let from = document.getElementById("tgl-awal").value;
            let to = document.getElementById("tgl-akhir").value;

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
                url: '{{ route('export_excel_rep_packing_line_sum_range') }}',
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
                        link.download = "Laporan Packing Line " +
                            from + " sampai " +
                            to + ".xlsx";
                        link.click();

                    }
                },
            });
        }

        function export_excel_buyer() {
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
                url: '{{ route('export_excel_rep_packing_line_sum_buyer') }}',
                data: {
                    buyer: buyer
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
                        link.download = "Laporan Packing Line " +
                            buyer + ".xlsx";
                        link.click();

                    }
                },
            });
        }
    </script>
@endsection
