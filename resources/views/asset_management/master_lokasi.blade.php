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
    </style>
@endsection

@section('content')
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-map-marker-alt"></i> Master Lokasi</h5>
        </div>
        <div class="card-body">
            <div class="row mb-3 align-items-end">
                <div class="col-md-3">
                    <label for="cbomain_lokasi"><small><b>Main Lokasi :</b></small></label>
                    <div class="d-flex gap-1">
                        <select id="cbomain_lokasi" name="cbomain_lokasi"
                            class="form-control form-control-sm select2bs4 border-primary flex-fill" style="width: 100%;">
                            <option value="">-- Pilih Main Lokasi --</option>
                            @foreach ($mainLokasiList as $row)
                                <option value="{{ $row->id }}">{{ $row->main_lokasi }}</option>
                            @endforeach
                        </select>
                        <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal"
                            data-bs-target="#CreateMainLokasiModal" title="Tambah Main Lokasi">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-3">
                    <label for="txtsub_lokasi"><small><b>Sub Lokasi :</b></small></label>
                    <input type="text" id="txtsub_lokasi" name="txtsub_lokasi" class="form-control form-control-sm"
                        autocomplete="off" list="subLokasiSuggestions" style="text-transform: uppercase;"
                        oninput="this.value = this.value.toUpperCase();">
                </div>
                <div class="col-md-3">
                    <label for="txtdivisi"><small><b>Divisi :</b></small></label>
                    <input type="text" id="txtdivisi" name="txtdivisi" class="form-control form-control-sm"
                        autocomplete="off" list="divisiSuggestions" style="text-transform: uppercase;"
                        oninput="this.value = this.value.toUpperCase();">
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-success btn-sm" id="saveLokasiButton"
                        onclick="save_lokasi_det();">
                        <i class="fas fa-save"></i> Save
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <datalist id="subLokasiSuggestions">
                    @foreach ($subLokasiList as $row)
                        <option value="{{ $row->sub_lokasi }}">
                    @endforeach
                </datalist>
                <datalist id="divisiSuggestions">
                    @foreach ($divisiList as $row)
                        <option value="{{ $row->divisi }}">
                    @endforeach
                </datalist>

                <table id="datatable" class="table table-bordered table-hover align-middle text-nowrap w-100">
                    <thead class="bg-sb">
                        <tr>
                            <th scope="col" class="text-center align-middle">Main Lokasi</th>
                            <th scope="col" class="text-center align-middle">Sub Lokasi</th>
                            <th scope="col" class="text-center align-middle">Divisi</th>
                            <th scope="col" class="text-center align-middle">Act</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Quick Add / Manage Main Lokasi -->
    <div class="modal fade" id="CreateMainLokasiModal" tabindex="-1" aria-labelledby="CreateMainLokasiModalLabel"
        aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
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
                            onclick="save_main_lokasi();">
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
                                            onclick="edit_main_lokasi({{ $row->id }}, '{{ $row->main_lokasi }}')">
                                            <i class="fa fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger"
                                            onclick="delete_main_lokasi({{ $row->id }})">
                                            <i class="fa fa-trash"></i>
                                        </button>
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
    <div class="modal fade" id="EditLokasiModal" tabindex="-1" aria-labelledby="EditLokasiModalLabel"
        aria-hidden="true" data-bs-backdrop="static">
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
                                class="form-control form-control-sm select2bs4 border-primary" style="width: 100%;">
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
                                style="text-transform: uppercase;"
                                oninput="this.value = this.value.toUpperCase();">
                        </div>
                        <div class="col-md-4">
                            <label for="txted_divisi"><small><b>Divisi :</b></small></label>
                            <input type="text" id="txted_divisi" name="txted_divisi"
                                class="form-control form-control-sm" autocomplete="off" list="divisiSuggestions"
                                style="text-transform: uppercase;"
                                oninput="this.value = this.value.toUpperCase();">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-warning btn-sm" id="editLokasiButton"
                        onclick="update_lokasi_det();">Edit</button>
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
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
    {{-- <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables 2.0/dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables 2.0/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables 2.0/dataTables.fixedColumns.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-rowsgroup/dataTables.rowsGroup.js') }}"></script> --}}
    <script>
        // Select2 Autofocus
        $(document).on('select2:open', () => {
            document.querySelector('.select2-search__field').focus();
        });

        // Initialize Select2 Elements
        $('.select2').select2();

        // Initialize Select2BS4 Elements
        $('.select2bs4').select2({
            theme: 'bootstrap4',
            width: 'resolve' // Ensures it respects the 100% width from inline style or Bootstrap
        });
        // Now set height and font-size on the Select2 container after init
        $('.select2-container--bootstrap4 .select2-selection--single').css({
            'height': '30px', // your desired height
            'font-size': '12px', // your desired font size
            'line-height': '30px' // vertically center text
        });

        function notif() {
            alert("Maaf, Fitur belum tersedia!");
        }

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

        function save_main_lokasi() {
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
                                    onclick="edit_main_lokasi(${response.id}, '${response.main_lokasi}')">
                                    <i class="fa fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="delete_main_lokasi(${response.id})">
                                    <i class="fa fa-trash"></i>
                                </button>
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

        function edit_main_lokasi(id, currentName) {
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

        function delete_main_lokasi(id) {
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

        function save_lokasi_det() {
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
                    dataTableReload();

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

        function editData(id_c) {
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
                },
                error: function(request, status, error) {
                    alert(request.responseText);
                },
            });
        }

        function update_lokasi_det() {
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
                    dataTableReload();

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

        function deleteData(id_c) {
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
                        dataTableReload();
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
    </script>
    <script>
        function dataTableReload() {
            datatable.ajax.reload();
        }

        let datatable = $("#datatable").DataTable({
            ordering: false,
            responsive: true,
            processing: true,
            serverSide: false,
            paging: true,
            searching: true,
            scrollY: true,
            scrollX: true,
            scrollCollapse: false,
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
                        <button class="btn btn-sm btn-warning" onclick="editData(${data})"
                                data-bs-toggle="modal" data-bs-target="#EditLokasiModal">
                            <i class="fa fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="deleteData(${data})">
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>`;
                    },
                    orderable: false,
                    searchable: false
                }
            ],
        });
    </script>
@endsection
