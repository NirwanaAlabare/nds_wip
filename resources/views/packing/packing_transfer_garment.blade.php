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
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-shirt"></i> Transfer Garment</h5>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <a href="{{ route('create-transfer-garment') }}" class="btn btn-outline-primary position-relative">
                    <i class="fas fa-plus"></i>
                    Baru
                </a>
            </div>

            <div class="d-flex align-items-end gap-3 mb-3">
                <div class="mb-3">
                    <label class="form-label"><small><b>Tgl Awal</b></small></label>
                    <input type="date" class="form-control form-control-sm " id="tgl-awal" name="tgl_awal"
                        oninput="dataTableReload()" value="{{ date('Y-m-d') }}">
                </div>
                <div class="mb-3">
                    <label class="form-label"><small><b>Tgl Akhir</b></small></label>
                    <input type="date" class="form-control form-control-sm" id="tgl-akhir" name="tgl_akhir"
                        oninput="dataTableReload()" value="{{ date('Y-m-d') }}">
                </div>
                <div class="mb-3">
                    <a onclick="export_excel_bpb()" class="btn btn-outline-success position-relative btn-sm">
                        <i class="fas fa-file-excel fa-sm"></i>
                        Export Excel
                    </a>
                </div>
            </div>

            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-striped table-sm w-100">
                    <thead>
                        <tr style='text-align:center; vertical-align:middle'>
                            <th>No. Transaksi</th>
                            <th>Tgl. Transaksi</th>
                            <th>WS</th>
                            <th>Qty</th>
                            <th>Line Asal</th>
                            <th>PO</th>
                            <th>Status</th>
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
    <script>
        function notif() {
            alert("Maaf, Fitur belum tersedia!");
        }
    </script>
    <script type="text/javascript">
        function setinisial() {
            let lok = $('#txtlok').val();
            let tingkat = $('#txttingkat').val();
            let baris = $('#txtbaris').val();
            let kode = lok.toUpperCase() + '.' + baris + '.' + tingkat;

            $('#txtkode_lok').val(kode);
        }
    </script>
    <script>
        function reset() {
            $("#form").trigger("reset");
        }
    </script>
    <script>
        let datatable = $("#datatable").DataTable({
            ordering: true,
            processing: true,
            serverSide: true,
            paging: true,
            searching: true,
            ajax: {
                url: '{{ route('master-lokasi-fg-stock') }}',
                data: function(d) {
                    d.dateFrom = $('#tgl-awal').val();
                    d.dateTo = $('#tgl-akhir').val();
                },
            },
            columns: [{
                    data: 'kode_lok_fg_stok'

                }, {
                    data: 'lokasi'
                },
                {
                    data: 'baris'
                },
                {
                    data: 'tingkat'
                },
                {
                    data: 'baris'
                },
            ],
            columnDefs: [{
                    targets: [4],
                    render: (data, type, row, meta) => {
                        return `
                    <div
                    class='d-flex gap-1 justify-content-center'>
                    <a class='btn btn-warning btn-sm' data-bs-toggle='tooltip' onclick='notif()'><i class='fas fa-edit'></i></a>
                    <a class='btn btn-success btn-sm' data-bs-toggle='tooltip' onclick='notif()'><i class='fas fa-lock'></i></a>
                    </div>
                        `;
                    }
                },
                // <a class='btn btn-warning btn-sm' href='{{ route('create-dc-in') }}/` +
            //             row.id +
            //             `' data-bs-toggle='tooltip'><i class='fas fa-edit'></i></a>
                {
                    "className": "dt-center",
                    "targets": "_all"
                },



            ]
        });
    </script>
@endsection
