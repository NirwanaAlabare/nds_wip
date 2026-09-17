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

    <form action="{{ route('store_packing_out_kirim_gudang_stok') }}" method="post" id="store_packing_out_kirim_gudang_stok" onsubmit="submitForm(this, event)">
        @csrf
        <div class="card card-sb">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title fw-bold mb-0">
                        Pengeluaran Packing Ke Gudang Stok
                    </h5>
                    <a href="{{ route('packing-out') }}" class="btn btn-sm btn-light">
                        <i class="fa fa-reply"></i> Kembali
                    </a>
                </div>
            </div>

            <div class="card-body">

                <div class="mb-4">
                    <h5 class="text-dark border-bottom pb-2" style="font-size: 16px;">
                        1. Informasi Lokasi
                    </h5>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">
                                    Lokasi Asal
                                </label>
                                <select class="form-control select2bs4" id="lokasi_asal" name="lokasi_asal">
                                    <option value="Packing Central">Packing Central</option>
                                    <option value="Temporary Packing">Temporary Packing</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">
                                    Lokasi Tujuan
                                </label>
                                <input type="text" class="form-control" value="GUDANG STOK" name="tujuan" readonly>
                            </div>
                        </div>
                    </div>
                </div>


                <div>
                    <h5 class="text-dark border-bottom pb-2" style="font-size: 16px;">
                        2. Entry Karton & Detail Barang
                    </h5>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">
                                    No. Karton
                                </label>
                                <input type="text" class="form-control" id="no_karton" name="no_karton" placeholder="Masukkan No. Karton">
                            </div>
                        </div>

                        <!-- Grade -->
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">
                                    Grade
                                </label>
                                <input type="text" class="form-control" id="grade" name="grade" value="A" readonly>
                            </div>
                        </div>

                        <!-- Pilih PO -->
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">
                                    PO
                                </label>
                                <select class="form-control select2bs4" id="cbopo" name="cbopo">
                                    <option value="">-- Pilih PO --</option>
                                </select>
                            </div>
                        </div>

                        <!-- Worksheet -->
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">
                                    Worksheet (WS)
                                </label>
                                <select class="form-control select2bs4" id="cbows" name="cbows">
                                    <option value="">-- Pilih Worksheet --</option>
                                </select>
                            </div>
                        </div>

                        <!-- Style -->
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">
                                    Style
                                </label>
                                <select class="form-control select2bs4" id="cbostyle" name="cbostyle">
                                    <option value="">-- Pilih Style --</option>
                                </select>
                            </div>
                        </div>

                        <!-- Color -->
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">
                                    Color
                                </label>
                                <select class="form-control select2bs4" id="cbocolor" name="cbocolor">
                                    <option value="">-- Pilih Color --</option>
                                </select>
                            </div>
                        </div>

                        <!-- Size -->
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">
                                    Size
                                </label>
                                <select class="form-control select2bs4" id="cbosize" name="cbosize">
                                    <option value="">-- Pilih Size --</option>
                                </select>
                            </div>
                        </div>

                        <!-- Qty -->
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">
                                    Qty (Pcs)
                                </label>
                                <input type="number" class="form-control" id="qty" name="qty" value="" min="0" max="0">
                            </div>
                        </div>
                    </div>


                    <!-- Button -->
                    <div class="d-flex justify-content-end gap-2 mt-2">
                        <button type="button" class="btn btn-secondary px-4" id="reset_entry">
                            Reset Entry
                        </button>

                        <button type="button" class="btn btn-primary px-4" id="simpan_detail_item">
                            + Tambah ke Tabel
                        </button>
                    </div>
                </div>


                <div class="mt-4">
                    <h5 class="text-dark border-bottom pb-2" style="font-size: 16px;">
                        3. Daftar Barang Siap Kirim
                    </h5>

                    <div class="row align-items-end">
                        <div class="col-md-12 table-responsive">
                            <table class="table table-bordered w-100 table" id="datatable">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Lokasi Asal</th>
                                        <th>No. Karton</th>
                                        <th>Grade</th>
                                        <th>PO</th>
                                        <th>WS</th>
                                        <th>Style</th>
                                        <th>Color</th>
                                        <th>Size</th>
                                        <th>Qty</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tfoot>
                                    <tr>
                                        <th colspan="9" class="text-center">TOTAL</th>
                                        <th id="total_qty" class="text-end">0</th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <input type="hidden" name="items" id="items">

                        <div class="col-12 my-2 text-end">
                            <button type="submit" class="btn btn-success px-4 mb-1 fw-bold" id="btnSimpan">
                                <i class="fa fa-save"></i> Simpan Transaksi
                            </button>
                        </div>
                    </div>
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

        $(document).ready(function() {

            table_detail_item = $('#datatable').DataTable({
                processing: true,
                serverSide: false,
                data: [],
                columns: [
                    { data: 'no' },
                    { data: 'lokasi_asal' },
                    { data: 'no_karton' },
                    { data: 'grade' },
                    { data: 'po' },
                    { data: 'ws' },
                    { data: 'style' },
                    { data: 'color' },
                    { data: 'size' },
                    {
                        data: 'qty',
                        className: 'text-end'
                    },
                    {
                        data: null,
                        className: 'text-center',
                        orderable: false,
                        render: function() {
                            return `
                                <button type="button" class="btn btn-danger btn-sm hapus">
                                    Hapus
                                </button>
                            `;
                        }
                    }
                ]
            });


            // =========================
            // TAMBAH KE TABEL
            // =========================
            $(document).on('click', '#simpan_detail_item', function() {

                let lokasi_asal = $('#lokasi_asal').val();
                let no_karton = $('#no_karton').val().trim();
                let grade = $('#grade').val();
                let po = $('#cbopo').val();
                let ws = $('#cbows').val();
                let style = $('#cbostyle').val();
                let color = $('#cbocolor').val();
                let size = $('#cbosize').val();
                let qty = parseInt($('#qty').val()) || 0;

                // Id referensi dari option size yang dipilih (dikirim getsize_...)
                let so_det_id = $('#cbosize option:selected').data('so-det-id') || null;
                let ppic_master_so_id = $('#cbosize option:selected').data('ppic-master-so-id') || null;

                // Validasi
                if (
                    !lokasi_asal ||
                    !no_karton ||
                    !grade ||
                    !po ||
                    !ws ||
                    !style ||
                    !color ||
                    !size ||
                    qty < 1
                ) {
                    iziToast.warning({
                        title: 'Warning',
                        message: 'Semua field wajib diisi!',
                        position: 'topCenter'
                    });
                    return;
                }

                let max_qty = parseInt($('#qty').attr('max')) || 0;

                // Validasi qty
                if (qty > max_qty) {
                    iziToast.warning({
                        title: 'Warning',
                        message: `Qty tidak boleh lebih dari ${max_qty} PCS!`,
                        position: 'topCenter'
                    });
                    return;
                }

                // Cek duplikat
                let isDuplicate = false;

                table_detail_item.rows().every(function() {
                    let data = this.data();

                    if (
                        data.lokasi_asal === lokasi_asal &&
                        data.no_karton === no_karton &&
                        data.grade === grade &&
                        data.po === po &&
                        data.ws === ws &&
                        data.style === style &&
                        data.color === color &&
                        data.size === size &&
                        String(data.so_det_id) === String(so_det_id)
                    ) {
                        isDuplicate = true;
                    }
                });

                if (isDuplicate) {
                    iziToast.error({
                        title: 'Data Duplikat',
                        message: 'Data dengan No. Karton, PO, WS, Style, Color dan Size yang sama sudah ada di tabel.',
                        position: 'topCenter'
                    });
                    return;
                }

                // Tambah ke tabel
                table_detail_item.row.add({
                    no: table_detail_item.rows().count() + 1,
                    lokasi_asal: lokasi_asal,
                    no_karton: no_karton,
                    grade: grade,
                    po: po,
                    ws: ws,
                    style: style,
                    color: color,
                    size: size,
                    qty: qty,
                    so_det_id: so_det_id,
                    ppic_master_so_id: ppic_master_so_id
                }).draw(false);

                // Reset: jika qty WS ini sudah habis dipakai di detail, reload daftar WS
                let qty_ws = parseInt($('#cbows option:selected').data('qty')) || 0;

                if (qty_ws - getQtyDiDetail(lokasi_asal, po, ws) < 1) {
                    getWSByPO();
                } else {
                    getStyleByWS();
                }

                updateTotalQty();

                iziToast.success({
                    title: 'Berhasil',
                    message: 'Data berhasil ditambahkan.',
                    position: 'topCenter'
                });
            });


            // =========================
            // HAPUS DATA
            // =========================
            $(document).on('click', '.hapus', function() {
                let row = table_detail_item.row($(this).closest('tr'));
                let deleted = row.data();

                row.remove().draw(false);

                // Update nomor
                table_detail_item.rows().every(function(index) {
                    let data = this.data();
                    data.no = index + 1;
                    this.data(data);
                });

                table_detail_item.draw(false);

                // Qty yang dihapus kembali tersedia: WS dipertahankan (atau dikembalikan
                // ke WS data yang dihapus jika sedang kosong), style/color/size di-reset
                // supaya user pilih ulang dari list yang sudah ter-update
                if (
                    deleted.lokasi_asal === $('#lokasi_asal').val() &&
                    deleted.po === $('#cbopo').val()
                ) {
                    getWSByPO({ ws: $('#cbows').val() || deleted.ws });
                }

                updateTotalQty();

                iziToast.success({
                    title: 'Berhasil',
                    message: 'Data berhasil dihapus.',
                    position: 'topCenter'
                });
            });

            getPOByLokasiAsal();
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

        $('#lokasi_asal').change(function() {
            getPOByLokasiAsal();

            // reset WS
            $('#cbows').html('<option value="">-- Pilih Worksheet --</option>');
            $('#cbows').trigger('change');
        });

        function getPOByLokasiAsal() {

            let lokasi_asal = $('#lokasi_asal').val();

            if (!lokasi_asal) {
                $('#cbopo').html('<option value="">-- Pilih PO --</option>');
                return;
            }

            // Hapus Select2 sebelumnya
            if ($('#cbopo').hasClass('select2-hidden-accessible')) {
                $('#cbopo').select2('destroy');
            }

            // Tetap jadikan -- Pilih PO -- sebagai option
            $('#cbopo').html('<option value="">-- Pilih PO --</option>');

            // PACKING CENTRAL
            if (lokasi_asal === 'Packing Central') {

                $('#cbopo').select2({
                    theme: 'bootstrap4',
                    minimumInputLength: 3,
                    language: {
                        inputTooShort: function() {
                            return 'Ketik minimal 3 karakter';
                        }
                    },
                    ajax: {
                        url: "{{ route('getpo_packing_out_kirim_gudang_stok') }}",
                        type: 'POST',
                        dataType: 'json',
                        delay: 300,
                        data: function(params) {
                            return {
                                _token: "{{ csrf_token() }}",
                                lokasi_asal: lokasi_asal,
                                search: params.term
                            };
                        },
                        processResults: function(response) {
                            return {
                                results: $.map(response, function(item) {
                                    return {
                                        id: item.po,
                                        text: item.po
                                    };
                                })
                            };
                        },
                        cache: true
                    }
                });

            } else {

                // Lokasi selain Packing Central
                $.ajax({
                    url: "{{ route('getpo_packing_out_kirim_gudang_stok') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        lokasi_asal: lokasi_asal
                    },
                    success: function(response) {

                        $('#cbopo').html('<option value="">-- Pilih PO --</option>');

                        $.each(response, function(index, item) {
                            $('#cbopo').append(
                                `<option value="${item.po}">${item.po}</option>`
                            );
                        });

                        $('#cbopo').trigger('change');
                    },
                    error: function(xhr) {
                        console.log(xhr.responseText);
                    }
                });

                // Init Select2 tetap Bootstrap 4
                $('#cbopo').select2({
                    theme: 'bootstrap4',
                });
            }
        }


        // Ketika PO dipilih
        // Pilih ulang nilai sebelumnya (jika masih ada di daftar) lalu lanjut load level berikutnya.
        // Jika tidak ada, trigger change biasa sehingga level di bawahnya ter-reset.
        function pilihUlang($select, nilai, lanjut) {
            let ada = nilai && $select.find('option').filter(function() {
                return this.value === nilai;
            }).length > 0;

            if (ada) {
                // change.select2 hanya update tampilan select2, tidak memanggil handler change
                $select.val(nilai).trigger('change.select2');
                lanjut();
            } else {
                $select.trigger('change');
            }
        }

        $('#cbopo').change(function() {
            getWSByPO();
        });

        function getWSByPO(keep) {

            let lokasi_asal = $('#lokasi_asal').val();
            let po = $('#cbopo').val();

            $('#cbows').html('<option value="">-- Pilih Worksheet --</option>');

            if (!po) {
                return;
            }

            $.ajax({
                url: "{{ route('getws_packing_out_kirim_gudang_stok') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    lokasi_asal: lokasi_asal,
                    po: po
                },
                success: function(response) {

                    $('#cbows').html('<option value="">-- Pilih Worksheet --</option>');

                    $.each(response, function(index, item) {
                        let sisa = (parseInt(item.qty) || 0) - getQtyDiDetail(lokasi_asal, po, item.ws);

                        if (sisa < 1) {
                            return;
                        }

                        $('#cbows').append(
                            `<option value="${item.ws}" data-qty="${parseInt(item.qty) || 0}">${item.ws}</option>`
                        );
                    });

                    pilihUlang($('#cbows'), keep && keep.ws, () => getStyleByWS(keep));
                },
                error: function(xhr) {
                    console.log(xhr.responseText);
                }
            });
        }

        // $('#cbows').change(function() {
        //     getStyleByWS();
        // });

        // function getStyleByWS(keep) {

        //     let lokasi_asal = $('#lokasi_asal').val();
        //     let po = $('#cbopo').val();
        //     let ws = $('#cbows').val();

        //     $('#cbostyle').html('<option value="">-- Pilih Style --</option>');
        //     $('#cbocolor').html('<option value="">-- Pilih Color --</option>');
        //     $('#cbosize').html('<option value="">-- Pilih Size --</option>');
        //     $('#qty').val('');

        //     if (!ws) {
        //         return;
        //     }

        //     $.ajax({
        //         url: "{{ route('getstyle_packing_out_kirim_gudang_stok') }}",
        //         type: "POST",
        //         data: {
        //             _token: "{{ csrf_token() }}",
        //             lokasi_asal: lokasi_asal,
        //             po: po,
        //             ws: ws
        //         },
        //         success: function(response) {

        //             $('#cbostyle').html('<option value="">-- Pilih Style --</option>');

        //             $.each(response, function(index, item) {
        //                 let sisa = (parseInt(item.qty) || 0) - getQtyDiDetail(lokasi_asal, po, ws, item.style);

        //                 if (sisa < 1) {
        //                     return;
        //                 }

        //                 $('#cbostyle').append(
        //                     `<option value="${item.style}">${item.style}</option>`
        //                 );
        //             });

        //             pilihUlang($('#cbostyle'), keep && keep.style, () => getColorByStyle(keep));
        //         },
        //         error: function(xhr) {
        //             console.log(xhr.responseText);
        //         }
        //     });
        // }

        // $('#cbostyle').change(function() {
        //     getColorByStyle();
        // });

        $('#cbows').change(function() {
            getStyleByWS();
        });

        function getStyleByWS() {
            let lokasi_asal = $('#lokasi_asal').val();
            let po = $('#cbopo').val();
            let ws = $('#cbows').val();

            $('#cbostyle').html('<option value="">-- Pilih Style --</option>');
            $('#cbocolor').html('<option value="">-- Pilih Color --</option>');
            $('#cbosize').html('<option value="">-- Pilih Size --</option>');
            $('#qty').val('');

            if (!ws) {
                return;
            }

            $.ajax({
                url: "{{ route('getstyle_packing_out_kirim_gudang_stok') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    lokasi_asal: lokasi_asal,
                    po: po,
                    ws: ws
                },
                success: function(response) {
                    $('#cbostyle').html('<option value="">-- Pilih Style --</option>');

                    let firstStyle = '';

                    $.each(response, function(index, item) {
                        let sisa = (parseInt(item.qty) || 0) -
                            getQtyDiDetail(lokasi_asal, po, ws, item.style);

                        if (sisa < 1) {
                            return;
                        }

                        if (!firstStyle) {
                            firstStyle = item.style;
                        }

                        $('#cbostyle').append(
                            `<option value="${item.style}">${item.style}</option>`
                        );
                    });

                    // Otomatis pilih style pertama
                    if (firstStyle) {
                        $('#cbostyle').val(firstStyle).trigger('change');
                    }
                },
                error: function(xhr) {
                    console.log(xhr.responseText);
                }
            });
        }

        $('#cbostyle').change(function() {
            getColorByStyle();
        });

        function getColorByStyle(keep) {

            let lokasi_asal = $('#lokasi_asal').val();
            let po = $('#cbopo').val();
            let ws = $('#cbows').val();
            let style = $('#cbostyle').val();

            $('#cbocolor').html('<option value="">-- Pilih Color --</option>');
            $('#cbosize').html('<option value="">-- Pilih Size --</option>');
            $('#qty').val('');

            if (!style) {
                return;
            }

            $.ajax({
                url: "{{ route('getcolor_packing_out_kirim_gudang_stok') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    lokasi_asal: lokasi_asal,
                    po: po,
                    ws: ws,
                    style: style
                },
                success: function(response) {

                    $('#cbocolor').html('<option value="">-- Pilih Color --</option>');

                    $.each(response, function(index, item) {
                        let sisa = (parseInt(item.qty) || 0) - getQtyDiDetail(lokasi_asal, po, ws, style, item.color);

                        if (sisa < 1) {
                            return;
                        }

                        $('#cbocolor').append(
                            `<option value="${item.color}">${item.color}</option>`
                        );
                    });

                    pilihUlang($('#cbocolor'), keep && keep.color, () => getSizeByColor(keep));
                },
                error: function(xhr) {
                    console.log(xhr.responseText);
                }
            });
        }

        $('#cbocolor').change(function() {
            getSizeByColor();
        });

        function getSizeByColor(keep) {

            let lokasi_asal = $('#lokasi_asal').val();
            let po = $('#cbopo').val();
            let ws = $('#cbows').val();
            let style = $('#cbostyle').val();
            let color = $('#cbocolor').val();

            $('#cbosize').html('<option value="">-- Pilih Size --</option>');
            $('#qty').val('');

            if (!color) {
                return;
            }

            $.ajax({
                url: "{{ route('getsize_packing_out_kirim_gudang_stok') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    lokasi_asal: lokasi_asal,
                    po: po,
                    ws: ws,
                    style: style,
                    color: color
                },
                success: function(response) {

                    $('#cbosize').html('<option value="">-- Pilih Size --</option>');

                    $.each(response, function(index, item) {
                        let sisa = (parseInt(item.qty) || 0) - getQtyDiDetail(lokasi_asal, po, ws, style, color, item.size, item.id_so_det);

                        // Size yang qty-nya sudah habis dipakai di detail tidak ditampilkan
                        if (sisa < 1) {
                            return;
                        }

                        // so_det_id & ppic_master_so_id ikut dibawa di option supaya
                        // bisa disimpan bersama baris detail item
                        $('#cbosize').append(
                            `<option value="${item.size}"
                                data-so-det-id="${item.id_so_det || ''}"
                                data-ppic-master-so-id="${item.id_ppic_master_so || ''}">${item.size} - ${sisa} PCS</option>`
                        );
                    });

                    pilihUlang($('#cbosize'), keep && keep.size, () => getQtyBySize());
                },
                error: function(xhr) {
                    console.log(xhr.responseText);
                }
            });
        }

        $('#cbosize').select2({
            theme: 'bootstrap4',

            templateResult: function(item) {

                if (!item.id) {
                    return item.text;
                }

                let parts = item.text.split(' - ');

                return $(`
                    <div style="
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        width: 100%;
                    ">
                        <span>${parts[0]}</span>

                        <span style="
                            background-color: #28a745;
                            color: white;
                            padding: 2px 6px;
                            border-radius: 3px;
                            font-weight: bold;
                            font-size: 12px;
                            line-height: 16px;
                        ">
                            ${parts[1]}
                        </span>
                    </div>
                `);
            },

            templateSelection: function(item) {

                if (!item.id) {
                    return item.text;
                }

                let parts = item.text.split(' - ');

                return $(`
                    <span style="
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        width: 100%;
                        line-height: normal;
                    ">
                        <span style="
                            line-height: 35px;
                        ">
                            ${parts[0]}
                        </span>

                        <span style="
                            background-color: #28a745;
                            color: white;
                            padding: 1px 5px;
                            border-radius: 3px;
                            font-weight: bold;
                            font-size: 11px;
                            line-height: 14px;
                        ">
                            ${parts[1]}
                        </span>
                    </span>
                `);
            }
        });

        $('#cbosize').change(function() {
            getQtyBySize();
        });

        // Total qty yang sudah masuk table_detail_item untuk kombinasi yang sama
        // (semua no karton & grade, karena memakai stok yang sama)
        // Parameter yang tidak dikirim (undefined) tidak ikut difilter,
        // misal getQtyDiDetail(lokasi_asal, po, ws) = total per WS
        function getQtyDiDetail(lokasi_asal, po, ws, style, color, size, so_det_id) {
            let total = 0;
            let cocok = (nilai, filter) => filter === undefined || String(nilai) === String(filter);

            table_detail_item.rows().every(function() {
                let data = this.data();

                if (
                    cocok(data.lokasi_asal, lokasi_asal) &&
                    cocok(data.po, po) &&
                    cocok(data.ws, ws) &&
                    cocok(data.style, style) &&
                    cocok(data.color, color) &&
                    cocok(data.size, size) &&
                    cocok(data.so_det_id, so_det_id)
                ) {
                    total += parseInt(data.qty) || 0;
                }
            });

            return total;
        }

        function getQtyBySize() {

            let lokasi_asal = $('#lokasi_asal').val();
            let po = $('#cbopo').val();
            let ws = $('#cbows').val();
            let style = $('#cbostyle').val();
            let color = $('#cbocolor').val();
            let size = $('#cbosize').val();
            let so_det_id = $('#cbosize option:selected').data('so-det-id') || null;

            $('#qty').val('');

            if (!size) {
                return;
            }

            $.ajax({
                url: "{{ route('getqty_packing_out_kirim_gudang_stok') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    lokasi_asal: lokasi_asal,
                    po: po,
                    ws: ws,
                    style: style,
                    color: color,
                    size: size,
                    so_det_id: so_det_id
                },
                success: function(response) {

                    let qty = (parseInt(response.qty) || 0) - getQtyDiDetail(lokasi_asal, po, ws, style, color, size, so_det_id);
                    qty = Math.max(qty, 0);
                    $('#qty').val(qty).attr('min', qty > 0 ? 1 : 0).attr('max', qty);
                },
                error: function(xhr) {
                    console.log(xhr.responseText);
                }
            });
        }

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
            
        

        $('#qty').on('input', function() {

            let max = parseInt($(this).attr('max')) || 0;
            let min = parseInt($(this).attr('min')) || 1;
            let value = parseInt($(this).val()) || 0;

            if (value > max) {
                $(this).val(max);
            }

            if (value < min && value !== 0) {
                $(this).val(min);
            }
        });

        $("#reset_entry").on('click', function() {
            getStyleByWS();
        });

        function updateTotalQty() {
            let data = table_detail_item.rows().data().toArray();

            let total = 0;

            data.forEach(row => {
                total += parseFloat(row.qty || 0);
            });

            $('#total_qty').text(total);
        }

    </script>
@endsection
