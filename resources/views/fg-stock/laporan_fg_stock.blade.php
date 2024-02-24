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
                        <a onclick="notif()" class="btn btn-outline-primary position-relative btn-sm">
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
            </div>


            {{-- <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-sm w-100 table-hover display nowrap">
                    <thead class="table-primary">
                        <tr style='text-align:center; vertical-align:middle'>
                            <th>No. Trans</th>
                            <th>Tgl. Trans</th>
                            <th>Lokasi</th>
                            <th>No. Karton</th>
                            <th>Buyer</th>
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
            </div> --}}
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
        function notif() {
            alert("Maaf, Fitur belum tersedia!");
        }

        function export_excel() {
            let from = document.getElementById("tgl-awal").value;
            let to = document.getElementById("tgl-akhir").value;
            let cbojns_lap = document.getElementById("cbojns_lap").value;

            console.log(cbojns_lap);

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
            } else
            if (cbojns_lap == 'Pengeluaran') {
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
    </script>
@endsection
