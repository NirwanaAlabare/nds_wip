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
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-list-alt"></i> Master Jenis Mesin</h5>
        </div>
        <div class="card-body">
            <div class="row mb-3 align-items-end">
                <div class="col-md-3">
                    <label for="cbojenis"><small><b>Jenis :</b></small></label>
                    <div class="d-flex align-items-center" style="gap: 6px;">
                        <select id="cbojenis" name="cbojenis"
                            class="form-control form-control-sm select2bs4 border-primary" style="width: 100%;">
                            <option value="">-- Pilih Jenis --</option>
                            @foreach ($jenisList as $row)
                                <option value="{{ $row->kd_jenis }}">{{ $row->kd_jenis }} - {{ $row->nm_jenis }}</option>
                            @endforeach
                        </select>
                        <button type="button" class="btn btn-outline-primary btn-sm flex-shrink-0" title="Tambah Jenis"
                            data-bs-toggle="modal" data-bs-target="#AddJenisModal">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-3">
                    <label for="cbomerk"><small><b>Merk :</b></small></label>
                    <div class="d-flex align-items-center" style="gap: 6px;">
                        <select id="cbomerk" name="cbomerk" class="form-control form-control-sm select2bs4 border-primary"
                            style="width: 100%;">
                            <option value="">-- Pilih Merk --</option>
                            @foreach ($merkList as $row)
                                <option value="{{ $row->kd_merk }}">{{ $row->kd_merk }} - {{ $row->nm_merk }}</option>
                            @endforeach
                        </select>
                        <button type="button" class="btn btn-outline-primary btn-sm flex-shrink-0" title="Tambah Merk"
                            data-bs-toggle="modal" data-bs-target="#AddMerkModal">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-2">
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
                            <th scope="col" class="text-center align-middle">Kode Jenis</th>
                            <th scope="col" class="text-center align-middle">Jenis</th>
                            <th scope="col" class="text-center align-middle">Kode Merk</th>
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
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-sb text-white">
                    <h5 class="modal-title" id="EditJenisMesinModalLabel">Edit Jenis Mesin</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="txted_id" name="txted_id" value="">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="cboed_jenis"><small><b>Jenis :</b></small></label>
                            <select id="cboed_jenis" name="cboed_jenis"
                                class="form-control form-control-sm select2bs4 border-primary" style="width: 100%;">
                                <option value="">-- Pilih Jenis --</option>
                                @foreach ($jenisList as $row)
                                    <option value="{{ $row->kd_jenis }}">{{ $row->kd_jenis }} - {{ $row->nm_jenis }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="cboed_merk"><small><b>Merk :</b></small></label>
                            <select id="cboed_merk" name="cboed_merk"
                                class="form-control form-control-sm select2bs4 border-primary" style="width: 100%;">
                                <option value="">-- Pilih Merk --</option>
                                @foreach ($merkList as $row)
                                    <option value="{{ $row->kd_merk }}">{{ $row->kd_merk }} - {{ $row->nm_merk }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="txted_tipe"><small><b>Tipe :</b></small></label>
                            <input type="text" id="txted_tipe" name="txted_tipe" class="form-control form-control-sm"
                                style="text-transform: uppercase;" oninput="this.value = this.value.toUpperCase();">
                        </div>
                        <div class="col-md-4">
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

    <!-- Modal Tambah/Edit Jenis -->
    <div class="modal fade" id="AddJenisModal" tabindex="-1" aria-labelledby="AddJenisModalLabel" aria-hidden="true"
        data-bs-backdrop="static">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-sb text-white">
                    <h5 class="modal-title" id="AddJenisModalLabel">Kelola Jenis</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="txtnew_id_jenis" name="txtnew_id_jenis" value="">
                    <div class="row mb-3">
                        <div class="col-md-5 mb-2">
                            <label for="txtnew_kd_jenis"><small><b>Kode Jenis :</b></small></label>
                            <input type="text" id="txtnew_kd_jenis" name="txtnew_kd_jenis"
                                class="form-control form-control-sm" placeholder="contoh: SN untuk SINGLE NEEDLE"
                                style="text-transform: uppercase;"
                                oninput="this.value = this.value.toUpperCase().replace(/\s/g, '');">
                        </div>
                        <div class="col-md-7">
                            <label for="txtnew_nm_jenis"><small><b>Nama Jenis :</b></small></label>
                            <div class="d-flex align-items-end" style="gap: 6px;">
                                <input type="text" id="txtnew_nm_jenis" name="txtnew_nm_jenis"
                                    class="form-control form-control-sm" style="text-transform: uppercase;"
                                    oninput="this.value = this.value.toUpperCase();">
                                <button type="button" class="btn btn-success btn-sm flex-shrink-0" id="saveJenisButton"
                                    onclick="save_kd_jenis();">Save</button>
                                <button type="button" class="btn btn-secondary btn-sm flex-shrink-0 d-none"
                                    id="cancelEditJenisButton" onclick="cancel_edit_kd_jenis();">Cancel</button>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="table-responsive" style="max-height: 250px; overflow-y: auto;">
                        <table class="table table-bordered table-sm table-hover align-middle mb-0">
                            <thead class="bg-sb">
                                <tr>
                                    <th class="text-center">Kode Jenis</th>
                                    <th class="text-center">Nama Jenis</th>
                                    <th class="text-center">Act</th>
                                </tr>
                            </thead>
                            <tbody id="tblJenisListBody">
                                @foreach ($jenisList as $row)
                                    <tr data-id="{{ $row->id_jenis }}">
                                        <td class="text-center">{{ $row->kd_jenis }}</td>
                                        <td>{{ $row->nm_jenis }}</td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-warning"
                                                onclick="edit_kd_jenis({{ $row->id_jenis }}, '{{ $row->kd_jenis }}', '{{ $row->nm_jenis }}')">
                                                <i class="fa fa-edit"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah/Edit Merk -->
    <div class="modal fade" id="AddMerkModal" tabindex="-1" aria-labelledby="AddMerkModalLabel" aria-hidden="true"
        data-bs-backdrop="static">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-sb text-white">
                    <h5 class="modal-title" id="AddMerkModalLabel">Kelola Merk</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="txtnew_id_merk" name="txtnew_id_merk" value="">
                    <div class="row mb-3">
                        <div class="col-md-5 mb-2">
                            <label for="txtnew_kd_merk"><small><b>Kode Merk :</b></small></label>
                            <input type="text" id="txtnew_kd_merk" name="txtnew_kd_merk"
                                class="form-control form-control-sm" placeholder="contoh: JK untuk JUKI"
                                style="text-transform: uppercase;"
                                oninput="this.value = this.value.toUpperCase().replace(/\s/g, '');">
                        </div>
                        <div class="col-md-7">
                            <label for="txtnew_nm_merk"><small><b>Nama Merk :</b></small></label>
                            <div class="d-flex align-items-end" style="gap: 6px;">
                                <input type="text" id="txtnew_nm_merk" name="txtnew_nm_merk"
                                    class="form-control form-control-sm" style="text-transform: uppercase;"
                                    oninput="this.value = this.value.toUpperCase();">
                                <button type="button" class="btn btn-success btn-sm flex-shrink-0" id="saveMerkButton"
                                    onclick="save_kd_merk();">Save</button>
                                <button type="button" class="btn btn-secondary btn-sm flex-shrink-0 d-none"
                                    id="cancelEditMerkButton" onclick="cancel_edit_kd_merk();">Cancel</button>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="table-responsive" style="max-height: 250px; overflow-y: auto;">
                        <table class="table table-bordered table-sm table-hover align-middle mb-0">
                            <thead class="bg-sb">
                                <tr>
                                    <th class="text-center">Kode Merk</th>
                                    <th class="text-center">Nama Merk</th>
                                    <th class="text-center">Act</th>
                                </tr>
                            </thead>
                            <tbody id="tblMerkListBody">
                                @foreach ($merkList as $row)
                                    <tr data-id="{{ $row->id_merk }}">
                                        <td class="text-center">{{ $row->kd_merk }}</td>
                                        <td>{{ $row->nm_merk }}</td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-warning"
                                                onclick="edit_kd_merk({{ $row->id_merk }}, '{{ $row->kd_merk }}', '{{ $row->nm_merk }}')">
                                                <i class="fa fa-edit"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
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

        // Reset filter form to default state on every page load/refresh
        $('#cbojenis').val(null).trigger('change');
        $('#cbomerk').val(null).trigger('change');
        $('#txttipe').val('');
        $('#cbosupplier').val(null).trigger('change');

        function notif() {
            alert("Maaf, Fitur belum tersedia!");
        }

        $('#AddJenisModal').on('hidden.bs.modal', function() {
            reset_kd_jenis_form();
        });

        $('#AddMerkModal').on('hidden.bs.modal', function() {
            reset_kd_merk_form();
        });

        function reset_kd_jenis_form() {
            $('#txtnew_id_jenis').val('');
            $('#txtnew_kd_jenis').val('').prop('readonly', false);
            $('#txtnew_nm_jenis').val('');
            $('#saveJenisButton').text('Save');
            $('#cancelEditJenisButton').addClass('d-none');
        }

        function edit_kd_jenis(id_jenis, kd_jenis, nm_jenis) {
            $('#txtnew_id_jenis').val(id_jenis);
            $('#txtnew_kd_jenis').val(kd_jenis);
            $('#txtnew_nm_jenis').val(nm_jenis).focus();
            $('#saveJenisButton').text('Update');
            $('#cancelEditJenisButton').removeClass('d-none');
        }

        function cancel_edit_kd_jenis() {
            reset_kd_jenis_form();
        }

        function save_kd_jenis() {
            let id_jenis = $('#txtnew_id_jenis').val();
            let kd_jenis = $('#txtnew_kd_jenis').val();
            let nm_jenis = $('#txtnew_nm_jenis').val();

            if (!kd_jenis || !nm_jenis) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Kode Jenis dan Nama Jenis wajib diisi',
                });
                return;
            }

            if (/\s/.test(kd_jenis)) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Kode Jenis tidak boleh mengandung spasi',
                });
                return;
            }

            let isEdit = !!id_jenis;
            let $btn = $('#saveJenisButton');
            $btn.prop('disabled', true);

            $.ajax({
                type: "POST",
                url: isEdit ? '{{ route('update_kd_jenis') }}' : '{{ route('store_kd_jenis') }}',
                data: {
                    _token: '{{ csrf_token() }}',
                    id_jenis: id_jenis,
                    kd_jenis: kd_jenis,
                    nm_jenis: nm_jenis
                },
                success: function(response) {
                    let label = response.kd_jenis + ' - ' + response.nm_jenis;

                    if (isEdit) {
                        let oldVal = response.old_kd_jenis;
                        [$('#cbojenis'), $('#cboed_jenis')].forEach(function($el) {
                            $el.find('option[value="' + oldVal + '"]').text(label).val(response
                                .kd_jenis);
                            $el.trigger('change');
                        });
                        $('#tblJenisListBody tr[data-id="' + id_jenis + '"]').html(
                            '<td class="text-center">' + response.kd_jenis + '</td>' +
                            '<td>' + response.nm_jenis + '</td>' +
                            '<td class="text-center">' +
                            '<button type="button" class="btn btn-sm btn-warning" onclick="edit_kd_jenis(' +
                            id_jenis + ',\'' + response.kd_jenis + '\',\'' + response.nm_jenis +
                            '\')"><i class="fa fa-edit"></i></button></td>'
                        );
                        dataTableReload();
                    } else {
                        let option = new Option(label, response.kd_jenis, false, false);
                        $('#cbojenis').append(option.cloneNode(true));
                        $('#cboed_jenis').append(option.cloneNode(true));
                        $('#cbojenis').val(response.kd_jenis).trigger('change');
                        $('#tblJenisListBody').append(
                            '<tr data-id="' + response.id_jenis + '">' +
                            '<td class="text-center">' + response.kd_jenis + '</td>' +
                            '<td>' + response.nm_jenis + '</td>' +
                            '<td class="text-center">' +
                            '<button type="button" class="btn btn-sm btn-warning" onclick="edit_kd_jenis(' +
                            response.id_jenis + ',\'' + response.kd_jenis + '\',\'' + response.nm_jenis +
                            '\')"><i class="fa fa-edit"></i></button></td></tr>'
                        );
                    }

                    reset_kd_jenis_form();

                    Swal.fire({
                        icon: 'success',
                        title: isEdit ? 'Jenis Diupdate' : 'Jenis Ditambahkan',
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

        function reset_kd_merk_form() {
            $('#txtnew_id_merk').val('');
            $('#txtnew_kd_merk').val('').prop('readonly', false);
            $('#txtnew_nm_merk').val('');
            $('#saveMerkButton').text('Save');
            $('#cancelEditMerkButton').addClass('d-none');
        }

        function edit_kd_merk(id_merk, kd_merk, nm_merk) {
            $('#txtnew_id_merk').val(id_merk);
            $('#txtnew_kd_merk').val(kd_merk);
            $('#txtnew_nm_merk').val(nm_merk).focus();
            $('#saveMerkButton').text('Update');
            $('#cancelEditMerkButton').removeClass('d-none');
        }

        function cancel_edit_kd_merk() {
            reset_kd_merk_form();
        }

        function save_kd_merk() {
            let id_merk = $('#txtnew_id_merk').val();
            let kd_merk = $('#txtnew_kd_merk').val();
            let nm_merk = $('#txtnew_nm_merk').val();

            if (!kd_merk || !nm_merk) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Kode Merk dan Nama Merk wajib diisi',
                });
                return;
            }

            if (/\s/.test(kd_merk)) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Kode Merk tidak boleh mengandung spasi',
                });
                return;
            }

            let isEdit = !!id_merk;
            let $btn = $('#saveMerkButton');
            $btn.prop('disabled', true);

            $.ajax({
                type: "POST",
                url: isEdit ? '{{ route('update_kd_merk') }}' : '{{ route('store_kd_merk') }}',
                data: {
                    _token: '{{ csrf_token() }}',
                    id_merk: id_merk,
                    kd_merk: kd_merk,
                    nm_merk: nm_merk
                },
                success: function(response) {
                    let label = response.kd_merk + ' - ' + response.nm_merk;

                    if (isEdit) {
                        let oldVal = response.old_kd_merk;
                        [$('#cbomerk'), $('#cboed_merk')].forEach(function($el) {
                            $el.find('option[value="' + oldVal + '"]').text(label).val(response
                                .kd_merk);
                            $el.trigger('change');
                        });
                        $('#tblMerkListBody tr[data-id="' + id_merk + '"]').html(
                            '<td class="text-center">' + response.kd_merk + '</td>' +
                            '<td>' + response.nm_merk + '</td>' +
                            '<td class="text-center">' +
                            '<button type="button" class="btn btn-sm btn-warning" onclick="edit_kd_merk(' +
                            id_merk + ',\'' + response.kd_merk + '\',\'' + response.nm_merk +
                            '\')"><i class="fa fa-edit"></i></button></td>'
                        );
                        dataTableReload();
                    } else {
                        let option = new Option(label, response.kd_merk, false, false);
                        $('#cbomerk').append(option.cloneNode(true));
                        $('#cboed_merk').append(option.cloneNode(true));
                        $('#cbomerk').val(response.kd_merk).trigger('change');
                        $('#tblMerkListBody').append(
                            '<tr data-id="' + response.id_merk + '">' +
                            '<td class="text-center">' + response.kd_merk + '</td>' +
                            '<td>' + response.nm_merk + '</td>' +
                            '<td class="text-center">' +
                            '<button type="button" class="btn btn-sm btn-warning" onclick="edit_kd_merk(' +
                            response.id_merk + ',\'' + response.kd_merk + '\',\'' + response.nm_merk +
                            '\')"><i class="fa fa-edit"></i></button></td></tr>'
                        );
                    }

                    reset_kd_merk_form();

                    Swal.fire({
                        icon: 'success',
                        title: isEdit ? 'Merk Diupdate' : 'Merk Ditambahkan',
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

        function save_jenis_mesin() {
            let jenis = $('#cbojenis').val();
            let merk = $('#cbomerk').val();
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
                    $('#cbojenis').val(null).trigger('change');
                    $('#cbomerk').val(null).trigger('change');
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
                    $('#cboed_jenis').val(res.kd_jenis).trigger('change');
                    $('#cboed_merk').val(res.kd_merk).trigger('change');
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
            let jenis = $('#cboed_jenis').val();
            let merk = $('#cboed_merk').val();
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
                    data: 'kd_jenis'
                }, // Kode Jenis
                {
                    data: 'jenis'
                }, // Jenis
                {
                    data: 'kd_merk'
                }, // Kode Merk
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
