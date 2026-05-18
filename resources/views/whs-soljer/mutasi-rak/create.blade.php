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
        input[type=file]::file-selector-button {
            margin-right: 20px;
            border: none;
            background: #084cdf;
            padding: 10px 20px;
            border-radius: 10px;
            color: #fff;
            cursor: pointer;
            transition: background .2s ease-in-out;
        }

        input[type=file]::file-selector-button:hover {
            background: #0d45a5;
        }

        .drop-container {
            position: relative;
            display: flex;
            gap: 10px;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 200px;
            padding: 20px;
            border-radius: 10px;
            border: 2px dashed #555;
            color: #444;
            cursor: pointer;
            transition: background .2s ease-in-out, border .2s ease-in-out;
        }

        .drop-container:hover {
            background: #eee;
            border-color: #111;
        }

        .drop-container:hover .drop-title {
            color: #222;
        }

        .drop-title {
            color: #444;
            font-size: 20px;
            font-weight: bold;
            text-align: center;
            transition: color .2s ease-in-out;
        }
    </style>
@endsection

@section('content')
    <div class="d-flex justify-content-between mb-3">
        <h5 class="fw-bold text-sb"><i class="fa fa-plus fa-sm"></i> Tambah Mutasi Rak (FABRIC)</h5>
        <a href="{{ route('mutasi-rak') }}" class="btn btn-primary btn-sm px-1 py-1"><i class="fas fa-reply"></i> Kembali List Mutasi Rak (FABRIC)</a>
    </div>
    <form action="{{ route('store-mutasi-rak') }}" method="post" id="store-mutasi-rak" onsubmit="submitForm(this, event)">
        @csrf
        <div class="card card-sb">
            <div class="card-header">
                <h5 class="card-title fw-bold">
                    Mutasi Rak
                </h5>
            </div>
            <div class="card-body">
                <div class="row align-items-end">
                    <div class="col-3 col-md-3">
                        <div class="mb-1">
                            <label class="form-label"><small>No. Mutasi</small></label>
                            <input type="text" class="form-control" id="no_mutasi" name="no_mutasi"
                                value="{{ $no_mutasi->kode }}" readonly>
                        </div>
                    </div>
                    <div class="col-3 col-md-3">
                        <div class="mb-1">
                            <label class="form-label"><small>Tgl Mutasi</small></label>
                            <input type="date" class="form-control" id="tgl_mutasi" name="tgl_mutasi" 
                                value="{{ date('Y-m-d') }}">
                        </div>
                    </div>

                    <div class="col-3 col-md-3">
                        <div class="mb-1">
                            <label class="form-label">
                                <small>Lokasi Tujuan</small>
                            </label>

                            <input type="text" class="form-control" id="lokasi_tujuan" name="lokasi_tujuan" list="list_lokasi" autocomplete="off">
                            <datalist id="list_lokasi">
                                @foreach($lokasi as $row)
                                    <option value="{{ $row->lokasi }}">
                                @endforeach
                            </datalist>
                        </div>
                    </div>

                    <div class="col-3 col-md-3">
                        <div class="mb-1">
                            <label class="form-label"><small>Keterangan</small></label>
                            <input type="text" class="form-control" id="keterangan" name="keterangan" value="">
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-2 col-md-2">
                        <div class="mb-1">
                            <label class="form-label"><small>Total Roll</small></label>
                            <input type="text" class="form-control text-end" id="total_roll" name="total_roll" value="" readonly>
                        </div>
                    </div>
                    <div class="col-2 col-md-2">
                        <div class="mb-1">
                            <label class="form-label"><small>Total Qty</small></label>
                            <input type="text" class="form-control text-end" id="total_qty" name="total_qty" value="" readonly>
                        </div>
                    </div>
                    <div class="col-2 col-md-2">
                        <div class="mb-1">
                            <label class="form-label"><small>Scan Barcode</small></label>
                            <input type="text" class="form-control" style="text-transform: uppercase;" oninput="this.value = this.value.toUpperCase()" id="barcode_scan" name="barcode_scan">
                        </div>
                    </div>

                    <div class="col-3 col-md-3">
                        <div class="mb-1">
                            <label class="form-label"><small>Multi Barcode</small></label>
                            <textarea class="form-control" id="multi_barcode" name="multi_barcode" rows="5" placeholder="Scan/masukkan banyak barcode (1 baris 1 barcode)"></textarea>
                        </div>
                    </div>

                    <div class="col-3 col-lg-3 d-flex align-items-start">
                        <button 
                            type="button"
                            class="btn btn-success w-100 w-lg-50 fw-bold mt-4" 
                            id="scan_multi_barcode">
                            Scan Multi Barcode
                        </button>
                    </div>

                </div>
            </div>
        </div>

        <div class="card card-sb">
            <div class="card-header">
                <h5 class="card-title fw-bold">
                    Detail Item
                </h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-end mb-2">
                    <button type="button" class="btn btn-danger btn-sm" id="btnDeleteSelected" style="display:none;">
                        <i class="fa fa-trash"></i> Delete
                    </button>
                </div>
                <div class="row align-items-end">
                    <div class="col-md-12 table-responsive">
                        <table class="table table-bordered w-100 table" id="datatable">
                            <thead>
                                <tr>
                                    <th>Barcode</th>
                                    <th>Buyer</th>
                                    <th>Keterangan</th>
                                    <th>Jenis Item</th>
                                    <th>Warna</th>
                                    <th>Lot</th>
                                    <th>No Roll</th>
                                    <th>Qty</th>
                                    <th>Satuan</th>
                                    <th>Tujuan</th>
                                    <th>Lokasi Barcode</th>
                                    <th>Lokasi Tujuan</th>
                                    <th class="text-center">
                                        <input type="checkbox" id="check_all">
                                    </th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                    <input type="hidden" name="items" id="items">
                </div>
                <div class="col-12 col-md-6 offset-md-3 my-2 text-center">
                    <button type="button" class="btn btn-success w-100 mb-1 fw-bold" id="btnSimpan">
                        <i class="fa fa-save"></i> SIMPAN
                    </button>
                </div>
            </div>
        </div>
    </form>
