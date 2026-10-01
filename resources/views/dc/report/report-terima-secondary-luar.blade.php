@extends('layouts.index')

@section('custom-link')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">

    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

    <style>
        div.dataTables_wrapper div.dataTables_processing {
            top: 15%;
        }

        .border-dashed {
            border-style: dashed !important;
        }

        .drop-zone {
            border: 2px dashed #7a7a7a;
            border-radius: 16px;
            padding: 20px;
            background-color: #ffffff;
            transition: all 0.2s ease-in-out;
        }

        .drop-zone.drag-over {
            background-color: #f0f4ff;
            border-color: #0d6efd;
        }
    </style>
@endsection

@section('content')
    <div class="card">
        <div class="card-header bg-sb text-light">
            <h5 class="card-title fw-bold mb-0"><i class="fa-solid fa-file"></i> REPORT TERIMA SECONDARY LUAR </h5>
        </div>
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-end gap-3 mb-3">
                <div class="d-flex align-items-end gap-3 mb-3">
                    <div>
                        <label class="form-label"><small>Tanggal Awal</small></label>
                        <input type="date" class="form-control form-control-sm" id="tgl-awal" name="tgl_awal" value="">
                    </div>
                    <div>
                        <label class="form-label"><small>Tanggal Akhir</small></label>
                        <input type="date" class="form-control form-control-sm" id="tgl-akhir" name="tgl_akhir" value="">
                    </div>
                    <div>
                        <button class="btn btn-primary btn-sm" onclick="datatableReportDC()"><i class="fa fa-search"></i></button>
                    </div>
                </div>
                <div class="d-flex gap-1">
                    <button type="button" class="btn btn-sm btn-sb-secondary mb-3" data-bs-toggle="modal" data-bs-target="#importSecondaryLuarModal" onclick="resetImportPreview()"><i class="fa fa-file-upload"></i> Import</button>
                    <button class="btn btn-sm btn-success mb-3" onclick="exportExcel()"><i class="fa fa-file-excel"></i> Export</button>
                </div>
            </div>
            <div class="table-responsive">
                <table id="datatable-dc-report" class="table table-bordered table w-100">
                    <thead>
                        <tr>
                            <th class="text-center">Tanggal</th>
                            <th class="text-center">No. WS</th>
                            <th class="text-center">Buyer</th>
                            <th class="text-center">Style</th>
                            <th class="text-center">Color</th>
                            <th class="text-center">Size</th>
                            <th class="text-center">Panel</th>
                            <th class="text-center">Part</th>
                            <th class="text-center">Qty</th>
                            <th class="text-center">Dibuat Oleh</th>
                            <th class="text-center">Dibuat Pada</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Import -->
    <div class="modal fade" id="importSecondaryLuarModal" tabindex="-1" aria-labelledby="importSecondaryLuarModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-3 overflow-hidden">
                <form id="importSecondaryLuarForm" enctype="multipart/form-data" class="d-flex flex-column h-100 mb-0">
                    @csrf
                    
                    <!-- Header (Navy / Dark Blue) -->
                    <div class="modal-header text-white py-3" style="background-color: #0d2342;">
                        <h5 class="modal-title fw-bold fs-5" id="importSecondaryLuarModalLabel">Import Terima Secondary Luar</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <!-- Body -->
                    <div class="modal-body p-4">
                        <!-- Drop Zone Box -->
                        <div class="drop-zone mb-3" id="drop-zone">
                            <div class="text-center py-4">
                                <p class="fs-4 fw-bold text-dark mb-2">Drop files here</p>
                                <p class="text-muted fw-semibold mb-3">or</p>
                                <div class="d-flex align-items-center justify-content-center gap-3">
                                    <label for="import-secondary-luar-file" class="btn btn-primary px-4 py-2 rounded-3 mb-0" style="background-color: #0d6efd; font-weight: 500; cursor: pointer;">
                                        Browse...
                                    </label>
                                    <input type="file" id="import-secondary-luar-file" name="file" accept=".xlsx,.xls,.csv" class="d-none">
                                    <span id="file-name-display" class="text-secondary fw-normal">No file selected.</span>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
                            <a href="{{ asset('example/contoh-import-terima-secondary-luar.xlsx') }}" 
                                id="btn-download-template" 
                                class="btn btn-outline-success btn-sm px-3 flex-shrink-0" 
                                download="Contoh_Import_Secondary_Luar.xlsx">
                                    <i class="fa fa-file-excel me-1"></i> Contoh Excel
                            </a>
                            <button type="button" id="btn-empty-preview" class="btn btn-outline-danger btn-sm px-3 flex-shrink-0">
                                <i class="fa fa-trash me-1"></i> Kosongkan Tabel
                            </button>
                        </div>

                        <!-- Alert & Summary -->
                        <div id="import-secondary-luar-error" class="alert alert-danger d-none shadow-sm rounded-3" role="alert"></div>
                        <div id="import-secondary-luar-summary" class="fw-medium text-secondary mb-2 px-1"></div>

                        <!-- Table Preview -->
                        <div class="table-responsive rounded-3 border shadow-sm">
                            <table class="table table-bordered table-hover align-middle mb-0" style="font-size: 0.875rem;">
                                <thead class="table-dark sticky-top">
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>No. WS</th>
                                        <th>Buyer</th>
                                        <th>Style</th>
                                        <th>Color</th>
                                        <th>Size</th>
                                        <th>Panel</th>
                                        <th>Part</th>
                                        <th class="text-end pe-3">Qty</th>
                                    </tr>
                                </thead>
                                <tbody id="import-secondary-luar-preview" class="bg-white">
                                    <tr>
                                        <td colspan="9" class="text-center text-muted py-5">
                                            Pilih file di atas untuk menampilkan preview data.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Footer (Batal & Simpan) -->
                    <div class="modal-footer bg-light border-top py-3 px-4">
                        <div class="d-flex w-100 justify-content-end align-items-center gap-2">
                            <button type="button" class="btn btn-secondary px-4 fw-medium" data-bs-dismiss="modal" id="btn-batal">
                                <i class="fa fa-times me-1"></i> Batal
                            </button>
                            <button type="submit" class="btn btn-success px-4 fw-medium" id="btn-simpan">
                                <i class="fa fa-save me-1"></i> Simpan
                            </button>
                        </div>
                    </div>
                </form>
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
        let datatableDcReport;

        // 1. Fungsi Upload & Preview (Global Scope)
        function uploadAndPreviewFile(file) {
            if (!file) return;

            let formData = new FormData();
            formData.append('file', file);
            formData.append('_token', '{{ csrf_token() }}');

            Swal.fire({
                title: 'Membaca File...',
                text: 'Sedang memuat data preview',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            $.ajax({
                url: "{{ route('terima.secondary.luar.preview') }}",
                type: "POST",
                data: formData,
                contentType: false,
                processData: false,
                success: function (res) {
                    Swal.close();
                    let html = '';
                    if (res.data && res.data.length > 0) {
                        res.data.forEach(row => {
                            html += `<tr>
                                <td>${row.tanggal || '-'}</td>
                                <td>${row.no_ws || '-'}</td>
                                <td>${row.buyer || '-'}</td>
                                <td>${row.style || '-'}</td>
                                <td>${row.color || '-'}</td>
                                <td>${row.size || '-'}</td>
                                <td>${row.panel || '-'}</td>
                                <td>${row.part || '-'}</td>
                                <td class="text-end pe-3">${row.qty || 0}</td>
                            </tr>`;
                        });
                    } else {
                        html = `<tr><td colspan="9" class="text-center text-muted py-4">Data kosong.</td></tr>`;
                    }
                    
                    $('#import-secondary-luar-preview').html(html);
                    $('#import-secondary-luar-summary').html(res.summary || '');
                    $('#import-secondary-luar-error').addClass('d-none').html('');
                },
                error: function (xhr) {
                    Swal.close();
                    let errMsg = xhr.responseJSON?.message || 'Gagal mengunggah file.';
                    $('#import-secondary-luar-error').removeClass('d-none').html('<i class="fa fa-exclamation-triangle me-1"></i> ' + errMsg);
                }
            });
        }

        // 2. Fungsi Reset Form Preview tanpa AJAX (Tampilan saja)
        function resetImportPreview() {
            $('#import-secondary-luar-file').val('');
            $('#file-name-display').text('No file selected.').removeClass('text-dark fw-semibold');
            $('#import-secondary-luar-preview').html(`
                <tr>
                    <td colspan="9" class="text-center text-muted py-5">
                        Pilih file di atas untuk menampilkan preview data.
                    </td>
                </tr>
            `);
            $('#import-secondary-luar-summary').html('');
            $('#import-secondary-luar-error').addClass('d-none').html('');
        }

        // 3. Fungsi Kosongkan Preview via AJAX dengan Swal Konfirmasi
        function emptyImportPreview(routeUrl) {
            Swal.fire({
                title: 'Kosongkan Preview?',
                text: 'Data preview yang tampil akan dibersihkan.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fa fa-trash me-1"></i> Ya, Kosongkan!',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Memproses...',
                        text: 'Membersihkan data preview',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });

                    $.ajax({
                        url: routeUrl,
                        type: "POST",
                        data: { _token: '{{ csrf_token() }}' },
                        success: function (res) {
                            resetImportPreview();
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: res.message || 'Preview berhasil dikosongkan.',
                                timer: 1500,
                                showConfirmButton: false
                            });
                        },
                        error: function (xhr) {
                            Swal.fire('Gagal!', xhr.responseJSON?.message || 'Terjadi kesalahan.', 'error');
                        }
                    });
                }
            });
        }

        // --- DOM Ready Event ---
        document.addEventListener("DOMContentLoaded", () => {

            // Set tanggal awal dan akhir otomatis (periode 1 bulan)
            const today = new Date();
            const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
            const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);
            
            const formatDate = (date) => {
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            };
            
            $('#tgl-awal').val(formatDate(firstDay));
            $('#tgl-akhir').val(formatDate(lastDay));

            // Inisialisasi DataTables Main Report
            datatableDcReport = $("#datatable-dc-report").DataTable({
                ordering: false,
                processing: true,
                serverSide: false,
                scrollX: true,
                scrollY: "60vh",
                scrollCollapse: true,
                pageLength: 25,
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "All"]
                ],
                ajax: {
                    url: '{{ route('dc-report-terima-secondary-luar') }}',
                    data: function (d) {
                        d.dateFrom = $("#tgl-awal").val();
                        d.dateTo = $("#tgl-akhir").val();
                    }
                },
                columns: [
                    { data: 'tanggal' },
                    { data: 'no_ws' },
                    { data: 'buyer' },
                    { data: 'style' },
                    { data: 'color' },
                    { data: 'size' },
                    { data: 'panel' },
                    { data: 'part' },
                    { data: 'qty' },
                    { data: 'created_by_username' },
                    { data: 'created_at' },
                ],
                columnDefs: [
                    {
                        targets: "_all",
                        className: 'align-middle text-nowrap'
                    },
                ],
            });

            // Event Browse File
            const $fileInput =$('#import-secondary-luar-file');
            const $fileNameDisplay =$('#file-name-display');
            const dropZone = document.getElementById('drop-zone');

            $fileInput.on('change', function () {
                if (this.files && this.files.length > 0) {
                    let file = this.files[0];
                    $fileNameDisplay.text(file.name).addClass('text-dark fw-semibold');
                    uploadAndPreviewFile(file);
                }
            });

            // Event Drag and Drop
            if (dropZone) {
                ['dragenter', 'dragover'].forEach(eventName => {
                    dropZone.addEventListener(eventName, (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        $(dropZone).addClass('drag-over');
                    }, false);
                });

                ['dragleave', 'drop'].forEach(eventName => {
                    dropZone.addEventListener(eventName, (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        $(dropZone).removeClass('drag-over');
                    }, false);
                });

                dropZone.addEventListener('drop', (e) => {
                    let dt = e.dataTransfer;
                    let files = dt.files;

                    if (files.length > 0) {
                        $fileInput[0].files = files;
                        $fileNameDisplay.text(files[0].name).addClass('text-dark fw-semibold');
                        uploadAndPreviewFile(files[0]);
                    }
                });
            }

            // Click Handler Kosongkan Preview
            $('#btn-empty-preview').on('click', function () {
                emptyImportPreview("{{ route('terima.secondary.luar.empty-preview') }}");
            });

            // Submit Form Save Import
            $('#importSecondaryLuarForm').on('submit', function (e) {
                e.preventDefault();

                Swal.fire({
                    title: 'Simpan Data?',
                    text: 'Data preview akan disimpan ke database.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Simpan'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Menyimpan...',
                            text: 'Mohon tunggu sebentar',
                            allowOutsideClick: false,
                            didOpen: () => Swal.showLoading()
                        });

                        $.ajax({
                            url: "{{ route('terima.secondary.luar.store') }}",
                            type: "POST",
                            data: { _token: '{{ csrf_token() }}' },
                            success: function (res) {
                                Swal.fire('Berhasil!', res.message, 'success').then(() => {
                                    $('#importSecondaryLuarModal').modal('hide');
                                    datatableDcReport.ajax.reload(); // Reload Datatable Utama tanpa refresh seluruh halaman
                                });
                            },
                            error: function (xhr) {
                                Swal.fire('Gagal!', xhr.responseJSON?.message || 'Terjadi kesalahan.', 'error');
                            }
                        });
                    }
                });
            });
        });

        // Filter DataTable Search
        function datatableReportDC() {
            let start_date = $('#tgl-awal').val();
            let end_date = $('#tgl-akhir').val();

            if (start_date && end_date) {
                Swal.fire({
                    title: 'Loading...',
                    text: 'Please wait while data is loading.',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });
            }

            datatableDcReport.ajax.reload(function () {
                Swal.close();
            });
        }

        // Export Excel
        async function exportExcel() {
            Swal.fire({
                title: "Exporting",
                html: "Please Wait...",
                timerProgressBar: true,
                didOpen: () => Swal.showLoading()
            });

            await $.ajax({
                url: "{{ route('export_excel_report_terima_secondary_luar') }}",
                type: "post",
                data: {
                    _token: '{{ csrf_token() }}',
                    from: $("#tgl-awal").val(),
                    to: $("#tgl-akhir").val(),
                },
                xhrFields: { responseType: 'blob' },
                success: function (res) {
                    Swal.close();

                    iziToast.success({
                        title: 'Success',
                        message: 'Berhasil diexport',
                        position: 'topCenter'
                    });

                    var blob = new Blob([res]);
                    var link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = "Report Terima Secondary Luar " + $("#tgl-awal").val() + " - " + $("#tgl-akhir").val() + ".xlsx";
                    link.click();
                },
                error: function (jqXHR) {
                    Swal.close();
                    console.error(jqXHR);
                }
            });
        }
    </script>
@endsection