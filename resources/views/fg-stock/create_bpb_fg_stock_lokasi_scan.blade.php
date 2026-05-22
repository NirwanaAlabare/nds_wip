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
    <form action="{{ route('store-bpb-fg-stock-lokasi-scan') }}" method="post" id="store-bpb-fg-stock-lokasi-scan" onsubmit="submitForm(this, event)">
        @csrf
        <div class="card card-sb">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center ">
                    <h5 class="card-title fw-bold mb-0"><i class="fas fa-search"></i> Input Lokasi Barang Jadi Stok Scan</h5>
                    <a href="{{ route('bpb-fg-stock-lokasi-scan') }}" class="btn btn-sm btn-primary">
                        <i class="fa fa-reply"></i> Kembali
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row align-items-end">
                    <div class="col-6 col-md-3">
                        <div class="mb-1">
                            <label class="form-label"><small>Tanggal Penerimaan</small></label>
                            <input type="date" class="form-control" id="tanggal_penerimaan" name="tanggal_penerimaan" 
                                value="{{ date('Y-m-d') }}">
                        </div>
                    </div>

                     <div class="col-6 col-md-3">
                        <div class="mb-1">
                            <label class="form-label"><small>No Karton</small></label>
                            <input type="text" class="form-control" id="no_karton" name="no_karton" value="">
                        </div>
                    </div>

                    <div class="col-6 col-md-3 d-flex align-items-end">
                        <button type="button" class="btn btn-primary mb-1" id="btn_tampilkan">
                            <i class="fas fa-search"></i> Tampilkan
                        </button>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-6 col-md-3">
                        <div class="mb-1">
                            <label class="form-label"><small>Lokasi Tujuan</small></label>
                            <select class="form-control select2bs4" id="lokasi" name="lokasi">
                                <option value="">Pilih Lokasi</option>
                                @foreach($lokasi as $row)
                                    <option value="{{ $row->isi }}">{{ $row->isi }}</option>
                                @endforeach
                            </select>
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
                <div class="row align-items-end">
                    <div class="col-md-12 table-responsive">
                        <table class="table table-bordered w-100 table" id="datatable">
                            <thead>
                                <tr>
                                    <th>id</th>
                                    <th>QR Code</th>
                                    <th>Buyer</th>
                                    <th>Brand</th>
                                    <th>Style</th>
                                    <th>Grade</th>
                                    <th>WS</th>
                                    <th>Color</th>
                                    <th>Size</th>
                                    <th>Qty</th>
                                </tr>
                            </thead>
                        </table>
                        <input type="hidden" name="items" id="items">
                    </div>
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

        $(document).ready(async function () {
            // $('#barcode_scan').focus();

            // $('#barcode_scan').on('keypress', function(e) {
            //     if (e.which === 13) {
            //         e.preventDefault();

            //         let barcode = $(this).val().trim();

            //         if (!barcode) return;

            //         $('#loading').removeClass('d-none');
            //         getDataBarcode(barcode);
            //     }
            // });
            
            
        });

        $('#btnSimpan').on('click', function () {
            let result = [];

            table.rows().every(function () {
                let row = this.data();

                result.push({
                    id: row.id,
                    qr_code: row.qr_code,
                    buyer: row.buyer,
                    brand: row.brand,
                    styleno: row.styleno,
                    grade: row.grade,
                    ws: row.ws,
                    color: row.color,
                    size: row.size,
                    qty: row.qty,
                });
            });

            if (result.length === 0) {
                Swal.fire('Warning', 'Data masih kosong!', 'warning');
                return;
            }
            if ($("#lokasi").val() == '') {
                Swal.fire('Warning', 'Lokasi Wajib Diisi', 'warning');
                return;
            }

            $('#items').val(JSON.stringify(result));

            $('#store-bpb-fg-stock-lokasi-scan').submit();
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



        $('#btn_tampilkan').on('click', function () {
            let tanggal = $('#tanggal_penerimaan').val();
            let karton = $('#no_karton').val();

            getDataStockScan(tanggal, karton);
        });

        let table = $('#datatable').DataTable({
            processing: true,
            serverSide: false,
            destroy: true,
            columns: [
                {
                    data: 'id',
                    visible: false,
                },
                { data: 'qr_code' },
                { data: 'buyer' },
                { data: 'brand' },
                { data: 'styleno' },
                { data: 'grade' },
                { data: 'ws' },
                { data: 'color' },
                { data: 'size' },
                { data: 'qty' }
            ]
        });

        function getDataStockScan(tanggal_terima, no_karton) {
            $.ajax({
                url: "{{ route('get-data-stok-scan-bpb-fg-stock-lokasi-scan') }}",
                type: 'GET',
                data: {
                    tanggal_terima: tanggal_terima,
                    no_karton: no_karton,
                },
                success: function(res) {

                    if (res.status == 200) {

                        if (!res.data || res.data.length === 0) {
                            table.clear().draw();

                            Swal.fire({
                                icon: 'warning',
                                title: 'Data tidak ditemukan',
                                text: 'Tidak ada data untuk tanggal dan karton tersebut'
                            });

                            return;
                        }

                        // load data ke datatable
                        table.clear().rows.add(res.data).draw();

                    } else {
                        Swal.fire('Warning', 'Data tidak ditemukan!', 'warning');
                    }

                },
                error: function() {
                    Swal.fire('Error', 'Gagal ambil data!', 'error');
                }
            });
        }
        
    </script>
@endsection
