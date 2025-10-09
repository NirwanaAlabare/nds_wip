@extends('layouts.index')

@section('custom-link')
    {{-- <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}"> --}}

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables 2.0/jquery.dataTables.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables 2.0/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables 2.0/fixedColumns.bootstrap4.min.css') }}">
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
    </style>
@endsection

@section('content')
    <div class="modal fade" id="myModal" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true"
        data-bs-backdrop="static">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">

                <div class="modal-header bg-sb text-white">
                    <h5 class="modal-title" id="modalCostingLabel"><i class="fas fa-list"></i> Detail Daily Cost</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- 📌 Bulan & Tahun Form Group -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="modal_nama_bulan" class="form-label"><small><strong>Bulan</strong></small></label>
                            <input type="text" class="form-control form-control-sm" id="modal_nama_bulan"
                                name="modal_nama_bulan" readonly>
                            <input type="hidden" class="form-control form-control-sm" id="modal_bulan" name="modal_bulan"
                                readonly>
                        </div>
                        <div class="col-md-6">
                            <label for="modal_tahun" class="form-label"><small><strong>Tahun</strong></small></label>
                            <input type="text" class="form-control form-control-sm text-end" id="modal_tahun"
                                name="modal_tahun" readonly>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table id="datatable_modal" class="table table-bordered w-100 text-nowrap">
                            <thead class="bg-sb">
                                <tr>
                                    <th scope="col" class="text-center align-middle">No. COA</th>
                                    <th scope="col" class="text-center align-middle">Nama COA</th>
                                    <th scope="col" class="text-center align-middle">Projection</th>
                                    <th scope="col" class="text-center align-middle">Daily Cost</th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr style="text-align:center; vertical-align:middle">
                                    <th colspan="2" style="text-align:center">Total:</th>
                                    <th id="tot_proj_modal">0</th>
                                    <th id="tot_daily_modal">0</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <div>
                        <!-- Left side: Delete button -->
                        <a class="btn btn-sm btn-outline-danger" onclick="delete_data()">
                            <i class="fas fa-trash"></i>
                            Delete
                        </a>
                    </div>
                    <div>
                        <!-- Right side: Close button -->
                        <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">
                            <i class="fa fa-window-close" aria-hidden="true"></i> Close
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>



    <!-- Import Excel -->
    <div class="modal fade" id="importExcel" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form method="post" action="{{ route('upload_excel_daily_cost') }}" enctype="multipart/form-data"
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


    <div class="card card-sb collapsed-card">
        <div class="card-header" data-card-widget="collapse" style="cursor: pointer;" onclick="dataTableReload();">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-upload"></i> Upload Daily Cost</h5>
        </div>
        <div class="card-body">
            <div class="row align-items-end g-3 mb-3">
                <!-- Periode Bulan -->
                <div class="col-md-2">
                    <label class="form-label">
                        <small><b>Periode Bulan</b></small>
                    </label>
                    <select class="form-control form-control-sm select2bs4" id="periode_bulan" name="periode_bulan"
                        style="width: 100%; font-size: 0.875rem;">
                        <option value="">-- Pilih Bulan --</option>
                        <option value="1">Januari</option>
                        <option value="2">Februari</option>
                        <option value="3">Maret</option>
                        <option value="4">April</option>
                        <option value="5">Mei</option>
                        <option value="6">Juni</option>
                        <option value="7">Juli</option>
                        <option value="8">Agustus</option>
                        <option value="9">September</option>
                        <option value="10">Oktober</option>
                        <option value="11">November</option>
                        <option value="12">Desember</option>
                    </select>
                </div>

                <!-- Periode Tahun -->
                <div class="col-md-2">
                    <label class="form-label">
                        <small><b>Periode Tahun</b></small>
                    </label>
                    <select class="form-control form-control-sm select2bs4" id="periode_tahun" name="periode_tahun"
                        style="width: 100%; font-size: 0.875rem;">
                        <option value="">-- Pilih Tahun --</option>
                        @for ($year = 2025; $year <= 2030; $year++)
                            <option value="{{ $year }}">{{ $year }}</option>
                        @endfor
                    </select>
                </div>

                <!-- Upload Button -->
                <div class="col-md-1">
                    <label class="form-label d-block">
                        <small><b>&nbsp;</b></small>
                    </label>
                    <a class="btn btn-outline-primary btn-sm w-100" style="font-size: 0.85rem;" onclick="OpenModal()">
                        <i class="fas fa-upload"></i> Upload
                    </a>
                </div>

                <!-- Format Button -->
                <div class="col-md-2">
                    <label class="form-label d-block">
                        <small><b>&nbsp;</b></small>
                    </label>
                    <a class="btn btn-outline-warning btn-sm w-100" style="font-size: 0.85rem;"
                        href="{{ route('contoh_upload_daily_cost') }}">
                        <i class="fas fa-file-excel"></i> Format Upload
                    </a>
                </div>

                <!-- Working Days -->
                <div class="col-md-2 ms-auto">
                    <label class="form-label">
                        <small><b>Working Days</b></small>
                    </label>
                    <input type="text" id="working_days" name="working_days" class="form-control form-control-sm"
                        style="font-size: 0.875rem;" readonly>
                </div>
            </div>

            <div class="table-responsive">
                <table id="datatable_preview" class="table table-bordered text-nowrap" style="width: 100%;">
                    <thead class="bg-sb">
                        <tr style='text-align:center; vertical-align:middle'>
                            <th class="text-center align-middle">No. COA</th>
                            <th class="text-center align-middle">Nama COA</th>
                            <th class="text-center align-middle">Projection</th>
                            <th class="text-center align-middle">Daily Cost</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr style="text-align:center; vertical-align:middle">
                            <th colspan="2" style="text-align:center">Total:</th>
                            <th id="total_projection">0</th>
                            <th id="total_daily_cost">0</th>
                        </tr>
                    </tfoot>
                </table>
                <div class="d-flex justify-content-between">
                    <div class="p-2 bd-highlight">
                        <a class="btn btn-sm btn-outline-info" onclick="undo()">
                            <i class="fas fa-redo"></i>
                            Undo
                        </a>
                    </div>
                    <div class="p-2 bd-highlight" id="simpan_tmp" name = "simpan_tmp">
                        <a class="btn btn-sm btn-outline-success" onclick="simpan()">
                            <i class="fas fa-check"></i>
                            Simpan
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-list"></i> Daily Cost</h5>
        </div>
        <div class="card-body">
            <div class="row align-items-end g-3 mb-3">
                <!-- Periode Tahun -->
                <div class="col-md-2">
                    <label class="form-label">
                        <small><b>Periode Tahun</b></small>
                    </label>
                    <select class="form-control form-control-sm select2bs4" id="periode_tahun_view"
                        name="periode_tahun_view" style="width: 100%;">
                        <option value="">-- Pilih Tahun --</option>
                        @for ($year = 2025; $year <= 2030; $year++)
                            <option value="{{ $year }}" {{ $year == now()->year ? 'selected' : '' }}>
                                {{ $year }}
                            </option>
                        @endfor
                    </select>
                </div>



            </div>

            <div class="table-responsive">
                <table id="datatable_list" class="table table-bordered text-nowrap" style="width: 100%;">
                    <thead class="bg-sb">
                        <tr>
                            <th class="text-center align-middle">Bulan</th>
                            <th class="text-center align-middle">Tahun</th>
                            <th class="text-center align-middle">Total Daily Cost</th>
                            <th class="text-center align-middle">Act</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('custom-script')
    <!-- DataTables & Plugins -->
    {{-- <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script> --}}
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables 2.0/dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables 2.0/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables 2.0/dataTables.fixedColumns.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-rowsgroup/dataTables.rowsGroup.js') }}"></script>
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
        $(document).ready(function() {
            $('#periode_bulan').val('').trigger('change');
            $('#periode_tahun').val('').trigger('change');
            dataTableReload();
            dataTableListReload();
        })

        function dataTableReload() {
            datatable.ajax.reload();
            // ✅ Wait a bit, then fix layout
            setTimeout(() => {
                datatable.columns.adjust();
            }, 300); // Delay ensures DOM updates are done
        }

        function dataTableListReload() {
            datatable_list.ajax.reload();
            // ✅ Wait a bit, then fix layout
            setTimeout(() => {
                datatable.columns.adjust();
            }, 300); // Delay ensures DOM updates are done
        }

        // Event listener on both selects
        $('#periode_bulan, #periode_tahun').on('change', function() {
            show_working_days().then(function() {
                dataTableReload(); // Now guaranteed to run after working_days is updated
            });
        });


        function show_working_days() {
            let bulan = $('#periode_bulan').val();
            let tahun = $('#periode_tahun').val();

            console.log(bulan, tahun); // Debug: check if values are correct

            // ✅ Return the AJAX call (which is a Promise)
            return $.ajax({
                url: '{{ route('mgt_report_proses_daily_cost_show_working_days') }}',
                method: 'GET',
                data: {
                    bulan: bulan,
                    tahun: tahun
                },
                success: function(res) {
                    if (res.status === 'success') {
                        $('#working_days').val(res.data.tot_working_days);
                        console.log('Working days:', res.data.tot_working_days);
                    } else {
                        console.error('Unexpected response:', res);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', status, error);
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
                            title: 'Data Upload berhasil diupload',
                            showCancelButton: false,
                            showConfirmButton: true,
                            confirmButtonText: 'Oke',
                            timer: 5000,
                            timerProgressBar: true
                        })
                        $('#importExcel').modal('hide');
                        dataTableReload();
                        dataTableListReload();
                    }
                },

            });
        }


        let datatable = $("#datatable_preview").DataTable({
            ordering: true,
            responsive: true,
            processing: true,
            serverSide: false,
            paging: false,
            searching: true,
            scrollY: '300px',
            scrollX: true,
            scrollCollapse: true,
            ajax: {
                url: '{{ route('mgt_report_proses_daily_cost_show_preview') }}',
                data: function(d) {
                    d.working_days = $('#working_days').val();
                    d.bulan = $('#periode_bulan').val();
                    d.tahun = $('#periode_tahun').val();
                    console.log(d.working_days);
                },
            },
            columns: [{
                    data: 'no_coa'
                },
                {
                    data: 'nama_coa'
                },
                {
                    data: 'projection',
                    className: 'text-end', // Bootstrap right align
                    render: function(data, type, row) {
                        return parseFloat(data).toLocaleString(undefined, {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });
                    }
                },
                {
                    data: 'daily_cost',
                    className: 'text-end',
                    render: function(data, type, row) {
                        return parseFloat(data).toLocaleString(undefined, {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });
                    }
                }
            ],

            // ✅ Highlight rows where cek_valid === 'N'
            rowCallback: function(row, data, index) {
                if (data.cek_valid === 'N') {
                    $(row).css('background-color', '#ff0000'); // Bright red
                    $(row).css('color', 'white'); // Optional: make text readable
                }
            },

            drawCallback: function(settings) {
                let api = this.api();
                let total_projection = 0;
                let total_daily_cost = 0;

                // Loop through all data in daily_cost column
                api.column(2, {
                    page: 'current'
                }).data().each(function(value) {
                    total_projection += parseFloat(value) || 0;
                });

                // Display the total in the footer
                $('#total_projection').html(total_projection.toLocaleString(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));

                // Loop through all data in daily_cost column (index 3)
                api.column(3, {
                    page: 'current'
                }).data().each(function(value) {
                    total_daily_cost += parseFloat(value) || 0;
                });

                // Display the total daily cost in the footer
                $('#total_daily_cost').html(total_daily_cost.toLocaleString(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));

            }
        });

        function simpan() {
            let bulan = $('#periode_bulan').val();
            let tahun = $('#periode_tahun').val();
            let working_days = $('#working_days').val();
            let bulanText = $('#periode_bulan option:selected').text();
            let tahunText = $('#periode_tahun option:selected').text();

            if (!bulan || !tahun || !working_days) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Oops...',
                    text: 'Periode Bulan dan Tahun Wajib Terisi',
                });
                return; // Stop execution here
            }

            // Check if datatable has any data rows
            if (!datatable || datatable.data().count() === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Oops...',
                    text: 'Data tabel masih kosong! Tidak bisa menyimpan.',
                });
                return;
            }

            // ✅ New: check if all rows have cek_valid === 'Y'
            let allValid = true;
            datatable.rows().every(function() {
                let data = this.data();
                if (data.cek_valid !== 'Y') {
                    allValid = false;
                    return false; // stop looping
                }
            });

            if (!allValid) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Validasi Gagal',
                    text: 'Terdapat data yang tidak valid . Mohon periksa kembali sebelum menyimpan.',
                });
                return;
            }

            // Continue with confirmation if all valid
            Swal.fire({
                title: 'Konfirmasi',
                text: `Apakah kamu yakin menyimpan dengan Bulan: ${bulanText}, Tahun: ${tahunText}?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, simpan',
                cancelButtonText: 'Batal',
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: "POST",
                        url: '{{ route('save_tmp_upload_daily_cost') }}',
                        data: {
                            _token: '{{ csrf_token() }}',
                            bulan: bulan,
                            tahun: tahun,
                            working_days: working_days,
                            bulanText: bulanText,
                            tahunText: tahunText,
                        },
                        success: function(response) {
                            if (response.status === 'duplicate') {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Data Sudah Ada',
                                    text: response.message ||
                                        'Data untuk periode ini sudah pernah disimpan.'
                                });
                                return;
                            }

                            Swal.fire({
                                icon: 'success',
                                title: 'Daily Cost Disimpan!',
                                html: `
                    <p><b>Bulan :</b> ${response.data.bulanText}</p>
                    <p><b>Tahun :</b> ${response.data.tahunText}</p>
                    <hr>
                    <p>Data berhasil disimpan.</p>
                `
                            }).then(() => {
                                location.reload();
                            });
                        },
                        error: function(xhr) {
                            console.error('Save Error:', xhr.responseText);
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: 'Gagal menyimpan data.'
                            });
                        }
                    });
                } else {
                    console.log('User cancelled saving.');
                    dataTableReload();
                    dataTableListReload();
                }
            });
        }

        function undo() {

            // Check if datatable has any data rows
            if (!datatable || datatable.data().count() === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Oops...',
                    text: 'Data tabel masih kosong! Tidak bisa di undo.',
                });
                return;
            }

            $.ajax({
                type: "POST",
                url: '{{ route('delete_tmp_upload_daily_cost') }}',
                data: {
                    _token: '{{ csrf_token() }}',
                },
                success: function(response) {

                    Swal.fire({
                        icon: 'success',
                        title: 'Daily Cost Dihapus!',
                        html: `
                        <p>Data berhasil Dihapus.</p>
                    `
                    }).then(() => {
                        // Reload table
                        dataTableReload();
                        dataTableListReload();
                    });
                },
                error: function(xhr) {
                    console.error('Delete Error:', xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Gagal menyimpan data.'
                    });
                }
            });
        }

        let datatable_list = $("#datatable_list").DataTable({
            ordering: false,
            responsive: true,
            processing: true,
            serverSide: false, // ✅ change this!
            paging: true, // ✅ maybe re-enable paging for large data
            searching: true,
            scrollY: true,
            scrollX: false,
            scrollCollapse: true,
            ajax: {
                url: '{{ route('mgt_report_proses_daily_cost') }}',
                data: function(d) {
                    d.periode_tahun_view = $('#periode_tahun_view').val();
                },
            },
            columns: [{
                    data: 'nama_bulan'
                },
                {
                    data: 'tahun'
                },
                {
                    data: 'tot_daily_cost'
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        let html = `
            <div class="text-center align-middle visual-input">
                <input
                    type="button"
                    class="btn btn-primary btn-sm"
                    value="Show"
                    onclick="show_det('${data.bulan}', '${data.nama_bulan}','${data.tahun}')">
            </div>
        `;
                        return html;
                    }
                }
            ],
        });

        function show_det(bulan, nama_bulan, tahun) {
            // Save the current filter parameters in global vars
            window.current_bulan = bulan;
            window.current_nama_bulan = nama_bulan;
            window.current_tahun = tahun;

            // Fill input values inside modal
            $('#modal_bulan').val(bulan);
            $('#modal_nama_bulan').val(nama_bulan);
            $('#modal_tahun').val(tahun);

            // Show modal
            const myModal = new bootstrap.Modal(document.getElementById('myModal'));
            myModal.show();

            // Reload DataTable with new params
            if (datatable_modal) {
                datatable_modal.ajax.reload();
            }
        }


        let datatable_modal = $("#datatable_modal").DataTable({
            ordering: false,
            responsive: false,
            processing: true,
            serverSide: false,
            paging: false,
            ordering: true,
            searching: true,
            scrollY: '300px',
            scrollX: true,
            scrollCollapse: false,

            ajax: {
                url: '{{ route('show_mgt_report_det_daily_cost') }}',
                data: function(d) {
                    d.bulan = window.current_bulan;
                    d.tahun = window.current_tahun;
                },
            },
            columns: [{
                    data: 'no_coa'
                },
                {
                    data: 'nama_coa'
                },
                {
                    data: 'projection',
                    className: 'text-end', // Bootstrap right align
                    render: function(data, type, row) {
                        return parseFloat(data).toLocaleString(undefined, {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });
                    }
                },
                {
                    data: 'daily_cost',
                    className: 'text-end',
                    render: function(data, type, row) {
                        return parseFloat(data).toLocaleString(undefined, {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });
                    }
                }
            ],
            drawCallback: function(settings) {
                let api = this.api();
                let tot_proj_modal = 0;
                let tot_daily_modal = 0;

                // Loop through all data in daily_cost column
                api.column(2, {
                    page: 'current'
                }).data().each(function(value) {
                    tot_proj_modal += parseFloat(value) || 0;
                });

                // Display the total in the footer
                $('#tot_proj_modal').html(tot_proj_modal.toLocaleString(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));


                // Loop through all data in daily_cost column
                api.column(3, {
                    page: 'current'
                }).data().each(function(value) {
                    tot_daily_modal += parseFloat(value) || 0;
                });

                // Display the total in the footer
                $('#tot_daily_modal').html(tot_daily_modal.toLocaleString(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));

            }
        });


        function delete_data() {
            let modal_bulan = $('#modal_bulan').val();
            let modal_nama_bulan = $('#modal_nama_bulan').val();
            let modal_tahun = $('#modal_tahun').val();

            // Continue with confirmation if all valid
            Swal.fire({
                title: 'Konfirmasi',
                text: `Apakah kamu yakin menghapus data dengan Bulan: ${modal_nama_bulan}, Tahun: ${modal_tahun}?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, simpan',
                cancelButtonText: 'Batal',
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: "POST",
                        url: '{{ route('delete_daily_cost') }}',
                        data: {
                            _token: '{{ csrf_token() }}',
                            bulan: modal_bulan,
                            nama_bulan: modal_nama_bulan,
                            tahun: modal_tahun
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Daily Cost Dihapus!',
                                html: `
                    <p><b>Bulan :</b> ${response.data.nama_bulan}</p>
                    <p><b>Tahun :</b> ${response.data.tahunText}</p>
                    <hr>
                    <p>Data berhasil dihapus.</p>
                `
                            }).then(() => {
                                location.reload();
                            });
                        },
                        error: function(xhr) {
                            console.error('Save Error:', xhr.responseText);
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: 'Gagal menyimpan data.'
                            });
                        }
                    });
                } else {
                    console.log('User cancelled saving.');
                    location.reload();
                }
            });
        }
    </script>
@endsection
