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
    </style>
@endsection

@section('content')
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-list"></i> Master Proses</h5>
        </div>
        <div class="card-body">
            <div class="d-flex justify-content-between mb-3">

                <!-- Button New (Left) -->
                <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal"
                    data-bs-target="#CreateModal">
                    <i class="fas fa-plus"></i> New
                </button>

                <!-- Right Buttons (Import + Format Upload) -->
                <div class="d-flex gap-2">
                    <!-- Button Format Upload -->
                    <a href="{{ route('contoh_upload_master_process') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-file-alt"></i> Format Upload
                    </a>
                    <!-- Button Import -->
                    <button type="button" class="btn btn-outline-success btn-sm" onclick="OpenModal()">
                        <i class="fas fa-file-import"></i> Import
                    </button>
                </div>

            </div>




            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-hover align-middle text-nowrap w-100">
                    <thead class="bg-sb">
                        <tr>
                            <th scope="col" class="text-center align-middle">Process Name</th>
                            <th scope="col" class="text-center align-middle">Class</th>
                            <th scope="col" class="text-center align-middle">SMV</th>
                            <th scope="col" class="text-center align-middle">AMV</th>
                            <th scope="col" class="text-center align-middle">Machine Type</th>
                            <th scope="col" class="text-center align-middle">Kategori</th>
                            <th scope="col" class="text-center align-middle">Remark</th>
                            <th scope="col" class="text-center align-middle">Created At</th>
                            <th scope="col" class="text-center align-middle">Act</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="CreateModal" tabindex="-1" aria-labelledby="CreateModalLabel" aria-hidden="true"
        data-bs-backdrop="static">
        <div class="modal-dialog modal-xl"> <!-- ubah modal-lg ke modal-sm/md sesuai kebutuhan -->
            <div class="modal-content">
                <div class="modal-header bg-sb text-white">
                    <h5 class="modal-title" id="CreateModalLabel">New Master Process</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Isi form di sini -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="txtname"><small><b>Process Name :</b></small></label>
                            <input type="text" id="txtname" name="txtname" class="form-control form-control-sm"
                                value="">
                        </div>
                        <div class="col-md-4">
                            <label for="cboclass" class="form-label"><small><b>Class :</b></small></label>
                            <select id="cboclass" name="cboclass"
                                class="form-control form-control-sm select2bs4 border-primary" style="width: 100%;">
                                <option value="">-- Select Class --</option>
                                <option value="Operator">OPERATOR</option>
                                <option value="Helper">HELPER</option>
                                <option value="Steam">STEAM</option>
                                <option value="QC">QC</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="txttype"><small><b>Machine Type :</b></small></label>
                            <input type="text" id="txttype" name="txttype" class="form-control form-control-sm"
                                value="">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="txtsmv"><small><b>SMV:</b></small></label>
                            <input type="number" id="txtsmv" name="txtsmv" class="form-control form-control-sm"
                                value="">
                        </div>
                        <div class="col-md-4">
                            <label for="txtamv"><small><b>AMV :</b></small></label>
                            <input type="number" id="txtamv" name="txtamv" class="form-control form-control-sm"
                                value="">
                        </div>
                        <div class="col-md-4">
                            <label for="txtkategori"><small><b>Kategori :</b></small></label>
                            <input type="text" id="txtkategori" name="txtkategori"
                                class="form-control form-control-sm" value="">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="txtremark"><small><b>Remark :</b></small></label>
                            <input type="text" id="txtremark" name="txtremark" class="form-control form-control-sm"
                                value="">
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-success" id="saveButton"
                        onclick="save_master_process();">Save</button>

                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>


    <!-- Modal Edit-->
    <div class="modal fade" id="EditModal" tabindex="-1" aria-labelledby="EditModalLabel" aria-hidden="true"
        data-bs-backdrop="static">
        <div class="modal-dialog modal-xl"> <!-- ubah modal-lg ke modal-sm/md sesuai kebutuhan -->
            <div class="modal-content">
                <div class="modal-header bg-sb text-white">
                    <h5 class="modal-title" id="EditModalLabel">Edit Master Process</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Isi form di sini -->
                    <input type="hidden" id="id_c" name="id_c" class="form-control form-control-sm"
                        value="">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="txtedname"><small><b>Process Name :</b></small></label>
                            <input type="text" id="txtedname" name="txtedname" class="form-control form-control-sm"
                                value="">
                        </div>
                        <div class="col-md-4">
                            <label for="cboedclass" class="form-label"><small><b>Class :</b></small></label>
                            <select id="cboedclass" name="cboedclass"
                                class="form-control form-control-sm select2bs4 border-primary" style="width: 100%;">
                                <option value="">-- Select Class --</option>
                                <option value="OPERATOR">OPERATOR</option>
                                <option value="HELPER">HELPER</option>
                                <option value="STEAM">STEAM</option>
                                <option value="QC">QC</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="txtedtype"><small><b>Machine Type :</b></small></label>
                            <input type="text" id="txtedtype" name="txtedtype" class="form-control form-control-sm"
                                value="">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="txtedsmv"><small><b>SMV:</b></small></label>
                            <input type="number" id="txtedsmv" name="txtedsmv" class="form-control form-control-sm"
                                value="">
                        </div>
                        <div class="col-md-4">
                            <label for="txtedamv"><small><b>AMV :</b></small></label>
                            <input type="number" id="txtedamv" name="txtedamv" class="form-control form-control-sm"
                                value="">
                        </div>
                        <div class="col-md-4">
                            <label for="txtedkategori"><small><b>Kategori :</b></small></label>
                            <input type="text" id="txtedkategori" name="txtedkategori"
                                class="form-control form-control-sm" value="">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="txtedremark"><small><b>Remark :</b></small></label>
                            <input type="text" id="txtedremark" name="txtedremark"
                                class="form-control form-control-sm" value="">
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-warning" id="editButton"
                        onclick="edit_master_process();">Edit</button>

                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>


    <!-- Import Excel -->
    <div class="modal fade" id="importExcel" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form method="post" action="{{ route('upload_excel_master_process') }}" enctype="multipart/form-data"
                onsubmit="submitUploadForm(this, event)">
                <div class="modal-content">
                    <div class="modal-header bg-sb text-light">
                        <h5 class="modal-title" id="exampleModalLabel">Import Excel Daily Cost</h5>
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
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i
                                class="fa fa-window-close" aria-hidden="true"></i> Close</button>
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
    </script>
    <script>
        function dataTableReload() {
            datatable.ajax.reload();
        }


        $(document).ready(function() {
            dataTableReload();
            $('#CreateModal').on('show.bs.modal', function() {
                // Clear text
                $('#txtname').val('');
                $('#txtsmv').val('');
                $('#txtamv').val('');
                $('#txtkategori').val('');
                $('#txtremark').val('');
                $('#txttype').val('');
                // Optional: Also clear select2 (if using select2)
                $('#cboclass').val(null).trigger('change');
            });
        })

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
                url: '{{ route('IE_master_process') }}',
            },
            columns: [{
                    data: 'nm_process'
                }, // Process Name
                {
                    data: 'class'
                }, // Class
                {
                    data: 'smv'
                }, // SMV
                {
                    data: 'amv'
                }, // AMV
                {
                    data: 'machine_type'
                }, // Machine Type
                {
                    data: 'kategori'
                },
                {
                    data: 'remark'
                }, // Remark
                {
                    data: null,
                    render: function(data, type, row) {
                        return `
      <span style="font-weight:600; vertical-align: middle;">${row.created_by}</span>
      <span style="color: #666; font-size: 0.9em; margin-left: 6px; vertical-align: middle;">&bull; ${row.tgl_update_fix}</span>
    `;
                    }
                },
                {
                    data: 'id',
                    render: function(data) {
                        return `
                    <div class="text-center">
                        <button class="btn btn-sm btn-warning" onclick="editData(${data})"
                                data-bs-toggle="modal" data-bs-target="#EditModal">
                            <i class="fa fa-edit"></i>
                        </button>
                    </div>`;
                    },
                    orderable: false,
                    searchable: false
                }

            ],


        });



        function save_master_process() {
            let process_name = $('#txtname').val();
            let class_name = $('#cboclass').val();
            let type = $('#txttype').val();
            let smv = $('#txtsmv').val();
            let amv = $('#txtamv').val();
            let kategori = $('#txtkategori').val();
            let remark = $('#txtremark').val();

            // Disable the Save button to prevent multiple clicks
            let $btn = $('#saveButton'); // Add id="saveButton" to your Save button
            $btn.prop('disabled', true);

            $.ajax({
                type: "POST",
                url: '{{ route('IE_save_master_process') }}',
                data: {
                    _token: '{{ csrf_token() }}',
                    process_name: process_name,
                    class_name: class_name,
                    type: type,
                    smv: smv,
                    amv: amv,
                    kategori: kategori,
                    remark: remark
                },
                success: function(response) {
                    // SweetAlert success notification
                    // Clear text
                    $('#txtname').val('');
                    $('#txtsmv').val('');
                    $('#txtamv').val('');
                    $('#txtremark').val('');
                    $('#txttype').val('');
                    $('#txtkategori').val('');
                    // Optional: Also clear select2 (if using select2)
                    $('#cboclass').val(null).trigger('change');
                    Swal.fire({
                        icon: 'success',
                        title: 'Data Added',
                        text: response.message,
                        timer: 1500,
                        showConfirmButton: false
                    });
                    dataTableReload();
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.message || 'Something went wrong while saving.',
                    });
                    dataTableReload();
                },
                complete: function() {
                    // Re-enable the Save button after request completes
                    $btn.prop('disabled', false);
                }
            });
        }

        function editData(id_c) {
            jQuery.ajax({
                url: '{{ route('IE_show_master_process') }}',
                method: 'GET',
                data: {
                    id_c: id_c
                },
                dataType: 'json',
                success: async function(res) {
                    $("#id_c").val(res.id);
                    $("#txtedname").val(res.nm_process);
                    $("#cboedclass").val(res.class).trigger('change'); // untuk select2
                    $("#txtedtype").val(res.machine_type);
                    $("#txtedsmv").val(res.smv);
                    $("#txtedamv").val(res.amv);
                    $("#txtedkategori").val(res.kategori);
                    $("#txtedremark").val(res.remark);
                },
                error: function(request, status, error) {
                    alert(request.responseText);
                },
            });
        }

        function edit_master_process() {
            let process_name = $('#txtedname').val();
            let class_name = $('#cboedclass').val();
            let txttype = $('#txtedtype').val();
            let smv = $('#txtedsmv').val();
            let amv = $('#txtedamv').val();
            let kategori = $('#txtedkategori').val();
            let remark = $('#txtedremark').val();
            let id_c = $('#id_c').val();

            // Disable the Save button to prevent multiple clicks
            let $btn = $('#editButton'); // Add id="editButton" to your Edit button
            $btn.prop('disabled', true);

            $.ajax({
                type: "POST",
                url: '{{ route('IE_edit_master_process') }}',
                data: {
                    _token: '{{ csrf_token() }}',
                    process_name: process_name,
                    class_name: class_name,
                    txttype: txttype,
                    smv: smv,
                    amv: amv,
                    kategori: kategori,
                    remark: remark,
                    id_c: id_c
                },
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Data Updated',
                        text: response.message,
                        timer: 1500,
                        showConfirmButton: false
                    });
                    dataTableReload();
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.message || 'Something went wrong while saving.',
                    });
                    dataTableReload();
                },
                complete: function() {
                    // Re-enable the Save button after request completes
                    $btn.prop('disabled', false);
                }
            });
        }


        function OpenModal() {
            $('#importExcel').modal('show');
        }

        function submitUploadForm(e, evt) {
            evt.preventDefault();

            clearModified();

            $.ajax({
                url: e.getAttribute('action'),
                type: e.getAttribute('method'),
                data: new FormData(e),
                processData: false,
                contentType: false,
                success: async function(res) {
                    if (res.status == 200) {
                        console.log(res);
                        e.reset();
                        Swal.fire({
                            icon: 'success',
                            title: 'Data berhasil diupload',
                            showCancelButton: false,
                            showConfirmButton: true,
                            confirmButtonText: 'Oke',
                            timer: 5000,
                            timerProgressBar: true
                        });
                        $('#importExcel').modal('hide');
                        dataTableReload();
                    } else {
                        // Jika status bukan 200, misal 422 atau 500
                        let errorMessage = res.message || 'Terjadi kesalahan saat upload';

                        // Jika ada detail error dari server
                        if (res.errors) {
                            if (Array.isArray(res.errors)) {
                                res.errors.forEach(err => {
                                    errorMessage += '\n' + (err.row ? `Baris ${err.row}: ` : '') + err
                                        .errors.join(', ');
                                });
                            } else {
                                errorMessage += '\n' + res.errors;
                            }
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Upload Gagal',
                            text: errorMessage,
                            showConfirmButton: true,
                            confirmButtonText: 'Oke'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    // Jika AJAX error, misal server mati atau 500
                    Swal.fire({
                        icon: 'error',
                        title: 'Upload Gagal',
                        text: 'Terjadi kesalahan server: ' + error,
                        showConfirmButton: true,
                        confirmButtonText: 'Oke'
                    });
                }
            });
        }
    </script>
@endsection
