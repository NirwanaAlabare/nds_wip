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

        .unit-preview-img {
            height: 42px;
            width: 42px;
            object-fit: cover;
            border-radius: 6px;
            border: 1px solid #ced4da;
            cursor: pointer;
        }

        .unit-foto-btn {
            height: 32px;
            width: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0;
        }
    </style>
@endsection

@section('content')
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-plus"></i> Sewa Mesin</h5>
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
            </div>
            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-hover align-middle text-nowrap w-100">
                    <thead class="bg-sb">
                        <tr>
                            <th scope="col" class="text-center align-middle">Tgl Transaksi</th>
                            <th scope="col" class="text-center align-middle">BPB</th>
                            <th scope="col" class="text-center align-middle">Supplier</th>
                            <th scope="col" class="text-center align-middle">Jenis</th>
                            <th scope="col" class="text-center align-middle">Merk</th>
                            <th scope="col" class="text-center align-middle">Nama Mesin</th>
                            <th scope="col" class="text-center align-middle">Tipe</th>
                            <th scope="col" class="text-center align-middle">Total</th>
                            <th scope="col" class="text-center align-middle">Terisi</th>
                            <th scope="col" class="text-center align-middle">Act</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal New Mesin -->
    <div class="modal fade" id="NewMesinModal" tabindex="-1" aria-labelledby="NewMesinModalLabel" aria-hidden="true"
        data-bs-backdrop="static">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-sb text-white">
                    <h5 class="modal-title" id="NewMesinModalLabel">Sewa Mesin</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3 align-items-center">
                        <label for="cbonomor_bpb" class="col-md-3 col-form-label"><small><b>Nomor BPB :</b></small></label>
                        <div class="col-md-9">
                            <select id="cbonomor_bpb" name="cbonomor_bpb"
                                class="form-control form-control-sm select2bs4 border-primary" style="width: 100%;">
                                <option value="">-- Pilih Nomor BPB --</option>
                                @foreach ($bpbList as $row)
                                    <option value="{{ $row->bpbno }}">{{ $row->bpbno_int }} - {{ $row->supplier }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table id="bpbDetailTable"
                                    class="table table-bordered table-hover table-sm align-middle text-nowrap w-100">
                                    <thead class="bg-sb">
                                        <tr>
                                            <th scope="col" class="text-center align-middle">ID Item</th>
                                            <th scope="col" class="text-center align-middle">Nama Barang</th>
                                            <th scope="col" class="text-center align-middle">Qty</th>
                                            <th scope="col" class="text-center align-middle">Unit</th>
                                            <th scope="col" class="text-center align-middle">Act</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Unit Mesin (input Serial Number & Foto per unit) -->
    <div class="modal fade" id="MesinUnitModal" tabindex="-1" aria-labelledby="MesinUnitModalLabel" aria-hidden="true"
        data-bs-backdrop="static">
        <div class="modal-dialog modal-xl" style="max-width: 95vw;">
            <div class="modal-content">
                <div class="modal-header bg-sb text-white">
                    <div>
                        <h5 class="modal-title mb-0" id="MesinUnitModalLabel">Detail Unit Mesin
                            <span id="unitFilledCounter" class="badge bg-light text-dark ms-1"></span>
                        </h5>
                        <small id="MesinUnitModalSubLabel" class="text-white-50"></small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted mb-2"><small><i class="fas fa-circle-info"></i> Ketik Serial Number lalu
                            tekan <kbd>Enter</kbd> untuk langsung menyimpan baris tersebut.</small></p>
                    <div class="mb-2 d-flex gap-2">
                        <input type="text" id="unitSerialSearch" class="form-control form-control-sm"
                            placeholder="Cari Serial Number...">
                        <select id="unitStatusFilter" class="form-select form-select-sm" style="max-width: 180px;">
                            <option value="">Semua Status</option>
                            <option value="completed">Completed</option>
                            <option value="incomplete">Incomplete</option>
                        </select>
                    </div>
                    <div class="table-responsive">
                        <table id="unitTable" class="table table-bordered table-sm align-middle mb-0">
                            <thead class="bg-sb">
                                <tr>
                                    <th scope="col" class="text-center" style="width: 50px;">No</th>
                                    <th scope="col" class="text-center" style="width: 110px;">QR Code</th>
                                    <th scope="col" style="width: 120px;">Serial Number</th>
                                    <th scope="col" class="text-center" style="width: 110px;">Foto</th>
                                    <th scope="col" style="width: 130px;">Jenis</th>
                                    <th scope="col" style="width: 130px;">Merk</th>
                                    <th scope="col" style="width: 110px;">Tipe</th>
                                    <th scope="col" style="width: 130px;">Tanggal Terima</th>
                                    <th scope="col" style="width: 110px;">Masa Kontrak (Hari)</th>
                                    <th scope="col" style="width: 130px;">Tanggal Akhir Kontrak</th>
                                </tr>
                            </thead>
                            <tbody id="unitTableBody"></tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
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
        // Select2 Autofocus
        $(document).on('select2:open', () => {
            document.querySelector('.select2-search__field').focus();
        });

        // dropdownParent diarahkan ke modal terdekat agar pencarian tetap bisa diketik
        // (modal Bootstrap 5 menahan focus, sehingga dropdown yang nempel di <body> jadi tidak bisa diketik)
        $('.select2bs4').each(function() {
            let $modal = $(this).closest('.modal');

            $(this).select2({
                theme: 'bootstrap4',
                width: 'resolve',
                dropdownParent: $modal.length ? $modal : $(document.body)
            });
        });
        $('.select2-container--bootstrap4 .select2-selection--single').css({
            'height': '30px',
            'font-size': '12px',
            'line-height': '30px'
        });

        // Default filter tanggal awal & akhir ke tanggal hari ini
        let todayStr = new Date().toISOString().slice(0, 10);
        $('#txttgl_awal').val(todayStr);
        $('#txttgl_akhir').val(todayStr);

        function dataTableReload() {
            datatable.ajax.reload();
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
                url: '{{ route('asset_mesin_sewa_list') }}',
                data: function(d) {
                    d.tgl_awal = $('#txttgl_awal').val();
                    d.tgl_akhir = $('#txttgl_akhir').val();
                }
            },
            columns: [{
                    data: 'tgl_trans'
                }, // Tgl Transaksi
                {
                    data: 'bpbno_int'
                }, // BPB
                {
                    data: 'supplier'
                }, // Supplier
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
                    data: 'itemdesc',
                    className: 'td-truncate',
                    render: function(data) {
                        return `<span title="${data ?? ''}">${data ?? '-'}</span>`;
                    }
                }, // Nama Mesin
                {
                    data: 'tipe',
                    render: function(data) {
                        return data ?? '-';
                    }
                }, // Tipe
                {
                    data: 'tot_qty'
                }, // Total
                {
                    data: null,
                    className: 'text-center',
                    render: function(data, type, row) {
                        let total = parseInt(row.tot_qty) || 0;
                        let complete = parseInt(row.tot_complete) || 0;
                        return `<span class="badge bg-success" title="Komplit: Serial Number & Foto sudah terisi">Complete: ${complete}/${total}</span>`;
                    },
                    orderable: false,
                    searchable: false
                }, // Terisi
                {
                    data: null,
                    className: 'text-center',
                    render: function() {
                        return `
                    <button type="button" class="btn btn-sm btn-primary btn-detail-unit">
                        <i class="fas fa-pen"></i> Detail
                    </button>`;
                    },
                    orderable: false,
                    searchable: false
                }, // Act
            ],
        });

        // Klik tombol Detail pada kolom Act: ambil data baris lewat DataTables API (aman dari karakter spesial)
        $('#datatable tbody').on('click', '.btn-detail-unit', function() {
            let row = datatable.row($(this).closest('tr')).data();
            openMesinUnitModal(row);
        });

        // Master daftar Kode QR (asset_master_mesin_sewa_qr) untuk dropdown pilihan di kolom QR Code
        const qrList = @json($qrList);

        let currentUnitContext = null;

        // Buka modal Unit Mesin: 1 tab per unit (sejumlah tot_qty), input Serial Number & Foto
        function openMesinUnitModal(row) {
            $('#MesinUnitModalLabel').text(row.itemdesc ?? '-');
            $('#MesinUnitModalSubLabel').html(
                `${row.supplier ?? '-'}</b> &nbsp;|&nbsp; BPB: <b>${row.bpbno_int ?? '-'}</b>`
            );
            $('#unitSerialSearch').val('');
            $('#unitStatusFilter').val('');

            currentUnitContext = {
                id_bpb: row.id_bpb,
                id_item: row.id_item
            };

            loadUnitTable(true);
        }

        // Ambil ulang data unit dari server & render tabel. showModal=true dipakai saat pertama kali dibuka,
        // false dipakai untuk refresh setelah simpan (modal sudah terbuka).
        function loadUnitTable(showModal) {
            if ($.fn.DataTable.isDataTable('#unitTable')) {
                $('#unitTable').DataTable().destroy();
            }

            let $body = $('#unitTableBody').empty();

            $.ajax({
                type: 'GET',
                url: '{{ route('asset_mesin_sewa_unit') }}',
                data: currentUnitContext,
                success: function(units) {
                    units.forEach(function(unit, i) {
                        let imgSrc = unit.foto ?
                            `/nds_wip/public/storage/gambar_penerimaan_mesin_sewa/${unit.foto}` : '';

                        let qrOptions = qrList.map(function(q) {
                            let selected = String(q.kode_qr) === String(unit.kode_qr ?? '') ?
                                'selected' : '';
                            return `<option value="${q.kode_qr}" ${selected}>${q.kode_qr}</option>`;
                        }).join('');

                        $body.append(`
                    <tr>
                        <td class="text-center align-middle">${i + 1}</td>
                        <td class="text-center align-middle">
                            <select class="form-control form-control-sm unit-qr-select" data-unit-id="${unit.id}"
                                style="width: 140px; margin: 0 auto;">
                                <option value="">-- Pilih --</option>
                                ${qrOptions}
                            </select>
                        </td>
                        <td class="align-middle">
                            <input type="text" class="form-control form-control-sm unit-serial-input"
                                data-unit-id="${unit.id}" value="${unit.serial_number ?? ''}"
                                placeholder="Masukkan Serial Number">
                        </td>
                        <td class="text-center align-middle">
                            <div class="d-flex align-items-center justify-content-center gap-2">
                                <input type="file" class="d-none unit-file-input" data-unit-id="${unit.id}"
                                    accept="image/png, image/jpeg, image/jpg">
                                <button type="button" class="btn btn-sm btn-outline-primary unit-foto-btn"
                                    title="${imgSrc ? 'Ganti Foto' : 'Upload Foto'}">
                                    <i class="fas fa-camera"></i>
                                </button>
                                <img class="unit-preview-img" src="${imgSrc}"
                                    style="display:${imgSrc ? 'inline-block' : 'none'};">
                            </div>
                        </td>
                        <td class="align-middle">
                            <input type="text" class="form-control form-control-sm unit-jenis-input"
                                data-unit-id="${unit.id}" value="${unit.nm_jenis ?? ''}" placeholder="Jenis">
                        </td>
                        <td class="align-middle">
                            <input type="text" class="form-control form-control-sm unit-merk-input"
                                data-unit-id="${unit.id}" value="${unit.nm_merk ?? ''}" placeholder="Merk">
                        </td>
                        <td class="align-middle">
                            <input type="text" class="form-control form-control-sm unit-tipe-input"
                                data-unit-id="${unit.id}" value="${unit.tipe ?? ''}" placeholder="Tipe">
                        </td>
                        <td class="align-middle">
                            <input type="date" class="form-control form-control-sm unit-tgl-terima-input"
                                data-unit-id="${unit.id}" value="${unit.tgl_awal_kontrak ?? ''}">
                        </td>
                        <td class="align-middle">
                            <input type="number" min="1" class="form-control form-control-sm unit-masa-kontrak-input"
                                data-unit-id="${unit.id}" value="${unit.masa_kontrak ?? ''}" placeholder="Hari"
                                title="${unit.tgl_awal_kontrak ? '' : 'Isi Tanggal Terima dahulu'}"
                                ${unit.tgl_awal_kontrak ? '' : 'disabled'}>
                        </td>
                        <td class="text-center align-middle">
                            <span class="unit-tgl-akhir-kontrak">${unit.tgl_akhir_kontrak ?? '-'}</span>
                        </td>
                    </tr>`);
                    });

                    updateUnitFilledCounter();

                    $('#unitTable').DataTable({
                        dom: 'rt<"d-flex justify-content-between align-items-center"ip>',
                        paging: true,
                        pageLength: 10,
                        lengthChange: false,
                        searching: true,
                        ordering: false,
                        info: true,
                        autoWidth: false
                    });

                    if (showModal) {
                        $('#MesinUnitModal').modal('show');
                    }
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Gagal memuat data unit mesin.',
                    });
                }
            });
        }

        // Hitung & tampilkan jumlah unit yang Serial Number-nya sudah terisi pada header modal
        function updateUnitFilledCounter() {
            let total = $('.unit-serial-input').length;
            let filled = $('.unit-serial-input').filter(function() {
                return $(this).val().trim() !== '';
            }).length;

            $('#unitFilledCounter').text(`${filled}/${total} terisi`);
        }

        // Terapkan ulang filter status Completed/Incomplete saat ada Serial Number/Foto yang baru disimpan
        function redrawUnitTableFilter() {
            if ($('#unitStatusFilter').val() && $.fn.DataTable.isDataTable('#unitTable')) {
                $('#unitTable').DataTable().draw(false);
            }
        }

        // Simpan Serial Number langsung saat tekan Enter, pakai id baris asset_penerimaan_mesin_sewa sebagai patokan update
        $(document).on('keydown', '.unit-serial-input', function(e) {
            if (e.key !== 'Enter') return;
            e.preventDefault();

            let $input = $(this);
            let id = $input.data('unit-id');

            $input.prop('disabled', true).removeClass('is-valid is-invalid');

            let formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append(`units[${id}][id]`, id);
            formData.append(`units[${id}][serial_number]`, $input.val());

            $.ajax({
                type: 'POST',
                url: '{{ route('store_penerimaan_mesin_sewa_unit') }}',
                data: formData,
                contentType: false,
                processData: false,
                success: function() {
                    $input.addClass('is-valid');
                    setTimeout(() => $input.removeClass('is-valid'), 1500);
                    updateUnitFilledCounter();
                    redrawUnitTableFilter();
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    $input.addClass('is-invalid');
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal menyimpan',
                        text: xhr.responseJSON?.message ||
                            'Terjadi kesalahan saat menyimpan Serial Number.',
                    });
                },
                complete: function() {
                    $input.prop('disabled', false).trigger('focus');
                }
            });
        });

        // Sinkronkan badge Terisi pada tabel utama begitu modal unit ditutup
        $('#MesinUnitModal').on('hidden.bs.modal', function() {
            dataTableReload();
        });

        // Filter baris unit berdasarkan Serial Number & status Completed/Incomplete (dibaca dari value input, bukan teks sel)
        $.fn.dataTable.ext.search.push(function(settings, data, dataIndex, rowData, counter) {
            if (settings.sTableId !== 'unitTable') return true;

            let $tr = $(settings.aoData[dataIndex].nTr);
            let keyword = $('#unitSerialSearch').val().trim().toLowerCase();
            let statusFilter = $('#unitStatusFilter').val();

            let serial = $tr.find('.unit-serial-input').val().toLowerCase();
            if (keyword && !serial.includes(keyword)) return false;

            if (statusFilter) {
                let serialFilled = serial.trim() !== '';
                let fotoFilled = !!$tr.find('.unit-preview-img').attr('src');
                let completed = serialFilled && fotoFilled;

                if (statusFilter === 'completed' && !completed) return false;
                if (statusFilter === 'incomplete' && completed) return false;
            }

            return true;
        });

        $(document).on('keyup', '#unitSerialSearch', function() {
            if ($.fn.DataTable.isDataTable('#unitTable')) {
                $('#unitTable').DataTable().draw();
            }
        });

        $(document).on('change', '#unitStatusFilter', function() {
            if ($.fn.DataTable.isDataTable('#unitTable')) {
                $('#unitTable').DataTable().draw();
            }
        });

        // Klik tombol Foto membuka file picker tersembunyi miliknya
        $(document).on('click', '.unit-foto-btn', function() {
            $(this).siblings('.unit-file-input').trigger('click');
        });

        // Auto-save foto begitu dipilih, pakai id baris asset_penerimaan_mesin_sewa sebagai patokan update
        $(document).on('change', '.unit-file-input', function() {
            let file = this.files[0];
            if (!file) return;

            let $input = $(this);
            let $tr = $input.closest('tr');
            let id = $input.data('unit-id');
            let $btn = $tr.find('.unit-foto-btn');

            let reader = new FileReader();
            reader.onload = function(e) {
                $tr.find('.unit-preview-img').attr('src', e.target.result).show();
                redrawUnitTableFilter();
            };
            reader.readAsDataURL(file);

            let formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append(`units[${id}][id]`, id);
            formData.append(`units[${id}][foto]`, file);

            $btn.prop('disabled', true);

            $.ajax({
                type: 'POST',
                url: '{{ route('store_penerimaan_mesin_sewa_unit') }}',
                data: formData,
                contentType: false,
                processData: false,
                success: function() {
                    $btn.attr('title', 'Ganti Foto').removeClass('btn-outline-primary').addClass(
                        'btn-outline-success');
                    setTimeout(() => $btn.removeClass('btn-outline-success').addClass(
                        'btn-outline-primary'), 1500);
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal menyimpan',
                        text: xhr.responseJSON?.message ||
                            'Terjadi kesalahan saat menyimpan foto.',
                    });
                },
                complete: function() {
                    $btn.prop('disabled', false);
                }
            });
        });

        // Klik thumbnail foto untuk melihat versi lebih besar
        $(document).on('click', '.unit-preview-img', function() {
            Swal.fire({
                imageUrl: this.src,
                imageAlt: 'Preview',
                showConfirmButton: false,
                showCloseButton: true,
                background: '#fff'
            });
        });

        // Simpan Kode QR yang dipilih dari master asset_master_mesin_sewa_qr langsung saat dropdown berubah
        $(document).on('change', '.unit-qr-select', function() {
            let $select = $(this);
            let id = $select.data('unit-id');

            $select.prop('disabled', true).removeClass('is-valid is-invalid');

            $.ajax({
                type: 'POST',
                url: '{{ route('store_penerimaan_mesin_sewa_unit') }}',
                data: {
                    _token: '{{ csrf_token() }}',
                    units: {
                        [id]: {
                            id: id,
                            kode_qr: $select.val()
                        }
                    }
                },
                success: function() {
                    $select.addClass('is-valid');
                    setTimeout(() => $select.removeClass('is-valid'), 1500);
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    $select.addClass('is-invalid');
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal menyimpan',
                        text: xhr.responseJSON?.message ||
                            'Terjadi kesalahan saat menyimpan Kode QR.',
                    });
                },
                complete: function() {
                    $select.prop('disabled', false);
                }
            });
        });

        // Simpan field Jenis, Merk & Tipe langsung ke kolom nm_jenis/nm_merk/tipe milik unit yang bersangkutan
        function saveUnitTextField($el, field) {
            let id = $el.data('unit-id');

            $el.prop('disabled', true).removeClass('is-valid is-invalid');

            $.ajax({
                type: 'POST',
                url: '{{ route('store_penerimaan_mesin_sewa_unit') }}',
                data: {
                    _token: '{{ csrf_token() }}',
                    units: {
                        [id]: {
                            id: id,
                            [field]: $el.val()
                        }
                    }
                },
                success: function() {
                    $el.addClass('is-valid');
                    setTimeout(() => $el.removeClass('is-valid'), 1500);
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    $el.addClass('is-invalid');
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal menyimpan',
                        text: xhr.responseJSON?.message ||
                            'Terjadi kesalahan saat menyimpan data.',
                    });
                },
                complete: function() {
                    $el.prop('disabled', false);
                }
            });
        }

        $(document).on('keydown', '.unit-jenis-input, .unit-merk-input, .unit-tipe-input', function(e) {
            if (e.key !== 'Enter') return;
            e.preventDefault();
            $(this).trigger('blur');
        });

        $(document).on('blur', '.unit-jenis-input', function() {
            saveUnitTextField($(this), 'nm_jenis');
        });

        $(document).on('blur', '.unit-merk-input', function() {
            saveUnitTextField($(this), 'nm_merk');
        });

        $(document).on('blur', '.unit-tipe-input', function() {
            saveUnitTextField($(this), 'tipe');
        });

        // Tanggal Akhir Kontrak = Tanggal Terima + Masa Kontrak (hari), dihitung di browser untuk tampilan
        // (nilai final tetap dihitung ulang & disimpan di server agar konsisten)
        function calcTglAkhirKontrak(tglTerima, masaKontrak) {
            if (!tglTerima || !masaKontrak) return '-';
            let d = new Date(tglTerima + 'T00:00:00');
            d.setDate(d.getDate() + parseInt(masaKontrak));
            return d.toISOString().slice(0, 10);
        }

        // Masa Kontrak baru bisa diisi setelah Tanggal Terima terisi
        $(document).on('change', '.unit-tgl-terima-input', function() {
            let $input = $(this);
            let $tr = $input.closest('tr');
            let tglTerima = $input.val();
            let $masaKontrak = $tr.find('.unit-masa-kontrak-input');

            $masaKontrak.prop('disabled', !tglTerima)
                .attr('title', tglTerima ? '' : 'Isi Tanggal Terima dahulu');

            $tr.find('.unit-tgl-akhir-kontrak').text(calcTglAkhirKontrak(tglTerima, $masaKontrak.val()));

            saveUnitTextField($input, 'tgl_awal_kontrak');
        });

        $(document).on('change', '.unit-masa-kontrak-input', function() {
            let $input = $(this);
            let $tr = $input.closest('tr');
            let tglTerima = $tr.find('.unit-tgl-terima-input').val();

            $tr.find('.unit-tgl-akhir-kontrak').text(calcTglAkhirKontrak(tglTerima, $input.val()));

            saveUnitTextField($input, 'masa_kontrak');
        });

        let bpbDetailTable = $("#bpbDetailTable").DataTable({
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
                url: '{{ route('asset_mesin_sewa_bpb_detail') }}',
                data: function(d) {
                    d.bpbno = $('#cbonomor_bpb').val();
                }
            },
            columns: [{
                    data: 'id_item'
                }, // ID Item
                {
                    data: 'itemdesc',
                    className: 'td-truncate',
                    render: function(data) {
                        return `<span title="${data ?? ''}">${data ?? '-'}</span>`;
                    }
                }, // Nama Barang
                {
                    data: 'qty'
                }, // Qty
                {
                    data: 'unit'
                }, // Unit
                {
                    data: null,
                    className: 'text-center',
                    render: function(data, type, row) {
                        return `
                    <button type="button" class="btn btn-sm btn-primary btn-simpan-sewa"
                        onclick='save_penerimaan_mesin_sewa(${row.qty}, "${row.bpbno}", "${row.bpbno_int}", ${row.id_item}, ${row.id})'>
                        <i class="fa fa-check"></i> Simpan
                    </button>`;
                    },
                    orderable: false,
                    searchable: false
                }, // Act
            ],
        });

        // Simpan langsung ke asset_penerimaan_mesin_sewa tanpa perlu pilih Jenis (beda dengan flow Tambah Mesin)
        function save_penerimaan_mesin_sewa(qty, bpbno, bpbnoInt, idItem, idBpb) {
            Swal.fire({
                icon: 'question',
                title: 'Simpan Sewa Mesin?',
                text: 'Akan menambahkan ' + qty + ' unit mesin sewa.',
                showCancelButton: true,
                confirmButtonText: 'Ya',
                cancelButtonText: 'Tidak'
            }).then((result) => {
                if (!result.isConfirmed) {
                    return;
                }

                $.ajax({
                    type: 'POST',
                    url: '{{ route('store_penerimaan_mesin_sewa') }}',
                    data: {
                        _token: '{{ csrf_token() }}',
                        id_item: idItem,
                        id_bpb: idBpb,
                        bpbno: bpbno,
                        bpbno_int: bpbnoInt,
                        qty: qty
                    },
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Mesin Sewa Disimpan',
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            bpbDetailTable.ajax.reload();
                            dataTableReload();
                        });
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON?.message ||
                                'Terjadi kesalahan saat menyimpan.',
                        });
                    }
                });
            });
        }

        // Reload tabel detail BPB saat nomor BPB dipilih
        $('#cbonomor_bpb').on('change', function() {
            bpbDetailTable.ajax.reload();
        });

        // Perbaiki lebar kolom saat modal ditampilkan (DataTables tidak bisa hitung lebar saat modal masih hidden)
        $('#NewMesinModal').on('shown.bs.modal', function() {
            bpbDetailTable.columns.adjust();
        });

        // Reset form setiap kali modal dibuka
        $('#NewMesinModal').on('show.bs.modal', function() {
            $('#cbonomor_bpb').val(null).trigger('change');
        });
    </script>
@endsection
