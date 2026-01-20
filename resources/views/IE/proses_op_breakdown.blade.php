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

    <style>
        #CreateModal .select2-container--bootstrap4 .select2-selection--single {
            font-size: 12px;
            height: 30px;
            line-height: 30px;
        }

        #CreateModal .select2-container--bootstrap4 .select2-results__option {
            font-size: 12px;
            padding: 6px 10px;
        }

        /* ===== MODAL SIZE ===== */
        .modal-fullscreen-width {
            width: 90vw;
            max-width: 90vw;

            /* fleksibel */
            max-height: 90vh;
            margin: auto;
        }


        .modal-content {
            display: flex;
            flex-direction: column;
            height: 100vh;
            /* modal maksimal 90% layar */
        }

        .modal-header,
        .modal-footer {
            flex: 0 0 auto;
            /* tinggi header/footer tetap sesuai konten */
        }

        .modal-body {
            flex: 1 1 auto;
            /* isi yang mengambil sisa ruang */
            overflow-y: auto;
            /* scroll otomatis jika konten terlalu panjang */
        }


        /* Box detail */
        .detail-box {
            background: #ffffff;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 8px 12px;
            margin-bottom: 8px;
            transition: all 0.25s ease;
        }




        /* Hover effect */
        .detail-box:hover {
            border-color: #0d6efd;
            box-shadow: 0 4px 12px rgba(13, 110, 253, .12);
        }

        /* Title detail */
        .detail-title {
            font-size: 15px;
            font-weight: 600;
            color: #0d6efd;
            margin-bottom: 10px;
            border-bottom: 1px dashed #dee2e6;
            padding-bottom: 6px;
        }

        /* Label */
        .detail-box label {
            font-size: 12px;
            font-weight: 600;
            color: #495057;
        }

        /* Wrapper: mulai dari atas, bukan tengah */
        .preview-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            padding-top: 4px;
            /* sejajar label kiri */
        }

        /* Box preview: ukuran stabil */
        .preview-box {
            width: 100%;
            height: 220px;
            border: 1px dashed #ccc;
            border-radius: 6px;
            background: #fafafa;

            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Gambar: proporsional */
        .preview-img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
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
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-list"></i> Operational Breakdown</h5>
        </div>
        <div class="card-body">
            <div class="d-flex justify-content-between mb-3">
                <!-- Button New (Left) -->
                <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal"
                    data-bs-target="#CreateModal">
                    <i class="fas fa-plus"></i> New
                </button>
            </div>

            <div class="d-flex align-items-end gap-3 mb-3">
                <div class="mb-3">
                    <label class="form-label"><small><b>Tgl Awal</b></small></label>
                    <input type="date" class="form-control form-control-sm " id="tgl_awal" name="tgl_awal"
                        value="{{ date('Y-m-d') }}">
                </div>
                <div class="mb-3">
                    <label class="form-label"><small><b>Tgl Akhir</b></small></label>
                    <input type="date" class="form-control form-control-sm" id="tgl_akhir" name="tgl_akhir"
                        value="{{ date('Y-m-d') }}">
                </div>
                <div class="mb-3">
                    <a class="btn btn-outline-primary position-relative btn-sm" onclick="dataTableReload()">
                        <i class="fas fa-search"></i>
                        Cari
                    </a>
                </div>
                {{-- <div class="mb-3">
                    <a onclick="notif_print()" class="btn btn-outline-danger position-relative btn-sm">
                        <i class="fas fa-print fa-sm"></i>
                        Print
                    </a>
                </div> --}}
            </div>

            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-hover align-middle text-nowrap w-100">
                    <thead class="bg-sb">
                        <tr>
                            <th scope="col" class="text-center align-middle">Tgl Input</th>
                            <th scope="col" class="text-center align-middle">Garment Picture</th>
                            <th scope="col" class="text-center align-middle">Style</th>
                            <th scope="col" class="text-center align-middle">Brand</th>
                            <th scope="col" class="text-center align-middle">Product Type</th>
                            <th scope="col" class="text-center align-middle">SMV</th>
                            <th scope="col" class="text-center align-middle">Request By</th>
                            <th scope="col" class="text-center align-middle">Request Date</th>
                            <th scope="col" class="text-center align-middle">Due Date</th>
                            <th scope="col" class="text-center align-middle">Status</th>
                            <th scope="col" class="text-center align-middle">User</th>
                            <th scope="col" class="text-center align-middle">Created At</th>
                            <th scope="col" class="text-center align-middle">Act</th>

                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <!-- Image Preview Modal -->
    <div id="previewModal" class="preview-modal">
        <span class="close" onclick="closePreview()">&times;</span>
        <img class="preview-modal-content" id="modalImg">
    </div>

    <!-- Modal -->
    <div class="modal fade" id="CreateModal" tabindex="-1" aria-labelledby="CreateModalLabel" aria-hidden="true"
        data-bs-backdrop="static">
        <div class="modal-dialog modal-fullscreen-width"> <!-- ubah modal-lg ke modal-sm/md sesuai kebutuhan -->
            <div class="modal-content">
                <div class="modal-header bg-sb text-white modal-header-sm">
                    <h5 class="modal-title" id="CreateModalLabel">New Operational Breakdown</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">

                    <!-- DETAIL PROCESS -->
                    <div class="detail-box">
                        <div class="detail-title">Detail Style</div>

                        <div class="row">
                            <!-- FORM KIRI -->
                            <div class="col-md-8">
                                <div class="row g-3 mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label"><b>Style :</b></label>
                                        <input type="text" id="txtstyle" name="txtstyle"
                                            class="form-control form-control-sm">
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label"><b>Brand :</b></label>
                                        <input type="text" id="txtbrand" name="txtbrand"
                                            class="form-control form-control-sm">
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label"><b>Product Type :</b></label>
                                        <select class="form-control form-control-sm select2bs4" id="cbo_prod"
                                            name="cbo_prod">
                                            <option selected disabled>Pilih Product Type</option>
                                            @foreach ($data_product as $dp)
                                                <option value="{{ $dp->isi }}">{{ $dp->tampil }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="row g-3 mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label"><b>Request By :</b></label>
                                        <select class="form-control form-control-sm select2bs4" id="cbo_req"
                                            name="cbo_req">
                                            <option selected disabled>Request By</option>
                                            @foreach ($data_request as $dr)
                                                <option value="{{ $dr->isi }}">{{ $dr->tampil }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label"><b>Request Date :</b></label>
                                        <input type="date" id="req_date" name="req_date"
                                            class="form-control form-control-sm" value="{{ date('Y-m-d') }}">
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label"><b>Due Date :</b></label>
                                        <input type="date" id="due_date" name="due_date"
                                            class="form-control form-control-sm" value="{{ date('Y-m-d') }}">
                                    </div>
                                </div>

                                <div class="row g-3 mb-3">
                                    <div class="col-md-8">
                                        <label for="picture"><small><b>Picture :</b></small></label>
                                        <input type="file" id="picture" name="picture"
                                            class="form-control form-control-sm" accept="image/*"
                                            onchange="previewImage(event)">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label"><b>Total SMV :</b></label>
                                        <input type="text" id ="total_smv" class="form-control form-control-sm"
                                            value="0" readonly>
                                    </div>
                                </div>
                            </div>

                            <!-- PREVIEW KANAN -->
                            <div class="col-md-4">
                                <div class="preview-wrapper">
                                    <div class="preview-box">
                                        <img id="imgPreview" src="" class="preview-img"
                                            onclick="openPreview(this)">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- DETAIL VALUE -->
                        <div class="col-md-6">
                            <div class="detail-box">
                                <div class="detail-title">Detail Operational Breakdown</div>

                                <div class="row g-3">
                                    <div class="table-responsive">
                                        <table id="datatable_modal"
                                            class="table table-bordered table-hover align-middle text-nowrap w-100">
                                            <thead class="bg-sb">
                                                <tr>
                                                    <th class="text-center">Act</th>
                                                    <th class="text-center">Picture</th>
                                                    <th class="text-center">Name</th>
                                                    <th class="text-center">Count Process</th>
                                                    <th class="text-center">Total SMV</th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Summary Operational -->
                        <div class="col-md-6">
                            <div class="detail-box">
                                <div class="detail-title">Summary Operational Breakdown</div>

                                <div class="row g-3">
                                    <div class="table-responsive">
                                        <table id="datatable_summary"
                                            class="table table-bordered table-hover align-middle text-nowrap w-100">
                                            <thead class="bg-sb">
                                                <tr>
                                                    <th class="text-center">Picture</th>
                                                    <th class="text-center">Name</th>
                                                    <th class="text-center">Process</th>
                                                    <th class="text-center">Class</th>
                                                    <th class="text-center">SMV</th>
                                                    <th class="text-center">Machine</th>
                                                    <th class="text-center">Remark</th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="modal-footer d-flex justify-content-between align-items-center">

                    <!-- Status -->
                    <div class="d-flex align-items-center gap-2">
                        <label class="mb-0 fs-6"><b>Status :</b></label>
                        <select class="form-select form-select-sm fs-6" id="stat" name="stat"
                            style="width: 150px;">
                            <option value="DRAFT" selected>DRAFT</option>
                            <option value="DONE">DONE</option>
                        </select>
                    </div>

                    <!-- Button -->
                    <div>
                        <button type="button" class="btn btn-success btn-sm" id="saveButton"
                            onclick="save_op_breakdown();">
                            Save
                        </button>

                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                            Cancel
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </div>




    <!-- Edit Modal -->
    <div class="modal fade" id="EditModal" tabindex="-1" aria-labelledby="EditModalLabel" aria-hidden="true"
        data-bs-backdrop="static">
        <div class="modal-dialog modal-fullscreen-width"> <!-- ubah modal-lg ke modal-sm/md sesuai kebutuhan -->
            <div class="modal-content">
                <div class="modal-header bg-sb text-white modal-header-sm">
                    <h5 class="modal-title" id="EditModalLabel">Edit Operational Breakdown</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">

                    <!-- DETAIL PROCESS -->
                    <div class="detail-box">
                        <div class="detail-title">Detail Style</div>
                        <input type="hidden" id="id_c" name="id_c" class="form-control form-control-sm">
                        <div class="row">
                            <!-- FORM KIRI -->
                            <div class="col-md-8">
                                <div class="row g-3 mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label"><b>Style :</b></label>
                                        <input type="text" id="txt_ed_style" name="txt_ed_style"
                                            class="form-control form-control-sm">
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label"><b>Brand :</b></label>
                                        <input type="text" id="txt_ed_brand" name="txt_ed_brand"
                                            class="form-control form-control-sm">
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label"><b>Product Type :</b></label>
                                        <select class="form-control form-control-sm select2bs4" id="cbo_ed_prod"
                                            name="cbo_ed_prod">
                                            <option selected disabled>Pilih Product Type</option>
                                            @foreach ($data_product as $dp)
                                                <option value="{{ $dp->isi }}">{{ $dp->tampil }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="row g-3 mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label"><b>Request By :</b></label>
                                        <select class="form-control form-control-sm select2bs4" id="cbo_ed_req"
                                            name="cbo_ed_req">
                                            <option selected disabled>Request By</option>
                                            @foreach ($data_request as $dr)
                                                <option value="{{ $dr->isi }}">{{ $dr->tampil }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label"><b>Request Date :</b></label>
                                        <input type="date" id="req_ed_date" name="req_ed_date"
                                            class="form-control form-control-sm" value="{{ date('Y-m-d') }}">
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label"><b>Due Date :</b></label>
                                        <input type="date" id="due_ed_date" name="due_ed_date"
                                            class="form-control form-control-sm" value="{{ date('Y-m-d') }}">
                                    </div>
                                </div>

                                <div class="row g-3 mb-3">
                                    <div class="col-md-8">
                                        <label for="edpicture"><b>Picture :</b></label>
                                        <input type="file" id="edpicture" name="edpicture"
                                            class="form-control form-control-sm" accept="image/*"
                                            onchange="previewImage(event)">
                                    </div>
                                    <!-- Hidden input untuk nama file lama -->
                                    <input type="hidden" id="old_picture" name="old_picture">
                                    <div class="col-md-4">
                                        <label class="form-label"><b>Total SMV :</b></label>
                                        <input type="text" id ="total_edit_smv" class="form-control form-control-sm"
                                            value="0" readonly>
                                    </div>
                                </div>
                            </div>

                            <!-- PREVIEW KANAN -->
                            <div class="col-md-4">
                                <div class="preview-wrapper">
                                    <div class="preview-box">
                                        <img id="imgPreviewEdit" src="" class="preview-img"
                                            onclick="openPreview(this)">
                                    </div>
                                    <small class="text-muted">Gambar lama ditampilkan. Pilih file baru untuk
                                        mengganti.</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- DETAIL VALUE -->
                        <div class="col-md-6">
                            <div class="detail-box">
                                <div class="detail-title">Detail Operational Breakdown</div>
                                <div class="row g-3">
                                    <div class="table-responsive">
                                        <table id="datatable_edit_modal"
                                            class="table table-bordered table-hover align-middle text-nowrap w-100">
                                            <thead class="bg-sb">
                                                <tr>
                                                    <th class="text-center">Act</th>
                                                    <th class="text-center">Picture</th>
                                                    <th class="text-center">Name</th>
                                                    <th class="text-center">Count Process</th>
                                                    <th class="text-center">Total SMV</th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Summary Operational -->
                        <div class="col-md-6">
                            <div class="detail-box">
                                <div class="detail-title">Summary Operational Breakdown</div>

                                <div class="row g-3">
                                    <div class="table-responsive">
                                        <table id="datatable_edit_summary"
                                            class="table table-bordered table-hover align-middle text-nowrap w-100">
                                            <thead class="bg-sb">
                                                <tr>
                                                    <th class="text-center">Picture</th>
                                                    <th class="text-center">Name</th>
                                                    <th class="text-center">Process</th>
                                                    <th class="text-center">Class</th>
                                                    <th class="text-center">SMV</th>
                                                    <th class="text-center">Machine</th>
                                                    <th class="text-center">Remark</th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="modal-footer d-flex justify-content-between align-items-center">

                    <!-- Status -->
                    <div class="d-flex align-items-center gap-2">
                        <label class="mb-0 fs-6"><b>Status :</b></label>
                        <select class="form-select form-select-sm fs-6" id="stat_ed" name="stat_ed"
                            style="width: 150px;">
                            <option value="DRAFT" selected>DRAFT</option>
                            <option value="DONE">DONE</option>
                        </select>
                    </div>

                    <!-- Button -->
                    <div>
                        <button type="button" class="btn btn-primary btn-sm" id="saveButton"
                            onclick="update_op_breakdown();">
                            Update
                        </button>

                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                            Cancel
                        </button>
                    </div>

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
    <script src="{{ asset('plugins/datatables-rowsgroup/dataTables.rowsGroup.js') }}"></script>
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

        function reset_modal() {
            selectedProcessIds = [];

            // Reset DataTable summary
            if (datatable_summary) {
                datatable_summary.clear().draw();
            }

            const today = new Date().toISOString().split('T')[0];
            $('#req_date').val(today);
            $('#due_date').val(today);

            $('#txtstyle').val('');
            $('#txtbrand').val('');
            $('#cbo_prod').prop('selectedIndex', 0).trigger('change');
            $('#cbo_req').prop('selectedIndex', 0).trigger('change');
            $('#picture').val('');


            // 2. uncheck semua checkbox di modal
            $('#datatable_modal .row-check').prop('checked', false);

            // Reset image preview
            const imgPreview = document.getElementById('imgPreview');
            imgPreview.src = '';
            imgPreview.style.display = 'none';
            dataTableModalReload();
            dataTableSummaryReload();
        }


        $(document).ready(function() {
            $('#CreateModal').on('show.bs.modal', function() {

                $('#cbo_prod').select2({
                    theme: 'bootstrap4',
                    dropdownParent: $('#CreateModal'),
                    width: '100%'
                });

                $('#cbo_req').select2({
                    theme: 'bootstrap4',
                    dropdownParent: $('#CreateModal'),
                    width: '100%'
                });
                reset_modal();
            });
        })

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

        function dataTableModalReload() {
            datatable_modal.ajax.reload();
        }

        function updateSelectedCountModal() {
            let count = selectedProcessIds.length;
            let info = $('#datatable_modal_info');

            if (!info.length) return;

            if (!$('#dt-modal-selected-info').length) {
                info.append(`
            <span id="dt-modal-selected-info"
                  class="ms-3 text-primary fw-semibold">
                | Selected: <span>${count}</span>
            </span>
        `);
            } else {
                $('#dt-modal-selected-info span').text(count);
            }
        }


        // DataTable Modal
        let datatable_modal = $("#datatable_modal").DataTable({
            ordering: false,
            processing: true,
            serverSide: false,
            paging: false,
            searching: true,
            scrollY: "300px",
            scrollCollapse: false,
            scrollX: true,
            ajax: {
                url: '{{ route('show_modal_proses_breakdown_new') }}',
            },
            columns: [{
                    data: null,
                    className: 'text-center align-middle',
                    render: function(data, type, row) {
                        return `<input type="checkbox" class="row-check"
                        data-id_part_process="${row.id_part_process}">`;
                    }
                },
                {
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
                }
            ],
            initComplete: function() {
                selectedProcessIds = []; // reset
                updateSelectedCountModal(); // ✅ TAMPIL 0
            }

        });

        let selectedProcessIds = [];

        $('#datatable_modal tbody').on('change', '.row-check', function() {
            let rowData = datatable_modal.row($(this).closest('tr')).data();
            if (!rowData) return;

            let id = rowData.id_part_process;
            if (this.checked) {
                // tambah kalau belum ada
                if (!selectedProcessIds.includes(id)) {
                    selectedProcessIds.push(id);
                }
            } else {
                // hapus kalau di-uncheck
                selectedProcessIds = selectedProcessIds.filter(x => x !== id);
            }
            console.log(selectedProcessIds); // DEBUG
            updateSelectedCountModal(); // 🔥 UPDATE INFO
            dataTableSummaryReload();
        });


        function dataTableSummaryReload() {
            datatable_summary.ajax.reload();
        }

        // datatable_summary
        let datatable_summary = $("#datatable_summary").DataTable({
            ordering: false,
            processing: true,
            serverSide: false,
            paging: false,
            searching: true,
            scrollY: "300px",
            scrollCollapse: false,
            scrollX: true, // ⬅️ koma WAJIB

            ajax: {
                url: '{{ route('show_modal_summary_breakdown') }}',
                type: 'GET',
                data: function(d) {
                    d.ids = selectedProcessIds; // ⬅️ ARRAY ID
                }
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
                    data: 'nm_process'
                },
                {
                    className: 'text-center align-middle',
                    data: 'class'
                },
                {
                    className: 'text-center align-middle',
                    data: 'smv'
                },
                {
                    className: 'text-center align-middle',
                    data: 'machine_type'
                },
                {
                    className: 'text-center align-middle',
                    data: 'remark'
                }
            ],
            rowsGroup: [
                0, 1 // Adjust this index to the correct column (zero-based)
            ],
            drawCallback: function() {
                let totalSMV = 0;
                this.api().rows({
                    page: 'current'
                }).data().each(function(row) {
                    totalSMV += parseFloat(row.smv) || 0;
                });
                $('#total_smv').val(totalSMV.toFixed(3));
            }
        });

        function save_op_breakdown() {
            let style = $('#txtstyle').val();
            let brand = $('#txtbrand').val();
            let cbo_prod = $('#cbo_prod').val();
            let cbo_req = $('#cbo_req').val();
            let req_date = $('#req_date').val();
            let due_date = $('#due_date').val();
            let stat = $('#stat').val();
            let picture = $('#picture')[0].files[0]; // file gambar

            // Disable the Save button to prevent multiple clicks
            let $btn = $('#saveButton'); // Add id="saveButton" to your Save button
            $btn.prop('disabled', true);

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
            formData.append('style', style);
            formData.append('brand', brand); // tambahkan gambar
            formData.append('cbo_prod', cbo_prod);
            formData.append('cbo_req', cbo_req);
            formData.append('req_date', req_date);
            formData.append('due_date', due_date);
            formData.append('stat', stat);
            formData.append('picture', picture);

            // Tambahkan ID checklist ke FormData
            selectedProcessIds.forEach(id => {
                formData.append('ids[]', id);
            });


            $.ajax({
                type: "POST",
                url: '{{ route('IE_save_op_breakdown') }}',
                data: formData,
                processData: false, // WAJIB → jangan diubah!
                contentType: false, // WAJIB → biar multipart/form-data
                success: function(response) {
                    // Reset form dulu
                    reset_modal();

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
                    dataTableModalReload();
                    dataTableSummaryReload();
                },
                complete: function() {
                    // Re-enable the Save button after request completes
                    $btn.prop('disabled', false);
                    dataTableReload();
                    dataTableModalReload();
                    dataTableSummaryReload();
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
                url: '{{ route('IE_proses_op_breakdown') }}',
                data: function(d) {
                    d.tgl_awal = $('#tgl_awal').val();
                    d.tgl_akhir = $('#tgl_akhir').val();
                },
            },

            columns: [{
                    className: 'text-center align-middle',
                    data: 'tgl_trans_fix'
                },
                {
                    data: 'picture',
                    className: 'text-center align-middle',
                    render: function(data) {
                        if (!data) return 'No Photo';
                        const imageUrl = `/nds_wip/public/storage/gambar_op_breakdown/${data}`;
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
                    data: 'style'
                },
                {
                    className: 'text-center align-middle',
                    data: 'brand'
                },
                {
                    className: 'text-center align-middle',
                    data: 'product_group'
                },
                {
                    className: 'text-center align-middle',
                    data: 'tot_smv'
                },
                {
                    className: 'text-center align-middle',
                    data: 'request_by'
                },
                {
                    className: 'text-center align-middle',
                    data: 'request_date_fix'
                },
                {
                    className: 'text-center align-middle',
                    data: 'due_date_fix'
                },
                {
                    className: 'text-center align-middle',
                    data: 'status'
                },
                {
                    className: 'text-center align-middle',
                    data: 'created_by'
                },
                {
                    className: 'text-center align-middle',
                    data: 'tgl_create_fix'
                },
                {
                    data: 'id_op_breakdown',
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

        function editData(id_c) {
            $("#id_c").val(id_c);

            $.ajax({
                url: '{{ route('IE_show_op_breakdown') }}',
                method: 'GET',
                data: {
                    id_c: id_c
                },
                dataType: 'json',
                success: function(res) {

                    $("#txt_ed_style").val(res.style);
                    $("#txt_ed_brand").val(res.brand);
                    $("#cbo_ed_prod").val(res.id_product).trigger('change');
                    $("#cbo_ed_req").val(res.request_by).trigger('change');
                    $("#req_ed_date").val(res.request_date);
                    $("#due_ed_date").val(res.due_date);
                    $("#stat_ed").val(res.status).trigger('change');
                    $("#old_picture").val(res.picture || '');

                    // === PREVIEW GAMBAR LAMA (PAKE IMG TAG) ===
                    if (res.picture) {
                        const imageUrl =
                            `/nds_wip/public/storage/gambar_op_breakdown/${res.picture}?t=${new Date().getTime()}`;
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

        function dataTableEditModalReload() {
            datatable_edit_modal.ajax.reload();
        }

        function updateSelectedCount() {
            let count = selectedProcessIds.length;
            $('#selectedCount').text(count);

            let info = $('#datatable_edit_modal_info');
            if (!$('#dt-selected-info').length) {
                info.append(
                    `<span id="dt-selected-info" class="ms-3 text-primary fw-semibold">
                | Selected: <span id="selectedCount">${count}</span>
            </span>`
                );
            } else {
                $('#dt-selected-info span').text(count);
            }
        }


        function loadDatatableEdit(id_c) {

            if ($.fn.DataTable.isDataTable('#datatable_edit_modal')) {
                $('#datatable_edit_modal').DataTable().destroy();
            }

            selectedProcessIds = []; // reset setiap buka edit

            let datatable_edit_modal = $("#datatable_edit_modal").DataTable({
                ordering: false,
                processing: true,
                serverSide: false,
                paging: false,
                searching: true,
                scrollY: "300px",
                scrollCollapse: false,
                scrollX: true,


                ajax: {
                    url: '{{ route('IE_show_op_breakdown_edit') }}',
                    data: {
                        id_c: id_c
                    }
                },

                columns: [{
                        data: null,
                        className: "text-center",
                        render: function(data, type, row) {
                            let checked = row.selected == 1 ? 'checked' : '';
                            return `
                        <input type="checkbox"
                               class="row-check"
                               data-id_part_process="${row.id_part_process}"
                               ${checked}>
                    `;
                        }
                    },
                    {
                        data: 'picture',
                        className: 'text-center',
                        render: function(data) {
                            if (!data) return 'No Photo';
                            return `<img src="/nds_wip/public/storage/gambar_part_process/${data}"
                            class="img-thumbnail" onclick="openPreview(this)" style="max-height: 100px; max-width: 100%; object-fit: contain; cursor: pointer;">`;
                        }
                    },
                    {
                        data: 'nm_part_process',
                        className: 'text-center'
                    },
                    {
                        data: 'tot_process',
                        className: 'text-center'
                    },
                    {
                        data: 'tot_smv',
                        className: 'text-center'
                    }
                ],

                initComplete: function(settings, json) {
                    json.data.forEach(row => {
                        if (row.selected == 1) {
                            selectedProcessIds.push(row.id_part_process);
                        }
                    });
                    console.log('INIT selected:', selectedProcessIds);

                    // 🔥 reload summary SETELAH init
                    datatable_edit_summary.ajax.reload();
                    updateSelectedCount();
                }
            });

            $('#datatable_edit_modal tbody')
                .off('change')
                .on('change', '.row-check', function() {

                    let rowData = datatable_edit_modal
                        .row($(this).closest('tr'))
                        .data();
                    if (!rowData) return;
                    let id = rowData.id_part_process;
                    if (this.checked) {
                        if (!selectedProcessIds.includes(id)) {
                            selectedProcessIds.push(id);
                        }
                    } else {
                        selectedProcessIds = selectedProcessIds.filter(x => x !== id);
                    }
                    console.log('CHANGE selected:', selectedProcessIds);
                    datatable_edit_summary.ajax.reload();
                    updateSelectedCount();
                });
        }


        function dataTableEditSummaryReload() {
            datatable_edit_summary.ajax.reload();
        }

        // datatable_edit_summary
        let datatable_edit_summary = $("#datatable_edit_summary").DataTable({
            ordering: false,
            processing: true,
            serverSide: false,
            paging: false,
            searching: true,
            scrollY: "300px",
            scrollCollapse: false,
            scrollX: true, // ⬅️ koma WAJIB

            ajax: {
                url: '{{ route('show_modal_summary_breakdown') }}',
                type: 'GET',
                data: function(d) {
                    d.ids = selectedProcessIds; // ⬅️ ARRAY ID
                }
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
                    data: 'nm_process'
                },
                {
                    className: 'text-center align-middle',
                    data: 'class'
                },
                {
                    className: 'text-center align-middle',
                    data: 'smv'
                },
                {
                    className: 'text-center align-middle',
                    data: 'machine_type'
                },
                {
                    className: 'text-center align-middle',
                    data: 'remark'
                }
            ],
            rowsGroup: [
                0, 1 // Adjust this index to the correct column (zero-based)
            ],
            drawCallback: function() {
                let totalSMV = 0;
                this.api().rows({
                    page: 'current'
                }).data().each(function(row) {
                    totalSMV += parseFloat(row.smv) || 0;
                });
                $('#total_edit_smv').val(totalSMV.toFixed(3));
            }
        });

        function update_op_breakdown() {
            let formData = new FormData();

            formData.append("id_c", $("#id_c").val());
            formData.append("style", $("#txt_ed_style").val());
            formData.append("brand", $("#txt_ed_brand").val());
            formData.append("cbo_prod", $("#cbo_ed_prod").val());
            formData.append("cbo_req", $("#cbo_ed_req").val());
            formData.append("req_date", $("#req_ed_date").val());
            formData.append("due_date", $("#req_ed_date").val());
            formData.append("stat", $("#stat_ed").val());

            formData.append('picture', picture);

            // Picture handling
            let newFile = $("#edpicture")[0].files[0];
            if (newFile) {
                formData.append("picture", newFile);
            }
            formData.append("old_picture", $("#old_picture").val());

            // Checkbox values (id_process)
            let selectedIDs = [];
            $("#datatable_edit_modal .row-check:checked").each(function() {
                selectedIDs.push($(this).data("id_part_process"));
            });

            formData.append("selected_ids", JSON.stringify(selectedIDs));

            // CSRF
            formData.append("_token", "{{ csrf_token() }}");

            $.ajax({
                url: "{{ route('IE_update_op_breakdown') }}",
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {

                    Swal.fire({
                        icon: "success",
                        title: "Updated!",
                        text: "Operational Breakdown berhasil diupdate."
                    });
                    dataTableReload();
                    datatable_edit_modal.ajax.reload();
                    dataTableEditSummaryReload();
                },
                error: function(xhr) {
                    alert(xhr.responseText);
                }
            });
        }
    </script>
@endsection
