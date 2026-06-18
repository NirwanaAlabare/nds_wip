@extends('layouts.index')

@section('custom-link')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">

    <!-- DataTables CSS -->
    {{-- <link rel="stylesheet" href="{{ asset('plugins/datatables 2.0/jquery.dataTables.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables 2.0/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables 2.0/fixedColumns.bootstrap4.min.css') }}"> --}}
    <!-- jQuery -->
    <script src="{{ asset('plugins/datatables 2.0/jquery-3.3.1.js') }}"></script>


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
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-list-alt"></i> Master Jenis Mesin</h5>
        </div>
        <div class="card-body">
            <div class="row mb-3 align-items-end">
                <div class="col-md-3">
                    <label for="txtjenis"><small><b>Jenis :</b></small></label>
                    <input type="text" id="txtjenis" name="txtjenis" class="form-control form-control-sm"
                        style="text-transform: uppercase;" oninput="this.value = this.value.toUpperCase();">
                </div>
                <div class="col-md-2">
                    <label for="txtmerk"><small><b>Merk :</b></small></label>
                    <input type="text" id="txtmerk" name="txtmerk" class="form-control form-control-sm"
                        style="text-transform: uppercase;" oninput="this.value = this.value.toUpperCase();">
                </div>
                <div class="col-md-3">
                    <label for="txttipe"><small><b>Tipe :</b></small></label>
                    <input type="text" id="txttipe" name="txttipe" class="form-control form-control-sm"
                        style="text-transform: uppercase;" oninput="this.value = this.value.toUpperCase();">
                </div>
                <div class="col-md-3">
                    <label for="cbosupplier"><small><b>Supplier :</b></small></label>
                    <select id="cbosupplier" name="cbosupplier"
                        class="form-control form-control-sm select2bs4 border-primary" style="width: 100%;">
                        <option value="">-- Pilih Supplier --</option>
                        @foreach ($supplierList as $row)
                            <option value="{{ $row->id_supplier }}">{{ $row->Supplier }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-success btn-sm" id="saveJenisMesinButton"
                        onclick="save_jenis_mesin();">
                        <i class="fas fa-save"></i> Save
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-hover align-middle text-nowrap w-100">
                    <thead class="bg-sb">
                        <tr>
                            <th scope="col" class="text-center align-middle">Jenis</th>
                            <th scope="col" class="text-center align-middle">Merk</th>
                            <th scope="col" class="text-center align-middle">Tipe</th>
                            <th scope="col" class="text-center align-middle">Supplier</th>
                            <th scope="col" class="text-center align-middle">Act</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Edit Jenis Mesin -->
    <div class="modal fade" id="EditJenisMesinModal" tabindex="-1" aria-labelledby="EditJenisMesinModalLabel"
        aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-sb text-white">
                    <h5 class="modal-title" id="EditJenisMesinModalLabel">Edit Jenis Mesin</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="txted_id" name="txted_id" value="">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="txted_jenis"><small><b>Jenis :</b></small></label>
                            <input type="text" id="txted_jenis" name="txted_jenis" class="form-control form-control-sm"
                                style="text-transform: uppercase;" oninput="this.value = this.value.toUpperCase();">
                        </div>
                        <div class="col-md-3">
                            <label for="txted_merk"><small><b>Merk :</b></small></label>
                            <input type="text" id="txted_merk" name="txted_merk" class="form-control form-control-sm"
                                style="text-transform: uppercase;" oninput="this.value = this.value.toUpperCase();">
                        </div>
                        <div class="col-md-3">
                            <label for="txted_tipe"><small><b>Tipe :</b></small></label>
                            <input type="text" id="txted_tipe" name="txted_tipe" class="form-control form-control-sm"
                                style="text-transform: uppercase;" oninput="this.value = this.value.toUpperCase();">
                        </div>
                        <div class="col-md-3">
                            <label for="cboed_supplier"><small><b>Supplier :</b></small></label>
                            <select id="cboed_supplier" name="cboed_supplier"
                                class="form-control form-control-sm select2bs4 border-primary" style="width: 100%;">
                                <option value="">-- Pilih Supplier --</option>
                                @foreach ($supplierList as $row)
                                    <option value="{{ $row->id_supplier }}">{{ $row->Supplier }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-warning btn-sm" id="editJenisMesinButton"
                        onclick="update_jenis_mesin();">Edit</button>
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

        function save_jenis_mesin() {
            let jenis = $('#txtjenis').val();
            let merk = $('#txtmerk').val();
            let tipe = $('#txttipe').val();
            let id_supplier = $('#cbosupplier').val();

            if (!jenis || !merk || !tipe || !id_supplier) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Jenis, Merk, Tipe, dan Supplier wajib diisi',
                });
                return;
            }

            let $btn = $('#saveJenisMesinButton');
            $btn.prop('disabled', true);

            $.ajax({
                type: "POST",
                url: '{{ route('store_jenis_mesin') }}',
                data: {
                    _token: '{{ csrf_token() }}',
                    jenis: jenis,
                    merk: merk,
                    tipe: tipe,
                    id_supplier: id_supplier
                },
                success: function(response) {
                    $('#txtjenis').val('');
                    $('#txtmerk').val('');
                    $('#txttipe').val('');
                    $('#cbosupplier').val(null).trigger('change');
                    dataTableReload();

                    Swal.fire({
                        icon: 'success',
                        title: 'Jenis Mesin Ditambahkan',
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
                url: '{{ route('show_jenis_mesin') }}',
                method: 'GET',
                data: {
                    id: id_c
                },
                dataType: 'json',
                success: function(res) {
                    $('#txted_id').val(res.id_jenis);
                    $('#txted_jenis').val(res.jenis);
                    $('#txted_merk').val(res.merk);
                    $('#txted_tipe').val(res.tipe);
                    $('#cboed_supplier').val(res.id_supplier).trigger('change');
                },
                error: function(request, status, error) {
                    alert(request.responseText);
                },
            });
        }

        function update_jenis_mesin() {
            let id = $('#txted_id').val();
            let jenis = $('#txted_jenis').val();
            let merk = $('#txted_merk').val();
            let tipe = $('#txted_tipe').val();
            let id_supplier = $('#cboed_supplier').val();

            if (!jenis || !merk || !tipe || !id_supplier) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Jenis, Merk, Tipe, dan Supplier wajib diisi',
                });
                return;
            }

            let $btn = $('#editJenisMesinButton');
            $btn.prop('disabled', true);

            $.ajax({
                type: "POST",
                url: '{{ route('update_jenis_mesin') }}',
                data: {
                    _token: '{{ csrf_token() }}',
                    id: id,
                    jenis: jenis,
                    merk: merk,
                    tipe: tipe,
                    id_supplier: id_supplier
                },
                success: function(response) {
                    $('#EditJenisMesinModal').modal('hide');
                    dataTableReload();

                    Swal.fire({
                        icon: 'success',
                        title: 'Jenis Mesin Diupdate',
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
                title: 'Hapus Jenis Mesin?',
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
                    url: '{{ route('delete_jenis_mesin') }}',
                    data: {
                        _token: '{{ csrf_token() }}',
                        id: id_c
                    },
                    success: function(response) {
                        dataTableReload();
                        Swal.fire({
                            icon: 'success',
                            title: 'Jenis Mesin Dihapus',
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
                url: '{{ route('asset_master_jenis_mesin') }}',
            },
            columns: [{
                    data: 'jenis'
                }, // Jenis
                {
                    data: 'merk'
                }, // Merk
                {
                    data: 'tipe'
                }, // Tipe
                {
                    data: 'supplier'
                }, // Supplier
                {
                    data: 'id_jenis',
                    render: function(data) {
                        return `
                    <div class="text-center">
                        <button class="btn btn-sm btn-warning" onclick="editData(${data})"
                                data-bs-toggle="modal" data-bs-target="#EditJenisMesinModal">
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
