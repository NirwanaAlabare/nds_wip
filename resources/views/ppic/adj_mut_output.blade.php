@extends('layouts.index')

@section('custom-link')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">

    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@endsection

@section('content')
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-list"></i> Adjustment Mutasi Output</h5>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <a class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#mutasiOutputModal"
                    onclick="reset()">
                    <i class="fas fa-plus"></i>
                    Baru
                </a>
            </div>
            <div class="d-flex align-items-end gap-3 mb-3">
                <div class="mb-3">
                    <label class="form-label"><small><b>Tgl Awal</b></small></label>
                    <input type="date" class="form-control form-control-sm " id="tgl-awal" name="tgl_awal"
                        value="{{ $tgl_awal_fix }}">
                </div>
                <div class="mb-3">
                    <label class="form-label"><small><b>Tgl Akhir</b></small></label>
                    <input type="date" class="form-control form-control-sm" id="tgl-akhir" name="tgl_akhir"
                        value="{{ $tgl_akhir_fix }}">
                </div>
                <div class="mb-3">
                    <a class="btn btn-outline-primary position-relative btn-sm" onclick="dataTableReload()">
                        <i class="fas fa-search"></i> Cari
                    </a>
                    <a class="btn btn-outline-danger position-relative btn-sm" onclick="deleteSelectedUpload()"">
                        <i class="fas fa-trash"></i> Hapus Terpilih
                    </a>
                </div>
            </div>
            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-striped w-100 text-nowrap">
                    <thead class="bg-sb">
                        <tr style="text-align:center; vertical-align:middle">
                            <th scope="col">Tgl. SO</th>
                            <th scope="col">WS</th>
                            <th scope="col">Buyer</th>
                            <th scope="col">Style</th>
                            <th scope="col">Color</th>
                            <th scope="col">Size</th>
                            <th scope="col">Adj Sewing</th>
                            <th scope="col">Adj Steam</th>
                            <th scope="col">Adj Defect Sewing</th>
                            <th scope="col">Adj Defect Spotcleaning</th>
                            <th scope="col">Adj Defect Mending</th>
                            <th scope="col">Adj Defect Packing Sewing</th>
                            <th scope="col">Adj Defect Packing Spotcleaning</th>
                            <th scope="col">Adj Defect Packing Mending</th>
                            <th scope="col">Adj Packing Line</th>
                            <th scope="col">Adj Transfer Garment</th>
                            <th scope="col">Adj Packing Central</th>
                            <th scope="col">Updated </th>
                            <th scope="col">User</th>
                            <th scope="col">
                                <input type="checkbox" id="selectAllCheckbox" />
                            </th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="mutasiOutputModal" tabindex="-1" aria-labelledby="mutasiOutputModalLabel"
        aria-hidden="true" data-bs-backdrop="static">
        <form action="{{ route('upload_adj_mut_output') }}" enctype="multipart/form-data" method="post"
            onsubmit="submitForm(this, event)" name="form_upload_adjustment" id="form_upload_adjustment">
            @csrf
            @method('POST')
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header bg-sb text-light">
                        <h5 class="modal-title" id="mutasiOutputModalLabel">Adjustment Mutasi Output (Production)</h5>
                        <!-- Custom close button with white icon -->
                        <button type="button" class="btn btn-sm text-white" data-bs-dismiss="modal" aria-label="Close">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <!-- Top Row -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <a href="{{ route('contoh_upload_adj_mut_output') }}"
                                class="btn btn-outline-success position-relative btn-sm">
                                <i class="fas fa-file-download fa-sm"></i>
                                Contoh Upload
                            </a>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label"><small><b>Upload File</b></small></label>
                                    <input type="file" class="form-control form-control-sm" name="file_tmbh"
                                        id="file_tmbh">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <div class="input-group">
                                        <button type="submit" class="btn btn-outline-info btn-sm me-1">
                                            <i class="fas fa-check"></i> Upload
                                        </button>
                                        <button type="button" class="btn btn-outline-warning btn-sm" onclick="undo()">
                                            <i class="fas fa-undo"></i> Undo
                                        </button>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="table-responsive mt-3">
                            <table id="datatable_upload" class="table table-bordered w-100 text-nowrap">
                                <thead class="bg-sb">
                                    <tr style="text-align:center; vertical-align:middle">
                                        <th scope="col">Tgl. Adj</th>
                                        <th scope="col">ID SO Det</th>
                                        <th scope="col">WS</th>
                                        <th scope="col">Buyer</th>
                                        <th scope="col">Style</th>
                                        <th scope="col">Color</th>
                                        <th scope="col">Size</th>
                                        <th scope="col">Adj Sewing</th>
                                        <th scope="col">Adj Steam</th>
                                        <th scope="col">Adj Defect Sewing</th>
                                        <th scope="col">Adj Defect Spotcleaning</th>
                                        <th scope="col">Adj Defect Mending</th>
                                        <th scope="col">Adj Defect Packing Sewing</th>
                                        <th scope="col">Adj Defect Packing Spotcleaning</th>
                                        <th scope="col">Adj Defect Packing Mending</th>
                                        <th scope="col">Adj Packing Line</th>
                                        <th scope="col">Adj Transfer Garment</th>
                                        <th scope="col">Adj Packing Central</th>
                                        <th scope="col">Updated </th>
                                        <th scope="col">User</th>
                                        <th scope="col">Status</th>
                                    </tr>
                                </thead>
                            </table>
                            <div class="mb-2">
                                <span class="badge bg-success" id="count-ok">OK: 0</span>
                                <span class="badge bg-danger" id="count-invalid">Invalid: 0</span>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-danger btn-sm" data-bs-dismiss="modal">
                            <i class="fas fa-times-circle"></i> Tutup
                        </button>
                        <a class="btn btn-outline-success btn-sm" onclick="simpan()">
                            <i class="fas fa-check"></i> Tambah
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@section('custom-script')
    <!-- DataTables & Plugins -->
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
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
            containerCssClass: 'form-control-sm rounded'
        });
    </script>
    <script>
        function notif() {
            alert("Maaf, Fitur belum tersedia!");
        }

        function reset() {
            $("#form_upload").trigger("reset");
            dataTableUploadReload();
            document.getElementById('file_tmbh').value = "";
        }


        function submitForm(form, event) {
            event.preventDefault();

            const formData = new FormData(form);
            const url = form.getAttribute("action");

            fetch(url, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: data.message,
                            timer: 2000,
                            showConfirmButton: false
                        });

                        // Optional: refresh DataTable
                        $('#datatable_upload').DataTable().ajax.reload();

                        // Optional: reset form
                        form.reset();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal Upload',
                            text: data.message,
                            confirmButtonText: 'Tutup'
                        });
                    }
                })
                .catch(error => {
                    console.error('Upload error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Terjadi Kesalahan',
                        text: 'Data tidak ada atau tidak sesuai',
                        confirmButtonText: 'Tutup'
                    });
                });
        }




        function undo() {
            // Clear file input
            document.getElementById("file_tmbh").value = '';

            // Call AJAX to delete temp upload
            $.ajax({
                url: '{{ route('undo_upload_adj_mut_output') }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    // Reload the table after deletion
                    dataTableUploadReload();
                    Swal.fire({
                        icon: 'success',
                        title: 'Data dihapus',
                        text: 'Upload sementara berhasil dibersihkan.'
                    });
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Terjadi kesalahan saat menghapus data sementara.'
                    });
                }
            });
        }

        function dataTableUploadReload() {
            datatable_upload.ajax.reload();
        }

        let datatable_upload = $("#datatable_upload").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            paging: true,
            searching: true,
            scrollY: '300px',
            scrollX: true,
            scrollCollapse: true,
            ajax: {
                url: '{{ route('show_datatable_upload_adj_mut_output') }}',
                dataSrc: function(json) {
                    $('#count-ok').text('OK: ' + (json.status_counts?.ok ?? 0));
                    $('#count-invalid').text('Invalid: ' + (json.status_counts?.invalid ?? 0));
                    return json.data;
                }
            },
            columns: [{
                    data: 'tgl_adj'
                },
                {
                    data: 'id_so_det'
                },
                {
                    data: 'ws'
                },
                {
                    data: 'buyer'
                },
                {
                    data: 'style'
                },
                {
                    data: 'color'
                },
                {
                    data: 'size'
                },
                {
                    data: 'sa_sewing'
                },
                {
                    data: 'sa_steam'
                },
                {
                    data: 'sa_def_sewing'
                },
                {
                    data: 'sa_def_spotcleaning'
                },
                {
                    data: 'sa_def_mending'
                },
                {
                    data: 'sa_def_pck_sewing'
                },
                {
                    data: 'sa_def_pck_spotcleaning'
                },
                {
                    data: 'sa_def_pck_mending'
                },
                {
                    data: 'sa_pck_line'
                },
                {
                    data: 'sa_trf_gmt'
                },
                {
                    data: 'sa_pck_central'
                },
                {
                    data: 'created_at'
                },
                {
                    data: 'created_by'
                },
                {
                    data: 'status'
                }
            ],
            columnDefs: [{
                className: "dt-center",
                targets: "_all"
            }],
            rowCallback: function(row, data, index) {
                if (data.status === 'invalid') {
                    $(row).css('background-color', '#f8d7da'); // Bootstrap red
                } else if (data.status === 'ok') {
                    $(row).css('background-color', '#d4edda'); // Bootstrap green
                }
            }

        });



        function simpan() {
            $.ajax({
                type: "post",
                url: '{{ route('store_upload_adj_mut_output') }}',
                success: function(response) {
                    if (response.icon == 'salah') {
                        iziToast.warning({
                            message: response.msg,
                            position: 'topCenter'
                        });
                        dataTableUploadReload();
                    } else {
                        Swal.fire({
                            text: response.msg,
                            icon: "success"
                        });
                        dataTableUploadReload();
                    }

                },
                error: function(request, status, error) {
                    iziToast.warning({
                        message: 'Silahkan cek lagi',
                        position: 'topCenter'
                    });
                    dataTableUploadReload();
                },
            });

        };


        function dataTableReload() {
            datatable.ajax.reload();
        }
        let datatable = $("#datatable").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            paging: true,
            searching: true,
            scrollY: '300px',
            scrollX: true,
            scrollCollapse: true,
            ajax: {
                url: '{{ route('ppic_tools_adj_mut_output') }}',
                data: function(d) {
                    d.dateFrom = $('#tgl-awal').val();
                    d.dateTo = $('#tgl-akhir').val();
                },
            },
            columns: [{
                    data: 'tgl_adj'

                },
                {
                    data: 'kpno'
                },
                {
                    data: 'buyer'
                },
                {
                    data: 'styleno'
                },
                {
                    data: 'color'
                },
                {
                    data: 'size'
                },
                {
                    data: 'sa_sewing'
                },
                {
                    data: 'sa_steam'
                },
                {
                    data: 'sa_def_sewing'
                },
                {
                    data: 'sa_def_spotcleaning'
                },
                {
                    data: 'sa_def_mending'
                },
                {
                    data: 'sa_def_pck_sewing'
                },
                {
                    data: 'sa_def_pck_spotcleaning'
                },
                {
                    data: 'sa_def_pck_mending'
                },
                {
                    data: 'sa_pck_line'
                },
                {
                    data: 'sa_trf_gmt'
                },
                {
                    data: 'sa_pck_central'
                },
                {
                    data: 'created_at'
                },
                {
                    data: 'created_by'
                },
                {
                    data: 'id',
                    render: function(data, type, row) {
                        return `<input type="checkbox" class="row-checkbox" value="${data}">`;
                    },
                    orderable: false,
                    searchable: false
                },
            ],
            columnDefs: [{
                "className": "dt-center",
                "targets": "_all"
            }, ]
        }, );

        // Select all checkboxes
        document.getElementById('selectAllCheckbox').addEventListener('change', function() {
            const checked = this.checked;
            document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = checked);
        });

        function deleteSelectedUpload() {
            const selected = Array.from(document.querySelectorAll('.row-checkbox:checked')).map(cb => cb.value);

            if (selected.length === 0) {
                Swal.fire('Peringatan', 'Pilih data yang ingin dihapus.', 'warning');
                return;
            }

            Swal.fire({
                title: 'Yakin?',
                text: `${selected.length} data akan dihapus.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('{{ route('delete_upload_adj_mut_output') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                            },
                            body: JSON.stringify({
                                ids: selected
                            })
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire('Berhasil', data.message, 'success');
                                dataTableReload();
                                document.getElementById('selectAllCheckbox').checked = false;
                            } else {
                                Swal.fire('Gagal', data.message, 'error');
                                dataTableReload();
                            }
                        })
                        .catch(() => {
                            Swal.fire('Error', 'Gagal menghubungi server.', 'error');
                        });
                }
            });
        }

        4
    </script>
@endsection
