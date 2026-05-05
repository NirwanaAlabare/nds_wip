@extends('layouts.index')

@section('custom-link')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">

    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

    <style>
        .modal-dialog.modal-fullscreen {
            width: 100vw;
            max-width: 100%;
            height: 100vh;
            margin: 0;
        }

        .modal-dialog.modal-fullscreen .modal-content {
            height: 100vh;
            border-radius: 0;
        }   
    </style>
@endsection

@section('content')
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"> Master Barang</h5>
        </div>
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-end gap-3 mb-3">
                <div class="d-flex align-items-end gap-3 mb-3">
                    <div>
                        <label class='form-label'><small>Kategori</small></label>
                        <select class="form-select form-select-sm" id="kategori" name="kategori" style="width: 100%;">
                            <option selected="selected" value="" disabled="true">- Pilih Kategori -</option>
                            <option value="FABRIC">FABRIC</option>
                            <option value="ACCESORIES">ACCESORIES</option>
                            <option value="FG">FG</option>
                        </select>
                    </div>
                    <div>
                        <button class="btn btn-primary btn-sm" onclick="dataTableReload()"><i class="fa fa-search"></i></button>
                    </div>
                </div>
                <div class="d-flex align-items-end gap-3 mb-3">
                    <div class="mb-3">
                        <button class="btn btn-success btn-sm" id="exportExcel" data-bs-toggle="tooltip" data-bs-title="Export Excel" onclick="exportExcel()" disabled><i class="fa fa-file-excel"></i></button>
                    </div>
                </div>
            </div>
            <div id="table-fabric" class="table-responsive">
                <table id="datatable-fabric" class="table table-bordered table-striped table-hover table w-100">
                    <thead class="bg-sb">
                        <tr>
                            <th>Barcode</th>
                            <th>Lokasi</th>
                            <th>Buyer</th>
                            <th>Keterangan</th>
                            <th>Jenis Item</th>
                            <th>Warna</th>
                            <th>Lot</th>
                            <th>No Roll</th>
                            <th>Qty</th>
                            <th>Satuan</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
            <div id="table-accesories" class="table-responsive d-none">
                <table id="datatable-accesories" class="table table-bordered table-striped w-100">
                    <thead class="bg-sb">
                        <tr>
                            <th>Barcode</th>
                            <th>No Box/Koli</th>
                            <th>Buyer</th>
                            <th>Worksheet</th>
                            <th>Nama Barang</th>
                            <th>Kode</th>
                            <th>Warna</th>
                            <th>Size</th>
                            <th>Qty</th>
                            <th>Satuan</th>
                            <th>Qty KGM</th>
                            <th>Keterangan</th>
                            <th>Lokasi</th>
                            <th>Action</th>
                    </thead>
                </table>
            </div>
            <div id="table-fg" class="table-responsive d-none">
                <table id="datatable-fg" class="table table-bordered table-striped w-100">
                    <thead class="bg-sb">
                        <tr>
                            <th>Barcode</th>
                            <th>No Koli</th>
                            <th>Buyer</th>
                            <th>No WS</th>
                            <th>Style</th>
                            <th>Product Item</th>
                            <th>Warna</th>
                            <th>Size</th>
                            <th>Grade</th>
                            <th>Qty</th>
                            <th>Satuan</th>
                            <th>Keterangan</th>
                            <th>Lokasi</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalFabric" tabindex="-1">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content">
            
            <div class="modal-header">
                <h5 class="modal-title">Detail History</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div id="contentFabric">
                    <div id="table-fabric-history" class="table-responsive">
                        <table id="datatable-fabric-history" class="table table-bordered table-striped table-hover table w-100">
                            <thead class="bg-sb">
                                <tr>
                                    <th>Barcode</th>
                                    <th>Lokasi</th>
                                    <th>Buyer</th>
                                    <th>Keterangan</th>
                                    <th>Jenis Item</th>
                                    <th>Warna</th>
                                    <th>Lot</th>
                                    <th>No Roll</th>
                                    <th>Qty</th>
                                    <th>Satuan</th>
                                </tr>
                            </thead>
                        </table>
                    </div>

                    <div id="table-fabric-history-detail" class="table-responsive mt-5">
                        <table id="datatable-fabric-history-detail" class="table table-bordered table-striped table-hover table w-100">
                            <thead class="bg-sb">
                                <tr>
                                    <th>Jenis Tipe</th>
                                    <th>No. BPB</th>
                                    <th>Tgl BPB</th>
                                    <th>Barcode</th>
                                    <th>Lokasi</th>
                                    <th>Buyer</th>
                                    <th>Keterangan</th>
                                    <th>Jenis Item</th>
                                    <th>Warna</th>
                                    <th>Lot</th>
                                    <th>No Roll</th>
                                    <th>Qty</th>
                                    <th>Satuan</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>

            </div>
        </div>
    </div>

    <div class="modal fade" id="modalAccesories" tabindex="-1">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail History</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div id="contentAccesories">
                        <div id="table-accesories-history" class="table-responsive">
                            <table id="datatable-accesories-history" class="table table-bordered table-striped table-hover table w-100">
                                <thead class="bg-sb">
                                    <tr>
                                        <th>Barcode</th>
                                        <th>No Box/Koli</th>
                                        <th>Buyer</th>
                                        <th>Worksheet</th>
                                        <th>Nama Barang</th>
                                        <th>Kode</th>
                                        <th>Warna</th>
                                        <th>Size</th>
                                        <th>Qty</th>
                                        <th>Satuan</th>
                                        <th>Qty KGM</th>
                                        <th>Keterangan</th>
                                        <th>Lokasi</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>

                        <div id="table-accesories-history-detail" class="table-responsive mt-5">
                            <table id="datatable-accesories-history-detail" class="table table-bordered table-striped table-hover table w-100">
                                <thead class="bg-sb">
                                    <tr>
                                        <th>Jenis Tipe</th>
                                        <th>No. BPB</th>
                                        <th>Tgl BPB</th>
                                        <th>Barcode</th>
                                        <th>No Box/Koli</th>
                                        <th>Buyer</th>
                                        <th>Worksheet</th>
                                        <th>Nama Barang</th>
                                        <th>Kode</th>
                                        <th>Warna</th>
                                        <th>Size</th>
                                        <th>Qty</th>
                                        <th>Satuan</th>
                                        <th>Qty KGM</th>
                                        <th>Keterangan</th>
                                        <th>Lokasi</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalFg" tabindex="-1">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail History</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div id="contentFg">
                        <div id="table-fg-history" class="table-responsive">
                            <table id="datatable-fg-history" class="table table-bordered table-striped table-hover table w-100">
                                <thead class="bg-sb">
                                    <tr>
                                        <th>Barcode</th>
                                        <th>No Koli</th>
                                        <th>Buyer</th>
                                        <th>No WS</th>
                                        <th>Style</th>
                                        <th>Product Item</th>
                                        <th>Warna</th>
                                        <th>Size</th>
                                        <th>Grade</th>
                                        <th>Qty</th>
                                        <th>Satuan</th>
                                        <th>Keterangan</th>
                                        <th>Lokasi</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>

                        <div id="table-fg-history-detail" class="table-responsive mt-5">
                            <table id="datatable-fg-history-detail" class="table table-bordered table-striped table-hover table w-100">
                                <thead class="bg-sb">
                                    <tr>
                                        <th>Jenis Tipe</th>
                                        <th>No. BPB</th>
                                        <th>Tgl BPB</th>
                                        <th>Barcode</th>
                                        <th>No Koli</th>
                                        <th>Buyer</th>
                                        <th>No WS</th>
                                        <th>Style</th>
                                        <th>Product Item</th>
                                        <th>Warna</th>
                                        <th>Size</th>
                                        <th>Grade</th>
                                        <th>Qty</th>
                                        <th>Satuan</th>
                                        <th>Keterangan</th>
                                        <th>Lokasi</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('custom-script')
    <!-- DataTables  & Plugins -->
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>

    <!-- Select2 -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        $('.select2').select2()
        $('.select2bs4').select2({
            theme: 'bootstrap4',
            dropdownParent: $("#editMejaModal")
        })
    </script>

    <script>

        let tableFabric, tableAccesories, tableFg;

        document.addEventListener("DOMContentLoaded", () => {

            // set tanggal default TANPA trigger
            let oneWeeksBefore = new Date(new Date().setDate(new Date().getDate() - 7));
            let oneWeeksBeforeFull = oneWeeksBefore.toISOString().slice(0,10);

            $("#tgl-awal").val(oneWeeksBeforeFull);
            $("#tgl-akhir").val(new Date().toISOString().slice(0,10));

            // ================= INIT DATATABLE =================
            tableFabric = $("#datatable-fabric").DataTable({
                processing: true,
                serverSide: true,
                ordering: false,
                ajax: {
                    url: '{{ route('master-barang-per-kategori') }}',
                    data: d => {
                        d.kategori = $("#kategori").val();
                    }
                },
                columns: [
                    {
                        data: 'barcode'
                    },
                    {
                        data: 'lokasi'
                    },
                    {
                        data: 'buyer'
                    },
                    {
                        data: 'keterangan'
                    },
                    {
                        data: 'jenis_item'
                    },
                    {
                        data: 'warna'
                    },
                    {
                        data: 'lot'
                    },
                    {
                        data: 'no_roll'
                    },
                    {
                        data: 'qty_saat_ini',
                        className: 'text-end',
                        render: function(data, type, row) {
                            return parseFloat(data).toFixed(2);
                        }
                    },
                    {
                        data: 'satuan'
                    },
                    {
                        data: 'barcode'
                    },
                ],
                columnDefs: [
                    {
                        targets: [10],
                        render: (data, type, row, meta) => {

                            let btnDetail = `
                                <button 
                                    class="btn btn-primary btn-sm btn-detail"
                                    data-barcode="${row.barcode}"
                                >
                                    <i class="fa-solid fa-list"></i>
                                </button>
                            `;

                            return `
                                <div class="d-flex gap-1 justify-content-center">
                                    ${btnDetail}
                                </div>
                            `;
                        }
                    },
                    {
                        targets: '_all',
                        className: 'text-nowrap'
                    }
                ],
            });

            tableAccesories = $("#datatable-accesories").DataTable({
                processing: true,
                serverSide: true,
                ordering: false,
                ajax: {
                    url: '{{ route('master-barang-per-kategori') }}',
                    data: d => {
                        d.kategori = $("#kategori").val();
                    }
                },
                columns: [
                    {
                        data: 'barcode'
                    },
                    {
                        data: 'no_box'
                    },
                    {
                        data: 'buyer'
                    },
                    {
                        data: 'worksheet'
                    },
                    {
                        data: 'nama_barang'
                    },
                    {
                        data: 'kode'
                    },
                    {
                        data: 'warna'
                    },
                    {
                        data: 'size'
                    },
                    {
                        data: 'qty_saat_ini',
                        className: 'text-end',
                        render: function(data, type, row) {
                            return parseFloat(data).toFixed(2);
                        }
                    },
                    {
                        data: 'satuan'
                    },
                    {
                        data: 'qty_kgm_saat_ini',
                        className: 'text-end',
                        render: function(data, type, row) {
                            return parseFloat(data).toFixed(2);
                        }
                    },
                    {
                        data: 'keterangan'
                    },
                    {
                        data: 'lokasi'
                    },
                    {
                        data: 'barcode'
                    },
                ],
                columnDefs: [
                    {
                        targets: [13],
                        render: (data, type, row, meta) => {

                            let btnDetail = `
                                <button 
                                    class="btn btn-primary btn-sm btn-detail"
                                    data-barcode="${row.barcode}"
                                >
                                    <i class="fa-solid fa-list"></i>
                                </button>
                            `;

                            return `
                                <div class="d-flex gap-1 justify-content-center">
                                    ${btnDetail}
                                </div>
                            `;
                        }
                    },
                    {
                        targets: '_all',
                        className: 'text-nowrap'
                    }
                ],
            });

            tableFg = $("#datatable-fg").DataTable({
                processing: true,
                serverSide: true,
                ordering: false,
                ajax: {
                    url: '{{ route('master-barang-per-kategori') }}',
                    data: d => {
                        d.kategori = $("#kategori").val();
                    }
                },
                columns: [
                    {
                        data: 'barcode'
                    },
                    {
                        data: 'no_koli'
                    },
                    {
                        data: 'buyer'
                    },
                    {
                        data: 'no_ws'
                    },
                    {
                        data: 'style'
                    },
                    {
                        data: 'product_item'
                    },
                    {
                        data: 'warna'
                    },
                    {
                        data: 'size'
                    },
                    {
                        data: 'grade'
                    },
                    {
                        data: 'qty_saat_ini',
                        className: 'text-end',
                        render: function(data, type, row) {
                            return parseFloat(data).toFixed(2);
                        }
                    },
                    {
                        data: 'satuan'
                    },
                    {
                        data: 'keterangan'
                    },
                    {
                        data: 'lokasi'
                    },
                    {
                        data: 'barcode'
                    },
                ],
                columnDefs: [
                    {
                        targets: [13],
                        render: (data, type, row, meta) => {

                            let btnDetail = `
                                <button 
                                    class="btn btn-primary btn-sm btn-detail"
                                    data-barcode="${row.barcode}"
                                >
                                    <i class="fa-solid fa-list"></i>
                                </button>
                            `;

                            return `
                                <div class="d-flex gap-1 justify-content-center">
                                    ${btnDetail}
                                </div>
                            `;
                        }
                    },
                    {
                        targets: '_all',
                        className: 'text-nowrap'
                    }
                ],
            });

        });

        function dataTableReload() {
            let kategori = $('#kategori').val();

            $('#table-fabric, #table-accesories, #table-fg').addClass('d-none');
            $('#exportExcel').prop('disabled', false);

            if (kategori === 'FABRIC') {
                $('#table-fabric').removeClass('d-none');
                if (tableFabric) tableFabric.ajax.reload();
            } else if (kategori === 'ACCESORIES') {
                $('#table-accesories').removeClass('d-none');
                if (tableAccesories) tableAccesories.ajax.reload();
            } else if (kategori === 'FG') {
                $('#table-fg').removeClass('d-none');
                if (tableFg) tableFg.ajax.reload();
            }
        }

        $(document).on('click', '.btn-detail', function () {
            let kategori = $('#kategori').val();
            let barcode = $(this).data('barcode');
            
            if (kategori === 'FABRIC') {
                $('#modalFabric').modal('show');
                initHistoryFabric(barcode);
            } else if (kategori === 'ACCESORIES') {
                $('#modalAccesories').modal('show');
                initHistoryAccesories(barcode);
            } else if (kategori === 'FG') {
                $('#modalFg').modal('show');
                initHistoryFg(barcode);
            }
        });

        // HISTORY FABRIC
        let tableHistoryFabric;
        let tableHistoryDetailFabric;
        let currentBarcodeFabric = '';
        
        function initHistoryFabric(barcode) {
            currentBarcodeFabric = barcode;

            if ($.fn.DataTable.isDataTable('#datatable-fabric-history')) {
                tableHistoryFabric.ajax.reload(null, false);
                tableHistoryDetailFabric.ajax.reload(null, false);
                return;
            }

            tableHistoryFabric = $('#datatable-fabric-history').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('history-fabric-master-barang-per-kategori') }}",
                    data: function (d) {
                        d.barcode = currentBarcodeFabric;
                    }
                },
                columns: [
                    { data: 'barcode' },
                    { data: 'lokasi' },
                    { data: 'buyer' },
                    { data: 'keterangan' },
                    { data: 'jenis_item' },
                    { data: 'warna' },
                    { data: 'lot' },
                    { data: 'no_roll' },
                    {
                        data: 'qty_saat_ini',
                        className: 'text-end',
                        render: function(data, type, row) {
                            return parseFloat(data).toFixed(2);
                        }
                    },
                    { data: 'satuan' },
                ]
            });

            tableHistoryDetailFabric = $('#datatable-fabric-history-detail').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('history-detail-fabric-master-barang-per-kategori') }}",
                    data: function (d) {
                        d.barcode = currentBarcodeFabric;
                    }
                },
                columns: [
                    { data: 'jenis_tipe' },
                    { data: 'no_bpb' },
                    { data: 'tgl_bpb' },
                    { data: 'barcode' },
                    { data: 'lokasi' },
                    { data: 'buyer' },
                    { data: 'keterangan' },
                    { data: 'jenis_item' },
                    { data: 'warna' },
                    { data: 'lot' },
                    { data: 'no_roll' },
                    {
                        data: 'qty',
                        className: 'text-end',
                        render: function(data, type, row) {
                            return parseFloat(data).toFixed(2);
                        }
                    },
                    { data: 'satuan' },
                ]
            });
        }

        // HISTORY ACCESORIES
        let tableHistoryAccesories;
        let tableHistoryDetailAccesories;
        let currentBarcodeAccesories = '';
        
        function initHistoryAccesories(barcode) {
            currentBarcodeAccesories = barcode;

            if ($.fn.DataTable.isDataTable('#datatable-accesories-history')) {
                tableHistoryAccesories.ajax.reload(null, false);
                tableHistoryDetailAccesories.ajax.reload(null, false);
                return;
            }

            tableHistoryAccesories = $('#datatable-accesories-history').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('history-accesories-master-barang-per-kategori') }}",
                    data: function (d) {
                        d.barcode = currentBarcodeAccesories;
                    }
                },
                columns: [
                    { data: 'barcode' },
                    { data: 'no_box' },
                    { data: 'buyer' },
                    { data: 'worksheet' },
                    { data: 'nama_barang' },
                    { data: 'kode' },
                    { data: 'warna' },
                    { data: 'size' },
                    {
                        data: 'qty_saat_ini',
                        className: 'text-end',
                        render: function(data, type, row) {
                            return parseFloat(data).toFixed(2);
                        }
                    },
                    { data: 'satuan' },
                    {
                        data: 'qty_kgm_saat_ini',
                        className: 'text-end',
                        render: function(data, type, row) {
                            return parseFloat(data).toFixed(2);
                        }
                    },
                    { data: 'keterangan' },
                    { data: 'lokasi' },
                ]
            });

            tableHistoryDetailAccesories = $('#datatable-accesories-history-detail').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('history-detail-accesories-master-barang-per-kategori') }}",
                    data: function (d) {
                        d.barcode = currentBarcodeAccesories;
                    }
                },
                columns: [
                    { data: 'jenis_tipe' },
                    { data: 'no_bpb' },
                    { data: 'tgl_bpb' },
                    { data: 'barcode' },
                    { data: 'no_box' },
                    { data: 'buyer' },
                    { data: 'worksheet' },
                    { data: 'nama_barang' },
                    { data: 'kode' },
                    { data: 'warna' },
                    { data: 'size' },
                    {
                        data: 'qty',
                        className: 'text-end',
                        render: function(data, type, row) {
                            return parseFloat(data).toFixed(2);
                        }
                    },
                    { data: 'satuan' },
                    {
                        data: 'qty_kgm',
                        className: 'text-end',
                        render: function(data, type, row) {
                            return parseFloat(data).toFixed(2);
                        }
                    },
                    { data: 'keterangan' },
                    { data: 'lokasi' },
                ]
            });
        }

        // HISTORY FG
        let tableHistoryFg;
        let tableHistoryDetailFg;
        let currentBarcodeFg = '';
        
        function initHistoryFg(barcode) {
            currentBarcodeFg = barcode;

            if ($.fn.DataTable.isDataTable('#datatable-fg-history')) {
                tableHistoryFg.ajax.reload(null, false);
                tableHistoryDetailFg.ajax.reload(null, false);
                return;
            }

            tableHistoryFg = $('#datatable-fg-history').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('history-fg-master-barang-per-kategori') }}",
                    data: function (d) {
                        d.barcode = currentBarcodeFg;
                    }
                },
                columns: [
                    { data: 'barcode' },
                    { data: 'no_koli' },
                    { data: 'buyer' },
                    { data: 'no_ws' },
                    { data: 'style' },
                    { data: 'product_item' },
                    { data: 'warna' },
                    { data: 'size' },
                    { data: 'grade' },
                    {
                        data: 'qty_saat_ini',
                        className: 'text-end',
                        render: function(data, type, row) {
                            return parseFloat(data).toFixed(2);
                        }
                    },
                    { data: 'satuan' },
                    { data: 'keterangan' },
                    { data: 'lokasi' },
                ]
            });

            tableHistoryDetailFg = $('#datatable-fg-history-detail').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('history-detail-fg-master-barang-per-kategori') }}",
                    data: function (d) {
                        d.barcode = currentBarcodeFg;
                    }
                },
                columns: [
                    { data: 'jenis_tipe' },
                    { data: 'no_bpb' },
                    { data: 'tgl_bpb' },
                    { data: 'barcode' },
                    { data: 'no_koli' },
                    { data: 'buyer' },
                    { data: 'no_ws' },
                    { data: 'style' },
                    { data: 'product_item' },
                    { data: 'warna' },
                    { data: 'size' },
                    { data: 'grade' },
                    {
                        data: 'qty',
                        className: 'text-end',
                        render: function(data, type, row) {
                            return parseFloat(data).toFixed(2);
                        }
                    },
                    { data: 'satuan' },
                    { data: 'keterangan' },
                    { data: 'lokasi' },
                ]
            });
        }

        async function exportExcel() {
            Swal.fire({
                title: "Exporting",
                html: "Please Wait...",
                timerProgressBar: true,
                didOpen: () => {
                    Swal.showLoading();
                },
            });

            await $.ajax({
                url: "{{ route("export-master-barang-per-kategori") }}",
                type: "post",
                data: {
                    kategori : $("#kategori").val()
                },
                xhrFields: { responseType : 'blob' },
                success: function (res) {
                    Swal.close();

                    iziToast.success({
                        title: 'Success',
                        message: 'Success',
                        position: 'topCenter'
                    });

                    var blob = new Blob([res]);
                    var link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = "Master Barang "+$("#kategori").val()+".xlsx";
                    link.click();
                },
                error: function (jqXHR) {
                    console.error(jqXHR);
                }
            });

            Swal.close();
        }

    </script>
@endsection
