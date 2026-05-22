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

    <form action="{{ route('store-bpb-fg-stock-scan') }}" method="post" id="store-bpb-fg-stock-scan" onsubmit="submitForm(this, event)">
        @csrf
        <div class="card card-sb">
            <div class="card-header">
                <h5 class="card-title fw-bold">
                    <i class="fas fa-search"></i> Input Penerimaan Barang Jadi Stok Scan
                </h5>
            </div>
            <div class="card-body">
                <div class="row align-items-end">
                    <div class="col-6 col-md-3">
                        <div class="mb-1">
                            <label class="form-label"><small>Tanggal Penerimaan</small></label>
                            <input type="date" class="form-control" id="tanggal_penerimaan" name="tanggal_penerimaan" 
                            value="{{ date('Y-m-d') }}" min="{{ date('Y-m-d', strtotime('-3 days')) }}">
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="mb-1">
                            <label class="form-label"><small>Sumber Penerimaan</small></label>
                            <select class="form-control select2bs4" id="sumber_penerimaan" name="sumber_penerimaan" style="width: 100%;">
                                <option selected="selected" value="" disabled="true">Pilih Sumber Penerimaan</option>
                                <option value="REJECT">REJECT</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row mt-3 align-items-end">
                    <div class="col-6 col-md-6">
                        <div class="mb-1">
                            <label class="form-label"><small>Scan QR</small></label>
                            <input type="text" class="form-control"
                                style="text-transform: uppercase;"
                                oninput="this.value = this.value.toUpperCase()"
                                id="barcode_scan"
                                name="barcode_scan">
                        </div>
                    </div>
                    <div class="col-6 col-md-6">
                        <div class="mb-1">
                            <label class="form-label"><small>No Karton</small></label>
                            <input type="number" class="form-control" id="no_karton" name="no_karton">
                            <input type="hidden" id="so_det_id" name="so_det_id">
                            <input type="hidden" id="qty" name="qty">
                            <input type="hidden" id="grade" name="grade">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- <div class="card card-primary card-outline">
            <div class="card-header">
                <h5 class="card-title fw-bold">
                    <i class="fas fa-cart-plus"></i> Input Penerimaan Barang Jadi Stok Scan
                </h5>
            </div>
            <div class="card-body">
                
            </div>
        </div> --}}
    </form>

    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-box"></i> Penerimaan Barang Jadi Stok Scan</h5>
        </div>
        <div class="card-body">
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
                <table id="datatable" class="table table-bordered 100 table-hover display nowrap">
                    <thead class="table-primary">
                        <tr style='text-align:center; vertical-align:middle'>
                            <th>No. Trans</th>
                            <th>Tgl. Trans</th>
                            <th>Lokasi</th>
                            <th>Buyer</th>
                            <th>Brand</th>
                            <th>Style</th>
                            <th>WS</th>
                            <th>Color</th>
                            <th>Size</th>
                            <th>Total Karton</th>
                            <th>Total Qty</th>
                            <th>Sumber</th>
                            <th>Detail</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal" tabindex="-1">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Barcode</h5>
                    <div class="d-flex align-items-center gap-2">
                        {{-- <button class="btn btn-success btn-sm" id="exportExcelHistory"
                            data-bs-toggle="tooltip" data-bs-title="Export Excel"
                            onclick="exportExcelHistory()">
                            <i class="fa fa-file-excel"></i>
                        </button> --}}

                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                </div>

                <div class="modal-body">
                    <div id="contentFabric">
                        <div id="table-detail" class="table-responsive">
                            <table id="datatable-detail" class="table table-bordered table-striped table-hover table w-100">
                                <thead class="bg-sb">
                                    <tr>
                                        <th>QR Code</th>
                                        <th>Buyer</th>
                                        <th>Brand</th>
                                        <th>Style</th>
                                        <th>WS</th>
                                        <th>Color</th>
                                        <th>Size</th>
                                        <th>No Karton</th>
                                        <th>Qty</th>
                                        <th>Sumber</th>
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
    <!-- DataTables & Plugins -->
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>

    <script>
        $(document).ready(() => {
            dataTableReload();

            $('#barcode_scan').focus();

            $('#barcode_scan').on('keydown', function(e) {
                if (e.keyCode === 13) {
                    e.preventDefault();

                    let barcode = $(this).val().trim();

                    if (!barcode) return;

                    $('#loading').removeClass('d-none');
                    getDataBarcode(barcode);
                }
            });

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
        });

        function dataTableReload() {
            datatable.ajax.reload();
        }

        $('#datatable thead tr').clone(true).appendTo('#datatable thead');
        $('#datatable thead tr:eq(1) th').each(function(i) {
            var title = $(this).text();
            $(this).html('<input type="text" class="form-control form-control-sm" />');

            $('input', this).on('keyup change', function() {
                if (datatable.column(i).search() !== this.value) {
                    datatable
                        .column(i)
                        .search(this.value)
                        .draw();
                }
            });

        });

        let datatable = $("#datatable").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            paging: true,
            searching: true,
            destroy: true,
            scrollX: true,
            ajax: {
                url: '{{ route('bpb-fg-stock-scan') }}',
                data: function(d) {
                    d.dateFrom = $('#tgl-awal').val();
                    d.dateTo = $('#tgl-akhir').val();
                },
            },
            columns: [
                {
                    data: 'no_trans'
                }, 
                {
                    data: 'tgl_terima_fix'
                },
                {
                    data: 'lokasi'
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
                    data: 'ws'
                },
                {
                    data: 'color'
                },
                {
                    data: 'size'
                },
                {
                    data: 'total_carton'
                },
                {
                    data: 'total_qty'
                },
                {
                    data: 'sumber_pemasukan'
                },
                {
                    data: 'no_trans'
                },
            ],
            columnDefs: [
                {
                    targets: [12],
                    render: (data, type, row, meta) => {

                        let btnDetail = `
                            <button 
                                class="btn btn-primary btn-sm btn-detail"
                                data-id_so_det="${row.id_so_det}"
                                data-no_trans="${row.no_trans}"
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
                    "className": "dt-center",
                    "targets": "_all"
                },
            ]
        });

        $(document).on('click', '.btn-detail', function () {
            let id_so_det = $(this).data('id_so_det');
            let no_trans = $(this).data('no_trans');

            $('#modal').modal('show');
            iniModalDetail(id_so_det, no_trans);
        });

        // HISTORY FABRIC
        let tableDetail;
        let currentidSoDet = '';
        let currentNoTrans = '';
        
        function iniModalDetail(id_so_det, no_trans) {
            currentidSoDet = id_so_det
            currentNoTrans = no_trans

            if ($.fn.DataTable.isDataTable('#datatable-detail')) {
                tableDetail.ajax.reload(null, false);
                return;
            }

            tableDetail = $('#datatable-detail').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('get-data-detail-bpb-fg-stock-scan') }}",
                    data: function (d) {
                        d.id_so_det = currentidSoDet;
                        d.no_trans = currentNoTrans;
                    }
                },
                columns: [
                    {
                        data: 'qr_code'
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
                        data: 'ws'
                    },
                    {
                        data: 'color'
                    },
                    {
                        data: 'size'
                    },
                    {
                        data: 'no_carton'
                    },
                    {
                        data: 'qty'
                    },
                    {
                        data: 'sumber_pemasukan'
                    },
                ]
            });
        }

        function export_excel_bpb() {
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
                url: '{{ route('export_excel_bpb_fg_stok_scan') }}',
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
                            to + " Laporan Penerimaan FG Stock Scan.xlsx";
                        link.click();

                    }
                },
            });
        }

        function getDataBarcode(barcode) {
            $.ajax({
                url: "{{ route('get-data-barcode-bpb-fg-stock-scan') }}",
                type: 'GET',
                data: { barcode: barcode },

                success: function(res) {

                    if (!res.data) {
                        iziToast.error({
                            title: 'Error',
                            message: res.message ?? 'Barcode tidak ditemukan!',
                            position: 'topCenter',
                            transitionIn: 'slideInRight',
                            timeout: 2000
                        });

                        $('#barcode_scan').val("").focus();
                        return;
                    }

                    let data = res.data;

                    saveScan(data);
                },

                error: function() {
                    iziToast.error({
                        title: 'Error',
                        message: 'Gagal ambil data!',
                        position: 'topCenter',
                        transitionIn: 'slideInRight',
                        timeout: 2000
                    });
                },

                complete: function() {
                    $('#loading').addClass('d-none');
                }
            });
        }

        function saveScan(data) {

            let no_karton = $('#no_karton').val();
            let sumber_penerimaan = $('#sumber_penerimaan').val();

            if (!no_karton || !sumber_penerimaan) {
                iziToast.warning({
                    title: 'Peringatan',
                    message: 'No Karton dan Sumber Penerimaan wajib diisi!',
                    position: 'topCenter',
                    timeout: 2000
                });

                $('#barcode_scan').val('').focus();

                return;
            }

            $.ajax({
                url: "{{ route('store-bpb-fg-stock-scan') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    so_det_id: data.so_det_id,
                    qty: data.qty,
                    grade: data.grade,
                    barcode_scan: $('#barcode_scan').val(),
                    no_karton: no_karton,
                    tanggal_penerimaan: $('#tanggal_penerimaan').val(),
                    sumber_penerimaan: sumber_penerimaan,
                },
                success: function(res) {

                    iziToast.success({
                        title: 'Success',
                        message: res.message ?? 'Tersimpan',
                        position: 'topCenter',
                        timeout: 2000
                    });

                    $('#barcode_scan').val('').focus();
                    dataTableReload();
                },
                error: function(xhr) {
                    iziToast.error({
                        title: 'Error',
                        message: xhr.responseJSON?.message ?? 'Gagal simpan',
                        position: 'topCenter',
                        timeout: 2000
                    });
                    $('#barcode_scan').val('').focus();
                }
            });
        }
    </script>
@endsection