@endsection

@section('custom-script')
    <!-- DataTables & Plugins -->
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <!-- Select2 -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <!-- Page specific script -->
    <script>
        // Initial Window On Load Event
        let table_detail_item;

        $(document).ready(async function () {

            $('#lokasi_tujuan').on('input', function () {
                let lokasi = $(this).val();

                table_detail_item.rows().every(function () {
                    let data = this.data();

                    data.lokasi_tujuan = lokasi;
                    this.data(data);
                });

                table_detail_item.draw(false);
            });

            $('#barcode_scan').focus();

            $('#barcode_scan').on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();

                    let barcode = $(this).val().trim();
                    let lokasi_tujuan = $('#lokasi_tujuan').val().trim();

                    if (!lokasi_tujuan) {
                        Swal.fire('Warning', 'Lokasi tujuan wajib diisi!', 'warning');
                        $('#lokasi_tujuan').focus();
                        return;
                    }

                    if (!barcode) return;

                    $('#loading').removeClass('d-none');
                    getDataBarcode(barcode);
                }
            });

            $('#scan_multi_barcode').on('click', function () {
                let multiBarcode = $('#multi_barcode').val();
                let lokasi_tujuan = $('#lokasi_tujuan').val().trim();

                if (!lokasi_tujuan) {
                    Swal.fire('Warning', 'Lokasi tujuan wajib diisi!', 'warning');
                    $('#lokasi_tujuan').focus();
                    return;
                }

                if (!multiBarcode.trim()) {
                    Swal.fire('Warning', 'Barcode masih kosong!', 'warning');
                    return;
                }

                $('#loading').removeClass('d-none');

                let barcodes = multiBarcode
                    .split('\n')
                    .map(item => item.trim())
                    .filter(item => item !== '');

                barcodes = [...new Set(barcodes)];

                barcodes.forEach(function(barcode) {
                    getDataBarcode(barcode);
                });

                $('#multi_barcode').val('');
            });
            
            table_detail_item = $('#datatable').DataTable({
                processing: true,
                serverSide: false,
                data: [],
                columns: [
                    { data: 'barcode' },
                    { data: 'buyer' },
                    { data: 'keterangan' },
                    { data: 'jenis_item' },
                    { data: 'warna' },
                    { data: 'lot' },
                    { data: 'no_roll' },
                    {
                        data: 'qty',
                        className: 'text-end',
                        render: function (data, type) {
                            let val = parseFloat(data) || 0;

                            if (type === 'sort' || type === 'type') {
                                return val;
                            }

                            return val.toFixed(2);
                        }
                    },
                    { data: 'satuan' },
                    { data: 'tujuan' },
                    { data: 'lokasi_asal' },
                    { data: 'lokasi_tujuan' },
                    { 
                        data: null,
                        className: 'text-center',
                        orderable: false,
                        render: function () {
                            return `<input type="checkbox" class="row-check">`;
                        }
                    },
                ]
            });

            // $('#datatable tbody').on('click', '.hapus', function () {
            //     let row = table_detail_item.row($(this).parents('tr'));

            //     Swal.fire({
            //         title: 'Yakin hapus?',
            //         text: 'Data akan dihapus dari list',
            //         icon: 'warning',
            //         showCancelButton: true,
            //         confirmButtonText: 'Ya, hapus!',
            //         cancelButtonText: 'Batal'
            //     }).then((result) => {
            //         if (result.isConfirmed) {
            //             row.remove().draw(false);
            //             updateTotalQty();

            //             Swal.fire({
            //                 icon: 'success',
            //                 title: 'Berhasil',
            //                 text: 'Data berhasil dihapus',
            //                 timer: 1200,
            //                 showConfirmButton: false
            //             });
            //         }
            //     });
            // });
        });


        $(document).on('click', '#btnSimpan', function() {
            let result = [];

            table_detail_item.rows().every(function () {
                let rowData = this.data();
                let rowNode = $(this.node());

                result.push({
                    barcode: rowData.barcode,
                    no_roll: rowData.no_roll,
                    buyer: rowData.buyer,
                    jenis_item: rowData.jenis_item,
                    warna: rowData.warna,
                    lot: rowData.lot,
                    qty: rowData.qty,
                    satuan: rowData.satuan,
                    tujuan: rowData.tujuan,
                    lokasi_asal: rowData.lokasi_asal,
                    lokasi_tujuan: rowData.lokasi_tujuan,
                    keterangan: rowData.keterangan,
                });
            });

            if (result.length === 0) {
                Swal.fire('Warning', 'Data masih kosong!', 'warning');
                return false;
            }

            $('#items').val(JSON.stringify(result));
            $('#store-mutasi-rak').submit();
        });
            
        // Select2 Autofocus
        $(document).on('select2:open', () => {
            document.querySelector('.select2-search__field').focus();
        });

        // Initialize Select2 Elements
        $('.select2').select2()

        // Initialize Select2BS4 Elements
        $('.select2bs4').select2({
            theme: 'bootstrap4',
        })

        function updateTotalQty() {
            let totalQty = 0;
            let totalRoll = 0;

            table_detail_item.rows().every(function () {

                let rowData = this.data();

                totalQty += parseFloat(rowData.qty) || 0;
                totalRoll++;
            });

            $('#total_qty').val(totalQty.toFixed(2));
            $('#total_roll').val(totalRoll);
        }

        function getDataBarcode(barcode) {
            $.ajax({
                url: "{{ route('get-data-barcode-mutasi-rak') }}",
                type: 'GET',
                data: { barcode: barcode },
                success: function(res) {

                    if (!res || res.status === 404) {
                        $('#barcode_scan').val("").focus();
                        Swal.fire('Error', 'Barcode tidak ditemukan!', 'error');
                        return;
                    }

                    if (res.status === 400) {
                        $('#barcode_scan').val("").focus();
                        Swal.fire('Warning', res.message, 'warning');
                        return;
                    }

                    let isDuplicate = false;
                    table_detail_item.rows().every(function () {
                        let data = this.data();
                        if (data.barcode === res.barcode) {
                            isDuplicate = true;
                        }
                    });

                    if (isDuplicate) {
                        $('#barcode_scan').val("").focus();
                        Swal.fire('Error', 'Barcode sudah ada di tabel!', 'error');
                        return;
                    }

                    table_detail_item.row.add({
                        barcode: res.barcode,
                        no_roll: res.no_roll,
                        buyer: res.buyer,
                        jenis_item: res.jenis_item,
                        warna: res.warna,
                        lot: res.lot,
                        qty: res.qty,
                        satuan: res.satuan,
                        tujuan: res.tujuan,
                        lokasi_asal: res.lokasi,
                        keterangan: res.keterangan,
                        lokasi_tujuan: $("#lokasi_tujuan").val(),
                        action: `<button type="button" class="btn btn-danger btn-sm hapus">Hapus</button>`
                    }).draw(false);

                    updateTotalQty();

                    $('#barcode_scan').val("").focus();
                },
                error: function() {
                    Swal.fire('Error', 'Gagal ambil data!', 'error');
                },
                complete: function() {
                    $('#loading').addClass('d-none');
                }
            });
        }

        $('#check_all').on('change', function () {
            $('.row-check').prop('checked', $(this).prop('checked'));
            toggleDeleteButton();
        });

        $('#btnDeleteSelected').on('click', function () {
            let table = $('#datatable').DataTable();
            let checked = $('.row-check:checked');

            if (checked.length === 0) {
                Swal.fire('Warning', 'Tidak ada data yang dipilih!', 'warning');
                return;
            }

            Swal.fire({
                title: 'Yakin?',
                text: 'Data yang dicentang akan dihapus!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus!'
            }).then((result) => {
                if (result.isConfirmed) {

                    checked.each(function () {
                        table.row($(this).closest('tr')).remove();
                    });

                    table.draw(false);

                    $('#check_all').prop('checked', false);
                    toggleDeleteButton();

                    updateTotalQty();

                    Swal.fire('Success', 'Data berhasil dihapus!', 'success');
                }
            });

        });

        $('#datatable').on('change', '.row-check', function () {
            toggleDeleteButton();
        });

        function toggleDeleteButton() {
            let checked = $('.row-check:checked').length;

            if (checked > 0) {
                $('#btnDeleteSelected').show();
            } else {
                $('#btnDeleteSelected').hide();
            }
        }
        
    </script>
@endsection
