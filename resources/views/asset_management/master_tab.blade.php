@extends('layouts.index')

@section('custom-link')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">

    <!-- SheetJS: baca isi file Excel/CSV di browser untuk kebutuhan preview -->
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

    <style type="text/css">
        .file-preview-info {
            display: none;
            font-size: 12px;
            color: #6c757d;
            margin-top: 8px;
        }

        #filePreviewTableWrap {
            display: none;
            max-height: 60vh;
            overflow: auto;
            margin-top: 8px;
            border: 1px solid #ced4da;
            border-radius: 8px;
        }

        #filePreviewTable {
            margin-bottom: 0;
        }

        #filePreviewTable thead th {
            position: sticky;
            top: 0;
            z-index: 2;
            background-color: var(--sb-color);
            color: var(--light-color);
        }
    </style>
@endsection

@section('content')
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-tags"></i> Master Tab</h5>
        </div>
        <div class="card-body">
            <div class="d-flex justify-content-between mb-3">
                <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal"
                    data-bs-target="#CreateModal">
                    <i class="fas fa-plus"></i> New
                </button>

                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="downloadFormatUpload()">
                        <i class="fas fa-file-alt"></i> Format Upload
                    </button>
                    <button type="button" class="btn btn-outline-success btn-sm" onclick="OpenModal()">
                        <i class="fas fa-file-upload"></i> Upload Data
                    </button>
                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="deleteAllData()">
                        <i class="fas fa-trash-alt"></i> Hapus Semua
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-hover align-middle text-nowrap w-100">
                    <thead class="bg-sb">
                        <tr>
                            <th scope="col" class="text-center align-middle" style="width: 60px;">No</th>
                            <th scope="col" class="text-center align-middle">RFID Code</th>
                            <th scope="col" class="text-center align-middle">Line Code</th>
                            <th scope="col" class="text-center align-middle">Tab Code</th>
                            <th scope="col" class="text-center align-middle">Act</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Upload Data -->
    <div class="modal fade" id="importExcel" tabindex="-1" aria-labelledby="importExcelLabel" aria-hidden="true"
        data-bs-backdrop="static">
        <div class="modal-dialog modal-lg">
            <form method="post" action="{{ route('import_master_tab') }}" enctype="multipart/form-data"
                onsubmit="submitUploadForm(this, event)">
                <div class="modal-content">
                    <div class="modal-header bg-sb text-white">
                        <h5 class="modal-title" id="importExcelLabel">Upload Data Master Tab</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        {{ csrf_field() }}

                        <label for="file"><small><b>File Excel :</b></small></label>
                        <input type="file" id="file" name="file" class="form-control form-control-sm"
                            accept=".xls,.xlsx,.csv" required onchange="previewFile(this)">

                        <div class="file-preview-info" id="filePreviewInfo"></div>

                        <div id="filePreviewTableWrap">
                            <input type="text" id="filePreviewSearch" class="form-control form-control-sm mb-2"
                                placeholder="Cari data preview..." oninput="filterFilePreview()">
                            <table class="table table-bordered table-sm mb-0" id="filePreviewTable">
                                <thead class="bg-sb"></thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary btn-sm" id="uploadButton" disabled>
                            <i class="fa fa-upload"></i> Upload
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal New -->
    <div class="modal fade" id="CreateModal" tabindex="-1" aria-labelledby="CreateModalLabel" aria-hidden="true"
        data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-sb text-white">
                    <h5 class="modal-title" id="CreateModalLabel">New Master Tab</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="txtrfid"><small><b>RFID Code :</b></small></label>
                        <input type="text" id="txtrfid" name="txtrfid" class="form-control form-control-sm">
                    </div>
                    <div class="mb-3">
                        <label for="txtline"><small><b>Line Code :</b></small></label>
                        <input type="text" id="txtline" name="txtline" class="form-control form-control-sm">
                    </div>
                    <div class="mb-3">
                        <label for="txttab"><small><b>Tab Code :</b></small></label>
                        <input type="text" id="txttab" name="txttab" class="form-control form-control-sm">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success btn-sm" id="saveButton" onclick="save_master_tab();">
                        <i class="fas fa-save"></i> Save
                    </button>
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Edit -->
    <div class="modal fade" id="EditModal" tabindex="-1" aria-labelledby="EditModalLabel" aria-hidden="true"
        data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-sb text-white">
                    <h5 class="modal-title" id="EditModalLabel">Edit Master Tab</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="txted_id">
                    <div class="mb-3">
                        <label for="txted_rfid"><small><b>RFID Code :</b></small></label>
                        <input type="text" id="txted_rfid" name="txted_rfid" class="form-control form-control-sm">
                    </div>
                    <div class="mb-3">
                        <label for="txted_line"><small><b>Line Code :</b></small></label>
                        <input type="text" id="txted_line" name="txted_line" class="form-control form-control-sm">
                    </div>
                    <div class="mb-3">
                        <label for="txted_tab"><small><b>Tab Code :</b></small></label>
                        <input type="text" id="txted_tab" name="txted_tab" class="form-control form-control-sm">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success btn-sm" id="editButton"
                        onclick="update_master_tab();">
                        <i class="fas fa-save"></i> Save
                    </button>
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

    <script>
        // Modul Asset: senyapkan alert bawaan DataTables saat ajax gagal, cukup dicatat di console
        $.fn.dataTable.ext.errMode = function(settings, techNote, message) {
            console.error('DataTable ajax error:', message);
        };

        const EXPECTED_HEADERS = ['RFID Code', 'Line Code', 'Tab Code'];
        let filePreviewState = {
            header: [],
            dataRows: [],
            duplicateRfids: []
        };

        function OpenModal() {
            clearFile();
            $('#importExcel').modal('show');
        }

        function previewFile(input) {
            let file = input.files[0];

            if (!file) {
                clearFile();
                return;
            }

            let reader = new FileReader();

            reader.onload = function(e) {
                let workbook = XLSX.read(e.target.result, {
                    type: 'array'
                });
                let sheet = workbook.Sheets[workbook.SheetNames[0]];
                let rows = XLSX.utils.sheet_to_json(sheet, {
                    header: 1,
                    defval: ''
                });

                renderFilePreview(rows);
            };

            reader.onerror = function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Membaca File',
                    text: 'File tidak dapat dibaca, pastikan formatnya benar (.xls/.xlsx/.csv).',
                });
                clearFile();
            };

            reader.readAsArrayBuffer(file);
        }

        function isValidHeader(header) {
            return EXPECTED_HEADERS.every((expected, i) =>
                String(header?.[i] ?? '').trim().toLowerCase() === expected.toLowerCase()
            );
        }

        function renderFilePreview(rows) {
            if (!rows.length) {
                Swal.fire({
                    icon: 'warning',
                    title: 'File Kosong',
                    text: 'Tidak ada data yang dapat ditampilkan dari file ini.',
                });
                $('#uploadButton').prop('disabled', true);
                return;
            }

            let header = rows[0];
            // baris kosong (mis. sisa range kosong di excel) tidak dihitung sebagai data
            let dataRows = rows.slice(1).filter(row => String(row[0] ?? '').trim() !== '');

            if (!isValidHeader(header)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Format File Tidak Sesuai',
                    text: `Kolom harus: ${EXPECTED_HEADERS.join(', ')}. Silakan gunakan Format Upload yang disediakan.`,
                });
                clearFile();
                return;
            }

            // RFID Code dianggap kolom pertama, dipakai untuk deteksi baris dobel di preview
            let rfidCount = {};
            dataRows.forEach(row => {
                let rfid = String(row[0] ?? '').trim();
                if (!rfid) return;
                rfidCount[rfid] = (rfidCount[rfid] || 0) + 1;
            });
            let duplicateRfids = Object.keys(rfidCount).filter(rfid => rfidCount[rfid] > 1);

            filePreviewState = {
                header,
                dataRows,
                duplicateRfids
            };

            let $thead = $('#filePreviewTable thead').empty();
            let $headRow = $('<tr></tr>');
            header.forEach(col => $headRow.append(`<th class="text-center align-middle">${col}</th>`));
            $thead.append($headRow);

            $('#filePreviewSearch').val('');
            renderPreviewRows();

            $('#filePreviewTableWrap').show();
            $('#uploadButton').prop('disabled', dataRows.length === 0 || duplicateRfids.length > 0);
        }

        function filterFilePreview() {
            renderPreviewRows();
        }

        function renderPreviewRows() {
            let {
                header,
                dataRows,
                duplicateRfids
            } = filePreviewState;

            let keyword = $('#filePreviewSearch').val().trim().toLowerCase();

            let filteredRows = keyword ?
                dataRows.filter(row => row.some(cell => String(cell ?? '').toLowerCase().includes(keyword))) :
                dataRows;

            let $tbody = $('#filePreviewTable tbody').empty();
            filteredRows.forEach(row => {
                let rfid = String(row[0] ?? '').trim();
                let isDuplicate = rfid && duplicateRfids.includes(rfid);
                let $tr = $('<tr></tr>').toggleClass('table-danger', isDuplicate);
                header.forEach((col, i) => $tr.append(`<td>${row[i] ?? ''}</td>`));
                $tbody.append($tr);
            });

            let infoText = keyword ?
                `Menampilkan ${filteredRows.length} dari ${dataRows.length} baris hasil pencarian` :
                `Total ${dataRows.length} baris data`;
            if (duplicateRfids.length) {
                infoText += `. RFID Code dobel: ${duplicateRfids.join(', ')}`;
            }
            $('#filePreviewInfo').text(infoText).show()
                .toggleClass('text-danger fw-bold', duplicateRfids.length > 0);
        }

        function clearFile() {
            $('#file').val('');
            $('#filePreviewInfo').hide().text('');
            $('#filePreviewSearch').val('');
            $('#filePreviewTableWrap').hide();
            $('#filePreviewTable thead').empty();
            $('#filePreviewTable tbody').empty();
            $('#uploadButton').prop('disabled', true);
            filePreviewState = {
                header: [],
                dataRows: [],
                duplicateRfids: []
            };
        }

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
            lengthMenu: [
                [10, 25, 50, 100, -1],
                [10, 25, 50, 100, 'All']
            ],
            pageLength: -1,
            ajax: {
                url: '{{ route('asset_master_tab') }}',
            },
            columns: [{
                    data: null,
                    className: 'text-center',
                    render: function(data, type, row, meta) {
                        return meta.row + 1;
                    }
                }, // No
                {
                    data: 'rfid_code'
                }, // Rf ID Code
                {
                    data: 'line_code'
                }, // Line Code
                {
                    data: 'tab_code'
                }, // Tab Code
                {
                    data: 'id',
                    render: function(data) {
                        return `
                    <div class="text-center">
                        <button class="btn btn-sm btn-warning" onclick="editData(${data})">
                            <i class="fa fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="deleteData(${data})">
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>`;
                    },
                    orderable: false,
                    searchable: false
                } // Act
            ],
        });

        function save_master_tab() {
            let rfid_code = $('#txtrfid').val();
            let line_code = $('#txtline').val();
            let tab_code = $('#txttab').val();

            if (!rfid_code) {
                Swal.fire({
                    icon: 'warning',
                    title: 'RFID Code wajib diisi',
                });
                return;
            }

            let $btn = $('#saveButton');
            $btn.prop('disabled', true);

            $.ajax({
                type: "POST",
                url: '{{ route('store_master_tab') }}',
                data: {
                    _token: '{{ csrf_token() }}',
                    rfid_code: rfid_code,
                    line_code: line_code,
                    tab_code: tab_code
                },
                success: function(response) {
                    $('#CreateModal').modal('hide');
                    $('#txtrfid, #txtline, #txttab').val('');
                    dataTableReload();

                    Swal.fire({
                        icon: 'success',
                        title: 'Master Tab Ditambahkan',
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
                url: '{{ route('show_master_tab') }}',
                method: 'GET',
                data: {
                    id: id_c
                },
                dataType: 'json',
                success: function(res) {
                    $('#txted_id').val(res.id);
                    $('#txted_rfid').val(res.rfid_code);
                    $('#txted_line').val(res.line_code);
                    $('#txted_tab').val(res.tab_code);
                    $('#EditModal').modal('show');
                },
                error: function(request, status, error) {
                    console.error(request.responseText);
                },
            });
        }

        function update_master_tab() {
            let id = $('#txted_id').val();
            let rfid_code = $('#txted_rfid').val();
            let line_code = $('#txted_line').val();
            let tab_code = $('#txted_tab').val();

            if (!rfid_code) {
                Swal.fire({
                    icon: 'warning',
                    title: 'RFID Code wajib diisi',
                });
                return;
            }

            let $btn = $('#editButton');
            $btn.prop('disabled', true);

            $.ajax({
                type: "POST",
                url: '{{ route('update_master_tab') }}',
                data: {
                    _token: '{{ csrf_token() }}',
                    id: id,
                    rfid_code: rfid_code,
                    line_code: line_code,
                    tab_code: tab_code
                },
                success: function(response) {
                    $('#EditModal').modal('hide');
                    dataTableReload();

                    Swal.fire({
                        icon: 'success',
                        title: 'Master Tab Diupdate',
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
                title: 'Hapus Master Tab?',
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
                    url: '{{ route('delete_master_tab') }}',
                    data: {
                        _token: '{{ csrf_token() }}',
                        id: id_c
                    },
                    success: function(response) {
                        dataTableReload();
                        Swal.fire({
                            icon: 'success',
                            title: 'Master Tab Dihapus',
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

        function deleteAllData() {
            Swal.fire({
                icon: 'warning',
                title: 'Hapus Semua Data Master Tab?',
                text: 'Apakah anda yakin? Seluruh data yang sudah terinput akan dihapus permanen dan tidak dapat dikembalikan.',
                showCancelButton: true,
                confirmButtonText: 'Ya, Hapus Semua',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#dc3545'
            }).then((result) => {
                if (!result.isConfirmed) {
                    return;
                }

                $.ajax({
                    type: "POST",
                    url: '{{ route('delete_all_master_tab') }}',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        dataTableReload();
                        Swal.fire({
                            icon: 'success',
                            title: 'Semua Data Dihapus',
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
                                'Terjadi kesalahan saat menghapus semua data.',
                        });
                    }
                });
            });
        }

        function submitUploadForm(e, evt) {
            evt.preventDefault();

            let formData = new FormData(e);
            let $btn = $('#uploadButton');
            $btn.prop('disabled', true);

            $.ajax({
                type: "POST",
                url: e.action,
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    $('#importExcel').modal('hide');
                    clearFile();
                    dataTableReload();

                    Swal.fire({
                        icon: 'success',
                        title: 'Upload Berhasil',
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
                        text: xhr.responseJSON?.message || 'Terjadi kesalahan saat upload.',
                    });
                },
                complete: function() {
                    $btn.prop('disabled', false);
                }
            });
        }

        // Contoh format file upload (RFID Code, Line Code, Tab Code) di-generate langsung di browser via SheetJS
        function downloadFormatUpload() {
            let sampleData = [
                ['RFID Code', 'Line Code', 'Tab Code'],
                ['RFID001', 'LINE-A', 'TAB-01'],
                ['RFID002', 'LINE-B', 'TAB-02'],
            ];

            let worksheet = XLSX.utils.aoa_to_sheet(sampleData);
            let workbook = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(workbook, worksheet, 'Master Tab');
            XLSX.writeFile(workbook, 'Format_Upload_Master_Tab.xlsx');
        }
    </script>
@endsection
