{{--
    Modal + script Master Lokasi. Di-include di section custom-script.

    Opsi:
      asModal               - true: form dibungkus modal #MasterLokasiModal (dipakai halaman lain).
                              false: form dirender sendiri oleh halaman (halaman Master Lokasi).
      autoInitMasterLokasi  - false kalau tabel ada di dalam modal; init ditunda sampai modal dibuka.
      canDeleteMasterLokasi - false untuk menyembunyikan tombol hapus.
--}}
@php
    $asModal = $asModal ?? false;
    $autoInitMasterLokasi = $autoInitMasterLokasi ?? !$asModal;
    $canDeleteMasterLokasi = $canDeleteMasterLokasi ?? true;
@endphp

@if ($asModal)
    <!-- Modal Master Lokasi -->
    <div class="modal fade" id="MasterLokasiModal" tabindex="-1" aria-labelledby="MasterLokasiModalLabel"
        aria-hidden="true" data-bs-backdrop="static" data-bs-focus="false">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-sb text-white">
                    <h5 class="modal-title mb-0" id="MasterLokasiModalLabel">
                        <i class="fas fa-map-marker-alt"></i> Master Lokasi
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @include('asset_management.partials.master_lokasi_form')
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endif

<!-- Modal Quick Add / Manage Main Lokasi -->
<div class="modal fade" id="CreateMainLokasiModal" tabindex="-1" aria-labelledby="CreateMainLokasiModalLabel"
    aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false" data-bs-focus="false">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header bg-sb text-white">
                <h5 class="modal-title" id="CreateMainLokasiModalLabel">Kelola Main Lokasi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <label for="txtnew_main_lokasi"><small><b>Nama Main Lokasi :</b></small></label>
                <div class="d-flex gap-1 mb-3">
                    <input type="text" id="txtnew_main_lokasi" name="txtnew_main_lokasi"
                        class="form-control form-control-sm flex-fill" style="text-transform: uppercase;"
                        oninput="this.value = this.value.toUpperCase();">
                    <button type="button" class="btn btn-success btn-sm" id="saveMainLokasiButton"
                        onclick="saveMainLokasi();">
                        <i class="fas fa-plus"></i> Save
                    </button>
                </div>

                <table class="table table-bordered table-hover align-middle text-nowrap w-100">
                    <thead class="bg-sb">
                        <tr>
                            <th scope="col" class="text-center align-middle">Main Lokasi</th>
                            <th scope="col" class="text-center align-middle">Act</th>
                        </tr>
                    </thead>
                    <tbody id="mainLokasiListBody">
                        @foreach ($mainLokasiList as $row)
                            <tr id="main_lokasi_row_{{ $row->id }}">
                                <td>{{ $row->main_lokasi }}</td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-warning"
                                        onclick="editMainLokasi({{ $row->id }}, '{{ $row->main_lokasi }}')">
                                        <i class="fa fa-edit"></i>
                                    </button>
                                    @if ($canDeleteMasterLokasi)
                                        <button class="btn btn-sm btn-danger"
                                            onclick="deleteMainLokasi({{ $row->id }})">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Edit Lokasi -->
