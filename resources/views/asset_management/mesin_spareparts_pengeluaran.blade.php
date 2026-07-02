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

        .form-control {
            border: 1.5px solid #ced4da;
            border-radius: 8px;
            padding: 6px 10px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.15rem rgba(13, 110, 253, 0.25);
        }

        .dataTables_length select {
            width: auto;
            min-width: 65px;
            padding-right: 24px;
        }

        .td-truncate {
            max-width: 220px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .select2-option-truncate {
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
    </style>
@endsection

@section('content')
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-minus"></i> Pengeluaran Spareparts (Pembelian)</h5>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                    data-bs-target="#NewMesinModal">
                    <i class="fas fa-plus"></i> New
                </button>
            </div>
            <div class="mb-3 d-flex align-items-end gap-2 flex-wrap">
                <div>
                    <label for="txttgl_awal" class="col-form-label"><small><b>Tgl Awal :</b></small></label>
                    <input type="date" id="txttgl_awal" class="form-control form-control-sm">
                </div>
                <div>
                    <label for="txttgl_akhir" class="col-form-label"><small><b>Tgl Akhir :</b></small></label>
                    <input type="date" id="txttgl_akhir" class="form-control form-control-sm">
                </div>
                <button type="button" class="btn btn-primary btn-sm" onclick="dataTableReload();">
                    <i class="fas fa-search"></i> Search
                </button>
                <button type="button" class="btn btn-success btn-sm"
                    onclick="export_excel_pengeluaran_spareparts_mesin();">
                    <i class="fas fa-file-excel"></i> Export
                </button>
            </div>
            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-hover align-middle text-nowrap w-100">
                    <thead class="bg-sb">
                        <tr>
                            <th scope="col" class="text-center align-middle">Tgl Keluar</th>
                            <th scope="col" class="text-center align-middle">Nama Barang</th>
                            <th scope="col" class="text-center align-middle">Rak</th>
                            <th scope="col" class="text-center align-middle">Serial Number</th>
                            <th scope="col" class="text-center align-middle">Jenis</th>
                            <th scope="col" class="text-center align-middle">Merk</th>
                            <th scope="col" class="text-center align-middle">Unit Mesin</th>
                            <th scope="col" class="text-center align-middle">Mekanik</th>
                            <th scope="col" class="text-center align-middle">Qty</th>
                            <th scope="col" class="text-center align-middle">BPB</th>
                            <th scope="col" class="text-center align-middle">Dibuat Oleh</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal New Pengeluaran Spareparts Mesin -->
    <div class="modal fade" id="NewMesinModal" tabindex="-1" aria-labelledby="NewMesinModalLabel" aria-hidden="true"
        data-bs-backdrop="static">
        <div class="modal-dialog modal-xl" style="max-width: 95vw;">
            <div class="modal-content">
                <div class="modal-header bg-sb text-white">
                    <h5 class="modal-title" id="NewMesinModalLabel">Pengeluaran Spareparts Mesin</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="card bg-light border mb-3">
                        <div class="card-body py-3">
                            <div class="d-flex align-items-end gap-2 flex-wrap">
                                <div style="flex: 2 1 220px; min-width: 0;">
                                    <label for="cbosparepart" class="col-form-label"><small><b>Sparepart
                                                :</b></small></label>
                                    <select id="cbosparepart" name="cbosparepart"
                                        class="form-control form-control-sm select2bs4 border-primary" style="width: 100%;">
                                        <option value="">-- Pilih Sparepart --</option>
                                    </select>
                                </div>
                                <div style="flex: 2 1 220px; min-width: 0;">
                                    <label for="cborak" class="col-form-label"><small><b>Rak :</b></small></label>
                                    <select id="cborak" name="cborak" disabled
                                        class="form-control form-control-sm select2bs4 border-primary"
                                        style="width: 100%;">
                                        <option value="">-- Pilih Sparepart Dahulu --</option>
                                    </select>
                                </div>
                                <div style="flex: 2 1 220px; min-width: 0;">
                                    <label for="cbomesin" class="col-form-label"><small><b>Unit Mesin
                                                :</b></small></label>
                                    <select id="cbomesin" name="cbomesin"
                                        class="form-control form-control-sm select2bs4 border-primary"
                                        style="width: 100%;">
                                        <option value="">-- Pilih Unit Mesin (Serial Number) --</option>
                                    </select>
                                </div>
                                <div style="flex: 1 1 180px; min-width: 0;">
                                    <label for="cbomekanik" class="col-form-label"><small><b>Mekanik :</b></small></label>
                                    <select id="cbomekanik" name="cbomekanik"
                                        class="form-control form-control-sm select2bs4 border-primary"
                                        style="width: 100%;">
                                        <option value="">-- Pilih Mekanik --</option>
                                    </select>
                                </div>
                                <div style="flex: 0 1 110px; min-width: 90px;">
                                    <label for="txtqty" class="col-form-label"><small><b>Qty :</b></small></label>
                                    <input type="number" id="txtqty" name="txtqty" min="1"
                                        class="form-control form-control-sm" placeholder="Qty">
                                </div>
                                <div>
                                    <label class="col-form-label d-block">&nbsp;</label>
                                    <button type="button" id="btnTambahPengeluaranSparepart"
                                        class="btn btn-primary btn-sm">
                                        <i class="fas fa-plus"></i> Tambah
                                    </button>
                                </div>
                            </div>
                            <div class="mt-2">
                                <small class="text-muted">Stok tersedia di rak ini:
                                    <span class="badge bg-info text-dark" id="stokSparepartLabel">-</span></small>
                            </div>
                        </div>
                    </div>
                    <div class="mb-2 d-flex justify-content-between align-items-center">
                        <small class="text-muted fw-bold">Daftar sparepart yang akan dikeluarkan</small>
                        <span class="badge bg-primary">Total Baris: <span id="pengeluaranSparepartCounter">0</span></span>
                    </div>
                    <div class="table-responsive">
                        <table id="pengeluaranSparepartTable"
                            class="table table-bordered table-hover table-sm align-middle text-nowrap w-100 mb-0">
                            <thead class="bg-sb">
                                <tr>
                                    <th scope="col" class="text-center" style="width: 40px;">No</th>
                                    <th scope="col">Rak</th>
                                    <th scope="col">Sparepart</th>
                                    <th scope="col">Info Mesin</th>
                                    <th scope="col">Mekanik</th>
                                    <th scope="col" class="text-center">Qty</th>
                                    <th scope="col" class="text-center" style="width: 60px;">Act</th>
                                </tr>
                            </thead>
                            <tbody id="pengeluaranSparepartTableBody">
                                <tr id="pengeluaranSparepartEmptyRow">
                                    <td colspan="7" class="text-center text-muted">Belum ada sparepart yang ditambahkan
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" id="btnSimpanPengeluaranSparepart" class="btn btn-success btn-sm"
                        onclick="save_pengeluaran_spareparts_mesin();">
                        <i class="fas fa-save"></i> Simpan
                    </button>
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

    <script>
        // Modul Asset: senyapkan alert bawaan DataTables saat ajax gagal, cukup dicatat di console
        $.fn.dataTable.ext.errMode = function (settings, techNote, message) {
            console.error('DataTable ajax error:', message);
        };
    </script>
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        // Select2 Autofocus
        $(document).on('select2:open', () => {
            document.querySelector('.select2-search__field').focus();

            // Pastikan dropdown select2 tampil di atas modal/backdrop yang sedang terbuka (modal bertumpuk)
            let maxZ = 1051;
            $('.modal.show').each(function() {
                let z = parseInt($(this).css('z-index')) || 0;
                if (z > maxZ) maxZ = z;
            });
            $('.select2-dropdown').last().css('z-index', maxZ + 10);
        });

        // Initialize Select2 Elements
        $('.select2').select2();

        // Initialize Select2BS4 Elements
        // dropdownParent diarahkan ke modal terdekat agar pencarian tetap bisa diketik
        // (modal Bootstrap 5 menahan focus, sehingga dropdown yang nempel di <body> jadi tidak bisa diketik)
        // #cbomesin di-skip di sini karena diinisialisasi sendiri di bawah dengan templateResult/templateSelection custom
        $('.select2bs4:not(#cbomesin)').each(function() {
            let $modal = $(this).closest('.modal');

            $(this).select2({
                theme: 'bootstrap4',
                width: 'resolve', // Ensures it respects the 100% width from inline style or Bootstrap
                dropdownParent: $modal.length ? $modal : $(document.body)
            });
        });
        // Now set height and font-size on the Select2 container after init
        $('.select2-container--bootstrap4 .select2-selection--single').css({
            'height': '30px', // your desired height
            'font-size': '12px', // your desired font size
            'line-height': '30px' // vertically center text
        });

        // Default filter tanggal awal & akhir ke tanggal hari ini
        let todayStr = new Date().toISOString().slice(0, 10);
        $('#txttgl_awal').val(todayStr);
        $('#txttgl_akhir').val(todayStr);
    </script>
    <script>
        function dataTableReload() {
            datatable.ajax.reload();
        }

        function export_excel_pengeluaran_spareparts_mesin() {
            let tgl_awal = $('#txttgl_awal').val();
            let tgl_akhir = $('#txttgl_akhir').val();

            Swal.fire({
                title: 'Please Wait,',
                html: 'Exporting Data...',
                didOpen: () => {
                    Swal.showLoading();
                },
                allowOutsideClick: false,
            });

            $.ajax({
                type: 'get',
                url: '{{ route('export_excel_pengeluaran_spareparts_mesin') }}',
                data: {
                    tgl_awal: tgl_awal,
                    tgl_akhir: tgl_akhir
                },
                xhrFields: {
                    responseType: 'blob'
                },
                success: function(response) {
                    Swal.close();
                    Swal.fire({
                        title: 'Data Berhasil Di Export!',
                        icon: 'success',
                        showConfirmButton: true,
                        allowOutsideClick: false
                    });
                    let blob = new Blob([response]);
                    let link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = 'Laporan Pengeluaran Spareparts Mesin ' + tgl_awal + ' sd ' + tgl_akhir +
                        '.xlsx';
                    link.click();
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    Swal.close();
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Gagal export data ke Excel.',
                    });
                }
            });
        }

        let datatable = $("#datatable").DataTable({
            ordering: false,
            responsive: false,
            processing: true,
            serverSide: false,
            paging: true,
            searching: true,
            scrollY: true,
            scrollX: true,
            scrollCollapse: false,
            ajax: {
                url: '{{ route('asset_mesin_spareparts_pengeluaran_list') }}',
                data: function(d) {
                    d.tgl_awal = $('#txttgl_awal').val();
                    d.tgl_akhir = $('#txttgl_akhir').val();
                }
            },
            columns: [{
                    data: 'tgl_trans_fix'
                }, // Tgl Keluar
                {
                    data: 'itemdesc',
                    className: 'td-truncate',
                    render: function(data) {
                        return `<span title="${data ?? ''}">${data ?? '-'}</span>`;
                    }
                }, // Nama Barang
                {
                    data: null,
                    className: 'td-truncate',
                    render: function(data, type, row) {
                        let rak = [row.no_rak, row.nm_rak].filter(Boolean).join(' - ') || '-';
                        if (row.rak_desc) rak += ` (${row.rak_desc})`;
                        return `<span title="${rak}">${rak}</span>`;
                    }
                }, // Rak
                {
                    data: 'serial_number',
                    render: function(data) {
                        return data ?? '-';
                    }
                }, // Serial Number
                {
                    data: 'nm_jenis',
                    render: function(data) {
                        return data ?? '-';
                    }
                }, // Jenis
                {
                    data: 'nm_merk',
                    render: function(data) {
                        return data ?? '-';
                    }
                }, // Merk
                {
                    data: null,
                    className: 'td-truncate',
                    render: function(data, type, row) {
                        let mesin = row.mesin_desc || '-';
                        if (row.tipe) mesin += ` (${row.tipe})`;
                        return `<span title="${mesin}">${mesin}</span>`;
                    }
                }, // Unit Mesin
                {
                    data: 'mekanik_name',
                    render: function(data) {
                        return data ?? '-';
                    }
                }, // Mekanik
                {
                    data: 'qty'
                }, // Qty
                {
                    data: 'bpbno_int',
                    render: function(data) {
                        return data ?? '-';
                    }
                }, // BPB
                {
                    data: 'created_by',
                    render: function(data) {
                        return data ?? '-';
                    }
                }, // Dibuat Oleh
            ],
        });

        // Daftar sparepart yang masih ada stok (dijumlah dari semua rak), dipilih dulu sebelum tentukan rak
        function loadSparepartSelect() {
            $.ajax({
                type: 'GET',
                url: '{{ route('asset_mesin_spareparts_pengeluaran_sparepart_select') }}',
                success: function(rows) {
                    let $select = $('#cbosparepart').empty().append(
                        '<option value="">-- Pilih Sparepart --</option>');

                    rows.forEach(function(row) {
                        $('<option>', {
                                value: row.id_item,
                                text: `${row.itemdesc ?? '-'} (Stok Total: ${row.stok})`
                            })
                            .appendTo($select);
                    });

                    $select.val('').trigger('change');
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Gagal memuat daftar sparepart.',
                    });
                }
            });
        }

        // Rak yang masih ada stok sparepart terpilih saja, dimuat ulang setiap kali dropdown Sparepart berubah
        function loadRakByItem(idItem) {
            let $select = $('#cborak').empty()
                .append('<option value="">-- Pilih Sparepart Dahulu --</option>')
                .prop('disabled', true).trigger('change');
            $('#stokSparepartLabel').text('-');
            $('#txtqty').val('').removeAttr('max');

            if (!idItem) return;

            $select.empty().append('<option value="">-- Pilih Rak --</option>');

            $.ajax({
                type: 'GET',
                url: '{{ route('asset_mesin_spareparts_pengeluaran_rak_by_sparepart') }}',
                data: {
                    id_item: idItem
                },
                success: function(rows) {
                    $select.empty().append('<option value="">-- Pilih Rak --</option>');

                    rows.forEach(function(row) {
                        let label = `${row.nm_rak ?? '-'} - ${row.no_rak ?? '-'}` +
                            (row.desc ? ` (${row.desc})` : '') +
                            ` (Stok: ${row.stok})`;
                        $('<option>', {
                                value: row.id_rak,
                                text: label
                            })
                            .data('stok', row.stok)
                            .appendTo($select);
                    });

                    $select.prop('disabled', false).val('').trigger('change');
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Gagal memuat daftar rak untuk sparepart ini.',
                    });
                }
            });
        }

        $('#cbosparepart').on('change', function() {
            loadRakByItem($(this).val());
        });

        // Daftar sparepart yang sudah ditambahkan ke list "mau dikeluarkan" pada sesi modal New saat ini
        let pengeluaranSparepartList = [];

        // Qty yang sudah masuk list (belum disimpan) untuk kombinasi rak+item yang sama, supaya tidak bisa
        // nambah lagi melebihi stok meskipun stok di dropdown belum di-refresh dari server
        function getQtyPendingForKey(idRak, idItem) {
            return pengeluaranSparepartList
                .filter(r => String(r.id_rak) === String(idRak) && String(r.id_item) === String(idItem))
                .reduce((sum, r) => sum + r.qty, 0);
        }

        // Tampilkan sisa stok di rak yang dipilih (dikurangi yang sudah masuk list) & batasi qty maksimal yang boleh diinput
        $('#cborak').on('change', function() {
            let stok = parseInt($(this).find(':selected').data('stok')) || 0;
            let idRak = $(this).val();
            let sisa = idRak ? (stok - getQtyPendingForKey(idRak, $('#cbosparepart').val())) : null;

            $('#stokSparepartLabel').text(sisa ?? '-');
            $('#txtqty').attr('max', sisa ?? '');
        });

        // Unit mesin (pembelian) yang masih aktif/belum di-replace, dropdown dirender 2 baris seperti di halaman Pengeluaran Mesin
        function formatUnitMesinOption(state) {
            if (!state.id) return state.text;

            let $option = $(state.element);

            let $top = $('<div>', {
                class: 'd-flex justify-content-between'
            });
            $top.append($('<span>', {
                class: 'fw-bold',
                text: $option.data('serial') || '-'
            }));
            $top.append($('<span>', {
                class: 'badge bg-light text-dark ms-2',
                text: 'BPB: ' + ($option.data('bpb') || '-')
            }));

            let bottomText = [
                $option.data('mesin') || '-',
                $option.data('jmt') || '-'
            ].join(' • ');

            let $bottom = $('<div>', {
                class: 'small text-dark fw-semibold select2-option-truncate',
                title: bottomText,
                text: bottomText
            });

            return $('<div>', {
                class: 'py-1'
            }).append($top, $bottom);
        }

        function formatUnitMesinSelection(state) {
            if (!state.id) return state.text;

            let $option = $(state.element);
            return ($option.data('serial') || '-') + ' — ' + ($option.data('mesin') || '-');
        }

        function loadUnitMesinSelect() {
            $.ajax({
                type: 'GET',
                url: '{{ route('asset_mesin_spareparts_pengeluaran_unit_mesin_select') }}',
                success: function(rows) {
                    let $select = $('#cbomesin').empty().append(
                        '<option value="">-- Pilih Unit Mesin --</option>');

                    rows.forEach(function(row) {
                        let jmt = [row.nm_jenis, row.nm_merk, row.tipe].filter(Boolean).join(' ');
                        let searchText = [row.serial_number, row.itemdesc, jmt, row.bpbno_int].filter(
                                Boolean)
                            .join(' ');

                        $('<option>', {
                                value: row.id,
                                text: searchText
                            })
                            .data('serial', row.serial_number || '-')
                            .data('mesin', row.itemdesc || '-')
                            .data('jmt', jmt || '-')
                            .data('bpb', row.bpbno_int || '-')
                            .appendTo($select);
                    });

                    $select.val('').trigger('change');
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Gagal memuat daftar unit mesin.',
                    });
                }
            });
        }

        $('#cbomesin').select2({
            theme: 'bootstrap4',
            width: 'resolve',
            dropdownParent: $('#NewMesinModal'),
            templateResult: formatUnitMesinOption,
            templateSelection: formatUnitMesinSelection
        });

        // Daftar mekanik (employee aktif dari HRIS), dimuat ulang setiap modal New dibuka
        function loadMekanikSelect() {
            $.ajax({
                type: 'GET',
                url: '{{ route('asset_mesin_spareparts_pengeluaran_mekanik_select') }}',
                success: function(rows) {
                    let $select = $('#cbomekanik').empty().append(
                        '<option value="">-- Pilih Mekanik --</option>');

                    rows.forEach(function(row) {
                        $('<option>', {
                            value: row.enroll_id,
                            text: row.employee_name
                        }).appendTo($select);
                    });

                    $select.val('').trigger('change');
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Gagal memuat daftar mekanik.',
                    });
                }
            });
        }

        // Render ulang tabel daftar sparepart yang mau dikeluarkan berdasarkan isi pengeluaranSparepartList saat ini
        function renderPengeluaranSparepartTable() {
            let $body = $('#pengeluaranSparepartTableBody').empty();
            $('#pengeluaranSparepartCounter').text(pengeluaranSparepartList.length);

            if (!pengeluaranSparepartList.length) {
                $body.append(
                    '<tr id="pengeluaranSparepartEmptyRow"><td colspan="7" class="text-center text-muted">Belum ada sparepart yang ditambahkan</td></tr>'
                );
                return;
            }

            pengeluaranSparepartList.forEach(function(row, i) {
                let $tr = $('<tr>').data('index', i);

                $tr.append($('<td>', {
                    class: 'text-center',
                    text: i + 1
                }));
                $tr.append($('<td>', {
                    text: row.rak_label
                }));
                $tr.append($('<td>', {
                    class: 'td-truncate',
                    title: row.item_label,
                    text: row.item_label
                }));
                $tr.append($('<td>', {
                    class: 'td-truncate',
                    title: row.mesin_label,
                    text: row.mesin_label
                }));
                $tr.append($('<td>', {
                    text: row.mekanik_label
                }));
                $tr.append($('<td>', {
                    class: 'text-center',
                    text: row.qty
                }));
                $tr.append(
                    $('<td>', {
                        class: 'text-center'
                    }).append($('<button>', {
                        type: 'button',
                        class: 'btn btn-sm btn-outline-danger btn-remove-pengeluaran-sparepart',
                        html: '<i class="fas fa-trash"></i>'
                    }))
                );

                $body.append($tr);
            });
        }

        // Tombol Tambah: validasi qty terhadap sisa stok (dikurangi yang sudah masuk list), lalu masukkan ke list
        $('#btnTambahPengeluaranSparepart').on('click', function() {
            let $itemOption = $('#cbosparepart').find(':selected');
            let idItem = $('#cbosparepart').val();
            let $rakOption = $('#cborak').find(':selected');
            let idRak = $('#cborak').val();
            let idPenerimaanMesin = $('#cbomesin').val();
            let enrollIdMekanik = $('#cbomekanik').val();
            let qty = parseInt($('#txtqty').val());

            if (!idItem || !idRak || !idPenerimaanMesin || !enrollIdMekanik || !qty || qty <= 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Form Belum Lengkap',
                    text: 'Pilih sparepart, rak, unit mesin, mekanik & isi qty (lebih dari 0) sebelum menambah.',
                });
                return;
            }

            let stok = parseInt($rakOption.data('stok')) || 0;
            let sisaBisaDitambah = stok - getQtyPendingForKey(idRak, idItem);

            if (qty > sisaBisaDitambah) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Qty Melebihi Stok',
                    text: `Sisa stok sparepart ini di rak yang dipilih masih bisa ditambahkan hanya ${sisaBisaDitambah}.`,
                });
                return;
            }

            pengeluaranSparepartList.push({
                id_item: idItem,
                item_label: ($itemOption.text() || '').replace(/\s*\(Stok Total:.*\)$/, ''),
                id_rak: idRak,
                rak_label: ($rakOption.text() || '').replace(/\s*\(Stok:.*\)$/, ''),
                id_penerimaan_mesin: idPenerimaanMesin,
                mesin_label: formatUnitMesinSelection({
                    id: idPenerimaanMesin,
                    element: $('#cbomesin').find(':selected')[0]
                }),
                enroll_id_mekanik: enrollIdMekanik,
                mekanik_label: $('#cbomekanik').find(':selected').text(),
                qty: qty
            });

            renderPengeluaranSparepartTable();

            // Qty & pilihan rak direset, sparepart/unit mesin/mekanik dibiarkan supaya gampang nambah baris berikutnya
            $('#txtqty').val('');
            $('#cborak').trigger('change'); // refresh label sisa stok (sudah dikurangi qty yang baru ditambahkan)
        });

        // Tombol hapus per baris di list
        $(document).on('click', '.btn-remove-pengeluaran-sparepart', function() {
            let index = $(this).closest('tr').data('index');
            pengeluaranSparepartList.splice(index, 1);
            renderPengeluaranSparepartTable();
            $('#cborak').trigger('change'); // refresh label sisa stok (slot yang dihapus kembali tersedia)
        });

        // Tombol Simpan: kirim semua baris yang sudah dikumpulkan sekaligus ke server
        function save_pengeluaran_spareparts_mesin() {
            if (!pengeluaranSparepartList.length) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Belum Ada Sparepart',
                    text: 'Tambahkan minimal 1 sparepart sebelum menyimpan.',
                });
                return;
            }

            Swal.fire({
                icon: 'question',
                title: 'Simpan Pengeluaran Sparepart?',
                text: `Akan mengeluarkan ${pengeluaranSparepartList.length} baris sparepart.`,
                showCancelButton: true,
                confirmButtonText: 'Ya',
                cancelButtonText: 'Tidak'
            }).then((result) => {
                if (!result.isConfirmed) return;

                let $btn = $('#btnSimpanPengeluaranSparepart').prop('disabled', true);

                $.ajax({
                    type: 'POST',
                    url: '{{ route('store_pengeluaran_spareparts_mesin') }}',
                    data: {
                        _token: '{{ csrf_token() }}',
                        items: pengeluaranSparepartList.map(r => ({
                            id_rak: r.id_rak,
                            id_item: r.id_item,
                            id_penerimaan_mesin: r.id_penerimaan_mesin,
                            enroll_id_mekanik: r.enroll_id_mekanik,
                            qty: r.qty
                        }))
                    },
                    success: function(response) {
                        $('#NewMesinModal').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil Disimpan',
                            text: response.message,
                            timer: 1800,
                            showConfirmButton: false
                        }).then(() => {
                            dataTableReload();
                        });
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal Menyimpan',
                            text: xhr.responseJSON?.message ||
                                'Terjadi kesalahan saat menyimpan data.',
                        });
                    },
                    complete: function() {
                        $btn.prop('disabled', false);
                    }
                });
            });
        }

        // Reset form, list & muat ulang semua dropdown setiap kali modal New dibuka
        $('#NewMesinModal').on('show.bs.modal', function() {
            pengeluaranSparepartList = [];
            renderPengeluaranSparepartTable();

            $('#cbosparepart').val('').trigger('change');
            $('#cborak').empty().append('<option value="">-- Pilih Sparepart Dahulu --</option>')
                .prop('disabled', true).trigger('change');
            $('#stokSparepartLabel').text('-');
            $('#txtqty').val('').removeAttr('max');

            loadSparepartSelect();
            loadUnitMesinSelect();
            loadMekanikSelect();
        });
    </script>
@endsection
