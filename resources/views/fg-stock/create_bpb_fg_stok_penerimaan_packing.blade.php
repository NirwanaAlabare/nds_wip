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

        .card-header-sejajar {
            min-height: 3.5rem;
        }

        #btn_kembali,
        #btn_kembali i {
            color: #000 !important;
        }

        #btn_kembali:hover,
        #btn_kembali:hover i {
            color: #fff !important;
        }
    </style>
@endsection

@section('content')
    <form action="{{ route('store-bpb-fg-stok-penerimaan-packing') }}" method="post" id="store-bpb-fg-stok-penerimaan-packing" onsubmit="submitForm(this, event)">
    @csrf
    <div class="container-fluid px-0">
        <div class="mb-3">
            <h4 class="fw-bold mb-0">
                <i class="fas fa-box-open text-primary"></i>
                Penerimaan Barang Jadi Packing
            </h4>

            <small class="text-muted">
                Pemindahan & Penginputan Pakaian dari Packing Line ke Gudang Stok
            </small>
        </div>

        <div class="row">
            <div class="col-lg-4 col-md-5 mb-3">
                <div class="card card-sb h-100">
                    <div class="card-header card-header-sejajar bg-primary text-white d-flex align-items-center">
                        <h5 class="card-title fw-bold mb-0">
                            <i class="fas fa-edit"></i>
                            Form Input Pemindahan
                        </h5>
                    </div>

                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">
                                <small>No. Transaksi Packing</small>
                            </label>
                            <select class="form-control select2bs4" id="no_transaksi_packing" name="no_transaksi_packing">
                                <option value=""> -- Pilih No. Transaksi Packing -- </option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                <small>No. Karton Asal</small>
                            </label>

                            <select class="form-control select2bs4" id="no_karton_asal" name="no_karton_asal">
                                <option value=""> -- Pilih No. Karton Asal -- </option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                <small>
                                    Detail Barang (Lokasi | WS / Style / Color / Size)
                                </small>
                            </label>

                            <select class="form-control select2bs4" id="detail_barang" name="detail_barang">
                                <option value=""> -- Pilih Detail Barang -- </option>
                            </select>

                            <input type="hidden" id="packing_out_gudang_stok_id" name="packing_out_gudang_stok_id">
                            <input type="hidden" id="so_det_id" name="so_det_id">
                            <input type="hidden" id="ppic_master_so_id" name="ppic_master_so_id">
                            <input type="hidden" id="po" name="po">
                            <input type="hidden" id="lokasi_asal" name="lokasi_asal">
                            <input type="hidden" id="grade" name="grade">
                            <input type="hidden" id="qty_tersedia" name="qty_tersedia">

                            {{-- ringkasan barang yang dipilih --}}
                            <div class="card bg-light border mt-2 d-none" id="detail_barang_info">
                                <div class="card-body py-2 px-3">
                                    <table class="table table-sm table-borderless mb-0">
                                        <tbody>
                                            <tr>
                                                <td class="ps-0 text-muted"><small>Lokasi Asal</small></td>
                                                <td class="pe-0 text-end fw-bold">
                                                    <div id="info_lokasi_asal">-</div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="ps-0 text-muted"><small>Worksheet</small></td>
                                                <td class="pe-0 text-end fw-bold">
                                                    <div id="info_ws">-</div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="ps-0 text-muted"><small>Style</small></td>
                                                <td class="pe-0 text-end fw-bold">
                                                    <div id="info_styleno">-</div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="ps-0 text-muted"><small>Color</small></td>
                                                <td class="pe-0 text-end fw-bold">
                                                    <div id="info_color">-</div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="ps-0 text-muted"><small>Grade</small></td>
                                                <td class="pe-0 text-end fw-bold">
                                                    <div id="info_grade">-</div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="ps-0 text-muted"><small>Size / Total Avail</small></td>
                                                <td class="pe-0 text-end fw-bold">
                                                    <div id="info_size_qty">-</div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                <small>No. Karton GD Target</small>
                            </label>
                            <input type="text" class="form-control" id="no_karton_gd_target" name="no_karton_gd_target" placeholder="Masukkan No. Karton GD">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                <small>Lokasi Palet Gudang</small>
                            </label>

                            <select class="form-control select2bs4" id="lokasi_palet_gudang" name="lokasi_palet_gudang">
                                <option value=""> -- Pilih Lokasi Palet -- </option>
                                @foreach($lokasi as $row)
                                    <option value="{{ $row->isi }}">
                                        {{ $row->isi }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                <small>QTY Dipindahkan (PCS)</small>
                            </label>
                            <input type="number" class="form-control" id="qty_dipindahkan" name="qty_dipindahkan" min="1" placeholder="Masukkan Jumlah QTY">
                        </div>

                        <div class="mt-4">
                            <button type="button" class="btn btn-primary w-100 fw-bold" id="btnTambah">
                                <i class="fa fa-plus"></i>
                                Tambah Data
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8 col-md-7 mb-3">
                <div class="card card-sb h-100">
                    <div class="card-header card-header-sejajar bg-dark text-white d-flex align-items-center">
                        <h5 class="card-title fw-bold mb-0">
                            <i class="fas fa-list-alt"></i>
                            Daftar Transaksi Gudang Stok
                        </h5>
                        <a href="{{ route('bpb-fg-stok-penerimaan-packing') }}" class="btn btn-sm btn-light ms-auto" id="btn_kembali">
                            <i class="fa fa-reply"></i> Kembali
                        </a>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover w-100" id="datatable">
                                <thead>
                                    <tr>
                                        <th>No Trx Packing</th>
                                        <th>Lokasi Asal</th>
                                        <th>Karton GD</th>
                                        <th>Lokasi Palet</th>
                                        <th>Worksheet</th>
                                        <th>Style</th>
                                        <th>Color</th>
                                        <th>Size</th>
                                        <th>Grade</th>
                                        <th>QTY</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>

                        <input type="hidden" name="items" id="items">
                        <div class="mt-3 text-end">
                            <button type="button" class="btn btn-success fw-bold" id="btnSimpan">
                                <i class="fa fa-save"></i>
                                Simpan Transaksi
                            </button>
                        </div>
                    </div>
                </div>
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

            $('#no_transaksi_packing').on('change', function () {
                getNoKartonAsal($(this).val());
            });

            $('#no_karton_asal').on('change', function () {
                getDetailBarang($('#no_transaksi_packing').val(), $(this).val());
            });

            $('#detail_barang').on('change', function () {
                setDetailBarang($(this).find('option:selected').data('item'));
            });

            getNoTransaksiPacking();
        });

        $('#btnTambah').on('click', function () {
            tambahData();
        });

        $('#btnSimpan').on('click', function () {
            let rows = table.rows().data().toArray();

            if (rows.length === 0) {
                Swal.fire('Warning', 'Data masih kosong!', 'warning');
                return;
            }

            let result = rows.map(function (row) {
                return {
                    packing_out_gudang_stok_id: row.packing_out_gudang_stok_id,
                    ppic_master_so_id: row.ppic_master_so_id,
                    so_det_id: row.so_det_id,
                    no_karton_asal: row.no_karton_asal,
                    po: row.po,
                    no_karton_gd_target: row.no_karton_gd_target,
                    lokasi_palet_gudang: row.lokasi_palet_gudang,
                    qty: row.qty,
                };
            });

            $('#items').val(JSON.stringify(result));

            $('#store-bpb-fg-stok-penerimaan-packing').submit();
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

        function getNoTransaksiPacking(keep) {
            keep = keep || {};

            resetSelect('#no_transaksi_packing', ' -- Pilih No. Transaksi Packing -- ');

            $.ajax({
                url: "{{ route('get-no-transaksi-bpb-fg-stok-penerimaan-packing') }}",
                type: 'GET',
                dataType: 'json',
                success: function (res) {

                    let tersedia = [];

                    $.each(res, function (i, row) {
                        let qty_total = parseInt(row.qty, 10) || 0;
                        let sisa = qty_total - getQtyDiTabel(row.no_trans);

                        // Transaksi sudah habis masuk tabel, sembunyikan dari pilihan
                        if (sisa < 1) return;

                        row.qty_total = qty_total;
                        row.qty = sisa;
                        row.text = row.no_trans;

                        tersedia.push(row);
                    });

                    fillSelect('#no_transaksi_packing', tersedia);

                    pilihUlang($('#no_transaksi_packing'), keep.no_trans, function () {
                        getNoKartonAsal(keep.no_trans, keep.no_karton);
                    });
                },
                error: function () {
                    Swal.fire('Error', 'Gagal ambil data No. Transaksi Packing!', 'error');
                }
            });
        }

        function getNoKartonAsal(no_trans, keep) {
            resetSelect('#no_karton_asal', ' -- Pilih No. Karton Asal -- ');

            if (!no_trans) return;

            $.ajax({
                url: "{{ route('get-no-karton-bpb-fg-stok-penerimaan-packing') }}",
                type: 'GET',
                dataType: 'json',
                data: {
                    no_trans: no_trans,
                },
                success: function (res) {

                    let tersedia = [];

                    $.each(res, function (i, row) {
                        let qty_total = parseInt(row.qty, 10) || 0;
                        let sisa = qty_total - getQtyDiTabel(no_trans, row.no_karton);

                        // Karton sudah habis masuk tabel, sembunyikan dari pilihan
                        if (sisa < 1) return;

                        row.qty_total = qty_total;
                        row.qty = sisa;
                        row.text = row.no_karton;

                        tersedia.push(row);
                    });

                    if (tersedia.length === 0) {
                        getNoTransaksiPacking();
                        return;
                    }

                    fillSelect('#no_karton_asal', tersedia);

                    pilihUlang($('#no_karton_asal'), keep, function () {
                        getDetailBarang(no_trans, keep);
                    });
                },
                error: function () {
                    Swal.fire('Error', 'Gagal ambil data No. Karton!', 'error');
                }
            });
        }

        function getDetailBarang(no_trans, no_karton) {
            resetSelect('#detail_barang', ' -- Pilih Detail Barang -- ');

            if (!no_trans || !no_karton) return;

            $.ajax({
                url: "{{ route('get-detail-barang-bpb-fg-stok-penerimaan-packing') }}",
                type: 'GET',
                dataType: 'json',
                data: {
                    no_trans: no_trans,
                    no_karton: no_karton,
                },
                success: function (res) {

                    let tersedia = [];

                    $.each(res, function (i, row) {
                        let qty_total = parseInt(row.qty, 10) || 0;
                        let sisa = qty_total - getQtyDiTabel(no_trans, no_karton, row.id);

                        // Barang sudah habis masuk tabel, sembunyikan dari pilihan
                        if (sisa < 1) return;

                        row.qty_total = qty_total;
                        row.qty = sisa;
                        row.text = labelDetailBarang(row, sisa);

                        tersedia.push(row);
                    });

                    fillSelect('#detail_barang', tersedia);
                },
                error: function () {
                    Swal.fire('Error', 'Gagal ambil data Detail Barang!', 'error');
                }
            });
        }

        function getQtyDiTabel(no_transaksi_packing, no_karton_asal, detail_barang) {
            let total = 0;
            let cocok = (nilai, filter) => filter === undefined || String(nilai) === String(filter);

            table.rows().every(function () {
                let row = this.data();

                if (cocok(row.no_transaksi_packing, no_transaksi_packing) &&
                    cocok(row.no_karton_asal, no_karton_asal) &&
                    cocok(row.detail_barang, detail_barang)) {
                    total += parseInt(row.qty, 10) || 0;
                }
            });

            return total;
        }

        function labelDetailBarang(row, sisa) {
            return (row.lokasi_asal || '-').toUpperCase() + ' | ' +
                (row.ws || '-') + ' / ' +
                (row.styleno || '-') + ' / ' +
                (row.color || '-') + ' / ' +
                (row.size || '-') +
                ' (Grade ' + (row.grade || '-') + ' - ' + sisa + ' PCS)';
        }

        // Isi hidden input dari detail barang yang dipilih
        function setDetailBarang(item) {
            if (!item) {
                resetDetailBarang();
                return;
            }

            $('#packing_out_gudang_stok_id').val(item.packing_out_gudang_stok_id || '');
            $('#so_det_id').val(item.so_det_id || '');
            $('#ppic_master_so_id').val(item.ppic_master_so_id || '');
            $('#po').val(item.po || '');
            $('#lokasi_asal').val(item.lokasi_asal || '');
            $('#grade').val(item.grade || '');
            $('#qty_tersedia').val(item.qty || '');

            $('#qty_dipindahkan').val(item.qty || '').attr('max', item.qty || '');

            // Ringkasan barang yang dipilih
            $('#info_lokasi_asal').text(item.lokasi_asal || '-');
            $('#info_ws').text(item.ws || '-');
            $('#info_styleno').text(item.styleno || '-');
            $('#info_color').text(item.color || '-');
            $('#info_grade').text(item.grade || '-');
            $('#info_size_qty').text((item.size || '-') + ' / ' + (item.qty || 0) + ' Pcs');

            $('#detail_barang_info').removeClass('d-none');
        }

        function tambahData() {
            let item = $('#detail_barang').find('option:selected').data('item');

            let no_transaksi_packing = $('#no_transaksi_packing').val();
            let no_karton_asal       = $('#no_karton_asal').val();
            let no_karton_gd_target  = $('#no_karton_gd_target').val().trim();
            let lokasi_palet_gudang  = $('#lokasi_palet_gudang').val();
            let qty                  = parseInt($('#qty_dipindahkan').val(), 10);
            let qty_tersedia         = parseInt($('#qty_tersedia').val(), 10);

            if (!no_transaksi_packing) {
                Swal.fire('Warning', 'No. Transaksi Packing wajib dipilih!', 'warning');
                return;
            }
            if (!no_karton_asal) {
                Swal.fire('Warning', 'No. Karton Asal wajib dipilih!', 'warning');
                return;
            }
            if (!item) {
                Swal.fire('Warning', 'Detail Barang wajib dipilih!', 'warning');
                return;
            }
            if (!no_karton_gd_target) {
                Swal.fire('Warning', 'No. Karton GD Target wajib diisi!', 'warning');
                return;
            }
            if (!lokasi_palet_gudang) {
                Swal.fire('Warning', 'Lokasi Palet Gudang wajib dipilih!', 'warning');
                return;
            }
            if (!qty || qty < 1) {
                Swal.fire('Warning', 'QTY Dipindahkan wajib diisi!', 'warning');
                return;
            }
            if (qty > qty_tersedia) {
                Swal.fire('Warning', 'QTY tidak boleh lebih dari ' + qty_tersedia + ' PCS!', 'warning');
                return;
            }

            // Cek duplikat: barang yang sama ke karton GD & palet yang sama
            let duplikat = false;

            table.rows().every(function () {
                let row = this.data();

                if (row.no_transaksi_packing === no_transaksi_packing &&
                    row.no_karton_asal === no_karton_asal &&
                    row.detail_barang === item.id &&
                    row.no_karton_gd_target === no_karton_gd_target &&
                    row.lokasi_palet_gudang === lokasi_palet_gudang) {
                    duplikat = true;
                }
            });

            if (duplikat) {
                Swal.fire({
                    icon: 'error',
                    title: 'Data Duplikat',
                    text: 'Barang ini sudah ditambahkan ke No. Karton GD & Lokasi Palet yang sama.'
                });

                return;
            }

            table.row.add({
                no_transaksi_packing: no_transaksi_packing,
                no_karton_asal: no_karton_asal,
                detail_barang: item.id,
                packing_out_gudang_stok_id: item.packing_out_gudang_stok_id,
                so_det_id: item.so_det_id,
                ppic_master_so_id: item.ppic_master_so_id,
                po: item.po,
                lokasi_asal: item.lokasi_asal,
                no_karton_gd_target: no_karton_gd_target,
                lokasi_palet_gudang: lokasi_palet_gudang,
                buyer: item.buyer,
                brand: item.brand,
                ws: item.ws,
                styleno: item.styleno,
                color: item.color,
                size: item.size,
                grade: item.grade,
                qty: qty,
            }).draw(false);

            $('#no_karton_gd_target').val('');
            $('#lokasi_palet_gudang').val('').trigger('change');

            let item_karton = $('#no_karton_asal').find('option:selected').data('item');
            let qty_total_karton = item_karton ? (parseInt(item_karton.qty_total, 10) || 0) : 0;

            if ((qty_total_karton - getQtyDiTabel(no_transaksi_packing, no_karton_asal)) < 1) {
                getNoKartonAsal(no_transaksi_packing);
            } else {
                getDetailBarang(no_transaksi_packing, no_karton_asal);
            }
        }

        function fillSelect(selector, rows) {
            let select = $(selector);

            $.each(rows, function (i, row) {
                let option = new Option(row.text, row.id, false, false);

                $(option).data('item', row);
                select.append(option);
            });

            select.trigger('change.select2');
        }

        function pilihUlang(select, nilai, lanjut) {
            let ada = nilai && select.find('option').filter(function () {
                return this.value === nilai;
            }).length > 0;

            if (ada) {
                select.val(nilai).trigger('change.select2');
                lanjut();
            } else {
                select.trigger('change');
            }
        }

        function resetSelect(selector, placeholder) {
            $(selector)
                .empty()
                .append(new Option(placeholder, '', true, true))
                .val('')
                .trigger('change');
        }

        function resetDetailBarang() {
            $('#packing_out_gudang_stok_id, #so_det_id, #ppic_master_so_id, #po, #lokasi_asal, #grade, #qty_tersedia').val('');
            $('#qty_dipindahkan').val('').removeAttr('max');

            $('#detail_barang_info').addClass('d-none');
            $('#info_lokasi_asal, #info_ws, #info_styleno, #info_color, #info_grade, #info_size_qty').text('-');
        }


        $('#btn_tampilkan').on('click', function () {
            let tanggal = $('#tanggal_penerimaan').val();
            let karton = $('#no_karton').val();

            getDataStockScan(tanggal, karton);
        });

        let table = $('#datatable').DataTable({
            processing: true,
            serverSide: false,
            destroy: true,
            searching: false,
            paging: false,
            info: false,
            ordering: false,
            data: [],
            language: {
                emptyTable: 'Belum ada data ditambahkan'
            },
            columns: [
                { data: 'no_transaksi_packing' },
                { data: 'lokasi_asal' },
                { data: 'no_karton_gd_target' },
                { data: 'lokasi_palet_gudang' },
                { data: 'ws' },
                { data: 'styleno' },
                { data: 'color' },
                { data: 'size' },
                { data: 'grade' },
                { data: 'qty' },
                {
                    data: null,
                    orderable: false,
                    className: 'text-center',
                    render: function () {
                        return '<button type="button" class="btn btn-danger btn-sm btn-hapus">' +
                            '<i class="fa fa-trash"></i>' +
                            '</button>';
                    }
                }
            ]
        });

        // Hapus baris dari tabel
        $('#datatable tbody').on('click', '.btn-hapus', function () {
            let row = table.row($(this).closest('tr'));
            let deleted = row.data();

            row.remove().draw(false);

            getNoTransaksiPacking({
                no_trans: $('#no_transaksi_packing').val() || deleted.no_transaksi_packing,
                no_karton: $('#no_karton_asal').val() || deleted.no_karton_asal,
            });
        });

    </script>
@endsection
