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
    </style>
@endsection

@section('content')
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title">
                <i class="fa-solid fa-screwdriver-wrench"></i> Stocker Tools
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <a type="button" class="home-item" data-bs-toggle="modal" data-bs-target="#resetStockerModal">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="text-sb mb-0"><i class="fa-solid fa-gears"></i> Reset Stocker Form</h5>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-4">
                    <a type="button" class="home-item" data-bs-toggle="modal" data-bs-target="#resetStockerIdModal">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="text-sb mb-0"><i class="fa-solid fa-gears"></i> Reset Stocker ID</h5>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-4">
                    <a type="button" class="home-item" onclick="resetRedundantStocker()">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="text-sb mb-0"><i class="fa-solid fa-gears"></i> Reset Redundant Stocker</h5>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-4">
                    <a type="button" class="home-item" onclick="openImportStockerManual()">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="text-sb mb-0"><i class="fa-solid fa-gears"></i> Import Stocker Manual</h5>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-4">
                    <a type="button" class="home-item" data-bs-toggle="modal" data-bs-target="#rearrangeGroup">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="text-sb mb-0"><i class="fa-solid fa-gears"></i> Rearrange Stocker Group</h5>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-4">
                    <a type="button" class="home-item" data-bs-toggle="modal" data-bs-target="#recalculateStockerTransaction">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="text-sb mb-0"><i class="fa-solid fa-gears"></i> Recalculate Stocker</h5>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-4">
                    <a type="button" class="home-item" data-bs-toggle="modal" data-bs-target="#restoreStockerLogModal">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="text-sb mb-0"><i class="fa-solid fa-gears"></i> Restore Stocker Log</h5>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-4">
                    <a type="button" class="home-item" data-bs-toggle="modal" data-bs-target="#undoStockerAdditionalModal">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="text-sb mb-0"><i class="fa-solid fa-gears"></i> Undo Stocker Additional</h5>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Reset Stocker -->
    <div class="modal fade" id="resetStockerModal" tabindex="-1" aria-labelledby="resetStockerModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-sb">
                    <h1 class="modal-title fs-5" id="resetStockerModalLabel">Reset Stocker Form</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">No. Form</label>
                        <select name="no_form" id="no_form" class="form-control select2bs4form" style="width: 100%;"  onchange="getFormGroupList();getFormStockerList();setFormType();">
                            <option value="">Pilih Form</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Form Type</label>
                        <input type="text" class="form-control" id="form_type" id="form_type" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Group</label>
                        <select name="form_group" id="form_group" class="form-control select2bs4form" style="width: 100%;" onchange="getFormStockerList()">
                            <option value="">Pilih Form Group</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Stocker</label>
                        <select name="form_stocker" id="form_stocker" class="form-control select2bs4form" style="width: 100%;">
                            <option value="">Pilih Form Stocker</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="fa fa-times"></i> Batal</button>
                    <button type="button" class="btn btn-sb" onclick="resetStockerForm()"><i class="fa fa-rotate-left"></i> Reset</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Reset Stocker By id -->
    <div class="modal fade" id="resetStockerIdModal" tabindex="-1" aria-labelledby="resetStockerIdModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-sb">
                    <h1 class="modal-title fs-5" id="resetStockerIdModalLabel">Reset Stocker ID</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Stocker ID</label>
                        <textarea class="form-control" name="stocker_ids" id="stocker_ids" cols="30" rows="10"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="fa fa-times"></i> Batal</button>
                    <button type="button" class="btn btn-sb" onclick="resetStockerId()"><i class="fa fa-rotate-left"></i> Reset</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Restore Stocker Log -->
    <div class="modal fade" id="restoreStockerLogModal" tabindex="-1" aria-labelledby="restoreStockerLogModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-sb">
                    <h1 class="modal-title fs-5" id="restoreStockerLogModalLabel">Restore Stocker Log</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Table</label>
                        <select name="table" id="table" class="form-control select2bs4restorestockerlog" style="width: 100%;">
                            <option value="">Pilih Table</option>
                            <option value="stocker_input">Stocker</option>
                            <option value="dc_in_input">DC In Input</option>
                            <option value="secondary_in_input">Secondary In Input</option>
                            <option value="secondary_inhouse_input">Secondary Inhouse Input</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Log</label>
                        <textarea class="form-control" name="stocker_log" id="stocker_log" cols="30" rows="10"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="fa fa-times"></i> Batal</button>
                    <button type="button" class="btn btn-sb" onclick="restoreStockerLog()"><i class="fa fa-rotate-left"></i> Restore</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Import Manual --}}
    <div class="modal fade" id="importExcel" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form method="post" action="{{ route('import-stocker-manual') }}" enctype="multipart/form-data" onsubmit="submitImportStockerManual(this, event)">
                <div class="modal-content">
                    <div class="modal-header bg-sb text-light">
                        <h5 class="modal-title" id="exampleModalLabel">Import Stocker Manual</h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        {{ csrf_field() }}
                        <label for="images" class="drop-container" id="dropcontainer">
                            <input type="file" name="file" required="required">
                        </label>
                        <a href="{{ asset('example/contoh-import-stocker-manual.xlsx') }}" download class="btn btn-sb-secondary btn-sm"><i class="fa fa-solid fa-download"></i> Contoh Excel</a>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="fa fa-window-close" aria-hidden="true"></i> Close</button>
                        <button type="submit" class="btn btn-sb toastsDefaultDanger"><i class="fa fa-thumbs-up" aria-hidden="true"></i> Import</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Rearrange Group -->
    <div class="modal fade" id="rearrangeGroup" tabindex="-1" aria-labelledby="rearrangeGroupLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-sb">
                    <h1 class="modal-title fs-5" id="rearrangeGroupLabel">Rearrange Stocker Group</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Tanggal</label>
                        <input type="date" class="form-control" id="rearrange-date">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="fa fa-times"></i> Batal</button>
                    <button type="button" class="btn btn-sb" onclick="rearrangeStockerGroup()"><i class="fa fa-rotate-left"></i> Rearrange</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Recalculate Stocker -->
    <div class="modal fade" id="recalculateStockerTransaction" tabindex="-1" aria-labelledby="recalculateStockerTransactionLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-sb">
                    <h1 class="modal-title fs-5" id="recalculateStockerTransactionLabel">Recalculate Stocker Transaction</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">No. Form</label>
                        <select name="no_form_recalc" id="no_form_recalc" class="form-control select2bs4recalc" style="width: 100%;">
                            <option value="">Pilih Form</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="fa fa-times"></i> Batal</button>
                    <button type="button" class="btn btn-sb" onclick="recalculateStockerTransaction()"><i class="fa fa-rotate-left"></i> Recalculate</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Undo Stocker Additional -->
    <div class="modal fade" id="undoStockerAdditionalModal" tabindex="-1" aria-labelledby="undoStockerAdditionalModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-sb">
                    <h1 class="modal-title fs-5" id="undoStockerAdditionalModalLabel">Undo Stocker Addtiional</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">No. Form</label>
                        <select name="no_form_undo_stocker_additional" id="no_form_undo_stocker_additional" class="form-control select2bs4undostockeradditional" style="width: 100%;"  onchange="setFormType('_undo_stocker_additional');">
                            <option value="">Pilih Form</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Form Type</label>
                        <input type="text" class="form-control" id="form_type_undo_stocker_additional" id="form_type_undo_stocker_additional" readonly>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="fa fa-times"></i> Batal</button>
                    <button type="button" class="btn btn-sb" onclick="undoStockerAdditional()"><i class="fa fa-rotate-left"></i> Undo</button>
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
    <script src="{{ asset('plugins/datatables-rowsgroup/dataTables.rowsGroup.js') }}"></script>

    <!-- Select2 -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        $('.select2').select2({
            theme: 'bootstrap4'
        });
        $('.select2bs4').select2({
            theme: 'bootstrap4'
        });
        $('.select2bs4form').select2({
            theme: 'bootstrap4',
            dropdownParent: $('#resetStockerModal')
        });
        $('.select2bs4recalc').select2({
            theme: 'bootstrap4',
            dropdownParent: $('#recalculateStockerTransaction')
        });
        $('.select2bs4restorestockerlog').select2({
            theme: 'bootstrap4',
            dropdownParent: $('#restoreStockerLogModal')
        });
        $('.select2bs4undostockeradditional').select2({
            theme: 'bootstrap4',
            dropdownParent: $('#undoStockerAdditionalModal')
        });

        $(document).ready(function () {
            getFormList();
        });

        function getFormList() {
            document.getElementById("loading").classList.remove("d-none");

            $.ajax({
                type: "get",
                url: "{{ route('get-no-form-cut') }}",
                dataType: "json",
                success: function (response) {
                    if (response) {
                        $('#no_form').empty();
                        $('#no_form').append('<option value="">Pilih Form</option>');
                        $.each(response, function (key, value) {
                            $('#no_form').append('<option value="' + value.form_cut_id + '" data-type="'+value.type+'">' + value.no_form + '</option>');
                        });

                        $('#no_form_recalc').empty();
                        $('#no_form_recalc').append('<option value="">Pilih Form</option>');
                        $.each(response, function (key, value) {
                            $('#no_form_recalc').append('<option value="' + value.form_cut_id + '" data-type="'+value.type+'">' + value.no_form + '</option>');
                        });

                        $('#no_form_undo_stocker_additional').empty();
                        $('#no_form_undo_stocker_additional').append('<option value="">Pilih Form</option>');
                        $.each(response, function (key, value) {
                            $('#no_form_undo_stocker_additional').append('<option value="' + value.form_cut_id + '" data-type="'+value.type+'">' + value.no_form + '</option>');
                        });
                    }

                    document.getElementById("form_type").value = "";

                    document.getElementById("loading").classList.add("d-none");
                },
                error: function (xhr, status, error) {
                    console.error(xhr);

                    document.getElementById("loading").classList.add("d-none");
                }
            });
        }

        function setFormType(suffix = null) {
            const formType = $('#no_form'+suffix+' option:selected').attr('data-type');
            $('#form_type'+suffix+'').val(formType);
        }

        function getFormGroupList() {
            document.getElementById("loading").classList.remove("d-none");

            $.ajax({
                type: "get",
                url: "{{ route('get-form-group') }}",
                data: {
                    form_cut_id: $("#no_form").val(),
                    form_group: $('#form_group').val() ? $('#form_group').val() : null,
                    form_type: $('#no_form option:selected').attr('data-type')
                },
                dataType: "json",
                success: function (response) {
                    if (response) {
                        $('#form_group').empty();
                        $('#form_group').append('<option value="">Pilih Form Group</option>');
                        $.each(response, function (key, value) {
                            $('#form_group').append('<option value="' + (value.group_stocker ? value.group_stocker : "") + '">' + (value.group_stocker ? value.group_stocker : '/') + " - " + (value.shade ? value.shade : '/') + '</option>');
                        });

                        document.getElementById("loading").classList.add("d-none");
                    }
                },
                error: function (xhr, status, error) {
                    console.error(xhr);

                    document.getElementById("loading").classList.add("d-none");
                }
            });
        }

        function getFormStockerList() {
            document.getElementById("loading").classList.remove("d-none");

            $.ajax({
                type: "get",
                url: "{{ route('get-form-stocker') }}",
                data: {
                    form_cut_id: $('#no_form').val(),
                    form_group:$('#form_group').val() ? $('#form_group').val() : null,
                    form_type: $('#no_form option:selected').attr('data-type')
                },
                dataType: "json",
                success: function (response) {
                    if (response) {
                        $('#form_stocker').empty();
                        $('#form_stocker').append('<option value="">Pilih Form Stocker</option>');
                        $.each(response, function (key, value) {
                            $('#form_stocker').append('<option value="' + value.stocker_ids + '">' + value.id_qr_stocker + ' || Size \'' + value.size + '\' || Ratio ' + (value.ratio ? value.ratio : null) + '</option>');
                        });
                    }

                    document.getElementById("loading").classList.add("d-none");
                },
                error: function (xhr, status, error) {
                    console.error(xhr);

                    document.getElementById("loading").classList.add("d-none");
                }
            });
        }

        function resetStockerForm() {
            Swal.fire({
                title: 'Please Wait...',
                html: 'Fixing Data...  <br><br> <b>0</b>s elapsed...',
                didOpen: () => {
                    Swal.showLoading();

                    let estimatedTime = 0;
                    const estimatedTimeElement = Swal.getPopup().querySelector("b");
                    estimatedTimeInterval = setInterval(() => {
                        estimatedTime++;
                        estimatedTimeElement.textContent = estimatedTime;
                    }, 1000);
                },
                allowOutsideClick: false,
            });

            $.ajax({
                type: "post",
                url: "{{ route('reset-stocker-form') }}",
                data: {
                    form_cut_id: $('#no_form').val(),
                    form_type: $('#no_form option:selected').attr('data-type'),
                    no_form: $('#no_form option:selected').text(),
                    form_group: $('#form_group').val() ? $('#form_group').val() : null,
                    form_stocker: $('#form_stocker').val()
                },
                dataType: "json",
                success: function (response) {
                    if (response.status == 200) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            html: response.message,
                            showCancelButton: false,
                            showConfirmButton: true,
                            confirmButtonText: 'Oke',
                            confirmButtonColor: "#082149",
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            html: response && response.message ? response.message : 'Terjadi kesalahan',
                            showCancelButton: false,
                            showConfirmButton: true,
                            confirmButtonText: 'Oke',
                            confirmButtonColor: "#082149",
                        });
                    }
                }
            });
        }

        function resetStockerId() {
            Swal.fire({
                title: 'Please Wait...',
                html: 'Fixing Data...  <br><br> <b>0</b>s elapsed...',
                didOpen: () => {
                    Swal.showLoading();

                    let estimatedTime = 0;
                    const estimatedTimeElement = Swal.getPopup().querySelector("b");
                    estimatedTimeInterval = setInterval(() => {
                        estimatedTime++;
                        estimatedTimeElement.textContent = estimatedTime;
                    }, 1000);
                },
                allowOutsideClick: false,
            });

            $.ajax({
                type: "post",
                url: "{{ route('reset-stocker-id') }}",
                data: {
                    stocker_ids: $('#stocker_ids').val(),
                },
                dataType: "json",
                success: function (response) {
                    if (response.status == 200) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            html: response.message,
                            showCancelButton: false,
                            showConfirmButton: true,
                            confirmButtonText: 'Oke',
                            confirmButtonColor: "#082149",
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            html: response && response.message ? response.message : 'Terjadi kesalahan',
                            showCancelButton: false,
                            showConfirmButton: true,
                            confirmButtonText: 'Oke',
                            confirmButtonColor: "#082149",
                        });
                    }
                }
            });
        }

        function restoreStockerLog() {
            Swal.fire({
                title: 'Please Wait...',
                html: 'Fixing Data...  <br><br> <b>0</b>s elapsed...',
                didOpen: () => {
                    Swal.showLoading();

                    let estimatedTime = 0;
                    const estimatedTimeElement = Swal.getPopup().querySelector("b");
                    estimatedTimeInterval = setInterval(() => {
                        estimatedTime++;
                        estimatedTimeElement.textContent = estimatedTime;
                    }, 1000);
                },
                allowOutsideClick: false,
            });

            $.ajax({
                type: "post",
                url: "{{ route('restore-stocker-log') }}",
                data: {
                    stocker_log: $('#stocker_log').val(),
                },
                dataType: "json",
                success: function (response) {
                    let totalRow = response ? response : 0;
                    Swal.fire({
                        icon: 'info',
                        title: 'INFO',
                        html: totalRow+" Row(s) Affected.",
                        showCancelButton: false,
                        showConfirmButton: true,
                        confirmButtonText: 'Oke',
                        confirmButtonColor: "#082149",
                    });
                },
                error: function (xhr, status, error) {
                    console.error(xhr);

                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        html: 'Terjadi kesalahan',
                        showCancelButton: false,
                        showConfirmButton: true,
                        confirmButtonText: 'Oke',
                        confirmButtonColor: "#082149",
                    });
                }
            });
        }

        function resetRedundantStocker() {
            Swal.fire({
                title: 'Fix Redundant Stocker',
                html: '<span class="text-danger"><b>Critical</b></span> <br> Yakin akan mengubah data stocker?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'UBAH',
                cancelButtonText: 'BATAL',
                confirmButtonColor: "#dc3545"
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Please Wait...',
                        html: 'Fixing Data...  <br><br> <b>0</b>s elapsed...',
                        didOpen: () => {
                            Swal.showLoading();

                            let estimatedTime = 0;
                            const estimatedTimeElement = Swal.getPopup().querySelector("b");
                            estimatedTimeInterval = setInterval(() => {
                                estimatedTime++;
                                estimatedTimeElement.textContent = estimatedTime;
                            }, 1000);
                        },
                        allowOutsideClick: false,
                    });

                    $.ajax({
                        type: "post",
                        url: "{{ route('reset-redundant-stocker') }}",
                        processData: false,
                        contentType: false,
                        xhrFields:
                        {
                            responseType: 'blob'
                        },
                        success: function(res) {
                            if (res) {
                                console.log(res);

                                var blob = new Blob([res], {type: 'application/pdf'});
                                var link = document.createElement('a');
                                link.href = window.URL.createObjectURL(blob);
                                link.download = "Redundant Stocker.pdf";
                                link.click();
                            }

                            window.location.reload();
                        },
                        error: function(jqXHR) {
                            console.log(jqXHR);

                            Swal.fire({
                                icon:'error',
                                title: 'Error',
                                html: 'Terjadi kesalahan.',
                            });
                        }
                    });
                }
            })
        }

        function openImportStockerManual(){
            $('#importExcel').modal('show');
        }

        function submitImportStockerManual(e, evt) {
            document.getElementById("loading").classList.remove("d-none");

            evt.preventDefault();

            clearModified();

            $.ajax({
                url: e.getAttribute('action'),
                type: e.getAttribute('method'),
                data: new FormData(e),
                processData: false,
                contentType: false,
                success: async function(res) {
                    document.getElementById("loading").classList.add("d-none");

                    if (res.status == 200) {
                        console.log(res);

                        e.reset();


                        Swal.fire({
                            icon: 'success',
                            title: 'Data Stocker berhasil diupload',
                            showCancelButton: false,
                            showConfirmButton: true,
                            confirmButtonText: 'Oke',
                            timer: 5000,
                            timerProgressBar: true
                        })

                        $('#importExcel').modal('hide');
                    }
                },
                error: function (jqXHR) {
                    document.getElementById("loading").classList.add("d-none");

                    console.error(jqXHR);
                }
            });
        }

        function rearrangeStockerGroup() {
            Swal.fire({
                title: 'Rearrange Stocker Group',
                html: '<span class="text-danger"><b>Critical</b></span> <br> Yakin akan mengubah data group stocker di tanggal '+$('#rearrange-date').val()+'?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'UBAH',
                cancelButtonText: 'BATAL',
                confirmButtonColor: "#dc3545"
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Please Wait...',
                        html: 'Rearranging Data...',
                        didOpen: () => {
                            Swal.showLoading()
                        },
                        allowOutsideClick: false,
                    });

                    $.ajax({
                        url: '{{ route('rearrange-groups') }}',
                        type: 'post',
                        data: {
                            date : $('#rearrange-date').val()
                        },
                        success: function(res) {
                            console.log("successs", res);

                            location.reload();
                        },
                        error: function(jqXHR) {
                            console.log("error", jqXHR);
                        }
                    });
                }
            });
        }

        function recalculateStockerTransaction() {
            Swal.fire({
                title: 'Recalculate Stocker',
                html: '<span class="text-danger"><b>Critical</b></span> <br> Yakin akan mengubah data transaksi stocker?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'UBAH',
                cancelButtonText: 'BATAL',
                confirmButtonColor: "#dc3545"
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Please Wait...',
                        html: 'Fixing Data...  <br><br> <b>0</b>s elapsed...',
                        didOpen: () => {
                            Swal.showLoading();

                            let estimatedTime = 0;
                            const estimatedTimeElement = Swal.getPopup().querySelector("b");
                            estimatedTimeInterval = setInterval(() => {
                                estimatedTime++;
                                estimatedTimeElement.textContent = estimatedTime;
                            }, 1000);
                        },
                        allowOutsideClick: false,
                    });

                    $.ajax({
                        type: "post",
                        url: "{{ route('recalculate-stocker-transaction') }}",
                        data: {
                            formCutId: $('#no_form_recalc').val(),
                        },
                        dataType: "json",
                        success: function(res) {
                            if (res) {
                                console.log(res);

                                Swal.fire({
                                    icon: 'info',
                                    title: 'Info',
                                    html: 'Proses Selesai.',
                                });
                            }
                        },
                        error: function(jqXHR) {
                            console.log(jqXHR);

                            Swal.fire({
                                icon:'error',
                                title: 'Error',
                                html: 'Terjadi kesalahan.',
                            });
                        }
                    });
                }
            })
        }

        function undoStockerAdditional() {
            $.ajax({
                type: "delete",
                url: "{{ route('undo-stocker-additional') }}",
                data: {
                    'id': $('#no_form_undo_stocker_additional').val()
                },
                dataType: "json",
                success: function (response) {
                    console.log(response)

                    if (response) {
                        Swal.fire({
                            icon: (response.status == 200 ? 'success' : 'error'),
                            title: 'Berhasil',
                            html: response.message,
                        });
                    }
                },
                error: function (jqXHR) {
                    console.error(jqXHR);
                }
            });
        }
    </script>
@endsection
