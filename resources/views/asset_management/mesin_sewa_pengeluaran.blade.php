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
            height: 160px;
            width: 160px;
            object-fit: cover;
            border-radius: 6px;
            border: 1px solid #ced4da;
            cursor: pointer;
        }

        .unit-preview-zoom-img {
            max-width: 90vw;
            max-height: 80vh;
            object-fit: contain;
            cursor: zoom-in;
            transition: transform 0.1s ease-out;
        }

        .unit-foto-btn {
            height: 32px;
            width: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0;
        }

        .qr-code-img {
            width: 50px;
            height: 50px;
            cursor: pointer;
        }
    </style>
@endsection

@section('content')
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-minus"></i> Pengajuan Cut Off Mesin Sewa</h5>
        </div>
        <div class="card-body">
            <div class="mb-3 d-flex justify-content-between align-items-center">
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
                <button type="button" class="btn btn-success btn-sm" onclick="export_excel_pengeluaran_mesin_sewa();">
                    <i class="fas fa-file-excel"></i> Export
                </button>
            </div>
            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-hover align-middle text-nowrap w-100">
                    <thead class="bg-sb">
                        <tr>
                            <th scope="col" class="text-center align-middle">Tgl Keluar</th>
                            <th scope="col" class="text-center align-middle">Serial Number</th>
                            <th scope="col" class="text-center align-middle">Nama Mesin</th>
                            <th scope="col" class="text-center align-middle">Jenis</th>
                            <th scope="col" class="text-center align-middle">Merk</th>
                            <th scope="col" class="text-center align-middle">Tipe</th>
                            <th scope="col" class="text-center align-middle">BPB</th>
                            <th scope="col" class="text-center align-middle">Supplier</th>
                            <th scope="col" class="text-center align-middle">Tgl Akhir Kontrak</th>
                            <th scope="col" class="text-center align-middle">Dibuat Oleh</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal New Pengeluaran Mesin -->
    <div class="modal fade" id="NewMesinModal" tabindex="-1" aria-labelledby="NewMesinModalLabel" aria-hidden="true"
        data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-xl" style="max-width: 95vw;">
            <div class="modal-content">
                <div class="modal-header bg-sb text-white">
                    <h5 class="modal-title" id="NewMesinModalLabel">Pengajuan Cut Off Mesin Sewa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3 align-items-center">
                        <label for="cbomesin" class="col-md-3 col-form-label"><small><b>Mesin :</b></small></label>
                        <div class="col-md-7">
                            <select id="cbomesin" name="cbomesin"
                                class="form-control form-control-sm select2bs4 border-primary" style="width: 100%;">
                                <option value="">-- Pilih Mesin --</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="button" id="btnAddMesinKeluar" class="btn btn-primary btn-sm w-100">
                                <i class="fas fa-plus"></i> Add
                            </button>
                        </div>
                    </div>
                    <div class="mb-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="input-group input-group-sm" style="max-width: 280px;">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" id="txtSearchMesinKeluar" class="form-control"
                                placeholder="Cari mesin yang dipilih...">
                        </div>
                        <span class="badge bg-primary">Total Unit: <span id="mesinKeluarCounter">0</span></span>
                    </div>
                    <div class="table-responsive">
                        <table id="mesinKeluarTable"
                            class="table table-bordered table-hover table-sm align-middle text-nowrap w-100 mb-0">
                            <thead class="bg-sb">
                                <tr>
                                    <th scope="col" class="text-center" style="width: 40px;">No</th>
                                    <th scope="col">Serial Number</th>
                                    <th scope="col">Nama Mesin</th>
                                    <th scope="col">Jenis / Merk / Tipe</th>
                                    <th scope="col">BPB</th>
                                    <th scope="col">Tgl Akhir Kontrak</th>
                                    <th scope="col" class="text-center" style="width: 60px;">Act</th>
                                </tr>
                            </thead>
                            <tbody id="mesinKeluarTableBody">
                                <tr id="mesinKeluarEmptyRow">
                                    <td colspan="7" class="text-center text-muted">Belum ada mesin yang dipilih</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" id="btnSimpanMesinKeluar" class="btn btn-success btn-sm">
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
        });

        // dropdownParent diarahkan ke modal terdekat agar pencarian tetap bisa diketik
        // (modal Bootstrap 5 menahan focus, sehingga dropdown yang nempel di <body> jadi tidak bisa diketik)
        $('.select2bs4:not(#cbomesin)').each(function() {
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

        // Ubah format tanggal dari 'YYYY-MM-DD' (format DB) jadi 'DD-MM-YYYY' untuk ditampilkan
        function formatTglIndo(dateStr) {
            if (!dateStr) return '-';
            let parts = dateStr.split('-');
            if (parts.length !== 3) return dateStr;
            return `${parts[2]}-${parts[1]}-${parts[0]}`;
        }

        // Tampilan tiap opsi dropdown Mesin dirender 2 baris (data diambil dari atribut data-* opsi,
        // bukan dari teks polos) supaya lebih rapi & enak dibaca dibanding satu baris panjang dipisah " | "
        function formatMesinOption(state) {
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
                text: 'Akhir Kontrak: ' + ($option.data('akhir') || '-')
            }));

            let $bottom = $('<div>', {
                class: 'small text-dark fw-semibold',
                text: [
                    $option.data('mesin') || '-',
                    $option.data('jmt') || '-',
                    'BPB: ' + ($option.data('bpb') || '-')
                ].join(' • ')
            });

            return $('<div>', {
                class: 'py-1'
            }).append($top, $bottom);
        }

        // Setelah dipilih, kotak select2 cukup tampilkan Serial Number & Nama Mesin saja (ringkas)
        function formatMesinSelection(state) {
            if (!state.id) return state.text;

            let $option = $(state.element);
            return ($option.data('serial') || '-') + ' — ' + ($option.data('mesin') || '-');
        }

        $('#cbomesin').select2({
            theme: 'bootstrap4',
            width: 'resolve',
            dropdownParent: $('#NewMesinModal'),
            templateResult: formatMesinOption,
            templateSelection: formatMesinSelection
        });

        // Default filter tanggal awal & akhir ke tanggal hari ini
        let todayStr = new Date().toISOString().slice(0, 10);
        $('#txttgl_awal').val(todayStr);
        $('#txttgl_akhir').val(todayStr);

        function dataTableReload() {
            datatable.ajax.reload();
        }

        // Export Excel mengikuti filter tanggal yang sedang aktif di tabel utama
        function export_excel_pengeluaran_mesin_sewa() {
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
                url: '{{ route('export_excel_pengeluaran_mesin_sewa') }}',
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
                    link.download = 'Laporan Pengeluaran Mesin Sewa ' + tgl_awal + ' sd ' + tgl_akhir + '.xlsx';
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
                url: '{{ route('asset_mesin_sewa_pengeluaran_list') }}',
                data: function(d) {
                    d.tgl_awal = $('#txttgl_awal').val();
                    d.tgl_akhir = $('#txttgl_akhir').val();
                }
            },
            columns: [{
                    data: 'tgl_keluar_fix'
                }, // Tgl Keluar
                {
                    data: 'serial_number',
                    render: function(data) {
                        return data ?? '-';
                    }
                }, // Serial Number
                {
                    data: 'itemdesc',
                    className: 'td-truncate',
                    render: function(data) {
                        return `<span title="${data ?? ''}">${data ?? '-'}</span>`;
                    }
                }, // Nama Mesin
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
                    data: 'tipe',
                    render: function(data) {
                        return data ?? '-';
                    }
                }, // Tipe
                {
                    data: 'bpbno_int',
                    render: function(data) {
                        return data ?? '-';
                    }
                }, // BPB
                {
                    data: 'supplier',
                    render: function(data) {
                        return data ?? '-';
                    }
                }, // Supplier
                {
                    data: 'tgl_akhir_kontrak_fix',
                    render: function(data) {
                        return data ?? '-';
                    }
                }, // Tgl Akhir Kontrak
                {
                    data: 'created_by',
                    render: function(data) {
                        return data ?? '-';
                    }
                }, // Dibuat Oleh
            ],
        });

        // Map id unit -> data mesin (hasil load dropdown), dipakai saat tombol Add diklik supaya tidak perlu request ulang
        let activeMesinMap = {};

        // Mesin yang sudah ditambahkan ke list "mau dikeluarkan" pada sesi modal New saat ini
        let mesinKeluarList = [];

        // Daftar mesin sewa ACTIVE & kontraknya belum berakhir, dimuat ulang setiap modal New dibuka
        // (label diawali Serial Number supaya pencarian select2 utamanya berdasarkan Serial Number)
        function loadActiveMesinList() {
            activeMesinMap = {};

            $.ajax({
                type: 'GET',
                url: '{{ route('asset_mesin_sewa_pengeluaran_unit_list') }}',
                success: function(rows) {
                    rows.forEach(function(row) {
                        activeMesinMap[row.id] = row;
                    });

                    renderMesinDropdown();
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Gagal memuat daftar mesin.',
                    });
                }
            });
        }

        // Bangun ulang opsi dropdown Mesin dari activeMesinMap, mesin yang sudah ada di mesinKeluarList disembunyikan
        // supaya tidak bisa dipilih/ditambahkan dobel
        function renderMesinDropdown() {
            let $select = $('#cbomesin').empty().append('<option value="">-- Pilih Mesin --</option>');
            let pickedIds = mesinKeluarList.map(row => String(row.id));

            Object.values(activeMesinMap).forEach(function(row) {
                if (pickedIds.includes(String(row.id))) return;

                let jmt = [row.nm_jenis, row.nm_merk, row.tipe].filter(Boolean).join(' ');

                // text dipakai select2 untuk pencarian (tetap memuat semua kata kunci termasuk Serial Number),
                // sedangkan tampilan dropdown-nya dirender rapi lewat formatMesinOption (lihat data-* di bawah)
                let searchText = [row.serial_number, row.itemdesc, jmt, row.bpbno_int, row.tgl_akhir_kontrak]
                    .filter(Boolean).join(' ');

                $('<option>', {
                        value: row.id,
                        text: searchText
                    })
                    .data('serial', row.serial_number || '-')
                    .data('mesin', row.itemdesc || '-')
                    .data('jmt', jmt || '-')
                    .data('bpb', row.bpbno_int || '-')
                    .data('akhir', formatTglIndo(row.tgl_akhir_kontrak))
                    .appendTo($select);
            });

            $select.val('').trigger('change');
        }

        // Filter baris tabel mesin yang sudah dipilih berdasarkan teks yang diketik di kotak pencarian
        function filterMesinKeluarTable() {
            let keyword = $('#txtSearchMesinKeluar').val().trim().toLowerCase();

            $('#mesinKeluarTableBody tr').each(function() {
                if (!$(this).data('unit-id')) return; // baris status kosong, tidak ikut difilter

                let text = $(this).text().toLowerCase();
                $(this).toggle(text.indexOf(keyword) !== -1);
            });
        }

        $(document).on('input', '#txtSearchMesinKeluar', filterMesinKeluarTable);

        // Render ulang tabel mesin yang mau dikeluarkan berdasarkan isi mesinKeluarList saat ini
        function renderMesinKeluarTable() {
            let $body = $('#mesinKeluarTableBody').empty();
            $('#mesinKeluarCounter').text(mesinKeluarList.length);

            if (!mesinKeluarList.length) {
                $body.append(
                    '<tr id="mesinKeluarEmptyRow"><td colspan="7" class="text-center text-muted">Belum ada mesin yang dipilih</td></tr>'
                );
                return;
            }

            mesinKeluarList.forEach(function(row, i) {
                let $tr = $('<tr>').data('unit-id', row.id);

                $tr.append($('<td>', {
                    class: 'text-center',
                    text: i + 1
                }));
                $tr.append($('<td>', {
                    text: row.serial_number || '-'
                }));
                $tr.append($('<td>', {
                    text: row.itemdesc || '-'
                }));
                $tr.append($('<td>', {
                    text: [row.nm_jenis, row.nm_merk, row.tipe].filter(Boolean).join(' ') || '-'
                }));
                $tr.append($('<td>', {
                    text: row.bpbno_int || '-'
                }));
                $tr.append($('<td>', {
                    text: formatTglIndo(row.tgl_akhir_kontrak)
                }));
                $tr.append(
                    $('<td>', {
                        class: 'text-center'
                    }).append($('<button>', {
                        type: 'button',
                        class: 'btn btn-sm btn-outline-danger btn-remove-mesin-keluar',
                        html: '<i class="fas fa-trash"></i>'
                    }))
                );

                $body.append($tr);
            });

            filterMesinKeluarTable();
        }

        // Tombol Add: ambil mesin yang sedang dipilih di dropdown, masukkan ke list (kalau belum ada)
        $('#btnAddMesinKeluar').on('click', function() {
            let id = $('#cbomesin').val();

            if (!id) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Pilih Mesin Dahulu',
                    text: 'Silakan pilih mesin dari dropdown sebelum klik Add.',
                });
                return;
            }

            mesinKeluarList.push(activeMesinMap[id]);
            renderMesinKeluarTable();
            renderMesinDropdown(); // mesin yang baru ditambahkan disembunyikan dari dropdown
        });

        // Tombol hapus per baris: keluarkan mesin tersebut dari list & munculkan lagi di dropdown
        $(document).on('click', '.btn-remove-mesin-keluar', function() {
            let id = $(this).closest('tr').data('unit-id');
            mesinKeluarList = mesinKeluarList.filter(row => String(row.id) !== String(id));
            renderMesinKeluarTable();
            renderMesinDropdown();
        });

        // Tombol Simpan: kirim id unit yang sudah dikumpulkan ke asset_pengeluaran_mesin_sewa
        $('#btnSimpanMesinKeluar').on('click', function() {
            if (!mesinKeluarList.length) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Belum Ada Mesin',
                    text: 'Tambahkan minimal 1 mesin sebelum menyimpan.',
                });
                return;
            }

            let $btn = $(this).prop('disabled', true);

            $.ajax({
                type: 'POST',
                url: '{{ route('store_pengeluaran_mesin_sewa') }}',
                data: {
                    _token: '{{ csrf_token() }}',
                    id_unit: mesinKeluarList.map(row => row.id)
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
                        location.reload();
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

        // Reset dropdown, list & pencarian setiap kali modal New dibuka
        $('#NewMesinModal').on('show.bs.modal', function() {
            mesinKeluarList = [];
            $('#txtSearchMesinKeluar').val('');
            renderMesinKeluarTable();
            loadActiveMesinList();
        });
    </script>
@endsection
