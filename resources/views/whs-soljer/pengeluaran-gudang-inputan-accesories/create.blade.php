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
        <h5 class="fw-bold text-sb"><i class="fa fa-plus fa-sm"></i> Tambah Pengeluaran Gudang Inputan (ACCESORIES)</h5>
        <a href="{{ route('pengeluaran-gudang-inputan-accesories') }}" class="btn btn-primary btn-sm px-1 py-1"><i class="fas fa-reply"></i> Kembali List Pengeluaran Gudang Inputan (ACCESORIES)</a>
    </div>
    <form action="{{ route('store-pengeluaran-gudang-inputan-accesories') }}" method="post" id="store-pengeluaran-gudang-inputan-accesories" onsubmit="submitForm(this, event)">
        @csrf
        <div class="card card-sb">
            <div class="card-header">
                <h5 class="card-title fw-bold">
                    Header Penginputan
                </h5>
            </div>
            <div class="card-body">
                <div class="row align-items-end">
                    <div class="col-6 col-md-3">
                        <div class="mb-1">
                            <label class="form-label"><small>No. BPB</small></label>
                            <input type="text" class="form-control" id="no_bpb" name="no_bpb"
                                value="{{ $no_bpb->kode }}" readonly>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="mb-1">
                            <label class="form-label"><small>Tgl BPB</small></label>
                            <input type="date" class="form-control" id="tgl_bpb" name="tgl_bpb" 
                                value="{{ date('Y-m-d') }}">
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-6 col-md-3">
                        <div class="mb-1">
                            <label class="form-label"><small>Barcode</small></label>
                            <input type="text" class="form-control"
                                style="text-transform: uppercase;"
                                oninput="this.value = this.value.toUpperCase()"
                                id="barcode_scan"
                                name="barcode_scan">
                        </div>
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
                                    <th width="100px;">Qty Out</th>
                                    <th width="100px;">Qty KGM Out</th>
                                    <th class="text-center">
                                        <input type="checkbox" id="check_all">
                                    </th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th colspan="8" class="text-center">TOTAL</th>
                                    <th id="total_qty" class="text-end">0</th>
                                    <th></th>
                                    <th id="total_qty_kgm" class="text-end">0</th>
                                    <th colspan="2"></th>
                                    <th id="total_qty_out" class="text-end">0</th>
                                    <th id="total_qty_kgm_out" class="text-end">0</th>
                                    <th></th>
                                </tr>
                            </tfoot>
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
            $('#barcode_scan').focus();

            $('#barcode_scan').on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();

                    let barcode = $(this).val().trim();

                    if (!barcode) return;

                    $('#loading').removeClass('d-none');
                    getDataBarcode(barcode);
                }
            });
            
            table_detail_item = $('#datatable').DataTable({
                processing: true,
                serverSide: false,
                data: [],
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
                    {
                        data: 'qty_kgm',
                        className: 'text-end',
                        render: function (data, type) {
                            let val = parseFloat(data) || 0;

                            if (type === 'sort' || type === 'type') {
                                return val;
                            }

                            return val.toFixed(2);
                        }
                    },
                    { data: 'keterangan' },
                    { data: 'lokasi' },
                    { data: 'qty_out', className: 'text-end' },
                    { data: 'qty_kgm_out', className: 'text-end' },
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

                let qty_out = rowNode.find('.qty_out_input').val();
                let qty_kgm_out = rowNode.find('.qty_kgm_out_input').val();

                result.push({
                    barcode: rowData.barcode,
                    qty: rowData.qty,
                    qty_kgm: rowData.qty_kgm,
                    qty_out: parseFloat(qty_out) || 0,
                    qty_kgm_out: parseFloat(qty_kgm_out) || 0
                });
            });

            if (result.length === 0) {
                Swal.fire('Warning', 'Data masih kosong!', 'warning');
                return false;
            }

            $('#items').val(JSON.stringify(result));
            $('#store-pengeluaran-gudang-inputan-accesories').submit();
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
            let total = 0;
            let total_qty_out = 0;
            let total_kgm = 0;
            let total_qty_kgm_out = 0;

            table_detail_item.rows().every(function () {
                let rowData = this.data();
                let rowNode = $(this.node());

                let qty = parseFloat(rowData.qty) || 0;
                let qty_out = parseFloat(rowNode.find('.qty_out_input').val()) || 0;

                let qty_kgm = parseFloat(rowData.qty_kgm) || 0;
                let qty_kgm_out = parseFloat(rowNode.find('.qty_kgm_out_input').val()) || 0;

                total += qty;
                total_qty_out += qty_out;
                total_kgm += qty_kgm;
                total_qty_kgm_out += qty_kgm_out;
            });

            $('#total_qty').text(total.toFixed(2));
            $('#total_qty_out').text(total_qty_out.toFixed(2));
            $('#total_qty_kgm').text(total_kgm.toFixed(2));
            $('#total_qty_kgm_out').text(total_qty_kgm_out.toFixed(2));
        }

        function getDataBarcode(barcode) {
            $.ajax({
                url: "{{ route('get-data-barcode-pengeluaran-gudang-inputan-accesories') }}",
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
                        no_box: res.no_box,
                        buyer: res.buyer,
                        worksheet: res.worksheet,
                        nama_barang: res.nama_barang,
                        kode: res.kode,
                        warna: res.warna,
                        size: res.size,
                        qty: res.qty,
                        satuan: res.satuan,
                        qty_kgm: res.qty_kgm,
                        keterangan: res.keterangan,
                        lokasi: res.lokasi,
                        qty_out: `<input type="number" 
                            class="form-control form-control-sm qty_out_input text-end" 
                            value="${parseFloat(res.qty).toFixed(2)}" 
                            min="1" 
                            max="${res.qty}" 
                            >`,
                        qty_kgm_out: `<input type="number" 
                            class="form-control form-control-sm qty_kgm_out_input text-end" 
                            value="${parseFloat(res.qty_kgm).toFixed(2)}" 
                            min="1" 
                            max="${res.qty_kgm}" 
                            >`,
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

        $(document).on('blur', '.qty_out_input, .qty_kgm_out_input', function () {
            let input = $(this);
            let val = parseFloat(input.val());

            if (!isNaN(val)) {
                input.val(val.toFixed(2));
            } else {
                input.val('0');
            }
        });

        $(document).on('input', '.qty_out_input', function () {
            let input = $(this);
            let value = parseFloat(input.val());

            // ambil data row
            let row = table_detail_item.row(input.closest('tr'));
            let data = row.data();

            let maxQty = parseFloat(data.qty);

            if (value > maxQty) {
                Swal.fire('Warning', 'Qty Out tidak boleh lebih besar dari Qty!', 'warning');
                input.val(parseFloat(maxQty).toFixed(2));
                value = maxQty;
            }

            if (value <= 0 || isNaN(value)) {
                input.val(1);
            }

            updateTotalQty();
        });

        $(document).on('input', '.qty_kgm_out_input', function () {
            let input = $(this);
            let value = parseFloat(input.val());

            // ambil data row
            let row = table_detail_item.row(input.closest('tr'));
            let data = row.data();

            let maxQty = parseFloat(data.qty_kgm);

            if (value > maxQty) {
                Swal.fire('Warning', 'Qty KGM Out tidak boleh lebih besar dari Qty KGM!', 'warning');
                input.val(parseFloat(maxQty).toFixed(2));
                value = maxQty;
            }

            if (value <= 0 || isNaN(value)) {
                input.val(1);
            }

            updateTotalQty();
        });

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
