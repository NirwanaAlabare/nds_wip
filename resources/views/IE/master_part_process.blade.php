@extends('layouts.index')

@section('custom-link')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">

    <!-- jQuery -->
    <script src="{{ asset('plugins/datatables 2.0/jquery-3.3.1.js') }}"></script>

    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

    <style>
        .modal-fullscreen-width {
            max-width: 90vw;
            width: 90vw;
            margin: 0 auto;
        }

        .preview-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
        }

        .preview-box {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
        }

        .preview-img {
            max-width: 150;
            max-height: 150px;
            border: 1px solid #ccc;
            border-radius: 10px;
            display: none;
            cursor: pointer;
        }

        /* Overlay modal image */
        .preview-modal {
            display: none;
            /* hidden by default */
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            justify-content: center;
            align-items: center;
        }

        .preview-modal-content {
            max-width: 90%;
            max-height: 90%;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.5);
        }

        .close {
            position: absolute;
            top: 20px;
            right: 35px;
            color: #fff;
            font-size: 40px;
            font-weight: bold;
            cursor: pointer;
        }
    </style>
@endsection

@section('content')
    <!-- Image Preview Modal -->
    <div id="previewModal" class="preview-modal">
        <span class="close" onclick="closePreview()">&times;</span>
        <img class="preview-modal-content" id="modalImg">
    </div>
    <!-- Create Modal -->
    <div class="modal fade" id="CreateModal" tabindex="-1" aria-labelledby="CreateModalLabel" aria-hidden="true"
        data-bs-backdrop="static">
        <div class="modal-dialog modal-fullscreen-width">
            <div class="modal-content">
                <div class="modal-header bg-sb text-white">
                    <h5 class="modal-title" id="CreateModalLabel">New Master Part Process</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="row mb-3">
                        <!-- Left column -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="txtname"><small><b>Name :</b></small></label>
                                <input type="text" id="txtname" name="txtname" class="form-control form-control-sm">
                            </div>

                            <div class="mb-3">
                                <label for="picture"><small><b>Picture :</b></small></label>
                                <input type="file" id="picture" name="picture" class="form-control form-control-sm"
                                    accept="image/*" onchange="previewImage(event)">
                            </div>
                        </div>

                        <!-- Right column (Preview) -->
                        <div class="col-md-6 preview-wrapper">
                            <label><small><b>Preview :</b></small></label>
                            <div class="preview-box">
                                <img id="imgPreview" src="" class="preview-img" onclick="openPreview(this)">
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table id="datatable_modal" class="table table-bordered table-hover align-middle text-nowrap w-100">
                            <thead class="bg-sb">
                                <tr>
                                    <th class="text-center">Act</th>
                                    <th class="text-center">Process</th>
                                    <th class="text-center">Class</th>
                                    <th class="text-center">SMV</th>
                                    <th class="text-center">AMV</th>
                                    <th class="text-center">Machine Type</th>
                                    <th class="text-center">Remark</th>
                                </tr>
                            </thead>
                        </table>
                    </div>

                    <div class="row mt-3 d-flex justify-content-center text-center">
                        <div class="col-md-3">
                            <label>Total SMV</label>
                            <input type="text" id="total_smv" class="form-control text-center" value="0" readonly>
                        </div>

                        <div class="col-md-3">
                            <label>Total AMV</label>
                            <input type="text" id="total_amv" class="form-control text-center" value="0" readonly>
                        </div>
                    </div>


                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-success" id="saveButton"
                        onclick="save_master_part_process();">Save</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="EditModal" tabindex="-1" aria-labelledby="EditModalLabel" aria-hidden="true"
        data-bs-backdrop="static">
        <div class="modal-dialog modal-fullscreen-width">
            <div class="modal-content">
                <div class="modal-header bg-sb text-white">
                    <h5 class="modal-title" id="EditModalLabel">Edit Master Part Process</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="row mb-3">
                        <!-- Left column -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="txtedname"><small><b>Name :</b></small></label>
                                <input type="text" id="txtedname" name="txtedname"
                                    class="form-control form-control-sm">
                            </div>

                            <div class="mb-3">
                                <label for="edpicture"><small><b>Picture :</b></small></label>
                                <input type="file" id="edpicture" name="edpicture"
                                    class="form-control form-control-sm" accept="image/*"
                                    onchange="previewImageEdit(event)">
                            </div>
                            <!-- Hidden input untuk nama file lama -->
                            <input type="hidden" id="old_picture" name="old_picture">
                            <input type="hidden" id="id_c" name="id_c">
                        </div>

                        <!-- Right column (Preview) -->
                        <div class="col-md-6 preview-wrapper">
                            <label><small><b>Preview :</b></small></label>
                            <div class="preview-box">
                                <img id="imgPreviewEdit" src="" class="preview-img"
                                    style="max-width:150px; max-height:150px; display:none; cursor:pointer;"
                                    onclick="openPreview(this)">
                            </div>

                            <small class="text-muted">Gambar lama ditampilkan. Pilih file baru untuk mengganti.</small>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table id="datatable_edit"
                            class="table table-bordered table-hover align-middle text-nowrap w-100">
                            <thead class="bg-sb">
                                <tr>
                                    <th class="text-center">Act</th>
                                    <th class="text-center">Process</th>
                                    <th class="text-center">Class</th>
                                    <th class="text-center">SMV</th>
                                    <th class="text-center">AMV</th>
                                    <th class="text-center">Machine Type</th>
                                    <th class="text-center">Remark</th>
                                </tr>
                            </thead>
                        </table>
                    </div>

                    <div class="row mt-3 d-flex justify-content-center text-center">
                        <div class="col-md-3">
                            <label>Total SMV</label>
                            <input type="text" id="total_smv_edit" class="form-control text-center" value="0"
                                readonly>
                        </div>

                        <div class="col-md-3">
                            <label>Total AMV</label>
                            <input type="text" id="total_amv_edit" class="form-control text-center" value="0"
                                readonly>
                        </div>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-success"
                        onclick="update_master_part_process();">Update</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Master Table -->
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-list"></i> Master Part Proses</h5>
        </div>
        <div class="card-body">
            <div class="d-flex justify-content-between mb-3">
                <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal"
                    data-bs-target="#CreateModal">
                    <i class="fas fa-plus"></i> New
                </button>
            </div>

            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-hover align-middle text-nowrap w-100">
                    <thead class="bg-sb">
                        <tr>
                            <th class="text-center">Picture</th>
                            <th class="text-center">Name</th>
                            <th class="text-center">Count Process</th>
                            <th class="text-center">Total SMV</th>
                            <th class="text-center">Total AMV</th>
                            <th class="text-center">Updated At</th>
                            <th class="text-center">Act</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('custom-script')
    <!-- DataTables & Select2 -->
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>

    <script>
        // Select2 Initialization
        $(document).ready(function() {
            $('.select2').select2();
            $('.select2bs4').select2({
                theme: 'bootstrap4',
                width: 'resolve'
            });
        });
        // Datatable
        let datatable = $("#datatable").DataTable({
            ordering: false,
            processing: true,
            serverSide: false,
            paging: true,
            searching: true,
            scrollY: "600px",
            scrollCollapse: true,
            scrollX: true,
            ajax: {
                url: '{{ route('IE_master_part_process') }}',
            },

            columns: [{
                    data: 'picture',
                    className: 'text-center align-middle',
                    render: function(data) {
                        if (!data) return 'No Photo';
                        const imageUrl = `/nds_wip/public/storage/gambar_part_process/${data}`;
                        return `
                    <img src="${imageUrl}"
                         alt="Photo"
                         class="img-thumbnail"
                         onclick="openPreview(this)"
                         style="max-height: 100px; max-width: 100%; object-fit: contain; cursor: pointer;" />
                `;
                    }
                },
                {
                    className: 'text-center align-middle',
                    data: 'nm_part_process'
                },
                {
                    className: 'text-center align-middle',
                    data: 'tot_process'
                },
                {
                    className: 'text-center align-middle',
                    data: 'tot_smv'
                },
                {
                    className: 'text-center align-middle',
                    data: 'tot_amv'
                },
                {
                    data: null,
                    className: 'text-center align-middle',
                    render: function(data, type, row) {
                        return `
                    <span style="font-weight:600;">${row.created_by}</span>
                    <span style="color:#666; font-size:0.9em; margin-left:6px;">&bull; ${row.tgl_update_fix}</span>
                `;
                    }
                },
                {
                    data: 'id_part_process',
                    className: 'text-center align-middle',
                    render: function(data) {
                        return `
            <button class="btn btn-sm btn-warning" onclick="editData('${data}')"
                    data-bs-toggle="modal" data-bs-target="#EditModal">
                <i class="fa fa-edit"></i>
            </button>
        `;
                    },
                    orderable: false,
                    searchable: false
                }

            ]
        });

        function dataTableReload() {
            datatable.ajax.reload();
        }

        function dataTableEditReload() {
            datatable_edit.ajax.reload();
        }

        // Preview Image before upload
        function previewImage(event) {
            const img = document.getElementById('imgPreview');
            img.src = URL.createObjectURL(event.target.files[0]);
            img.style.display = 'block';
        }

        // Open image in modal
        function openPreview(imgElement) {
            const modal = document.getElementById('previewModal');
            const modalImg = document.getElementById('modalImg');
            modalImg.src = imgElement.src;
            modal.style.display = 'flex';
        }

        function closePreview() {
            document.getElementById('previewModal').style.display = 'none';
        }

        // Tutup modal dengan tombol ESC
        document.addEventListener('keydown', function(event) {
            if (event.key === "Escape") {
                closePreview();
            }
        });


        function reset_modal_new() {
            const imgPreview = document.getElementById('imgPreview');
            imgPreview.src = '';
            imgPreview.style.display = 'none';

            const modal = document.getElementById('previewModal');
            const modalImg = document.getElementById('modalImg');
            modal.style.display = 'none';
            modalImg.src = '';

            $('#txtname').val('');
            $('#picture').val('');
            $('#total_smv').val('0');
            $('#total_amv').val('0');

            dataTableModalReload();

            setTimeout(() => {
                $(".row-check").prop("checked", false);
                calculateTotals(); // untuk memastikan total kembali 0
            }, 300); // delay kecil supaya datatables selesai render
        }

        // Clear preview when Create Modal opens
        $('#CreateModal').on('show.bs.modal', function() {
            reset_modal_new();
        });

        function dataTableModalReload() {
            datatable_modal.ajax.reload();
        }

        // DataTable Modal
        let datatable_modal = $("#datatable_modal").DataTable({
            ordering: false,
            processing: true,
            serverSide: false,
            paging: false,
            searching: true,
            scrollY: "300px",
            scrollCollapse: true,
            scrollX: true,
            ajax: {
                url: '{{ route('IE_master_part_process_show_new') }}',
            },
            columns: [{
                    data: null,
                    className: "text-center",
                    render: function(data, type, row) {
                        return `<input type="checkbox" class="row-check"
                        data-id="${row.id}"
                         data-smv="${row.smv}"
                         data-amv="${row.amv}">`;
                    }
                },
                {
                    data: 'nm_process'
                },
                {
                    data: 'class'
                },
                {
                    data: 'smv'
                },
                {
                    data: 'amv'
                },
                {
                    data: 'machine_type'
                },
                {
                    data: 'remark'
                }
            ],
        });

        // Hitung ketika checkbox individual diklik
        $(document).on("change", ".row-check", function() {
            calculateTotals();
        });

        // Function total
        function calculateTotals() {
            let totalSMV = 0;
            let totalAMV = 0;

            $(".row-check:checked").each(function() {
                totalSMV += parseFloat($(this).data("smv")) || 0;
                totalAMV += parseFloat($(this).data("amv")) || 0;
            });

            $("#total_smv").val(totalSMV.toFixed(3));
            $("#total_amv").val(totalAMV.toFixed(3));
        }

        function save_master_part_process() {
            let name = $('#txtname').val();
            let picture = $('#picture')[0].files[0]; // file gambar

            // Disable the Save button to prevent multiple clicks
            let $btn = $('#saveButton'); // Add id="saveButton" to your Save button
            $btn.prop('disabled', true);

            // Ambil semua ID checklist
            let selectedIds = [];
            $(".row-check:checked").each(function() {
                selectedIds.push($(this).data("id"));
            });

            if (!picture) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Picture Required',
                    text: 'Please upload a picture first.'
                });
                $btn.prop('disabled', false);
                return;
            }


            // FORM DATA → WAJIB untuk upload file
            let formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('name', name);
            formData.append('picture', picture); // tambahkan gambar

            // Tambahkan ID checklist ke FormData
            selectedIds.forEach(id => {
                formData.append('ids[]', id);
            });


            $.ajax({
                type: "POST",
                url: '{{ route('IE_save_master_part_process') }}',
                data: formData,
                processData: false, // WAJIB → jangan diubah!
                contentType: false, // WAJIB → biar multipart/form-data
                success: function(response) {
                    // Reset form dulu

                    $('#txtname').val('');
                    $('#picture').val('');
                    $('#total_smv').val('0');
                    $('#total_amv').val('0');

                    // Reset checkbox
                    $(".row-check").prop("checked", false);

                    // Reset image preview
                    const imgPreview = document.getElementById('imgPreview');
                    imgPreview.src = '';
                    imgPreview.style.display = 'none';
                    dataTableReload();

                    // Reload DataTable dan setelah selesai baru hitung total
                    datatable_modal.ajax.reload(function() {
                        calculateTotals();
                    });
                    // SweetAlert success notification
                    Swal.fire({
                        icon: 'success',
                        title: 'Data Added',
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

        let datatable_edit;

        function calculateTotalsEdit() {
            let totalSMV = 0;
            let totalAMV = 0;

            $("#datatable_edit .row-check:checked").each(function() {
                totalSMV += parseFloat($(this).data("smv")) || 0;
                totalAMV += parseFloat($(this).data("amv")) || 0;
            });

            $("#total_smv_edit").val(totalSMV.toFixed(3));
             $("#total_amv_edit").val(totalAMV.toFixed(3));
        }

        $(document).on("change", "#datatable_edit .row-check", function() {
            calculateTotalsEdit();
        });



        function loadDatatableEdit(id_c) {

            if ($.fn.DataTable.isDataTable('#datatable_edit')) {
                datatable_edit.destroy();
            }

            datatable_edit = $("#datatable_edit").DataTable({
                ordering: false,
                processing: true,
                serverSide: false,
                paging: false,
                searching: true,
                scrollY: "300px",
                scrollCollapse: true,
                scrollX: true,
                ajax: {
                    url: '{{ route('IE_master_part_process_show_edit') }}',
                    data: {
                        id_c: id_c
                    }
                },
                columns: [{
                        data: null,
                        className: "text-center",
                        render: function(data, type, row) {
                            let checked = row.selected ? 'checked' : '';
                            return `
                        <input type="checkbox" class="row-check"
                        data-id="${row.id}"
                        data-smv="${row.smv}"
                        data-amv="${row.amv}"
                        ${checked}>
                    `;
                        }
                    },
                    {
                        data: 'nm_process'
                    },
                    {
                        data: 'class'
                    },
                    {
                        data: 'smv'
                    },
                    {
                        data: 'amv'
                    },
                    {
                        data: 'machine_type'
                    },
                    {
                        data: 'remark'
                    }
                ],

                // === HITUNG TOTAL SETELAH DATATABLE SIAP ===
                initComplete: function() {
                    calculateTotalsEdit();
                }
            });
        }



        function editData(id_c) {
            $("#id_c").val(id_c);

            $.ajax({
                url: '{{ route('IE_show_master_part_process') }}',
                method: 'GET',
                data: {
                    id_c: id_c
                },
                dataType: 'json',
                success: function(res) {

                    $("#txtedname").val(res.nm_part_process);
                    $("#old_picture").val(res.picture || '');

                    // === PREVIEW GAMBAR LAMA (PAKE IMG TAG) ===
                    if (res.picture) {
                        const imageUrl =
                            `/nds_wip/public/storage/gambar_part_process/${res.picture}?t=${new Date().getTime()}`;
                        $("#imgPreviewEdit").attr("src", imageUrl).show();
                    } else {
                        $("#imgPreviewEdit").attr("src", "").hide();
                    }

                    // SHOW MODAL
                    $("#EditModal").modal('show');

                    // LOAD DATATABLE SETELAH MODAL SIAP
                    setTimeout(() => {
                        loadDatatableEdit(id_c);
                    }, 200);

                    dataTableReload();

                }
            });
        }



        // Preview file baru di modal Edit
        function previewImageEdit(event) {
            const img = document.getElementById('imgPreviewEdit');
            img.src = URL.createObjectURL(event.target.files[0]);
            img.style.display = 'block';
        }


        function update_master_part_process() {

            let formData = new FormData();

            formData.append("id_c", $("#id_c").val());
            formData.append("name", $("#txtedname").val());

            // Picture handling
            let newFile = $("#edpicture")[0].files[0];
            if (newFile) {
                formData.append("picture", newFile);
            }
            formData.append("old_picture", $("#old_picture").val());

            // Checkbox values (id_process)
            let selectedIDs = [];
            $("#datatable_edit .row-check:checked").each(function() {
                selectedIDs.push($(this).data("id"));
            });

            formData.append("selected_ids", JSON.stringify(selectedIDs));

            // CSRF
            formData.append("_token", "{{ csrf_token() }}");

            $.ajax({
                url: "{{ route('IE_update_master_part_process') }}",
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {

                    Swal.fire({
                        icon: "success",
                        title: "Updated!",
                        text: "Master Part Process berhasil diupdate."
                    });
                    dataTableReload();
                    dataTableEditReload();
                },
                error: function(xhr) {
                    alert(xhr.responseText);
                }
            });
        }
    </script>
@endsection
