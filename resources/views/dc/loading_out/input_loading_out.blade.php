@extends('layouts.index')

@section('custom-link')
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables 2.0/jquery.dataTables.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables 2.0/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables 2.0/fixedColumns.bootstrap4.min.css') }}">
    <!-- jQuery -->
    <script src="{{ asset('plugins/datatables 2.0/jquery-3.3.1.js') }}"></script>


    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

    <style>
        .select2-container--bootstrap4 .select2-selection--single {
            height: 31px !important;
            padding: 0.25rem 0.5rem !important;
            font-size: 0.875rem !important;
        }

        .select2-container--bootstrap4 .select2-selection__rendered {
            line-height: 1.5 !important;
        }
    </style>
@endsection

@section('content')
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-list"></i> Header Loading Out / WIP Out</h5>
        </div>
        <div class="card-body pb-0">
            <div class="row mb-3 gy-3">
                <div class="col-12 col-sm-6 col-lg-3">
                    <label for="cbo_sup"><small><b>Supplier :</b></small></label>
                    <select class="form-control form-control-sm select2bs4 select-border-primary visual-input"
                        id="cbo_sup" name="cbo_sup" style="width: 100%;" onchange="getno_po();">
                        <option selected="selected" value="" disabled="true">Pilih Supplier
                        </option>
                        @foreach ($data_supplier as $ds)
                            <option value="{{ $ds->isi }}">
                                {{ $ds->tampil }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-lg-3">
                    <label for="cbo_po"><small><b>PO :</b></small></label>
                    <select class='form-control select2bs4 select-border-primary visual-input' style='width: 100%;'
                        name='cbo_po' id='cbo_po' onchange="dataTablePOReload();dataTableScanReload();"></select>
                </div>
                <div class="col-12 col-sm-6 col-lg-3">
                    <label for="cbo_dok"><small><b>Jenis Dokumen :</b></small></label>
                    <select class="form-control form-control-sm select2bs4 select-border-primary visual-input"
                        id="cbo_dok" name="cbo_dok" style="width: 100%;">
                        <option selected="selected" value="" disabled="true">Pilih Dokumen
                        </option>
                        @foreach ($data_dok as $dd)
                            <option value="{{ $dd->isi }}">
                                {{ $dd->tampil }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-lg-3">
                    <label for="cbo_jns"><small><b>Jenis Pengeluaran :</b></small></label>
                    <select class="form-control form-control-sm select2bs4 select-border-primary visual-input"
                        id="cbo_jns" name="cbo_jns" style="width: 100%;">
                        <option selected="selected" value="" disabled="true">Pilih Jenis
                        </option>
                        @foreach ($data_jns as $dj)
                            <option value="{{ $dj->isi }}">
                                {{ $dj->tampil }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row mb-3 gy-3">
                <div class="col-12 col-sm-6 col-lg-3">
                    <label for="txt_ket"><small><b>Tgl. Transaksi :</b></small></label>
                    <input type="date" class="form-control form-control-sm " id="tgl_trans" name="tgl_trans"
                        value="{{ date('Y-m-d') }}">
                </div>
                <div class="col-12 col-sm-6 col-lg-3">
                    <label for="txt_ket"><small><b>Keterangan :</b></small></label>
                    <input type="text" id="txt_ket" name="txt_ket" class="form-control form-control-sm border-primary">
                </div>
                <div class="col-12 col-sm-6 col-lg-3">
                    <label for="txt_berat_panel"><small><b>Berat Set Panel:</b></small></label>
                    <div class="input-group input-group-sm">
                        <input type="text" id="txt_berat_panel" name="txt_berat_panel"
                            class="form-control form-control-sm border-primary">
                        <span class="input-group-text border-primary text-primary">KG</span>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-lg-3">
                    <label for="txt_berat_karung"><small><b>Berat Karung:</b></small></label>
                    <div class="input-group input-group-sm">
                        <input type="text" id="txt_berat_karung" name="txt_berat_karung"
                            class="form-control form-control-sm border-primary">
                        <span class="input-group-text border-primary text-primary">KG</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-footer text-end">
            <button type="button" id="btnsave" name="btnsave" class="btn btn-success btn-sm px-4"
                onclick="saveLoadingOut()">
                <i class="fas fa-save me-1"></i> Save
            </button>
        </div>

    </div>

    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-list"></i> Detail PO </h5>
        </div>
        <div class="card-body pb-0">
            <div class="row mb-3">
                <div class="table-responsive">
                    <table id="datatable_po" class="table table-bordered table-hover align-middle w-100">
                        <thead class="bg-sb">
                            <tr>
                                <th class="text-center">Job Order</th>
                                <th class="text-center">WS</th>
                                <th class="text-center">ID Item</th>
                                <th class="text-center">Item</th>
                                <th class="text-center">Qty PO</th>
                                <th class="text-center">Unit</th>
                                <th class="text-center">Qty Outstanding</th>
                                <th class="text-center">Qty Out</th>
                                <th class="text-center">Balance</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>



    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-list"></i> Scan Stocker </h5>
        </div>
        <div class="card-body pb-0">
            <div class="row mb-3">
                <!-- No. Karung -->
                <div class="col-md-6 mb-2 mb-md-0">
                    <label for="txtno_karung" class="form-label">
                        <small><b>No. Karung</b></small>
                    </label>
                    <input type="text" id="txtno_karung" name="txtno_karung"
                        class="form-control form-control-sm border-primary" placeholder="Masukkan No. Karung">
                </div>

                <!-- Scan Stocker-->
                <div class="col-md-6">
                    <label class="form-label">
                        <small><b>No. Stocker</b></small>
                    </label>
                    <div class="input-group input-group-sm">
                        <input type="text" id="txtno_stocker" class="form-control border-primary"
                            placeholder="Scan stocker" autocomplete="off">
                        <a href="#" onclick="scan_stocker();" class="btn btn-outline-primary border-primary"
                            id="btn_scan">
                            SCAN
                        </a>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table id="datatable_scan" class="table table-bordered table-hover align-middle w-100">
                    <thead class="bg-sb">
                        <tr>
                            <th class="text-center">ACT</th>
                            <th class="text-center">No. Karung</th>
                            <th class="text-center">No. Stocker</th>
                            <th class="text-center">No. Cut</th>
                            <th class="text-center">Panel</th>
                            <th class="text-center">Group</th>
                            <th class="text-center">WS</th>
                            <th class="text-center">Style</th>
                            <th class="text-center">Color</th>
                            <th class="text-center">Size</th>
                            <th class="text-center">Qty</th>
                            <th class="text-center">Range</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('custom-script')
    <!-- DataTables & Plugins -->
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
        });

        function notif() {
            alert("Maaf, Fitur belum tersedia!");
        }
    </script>
    <script>
        $(document).ready(function() {
            $('#cbo_sup').val('').trigger('change');
            $('#cbo_dok').val('').trigger('change');
            $('#cbo_jns').val('').trigger('change');
            $('#txt_ket').val('');
            $('#txt_berat_panel').val('0');
            $('#txt_berat_karung').val('0');
            $('#txtno_karung').val('');

            // Trigger scan saat ENTER dari scanner
            $('#txtno_stocker').on('keydown', function(e) {
                if (e.key === 'Enter' || e.keyCode === 13) {
                    e.preventDefault();
                    $(this).val($(this).val().trim());
                    scan_stocker();
                }
            });

            // Fallback jika scanner kirim newline (\n / \r)
            $('#txtno_stocker').on('input', function() {
                if (this.value.includes('\n') || this.value.includes('\r')) {
                    this.value = this.value.trim();
                    scan_stocker();
                }
            });
        });

        function getno_po() {
            let cbo_sup = $('#cbo_sup').val();

            $.ajax({
                type: "GET",
                url: '{{ route('getpo_loading_out') }}',
                data: {
                    cbo_sup: cbo_sup
                },
                success: function(html) {
                    if (html !== "") {
                        $("#cbo_po").html(html);
                    }
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                }
            });
        }


        let datatable_po = $('#datatable_po').DataTable({
            ordering: false,
            processing: true,
            serverSide: false,
            paging: false,
            searching: true,
            scrollCollapse: true,
            autoWidth: false,
            responsive: false,
            scrollX: true,

            ajax: {
                url: '{{ route('get_list_po_loading_out') }}',
                type: "GET",
                data: function(d) {
                    d.id_po = $('#cbo_po').val();
                    console.log(d.id_po);
                }
            },

            columns: [{
                    data: 'jo_no'
                },
                {
                    data: 'ws'
                },
                {
                    data: 'id_item'
                },
                {
                    data: 'itemdesc'
                },
                {
                    data: 'qty_po'
                },
                {
                    data: 'unit'
                },
                {
                    data: 'qty_outstanding'
                },
                {
                    data: 'qty_input'
                },
                {
                    data: 'blc'
                }
            ],
            initComplete: function() {
                this.api().columns.adjust();
            }

        });

        function dataTablePOReload() {
            datatable_po.ajax.reload(null, false);

            datatable_po.off('draw.dt.adjust').on('draw.dt.adjust', function() {
                requestAnimationFrame(() => {
                    datatable_po.columns.adjust();
                });
            });
        }


        function scan_stocker() {

            let no_karung = $('#txtno_karung').val().trim();
            let no_stocker = $('#txtno_stocker').val().trim();
            let id_po = $('#cbo_po').val();

            console.log(id_po, no_stocker);

            // Validasi PO
            if (!id_po) {
                Swal.fire({
                    icon: 'warning',
                    title: 'PO Belum Dipilih',
                    text: 'Silakan pilih PO terlebih dahulu',
                    timer: 500,
                    showConfirmButton: false
                }).then(() => {
                    $('#cbo_po').focus();
                });
                return;
            }

            // Validasi No Karung
            if (!no_karung) {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Karung Kosong',
                    text: 'Silakan isi No Karung',
                    timer: 500,
                    showConfirmButton: false
                }).then(() => {
                    $('#txtno_karung').focus();
                });
                return;
            }

            // Validasi No Stocker
            if (!no_stocker) {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Stocker Kosong',
                    text: 'Silakan scan No Stocker',
                    timer: 500,
                    showConfirmButton: false
                }).then(() => {
                    $('#txtno_stocker').focus();
                });
                return;
            }

            $.ajax({
                type: "POST",
                url: '{{ route('get_loading_out_stocker_info') }}',
                data: {
                    _token: "{{ csrf_token() }}",
                    no_karung: no_karung,
                    no_stocker: no_stocker,
                    id_po: id_po
                },
                dataType: "json",
                success: function(res) {

                    if (res.result === 'N') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Stocker Tidak Valid',
                            text: res.message || 'Stocker Tidak Valid',
                            timer: 500,
                            showConfirmButton: false
                        });
                        return;
                    }

                    // Step berikutnya: simpan ke TMP
                    saveTmpStocker(res.data);
                },
                error: function() {
                    alert('Gagal koneksi ke server');
                },
                complete: function() {
                    // Bersihkan & fokus ulang (scanner friendly)
                    $('#txtno_stocker').val('');
                }
            });
        }

        function saveTmpStocker(data) {

            $.ajax({
                type: "POST",
                url: '{{ route('save_tmp_stocker_loading_out') }}',
                data: {
                    _token: "{{ csrf_token() }}",
                    id_po: $('#cbo_po').val(),
                    no_karung: $('#txtno_karung').val().trim(),
                    no_stocker: $('#txtno_stocker').val().trim(),
                    // data tambahan dari backend sebelumnya
                    // item_id: data.item_id ?? null,
                    // qty: data.qty ?? 1
                },
                dataType: "json",
                success: function(res) {

                    if (res.status === 'success') {
                        // Swal.fire({
                        //     icon: 'success',
                        //     title: 'Berhasil',
                        //     text: res.message || 'Stocker berhasil disimpan',
                        //     timer: 500,
                        //     showConfirmButton: false
                        // });

                        // optional: refresh tabel TMP
                        // loadTmpStocker();

                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: res.message || 'Gagal simpan stocker'
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Gagal koneksi ke server'
                    });
                },
                complete: function() {
                    // siap scan berikutnya
                    dataTableScanReload();
                    dataTablePOReload();
                }
            });
        }


        let datatable_scan = $('#datatable_scan').DataTable({
            ordering: false,
            processing: true,
            serverSide: false,
            paging: false,
            searching: true,
            scrollCollapse: true,
            autoWidth: true,
            scrollX: true,

            ajax: {
                url: '{{ route('get_list_tmp_scan_loading_out') }}',
                type: "GET",
                data: function(d) {
                    d.id_po = $('#cbo_po').val();
                }
            },

            columns: [{
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        return `
            <div class="text-center align-middle visual-input">
                <button class="btn btn-outline-danger btn-sm btnDelete"
                        onclick="deleteTmp(${data.id}, '${data.id_qr_stocker}')">
                    Delete
                </button>
            </div>
                `;
                    }
                },
                {
                    data: 'no_karung'
                },
                {
                    data: 'id_qr_stocker'
                },
                {
                    data: 'no_cut'
                },
                {
                    data: 'itemdesc'
                },
                {
                    data: 'shell'
                },
                {
                    data: 'kpno'
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
                    data: 'qty_ply'
                },
                {
                    data: 'range_stocker'
                }
            ],
            initComplete: function() {
                this.api().columns.adjust();
            }

        });

        function dataTableScanReload() {
            datatable_scan.ajax.reload(null, false);

            datatable_scan.off('draw.dt.adjust').on('draw.dt.adjust', function() {
                requestAnimationFrame(() => {
                    datatable_scan.columns.adjust();
                });
            });
        }

        function deleteTmp(id, id_qr_stocker) {
            Swal.fire({
                title: 'Yakin ingin menghapus?',
                html: `<b>QR Stocker:</b> ${id_qr_stocker}`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: 'POST',
                        url: '{{ route('loading_out_delete_tmp') }}', // Your delete route
                        data: {
                            _token: '{{ csrf_token() }}',
                            id: id
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: response.message,
                                timer: 500,
                                showConfirmButton: false
                            });
                            dataTableScanReload();
                            dataTablePOReload();
                        },
                        error: function() {
                            Swal.fire('Gagal', 'Data tidak dapat dihapus', 'error');
                        }
                    });
                }
            });
        }

        function saveLoadingOut() {

            let cbo_sup = $('#cbo_sup').val();
            let id_po = $('#cbo_po').val();
            let cbo_dok = $('#cbo_dok').val();
            let cbo_jns = $('#cbo_jns').val();
            let tgl_trans = $('#tgl_trans').val();
            let txt_ket = $('#txt_ket').val();
            let txt_berat_panel = $('#txt_berat_panel').val();
            let txt_berat_karung = $('#txt_berat_karung').val();

            if (!cbo_sup || !id_po || !cbo_dok || !cbo_jns) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Data belum lengkap',
                    text: 'Mohon lengkapi data terlebih dahulu.'
                });
                return;
            }

            Swal.fire({
                title: 'Simpan data Loading Out?',
                text: 'Pastikan data sudah benar.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Simpan',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (!result.isConfirmed) return;

                $('#btnsave').prop('disabled', true);

                $.ajax({
                    type: "POST",
                    url: '{{ route('save_loading_out') }}',
                    data: {
                        _token: '{{ csrf_token() }}',
                        cbo_sup,
                        id_po,
                        cbo_dok,
                        cbo_jns,
                        tgl_trans,
                        txt_ket,
                        txt_berat_panel,
                        txt_berat_karung
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            Swal.fire({
                                title: 'Berhasil Disimpan!',
                                html: `No Form: <b>${response.no_form}</b>`,
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                location.reload(); // reload setelah user klik OK
                            });
                        } else {
                            Swal.fire('Gagal', response.message, 'error');
                        }

                    },
                    error: function(xhr) {
                        let message = 'Gagal menyimpan data.';

                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }

                        Swal.fire({
                            icon: 'warning',
                            title: 'Tidak bisa disimpan',
                            text: message
                        });
                    },

                    complete: function() {
                        $('#btnsave').prop('disabled', false);
                    }
                });

            });
        }
    </script>
@endsection
