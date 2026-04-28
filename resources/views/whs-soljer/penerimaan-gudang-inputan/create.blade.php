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
        <h5 class="fw-bold text-sb"><i class="fa fa-plus fa-sm"></i> Tambah Penerimaan Gudang Inputan (FABRIC)</h5>
        <a href="{{ route('penerimaan-gudang-inputan') }}" class="btn btn-primary btn-sm px-1 py-1"><i class="fas fa-reply"></i> Kembali List Penerimaan Gudang Inputan (FABRIC)</a>
    </div>
    <form action="{{ route('store-penerimaan-gudang-inputan') }}" method="post" id="store-penerimaan-gudang-inputan" onsubmit="submitForm(this, event)">
        @csrf
        <div class="card card-sb">
            <div class="card-header">
                <h5 class="card-title fw-bold">
                    Header Penginputan
                </h5>
            </div>
            <div class="card-body">
                <div class="row align-items-end">
                    <div class="row align-items-end">
                        <div class="col-3 col-md-3">
                            <div class="mb-1">
                                <label class="form-label"><small>No. BPB</small></label>
                                <input type="text" class="form-control" id="no_bpb" name="no_bpb"
                                    value="{{ $no_bpb->kode }}" readonly>
                            </div>
                        </div>

                        <div class="col-3 col-md-3">
                            <div class="mb-1">
                                <label class="form-label"><small>Tgl BPB</small></label>
                                <input type="text" class="form-control" id="tgl_bpb" name="tgl_bpb"
                                    value="{{ date('d-m-Y') }}" readonly>
                            </div>
                        </div>

                        <div class="col"></div>

                        <div class="col-auto d-flex gap-2 justify-content-end align-items-end mb-3">

                            <a class="btn btn-outline-info btn-sm"
                                data-toggle="modal"
                                data-target="#importExcel"
                                onclick="OpenModal()">
                                <i class="fas fa-file-upload fa-sm"></i>
                                Upload
                            </a>

                            <a class="btn btn-outline-warning btn-sm"
                                href="{{ route('contoh-upload-import-penerimaan-gudang-inputan') }}">
                                <i class="fas fa-file-download fa-sm"></i>
                                Contoh Upload
                            </a>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-1 col-md-1">
                            <div class="mb-1">
                                <label class="form-label"><small>No Roll</small></label>
                                <input type="text" class="form-control" style="text-transform: uppercase;" oninput="this.value = this.value.toUpperCase()" id="no_roll" name="no_roll" value="">
                            </div>
                        </div>
                        <div class="col-2 col-md-2">
                            <div class="mb-1">
                                <label class="form-label"><small>Buyer</small></label>
                                <input type="text" class="form-control" style="text-transform: uppercase;" oninput="this.value = this.value.toUpperCase()" id="buyer" name="buyer" value="">
                            </div>
                        </div>
                        <div class="col-2 col-md-2">
                            <div class="mb-1">
                                <label class="form-label"><small>Jenis Item</small></label>
                                <input type="text" class="form-control" style="text-transform: uppercase;" oninput="this.value = this.value.toUpperCase()" id="jenis_item" name="jenis_item" value="">
                            </div>
                        </div>
                        <div class="col-2 col-md-2">
                            <div class="mb-1">
                                <label class="form-label"><small>Warna</small></label>
                                <input type="text" class="form-control" style="text-transform: uppercase;" oninput="this.value = this.value.toUpperCase()" id="warna" name="warna" value="">
                            </div>
                        </div>
                        <div class="col-1 col-md-1">
                            <div class="mb-1">
                                <label class="form-label"><small>Lot</small></label>
                                <input type="text" class="form-control" style="text-transform: uppercase;" oninput="this.value = this.value.toUpperCase()" id="lot" name="lot" value="">
                            </div>
                        </div>
                        <div class="col-1 col-md-1">
                            <div class="mb-1">
                                <label class="form-label"><small>Qty</small></label>
                                <input type="number" class="form-control text-end" id="qty" name="qty" value="">
                            </div>
                        </div>
                        <div class="col-1 col-md-1">
                            <div class="mb-1">
                                <label class="form-label"><small>Satuan</small></label>
                                <select class="form-control select2bs4" id="satuan" name="satuan">
                                    <option value="">Pilih Satuan</option>
                                    @foreach($satuan as $row)
                                        <option value="{{ $row->id }}">{{ $row->nama_pilihan }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-1 col-md-1">
                            <div class="mb-1">
                                <label class="form-label"><small>Keterangan</small></label>
                                <input type="text" class="form-control" style="text-transform: uppercase;" oninput="this.value = this.value.toUpperCase()" id="keterangan" name="keterangan" value="">
                            </div>
                        </div>
                        <div class="col-1 col-md-1">
                            <div class="mb-1">
                                <label class="form-label"><small>Lokasi</small></label>
                                <select class="form-control select2bs4" id="lokasi" name="lokasi">
                                    <option value="">Pilih Lokasi</option>
                                    @foreach($lokasi as $row)
                                        <option value="{{ $row->idx }}">{{ $row->lokasi }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6 mt-3 offset-3 text-center">
                            <div class="btn btn-success btn-block mb-1 fw-bold" id="simpan_detail_item"> TAMBAH KE DETAIL ITEM</div>
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
                                    <th>No Roll</th>
                                    <th>Buyer</th>
                                    <th>Jenis Item</th>
                                    <th>Warna</th>
                                    <th>Lot</th>
                                    <th>Qty</th>
                                    <th>Satuan</th>
                                    <th>Keterangan</th>
                                    <th>Lokasi</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th colspan="5" class="text-center">TOTAL</th>
                                    <th id="total_qty" class="text-end">0</th>
                                    <th colspan="4"></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <input type="hidden" name="items" id="items">
                </div>
                <div class="col-12 col-md-6 offset-md-3 my-2 text-center">
                    <button type="submit" class="btn btn-success w-100 mb-1 fw-bold" id="btnSimpan">
                        <i class="fa fa-save"></i> SIMPAN
                    </button>
                </div>
            </div>
        </div>
    </form>

    <div class="modal fade" id="importExcel" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form method="post" action="{{ route('import-data-penerimaan-gudang-inputan') }}" enctype="multipart/form-data"
                onsubmit="submitUploadForm(this, event)">
                <div class="modal-content">
                    <div class="modal-header bg-sb text-light">
                        <h5 class="modal-title" id="exampleModalLabel">Import Excel</h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">

                        {{ csrf_field() }}

                        <label for="images" class="drop-container" id="dropcontainer">
                            <span class="drop-title">Drop files here</span>
                            or
                            <input type="file" name="file" required="required">
                        </label>


                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="fa fa-window-close"
                                aria-hidden="true"></i> Close</button>
                        <button type="submit" class="btn btn-primary toastsDefaultDanger"><i class="fa fa-thumbs-up"
                                aria-hidden="true"></i> Import</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
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
            
            table_detail_item = $('#datatable').DataTable({
                processing: true,
                serverSide: false,
                data: [],
                columns: [
                    { data: 'no_roll' },
                    { data: 'buyer' },
                    { data: 'jenis_item' },
                    { data: 'warna' },
                    { data: 'lot' },
                    { data: 'qty', className: 'text-end' },
                    { data: 'satuan' },
                    { data: 'keterangan' },
                    { data: 'lokasi' },
                    { data: 'action' },
                ]
            });

            $(document).on('click', '#simpan_detail_item', function () {
                let no_roll = $('#no_roll').val();
                let buyer = $('#buyer').val();
                let jenis_item = $('#jenis_item').val();
                let warna = $('#warna').val();
                let lot = $('#lot').val();
                let qty = $('#qty').val();
                let satuan = $('#satuan').val();
                let lokasi = $('#lokasi').val();
                let keterangan = $('#keterangan').val();
                
                let satuan_txt = $('#satuan option:selected').text();
                let lokasi_txt = $('#lokasi option:selected').text();

                // Validasi sederhana
                if (!no_roll || !buyer || !jenis_item || !warna || !lot || !qty || !satuan || !lokasi) {
                    Swal.fire('Warning', 'Semua field wajib diisi kecuali keterangan!', 'warning');
                    return;
                }

                let isDuplicate = false;

                table_detail_item.rows().every(function () {
                    let data = this.data();

                    if (
                        data.no_roll === no_roll &&
                        data.buyer === buyer &&
                        data.jenis_item === jenis_item &&
                        data.warna === warna &&
                        data.lot === lot &&
                        data.qty === qty &&
                        data.satuan === satuan_txt &&
                        data.lokasi === lokasi_txt
                    ) {
                        isDuplicate = true;
                    }
                });

                if (isDuplicate) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Data Duplikat!',
                        text: 'Data yang sama persis sudah ada di tabel.',
                    });
                    return;
                }

                table_detail_item.row.add({
                    no_roll: no_roll,
                    buyer: buyer,
                    jenis_item: jenis_item,
                    warna: warna,
                    lot: lot,
                    qty: qty,
                    satuan: satuan_txt,
                    lokasi: lokasi_txt,
                    keterangan: keterangan,
                    action: `<button type="button" class="btn btn-danger btn-sm hapus">Hapus</button>`
                }).draw(false);

                updateTotalQty();


                $("#no_roll").val("");
                $("#qty").val("");
            });

            $('#datatable tbody').on('click', '.hapus', function () {
                let row = table_detail_item.row($(this).parents('tr'));

                Swal.fire({
                    title: 'Yakin hapus?',
                    text: 'Data akan dihapus dari list',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        row.remove().draw(false);
                        updateTotalQty();

                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: 'Data berhasil dihapus',
                            timer: 1200,
                            showConfirmButton: false
                        });
                    }
                });
            });
        });


        $(document).on('click', '#btnSimpan', function() {
            let data = table_detail_item.rows().data().toArray();

            if (data.length === 0) {
                Swal.fire('Warning', 'Data masih kosong!', 'warning');
                return false;
            }

            data = data.map(row => {
                delete row.action;
                return row;
            });

            $('#items').val(JSON.stringify(data));
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
            let data = table_detail_item.rows().data().toArray();

            let total = 0;

            data.forEach(row => {
                total += parseFloat(row.qty || 0);
            });

            $('#total_qty').text(total);
        }

        function OpenModal() {
            $('#importExcel').modal('show');
        }

        function submitUploadForm(form, event) {
            event.preventDefault();

            let formData = new FormData(form);

            $.ajax({
                url: form.action,
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    if (res.status === 200) {

                        res.data.forEach(item => {
                            table_detail_item.row.add({
                                no_roll: item.no_roll,
                                buyer: item.buyer,
                                jenis_item: item.jenis_item,
                                warna: item.warna,
                                lot: item.lot,
                                qty: item.qty,
                                satuan: item.satuan,
                                lokasi: item.lokasi,
                                keterangan: item.keterangan,
                                action: `<button type="button" class="btn btn-danger btn-sm hapus">Hapus</button>`
                            }).draw(false);
                        });

                        updateTotalQty();
                        
                        $('#importExcel').modal('hide');

                        Swal.fire('Success', 'Data berhasil diimport', 'success');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Import gagal', 'error');
                }
            });
        }
        
    </script>
@endsection
