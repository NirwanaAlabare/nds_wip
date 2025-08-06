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
                        <select name="no_form" id="no_form" class="form-control select2bs4form" style="width: 100%;"  onchange="getFormGroupList();getFormStockerList();">
                            <option value="">Pilih Form</option>
                        </select>
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
                            $('#no_form').append('<option value="' + value.form_cut_id + '">' + value.no_form + '</option>');
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

        function getFormGroupList() {
            document.getElementById("loading").classList.remove("d-none");

            $.ajax({
                type: "get",
                url: "{{ route('get-form-group') }}",
                data: {
                    form_cut_id: $("#no_form").val()
                },
                dataType: "json",
                success: function (response) {
                    if (response) {
                        $('#form_group').empty();
                        $('#form_group').append('<option value="">Pilih Form Group</option>');
                        $.each(response, function (key, value) {
                            $('#form_group').append('<option value="' + value.group_stocker + '">' + value.group_stocker + " - " + value.shade + '</option>');
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
                    form_group: $('#form_group').val()
                },
                dataType: "json",
                success: function (response) {
                    if (response) {
                        $('#form_stocker').empty();
                        $('#form_stocker').append('<option value="">Pilih Form Stocker</option>');
                        $.each(response, function (key, value) {
                            $('#form_stocker').append('<option value="' + value.stocker_ids + '">' + value.id_qr_stocker + ' || Size \'' + value.size + '\' || Ratio ' + value.ratio + '</option>');
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
                    no_form: $('#no_form option:selected').text(),
                    form_group: $('#form_group').val(),
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

        function resetRedundantStocker() {
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
    </script>
@endsection