<div class="modal fade" id="EditLokasiModal" tabindex="-1" aria-labelledby="EditLokasiModalLabel" aria-hidden="true"
    data-bs-backdrop="static" data-bs-focus="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-sb text-white">
                <h5 class="modal-title" id="EditLokasiModalLabel">Edit Lokasi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="txted_id" name="txted_id" value="">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="cboed_main_lokasi"><small><b>Main Lokasi :</b></small></label>
                        <select id="cboed_main_lokasi" name="cboed_main_lokasi"
                            class="form-control form-control-sm select2bs4 select2-master-lokasi border-primary"
                            style="width: 100%;">
                            <option value="">-- Pilih Main Lokasi --</option>
                            @foreach ($mainLokasiList as $row)
                                <option value="{{ $row->id }}">{{ $row->main_lokasi }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="txted_sub_lokasi"><small><b>Sub Lokasi :</b></small></label>
                        <input type="text" id="txted_sub_lokasi" name="txted_sub_lokasi"
                            class="form-control form-control-sm" autocomplete="off" list="subLokasiSuggestions"
                            style="text-transform: uppercase;" oninput="this.value = this.value.toUpperCase();">
                    </div>
                    <div class="col-md-4">
                        <label for="txted_divisi"><small><b>Divisi :</b></small></label>
                        <input type="text" id="txted_divisi" name="txted_divisi" class="form-control form-control-sm"
                            autocomplete="off" list="divisiSuggestions" style="text-transform: uppercase;"
                            oninput="this.value = this.value.toUpperCase();">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-warning btn-sm" id="editLokasiButton"
                    onclick="updateMasterLokasiDet();">Edit</button>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<script>
        function addDatalistOption(listId, value) {
            value = (value || '').trim().toUpperCase();

            if (!value) {
                return;
            }

            let exists = $('#' + listId + ' option').filter(function() {
                return this.value.toUpperCase() === value;
            }).length > 0;

            if (!exists) {
                $('<option></option>').val(value).appendTo('#' + listId);
            }
        }

        function saveMainLokasi() {
            let main_lokasi = $('#txtnew_main_lokasi').val();

            if (!main_lokasi) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Nama Main Lokasi wajib diisi',
                });
                return;
            }

            let $btn = $('#saveMainLokasiButton');
            $btn.prop('disabled', true);

            $.ajax({
                type: "POST",
                url: '{{ route('store_main_lokasi') }}',
                data: {
                    _token: '{{ csrf_token() }}',
                    main_lokasi: main_lokasi
                },
                success: function(response) {
                    $('#txtnew_main_lokasi').val('');

                    $('<option></option>').val(response.id).text(response.main_lokasi)
                        .appendTo('#cbomain_lokasi');
                    $('<option></option>').val(response.id).text(response.main_lokasi)
                        .appendTo('#cboed_main_lokasi');

                    $('#mainLokasiListBody').append(`
                        <tr id="main_lokasi_row_${response.id}">
                            <td>${response.main_lokasi}</td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-warning"
                                    onclick="editMainLokasi(${response.id}, '${response.main_lokasi}')">
                                    <i class="fa fa-edit"></i>
                                </button>
                                @if ($canDeleteMasterLokasi)
                                    <button class="btn btn-sm btn-danger" onclick="deleteMainLokasi(${response.id})">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                @endif
                            </td>
                        </tr>
                    `);

                    Swal.fire({
                        icon: 'success',
                        title: 'Main Lokasi Ditambahkan',
                        text: response.message,
                        timer: 1500,
                        showConfirmButton: false
                    });
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.message || 'Terjadi kesalahan saat menyimpan.',
                    });
                },
                complete: function() {
                    $btn.prop('disabled', false);
                }
            });
        }

        function editMainLokasi(id, currentName) {
            Swal.fire({
                title: 'Edit Main Lokasi',
                input: 'text',
                inputValue: currentName,
                inputAttributes: {
                    autocapitalize: 'off'
                },
                showCancelButton: true,
                confirmButtonText: 'Save',
                cancelButtonText: 'Cancel',
                preConfirm: (value) => {
                    if (!value) {
                        Swal.showValidationMessage('Nama Main Lokasi wajib diisi');
                    }
                    return value;
                }
            }).then((result) => {
                if (!result.isConfirmed) {
                    return;
                }

                let main_lokasi = result.value.toUpperCase();

                $.ajax({
                    type: "POST",
                    url: '{{ route('update_main_lokasi') }}',
                    data: {
                        _token: '{{ csrf_token() }}',
                        id: id,
                        main_lokasi: main_lokasi
                    },
                    success: function(response) {
                        $('#main_lokasi_row_' + id + ' td:first').text(main_lokasi);
                        $('#cbomain_lokasi option[value="' + id + '"]').text(main_lokasi);
                        $('#cboed_main_lokasi option[value="' + id + '"]').text(main_lokasi);
                        $('#cbomain_lokasi, #cboed_main_lokasi').trigger('change');

                        Swal.fire({
                            icon: 'success',
                            title: 'Main Lokasi Diupdate',
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false
                        });
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON?.message ||
                                'Terjadi kesalahan saat mengupdate.',
                        });
                    }
                });
            });
        }

        function deleteMainLokasi(id) {
            Swal.fire({
                icon: 'warning',
                title: 'Hapus Main Lokasi?',
                text: 'Data yang sudah dihapus tidak dapat dikembalikan.',
                showCancelButton: true,
                confirmButtonText: 'Hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (!result.isConfirmed) {
                    return;
                }

                $.ajax({
                    type: "POST",
                    url: '{{ route('delete_main_lokasi') }}',
                    data: {
                        _token: '{{ csrf_token() }}',
                        id: id
                    },
                    success: function(response) {
                        $('#main_lokasi_row_' + id).remove();
                        $('#cbomain_lokasi option[value="' + id + '"]').remove();
                        $('#cboed_main_lokasi option[value="' + id + '"]').remove();
                        $('#cbomain_lokasi, #cboed_main_lokasi').trigger('change');

                        Swal.fire({
                            icon: 'success',
                            title: 'Main Lokasi Dihapus',
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false
                        });
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON?.message ||
                                'Terjadi kesalahan saat menghapus.',
                        });
                    }
                });
            });
        }

        function saveMasterLokasiDet() {
            let id_main_lokasi = $('#cbomain_lokasi').val();
            let sub_lokasi = $('#txtsub_lokasi').val();
            let divisi = $('#txtdivisi').val();

            if (!id_main_lokasi || !sub_lokasi || !divisi) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Main Lokasi, Sub Lokasi, dan Divisi wajib diisi',
                });
                return;
            }

            let $btn = $('#saveLokasiButton');
            $btn.prop('disabled', true);

            $.ajax({
                type: "POST",
                url: '{{ route('store_lokasi_det') }}',
                data: {
                    _token: '{{ csrf_token() }}',
                    id_main_lokasi: id_main_lokasi,
                    sub_lokasi: sub_lokasi,
                    divisi: divisi
                },
                success: function(response) {
                    addDatalistOption('subLokasiSuggestions', sub_lokasi);
                    addDatalistOption('divisiSuggestions', divisi);

                    $('#txtsub_lokasi').val('');
                    $('#txtdivisi').val('');
                    $('#cbomain_lokasi').val(null).trigger('change');
                    reloadMasterLokasi();

                    Swal.fire({
                        icon: 'success',
                        title: 'Lokasi Ditambahkan',
                        text: response.message,
                        timer: 1500,
                        showConfirmButton: false
                    });
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.message || 'Terjadi kesalahan saat menyimpan.',
                    });
                },
                complete: function() {
                    $btn.prop('disabled', false);
                }
            });
        }

        function editMasterLokasiDet(id_c) {
            jQuery.ajax({
                url: '{{ route('show_lokasi_det') }}',
                method: 'GET',
                data: {
                    id: id_c
                },
                dataType: 'json',
                success: function(res) {
                    $('#txted_id').val(res.id);
                    $('#cboed_main_lokasi').val(res.id_main_lokasi).trigger('change');
                    $('#txted_sub_lokasi').val(res.sub_lokasi);
                    $('#txted_divisi').val(res.divisi);
                    openMasterLokasiChildModal('EditLokasiModal');
                },
                error: function(request, status, error) {
                    alert(request.responseText);
                },
            });
        }

        function updateMasterLokasiDet() {
            let id = $('#txted_id').val();
            let id_main_lokasi = $('#cboed_main_lokasi').val();
            let sub_lokasi = $('#txted_sub_lokasi').val();
            let divisi = $('#txted_divisi').val();

            if (!id_main_lokasi || !sub_lokasi || !divisi) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Main Lokasi, Sub Lokasi, dan Divisi wajib diisi',
                });
                return;
            }

            let $btn = $('#editLokasiButton');
            $btn.prop('disabled', true);

            $.ajax({
                type: "POST",
                url: '{{ route('update_lokasi_det') }}',
                data: {
                    _token: '{{ csrf_token() }}',
                    id: id,
                    id_main_lokasi: id_main_lokasi,
                    sub_lokasi: sub_lokasi,
                    divisi: divisi
                },
                success: function(response) {
                    addDatalistOption('subLokasiSuggestions', sub_lokasi);
                    addDatalistOption('divisiSuggestions', divisi);

                    $('#EditLokasiModal').modal('hide');
                    reloadMasterLokasi();

                    Swal.fire({
                        icon: 'success',
                        title: 'Lokasi Diupdate',
                        text: response.message,
                        timer: 1500,
                        showConfirmButton: false
                    });
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.message || 'Terjadi kesalahan saat mengupdate.',
                    });
                },
                complete: function() {
                    $btn.prop('disabled', false);
                }
            });
        }

        function deleteMasterLokasiDet(id_c) {
            Swal.fire({
                icon: 'warning',
                title: 'Hapus Lokasi?',
                text: 'Data yang sudah dihapus tidak dapat dikembalikan.',
                showCancelButton: true,
                confirmButtonText: 'Hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (!result.isConfirmed) {
                    return;
                }

                $.ajax({
                    type: "POST",
                    url: '{{ route('delete_lokasi_det') }}',
                    data: {
                        _token: '{{ csrf_token() }}',
                        id: id_c
                    },
                    success: function(response) {
                        reloadMasterLokasi();
                        Swal.fire({
                            icon: 'success',
                            title: 'Lokasi Dihapus',
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false
                        });
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON?.message ||
                                'Terjadi kesalahan saat menghapus.',
                        });
                    }
                });
            });
        }

        let masterLokasiTable = null;

        function initMasterLokasi() {
            if (masterLokasiTable) {
                masterLokasiTable.columns.adjust();
                return masterLokasiTable;
            }

            // dropdownParent wajib diisi kalau select berada di dalam modal,
            // kalau tidak dropdown-nya muncul di belakang modal.
            $('.select2-master-lokasi').each(function() {
                let $el = $(this);
                let $modal = $el.closest('.modal');

                $el.select2({
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

            masterLokasiTable = $('#tblMasterLokasi').DataTable({
                // Cuma 4 kolom, jadi tidak perlu responsive/scrollX yang bikin DataTables
                // menggandakan tabel header-body. deferRender menahan render baris di luar halaman aktif.
                ordering: false,
                processing: true,
                serverSide: false,
                autoWidth: false,
                deferRender: true,
                pageLength: 10,
                lengthMenu: [
                    [10, 25, 50, 100],
                    [10, 25, 50, 100]
                ],
                dom: '<"d-flex justify-content-between align-items-center mb-2"lf>rt<"d-flex justify-content-between align-items-center mt-2"ip>',
                language: {
                    search: '',
                    searchPlaceholder: 'Cari lokasi...',
                    lengthMenu: '_MENU_ baris',
                    info: '_START_-_END_ dari _TOTAL_',
                    infoEmpty: 'Tidak ada data',
                    zeroRecords: 'Lokasi tidak ditemukan',
                    paginate: {
                        previous: '&laquo;',
                        next: '&raquo;'
                    }
                },
                columnDefs: [{
                    targets: -1,
                    width: '{{ $canDeleteMasterLokasi ? '90px' : '50px' }}'
                }],
                ajax: {
                    url: '{{ route('asset_master_lokasi') }}',
                },
                columns: [{
                        data: 'main_lokasi'
                    }, // Main Lokasi
                    {
                        data: 'sub_lokasi'
                    }, // Sub Lokasi
                    {
                        data: 'divisi'
                    }, // Divisi
                    {
                        data: 'id',
                        render: function(data) {
                            return `
                        <div class="text-center">
                            <button class="btn btn-sm btn-warning" onclick="editMasterLokasiDet(${data})">
                                <i class="fa fa-edit"></i>
                            </button>
                            @if ($canDeleteMasterLokasi)
                                <button class="btn btn-sm btn-danger" onclick="deleteMasterLokasiDet(${data})">
                                    <i class="fa fa-trash"></i>
                                </button>
                            @endif
                        </div>`;
                        },
                        orderable: false,
                        searchable: false
                    }
                ],
            });

            return masterLokasiTable;
        }

        function reloadMasterLokasi() {
            if (masterLokasiTable) {
                masterLokasiTable.ajax.reload(null, false);
            }
        }

        // Modal anak (Tambah Main Lokasi / Edit Lokasi) TIDAK ditumpuk di atas modal Master
        // Lokasi: focus trap Bootstrap milik modal induk terus merebut fokus, jadi input di
        // modal atas tidak bisa diketik. Induknya ditutup dulu, lalu dibuka lagi setelah selesai.
        $(document).on('shown.bs.modal', '#CreateMainLokasiModal', function() {
            $('#txtnew_main_lokasi').trigger('focus');
        });

        // Enter di input Main Lokasi langsung menyimpan
        $(document).on('keydown', '#txtnew_main_lokasi', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                saveMainLokasi();
            }
        });

        function openMasterLokasiChildModal(childId) {
            let $child = $('#' + childId);
            let $parent = $('#MasterLokasiModal');

            // Di halaman Master Lokasi tidak ada modal induk, jadi langsung dibuka.
            if (!$parent.length || !$parent.hasClass('show')) {
                $child.modal('show');
                return;
            }

            $parent.one('hidden.bs.modal', function() {
                $child.one('hidden.bs.modal', function() {
                    $parent.modal('show');
                });
                $child.modal('show');
            });

            $parent.modal('hide');
        }

        @if ($asModal)
            // Delegated: modal ini dirender di section custom-script, jadi belum tentu ada
            // saat script halaman pemanggil dijalankan.
            $(document).on('shown.bs.modal', '#MasterLokasiModal', function() {
                initMasterLokasi();
            });
        @endif

        @if ($autoInitMasterLokasi)
            $(function() {
                initMasterLokasi();
            });
        @endif
</script>
